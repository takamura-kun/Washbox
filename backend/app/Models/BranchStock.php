<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchStock extends Model
{
    protected $fillable = [
        'branch_id',
        'inventory_item_id',
        'current_stock',
        'cost_price',
        'last_updated_at',
        'reorder_point',
        'max_stock_level',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'last_updated_at' => 'datetime',
        'reorder_point' => 'decimal:2',
        'max_stock_level' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

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
}
