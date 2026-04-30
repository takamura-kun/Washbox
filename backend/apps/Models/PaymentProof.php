<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'laundry_id',
        'payment_method',
        'amount',
        'reference_number',
        'proof_image',
        'status',
        'admin_notes',
        'verified_at',
        'verified_by'
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    public function laundry()
    {
        return $this->belongsTo(Laundry::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getProofImageUrlAttribute()
    {
        return asset('storage/payment-proofs/' . $this->proof_image);
    }
}