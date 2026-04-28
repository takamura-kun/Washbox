<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'leave_date_from',
        'leave_date_to',
        'total_days',
        'leave_type',
        'reason',
        'attachment',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'leave_date_from' => 'date',
        'leave_date_to' => 'date',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Methods
    public function approve(int $approvedBy): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        // Create attendance records for leave dates
        $this->createLeaveAttendance();
    }

    public function reject(int $rejectedBy, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $rejectedBy,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    private function createLeaveAttendance(): void
    {
        $currentDate = $this->leave_date_from->copy();

        while ($currentDate->lte($this->leave_date_to)) {
            Attendance::updateOrCreate(
                [
                    'user_id' => $this->user_id,
                    'attendance_date' => $currentDate,
                ],
                [
                    'branch_id' => $this->branch_id,
                    'status' => 'on_leave',
                    'hours_worked' => 0,
                    'notes' => "On {$this->leave_type} leave",
                ]
            );

            $currentDate->addDay();
        }
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending' => 'warning',
            default => 'secondary',
        };
    }
}
