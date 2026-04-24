<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Budget;
use App\Models\CashFlowRecord;
use Carbon\Carbon;

class FinanceSetupSeeder extends Seeder
{
    public function run()
    {
        $this->createBudgets();
        $this->generateCashFlowRecords();
    }

    private function createBudgets()
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $budgets = [
            // Branch 1 (Siaton) - Monthly Budgets
            [
                'name' => 'Siaton - Monthly Utilities',
                'branch_id' => 1,
                'expense_category_id' => 1, // Utilities
                'period_type' => 'monthly',
                'start_date' => $startOfMonth,
                'end_date' => $endOfMonth,
                'allocated_amount' => 15000,
                'alert_threshold' => 80,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Siaton - Monthly Rent',
                'branch_id' => 1,
                'expense_category_id' => 2, // Rent
                'period_type' => 'monthly',
                'start_date' => $startOfMonth,
                'end_date' => $endOfMonth,
                'allocated_amount' => 20000,
                'alert_threshold' => 90,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Siaton - Monthly Supplies',
                'branch_id' => 1,
                'expense_category_id' => 4, // Supplies
                'period_type' => 'monthly',
                'start_date' => $startOfMonth,
                'end_date' => $endOfMonth,
                'allocated_amount' => 25000,
                'alert_threshold' => 85,
                'is_active' => true,
                'created_by' => 1,
            ],

            // Branch 2 (Bais) - Monthly Budgets
            [
                'name' => 'Bais - Monthly Utilities',
                'branch_id' => 2,
                'expense_category_id' => 1, // Utilities
                'period_type' => 'monthly',
                'start_date' => $startOfMonth,
                'end_date' => $endOfMonth,
                'allocated_amount' => 18000,
                'alert_threshold' => 80,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Bais - Monthly Rent',
                'branch_id' => 2,
                'expense_category_id' => 2, // Rent
                'period_type' => 'monthly',
                'start_date' => $startOfMonth,
                'end_date' => $endOfMonth,
                'allocated_amount' => 25000,
                'alert_threshold' => 90,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Bais - Monthly Supplies',
                'branch_id' => 2,
                'expense_category_id' => 4, // Supplies
                'period_type' => 'monthly',
                'start_date' => $startOfMonth,
                'end_date' => $endOfMonth,
                'allocated_amount' => 30000,
                'alert_threshold' => 85,
                'is_active' => true,
                'created_by' => 1,
            ],

            // Company-Wide Budgets
            [
                'name' => 'Company-Wide Marketing Budget',
                'branch_id' => null, // All branches
                'expense_category_id' => 5, // Marketing
                'period_type' => 'monthly',
                'start_date' => $startOfMonth,
                'end_date' => $endOfMonth,
                'allocated_amount' => 50000,
                'alert_threshold' => 75,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Company-Wide Maintenance',
                'branch_id' => null, // All branches
                'expense_category_id' => 3, // Maintenance
                'period_type' => 'monthly',
                'start_date' => $startOfMonth,
                'end_date' => $endOfMonth,
                'allocated_amount' => 40000,
                'alert_threshold' => 80,
                'is_active' => true,
                'created_by' => 1,
            ],

            // Quarterly Budget
            [
                'name' => 'Q2 2024 - Training & Development',
                'branch_id' => null,
                'expense_category_id' => 9, // Training
                'period_type' => 'quarterly',
                'start_date' => $now->copy()->startOfQuarter(),
                'end_date' => $now->copy()->endOfQuarter(),
                'allocated_amount' => 100000,
                'alert_threshold' => 85,
                'is_active' => true,
                'created_by' => 1,
            ],
        ];

        foreach ($budgets as $budgetData) {
            $budget = Budget::create($budgetData);
            $budget->updateSpentAmount(); // Calculate initial spent amount
            echo "✓ Created budget: {$budget->name}\n";
        }

        echo "\n✅ Created " . count($budgets) . " budgets successfully!\n\n";
    }

    private function generateCashFlowRecords()
    {
        echo "Generating Cash Flow Records...\n";
        echo "================================\n";

        // Generate for last 30 days
        $startDate = now()->subDays(30);
        $endDate = now();
        $currentDate = $startDate->copy();

        $count = 0;

        while ($currentDate->lte($endDate)) {
            // Generate for Branch 1
            $record1 = CashFlowRecord::generateForDate($currentDate, 1);
            echo "✓ {$currentDate->format('Y-m-d')} - Branch 1: In=₱" . number_format($record1->cash_inflow, 2) . 
                 " Out=₱" . number_format($record1->cash_outflow, 2) . 
                 " Balance=₱" . number_format($record1->closing_balance, 2) . "\n";

            // Generate for Branch 2
            $record2 = CashFlowRecord::generateForDate($currentDate, 2);
            echo "✓ {$currentDate->format('Y-m-d')} - Branch 2: In=₱" . number_format($record2->cash_inflow, 2) . 
                 " Out=₱" . number_format($record2->cash_outflow, 2) . 
                 " Balance=₱" . number_format($record2->closing_balance, 2) . "\n";

            $currentDate->addDay();
            $count += 2;
        }

        echo "\n✅ Generated {$count} cash flow records successfully!\n";
    }
}
