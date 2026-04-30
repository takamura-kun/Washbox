<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_type',
        'period',
        'period_start',
        'period_end',
        'branch_id',
        'gross_sales',
        'vat_exempt_sales',
        'vat_zero_rated_sales',
        'vatable_sales',
        'output_vat',
        'input_vat',
        'net_vat_payable',
        'withholding_tax',
        'status',
        'filed_date',
        'payment_date',
        'reference_number',
        'notes',
        'prepared_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'gross_sales' => 'decimal:2',
        'vat_exempt_sales' => 'decimal:2',
        'vat_zero_rated_sales' => 'decimal:2',
        'vatable_sales' => 'decimal:2',
        'output_vat' => 'decimal:2',
        'input_vat' => 'decimal:2',
        'net_vat_payable' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'filed_date' => 'date',
        'payment_date' => 'date',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function preparer()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    // Scopes
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('tax_type', $type);
    }

    public function scopeFiled($query)
    {
        return $query->whereIn('status', ['filed', 'paid']);
    }

    // Helpers
    public function calculateVAT(): void
    {
        // VAT rate is 12% in Philippines
        $vatRate = 0.12;
        
        $this->vatable_sales = $this->gross_sales - $this->vat_exempt_sales - $this->vat_zero_rated_sales;
        $this->output_vat = $this->vatable_sales * $vatRate;
        $this->net_vat_payable = $this->output_vat - $this->input_vat;
    }

    public function markAsFiled(string $referenceNumber): void
    {
        $this->update([
            'status' => 'filed',
            'filed_date' => now(),
            'reference_number' => $referenceNumber,
        ]);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'payment_date' => now(),
        ]);
    }
}
