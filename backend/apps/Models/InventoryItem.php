<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'brand',
        'supplier_name',
        'supplier_contact',
        'description',
        'sku',
        'barcode',
        'image_path',
        'storage_location',
        'lead_time_days',
        'has_expiration',
        'notes',
        'supply_type',
        'purchase_unit',
        'bulk_unit',
        'distribution_unit',
        'units_per_bulk',
        'units_per_purchase',
        'reorder_point',
        'max_level',
        'bulk_cost_price',
        'unit_cost_price',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'units_per_bulk' => 'integer',
        'units_per_purchase' => 'integer',
        'reorder_point' => 'integer',
        'max_level' => 'integer',
        'lead_time_days' => 'integer',
        'bulk_cost_price' => 'decimal:2',
        'unit_cost_price' => 'decimal:2',
        'is_active' => 'boolean',
        'has_expiration' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (auth()->check()) {
                $item->created_by = auth()->id();
            }
            // Auto-generate SKU if not provided
            if (empty($item->sku)) {
                $item->sku = 'ITM-' . strtoupper(substr($item->name, 0, 3)) . '-' . time();
            }
        });

        static::updating(function ($item) {
            if (auth()->check()) {
                $item->updated_by = auth()->id();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function centralStock(): HasOne
    {
        return $this->hasOne(CentralStock::class);
    }

    public function branchStocks(): HasMany
    {
        return $this->hasMany(BranchStock::class);
    }

    public function purchaseItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            InventoryPurchaseItem::class,
            InventoryPurchase::class,
            'inventory_item_id',
            'inventory_purchase_id',
            'id',
            'id'
        );
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(InventoryPurchase::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Laundries that used this inventory item as an add-on
     */
    public function laundries(): BelongsToMany
    {
        return $this->belongsToMany(Laundry::class, 'laundry_inventory_items', 'inventory_item_id', 'laundries_id')
            ->withPivot('price_at_purchase', 'quantity')
            ->withTimestamps();
    }

    public function getOrCreateBranchStock(int $branchId): BranchStock
    {
        return $this->branchStocks()->firstOrCreate(
            ['branch_id' => $branchId],
            [
                'current_stock' => 0,
                'reorder_point' => $this->reorder_point ?? 0,
                'max_stock_level' => $this->max_level ?? 0,
            ]
        );
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(StockHistory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Calculate unit cost from bulk cost
    public function calculateUnitCost(): float
    {
        if ($this->units_per_bulk > 0) {
            return round($this->bulk_cost_price / $this->units_per_bulk, 2);
        }
        return $this->unit_cost_price;
    }

    public function getStatusAttribute(): string
    {
        $stock = $this->centralStock?->current_stock ?? 0;
        if ($stock === 0) return 'out';
        if ($stock <= $this->reorder_point) return 'low';
        return 'ok';
    }

    public function getStockProgressAttribute(): int
    {
        $stock = $this->centralStock?->current_stock ?? 0;
        return $this->max_level > 0
            ? min(100, (int)(($stock / $this->max_level) * 100))
            : 0;
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('centralStock', fn($q) =>
            $q->whereColumn('current_stock', '<=', 'inventory_items.reorder_point')
              ->where('current_stock', '>', 0)
        );
    }

    public function scopeOutOfStock($query)
    {
        return $query->whereHas('centralStock', fn($q) =>
            $q->where('current_stock', 0)
        );
    }
}
