<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\{Laundry, Expense, ExpenseCategory, RetailSale};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceController extends Controller
{
    /**
     * Branch financial dashboard
     */
    public function index(Request $request)
    {
        $branch = auth()->guard('branch')->user();
        $branchId = $branch->id;
        $period = $request->input('period', 'today');
        $dates = $this->getDateRange($period);

        $summary = $this->getSummary($branchId, $dates);
        $dailyBreakdown = $this->getDailyBreakdown($branchId, $dates);
        $expensesByCategory = $this->getExpensesByCategory($branchId, $dates);
        $recentTransactions = $this->getRecentTransactions($branchId);

        return view('branch.finance.index', compact(
            'summary',
            'dailyBreakdown',
            'expensesByCategory',
            'recentTransactions',
            'period'
        ));
    }

    /**
     * Record branch expense
     */
    public function recordExpense(Request $request)
    {
        $branch = auth()->guard('branch')->user();

        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'created_by_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('expense-attachments', 'public');
            }

            // Combine description with created by info
            $notes = "Created by: {$request->created_by_name}";
            if ($request->description) {
                $notes .= "\n\n{$request->description}";
            }

            Expense::create([
                'branch_id' => $branch->id,
                'expense_category_id' => $request->expense_category_id,
                'title' => $request->title,
                'description' => $request->description,
                'amount' => $request->amount,
                'expense_date' => $request->expense_date,
                'attachment' => $attachmentPath,
                'notes' => $notes,
                'source' => 'manual',
                'created_by' => null,
            ]);

            return redirect()->back()->with('success', 'Expense recorded successfully');
        } catch (\Exception $e) {
            \Log::error('Expense creation error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to record expense: ' . $e->getMessage());
        }
    }

    /**
     * View branch expenses
     */
    public function expenses(Request $request)
    {
        $branch = auth()->guard('branch')->user();
        $branchId = $branch->id;
        $categoryId = $request->input('category_id');
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $query = Expense::with(['category', 'creator'])
            ->where('branch_id', $branchId)
            ->whereBetween('expense_date', [$startDate, $endDate]);

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(20);

        $summary = [
            'total_expenses' => $expenses->sum('amount'),
            'count' => $expenses->total(),
        ];

        $categories = ExpenseCategory::active()->get();

        return view('branch.finance.expenses', compact(
            'expenses',
            'summary',
            'categories',
            'categoryId',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Daily cash report
     */
    public function dailyCashReport(Request $request)
    {
        $branch = auth()->guard('branch')->user();
        $branchId = $branch->id;
        $date = $request->input('date', today()->toDateString());

        // Opening balance (previous day's closing)
        $previousDate = Carbon::parse($date)->subDay();
        $previousClosing = $this->getClosingBalance($branchId, $previousDate);

        // Today's transactions
        $cashIn = $this->getCashIn($branchId, $date);
        $cashOut = $this->getCashOut($branchId, $date);

        $report = [
            'date' => $date,
            'opening_balance' => $previousClosing,
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'net_cash_flow' => $cashIn['total'] - $cashOut['total'],
            'closing_balance' => $previousClosing + $cashIn['total'] - $cashOut['total'],
        ];

        return view('branch.finance.daily-cash-report', compact('report', 'date'));
    }

    /**
     * Weekly summary
     */
    public function weeklySummary(Request $request)
    {
        $branch = auth()->guard('branch')->user();
        $branchId = $branch->id;
        $startDate = $request->input('start_date', now()->startOfWeek()->toDateString());
        $endDate = $request->input('end_date', now()->endOfWeek()->toDateString());

        $dates = ['start' => $startDate, 'end' => $endDate];
        $summary = $this->getSummary($branchId, $dates);
        $dailyBreakdown = $this->getDailyBreakdown($branchId, $dates);

        return view('branch.finance.weekly-summary', compact(
            'summary',
            'dailyBreakdown',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Transaction summary
     */
    public function transactionSummary(Request $request)
    {
        $branch = auth()->guard('branch')->user();
        $branchId = $branch->id;
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $laundries = Laundry::where('branch_id', $branchId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('paid_at', [$startDate, $endDate]);

        $retail = RetailSale::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $expenses = Expense::where('branch_id', $branchId)
            ->whereBetween('expense_date', [$startDate, $endDate]);

        $laundrySales = $laundries->sum('total_amount');
        $retailSales = $retail->sum('total_amount');
        $totalExpenses = $expenses->sum('amount');
        $totalIncome = $laundrySales + $retailSales;

        $summary = [
            'laundry_sales'   => $laundrySales,
            'laundry_count'   => $laundries->count(),
            'retail_sales'    => $retailSales,
            'retail_count'    => $retail->count(),
            'total_income'    => $totalIncome,
            'total_expenses'  => $totalExpenses,
            'net'             => $totalIncome - $totalExpenses,
        ];

        $expensesByCategory = $expenses->select('expense_category_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->with('category')
            ->groupBy('expense_category_id')
            ->get()
            ->map(fn($e) => [
                'category' => $e->category->name ?? 'Uncategorized',
                'total'    => $e->total,
                'count'    => $e->count,
            ]);

        $recentTransactions = $this->getRecentTransactions($branchId, 20);

        return view('branch.finance.transaction-summary', compact(
            'summary',
            'expensesByCategory',
            'recentTransactions',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Sales report
     */
    public function salesReport(Request $request)
    {
        $branch = auth()->guard('branch')->user();
        $branchId = $branch->id;
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $laundries = Laundry::with(['customer', 'service'])
            ->where('branch_id', $branchId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->orderBy('paid_at', 'desc')
            ->paginate(20);

        $summary = [
            'total_sales' => $laundries->sum('total_amount'),
            'total_orders' => $laundries->total(),
            'average_order' => $laundries->count() > 0 ? $laundries->sum('total_amount') / $laundries->count() : 0,
        ];

        return view('branch.finance.sales-report', compact(
            'laundries',
            'summary',
            'startDate',
            'endDate'
        ));
    }

    // ========================================================================
    // PRIVATE HELPER METHODS
    // ========================================================================

    private function getDateRange($period)
    {
        $now = now();
        
        // Handle period formats like "week:1" or just "week"
        if (str_contains($period, ':')) {
            [$periodType, $periodNumber] = explode(':', $period, 2);
            $periodNumber = (int) $periodNumber;
        } else {
            $periodType = $period;
            $periodNumber = null;
        }
        
        return match($periodType) {
            'today' => [
                'start' => $now->clone()->startOfDay(),
                'end' => $now->clone()->endOfDay(),
            ],
            'yesterday' => [
                'start' => $now->clone()->subDay()->startOfDay(),
                'end' => $now->clone()->subDay()->endOfDay(),
            ],
            'week' => [
                'start' => $now->clone()->startOfWeek(),
                'end' => $now->clone()->endOfWeek(),
            ],
            'month' => [
                'start' => $now->clone()->startOfMonth(),
                'end' => $now->clone()->endOfMonth(),
            ],
            default => [
                'start' => $now->clone()->startOfDay(),
                'end' => $now->clone()->endOfDay(),
            ],
        };
    }

    private function getSummary($branchId, $dates)
    {
        // Laundry Sales (already includes pickup/delivery fees in total_amount)
        $laundrySales = Laundry::where('branch_id', $branchId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('paid_at', [$dates['start'], $dates['end']])
            ->sum('total_amount');

        $laundryCount = Laundry::where('branch_id', $branchId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('paid_at', [$dates['start'], $dates['end']])
            ->count();

        // Retail Sales
        $retailSales = RetailSale::where('branch_id', $branchId)
            ->whereBetween('created_at', [$dates['start'], $dates['end']])
            ->sum('total_amount');

        $retailCount = RetailSale::where('branch_id', $branchId)
            ->whereBetween('created_at', [$dates['start'], $dates['end']])
            ->count();

        // Total Sales (laundry + retail)
        $totalSales = $laundrySales + $retailSales;
        $totalOrders = $laundryCount + $retailCount;

        // Expenses
        $totalExpenses = Expense::where('branch_id', $branchId)
            ->whereBetween('expense_date', [$dates['start'], $dates['end']])
            ->sum('amount');

        // Net
        $netIncome = $totalSales - $totalExpenses;

        return [
            'total_sales' => $totalSales,
            'laundry_sales' => $laundrySales,
            'retail_sales' => $retailSales,
            'total_orders' => $totalOrders,
            'laundry_orders' => $laundryCount,
            'retail_orders' => $retailCount,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
            'average_order' => $totalOrders > 0 ? $totalSales / $totalOrders : 0,
        ];
    }

    private function getDailyBreakdown($branchId, $dates)
    {
        $days = Carbon::parse($dates['start'])->diffInDays(Carbon::parse($dates['end'])) + 1;
        $breakdown = [];

        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::parse($dates['start'])->addDays($i);
            
            // Laundry sales
            $laundrySales = Laundry::where('branch_id', $branchId)
                ->whereIn('status', ['paid', 'completed'])
                ->whereDate('paid_at', $date)
                ->sum('total_amount');

            // Retail sales
            $retailSales = RetailSale::where('branch_id', $branchId)
                ->whereDate('created_at', $date)
                ->sum('total_amount');

            $totalSales = $laundrySales + $retailSales;

            $expenses = Expense::where('branch_id', $branchId)
                ->whereDate('expense_date', $date)
                ->sum('amount');

            $breakdown[] = [
                'date' => $date->format('M d'),
                'sales' => $totalSales,
                'expenses' => $expenses,
                'net' => $totalSales - $expenses,
            ];
        }

        return $breakdown;
    }

    private function getExpensesByCategory($branchId, $dates)
    {
        return Expense::where('branch_id', $branchId)
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

    private function getRecentTransactions($branchId, $limit = 10)
    {
        $laundries = Laundry::where('branch_id', $branchId)
            ->whereIn('status', ['paid', 'completed'])
            ->select('id', 'tracking_number', 'total_amount', 'paid_at', DB::raw("'sale' as type"))
            ->orderBy('paid_at', 'desc')
            ->limit($limit);

        $expenses = Expense::where('branch_id', $branchId)
            ->select('id', 'title as tracking_number', 'amount as total_amount', 'expense_date as paid_at', DB::raw("'expense' as type"))
            ->orderBy('expense_date', 'desc')
            ->limit($limit);

        return $laundries->union($expenses)
            ->orderBy('paid_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getCashIn($branchId, $date)
    {
        $laundry = Laundry::where('branch_id', $branchId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereDate('paid_at', $date)
            ->sum('total_amount');

        $retail = RetailSale::where('branch_id', $branchId)
            ->whereDate('created_at', $date)
            ->sum('total_amount');

        return [
            'laundry' => $laundry,
            'retail' => $retail,
            'total' => $laundry + $retail,
        ];
    }

    private function getCashOut($branchId, $date)
    {
        $expenses = Expense::where('branch_id', $branchId)
            ->whereDate('expense_date', $date)
            ->sum('amount');

        return [
            'expenses' => $expenses,
            'total' => $expenses,
        ];
    }

    private function getClosingBalance($branchId, $date)
    {
        // This would ideally come from a cash_register table
        // For now, calculate from transactions
        $allLaundrySales = Laundry::where('branch_id', $branchId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereDate('paid_at', '<=', $date)
            ->sum('total_amount');

        $allRetailSales = RetailSale::where('branch_id', $branchId)
            ->whereDate('created_at', '<=', $date)
            ->sum('total_amount');

        $allExpenses = Expense::where('branch_id', $branchId)
            ->whereDate('expense_date', '<=', $date)
            ->sum('amount');

        return ($allLaundrySales + $allRetailSales) - $allExpenses;
    }
}
