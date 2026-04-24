@extends('admin.layouts.app')

@section('title', 'Transaction Details — WashBox')
@section('page-title', 'Transaction Details')

@push('styles')
<!-- Google Fonts - Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<style>
/* Transaction Details - Inter Font Family */
body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.5;
    font-size: 0.813rem;
}

.container-xl,
.inventory-card,
.form-label,
.form-control,
.btn-inventory {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

h2, h3, h4, h5, h6 {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-weight: 700;
}

.badge {
    font-size: 0.65rem !important;
    padding: 0.25rem 0.5rem !important;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
}

.text-muted.small {
    font-size: 0.7rem !important;
    font-weight: 600;
}

.fw-semibold {
    font-size: 0.813rem !important;
}

/* Approval Actions Card */
.approval-actions {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 2px solid #fbbf24;
    border-radius: 0.5rem;
    padding: 1.5rem;
}

.approval-actions h5 {
    color: #92400e;
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

.approval-actions p {
    color: #78350f;
    font-size: 0.75rem;
    margin-bottom: 1rem;
}

.btn-approve {
    background: #10b981;
    color: white;
    border: none;
    padding: 0.625rem 1.25rem;
    border-radius: 0.375rem;
    font-size: 0.813rem;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-approve:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);
}

.btn-reject {
    background: #ef4444;
    color: white;
    border: none;
    padding: 0.625rem 1.25rem;
    border-radius: 0.375rem;
    font-size: 0.813rem;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-reject:hover {
    background: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2);
}

/* Rejection Modal */
.modal-content {
    font-family: 'Inter', sans-serif;
}

.modal-header {
    border-bottom: 1px solid #e5e7eb;
}

.modal-title {
    font-size: 1.125rem;
    font-weight: 700;
}

.modal-body label {
    font-size: 0.813rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.modal-body textarea {
    font-size: 0.813rem;
    font-family: 'Inter', sans-serif;
}

.modal-footer {
    border-top: 1px solid #e5e7eb;
}
</style>
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Transaction #{{ $transaction->transaction_number }}</h2>
            <p class="text-muted mb-0">{{ $transaction->transaction_date->format('F d, Y h:i A') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.finance.ledger.index') }}" class="btn-inventory btn-inventory-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Ledger
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- PENDING TRANSACTIONS APPROVAL FEATURE - DISABLED --}}
            {{-- Uncomment this section to enable approval workflow --}}
            {{--
            @if($transaction->status === 'pending')
            <div class="approval-actions mb-4">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <h5 class="mb-1">
                            <i class="bi bi-clock-history me-2"></i>Pending Approval
                        </h5>
                        <p class="mb-0">This transaction requires approval before it can be processed.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.finance.ledger.approve', $transaction) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-approve" onclick="return confirm('Are you sure you want to approve this transaction?')">
                                <i class="bi bi-check-circle me-1"></i>Approve
                            </button>
                        </form>
                        <button type="button" class="btn btn-reject" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle me-1"></i>Reject
                        </button>
                    </div>
                </div>
            </div>
            @endif
            --}}

            <div class="inventory-card mb-4">
                <div class="inventory-card-header">
                    <h5 class="mb-0">Transaction Information</h5>
                </div>
                <div class="inventory-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Transaction Number</label>
                            <p class="fw-semibold mb-0">{{ $transaction->transaction_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Date</label>
                            <p class="fw-semibold mb-0">{{ $transaction->transaction_date->format('M d, Y h:i A') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Type</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $transaction->type == 'income' ? 'success' : 'danger' }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Category</label>
                            <p class="mb-0">
                                <span class="badge bg-secondary">
                                    {{ str_replace('_', ' ', ucwords($transaction->category)) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Amount</label>
                            <h4 class="mb-0 text-{{ $transaction->type == 'income' ? 'success' : 'danger' }}">
                                ₱{{ number_format($transaction->amount, 2) }}
                            </h4>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $transaction->status == 'completed' ? 'success' : ($transaction->status == 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small">Description</label>
                            <p class="mb-0">{{ $transaction->description }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Branch</label>
                            <p class="mb-0">{{ $transaction->branch->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Payment Method</label>
                            <p class="mb-0">{{ $transaction->payment_method ? ucfirst($transaction->payment_method) : 'N/A' }}</p>
                        </div>
                        @if($transaction->approved_by)
                        <div class="col-md-6">
                            <label class="text-muted small">Approved By</label>
                            <p class="mb-0">{{ $transaction->approver->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Approved At</label>
                            <p class="mb-0">{{ $transaction->approved_at ? $transaction->approved_at->format('M d, Y h:i A') : 'N/A' }}</p>
                        </div>
                        @endif
                        @if($transaction->reference_number)
                        <div class="col-12">
                            <label class="text-muted small">Reference Number</label>
                            <p class="mb-0">{{ $transaction->reference_number }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($transaction->notes)
            <div class="inventory-card">
                <div class="inventory-card-header">
                    <h5 class="mb-0">Notes</h5>
                </div>
                <div class="inventory-card-body">
                    <p class="mb-0">{{ $transaction->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            @if($transaction->relatedRecord)
            <div class="inventory-card mb-4">
                <div class="inventory-card-header">
                    <h5 class="mb-0">Related Record</h5>
                </div>
                <div class="inventory-card-body">
                    <p class="text-muted small mb-2">Type</p>
                    <p class="fw-semibold mb-3">{{ class_basename($transaction->related_type) }}</p>
                    <p class="text-muted small mb-2">Record ID</p>
                    <p class="fw-semibold mb-0">#{{ $transaction->related_id }}</p>
                </div>
            </div>
            @endif

            <div class="inventory-card">
                <div class="inventory-card-header">
                    <h5 class="mb-0">Audit Trail</h5>
                </div>
                <div class="inventory-card-body">
                    <p class="text-muted small mb-2">Created By</p>
                    <p class="mb-3">{{ $transaction->creator->name ?? 'System' }}</p>
                    <p class="text-muted small mb-2">Created At</p>
                    <p class="mb-3">{{ $transaction->created_at->format('M d, Y h:i A') }}</p>
                    <p class="text-muted small mb-2">Last Updated</p>
                    <p class="mb-0">{{ $transaction->updated_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- PENDING TRANSACTIONS REJECTION MODAL - DISABLED --}}
{{-- Uncomment to enable rejection workflow --}}
{{--
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.finance.ledger.reject', $transaction) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">
                        <i class="bi bi-x-circle text-danger me-2"></i>Reject Transaction
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action will cancel the transaction and cannot be undone.
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reason" name="reason" rows="4" required placeholder="Please provide a reason for rejecting this transaction..."></textarea>
                        <div class="form-text">This reason will be recorded in the audit log.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i>Reject Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
--}}
@endsection
