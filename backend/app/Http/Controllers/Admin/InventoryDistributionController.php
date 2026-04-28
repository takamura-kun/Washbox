<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{InventoryDistribution, InventoryItem, Branch, CentralStock, BranchStock, StockHistory};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryDistributionController extends Controller
{
    public function index()
    {
        $categories = \App\Models\InventoryCategory::where('is_active', true)->get();
        $items = InventoryItem::with(['category', 'centralStock'])
                              ->where('is_active', true)
                              ->get();
        $branchesRaw = Branch::where('is_active', true)->get();
        
        // Transform items to include warehouse_stock
        $allItems = $items->map(function($item) {
            $item->warehouse_stock = $item->centralStock->current_stock ?? 0;
            $item->unit_label = $item->distribution_unit ?? 'pc';
            return $item;
        });
        
        // Get branch stocks for all items
        $branchItemStocks = [];
        foreach ($branchesRaw as $branch) {
            $branchItemStocks[$branch->id] = [];
            $stocks = \App\Models\BranchStock::where('branch_id', $branch->id)->get();
            foreach ($stocks as $stock) {
                $branchItemStocks[$branch->id][$stock->inventory_item_id] = $stock->current_stock;
            }
        }
        
        // Transform branches for view
        $branches = $branchesRaw->map(function($branch) use ($branchItemStocks) {
            $stocks = \App\Models\BranchStock::where('branch_id', $branch->id)
                ->with('inventoryItem')
                ->get();
            
            $totalUnits = $stocks->sum('current_stock');
            $totalValue = $stocks->sum(function($stock) {
                return $stock->current_stock * ($stock->inventoryItem->unit_cost_price ?? 0);
            });
            
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'color' => '#3b82f6',
                'color_light' => '#dbeafe',
                'border' => '#3b82f6',
                'total_units' => $totalUnits,
                'total_value' => $totalValue,
            ];
        })->toArray();
        
        return view('admin.inventory.supply.distribute.index',
                    compact('categories', 'allItems', 'branches', 'branchItemStocks'));
    }

    public function create()
    {
        return redirect()->route('admin.inventory.distributions.index');
    }

    public function log()
    {
        try {
            $distributions = InventoryDistribution::with([
                'items' => function($query) {
                    $query->with(['item', 'branch']);
                },
                'distributedBy'
            ])
            ->orderBy('distribution_date', 'desc')
            ->paginate(20);
            
            return view('admin.inventory.supply.dist-log.index', compact('distributions'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error loading distribution log: ' . $e->getMessage()]);
        }
    }

    public function history()
    {
        return $this->log();
    }

    public function store(Request $request)
    {
        $request->validate([
            'distributed_at' => 'required|date',
            'supply_item_id' => 'required|exists:inventory_items,id',
            'quantities' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $item = InventoryItem::findOrFail($request->supply_item_id);
            $central = CentralStock::where('inventory_item_id', $item->id)->first();
            
            if (!$central) {
                throw new \Exception('No central stock record found for ' . $item->name);
            }
            
            $totalSent = array_sum($request->quantities);
            
            if ($totalSent <= 0) {
                throw new \Exception('Please enter at least one quantity to distribute.');
            }

            if ($totalSent > $central->current_stock) {
                throw new \Exception('Cannot distribute more than available warehouse stock (' . $central->current_stock . ' available)');
            }

            $distribution = InventoryDistribution::create([
                'reference_no' => 'DIST-' . date('Y') . '-' . str_pad(InventoryDistribution::count() + 1, 4, '0', STR_PAD_LEFT),
                'distribution_date' => $request->distributed_at,
                'notes' => $request->notes,
                'distributed_by' => auth()->id(),
            ]);

            foreach ($request->quantities as $branchId => $qty) {
                if ($qty <= 0) continue;

                $distribution->items()->create([
                    'inventory_item_id' => $item->id,
                    'branch_id' => $branchId,
                    'quantity' => $qty,
                ]);

                $branchStock = BranchStock::firstOrCreate(
                    ['branch_id' => $branchId, 'inventory_item_id' => $item->id],
                    ['current_stock' => 0, 'cost_price' => $central->cost_price ?? 0]
                );
                $stockBefore = $branchStock->current_stock;
                $branchStock->increment('current_stock', $qty);
                $branchStock->update(['last_updated_at' => now()]);

                StockHistory::create([
                    'inventory_item_id' => $item->id,
                    'branch_id' => $branchId,
                    'type' => 'transfer_in',
                    'quantity' => $qty,
                    'balance_after' => $stockBefore + $qty,
                    'reference_type' => 'App\\Models\\InventoryDistribution',
                    'reference_id' => $distribution->id,
                    'user_id' => auth()->id(),
                    'notes' => 'Distribution: ' . $distribution->reference_no,
                ]);
            }

            $stockBefore = $central->current_stock;
            $central->decrement('current_stock', $totalSent);

            StockHistory::create([
                'inventory_item_id' => $item->id,
                'branch_id' => null,
                'type' => 'transfer_out',
                'quantity' => -$totalSent,
                'balance_after' => $stockBefore - $totalSent,
                'reference_type' => 'App\\Models\\InventoryDistribution',
                'reference_id' => $distribution->id,
                'user_id' => auth()->id(),
                'notes' => 'Distribution out: ' . $distribution->reference_no,
            ]);

            DB::commit();
            return redirect()->route('admin.inventory.distribute.index')
                ->with('success', 'Distribution completed! Branch stocks updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(InventoryDistribution $distribution)
    {
        $distribution->load(['items.item.category', 'items.branch', 'distributedBy']);
        return view('admin.inventory.distributions.show', compact('distribution'));
    }
}
