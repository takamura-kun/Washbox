<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{StockTransfer, CentralStock, BranchStock, StockHistory};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    /**
     * Display pending stock transfer requests
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $transfers = StockTransfer::with(['inventoryItem', 'toBranch', 'requestedBy', 'approvedBy'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $summary = [
            'pending' => StockTransfer::where('status', 'pending')->count(),
            'approved' => StockTransfer::where('status', 'approved')->count(),
            'completed' => StockTransfer::where('status', 'completed')->count(),
            'rejected' => StockTransfer::where('status', 'rejected')->count(),
        ];

        return view('admin.inventory.stock-transfers.index', compact('transfers', 'summary', 'status'));
    }

    /**
     * Approve stock transfer request
     */
    public function approve(StockTransfer $transfer)
    {
        if ($transfer->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending requests can be approved');
        }

        DB::beginTransaction();
        try {
            // Check central stock availability
            $centralStock = CentralStock::where('inventory_item_id', $transfer->inventory_item_id)->first();
            
            if (!$centralStock || $centralStock->current_stock < $transfer->quantity) {
                throw new \Exception('Insufficient stock in central warehouse. Available: ' . ($centralStock->current_stock ?? 0));
            }

            // Deduct from central warehouse
            $centralStockBefore = $centralStock->current_stock;
            $centralStock->decrement('current_stock', $transfer->quantity);

            // Record central warehouse history
            StockHistory::create([
                'inventory_item_id' => $transfer->inventory_item_id,
                'branch_id' => null,
                'type' => 'transfer_out',
                'quantity' => -$transfer->quantity,
                'balance_after' => $centralStockBefore - $transfer->quantity,
                'reference_type' => StockTransfer::class,
                'reference_id' => $transfer->id,
                'user_id' => auth()->id(),
                'notes' => "Transfer to {$transfer->toBranch->name}",
            ]);

            // Add to branch stock
            $branchStock = BranchStock::firstOrCreate(
                [
                    'branch_id' => $transfer->to_branch_id,
                    'inventory_item_id' => $transfer->inventory_item_id,
                ],
                [
                    'current_stock' => 0,
                    'reorder_point' => $transfer->inventoryItem->reorder_point ?? 0,
                    'max_stock_level' => $transfer->inventoryItem->max_level ?? 0,
                ]
            );

            $branchStockBefore = $branchStock->current_stock;
            $branchStock->increment('current_stock', $transfer->quantity);

            // Record branch history
            StockHistory::create([
                'inventory_item_id' => $transfer->inventory_item_id,
                'branch_id' => $transfer->to_branch_id,
                'type' => 'transfer_in',
                'quantity' => $transfer->quantity,
                'balance_after' => $branchStockBefore + $transfer->quantity,
                'reference_type' => StockTransfer::class,
                'reference_id' => $transfer->id,
                'user_id' => auth()->id(),
                'notes' => 'Transfer from central warehouse',
            ]);

            // Mark transfer as completed
            $transfer->approve(auth()->id());
            $transfer->complete();

            DB::commit();

            return redirect()->back()->with('success', 'Stock transfer approved and completed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to approve transfer: ' . $e->getMessage());
        }
    }

    /**
     * Reject stock transfer request
     */
    public function reject(Request $request, StockTransfer $transfer)
    {
        $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        if ($transfer->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending requests can be rejected');
        }

        $transfer->reject(auth()->id(), $request->rejection_reason);

        return redirect()->back()->with('success', 'Stock transfer request rejected');
    }

    /**
     * Bulk approve transfers
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'transfer_ids' => 'required|array',
            'transfer_ids.*' => 'exists:stock_transfers,id',
        ]);

        $successCount = 0;
        $failCount = 0;
        $errors = [];

        foreach ($request->transfer_ids as $transferId) {
            $transfer = StockTransfer::find($transferId);
            
            if ($transfer->status !== 'pending') {
                $failCount++;
                $errors[] = "Transfer #{$transferId} is not pending";
                continue;
            }

            DB::beginTransaction();
            try {
                $centralStock = CentralStock::where('inventory_item_id', $transfer->inventory_item_id)->first();
                
                if (!$centralStock || $centralStock->current_stock < $transfer->quantity) {
                    throw new \Exception('Insufficient stock');
                }

                // Deduct from central
                $centralStockBefore = $centralStock->current_stock;
                $centralStock->decrement('current_stock', $transfer->quantity);

                StockHistory::create([
                    'inventory_item_id' => $transfer->inventory_item_id,
                    'branch_id' => null,
                    'type' => 'transfer_out',
                    'quantity' => -$transfer->quantity,
                    'balance_after' => $centralStockBefore - $transfer->quantity,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'user_id' => auth()->id(),
                    'notes' => "Bulk transfer to {$transfer->toBranch->name}",
                ]);

                // Add to branch
                $branchStock = BranchStock::firstOrCreate(
                    [
                        'branch_id' => $transfer->to_branch_id,
                        'inventory_item_id' => $transfer->inventory_item_id,
                    ],
                    [
                        'current_stock' => 0,
                        'reorder_point' => $transfer->inventoryItem->reorder_point ?? 0,
                        'max_stock_level' => $transfer->inventoryItem->max_level ?? 0,
                    ]
                );

                $branchStockBefore = $branchStock->current_stock;
                $branchStock->increment('current_stock', $transfer->quantity);

                StockHistory::create([
                    'inventory_item_id' => $transfer->inventory_item_id,
                    'branch_id' => $transfer->to_branch_id,
                    'type' => 'transfer_in',
                    'quantity' => $transfer->quantity,
                    'balance_after' => $branchStockBefore + $transfer->quantity,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'user_id' => auth()->id(),
                    'notes' => 'Bulk transfer from central warehouse',
                ]);

                $transfer->approve(auth()->id());
                $transfer->complete();

                DB::commit();
                $successCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                $failCount++;
                $errors[] = "Transfer #{$transferId}: " . $e->getMessage();
            }
        }

        $message = "{$successCount} transfers approved successfully";
        if ($failCount > 0) {
            $message .= ", {$failCount} failed";
        }

        return redirect()->back()
            ->with('success', $message)
            ->with('errors', $errors);
    }
}
