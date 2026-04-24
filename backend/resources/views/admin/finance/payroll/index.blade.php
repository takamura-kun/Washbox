@extends('admin.layouts.app')

@section('title', 'Payroll Management')

@push('styles')
<style>
    .metric-card-compact {
        border-left: 3px solid;
        transition: transform 0.2s;
    }
    .metric-card-compact:hover {
        transform: translateY(-2px);
    }
    .table-responsive, .table, .table tbody, .table tbody tr, .table tbody tr td,
    .table thead, .table thead tr, .table thead tr th {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    .table thead {
        background: var(--border-color) !important;
    }
    .table tbody tr:hover {
        background: var(--border-color) !important;
        opacity: 0.95;
    }
</style>
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-size: 1.5rem;">Payroll Management</h2>
            <p class="text-muted small mb-0" style="font-size: 0.8rem;">Manage payroll periods and staff payments</p>
        </div>
        <a href="{{ route('admin.finance.payroll.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-1"></i>New Payroll Period
        </a>
    </div>

    {{-- Quick Navigation --}}
    <div class="btn-group mb-3 w-100" role="group" style="box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-info">
            <i class="bi bi-people me-1"></i>Staff List
        </a>
        <a href="{{ route('admin.finance.payroll.index') }}" class="btn btn-primary">
            <i class="bi bi-list-ul me-1"></i>Payroll Periods
        </a>
        <a href="{{ route('admin.staff.salary-management') }}" class="btn btn-outline-success">
            <i class="bi bi-cash-stack me-1"></i>Salary Management
        </a>
        <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-calendar-check me-1"></i>Attendance
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="card metric-card-compact border-0 shadow-sm h-100" style="border-left-color: #3b82f6 !important;">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size: 0.7rem; color: var(--text-secondary);">Total Periods</span>
                        <i class="bi bi-calendar3 text-primary" style="font-size: 1rem;"></i>
                    </div>
                    <h3 class="mb-0 fw-bold" style="font-size: 1.5rem;">{{ $periods->total() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card metric-card-compact border-0 shadow-sm h-100" style="border-left-color: #10b981 !important;">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size: 0.7rem; color: var(--text-secondary);">Paid</span>
                        <i class="bi bi-check-circle text-success" style="font-size: 1rem;"></i>
                    </div>
                    <h3 class="mb-0 fw-bold" style="font-size: 1.5rem;">{{ $periods->where('status', 'paid')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card metric-card-compact border-0 shadow-sm h-100" style="border-left-color: #f59e0b !important;">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size: 0.7rem; color: var(--text-secondary);">Pending</span>
                        <i class="bi bi-clock text-warning" style="font-size: 1rem;"></i>
                    </div>
                    <h3 class="mb-0 fw-bold" style="font-size: 1.5rem;">{{ $periods->whereIn('status', ['draft', 'approved'])->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card metric-card-compact border-0 shadow-sm h-100" style="border-left-color: #8b5cf6 !important;">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size: 0.7rem; color: var(--text-secondary);">Total Amount</span>
                        <i class="bi bi-cash-stack text-purple" style="font-size: 1rem;"></i>
                    </div>
                    <h3 class="mb-0 fw-bold" style="font-size: 1.2rem;">₱{{ number_format($periods->sum('total_amount'), 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small mb-1" style="font-size: 0.75rem;">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1" style="font-size: 0.75rem;">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.finance.payroll.index') }}" class="btn btn-light border btn-sm w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="padding: 10px; font-size: 0.85rem;">Period</th>
                            <th style="padding: 10px; font-size: 0.85rem;">Branch</th>
                            <th style="padding: 10px; font-size: 0.85rem;">Date Range</th>
                            <th style="padding: 10px; font-size: 0.85rem;">Pay Date</th>
                            <th style="padding: 10px; font-size: 0.85rem;">Staff Count</th>
                            <th style="padding: 10px; font-size: 0.85rem;">Total Amount</th>
                            <th style="padding: 10px; font-size: 0.85rem;">Status</th>
                            <th style="padding: 10px; font-size: 0.85rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periods as $period)
                            <tr>
                                <td style="padding: 10px; font-size: 0.85rem;"><strong>{{ $period->period_label }}</strong></td>
                                <td style="padding: 10px; font-size: 0.85rem;">{{ $period->branch->name ?? 'All Branches' }}</td>
                                <td style="padding: 10px; font-size: 0.85rem;">{{ $period->date_from->format('M d') }} - {{ $period->date_to->format('M d, Y') }}</td>
                                <td style="padding: 10px; font-size: 0.85rem;">{{ $period->pay_date->format('M d, Y') }}</td>
                                <td style="padding: 10px; font-size: 0.85rem;">{{ $period->items->count() }}</td>
                                <td style="padding: 10px; font-size: 0.85rem;"><strong>₱{{ number_format($period->total_amount, 2) }}</strong></td>
                                <td style="padding: 10px; font-size: 0.85rem;">
                                    @if($period->status == 'draft')
                                        <span class="badge bg-secondary" style="font-size: 0.7rem;">Draft</span>
                                    @elseif($period->status == 'approved')
                                        <span class="badge bg-warning" style="font-size: 0.7rem;">Approved</span>
                                    @else
                                        <span class="badge bg-success" style="font-size: 0.7rem;">Paid</span>
                                    @endif
                                </td>
                                <td style="padding: 10px; font-size: 0.85rem;">
                                    <a href="{{ route('admin.finance.payroll.show', $period) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($period->status == 'draft')
                                        <form action="{{ route('admin.finance.payroll.destroy', $period) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this payroll?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted" style="padding: 10px; font-size: 0.85rem;">No payroll periods found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($periods->hasPages())
                <div class="p-3 border-top">
                    {{ $periods->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
