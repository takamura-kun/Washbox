<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerRating;
use App\Models\Laundry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        $stats = [
            'total_revenue' => Laundry::where('payment_status', 'paid')->sum('total_amount'),
            'total_laundries' => Laundry::count(),
            'total_customers' => Customer::count(),
            'active_branches' => Branch::where('is_active', true)->count(),
            'total_ratings' => CustomerRating::count(),
        ];

        // Revenue by month (last 12 months)
        $monthlyRevenue = Laundry::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as laundries')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        // Top customers
        $topCustomers = Customer::withCount('laundries')
            ->with(['laundries' => function($query) {
                $query->where('payment_status', 'paid');
            }])
            ->having('laundries_count', '>', 0)
            ->orderBy('laundries_count', 'desc')
            ->limit(10)
            ->get();

        // Branch performance
        $branchStats = Branch::withCount('laundries')
            ->with(['laundries' => function($query) {
                $query->where('payment_status', 'paid');
            }])
            ->get()
            ->map(function($branch) {
                return [
                    'name' => $branch->name,
                    'laundries_count' => $branch->laundries_count,
                    'revenue' => $branch->laundries()->where('payment_status', 'paid')->sum('total_amount'),
                ];
            });

        return view('admin.reports.index', compact(
            'stats',
            'monthlyRevenue',
            'topCustomers',
            'branchStats'
        ));
    }

    /**
     * Revenue report
     */
    public function revenue(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        // Daily revenue data
        $data = Laundry::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as laundries')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Monthly revenue trends (last 12 months)
        $monthlyTrends = $this->getMonthlyRevenueTrends();

        // Revenue by service type
        $serviceRevenue = $this->getRevenueByService($startDate, $endDate);

        // Peak hours analysis
        $peakHours = $this->getPeakHoursRevenue($startDate, $endDate);

        // Payment method analytics
        $paymentMethods = $this->getPaymentMethodAnalytics($startDate, $endDate);

        // Revenue by branch
        $branchRevenue = $this->getRevenueBranches($startDate, $endDate);

        // Customer analytics
        $customerAnalytics = $this->getCustomerRevenueAnalytics($startDate, $endDate);

        // Seasonal patterns
        $seasonalData = $this->getSeasonalRevenue();

        return view('admin.reports.revenue', compact(
            'data', 'startDate', 'endDate', 'monthlyTrends', 'serviceRevenue', 
            'peakHours', 'paymentMethods', 'branchRevenue', 'customerAnalytics', 'seasonalData'
        ));
    }

    /**
     * Laundries report
     */
    public function laundries(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $laundries = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->with(['customer', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $summary = [
            'total_laundries' => Laundry::whereBetween('created_at', [$startDate, $endDate])->count(),
            'completed_laundries' => Laundry::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')->count(),
            'pending_laundries' => Laundry::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['pending', 'processing'])->count(),
            'total_revenue' => Laundry::whereBetween('created_at', [$startDate, $endDate])
                ->where('payment_status', 'paid')->sum('total_amount'),
        ];

        return view('admin.reports.laundries', compact('laundries', 'summary', 'startDate', 'endDate'));
    }

    /**
     * Customers report with filtering
     */
    public function customers(Request $request)
    {
        $dateFrom = $this->getDateFromFilter($request);
        $dateTo = $this->getDateToFilter($request);

        // Mark all ratings as viewed when accessing this page
        \App\Models\CustomerRating::whereNull('viewed_at')->update(['viewed_at' => now()]);

        // Build base query
        $query = Customer::withCount(['laundries' => function($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('created_at', [$dateFrom, $dateTo]);
        }])
        ->with(['laundries' => function($q) use ($dateFrom, $dateTo) {
            $q->where('payment_status', 'paid')
              ->whereBetween('created_at', [$dateFrom, $dateTo]);
        }, 'ratings']);

        // Apply rating filter if provided
        if ($request->filled('rating')) {
            $query = $this->applyRatingFilter($query, $request->get('rating'));
        }

        $customers = $query->paginate(50);

        // Calculate metrics
        $metrics = $this->calculateCustomerMetrics($customers, $dateFrom, $dateTo);

        return view('admin.reports.customers', compact('customers', 'metrics'));
    }

    /**
     * Calculate customer metrics for display
     */
    private function calculateCustomerMetrics($customers, $dateFrom, $dateTo)
    {
        $totalCustomers = Customer::count();
        $newCustomersPeriod = Customer::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $activeCustomers = Customer::whereHas('laundries', function($q) {
            $q->where('created_at', '>=', now()->subDays(30));
        })->count();
        $returningCustomers = Customer::whereHas('laundries', function($q) {
            $q->where('created_at', '<', now()->subDays(30));
        })->count();
        $inactiveCustomers = $totalCustomers - $activeCustomers;

        // Ratings data
        $ratedCustomers = $customers->filter(fn($c) => $c->ratings && $c->ratings->count() > 0);
        $avgRating = $ratedCustomers->count() > 0
            ? round($ratedCustomers->avg(fn($c) => $c->ratings->avg('rating')), 1)
            : 0;

        // Calculate aggregate values
        $totalOrdersPeriod = Laundry::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $totalRevenuePeriod = Laundry::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('payment_status', 'paid')
            ->sum('total_amount');
        $avgOrdersPerCustomer = $totalCustomers > 0 ? round($totalOrdersPeriod / $totalCustomers, 2) : 0;
        $avgSpendPeriod = $totalOrdersPeriod > 0 ? round($totalRevenuePeriod / $totalOrdersPeriod, 2) : 0;
        $avgCustomerValue = $totalCustomers > 0
            ? round(Laundry::where('payment_status', 'paid')->sum('total_amount') / $totalCustomers, 2)
            : 0;

        return [
            'total_customers' => $totalCustomers,
            'new_customers_period' => $newCustomersPeriod,
            'active_customers' => $activeCustomers,
            'inactive_customers' => $inactiveCustomers,
            'returning_customers' => $returningCustomers,
            'avg_rating' => $avgRating,
            'rated_customers_count' => $ratedCustomers->count(),
            'avg_orders_per_customer' => $avgOrdersPerCustomer,
            'total_orders_period' => $totalOrdersPeriod,
            'total_revenue_period' => $totalRevenuePeriod,
            'avg_spend_period' => $avgSpendPeriod,
            'avg_customer_value' => $avgCustomerValue,
        ];
    }

    /**
     * Get date from filter or default
     */
    private function getDateFromFilter(Request $request)
    {
        $filter = $request->get('filter', 'this_month');

        return match($filter) {
            'this_month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            'last_3_months' => now()->subMonths(3)->startOfDay(),
            'last_6_months' => now()->subMonths(6)->startOfDay(),
            'this_year' => now()->startOfYear(),
            'custom' => Carbon::parse($request->get('date_from', now()->startOfMonth())),
            default => now()->startOfMonth(),
        };
    }

    /**
     * Get date to filter or default
     */
    private function getDateToFilter(Request $request)
    {
        $filter = $request->get('filter', 'this_month');

        return match($filter) {
            'this_month' => now()->endOfMonth(),
            'last_month' => now()->subMonth()->endOfMonth(),
            'last_3_months' => now()->endOfDay(),
            'last_6_months' => now()->endOfDay(),
            'this_year' => now()->endOfYear(),
            'custom' => Carbon::parse($request->get('date_to', now()->endOfMonth())),
            default => now()->endOfMonth(),
        };
    }

    /**
     * Apply rating filter to query
     */
    private function applyRatingFilter($query, $ratingValue)
    {
        if ($ratingValue == 0) {
            // No ratings
            return $query->whereDoesntHave('ratings');
        }

        $rating = (int)$ratingValue;
        $minRating = $rating;
        $maxRating = $rating + 1;

        return $query->whereHas('ratings', function($q) use ($minRating, $maxRating) {
            $q->selectRaw('customer_id, AVG(rating) as avg_rating')
              ->groupByRaw('customer_id')
              ->havingRaw('AVG(rating) >= ? AND AVG(rating) < ?', [$minRating, $maxRating]);
        }, '>=', 1);
    }

    /**
     * Branches report
     */
    public function branches(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $branches = Branch::with(['laundries' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])
        ->withCount(['laundries' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])
        ->get()
        ->map(function($branch) use ($startDate, $endDate) {
            $revenue = $branch->laundries()->where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount');
            
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
                'laundries_count' => $branch->laundries_count,
                'revenue' => $revenue,
                'avg_laundry_value' => $branch->laundries_count > 0 ? $revenue / $branch->laundries_count : 0,
            ];
        });

        // Calculate totals for comparison
        $totalRevenue = $branches->sum('revenue');
        $totalLaundries = $branches->sum('laundries_count');
        
        // Add percentage calculations
        $branches = $branches->map(function($branch) use ($totalRevenue, $totalLaundries) {
            $branch['revenue_percentage'] = $totalRevenue > 0 ? round(($branch['revenue'] / $totalRevenue) * 100, 1) : 0;
            $branch['laundries_percentage'] = $totalLaundries > 0 ? round(($branch['laundries_count'] / $totalLaundries) * 100, 1) : 0;
            return $branch;
        })->sortByDesc('revenue')->values();

        // Performance leaderboard data
        $leaderboard = $branches->take(10)->map(function($branch, $index) {
            $branch['rank'] = $index + 1;
            return $branch;
        });

        return view('admin.reports.branches', compact('branches', 'leaderboard', 'totalRevenue', 'totalLaundries', 'startDate', 'endDate'));
    }

    /**
     * Branch Ratings Report
     */
    public function branchRatings(Request $request)
    {
        $filter = $request->get('filter', 'this_month');
        $branchId = $request->get('branch_id');
        $rating = $request->get('rating');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Mark all ratings as viewed when accessing this page
        \App\Models\CustomerRating::whereNull('viewed_at')->update(['viewed_at' => now()]);

        // Set date range based on filter
        switch($filter) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'this_week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'last_3_months':
                $startDate = Carbon::now()->subMonths(3)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'last_6_months':
                $startDate = Carbon::now()->subMonths(6)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'this_year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $startDate = $dateFrom ? Carbon::parse($dateFrom) : Carbon::now()->startOfMonth();
                $endDate = $dateTo ? Carbon::parse($dateTo)->endOfDay() : Carbon::now()->endOfMonth();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
        }

        // Get all active branches for filter dropdown
        $allBranches = Branch::where('is_active', true)
            ->select('id', 'name', 'code')
            ->get();

        // Get branches with their ratings
        $branches = Branch::where('is_active', true)
            ->with(['ratings' => function($query) use ($startDate, $endDate, $branchId, $rating) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->when($branchId, function($q) use ($branchId) {
                        return $q->where('branch_id', $branchId);
                    })
                    ->when($rating, function($q) use ($rating) {
                        return $q->where('rating', $rating);
                    })
                    ->with(['customer', 'laundry']);
            }])
            ->withAvg(['ratings' => function($query) use ($startDate, $endDate, $branchId, $rating) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->when($branchId, function($q) use ($branchId) {
                        return $q->where('branch_id', $branchId);
                    })
                    ->when($rating, function($q) use ($rating) {
                        return $q->where('rating', $rating);
                    });
            }], 'rating')
            ->withCount(['ratings as total_ratings' => function($query) use ($startDate, $endDate, $branchId, $rating) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->when($branchId, function($q) use ($branchId) {
                        return $q->where('branch_id', $branchId);
                    })
                    ->when($rating, function($q) use ($rating) {
                        return $q->where('rating', $rating);
                    });
            }])
            ->get()
            ->map(function($branch) use ($startDate, $endDate) {
                $ratings = $branch->ratings;
                
                // Calculate rating distribution
                $distribution = [
                    5 => $ratings->where('rating', 5)->count(),
                    4 => $ratings->where('rating', 4)->count(),
                    3 => $ratings->where('rating', 3)->count(),
                    2 => $ratings->where('rating', 2)->count(),
                    1 => $ratings->where('rating', 1)->count(),
                ];

                // Calculate monthly trend (last 3 months)
                $trend = [];
                for ($i = 2; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    $monthRatings = $ratings->filter(function($rating) use ($month) {
                        return $rating->created_at->month == $month->month && 
                               $rating->created_at->year == $month->year;
                    });
                    
                    $trend[] = [
                        'month' => $month->format('M'),
                        'rating' => $monthRatings->isNotEmpty() ? round($monthRatings->avg('rating'), 1) : 0,
                    ];
                }

                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'code' => $branch->code ?? substr($branch->name, 0, 3),
                    'total_ratings' => $branch->total_ratings,
                    'average_rating' => round($branch->ratings_avg_rating ?? 0, 2),
                    'distribution' => $distribution,
                    'trend' => $trend,
                ];
            })
            ->filter(function($branch) {
                return $branch['total_ratings'] > 0;
            })
            ->sortByDesc('average_rating')
            ->values();

        // Calculate overall statistics
        $totalRatings = DB::table('customer_ratings')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when($rating, function($query) use ($rating) {
                return $query->where('rating', $rating);
            })
            ->count();

        $totalComments = DB::table('customer_ratings')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when($rating, function($query) use ($rating) {
                return $query->where('rating', $rating);
            })
            ->count();

        $avgRating = DB::table('customer_ratings')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when($rating, function($query) use ($rating) {
                return $query->where('rating', $rating);
            })
            ->avg('rating') ?? 0;

        // Rating distribution for all branches combined
        $ratingDistribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = DB::table('customer_ratings')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('rating', $i)
                ->when($branchId, function($query) use ($branchId) {
                    return $query->where('branch_id', $branchId);
                })
                ->when($rating, function($query) use ($rating) {
                    return $query->where('rating', $rating);
                })
                ->count();
            
            $percentage = $totalRatings > 0 ? round(($count / $totalRatings) * 100, 1) : 0;
            
            $ratingDistribution[$i] = [
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        $summary = [
            'total_ratings' => $totalRatings,
            'total_comments' => $totalComments,
            'avg_rating' => round($avgRating, 2),
            'branches_with_ratings' => $branches->count(),
            'total_branches' => Branch::where('is_active', true)->count(),
            'rating_distribution' => $ratingDistribution,
        ];

        // Get top rated branches (as objects for the view) - using average_rating property
        $topBranches = $branches->sortByDesc('average_rating')
            ->take(3)
            ->map(function($branch) {
                return (object) $branch;
            });

        // Get branches that need improvement (lowest rated)
        $needsImprovement = $branches->sortBy('average_rating')
            ->take(3)
            ->map(function($branch) {
                return (object) $branch;
            });

        // Get recent ratings grouped by customer
        $query = CustomerRating::with(['branch', 'customer', 'laundry'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when($rating, function($query) use ($rating) {
                return $query->where('rating', $rating);
            })
            ->orderBy('created_at', 'desc');

        // Group ratings by customer
        $allRatings = $query->get();
        $customersWithRatings = $allRatings->groupBy('customer_id')->map(function($customerRatings) {
            $customer = $customerRatings->first()->customer;
            return (object) [
                'customer' => $customer,
                'average_rating' => round($customerRatings->avg('rating'), 1),
                'rating_count' => $customerRatings->count(),
                'latest_rating' => $customerRatings->first(),
                'all_ratings' => $customerRatings
            ];
        })->values();

        return view('admin.reports.branch-ratings', compact(
            'branches',
            'allBranches',
            'summary',
            'startDate',
            'endDate',
            'filter',
            'branchId',
            'rating',
            'topBranches',
            'needsImprovement',
            'customersWithRatings',
            'ratingDistribution'
        ));
    }

    /**
     * Profitability Analysis Report
     */
    public function profitability(Request $request)
    {
        $startDate = $request->get('start_date') ? \Carbon\Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? \Carbon\Carbon::parse($request->get('end_date')) : now()->endOfMonth();

        // Service profitability analysis
        $serviceProfitability = $this->getServiceProfitability($startDate, $endDate);
        
        // Branch profitability analysis
        $branchProfitability = $this->getBranchProfitability($startDate, $endDate);
        
        // Overall profitability metrics
        $overallMetrics = $this->getOverallProfitabilityMetrics($startDate, $endDate);
        
        // Cost breakdown analysis
        $costBreakdown = $this->getCostBreakdown($startDate, $endDate);
        
        // Profit trends over time
        $profitTrends = $this->getProfitTrends($startDate, $endDate);

        return view('admin.reports.profitability', compact(
            'serviceProfitability',
            'branchProfitability', 
            'overallMetrics',
            'costBreakdown',
            'profitTrends',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get service profitability analysis
     */
    private function getServiceProfitability($startDate, $endDate)
    {
        return DB::table('laundries')
            ->join('services', 'laundries.service_id', '=', 'services.id')
            ->whereBetween('laundries.created_at', [$startDate, $endDate])
            ->where('laundries.payment_status', 'paid')
            ->select(
                'services.name',
                'services.service_type',
                DB::raw('COUNT(laundries.id) as total_orders'),
                DB::raw('SUM(laundries.total_amount) as total_revenue'),
                DB::raw('AVG(laundries.total_amount) as avg_order_value'),
                // Estimated costs (30% of revenue as baseline)
                DB::raw('SUM(laundries.total_amount * 0.30) as estimated_costs'),
                DB::raw('SUM(laundries.total_amount * 0.70) as estimated_profit'),
                DB::raw('ROUND((SUM(laundries.total_amount * 0.70) / SUM(laundries.total_amount)) * 100, 2) as profit_margin')
            )
            ->groupBy('services.id', 'services.name', 'services.service_type')
            ->orderBy('estimated_profit', 'desc')
            ->get();
    }

    /**
     * Get branch profitability analysis
     */
    private function getBranchProfitability($startDate, $endDate)
    {
        return DB::table('laundries')
            ->join('branches', 'laundries.branch_id', '=', 'branches.id')
            ->whereBetween('laundries.created_at', [$startDate, $endDate])
            ->where('laundries.payment_status', 'paid')
            ->select(
                'branches.name',
                'branches.code',
                DB::raw('COUNT(laundries.id) as total_orders'),
                DB::raw('SUM(laundries.total_amount) as total_revenue'),
                DB::raw('AVG(laundries.total_amount) as avg_order_value'),
                // Estimated operational costs (35% for branches)
                DB::raw('SUM(laundries.total_amount * 0.35) as estimated_costs'),
                DB::raw('SUM(laundries.total_amount * 0.65) as estimated_profit'),
                DB::raw('ROUND((SUM(laundries.total_amount * 0.65) / SUM(laundries.total_amount)) * 100, 2) as profit_margin')
            )
            ->groupBy('branches.id', 'branches.name', 'branches.code')
            ->orderBy('estimated_profit', 'desc')
            ->get();
    }

    /**
     * Get overall profitability metrics
     */
    private function getOverallProfitabilityMetrics($startDate, $endDate)
    {
        $totalRevenue = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->sum('total_amount');
            
        $totalOrders = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->count();
            
        // Estimated costs breakdown
        $estimatedCosts = [
            'materials' => $totalRevenue * 0.15, // 15% for detergents, utilities
            'labor' => $totalRevenue * 0.20,     // 20% for staff wages
            'overhead' => $totalRevenue * 0.10,  // 10% for rent, equipment
            'total' => $totalRevenue * 0.45      // 45% total costs
        ];
        
        $grossProfit = $totalRevenue - $estimatedCosts['total'];
        $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        
        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'avg_order_value' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0,
            'estimated_costs' => $estimatedCosts,
            'gross_profit' => $grossProfit,
            'profit_margin' => round($profitMargin, 2)
        ];
    }

    /**
     * Get cost breakdown analysis
     */
    private function getCostBreakdown($startDate, $endDate)
    {
        $totalRevenue = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->sum('total_amount');
            
        return [
            'materials' => [
                'amount' => $totalRevenue * 0.15,
                'percentage' => 15,
                'description' => 'Detergents, fabric softeners, utilities'
            ],
            'labor' => [
                'amount' => $totalRevenue * 0.20,
                'percentage' => 20,
                'description' => 'Staff wages and benefits'
            ],
            'overhead' => [
                'amount' => $totalRevenue * 0.10,
                'percentage' => 10,
                'description' => 'Rent, equipment maintenance, insurance'
            ],
            'pickup_delivery' => [
                'amount' => Laundry::whereBetween('created_at', [$startDate, $endDate])
                    ->where('payment_status', 'paid')
                    ->sum(DB::raw('pickup_fee + delivery_fee')),
                'percentage' => 0, // Will be calculated
                'description' => 'Transportation and logistics costs'
            ]
        ];
    }

    /**
     * Get profit trends over time
     */
    private function getProfitTrends($startDate, $endDate)
    {
        return DB::table('laundries')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount * 0.45) as estimated_costs'),
                DB::raw('SUM(total_amount * 0.55) as estimated_profit')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Export branch ratings to CSV
     */
    public function exportBranchRatings(Request $request)
    {
        $filter = $request->get('filter', 'this_month');
        $branchId = $request->get('branch_id');
        $rating = $request->get('rating');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Set date range based on filter
        switch($filter) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'this_week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'last_3_months':
                $startDate = Carbon::now()->subMonths(3)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'last_6_months':
                $startDate = Carbon::now()->subMonths(6)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'this_year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $startDate = $dateFrom ? Carbon::parse($dateFrom) : Carbon::now()->startOfMonth();
                $endDate = $dateTo ? Carbon::parse($dateTo)->endOfDay() : Carbon::now()->endOfMonth();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
        }

        // Get data for export with filters
        $ratings = DB::table('customer_ratings')
            ->join('branches', 'customer_ratings.branch_id', '=', 'branches.id')
            ->join('customers', 'customer_ratings.customer_id', '=', 'customers.id')
            ->join('laundries', 'customer_ratings.laundry_id', '=', 'laundries.id')
            ->whereBetween('customer_ratings.created_at', [$startDate, $endDate])
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('customer_ratings.branch_id', $branchId);
            })
            ->when($rating, function($query) use ($rating) {
                return $query->where('customer_ratings.rating', $rating);
            })
            ->select(
                'branches.name as branch_name',
                'branches.code as branch_code',
                'customers.name as customer_name',
                'customers.email as customer_email',
                'customers.phone as customer_phone',
                'customer_ratings.rating',
                'customer_ratings.comment',
                'customer_ratings.created_at',
                'laundries.tracking_number'
            )
            ->orderBy('customer_ratings.created_at', 'desc')
            ->get();

        $filename = 'branch-ratings-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function() use ($ratings) {
            $handle = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($handle, [
                'Branch Name',
                'Branch Code',
                'Customer Name',
                'Customer Email',
                'Customer Phone',
                'Rating',
                'Comment',
                'Order #',
                'Date'
            ]);
            
            // Add data
            foreach ($ratings as $rating) {
                fputcsv($handle, [
                    $rating->branch_name,
                    $rating->branch_code,
                    $rating->customer_name,
                    $rating->customer_email,
                    $rating->customer_phone,
                    $rating->rating . ' ★',
                    $rating->comment,
                    $rating->tracking_number,
                    Carbon::parse($rating->created_at)->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export report
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'laundries');
        $format = $request->get('format', 'csv');

        $filename = "{$type}_report_" . now()->format('Y-m-d') . ".{$format}";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        switch ($type) {
            case 'laundries':
                return $this->exportLaundries($request, $headers);
            case 'revenue':
                return $this->exportRevenue($request, $headers);
            case 'customers':
                return $this->exportCustomers($request, $headers);
            case 'branch-ratings':
                return $this->exportBranchRatings($request);
            default:
                return redirect()->back()->with('error', 'Invalid export type');
        }
    }

    /**
     * Get monthly revenue trends for last 12 months
     */
    private function getMonthlyRevenueTrends()
    {
        return Laundry::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as laundries'),
                DB::raw('AVG(total_amount) as avg_order_value')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();
    }

    /**
     * Get revenue breakdown by service type
     */
    private function getRevenueByService($startDate, $endDate)
    {
        return Laundry::where('laundries.payment_status', 'paid')
            ->whereBetween('laundries.created_at', [$startDate, $endDate])
            ->join('services', 'laundries.service_id', '=', 'services.id')
            ->select(
                'services.name',
                'services.service_type',
                DB::raw('SUM(laundries.total_amount) as revenue'),
                DB::raw('COUNT(laundries.id) as count'),
                DB::raw('AVG(laundries.total_amount) as avg_value')
            )
            ->groupBy('services.id', 'services.name', 'services.service_type')
            ->orderBy('revenue', 'desc')
            ->get();
    }

    /**
     * Get peak hours revenue analysis
     */
    private function getPeakHoursRevenue($startDate, $endDate)
    {
        return Laundry::where('payment_status', 'paid')
            ->whereBetween('laundries.created_at', [$startDate, $endDate])
            ->select(
                DB::raw('HOUR(laundries.created_at) as hour'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as laundries')
            )
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get();
    }

    /**
     * Get payment method analytics
     */
    private function getPaymentMethodAnalytics($startDate, $endDate)
    {
        return Laundry::where('payment_status', 'paid')
            ->whereBetween('laundries.created_at', [$startDate, $endDate])
            ->select(
                'payment_method',
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(total_amount) as avg_value')
            )
            ->groupBy('payment_method')
            ->orderBy('revenue', 'desc')
            ->get();
    }

    /**
     * Get revenue by branches
     */
    private function getRevenueBranches($startDate, $endDate)
    {
        return Branch::with(['laundries' => function($query) use ($startDate, $endDate) {
            $query->where('payment_status', 'paid')
                  ->whereBetween('laundries.created_at', [$startDate, $endDate]);
        }])
        ->get()
        ->map(function($branch) use ($startDate, $endDate) {
            $revenue = $branch->laundries()->where('payment_status', 'paid')
                ->whereBetween('laundries.created_at', [$startDate, $endDate])
                ->sum('total_amount');
            $count = $branch->laundries()->where('payment_status', 'paid')
                ->whereBetween('laundries.created_at', [$startDate, $endDate])
                ->count();
            
            return [
                'name' => $branch->name,
                'revenue' => $revenue,
                'count' => $count,
                'avg_value' => $count > 0 ? $revenue / $count : 0
            ];
        })
        ->sortByDesc('revenue')
        ->values();
    }

    /**
     * Get customer revenue analytics
     */
    private function getCustomerRevenueAnalytics($startDate, $endDate)
    {
        $totalRevenue = Laundry::where('payment_status', 'paid')
            ->whereBetween('laundries.created_at', [$startDate, $endDate])
            ->sum('total_amount');
        
        $totalCustomers = Customer::whereHas('laundries', function($query) use ($startDate, $endDate) {
            $query->where('payment_status', 'paid')
                  ->whereBetween('laundries.created_at', [$startDate, $endDate]);
        })->count();

        $avgCustomerValue = $totalCustomers > 0 ? $totalRevenue / $totalCustomers : 0;

        $topCustomers = Customer::withSum(['laundries' => function($query) use ($startDate, $endDate) {
            $query->where('payment_status', 'paid')
                  ->whereBetween('laundries.created_at', [$startDate, $endDate]);
        }], 'total_amount')
        ->having('laundries_sum_total_amount', '>', 0)
        ->orderBy('laundries_sum_total_amount', 'desc')
        ->limit(10)
        ->get();

        return [
            'total_revenue' => $totalRevenue,
            'total_customers' => $totalCustomers,
            'avg_customer_value' => $avgCustomerValue,
            'top_customers' => $topCustomers
        ];
    }

    /**
     * Get seasonal revenue patterns
     */
    private function getSeasonalRevenue()
    {
        return Laundry::where('payment_status', 'paid')
            ->where('laundries.created_at', '>=', now()->subYear())
            ->select(
                DB::raw('MONTH(laundries.created_at) as month'),
                DB::raw('MONTHNAME(laundries.created_at) as month_name'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as laundries')
            )
            ->groupBy('month', 'month_name')
            ->orderBy('month', 'asc')
            ->get();
    }

    /**
     * Export laundries to CSV
     */
    private function exportLaundries(Request $request, array $headers)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $laundries = Laundry::whereBetween('created_at', [$startDate, $endDate])
            ->with(['customer', 'branch'])
            ->get();

        return response()->stream(function() use ($laundries) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Laundry ID',
                'Tracking Number',
                'Customer',
                'Branch',
                'Status',
                'Payment Status',
                'Amount',
                'Date'
            ]);

            // Data rows
            foreach ($laundries as $laundry) {
                fputcsv($file, [
                    $laundry->id,
                    $laundry->tracking_number,
                    $laundry->customer->name ?? 'N/A',
                    $laundry->branch->name ?? 'N/A',
                    $laundry->status,
                    $laundry->payment_status,
                    $laundry->total_amount,
                    $laundry->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Export revenue to CSV
     */
    private function exportRevenue(Request $request, array $headers)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $data = Laundry::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as laundries')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->stream(function() use ($data) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Date', 'Revenue', 'Laundries']);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->date,
                    $row->revenue,
                    $row->laundries,
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Export customers to CSV
     */
    private function exportCustomers(Request $request, array $headers)
    {
        $dateFrom = $this->getDateFromFilter($request);
        $dateTo = $this->getDateToFilter($request);

        $customers = Customer::withCount(['laundries' => function($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('created_at', [$dateFrom, $dateTo]);
        }])
        ->with(['laundries' => function($q) use ($dateFrom, $dateTo) {
            $q->where('payment_status', 'paid')->whereBetween('created_at', [$dateFrom, $dateTo]);
        }, 'ratings'])
        ->get();

        return response()->stream(function() use ($customers) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Customer ID',
                'Name',
                'Email',
                'Phone',
                'Total Laundries',
                'Total Spent',
                'Avg Rating',
                'Rating Count',
                'Registration Date'
            ]);

            foreach ($customers as $customer) {
                $avgRating = $customer->ratings?->avg('rating') ?? 0;
                $ratingCount = $customer->ratings?->count() ?? 0;

                fputcsv($file, [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $customer->phone,
                    $customer->laundries_count,
                    $customer->laundries()->where('payment_status', 'paid')->sum('total_amount'),
                    round($avgRating, 1),
                    $ratingCount,
                    $customer->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }
}