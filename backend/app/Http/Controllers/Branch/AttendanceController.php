<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\{Attendance, User, LeaveRequest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Get the current branch ID from authenticated user
     */
    private function getBranchId()
    {
        if (auth()->guard('branch')->check()) {
            return auth()->guard('branch')->user()->id;
        }
        return auth()->user()->branch_id;
    }

    /**
     * Display branch attendance dashboard
     */
    public function index(Request $request)
    {
        $branchId = $this->getBranchId();
        $date = $request->input('date', today()->toDateString());

        $attendances = Attendance::with(['user'])
            ->where('branch_id', $branchId)
            ->where('attendance_date', $date)
            ->orderBy('time_in', 'desc')
            ->get();

        // Get branch staff
        $staff = User::where('role', 'staff')
            ->where('is_active', true)
            ->where('branch_id', $branchId)
            ->get();

        $summary = [
            'total_staff' => $staff->count(),
            'present' => $attendances->whereIn('status', ['present', 'late'])->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'half_day' => $attendances->where('shift_type', 'half_day')->count(),
            'on_leave' => $attendances->where('status', 'on_leave')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'not_timed_in' => $staff->count() - $attendances->count(),
        ];

        return view('branch.attendance.index', compact('attendances', 'summary', 'staff', 'date'));
    }

    /**
     * Time in staff
     */
    public function timeIn(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'photo_data' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $branchId = $this->getBranchId();

        // Verify staff belongs to this branch
        $staff = User::find($request->user_id);
        if ($staff->branch_id !== $branchId) {
            return response()->json([
                'success' => false,
                'message' => 'Staff does not belong to your branch',
            ], 403);
        }

        try {
            // Convert base64 image to file
            $photoPath = null;
            if ($request->filled('photo_data')) {
                $image = $request->photo_data;
                $image = str_replace('data:image/jpeg;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = 'attendance_' . time() . '_' . $request->user_id . '.jpg';
                $path = 'attendance/time-in/' . $imageName;
                \Storage::disk('public')->put($path, base64_decode($image));
                $photoPath = $path;
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
                $branchId,
                $photoPath,
                $location
            );

            return redirect()->back()->with('success', 'Time in recorded successfully at ' . $attendance->formatted_time_in);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Time out staff
     */
    public function timeOut(Request $request, Attendance $attendance)
    {
        // Verify attendance belongs to this branch
        if ($attendance->branch_id !== $this->getBranchId()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'photo_data' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        try {
            // Convert base64 image to file
            $photoPath = null;
            if ($request->filled('photo_data')) {
                $image = $request->photo_data;
                $image = str_replace('data:image/jpeg;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = 'attendance_out_' . time() . '_' . $attendance->user_id . '.jpg';
                $path = 'attendance/time-out/' . $imageName;
                \Storage::disk('public')->put($path, base64_decode($image));
                $photoPath = $path;
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
     * Manual attendance entry
     */
    public function manualEntry(Request $request)
    {
        $branchId = $this->getBranchId();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,half_day,late,on_leave',
            'notes' => 'nullable|string',
        ]);

        // Verify staff belongs to this branch
        $staff = User::find($request->user_id);
        if ($staff->branch_id !== $branchId) {
            return redirect()->back()->with('error', 'Staff does not belong to your branch');
        }

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
                'branch_id' => $branchId,
                'time_in' => $timeIn,
                'time_out' => $timeOut,
                'hours_worked' => $hoursWorked,
                'status' => $request->status,
                'shift_type' => $hoursWorked >= 8 ? 'full_day' : 'half_day',
                'notes' => $request->notes,
                'is_verified' => false, // Branch entries need admin verification
            ]
        );

        return redirect()->back()->with('success', 'Attendance recorded successfully');
    }

    /**
     * Attendance report
     */
    public function report(Request $request)
    {
        $branchId = $this->getBranchId();
        $userId = $request->input('user_id');
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $query = Attendance::with(['user'])
            ->where('branch_id', $branchId)
            ->byDateRange($startDate, $endDate);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->get();

        $summary = [
            'total_days' => $attendances->count(),
            'present_days' => $attendances->whereIn('status', ['present', 'late'])->count(),
            'absent_days' => $attendances->where('status', 'absent')->count(),
            'half_days' => $attendances->where('shift_type', 'half_day')->count(),
            'leave_days' => $attendances->where('status', 'on_leave')->count(),
            'total_hours' => $attendances->sum('hours_worked'),
            'total_pay' => $attendances->sum(fn($a) => $a->calculateDailyPay()),
        ];

        $staff = User::where('role', 'staff')
            ->where('is_active', true)
            ->where('branch_id', $branchId)
            ->get();

        return view('branch.attendance.report', compact(
            'attendances',
            'summary',
            'staff',
            'userId',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Submit leave request for staff
     */
    public function submitLeave(Request $request)
    {
        $branchId = $this->getBranchId();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'leave_date_from' => 'required|date',
            'leave_date_to' => 'required|date|after_or_equal:leave_date_from',
            'leave_type' => 'required|in:sick,vacation,emergency,unpaid',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Verify staff belongs to this branch
        $staff = User::find($request->user_id);
        if ($staff->branch_id !== $branchId) {
            return redirect()->back()->with('error', 'Staff does not belong to your branch');
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
        }

        $totalDays = Carbon::parse($request->leave_date_from)
            ->diffInDays(Carbon::parse($request->leave_date_to)) + 1;

        LeaveRequest::create([
            'user_id' => $request->user_id,
            'branch_id' => $branchId,
            'leave_date_from' => $request->leave_date_from,
            'leave_date_to' => $request->leave_date_to,
            'total_days' => $totalDays,
            'leave_type' => $request->leave_type,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Leave request submitted successfully. Waiting for admin approval.');
    }

    /**
     * View leave requests
     */
    public function leaveRequests()
    {
        $branchId = $this->getBranchId();

        $leaveRequests = LeaveRequest::with(['user', 'approver'])
            ->where('branch_id', $branchId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $staff = User::where('role', 'staff')
            ->where('is_active', true)
            ->where('branch_id', $branchId)
            ->get();

        return view('branch.attendance.leave-requests', compact('leaveRequests', 'staff'));
    }

    /**
     * Mark staff as absent
     */
    public function markAbsent(Request $request)
    {
        $branchId = $this->getBranchId();
        $date = $request->input('date', today()->toDateString());

        Attendance::markAbsentForDate($date, $branchId);

        return redirect()->back()->with('success', 'Absent staff marked for ' . Carbon::parse($date)->format('M d, Y'));
    }
}
