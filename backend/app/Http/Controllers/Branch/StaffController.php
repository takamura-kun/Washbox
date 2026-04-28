<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StaffSalaryInfo;
use App\Models\PayrollItem;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    /**
     * Display staff list for the branch
     */
    public function index(Request $request)
    {
        $branch = Auth::guard('branch')->user();
        
        // Get staff for this branch only
        $query = User::where('role', 'staff')
            ->where('branch_id', $branch->id)
            ->with(['salaryInfo' => function($q) {
                $q->where('is_active', true);
            }])
            ->withCount(['laundries']);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $staff = $query->orderBy('name')->paginate(12);

        // Calculate statistics
        $stats = [
            'total' => User::where('role', 'staff')->where('branch_id', $branch->id)->count(),
            'active' => User::where('role', 'staff')->where('branch_id', $branch->id)->where('is_active', true)->count(),
            'with_salary' => StaffSalaryInfo::where('branch_id', $branch->id)->where('is_active', true)->distinct('user_id')->count('user_id'),
        ];

        // Calculate total monthly payroll estimate
        $salaries = StaffSalaryInfo::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->get();
        
        $totalMonthly = $salaries->sum(function($salary) {
            if ($salary->salary_type === 'monthly') {
                return $salary->base_rate;
            } elseif ($salary->salary_type === 'daily') {
                return $salary->base_rate * 26; // Approximate monthly
            } else {
                return $salary->base_rate * 8 * 26; // Hourly
            }
        });

        // Add default rate for staff without salary
        $withoutSalary = $stats['total'] - $stats['with_salary'];
        $totalMonthly += ($withoutSalary * 480 * 26);

        $stats['total_monthly'] = $totalMonthly;

        return view('branch.staff.index', compact('staff', 'stats', 'branch'));
    }

    /**
     * Display staff details with payroll info
     */
    public function show(User $user)
    {
        $branch = Auth::guard('branch')->user();
        
        // Ensure staff belongs to this branch
        if ($user->branch_id !== $branch->id) {
            abort(403, 'Unauthorized access to staff from another branch');
        }

        // Load relationships
        $user->load(['branch']);

        // Get salary info
        $salaryInfo = StaffSalaryInfo::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        // Get attendance statistics
        $attendances = Attendance::where('user_id', $user->id)->get();
        
        $stats = [
            'total_days' => $attendances->count(),
            'present_days' => $attendances->whereIn('status', ['present', 'on_leave'])->count(),
            'total_hours' => $attendances->sum('hours_worked') ?? 0,
            'total_earnings' => $attendances->sum(function($att) {
                if ($att->status === 'on_leave') return 480;
                if ($att->status === 'absent') return 0;
                $hours = $att->hours_worked ?? 0;
                if ($hours >= 8) return 480;
                if ($hours >= 4) return 240;
                return 0;
            }),
        ];

        // Recent attendance
        $recent_attendance = Attendance::where('user_id', $user->id)
            ->latest('attendance_date')
            ->take(10)
            ->get();

        // Payroll history
        $payrollHistory = PayrollItem::where('user_id', $user->id)
            ->with(['payrollPeriod'])
            ->latest()
            ->take(10)
            ->get();

        return view('branch.staff.show', compact('user', 'salaryInfo', 'stats', 'recent_attendance', 'payrollHistory', 'branch'));
    }

    /**
     * Display salary information table for all staff
     */
    public function salaryInformation()
    {
        $branch = Auth::guard('branch')->user();

        // Get staff with salary information
        $staffWithSalary = User::where('branch_id', $branch->id)
            ->whereHas('salaryInfo', function($q) {
                $q->where('is_active', true);
            })
            ->with(['branch', 'salaryInfo' => function($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name')
            ->get();

        // Calculate estimated monthly payroll
        $estimatedMonthlyPayroll = $staffWithSalary->sum(function ($staff) {
            if ($staff->salaryInfo->salary_type === 'monthly') {
                return $staff->salaryInfo->base_rate;
            } elseif ($staff->salaryInfo->salary_type === 'daily') {
                return $staff->salaryInfo->base_rate * 26;
            } else {
                return $staff->salaryInfo->base_rate * 8 * 26;
            }
        });

        return view('branch.staff.salary-information', compact(
            'staffWithSalary',
            'estimatedMonthlyPayroll'
        ));
    }
}
