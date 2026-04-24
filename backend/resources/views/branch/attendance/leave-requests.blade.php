@extends('branch.layouts.app')

@section('page-title', 'Leave Requests')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Leave Requests</h2>
            <p class="text-muted small mb-0">View and manage staff leave requests</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('branch.attendance.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Attendance
            </a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#leaveRequestModal">
                <i class="bi bi-calendar-plus me-2"></i>Request Leave
            </button>
        </div>
    </div>

    {{-- Emergency Leave Info --}}
    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-start">
            <i class="bi bi-info-circle fs-4 me-3"></i>
            <div>
                <h6 class="alert-heading mb-2">🤒 Sick or Emergency? File Leave Immediately!</h6>
                <p class="mb-2">If you're sick or have an emergency, you can file leave for <strong>today or even yesterday</strong>:</p>
                <ul class="mb-0">
                    <li><strong>Sick Leave:</strong> Can be filed same-day or retroactively (medical certificate may be required)</li>
                    <li><strong>Emergency Leave:</strong> Can be filed immediately for urgent situations</li>
                    <li><strong>Available 24/7:</strong> Submit your request anytime, even at night</li>
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pending</h6>
                            <h3 class="mb-0">{{ $leaveRequests->where('status', 'pending')->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="bi bi-check-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Approved</h6>
                            <h3 class="mb-0">{{ $leaveRequests->where('status', 'approved')->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 text-danger rounded p-3">
                                <i class="bi bi-x-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Rejected</h6>
                            <h3 class="mb-0">{{ $leaveRequests->where('status', 'rejected')->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Leave Requests Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Leave Requests History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Staff</th>
                            <th>Leave Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Days</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveRequests as $request)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                        {{ substr($request->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $request->user->name }}</div>
                                        <small class="text-muted">{{ $request->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $request->leave_type === 'sick' ? 'danger' : ($request->leave_type === 'vacation' ? 'info' : 'warning') }}">
                                    {{ ucfirst($request->leave_type) }}
                                </span>
                            </td>
                            <td>{{ $request->leave_date_from->format('M d, Y') }}</td>
                            <td>{{ $request->leave_date_to->format('M d, Y') }}</td>
                            <td>{{ $request->total_days }} day(s)</td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $request->reason }}">
                                    {{ Str::limit($request->reason, 50) }}
                                </span>
                                @if($request->attachment)
                                    <a href="{{ asset('storage/' . $request->attachment) }}" target="_blank" class="ms-1" title="View Attachment">
                                        <i class="bi bi-paperclip text-primary"></i>
                                    </a>
                                @endif
                            </td>
                            <td>
                                @if($request->status === 'pending')
                                    <span class="badge bg-warning">
                                        <i class="bi bi-clock"></i> Pending
                                    </span>
                                @elseif($request->status === 'approved')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Approved
                                    </span>
                                    <br>
                                    <small class="text-muted">By {{ $request->approver->name ?? 'Admin' }}</small>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle"></i> Rejected
                                    </span>
                                    <br>
                                    <small class="text-muted">By {{ $request->approver->name ?? 'Admin' }}</small>
                                    @if($request->rejection_reason)
                                        <br>
                                        <button class="btn btn-sm btn-link p-0 text-danger" onclick="showRejectionReason('{{ addslashes($request->rejection_reason) }}')">
                                            View Reason
                                        </button>
                                    @endif
                                @endif
                            </td>
                            <td>
                                {{ $request->created_at->format('M d, Y') }}<br>
                                <small class="text-muted">{{ $request->created_at->format('h:i A') }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No leave requests found</p>
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#leaveRequestModal">
                                    <i class="bi bi-calendar-plus me-2"></i>Submit Your First Leave Request
                                </button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($leaveRequests->hasPages())
            <div class="card-footer bg-white">
                {{ $leaveRequests->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Leave Request Modal --}}
<div class="modal fade" id="leaveRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('branch.attendance.submit-leave') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Submit Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Staff Member <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Select Staff</option>
                            @foreach($staff as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">From Date <span class="text-danger">*</span></label>
                            <input type="date" name="leave_date_from" id="leave_date_from" class="form-control" required>
                            <small class="text-muted">Can select today or past dates for emergency/sick leave</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">To Date <span class="text-danger">*</span></label>
                            <input type="date" name="leave_date_to" id="leave_date_to" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type" id="leave_type" class="form-select" required onchange="handleLeaveTypeChange()">
                            <option value="">Select Type</option>
                            <option value="sick">🤒 Sick Leave (Can be same-day)</option>
                            <option value="emergency">🚨 Emergency Leave (Can be same-day)</option>
                            <option value="vacation">🏖️ Vacation Leave (Advance notice required)</option>
                            <option value="unpaid">💼 Unpaid Leave</option>
                        </select>
                        <small class="text-muted" id="leaveTypeHint"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Please provide a detailed reason for your leave request..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Attachment (Optional)</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">Upload medical certificate or supporting document (PDF, JPG, PNG - Max 2MB)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Rejection Reason Modal --}}
<div class="modal fade" id="rejectionReasonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rejection Reason</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="rejectionReasonText"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function handleLeaveTypeChange() {
    const leaveType = document.getElementById('leave_type').value;
    const fromDate = document.getElementById('leave_date_from');
    const toDate = document.getElementById('leave_date_to');
    const hint = document.getElementById('leaveTypeHint');
    
    const today = new Date().toISOString().split('T')[0];
    const yesterday = new Date(Date.now() - 86400000).toISOString().split('T')[0];
    
    if (leaveType === 'sick' || leaveType === 'emergency') {
        // Allow same-day and backdating for sick/emergency
        fromDate.removeAttribute('min');
        toDate.removeAttribute('min');
        fromDate.value = today; // Default to today
        toDate.value = today;
        
        if (leaveType === 'sick') {
            hint.textContent = '✅ Sick leave can be filed same-day or retroactively. Medical certificate may be required.';
            hint.className = 'text-success';
        } else {
            hint.textContent = '✅ Emergency leave can be filed immediately. Explanation required.';
            hint.className = 'text-success';
        }
    } else if (leaveType === 'vacation') {
        // Vacation requires advance notice (at least tomorrow)
        const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
        fromDate.setAttribute('min', tomorrow);
        toDate.setAttribute('min', tomorrow);
        fromDate.value = tomorrow;
        toDate.value = tomorrow;
        hint.textContent = '⚠️ Vacation leave requires advance notice (at least 1 day).';
        hint.className = 'text-warning';
    } else {
        // Unpaid leave
        fromDate.setAttribute('min', today);
        toDate.setAttribute('min', today);
        fromDate.value = today;
        toDate.value = today;
        hint.textContent = '💼 Unpaid leave will not be compensated.';
        hint.className = 'text-muted';
    }
}

// Set default to today when modal opens
document.getElementById('leaveRequestModal').addEventListener('show.bs.modal', function () {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('leave_date_from').value = today;
    document.getElementById('leave_date_to').value = today;
});

function showRejectionReason(reason) {
    document.getElementById('rejectionReasonText').textContent = reason;
    const modal = new bootstrap.Modal(document.getElementById('rejectionReasonModal'));
    modal.show();
}
</script>
@endpush
@endsection
