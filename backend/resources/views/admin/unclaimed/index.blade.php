@extends('admin.layouts.app')

@section('title', 'Unclaimed Laundry')
@section('page-title', 'Unclaimed Laundry')

@push('styles')
<style>
/* ══════════════════════════════════════════════════════
   UNCLAIMED LAUNDRY — LIGHT / DARK MODE SUPPORT
   Uses .dark-mode on <body> (existing project toggle)
   + [data-bs-theme="dark"] for Bootstrap 5.3 compat
══════════════════════════════════════════════════════ */

/* ── CSS Variables ── */
:root {
    --uc-bg:          #ffffff;
    --uc-bg-soft:     #f8f9fa;
    --uc-card-bg:     #ffffff;
    --uc-card-header: #ffffff;
    --uc-border:      #dee2e6;
    --uc-text:        #212529;
    --uc-text-muted:  #6c757d;
    --uc-table-head:  #f8f9fa;
    --uc-table-row:   #ffffff;
    --uc-table-hover: #f1f3f5;
    --uc-badge-light: #e9ecef;
    --uc-badge-text:  #495057;
    --uc-input-bg:    #ffffff;
    --uc-input-border:#ced4da;
    --uc-shadow:      0 2px 8px rgba(0,0,0,.08);
    --uc-row-danger:  #fff5f5;
    --uc-row-warning: #fffbf0;
    --uc-link:        #0d6efd;
}

body.dark-mode,
[data-bs-theme="dark"] {
    --uc-bg:          #0f172a;
    --uc-bg-soft:     #1e293b;
    --uc-card-bg:     #1e293b;
    --uc-card-header: #162032;
    --uc-border:      #334155;
    --uc-text:        #e2e8f0;
    --uc-text-muted:  #94a3b8;
    --uc-table-head:  #162032;
    --uc-table-row:   #1e293b;
    --uc-table-hover: #243447;
    --uc-badge-light: #334155;
    --uc-badge-text:  #cbd5e1;
    --uc-input-bg:    #0f172a;
    --uc-input-border:#334155;
    --uc-shadow:      0 2px 8px rgba(0,0,0,.35);
    --uc-row-danger:  rgba(220,53,69,.12);
    --uc-row-warning: rgba(255,193,7,.09);
    --uc-link:        #60a5fa;
}

/* ── Base ── */
.uc-card {
    background: var(--uc-card-bg);
    border: 1px solid var(--uc-border) !important;
    box-shadow: var(--uc-shadow);
    border-radius: 12px;
}
.uc-card-header {
    background: var(--uc-card-header) !important;
    border-bottom: 1px solid var(--uc-border) !important;
    color: var(--uc-text);
    border-radius: 12px 12px 0 0 !important;
}
.uc-text       { color: var(--uc-text) !important; }
.uc-text-muted { color: var(--uc-text-muted) !important; }
.uc-border-b   { border-bottom: 1px solid var(--uc-border) !important; }
.uc-link       { color: var(--uc-link) !important; text-decoration: none; }
.uc-link:hover { text-decoration: underline; }

/* ── Table ── */
.uc-table thead th {
    background: var(--uc-table-head) !important;
    color: var(--uc-text-muted);
    border-bottom: 1px solid var(--uc-border) !important;
    font-size: .78rem; font-weight: 600;
    letter-spacing: .3px; text-transform: uppercase;
    padding: .75rem 1rem;
}
.uc-table tbody tr {
    background: var(--uc-table-row) !important;
    border-bottom: 1px solid var(--uc-border) !important;
    transition: background .15s;
}
.uc-table tbody tr:hover {
    background: var(--uc-table-hover) !important;
}
.uc-table tbody tr.row-critical {
    background: var(--uc-row-danger) !important;
}
.uc-table tbody tr.row-warning-row {
    background: var(--uc-row-warning) !important;
}
.uc-table td { color: var(--uc-text); padding: .7rem 1rem; vertical-align: middle; border: none; }

/* ── Inputs ── */
.uc-input {
    background: var(--uc-input-bg) !important;
    border: 1px solid var(--uc-input-border) !important;
    color: var(--uc-text) !important;
    border-radius: 8px;
}
.uc-input::placeholder { color: var(--uc-text-muted) !important; }
.uc-input:focus {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 3px rgba(13,110,253,.15) !important;
    outline: none;
}
body.dark-mode .uc-input:focus,
[data-bs-theme="dark"] .uc-input:focus {
    border-color: #60a5fa !important;
    box-shadow: 0 0 0 3px rgba(96,165,250,.15) !important;
}
.uc-select {
    background: var(--uc-input-bg) !important;
    border: 1px solid var(--uc-input-border) !important;
    color: var(--uc-text) !important;
    border-radius: 8px;
}

/* ── Branch badge ── */
.branch-badge {
    background: var(--uc-badge-light) !important;
    color: var(--uc-badge-text) !important;
    border: 1px solid var(--uc-border);
    font-size: .75rem; padding: .3rem .7rem; border-radius: 6px; font-weight: 500;
}

/* ── Urgency filter pills ── */
.urgency-pill {
    display: block; padding: .7rem; border-radius: 10px;
    text-decoration: none; transition: background .2s;
    color: var(--uc-text-muted);
}
.urgency-pill:hover { background: var(--uc-table-hover); }
.urgency-pill.active-critical { background: rgba(220,53,69,.12); }
.urgency-pill.active-urgent   { background: rgba(255,193,7,.12); }
.urgency-pill.active-warning  { background: rgba(13,202,240,.1); }
.urgency-pill.active-pending  { background: rgba(108,117,125,.1); }
.urgency-pill.active-all      { background: rgba(13,110,253,.1); }
.urgency-pill strong { display: block; font-size: .72rem; color: var(--uc-text); }
.urgency-pill span   { font-size: .65rem; color: var(--uc-text-muted); }

/* ── Sidebar links (branch comparison) ── */
.branch-link {
    display: flex; justify-content: space-between; align-items: center;
    padding: .75rem 1rem; border-bottom: 1px solid var(--uc-border) !important;
    text-decoration: none; transition: background .15s;
    color: var(--uc-text);
}
.branch-link:hover   { background: var(--uc-table-hover) !important; }
.branch-link.active  { background: var(--uc-table-hover) !important; }
.branch-link strong  { color: var(--uc-text); font-size: .9rem; }
.branch-link small   { color: var(--uc-text-muted); font-size: .73rem; }

/* ── Sidebar rows ── */
.uc-sb-row {
    display: flex; justify-content: space-between; align-items: center;
    padding-bottom: .85rem; margin-bottom: .85rem;
    border-bottom: 1px solid var(--uc-border);
}
.uc-sb-row:last-child { border-bottom: none; padding-bottom: 0; margin-bottom: 0; }
.uc-sb-row .label  { font-size: .82rem; color: var(--uc-text-muted); margin-bottom: .1rem; }
.uc-sb-row .value  { font-weight: 700; font-size: .95rem; color: var(--uc-text); }
.uc-sb-row .value.green  { color: #22c55e; }
.uc-sb-row .value.yellow { color: #eab308; }
.uc-sb-row .value.red    { color: #ef4444; }
.uc-sb-row .value.big    { font-size: 1.1rem; }

/* ── Alert info ── */
.uc-alert-info {
    background: rgba(13,202,240,.08);
    border: 1px solid rgba(13,202,240,.2);
    border-radius: 8px; padding: .5rem .8rem;
    font-size: .78rem; color: var(--uc-text-muted);
    margin-top: .75rem;
}

/* ── Action buttons ── */
.uc-btn-group .btn { border-radius: 8px !important; margin-left: 3px; }

/* ── Pagination ── */
.uc-card-footer {
    background: var(--uc-card-header) !important;
    border-top: 1px solid var(--uc-border) !important;
    border-radius: 0 0 12px 12px !important;
    padding: .6rem 1rem;
}

/* ── Alert messages ── */
.uc-alert { border: none !important; border-radius: 10px !important; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 uc-text">
                <i class="bi bi-exclamation-triangle text-warning me-2"></i>Unclaimed Laundry
            </h4>
            <p class="small mb-0 uc-text-muted">Monitor unclaimed laundry across all branches</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.unclaimed.export') }}" class="btn btn-outline-success btn-sm shadow-sm">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
            <a href="{{ route('admin.unclaimed.history') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="bi bi-clock-history me-1"></i>History
            </a>
            <a href="{{ route('admin.unclaimed.remindAll') }}" class="btn btn-danger btn-sm shadow-sm"
               onclick="return confirm('Send reminders to all customers with unclaimed laundry (3+ days)?')">
                <i class="bi bi-bell-fill me-1"></i>Remind All
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show uc-alert shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show uc-alert shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show uc-alert shadow-sm mb-4">
            <i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Key Metrics --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-2 opacity-75">Total Value at Risk</h6>
                            <h2 class="fw-bold mb-1">₱{{ number_format($stats['total_value'], 0) }}</h2>
                            <small class="opacity-75">{{ $stats['total'] }} laundries</small>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-3">
                            <i class="bi bi-currency-dollar fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 uc-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="small mb-2 uc-text-muted">Critical (14+ days)</h6>
                            <h2 class="fw-bold text-danger mb-1">{{ $stats['critical'] }}</h2>
                            <small class="uc-text-muted">₱{{ number_format($stats['critical_value'], 0) }}</small>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded-3">
                            <i class="bi bi-exclamation-octagon fs-3 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 uc-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="small mb-2 uc-text-muted">Recovered (This Month)</h6>
                            <h2 class="fw-bold text-success mb-1">₱{{ number_format($stats['recovered_this_month'], 0) }}</h2>
                            <small class="uc-text-muted">Revenue saved</small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-3">
                            <i class="bi bi-graph-up-arrow fs-3 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 uc-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="small mb-2 uc-text-muted">Reminders Today</h6>
                            <h2 class="fw-bold mb-1 uc-text">{{ $stats['reminders_today'] }}</h2>
                            <small class="uc-text-muted">Notifications sent</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                            <i class="bi bi-bell fs-3 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Urgency Breakdown --}}
    <div class="card border-0 shadow-sm mb-4 uc-card">
        <div class="card-body py-2">
            <div class="row text-center g-0">
                <div class="col">
                    <a href="{{ route('admin.unclaimed.index', ['urgency' => 'critical']) }}"
                       class="urgency-pill {{ request('urgency') == 'critical' ? 'active-critical' : '' }}">
                        <span class="badge bg-danger fs-6 mb-1">{{ $stats['critical'] }}</span>
                        <strong>🚨 Critical</strong>
                        <span>14+ days</span>
                    </a>
                </div>
                <div class="col">
                    <a href="{{ route('admin.unclaimed.index', ['urgency' => 'urgent']) }}"
                       class="urgency-pill {{ request('urgency') == 'urgent' ? 'active-urgent' : '' }}">
                        <span class="badge bg-warning text-dark fs-6 mb-1">{{ $stats['urgent'] }}</span>
                        <strong>⚠️ Urgent</strong>
                        <span>7–13 days</span>
                    </a>
                </div>
                <div class="col">
                    <a href="{{ route('admin.unclaimed.index', ['urgency' => 'warning']) }}"
                       class="urgency-pill {{ request('urgency') == 'warning' ? 'active-warning' : '' }}">
                        <span class="badge bg-info fs-6 mb-1">{{ $stats['warning'] }}</span>
                        <strong>⏰ Warning</strong>
                        <span>3–6 days</span>
                    </a>
                </div>
                <div class="col">
                    <a href="{{ route('admin.unclaimed.index', ['urgency' => 'pending']) }}"
                       class="urgency-pill {{ request('urgency') == 'pending' ? 'active-pending' : '' }}">
                        <span class="badge bg-secondary fs-6 mb-1">{{ $stats['pending'] }}</span>
                        <strong>📌 Pending</strong>
                        <span>1–2 days</span>
                    </a>
                </div>
                <div class="col">
                    <a href="{{ route('admin.unclaimed.index') }}"
                       class="urgency-pill {{ !request('urgency') && !request('branch_id') ? 'active-all' : '' }}">
                        <span class="badge bg-primary fs-6 mb-1">{{ $stats['total'] }}</span>
                        <strong>📊 All</strong>
                        <span>Total</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Main Content --}}
        <div class="col-lg-9">

            {{-- Filters --}}
            <div class="card border-0 shadow-sm mb-4 uc-card">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label small fw-semibold mb-1 uc-text-muted">Search</label>
                            <input type="text" name="search" class="form-control uc-input"
                                   placeholder="Tracking #, customer, phone..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-lg-3 col-md-4">
                            <label class="form-label small fw-semibold mb-1 uc-text-muted">Branch</label>
                            <select name="branch_id" class="form-select uc-select">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <label class="form-label small fw-semibold mb-1 uc-text-muted">Min Days</label>
                            <input type="number" name="min_days" class="form-control uc-input"
                                   placeholder="0" min="0" value="{{ request('min_days') }}">
                        </div>
                        <div class="col-lg-3 col-md-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="bi bi-search me-1"></i>Filter
                                </button>
                                <a href="{{ route('admin.unclaimed.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Table --}}
            <div class="card border-0 shadow-sm uc-card">
                <div class="card-header uc-card-header d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold uc-text">
                        <i class="bi bi-list-ul me-2 text-primary"></i>Unclaimed Laundry
                    </h6>
                    <button type="button" class="btn btn-sm btn-primary" id="bulkReminderBtn" disabled>
                        <i class="bi bi-send me-1"></i>Send Selected
                    </button>
                </div>
                <div class="card-body p-0">
                    <form id="bulkForm" action="{{ route('admin.unclaimed.bulk-reminders') }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 uc-table">
                                <thead>
                                    <tr>
                                        <th width="40" class="ps-4">
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                        </th>
                                        <th>Laundry</th>
                                        <th>Customer</th>
                                        <th>Branch</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-center">Days</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($laundries as $laundry)
                                        @php
                                            $days    = $laundry->days_unclaimed ?? 0;
                                            $urgency = $laundry->unclaimed_status ?? 'normal';
                                            $color   = $laundry->unclaimed_color ?? 'secondary';
                                        @endphp
                                        <tr class="{{ $urgency === 'critical' ? 'row-critical' : ($urgency === 'urgent' ? 'row-warning-row' : '') }}">
                                            <td class="ps-4">
                                                <input type="checkbox" class="form-check-input laundry-checkbox"
                                                       name="laundry_ids[]" value="{{ $laundry->id }}">
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.unclaimed.show', $laundry) }}" class="fw-semibold uc-link">
                                                    {{ $laundry->tracking_number }}
                                                </a>
                                                <div class="small uc-text-muted">
                                                    Ready: {{ $laundry->ready_at?->format('M d, Y') ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold uc-text">{{ $laundry->customer->name ?? 'N/A' }}</div>
                                                <div class="small uc-text-muted">
                                                    <i class="bi bi-telephone me-1"></i>{{ $laundry->customer->phone ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="branch-badge">
                                                    {{ $laundry->branch->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="fw-semibold uc-text">₱{{ number_format($laundry->total_amount, 2) }}</div>
                                                @php $storageFee = $laundry->calculated_storage_fee ?? 0; @endphp
                                                @if($storageFee > 0)
                                                    <div class="small text-warning">
                                                        +₱{{ number_format($storageFee, 2) }} fee
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $color }} px-3 fs-6">{{ $days }}</span>
                                            </td>
                                            <td class="text-center">
                                                @switch($urgency)
                                                    @case('critical')
                                                        <span class="badge bg-danger">🚨 Critical</span>
                                                        @break
                                                    @case('urgent')
                                                        <span class="badge bg-warning text-dark">⚠️ Urgent</span>
                                                        @break
                                                    @case('warning')
                                                        <span class="badge bg-info">⏰ Warning</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">📌 Pending</span>
                                                @endswitch
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group btn-group-sm uc-btn-group">
                                                    {{-- Send Reminder --}}
                                                    <form action="{{ route('admin.unclaimed.send-reminder', $laundry->id) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-primary" title="Send Reminder">
                                                            <i class="bi bi-bell"></i>
                                                        </button>
                                                    </form>

                                                    {{-- Mark Claimed --}}
                                                    <form action="{{ route('admin.unclaimed.mark-claimed', $laundry->id) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success"
                                                                title="Mark Claimed"
                                                                onclick="return confirm('Mark as claimed?')">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    </form>

                                                    {{-- Dispose (only if 30+ days) --}}
                                                    <form action="{{ route('admin.unclaimed.mark-disposed', $laundry->id) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-outline-danger"
                                                                title="Dispose"
                                                                {{ $days < ($disposalThreshold ?? 30) ? 'disabled' : '' }}
                                                                onclick="return confirm('Dispose this laundry? This cannot be undone.')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="py-4">
                                                    <i class="bi bi-emoji-smile fs-1 text-success d-block mb-3"></i>
                                                    <h5 class="text-success fw-bold">Excellent!</h5>
                                                    <p class="uc-text-muted mb-0">No unclaimed laundry found.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>

                @if($laundries->hasPages())
                    <div class="uc-card-footer">
                        {{ $laundries->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-3">

            {{-- Branch Comparison --}}
            <div class="card border-0 shadow-sm mb-4 uc-card">
                <div class="card-header uc-card-header py-3">
                    <h6 class="mb-0 fw-bold uc-text">
                        <i class="bi bi-building me-2 text-primary"></i>By Branch
                    </h6>
                </div>
                <div class="card-body p-0">
                    @foreach($branchStats as $branch)
                        <a href="{{ route('admin.unclaimed.index', ['branch_id' => $branch['id']]) }}"
                           class="branch-link {{ request('branch_id') == $branch['id'] ? 'active' : '' }}">
                            <div>
                                <strong>{{ $branch['name'] }}</strong>
                                <small class="d-block">₱{{ number_format($branch['value'], 0) }} at risk</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $branch['critical'] > 0 ? 'danger' : 'secondary' }} px-2">
                                    {{ $branch['total'] }}
                                </span>
                                @if($branch['critical'] > 0)
                                    <div class="small text-danger fw-semibold">{{ $branch['critical'] }} critical</div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Potential Revenue --}}
            <div class="card border-0 shadow-sm mb-4 uc-card">
                <div class="card-header uc-card-header py-3">
                    <h6 class="mb-0 fw-bold uc-text">
                        <i class="bi bi-cash-stack me-2 text-primary"></i>Potential Revenue
                    </h6>
                </div>
                <div class="card-body">
                    <div class="uc-sb-row">
                        <div>
                            <div class="label">Laundry Value</div>
                            <div class="value">₱{{ number_format($stats['total_value'], 2) }}</div>
                        </div>
                    </div>
                    <div class="uc-sb-row">
                        <div>
                            <div class="label">Storage Fees</div>
                            <div class="value yellow">₱{{ number_format($stats['storage_fees'], 2) }}</div>
                        </div>
                    </div>
                    <div class="uc-sb-row">
                        <div>
                            <div class="label fw-bold uc-text" style="font-size:.9rem;">Total Potential</div>
                            <div class="value green big">₱{{ number_format($stats['potential_total'], 2) }}</div>
                        </div>
                    </div>
                    <div class="uc-alert-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Storage: ₱{{ config('unclaimed.storage_fee_per_day', 10) }}/day after 7 days
                    </div>
                </div>
            </div>

            {{-- This Month --}}
            <div class="card border-0 shadow-sm uc-card">
                <div class="card-header uc-card-header py-3">
                    <h6 class="mb-0 fw-bold uc-text">
                        <i class="bi bi-calendar3 me-2 text-primary"></i>This Month
                    </h6>
                </div>
                <div class="card-body">
                    <div class="uc-sb-row">
                        <div>
                            <div class="label">Recovered</div>
                            <div class="value green">₱{{ number_format($stats['recovered_this_month'], 0) }}</div>
                        </div>
                        <i class="bi bi-arrow-up-circle-fill text-success fs-3"></i>
                    </div>
                    <div class="uc-sb-row">
                        <div>
                            <div class="label">Disposed</div>
                            <div class="value">{{ $stats['disposed_this_month'] }} laundries</div>
                        </div>
                        <i class="bi bi-trash-fill uc-text-muted fs-3"></i>
                    </div>
                    <div class="uc-sb-row">
                        <div>
                            <div class="label">Lost Revenue</div>
                            <div class="value red">₱{{ number_format($stats['loss_this_month'] ?? 0, 0) }}</div>
                        </div>
                        <i class="bi bi-arrow-down-circle-fill text-danger fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('selectAll')?.addEventListener('change', function () {
    document.querySelectorAll('.laundry-checkbox').forEach(cb => cb.checked = this.checked);
    updateBulkBtn();
});
document.querySelectorAll('.laundry-checkbox').forEach(cb => cb.addEventListener('change', updateBulkBtn));

function updateBulkBtn() {
    const n   = document.querySelectorAll('.laundry-checkbox:checked').length;
    const btn = document.getElementById('bulkReminderBtn');
    btn.disabled = n === 0;
    btn.innerHTML = n > 0
        ? `<i class="bi bi-send me-1"></i> Send (${n})`
        : `<i class="bi bi-send me-1"></i> Send Selected`;
}

document.getElementById('bulkReminderBtn')?.addEventListener('click', function () {
    if (confirm('Send reminders to all selected customers?'))
        document.getElementById('bulkForm').submit();
});
</script>
@endpush
