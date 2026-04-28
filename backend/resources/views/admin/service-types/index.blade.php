@extends('admin.layouts.app')

@section('title', 'Service Types')
@section('page-title', 'SERVICE TYPES')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted small mb-0">Manage predefined service type templates</p>
        </div>
        <a href="{{ route('admin.service-types.create') }}" class="btn btn-primary shadow-sm" style="background: #3D3B6B; border: none;">
            <i class="bi bi-plus-circle me-2"></i>Add Service Type
        </a>
    </div>

    @foreach($serviceTypes as $category => $types)
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: var(--card-bg) !important;">
        <div class="card-header border-bottom py-3" style="background-color: var(--border-color) !important; color: var(--text-primary) !important;">
            <h6 class="mb-0 fw-bold" style="color: var(--text-primary) !important;">
                @php
                    $catIcons = ['drop_off' => 'bi-bag', 'self_service' => 'bi-arrow-repeat'];
                    $catLabels = ['drop_off' => 'Drop Off Services', 'self_service' => 'Self Service'];
                @endphp
                <i class="bi {{ $catIcons[$category] ?? 'bi-box-seam' }} me-2" style="color: #3D3B6B;"></i>
                {{ $catLabels[$category] ?? ucfirst(str_replace('_', ' ', $category)) }}
            </h6>
        </div>
        <div class="card-body p-0" style="background-color: var(--card-bg) !important;">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
                    <thead style="background-color: var(--border-color) !important;">
                        <tr>
                            <th style="width: 50px; background-color: var(--border-color) !important; color: var(--text-primary) !important;">#</th>
                            <th style="background-color: var(--border-color) !important; color: var(--text-primary) !important;">Name</th>
                            <th style="background-color: var(--border-color) !important; color: var(--text-primary) !important;">Description</th>
                            <th style="background-color: var(--border-color) !important; color: var(--text-primary) !important;">Default Price</th>
                            <th style="background-color: var(--border-color) !important; color: var(--text-primary) !important;">Pricing Type</th>
                            <th style="background-color: var(--border-color) !important; color: var(--text-primary) !important;">Max Weight</th>
                            <th style="background-color: var(--border-color) !important; color: var(--text-primary) !important;">Turnaround</th>
                            <th style="background-color: var(--border-color) !important; color: var(--text-primary) !important;">Status</th>
                            <th style="width: 150px; background-color: var(--border-color) !important; color: var(--text-primary) !important;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($types as $type)
                        <tr style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
                            <td style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">{{ $type->display_order }}</td>
                            <td style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
                                <div class="d-flex align-items-center">
                                    <i class="bi {{ $type->icon }} me-2" style="font-size: 1.2rem; color: #3D3B6B;"></i>
                                    <strong>{{ $type->name }}</strong>
                                </div>
                            </td>
                            <td style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;"><small class="text-muted">{{ Str::limit($type->description, 50) }}</small></td>
                            <td style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">₱{{ number_format($type->defaults['price'] ?? 0, 2) }}</td>
                            <td style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
                                <span class="badge {{ ($type->defaults['pricing_type'] ?? 'per_load') === 'per_piece' ? 'bg-warning text-dark' : 'bg-primary' }}">
                                    {{ ($type->defaults['pricing_type'] ?? 'per_load') === 'per_piece' ? 'Per Piece' : 'Per Load' }}
                                </span>
                            </td>
                            <td style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">{{ $type->defaults['max_weight'] ? number_format($type->defaults['max_weight'], 1) . ' kg' : '—' }}</td>
                            <td style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">{{ $type->defaults['turnaround'] ?? 0 }} hrs</td>
                            <td style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
                                <span class="badge {{ $type->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $type->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.service-types.edit', $type) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-{{ $type->is_active ? 'warning' : 'success' }}"
                                            onclick="toggleStatus({{ $type->id }})" title="Toggle Status">
                                        <i class="bi bi-{{ $type->is_active ? 'pause' : 'play' }}-circle"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="deleteServiceType({{ $type->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
</div>

@push('styles')
<style>
.card {
    background-color: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}

.card-body {
    background-color: var(--card-bg) !important;
    color: var(--text-primary) !important;
}

.card-header {
    background-color: var(--border-color) !important;
    color: var(--text-primary) !important;
}

.table {
    background-color: var(--card-bg) !important;
    color: var(--text-primary) !important;
}

.table thead th {
    background-color: var(--border-color) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}

.table tbody tr {
    background-color: var(--card-bg) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}

.table tbody tr:hover {
    background-color: var(--input-bg) !important;
}

.table td {
    border-color: var(--border-color) !important;
}
</style>
@endpush

@push('scripts')
<script>
function toggleStatus(id) {
    if (!confirm('Are you sure you want to toggle the status?')) return;

    fetch(`/admin/service-types/${id}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error updating status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating status');
    });
}

function deleteServiceType(id) {
    if (!confirm('Are you sure you want to delete this service type? This action cannot be undone.')) return;

    fetch(`/admin/service-types/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error deleting service type');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting service type');
    });
}
</script>
@endpush
@endsection
