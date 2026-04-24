<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceItem extends Model
{
    protected $fillable = [
        'service_id',
        'inventory_item_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public $timestamps = true;

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    // ========================================================================
    // ACCESSORS
    // ========================================================================

    public function getFormattedQuantityAttribute(): string
    {
        $item = $this->inventoryItem;
        return $this->quantity . ' ' . $item->unit;
    }

    public function getTotalCostAttribute(): float
    {
        return (float) ($this->quantity * $this->inventoryItem->unit_cost);
    }

    public function getFormattedTotalCostAttribute(): string
    {
        return '₱' . number_format($this->total_cost, 2);
    }
}
