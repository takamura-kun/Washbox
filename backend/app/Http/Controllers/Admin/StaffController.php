<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use App\Models\StaffSalaryInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
{
    /**
     * Display a listing of staff members
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'staff')
            ->with(['branch'])
            ->withCount(['laundries']);

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortLaundry = $request->get('sort_laundries', 'desc');
        $query->orderBy('laundries_count', $sortLaundry);

        $staff = $query->paginate(12);
        $branches = Branch::active()->get();

        // Statistics
        $stats = [
            'total' => User::where('role', 'staff')->count(),
            'active' => User::where('role', 'staff')->where('is_active', true)->count(),
            'inactive' => User::where('role', 'staff')->where('is_active', false)->count(),
            'total_laundries' => User::where('role', 'staff')->withCount('laundries')->get()->sum('laundries_count'),
            'with_salary' => \App\Models\StaffSalaryInfo::where('is_active', true)->distinct('user_id')->count('user_id'),
        ];

        return view('admin.staff.index', compact('staff', 'branches', 'stats'));
    }

    /**
     * Show the form for creating a new staff member
     */
    public function create()
    {
        $branches = Branch::active()->get();
        return view('admin.staff.create', compact('branches'));
    }

    /**
     * Store a newly created staff member in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'branch_id' => 'required|exists:branches,id',
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id',
            'position' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['profile_photo_path'] = $request->file('photo')->store('staff', 'public');
        }

        // Set role and dummy password (staff use branch credentials to login)
        $validated['role'] = 'staff';
        $validated['password'] = Hash::make('dummy_password_' . uniqid());
        $validated['is_active'] = $request->has('is_active') ? true : false;

        User::create($validated);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member added successfully!');
    }

    /**
     * Display the specified staff member
     */
    public function show(User $staff)
    {
        // Load relationships
        $staff->load(['branch']);

        // Get payroll history first
        $payrollHistory = \App\Models\PayrollItem::where('user_id', $staff->id)
            ->with(['payrollPeriod'])
            ->latest()
            ->take(10)
            ->get();

        // Calculate attendance statistics
        $attendances = \App\Models\Attendance::where('user_id', $staff->id)->get();
        
        // Calculate total earnings from PAID payroll items
        $totalEarningsFromPayroll = \App\Models\PayrollItem::where('user_id', $staff->id)
            ->where('status', 'paid')
            ->sum('net_pay');
        
        $stats = [
            'total_days' => $attendances->count(),
            'present_days' => $attendances->whereIn('status', ['present', 'late', 'on_leave'])->count(),
            'total_hours' => $attendances->sum('hours_worked') ?? 0,
            'total_earnings' => $totalEarningsFromPayroll, // Use actual paid payroll
        ];

        // Recent attendance
        $recent_attendance = \App\Models\Attendance::where('user_id', $staff->id)
            ->latest('attendance_date')
            ->take(10)
            ->get();

        // Payroll data
        $salaryInfo = \App\Models\StaffSalaryInfo::where('user_id', $staff->id)
            ->where('is_active', true)
            ->first();

        return view('admin.staff.show', compact('staff', 'stats', 'recent_attendance', 'salaryInfo', 'payrollHistory'));
    }

    /**
     * Show the form for editing the specified staff member
     */
    public function edit(User $staff)
    {
        $branches = Branch::active()->get();
        return view('admin.staff.edit', compact('staff', 'branches'));
    }

    /**
     * Update the specified staff member in storage
     */
    public function update(Request $request, User $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->id,
            'phone' => 'nullable|string|max:50',
            'branch_id' => 'required|exists:branches,id',
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id,' . $staff->id,
            'position' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($staff->profile_photo_path) {
                Storage::disk('public')->delete($staff->profile_photo_path);
            }
            $validated['profile_photo_path'] = $request->file('photo')->store('staff', 'public');
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $staff->update($validated);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member updated successfully!');
    }

    /**
     * Remove the specified staff member from storage
     */
    public function destroy(User $staff)
    {
        // Check if staff has any laundries
        if ($staff->laundries()->count() > 0) {
            return redirect()->route('admin.staff.index')
                ->with('error', 'Cannot delete staff member with existing laundries. Please deactivate instead.');
        }

        // Delete photo if exists
        if ($staff->profile_photo_path) {
            Storage::disk('public')->delete($staff->profile_photo_path);
        }

        $staff->delete();

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member deleted successfully!');
    }

    /**
     * Toggle staff member active/inactive status
     */
    public function toggleStatus(User $staff)
    {
        $staff->update([
            'is_active' => !$staff->is_active
        ]);

        $status = $staff->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff member {$staff->name} {$status} successfully!")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Update staff salary information
     */
    public function updateSalary(Request $request, User $user)
    {
        $validated = $request->validate([
            'salary_type' => 'required|in:monthly,daily,hourly',
            'base_rate' => 'required|numeric|min:0',
            'pay_period' => 'required|in:weekly,bi-weekly,monthly',
            'effectivity_date' => 'required|date',
        ]);

        // Deactivate old salary info
        \App\Models\StaffSalaryInfo::where('user_id', $user->id)
            ->update(['is_active' => false]);

        // Create new salary info
        \App\Models\StaffSalaryInfo::create([
            'user_id' => $user->id,
            'branch_id' => $user->branch_id,
            'salary_type' => $validated['salary_type'],
            'base_rate' => $validated['base_rate'],
            'pay_period' => $validated['pay_period'],
            'effectivity_date' => $validated['effectivity_date'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.staff.show', $user)
            ->with('success', 'Salary information updated successfully!');
    }

    /**
     * Delete staff salary information
     */
    public function deleteSalary(User $user)
    {
        \App\Models\StaffSalaryInfo::where('user_id', $user->id)
            ->update(['is_active' => false]);

        return redirect()->back()
            ->with('success', 'Salary information deactivated successfully!');
    }

    /**
     * Salary management page
     */
    public function salaryManagement(Request $request)
    {
        $query = User::where('role', 'staff')
            ->with(['branch', 'salaryInfo' => function($q) {
                $q->where('is_active', true);
            }])
            ->where('is_active', true);

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by salary type
        if ($request->filled('salary_type')) {
            if ($request->salary_type === 'none') {
                $query->doesntHave('salaryInfo');
            } else {
                $query->whereHas('salaryInfo', function($q) use ($request) {
                    $q->where('salary_type', $request->salary_type)
                      ->where('is_active', true);
                });
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $staff = $query->paginate(20);
        $branches = Branch::active()->get();

        // Calculate summary
        $totalStaff = User::where('role', 'staff')->where('is_active', true)->count();
        $withSalary = \App\Models\StaffSalaryInfo::where('is_active', true)
            ->distinct('user_id')
            ->count('user_id');
        $withoutSalary = $totalStaff - $withSalary;
        
        // Calculate total monthly (approximate)
        $totalMonthly = \App\Models\StaffSalaryInfo::where('is_active', true)
            ->get()
            ->sum(function($salary) {
                if ($salary->salary_type === 'monthly') {
                    return $salary->base_rate;
                } elseif ($salary->salary_type === 'daily') {
                    return $salary->base_rate * 26; // Approximate monthly
                } else {
                    return $salary->base_rate * 8 * 26; // Hourly * 8 hours * 26 days
                }
            });
        
        // Add default rate for staff without salary info
        $totalMonthly += ($withoutSalary * 480 * 26);

        $summary = [
            'total_staff' => $totalStaff,
            'with_salary' => $withSalary,
            'without_salary' => $withoutSalary,
            'total_monthly' => $totalMonthly,
        ];

        return view('admin.staff.salary-management', compact('staff', 'branches', 'summary'));
    }

    /**
     * Bulk salary update
     */
    public function bulkSalaryUpdate(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'salary_type' => 'required|in:monthly,daily,hourly',
            'base_rate' => 'required|numeric|min:0',
            'pay_period' => 'required|in:weekly,bi-weekly,monthly',
            'effectivity_date' => 'required|date',
        ]);

        $staff = User::where('role', 'staff')
            ->where('branch_id', $validated['branch_id'])
            ->where('is_active', true)
            ->get();

        $count = 0;
        foreach ($staff as $member) {
            // Deactivate old salary info
            \App\Models\StaffSalaryInfo::where('user_id', $member->id)
                ->update(['is_active' => false]);

            // Create new salary info
            \App\Models\StaffSalaryInfo::create([
                'user_id' => $member->id,
                'branch_id' => $member->branch_id,
                'salary_type' => $validated['salary_type'],
                'base_rate' => $validated['base_rate'],
                'pay_period' => $validated['pay_period'],
                'effectivity_date' => $validated['effectivity_date'],
                'is_active' => true,
            ]);
            $count++;
        }

        return redirect()->route('admin.staff.salary-management')
            ->with('success', "Salary information updated for {$count} staff members!");
    }
}
