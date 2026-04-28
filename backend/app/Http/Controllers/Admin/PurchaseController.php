<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryPurchase;
use App\Models\InventoryItem;
use App\Models\CentralStock;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = InventoryPurchase::with(['inventoryItem', 'purchasedBy'])
            ->latest()
            ->paginate(20);

        return view('admin.inventory.purchases.index', compact('purchases'));
    }

    public function create()
    {
        $items = InventoryItem::active()->with('category')->get();
        return view('admin.inventory.purchases.create', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity_bulk' => 'required|numeric|min:0.01',
            'cost_per_bulk' => 'required|numeric|min:0',
            'supplier_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'purchase_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $item = InventoryItem::findOrFail($validated['inventory_item_id']);
            
            $quantityUnits = $validated['quantity_bulk'] * $item->units_per_bulk;
            $totalCost = $validated['quantity_bulk'] * $validated['cost_per_bulk'];

            $purchase = InventoryPurchase::create([
                'inventory_item_id' => $item->id,
                'purchased_by' => auth()->id(),
                'quantity_bulk' => $validated['quantity_bulk'],
                'quantity_units' => $quantityUnits,
                'cost_per_bulk' => $validated['cost_per_bulk'],
                'total_cost' => $totalCost,
                'supplier_name' => $validated['supplier_name'],
                'notes' => $validated['notes'],
                'purchase_date' => $validated['purchase_date'],
            ]);

            $centralStock = CentralStock::firstOrCreate(
                ['inventory_item_id' => $item->id],
                ['quantity_in_bulk' => 0, 'quantity_in_units' => 0]
            );

            $centralStock->quantity_in_bulk += $validated['quantity_bulk'];
            $centralStock->quantity_in_units += $quantityUnits;
            $centralStock->save();

            StockHistory::create([
                'inventory_item_id' => $item->id,
                'branch_id' => null,
                'type' => 'purchase',
                'quantity' => $quantityUnits,
                'balance_after' => $centralStock->quantity_in_units,
                'reference_type' => InventoryPurchase::class,
                'reference_id' => $purchase->id,
                'user_id' => auth()->id(),
                'notes' => "Purchase: {$validated['quantity_bulk']} {$item->bulk_unit} ({$quantityUnits} {$item->distribution_unit})",
            ]);

            DB::commit();
            return redirect()->route('admin.purchases.index')->with('success', 'Purchase recorded successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record purchase: ' . $e->getMessage())->withInput();
        }
    }

    public function show(InventoryPurchase $purchase)
    {
        $purchase->load(['inventoryItem.category', 'purchasedBy']);
        return view('admin.inventory.purchases.show', compact('purchase'));
    }
}
