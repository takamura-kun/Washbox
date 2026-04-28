<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'label',
        'full_address',
        'street',
        'barangay',
        'city',
        'province',
        'postal_code',
        'latitude',
        'longitude',
        'contact_person',
        'contact_phone',
        'delivery_notes',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street,
            $this->barangay,
            $this->city,
            $this->province,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }

    public function getIconAttribute(): string
    {
        return match(strtolower($this->label)) {
            'home' => 'home-outline',
            'office', 'work' => 'business-outline',
            'school' => 'school-outline',
            default => 'location-outline',
        };
    }
}