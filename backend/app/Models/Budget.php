<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'branch_id',
        'expense_category_id',
        'period_type',
        'start_date',
        'end_date',
        'allocated_amount',
        'spent_amount',
        'remaining_amount',
        'alert_threshold',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'allocated_amount' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('expense_category_id', $categoryId);
    }

    public function scopeCurrent($query)
    {
        $now = now();
        return $query->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }

    // Helpers
    public function getUtilizationPercentageAttribute(): float
    {
        if ($this->allocated_amount <= 0) {
            return 0;
        }
        return round(($this->spent_amount / $this->allocated_amount) * 100, 2);
    }

    public function isOverBudget(): bool
    {
        return $this->spent_amount > $this->allocated_amount;
    }

    public function isNearingLimit(): bool
    {
        return $this->utilization_percentage >= $this->alert_threshold;
    }

    public function updateSpentAmount(): void
    {
        $query = Expense::query();
        
        // Filter by branch if specified
        if ($this->branch_id) {
            $query->where('branch_id', $this->branch_id);
        }
        
        // Filter by category if specified
        if ($this->expense_category_id) {
            $query->where('expense_category_id', $this->expense_category_id);
        }
        
        // Filter by date range
        $spent = $query->whereBetween('expense_date', [$this->start_date, $this->end_date])
            ->sum('amount');

        $this->update([
            'spent_amount' => $spent,
            'remaining_amount' => $this->allocated_amount - $spent,
        ]);
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($budget) {
            $budget->remaining_amount = $budget->allocated_amount;
        });
    }
}
