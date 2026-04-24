@extends('staff.layouts.staff')

@section('title', 'Add-ons Management')
@section('page-title', 'Laundry Add-ons')
@section('page-icon', 'bi-plus-circle')

@push('styles')
<style>
/* Add-ons page - proper light/dark mode support */

/* Light mode (default) */
.addons-page .card {
    background: var(--card-bg);
    border-color: var(--border-color);
    color: var(--text-primary);
    transition: all 0.3s ease;
}

.addons-page .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}

.addons-page .card-header {
    background: var(--bg-color);
    border-color: var(--border-color);
    color: var(--text-primary);
}

.addons-page .table {
    background: var(--card-bg);
    color: var(--text-primary);
    border-color: var(--border-color);
}

.addons-page .table thead th {
    background: var(--bg-color);
    color: var(--text-primary);
    border-color: var(--border-color);
}

.addons-page .table tbody td {
    background: var(--card-bg);
    color: var(--text-primary);
    border-color: var(--border-color);
}

.addons-page .table-hover tbody tr:hover td {
    background: var(--bg-color);
}

.addons-page h4,
.addons-page h5,
.addons-page h6 {
    color: var(--text-primary);
}

.addons-page .text-muted {
    color: var(--text-secondary) !important;
}

.addons-page .fw-semibold {
    color: var(--text-primary);
}

.addons-page .bg-light {
    background: var(--border-color) !important;
}

/* Dark mode overrides */
[data-theme="dark"] .addons-page .card {
    background: var(--card-bg);
    border-color: var(--border-color);
}

[data-theme="dark"] .addons-page .card-header {
    background: var(--bg-color);
    border-color: var(--border-color);
}

[data-theme="dark"] .addons-page .table {
    background: transparent;
    color: var(--text-primary);
}

[data-theme="dark"] .addons-page .table thead th {
    background: var(--bg-color);
    color: var(--text-primary);
}

[data-theme="dark"] .addons-page .table tbody td {
    background: transparent;
    color: var(--text-primary);
}

[data-theme="dark"] .addons-page .table-hover tbody tr:hover td {
    background: rgba(255,255,255,0.05);
}

[data-theme="dark"] .addons-page .bg-light {
    background: var(--border-color) !important;
}

[data-theme="dark"] .addons-page .text-success {
    color: #4ade80 !important;
}

[data-theme="dark"] .addons-page .badge.bg-light {
    background: var(--border-color) !important;
    color: var(--text-primary) !important;
}

[data-theme="dark"] .addons-page .badge.bg-light.text-dark {
    color: var(--text-primary) !important;
}

@media (max-width: 768px) {
    .addons-page .input-group { max-width: 100% !important; }
    .addons-page .table th i { font-size: 0.75rem; }
}

@media (max-width: 576px) {
    .addons-page .form-check-label { font-size: 0.8rem; }
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4 addons-page">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Laundry Add-ons</h4>
            <p class="text-muted small mb-0">View all available add-on services</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="showInactiveAddons" checked>
                <label class="form-check-label small fw-semibold" for="showInactiveAddons">Show Inactive</label>
            </div>
            <div class="input-group" style="max-width: 250px;">
                <span class="input-group-text bg-light border-0">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control bg-light border-0" id="searchAddons" placeholder="Search add-ons...">
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-plus-circle text-success fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 small">Total Add-ons</h6>
                            <h3 class="fw-bold mb-0">{{ $addons->count() }}</h3>
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
                            <h6 class="text-muted mb-1 small">Active Add-ons</h6>
                            <h3 class="fw-bold mb-0">{{ $addons->where('is_active', true)->count() }}</h3>
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
                            <h6 class="text-muted mb-1 small">Price Range</h6>
                            <h3 class="fw-bold mb-0">
                                ₱{{ number_format($addons->min('price'), 0) }}–₱{{ number_format($addons->max('price'), 0) }}
                            </h3>
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
                            <h3 class="fw-bold mb-0">{{ $addons->sum('times_used') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add-ons Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-plus-circle me-2 text-success"></i>All Add-ons
            </h6>
        </div>
        <div class="card-body p-0">
            @if($addons->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="addonsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4"><i class="bi bi-hash me-1"></i>ID</th>
                            <th><i class="bi bi-image me-1"></i>Image</th>
                            <th><i class="bi bi-plus-circle me-1"></i>Add-on Name</th>
                            <th><i class="bi bi-link-45deg me-1"></i>Slug</th>
                            <th><i class="bi bi-card-text me-1"></i>Description</th>
                            <th><i class="bi bi-cash me-1"></i>Price</th>
                            <th><i class="bi bi-flag me-1"></i>Status</th>
                            <th><i class="bi bi-arrow-repeat me-1"></i>Usage</th>
                            <th class="text-end pe-4"><i class="bi bi-gear me-1"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($addons as $addon)
                        <tr class="addon-row" data-active="{{ $addon->is_active ? '1' : '0' }}" data-id="{{ $addon->id }}">
                            <td class="ps-4 fw-bold text-success">#{{ $addon->id }}</td>
                            <td>
                                @if($addon->image)
                                    <img src="{{ asset('storage/addons/' . $addon->image) }}" 
                                         class="rounded" 
                                         style="width: 50px; height: 50px; object-fit: cover;"
                                         alt="{{ $addon->name }}">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $addon->name }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $addon->slug }}</span>
                            </td>
                            <td>
                                @if($addon->description)
                                    <div class="small text-muted">{{ Str::limit($addon->description, 60) }}</div>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="fw-semibold text-success">
                                ₱{{ number_format($addon->price, 2) }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $addon->is_active ? 'success' : 'secondary' }}">
                                    {{ $addon->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                @php $usageCount = $addon->times_used ?? 0; @endphp
                                <span class="badge bg-{{ $usageCount > 0 ? 'info' : 'light' }} text-{{ $usageCount > 0 ? 'white' : 'dark' }} border">
                                    <i class="bi bi-{{ $usageCount > 0 ? 'check-circle-fill' : 'dash-circle' }} me-1"></i>
                                    {{ $usageCount }} {{ Str::plural('use', $usageCount) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('staff.addons.show', $addon) }}" class="btn btn-sm btn-outline-info" title="View Details">
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
                    <i class="bi bi-plus-circle display-1 text-muted opacity-25"></i>
                    <h5 class="text-muted mb-2 mt-3">No add-ons available</h5>
                    <p class="text-muted mb-0">Add-ons will appear here once created by admin.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchAddons');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const val = this.value.toLowerCase();
            document.querySelectorAll('#addonsTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
            });
        });
    }

    const showInactive = document.getElementById('showInactiveAddons');
    if (showInactive) {
        showInactive.addEventListener('change', function() {
            document.querySelectorAll('.addon-row').forEach(row => {
                row.style.display = (!this.checked && row.dataset.active === '0') ? 'none' : '';
            });
        });
        showInactive.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection
