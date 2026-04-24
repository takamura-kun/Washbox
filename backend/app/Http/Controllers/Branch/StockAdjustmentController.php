<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\BranchStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    private function getBranchId()
    {
        if (auth()->guard('branch')->check()) {
            return auth()->guard('branch')->user()->id;
        }
        return auth()->user()->branch_id;
    }

    public function index(Request $request)
    {
        $branchId = $this->getBranchId();
        
        $query = InventoryAdjustment::where('branch_id', $branchId)
            ->with(['item', 'adjustedBy', 'approvedBy']);

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

        // Summary
        $summary = [
            'total' => InventoryAdjustment::where('branch_id', $branchId)->count(),
            'pending' => InventoryAdjustment::where('branch_id', $branchId)->where('status', 'pending')->count(),
            'approved' => InventoryAdjustment::where('branch_id', $branchId)->where('status', 'approved')->count(),
            'total_value_loss' => InventoryAdjustment::where('branch_id', $branchId)->where('status', 'approved')->sum('value_loss'),
        ];

        return view('branch.adjustments.index', compact('adjustments', 'summary'));
    }

    public function create()
    {
        $branchId = $this->getBranchId();
        
        // Get items with stock in this branch
        $items = BranchStock::where('branch_id', $branchId)
            ->with(['inventoryItem'])
            ->get()
            ->filter(function($stock) {
                return $stock->inventoryItem !== null;
            });

        return view('branch.adjustments.create', compact('items'));
    }

    public function store(Request $request)
    {
        $branchId = $this->getBranchId();

        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'type' => 'required|in:damaged,expired,lost,theft,spoilage',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'photo_proof' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        DB::beginTransaction();
        try {
            // Check stock availability
            $branchStock = BranchStock::where('branch_id', $branchId)
                ->where('inventory_item_id', $validated['inventory_item_id'])
                ->first();

            if (!$branchStock) {
                return back()->with('error', 'Item not found in branch stock!');
            }

            // For reductions, check if we have enough stock
            $adjustmentQty = -abs($validated['quantity']); // Always negative for branch adjustments
            if ($branchStock->current_stock < abs($adjustmentQty)) {
                return back()->with('error', 'Insufficient stock for this adjustment!');
            }

            // Calculate value loss
            $unitCost = $branchStock->cost_price ?? $branchStock->inventoryItem->unit_cost_price;
            $valueLoss = abs($adjustmentQty) * $unitCost;

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo_proof')) {
                $photoPath = $request->file('photo_proof')->store('adjustments', 'public');
            }

            // Create adjustment (pending approval)
            InventoryAdjustment::create([
                'branch_id' => $branchId,
                'inventory_item_id' => $validated['inventory_item_id'],
                'type' => $validated['type'],
                'quantity' => $adjustmentQty,
                'value_loss' => $valueLoss,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
                'photo_proof' => $photoPath,
                'adjusted_by' => auth()->id(),
                'status' => 'pending',
            ]);

            DB::commit();

            return redirect()->route('branch.adjustments.index')
                ->with('success', 'Stock adjustment submitted for approval!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to submit adjustment: ' . $e->getMessage());
        }
    }

    public function show(InventoryAdjustment $adjustment)
    {
        $branchId = $this->getBranchId();
        
        if ($adjustment->branch_id !== $branchId) {
            abort(403, 'Unauthorized access');
        }

        $adjustment->load(['item', 'adjustedBy', 'approvedBy']);

        return view('branch.adjustments.show', compact('adjustment'));
    }
}
