@extends('admin.layouts.app')

@section('page-title', 'Stock Adjustments')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold" style="color: var(--text-primary);">Stock Adjustments</h4>
            <p class="text-muted small mb-0">Review and approve stock adjustments from all branches</p>
        </div>
        <a href="{{ route('admin.inventory.adjustments.create') }}" class="btn btn-primary shadow-sm" style="background: #3D3B6B; border: none;">
            <i class="bi bi-plus-circle me-2"></i>Create Adjustment
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg);">
                <div class="card-body p-3 text-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                        <i class="bi bi-clipboard-data fs-4 text-primary"></i>
                    </div>
                    <h4 class="fw-bold mb-0" style="color: var(--text-primary);">{{ $summary['total'] }}</h4>
                    <small class="text-muted">Total Adjustments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg);">
                <div class="card-body p-3 text-center">
                    <div class="bg-warning bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                        <i class="bi bi-clock-history fs-4 text-warning"></i>
                    </div>
                    <h4 class="fw-bold mb-0" style="color: var(--text-primary);">{{ $summary['pending'] }}</h4>
                    <small class="text-muted">Pending Approval</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg);">
                <div class="card-body p-3 text-center">
                    <div class="bg-success bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                        <i class="bi bi-check-circle fs-4 text-success"></i>
                    </div>
                    <h4 class="fw-bold mb-0" style="color: var(--text-primary);">{{ $summary['approved'] }}</h4>
                    <small class="text-muted">Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg);">
                <div class="card-body p-3 text-center">
                    <div class="bg-danger bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                        <i class="bi bi-cash-stack fs-4 text-danger"></i>
                    </div>
                    <h4 class="fw-bold mb-0" style="color: var(--text-primary);">₱{{ number_format($summary['total_value_loss'], 2) }}</h4>
                    <small class="text-muted">Total Value Loss</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: var(--card-bg);">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.inventory.adjustments.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small" style="color: var(--text-primary);">Branch</label>
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small" style="color: var(--text-primary);">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small" style="color: var(--text-primary);">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="damaged" {{ request('type') === 'damaged' ? 'selected' : '' }}>Damaged</option>
                        <option value="expired" {{ request('type') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="lost" {{ request('type') === 'lost' ? 'selected' : '' }}>Lost</option>
                        <option value="theft" {{ request('type') === 'theft' ? 'selected' : '' }}>Theft</option>
                        <option value="spoilage" {{ request('type') === 'spoilage' ? 'selected' : '' }}>Spoilage</option>
                        <option value="found" {{ request('type') === 'found' ? 'selected' : '' }}>Found</option>
                        <option value="correction" {{ request('type') === 'correction' ? 'selected' : '' }}>Correction</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small" style="color: var(--text-primary);">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small" style="color: var(--text-primary);">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100" style="background: #3D3B6B; border: none;">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk Actions --}}
    @if($adjustments->where('status', 'pending')->count() > 0)
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: var(--card-bg);">
        <div class="card-body p-3">
            <form action="{{ route('admin.inventory.adjustments.bulk-approve') }}" method="POST" id="bulkApproveForm">
                @csrf
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <input type="checkbox" id="selectAll" class="form-check-input me-2">
                        <label for="selectAll" class="form-check-label" style="color: var(--text-primary);">
                            Select All Pending
                        </label>
                        <span class="badge bg-secondary ms-2" id="selectedCount">0 selected</span>
                    </div>
                    <button type="submit" class="btn btn-sm btn-success" id="bulkApproveBtn" disabled>
                        <i class="bi bi-check-circle me-1"></i>Approve Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Adjustments Table --}}
    <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg);">
        <div class="card-header border-bottom py-3" style="background-color: var(--card-bg);">
            <h6 class="mb-0 fw-bold" style="color: var(--text-primary);">
                <i class="bi bi-list-ul me-2" style="color: #3D3B6B;"></i>
                Adjustment Records
            </h6>
        </div>
        <div class="card-body p-0">
            @if($adjustments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: var(--bg-color);">
                            <tr>
                                <th style="color: var(--text-primary); width: 40px;">
                                    <input type="checkbox" class="form-check-input" disabled>
                                </th>
                                <th style="color: var(--text-primary);">Date</th>
                                <th style="color: var(--text-primary);">Branch</th>
                                <th style="color: var(--text-primary);">Item</th>
                                <th style="color: var(--text-primary);">Type</th>
                                <th style="color: var(--text-primary);">Qty</th>
                                <th style="color: var(--text-primary);">Value Loss</th>
                                <th style="color: var(--text-primary);">Reason</th>
                                <th style="color: var(--text-primary);">Status</th>
                                <th style="color: var(--text-primary);">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($adjustments as $adjustment)
                            <tr>
                                <td>
                                    @if($adjustment->status === 'pending')
                                        <input type="checkbox" name="adjustment_ids[]" value="{{ $adjustment->id }}" 
                                               class="form-check-input adjustment-checkbox" form="bulkApproveForm">
                                    @endif
                                </td>
                                <td style="color: var(--text-primary);">
                                    <small>{{ $adjustment->created_at->format('M d, Y') }}</small><br>
                                    <small class="text-muted">{{ $adjustment->created_at->format('h:i A') }}</small>
                                </td>
                                <td style="color: var(--text-primary);">
                                    <strong>{{ $adjustment->branch->name }}</strong>
                                </td>
                                <td style="color: var(--text-primary);">
                                    <strong>{{ $adjustment->item->name }}</strong><br>
                                    <small class="text-muted">{{ $adjustment->item->sku }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $adjustment->type === 'damaged' ? 'warning' : ($adjustment->type === 'expired' ? 'danger' : 'secondary') }}">
                                        {{ $adjustment->type_label }}
                                    </span>
                                </td>
                                <td style="color: var(--text-primary);">
                                    <strong class="text-danger">{{ $adjustment->quantity }}</strong>
                                </td>
                                <td style="color: var(--text-primary);">
                                    <strong class="text-danger">₱{{ number_format($adjustment->value_loss, 2) }}</strong>
                                </td>
                                <td style="color: var(--text-primary);">
                                    <small>{{ Str::limit($adjustment->reason, 30) }}</small>
                                </td>
                                <td>
                                    @if($adjustment->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($adjustment->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.inventory.adjustments.show', $adjustment) }}" 
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($adjustment->status === 'pending')
                                            <button type="button" class="btn btn-outline-success" 
                                                    onclick="approveAdjustment({{ $adjustment->id }})" title="Approve">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    data-bs-toggle="modal" data-bs-target="#rejectModal{{ $adjustment->id }}" title="Reject">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Reject Modal --}}
                            @if($adjustment->status === 'pending')
                            <div class="modal fade" id="rejectModal{{ $adjustment->id }}" tabindex="-1">
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
                                                    <textarea name="rejection_reason" class="form-control" rows="3" required 
                                                              placeholder="Explain why this adjustment is being rejected..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Reject Adjustment</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="p-3 border-top">
                    {{ $adjustments->links() }}
                </div>
            @else
                <div class="p-5 text-center">
                    <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.2;"></i>
                    <p class="text-muted mb-0 mt-2">No adjustments found</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Approve adjustment
function approveAdjustment(id) {
    if (confirm('Are you sure you want to approve this adjustment? Stock will be deducted immediately.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/inventory/adjustments/${id}/approve`;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Bulk selection
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.adjustment-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const bulkApproveBtn = document.getElementById('bulkApproveBtn');

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateSelectedCount();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });

        function updateSelectedCount() {
            const count = document.querySelectorAll('.adjustment-checkbox:checked').length;
            selectedCount.textContent = `${count} selected`;
            bulkApproveBtn.disabled = count === 0;
            
            if (count > 0 && count < checkboxes.length) {
                selectAll.indeterminate = true;
            } else {
                selectAll.indeterminate = false;
                selectAll.checked = count === checkboxes.length;
            }
        }
    }
});
</script>
@endpush
@endsection
