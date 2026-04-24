<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{CashFlowRecord, Branch, Laundry, Expense, RetailSale, InventoryPurchase};
use Illuminate\Http\Request;
use Carbon\Carbon;

class CashFlowController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->input('branch_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        
        // Default to current month if no dates provided
        if (!$dateFrom || !$dateTo) {
            $dateFrom = now()->startOfMonth()->toDateString();
            $dateTo = now()->endOfMonth()->toDateString();
        }

        $query = CashFlowRecord::with(['branch', 'creator'])
            ->whereBetween('record_date', [$dateFrom, $dateTo])
            ->orderBy('record_date', 'desc');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $records = $query->paginate(50);

        // Calculate real-time today's data
        $today = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        
        // Today's Cash In
        $todayLaundrySales = Laundry::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('paid_at', [$today, $todayEnd])
            ->sum('total_amount');
        
        $todayRetailSales = RetailSale::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$today, $todayEnd])
            ->sum('total_amount');
        
        $todayCashIn = $todayLaundrySales + $todayRetailSales;
        
        // Today's Cash Out
        $todayExpenses = Expense::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('expense_date', [$today, $todayEnd])
            ->whereNull('inventory_purchase_id') // Exclude inventory expenses (already counted in purchases)
            ->sum('amount');
        
        $todayInventoryPurchases = InventoryPurchase::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('purchase_date', [$today, $todayEnd])
            ->sum('total_cost');
        
        $todayCashOut = $todayExpenses + $todayInventoryPurchases;
        
        // Calculate cumulative balance
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
            ->whereNull('inventory_purchase_id') // Exclude inventory expenses (already counted in purchases)
            ->sum('amount');
        
        $allTimeInventory = InventoryPurchase::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_cost');
        
        $currentBalance = ($allTimeSales + $allTimeRetail) - ($allTimeExpenses + $allTimeInventory);

        // Summary
        $summary = [
            'today_cash_in' => $todayCashIn,
            'today_cash_out' => $todayCashOut,
            'net_cash_flow' => $todayCashIn - $todayCashOut,
            'current_balance' => $currentBalance,
        ];

        // Chart data
        $chartData = $records->reverse()->map(function($record) {
            return [
                'date' => $record->record_date->format('M d'),
                'inflow' => $record->cash_inflow,
                'outflow' => $record->cash_outflow,
                'net' => $record->net_cash_flow,
                'balance' => $record->closing_balance,
            ];
        });

        $branches = Branch::active()->get();

        return view('admin.finance.cash-flow.index', compact(
            'records',
            'summary',
            'chartData',
            'branches',
            'branchId'
        ));
    }

    public function generate(Request $request)
    {
        $branchId = $request->input('branch_id');
        $date = now();

        // Calculate today's cash flow
        $today = $date->startOfDay();
        $todayEnd = $date->copy()->endOfDay();
        
        // Cash In
        $laundrySales = Laundry::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('paid_at', [$today, $todayEnd])
            ->sum('total_amount');
        
        $retailSales = RetailSale::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$today, $todayEnd])
            ->sum('total_amount');
        
        $cashIn = $laundrySales + $retailSales;
        
        // Cash Out
        $expenses = Expense::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('expense_date', [$today, $todayEnd])
            ->whereNull('inventory_purchase_id')
            ->sum('amount');
        
        $inventoryPurchases = InventoryPurchase::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('purchase_date', [$today, $todayEnd])
            ->sum('total_cost');
        
        $cashOut = $expenses + $inventoryPurchases;
        
        // Get previous day's closing balance
        $previousRecord = CashFlowRecord::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereDate('record_date', '<', $date)
            ->orderBy('record_date', 'desc')
            ->first();
        
        $openingBalance = $previousRecord ? $previousRecord->closing_balance : 0;
        $closingBalance = $openingBalance + $cashIn - $cashOut;
        
        // Create or update record
        CashFlowRecord::updateOrCreate(
            [
                'record_date' => $date->toDateString(),
                'branch_id' => $branchId,
            ],
            [
                'opening_balance' => $openingBalance,
                'cash_inflow' => $cashIn,
                'cash_outflow' => $cashOut,
                'net_cash_flow' => $cashIn - $cashOut,
                'closing_balance' => $closingBalance,
                'created_by' => auth()->id(),
            ]
        );

        return redirect()
            ->route('admin.finance.cash-flow.index', ['branch_id' => $branchId])
            ->with('success', 'Cash flow record generated successfully');
    }

    public function generateRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $branchId = $request->branch_id;

        $count = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Similar logic as generate() but for each date
            $today = $currentDate->copy()->startOfDay();
            $todayEnd = $currentDate->copy()->endOfDay();
            
            $laundrySales = Laundry::query()
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereIn('status', ['paid', 'completed'])
                ->whereBetween('paid_at', [$today, $todayEnd])
                ->sum('total_amount');
            
            $retailSales = RetailSale::query()
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereBetween('created_at', [$today, $todayEnd])
                ->sum('total_amount');
            
            $cashIn = $laundrySales + $retailSales;
            
            $expenses = Expense::query()
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereBetween('expense_date', [$today, $todayEnd])
                ->whereNull('inventory_purchase_id')
                ->sum('amount');
            
            $inventoryPurchases = InventoryPurchase::query()
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereBetween('purchase_date', [$today, $todayEnd])
                ->sum('total_cost');
            
            $cashOut = $expenses + $inventoryPurchases;
            
            $previousRecord = CashFlowRecord::query()
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereDate('record_date', '<', $currentDate)
                ->orderBy('record_date', 'desc')
                ->first();
            
            $openingBalance = $previousRecord ? $previousRecord->closing_balance : 0;
            $closingBalance = $openingBalance + $cashIn - $cashOut;
            
            CashFlowRecord::updateOrCreate(
                [
                    'record_date' => $currentDate->toDateString(),
                    'branch_id' => $branchId,
                ],
                [
                    'opening_balance' => $openingBalance,
                    'cash_inflow' => $cashIn,
                    'cash_outflow' => $cashOut,
                    'net_cash_flow' => $cashIn - $cashOut,
                    'closing_balance' => $closingBalance,
                    'created_by' => auth()->id(),
                ]
            );
            
            $currentDate->addDay();
            $count++;
        }

        return redirect()
            ->route('admin.finance.cash-flow.index', ['branch_id' => $branchId])
            ->with('success', "{$count} cash flow records generated successfully");
    }

    public function show(CashFlowRecord $cashFlowRecord)
    {
        $cashFlowRecord->load(['branch', 'creator']);

        return view('admin.finance.cash-flow.show', compact('cashFlowRecord'));
    }
}

