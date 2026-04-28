<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustment extends Model
{
    protected $fillable = [
        'branch_id',
        'inventory_item_id',
        'type',
        'quantity',
        'value_loss',
        'reason',
        'notes',
        'photo_proof',
        'adjusted_by',
        'approved_by',
        'approved_at',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'value_loss' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ========================================================================
    // SCOPES
    // ========================================================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // ========================================================================
    // METHODS
    // ========================================================================

    public function approve($approverId)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        // Apply stock adjustment
        $branchStock = BranchStock::where('branch_id', $this->branch_id)
            ->where('inventory_item_id', $this->inventory_item_id)
            ->first();

        if ($branchStock) {
            $branchStock->current_stock += $this->quantity;
            $branchStock->last_updated_at = now();
            $branchStock->save();
        }
    }

    public function reject($approverId, $reason)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    // ========================================================================
    // ACCESSORS
    // ========================================================================

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'approved' => '<span class="badge bg-success">Approved</span>',
            'rejected' => '<span class="badge bg-danger">Rejected</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'damaged' => 'Damaged',
            'expired' => 'Expired',
            'lost' => 'Lost',
            'found' => 'Found',
            'correction' => 'Correction',
            'theft' => 'Theft',
            'spoilage' => 'Spoilage',
            default => ucfirst($this->type),
        };
    }
}
