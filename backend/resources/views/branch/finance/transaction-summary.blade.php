@extends('branch.layouts.app')

@section('page-title', 'Transaction Summary')

@push('styles')
<style>
    .card { background: var(--card-bg) !important; border-color: var(--border-color) !important; color: var(--text-primary) !important; }
    .card-body, .card-header { background: var(--card-bg) !important; color: var(--text-primary) !important; border-color: var(--border-color) !important; }
    .table { background: var(--card-bg) !important; color: var(--text-primary) !important; }
    .table thead th, .table tbody td { background: var(--card-bg) !important; color: var(--text-primary) !important; border-color: var(--border-color) !important; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Transaction Summary</h2>
    </div>

    {{-- Quick Navigation --}}
    <div class="btn-group mb-4 w-100" role="group">
        <a href="{{ route('branch.finance.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </a>
        <a href="{{ route('branch.finance.expenses') }}" class="btn btn-outline-danger">
            <i class="bi bi-receipt me-1"></i>Expenses
        </a>
        <a href="{{ route('branch.finance.daily-cash-report') }}" class="btn btn-outline-success">
            <i class="bi bi-cash-coin me-1"></i>Daily Cash
        </a>
        <a href="{{ route('branch.finance.weekly-summary') }}" class="btn btn-outline-info">
            <i class="bi bi-graph-up me-1"></i>Weekly Summary
        </a>
        <a href="{{ route('branch.finance.sales-report') }}" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-text me-1"></i>Sales Report
        </a>
        <a href="{{ route('branch.finance.transaction-summary') }}" class="btn btn-primary active">
            <i class="bi bi-list-columns me-1"></i>Transaction Summary
        </a>
    </div>

    {{-- Date Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">From</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">To</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                            <i class="bi bi-basket fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-1" style="color: var(--text-secondary);">Laundry Sales</h6>
                            <h4 class="mb-0 text-primary">₱{{ number_format($summary['laundry_sales'], 2) }}</h4>
                            <small style="color: var(--text-secondary);">{{ $summary['laundry_count'] }} orders</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 text-info rounded p-3 me-3">
                            <i class="bi bi-shop fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-1" style="color: var(--text-secondary);">Retail Sales</h6>
                            <h4 class="mb-0 text-info">₱{{ number_format($summary['retail_sales'], 2) }}</h4>
                            <small style="color: var(--text-secondary);">{{ $summary['retail_count'] }} orders</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger bg-opacity-10 text-danger rounded p-3 me-3">
                            <i class="bi bi-receipt fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-1" style="color: var(--text-secondary);">Total Expenses</h6>
                            <h4 class="mb-0 text-danger">₱{{ number_format($summary['total_expenses'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-{{ $summary['net'] >= 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $summary['net'] >= 0 ? 'success' : 'danger' }} rounded p-3 me-3">
                            <i class="bi bi-graph-up fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-1" style="color: var(--text-secondary);">Net Income</h6>
                            <h4 class="mb-0 text-{{ $summary['net'] >= 0 ? 'success' : 'danger' }}">
                                ₱{{ number_format($summary['net'], 2) }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Expenses by Category --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Expenses by Category</h5>
                </div>
                <div class="card-body">
                    @forelse($expensesByCategory as $item)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-semibold">{{ $item['category'] }}</div>
                            <small style="color: var(--text-secondary);">{{ $item['count'] }} transaction(s)</small>
                        </div>
                        <div class="text-end text-danger fw-bold">₱{{ number_format($item['total'], 2) }}</div>
                    </div>
                    @empty
                    <p class="text-center py-4" style="color: var(--text-secondary);">No expenses recorded</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Recent Transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Reference</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $txn)
                                <tr>
                                    <td>
                                        <span class="badge bg-{{ $txn->type === 'sale' ? 'success' : 'danger' }}">
                                            {{ ucfirst($txn->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $txn->tracking_number }}</td>
                                    <td class="fw-bold text-{{ $txn->type === 'sale' ? 'success' : 'danger' }}">
                                        {{ $txn->type === 'sale' ? '+' : '-' }}₱{{ number_format($txn->total_amount, 2) }}
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($txn->paid_at)->format('M d, Y h:i A') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1" style="color: var(--text-secondary);"></i>
                                        <p class="mt-2" style="color: var(--text-secondary);">No transactions found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
