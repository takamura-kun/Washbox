@extends('admin.layouts.app')

@section('title', 'Expense Report — WashBox')
@section('page-title', 'Expense Report')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
@endpush

@section('content')

<div class="container-xl px-4 py-4">
    {{-- Header with Filters --}}
    <div class="row mb-4 g-3">
        <div class="col-md-8">
            <form method="GET" class="d-flex gap-2 flex-wrap">
                <select name="period" class="inventory-form-control" onchange="this.form.submit()">
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                    <option value="year" {{ $period === 'year' ? 'selected' : '' }}>This Year</option>
                </select>
                <select name="category_id" class="inventory-form-control" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.finance.expenses.create') }}" class="btn-inventory btn-inventory-primary me-2">
                <i class="bi bi-plus-lg me-2"></i>Add Expense
            </a>
            <a href="{{ route('admin.finance.dashboard') }}" class="btn-inventory btn-inventory-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <small class="text-muted d-block mb-1">Total Expenses</small>
                    <h3 class="mb-0 text-danger">₱{{ number_format($summary['totalExpenses'], 2) }}</h3>
                    <small class="text-muted mt-2 d-block">Operating expenses</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <small class="text-muted d-block mb-1">Inventory Cost</small>
                    <h3 class="mb-0">₱{{ number_format($summary['inventoryCost'], 2) }}</h3>
                    <small class="text-muted mt-2 d-block">Purchases & supplies</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <small class="text-muted d-block mb-1">Total Costs</small>
                    <h3 class="mb-0">₱{{ number_format($summary['totalCosts'], 2) }}</h3>
                    <small class="text-muted mt-2 d-block">All expenses combined</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Expenses Table --}}
    <div class="inventory-card">
        <div class="inventory-card-body">
            <h5 class="mb-3">Expense Transactions</h5>
            <div class="table-responsive">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Title</th>
                            <th>Branch</th>
                            <th>Amount</th>
                            <th>Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $expense->category->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $expense->title }}</div>
                                    <small class="text-muted">{{ $expense->description }}</small>
                                </td>
                                <td>{{ $expense->branch->name ?? 'N/A' }}</td>
                                <td class="fw-semibold">₱{{ number_format($expense->amount, 2) }}</td>
                                <td>
                                    <span class="badge" style="background-color: {{ $expense->source === 'auto' ? '#3b82f6' : '#8b5cf6' }}">
                                        {{ $expense->source === 'auto' ? '🤖 Auto' : '✋ Manual' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No expenses found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($expenses->hasPages())
                <div class="mt-3">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
