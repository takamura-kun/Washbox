@extends('admin.layouts.app')

@section('title', 'Performance Report')
@section('page-title', 'PERFORMANCE REPORT')

@section('content')
<div class="analytics-root">

    {{-- Page Header --}}
    <div class="an-page-header">
        <div class="an-header-content">
            <div class="an-header-left">
                <a href="{{ route('admin.finance.dashboard') }}" class="an-back-link">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div class="an-title-group">
                    <h1 class="an-page-title">Performance Report</h1>
                    <span class="an-date-range">
                        <i class="bi bi-calendar3"></i>
                        {{ \Carbon\Carbon::parse($startDate)->format('M d') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        <span class="an-date-badge">{{ \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1 }}d</span>
                    </span>
                </div>
            </div>
            <div class="an-header-right">
                <select id="branchFilter" class="an-filter-select">
                    <option value="all">All Branches</option>
                    @if(isset($branchPerformance['branches']) && is_array($branchPerformance['branches']))
                        @foreach($branchPerformance['branches'] as $branch)
                            <option value="{{ $branch['id'] ?? '' }}"
                                {{ (request('branch_id') == ($branch['id'] ?? '')) ? 'selected' : '' }}>
                                {{ $branch['name'] ?? 'Unknown Branch' }}
                            </option>
                        @endforeach
                    @endif
                </select>
                <button class="an-filter-btn" data-bs-toggle="modal" data-bs-target="#dateRangeModal">
                    <i class="bi bi-calendar-range"></i>
                </button>
                <div class="an-live-indicator" id="liveIndicator">
                    <span class="an-live-dot" id="liveDot"></span>
                </div>
                <button class="an-export-btn" onclick="window.print()" title="Export Report">
                    <i class="bi bi-download"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         EXECUTIVE SUMMARY - OVERALL SYSTEM PERFORMANCE
    ══════════════════════════════════════════════════════════════════════ --}}
    <div class="an-executive-summary mb-4">
        <div class="an-exec-header">
            <h5 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Executive Performance Summary</h5>
            <small class="text-muted">Complete system health overview</small>
        </div>

        <div class="row g-3 mt-2">
            {{-- Revenue Growth --}}
            <div class="col-lg-3 col-md-6">
                <div class="an-exec-card">
                    <div class="an-exec-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div class="an-exec-content">
                        <div class="an-exec-label">Revenue Growth</div>
                        <div class="an-exec-value">
                            @if(!isset($revenueAnalytics['has_previous_data']) || !$revenueAnalytics['has_previous_data'])
                                <span style="font-size:1rem;color:#94a3b8;">No comparison</span>
                            @else
                                {{ $revenueAnalytics['growth_percentage'] ?? 0 }}%
                            @endif
                        </div>
                        <div class="an-exec-sub">₱{{ number_format($revenueAnalytics['total'] ?? 0, 0) }} total</div>
                    </div>
                    <div class="an-exec-trend {{ ($revenueAnalytics['growth_percentage'] ?? 0) >= 0 ? 'up' : 'down' }}">
                        @if(!isset($revenueAnalytics['has_previous_data']) || !$revenueAnalytics['has_previous_data'])
                            <i class="bi bi-dash"></i>
                        @else
                            <i class="bi bi-arrow-{{ ($revenueAnalytics['growth_percentage'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Customer Growth --}}
            <div class="col-lg-3 col-md-6">
                <div class="an-exec-card">
                    <div class="an-exec-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="an-exec-content">
                        <div class="an-exec-label">Customer Growth</div>
                        <div class="an-exec-value">+{{ $customerAnalytics['new'] ?? 0 }}</div>
                        <div class="an-exec-sub">{{ number_format($customerAnalytics['total'] ?? 0) }} total customers</div>
                    </div>
                    <div class="an-exec-trend up">
                        <i class="bi bi-arrow-up"></i>
                    </div>
                </div>
            </div>

            {{-- Customer Satisfaction --}}
            <div class="col-lg-3 col-md-6">
                <div class="an-exec-card">
                    <div class="an-exec-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <div class="an-exec-content">
                        <div class="an-exec-label">Customer Satisfaction</div>
                        <div class="an-exec-value">{{ $customerSatisfaction['avg_rating'] ?? 0 }}/5</div>
                        <div class="an-exec-sub">{{ $customerSatisfaction['satisfaction_rate'] ?? 0 }}% satisfied</div>
                    </div>
                    <div class="an-exec-trend {{ ($customerSatisfaction['rating_growth'] ?? 0) >= 0 ? 'up' : 'down' }}">
                        <i class="bi bi-arrow-{{ ($customerSatisfaction['rating_growth'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                    </div>
                </div>
            </div>

            {{-- Profit Margin --}}
            <div class="col-lg-3 col-md-6">
                <div class="an-exec-card">
                    <div class="an-exec-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div class="an-exec-content">
                        <div class="an-exec-label">Profit Margin</div>
                        <div class="an-exec-value">{{ $profitAnalytics['profit_margin'] ?? 0 }}%</div>
                        <div class="an-exec-sub">₱{{ number_format($profitAnalytics['total_profit'] ?? 0, 0) }} profit</div>
                    </div>
                    <div class="an-exec-trend {{ ($profitAnalytics['profit_margin'] ?? 0) >= 20 ? 'up' : 'neutral' }}">
                        <i class="bi bi-dash"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Secondary Metrics Row --}}
        <div class="row g-3 mt-2">
            {{-- Pickup Performance --}}
            <div class="col-lg-2 col-md-4 col-6">
                <div class="an-metric-card">
                    <div class="an-metric-icon" style="color: #3b82f6;">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div class="an-metric-label">Pickup Rate</div>
                    <div class="an-metric-value">{{ $pickupAnalytics['completion_rate'] ?? 0 }}%</div>
                    <div class="an-metric-sub">{{ $pickupAnalytics['completed'] ?? 0 }}/{{ $pickupAnalytics['total'] ?? 0 }} completed</div>
                </div>
            </div>

            {{-- Unclaimed Items --}}
            <div class="col-lg-2 col-md-4 col-6">
                <div class="an-metric-card">
                    <div class="an-metric-icon" style="color: #ef4444;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="an-metric-label">Unclaimed</div>
                    <div class="an-metric-value">{{ $unclaimedAnalytics['total'] ?? 0 }}</div>
                    <div class="an-metric-sub">{{ $unclaimedAnalytics['critical'] ?? 0 }} critical</div>
                </div>
            </div>

            {{-- Stock Health --}}
            <div class="col-lg-2 col-md-4 col-6">
                <div class="an-metric-card">
                    <div class="an-metric-icon" style="color: #10b981;">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                    <div class="an-metric-label">Stock Health</div>
                    <div class="an-metric-value">{{ $stockAnalytics['stock_health'] ?? 0 }}%</div>
                    <div class="an-metric-sub">{{ $stockAnalytics['low_stock'] ?? 0 }} low stock</div>
                </div>
            </div>

            {{-- Staff Growth --}}
            <div class="col-lg-2 col-md-4 col-6">
                <div class="an-metric-card">
                    <div class="an-metric-icon" style="color: #8b5cf6;">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div class="an-metric-label">Staff Growth</div>
                    <div class="an-metric-value">{{ $staffGrowth['staff_growth'] ?? 0 }}%</div>
                    <div class="an-metric-sub">{{ $staffGrowth['total_staff'] ?? 0 }} total staff</div>
                </div>
            </div>

            {{-- Attendance Rate --}}
            <div class="col-lg-2 col-md-4 col-6">
                <div class="an-metric-card">
                    <div class="an-metric-icon" style="color: #06b6d4;">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="an-metric-label">Attendance</div>
                    <div class="an-metric-value">{{ $staffGrowth['attendance_rate'] ?? 0 }}%</div>
                    <div class="an-metric-sub">Staff presence</div>
                </div>
            </div>

            {{-- Recovery Rate --}}
            <div class="col-lg-2 col-md-4 col-6">
                <div class="an-metric-card">
                    <div class="an-metric-icon" style="color: #f59e0b;">
                        <i class="bi bi-arrow-clockwise"></i>
                    </div>
                    <div class="an-metric-label">Recovery Rate</div>
                    <div class="an-metric-value">{{ $unclaimedAnalytics['recovery_rate'] ?? 0 }}%</div>
                    <div class="an-metric-sub">{{ $unclaimedAnalytics['recovered'] ?? 0 }} recovered</div>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Row --}}
    <div class="row g-2 mb-3">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="an-kpi-card an-kpi-revenue">
                <div class="an-kpi-glow"></div>
                <div class="an-kpi-icon-sm"><i class="bi bi-cash-stack"></i></div>
                <div class="an-kpi-label">Total Revenue</div>
                <div id="kpi-revenue" class="an-kpi-value-sm">₱{{ number_format($revenueAnalytics['total'] ?? 0, 0) }}</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="an-kpi-card an-kpi-customers">
                <div class="an-kpi-glow"></div>
                <div class="an-kpi-icon-sm"><i class="bi bi-people-fill"></i></div>
                <div class="an-kpi-label">Total Customers</div>
                <div id="kpi-customers" class="an-kpi-value-sm">{{ number_format($customerAnalytics['total'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="an-kpi-card an-kpi-retail">
                <div class="an-kpi-glow"></div>
                <div class="an-kpi-icon-sm"><i class="bi bi-cart-check-fill"></i></div>
                <div class="an-kpi-label">Retail Sales</div>
                <div id="kpi-retail" class="an-kpi-value-sm">₱{{ number_format($retailAnalytics['total'] ?? 0, 0) }}</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="an-kpi-card an-kpi-profit">
                <div class="an-kpi-glow"></div>
                <div class="an-kpi-icon-sm"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="an-kpi-label">Total Profit</div>
                <div id="kpi-profit" class="an-kpi-value-sm">₱{{ number_format($profitAnalytics['total_profit'] ?? 0, 0) }}</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="an-kpi-card an-kpi-expenses">
                <div class="an-kpi-glow"></div>
                <div class="an-kpi-icon-sm"><i class="bi bi-wallet2"></i></div>
                <div class="an-kpi-label">Total Expenses</div>
                <div id="kpi-expenses" class="an-kpi-value-sm">₱{{ number_format($profitAnalytics['total_expenses'] ?? 0, 0) }}</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="an-kpi-card an-kpi-laundry">
                <div class="an-kpi-glow"></div>
                <div class="an-kpi-icon-sm"><i class="bi bi-basket3-fill"></i></div>
                <div class="an-kpi-label">Total Laundries</div>
                <div id="kpi-laundries" class="an-kpi-value-sm">{{ number_format($laundryAnalytics['total'] ?? 0) }}</div>
            </div>
        </div>
    </div>

    <div class="an-all-charts-container">

        {{-- ══════════════════════════════════════════════
             SECTION 1: BRANCH ANALYTICS
        ══════════════════════════════════════════════ --}}
        <div class="an-section-header">
            <h5 class="mb-0"><i class="bi bi-building me-2"></i>Branch Analytics</h5>
        </div>
        <div class="row g-3 mb-4">

            {{-- Revenue Growth, Profit Growth & Expenses --}}
            <div class="col-lg-7">
                <div class="an-chart-card">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Revenue, Profit & Expenses</h6>
                            <small class="text-muted">Financial performance trends by branch</small>
                        </div>
                        <div class="an-chart-badge green">Line</div>
                    </div>
                    <div class="an-chart-body">
                        <canvas id="realProfitExpensesChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            {{-- Retail Sales by Branch --}}
            <div class="col-lg-5">
                <div class="an-chart-card h-100">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Retail Sales by Branch</h6>
                            <small class="text-muted">Distribution of retail revenue</small>
                        </div>
                        <div class="an-chart-badge teal">Bar</div>
                    </div>
                    <div class="an-chart-body">
                        <canvas id="retailByBranchChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            {{-- Expense Breakdown by Category --}}
            <div class="col-lg-5">
                <div class="an-chart-card h-100">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Expense Breakdown by Category</h6>
                            <small class="text-muted">Where the money is being spent</small>
                        </div>
                        <div class="an-chart-badge purple">Doughnut</div>
                    </div>
                    <div class="an-chart-body d-flex align-items-center justify-content-center" style="min-height:240px;">
                        <canvas id="expenseCategoryChart" style="max-height:240px;"></canvas>
                    </div>
                </div>
            </div>

            {{-- Monthly Comparison --}}
            <div class="col-lg-7">
                <div class="an-chart-card">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Monthly Revenue Comparison</h6>
                            <small class="text-muted">Current year vs previous year</small>
                        </div>
                        <div class="an-chart-badge blue">Grouped Bar</div>
                    </div>
                    <div class="an-chart-body">
                        <canvas id="monthlyComparisonChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            {{-- Branch Performance Bar + Breakdown cards --}}
            <div class="col-lg-6">
                <div class="an-chart-card">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Branch Performance</h6>
                            <small class="text-muted">Revenue by branch</small>
                        </div>
                        <div class="an-chart-badge teal">Bar</div>
                    </div>
                    <div class="an-chart-body">
                        <canvas id="branchBarChart" height="180"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="an-chart-card h-100">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Branch Breakdown</h6>
                            <small class="text-muted">Individual stats</small>
                        </div>
                    </div>
                    <div class="an-chart-body">
                        @php $maxRev = collect($branchPerformance['branches'])->max('revenue') ?: 1; @endphp
                        @foreach($branchPerformance['branches'] as $i => $branch)
                            @php
                                $pct = round(($branch['revenue'] / $maxRev) * 100);
                                $bColors = ['#3b82f6','#8b5cf6','#06b6d4','#f59e0b','#ef4444','#10b981'];
                                $bc = $bColors[$i % count($bColors)];
                            @endphp
                            <div class="an-branch-row mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="an-branch-dot" style="background:{{ $bc }};"></div>
                                        <span class="fw-700 text-slate-800" style="font-size:.85rem;">{{ $branch['name'] }}</span>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-800" style="color:{{ $bc }};">₱{{ number_format($branch['revenue'], 0) }}</span>
                                        <span class="text-muted ms-2" style="font-size:.75rem;">{{ $branch['laundries'] }} laundries</span>
                                    </div>
                                </div>
                                <div class="an-branch-bar-track">
                                    <div class="an-branch-bar-fill" style="width:{{ $pct }}%;background:{{ $bc }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════════════════
             SECTION 2: SERVICE ANALYTICS
        ══════════════════════════════════════════════ --}}
        <div class="an-section-header">
            <h5 class="mb-0"><i class="bi bi-layers me-2"></i>Service Analytics</h5>
        </div>
        <div class="row g-3 mb-4">

            {{-- Top Services by Revenue --}}
            <div class="col-lg-7">
                <div class="an-chart-card">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Top Services by Revenue</h6>
                            <small class="text-muted">Ranked by total revenue generated</small>
                        </div>
                        <div class="an-chart-badge blue">Horizontal Bar</div>
                    </div>
                    <div class="an-chart-body">
                        <canvas id="topServiceRevenueChart" height="220"></canvas>
                    </div>
                </div>
            </div>

            {{-- Branch Performance Grouped --}}
            <div class="col-lg-5">
                <div class="an-chart-card h-100">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Revenue vs Volume</h6>
                            <small class="text-muted">Revenue and laundry count per branch</small>
                        </div>
                        <div class="an-chart-badge blue">Grouped Bar</div>
                    </div>
                    <div class="an-chart-body">
                        <canvas id="branchGroupedChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            {{-- Inventory Turnover Rate --}}
            <div class="col-lg-5">
                <div class="an-chart-card h-100">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Inventory Turnover Rate</h6>
                            <small class="text-muted">Units consumed vs restocked per item</small>
                        </div>
                        <div class="an-chart-badge orange">Bar</div>
                    </div>
                    <div class="an-chart-body">
                        <canvas id="inventoryTurnoverChart" height="220"></canvas>
                    </div>
                </div>
            </div>

            {{-- Service Detail Table --}}
            <div class="col-lg-7">
                <div class="an-chart-card">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Service Detail</h6>
                            <small class="text-muted">All services ranked by usage</small>
                        </div>
                    </div>
                    <div class="an-chart-body p-0">
                        <div class="table-responsive">
                            <table class="an-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Service</th>
                                        <th>Usage</th>
                                        <th>Volume Bar</th>
                                        <th class="text-end">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $maxSvc = collect($servicePopularity['services'])->max('laundries') ?: 1; @endphp
                                    @foreach($servicePopularity['services'] as $i => $svc)
                                    @php $sp = round(($svc['laundries'] / $maxSvc) * 100); @endphp
                                    <tr>
                                        <td><span class="an-table-rank">{{ $i + 1 }}</span></td>
                                        <td><span class="fw-700">{{ $svc['name'] }}</span></td>
                                        <td><span class="an-usage-badge">{{ $svc['laundries'] }}</span></td>
                                        <td style="width:38%;">
                                            <div class="an-table-bar-track">
                                                <div class="an-table-bar-fill" style="width:{{ $sp }}%;"></div>
                                            </div>
                                        </td>
                                        <td class="text-end fw-700" style="color:#2563eb;">₱{{ number_format($svc['revenue'], 0) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════════════════
             SECTION 3: CUSTOMER ANALYTICS
        ══════════════════════════════════════════════ --}}
        <div class="an-section-header">
            <h5 class="mb-0"><i class="bi bi-people me-2"></i>Customer Analytics</h5>
        </div>
        <div class="row g-3 mb-4">

            {{-- Customer Acquisition Trend (New vs Returning) --}}
            <div class="col-lg-8">
                <div class="an-chart-card">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Customer Acquisition Trend</h6>
                            <small class="text-muted">New vs returning customers over time</small>
                        </div>
                        <div class="an-chart-badge green">Area</div>
                    </div>
                    <div class="an-chart-body">
                        <canvas id="customerAcquisitionChart" height="180"></canvas>
                    </div>
                </div>
            </div>

            {{-- Customer Type Doughnut --}}
            <div class="col-lg-4">
                <div class="an-chart-card h-100">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Customer Type</h6>
                            <small class="text-muted">Walk-in vs self-registered</small>
                        </div>
                        <div class="an-chart-badge teal">Doughnut</div>
                    </div>
                    <div class="an-chart-body d-flex align-items-center justify-content-center" style="min-height:220px;">
                        <canvas id="customerTypeChart" style="max-height:220px;"></canvas>
                    </div>
                </div>
            </div>

            {{-- Top Customers Bar --}}
            <div class="col-lg-7">
                <div class="an-chart-card">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Top Customers by Spend</h6>
                            <small class="text-muted">Highest lifetime value</small>
                        </div>
                        <div class="an-chart-badge blue">Bar</div>
                    </div>
                    <div class="an-chart-body">
                        <canvas id="topCustomersChart" height="220"></canvas>
                    </div>
                </div>
            </div>

            {{-- Leaderboard --}}
            <div class="col-lg-5">
                <div class="an-chart-card h-100">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Leaderboard</h6>
                            <small class="text-muted">Ranked by total spend</small>
                        </div>
                    </div>
                    <div class="an-chart-body p-0">
                        <div class="an-leaderboard">
                            @foreach($customerAnalytics['top_customers']->take(8) as $idx => $cust)
                            <div class="an-lb-row">
                                <div class="an-lb-rank {{ $idx < 3 ? 'top' : '' }}">{{ $idx + 1 }}</div>
                                <div class="an-lb-avatar">{{ strtoupper(substr($cust->name, 0, 1)) }}</div>
                                <div class="an-lb-info">
                                    <div class="an-lb-name">{{ $cust->name }}</div>
                                    <div class="an-lb-sub">{{ $cust->laundries_count ?? 0 }} laundries</div>
                                </div>
                                <div class="an-lb-value">₱{{ number_format($cust->laundries_sum_total_amount ?? 0, 0) }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════════════════
             SECTION 4: PROMOTION ANALYTICS
        ══════════════════════════════════════════════ --}}
        <div class="an-section-header">
            <h5 class="mb-0"><i class="bi bi-megaphone me-2"></i>Promotion Analytics</h5>
        </div>
        <div class="row g-3 mb-4">

            {{-- Promotion ROI Comparison --}}
            <div class="col-lg-8">
                <div class="an-chart-card">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Promotion ROI Comparison</h6>
                            <small class="text-muted">Revenue generated vs discount given per promotion</small>
                        </div>
                        <div class="an-chart-badge purple">Grouped Bar</div>
                    </div>
                    <div class="an-chart-body">
                        <canvas id="promoRoiChart" height="220"></canvas>
                    </div>
                </div>
            </div>

            {{-- Promotion Usage --}}
            <div class="col-lg-4">
                <div class="an-chart-card h-100">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Promotion Usage</h6>
                            <small class="text-muted">Times each promotion was applied</small>
                        </div>
                        <div class="an-chart-badge orange">Column</div>
                    </div>
                    <div class="an-chart-body">
                        <canvas id="promoUsageChart" height="220"></canvas>
                    </div>
                </div>
            </div>

            {{-- Promotions Detail Table --}}
            <div class="col-12">
                <div class="an-chart-card">
                    <div class="an-chart-header d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Promotion Detail</h6>
                            <small class="text-muted">All promotions with ROI analysis</small>
                        </div>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-sm btn-primary rounded-pill">
                            <i class="bi bi-plus-circle me-1"></i>New Promotion
                        </a>
                    </div>
                    <div class="an-chart-body p-0">
                        <div class="table-responsive">
                            <table class="an-table">
                                <thead>
                                    <tr>
                                        <th>Promotion</th>
                                        <th class="text-center">Type</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end">Usage</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Discount</th>
                                        <th class="text-end">ROI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($promotionEffectiveness['promotions'] as $promo)
                                    @php
                                        $roi = ($promo['total_discount'] > 0)
                                            ? $promo['revenue'] / $promo['total_discount']
                                            : ($promo['revenue'] > 0 ? 100 : 0);
                                        $roiClass = $roi > 3 ? 'success' : ($roi > 1.5 ? 'warning' : 'danger');
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-700">{{ $promo['name'] }}</div>
                                            <small class="text-muted">{{ ucfirst(str_replace('_',' ',$promo['type'])) }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="an-type-badge">{{ ucfirst(str_replace('_',' ',$promo['type'])) }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($promo['is_active'])
                                                <span class="an-status-active">Active</span>
                                            @else
                                                <span class="an-status-inactive">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-700">{{ number_format($promo['usage_count']) }}</td>
                                        <td class="text-end fw-700 text-primary">₱{{ number_format($promo['revenue'], 0) }}</td>
                                        <td class="text-end text-danger">-₱{{ number_format($promo['total_discount'], 0) }}</td>
                                        <td class="text-end">
                                            <span class="an-roi-badge {{ $roiClass }}">{{ number_format($roi, 1) }}x</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="bi bi-megaphone text-muted d-block mb-2" style="font-size:2rem;opacity:.3;"></i>
                                            <p class="text-muted mb-2">No promotions in this period</p>
                                            <a href="{{ route('admin.promotions.create') }}" class="btn btn-sm btn-primary">Create First Promotion</a>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════════════════
             SECTION 5: STAFF PERFORMANCE ANALYTICS
        ══════════════════════════════════════════════ --}}
        <div class="an-section-header">
            <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Staff Performance Analytics</h5>
        </div>
        <div class="row g-3 mb-4">

            {{-- Staff Revenue Performance --}}
            <div class="col-lg-8">
                <div class="an-chart-card">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Staff Revenue Performance</h6>
                            <small class="text-muted">Revenue generated by each staff member</small>
                        </div>
                        <div class="an-chart-badge blue">Bar</div>
                    </div>
                    <div class="an-chart-body">
                        <canvas id="staffRevenueChart" height="220"></canvas>
                    </div>
                </div>
            </div>

            {{-- Staff Productivity Score --}}
            <div class="col-lg-4">
                <div class="an-chart-card h-100">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Productivity Score</h6>
                            <small class="text-muted">Laundries per attendance day</small>
                        </div>
                        <div class="an-chart-badge orange">Bar</div>
                    </div>
                    <div class="an-chart-body">
                        <canvas id="staffProductivityChart" height="220"></canvas>
                    </div>
                </div>
            </div>

            {{-- Staff Performance Table --}}
            <div class="col-12">
                <div class="an-chart-card">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Staff Performance Detail</h6>
                            <small class="text-muted">Comprehensive staff metrics</small>
                        </div>
                    </div>
                    <div class="an-chart-body p-0">
                        <div class="table-responsive">
                            <table class="an-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Staff Name</th>
                                        <th class="text-center">Laundries</th>
                                        <th class="text-center">Completed</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-center">Attendance</th>
                                        <th class="text-center">Avg Time</th>
                                        <th class="text-center">Productivity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($staffPerformance['staff'] as $idx => $staff)
                                    <tr>
                                        <td><span class="an-table-rank">{{ $idx + 1 }}</span></td>
                                        <td><span class="fw-700">{{ $staff['name'] }}</span></td>
                                        <td class="text-center"><span class="an-usage-badge">{{ $staff['laundries_processed'] }}</span></td>
                                        <td class="text-center">{{ $staff['completed'] }}</td>
                                        <td class="text-end fw-700 text-primary">₱{{ number_format($staff['revenue_generated'], 0) }}</td>
                                        <td class="text-center">{{ $staff['attendance_days'] }} days</td>
                                        <td class="text-center">{{ $staff['avg_processing_hours'] }}h</td>
                                        <td class="text-center">
                                            <span class="an-roi-badge {{ $staff['productivity_score'] >= 3 ? 'success' : ($staff['productivity_score'] >= 2 ? 'warning' : 'danger') }}">
                                                {{ $staff['productivity_score'] }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="bi bi-people text-muted d-block mb-2" style="font-size:2rem;opacity:.3;"></i>
                                            <p class="text-muted mb-0">No staff performance data available</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════════════════
             SECTION 6: PEAK HOURS & DEMAND ANALYTICS
        ══════════════════════════════════════════════ --}}
        <div class="an-section-header">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Peak Hours & Demand Analytics</h5>
        </div>
        <div class="row g-3 mb-4">

            {{-- Hourly Order Distribution --}}
            <div class="col-lg-8">
                <div class="an-chart-card">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Hourly Order Distribution</h6>
                            <small class="text-muted">Orders by hour of day</small>
                        </div>
                        <div class="an-chart-badge purple">Line</div>
                    </div>
                    <div class="an-chart-body">
                        <canvas id="hourlyOrdersChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            {{-- Peak Insights Card --}}
            <div class="col-lg-4">
                <div class="an-chart-card h-100">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Peak Insights</h6>
                            <small class="text-muted">Busiest times</small>
                        </div>
                    </div>
                    <div class="an-chart-body">
                        <div class="d-flex flex-column gap-3">
                            <div class="p-3 rounded" style="background:linear-gradient(135deg,#8b5cf6,#a78bfa);">
                                <div class="text-white opacity-75 small mb-1">PEAK HOUR</div>
                                <div class="text-white fw-800" style="font-size:1.8rem;">{{ $peakHoursAnalytics['peak_hour'] }}</div>
                            </div>
                            <div class="p-3 rounded" style="background:linear-gradient(135deg,#3b82f6,#60a5fa);">
                                <div class="text-white opacity-75 small mb-1">BUSIEST DAY</div>
                                <div class="text-white fw-800" style="font-size:1.8rem;">{{ $peakHoursAnalytics['peak_day'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Day of Week Distribution --}}
            <div class="col-lg-6">
                <div class="an-chart-card">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Orders by Day of Week</h6>
                            <small class="text-muted">Weekly demand pattern</small>
                        </div>
                        <div class="an-chart-badge teal">Bar</div>
                    </div>
                    <div class="an-chart-body">
                        <canvas id="dayOfWeekChart" height="220"></canvas>
                    </div>
                </div>
            </div>

            {{-- Hourly Revenue --}}
            <div class="col-lg-6">
                <div class="an-chart-card">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Hourly Revenue Distribution</h6>
                            <small class="text-muted">Revenue by hour</small>
                        </div>
                        <div class="an-chart-badge green">Area</div>
                    </div>
                    <div class="an-chart-body">
                        <canvas id="hourlyRevenueChart" height="220"></canvas>
                    </div>
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════════════════
             SECTION 7: CUSTOMER LIFETIME VALUE
        ══════════════════════════════════════════════ --}}
        <div class="an-section-header">
            <h5 class="mb-0"><i class="bi bi-gem me-2"></i>Customer Lifetime Value Analytics</h5>
        </div>
        <div class="row g-3 mb-4">

            {{-- CLV Metrics Cards --}}
            <div class="col-lg-3 col-md-6">
                <div class="an-chart-card">
                    <div class="an-chart-header border-0 pb-0">
                        <div class="w-100">
                            <div class="text-muted small mb-1">Average CLV</div>
                            <div class="fw-800" style="font-size:1.6rem;color:#3b82f6;">₱{{ number_format($customerLifetimeValue['avg_clv'], 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="an-chart-card">
                    <div class="an-chart-header border-0 pb-0">
                        <div class="w-100">
                            <div class="text-muted small mb-1">Retention Rate</div>
                            <div class="fw-800" style="font-size:1.6rem;color:#10b981;">{{ $customerLifetimeValue['retention_rate'] }}%</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="an-chart-card">
                    <div class="an-chart-header border-0 pb-0">
                        <div class="w-100">
                            <div class="text-muted small mb-1">Active Customers</div>
                            <div class="fw-800" style="font-size:1.6rem;color:#8b5cf6;">{{ number_format($customerLifetimeValue['active_count']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="an-chart-card">
                    <div class="an-chart-header border-0 pb-0">
                        <div class="w-100">
                            <div class="text-muted small mb-1">Churned Customers</div>
                            <div class="fw-800" style="font-size:1.6rem;color:#ef4444;">{{ number_format($customerLifetimeValue['churned_count']) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Customer Segments --}}
            <div class="col-lg-5">
                <div class="an-chart-card h-100">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Customer Segments</h6>
                            <small class="text-muted">Distribution by value</small>
                        </div>
                        <div class="an-chart-badge purple">Doughnut</div>
                    </div>
                    <div class="an-chart-body d-flex align-items-center justify-content-center" style="min-height:240px;">
                        <canvas id="customerSegmentsChart" style="max-height:240px;"></canvas>
                    </div>
                </div>
            </div>

            {{-- Top CLV Customers --}}
            <div class="col-lg-7">
                <div class="an-chart-card h-100">
                    <div class="an-chart-header">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800">Top Customers by Lifetime Value</h6>
                            <small class="text-muted">Highest value customers</small>
                        </div>
                    </div>
                    <div class="an-chart-body p-0">
                        <div class="table-responsive">
                            <table class="an-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Customer</th>
                                        <th class="text-center">Orders</th>
                                        <th class="text-end">Total Spent</th>
                                        <th class="text-end">Avg Order</th>
                                        <th class="text-center">Segment</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customerLifetimeValue['top_customers'] as $idx => $cust)
                                    <tr>
                                        <td><span class="an-table-rank {{ $idx < 3 ? 'top' : '' }}">{{ $idx + 1 }}</span></td>
                                        <td><span class="fw-700">{{ $cust['name'] }}</span></td>
                                        <td class="text-center"><span class="an-usage-badge">{{ $cust['order_count'] }}</span></td>
                                        <td class="text-end fw-700 text-primary">₱{{ number_format($cust['total_spent'], 0) }}</td>
                                        <td class="text-end">₱{{ number_format($cust['avg_order_value'], 0) }}</td>
                                        <td class="text-center"><span class="an-type-badge">{{ $cust['segment'] }}</span></td>
                                        <td class="text-center">
                                            @if($cust['is_churned'])
                                                <span class="an-status-inactive">Churned</span>
                                            @else
                                                <span class="an-status-active">Active</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <p class="text-muted mb-0">No customer data available</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>{{-- /an-all-charts-container --}}
</div>{{-- /analytics-root --}}


{{-- Date Range Modal --}}
<div class="modal fade" id="dateRangeModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;overflow:hidden;">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#1e3a8a,#2563eb);padding:1.4rem 1.5rem;">
                <h5 class="modal-title fw-800 text-white mb-0">
                    <i class="bi bi-calendar-range me-2"></i>Date Range
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('admin.analytics') }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-700 small text-uppercase text-muted" style="letter-spacing:.5px;">Start Date</label>
                        <input type="date" class="form-control" name="start_date" value="{{ $startDate }}" style="border-radius:10px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-700 small text-uppercase text-muted" style="letter-spacing:.5px;">End Date</label>
                        <input type="date" class="form-control" name="end_date" value="{{ $endDate }}" style="border-radius:10px;">
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year'] as $k=>$l)
                        <button type="button" class="btn btn-sm btn-outline-secondary an-quick-btn" onclick="setQuickRange('{{ $k }}')">{{ $l }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light flex-fill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary flex-fill">Apply Range</button>
                </div>
            </form>
        </div>
    </div>
</div>


@push('styles')
<style>
/* Executive Summary Styles */
.an-executive-summary { background: white; border: 1px solid var(--slate-100); border-radius: 12px; padding: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.an-exec-header { margin-bottom: 0.4rem; }
.an-exec-header h5 { font-size: 0.95rem; font-weight: 800; color: var(--slate-800); margin: 0; }
.an-exec-header small { font-size: 0.7rem; color: var(--slate-500); }

.an-exec-card { background: white; border: 1px solid var(--slate-100); border-radius: 10px; padding: 0.85rem; display: flex; align-items: center; gap: 0.75rem; transition: all 0.3s ease; position: relative; overflow: hidden; }
.an-exec-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.08); border-color: var(--slate-200); }
.an-exec-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, #3b82f6, #8b5cf6); opacity: 0; transition: opacity 0.3s ease; }
.an-exec-card:hover::before { opacity: 1; }

.an-exec-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.15rem; flex-shrink: 0; box-shadow: 0 3px 8px rgba(0,0,0,0.12); }
.an-exec-content { flex: 1; min-width: 0; }
.an-exec-label { font-size: 0.65rem; font-weight: 600; color: var(--slate-500); text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 0.2rem; }
.an-exec-value { font-size: 1.4rem; font-weight: 900; color: var(--slate-800); line-height: 1; margin-bottom: 0.2rem; }
.an-exec-sub { font-size: 0.65rem; color: var(--slate-500); }
.an-exec-trend { width: 26px; height: 26px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0; }
.an-exec-trend.up { background: #f0fdf4; color: #15803d; }
.an-exec-trend.down { background: #fef2f2; color: #b91c1c; }
.an-exec-trend.neutral { background: var(--slate-100); color: var(--slate-500); }

.an-metric-card { background: white; border: 1px solid var(--slate-100); border-radius: 10px; padding: 0.75rem; text-align: center; transition: all 0.3s ease; }
.an-metric-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.06); border-color: var(--slate-200); }
.an-metric-icon { font-size: 1.4rem; margin-bottom: 0.4rem; }
.an-metric-label { font-size: 0.62rem; font-weight: 600; color: var(--slate-500); text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 0.2rem; }
.an-metric-value { font-size: 1.2rem; font-weight: 900; color: var(--slate-800); line-height: 1; margin-bottom: 0.2rem; }
.an-metric-sub { font-size: 0.62rem; color: var(--slate-500); }

[data-theme="dark"] .an-executive-summary,
[data-theme="dark"] .an-exec-card,
[data-theme="dark"] .an-metric-card { background: #1e293b; border-color: #334155; }
[data-theme="dark"] .an-exec-header h5,
[data-theme="dark"] .an-exec-value,
[data-theme="dark"] .an-metric-value { color: #f1f5f9; }
[data-theme="dark"] .an-exec-label,
[data-theme="dark"] .an-exec-sub,
[data-theme="dark"] .an-metric-label,
[data-theme="dark"] .an-metric-sub { color: #94a3b8; }
[data-theme="dark"] .an-exec-trend.neutral { background: #334155; color: #94a3b8; }

@media(max-width: 768px) {
    .an-exec-card { flex-direction: column; text-align: center; }
    .an-exec-icon { margin-bottom: 0.5rem; }
}

<style>
* { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
body { font-size: 0.813rem; }

.analytics-root { padding: 0 0 2rem; }

.an-page-header { background:linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);border-radius:16px;padding:1rem 1.5rem;margin-bottom:1.5rem;box-shadow:0 4px 20px rgba(59,130,246,0.25); }

.an-header-content { display:flex;align-items:center;justify-content:space-between;gap:1.5rem; }
.an-header-left { display:flex;align-items:center;gap:1rem;flex:1; }
.an-header-right { display:flex;align-items:center;gap:0.75rem; }

.an-back-link { display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,0.15);border:1.5px solid rgba(255,255,255,0.25);color:white;text-decoration:none;transition:all .2s ease;flex-shrink:0; }
.an-back-link:hover { background:rgba(255,255,255,0.25);transform:translateX(-2px); }
.an-back-link i { font-size:1.1rem; }

.an-title-group { display:flex;align-items:center;gap:1rem;flex-wrap:wrap; }
.an-page-title { font-size:1.4rem;font-weight:900;margin:0;color:white;letter-spacing:-0.5px;line-height:1; }
.an-date-range { display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;color:rgba(255,255,255,0.9);font-weight:600; }
.an-date-range i { font-size:1rem; }
.an-date-badge { background:rgba(255,255,255,0.2);border-radius:6px;padding:2px 8px;font-size:0.75rem;font-weight:700; }

.an-filter-select { min-width:180px;background:rgba(255,255,255,0.95);border:1.5px solid rgba(255,255,255,0.3);border-radius:10px;padding:0.5rem 1rem;font-weight:600;font-size:0.85rem;color:#1e293b;transition:all .2s ease; }
.an-filter-select:focus { border-color:white;box-shadow:0 0 0 3px rgba(255,255,255,0.2);outline:none;background:white; }
.an-filter-select:hover { background:white;border-color:white; }

.an-filter-btn { width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.15);border:1.5px solid rgba(255,255,255,0.25);border-radius:10px;color:white;transition:all .2s ease;cursor:pointer; }
.an-filter-btn:hover { background:rgba(255,255,255,0.25);transform:translateY(-1px); }
.an-filter-btn i { font-size:1rem; }

.an-export-btn { width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.15);border:1.5px solid rgba(255,255,255,0.25);border-radius:10px;color:white;transition:all .2s ease;cursor:pointer; }
.an-export-btn:hover { background:rgba(255,255,255,0.25);transform:translateY(-1px); }
.an-export-btn i { font-size:1rem; }

.an-live-indicator { display:flex;align-items:center;justify-content:center;width:36px;height:36px;background:rgba(255,255,255,0.15);border:1.5px solid rgba(255,255,255,0.25);border-radius:10px;cursor:default; }
.an-live-indicator.syncing { border-color:rgba(245,158,11,0.5);background:rgba(245,158,11,0.2); }
.an-live-indicator.updated { border-color:rgba(16,185,129,0.5);background:rgba(16,185,129,0.2); }
.an-live-dot { width:8px;height:8px;border-radius:50%;background:#10b981;animation:an-pulse 2s infinite; }
.an-live-indicator.syncing .an-live-dot { background:#f59e0b;animation:none; }

.an-kpi-card { border-radius:14px;padding:.9rem;position:relative;overflow:hidden;min-height:110px;box-shadow:0 2px 12px rgba(0,0,0,0.06);transition:transform .25s ease,box-shadow .25s ease; }
.an-kpi-card:hover { transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,0.12); }
.an-kpi-glow { position:absolute;width:160px;height:160px;border-radius:50%;top:-40px;right:-40px;background:rgba(255,255,255,0.12);pointer-events:none; }
.an-kpi-revenue  { background:linear-gradient(135deg,#1d4ed8 0%,#3b82f6 100%);color:white; }
.an-kpi-customers{ background:linear-gradient(135deg,#7c3aed 0%,#a78bfa 100%);color:white; }
.an-kpi-retail   { background:linear-gradient(135deg,#0891b2 0%,#06b6d4 100%);color:white; }
.an-kpi-profit   { background:linear-gradient(135deg,#059669 0%,#10b981 100%);color:white; }
.an-kpi-expenses { background:linear-gradient(135deg,#dc2626 0%,#ef4444 100%);color:white; }
.an-kpi-laundry  { background:linear-gradient(135deg,#d97706 0%,#f59e0b 100%);color:white; }
.an-kpi-icon-sm { width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,0.18);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:1rem;color:white;margin-bottom:.5rem; }
.an-kpi-label { font-size:.68rem;font-weight:600;opacity:.85;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.3rem; }
.an-kpi-value-sm { font-size:1.4rem;font-weight:900;line-height:1.1; }

.an-all-charts-container { padding:0; }
.an-section-header { background:white;border:1px solid var(--slate-100);border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;margin-top:2rem;box-shadow:0 1px 6px rgba(0,0,0,0.04); }
.an-section-header h5 { font-size:1rem;font-weight:800;color:var(--slate-800);margin:0; }
.an-section-header:first-of-type { margin-top:0; }

.an-chart-card { background:white;border:1px solid var(--slate-100);border-radius:14px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.04);transition:box-shadow .2s ease; }
.an-chart-card:hover { box-shadow:0 8px 24px rgba(0,0,0,0.09); }
.an-chart-header { display:flex;align-items:flex-start;justify-content:space-between;padding:.9rem 1rem .6rem;border-bottom:1px solid var(--slate-100); }
.an-chart-body { padding:.9rem 1rem; }
.an-chart-badge { display:inline-flex;align-items:center;border-radius:50px;padding:3px 11px;font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap; }
.an-chart-badge.blue   { background:#eff6ff;color:#2563eb; }
.an-chart-badge.purple { background:#f5f3ff;color:#7c3aed; }
.an-chart-badge.teal   { background:#ecfeff;color:#0e7490; }
.an-chart-badge.orange { background:#fff7ed;color:#c2410c; }
.an-chart-badge.green  { background:#f0fdf4;color:#15803d; }

.an-branch-dot { width:10px;height:10px;border-radius:50%;flex-shrink:0; }
.an-branch-bar-track { height:6px;border-radius:99px;background:var(--slate-100);overflow:hidden; }
.an-branch-bar-fill { height:100%;border-radius:99px;transition:width 0.8s cubic-bezier(.4,0,.2,1); }

.an-table { width:100%;border-collapse:collapse; }
.an-table thead tr { border-bottom:2px solid var(--slate-100); }
.an-table th { padding:.75rem 1.25rem;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--slate-500);white-space:nowrap; }
.an-table td { padding:.85rem 1.25rem;border-bottom:1px solid var(--slate-100);vertical-align:middle; }
.an-table tr:last-child td { border-bottom:none; }
.an-table tr:hover td { background:#f8fafc; }
.an-table-rank { display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:7px;background:var(--slate-100);color:var(--slate-500);font-size:.7rem;font-weight:800; }
.an-usage-badge { background:#eff6ff;color:#2563eb;border-radius:50px;padding:2px 10px;font-size:.75rem;font-weight:700; }
.an-table-bar-track { height:7px;border-radius:99px;background:var(--slate-100);overflow:hidden; }
.an-table-bar-fill  { height:100%;border-radius:99px;background:linear-gradient(90deg,#3b82f6,#8b5cf6); }

.an-leaderboard { padding:.5rem 0; }
.an-lb-row { display:flex;align-items:center;gap:10px;padding:.65rem 1.25rem;transition:background .15s ease; }
.an-lb-row:hover { background:#f8fafc; }
.an-lb-rank { width:22px;height:22px;border-radius:7px;background:var(--slate-100);color:var(--slate-500);font-size:.68rem;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.an-lb-rank.top { background:linear-gradient(135deg,#f59e0b,#d97706);color:white; }
.an-lb-avatar { width:34px;height:34px;border-radius:10px;flex-shrink:0;background:linear-gradient(135deg,#2563eb,#7c3aed);color:white;font-weight:800;font-size:.9rem;display:flex;align-items:center;justify-content:center; }
.an-lb-info { flex:1;min-width:0; }
.an-lb-name { font-weight:700;font-size:.83rem;color:var(--slate-800);white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.an-lb-sub  { font-size:.68rem;color:var(--slate-500); }
.an-lb-value { font-weight:800;color:#2563eb;font-size:.83rem;flex-shrink:0; }

.an-type-badge { background:var(--slate-100);color:var(--slate-600);border-radius:50px;padding:2px 9px;font-size:.7rem;font-weight:700; }
.an-status-active   { background:#f0fdf4;color:#15803d;border-radius:50px;padding:2px 9px;font-size:.7rem;font-weight:700; }
.an-status-inactive { background:var(--slate-100);color:var(--slate-500);border-radius:50px;padding:2px 9px;font-size:.7rem;font-weight:700; }
.an-roi-badge { border-radius:50px;padding:2px 10px;font-size:.72rem;font-weight:800; }
.an-roi-badge.success { background:#f0fdf4;color:#15803d; }
.an-roi-badge.warning { background:#fffbeb;color:#b45309; }
.an-roi-badge.danger  { background:#fef2f2;color:#b91c1c; }
.an-quick-btn { border-radius:50px;font-size:.75rem;font-weight:700; }

.an-live-indicator { display:inline-flex;align-items:center;gap:6px;background:white;border:1.5px solid var(--slate-100);border-radius:10px;padding:0.5rem 1rem;font-size:.75rem;font-weight:700;color:var(--slate-500);cursor:default;user-select:none;transition:all .3s ease; }
.an-live-indicator.syncing { border-color:#f59e0b;color:#b45309; }
.an-live-indicator.updated { border-color:#10b981;color:#065f46; }
.an-live-dot { width:8px;height:8px;border-radius:50%;background:#10b981;animation:an-pulse 2s infinite; }
.an-live-indicator.syncing .an-live-dot { background:#f59e0b;animation:none; }
@keyframes an-pulse { 0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.8)} }
@keyframes an-flip-in { 0%{opacity:0;transform:translateY(-8px)}100%{opacity:1;transform:translateY(0)} }
.an-kpi-updating { animation:an-flip-in .4s ease forwards; }

/* Dark mode */
[data-theme="dark"] .an-page-header { background:linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); }
[data-theme="dark"] .an-filter-select { background:rgba(30,41,59,0.95);color:#f1f5f9; }
[data-theme="dark"] .an-filter-select:focus,[data-theme="dark"] .an-filter-select:hover { background:#1e293b; }
[data-theme="dark"] .an-section-header h5 { color:#f1f5f9; }
[data-theme="dark"] .an-chart-header { border-color:#334155; }
[data-theme="dark"] .an-table th { color:#94a3b8; }
[data-theme="dark"] .an-table td { border-color:#334155;color:#e2e8f0; }
[data-theme="dark"] .an-table tr:hover td { background:#253348; }
[data-theme="dark"] .an-table-rank,[data-theme="dark"] .an-lb-rank { background:#334155;color:#94a3b8; }
[data-theme="dark"] .an-branch-bar-track,[data-theme="dark"] .an-table-bar-track { background:#334155; }
[data-theme="dark"] .an-lb-row:hover { background:#253348; }
[data-theme="dark"] .an-usage-badge { background:rgba(37,99,235,.15);color:#93c5fd; }
[data-theme="dark"] .an-date-btn { background:#1e293b;border-color:#334155;color:#f1f5f9; }
[data-theme="dark"] .an-date-btn:hover { background:#334155;border-color:#3b82f6; }
[data-theme="dark"] .an-back-btn:hover { background:#334155; }
[data-theme="dark"] .an-branch-filter-select { background:#1e293b;border-color:#334155;color:#f1f5f9;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 9L1 4h10z'/%3E%3C/svg%3E"); }
[data-theme="dark"] .an-branch-filter-select:focus { border-color:#3b82f6; }
[data-theme="dark"] .an-branch-filter-select:hover { border-color:#3b82f6;background:#253348; }
[data-theme="dark"] .an-filter-icon { color:#94a3b8; }
[data-theme="dark"] .an-type-badge,[data-theme="dark"] .an-status-inactive { background:#334155;color:#94a3b8; }
[data-theme="dark"] .an-live-indicator { background:#1e293b;border-color:#334155; }
[data-theme="dark"] .fw-800,.text-slate-800,[data-theme="dark"] h4,[data-theme="dark"] h5,[data-theme="dark"] h6 { color:#f1f5f9 !important; }
[data-theme="dark"] .text-muted,[data-theme="dark"] small.text-muted { color:#94a3b8 !important; }
[data-theme="dark"] .modal-content { background:#1e293b;color:#f1f5f9; }
[data-theme="dark"] .form-label { color:#94a3b8 !important; }
[data-theme="dark"] .form-control { background:#334155;border-color:#475569;color:#f1f5f9; }

@media(max-width:768px){
    .an-page-header { padding:1rem; }
    .an-header-content { flex-wrap:wrap;gap:1rem; }
    .an-title-group { flex-direction:column;align-items:flex-start;gap:0.5rem; }
    .an-page-title { font-size:1.2rem; }
    .an-header-right { width:100%;justify-content:space-between; }
    .an-filter-select { flex:1;min-width:auto; }
}
</style>
@endpush


@push('scripts')
<script src="{{ asset('assets/chart.js/chart.umd.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const isDark = () => document.documentElement.getAttribute('data-theme') === 'dark'
                      || document.body.getAttribute('data-theme') === 'dark';

    function theme() {
        const dark = isDark();
        return {
            grid     : dark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)',
            tick     : dark ? '#94a3b8' : '#6b7280',
            legend   : dark ? '#cbd5e1' : '#374151',
            tooltipBg: dark ? 'rgba(15,23,42,0.95)' : 'rgba(30,41,59,0.95)',
            border   : dark ? 'rgba(30,41,59,0.8)' : 'rgba(255,255,255,0.8)',
        };
    }

    const COLORS = ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444','#ec4899','#6366f1','#14b8a6','#f97316'];

    function ax(opts = {}) {
        const t = theme();
        return { grid:{color:t.grid,drawBorder:false}, ticks:{color:t.tick,font:{size:11}}, ...opts };
    }
    function tip() {
        const t = theme();
        return { backgroundColor:t.tooltipBg, titleColor:'#fff', bodyColor:'#e5e7eb', padding:12, cornerRadius:10, mode:'index', intersect:false };
    }

    // ── Branch Filter ────────────────────────────────────────────
    const branchFilter = document.getElementById('branchFilter');
    if (branchFilter) {
        branchFilter.addEventListener('change', function () {
            const url = new URL(window.location.href);
            this.value === 'all' ? url.searchParams.delete('branch_id') : url.searchParams.set('branch_id', this.value);
            window.location.href = url.toString();
        });
    }

    // ── PHP Data ─────────────────────────────────────────────────
    const branchNames  = @json(array_column($branchPerformance['branches'] ?? [], 'name'));
    const branchRev    = @json($branchPerformance['revenue_data'] ?? []);
    const branchOrds   = @json($branchPerformance['laundry_data'] ?? []);

    {{-- Real profit/expense by branch — expects controller to provide these --}}
    const profitLabels = @json($profitAnalytics['labels'] ?? []);
    const profitRevData = @json(array_map(fn($item) => $item['revenue'] ?? 0, $profitAnalytics['daily_data'] ?? []));
    const profitExpData = @json(array_map(fn($item) => $item['expenses'] ?? 0, $profitAnalytics['daily_data'] ?? []));
    const profitNetData = @json($profitAnalytics['data'] ?? []);

    {{-- Retail sales by branch --}}
    const retailByBranch = @json($branchPerformance['retail_data'] ?? []);

    {{-- Expense categories --}}
    const expCatLabels = @json($expenseCategoryData['labels'] ?? []);
    const expCatData   = @json($expenseCategoryData['data'] ?? []);

    {{-- Monthly comparison (current year vs last year) --}}
    const monthLabels  = @json($monthlyComparison['labels'] ?? []);
    const monthCurrent = @json($monthlyComparison['current_year'] ?? []);
    const monthPrev    = @json($monthlyComparison['previous_year'] ?? []);

    {{-- Service data --}}
    const svcLbls  = @json($servicePopularity['labels'] ?? []);
    const svcRev   = @json($servicePopularity['revenue_data'] ?? []);
    const svcOrds  = @json($servicePopularity['laundry_data'] ?? []);

    {{-- Customer data --}}
    const custGrowthL = @json($customerAnalytics['growth_labels'] ?? []);
    const custGrowthD = @json($customerAnalytics['growth_data'] ?? []);
    const newCustD    = @json($customerAnalytics['new_data'] ?? []);
    const retCustD    = @json($customerAnalytics['returning_data'] ?? []);
    @php
        $custRegSrcData  = $customerAnalytics['registration_source'] ?? ['walk_in'=>0,'self_registered'=>0];
        $topCustVData    = $customerAnalytics['top_customers']->pluck('laundries_sum_total_amount')->take(8)->map(fn($v) => (float)($v ?? 0))->toArray();
        $topCustNData    = $customerAnalytics['top_customers']->pluck('name')->take(8)->toArray();
    @endphp
    const custRegSrc = @json($custRegSrcData);
    const topCustN   = @json($topCustNData);
    const topCustV   = @json($topCustVData);

    {{-- Inventory turnover --}}
    const invItems    = @json($inventoryTurnover['labels'] ?? []);
    const invConsumed = @json($inventoryTurnover['consumed'] ?? []);
    const invRestocked= @json($inventoryTurnover['restocked'] ?? []);

    {{-- Promotions --}}
    const promoLbls  = @json($promotionEffectiveness['labels'] ?? []);
    const promoUsage = @json($promotionEffectiveness['usage_data'] ?? []);
    const promoRevArr= @json(array_column($promotionEffectiveness['promotions'] ?? [], 'revenue'));
    const promoDisArr= @json(array_column($promotionEffectiveness['promotions'] ?? [], 'total_discount'));

    {{-- Staff Performance --}}
    const staffLabels = @json($staffPerformance['labels'] ?? []);
    const staffRevenue = @json($staffPerformance['revenue_data'] ?? []);
    const staffLaundries = @json($staffPerformance['laundries_data'] ?? []);
    const staffProductivity = @json($staffPerformance['productivity_data'] ?? []);

    {{-- Peak Hours --}}
    const hourlyLabels = @json($peakHoursAnalytics['hourly_labels'] ?? []);
    const hourlyCounts = @json($peakHoursAnalytics['hourly_counts'] ?? []);
    const hourlyRevenue = @json($peakHoursAnalytics['hourly_revenue'] ?? []);
    const dayLabels = @json($peakHoursAnalytics['daily_labels'] ?? []);
    const dayCounts = @json($peakHoursAnalytics['daily_counts'] ?? []);

    {{-- Customer Lifetime Value --}}
    const clvSegmentLabels = @json($customerLifetimeValue['segment_labels'] ?? []);
    const clvSegmentCounts = @json($customerLifetimeValue['segment_counts'] ?? []);

    const t = theme();


    // ════════════════════════════════════════════════════════════
    // SECTION 1: BRANCH ANALYTICS
    // ════════════════════════════════════════════════════════════

    // Revenue, Profit & Expenses Line Chart (Over Time)
    const rpCtx = document.getElementById('realProfitExpensesChart');
    if (rpCtx && profitLabels.length) {
        new Chart(rpCtx, {
            type: 'line',
            data: {
                labels: profitLabels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: profitRevData,
                        borderColor: '#534AB7',
                        backgroundColor: 'rgba(83,74,183,0.15)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        borderDash: [],
                        pointRadius: 3,
                        pointBackgroundColor: '#534AB7',
                        pointBorderColor: isDark() ? '#1e293b' : '#fff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 5
                    },
                    {
                        label: 'Expenses',
                        data: profitExpData,
                        borderColor: '#D85A30',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        borderDash: [5, 3],
                        pointRadius: 3,
                        pointBackgroundColor: '#D85A30',
                        pointBorderColor: isDark() ? '#1e293b' : '#fff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 5
                    },
                    {
                        label: 'Net Profit',
                        data: profitNetData,
                        borderColor: '#1D9E75',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        borderDash: [2, 2],
                        pointRadius: 3,
                        pointBackgroundColor: '#1D9E75',
                        pointBorderColor: isDark() ? '#1e293b' : '#fff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 5
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { labels: { color: t.legend, usePointStyle: true, font: { size: 11 } } },
                    tooltip: { ...tip(), callbacks: { label: ctx => ` ${ctx.dataset.label}: ₱${ctx.raw.toLocaleString()}` } }
                },
                scales: {
                    y: { ...ax(), beginAtZero: true, ticks: { ...ax().ticks, callback: v => '₱'+(v>=1000?(v/1000).toFixed(0)+'k':v) } },
                    x: { ...ax(), grid: { display: false } }
                }
            }
        });
    }

    // Retail Sales by Branch
    const rtlCtx = document.getElementById('retailByBranchChart');
    if (rtlCtx && branchNames.length) {
        new Chart(rtlCtx, {
            type: 'bar',
            data: {
                labels: branchNames,
                datasets: [{
                    label: 'Retail Sales',
                    data: retailByBranch.length ? retailByBranch : branchRev.map(v => Math.round(v * 0)), // zeros if no data
                    backgroundColor: COLORS.slice(0, branchNames.length),
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { ...tip(), callbacks: { label: ctx => ` ₱${ctx.raw.toLocaleString()}` } }
                },
                scales: {
                    y: { ...ax(), beginAtZero: true, ticks: { ...ax().ticks, callback: v => '₱'+(v>=1000?(v/1000).toFixed(0)+'k':v) } },
                    x: { ...ax(), grid: { display: false } }
                }
            }
        });
    }

    // Expense Breakdown by Category
    const expCtx = document.getElementById('expenseCategoryChart');
    if (expCtx) {
        if (expCatLabels.length === 0) {
            expCtx.closest('.an-chart-body').innerHTML =
                '<div class="text-center py-4 text-muted"><i class="bi bi-receipt" style="font-size:2rem;opacity:.2;"></i><p class="mt-2 small">No expense data for this period</p></div>';
        } else {
            new Chart(expCtx, {
                type: 'doughnut',
                data: {
                    labels: expCatLabels,
                    datasets: [{ data: expCatData, backgroundColor: COLORS, borderColor: t.border, borderWidth: 2, hoverOffset: 10 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: true, cutout: '65%',
                    animation: { animateScale: true },
                    plugins: {
                        legend: { position: 'bottom', labels: { color: t.legend, padding: 10, usePointStyle: true, font: { size: 10 } } },
                        tooltip: { ...tip(), callbacks: { label: ctx => ` ${ctx.label}: ₱${ctx.raw.toLocaleString()}` } }
                    }
                }
            });
        }
    }

    // Monthly Comparison (current year vs previous year)
    const mcCtx = document.getElementById('monthlyComparisonChart');
    if (mcCtx) {
        new Chart(mcCtx, {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [
                    { label: '{{ now()->year }}',     data: monthCurrent, backgroundColor: 'rgba(59,130,246,0.85)', borderRadius: 5 },
                    { label: '{{ now()->year - 1 }}', data: monthPrev,    backgroundColor: 'rgba(148,163,184,0.5)', borderRadius: 5 },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: t.legend, usePointStyle: true, font: { size: 11 } } },
                    tooltip: { ...tip(), callbacks: { label: ctx => ` ₱${ctx.raw.toLocaleString()}` } }
                },
                scales: {
                    y: { ...ax(), beginAtZero: true, ticks: { ...ax().ticks, callback: v => '₱'+(v>=1000?(v/1000).toFixed(0)+'k':v) } },
                    x: { ...ax(), grid: { display: false } }
                }
            }
        });
    }

    // Branch Performance Bar
    const branchBarCtx = document.getElementById('branchBarChart');
    if (branchBarCtx) {
        new Chart(branchBarCtx, {
            type: 'bar',
            data: {
                labels: branchNames,
                datasets: [{ label: 'Revenue', data: branchRev, backgroundColor: COLORS.slice(0, branchRev.length), borderRadius: 8 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { ...tip(), callbacks: { label: ctx => ` ₱${ctx.raw.toLocaleString()}` } } },
                scales: {
                    y: { ...ax(), beginAtZero: true, ticks: { ...ax().ticks, callback: v => '₱'+(v>=1000?(v/1000).toFixed(0)+'k':v) } },
                    x: { ...ax(), grid: { display: false } }
                }
            }
        });
    }


    // ════════════════════════════════════════════════════════════
    // SECTION 2: SERVICE ANALYTICS
    // ════════════════════════════════════════════════════════════

    // Top Services by Revenue (horizontal sorted bar)
    const topSvcCtx = document.getElementById('topServiceRevenueChart');
    if (topSvcCtx && svcLbls.length) {
        const sortIdx = [...svcRev.map((v,i) => ({v,i}))].sort((a,b) => b.v - a.v);
        new Chart(topSvcCtx, {
            type: 'bar',
            data: {
                labels: sortIdx.map(s => svcLbls[s.i] || ''),
                datasets: [{
                    label: 'Revenue',
                    data: sortIdx.map(s => s.v),
                    backgroundColor: sortIdx.map((_, i) => COLORS[i % COLORS.length]),
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { ...tip(), callbacks: { label: ctx => ` ₱${ctx.raw.toLocaleString()}` } } },
                scales: {
                    x: { ...ax(), beginAtZero: true, ticks: { ...ax().ticks, callback: v => '₱'+(v>=1000?(v/1000).toFixed(0)+'k':v) } },
                    y: { ...ax(), grid: { display: false } }
                }
            }
        });
    }

    // Branch Revenue vs Volume Grouped Bar
    const bGroupCtx = document.getElementById('branchGroupedChart');
    if (bGroupCtx) {
        new Chart(bGroupCtx, {
            type: 'bar',
            data: {
                labels: branchNames,
                datasets: [
                    { label: 'Revenue (₱)', data: branchRev,  backgroundColor: 'rgba(59,130,246,0.85)', borderRadius: 6, yAxisID: 'yRev' },
                    { label: 'Laundries',   data: branchOrds, backgroundColor: 'rgba(139,92,246,0.85)', borderRadius: 6, yAxisID: 'yOrd' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: t.legend, usePointStyle: true, font: { size: 11 } } },
                    tooltip: { ...tip(), callbacks: { label: ctx => ctx.datasetIndex === 0 ? ` Revenue: ₱${ctx.raw.toLocaleString()}` : ` Laundries: ${ctx.raw}` } }
                },
                scales: {
                    yRev: { ...ax(), beginAtZero: true, position: 'left',  ticks: { ...ax().ticks, callback: v => '₱'+(v>=1000?(v/1000).toFixed(0)+'k':v) } },
                    yOrd: { ...ax(), beginAtZero: true, position: 'right', grid: { display: false } },
                    x:    { ...ax(), grid: { display: false } }
                }
            }
        });
    }

    // Inventory Turnover Rate
    const invCtx = document.getElementById('inventoryTurnoverChart');
    if (invCtx) {
        if (!invItems.length) {
            invCtx.closest('.an-chart-body').innerHTML =
                '<div class="text-center py-4 text-muted"><i class="bi bi-box-seam" style="font-size:2rem;opacity:.2;"></i><p class="mt-2 small">No inventory movement data for this period</p></div>';
        } else {
            new Chart(invCtx, {
                type: 'bar',
                data: {
                    labels: invItems,
                    datasets: [
                        { label: 'Consumed', data: invConsumed,  backgroundColor: 'rgba(239,68,68,0.8)',  borderRadius: 5 },
                        { label: 'Restocked',data: invRestocked, backgroundColor: 'rgba(16,185,129,0.8)', borderRadius: 5 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: t.legend, usePointStyle: true, font: { size: 11 } } },
                        tooltip: { ...tip(), callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.raw} units` } }
                    },
                    scales: {
                        y: { ...ax(), beginAtZero: true, ticks: { ...ax().ticks } },
                        x: { ...ax(), grid: { display: false }, ticks: { ...ax().ticks, maxRotation: 45 } }
                    }
                }
            });
        }
    }


    // ════════════════════════════════════════════════════════════
    // SECTION 3: CUSTOMER ANALYTICS
    // ════════════════════════════════════════════════════════════

    // Customer Acquisition Trend — New vs Returning
    const acqCtx = document.getElementById('customerAcquisitionChart');
    if (acqCtx && custGrowthL.length) {
        new Chart(acqCtx, {
            type: 'line',
            data: {
                labels: custGrowthL,
                datasets: [
                    {
                        label: 'New customers',
                        data: newCustD.length ? newCustD : custGrowthD,
                        borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.12)',
                        borderWidth: 3, fill: true, tension: 0.5,
                        pointBackgroundColor: '#10b981', pointBorderColor: isDark() ? '#1e293b' : '#fff',
                        pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6
                    },
                    {
                        label: 'Returning customers',
                        data: retCustD.length ? retCustD : custGrowthD.map(v => Math.round(v * 0.65)),
                        borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.08)',
                        borderWidth: 3, fill: true, tension: 0.5,
                        pointBackgroundColor: '#8b5cf6', pointBorderColor: isDark() ? '#1e293b' : '#fff',
                        pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6,
                        borderDash: [6, 4]
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { labels: { color: t.legend, usePointStyle: true, font: { size: 11 } } },
                    tooltip: { ...tip(), callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.raw}` } }
                },
                scales: {
                    y: { ...ax(), beginAtZero: true },
                    x: { ...ax(), grid: { display: false } }
                }
            }
        });
    }

    // Customer Type Doughnut
    const custTypeCtx = document.getElementById('customerTypeChart');
    if (custTypeCtx) {
        new Chart(custTypeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Walk-In', 'Self-Registered'],
                datasets: [{
                    data: [custRegSrc.walk_in || 0, custRegSrc.self_registered || 0],
                    backgroundColor: ['#34d399','#818cf8'],
                    borderColor: t.border, borderWidth: 2, hoverOffset: 10
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: true, cutout: '68%',
                animation: { animateScale: true },
                plugins: {
                    legend: { position: 'bottom', labels: { color: t.legend, padding: 14, usePointStyle: true, font: { size: 11 } } },
                    tooltip: { ...tip(), callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}` } }
                }
            }
        });
    }

    // Top Customers Horizontal Bar
    const topCustCtx = document.getElementById('topCustomersChart');
    if (topCustCtx) {
        new Chart(topCustCtx, {
            type: 'bar',
            data: {
                labels: topCustN,
                datasets: [{
                    label: 'Total Spend',
                    data: topCustV,
                    backgroundColor: topCustN.map((_, i) => COLORS[i % COLORS.length]),
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { ...tip(), callbacks: { label: ctx => ` ₱${ctx.raw.toLocaleString()}` } }
                },
                scales: {
                    x: { ...ax(), beginAtZero: true, ticks: { ...ax().ticks, callback: v => '₱'+(v>=1000?(v/1000).toFixed(0)+'k':v) } },
                    y: { ...ax(), grid: { display: false } }
                }
            }
        });
    }


    // ════════════════════════════════════════════════════════════
    // SECTION 4: PROMOTION ANALYTICS
    // ════════════════════════════════════════════════════════════

    // Promotion ROI Comparison
    const promoRoiCtx = document.getElementById('promoRoiChart');
    if (promoRoiCtx) {
        new Chart(promoRoiCtx, {
            type: 'bar',
            data: {
                labels: promoLbls,
                datasets: [
                    { label: 'Revenue Generated', data: promoRevArr, backgroundColor: 'rgba(59,130,246,0.85)', borderRadius: 6 },
                    { label: 'Discount Given',    data: promoDisArr, backgroundColor: 'rgba(239,68,68,0.75)',  borderRadius: 6 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: t.legend, usePointStyle: true, font: { size: 11 } } },
                    tooltip: {
                        ...tip(),
                        callbacks: {
                            label: ctx => ` ${ctx.dataset.label}: ₱${ctx.raw.toLocaleString()}`,
                            afterBody: (items) => {
                                const rev = items[0]?.raw || 0;
                                const dis = items[1]?.raw || 0;
                                if (dis > 0) return [`ROI: ${(rev / dis).toFixed(1)}x`];
                                return [];
                            }
                        }
                    }
                },
                scales: {
                    y: { ...ax(), beginAtZero: true, ticks: { ...ax().ticks, callback: v => '₱'+(v>=1000?(v/1000).toFixed(0)+'k':v) } },
                    x: { ...ax(), grid: { display: false }, ticks: { ...ax().ticks, maxRotation: 30 } }
                }
            }
        });
    }

    // Promotion Usage
    const promoUsageCtx = document.getElementById('promoUsageChart');
    if (promoUsageCtx) {
        new Chart(promoUsageCtx, {
            type: 'bar',
            data: {
                labels: promoLbls,
                datasets: [{
                    label: 'Usage Count',
                    data: promoUsage,
                    backgroundColor: COLORS.slice(0, promoLbls.length),
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { ...tip(), callbacks: { label: ctx => ` ${ctx.raw} uses` } } },
                scales: {
                    y: { ...ax(), beginAtZero: true },
                    x: { ...ax(), grid: { display: false }, ticks: { ...ax().ticks, maxRotation: 30 } }
                }
            }
        });
    }


    // ════════════════════════════════════════════════════════════
    // SECTION 5: STAFF PERFORMANCE ANALYTICS
    // ════════════════════════════════════════════════════════════

    // Staff Revenue Performance
    const staffRevCtx = document.getElementById('staffRevenueChart');
    if (staffRevCtx && staffLabels.length) {
        new Chart(staffRevCtx, {
            type: 'bar',
            data: {
                labels: staffLabels,
                datasets: [{
                    label: 'Revenue Generated',
                    data: staffRevenue,
                    backgroundColor: COLORS.slice(0, staffLabels.length),
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { ...tip(), callbacks: { label: ctx => ` ₱${ctx.raw.toLocaleString()}` } } },
                scales: {
                    x: { ...ax(), beginAtZero: true, ticks: { ...ax().ticks, callback: v => '₱'+(v>=1000?(v/1000).toFixed(0)+'k':v) } },
                    y: { ...ax(), grid: { display: false } }
                }
            }
        });
    }

    // Staff Productivity Score
    const staffProdCtx = document.getElementById('staffProductivityChart');
    if (staffProdCtx && staffLabels.length) {
        new Chart(staffProdCtx, {
            type: 'bar',
            data: {
                labels: staffLabels,
                datasets: [{
                    label: 'Productivity Score',
                    data: staffProductivity,
                    backgroundColor: staffProductivity.map(v => v >= 3 ? '#10b981' : v >= 2 ? '#f59e0b' : '#ef4444'),
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { ...tip(), callbacks: { label: ctx => ` Score: ${ctx.raw}` } } },
                scales: {
                    x: { ...ax(), beginAtZero: true },
                    y: { ...ax(), grid: { display: false } }
                }
            }
        });
    }


    // ════════════════════════════════════════════════════════════
    // SECTION 6: PEAK HOURS & DEMAND ANALYTICS
    // ════════════════════════════════════════════════════════════

    // Hourly Order Distribution
    const hourlyOrdersCtx = document.getElementById('hourlyOrdersChart');
    if (hourlyOrdersCtx) {
        new Chart(hourlyOrdersCtx, {
            type: 'line',
            data: {
                labels: hourlyLabels,
                datasets: [{
                    label: 'Orders',
                    data: hourlyCounts,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139,92,246,0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#8b5cf6',
                    pointBorderColor: isDark() ? '#1e293b' : '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { ...tip(), callbacks: { label: ctx => ` ${ctx.raw} orders` } }
                },
                scales: {
                    y: { ...ax(), beginAtZero: true },
                    x: { ...ax(), grid: { display: false }, ticks: { ...ax().ticks, maxRotation: 45 } }
                }
            }
        });
    }

    // Day of Week Distribution
    const dayOfWeekCtx = document.getElementById('dayOfWeekChart');
    if (dayOfWeekCtx) {
        new Chart(dayOfWeekCtx, {
            type: 'bar',
            data: {
                labels: dayLabels,
                datasets: [{
                    label: 'Orders',
                    data: dayCounts,
                    backgroundColor: ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444','#ec4899'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { ...tip(), callbacks: { label: ctx => ` ${ctx.raw} orders` } } },
                scales: {
                    y: { ...ax(), beginAtZero: true },
                    x: { ...ax(), grid: { display: false } }
                }
            }
        });
    }

    // Hourly Revenue Distribution
    const hourlyRevCtx = document.getElementById('hourlyRevenueChart');
    if (hourlyRevCtx) {
        new Chart(hourlyRevCtx, {
            type: 'line',
            data: {
                labels: hourlyLabels,
                datasets: [{
                    label: 'Revenue',
                    data: hourlyRevenue,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.15)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: isDark() ? '#1e293b' : '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { ...tip(), callbacks: { label: ctx => ` ₱${ctx.raw.toLocaleString()}` } }
                },
                scales: {
                    y: { ...ax(), beginAtZero: true, ticks: { ...ax().ticks, callback: v => '₱'+(v>=1000?(v/1000).toFixed(0)+'k':v) } },
                    x: { ...ax(), grid: { display: false }, ticks: { ...ax().ticks, maxRotation: 45 } }
                }
            }
        });
    }


    // ════════════════════════════════════════════════════════════
    // SECTION 7: CUSTOMER LIFETIME VALUE
    // ════════════════════════════════════════════════════════════

    // Customer Segments Doughnut
    const clvSegCtx = document.getElementById('customerSegmentsChart');
    if (clvSegCtx && clvSegmentLabels.length) {
        new Chart(clvSegCtx, {
            type: 'doughnut',
            data: {
                labels: clvSegmentLabels,
                datasets: [{
                    data: clvSegmentCounts,
                    backgroundColor: ['#f59e0b','#3b82f6','#10b981','#8b5cf6','#ef4444'],
                    borderColor: t.border,
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: true, cutout: '65%',
                animation: { animateScale: true },
                plugins: {
                    legend: { position: 'bottom', labels: { color: t.legend, padding: 12, usePointStyle: true, font: { size: 11 } } },
                    tooltip: { ...tip(), callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw} customers` } }
                }
            }
        });
    }

});

// ── AJAX Polling ─────────────────────────────────────────────────
const POLL_INTERVAL = 60000;
const REFRESH_URL   = '{{ route("admin.analytics.refresh") }}';
const CSRF_TOKEN    = '{{ csrf_token() }}';
const START_DATE    = '{{ $startDate }}';
const END_DATE      = '{{ $endDate }}';
const LIVE_CHARTS   = {};

function registerChart(key, instance) { LIVE_CHARTS[key] = instance; }

function setLive(state, label) {
    const ind = document.getElementById('liveIndicator');
    const lbl = document.getElementById('liveLabel');
    if (!ind) return;
    ind.className = 'an-live-indicator ' + state;
    if (lbl) lbl.textContent = label;
}

function flipUpdate(el, text) {
    if (!el) return;
    el.classList.remove('an-kpi-updating');
    void el.offsetWidth;
    el.textContent = text;
    el.classList.add('an-kpi-updating');
}

async function pollAnalytics() {
    setLive('syncing', 'Syncing…');
    try {
        const res = await fetch(REFRESH_URL, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                start_date: START_DATE,
                end_date: END_DATE
            })
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const d = await res.json();
        flipUpdate(document.getElementById('kpi-revenue'),   '₱' + Number(d.revenue?.total  || 0).toLocaleString());
        flipUpdate(document.getElementById('kpi-customers'),       Number(d.customer?.total  || 0).toLocaleString());
        flipUpdate(document.getElementById('kpi-retail'),    '₱' + Number(d.retail_sales     || 0).toLocaleString());
        flipUpdate(document.getElementById('kpi-profit'),    '₱' + Number(d.profit           || 0).toLocaleString());
        flipUpdate(document.getElementById('kpi-expenses'),  '₱' + Number(d.expenses         || 0).toLocaleString());
        flipUpdate(document.getElementById('kpi-laundries'),       Number(d.laundry?.total   || 0).toLocaleString());
        setLive('updated', 'Updated ' + new Date().toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit' }));
        setTimeout(() => setLive('', 'Live'), 4000);
    } catch (err) {
        console.warn('Analytics poll failed:', err);
        setLive('', 'Live');
    }
}

document.addEventListener('DOMContentLoaded', () => setTimeout(() => setInterval(pollAnalytics, POLL_INTERVAL), 2000));

function setQuickRange(range) {
    const now = new Date();
    const s = document.querySelector('input[name="start_date"]');
    const e = document.querySelector('input[name="end_date"]');
    let start, end = new Date();
    switch(range) {
        case 'today': start = new Date(); break;
        case 'week':  start = new Date(now); start.setDate(now.getDate() - now.getDay()); break;
        case 'month': start = new Date(now.getFullYear(), now.getMonth(), 1); break;
        case 'year':  start = new Date(now.getFullYear(), 0, 1); break;
    }
    if (s && e) { s.value = start.toISOString().split('T')[0]; e.value = end.toISOString().split('T')[0]; }
}
</script>
@endpush
@endsection
