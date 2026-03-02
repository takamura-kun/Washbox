@extends('staff.layouts.staff')

@section('page-title', 'Dashboard Overview')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/leaflet/leaflet.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/leaflet/MarkerCluster.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/leaflet/MarkerCluster.Default.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/staff.css') }}">
@endpush

@section('content')
<div class="container-fluid px-4 py-4 dashboard-modern-wrapper">

    {{-- ================================================================
         DASHBOARD HEADER
    ================================================================ --}}
    <div class="glass-header mb-4 shadow-sm">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        {{-- FIX 1: text-dark → dashboard-title (dark-mode overrideable) --}}
                        <h2 class="fw-bold dashboard-title mb-1">Dashboard Overview</h2>
                        <p class="text-muted small mb-0 d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge-status-live"><span class="pulse-dot"></span> LIVE</span>
                            <span class="v-divider"></span>
                            <i class="bi bi-calendar-check text-primary-blue"></i>
                            <span id="current-date" class="fw-600">{{ now()->format('l, F j, Y') }}</span>
                            <span class="v-divider"></span>
                            <span class="text-success fw-bold" style="font-size:.75rem;">
                                <i class="bi bi-arrow-repeat me-1"></i>Live Sync
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0">
                <div class="d-flex gap-2 justify-content-lg-end align-items-center flex-wrap">
                    <button onclick="window.print()" class="btn btn-sm rounded-pill btn-outline-primary d-flex align-items-center">
                        <i class="bi bi-printer me-2"></i>Print
                    </button>
                    <a href="{{ route('staff.dashboard.export') }}" class="btn btn-sm rounded-pill btn-primary d-flex align-items-center">
                        <i class="bi bi-download me-2"></i>Export
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================
         SERVICES CAROUSEL
    ================================================================ --}}
    <div class="services-carousel-container shadow-sm mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-1" style="color:var(--primary-color);">
                    <i class="bi bi-grid-3x3-gap-fill me-2"></i>Our Services
                </h5>
                <p class="text-muted small mb-0">Quick access to all laundry services and add-ons</p>
            </div>
            <div class="carousel-controls">
                <button class="carousel-btn" onclick="document.querySelector('.services-carousel').scrollBy({left:-250,behavior:'smooth'})">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="carousel-btn" onclick="document.querySelector('.services-carousel').scrollBy({left:250,behavior:'smooth'})">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
        <div class="services-carousel">
            @foreach($services as $service)
                <div class="service-card-carousel" onclick="window.location.href='{{ route('staff.services.show', $service) }}'">
                    <span class="service-badge {{ $service->pricing_type === 'per_load' ? 'per-load' : 'per-kg' }}">
                        {{ $service->pricing_type === 'per_load' ? 'Per Load' : 'Per Kg' }}
                    </span>
                    <div class="service-icon-wrapper"><i class="bi bi-droplet"></i></div>
                    <h6 class="fw-bold mb-1">{{ Str::limit($service->name, 20) }}</h6>
                    <div class="service-price">
                        @if($service->pricing_type === 'per_load')
                            ₱{{ number_format($service->price_per_load, 2) }}/load
                        @else
                            ₱{{ number_format($service->price_per_piece, 2) }}/pc
                        @endif
                    </div>
                    <div class="service-description">{{ $service->description ?? 'No description' }}</div>
                    <div class="service-stats">
                        <div class="service-stat-item">
                            <div class="service-stat-value">{{ $service->laundries_count ?? 0 }}</div>
                            <div class="text-muted">Used</div>
                        </div>
                        <div class="service-stat-item">
                            <div class="service-stat-value">{{ $service->turnaround_time }}h</div>
                            <div class="text-muted">TAT</div>
                        </div>
                    </div>
                </div>
            @endforeach
            @foreach($addons as $addon)
                {{-- FIX 3: Replace inline styles with semantic classes for dark-mode support --}}
                <div class="service-card-carousel" onclick="window.location.href='{{ route('staff.addons.show', $addon) }}'">
                    <span class="service-badge service-badge-addon">Add-on</span>
                    <div class="service-icon-wrapper service-icon-addon">
                        <i class="bi bi-plus-circle"></i>
                    </div>
                    <h6 class="fw-bold mb-1">{{ Str::limit($addon->name, 20) }}</h6>
                    <div class="service-price text-success">₱{{ number_format($addon->price, 2) }}</div>
                    <div class="service-description">{{ $addon->description ?? 'No description' }}</div>
                    <div class="service-stats">
                        <div class="service-stat-item">
                            <div class="service-stat-value">{{ $addon->laundries_count ?? 0 }}</div>
                            <div class="text-muted">Used</div>
                        </div>
                        <div class="service-stat-item">
                            <div class="service-stat-value"><i class="bi bi-plus text-success"></i></div>
                            <div class="text-muted">Add-on</div>
                        </div>
                    </div>
                </div>
            @endforeach
            {{-- FIX 3: View All card uses semantic classes instead of inline styles --}}
            <div class="service-card-carousel service-card-view-all" onclick="window.location.href='{{ route('staff.services.index') }}'">
                <div class="service-icon-wrapper service-icon-view-all">
                    <i class="bi bi-arrow-right"></i>
                </div>
                <h6 class="fw-bold mb-1 text-center">View All</h6>
                <div class="service-description text-center">Browse all services & add-ons</div>
                <div class="text-center mt-3"><span class="badge bg-primary">View All →</span></div>
            </div>
        </div>
    </div>

    {{-- ================================================================
         FILTERS
    ================================================================ --}}
    <div class="modern-card shadow-sm mb-4">
        <div class="card-body-modern">
            <form method="GET" action="{{ route('staff.dashboard') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-600"><i class="bi bi-calendar me-1"></i>Date Range</label>
                    <select name="date_range" class="form-select modern-select" onchange="this.form.submit()">
                        @foreach([
                            'today'       => 'Today',
                            'yesterday'   => 'Yesterday',
                            'last_7_days' => 'Last 7 Days',
                            'last_30_days'=> 'Last 30 Days',
                            'this_week'   => 'This Week',
                            'this_month'  => 'This Month',
                            'last_month'  => 'Last Month',
                            'this_year'   => 'This Year',
                        ] as $val => $label)
                            <option value="{{ $val }}" {{ $current_filters['date_range'] == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-600"><i class="bi bi-shop me-1"></i>Branch</label>
                    <select name="branch_id" class="form-select modern-select" disabled>
                        @foreach($branchOptions as $branch)
                            <option value="{{ $branch->id }}" selected>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('staff.dashboard') }}" class="btn btn-secondary btn-sm w-100 d-flex align-items-center justify-content-center">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ================================================================
         ALERTS
    ================================================================ --}}
    @if(count($alerts) > 0)
        <div class="mb-4">
            @foreach($alerts as $alert)
                <div class="alert-modern alert-{{ $alert['type'] }} shadow-sm mb-2">
                    <div class="d-flex align-items-center">
                        <div class="alert-icon-modern"><i class="bi bi-{{ $alert['icon'] }}"></i></div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="alert-title-modern mb-1">{{ $alert['title'] }}</h6>
                            <p class="alert-text-modern mb-0">{{ $alert['message'] }}</p>
                        </div>
                        @if(isset($alert['action']))
                            <a href="{{ $alert['action'] }}" class="btn btn-sm btn-{{ $alert['type'] }} ms-3">
                                {{ $alert['action_text'] ?? 'View' }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ================================================================
         KPI CARDS — ROW 1 (Revenue)
    ================================================================ --}}
    <div class="row g-3 mb-4">
        {{-- Today's Revenue --}}
        <div class="col-6 col-md-3">
            <div class="kpi-card-modern shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-glow icon-blue"><i class="bi bi-cash-coin text-primary-blue"></i></div>
                    <span class="kpi-trend {{ $kpis['today_revenue']['change'] >= 0 ? 'up' : 'down' }}">
                        <i class="bi bi-{{ $kpis['today_revenue']['change'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ number_format(abs($kpis['today_revenue']['change']), 1) }}%
                    </span>
                </div>
                <div class="mt-3">
                    <span class="kpi-label">Today's Revenue</span>
                    <h2 class="kpi-value">₱{{ number_format($kpis['today_revenue']['value'], 0) }}</h2>
                    <small class="text-muted">vs ₱{{ number_format($kpis['today_revenue']['yesterday'], 0) }} yesterday</small>
                </div>
            </div>
        </div>

        {{-- Weekly Revenue --}}
        <div class="col-6 col-md-3">
            <div class="kpi-card-modern shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-glow icon-indigo"><i class="bi bi-bar-chart text-indigo"></i></div>
                    <span class="kpi-trend {{ $kpis['weekly_revenue']['change'] >= 0 ? 'up' : 'down' }}">
                        <i class="bi bi-{{ $kpis['weekly_revenue']['change'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ number_format(abs($kpis['weekly_revenue']['change']), 1) }}%
                    </span>
                </div>
                <div class="mt-3">
                    <span class="kpi-label">This Week</span>
                    <h2 class="kpi-value">₱{{ number_format($kpis['weekly_revenue']['value'], 0) }}</h2>
                    <small class="text-muted">vs last week</small>
                </div>
            </div>
        </div>

        {{-- Monthly Revenue --}}
        <div class="col-6 col-md-3">
            <div class="kpi-card-modern shadow-sm kpi-success">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-glow icon-green"><i class="bi bi-graph-up-arrow text-success"></i></div>
                    <span class="kpi-trend {{ $kpis['monthly_revenue']['change'] >= 0 ? 'up' : 'down' }}">
                        <i class="bi bi-{{ $kpis['monthly_revenue']['change'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ number_format(abs($kpis['monthly_revenue']['change']), 1) }}%
                    </span>
                </div>
                <div class="mt-3">
                    <span class="kpi-label">This Month</span>
                    <h2 class="kpi-value">₱{{ number_format($kpis['monthly_revenue']['value'], 0) }}</h2>
                    <small class="text-muted">vs last month</small>
                </div>
            </div>
        </div>

        {{-- Yearly Revenue --}}
        <div class="col-6 col-md-3">
            <div class="kpi-card-modern shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-glow icon-purple"><i class="bi bi-calendar-range text-purple"></i></div>
                    <span class="kpi-trend {{ $kpis['yearly_revenue']['change'] >= 0 ? 'up' : 'down' }}">
                        <i class="bi bi-{{ $kpis['yearly_revenue']['change'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ number_format(abs($kpis['yearly_revenue']['change']), 1) }}%
                    </span>
                </div>
                <div class="mt-3">
                    <span class="kpi-label">This Year</span>
                    <h2 class="kpi-value">₱{{ number_format($kpis['yearly_revenue']['value'], 0) }}</h2>
                    <small class="text-muted">vs last year</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================
         KPI CARDS — ROW 2 (Operations & Customers)
    ================================================================ --}}
    <div class="row g-3 mb-4">
        {{-- Total Customers --}}
        <div class="col-6 col-md-3">
            <div class="kpi-card-modern shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-glow icon-cyan"><i class="bi bi-people-fill text-cyan"></i></div>
                    <span class="badge bg-success-subtle text-success" style="font-size:.65rem;">+{{ $kpis['total_customers']['today'] }} today</span>
                </div>
                <div class="mt-3">
                    <span class="kpi-label">Total Customers</span>
                    <h2 class="kpi-value">{{ number_format($kpis['total_customers']['value']) }}</h2>
                    <div class="d-flex gap-2 mt-1">
                        <small class="text-muted">+{{ $kpis['total_customers']['week'] }} this week</small>
                        <small class="text-muted">·</small>
                        <small class="text-muted">+{{ $kpis['total_customers']['month'] }} mo</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Laundries --}}
        <div class="col-6 col-md-3">
            <div class="kpi-card-modern shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-glow icon-orange"><i class="bi bi-box-seam text-orange"></i></div>
                    <a href="{{ route('staff.laundries.index') }}" class="btn btn-link p-0 text-muted" style="font-size:.7rem;">View →</a>
                </div>
                <div class="mt-3">
                    <span class="kpi-label">Active Laundries</span>
                    <h2 class="kpi-value">{{ $kpis['active_laundries']['value'] }}</h2>
                    <small class="text-muted">In progress right now</small>
                </div>
            </div>
        </div>

        {{-- Ready for Pickup --}}
        <div class="col-6 col-md-3">
            <div class="kpi-card-modern shadow-sm kpi-warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-glow icon-orange"><i class="bi bi-bag-check text-warning"></i></div>
                    <a href="{{ route('staff.laundries.index', ['status' => 'ready']) }}" class="btn btn-link p-0 text-muted" style="font-size:.7rem;">View →</a>
                </div>
                <div class="mt-3">
                    <span class="kpi-label">Ready for Pickup</span>
                    <h2 class="kpi-value">{{ $kpis['ready_for_pickup']['value'] }}</h2>
                    <small class="text-muted">Avg {{ number_format($kpis['ready_for_pickup']['avg_wait_days'], 1) }} days waiting</small>
                </div>
            </div>
        </div>

        {{-- Completed Today --}}
        <div class="col-6 col-md-3">
            <div class="kpi-card-modern shadow-sm kpi-success">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-glow icon-green"><i class="bi bi-check2-all text-success"></i></div>
                    <span class="kpi-trend up">
                        <i class="bi bi-check-circle"></i> Done
                    </span>
                </div>
                <div class="mt-3">
                    <span class="kpi-label">Completed Today</span>
                    <h2 class="kpi-value">{{ $kpis['completed_today']['value'] }}</h2>
                    <small class="text-muted">Successfully delivered</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================
         UNCLAIMED VALUE BANNER (if any)
    ================================================================ --}}
    @if($unclaimed['total_count'] > 0)
    <div class="unclaimed-banner mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="unclaimed-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div>
                    <h6 class="mb-0 fw-800 text-danger">{{ $unclaimed['total_count'] }} Unclaimed Laundries</h6>
                    <small class="text-danger-emphasis">₱{{ number_format($unclaimed['total_value'], 2) }} at risk · Oldest: {{ $unclaimed['oldest_days'] }} days</small>
                </div>
            </div>
            <div class="d-flex gap-3 align-items-center">
                @foreach($unclaimed['categorized'] as $label => $count)
                    @if($count > 0)
                        <div class="text-center">
                            <div class="fw-800 text-danger">{{ $count }}</div>
                            <small class="text-muted">{{ $label }}</small>
                        </div>
                    @endif
                @endforeach
                <a href="{{ route('staff.unclaimed.index') }}" class="btn btn-danger btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>Manage
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- ================================================================
         QUICK ACTIONS
    ================================================================ --}}
    <div class="modern-card shadow-sm mb-4">
        <div class="card-header-modern border-0 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0 fw-800 text-slate-800">Quick Actions</h6>
                <small class="text-muted">Frequently accessed functions</small>
            </div>
            <span class="badge bg-primary bg-opacity-10 text-primary"><i class="bi bi-lightning me-1"></i>Instant Access</span>
        </div>
        <div class="card-body-modern">
            <div class="row g-3">
                @php
                    $quickActions = [
                        ['route'=>'staff.laundries.create','icon'=>'bi-plus-lg','label'=>'New Laundry','desc'=>'Create order','color'=>'blue'],
                        ['route'=>'staff.customers.create','icon'=>'bi-person-plus','label'=>'New Customer','desc'=>'Register','color'=>'indigo'],
                        ['route'=>'staff.pickups.index','icon'=>'bi-truck','label'=>'Pickups','desc'=>'Delivery','color'=>'cyan'],
                        ['route'=>'staff.laundries.index','icon'=>'bi-list-check','label'=>'All Laundries','desc'=>'Manage','color'=>'green'],
                        ['route'=>'staff.reports.index','icon'=>'bi-graph-up','label'=>'Reports','desc'=>'Analytics','color'=>'purple'],
                        ['route'=>'staff.branches.index','icon'=>'bi-shop','label'=>'Branches','desc'=>'Locations','color'=>'orange'],
                    ];
                @endphp
                @foreach($quickActions as $action)
                    @if(Route::has($action['route']))
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="{{ route($action['route']) }}" class="launch-action-btn {{ $action['color'] }}">
                                <div class="launch-icon shadow-sm"><i class="bi {{ $action['icon'] }}"></i></div>
                                <h6 class="action-label mb-1">{{ $action['label'] }}</h6>
                                <small class="text-muted">{{ $action['desc'] }}</small>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- ================================================================
         TABS
    ================================================================ --}}
    <div class="modern-tabs-container mb-4">
        <ul class="nav nav-segmented shadow-sm" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#overview" type="button"><i class="bi bi-speedometer2 me-2"></i>Overview</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#revenue" type="button"><i class="bi bi-graph-up me-2"></i>Revenue</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#branches" type="button"><i class="bi bi-shop me-2"></i>Branches</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#laundries" type="button"><i class="bi bi-basket me-2"></i>Laundries</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#customers-tab" type="button"><i class="bi bi-people me-2"></i>Customers</button></li>
        </ul>
    </div>

    <div class="tab-content">
        {{-- ============================================================
             OVERVIEW TAB
        ============================================================ --}}
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row g-4">
                {{-- Revenue Trend --}}
                <div class="col-lg-8">
                    <div class="modern-card shadow-sm h-100">
                        <div class="card-header-modern bg-transparent border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-800 text-slate-800">Revenue Trend</h6>
                                <small class="text-muted">
                                    {{ ucwords(str_replace('_', ' ', $current_filters['date_range'])) }} performance
                                </small>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body-modern">
                            <div class="chart-container">
                                <canvas id="revenueTrendChart"></canvas>
                            </div>
                            <div class="row mt-3 text-center g-0 stat-strip">
                                <div class="col-4 stat-strip-item">
                                    <div class="sv">₱{{ number_format($revenue['per_day'], 0) }}</div>
                                    <div class="sl">AVG/DAY</div>
                                </div>
                                <div class="col-4 stat-strip-item">
                                    <div class="sv">₱{{ number_format($revenue['highest'], 0) }}</div>
                                    <div class="sl">HIGHEST DAY</div>
                                </div>
                                <div class="col-4 stat-strip-item">
                                    <div class="sv">{{ number_format($revenue['laundries']) }}</div>
                                    <div class="sl">TOTAL LAUNDRIES</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pipeline + Today Customer Stats --}}
                <div class="col-lg-4">
                    <div class="row g-4 h-100">
                        {{-- Customers Today Card --}}
                        <div class="col-12">
                            <div class="modern-card shadow-sm customers-today-card">
                                <div class="card-body-modern">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h6 class="fw-800 mb-0 text-slate-800">Customers Today</h6>
                                        <div class="kpi-icon-glow icon-cyan"><i class="bi bi-people-fill text-cyan"></i></div>
                                    </div>
                                    <div class="row g-2 text-center">
                                        <div class="col-3">
                                            <div class="cust-mini-stat today">
                                                <div class="cust-stat-num">{{ $kpis['total_customers']['today'] }}</div>
                                                <div class="cust-stat-lbl">Today</div>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="cust-mini-stat week">
                                                <div class="cust-stat-num">{{ $kpis['total_customers']['week'] }}</div>
                                                <div class="cust-stat-lbl">Week</div>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="cust-mini-stat month">
                                                <div class="cust-stat-num">{{ $kpis['total_customers']['month'] }}</div>
                                                <div class="cust-stat-lbl">Month</div>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="cust-mini-stat total">
                                                <div class="cust-stat-num">{{ $kpis['total_customers']['value'] }}</div>
                                                <div class="cust-stat-lbl">Total</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Laundry Pipeline --}}
                        <div class="col-12">
                            <div class="modern-card shadow-sm h-100">
                                <div class="card-header-modern bg-transparent border-0">
                                    <h6 class="mb-0 fw-800 text-slate-800">Laundry Pipeline</h6>
                                    <small class="text-muted">Current status distribution</small>
                                </div>
                                <div class="card-body-modern">
                                    @php
                                        $pipelineTotal = max(array_sum($pipeline), 1);
                                        $pipelineItems = [
                                            'received'  => ['label'=>'Received',  'color'=>'#3b82f6'],
                                            'processing'=> ['label'=>'Processing','color'=>'#8b5cf6'],
                                            'ready'     => ['label'=>'Ready',     'color'=>'#f59e0b'],
                                            'paid'      => ['label'=>'Paid',      'color'=>'#10b981'],
                                            'completed' => ['label'=>'Done Today','color'=>'#6366f1'],
                                        ];
                                    @endphp
                                    @foreach($pipelineItems as $key => $item)
                                        <div class="pipeline-item-modern mb-2">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <div class="d-flex align-items-center">
                                                    <span class="status-dot-modern" style="background:{{ $item['color'] }};"></span>
                                                    <span class="pipeline-label-modern">{{ $item['label'] }}</span>
                                                </div>
                                                <span class="pipeline-value-modern">{{ $pipeline[$key] ?? 0 }}</span>
                                            </div>
                                            <div class="progress-bar-modern">
                                                <div class="progress-fill-modern" style="width:{{ min(100, round(($pipeline[$key] ?? 0) / $pipelineTotal * 100)) }}%;background:{{ $item['color'] }};"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Map & Pickups --}}
                <div class="col-lg-5">
                    <div class="modern-card shadow-sm h-100">
                        <div class="card-header-modern bg-transparent border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-800 text-slate-800">Pickup Management</h6>
                                <small class="text-muted">Select pickups for optimized routing</small>
                            </div>
                            <span id="selectedPickupCount" class="badge bg-purple" style="display:none;">0</span>
                        </div>
                        <div class="card-body-modern">
                            <div id="multiRouteBtn" class="d-grid mb-3" style="display:none!important;">
                                <button class="btn btn-purple shadow-sm" onclick="window.getOptimizedMultiRoute()">
                                    <i class="bi bi-route me-2"></i>Optimize Route (<span id="selectedCount">0</span>)
                                </button>
                            </div>
                            <div class="d-grid mb-3">
                                <button class="btn btn-primary btn-sm" onclick="window.autoRouteAllVisible()">
                                    <i class="bi bi-magic me-2"></i>Auto-Optimize All Pending
                                </button>
                            </div>
                            <div class="d-flex gap-2 mb-3">
                                <button class="btn btn-sm btn-outline-purple flex-fill" onclick="window.selectAllPending()">
                                    <i class="bi bi-check-square me-1"></i>Select All
                                </button>
                                <button class="btn btn-sm btn-outline-danger flex-fill" onclick="window.clearSelections()">
                                    <i class="bi bi-x-circle me-1"></i>Clear
                                </button>
                            </div>
                            <h6 class="mb-2 fw-700 text-slate-600" style="font-size:.8rem;">Active Pickups</h6>
                            <div style="max-height:260px;overflow-y:auto;" id="pickupListContainer">
                                @forelse($pickupLocations as $pickup)
                                    <label class="pickup-list-item d-flex align-items-center gap-2 p-2 mb-2 rounded border" for="chk-{{ $pickup->id }}" style="cursor:pointer;font-size:.82rem;">
                                        <input type="checkbox" class="form-check-input pickup-checkbox m-0" id="chk-{{ $pickup->id }}" onclick="window.togglePickupSelection({{ $pickup->id }})">
                                        <div class="flex-grow-1">
                                            <div class="fw-600">{{ $pickup->customer->name ?? 'Customer' }}</div>
                                            <small class="text-muted">{{ $pickup->customer->phone ?? '' }}</small>
                                        </div>
                                        <span class="badge bg-{{ $pickup->status === 'en_route' ? 'warning' : 'danger' }}" style="font-size:.65rem;">{{ ucfirst(str_replace('_',' ',$pickup->status)) }}</span>
                                    </label>
                                @empty
                                    <div class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size:2rem;"></i>
                                        <p class="mt-2 mb-0" style="font-size:.85rem;">No active pickup requests</p>
                                    </div>
                                @endforelse
                            </div>
                            <hr class="my-3">
                            <div class="d-flex justify-content-between text-center" style="font-size:.75rem;">
                                <div><div class="fw-800 fs-5">{{ $pickups['pending'] ?? 0 }}</div><small class="text-muted">Pending</small></div>
                                <div><div class="fw-800 fs-5">{{ $pickups['en_route'] ?? 0 }}</div><small class="text-muted">En Route</small></div>
                                <div><div class="fw-800 fs-5">{{ $pickups['completed_today'] ?? 0 }}</div><small class="text-muted">Done</small></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="modern-card shadow-sm h-100">
                        <div class="card-header-modern bg-transparent border-0 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-800 text-slate-800">Logistics Map</h6>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary" onclick="window.refreshMapMarkers()"><i class="bi bi-arrow-clockwise"></i></button>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mapModal"><i class="bi bi-arrows-fullscreen"></i></button>
                            </div>
                        </div>
                        <div class="card-body-modern p-0 position-relative">
                            <div id="address-search-overlay" style="position:absolute;top:12px;right:12px;z-index:1000;max-width:300px;">
                                <div class="card shadow-lg border-0" style="border-radius:10px;">
                                    <div class="card-body p-2">
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="map-address-search" class="form-control" placeholder="Search address..." style="font-size:12px;">
                                            <button class="btn btn-primary" onclick="window.searchMapAddress()"><i class="bi bi-geo-alt-fill"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="branchMap" class="branch-map-container"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             REVENUE TAB
        ============================================================ --}}
        <div class="tab-pane fade" id="revenue" role="tabpanel">
            <div class="row g-4">
                {{-- Revenue Summary Cards --}}
                <div class="col-12">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="rev-metric">
                                <div class="rev-metric-icon" style="background:#eff6ff;"><i class="bi bi-cash-coin" style="color:#2563eb;font-size:1.1rem;"></i></div>
                                <div>
                                    <div class="rev-metric-val">₱{{ number_format($revenue['total'] ?? 0, 2) }}</div>
                                    <div class="rev-metric-lbl">TOTAL REVENUE</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="rev-metric">
                                <div class="rev-metric-icon" style="background:#f0fdf4;"><i class="bi bi-calculator" style="color:#10b981;font-size:1.1rem;"></i></div>
                                <div>
                                    <div class="rev-metric-val">₱{{ number_format($revenue['average'] ?? 0, 2) }}</div>
                                    <div class="rev-metric-lbl">AVG ORDER VALUE</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="rev-metric">
                                <div class="rev-metric-icon" style="background:#faf5ff;"><i class="bi bi-trophy" style="color:#8b5cf6;font-size:1.1rem;"></i></div>
                                <div>
                                    <div class="rev-metric-val">₱{{ number_format($revenue['highest'] ?? 0, 2) }}</div>
                                    <div class="rev-metric-lbl">HIGHEST ORDER</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="rev-metric">
                                <div class="rev-metric-icon" style="background:#ecfeff;"><i class="bi bi-graph-up" style="color:#0891b2;font-size:1.1rem;"></i></div>
                                <div>
                                    <div class="rev-metric-val">₱{{ number_format($revenue['per_day'] ?? 0, 2) }}</div>
                                    <div class="rev-metric-lbl">AVG PER DAY</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Revenue Detail Chart --}}
                <div class="col-lg-8">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <div>
                                <h6>Revenue Trend (Detailed)</h6>
                                <small>Daily revenue for selected period</small>
                            </div>
                        </div>
                        <div class="chart-card-body">
                            <div class="cw-lg">
                                <canvas id="revenueDetailChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Monthly Trend --}}
                <div class="col-lg-4">
                    <div class="chart-card h-100">
                        <div class="chart-card-header">
                            <div>
                                <h6>Monthly Trend</h6>
                                <small>Last 6 months performance</small>
                            </div>
                        </div>
                        <div class="chart-card-body">
                            <div class="cw-micro">
                                <canvas id="monthlyTrendChart"></canvas>
                            </div>
                            <div class="mt-3">
                                @foreach($monthlyTrend as $month)
                                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:.78rem;">
                                        <span class="text-muted">{{ $month['short_month'] }}</span>
                                        <div class="flex-grow-1 mx-2">
                                            <div style="height:4px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
                                                @php $maxRev = max(collect($monthlyTrend)->pluck('revenue')->toArray()) ?: 1; @endphp
                                                <div style="height:100%;width:{{ min(100, round($month['revenue']/$maxRev*100)) }}%;background:linear-gradient(90deg,#3D3B6B,#7C78C8);border-radius:99px;"></div>
                                            </div>
                                        </div>
                                        <span class="fw-700">₱{{ number_format($month['revenue']/1000, 1) }}k</span>
                                        @if($month['growth'] != 0)
                                            <span class="ms-1 {{ $month['growth'] > 0 ? 'text-success' : 'text-danger' }}" style="font-size:.65rem;">
                                                {{ $month['growth'] > 0 ? '+' : '' }}{{ $month['growth'] }}%
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Daily Order Count --}}
                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <div>
                                <h6>Daily Order Count</h6>
                                <small>Laundries per day for selected period</small>
                            </div>
                        </div>
                        <div class="chart-card-body">
                            <div class="cw-xs">
                                <canvas id="dailyCountChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Weekly Performance --}}
                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <div>
                                <h6>This Week's Performance</h6>
                                <small>Revenue & order count by day</small>
                            </div>
                        </div>
                        <div class="chart-card-body">
                            <div class="cw-xs">
                                <canvas id="weeklyPerfChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment Methods + Top Services --}}
                <div class="col-lg-5">
                    <div class="chart-card h-100">
                        <div class="chart-card-header">
                            <div>
                                <h6>Payment Methods</h6>
                                <small>Distribution by payment type</small>
                            </div>
                        </div>
                        <div class="chart-card-body">
                            <div class="cw-mini">
                                <canvas id="paymentMethodChart"></canvas>
                            </div>
                            <div class="donut-legend mt-3" id="paymentLegend"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="chart-card h-100">
                        <div class="chart-card-header">
                            <div>
                                <h6>Top Services</h6>
                                <small>Most used services this period</small>
                            </div>
                        </div>
                        <div class="chart-card-body">
                            @foreach($topServices as $i => $svc)
                                <div class="svc-pill">
                                    <div class="svc-pill-icon" style="background:{{ ['#eff6ff','#f0fdf4','#faf5ff','#ffedd5','#ecfeff'][$i % 5] }};color:{{ ['#2563eb','#10b981','#8b5cf6','#f97316','#0891b2'][$i % 5] }}">
                                        <i class="bi bi-droplet-fill"></i>
                                    </div>
                                    <span class="svc-pill-name">{{ $svc->name }}</span>
                                    <div class="svc-pill-track">
                                        <div class="svc-pill-fill" style="width:{{ $topServices->max('count') > 0 ? round($svc->count/$topServices->max('count')*100) : 0 }}%;background:{{ ['#2563eb','#10b981','#8b5cf6','#f97316','#0891b2'][$i % 5] }}"></div>
                                    </div>
                                    <span class="svc-pill-count">{{ $svc->count }}</span>
                                    <span class="text-muted" style="font-size:.75rem;min-width:60px;text-align:right;">₱{{ number_format($svc->revenue/1000, 1) }}k</span>
                                </div>
                            @endforeach
                            @if($topServices->isEmpty())
                                <div class="text-center text-muted py-4"><i class="bi bi-inbox fs-1"></i><p class="mt-2">No service data</p></div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Yearly Trend --}}
                <div class="col-12">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <div>
                                <h6>Year-over-Year Revenue</h6>
                                <small>Last 5 years comparison</small>
                            </div>
                        </div>
                        <div class="chart-card-body">
                            <div class="cw-xs">
                                <canvas id="yearlyTrendChart"></canvas>
                            </div>
                            <div class="row mt-3 g-3">
                                @foreach($yearlyTrend as $yr)
                                    <div class="col">
                                        <div class="text-center p-2 rounded" style="background:var(--slate-50);">
                                            <div class="fw-800" style="font-size:1rem;">₱{{ number_format($yr['revenue']/1000, 1) }}k</div>
                                            <div class="text-muted" style="font-size:.7rem;">{{ $yr['year'] }}</div>
                                            @if($yr['growth'] != 0)
                                                <div class="{{ $yr['growth'] > 0 ? 'text-success' : 'text-danger' }}" style="font-size:.65rem;">
                                                    {{ $yr['growth'] > 0 ? '+' : '' }}{{ $yr['growth'] }}%
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             BRANCHES TAB
        ============================================================ --}}
        <div class="tab-pane fade" id="branches" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <div>
                                <h6>Branch Revenue Comparison</h6>
                                <small>All branches — completed revenue this period</small>
                            </div>
                        </div>
                        <div class="chart-card-body">
                            <div class="cw-xl">
                                <canvas id="allBranchRevenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="chart-card h-100">
                        <div class="chart-card-header">
                            <div>
                                <h6>Revenue Share</h6>
                                <small>Percentage of total revenue</small>
                            </div>
                        </div>
                        <div class="chart-card-body">
                            <div class="cw-mini">
                                <canvas id="branchShareDonut"></canvas>
                            </div>
                            <div class="donut-legend mt-3" id="branchShareLegend"></div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <div>
                                <h6>Branch Performance Leaderboard</h6>
                                <small>Ranked by revenue contribution</small>
                            </div>
                        </div>
                        <div class="chart-card-body">
                            @php $rank = 1; @endphp
                            @foreach($allBranchesPerformance as $branch)
                                <div class="branch-perf-row d-flex align-items-center gap-3 {{ $branch['id'] == $current_filters['branch_id'] ? 'row-mine' : 'row-other' }} mb-3">
                                    <div style="width:28px;text-align:center;font-weight:800;color:{{ $rank <= 3 ? ['#f59e0b','#94a3b8','#cd7c2e'][$rank-1] : '#94a3b8' }};font-size:{{ $rank <= 3 ? '1.1' : '0.85' }}rem;">
                                        {{ $rank <= 3 ? ['🥇','🥈','🥉'][$rank-1] : "#$rank" }}
                                    </div>
                                    <div class="branch-perf-name">
                                        {{ $branch['name'] }}
                                        @if($branch['id'] == $current_filters['branch_id'])
                                            <span class="badge bg-primary" style="font-size:.55rem;margin-left:4px;">YOURS</span>
                                        @endif
                                    </div>
                                    <div class="branch-perf-track flex-grow-1">
                                        <div class="branch-perf-fill" style="width:{{ $branch['percentage'] }}%;"></div>
                                    </div>
                                    <div class="branch-perf-val">₱{{ number_format($branch['revenue']/1000, 1) }}k</div>
                                    <div class="branch-perf-pct">{{ $branch['percentage'] }}%</div>
                                    <div class="branch-perf-cnt">
                                        <span class="badge bg-light text-dark" style="font-size:.65rem;">{{ $branch['laundries_count'] }} laundries</span>
                                    </div>
                                </div>
                                @php $rank++; @endphp
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <div>
                                <h6>Order Volume Comparison</h6>
                                <small>Total laundries by branch</small>
                            </div>
                        </div>
                        <div class="chart-card-body">
                            <div class="cw-sm">
                                <canvas id="branchOrderVolumeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <div>
                                <h6>Average Order Value</h6>
                                <small>Revenue per transaction by branch</small>
                            </div>
                        </div>
                        <div class="chart-card-body">
                            <div class="cw-sm">
                                <canvas id="branchAvgOrderChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             LAUNDRIES TAB
        ============================================================ --}}
        <div class="tab-pane fade" id="laundries" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="chart-card h-100">
                        <div class="chart-card-header">
                            <h6>Status Distribution</h6>
                            <small>All laundry statuses</small>
                        </div>
                        <div class="chart-card-body">
                            <div class="cw-sm">
                                <canvas id="laundryStatusDonut"></canvas>
                            </div>
                            <div class="donut-legend mt-3" id="laundryStatusLegend"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="chart-card h-100">
                        <div class="chart-card-header">
                            <h6>Service Breakdown</h6>
                            <small>Laundries by service type</small>
                        </div>
                        <div class="chart-card-body">
                            <div class="cw-sm">
                                <canvas id="serviceDistributionChart"></canvas>
                            </div>
                            <div class="donut-legend mt-3" id="serviceLegend"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="chart-card h-100">
                        <div class="chart-card-header">
                            <h6>Weight Distribution</h6>
                            <small>Load size breakdown</small>
                        </div>
                        <div class="chart-card-body">
                            <div class="cw-sm">
                                <canvas id="weightDistributionChart"></canvas>
                            </div>
                            <div class="stat-strip mt-3">
                                <div class="stat-strip-item">
                                    <div class="sv">{{ number_format($revenue['total_weight'] ?? 0, 1) }}kg</div>
                                    <div class="sl">TOTAL WEIGHT</div>
                                </div>
                                <div class="stat-strip-item">
                                    <div class="sv">{{ number_format(($revenue['total_weight'] ?? 0) / max($revenue['laundries'] ?? 1, 1), 1) }}kg</div>
                                    <div class="sl">AVG/ORDER</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h6>Hourly Order Distribution</h6>
                            <small>Peak hours analysis</small>
                        </div>
                        <div class="chart-card-body">
                            <div class="cw-xs">
                                <canvas id="hourlyDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h6>Weekday Distribution</h6>
                            <small>Busiest days of the week</small>
                        </div>
                        <div class="chart-card-body">
                            <div class="cw-xs">
                                <canvas id="weekdayDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recent Laundries Table --}}
                <div class="col-12">
                    <div class="modern-card shadow-sm">
                        <div class="card-header-modern bg-transparent border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-800 text-slate-800">Recent Laundries</h6>
                                <small class="text-muted">Latest laundry activities</small>
                            </div>
                            <a href="{{ route('staff.laundries.index') }}" class="btn btn-sm btn-outline-primary">
                                View All <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body-modern">
                            <div class="table-responsive">
                                <table class="table table-modern tbl-laundry">
                                    <thead>
                                        <tr>
                                            <th>Tracking #</th>
                                            <th>Customer</th>
                                            <th>Service</th>
                                            <th>Weight</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recent_laundries as $laundry)
                                            <tr>
                                                <td><span class="tracking-mono">{{ $laundry->tracking_number }}</span></td>
                                                <td><span class="customer-name-modern">{{ $laundry->customer->name ?? 'N/A' }}</span></td>
                                                <td>
                                                    @if($laundry->service)
                                                        {{ $laundry->service->name }}
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>{{ number_format($laundry->weight, 2) }} kg</td>
                                                <td><strong class="amount-modern">₱{{ number_format($laundry->total_amount, 2) }}</strong></td>
                                                <td>
                                                    <span class="status-pill sp-{{ $laundry->status }}">
                                                        <i class="bi bi-circle-fill" style="font-size:.4rem;"></i>
                                                        {{ ucfirst($laundry->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="date-text-modern">{{ $laundry->created_at->format('M d, Y') }}</span>
                                                    <span class="time-text-modern">{{ $laundry->created_at->format('h:i A') }}</span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons-modern d-flex gap-1">
                                                        <a href="{{ route('staff.laundries.show', $laundry->id) }}" class="btn-icon-modern"><i class="bi bi-eye"></i></a>
                                                        <a href="{{ route('staff.laundries.edit', $laundry->id) }}" class="btn-icon-modern"><i class="bi bi-pencil"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="8" class="text-center py-5"><div class="empty-state-modern"><i class="bi bi-inbox fs-1 text-muted mb-3"></i><p class="text-muted">No laundries found</p></div></td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             CUSTOMERS TAB
        ============================================================ --}}
        <div class="tab-pane fade" id="customers-tab" role="tabpanel">
            <div class="row g-4">
                <div class="col-12">
                    <div class="row g-3">
                        @foreach([
                            ['label'=>'Total Customers','value'=>$customers['total'],'icon'=>'people-fill','color'=>'blue','bg'=>'#eff6ff'],
                            ['label'=>'Active This Period','value'=>$customers['active'],'icon'=>'person-check-fill','color'=>'green','bg'=>'#f0fdf4'],
                            ['label'=>'New This Period','value'=>$customers['new'],'icon'=>'person-plus-fill','color'=>'purple','bg'=>'#faf5ff'],
                            ['label'=>'New Customers Today','value'=>$kpis['total_customers']['today'],'icon'=>'person-badge-fill','color'=>'cyan','bg'=>'#ecfeff'],
                        ] as $cst)
                            <div class="col-6 col-md-3">
                                <div class="kpi-card-modern shadow-sm">
                                    <div class="kpi-icon-glow icon-{{ $cst['color'] }}"><i class="bi bi-{{ $cst['icon'] }}"></i></div>
                                    <div class="mt-3">
                                        <span class="kpi-label">{{ $cst['label'] }}</span>
                                        <h2 class="kpi-value">{{ number_format($cst['value']) }}</h2>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h6>Customer Growth Overview</h6>
                            <small>New vs active customers over time</small>
                        </div>
                        <div class="chart-card-body">
                            <div class="cw-md">
                                <canvas id="customerGrowthChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="chart-card h-100">
                        <div class="chart-card-header">
                            <h6>Top 5 Customers</h6>
                            <small>By total spending this period</small>
                        </div>
                        <div class="chart-card-body">
                            @foreach($customers['top_customers'] as $i => $tc)
                                @if($tc->customer)
                                <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded" style="background:var(--slate-50);">
                                    <div class="top-cust-rank" style="width:24px;height:24px;border-radius:50%;background:{{ ['#f59e0b','#94a3b8','#cd7c2e','#3b82f6','#10b981'][$i % 5] }};color:white;font-size:.7rem;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $i + 1 }}</div>
                                    <div class="flex-grow-1" style="min-width:0;">
                                        <div class="fw-700 text-slate-800" style="font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $tc->customer->name ?? 'N/A' }}</div>
                                        <div class="text-muted" style="font-size:.7rem;">{{ $tc->laundry_count }} laundries</div>
                                    </div>
                                    <div class="fw-800 text-success" style="font-size:.85rem;white-space:nowrap;">₱{{ number_format($tc->total_spent, 0) }}</div>
                                </div>
                                @endif
                            @endforeach
                            @if($customers['top_customers']->isEmpty())
                                <div class="text-center text-muted py-4"><i class="bi bi-inbox fs-1"></i><p class="mt-2">No data</p></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MAP MODAL --}}
    <div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header border-bottom shadow-sm bg-navy text-white py-2">
                    <h5 class="modal-title fw-bold"><i class="bi bi-map me-2"></i>Logistics Map</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 position-relative">
                    <div id="modalBranchMap" style="height:100%;width:100%;"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="routeDetailsPanel" class="route-details-panel" style="display:none;"></div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/chart.js/chart.umd.min.js') }}"></script>
    <script src="{{ asset('assets/jquery/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('assets/leaflet/leaflet.js') }}"></script>
    <script src="{{ asset('assets/leaflet/leaflet.markercluster.js') }}"></script>

    <script>
        window.REVENUE_TREND_DATA   = @json($charts['revenue_trend'] ?? []);
        window.DAILY_COUNT_DATA     = @json($charts['daily_count'] ?? []);
        window.SERVICE_DATA         = @json(collect($charts['service_distribution'] ?? [])->mapWithKeys(fn($s) => [$s->name ?? $s['name'] ?? '' => ['count' => $s->count ?? $s['count'] ?? 0, 'revenue' => $s->revenue ?? $s['revenue'] ?? 0]]));
        window.STATUS_BREAKDOWN     = @json($charts['status_breakdown'] ?? []);
        window.HOURLY_DATA          = @json($charts['hourly_distribution'] ?? []);
        window.WEEKDAY_DATA         = @json($charts['weekday_distribution'] ?? []);
        window.PAYMENT_METHODS      = @json($charts['payment_methods'] ?? []);
        window.WEIGHT_DIST          = @json($charts['weight_distribution'] ?? []);
        window.WEEKLY_PERF          = @json($weeklyPerformance ?? []);
        window.MONTHLY_TREND        = @json($monthlyTrend ?? []);
        window.YEARLY_TREND         = @json($yearlyTrend ?? []);
        window.ALL_BRANCHES_PERF    = @json($allBranchesPerformance ?? []);
        window.MY_BRANCH_ID         = {{ $current_filters['branch_id'] ?? 'null' }};
    </script>

    <script src="{{ asset('assets/js/staff.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.initializeDashboardData === 'function') {
                window.initializeDashboardData(
                    @json($allBranches ?? []),
                    @json($pickupLocations ?? []),
                    {{ $current_filters['branch_id'] ?? 'null' }}
                );
            }
        });
    </script>
@endpush
