<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\AdminNotification;
use App\Models\BranchNotification;

class ExpenseObserver
{
    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void
    {
        $expense->loadMissing('category', 'branch', 'creator');

        // 🔔 NOTIFY ADMIN: New expense recorded
        AdminNotification::create([
            'type' => 'expense_created',
            'title' => 'New Expense Recorded',
            'message' => "{$expense->category->name} expense - ₱" . number_format($expense->amount, 2) . " at " . ($expense->branch->name ?? 'Central'),
            'icon' => 'cash-stack',
            'color' => 'warning',
            'link' => route('admin.finance.expenses.index'),
            'data' => [
                'expense_id' => $expense->id,
                'category' => $expense->category->name,
                'amount' => $expense->amount,
                'description' => $expense->description,
            ],
            'branch_id' => $expense->branch_id,
        ]);

        // 🔔 NOTIFY BRANCH: New expense recorded
        if ($expense->branch_id) {
            BranchNotification::create([
                'branch_id' => $expense->branch_id,
                'type' => 'expense_created',
                'title' => 'Expense Recorded',
                'message' => "{$expense->category->name} - ₱" . number_format($expense->amount, 2),
                'icon' => 'cash-stack',
                'color' => 'warning',
                'link' => route('branch.finance.expenses'),
                'data' => [
                    'expense_id' => $expense->id,
                    'category' => $expense->category->name,
                    'amount' => $expense->amount,
                ],
            ]);
        }

        // 🔔 NOTIFY ADMIN: High-value expense alert (over ₱10,000)
        if ($expense->amount >= 10000) {
            AdminNotification::create([
                'type' => 'high_expense_alert',
                'title' => '⚠️ High-Value Expense Alert',
                'message' => "Large expense of ₱" . number_format($expense->amount, 2) . " recorded for {$expense->category->name}",
                'icon' => 'exclamation-triangle',
                'color' => 'danger',
                'link' => route('admin.finance.expenses.index'),
                'data' => [
                    'expense_id' => $expense->id,
                    'amount' => $expense->amount,
                ],
                'branch_id' => $expense->branch_id,
            ]);
        }
    }
}
