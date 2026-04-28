<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'branch_id',
        'expense_category_id',
        'title',
        'description',
        'amount',
        'expense_date',
        'reference_no',
        'attachment',
        'notes',
        'is_recurring',
        'source',
        'salary_type',
        'inventory_purchase_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function inventoryPurchase(): BelongsTo
    {
        return $this->belongsTo(InventoryPurchase::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ========================================================================
    // SCOPES
    // ========================================================================

    public function scopeByBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('expense_category_id', $categoryId);
    }

    public function scopeManual($query)
    {
        return $query->where('source', 'manual');
    }

    public function scopeAuto($query)
    {
        return $query->where('source', 'auto');
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    // ========================================================================
    // ACCESSORS
    // ========================================================================

    public function getFormattedAmountAttribute(): string
    {
        return '₱' . number_format($this->amount, 2);
    }

    public function getSourceBadgeAttribute(): string
    {
        return $this->source === 'auto' ? '🤖 Auto' : '✋ Manual';
    }
}
