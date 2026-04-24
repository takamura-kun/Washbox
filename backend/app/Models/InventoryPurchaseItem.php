<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryPurchaseItem extends Model
{
    protected $fillable = [
        'inventory_purchase_id',
        'inventory_item_id',
        'purchase_unit',
        'quantity',
        'cost_per_bulk',
        'cost_per_unit',
        'units_received',
        'total_cost',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'units_received' => 'integer',
        'cost_per_bulk' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(InventoryPurchase::class, 'inventory_purchase_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
