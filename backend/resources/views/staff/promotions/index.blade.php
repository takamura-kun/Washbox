@extends('staff.layouts.staff')

@section('title', 'Promotions Management')
@section('page-title', 'Promotions & Discounts')

@push('styles')
<style>
/* ── Dark mode: Promotions page ── */
[data-theme="dark"] .card { background: #1e293b !important; border-color: #334155 !important; }
[data-theme="dark"] .card-body { color: #f1f5f9; }
[data-theme="dark"] .card h3.fw-bold,
[data-theme="dark"] .card h4.fw-bold { color: #f1f5f9 !important; }
[data-theme="dark"] .card-header { background: #0f172a !important; border-color: #334155 !important; }
[data-theme="dark"] .card-header h6,
[data-theme="dark"] .card-header p { color: #f1f5f9 !important; }
[data-theme="dark"] h4.fw-bold { color: #f1f5f9 !important; }
[data-theme="dark"] .text-muted { color: #94a3b8 !important; }
[data-theme="dark"] .form-check-label { color: #f1f5f9 !important; }
/* search */
[data-theme="dark"] .input-group-text { background: #334155 !important; border-color: #334155 !important; color: #94a3b8 !important; }
[data-theme="dark"] .form-control.bg-light { background: #1e293b !important; border-color: #334155 !important; color: #f1f5f9 !important; }
[data-theme="dark"] .form-control.bg-light::placeholder { color: #64748b; }
[data-theme="dark"] .form-control.bg-light:focus { background: #0f172a !important; border-color: #5452a0 !important; box-shadow: 0 0 0 0.25rem rgba(84,82,160,0.2) !important; color: #f1f5f9 !important; }
/* stat icon tints */
[data-theme="dark"] .bg-primary.bg-opacity-10 { background: rgba(99,102,241,0.15) !important; }
[data-theme="dark"] .bg-success.bg-opacity-10 { background: rgba(16,185,129,0.15) !important; }
[data-theme="dark"] .bg-info.bg-opacity-10    { background: rgba(59,130,246,0.15)  !important; }
[data-theme="dark"] .bg-warning.bg-opacity-10 { background: rgba(245,158,11,0.15)  !important; }
/* table */
[data-theme="dark"] .table { --bs-table-color: #f1f5f9; --bs-table-bg: transparent; --bs-table-border-color: #334155; --bs-table-hover-bg: rgba(255,255,255,0.04); color: #f1f5f9; border-color: #334155; }
[data-theme="dark"] thead.table-light th, [data-theme="dark"] .table-light th { background: #0f172a !important; color: #94a3b8 !important; border-color: #334155 !important; }
[data-theme="dark"] .table td { color: #f1f5f9 !important; border-color: #334155 !important; background: transparent !important; }
[data-theme="dark"] .table-hover tbody tr:hover > td { background: rgba(255,255,255,0.04) !important; }
[data-theme="dark"] .table td .fw-semibold { color: #f1f5f9; }
[data-theme="dark"] .table td .small.text-muted { color: #94a3b8 !important; }
[data-theme="dark"] .table td.fw-bold.text-primary { color: #a5b4fc !important; }
[data-theme="dark"] .text-primary { color: #a5b4fc !important; }
[data-theme="dark"] .text-info    { color: #60a5fa !important; }
[data-theme="dark"] .text-warning { color: #fbbf24 !important; }
[data-theme="dark"] .text-success { color: #4ade80 !important; }
/* badges */
[data-theme="dark"] .badge.bg-light { background: #334155 !important; color: #e2e8f0 !important; border-color: #475569 !important; }
[data-theme="dark"] .badge.bg-light.text-dark { color: #e2e8f0 !important; }
[data-theme="dark"] .badge.bg-success   { background: rgba(16,185,129,0.2) !important; color: #4ade80 !important; }
[data-theme="dark"] .badge.bg-secondary { background: rgba(100,116,139,0.2) !important; color: #94a3b8 !important; }
[data-theme="dark"] .badge.bg-primary   { background: rgba(99,102,241,0.2)  !important; color: #a5b4fc !important; }
[data-theme="dark"] .badge.bg-info      { background: rgba(59,130,246,0.2)  !important; color: #60a5fa !important; }
[data-theme="dark"] .badge.bg-warning   { background: rgba(245,158,11,0.2)  !important; color: #fbbf24 !important; }
/* buttons */
[data-theme="dark"] .btn-outline-info    { border-color: #3b82f6; color: #60a5fa; }
[data-theme="dark"] .btn-outline-info:hover { background: #3b82f6; color: #fff; }
[data-theme="dark"] .btn-outline-primary { border-color: #6366f1; color: #a5b4fc; }
[data-theme="dark"] .btn-outline-primary:hover { background: #6366f1; color: #fff; }
[data-theme="dark"] .btn-outline-secondary { border-color: #475569; color: #94a3b8; }
[data-theme="dark"] .btn-outline-secondary:hover { background: #334155; color: #f1f5f9; }
/* ── modal ── */
[data-theme="dark"] .modal-content { background: #1e293b !important; border-color: #334155 !important; }
[data-theme="dark"] .modal-body    { background: #1e293b !important; color: #f1f5f9 !important; }
[data-theme="dark"] .modal-footer  { background: #0f172a !important; border-color: #334155 !important; }
[data-theme="dark"] .modal-header.bg-info { background: #1d4ed8 !important; }
[data-theme="dark"] .modal-title   { color: #fff !important; }
[data-theme="dark"] .btn-close     { filter: invert(1) brightness(1.5); }
/* bg-light info panels inside modal */
[data-theme="dark"] .modal-body .bg-light { background: #0f172a !important; border: 1px solid #334155; }
[data-theme="dark"] .modal-body .bg-light small.text-muted { color: #64748b !important; }
[data-theme="dark"] .modal-body .bg-light h5,
[data-theme="dark"] .modal-body .bg-light strong,
[data-theme="dark"] .modal-body .bg-light p { color: #f1f5f9 !important; }
[data-theme="dark"] .modal-body code { background: #334155; color: #a5b4fc !important; padding: 0.1rem 0.4rem; border-radius: 4px; }
/* discount value */
[data-theme="dark"] #viewPromotionDiscountValue.text-info    { color: #60a5fa !important; }
[data-theme="dark"] #viewPromotionDiscountValue.text-warning { color: #fbbf24 !important; }
[data-theme="dark"] #viewPromotionDisplayPrice { color: #94a3b8 !important; }
/* empty state */
[data-theme="dark"] h5.text-muted { color: #64748b !important; }

/* Card hover */
.card { border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s; }
.card:hover { transform: translateY(-2px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important; }
.btn-group-sm .btn { border-radius: 0.375rem !important; margin-left: 0.1rem; padding: 0.25rem 0.5rem; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Promotions & Discounts</h4>
            <p class="text-muted small mb-0">View and manage active promotions for laundry services</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="showInactivePromotions" checked>
                <label class="form-check-label small fw-semibold" for="showInactivePromotions">Show Inactive</label>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-megaphone text-primary fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 small">Total Promotions</h6>
                            <h3 class="fw-bold mb-0">{{ $promotions->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-tag text-success fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 small">Active Promotions</h6>
                            <h3 class="fw-bold mb-0">{{ $promotions->where('is_active', true)->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-percent text-info fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 small">Percentage Off</h6>
                            <h3 class="fw-bold mb-0">{{ $promotions->where('discount_type', 'percentage')->where('is_active', true)->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-coin text-warning fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 small">Fixed Amount</h6>
                            <h3 class="fw-bold mb-0">{{ $promotions->where('discount_type', 'fixed')->where('is_active', true)->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Promotions Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-1">
                        <i class="bi bi-megaphone me-2 text-primary"></i>All Promotions
                    </h6>
                    <p class="text-muted small mb-0">List of all promotions created by admin</p>
                </div>
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text bg-light border-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control bg-light border-0" id="searchPromotions" placeholder="Search promotions...">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($promotions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="promotionsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4"><i class="bi bi-hash me-1"></i>ID</th>
                            <th><i class="bi bi-megaphone me-1"></i>Promotion Name</th>
                            <th><i class="bi bi-card-text me-1"></i>Description</th>
                            <th><i class="bi bi-percent me-1"></i>Discount Type</th>
                            <th><i class="bi bi-cash me-1"></i>Value</th>
                            <th><i class="bi bi-grid me-1"></i>Application</th>
                            <th><i class="bi bi-calendar3 me-1"></i>Valid Period</th>
                            <th><i class="bi bi-flag me-1"></i>Status</th>
                            <th><i class="bi bi-arrow-repeat me-1"></i>Usage</th>
                            <th class="text-end pe-4"><i class="bi bi-gear me-1"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($promotions as $promotion)
                        <tr class="promotion-row" data-active="{{ $promotion->is_active ? '1' : '0' }}" data-id="{{ $promotion->id }}">
                            <td class="ps-4 fw-bold text-primary">#{{ $promotion->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $promotion->name }}</div>
                                <small class="text-muted">
                                    Code: <span class="badge bg-light text-dark border">{{ $promotion->code ?? 'N/A' }}</span>
                                </small>
                            </td>
                            <td>
                                @if($promotion->description)
                                    <div class="small text-muted">{{ Str::limit($promotion->description, 60) }}</div>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $promotion->discount_type === 'percentage' ? 'info' : 'warning' }}">
                                    {{ ucfirst($promotion->discount_type) }}
                                </span>
                            </td>
                            <td class="fw-semibold">
                                @if($promotion->discount_type === 'percentage')
                                    <span class="text-info">{{ $promotion->discount_value }}% OFF</span>
                                @else
                                    <span class="text-warning">₱{{ number_format($promotion->discount_value, 2) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($promotion->application_type === 'per_load_override')
                                    <span class="badge bg-primary">Per Load Override</span>
                                    <div class="small text-muted mt-1">₱{{ number_format($promotion->display_price, 2) }}/load</div>
                                @elseif($promotion->application_type === 'all_services')
                                    <span class="badge bg-success">All Services</span>
                                @elseif($promotion->application_type === 'specific_services')
                                    <span class="badge bg-info">Specific Services</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">
                                    <div><strong>From:</strong> {{ $promotion->start_date ? $promotion->start_date->format('M d, Y') : '—' }}</div>
                                    <div><strong>To:</strong> {{ $promotion->end_date ? $promotion->end_date->format('M d, Y') : '—' }}</div>
                                    @if($promotion->end_date && $promotion->end_date->isPast())
                                        <span class="badge bg-secondary mt-1">Expired</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $promotion->is_active ? 'success' : 'secondary' }}">
                                    {{ $promotion->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-arrow-repeat me-1"></i>{{ $promotion->times_used ?? 0 }} uses
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info view-promotion" title="View Details"
                                            data-bs-toggle="modal" data-bs-target="#viewPromotionModal"
                                            data-promotion-id="{{ $promotion->id }}"
                                            data-promotion-name="{{ $promotion->name }}"
                                            data-promotion-code="{{ $promotion->code ?? 'N/A' }}"
                                            data-promotion-description="{{ $promotion->description }}"
                                            data-promotion-discount-type="{{ $promotion->discount_type }}"
                                            data-promotion-discount-value="{{ $promotion->discount_value }}"
                                            data-promotion-application-type="{{ $promotion->application_type }}"
                                            data-promotion-display-price="{{ $promotion->display_price }}"
                                            data-promotion-start-date="{{ $promotion->start_date ? $promotion->start_date->format('F d, Y') : 'Not set' }}"
                                            data-promotion-end-date="{{ $promotion->end_date ? $promotion->end_date->format('F d, Y') : 'Not set' }}"
                                            data-promotion-is-active="{{ $promotion->is_active ? '1' : '0' }}"
                                            data-promotion-created="{{ $promotion->created_at->format('F d, Y') }}"
                                            data-promotion-usage="{{ $promotion->times_used ?? 0 }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('staff.promotions.show', $promotion) }}" class="btn btn-outline-primary" title="View Details">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <div class="py-4">
                    <i class="bi bi-megaphone display-1 text-muted opacity-25"></i>
                    <h5 class="text-muted mb-2 mt-3">No promotions yet</h5>
                    <p class="text-muted mb-4">Admin hasn't created any promotions yet. Check back later!</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- View Promotion Modal --}}
<div class="modal fade" id="viewPromotionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-eye me-2"></i>Promotion Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="rounded-3 mx-auto d-flex align-items-center justify-content-center mb-3"
                        style="width:100px;height:100px;background:linear-gradient(135deg,#0d6efd 0%,#0a58ca 100%);">
                        <i class="bi bi-megaphone text-white" style="font-size:3rem;"></i>
                    </div>
                    <span id="viewPromotionStatus" class="badge mb-2" style="font-size:0.9rem;padding:0.5rem 1rem;background:#198754;">
                        <i class="bi bi-check-circle me-1"></i>
                        <span id="viewPromotionStatusText">ACTIVE</span>
                    </span>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Promotion Name</small>
                            <h5 id="viewPromotionName" class="fw-bold mb-0">-</h5>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Promotion Code</small>
                            <code id="viewPromotionCode" class="fw-semibold">-</code>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Description</small>
                            <p id="viewPromotionDescription" class="mb-0">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Discount Type</small>
                            <div id="viewPromotionDiscountType" class="mb-0">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Discount Value</small>
                            <strong id="viewPromotionDiscountValue" class="d-block fs-5">-</strong>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Application Type</small>
                            <div id="viewPromotionApplicationType" class="mb-0">-</div>
                            <div id="viewPromotionDisplayPrice" class="small text-muted mt-1" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Valid From</small>
                            <strong id="viewPromotionStartDate" class="d-block">-</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Valid Until</small>
                            <strong id="viewPromotionEndDate" class="d-block">-</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Usage Count</small>
                            <strong id="viewPromotionUsage" class="d-block fs-5">-</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Created</small>
                            <strong id="viewPromotionCreated" class="d-block">-</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchPromotions');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const val = this.value.toLowerCase();
            document.querySelectorAll('#promotionsTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
            });
        });
    }

    const showInactive = document.getElementById('showInactivePromotions');
    if (showInactive) {
        showInactive.addEventListener('change', function() {
            document.querySelectorAll('.promotion-row').forEach(row => {
                row.style.display = (!this.checked && row.dataset.active === '0') ? 'none' : '';
            });
        });
        showInactive.dispatchEvent(new Event('change'));
    }

    const viewPromotionModal = document.getElementById('viewPromotionModal');
    if (viewPromotionModal) {
        viewPromotionModal.addEventListener('show.bs.modal', function(event) {
            const b = event.relatedTarget;
            const discountType  = b.getAttribute('data-promotion-discount-type');
            const discountValue = b.getAttribute('data-promotion-discount-value');
            const applicationType = b.getAttribute('data-promotion-application-type');
            const displayPrice  = b.getAttribute('data-promotion-display-price');
            const isActive      = b.getAttribute('data-promotion-is-active') === '1';
            const usage         = b.getAttribute('data-promotion-usage') || '0';

            document.getElementById('viewPromotionName').textContent        = b.getAttribute('data-promotion-name');
            document.getElementById('viewPromotionCode').textContent        = b.getAttribute('data-promotion-code');
            document.getElementById('viewPromotionDescription').textContent = b.getAttribute('data-promotion-description') || 'No description provided';
            document.getElementById('viewPromotionStartDate').textContent   = b.getAttribute('data-promotion-start-date');
            document.getElementById('viewPromotionEndDate').textContent     = b.getAttribute('data-promotion-end-date');
            document.getElementById('viewPromotionUsage').textContent       = `${usage} time${usage != 1 ? 's' : ''}`;
            document.getElementById('viewPromotionCreated').textContent     = b.getAttribute('data-promotion-created');

            document.getElementById('viewPromotionDiscountType').innerHTML =
                `<span class="badge bg-${discountType === 'percentage' ? 'info' : 'warning'}">${discountType.charAt(0).toUpperCase() + discountType.slice(1)}</span>`;

            const dvEl = document.getElementById('viewPromotionDiscountValue');
            dvEl.textContent  = discountType === 'percentage' ? `${discountValue}% OFF` : `₱${parseFloat(discountValue).toFixed(2)} OFF`;
            dvEl.className    = `d-block fs-5 text-${discountType === 'percentage' ? 'info' : 'warning'}`;

            const appEl   = document.getElementById('viewPromotionApplicationType');
            const priceEl = document.getElementById('viewPromotionDisplayPrice');
            if (applicationType === 'per_load_override') {
                appEl.innerHTML     = '<span class="badge bg-primary">Per Load Override</span>';
                priceEl.style.display = 'block';
                priceEl.textContent = `Display Price: ₱${parseFloat(displayPrice).toFixed(2)}/load`;
            } else if (applicationType === 'all_services') {
                appEl.innerHTML     = '<span class="badge bg-success">All Services</span>';
                priceEl.style.display = 'none';
            } else {
                appEl.innerHTML     = '<span class="badge bg-info">Specific Services</span>';
                priceEl.style.display = 'none';
            }

            const statusEl   = document.getElementById('viewPromotionStatus');
            const statusText = document.getElementById('viewPromotionStatusText');
            statusEl.style.background = isActive ? '#198754' : '#6c757d';
            statusText.textContent    = isActive ? 'ACTIVE' : 'INACTIVE';
        });
    }
});
</script>
@endpush
@endsection
