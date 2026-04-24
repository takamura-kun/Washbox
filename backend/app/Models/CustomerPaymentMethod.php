<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'type',
        'name',
        'details',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'details' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getTypeDisplayAttribute(): string
    {
        return match($this->type) {
            'cash' => 'Cash',
            'gcash' => 'GCash',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            default => ucfirst($this->type),
        };
    }

    public function getIconAttribute(): string
    {
        return match($this->type) {
            'cash' => 'cash-outline',
            'gcash' => 'phone-portrait-outline',
            'bank_transfer' => 'card-outline',
            'credit_card' => 'card-outline',
            default => 'wallet-outline',
        };
    }
}