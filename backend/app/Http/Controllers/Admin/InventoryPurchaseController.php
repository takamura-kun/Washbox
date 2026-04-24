<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{InventoryPurchase, InventoryItem, InventoryCategory, CentralStock, StockHistory, InventoryCostHistory, Supplier};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryPurchaseController extends Controller
{
    public function index()
    {
        $purchases = InventoryPurchase::with(['items.item.category', 'purchasedBy'])
                                      ->orderBy('purchase_date', 'desc')
                                      ->paginate(20);
        return view('admin.inventory.supply.purchases.index', compact('purchases'));
    }

    public function create()
    {
        $categories = InventoryCategory::with('items')->get();
        $items = InventoryItem::with('category')
                              ->orderBy('name')
                              ->get();
        $referenceNo = 'PUR-' . date('Y') . '-' .
                       str_pad(InventoryPurchase::count() + 1, 4, '0', STR_PAD_LEFT);
        return view('admin.inventory.supply.purchases.create',
                    compact('categories', 'items', 'referenceNo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_date'   => 'required|date',
            'items'           => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity'          => 'required|integer|min:1',
            'items.*.cost_per_bulk'     => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Handle supplier - create or find by name
            $supplier = null;
            if ($request->supplier) {
                $supplier = Supplier::firstOrCreate(
                    ['name' => $request->supplier],
                    ['is_active' => true]
                );
            }

            $purchase = InventoryPurchase::create([
                'reference_no'  => 'PUR-' . date('Y') . '-' .
                                   str_pad(InventoryPurchase::count() + 1, 4, '0', STR_PAD_LEFT),
                'purchase_date' => $request->purchase_date,
                'branch_id'     => null, // Central inventory purchases, not branch-specific
                'supplier_id'   => $supplier?->id,
                'notes'         => $request->notes,
                'purchased_by'  => auth()->id(),
                'grand_total'   => 0,
            ]);

            $grandTotal = 0;

            foreach ($request->items as $row) {
                $item = InventoryItem::find($row['inventory_item_id']);
                $unitsReceived = $row['quantity'] * $item->units_per_purchase;
                $costPerUnit   = $row['cost_per_bulk'] / $item->units_per_purchase;
                $totalCost     = $row['quantity'] * $row['cost_per_bulk'];
                $grandTotal   += $totalCost;

                $purchase->items()->create([
                    'inventory_item_id' => $item->id,
                    'purchase_unit'     => $item->purchase_unit,
                    'quantity'          => $row['quantity'],
                    'cost_per_bulk'     => $row['cost_per_bulk'],
                    'cost_per_unit'     => $costPerUnit,
                    'units_received'    => $unitsReceived,
                    'total_cost'        => $totalCost,
                ]);

                $central = CentralStock::firstOrCreate(
                    ['inventory_item_id' => $item->id],
                    ['current_stock' => 0, 'cost_price' => $costPerUnit]
                );

                if ($central->cost_price != $costPerUnit && $central->current_stock > 0) {
                    InventoryCostHistory::create([
                        'inventory_item_id' => $item->id,
                        'branch_id'         => null,
                        'old_cost_price'    => $central->cost_price,
                        'new_cost_price'    => $costPerUnit,
                        'effective_date'    => $request->purchase_date,
                        'reason'            => 'Price updated via purchase ' . $purchase->reference_no,
                        'changed_by'        => auth()->id(),
                    ]);
                }

                $stockBefore = $central->current_stock;
                $central->increment('current_stock', $unitsReceived);
                $central->update([
                    'cost_price'        => $costPerUnit,
                    'last_purchased_at' => now(),
                ]);

                StockHistory::create([
                    'inventory_item_id' => $item->id,
                    'branch_id'         => null,
                    'type'              => 'purchase',
                    'quantity'          => $unitsReceived,
                    'balance_after'     => $stockBefore + $unitsReceived,
                    'reference_type'    => 'App\\Models\\InventoryPurchase',
                    'reference_id'      => $purchase->id,
                    'user_id'           => auth()->id(),
                    'notes'             => 'Purchase: ' . $purchase->reference_no,
                ]);
            }

            $purchase->update(['grand_total' => $grandTotal]);

            // Record financial transaction
            $purchase->total_cost = $grandTotal;
            $purchase->save();

            $financialService = app(\App\Services\FinancialTransactionService::class);
            $financialService->recordInventoryPurchase($purchase);

            DB::commit();
            return redirect()->route('admin.inventory.purchases.index')
                             ->with('success', 'Purchase recorded! Stock updated and expense logged.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Purchase failed: ' . $e->getMessage()]);
        }
    }

    public function show(InventoryPurchase $purchase)
    {
        $purchase->load(['items.item.category', 'purchasedBy']);
        return view('admin.inventory.purchases.show', compact('purchase'));
    }
}
