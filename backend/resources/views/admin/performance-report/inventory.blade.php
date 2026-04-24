<div class="row g-2 mb-3">
    <!-- KPI Cards -->
    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-blue">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-blue-light"><i class="bi bi-box-seam"></i></div>
            </div>
            <div class="metric-label-compact">Total items</div>
            <div class="metric-value-large text-slate-900">{{ number_format($data['total_items']) }}</div>
            <small class="text-muted">In inventory</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-yellow">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-yellow-light"><i class="bi bi-exclamation-triangle"></i></div>
            </div>
            <div class="metric-label-compact">Low stock</div>
            <div class="metric-value-large text-warning">{{ number_format($data['low_stock']) }}</div>
            <small class="text-muted">Need reorder</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-red">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-red-light"><i class="bi bi-x-circle"></i></div>
            </div>
            <div class="metric-label-compact">Out of stock</div>
            <div class="metric-value-large text-danger">{{ number_format($data['out_of_stock']) }}</div>
            <small class="text-muted">Critical items</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-compact border-green">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-green-light"><i class="bi bi-cash-coin"></i></div>
            </div>
            <div class="metric-label-compact">Total value</div>
            <div class="metric-value-large text-success" style="font-size:1rem;">₱{{ number_format($data['total_value'], 0) }}</div>
            <small class="text-muted">Stock worth</small>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Stock Levels -->
    <div class="col-lg-8">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-bar-chart text-blue me-2"></i>Current stock levels
                </h6>
                <small>Top 10 items by quantity</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="stockLevelsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Value by Category -->
    <div class="col-lg-4">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-pie-chart text-primary me-2"></i>Stock value by category
                </h6>
                <small>Inventory distribution</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="stockValueByCategoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Top Used Items -->
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-graph-down text-success me-2"></i>Top used items
                </h6>
                <small>Most consumed supplies</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:240px;">
                    <canvas id="topUsedItemsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Trend -->
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-graph-up text-purple me-2"></i>Purchase trend
                </h6>
                <small>Inventory purchases over time</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:240px;">
                    <canvas id="purchaseTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inventory tab: Initializing charts...');
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
        const stockLevelsCtx = document.getElementById('stockLevelsChart');
        if (stockLevelsCtx) {
            const stockData = {!! json_encode($data['stock_levels'] ?? []) !!};
            console.log('Stock levels data:', stockData);
            
            if (!stockData || stockData.length === 0) {
                stockLevelsCtx.parentElement.innerHTML = '<p class="text-center text-muted py-4">No stock data available</p>';
            } else {
                const labels = stockData.map(item => item.inventory_item?.name || 'Unknown');
                const stockValues = stockData.map(item => item.current_stock || 0);
                const reorderPoints = stockData.map(item => item.reorder_point || 0);
                
                new Chart(stockLevelsCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Current Stock',
                            data: stockValues,
                            backgroundColor: function(context) {
                                const stock = context.parsed.y;
                                const reorder = reorderPoints[context.dataIndex];
                                return stock <= reorder ? '#ef4444' : '#3b82f6';
                            },
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
                console.log('Stock Levels Chart created');
            }
        }
    } catch (error) {
        console.error('Error creating Stock Levels Chart:', error);
    }

    try {
        const stockValueByCategoryCtx = document.getElementById('stockValueByCategoryChart');
        if (stockValueByCategoryCtx) {
            const categoryData = {!! json_encode($data['stock_value_by_category'] ?? []) !!};
            console.log('Stock value by category data:', categoryData);
            
            if (!categoryData || categoryData.length === 0) {
                stockValueByCategoryCtx.parentElement.innerHTML = '<p class="text-center text-muted py-4">No category data available</p>';
            } else {
                new Chart(stockValueByCategoryCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: categoryData.map(item => item.name),
                        datasets: [{
                            data: categoryData.map(item => item.value),
                            backgroundColor: ['#3b82f6', '#10b981', '#fbbf24', '#8b5cf6', '#ec4899'],
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
                console.log('Stock Value by Category Chart created');
            }
        }
    } catch (error) {
        console.error('Error creating Stock Value by Category Chart:', error);
    }

    try {
        const topUsedItemsCtx = document.getElementById('topUsedItemsChart');
        if (topUsedItemsCtx) {
            const topUsedData = {!! json_encode($data['top_used_items'] ?? []) !!};
            console.log('Top used items data:', topUsedData);
            
            if (!topUsedData || topUsedData.length === 0) {
                topUsedItemsCtx.parentElement.innerHTML = '<p class="text-center text-muted py-4">No usage data available</p>';
            } else {
                new Chart(topUsedItemsCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: topUsedData.map(item => item.name),
                        datasets: [{
                            label: 'Quantity Used',
                            data: topUsedData.map(item => item.total_used),
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
                console.log('Top Used Items Chart created');
            }
        }
    } catch (error) {
        console.error('Error creating Top Used Items Chart:', error);
    }

    try {
        const purchaseTrendCtx = document.getElementById('purchaseTrendChart');
        if (purchaseTrendCtx) {
            const dateType = '{{ $dates['type'] }}';
            const purchaseData = {!! json_encode($data['purchase_trend'] ?? []) !!};
            console.log('Purchase trend data:', purchaseData);
            
            if (!purchaseData || purchaseData.length === 0) {
                purchaseTrendCtx.parentElement.innerHTML = '<p class="text-center text-muted py-4">No purchase data available</p>';
            } else {
                const labels = purchaseData.map(item => {
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
                
                new Chart(purchaseTrendCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Purchase Amount',
                            data: purchaseData.map(item => item.amount),
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
                console.log('Purchase Trend Chart created');
            }
        }
    } catch (error) {
        console.error('Error creating Purchase Trend Chart:', error);
    }
});
</script>
@endpush
