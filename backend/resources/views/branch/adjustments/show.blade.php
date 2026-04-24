@extends('branch.layouts.app')

@section('page-title', 'Adjustment Details')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold" style="color: var(--text-primary);">Adjustment Details</h4>
            <p class="text-muted small mb-0">View adjustment information and status</p>
        </div>
        <a href="{{ route('branch.adjustments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="row">
        {{-- Main Details --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: var(--card-bg);">
                <div class="card-header border-bottom py-3" style="background-color: var(--card-bg);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold" style="color: var(--text-primary);">
                            <i class="bi bi-clipboard-data me-2" style="color: #3D3B6B;"></i>
                            Adjustment Information
                        </h6>
                        @if($adjustment->status === 'pending')
                            <span class="badge bg-warning">Pending Approval</span>
                        @elseif($adjustment->status === 'approved')
                            <span class="badge bg-success">Approved</span>
                        @else
                            <span class="badge bg-danger">Rejected</span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small text-muted">Item Name</label>
                            <p class="fw-bold mb-0" style="color: var(--text-primary);">{{ $adjustment->item->name }}</p>
                            @if($adjustment->item->brand)
                                <small class="text-muted">{{ $adjustment->item->brand }}</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">SKU</label>
                            <p class="fw-bold mb-0" style="color: var(--text-primary);">{{ $adjustment->item->sku }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted">Adjustment Type</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $adjustment->type === 'damaged' ? 'warning' : ($adjustment->type === 'expired' ? 'danger' : 'secondary') }}">
                                    {{ $adjustment->type_label }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted">Quantity Deducted</label>
                            <p class="fw-bold mb-0 text-danger" style="font-size: 1.2rem;">{{ $adjustment->quantity }} {{ $adjustment->item->distribution_unit }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted">Value Loss</label>
                            <p class="fw-bold mb-0 text-danger" style="font-size: 1.2rem;">₱{{ number_format($adjustment->value_loss, 2) }}</p>
                        </div>
                        <div class="col-12">
                            <label class="small text-muted">Reason</label>
                            <p class="mb-0" style="color: var(--text-primary);">{{ $adjustment->reason }}</p>
                        </div>
                        @if($adjustment->notes)
                        <div class="col-12">
                            <label class="small text-muted">Additional Notes</label>
                            <p class="mb-0" style="color: var(--text-primary);">{{ $adjustment->notes }}</p>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label class="small text-muted">Reported By</label>
                            <p class="mb-0" style="color: var(--text-primary);">{{ $adjustment->adjustedBy->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">Reported Date</label>
                            <p class="mb-0" style="color: var(--text-primary);">{{ $adjustment->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Photo Proof --}}
            @if($adjustment->photo_proof)
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: var(--card-bg);">
                <div class="card-header border-bottom py-3" style="background-color: var(--card-bg);">
                    <h6 class="mb-0 fw-bold" style="color: var(--text-primary);">
                        <i class="bi bi-camera me-2" style="color: #3D3B6B;"></i>
                        Photo Proof
                    </h6>
                </div>
                <div class="card-body p-4 text-center">
                    <img src="{{ asset('storage/' . $adjustment->photo_proof) }}" class="img-fluid rounded" style="max-height: 400px;">
                </div>
            </div>
            @endif

            {{-- Approval/Rejection Details --}}
            @if($adjustment->status !== 'pending')
            <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg);">
                <div class="card-header border-bottom py-3" style="background-color: var(--card-bg);">
                    <h6 class="mb-0 fw-bold" style="color: var(--text-primary);">
                        <i class="bi bi-{{ $adjustment->status === 'approved' ? 'check-circle' : 'x-circle' }} me-2" style="color: {{ $adjustment->status === 'approved' ? '#10B981' : '#EF4444' }};"></i>
                        {{ $adjustment->status === 'approved' ? 'Approval' : 'Rejection' }} Details
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small text-muted">{{ $adjustment->status === 'approved' ? 'Approved' : 'Rejected' }} By</label>
                            <p class="mb-0" style="color: var(--text-primary);">{{ $adjustment->approvedBy->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">{{ $adjustment->status === 'approved' ? 'Approved' : 'Rejected' }} Date</label>
                            <p class="mb-0" style="color: var(--text-primary);">{{ $adjustment->approved_at->format('M d, Y h:i A') }}</p>
                        </div>
                        @if($adjustment->status === 'rejected' && $adjustment->rejection_reason)
                        <div class="col-12">
                            <label class="small text-muted">Rejection Reason</label>
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                {{ $adjustment->rejection_reason }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-3" style="background-color: var(--card-bg);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color: var(--text-primary);">
                        <i class="bi bi-info-circle text-info me-2"></i>Status Information
                    </h6>
                    @if($adjustment->status === 'pending')
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            <strong>Pending Approval</strong>
                            <p class="small mb-0 mt-2">This adjustment is awaiting admin review. You'll be notified once it's approved or rejected.</p>
                        </div>
                    @elseif($adjustment->status === 'approved')
                        <div class="alert alert-success mb-0">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Approved</strong>
                            <p class="small mb-0 mt-2">This adjustment has been approved. The stock has been deducted and the loss has been recorded.</p>
                        </div>
                    @else
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-x-circle me-2"></i>
                            <strong>Rejected</strong>
                            <p class="small mb-0 mt-2">This adjustment was rejected by admin. Please review the rejection reason above.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color: var(--text-primary);">
                        <i class="bi bi-box-seam text-primary me-2"></i>Item Details
                    </h6>
                    <div class="small" style="color: var(--text-secondary);">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Category:</span>
                            <strong style="color: var(--text-primary);">{{ $adjustment->item->category->name ?? 'N/A' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Distribution Unit:</span>
                            <strong style="color: var(--text-primary);">{{ $adjustment->item->distribution_unit }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Unit Cost:</span>
                            <strong style="color: var(--text-primary);">₱{{ number_format($adjustment->item->unit_cost_price, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
