@extends('admin.layouts.app')

@section('title', 'Budget Management — WashBox')
@section('page-title', 'Budget Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Budget Management</h2>
            <p class="text-muted mb-0">Track and manage budgets across branches</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.finance.budgets.create') }}" class="btn-inventory btn-inventory-primary">
                <i class="bi bi-plus-circle me-2"></i>Create Budget
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-1">Total Allocated</p>
                    <h4 class="mb-0">₱{{ number_format($summary['total_allocated'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-1">Total Utilized</p>
                    <h4 class="mb-0 text-primary">₱{{ number_format($summary['total_spent'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-1">Remaining</p>
                    <h4 class="mb-0 text-success">₱{{ number_format($summary['total_allocated'] - $summary['total_spent'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-1">Over Budget</p>
                    <h4 class="mb-0 text-danger">{{ $summary['over_budget_count'] }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="inventory-card mb-4">
        <div class="inventory-card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
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
                    <select name="period" class="form-select">
                        <option value="">All Periods</option>
                        <option value="monthly" {{ request('period') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ request('period') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="yearly" {{ request('period') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="exceeded" {{ request('status') == 'exceeded' ? 'selected' : '' }}>Exceeded</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-inventory btn-inventory-primary flex-grow-1">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.finance.budgets.index') }}" class="btn-inventory btn-inventory-secondary">
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
                            <th>Budget Name</th>
                            <th>Branch</th>
                            <th>Period</th>
                            <th>Date Range</th>
                            <th class="text-end">Allocated</th>
                            <th class="text-end">Utilized</th>
                            <th>Utilization</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($budgets as $budget)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.finance.budgets.show', $budget) }}" class="text-primary fw-semibold">
                                        {{ $budget->name }}
                                    </a>
                                </td>
                                <td>{{ $budget->branch->name ?? 'All Branches' }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($budget->period_type) }}</span></td>
                                <td>{{ $budget->start_date->format('M d, Y') }} - {{ $budget->end_date->format('M d, Y') }}</td>
                                <td class="text-end fw-semibold">₱{{ number_format($budget->allocated_amount, 2) }}</td>
                                <td class="text-end">₱{{ number_format($budget->spent_amount, 2) }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-{{ $budget->utilization_percentage >= 100 ? 'danger' : ($budget->utilization_percentage >= 80 ? 'warning' : 'success') }}" 
                                                 style="width: {{ min($budget->utilization_percentage, 100) }}%"></div>
                                        </div>
                                        <span class="small">{{ number_format($budget->utilization_percentage, 1) }}%</span>
                                    </div>
                                </td>
                                <td>
                                    @if($budget->utilization_percentage >= 100)
                                        <span class="badge bg-danger">Exceeded</span>
                                    @elseif($budget->end_date->isPast())
                                        <span class="badge bg-secondary">Completed</span>
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.finance.budgets.show', $budget) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.finance.budgets.edit', $budget) }}" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No budgets found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($budgets->hasPages())
                <div class="mt-4">
                    {{ $budgets->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
