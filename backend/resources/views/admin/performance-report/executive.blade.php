{{-- Debug: Show data structure --}}
@if(config('app.debug'))
<div class="alert alert-info mb-3" style="font-size:0.75rem;">
    <strong>Debug Info:</strong><br>
    Total Revenue: {{ number_format($data['total_revenue'] ?? 0, 2) }}<br>
    Total Orders: {{ $data['total_orders'] ?? 0 }}<br>
    Revenue Trend Count: {{ $data['revenue_trend']->count() ?? 0 }}<br>
    Branch Comparison Count: {{ $data['branch_comparison']->count() ?? 0 }}
</div>
@endif

<div class="row g-2 mb-3">
    <!-- KPI Cards -->
    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-blue">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-blue-light"><i class="bi bi-currency-dollar"></i></div>
                <span class="metric-change {{ $data['revenue_growth'] >= 0 ? 'positive' : 'negative' }}">
                    <i class="bi bi-arrow-{{ $data['revenue_growth'] >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($data['revenue_growth']) }}%
                </span>
            </div>
            <div class="metric-label-compact">Total revenue</div>
            <div class="metric-value-large text-slate-900" style="font-size:1rem;">₱{{ number_format($data['total_revenue'], 0) }}</div>
            <small class="text-muted">This period</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-cyan">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-cyan-light"><i class="bi bi-cart"></i></div>
                <span class="metric-change {{ $data['orders_growth'] >= 0 ? 'positive' : 'negative' }}">
                    <i class="bi bi-arrow-{{ $data['orders_growth'] >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($data['orders_growth']) }}%
                </span>
            </div>
            <div class="metric-label-compact">Total orders</div>
            <div class="metric-value-large text-slate-900">{{ number_format($data['total_orders']) }}</div>
            <small class="text-muted">Completed</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-yellow">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-yellow-light"><i class="bi bi-people"></i></div>
                <span class="metric-change positive">
                    <i class="bi bi-arrow-up"></i>8.3%
                </span>
            </div>
            <div class="metric-label-compact">Active customers</div>
            <div class="metric-value-large text-slate-900">{{ number_format($data['active_customers']) }}</div>
            <small class="text-muted">Engaged users</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-green">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-green-light"><i class="bi bi-graph-up-arrow"></i></div>
                <span class="metric-change positive">
                    <i class="bi bi-arrow-up"></i>2.1%
                </span>
            </div>
            <div class="metric-label-compact">Profit margin</div>
            <div class="metric-value-large text-success">{{ $data['profit_margin'] }}%</div>
            <small class="text-muted">Net margin</small>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Revenue Trend Chart -->
    <div class="col-lg-8">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-graph-up text-primary me-2"></i>Revenue trend
                </h6>
                <small>Performance {{ ucfirst(str_replace('_', ' ', $dateRange)) }}</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Alerts -->
    <div class="col-lg-4">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-bell text-warning me-2"></i>Key alerts
                </h6>
                <small>Important notifications</small>
            </div>
            <div class="card-body-modern" style="max-height:200px;overflow-y:auto;">
                @forelse($data['alerts'] as $alert)
                <div class="alert alert-{{ $alert['type'] }} d-flex align-items-start mb-2 py-2" role="alert" style="font-size:0.75rem;">
                    <i class="bi bi-{{ $alert['icon'] }} me-2 mt-1"></i>
                    <div class="flex-grow-1">
                        <strong class="d-block small">{{ $alert['title'] }}</strong>
                        <small>{{ $alert['message'] }}</small>
                    </div>
                </div>
                @empty
                <div class="text-center py-4" style="color:#475569;">
                    <i class="bi bi-check-circle" style="font-size:2rem;opacity:0.3;"></i>
                    <p class="mb-0 mt-2" style="font-size:0.75rem;">No alerts at this time</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Revenue by Service Type (Line Chart) -->
    @if(count($data['revenue_by_service_breakdown']['datasets'] ?? []) > 0)
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-graph-up text-success me-2"></i>Revenue by service
                </h6>
                <small>Service type breakdown</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:240px;">
                    <canvas id="revenueByServiceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Branch Performance Comparison -->
    @if(!$selectedBranch)
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-building text-info me-2"></i>Top branches
                </h6>
                <small>Revenue distribution</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:240px;">
                    <canvas id="branchRevenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
// Wait for DOM and Chart.js to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }
    
    console.log('Initializing Executive Charts...');
    console.log('Executive Data:', @json($data));

    // Chart.js configuration
    const GRID_COLOR = 'rgba(255,255,255,0.04)';
    const TICK_COLOR = '#334155';
    const BASE_CONFIG = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(10,14,28,0.95)',
                padding: 10,
                titleColor: '#94a3b8',
                bodyColor: '#e2e8f0'
            }
        },
        interaction: { intersect: false, mode: 'index' }
    };

    // Revenue Trend Chart
    const revenueTrendCtx = document.getElementById('revenueTrendChart');
    if (revenueTrendCtx) {
        try {
            const dateType = '{{ $dates['type'] }}';
            const labels = {!! json_encode($data['revenue_trend']->map(function($item) use ($dates) {
                if ($dates['type'] === 'hourly') {
                    return $item->period . ':00';
                } elseif ($dates['type'] === 'monthly') {
                    return \Carbon\Carbon::parse($item->period . '-01')->format('M Y');
                } else {
                    return \Carbon\Carbon::parse($item->period)->format('M d');
                }
            })) !!};
            
            const chartData = {!! json_encode($data['revenue_trend']->pluck('revenue')) !!};
            
            console.log('Revenue Trend Labels:', labels);
            console.log('Revenue Trend Data:', chartData);
            
            new Chart(revenueTrendCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue',
                        data: chartData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.08)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#0b1120',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    ...BASE_CONFIG,
                    plugins: {
                        ...BASE_CONFIG.plugins,
                        tooltip: {
                            ...BASE_CONFIG.plugins.tooltip,
                            callbacks: {
                                label: function(context) {
                                    return '₱' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            grid: { color: GRID_COLOR, drawBorder: false },
                            ticks: {
                                color: TICK_COLOR,
                                font: { size: 10 },
                                callback: function(value) {
                                    return '₱' + (value / 1000).toFixed(0) + 'k';
                                }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: TICK_COLOR, font: { size: 10 } }
                        }
                    }
                }
            });
            console.log('Revenue Trend Chart created successfully');
        } catch (error) {
            console.error('Error creating Revenue Trend Chart:', error);
        }
    } else {
        console.error('Revenue Trend Chart canvas not found');
    }

    // Revenue by Service Line Chart
    const revenueByServiceCtx = document.getElementById('revenueByServiceChart');
    if (revenueByServiceCtx) {
        try {
            const rbs = {!! json_encode($data['revenue_by_service_breakdown']) !!};
            const rbsColors = ['#3b82f6','#10b981','#f59e0b','#a855f7','#ec4899','#22c55e'];
            new Chart(revenueByServiceCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: rbs.labels,
                    datasets: rbs.datasets.map((ds, i) => ({
                        label: ds.label,
                        data: ds.data,
                        borderColor: rbsColors[i % rbsColors.length],
                        backgroundColor: i === 0 ? 'rgba(59,130,246,0.08)' : 'transparent',
                        fill: i === 0,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: rbsColors[i % rbsColors.length],
                        pointBorderColor: '#0b1120',
                        pointBorderWidth: 2,
                        borderDash: i > 0 ? [5,4] : []
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'bottom', labels: { usePointStyle: true, padding: 12, font: { size: 10 }, color: '#475569' } },
                        tooltip: { backgroundColor: 'rgba(10,14,28,0.95)', padding: 10, callbacks: { label: ctx => ctx.dataset.label + ': ₱' + ctx.parsed.y.toLocaleString() } }
                    },
                    interaction: { intersect: false, mode: 'index' },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false }, ticks: { color: '#334155', font: { size: 10 }, callback: v => '₱' + (v/1000).toFixed(0) + 'k' } },
                        x: { grid: { display: false }, ticks: { color: '#334155', font: { size: 10 } } }
                    }
                }
            });
        } catch (error) {
            console.error('Error creating Revenue by Service Chart:', error);
        }
    }

    // Branch Revenue Distribution Pie Chart
    const branchRevenueCtx = document.getElementById('branchRevenueChart');
    if (branchRevenueCtx) {
        try {
            const chartColors = ['#3b82f6', '#10b981', '#f59e0b', '#a855f7', '#ec4899'];
            new Chart(branchRevenueCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($data['branch_comparison']->pluck('name')) !!},
                    datasets: [{
                        data: {!! json_encode($data['branch_comparison']->pluck('revenue')) !!},
                        backgroundColor: chartColors.slice(0, {!! json_encode($data['branch_comparison']->count()) !!}),
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#475569',
                                font: { size: 10 },
                                padding: 12,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(10,14,28,0.95)',
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    return '₱' + context.parsed.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            console.log('Branch Revenue Chart created successfully');
        } catch (error) {
            console.error('Error creating Branch Revenue Chart:', error);
        }
    }
    
    console.log('All Executive Charts initialized');
}); // End DOMContentLoaded
</script>
@endpush
