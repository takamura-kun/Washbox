@extends('branch.layouts.app')

@push('styles')
<style>
    .card {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
    }
    .card-body, .card-header, .card-footer {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    .card-header.bg-white, .card-footer.bg-white {
        background: var(--card-bg) !important;
    }
    .table {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
    }
    .table thead th, .table tbody td {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    .table-light {
        background: var(--card-bg) !important;
    }
    .table tbody tr:hover td {
        background: var(--hover-bg, rgba(0,0,0,0.05)) !important;
    }
    [data-theme="dark"] .table tbody tr:hover td {
        background: rgba(255,255,255,0.05) !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Branch Payroll</h1>
            <p class="mb-0" style="color: var(--text-secondary);">View payroll for all staff in your branch</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 small" style="color: var(--text-secondary);">Total Staff</p>
                            <h3 class="mb-0">{{ $stats['total_staff'] }}</h3>
                            <small style="color: var(--text-secondary);">In your branch</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-users text-primary fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 small" style="color: var(--text-secondary);">Total Paid</p>
                            <h3 class="mb-0">₱{{ number_format($stats['total_paid'], 2) }}</h3>
                            <small class="text-success"><i class="fas fa-check-circle"></i> Completed</small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-money-bill-wave text-success fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 small" style="color: var(--text-secondary);">Pending Payments</p>
                            <h3 class="mb-0">₱{{ number_format($stats['pending_payments'], 2) }}</h3>
                            <small class="text-warning"><i class="fas fa-clock"></i> Approved</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-hourglass-half text-warning fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 small" style="color: var(--text-secondary);">Total Payrolls</p>
                            <h3 class="mb-0">{{ $stats['total_payrolls'] }}</h3>
                            <small style="color: var(--text-secondary);">All records</small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-file-invoice-dollar text-info fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payroll List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Payroll Records</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Staff</th>
                            <th>Period</th>
                            <th>Days Worked</th>
                            <th>Hours Worked</th>
                            <th>Gross Pay</th>
                            <th>Deductions</th>
                            <th>Bonuses</th>
                            <th>Net Pay</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrollItems as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                        <span class="text-primary fw-bold small">{{ strtoupper(substr($item->user->name, 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $item->user->name }}</div>
                                        <small style="color: var(--text-secondary);">{{ $item->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $item->payrollPeriod->period_name }}</div>
                                <small style="color: var(--text-secondary);">
                                    {{ \Carbon\Carbon::parse($item->payrollPeriod->period_start)->format('M d') }} - 
                                    {{ \Carbon\Carbon::parse($item->payrollPeriod->period_end)->format('M d, Y') }}
                                </small>
                            </td>
                            <td>{{ $item->days_worked ?? 0 }} days</td>
                            <td>{{ $item->hours_worked ?? 0 }} hrs</td>
                            <td class="fw-semibold">₱{{ number_format($item->gross_pay, 2) }}</td>
                            <td class="text-danger">
                                @if($item->deductions > 0)
                                    -₱{{ number_format($item->deductions, 2) }}
                                @else
                                    ₱0.00
                                @endif
                            </td>
                            <td class="text-success">
                                @if($item->bonuses > 0)
                                    +₱{{ number_format($item->bonuses, 2) }}
                                @else
                                    ₱0.00
                                @endif
                            </td>
                            <td class="fw-bold text-primary">₱{{ number_format($item->net_pay, 2) }}</td>
                            <td>
                                @if($item->status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($item->status === 'approved')
                                    <span class="badge bg-warning">Approved</span>
                                @else
                                    <span class="badge bg-secondary">Pending</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('branch.payroll.show', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="fas fa-file-invoice fa-3x mb-3" style="color: var(--text-secondary);"></i>
                                <h5 style="color: var(--text-secondary);">No payroll records</h5>
                                <p class="mb-0" style="color: var(--text-secondary);">No payroll has been created for your branch yet.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payrollItems->hasPages())
        <div class="card-footer bg-white">
            {{ $payrollItems->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
