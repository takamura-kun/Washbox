@extends('branch.layouts.app')

@section('page-title', 'Branch Analytics')

@push('styles')
<style>
    .metric-card-compact {
        border-left: 3px solid;
        transition: transform 0.2s;
    }
    .metric-card-compact:hover {
        transform: translateY(-2px);
    }
    .modern-card {
        border-radius: 12px;
        border: 1px solid var(--border-color);
        background: var(--card-bg);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .card-header-modern {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-color);
        background: var(--card-bg);
    }
    .card-body-modern {
        padding: 16px;
        background: var(--card-bg);
    }
    .card {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
    }
    .card-body {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1">Branch Analytics Dashboard</h4>
            <p class="small mb-0" style="color: var(--text-secondary);">Comprehensive analytics for {{ auth()->user()->branch->name ?? 'your branch' }}</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <form method="GET" action="{{ route('branch.analytics.index') }}" class="d-flex gap-2 align-items-center">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate->format('Y-m-d') }}">
                <span class="small">to</span>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate->format('Y-m-d') }}">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
            </form>
            <a href="{{ route('branch.analytics.export', request()->query()) }}" class="btn btn-outline-success btn-sm">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-2">
            <div class="card metric-card-compact border-0 shadow-sm h-100" style="border-left-color: #3b82f6 !important;">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size: 0.7rem; color: var(--text-secondary);">Total Laundries</span>
                        <i class="bi bi-basket3 text-primary" style="font-size: 1rem;"></i>
                    </div>
                    <h3 class="mb-0 fw-bold" style="font-size: 1.5rem;">{{ number_format($stats['total_laundries'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card metric-card-compact border-0 shadow-sm h-100" style="border-left-color: #10b981 !important;">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size: 0.7rem; color: var(--text-secondary);">Total Revenue</span>
                        <i class="bi bi-cash-stack text-success" style="font-size: 1rem;"></i>
                    </div>
                    <h3 class="mb-0 fw-bold text-success" style="font-size: 1.3rem;">₱{{ number_format($stats['total_revenue'] ?? 0, 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card metric-card-compact border-0 shadow-sm h-100" style="border-left-color: #06b6d4 !important;">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size: 0.7rem; color: var(--text-secondary);">Total Customers</span>
                        <i class="bi bi-people text-info" style="font-size: 1rem;"></i>
                    </div>
                    <h3 class="mb-0 fw-bold" style="font-size: 1.5rem;">{{ number_format($stats['total_customers'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card metric-card-compact border-0 shadow-sm h-100" style="border-left-color: #f59e0b !important;">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size: 0.7rem; color: var(--text-secondary);">Retail Sales</span>
                        <i class="bi bi-cart-check text-warning" style="font-size: 1rem;"></i>
                    </div>
                    <h3 class="mb-0 fw-bold text-warning" style="font-size: 1.3rem;">₱{{ number_format($stats['retail_sales'] ?? 0, 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card metric-card-compact border-0 shadow-sm h-100" style="border-left-color: #8b5cf6 !important;">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size: 0.7rem; color: var(--text-secondary);">Total Profit</span>
                        <i class="bi bi-graph-up" style="font-size: 1rem; color: #8b5cf6;"></i>
                    </div>
                    <h3 class="mb-0 fw-bold" style="font-size: 1.3rem; color: #8b5cf6;">₱{{ number_format($stats['total_profit'] ?? 0, 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card metric-card-compact border-0 shadow-sm h-100" style="border-left-color: #ef4444 !important;">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size: 0.7rem; color: var(--text-secondary);">Total Expenses</span>
                        <i class="bi bi-receipt text-danger" style="font-size: 1rem;"></i>
                    </div>
                    <h3 class="mb-0 fw-bold text-danger" style="font-size: 1.3rem;">₱{{ number_format($stats['total_expenses'] ?? 0, 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue / Expenses / Profit Trend --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="modern-card h-100">
                <div class="card-header-modern d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">
                            <i class="bi bi-graph-up text-success me-2"></i>Revenue, Expenses & Profit
                        </h6>
                        <small style="font-size: 0.75rem; color: var(--text-secondary);">{{ $startDate->format('M d') }} – {{ $endDate->format('M d, Y') }}</small>
                    </div>
                </div>
                <div class="card-body-modern">
                    <div style="position:relative;width:100%;height:250px;">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Revenue Breakdown --}}
        <div class="col-lg-4">
            <div class="modern-card h-100">
                <div class="card-header-modern">
                    <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">
                        <i class="bi bi-pie-chart text-primary me-2"></i>Revenue Breakdown
                    </h6>
                    <small style="font-size: 0.75rem; color: var(--text-secondary);">Laundry vs Retail</small>
                </div>
                <div class="card-body-modern">
                    <div style="position:relative;width:100%;height:250px;">
                        <canvas id="revenueBreakdownChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 2 --}}
    <div class="row g-3 mb-3">
        {{-- Top Services --}}
        <div class="col-lg-6">
            <div class="modern-card h-100">
                <div class="card-header-modern">
                    <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">
                        <i class="bi bi-star text-warning me-2"></i>Most Used Services
                    </h6>
                    <small style="font-size: 0.75rem; color: var(--text-secondary);">Daily orders per service (last 7 days)</small>
                </div>
                <div class="card-body-modern">
                    <div style="position:relative;width:100%;height:280px;">
                        <canvas id="topServicesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Order Status Distribution --}}
        <div class="col-lg-6">
            <div class="modern-card h-100">
                <div class="card-header-modern">
                    <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">
                        <i class="bi bi-clipboard-check text-info me-2"></i>Order Status Distribution
                    </h6>
                    <small style="font-size: 0.75rem; color: var(--text-secondary);">Current order statuses</small>
                </div>
                <div class="card-body-modern">
                    <div style="position:relative;width:100%;height:280px;">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 3 --}}
    <div class="row g-3 mb-3">
        {{-- Peak Hours --}}
        <div class="col-lg-8">
            <div class="modern-card h-100">
                <div class="card-header-modern">
                    <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">
                        <i class="bi bi-clock text-purple me-2"></i>Peak Hours Analysis
                    </h6>
                    <small style="font-size: 0.75rem; color: var(--text-secondary);">Orders by hour of day</small>
                </div>
                <div class="card-body-modern">
                    <div style="position:relative;width:100%;height:250px;">
                        <canvas id="peakHoursChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Methods --}}
        <div class="col-lg-4">
            <div class="modern-card h-100">
                <div class="card-header-modern">
                    <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">
                        <i class="bi bi-credit-card text-success me-2"></i>Payment Methods
                    </h6>
                    <small style="font-size: 0.75rem; color: var(--text-secondary);">Revenue by payment type</small>
                </div>
                <div class="card-body-modern">
                    <div style="position:relative;width:100%;height:250px;">
                        <canvas id="paymentMethodsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 4 --}}
    <div class="row g-3 mb-3">
        {{-- Customer Satisfaction --}}
        <div class="col-lg-6">
            <div class="modern-card h-100">
                <div class="card-header-modern">
                    <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">
                        <i class="bi bi-star-fill text-warning me-2"></i>Customer Satisfaction
                    </h6>
                    <small style="font-size: 0.75rem; color: var(--text-secondary);">Ratings & reviews</small>
                </div>
                <div class="card-body-modern">
                    {{-- Average score --}}
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="text-center">
                            <div class="fw-bold" style="font-size: 2.8rem; line-height:1; color:#f59e0b;">{{ number_format($avgRating, 1) }}</div>
                            <div style="font-size:1.1rem; color:#f59e0b; letter-spacing:2px;">
                                @for($i=1;$i<=5;$i++)
                                    @if($i <= floor($avgRating))
                                        <i class="bi bi-star-fill"></i>
                                    @elseif($i - $avgRating < 1)
                                        <i class="bi bi-star-half"></i>
                                    @else
                                        <i class="bi bi-star"></i>
                                    @endif
                                @endfor
                            </div>
                            <div style="font-size:0.7rem; color:var(--text-secondary);">{{ number_format($totalRatings) }} reviews</div>
                        </div>
                        {{-- Star breakdown bars --}}
                        <div class="flex-grow-1">
                            @foreach([5,4,3,2,1] as $star)
                                @php $pct = $totalRatings > 0 ? round(($ratingCounts[$star] / $totalRatings) * 100) : 0; @endphp
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span style="font-size:0.7rem; width:10px;">{{ $star }}</span>
                                    <i class="bi bi-star-fill" style="font-size:0.65rem; color:#f59e0b;"></i>
                                    <div class="flex-grow-1 rounded" style="height:8px; background:rgba(0,0,0,0.08);">
                                        <div class="rounded" style="height:8px; width:{{ $pct }}%; background:{{ $star >= 4 ? '#10b981' : ($star == 3 ? '#f59e0b' : '#ef4444') }};"></div>
                                    </div>
                                    <span style="font-size:0.7rem; width:28px; text-align:right; color:var(--text-secondary);">{{ $ratingCounts[$star] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    {{-- Rating trend mini chart --}}
                    <div style="position:relative;width:100%;height:100px;">
                        <canvas id="ratingTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Reviews --}}
        <div class="col-lg-6">
            <div class="modern-card h-100">
                <div class="card-header-modern">
                    <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">
                        <i class="bi bi-chat-quote text-info me-2"></i>Recent Reviews
                    </h6>
                    <small style="font-size: 0.75rem; color: var(--text-secondary);">Latest customer feedback</small>
                </div>
                <div class="card-body-modern" style="max-height:320px; overflow-y:auto;">
                    @forelse($recentReviews as $review)
                    <div class="mb-3 pb-3" style="border-bottom: 1px solid var(--border-color);">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="fw-semibold" style="font-size:0.8rem;">{{ $review->customer->name ?? 'Anonymous' }}</span>
                            <span style="font-size:0.7rem; color:var(--text-secondary);">{{ $review->created_at->format('M d') }}</span>
                        </div>
                        <div style="color:#f59e0b; font-size:0.75rem; margin-bottom:4px;">
                            @for($i=1;$i<=5;$i++)
                                <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                            @endfor
                        </div>
                        @if($review->comment)
                        <p class="mb-0" style="font-size:0.78rem; color:var(--text-secondary); line-height:1.4;">{{ Str::limit($review->comment, 100) }}</p>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-4" style="color:var(--text-secondary); font-size:0.8rem;">No reviews yet</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 5 --}}
    <div class="row g-3 mb-3">
    <div class="row g-3 mb-3">
        {{-- Customer Growth --}}
        <div class="col-lg-6">
            <div class="modern-card h-100">
                <div class="card-header-modern">
                    <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">
                        <i class="bi bi-people-fill text-cyan me-2"></i>Customer Growth Trend
                    </h6>
                    <small style="font-size: 0.75rem; color: var(--text-secondary);">{{ $startDate->format('M d') }} – {{ $endDate->format('M d, Y') }}</small>
                </div>
                <div class="card-body-modern">
                    <div style="position:relative;width:100%;height:250px;">
                        <canvas id="customerGrowthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Customers --}}
        <div class="col-lg-6">
            <div class="modern-card h-100">
                <div class="card-header-modern">
                    <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">
                        <i class="bi bi-trophy text-warning me-2"></i>Top 5 Customers
                    </h6>
                    <small style="font-size: 0.75rem; color: var(--text-secondary);">Highest spending customers</small>
                </div>
                <div class="card-body-modern">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                            <thead>
                                <tr style="background: rgba(0,0,0,0.02);">
                                    <th style="padding: 8px;">Rank</th>
                                    <th style="padding: 8px;">Customer</th>
                                    <th class="text-center" style="padding: 8px;">Orders</th>
                                    <th class="text-end" style="padding: 8px;">Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCustomers ?? [] as $index => $customer)
                                <tr>
                                    <td style="padding: 8px;">
                                        <span class="badge" style="background: {{ $index == 0 ? '#fbbf24' : ($index == 1 ? '#94a3b8' : '#cd7f32') }}; font-size: 0.7rem;">
                                            #{{ $index + 1 }}
                                        </span>
                                    </td>
                                    <td style="padding: 8px; font-weight: 500;">{{ $customer->customer_name }}</td>
                                    <td class="text-center" style="padding: 8px;">
                                        <span class="badge bg-primary" style="font-size: 0.7rem;">{{ $customer->order_count }}</span>
                                    </td>
                                    <td class="text-end" style="padding: 8px;">
                                        <span class="fw-bold text-success">₱{{ number_format($customer->total_spent, 2) }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3" style="color: var(--text-secondary);">No customer data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/chart.js/chart.umd.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Branch Analytics: Initializing charts...');
    
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
        }
    };

    // Revenue / Expenses / Profit Trend Chart (combined)
    try {
        const revenueTrendCtx = document.getElementById('revenueTrendChart');
        if (revenueTrendCtx) {
            new Chart(revenueTrendCtx, {
                type: 'line',
                data: {
                    labels: @json($chartData['revenue_trend']['labels'] ?? []),
                    datasets: [
                        {
                            label: 'Revenue',
                            data: @json($chartData['revenue_trend']['data'] ?? []),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16,185,129,0.08)',
                            tension: 0.4, fill: true, borderWidth: 2,
                            pointRadius: 4, pointBackgroundColor: '#10b981'
                        },
                        {
                            label: 'Expenses',
                            data: @json($chartData['expenses_trend']['data'] ?? []),
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239,68,68,0.08)',
                            tension: 0.4, fill: true, borderWidth: 2,
                            pointRadius: 4, pointBackgroundColor: '#ef4444'
                        },
                        {
                            label: 'Profit',
                            data: @json($chartData['profit_trend']['data'] ?? []),
                            borderColor: '#8b5cf6',
                            backgroundColor: 'rgba(139,92,246,0.08)',
                            tension: 0.4, fill: true, borderWidth: 2,
                            pointRadius: 4, pointBackgroundColor: '#8b5cf6',
                            segment: { borderColor: ctx => ctx.p1.parsed.y < 0 ? '#ef4444' : '#8b5cf6' }
                        }
                    ]
                },
                options: {
                    ...BASE_CONFIG,
                    plugins: {
                        ...BASE_CONFIG.plugins,
                        legend: {
                            display: true,
                            position: 'top',
                            labels: { color: TICK_COLOR, font: { size: 10 }, usePointStyle: true, padding: 16 }
                        }
                    },
                    scales: {
                        y: {
                            grid: { color: GRID_COLOR },
                            ticks: { color: TICK_COLOR, font: { size: 10 }, callback: v => '\u20b1' + v.toLocaleString() }
                        },
                        x: { grid: { display: false }, ticks: { color: TICK_COLOR, font: { size: 10 } } }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error creating combined trend chart:', error);
    }

    // Rating Trend Chart
    try {
        const ratingTrendCtx = document.getElementById('ratingTrendChart');
        if (ratingTrendCtx) {
            new Chart(ratingTrendCtx, {
                type: 'line',
                data: {
                    labels: @json($chartData['rating_trend']['labels'] ?? []),
                    datasets: [{
                        label: 'Avg Rating',
                        data: @json($chartData['rating_trend']['data'] ?? []),
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245,158,11,0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#f59e0b'
                    }]
                },
                options: {
                    ...BASE_CONFIG,
                    scales: {
                        y: {
                            min: 0, max: 5,
                            grid: { color: GRID_COLOR },
                            ticks: { color: TICK_COLOR, font: { size: 9 }, stepSize: 1 }
                        },
                        x: { grid: { display: false }, ticks: { color: TICK_COLOR, font: { size: 9 } } }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error creating Rating Trend Chart:', error);
    }

    // Revenue Breakdown Chart
    try {
        const revenueBreakdownCtx = document.getElementById('revenueBreakdownChart');
        if (revenueBreakdownCtx) {
            new Chart(revenueBreakdownCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Laundry Services', 'Retail Sales'],
                    datasets: [{
                        data: [{{ $stats['laundry_revenue'] ?? 0 }}, {{ $stats['retail_sales'] ?? 0 }}],
                        backgroundColor: ['#3b82f6', '#f59e0b'],
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
                        }
                    }
                }
            });
            console.log('Revenue Breakdown Chart created');
        }
    } catch (error) {
        console.error('Error creating Revenue Breakdown Chart:', error);
    }

    // Top Services Line Chart
    try {
        const topServicesCtx = document.getElementById('topServicesChart');
        if (topServicesCtx) {
            new Chart(topServicesCtx, {
                type: 'line',
                data: {
                    labels: @json($chartData['top_services']['labels'] ?? []),
                    datasets: @json($chartData['top_services']['datasets'] ?? [])
                },
                options: {
                    ...BASE_CONFIG,
                    plugins: {
                        ...BASE_CONFIG.plugins,
                        legend: {
                            display: true,
                            position: 'top',
                            labels: { color: TICK_COLOR, font: { size: 10 }, usePointStyle: true, padding: 12 }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: GRID_COLOR },
                            ticks: { color: TICK_COLOR, font: { size: 10 }, stepSize: 1 }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: TICK_COLOR, font: { size: 10 } }
                        }
                    }
                }
            });
            console.log('Top Services Chart created');
        }
    } catch (error) {
        console.error('Error creating Top Services Chart:', error);
    }

    // Order Status Chart
    try {
        const orderStatusCtx = document.getElementById('orderStatusChart');
        if (orderStatusCtx) {
            new Chart(orderStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($chartData['order_status']['labels'] ?? []),
                    datasets: [{
                        data: @json($chartData['order_status']['data'] ?? []),
                        backgroundColor: ['#3b82f6', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6'],
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
                        }
                    }
                }
            });
            console.log('Order Status Chart created');
        }
    } catch (error) {
        console.error('Error creating Order Status Chart:', error);
    }

    // Peak Hours Chart
    try {
        const peakHoursCtx = document.getElementById('peakHoursChart');
        if (peakHoursCtx) {
            new Chart(peakHoursCtx, {
                type: 'bar',
                data: {
                    labels: @json($chartData['peak_hours']['labels'] ?? []),
                    datasets: [{
                        label: 'Orders',
                        data: @json($chartData['peak_hours']['data'] ?? []),
                        backgroundColor: '#8b5cf6',
                        borderRadius: 6,
                        borderWidth: 0,
                        maxBarThickness: 48
                    }]
                },
                options: {
                    ...BASE_CONFIG,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: GRID_COLOR },
                            ticks: { color: TICK_COLOR, font: { size: 10 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: TICK_COLOR, font: { size: 10 } }
                        }
                    }
                }
            });
            console.log('Peak Hours Chart created');
        }
    } catch (error) {
        console.error('Error creating Peak Hours Chart:', error);
    }

    // Payment Methods Chart
    try {
        const paymentMethodsCtx = document.getElementById('paymentMethodsChart');
        if (paymentMethodsCtx) {
            new Chart(paymentMethodsCtx, {
                type: 'pie',
                data: {
                    labels: @json($chartData['payment_methods']['labels'] ?? []),
                    datasets: [{
                        data: @json($chartData['payment_methods']['data'] ?? []),
                        backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6'],
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
                        }
                    }
                }
            });
            console.log('Payment Methods Chart created');
        }
    } catch (error) {
        console.error('Error creating Payment Methods Chart:', error);
    }

    // Customer Growth Chart
    try {
        const customerGrowthCtx = document.getElementById('customerGrowthChart');
        if (customerGrowthCtx) {
            new Chart(customerGrowthCtx, {
                type: 'line',
                data: {
                    labels: @json($chartData['customer_growth']['labels'] ?? []),
                    datasets: [{
                        label: 'Customers',
                        data: @json($chartData['customer_growth']['data'] ?? []),
                        borderColor: '#06b6d4',
                        backgroundColor: 'rgba(6, 182, 212, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#06b6d4'
                    }]
                },
                options: {
                    ...BASE_CONFIG,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: GRID_COLOR },
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
        }
    } catch (error) {
        console.error('Error creating Customer Growth Chart:', error);
    }
});
</script>
@endpush
