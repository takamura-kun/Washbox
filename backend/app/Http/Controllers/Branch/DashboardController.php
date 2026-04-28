<?php

namespace App\Http\Controllers\Branch;

use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Laundry;
use App\Models\Service;
use App\Models\InventoryItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\PickupRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $branch = Auth::guard('branch')->user();

        if (!$branch) {
            return redirect()->route('branch.login')
                ->with('error', 'Please login to access the dashboard.');
        }

        $branchId  = $branch->id;
        $dateRange = $request->get('date_range', 'last_30_days');
        $dates     = $this->getDateRange($dateRange);
        $startDate = $dates['start'];
        $endDate   = $dates['end'];

        // Services & addons for carousel
        $services = Service::withCount('laundries')
            ->where('is_active', true)
            ->orderBy('name')
            ->take(8)
            ->get();

        // Get inventory items used as add-ons
        $addons = InventoryItem::whereHas('laundries')
            ->withCount('laundries')
            ->orderBy('name')
            ->take(4)
            ->get();

        $data = [
            // Customer summary (top-level for quick KPI display)
            'total_customers'       => Customer::count(),
            'new_customers_today'   => Customer::whereDate('created_at', today())->count(),
            'new_customers_week'    => Customer::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'new_customers_month'   => Customer::whereMonth('created_at', now()->month)->count(),

            // KPIs
            'kpis'     => $this->getKPIs($startDate, $endDate, $branchId),

            // Revenue metrics
            'revenue'  => $this->getRevenueMetrics($startDate, $endDate, $branchId),

            // Laundry metrics
            'laundries' => $this->getLaundryMetrics($startDate, $endDate, $branchId),

            // Customer metrics
            'customers' => $this->getCustomerMetrics($startDate, $endDate, $branchId),

            // Pickup metrics
            'pickups'   => $this->getPickupMetrics($branchId),

            // Pickup locations for map
            'pickupLocations' => PickupRequest::with('customer:id,name,phone')
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereIn('status', ['pending', 'accepted', 'en_route'])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get(),

            // Unclaimed laundry
            'unclaimed' => $this->getUnclaimedLaundry($branchId),

            // Branch performance (staff's branch only)
            'branchPerformance'    => $this->getBranchPerformance($startDate, $endDate, $branchId),
            'allBranchesPerformance' => $this->getAllBranchesPerformance($startDate, $endDate),

            // Pipeline
            'pipeline' => $this->getLaundryPipeline($branchId),

            // Recent laundries
            'recent_laundries' => $this->getRecentLaundries($branchId, 10),

            // Alerts
            'alerts' => $this->getAlerts($branchId),

            // Charts data
            'charts' => $this->getChartsData($startDate, $endDate, $branchId),

            // Weekly / Monthly / Yearly trend
            'weeklyPerformance' => $this->getWeeklyPerformance($branchId),
            'monthlyTrend'      => $this->getMonthlyTrend($branchId),
            'yearlyTrend'       => $this->getYearlyTrend($branchId),

            // Top services
            'topServices' => $this->getTopServices($startDate, $endDate, $branchId),

            // Payment method distribution
            'paymentMethods' => $this->getPaymentMethodDistribution($startDate, $endDate, $branchId),

            // All branches with coordinates for map
            'allBranches' => Branch::select('id', 'name', 'address', 'phone', 'latitude', 'longitude')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('is_active', true)
                ->get()
                ->map(fn($b) => [
                    'id'        => $b->id,
                    'name'      => $b->name,
                    'address'   => $b->address,
                    'phone'     => $b->phone,
                    'latitude'  => (float) $b->latitude,
                    'longitude' => (float) $b->longitude,
                ])
                ->values()
                ->toArray(),

            // Filters
            'branchOptions'   => Branch::where('id', $branchId)->get(),
            'current_filters' => [
                'date_range' => $dateRange,
                'branch_id'  => $branchId,
            ],

            // Services and Addons for carousel
            'services' => $services,
            'addons'   => $addons,

            // Retail data
            'retail_sales_today' => $this->getRetailSalesToday($branchId),
            'retail_top_products' => $this->getTopRetailProducts($branchId),
            'retail_low_stock' => $this->getLowStockItems($branchId),
            'retail_products' => $this->getRetailProducts($branchId),

            // Inventory items for sidebar
            'inventory_items' => $this->getInventoryItems($branchId),

            // Inventory count
            'inventory_count' => InventoryItem::active()->count(),

            // Top customers
            'top_customers' => $this->getTopCustomers($branchId),

            // Financial Analytics
            'financial_analytics' => $this->getFinancialAnalytics($branchId),

            // Service Quality Metrics
            'service_quality' => $this->getServiceQualityMetrics($branchId),
        ];

        return view('branch.dashboard', $data);
    }

    // ----------------------------------------------------------------
    // KPIs
    // ----------------------------------------------------------------
    private function getKPIs($startDate, $endDate, $branchId = null)
    {
        // Today
        $todayRevenue = Laundry::whereDate('created_at', today())
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount')
            + \App\Models\RetailSale::whereDate('created_at', today())
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        $yesterdayRevenue = Laundry::whereDate('created_at', today()->subDay())
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount')
            + \App\Models\RetailSale::whereDate('created_at', today()->subDay())
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        // Weekly
        $weeklyRevenue = Laundry::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount')
            + \App\Models\RetailSale::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        $lastWeekRevenue = Laundry::whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount')
            + \App\Models\RetailSale::whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        // Monthly
        $monthlyRevenue = Laundry::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount')
            + \App\Models\RetailSale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        $lastMonthRevenue = Laundry::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount')
            + \App\Models\RetailSale::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        // Yearly
        $yearlyRevenue = Laundry::whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount')
            + \App\Models\RetailSale::whereYear('created_at', now()->year)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        $lastYearRevenue = Laundry::whereYear('created_at', now()->subYear()->year)
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount')
            + \App\Models\RetailSale::whereYear('created_at', now()->subYear()->year)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        // Customers
        $totalCustomers     = Customer::count();
        $newCustomersToday  = Customer::whereDate('created_at', today())->count();
        $newCustomersWeek   = Customer::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $newCustomersMonth  = Customer::whereMonth('created_at', now()->month)->count();

        // Average order value
        $avgOrderValue = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->avg('total_amount') ?? 0;

        return [
            'today_revenue' => [
                'value'  => $todayRevenue,
                'change' => $yesterdayRevenue > 0
                    ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100
                    : 0,
                'vs'     => 'yesterday',
                'yesterday' => $yesterdayRevenue,
            ],
            'weekly_revenue' => [
                'value'  => $weeklyRevenue,
                'change' => $lastWeekRevenue > 0
                    ? (($weeklyRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100
                    : 0,
                'vs'     => 'last week',
            ],
            'monthly_revenue' => [
                'value'  => $monthlyRevenue,
                'change' => $lastMonthRevenue > 0
                    ? (($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
                    : 0,
                'vs'     => 'last month',
            ],
            'yearly_revenue' => [
                'value'  => $yearlyRevenue,
                'change' => $lastYearRevenue > 0
                    ? (($yearlyRevenue - $lastYearRevenue) / $lastYearRevenue) * 100
                    : 0,
                'vs'     => 'last year',
            ],
            'avg_order_value' => $avgOrderValue,
            'active_laundries' => [
                'value' => Laundry::whereIn('status', ['received', 'processing', 'ready', 'paid'])
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->count(),
            ],
            'ready_for_pickup' => [
                'value'        => Laundry::where('status', 'ready')
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->count(),
                'avg_wait_days' => Laundry::where('status', 'ready')
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->selectRaw('AVG(DATEDIFF(NOW(), updated_at)) as avg_days')
                    ->value('avg_days') ?? 0,
            ],
            'completed_today' => [
                'value' => Laundry::whereDate('updated_at', today())
                    ->where('status', 'completed')
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->count(),
            ],
            'total_customers' => [
                'value'         => $totalCustomers,
                'today'         => $newCustomersToday,
                'week'          => $newCustomersWeek,
                'month'         => $newCustomersMonth,
                'new_this_month'=> $newCustomersMonth,
            ],
            'pending_pickups' => [
                'value' => PickupRequest::where('status', 'pending')
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->count(),
            ],
        ];
    }

    // ----------------------------------------------------------------
    // All Branches Performance
    // ----------------------------------------------------------------
    private function getAllBranchesPerformance($startDate, $endDate)
    {
        $branches = Branch::where('is_active', true)
            ->withCount([
                'laundries as laundries_count' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]),
            ])
            ->withSum([
                'laundries as revenue' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])->where('status', 'completed'),
            ], 'total_amount')
            ->withAvg([
                'laundries as avg_order_value' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])->where('status', 'completed'),
            ], 'total_amount')
            ->orderByDesc('revenue')
            ->get();

        $totalRevenue   = $branches->sum('revenue')         ?: 1;
        $totalLaundries = $branches->sum('laundries_count') ?: 1;

        return $branches->map(fn($b) => [
            'id'              => $b->id,
            'name'            => $b->name,
            'laundries_count' => $b->laundries_count ?? 0,
            'revenue'         => $b->revenue ?? 0,
            'avg_order_value' => round($b->avg_order_value ?? 0, 2),
            'percentage'      => round(($b->revenue / $totalRevenue) * 100, 1),
            'market_share'    => round(($b->laundries_count / $totalLaundries) * 100, 1),
        ]);
    }

    // ----------------------------------------------------------------
    // Weekly Performance
    // ----------------------------------------------------------------
    private function getWeeklyPerformance($branchId = null)
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek   = now()->endOfWeek();

        $laundryResults = Laundry::whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('DATE(created_at) as date, SUM(CASE WHEN status="completed" THEN total_amount ELSE 0 END) as revenue, COUNT(*) as count')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $retailResults = \App\Models\RetailSale::whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $dailyData = [];
        $current   = clone $startOfWeek;
        while ($current <= $endOfWeek) {
            $dateStr     = $current->format('Y-m-d');
            $laundryRow  = $laundryResults[$dateStr] ?? null;
            $retailRow   = $retailResults[$dateStr] ?? null;
            $laundryRev  = $laundryRow ? (float) $laundryRow->revenue : 0;
            $retailRev   = $retailRow ? (float) $retailRow->revenue : 0;
            $dailyData[] = [
                'day'       => $current->format('l'),
                'short_day' => $current->format('D'),
                'revenue'   => $laundryRev + $retailRev,
                'count'     => $laundryRow ? (int) $laundryRow->count : 0,
                'date'      => $dateStr,
            ];
            $current->addDay();
        }
        return $dailyData;
    }

    // ----------------------------------------------------------------
    // Monthly Trend (last 6 months)
    // ----------------------------------------------------------------
    private function getMonthlyTrend($branchId = null)
    {
        $results = Laundry::selectRaw('
                YEAR(created_at) as yr,
                MONTH(created_at) as mo,
                SUM(CASE WHEN status="completed" THEN total_amount ELSE 0 END) as revenue,
                COUNT(*) as count
            ')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('yr', 'mo')
            ->get()
            ->keyBy(fn($r) => $r->yr . '-' . str_pad($r->mo, 2, '0', STR_PAD_LEFT));

        $months = [];
        $prev   = 0;
        for ($i = 5; $i >= 0; $i--) {
            $month  = now()->subMonths($i);
            $key    = $month->format('Y-m');
            $row    = $results[$key] ?? null;
            $rev    = $row ? (float) $row->revenue : 0;
            $months[] = [
                'month'       => $month->format('M Y'),
                'short_month' => $month->format('M'),
                'revenue'     => $rev,
                'count'       => $row ? (int) $row->count : 0,
                'growth'      => $prev > 0 ? round((($rev - $prev) / $prev) * 100, 1) : 0,
                'year'        => $month->year,
            ];
            $prev = $rev;
        }
        return $months;
    }

    // ----------------------------------------------------------------
    // Yearly Trend (last 5 years)
    // ----------------------------------------------------------------
    private function getYearlyTrend($branchId = null)
    {
        $results = Laundry::selectRaw('
                YEAR(created_at) as yr,
                SUM(CASE WHEN status="completed" THEN total_amount ELSE 0 END) as revenue,
                COUNT(*) as count
            ')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('created_at', '>=', now()->subYears(4)->startOfYear())
            ->groupBy('yr')
            ->get()
            ->keyBy('yr');

        $years = [];
        $prev  = 0;
        for ($i = 4; $i >= 0; $i--) {
            $year  = now()->subYears($i)->year;
            $row   = $results[$year] ?? null;
            $rev   = $row ? (float) $row->revenue : 0;
            $years[] = [
                'year'    => $year,
                'revenue' => $rev,
                'count'   => $row ? (int) $row->count : 0,
                'growth'  => $prev > 0 ? round((($rev - $prev) / $prev) * 100, 1) : 0,
            ];
            $prev = $rev;
        }
        return $years;
    }

    // ----------------------------------------------------------------
    // Top Services
    // ----------------------------------------------------------------
    private function getTopServices($startDate, $endDate, $branchId = null, $limit = 5)
    {
        return Laundry::whereBetween('laundries.created_at', [$startDate, $endDate])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->join('services', 'laundries.service_id', '=', 'services.id')
            ->select('services.id', 'services.name',
                     DB::raw('COUNT(*) as count'),
                     DB::raw('SUM(laundries.total_amount) as revenue'),
                     DB::raw('AVG(laundries.total_amount) as avg_amount'))
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }

    // ----------------------------------------------------------------
    // Payment Method Distribution
    // ----------------------------------------------------------------
    private function getPaymentMethodDistribution($startDate, $endDate, $branchId = null)
    {
        $dist = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        return [
            'cash'   => $dist['cash']->count   ?? 0,
            'card'   => $dist['card']->count   ?? 0,
            'online' => $dist['online']->count ?? 0,
            'gcash'  => $dist['gcash']->count  ?? 0,
            'cash_total'   => $dist['cash']->total   ?? 0,
            'card_total'   => $dist['card']->total   ?? 0,
            'online_total' => $dist['online']->total ?? 0,
            'gcash_total'  => $dist['gcash']->total  ?? 0,
        ];
    }

    // ----------------------------------------------------------------
    // Laundry Metrics
    // ----------------------------------------------------------------
    private function getLaundryMetrics($startDate, $endDate, $branchId = null)
    {
        $base = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        $stats = (clone $base)
            ->selectRaw('COUNT(*) as total, SUM(weight) as total_weight, AVG(weight) as avg_weight')
            ->first();

        return [
            'total'        => $stats->total ?? 0,
            'weight_total' => $stats->total_weight ?? 0,
            'avg_weight'   => $stats->avg_weight ?? 0,
            'by_status' => (clone $base)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'by_service' => Laundry::whereBetween('laundries.created_at', [$startDate, $endDate])
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->join('services', 'laundries.service_id', '=', 'services.id')
                ->select('services.name', DB::raw('count(*) as count'))
                ->groupBy('services.name')
                ->pluck('count', 'name')
                ->toArray(),
        ];
    }

    // ----------------------------------------------------------------
    // Customer Metrics
    // ----------------------------------------------------------------
    private function getCustomerMetrics($startDate, $endDate, $branchId = null)
    {
        return [
            'total' => Customer::count(),
            'new'   => Customer::whereBetween('created_at', [$startDate, $endDate])->count(),
            'active' => Customer::whereHas('laundries', function ($q) use ($startDate, $endDate, $branchId) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
                if ($branchId) $q->where('branch_id', $branchId);
            })->count(),
            'top_customers' => Laundry::whereBetween('created_at', [$startDate, $endDate])
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->select('customer_id', DB::raw('count(*) as laundry_count, sum(total_amount) as total_spent'))
                ->groupBy('customer_id')
                ->orderByDesc('total_spent')
                ->with('customer:id,name,email,phone')
                ->limit(5)
                ->get(),
        ];
    }

    // ----------------------------------------------------------------
    // Pickup Metrics
    // ----------------------------------------------------------------
    private function getPickupMetrics($branchId = null)
    {
        $base = PickupRequest::query()->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        return [
            'pending'         => (clone $base)->where('status', 'pending')->count(),
            'en_route'        => (clone $base)->where('status', 'en_route')->count(),
            'completed_today' => PickupRequest::whereDate('picked_up_at', today())
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->count(),
            'total'  => (clone $base)->count(),
            'recent' => PickupRequest::with(['customer:id,name,phone', 'branch:id,name'])
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereIn('status', ['pending', 'en_route'])
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
        ];
    }

    // ----------------------------------------------------------------
    // Unclaimed Laundry
    // ----------------------------------------------------------------
    private function getUnclaimedLaundry($branchId = null)
    {
        $unclaimed = Laundry::where('status', 'ready')
            ->where('updated_at', '<', now()->subDays(3))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with(['customer:id,name,phone', 'branch:id,name'])
            ->orderBy('updated_at')
            ->get();

        return [
            'total_count' => $unclaimed->count(),
            'total_value' => $unclaimed->sum('total_amount'),
            'categorized' => [
                '3-5 days' => $unclaimed->filter(fn($l) => $l->updated_at->diffInDays(now()) >= 3 && $l->updated_at->diffInDays(now()) < 5)->count(),
                '5-7 days' => $unclaimed->filter(fn($l) => $l->updated_at->diffInDays(now()) >= 5 && $l->updated_at->diffInDays(now()) < 7)->count(),
                '7+ days'  => $unclaimed->filter(fn($l) => $l->updated_at->diffInDays(now()) >= 7)->count(),
            ],
            'laundries'  => $unclaimed->take(10),
            'oldest_days' => $unclaimed->first()
                ? now()->diffInDays($unclaimed->first()->updated_at)
                : 0,
        ];
    }

    // ----------------------------------------------------------------
    // Branch Performance (staff's branch)
    // ----------------------------------------------------------------
    private function getBranchPerformance($startDate, $endDate, $branchId = null)
    {
        $branches = Branch::when($branchId, fn($q) => $q->where('id', $branchId))
            ->withCount([
                'laundries as laundries_count' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]),
            ])
            ->withSum([
                'laundries as revenue' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])->where('status', 'completed'),
            ], 'total_amount')
            ->orderByDesc('revenue')
            ->get();

        $totalRevenue = max($branches->sum('revenue'), 1);

        return $branches->map(fn($b) => [
            'id'              => $b->id,
            'name'            => $b->name,
            'laundries_count' => $b->laundries_count ?? 0,
            'revenue'         => $b->revenue ?? 0,
            'percentage'      => round(($b->revenue / $totalRevenue) * 100, 1),
            'is_my_branch'    => true,
        ]);
    }

    // ----------------------------------------------------------------
    // Laundry Pipeline
    // ----------------------------------------------------------------
    private function getLaundryPipeline($branchId = null)
    {
        $statuses = ['received', 'processing', 'ready', 'paid', 'completed'];
        $rows     = Laundry::select('status', DB::raw('count(*) as count'))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', $statuses)
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $pipeline = [];
        foreach ($statuses as $s) {
            $pipeline[$s] = $rows[$s] ?? 0;
        }
        // completed = today only
        $pipeline['completed'] = Laundry::whereDate('updated_at', today())
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->count();

        return $pipeline;
    }

    // ----------------------------------------------------------------
    // Recent Laundries
    // ----------------------------------------------------------------
    private function getRecentLaundries($branchId = null, $limit = 10)
    {
        return Laundry::with(['customer:id,name,phone', 'branch:id,name', 'service:id,name'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    // ----------------------------------------------------------------
    // Alerts
    // ----------------------------------------------------------------
    private function getAlerts($branchId = null)
    {
        $alerts = [];

        $readyCount = Laundry::where('status', 'ready')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->count();

        if ($readyCount > 0) {
            $avgWait = Laundry::where('status', 'ready')
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->selectRaw('AVG(DATEDIFF(NOW(), updated_at)) as avg_days')
                ->value('avg_days');
            $alerts[] = [
                'type'        => 'warning',
                'icon'        => 'bag-check',
                'title'       => "{$readyCount} Laundries Ready for Pickup",
                'message'     => 'Average wait: ' . round($avgWait, 1) . ' days. Consider sending reminders.',
                'action'      => route('branch.laundries.index', ['status' => 'ready']),
                'action_text' => 'View Laundries',
            ];
        }

        $unclaimedCount = Laundry::where('status', 'ready')
            ->where('updated_at', '<', now()->subDays(3))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->count();

        if ($unclaimedCount > 0) {
            $unclaimedValue = Laundry::where('status', 'ready')
                ->where('updated_at', '<', now()->subDays(3))
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->sum('total_amount');
            $oldest = Laundry::where('status', 'ready')
                ->where('updated_at', '<', now()->subDays(3))
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->selectRaw('DATEDIFF(NOW(), MIN(updated_at)) as days')
                ->value('days');
            $alerts[] = [
                'type'        => 'danger',
                'icon'        => 'exclamation-triangle',
                'title'       => 'Unclaimed Laundry Alert',
                'message'     => "{$unclaimedCount} unclaimed (₱" . number_format($unclaimedValue, 2) . " at risk). Oldest: {$oldest} days.",
                'action'      => route('branch.unclaimed.index'),
                'action_text' => 'View Details',
            ];
        }

        $todayRev     = Laundry::whereDate('created_at', today())->where('status', 'completed')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('total_amount');
        $yesterdayRev = Laundry::whereDate('created_at', today()->subDay())->where('status', 'completed')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('total_amount');

        if ($todayRev > $yesterdayRev && $yesterdayRev > 0) {
            $increase = (($todayRev - $yesterdayRev) / $yesterdayRev) * 100;
            if ($increase >= 10) {
                $alerts[] = [
                    'type'    => 'success',
                    'icon'    => 'graph-up-arrow',
                    'title'   => '🔥 Revenue Up ' . round($increase, 1) . '%!',
                    'message' => "Today: ₱" . number_format($todayRev, 2) . " vs ₱" . number_format($yesterdayRev, 2) . " yesterday.",
                ];
            }
        }

        $newCustomers = Customer::whereDate('created_at', today())->count();
        if ($newCustomers > 5) {
            $alerts[] = [
                'type'    => 'info',
                'icon'    => 'people-fill',
                'title'   => "{$newCustomers} New Customers Today!",
                'message' => "Great growth! Your customer base is expanding.",
            ];
        }

        $pendingPickups = PickupRequest::where('status', 'pending')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->count();

        if ($pendingPickups > 0) {
            $alerts[] = [
                'type'        => $pendingPickups > 5 ? 'warning' : 'info',
                'icon'        => 'truck',
                'title'       => "{$pendingPickups} Pending Pickup" . ($pendingPickups > 1 ? 's' : ''),
                'message'     => "Pickup request" . ($pendingPickups > 1 ? 's are' : ' is') . " waiting for confirmation.",
                'action'      => route('branch.pickups.index'),
                'action_text' => 'View',
            ];
        }

        return $alerts;
    }

    // ----------------------------------------------------------------
    // Date Range
    // ----------------------------------------------------------------
    private function getDateRange($range)
    {
        $end = now();
        switch ($range) {
            case 'today':
                $start = now()->startOfDay();
                break;
            case 'yesterday':
                $start = now()->subDay()->startOfDay();
                $end   = now()->subDay()->endOfDay();
                break;
            case 'last_7_days':
                $start = now()->subDays(7)->startOfDay();
                break;
            case 'this_week':
                $start = now()->startOfWeek();
                break;
            case 'this_month':
                $start = now()->startOfMonth();
                break;
            case 'last_month':
                $start = now()->subMonth()->startOfMonth();
                $end   = now()->subMonth()->endOfMonth();
                break;
            case 'this_year':
                $start = now()->startOfYear();
                break;
            case 'last_30_days':
            default:
                $start = now()->subDays(30)->startOfDay();
                break;
        }
        return ['start' => $start, 'end' => $end];
    }

    // ----------------------------------------------------------------
    // Export
    // ----------------------------------------------------------------
    public function export(Request $request)
    {
        $branch = Auth::guard('branch')->user();
        if (!$branch) {
            return back()->with('error', 'Please login to access this feature.');
        }
        return back()->with('info', 'Export feature coming soon!');
    }

    // ----------------------------------------------------------------
    // Revenue Metrics
    // ----------------------------------------------------------------
    private function getRevenueMetrics($startDate, $endDate, $branchId = null)
    {
        $rev = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('SUM(total_amount) as total, AVG(total_amount) as average, MAX(total_amount) as highest, MIN(total_amount) as lowest, COUNT(*) as laundries, SUM(weight) as total_weight')
            ->first();

        $trend = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $filled = [];
        $filledCount = [];
        $current = clone $startDate;
        while ($current <= $endDate) {
            $d = $current->format('Y-m-d');
            $filled[$d]      = isset($trend[$d]) ? (float) $trend[$d]->revenue : 0;
            $filledCount[$d] = isset($trend[$d]) ? (int)   $trend[$d]->count   : 0;
            $current->addDay();
        }

        $days = max(1, $startDate->diffInDays($endDate));
        return [
            'total'          => $rev->total        ?? 0,
            'average'        => $rev->average       ?? 0,
            'average_laundry'=> $rev->average       ?? 0,
            'highest'        => $rev->highest       ?? 0,
            'lowest'         => $rev->lowest        ?? 0,
            'laundries'      => $rev->laundries     ?? 0,
            'total_weight'   => $rev->total_weight  ?? 0,
            'per_day'        => ($rev->total ?? 0) > 0 ? $rev->total / $days : 0,
            'trend'          => $filled,
            'count_trend'    => $filledCount,
        ];
    }

    // ----------------------------------------------------------------
    // Charts Data
    // ----------------------------------------------------------------
    private function getChartsData($startDate, $endDate, $branchId = null)
    {
        // Revenue trend
        $revenueTrend = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn($i) => [$i->date => (float) $i->revenue]);

        $dates = [];
        $current = clone $startDate;
        while ($current <= $endDate) {
            $d = $current->format('Y-m-d');
            $dates[$d] = $revenueTrend[$d] ?? 0;
            $current->addDay();
        }

        // Daily order count
        $dailyCount = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $countDates = [];
        $current = clone $startDate;
        while ($current <= $endDate) {
            $d = $current->format('Y-m-d');
            $countDates[$d] = $dailyCount[$d] ?? 0;
            $current->addDay();
        }

        // Service distribution
        $serviceDistribution = Laundry::whereBetween('laundries.created_at', [$startDate, $endDate])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->join('services', 'laundries.service_id', '=', 'services.id')
            ->select('services.name', DB::raw('count(*) as count'), DB::raw('SUM(laundries.total_amount) as revenue'))
            ->groupBy('services.name')
            ->orderByDesc('count')
            ->get();

        // Status breakdown
        $statusBreakdown = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Hourly distribution
        $hourly = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();
        $filledHourly = [];
        for ($h = 0; $h < 24; $h++) $filledHourly[$h] = $hourly[$h] ?? 0;

        // Weekday distribution
        $weekday = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('DAYOFWEEK(created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $weekdayData = [];
        for ($i = 1; $i <= 7; $i++) $weekdayData[$days[$i-1]] = $weekday[$i] ?? 0;

        // Payment methods
        $payMethods = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->select('payment_method', DB::raw('count(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();
        $paymentMethodData = [
            'labels' => $payMethods->pluck('payment_method')->map(fn($m) => ucfirst($m ?? 'Unknown'))->toArray(),
            'counts' => $payMethods->pluck('count')->toArray(),
            'totals' => $payMethods->pluck('total')->toArray(),
        ];

        // Weight distribution
        $weightRanges = ['0-2 kg' => [0,2], '2-5 kg' => [2,5], '5-10 kg' => [5,10], '10+ kg' => [10, PHP_INT_MAX]];
        $weightDist   = [];
        foreach ($weightRanges as $range => [$min, $max]) {
            $q = Laundry::whereBetween('created_at', [$startDate, $endDate])
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->where('weight', '>=', $min);
            if ($max < PHP_INT_MAX) $q->where('weight', '<', $max);
            $weightDist[$range] = $q->count();
        }

        return [
            'revenue_trend'       => $dates,
            'daily_count'         => $countDates,
            'service_distribution'=> $serviceDistribution,
            'status_breakdown'    => $statusBreakdown,
            'hourly_distribution' => $filledHourly,
            'weekday_distribution'=> $weekdayData,
            'payment_methods'     => $paymentMethodData,
            'weight_distribution' => $weightDist,
            'labels'              => array_keys($dates),
            'values'              => array_values($dates),
        ];
    }

    // ----------------------------------------------------------------
    // Attendance Data (Disabled - Staff model not available)
    // ----------------------------------------------------------------
    private function getAttendanceData($branchId = null)
    {
        return [
            'present_today' => 0,
            'absent_today' => 0,
            'on_leave' => 0,
            'total_staff' => 0,
        ];
    }

    // ----------------------------------------------------------------
    // Retail Sales Today
    // ----------------------------------------------------------------
    private function getRetailSalesToday($branchId = null)
    {
        $today = \App\Models\RetailSale::whereDate('created_at', today())
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('SUM(total_amount) as revenue, SUM(quantity) as items_sold')
            ->first();

        return [
            'revenue' => $today->revenue ?? 0,
            'items_sold' => $today->items_sold ?? 0,
        ];
    }

    // ----------------------------------------------------------------
    // Top Retail Products
    // ----------------------------------------------------------------
    private function getTopRetailProducts($branchId = null, $limit = 3)
    {
        return \App\Models\RetailSale::whereDate('created_at', today())
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->select('item_name', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('item_name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }

    // ----------------------------------------------------------------
    // Low Stock Items
    // ----------------------------------------------------------------
    private function getLowStockItems($branchId = null, $limit = 3)
    {
        return InventoryItem::with(['branchStocks' => function($q) use ($branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            }])
            ->whereHas('branchStocks', function($q) use ($branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
                $q->whereColumn('current_stock', '<=', 'reorder_point')
                  ->where('current_stock', '>', 0);
            })
            ->active()
            ->limit($limit)
            ->get()
            ->map(function($item) use ($branchId) {
                $stock = $item->branchStocks->where('branch_id', $branchId)->first();
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'current_stock' => $stock ? $stock->current_stock : 0,
                    'reorder_point' => $item->reorder_point,
                ];
            });
    }

    // ----------------------------------------------------------------
    // Retail Products for POS
    // ----------------------------------------------------------------
    private function getRetailProducts($branchId = null, $limit = 12)
    {
        return InventoryItem::with(['branchStocks' => function($q) use ($branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            }])
            ->whereHas('branchStocks', function($q) use ($branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
                $q->where('current_stock', '>', 0);
            })
            ->active()
            ->limit($limit)
            ->get()
            ->map(function($item) use ($branchId) {
                $stock = $item->branchStocks->where('branch_id', $branchId)->first();
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->unit_cost_price,
                    'stock' => $stock ? $stock->current_stock : 0,
                    'unit' => $item->distribution_unit,
                ];
            });
    }

    // ----------------------------------------------------------------
    // Inventory Items for Sidebar
    // ----------------------------------------------------------------
    private function getInventoryItems($branchId = null, $limit = 20)
    {
        return \App\Models\BranchStock::where('branch_id', $branchId)
            ->with('inventoryItem')
            ->orderBy('current_stock', 'asc')
            ->limit($limit)
            ->get()
            ->map(function($stock) {
                $item = $stock->inventoryItem;
                return (object) [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $stock->current_stock,
                    'reorder_level' => $stock->reorder_point ?? ($stock->max_stock * 0.2),
                    'unit' => $item->distribution_unit ?? 'pcs',
                ];
            });
    }

    // ----------------------------------------------------------------
    // Top Customers
    // ----------------------------------------------------------------
    private function getTopCustomers($branchId = null, $limit = 5)
    {
        return Customer::withCount(['laundries' => function($q) use ($branchId) {
                $q->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            }])
            ->withSum(['laundries as total_spent' => function($q) use ($branchId) {
                $q->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year)
                  ->whereIn('status', ['paid', 'completed']);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            }], 'total_amount')
            ->having('laundries_count', '>', 0)
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get()
            ->map(function($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'orders_count' => $customer->laundries_count,
                    'total_spent' => $customer->total_spent ?? 0,
                ];
            });
    }

    // ----------------------------------------------------------------
    // Financial Analytics
    // ----------------------------------------------------------------
    private function getFinancialAnalytics($branchId = null)
    {
        // This Month Laundry Revenue
        $thisMonthLaundryRevenue = Laundry::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['paid', 'completed'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        // This Month Retail Revenue
        $thisMonthRetailRevenue = \App\Models\RetailSale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        // Total Revenue
        $thisMonthRevenue = $thisMonthLaundryRevenue + $thisMonthRetailRevenue;

        // This Month Operating Expenses (from expenses table only, NOT cost of goods)
        $thisMonthExpenses = \App\Models\Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('amount');

        // Gross Profit
        $grossProfit = $thisMonthRevenue - $thisMonthExpenses;
        $profitMargin = $thisMonthRevenue > 0 ? round(($grossProfit / $thisMonthRevenue) * 100, 0) : 0;

        // Outstanding Receivables (Ready but not paid)
        $outstandingReceivables = Laundry::where('status', 'ready')
            ->where('payment_status', '!=', 'paid')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        // Cash Flow (This Month) - Use created_at as fallback if paid_at is null
        $cashInflow = Laundry::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['paid', 'completed'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        $cashOutflow = $thisMonthExpenses;
        $netCashFlow = $cashInflow - $cashOutflow;

        // Revenue by Payment Method
        $revenueByPaymentMethod = Laundry::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['paid', 'completed'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(fn($item) => [ucfirst($item->payment_method ?? 'Unknown') => $item->total]);

        // Daily Revenue Trend (Last 30 days)
        $dailyRevenue = Laundry::whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
            ->whereIn('status', ['paid', 'completed'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn($item) => [$item->date => (float) $item->revenue]);

        // Fill missing dates
        $filledDailyRevenue = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $filledDailyRevenue[$date] = $dailyRevenue[$date] ?? 0;
        }

        // Average Transaction Value
        $avgTransactionValue = Laundry::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['paid', 'completed'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->avg('total_amount') ?? 0;

        return [
            'revenue' => $thisMonthRevenue,
            'expenses' => $thisMonthExpenses,
            'gross_profit' => $grossProfit,
            'profit_margin' => $profitMargin,
            'outstanding_receivables' => $outstandingReceivables,
            'cash_inflow' => $cashInflow,
            'cash_outflow' => $cashOutflow,
            'net_cash_flow' => $netCashFlow,
            'revenue_by_payment_method' => $revenueByPaymentMethod,
            'daily_revenue_trend' => $filledDailyRevenue,
            'avg_transaction_value' => round($avgTransactionValue, 2),
        ];
    }

    // ----------------------------------------------------------------
    // Service Quality Metrics
    // ----------------------------------------------------------------
    private function getServiceQualityMetrics($branchId = null)
    {
        // Total Orders This Month
        $totalOrders = Laundry::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->count();

        // Completed Orders
        $completedOrders = Laundry::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->count();

        // Cancelled Orders (Complaints/Redos)
        $cancelledOrders = Laundry::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'cancelled')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->count();

        // Service Completion Rate
        $completionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 0) : 0;

        // Redo/Complaint Rate
        $redoRate = $totalOrders > 0 ? round(($cancelledOrders / $totalOrders) * 100, 0) : 0;

        // Average Turnaround Time (from received to completed)
        $avgTurnaroundTime = Laundry::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
            ->value('avg_hours') ?? 0;

        // Customer Retention Rate (Repeat Customers)
        $repeatCustomers = Customer::whereHas('laundries', function($q) use ($branchId) {
            $q->whereMonth('created_at', now()->month)
              ->whereYear('created_at', now()->year);
            if ($branchId) $q->where('branch_id', $branchId);
        }, '>=', 2)->count();

        $totalCustomersThisMonth = Customer::whereHas('laundries', function($q) use ($branchId) {
            $q->whereMonth('created_at', now()->month)
              ->whereYear('created_at', now()->year);
            if ($branchId) $q->where('branch_id', $branchId);
        })->count();

        $retentionRate = $totalCustomersThisMonth > 0 ? round(($repeatCustomers / $totalCustomersThisMonth) * 100, 0) : 0;

        // First-time vs Repeat Customers
        $firstTimeCustomers = Customer::whereHas('laundries', function($q) use ($branchId) {
            $q->whereMonth('created_at', now()->month)
              ->whereYear('created_at', now()->year);
            if ($branchId) $q->where('branch_id', $branchId);
        })
        ->withCount(['laundries' => function($q) use ($branchId) {
            if ($branchId) $q->where('branch_id', $branchId);
        }])
        ->get()
        ->filter(fn($c) => $c->laundries_count == 1)
        ->count();

        // On-Time Delivery Rate (Orders completed within expected time)
        // Assuming expected turnaround is 48 hours
        $onTimeOrders = Laundry::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, updated_at) <= 48')
            ->count();

        $onTimeRate = $completedOrders > 0 ? round(($onTimeOrders / $completedOrders) * 100, 0) : 0;

        // Service Accuracy (Non-cancelled orders)
        $accuracyRate = $totalOrders > 0 ? round((($totalOrders - $cancelledOrders) / $totalOrders) * 100, 0) : 0;

        return [
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'cancelled_orders' => $cancelledOrders,
            'completion_rate' => $completionRate,
            'redo_rate' => $redoRate,
            'avg_turnaround_hours' => round($avgTurnaroundTime, 1),
            'avg_turnaround_days' => round($avgTurnaroundTime / 24, 1),
            'retention_rate' => $retentionRate,
            'repeat_customers' => $repeatCustomers,
            'first_time_customers' => $firstTimeCustomers,
            'total_customers_this_month' => $totalCustomersThisMonth,
            'on_time_delivery_rate' => $onTimeRate,
            'service_accuracy' => $accuracyRate,
        ];
    }
}
