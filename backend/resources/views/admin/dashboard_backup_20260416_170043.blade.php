@extends('admin.layouts.app')

@section('title', 'Dashboard Overview')

@section('content')

    {{-- KPI ROW: 5 cards --}}
    <div class="kpi-row mb-4">

        <div class="kpi-card blue">
            <div class="kpi-label">Today's laundries</div>
            <div class="kpi-value">{{ $stats['todayLaundries'] }}</div>
            <div class="kpi-footer">
                <div class="kpi-sub">Total: {{ number_format($stats['totalLaundries'] ?? 0) }}</div>
                <div class="kpi-change {{ $stats['laundriesChange'] >= 0 ? 'up' : 'down' }}">
                    ↑ {{ abs($stats['laundriesChange']) }}%
                </div>
            </div>
        </div>

        <div class="kpi-card green">
            <div class="kpi-label">Today's revenue</div>
            <div class="kpi-value">₱{{ number_format($stats['todayRevenue'], 0) }}</div>
            <div class="kpi-footer">
                <div class="kpi-sub">Month: ₱{{ number_format($stats['thisMonthRevenue'] ?? 0, 0) }}</div>
                <div class="kpi-change {{ $stats['revenueChange'] >= 0 ? 'up' : 'down' }}">
                    ↑ {{ abs($stats['revenueChange']) }}%
                </div>
            </div>
        </div>

        <div class="kpi-card green">
            <div class="kpi-label">Today's profit</div>
            <div class="kpi-value">₱{{ number_format($stats['profitMetrics']['profit'], 0) }}</div>
            <div class="kpi-footer">
                <div class="kpi-sub">Margin: {{ $stats['profitMetrics']['margin'] }}%</div>
                <div class="kpi-change {{ $stats['profitMetrics']['profit'] >= 0 ? 'up' : 'down' }}">
                    ↑ {{ abs($stats['profitMetrics']['margin']) }}%
                </div>
            </div>
        </div>

        <div class="kpi-card amber">
            <div class="kpi-label">Active customers</div>
            <div class="kpi-value">{{ number_format($stats['activeCustomers']) }}</div>
            <div class="kpi-footer">
                <div class="kpi-sub">+{{ $stats['newCustomersToday'] ?? 0 }} new today</div>
                <div class="kpi-change up">↑ 3%</div>
            </div>
        </div>

        <div class="kpi-card red">
            <div class="kpi-label">Unclaimed items</div>
            <div class="kpi-value">{{ $stats['unclaimedLaundry'] }}</div>
            <div class="kpi-footer">
                <div class="kpi-sub">Est. loss ₱{{ number_format($stats['estimatedUnclaimedLoss'] ?? 0, 0) }}</div>
                <div class="kpi-change crit">CRITICAL</div>
            </div>
        </div>
    </div>



    {{-- TABS --}}
    <div class="tab-wrapper mb-4">
        <div class="tab-bar">
            <button class="tab-btn active" onclick="switchTab('overview',this)">
                <i class="bi bi-speedometer2"></i> Overview
            </button>
            <button class="tab-btn" onclick="switchTab('laundries',this)">
                <i class="bi bi-basket"></i> Laundries
            </button>
            <button class="tab-btn" onclick="switchTab('customers',this)">
                <i class="bi bi-people"></i> Customers
            </button>
            <button class="tab-btn" onclick="switchTab('operations',this)">
                <i class="bi bi-truck"></i> Operations
            </button>
            <button class="tab-btn" onclick="switchTab('financial',this)">
                <i class="bi bi-cash-stack"></i> Financial
            </button>
            <button class="tab-btn" onclick="switchTab('inventory',this)">
                <i class="bi bi-box2"></i> Inventory
            </button>
        </div>

        {{-- OVERVIEW TAB --}}
        <div id="tab-overview" class="tab-panel active" style="padding: 14px;">
            <div class="grid-2" style="margin-bottom: 10px;">
        {{-- Revenue & Expense Trend --}}
        <div class="col-lg-6">
            <div class="modern-card shadow-sm h-100">
                <div class="card-header-modern bg-transparent border-0">
                    <h6 class="mb-0 fw-800 text-slate-800">Revenue & Expense Trend</h6>
                    <small class="text-muted">Daily revenue vs expenses this week</small>
                </div>
                <div class="card-body-modern">
                    <canvas id="revenueExpenseTrendChart" height="200"></canvas>
                </div>
            </div>
        </div>

        {{-- Branch Revenue Comparison --}}
        <div class="col-lg-6">
            <div class="modern-card shadow-sm h-100">
                <div class="card-header-modern bg-transparent border-0">
                    <h6 class="mb-0 fw-800 text-slate-800">Branch Revenue Comparison</h6>
                    <small class="text-muted">Revenue by branch this month</small>
                </div>
                <div class="card-body-modern">
                    @php
                        $branchRevenues = \App\Models\Branch::withSum(['laundries' => function($q) {
                            $q->whereIn('status', ['paid', 'completed'])
                              ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()]);
                        }], 'total_amount')
                        ->orderByDesc('laundries_sum_total_amount')
                        ->limit(5)
                        ->get();

                        $maxRevenue = $branchRevenues->max('laundries_sum_total_amount') ?: 1;
                        $barColors = ['#3b82f6', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b'];
                    @endphp
                    @foreach($branchRevenues as $index => $branch)
                        @php
                            $revenue = $branch->laundries_sum_total_amount ?? 0;
                            $percentage = round(($revenue / $maxRevenue) * 100);
                        @endphp
                        <div class="horizontal-bar-chart">
                            <div class="bar-label">
                                <span>{{ $branch->name }}</span>
                                <span class="fw-700">₱{{ number_format($revenue, 0) }}</span>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: {{ $percentage }}%; background: {{ $barColors[$index] }};">
                                    {{ $percentage }}%
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         PROFIT TREND + FINANCIAL SUMMARY ROW
    ════════════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="modern-card shadow-sm h-100">
                <div class="card-header-modern bg-transparent border-0">
                    <h6 class="mb-0 fw-800 text-slate-800"><i class="bi bi-graph-up text-success me-2"></i>Daily Profit Trend</h6>
                    <small class="text-muted">Last 7 days profit analysis</small>
                </div>
                <div class="card-body-modern">
                    <canvas id="profitTrendChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="modern-card shadow-sm h-100">
                <div class="card-header-modern bg-transparent border-0">
                    <h6 class="mb-0 fw-800 text-slate-800"><i class="bi bi-cash-stack text-primary me-2"></i>Financial Summary (Today)</h6>
                    <small class="text-muted">Income, Expenses & Profit</small>
                </div>
                <div class="card-body-modern">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="financial-metric">
                                <div class="metric-label text-muted small mb-2">Total Income</div>
                                <div class="metric-value text-success fw-800" style="font-size: 1.3rem;">
                                    ₱{{ number_format($stats['financialBreakdown']['total_income'], 0) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="financial-metric">
                                <div class="metric-label text-muted small mb-2">Total Expenses</div>
                                <div class="metric-value text-danger fw-800" style="font-size: 1.3rem;">
                                    ₱{{ number_format($stats['financialBreakdown']['total_expense'], 0) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="financial-metric">
                                <div class="metric-label text-muted small mb-2">Net Profit</div>
                                <div class="metric-value {{ $stats['financialBreakdown']['net_profit'] >= 0 ? 'text-success' : 'text-danger' }} fw-800" style="font-size: 1.3rem;">
                                    ₱{{ number_format($stats['financialBreakdown']['net_profit'], 0) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="financial-metric">
                                <div class="metric-label text-muted small mb-2">Profit Margin</div>
                                <div class="metric-value {{ $stats['financialBreakdown']['profit_margin'] >= 0 ? 'text-success' : 'text-danger' }} fw-800" style="font-size: 1.3rem;">
                                    {{ $stats['financialBreakdown']['profit_margin'] }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         QUICK ACTIONS
    ════════════════════════════════════════════════════════════════ --}}
    <div class="modern-card shadow-sm mb-4">
        <div class="card-header-modern border-0 py-2">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-800 text-slate-800 fs-6">Quick Actions</h6>
                <span class="badge bg-primary-blue bg-opacity-10 text-primary-blue small">
                    <i class="bi bi-lightning"></i>
                </span>
            </div>
        </div>
        <div class="card-body-modern py-2">
            <div class="row g-2">
                @php
                    $quickActions = [
                        ['route' => 'admin.laundries.create', 'icon' => 'bi-plus-lg', 'label' => 'New Laundry', 'color' => 'blue'],
                        ['route' => 'admin.inventory.index', 'icon' => 'bi-boxes', 'label' => 'Inventory', 'color' => 'warning'],
                        ['route' => 'admin.branches.index', 'icon' => 'bi-building', 'label' => 'Branches', 'color' => 'success'],
                        ['route' => 'admin.employees.index', 'icon' => 'bi-user-tie', 'label' => 'Employees', 'color' => 'secondary'],
                        ['route' => 'admin.reports.index', 'icon' => 'bi-graph-up', 'label' => 'Reports', 'color' => 'dark'],
                        ['route' => 'admin.settings.index', 'icon' => 'bi-gear', 'label' => 'Settings', 'color' => 'primary'],
                    ];
                @endphp
                @foreach($quickActions as $action)
                    @if(Route::has($action['route']))
                    <div class="col-6 col-md-4 col-lg-2">
                        <a href="{{ route($action['route']) }}" class="btn btn-outline-{{ $action['color'] }} btn-sm w-100 d-flex flex-column align-items-center py-2 text-decoration-none">
                            <i class="bi {{ $action['icon'] }} fs-5 mb-1"></i>
                            <span class="small fw-600">{{ $action['label'] }}</span>
                        </a>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         RECENT TRANSACTIONS + BRANCH RANKINGS
    ════════════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        {{-- Recent Transactions Widget --}}
        <div class="col-lg-8">
            <div class="modern-card shadow-sm h-100">
                <div class="card-header-modern bg-transparent border-0">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="mb-0 fw-800 text-slate-800"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Transactions</h6>
                            <small class="text-muted">Latest financial activity</small>
                        </div>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            @php
                                $todayIncome = \App\Models\FinancialTransaction::where('type', 'income')
                                    ->whereDate('transaction_date', today())
                                    ->sum('amount');
                                $todayExpense = \App\Models\FinancialTransaction::where('type', 'expense')
                                    ->whereDate('transaction_date', today())
                                    ->sum('amount');
                            @endphp
                            <div class="text-end">
                                <small class="text-muted d-block" style="font-size: 0.7rem;">Today's Income</small>
                                <strong class="text-success" style="font-size: 0.85rem;">+₱{{ number_format($todayIncome, 0) }}</strong>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block" style="font-size: 0.7rem;">Today's Expense</small>
                                <strong class="text-danger" style="font-size: 0.85rem;">-₱{{ number_format($todayExpense, 0) }}</strong>
                            </div>
                            <a href="{{ route('admin.finance.ledger.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                <i class="bi bi-arrow-right me-1"></i>View All
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body-modern p-3">
                    @php
                        $recentTransactions = \App\Models\FinancialTransaction::with(['branch', 'creator'])
                            ->latest('transaction_date')
                            ->limit(6)
                            ->get();
                    @endphp
                    @if($recentTransactions->count() > 0)
                        <div class="transaction-list">
                            @foreach($recentTransactions as $transaction)
                                @php
                                    $isIncome = $transaction->type === 'income';
                                    $bgColor = $isIncome ? '#10B98115' : '#DC262615';
                                    $borderColor = $isIncome ? '#10B981' : '#DC2626';
                                    $textColor = $isIncome ? '#059669' : '#991b1b';
                                @endphp
                                <div class="transaction-item mb-3 p-3" style="background: linear-gradient(135deg, {{ $bgColor }}, transparent); border-left: 3px solid {{ $borderColor }}; border-radius: 8px; transition: all 0.3s ease;">
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <div class="d-flex align-items-start gap-3 flex-grow-1">
                                            <div style="font-size: 1.5rem;">
                                                @if($isIncome)
                                                    <i class="bi bi-arrow-down-circle" style="color: {{ $borderColor }};"></i>
                                                @else
                                                    <i class="bi bi-arrow-up-circle" style="color: {{ $borderColor }};"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1" style="min-width: 0;">
                                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                                    <h6 class="mb-0 fw-700" style="font-size: 0.95rem;">{{ Str::limit($transaction->description, 30) }}</h6>
                                                    <span class="badge {{ $isIncome ? 'bg-success' : 'bg-danger' }} bg-opacity-15 {{ $isIncome ? 'text-success' : 'text-danger' }}" style="font-size: 0.65rem; white-space: nowrap;">
                                                        {{ ucfirst(str_replace('_', ' ', $transaction->category)) }}
                                                    </span>
                                                </div>
                                                <small class="d-block text-muted" style="font-size: 0.75rem;">{{ $transaction->transaction_number }} • {{ $transaction->branch->name ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                        <div class="text-end" style="white-space: nowrap;">
                                            <strong style="color: {{ $textColor }}; font-size: 0.95rem; display: block;">
                                                {{ $isIncome ? '+' : '-' }}₱{{ number_format($transaction->amount, 0) }}
                                            </strong>
                                            <small class="text-muted d-block" style="font-size: 0.7rem;">{{ $transaction->transaction_date->format('M d, h:i A') }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-receipt text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-3">No transactions yet</p>
                            <small class="text-muted">Financial transactions will appear here</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Branch Performance Ranking Widget --}}
        <div class="col-lg-4">
            <div class="modern-card shadow-sm h-100">
                <div class="card-header-modern bg-transparent border-0">
                    <div>
                        <h6 class="mb-0 fw-800 text-slate-800"><i class="bi bi-trophy me-2 text-warning"></i>Branch Rankings</h6>
                        <small class="text-muted">Top performers - {{ now()->format('F Y') }}</small>
                    </div>
                </div>
                <div class="card-body-modern">
                    @php
                        $branchRankings = \App\Models\Branch::withSum(['laundries' => function($q) {
                            $q->whereIn('status', ['paid', 'completed'])
                              ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()]);
                        }], 'total_amount')
                        ->orderByDesc('laundries_sum_total_amount')
                        ->limit(5)
                        ->get();

                        $totalRevenue = $branchRankings->sum('laundries_sum_total_amount');
                        $rankIcons = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];
                        $rankColors = ['#fbbf24', '#94a3b8', '#cd7f32', '#64748b', '#94a3b8'];
                    @endphp

                    @forelse($branchRankings as $index => $branch)
                        @php
                            $revenue = $branch->laundries_sum_total_amount ?? 0;
                            $percentage = $totalRevenue > 0 ? round(($revenue / $totalRevenue) * 100, 1) : 0;
                        @endphp
                        <div class="branch-rank-item mb-3 p-3" style="background: linear-gradient(135deg, {{ $rankColors[$index] }}15, {{ $rankColors[$index] }}05); border-left: 3px solid {{ $rankColors[$index] }}; border-radius: 8px;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span style="font-size: 1.5rem;">{{ $rankIcons[$index] }}</span>
                                    <div>
                                        <h6 class="mb-0 fw-700">{{ $branch->name }}</h6>
                                        <small class="text-muted">Rank #{{ $index + 1 }}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <h6 class="mb-0 text-success fw-700">₱{{ number_format($revenue, 0) }}</h6>
                                    <small class="text-muted">{{ $percentage }}%</small>
                                </div>
                            </div>
                            <div class="progress" style="height: 6px; background: rgba(0,0,0,0.05);">
                                <div class="progress-bar" style="width: {{ $percentage }}%; background: {{ $rankColors[$index] }};"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-building text-muted" style="font-size: 2rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-2 mb-0">No branch data</p>
                        </div>
                    @endforelse

                    @if($branchRankings->count() > 0)
                        <div class="mt-3 pt-3 border-top">
                            <a href="{{ route('admin.branches.index') }}" class="btn btn-sm btn-outline-primary w-100 rounded-pill">
                                <i class="bi bi-building me-1"></i>View All Branches
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

{{-- ═══════════════════════════════════════════════════════════════════════════════════════════════════════════
     DETAILED TABS SECTION - COMMENTED OUT FOR STREAMLINED MULTI-BRANCH MONITORING DASHBOARD
     To enable detailed analytics, change @if(false) to @if(true) below
═══════════════════════════════════════════════════════════════════════════════════════════════════════════ --}}
@if(false)
    {{-- ═══════════════════════════════════════════════════════════
         STICKY TABS — must be a direct sibling of tab-content
    ════════════════════════════════════════════════════════════════ --}}
    <div class="modern-tabs-sticky" style="background: #0f172a;">
        <ul class="nav nav-segmented" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                    <i class="bi bi-speedometer2 me-2"></i>Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="laundries-tab" data-bs-toggle="pill" data-bs-target="#laundries" type="button" role="tab" aria-controls="laundries" aria-selected="false">
                    <i class="bi bi-basket me-2"></i>Laundries
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="customers-tab" data-bs-toggle="pill" data-bs-target="#customers" type="button" role="tab" aria-controls="customers" aria-selected="false">
                    <i class="bi bi-people me-2"></i>Customers
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="operations-tab" data-bs-toggle="pill" data-bs-target="#operations" type="button" role="tab" aria-controls="operations" aria-selected="false">
                    <i class="bi bi-gear me-2"></i>Operations
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="financial-tab" data-bs-toggle="pill" data-bs-target="#financial" type="button" role="tab" aria-controls="financial" aria-selected="false">
                    <i class="bi bi-cash-stack me-2"></i>Financial
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="inventory-tab" data-bs-toggle="pill" data-bs-target="#inventory" type="button" role="tab" aria-controls="inventory" aria-selected="false">
                    <i class="bi bi-box2 me-2"></i>Inventory
                </button>
            </li>
        </ul>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         TAB CONTENT — ALL 6 panes must live inside this single div
    ════════════════════════════════════════════════════════════════ --}}
    <div class="tab-content" style="padding-top: 1.5rem; min-height: 400px;">

        {{-- ── TAB 1: Overview ────────────────────────────────── --}}
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row g-4">
                {{-- Laundry Pipeline --}}
                <div class="col-lg-8">
                    <div class="modern-card shadow-sm h-100">
                        <div class="card-header-modern bg-transparent border-0">
                            <h6 class="mb-0 fw-800 text-slate-800"><i class="bi bi-cart-check me-2 text-primary"></i>Laundries Status</h6>
                            <small class="text-muted">7-day status trend</small>
                        </div>
                        <div class="card-body-modern">
                            <canvas id="laundryStatusTrendChart" height="200"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Unclaimed Breakdown --}}
                <div class="col-lg-4">
                    <div class="modern-card shadow-sm border-danger-soft">
                        <div class="card-header-modern bg-danger-soft">
                            <h6 class="mb-0 fw-800 text-danger">Unclaimed Items - Age Tracker</h6>
                            <small class="text-danger">Items by age since completion</small>
                        </div>
                        <div class="card-body-modern">
                            <div class="age-tracker-grid">
                                @php
                                    $ageCategories = [
                                        ['key' => 'within_7_days', 'label' => '0-7 Days', 'class' => 'age-0-7'],
                                        ['key' => '1_to_2_weeks', 'label' => '1-2 Weeks', 'class' => 'age-1-2'],
                                        ['key' => '2_to_4_weeks', 'label' => '2-4 Weeks', 'class' => 'age-2-4'],
                                        ['key' => 'over_1_month', 'label' => '>1 Month', 'class' => 'age-over'],
                                    ];
                                @endphp
                                @foreach($ageCategories as $category)
                                    <div class="age-box {{ $category['class'] }}">
                                        <div class="age-number">{{ $stats['unclaimedBreakdown'][$category['key']] ?? 0 }}</div>
                                        <div class="age-label">{{ $category['label'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="alert alert-danger mt-3 mb-0 bg-danger-soft border-0">
                                <div class="text-center">
                                    <strong class="d-block mb-1">Estimated Loss</strong>
                                    <h4 class="mb-0 text-danger fw-800">₱{{ number_format($stats['estimatedUnclaimedLoss'] ?? 0, 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Revenue Trend Chart --}}
                <div class="col-12">
                    <div class="modern-card shadow-sm">
                        <div class="card-header-modern bg-transparent border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-800 text-slate-800">Revenue Trend</h6>
                                <small class="text-muted">
                                    @php
                                        $rangeLabels = [
                                            'today'        => 'Today',
                                            'yesterday'    => 'Yesterday',
                                            'last_7_days'  => 'Last 7 days',
                                            'this_week'    => 'This week',
                                            'last_30_days' => 'Last 30 days',
                                            'this_month'   => 'This month',
                                            'last_month'   => 'Last month',
                                            'this_year'    => 'This year',
                                        ];
                                        $activeRangeLabel = $rangeLabels[$currentFilters['date_range'] ?? 'last_30_days'] ?? 'Last 30 days';
                                    @endphp
                                    {{ $activeRangeLabel }} performance
                                </small>
                            </div>
                        </div>
                        <div class="card-body-modern">
                            <div class="chart-container">
                                <canvas id="revenueChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pipeline by Branch --}}
                <div class="col-12">
                    <div class="bpb-wrapper modern-card shadow-sm overflow-hidden">
                        <div class="bpb-main-header">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bpb-header-icon"><i class="bi bi-shop"></i></div>
                                    <div>
                                        <h5 class="mb-0 fw-800 text-white">Pipeline by Branch</h5>
                                        <small class="text-white" style="opacity:.75;">Laundry status breakdown per branch</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    @php
                                        $bpbDefs = [
                                            'received'   => ['label'=>'Received',   'hex'=>'#60a5fa', 'icon'=>'bi-inbox-fill'],
                                            'ready'      => ['label'=>'Ready',      'hex'=>'#22d3ee', 'icon'=>'bi-check-circle-fill'],
                                            'paid'       => ['label'=>'Paid',       'hex'=>'#10b981', 'icon'=>'bi-credit-card-fill'],
                                            'completed'  => ['label'=>'Completed',  'hex'=>'#34d399', 'icon'=>'bi-check2-all'],
                                            'cancelled'  => ['label'=>'Cancelled',  'hex'=>'#f87171', 'icon'=>'bi-x-circle-fill'],
                                        ];
                                    @endphp
                                    <div class="bpb-legend d-none d-lg-flex">
                                        @foreach($bpbDefs as $key => $def)
                                            <div class="bpb-legend-item">
                                                <span class="bpb-legend-dot" style="background:{{ $def['hex'] }};"></span>
                                                <span>{{ $def['label'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <a href="{{ route('admin.branches.index') }}" class="btn btn-sm btn-light rounded-pill fw-600">
                                        <i class="bi bi-shop me-1"></i>Manage Branches
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body-modern">
                            @if(empty($stats['branchPipeline']))
                                <div class="text-center py-5">
                                    <div class="bpb-empty-icon mb-3"><i class="bi bi-shop"></i></div>
                                    <h6 class="text-muted fw-600">No branch data available</h6>
                                </div>
                            @else
                                <div class="row g-4">
                                    @php $bpbAccentColors = ['#3b82f6','#6366f1','#06b6d4','#10b981','#f59e0b','#ef4444']; @endphp
                                    @foreach($stats['branchPipeline'] as $branch)
                                        @php
                                            $accent = $bpbAccentColors[$loop->index % count($bpbAccentColors)];
                                            $branchTotal = max($branch['total'], 1);
                                        @endphp
                                        <div class="col-xl-4 col-md-6">
                                            <div class="bpb-card" style="--bpb-accent: {{ $accent }};">
                                                <div class="bpb-accent-bar"></div>
                                                <div class="bpb-card-header">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="bpb-avatar" style="background: linear-gradient(135deg, {{ $accent }}, {{ $accent }}aa);">
                                                            {{ strtoupper(substr($branch['name'], 0, 1)) }}
                                                        </div>
                                                        <div class="flex-grow-1 min-w-0">
                                                            <h6 class="mb-0 fw-700 bpb-branch-name">{{ $branch['name'] }}</h6>
                                                            <span class="bpb-total-badge mt-1 d-inline-block">
                                                                <i class="bi bi-basket me-1"></i>{{ $branch['total'] }} laundries
                                                            </span>
                                                        </div>
                                                        <a href="{{ route('admin.laundries.index', ['branch' => $branch['id']]) }}" class="bpb-view-link flex-shrink-0">
                                                            <i class="bi bi-box-arrow-up-right"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="bpb-stacked-bar-wrap">
                                                    <div class="bpb-stacked-bar">
                                                        @foreach($bpbDefs as $statusKey => $def)
                                                            @php $pct = round(($branch['statuses'][$statusKey] / $branchTotal) * 100, 1); @endphp
                                                            @if($pct > 0)
                                                                <div class="bpb-bar-seg" style="width:{{ $pct }}%;background:{{ $def['hex'] }};"
                                                                     data-bs-toggle="tooltip"
                                                                     data-bs-title="{{ $def['label'] }}: {{ $branch['statuses'][$statusKey] }} ({{ $pct }}%)"></div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="bpb-status-grid">
                                                    @foreach($bpbDefs as $statusKey => $def)
                                                        @php
                                                            $cnt = $branch['statuses'][$statusKey];
                                                            $pctTile = $branchTotal > 1 ? round(($cnt / $branchTotal) * 100) : 0;
                                                        @endphp
                                                        <a href="{{ route('admin.laundries.index', ['branch' => $branch['id'], 'status' => $statusKey]) }}"
                                                           class="bpb-status-tile text-decoration-none" style="--tile-color:{{ $def['hex'] }};">
                                                            <div class="bpb-tile-icon"><i class="bi {{ $def['icon'] }}"></i></div>
                                                            <div class="bpb-tile-count">{{ $cnt }}</div>
                                                            <div class="bpb-tile-label">{{ $def['label'] }}</div>
                                                            <div class="bpb-tile-pct">{{ $pctTile }}%</div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>{{-- /overview --}}

        {{-- ── TAB 2: Laundries ───────────────────────────────── --}}
        <div class="tab-pane fade" id="laundries" role="tabpanel">
            <div class="row g-4">

                {{-- Daily Laundry Count --}}
                <div class="col-lg-6">
                    <div class="daily-count-card">
                        <div class="daily-count-header">
                            <div class="daily-count-icon"><i class="bi bi-bar-chart-line"></i></div>
                            <div>
                                <h6 class="mb-0 fw-800 text-slate-800">Daily Laundry Count</h6>
                                <small class="text-muted">Laundries per day for selected period</small>
                            </div>
                        </div>
                        <div class="daily-chart-container">
                            <canvas id="dailyLaundryChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Payment Methods --}}
                <div class="col-lg-6">
                    <div class="payment-methods-card">
                        <div class="payment-header">
                            <div class="payment-icon"><i class="bi bi-credit-card"></i></div>
                            <div>
                                <h6 class="mb-0 fw-800 text-slate-800">Payment Methods</h6>
                                <small class="text-muted">Payment breakdown for selected period</small>
                            </div>
                        </div>
                        <div class="payment-methods-list">
                            @php
                                $paymentMethods = $stats['paymentMethods'] ?? [];
                                $paymentIcons = [
                                    'cash'  => ['icon' => 'bi-cash-coin',   'color' => '#10b981', 'bg' => '#ecfdf5'],
                                    'card'  => ['icon' => 'bi-credit-card', 'color' => '#3b82f6', 'bg' => '#eff6ff'],
                                    'gcash' => ['icon' => 'bi-phone',       'color' => '#f59e0b', 'bg' => '#fffbeb'],
                                    'bank'  => ['icon' => 'bi-bank',        'color' => '#8b5cf6', 'bg' => '#faf5ff'],
                                ];
                            @endphp
                            @forelse($paymentMethods as $payment)
                                @php
                                    $methodKey = strtolower($payment['method']);
                                    $iconData = $paymentIcons[$methodKey] ?? $paymentIcons['cash'];
                                @endphp
                                <div class="payment-method-item">
                                    <div class="payment-method-icon" style="background: {{ $iconData['bg'] }}; color: {{ $iconData['color'] }};">
                                        <i class="bi {{ $iconData['icon'] }}"></i>
                                    </div>
                                    <div class="payment-method-details">
                                        <div class="payment-method-name">{{ ucfirst($payment['method']) }}</div>
                                        <div class="payment-method-count">{{ $payment['count'] }} transactions</div>
                                    </div>
                                    <div>
                                        <div class="payment-method-amount">₱{{ number_format($payment['amount'], 0) }}</div>
                                        <div class="payment-method-percentage">{{ $payment['percentage'] }}%</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <i class="bi bi-credit-card text-muted fs-1 mb-2"></i>
                                    <p class="text-muted">No payment data available</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Top Services --}}
                <div class="col-lg-6">
                    <div class="top-services-card">
                        <div class="top-services-header">
                            <div class="top-services-icon"><i class="bi bi-star"></i></div>
                            <div>
                                <h6 class="mb-0 fw-800 text-slate-800">Top Services</h6>
                                <small class="text-muted">Most used services this period</small>
                            </div>
                        </div>
                        <div class="services-list">
                            @php
                                $topServices = $stats['topServices'] ?? [];
                                $serviceColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
                            @endphp
                            @forelse($topServices as $service)
                                @php $color = $serviceColors[($service['rank'] - 1) % count($serviceColors)]; @endphp
                                <div class="service-item">
                                    <div class="service-rank" style="background: {{ $color }}; color: white;">{{ $service['rank'] }}</div>
                                    <div class="service-details">
                                        <div class="service-name">{{ $service['name'] }}</div>
                                        <div class="service-usage">{{ $service['count'] }} times used</div>
                                        <div class="service-bar">
                                            <div class="service-bar-fill" style="width: {{ $service['percentage'] }}%; background: {{ $color }};" data-width="{{ $service['percentage'] }}"></div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <i class="bi bi-star text-muted fs-1 mb-2"></i>
                                    <p class="text-muted">No service data available</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Year-over-Year Revenue --}}
                <div class="col-lg-6">
                    <div class="yoy-revenue-card">
                        <div class="yoy-header">
                            <div class="yoy-icon"><i class="bi bi-graph-up-arrow"></i></div>
                            <div>
                                <h6 class="mb-0 fw-800 text-slate-800">Year-over-Year Revenue</h6>
                                <small class="text-muted">Last 5 years comparison</small>
                            </div>
                        </div>
                        <div class="yoy-chart-container">
                            <canvas id="yoyRevenueChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Service Type Summary Strips --}}
                @php
                    $scd = $stats['serviceChartData'] ?? [
                        'drop_off'     => ['labels'=>[],'counts'=>[],'revenues'=>[],'total'=>0],
                        'self_service' => ['labels'=>[],'counts'=>[],'revenues'=>[],'total'=>0],
                        'addon'        => ['labels'=>[],'counts'=>[],'revenues'=>[],'total'=>0],
                        'all_services' => [],
                        'grand_total'  => ['count'=>0,'revenue'=>0],
                    ];
                    $svcTypeConfig = [
                        'drop_off'     => ['label'=>'Full Service',  'icon'=>'bi-stars',       'gradient'=>'linear-gradient(135deg,#1e3a8a,#3b82f6)', 'hex'=>'#3b82f6','bg'=>'#eff6ff','border'=>'#bfdbfe'],
                        'self_service' => ['label'=>'Self Service',  'icon'=>'bi-person-gear', 'gradient'=>'linear-gradient(135deg,#4c1d95,#8b5cf6)', 'hex'=>'#8b5cf6','bg'=>'#faf5ff','border'=>'#ddd6fe'],
                        'addon'        => ['label'=>'Add-On',        'icon'=>'bi-plus-circle', 'gradient'=>'linear-gradient(135deg,#92400e,#f59e0b)', 'hex'=>'#f59e0b','bg'=>'#fffbeb','border'=>'#fde68a'],
                    ];
                @endphp
                @foreach(['drop_off','self_service','addon'] as $typeKey)
                    @php $tc = $svcTypeConfig[$typeKey]; $ts = $scd[$typeKey]; @endphp
                    <div class="col-md-4">
                        <div class="svc-summary-strip" style="--svc-hex:{{ $tc['hex'] }};--svc-bg:{{ $tc['bg'] }};--svc-border:{{ $tc['border'] }};">
                            <div class="svc-strip-icon-wrap" style="background:{{ $tc['gradient'] }};"><i class="bi {{ $tc['icon'] }}"></i></div>
                            <div class="flex-grow-1">
                                <div class="svc-strip-label">{{ $tc['label'] }}</div>
                                <div class="svc-strip-count">{{ number_format($ts['total']) }} laundries</div>
                            </div>
                            <div class="text-end">
                                @php $gc = $scd['grand_total']['count']; $pct = $gc > 0 ? round(($ts['total']/$gc)*100) : 0; @endphp
                                <div class="svc-strip-pct" style="color:{{ $tc['hex'] }};">{{ $pct }}%</div>
                                <div class="svc-strip-pct-label">of all laundries</div>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Full Service doughnut --}}
                <div class="col-lg-4">
                    <div class="modern-card shadow-sm h-100">
                        <div class="card-header-modern bg-transparent border-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="svc-chart-header-icon" style="background:linear-gradient(135deg,#1e3a8a,#3b82f6);"><i class="bi bi-stars"></i></div>
                                <div>
                                    <h6 class="mb-0 fw-800 text-slate-800">Full Service</h6>
                                    <small class="text-muted">Service breakdown by type</small>
                                </div>
                                <div class="ms-auto">
                                    <span class="svc-chart-total-badge" style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;">
                                        {{ number_format($scd['drop_off']['total']) }} orders
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body-modern">
                            @if(!empty($scd['drop_off']['labels']))
                                <div class="svc-donut-wrap">
                                    <canvas id="fullServiceChart"></canvas>
                                    <div class="svc-donut-center">
                                        <div class="svc-donut-num">{{ number_format($scd['drop_off']['total']) }}</div>
                                        <div class="svc-donut-lbl">Usage</div>
                                    </div>
                                </div>
                            @else
                                <div class="svc-empty-state"><i class="bi bi-stars"></i><p>No full service data</p></div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Self Service doughnut --}}
                <div class="col-lg-4">
                    <div class="modern-card shadow-sm h-100">
                        <div class="card-header-modern bg-transparent border-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="svc-chart-header-icon" style="background:linear-gradient(135deg,#4c1d95,#8b5cf6);"><i class="bi bi-person-gear"></i></div>
                                <div>
                                    <h6 class="mb-0 fw-800 text-slate-800">Self Service</h6>
                                    <small class="text-muted">Service breakdown by type</small>
                                </div>
                                <div class="ms-auto">
                                    <span class="svc-chart-total-badge" style="background:#faf5ff;color:#5b21b6;border-color:#ddd6fe;">
                                        {{ number_format($scd['self_service']['total']) }} orders
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body-modern">
                            @if(!empty($scd['self_service']['labels']))
                                <div class="svc-donut-wrap">
                                    <canvas id="selfServiceChart"></canvas>
                                    <div class="svc-donut-center">
                                        <div class="svc-donut-num">{{ number_format($scd['self_service']['total']) }}</div>
                                        <div class="svc-donut-lbl">Usage</div>
                                    </div>
                                </div>
                            @else
                                <div class="svc-empty-state"><i class="bi bi-person-gear"></i><p>No self service data</p></div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Add-On doughnut --}}
                <div class="col-lg-4">
                    <div class="modern-card shadow-sm h-100">
                        <div class="card-header-modern bg-transparent border-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="svc-chart-header-icon" style="background:linear-gradient(135deg,#92400e,#f59e0b);"><i class="bi bi-plus-circle"></i></div>
                                <div>
                                    <h6 class="mb-0 fw-800 text-slate-800">Add-Ons</h6>
                                    <small class="text-muted">Service breakdown by type</small>
                                </div>
                                <div class="ms-auto">
                                    <span class="svc-chart-total-badge" style="background:#fffbeb;color:#92400e;border-color:#fde68a;">
                                        {{ number_format($scd['addon']['total']) }} orders
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body-modern">
                            @if(!empty($scd['addon']['labels']))
                                <div class="svc-donut-wrap">
                                    <canvas id="addonServiceChart"></canvas>
                                    <div class="svc-donut-center">
                                        <div class="svc-donut-num">{{ number_format($scd['addon']['total']) }}</div>
                                        <div class="svc-donut-lbl">Usage</div>
                                    </div>
                                </div>
                            @else
                                <div class="svc-empty-state"><i class="bi bi-plus-circle"></i><p>No add-on data</p></div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- All Service Types table --}}
                <div class="col-lg-8">
                    <div class="modern-card shadow-sm">
                        <div class="card-header-modern bg-transparent border-0 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-0 fw-800 text-slate-800">All Service Types</h6>
                                <small class="text-muted">Ranked by laundry volume</small>
                            </div>
                            <a href="{{ route('admin.services.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                <i class="bi bi-gear me-1"></i>Manage Services
                            </a>
                        </div>
                        <div class="card-body-modern p-0">
                            @if(!empty($scd['all_services']))
                                <div class="table-responsive">
                                    <table class="table svc-table mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:32px;">#</th>
                                                <th>Service</th>
                                                <th>Category</th>
                                                <th class="text-center">Used</th>
                                                <th class="text-end">Revenue</th>
                                                <th style="width:130px;">Volume</th>
                                                <th style="width:32px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($scd['all_services'] as $i => $svc)
                                                @php $tc = $svcTypeConfig[$svc['category']] ?? $svcTypeConfig['drop_off']; @endphp
                                                <tr class="svc-table-row">
                                                    <td class="svc-rank-cell">{{ $i+1 }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="svc-icon-sm" style="background:{{ $tc['bg'] }};color:{{ $tc['hex'] }};"><i class="bi bi-tag-fill"></i></div>
                                                            <span class="svc-name-text">{{ $svc['name'] }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="svc-type-pill" style="background:{{ $tc['bg'] }};color:{{ $tc['hex'] }};border-color:{{ $tc['border'] }};">
                                                            <i class="bi {{ $tc['icon'] }}"></i>{{ $tc['label'] }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="fw-700">{{ number_format($svc['count']) }}</span>
                                                        <div class="svc-sub-pct">{{ $svc['count_pct'] }}%</div>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="fw-700 text-success">₱{{ number_format($svc['revenue'],0) }}</span>
                                                        <div class="svc-sub-pct">{{ $svc['revenue_pct'] }}%</div>
                                                    </td>
                                                    <td>
                                                        <div class="svc-bar-track">
                                                            <div class="svc-bar-fill" style="width:{{ $svc['count_pct'] }}%;background:{{ $tc['hex'] }};"></div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.laundries.index', ['service'=>$svc['id']]) }}" class="svc-row-link" title="View laundries">
                                                            <i class="bi bi-arrow-right-circle"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="svc-empty-state m-4">
                                    <i class="bi bi-tag"></i>
                                    <p>No services configured yet</p>
                                    <a href="{{ route('admin.services.index') }}" class="btn btn-sm btn-primary rounded-pill mt-2">Add Services</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Latest Laundries --}}
                <div class="col-lg-4">
                    <div class="modern-card shadow-sm h-100">
                        <div class="card-header-modern bg-transparent border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-800 text-slate-800">Latest</h6>
                                <small class="text-muted">Most recent laundries</small>
                            </div>
                            <a href="{{ route('admin.laundries.index') }}" class="btn btn-sm btn-outline-primary">All</a>
                        </div>
                        <div class="card-body-modern">
                            @php $latestLaundries = \App\Models\Laundry::with('customer')->latest()->limit(6)->get(); @endphp
                            @forelse($latestLaundries as $laundry)
                                <div class="recent-laundry-item {{ !$loop->last ? 'mb-2' : '' }}">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="laundry-status-badge status-{{ $laundry->status }} me-3">
                                                <i class="bi bi-circle-fill"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0" style="font-size:.82rem;">#{{ $laundry->laundry_number ?? $laundry->id }}</h6>
                                                <small class="text-muted">{{ $laundry->customer->name ?? 'Guest' }}</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div style="font-size:.82rem;font-weight:700;">₱{{ number_format($laundry->total_amount,0) }}</div>
                                            <small class="text-capitalize text-muted">{{ $laundry->status }}</small>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <i class="bi bi-basket text-muted fs-1 d-block mb-2"></i>
                                    <p class="text-muted">No laundries yet</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>{{-- /laundries --}}

        {{-- ── TAB 3: Customers ───────────────────────────────── --}}
        <div class="tab-pane fade" id="customers" role="tabpanel">
            <div class="row g-4">
                {{-- Customer Pipeline by Branch --}}
                <div class="col-12">
                    <div class="cbp-wrapper modern-card shadow-sm overflow-hidden">
                        <div class="cbp-main-header">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="cbp-header-icon"><i class="bi bi-people-fill"></i></div>
                                    <div>
                                        <h5 class="mb-0 fw-800 text-white">Customer Pipeline by Branch</h5>
                                        <small class="text-white" style="opacity:.75;">Walk-in vs Mobile customers per branch</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <div class="cbp-legend d-none d-lg-flex">
                                        <div class="cbp-legend-item"><span class="cbp-legend-dot" style="background:#34d399;"></span><span>Walk-In</span></div>
                                        <div class="cbp-legend-item"><span class="cbp-legend-dot" style="background:#818cf8;"></span><span>Self-Registered</span></div>
                                    </div>
                                    <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-light rounded-pill fw-600">
                                        <i class="bi bi-arrow-right-circle me-1"></i>All Customers
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body-modern">
                            @if(empty($stats['customerBranchPipeline']))
                                <div class="text-center py-5">
                                    <div class="cbp-empty-icon mb-3"><i class="bi bi-people"></i></div>
                                    <h6 class="text-muted fw-600">No customer branch data</h6>
                                </div>
                            @else
                                <div class="row g-4 align-items-stretch">
                                    <div class="col-lg-8">
                                        <div class="row g-3">
                                            @php $cbpAccents = ['#10b981','#6366f1','#06b6d4','#f59e0b','#ef4444','#8b5cf6']; @endphp
                                            @foreach($stats['customerBranchPipeline'] as $branch)
                                                @php
                                                    $accent = $cbpAccents[$loop->index % count($cbpAccents)];
                                                    $bTotal = max($branch['total'], 1);
                                                    $wiPct  = round(($branch['walk_in'] / $bTotal) * 100);
                                                    $mobPct = 100 - $wiPct;
                                                @endphp
                                                <div class="col-md-6">
                                                    <div class="cbp-card" style="--cbp-accent: {{ $accent }};">
                                                        <div class="cbp-accent-bar"></div>
                                                        <div class="cbp-card-header">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="cbp-avatar" style="background:linear-gradient(135deg,{{ $accent }},{{ $accent }}aa);">
                                                                    {{ strtoupper(substr($branch['name'], 0, 1)) }}
                                                                </div>
                                                                <div class="flex-grow-1 min-w-0">
                                                                    <h6 class="mb-0 fw-700 cbp-branch-name">{{ $branch['name'] }}</h6>
                                                                    <span class="cbp-total-badge mt-1 d-inline-block">
                                                                        <i class="bi bi-people me-1"></i>{{ $branch['total'] }} customers
                                                                    </span>
                                                                </div>
                                                                <a href="{{ route('admin.customers.index', ['branch' => $branch['id']]) }}" class="cbp-view-link flex-shrink-0">
                                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="cbp-bar-wrap">
                                                            <div class="cbp-stacked-bar">
                                                                @if($wiPct > 0)
                                                                    <div class="cbp-bar-seg" style="width:{{ $wiPct }}%;background:#34d399;" data-bs-toggle="tooltip" data-bs-title="Walk-In: {{ $branch['walk_in'] }} ({{ $wiPct }}%)"></div>
                                                                @endif
                                                                @if($mobPct > 0)
                                                                    <div class="cbp-bar-seg" style="width:{{ $mobPct }}%;background:#818cf8;" data-bs-toggle="tooltip" data-bs-title="Mobile: {{ $branch['mobile'] }} ({{ $mobPct }}%)"></div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="cbp-status-grid">
                                                            <a href="{{ route('admin.customers.index', ['branch_id' => $branch['id'], 'registration_type' => 'walk_in']) }}" class="cbp-status-tile text-decoration-none" style="--tile-color:#34d399;">
                                                                <div class="cbp-tile-icon"><i class="bi bi-person-walking"></i></div>
                                                                <div class="cbp-tile-count">{{ $branch['walk_in'] }}</div>
                                                                <div class="cbp-tile-label">Walk-In</div>
                                                                <div class="cbp-tile-pct">{{ $wiPct }}%</div>
                                                            </a>
                                                            <a href="{{ route('admin.customers.index', ['branch_id' => $branch['id'], 'registration_type' => 'self_registered']) }}" class="cbp-status-tile text-decoration-none" style="--tile-color:#818cf8;">
                                                                <div class="cbp-tile-icon"><i class="bi bi-phone-fill"></i></div>
                                                                <div class="cbp-tile-count">{{ $branch['mobile'] }}</div>
                                                                <div class="cbp-tile-label">Self-Reg</div>
                                                                <div class="cbp-tile-pct">{{ $mobPct }}%</div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="cbp-chart-card h-100">
                                            <div class="cbp-chart-header">
                                                <div class="cbp-chart-icon"><i class="bi bi-graph-up"></i></div>
                                                <div>
                                                    <h6 class="mb-0 fw-800">Customer Growth</h6>
                                                    <small class="text-muted">Walk-in vs Mobile</small>
                                                </div>
                                            </div>
                                            @php $cgt = $stats['customerGrowthTrend'] ?? []; @endphp
                                            <div class="d-flex gap-2 px-3 pb-2 flex-wrap">
                                                <div class="cbp-today-badge" style="background:#34d39920;border:1px solid #34d39960;">
                                                    <span class="cbp-today-dot" style="background:#34d399;"></span>
                                                    <span class="cbp-today-label">Walk-in Today</span>
                                                    <span class="cbp-today-num" style="color:#34d399;">{{ $cgt['today_walk_in'] ?? 0 }}</span>
                                                </div>
                                                <div class="cbp-today-badge" style="background:#818cf820;border:1px solid #818cf860;">
                                                    <span class="cbp-today-dot" style="background:#818cf8;"></span>
                                                    <span class="cbp-today-label">Mobile Today</span>
                                                    <span class="cbp-today-num" style="color:#818cf8;">{{ $cgt['today_mobile'] ?? 0 }}</span>
                                                </div>
                                                <div class="cbp-today-badge" style="background:#f59e0b20;border:1px solid #f59e0b60;">
                                                    <span class="cbp-today-dot" style="background:#f59e0b;"></span>
                                                    <span class="cbp-today-label">Total Today</span>
                                                    <span class="cbp-today-num" style="color:#f59e0b;">{{ $cgt['today_total'] ?? 0 }}</span>
                                                </div>
                                            </div>
                                            <div style="padding: 0 12px 12px;">
                                                <canvas id="customerGrowthChart" height="200"></canvas>
                                            </div>
                                            <div class="cbp-chart-legend" style="padding: 0 16px 16px;">
                                                <div class="cbp-chart-legend-item">
                                                    <span class="cbp-chart-legend-dot" style="background:#34d399;"></span>
                                                    <span class="cbp-chart-legend-label">Walk-In Total</span>
                                                    <span class="cbp-chart-legend-val">{{ number_format($cgt['total_walk_in'] ?? 0) }}</span>
                                                </div>
                                                <div class="cbp-chart-legend-item">
                                                    <span class="cbp-chart-legend-dot" style="background:#818cf8;"></span>
                                                    <span class="cbp-chart-legend-label">Mobile Total</span>
                                                    <span class="cbp-chart-legend-val">{{ number_format($cgt['total_mobile'] ?? 0) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Top Customers + Top Rated --}}
                <div class="col-lg-6">
                    <div class="modern-card shadow-sm h-100">
                        <div class="card-header-modern bg-transparent border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-800 text-slate-800">Top Customers</h6>
                                <small class="text-muted">By lifetime value</small>
                            </div>
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body-modern">
                            @php $topCustomers = $stats['topCustomers'] ?? []; @endphp
                            @forelse($topCustomers as $customer)
                                <div class="top-customer-item mb-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="customer-avatar me-3">{{ substr($customer['name'], 0, 1) }}</div>
                                            <div>
                                                <h6 class="mb-0">{{ $customer['name'] }}</h6>
                                                <small class="text-muted">{{ $customer['laundries_count'] }} laundries</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <h6 class="mb-0 text-success">₱{{ number_format($customer['total_spent'], 0) }}</h6>
                                            <small class="text-muted">Lifetime value</small>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="bi bi-people text-muted fs-1 mb-3"></i>
                                    <p class="text-muted">No customer data available</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="modern-card shadow-sm h-100">
                        <div class="card-header-modern bg-transparent border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-800 text-slate-800">Top Rated Customers</h6>
                                <small class="text-muted">Highest average rating given</small>
                            </div>
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-warning">View All</a>
                        </div>
                        <div class="card-body-modern">
                            @php
                                $topRatedCustomers = $stats['topRatedCustomers'] ?? [];
                                $starColors = [5=>'#f59e0b',4=>'#f59e0b',3=>'#94a3b8',2=>'#ef4444',1=>'#ef4444'];
                            @endphp
                            @forelse($topRatedCustomers as $index => $customer)
                                @php
                                    $stars = (int) round($customer['avg_rating']);
                                    $starColor = $starColors[$stars] ?? '#94a3b8';
                                @endphp
                                <div class="top-customer-item top-rated-item mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="trc-rank">{{ $index + 1 }}</div>
                                        <div class="customer-avatar flex-shrink-0" style="background:linear-gradient(135deg,{{ $starColor }},{{ $starColor }}cc);">
                                            {{ substr($customer['name'], 0, 1) }}
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <h6 class="mb-0 text-truncate">{{ $customer['name'] }}</h6>
                                            <div class="trc-star-bar mt-1">
                                                <div class="trc-star-fill" style="width:{{ ($customer['avg_rating'] / 5) * 100 }}%;background:{{ $starColor }};" data-width="{{ ($customer['avg_rating'] / 5) * 100 }}"></div>
                                            </div>
                                            <small class="text-muted">{{ $customer['ratings_count'] }} {{ $customer['ratings_count'] == 1 ? 'rating' : 'ratings' }}</small>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            <div class="trc-score" style="color:{{ $starColor }};">
                                                {{ $customer['avg_rating'] }}<i class="bi bi-star-fill ms-1" style="font-size:0.75rem;"></i>
                                            </div>
                                            <div class="trc-stars">
                                                @for($s = 1; $s <= 5; $s++)
                                                    <i class="bi bi-star{{ $s <= $stars ? '-fill' : '' }}" style="color:{{ $s <= $stars ? $starColor : '#e2e8f0' }};"></i>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="bi bi-star text-muted fs-1 mb-3 d-block"></i>
                                    <p class="text-muted">No ratings submitted yet</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>{{-- /customers --}}

        {{-- ── TAB 4: Operations ──────────────────────────────── --}}
        <div class="tab-pane fade" id="operations" role="tabpanel">
            <div class="row g-4">

                {{-- Pickup Pipeline by Branch --}}
                <div class="col-12">
                    <div class="pbp-wrapper modern-card shadow-sm overflow-hidden">
                        <div class="pbp-main-header">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="pbp-header-icon"><i class="bi bi-truck"></i></div>
                                    <div>
                                        <h5 class="mb-0 fw-800 text-white">Pickup Pipeline by Branch</h5>
                                        <small class="text-white" style="opacity:.75;">Live pickup request status per branch</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    @php
                                        $pbpDefs = [
                                            'pending'   => ['label'=>'Pending',   'hex'=>'#fbbf24', 'icon'=>'bi-clock-fill'],
                                            'accepted'  => ['label'=>'Accepted',  'hex'=>'#22d3ee', 'icon'=>'bi-check-circle-fill'],
                                            'en_route'  => ['label'=>'En Route',  'hex'=>'#818cf8', 'icon'=>'bi-truck'],
                                            'picked_up' => ['label'=>'Picked Up', 'hex'=>'#34d399', 'icon'=>'bi-bag-check-fill'],
                                            'cancelled' => ['label'=>'Cancelled', 'hex'=>'#f87171', 'icon'=>'bi-x-circle-fill'],
                                        ];
                                    @endphp
                                    <div class="pbp-legend d-none d-lg-flex">
                                        @foreach($pbpDefs as $key => $def)
                                            <div class="pbp-legend-item">
                                                <span class="pbp-legend-dot" style="background:{{ $def['hex'] }};"></span>
                                                <span>{{ $def['label'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <a href="{{ route('admin.pickups.index') }}" class="btn btn-sm btn-light rounded-pill fw-600">
                                        <i class="bi bi-arrow-right-circle me-1"></i>All Pickups
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body-modern">
                            @if(empty($stats['pickupBranchPipeline']) || collect($stats['pickupBranchPipeline'])->sum('total') === 0)
                                <div class="text-center py-5">
                                    <div class="pbp-empty-icon mb-3"><i class="bi bi-truck"></i></div>
                                    <h6 class="text-muted fw-600">No pickup request data</h6>
                                    <small class="text-muted">Pickup requests will appear here once created.</small>
                                </div>
                            @else
                                <div class="row g-4">
                                    @php $pbpAccents = ['#f59e0b','#6366f1','#06b6d4','#10b981','#ef4444','#8b5cf6']; @endphp
                                    @foreach($stats['pickupBranchPipeline'] as $branch)
                                        @php
                                            $accent = $pbpAccents[$loop->index % count($pbpAccents)];
                                            $bTotal = max($branch['total'], 1);
                                        @endphp
                                        <div class="col-xl-4 col-md-6">
                                            <div class="pbp-card" style="--pbp-accent: {{ $accent }};">
                                                <div class="pbp-accent-bar"></div>
                                                <div class="pbp-card-header">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="pbp-avatar" style="background:linear-gradient(135deg,{{ $accent }},{{ $accent }}aa);">
                                                            {{ strtoupper(substr($branch['name'], 0, 1)) }}
                                                        </div>
                                                        <div class="flex-grow-1 min-w-0">
                                                            <h6 class="mb-0 fw-700 pbp-branch-name">{{ $branch['name'] }}</h6>
                                                            <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                                                <span class="pbp-total-badge"><i class="bi bi-truck me-1"></i>{{ $branch['total'] }} requests</span>
                                                                @if($branch['active'] > 0)
                                                                    <span class="pbp-active-badge"><span class="pbp-pulse-dot"></span>{{ $branch['active'] }} active</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <a href="{{ route('admin.pickups.index', ['branch' => $branch['id']]) }}" class="pbp-view-link flex-shrink-0">
                                                            <i class="bi bi-box-arrow-up-right"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="pbp-bar-wrap">
                                                    <div class="pbp-stacked-bar">
                                                        @foreach($pbpDefs as $statusKey => $def)
                                                            @php $pct = $bTotal > 1 ? round(($branch['statuses'][$statusKey] / $bTotal) * 100, 1) : 0; @endphp
                                                            @if($pct > 0)
                                                                <div class="pbp-bar-seg" style="width:{{ $pct }}%;background:{{ $def['hex'] }};"
                                                                     data-bs-toggle="tooltip" data-bs-title="{{ $def['label'] }}: {{ $branch['statuses'][$statusKey] }} ({{ $pct }}%)"></div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="pbp-status-grid">
                                                    @foreach($pbpDefs as $statusKey => $def)
                                                        @php
                                                            $cnt = $branch['statuses'][$statusKey];
                                                            $pctTile = $bTotal > 1 ? round(($cnt / $bTotal) * 100) : 0;
                                                        @endphp
                                                        <a href="{{ route('admin.pickups.index', ['branch' => $branch['id'], 'status' => $statusKey]) }}"
                                                           class="pbp-status-tile text-decoration-none" style="--tile-color:{{ $def['hex'] }};">
                                                            <div class="pbp-tile-icon"><i class="bi {{ $def['icon'] }}"></i></div>
                                                            <div class="pbp-tile-count">{{ $cnt }}</div>
                                                            <div class="pbp-tile-label">{{ $def['label'] }}</div>
                                                            <div class="pbp-tile-pct">{{ $pctTile }}%</div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Pickup Management Panel --}}
                <div class="col-lg-5">
                    <div class="modern-card shadow-sm h-100">
                        <div class="card-header-modern bg-transparent border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-800 text-slate-800">Pickup Management</h6>
                                <small class="text-muted">Select multiple pickups for optimized route</small>
                            </div>
                            <div>
                                <span id="selectedPickupCount" class="badge bg-purple" style="display: none;">0</span>
                            </div>
                        </div>
                        <div class="card-body-modern">
                            <div id="multiRouteBtn" class="d-grid mb-4" style="display: none;">
                                <button class="btn btn-purple shadow-sm" onclick="getOptimizedMultiRoute()">
                                    <i class="bi bi-route me-2"></i>Optimize Route (<span id="selectedCount">0</span> selected)
                                </button>
                            </div>
                            <div class="d-grid mb-4">
                                <button class="btn btn-primary shadow-sm" onclick="autoRouteAllVisible()">
                                    <i class="bi bi-magic me-2"></i> Auto-Optimize All Pending
                                </button>
                            </div>
                            <div class="d-flex gap-2 mb-4">
                                <button class="btn btn-sm btn-outline-purple flex-fill" onclick="selectAllPending()">
                                    <i class="bi bi-check-square me-1"></i> Select All Pending
                                </button>
                                <button class="btn btn-sm btn-outline-danger flex-fill" onclick="clearSelections()">
                                    <i class="bi bi-x-circle me-1"></i> Clear All
                                </button>
                            </div>
                            <h6 class="mb-3 fw-800 text-slate-600">Pickup Status Summary</h6>
                            @foreach([
                                'pending'   => 'Pending',
                                'accepted'  => 'Accepted',
                                'en_route'  => 'En Route',
                                'picked_up' => 'Picked Up',
                                'cancelled' => 'Cancelled',
                            ] as $statusKey => $label)
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <div class="pickup-status-indicator status-{{ $statusKey }} me-3"></div>
                                        <div>
                                            <h6 class="mb-0">{{ $label }}</h6>
                                            <small class="text-muted">Pickup requests</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <h4 class="mb-0">{{ $stats['pickupStats'][$statusKey] ?? 0 }}</h4>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Logistics Map --}}
                <div class="col-lg-7">
                    <div class="modern-card shadow-sm h-100">
                        <div class="card-header-modern bg-transparent border-0 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-800 text-slate-800">Logistics Map</h6>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-purple" id="multiRouteTopBtn" style="display: none;" onclick="getOptimizedMultiRoute()">
                                    <i class="bi bi-route"></i> Optimize (<span id="selectedCountTop">0</span>)
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshMapMarkers()">
                                    <i class="bi bi-geo-alt"></i> Refresh
                                </button>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mapModal">
                                    <i class="bi bi-arrows-fullscreen"></i> Fullscreen
                                </button>
                            </div>
                        </div>
                        <div class="card-body-modern p-0 position-relative">
                            <div id="address-search-overlay" style="position: absolute; top: 15px; right: 15px; z-index: 1000; max-width: 380px;">
                                <div class="card shadow-lg border-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-search text-primary"></i>
                                            <h6 class="mb-0 fw-bold">Search Location</h6>
                                        </div>
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="map-address-search" class="form-control"
                                                   placeholder="e.g., 183 Dr. V Locsin Street, Dumaguete City"
                                                   style="font-size: 13px;">
                                            <button class="btn btn-primary" onclick="searchMapAddress()">
                                                <i class="bi bi-geo-alt-fill"></i>
                                            </button>
                                        </div>
                                        <div id="search-result-display" class="mt-2" style="display: none;">
                                            <div class="alert alert-success mb-0 py-2 px-2 small">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <strong id="result-address-text" class="d-block mb-1"></strong>
                                                        <small class="text-muted d-block" id="result-coords-text"></small>
                                                    </div>
                                                    <button class="btn btn-sm btn-link p-0 text-decoration-none"
                                                            onclick="document.getElementById('search-result-display').style.display='none'">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="logisticsMap" class="admin-logistics-map"></div>
                            <div id="map-controls-container" style="position: absolute; top: 10px; left: 10px; z-index: 1000;">
                                <div id="eta-display-container" style="display: none; margin-bottom: 10px;"></div>
                                <div class="route-controls" style="display: none;">
                                    <button class="route-btn btn-clear-route" onclick="clearRoute()">
                                        <i class="bi bi-x-circle"></i> Clear Route
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>{{-- /operations --}}

        {{-- ── TAB 5: Financial ────────────────────────────────── --}}
        {{-- FIX: This pane was previously OUTSIDE tab-content —— now correctly placed inside --}}
        <div class="tab-pane fade" id="financial" role="tabpanel">
            <div class="row g-4 mb-0">
                {{-- Income Breakdown Chart --}}
                <div class="col-lg-6">
                    <div class="modern-card shadow-sm h-100">
                        <div class="card-header-modern bg-transparent border-0">
                            <h6 class="mb-0 fw-800 text-slate-800"><i class="bi bi-pie-chart text-success me-2"></i>Income Breakdown</h6>
                            <small class="text-muted">Revenue by source ({{ $rangeLabels[$currentFilters['date_range'] ?? 'last_30_days'] ?? 'Period' }})</small>
                        </div>
                        <div class="card-body-modern">
                            <div style="position:relative;height:250px;">
                                <canvas id="incomeBreakdownChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Expense Breakdown Chart --}}
                <div class="col-lg-6">
                    <div class="modern-card shadow-sm h-100">
                        <div class="card-header-modern bg-transparent border-0">
                            <h6 class="mb-0 fw-800 text-slate-800"><i class="bi bi-pie-chart text-danger me-2"></i>Expense Breakdown</h6>
                            <small class="text-muted">Costs by category ({{ $rangeLabels[$currentFilters['date_range'] ?? 'last_30_days'] ?? 'Period' }})</small>
                        </div>
                        <div class="card-body-modern">
                            <div style="position:relative;height:250px;">
                                <canvas id="expenseBreakdownChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-3 mb-0">
                <div class="col-12">
                    <div class="modern-card shadow-sm">
                        <div class="card-header-modern bg-transparent border-0">
                            <h6 class="mb-0 fw-800 text-slate-800"><i class="bi bi-table text-primary me-2"></i>Financial Summary</h6>
                            <small class="text-muted">Complete income and expense overview</small>
                        </div>
                        <div class="card-body-modern">
                            <div class="table-responsive">
                                <table class="table table-sm table-modern">
                                    <tbody>
                                        <tr class="table-active">
                                            <td class="fw-800 text-success">Total Income</td>
                                            <td class="fw-800 text-success text-end">₱{{ number_format($stats['financialBreakdown']['total_income'], 0) }}</td>
                                        </tr>
                                        <tr class="table-active">
                                            <td class="fw-800 text-danger">Total Expenses</td>
                                            <td class="fw-800 text-danger text-end">₱{{ number_format($stats['financialBreakdown']['total_expense'], 0) }}</td>
                                        </tr>
                                        <tr class="table-secondary fw-800">
                                            <td>Net Profit</td>
                                            <td class="text-end {{ $stats['financialBreakdown']['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                ₱{{ number_format($stats['financialBreakdown']['net_profit'], 0) }}
                                            </td>
                                        </tr>
                                        <tr class="table-secondary fw-800">
                                            <td>Profit Margin</td>
                                            <td class="text-end {{ $stats['financialBreakdown']['profit_margin'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $stats['financialBreakdown']['profit_margin'] }}%
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>{{-- /financial --}}

        {{-- ── TAB 6: Inventory ────────────────────────────────── --}}
        {{-- FIX: This pane was previously OUTSIDE tab-content —— now correctly placed inside --}}
        <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="metric-card-compact border-indigo">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="metric-icon-compact bg-indigo-light"><i class="bi bi-box2"></i></div>
                        </div>
                        <div class="metric-label-compact">Total Value</div>
                        <div class="metric-value-large text-slate-900">₱{{ number_format($stats['inventoryMetrics']['total_value'], 0) }}</div>
                        <small class="text-muted">Inventory worth</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card-compact border-warning">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="metric-icon-compact bg-yellow-light"><i class="bi bi-exclamation-triangle"></i></div>
                        </div>
                        <div class="metric-label-compact">Low Stock</div>
                        <div class="metric-value-large text-warning">{{ count($stats['inventoryAnalysis']['low_stock_items'] ?? []) }}</div>
                        <small class="text-muted">Items need reorder</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card-compact border-red">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="metric-icon-compact bg-red-light"><i class="bi bi-x-circle"></i></div>
                        </div>
                        <div class="metric-label-compact">Out of Stock</div>
                        <div class="metric-value-large text-danger">{{ $stats['inventoryAnalysis']['out_of_stock_count'] ?? 0 }}</div>
                        <small class="text-muted">Items depleted</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card-compact border-blue">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="metric-icon-compact bg-blue-light"><i class="bi bi-building"></i></div>
                        </div>
                        <div class="metric-label-compact">Locations</div>
                        <div class="metric-value-large text-slate-900">{{ count($stats['inventoryAnalysis']['stock_by_location'] ?? []) }}</div>
                        <small class="text-muted">Active branches</small>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-0">
                <div class="col-lg-6">
                    <div class="modern-card shadow-sm h-100">
                        <div class="card-header-modern bg-transparent border-0">
                            <h6 class="mb-0 fw-800 text-slate-800"><i class="bi bi-diagram-3 text-info me-2"></i>Stock Value by Location</h6>
                            <small class="text-muted">Inventory distribution across branches</small>
                        </div>
                        <div class="card-body-modern">
                            @if(!empty($stats['inventoryAnalysis']['stock_by_location']) && count($stats['inventoryAnalysis']['stock_by_location']) > 0)
                                <div style="position:relative;height:250px;">
                                    <canvas id="stockByLocationChart"></canvas>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-box2 text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p class="text-muted mt-3">No inventory data available</p>
                                    <a href="{{ route('admin.inventory.index') }}" class="btn btn-sm btn-primary mt-2">
                                        <i class="bi bi-plus-lg me-1"></i>Add Inventory
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="modern-card shadow-sm h-100">
                        <div class="card-header-modern bg-transparent border-0">
                            <h6 class="mb-0 fw-800 text-slate-800"><i class="bi bi-activity text-warning me-2"></i>Inventory Health</h6>
                            <small class="text-muted">Stock status overview</small>
                        </div>
                        <div class="card-body-modern">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                        <div>
                                            <div class="text-muted small mb-1">Low Stock Items</div>
                                            <h3 class="mb-0 text-warning fw-800">{{ count($stats['inventoryAnalysis']['low_stock_items'] ?? []) }}</h3>
                                        </div>
                                        <div class="text-warning" style="font-size: 2rem;"><i class="bi bi-exclamation-triangle-fill"></i></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                        <div>
                                            <div class="text-muted small mb-1">Out of Stock</div>
                                            <h3 class="mb-0 text-danger fw-800">{{ $stats['inventoryAnalysis']['out_of_stock_count'] ?? 0 }}</h3>
                                        </div>
                                        <div class="text-danger" style="font-size: 2rem;"><i class="bi bi-x-circle-fill"></i></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                        <div>
                                            <div class="text-muted small mb-1">Total Locations</div>
                                            <h3 class="mb-0 text-primary fw-800">{{ count($stats['inventoryAnalysis']['stock_by_location'] ?? []) }}</h3>
                                        </div>
                                        <div class="text-primary" style="font-size: 2rem;"><i class="bi bi-building"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('admin.inventory.index') }}" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bi bi-box-seam me-1"></i>View Full Inventory
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-3 mb-0">
                <div class="col-12">
                    <div class="modern-card shadow-sm">
                        <div class="card-header-modern bg-transparent border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-800 text-slate-800"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Low Stock Alerts</h6>
                                <small class="text-muted">{{ count($stats['inventoryAnalysis']['low_stock_items'] ?? []) }} items below reorder point</small>
                            </div>
                            <a href="{{ route('admin.inventory.index') }}?filter=low_stock" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-funnel me-1"></i>View All Low Stock
                            </a>
                        </div>
                        <div class="card-body-modern">
                            @if(!empty($stats['inventoryAnalysis']['low_stock_items']) && count($stats['inventoryAnalysis']['low_stock_items']) > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead style="background: rgba(245, 158, 11, 0.1);">
                                            <tr>
                                                <th class="py-2">Item</th>
                                                <th class="py-2">Location</th>
                                                <th class="text-center py-2">Current Stock</th>
                                                <th class="text-center py-2">Reorder Point</th>
                                                <th class="text-center py-2">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($stats['inventoryAnalysis']['low_stock_items'] as $item)
                                                <tr>
                                                    <td class="fw-600 py-2">{{ $item['item'] }}</td>
                                                    <td class="py-2">{{ $item['location'] }}</td>
                                                    <td class="text-center py-2">
                                                        <span class="badge bg-warning text-dark">{{ number_format($item['current'], 0) }}</span>
                                                    </td>
                                                    <td class="text-center py-2">{{ number_format($item['reorder'], 0) }}</td>
                                                    <td class="text-center py-2">
                                                        <span class="badge bg-warning"><i class="bi bi-exclamation-triangle me-1"></i>Low Stock</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3 mb-0">All inventory items are well stocked! ✅</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>{{-- /inventory --}}

    </div>{{-- /tab-content --}}
@endif
{{-- ═══════════════════════════════════════════════════════════════════════════════════════════════════════════
     END OF COMMENTED TABS SECTION
═══════════════════════════════════════════════════════════════════════════════════════════════════════════ --}}

</div>{{-- /container-xl .dashboard-modern-wrapper --}}

{{-- ═══════════════════════════════════════════════════════════════
     OVERLAY ELEMENTS — placed OUTSIDE the main container
     (modals and fixed panels must not be trapped inside a scroll container)
════════════════════════════════════════════════════════════════════ --}}

{{-- Route Details Panel --}}
<div id="routeDetailsPanel" class="route-details-panel" style="display: none;"></div>

{{-- Fullscreen Map Modal --}}
<div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header border-bottom shadow-sm bg-navy text-white">
                <h5 class="modal-title fw-bold">Logistics Command Center</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-warning" id="modalMultiRouteBtn" style="display: none;" onclick="getOptimizedMultiRoute()">
                        <i class="bi bi-route me-1"></i>Optimize (<span id="modalSelectedCount">0</span>)
                    </button>
                    <button class="btn btn-sm btn-info" onclick="autoRouteAllVisible()">
                        <i class="bi bi-magic me-1"></i>Auto-Optimize All
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-0">
                <div id="modalLogisticsMap" style="height: 100%; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/leaflet/leaflet.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/leaflet/MarkerCluster.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/leaflet/MarkerCluster.Default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    <style>
        /* KPI ROW */
        .kpi-row { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
        .kpi-card { background: var(--color-background-primary, #fff); border: 0.5px solid var(--color-border-tertiary, #e2e8f0); border-radius: 8px; padding: 1rem; display: flex; flex-direction: column; gap: 4px; position: relative; overflow: hidden; }
        .kpi-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: 8px 8px 0 0; }
        .kpi-card.blue::before { background: #3b82f6; }
        .kpi-card.green::before { background: #10b981; }
        .kpi-card.amber::before { background: #f59e0b; }
        .kpi-card.red::before { background: #ef4444; }
        .kpi-card.purple::before { background: #8b5cf6; }
        .kpi-label { font-size: 11px; color: var(--color-text-tertiary, #94a3b8); }
        .kpi-value { font-size: 22px; font-weight: 500; color: var(--color-text-primary, #0f172a); line-height: 1.1; }
        .kpi-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 2px; }
        .kpi-sub { font-size: 11px; color: var(--color-text-tertiary, #94a3b8); }
        .kpi-change { font-size: 11px; font-weight: 500; display: flex; align-items: center; gap: 2px; }
        .kpi-change.up { color: #059669; }
        .kpi-change.down { color: #dc2626; }
        .kpi-change.crit { background: #fef2f2; color: #dc2626; padding: 2px 6px; border-radius: 10px; font-size: 10px; }

        /* TABS */
        .tab-wrapper { background: var(--color-background-primary, #fff); border: 0.5px solid var(--color-border-tertiary, #e2e8f0); border-radius: 8px; overflow: hidden; }
        .tab-bar { display: flex; gap: 0; border-bottom: 0.5px solid var(--color-border-tertiary, #e2e8f0); background: var(--color-background-primary, #fff); }
        .tab-btn { padding: 10px 16px; font-size: 12px; color: var(--color-text-secondary, #64748b); cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.15s; display: flex; align-items: center; gap: 6px; white-space: nowrap; background: none; border-top: none; border-left: none; border-right: none; }
        .tab-btn:hover { color: var(--color-text-primary, #0f172a); background: var(--color-background-secondary, #f8fafc); }
        .tab-btn.active { color: #1e40af; border-bottom: 2px solid #1e40af; font-weight: 500; }
        .tab-btn i { font-size: 13px; }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* GRID LAYOUTS */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }

        /* CARD */
        .card { background: var(--color-background-primary, #fff); border: 0.5px solid var(--color-border-tertiary, #e2e8f0); border-radius: 8px; overflow: hidden; }
        .card-head { padding: 10px 14px; border-bottom: 0.5px solid var(--color-border-tertiary, #e2e8f0); display: flex; align-items: center; justify-content: space-between; }
        .card-title { font-size: 12px; font-weight: 500; color: var(--color-text-primary, #0f172a); }
        .card-sub { font-size: 11px; color: var(--color-text-tertiary, #94a3b8); }
        .card-body { padding: 14px; }
        .view-all { font-size: 11px; color: #3b82f6; cursor: pointer; }

        /* CHART */
        .chart-wrap { position: relative; width: 100%; height: 180px; }

        /* QUICK ACTIONS */
        .qa-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 8px; padding: 10px 14px; }
        .qa-btn { display: flex; flex-direction: column; align-items: center; gap: 5px; padding: 10px 6px; border: 0.5px solid var(--color-border-tertiary, #e2e8f0); border-radius: 6px; cursor: pointer; background: var(--color-background-secondary, #f8fafc); transition: all 0.15s; text-decoration: none; }
        .qa-btn:hover { background: #eff6ff; border-color: #bfdbfe; }
        .qa-icon { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .qa-icon i { font-size: 14px; }
        .qa-label { font-size: 10px; color: var(--color-text-secondary, #64748b); text-align: center; }

        /* RESPONSIVE */
        @media (max-width: 1200px) {
            .kpi-row { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 768px) {
            .kpi-row { grid-template-columns: repeat(2, 1fr); }
            .grid-2 { grid-template-columns: 1fr; }
            .qa-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 480px) {
            .kpi-row { grid-template-columns: 1fr; }
            .qa-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/chart.js/chart.umd.min.js') }}"></script>
    <script src="{{ asset('assets/leaflet/leaflet.js') }}"></script>
    <script src="{{ asset('assets/leaflet/leaflet.markercluster.js') }}"></script>
    <script src="{{ asset('assets/js/utils/tabFix.js') }}"></script>
    <script src="{{ asset('assets/js/utils/dataStabilizer.js') }}"></script>
    <script src="{{ asset('assets/js/utils/postLoadOptimizer.js') }}"></script>
    <script src="{{ asset('assets/js/utils/performanceMonitorWidget.js') }}"></script>

    <script>
        window.BRANCHES           = @json($stats['branches'] ?? []);
        window.PENDING_PICKUPS    = @json($stats['pendingPickups'] ?? []);
        window.REVENUE_DATA       = { labels: @json($stats['revenueLabels'] ?? []), values: @json($stats['last7DaysRevenue'] ?? []) };
        window.CUSTOMER_SOURCE_DATA  = @json($stats['customerRegistrationSource'] ?? []);
        window.CUSTOMER_BRANCH_DATA  = @json($stats['customerBranchPipeline'] ?? []);
        window.CUSTOMER_GROWTH_DATA  = @json($stats['customerGrowthTrend'] ?? []);
        window.TOP_RATED_DATA        = @json($stats['topRatedCustomers'] ?? []);
        window.SERVICE_CHART_DATA    = @json($stats['serviceChartData'] ?? []);
        window.DAILY_LAUNDRY_DATA    = @json($stats['dailyLaundryCount'] ?? []);
        window.PAYMENT_METHODS_DATA  = @json($stats['paymentMethods'] ?? []);
        window.TOP_SERVICES_DATA     = @json($stats['topServices'] ?? []);
        window.YOY_REVENUE_DATA      = @json($stats['yoyRevenue'] ?? []);
        window.DASHBOARD_STATS       = @json($stats ?? []);
        window.CURRENT_DATE_RANGE    = '{{ $currentFilters["date_range"] ?? "last_30_days" }}';
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animate horizontal bars
            setTimeout(() => {
                document.querySelectorAll('.bar-fill').forEach((bar, index) => {
                    setTimeout(() => {
                        const width = bar.style.width;
                        bar.style.width = '0%';
                        setTimeout(() => { bar.style.width = width; }, 50);
                    }, index * 100);
                });
            }, 300);
        });

        window.addEventListener('load', function() {
            setTimeout(() => { initializeDashboardLazy(); }, 300);
        });

        function initializeDashboardLazy() {
            if (window.dataStabilizer) {
                requestIdleCallback(() => {
                    window.dataStabilizer.cacheData('branches', window.BRANCHES);
                    window.dataStabilizer.cacheData('dashboard_stats', window.DASHBOARD_STATS);
                    window.dataStabilizer.cacheData('pending_pickups', window.PENDING_PICKUPS);
                }, { timeout: 2000 });
            }
            if (typeof window.initializeDashboardData === 'function') {
                requestIdleCallback(() => {
                    window.initializeDashboardData(window.BRANCHES, window.DASHBOARD_STATS);
                }, { timeout: 2000 });
            }
        }

        window.addEventListener('dataRefreshRequested', function(event) {
            if (event.detail.key.startsWith('pipeline_')) {
                setTimeout(() => window.location.reload(), 1000);
            }
        });
    </script>

    <script>
        function initializeFinancialCharts() {
            if (typeof Chart === 'undefined') { setTimeout(initializeFinancialCharts, 200); return; }

            const cc = {
                blue: '#3b82f6', green: '#10b981', red: '#ef4444',
                yellow: '#f59e0b', purple: '#8b5cf6', cyan: '#06b6d4', indigo: '#4f46e5'
            };

            // Revenue & Expense Trend
            const revExpCtx = document.getElementById('revenueExpenseTrendChart');
            if (revExpCtx) {
                new Chart(revExpCtx, {
                    type: 'line',
                    data: {
                        labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
                        datasets: [
                            { label:'Revenue', data:[12000,15000,13500,18000,16500,21000,19500], borderColor:cc.green, backgroundColor:'rgba(16,185,129,0.1)', borderWidth:3, fill:true, tension:0.4, pointRadius:5, pointHoverRadius:7, pointBackgroundColor:cc.green, pointBorderColor:'#fff', pointBorderWidth:2 },
                            { label:'Expenses', data:[8000,9500,8200,11000,9800,12500,10500], borderColor:cc.red, backgroundColor:'rgba(239,68,68,0.1)', borderWidth:3, fill:true, tension:0.4, pointRadius:5, pointHoverRadius:7, pointBackgroundColor:cc.red, pointBorderColor:'#fff', pointBorderWidth:2 }
                        ]
                    },
                    options: {
                        responsive:true, maintainAspectRatio:false,
                        plugins: {
                            legend:{ position:'top', align:'end', labels:{ usePointStyle:true, padding:15, font:{size:12,weight:'600'} } },
                            tooltip:{ backgroundColor:'rgba(0,0,0,0.8)', padding:12, callbacks:{ label: ctx => ctx.dataset.label+': ₱'+ctx.parsed.y.toLocaleString() } }
                        },
                        scales: {
                            y:{ beginAtZero:true, grid:{color:'rgba(0,0,0,0.05)',drawBorder:false}, ticks:{ font:{size:11}, callback: v => '₱'+(v/1000)+'k' } },
                            x:{ grid:{display:false,drawBorder:false}, ticks:{font:{size:11,weight:'600'}} }
                        },
                        interaction:{ intersect:false, mode:'index' }
                    }
                });
            }

            // Profit Trend
            const profitCtx = document.getElementById('profitTrendChart');
            if (profitCtx && window.DASHBOARD_STATS?.profitTrend) {
                const pd = window.DASHBOARD_STATS.profitTrend;
                new Chart(profitCtx, {
                    type:'line',
                    data:{ labels:pd.labels||[], datasets:[{ label:'Daily Profit', data:pd.data||[], borderColor:cc.green, backgroundColor:'rgba(16,185,129,0.1)', borderWidth:3, fill:true, tension:0.4, pointRadius:5, pointHoverRadius:7, pointBackgroundColor:cc.green, pointBorderColor:'#fff', pointBorderWidth:2 }] },
                    options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{position:'top',labels:{usePointStyle:true,padding:15,font:{size:12,weight:'600'}}}, tooltip:{backgroundColor:'rgba(0,0,0,0.8)',padding:12,callbacks:{label:ctx=>'Profit: ₱'+ctx.parsed.y.toLocaleString()}} }, scales:{ y:{beginAtZero:true,grid:{color:'rgba(0,0,0,0.05)',drawBorder:false},ticks:{callback:v=>'₱'+(v/1000).toFixed(0)+'k'}}, x:{grid:{display:false,drawBorder:false}} } }
                });
            }

            // Income Breakdown
            const incomeCtx = document.getElementById('incomeBreakdownChart');
            if (incomeCtx) {
                const incomeData = window.DASHBOARD_STATS?.financialBreakdown?.income_breakdown || [];
                if (incomeData.length > 0) {
                    new Chart(incomeCtx, { type:'doughnut', data:{ labels:incomeData.map(i=>i.label), datasets:[{ data:incomeData.map(i=>i.value), backgroundColor:[cc.blue,cc.green,cc.purple,cc.yellow,cc.cyan], borderColor:'#fff', borderWidth:2 }] }, options:{ responsive:true, maintainAspectRatio:true, plugins:{ legend:{position:'bottom',labels:{font:{size:11},padding:12,usePointStyle:true}}, tooltip:{callbacks:{label:ctx=>{ const t=ctx.dataset.data.reduce((a,b)=>a+b,0); return '₱'+ctx.parsed.toLocaleString()+' ('+(ctx.parsed/t*100).toFixed(1)+'%)'; }}} } } });
                } else {
                    incomeCtx.closest('.card-body-modern').innerHTML = '<div class="text-center py-5 text-muted"><i class="bi bi-bar-chart" style="font-size:2.5rem;opacity:.3;"></i><p class="mt-3">No income data for this period</p></div>';
                }
            }

            // Expense Breakdown
            const expenseCtx = document.getElementById('expenseBreakdownChart');
            if (expenseCtx) {
                const expenseData = window.DASHBOARD_STATS?.financialBreakdown?.expense_breakdown || [];
                if (expenseData.length > 0) {
                    new Chart(expenseCtx, { type:'doughnut', data:{ labels:expenseData.map(i=>i.label), datasets:[{ data:expenseData.map(i=>i.value), backgroundColor:[cc.red,cc.yellow,cc.indigo,cc.purple,cc.cyan], borderColor:'#fff', borderWidth:2 }] }, options:{ responsive:true, maintainAspectRatio:true, plugins:{ legend:{position:'bottom',labels:{font:{size:11},padding:12,usePointStyle:true}}, tooltip:{callbacks:{label:ctx=>{ const t=ctx.dataset.data.reduce((a,b)=>a+b,0); return '₱'+ctx.parsed.toLocaleString()+' ('+(ctx.parsed/t*100).toFixed(1)+'%)'; }}} } } });
                } else {
                    expenseCtx.closest('.card-body-modern').innerHTML = '<div class="text-center py-5 text-muted"><i class="bi bi-receipt" style="font-size:2.5rem;opacity:.3;"></i><p class="mt-3">No expense data for this period</p></div>';
                }
            }

            // Stock by Location — lazy init on tab show
            let stockChartInitialized = false;
            function initStockByLocationChart() {
                if (stockChartInitialized) return;
                stockChartInitialized = true;
                const ctx = document.getElementById('stockByLocationChart');
                if (!ctx) return;
                const stockData = window.DASHBOARD_STATS?.inventoryAnalysis?.stock_by_location || [];
                if (stockData.length > 0) {
                    new Chart(ctx, { type:'bar', data:{ labels:stockData.map(i=>i.location), datasets:[{ label:'Stock Value (₱)', data:stockData.map(i=>i.value), backgroundColor:[cc.blue,cc.green,cc.purple,cc.yellow,cc.cyan], borderColor:'rgba(0,0,0,0.1)', borderWidth:1 }] }, options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{ legend:{position:'top',labels:{font:{size:11},padding:12,usePointStyle:true}}, tooltip:{callbacks:{label:ctx=>'Stock Value: ₱'+ctx.parsed.x.toLocaleString()}} }, scales:{ x:{beginAtZero:true,ticks:{callback:v=>'₱'+(v/1000).toFixed(0)+'k'},grid:{color:'rgba(0,0,0,0.05)',drawBorder:false}}, y:{grid:{display:false,drawBorder:false}} } } });
                }
            }
            const inventoryTab = document.getElementById('inventory-tab');
            if (inventoryTab) { inventoryTab.addEventListener('shown.bs.tab', () => setTimeout(initStockByLocationChart, 100)); }

            // Customer Growth Line Chart
            const cgCtx = document.getElementById('customerGrowthChart');
            if (cgCtx && window.CUSTOMER_GROWTH_DATA?.labels?.length) {
                const cgd = window.CUSTOMER_GROWTH_DATA;
                new Chart(cgCtx, { type:'line', data:{ labels:cgd.labels, datasets:[ { label:'Walk-in', data:cgd.walk_in, borderColor:'#34d399', backgroundColor:'rgba(52,211,153,0.12)', borderWidth:2.5, fill:true, tension:0.4, pointRadius:4, pointHoverRadius:6, pointBackgroundColor:'#34d399', pointBorderColor:'#fff', pointBorderWidth:2 }, { label:'Mobile', data:cgd.mobile, borderColor:'#818cf8', backgroundColor:'rgba(129,140,248,0.12)', borderWidth:2.5, fill:true, tension:0.4, pointRadius:4, pointHoverRadius:6, pointBackgroundColor:'#818cf8', pointBorderColor:'#fff', pointBorderWidth:2 } ] }, options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false}, tooltip:{backgroundColor:'rgba(15,19,50,0.95)',padding:10,callbacks:{label:ctx=>` ${ctx.dataset.label}: ${ctx.parsed.y} customer${ctx.parsed.y!==1?'s':''}`}} }, scales:{ y:{beginAtZero:true,ticks:{stepSize:1,font:{size:10},callback:v=>Number.isInteger(v)?v:''},grid:{color:'rgba(0,0,0,0.05)',drawBorder:false}}, x:{ticks:{font:{size:10},maxRotation:45},grid:{display:false,drawBorder:false}} }, interaction:{intersect:false,mode:'index'} } });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeFinancialCharts);
        } else {
            setTimeout(initializeFinancialCharts, 500);
        }
    </script>

    <script type="module" src="{{ asset('assets/js/admin.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('button[data-bs-toggle="pill"]').forEach(function(triggerEl) {
                const tabTrigger = new bootstrap.Tab(triggerEl);
                triggerEl.addEventListener('click', function(event) {
                    event.preventDefault();
                    tabTrigger.show();
                });
            });
        });

        // Tab switching function
        function switchTab(id, btn) {
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('tab-' + id).classList.add('active');
            btn.classList.add('active');
        }
    </script>
@endpush
