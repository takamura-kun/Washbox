<div class="row g-2 mb-3">
    <!-- KPI Cards -->
    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-blue">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-blue-light"><i class="bi bi-people"></i></div>
            </div>
            <div class="metric-label-compact">Total staff</div>
            <div class="metric-value-large text-slate-900">{{ number_format($data['total_staff']) }}</div>
            <small class="text-muted">All employees</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-green">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-green-light"><i class="bi bi-check-circle"></i></div>
            </div>
            <div class="metric-label-compact">Attendance rate</div>
            <div class="metric-value-large text-success">{{ $data['attendance_rate'] }}%</div>
            <small class="text-muted">Present today</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-cyan">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-cyan-light"><i class="bi bi-clock"></i></div>
            </div>
            <div class="metric-label-compact">Avg hours worked</div>
            <div class="metric-value-large text-info">{{ $data['avg_hours_worked'] }}h</div>
            <small class="text-muted">Per employee</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-yellow">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-yellow-light"><i class="bi bi-clock-history"></i></div>
            </div>
            <div class="metric-label-compact">Total overtime</div>
            <div class="metric-value-large text-warning">{{ $data['total_overtime'] }}h</div>
            <small class="text-muted">Extra hours</small>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Attendance Trend -->
    <div class="col-lg-8">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-graph-up text-success me-2"></i>Attendance trend
                </h6>
                <small>Daily attendance rate</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="attendanceTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff by Status -->
    <div class="col-lg-4">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-pie-chart text-primary me-2"></i>Staff by status
                </h6>
                <small>Current attendance breakdown</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="staffByStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Staff Productivity -->
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-bar-chart text-blue me-2"></i>Staff productivity
                </h6>
                <small>Orders processed per staff member</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:240px;">
                    <canvas id="staffProductivityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Requests -->
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-calendar-x text-warning me-2"></i>Leave requests
                </h6>
                <small>Leave type distribution</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:240px;">
                    <canvas id="leaveRequestsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Staff tab: Initializing charts...');
    
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

    try {
        const attendanceTrendCtx = document.getElementById('attendanceTrendChart');
        if (attendanceTrendCtx) {
            const labels = {!! json_encode($data['attendance_trend']->map(function($item) use ($dates) {
                if ($dates['type'] === 'hourly') {
                    return $item->period . ':00';
                } elseif ($dates['type'] === 'monthly') {
                    return \Carbon\Carbon::parse($item->period . '-01')->format('M Y');
                } else {
                    return \Carbon\Carbon::parse($item->period)->format('M d');
                }
            })) !!};
            
            new Chart(attendanceTrendCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Attendance Rate %',
                        data: {!! json_encode($data['attendance_trend']->map(function($item) {
                            return $item->total > 0 ? round(($item->present / $item->total) * 100, 1) : 0;
                        })) !!},
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
                    plugins: {
                        ...BASE_CONFIG.plugins,
                        tooltip: {
                            ...BASE_CONFIG.plugins.tooltip,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            max: 100,
                            grid: { color: GRID_COLOR, drawBorder: false },
                            ticks: {
                                color: TICK_COLOR,
                                font: { size: 10 },
                                callback: function(value) {
                                    return value + '%';
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
            console.log('Attendance Trend Chart created');
        }
    } catch (error) {
        console.error('Error creating Attendance Trend Chart:', error);
    }

    try {
        const staffByStatusCtx = document.getElementById('staffByStatusChart');
        if (staffByStatusCtx) {
            new Chart(staffByStatusCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($data['staff_by_status']->map(function($item) {
                        return ucfirst(str_replace('_', ' ', $item->status));
                    })) !!},
                    datasets: [{
                        data: {!! json_encode($data['staff_by_status']->pluck('count')) !!},
                        backgroundColor: ['#10b981', '#fbbf24', '#ef4444', '#9ca3af'],
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
            console.log('Staff by Status Chart created');
        }
    } catch (error) {
        console.error('Error creating Staff by Status Chart:', error);
    }

    try {
        const staffProductivityCtx = document.getElementById('staffProductivityChart');
        if (staffProductivityCtx) {
            new Chart(staffProductivityCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($data['staff_productivity']->pluck('name')) !!},
                    datasets: [{
                        label: 'Orders Processed',
                        data: {!! json_encode($data['staff_productivity']->pluck('orders_processed')) !!},
                        backgroundColor: '#3b82f6',
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
            console.log('Staff Productivity Chart created');
        }
    } catch (error) {
        console.error('Error creating Staff Productivity Chart:', error);
    }

    try {
        const leaveRequestsCtx = document.getElementById('leaveRequestsChart');
        if (leaveRequestsCtx) {
            new Chart(leaveRequestsCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($data['leave_requests']->map(function($item) {
                        return ucfirst($item->leave_type);
                    })) !!},
                    datasets: [{
                        label: 'Count',
                        data: {!! json_encode($data['leave_requests']->pluck('count')) !!},
                        backgroundColor: ['#ef4444', '#3b82f6', '#fbbf24', '#8b5cf6'],
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
            console.log('Leave Requests Chart created');
        }
    } catch (error) {
        console.error('Error creating Leave Requests Chart:', error);
    }
});
</script>
@endpush
