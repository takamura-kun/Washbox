<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollViewController extends Controller
{
    /**
     * Display list of payroll for all staff in the branch
     */
    public function index()
    {
        $branch = Auth::guard('branch')->user();

        // Get all payroll items for staff in this branch
        $payrollItems = PayrollItem::where('branch_id', $branch->id)
            ->with(['payrollPeriod', 'branch', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Calculate totals for this branch
        $totalPaid = PayrollItem::where('branch_id', $branch->id)
            ->where('status', 'paid')
            ->sum('net_pay');

        $pendingPayments = PayrollItem::where('branch_id', $branch->id)
            ->where('status', 'approved')
            ->sum('net_pay');

        $totalStaff = User::where('branch_id', $branch->id)
            ->where('role', 'staff')
            ->count();

        $stats = [
            'total_paid' => $totalPaid,
            'pending_payments' => $pendingPayments,
            'total_payrolls' => $payrollItems->total(),
            'total_staff' => $totalStaff,
        ];

        return view('branch.payroll.index', compact('payrollItems', 'stats'));
    }

    /**
     * Display detailed payslip for a specific payroll item
     */
    public function show(PayrollItem $payrollItem)
    {
        $branch = Auth::guard('branch')->user();

        // Ensure the payroll item belongs to this branch
        if ($payrollItem->branch_id !== $branch->id) {
            abort(403, 'Unauthorized access to payroll information.');
        }

        // Load relationships
        $payrollItem->load(['payrollPeriod', 'branch', 'user.salaryInfo']);

        return view('branch.payroll.show', compact('payrollItem'));
    }
}
