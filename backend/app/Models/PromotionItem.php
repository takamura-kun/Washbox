<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionItem extends Model
{
    protected $fillable = [
        'promotion_id',
        'inventory_item_id',
        'quantity_per_use',
        'is_active',
    ];

    protected $casts = [
        'quantity_per_use' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function getFormattedQuantityAttribute(): string
    {
        return $this->quantity_per_use . ' ' . ($this->inventoryItem->distribution_unit ?? 'unit');
    }
}
