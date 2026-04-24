<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laundry;
use App\Models\Expense;
use App\Models\RetailSale;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceReportController extends Controller
{
    public function profitLoss(Request $request)
    {
        try {
            $branchId = $request->input('branch_id');
            $period = $request->input('period', 'month');
            $month = $request->input('month', now()->format('Y-m'));
            
            // Determine date range based on period
            if ($period === 'quarter') {
                $startDate = now()->startOfQuarter()->format('Y-m-d');
                $endDate = now()->endOfQuarter()->format('Y-m-d');
            } elseif ($period === 'year') {
                $startDate = now()->startOfYear()->format('Y-m-d');
                $endDate = now()->endOfYear()->format('Y-m-d');
            } else {
                [$year, $monthNum] = explode('-', $month);
                $startDate = "{$year}-{$monthNum}-01";
                $endDate = date('Y-m-t', strtotime($startDate));
            }

            // Sales
            $laundrySales = Laundry::query()
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereIn('status', ['paid', 'completed'])
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->sum('total_amount');

            $retailSales = RetailSale::query()
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount');

            $pickupFees = Laundry::query()
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereIn('status', ['paid', 'completed'])
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->sum(DB::raw('COALESCE(pickup_fee, 0) + COALESCE(delivery_fee, 0)'));

            $totalSales = $laundrySales + $retailSales + $pickupFees;

            // Expenses
            $expensesByCategory = Expense::query()
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
                ->select('expense_categories.name as category_name', DB::raw('SUM(expenses.amount) as total'))
                ->groupBy('expense_categories.id', 'expense_categories.name')
                ->get();

            $totalExpenses = $expensesByCategory->sum('total');
            $netProfit = $totalSales - $totalExpenses;

            // Per Branch
            $branchStats = Branch::query()
                ->when($branchId, fn($q) => $q->where('id', $branchId))
                ->get()
                ->map(function ($branch) use ($startDate, $endDate) {
                    $sales = Laundry::where('branch_id', $branch->id)
                        ->whereIn('status', ['paid', 'completed'])
                        ->whereBetween('paid_at', [$startDate, $endDate])
                        ->sum('total_amount');

                    $expenses = Expense::where('branch_id', $branch->id)
                        ->whereBetween('expense_date', [$startDate, $endDate])
                        ->sum('amount');

                    return [
                        'name' => $branch->name,
                        'sales' => $sales ?? 0,
                        'expenses' => $expenses ?? 0,
                        'profit' => ($sales ?? 0) - ($expenses ?? 0),
                    ];
                });

            // Prepare data for view
            $summary = [
                'totalSales' => $totalSales,
                'totalExpenses' => $totalExpenses,
                'netProfit' => $netProfit
            ];

            $salesBreakdown = [
                'laundry' => $laundrySales,
                'retail' => $retailSales,
                'fees' => $pickupFees
            ];

            $expenseBreakdown = $expensesByCategory->map(function($item) {
                return [
                    'category' => $item->category_name,
                    'amount' => $item->total
                ];
            });

            $branchComparison = $branchStats;

            // Profit trend (last 7 days/weeks/months based on period)
            $profitTrend = $this->getProfitTrend($branchId, $period, $startDate, $endDate);

            $branches = Branch::where('is_active', true)->get();

            return view('admin.finance.reports.profit-loss', compact(
                'summary',
                'salesBreakdown',
                'expenseBreakdown',
                'branchComparison',
                'profitTrend',
                'branches',
                'branchId',
                'period',
                'month'
            ));
        } catch (\Exception $e) {
            \Log::error('Profit Loss Report Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->with('error', 'Failed to load profit-loss report: ' . $e->getMessage());
        }
    }

    private function getProfitTrend($branchId, $period, $startDate, $endDate)
    {
        $trend = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($period === 'year') {
            // Monthly trend for year
            while ($start <= $end) {
                $monthStart = $start->copy()->startOfMonth();
                $monthEnd = $start->copy()->endOfMonth();

                $sales = Laundry::query()
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->whereIn('status', ['paid', 'completed'])
                    ->whereBetween('paid_at', [$monthStart, $monthEnd])
                    ->sum('total_amount');

                $expenses = Expense::query()
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->whereBetween('expense_date', [$monthStart, $monthEnd])
                    ->sum('amount');

                $trend[] = [
                    'date' => $start->format('M Y'),
                    'sales' => $sales ?? 0,
                    'expenses' => $expenses ?? 0
                ];

                $start->addMonth();
            }
        } else {
            // Daily trend for month/quarter
            $days = min(30, $start->diffInDays($end) + 1);
            $interval = max(1, ceil($days / 10)); // Show max 10 points

            for ($i = 0; $i < $days; $i += $interval) {
                $dayStart = $start->copy()->addDays($i)->startOfDay();
                $dayEnd = $dayStart->copy()->endOfDay();

                $sales = Laundry::query()
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->whereIn('status', ['paid', 'completed'])
                    ->whereBetween('paid_at', [$dayStart, $dayEnd])
                    ->sum('total_amount');

                $expenses = Expense::query()
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->whereBetween('expense_date', [$dayStart, $dayEnd])
                    ->sum('amount');

                $trend[] = [
                    'date' => $dayStart->format('M d'),
                    'sales' => $sales ?? 0,
                    'expenses' => $expenses ?? 0
                ];
            }
        }

        return $trend;
    }
}
