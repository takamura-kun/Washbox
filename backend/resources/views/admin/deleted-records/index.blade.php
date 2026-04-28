@extends('admin.layouts.app')

@section('title', 'Deleted Records — WashBox')
@section('page-title', 'Deleted Records')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<style>
.module-badge {
    font-size: 0.65rem; padding: 0.2rem 0.5rem; border-radius: 0.375rem;
    font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px;
}
.module-laundry    { background:#dbeafe;color:#1e40af; }
.module-customer   { background:#fce7f3;color:#9d174d; }
.module-staff      { background:#ffe4e6;color:#9f1239; }
.module-service    { background:#e0f2fe;color:#0369a1; }
.module-promotion  { background:#fef9c3;color:#854d0e; }
.module-inventory  { background:#f3e8ff;color:#6b21a8; }
.module-finance    { background:#dcfce7;color:#166534; }
.module-retail     { background:#ffedd5;color:#9a3412; }
</style>
@endpush

@section('content')
<div class="container-xl px-4 py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Deleted Records</h2>
            <p class="text-muted mb-0">History of all deleted data across the system</p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Total Deleted</p>
                            <h4 class="mb-0">{{ number_format($summary['total']) }}</h4>
                        </div>
                        <div class="inventory-icon bg-danger bg-opacity-10">
                            <i class="bi bi-trash text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Deleted Today</p>
                            <h4 class="mb-0 text-danger">{{ number_format($summary['today']) }}</h4>
                        </div>
                        <div class="inventory-icon bg-warning bg-opacity-10">
                            <i class="bi bi-calendar-x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">This Week</p>
                            <h4 class="mb-0">{{ number_format($summary['week']) }}</h4>
                        </div>
                        <div class="inventory-icon bg-info bg-opacity-10">
                            <i class="bi bi-calendar-week text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-2">By Module</p>
                    @foreach($summary['by_module'] as $mod => $count)
                        <div class="d-flex justify-content-between" style="font-size:0.75rem;">
                            <span class="module-badge module-{{ $mod }}">{{ $mod }}</span>
                            <span class="fw-bold">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="inventory-card mb-4">
        <div class="inventory-card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <select name="module" class="form-select form-select-sm">
                        <option value="">All Modules</option>
                        @foreach($modules as $module)
                            <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>
                                {{ ucfirst($module) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
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
                    <input type="text" name="deleted_by" class="form-control form-control-sm" placeholder="Deleted by..." value="{{ request('deleted_by') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn-inventory btn-inventory-primary btn-sm flex-grow-1">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.deleted-records.index') }}" class="btn-inventory btn-inventory-secondary btn-sm">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
                <div class="col-12">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, tracking number, label..." value="{{ request('search') }}">
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="inventory-card">
        <div class="inventory-card-body">
            <div class="table-responsive">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Deleted At</th>
                            <th>Module</th>
                            <th>Record</th>
                            <th>Model</th>
                            <th>Deleted By</th>
                            <th>Branch</th>
                            <th>IP</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        <tr>
                            <td style="white-space:nowrap;">
                                {{ $record->deleted_at->format('M d, Y') }}<br>
                                <small class="text-muted">{{ $record->deleted_at->format('h:i:s A') }}</small>
                            </td>
                            <td>
                                <span class="module-badge module-{{ $record->module }}">
                                    {{ $record->module }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $record->model_label }}</div>
                                <small class="text-muted">ID #{{ $record->original_id }}</small>
                            </td>
                            <td>
                                <small class="text-muted">{{ class_basename($record->model_type) }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $record->deleted_by_name ?? 'System' }}</div>
                                <small class="text-muted">{{ class_basename($record->deleted_by_type ?? '') }}</small>
                            </td>
                            <td><small>{{ $record->branch->name ?? '—' }}</small></td>
                            <td><small class="text-muted">{{ $record->ip_address ?? '—' }}</small></td>
                            <td>
                                <a href="{{ route('admin.deleted-records.show', $record) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-trash display-4 d-block mb-2"></i>
                                No deleted records found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($records->hasPages())
                <div class="mt-4">{{ $records->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
