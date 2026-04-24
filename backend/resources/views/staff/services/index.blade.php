@extends('staff.layouts.staff')

@section('title', 'Services Management')
@section('page-title', 'Laundry Services')
@section('page-icon', 'bi-droplet')

@push('styles')
<style>
/* Services page - proper light/dark mode support */

/* Light mode (default) */
.services-page .card {
    background: var(--card-bg);
    border-color: var(--border-color);
    color: var(--text-primary);
    transition: all 0.3s ease;
}

.services-page .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}

.services-page .card-header {
    background: var(--bg-color);
    border-color: var(--border-color);
    color: var(--text-primary);
}

.services-page .table {
    background: var(--card-bg);
    color: var(--text-primary);
    border-color: var(--border-color);
}

.services-page .table thead th {
    background: var(--bg-color);
    color: var(--text-primary);
    border-color: var(--border-color);
}

.services-page .table tbody td {
    background: var(--card-bg);
    color: var(--text-primary);
    border-color: var(--border-color);
}

.services-page .table-hover tbody tr:hover td {
    background: var(--bg-color);
}

.services-page h4,
.services-page h5,
.services-page h6 {
    color: var(--text-primary);
}

.services-page .text-muted {
    color: var(--text-secondary) !important;
}

.services-page .fw-semibold {
    color: var(--text-primary);
}

.services-page .bg-light {
    background: var(--border-color) !important;
}

/* Dark mode overrides */
[data-theme="dark"] .services-page .card {
    background: var(--card-bg);
    border-color: var(--border-color);
}

[data-theme="dark"] .services-page .card-header {
    background: var(--bg-color);
    border-color: var(--border-color);
}

[data-theme="dark"] .services-page .table {
    background: transparent;
    color: var(--text-primary);
}

[data-theme="dark"] .services-page .table thead th {
    background: var(--bg-color);
    color: var(--text-primary);
}

[data-theme="dark"] .services-page .table tbody td {
    background: transparent;
    color: var(--text-primary);
}

[data-theme="dark"] .services-page .table-hover tbody tr:hover td {
    background: rgba(255,255,255,0.05);
}

[data-theme="dark"] .services-page .bg-light {
    background: var(--border-color) !important;
}

[data-theme="dark"] .services-page .text-primary {
    color: #a5b4fc !important;
}

[data-theme="dark"] .services-page .badge.bg-light {
    background: var(--border-color) !important;
    color: var(--text-primary) !important;
}

[data-theme="dark"] .services-page .badge.bg-light.text-dark {
    color: var(--text-primary) !important;
}

@media (max-width: 768px) {
    .services-page .input-group { max-width: 100% !important; }
    .services-page .table th i { font-size: 0.75rem; }
}

@media (max-width: 576px) {
    .services-page .form-check-label { font-size: 0.8rem; }
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4 services-page">
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
                            <th><i class="bi bi-image me-1"></i>Image</th>
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
                                @if($service->icon_path)
                                    <img src="{{ asset('storage/' . $service->icon_path) }}" 
                                         class="rounded" 
                                         style="width: 50px; height: 50px; object-fit: cover;"
                                         alt="{{ $service->name }}">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
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
