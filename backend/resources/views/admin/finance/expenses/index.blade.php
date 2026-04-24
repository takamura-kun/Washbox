@extends('admin.layouts.app')

@section('title', 'Expenses — WashBox')
@section('page-title', 'Expense Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<style>
.expense-tabs {
    border-bottom: 2px solid var(--border-color);
    margin-bottom: 1.5rem;
}
.expense-tab {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    margin-right: 0.5rem;
    border: none;
    background: transparent;
    color: var(--text-muted);
    font-weight: 500;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
}
.expense-tab:hover {
    color: var(--text-primary);
}
.expense-tab.active {
    color: #3D3B6B;
    border-bottom-color: #3D3B6B;
}
.expense-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 0.5rem;
}
.table-responsive {
    background: var(--card-bg) !important;
}
.table {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
}
.table tbody tr {
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
.table tfoot th {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}
.card-body {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
}
</style>
@endpush

@section('content')

<div class="container-xl px-4 py-4">
    {{-- Tabs Navigation --}}
    <div class="expense-tabs">
        <button class="expense-tab {{ request('type', 'all') === 'all' ? 'active' : '' }}"
                onclick="window.location.href='{{ route('admin.finance.expenses.index', array_merge(request()->except('type'), ['type' => 'all'])) }}'">
            All Expenses
            <span class="expense-badge" style="background: #e5e7eb; color: #374151;">{{ $stats['total'] ?? 0 }}</span>
        </button>
        <button class="expense-tab {{ request('type') === 'salary' ? 'active' : '' }}"
                onclick="window.location.href='{{ route('admin.finance.expenses.index', array_merge(request()->except('type'), ['type' => 'salary'])) }}'">
            💰 Salary Expenses
            <span class="expense-badge" style="background: #dbeafe; color: #1e40af;">{{ $stats['salary'] ?? 0 }}</span>
        </button>
        <button class="expense-tab {{ request('type') === 'inventory' ? 'active' : '' }}"
                onclick="window.location.href='{{ route('admin.finance.expenses.index', array_merge(request()->except('type'), ['type' => 'inventory'])) }}'">
            📦 Inventory Expenses
            <span class="expense-badge" style="background: #fef3c7; color: #92400e;">{{ $stats['inventory'] ?? 0 }}</span>
        </button>
        <button class="expense-tab {{ request('type') === 'other' ? 'active' : '' }}"
                onclick="window.location.href='{{ route('admin.finance.expenses.index', array_merge(request()->except('type'), ['type' => 'other'])) }}'">
            📋 Other Expenses
            <span class="expense-badge" style="background: #f3e8ff; color: #6b21a8;">{{ $stats['other'] ?? 0 }}</span>
        </button>
    </div>
    {{-- Header with Filters --}}
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
                <input type="hidden" name="type" value="{{ request('type', 'all') }}">

                <label class="small mb-0" style="white-space: nowrap; color: var(--text-secondary);">Filters:</label>

                <select name="branch_id" class="form-select form-select-sm" style="width: auto; min-width: 150px;" onchange="this.form.submit()">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>

                <select name="category_id" class="form-select form-select-sm" style="width: auto; min-width: 150px;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <select name="source" class="form-select form-select-sm" style="width: auto; min-width: 120px;" onchange="this.form.submit()">
                    <option value="">All Sources</option>
                    <option value="manual" {{ request('source') === 'manual' ? 'selected' : '' }}>Manual</option>
                    <option value="auto" {{ request('source') === 'auto' ? 'selected' : '' }}>Auto</option>
                </select>

                @if(request('branch_id') || request('category_id') || request('source'))
                    <a href="{{ route('admin.finance.expenses.index', ['type' => request('type', 'all')]) }}"
                       class="btn btn-sm btn-outline-secondary"
                       title="Clear Filters">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </form>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.finance.expenses.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Add Expense
            </a>
        </div>
    </div>

    {{-- Summary Cards - Compact Design (Hide when on Salary/Inventory/Other tab) --}}
    @if(request('type') !== 'salary' && request('type') !== 'inventory' && request('type') !== 'other')
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                                <i class="bi bi-cash-stack text-white" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="d-block mb-1" style="color: var(--text-secondary);">Total Expenses</small>
                            <h5 class="mb-0 fw-bold text-danger">₱{{ number_format($totalExpenses, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                <i class="bi bi-list-check text-white" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="d-block mb-1" style="color: var(--text-secondary);">Filtered Results</small>
                            <h5 class="mb-0 fw-bold">{{ $expenses->total() }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <i class="bi bi-calculator text-white" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="d-block mb-1" style="color: var(--text-secondary);">Average Expense</small>
                            <h5 class="mb-0 fw-bold">₱{{ number_format($expenses->total() > 0 ? $totalExpenses / $expenses->total() : 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                                <i class="bi bi-calendar-range text-white" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="d-block mb-1" style="color: var(--text-secondary);">This Month</small>
                            <h5 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Other Expenses Summary Cards (Only show when on Other tab) --}}
    @if(request('type') === 'other')
    <div class="row mb-4 g-3">
        {{-- Total Other Expenses --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #8b5cf6 !important; cursor: pointer; transition: transform 0.2s;"
                 data-bs-toggle="modal" data-bs-target="#totalOtherModal"
                 onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: #f3e8ff;">
                                <i class="bi bi-receipt" style="color: #8b5cf6; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="d-block mb-1" style="color: var(--text-secondary);">Total Other Expenses</small>
                            <h5 class="mb-0 fw-bold" style="color: #8b5cf6;">₱{{ number_format($otherTotals['total'] ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Weekly Other Expenses --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #3b82f6 !important; cursor: pointer; transition: transform 0.2s;"
                 data-bs-toggle="modal" data-bs-target="#weeklyOtherModal"
                 onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: #dbeafe;">
                                <i class="bi bi-calendar-week" style="color: #3b82f6; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="d-block mb-1" style="color: var(--text-secondary);">Weekly Other Expenses</small>
                            <h5 class="mb-0 fw-bold" style="color: #3b82f6;">₱{{ number_format($otherTotals['weekly'] ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Monthly Other Expenses --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #10b981 !important; cursor: pointer; transition: transform 0.2s;"
                 data-bs-toggle="modal" data-bs-target="#monthlyOtherModal"
                 onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: #d1fae5;">
                                <i class="bi bi-calendar-month" style="color: #10b981; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="d-block mb-1" style="color: var(--text-secondary);">Monthly Other Expenses</small>
                            <h5 class="mb-0 fw-bold" style="color: #10b981;">₱{{ number_format($otherTotals['monthly'] ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Yearly Other Expenses --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ef4444 !important; cursor: pointer; transition: transform 0.2s;"
                 data-bs-toggle="modal" data-bs-target="#yearlyOtherModal"
                 onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: #fee2e2;">
                                <i class="bi bi-calendar-year" style="color: #ef4444; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="d-block mb-1" style="color: var(--text-secondary);">Yearly Other Expenses</small>
                            <h5 class="mb-0 fw-bold" style="color: #ef4444;">₱{{ number_format($otherTotals['yearly'] ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Inventory Summary Cards (Only show when on Inventory tab) --}}
    @if(request('type') === 'inventory')
    <div class="row mb-4 g-3">
        {{-- Total Inventory Expenses --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f59e0b !important; cursor: pointer; transition: transform 0.2s;"
                 data-bs-toggle="modal" data-bs-target="#totalInventoryModal"
                 onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: #fef3c7;">
                                <i class="bi bi-box-seam" style="color: #f59e0b; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="d-block mb-1" style="color: var(--text-secondary);">Total Inventory Expenses</small>
                            <h5 class="mb-0 fw-bold" style="color: #f59e0b;">₱{{ number_format($inventoryTotals['total'] ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Weekly Inventory Expenses --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #3b82f6 !important; cursor: pointer; transition: transform 0.2s;"
                 data-bs-toggle="modal" data-bs-target="#weeklyInventoryModal"
                 onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: #dbeafe;">
                                <i class="bi bi-calendar-week" style="color: #3b82f6; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="d-block mb-1" style="color: var(--text-secondary);">Weekly Inventory Expenses</small>
                            <h5 class="mb-0 fw-bold" style="color: #3b82f6;">₱{{ number_format($inventoryTotals['weekly'] ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Monthly Inventory Expenses --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #10b981 !important; cursor: pointer; transition: transform 0.2s;"
                 data-bs-toggle="modal" data-bs-target="#monthlyInventoryModal"
                 onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: #d1fae5;">
                                <i class="bi bi-calendar-month" style="color: #10b981; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="d-block mb-1" style="color: var(--text-secondary);">Monthly Inventory Expenses</small>
                            <h5 class="mb-0 fw-bold" style="color: #10b981;">₱{{ number_format($inventoryTotals['monthly'] ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Yearly Inventory Expenses --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #8b5cf6 !important; cursor: pointer; transition: transform 0.2s;"
                 data-bs-toggle="modal" data-bs-target="#yearlyInventoryModal"
                 onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: #f3e8ff;">
                                <i class="bi bi-calendar-year" style="color: #8b5cf6; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="d-block mb-1" style="color: var(--text-secondary);">Yearly Inventory Expenses</small>
                            <h5 class="mb-0 fw-bold" style="color: #8b5cf6;">₱{{ number_format($inventoryTotals['yearly'] ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Salary Summary Cards (Only show when on Salary tab) --}}
    @if(request('type') === 'salary')
    <div class="row mb-4 g-3">
        {{-- Total Salary Expenses --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6366f1 !important; cursor: pointer; transition: transform 0.2s;"
                 data-bs-toggle="modal" data-bs-target="#totalSalaryModal"
                 onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: #eef2ff;">
                                <i class="bi bi-cash-stack" style="color: #6366f1; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="d-block mb-1" style="color: var(--text-secondary);">Total Salary Expenses</small>
                            <h5 class="mb-0 fw-bold" style="color: #6366f1;">₱{{ number_format($salaryTotals['total'] ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Weekly Salary Expenses --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #3b82f6 !important; cursor: pointer; transition: transform 0.2s;"
                 data-bs-toggle="modal" data-bs-target="#weeklySalaryModal"
                 onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: #dbeafe;">
                                <i class="bi bi-calendar-week" style="color: #3b82f6; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="d-block mb-1" style="color: var(--text-secondary);">Weekly Staff Salary</small>
                            <h5 class="mb-0 fw-bold" style="color: #3b82f6;">₱{{ number_format($salaryTotals['weekly'] ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Monthly Salary Expenses --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #10b981 !important; cursor: pointer; transition: transform 0.2s;"
                 data-bs-toggle="modal" data-bs-target="#monthlySalaryModal"
                 onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: #d1fae5;">
                                <i class="bi bi-calendar-month" style="color: #10b981; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="d-block mb-1" style="color: var(--text-secondary);">Monthly Staff Salary</small>
                            <h5 class="mb-0 fw-bold" style="color: #10b981;">₱{{ number_format($salaryTotals['monthly'] ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Yearly Salary Expenses --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f59e0b !important; cursor: pointer; transition: transform 0.2s;"
                 data-bs-toggle="modal" data-bs-target="#yearlySalaryModal"
                 onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: #fef3c7;">
                                <i class="bi bi-calendar-year" style="color: #f59e0b; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="d-block mb-1" style="color: var(--text-secondary);">Yearly Staff Salary</small>
                            <h5 class="mb-0 fw-bold" style="color: #f59e0b;">₱{{ number_format($salaryTotals['yearly'] ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Expenses Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="p-3 border-bottom">
                <h6 class="mb-0 fw-bold">
                    @if(request('type') === 'salary' && request('period') === 'weekly')
                        📅 Weekly Salary Expense Transactions
                    @elseif(request('type') === 'salary')
                        💰 Salary Expense Transactions
                    @elseif(request('type') === 'inventory')
                        📦 Inventory Expense Transactions
                    @elseif(request('type') === 'other')
                        📋 Other Expense Transactions
                    @else
                        All Expense Transactions
                    @endif
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">Date</th>
                            <th class="border-0">Category</th>
                            <th class="border-0">Title</th>
                            @if(request('type') === 'inventory')
                                <th class="border-0">Purchase Ref</th>
                            @endif
                            <th class="border-0">Branch</th>
                            <th class="border-0">Amount</th>
                            <th class="border-0">Source</th>
                            <th class="border-0">Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                <td>
                                    @if(request('type') === 'salary')
                                        @if($expense->salary_type === 'weekly')
                                            <span class="badge" style="background: #3b82f6; color: white;">📅 Weekly Staff Salary</span>
                                        @elseif($expense->salary_type === 'monthly')
                                            <span class="badge" style="background: #10b981; color: white;">📅 Monthly Staff Salary</span>
                                        @elseif($expense->salary_type === 'yearly')
                                            <span class="badge" style="background: #f59e0b; color: white;">📅 Yearly Staff Salary</span>
                                        @else
                                            <span class="badge" style="background: #6366f1; color: white;">💰 {{ $expense->category->name ?? 'Salary' }}</span>
                                        @endif
                                    @elseif(request('type') === 'inventory')
                                        <span class="badge" style="background: #f59e0b; color: white;">📦 Inventory</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $expense->category->name ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $expense->title }}</div>
                                    @if($expense->description)
                                        <small style="color: var(--text-secondary);">{{ Str::limit($expense->description, 50) }}</small>
                                    @endif
                                </td>
                                @if(request('type') === 'inventory')
                                    <td>
                                        @if($expense->inventory_purchase_id)
                                            <span class="text-primary fw-semibold">#{{ $expense->inventory_purchase_id }}</span>
                                        @else
                                            <span style="color: var(--text-secondary);">-</span>
                                        @endif
                                    </td>
                                @endif
                                <td>{{ $expense->branch->name ?? 'N/A' }}</td>
                                <td class="fw-semibold text-danger">₱{{ number_format($expense->amount, 2) }}</td>
                                <td>
                                    <span class="badge" style="background-color: {{ $expense->source === 'auto' ? '#3b82f6' : '#8b5cf6' }}">
                                        {{ $expense->source === 'auto' ? '🤖 Auto' : '✋ Manual' }}
                                    </span>
                                </td>
                                <td>
                                    <small style="color: var(--text-secondary);">{{ $expense->creator->name ?? 'System' }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ request('type') === 'inventory' ? '8' : '7' }}" class="text-center py-4" style="color: var(--text-secondary);">
                                    @if(request('type') === 'salary')
                                        No salary expenses found
                                    @elseif(request('type') === 'inventory')
                                        No inventory expenses found
                                    @elseif(request('type') === 'other')
                                        No other expenses found
                                    @else
                                        No expenses found
                                    @endif
                                </td>
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

{{-- Other Expense Modals --}}

{{-- Total Other Modal --}}
<div class="modal fade" id="totalOtherModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #8b5cf6; color: white;">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Total Other Expenses</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Title</th>
                                <th>Branch</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $salaryCat = $salaryCategory ?? \App\Models\ExpenseCategory::where('slug', 'salaries')->first();
                                $totalOtherExpenses = \App\Models\Expense::whereNull('inventory_purchase_id')
                                    ->where(function($q) use ($salaryCat) {
                                        if ($salaryCat) {
                                            $q->where('expense_category_id', '!=', $salaryCat->id);
                                        }
                                    })
                                    ->when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                                    ->when(request('date_from') && request('date_to'), fn($q) => $q->whereBetween('expense_date', [request('date_from'), request('date_to')]))
                                    ->with(['branch', 'category'])
                                    ->orderBy('expense_date', 'desc')
                                    ->get();
                            @endphp
                            @forelse($totalOtherExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td><span class="badge bg-secondary">{{ $expense->category->name ?? 'N/A' }}</span></td>
                                    <td>{{ $expense->title }}</td>
                                    <td>{{ $expense->branch->name ?? 'N/A' }}</td>
                                    <td class="fw-bold" style="color: #8b5cf6;">₱{{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4" style="color: var(--text-secondary);">No other expenses found</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($totalOtherExpenses->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th style="color: #8b5cf6;">₱{{ number_format($totalOtherExpenses->sum('amount'), 2) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Weekly Other Modal --}}
<div class="modal fade" id="weeklyOtherModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #3b82f6; color: white;">
                <h5 class="modal-title"><i class="bi bi-calendar-week me-2"></i>Weekly Other Expenses</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>Showing expenses from {{ now()->startOfWeek()->format('M d') }} to {{ now()->endOfWeek()->format('M d, Y') }}
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Title</th>
                                <th>Branch</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $salaryCat = $salaryCategory ?? \App\Models\ExpenseCategory::where('slug', 'salaries')->first();
                                $weeklyOtherExpenses = \App\Models\Expense::whereNull('inventory_purchase_id')
                                    ->where(function($q) use ($salaryCat) {
                                        if ($salaryCat) {
                                            $q->where('expense_category_id', '!=', $salaryCat->id);
                                        }
                                    })
                                    ->whereBetween('expense_date', [now()->startOfWeek(), now()->endOfWeek()])
                                    ->when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                                    ->with(['branch', 'category'])
                                    ->orderBy('expense_date', 'desc')
                                    ->get();
                            @endphp
                            @forelse($weeklyOtherExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td><span class="badge bg-secondary">{{ $expense->category->name ?? 'N/A' }}</span></td>
                                    <td>{{ $expense->title }}</td>
                                    <td>{{ $expense->branch->name ?? 'N/A' }}</td>
                                    <td class="fw-bold text-primary">₱{{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4" style="color: var(--text-secondary);">No other expenses this week</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($weeklyOtherExpenses->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th class="text-primary">₱{{ number_format($weeklyOtherExpenses->sum('amount'), 2) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Monthly Other Modal --}}
<div class="modal fade" id="monthlyOtherModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #10b981; color: white;">
                <h5 class="modal-title"><i class="bi bi-calendar-month me-2"></i>Monthly Other Expenses</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>Showing expenses for {{ now()->format('F Y') }}
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Title</th>
                                <th>Branch</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $salaryCat = $salaryCategory ?? \App\Models\ExpenseCategory::where('slug', 'salaries')->first();
                                $monthlyOtherExpenses = \App\Models\Expense::whereNull('inventory_purchase_id')
                                    ->where(function($q) use ($salaryCat) {
                                        if ($salaryCat) {
                                            $q->where('expense_category_id', '!=', $salaryCat->id);
                                        }
                                    })
                                    ->whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()])
                                    ->when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                                    ->with(['branch', 'category'])
                                    ->orderBy('expense_date', 'desc')
                                    ->get();
                            @endphp
                            @forelse($monthlyOtherExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td><span class="badge bg-secondary">{{ $expense->category->name ?? 'N/A' }}</span></td>
                                    <td>{{ $expense->title }}</td>
                                    <td>{{ $expense->branch->name ?? 'N/A' }}</td>
                                    <td class="fw-bold text-success">₱{{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4" style="color: var(--text-secondary);">No other expenses this month</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($monthlyOtherExpenses->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th class="text-success">₱{{ number_format($monthlyOtherExpenses->sum('amount'), 2) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Yearly Other Modal --}}
<div class="modal fade" id="yearlyOtherModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #ef4444; color: white;">
                <h5 class="modal-title"><i class="bi bi-calendar-year me-2"></i>Yearly Other Expenses</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>Showing expenses for {{ now()->format('Y') }}
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Title</th>
                                <th>Branch</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $salaryCat = $salaryCategory ?? \App\Models\ExpenseCategory::where('slug', 'salaries')->first();
                                $yearlyOtherExpenses = \App\Models\Expense::whereNull('inventory_purchase_id')
                                    ->where(function($q) use ($salaryCat) {
                                        if ($salaryCat) {
                                            $q->where('expense_category_id', '!=', $salaryCat->id);
                                        }
                                    })
                                    ->whereBetween('expense_date', [now()->startOfYear(), now()->endOfYear()])
                                    ->when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                                    ->with(['branch', 'category'])
                                    ->orderBy('expense_date', 'desc')
                                    ->get();
                            @endphp
                            @forelse($yearlyOtherExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td><span class="badge bg-secondary">{{ $expense->category->name ?? 'N/A' }}</span></td>
                                    <td>{{ $expense->title }}</td>
                                    <td>{{ $expense->branch->name ?? 'N/A' }}</td>
                                    <td class="fw-bold text-danger">₱{{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4" style="color: var(--text-secondary);">No other expenses this year</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($yearlyOtherExpenses->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th class="text-danger">₱{{ number_format($yearlyOtherExpenses->sum('amount'), 2) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Inventory Expense Modals --}}

{{-- Total Inventory Modal --}}
<div class="modal fade" id="totalInventoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #f59e0b; color: white;">
                <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Total Inventory Expenses</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Purchase Ref</th>
                                <th>Supplier</th>
                                <th>Branch</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalInventoryExpenses = \App\Models\Expense::whereNotNull('inventory_purchase_id')
                                    ->when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                                    ->when(request('date_from') && request('date_to'), fn($q) => $q->whereBetween('expense_date', [request('date_from'), request('date_to')]))
                                    ->with(['branch', 'inventoryPurchase'])
                                    ->orderBy('expense_date', 'desc')
                                    ->get();
                            @endphp
                            @forelse($totalInventoryExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($expense->inventory_purchase_id)
                                            <span class="badge bg-primary">#{{ $expense->inventory_purchase_id }}</span>
                                        @else
                                            <span style="color: var(--text-secondary);">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $expense->inventoryPurchase->supplier?->name ?? 'N/A' }}</td>
                                    <td>{{ $expense->branch->name ?? 'N/A' }}</td>
                                    <td class="fw-bold text-warning">₱{{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4" style="color: var(--text-secondary);">No inventory expenses found</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($totalInventoryExpenses->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th class="text-warning">₱{{ number_format($totalInventoryExpenses->sum('amount'), 2) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Weekly Inventory Modal --}}
<div class="modal fade" id="weeklyInventoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #3b82f6; color: white;">
                <h5 class="modal-title"><i class="bi bi-calendar-week me-2"></i>Weekly Inventory Expenses</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>Showing expenses from {{ now()->startOfWeek()->format('M d') }} to {{ now()->endOfWeek()->format('M d, Y') }}
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Purchase Ref</th>
                                <th>Supplier</th>
                                <th>Branch</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $weeklyInventoryExpenses = \App\Models\Expense::whereNotNull('inventory_purchase_id')
                                    ->whereBetween('expense_date', [now()->startOfWeek(), now()->endOfWeek()])
                                    ->when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                                    ->with(['branch', 'inventoryPurchase'])
                                    ->orderBy('expense_date', 'desc')
                                    ->get();
                            @endphp
                            @forelse($weeklyInventoryExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($expense->inventory_purchase_id)
                                            <span class="badge bg-primary">#{{ $expense->inventory_purchase_id }}</span>
                                        @else
                                            <span style="color: var(--text-secondary);">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $expense->inventoryPurchase->supplier?->name ?? 'N/A' }}</td>
                                    <td>{{ $expense->branch->name ?? 'N/A' }}</td>
                                    <td class="fw-bold text-primary">₱{{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4" style="color: var(--text-secondary);">No inventory expenses this week</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($weeklyInventoryExpenses->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th class="text-primary">₱{{ number_format($weeklyInventoryExpenses->sum('amount'), 2) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Monthly Inventory Modal --}}
<div class="modal fade" id="monthlyInventoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #10b981; color: white;">
                <h5 class="modal-title"><i class="bi bi-calendar-month me-2"></i>Monthly Inventory Expenses</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>Showing expenses for {{ now()->format('F Y') }}
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Purchase Ref</th>
                                <th>Supplier</th>
                                <th>Branch</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $monthlyInventoryExpenses = \App\Models\Expense::whereNotNull('inventory_purchase_id')
                                    ->whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()])
                                    ->when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                                    ->with(['branch', 'inventoryPurchase'])
                                    ->orderBy('expense_date', 'desc')
                                    ->get();
                            @endphp
                            @forelse($monthlyInventoryExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($expense->inventory_purchase_id)
                                            <span class="badge bg-primary">#{{ $expense->inventory_purchase_id }}</span>
                                        @else
                                            <span style="color: var(--text-secondary);">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $expense->inventoryPurchase->supplier?->name ?? 'N/A' }}</td>
                                    <td>{{ $expense->branch->name ?? 'N/A' }}</td>
                                    <td class="fw-bold text-success">₱{{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4" style="color: var(--text-secondary);">No inventory expenses this month</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($monthlyInventoryExpenses->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th class="text-success">₱{{ number_format($monthlyInventoryExpenses->sum('amount'), 2) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Yearly Inventory Modal --}}
<div class="modal fade" id="yearlyInventoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #8b5cf6; color: white;">
                <h5 class="modal-title"><i class="bi bi-calendar-year me-2"></i>Yearly Inventory Expenses</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>Showing expenses for {{ now()->format('Y') }}
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Purchase Ref</th>
                                <th>Supplier</th>
                                <th>Branch</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $yearlyInventoryExpenses = \App\Models\Expense::whereNotNull('inventory_purchase_id')
                                    ->whereBetween('expense_date', [now()->startOfYear(), now()->endOfYear()])
                                    ->when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                                    ->with(['branch', 'inventoryPurchase'])
                                    ->orderBy('expense_date', 'desc')
                                    ->get();
                            @endphp
                            @forelse($yearlyInventoryExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($expense->inventory_purchase_id)
                                            <span class="badge bg-primary">#{{ $expense->inventory_purchase_id }}</span>
                                        @else
                                            <span style="color: var(--text-secondary);">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $expense->inventoryPurchase->supplier?->name ?? 'N/A' }}</td>
                                    <td>{{ $expense->branch->name ?? 'N/A' }}</td>
                                    <td class="fw-bold" style="color: #8b5cf6;">₱{{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4" style="color: var(--text-secondary);">No inventory expenses this year</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($yearlyInventoryExpenses->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th style="color: #8b5cf6;">₱{{ number_format($yearlyInventoryExpenses->sum('amount'), 2) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modals for Salary Details --}}

{{-- Total Salary Modal --}}
<div class="modal fade" id="totalSalaryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #6366f1; color: white;">
                <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i>Total Salary Expenses</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Branch</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $salaryCat = $salaryCategory ?? \App\Models\ExpenseCategory::where('slug', 'salaries')->first();
                                $totalSalaryExpenses = \App\Models\Expense::where('expense_category_id', $salaryCat->id ?? null)
                                    ->when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                                    ->when(request('date_from') && request('date_to'), fn($q) => $q->whereBetween('expense_date', [request('date_from'), request('date_to')]))
                                    ->with(['branch', 'creator'])
                                    ->orderBy('expense_date', 'desc')
                                    ->get();
                            @endphp
                            @forelse($totalSalaryExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td>{{ $expense->title }}</td>
                                    <td>{{ $expense->branch->name ?? 'N/A' }}</td>
                                    <td class="fw-bold text-danger">₱{{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4" style="color: var(--text-secondary);">No salary expenses found</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($totalSalaryExpenses->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th class="text-danger">₱{{ number_format($totalSalaryExpenses->sum('amount'), 2) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Weekly Salary Modal --}}
<div class="modal fade" id="weeklySalaryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #3b82f6; color: white;">
                <h5 class="modal-title"><i class="bi bi-calendar-week me-2"></i>Weekly Staff Salary Expenses</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Branch</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $salaryCat = $salaryCategory ?? \App\Models\ExpenseCategory::where('slug', 'salaries')->first();
                                $weeklySalaryExpenses = \App\Models\Expense::where('expense_category_id', $salaryCat->id ?? null)
                                    ->where('salary_type', 'weekly')
                                    ->when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                                    ->with(['branch', 'creator'])
                                    ->orderBy('expense_date', 'desc')
                                    ->get();
                            @endphp
                            @forelse($weeklySalaryExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td>{{ $expense->title }}</td>
                                    <td>{{ $expense->branch->name ?? 'N/A' }}</td>
                                    <td class="fw-bold text-primary">₱{{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4" style="color: var(--text-secondary);">No weekly staff salary expenses found</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($weeklySalaryExpenses->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th class="text-primary">₱{{ number_format($weeklySalaryExpenses->sum('amount'), 2) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Monthly Salary Modal --}}
<div class="modal fade" id="monthlySalaryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #10b981; color: white;">
                <h5 class="modal-title"><i class="bi bi-calendar-month me-2"></i>Monthly Staff Salary Expenses</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Branch</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $salaryCat = $salaryCategory ?? \App\Models\ExpenseCategory::where('slug', 'salaries')->first();
                                $monthlySalaryExpenses = \App\Models\Expense::where('expense_category_id', $salaryCat->id ?? null)
                                    ->where('salary_type', 'monthly')
                                    ->when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                                    ->with(['branch', 'creator'])
                                    ->orderBy('expense_date', 'desc')
                                    ->get();
                            @endphp
                            @forelse($monthlySalaryExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td>{{ $expense->title }}</td>
                                    <td>{{ $expense->branch->name ?? 'N/A' }}</td>
                                    <td class="fw-bold text-success">₱{{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4" style="color: var(--text-secondary);">No monthly staff salary expenses found</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($monthlySalaryExpenses->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th class="text-success">₱{{ number_format($monthlySalaryExpenses->sum('amount'), 2) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Yearly Salary Modal --}}
<div class="modal fade" id="yearlySalaryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #f59e0b; color: white;">
                <h5 class="modal-title"><i class="bi bi-calendar-year me-2"></i>Yearly Staff Salary Expenses</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Branch</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $salaryCat = $salaryCategory ?? \App\Models\ExpenseCategory::where('slug', 'salaries')->first();
                                $yearlySalaryExpenses = \App\Models\Expense::where('expense_category_id', $salaryCat->id ?? null)
                                    ->where('salary_type', 'yearly')
                                    ->when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                                    ->with(['branch', 'creator'])
                                    ->orderBy('expense_date', 'desc')
                                    ->get();
                            @endphp
                            @forelse($yearlySalaryExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td>{{ $expense->title }}</td>
                                    <td>{{ $expense->branch->name ?? 'N/A' }}</td>
                                    <td class="fw-bold text-warning">₱{{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4" style="color: var(--text-secondary);">No yearly staff salary expenses found</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($yearlySalaryExpenses->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th class="text-warning">₱{{ number_format($yearlySalaryExpenses->sum('amount'), 2) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
