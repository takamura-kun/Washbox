@extends('admin.layouts.app')

@section('title', 'Profit & Loss Report — WashBox')
@section('page-title', 'Profit & Loss Report')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.pl-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    gap: 1rem;
}
.pl-title {
    font-size: 1.5rem;
    font-weight: 600;
}
.pl-controls {
    display: flex;
    gap: 1rem;
    align-items: center;
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
    {{-- Header --}}
    <div class="pl-header">
        <div>
            <h2 class="pl-title mb-0">Profit & Loss — {{ now()->format('F Y') }}</h2>
        </div>
        <div class="pl-controls">
            <select class="form-control" style="width: auto;" onchange="window.location.href='?period=' + this.value">
                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                <option value="year" {{ $period === 'year' ? 'selected' : '' }}>This Year</option>
            </select>
            <button class="btn-inventory btn-inventory-primary" onclick="exportReport()">
                <i class="bi bi-download me-2"></i>Export
            </button>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="finance-tabs">
        <button class="finance-tab active" onclick="switchTab('sales', event)">Sales</button>
        <button class="finance-tab" onclick="switchTab('expenses', event)">Expenses</button>
        <button class="finance-tab" onclick="switchTab('payroll', event)">Payroll</button>
        <button class="finance-tab" onclick="switchTab('reports', event)">Reports</button>
    </div>

    {{-- Sales Tab --}}
    <div id="sales" class="finance-content active">
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

    {{-- Expenses Tab --}}
    <div id="expenses" class="finance-content" style="display: none;">
        <div class="inventory-card">
            <div class="inventory-card-body">
                <h5 class="mb-3">Expense Details</h5>
                <p class="text-muted mb-3">View detailed expense breakdown and analysis</p>
                <a href="{{ route('admin.finance.expenses.report', ['period' => $period, 'branch_id' => $branchId]) }}" class="btn-inventory btn-inventory-primary">
                    <i class="bi bi-wallet2 me-2"></i>View Expense Report
                </a>
            </div>
        </div>
    </div>

    {{-- Payroll Tab --}}
    <div id="payroll" class="finance-content" style="display: none;">
        <div class="inventory-card">
            <div class="inventory-card-body">
                <h5 class="mb-3">Payroll Management</h5>
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
    <div id="reports" class="finance-content" style="display: none;">
        <div class="inventory-card">
            <div class="inventory-card-body">
                <h5 class="mb-3">Financial Reports</h5>
                <p class="text-muted mb-3">View detailed financial analysis and reports</p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="{{ route('admin.finance.sales.index', ['period' => $period, 'branch_id' => $branchId]) }}" class="btn-inventory btn-inventory-primary">
                        <i class="bi bi-receipt me-2"></i>Sales Report
                    </a>
                    <a href="{{ route('admin.finance.expenses.report', ['period' => $period, 'branch_id' => $branchId]) }}" class="btn-inventory btn-inventory-primary">
                        <i class="bi bi-wallet2 me-2"></i>Expense Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function switchTab(tabName, event) {
    event.preventDefault();
    
    // Hide all content
    document.querySelectorAll('.finance-content').forEach(el => {
        el.style.display = 'none';
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.finance-tab').forEach(el => {
        el.classList.remove('active');
    });
    
    // Show selected content
    document.getElementById(tabName).style.display = 'block';
    
    // Add active class to clicked tab
    event.target.classList.add('active');
}

const trendData = @json($profitTrend);
const branchComparison = @json($branchComparison);

// Sales vs Expenses Trend
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: trendData.map(d => d.date),
        datasets: [
            {
                label: 'Sales',
                data: trendData.map(d => d.sales),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.15)',
                tension: 0.4,
                fill: true,
                borderWidth: 2
            },
            {
                label: 'Expenses',
                data: trendData.map(d => d.expenses),
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.15)',
                tension: 0.4,
                fill: true,
                borderWidth: 2
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { usePointStyle: true }
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Profit Margin by Branch
const marginCtx = document.getElementById('marginChart').getContext('2d');
new Chart(marginCtx, {
    type: 'bar',
    data: {
        labels: branchComparison.map(b => b.name),
        datasets: [{
            label: 'Profit Margin %',
            data: branchComparison.map(b => b.sales > 0 ? ((b.profit / b.sales) * 100) : 0),
            backgroundColor: '#06b6d4',
            borderRadius: 4
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: { beginAtZero: true }
        }
    }
});

function exportReport() {
    alert('Export functionality coming soon');
}
</script>
@endpush
