<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_period_id',
        'user_id',
        'branch_id',
        'days_worked',
        'hours_worked',
        'overtime_hours',
        'base_rate',
        'gross_pay',
        'overtime_pay',
        'deductions',
        'bonuses',
        'net_pay',
        'status',
        'notes',
    ];

    protected $casts = [
        'hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'base_rate' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'deductions' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
