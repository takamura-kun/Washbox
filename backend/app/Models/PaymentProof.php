<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'laundry_id',
        'customer_id',
        'payment_method',
        'transaction_id',
        'amount',
        'reference_number',
        'proof_image',
        'screenshot_path',
        'notes',
        'status',
        'admin_notes',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'verified_at',
        'verified_by'
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
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
