<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BranchStock;
use App\Models\InventoryAdjustment;
use App\Models\InventoryItem;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InventoryAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryAdjustment::with(['branch', 'item', 'adjustedBy', 'approvedBy']);

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $adjustments = $query->latest()->paginate(20);
        $branches = Branch::all();

        // Summary
        $summary = [
            'total' => InventoryAdjustment::count(),
            'pending' => InventoryAdjustment::where('status', 'pending')->count(),
            'approved' => InventoryAdjustment::where('status', 'approved')->count(),
            'total_value_loss' => InventoryAdjustment::where('status', 'approved')->sum('value_loss'),
        ];

        return view('admin.inventory.adjustments.index', compact('adjustments', 'branches', 'summary'));
    }

    public function create()
    {
        $branches = Branch::active()->get();
        return view('admin.inventory.adjustments.create', compact('branches'));
    }

    public function getBranchItems($branchId)
    {
        $items = BranchStock::where('branch_id', $branchId)
            ->with(['inventoryItem'])
            ->get()
            ->map(function($stock) {
                return [
                    'id' => $stock->inventory_item_id,
                    'name' => $stock->inventoryItem->name,
                    'brand' => $stock->inventoryItem->brand,
                    'sku' => $stock->inventoryItem->sku,
                    'current_stock' => $stock->current_stock,
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
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'type' => 'required|in:damaged,expired,lost,found,correction,theft,spoilage',
            'quantity' => 'required|integer',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'photo_proof' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        DB::beginTransaction();
        try {
            // Check if adjustment would cause negative stock
            $branchStock = BranchStock::where('branch_id', $validated['branch_id'])
                ->where('inventory_item_id', $validated['inventory_item_id'])
                ->first();

            if (!$branchStock) {
                return back()->with('error', 'Item not found in branch stock!');
            }

            $newStock = $branchStock->current_stock + $validated['quantity'];
            if ($newStock < 0) {
                return back()->with('error', 'Adjustment would cause negative stock!');
            }

            // Calculate value loss (for reductions only)
            $valueLoss = 0;
            if ($validated['quantity'] < 0) {
                $unitCost = $branchStock->cost_price ?? $branchStock->inventoryItem->unit_cost_price;
                $valueLoss = abs($validated['quantity']) * $unitCost;
            }

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo_proof')) {
                $photoPath = $request->file('photo_proof')->store('adjustments', 'public');
            }

            // Create adjustment (auto-approved for admin)
            $adjustment = InventoryAdjustment::create([
                'branch_id' => $validated['branch_id'],
                'inventory_item_id' => $validated['inventory_item_id'],
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'value_loss' => $valueLoss,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
                'photo_proof' => $photoPath,
                'adjusted_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'status' => 'approved',
            ]);

            // Apply stock adjustment immediately
            $branchStock->current_stock = $newStock;
            $branchStock->last_updated_at = now();
            $branchStock->save();

            DB::commit();

            return redirect()->route('admin.inventory.adjustments.index')
                ->with('success', 'Stock adjustment recorded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record adjustment: ' . $e->getMessage());
        }
    }

    public function show(InventoryAdjustment $adjustment)
    {
        $adjustment->load(['branch', 'item', 'adjustedBy', 'approvedBy']);
        return view('admin.inventory.adjustments.show', compact('adjustment'));
    }

    public function approve(InventoryAdjustment $adjustment)
    {
        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'Only pending adjustments can be approved!');
        }

        DB::beginTransaction();
        try {
            $adjustment->approve(auth()->id());
            DB::commit();

            return back()->with('success', 'Adjustment approved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve adjustment: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, InventoryAdjustment $adjustment)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'Only pending adjustments can be rejected!');
        }

        $adjustment->reject(auth()->id(), $validated['rejection_reason']);

        return back()->with('success', 'Adjustment rejected!');
    }

    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'adjustment_ids' => 'required|array',
            'adjustment_ids.*' => 'exists:inventory_adjustments,id',
        ]);

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($validated['adjustment_ids'] as $id) {
                $adjustment = InventoryAdjustment::find($id);
                if ($adjustment && $adjustment->status === 'pending') {
                    $adjustment->approve(auth()->id());
                    $count++;
                }
            }

            DB::commit();

            return back()->with('success', "{$count} adjustments approved successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve adjustments: ' . $e->getMessage());
        }
    }
}
