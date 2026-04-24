@extends('admin.layouts.app')

@section('title', 'Performance Report')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="bg-primary text-white rounded p-2">
                    <i class="bi bi-graph-up-arrow fs-5"></i>
                </div>
                <div>
                    <h2 class="mb-0 fw-bold">Performance Report</h2>
                    <p class="text-muted mb-0 small">
                        @if($selectedBranch)
                            {{ $branches->firstWhere('id', $selectedBranch)->name ?? 'All Branches' }} - 
                        @endif
                        Comprehensive Analytics & Metrics
                    </p>
                </div>
            </div>
        </div>
        <div class="text-end">
            <small class="text-muted d-block">Last Updated</small>
            <strong>{{ now()->format('m/d/Y') }}</strong>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4" style="background: var(--card-bg); border-color: var(--border-color);">
        <div class="card-body" style="color: var(--text-primary);">
            <form method="GET" action="{{ route('admin.performance-report') }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">
                            <i class="bi bi-building"></i> Branch
                        </label>
                        <select class="form-select" name="branch_id" id="branchFilter">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $selectedBranch == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">
                            <i class="bi bi-calendar-range"></i> Date Range
                        </label>
                        <select class="form-select" name="date_range" id="dateRangeFilter">
                            <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ $dateRange == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="this_week" {{ $dateRange == 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="last_week" {{ $dateRange == 'last_week' ? 'selected' : '' }}>Last Week</option>
                            <option value="this_month" {{ $dateRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month" {{ $dateRange == 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="this_year" {{ $dateRange == 'this_year' ? 'selected' : '' }}>This Year</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">
                            <i class="bi bi-funnel"></i> View Type
                        </label>
                        <select class="form-select" name="view_type" id="viewTypeFilter">
                            <option value="summary" {{ $viewType == 'summary' ? 'selected' : '' }}>Summary View</option>
                            <option value="detailed" {{ $viewType == 'detailed' ? 'selected' : '' }}>Detailed View</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Actions</label>
                        <div class="btn-group w-100">
                            <button type="button" class="btn btn-outline-primary" title="Refresh" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-outline-success" title="Export" onclick="window.print()">
                                <i class="bi bi-download"></i> Export
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs nav-fill mb-4" id="reportTabs" role="tablist" style="background: var(--card-bg); border-color: var(--border-color);">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="executive-tab" data-bs-toggle="tab" data-bs-target="#executive" type="button" style="color: var(--text-primary);">
                <i class="bi bi-speedometer2"></i> Executive
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="operations-tab" data-bs-toggle="tab" data-bs-target="#operations" type="button" style="color: var(--text-primary);">
                <i class="bi bi-gear-fill"></i> Operations
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="financial-tab" data-bs-toggle="tab" data-bs-target="#financial" type="button" style="color: var(--text-primary);">
                <i class="bi bi-currency-dollar"></i> Financial
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" style="color: var(--text-primary);">
                <i class="bi bi-box-seam"></i> Inventory
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff" type="button" style="color: var(--text-primary);">
                <i class="bi bi-people-fill"></i> Staff
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="customers-tab" data-bs-toggle="tab" data-bs-target="#customers" type="button" style="color: var(--text-primary);">
                <i class="bi bi-person-circle"></i> Customers
            </button>
        </li>
    </ul>

    <style>
        /* Performance Report Tab Styling for Light/Dark Mode */
        #reportTabs .nav-link {
            color: var(--text-primary) !important;
            background: transparent;
            border-color: var(--border-color);
        }
        
        #reportTabs .nav-link:hover {
            color: var(--primary-color) !important;
            background: var(--border-color);
        }
        
        #reportTabs .nav-link.active {
            color: var(--primary-color) !important;
            background: var(--card-bg);
            border-color: var(--border-color) var(--border-color) var(--card-bg);
        }
    </style>

    <!-- Tab Content -->
    <div class="tab-content" id="reportTabsContent">
        <!-- Executive Tab -->
        <div class="tab-pane fade show active" id="executive" role="tabpanel">
            @include('admin.performance-report.executive', ['data' => $executive, 'selectedBranch' => $selectedBranch, 'dates' => $dates, 'dateRange' => $dateRange])
        </div>

        <!-- Operations Tab -->
        <div class="tab-pane fade" id="operations" role="tabpanel">
            @include('admin.performance-report.operations', ['data' => $operations, 'selectedBranch' => $selectedBranch, 'dates' => $dates, 'dateRange' => $dateRange])
        </div>

        <!-- Financial Tab -->
        <div class="tab-pane fade" id="financial" role="tabpanel">
            @include('admin.performance-report.financial', ['data' => $financial, 'selectedBranch' => $selectedBranch, 'dates' => $dates, 'dateRange' => $dateRange])
        </div>

        <!-- Inventory Tab -->
        <div class="tab-pane fade" id="inventory" role="tabpanel">
            @include('admin.performance-report.inventory', ['data' => $inventory, 'selectedBranch' => $selectedBranch, 'dates' => $dates, 'dateRange' => $dateRange])
        </div>

        <!-- Staff Tab -->
        <div class="tab-pane fade" id="staff" role="tabpanel">
            @include('admin.performance-report.staff', ['data' => $staff, 'selectedBranch' => $selectedBranch, 'dates' => $dates, 'dateRange' => $dateRange])
        </div>

        <!-- Customers Tab -->
        <div class="tab-pane fade" id="customers" role="tabpanel">
            @include('admin.performance-report.customers', ['data' => $customers, 'selectedBranch' => $selectedBranch, 'dates' => $dates, 'dateRange' => $dateRange])
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Ensure charts render properly */
canvas {
    max-height: 100% !important;
}
</style>
@endpush

@push('scripts')
<!-- Chart.js Library -->
<script src="{{ asset('assets/chart.js/chart.umd.min.js') }}"></script>

<script>
// Wait for Chart.js to load
if (typeof Chart === 'undefined') {
    console.error('Chart.js is not loaded!');
} else {
    console.log('Chart.js loaded successfully');
}

document.getElementById('branchFilter').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

document.getElementById('dateRangeFilter').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

document.getElementById('viewTypeFilter').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
</script>
@endpush
