@extends('admin.layouts.app')

@section('title', 'Revenue Report')

@section('content')
<div class="container-fluid px-4 py-5">
    {{-- Header Section --}}
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-end">
            <div>
                <h1 class="mb-2 fw-bold style="color: var(--text-primary) !important;"" style="font-size: 2rem; letter-spacing: -0.5px;">Revenue Analytics</h1>
                <p class="text-muted mb-0" style="font-size: 0.95rem;">Comprehensive revenue trends, financial analytics, and performance insights</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </a>
                <form method="POST" action="{{ route('admin.reports.export') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="type" value="revenue">
                    <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                    <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download me-2"></i>Export
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Key Metrics Cards --}}
    @php
        $totalRevenue = $data->sum('revenue');
        $totalLaundries = $data->sum('laundries');
        $avgRevenuePerDay = $data->count() > 0 ? round($totalRevenue / $data->count(), 2) : 0;
        $avgPerLaundry = $totalLaundries > 0 ? round($totalRevenue / $totalLaundries, 2) : 0;

        // Calculate trend
        $midPoint = ceil($data->count() / 2);
        $firstHalf = $data->take($midPoint)->sum('revenue');
        $secondHalf = $data->skip($midPoint)->sum('revenue');
        $trendPercentage = $firstHalf > 0 ? round((($secondHalf - $firstHalf) / $firstHalf) * 100, 1) : 0;
        $trendDirection = $trendPercentage >= 0 ? 'up' : 'down';
    @endphp

    <div class="row g-3 mb-5">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card h-100" style="background-color: var(--card-bg) !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small fw-500" style="font-size: 0.85rem; letter-spacing: 0.3px;">TOTAL REVENUE</p>
                            <h3 class="mb-0 fw-bold" style="color: var(--text-primary) !important;">₱{{ number_format($totalRevenue, 2) }}</h3>
                        </div>
                        <div class="stat-icon bg-success">
                            <i class="bi bi-currency-peso"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center text-success small">
                        <i class="bi bi-arrow-up-right me-1"></i>
                        <span>{{ $data->count() }} days tracked</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card h-100" style="background-color: var(--card-bg) !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small fw-500" style="font-size: 0.85rem; letter-spacing: 0.3px;">AVG ORDER VALUE</p>
                            <h3 class="mb-0 fw-bold" style="color: var(--text-primary) !important;">₱{{ number_format($avgPerLaundry, 2) }}</h3>
                        </div>
                        <div class="stat-icon bg-primary">
                            <i class="bi bi-tag"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center text-{{ $trendPercentage >= 0 ? 'success' : 'danger' }} small">
                        <i class="bi bi-arrow-{{ $trendDirection }}-right me-1"></i>
                        <span>{{ abs($trendPercentage) }}% {{ $trendDirection == 'up' ? 'increase' : 'decrease' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card h-100" style="background-color: var(--card-bg) !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small fw-500" style="font-size: 0.85rem; letter-spacing: 0.3px;">CUSTOMER VALUE</p>
                            <h3 class="mb-0 fw-bold" style="color: var(--text-primary) !important;">₱{{ number_format($customerAnalytics['avg_customer_value'], 2) }}</h3>
                        </div>
                        <div class="stat-icon bg-info">
                            <i class="bi bi-person-check"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center text-muted small">
                        <i class="bi bi-dash me-1"></i>
                        <span>{{ $customerAnalytics['total_customers'] }} customers</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card h-100" style="background-color: var(--card-bg) !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small fw-500" style="font-size: 0.85rem; letter-spacing: 0.3px;">DAILY AVERAGE</p>
                            <h3 class="mb-0 fw-bold" style="color: var(--text-primary) !important;">₱{{ number_format($avgRevenuePerDay, 2) }}</h3>
                        </div>
                        <div class="stat-icon bg-warning">
                            <i class="bi bi-graph-up"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center text-muted small">
                        <i class="bi bi-dash me-1"></i>
                        <span>Per day average</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Date Filter Card --}}
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.reports.revenue') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-600 style="color: var(--text-primary) !important;" mb-2">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" style="border-radius: 8px;" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-600 style="color: var(--text-primary) !important;" mb-2">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" style="border-radius: 8px;" value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-funnel me-2"></i>Apply Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Revenue Analytics Grid --}}
    <div class="row g-4 mb-5">
        {{-- Monthly Trends Chart --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm chart-card" style="background-color: var(--card-bg) !important;">
                <div class="card-header border-0 p-4" style="background-color: var(--card-bg) !important;">
                    <h6 class="mb-0 fw-bold style="color: var(--text-primary) !important;"">
                        <i class="bi bi-graph-up text-primary me-2"></i>Monthly Revenue Trends
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="monthlyTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Service Revenue Breakdown --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm chart-card" style="background-color: var(--card-bg) !important;">
                <div class="card-header border-0 p-4" style="background-color: var(--card-bg) !important;">
                    <h6 class="mb-0 fw-bold style="color: var(--text-primary) !important;"">
                        <i class="bi bi-pie-chart text-success me-2"></i>Service Revenue
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="serviceRevenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Methods & Peak Hours --}}
    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm" style="background-color: var(--card-bg) !important;">
                <div class="card-header border-0 p-4" style="background-color: var(--card-bg) !important;">
                    <h6 class="mb-0 fw-bold style="color: var(--text-primary) !important;"">
                        <i class="bi bi-credit-card text-info me-2"></i>Payment Methods
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if($paymentMethods->count() > 0)
                        @foreach($paymentMethods as $method)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-500">{{ ucfirst($method->payment_method ?? 'Cash') }}</span>
                                    <span class="fw-bold">₱{{ number_format($method->revenue, 2) }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: {{ $paymentMethods->sum('revenue') > 0 ? round(($method->revenue / $paymentMethods->sum('revenue')) * 100, 1) : 0 }}%"></div>
                                </div>
                                <small class="text-muted">{{ $method->count }} transactions</small>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No payment data available</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm chart-card" style="background-color: var(--card-bg) !important;">
                <div class="card-header border-0 p-4" style="background-color: var(--card-bg) !important;">
                    <h6 class="mb-0 fw-bold style="color: var(--text-primary) !important;"">
                        <i class="bi bi-clock text-warning me-2"></i>Peak Hours Revenue
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="peakHoursChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Branch Performance & Top Customers --}}
    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm" style="background-color: var(--card-bg) !important;">
                <div class="card-header border-0 p-4" style="background-color: var(--card-bg) !important;">
                    <h6 class="mb-0 fw-bold style="color: var(--text-primary) !important;"">
                        <i class="bi bi-building text-primary me-2"></i>Branch Performance
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if($branchRevenue->count() > 0)
                        @foreach($branchRevenue->take(5) as $branch)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-500">{{ $branch['name'] }}</span>
                                    <span class="fw-bold">₱{{ number_format($branch['revenue'], 2) }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $branchRevenue->sum('revenue') > 0 ? round(($branch['revenue'] / $branchRevenue->sum('revenue')) * 100, 1) : 0 }}%"></div>
                                </div>
                                <small class="text-muted">{{ $branch['count'] }} orders • ₱{{ number_format($branch['avg_value'], 2) }} avg</small>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No branch data available</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm" style="background-color: var(--card-bg) !important;">
                <div class="card-header border-0 p-4" style="background-color: var(--card-bg) !important;">
                    <h6 class="mb-0 fw-bold style="color: var(--text-primary) !important;"">
                        <i class="bi bi-star text-success me-2"></i>Top Customers
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if($customerAnalytics['top_customers']->count() > 0)
                        @foreach($customerAnalytics['top_customers']->take(5) as $customer)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <span class="fw-500 d-block">{{ $customer->name }}</span>
                                    <small class="text-muted">{{ $customer->email }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-success">₱{{ number_format($customer->laundries_sum_total_amount, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No customer data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Seasonal Patterns --}}
    <div class="row g-4 mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm chart-card" style="background-color: var(--card-bg) !important;">
                <div class="card-header border-0 p-4" style="background-color: var(--card-bg) !important;">
                    <h6 class="mb-0 fw-bold style="color: var(--text-primary) !important;"">
                        <i class="bi bi-calendar3 text-info me-2"></i>Seasonal Revenue Patterns (Last 12 Months)
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="chart-container" style="position: relative; height: 350px;">
                        <canvas id="seasonalChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Daily Revenue Table --}}
    <div class="card border-0 shadow-sm" style="background-color: var(--card-bg) !important;">
        <div class="card-header bg-light border-0 p-4 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold style="color: var(--text-primary) !important;"">
                <i class="bi bi-table style="color: var(--text-primary) !important;" me-2"></i>Daily Revenue Details
            </h6>
            <span class="badge bg-light style="color: var(--text-primary) !important;"">{{ $data->count() }} days</span>
        </div>
        <div class="card-body p-0">
            @if($data->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-600" style="width: 35%;">Date</th>
                                <th class="text-center fw-600" style="width: 25%;">Orders</th>
                                <th class="text-end fw-600" style="width: 25%;">Revenue</th>
                                <th class="text-end fw-600" style="width: 15%;">Avg/Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $row)
                                <tr class="align-middle">
                                    <td>
                                        <span class="fw-500">{{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}</span>
                                        <br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($row->date)->format('l') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light style="color: var(--text-primary) !important;"" style="font-size: 0.9rem;">{{ $row->laundries }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-600 style="color: var(--text-primary) !important;"">₱{{ number_format($row->revenue, 2) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <small class="text-muted">₱{{ number_format($row->revenue / max($row->laundries, 1), 2) }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td><i class="bi bi-check-circle text-success me-2"></i>Total</td>
                                <td class="text-center"><span class="badge bg-success">{{ $totalLaundries }}</span></td>
                                <td class="text-end"><span class="text-success">₱{{ number_format($totalRevenue, 2) }}</span></td>
                                <td class="text-end"><span class="text-success">₱{{ number_format($avgPerLaundry, 2) }}</span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-graph-down text-muted" style="font-size: 3rem; opacity: 0.6;"></i>
                    <p class="text-muted mt-3 mb-0">No revenue data for this period</p>
                    <small class="text-muted">Try adjusting your date range filters</small>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.stat-card {
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--bs-success), transparent);
    border-radius: 12px 12px 0 0;
}

.stat-card:hover {
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12) !important;
    transform: translateY(-2px);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-icon.bg-success { background: linear-gradient(135deg, #198754, #146c43); }
.stat-icon.bg-primary { background: linear-gradient(135deg, #0d6efd, #0a58ca); }
.stat-icon.bg-info { background: linear-gradient(135deg, #0dcaf0, #0aa2c0); }
.stat-icon.bg-warning { background: linear-gradient(135deg, #ffc107, #e0a800); }

.chart-card {
    border-radius: 12px;
    transition: all 0.3s ease;
}

.chart-card:hover {
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
}

.chart-container {
    position: relative;
    width: 100%;
}

.table-light { background-color: #f8f9fa; }
.fw-600 { font-weight: 600; }

table tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

table tbody tr:hover {
    background-color: #f8f9fa;
}

.card {
    transition: all 0.2s ease;
}

.card:hover {
    border-color: #dee2e6;
}

/* Responsive adjustments */
@media (max-width: 991px) {
    .chart-container {
        height: 280px !important;
    }
}

@media (max-width: 767px) {
    .stat-card {
        margin-bottom: 1rem;
    }
    
    .chart-container {
        height: 250px !important;
    }
    
    .card-body {
        padding: 1rem !important;
    }
    
    h1 {
        font-size: 1.5rem !important;
    }
}

@media (max-width: 575px) {
    .chart-container {
        height: 220px !important;
    }
}
</style>

@push('scripts')
<script src="{{ asset('assets/chart.js/chart.umd.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Common chart options
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false
        },
        animation: {
            duration: 1000,
            easing: 'easeInOutQuart'
        }
    };

    // Monthly Trends Chart - Advanced Line with Gradient
    const monthlyCtx = document.getElementById('monthlyTrendsChart')?.getContext('2d');
    if (monthlyCtx) {
        const gradient = monthlyCtx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(25, 135, 84, 0.3)');
        gradient.addColorStop(0.5, 'rgba(25, 135, 84, 0.1)');
        gradient.addColorStop(1, 'rgba(25, 135, 84, 0)');

        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach($monthlyTrends as $trend)
                        '{{ \Carbon\Carbon::parse($trend->month)->format('M Y') }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Revenue',
                    data: [
                        @foreach($monthlyTrends as $trend)
                            {{ $trend->revenue }},
                        @endforeach
                    ],
                    borderColor: '#198754',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#198754',
                    pointBorderWidth: 3,
                    pointHoverBackgroundColor: '#198754',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.parsed.y.toLocaleString('en-PH', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.08)',
                            drawBorder: true,
                            borderColor: 'rgba(0, 0, 0, 0.15)',
                            borderWidth: 1.5
                        },
                        ticks: {
                            padding: 10,
                            font: { size: 11 },
                            callback: function(value) {
                                return '₱' + (value / 1000).toFixed(0) + 'k';
                            }
                        }
                    },
                    x: {
                        grid: { 
                            display: true,
                            color: 'rgba(0, 0, 0, 0.08)',
                            drawBorder: true,
                            borderColor: 'rgba(0, 0, 0, 0.15)',
                            borderWidth: 1.5
                        },
                        ticks: {
                            padding: 10,
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }

    // Service Revenue Chart - Advanced Doughnut
    const serviceCtx = document.getElementById('serviceRevenueChart')?.getContext('2d');
    if (serviceCtx) {
        new Chart(serviceCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    @foreach($serviceRevenue->take(5) as $service)
                        '{{ $service->name }}',
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach($serviceRevenue->take(5) as $service)
                            {{ $service->revenue }},
                        @endforeach
                    ],
                    backgroundColor: [
                        '#198754', '#0d6efd', '#ffc107', '#dc3545', '#6f42c1'
                    ],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 15,
                    hoverBorderWidth: 4
                }]
            },
            options: {
                ...commonOptions,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 11, weight: '500' },
                            usePointStyle: true,
                            pointStyle: 'circle'
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
                                return context.label + ': ₱' + context.parsed.toLocaleString('en-PH', {minimumFractionDigits: 2}) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Peak Hours Chart - Advanced Bar with Gradient
    const peakCtx = document.getElementById('peakHoursChart')?.getContext('2d');
    if (peakCtx) {
        const peakGradient = peakCtx.createLinearGradient(0, 0, 0, 400);
        peakGradient.addColorStop(0, '#ffc107');
        peakGradient.addColorStop(1, '#ffdb6d');

        new Chart(peakCtx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($peakHours as $hour)
                        '{{ $hour->hour }}:00',
                    @endforeach
                ],
                datasets: [{
                    label: 'Revenue',
                    data: [
                        @foreach($peakHours as $hour)
                            {{ $hour->revenue }},
                        @endforeach
                    ],
                    backgroundColor: peakGradient,
                    borderRadius: 8,
                    borderSkipped: false,
                    hoverBackgroundColor: '#e0a800'
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.parsed.y.toLocaleString('en-PH', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.08)',
                            drawBorder: true,
                            borderColor: 'rgba(0, 0, 0, 0.15)',
                            borderWidth: 1.5
                        },
                        ticks: {
                            padding: 10,
                            font: { size: 11 },
                            callback: function(value) {
                                return '₱' + (value / 1000).toFixed(0) + 'k';
                            }
                        }
                    },
                    x: {
                        grid: { 
                            display: true,
                            color: 'rgba(0, 0, 0, 0.08)',
                            drawBorder: true,
                            borderColor: 'rgba(0, 0, 0, 0.15)',
                            borderWidth: 1.5
                        },
                        ticks: {
                            padding: 10,
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }

    // Seasonal Chart - Advanced Area Line Chart
    const seasonalCtx = document.getElementById('seasonalChart')?.getContext('2d');
    if (seasonalCtx) {
        const seasonalGradient = seasonalCtx.createLinearGradient(0, 0, 0, 400);
        seasonalGradient.addColorStop(0, 'rgba(13, 202, 240, 0.4)');
        seasonalGradient.addColorStop(0.5, 'rgba(13, 202, 240, 0.2)');
        seasonalGradient.addColorStop(1, 'rgba(13, 202, 240, 0)');

        const seasonalData = [
            @foreach($seasonalData as $season)
                {{ $season->revenue }},
            @endforeach
        ];

        new Chart(seasonalCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach($seasonalData as $season)
                        '{{ $season->month_name }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Revenue',
                    data: seasonalData,
                    borderColor: '#0dcaf0',
                    backgroundColor: seasonalGradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 9,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0dcaf0',
                    pointBorderWidth: 3,
                    pointHoverBackgroundColor: '#0dcaf0',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.parsed.y.toLocaleString('en-PH', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.08)',
                            drawBorder: true,
                            borderColor: 'rgba(0, 0, 0, 0.15)',
                            borderWidth: 1.5
                        },
                        ticks: {
                            padding: 10,
                            font: { size: 11 },
                            callback: function(value) {
                                return '₱' + (value / 1000).toFixed(0) + 'k';
                            }
                        }
                    },
                    x: {
                        grid: { 
                            display: true,
                            color: 'rgba(0, 0, 0, 0.08)',
                            drawBorder: true,
                            borderColor: 'rgba(0, 0, 0, 0.15)',
                            borderWidth: 1.5
                        },
                        ticks: {
                            padding: 10,
                            font: { size: 11 },
                            maxRotation: 0,
                            minRotation: 0
                        }
                    }
                }
            }
        });
    }

    // Responsive chart height adjustment
    function adjustChartHeights() {
        const width = window.innerWidth;
        const charts = [
            { id: 'monthlyTrendsChart', desktop: 300, mobile: 250 },
            { id: 'serviceRevenueChart', desktop: 300, mobile: 280 },
            { id: 'peakHoursChart', desktop: 300, mobile: 250 },
            { id: 'seasonalChart', desktop: 350, mobile: 280 }
        ];

        charts.forEach(chart => {
            const canvas = document.getElementById(chart.id);
            if (canvas) {
                canvas.style.height = (width < 768 ? chart.mobile : chart.desktop) + 'px';
            }
        });
    }

    adjustChartHeights();
    window.addEventListener('resize', adjustChartHeights);
});
</script>
@endpush
@endsection