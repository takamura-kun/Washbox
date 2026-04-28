<?php

namespace App\Observers;

use App\Models\InventoryItem;
use App\Models\ActivityLog;
use App\Models\DeletedRecord;

class InventoryItemObserver
{
    public function deleting(InventoryItem $item): void
    {
        DeletedRecord::snapshot($item, 'inventory');
        ActivityLog::log('deleted', "Inventory item \"{$item->name}\" deleted", 'inventory', null, [
            'name' => $item->name,
            'sku'  => $item->sku ?? null,
        ]);
    }
}
