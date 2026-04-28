<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSupply extends Model
{
    protected $table = 'service_supplies';

    protected $fillable = [
        'service_id',
        'inventory_item_id',
        'quantity_required',
    ];

    protected $casts = [
        'quantity_required' => 'decimal:2',
    ];

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

    public function getTotalCostAttribute(): float
    {
        return (float) ($this->inventoryItem->unit_cost * $this->quantity_required);
    }

    public function getFormattedCostAttribute(): string
    {
        return '₱' . number_format($this->total_cost, 2);
    }
}
