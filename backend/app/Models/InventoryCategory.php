<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCategory extends Model
{
    protected $fillable = [
        'name',
        'color',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getPillHtmlAttribute(): string
    {
        return \App\Helpers\ColorHelper::pill($this->color, $this->name);
    }

    public function getSwatchColorAttribute(): string
    {
        return \App\Helpers\ColorHelper::swatch($this->color);
    }

    public function getTotalCentralStockAttribute(): int
    {
        return $this->items->sum(fn($i) => $i->centralStock?->current_stock ?? 0);
    }

    public function getColorBgAttribute(): string
    {
        return $this->color . '22'; // Add transparency
    }

    public function getColorTextAttribute(): string
    {
        return $this->color;
    }

    public function getColorBorderAttribute(): string
    {
        return $this->color . '44'; // Add transparency
    }
}
