<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CentralStock extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'quantity_in_bulk',
        'quantity_in_units',
        'current_stock',
        'cost_price',
        'last_purchased_at',
        'reorder_point',
        'max_stock_level',
    ];

    protected $casts = [
        'quantity_in_bulk' => 'decimal:2',
        'quantity_in_units' => 'decimal:2',
        'current_stock' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'last_purchased_at' => 'datetime',
        'reorder_point' => 'decimal:2',
        'max_stock_level' => 'decimal:2',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->reorder_point;
    }

    public function isOutOfStock(): bool
    {
        return $this->current_stock <= 0;
    }

    public function stockPercentage(): float
    {
        if ($this->max_stock_level > 0) {
            return round(($this->current_stock / $this->max_stock_level) * 100, 2);
        }
        return 0;
    }

    public function getCurrentStockAttribute($value)
    {
        return $value ?? $this->attributes['quantity_in_units'] ?? 0;
    }

    public function getCostPriceAttribute($value)
    {
        return $value ?? $this->attributes['unit_cost_price'] ?? 0;
    }
}
