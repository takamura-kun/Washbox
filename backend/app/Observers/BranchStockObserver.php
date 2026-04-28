<?php

namespace App\Observers;

use App\Models\BranchStock;
use App\Models\AdminNotification;
use App\Models\BranchNotification;

class BranchStockObserver
{
    /**
     * Handle the BranchStock "updated" event.
     */
    public function updated(BranchStock $stock): void
    {
        // Check if stock level changed
        if ($stock->isDirty('current_stock')) {
            $stock->loadMissing('inventoryItem', 'branch');

            $oldStock = $stock->getOriginal('current_stock');
            $newStock = $stock->current_stock;

            // 🔔 LOW STOCK ALERT: Stock below reorder point
            if ($newStock <= $stock->reorder_point && $oldStock > $stock->reorder_point) {
                // Notify Admin
                AdminNotification::create([
                    'type' => 'low_stock_alert',
                    'title' => '⚠️ Low Stock Alert',
                    'message' => "{$stock->inventoryItem->name} at {$stock->branch->name} is low ({$newStock} units remaining)",
                    'icon' => 'exclamation-triangle',
                    'color' => 'warning',
                    'link' => route('admin.inventory.items.show', $stock->inventory_item_id),
                    'data' => [
                        'item_name' => $stock->inventoryItem->name,
                        'branch_name' => $stock->branch->name,
                        'current_stock' => $newStock,
                        'reorder_point' => $stock->reorder_point,
                    ],
                    'branch_id' => $stock->branch_id,
                ]);

                // Notify Branch
                BranchNotification::create([
                    'branch_id' => $stock->branch_id,
                    'type' => 'low_stock_alert',
                    'title' => '⚠️ Low Stock Alert',
                    'message' => "{$stock->inventoryItem->name} is running low ({$newStock} units remaining)",
                    'icon' => 'exclamation-triangle',
                    'color' => 'warning',
                    'link' => route('branch.inventory.index'),
                    'data' => [
                        'item_name' => $stock->inventoryItem->name,
                        'current_stock' => $newStock,
                        'reorder_point' => $stock->reorder_point,
                    ],
                ]);
            }

            // 🔔 CRITICAL STOCK ALERT: Stock at or below 10% of reorder point
            if ($newStock > 0 && $newStock <= ($stock->reorder_point * 0.1) && $oldStock > ($stock->reorder_point * 0.1)) {
                AdminNotification::create([
                    'type' => 'critical_stock_alert',
                    'title' => '🚨 CRITICAL Stock Alert',
                    'message' => "{$stock->inventoryItem->name} at {$stock->branch->name} is critically low ({$newStock} units)",
                    'icon' => 'exclamation-octagon',
                    'color' => 'danger',
                    'link' => route('admin.inventory.items.show', $stock->inventory_item_id),
                    'data' => [
                        'item_name' => $stock->inventoryItem->name,
                        'branch_name' => $stock->branch->name,
                        'current_stock' => $newStock,
                    ],
                    'branch_id' => $stock->branch_id,
                ]);

                BranchNotification::create([
                    'branch_id' => $stock->branch_id,
                    'type' => 'critical_stock_alert',
                    'title' => '🚨 CRITICAL Stock Alert',
                    'message' => "{$stock->inventoryItem->name} is critically low ({$newStock} units) - Urgent reorder needed!",
                    'icon' => 'exclamation-octagon',
                    'color' => 'danger',
                    'link' => route('branch.inventory.index'),
                ]);
            }

            // 🔔 OUT OF STOCK ALERT
            if ($newStock == 0 && $oldStock > 0) {
                AdminNotification::create([
                    'type' => 'out_of_stock',
                    'title' => '❌ Out of Stock',
                    'message' => "{$stock->inventoryItem->name} at {$stock->branch->name} is OUT OF STOCK",
                    'icon' => 'x-octagon',
                    'color' => 'danger',
                    'link' => route('admin.inventory.items.show', $stock->inventory_item_id),
                    'data' => [
                        'item_name' => $stock->inventoryItem->name,
                        'branch_name' => $stock->branch->name,
                    ],
                    'branch_id' => $stock->branch_id,
                ]);

                BranchNotification::create([
                    'branch_id' => $stock->branch_id,
                    'type' => 'out_of_stock',
                    'title' => '❌ Out of Stock',
                    'message' => "{$stock->inventoryItem->name} is OUT OF STOCK - Immediate action required!",
                    'icon' => 'x-octagon',
                    'color' => 'danger',
                    'link' => route('branch.inventory.index'),
                ]);
            }
        }
    }
}
