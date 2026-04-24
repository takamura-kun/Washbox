@extends('branch.layouts.app')

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
                    <a href="{{ route('branch.dashboard.export') }}" class="btn btn-sm rounded-pill d-flex align-items-center gap-1" style="background:rgba(255,255,255,0.15);color:white;border:1px solid rgba(255,255,255,0.25);font-size:.8rem;backdrop-filter:blur(6px);">
                        <i class="bi bi-download"></i><span>Export</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         QUICK SEARCH BAR
    ============================================================ --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="quickSearch" class="form-control border-start-0 ps-0" placeholder="🔍 Search by tracking number, customer name, or phone..." autocomplete="off">
                        <button class="btn btn-primary" type="button" onclick="performQuickSearch()">
                            <i class="bi bi-search me-2"></i>Search
                        </button>
                    </div>
                    <div id="searchResults" class="mt-2" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         QUICK ACTIONS — 1×4 Grid
    ============================================================ --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <a href="{{ route('branch.laundries.create') }}" class="text-decoration-none">
                <div class="alert-card-modern pickup-pending" style="padding:2rem;cursor:pointer;transition:transform 0.2s, box-shadow 0.2s;min-height:200px;display:flex;flex-direction:column;justify-content:center;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 16px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                    <div class="alert-icon-modern" style="width:64px;height:64px;font-size:1.75rem;margin:0 auto;"><i class="bi bi-plus-lg"></i></div>
                    <div class="alert-content" style="margin-top:1.25rem;text-align:center;">
                        <h6 class="alert-title" style="font-size:1.1rem;margin-bottom:0.5rem;font-weight:700;">Create Laundry</h6>
                        <p class="alert-desc" style="font-size:0.95rem;margin-bottom:0;">Start a new laundry order</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('branch.customers.create') }}" class="text-decoration-none">
                <div class="alert-card-modern laundry-ready" style="padding:2rem;cursor:pointer;transition:transform 0.2s, box-shadow 0.2s;min-height:200px;display:flex;flex-direction:column;justify-content:center;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 16px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                    <div class="alert-icon-modern" style="width:64px;height:64px;font-size:1.75rem;margin:0 auto;"><i class="bi bi-person-plus"></i></div>
                    <div class="alert-content" style="margin-top:1.25rem;text-align:center;">
                        <h6 class="alert-title" style="font-size:1.1rem;margin-bottom:0.5rem;font-weight:700;">Create Customer</h6>
                        <p class="alert-desc" style="font-size:0.95rem;margin-bottom:0;">Register a new customer</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('branch.unclaimed.index') }}" class="text-decoration-none">
                <div class="alert-card-modern unclaimed-alert" style="padding:2rem;cursor:pointer;transition:transform 0.2s, box-shadow 0.2s;min-height:200px;display:flex;flex-direction:column;justify-content:center;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 16px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                    <div class="alert-icon-modern" style="width:64px;height:64px;font-size:1.75rem;margin:0 auto;"><i class="bi bi-exclamation-triangle"></i></div>
                    <div class="alert-content" style="margin-top:1.25rem;text-align:center;">
                        <h3 class="alert-number" style="font-size:2.25rem;margin-bottom:0.5rem;font-weight:800;">{{ $unclaimed['total_count'] ?? 0 }}</h3>
                        <h6 class="alert-title" style="font-size:1.1rem;margin-bottom:0.5rem;font-weight:700;">Unclaimed Items</h6>
                        <p class="alert-desc" style="font-size:0.95rem;margin-bottom:0;">Require attention</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('branch.retail.index') }}" class="text-decoration-none">
                <div class="alert-card-modern revenue-risk" style="padding:2rem;cursor:pointer;transition:transform 0.2s, box-shadow 0.2s;min-height:200px;display:flex;flex-direction:column;justify-content:center;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 16px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                    <div class="alert-icon-modern" style="width:64px;height:64px;font-size:1.75rem;margin:0 auto;"><i class="bi bi-shop"></i></div>
                    <div class="alert-content" style="margin-top:1.25rem;text-align:center;">
                        <h6 class="alert-title" style="font-size:1.1rem;margin-bottom:0.5rem;font-weight:700;">Retail</h6>
                        <p class="alert-desc" style="font-size:0.95rem;margin-bottom:0;">Manage retail products</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- ============================================================
         ATTENDANCE CARD
    ============================================================ --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="alert-card-modern" style="padding:2rem;cursor:pointer;transition:transform 0.2s, box-shadow 0.2s;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);border:none;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 20px rgba(102,126,234,0.3)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''" data-bs-toggle="modal" data-bs-target="#attendanceModal">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <div class="alert-icon-modern" style="width:80px;height:80px;font-size:2.5rem;margin:0 auto;background:rgba(255,255,255,0.2);border:3px solid rgba(255,255,255,0.3);color:white;"><i class="bi bi-clock-history"></i></div>
                    </div>
                    <div class="col-md-10">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 style="font-size:2rem;margin-bottom:0.5rem;font-weight:800;color:white;">Staff Attendance</h3>
                                <p style="font-size:1.1rem;margin-bottom:0;color:rgba(255,255,255,0.9);font-weight:600;">Click here to Clock In or Clock Out</p>
                            </div>
                            <div class="text-center" style="padding:1.5rem;background:rgba(255,255,255,0.15);border-radius:12px;min-width:150px;">
                                <div id="currentTime" style="font-size:2.5rem;font-weight:800;color:white;font-family:'DM Mono',monospace;">--:--</div>
                                <div style="font-size:0.9rem;color:rgba(255,255,255,0.8);margin-top:0.25rem;">Current Time</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Time In Modal --}}
    <div class="modal fade" id="attendanceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Time In Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Staff Member (0 available)</label>
                        <select id="staffSelect" class="form-select" required>
                            <option value="">Select Staff</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Take Photo <span class="text-danger">*</span></label>
                        <div class="text-center">
                            <video id="attendanceVideo" width="100%" height="300" autoplay style="border-radius: 8px; background: #000;"></video>
                            <canvas id="attendanceCanvas" style="display:none;"></canvas>
                            <img id="capturedPhoto" style="display:none; width:100%; border-radius: 8px;" />
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-success w-100" id="captureBtn">
                                <i class="bi bi-camera-fill me-2"></i>Capture
                            </button>
                            <button type="button" class="btn btn-warning w-100 mt-2" id="retakeBtn" style="display:none;">
                                <i class="bi bi-arrow-clockwise me-2"></i>Retake
                            </button>
                        </div>
                        <small class="text-muted">Photo is required for time-in verification</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="timeInBtn">Time In</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         OPERATIONAL QUICK ACCESS CARDS
    ============================================================ --}}
    <div class="row g-3 mb-4">
        {{-- Today's Pickup Queue --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#pickupQueueModal">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <i class="bi bi-box-seam text-white fs-4"></i>
                        </div>
                        <span class="badge bg-warning">{{ $kpis['ready_for_pickup']['value'] ?? 0 }}</span>
                    </div>
                    <h6 class="fw-bold mb-1">Pickup Queue</h6>
                    <p class="text-muted small mb-0">Ready for pickup today</p>
                </div>
            </div>
        </div>

        {{-- Quick Payment --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#quickPaymentModal">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;background:linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="bi bi-cash-coin text-white fs-4"></i>
                        </div>
                        <span class="badge bg-success">Fast</span>
                    </div>
                    <h6 class="fw-bold mb-1">Collect Payment</h6>
                    <p class="text-muted small mb-0">Quick payment processing</p>
                </div>
            </div>
        </div>

        {{-- Status Update --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#statusUpdateModal">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;background:linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                            <i class="bi bi-arrow-repeat text-white fs-4"></i>
                        </div>
                        <span class="badge bg-primary">Update</span>
                    </div>
                    <h6 class="fw-bold mb-1">Update Status</h6>
                    <p class="text-muted small mb-0">Change order status</p>
                </div>
            </div>
        </div>

        {{-- Today's Tasks --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#tasksModal">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                            <i class="bi bi-list-check text-white fs-4"></i>
                        </div>
                        <span class="badge bg-danger">8</span>
                    </div>
                    <h6 class="fw-bold mb-1">Today's Tasks</h6>
                    <p class="text-muted small mb-0">Urgent items to handle</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         ADDITIONAL INFO CARDS
    ============================================================ --}}
    <div class="row g-3 mb-4">
        {{-- Customer Lookup --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-person-search me-2"></i>Quick Customer Lookup</h6>
                    <div class="input-group">
                        <input type="text" id="customerLookup" class="form-control" placeholder="Phone or name...">
                        <button class="btn btn-primary" onclick="lookupCustomer()">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div id="customerInfo" class="mt-2" style="display:none;"></div>
                </div>
            </div>
        </div>

        {{-- Low Stock Alert --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Low Stock Items</h6>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="small">Detergent</span>
                            <span class="badge bg-warning">2 left</span>
                        </div>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="small">Fabric Softener</span>
                            <span class="badge bg-warning">1 left</span>
                        </div>
                        <div class="list-group-item px-0 py-2">
                            <button class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-cart-plus me-1"></i>Reorder
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Branch Announcements --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-megaphone text-info me-2"></i>Announcements</h6>
                    <div class="alert alert-info mb-2 py-2">
                        <small><strong>Promo:</strong> 20% off wash & fold this week!</small>
                    </div>
                    <div class="alert alert-warning mb-0 py-2">
                        <small><strong>Reminder:</strong> Monthly inventory on Friday</small>
                    </div>
                </div>
            </div>
        </div>
    </div>



    {{-- ============================================================
         TODAY'S METRICS
    ============================================================ --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="text-center p-3 rounded" style="background:var(--surface-2);border:1px solid var(--border);">
                <div class="fw-800" style="font-size:1.5rem;color:var(--text-1);font-family:'DM Mono',monospace;">{{ $kpis['completed_today']['value'] ?? 0 }}</div>
                <div style="font-size:.85rem;color:var(--text-3);font-weight:600;">Laundries Today</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center p-3 rounded" style="background:var(--surface-2);border:1px solid var(--border);">
                <div class="fw-800" style="font-size:1.5rem;color:var(--green);font-family:'DM Mono',monospace;">₱{{ number_format($kpis['today_revenue']['value'] ?? 0, 0) }}</div>
                <div style="font-size:.85rem;color:var(--text-3);font-weight:600;">Revenue Today</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center p-3 rounded" style="background:var(--surface-2);border:1px solid var(--border);">
                <div class="fw-800" style="font-size:1.5rem;color:var(--purple);font-family:'DM Mono',monospace;">{{ $inventory_count ?? 0 }}</div>
                <div style="font-size:.85rem;color:var(--text-3);font-weight:600;">Inventory Stocks</div>
            </div>
        </div>
    </div>


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
                    <a href="{{ route('branch.laundries.index') }}" style="font-size:.7rem;color:var(--text-3);text-decoration:none;font-weight:600;">View →</a>
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
                    <a href="{{ route('branch.laundries.index', ['status' => 'ready']) }}" style="font-size:.7rem;color:var(--text-3);text-decoration:none;font-weight:600;">View →</a>
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
         TABS
    ============================================================ --}}
    <div class="modern-tabs-container mb-4">
        <ul class="nav nav-segmented" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#overview" type="button"><i class="bi bi-speedometer2 me-2"></i>Overview</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#revenue" type="button"><i class="bi bi-graph-up me-2"></i>Revenue</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#laundries" type="button"><i class="bi bi-basket me-2"></i>Laundries</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#customers-tab" type="button"><i class="bi bi-people me-2"></i>Customers</button></li>
        </ul>
    </div>

    <div class="tab-content">

        {{-- ========== OVERVIEW TAB ========== --}}
        <div class="tab-pane fade show active" id="overview" role="tabpanel">

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
                             onclick="window.location.href='{{ route('branch.services.show', $service) }}'"
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
                        <div class="service-card-carousel" onclick="window.location.href='{{ route('branch.addons.show', $addon) }}'">
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

                    <div class="service-card-carousel service-card-view-all" onclick="window.location.href='{{ route('branch.services.index') }}'">
                        <div class="service-icon-wrapper service-icon-view-all"><i class="bi bi-arrow-right"></i></div>
                        <h6 class="fw-bold mb-1 text-center">View All</h6>
                        <div class="service-description text-center">Browse all services & add-ons</div>
                        <div class="text-center mt-3"><span class="badge" style="background:var(--brand);color:white;font-size:.72rem;">View All →</span></div>
                    </div>
                </div>
            </div>

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
                            <a href="{{ route('branch.laundries.index') }}" class="btn btn-sm" style="border-radius:var(--r-sm);background:var(--blue-soft);color:var(--blue);border:1px solid rgba(37,99,235,0.2);font-weight:600;font-size:.78rem;">
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
                                                        <a href="{{ route('branch.laundries.show', $laundry->id) }}" class="btn-icon-modern"><i class="bi bi-eye"></i></a>
                                                        <a href="{{ route('branch.laundries.edit', $laundry->id) }}" class="btn-icon-modern"><i class="bi bi-pencil"></i></a>
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

    {{-- ============================================================
         ALL MODALS
    ============================================================ --}}

    {{-- Pickup Queue Modal --}}
    <div class="modal fade" id="pickupQueueModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Today's Pickup Queue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tracking #</th>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-primary">WB-2024-001</span></td>
                                    <td>John Doe</td>
                                    <td>0912-345-6789</td>
                                    <td>₱450.00</td>
                                    <td><button class="btn btn-sm btn-success">Mark Picked Up</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Payment Modal --}}
    <div class="modal fade" id="quickPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-cash-coin me-2"></i>Quick Payment Collection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tracking Number</label>
                        <input type="text" class="form-control" id="paymentTracking" placeholder="Scan or enter tracking number">
                    </div>
                    <div id="paymentDetails" style="display:none;">
                        <div class="alert alert-info">
                            <strong>Customer:</strong> <span id="payCustomer"></span><br>
                            <strong>Amount Due:</strong> <span id="payAmount"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select">
                                <option>Cash</option>
                                <option>GCash</option>
                                <option>Card</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success">Collect Payment</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Update Modal --}}
    <div class="modal fade" id="statusUpdateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-arrow-repeat me-2"></i>Update Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tracking Number</label>
                        <input type="text" class="form-control" placeholder="Scan or enter tracking number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select class="form-select">
                            <option>Received</option>
                            <option>Processing</option>
                            <option>Ready</option>
                            <option>Completed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Update Status</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tasks Modal --}}
    <div class="modal fade" id="tasksModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-list-check me-2"></i>Today's Urgent Tasks</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-exclamation-circle text-danger me-2"></i>
                                <strong>3 orders overdue for pickup</strong>
                                <p class="mb-0 small text-muted">Contact customers immediately</p>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">View</button>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-clock text-warning me-2"></i>
                                <strong>5 orders ready today</strong>
                                <p class="mb-0 small text-muted">Notify customers for pickup</p>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">Notify</button>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-telephone text-info me-2"></i>
                                <strong>2 customer follow-ups needed</strong>
                                <p class="mb-0 small text-muted">Pending payment confirmation</p>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">Call</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Floating Quick Actions Button --}}
    <div class="position-fixed bottom-0 end-0 p-4" style="z-index:1050;">
        <div class="dropup">
            <button class="btn btn-primary btn-lg rounded-circle shadow-lg" type="button" data-bs-toggle="dropdown" style="width:60px;height:60px;">
                <i class="bi bi-lightning-charge-fill fs-4"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end mb-2 shadow-lg">
                <li><h6 class="dropdown-header">Quick Actions</h6></li>
                <li><a class="dropdown-item" href="#" onclick="document.getElementById('quickSearch').focus()"><i class="bi bi-search me-2"></i>Search Order</a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#quickPaymentModal"><i class="bi bi-cash-coin me-2"></i>Collect Payment</a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#statusUpdateModal"><i class="bi bi-arrow-repeat me-2"></i>Update Status</a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#attendanceModal"><i class="bi bi-clock me-2"></i>Time In/Out</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="lookupCustomer()"><i class="bi bi-person-search me-2"></i>Find Customer</a></li>
            </ul>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/chart.js/chart.umd.min.js') }}"></script>
    <script src="{{ asset('assets/jquery/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('assets/leaflet/leaflet.js') }}"></script>
    <script src="{{ asset('assets/leaflet/leaflet.markercluster.js') }}"></script>

    <script>
        // Update current time
        function updateCurrentTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = hours + ':' + minutes;
            }
        }
        updateCurrentTime();
        setInterval(updateCurrentTime, 1000);

        // Attendance Modal Handler
        let videoStream = null;
        const attendanceModal = document.getElementById('attendanceModal');
        const video = document.getElementById('attendanceVideo');
        const canvas = document.getElementById('attendanceCanvas');
        const capturedPhoto = document.getElementById('capturedPhoto');
        const captureBtn = document.getElementById('captureBtn');
        const retakeBtn = document.getElementById('retakeBtn');
        const timeInBtn = document.getElementById('timeInBtn');
        const staffSelect = document.getElementById('staffSelect');

        // Start camera when modal opens
        attendanceModal.addEventListener('shown.bs.modal', async function () {
            try {
                videoStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: 640, height: 480 }
                });
                video.srcObject = videoStream;
            } catch (error) {
                console.error('Camera error:', error);
                alert('Unable to access camera: ' + error.message);
            }
        });

        // Stop camera when modal closes
        attendanceModal.addEventListener('hidden.bs.modal', function () {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
            video.style.display = 'block';
            capturedPhoto.style.display = 'none';
            captureBtn.style.display = 'block';
            retakeBtn.style.display = 'none';
            staffSelect.value = '';
        });

        // Capture photo
        captureBtn.addEventListener('click', function () {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);

            capturedPhoto.src = canvas.toDataURL('image/jpeg', 0.8);
            video.style.display = 'none';
            capturedPhoto.style.display = 'block';
            captureBtn.style.display = 'none';
            retakeBtn.style.display = 'block';
        });

        // Retake photo
        retakeBtn.addEventListener('click', function () {
            video.style.display = 'block';
            capturedPhoto.style.display = 'none';
            captureBtn.style.display = 'block';
            retakeBtn.style.display = 'none';
        });

        // Submit attendance
        timeInBtn.addEventListener('click', async function () {
            const staffId = staffSelect.value;
            if (!staffId) {
                alert('Please select a staff member');
                return;
            }

            if (!capturedPhoto.src || capturedPhoto.style.display === 'none') {
                alert('Please capture a photo');
                return;
            }

            try {
                timeInBtn.disabled = true;
                timeInBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';

                const response = await fetch('/branch/attendance/time-in', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        user_id: staffId,
                        photo_data: capturedPhoto.src
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Time in recorded successfully!');
                    bootstrap.Modal.getInstance(attendanceModal).hide();
                    location.reload();
                } else {
                    alert(result.message || 'Failed to record time in');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            } finally {
                timeInBtn.disabled = false;
                timeInBtn.innerHTML = 'Time In';
            }
        });
    </script>

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

    <script>
        // Quick Search Function
        function performQuickSearch() {
            const query = document.getElementById('quickSearch').value;
            if (!query) return;

            fetch(`/branch/search?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    const results = document.getElementById('searchResults');
                    if (data.length > 0) {
                        results.innerHTML = data.map(item => `
                            <div class="card mb-2">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>${item.tracking_number}</strong> - ${item.customer_name}
                                            <br><small class="text-muted">${item.phone}</small>
                                        </div>
                                        <a href="/branch/laundries/${item.id}" class="btn btn-sm btn-primary">View</a>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                        results.style.display = 'block';
                    } else {
                        results.innerHTML = '<div class="alert alert-warning">No results found</div>';
                        results.style.display = 'block';
                    }
                });
        }

        // Customer Lookup Function
        function lookupCustomer() {
            const query = document.getElementById('customerLookup').value;
            if (!query) return;

            fetch(`/branch/customers/lookup?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    const info = document.getElementById('customerInfo');
                    if (data) {
                        info.innerHTML = `
                            <div class="card">
                                <div class="card-body">
                                    <h6>${data.name}</h6>
                                    <p class="mb-1"><i class="bi bi-telephone me-2"></i>${data.phone}</p>
                                    <p class="mb-1"><small>Recent orders: ${data.order_count}</small></p>
                                    <p class="mb-0"><small>Total spent: ₱${data.total_spent}</small></p>
                                    <a href="tel:${data.phone}" class="btn btn-sm btn-success mt-2 w-100">
                                        <i class="bi bi-telephone me-1"></i>Call Customer
                                    </a>
                                </div>
                            </div>
                        `;
                        info.style.display = 'block';
                    } else {
                        info.innerHTML = '<div class="alert alert-warning">Customer not found</div>';
                        info.style.display = 'block';
                    }
                });
        }

        // Enter key support for search
        document.getElementById('quickSearch')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') performQuickSearch();
        });

        document.getElementById('customerLookup')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') lookupCustomer();
        });
    </script>


@endpush
