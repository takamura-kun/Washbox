@extends('admin.layouts.app')

@section('title', 'Financial Dashboard — WashBox')
@section('page-title', 'Finance')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.finance-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    gap: 1rem;
}

.finance-tabs {
    display: flex;
    gap: 0.5rem;
    border-bottom: 2px solid var(--table-border);
    margin-bottom: 2rem;
}

.finance-tab {
    padding: 0.75rem 1.5rem;
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    font-weight: 500;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.finance-tab:hover {
    color: var(--text-primary);
}

.finance-tab.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
}

.finance-content {
    display: none;
}

.finance-content.active {
    display: block;
}

.kpi-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.kpi-card {
    background: var(--card-bg);
    border: 1px solid var(--table-border);
    border-radius: 8px;
    padding: 1.5rem;
}

.kpi-label {
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.kpi-value {
    font-size: 1.75rem;
    font-weight: bold;
    color: var(--text-primary);
}

.kpi-subtext {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-top: 0.5rem;
}

.sales-metrics-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.sales-metric-card {
    background: var(--card-bg);
    border: 1px solid var(--table-border);
    border-radius: 8px;
    padding: 1.25rem;
    text-align: center;
}

.sales-metric-label {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.sales-metric-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #10b981;
    margin-bottom: 0.25rem;
}

.sales-metric-subtext {
    font-size: 0.75rem;
    color: var(--text-secondary);
}

.two-column {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

@media (max-width: 1024px) {
    .two-column {
        grid-template-columns: 1fr;
    }
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 1rem;
    margin-top: 1.5rem;
}

.list-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--table-border);
}

.list-item:last-child {
    border-bottom: none;
}

.list-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.list-value {
    font-weight: 600;
    color: var(--text-primary);
}

.list-value.positive {
    color: #10b981;
}

.list-value.negative {
    color: #ef4444;
}

.filter-selector {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.filter-selector select {
    padding: 0.5rem 1rem;
    border: 1px solid var(--table-border);
    border-radius: 4px;
    background: var(--card-bg);
    color: var(--text-primary);
    cursor: pointer;
    font-size: 0.9rem;
}

.chart-container {
    position: relative;
    height: 300px;
    margin-top: 1rem;
}

.pie-chart-container {
    position: relative;
    height: 250px;
    margin-top: 1rem;
}

.category-bar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--table-border);
}

.category-bar:last-child {
    border-bottom: none;
}

.category-name {
    flex: 1;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.category-bar-container {
    flex: 1;
    height: 6px;
    background: var(--table-border);
    border-radius: 3px;
    overflow: hidden;
}

.category-bar-fill {
    height: 100%;
    background: #ef4444;
    border-radius: 3px;
}

.category-amount {
    font-weight: 600;
    color: #ef4444;
    font-size: 0.9rem;
    min-width: 100px;
    text-align: right;
}

.expense-log-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--table-border);
    background: var(--card-bg);
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.expense-log-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.expense-log-left {
    flex: 1;
}

.expense-log-title {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.expense-log-date {
    font-size: 0.8rem;
    color: var(--text-secondary);
}

.expense-log-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: #3b82f6;
    color: white;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-right: 0.5rem;
}

.expense-log-amount {
    font-weight: 600;
    color: #ef4444;
    font-size: 0.95rem;
}

.expense-filter-bar {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.filter-badge {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    background: var(--card-bg);
    border: 1px solid var(--table-border);
    border-radius: 20px;
    font-size: 0.85rem;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-badge.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.add-expense-btn {
    margin-left: auto;
}

.pl-summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.pl-card {
    background: var(--card-bg);
    border: 1px solid var(--table-border);
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
}

.pl-card-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
}

.pl-card-value {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.pl-card-value.positive {
    color: #10b981;
}

.pl-card-value.negative {
    color: #ef4444;
}

.pl-breakdown {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.pl-breakdown-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid var(--table-border);
}

.pl-breakdown-item:last-child {
    border-bottom: none;
}

.pl-breakdown-label {
    font-weight: 500;
}

.pl-breakdown-amount {
    font-weight: 600;
    color: #10b981;
}

.pl-charts {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

@media (max-width: 1024px) {
    .pl-breakdown {
        grid-template-columns: 1fr;
    }
    .pl-charts {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@section('content')

<div class="container-xl px-4 py-4">
    {{-- Header with Period & Branch Selector --}}
    <div class="finance-header">
        <h2 style="margin: 0; font-size: 1.5rem;">Finance</h2>
        <div class="filter-selector">
            <form method="GET" class="d-flex gap-2" id="filterForm">
                <select name="branch_id" class="form-control" style="width: auto;" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                <select name="period" class="form-control" style="width: auto;" onchange="document.getElementById('filterForm').submit()">
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                    <option value="year" {{ $period === 'year' ? 'selected' : '' }}>This Year</option>
                </select>
            </form>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-label">Total Sales (₱)</div>
            <div class="kpi-value" style="color: #10b981;">₱{{ number_format($summary['totalSales'], 0) }}</div>
            <div class="kpi-subtext">{{ $summary['laundryCount'] + $summary['retailCount'] }} transactions</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Total Expenses (₱)</div>
            <div class="kpi-value" style="color: #ef4444;">₱{{ number_format($summary['totalCosts'], 0) }}</div>
            <div class="kpi-subtext">Operating & Inventory</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Net Profit (₱)</div>
            <div class="kpi-value" style="color: #3b82f6;">₱{{ number_format($summary['netProfit'], 0) }}</div>
            <div class="kpi-subtext">{{ $summary['profitMargin'] }}% margin</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Profit/Remaining</div>
            <div class="kpi-value">{{ round(($summary['totalCosts'] / max($summary['totalSales'], 1)) * 100, 1) }}%</div>
            <div class="kpi-subtext">Expense ratio</div>
        </div>
    </div>

    {{-- Financial Management Quick Links --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <a href="{{ route('admin.finance.ledger.index') }}" class="inventory-card text-decoration-none" style="display: block;">
                <div class="inventory-card-body text-center">
                    <i class="bi bi-journal-text display-4 text-primary mb-2"></i>
                    <h6 class="mb-1">Financial Ledger</h6>
                    <small class="text-muted">View all transactions</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.finance.budgets.index') }}" class="inventory-card text-decoration-none" style="display: block;">
                <div class="inventory-card-body text-center">
                    <i class="bi bi-wallet2 display-4 text-success mb-2"></i>
                    <h6 class="mb-1">Budgets</h6>
                    <small class="text-muted">Manage budgets</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.finance.cash-flow.index') }}" class="inventory-card text-decoration-none" style="display: block;">
                <div class="inventory-card-body text-center">
                    <i class="bi bi-cash-stack display-4 text-warning mb-2"></i>
                    <h6 class="mb-1">Cash Flow</h6>
                    <small class="text-muted">Track cash flow</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.finance.audit-logs.index') }}" class="inventory-card text-decoration-none" style="display: block;">
                <div class="inventory-card-body text-center">
                    <i class="bi bi-shield-check display-4 text-danger mb-2"></i>
                    <h6 class="mb-1">Audit Logs</h6>
                    <small class="text-muted">View audit trail</small>
                </div>
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="finance-tabs">
        <button class="finance-tab active" onclick="switchTab('sales')">Sales</button>
        <button class="finance-tab" onclick="switchTab('expenses')">Expenses</button>
        <button class="finance-tab" onclick="switchTab('payroll')">Payroll</button>
        <button class="finance-tab" onclick="switchTab('reports')">Reports</button>
    </div>

    {{-- Sales Tab --}}
    <div id="sales" class="finance-content active">
        {{-- Sales Metrics Cards --}}
        <div class="sales-metrics-row">
            <div class="sales-metric-card">
                <div class="sales-metric-label">Laundry services</div>
                <div class="sales-metric-value">₱{{ number_format($summary['laundrySales'], 0) }}</div>
                <div class="sales-metric-subtext">{{ $summary['laundryCount'] }} of total</div>
            </div>

            <div class="sales-metric-card">
                <div class="sales-metric-label">Retail sales</div>
                <div class="sales-metric-value">₱{{ number_format($summary['retailSales'], 0) }}</div>
                <div class="sales-metric-subtext">{{ $summary['retailCount'] }} of total</div>
            </div>

            <div class="sales-metric-card">
                <div class="sales-metric-label">Pickup / delivery fees</div>
                <div class="sales-metric-value">₱{{ number_format($summary['pickupDeliveryFees'], 0) }}</div>
                <div class="sales-metric-subtext">Fees of total</div>
            </div>
        </div>

        <div class="two-column">
            {{-- Sales by Branch --}}
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <div class="section-title">Sales by branch</div>
                    @forelse($branchComparison as $branch)
                        <div class="list-item">
                            <span class="list-label">{{ $branch['name'] }}</span>
                            <span class="list-value positive">₱{{ number_format($branch['sales'], 0) }}</span>
                        </div>
                    @empty
                        <p class="text-muted">No branch data</p>
                    @endforelse
                </div>
            </div>

            {{-- Top Services --}}
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <div class="section-title">Top Services</div>
                    <div class="list-item">
                        <span class="list-label">Premium Laundry</span>
                        <span class="list-value positive">₱{{ number_format($summary['laundrySales'] * 0.4, 0) }}</span>
                    </div>
                    <div class="list-item">
                        <span class="list-label">Basic Laundry</span>
                        <span class="list-value positive">₱{{ number_format($summary['laundrySales'] * 0.35, 0) }}</span>
                    </div>
                    <div class="list-item">
                        <span class="list-label">Surf Service</span>
                        <span class="list-value positive">₱{{ number_format($summary['laundrySales'] * 0.15, 0) }}</span>
                    </div>
                    <div class="list-item">
                        <span class="list-label">Dry Clean</span>
                        <span class="list-value positive">₱{{ number_format($summary['laundrySales'] * 0.07, 0) }}</span>
                    </div>
                    <div class="list-item">
                        <span class="list-label">Add-ons</span>
                        <span class="list-value positive">₱{{ number_format($summary['laundrySales'] * 0.03, 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sales Breakdown Chart --}}
        <div class="inventory-card">
            <div class="inventory-card-body">
                <div class="section-title">Monthly revenue trend</div>
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Expenses Tab --}}
    <div id="expenses" class="finance-content">
        {{-- Filter Bar --}}
        <div class="expense-filter-bar">
            <span class="filter-badge active">From Inventory purchases / payroll</span>
            <span class="filter-badge">Entered Logged</span>
            <a href="{{ route('admin.finance.expenses.create') }}" class="btn-inventory btn-inventory-primary add-expense-btn">
                <i class="bi bi-plus-lg me-2"></i>Add Expense
            </a>
        </div>

        {{-- Category Breakdown & Pie Chart --}}
        <div class="two-column">
            {{-- By Category --}}
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <div class="section-title">By category</div>
                    @php
                        $maxAmount = $topExpenseCategories->max('amount') ?? 1;
                    @endphp
                    @forelse($topExpenseCategories as $expense)
                        <div class="category-bar">
                            <span class="category-name">{{ $expense['category'] }}</span>
                            <div class="category-bar-container">
                                <div class="category-bar-fill" style="width: {{ ($expense['amount'] / $maxAmount) * 100 }}%"></div>
                            </div>
                            <span class="category-amount">₱{{ number_format($expense['amount'], 0) }}</span>
                        </div>
                    @empty
                        <p class="text-muted">No expenses recorded</p>
                    @endforelse
                </div>
            </div>

            {{-- Expenses Chart --}}
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <div class="section-title">Expenses Chart</div>
                    <div class="pie-chart-container">
                        <canvas id="expensesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Expense Log --}}
        <div class="inventory-card">
            <div class="inventory-card-body">
                <div class="section-title">Expense log</div>
                <div style="max-height: 500px; overflow-y: auto;">
                    @forelse($topExpenseCategories as $index => $expense)
                        <div class="expense-log-item">
                            <div class="expense-log-left">
                                <div class="expense-log-title">
                                    <span class="expense-log-badge">{{ chr(65 + $index) }}</span>
                                    Purchase - {{ $expense['category'] }}
                                </div>
                                <div class="expense-log-date">March 15 - 2026 10:30 AM</div>
                            </div>
                            <div class="expense-log-amount">₱{{ number_format($expense['amount'], 0) }}</div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4">No expenses recorded</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Payroll Tab --}}
    <div id="payroll" class="finance-content">
        <div class="inventory-card">
            <div class="inventory-card-body">
                <div class="section-title">Payroll Management</div>
                <p class="text-muted mb-3">Manage staff salaries and payroll periods</p>
                <div style="display: flex; gap: 1rem;">
                    <a href="{{ route('admin.finance.payroll.index') }}" class="btn-inventory btn-inventory-primary">
                        <i class="bi bi-list me-2"></i>View Payroll
                    </a>
                    <a href="{{ route('admin.finance.payroll.create') }}" class="btn-inventory btn-inventory-secondary">
                        <i class="bi bi-plus-lg me-2"></i>New Payroll Period
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Reports Tab --}}
    <div id="reports" class="finance-content">
        {{-- Summary Cards --}}
        <div class="pl-summary-cards">
            <div class="pl-card">
                <div class="pl-card-label">Total Sales</div>
                <div class="pl-card-value positive">₱{{ number_format($summary['totalSales'], 0) }}</div>
            </div>
            <div class="pl-card">
                <div class="pl-card-label">Total Expenses</div>
                <div class="pl-card-value negative">₱{{ number_format($summary['totalExpenses'], 0) }}</div>
            </div>
            <div class="pl-card">
                <div class="pl-card-label">Net Profit</div>
                <div class="pl-card-value {{ $summary['netProfit'] >= 0 ? 'positive' : 'negative' }}">₱{{ number_format($summary['netProfit'], 0) }}</div>
            </div>
        </div>

        {{-- Sales & Expenses Breakdown --}}
        <div class="pl-breakdown">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <h5 class="mb-3">Sales Breakdown</h5>
                    <div class="pl-breakdown-item">
                        <span class="pl-breakdown-label">Laundry Services</span>
                        <span class="pl-breakdown-amount">₱{{ number_format($salesBreakdown['laundry'], 0) }}</span>
                    </div>
                    <div class="pl-breakdown-item">
                        <span class="pl-breakdown-label">Retail Sales (Bulk)</span>
                        <span class="pl-breakdown-amount">₱{{ number_format($salesBreakdown['retail'], 0) }}</span>
                    </div>
                    <div class="pl-breakdown-item">
                        <span class="pl-breakdown-label">Pickup / Delivery Fees</span>
                        <span class="pl-breakdown-amount">₱{{ number_format($salesBreakdown['fees'], 0) }}</span>
                    </div>
                </div>
            </div>

            <div class="inventory-card">
                <div class="inventory-card-body">
                    <h5 class="mb-3">Expenses Breakdown</h5>
                    @forelse($expenseBreakdown as $expense)
                        <div class="pl-breakdown-item">
                            <span class="pl-breakdown-label">{{ $expense['category'] }}</span>
                            <span class="pl-breakdown-amount">₱{{ number_format($expense['amount'], 0) }}</span>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">No expenses recorded</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Branch Comparison --}}
        <div class="inventory-card mb-4">
            <div class="inventory-card-body">
                <h5 class="mb-3">Profit & Loss by Branch</h5>
                <div class="table-responsive">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Branch</th>
                                <th class="text-end">Sales</th>
                                <th class="text-end">Expenses</th>
                                <th class="text-end">Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branchComparison as $branch)
                                <tr>
                                    <td class="fw-semibold">{{ $branch['name'] }}</td>
                                    <td class="text-end">₱{{ number_format($branch['sales'], 0) }}</td>
                                    <td class="text-end">₱{{ number_format($branch['expenses'], 0) }}</td>
                                    <td class="text-end">
                                        <span style="color: {{ $branch['profit'] >= 0 ? '#10b981' : '#ef4444' }}; font-weight: 600;">
                                            ₱{{ number_format($branch['profit'], 0) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No branch data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="pl-charts">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <h5 class="mb-3">Sales vs Expenses Trend</h5>
                    <canvas id="trendChart" height="250"></canvas>
                </div>
            </div>

            <div class="inventory-card">
                <div class="inventory-card-body">
                    <h5 class="mb-3">Profit Margin by Branch</h5>
                    <canvas id="marginChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function switchTab(tabName) {
    // Hide all content
    document.querySelectorAll('.finance-content').forEach(el => {
        el.classList.remove('active');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.finance-tab').forEach(el => {
        el.classList.remove('active');
    });
    
    // Show selected content
    document.getElementById(tabName).classList.add('active');
    
    // Add active class to clicked tab
    event.target.classList.add('active');
}

// Sales Chart
const profitTrendData = @json($profitTrend);

const salesCtx = document.getElementById('salesChart');
if (salesCtx) {
    new Chart(salesCtx, {
        type: 'bar',
        data: {
            labels: profitTrendData.map(d => d.date),
            datasets: [{
                label: 'Revenue',
                data: profitTrendData.map(d => d.sales),
                backgroundColor: '#3b82f6',
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

// Expenses Pie Chart
const expenseData = @json($topExpenseCategories);
const expensesCtx = document.getElementById('expensesChart');
if (expensesCtx && expenseData.length > 0) {
    const colors = ['#ef4444', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#14b8a6'];
    
    new Chart(expensesCtx, {
        type: 'doughnut',
        data: {
            labels: expenseData.map(e => e.category),
            datasets: [{
                data: expenseData.map(e => e.amount),
                backgroundColor: colors.slice(0, expenseData.length),
                borderColor: 'var(--card-bg)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: 'var(--text-primary)',
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
}
</script>
@endpush
