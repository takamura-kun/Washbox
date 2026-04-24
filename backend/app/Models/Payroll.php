<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffSalaryInfo extends Model
{
    protected $table = 'staff_salary_info';

    protected $fillable = [
        'user_id',
        'branch_id',
        'salary_type',
        'base_rate',
        'pay_period',
        'effectivity_date',
        'is_active',
    ];

    protected $casts = [
        'base_rate' => 'decimal:2',
        'effectivity_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}

class PayrollPeriod extends Model
{
    protected $fillable = [
        'branch_id',
        'period_label',
        'date_from',
        'date_to',
        'pay_date',
        'status',
        'total_amount',
        'processed_by',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'pay_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }
}

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
        'overtime_pay',
        'gross_pay',
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
        'overtime_pay' => 'decimal:2',
        'gross_pay' => 'decimal:2',
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
}
