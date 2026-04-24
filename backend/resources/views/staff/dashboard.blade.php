@extends('staff.layouts.staff')

@section('page-title', 'Dashboard Overview')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/leaflet/leaflet.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/leaflet/MarkerCluster.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/leaflet/MarkerCluster.Default.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/staff.css') }}">
@endpush

@section('content')
<div class="container-fluid px-0 py-0 dashboard-modern-wrapper">

    {{-- ============================================================
         HEADER
    ============================================================ --}}
    <div class="glass-header mb-4 position-relative overflow-hidden">
        <div class="header-gradient-bg"></div>
        <div class="row align-items-center position-relative">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="welcome-avatar">
                        <i class="bi bi-person-circle text-white"></i>
                    </div>
                    <div>
                        <h2 class="fw-bold dashboard-title mb-1">
                            Welcome back, {{ auth()->user()->name }}! 👋
                        </h2>
                        <p class="text-white-50 small mb-0 d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge-status-live"><span class="pulse-dot"></span>LIVE</span>
                            <span class="v-divider"></span>
                            <i class="bi bi-calendar-check" style="color:rgba(255,255,255,0.6);"></i>
                            <span id="current-date" class="fw-600" style="color:rgba(255,255,255,0.85);">{{ now()->format('l, F j, Y') }}</span>
                            <span class="v-divider"></span>
                            <span style="color:#6ee7b7;font-weight:700;font-size:.72rem;">
                                <i class="bi bi-arrow-repeat me-1"></i>Live Sync
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0">
                <div class="d-flex gap-2 justify-content-lg-end align-items-center flex-wrap">
                    <button onclick="window.print()" class="btn btn-sm rounded-pill btn-outline-light d-flex align-items-center gap-1" style="font-size:.8rem;">
                        <i class="bi bi-printer"></i><span>Print</span>
                    </button>
                    <a href="{{ route('staff.dashboard.export') }}" class="btn btn-sm rounded-pill d-flex align-items-center gap-1" style="background:rgba(255,255,255,0.15);color:white;border:1px solid rgba(255,255,255,0.25);font-size:.8rem;backdrop-filter:blur(6px);">
                        <i class="bi bi-download"></i><span>Export</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         SERVICES CAROUSEL
    ============================================================ --}}
    <div class="services-carousel-container mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-grid-3x3-gap-fill me-2"></i>Our Services
                </h5>
                <p class="mb-0" style="font-size:.78rem;color:var(--text-3);">Quick access to all laundry services and add-ons</p>
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
                <div class="service-card-carousel"
                     onclick="window.location.href='{{ route('staff.services.show', $service) }}'"
                     @if($service->icon_path || $service->image_url)
                         style="background: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.75)), url('{{ $service->icon_path ? asset('storage/' . $service->icon_path) : $service->image_url }}') center/cover;"
                     @endif>
                    <span class="service-badge {{ $service->pricing_type === 'per_load' ? 'per-load' : 'per-kg' }}"
                          @if($service->icon_path || $service->image_url) style="background:rgba(255,255,255,0.2);color:white;" @endif>
                        {{ $service->pricing_type === 'per_load' ? 'Per Load' : 'Per Kg' }}
                    </span>
                    <div class="service-icon-wrapper" @if($service->icon_path || $service->image_url) style="background:rgba(255,255,255,0.15);" @endif>
                        <i class="bi bi-droplet"></i>
                    </div>
                    <h6 class="fw-bold mb-1" @if($service->icon_path || $service->image_url) style="color:white;" @endif>{{ Str::limit($service->name, 20) }}</h6>
                    <div class="service-price" @if($service->icon_path || $service->image_url) style="color:rgba(255,255,255,0.9);" @endif>
                        @if($service->pricing_type === 'per_load')
                            ₱{{ number_format($service->price_per_load, 2) }}/load
                        @else
                            ₱{{ number_format($service->price_per_piece, 2) }}/pc
                        @endif
                    </div>
                    <div class="service-description" @if($service->icon_path || $service->image_url) style="color:rgba(255,255,255,0.75);" @endif>
                        {{ $service->description ?? 'No description' }}
                    </div>
                    <div class="service-stats">
                        <div class="service-stat-item">
                            <div class="service-stat-value" @if($service->icon_path || $service->image_url) style="color:white;" @endif>{{ $service->laundries_count ?? 0 }}</div>
                            <div @if($service->icon_path || $service->image_url) style="color:rgba(255,255,255,0.65);" @else class="text-muted" @endif style="font-size:.65rem;">Used</div>
                        </div>
                        <div class="service-stat-item">
                            <div class="service-stat-value" @if($service->icon_path || $service->image_url) style="color:white;" @endif>{{ $service->turnaround_time }}h</div>
                            <div @if($service->icon_path || $service->image_url) style="color:rgba(255,255,255,0.65);" @else class="text-muted" @endif style="font-size:.65rem;">TAT</div>
                        </div>
                    </div>
                </div>
            @endforeach

            @foreach($addons as $addon)
                <div class="service-card-carousel" onclick="window.location.href='{{ route('staff.addons.show', $addon) }}'">
                    <span class="service-badge service-badge-addon">Add-on</span>
                    <div class="service-icon-wrapper service-icon-addon"><i class="bi bi-plus-circle"></i></div>
                    <h6 class="fw-bold mb-1">{{ Str::limit($addon->name, 20) }}</h6>
                    <div class="service-price" style="color:var(--green);">₱{{ number_format($addon->price, 2) }}</div>
                    <div class="service-description">{{ $addon->description ?? 'No description' }}</div>
                    <div class="service-stats">
                        <div class="service-stat-item">
                            <div class="service-stat-value">{{ $addon->laundries_count ?? 0 }}</div>
                            <div class="text-muted" style="font-size:.65rem;">Used</div>
                        </div>
                        <div class="service-stat-item">
                            <div class="service-stat-value" style="color:var(--green);"><i class="bi bi-plus"></i></div>
                            <div class="text-muted" style="font-size:.65rem;">Add-on</div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="service-card-carousel service-card-view-all" onclick="window.location.href='{{ route('staff.services.index') }}'">
                <div class="service-icon-wrapper service-icon-view-all"><i class="bi bi-arrow-right"></i></div>
                <h6 class="fw-bold mb-1 text-center">View All</h6>
                <div class="service-description text-center">Browse all services & add-ons</div>
                <div class="text-center mt-3"><span class="badge" style="background:var(--brand);color:white;font-size:.72rem;">View All →</span></div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         FILTERS
    ============================================================ --}}
    <div class="modern-card mb-4">
        <div class="card-body-modern">
            <form method="GET" action="{{ route('staff.dashboard') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-600"><i class="bi bi-calendar me-1"></i>Date Range</label>
                    <select name="date_range" class="modern-select" onchange="this.form.submit()">
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
                    <select name="branch_id" class="modern-select" disabled>
                        @foreach($branchOptions as $branch)
                            <option value="{{ $branch->id }}" selected>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('staff.dashboard') }}" class="btn btn-sm w-100 d-flex align-items-center justify-content-center gap-1" style="height:40px;border-radius:var(--r-sm);background:var(--surface-2);border:1px solid var(--border);color:var(--text-2);font-weight:600;font-size:.825rem;">
                        <i class="bi bi-arrow-clockwise"></i> Reset Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================
         ALERT CARDS — 2×2 Grid
    ============================================================ --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="alert-card-modern pickup-pending">
                <div class="alert-icon-modern"><i class="bi bi-truck"></i></div>
                <div class="alert-content">
                    <h3 class="alert-number">{{ $pickups['pending'] ?? 0 }}</h3>
                    <h6 class="alert-title">Pending Pickup{{ ($pickups['pending'] ?? 0) != 1 ? 's' : '' }}</h6>
                    <p class="alert-desc">Awaiting collection</p>
                </div>
                <div class="alert-action">
                    <a href="{{ route('staff.pickups.index') }}" class="btn btn-sm" style="border-radius:var(--r-sm);background:var(--blue-soft);color:var(--blue);border:1px solid rgba(37,99,235,0.2);font-size:.78rem;font-weight:600;">
                        View <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="alert-card-modern laundry-ready">
                <div class="alert-icon-modern"><i class="bi bi-bag-check"></i></div>
                <div class="alert-content">
                    <h3 class="alert-number">{{ $kpis['ready_for_pickup']['value'] ?? 0 }}</h3>
                    <h6 class="alert-title">Ready for Pickup</h6>
                    <p class="alert-desc">Ready for collection</p>
                </div>
                <div class="alert-action">
                    <a href="{{ route('staff.laundries.index', ['status' => 'ready']) }}" class="btn btn-sm" style="border-radius:var(--r-sm);background:var(--warning-soft);color:var(--warning);border:1px solid rgba(217,119,6,0.25);font-size:.78rem;font-weight:600;">
                        View <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="alert-card-modern unclaimed-alert">
                <div class="alert-icon-modern"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="alert-content">
                    <h3 class="alert-number">{{ $unclaimed['total_count'] ?? 0 }}</h3>
                    <h6 class="alert-title">Unclaimed Laundries</h6>
                    <p class="alert-desc">Require attention</p>
                </div>
                <div class="alert-action">
                    <a href="{{ route('staff.unclaimed.index') }}" class="btn btn-sm" style="border-radius:var(--r-sm);background:var(--danger-soft);color:var(--danger);border:1px solid rgba(220,38,38,0.2);font-size:.78rem;font-weight:600;">
                        View <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="alert-card-modern revenue-risk">
                <div class="alert-icon-modern"><i class="bi bi-currency-dollar"></i></div>
                <div class="alert-content">
                    <h3 class="alert-number">₱{{ number_format($unclaimed['total_value'] ?? 0, 0) }}</h3>
                    <h6 class="alert-title">Revenue at Risk</h6>
                    <p class="alert-desc">From unclaimed items</p>
                </div>
                <div class="alert-action">
                    <a href="{{ route('staff.unclaimed.index') }}" class="btn btn-sm" style="border-radius:var(--r-sm);background:var(--purple-soft);color:var(--purple);border:1px solid rgba(124,58,237,0.2);font-size:.78rem;font-weight:600;">
                        View <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         KPI ROW 1 — Revenue
    ============================================================ --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card-modern">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-glow icon-blue"><i class="bi bi-cash-coin"></i></div>
                    <span class="kpi-trend {{ $kpis['today_revenue']['change'] >= 0 ? 'up' : 'down' }}">
                        <i class="bi bi-arrow-{{ $kpis['today_revenue']['change'] >= 0 ? 'up' : 'down' }}"></i>
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

        <div class="col-6 col-md-3">
            <div class="kpi-card-modern">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-glow icon-indigo"><i class="bi bi-bar-chart"></i></div>
                    <span class="kpi-trend {{ $kpis['weekly_revenue']['change'] >= 0 ? 'up' : 'down' }}">
                        <i class="bi bi-arrow-{{ $kpis['weekly_revenue']['change'] >= 0 ? 'up' : 'down' }}"></i>
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

        <div class="col-6 col-md-3">
            <div class="kpi-card-modern kpi-success">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-glow icon-green"><i class="bi bi-graph-up-arrow"></i></div>
                    <span class="kpi-trend {{ $kpis['monthly_revenue']['change'] >= 0 ? 'up' : 'down' }}">
                        <i class="bi bi-arrow-{{ $kpis['monthly_revenue']['change'] >= 0 ? 'up' : 'down' }}"></i>
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

        <div class="col-6 col-md-3">
            <div class="kpi-card-modern">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-glow icon-purple"><i class="bi bi-calendar-range"></i></div>
                    <span class="kpi-trend {{ $kpis['yearly_revenue']['change'] >= 0 ? 'up' : 'down' }}">
                        <i class="bi bi-arrow-{{ $kpis['yearly_revenue']['change'] >= 0 ? 'up' : 'down' }}"></i>
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

    {{-- ============================================================
         KPI ROW 2 — Operations
    ============================================================ --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card-modern">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-glow icon-cyan"><i class="bi bi-people-fill"></i></div>
                    <span class="badge" style="background:var(--green-soft);color:var(--green);font-size:.62rem;font-weight:700;border-radius:999px;padding:.2rem .55rem;">+{{ $kpis['total_customers']['today'] }} today</span>
                </div>
                <div class="mt-3">
                    <span class="kpi-label">Total Customers</span>
                    <h2 class="kpi-value">{{ number_format($kpis['total_customers']['value']) }}</h2>
                    <div class="d-flex gap-2 mt-1">
                        <small class="text-muted">+{{ $kpis['total_customers']['week'] }} wk</small>
                        <small class="text-muted">·</small>
                        <small class="text-muted">+{{ $kpis['total_customers']['month'] }} mo</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="kpi-card-modern">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-glow icon-orange"><i class="bi bi-box-seam"></i></div>
                    <a href="{{ route('staff.laundries.index') }}" style="font-size:.7rem;color:var(--text-3);text-decoration:none;font-weight:600;">View →</a>
                </div>
                <div class="mt-3">
                    <span class="kpi-label">Active Laundries</span>
                    <h2 class="kpi-value">{{ $kpis['active_laundries']['value'] }}</h2>
                    <small class="text-muted">In progress now</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="kpi-card-modern kpi-warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-glow icon-orange"><i class="bi bi-bag-check"></i></div>
                    <a href="{{ route('staff.laundries.index', ['status' => 'ready']) }}" style="font-size:.7rem;color:var(--text-3);text-decoration:none;font-weight:600;">View →</a>
                </div>
                <div class="mt-3">
                    <span class="kpi-label">Ready for Pickup</span>
                    <h2 class="kpi-value">{{ $kpis['ready_for_pickup']['value'] }}</h2>
                    <small class="text-muted">Avg {{ number_format($kpis['ready_for_pickup']['avg_wait_days'], 1) }} days waiting</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="kpi-card-modern kpi-success">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-glow icon-green"><i class="bi bi-check2-all"></i></div>
                    <span class="kpi-trend up"><i class="bi bi-check-circle"></i> Done</span>
                </div>
                <div class="mt-3">
                    <span class="kpi-label">Completed Today</span>
                    <h2 class="kpi-value">{{ $kpis['completed_today']['value'] }}</h2>
                    <small class="text-muted">Successfully delivered</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         QUICK ACTIONS
    ============================================================ --}}
    <div class="modern-card mb-4">
        <div class="card-header-modern">
            <div>
                <h6 class="mb-0 fw-700">Quick Actions</h6>
                <small>Frequently accessed functions</small>
            </div>
            <span class="badge" style="background:var(--blue-soft);color:var(--blue);font-size:.68rem;font-weight:700;padding:.25rem .65rem;border-radius:999px;">
                <i class="bi bi-lightning me-1"></i>Instant Access
            </span>
        </div>
        <div class="card-body-modern">
            <div class="row g-3">
                @php
                    $quickActions = [
                        ['route'=>'staff.laundries.create','icon'=>'bi-plus-lg',    'label'=>'New Laundry', 'desc'=>'Create a laundry','color'=>'blue'],
                        ['route'=>'staff.customers.create','icon'=>'bi-person-plus','label'=>'New Customer','desc'=>'Register',         'color'=>'indigo'],
                        ['route'=>'staff.pickups.index',   'icon'=>'bi-truck',       'label'=>'Pickups',     'desc'=>'Delivery',        'color'=>'cyan'],
                        ['route'=>'staff.laundries.index', 'icon'=>'bi-list-check',  'label'=>'All Laundries','desc'=>'Manage',         'color'=>'green'],
                    ];
                @endphp
                @foreach($quickActions as $action)
                    @if(Route::has($action['route']))
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="{{ route($action['route']) }}" class="launch-action-btn {{ $action['color'] }}">
                                <div class="launch-icon shadow-sm"><i class="bi {{ $action['icon'] }}"></i></div>
                                <h6 class="action-label mb-1">{{ $action['label'] }}</h6>
                                <small class="text-muted" style="font-size:.7rem;">{{ $action['desc'] }}</small>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- ============================================================
         TABS
    ============================================================ --}}
    <div class="modern-tabs-container mb-4">
        <ul class="nav nav-segmented" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#overview" type="button"><i class="bi bi-speedometer2 me-2"></i>Overview</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#revenue" type="button"><i class="bi bi-graph-up me-2"></i>Revenue</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#branches" type="button"><i class="bi bi-shop me-2"></i>Branches</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#laundries" type="button"><i class="bi bi-basket me-2"></i>Laundries</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#customers-tab" type="button"><i class="bi bi-people me-2"></i>Customers</button></li>
        </ul>
    </div>

    <div class="tab-content">

        {{-- ========== OVERVIEW TAB ========== --}}
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row g-4">

                {{-- Revenue Trend --}}
                <div class="col-lg-8">
                    <div class="chart-card h-100">
                        <div class="chart-card-header">
                            <div>
                                <h6>Revenue Trend</h6>
                                <small>{{ ucwords(str_replace('_', ' ', $current_filters['date_range'])) }} performance</small>
                            </div>
                            <button class="btn btn-sm" style="border-radius:var(--r-sm);background:var(--surface-2);border:1px solid var(--border);color:var(--text-3);" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                        <div class="chart-card-body">
                            <div class="chart-container"><canvas id="revenueTrendChart"></canvas></div>
                            <div class="row mt-3 text-center g-0 stat-strip">
                                <div class="col-4 stat-strip-item">
                                    <div class="sv">₱{{ number_format($revenue['per_day'], 0) }}</div>
                                    <div class="sl">Avg/Day</div>
                                </div>
                                <div class="col-4 stat-strip-item">
                                    <div class="sv">₱{{ number_format($revenue['highest'], 0) }}</div>
                                    <div class="sl">Highest Day</div>
                                </div>
                                <div class="col-4 stat-strip-item">
                                    <div class="sv">{{ number_format($revenue['laundries']) }}</div>
                                    <div class="sl">Total Laundries</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pipeline + Customer Today --}}
                <div class="col-lg-4">
                    <div class="row g-4 h-100">
                        <div class="col-12">
                            <div class="modern-card customers-today-card">
                                <div class="card-body-modern">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h6 class="fw-700 mb-0" style="font-size:.875rem;color:var(--text-1);">Customers Today</h6>
                                        <div class="kpi-icon-glow icon-cyan"><i class="bi bi-people-fill"></i></div>
                                    </div>
                                    <div class="row g-2 text-center">
                                        <div class="col-3"><div class="cust-mini-stat today"><div class="cust-stat-num">{{ $kpis['total_customers']['today'] }}</div><div class="cust-stat-lbl">Today</div></div></div>
                                        <div class="col-3"><div class="cust-mini-stat week"><div class="cust-stat-num">{{ $kpis['total_customers']['week'] }}</div><div class="cust-stat-lbl">Week</div></div></div>
                                        <div class="col-3"><div class="cust-mini-stat month"><div class="cust-stat-num">{{ $kpis['total_customers']['month'] }}</div><div class="cust-stat-lbl">Month</div></div></div>
                                        <div class="col-3"><div class="cust-mini-stat total"><div class="cust-stat-num">{{ $kpis['total_customers']['value'] }}</div><div class="cust-stat-lbl">Total</div></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="chart-card h-100">
                                <div class="chart-card-header">
                                    <div>
                                        <h6>Laundry Pipeline</h6>
                                        <small>Current status distribution</small>
                                    </div>
                                </div>
                                <div class="chart-card-body">
                                    @php
                                        $pipelineTotal = max(array_sum($pipeline), 1);
                                        $pipelineItems = [
                                            'received'   => ['label'=>'Received',   'color'=>'#3b82f6'],
                                            'processing' => ['label'=>'Processing', 'color'=>'#8b5cf6'],
                                            'ready'      => ['label'=>'Ready',      'color'=>'#f59e0b'],
                                            'paid'       => ['label'=>'Paid',       'color'=>'#10b981'],
                                            'completed'  => ['label'=>'Done Today', 'color'=>'#6366f1'],
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

                {{-- Pickup Management --}}
                <div class="col-lg-5">
                    <div class="chart-card h-100">
                        <div class="chart-card-header">
                            <div>
                                <h6>Pickup Management</h6>
                                <small>Select pickups for optimized routing</small>
                            </div>
                            <span id="selectedPickupCount" class="badge" style="display:none;background:var(--purple);color:white;border-radius:999px;">0</span>
                        </div>
                        <div class="chart-card-body">
                            <div id="multiRouteBtn" class="d-grid mb-3" style="display:none!important;">
                                <button class="btn btn-purple btn-sm shadow-sm" onclick="window.getOptimizedMultiRoute()">
                                    <i class="bi bi-route me-2"></i>Optimize Route (<span id="selectedCount">0</span>)
                                </button>
                            </div>
                            <div class="d-grid mb-3">
                                <button class="btn btn-sm" style="border-radius:var(--r-sm);background:var(--brand);color:white;border:none;padding:.5rem;font-weight:600;font-size:.8rem;" onclick="window.autoRouteAllVisible()">
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
                            <h6 class="mb-2 fw-700" style="font-size:.75rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.08em;">Active Pickups</h6>
                            <div style="max-height:260px;overflow-y:auto;" id="pickupListContainer">
                                @forelse($pickupLocations as $pickup)
                                    <label class="pickup-list-item d-flex align-items-center gap-2 p-2 mb-1 rounded" for="chk-{{ $pickup->id }}" style="cursor:pointer;font-size:.82rem;">
                                        <input type="checkbox" class="form-check-input pickup-checkbox m-0" id="chk-{{ $pickup->id }}" onclick="window.togglePickupSelection({{ $pickup->id }})">
                                        <div class="flex-grow-1">
                                            <div class="fw-600" style="color:var(--text-1);">{{ $pickup->customer->name ?? 'Customer' }}</div>
                                            <small style="color:var(--text-3);">{{ $pickup->customer->phone ?? '' }}</small>
                                        </div>
                                        <span class="status-pill sp-{{ $pickup->status === 'en_route' ? 'processing' : 'ready' }}" style="font-size:.62rem;">
                                            {{ ucfirst(str_replace('_', ' ', $pickup->status)) }}
                                        </span>
                                    </label>
                                @empty
                                    <div class="text-center py-4" style="color:var(--text-3);">
                                        <i class="bi bi-inbox" style="font-size:1.75rem;display:block;margin-bottom:.5rem;"></i>
                                        <span style="font-size:.82rem;">No active pickup requests</span>
                                    </div>
                                @endforelse
                            </div>
                            <hr style="border-color:var(--border);opacity:.5;margin:1rem 0;">
                            <div class="d-flex justify-content-between text-center" style="font-size:.72rem;">
                                <div>
                                    <div class="fw-800" style="font-size:1.2rem;color:var(--text-1);font-family:'DM Mono',monospace;">{{ $pickups['pending'] ?? 0 }}</div>
                                    <small style="color:var(--text-3);">Pending</small>
                                </div>
                                <div>
                                    <div class="fw-800" style="font-size:1.2rem;color:var(--text-1);font-family:'DM Mono',monospace;">{{ $pickups['en_route'] ?? 0 }}</div>
                                    <small style="color:var(--text-3);">En Route</small>
                                </div>
                                <div>
                                    <div class="fw-800" style="font-size:1.2rem;color:var(--green);font-family:'DM Mono',monospace;">{{ $pickups['completed_today'] ?? 0 }}</div>
                                    <small style="color:var(--text-3);">Done</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Map --}}
                <div class="col-lg-7">
                    <div class="chart-card h-100">
                        <div class="chart-card-header">
                            <h6>Logistics Map</h6>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm" style="border-radius:var(--r-sm);background:var(--surface-2);border:1px solid var(--border);color:var(--text-2);" onclick="window.refreshMapMarkers()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                                <button class="btn btn-sm" style="border-radius:var(--r-sm);background:var(--brand);border:none;color:white;" data-bs-toggle="modal" data-bs-target="#mapModal">
                                    <i class="bi bi-arrows-fullscreen"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body-modern p-0 position-relative">
                            <div id="address-search-overlay" style="position:absolute;top:12px;right:12px;z-index:1000;max-width:280px;">
                                <div class="card border-0 shadow-lg" style="border-radius:var(--r-md);background:var(--surface);">
                                    <div class="card-body p-2">
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="map-address-search" class="form-control" placeholder="Search address..." style="font-size:12px;border-radius:var(--r-sm) 0 0 var(--r-sm);border:1px solid var(--border);background:var(--surface);color:var(--text-1);">
                                            <button class="btn btn-sm" style="background:var(--brand);color:white;border:none;border-radius:0 var(--r-sm) var(--r-sm) 0;" onclick="window.searchMapAddress()">
                                                <i class="bi bi-geo-alt-fill"></i>
                                            </button>
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

        {{-- ========== REVENUE TAB ========== --}}
        <div class="tab-pane fade" id="revenue" role="tabpanel">
            <div class="row g-4">
                <div class="col-12">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="rev-metric">
                                <div class="rev-metric-icon" style="background:#eff6ff;"><i class="bi bi-cash-coin" style="color:#2563eb;font-size:1rem;"></i></div>
                                <div>
                                    <div class="rev-metric-val">₱{{ number_format($revenue['total'] ?? 0, 2) }}</div>
                                    <div class="rev-metric-lbl">Total Revenue</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="rev-metric">
                                <div class="rev-metric-icon" style="background:#f0fdf4;"><i class="bi bi-calculator" style="color:#10b981;font-size:1rem;"></i></div>
                                <div>
                                    <div class="rev-metric-val">₱{{ number_format($revenue['average'] ?? 0, 2) }}</div>
                                    <div class="rev-metric-lbl">Avg Laundry Value</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="rev-metric">
                                <div class="rev-metric-icon" style="background:#faf5ff;"><i class="bi bi-trophy" style="color:#8b5cf6;font-size:1rem;"></i></div>
                                <div>
                                    <div class="rev-metric-val">₱{{ number_format($revenue['highest'] ?? 0, 2) }}</div>
                                    <div class="rev-metric-lbl">Highest Laundry</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="rev-metric">
                                <div class="rev-metric-icon" style="background:#ecfeff;"><i class="bi bi-graph-up" style="color:#0891b2;font-size:1rem;"></i></div>
                                <div>
                                    <div class="rev-metric-val">₱{{ number_format($revenue['per_day'] ?? 0, 2) }}</div>
                                    <div class="rev-metric-lbl">Avg Per Day</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="chart-card">
                        <div class="chart-card-header"><div><h6>Revenue Trend (Detailed)</h6><small>Daily revenue for selected period</small></div></div>
                        <div class="chart-card-body"><div class="cw-lg"><canvas id="revenueDetailChart"></canvas></div></div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="chart-card h-100">
                        <div class="chart-card-header"><div><h6>Monthly Trend</h6><small>Last 6 months</small></div></div>
                        <div class="chart-card-body">
                            <div class="cw-micro"><canvas id="monthlyTrendChart"></canvas></div>
                            <div class="mt-3">
                                @foreach($monthlyTrend as $month)
                                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:.78rem;">
                                        <span style="color:var(--text-3);min-width:30px;">{{ $month['short_month'] }}</span>
                                        <div class="flex-grow-1 mx-2">
                                            <div style="height:4px;background:var(--surface-3);border-radius:99px;overflow:hidden;">
                                                @php $maxRev = max(collect($monthlyTrend)->pluck('revenue')->toArray()) ?: 1; @endphp
                                                <div style="height:100%;width:{{ min(100, round($month['revenue']/$maxRev*100)) }}%;background:linear-gradient(90deg,var(--brand),var(--brand-light));border-radius:99px;"></div>
                                            </div>
                                        </div>
                                        <span class="fw-700" style="color:var(--text-1);font-family:'DM Mono',monospace;">₱{{ number_format($month['revenue']/1000, 1) }}k</span>
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

                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-card-header"><div><h6>Daily Laundry Count</h6><small>Laundries per day</small></div></div>
                        <div class="chart-card-body"><div class="cw-xs"><canvas id="dailyCountChart"></canvas></div></div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-card-header"><div><h6>This Week's Performance</h6><small>Revenue & count by day</small></div></div>
                        <div class="chart-card-body"><div class="cw-xs"><canvas id="weeklyPerfChart"></canvas></div></div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="chart-card h-100">
                        <div class="chart-card-header"><div><h6>Payment Methods</h6><small>Distribution by type</small></div></div>
                        <div class="chart-card-body">
                            <div class="cw-mini"><canvas id="paymentMethodChart"></canvas></div>
                            <div class="donut-legend mt-3" id="paymentLegend"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="chart-card h-100">
                        <div class="chart-card-header"><div><h6>Top Services</h6><small>Most used this period</small></div></div>
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
                                    <span style="font-size:.72rem;color:var(--text-3);min-width:58px;text-align:right;font-family:'DM Mono',monospace;">₱{{ number_format($svc->revenue/1000, 1) }}k</span>
                                </div>
                            @endforeach
                            @if($topServices->isEmpty())
                                <div class="text-center py-4" style="color:var(--text-3);"><i class="bi bi-inbox fs-1"></i><p class="mt-2">No service data</p></div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="chart-card">
                        <div class="chart-card-header"><div><h6>Year-over-Year Revenue</h6><small>Last 5 years</small></div></div>
                        <div class="chart-card-body">
                            <div class="cw-xs"><canvas id="yearlyTrendChart"></canvas></div>
                            <div class="row mt-3 g-3">
                                @foreach($yearlyTrend as $yr)
                                    <div class="col">
                                        <div class="text-center p-2 rounded" style="background:var(--surface-2);">
                                            <div class="fw-800" style="font-size:.95rem;font-family:'DM Mono',monospace;color:var(--text-1);">₱{{ number_format($yr['revenue']/1000, 1) }}k</div>
                                            <div style="font-size:.68rem;color:var(--text-3);">{{ $yr['year'] }}</div>
                                            @if($yr['growth'] != 0)
                                                <div class="{{ $yr['growth'] > 0 ? 'text-success' : 'text-danger' }}" style="font-size:.62rem;">
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

        {{-- ========== BRANCHES TAB ========== --}}
        <div class="tab-pane fade" id="branches" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="chart-card">
                        <div class="chart-card-header"><div><h6>Branch Revenue Comparison</h6><small>All branches — completed revenue this period</small></div></div>
                        <div class="chart-card-body"><div class="cw-xl"><canvas id="allBranchRevenueChart"></canvas></div></div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="chart-card h-100">
                        <div class="chart-card-header"><div><h6>Revenue Share</h6><small>Percentage of total</small></div></div>
                        <div class="chart-card-body">
                            <div class="cw-mini"><canvas id="branchShareDonut"></canvas></div>
                            <div class="donut-legend mt-3" id="branchShareLegend"></div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="chart-card">
                        <div class="chart-card-header"><div><h6>Branch Performance Leaderboard</h6><small>Ranked by revenue</small></div></div>
                        <div class="chart-card-body">
                            @php $rank = 1; @endphp
                            @foreach($allBranchesPerformance as $branch)
                                <div class="branch-perf-row d-flex align-items-center gap-3 {{ $branch['id'] == $current_filters['branch_id'] ? 'row-mine' : 'row-other' }} mb-3">
                                    <div style="width:28px;text-align:center;font-weight:800;color:{{ $rank <= 3 ? ['#f59e0b','#94a3b8','#cd7c2e'][$rank-1] : 'var(--text-4)' }};font-size:{{ $rank <= 3 ? '1.1' : '0.82' }}rem;">
                                        {{ $rank <= 3 ? ['🥇','🥈','🥉'][$rank-1] : "#$rank" }}
                                    </div>
                                    <div class="branch-perf-name">
                                        {{ $branch['name'] }}
                                        @if($branch['id'] == $current_filters['branch_id'])
                                            <span class="badge ms-1" style="background:var(--blue-soft);color:var(--blue);font-size:.52rem;padding:.18rem .45rem;border-radius:999px;">YOURS</span>
                                        @endif
                                    </div>
                                    <div class="branch-perf-track flex-grow-1">
                                        <div class="branch-perf-fill" style="width:{{ $branch['percentage'] }}%;"></div>
                                    </div>
                                    <div class="branch-perf-val">₱{{ number_format($branch['revenue']/1000, 1) }}k</div>
                                    <div class="branch-perf-pct">{{ $branch['percentage'] }}%</div>
                                    <div class="branch-perf-cnt">
                                        <span class="badge" style="background:var(--surface-2);color:var(--text-2);border:1px solid var(--border);font-size:.62rem;">{{ $branch['laundries_count'] }} laundries</span>
                                    </div>
                                </div>
                                @php $rank++; @endphp
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-card-header"><div><h6>Laundry Volume</h6><small>Total by branch</small></div></div>
                        <div class="chart-card-body"><div class="cw-sm"><canvas id="branchLaundryVolumeChart"></canvas></div></div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-card-header"><div><h6>Average Laundry Value</h6><small>Revenue per transaction</small></div></div>
                        <div class="chart-card-body"><div class="cw-sm"><canvas id="branchAvgLaundryValueChart"></canvas></div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========== LAUNDRIES TAB ========== --}}
        <div class="tab-pane fade" id="laundries" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="chart-card h-100">
                        <div class="chart-card-header"><h6>Status Distribution</h6><small>All laundry statuses</small></div>
                        <div class="chart-card-body">
                            <div class="cw-sm"><canvas id="laundryStatusDonut"></canvas></div>
                            <div class="donut-legend mt-3" id="laundryStatusLegend"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="chart-card h-100">
                        <div class="chart-card-header"><h6>Service Breakdown</h6><small>By service type</small></div>
                        <div class="chart-card-body">
                            <div class="cw-sm"><canvas id="serviceDistributionChart"></canvas></div>
                            <div class="donut-legend mt-3" id="serviceLegend"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="chart-card h-100">
                        <div class="chart-card-header"><h6>Weight Distribution</h6><small>Load size breakdown</small></div>
                        <div class="chart-card-body">
                            <div class="cw-sm"><canvas id="weightDistributionChart"></canvas></div>
                            <div class="stat-strip mt-3">
                                <div class="stat-strip-item">
                                    <div class="sv">{{ number_format($revenue['total_weight'] ?? 0, 1) }}kg</div>
                                    <div class="sl">Total Weight</div>
                                </div>
                                <div class="stat-strip-item">
                                    <div class="sv">{{ number_format(($revenue['total_weight'] ?? 0) / max($revenue['laundries'] ?? 1, 1), 1) }}kg</div>
                                    <div class="sl">Avg/Laundry</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-card-header"><div><h6>Hourly Distribution</h6><small>Peak hours analysis</small></div></div>
                        <div class="chart-card-body"><div class="cw-xs"><canvas id="hourlyDistributionChart"></canvas></div></div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-card-header"><div><h6>Weekday Distribution</h6><small>Busiest days</small></div></div>
                        <div class="chart-card-body"><div class="cw-xs"><canvas id="weekdayDistributionChart"></canvas></div></div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="modern-card">
                        <div class="card-header-modern">
                            <div>
                                <h6 class="mb-0 fw-700">Recent Laundries</h6>
                                <small>Latest laundry activities</small>
                            </div>
                            <a href="{{ route('staff.laundries.index') }}" class="btn btn-sm" style="border-radius:var(--r-sm);background:var(--blue-soft);color:var(--blue);border:1px solid rgba(37,99,235,0.2);font-weight:600;font-size:.78rem;">
                                View All <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body-modern">
                            <div class="table-responsive">
                                <table class="table-modern tbl-laundry">
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
                                                <td style="color:var(--text-2);font-size:.82rem;">{{ $laundry->service->name ?? '—' }}</td>
                                                <td style="font-family:'DM Mono',monospace;font-size:.82rem;color:var(--text-2);">{{ number_format($laundry->weight, 2) }} kg</td>
                                                <td><strong class="amount-modern">₱{{ number_format($laundry->total_amount, 2) }}</strong></td>
                                                <td>
                                                    <span class="status-pill sp-{{ $laundry->status }}">
                                                        <i class="bi bi-circle-fill" style="font-size:.35rem;"></i>
                                                        {{ ucfirst($laundry->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="date-text-modern">{{ $laundry->created_at->format('M d, Y') }}</span>
                                                    <span class="time-text-modern">{{ $laundry->created_at->format('h:i A') }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a href="{{ route('staff.laundries.show', $laundry->id) }}" class="btn-icon-modern"><i class="bi bi-eye"></i></a>
                                                        <a href="{{ route('staff.laundries.edit', $laundry->id) }}" class="btn-icon-modern"><i class="bi bi-pencil"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="8" class="text-center py-5">
                                                <div class="empty-state-modern">
                                                    <i class="bi bi-inbox fs-1"></i>
                                                    <p class="mt-2" style="color:var(--text-3);">No laundries found</p>
                                                </div>
                                            </td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========== CUSTOMERS TAB ========== --}}
        <div class="tab-pane fade" id="customers-tab" role="tabpanel">
            <div class="row g-4">
                <div class="col-12">
                    <div class="row g-3">
                        @foreach([
                            ['label'=>'Total Customers',     'value'=>$customers['total'],                   'icon'=>'people-fill',       'color'=>'blue'],
                            ['label'=>'Active This Period',   'value'=>$customers['active'],                  'icon'=>'person-check-fill', 'color'=>'green'],
                            ['label'=>'New This Period',      'value'=>$customers['new'],                     'icon'=>'person-plus-fill',  'color'=>'purple'],
                            ['label'=>'New Customers Today',  'value'=>$kpis['total_customers']['today'],     'icon'=>'person-badge-fill', 'color'=>'cyan'],
                        ] as $cst)
                            <div class="col-6 col-md-3">
                                <div class="kpi-card-modern">
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
                        <div class="chart-card-header"><div><h6>Customer Growth</h6><small>New vs active customers</small></div></div>
                        <div class="chart-card-body"><div class="cw-md"><canvas id="customerGrowthChart"></canvas></div></div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="chart-card h-100">
                        <div class="chart-card-header"><div><h6>Top 5 Customers</h6><small>By total spending</small></div></div>
                        <div class="chart-card-body">
                            @foreach($customers['top_customers'] as $i => $tc)
                                @if($tc->customer)
                                <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded" style="background:var(--surface-2);">
                                    <div style="width:24px;height:24px;border-radius:50%;background:{{ ['#f59e0b','#94a3b8','#cd7c2e','#3b82f6','#10b981'][$i % 5] }};color:white;font-size:.68rem;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $i + 1 }}</div>
                                    <div class="flex-grow-1" style="min-width:0;">
                                        <div class="fw-700" style="font-size:.8rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--text-1);">{{ $tc->customer->name ?? 'N/A' }}</div>
                                        <div style="font-size:.68rem;color:var(--text-3);">{{ $tc->laundry_count }} laundries</div>
                                    </div>
                                    <div class="fw-800" style="font-size:.82rem;white-space:nowrap;color:var(--green);font-family:'DM Mono',monospace;">₱{{ number_format($tc->total_spent, 0) }}</div>
                                </div>
                                @endif
                            @endforeach
                            @if($customers['top_customers']->isEmpty())
                                <div class="text-center py-4" style="color:var(--text-3);"><i class="bi bi-inbox fs-1"></i><p class="mt-2">No data</p></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /.tab-content --}}

    {{-- MAP MODAL --}}
    <div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content" style="background:var(--surface);border:none;">
                <div class="modal-header border-bottom shadow-sm bg-navy text-white py-2" style="background:var(--brand) !important;">
                    <h5 class="modal-title fw-bold" style="font-size:.9rem;"><i class="bi bi-map me-2"></i>Logistics Map</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-warning" id="modalMultiRouteBtn" style="display:none;" onclick="getOptimizedMultiRoute()">
                            <i class="bi bi-route me-1"></i>Optimize (<span id="modalSelectedCount">0</span>)
                        </button>
                        <button class="btn btn-sm btn-info" onclick="autoRouteAllVisible()">
                            <i class="bi bi-magic me-1"></i>Auto-Optimize
                        </button>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
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
        document.addEventListener('DOMContentLoaded', function () {
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
