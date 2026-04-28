@extends('admin.layouts.app')

@section('title', 'Financial Dashboard — WashBox')
@section('page-title', 'Finance')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root {
    /* Typography */
    --font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    --font-size-xs: 0.75rem;
    --font-size-sm: 0.875rem;
    --font-size-base: 1rem;
    --font-size-lg: 1.125rem;
    --font-size-xl: 1.25rem;
}

body {
    font-family: var(--font-family);
    font-size: var(--font-size-base);
}

.growth-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: var(--font-size-sm);
    font-weight: 600;
}
.growth-positive { color: #10b981; }
.growth-negative { color: #ef4444; }
.growth-neutral { color: #6b7280; }
.metric-card {
    background: var(--card-bg);
    border: 1px solid var(--table-border);
    border-radius: 8px;
    padding: 1.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
}
.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.metric-card .text-muted.small {
    font-size: var(--font-size-sm);
}
.metric-card h3 {
    font-size: var(--font-size-xl);
}
.metric-card small {
    font-size: var(--font-size-xs);
}
.alert-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: var(--font-size-xs);
    font-weight: 600;
}
.alert-warning { background: #fef3c7; color: #92400e; }
.alert-danger { background: #fee2e2; color: #991b1b; }

.inventory-card-header h5 {
    font-size: var(--font-size-lg);
}

.inventory-card-body .fw-semibold {
    font-size: var(--font-size-base);
}

.inventory-card-body small,
.inventory-card-body .text-muted {
    font-size: var(--font-size-sm);
}

.inventory-table th,
.inventory-table td {
    font-size: var(--font-size-sm);
}
</style>
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Financial Overview</h2>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <select name="branch_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                <select name="period" class="form-select" onchange="this.form.submit()">
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                    <option value="year" {{ $period === 'year' ? 'selected' : '' }}>This Year</option>
                </select>
            </form>
        </div>
    </div>

    {{-- KPI Cards with Growth Indicators --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Today's Cash In</span>
                    <i class="bi bi-arrow-down-circle text-success"></i>
                </div>
                <h3 class="mb-1 text-success">₱{{ number_format($cashFlowSummary['today_cash_in'], 2) }}</h3>
                <small class="text-muted">Sales & Revenue</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Today's Cash Out</span>
                    <i class="bi bi-arrow-up-circle text-danger"></i>
                </div>
                <h3 class="mb-1 text-danger">₱{{ number_format($cashFlowSummary['today_cash_out'], 2) }}</h3>
                <small class="text-muted">Expenses & Purchases</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Net Cash Flow</span>
                    <i class="bi bi-arrow-{{ $cashFlowSummary['today_net_cash_flow'] >= 0 ? 'up' : 'down' }}-circle text-{{ $cashFlowSummary['today_net_cash_flow'] >= 0 ? 'success' : 'danger' }}"></i>
                </div>
                <h3 class="mb-1 text-{{ $cashFlowSummary['today_net_cash_flow'] >= 0 ? 'success' : 'danger' }}">₱{{ number_format($cashFlowSummary['today_net_cash_flow'], 2) }}</h3>
                <small class="text-muted">Today's Net</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Current Balance</span>
                    <i class="bi bi-cash-stack text-primary"></i>
                </div>
                <h3 class="mb-1">₱{{ number_format($cashFlowSummary['latest_balance'], 2) }}</h3>
                <small class="text-muted">Cumulative Balance</small>
            </div>
        </div>
    </div>

    {{-- Period Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Total Revenue</span>
                    <span class="growth-indicator growth-{{ $summary['salesGrowth'] >= 0 ? 'positive' : 'negative' }}">
                        <i class="bi bi-arrow-{{ $summary['salesGrowth'] >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($summary['salesGrowth']) }}%
                    </span>
                </div>
                <h3 class="mb-1">₱{{ number_format($summary['totalSales'], 2) }}</h3>
                <small class="text-muted">{{ $summary['laundryCount'] + $summary['retailCount'] }} transactions</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Total Expenses</span>
                    <span class="growth-indicator growth-{{ $summary['expenseGrowth'] <= 0 ? 'positive' : 'negative' }}">
                        <i class="bi bi-arrow-{{ $summary['expenseGrowth'] >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($summary['expenseGrowth']) }}%
                    </span>
                </div>
                <h3 class="mb-1 text-danger">₱{{ number_format($summary['totalCosts'], 2) }}</h3>
                <small class="text-muted">Operating & Inventory</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Net Profit</span>
                    <span class="growth-indicator growth-{{ $summary['profitGrowth'] >= 0 ? 'positive' : 'negative' }}">
                        <i class="bi bi-arrow-{{ $summary['profitGrowth'] >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($summary['profitGrowth']) }}%
                    </span>
                </div>
                <h3 class="mb-1 text-{{ $summary['netProfit'] >= 0 ? 'success' : 'danger' }}">₱{{ number_format($summary['netProfit'], 2) }}</h3>
                <small class="text-muted">{{ $summary['profitMargin'] }}% margin</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Period Cash Flow</span>
                    <i class="bi bi-graph-up text-info"></i>
                </div>
                <h3 class="mb-1 text-{{ $cashFlowSummary['net_cash_flow'] >= 0 ? 'success' : 'danger' }}">₱{{ number_format($cashFlowSummary['net_cash_flow'], 2) }}</h3>
                <small class="text-muted">{{ $dates['label'] }}</small>
            </div>
        </div>
    </div>

    {{-- Quick Access Buttons --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="{{ route('admin.finance.ledger.index') }}" class="inventory-card text-decoration-none d-block h-100">
                <div class="inventory-card-body text-center py-4">
                    <i class="bi bi-journal-text fs-1 text-primary mb-3"></i>
                    <h6 class="mb-1">Financial Ledger</h6>
                    <small class="text-muted">View all transactions</small>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.finance.cash-flow.index') }}" class="inventory-card text-decoration-none d-block h-100">
                <div class="inventory-card-body text-center py-4">
                    <i class="bi bi-cash-stack fs-1 text-warning mb-3"></i>
                    <h6 class="mb-1">Cash Flow</h6>
                    <small class="text-muted">Track cash flow</small>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.finance.audit-logs.index') }}" class="inventory-card text-decoration-none d-block h-100">
                <div class="inventory-card-body text-center py-4">
                    <i class="bi bi-shield-check fs-1 text-danger mb-3"></i>
                    <h6 class="mb-1">Audit Logs</h6>
                    <small class="text-muted">View audit trail</small>
                </div>
            </a>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="inventory-card">
                <div class="inventory-card-header">
                    <h5 class="mb-0">Revenue & Expense Trend</h5>
                </div>
                <div class="inventory-card-body">
                    <canvas id="profitTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="inventory-card">
                <div class="inventory-card-header">
                    <h5 class="mb-0">Payment Methods</h5>
                </div>
                <div class="inventory-card-body">
                    <canvas id="paymentMethodChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Services & Expense Breakdown --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="inventory-card">
                <div class="inventory-card-header">
                    <h5 class="mb-0">Top Services</h5>
                </div>
                <div class="inventory-card-body">
                    @forelse($topServices as $service)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="fw-semibold">{{ $service['name'] }}</div>
                                <small class="text-muted">{{ $service['count'] }} orders</small>
                            </div>
                            <div class="fw-bold text-success">₱{{ number_format($service['revenue'], 2) }}</div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4 mb-0">No service data</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="inventory-card">
                <div class="inventory-card-header">
                    <h5 class="mb-0">Top Expense Categories</h5>
                </div>
                <div class="inventory-card-body">
                    @forelse($topExpenseCategories as $expense)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $expense['category'] }}</div>
                                <div class="progress mt-1" style="height: 6px;">
                                    @php
                                        $maxAmount = $topExpenseCategories?->max('amount') ?? 0;
                                        $percentage = $maxAmount > 0 ? ($expense['amount'] / $maxAmount) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar bg-danger" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                            <div class="fw-bold text-danger ms-3">₱{{ number_format($expense['amount'], 2) }}</div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4 mb-0">No expense data</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Branch Comparison --}}
    <div class="inventory-card">
        <div class="inventory-card-header">
            <h5 class="mb-0">Branch Performance</h5>
        </div>
        <div class="inventory-card-body">
            <div class="table-responsive">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Branch</th>
                            <th class="text-end">Sales</th>
                            <th class="text-end">Expenses</th>
                            <th class="text-end">Profit</th>
                            <th class="text-end">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branchComparison as $branch)
                            <tr>
                                <td class="fw-semibold">{{ $branch['name'] }}</td>
                                <td class="text-end">₱{{ number_format($branch['sales'], 2) }}</td>
                                <td class="text-end">₱{{ number_format($branch['expenses'], 2) }}</td>
                                <td class="text-end fw-bold text-{{ $branch['profit'] >= 0 ? 'success' : 'danger' }}">
                                    ₱{{ number_format($branch['profit'], 2) }}
                                </td>
                                <td class="text-end">
                                    {{ $branch['sales'] > 0 ? number_format(($branch['profit'] / $branch['sales']) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No branch data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Profit Trend Chart
const profitTrendData = @json($profitTrend);
const profitCtx = document.getElementById('profitTrendChart');
new Chart(profitCtx, {
    type: 'line',
    data: {
        labels: profitTrendData.map(d => d.date),
        datasets: [
            {
                label: 'Revenue',
                data: profitTrendData.map(d => d.sales),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Expenses',
                data: profitTrendData.map(d => d.expenses),
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => '₱' + value.toLocaleString()
                }
            }
        }
    }
});

// Payment Method Chart
const paymentData = @json($paymentMethodBreakdown);
const paymentCtx = document.getElementById('paymentMethodChart');
new Chart(paymentCtx, {
    type: 'doughnut',
    data: {
        labels: paymentData.map(p => p.method),
        datasets: [{
            data: paymentData.map(p => p.total),
            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>
@endpush
