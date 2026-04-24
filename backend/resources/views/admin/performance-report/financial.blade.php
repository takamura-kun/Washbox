<div class="row g-2 mb-3">
    <!-- KPI Cards -->
    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-green">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-green-light"><i class="bi bi-cash-stack"></i></div>
            </div>
            <div class="metric-label-compact">Total revenue</div>
            <div class="metric-value-large text-success" style="font-size:1rem;">₱{{ number_format($data['total_revenue'], 0) }}</div>
            <small class="text-muted">This period</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-red">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-red-light"><i class="bi bi-wallet2"></i></div>
            </div>
            <div class="metric-label-compact">Total expenses</div>
            <div class="metric-value-large text-danger" style="font-size:1rem;">₱{{ number_format($data['total_expenses'], 0) }}</div>
            <small class="text-muted">Operating costs</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-blue">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-blue-light"><i class="bi bi-graph-up"></i></div>
                <span class="metric-change {{ $data['profit_growth'] >= 0 ? 'positive' : 'negative' }}">
                    <i class="bi bi-arrow-{{ $data['profit_growth'] >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($data['profit_growth']) }}%
                </span>
            </div>
            <div class="metric-label-compact">Net profit</div>
            <div class="metric-value-large text-primary" style="font-size:1rem;">₱{{ number_format($data['profit'], 0) }}</div>
            <small class="text-muted">After expenses</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-cyan">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-cyan-light"><i class="bi bi-percent"></i></div>
            </div>
            <div class="metric-label-compact">Profit margin</div>
            <div class="metric-value-large text-info">{{ $data['profit_margin'] }}%</div>
            <small class="text-muted">Margin rate</small>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Revenue vs Expenses Trend -->
    <div class="col-lg-8">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-graph-up text-success me-2"></i>Revenue vs expenses
                </h6>
                <small>Daily trend this {{ ucfirst(str_replace('_', ' ', $dateRange)) }}</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="revenueVsExpensesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Methods Distribution -->
    <div class="col-lg-4">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-credit-card text-primary me-2"></i>Payment methods
                </h6>
                <small>Revenue by payment type</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="paymentMethodsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Profit Trend -->
@if(count($data['monthly_profit_trend']) > 0)
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="modern-card">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-bar-chart text-blue me-2"></i>Monthly profit trend
                </h6>
                <small>Revenue, Expenses, and Profit Analysis</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="monthlyProfitTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row g-3 mb-3">
    <!-- Daily Profit Bar Chart -->
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>Daily profit
                </h6>
                <small>Profit per period (revenue minus expenses)</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:220px;">
                    <canvas id="dailyProfitChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Laundry vs Retail Trend -->
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-graph-up-arrow text-cyan me-2"></i>Laundry vs retail revenue
                </h6>
                <small>Revenue stream comparison</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:220px;">
                    <canvas id="laundryVsRetailChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Cumulative Revenue Line Chart -->
    <div class="col-12">
        <div class="modern-card">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-graph-up text-success me-2"></i>Cumulative revenue
                </h6>
                <small>Running total of revenue over the period</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="cumulativeRevenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Expenses by Category -->
    <div class="col-lg-{{ $selectedBranch ? '12' : '6' }}">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-pie-chart text-danger me-2"></i>Expenses by category
                </h6>
                <small>Expense breakdown</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:240px;">
                    <canvas id="expensesByCategoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue by Branch -->
    @if(!$selectedBranch)
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-building text-success me-2"></i>Revenue by branch
                </h6>
                <small>Branch performance comparison</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:240px;">
                    <canvas id="revenueByBranchChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Financial tab: Initializing charts...');
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
        const revenueVsExpensesCtx = document.getElementById('revenueVsExpensesChart');
        if (revenueVsExpensesCtx) {
            const dateType = '{{ $dates['type'] }}';
            const revenueData = {!! json_encode($data['revenue_vs_expenses']['revenue'] ?? []) !!};
            const expensesData = {!! json_encode($data['revenue_vs_expenses']['expenses'] ?? []) !!};
            console.log('Revenue data:', revenueData);
            console.log('Expenses data:', expensesData);
            
            if (!revenueData || revenueData.length === 0) {
                revenueVsExpensesCtx.parentElement.innerHTML = '<p class="text-center text-muted py-4">No financial data available</p>';
            } else {
                const labels = revenueData.map(item => {
                    if (dateType === 'hourly') {
                        return item.period + ':00';
                    } else if (dateType === 'monthly') {
                        const date = new Date(item.period + '-01');
                        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                    } else {
                        const date = new Date(item.period);
                        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    }
                });
            
                new Chart(revenueVsExpensesCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Revenue',
                                data: revenueData.map(item => item.amount),
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
                            },
                            {
                                label: 'Expenses',
                                data: expensesData.map(item => item.amount),
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.06)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                borderDash: [5, 4],
                                pointRadius: 4,
                                pointBackgroundColor: '#ef4444',
                                pointBorderColor: '#0b1120',
                                pointBorderWidth: 2,
                                pointHoverRadius: 6
                            }
                        ]
                    },
                    options: {
                        ...BASE_CONFIG,
                        plugins: {
                            legend: { 
                                display: true,
                                position: 'top',
                                align: 'end',
                                labels: {
                                    usePointStyle: true,
                                    padding: 12,
                                    font: { size: 10 },
                                    color: '#475569'
                                }
                            },
                            tooltip: {
                                ...BASE_CONFIG.plugins.tooltip,
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
                console.log('Revenue vs Expenses Chart created');
            }
        }
    } catch (error) {
        console.error('Error creating Revenue vs Expenses Chart:', error);
    }

    try {
        const paymentMethodsCtx = document.getElementById('paymentMethodsChart');
        if (paymentMethodsCtx) {
            const paymentData = {!! json_encode($data['payment_methods'] ?? []) !!};
            console.log('Payment methods data:', paymentData);
            
            if (!paymentData || paymentData.length === 0) {
                paymentMethodsCtx.parentElement.innerHTML = '<p class="text-center text-muted py-4">No payment data available</p>';
            } else {
                new Chart(paymentMethodsCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: paymentData.map(item => item.payment_method ? item.payment_method.charAt(0).toUpperCase() + item.payment_method.slice(1) : 'Cash'),
                        datasets: [{
                            data: paymentData.map(item => item.total),
                            backgroundColor: ['#3b82f6', '#10b981', '#fbbf24', '#8b5cf6'],
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
                                        return context.label + ': ₱' + context.parsed.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
                console.log('Payment Methods Chart created');
            }
        }
    } catch (error) {
        console.error('Error creating Payment Methods Chart:', error);
    }

    try {
        const expensesByCategoryCtx = document.getElementById('expensesByCategoryChart');
        if (expensesByCategoryCtx) {
            const expensesData = {!! json_encode($data['expenses_by_category'] ?? []) !!};
            console.log('Expenses by category data:', expensesData);
            
            if (!expensesData || expensesData.length === 0) {
                expensesByCategoryCtx.parentElement.innerHTML = '<p class="text-center text-muted py-4">No expense data available</p>';
            } else {
                new Chart(expensesByCategoryCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: expensesData.map(item => item.name),
                        datasets: [{
                            label: 'Amount',
                            data: expensesData.map(item => item.total),
                            backgroundColor: '#ef4444',
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
                console.log('Expenses by Category Chart created');
            }
        }
    } catch (error) {
        console.error('Error creating Expenses by Category Chart:', error);
    }

    try {
        const revenueByBranchCtx = document.getElementById('revenueByBranchChart');
        if (revenueByBranchCtx) {
            const branchData = {!! json_encode($data['revenue_by_branch'] ?? []) !!};
            console.log('Revenue by branch data:', branchData);
            
            if (!branchData || branchData.length === 0) {
                revenueByBranchCtx.parentElement.innerHTML = '<p class="text-center text-muted py-4">No branch data available</p>';
            } else {
                new Chart(revenueByBranchCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: branchData.map(item => item.name),
                        datasets: [{
                            label: 'Revenue',
                            data: branchData.map(item => item.total),
                            backgroundColor: '#10b981',
                            borderRadius: 6,
                            borderWidth: 0
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
                console.log('Revenue by Branch Chart created');
            }
        }
    } catch (error) {
        console.error('Error creating Revenue by Branch Chart:', error);
    }

    try {
        const monthlyProfitTrendCtx = document.getElementById('monthlyProfitTrendChart');
        if (monthlyProfitTrendCtx) {
            const monthlyData = {!! json_encode($data['monthly_profit_trend'] ?? []) !!};
            console.log('Monthly profit trend data:', monthlyData);
            
            if (!monthlyData || monthlyData.length === 0) {
                monthlyProfitTrendCtx.parentElement.innerHTML = '<p class="text-center text-muted py-4">No monthly data available</p>';
            } else {
                new Chart(monthlyProfitTrendCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: monthlyData.map(item => item.month),
                        datasets: [
                            {
                                label: 'Revenue',
                                data: monthlyData.map(item => item.revenue),
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.08)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointRadius: 4,
                                pointBackgroundColor: '#10b981'
                            },
                            {
                                label: 'Expenses',
                                data: monthlyData.map(item => item.expenses),
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.06)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointRadius: 4,
                                pointBackgroundColor: '#ef4444'
                            },
                            {
                                label: 'Profit',
                                data: monthlyData.map(item => item.profit),
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.08)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointRadius: 4,
                                pointBackgroundColor: '#3b82f6'
                            }
                        ]
                    },
                    options: {
                        ...BASE_CONFIG,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    color: '#475569',
                                    font: { size: 10 },
                                    padding: 12,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                ...BASE_CONFIG.plugins.tooltip,
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
                console.log('Monthly Profit Trend Chart created');
            }
        }
    } catch (error) {
        console.error('Error creating Monthly Profit Trend Chart:', error);
    }
    try {
        const dailyProfitCtx = document.getElementById('dailyProfitChart');
        if (dailyProfitCtx) {
            const dailyData = {!! json_encode($data['daily_profit'] ?? []) !!};
            const dateType = '{{ $dates['type'] }}';
            const labels = dailyData.map(item => {
                if (dateType === 'hourly') return item.period + ':00';
                if (dateType === 'monthly') return new Date(item.period + '-01').toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                return new Date(item.period).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            const profits = dailyData.map(item => item.profit);
            new Chart(dailyProfitCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Profit',
                        data: profits,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.08)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: profits.map(v => v >= 0 ? '#3b82f6' : '#ef4444'),
                        pointBorderColor: '#0b1120',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6,
                        segment: {
                            borderColor: ctx => ctx.p0.parsed.y < 0 || ctx.p1.parsed.y < 0 ? '#ef4444' : '#3b82f6',
                            backgroundColor: ctx => ctx.p0.parsed.y < 0 || ctx.p1.parsed.y < 0 ? 'rgba(239,68,68,0.08)' : 'rgba(59,130,246,0.08)'
                        }
                    }]
                },
                options: {
                    ...BASE_CONFIG,
                    plugins: {
                        ...BASE_CONFIG.plugins,
                        tooltip: { ...BASE_CONFIG.plugins.tooltip, callbacks: { label: ctx => '₱' + ctx.parsed.y.toLocaleString() } }
                    },
                    scales: {
                        y: { grid: { color: GRID_COLOR }, ticks: { color: TICK_COLOR, font: { size: 10 }, callback: v => '₱' + (v/1000).toFixed(0) + 'k' } },
                        x: { grid: { display: false }, ticks: { color: TICK_COLOR, font: { size: 10 } } }
                    }
                }
            });
        }
    } catch (e) { console.error('Daily Profit Chart:', e); }

    try {
        const laundryVsRetailCtx = document.getElementById('laundryVsRetailChart');
        if (laundryVsRetailCtx) {
            const lvr = {!! json_encode($data['laundry_vs_retail_trend'] ?? []) !!};
            const dateType = '{{ $dates['type'] }}';
            const labels = lvr.map(item => {
                if (dateType === 'hourly') return item.period + ':00';
                if (dateType === 'monthly') return new Date(item.period + '-01').toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                return new Date(item.period).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            new Chart(laundryVsRetailCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Laundry', data: lvr.map(i => i.laundry), borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.08)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 4, pointBackgroundColor: '#3b82f6' },
                        { label: 'Retail',  data: lvr.map(i => i.retail),  borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.08)',  fill: true, tension: 0.4, borderWidth: 2, pointRadius: 4, pointBackgroundColor: '#f59e0b' }
                    ]
                },
                options: {
                    ...BASE_CONFIG,
                    plugins: {
                        legend: { display: true, position: 'top', align: 'end', labels: { usePointStyle: true, font: { size: 10 }, color: '#475569', padding: 12 } },
                        tooltip: { ...BASE_CONFIG.plugins.tooltip, callbacks: { label: ctx => ctx.dataset.label + ': ₱' + ctx.parsed.y.toLocaleString() } }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: GRID_COLOR }, ticks: { color: TICK_COLOR, font: { size: 10 }, callback: v => '₱' + (v/1000).toFixed(0) + 'k' } },
                        x: { grid: { display: false }, ticks: { color: TICK_COLOR, font: { size: 10 } } }
                    }
                }
            });
        }
    } catch (e) { console.error('Laundry vs Retail Chart:', e); }

    try {
        const cumulativeCtx = document.getElementById('cumulativeRevenueChart');
        if (cumulativeCtx) {
            const cumData = {!! json_encode($data['cumulative_revenue'] ?? []) !!};
            const dateType = '{{ $dates['type'] }}';
            const labels = cumData.map(item => {
                if (dateType === 'hourly') return item.period + ':00';
                if (dateType === 'monthly') return new Date(item.period + '-01').toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                return new Date(item.period).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            new Chart(cumulativeCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Cumulative Revenue',
                        data: cumData.map(i => i.cumulative),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#10b981'
                    }]
                },
                options: {
                    ...BASE_CONFIG,
                    plugins: { ...BASE_CONFIG.plugins, tooltip: { ...BASE_CONFIG.plugins.tooltip, callbacks: { label: ctx => '₱' + ctx.parsed.y.toLocaleString() } } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: GRID_COLOR }, ticks: { color: TICK_COLOR, font: { size: 10 }, callback: v => '₱' + (v/1000).toFixed(0) + 'k' } },
                        x: { grid: { display: false }, ticks: { color: TICK_COLOR, font: { size: 10 } } }
                    }
                }
            });
        }
    } catch (e) { console.error('Cumulative Revenue Chart:', e); }

});
</script>
@endpush
