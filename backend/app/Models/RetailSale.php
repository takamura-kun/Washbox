<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailSale extends Model
{
    protected $fillable = [
        'sale_number',
        'branch_id',
        'inventory_item_id',
        'item_name',
        'quantity',
        'unit_price',
        'total_amount',
        'payment_method',
        'notes',
        'sold_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    // ========================================================================
    // SCOPES
    // ========================================================================

    public function scopeByBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // ========================================================================
    // ACCESSORS
    // ========================================================================

    public function getFormattedTotalAttribute(): string
    {
        return '₱' . number_format($this->total_amount, 2);
    }

    // ========================================================================
    // METHODS
    // ========================================================================

    public static function generateSaleNumber(): string
    {
        $prefix = 'RS-' . date('Ymd');
        $lastSale = self::where('sale_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastSale) {
            return $prefix . '-0001';
        }

        $lastNumber = (int) substr($lastSale->sale_number, -4);
        return $prefix . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }
}
