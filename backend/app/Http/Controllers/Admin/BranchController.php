<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\laundry;
use App\Models\User;
use App\Models\DeletedRecord;
use App\Models\ActivityLog;
use App\Services\GeocodingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BranchController extends Controller
{
    /**
     * Display a listing of branches with statistics
     */
    public function index()
    {
        // Get all branches with stats for the current month
        $branches = Branch::withCount(['laundries as laundries_mtd' => function($query) {
                $query->whereMonth('created_at', Carbon::now()->month);
            }])
            ->withAvg('ratings', 'rating')      // avg rating per branch (single SQL aggregate)
            ->withCount('ratings')               // total review count per branch
            ->with(['ratings' => function($q) { // lightweight eager load for per-star breakdown
                $q->select('id', 'branch_id', 'rating');
            }])
            ->get()
            ->map(function($branch) {
                // Calculate MTD revenue (laundry + retail)
                $laundryRevenue = laundry::where('branch_id', $branch->id)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->sum('total_amount');
                
                $retailRevenue = \App\Models\RetailSale::where('branch_id', $branch->id)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->sum('total_amount');
                
                $branch->revenue_mtd = $laundryRevenue + $retailRevenue;

                // Count active staff
                $branch->active_staff = User::where('branch_id', $branch->id)
                    ->where('role', 'staff')
                    ->where('is_active', true)
                    ->count();

                // Rating convenience fields for the blade
                $branch->avg_rating    = round((float) ($branch->ratings_avg_rating ?? 0), 1);
                $branch->total_ratings = (int) ($branch->ratings_count ?? 0);

                // Per-star breakdown [5 => n, 4 => n, 3 => n, 2 => n, 1 => n]
                $counts = $branch->ratings->groupBy('rating')->map->count();
                $branch->rating_distribution = collect(range(5, 1))
                    ->mapWithKeys(fn($star) => [$star => $counts->get($star, 0)])
                    ->toArray();

                return $branch;
            });

        // Calculate network-wide statistics
        $total_laundries = Laundry::count();
        $laundry_revenue = Laundry::sum('total_amount');
        $retail_revenue = \App\Models\RetailSale::sum('total_amount');
        $total_revenue = $laundry_revenue + $retail_revenue;
        $total_staff = $branches->sum('active_staff');

        return view('admin.branches.index', compact('branches', 'total_laundries', 'total_revenue', 'total_staff'));
    }

    /**
     * Show the form for creating a new branch
     */
    public function create()
    {
        $defaultProvince = 'Negros Oriental';
        return view('admin.branches.create', compact('defaultProvince'));
    }

    /**
     * Store a newly created branch in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:branches,code',
            'username' => 'required|string|max:50|unique:branches,username',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'operating_hours' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'gcash_number' => 'nullable|string|max:20',
            'gcash_name' => 'nullable|string|max:255',
            'gcash_qr_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Map manager to manager_name for database
        if (isset($validated['manager'])) {
            $validated['manager_name'] = $validated['manager'];
            unset($validated['manager']);
        }

        // Map gcash fields to database column names
        if (isset($validated['gcash_name'])) {
            $validated['gcash_account_name'] = $validated['gcash_name'];
            unset($validated['gcash_name']);
        }
        if (isset($validated['gcash_number'])) {
            $validated['gcash_account_number'] = $validated['gcash_number'];
            unset($validated['gcash_number']);
        }

        // Hash the password
        $validated['password'] = bcrypt($validated['password']);

        // Set is_active (default to true if not provided)
        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Auto-geocode if coordinates not provided by map picker
        if (empty($validated['latitude']) || empty($validated['longitude'])) {
            $validated = $this->autoGeocode($validated);
        }

        // Handle GCash QR image upload
        if ($request->hasFile('gcash_qr_image')) {
            $file = $request->file('gcash_qr_image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('gcash-qr', $filename, 'public');
            $validated['gcash_qr_image'] = $filename;
        }

        Branch::create($validated);

        $this->clearDashboardCache();

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch created successfully!');
    }

    /**
     * Display the specified branch with analytics
     */
    public function show(Branch $branch)
    {
        // Load relationships for detailed analytics
        $branch->load(['laundries', 'staff']);

        // Calculate branch statistics
        $stats = [
            'total_laundries' => $branch->laundries()->count(),
            'completed_laundries' => $branch->laundries()->where('status', 'completed')->count(),
            'laundry_revenue' => $branch->laundries()->sum('total_amount'),
            'retail_revenue' => \App\Models\RetailSale::where('branch_id', $branch->id)->sum('total_amount'),
            'total_revenue' => $branch->laundries()->sum('total_amount') + \App\Models\RetailSale::where('branch_id', $branch->id)->sum('total_amount'),
            'avg_laundry_value' => $branch->laundries()->avg('total_amount') ?? 0,
            'staff_count' => $branch->staff()->count(),
            'active_staff' => $branch->staff()->where('is_active', true)->count(),
            'laundries_mtd' => $branch->laundries()->whereMonth('created_at', Carbon::now()->month)->count(),
            'revenue_mtd' => $branch->laundries()->whereMonth('created_at', Carbon::now()->month)->sum('total_amount') + \App\Models\RetailSale::where('branch_id', $branch->id)->whereMonth('created_at', Carbon::now()->month)->sum('total_amount'),
        ];

        // Recent laundries
        $recent_laundries = $branch->laundries()
            ->with(['customer', 'service'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.branches.show', compact('branch', 'stats', 'recent_laundries'));
    }

    /**
     * Show the form for editing the specified branch
     */
    public function edit(Branch $branch)
    {
        // Convert JSON operating_hours to simple text for display
        if ($branch->operating_hours && is_array($branch->operating_hours)) {
            $branch->operating_hours = $this->convertJsonToText($branch->operating_hours);
        }

        // Add MTD stats for display
        $branch->laundries_mtd = $branch->laundries()->whereMonth('created_at', Carbon::now()->month)->count();
        $laundryRevenueMtd = $branch->laundries()->whereMonth('created_at', Carbon::now()->month)->sum('total_amount');
        $retailRevenueMtd = \App\Models\RetailSale::where('branch_id', $branch->id)->whereMonth('created_at', Carbon::now()->month)->sum('total_amount');
        $branch->revenue_mtd = $laundryRevenueMtd + $retailRevenueMtd;
        $branch->active_staff = User::where('branch_id', $branch->id)
            ->where('role', 'staff')
            ->where('is_active', true)
            ->count();

        return view('admin.branches.edit', compact('branch'));
    }

    /**
     * Update the specified branch in storage
     */
    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:branches,code,' . $branch->id,
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
            'hours' => 'nullable|array',
            'hours.*.open' => 'nullable|string',
            'hours.*.close' => 'nullable|string',
            'hours.*.enabled' => 'nullable|boolean',
            'gcash_number' => 'nullable|string|max:20',
            'gcash_name' => 'nullable|string|max:255',
            'gcash_qr_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Map manager to manager_name for database
        if (isset($validated['manager'])) {
            $validated['manager_name'] = $validated['manager'];
            unset($validated['manager']);
        }

        // Map gcash fields to database column names
        if (isset($validated['gcash_name'])) {
            $validated['gcash_account_name'] = $validated['gcash_name'];
            unset($validated['gcash_name']);
        }
        if (isset($validated['gcash_number'])) {
            $validated['gcash_account_number'] = $validated['gcash_number'];
            unset($validated['gcash_number']);
        }

        // Update is_active status
        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Process operating hours
        if ($request->has('hours')) {
            $operatingHours = [];
            foreach ($request->hours as $day => $hours) {
                if (isset($hours['enabled'])) {
                    $operatingHours[$day] = [
                        'open' => $hours['open'] ?? '07:00',
                        'close' => $hours['close'] ?? '20:00',
                        'status' => 'open'
                    ];
                } else {
                    $operatingHours[$day] = 'closed';
                }
            }
            $validated['operating_hours'] = $operatingHours;
        }

        // Auto-geocode if coordinates were cleared or never set
        if (empty($validated['latitude']) || empty($validated['longitude'])) {
            $validated = $this->autoGeocode($validated);
        }

        // Handle GCash QR image upload
        if ($request->hasFile('gcash_qr_image')) {
            // Delete old image if exists
            if ($branch->gcash_qr_image) {
                Storage::disk('public')->delete('gcash-qr/' . $branch->gcash_qr_image);
            }
            
            // Store new image
            $file = $request->file('gcash_qr_image');
            $filename = time() . '_' . $branch->id . '.' . $file->getClientOriginalExtension();
            $file->storeAs('gcash-qr', $filename, 'public');
            $validated['gcash_qr_image'] = $filename;
        }

        $branch->update($validated);

        $this->clearDashboardCache();

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch updated successfully!');
    }

    /**
     * Remove the specified branch from storage
     */
    public function destroy(Branch $branch)
    {
        // Check if branch has any laundries
        if ($branch->laundries()->count() > 0) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Cannot delete branch with existing laundries. Please deactivate instead.');
        }

        // Check if branch has any staff
        if ($branch->staff()->count() > 0) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Cannot delete branch with assigned staff. Please reassign or deactivate staff first.');
        }

        $branch->delete();

        DeletedRecord::snapshot($branch, 'branch');
        ActivityLog::log('deleted', "Branch \"{$branch->name}\" deleted", 'branch', null, [
            'name' => $branch->name,
        ]);

        $this->clearDashboardCache();

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch deleted successfully!');
    }

    /**
     * Toggle branch active/inactive status
     */
    public function toggleStatus(Branch $branch)
    {
        $branch->update(['is_active' => !$branch->is_active]);

        $this->clearDashboardCache();

        $status = $branch->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.branches.index')
            ->with('success', "Branch {$status} successfully!");
    }

    /**
     * Deactivate a branch
     */
    public function deactivate(Branch $branch)
    {
        $branch->update(['is_active' => false]);
        $this->clearDashboardCache();
        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch deactivated successfully.');
    }

    /**
     * Activate a branch
     */
    public function activate(Branch $branch)
    {
        $branch->update(['is_active' => true]);
        $this->clearDashboardCache();
        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch activated successfully.');
    }

    /**
     * Reset branch password
     */
    public function resetPassword(Request $request, Branch $branch)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $branch->update([
            'password' => bcrypt($request->password),
        ]);

        return redirect()->route('admin.branches.edit', $branch)
            ->with('success', 'Branch password reset successfully!');
    }

    /**
     * Get branch staff
     */
    public function staff(Branch $branch)
    {
        $staff = User::where('branch_id', $branch->id)
            ->where('role', 'staff')
            ->with(['laundries' => function($query) {
                $query->whereMonth('created_at', Carbon::now()->month);
            }])
            ->get()
            ->map(function($user) {
                $user->laundries_mtd = $user->laundries->count();
                $user->revenue_mtd = $user->laundries->sum('total_amount');
                return $user;
            });

        return view('admin.branches.staff', compact('branch', 'staff'));
    }

    /**
     * Get branch analytics
     */
    public function analytics(Branch $branch, Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now());
        
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // KPI Statistics
        $laundries = $branch->laundries()
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        $stats = [
            'total_laundries' => $laundries->count(),
            'completed_laundries' => (clone $laundries)->where('status', 'completed')->count(),
            'laundry_revenue' => $laundries->sum('total_amount'),
            'retail_sales' => \App\Models\RetailSale::where('branch_id', $branch->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount'),
            'retail_count' => \App\Models\RetailSale::where('branch_id', $branch->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'total_customers' => $laundries->distinct('customer_id')->count('customer_id'),
            'total_expenses' => \App\Models\Expense::where('branch_id', $branch->id)
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->sum('amount'),
            'expense_count' => \App\Models\Expense::where('branch_id', $branch->id)
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->count(),
        ];
        
        $stats['total_revenue'] = $stats['laundry_revenue'] + $stats['retail_sales'];
        $stats['total_profit'] = $stats['total_revenue'] - $stats['total_expenses'];
        $stats['profit_margin'] = $stats['total_revenue'] > 0 
            ? round(($stats['total_profit'] / $stats['total_revenue']) * 100, 1) 
            : 0;

        // Revenue Trend (Daily)
        $days = [];
        $laundryRevenue = [];
        $retailRevenue = [];
        
        $period = Carbon::parse($startDate);
        while ($period <= $endDate) {
            $days[] = $period->format('M d');
            
            $laundryRevenue[] = $branch->laundries()
                ->whereDate('created_at', $period)
                ->sum('total_amount');
            
            $retailRevenue[] = \App\Models\RetailSale::where('branch_id', $branch->id)
                ->whereDate('created_at', $period)
                ->sum('total_amount');
            
            $period->addDay();
        }

        // Service Performance
        $servicePerformance = $branch->laundries()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('service')
            ->select('service_id')
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(total_amount) as revenue')
            ->groupBy('service_id')
            ->orderByDesc('order_count')
            ->get();

        // Daily Orders
        $dailyOrders = [];
        $period = Carbon::parse($startDate);
        while ($period <= $endDate) {
            $dailyOrders[] = $branch->laundries()
                ->whereDate('created_at', $period)
                ->count();
            $period->addDay();
        }

        // Expense Categories
        $expenseCategories = \App\Models\Expense::where('branch_id', $branch->id)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->with('category')
            ->select('expense_category_id')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->orderByDesc('total')
            ->get();

        // Top Customers
        $topCustomers = \App\Models\Customer::whereHas('laundries', function($q) use ($branch, $startDate, $endDate) {
                $q->where('branch_id', $branch->id)
                  ->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->withCount(['laundries as order_count' => function($q) use ($branch, $startDate, $endDate) {
                $q->where('branch_id', $branch->id)
                  ->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withSum(['laundries as total_spent' => function($q) use ($branch, $startDate, $endDate) {
                $q->where('branch_id', $branch->id)
                  ->whereBetween('created_at', [$startDate, $endDate]);
            }], 'total_amount')
            ->orderByDesc('total_spent')
            ->take(10)
            ->get();

        // Chart Data
        $chartData = [
            'revenueTrend' => [
                'labels' => $days,
                'laundry' => $laundryRevenue,
                'retail' => $retailRevenue,
            ],
            'revenueBreakdown' => [
                'laundry' => $stats['laundry_revenue'],
                'retail' => $stats['retail_sales'],
            ],
            'servicePerformance' => [
                'labels' => $servicePerformance->pluck('service.name')->toArray(),
                'orders' => $servicePerformance->pluck('order_count')->toArray(),
            ],
            'dailyOrders' => [
                'labels' => $days,
                'data' => $dailyOrders,
            ],
            'expenseCategories' => [
                'labels' => $expenseCategories->pluck('category.name')->toArray(),
                'data' => $expenseCategories->pluck('total')->toArray(),
            ],
        ];

        return view('admin.branches.analytics', compact('branch', 'stats', 'chartData', 'topCustomers'));
    }

    /**
     * Auto-geocode branch address to coordinates using GeocodingService
     * Called as a server-side fallback when map picker wasn't used
     */
    private function autoGeocode(array $data): array
    {
        $addressParts = array_filter([
            $data['address'] ?? '',
            $data['city'] ?? '',
            $data['province'] ?? '',
            'Philippines'
        ]);

        if (count($addressParts) <= 1) {
            return $data;
        }

        try {
            $geocoder = app(GeocodingService::class);
            $result = $geocoder->geocodeAddress(implode(', ', $addressParts));

            if (!empty($result['success']) && $result['success'] === true) {
                $data['latitude'] = $result['latitude'];
                $data['longitude'] = $result['longitude'];
            }
        } catch (\Exception $e) {
            // Geocoding is best-effort; don't block branch creation
            \Illuminate\Support\Facades\Log::warning('Branch auto-geocode failed', [
                'address' => implode(', ', $addressParts),
                'error' => $e->getMessage(),
            ]);
        }

        return $data;
    }

    /**
     * Clear dashboard cache so map markers update immediately
     */
    private function clearDashboardCache()
    {
        Cache::forget('dashboard_stats_' . Auth::id());

        $adminIds = User::where('role', 'admin')->pluck('id');
        foreach ($adminIds as $id) {
            Cache::forget('dashboard_stats_' . $id);
        }
    }

    /**
     * Convert JSON operating hours to simple text
     */
    private function convertJsonToText($hours): string
    {
        if (empty($hours)) {
            return '';
        }

        // Check if all weekdays have same hours
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $weekends = ['Saturday', 'Sunday'];

        $weekdayHours = '';
        $weekendHours = '';

        // Check weekdays
        $firstWeekday = $hours['Monday'] ?? null;
        if ($firstWeekday && !isset($firstWeekday['closed'])) {
            $allSame = true;
            foreach ($weekdays as $day) {
                if (!isset($hours[$day]) ||
                    ($hours[$day]['open'] ?? '') !== ($firstWeekday['open'] ?? '') ||
                    ($hours[$day]['close'] ?? '') !== ($firstWeekday['close'] ?? '')) {
                    $allSame = false;
                    break;
                }
            }

            if ($allSame) {
                $open = $this->formatTime($firstWeekday['open']);
                $close = $this->formatTime($firstWeekday['close']);
                $weekdayHours = "Monday-Friday: {$open} - {$close}";
            }
        }

        // Check weekends
        $firstWeekend = $hours['Saturday'] ?? null;
        if ($firstWeekend && !isset($firstWeekend['closed'])) {
            $bothSame = true;
            foreach ($weekends as $day) {
                if (!isset($hours[$day]) ||
                    ($hours[$day]['open'] ?? '') !== ($firstWeekend['open'] ?? '') ||
                    ($hours[$day]['close'] ?? '') !== ($firstWeekend['close'] ?? '')) {
                    $bothSame = false;
                    break;
                }
            }

            if ($bothSame) {
                $open = $this->formatTime($firstWeekend['open']);
                $close = $this->formatTime($firstWeekend['close']);
                $weekendHours = "Saturday-Sunday: {$open} - {$close}";
            }
        }

        // Combine
        if ($weekdayHours && $weekendHours) {
            return "$weekdayHours, $weekendHours";
        } elseif ($weekdayHours) {
            return $weekdayHours;
        } elseif ($weekendHours) {
            return $weekendHours;
        }

        // Fallback: list all days
        $result = [];
        foreach ($hours as $day => $time) {
            if (isset($time['closed']) && $time['closed']) {
                $result[] = ucfirst($day) . ": Closed";
            } elseif (isset($time['open']) && isset($time['close'])) {
                $open = $this->formatTime($time['open']);
                $close = $this->formatTime($time['close']);
                $result[] = ucfirst($day) . ": {$open} - {$close}";
            }
        }

        return implode(', ', $result);
    }

    /**
     * Format time to 12-hour format with AM/PM
     */
    private function formatTime($time): string
    {
        try {
            $dateTime = \DateTime::createFromFormat('H:i', $time);
            if ($dateTime) {
                return $dateTime->format('g:i A');
            }
        } catch (\Exception $e) {
            // Return as-is if conversion fails
        }
        return $time;
    }
}
