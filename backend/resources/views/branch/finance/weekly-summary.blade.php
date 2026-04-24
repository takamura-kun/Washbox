@extends('branch.layouts.app')

@section('title', 'Weekly Summary')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.card {
    background: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}
.card-body {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
}
.card-header {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
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
</style>
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1" style="color: var(--text-primary);">Weekly Financial Summary</h2>
            <p class="mb-0" style="color: var(--text-secondary);">Performance overview for the week</p>
        </div>
    </div>

    {{-- Quick Navigation --}}
    <div class="btn-group mb-4 w-100" role="group">
        <a href="{{ route('branch.finance.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </a>
        <a href="{{ route('branch.finance.expenses') }}" class="btn btn-outline-danger">
            <i class="bi bi-receipt me-1"></i>Expenses
        </a>
        <a href="{{ route('branch.finance.daily-cash-report') }}" class="btn btn-outline-success">
            <i class="bi bi-cash-coin me-1"></i>Daily Cash
        </a>
        <a href="{{ route('branch.finance.weekly-summary') }}" class="btn btn-info active">
            <i class="bi bi-graph-up me-1"></i>Weekly Summary
        </a>
        <a href="{{ route('branch.finance.sales-report') }}" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-text me-1"></i>Sales Report
        </a>
    </div>

    {{-- Date Range Selector --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-calendar-check me-1"></i>Load
                    </button>
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>Print
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-success">
                <div class="card-body text-center">
                    <i class="bi bi-cash-coin text-success fs-1 mb-2"></i>
                    <p class="mb-1" style="color: var(--text-secondary);">Total Sales</p>
                    <h3 class="mb-0 text-success">₱{{ number_format($summary['total_sales'], 2) }}</h3>
                    <small style="color: var(--text-secondary);">{{ $summary['total_orders'] }} orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-danger">
                <div class="card-body text-center">
                    <i class="bi bi-receipt text-danger fs-1 mb-2"></i>
                    <p class="mb-1" style="color: var(--text-secondary);">Total Expenses</p>
                    <h3 class="mb-0 text-danger">₱{{ number_format($summary['total_expenses'], 2) }}</h3>
                    <small style="color: var(--text-secondary);">Operating costs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm {{ $summary['net_income'] >= 0 ? 'border-primary' : 'border-warning' }}">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up {{ $summary['net_income'] >= 0 ? 'text-primary' : 'text-warning' }} fs-1 mb-2"></i>
                    <p class="mb-1" style="color: var(--text-secondary);">Net Income</p>
                    <h3 class="mb-0 {{ $summary['net_income'] >= 0 ? 'text-primary' : 'text-warning' }}">
                        ₱{{ number_format($summary['net_income'], 2) }}
                    </h3>
                    <small style="color: var(--text-secondary);">Profit/Loss</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-info">
                <div class="card-body text-center">
                    <i class="bi bi-calculator text-info fs-1 mb-2"></i>
                    <p class="mb-1" style="color: var(--text-secondary);">Avg Order Value</p>
                    <h3 class="mb-0 text-info">₱{{ number_format($summary['average_order'], 2) }}</h3>
                    <small style="color: var(--text-secondary);">Per transaction</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Daily Breakdown Chart --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-bar-chart me-2"></i>Daily Performance
            </h5>
        </div>
        <div class="card-body">
            <canvas id="dailyChart" height="80"></canvas>
        </div>
    </div>

    {{-- Detailed Daily Breakdown --}}
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-table me-2"></i>Daily Breakdown
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-end">Sales</th>
                            <th class="text-end">Expenses</th>
                            <th class="text-end">Net</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dailyBreakdown as $day)
                            <tr>
                                <td><strong>{{ $day['date'] }}</strong></td>
                                <td class="text-end text-success">₱{{ number_format($day['sales'], 2) }}</td>
                                <td class="text-end text-danger">₱{{ number_format($day['expenses'], 2) }}</td>
                                <td class="text-end">
                                    <strong class="{{ $day['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $day['net'] >= 0 ? '+' : '' }}₱{{ number_format($day['net'], 2) }}
                                    </strong>
                                </td>
                                <td class="text-center">
                                    @if($day['net'] >= 0)
                                        <span class="badge bg-success">Profit</span>
                                    @else
                                        <span class="badge bg-danger">Loss</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <th>Total</th>
                            <th class="text-end text-success">₱{{ number_format($summary['total_sales'], 2) }}</th>
                            <th class="text-end text-danger">₱{{ number_format($summary['total_expenses'], 2) }}</th>
                            <th class="text-end">
                                <strong class="{{ $summary['net_income'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $summary['net_income'] >= 0 ? '+' : '' }}₱{{ number_format($summary['net_income'], 2) }}
                                </strong>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Key Insights --}}
    <div class="row g-3 mt-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>Key Insights
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        @php
                            $bestDay = collect($dailyBreakdown)->sortByDesc('net')->first();
                            $worstDay = collect($dailyBreakdown)->sortBy('net')->first();
                            $avgDaily = $summary['total_sales'] / count($dailyBreakdown);
                        @endphp
                        <li class="mb-2">
                            <strong>Best Day:</strong> {{ $bestDay['date'] }} with ₱{{ number_format($bestDay['net'], 2) }} net income
                        </li>
                        <li class="mb-2">
                            <strong>Average Daily Sales:</strong> ₱{{ number_format($avgDaily, 2) }}
                        </li>
                        <li class="mb-2">
                            <strong>Profit Margin:</strong> 
                            {{ $summary['total_sales'] > 0 ? number_format(($summary['net_income'] / $summary['total_sales']) * 100, 1) : 0 }}%
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-flag me-2"></i>Action Items
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        @if($summary['net_income'] < 0)
                            <li class="mb-2 text-danger">
                                <strong>Alert:</strong> Week ended with a loss. Review expenses.
                            </li>
                        @endif
                        @if($summary['total_expenses'] > $summary['total_sales'] * 0.7)
                            <li class="mb-2 text-warning">
                                <strong>Warning:</strong> Expenses are high (>70% of sales). Consider cost reduction.
                            </li>
                        @endif
                        <li class="mb-2">
                            Review low-performing days and identify causes
                        </li>
                        <li class="mb-2">
                            Plan promotions for slow days
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const dailyData = @json($dailyBreakdown);

const ctx = document.getElementById('dailyChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: dailyData.map(d => d.date),
        datasets: [
            {
                label: 'Sales',
                data: dailyData.map(d => d.sales),
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 1
            },
            {
                label: 'Expenses',
                data: dailyData.map(d => d.expenses),
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: 'rgb(239, 68, 68)',
                borderWidth: 1
            },
            {
                label: 'Net',
                data: dailyData.map(d => d.net),
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1,
                type: 'line'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ₱' + context.parsed.y.toLocaleString();
                    }
                }
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
</script>
@endpush
@endsection
