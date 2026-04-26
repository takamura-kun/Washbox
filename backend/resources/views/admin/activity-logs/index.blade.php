@extends('admin.layouts.app')

@section('title', 'Activity Logs — WashBox')
@section('page-title', 'Activity Logs')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<style>
.module-badge {
    font-size: 0.65rem;
    padding: 0.2rem 0.5rem;
    border-radius: 0.375rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.module-laundry    { background: #dbeafe; color: #1e40af; }
.module-pickup     { background: #e0f2fe; color: #0369a1; }
.module-finance    { background: #dcfce7; color: #166534; }
.module-retail     { background: #fef9c3; color: #854d0e; }
.module-inventory  { background: #f3e8ff; color: #6b21a8; }
.module-staff      { background: #ffe4e6; color: #9f1239; }
.module-attendance { background: #ffedd5; color: #9a3412; }
.module-payroll    { background: #d1fae5; color: #065f46; }
.module-auth       { background: #f1f5f9; color: #334155; }

.event-badge {
    font-size: 0.65rem;
    padding: 0.2rem 0.5rem;
    border-radius: 0.375rem;
    font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="container-xl px-4 py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Activity Logs</h2>
            <p class="text-muted mb-0">Complete audit trail of all system activities</p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Total Logs</p>
                            <h4 class="mb-0">{{ number_format($summary['total']) }}</h4>
                        </div>
                        <div class="inventory-icon bg-primary bg-opacity-10">
                            <i class="bi bi-journal-text text-primary"></i>
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
                            <p class="text-muted mb-1">Today's Activities</p>
                            <h4 class="mb-0">{{ number_format($summary['today']) }}</h4>
                        </div>
                        <div class="inventory-icon bg-success bg-opacity-10">
                            <i class="bi bi-activity text-success"></i>
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
                            <p class="text-muted mb-1">Logins Today</p>
                            <h4 class="mb-0">{{ number_format($summary['logins']) }}</h4>
                        </div>
                        <div class="inventory-icon bg-info bg-opacity-10">
                            <i class="bi bi-box-arrow-in-right text-info"></i>
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
                            <p class="text-muted mb-1">Critical Today</p>
                            <h4 class="mb-0 text-danger">{{ number_format($summary['errors']) }}</h4>
                        </div>
                        <div class="inventory-icon bg-danger bg-opacity-10">
                            <i class="bi bi-exclamation-triangle text-danger"></i>
                        </div>
                    </div>
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
                    <select name="event" class="form-select form-select-sm">
                        <option value="">All Events</option>
                        @foreach($events as $event)
                            <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $event)) }}
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
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="From">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="To">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn-inventory btn-inventory-primary btn-sm flex-grow-1">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn-inventory btn-inventory-secondary btn-sm">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search description or user..." value="{{ request('search') }}">
                </div>
            </form>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="inventory-card">
        <div class="inventory-card-body">
            <div class="table-responsive">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User / Actor</th>
                            <th>Module</th>
                            <th>Event</th>
                            <th>Description</th>
                            <th>Subject</th>
                            <th>Branch</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td style="white-space:nowrap;">
                                {{ $log->created_at->format('M d, Y') }}<br>
                                <small class="text-muted">{{ $log->created_at->format('h:i:s A') }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $log->causer_name ?? 'System' }}</div>
                                <small class="text-muted">{{ class_basename($log->causer_type ?? '') }}</small>
                            </td>
                            <td>
                                <span class="module-badge module-{{ $log->module }}">
                                    {{ $log->module }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $eventColor = match($log->event) {
                                        'created'        => 'success',
                                        'updated', 'status_changed' => 'primary',
                                        'deleted', 'deactivated'    => 'danger',
                                        'login'          => 'info',
                                        'logout'         => 'secondary',
                                        'paid', 'received' => 'success',
                                        'transferred'    => 'warning',
                                        default          => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $eventColor }} event-badge">
                                    {{ ucfirst(str_replace('_', ' ', $log->event)) }}
                                </span>
                            </td>
                            <td>{{ $log->description }}</td>
                            <td>
                                @if($log->subject_label)
                                    <span class="text-muted" style="font-size:0.7rem;">
                                        {{ class_basename($log->subject_type) }}<br>
                                    </span>
                                    <span class="fw-semibold" style="font-size:0.75rem;">{{ $log->subject_label }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $log->branch->name ?? '—' }}</small>
                            </td>
                            <td>
                                <small class="text-muted">{{ $log->ip_address ?? '—' }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x display-4 d-block mb-2"></i>
                                No activity logs found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
