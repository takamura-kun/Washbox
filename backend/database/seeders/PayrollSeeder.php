<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        // Get all staff members
        $staff = User::whereNotNull('employee_id')->get();
        
        if ($staff->isEmpty()) {
            $this->command->info('No staff found. Please create staff users first.');
            return;
        }

        // 1. Create Staff Salary Info
        $this->command->info('Creating staff salary information...');
        foreach ($staff as $employee) {
            DB::table('staff_salary_info')->insert([
                'user_id' => $employee->id,
                'branch_id' => $employee->branch_id,
                'salary_type' => 'monthly',
                'base_rate' => rand(15000, 35000), // PHP 15,000 - 35,000
                'pay_period' => 'monthly',
                'effectivity_date' => now()->subMonths(3)->startOfMonth(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Create Payroll Periods (last 3 months)
        $this->command->info('Creating payroll periods...');
        $branches = Branch::all();
        $payrollPeriods = [];

        for ($i = 2; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $dateFrom = $month->copy()->startOfMonth();
            $dateTo = $month->copy()->endOfMonth();
            $payDate = $dateTo->copy()->addDays(5);

            foreach ($branches as $branch) {
                $periodId = DB::table('payroll_periods')->insertGetId([
                    'branch_id' => $branch->id,
                    'period_label' => $month->format('F Y') . ' - ' . $branch->name,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'pay_date' => $payDate,
                    'status' => $i == 0 ? 'draft' : 'paid', // Current month is draft, past months are paid
                    'total_amount' => 0, // Will be calculated
                    'processed_by' => 1, // Admin user
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $payrollPeriods[] = [
                    'id' => $periodId,
                    'branch_id' => $branch->id,
                    'month' => $i
                ];
            }
        }

        // 3. Create Payroll Items for each period
        $this->command->info('Creating payroll items...');
        foreach ($payrollPeriods as $period) {
            $branchStaff = $staff->where('branch_id', $period['branch_id']);
            
            foreach ($branchStaff as $employee) {
                $salaryInfo = DB::table('staff_salary_info')
                    ->where('user_id', $employee->id)
                    ->first();

                if (!$salaryInfo) continue;

                $baseRate = $salaryInfo->base_rate;
                $daysWorked = rand(20, 26); // 20-26 working days
                $hoursWorked = $daysWorked * 8; // 8 hours per day
                $overtimeHours = rand(0, 20); // 0-20 OT hours
                $overtimePay = $overtimeHours * 40; // ₱40 per hour OT
                
                // Calculate pay
                $grossPay = $baseRate + $overtimePay;
                $deductions = $baseRate * 0.10; // 10% deductions on base only
                $bonuses = $period['month'] == 0 ? 0 : rand(0, 2000); // Random bonuses for past months
                $netPay = $grossPay - $deductions + $bonuses;

                DB::table('payroll_items')->insert([
                    'payroll_period_id' => $period['id'],
                    'user_id' => $employee->id,
                    'branch_id' => $period['branch_id'],
                    'days_worked' => $daysWorked,
                    'hours_worked' => $hoursWorked,
                    'base_rate' => $baseRate,
                    'overtime_hours' => $overtimeHours,
                    'overtime_pay' => $overtimePay,
                    'gross_pay' => $grossPay,
                    'deductions' => $deductions,
                    'bonuses' => $bonuses,
                    'net_pay' => $netPay,
                    'status' => $period['month'] == 0 ? 'pending' : 'paid',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update period total
                DB::table('payroll_periods')
                    ->where('id', $period['id'])
                    ->increment('total_amount', $netPay);
            }
        }

        $this->command->info('Payroll data seeded successfully!');
        $this->command->info('- Staff Salary Info: ' . $staff->count() . ' records');
        $this->command->info('- Payroll Periods: ' . count($payrollPeriods) . ' records');
        $this->command->info('- Payroll Items: ' . (count($payrollPeriods) * $staff->count()) . ' records');
    }
}
