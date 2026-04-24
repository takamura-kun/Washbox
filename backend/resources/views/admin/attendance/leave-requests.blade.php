@extends('admin.layouts.app')

@section('page-title', 'Leave Requests Management')

@push('styles')
<style>
.card {
    background: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}
.card-body {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
}
.card-header {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}
.card-footer {
    background: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}
.table-responsive {
    background: var(--card-bg) !important;
}
.table {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
}
.table tbody tr {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
}
.table thead th {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}
.table tbody td {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--text-primary);">Leave Requests</h2>
            <p class="small mb-0" style="color: var(--text-secondary);">Review and approve staff leave requests</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Attendance
            </a>
            <a href="{{ route('admin.leave-requests.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-2"></i>Create Leave
            </a>
        </div>
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
                            <h6 class="mb-1" style="color: var(--text-secondary);">Pending</h6>
                            <h3 class="mb-0" style="color: var(--text-primary);">{{ $summary['pending'] }}</h3>
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
                            <h6 class="mb-1" style="color: var(--text-secondary);">Approved</h6>
                            <h3 class="mb-0" style="color: var(--text-primary);">{{ $summary['approved'] }}</h3>
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
                            <h6 class="mb-1" style="color: var(--text-secondary);">Rejected</h6>
                            <h3 class="mb-0" style="color: var(--text-primary);">{{ $summary['rejected'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Branch</label>
                    <select name="branch_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Staff</label>
                    <select name="user_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Staff</option>
                        @foreach($staff as $member)
                            <option value="{{ $member->id }}" {{ $userId == $member->id ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.leave-requests.index') }}" class="btn btn-light border w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i>Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Leave Requests Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Leave Requests</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Staff</th>
                            <th>Branch</th>
                            <th>Leave Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Days</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
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
                                        <div class="fw-semibold" style="color: var(--text-primary);">{{ $request->user->name }}</div>
                                        <small style="color: var(--text-secondary);">{{ $request->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $request->branch->name }}
                                </span>
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
                                    <a href="{{ asset('storage/' . $request->attachment) }}" target="_blank" class="ms-1">
                                        <i class="bi bi-paperclip text-primary"></i>
                                    </a>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $request->status_badge }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            <td>
                                @if($request->status === 'pending')
                                    <div class="btn-group btn-group-sm">
                                        <form action="{{ route('admin.leave-requests.approve', $request) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve" onclick="return confirm('Approve this leave request?')">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                        <button class="btn btn-sm btn-danger" onclick="showRejectModal({{ $request->id }})" title="Reject">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                @else
                                    <small style="color: var(--text-secondary);">
                                        By {{ $request->approver->name ?? 'N/A' }}<br>
                                        {{ $request->approved_at?->format('M d, Y') }}
                                    </small>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bi bi-inbox fs-1" style="color: var(--text-secondary);"></i>
                                <p class="mt-2" style="color: var(--text-secondary);">No leave requests found</p>
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

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showRejectModal(requestId) {
    const form = document.getElementById('rejectForm');
    form.action = `/admin/leave-requests/${requestId}/reject`;
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>
@endpush
@endsection
