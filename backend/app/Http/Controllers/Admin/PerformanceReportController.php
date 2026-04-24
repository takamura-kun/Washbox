<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Laundry;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\BranchStock;
use App\Models\Attendance;
use App\Models\User;
use App\Models\PickupRequest;
use App\Models\Promotion;
use App\Models\RetailSale;
use App\Models\StockTransfer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PerformanceReportController extends Controller
{
    protected $cacheExpiry = 3600; // Cache for 1 hour

    public function index(Request $request)
    {
        try {
            $branchId = $request->get('branch_id');
            $dateRange = $request->get('date_range', 'this_month');
            $viewType = $request->get('view_type', 'summary'); // summary or detailed

            $dates = $this->getDateRange($dateRange);
            $cacheKey = "perf_report_{$branchId}_{$dateRange}_{$viewType}";

            // Try to get from cache first
            $cachedData = Cache::get($cacheKey);
            if ($cachedData && isset($cachedData['data']['operations']['pipeline_by_branch'])) {
                return view('admin.performance-report.index', $cachedData);
            }

            // Get period-over-period data for comparisons
            $previousDates = $this->getPreviousPeriodDates($dates);

            $data = [
                'branches' => Branch::active()->get(),
                'selectedBranch' => $branchId,
                'dateRange' => $dateRange,
                'viewType' => $viewType,
                'dates' => $dates,
                'executive' => $this->getExecutiveData($branchId, $dates, $previousDates),
                'operations' => $this->getOperationsData($branchId, $dates, $previousDates, Branch::active()->get()),
                'financial' => $this->getFinancialData($branchId, $dates, $previousDates),
                'inventory' => $this->getInventoryData($branchId, $dates),
                'staff' => $this->getStaffData($branchId, $dates, $previousDates),
                'customers' => $this->getCustomersData($branchId, $dates, $previousDates),
            ];

            // Cache the results
            Cache::put($cacheKey, $data, $this->cacheExpiry);

            return view('admin.performance-report.index', $data);
        } catch (\Exception $e) {
            \Log::error('Performance Report Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->with('error', 'Error loading performance report: ' . $e->getMessage());
        }
    }

    /**
     * Get previous period dates for comparison
     */
    private function getPreviousPeriodDates($dates)
    {
        $start = Carbon::parse($dates['start']);
        $end = Carbon::parse($dates['end']);
        $difference = $end->diffInDays($start);

        $prevEnd = $start->clone()->subDay();
        $prevStart = $prevEnd->clone()->subDays($difference);

        return [
            'start' => $prevStart,
            'end' => $prevEnd,
            'type' => $dates['type']
        ];
    }
    
    private function getExecutiveData($branchId, $dates, $previousDates)
    {
        // Current period data
        $query = Laundry::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->whereIn('status', ['paid', 'completed'])
            ->whereNotNull('paid_at');
        if ($branchId) $query->where('branch_id', $branchId);

        $laundryRevenue = $query->sum('total_amount');
        $totalOrders = $query->count();

        // Add retail sales to total revenue
        $retailQuery = RetailSale::whereBetween('created_at', [$dates['start'], $dates['end']]);
        if ($branchId) $retailQuery->where('branch_id', $branchId);
        $retailRevenue = $retailQuery->sum('total_amount');

        $totalRevenue = $laundryRevenue + $retailRevenue;

        $activeCustomers = Customer::whereHas('laundries', function($q) use ($dates, $branchId) {
            $q->whereBetween('created_at', [$dates['start'], $dates['end']]);
            if ($branchId) $q->where('branch_id', $branchId);
        })->count();

        $expensesQuery = Expense::whereBetween('expense_date', [$dates['start'], $dates['end']]);
        if ($branchId) $expensesQuery->where('branch_id', $branchId);
        $totalExpenses = $expensesQuery->sum('amount');

        $profit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0;

        // Previous period data for comparisons
        $prevQuery = Laundry::whereBetween('created_at', [$previousDates['start'], $previousDates['end']])
            ->whereIn('status', ['paid', 'completed'])
            ->whereNotNull('paid_at');
        if ($branchId) $prevQuery->where('branch_id', $branchId);
        $prevLaundryRevenue = $prevQuery->sum('total_amount');
        $prevTotalOrders = $prevQuery->count();

        $prevRetailQuery = RetailSale::whereBetween('created_at', [$previousDates['start'], $previousDates['end']]);
        if ($branchId) $prevRetailQuery->where('branch_id', $branchId);
        $prevRetailRevenue = $prevRetailQuery->sum('total_amount');
        $prevTotalRevenue = $prevLaundryRevenue + $prevRetailRevenue;

        // Calculate growth percentages
        $revenueGrowth = $prevTotalRevenue > 0 ? (($totalRevenue - $prevTotalRevenue) / $prevTotalRevenue) * 100 : 0;
        $ordersGrowth = $prevTotalOrders > 0 ? (($totalOrders - $prevTotalOrders) / $prevTotalOrders) * 100 : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'active_customers' => $activeCustomers,
            'profit_margin' => round($profitMargin, 1),
            'revenue_growth' => round($revenueGrowth, 1),
            'orders_growth' => round($ordersGrowth, 1),
            'revenue_trend' => $this->getRevenueTrend($branchId, $dates),
            'alerts' => $this->getKeyAlerts($branchId),
            'branch_comparison' => $this->getBranchComparison($dates),
            'revenue_by_service_breakdown' => $this->getRevenueByServiceBreakdown($branchId, $dates),
        ];
    }
    
    private function getOperationsData($branchId, $dates, $previousDates, $branches = null)
    {
        $query = Laundry::whereBetween('created_at', [$dates['start'], $dates['end']]);
        if ($branchId) $query->where('branch_id', $branchId);

        $statusCounts = (clone $query)->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $avgProcessingTimeQuery = Laundry::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->whereNotNull('received_at')
            ->whereNotNull('ready_at');
        if ($branchId) $avgProcessingTimeQuery->where('branch_id', $branchId);

        $avgProcessingTime = $avgProcessingTimeQuery
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, received_at, ready_at)) as avg_hours')
            ->value('avg_hours');

        // Previous period data
        $prevQuery = Laundry::whereBetween('created_at', [$previousDates['start'], $previousDates['end']]);
        if ($branchId) $prevQuery->where('branch_id', $branchId);
        $prevTotalOrders = $prevQuery->count();

        // Calculate growth
        $currentOrders = $query->count();
        $ordersGrowth = $prevTotalOrders > 0 ? (($currentOrders - $prevTotalOrders) / $prevTotalOrders) * 100 : 0;

        return [
            'total_orders' => $currentOrders,
            'pending' => $statusCounts['received'] ?? 0,
            'processing' => $statusCounts['processing'] ?? 0,
            'ready' => $statusCounts['ready'] ?? 0,
            'completed' => $statusCounts['completed'] ?? 0,
            'cancelled' => $statusCounts['cancelled'] ?? 0,
            'avg_processing_time' => round($avgProcessingTime ?? 0, 1),
            'orders_growth' => round($ordersGrowth, 1),
            'orders_by_service' => $this->getOrdersByService($branchId, $dates),
            'orders_by_branch' => $this->getOrdersByBranch($dates, $branchId),
            'peak_hours' => $this->getPeakHours($branchId, $dates),
            'processing_time_trend' => $this->getProcessingTimeTrend($branchId, $dates),
            'service_efficiency_breakdown' => $this->getServiceEfficiencyBreakdown($branchId, $dates),
            'revenue_vs_expenses_trend' => $this->getTrendRevenueVsExpenses($branchId),
            'service_demand_trend' => $this->getTrendServiceDemand($branchId),
            'staff_attendance_trend' => $this->getTrendStaffAttendance($branchId),
            'profit_trend' => $this->getTrendProfit($branchId),
            'laundry_status_trend' => $this->getTrendLaundryStatus($branchId),
            'pipeline_by_branch' => $this->getPipelineByBranch($branchId, $branches),
        ];
    }
    
    private function getFinancialData($branchId, $dates, $previousDates)
    {
        $revenueQuery = Laundry::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->whereIn('status', ['paid', 'completed'])
            ->whereNotNull('paid_at');
        if ($branchId) $revenueQuery->where('branch_id', $branchId);

        $laundryRevenue = $revenueQuery->sum('total_amount');

        // Add retail sales to total revenue
        $retailQuery = RetailSale::whereBetween('created_at', [$dates['start'], $dates['end']]);
        if ($branchId) $retailQuery->where('branch_id', $branchId);
        $retailRevenue = $retailQuery->sum('total_amount');

        $totalRevenue = $laundryRevenue + $retailRevenue;

        $expensesQuery = Expense::whereBetween('expense_date', [$dates['start'], $dates['end']]);
        if ($branchId) $expensesQuery->where('branch_id', $branchId);

        $totalExpenses = $expensesQuery->sum('amount');
        $expensesByCategory = (clone $expensesQuery)
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.name')
            ->get();

        $profit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0;

        // Previous period data
        $prevRevenueQuery = Laundry::whereBetween('created_at', [$previousDates['start'], $previousDates['end']])
            ->whereIn('status', ['paid', 'completed'])
            ->whereNotNull('paid_at');
        if ($branchId) $prevRevenueQuery->where('branch_id', $branchId);
        $prevLaundryRevenue = $prevRevenueQuery->sum('total_amount');

        $prevRetailQuery = RetailSale::whereBetween('created_at', [$previousDates['start'], $previousDates['end']]);
        if ($branchId) $prevRetailQuery->where('branch_id', $branchId);
        $prevRetailRevenue = $prevRetailQuery->sum('total_amount');
        $prevTotalRevenue = $prevLaundryRevenue + $prevRetailRevenue;

        $prevExpensesQuery = Expense::whereBetween('expense_date', [$previousDates['start'], $previousDates['end']]);
        if ($branchId) $prevExpensesQuery->where('branch_id', $branchId);
        $prevTotalExpenses = $prevExpensesQuery->sum('amount');

        $profitGrowth = (($profit - ($prevTotalRevenue - $prevTotalExpenses)) / abs($prevTotalRevenue - $prevTotalExpenses ?: 1)) * 100;

        $paymentMethodsQuery = Laundry::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->whereIn('status', ['paid', 'completed'])
            ->whereNotNull('paid_at');
        if ($branchId) $paymentMethodsQuery->where('branch_id', $branchId);

        $paymentMethods = $paymentMethodsQuery
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        return [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'profit' => $profit,
            'profit_margin' => round($profitMargin, 1),
            'profit_growth' => round($profitGrowth, 1),
            'laundry_revenue' => $laundryRevenue,
            'retail_revenue' => $retailRevenue,
            'revenue_vs_expenses' => $this->getRevenueVsExpenses($branchId, $dates),
            'expenses_by_category' => $expensesByCategory,
            'payment_methods' => $paymentMethods,
            'revenue_by_branch' => $this->getRevenueByBranch($dates, $branchId),
            'monthly_profit_trend' => $this->getMonthlyProfitTrend($branchId, $dates),
            'daily_profit' => $this->getDailyProfit($branchId, $dates),
            'laundry_vs_retail_trend' => $this->getLaundryVsRetailTrend($branchId, $dates),
            'cumulative_revenue' => $this->getCumulativeRevenue($branchId, $dates),
        ];
    }
    
    private function getInventoryData($branchId, $dates)
    {
        $stockQuery = BranchStock::with('inventoryItem');
        if ($branchId) $stockQuery->where('branch_id', $branchId);
        
        $stocks = $stockQuery->get();
        
        $lowStock = $stocks->filter(function($stock) {
            return $stock->current_stock <= $stock->reorder_point && $stock->current_stock > 0;
        })->count();
        
        $outOfStock = $stocks->filter(function($stock) {
            return $stock->current_stock == 0;
        })->count();
        
        $totalValue = $stocks->sum(function($stock) {
            return $stock->current_stock * ($stock->cost_price ?? 0);
        });
        
        return [
            'total_items' => $stocks->count(),
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'total_value' => $totalValue,
            'stock_levels' => $this->getStockLevels($branchId),
            'top_used_items' => $this->getTopUsedItems($branchId, $dates),
            'stock_value_by_category' => $this->getStockValueByCategory($branchId),
            'purchase_trend' => $this->getPurchaseTrend($branchId, $dates),
        ];
    }
    
    private function getStaffData($branchId, $dates, $previousDates)
    {
        $staffQuery = User::where('role', 'staff')->where('is_active', true);
        if ($branchId) {
            $staffQuery->whereHas('salaryInfo', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        
        $totalStaff = $staffQuery->count();
        
        $attendanceQuery = Attendance::whereBetween('attendance_date', [$dates['start'], $dates['end']]);
        if ($branchId) $attendanceQuery->where('branch_id', $branchId);
        
        $attendanceData = $attendanceQuery->get();
        
        $presentCount = $attendanceData->whereIn('status', ['present', 'late'])->count();
        $totalDays = $attendanceData->count();
        $attendanceRate = $totalDays > 0 ? ($presentCount / $totalDays) * 100 : 0;
        
        $avgHoursWorked = $attendanceData->avg('hours_worked') ?? 0;
        $totalOvertimeHours = $attendanceData->sum(function($att) {
            return $att->hours_worked > 8 ? $att->hours_worked - 8 : 0;
        });
        
        return [
            'total_staff' => $totalStaff,
            'attendance_rate' => round($attendanceRate, 1),
            'avg_hours_worked' => round($avgHoursWorked, 1),
            'total_overtime' => round($totalOvertimeHours, 1),
            'attendance_trend' => $this->getAttendanceTrend($branchId, $dates),
            'staff_by_status' => $this->getStaffByStatus($branchId, $dates),
            'staff_productivity' => $this->getStaffProductivity($branchId, $dates),
            'leave_requests' => $this->getLeaveRequests($branchId, $dates),
        ];
    }
    
    private function getCustomersData($branchId, $dates, $previousDates)
    {
        $customerQuery = Customer::whereHas('laundries', function($q) use ($dates, $branchId) {
            $q->whereBetween('created_at', [$dates['start'], $dates['end']]);
            if ($branchId) $q->where('branch_id', $branchId);
        });
        
        $totalCustomers = $customerQuery->count();
        
        $newCustomers = Customer::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->when($branchId, function($q) use ($branchId) {
                $q->where('preferred_branch_id', $branchId);
            })
            ->count();
        
        $avgRating = DB::table('customer_ratings')
            ->whereBetween('created_at', [$dates['start'], $dates['end']])
            ->when($branchId, function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->avg('rating') ?? 0;
        
        return [
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'avg_rating' => round($avgRating, 1),
            'retention_rate' => $this->getRetentionRate($branchId, $dates),
            'customer_growth' => $this->getCustomerGrowth($branchId, $dates),
            'top_customers' => $this->getTopCustomers($branchId, $dates),
            'customer_segmentation' => $this->getCustomerSegmentation($branchId, $dates),
            'satisfaction_distribution' => $this->getSatisfactionDistribution($branchId, $dates),
        ];
    }
    
    // Helper methods
    private function getRevenueTrend($branchId, $dates)
    {
        $groupBy = $this->getGroupByFormat($dates);
        
        // Get laundry revenue (only paid orders)
        $laundryQuery = Laundry::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->whereIn('status', ['paid', 'completed'])
            ->whereNotNull('paid_at')
            ->selectRaw("{$groupBy['select']} as period, SUM(total_amount) as revenue")
            ->groupBy('period')
            ->orderBy('period');
            
        if ($branchId) $laundryQuery->where('branch_id', $branchId);
        
        $laundryData = $laundryQuery->get()->keyBy('period');
        
        // Get retail revenue
        $retailQuery = RetailSale::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->selectRaw("{$groupBy['select']} as period, SUM(total_amount) as revenue")
            ->groupBy('period')
            ->orderBy('period');
            
        if ($branchId) $retailQuery->where('branch_id', $branchId);
        
        $retailData = $retailQuery->get()->keyBy('period');
        
        // Combine laundry and retail revenue
        $allPeriods = $laundryData->keys()->merge($retailData->keys())->unique();
        
        $combinedData = $allPeriods->map(function($period) use ($laundryData, $retailData) {
            $laundryRevenue = $laundryData->has($period) ? $laundryData[$period]->revenue : 0;
            $retailRevenue = $retailData->has($period) ? $retailData[$period]->revenue : 0;
            
            return (object)[
                'period' => $period,
                'revenue' => $laundryRevenue + $retailRevenue
            ];
        });
        
        return $this->fillMissingDates($combinedData, $dates, 'revenue');
    }
    
    private function getKeyAlerts($branchId)
    {
        $alerts = [];
        
        $lowStockQuery = InventoryItem::whereHas('branchStocks', function($q) use ($branchId) {
            $q->whereColumn('current_stock', '<=', 'reorder_point');
            if ($branchId) $q->where('branch_id', $branchId);
        });
        $lowStock = $lowStockQuery->count();
        
        if ($lowStock > 0) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Low Inventory Alert',
                'message' => "$lowStock items below reorder point",
                'icon' => 'exclamation-circle'
            ];
        }
        
        $unclaimedQuery = Laundry::where('status', 'ready')
            ->whereNull('paid_at')
            ->whereDate('ready_at', '<=', now()->subDays(3));
        if ($branchId) $unclaimedQuery->where('branch_id', $branchId);
        $unclaimed = $unclaimedQuery->count();
            
        if ($unclaimed > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Unclaimed Items',
                'message' => "$unclaimed orders ready for pickup",
                'icon' => 'clock'
            ];
        }
        
        $today = now()->toDateString();
        $staffQuery = User::where('role', 'staff')->where('is_active', true);
        if ($branchId) {
            $staffQuery->whereHas('salaryInfo', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        $totalStaff = $staffQuery->count();
        
        $presentQuery = Attendance::where('attendance_date', $today)
            ->whereIn('status', ['present', 'late']);
        if ($branchId) $presentQuery->where('branch_id', $branchId);
        $presentStaff = $presentQuery->count();
        
        $attendanceRate = $totalStaff > 0 ? round(($presentStaff / $totalStaff) * 100) : 0;
        
        $alerts[] = [
            'type' => 'success',
            'title' => 'Staff Attendance',
            'message' => "$attendanceRate% attendance rate",
            'icon' => 'check-circle'
        ];
        
        return $alerts;
    }
    
    private function getBranchComparison($dates)
    {
        return Branch::active()->get()->map(function($branch) use ($dates) {
            $laundryRevenue = Laundry::where('branch_id', $branch->id)
                ->whereBetween('created_at', [$dates['start'], $dates['end']])
                ->whereIn('status', ['paid', 'completed'])
                ->whereNotNull('paid_at')
                ->sum('total_amount');
            
            $retailRevenue = RetailSale::where('branch_id', $branch->id)
                ->whereBetween('created_at', [$dates['start'], $dates['end']])
                ->sum('total_amount');
            
            $totalRevenue = $laundryRevenue + $retailRevenue;
                
            $expenses = Expense::where('branch_id', $branch->id)
                ->whereBetween('expense_date', [$dates['start'], $dates['end']])
                ->sum('amount');
                
            return [
                'name' => $branch->name,
                'revenue' => $totalRevenue,
                'profit' => $totalRevenue - $expenses,
            ];
        });
    }
    
    private function getOrdersByService($branchId, $dates)
    {
        $query = Laundry::whereBetween('laundries.created_at', [$dates['start'], $dates['end']])
            ->join('services', 'laundries.service_id', '=', 'services.id')
            ->select('services.name', DB::raw('COUNT(*) as count'))
            ->groupBy('services.name');
            
        if ($branchId) $query->where('laundries.branch_id', $branchId);
        
        return $query->get();
    }
    
    private function getOrdersByBranch($dates, $branchId = null)
    {
        $query = Laundry::whereBetween('laundries.created_at', [$dates['start'], $dates['end']])
            ->join('branches', 'laundries.branch_id', '=', 'branches.id')
            ->select('branches.name', DB::raw('COUNT(*) as count'))
            ->groupBy('branches.name');
            
        if ($branchId) $query->where('laundries.branch_id', $branchId);
        
        return $query->get();
    }
    
    private function getPeakHours($branchId, $dates)
    {
        $query = Laundry::whereBetween('laundries.created_at', [$dates['start'], $dates['end']])
            ->selectRaw('HOUR(laundries.created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour');
            
        if ($branchId) $query->where('branch_id', $branchId);
        
        return $query->get();
    }
    
    private function getProcessingTimeTrend($branchId, $dates)
    {
        $groupBy = $this->getGroupByFormat($dates);
        
        $query = Laundry::whereBetween('laundries.created_at', [$dates['start'], $dates['end']])
            ->whereNotNull('received_at')
            ->whereNotNull('ready_at')
            ->selectRaw("{$groupBy['select']} as period, AVG(TIMESTAMPDIFF(HOUR, received_at, ready_at)) as avg_hours")
            ->groupBy('period')
            ->orderBy('period');
            
        if ($branchId) $query->where('branch_id', $branchId);
        
        $data = $query->get();
        
        return $this->fillMissingDates($data, $dates, 'avg_hours');
    }
    
    private function getRevenueVsExpenses($branchId, $dates)
    {
        $groupBy = $this->getGroupByFormat($dates);
        
        // Get laundry revenue (only paid orders)
        $laundryRevenue = Laundry::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->whereIn('status', ['paid', 'completed'])
            ->whereNotNull('paid_at')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw("{$groupBy['select']} as period, SUM(total_amount) as amount")
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');
        
        // Get retail revenue
        $retailRevenue = RetailSale::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw("{$groupBy['select']} as period, SUM(total_amount) as amount")
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');
        
        // Combine laundry and retail revenue
        $allPeriods = $laundryRevenue->keys()->merge($retailRevenue->keys())->unique();
        
        $combinedRevenue = $allPeriods->map(function($period) use ($laundryRevenue, $retailRevenue) {
            $laundry = $laundryRevenue->has($period) ? $laundryRevenue[$period]->amount : 0;
            $retail = $retailRevenue->has($period) ? $retailRevenue[$period]->amount : 0;
            
            return (object)[
                'period' => $period,
                'amount' => $laundry + $retail
            ];
        });
            
        $expenses = Expense::whereBetween('expense_date', [$dates['start'], $dates['end']])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw(str_replace('created_at', 'expense_date', $groupBy['select']) . " as period, SUM(amount) as amount")
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        
        return [
            'revenue' => $this->fillMissingDates($combinedRevenue, $dates, 'amount'),
            'expenses' => $this->fillMissingDates($expenses, $dates, 'amount')
        ];
    }
    
    private function getRevenueByBranch($dates, $branchId = null)
    {
        // Get laundry revenue by branch (only paid orders)
        $laundryRevenue = Laundry::whereBetween('laundries.created_at', [$dates['start'], $dates['end']])
            ->whereIn('laundries.status', ['paid', 'completed'])
            ->whereNotNull('laundries.paid_at')
            ->join('branches', 'laundries.branch_id', '=', 'branches.id')
            ->select('branches.name', 'branches.id', DB::raw('SUM(laundries.total_amount) as total'))
            ->groupBy('branches.name', 'branches.id');
            
        if ($branchId) $laundryRevenue->where('laundries.branch_id', $branchId);
        
        $laundryData = $laundryRevenue->get()->keyBy('name');
        
        // Get retail revenue by branch
        $retailRevenue = RetailSale::whereBetween('retail_sales.created_at', [$dates['start'], $dates['end']])
            ->join('branches', 'retail_sales.branch_id', '=', 'branches.id')
            ->select('branches.name', 'branches.id', DB::raw('SUM(retail_sales.total_amount) as total'))
            ->groupBy('branches.name', 'branches.id');
            
        if ($branchId) $retailRevenue->where('retail_sales.branch_id', $branchId);
        
        $retailData = $retailRevenue->get()->keyBy('name');
        
        // Combine laundry and retail revenue
        $allBranches = $laundryData->keys()->merge($retailData->keys())->unique();
        
        return $allBranches->map(function($branchName) use ($laundryData, $retailData) {
            $laundry = $laundryData->has($branchName) ? $laundryData[$branchName]->total : 0;
            $retail = $retailData->has($branchName) ? $retailData[$branchName]->total : 0;
            
            return (object)[
                'name' => $branchName,
                'total' => $laundry + $retail
            ];
        })->values();
    }
    
    private function getStockLevels($branchId)
    {
        $query = BranchStock::with('inventoryItem')
            ->orderBy('current_stock', 'desc')
            ->limit(10);
            
        if ($branchId) $query->where('branch_id', $branchId);
        
        return $query->get();
    }
    
    private function getTopUsedItems($branchId, $dates)
    {
        return DB::table('laundry_inventory_items')
            ->join('inventory_items', 'laundry_inventory_items.inventory_item_id', '=', 'inventory_items.id')
            ->join('laundries', 'laundry_inventory_items.laundries_id', '=', 'laundries.id')
            ->whereBetween('laundries.created_at', [$dates['start'], $dates['end']])
            ->when($branchId, fn($q) => $q->where('laundries.branch_id', $branchId))
            ->select('inventory_items.name', DB::raw('SUM(laundry_inventory_items.quantity) as total_used'))
            ->groupBy('inventory_items.name')
            ->orderBy('total_used', 'desc')
            ->limit(10)
            ->get();
    }
    
    private function getStockValueByCategory($branchId)
    {
        $query = BranchStock::join('inventory_items', 'branch_stocks.inventory_item_id', '=', 'inventory_items.id')
            ->join('inventory_categories', 'inventory_items.category_id', '=', 'inventory_categories.id')
            ->select('inventory_categories.name', DB::raw('SUM(branch_stocks.current_stock * branch_stocks.cost_price) as value'))
            ->groupBy('inventory_categories.name');
            
        if ($branchId) $query->where('branch_stocks.branch_id', $branchId);
        
        return $query->get();
    }
    
    private function getPurchaseTrend($branchId, $dates)
    {
        $groupBy = $this->getGroupByFormat($dates);
        
        $query = DB::table('inventory_purchases')
            ->whereBetween('purchase_date', [$dates['start'], $dates['end']])
            ->selectRaw(str_replace('created_at', 'purchase_date', $groupBy['select']) . " as period, SUM(grand_total) as amount")
            ->groupBy('period')
            ->orderBy('period');
            
        if ($branchId) $query->where('branch_id', $branchId);
        
        $data = $query->get();
        
        return $this->fillMissingDates($data, $dates, 'amount');
    }
    
    private function getAttendanceTrend($branchId, $dates)
    {
        $groupBy = $this->getGroupByFormat($dates);
        
        $query = Attendance::whereBetween('attendance_date', [$dates['start'], $dates['end']])
            ->selectRaw(str_replace('created_at', 'attendance_date', $groupBy['select']) . " as period, 
                COUNT(CASE WHEN status IN ('present', 'late') THEN 1 END) as present,
                COUNT(*) as total")
            ->groupBy('period')
            ->orderBy('period');
            
        if ($branchId) $query->where('branch_id', $branchId);
        
        $data = $query->get();
        
        return $this->fillMissingDates($data, $dates, 'present', 'total');
    }
    
    private function getStaffByStatus($branchId, $dates)
    {
        $query = Attendance::whereBetween('attendance_date', [$dates['start'], $dates['end']])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status');
            
        if ($branchId) $query->where('branch_id', $branchId);
        
        return $query->get();
    }
    
    private function getStaffProductivity($branchId, $dates)
    {
        $query = Laundry::whereBetween('laundries.created_at', [$dates['start'], $dates['end']])
            ->whereNotNull('staff_id')
            ->join('users', 'laundries.staff_id', '=', 'users.id')
            ->select('users.name', DB::raw('COUNT(*) as orders_processed'))
            ->groupBy('users.name')
            ->orderBy('orders_processed', 'desc')
            ->limit(10);
            
        if ($branchId) $query->where('laundries.branch_id', $branchId);
        
        return $query->get();
    }
    
    private function getLeaveRequests($branchId, $dates)
    {
        $query = DB::table('leave_requests')
            ->whereBetween('leave_date_from', [$dates['start'], $dates['end']])
            ->select('leave_type', DB::raw('COUNT(*) as count'))
            ->groupBy('leave_type');
            
        if ($branchId) {
            $query->whereIn('user_id', function($q) use ($branchId) {
                $q->select('user_id')
                    ->from('staff_salary_info')
                    ->where('branch_id', $branchId);
            });
        }
        
        return $query->get();
    }
    
    private function getCustomerGrowth($branchId, $dates)
    {
        $groupBy = $this->getGroupByFormat($dates);
        
        $query = Customer::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->selectRaw("{$groupBy['select']} as period, COUNT(*) as count")
            ->groupBy('period')
            ->orderBy('period');
            
        if ($branchId) $query->where('preferred_branch_id', $branchId);
        
        $data = $query->get();
        
        return $this->fillMissingDates($data, $dates, 'count');
    }
    
    private function getTopCustomers($branchId, $dates)
    {
        $query = Customer::whereHas('laundries', function($q) use ($dates, $branchId) {
                $q->whereBetween('created_at', [$dates['start'], $dates['end']]);
                if ($branchId) $q->where('branch_id', $branchId);
            })
            ->withSum(['laundries' => function($q) use ($dates, $branchId) {
                $q->whereBetween('created_at', [$dates['start'], $dates['end']]);
                if ($branchId) $q->where('branch_id', $branchId);
            }], 'total_amount')
            ->orderBy('laundries_sum_total_amount', 'desc')
            ->limit(10);
            
        return $query->get();
    }
    
    private function getCustomerSegmentation($branchId, $dates)
    {
        $query = Customer::whereHas('laundries', function($q) use ($dates, $branchId) {
                $q->whereBetween('created_at', [$dates['start'], $dates['end']]);
                if ($branchId) $q->where('branch_id', $branchId);
            })
            ->select('registration_type', DB::raw('COUNT(*) as count'))
            ->groupBy('registration_type');
            
        return $query->get();
    }
    
    private function getSatisfactionDistribution($branchId, $dates)
    {
        $query = DB::table('customer_ratings')
            ->whereBetween('created_at', [$dates['start'], $dates['end']])
            ->select('rating', DB::raw('COUNT(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating', 'desc');
            
        if ($branchId) $query->where('branch_id', $branchId);
        
        return $query->get();
    }
    
    private function getRetentionRate($branchId, $dates)
    {
        $previousPeriod = [
            'start' => Carbon::parse($dates['start'])->subMonth(),
            'end' => Carbon::parse($dates['end'])->subMonth()
        ];
        
        $previousCustomers = Customer::whereHas('laundries', function($q) use ($previousPeriod, $branchId) {
            $q->whereBetween('created_at', [$previousPeriod['start'], $previousPeriod['end']]);
            if ($branchId) $q->where('branch_id', $branchId);
        })->pluck('id');
        
        $returningCustomers = Customer::whereIn('id', $previousCustomers)
            ->whereHas('laundries', function($q) use ($dates, $branchId) {
                $q->whereBetween('created_at', [$dates['start'], $dates['end']]);
                if ($branchId) $q->where('branch_id', $branchId);
            })->count();
        
        $retentionRate = $previousCustomers->count() > 0 
            ? ($returningCustomers / $previousCustomers->count()) * 100 
            : 0;
            
        return round($retentionRate, 1);
    }
    
    private function getDateRange($range)
    {
        return match($range) {
            'today' => ['start' => now()->startOfDay(), 'end' => now()->endOfDay(), 'type' => 'hourly'],
            'yesterday' => ['start' => now()->subDay()->startOfDay(), 'end' => now()->subDay()->endOfDay(), 'type' => 'hourly'],
            'this_week' => ['start' => now()->startOfWeek(Carbon::SUNDAY), 'end' => now()->endOfWeek(Carbon::SATURDAY), 'type' => 'daily'],
            'last_week' => ['start' => now()->subWeek()->startOfWeek(Carbon::SUNDAY), 'end' => now()->subWeek()->endOfWeek(Carbon::SATURDAY), 'type' => 'daily'],
            'this_month' => ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth(), 'type' => 'daily'],
            'last_month' => ['start' => now()->subMonth()->startOfMonth(), 'end' => now()->subMonth()->endOfMonth(), 'type' => 'daily'],
            'this_year' => ['start' => now()->startOfYear(), 'end' => now()->endOfYear(), 'type' => 'monthly'],
            default => ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth(), 'type' => 'daily'],
        };
    }
    
    private function getGroupByFormat($dates)
    {
        return match($dates['type']) {
            'hourly'  => ['select' => 'HOUR(created_at)',                     'select_prefixed' => 'HOUR(laundries.created_at)',                     'format' => 'H:00'],
            'daily'   => ['select' => 'DATE(created_at)',                     'select_prefixed' => 'DATE(laundries.created_at)',                     'format' => 'M d'],
            'monthly' => ['select' => "DATE_FORMAT(created_at, '%Y-%m')",     'select_prefixed' => "DATE_FORMAT(laundries.created_at, '%Y-%m')",     'format' => 'M Y'],
            default   => ['select' => 'DATE(created_at)',                     'select_prefixed' => 'DATE(laundries.created_at)',                     'format' => 'M d'],
        };
    }

    private function fillMissingDates($data, $dates, $valueField, $secondField = null)
    {
        if (!$data) {
            $data = collect();
        }
        
        $filled = collect();
        $dataByPeriod = $data->keyBy('period');
        
        if ($dates['type'] === 'hourly') {
            // Fill hours 0-23
            for ($hour = 0; $hour < 24; $hour++) {
                $period = $hour;
                $item = (object)[
                    'period' => $period,
                    $valueField => $dataByPeriod->has($period) ? ($dataByPeriod[$period]->$valueField ?? 0) : 0
                ];
                if ($secondField) {
                    $item->$secondField = $dataByPeriod->has($period) ? ($dataByPeriod[$period]->$secondField ?? 0) : 0;
                }
                $filled->push($item);
            }
        } elseif ($dates['type'] === 'monthly') {
            // Fill all months in the year
            $start = Carbon::parse($dates['start']);
            $end = Carbon::parse($dates['end']);
            
            while ($start <= $end) {
                $period = $start->format('Y-m');
                $item = (object)[
                    'period' => $period,
                    $valueField => $dataByPeriod->has($period) ? ($dataByPeriod[$period]->$valueField ?? 0) : 0
                ];
                if ($secondField) {
                    $item->$secondField = $dataByPeriod->has($period) ? ($dataByPeriod[$period]->$secondField ?? 0) : 0;
                }
                $filled->push($item);
                $start->addMonth();
            }
        } else {
            // Fill all days in the range
            $start = Carbon::parse($dates['start']);
            $end = Carbon::parse($dates['end']);
            
            while ($start <= $end) {
                $period = $start->format('Y-m-d');
                $item = (object)[
                    'period' => $period,
                    $valueField => $dataByPeriod->has($period) ? ($dataByPeriod[$period]->$valueField ?? 0) : 0
                ];
                if ($secondField) {
                    $item->$secondField = $dataByPeriod->has($period) ? ($dataByPeriod[$period]->$secondField ?? 0) : 0;
                }
                $filled->push($item);
                $start->addDay();
            }
        }
        
        return $filled;
    }

    /**
     * Get revenue breakdown by service type for executive dashboard
     */
    private function getRevenueByServiceBreakdown($branchId, $dates)
    {
        $groupBy = $this->getGroupByFormat($dates);

        $services = Laundry::join('services', 'laundries.service_id', '=', 'services.id')
            ->whereBetween('laundries.created_at', [$dates['start'], $dates['end']])
            ->whereIn('laundries.status', ['paid', 'completed'])
            ->whereNotNull('laundries.paid_at')
            ->when($branchId, fn($q) => $q->where('laundries.branch_id', $branchId))
            ->select('services.name')
            ->distinct()->pluck('name');

        $periodData = Laundry::join('services', 'laundries.service_id', '=', 'services.id')
            ->whereBetween('laundries.created_at', [$dates['start'], $dates['end']])
            ->whereIn('laundries.status', ['paid', 'completed'])
            ->whereNotNull('laundries.paid_at')
            ->when($branchId, fn($q) => $q->where('laundries.branch_id', $branchId))
            ->selectRaw(str_replace('created_at', 'laundries.created_at', $groupBy['select']) . " as period, services.name as service_name, SUM(laundries.total_amount) as revenue")
            ->groupBy('period', 'service_name')
            ->orderBy('period')
            ->get()
            ->groupBy('service_name');

        $allPeriods = $this->fillMissingDates(collect(), $dates, 'revenue')->pluck('period');

        $datasets = $services->map(function($name) use ($periodData, $allPeriods) {
            $byPeriod = ($periodData[$name] ?? collect())->keyBy('period');
            $data = $allPeriods->map(fn($p) => (float) ($byPeriod[$p]->revenue ?? 0));
            return ['label' => $name, 'data' => $data->values()];
        });

        $labels = $allPeriods->map(function($p) use ($dates) {
            if ($dates['type'] === 'hourly') return $p . ':00';
            if ($dates['type'] === 'monthly') return \Carbon\Carbon::parse($p . '-01')->format('M Y');
            return \Carbon\Carbon::parse($p)->format('M d');
        });

        return ['labels' => $labels->values(), 'datasets' => $datasets->values()];
    }

    /**
     * Get service efficiency metrics (completion rate, avg time)
     */
    private function getServiceEfficiencyBreakdown($branchId, $dates)
    {
        $query = Laundry::join('services', 'laundries.service_id', '=', 'services.id')
            ->whereBetween('laundries.created_at', [$dates['start'], $dates['end']])
            ->select(
                'services.name',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(CASE WHEN laundries.status = "completed" THEN 1 ELSE 0 END) as completed_orders'),
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, laundries.received_at, laundries.ready_at)) as avg_processing_hours')
            )
            ->groupBy('services.name');

        if ($branchId) $query->where('laundries.branch_id', $branchId);

        return $query->get()->map(function($item) {
            $item->completion_rate = $item->total_orders > 0 ? round(($item->completed_orders / $item->total_orders) * 100, 1) : 0;
            return $item;
        });
    }

    /**
     * Get monthly profit trend for financial section
     */
    private function getMonthlyProfitTrend($branchId, $dates)
    {
        // Get monthly revenue and expenses
        $laundryRevenue = Laundry::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->whereIn('status', ['paid', 'completed'])
            ->whereNotNull('paid_at')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as amount")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $retailRevenue = RetailSale::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as amount")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $expenses = Expense::whereBetween('expense_date', [$dates['start'], $dates['end']])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as month, SUM(amount) as amount")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $allMonths = $laundryRevenue->keys()->merge($retailRevenue->keys())->merge($expenses->keys())->unique()->sort();

        return $allMonths->map(function($month) use ($laundryRevenue, $retailRevenue, $expenses) {
            $totalRevenue = ($laundryRevenue[$month]->amount ?? 0) + ($retailRevenue[$month]->amount ?? 0);
            $totalExpenses = $expenses[$month]->amount ?? 0;

            return (object)[
                'month' => $month,
                'revenue' => $totalRevenue,
                'expenses' => $totalExpenses,
                'profit' => $totalRevenue - $totalExpenses
            ];
        });
    }

    private function getDailyProfit($branchId, $dates)
    {
        $groupBy = $this->getGroupByFormat($dates);

        $revenue = Laundry::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->whereIn('status', ['paid', 'completed'])
            ->whereNotNull('paid_at')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw("{$groupBy['select']} as period, SUM(total_amount) as amount")
            ->groupBy('period')->orderBy('period')->get()->keyBy('period');

        $retail = RetailSale::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw("{$groupBy['select']} as period, SUM(total_amount) as amount")
            ->groupBy('period')->orderBy('period')->get()->keyBy('period');

        $expenses = Expense::whereBetween('expense_date', [$dates['start'], $dates['end']])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw(str_replace('created_at', 'expense_date', $groupBy['select']) . " as period, SUM(amount) as amount")
            ->groupBy('period')->orderBy('period')->get()->keyBy('period');

        $filled = $this->fillMissingDates(collect(), $dates, 'amount');
        return $filled->map(function($item) use ($revenue, $retail, $expenses) {
            $rev = ($revenue[$item->period]->amount ?? 0) + ($retail[$item->period]->amount ?? 0);
            $exp = $expenses[$item->period]->amount ?? 0;
            return (object)['period' => $item->period, 'profit' => $rev - $exp];
        });
    }

    private function getLaundryVsRetailTrend($branchId, $dates)
    {
        $groupBy = $this->getGroupByFormat($dates);

        $laundry = Laundry::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->whereIn('status', ['paid', 'completed'])
            ->whereNotNull('paid_at')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw("{$groupBy['select']} as period, SUM(total_amount) as amount")
            ->groupBy('period')->orderBy('period')->get()->keyBy('period');

        $retail = RetailSale::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw("{$groupBy['select']} as period, SUM(total_amount) as amount")
            ->groupBy('period')->orderBy('period')->get()->keyBy('period');

        $filled = $this->fillMissingDates(collect(), $dates, 'amount');
        return $filled->map(function($item) use ($laundry, $retail) {
            return (object)[
                'period'  => $item->period,
                'laundry' => (float) ($laundry[$item->period]->amount ?? 0),
                'retail'  => (float) ($retail[$item->period]->amount ?? 0),
            ];
        });
    }

    private function getCumulativeRevenue($branchId, $dates)
    {
        $groupBy = $this->getGroupByFormat($dates);

        $laundry = Laundry::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->whereIn('status', ['paid', 'completed'])
            ->whereNotNull('paid_at')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw("{$groupBy['select']} as period, SUM(total_amount) as amount")
            ->groupBy('period')->orderBy('period')->get()->keyBy('period');

        $retail = RetailSale::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw("{$groupBy['select']} as period, SUM(total_amount) as amount")
            ->groupBy('period')->orderBy('period')->get()->keyBy('period');

        $filled = $this->fillMissingDates(collect(), $dates, 'amount');
        $cumulative = 0;
        return $filled->map(function($item) use ($laundry, $retail, &$cumulative) {
            $cumulative += ($laundry[$item->period]->amount ?? 0) + ($retail[$item->period]->amount ?? 0);
            return (object)['period' => $item->period, 'cumulative' => (float) $cumulative];
        });
    }

    private function getTrendRevenueVsExpenses($branchId = null)
    {
        $labels = [];
        $revenue = [];
        $expenses = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $labels[] = $date->format('D');
            $rev = Laundry::whereDate('created_at', $date)
                ->whereIn('status', ['paid', 'completed'])
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->sum('total_amount')
                + RetailSale::whereDate('created_at', $date)
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->sum('total_amount');
            $exp = Expense::whereDate('expense_date', $date)
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->sum('amount');
            $revenue[] = (float) $rev;
            $expenses[] = (float) $exp;
        }
        return compact('labels', 'revenue', 'expenses');
    }

    private function getTrendServiceDemand($branchId = null)
    {
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels[] = today()->subDays($i)->format('D');
        }
        $services = Laundry::join('services', 'laundries.service_id', '=', 'services.id')
            ->when($branchId, fn($q) => $q->where('laundries.branch_id', $branchId))
            ->whereBetween('laundries.created_at', [today()->subDays(6)->startOfDay(), now()])
            ->select('services.name')
            ->distinct()->pluck('name');
        $datasets = [];
        foreach ($services as $name) {
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = today()->subDays($i);
                $data[] = Laundry::join('services', 'laundries.service_id', '=', 'services.id')
                    ->where('services.name', $name)
                    ->whereDate('laundries.created_at', $date)
                    ->when($branchId, fn($q) => $q->where('laundries.branch_id', $branchId))
                    ->count();
            }
            $datasets[] = ['label' => $name, 'data' => $data];
        }
        return compact('labels', 'datasets');
    }

    private function getTrendStaffAttendance($branchId = null)
    {
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels[] = today()->subDays($i)->format('D');
        }
        $branchList = $branchId ? Branch::where('id', $branchId)->get() : Branch::active()->get();
        $datasets = [];
        foreach ($branchList as $branch) {
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = today()->subDays($i);
                $data[] = Attendance::where('branch_id', $branch->id)
                    ->where('attendance_date', $date)
                    ->whereIn('status', ['present', 'late'])
                    ->count();
            }
            $datasets[] = ['label' => $branch->name, 'data' => $data];
        }
        return compact('labels', 'datasets');
    }

    private function getTrendProfit($branchId = null)
    {
        $labels = [];
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $labels[] = $date->format('M d');
            $rev = Laundry::whereDate('created_at', $date)
                ->whereIn('status', ['paid', 'completed'])
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->sum('total_amount')
                + RetailSale::whereDate('created_at', $date)
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->sum('total_amount');
            $exp = Expense::whereDate('expense_date', $date)
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->sum('amount');
            $data[] = (float) ($rev - $exp);
        }
        return compact('labels', 'data');
    }

    private function getTrendLaundryStatus($branchId = null)
    {
        $statusMap = ['received' => 'Received', 'ready' => 'Ready', 'paid' => 'Paid', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels[] = today()->subDays($i)->format('D');
        }
        $datasets = [];
        foreach ($statusMap as $dbStatus => $label) {
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = today()->subDays($i);
                $data[] = Laundry::whereDate('created_at', $date)
                    ->where('status', $dbStatus)
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->count();
            }
            $datasets[] = ['label' => $label, 'data' => $data];
        }
        return compact('labels', 'datasets');
    }

    private function getPipelineByBranch($branchId = null, $branches = null)
    {
        $branchList = $branchId
            ? Branch::where('id', $branchId)->get()
            : ($branches ?? Branch::active()->get());
        $statuses = ['received', 'ready', 'paid', 'completed', 'cancelled'];
        return $branchList->map(function($branch) use ($statuses) {
            $counts = [];
            foreach ($statuses as $s) {
                $counts[$s] = Laundry::where('branch_id', $branch->id)->where('status', $s)->count();
            }
            return [
                'name' => $branch->name,
                'total' => array_sum($counts),
                'counts' => $counts,
            ];
        })->values();
    }
}
