<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'pickup_request_id',
        'user_id',
        'user_type',
        'latitude',
        'longitude',
        'accuracy',
        'speed',
        'heading',
        'timestamp'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'speed' => 'decimal:2',
        'heading' => 'decimal:2',
        'timestamp' => 'datetime'
    ];

    // Relationships
    public function pickupRequest()
    {
        return $this->belongsTo(PickupRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForPickup($query, $pickupId)
    {
        return $query->where('pickup_request_id', $pickupId);
    }

    public function scopeStaffOnly($query)
    {
        return $query->where('user_type', 'staff');
    }

    public function scopeCustomerOnly($query)
    {
        return $query->where('user_type', 'customer');
    }

    public function scopeRecent($query, $minutes = 30)
    {
        return $query->where('timestamp', '>=', now()->subMinutes($minutes));
    }
}