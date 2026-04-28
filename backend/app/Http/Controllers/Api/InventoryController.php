<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InventoryController extends Controller
{
    /**
     * Get all active inventory items
     */
    public function index(Request $request)
    {
        try {
            $query = InventoryItem::active()->with('category', 'branch');

            // Filter by branch
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            // Filter by category
            if ($request->filled('category_id')) {
                $query->where('inventory_category_id', $request->category_id);
            }

            // Search
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $items = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching inventory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch inventory',
            ], 500);
        }
    }

    /**
     * Get single inventory item
     */
    public function show($id)
    {
        try {
            $item = InventoryItem::find($id);
            
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found',
                ], 404);
            }
            
            if (!$item->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not available',
                ], 403);
            }

            $item->load('category', 'centralStock', 'branchStocks.branch');

            return response()->json([
                'success' => true,
                'data' => $item,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching inventory item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch item',
            ], 500);
        }
    }

    /**
     * Get inventory by branch
     */
    public function byBranch(Branch $branch)
    {
        try {
            if (!$branch->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Branch not found',
                ], 404);
            }

            $items = $branch->inventoryItems()
                ->active()
                ->with('category')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'branch' => $branch,
                'items' => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching branch inventory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch branch inventory',
            ], 500);
        }
    }

    /**
     * Get low stock items
     */
    public function lowStock(Request $request)
    {
        try {
            $query = InventoryItem::lowStock()->active()->with('category', 'branch');

            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            $items = $query->orderBy('current_stock')->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching low stock items: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch low stock items',
            ], 500);
        }
    }

    /**
     * Get out of stock items
     */
    public function outOfStock(Request $request)
    {
        try {
            $query = InventoryItem::outOfStock()->active()->with('category', 'branch');

            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            $items = $query->orderBy('created_at', 'desc')->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching out of stock items: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch out of stock items',
            ], 500);
        }
    }

    /**
     * Get inventory statistics
     */
    public function stats(Request $request)
    {
        try {
            $query = InventoryItem::active();

            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            $items = $query->get();

            $stats = [
                'total_items' => $items->count(),
                'low_stock_count' => $items->filter(fn($item) => $item->isLowStock())->count(),
                'out_of_stock_count' => $items->filter(fn($item) => $item->isOutOfStock())->count(),
                'total_value' => $items->sum(fn($item) => $item->total_value),
                'average_stock_level' => $items->avg('current_stock'),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching inventory stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
            ], 500);
        }
    }

    /**
     * Check stock availability
     */
    public function checkStock(Request $request)
    {
        try {
            $validated = $request->validate([
                'item_id' => 'required|exists:inventory_items,id',
                'quantity' => 'required|numeric|min:0.01',
            ]);

            $item = InventoryItem::find($validated['item_id']);

            if (!$item->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not available',
                ], 404);
            }

            $hasStock = $item->hasStock($validated['quantity']);

            return response()->json([
                'success' => true,
                'available' => $hasStock,
                'current_stock' => $item->current_stock,
                'requested_quantity' => $validated['quantity'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking stock: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check stock',
            ], 500);
        }
    }
}
