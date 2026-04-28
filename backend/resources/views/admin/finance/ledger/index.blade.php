@extends('admin.layouts.app')

@section('title', 'Financial Ledger — WashBox')
@section('page-title', 'Financial Ledger')

@push('styles')
<!-- Google Fonts - Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<style>
/* Financial Ledger - Inter Font Family */
body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.5;
    font-size: 0.813rem;
}

.container-xl,
.inventory-card,
.inventory-table,
.form-label,
.form-control,
.form-select,
.btn-inventory {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

h2, h3, h4, h5, h6 {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-weight: 700;
}

/* Compact Table Styling - No Horizontal Scroll */
.inventory-table {
    font-size: 0.75rem !important; /* 12px */
    width: 100%;
    table-layout: auto;
}

.inventory-table thead th {
    padding: 0.625rem 0.5rem !important; /* Reduced padding */
    font-size: 0.6875rem !important; /* 11px */
    font-weight: 600 !important;
    white-space: nowrap;
    letter-spacing: 0.3px;
}

.inventory-table tbody td {
    padding: 0.625rem 0.5rem !important; /* Reduced padding */
    font-size: 0.75rem !important; /* 12px */
    vertical-align: middle !important;
    line-height: 1.4;
}

/* Compact Transaction Number */
.inventory-table tbody td:first-child {
    font-size: 0.7rem !important;
    font-weight: 600;
}

/* Compact Date Column */
.inventory-table tbody td:nth-child(2) {
    font-size: 0.7rem !important;
    white-space: nowrap;
}

/* Transaction Type - Compact */
.transaction-type-income,
.transaction-type-expense {
    font-size: 0.7rem !important;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.transaction-type-income {
    color: #10b981;
}

.transaction-type-expense {
    color: #ef4444;
}

.transaction-type-income i,
.transaction-type-expense i {
    font-size: 0.75rem;
}

/* Compact Badge Styling */
.badge {
    font-size: 0.65rem !important; /* 10.4px */
    padding: 0.25rem 0.5rem !important;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
}

.transaction-status-completed,
.transaction-status-pending,
.transaction-status-cancelled,
.transaction-status-reversed {
    font-size: 0.65rem !important;
    padding: 0.25rem 0.5rem !important;
    font-weight: 500;
    font-family: 'Inter', sans-serif;
    border-radius: 0.375rem;
    display: inline-block;
    white-space: nowrap;
}

.transaction-status-completed {
    background: #d1fae5;
    color: #065f46;
}

.transaction-status-pending {
    background: #fef3c7;
    color: #92400e;
}

.transaction-status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.transaction-status-reversed {
    background: #e5e7eb;
    color: #374151;
}

/* Compact Description Column */
.inventory-table tbody td:nth-child(5) {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.7rem !important;
}

/* Compact Branch Column */
.inventory-table tbody td:nth-child(6) {
    font-size: 0.7rem !important;
    white-space: nowrap;
}

/* Amount Column - Slightly Larger */
.inventory-table tbody td:nth-child(7) {
    font-size: 0.8125rem !important; /* 13px */
    font-weight: 700;
    white-space: nowrap;
}

/* Compact Action Buttons */
.btn-sm {
    padding: 0.25rem 0.5rem !important;
    font-size: 0.7rem !important;
}

.btn-sm i {
    font-size: 0.875rem;
}

/* Responsive Table Container */
.table-responsive {
    overflow-x: visible !important;
}

/* Adjust Summary Cards for Consistency */
h3 {
    font-size: 1.5rem !important;
}

.text-muted {
    font-size: 0.75rem !important;
}

/* Compact Filter Form */
.form-label.small {
    font-size: 0.7rem !important;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.form-select-sm,
.form-control-sm {
    font-size: 0.75rem !important;
    padding: 0.375rem 0.5rem !important;
}

/* Compact Header */
h2 {
    font-size: 1.5rem !important;
}

.text-muted.mb-0 {
    font-size: 0.8125rem !important;
}

/* Icon Sizing */
.bi {
    font-size: inherit;
}

/* Ensure no horizontal scroll */
@media (min-width: 1200px) {
    .container-xl {
        max-width: 100%;
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

/* Compact Empty State */
.inventory-table tbody td[colspan] {
    padding: 2rem 1rem !important;
}

.inventory-table tbody td[colspan] i {
    font-size: 2.5rem !important;
}

.inventory-table tbody td[colspan] .text-muted {
    font-size: 0.875rem !important;
}
</style>
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Financial Ledger</h2>
            <p class="text-muted mb-0">Complete transaction history</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.finance.ledger.export') }}" class="btn-inventory btn-inventory-secondary">
                <i class="bi bi-download me-2"></i>Export
            </a>
            <a href="{{ route('admin.finance.dashboard') }}" class="btn-inventory btn-inventory-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Total Income</p>
                            <h3 class="mb-0 text-success">₱{{ number_format($summary['total_income'], 2) }}</h3>
                        </div>
                        <div class="inventory-icon bg-success bg-opacity-10">
                            <i class="bi bi-arrow-up-circle text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Total Expenses</p>
                            <h3 class="mb-0 text-danger">₱{{ number_format($summary['total_expenses'], 2) }}</h3>
                        </div>
                        <div class="inventory-icon bg-danger bg-opacity-10">
                            <i class="bi bi-arrow-down-circle text-danger"></i>
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
                    <label class="form-label small">Branch</label>
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
                    <label class="form-label small">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income</option>
                        <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                        <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <option value="laundry_sale" {{ request('category') == 'laundry_sale' ? 'selected' : '' }}>Laundry Sale</option>
                        <option value="retail_sale" {{ request('category') == 'retail_sale' ? 'selected' : '' }}>Retail Sale</option>
                        <option value="pickup_fee" {{ request('category') == 'pickup_fee' ? 'selected' : '' }}>Pickup/Delivery Fee</option>
                        <option value="expense" {{ request('category') == 'expense' ? 'selected' : '' }}>Expense</option>
                        <option value="inventory_purchase" {{ request('category') == 'inventory_purchase' ? 'selected' : '' }}>Inventory Purchase</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="reversed" {{ request('status') == 'reversed' ? 'selected' : '' }}>Reversed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Transaction number or description..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn-inventory btn-inventory-primary btn-sm flex-grow-1">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.finance.ledger.index') }}" class="btn-inventory btn-inventory-secondary btn-sm">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="inventory-card">
        <div class="inventory-card-body">
            <div class="table-responsive">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Transaction #</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Branch</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.finance.ledger.show', $transaction) }}" class="text-primary fw-semibold">
                                        {{ $transaction->transaction_number }}
                                    </a>
                                </td>
                                <td>{{ $transaction->transaction_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="transaction-type-{{ $transaction->type }}">
                                        <i class="bi bi-{{ $transaction->type == 'income' ? 'arrow-up' : 'arrow-down' }}-circle me-1"></i>
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ str_replace('_', ' ', ucwords($transaction->category)) }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($transaction->description, 50) }}</td>
                                <td>{{ $transaction->branch->name ?? 'N/A' }}</td>
                                <td class="text-end fw-bold {{ $transaction->type == 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type == 'income' ? '+' : '-' }}₱{{ number_format($transaction->amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge transaction-status-{{ $transaction->status }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.finance.ledger.show', $transaction) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No transactions found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($transactions->hasPages())
                <div class="mt-4">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
