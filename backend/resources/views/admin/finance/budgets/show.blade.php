@extends('admin.layouts.app')

@section('title', 'Budget Details — WashBox')
@section('page-title', 'Budget Details')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $budget->name }}</h2>
            <p class="text-muted mb-0">{{ $budget->start_date->format('M d, Y') }} - {{ $budget->end_date->format('M d, Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.finance.budgets.edit', $budget) }}" class="btn-inventory btn-inventory-warning">
                <i class="bi bi-pencil me-2"></i>Edit
            </a>
            <a href="{{ route('admin.finance.budgets.index') }}" class="btn-inventory btn-inventory-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-1">Allocated Amount</p>
                    <h3 class="mb-0">₱{{ number_format($budget->allocated_amount, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-1">Utilized Amount</p>
                    <h3 class="mb-0 text-primary">₱{{ number_format($budget->spent_amount, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-1">Remaining</p>
                    <h3 class="mb-0 text-{{ $budget->spent_amount > $budget->allocated_amount ? 'danger' : 'success' }}">
                        ₱{{ number_format($budget->allocated_amount - $budget->spent_amount, 2) }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-1">Utilization</p>
                    <h3 class="mb-0">{{ number_format($budget->utilization_percentage, 1) }}%</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="inventory-card mb-4">
                <div class="inventory-card-header">
                    <h5 class="mb-0">Utilization Progress</h5>
                </div>
                <div class="inventory-card-body">
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar bg-{{ $budget->utilization_percentage >= 100 ? 'danger' : ($budget->utilization_percentage >= $budget->alert_threshold ? 'warning' : 'success') }}" 
                             style="width: {{ min($budget->utilization_percentage, 100) }}%">
                            {{ number_format($budget->utilization_percentage, 1) }}%
                        </div>
                    </div>
                    @if($budget->utilization_percentage >= 100)
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Budget exceeded by ₱{{ number_format($budget->spent_amount - $budget->allocated_amount, 2) }}
                        </div>
                    @elseif($budget->utilization_percentage >= $budget->alert_threshold)
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            Budget utilization has reached {{ number_format($budget->utilization_percentage, 1) }}% (Alert threshold: {{ $budget->alert_threshold }}%)
                        </div>
                    @endif
                </div>
            </div>

            <div class="inventory-card">
                <div class="inventory-card-header">
                    <h5 class="mb-0">Related Expenses</h5>
                </div>
                <div class="inventory-card-body">
                    <div class="table-responsive">
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $expense)
                                    <tr>
                                        <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                        <td>{{ $expense->category->name ?? 'N/A' }}</td>
                                        <td>{{ Str::limit($expense->description, 50) }}</td>
                                        <td class="text-end fw-semibold">₱{{ number_format($expense->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No expenses recorded yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($expenses->hasPages())
                        <div class="mt-4">
                            {{ $expenses->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="inventory-card mb-4">
                <div class="inventory-card-header">
                    <h5 class="mb-0">Budget Information</h5>
                </div>
                <div class="inventory-card-body">
                    <div class="mb-3">
                        <p class="text-muted small mb-1">Branch</p>
                        <p class="mb-0">{{ $budget->branch->name ?? 'All Branches' }}</p>
                    </div>
                    <div class="mb-3">
                        <p class="text-muted small mb-1">Period</p>
                        <p class="mb-0"><span class="badge bg-secondary">{{ ucfirst($budget->period_type) }}</span></p>
                    </div>
                    <div class="mb-3">
                        <p class="text-muted small mb-1">Alert Threshold</p>
                        <p class="mb-0">{{ $budget->alert_threshold }}%</p>
                    </div>
                    @if($budget->description)
                    <div>
                        <p class="text-muted small mb-1">Description</p>
                        <p class="mb-0">{{ $budget->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="inventory-card">
                <div class="inventory-card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="inventory-card-body">
                    <form method="POST" action="{{ route('admin.finance.budgets.refresh', $budget) }}" class="mb-2">
                        @csrf
                        <button type="submit" class="btn-inventory btn-inventory-primary w-100">
                            <i class="bi bi-arrow-clockwise me-2"></i>Refresh Utilization
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.finance.budgets.destroy', $budget) }}" 
                          onsubmit="return confirm('Are you sure you want to delete this budget?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-inventory btn-inventory-danger w-100">
                            <i class="bi bi-trash me-2"></i>Delete Budget
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
