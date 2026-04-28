<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryDistributionItem extends Model
{
    protected $fillable = [
        'inventory_distribution_id',
        'inventory_item_id',
        'branch_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(InventoryDistribution::class, 'inventory_distribution_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
