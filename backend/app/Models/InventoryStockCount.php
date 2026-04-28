<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStockCount extends Model
{
    protected $fillable = [
        'branch_id',
        'inventory_item_id',
        'system_stock',
        'actual_stock',
        'discrepancy',
        'count_date',
        'notes',
        'counted_by',
    ];

    protected $casts = [
        'system_stock' => 'integer',
        'actual_stock' => 'integer',
        'discrepancy' => 'integer',
        'count_date' => 'date',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function countedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }
}
