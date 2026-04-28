@extends('branch.layouts.app')

@section('title', 'Daily Cash Report')

@push('styles')
<style>
.card {
    background: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}
.card-body {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
}
.card-header {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}
.list-group-item {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}
.card-header.bg-success,
.card-header.bg-danger {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
}
@media print {
    .btn, .card-header, nav, aside, .no-print {
        display: none !important;
    }
    .card {
        border: 1px solid #000 !important;
        page-break-inside: avoid;
    }
}
</style>
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1" style="color: var(--text-primary);">Daily Cash Report</h2>
            <p class="mb-0" style="color: var(--text-secondary);">Cash reconciliation and daily summary</p>
        </div>
    </div>

    {{-- Quick Navigation --}}
    <div class="btn-group mb-4 w-100" role="group">
        <a href="{{ route('branch.finance.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </a>
        <a href="{{ route('branch.finance.expenses') }}" class="btn btn-outline-danger">
            <i class="bi bi-receipt me-1"></i>Expenses
        </a>
        <a href="{{ route('branch.finance.daily-cash-report') }}" class="btn btn-success active">
            <i class="bi bi-cash-coin me-1"></i>Daily Cash
        </a>
        <a href="{{ route('branch.finance.weekly-summary') }}" class="btn btn-outline-info">
            <i class="bi bi-graph-up me-1"></i>Weekly Summary
        </a>
        <a href="{{ route('branch.finance.sales-report') }}" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-text me-1"></i>Sales Report
        </a>
    </div>

    {{-- Date Selector --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Select Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}" max="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-calendar-check me-1"></i>Load Report
                    </button>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>Print Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Cash Flow Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <p class="mb-2" style="color: var(--text-secondary);">Opening Balance</p>
                    <h4 class="mb-0" style="color: var(--text-primary);">₱{{ number_format($report['opening_balance'], 2) }}</h4>
                    <small style="color: var(--text-secondary);">Start of day</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-success">
                <div class="card-body text-center">
                    <p class="mb-2" style="color: var(--text-secondary);">Cash In</p>
                    <h4 class="mb-0 text-success">₱{{ number_format($report['cash_in']['total'], 2) }}</h4>
                    <small style="color: var(--text-secondary);">Total income</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-danger">
                <div class="card-body text-center">
                    <p class="mb-2" style="color: var(--text-secondary);">Cash Out</p>
                    <h4 class="mb-0 text-danger">₱{{ number_format($report['cash_out']['total'], 2) }}</h4>
                    <small style="color: var(--text-secondary);">Total expenses</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm {{ $report['closing_balance'] >= 0 ? 'border-primary' : 'border-warning' }}">
                <div class="card-body text-center">
                    <p class="mb-2" style="color: var(--text-secondary);">Closing Balance</p>
                    <h4 class="mb-0 {{ $report['closing_balance'] >= 0 ? 'text-primary' : 'text-warning' }}">
                        ₱{{ number_format($report['closing_balance'], 2) }}
                    </h4>
                    <small style="color: var(--text-secondary);">End of day</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Net Cash Flow --}}
    <div class="card shadow-sm mb-4 {{ $report['net_cash_flow'] >= 0 ? 'border-success' : 'border-danger' }}">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1" style="color: var(--text-primary);">Net Cash Flow</h5>
                    <p class="mb-0" style="color: var(--text-secondary);">Total cash in minus total cash out</p>
                </div>
                <div class="col-md-4 text-end">
                    <h2 class="mb-0 {{ $report['net_cash_flow'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $report['net_cash_flow'] >= 0 ? '+' : '' }}₱{{ number_format($report['net_cash_flow'], 2) }}
                    </h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Breakdown --}}
    <div class="row g-3">
        {{-- Cash In Details --}}
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-down-circle me-2"></i>Cash In Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-basket text-success me-2"></i>
                                <strong>Laundry Sales</strong>
                            </div>
                            <span class="badge bg-success rounded-pill">
                                ₱{{ number_format($report['cash_in']['laundry'], 2) }}
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-shop text-success me-2"></i>
                                <strong>Retail Sales</strong>
                            </div>
                            <span class="badge bg-success rounded-pill">
                                ₱{{ number_format($report['cash_in']['retail'], 2) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Total Cash In:</strong>
                            <h5 class="mb-0 text-success">₱{{ number_format($report['cash_in']['total'], 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cash Out Details --}}
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-up-circle me-2"></i>Cash Out Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-receipt text-danger me-2"></i>
                                <strong>Operating Expenses</strong>
                            </div>
                            <span class="badge bg-danger rounded-pill">
                                ₱{{ number_format($report['cash_out']['expenses'], 2) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Total Cash Out:</strong>
                            <h5 class="mb-0 text-danger">₱{{ number_format($report['cash_out']['total'], 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cash Reconciliation Note --}}
    <div class="card shadow-sm mt-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-clipboard-check me-2"></i>Cash Reconciliation
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Expected Cash on Hand:</strong> ₱{{ number_format($report['closing_balance'], 2) }}
            </div>
            
            <p class="mb-2"><strong>Reconciliation Steps:</strong></p>
            <ol class="mb-0">
                <li>Count physical cash in register</li>
                <li>Compare with expected closing balance above</li>
                <li>Investigate any discrepancies</li>
                <li>Document findings in notes</li>
            </ol>

            <div class="mt-3">
                <label class="form-label">Reconciliation Notes:</label>
                <textarea class="form-control" rows="3" placeholder="Add any notes about cash discrepancies or issues..."></textarea>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .btn, .card-header, nav, aside, .no-print {
        display: none !important;
    }
    .card {
        border: 1px solid #000 !important;
        page-break-inside: avoid;
    }
}
</style>
@endpush
@endsection
