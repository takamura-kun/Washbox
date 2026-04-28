<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Attendance, User, Branch, LeaveRequest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display attendance dashboard
     */
    public function index(Request $request)
    {
        // Validate inputs to prevent SQL injection
        $validated = $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        $branchId = $validated['branch_id'] ?? null;
        $date = $validated['date'] ?? today()->toDateString();

        $query = Attendance::with(['user', 'branch'])
            ->where('attendance_date', $date);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $attendances = $query->orderBy('time_in', 'desc')->paginate(50);

        $summary = [
            'total_staff' => User::where('role', 'staff')->where('is_active', true)->count(),
            'present' => Attendance::where('attendance_date', $date)->whereIn('status', ['present', 'late'])->count(),
            'absent' => Attendance::where('attendance_date', $date)->where('status', 'absent')->count(),
            'half_day' => Attendance::where('attendance_date', $date)->where('shift_type', 'half_day')->count(),
            'on_leave' => Attendance::where('attendance_date', $date)->where('status', 'on_leave')->count(),
            'late' => Attendance::where('attendance_date', $date)->where('status', 'late')->count(),
            'pending_leaves' => LeaveRequest::where('status', 'pending')->count(),
        ];

        $branches = Branch::active()->get();
        $staff = User::where('role', 'staff')->where('is_active', true)->get();

        return view('admin.attendance.index', compact('attendances', 'summary', 'branches', 'staff', 'branchId', 'date'));
    }

    /**
     * Time in (with photo and location verification)
     */
    public function timeIn(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'photo' => 'nullable|image|max:2048',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        try {
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('attendance/time-in', 'public');
            }

            $location = null;
            if ($request->filled('latitude') && $request->filled('longitude')) {
                $location = [
                    'lat' => $request->latitude,
                    'lng' => $request->longitude,
                ];
            }

            $attendance = Attendance::recordTimeIn(
                $request->user_id,
                $request->branch_id,
                $photoPath,
                $location
            );

            return response()->json([
                'success' => true,
                'message' => 'Time in recorded successfully at ' . $attendance->formatted_time_in,
                'attendance' => $attendance,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Time out
     */
    public function timeOut(Request $request, Attendance $attendance)
    {
        $request->validate([
            'photo' => 'nullable|image|max:2048',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        try {
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('attendance/time-out', 'public');
            }

            $location = null;
            if ($request->filled('latitude') && $request->filled('longitude')) {
                $location = [
                    'lat' => $request->latitude,
                    'lng' => $request->longitude,
                ];
            }

            $attendance->recordTimeOut($photoPath, $location);

            return response()->json([
                'success' => true,
                'message' => 'Time out recorded successfully at ' . $attendance->formatted_time_out,
                'hours_worked' => $attendance->hours_worked,
                'attendance' => $attendance->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Manual attendance entry (admin only)
     */
    public function manualEntry(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'attendance_date' => 'required|date',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,half_day,late,on_leave',
            'notes' => 'nullable|string',
        ]);

        $timeIn = Carbon::parse($request->attendance_date . ' ' . $request->time_in);
        $timeOut = $request->time_out ? Carbon::parse($request->attendance_date . ' ' . $request->time_out) : null;

        $hoursWorked = 0;
        if ($timeOut) {
            $hoursWorked = $timeIn->diffInMinutes($timeOut) / 60;
            $hoursWorked = max(0, $hoursWorked - 1); // Subtract 1 hour lunch
        }

        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'attendance_date' => $request->attendance_date,
            ],
            [
                'branch_id' => $request->branch_id,
                'time_in' => $timeIn,
                'time_out' => $timeOut,
                'hours_worked' => $hoursWorked,
                'status' => $request->status,
                'shift_type' => $hoursWorked >= 8 ? 'full_day' : 'half_day',
                'notes' => $request->notes,
                'is_verified' => true,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Attendance recorded successfully');
    }

    /**
     * Verify attendance
     */
    public function verify(Attendance $attendance)
    {
        $attendance->verify(auth()->id());

        return redirect()->back()->with('success', 'Attendance verified successfully');
    }

    /**
     * Bulk verify
     */
    public function bulkVerify(Request $request)
    {
        $request->validate([
            'attendance_ids' => 'required|array',
            'attendance_ids.*' => 'exists:attendances,id',
        ]);

        Attendance::whereIn('id', $request->attendance_ids)
            ->update([
                'is_verified' => true,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

        return redirect()->back()->with('success', count($request->attendance_ids) . ' attendance records verified');
    }

    /**
     * Staff attendance report
     */
    public function report(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $userId = $validated['user_id'] ?? null;
        $branchId = $validated['branch_id'] ?? null;
        $startDate = $validated['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? now()->endOfMonth()->toDateString();

        $query = Attendance::with(['user.salaryInfo', 'branch'])
            ->byDateRange($startDate, $endDate);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->get();

        $totalPay = 0;
        foreach ($attendances as $attendance) {
            $totalPay += $attendance->calculateDailyPay();
        }

        $summary = [
            'total_days' => $attendances->count(),
            'present_days' => $attendances->whereIn('status', ['present', 'late'])->count(),
            'absent_days' => $attendances->where('status', 'absent')->count(),
            'half_days' => $attendances->where('shift_type', 'half_day')->count(),
            'leave_days' => $attendances->where('status', 'on_leave')->count(),
            'total_hours' => $attendances->sum('hours_worked'),
            'total_pay' => $totalPay,
        ];

        $staff = User::where('role', 'staff')->where('is_active', true)->get();
        $branches = Branch::active()->get();

        return view('admin.attendance.report', compact(
            'attendances',
            'summary',
            'staff',
            'branches',
            'userId',
            'branchId',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Mark absent for no-show staff
     */
    public function markAbsent(Request $request)
    {
        // Validate inputs to prevent SQL injection
        $validated = $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        $date = $validated['date'] ?? today()->toDateString();
        $branchId = $validated['branch_id'] ?? null;

        Attendance::markAbsentForDate($date, $branchId);

        return redirect()->back()->with('success', 'Absent staff marked for ' . Carbon::parse($date)->format('M d, Y'));
    }
}
