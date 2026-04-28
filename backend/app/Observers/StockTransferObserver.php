<?php

namespace App\Observers;

use App\Models\StockTransfer;
use App\Models\AdminNotification;
use App\Models\BranchNotification;

class StockTransferObserver
{
    /**
     * Handle the StockTransfer "created" event.
     */
    public function created(StockTransfer $transfer): void
    {
        $transfer->loadMissing('inventoryItem', 'fromBranch', 'toBranch');

        // 🔔 NOTIFY ADMIN: Stock transfer initiated
        AdminNotification::create([
            'type' => 'stock_transfer',
            'title' => 'Stock Transfer Initiated',
            'message' => "{$transfer->inventoryItem->name} transfer from {$transfer->fromBranch->name} to {$transfer->toBranch->name} - {$transfer->quantity} units",
            'icon' => 'arrow-left-right',
            'color' => 'info',
            'link' => route('admin.inventory.transfers.show', $transfer->id),
            'data' => [
                'transfer_id' => $transfer->id,
                'item_name' => $transfer->inventoryItem->name,
                'quantity' => $transfer->quantity,
                'from_branch' => $transfer->fromBranch->name,
                'to_branch' => $transfer->toBranch->name,
            ],
        ]);

        // 🔔 NOTIFY SOURCE BRANCH: Stock transfer out
        BranchNotification::create([
            'branch_id' => $transfer->from_branch_id,
            'type' => 'stock_transfer_out',
            'title' => 'Stock Transfer Out',
            'message' => "{$transfer->inventoryItem->name} ({$transfer->quantity} units) transferred to {$transfer->toBranch->name}",
            'icon' => 'box-arrow-right',
            'color' => 'warning',
            'link' => route('branch.inventory.index'),
            'data' => [
                'transfer_id' => $transfer->id,
                'item_name' => $transfer->inventoryItem->name,
                'quantity' => $transfer->quantity,
            ],
        ]);

        // 🔔 NOTIFY DESTINATION BRANCH: Stock transfer in
        BranchNotification::create([
            'branch_id' => $transfer->to_branch_id,
            'type' => 'stock_transfer_in',
            'title' => 'Incoming Stock Transfer',
            'message' => "{$transfer->inventoryItem->name} ({$transfer->quantity} units) incoming from {$transfer->fromBranch->name}",
            'icon' => 'box-arrow-in-down',
            'color' => 'success',
            'link' => route('branch.inventory.index'),
            'data' => [
                'transfer_id' => $transfer->id,
                'item_name' => $transfer->inventoryItem->name,
                'quantity' => $transfer->quantity,
            ],
        ]);
    }

    /**
     * Handle the StockTransfer "updated" event.
     */
    public function updated(StockTransfer $transfer): void
    {
        // Check if status changed to completed
        if ($transfer->isDirty('status') && $transfer->status === 'completed') {
            $transfer->loadMissing('inventoryItem', 'fromBranch', 'toBranch');

            // 🔔 NOTIFY ADMIN: Transfer completed
            AdminNotification::create([
                'type' => 'stock_transfer_completed',
                'title' => 'Stock Transfer Completed',
                'message' => "{$transfer->inventoryItem->name} transfer completed successfully",
                'icon' => 'check-circle',
                'color' => 'success',
                'link' => route('admin.inventory.transfers.show', $transfer->id),
            ]);

            // 🔔 NOTIFY DESTINATION BRANCH: Transfer received
            BranchNotification::create([
                'branch_id' => $transfer->to_branch_id,
                'type' => 'stock_transfer_received',
                'title' => 'Stock Transfer Received',
                'message' => "{$transfer->inventoryItem->name} ({$transfer->quantity} units) received successfully",
                'icon' => 'check-circle',
                'color' => 'success',
                'link' => route('branch.inventory.index'),
            ]);
        }
    }
}
