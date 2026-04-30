<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_per_piece',
        'price_per_load',
        'pricing_type',
        'service_type_id',
        'service_type',
        'min_weight',
        'max_weight',
        'max_weight_per_load',
        'excess_weight_charge_per_kg',
        'allow_excess_weight',
        'turnaround_time',
        'icon_path',
        'image',
        'is_active',
        'category',
        'display_laundry',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(function (Service $service) {
            // Only auto-set category if it's NOT already set AND service_type exists
            // This prevents overriding the manually selected category
            if (empty($service->category) && !empty($service->service_type)) {
                $service->category = match (true) {
                    in_array($service->service_type, ['regular_clothes', 'special_item', 'full_service']) => 'drop_off',
                    str_contains($service->service_type, 'self_service') => 'self_service',
                    str_contains($service->service_type, 'addon') => 'addon',
                    default => 'drop_off',
                };
            }

            // Generate slug if not set
            if (empty($service->slug) && !empty($service->name)) {
                $service->slug = Str::slug($service->name);
            }
        });
    }

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price_per_piece' => 'decimal:2',
        'price_per_load' => 'decimal:2',
        'min_weight' => 'decimal:2',
        'max_weight' => 'decimal:2',
        'max_weight_per_load' => 'decimal:2',
        'excess_weight_charge_per_kg' => 'decimal:2',
        'allow_excess_weight' => 'boolean',
        'turnaround_time' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all laundries using this service.
     */
    public function laundries()
    {
        return $this->hasMany(Laundry::class);
    }

    /**
     * Get branches that offer this service.
     */
    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_services')
            ->withPivot('is_available')
            ->withTimestamps();
    }

    /**
     * Get service items (inventory items used in this service).
     */
    public function serviceItems()
    {
        return $this->hasMany(ServiceItem::class);
    }

    /**
     * Get inventory items through service items.
     */
    public function inventoryItems()
    {
        return $this->belongsToMany(InventoryItem::class, 'service_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Get the service type relationship
     */
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    /**
     * Get supplies for this service (many-to-many relationship).
     */
    public function supplies()
    {
        return $this->belongsToMany(InventoryItem::class, 'service_supplies', 'service_id', 'inventory_item_id')
            ->withPivot('quantity_required')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive services.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope a query by service type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('service_type', $type);
    }

    /**
     * Scope a query by pricing type.
     */
    public function scopeByPricingType($query, $type)
    {
        return $query->where('pricing_type', $type);
    }

    /**
     * Get the image URL attribute.
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        // If it's already a full URL, return it
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        // Otherwise, return storage URL
        return asset('storage/services/' . $this->image);
    }

    /**
     * Get the icon URL attribute.
     */
    public function getIconUrlAttribute()
    {
        if (!$this->icon_path) {
            return null;
        }

        // If it's already a full URL, return it
        if (filter_var($this->icon_path, FILTER_VALIDATE_URL)) {
            return $this->icon_path;
        }

        // Otherwise, return storage URL
        return asset('storage/' . $this->icon_path);
    }

    /**
     * Get the formatted price attribute based on pricing type with system fallback.
     */
    public function getFormattedPriceAttribute()
    {
        if ($this->pricing_type === 'per_piece') {
            $price = $this->price_per_piece ?? SystemSetting::get('default_price_per_piece', 60);
            return '₱' . number_format($price, 2) . '/piece';
        }
        $price = $this->price_per_load ?? SystemSetting::get('default_price_per_load', 120);
        return '₱' . number_format($price, 2) . '/load';
    }

    /**
     * Get the actual price value based on pricing type with system fallback.
     */
    public function getPriceAttribute()
    {
        if ($this->pricing_type === 'per_piece') {
            return $this->price_per_piece ?? SystemSetting::get('default_price_per_piece', 60);
        }
        return $this->price_per_load ?? SystemSetting::get('default_price_per_load', 120);
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get the status color.
     */
    public function getStatusColorAttribute()
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    /**
     * Get formatted service type.
     */
    public function getServiceTypeDisplayAttribute()
    {
        if (!$this->service_type) {
            return 'General Service';
        }
        return ucfirst(str_replace('_', ' ', $this->service_type));
    }

    /**
     * Human-readable service type label.
     */
    public function getServiceTypeLabelAttribute()
    {
        return match ($this->service_type) {
            'full_service' => 'Full Service',
            'self_service' => 'Self Service',
            'special_item' => 'Special Item',
            'addon' => 'Add-on',
            default => 'General Service',
        };
    }

    /**
     * Human-readable pricing type label.
     */
    public function getPricingTypeLabelAttribute()
    {
        return match ($this->pricing_type) {
            'per_piece' => 'Per Piece',
            default => 'Per Load',
        };
    }

    /**
     * Get formatted pricing type.
     */
    public function getPricingTypeDisplayAttribute()
    {
        if (!$this->pricing_type) {
            return 'Not Set';
        }

        return ucfirst(str_replace('_', ' ', $this->pricing_type));
    }

    /**
     * Calculate total revenue from this service.
     */
    public function getTotalRevenueAttribute()
    {
        return $this->laundries()->where('status', 'completed')->sum('total_amount');
    }

    /**
     * Get count of completed laundries.
     */
    public function getCompletedLaundriesCountAttribute()
    {
        return $this->laundries()->where('status', 'completed')->count();
    }

    /**
     * Get count of pending laundries.
     */
    public function getPendingLaundriesCountAttribute()
    {
        return $this->laundries()->whereIn('status', ['pending', 'processing', 'ready'])->count();
    }

    /**
     * Get average laundry value.
     */
    public function getAvgLaundryValueAttribute()
    {
        return $this->laundries()->where('status', 'completed')->avg('total_amount') ?? 0;
    }

    /**
     * Get total weight processed.
     */
    public function getTotalWeightAttribute()
    {
        return $this->laundries()->sum('weight') ?? 0;
    }

    /**
     * Check if service has any laundries.
     */
    public function hasLaundries()
    {
        return $this->laundries()->exists();
    }

    /**
     * Calculate price based on quantity and pricing type.
     * Falls back to system settings if service price is not set.
     *
     * @param float|null $quantity
     * @return float
     */
    public function calculatePrice($quantity = null)
    {
        if ($this->pricing_type === 'per_piece') {
            $pricePerPiece = $this->price_per_piece ?? SystemSetting::get('default_price_per_piece', 60);
            return (float) $pricePerPiece * ($quantity ?? 1);
        }
        
        $pricePerLoad = $this->price_per_load ?? SystemSetting::get('default_price_per_load', 120);
        return (float) $pricePerLoad;
    }

    /**
     * Update all services with null prices to use system defaults
     */
    public static function updateNullPricesFromSettings(): array
    {
        $defaultPricePerPiece = (float) SystemSetting::get('default_price_per_piece', 60);
        $defaultPricePerLoad = (float) SystemSetting::get('default_price_per_load', 120);
        
        $updatedPerPiece = self::whereNull('price_per_piece')
            ->where('pricing_type', 'per_piece')
            ->update(['price_per_piece' => $defaultPricePerPiece]);
            
        $updatedPerLoad = self::whereNull('price_per_load')
            ->where('pricing_type', 'per_load')
            ->update(['price_per_load' => $defaultPricePerLoad]);
            
        return [
            'per_piece_updated' => $updatedPerPiece,
            'per_load_updated' => $updatedPerLoad,
            'total_updated' => $updatedPerPiece + $updatedPerLoad
        ];
    }

    /**
     * Check if this service is priced per piece.
     */
    public function isPerPiece(): bool
    {
        return $this->pricing_type === 'per_piece';
    }

    /**
     * Check if this service is priced per load.
     */
    public function isPerLoad(): bool
    {
        return $this->pricing_type === 'per_load';
    }

    /**
     * Get popular services (most laundries).
     */
    public static function popular($limit = 5)
    {
        return static::withCount('laundries')
            ->orderBy('laundries_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top revenue services.
     */
    public static function topRevenue($limit = 5)
    {
        return static::select('services.*')
            ->selectRaw('(SELECT SUM(total_amount) FROM laundries WHERE laundries.service_id = services.id AND laundries.status = "completed") as revenue')
            ->orderBy('revenue', 'desc')
            ->limit($limit)
            ->get();
    }
}
