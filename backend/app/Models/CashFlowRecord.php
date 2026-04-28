<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashFlowRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'record_date',
        'opening_balance',
        'cash_inflow',
        'cash_outflow',
        'closing_balance',
        'inflow_breakdown',
        'outflow_breakdown',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'record_date' => 'date',
        'opening_balance' => 'decimal:2',
        'cash_inflow' => 'decimal:2',
        'cash_outflow' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'inflow_breakdown' => 'array',
        'outflow_breakdown' => 'array',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('record_date', [$start, $end]);
    }

    // Helpers
    public function getNetCashFlowAttribute(): float
    {
        return $this->cash_inflow - $this->cash_outflow;
    }

    // Static method to generate daily cash flow
    public static function generateForDate($date, $branchId = null)
    {
        $date = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
        
        // Get previous day's closing balance
        $previousRecord = self::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('record_date', '<', $date)
            ->orderBy('record_date', 'desc')
            ->first();

        $openingBalance = $previousRecord ? $previousRecord->closing_balance : 0;

        // Calculate inflows
        $laundryInflow = Laundry::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['paid', 'completed'])
            ->whereDate('paid_at', $date)
            ->sum('total_amount');

        $retailInflow = RetailSale::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereDate('created_at', $date)
            ->sum('total_amount');

        $totalInflow = $laundryInflow + $retailInflow;

        // Calculate outflows
        $expenseOutflow = Expense::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereDate('expense_date', $date)
            ->sum('amount');

        $inventoryOutflow = InventoryPurchase::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereDate('purchase_date', $date)
            ->sum('total_cost');

        $totalOutflow = $expenseOutflow + $inventoryOutflow;

        $closingBalance = $openingBalance + $totalInflow - $totalOutflow;

        return self::updateOrCreate(
            [
                'branch_id' => $branchId,
                'record_date' => $date,
            ],
            [
                'opening_balance' => $openingBalance,
                'cash_inflow' => $totalInflow,
                'cash_outflow' => $totalOutflow,
                'closing_balance' => $closingBalance,
                'inflow_breakdown' => [
                    'laundry' => $laundryInflow,
                    'retail' => $retailInflow,
                ],
                'outflow_breakdown' => [
                    'expenses' => $expenseOutflow,
                    'inventory' => $inventoryOutflow,
                ],
                'created_by' => auth()->id(),
            ]
        );
    }
}
