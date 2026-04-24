{{-- Advanced Analytics Charts Section --}}
<div class="row g-3 mb-4">
    {{-- Revenue Trend Chart --}}
    <div class="col-lg-8">
        <div class="analytics-card">
            <div class="analytics-card-header">
                <div>
                    <h6 class="analytics-card-title">Revenue Trend</h6>
                    <p class="analytics-card-subtitle">Last 30 Days Performance</p>
                </div>
                <div class="analytics-card-badge">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
            </div>
            <div class="analytics-card-body">
                <canvas id="revenueTrendChart" height="80"></canvas>
            </div>
        </div>
    </div>

    {{-- Revenue Breakdown Pie --}}
    <div class="col-lg-4">
        <div class="analytics-card">
            <div class="analytics-card-header">
                <div>
                    <h6 class="analytics-card-title">Revenue Mix</h6>
                    <p class="analytics-card-subtitle">This Month</p>
                </div>
                <div class="analytics-card-badge">
                    <i class="bi bi-pie-chart"></i>
                </div>
            </div>
            <div class="analytics-card-body">
                <canvas id="revenueBreakdownChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Service Distribution --}}
    <div class="col-lg-4">
        <div class="analytics-card">
            <div class="analytics-card-header">
                <div>
                    <h6 class="analytics-card-title">Top Services</h6>
                    <p class="analytics-card-subtitle">By Order Count</p>
                </div>
                <div class="analytics-card-badge">
                    <i class="bi bi-bar-chart"></i>
                </div>
            </div>
            <div class="analytics-card-body">
                <canvas id="serviceDistributionChart" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- Payment Methods --}}
    <div class="col-lg-4">
        <div class="analytics-card">
            <div class="analytics-card-header">
                <div>
                    <h6 class="analytics-card-title">Payment Methods</h6>
                    <p class="analytics-card-subtitle">Customer Preferences</p>
                </div>
                <div class="analytics-card-badge">
                    <i class="bi bi-credit-card"></i>
                </div>
            </div>
            <div class="analytics-card-body">
                <canvas id="paymentMethodsChart" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- Order Status --}}
    <div class="col-lg-4">
        <div class="analytics-card">
            <div class="analytics-card-header">
                <div>
                    <h6 class="analytics-card-title">Order Pipeline</h6>
                    <p class="analytics-card-subtitle">Current Status</p>
                </div>
                <div class="analytics-card-badge">
                    <i class="bi bi-funnel"></i>
                </div>
            </div>
            <div class="analytics-card-body">
                <canvas id="orderStatusChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.analytics-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.15);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease-out;
}

.analytics-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.analytics-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(102, 126, 234, 0.25);
}

.analytics-card:hover::before {
    opacity: 1;
}

.analytics-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.analytics-card-title {
    color: #ffffff;
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0;
    letter-spacing: -0.02em;
}

.analytics-card-subtitle {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.75rem;
    margin: 4px 0 0 0;
    font-weight: 500;
}

.analytics-card-badge {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 1.1rem;
    animation: pulse 2s ease-in-out infinite;
}

.analytics-card-body {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    padding: 16px;
    position: relative;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

/* Gradient variations for different cards */
.analytics-card:nth-child(1) {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.analytics-card:nth-child(2) {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.analytics-card:nth-child(3) {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.analytics-card:nth-child(4) {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.analytics-card:nth-child(5) {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

/* Dark mode support */
[data-theme="dark"] .analytics-card-body {
    background: rgba(30, 41, 59, 0.95);
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js global configuration
    Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
    Chart.defaults.plugins.legend.display = true;
    Chart.defaults.plugins.legend.position = 'bottom';
    Chart.defaults.plugins.legend.labels.padding = 15;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.pointStyle = 'circle';

    // Revenue Trend Chart
    const revenueTrendCtx = document.getElementById('revenueTrendChart');
    if (revenueTrendCtx) {
        const revenueData = @json($financial_analytics['daily_revenue_trend'] ?? []);
        const labels = Object.keys(revenueData).map(date => {
            const d = new Date(date);
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        const values = Object.values(revenueData);

        new Chart(revenueTrendCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue',
                    data: values,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#667eea',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 13, weight: '600' },
                        bodyFont: { size: 14, weight: '700' },
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            callback: function(value) {
                                return '₱' + (value / 1000).toFixed(0) + 'k';
                            },
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 10,
                            font: { size: 11 }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    // Revenue Breakdown Pie Chart
    const revenueBreakdownCtx = document.getElementById('revenueBreakdownChart');
    if (revenueBreakdownCtx) {
        const laundryRev = {{ $financial_analytics['revenue'] ?? 0 }} - {{ $retail_sales_today['revenue'] ?? 0 }};
        const retailRev = {{ $retail_sales_today['revenue'] ?? 0 }};

        new Chart(revenueBreakdownCtx, {
            type: 'doughnut',
            data: {
                labels: ['Laundry Services', 'Retail Sales'],
                datasets: [{
                    data: [laundryRev, retailRev],
                    backgroundColor: ['#667eea', '#f5576c'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12, weight: '600' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ₱' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    // Service Distribution Chart
    const serviceDistCtx = document.getElementById('serviceDistributionChart');
    if (serviceDistCtx) {
        const services = @json($topServices ?? []);
        new Chart(serviceDistCtx, {
            type: 'doughnut',
            data: {
                labels: services.map(s => s.name),
                datasets: [{
                    data: services.map(s => s.count),
                    backgroundColor: ['#4facfe', '#43e97b', '#fa709a', '#fee140', '#667eea'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            font: { size: 11, weight: '600' }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    // Payment Methods Chart
    const paymentCtx = document.getElementById('paymentMethodsChart');
    if (paymentCtx) {
        const payments = @json($paymentMethods ?? []);
        new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cash', 'GCash', 'Card', 'Online'],
                datasets: [{
                    data: [
                        payments.cash || 0,
                        payments.gcash || 0,
                        payments.card || 0,
                        payments.online || 0
                    ],
                    backgroundColor: ['#43e97b', '#38f9d7', '#667eea', '#f5576c'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            font: { size: 11, weight: '600' }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    // Order Status Chart
    const orderStatusCtx = document.getElementById('orderStatusChart');
    if (orderStatusCtx) {
        const pipeline = @json($pipeline ?? []);
        new Chart(orderStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Received', 'Processing', 'Ready', 'Paid', 'Completed'],
                datasets: [{
                    data: [
                        pipeline.received || 0,
                        pipeline.processing || 0,
                        pipeline.ready || 0,
                        pipeline.paid || 0,
                        pipeline.completed || 0
                    ],
                    backgroundColor: ['#667eea', '#4facfe', '#43e97b', '#fee140', '#f5576c'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            font: { size: 11, weight: '600' }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }
});
</script>
@endpushn '₱' + (value / 1000).toFixed(0) + 'k';
                            },
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 10,
                            font: { size: 11 }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    // Revenue Breakdown Pie Chart
    const revenueBreakdownCtx = document.getElementById('revenueBreakdownChart');
    if (revenueBreakdownCtx) {
        const laundryRev = {{ $financial_analytics['revenue'] ?? 0 }} - {{ $retail_sales_today['revenue'] ?? 0 }};
        const retailRev = {{ $retail_sales_today['revenue'] ?? 0 }};

        new Chart(revenueBreakdownCtx, {
            type: 'doughnut',
            data: {
                labels: ['Laundry Services', 'Retail Sales'],
                datasets: [{
                    data: [laundryRev, retailRev],
                    backgroundColor: ['#667eea', '#f5576c'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12, weight: '600' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ₱' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    // Service Distribution Chart
    const serviceDistCtx = document.getElementById('serviceDistributionChart');
    if (serviceDistCtx) {
        const services = @json($topServices ?? []);
        new Chart(serviceDistCtx, {
            type: 'doughnut',
            data: {
                labels: services.map(s => s.name),
                datasets: [{
                    data: services.map(s => s.count),
                    backgroundColor: ['#4facfe', '#43e97b', '#fa709a', '#fee140', '#667eea'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            font: { size: 11, weight: '600' }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    // Payment Methods Chart
    const paymentCtx = document.getElementById('paymentMethodsChart');
    if (paymentCtx) {
        const payments = @json($paymentMethods ?? []);
        new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cash', 'GCash', 'Card', 'Online'],
                datasets: [{
                    data: [
                        payments.cash || 0,
                        payments.gcash || 0,
                        payments.card || 0,
                        payments.online || 0
                    ],
                    backgroundColor: ['#43e97b', '#38f9d7', '#667eea', '#f5576c'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            font: { size: 11, weight: '600' }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    // Order Status Chart
    const orderStatusCtx = document.getElementById('orderStatusChart');
    if (orderStatusCtx) {
        const pipeline = @json($pipeline ?? []);
        new Chart(orderStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Received', 'Processing', 'Ready', 'Paid', 'Completed'],
                datasets: [{
                    data: [
                        pipeline.received || 0,
                        pipeline.processing || 0,
                        pipeline.ready || 0,
                        pipeline.paid || 0,
                        pipeline.completed || 0
                    ],
                    backgroundColor: ['#667eea', '#4facfe', '#43e97b', '#fee140', '#f5576c'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            font: { size: 11, weight: '600' }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }
});
</script>
@endpush
