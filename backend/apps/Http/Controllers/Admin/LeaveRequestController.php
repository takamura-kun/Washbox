<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{LeaveRequest, User, Branch};
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $branchId = $request->input('branch_id');
        $userId = $request->input('user_id');

        $query = LeaveRequest::with(['user', 'branch', 'approver'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $leaveRequests = $query->paginate(20);

        $summary = [
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
        ];

        $branches = Branch::active()->get();
        $staff = User::where('role', 'staff')->where('is_active', true)->get();

        return view('admin.attendance.leave-requests', compact(
            'leaveRequests',
            'summary',
            'branches',
            'staff',
            'status',
            'branchId',
            'userId'
        ));
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['user', 'branch', 'approver']);
        return view('admin.attendance.leave-show', compact('leaveRequest'));
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        $leaveRequest->approve(auth()->id());

        return redirect()->back()->with('success', 'Leave request approved successfully. Attendance records created.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $leaveRequest->reject(auth()->id(), $request->rejection_reason);

        return redirect()->back()->with('success', 'Leave request rejected.');
    }

    public function create()
    {
        $branches = Branch::active()->get();
        $staff = User::where('role', 'staff')->where('is_active', true)->get();

        return view('admin.attendance.leave-create', compact('branches', 'staff'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'leave_date_from' => 'required|date',
            'leave_date_to' => 'required|date|after_or_equal:leave_date_from',
            'leave_type' => 'required|in:sick,vacation,emergency,unpaid',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
        }

        $totalDays = \Carbon\Carbon::parse($request->leave_date_from)
            ->diffInDays(\Carbon\Carbon::parse($request->leave_date_to)) + 1;

        $leaveRequest = LeaveRequest::create([
            'user_id' => $request->user_id,
            'branch_id' => $request->branch_id,
            'leave_date_from' => $request->leave_date_from,
            'leave_date_to' => $request->leave_date_to,
            'total_days' => $totalDays,
            'leave_type' => $request->leave_type,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $leaveRequest->approve(auth()->id());

        return redirect()->route('admin.leave-requests.index')
            ->with('success', 'Leave request created and approved successfully.');
    }
}
