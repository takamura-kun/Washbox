@extends('staff.layouts.staff')

@section('title', 'Services Management')
@section('page-title', 'Laundry Services')
@section('page-icon', 'bi-droplet')

@push('styles')
<style>
/* ── Dark mode: Services page ── */
[data-theme="dark"] .card { background: #1e293b !important; border-color: #334155 !important; }
[data-theme="dark"] .card-body { color: #f1f5f9; }
[data-theme="dark"] .card h3,.card h4,.card h5,.card h6 { color: #f1f5f9 !important; }
[data-theme="dark"] .card-header { background: #0f172a !important; border-color: #334155 !important; }
[data-theme="dark"] .card-header h6 { color: #f1f5f9 !important; }
[data-theme="dark"] h4.fw-bold { color: #f1f5f9 !important; }
[data-theme="dark"] .text-muted { color: #94a3b8 !important; }
[data-theme="dark"] .form-check-label { color: #f1f5f9 !important; }
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
[data-theme="dark"] .table-hover tbody tr:hover > td { background: rgba(255,255,255,0.04) !important; color: #f1f5f9 !important; }
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
[data-theme="dark"] .btn-outline-info { border-color: #3b82f6; color: #60a5fa; }
[data-theme="dark"] .btn-outline-info:hover { background: #3b82f6; color: #fff; }
/* empty state */
[data-theme="dark"] h5.text-muted { color: #64748b !important; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Laundry Services</h4>
            <p class="text-muted small mb-0">View all available laundry service packages</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="showInactiveServices" checked>
                <label class="form-check-label small fw-semibold" for="showInactiveServices">Show Inactive</label>
            </div>
            <div class="input-group" style="max-width: 250px;">
                <span class="input-group-text bg-light border-0">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control bg-light border-0" id="searchServices" placeholder="Search services...">
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
                            <i class="bi bi-droplet text-primary fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 small">Total Services</h6>
                            <h3 class="fw-bold mb-0">{{ $services->count() }}</h3>
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
                            <i class="bi bi-check-circle text-success fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 small">Active Services</h6>
                            <h3 class="fw-bold mb-0">{{ $services->where('is_active', true)->count() }}</h3>
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
                            <i class="bi bi-tag text-info fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 small">Service Types</h6>
                            <h3 class="fw-bold mb-0">{{ $services->groupBy('service_type')->count() }}</h3>
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
                            <i class="bi bi-arrow-repeat text-warning fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 small">Total Uses</h6>
                            <h3 class="fw-bold mb-0">{{ $services->sum('times_used') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Services Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-droplet me-2 text-primary"></i>All Services
            </h6>
        </div>
        <div class="card-body p-0">
            @if($services->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="servicesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">
                                <i class="bi bi-hash me-1"></i>ID
                            </th>
                            <th><i class="bi bi-droplet me-1"></i>Service Name</th>
                            <th><i class="bi bi-tag me-1"></i>Type</th>
                            <th><i class="bi bi-calculator me-1"></i>Pricing</th>
                            <th><i class="bi bi-clock me-1"></i>Turnaround</th>
                            <th><i class="bi bi-cash me-1"></i>Price</th>
                            <th><i class="bi bi-box me-1"></i>Weight Limit</th>
                            <th><i class="bi bi-flag me-1"></i>Status</th>
                            <th><i class="bi bi-arrow-repeat me-1"></i>Usage</th>
                            <th class="text-end pe-4"><i class="bi bi-gear me-1"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($services as $service)
                        <tr class="service-row" data-active="{{ $service->is_active ? '1' : '0' }}" data-id="{{ $service->id }}">
                            <td class="ps-4 fw-bold text-primary">#{{ $service->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $service->name }}</div>
                                @if($service->description)
                                    <div class="small text-muted">{{ Str::limit($service->description, 40) }}</div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $typeColors = [
                                        'full_service'  => 'primary',
                                        'self_service'  => 'success',
                                        'special_item'  => 'warning',
                                        'addon'         => 'info',
                                        'regular_clothes'=> 'primary',
                                    ];
                                    $typeLabels = [
                                        'full_service'   => 'Full Service',
                                        'self_service'   => 'Self Service',
                                        'special_item'   => 'Special Item',
                                        'addon'          => 'Add-on',
                                        'regular_clothes'=> 'Regular Clothes',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $typeColors[$service->service_type] ?? 'secondary' }}">
                                    {{ $typeLabels[$service->service_type] ?? ucfirst(str_replace('_', ' ', $service->service_type)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $service->pricing_type === 'per_piece' ? 'Per piece' : 'Per load' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $service->turnaround_time }}h</span>
                            </td>
                            <td class="fw-semibold text-primary">
                                @if($service->pricing_type === 'per_piece')
                                    ₱{{ number_format($service->price_per_piece, 2) }}/piece
                                @else
                                    ₱{{ number_format($service->price_per_load, 2) }}/load
                                @endif
                            </td>
                            <td>
                                @if($service->min_weight && $service->max_weight)
                                    {{ $service->min_weight }}–{{ $service->max_weight }}kg
                                @elseif($service->max_weight)
                                    up to {{ $service->max_weight }}kg
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $service->is_active ? 'success' : 'secondary' }}">
                                    {{ $service->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                @php $usageCount = $service->times_used ?? 0; @endphp
                                <span class="badge bg-{{ $usageCount > 0 ? 'info' : 'light' }} text-{{ $usageCount > 0 ? 'white' : 'dark' }} border">
                                    <i class="bi bi-{{ $usageCount > 0 ? 'check-circle-fill' : 'dash-circle' }} me-1"></i>
                                    {{ $usageCount }} {{ Str::plural('use', $usageCount) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('staff.services.show', $service) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <div class="py-4">
                    <i class="bi bi-droplet display-1 text-muted opacity-25"></i>
                    <h5 class="text-muted mb-2 mt-3">No services available</h5>
                    <p class="text-muted mb-0">Services will appear here once created by admin.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchServices');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const val = this.value.toLowerCase();
            document.querySelectorAll('#servicesTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
            });
        });
    }

    const showInactive = document.getElementById('showInactiveServices');
    if (showInactive) {
        showInactive.addEventListener('change', function() {
            document.querySelectorAll('.service-row').forEach(row => {
                row.style.display = (!this.checked && row.dataset.active === '0') ? 'none' : '';
            });
        });
        showInactive.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection
