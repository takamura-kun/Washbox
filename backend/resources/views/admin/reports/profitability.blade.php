@extends('admin.layouts.app')

@section('title', 'Profitability Analysis')
@section('page-title', 'Profitability Analysis')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/laundry.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/dark-mode-fixes.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/reports-fixes.css') }}">
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header with Date Filter --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Profitability Analysis</h4>
            <p class="text-muted mb-0">Revenue, costs, and profit margins analysis</p>
        </div>
        <form method="GET" class="d-flex gap-2">
            <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
            <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    {{-- Overall Metrics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="bi bi-currency-dollar text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 text-muted small">TOTAL REVENUE</h6>
                            <h4 class="mb-0 fw-bold">₱{{ number_format($overallMetrics['total_revenue'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded p-3">
                                <i class="bi bi-graph-down text-danger fs-4"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 text-muted small">ESTIMATED COSTS</h6>
                            <h4 class="mb-0 fw-bold">₱{{ number_format($overallMetrics['estimated_costs']['total'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <i class="bi bi-graph-up text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 text-muted small">GROSS PROFIT</h6>
                            <h4 class="mb-0 fw-bold">₱{{ number_format($overallMetrics['gross_profit'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded p-3">
                                <i class="bi bi-percent text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 text-muted small">PROFIT MARGIN</h6>
                            <h4 class="mb-0 fw-bold">{{ $overallMetrics['profit_margin'] }}%</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Service Profitability --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="mb-0 fw-bold">Service Profitability</h5>
                    <small class="text-muted">Revenue and profit analysis by service type</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Type</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-end">Est. Costs</th>
                                    <th class="text-end">Est. Profit</th>
                                    <th class="text-end">Margin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($serviceProfitability as $service)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $service->name }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $service->service_type)) }}</span>
                                    </td>
                                    <td class="text-end">{{ number_format($service->total_orders) }}</td>
                                    <td class="text-end">₱{{ number_format($service->total_revenue, 2) }}</td>
                                    <td class="text-end text-danger">₱{{ number_format($service->estimated_costs, 2) }}</td>
                                    <td class="text-end text-success">₱{{ number_format($service->estimated_profit, 2) }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ $service->profit_margin >= 60 ? 'success' : ($service->profit_margin >= 40 ? 'warning' : 'danger') }}">
                                            {{ $service->profit_margin }}%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-inbox text-muted fs-1"></i>
                                        <p class="text-muted mt-2">No service data available for selected period</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cost Breakdown --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="mb-0 fw-bold">Cost Breakdown</h5>
                    <small class="text-muted">Estimated operational costs</small>
                </div>
                <div class="card-body">
                    @php
                        $costColors = [
                            'materials' => 'primary',
                            'labor' => 'warning',
                            'overhead' => 'info',
                            'pickup_delivery' => 'secondary'
                        ];
                    @endphp
                    @foreach($costBreakdown as $key => $cost)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <div class="d-flex align-items-center mb-1">
                                <div class="bg-{{ $costColors[$key] }} bg-opacity-10 rounded-circle p-2 me-2">
                                    <i class="bi bi-circle-fill text-{{ $costColors[$key] }}" style="font-size: 0.5rem;"></i>
                                </div>
                                <h6 class="mb-0">{{ ucfirst(str_replace('_', ' ', $key)) }}</h6>
                            </div>
                            <small class="text-muted">{{ $cost['description'] }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">₱{{ number_format($cost['amount'], 2) }}</div>
                            @if($cost['percentage'] > 0)
                            <small class="text-muted">{{ $cost['percentage'] }}%</small>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Branch Profitability --}}
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="mb-0 fw-bold">Branch Profitability</h5>
                    <small class="text-muted">Revenue and profit analysis by branch</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-dark-mode">
                            <thead>
                                <tr>
                                    <th class="text-body">Branch</th>
                                    <th class="text-end text-body">Orders</th>
                                    <th class="text-end text-body">Revenue</th>
                                    <th class="text-end text-body">Avg Order Value</th>
                                    <th class="text-end text-body">Est. Costs</th>
                                    <th class="text-end text-body">Est. Profit</th>
                                    <th class="text-end text-body">Margin</th>
                                    <th class="text-end text-body">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branchProfitability as $branch)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                                <span class="fw-bold text-primary small">{{ strtoupper(substr($branch->name, 0, 2)) }}</span>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-body">{{ $branch->name }}</div>
                                                @if($branch->code)
                                                <small class="text-muted">{{ $branch->code }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end text-body">{{ number_format($branch->total_orders) }}</td>
                                    <td class="text-end text-body">₱{{ number_format($branch->total_revenue, 2) }}</td>
                                    <td class="text-end text-body">₱{{ number_format($branch->avg_order_value, 2) }}</td>
                                    <td class="text-end text-danger">₱{{ number_format($branch->estimated_costs, 2) }}</td>
                                    <td class="text-end text-success">₱{{ number_format($branch->estimated_profit, 2) }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ $branch->profit_margin >= 60 ? 'success' : ($branch->profit_margin >= 40 ? 'warning' : 'danger') }}">
                                            {{ $branch->profit_margin }}%
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="progress" style="width: 60px; height: 8px;">
                                            <div class="progress-bar bg-{{ $branch->profit_margin >= 60 ? 'success' : ($branch->profit_margin >= 40 ? 'warning' : 'danger') }}"
                                                 style="width: {{ min($branch->profit_margin, 100) }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="bi bi-building text-muted fs-1"></i>
                                        <p class="text-muted mt-2">No branch data available for selected period</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Profit Trends Chart --}}
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="mb-0 fw-bold">Profit Trends</h5>
                    <small class="text-muted">Daily profit and revenue trends</small>
                </div>
                <div class="card-body">
                    <canvas id="profitTrendsChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/chart.js/chart.umd.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profit Trends Chart
    const profitData = @json($profitTrends);
    const ctx = document.getElementById('profitTrendsChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: profitData.map(item => new Date(item.date).toLocaleDateString()),
            datasets: [{
                label: 'Revenue',
                data: profitData.map(item => parseFloat(item.revenue)),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4
            }, {
                label: 'Estimated Profit',
                data: profitData.map(item => parseFloat(item.estimated_profit)),
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                tension: 0.4
            }, {
                label: 'Estimated Costs',
                data: profitData.map(item => parseFloat(item.estimated_costs)),
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
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
});
</script>
@endpush

@endsection
