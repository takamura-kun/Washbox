<?php

namespace App\Services;

use App\Models\{FinancialTransaction, FinancialAuditLog, Laundry, RetailSale, Expense, InventoryPurchase};
use Illuminate\Support\Facades\DB;

class FinancialTransactionService
{
    /**
     * Record a laundry sale transaction
     */
    public function recordLaundrySale(Laundry $laundry, bool $requiresApproval = false): FinancialTransaction
    {
        return DB::transaction(function () use ($laundry, $requiresApproval) {
            $transaction = FinancialTransaction::create([
                'type' => 'income',
                'category' => 'laundry_sale',
                'amount' => $laundry->total_amount,
                'branch_id' => $laundry->branch_id,
                'reference_type' => Laundry::class,
                'reference_id' => $laundry->id,
                'description' => "Laundry sale - {$laundry->tracking_number}",
                'metadata' => [
                    'customer_id' => $laundry->customer_id,
                    'service_id' => $laundry->service_id,
                    'payment_method' => $laundry->payment_method,
                ],
                'transaction_date' => $laundry->paid_at ?? now(),
                'status' => $requiresApproval ? 'pending' : 'completed',
                'created_by' => auth()->id(),
            ]);

            FinancialAuditLog::logAudit(
                'created',
                $transaction,
                null,
                $transaction->toArray(),
                "Laundry sale transaction recorded for {$laundry->tracking_number}"
            );

            return $transaction;
        });
    }

    /**
     * Record a retail sale transaction
     */
    public function recordRetailSale(RetailSale $sale): FinancialTransaction
    {
        return DB::transaction(function () use ($sale) {
            $transaction = FinancialTransaction::create([
                'type' => 'income',
                'category' => 'retail_sale',
                'amount' => $sale->total_amount,
                'branch_id' => $sale->branch_id,
                'reference_type' => RetailSale::class,
                'reference_id' => $sale->id,
                'description' => "Retail sale - {$sale->receipt_number}",
                'metadata' => [
                    'customer_id' => $sale->customer_id,
                    'payment_method' => $sale->payment_method,
                ],
                'transaction_date' => $sale->created_at,
                'status' => 'completed',
                'created_by' => auth()->id(),
            ]);

            FinancialAuditLog::logAudit(
                'created',
                $transaction,
                null,
                $transaction->toArray(),
                "Retail sale transaction recorded for {$sale->receipt_number}"
            );

            return $transaction;
        });
    }

    /**
     * Record an expense transaction
     */
    public function recordExpense(Expense $expense, bool $requiresApproval = false): FinancialTransaction
    {
        return DB::transaction(function () use ($expense, $requiresApproval) {
            // Determine if approval is needed based on amount threshold
            $approvalThreshold = config('finance.expense_approval_threshold', 10000);
            $needsApproval = $requiresApproval || ($expense->amount >= $approvalThreshold);

            $transaction = FinancialTransaction::create([
                'type' => 'expense',
                'category' => 'expense',
                'amount' => $expense->amount,
                'branch_id' => $expense->branch_id,
                'reference_type' => Expense::class,
                'reference_id' => $expense->id,
                'description' => $expense->description ?? "Expense - {$expense->category->name}",
                'metadata' => [
                    'category_id' => $expense->expense_category_id,
                    'payment_method' => $expense->payment_method,
                ],
                'transaction_date' => $expense->expense_date,
                'status' => $needsApproval ? 'pending' : 'completed',
                'created_by' => auth()->id(),
            ]);

            FinancialAuditLog::logAudit(
                'created',
                $transaction,
                null,
                $transaction->toArray(),
                "Expense transaction recorded" . ($needsApproval ? ' (pending approval)' : '')
            );

            // Update budget if applicable and transaction is completed
            if (!$needsApproval) {
                $this->updateBudgetForExpense($expense);
            }

            return $transaction;
        });
    }

    /**
     * Record an inventory purchase transaction
     */
    public function recordInventoryPurchase(InventoryPurchase $purchase): FinancialTransaction
    {
        return DB::transaction(function () use ($purchase) {
            // Create Expense record for inventory purchase
            $inventoryCategory = \App\Models\ExpenseCategory::firstOrCreate(
                ['slug' => 'inventory'],
                [
                    'name' => 'Inventory Purchases',
                    'is_system' => true,
                    'is_active' => true,
                ]
            );

            $supplierName = 'Unknown Supplier';
            if ($purchase->supplier_id) {
                $supplier = \App\Models\Supplier::find($purchase->supplier_id);
                $supplierName = $supplier ? $supplier->name : 'Unknown Supplier';
            }
            
            // Inventory purchases go to central/head office, not to specific branches
            // Use null for branch_id to indicate central expense
            $branchId = null;

            $expense = Expense::create([
                'branch_id' => $branchId,
                'expense_category_id' => $inventoryCategory->id,
                'title' => "Inventory Purchase - {$purchase->reference_no}",
                'description' => "Purchase from {$supplierName} (Central Stock)",
                'amount' => $purchase->total_cost,
                'expense_date' => $purchase->purchase_date,
                'reference_no' => $purchase->reference_no,
                'source' => 'auto',
                'inventory_purchase_id' => $purchase->id,
                'created_by' => auth()->id(),
            ]);

            $transaction = FinancialTransaction::create([
                'type' => 'expense',
                'category' => 'inventory_purchase',
                'amount' => $purchase->total_cost,
                'branch_id' => $branchId,
                'reference_type' => InventoryPurchase::class,
                'reference_id' => $purchase->id,
                'description' => "Inventory purchase - {$purchase->reference_no}",
                'metadata' => [
                    'supplier' => $supplierName,
                    'expense_id' => $expense->id,
                ],
                'transaction_date' => $purchase->purchase_date,
                'status' => 'completed',
                'created_by' => auth()->id(),
            ]);

            FinancialAuditLog::logAudit(
                'created',
                $transaction,
                null,
                $transaction->toArray(),
                "Inventory purchase transaction and expense recorded"
            );

            return $transaction;
        });
    }

    /**
     * Record pickup/delivery fee
     */
    public function recordPickupDeliveryFee(Laundry $laundry): ?FinancialTransaction
    {
        $totalFees = ($laundry->pickup_fee ?? 0) + ($laundry->delivery_fee ?? 0);

        if ($totalFees <= 0) {
            return null;
        }

        return DB::transaction(function () use ($laundry, $totalFees) {
            $transaction = FinancialTransaction::create([
                'type' => 'income',
                'category' => 'pickup_fee',
                'amount' => $totalFees,
                'branch_id' => $laundry->branch_id,
                'reference_type' => Laundry::class,
                'reference_id' => $laundry->id,
                'description' => "Pickup/Delivery fee - {$laundry->tracking_number}",
                'metadata' => [
                    'pickup_fee' => $laundry->pickup_fee,
                    'delivery_fee' => $laundry->delivery_fee,
                ],
                'transaction_date' => $laundry->paid_at ?? now(),
                'status' => 'completed',
                'created_by' => auth()->id(),
            ]);

            FinancialAuditLog::logAudit(
                'created',
                $transaction,
                null,
                $transaction->toArray(),
                "Pickup/Delivery fee recorded"
            );

            return $transaction;
        });
    }

    /**
     * Reverse a transaction (for refunds/cancellations)
     */
    public function reverseTransaction(FinancialTransaction $transaction, string $reason): FinancialTransaction
    {
        return DB::transaction(function () use ($transaction, $reason) {
            // Mark original as reversed
            $transaction->update(['status' => 'reversed']);

            // Create reversal transaction
            $reversal = FinancialTransaction::create([
                'type' => $transaction->type === 'income' ? 'expense' : 'income',
                'category' => $transaction->category,
                'amount' => $transaction->amount,
                'branch_id' => $transaction->branch_id,
                'reference_type' => $transaction->reference_type,
                'reference_id' => $transaction->reference_id,
                'description' => "REVERSAL: {$transaction->description} - Reason: {$reason}",
                'metadata' => array_merge($transaction->metadata ?? [], ['reversed_transaction_id' => $transaction->id]),
                'transaction_date' => now(),
                'status' => 'completed',
                'created_by' => auth()->id(),
            ]);

            FinancialAuditLog::logAudit(
                'reversed',
                $transaction,
                $transaction->toArray(),
                $reversal->toArray(),
                "Transaction reversed: {$reason}"
            );

            return $reversal;
        });
    }

    /**
     * Update budget when expense is recorded
     */
    private function updateBudgetForExpense(Expense $expense): void
    {
        $budgets = \App\Models\Budget::active()
            ->where('branch_id', $expense->branch_id)
            ->where('expense_category_id', $expense->expense_category_id)
            ->where('start_date', '<=', $expense->expense_date)
            ->where('end_date', '>=', $expense->expense_date)
            ->get();

        foreach ($budgets as $budget) {
            $budget->updateSpentAmount();
        }
    }

    /**
     * Get financial summary for a period
     */
    public function getSummary($startDate, $endDate, $branchId = null): array
    {
        $income = FinancialTransaction::income()
            ->completed()
            ->dateRange($startDate, $endDate)
            ->when($branchId, fn($q) => $q->byBranch($branchId))
            ->sum('amount');

        $expenses = FinancialTransaction::expense()
            ->completed()
            ->dateRange($startDate, $endDate)
            ->when($branchId, fn($q) => $q->byBranch($branchId))
            ->sum('amount');

        $netProfit = $income - $expenses;
        $profitMargin = $income > 0 ? ($netProfit / $income) * 100 : 0;

        return [
            'total_income' => $income,
            'total_expenses' => $expenses,
            'net_profit' => $netProfit,
            'profit_margin' => round($profitMargin, 2),
        ];
    }
}
