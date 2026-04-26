@extends('admin.layouts.app')

@section('title', 'Dashboard Overview')

@section('content')

{{-- ══════════════════════════════════════════
     TOP BAR — Title + Live Badge + Timestamp
══════════════════════════════════════════ --}}
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h1 class="dash-card-title mb-1" style="font-size: 1.1rem;">Dashboard overview</h1>
        <p class="dash-card-subtitle mb-0">Multi-branch operations — Sibulan · Siaton · Bais City</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="dash-badge dash-badge-success d-flex align-items-center gap-1">
            <span style="width:6px;height:6px;border-radius:50%;background:#fff;display:inline-block;"></span>
            Live
        </span>
        <small class="dash-text-muted">{{ now()->format('D, M j, Y · H:i') }}</small>
    </div>
</div>


{{-- ══════════════════════════════════════════
     ACTION TRIAGE — Decision-First Layer
══════════════════════════════════════════ --}}
@if(!empty($stats['actionTriage']) && count($stats['actionTriage']) > 0)
<div class="row g-2 mb-3" id="actionTriageSection">
    @foreach($stats['actionTriage'] as $action)
    <div class="col-12 col-md-6 col-lg-4">
        <a href="{{ $action['url'] ?? '#' }}" class="text-decoration-none">
            <div class="dash-card dash-border-left-{{ $action['urgency'] === 'critical' ? 'critical' : 'warning' }}" style="padding: 0.7rem;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="dash-card-icon dash-icon-{{ $action['urgency'] === 'critical' ? 'critical' : 'warning' }}" style="width:30px;height:30px;font-size:0.85rem;">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div>
                            <p class="dash-text-xs dash-text-muted mb-0" style="text-transform:uppercase;letter-spacing:.4px;">{{ $action['location'] ?? 'System' }}</p>
                            <p class="dash-text-sm dash-text-bold mb-0 {{ $action['urgency'] === 'critical' ? 'dash-text-critical' : 'dash-text-warning' }}" style="line-height:1.3;">
                                {{ Str::limit($action['situation'] ?? 'Action needed', 55) }}
                            </p>
                        </div>
                    </div>
                    <span class="dash-badge {{ $action['urgency'] === 'critical' ? 'dash-badge-critical' : 'dash-badge-warning' }}" style="white-space:nowrap;">
                        {{ $action['urgency'] === 'critical' ? 'URGENT' : 'TODAY' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="dash-text-muted">{{ $action['button_text'] ?? 'Resolve soon' }}</small>
                    <span class="dash-btn dash-btn-info" style="font-size:0.6rem;padding:3px 8px;">Review</span>
                </div>
            </div>
        </a>
    </div>
    @endforeach

    <div class="col-12 d-flex justify-content-end">
        <button type="button"
            class="btn btn-sm"
            onclick="document.getElementById('actionTriageSection').style.display='none'; localStorage.setItem('dismissTriage_{{ date('Y-m-d') }}', '1');"
            style="font-size:0.65rem;color:#94a3b8;background:transparent;border:1px solid #334155;padding:3px 10px;">
            <i class="bi bi-x me-1"></i>Dismiss for today
        </button>
    </div>
</div>
<script>
    if (localStorage.getItem('dismissTriage_{{ date('Y-m-d') }}') === '1') {
        document.getElementById('actionTriageSection').style.display = 'none';
    }
</script>
@endif


{{-- ══════════════════════════════════════════
     METRIC CARDS — Row 1: Core Operations
══════════════════════════════════════════ --}}
<div class="row g-2 mb-2">

    {{-- Today's Laundries --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="metric-card-compact border-blue" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#laundriesBreakdownModal">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-blue-light"><i class="bi bi-basket3"></i></div>
                <span class="metric-change {{ ($stats['laundriesChange'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                    <i class="bi bi-arrow-{{ ($stats['laundriesChange'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($stats['laundriesChange'] ?? 0) }}%
                </span>
            </div>
            <div class="metric-label-compact">Today's laundries</div>
            <div class="metric-value-large text-slate-900">{{ $stats['todayLaundries'] ?? 0 }}</div>
            <small class="text-muted">Total: {{ number_format($stats['totalLaundries'] ?? 0) }}</small>
        </div>
    </div>

    {{-- Today's Revenue --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="metric-card-compact border-green" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#revenueBreakdownModal">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-green-light"><i class="bi bi-cash-coin"></i></div>
                <span class="metric-change {{ ($stats['revenueChange'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                    <i class="bi bi-arrow-{{ ($stats['revenueChange'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($stats['revenueChange'] ?? 0) }}%
                </span>
            </div>
            <div class="metric-label-compact">Today's revenue</div>
            <div class="metric-value-large text-slate-900" style="font-size:1rem;">₱{{ number_format($stats['todayRevenue'] ?? 0, 0) }}</div>
            <small class="text-muted">Month: ₱{{ number_format($stats['thisMonthRevenue'] ?? 0, 0) }}</small>
        </div>
    </div>

    {{-- Active Customers --}}
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.customers.index') }}" class="text-decoration-none">
            <div class="metric-card-compact border-yellow">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-compact bg-yellow-light"><i class="bi bi-people"></i></div>
                    <span class="metric-change positive">
                        <i class="bi bi-plus"></i>{{ $stats['newCustomersToday'] ?? 0 }}
                    </span>
                </div>
                <div class="metric-label-compact">Active customers</div>
                <div class="metric-value-large text-slate-900">{{ number_format($stats['activeCustomers'] ?? 0) }}</div>
                <small class="text-muted">{{ $stats['newCustomersToday'] ?? 0 }} new today</small>
            </div>
        </a>
    </div>

    {{-- Unclaimed Items --}}
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.unclaimed.index') }}" class="text-decoration-none">
            <div class="metric-card-compact border-red">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-compact bg-red-light"><i class="bi bi-exclamation-triangle"></i></div>
                    <span class="dash-badge dash-badge-critical" style="font-size:0.55rem;padding:2px 5px;">CRITICAL</span>
                </div>
                <div class="metric-label-compact">Unclaimed items</div>
                <div class="metric-value-large text-danger">{{ $stats['unclaimedLaundry'] ?? 0 }}</div>
                <small class="text-muted">Loss: ₱{{ number_format($stats['estimatedUnclaimedLoss'] ?? 0, 0) }}</small>
            </div>
        </a>
    </div>

    {{-- Today's Profit --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="metric-card-compact border-purple" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#profitBreakdownModal">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-purple-light"><i class="bi bi-graph-up-arrow"></i></div>
                <span class="metric-change {{ ($stats['profitMetrics']['profit'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                    <i class="bi bi-arrow-{{ ($stats['profitMetrics']['profit'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($stats['profitMetrics']['margin'] ?? 0) }}%
                </span>
            </div>
            <div class="metric-label-compact">Today's profit</div>
            <div class="metric-value-large {{ ($stats['profitMetrics']['profit'] ?? 0) >= 0 ? 'text-slate-900' : 'text-danger' }}" style="font-size:1rem;">
                ₱{{ number_format($stats['profitMetrics']['profit'] ?? 0, 0) }}
            </div>
            <small class="text-muted">Margin: {{ $stats['profitMetrics']['margin'] ?? 0 }}%</small>
        </div>
    </div>

    {{-- Staff Attendance --}}
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.staff.index') }}" class="text-decoration-none">
            <div class="metric-card-compact border-cyan">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-compact bg-cyan-light"><i class="bi bi-check-circle"></i></div>
                    <span class="metric-change positive">
                        <i class="bi bi-arrow-up"></i>{{ $stats['attendanceMetrics']['rate'] ?? 0 }}%
                    </span>
                </div>
                <div class="metric-label-compact">Staff attendance</div>
                <div class="metric-value-large text-slate-900">
                    {{ $stats['attendanceMetrics']['present'] ?? 0 }}/{{ $stats['attendanceMetrics']['total'] ?? 0 }}
                </div>
                <small class="text-muted">{{ $stats['attendanceMetrics']['rate'] ?? 0 }}% present today</small>
            </div>
        </a>
    </div>
</div>


{{-- ══════════════════════════════════════════
     METRIC CARDS — Row 2: Performance KPIs
══════════════════════════════════════════ --}}
<div class="row g-2 mb-3">

    {{-- Retention Rate --}}
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.customers.index') }}" class="text-decoration-none">
            <div class="metric-card-compact border-purple">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-compact bg-purple-light"><i class="bi bi-arrow-repeat"></i></div>
                    <span class="metric-change {{ ($stats['customerRetentionRate']['rate'] ?? 0) >= 50 ? 'positive' : 'negative' }}">
                        <i class="bi bi-arrow-{{ ($stats['customerRetentionRate']['rate'] ?? 0) >= 50 ? 'up' : 'down' }}"></i>
                        {{ $stats['customerRetentionRate']['rate'] ?? 0 }}%
                    </span>
                </div>
                <div class="metric-label-compact">Retention rate</div>
                <div class="metric-value-large text-slate-900">{{ $stats['customerRetentionRate']['rate'] ?? 0 }}%</div>
                <small class="text-muted">{{ $stats['customerRetentionRate']['returning'] ?? 0 }}/{{ $stats['customerRetentionRate']['total'] ?? 0 }} returning</small>
            </div>
        </a>
    </div>

    {{-- Pickup Requests --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="metric-card-compact border-yellow" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#pendingPickupsModal">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="metric-icon-compact bg-yellow-light"><i class="bi bi-box-arrow-up"></i></div>
                <span class="dash-badge dash-badge-warning" style="font-size:0.55rem;padding:2px 5px;">PENDING</span>
            </div>
            <div class="metric-label-compact">Pickup requests</div>
            <div class="metric-value-large text-slate-900">{{ $stats['pickupRequests']['total'] ?? 0 }}</div>
            <small class="text-muted">{{ $stats['pickupRequests']['pending'] ?? 0 }} pending</small>
        </div>
    </div>

    {{-- Delivery Success Rate --}}
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.pickups.index') }}" class="text-decoration-none">
            <div class="metric-card-compact border-green">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-compact bg-green-light"><i class="bi bi-truck"></i></div>
                    <span class="metric-change {{ ($stats['deliverySuccessRate']['rate'] ?? 0) >= 80 ? 'positive' : 'negative' }}">
                        <i class="bi bi-arrow-{{ ($stats['deliverySuccessRate']['rate'] ?? 0) >= 80 ? 'up' : 'down' }}"></i>
                        {{ $stats['deliverySuccessRate']['rate'] ?? 0 }}%
                    </span>
                </div>
                <div class="metric-label-compact">Delivery success</div>
                <div class="metric-value-large text-slate-900">{{ $stats['deliverySuccessRate']['rate'] ?? 0 }}%</div>
                <small class="text-muted">{{ $stats['deliverySuccessRate']['onTime'] ?? 0 }}/{{ $stats['deliverySuccessRate']['total'] ?? 0 }} on-time</small>
            </div>
        </a>
    </div>

    {{-- Avg Completion Time --}}
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.laundries.index') }}" class="text-decoration-none">
            <div class="metric-card-compact border-blue">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-compact bg-blue-light"><i class="bi bi-clock-history"></i></div>
                    <span class="metric-change {{ ($stats['serviceCompletionTime']['hours'] ?? 0) <= 48 ? 'positive' : 'negative' }}">
                        <i class="bi bi-arrow-{{ ($stats['serviceCompletionTime']['hours'] ?? 0) <= 48 ? 'down' : 'up' }}"></i>
                        {{ $stats['serviceCompletionTime']['formatted'] ?? '0h' }}
                    </span>
                </div>
                <div class="metric-label-compact">Avg completion</div>
                <div class="metric-value-large text-slate-900">{{ $stats['serviceCompletionTime']['formatted'] ?? '0h' }}</div>
                <small class="text-muted">Last 30 days avg</small>
            </div>
        </a>
    </div>

    {{-- Average Order Value --}}
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.laundries.index') }}" class="text-decoration-none">
            <div class="metric-card-compact border-cyan">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-compact bg-cyan-light"><i class="bi bi-receipt"></i></div>
                    <span class="metric-change {{ ($stats['averageOrderValue']['change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        <i class="bi bi-arrow-{{ ($stats['averageOrderValue']['change'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($stats['averageOrderValue']['change'] ?? 0) }}%
                    </span>
                </div>
                <div class="metric-label-compact">Avg order value</div>
                <div class="metric-value-large text-slate-900" style="font-size:1rem;">₱{{ number_format($stats['averageOrderValue']['current'] ?? 0, 0) }}</div>
                <small class="text-muted">This month avg</small>
            </div>
        </a>
    </div>

    {{-- Retail Sales --}}
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.finance.retail-sales.index') }}" class="text-decoration-none">
            <div class="metric-card-compact border-green">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-compact bg-green-light"><i class="bi bi-cart-check"></i></div>
                    <span class="metric-change {{ ($stats['retailSales']['change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        <i class="bi bi-arrow-{{ ($stats['retailSales']['change'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($stats['retailSales']['change'] ?? 0) }}%
                    </span>
                </div>
                <div class="metric-label-compact">Retail sales</div>
                <div class="metric-value-large text-slate-900" style="font-size:1rem;">₱{{ number_format($stats['retailSales']['total'] ?? 0, 0) }}</div>
                <small class="text-muted">{{ $stats['retailSales']['count'] ?? 0 }} items sold today</small>
            </div>
        </a>
    </div>

    {{-- Pending Payment --}}
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.laundries.index', ['status' => 'ready']) }}" class="text-decoration-none">
            <div class="metric-card-compact border-red">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-compact bg-red-light"><i class="bi bi-cash-stack"></i></div>
                    <span class="dash-badge dash-badge-warning" style="font-size:0.55rem;padding:2px 5px;">UNPAID</span>
                </div>
                <div class="metric-label-compact">Pending payment</div>
                <div class="metric-value-large text-danger">{{ $stats['paymentCollection']['pending_payment'] ?? 0 }}</div>
                <small class="text-muted">₱{{ number_format($stats['paymentCollection']['pending_amount'] ?? 0, 0) }} total</small>
            </div>
        </a>
    </div>

    {{-- Ready to Deliver --}}
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.laundries.index') }}?has_pickup=1&status=ready" class="text-decoration-none">
            <div class="metric-card-compact border-blue">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-compact bg-blue-light"><i class="bi bi-truck"></i></div>
                    <span class="dash-badge dash-badge-info" style="font-size:0.55rem;padding:2px 5px;">DELIVER</span>
                </div>
                <div class="metric-label-compact">Ready to deliver</div>
                <div class="metric-value-large text-slate-900">{{ $stats['paymentCollection']['ready_to_deliver'] ?? 0 }}</div>
                <small class="text-muted">From pickup requests</small>
            </div>
        </a>
    </div>

    {{-- Total Branches Active --}}
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.branches.index') }}" class="text-decoration-none">
            <div class="metric-card-compact border-cyan">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-compact bg-cyan-light"><i class="bi bi-shop"></i></div>
                    <span class="dash-badge dash-badge-info" style="font-size:0.55rem;padding:2px 5px;">ACTIVE</span>
                </div>
                <div class="metric-label-compact">Active branches</div>
                <div class="metric-value-large text-slate-900">{{ \App\Models\Branch::where('is_active', true)->count() }}</div>
                <small class="text-muted">{{ \App\Models\Branch::count() }} total branches</small>
            </div>
        </a>
    </div>

    {{-- New Customers Today --}}
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.customers.index') }}" class="text-decoration-none">
            <div class="metric-card-compact border-green">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-compact bg-green-light"><i class="bi bi-person-plus"></i></div>
                    <span class="metric-change positive"><i class="bi bi-arrow-up"></i>Today</span>
                </div>
                <div class="metric-label-compact">New customers</div>
                <div class="metric-value-large text-slate-900">{{ $stats['newCustomersToday'] ?? 0 }}</div>
                <small class="text-muted">{{ $stats['newCustomersWeek'] ?? 0 }} this week</small>
            </div>
        </a>
    </div>

    {{-- Cancelled Orders Today --}}
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.laundries.index', ['status' => 'cancelled']) }}" class="text-decoration-none">
            <div class="metric-card-compact border-red">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-compact bg-red-light"><i class="bi bi-x-circle"></i></div>
                    <span class="dash-badge dash-badge-danger" style="font-size:0.55rem;padding:2px 5px;">TODAY</span>
                </div>
                <div class="metric-label-compact">Cancelled orders</div>
                <div class="metric-value-large text-danger">{{ \App\Models\Laundry::whereDate('updated_at', today())->where('status', 'cancelled')->count() }}</div>
                <small class="text-muted">{{ \App\Models\Laundry::whereMonth('created_at', now()->month)->where('status', 'cancelled')->count() }} this month</small>
            </div>
        </a>
    </div>

    {{-- Processing Now --}}
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.laundries.index', ['status' => 'processing']) }}" class="text-decoration-none">
            <div class="metric-card-compact border-purple">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="metric-icon-compact bg-purple-light"><i class="bi bi-arrow-repeat"></i></div>
                    <span class="dash-badge" style="font-size:0.55rem;padding:2px 5px;background:rgba(139,92,246,0.15);color:#7c3aed;">LIVE</span>
                </div>
                <div class="metric-label-compact">Processing now</div>
                <div class="metric-value-large text-slate-900">{{ \App\Models\Laundry::whereIn('status', ['received','processing'])->count() }}</div>
                <small class="text-muted">Across all branches</small>
            </div>
        </a>
    </div>
</div>


{{-- ══════════════════════════════════════════
     SMART RECOMMENDATIONS
══════════════════════════════════════════ --}}
@if(!empty($smartRecommendations) && count($smartRecommendations) > 0)
<div class="row g-3 mb-3" id="smartRecommendationsSection">
    <div class="col-12">
        <div class="dash-card-gradient">
            <div class="dash-card-header mb-2">
                <div class="dash-card-icon">
                    <i class="bi bi-lightbulb" style="font-size:0.9rem;"></i>
                </div>
                <div>
                    <h6 class="dash-card-title mb-0">Smart recommendations</h6>
                    <p class="dash-card-subtitle mb-0">AI-powered insights · {{ count($smartRecommendations) }} active</p>
                </div>
                <button type="button"
                    class="btn btn-sm ms-auto"
                    onclick="document.getElementById('smartRecommendationsSection').style.display='none'; localStorage.setItem('dismissReco_{{ date('Y-m-d') }}', '1');"
                    style="font-size:0.6rem;color:rgba(255,255,255,0.7);background:transparent;border:1px solid rgba(255,255,255,0.2);padding:2px 8px;">
                    <i class="bi bi-x"></i> Dismiss
                </button>
            </div>
            <div class="row g-2">
                @foreach($smartRecommendations as $rec)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="dash-recommendation-card">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <i class="bi {{ $rec['icon'] ?? 'bi-lightbulb' }}" style="font-size:1rem;margin-top:2px;color:#fff;"></i>
                            <div>
                                <h6 class="dash-card-title mb-1" style="font-size:0.75rem;">{{ $rec['title'] ?? 'Recommendation' }}</h6>
                                <p class="dash-alert-text mb-0" style="font-size:0.68rem;line-height:1.35;color:rgba(255,255,255,0.85);">{{ $rec['description'] ?? '' }}</p>
                            </div>
                        </div>
                        @if(!empty($rec['link']))
                        <a href="{{ $rec['link'] }}" class="dash-btn w-100 justify-content-center mt-2" style="font-size:0.65rem;">
                            <i class="bi bi-arrow-right me-1"></i>View details
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
<script>
    if (localStorage.getItem('dismissReco_{{ date('Y-m-d') }}') === '1') {
        document.getElementById('smartRecommendationsSection').style.display = 'none';
    }
</script>
@endif


{{-- Quick Actions section removed - using floating FAB instead --}}

{{-- ══════════════════════════════════════════
     CHARTS ROW 1 — Inventory Snapshot + Financial Summary
══════════════════════════════════════════ --}}
<div class="row g-3 mb-3">

    {{-- Inventory Snapshot Card --}}
    <div class="col-lg-7">
        <div class="modern-card h-100">
            <div class="card-header-modern d-flex align-items-center justify-content-between" style="padding:12px 16px;">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size:1.1rem;">📦</span>
                    <h6 class="mb-0 fw-bold" style="font-size:0.8rem;">Inventory snapshot</h6>
                </div>
                <a href="{{ route('admin.inventory.index') }}" 
                   style="color:#2563eb;font-size:0.65rem;text-decoration:none;padding:3px 8px;border:1px solid rgba(37,99,235,0.2);border-radius:4px;background:rgba(59,130,246,0.1);">
                    Manage ↗
                </a>
            </div>
            <div class="card-body-modern" style="padding:16px;">
                @php
                    $branchId = request('branch_id');
                    $totalItems = \App\Models\BranchStock::when($branchId, fn($q) => $q->where('branch_id', $branchId))->count();
                    $criticalItems = \App\Models\BranchStock::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->whereRaw('current_stock <= reorder_point * 0.5')
                        ->where('current_stock', '>', 0)->count();
                    $lowStockItems = \App\Models\BranchStock::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->whereRaw('current_stock > reorder_point * 0.5 AND current_stock <= reorder_point')->count();
                    $totalValue = \App\Models\BranchStock::join('inventory_items', 'branch_stocks.inventory_item_id', '=', 'inventory_items.id')
                        ->when($branchId, fn($q) => $q->where('branch_stocks.branch_id', $branchId))
                        ->where('branch_stocks.current_stock', '>', 0)
                        ->selectRaw('SUM(branch_stocks.current_stock * inventory_items.unit_cost_price) as total')->value('total') ?? 0;
                    
                    // Get all inventory items with their stock status
                    $allInventoryItems = \App\Models\BranchStock::with(['inventoryItem', 'branch'])
                        ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->where('current_stock', '>', 0)
                        ->orderByRaw('CASE 
                            WHEN current_stock <= reorder_point * 0.5 THEN 0 
                            WHEN current_stock <= reorder_point THEN 1 
                            ELSE 2 
                        END')
                        ->orderBy('current_stock', 'asc')
                        ->get()
                        ->map(function($stock) {
                            $isCritical = $stock->current_stock <= ($stock->reorder_point * 0.5);
                            $isLow = !$isCritical && $stock->current_stock <= $stock->reorder_point;
                            
                            // Get last restocked date from inventory distribution items
                            $lastRestocked = \App\Models\InventoryDistributionItem::where('branch_id', $stock->branch_id)
                                ->where('inventory_item_id', $stock->inventory_item_id)
                                ->latest('created_at')
                                ->first();
                            
                            return [
                                'id' => $stock->id,
                                'name' => $stock->inventoryItem->name ?? 'Unknown',
                                'branch' => $stock->branch->name ?? 'Unknown',
                                'stock' => $stock->current_stock,
                                'reorder_point' => $stock->reorder_point,
                                'unit' => $stock->inventoryItem->unit ?? 'units',
                                'status' => $isCritical ? 'Critical' : ($isLow ? 'Low' : 'Good'),
                                'color' => $isCritical ? '#ef4444' : ($isLow ? '#f59e0b' : '#10b981'),
                                'last_restocked' => $lastRestocked ? $lastRestocked->created_at->diffForHumans() : 'Never',
                                'percentage' => $stock->reorder_point > 0 ? round(($stock->current_stock / $stock->reorder_point) * 100) : 100
                            ];
                        });
                @endphp
                
                {{-- Summary Stats --}}
                <div class="row g-2 mb-3">
                    <div class="col-3">
                        <div class="text-center p-2" style="background:rgba(37,99,235,0.1);border-radius:6px;border:1px solid rgba(37,99,235,0.3);">
                            <div style="font-size:1.4rem;font-weight:700;color:#2563eb;">{{ $totalItems }}</div>
                            <div class="text-muted" style="font-size:0.6rem;text-transform:uppercase;letter-spacing:0.5px;">Total</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center p-2" style="background:rgba(220,38,38,0.1);border-radius:6px;border:1px solid rgba(220,38,38,0.3);">
                            <div style="font-size:1.4rem;font-weight:700;color:#dc2626;">{{ $criticalItems }}</div>
                            <div class="text-muted" style="font-size:0.6rem;text-transform:uppercase;letter-spacing:0.5px;">Critical</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center p-2" style="background:rgba(217,119,6,0.1);border-radius:6px;border:1px solid rgba(217,119,6,0.3);">
                            <div style="font-size:1.4rem;font-weight:700;color:#d97706;">{{ $lowStockItems }}</div>
                            <div class="text-muted" style="font-size:0.6rem;text-transform:uppercase;letter-spacing:0.5px;">Low stock</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center p-2" style="background:rgba(22,163,74,0.1);border-radius:6px;border:1px solid rgba(22,163,74,0.3);">
                            <div style="font-size:1.4rem;font-weight:700;color:#16a34a;">₱{{ number_format($totalValue / 1000, 1) }}k</div>
                            <div class="text-muted" style="font-size:0.6rem;text-transform:uppercase;letter-spacing:0.5px;">Value</div>
                        </div>
                    </div>
                </div>

                {{-- Search and Filter Bar --}}
                <div class="mb-2">
                    <div class="row g-2">
                        <div class="col-8">
                            <input type="text" 
                                   id="inventorySearch" 
                                   class="form-control form-control-sm" 
                                   placeholder="Search items..." 
                                   style="background:rgba(0,0,0,0.02);border:1px solid rgba(0,0,0,0.1);font-size:0.7rem;padding:4px 8px;">
                        </div>
                        <div class="col-4">
                            <select id="inventoryFilter" 
                                    class="form-select form-select-sm" 
                                    style="background:rgba(0,0,0,0.02);border:1px solid rgba(0,0,0,0.1);font-size:0.7rem;padding:4px 8px;">
                                <option value="all">All Status</option>
                                <option value="critical">Critical</option>
                                <option value="low">Low Stock</option>
                                <option value="good">Good</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- All Inventory Items List --}}
                @if($allInventoryItems->count() > 0)
                <div id="inventoryList" style="background:rgba(0,0,0,0.02);border-radius:6px;padding:10px;border:1px solid rgba(0,0,0,0.1);max-height:240px;overflow-y:auto;">
                    @foreach($allInventoryItems as $item)
                    <div class="inventory-item" 
                         data-name="{{ strtolower($item['name']) }}" 
                         data-branch="{{ strtolower($item['branch']) }}" 
                         data-status="{{ strtolower($item['status']) }}"
                         style="padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid rgba(0,0,0,0.1);' : '' }}">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1 min-w-0" style="padding-right:12px;">
                                <div style="font-size:0.75rem;font-weight:500;margin-bottom:3px;">
                                    {{ $item['name'] }}
                                </div>
                                <div class="text-muted" style="font-size:0.62rem;margin-bottom:4px;">{{ $item['branch'] }}</div>
                                
                                {{-- Stock Progress Bar --}}
                                <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px;">
                                    <div style="flex:1;height:4px;background:rgba(0,0,0,0.2);border-radius:2px;overflow:hidden;">
                                        <div style="width:{{ min($item['percentage'], 100) }}%;height:100%;background:{{ $item['color'] }};transition:width 0.3s;"></div>
                                    </div>
                                    <span class="text-muted" style="font-size:0.6rem;white-space:nowrap;">{{ $item['stock'] }}/{{ $item['reorder_point'] }}</span>
                                </div>
                                
                                {{-- Last Restocked --}}
                                <div class="text-muted" style="font-size:0.58rem;">
                                    <i class="bi bi-clock" style="font-size:0.55rem;"></i> {{ $item['last_restocked'] }}
                                </div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <div style="font-size:0.75rem;font-weight:600;color:{{ $item['color'] }};">{{ number_format($item['stock'], 0) }} {{ $item['unit'] }}</div>
                                <div style="font-size:0.6rem;color:{{ $item['color'] }};">{{ $item['status'] }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div style="background:rgba(0,0,0,0.02);border:1px solid rgba(0,0,0,0.1);border-radius:6px;padding:24px;text-align:center;">
                    <i class="bi bi-box-seam text-muted" style="font-size:2rem;opacity:0.5;"></i>
                    <p class="text-muted" style="font-size:0.72rem;margin-top:8px;margin-bottom:0;">No inventory items found</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Inventory Search & Filter Script --}}
    <script>
    (function() {
        const searchInput = document.getElementById('inventorySearch');
        const filterSelect = document.getElementById('inventoryFilter');
        const inventoryItems = document.querySelectorAll('.inventory-item');
        
        function filterInventory() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusFilter = filterSelect.value.toLowerCase();
            
            inventoryItems.forEach(item => {
                const name = item.dataset.name;
                const branch = item.dataset.branch;
                const status = item.dataset.status;
                
                const matchesSearch = name.includes(searchTerm) || branch.includes(searchTerm);
                const matchesFilter = statusFilter === 'all' || status === statusFilter;
                
                if (matchesSearch && matchesFilter) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        if (searchInput) searchInput.addEventListener('input', filterInventory);
        if (filterSelect) filterSelect.addEventListener('change', filterInventory);
    })();
    </script>

    {{-- Financial Summary --}}
    <div class="col-lg-5">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-cash-stack text-success me-2"></i>Financial summary
                </h6>
                <small>Today's income, expenses & profit</small>
            </div>
            <div class="card-body-modern">
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <div class="financial-metric">
                            <div class="metric-label small mb-1">Total income</div>
                            <div class="metric-value text-success fw-bold" style="font-size:1.2rem;">
                                ₱{{ number_format($stats['financialBreakdown']['total_income'] ?? 0, 0) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="financial-metric">
                            <div class="metric-label small mb-1">Total expenses</div>
                            <div class="metric-value text-danger fw-bold" style="font-size:1.2rem;">
                                ₱{{ number_format($stats['financialBreakdown']['total_expense'] ?? 0, 0) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="financial-metric">
                            <div class="metric-label small mb-1">Net profit</div>
                            <div class="metric-value fw-bold {{ ($stats['financialBreakdown']['net_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:1.2rem;">
                                ₱{{ number_format($stats['financialBreakdown']['net_profit'] ?? 0, 0) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="financial-metric">
                            <div class="metric-label small mb-1">Profit margin</div>
                            <div class="metric-value fw-bold {{ ($stats['financialBreakdown']['profit_margin'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:1.2rem;">
                                {{ $stats['financialBreakdown']['profit_margin'] ?? 0 }}%
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-2" style="background:rgba(15,23,42,0.4);border-radius:6px;border:1px solid #1e293b;">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted" style="font-size:0.62rem;">Laundry services</small>
                        <small class="text-success" style="font-size:0.62rem;font-weight:600;">
                            ₱{{ number_format($stats['financialBreakdown']['laundry_income'] ?? 0, 0) }}
                        </small>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted" style="font-size:0.62rem;">Retail sales</small>
                        <small class="text-success" style="font-size:0.62rem;font-weight:600;">
                            ₱{{ number_format($stats['financialBreakdown']['retail_income'] ?? 0, 0) }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════
     CHARTS ROW 2 — Revenue vs Expenses + Service Demand
══════════════════════════════════════════ --}}
<div class="row g-3 mb-3">

    {{-- Revenue vs Expense Trend --}}
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-slate-800">Revenue vs expenses</h6>
                    <small>Daily trend this week</small>
                </div>
                <div class="d-flex gap-3">
                    <span style="display:flex;align-items:center;gap:4px;font-size:10px;color:#94a3b8;">
                        <span style="width:8px;height:8px;border-radius:2px;background:#10b981;display:inline-block;"></span>Revenue
                    </span>
                    <span style="display:flex;align-items:center;gap:4px;font-size:10px;color:#94a3b8;">
                        <span style="width:8px;height:8px;border-radius:2px;background:#ef4444;display:inline-block;"></span>Expenses
                    </span>
                </div>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="revenueExpenseTrendChart"
                        role="img"
                        aria-label="Line chart showing daily revenue vs expenses this week">
                        Revenue and expense trend over the past 7 days.
                    </canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Service Demand Trends --}}
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-slate-800">Service demand trends</h6>
                    <small>Service usage popularity this week</small>
                </div>
                <div class="d-flex gap-3">
                    <span style="display:flex;align-items:center;gap:4px;font-size:10px;color:#94a3b8;">
                        <span style="width:8px;height:8px;border-radius:2px;background:#60a5fa;display:inline-block;"></span>Full service
                    </span>
                    <span style="display:flex;align-items:center;gap:4px;font-size:10px;color:#94a3b8;">
                        <span style="width:8px;height:8px;border-radius:2px;background:#a78bfa;display:inline-block;"></span>Self service
                    </span>
                </div>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="serviceDemandChart"
                        role="img"
                        aria-label="Line chart showing service demand trends this week">
                        Service demand by type over the past 7 days.
                    </canvas>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════
     CHARTS ROW 3 — Staff Attendance + Laundries Status
══════════════════════════════════════════ --}}
<div class="row g-3 mb-3">

    {{-- Staff Attendance Trends --}}
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-people text-info me-2"></i>Staff attendance trends
                </h6>
                <small>All branches daily attendance</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="staffAttendanceTrendsChart"
                        role="img"
                        aria-label="Line chart showing staff attendance trends per branch">
                        Staff attendance per branch over the past 7 days.
                    </canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Daily Profit Trend --}}
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-graph-up text-success me-2"></i>Daily profit trend
                </h6>
                <small>Last 7 days profit analysis</small>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="profitTrendChart"
                        role="img"
                        aria-label="Line chart showing daily profit over the past 7 days">
                        Daily profit trend for the past 7 days.
                    </canvas>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════
     SATISFACTION RATINGS — Services & Branches
══════════════════════════════════════════ --}}
{{-- Service Satisfaction Widget --}}
<div class="satisfaction-widget collapsed" id="serviceSatisfactionWidget">
    <div class="satisfaction-header" id="serviceHeader">
        <h3>
            <i class="bi bi-star-fill"></i>
            <span>Service Ratings</span>
        </h3>
        <div class="satisfaction-controls">
            <button class="satisfaction-btn" id="serviceMinimizeBtn" title="Minimize">
                <i class="bi bi-dash"></i>
            </button>
            <button class="satisfaction-btn" id="serviceCloseBtn" title="Close">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>
    <div class="satisfaction-body" id="serviceBody">
        @php
            $serviceRatingsData = \App\Models\CustomerRating::whereNotNull('laundry_id')
                ->with('laundry.service')
                ->get()
                ->groupBy(function($item) {
                    return $item->laundry && $item->laundry->service ? $item->laundry->service->name : 'Unknown';
                })
                ->map(function($group, $serviceName) {
                    return [
                        'name' => $serviceName,
                        'rating' => round($group->avg('rating'), 1),
                        'reviews' => $group->count(),
                    ];
                })
                ->sortByDesc('reviews')
                ->take(10)
                ->values();
            $serviceSatisfaction = $serviceRatingsData->count() > 0 ? $serviceRatingsData->toArray() : [
                ['name' => 'No ratings yet', 'rating' => 0, 'reviews' => 0],
            ];
            $serviceOverallRating = $serviceRatingsData->count() > 0 ? round($serviceRatingsData->avg('rating'), 1) : 0;
        @endphp
        <div class="satisfaction-overall">
            <div class="satisfaction-score">{{ number_format($serviceOverallRating, 1) }}</div>
            <div class="satisfaction-stars">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= floor($serviceOverallRating))
                        <i class="bi bi-star-fill"></i>
                    @elseif($i == ceil($serviceOverallRating) && $serviceOverallRating - floor($serviceOverallRating) >= 0.5)
                        <i class="bi bi-star-half"></i>
                    @else
                        <i class="bi bi-star"></i>
                    @endif
                @endfor
            </div>
            <div class="satisfaction-label">Overall Rating</div>
        </div>
        
        @foreach($serviceSatisfaction as $service)
        <div class="satisfaction-item">
            <div class="satisfaction-item-name">{{ $service['name'] }}</div>
            <div class="satisfaction-item-rating">
                <div class="satisfaction-item-stars">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= floor($service['rating']))
                            <i class="bi bi-star-fill"></i>
                        @elseif($i == ceil($service['rating']) && $service['rating'] - floor($service['rating']) >= 0.5)
                            <i class="bi bi-star-half"></i>
                        @else
                            <i class="bi bi-star"></i>
                        @endif
                    @endfor
                </div>
                <span class="satisfaction-item-score">{{ number_format($service['rating'], 1) }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Branch Satisfaction Widget --}}
<div class="satisfaction-widget collapsed" id="branchSatisfactionWidget">
    <div class="satisfaction-header" id="branchHeader">
        <h3>
            <i class="bi bi-building"></i>
            <span>Branch Ratings</span>
        </h3>
        <div class="satisfaction-controls">
            <button class="satisfaction-btn" id="branchMinimizeBtn" title="Minimize">
                <i class="bi bi-dash"></i>
            </button>
            <button class="satisfaction-btn" id="branchCloseBtn" title="Close">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>
    <div class="satisfaction-body" id="branchBody">
        @php
            $branchRatingsData = \App\Models\CustomerRating::whereNotNull('branch_id')
                ->with('branch')
                ->get()
                ->groupBy('branch_id')
                ->map(function($group) {
                    $branch = $group->first()->branch;
                    return [
                        'name' => $branch ? $branch->name : 'Unknown',
                        'rating' => round($group->avg('rating'), 1),
                        'reviews' => $group->count(),
                    ];
                })
                ->sortByDesc('reviews')
                ->values();
            $branchSatisfaction = $branchRatingsData->count() > 0 ? $branchRatingsData->toArray() : [
                ['name' => 'No ratings yet', 'rating' => 0, 'reviews' => 0],
            ];
            $branchOverallRating = $branchRatingsData->count() > 0 ? round($branchRatingsData->avg('rating'), 1) : 0;
        @endphp
        <div class="satisfaction-overall">
            <div class="satisfaction-score">{{ number_format($branchOverallRating, 1) }}</div>
            <div class="satisfaction-stars">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= floor($branchOverallRating))
                        <i class="bi bi-star-fill"></i>
                    @elseif($i == ceil($branchOverallRating) && $branchOverallRating - floor($branchOverallRating) >= 0.5)
                        <i class="bi bi-star-half"></i>
                    @else
                        <i class="bi bi-star"></i>
                    @endif
                @endfor
            </div>
            <div class="satisfaction-label">Overall Rating</div>
        </div>
        
        @foreach($branchSatisfaction as $branch)
        <div class="satisfaction-item">
            <div class="satisfaction-item-name">{{ $branch['name'] }}</div>
            <div class="satisfaction-item-rating">
                <div class="satisfaction-item-stars">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= floor($branch['rating']))
                            <i class="bi bi-star-fill"></i>
                        @elseif($i == ceil($branch['rating']) && $branch['rating'] - floor($branch['rating']) >= 0.5)
                            <i class="bi bi-star-half"></i>
                        @else
                            <i class="bi bi-star"></i>
                        @endif
                    @endfor
                </div>
                <span class="satisfaction-item-score">{{ number_format($branch['rating'], 1) }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>


{{-- Old Top Customers section removed - now in People & Rankings section below --}}

{{-- ══════════════════════════════════════════
     DASHBOARD WIDGETS — Pipeline, Maps, etc.
══════════════════════════════════════════ --}}
@include('admin.dashboard_widgets')

{{-- People & Rankings Section - Dark Theme Cards --}}
@include('admin.dashboard_people_rankings')

{{-- Inventory Stock Monitor Widget removed - now in People & Rankings section above --}}


{{-- ══════════════════════════════════════════
     MODALS
══════════════════════════════════════════ --}}
<div id="routeDetailsPanel" class="route-details-panel" style="display:none;"></div>

{{-- Fullscreen Map Modal --}}
<div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content" style="background:#0f172a;">
            <div class="modal-header" style="background:#0f172a;border-color:#334155;">
                <h5 class="modal-title fw-bold" style="color:#f1f5f9;">Logistics command center</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-warning" id="modalMultiRouteBtn" style="display:none;" onclick="getOptimizedMultiRoute()">
                        <i class="bi bi-route me-1"></i>Optimize (<span id="modalSelectedCount">0</span>)
                    </button>
                    <button class="btn btn-sm btn-info" onclick="autoRouteAllVisible()">
                        <i class="bi bi-magic me-1"></i>Auto-optimize
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-0">
                <div id="modalLogisticsMap" style="height:100%;width:100%;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Laundries Breakdown Modal --}}
<div class="modal fade" id="laundriesBreakdownModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:#0f172a;border:1px solid #334155;">
            <div class="modal-header" style="border-color:#334155;">
                <div>
                    <h5 class="modal-title fw-bold" style="color:#f1f5f9;">
                        <i class="bi bi-basket3 text-primary me-2"></i>Today's laundries breakdown
                    </h5>
                    <small style="color:#94a3b8;">{{ now()->format('l, F j, Y') }}</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $todayStart = today();
                    $todayEnd   = today()->endOfDay();
                    $branchId = request('branch_id');

                    $laundriesByStatus = \App\Models\Laundry::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->whereBetween('created_at', [$todayStart, $todayEnd])
                        ->selectRaw('status, COUNT(*) as count')
                        ->groupBy('status')
                        ->get()->pluck('count', 'status');

                    $laundriesByBranch = \App\Models\Laundry::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->whereBetween('created_at', [$todayStart, $todayEnd])
                        ->selectRaw('branch_id, COUNT(*) as count, SUM(total_amount) as revenue')
                        ->groupBy('branch_id')
                        ->with('branch')->get();

                    $totalLaundries = $laundriesByStatus->sum();

                    $recentLaundries = \App\Models\Laundry::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->whereBetween('created_at', [$todayStart, $todayEnd])
                        ->with(['customer:id,name', 'service:id,name', 'branch:id,name'])
                        ->orderByDesc('created_at')
                        ->limit(10)
                        ->get(['id','tracking_number','customer_id','service_id','branch_id','status','total_amount','created_at']);

                    $statusConfig = [
                        'received'  => ['label' => 'Received',  'color' => '#60a5fa', 'icon' => 'bi-inbox-fill'],
                        'ready'     => ['label' => 'Ready',     'color' => '#22d3ee', 'icon' => 'bi-check-circle-fill'],
                        'paid'      => ['label' => 'Paid',      'color' => '#10b981', 'icon' => 'bi-credit-card-fill'],
                        'completed' => ['label' => 'Completed', 'color' => '#34d399', 'icon' => 'bi-check2-all'],
                        'cancelled' => ['label' => 'Cancelled', 'color' => '#f87171', 'icon' => 'bi-x-circle-fill'],
                    ];
                @endphp

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.3);">
                            <div style="color:#94a3b8;font-size:0.65rem;text-transform:uppercase;margin-bottom:4px;">Total laundries</div>
                            <div style="font-size:1.5rem;font-weight:700;color:#60a5fa;">{{ $totalLaundries }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);">
                            <div style="color:#94a3b8;font-size:0.65rem;text-transform:uppercase;margin-bottom:4px;">Completed</div>
                            <div style="font-size:1.5rem;font-weight:700;color:#10b981;">{{ $laundriesByStatus->get('completed', 0) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:rgba(34,211,238,0.1);border:1px solid rgba(34,211,238,0.3);">
                            <div style="color:#94a3b8;font-size:0.65rem;text-transform:uppercase;margin-bottom:4px;">In progress</div>
                            <div style="font-size:1.5rem;font-weight:700;color:#22d3ee;">{{ $laundriesByStatus->get('received', 0) + $laundriesByStatus->get('ready', 0) }}</div>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mb-2" style="color:#f1f5f9;font-size:0.8rem;">Status breakdown</h6>
                <div class="row g-2 mb-3">
                    @foreach($statusConfig as $key => $cfg)
                    @php $cnt = $laundriesByStatus->get($key, 0); @endphp
                    <div class="col-md-4">
                        <div class="p-2 rounded d-flex align-items-center justify-content-between"
                            style="background:rgba(255,255,255,0.04);border-left:3px solid {{ $cfg['color'] }};">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi {{ $cfg['icon'] }}" style="color:{{ $cfg['color'] }};font-size:1rem;"></i>
                                <div>
                                    <div style="font-size:0.72rem;font-weight:600;color:#f1f5f9;">{{ $cfg['label'] }}</div>
                                    <div style="font-size:0.6rem;color:#475569;">{{ $totalLaundries > 0 ? round(($cnt/$totalLaundries)*100, 1) : 0 }}%</div>
                                </div>
                            </div>
                            <div style="font-size:1rem;font-weight:700;color:{{ $cfg['color'] }};">{{ $cnt }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($laundriesByBranch->count() > 0)
                <h6 class="fw-bold mb-2" style="color:#f1f5f9;font-size:0.8rem;">Branch breakdown</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr>
                            <th style="font-size:0.7rem;">Branch</th>
                            <th class="text-center" style="font-size:0.7rem;">Orders</th>
                            <th class="text-end" style="font-size:0.7rem;">Revenue</th>
                            <th class="text-end" style="font-size:0.7rem;">%</th>
                        </tr></thead>
                        <tbody>
                        @foreach($laundriesByBranch as $item)
                        <tr>
                            <td style="font-size:0.75rem;color:#f1f5f9;">{{ $item->branch->name ?? '—' }}</td>
                            <td class="text-center"><span class="badge bg-warning">{{ $item->count }}</span></td>
                            <td class="text-end text-success fw-bold" style="font-size:0.75rem;">₱{{ number_format($item->revenue, 0) }}</td>
                            <td class="text-end" style="font-size:0.72rem;color:#94a3b8;">{{ $totalLaundries > 0 ? round(($item->count/$totalLaundries)*100, 1) : 0 }}%</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                @if($recentLaundries->count() > 0)
                <h6 class="fw-bold mb-2 mt-3" style="color:#f1f5f9;font-size:0.8rem;">Recent orders today</h6>
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="font-size:0.75rem;">
                        <thead>
                            <tr style="border-color:#334155;">
                                <th style="color:#94a3b8;font-weight:500;">Tracking #</th>
                                <th style="color:#94a3b8;font-weight:500;">Customer</th>
                                <th style="color:#94a3b8;font-weight:500;">Service</th>
                                <th style="color:#94a3b8;font-weight:500;">Branch</th>
                                <th style="color:#94a3b8;font-weight:500;">Status</th>
                                <th class="text-end" style="color:#94a3b8;font-weight:500;">Amount</th>
                                <th style="color:#94a3b8;font-weight:500;">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($recentLaundries as $l)
                        @php $sc = $statusConfig[$l->status] ?? ['color'=>'#94a3b8','label'=>ucfirst($l->status)]; @endphp
                        <tr style="border-color:#1e293b;">
                            <td style="color:#60a5fa;font-family:monospace;">{{ $l->tracking_number ?? '—' }}</td>
                            <td style="color:#f1f5f9;">{{ $l->customer->name ?? '—' }}</td>
                            <td style="color:#cbd5e1;">{{ $l->service->name ?? '—' }}</td>
                            <td style="color:#94a3b8;">{{ $l->branch->name ?? '—' }}</td>
                            <td>
                                <span class="badge" style="background:{{ $sc['color'] }}20;color:{{ $sc['color'] }};font-size:0.65rem;border:1px solid {{ $sc['color'] }}40;">{{ $sc['label'] }}</span>
                            </td>
                            <td class="text-end" style="color:#10b981;font-weight:600;">₱{{ number_format($l->total_amount, 0) }}</td>
                            <td style="color:#64748b;">{{ $l->created_at->format('h:i A') }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                @if($totalLaundries === 0)
                <div class="text-center py-5">
                    <i class="bi bi-basket3" style="font-size:2.5rem;color:#334155;"></i>
                    <p style="color:#475569;margin-top:8px;font-size:0.8rem;">No laundries recorded today</p>
                </div>
                @endif
            </div>
            <div class="modal-footer" style="border-color:#334155;">
                <a href="{{ route('admin.laundries.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-list me-1"></i>View all laundries
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Revenue Breakdown Modal --}}
<div class="modal fade" id="revenueBreakdownModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-cash-coin text-success me-2"></i>Today's revenue breakdown
                    </h5>
                    <small class="text-muted">{{ now()->format('l, F j, Y') }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $branchId = request('branch_id');
                    $laundryRevenue = \App\Models\Laundry::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->whereBetween('paid_at', [$todayStart ?? today(), $todayEnd ?? today()->endOfDay()])
                        ->whereIn('status', ['paid', 'completed'])->sum('total_amount');
                    $retailRevenue  = \App\Models\RetailSale::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->whereDate('created_at', today())->sum('total_amount');
                    $totalRevModal  = $laundryRevenue + $retailRevenue;

                    $revenueByService = \App\Models\Laundry::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->whereBetween('paid_at', [today(), today()->endOfDay()])
                        ->whereIn('status', ['paid', 'completed'])
                        ->with([
                            'service:id,name',
                            'service.supplies.category',
                            'promotion:id,name',
                            'promotion.promotionItems.inventoryItem.category',
                            'customer:id,name',
                            'inventoryItems:id,name,category_id',
                            'inventoryItems.category:id,name',
                        ])
                        ->orderByDesc('paid_at')
                        ->get()
                        ->map(function($laundry) use ($totalRevModal) {
                            $loads = max(1, $laundry->number_of_loads ?? 1);

                            $addonItems = [];
                            foreach ($laundry->inventoryItems as $i) {
                                $addonItems[] = ['name' => $i->name, 'category' => $i->category->name ?? '', 'qty' => (float) $i->pivot->quantity];
                            }
                            $addonsCount = array_sum(array_map(fn($i) => (int) round($i['qty']), $addonItems));

                            $allItems = $addonItems;
                            foreach (($laundry->service?->supplies ?? collect()) as $supply) {
                                $qty = (float) $supply->pivot->quantity_required * $loads;
                                $found = false;
                                foreach ($allItems as &$row) {
                                    if ($row['name'] === $supply->name) { $row['qty'] += $qty; $found = true; break; }
                                } unset($row);
                                if (!$found) $allItems[] = ['name' => $supply->name, 'category' => $supply->category->name ?? '', 'qty' => $qty];
                            }
                            foreach (($laundry->promotion?->promotionItems ?? collect()) as $promoItem) {
                                if (!$promoItem->is_active || !$promoItem->inventoryItem) continue;
                                $qty  = (float) $promoItem->quantity_per_use * $loads;
                                $name = $promoItem->inventoryItem->name;
                                $cat  = $promoItem->inventoryItem->category->name ?? '';
                                $found = false;
                                foreach ($allItems as &$row) {
                                    if ($row['name'] === $name) { $row['qty'] += $qty; $found = true; break; }
                                } unset($row);
                                if (!$found) $allItems[] = ['name' => $name, 'category' => $cat, 'qty' => $qty];
                            }

                            return [
                                'id'            => $laundry->id,
                                'customer_name' => $laundry->customer->name ?? 'N/A',
                                'service_name'  => $laundry->service->name ?? $laundry->promotion->name ?? 'N/A',
                                'is_promo'      => !$laundry->service && $laundry->promotion,
                                'loads'         => $laundry->number_of_loads ?? 0,
                                'addons'        => $addonsCount,
                                'detergent'     => array_sum(array_column(array_filter($allItems, fn($i) => $i['category'] === 'DETERGENT'), 'qty')),
                                'fabcon'        => array_sum(array_column(array_filter($allItems, fn($i) => $i['category'] === 'FABRIC CONDTIONER'), 'qty')),
                                'bleach'        => array_sum(array_column(array_filter($allItems, fn($i) => $i['category'] === 'BLEACH'), 'qty')),
                                'plastics'      => array_sum(array_column(array_filter($allItems, fn($i) => $i['category'] === 'PACKAGING PLASTICS'), 'qty')),
                                'revenue'       => $laundry->total_amount,
                                'percentage'    => $totalRevModal > 0 ? round(($laundry->total_amount / $totalRevModal) * 100, 1) : 0,
                            ];
                        });
                @endphp

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="p-3 rounded border">
                            <div class="text-muted small text-uppercase mb-1">Total revenue</div>
                            <div class="fs-5 fw-bold text-success">₱{{ number_format($totalRevModal, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded border">
                            <div class="text-muted small text-uppercase mb-1">Laundry services</div>
                            <div class="fs-5 fw-bold text-primary">&#8369;{{ number_format($laundryRevenue, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded border">
                            <div class="text-muted small text-uppercase mb-1">Retail sales</div>
                            <div class="fs-5 fw-bold text-warning">₱{{ number_format($retailRevenue, 2) }}</div>
                        </div>
                    </div>
                </div>

                @if($revenueByService->count() > 0)
                <h6 class="fw-bold mb-2">
                    <i class="bi bi-table me-1"></i>Services laundry breakdown
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover" style="font-size:0.72rem;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Service</th>
                                <th class="text-center">Add-ons</th>
                                <th class="text-center">Loads/Pieces</th>
                                <th class="text-center">Detergent</th>
                                <th class="text-center">Fab Con</th>
                                <th class="text-center">Bleach</th>
                                <th class="text-center">Plastics</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">%</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($revenueByService as $svc)
                        <tr>
                            <td><span class="badge bg-primary">#{{ $svc['id'] }}</span></td>
                            <td>{{ $svc['customer_name'] }}</td>
                            <td>
                                {{ $svc['service_name'] }}
                                @if($svc['is_promo'])<span class="badge ms-1" style="background:rgba(139,92,246,0.15);color:#7c3aed;font-size:0.65rem;">Promo</span>@endif
                            </td>
                            <td class="text-center">{{ $svc['addons'] ?: '—' }}</td>
                            <td class="text-center">{{ $svc['loads'] }}</td>
                            <td class="text-center">{{ $svc['detergent'] > 0 ? (int)round($svc['detergent']) : '—' }}</td>
                            <td class="text-center">{{ $svc['fabcon'] > 0 ? (int)round($svc['fabcon']) : '—' }}</td>
                            <td class="text-center">{{ $svc['bleach'] > 0 ? (int)round($svc['bleach']) : '—' }}</td>
                            <td class="text-center">{{ $svc['plastics'] > 0 ? (int)round($svc['plastics']) : '—' }}</td>
                            <td class="text-end fw-bold text-success">₱{{ number_format($svc['revenue'], 2) }}</td>
                            <td class="text-end text-muted">{{ $svc['percentage'] }}%</td>
                        </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-active fw-bold">
                                <td colspan="9" class="text-end">Total Laundry Revenue:</td>
                                <td class="text-end text-success">₱{{ number_format($laundryRevenue, 2) }}</td>
                                <td class="text-end text-muted">{{ $totalRevModal > 0 ? round(($laundryRevenue / $totalRevModal) * 100, 1) : 0 }}%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox" style="font-size:2rem;opacity:0.3;"></i>
                    <p class="small mt-2">No service data for today</p>
                </div>
                @endif
            </div>
            <div class="modal-footer" style="border-color:#334155;">
                <a href="{{ route('admin.finance.reports.profit-loss') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-graph-up me-1"></i>View full report
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Profit Breakdown Modal --}}
<div class="modal fade" id="profitBreakdownModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:#0f172a;border:1px solid #334155;">
            <div class="modal-header" style="border-color:#334155;">
                <div>
                    <h5 class="modal-title fw-bold" style="color:#f1f5f9;">
                        <i class="bi bi-graph-up-arrow text-success me-2"></i>Today's profit breakdown
                    </h5>
                    <small style="color:#94a3b8;">{{ now()->format('l, F j, Y') }}</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $branchId = request('branch_id');
                    $profitTotalRev  = \App\Models\Laundry::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->whereBetween('paid_at', [today(), today()->endOfDay()])
                        ->whereIn('status', ['paid', 'completed'])->sum('total_amount')
                        + \App\Models\RetailSale::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->whereDate('created_at', today())->sum('total_amount');
                    $profitTotalExp  = \App\Models\Expense::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->whereDate('expense_date', today())->sum('amount');
                    $profitNet       = $profitTotalRev - $profitTotalExp;
                    $profitMarginMod = $profitTotalRev > 0 ? round(($profitNet / $profitTotalRev) * 100, 1) : 0;
                    $expensesByCategory = \App\Models\Expense::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->whereDate('expense_date', today())
                        ->selectRaw('expense_category_id, SUM(amount) as total')
                        ->groupBy('expense_category_id')->with('category')->get();
                @endphp

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);">
                            <div style="color:#94a3b8;font-size:0.65rem;text-transform:uppercase;margin-bottom:4px;">Total revenue</div>
                            <div style="font-size:1.4rem;font-weight:700;color:#10b981;">₱{{ number_format($profitTotalRev, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);">
                            <div style="color:#94a3b8;font-size:0.65rem;text-transform:uppercase;margin-bottom:4px;">Total expenses</div>
                            <div style="font-size:1.4rem;font-weight:700;color:#f87171;">₱{{ number_format($profitTotalExp, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:rgba(139,92,246,0.1);border:1px solid rgba(139,92,246,0.3);">
                            <div style="color:#94a3b8;font-size:0.65rem;text-transform:uppercase;margin-bottom:4px;">Net profit</div>
                            <div style="font-size:1.4rem;font-weight:700;color:{{ $profitNet >= 0 ? '#10b981' : '#f87171' }};">₱{{ number_format($profitNet, 2) }}</div>
                            <div style="font-size:0.65rem;color:#475569;">Margin: {{ $profitMarginMod }}%</div>
                        </div>
                    </div>
                </div>

                @if($expensesByCategory->count() > 0)
                <h6 class="fw-bold mb-2" style="color:#f1f5f9;font-size:0.8rem;">Expense breakdown</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr>
                            <th style="font-size:0.7rem;">Category</th>
                            <th class="text-end" style="font-size:0.7rem;">Amount</th>
                            <th class="text-end" style="font-size:0.7rem;">% of expenses</th>
                        </tr></thead>
                        <tbody>
                        @foreach($expensesByCategory as $exp)
                        <tr>
                            <td style="font-size:0.75rem;color:#f1f5f9;">{{ $exp->category->name ?? 'Uncategorized' }}</td>
                            <td class="text-end text-danger fw-bold" style="font-size:0.75rem;">₱{{ number_format($exp->total, 2) }}</td>
                            <td class="text-end" style="font-size:0.72rem;color:#94a3b8;">{{ $profitTotalExp > 0 ? round(($exp->total/$profitTotalExp)*100, 1) : 0 }}%</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-receipt" style="font-size:2rem;color:#334155;"></i>
                    <p style="font-size:0.75rem;color:#475569;margin-top:8px;margin-bottom:0;">No expenses recorded today</p>
                </div>
                @endif
            </div>
            <div class="modal-footer" style="border-color:#334155;">
                <a href="{{ route('admin.finance.reports.profit-loss') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-graph-up me-1"></i>View full report
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Pending Pickups Modal --}}
<div class="modal fade" id="pendingPickupsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="background:#0f172a;border:1px solid #334155;">
            <div class="modal-header" style="border-color:#334155;">
                <div>
                    <h5 class="modal-title fw-bold" style="color:#f1f5f9;">
                        <i class="bi bi-truck text-warning me-2"></i>Pending pickup requests
                    </h5>
                    <small style="color:#94a3b8;">Awaiting acceptance and assignment</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $branchId = request('branch_id');
                    $pendingPickups = \App\Models\PickupRequest::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->where('status', 'pending')
                        ->with(['customer', 'branch'])
                        ->orderBy('created_at', 'desc')->get();
                @endphp
                @if($pendingPickups->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead><tr>
                            <th style="font-size:0.7rem;">ID</th>
                            <th style="font-size:0.7rem;">Customer</th>
                            <th style="font-size:0.7rem;">Branch</th>
                            <th style="font-size:0.7rem;">Pickup address</th>
                            <th style="font-size:0.7rem;">Date</th>
                            <th class="text-end" style="font-size:0.7rem;">Fee</th>
                            <th style="font-size:0.7rem;"></th>
                        </tr></thead>
                        <tbody>
                        @foreach($pendingPickups as $pickup)
                        <tr>
                            <td><span class="badge bg-warning">#{{ $pickup->id }}</span></td>
                            <td style="font-size:0.75rem;color:#f1f5f9;">
                                {{ $pickup->customer->name ?? '—' }}
                                <div style="font-size:0.62rem;color:#475569;">{{ $pickup->contact_phone ?? $pickup->phone_number ?? '' }}</div>
                            </td>
                            <td style="font-size:0.75rem;color:#f1f5f9;">{{ $pickup->branch->name ?? '—' }}</td>
                            <td style="font-size:0.72rem;color:#94a3b8;max-width:200px;">{{ Str::limit($pickup->pickup_address, 45) }}</td>
                            <td style="font-size:0.72rem;color:#f1f5f9;">{{ $pickup->preferred_date->format('M d, Y') }}</td>
                            <td class="text-end text-success fw-bold" style="font-size:0.75rem;">₱{{ number_format($pickup->total_fee, 2) }}</td>
                            <td>
                                <a href="{{ route('admin.pickups.show', $pickup->id) }}" class="dash-btn dash-btn-info" style="font-size:0.6rem;padding:3px 8px;">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-truck" style="font-size:2.5rem;color:#334155;"></i>
                    <p style="font-size:0.8rem;color:#475569;margin-top:8px;margin-bottom:0;">No pending pickup requests</p>
                </div>
                @endif
            </div>
            <div class="modal-footer" style="border-color:#334155;">
                <a href="{{ route('admin.pickups.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-list me-1"></i>View all pickups
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Floating Action Button (FAB) for Quick Actions --}}
<button class="fab" data-bs-toggle="dropdown" aria-expanded="false" title="Quick Actions">
    <i class="bi bi-lightning-charge-fill"></i>
</button>
<ul class="dropdown-menu dropdown-menu-end shadow-lg" style="min-width: 240px;">
    <li><h6 class="dropdown-header"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h6></li>
    <li><hr class="dropdown-divider"></li>
    
    {{-- Laundry Actions --}}
    @if(Route::has('admin.laundries.create'))
    <li>
        <a class="dropdown-item" href="{{ route('admin.laundries.create') }}">
            <i class="bi bi-plus-circle text-primary me-2"></i>New Laundry Order
        </a>
    </li>
    @endif
    @if(Route::has('admin.customers.create'))
    <li>
        <a class="dropdown-item" href="{{ route('admin.customers.create') }}">
            <i class="bi bi-person-plus text-success me-2"></i>Add Customer
        </a>
    </li>
    @endif
    @if(Route::has('admin.pickups.index'))
    <li>
        <a class="dropdown-item" href="{{ route('admin.pickups.index') }}">
            <i class="bi bi-truck text-info me-2"></i>Pickup Requests
        </a>
    </li>
    @endif
    
    <li><hr class="dropdown-divider"></li>
    <li><h6 class="dropdown-header" style="font-size:0.75rem;color:#94a3b8;"><i class="bi bi-box-seam me-2"></i>Inventory Actions</h6></li>
    
    {{-- Inventory Quick Actions --}}
    @if(Route::has('admin.inventory.purchases.create'))
    <li>
        <a class="dropdown-item" href="{{ route('admin.inventory.purchases.create') }}">
            <i class="bi bi-cart-plus text-warning me-2"></i>Reorder Stock
        </a>
    </li>
    @endif
    @if(Route::has('admin.inventory.distributions.create'))
    <li>
        <a class="dropdown-item" href="{{ route('admin.inventory.distributions.create') }}">
            <i class="bi bi-arrow-left-right text-info me-2"></i>Transfer Stock
        </a>
    </li>
    @endif
    @if(Route::has('admin.inventory.adjustments.create'))
    <li>
        <a class="dropdown-item" href="{{ route('admin.inventory.adjustments.create') }}">
            <i class="bi bi-plus-square text-success me-2"></i>Add Stock
        </a>
    </li>
    @endif
    @if(Route::has('admin.inventory.index'))
    <li>
        <a class="dropdown-item" href="{{ route('admin.inventory.index') }}">
            <i class="bi bi-eye text-primary me-2"></i>View Details
        </a>
    </li>
    @endif
    @if(Route::has('admin.inventory.adjustments.index'))
    <li>
        <a class="dropdown-item" href="{{ route('admin.inventory.adjustments.index') }}">
            <i class="bi bi-sliders text-purple me-2"></i>Adjust Stock
        </a>
    </li>
    @endif
    <li>
        <a class="dropdown-item" href="#" onclick="window.print(); return false;">
            <i class="bi bi-printer text-secondary me-2"></i>Print Report
        </a>
    </li>
    
    <li><hr class="dropdown-divider"></li>
    
    {{-- Other Actions --}}
    @if(Route::has('admin.finance.expenses.create'))
    <li>
        <a class="dropdown-item" href="{{ route('admin.finance.expenses.create') }}">
            <i class="bi bi-receipt text-danger me-2"></i>Add Expense
        </a>
    </li>
    @endif
    @if(Route::has('admin.staff.index'))
    <li>
        <a class="dropdown-item" href="{{ route('admin.staff.index') }}">
            <i class="bi bi-people text-purple me-2"></i>Staff Management
        </a>
    </li>
    @endif
    <li><hr class="dropdown-divider"></li>
    @if(Route::has('admin.finance.reports.profit-loss'))
    <li>
        <a class="dropdown-item" href="{{ route('admin.finance.reports.profit-loss') }}">
            <i class="bi bi-graph-up text-cyan me-2"></i>Financial Reports
        </a>
    </li>
    @endif
</ul>

@endsection


@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/leaflet/leaflet.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/leaflet/MarkerCluster.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/leaflet/MarkerCluster.Default.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    <style>
        /* Floating Action Button (FAB) */
        .fab {
            position: fixed;
            top: 24px;
            right: 30px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1050;
            font-size: 18px;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
            transition: box-shadow 0.3s ease, transform 0.1s ease;
            user-select: none;
            touch-action: none;
        }

        .fab.dragging {
            cursor: grabbing;
            transform: scale(1.1);
            box-shadow: 0 8px 40px rgba(102, 126, 234, 0.6);
        }

        .fab:hover:not(.dragging) {
            transform: scale(1.05);
            box-shadow: 0 6px 30px rgba(102, 126, 234, 0.6);
        }

        /* FAB Dropdown Menu Styling */
        .fab + .dropdown-menu {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            padding: 0.5rem 0;
            background: #1e293b;
            margin-bottom: 10px;
        }

        .fab + .dropdown-menu .dropdown-header {
            color: #f1f5f9;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 0.75rem 1rem;
        }

        .fab + .dropdown-menu .dropdown-item {
            color: #cbd5e1;
            padding: 0.6rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .fab + .dropdown-menu .dropdown-item:hover {
            background: rgba(99, 102, 241, 0.1);
            color: #f1f5f9;
            padding-left: 1.25rem;
        }

        .fab + .dropdown-menu .dropdown-item i {
            width: 20px;
            font-size: 1rem;
        }

        .fab + .dropdown-menu .dropdown-divider {
            border-color: rgba(255, 255, 255, 0.1);
            margin: 0.5rem 0;
        }

        /* Responsive FAB */
        @media (max-width: 768px) {
            .fab {
                width: 48px;
                height: 48px;
                top: 20px;
                right: 20px;
                font-size: 18px;
            }
        }

        /* Satisfaction Widgets */
        .satisfaction-widget {
            position: fixed;
            width: 240px;
            background: var(--card-bg, #ffffff);
            border: 1px solid var(--border-color, rgba(0,0,0,0.08));
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            z-index: 1040;
            overflow: hidden;
            transition: all 0.3s ease;
            user-select: none;
        }

        .satisfaction-widget.collapsed {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            cursor: pointer;
        }

        .satisfaction-widget.collapsed .satisfaction-header {
            padding: 0;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            justify-content: center;
        }

        .satisfaction-widget.collapsed .satisfaction-header h3 {
            font-size: 18px;
        }

        .satisfaction-widget.collapsed .satisfaction-header h3 span {
            display: none;
        }

        .satisfaction-widget.collapsed .satisfaction-controls {
            display: none;
        }

        .satisfaction-widget.collapsed .satisfaction-body {
            display: none;
        }

        #serviceSatisfactionWidget {
            bottom: 180px;
            right: 24px;
            left: auto;
        }

        #branchSatisfactionWidget {
            bottom: 90px;
            right: 24px;
            left: auto;
        }

        @media (max-width: 576px) {
            .satisfaction-widget {
                width: 180px;
            }

            #serviceSatisfactionWidget {
                bottom: 160px;
                right: 8px;
                left: auto;
            }

            #branchSatisfactionWidget {
                bottom: 75px;
                right: 8px;
                left: auto;
            }
        }

        .satisfaction-widget.minimized {
            height: auto;
        }

        .satisfaction-widget.hidden {
            opacity: 0;
            transform: translateY(20px);
            pointer-events: none;
        }

        .satisfaction-widget.dragging {
            cursor: grabbing;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
        }

        .satisfaction-header {
            padding: 10px 12px;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            cursor: move;
            display: flex;
            align-items: center;
            justify-content: space-between;
            user-select: none;
        }

        .satisfaction-header h3 {
            font-size: 12px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .satisfaction-controls {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .satisfaction-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            transition: background 0.2s;
        }

        .satisfaction-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .satisfaction-body {
            padding: 12px;
            max-height: 280px;
            overflow-y: auto;
        }

        .satisfaction-body.minimized {
            display: none;
        }

        .satisfaction-overall {
            text-align: center;
            padding: 12px;
            background: rgba(251, 191, 36, 0.1);
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .satisfaction-score {
            font-size: 28px;
            font-weight: 700;
            color: #fbbf24;
            line-height: 1;
        }

        .satisfaction-stars {
            color: #fbbf24;
            font-size: 14px;
            margin: 6px 0;
        }

        .satisfaction-label {
            font-size: 10px;
            opacity: 0.7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .satisfaction-item {
            padding: 8px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .satisfaction-item:last-child {
            border-bottom: none;
        }

        .satisfaction-item-name {
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .satisfaction-item-rating {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .satisfaction-item-stars {
            display: flex;
            gap: 1px;
            color: #fbbf24;
            font-size: 10px;
        }

        .satisfaction-item-stars .bi-star {
            color: #d1d5db;
        }

        .satisfaction-item-score {
            font-size: 11px;
            font-weight: 700;
            color: #fbbf24;
        }

        @media (max-width: 768px) {
            .satisfaction-widget {
                width: 180px;
            }
            #branchSatisfactionWidget {
                right: 8px;
                left: auto;
            }
            #serviceSatisfactionWidget {
                right: 8px;
                left: auto;
            }
        }
    </style>

    {{-- FAB Drag Functionality --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fab = document.querySelector('.fab');
        if (fab) {
            let isDragging = false;
            let currentX, currentY, initialX, initialY;
            let xOffset = 0, yOffset = 0;

            fab.addEventListener('mousedown', dragStart);
            fab.addEventListener('touchstart', dragStart);
            document.addEventListener('mousemove', drag);
            document.addEventListener('touchmove', drag);
            document.addEventListener('mouseup', dragEnd);
            document.addEventListener('touchend', dragEnd);

            function dragStart(e) {
                if (e.type === 'touchstart') {
                    initialX = e.touches[0].clientX - xOffset;
                    initialY = e.touches[0].clientY - yOffset;
                } else {
                    initialX = e.clientX - xOffset;
                    initialY = e.clientY - yOffset;
                }

                if (e.target === fab || fab.contains(e.target)) {
                    isDragging = true;
                    fab.classList.add('dragging');
                }
            }

            function drag(e) {
                if (isDragging) {
                    e.preventDefault();
                    
                    if (e.type === 'touchmove') {
                        currentX = e.touches[0].clientX - initialX;
                        currentY = e.touches[0].clientY - initialY;
                    } else {
                        currentX = e.clientX - initialX;
                        currentY = e.clientY - initialY;
                    }

                    xOffset = currentX;
                    yOffset = currentY;

                    setTranslate(currentX, currentY, fab);
                }
            }

            function dragEnd(e) {
                if (isDragging) {
                    initialX = currentX;
                    initialY = currentY;
                    isDragging = false;
                    fab.classList.remove('dragging');
                }
            }

            function setTranslate(xPos, yPos, el) {
                el.style.transform = `translate3d(${xPos}px, ${yPos}px, 0)`;
            }
        }

        // Satisfaction Widgets Functionality
        function initSatisfactionWidget(widgetId, headerId, minimizeBtnId, closeBtnId, bodyId, storageKey) {
            const widget = document.getElementById(widgetId);
            const header = document.getElementById(headerId);
            const minimizeBtn = document.getElementById(minimizeBtnId);
            const closeBtn = document.getElementById(closeBtnId);
            const body = document.getElementById(bodyId);

            if (!widget || !header) return;

            // Click to expand/collapse
            widget.addEventListener('click', function(e) {
                if (widget.classList.contains('collapsed') && !e.target.closest('.satisfaction-btn')) {
                    widget.classList.remove('collapsed');
                }
            });

            // Dragging (only when expanded)
            let isDragging = false;
            let currentX, currentY, initialX, initialY;
            let xOffset = 0, yOffset = 0;

            header.addEventListener('mousedown', dragStart);
            header.addEventListener('touchstart', dragStart);
            document.addEventListener('mousemove', drag);
            document.addEventListener('touchmove', drag);
            document.addEventListener('mouseup', dragEnd);
            document.addEventListener('touchend', dragEnd);

            function dragStart(e) {
                if (widget.classList.contains('collapsed')) return;
                
                if (e.type === 'touchstart') {
                    initialX = e.touches[0].clientX - xOffset;
                    initialY = e.touches[0].clientY - yOffset;
                } else {
                    initialX = e.clientX - xOffset;
                    initialY = e.clientY - yOffset;
                }

                if (e.target === header || header.contains(e.target)) {
                    if (!e.target.closest('.satisfaction-btn')) {
                        isDragging = true;
                        widget.classList.add('dragging');
                    }
                }
            }

            function drag(e) {
                if (isDragging) {
                    e.preventDefault();
                    
                    if (e.type === 'touchmove') {
                        currentX = e.touches[0].clientX - initialX;
                        currentY = e.touches[0].clientY - initialY;
                    } else {
                        currentX = e.clientX - initialX;
                        currentY = e.clientY - initialY;
                    }

                    xOffset = currentX;
                    yOffset = currentY;

                    setTranslate(currentX, currentY, widget);
                }
            }

            function dragEnd(e) {
                if (isDragging) {
                    initialX = currentX;
                    initialY = currentY;
                    isDragging = false;
                    widget.classList.remove('dragging');
                }
            }

            function setTranslate(xPos, yPos, el) {
                el.style.transform = `translate3d(${xPos}px, ${yPos}px, 0)`;
            }

            // Minimize/Maximize
            if (minimizeBtn && body) {
                minimizeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    widget.classList.toggle('minimized');
                    body.classList.toggle('minimized');
                    const icon = minimizeBtn.querySelector('i');
                    if (widget.classList.contains('minimized')) {
                        icon.className = 'bi bi-plus';
                        minimizeBtn.title = 'Maximize';
                    } else {
                        icon.className = 'bi bi-dash';
                        minimizeBtn.title = 'Minimize';
                    }
                });
            }

            // Close - collapse back to button
            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    widget.classList.add('collapsed');
                    widget.classList.remove('minimized');
                    if (body) body.classList.remove('minimized');
                });
            }
        }

        // Initialize both widgets
        initSatisfactionWidget('serviceSatisfactionWidget', 'serviceHeader', 'serviceMinimizeBtn', 'serviceCloseBtn', 'serviceBody', 'serviceSatisfactionClosed');
        initSatisfactionWidget('branchSatisfactionWidget', 'branchHeader', 'branchMinimizeBtn', 'branchCloseBtn', 'branchBody', 'branchSatisfactionClosed');
    });
    </script>
@endpush


@push('scripts')
    <script src="{{ asset('assets/chart.js/chart.umd.min.js') }}"></script>
    <script src="{{ asset('assets/leaflet/leaflet.js') }}"></script>
    <script src="{{ asset('assets/leaflet/leaflet.markercluster.js') }}"></script>
    <script src="{{ asset('assets/js/utils/tabFix.js') }}"></script>
    <script src="{{ asset('assets/js/utils/dataStabilizer.js') }}"></script>
    <script src="{{ asset('assets/js/utils/postLoadOptimizer.js') }}"></script>
    <script src="{{ asset('assets/js/utils/performanceMonitorWidget.js') }}"></script>

    {{-- Window globals consumed by admin.js modules --}}
    <script>
        window.BRANCHES              = @json($stats['branches'] ?? []);
        window.PENDING_PICKUPS       = @json($stats['pendingPickups'] ?? []);
        window.DASHBOARD_STATS       = @json($stats ?? []);
        window.CURRENT_DATE_RANGE    = '{{ $currentFilters["date_range"] ?? "last_7_days" }}';
        window.STAFF_ATTENDANCE_DATA = @json($stats['staffAttendanceTrends'] ?? []);
        window.LAUNDRIES_STATUS_DATA = @json($stats['laundriesStatusTrends'] ?? []);
        window.SERVICE_DEMAND_DATA   = @json($stats['serviceDemandTrends'] ?? []);
        window.BRANCH_REVENUE_DATA   = @json($stats['branchRevenueComparison'] ?? []);
        window.CUSTOMER_GROWTH_DATA  = @json($stats['customerGrowthTrend'] ?? []);
        
        // Debug: Log the laundries status data
        console.log('=== LAUNDRIES STATUS DATA DEBUG ===');
        console.log('Data exists:', !!window.LAUNDRIES_STATUS_DATA);
        if (window.LAUNDRIES_STATUS_DATA) {
            console.log('Labels:', window.LAUNDRIES_STATUS_DATA.labels);
            console.log('Datasets count:', window.LAUNDRIES_STATUS_DATA.datasets ? window.LAUNDRIES_STATUS_DATA.datasets.length : 0);
            if (window.LAUNDRIES_STATUS_DATA.datasets) {
                window.LAUNDRIES_STATUS_DATA.datasets.forEach(ds => {
                    console.log('Dataset:', ds.label, 'Total:', ds.data.reduce((a,b) => a+b, 0));
                });
            }
        }
        console.log('===================================');
    </script>

    {{-- Financial Charts --}}
    <script>
        (function initFinancialCharts() {
            if (typeof Chart === 'undefined') {
                return setTimeout(initFinancialCharts, 200);
            }

            const GRID  = 'rgba(255,255,255,0.04)';
            const TICK  = '#334155';
            const BASE  = {
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
            const SCALES_PESO = {
                x: { grid: { color: GRID, drawBorder: false }, ticks: { color: TICK, font: { size: 10 } } },
                y: { beginAtZero: true, grid: { color: GRID, drawBorder: false },
                     ticks: { color: TICK, font: { size: 10 }, callback: v => '₱' + (v / 1000).toFixed(0) + 'k' } }
            };
            const SCALES_COUNT = {
                x: { grid: { color: GRID, drawBorder: false }, ticks: { color: TICK, font: { size: 10 } } },
                y: { beginAtZero: true, grid: { color: GRID, drawBorder: false },
                     ticks: { color: TICK, font: { size: 10 }, stepSize: 1, callback: v => Number.isInteger(v) ? v : '' } }
            };

            function mkLine(id, datasets, scales, aria) {
                const el = document.getElementById(id);
                if (!el) return;
                if (!el.getAttribute('aria-label')) el.setAttribute('aria-label', aria);
                new Chart(el, { type: 'line', data: { labels: window.BRANCH_REVENUE_DATA?.labels || [], datasets }, options: { ...BASE, scales } });
            }

            // Revenue vs Expense Trend
            const revEl = document.getElementById('revenueExpenseTrendChart');
            if (revEl) {
                const ret = window.DASHBOARD_STATS?.revenueExpenseTrend || {};
                new Chart(revEl, {
                    type: 'line',
                    data: {
                        labels: ret.labels || [],
                        datasets: [
                            { label: 'Revenue (₱)', data: ret.revenue || [],  borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)', borderWidth: 2, fill: true, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#10b981', pointBorderColor: '#0b1120', pointBorderWidth: 2 },
                            { label: 'Expenses (₱)', data: ret.expenses || [], borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.06)',  borderWidth: 2, fill: true, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#ef4444', pointBorderColor: '#0b1120', pointBorderWidth: 2, borderDash: [5, 4] }
                        ]
                    },
                    options: { ...BASE, scales: SCALES_PESO }
                });
            }

            // Branch Revenue Comparison chart removed - moved to Analytics page

            // Service Demand
            const sdEl = document.getElementById('serviceDemandChart');
            if (sdEl) {
                const sd = window.SERVICE_DEMAND_DATA || {};
                const sdColors = ['#60a5fa','#a78bfa','#fbbf24'];
                if (!sd.datasets || sd.datasets.length === 0) {
                    sdEl.closest('.card-body-modern').innerHTML =
                        '<div class="text-center py-4" style="color:#475569;"><i class="bi bi-graph-up" style="font-size:1.5rem;opacity:.3;"></i><p style="font-size:0.72rem;margin-top:6px;">No service data yet</p></div>';
                } else {
                    new Chart(sdEl, {
                        type: 'line',
                        data: {
                            labels: sd.labels || [],
                            datasets: sd.datasets.map((ds, i) => ({
                                label: ds.label,
                                data: ds.data,
                                borderColor: sdColors[i % sdColors.length],
                                backgroundColor: i === 0 ? 'rgba(96,165,250,0.08)' : 'transparent',
                                fill: i === 0,
                                borderWidth: 2,
                                tension: 0.4,
                                pointRadius: 3,
                                pointBackgroundColor: sdColors[i % sdColors.length],
                                pointBorderColor: '#0b1120',
                                pointBorderWidth: 2,
                                borderDash: i > 0 ? [5, 4] : []
                            }))
                        },
                        options: { ...BASE, scales: SCALES_COUNT }
                    });
                }
            }

            // Staff Attendance Trends
            const saEl = document.getElementById('staffAttendanceTrendsChart');
            if (saEl && window.STAFF_ATTENDANCE_DATA?.datasets?.length) {
                const sa = window.STAFF_ATTENDANCE_DATA;
                const saColors = ['#818cf8','#f59e0b','#10b981','#06b6d4','#8b5cf6'];
                new Chart(saEl, {
                    type: 'line',
                    data: {
                        labels: sa.labels || [],
                        datasets: sa.datasets.map((ds, i) => ({
                            label: ds.label,
                            data: ds.data,
                            borderColor: saColors[i % saColors.length],
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.4,
                            pointRadius: 3,
                            pointBackgroundColor: saColors[i % saColors.length],
                            pointBorderColor: '#0b1120',
                            pointBorderWidth: 2,
                            borderDash: i > 0 ? [5, 4] : []
                        }))
                    },
                    options: {
                        ...BASE,
                        plugins: {
                            legend: { display: true, position: 'bottom', labels: { usePointStyle: true, padding: 12, font: { size: 10 }, color: '#475569' } },
                            tooltip: { backgroundColor: 'rgba(10,14,28,0.95)', padding: 10 }
                        },
                        scales: SCALES_COUNT
                    }
                });
            }

            // Profit Trend
            const ptEl = document.getElementById('profitTrendChart');
            if (ptEl && window.DASHBOARD_STATS?.profitTrend) {
                const pd = window.DASHBOARD_STATS.profitTrend;
                new Chart(ptEl, {
                    type: 'line',
                    data: {
                        labels: pd.labels || [],
                        datasets: [{ label: 'Profit (₱)', data: pd.data || [], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)', borderWidth: 2, fill: true, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#10b981', pointBorderColor: '#0b1120', pointBorderWidth: 2 }]
                    },
                    options: { ...BASE, scales: SCALES_PESO }
                });
            }
        })();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('button[data-bs-toggle="pill"]').forEach(function (el) {
                const tab = new bootstrap.Tab(el);
                el.addEventListener('click', function (e) { e.preventDefault(); tab.show(); });
            });
        });

        window.addEventListener('load', function () {
            setTimeout(function () {
                if (typeof window.initializeDashboardData === 'function') {
                    requestIdleCallback(() => {
                        window.initializeDashboardData(window.BRANCHES, window.DASHBOARD_STATS);
                    }, { timeout: 2000 });
                }
            }, 300);
        });
    </script>

    <script type="module" src="{{ asset('assets/js/admin.js') }}"></script>
@endpush
