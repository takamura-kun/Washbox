<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\InventoryItem;
use App\Models\Branch;
use App\Models\CentralStock;
use App\Models\BranchStock;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function index()
    {
        $transfers = StockTransfer::with(['inventoryItem', 'toBranch', 'transferredBy'])
            ->latest()
            ->paginate(20);

        return view('admin.inventory.transfers.index', compact('transfers'));
    }

    public function create()
    {
        $items = InventoryItem::active()
            ->with(['category', 'centralStock'])
            ->get();
        $branches = Branch::active()->get();
        
        return view('admin.inventory.transfers.create', compact('items', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'to_branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $item = InventoryItem::findOrFail($validated['inventory_item_id']);
            $centralStock = CentralStock::where('inventory_item_id', $item->id)->firstOrFail();

            if ($centralStock->quantity_in_units < $validated['quantity']) {
                return back()->with('error', 'Insufficient stock in central warehouse')->withInput();
            }

            $transfer = StockTransfer::create([
                'inventory_item_id' => $item->id,
                'to_branch_id' => $validated['to_branch_id'],
                'transferred_by' => auth()->id(),
                'quantity' => $validated['quantity'],
                'status' => 'in_transit',
                'notes' => $validated['notes'],
                'transferred_at' => now(),
            ]);

            $centralStock->quantity_in_units -= $validated['quantity'];
            $centralStock->save();

            StockHistory::create([
                'inventory_item_id' => $item->id,
                'branch_id' => null,
                'type' => 'transfer_out',
                'quantity' => -$validated['quantity'],
                'balance_after' => $centralStock->quantity_in_units,
                'reference_type' => StockTransfer::class,
                'reference_id' => $transfer->id,
                'user_id' => auth()->id(),
                'notes' => "Transfer to branch: {$transfer->toBranch->name}",
            ]);

            DB::commit();
            return redirect()->route('admin.transfers.index')->with('success', 'Stock transfer created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create transfer: ' . $e->getMessage())->withInput();
        }
    }

    public function receive(StockTransfer $transfer)
    {
        if ($transfer->status === 'received') {
            return back()->with('error', 'Transfer already received');
        }

        DB::beginTransaction();
        try {
            $branchStock = BranchStock::firstOrCreate(
                [
                    'branch_id' => $transfer->to_branch_id,
                    'inventory_item_id' => $transfer->inventory_item_id,
                ],
                ['current_stock' => 0]
            );

            $branchStock->current_stock += $transfer->quantity;
            $branchStock->save();

            $transfer->update([
                'status' => 'received',
                'received_at' => now(),
                'received_by' => auth()->id(),
            ]);

            StockHistory::create([
                'inventory_item_id' => $transfer->inventory_item_id,
                'branch_id' => $transfer->to_branch_id,
                'type' => 'transfer_in',
                'quantity' => $transfer->quantity,
                'balance_after' => $branchStock->current_stock,
                'reference_type' => StockTransfer::class,
                'reference_id' => $transfer->id,
                'user_id' => auth()->id(),
                'notes' => "Received transfer from central warehouse",
            ]);

            DB::commit();
            return back()->with('success', 'Stock transfer received successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to receive transfer: ' . $e->getMessage());
        }
    }
}
