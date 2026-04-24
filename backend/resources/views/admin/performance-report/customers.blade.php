<div class="row g-2 mb-3">
    <!-- KPI Cards -->
    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-blue">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-blue-light"><i class="bi bi-people"></i></div>
            </div>
            <div class="metric-label-compact">Total customers</div>
            <div class="metric-value-large text-slate-900">{{ number_format($data['total_customers']) }}</div>
            <small class="text-muted">All registered</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-green">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-green-light"><i class="bi bi-person-plus"></i></div>
            </div>
            <div class="metric-label-compact">New customers</div>
            <div class="metric-value-large text-success">{{ number_format($data['new_customers']) }}</div>
            <small class="text-muted">This period</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-yellow">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-yellow-light"><i class="bi bi-star-fill"></i></div>
            </div>
            <div class="metric-label-compact">Avg rating</div>
            <div class="metric-value-large text-warning">{{ $data['avg_rating'] }}/5</div>
            <small class="text-muted">Customer satisfaction</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-cyan">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-cyan-light"><i class="bi bi-arrow-repeat"></i></div>
            </div>
            <div class="metric-label-compact">Retention rate</div>
            <div class="metric-value-large text-info">{{ $data['retention_rate'] }}%</div>
            <small class="text-muted">Returning customers</small>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Customer Growth -->
    <div class="col-lg-8">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-graph-up text-success me-2"></i>Customer growth
                </h6>
                <small>New customer acquisition over time</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="customerGrowthChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Segmentation -->
    <div class="col-lg-4">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-pie-chart text-primary me-2"></i>Segmentation
                </h6>
                <small>By registration type</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="customerSegmentationChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Top Customers -->
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-trophy text-warning me-2"></i>Top customers
                </h6>
                <small>By total spending</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:240px;">
                    <canvas id="topCustomersChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Satisfaction Distribution -->
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-star-fill text-warning me-2"></i>Satisfaction distribution
                </h6>
                <small>Customer ratings breakdown</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:240px;">
                    <canvas id="satisfactionDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Wait for DOM and Chart.js to be ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }
    
    console.log('Initializing Customers Charts...');

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

    // Customer Growth Chart
    const customerGrowthCtx = document.getElementById('customerGrowthChart');
    if (customerGrowthCtx) {
        try {
            const dateType = '{{ $dates['type'] }}';
            const labels = {!! json_encode($data['customer_growth']->map(function($item) use ($dates) {
                if ($dates['type'] === 'hourly') {
                    return $item->period . ':00';
                } elseif ($dates['type'] === 'monthly') {
                    return \Carbon\Carbon::parse($item->period . '-01')->format('M Y');
                } else {
                    return \Carbon\Carbon::parse($item->period)->format('M d');
                }
            })) !!};
            
            new Chart(customerGrowthCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'New Customers',
                        data: {!! json_encode($data['customer_growth']->pluck('count')) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#0b1120',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    ...BASE_CONFIG,
                    scales: {
                        y: { 
                            beginAtZero: true,
                            grid: { color: GRID_COLOR, drawBorder: false },
                            ticks: { color: TICK_COLOR, font: { size: 10 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: TICK_COLOR, font: { size: 10 } }
                        }
                    }
                }
            });
            console.log('Customer Growth Chart created');
        } catch (error) {
            console.error('Error creating Customer Growth Chart:', error);
        }
    }

    // Customer Segmentation Chart
    const customerSegmentationCtx = document.getElementById('customerSegmentationChart');
    if (customerSegmentationCtx) {
        try {
            new Chart(customerSegmentationCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($data['customer_segmentation']->map(function($item) {
                        return ucfirst(str_replace('_', ' ', $item->registration_type));
                    })) !!},
                    datasets: [{
                        data: {!! json_encode($data['customer_segmentation']->pluck('count')) !!},
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b'],
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
                            padding: 10
                        }
                    }
                }
            });
            console.log('Customer Segmentation Chart created');
        } catch (error) {
            console.error('Error creating Customer Segmentation Chart:', error);
        }
    }

    // Top Customers Chart
    const topCustomersCtx = document.getElementById('topCustomersChart');
    if (topCustomersCtx) {
        try {
            new Chart(topCustomersCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($data['top_customers']->pluck('name')) !!},
                    datasets: [{
                        label: 'Total Spent',
                        data: {!! json_encode($data['top_customers']->pluck('laundries_sum_total_amount')) !!},
                        backgroundColor: '#8b5cf6',
                        borderRadius: 6,
                        borderWidth: 0
                    }]
                },
                options: {
                    indexAxis: 'y',
                    ...BASE_CONFIG,
                    plugins: {
                        ...BASE_CONFIG.plugins,
                        tooltip: {
                            ...BASE_CONFIG.plugins.tooltip,
                            callbacks: {
                                label: function(context) {
                                    return '₱' + context.parsed.x.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: { 
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
                        y: {
                            grid: { display: false },
                            ticks: { color: TICK_COLOR, font: { size: 10 } }
                        }
                    }
                }
            });
            console.log('Top Customers Chart created');
        } catch (error) {
            console.error('Error creating Top Customers Chart:', error);
        }
    }

    // Satisfaction Distribution Chart
    const satisfactionDistributionCtx = document.getElementById('satisfactionDistributionChart');
    if (satisfactionDistributionCtx) {
        try {
            new Chart(satisfactionDistributionCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($data['satisfaction_distribution']->map(function($item) {
                        return $item->rating . ' Stars';
                    })) !!},
                    datasets: [{
                        label: 'Count',
                        data: {!! json_encode($data['satisfaction_distribution']->pluck('count')) !!},
                        backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#10b981', '#22c55e'],
                        borderRadius: 6,
                        borderWidth: 0
                    }]
                },
                options: {
                    ...BASE_CONFIG,
                    scales: { 
                        y: { 
                            beginAtZero: true,
                            grid: { color: GRID_COLOR, drawBorder: false },
                            ticks: { 
                                color: TICK_COLOR, 
                                font: { size: 10 },
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: TICK_COLOR, font: { size: 10 } }
                        }
                    }
                }
            });
            console.log('Satisfaction Distribution Chart created');
        } catch (error) {
            console.error('Error creating Satisfaction Distribution Chart:', error);
        }
    }
    
    console.log('All Customers Charts initialized');
}); // End DOMContentLoaded
</script>
@endpush
