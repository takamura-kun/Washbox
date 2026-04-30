<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Laundry, Expense, RetailSale, Branch, FinancialTransaction, CashFlowRecord, InventoryPurchase};
use App\Services\FinancialTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialDashboardController extends Controller
{
    protected $financialService;

    public function __construct(FinancialTransactionService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index(Request $request)
    {
        $period = $request->input('period', 'month');
        $branchId = $request->input('branch_id');
        $dates = $this->getDateRange($period);

        $data = [
            'period' => $period,
            'branchId' => $branchId,
            'dates' => $dates,
            'branches' => Branch::active()->get(),
            'summary' => $this->getEnhancedSummary($dates, $branchId),
            'salesBreakdown' => $this->getSalesBreakdown($dates, $branchId),
            'expenseBreakdown' => $this->getExpenseBreakdown($dates, $branchId),
            'profitTrend' => $this->getProfitTrend($period, $branchId),
            'topExpenseCategories' => $this->getTopExpenseCategories($dates, $branchId),
            'branchComparison' => $this->getBranchComparison($dates),
            'recentTransactions' => $this->getRecentTransactions($branchId),
            'cashFlowSummary' => $this->getCashFlowSummary($dates, $branchId),
            'topServices' => $this->getTopServices($dates, $branchId),
            'growthMetrics' => $this->getGrowthMetrics($period, $branchId),
            'paymentMethodBreakdown' => $this->getPaymentMethodBreakdown($dates, $branchId),
        ];

        return view('admin.finance.dashboard', $data);
    }

    private function getDateRange($period)
    {
        $now = now();
        
        // Handle period formats like "month:1" or just "month"
        if (str_contains($period, ':')) {
            [$periodType, $periodNumber] = explode(':', $period, 2);
            $periodNumber = (int) $periodNumber;
        } else {
            $periodType = $period;
            $periodNumber = null;
        }
        
        return match($periodType) {
            'week' => [
                'start' => $now->clone()->startOfWeek(),
                'end' => $now->clone()->endOfWeek(),
                'label' => 'This Week',
            ],
            'month' => [
                'start' => $now->clone()->startOfMonth(),
                'end' => $now->clone()->endOfMonth(),
                'label' => 'This Month',
            ],
            'quarter' => [
                'start' => $now->clone()->startOfQuarter(),
                'end' => $now->clone()->endOfQuarter(),
                'label' => 'This Quarter',
            ],
            'year' => [
                'start' => $now->clone()->startOfYear(),
                'end' => $now->clone()->endOfYear(),
                'label' => 'This Year',
            ],
            default => [
                'start' => $now->clone()->startOfMonth(),
                'end' => $now->clone()->endOfMonth(),
                'label' => 'This Month',
            ],
        };
    }

    private function getEnhancedSummary($dates, $branchId = null)
    {
        $current = $this->getSummary($dates, $branchId);
        $previous = $this->getSummary($this->getPreviousPeriod($dates), $branchId);

        return array_merge($current, [
            'salesGrowth' => $this->calculateGrowth($current['totalSales'], $previous['totalSales']),
            'expenseGrowth' => $this->calculateGrowth($current['totalCosts'], $previous['totalCosts']),
            'profitGrowth' => $this->calculateGrowth($current['netProfit'], $previous['netProfit']),
            'previousSales' => $previous['totalSales'],
            'previousExpenses' => $previous['totalCosts'],
            'previousProfit' => $previous['netProfit'],
        ]);
    }

    private function getPreviousPeriod($dates)
    {
        $diff = $dates['start']->diffInDays($dates['end']);
        return [
            'start' => $dates['start']->copy()->subDays($diff + 1),
            'end' => $dates['start']->copy()->subDay(),
        ];
    }

    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function getRecentTransactions($branchId = null, $limit = 10)
    {
        return FinancialTransaction::with(['branch'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->latest('transaction_date')
            ->limit($limit)
            ->get();
    }

    private function getCashFlowSummary($dates, $branchId = null)
    {
        // Get today's date for real-time data
        $today = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        
        // Calculate today's cash in from laundry sales
        $todayLaundrySales = Laundry::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('paid_at', [$today, $todayEnd])
            ->sum('total_amount');
        
        // Calculate today's cash in from retail sales
        $todayRetailSales = RetailSale::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$today, $todayEnd])
            ->sum('total_amount');
        
        // Calculate today's cash out from expenses (excluding inventory-related)
        // Inventory purchases are already counted separately
        $todayExpenses = Expense::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('expense_date', [$today, $todayEnd])
            ->whereNull('inventory_purchase_id') // Exclude inventory expenses
            ->sum('amount');
        
        $todayCashIn = $todayLaundrySales + $todayRetailSales;
        $todayCashOut = $todayExpenses; // No double-counting
        $todayNetCashFlow = $todayCashIn - $todayCashOut;
        
        // Calculate cumulative balance (all time)
        $allTimeSales = Laundry::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['paid', 'completed'])
            ->whereNotNull('paid_at')
            ->sum('total_amount');
        
        $allTimeRetail = RetailSale::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');
        
        $allTimeExpenses = Expense::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereNull('inventory_purchase_id') // Exclude inventory expenses
            ->sum('amount');
        
        // Add inventory purchases separately
        $allTimeInventory = InventoryPurchase::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_cost');
        
        $currentBalance = ($allTimeSales + $allTimeRetail) - ($allTimeExpenses + $allTimeInventory);
        
        // Period totals
        $periodCashIn = Laundry::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('paid_at', [$dates['start'], $dates['end']])
            ->sum('total_amount') +
            RetailSale::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$dates['start'], $dates['end']])
            ->sum('total_amount');
        
        $periodCashOut = Expense::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('expense_date', [$dates['start'], $dates['end']])
            ->whereNull('inventory_purchase_id') // Exclude inventory expenses
            ->sum('amount') +
            InventoryPurchase::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('purchase_date', [$dates['start'], $dates['end']])
            ->sum('total_cost');

        return [
            'today_cash_in' => $todayCashIn,
            'today_cash_out' => $todayCashOut,
            'today_net_cash_flow' => $todayNetCashFlow,
            'total_cash_in' => $periodCashIn,
            'total_cash_out' => $periodCashOut,
            'net_cash_flow' => $periodCashIn - $periodCashOut,
            'average_daily_flow' => $dates['start']->diffInDays($dates['end']) > 0 
                ? ($periodCashIn - $periodCashOut) / $dates['start']->diffInDays($dates['end']) 
                : 0,
            'latest_balance' => $currentBalance,
        ];
    }

    private function getTopServices($dates, $branchId = null, $limit = 5)
    {
        return Laundry::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('paid_at', [$dates['start'], $dates['end']])
            ->select('service_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as revenue'))
            ->with('service')
            ->groupBy('service_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(fn($item) => [
                'name' => $item->service->name ?? 'Unknown',
                'count' => $item->count,
                'revenue' => $item->revenue,
            ]);
    }

    private function getGrowthMetrics($period, $branchId = null)
    {
        $currentDates = $this->getDateRange($period);
        $previousDates = $this->getPreviousPeriod($currentDates);

        $currentSummary = $this->getSummary($currentDates, $branchId);
        $previousSummary = $this->getSummary($previousDates, $branchId);

        return [
            'revenue_growth' => $this->calculateGrowth($currentSummary['totalSales'], $previousSummary['totalSales']),
            'expense_growth' => $this->calculateGrowth($currentSummary['totalCosts'], $previousSummary['totalCosts']),
            'profit_growth' => $this->calculateGrowth($currentSummary['netProfit'], $previousSummary['netProfit']),
            'transaction_growth' => $this->calculateGrowth(
                $currentSummary['laundryCount'] + $currentSummary['retailCount'],
                $previousSummary['laundryCount'] + $previousSummary['retailCount']
            ),
        ];
    }

    private function getPaymentMethodBreakdown($dates, $branchId = null)
    {
        $laundryPayments = Laundry::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('paid_at', [$dates['start'], $dates['end']])
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        $retailPayments = RetailSale::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$dates['start'], $dates['end']])
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        $combined = [];
        foreach ($laundryPayments->concat($retailPayments) as $payment) {
            $method = $payment->payment_method ?? 'cash';
            $combined[$method] = ($combined[$method] ?? 0) + $payment->total;
        }

        return collect($combined)->map(fn($total, $method) => [
            'method' => ucfirst($method),
            'total' => $total,
        ])->values();
    }

    private function getSummary($dates, $branchId = null)
    {
        $query = Laundry::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('paid_at', [$dates['start'], $dates['end']]);

        $laundrySales = $query->sum('total_amount');
        $laundryCount = $query->count();
        
        // Calculate COGS (Cost of Goods Sold) from inventory used in laundry
        // Note: inventory_cost column doesn't exist yet, defaulting to 0
        $laundryCOGS = 0; // $query->sum('inventory_cost');

        $retailQuery = RetailSale::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$dates['start'], $dates['end']]);

        $retailSales = $retailQuery->sum('total_amount');
        $retailCount = $retailQuery->count();

        // REMOVED: Pickup/delivery fees are already included in total_amount
        $pickupDeliveryFees = 0;

        $totalSales = $laundrySales + $retailSales;

        $expenseQuery = Expense::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('expense_date', [$dates['start'], $dates['end']]);

        // Get operating expenses only (excluding inventory)
        $operatingExpenses = $expenseQuery->clone()->whereNull('inventory_purchase_id')->sum('amount');
        
        // Get inventory purchases separately
        $inventoryCost = InventoryPurchase::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('purchase_date', [$dates['start'], $dates['end']])
            ->sum('total_cost');
        
        $totalExpenses = $operatingExpenses + $inventoryCost;

        // Enhanced profit calculations
        $totalCosts = $totalExpenses; // Only expenses, no double-counting
        $grossProfit = $totalSales - $laundryCOGS; // Revenue minus direct costs
        $operatingProfit = $grossProfit - $totalExpenses; // Gross profit minus operating expenses
        $netProfit = $totalSales - $totalCosts; // Total revenue minus all costs
        $profitMargin = $totalSales > 0 ? ($netProfit / $totalSales) * 100 : 0;
        $grossMargin = $totalSales > 0 ? ($grossProfit / $totalSales) * 100 : 0;

        return [
            'totalSales' => $totalSales,
            'laundrySales' => $laundrySales,
            'laundryCount' => $laundryCount,
            'retailSales' => $retailSales,
            'retailCount' => $retailCount,
            'pickupDeliveryFees' => $pickupDeliveryFees,
            'totalExpenses' => $totalExpenses,
            'inventoryCost' => $inventoryCost,
            'laundryCOGS' => $laundryCOGS,
            'totalCosts' => $totalCosts,
            'grossProfit' => $grossProfit,
            'operatingProfit' => $operatingProfit,
            'netProfit' => $netProfit,
            'profitMargin' => round($profitMargin, 2),
            'grossMargin' => round($grossMargin, 2),
        ];
    }

    private function getSalesBreakdown($dates, $branchId = null)
    {
        $pickupFees = Laundry::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('paid_at', [$dates['start'], $dates['end']])
            ->sum(DB::raw('COALESCE(pickup_fee, 0) + COALESCE(delivery_fee, 0)'));

        return [
            'laundry' => Laundry::query()
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereIn('status', ['paid', 'completed'])
                ->whereBetween('paid_at', [$dates['start'], $dates['end']])
                ->sum('total_amount'),
            'retail' => RetailSale::query()
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereBetween('created_at', [$dates['start'], $dates['end']])
                ->sum('total_amount'),
            'fees' => $pickupFees,
        ];
    }

    private function getExpenseBreakdown($dates, $branchId = null)
    {
        return Expense::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('expense_date', [$dates['start'], $dates['end']])
            ->select('expense_category_id', DB::raw('SUM(amount) as total'))
            ->with('category')
            ->groupBy('expense_category_id')
            ->get()
            ->map(fn($e) => [
                'category' => $e->category->name ?? 'Unknown',
                'amount' => $e->total,
            ]);
    }

    private function getProfitTrend($period, $branchId = null)
    {
        $days = $period === 'week' ? 7 : ($period === 'month' ? 30 : ($period === 'quarter' ? 90 : 365));
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $nextDate = $date->clone()->endOfDay();

            $laundrySales = Laundry::query()
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereIn('status', ['paid', 'completed'])
                ->whereBetween('paid_at', [$date, $nextDate])
                ->sum('total_amount');

            $retailSales = RetailSale::query()
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereBetween('created_at', [$date, $nextDate])
                ->sum('total_amount');

            $totalSales = $laundrySales + $retailSales;

            $expenses = Expense::query()
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereBetween('expense_date', [$date, $nextDate])
                ->whereNull('inventory_purchase_id')
                ->sum('amount') +
                InventoryPurchase::query()
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereBetween('purchase_date', [$date, $nextDate])
                ->sum('total_cost');

            $data[] = [
                'date' => $date->format('M d'),
                'sales' => $totalSales,
                'expenses' => $expenses,
                'profit' => $totalSales - $expenses,
            ];
        }

        return $data;
    }

    private function getTopExpenseCategories($dates, $branchId = null, $limit = 5)
    {
        return Expense::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('expense_date', [$dates['start'], $dates['end']])
            ->select('expense_category_id', DB::raw('SUM(amount) as total'))
            ->with('category')
            ->groupBy('expense_category_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn($e) => [
                'category' => $e->category->name ?? 'Unknown',
                'amount' => $e->total,
            ]);
    }

    private function getBranchComparison($dates)
    {
        return Branch::active()
            ->get()
            ->map(function ($branch) use ($dates) {
                $laundrySales = Laundry::where('branch_id', $branch->id)
                    ->whereIn('status', ['paid', 'completed'])
                    ->whereBetween('paid_at', [$dates['start'], $dates['end']])
                    ->sum('total_amount');

                $retailSales = RetailSale::where('branch_id', $branch->id)
                    ->whereBetween('created_at', [$dates['start'], $dates['end']])
                    ->sum('total_amount');

                $totalSales = $laundrySales + $retailSales;

                $expenses = Expense::where('branch_id', $branch->id)
                    ->whereBetween('expense_date', [$dates['start'], $dates['end']])
                    ->whereNull('inventory_purchase_id')
                    ->sum('amount') +
                    InventoryPurchase::where('branch_id', $branch->id)
                    ->whereBetween('purchase_date', [$dates['start'], $dates['end']])
                    ->sum('total_cost');

                return [
                    'name' => $branch->name,
                    'sales' => $totalSales,
                    'expenses' => $expenses,
                    'profit' => $totalSales - $expenses,
                ];
            });
    }

    public function salesReport(Request $request)
    {
        $period = $request->input('period', 'month');
        $branchId = $request->input('branch_id');
        $dates = $this->getDateRange($period);

        $sales = Laundry::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('paid_at', [$dates['start'], $dates['end']])
            ->with(['branch', 'customer'])
            ->orderBy('paid_at', 'desc')
            ->paginate(20);

        $summary = $this->getSummary($dates, $branchId);

        return view('admin.finance.sales.index', compact('sales', 'summary', 'period', 'branchId'));
    }

    public function expenseReport(Request $request)
    {
        $period = $request->input('period', 'month');
        $branchId = $request->input('branch_id');
        $categoryId = $request->input('category_id');
        $dates = $this->getDateRange($period);

        $expenses = Expense::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($categoryId, fn($q) => $q->where('expense_category_id', $categoryId))
            ->whereBetween('expense_date', [$dates['start'], $dates['end']])
            ->with(['branch', 'category', 'creator'])
            ->orderBy('expense_date', 'desc')
            ->paginate(20);

        $summary = $this->getSummary($dates, $branchId);
        $categories = \App\Models\ExpenseCategory::active()->get();

        return view('admin.finance.expenses.report', compact('expenses', 'summary', 'period', 'branchId', 'categories'));
    }

    public function profitLossReport(Request $request)
    {
        $period = $request->input('period', 'month');
        $branchId = $request->input('branch_id');
        $dates = $this->getDateRange($period);

        $summary = $this->getSummary($dates, $branchId);
        $salesBreakdown = $this->getSalesBreakdown($dates, $branchId);
        $expenseBreakdown = $this->getExpenseBreakdown($dates, $branchId);
        $profitTrend = $this->getProfitTrend($period, $branchId);
        $branchComparison = $this->getBranchComparison($dates);
        $branches = Branch::active()->get();

        return view('admin.finance.reports.profit-loss', compact(
            'summary',
            'salesBreakdown',
            'expenseBreakdown',
            'profitTrend',
            'branchComparison',
            'branches',
            'period',
            'branchId'
        ));
    }
}
