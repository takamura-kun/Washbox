<?php

namespace App\Observers;

use App\Models\InventoryPurchase;
use App\Models\AdminNotification;
use App\Models\BranchNotification;

class InventoryPurchaseObserver
{
    public function created(InventoryPurchase $purchase)
    {
        $purchase->loadMissing('supplier', 'branch');
        $supplierName = $purchase->supplier?->name ?? 'Unknown Supplier';

        // 🔔 NOTIFY ADMIN: New inventory purchase
        AdminNotification::create([
            'type' => 'inventory_purchase',
            'title' => 'New Inventory Purchase',
            'message' => "Purchase order {$purchase->purchase_order_number} from {$supplierName} - ₱" . number_format($purchase->total_cost, 2),
            'icon' => 'box-seam',
            'color' => 'info',
            'link' => route('admin.inventory.purchases.show', $purchase->id),
            'data' => [
                'purchase_id' => $purchase->id,
                'po_number' => $purchase->purchase_order_number,
                'supplier_name' => $supplierName,
                'total_cost' => $purchase->total_cost,
            ],
            'branch_id' => $purchase->branch_id,
        ]);

        // 🔔 NOTIFY BRANCH: New inventory purchase
        if ($purchase->branch_id) {
            BranchNotification::create([
                'branch_id' => $purchase->branch_id,
                'type' => 'inventory_purchase',
                'title' => 'New Inventory Purchase',
                'message' => "Purchase order {$purchase->purchase_order_number} - ₱" . number_format($purchase->total_cost, 2),
                'icon' => 'box-seam',
                'color' => 'info',
                'link' => route('branch.inventory.index'),
                'data' => [
                    'purchase_id' => $purchase->id,
                    'po_number' => $purchase->purchase_order_number,
                    'total_cost' => $purchase->total_cost,
                ],
            ]);
        }
    }

    public function updated(InventoryPurchase $purchase)
    {
        // Check if status changed to received
        if ($purchase->isDirty('status') && $purchase->status === 'received') {
            $purchase->loadMissing('supplier', 'branch');
            $supplierName = $purchase->supplier?->name ?? 'Unknown Supplier';

            // 🔔 NOTIFY ADMIN: Inventory received
            AdminNotification::create([
                'type' => 'inventory_received',
                'title' => 'Inventory Received',
                'message' => "Purchase order {$purchase->purchase_order_number} has been received",
                'icon' => 'check-circle',
                'color' => 'success',
                'link' => route('admin.inventory.purchases.show', $purchase->id),
                'branch_id' => $purchase->branch_id,
            ]);

            // 🔔 NOTIFY BRANCH: Inventory received
            if ($purchase->branch_id) {
                BranchNotification::create([
                    'branch_id' => $purchase->branch_id,
                    'type' => 'inventory_received',
                    'title' => 'Inventory Received',
                    'message' => "Purchase order {$purchase->purchase_order_number} has been received",
                    'icon' => 'check-circle',
                    'color' => 'success',
                    'link' => route('branch.inventory.index'),
                ]);
            }
        }
    }
}
