@extends('admin.layouts.app')

@section('title', $branch->name . ' - Analytics')
@section('page-title', 'BRANCH ANALYTICS')

@section('content')
<div class="container-fluid px-4 py-2">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">{{ $branch->name }} Analytics</h4>
            <p class="small mb-0" style="color: var(--text-secondary);">{{ $branch->address }}, {{ $branch->city }}</p>
        </div>
        <div>
            <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Branches
            </a>
        </div>
    </div>

    {{-- Date Range Filter --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.branches.analytics', $branch->id) }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" 
                           value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" 
                           value="{{ request('end_date', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-funnel me-1"></i>Apply Filter
                    </button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.branches.analytics', $branch->id) }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-2 mb-3">
        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-primary border-3">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="bg-primary bg-opacity-10 p-1 rounded" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-basket3 text-primary" style="font-size:0.9rem;"></i>
                        </div>
                    </div>
                    <h6 class="mb-1" style="font-size:0.65rem; color: var(--text-secondary);">Total Laundries</h6>
                    <h3 class="fw-bold mb-0" style="font-size:1.3rem; color: var(--text-primary);">{{ number_format($stats['total_laundries']) }}</h3>
                    <small style="font-size:0.6rem; color: var(--text-secondary);">{{ $stats['completed_laundries'] }} completed</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-success border-3">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="bg-success bg-opacity-10 p-1 rounded" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-cash-stack text-success" style="font-size:0.9rem;"></i>
                        </div>
                    </div>
                    <h6 class="mb-1" style="font-size:0.65rem; color: var(--text-secondary);">Total Revenue</h6>
                    <h3 class="fw-bold mb-0 text-success" style="font-size:1.3rem;">₱{{ number_format($stats['total_revenue'], 0) }}</h3>
                    <small style="font-size:0.6rem; color: var(--text-secondary);">Laundry + Retail</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-info border-3">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="bg-info bg-opacity-10 p-1 rounded" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-people text-info" style="font-size:0.9rem;"></i>
                        </div>
                    </div>
                    <h6 class="mb-1" style="font-size:0.65rem; color: var(--text-secondary);">Total Customers</h6>
                    <h3 class="fw-bold mb-0 text-info" style="font-size:1.3rem;">{{ number_format($stats['total_customers']) }}</h3>
                    <small style="font-size:0.6rem; color: var(--text-secondary);">Unique customers</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-warning border-3">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="bg-warning bg-opacity-10 p-1 rounded" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-cart-check text-warning" style="font-size:0.9rem;"></i>
                        </div>
                    </div>
                    <h6 class="mb-1" style="font-size:0.65rem; color: var(--text-secondary);">Retail Sales</h6>
                    <h3 class="fw-bold mb-0 text-warning" style="font-size:1.3rem;">₱{{ number_format($stats['retail_sales'], 0) }}</h3>
                    <small style="font-size:0.6rem; color: var(--text-secondary);">{{ $stats['retail_count'] }} transactions</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-purple border-3">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="bg-purple bg-opacity-10 p-1 rounded" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-graph-up text-purple" style="font-size:0.9rem;"></i>
                        </div>
                    </div>
                    <h6 class="mb-1" style="font-size:0.65rem; color: var(--text-secondary);">Total Profit</h6>
                    <h3 class="fw-bold mb-0" style="font-size:1.3rem;color:#8b5cf6;">₱{{ number_format($stats['total_profit'], 0) }}</h3>
                    <small style="font-size:0.6rem; color: var(--text-secondary);">{{ $stats['profit_margin'] }}% margin</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-danger border-3">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="bg-danger bg-opacity-10 p-1 rounded" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-receipt text-danger" style="font-size:0.9rem;"></i>
                        </div>
                    </div>
                    <h6 class="mb-1" style="font-size:0.65rem; color: var(--text-secondary);">Total Expenses</h6>
                    <h3 class="fw-bold mb-0 text-danger" style="font-size:1.3rem;">₱{{ number_format($stats['total_expenses'], 0) }}</h3>
                    <small style="font-size:0.6rem; color: var(--text-secondary);">{{ $stats['expense_count'] }} entries</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 1 --}}
    <div class="row g-3 mb-3">
        {{-- Revenue Trend Line Chart --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="font-size:0.85rem;">
                        <i class="bi bi-graph-up text-success me-2"></i>Revenue Trend
                    </h6>
                    <div style="height:280px;">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Revenue Breakdown Pie Chart --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="font-size:0.85rem;">
                        <i class="bi bi-pie-chart text-primary me-2"></i>Revenue Breakdown
                    </h6>
                    <div style="height:280px;display:flex;align-items:center;justify-content:center;">
                        <canvas id="revenueBreakdownChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 2 --}}
    <div class="row g-3 mb-3">
        {{-- Service Performance Bar Chart --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="font-size:0.85rem;">
                        <i class="bi bi-bar-chart text-info me-2"></i>Service Performance
                    </h6>
                    <div style="height:280px;">
                        <canvas id="servicePerformanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daily Orders Line Chart --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="font-size:0.85rem;">
                        <i class="bi bi-calendar3 text-warning me-2"></i>Daily Orders
                    </h6>
                    <div style="height:280px;">
                        <canvas id="dailyOrdersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 3 --}}
    <div class="row g-3 mb-3">
        {{-- Expense Categories Pie Chart --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="font-size:0.85rem;">
                        <i class="bi bi-pie-chart-fill text-danger me-2"></i>Expense Categories
                    </h6>
                    <div style="height:280px;display:flex;align-items:center;justify-content:center;">
                        <canvas id="expenseCategoriesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Customers Table --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="font-size:0.85rem;">
                        <i class="bi bi-trophy text-warning me-2"></i>Top Customers
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" style="font-size:0.75rem;">
                            <thead>
                                <tr style="background:rgba(0,0,0,0.02);">
                                    <th style="padding:8px;">Rank</th>
                                    <th style="padding:8px;">Customer</th>
                                    <th class="text-center" style="padding:8px;">Orders</th>
                                    <th class="text-end" style="padding:8px;">Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCustomers as $index => $customer)
                                <tr>
                                    <td style="padding:8px;">
                                        <span class="badge" style="background:{{ $index == 0 ? '#fbbf24' : ($index == 1 ? '#94a3b8' : '#cd7f32') }};">
                                            #{{ $index + 1 }}
                                        </span>
                                    </td>
                                    <td style="padding:8px;font-weight:500;">{{ $customer->name }}</td>
                                    <td class="text-center" style="padding:8px;">
                                        <span class="badge bg-primary">{{ $customer->order_count }}</span>
                                    </td>
                                    <td class="text-end" style="padding:8px;">
                                        <span class="fw-bold text-success">₱{{ number_format($customer->total_spent, 2) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/chart.js/chart.umd.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = @json($chartData);

    // Revenue Trend Line Chart
    new Chart(document.getElementById('revenueTrendChart'), {
        type: 'line',
        data: {
            labels: chartData.revenueTrend.labels,
            datasets: [
                {
                    label: 'Laundry Revenue',
                    data: chartData.revenueTrend.laundry,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Retail Sales',
                    data: chartData.revenueTrend.retail,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Revenue Breakdown Pie Chart
    new Chart(document.getElementById('revenueBreakdownChart'), {
        type: 'doughnut',
        data: {
            labels: ['Laundry Services', 'Retail Sales'],
            datasets: [{
                data: [chartData.revenueBreakdown.laundry, chartData.revenueBreakdown.retail],
                backgroundColor: ['#10b981', '#f59e0b']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Service Performance Bar Chart
    new Chart(document.getElementById('servicePerformanceChart'), {
        type: 'bar',
        data: {
            labels: chartData.servicePerformance.labels,
            datasets: [{
                label: 'Orders',
                data: chartData.servicePerformance.orders,
                backgroundColor: '#3b82f6'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Daily Orders Line Chart
    new Chart(document.getElementById('dailyOrdersChart'), {
        type: 'line',
        data: {
            labels: chartData.dailyOrders.labels,
            datasets: [{
                label: 'Orders',
                data: chartData.dailyOrders.data,
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Expense Categories Pie Chart
    new Chart(document.getElementById('expenseCategoriesChart'), {
        type: 'pie',
        data: {
            labels: chartData.expenseCategories.labels,
            datasets: [{
                data: chartData.expenseCategories.data,
                backgroundColor: ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.border-purple { border-color: #8b5cf6 !important; }
.bg-purple { background-color: #8b5cf6 !important; }
.text-purple { color: #8b5cf6 !important; }
.bg-purple.bg-opacity-10 { background-color: rgba(139, 92, 246, 0.1) !important; }
.card {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}
.card-body {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
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
</style>
@endpush
@endsection
