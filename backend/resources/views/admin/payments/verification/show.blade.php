@extends('admin.layouts.app')

@section('title', 'Payment Proof Details')
@section('page-title', 'Payment Proof Details')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/payment-verification.css') }}">
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    
    {{-- Header --}}
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="page-title">Payment Proof Details</h4>
            <p class="page-subtitle">Review GCash payment proof for {{ $paymentProof->laundry->tracking_number }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.laundries.show', $paymentProof->laundry) }}" class="btn btn-info">
                <i class="bi bi-box-seam me-1"></i>
                View Laundry
            </a>
            <a href="{{ route('admin.payments.verification.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to List
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Payment Proof Image --}}
        <div class="col-lg-6">
            <div class="detail-card">
                <div class="detail-card-header">
                    <h6>Payment Proof Image</h6>
                </div>
                <div class="proof-image-container">
                    <img src="{{ $paymentProof->proof_image_url }}" 
                         alt="Payment Proof" 
                         class="proof-image img-fluid"
                         data-bs-toggle="modal" 
                         data-bs-target="#imageModal">
                    <p class="image-caption">Click to view full size</p>
                </div>
            </div>
        </div>

        {{-- Payment Details --}}
        <div class="col-lg-6">
            <div class="detail-card">
                <div class="detail-card-header">
                    <h6>Payment Information</h6>
                </div>
                <div class="detail-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="detail-row">
                                <div class="detail-label">Status</div>
                                <div>
                                    @if($paymentProof->status === 'pending')
                                        <span class="status-badge pending">Pending Verification</span>
                                    @elseif($paymentProof->status === 'approved')
                                        <span class="status-badge approved">Approved</span>
                                    @else
                                        <span class="status-badge rejected">Rejected</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">Payment Method</div>
                                <div class="detail-value">{{ strtoupper($paymentProof->payment_method) }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">Amount Paid</div>
                                <div class="detail-value">₱{{ number_format($paymentProof->amount, 2) }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">Expected Amount</div>
                                <div class="detail-value">₱{{ number_format($paymentProof->laundry->total_amount, 2) }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">Amount Match</div>
                                <div>
                                    @if($paymentProof->amount == $paymentProof->laundry->total_amount)
                                        <span class="amount-match">Exact Match</span>
                                    @else
                                        <span class="amount-mismatch">Amount Mismatch</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="detail-row">
                                <div class="detail-label">Reference Number</div>
                                <div class="detail-value">{{ $paymentProof->reference_number ?? 'Not provided' }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">Submitted At</div>
                                <div class="detail-value">{{ $paymentProof->created_at->format('M d, Y H:i A') }}</div>
                            </div>
                        </div>
                        
                        @if($paymentProof->verified_at)
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">Verified At</div>
                                <div class="detail-value">{{ $paymentProof->verified_at->format('M d, Y H:i A') }}</div>
                            </div>
                        </div>
                        @endif
                        
                        @if($paymentProof->verifiedBy)
                        <div class="col-12">
                            <div class="detail-row">
                                <div class="detail-label">Verified By</div>
                                <div class="detail-value">{{ $paymentProof->verifiedBy->name }}</div>
                            </div>
                        </div>
                        @endif
                        
                        @if($paymentProof->admin_notes)
                        <div class="col-12">
                            <div class="detail-row">
                                <div class="detail-label">Admin Notes</div>
                                <div class="detail-value">{{ $paymentProof->admin_notes }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Laundry Details --}}
            <div class="detail-card">
                <div class="detail-card-header d-flex justify-content-between align-items-center">
                    <h6>Related Laundry Order</h6>
                    <a href="{{ route('admin.laundries.show', $paymentProof->laundry) }}" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        View Full Details
                    </a>
                </div>
                <div class="detail-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">Tracking Number</div>
                                <div class="detail-value">{{ $paymentProof->laundry->tracking_number }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">Service</div>
                                <div class="detail-value">{{ $paymentProof->laundry->service->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">Customer</div>
                                <div class="detail-value">{{ $paymentProof->laundry->customer->name }}</div>
                                <div class="detail-sub">{{ $paymentProof->laundry->customer->phone }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">Branch</div>
                                <div class="detail-value">{{ $paymentProof->laundry->branch->name }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            @if($paymentProof->status === 'pending')
            <div class="action-card">
                <div class="row g-3">
                    <div class="col-md-6">
                        <button type="button" class="action-btn-large action-btn-approve" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="bi bi-check-circle me-1"></i>
                            Approve Payment
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="action-btn-large action-btn-reject" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle me-1"></i>
                            Reject Payment
                        </button>
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
            <form method="POST" action="{{ route('admin.payments.verification.approve', $paymentProof) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Approve Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this payment proof?</p>
                    <div class="mb-3">
                        <label class="form-label">Admin Notes (Optional)</label>
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
            <form method="POST" action="{{ route('admin.payments.verification.reject', $paymentProof) }}">
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