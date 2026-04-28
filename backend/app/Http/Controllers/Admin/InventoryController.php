<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\InventoryPurchase;
use App\Models\CentralStock;
use App\Models\StockHistory;
use App\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $categories = InventoryCategory::with(['items.centralStock'])->get();
        $totalSkus = InventoryItem::count();
        $totalUnits = CentralStock::sum('current_stock') ?? 0;
        $totalValue = CentralStock::join('inventory_items', 'central_stocks.inventory_item_id', '=', 'inventory_items.id')
            ->selectRaw('SUM(central_stocks.current_stock * inventory_items.unit_cost_price) as total')
            ->value('total') ?? 0;
        $lowStockItems = InventoryItem::with('centralStock')->whereHas('centralStock', function($q) {
            $q->whereColumn('current_stock', '<=', 'reorder_point')->where('reorder_point', '>', 0);
        })->get();
        $lowCount = $lowStockItems->count();

        return view('admin.inventory.supply.warehouse.index', compact('categories', 'totalSkus', 'totalUnits', 'totalValue', 'lowStockItems', 'lowCount'));
    }

    public function dashboard()
    {
        $stats = [
            'total_items' => InventoryItem::count(),
            'items_in_stock' => InventoryItem::whereHas('centralStock', fn($q) => $q->where('current_stock', '>', 0))->count(),
            'low_stock_count' => InventoryItem::whereHas('centralStock', fn($q) => $q->whereColumn('current_stock', '<=', 'reorder_point')->where('reorder_point', '>', 0))->count(),
            'out_of_stock_count' => InventoryItem::whereHas('centralStock', fn($q) => $q->where('current_stock', '<=', 0))->count(),
            'out_of_stock' => InventoryItem::with('category')->whereHas('centralStock', fn($q) => $q->where('current_stock', '<=', 0))->get(),
            'low_stock' => InventoryItem::with('centralStock')->whereHas('centralStock', fn($q) => $q->whereColumn('current_stock', '<=', 'reorder_point')->where('reorder_point', '>', 0))->get(),
            'total_inventory_value' => CentralStock::join('inventory_items', 'central_stocks.inventory_item_id', '=', 'inventory_items.id')
                ->selectRaw('SUM(central_stocks.current_stock * inventory_items.unit_cost_price) as total')
                ->value('total') ?? 0,
            'recent_adjustments' => InventoryAdjustment::with('item')->latest()->take(10)->get(),
        ];

        return view('admin.inventory.dashboard', compact('stats'));
    }

    public function branchStock()
    {
        $categories = InventoryCategory::all();
        $totalSkus = InventoryItem::count();
        $warehouseStock = CentralStock::sum('current_stock') ?? 0;
        $totalSpent = CentralStock::join('inventory_items', 'central_stocks.inventory_item_id', '=', 'inventory_items.id')
            ->selectRaw('SUM(central_stocks.current_stock * inventory_items.unit_cost_price) as total')
            ->value('total') ?? 0;
        
        $branchesRaw = \App\Models\Branch::with(['branchStocks.inventoryItem.category'])->where('is_active', true)->get();
        
        // Transform branches data to match view expectations
        $branches = $branchesRaw->map(function($branch) {
            $items = $branch->branchStocks->map(function($stock) {
                return (object)[
                    'id' => $stock->inventoryItem->id,
                    'name' => $stock->inventoryItem->name,
                    'brand' => $stock->inventoryItem->brand,
                    'category_id' => $stock->inventoryItem->category_id,
                    'branch_stock' => $stock->current_stock,
                    'cost_per_unit' => $stock->inventoryItem->unit_cost_price,
                    'unit_label' => $stock->inventoryItem->distribution_unit ?? 'pc',
                ];
            });
            
            $totalUnits = $items->sum('branch_stock');
            $totalValue = $items->sum(fn($item) => $item->branch_stock * $item->cost_per_unit);
            $stockedCount = $items->where('branch_stock', '>', 0)->count();
            $lowCount = $items->where('branch_stock', '<', 60)->where('branch_stock', '>', 0)->count() + 
                        $items->where('branch_stock', '<=', 0)->count();
            
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'color' => '#3b82f6',
                'color_light' => '#dbeafe',
                'total_units' => $totalUnits,
                'total_value' => $totalValue,
                'stocked_count' => $stockedCount,
                'total_items' => InventoryItem::count(),
                'low_count' => $lowCount,
                'items' => $items,
            ];
        });
        
        return view('admin.inventory.supply.branches.index', compact('branches', 'categories', 'totalSkus', 'warehouseStock', 'totalSpent'));
    }

    public function lowStock()
    {
        $lowStockItems = InventoryItem::with(['category', 'centralStock', 'branchStocks.branch'])
            ->whereHas('centralStock', function($q) {
                $q->whereColumn('current_stock', '<=', 'reorder_point');
            })->get();
        return view('admin.inventory.supply.manage.index', compact('lowStockItems'));
    }

    public function manage()
    {
        $categories = InventoryCategory::with(['items.centralStock'])->get();
        return view('admin.inventory.supply.manage.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:inventory_categories,id',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|unique:inventory_items,sku',
            'bulk_unit' => 'required|string',
            'distribution_unit' => 'required|string',
            'units_per_bulk' => 'required|integer|min:1',
            'bulk_cost_price' => 'required|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $validated['unit_cost_price'] = $validated['bulk_cost_price'] / $validated['units_per_bulk'];
            $item = InventoryItem::create($validated);

            CentralStock::create([
                'inventory_item_id' => $item->id,
                'current_stock' => 0,
                'cost_price' => $validated['unit_cost_price'],
                'reorder_point' => $request->reorder_point ?? 0,
                'max_stock_level' => $request->max_stock_level,
            ]);

            DB::commit();
            return redirect()->route('admin.inventory.manage')->with('success', 'Item added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create inventory item: ' . $e->getMessage());
        }
    }

    public function update(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:inventory_categories,id',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'bulk_unit' => 'required|string',
            'distribution_unit' => 'required|string',
            'units_per_bulk' => 'required|integer|min:1',
            'bulk_cost_price' => 'required|numeric|min:0',
        ]);

        $validated['unit_cost_price'] = $validated['bulk_cost_price'] / $validated['units_per_bulk'];
        $item->update($validated);

        return redirect()->route('admin.inventory.index')->with('success', 'Inventory item updated successfully');
    }

    public function destroy(InventoryItem $item)
    {
        $item->delete();
        return redirect()->route('admin.inventory.index')->with('success', 'Inventory item deleted successfully');
    }

    /**
     * API endpoint to get active supplies for service supplies dropdown
     */
    public function getActiveSupplies()
    {
        $supplies = InventoryItem::with(['category', 'centralStock'])
            ->where(function($query) {
                $query->where('is_active', true)
                      ->orWhereNull('is_active');
            })
            ->orderBy('name')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'brand' => $item->brand,
                    'category' => $item->category->name ?? 'Uncategorized',
                    'unit' => $item->distribution_unit ?? 'pcs',
                    'unit_label' => $item->distribution_unit ?? 'pcs',
                    'current_stock' => $item->centralStock->current_stock ?? 0,
                ];
            });

        return response()->json([
            'success' => true,
            'supplies' => $supplies
        ]);
    }
}
