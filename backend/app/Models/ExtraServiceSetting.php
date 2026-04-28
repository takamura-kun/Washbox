<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtraServiceSetting extends Model
{
    protected $fillable = [
        'service_key',
        'service_name',
        'description',
        'price',
        'icon',
        'color',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
