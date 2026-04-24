<?php

namespace App\Observers;

use App\Models\Budget;
use App\Models\AdminNotification;
use App\Models\BranchNotification;

class BudgetObserver
{
    /**
     * Handle the Budget "updated" event.
     */
    public function updated(Budget $budget): void
    {
        // Check if spent amount changed
        if ($budget->isDirty('spent_amount')) {
            $budget->loadMissing('category', 'branch');

            $percentage = $budget->allocated_amount > 0 
                ? ($budget->spent_amount / $budget->allocated_amount) * 100 
                : 0;

            // 🔔 BUDGET WARNING: 80% spent
            if ($percentage >= 80 && $percentage < 100) {
                $oldPercentage = $budget->getOriginal('spent_amount') > 0
                    ? ($budget->getOriginal('spent_amount') / $budget->allocated_amount) * 100
                    : 0;

                // Only notify when crossing the 80% threshold
                if ($oldPercentage < 80) {
                    AdminNotification::create([
                        'type' => 'budget_warning',
                        'title' => '⚠️ Budget Warning',
                        'message' => "{$budget->category->name} budget at " . ($budget->branch?->name ?? 'Central') . " is " . round($percentage, 1) . "% spent",
                        'icon' => 'exclamation-triangle',
                        'color' => 'warning',
                        'link' => route('admin.finance.budgets.index'),
                        'data' => [
                            'budget_id' => $budget->id,
                            'category' => $budget->category->name,
                            'percentage' => round($percentage, 2),
                            'spent' => $budget->spent_amount,
                            'allocated' => $budget->allocated_amount,
                        ],
                        'branch_id' => $budget->branch_id,
                    ]);

                    if ($budget->branch_id) {
                        BranchNotification::create([
                            'branch_id' => $budget->branch_id,
                            'type' => 'budget_warning',
                            'title' => '⚠️ Budget Warning',
                            'message' => "{$budget->category->name} budget is " . round($percentage, 1) . "% spent",
                            'icon' => 'exclamation-triangle',
                            'color' => 'warning',
                            'link' => route('branch.finance.index'),
                        ]);
                    }
                }
            }

            // 🔔 BUDGET EXCEEDED: Over 100%
            if ($percentage >= 100) {
                $oldPercentage = $budget->getOriginal('spent_amount') > 0
                    ? ($budget->getOriginal('spent_amount') / $budget->allocated_amount) * 100
                    : 0;

                // Only notify when crossing the 100% threshold
                if ($oldPercentage < 100) {
                    AdminNotification::create([
                        'type' => 'budget_exceeded',
                        'title' => '🚨 Budget Exceeded',
                        'message' => "{$budget->category->name} budget at " . ($budget->branch?->name ?? 'Central') . " has been exceeded!",
                        'icon' => 'exclamation-octagon',
                        'color' => 'danger',
                        'link' => route('admin.finance.budgets.index'),
                        'data' => [
                            'budget_id' => $budget->id,
                            'category' => $budget->category->name,
                            'percentage' => round($percentage, 2),
                            'spent' => $budget->spent_amount,
                            'allocated' => $budget->allocated_amount,
                            'overspent' => $budget->spent_amount - $budget->allocated_amount,
                        ],
                        'branch_id' => $budget->branch_id,
                    ]);

                    if ($budget->branch_id) {
                        BranchNotification::create([
                            'branch_id' => $budget->branch_id,
                            'type' => 'budget_exceeded',
                            'title' => '🚨 Budget Exceeded',
                            'message' => "{$budget->category->name} budget has been exceeded by ₱" . number_format($budget->spent_amount - $budget->allocated_amount, 2),
                            'icon' => 'exclamation-octagon',
                            'color' => 'danger',
                            'link' => route('branch.finance.index'),
                        ]);
                    }
                }
            }
        }
    }
}
