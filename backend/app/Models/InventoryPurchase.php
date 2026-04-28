<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryPurchase extends Model
{
    protected $fillable = [
        'reference_no',
        'purchase_order_number',
        'purchase_date',
        'branch_id',
        'supplier',
        'supplier_id',
        'grand_total',
        'total_cost',
        'notes',
        'purchased_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'grand_total' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryPurchaseItem::class);
    }

    public function purchasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchased_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
