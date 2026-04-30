<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentEvent extends Model
{
    protected $fillable = [
        'laundry_id',
        'customer_id',
        'event_type',
        'amount',
        'status',
        'data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'data' => 'array',
    ];

    /**
     * Get the laundry associated with this payment event
     */
    public function laundry(): BelongsTo
    {
        return $this->belongsTo(Laundry::class);
    }

    /**
     * Get the customer associated with this payment event
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope to get events for a specific laundry
     */
    public function scopeForLaundry($query, $laundryId)
    {
        return $query->where('laundry_id', $laundryId);
    }

    /**
     * Scope to get events by type
     */
    public function scopeByType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope to get events with a specific status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
