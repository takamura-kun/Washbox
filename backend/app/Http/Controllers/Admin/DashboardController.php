<?php

namespace App\Http\Controllers\Admin;

use App\Models\Laundry;
use App\Models\Branch;
use App\Models\AddOn;
use App\Models\Service;
use App\Models\Customer;
use App\Models\CustomerRating;
use App\Models\Promotion;
use App\Models\PickupRequest;
use App\Models\SystemSetting;
use App\Models\UnclaimedLaundry;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    // Cache duration in minutes
    protected $cacheDuration = 5;

    public function index(Request $request)
    {
        $dateRange = $request->get('date_range', 'last_30_days');
        $dates     = $this->getDateRange($dateRange);
        $startDate = $dates['start'];
        $endDate   = $dates['end'];

        // Use a per-range cache key so different filters don't collide
        $cacheKey = 'admin_dashboard_' . Auth::id() . '_' . $dateRange;
        $stats = Cache::remember($cacheKey, $this->cacheDuration * 60, function () use ($startDate, $endDate) {
            return $this->getDashboardStats($startDate, $endDate);
        });

        $currentFilters = ['date_range' => $dateRange];

        return view('admin.dashboard', compact('stats', 'currentFilters'));
    }

    private function getDashboardStats($startDate = null, $endDate = null)
    {
        // Fallback when called without params (e.g. from getStats API endpoint)
        if (!$startDate || !$endDate) {
            $d = $this->getDateRange('last_30_days');
            $startDate = $d['start'];
            $endDate   = $d['end'];
        }

        // Get dates for calculations
        $today = today();
        $yesterday = today()->subDay();
        $currentMonth = now()->startOfMonth();

        // 1. Laundry Stats & Trends
        $laundriesData = $this->getLaundryStats($today, $yesterday);
        $todayLaundries = $laundriesData['today'];
        $yesterdayLaundries = $laundriesData['yesterday'];
        $laundriesChange = $this->calculatePercentageChange($yesterdayLaundries, $todayLaundries);

        // 2. Revenue Stats
        $revenueData = $this->getRevenueStats($today, $yesterday);
        $todayRevenue = $revenueData['today'];
        $yesterdayRevenue = $revenueData['yesterday'];
        $revenueChange = $this->calculatePercentageChange($yesterdayRevenue, $todayRevenue);
        $thisMonthRevenue = $revenueData['month'];

        // 3. Laundry Pipeline Status
        $laundryPipeline = $this->getLaundryPipeline();
        $branchPipeline  = $this->getLaundryPipelineByBranch();

        // 4. Customer Management
        $customerData = $this->getCustomerStats($currentMonth);
        $activeCustomers = $customerData['active'];
        $newCustomersThisMonth = $customerData['new_this_month'];
        $customerRegistrationSource = $customerData['sources'];

        // 5. Unclaimed Laundry
        $unclaimedData = $this->getUnclaimedStats();
        $unclaimedLaundry = $unclaimedData['total'];
        $unclaimedBreakdown = $unclaimedData['breakdown'];
        $estimatedUnclaimedLoss = $unclaimedLaundry * 500;

        // 6. Pickup Requests
        $pickupStats = $this->getPickupStats();

        // 7. Notification Metrics
        $notificationStats = $this->getNotificationMetrics();

        // 8. Payment Collection
        $paymentCollection = $this->getPaymentCollection();

        // 9. Branch Performance
        $branchPerformance = $this->getBranchPerformance($currentMonth, $startDate, $endDate);

        // 10. Data Quality Metrics
        $dataQuality = $this->getDataQualityMetrics();

        // 11. Revenue Trend (respects the selected date range)
        $revenueTrend = $this->getRevenueTrend($startDate, $endDate);

        // 12. System Health Check
        $systemPulse = $this->getSystemHealth();

        return [
            // KPI Metrics
            'todayLaundries'      => $todayLaundries,
            'laundriesChange'     => $laundriesChange,
            'todayRevenue'     => $todayRevenue,
            'revenueChange'    => $revenueChange,
            'thisMonthRevenue' => $thisMonthRevenue,
            'activeCustomers'       => $activeCustomers,
            'newCustomersThisMonth' => $newCustomersThisMonth,
            'newCustomersToday'     => Customer::whereDate('created_at', today())->count(),
            'unclaimedLaundry' => $unclaimedLaundry,
            'estimatedUnclaimedLoss' => $estimatedUnclaimedLoss,
            'avgProcessingTime' => $this->calculateAverageProcessingTime(),

            // Laundry Management
            'laundryPipeline'  => $laundryPipeline,
            'branchPipeline'   => $branchPipeline,
            'totalLaundries'   => array_sum($laundryPipeline),

            // Unclaimed & Pickups
            'unclaimedBreakdown'    => $unclaimedBreakdown,
            'pickupStats'           => $pickupStats,
            'pendingPickups'        => $this->getPendingPickups(), // Important for map
            'pickupBranchPipeline'  => $this->getPickupPipelineByBranch(),

            // Customer Management
            'customerRegistrationSource' => $customerRegistrationSource,
            'customerBranchPipeline'    => $this->getCustomerPipelineByBranch(),
            'topCustomers'              => $this->getTopCustomers(),
            'topRatedCustomers'         => $this->getTopRatedCustomers(),

            // Notifications
            'notificationStats' => $notificationStats,

            // Revenue & Payment
            'paymentCollection'  => $paymentCollection,
            'revenueByService'   => $this->getRevenueByService($startDate, $endDate),
            'serviceChartData'   => $this->getServiceChartData($startDate, $endDate),

            // Branch Performance
            'branchPerformance' => $branchPerformance,

            // Data Quality
            'dataQuality' => $dataQuality,

            // Charts Data
            'last7DaysRevenue' => $revenueTrend['data'],
            'revenueLabels'    => $revenueTrend['labels'],
            'dailyLaundryCount' => $this->getDailyLaundryCount($startDate, $endDate),
            'paymentMethods'   => $this->getPaymentMethods($startDate, $endDate),
            'topServices'      => $this->getTopServices($startDate, $endDate),
            'yoyRevenue'       => $this->getYearOverYearRevenue(),

            // Utilities
            'activePromotions' => Promotion::where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get(),
            'fcm_ready'        => \Illuminate\Support\Facades\File::exists(storage_path('app/firebase/service-account.json')),
            'system_pulse'     => $systemPulse,

            // Branch locations for map markers
            'branches' => Branch::select('id', 'name', 'address', 'phone', 'latitude', 'longitude')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get()
                ->map(function ($branch) {
                    return [
                        'id'        => $branch->id,
                        'name'      => $branch->name,
                        'address'   => $branch->address,
                        'phone'     => $branch->phone,
                        'latitude'  => (float) $branch->latitude,
                        'longitude' => (float) $branch->longitude,
                    ];
                })
                ->values()
                ->toArray(),
        ];
    }

    private function getLaundryStats($today, $yesterday)
    {
        $laundries = Laundry::selectRaw("
            SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today_count,
            SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as yesterday_count
        ", [$today->toDateString(), $yesterday->toDateString()])
        ->first();

        return [
            'today' => $laundries->today_count ?? 0,
            'yesterday' => $laundries->yesterday_count ?? 0
        ];
    }

    private function getRevenueStats($today, $yesterday)
    {
        $revenue = Laundry::where('status', '!=', 'cancelled')
            ->selectRaw("
                SUM(CASE WHEN DATE(created_at) = ? THEN total_amount ELSE 0 END) as today_revenue,
                SUM(CASE WHEN DATE(created_at) = ? THEN total_amount ELSE 0 END) as yesterday_revenue,
                SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE())
                         AND YEAR(created_at) = YEAR(CURDATE())
                         THEN total_amount ELSE 0 END) as month_revenue
            ", [$today->toDateString(), $yesterday->toDateString()])
            ->first();

        return [
            'today' => $revenue->today_revenue ?? 0,
            'yesterday' => $revenue->yesterday_revenue ?? 0,
            'month' => $revenue->month_revenue ?? 0
        ];
    }

    private function getLaundryPipeline()
    {
        return Laundry::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    private function getCustomerStats($currentMonth)
    {
        $activeCustomers = Customer::where('is_active', true)->count();

        $newCustomersThisMonth = Customer::where('created_at', '>=', $currentMonth)
            ->count();

        $rawSources = Customer::select('registration_type', DB::raw('count(*) as count'))
            ->groupBy('registration_type')
            ->pluck('count', 'registration_type')
            ->toArray();

        // Normalize DB values to consistent keys.
        // DB may store: 'mobile_app', 'Mobile App', 'app', 'walk_in', 'Walk-in', etc.
        $normalized = ['walk_in' => 0, 'app' => 0, 'referral' => 0, 'other' => 0];

        foreach ($rawSources as $type => $count) {
            $key = strtolower(trim((string) $type));
            $key = str_replace([' ', '-'], '_', $key); // "Mobile App" -> "mobile_app"

            if (in_array($key, ['app', 'mobile_app', 'mobile', 'online', 'self_registered'])) {
                $normalized['app'] += $count;
            } elseif (in_array($key, ['walk_in', 'walkin', 'counter'])) {
                $normalized['walk_in'] += $count;
            } elseif (in_array($key, ['referral', 'referred'])) {
                $normalized['referral'] += $count;
            } else {
                $normalized['other'] += $count;
            }
        }

        return [
            'active'         => $activeCustomers,
            'new_this_month' => $newCustomersThisMonth,
            'sources'        => $normalized,
        ];
    }

    private function getCustomerPipelineByBranch()
    {
        // Customers have preferred_branch_id directly on the customers table.
        // Registration types are: 'walk_in' and 'self_registered'.
        
        // Get customers WITH preferred branch
        $raw = Customer::select(
                'preferred_branch_id',
                'registration_type',
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('preferred_branch_id')
            ->groupBy('preferred_branch_id', 'registration_type')
            ->get()
            ->groupBy('preferred_branch_id');

        // Get customers WITHOUT preferred branch (unassigned)
        $unassigned = Customer::select(
                'registration_type',
                DB::raw('COUNT(*) as count')
            )
            ->whereNull('preferred_branch_id')
            ->groupBy('registration_type')
            ->get();

        $branches = Branch::select('id', 'name')->orderBy('name')->get();

        $result = $branches->map(function ($branch) use ($raw) {
            $rows     = $raw->get($branch->id, collect());
            $walkIn   = 0;
            $mobile   = 0;

            foreach ($rows as $row) {
                $type = strtolower(trim((string) $row->registration_type));
                $cnt  = (int) $row->count;

                if ($type === 'self_registered') {
                    $mobile += $cnt;   // self-registered = mobile app customer
                } else {
                    $walkIn += $cnt;   // walk_in (default)
                }
            }

            $total = $walkIn + $mobile;
            if ($total === 0) return null;

            return [
                'id'      => $branch->id,
                'name'    => $branch->name,
                'walk_in' => $walkIn,
                'mobile'  => $mobile,
                'total'   => $total,
            ];
        })->filter()->values();

        // Add unassigned customers as a separate entry if any exist
        if ($unassigned->isNotEmpty()) {
            $walkIn = 0;
            $mobile = 0;

            foreach ($unassigned as $row) {
                $type = strtolower(trim((string) $row->registration_type));
                $cnt  = (int) $row->count;

                if ($type === 'self_registered') {
                    $mobile += $cnt;
                } else {
                    $walkIn += $cnt;
                }
            }

            $total = $walkIn + $mobile;
            if ($total > 0) {
                $result->push([
                    'id'      => 0,  // Special ID for unassigned
                    'name'    => 'Unassigned',
                    'walk_in' => $walkIn,
                    'mobile'  => $mobile,
                    'total'   => $total,
                ]);
            }
        }

        return $result->toArray();
    }

    private function getTopCustomers(int $limit = 5)
    {
        return Customer::withSum('laundries', 'total_amount')
            ->withCount('laundries')
            ->orderByDesc('laundries_sum_total_amount')
            ->limit($limit)
            ->get()
            ->map(fn($c) => [
                'id'             => $c->id,
                'name'           => $c->name,
                'laundries_count'=> (int) $c->laundries_count,
                'total_spent'    => (float) ($c->laundries_sum_total_amount ?? 0),
            ])->toArray();
    }

    private function getTopRatedCustomers(int $limit = 5)
    {
        // Use a subquery to get rating stats first, then join to customers
        // to avoid ONLY_FULL_GROUP_BY issues in strict MySQL mode.
        $ratingStats = CustomerRating::select('customer_id')
            ->selectRaw('ROUND(AVG(rating), 1) as avg_rating')
            ->selectRaw('COUNT(id) as ratings_count')
            ->groupBy('customer_id')
            ->having('ratings_count', '>=', 1)
            ->orderByDesc('avg_rating')
            ->orderByDesc('ratings_count')
            ->limit($limit)
            ->get();

        $customerIds = $ratingStats->pluck('customer_id');

        $customers = Customer::whereIn('id', $customerIds)->get()->keyBy('id');

        return $ratingStats->map(function ($stat) use ($customers) {
            $customer = $customers->get($stat->customer_id);
            if (!$customer) return null;

            return [
                'id'            => $customer->id,
                'name'          => $customer->name,
                'avg_rating'    => (float) $stat->avg_rating,
                'ratings_count' => (int)   $stat->ratings_count,
            ];
        })->filter()->values()->toArray();
    }

    private function getUnclaimedStats()
    {
        $unclaimed = UnclaimedLaundry::whereNull('recovered_at')
            ->whereNull('disposed_at');

        $breakdown = [
            'within_7_days' => (clone $unclaimed)->where('days_unclaimed', '<=', 7)->count(),
            '1_to_2_weeks' => (clone $unclaimed)->whereBetween('days_unclaimed', [8, 14])->count(),
            '2_to_4_weeks' => (clone $unclaimed)->whereBetween('days_unclaimed', [15, 28])->count(),
            'over_1_month' => (clone $unclaimed)->where('days_unclaimed', '>', 28)->count(),
        ];

        return [
            'total' => array_sum($breakdown),
            'breakdown' => $breakdown
        ];
    }

    private function getPickupStats()
    {
        return PickupRequest::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    private function getPickupPipelineByBranch()
    {
        $statuses = ['pending', 'accepted', 'en_route', 'picked_up', 'cancelled'];

        // Single grouped query — no N+1
        $raw = PickupRequest::select('branch_id', 'status', DB::raw('count(*) as count'))
            ->whereIn('status', $statuses)
            ->groupBy('branch_id', 'status')
            ->get()
            ->groupBy('branch_id');

        $branches = Branch::select('id', 'name')->orderBy('name')->get();

        return $branches->map(function ($branch) use ($raw, $statuses) {
            $rows = $raw->get($branch->id, collect());

            $counts = collect($statuses)->mapWithKeys(function ($s) use ($rows) {
                $row = $rows->firstWhere('status', $s);
                return [$s => $row ? (int) $row->count : 0];
            })->toArray();

            return [
                'id'       => $branch->id,
                'name'     => $branch->name,
                'statuses' => $counts,
                'total'    => array_sum($counts),
                'active'   => ($counts['pending'] ?? 0) + ($counts['accepted'] ?? 0) + ($counts['en_route'] ?? 0),
            ];
        })->values()->toArray();
    }

    private function getPendingPickups()
    {
        return PickupRequest::with(['customer', 'branch'])
            ->whereIn('status', ['pending', 'accepted', 'en_route'])
            ->whereNotIn('status', ['cancelled', 'picked_up'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->limit(20)
            ->get()
            ->map(function($pickup) {
                return [
                    'id' => $pickup->id,
                    'customer' => [
                        'name' => $pickup->customer->name ?? 'Unknown',
                        'phone' => $pickup->customer->phone ?? null
                    ],
                    'branch' => [
                        'name' => $pickup->branch->name ?? 'Main Branch',
                        'address' => $pickup->branch->address ?? null
                    ],
                    'pickup_address' => $pickup->pickup_address,
                    'latitude' => $pickup->latitude,
                    'longitude' => $pickup->longitude,
                    'status' => $pickup->status,
                    'preferred_date' => $pickup->preferred_date,
                    'preferred_time' => $pickup->preferred_time,
                    'notes' => $pickup->notes
                ];
            });
    }

    private function getNotificationMetrics()
    {
        return [
            'total_sent' => 0,
            'delivery_success' => 0,
            'delivery_failed' => 0,
            'engagement_rate' => $this->calculateEngagementRate(),
        ];
    }

    private function getPaymentCollection()
    {
        return [
            'paid_cash' => Laundry::where('status', 'paid')->count(),
            'pending_payment' => Laundry::where('status', 'ready')->count(),
            'disputes' => UnclaimedLaundry::where('status', 'disputed')->count(),
        ];
    }

    private function getBranchPerformance($currentMonth, $startDate = null, $endDate = null)
    {
        // Use the filter range when provided, otherwise fall back to current month
        $from = $startDate ?? $currentMonth;
        $to   = $endDate   ?? now();

        $branches = Branch::withCount([
            'laundries as monthly_laundries' => function ($query) use ($from, $to) {
                $query->whereBetween('created_at', [$from, $to])
                      ->where('status', '!=', 'cancelled');
            },
            'laundries as monthly_revenue' => function ($query) use ($from, $to) {
                $query->whereBetween('created_at', [$from, $to])
                      ->where('status', '!=', 'cancelled')
                      ->select(DB::raw('SUM(total_amount)'));
            }
        ])->get();

        $totalMonthlyRevenue = $branches->sum('monthly_revenue');

        return $branches->map(function ($branch) use ($totalMonthlyRevenue) {
            $percentage = $totalMonthlyRevenue > 0
                ? round(($branch->monthly_revenue / $totalMonthlyRevenue) * 100, 1)
                : 0;

            return (object) [
                'id' => $branch->id,
                'name' => $branch->name,
                'total_revenue' => $branch->monthly_revenue ?? 0,
                'total_laundries' => $branch->monthly_laundries ?? 0,
                'avg_laundry_value' => ($branch->monthly_laundries > 0)
                    ? round(($branch->monthly_revenue / $branch->monthly_laundries), 2)
                    : 0,
                'percentage' => $percentage
            ];
        })->sortByDesc('total_revenue')->values();
    }

    private function getDataQualityMetrics()
    {
        $totalRecords = Laundry::count();

        if ($totalRecords == 0) {
            return [
                'data_entry_errors' => 0,
                'billing_disputes' => 0,
                'info_accuracy' => 100
            ];
        }

        $accurateRecords = Laundry::whereNotNull('customer_id')
            ->whereNotNull('branch_id')
            ->where('total_amount', '>', 0)
            ->count();

        return [
            'data_entry_errors' => $totalRecords - $accurateRecords,
            'billing_disputes' => UnclaimedLaundry::where('status', 'disputed')->count(),
            'info_accuracy' => round(($accurateRecords / $totalRecords) * 100, 1)
        ];
    }

    private function getLast7DaysRevenue()
    {
        $data = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $revenue = Laundry::whereDate('created_at', $date)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');
            $data[] = (float)$revenue;
            $labels[] = $date->format('M d');
        }

        return ['data' => $data, 'labels' => $labels];
    }

    /**
     * Revenue trend aggregated by day across any date range.
     * Replaces getLast7DaysRevenue when a date filter is active.
     */
    private function getRevenueTrend($startDate, $endDate)
    {
        $trend = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $data    = [];
        $labels  = [];
        $current = clone $startDate;

        while ($current <= $endDate) {
            $d        = $current->format('Y-m-d');
            $data[]   = isset($trend[$d]) ? (float) $trend[$d]->revenue : 0;
            $labels[] = $current->format('M d');
            $current->addDay();
        }

        return ['data' => $data, 'labels' => $labels];
    }

    private function getDateRange(string $range): array
    {
        $end = now()->endOfDay();
        switch ($range) {
            case 'today':
                $start = now()->startOfDay();
                break;
            case 'yesterday':
                $start = now()->subDay()->startOfDay();
                $end   = now()->subDay()->endOfDay();
                break;
            case 'last_7_days':
                $start = now()->subDays(6)->startOfDay();
                break;
            case 'this_week':
                $start = now()->startOfWeek();
                break;
            case 'this_month':
                $start = now()->startOfMonth();
                break;
            case 'last_month':
                $start = now()->subMonth()->startOfMonth();
                $end   = now()->subMonth()->endOfMonth()->endOfDay();
                break;
            case 'this_year':
                $start = now()->startOfYear();
                break;
            case 'last_30_days':
            default:
                $start = now()->subDays(29)->startOfDay();
                break;
        }
        return ['start' => $start, 'end' => $end];
    }

    private function getSystemHealth()
    {
        $dbStatus = false;
        try {
            DB::connection()->getPdo();
            $dbStatus = true;
        } catch (\Exception $e) {
            $dbStatus = false;
        }

        return [
            'db_connected' => $dbStatus,
            'last_check' => now()->format('h:i:s A')
        ];
    }

    private function calculatePercentageChange($old, $new)
    {
        if ($old == 0) return $new > 0 ? 100 : 0;
        return round((($new - $old) / abs($old)) * 100, 1);
    }

    private function calculateAverageProcessingTime()
    {
        $avgDays = Laundry::whereNotNull('completed_at')
            ->whereNotNull('created_at')
            ->selectRaw('AVG(DATEDIFF(completed_at, created_at)) as avg_days')
            ->first()->avg_days ?? 0;

        return round($avgDays, 1) . ' days';
    }

    private function calculateEngagementRate()
    {
        return 0;
    }

    private function getRevenueByService($startDate = null, $endDate = null)
    {
        return Service::where('is_active', true)
            ->withSum(['laundries' => function ($query) use ($startDate, $endDate) {
                $query->where('status', '!=', 'cancelled');
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            }], 'total_amount')
            ->get()
            ->map(function ($service) {
                return [
                    'service' => $service->name,
                    'revenue' => $service->laundries_sum_total_amount ?? 0,
                ];
            })
            ->toArray();
    }

    private function getLaundryPipelineByBranch()
    {
        $statuses = ['received', 'ready', 'paid', 'completed', 'cancelled'];

        $raw = Laundry::select('branch_id', 'status', DB::raw('count(*) as count'))
            ->whereIn('status', $statuses)
            ->groupBy('branch_id', 'status')
            ->get()
            ->groupBy('branch_id');

        $branches = Branch::select('id', 'name')->orderBy('name')->get();

        return $branches->map(function ($branch) use ($raw, $statuses) {
            $rows = $raw->get($branch->id, collect());

            $counts = collect($statuses)->mapWithKeys(function ($s) use ($rows) {
                $row = $rows->firstWhere('status', $s);
                return [$s => $row ? (int) $row->count : 0];
            })->toArray();

            $total = array_sum($counts);
            if ($total === 0) return null;

            return [
                'id'       => $branch->id,
                'name'     => $branch->name,
                'statuses' => $counts,
                'total'    => $total,
            ];
        })->filter()->values()->toArray();
    }

    private function getServiceChartData($startDate = null, $endDate = null)
    {
        $dateFilter = fn($q) => ($startDate && $endDate)
            ? $q->where('status', '!=', 'cancelled')->whereBetween('created_at', [$startDate, $endDate])
            : $q->where('status', '!=', 'cancelled');

        // Services table covers drop_off and self_service categories
        $services = Service::where('is_active', true)
            ->withCount(['laundries as laundry_count'  => $dateFilter])
            ->withSum(['laundries as laundry_revenue'  => $dateFilter], 'total_amount')
            ->orderBy('name')
            ->get();

        // Add-ons are a separate model/table (add_ons) — query them independently
        // withSum can't reach pivot columns, so use a raw subquery for revenue
        $addons = AddOn::where('is_active', true)
            ->withCount('laundries as laundry_count')
            ->selectRaw('add_ons.*, (
                SELECT COALESCE(SUM(la.price_at_purchase * la.quantity), 0)
                FROM laundry_addon la
                WHERE la.add_on_id = add_ons.id
            ) as laundry_revenue')
            ->orderBy('name')
            ->get();

        $grandCount   = (int)   $services->sum('laundry_count') + (int) $addons->sum('laundry_count');
        $grandRevenue = (float) $services->sum('laundry_revenue') + (float) $addons->sum('laundry_revenue');

        $buckets = [
            'drop_off'     => ['labels' => [], 'counts' => [], 'revenues' => [], 'total' => 0],
            'self_service' => ['labels' => [], 'counts' => [], 'revenues' => [], 'total' => 0],
            'addon'        => ['labels' => [], 'counts' => [], 'revenues' => [], 'total' => 0],
        ];

        $allServices = [];

        // Bucket services by category
        foreach ($services as $svc) {
            $cat   = strtolower(trim((string) ($svc->category ?? 'drop_off')));
            if (!isset($buckets[$cat])) $cat = 'drop_off';

            $count = (int)   ($svc->laundry_count   ?? 0);
            $rev   = (float) ($svc->laundry_revenue  ?? 0);

            $buckets[$cat]['labels'][]   = $svc->name;
            $buckets[$cat]['counts'][]   = $count;
            $buckets[$cat]['revenues'][] = $rev;
            $buckets[$cat]['total']      += $count;

            $allServices[] = [
                'id'          => $svc->id,
                'name'        => $svc->name,
                'category'    => $cat,
                'count'       => $count,
                'revenue'     => $rev,
                'count_pct'   => $grandCount   > 0 ? round(($count / $grandCount)   * 100, 1) : 0,
                'revenue_pct' => $grandRevenue > 0 ? round(($rev   / $grandRevenue) * 100, 1) : 0,
            ];
        }

        // Bucket add-ons (always 'addon' category)
        foreach ($addons as $addon) {
            $count = (int)   ($addon->laundry_count   ?? 0);
            $rev   = (float) ($addon->laundry_revenue  ?? 0);

            $buckets['addon']['labels'][]   = $addon->name;
            $buckets['addon']['counts'][]   = $count;
            $buckets['addon']['revenues'][] = $rev;
            $buckets['addon']['total']      += $count;

            $allServices[] = [
                'id'          => $addon->id,
                'name'        => $addon->name . ' (Add-On)',
                'category'    => 'addon',
                'count'       => $count,
                'revenue'     => $rev,
                'count_pct'   => $grandCount   > 0 ? round(($count / $grandCount)   * 100, 1) : 0,
                'revenue_pct' => $grandRevenue > 0 ? round(($rev   / $grandRevenue) * 100, 1) : 0,
            ];
        }

        usort($allServices, fn($a, $b) => $b['count'] <=> $a['count']);

        return [
            'drop_off'     => $buckets['drop_off'],
            'self_service' => $buckets['self_service'],
            'addon'        => $buckets['addon'],
            'all_services' => $allServices,
            'grand_total'  => ['count' => $grandCount, 'revenue' => $grandRevenue],
        ];
    }

    /**
     * Get dashboard stats via API
     */
    public function getStats()
    {
        $stats = $this->getDashboardStats();
        return response()->json($stats);
    }

    /**
     * Clear dashboard cache manually
     */
    public function clearCache()
    {
        $ranges = ['today','yesterday','last_7_days','this_week','this_month','last_month','this_year','last_30_days'];
        foreach ($ranges as $range) {
            Cache::forget('admin_dashboard_' . Auth::id() . '_' . $range);
        }
        Cache::forget('dashboard_stats_' . Auth::id()); // legacy key
        return response()->json(['message' => 'Dashboard cache cleared successfully']);
    }

    /**
     * Get daily laundry count for selected period
     */
    private function getDailyLaundryCount($startDate, $endDate)
    {
        $dailyData = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $data = [];
        $labels = [];
        $current = clone $startDate;

        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $data[] = isset($dailyData[$dateStr]) ? (int) $dailyData[$dateStr]->count : 0;
            $labels[] = $current->format('M d');
            $current->addDay();
        }

        return ['data' => $data, 'labels' => $labels];
    }

    /**
     * Get payment methods breakdown
     */
    private function getPaymentMethods($startDate, $endDate)
    {
        $payments = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        $totalAmount = $payments->sum('total');
        
        return $payments->map(function($payment) use ($totalAmount) {
            return [
                'method' => $payment->payment_method ?: 'Cash',
                'count' => (int) $payment->count,
                'amount' => (float) $payment->total,
                'percentage' => $totalAmount > 0 ? round(($payment->total / $totalAmount) * 100, 1) : 0
            ];
        })->toArray();
    }

    /**
     * Get top services for selected period
     */
    private function getTopServices($startDate, $endDate)
    {
        $services = Service::withCount(['laundries' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                      ->where('status', '!=', 'cancelled');
            }])
            ->having('laundries_count', '>', 0)
            ->orderByDesc('laundries_count')
            ->limit(5)
            ->get();

        $maxCount = $services->max('laundries_count') ?: 1;

        return $services->map(function($service, $index) use ($maxCount) {
            return [
                'rank' => $index + 1,
                'name' => $service->name,
                'count' => (int) $service->laundries_count,
                'percentage' => round(($service->laundries_count / $maxCount) * 100, 1)
            ];
        })->toArray();
    }

    /**
     * Get year-over-year revenue comparison (last 5 years)
     */
    private function getYearOverYearRevenue()
    {
        $years = [];
        $data = [];
        $currentYear = now()->year;

        for ($i = 4; $i >= 0; $i--) {
            $year = $currentYear - $i;
            $years[] = $year;
            
            $revenue = Laundry::whereYear('created_at', $year)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');
            
            $data[] = (float) $revenue;
        }

        return [
            'years' => $years,
            'data' => $data
        ];
    }
}
