@extends('staff.layouts.staff')

@section('page-title', 'Payment Proof Details')

@section('content')
<div class="container-fluid px-4 py-4">
    
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Payment Proof Details</h4>
            <p class="text-muted mb-0">Review GCash payment proof for {{ $paymentProof->laundry->tracking_number }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('staff.laundries.show', $paymentProof->laundry) }}" class="btn btn-info">
                <i class="bi bi-box-seam me-1"></i>
                View Laundry
            </a>
            <a href="{{ route('staff.payments.verification.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to List
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Payment Proof Image --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 fw-semibold">Payment Proof Image</h6>
                </div>
                <div class="card-body text-center">
                    <img src="{{ $paymentProof->proof_image_url }}" 
                         alt="Payment Proof" 
                         class="img-fluid rounded border"
                         style="max-height: 500px; cursor: pointer;"
                         data-bs-toggle="modal" 
                         data-bs-target="#imageModal">
                    <p class="text-muted mt-2 small">Click to view full size</p>
                </div>
            </div>
        </div>

        {{-- Payment Details --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 fw-semibold">Payment Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted">Status</label>
                            <div>
                                @if($paymentProof->status === 'pending')
                                    <span class="badge bg-warning fs-6">Pending Verification</span>
                                @elseif($paymentProof->status === 'approved')
                                    <span class="badge bg-success fs-6">Approved</span>
                                @else
                                    <span class="badge bg-danger fs-6">Rejected</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted">Payment Method</label>
                            <div class="fw-semibold">{{ strtoupper($paymentProof->payment_method) }}</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted">Amount Paid</label>
                            <div class="fw-semibold">₱{{ number_format($paymentProof->amount, 2) }}</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted">Expected Amount</label>
                            <div class="fw-semibold">₱{{ number_format($paymentProof->laundry->total_amount, 2) }}</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted">Amount Match</label>
                            <div>
                                @if($paymentProof->amount == $paymentProof->laundry->total_amount)
                                    <span class="badge bg-success">Exact Match</span>
                                @else
                                    <span class="badge bg-warning">Amount Mismatch</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label text-muted">Reference Number</label>
                            <div class="fw-semibold">{{ $paymentProof->reference_number ?? 'Not provided' }}</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted">Submitted At</label>
                            <div class="fw-semibold">{{ $paymentProof->created_at->format('M d, Y H:i A') }}</div>
                        </div>
                        
                        @if($paymentProof->verified_at)
                        <div class="col-md-6">
                            <label class="form-label text-muted">Verified At</label>
                            <div class="fw-semibold">{{ $paymentProof->verified_at->format('M d, Y H:i A') }}</div>
                        </div>
                        @endif
                        
                        @if($paymentProof->verifiedBy)
                        <div class="col-12">
                            <label class="form-label text-muted">Verified By</label>
                            <div class="fw-semibold">{{ $paymentProof->verifiedBy->name }}</div>
                        </div>
                        @endif
                        
                        @if($paymentProof->admin_notes)
                        <div class="col-12">
                            <label class="form-label text-muted">Notes</label>
                            <div class="fw-semibold">{{ $paymentProof->admin_notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Laundry Details --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Related Laundry Order</h6>
                    <a href="{{ route('staff.laundries.show', $paymentProof->laundry) }}" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        View Full Details
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Tracking Number</label>
                            <div class="fw-semibold">{{ $paymentProof->laundry->tracking_number }}</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted">Service</label>
                            <div class="fw-semibold">{{ $paymentProof->laundry->service->name ?? 'N/A' }}</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted">Customer</label>
                            <div class="fw-semibold">{{ $paymentProof->laundry->customer->name }}</div>
                            <small class="text-muted">{{ $paymentProof->laundry->customer->phone }}</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted">Branch</label>
                            <div class="fw-semibold">{{ $paymentProof->laundry->branch->name }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            @if($paymentProof->status === 'pending')
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#approveModal">
                                <i class="bi bi-check-circle me-1"></i>
                                Approve Payment
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="bi bi-x-circle me-1"></i>
                                Reject Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Image Modal --}}
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Proof Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ $paymentProof->proof_image_url }}" alt="Payment Proof" class="img-fluid">
            </div>
        </div>
    </div>
</div>

{{-- Approve Modal --}}
@if($paymentProof->status === 'pending')
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('staff.payments.verification.approve', $paymentProof) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Approve Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this payment proof?</p>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add any notes about this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('staff.payments.verification.reject', $paymentProof) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Please provide a reason for rejecting this payment proof:</p>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Explain why this payment is being rejected..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
