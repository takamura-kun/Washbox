<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function index(Request $request)
    {
        $categories = InventoryCategory::active()->get();

        $items = InventoryItem::with(['category', 'centralStock'])
            ->when($request->category_id, fn($query) => $query->where('category_id', $request->category_id))
            ->when($request->supply_type, fn($query) => $query->where('supply_type', $request->supply_type))
            ->when($request->status === 'low', fn($query) => $query->whereHas('centralStock', fn($q) =>
                $q->whereColumn('current_stock', '<=', 'inventory_items.reorder_point')
                    ->where('current_stock', '>', 0)
            ))
            ->when($request->status === 'out', fn($query) => $query->whereHas('centralStock', fn($q) =>
                $q->where('current_stock', 0)
            ))
            ->when($request->status === 'ok', fn($query) => $query->whereHas('centralStock', fn($q) =>
                $q->where('current_stock', '>', 0)
                    ->whereColumn('current_stock', '>', 'inventory_items.reorder_point')
            ))
            ->orderBy('name')
            ->get();

        return view('admin.inventory.supply.manage.index', compact('items', 'categories'));
    }

    public function create()
    {
        $categories = InventoryCategory::active()->get();
        return view('admin.inventory.supply.manage.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:inventory_categories,id',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'supplier_name' => 'nullable|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|unique:inventory_items,sku',
            'barcode' => 'nullable|string|unique:inventory_items,barcode',
            'storage_location' => 'nullable|string|max:255',
            'lead_time_days' => 'nullable|integer|min:0',
            'has_expiration' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'supply_type' => 'required|string|max:50',
            'purchase_unit' => 'required|string|max:100',
            'units_per_purchase' => 'required|integer|min:1',
            'distribution_unit' => 'required|string|max:100',
            'bulk_cost_price' => 'required|numeric|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'max_level' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('storage/inventory'), $imageName);
            $validated['image_path'] = 'storage/inventory/' . $imageName;
        }

        // Calculate unit cost
        $validated['unit_cost_price'] = $validated['units_per_purchase'] > 0
            ? $validated['bulk_cost_price'] / $validated['units_per_purchase']
            : 0;

        // Set bulk_unit same as purchase_unit
        $validated['bulk_unit'] = $validated['purchase_unit'];
        $validated['units_per_bulk'] = $validated['units_per_purchase'];

        $item = InventoryItem::create($validated);

        // Create central stock record
        if (!$item->centralStock) {
            $item->centralStock()->create([
                'inventory_item_id' => $item->id,
                'current_stock' => 0,
                'cost_price' => $item->unit_cost_price,
                'reorder_point' => $validated['reorder_point'] ?? 0,
                'max_stock_level' => $validated['max_level'] ?? 0,
                'last_purchased_at' => null,
            ]);
        }

        return redirect()->route('admin.inventory.manage')
            ->with('success', 'Inventory item created successfully.');
    }

    public function show(InventoryItem $item)
    {
        $item->load(['category', 'centralStock', 'branchStocks']);
        return view('admin.inventory.supply.manage.show', compact('item'));
    }

    public function edit(InventoryItem $item)
    {
        $categories = InventoryCategory::active()->get();
        return view('admin.inventory.supply.manage.edit', compact('item', 'categories'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:inventory_categories,id',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'supplier_name' => 'nullable|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'barcode' => 'nullable|string|unique:inventory_items,barcode,' . $item->id,
            'storage_location' => 'nullable|string|max:255',
            'lead_time_days' => 'nullable|integer|min:0',
            'has_expiration' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'supply_type' => 'required|string|max:50',
            'purchase_unit' => 'required|string|max:100',
            'units_per_purchase' => 'required|integer|min:1',
            'distribution_unit' => 'required|string|max:100',
            'bulk_cost_price' => 'required|numeric|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'max_level' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($item->image_path && file_exists(public_path($item->image_path))) {
                unlink(public_path($item->image_path));
            }
            
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('storage/inventory'), $imageName);
            $validated['image_path'] = 'storage/inventory/' . $imageName;
        }

        // Calculate unit cost
        $validated['unit_cost_price'] = $validated['units_per_purchase'] > 0
            ? $validated['bulk_cost_price'] / $validated['units_per_purchase']
            : 0;

        // Set bulk_unit same as purchase_unit
        $validated['bulk_unit'] = $validated['purchase_unit'];
        $validated['units_per_bulk'] = $validated['units_per_purchase'];

        $item->update($validated);

        // Update central stock reorder points if changed
        if ($item->centralStock) {
            $item->centralStock->update([
                'reorder_point' => $validated['reorder_point'] ?? 0,
                'max_stock_level' => $validated['max_level'] ?? 0,
            ]);
        }

        return redirect()->route('admin.inventory.manage')
            ->with('success', 'Inventory item updated successfully.');
    }

    public function destroy(InventoryItem $item)
    {
        $item->delete();
        return redirect()->route('admin.inventory.manage')
            ->with('success', 'Inventory item deleted successfully.');
    }
}
