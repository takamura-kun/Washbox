@extends('branch.layouts.app')

@section('title', 'Expenses')

@push('styles')
<style>
    .card {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
    }
    .card-body {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
    }
    .table {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
    }
    .table thead th {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    .table tbody td {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    .table tbody tr:hover td {
        background: var(--hover-bg, rgba(0,0,0,0.05)) !important;
    }
    [data-theme="dark"] .table tbody tr:hover td {
        background: rgba(255,255,255,0.05) !important;
    }
    .modal-content {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
    }
    .modal-header, .modal-footer {
        border-color: var(--border-color) !important;
    }
    .form-control, .form-select {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    .input-group-text {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
</style>
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Branch Expenses</h2>
            <p class="mb-0" style="color: var(--text-secondary);">Track and manage your branch expenses</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="bi bi-plus-circle me-2"></i>Add Expense
        </button>
    </div>

    {{-- Quick Navigation --}}
    <div class="btn-group mb-4 w-100" role="group">
        <a href="{{ route('branch.finance.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </a>
        <a href="{{ route('branch.finance.expenses') }}" class="btn btn-danger active">
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
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1" style="color: var(--text-secondary);">Total Expenses</p>
                            <h3 class="mb-0 text-danger">₱{{ number_format($summary['total_expenses'], 2) }}</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="bi bi-cash-stack text-danger fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1" style="color: var(--text-secondary);">Total Transactions</p>
                            <h3 class="mb-0">{{ $summary['count'] }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-receipt text-primary fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="{{ route('branch.finance.expenses') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Expenses Table --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Created By</th>
                            <th>Description</th>
                            <th>Attachment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                <td><strong>{{ $expense->title }}</strong></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $expense->category->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td><strong class="text-danger">₱{{ number_format($expense->amount, 2) }}</strong></td>
                                <td>
                                    @if($expense->notes && str_contains($expense->notes, 'Created by:'))
                                        {{ trim(explode('\n', explode('Created by:', $expense->notes)[1])[0]) }}
                                    @elseif($expense->creator)
                                        {{ $expense->creator->name }}
                                    @else
                                        <span style="color: var(--text-secondary);">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($expense->description)
                                        <small>{{ Str::limit($expense->description, 50) }}</small>
                                    @else
                                        <span style="color: var(--text-secondary);">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($expense->attachment)
                                        <a href="{{ Storage::url($expense->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-paperclip"></i>
                                        </a>
                                    @else
                                        <span style="color: var(--text-secondary);">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4" style="color: var(--text-secondary);">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No expenses found for the selected period
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($expenses->hasPages())
                <div class="mt-4">
                    {{ $expenses->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Add Expense Modal --}}
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('branch.finance.record-expense') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Record New Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="expense_category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g., Electricity Bill" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Expense Date <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Created By <span class="text-danger">*</span></label>
                        <input type="text" name="created_by_name" class="form-control" placeholder="Your name" required>
                        <small style="color: var(--text-secondary);">Enter the name of the person recording this expense</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Additional details..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Attachment (Receipt/Invoice)</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <small style="color: var(--text-secondary);">Max 2MB. Formats: PDF, JPG, PNG</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
