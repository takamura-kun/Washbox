<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\{BranchStock, InventoryItem, StockTransfer, StockHistory};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display branch inventory dashboard
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $branch = auth('branch')->user();

        // Handle admin login with branch_id
        if ($user && $user->user_type === 'admin' && $user->branch_id) {
            $branchId = $user->branch_id;
        }
        // Handle branch staff login
        elseif ($branch && $branch->id) {
            $branchId = $branch->id;
        }
        // Admin without branch_id - redirect to admin inventory
        elseif ($user && $user->user_type === 'admin') {
            return redirect()->route('admin.inventory.index')
                ->with('info', 'Please use the admin inventory page to manage all branches.');
        }
        else {
            abort(403, 'You are not assigned to any branch.');
        }

        $branch = $branch ?? $user->branch;

        if (!$branch) {
            abort(404, 'Branch not found.');
        }

        $search = $request->input('search');
        $status = $request->input('status');

        $query = BranchStock::with(['inventoryItem.category'])
            ->where('branch_id', $branchId);

        if ($search) {
            $query->whereHas('inventoryItem', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($status === 'low_stock') {
            $query->whereColumn('current_stock', '<=', 'reorder_point')
                  ->where('current_stock', '>', 0);
        } elseif ($status === 'out_of_stock') {
            $query->where('current_stock', 0);
        } elseif ($status === 'active') {
            $query->whereColumn('current_stock', '>', 'reorder_point');
        }

        $stocks = $query->paginate(20);

        $items = $stocks->map(function($stock) {
            return (object) [
                'id' => $stock->inventoryItem->id,
                'name' => $stock->inventoryItem->name,
                'category' => $stock->inventoryItem->category,
                'unit' => $stock->inventoryItem->unit,
                'current_stock' => $stock->current_stock,
                'reorder_point' => $stock->reorder_point,
                'unit_cost' => $stock->inventoryItem->unit_cost_price ?? 0,
                'total_value' => $stock->current_stock * ($stock->inventoryItem->unit_cost_price ?? 0),
                'stock_status' => $stock->current_stock == 0 ? 'out_of_stock' : 
                                 ($stock->current_stock <= $stock->reorder_point ? 'low_stock' : 'active'),
            ];
        });

        $items = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $stocks->total(),
            $stocks->perPage(),
            $stocks->currentPage(),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('branch.inventory.index', compact('branch', 'items', 'search', 'status'));
    }

    /**
     * Show single inventory item
     */
    public function show(InventoryItem $item)
    {
        $user = auth()->user();
        $branch = auth('branch')->user();

        if ($user && $user->user_type === 'admin' && $user->branch_id) {
            $branchId = $user->branch_id;
        } elseif ($branch && $branch->id) {
            $branchId = $branch->id;
        } else {
            abort(403);
        }

        $branch = $branch ?? $user->branch;

        $branchStock = BranchStock::where('branch_id', $branchId)
            ->where('inventory_item_id', $item->id)
            ->first();

        $logs = StockHistory::where('branch_id', $branchId)
            ->where('inventory_item_id', $item->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('branch.inventory.show', compact('item', 'branch', 'branchStock', 'logs'));
    }

    /**
     * Export inventory to CSV
     */
    public function export()
    {
        $user = auth()->user();
        $branch = auth('branch')->user();

        if ($user && $user->user_type === 'admin' && $user->branch_id) {
            $branchId = $user->branch_id;
        } elseif ($branch && $branch->id) {
            $branchId = $branch->id;
        } else {
            abort(403);
        }

        $branch = $branch ?? $user->branch;

        $stocks = BranchStock::with(['inventoryItem.category'])
            ->where('branch_id', $branchId)
            ->get();

        $filename = 'inventory_' . $branch->name . '_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($stocks) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Item Name', 'Category', 'Unit', 'Current Stock', 'Reorder Point', 'Unit Cost', 'Total Value', 'Status']);

            foreach ($stocks as $stock) {
                $status = $stock->current_stock == 0 ? 'Out of Stock' : 
                         ($stock->current_stock <= $stock->reorder_point ? 'Low Stock' : 'OK');
                
                fputcsv($file, [
                    $stock->inventoryItem->name,
                    $stock->inventoryItem->category->name ?? 'N/A',
                    $stock->inventoryItem->unit,
                    $stock->current_stock,
                    $stock->reorder_point,
                    $stock->inventoryItem->unit_cost_price ?? 0,
                    $stock->current_stock * ($stock->inventoryItem->unit_cost_price ?? 0),
                    $status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Request stock from central warehouse
     */
    public function requestStock(Request $request)
    {
        $user = auth()->user();

        if ($user->user_type === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admins cannot request stock. Use admin inventory distribution.',
            ], 403);
        }

        $branchId = $user->branch_id;

        $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        // Create stock transfer request
        $transfer = StockTransfer::create([
            'inventory_item_id' => $request->inventory_item_id,
            'from_branch_id' => null, // From central warehouse
            'to_branch_id' => $branchId,
            'quantity' => $request->quantity,
            'status' => 'pending',
            'requested_by' => auth()->id(),
            'notes' => $request->reason,
        ]);

        return redirect()->back()->with('success', 'Stock request submitted. Waiting for admin approval.');
    }

    /**
     * View stock request history
     */
    public function requests()
    {
        $user = auth()->user();

        if ($user->user_type === 'admin') {
            return redirect()->route('admin.inventory.stock-transfers.index');
        }

        $branchId = $user->branch_id;

        $requests = StockTransfer::with(['inventoryItem', 'requestedBy', 'approvedBy'])
            ->where('to_branch_id', $branchId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('branch.inventory.requests', compact('requests'));
    }

    /**
     * View stock movement history
     */
    public function history(Request $request)
    {
        $user = auth()->user();

        if ($user->user_type === 'admin') {
            $branchId = $request->input('branch_id');
            if (!$branchId) {
                return redirect()->route('admin.inventory.index');
            }
        } else {
            $branchId = $user->branch_id;
        }
        $itemId = $request->input('item_id');

        $query = StockHistory::with(['inventoryItem', 'user'])
            ->where('branch_id', $branchId);

        if ($itemId) {
            $query->where('inventory_item_id', $itemId);
        }

        $history = $query->orderBy('created_at', 'desc')->paginate(50);

        $items = InventoryItem::active()->orderBy('name')->get();

        return view('branch.inventory.history', compact('history', 'items', 'itemId'));
    }

    /**
     * View low stock items
     */
    public function lowStock()
    {
        $user = auth()->user();

        if ($user->user_type === 'admin') {
            $branchId = request()->input('branch_id');
            if (!$branchId) {
                return redirect()->route('admin.inventory.index');
            }
        } else {
            $branchId = $user->branch_id;
        }

        $lowStocks = BranchStock::with(['inventoryItem.category'])
            ->where('branch_id', $branchId)
            ->whereColumn('current_stock', '<=', 'reorder_point')
            ->where('current_stock', '>', 0)
            ->get();

        return view('branch.inventory.low-stock', compact('lowStocks'));
    }

    /**
     * View out of stock items
     */
    public function outOfStock()
    {
        $user = auth()->user();

        if ($user->user_type === 'admin') {
            $branchId = request()->input('branch_id');
            if (!$branchId) {
                return redirect()->route('admin.inventory.index');
            }
        } else {
            $branchId = $user->branch_id;
        }

        $outOfStocks = BranchStock::with(['inventoryItem.category'])
            ->where('branch_id', $branchId)
            ->where('current_stock', 0)
            ->get();

        return view('branch.inventory.out-of-stock', compact('outOfStocks'));
    }

    /**
     * Get item details
     */
    public function itemDetails(InventoryItem $item)
    {
        $user = auth()->user();

        if ($user->user_type === 'admin') {
            $branchId = request()->input('branch_id');
            if (!$branchId) {
                abort(400, 'Branch ID required');
            }
        } else {
            $branchId = $user->branch_id;
        }

        $branchStock = BranchStock::where('branch_id', $branchId)
            ->where('inventory_item_id', $item->id)
            ->first();

        $history = StockHistory::where('branch_id', $branchId)
            ->where('inventory_item_id', $item->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('branch.inventory.item-details', compact('item', 'branchStock', 'history'));
    }

    /**
     * Record inventory usage (when used in laundry)
     */
    public function recordUsage(Request $request)
    {
        $user = auth()->user();

        if ($user->user_type === 'admin') {
            $branchId = $request->input('branch_id');
            if (!$branchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Branch ID required',
                ], 400);
            }
        } else {
            $branchId = $user->branch_id;
        }

        $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:1',
            'laundry_id' => 'nullable|exists:laundries,id',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $branchStock = BranchStock::where('branch_id', $branchId)
                ->where('inventory_item_id', $request->inventory_item_id)
                ->firstOrFail();

            if ($branchStock->current_stock < $request->quantity) {
                throw new \Exception('Insufficient stock. Available: ' . $branchStock->current_stock);
            }

            // Deduct stock
            $stockBefore = $branchStock->current_stock;
            $branchStock->decrement('current_stock', $request->quantity);

            // Record history
            StockHistory::create([
                'inventory_item_id' => $request->inventory_item_id,
                'branch_id' => $branchId,
                'type' => 'usage',
                'quantity' => -$request->quantity,
                'balance_after' => $stockBefore - $request->quantity,
                'reference_type' => $request->laundry_id ? 'App\\Models\\Laundry' : null,
                'reference_id' => $request->laundry_id,
                'user_id' => auth()->id(),
                'notes' => $request->notes ?? 'Used in laundry service',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usage recorded successfully',
                'remaining_stock' => $branchStock->current_stock,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
