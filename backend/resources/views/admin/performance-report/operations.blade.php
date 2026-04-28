{{-- Trend Charts Section --}}
<div class="row g-3 mb-3">
    {{-- Revenue vs Expenses --}}
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-slate-800">Revenue vs expenses</h6>
                    <small>Daily trend this week</small>
                </div>
                <div class="d-flex gap-3">
                    <span style="display:flex;align-items:center;gap:4px;font-size:10px;color:#94a3b8;"><span style="width:8px;height:8px;border-radius:2px;background:#10b981;display:inline-block;"></span>Revenue</span>
                    <span style="display:flex;align-items:center;gap:4px;font-size:10px;color:#94a3b8;"><span style="width:8px;height:8px;border-radius:2px;background:#ef4444;display:inline-block;"></span>Expenses</span>
                </div>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="trendRevExpChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Service Demand Trends --}}
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">Service demand trends</h6>
                <small>Service usage popularity this week</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="trendServiceDemandChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Staff Attendance Trends --}}
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-people text-info me-2"></i>Staff attendance trends
                </h6>
                <small>All branches daily attendance</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="trendStaffAttChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Daily Profit Trend --}}
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-graph-up text-success me-2"></i>Daily profit trend
                </h6>
                <small>Last 7 days profit analysis</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="trendProfitChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Laundry Status Trends --}}
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-cart text-primary me-2"></i>Laundry status trends
                </h6>
                <small>Lifecycle tracking this week</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="trendLaundryStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Pipeline by Branch --}}
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-slate-800">
                        <i class="bi bi-diagram-3 text-warning me-2"></i>Pipeline by branch
                    </h6>
                    <small>Laundry status per branch</small>
                </div>
                @if(!$selectedBranch)
                <a href="{{ route('admin.laundries.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:0.65rem;">View all</a>
                @endif
            </div>
            <div class="card-body-modern" style="max-height:220px;overflow-y:auto;">
                @foreach($data['pipeline_by_branch'] as $branch)
                <div class="mb-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-building" style="color:#fff;font-size:0.7rem;"></i>
                        </div>
                        <div>
                            <div style="font-size:0.75rem;font-weight:600;">{{ $branch['name'] }}</div>
                            <div style="font-size:0.62rem;color:#94a3b8;">{{ $branch['total'] }} laundries</div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        <span class="badge" style="background:rgba(129,140,248,0.2);color:#818cf8;font-size:0.62rem;">Received {{ $branch['counts']['received'] }}</span>
                        <span class="badge" style="background:rgba(34,211,238,0.2);color:#22d3ee;font-size:0.62rem;">Ready {{ $branch['counts']['ready'] }}</span>
                        <span class="badge" style="background:rgba(16,185,129,0.2);color:#10b981;font-size:0.62rem;">Paid {{ $branch['counts']['paid'] }}</span>
                        <span class="badge" style="background:rgba(74,222,128,0.2);color:#4ade80;font-size:0.62rem;">Completed {{ $branch['counts']['completed'] }}</span>
                        <span class="badge" style="background:rgba(248,113,113,0.2);color:#f87171;font-size:0.62rem;">Cancelled {{ $branch['counts']['cancelled'] }}</span>
                    </div>
                    <div class="mt-1" style="height:4px;background:rgba(0,0,0,0.1);border-radius:2px;overflow:hidden;">
                        @php $total = max($branch['total'], 1); @endphp
                        <div style="width:{{ round(($branch['counts']['completed'] + $branch['counts']['paid']) / $total * 100) }}%;height:100%;background:#10b981;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-3">

    <div class="col-6 col-md-2">
        <div class="metric-card-compact border-yellow">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-yellow-light"><i class="bi bi-clock-history"></i></div>
            </div>
            <div class="metric-label-compact">Pending</div>
            <div class="metric-value-large text-warning">{{ number_format($data['pending']) }}</div>
            <small class="text-muted">Awaiting process</small>
        </div>
    </div>

    <div class="col-6 col-md-2">
        <div class="metric-card-compact border-blue">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-blue-light"><i class="bi bi-arrow-repeat"></i></div>
            </div>
            <div class="metric-label-compact">Processing</div>
            <div class="metric-value-large text-info">{{ number_format($data['processing']) }}</div>
            <small class="text-muted">In progress</small>
        </div>
    </div>

    <div class="col-6 col-md-2">
        <div class="metric-card-compact border-purple">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-purple-light"><i class="bi bi-check-circle"></i></div>
            </div>
            <div class="metric-label-compact">Ready</div>
            <div class="metric-value-large text-primary">{{ number_format($data['ready']) }}</div>
            <small class="text-muted">For pickup</small>
        </div>
    </div>

    <div class="col-6 col-md-2">
        <div class="metric-card-compact border-green">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-green-light"><i class="bi bi-check-all"></i></div>
            </div>
            <div class="metric-label-compact">Completed</div>
            <div class="metric-value-large text-success">{{ number_format($data['completed']) }}</div>
            <small class="text-muted">Finished</small>
        </div>
    </div>

    <div class="col-6 col-md-2">
        <div class="metric-card-compact border-red">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-red-light"><i class="bi bi-x-circle"></i></div>
            </div>
            <div class="metric-label-compact">Cancelled</div>
            <div class="metric-value-large text-danger">{{ number_format($data['cancelled']) }}</div>
            <small class="text-muted">Rejected</small>
        </div>
    </div>

    <div class="col-6 col-md-2">
        <div class="metric-card-compact border-cyan">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-cyan-light"><i class="bi bi-speedometer2"></i></div>
                <span class="metric-change {{ $data['orders_growth'] >= 0 ? 'positive' : 'negative' }}">
                    <i class="bi bi-arrow-{{ $data['orders_growth'] >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($data['orders_growth']) }}%
                </span>
            </div>
            <div class="metric-label-compact">Avg time</div>
            <div class="metric-value-large text-slate-900">{{ $data['avg_processing_time'] }}h</div>
            <small class="text-muted">Processing time</small>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Orders by Status -->
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-pie-chart text-primary me-2"></i>Orders by status
                </h6>
                <small>Current order distribution</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:240px;">
                    <canvas id="ordersByStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Processing Time Trend -->
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-graph-up text-purple me-2"></i>Processing time trend
                </h6>
                <small>Average hours to complete</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:240px;">
                    <canvas id="processingTimeTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Orders by Service Type -->
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-bar-chart text-blue me-2"></i>Orders by service type
                </h6>
                <small>Service popularity</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:240px;">
                    <canvas id="ordersByServiceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Peak Hours -->
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-clock text-warning me-2"></i>Peak hours analysis
                </h6>
                <small>Busiest times of day</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:240px;">
                    <canvas id="peakHoursChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Orders by Branch -->
@if(!$selectedBranch)
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="modern-card">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-building text-success me-2"></i>Orders by branch
                </h6>
                <small>Volume comparison across branches</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="ordersByBranchChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Service Efficiency Breakdown -->
@if(count($data['service_efficiency_breakdown']) > 0)
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="modern-card">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-speedometer text-info me-2"></i>Service efficiency analysis
                </h6>
                <small>Completion rates and processing time by service</small>
            </div>
            <div class="card-body-modern">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size:0.75rem;">
                        <thead style="background:rgba(0,0,0,0.02);">
                            <tr>
                                <th style="padding:10px;">Service</th>
                                <th class="text-end" style="padding:10px;">Total Orders</th>
                                <th class="text-end" style="padding:10px;">Completion Rate</th>
                                <th class="text-end" style="padding:10px;">Avg Processing Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['service_efficiency_breakdown'] as $service)
                            <tr style="border-bottom:1px solid rgba(0,0,0,0.05);">
                                <td style="padding:10px;font-weight:500;">{{ $service->name }}</td>
                                <td class="text-end" style="padding:10px;">
                                    <span class="badge" style="background:#3b82f6;font-size:0.7rem;padding:3px 8px;">
                                        {{ $service->total_orders }}
                                    </span>
                                </td>
                                <td class="text-end" style="padding:10px;">
                                    <span class="badge {{ $service->completion_rate >= 90 ? 'bg-success' : ($service->completion_rate >= 70 ? 'bg-warning' : 'bg-danger') }}" style="font-size:0.7rem;padding:3px 8px;">
                                        {{ $service->completion_rate }}%
                                    </span>
                                </td>
                                <td class="text-end" style="padding:10px;font-weight:600;color:#475569;">
                                    {{ round($service->avg_processing_hours, 1) }}h
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
@endif

@push('scripts')
<script>
// Wait for DOM and Chart.js to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }
    
    console.log('Initializing Operations Charts...');
    console.log('Operations Data:', @json($data));

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

    // Orders by Status Chart (Doughnut)
    const ordersByStatusCtx = document.getElementById('ordersByStatusChart');
    if (ordersByStatusCtx) {
        try {
            new Chart(ordersByStatusCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'Processing', 'Ready', 'Completed', 'Cancelled'],
                    datasets: [{
                        data: [
                            {{ $data['pending'] }},
                            {{ $data['processing'] }},
                            {{ $data['ready'] }},
                            {{ $data['completed'] }},
                            {{ $data['cancelled'] }}
                        ],
                        backgroundColor: ['#fbbf24', '#3b82f6', '#8b5cf6', '#10b981', '#ef4444'],
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
            console.log('Orders by Status Chart created successfully');
        } catch (error) {
            console.error('Error creating Orders by Status Chart:', error);
        }
    }

    // Processing Time Trend Chart
    const processingTimeTrendCtx = document.getElementById('processingTimeTrendChart');
    if (processingTimeTrendCtx) {
        try {
            const dateType = '{{ $dates['type'] }}';
            const labels = {!! json_encode($data['processing_time_trend']->map(function($item) use ($dates) {
                if ($dates['type'] === 'hourly') {
                    return $item->period . ':00';
                } elseif ($dates['type'] === 'monthly') {
                    return \Carbon\Carbon::parse($item->period . '-01')->format('M Y');
                } else {
                    return \Carbon\Carbon::parse($item->period)->format('M d');
                }
            })) !!};
            
            new Chart(processingTimeTrendCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Avg Hours',
                        data: {!! json_encode($data['processing_time_trend']->pluck('avg_hours')) !!},
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.08)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#8b5cf6',
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
                                    return context.parsed.y.toFixed(1) + ' hours';
                                }
                            }
                        }
                    },
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
            console.log('Processing Time Trend Chart created successfully');
        } catch (error) {
            console.error('Error creating Processing Time Trend Chart:', error);
        }
    }

    // Orders by Service Chart
    const ordersByServiceCtx = document.getElementById('ordersByServiceChart');
    if (ordersByServiceCtx) {
        try {
            new Chart(ordersByServiceCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($data['orders_by_service']->pluck('name')) !!},
                    datasets: [{
                        label: 'Orders',
                        data: {!! json_encode($data['orders_by_service']->pluck('count')) !!},
                        backgroundColor: '#3b82f6',
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
            console.log('Orders by Service Chart created successfully');
        } catch (error) {
            console.error('Error creating Orders by Service Chart:', error);
        }
    }

    // Peak Hours Chart
    const peakHoursCtx = document.getElementById('peakHoursChart');
    if (peakHoursCtx) {
        try {
            new Chart(peakHoursCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($data['peak_hours']->map(function($item) {
                        return $item->hour . ':00';
                    })) !!},
                    datasets: [{
                        label: 'Orders',
                        data: {!! json_encode($data['peak_hours']->pluck('count')) !!},
                        backgroundColor: '#fbbf24',
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
            console.log('Peak Hours Chart created successfully');
        } catch (error) {
            console.error('Error creating Peak Hours Chart:', error);
        }
    }

    // Orders by Branch Chart
    const ordersByBranchCtx = document.getElementById('ordersByBranchChart');
    if (ordersByBranchCtx) {
        try {
            new Chart(ordersByBranchCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($data['orders_by_branch']->pluck('name')) !!},
                    datasets: [{
                        label: 'Orders',
                        data: {!! json_encode($data['orders_by_branch']->pluck('count')) !!},
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                        borderWidth: 0
                    }]
                },
                options: {
                    indexAxis: 'y',
                    ...BASE_CONFIG,
                    scales: { 
                        x: { 
                            beginAtZero: true,
                            grid: { color: GRID_COLOR, drawBorder: false },
                            ticks: { 
                                color: TICK_COLOR, 
                                font: { size: 10 },
                                stepSize: 1
                            }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { color: TICK_COLOR, font: { size: 10 } }
                        }
                    }
                }
            });
            console.log('Orders by Branch Chart created successfully');
        } catch (error) {
            console.error('Error creating Orders by Branch Chart:', error);
        }
    }
    
    console.log('All Operations Charts initialized');
}); // End DOMContentLoaded

// Trend Charts
(function initTrendCharts() {
    if (typeof Chart === 'undefined') return setTimeout(initTrendCharts, 100);

    const GRID = 'rgba(255,255,255,0.04)';
    const TICK = '#334155';
    const BASE = {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(10,14,28,0.95)', padding: 10, titleColor: '#94a3b8', bodyColor: '#e2e8f0' } },
        interaction: { intersect: false, mode: 'index' }
    };
    const SCALES_PESO = {
        x: { grid: { display: false }, ticks: { color: TICK, font: { size: 10 } } },
        y: { beginAtZero: true, grid: { color: GRID, drawBorder: false }, ticks: { color: TICK, font: { size: 10 }, callback: v => '₱' + (v/1000).toFixed(0) + 'k' } }
    };
    const SCALES_COUNT = {
        x: { grid: { display: false }, ticks: { color: TICK, font: { size: 10 } } },
        y: { beginAtZero: true, grid: { color: GRID, drawBorder: false }, ticks: { color: TICK, font: { size: 10 }, stepSize: 1, callback: v => Number.isInteger(v) ? v : '' } }
    };

    // Revenue vs Expenses
    const revExpEl = document.getElementById('trendRevExpChart');
    if (revExpEl) {
        const d = @json($data['revenue_vs_expenses_trend']);
        new Chart(revExpEl, { type: 'line', data: { labels: d.labels, datasets: [
            { label: 'Revenue', data: d.revenue, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 3, pointBackgroundColor: '#10b981', pointBorderColor: '#0b1120', pointBorderWidth: 2 },
            { label: 'Expenses', data: d.expenses, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.06)', fill: true, tension: 0.4, borderWidth: 2, borderDash: [5,4], pointRadius: 3, pointBackgroundColor: '#ef4444', pointBorderColor: '#0b1120', pointBorderWidth: 2 }
        ]}, options: { ...BASE, scales: SCALES_PESO } });
    }

    // Service Demand
    const sdEl = document.getElementById('trendServiceDemandChart');
    if (sdEl) {
        const d = @json($data['service_demand_trend']);
        const sdColors = ['#60a5fa','#a78bfa','#fbbf24','#10b981','#f87171'];
        if (!d.datasets || !d.datasets.length) {
            sdEl.closest('.card-body-modern').innerHTML = '<div class="text-center py-4" style="color:#475569;"><i class="bi bi-graph-up" style="font-size:1.5rem;opacity:.3;"></i><p style="font-size:0.72rem;margin-top:6px;">No service data yet</p></div>';
        } else {
            new Chart(sdEl, { type: 'line', data: { labels: d.labels, datasets: d.datasets.map((ds, i) => ({
                label: ds.label, data: ds.data, borderColor: sdColors[i % sdColors.length],
                backgroundColor: i === 0 ? 'rgba(96,165,250,0.08)' : 'transparent',
                fill: i === 0, borderWidth: 2, tension: 0.4, pointRadius: 3,
                pointBackgroundColor: sdColors[i % sdColors.length], pointBorderColor: '#0b1120', pointBorderWidth: 2,
                borderDash: i > 0 ? [5,4] : []
            }))}, options: { ...BASE, plugins: { ...BASE.plugins, legend: { display: true, position: 'top', align: 'end', labels: { usePointStyle: true, padding: 10, font: { size: 10 }, color: '#475569' } } }, scales: SCALES_COUNT } });
        }
    }

    // Staff Attendance
    const saEl = document.getElementById('trendStaffAttChart');
    if (saEl) {
        const d = @json($data['staff_attendance_trend']);
        const saColors = ['#818cf8','#f59e0b','#10b981','#06b6d4','#8b5cf6'];
        if (!d.datasets || !d.datasets.length) {
            saEl.closest('.card-body-modern').innerHTML = '<div class="text-center py-4" style="color:#475569;"><i class="bi bi-people" style="font-size:1.5rem;opacity:.3;"></i><p style="font-size:0.72rem;margin-top:6px;">No attendance data yet</p></div>';
        } else {
            new Chart(saEl, { type: 'line', data: { labels: d.labels, datasets: d.datasets.map((ds, i) => ({
                label: ds.label, data: ds.data, borderColor: saColors[i % saColors.length],
                backgroundColor: 'transparent', borderWidth: 2, tension: 0.4, pointRadius: 3,
                pointBackgroundColor: saColors[i % saColors.length], pointBorderColor: '#0b1120', pointBorderWidth: 2,
                borderDash: i > 0 ? [5,4] : []
            }))}, options: { ...BASE, plugins: { ...BASE.plugins, legend: { display: true, position: 'bottom', labels: { usePointStyle: true, padding: 10, font: { size: 10 }, color: '#475569' } } }, scales: SCALES_COUNT } });
        }
    }

    // Daily Profit
    const ptEl = document.getElementById('trendProfitChart');
    if (ptEl) {
        const d = @json($data['profit_trend']);
        new Chart(ptEl, { type: 'line', data: { labels: d.labels, datasets: [{
            label: 'Profit', data: d.data, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)',
            fill: true, tension: 0.4, borderWidth: 2, pointRadius: 3, pointBackgroundColor: '#10b981', pointBorderColor: '#0b1120', pointBorderWidth: 2
        }]}, options: { ...BASE, scales: SCALES_PESO } });
    }

    // Laundry Status
    const lsEl = document.getElementById('trendLaundryStatusChart');
    if (lsEl) {
        const d = @json($data['laundry_status_trend']);
        const lsColors = { 'Received': '#818cf8', 'Ready': '#22d3ee', 'Paid': '#10b981', 'Completed': '#4ade80', 'Cancelled': '#f87171' };
        new Chart(lsEl, { type: 'line', data: { labels: d.labels, datasets: d.datasets.map((ds, i) => ({
            label: ds.label, data: ds.data, borderColor: lsColors[ds.label] || '#94a3b8',
            backgroundColor: 'transparent', borderWidth: 2, tension: 0.4, pointRadius: 3,
            pointBackgroundColor: lsColors[ds.label] || '#94a3b8', pointBorderColor: '#0b1120', pointBorderWidth: 2,
            borderDash: i > 0 ? [5,4] : []
        }))}, options: { ...BASE, plugins: { ...BASE.plugins, legend: { display: true, position: 'bottom', labels: { usePointStyle: true, padding: 8, font: { size: 10 }, color: '#475569' } } }, scales: SCALES_COUNT } });
    }
})();
</script>
@endpush
