<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use App\Models\PayrollItem;
use App\Models\StaffSalaryInfo;
use App\Models\User;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $query = PayrollPeriod::with(['branch', 'processedBy', 'items']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $periods = $query->latest('date_from')->paginate(20);
        $branches = Branch::all();

        return view('admin.finance.payroll.index', compact('periods', 'branches'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('admin.finance.payroll.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'period_label' => 'required|string|max:255',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'pay_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            // Create payroll period
            $period = PayrollPeriod::create([
                'branch_id' => $validated['branch_id'],
                'period_label' => $validated['period_label'],
                'date_from' => $validated['date_from'],
                'date_to' => $validated['date_to'],
                'pay_date' => $validated['pay_date'],
                'status' => 'draft',
                'total_amount' => 0,
                'processed_by' => auth()->id(),
            ]);

            // Get staff with salary info
            $query = StaffSalaryInfo::with(['user', 'branch'])
                ->where('is_active', true);

            if ($validated['branch_id']) {
                $query->where('branch_id', $validated['branch_id']);
            }

            $staffSalaries = $query->get();

            $totalAmount = 0;

            foreach ($staffSalaries as $salary) {
                // Get attendance records for the period
                $attendances = \App\Models\Attendance::where('user_id', $salary->user_id)
                    ->whereBetween('attendance_date', [$validated['date_from'], $validated['date_to']])
                    ->get();

                // Calculate based on actual attendance
                $daysWorked = $attendances->whereIn('status', ['present', 'late'])->count();
                $halfDays = $attendances->where('shift_type', 'half_day')->count();
                $totalHours = $attendances->sum('hours_worked');
                $overtimeHours = $attendances->sum(fn($a) => $a->calculateOvertimeHours());
                $overtimePay = $attendances->sum(fn($a) => $a->calculateOvertimePay());
                $grossPay = $attendances->sum(fn($a) => $a->calculateDailyPay()) + $overtimePay;

                // Create payroll item
                PayrollItem::create([
                    'payroll_period_id' => $period->id,
                    'user_id' => $salary->user_id,
                    'branch_id' => $salary->branch_id,
                    'days_worked' => $daysWorked,
                    'hours_worked' => $totalHours,
                    'overtime_hours' => $overtimeHours,
                    'base_rate' => $salary->base_rate,
                    'overtime_pay' => $overtimePay,
                    'gross_pay' => $grossPay,
                    'deductions' => 0,
                    'bonuses' => 0,
                    'net_pay' => $grossPay,
                    'status' => 'pending',
                ]);

                $totalAmount += $grossPay;
            }

            // Update period total
            $period->total_amount = $totalAmount;
            $period->save();

            DB::commit();

            return redirect()->route('admin.finance.payroll.show', $period)
                ->with('success', 'Payroll period created successfully with attendance data.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create payroll: ' . $e->getMessage());
        }
    }

    public function show(PayrollPeriod $period)
    {
        $period->load(['branch', 'processedBy', 'items.user', 'items.branch']);
        return view('admin.finance.payroll.show', compact('period'));
    }

    public function updateItem(Request $request, PayrollItem $item)
    {
        // Check if payroll is still in draft
        if ($item->payrollPeriod->status !== 'draft') {
            return back()->with('error', 'Cannot edit payroll that is not in draft status.');
        }

        $validated = $request->validate([
            'days_worked'    => 'required|numeric|min:0',
            'hours_worked'   => 'required|numeric|min:0',
            'overtime_hours' => 'required|numeric|min:0',
            'deductions'     => 'required|numeric|min:0',
            'bonuses'        => 'required|numeric|min:0',
        ]);

        // Cascading calculation logic
        $daysWorked = $validated['days_worked'];
        $hoursWorked = $validated['hours_worked'];
        $overtimeHours = $validated['overtime_hours'];
        $deductions = $validated['deductions'];
        $bonuses = $validated['bonuses'];
        
        // Calculate pay components
        $regularPay = $item->base_rate * $daysWorked; // Base rate × days
        $overtimePay = round($overtimeHours * 40, 2); // OT hours × ₱40
        $grossPay = $regularPay + $overtimePay; // Regular + OT
        $netPay = $grossPay - $deductions + $bonuses; // Gross - deductions + bonuses

        // Update all related fields
        $item->update([
            'days_worked' => $daysWorked,
            'hours_worked' => $hoursWorked,
            'overtime_hours' => $overtimeHours,
            'overtime_pay' => $overtimePay,
            'gross_pay' => $grossPay,
            'deductions' => $deductions,
            'bonuses' => $bonuses,
            'net_pay' => $netPay,
        ]);

        // Update period total
        $period = $item->payrollPeriod;
        $period->total_amount = $period->items()->sum('net_pay');
        $period->save();

        return back()->with('success', 'Payroll item updated successfully. All related fields have been recalculated.');
    }

    public function approve(PayrollPeriod $period)
    {
        DB::beginTransaction();
        try {
            $period->status = 'approved';
            $period->save();

            // Create expense for payroll
            $expenseCategory = ExpenseCategory::where('slug', 'salaries')->first();
            if ($expenseCategory) {
                Expense::create([
                    'branch_id' => $period->branch_id,
                    'expense_category_id' => $expenseCategory->id,
                    'title' => 'Payroll - ' . $period->period_label,
                    'amount' => $period->total_amount,
                    'expense_date' => $period->pay_date,
                    'reference_no' => 'PAYROLL-' . $period->id,
                    'source' => 'auto',
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();

            return back()->with('success', 'Payroll approved and expense recorded.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve payroll: ' . $e->getMessage());
        }
    }

    public function markAsPaid(PayrollPeriod $period)
    {
        $period->status = 'paid';
        $period->items()->update(['status' => 'paid']);
        $period->save();

        return back()->with('success', 'Payroll marked as paid.');
    }

    public function destroy(PayrollPeriod $period)
    {
        if ($period->status !== 'draft') {
            return back()->with('error', 'Only draft payroll can be deleted.');
        }

        $period->delete();

        return redirect()->route('admin.finance.payroll.index')
            ->with('success', 'Payroll deleted successfully.');
    }

    private function calculateGrossPay($salary, $dateFrom, $dateTo)
    {
        $days = \Carbon\Carbon::parse($dateFrom)->diffInDays(\Carbon\Carbon::parse($dateTo)) + 1;

        switch ($salary->salary_type) {
            case 'monthly':
                return $salary->base_rate;
            case 'daily':
                return $salary->base_rate * $days;
            case 'hourly':
                return $salary->base_rate * 8 * $days; // Assuming 8 hours per day
            default:
                return 0;
        }
    }
}
