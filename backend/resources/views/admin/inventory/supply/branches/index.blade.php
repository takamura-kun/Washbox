@extends('admin.layouts.app')

@section('title', 'Branch Inventory')
@section('page-title', 'Branch Inventory')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
@endpush

@section('content')
<div class="container-fluid px-4 py-2">
    {{-- Navigation Tabs --}}
    <div class="supply-tabs mb-4">
        <a href="{{ route('admin.inventory.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.inventory.index') }}">All Items</a>
        <a href="{{ route('admin.inventory.manage') }}">Manage</a>
        <a href="{{ route('admin.inventory.purchases.index') }}">Purchases</a>
        <a href="{{ route('admin.inventory.distribute.index') }}">Distribute</a>
        <a href="{{ route('admin.inventory.branch-stock') }}" class="active">Branches</a>
        <a href="{{ route('admin.inventory.dist-log') }}">Dist-log</a>
    </div>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <p class="text-muted small mb-0">Current stock levels at each branch</p>
        </div>
        <a href="{{ route('admin.inventory.distribute.index') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-box-arrow-right me-2"></i>Send Supplies
        </a>
    </div>

    {{-- Branch Tab Switcher --}}
    <div class="d-flex gap-2 mb-3 flex-wrap" id="branchTabs">
        @foreach($branches as $index => $branch)
        <button
            class="branch-tab-btn {{ $index === 0 ? 'active' : '' }}"
            data-branch="{{ $branch['id'] }}"
            data-color="{{ $branch['color'] }}"
            data-color-light="{{ $branch['color_light'] }}"
            onclick="switchBranch('{{ $branch['id'] }}', this)"
            style="
                padding: 8px 20px;
                border-radius: 10px;
                border: 1.5px solid {{ $index === 0 ? $branch['color'] : 'var(--bs-border-color)' }};
                background: {{ $index === 0 ? $branch['color_light'] : 'transparent' }};
                color: {{ $index === 0 ? $branch['color'] : 'var(--bs-secondary)' }};
                font-weight: {{ $index === 0 ? '600' : '400' }};
                font-size: 13px;
                cursor: pointer;
                transition: all .2s;
                display: flex;
                align-items: center;
                gap: 8px;
            ">
            <span style="
                width: 8px; height: 8px; border-radius: 50%;
                background: {{ $branch['color'] }};
                display: inline-block;
                flex-shrink: 0;
            "></span>
            {{ $branch['name'] }}
            @if($branch['low_count'] > 0)
                <span class="badge rounded-pill bg-danger" style="font-size:9px;padding:2px 6px">
                    {{ $branch['low_count'] }}
                </span>
            @endif
        </button>
        @endforeach
    </div>

    {{-- Branch Panels --}}
    @foreach($branches as $index => $branch)
    <div id="panel_{{ $branch['id'] }}" class="branch-panel" style="{{ $index > 0 ? 'display:none' : '' }}">

        {{-- Branch Metric Cards --}}
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 branch-stat-card h-100"
                     style="border-left: 3px solid {{ $branch['color'] }} !important; border-radius: 0 12px 12px 0 !important;">
                    <div class="card-body p-3">
                        <small class="text-muted d-block" style="font-size:0.7rem">Total units</small>
                        <h3 class="fw-bold mb-0" style="font-size:1.4rem">
                            {{ number_format($branch['total_units']) }}
                        </h3>
                        <small class="text-muted" style="font-size:0.68rem">across all items</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 branch-stat-card h-100">
                    <div class="card-body p-3">
                        <small class="text-muted d-block" style="font-size:0.7rem">Inventory value</small>
                        <h3 class="fw-bold text-success mb-0" style="font-size:1.4rem">
                            ₱{{ number_format($branch['total_value'], 2) }}
                        </h3>
                        <small class="text-muted" style="font-size:0.68rem">at cost price</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 branch-stat-card h-100">
                    <div class="card-body p-3">
                        <small class="text-muted d-block" style="font-size:0.7rem">Items stocked</small>
                        <h3 class="fw-bold mb-0" style="font-size:1.4rem">
                            {{ $branch['stocked_count'] }}
                            <span class="text-muted fw-normal" style="font-size:0.9rem">
                                / {{ $branch['total_items'] }}
                            </span>
                        </h3>
                        <small class="text-muted" style="font-size:0.68rem">items have stock</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 branch-stat-card h-100
                    {{ $branch['low_count'] > 0 ? 'border-warning border' : '' }}">
                    <div class="card-body p-3">
                        <small class="text-muted d-block" style="font-size:0.7rem">Low / out of stock</small>
                        <h3 class="fw-bold mb-0 {{ $branch['low_count'] > 0 ? 'text-warning' : 'text-success' }}"
                            style="font-size:1.4rem">
                            {{ $branch['low_count'] }}
                        </h3>
                        <small class="{{ $branch['low_count'] > 0 ? 'text-warning' : 'text-muted' }}"
                               style="font-size:0.68rem">
                            {{ $branch['low_count'] > 0 ? 'needs restocking' : 'all good' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items grouped by category --}}
        @if($branch['items']->isEmpty())
            <div class="card border-0 shadow-sm rounded-4 branch-stat-card">
                <div class="card-body py-5 text-center">
                    <i class="bi bi-box-seam text-muted" style="font-size:3rem;opacity:.2"></i>
                    <h5 class="fw-bold mt-3">No stock yet for {{ $branch['name'] }}</h5>
                    <p class="text-muted mb-3">Distribute supplies from the warehouse to this branch.</p>
                    <a href="{{ route('admin.inventory.distribute.index') }}" class="btn btn-primary">
                        <i class="bi bi-box-arrow-right me-2"></i>Send Supplies
                    </a>
                </div>
            </div>
        @else
            @foreach($categories as $category)
            @php $catItems = $branch['items']->where('category_id', $category->id); @endphp
            @if($catItems->isNotEmpty())
            <div class="card border-0 shadow-sm rounded-4 branch-stat-card mb-3">

                {{-- Category Header --}}
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="cat-pill-badge"
                              style="background:{{ $category->color_bg }};
                                     color:{{ $category->color_text }};
                                     border:1px solid {{ $category->color_border }};
                                     padding:3px 12px;
                                     border-radius:20px;
                                     font-size:12px;
                                     font-weight:600;">
                            {{ $category->name }}
                        </span>
                        <small class="text-muted">{{ $catItems->count() }} items</small>
                    </div>
                    <small class="text-muted">
                        <strong>{{ number_format($catItems->sum('branch_stock')) }}</strong>
                        units in branch
                    </small>
                </div>

                {{-- Items Table --}}
                <div class="table-responsive">
                    <table class="table table-hover mb-0 branch-table">
                        <thead>
                            <tr style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.04em">
                                <th class="ps-4 py-2 text-muted fw-semibold" style="width:28%">Item / Brand</th>
                                <th class="py-2 text-muted fw-semibold" style="width:30%">Stock level</th>
                                <th class="py-2 text-muted fw-semibold" style="width:12%">Units</th>
                                <th class="py-2 text-muted fw-semibold" style="width:16%">Value</th>
                                <th class="py-2 text-muted fw-semibold" style="width:10%">Status</th>
                                <th class="py-2" style="width:10%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($catItems as $item)
                            @php
                                $pcs      = $item->branch_stock;
                                $maxLevel = $item->max_level ?? 300;
                                $pct      = $maxLevel > 0 ? min($pcs / $maxLevel, 1) : 0;
                                $barColor = $pct > 0.4 ? '#639922' : ($pct > 0.15 ? '#EF9F27' : '#E24B4A');
                                $status   = $pcs <= 0 ? 'out' : ($pcs < ($item->reorder_point ?? 60) ? 'low' : 'ok');
                                $val      = $pcs * ($item->cost_per_unit ?? 0);
                            @endphp
                            <tr>
                                <td class="ps-4 py-3 align-middle">
                                    <div class="fw-semibold" style="font-size:13px">{{ $item->name }}</div>
                                    <span class="badge bg-light text-secondary border"
                                          style="font-size:10px;font-weight:400">
                                        {{ $item->brand }}
                                    </span>
                                </td>
                                <td class="py-3 align-middle" style="min-width:140px">
                                    <div class="progress mb-1" style="height:6px;border-radius:4px">
                                        <div class="progress-bar"
                                             style="width:{{ round($pct * 100) }}%;
                                                    background:{{ $barColor }};
                                                    border-radius:4px">
                                        </div>
                                    </div>
                                    <small class="text-muted" style="font-size:10px">
                                        {{ round($pct * 100) }}% of max ({{ number_format($maxLevel) }})
                                    </small>
                                </td>
                                <td class="py-3 align-middle">
                                    <span class="fw-semibold">{{ number_format($pcs) }}</span>
                                    <small class="text-muted d-block" style="font-size:10px">
                                        {{ $item->unit_label }}s
                                    </small>
                                </td>
                                <td class="py-3 align-middle">
                                    <span class="fw-semibold text-success">
                                        {{ $val > 0 ? '₱'.number_format($val, 2) : '—' }}
                                    </span>
                                </td>
                                <td class="py-3 align-middle">
                                    @if($status === 'ok')
                                        <span class="badge rounded-pill"
                                              style="background:#EAF3DE;color:#27500A;
                                                     font-size:11px;padding:4px 10px">
                                            In stock
                                        </span>
                                    @elseif($status === 'low')
                                        <span class="badge rounded-pill"
                                              style="background:#FAEEDA;color:#633806;
                                                     font-size:11px;padding:4px 10px">
                                            Low
                                        </span>
                                    @else
                                        <span class="badge rounded-pill"
                                              style="background:#FCEBEB;color:#791F1F;
                                                     font-size:11px;padding:4px 10px">
                                            Out
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 align-middle">
                                    <a href="{{ route('admin.inventory.distribute.index', ['item' => $item->id]) }}"
                                       class="btn btn-sm btn-outline-primary rounded-3"
                                       style="font-size:11px;padding:3px 10px">
                                        Restock
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            @endforeach
        @endif

    </div>
    @endforeach

</div>
@endsection

@push('styles')
<style>
    .branch-stat-card {
        background-color: #ffffff;
        color: #000000;
    }
    [data-theme="dark"] .branch-stat-card {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }
    [data-theme="dark"] .branch-stat-card .card-body {
        background-color: #1e293b !important;
        color: #f1f5f9 !important;
    }
    [data-theme="dark"] .branch-stat-card h3,
    [data-theme="dark"] .branch-stat-card .fw-bold {
        color: #f1f5f9 !important;
    }
    [data-theme="dark"] .branch-stat-card .text-muted {
        color: #94a3b8 !important;
    }
    [data-theme="dark"] .branch-stat-card .card-header {
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }
    [data-theme="dark"] .branch-table {
        color: #f1f5f9 !important;
    }
    [data-theme="dark"] .branch-table thead tr {
        background-color: rgba(255,255,255,0.03) !important;
    }
    [data-theme="dark"] .branch-table tbody tr {
        border-color: #334155 !important;
    }
    [data-theme="dark"] .branch-table tbody tr:hover {
        background-color: rgba(255,255,255,0.03) !important;
    }
    [data-theme="dark"] .branch-table tbody td {
        color: #f1f5f9 !important;
        border-color: #334155 !important;
    }
    [data-theme="dark"] .branch-table .badge.bg-light {
        background-color: rgba(255,255,255,0.08) !important;
        color: #cbd5e1 !important;
        border-color: #475569 !important;
    }
    [data-theme="dark"] .branch-tab-btn {
        border-color: #334155 !important;
        color: #94a3b8 !important;
    }
    .branch-tab-btn:hover {
        opacity: 0.85;
    }
</style>
@endpush

@push('scripts')
<script>
function switchBranch(id, btn) {
    const color      = btn.getAttribute('data-color');
    const colorLight = btn.getAttribute('data-color-light');

    document.querySelectorAll('.branch-panel').forEach(p => p.style.display = 'none');

    document.querySelectorAll('.branch-tab-btn').forEach(t => {
        t.style.background   = 'transparent';
        t.style.borderColor  = 'var(--bs-border-color)';
        t.style.color        = 'var(--bs-secondary)';
        t.style.fontWeight   = '400';
        t.classList.remove('active');
    });

    document.getElementById('panel_' + id).style.display = 'block';

    btn.style.background  = colorLight;
    btn.style.borderColor = color;
    btn.style.color       = color;
    btn.style.fontWeight  = '600';
    btn.classList.add('active');
}
</script>
@endpush
