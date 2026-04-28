@extends('admin.layouts.app')

@section('page-title', 'Adjustment Details')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold" style="color: var(--text-primary);">Adjustment Details #{{ $adjustment->id }}</h4>
            <p class="text-muted small mb-0">Review adjustment information and take action</p>
        </div>
        <div class="d-flex gap-2">
            @if($adjustment->status === 'pending')
                <button type="button" class="btn btn-success shadow-sm" onclick="approveAdjustment()">
                    <i class="bi bi-check-circle me-2"></i>Approve
                </button>
                <button type="button" class="btn btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x-circle me-2"></i>Reject
                </button>
            @endif
            <a href="{{ route('admin.inventory.adjustments.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Main Details --}}
        <div class="col-lg-8">
            {{-- Status Alert --}}
            @if($adjustment->status === 'pending')
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-clock-history me-2"></i>
                    <strong>Pending Approval</strong> - This adjustment is awaiting your review. Please approve or reject it.
                </div>
            @elseif($adjustment->status === 'approved')
                <div class="alert alert-success mb-4">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Approved</strong> - This adjustment has been approved and stock has been updated.
                </div>
            @else
                <div class="alert alert-danger mb-4">
                    <i class="bi bi-x-circle me-2"></i>
                    <strong>Rejected</strong> - This adjustment was rejected. No stock changes were made.
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: var(--card-bg);">
                <div class="card-header border-bottom py-3" style="background-color: var(--card-bg);">
                    <h6 class="mb-0 fw-bold" style="color: var(--text-primary);">
                        <i class="bi bi-clipboard-data me-2" style="color: #3D3B6B;"></i>
                        Adjustment Information
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small text-muted">Branch</label>
                            <p class="fw-bold mb-0" style="color: var(--text-primary);">{{ $adjustment->branch->name }}</p>
                            <small class="text-muted">{{ $adjustment->branch->address }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">Item Name</label>
                            <p class="fw-bold mb-0" style="color: var(--text-primary);">{{ $adjustment->item->name }}</p>
                            @if($adjustment->item->brand)
                                <small class="text-muted">{{ $adjustment->item->brand }}</small>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted">SKU</label>
                            <p class="mb-0" style="color: var(--text-primary);">{{ $adjustment->item->sku }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted">Category</label>
                            <p class="mb-0" style="color: var(--text-primary);">{{ $adjustment->item->category->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted">Adjustment Type</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $adjustment->type === 'damaged' ? 'warning' : ($adjustment->type === 'expired' ? 'danger' : 'secondary') }}">
                                    {{ $adjustment->type_label }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted">Status</label>
                            <p class="mb-0">
                                @if($adjustment->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($adjustment->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">Quantity Adjustment</label>
                            <p class="fw-bold mb-0" style="font-size: 1.5rem; color: {{ $adjustment->quantity < 0 ? '#EF4444' : '#10B981' }};">
                                {{ $adjustment->quantity > 0 ? '+' : '' }}{{ $adjustment->quantity }} {{ $adjustment->item->distribution_unit }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">Value Loss</label>
                            <p class="fw-bold mb-0 text-danger" style="font-size: 1.5rem;">₱{{ number_format($adjustment->value_loss, 2) }}</p>
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
                            <small class="text-muted">{{ $adjustment->adjustedBy->email }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">Reported Date</label>
                            <p class="mb-0" style="color: var(--text-primary);">{{ $adjustment->created_at->format('M d, Y h:i A') }}</p>
                            <small class="text-muted">{{ $adjustment->created_at->diffForHumans() }}</small>
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
                    <img src="{{ asset('storage/' . $adjustment->photo_proof) }}" class="img-fluid rounded" style="max-height: 500px; cursor: pointer;" 
                         onclick="window.open(this.src, '_blank')">
                    <p class="small text-muted mt-2 mb-0">Click image to view full size</p>
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
                            <small class="text-muted">{{ $adjustment->approvedBy->email }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">{{ $adjustment->status === 'approved' ? 'Approved' : 'Rejected' }} Date</label>
                            <p class="mb-0" style="color: var(--text-primary);">{{ $adjustment->approved_at->format('M d, Y h:i A') }}</p>
                            <small class="text-muted">{{ $adjustment->approved_at->diffForHumans() }}</small>
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
                        <i class="bi bi-box-seam text-primary me-2"></i>Current Stock Info
                    </h6>
                    @php
                        $branchStock = \App\Models\BranchStock::where('branch_id', $adjustment->branch_id)
                            ->where('inventory_item_id', $adjustment->inventory_item_id)
                            ->first();
                    @endphp
                    @if($branchStock)
                        <div class="small" style="color: var(--text-secondary);">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Current Stock:</span>
                                <strong style="color: var(--text-primary);">{{ $branchStock->current_stock }} {{ $adjustment->item->distribution_unit }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Reorder Point:</span>
                                <strong style="color: var(--text-primary);">{{ $branchStock->reorder_point }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Max Level:</span>
                                <strong style="color: var(--text-primary);">{{ $branchStock->max_stock_level }}</strong>
                            </div>
                        </div>
                    @else
                        <p class="small text-muted mb-0">No stock information available</p>
                    @endif
                </div>
            </div>

            @if($adjustment->status === 'pending')
            <div class="card border-0 shadow-sm rounded-4 mb-3" style="background-color: var(--card-bg);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color: var(--text-primary);">
                        <i class="bi bi-info-circle text-info me-2"></i>Review Checklist
                    </h6>
                    <ul class="small mb-0" style="color: var(--text-secondary);">
                        <li class="mb-2">✓ Verify the reason is valid</li>
                        <li class="mb-2">✓ Check photo proof if available</li>
                        <li class="mb-2">✓ Confirm quantity is reasonable</li>
                        <li class="mb-2">✓ Review value loss amount</li>
                        <li class="mb-0">✓ Ensure no duplicate requests</li>
                    </ul>
                </div>
            </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color: var(--text-primary);">
                        <i class="bi bi-calculator text-warning me-2"></i>Financial Impact
                    </h6>
                    <div class="small" style="color: var(--text-secondary);">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Unit Cost:</span>
                            <strong style="color: var(--text-primary);">₱{{ number_format($adjustment->item->unit_cost_price, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Quantity:</span>
                            <strong style="color: var(--text-primary);">{{ abs($adjustment->quantity) }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span><strong>Total Loss:</strong></span>
                            <strong class="text-danger">₱{{ number_format($adjustment->value_loss, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--card-bg);">
            <form action="{{ route('admin.inventory.adjustments.reject', $adjustment) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" style="color: var(--text-primary);">Reject Adjustment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p style="color: var(--text-primary);">Are you sure you want to reject this adjustment?</p>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-primary);">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required 
                                  placeholder="Explain why this adjustment is being rejected..."></textarea>
                        <small class="text-muted">This reason will be visible to the branch staff</small>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i>Reject Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function approveAdjustment() {
    if (confirm('Are you sure you want to approve this adjustment?\n\nStock will be updated immediately:\n- Quantity: {{ $adjustment->quantity }} {{ $adjustment->item->distribution_unit }}\n- Value Loss: ₱{{ number_format($adjustment->value_loss, 2) }}')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('admin.inventory.adjustments.approve', $adjustment) }}';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection
