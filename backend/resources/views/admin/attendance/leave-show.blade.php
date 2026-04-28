@extends('admin.layouts.app')

@section('page-title', 'Leave Request Details')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Leave Request Details</h2>
            <p class="text-muted small mb-0">Review and manage leave request</p>
        </div>
        <a href="{{ route('admin.leave-requests.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to Leave Requests
        </a>
    </div>

    <div class="row">
        {{-- Main Details Card --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Leave Request Information</h5>
                        <span class="badge @if($leaveRequest->status === 'pending') bg-warning
                                          @elseif($leaveRequest->status === 'approved') bg-success
                                          @else bg-danger @endif">
                            {{ ucfirst($leaveRequest->status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Staff Information --}}
                    <div class="mb-4 pb-4 border-bottom">
                        <h6 class="fw-bold text-muted mb-3">Staff Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Staff Name</p>
                                <p class="fw-bold">{{ $leaveRequest->user->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Staff ID</p>
                                <p class="fw-bold">{{ $leaveRequest->user->id }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Branch</p>
                                <p class="fw-bold">{{ $leaveRequest->branch->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Position</p>
                                <p class="fw-bold">{{ $leaveRequest->user->position ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Leave Details --}}
                    <div class="mb-4 pb-4 border-bottom">
                        <h6 class="fw-bold text-muted mb-3">Leave Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Leave Type</p>
                                <p class="fw-bold">
                                    <span class="badge bg-info">{{ ucfirst($leaveRequest->leave_type) }}</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Total Days</p>
                                <p class="fw-bold">{{ $leaveRequest->total_days }} days</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">From Date</p>
                                <p class="fw-bold">{{ $leaveRequest->leave_date_from->format('M d, Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">To Date</p>
                                <p class="fw-bold">{{ $leaveRequest->leave_date_to->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Reason --}}
                    <div class="mb-4 pb-4 border-bottom">
                        <h6 class="fw-bold text-muted mb-3">Reason</h6>
                        <p class="mb-0">{{ $leaveRequest->reason }}</p>
                    </div>

                    {{-- Attachment --}}
                    @if($leaveRequest->attachment)
                    <div class="mb-4 pb-4 border-bottom">
                        <h6 class="fw-bold text-muted mb-3">Attachment</h6>
                        <a href="{{ Storage::url($leaveRequest->attachment) }}"
                           class="btn btn-sm btn-outline-primary"
                           target="_blank">
                            <i class="bi bi-download me-2"></i>Download File
                        </a>
                    </div>
                    @endif

                    {{-- Approval Information --}}
                    @if($leaveRequest->status !== 'pending')
                    <div class="mb-0">
                        <h6 class="fw-bold text-muted mb-3">Approval Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">
                                    @if($leaveRequest->status === 'approved')
                                        Approved By
                                    @else
                                        Rejected By
                                    @endif
                                </p>
                                <p class="fw-bold">{{ $leaveRequest->approver->name ?? 'System' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">
                                    @if($leaveRequest->status === 'approved')
                                        Approved At
                                    @else
                                        Rejected At
                                    @endif
                                </p>
                                <p class="fw-bold">{{ $leaveRequest->approved_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        @if($leaveRequest->status === 'rejected' && $leaveRequest->rejection_reason)
                        <p class="text-muted small mb-1">Rejection Reason</p>
                        <p>{{ $leaveRequest->rejection_reason }}</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Action Card --}}
        <div class="col-lg-4">
            @if($leaveRequest->status === 'pending')
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-light border-bottom py-3">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <form action="{{ route('admin.leave-requests.approve', $leaveRequest->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle me-2"></i>Approve Leave
                            </button>
                        </form>

                        <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle me-2"></i>Reject Leave
                        </button>
                    </div>

                    <hr>

                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Tip:</strong> Review all details before taking action. This cannot be undone easily.
                    </div>
                </div>
            </div>

            {{-- Reject Modal --}}
            <div class="modal fade" id="rejectModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header border-bottom">
                            <h5 class="modal-title">Reject Leave Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('admin.leave-requests.reject', $leaveRequest->id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="rejection_reason" class="form-label">Rejection Reason</label>
                                    <textarea class="form-control @error('rejection_reason') is-invalid @enderror"
                                              id="rejection_reason"
                                              name="rejection_reason"
                                              rows="4"
                                              placeholder="Please provide a reason for rejecting this leave request"
                                              required></textarea>
                                    @error('rejection_reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="modal-footer border-top">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Reject Request</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @else
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom py-3">
                    <h5 class="mb-0">Status</h5>
                </div>
                <div class="card-body text-center">
                    @if($leaveRequest->status === 'approved')
                        <div class="mb-3">
                            <i class="bi bi-check-circle text-success display-6"></i>
                        </div>
                        <p class="text-muted">This leave request has been approved.</p>
                        <p class="small text-muted">
                            Approved by {{ $leaveRequest->approver->name ?? 'System' }} on
                            {{ $leaveRequest->approved_at->format('M d, Y') }}
                        </p>
                    @else
                        <div class="mb-3">
                            <i class="bi bi-x-circle text-danger display-6"></i>
                        </div>
                        <p class="text-muted">This leave request has been rejected.</p>
                        <p class="small text-muted">
                            Rejected by {{ $leaveRequest->approver->name ?? 'System' }} on
                            {{ $leaveRequest->approved_at->format('M d, Y') }}
                        </p>
                    @endif
                </div>
            </div>

            <a href="{{ route('admin.leave-requests.index') }}" class="btn btn-outline-primary w-100 mt-3">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
            @endif

            {{-- Timeline Card --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light border-bottom py-3">
                    <h5 class="mb-0">Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline-item mb-3">
                        <div class="d-flex">
                            <div class="timeline-icon bg-primary bg-opacity-10 text-primary rounded-circle flex-shrink-0"
                                 style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-plus-circle"></i>
                            </div>
                            <div class="ms-3">
                                <p class="fw-bold mb-0">Requested</p>
                                <p class="text-muted small mb-0">{{ $leaveRequest->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($leaveRequest->status !== 'pending')
                    <div class="timeline-item">
                        <div class="d-flex">
                            <div class="timeline-icon @if($leaveRequest->status === 'approved') bg-success @else bg-danger @endif bg-opacity-10
                                            @if($leaveRequest->status === 'approved') text-success @else text-danger @endif rounded-circle flex-shrink-0"
                                 style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi @if($leaveRequest->status === 'approved') bi-check-circle @else bi-x-circle @endif"></i>
                            </div>
                            <div class="ms-3">
                                <p class="fw-bold mb-0">{{ ucfirst($leaveRequest->status) }}</p>
                                <p class="text-muted small mb-0">{{ $leaveRequest->approved_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline-item {
        padding-bottom: 20px;
        position: relative;
    }

    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 19px;
        top: 40px;
        width: 2px;
        height: 40px;
        background-color: #e9ecef;
    }
</style>
@endsection
