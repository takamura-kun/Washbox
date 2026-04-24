<?php

namespace App\Observers;

use App\Models\PayrollItem;
use App\Models\AdminNotification;
use App\Models\BranchNotification;

class PayrollObserver
{
    /**
     * Handle the PayrollItem "created" event.
     */
    public function created(PayrollItem $payroll): void
    {
        $payroll->loadMissing('user', 'branch', 'payrollPeriod');

        $periodLabel = $payroll->payrollPeriod->period_label ?? 'N/A';

        // 🔔 NOTIFY ADMIN: Payroll generated
        AdminNotification::create([
            'type' => 'payroll_generated',
            'title' => 'Payroll Generated',
            'message' => "Payroll for {$payroll->user->name} - ₱" . number_format($payroll->net_pay, 2) . " ({$periodLabel})",
            'icon' => 'cash-stack',
            'color' => 'info',
            'link' => route('admin.finance.payroll.show', $payroll->payroll_period_id),
            'data' => [
                'payroll_id' => $payroll->id,
                'staff_name' => $payroll->user->name,
                'net_pay' => $payroll->net_pay,
                'period' => $periodLabel,
            ],
            'branch_id' => $payroll->branch_id,
        ]);

        // 🔔 NOTIFY BRANCH: Payroll generated
        if ($payroll->branch_id) {
            BranchNotification::create([
                'branch_id' => $payroll->branch_id,
                'type' => 'payroll_generated',
                'title' => 'Payroll Generated',
                'message' => "Payroll for {$payroll->user->name} - ₱" . number_format($payroll->net_pay, 2),
                'icon' => 'cash-stack',
                'color' => 'info',
                'link' => route('admin.finance.payroll.show', $payroll->payroll_period_id),
            ]);
        }
    }

    /**
     * Handle the PayrollItem "updated" event.
     */
    public function updated(PayrollItem $payroll): void
    {
        // Check if payment status changed to paid
        if ($payroll->isDirty('payment_status') && $payroll->payment_status === 'paid') {
            $payroll->loadMissing('user', 'branch');

            // 🔔 NOTIFY ADMIN: Payroll paid
            AdminNotification::create([
                'type' => 'payroll_paid',
                'title' => 'Payroll Payment Completed',
                'message' => "Payroll for {$payroll->user->name} paid - ₱" . number_format($payroll->net_pay, 2),
                'icon' => 'check-circle',
                'color' => 'success',
                'link' => route('admin.finance.payroll.show', $payroll->payroll_period_id),
                'branch_id' => $payroll->branch_id,
            ]);
        }
    }
}
