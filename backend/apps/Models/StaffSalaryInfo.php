<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
