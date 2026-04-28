<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailSaleItem extends Model
{
    protected $fillable = [
        'retail_sale_id',
        'inventory_item_id',
        'quantity',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function retailSale(): BelongsTo
    {
        return $this->belongsTo(RetailSale::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
