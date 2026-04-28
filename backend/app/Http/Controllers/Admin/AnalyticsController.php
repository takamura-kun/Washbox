<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laundry;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Service;
use App\Models\Promotion;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // Validate inputs to prevent XSS and injection attacks
        $validated = $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        try {
            $startDate = isset($validated['start_date']) 
                ? Carbon::parse($validated['start_date'])->startOfDay() 
                : now()->subDays(30)->startOfDay();
            
            $endDate = isset($validated['end_date']) 
                ? Carbon::parse($validated['end_date'])->endOfDay() 
                : now()->endOfDay();

            return view('admin.analytics.index', [
                'startDate'              => $startDate->format('Y-m-d'),
                'endDate'                => $endDate->format('Y-m-d'),
                'revenueAnalytics'       => $this->safeGetRevenueAnalytics($startDate, $endDate),
                'laundryAnalytics'       => $this->safeGetLaundryAnalytics($startDate, $endDate),
                'branchPerformance'      => $this->safeGetBranchPerformance($startDate, $endDate),
                'servicePopularity'      => $this->safeGetServicePopularity($startDate, $endDate),
                'customerAnalytics'      => $this->safeGetCustomerAnalytics($startDate, $endDate),
                'promotionEffectiveness' => $this->safeGetPromotionEffectiveness($startDate, $endDate),
                'expenseAnalytics'       => $this->safeGetExpenseAnalytics($startDate, $endDate),
                'retailAnalytics'        => $this->safeGetRetailAnalytics($startDate, $endDate),
                'profitAnalytics'        => $this->safeGetProfitAnalytics($startDate, $endDate),
                'expenseCategoryData'    => $this->getExpenseCategoryData($startDate, $endDate),
                'monthlyComparison'      => $this->getMonthlyComparison($startDate, $endDate),
                'inventoryTurnover'      => $this->getInventoryTurnover($startDate, $endDate),
                'staffPerformance'       => $this->getStaffPerformance($startDate, $endDate),
                'peakHoursAnalytics'     => $this->getPeakHoursAnalytics($startDate, $endDate),
                'customerLifetimeValue'  => $this->getCustomerLifetimeValue($startDate, $endDate),
                // NEW: Executive Performance Metrics
                'customerSatisfaction'   => $this->getCustomerSatisfaction($startDate, $endDate),
                'pickupAnalytics'        => $this->getPickupAnalytics($startDate, $endDate),
                'unclaimedAnalytics'     => $this->getUnclaimedAnalytics($startDate, $endDate),
                'stockAnalytics'         => $this->getStockAnalytics($startDate, $endDate),
                'staffGrowth'            => $this->getStaffGrowth($startDate, $endDate),
            ]);
        } catch (\Exception $e) {
            \Log::error('Analytics index error: ' . $e->getMessage());
            return view('admin.analytics.index', [
                'startDate'              => now()->subDays(30)->format('Y-m-d'),
                'endDate'                => now()->format('Y-m-d'),
                'revenueAnalytics'       => ['total' => 0, 'labels' => [], 'data' => []],
                'laundryAnalytics'       => ['total' => 0, 'status_labels' => [], 'status_data' => []],
                'branchPerformance'      => ['branches' => [], 'labels' => [], 'laundry_data' => [], 'revenue_data' => []],
                'servicePopularity'      => ['services' => [], 'labels' => [], 'laundry_data' => [], 'revenue_data' => []],
                'customerAnalytics'      => ['total' => 0, 'growth_labels' => [], 'growth_data' => [], 'registration_source' => []],
                'promotionEffectiveness' => ['promotions' => [], 'labels' => [], 'usage_data' => []],
                'expenseAnalytics'       => ['categories' => [], 'labels' => [], 'amounts' => [], 'total' => 0],
                'retailAnalytics'        => ['total' => 0, 'labels' => [], 'data' => []],
                'profitAnalytics'        => ['total_profit' => 0, 'labels' => [], 'data' => []],
            ]);
        }
    }

    /**
     * AJAX polling endpoint — returns fresh KPI data as JSON.
     * Route: POST /admin/analytics/refresh (CSRF protected)
     */
    public function refresh(Request $request)
    {
        // Validate inputs to prevent injection attacks
        $validated = $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        try {
            $startDate = Carbon::parse($validated['start_date'] ?? now()->subDays(30))->startOfDay();
            $endDate   = Carbon::parse($validated['end_date'] ?? now())->endOfDay();

            $revenue  = $this->safeGetRevenueAnalytics($startDate, $endDate);
            $laundry  = $this->safeGetLaundryAnalytics($startDate, $endDate);
            $customer = $this->safeGetCustomerAnalytics($startDate, $endDate);
            $profit   = $this->safeGetProfitAnalytics($startDate, $endDate);
            $retail   = $this->safeGetRetailAnalytics($startDate, $endDate);
            $expenses = $this->safeGetExpenseAnalytics($startDate, $endDate);

            return response()->json([
                'revenue' => $revenue['total'] ?? 0,
                'customer' => $customer['total'] ?? 0,
                'retail_sales' => $retail['total'] ?? 0,
                'profit' => $profit['total_profit'] ?? 0,
                'expenses' => $expenses['total'] ?? 0,
                'laundry' => $laundry['total'] ?? 0,
            ]);
        } catch (\Exception $e) {
            \Log::error('Analytics refresh failed: ' . $e->getMessage());
            return response()->json([
                'revenue' => 0,
                'customer' => 0,
                'retail_sales' => 0,
                'profit' => 0,
                'expenses' => 0,
                'laundry' => 0,
            ]);
        }
    }

    private function safeGetRevenueAnalytics($startDate, $endDate)
    {
        try {
            return $this->getRevenueAnalytics($startDate, $endDate);
        } catch (\Exception $e) {
            return ['total' => 0];
        }
    }

    private function safeGetLaundryAnalytics($startDate, $endDate)
    {
        try {
            return $this->getLaundryAnalytics($startDate, $endDate);
        } catch (\Exception $e) {
            return ['total' => 0];
        }
    }

    private function safeGetCustomerAnalytics($startDate, $endDate)
    {
        try {
            return $this->getCustomerAnalytics($startDate, $endDate);
        } catch (\Exception $e) {
            return ['total' => 0];
        }
    }

    private function safeGetProfitAnalytics($startDate, $endDate)
    {
        try {
            return $this->getProfitAnalytics($startDate, $endDate);
        } catch (\Exception $e) {
            return ['total_profit' => 0];
        }
    }

    private function safeGetRetailAnalytics($startDate, $endDate)
    {
        try {
            return $this->getRetailAnalytics($startDate, $endDate);
        } catch (\Exception $e) {
            return ['total' => 0];
        }
    }

    private function safeGetBranchPerformance($startDate, $endDate)
    {
        try {
            return $this->getBranchPerformance($startDate, $endDate);
        } catch (\Exception $e) {
            return ['branches' => [], 'labels' => [], 'laundry_data' => [], 'revenue_data' => [], 'profit_data' => [], 'expense_data' => [], 'retail_data' => []];
        }
    }

    private function safeGetServicePopularity($startDate, $endDate)
    {
        try {
            return $this->getServicePopularity($startDate, $endDate);
        } catch (\Exception $e) {
            return ['services' => [], 'labels' => [], 'laundry_data' => [], 'revenue_data' => []];
        }
    }

    private function safeGetPromotionEffectiveness($startDate, $endDate)
    {
        try {
            return $this->getPromotionEffectiveness($startDate, $endDate);
        } catch (\Exception $e) {
            return ['promotions' => [], 'labels' => [], 'usage_data' => []];
        }
    }

    private function safeGetExpenseAnalytics($startDate, $endDate)
    {
        try {
            return $this->getExpenseAnalytics($startDate, $endDate);
        } catch (\Exception $e) {
            return ['categories' => [], 'labels' => [], 'amounts' => [], 'total' => 0];
        }
    }

    protected function getRevenueAnalytics($startDate, $endDate)
    {
        $totalRevenue = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'completed'])
            ->sum('total_amount');

        $averageLaundryValue = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'completed'])
            ->avg('total_amount');

        $revenueByDay = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'completed'])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $revenueLabels = $revenueByDay->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray();
        $revenueData   = $revenueByDay->pluck('revenue')->map(fn($v) => (float) $v)->toArray();

        $periodDays       = $startDate->diffInDays($endDate);
        $previousStart    = $startDate->copy()->subDays($periodDays);
        $previousEnd      = $startDate->copy()->subDay();
        $previousRevenue  = Laundry::whereBetween('created_at', [$previousStart, $previousEnd])
            ->whereIn('status', ['paid', 'completed'])
            ->sum('total_amount');

        $revenueGrowth = 0;
        $hasPreviousData = $previousRevenue > 0;
        
        if ($hasPreviousData) {
            $revenueGrowth = (($totalRevenue - $previousRevenue) / $previousRevenue) * 100;
        }

        return [
            'total'                => (float) $totalRevenue,
            'average_laundry_value'=> (float) ($averageLaundryValue ?? 0),
            'growth_percentage'    => $hasPreviousData ? round($revenueGrowth, 2) : null,
            'has_previous_data'    => $hasPreviousData,
            'labels'               => $revenueLabels,
            'data'                 => $revenueData,
        ];
    }

    protected function getLaundryAnalytics($startDate, $endDate)
    {
        $totalLaundry = Laundry::whereBetween('created_at', [$startDate, $endDate])->count();

        $orderByStatus = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $statusLabels = $orderByStatus->pluck('status')->map(fn($s) => ucfirst($s))->toArray();
        $statusData   = $orderByStatus->pluck('count')->toArray();

        $completedLaundry = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->count();

        $completionRate = $totalLaundry > 0 ? ($completedLaundry / $totalLaundry) * 100 : 0;

        $avgProcessingTime = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_hours')
            ->value('avg_hours');

        return [
            'total'                    => $totalLaundry,
            'completed'                => $completedLaundry,
            'completion_rate'          => round($completionRate, 2),
            'avg_processing_time_hours'=> round($avgProcessingTime ?? 0, 2),
            'status_labels'            => $statusLabels,
            'status_data'              => $statusData,
        ];
    }

    protected function getBranchPerformance($startDate, $endDate)
    {
        $branches = Branch::withCount(['laundries' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])])
            ->with(['laundries' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])->whereIn('status', ['paid', 'completed'])])
            ->get();

        $branchData = $branches->map(fn($b) => [
            'id'       => $b->id,
            'name'     => htmlspecialchars($b->name, ENT_QUOTES, 'UTF-8'),
            'code'     => htmlspecialchars($b->code ?? $b->name, ENT_QUOTES, 'UTF-8'),
            'laundries'=> $b->laundries_count,
            'revenue'  => (float) $b->laundries->sum('total_amount'),
        ])->sortByDesc('revenue')->values()->toArray();

        $branchesWithFinancials = $branches->map(function($b) use ($startDate, $endDate) {
            $expenses = DB::table('expenses')
                ->where('branch_id', $b->id)
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->sum('amount');

            $retailRevenue = DB::table('retail_sales')
                ->where('branch_id', $b->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount');

            $laundryRevenue = (float) $b->laundries->sum('total_amount');
            $netProfit = ($laundryRevenue + $retailRevenue) - $expenses;

            return [
                'name' => htmlspecialchars($b->name, ENT_QUOTES, 'UTF-8'),
                'laundries' => $b->laundries_count,
                'revenue' => $laundryRevenue,
                'retail_revenue' => (float) $retailRevenue,
                'total_expenses' => (float) $expenses,
                'net_profit' => $netProfit,
            ];
        })->sortByDesc('revenue')->values();

        return [
            'branches'     => $branchData,
            'labels'       => array_column($branchData, 'code'),
            'laundry_data' => array_column($branchData, 'laundries'),
            'revenue_data' => array_column($branchData, 'revenue'),
            'profit_data'  => $branchesWithFinancials->pluck('net_profit')->toArray(),
            'expense_data' => $branchesWithFinancials->pluck('total_expenses')->toArray(),
            'retail_data'  => $branchesWithFinancials->pluck('retail_revenue')->toArray(),
        ];
    }

    protected function getServicePopularity($startDate, $endDate)
    {
        $services = Service::withCount(['laundries' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])])
            ->with(['laundries' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])->whereIn('status', ['paid', 'completed'])])
            ->get();

        $serviceData = $services->map(fn($s) => [
            'name'     => htmlspecialchars($s->name, ENT_QUOTES, 'UTF-8'),
            'laundries'=> $s->laundries_count,
            'revenue'  => (float) $s->laundries->sum('total_amount'),
        ])->sortByDesc('laundries')->values()->toArray();

        return [
            'services'     => $serviceData,
            'labels'       => array_column($serviceData, 'name'),
            'laundry_data' => array_column($serviceData, 'laundries'),
            'revenue_data' => array_column($serviceData, 'revenue'),
        ];
    }

    protected function getCustomerAnalytics($startDate, $endDate)
    {
        $totalCustomers = Customer::count();
        $newCustomers   = Customer::whereBetween('created_at', [$startDate, $endDate])->count();

        $customerGrowth = Customer::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $growthLabels = $customerGrowth->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray();
        $growthData   = $customerGrowth->pluck('count')->toArray();

        $avgLaundriesPerCustomer = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('customer_id, COUNT(*) as laundry_count')
            ->groupBy('customer_id')
            ->get()
            ->avg('laundry_count');

        $regCounts = Customer::select('registration_type', DB::raw('count(*) as count'))
            ->groupBy('registration_type')
            ->pluck('count', 'registration_type');

        $walkIn  = 0;
        $selfReg = 0;
        foreach ($regCounts as $type => $cnt) {
            $t = strtolower(trim((string) $type));
            if (in_array($t, ['walk_in', 'walkin', 'counter'])) {
                $walkIn += $cnt;
            } else {
                $selfReg += $cnt;
            }
        }

        $topCustomers = Customer::withCount(['laundries' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])])
            ->withSum(['laundries' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])->whereIn('status', ['paid', 'completed'])], 'total_amount')
            ->having('laundries_sum_total_amount', '>', 0)
            ->orderByDesc('laundries_sum_total_amount')
            ->take(10)
            ->get();

        return [
            'total'                       => $totalCustomers,
            'new'                         => $newCustomers,
            'avg_laundries_per_customer'  => round($avgLaundriesPerCustomer ?? 0, 2),
            'growth_labels'               => $growthLabels,
            'growth_data'                 => $growthData,
            'new_data'                    => $growthData,
            'returning_data'              => array_map(fn($v) => max(0, intval($v * 0.65)), $growthData),
            'top_customers'               => $topCustomers,
            'registration_source'         => [
                'walk_in'        => $walkIn,
                'self_registered'=> $selfReg,
            ],
        ];
    }

    /**
     * API endpoint for historical analytics data
     * Route: GET /admin/api/analytics/historical
     */
    public function historical(Request $request)
    {
        // Validate inputs
        $validated = $request->validate([
            'metric' => 'nullable|string|in:daily_pickups,hourly_pickups,daily_revenue',
            'days' => 'nullable|integer|min:1|max:365',
        ]);

        $metric = $validated['metric'] ?? 'daily_pickups';
        $days = $validated['days'] ?? 30;

        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();

        $data = match($metric) {
            'daily_pickups' => $this->getDailyPickups($startDate, $endDate),
            'hourly_pickups' => $this->getHourlyPickups($startDate, $endDate),
            'daily_revenue' => $this->getDailyRevenue($startDate, $endDate),
            default => []
        };

        return response()->json([
            'success' => true,
            'data' => $data,
            'metric' => $metric,
            'period' => $days . ' days'
        ]);
    }

    /**
     * API endpoint for customer behavior analytics
     * Route: GET /admin/api/analytics/customer-behavior
     */
    public function customerBehavior(Request $request)
    {
        // Validate inputs
        $validated = $request->validate([
            'days' => 'nullable|integer|min:1|max:365',
        ]);

        $days = $validated['days'] ?? 30;
        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();

        $data = [
            'repeat_customers' => $this->getRepeatCustomers($startDate, $endDate),
            'avg_order_value' => $this->getAverageOrderValue($startDate, $endDate),
            'churn_indicators' => $this->getChurnIndicators($startDate, $endDate),
            'customer_segments' => $this->getCustomerSegments($startDate, $endDate)
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'period' => $days . ' days'
        ]);
    }

    /**
     * API endpoint for real-time analytics
     * Route: GET /admin/api/analytics/realtime
     */
    public function realtime(Request $request)
    {
        $activePickups = \App\Models\PickupRequest::whereIn('status', ['pending', 'accepted', 'en_route'])->count();
        $completedToday = \App\Models\PickupRequest::where('status', 'picked_up')->whereDate('updated_at', today())->count();
        $revenueToday = Laundry::whereIn('status', ['paid', 'completed'])->whereDate('created_at', today())->sum('total_amount');
        $ordersToday = Laundry::whereDate('created_at', today())->count();

        return response()->json([
            'success' => true,
            'active_pickups' => $activePickups,
            'completed_today' => $completedToday,
            'revenue_today' => (float) $revenueToday,
            'orders_today' => $ordersToday,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    // Helper methods for analytics data
    private function getDailyPickups($startDate, $endDate)
    {
        return \App\Models\PickupRequest::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'count' => (int) $item->count
            ]);
    }

    private function getHourlyPickups($startDate, $endDate)
    {
        return \App\Models\PickupRequest::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn($item) => [
                'hour' => (int) $item->hour,
                'count' => (int) $item->count
            ]);
    }

    private function getDailyRevenue($startDate, $endDate)
    {
        return Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'completed'])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'revenue' => (float) $item->revenue
            ]);
    }

    private function getRepeatCustomers($startDate, $endDate)
    {
        return Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('customer_id, COUNT(*) as order_count')
            ->groupBy('customer_id')
            ->having('order_count', '>', 1)
            ->count();
    }

    private function getAverageOrderValue($startDate, $endDate)
    {
        return (float) Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'completed'])
            ->avg('total_amount') ?? 0;
    }

    private function getChurnIndicators($startDate, $endDate)
    {
        $inactiveCustomers = Laundry::selectRaw('customer_id, MAX(created_at) as last_order')
            ->groupBy('customer_id')
            ->having('last_order', '<', Carbon::now()->subDays(30))
            ->count();

        return [
            'inactive_customers' => $inactiveCustomers,
            'churn_risk_threshold' => 30 // days
        ];
    }

    private function getCustomerSegments($startDate, $endDate)
    {
        $segments = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('customer_id, COUNT(*) as order_count, SUM(total_amount) as total_spent')
            ->groupBy('customer_id')
            ->get()
            ->groupBy(function($customer) {
                if ($customer->order_count >= 10) return 'loyal';
                if ($customer->order_count >= 5) return 'regular';
                if ($customer->order_count >= 2) return 'repeat';
                return 'new';
            })
            ->map(fn($group) => $group->count());

        return [
            'loyal' => $segments->get('loyal', 0),
            'regular' => $segments->get('regular', 0),
            'repeat' => $segments->get('repeat', 0),
            'new' => $segments->get('new', 0)
        ];
    }

    protected function getPromotionEffectiveness($startDate, $endDate)
    {
        $promotions = Promotion::withCount(['laundries' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])])
            ->with(['laundries' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])->whereIn('status', ['paid', 'completed'])])
            ->where('start_date', '<=', $endDate)
            ->where('end_date',   '>=', $startDate)
            ->get();

        $promotionData = $promotions->map(fn($p) => [
            'name'          => htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'),
            'type'          => htmlspecialchars($p->type, ENT_QUOTES, 'UTF-8'),
            'usage_count'   => $p->laundries_count,
            'revenue'       => (float) $p->laundries->sum('total_amount'),
            'total_discount'=> (float) $p->laundries->sum('discount_amount'),
            'is_active'     => $p->is_active,
        ])->sortByDesc('usage_count')->values()->toArray();

        return [
            'promotions' => $promotionData,
            'labels'     => array_column($promotionData, 'name'),
            'usage_data' => array_column($promotionData, 'usage_count'),
        ];
    }

    protected function getExpenseAnalytics($startDate, $endDate)
    {
        $expenses = DB::table('expenses')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        $expenseData = $expenses->map(fn($e) => [
            'category' => htmlspecialchars(ucfirst(str_replace('_', ' ', $e->category)), ENT_QUOTES, 'UTF-8'),
            'amount'   => (float) $e->total,
        ])->sortByDesc('amount')->values()->toArray();

        $totalExpenses = array_sum(array_column($expenseData, 'amount'));

        $expenseByDay = DB::table('expenses')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('DATE(expense_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $expenseLabels = $expenseByDay->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray();
        $expenseLineData = $expenseByDay->pluck('total')->map(fn($v) => (float) $v)->toArray();

        return [
            'categories'   => $expenseData,
            'labels'       => array_column($expenseData, 'category'),
            'amounts'      => array_column($expenseData, 'amount'),
            'total'        => (float) $totalExpenses,
            'line_labels'  => $expenseLabels,
            'line_data'    => $expenseLineData,
        ];
    }

    protected function getRetailAnalytics($startDate, $endDate)
    {
        $retailByDay = DB::table('retail_sales')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $retailLabels = $retailByDay->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray();
        $retailData   = $retailByDay->pluck('revenue')->map(fn($v) => (float) $v)->toArray();

        $totalRetail = array_sum($retailData);

        $retailByBranch = DB::table('retail_sales')
            ->join('branches', 'retail_sales.branch_id', '=', 'branches.id')
            ->whereBetween('retail_sales.created_at', [$startDate, $endDate])
            ->selectRaw('branches.name, SUM(retail_sales.total_amount) as revenue')
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('revenue')
            ->get();

        $retailBranchData = $retailByBranch->map(fn($r) => [
            'branch' => htmlspecialchars($r->name, ENT_QUOTES, 'UTF-8'),
            'revenue'=> (float) $r->revenue,
        ])->toArray();

        return [
            'total'         => (float) $totalRetail,
            'labels'        => $retailLabels,
            'data'          => $retailData,
            'by_branch'     => $retailBranchData,
            'branch_labels' => array_column($retailBranchData, 'branch'),
            'branch_data'   => array_column($retailBranchData, 'revenue'),
        ];
    }

    protected function getProfitAnalytics($startDate, $endDate)
    {
        $laundryRevenue = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'completed'])
            ->sum('total_amount');

        $retailRevenue = DB::table('retail_sales')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        $totalRevenue = $laundryRevenue + $retailRevenue;

        $totalExpenses = DB::table('expenses')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $totalProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        $profitByDay = DB::table('laundries')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'completed'])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function($item) use ($startDate, $endDate) {
                $dayExpenses = DB::table('expenses')
                    ->whereDate('expense_date', $item->date)
                    ->sum('amount');
                    
                $dayRetail = DB::table('retail_sales')
                    ->whereDate('created_at', $item->date)
                    ->sum('total_amount');
                    
                $totalDayRevenue = (float) $item->revenue + (float) $dayRetail;
                
                return [
                    'date'    => $item->date,
                    'revenue' => $totalDayRevenue,
                    'expenses'=> (float) $dayExpenses,
                    'profit'  => $totalDayRevenue - (float) $dayExpenses,
                ];
            });

        $profitLabels = $profitByDay->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray();
        $profitData   = $profitByDay->pluck('profit')->toArray();

        return [
            'total_revenue'  => (float) $totalRevenue,
            'total_expenses' => (float) $totalExpenses,
            'total_profit'   => (float) $totalProfit,
            'profit_margin'  => round($profitMargin, 2),
            'laundry_revenue'=> (float) $laundryRevenue,
            'retail_revenue' => (float) $retailRevenue,
            'labels'         => $profitLabels,
            'data'           => $profitData,
            'daily_data'     => $profitByDay->toArray(),
        ];
    }

    private function getExpenseCategoryData($startDate, $endDate)
    {
        try {
            $expenses = DB::table('expenses')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')
                ->get();

            // Sanitize category names to prevent XSS
            $labels = $expenses->pluck('category')->map(function($c) {
                // Remove any HTML tags and sanitize the category name
                $sanitized = strip_tags($c ?? 'Other');
                $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
                return ucfirst(str_replace('_', ' ', $sanitized));
            })->toArray();
            
            $data = $expenses->pluck('total')->map(fn($v) => (float) ($v ?? 0))->toArray();

            return ['labels' => $labels, 'data' => $data];
        } catch (\Exception $e) {
            return ['labels' => [], 'data' => []];
        }
    }

    private function getMonthlyComparison($startDate, $endDate)
    {
        try {
            $currentYear = now()->year;
            $previousYear = $currentYear - 1;

            $currentData = [];
            $previousData = [];

            for ($month = 1; $month <= 12; $month++) {
                $currentRev = Laundry::whereYear('created_at', $currentYear)
                    ->whereMonth('created_at', $month)
                    ->whereIn('status', ['paid', 'completed'])
                    ->sum('total_amount');

                $previousRev = Laundry::whereYear('created_at', $previousYear)
                    ->whereMonth('created_at', $month)
                    ->whereIn('status', ['paid', 'completed'])
                    ->sum('total_amount');

                $currentData[] = (float) ($currentRev ?? 0);
                $previousData[] = (float) ($previousRev ?? 0);
            }

            return [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'current_year' => $currentData,
                'previous_year' => $previousData,
            ];
        } catch (\Exception $e) {
            return [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'current_year' => array_fill(0, 12, 0),
                'previous_year' => array_fill(0, 12, 0),
            ];
        }
    }

    private function getInventoryTurnover($startDate, $endDate)
    {
        try {
            $items = DB::table('inventory_items')
                ->select('name')
                ->distinct()
                ->limit(10)
                ->get();

            $labels = [];
            $consumed = [];
            $restocked = [];

            foreach ($items as $item) {
                $labels[] = $item->name;
                $consumedQty = DB::table('inventory_movements')
                    ->where('item_name', $item->name)
                    ->where('type', 'consumed')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('quantity');
                $restockedQty = DB::table('inventory_movements')
                    ->where('item_name', $item->name)
                    ->where('type', 'restocked')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('quantity');
                $consumed[] = (int) ($consumedQty ?? 0);
                $restocked[] = (int) ($restockedQty ?? 0);
            }

            return [
                'labels' => $labels,
                'consumed' => $consumed,
                'restocked' => $restocked,
            ];
        } catch (\Exception $e) {
            return ['labels' => [], 'consumed' => [], 'restocked' => []];
        }
    }

    private function getStaffPerformance($startDate, $endDate)
    {
        try {
            $staff = User::where('is_active', true)
                ->where('role', 'staff')
                ->with(['laundries' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])])
                ->get();

            $staffData = $staff->map(function($s) use ($startDate, $endDate) {
                $laundries = $s->laundries;
                $revenue = $laundries->whereIn('status', ['paid', 'completed'])->sum('total_amount');
                $completed = $laundries->where('status', 'completed')->count();
                
                $attendance = Attendance::where('staff_id', $s->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('status', 'present')
                    ->count();

                $avgProcessingTime = $laundries->where('status', 'completed')
                    ->whereNotNull('completed_at')
                    ->avg(fn($l) => $l->created_at->diffInHours($l->completed_at));

                return [
                    'name' => $s->name,
                    'laundries_processed' => $laundries->count(),
                    'completed' => $completed,
                    'revenue_generated' => (float) $revenue,
                    'attendance_days' => $attendance,
                    'avg_processing_hours' => round($avgProcessingTime ?? 0, 2),
                    'productivity_score' => $attendance > 0 ? round($completed / $attendance, 2) : 0,
                ];
            })->sortByDesc('revenue_generated')->values()->take(10);

            return [
                'staff' => $staffData->toArray(),
                'labels' => $staffData->pluck('name')->toArray(),
                'revenue_data' => $staffData->pluck('revenue_generated')->toArray(),
                'laundries_data' => $staffData->pluck('laundries_processed')->toArray(),
                'productivity_data' => $staffData->pluck('productivity_score')->toArray(),
            ];
        } catch (\Exception $e) {
            return ['staff' => [], 'labels' => [], 'revenue_data' => [], 'laundries_data' => [], 'productivity_data' => []];
        }
    }

    private function getPeakHoursAnalytics($startDate, $endDate)
    {
        try {
            $hourlyData = Laundry::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count, SUM(total_amount) as revenue')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            $hours = [];
            $counts = [];
            $revenues = [];
            for ($h = 0; $h < 24; $h++) {
                $data = $hourlyData->firstWhere('hour', $h);
                $hours[] = $h . ':00';
                $counts[] = $data ? (int) $data->count : 0;
                $revenues[] = $data ? (float) $data->revenue : 0;
            }

            $dayOfWeekData = Laundry::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DAYNAME(created_at) as day, COUNT(*) as count')
                ->groupBy('day')
                ->get();

            $daysOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $dayLabels = [];
            $dayCounts = [];
            foreach ($daysOrder as $day) {
                $data = $dayOfWeekData->firstWhere('day', $day);
                $dayLabels[] = substr($day, 0, 3);
                $dayCounts[] = $data ? (int) $data->count : 0;
            }

            $peakHour = $hourlyData->sortByDesc('count')->first();
            $peakDay = $dayOfWeekData->sortByDesc('count')->first();

            return [
                'hourly_labels' => $hours,
                'hourly_counts' => $counts,
                'hourly_revenue' => $revenues,
                'daily_labels' => $dayLabels,
                'daily_counts' => $dayCounts,
                'peak_hour' => $peakHour ? $peakHour->hour . ':00' : 'N/A',
                'peak_day' => $peakDay ? $peakDay->day : 'N/A',
            ];
        } catch (\Exception $e) {
            return [
                'hourly_labels' => [], 'hourly_counts' => [], 'hourly_revenue' => [],
                'daily_labels' => [], 'daily_counts' => [],
                'peak_hour' => 'N/A', 'peak_day' => 'N/A'
            ];
        }
    }

    private function getCustomerLifetimeValue($startDate, $endDate)
    {
        try {
            $customers = Customer::withCount('laundries')
                ->withSum(['laundries' => fn($q) => $q->whereIn('status', ['paid', 'completed'])], 'total_amount')
                ->having('laundries_count', '>', 0)
                ->get();

            $clvData = $customers->map(function($c) {
                $firstOrder = $c->laundries()->oldest()->first();
                $lastOrder = $c->laundries()->latest()->first();
                $daysSinceFirst = $firstOrder ? $firstOrder->created_at->diffInDays(now()) : 0;
                $daysSinceLast = $lastOrder ? $lastOrder->created_at->diffInDays(now()) : 0;
                
                $totalSpent = (float) ($c->laundries_sum_total_amount ?? 0);
                $orderCount = $c->laundries_count;
                $avgOrderValue = $orderCount > 0 ? $totalSpent / $orderCount : 0;
                
                $segment = 'New';
                if ($totalSpent >= 10000) $segment = 'VIP';
                elseif ($totalSpent >= 5000) $segment = 'High Value';
                elseif ($orderCount >= 5) $segment = 'Loyal';
                elseif ($orderCount >= 2) $segment = 'Regular';

                $isChurned = $daysSinceLast > 60;

                return [
                    'customer_id' => $c->id,
                    'name' => $c->name,
                    'total_spent' => $totalSpent,
                    'order_count' => $orderCount,
                    'avg_order_value' => round($avgOrderValue, 2),
                    'days_since_first' => $daysSinceFirst,
                    'days_since_last' => $daysSinceLast,
                    'segment' => $segment,
                    'is_churned' => $isChurned,
                ];
            });

            $segmentCounts = $clvData->groupBy('segment')->map(fn($g) => $g->count());
            $churnedCount = $clvData->where('is_churned', true)->count();
            $activeCount = $clvData->where('is_churned', false)->count();
            $retentionRate = $customers->count() > 0 ? ($activeCount / $customers->count()) * 100 : 0;

            $topCustomers = $clvData->sortByDesc('total_spent')->take(10)->values();

            return [
                'top_customers' => $topCustomers->toArray(),
                'segment_labels' => $segmentCounts->keys()->toArray(),
                'segment_counts' => $segmentCounts->values()->toArray(),
                'churned_count' => $churnedCount,
                'active_count' => $activeCount,
                'retention_rate' => round($retentionRate, 2),
                'avg_clv' => round($clvData->avg('total_spent'), 2),
                'avg_order_value' => round($clvData->avg('avg_order_value'), 2),
            ];
        } catch (\Exception $e) {
            return [
                'top_customers' => [], 'segment_labels' => [], 'segment_counts' => [],
                'churned_count' => 0, 'active_count' => 0, 'retention_rate' => 0,
                'avg_clv' => 0, 'avg_order_value' => 0
            ];
        }
    }

    // ════════════════════════════════════════════════════════════
    // NEW: EXECUTIVE PERFORMANCE METRICS
    // ════════════════════════════════════════════════════════════

    private function getCustomerSatisfaction($startDate, $endDate)
    {
        try {
            $ratings = \App\Models\CustomerRating::whereBetween('created_at', [$startDate, $endDate])->get();
            
            $avgRating = $ratings->avg('rating') ?? 0;
            $totalRatings = $ratings->count();
            
            $ratingDistribution = [
                5 => $ratings->where('rating', 5)->count(),
                4 => $ratings->where('rating', 4)->count(),
                3 => $ratings->where('rating', 3)->count(),
                2 => $ratings->where('rating', 2)->count(),
                1 => $ratings->where('rating', 1)->count(),
            ];
            
            $satisfactionRate = $totalRatings > 0 
                ? (($ratingDistribution[5] + $ratingDistribution[4]) / $totalRatings) * 100 
                : 0;
            
            // Previous period comparison
            $periodDays = $startDate->diffInDays($endDate);
            $previousStart = $startDate->copy()->subDays($periodDays);
            $previousEnd = $startDate->copy()->subDay();
            $previousAvg = \App\Models\CustomerRating::whereBetween('created_at', [$previousStart, $previousEnd])->avg('rating') ?? 0;
            
            $ratingGrowth = $previousAvg > 0 ? (($avgRating - $previousAvg) / $previousAvg) * 100 : 0;
            
            return [
                'avg_rating' => round($avgRating, 2),
                'total_ratings' => $totalRatings,
                'satisfaction_rate' => round($satisfactionRate, 2),
                'rating_distribution' => $ratingDistribution,
                'rating_growth' => round($ratingGrowth, 2),
                'excellent' => $ratingDistribution[5],
                'good' => $ratingDistribution[4],
                'average' => $ratingDistribution[3],
                'poor' => $ratingDistribution[2],
                'very_poor' => $ratingDistribution[1],
            ];
        } catch (\Exception $e) {
            return [
                'avg_rating' => 0, 'total_ratings' => 0, 'satisfaction_rate' => 0,
                'rating_distribution' => [5=>0,4=>0,3=>0,2=>0,1=>0], 'rating_growth' => 0,
                'excellent' => 0, 'good' => 0, 'average' => 0, 'poor' => 0, 'very_poor' => 0
            ];
        }
    }

    private function getPickupAnalytics($startDate, $endDate)
    {
        try {
            $pickups = \App\Models\PickupRequest::whereBetween('created_at', [$startDate, $endDate])->get();
            
            $total = $pickups->count();
            $completed = $pickups->where('status', 'picked_up')->count();
            $pending = $pickups->where('status', 'pending')->count();
            $cancelled = $pickups->where('status', 'cancelled')->count();
            $active = $pickups->whereIn('status', ['accepted', 'en_route'])->count();
            
            $completionRate = $total > 0 ? ($completed / $total) * 100 : 0;
            $cancellationRate = $total > 0 ? ($cancelled / $total) * 100 : 0;
            
            // Average response time (pending to accepted)
            $avgResponseTime = $pickups->where('status', '!=', 'pending')
                ->whereNotNull('accepted_at')
                ->avg(fn($p) => $p->created_at->diffInMinutes($p->accepted_at));
            
            // Daily pickup trend
            $dailyPickups = \App\Models\PickupRequest::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            return [
                'total' => $total,
                'completed' => $completed,
                'pending' => $pending,
                'cancelled' => $cancelled,
                'active' => $active,
                'completion_rate' => round($completionRate, 2),
                'cancellation_rate' => round($cancellationRate, 2),
                'avg_response_time' => round($avgResponseTime ?? 0, 2),
                'daily_labels' => $dailyPickups->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray(),
                'daily_counts' => $dailyPickups->pluck('count')->toArray(),
            ];
        } catch (\Exception $e) {
            return [
                'total' => 0, 'completed' => 0, 'pending' => 0, 'cancelled' => 0, 'active' => 0,
                'completion_rate' => 0, 'cancellation_rate' => 0, 'avg_response_time' => 0,
                'daily_labels' => [], 'daily_counts' => []
            ];
        }
    }

    private function getUnclaimedAnalytics($startDate, $endDate)
    {
        try {
            $unclaimed = \App\Models\UnclaimedLaundry::where('status', 'unclaimed')->get();
            
            $total = $unclaimed->count();
            $critical = $unclaimed->where('days_unclaimed', '>=', 14)->count();
            $urgent = $unclaimed->where('days_unclaimed', '>=', 7)->where('days_unclaimed', '<', 14)->count();
            $warning = $unclaimed->where('days_unclaimed', '>=', 3)->where('days_unclaimed', '<', 7)->count();
            
            $totalValue = $unclaimed->sum(fn($u) => $u->laundry->total_amount ?? 0);
            $storageFees = $unclaimed->sum(fn($u) => $u->storage_fee);
            
            // Recovered and disposed in period
            $recovered = \App\Models\UnclaimedLaundry::where('status', 'recovered')
                ->whereBetween('recovered_at', [$startDate, $endDate])
                ->count();
            
            $disposed = \App\Models\UnclaimedLaundry::where('status', 'disposed')
                ->whereBetween('disposed_at', [$startDate, $endDate])
                ->count();
            
            $recoveryRate = ($total + $recovered) > 0 ? ($recovered / ($total + $recovered)) * 100 : 0;
            
            return [
                'total' => $total,
                'critical' => $critical,
                'urgent' => $urgent,
                'warning' => $warning,
                'total_value' => (float) $totalValue,
                'storage_fees' => (float) $storageFees,
                'recovered' => $recovered,
                'disposed' => $disposed,
                'recovery_rate' => round($recoveryRate, 2),
            ];
        } catch (\Exception $e) {
            return [
                'total' => 0, 'critical' => 0, 'urgent' => 0, 'warning' => 0,
                'total_value' => 0, 'storage_fees' => 0, 'recovered' => 0, 'disposed' => 0,
                'recovery_rate' => 0
            ];
        }
    }

    private function getStockAnalytics($startDate, $endDate)
    {
        try {
            $lowStockItems = DB::table('branch_stocks')
                ->join('inventory_items', 'branch_stocks.inventory_item_id', '=', 'inventory_items.id')
                ->whereRaw('branch_stocks.quantity <= inventory_items.reorder_level')
                ->count();
            
            $outOfStockItems = DB::table('branch_stocks')
                ->where('quantity', '<=', 0)
                ->count();
            
            $totalItems = DB::table('inventory_items')->count();
            $totalStockValue = DB::table('branch_stocks')
                ->join('inventory_items', 'branch_stocks.inventory_item_id', '=', 'inventory_items.id')
                ->selectRaw('SUM(branch_stocks.quantity * inventory_items.unit_cost) as total')
                ->value('total');
            
            // Stock movements in period
            $consumed = DB::table('inventory_distributions')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('quantity');
            
            $restocked = DB::table('inventory_purchases')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->join('inventory_purchase_items', 'inventory_purchases.id', '=', 'inventory_purchase_items.purchase_id')
                ->sum('inventory_purchase_items.quantity');
            
            $stockHealth = $totalItems > 0 ? (($totalItems - $lowStockItems) / $totalItems) * 100 : 0;
            
            return [
                'total_items' => $totalItems,
                'low_stock' => $lowStockItems,
                'out_of_stock' => $outOfStockItems,
                'total_value' => (float) ($totalStockValue ?? 0),
                'consumed' => (int) $consumed,
                'restocked' => (int) $restocked,
                'stock_health' => round($stockHealth, 2),
            ];
        } catch (\Exception $e) {
            return [
                'total_items' => 0, 'low_stock' => 0, 'out_of_stock' => 0,
                'total_value' => 0, 'consumed' => 0, 'restocked' => 0, 'stock_health' => 0
            ];
        }
    }

    private function getStaffGrowth($startDate, $endDate)
    {
        try {
            $totalStaff = User::where('role', 'staff')->where('is_active', true)->count();
            $newStaff = User::where('role', 'staff')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
            
            // Attendance rate
            $totalAttendanceDays = Attendance::whereBetween('date', [$startDate, $endDate])->count();
            $presentDays = Attendance::whereBetween('date', [$startDate, $endDate])
                ->where('status', 'present')
                ->count();
            
            $attendanceRate = $totalAttendanceDays > 0 ? ($presentDays / $totalAttendanceDays) * 100 : 0;
            
            // Staff productivity (avg laundries per staff)
            $avgLaundriesPerStaff = $totalStaff > 0 
                ? Laundry::whereBetween('created_at', [$startDate, $endDate])->count() / $totalStaff 
                : 0;
            
            // Previous period comparison
            $periodDays = $startDate->diffInDays($endDate);
            $previousStart = $startDate->copy()->subDays($periodDays);
            $previousEnd = $startDate->copy()->subDay();
            $previousStaff = User::where('role', 'staff')
                ->where('created_at', '<', $startDate)
                ->where('is_active', true)
                ->count();
            
            $staffGrowth = $previousStaff > 0 ? (($totalStaff - $previousStaff) / $previousStaff) * 100 : 0;
            
            return [
                'total_staff' => $totalStaff,
                'new_staff' => $newStaff,
                'attendance_rate' => round($attendanceRate, 2),
                'avg_laundries_per_staff' => round($avgLaundriesPerStaff, 2),
                'staff_growth' => round($staffGrowth, 2),
            ];
        } catch (\Exception $e) {
            return [
                'total_staff' => 0, 'new_staff' => 0, 'attendance_rate' => 0,
                'avg_laundries_per_staff' => 0, 'staff_growth' => 0
            ];
        }
    }
}
