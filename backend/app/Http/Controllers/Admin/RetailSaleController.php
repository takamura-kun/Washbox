<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RetailSale;
use App\Models\InventoryItem;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RetailSaleController extends Controller
{
    public function index(Request $request)
    {
        $query = RetailSale::with(['branch', 'seller', 'inventoryItem']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->latest()->paginate(20);
        $branches = Branch::all();

        // Summary
        $summary = [
            'total_sales' => RetailSale::count(),
            'today_sales' => RetailSale::whereDate('created_at', today())->count(),
            'total_revenue' => RetailSale::sum('total_amount'),
            'today_revenue' => RetailSale::whereDate('created_at', today())->sum('total_amount'),
        ];

        return view('admin.finance.retail-sales.index', compact('sales', 'branches', 'summary'));
    }

    public function create()
    {
        $branches = Branch::all();
        $items = []; // Items will be loaded via AJAX when branch is selected
        return view('admin.finance.retail-sales.create', compact('branches', 'items'));
    }

    public function getAvailableItems($branchId)
    {
        $items = BranchStock::where('branch_id', $branchId)
            ->where('current_stock', '>', 0)
            ->with(['inventoryItem' => function($q) {
                $q->where('is_active', true);
            }])
            ->get()
            ->filter(function($stock) {
                return $stock->inventoryItem !== null;
            })
            ->map(function($stock) {
                return [
                    'id' => $stock->inventory_item_id,
                    'name' => $stock->inventoryItem->name,
                    'brand' => $stock->inventoryItem->brand,
                    'sku' => $stock->inventoryItem->sku,
                    'available_stock' => $stock->current_stock,
                    'unit_cost' => $stock->cost_price ?? $stock->inventoryItem->unit_cost_price,
                    'distribution_unit' => $stock->inventoryItem->distribution_unit,
                ];
            });

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['items'] as $itemData) {
                // Check stock availability
                $branchStock = BranchStock::where('branch_id', $validated['branch_id'])
                    ->where('inventory_item_id', $itemData['inventory_item_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$branchStock || $branchStock->current_stock < $itemData['quantity']) {
                    DB::rollBack();
                    return back()->with('error', 'Insufficient stock available for one or more items!');
                }

                $inventoryItem = InventoryItem::find($itemData['inventory_item_id']);

                // Create retail sale
                $sale = RetailSale::create([
                    'sale_number' => RetailSale::generateSaleNumber(),
                    'branch_id' => $validated['branch_id'],
                    'inventory_item_id' => $itemData['inventory_item_id'],
                    'item_name' => $inventoryItem->name,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_cost'],
                    'total_amount' => $itemData['quantity'] * $itemData['unit_cost'],
                    'payment_method' => 'cash',
                    'notes' => $request->notes,
                    'sold_by' => auth()->id(),
                ]);

                // Deduct stock
                $branchStock->decrement('current_stock', $itemData['quantity']);
                $branchStock->update(['last_updated_at' => now()]);

                // Record financial transaction
                FinancialTransaction::create([
                    'branch_id' => $validated['branch_id'],
                    'transaction_type' => 'retail_sale',
                    'transactionable_type' => RetailSale::class,
                    'transactionable_id' => $sale->id,
                    'amount' => $sale->total_amount,
                    'payment_method' => 'cash',
                    'description' => "Retail sale: {$inventoryItem->name} x{$itemData['quantity']}",
                    'transaction_date' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('admin.finance.retail-sales.index')
                ->with('success', 'Sale recorded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record sale: ' . $e->getMessage());
        }
    }

    public function show(RetailSale $retail)
    {
        $retail->load(['inventoryItem', 'seller', 'branch']);
        $sale = $retail; // Alias for view compatibility
        return view('admin.finance.retail-sales.show', compact('sale'));
    }

    public function destroy(RetailSale $retail)
    {
        DB::beginTransaction();
        try {
            // Return stock
            $branchStock = BranchStock::where('branch_id', $retail->branch_id)
                ->where('inventory_item_id', $retail->inventory_item_id)
                ->first();

            if ($branchStock) {
                $branchStock->increment('current_stock', $retail->quantity);
                $branchStock->update(['last_updated_at' => now()]);
            }

            // Delete financial transaction
            FinancialTransaction::where('transactionable_type', RetailSale::class)
                ->where('transactionable_id', $retail->id)
                ->delete();

            $retail->delete();

            DB::commit();

            return redirect()->route('admin.retail.index')
                ->with('success', 'Sale deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete sale: ' . $e->getMessage());
        }
    }
}
