@extends('admin.layouts.app')

@section('title', 'Financial Audit Logs — WashBox')
@section('page-title', 'Financial Audit Logs')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Financial Audit Logs</h2>
            <p class="text-muted mb-0">Complete audit trail of all financial activities</p>
        </div>
        <a href="{{ route('admin.finance.dashboard') }}" class="btn-inventory btn-inventory-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-1">Total Logs</p>
                    <h4 class="mb-0">{{ number_format($summary['total_logs']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-1">Today's Activities</p>
                    <h4 class="mb-0">{{ number_format($summary['today_logs']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-1">Unique Users</p>
                    <h4 class="mb-0">{{ number_format($summary['unique_users']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-1">Critical Actions</p>
                    <h4 class="mb-0 text-danger">{{ number_format($summary['critical_actions']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="inventory-card mb-4">
        <div class="inventory-card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <select name="action" class="form-select form-select-sm">
                        <option value="">All Actions</option>
                        <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                        <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                        <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                        <option value="reversed" {{ request('action') == 'reversed' ? 'selected' : '' }}>Reversed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search description..." value="{{ request('search') }}">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn-inventory btn-inventory-primary btn-sm flex-grow-1">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('admin.finance.audit-logs.index') }}" class="btn-inventory btn-inventory-secondary btn-sm">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="inventory-card">
        <div class="inventory-card-body">
            <div class="table-responsive">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Transaction</th>
                            <th class="text-end">Amount</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('M d, Y h:i A') }}</td>
                                <td>
                                    <div>
                                        <div class="fw-semibold">{{ $log->user->name ?? 'System' }}</div>
                                        <small class="text-muted">{{ $log->user->email ?? 'N/A' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->action == 'created' ? 'success' : ($log->action == 'updated' ? 'primary' : ($log->action == 'deleted' ? 'danger' : 'warning')) }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($log->description, 60) }}</td>
                                <td>
                                    @if($log->auditable && method_exists($log->auditable, 'getTable') && $log->auditable->getTable() === 'financial_transactions')
                                        <a href="{{ route('admin.finance.ledger.show', $log->auditable) }}" class="text-primary">
                                            {{ $log->auditable->transaction_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">{{ class_basename($log->auditable_type) ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @php
                                        $amountBefore = data_get($log->old_values, 'amount');
                                        $amountAfter  = data_get($log->new_values, 'amount');
                                    @endphp
                                    @if($amountBefore || $amountAfter)
                                        <div>
                                            @if($amountBefore)
                                                <small class="text-muted">₱{{ number_format($amountBefore, 2) }}</small>
                                                <i class="bi bi-arrow-right mx-1"></i>
                                            @endif
                                            @if($amountAfter)
                                                <span class="fw-semibold">₱{{ number_format($amountAfter, 2) }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $log->ip_address ?? 'N/A' }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No audit logs found
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
