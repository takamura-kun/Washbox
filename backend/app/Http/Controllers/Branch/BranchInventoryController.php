<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BranchInventoryController extends Controller
{
    /**
     * Display branch inventory items
     */
    public function index(Request $request, Branch $branch)
    {
        try {
            // Verify access
            $this->authorizeBranch($branch);

            $query = $branch->inventoryItems();

            // Filter by status
            if ($request->filled('status')) {
                match ($request->status) {
                    'active' => $query->active(),
                    'low_stock' => $query->lowStock(),
                    'out_of_stock' => $query->outOfStock(),
                    'overstock' => $query->overstock(),
                    default => null,
                };
            }

            // Filter by category
            if ($request->filled('category_id')) {
                $query->where('inventory_category_id', $request->category_id);
            }

            // Search
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $items = $query->orderBy('name')->paginate(15);
            $categories = InventoryCategory::active()->get();

            return view('branch.inventory.index', [
                'branch' => $branch,
                'items' => $items,
                'categories' => $categories,
                'status' => $request->status,
                'search' => $request->search,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching branch inventory: ' . $e->getMessage());
            return back()->with('error', 'Failed to fetch inventory');
        }
    }

    /**
     * Show inventory item details
     */
    public function show(Request $request, Branch $branch, InventoryItem $item)
    {
        try {
            $this->authorizeBranch($branch);

            if ($item->branch_id !== $branch->id) {
                abort(404);
            }

            $item->load('category', 'purchases', 'costHistory');
            $logs = $item->inventoryLogs()
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return view('branch.inventory.show', [
                'branch' => $branch,
                'item' => $item,
                'logs' => $logs,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching inventory item: ' . $e->getMessage());
            return back()->with('error', 'Failed to fetch inventory item');
        }
    }

    /**
     * Show create form
     */
    public function create(Branch $branch)
    {
        $this->authorizeBranch($branch);
        $categories = InventoryCategory::active()->get();
        return view('branch.inventory.create', [
            'branch' => $branch,
            'categories' => $categories,
        ]);
    }

    /**
     * Store new inventory item
     */
    public function store(Request $request, Branch $branch)
    {
        try {
            $this->authorizeBranch($branch);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'inventory_category_id' => 'required|exists:inventory_categories,id',
                'description' => 'nullable|string|max:1000',
                'purchase_unit' => 'required|string|max:50',
                'cost_price' => 'required|numeric|min:0',
                'unit' => 'required|string|max:50',
                'current_stock' => 'required|integer|min:0',
                'unit_cost' => 'required|numeric|min:0',
                'reorder_point' => 'required|integer|min:0',
                'max_level' => 'required|integer|min:0',
            ]);

            $validated['branch_id'] = $branch->id;
            $validated['is_active'] = true;

            $item = InventoryItem::create($validated);

            // Log initial stock
            $item->inventoryLogs()->create([
                'quantity_change' => $validated['current_stock'],
                'stock_after' => $validated['current_stock'],
                'reason' => 'initial_stock',
            ]);

            return redirect()->route('branch.inventory.show', [$branch, $item])
                ->with('success', 'Inventory item created successfully');
        } catch (\Exception $e) {
            Log::error('Error creating inventory item: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create inventory item');
        }
    }

    /**
     * Show edit form
     */
    public function edit(Branch $branch, InventoryItem $item)
    {
        $this->authorizeBranch($branch);

        if ($item->branch_id !== $branch->id) {
            abort(404);
        }

        $categories = InventoryCategory::active()->get();
        return view('branch.inventory.edit', [
            'branch' => $branch,
            'item' => $item,
            'categories' => $categories,
        ]);
    }

    /**
     * Update inventory item
     */
    public function update(Request $request, Branch $branch, InventoryItem $item)
    {
        try {
            $this->authorizeBranch($branch);

            if ($item->branch_id !== $branch->id) {
                abort(404);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'inventory_category_id' => 'required|exists:inventory_categories,id',
                'description' => 'nullable|string|max:1000',
                'purchase_unit' => 'required|string|max:50',
                'cost_price' => 'required|numeric|min:0',
                'unit' => 'required|string|max:50',
                'unit_cost' => 'required|numeric|min:0',
                'reorder_point' => 'required|integer|min:0',
                'max_level' => 'required|integer|min:0',
            ]);

            $item->update($validated);

            return redirect()->route('branch.inventory.show', [$branch, $item])
                ->with('success', 'Inventory item updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating inventory item: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update inventory item');
        }
    }

    /**
     * Delete inventory item
     */
    public function destroy(Branch $branch, InventoryItem $item)
    {
        try {
            $this->authorizeBranch($branch);

            if ($item->branch_id !== $branch->id) {
                abort(404);
            }

            $itemName = $item->name;
            $item->delete();

            return redirect()->route('branch.inventory.index', $branch)
                ->with('success', "Inventory item '{$itemName}' deleted successfully");
        } catch (\Exception $e) {
            Log::error('Error deleting inventory item: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete inventory item');
        }
    }

    /**
     * Adjust inventory stock
     */
    public function adjust(Request $request, Branch $branch, InventoryItem $item)
    {
        try {
            $this->authorizeBranch($branch);

            if ($item->branch_id !== $branch->id) {
                abort(404);
            }

            $validated = $request->validate([
                'quantity' => 'required|integer',
                'reason' => 'required|string|max:255',
            ]);

            if ($validated['quantity'] > 0) {
                $item->addStock($validated['quantity'], $validated['reason']);
            } elseif ($validated['quantity'] < 0) {
                if (!$item->deductStock(abs($validated['quantity']), $validated['reason'])) {
                    return back()->with('error', 'Insufficient stock to deduct');
                }
            }

            return back()->with('success', 'Stock adjusted successfully');
        } catch (\Exception $e) {
            Log::error('Error adjusting stock: ' . $e->getMessage());
            return back()->with('error', 'Failed to adjust stock');
        }
    }

    /**
     * View low stock items
     */
    public function lowStock(Branch $branch)
    {
        try {
            $this->authorizeBranch($branch);

            $items = $branch->inventoryItems()
                ->lowStock()
                ->with('category')
                ->orderBy('current_stock')
                ->paginate(20);

            return view('branch.inventory.low-stock', [
                'branch' => $branch,
                'items' => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching low stock items: ' . $e->getMessage());
            return back()->with('error', 'Failed to fetch low stock items');
        }
    }

    /**
     * Export inventory report
     */
    public function export(Branch $branch)
    {
        try {
            $this->authorizeBranch($branch);

            $items = $branch->inventoryItems()->with('category')->get();
            $filename = "inventory_{$branch->code}_" . date('Y-m-d_H-i-s') . '.csv';

            $handle = fopen('php://memory', 'w');
            fputcsv($handle, ['Name', 'Category', 'Unit', 'Quantity', 'Unit Cost', 'Total Value', 'Status']);

            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->name,
                    $item->category->name ?? 'N/A',
                    $item->unit,
                    $item->current_stock,
                    $item->unit_cost,
                    $item->current_stock * $item->unit_cost,
                    $item->is_active ? 'Active' : 'Inactive',
                ]);
            }

            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting inventory: ' . $e->getMessage());
            return back()->with('error', 'Failed to export inventory');
        }
    }

    /**
     * Authorize branch access
     */
    private function authorizeBranch(Branch $branch)
    {
        $user = auth()->user();
        
        // Admin can access all branches
        if ($user->role === 'admin') {
            return;
        }

        // Branch owner can only access their branch
        if ($user->branch_id !== $branch->id) {
            abort(403, 'Unauthorized access to this branch');
        }
    }
}
