@extends('admin.layouts.app')

@section('title', 'Distribution Log — Inventory')
@section('page-title', 'Distribution Log')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<style>
    /* Force table theme support */
    .table-responsive,
    .table,
    .table tbody,
    .table tbody tr,
    .table tbody tr td,
    .table thead,
    .table thead tr,
    .table thead tr th,
    .table tfoot,
    .table tfoot tr,
    .table tfoot tr th {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }

    .table thead {
        background: var(--border-color) !important;
    }

    .table tfoot {
        background: var(--border-color) !important;
    }

    .table tbody tr:hover {
        background: var(--border-color) !important;
        opacity: 0.95;
    }

    /* Ensure card body has proper background */
    .card-body {
        background: var(--card-bg) !important;
    }
</style>
@endpush

@section('content')
<div class="container-xl px-4 py-4 dashboard-modern-wrapper">
    {{-- Navigation Tabs --}}
    <div class="supply-tabs mb-4">
        <a href="{{ route('admin.inventory.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.inventory.index') }}">All Items</a>
        <a href="{{ route('admin.inventory.manage') }}">Manage</a>
        <a href="{{ route('admin.inventory.purchases.index') }}">Purchases</a>
        <a href="{{ route('admin.inventory.distribute.index') }}">Distribute</a>
        <a href="{{ route('admin.inventory.branch-stock') }}">Branches</a>
        <a href="{{ route('admin.inventory.dist-log') }}" class="active">Dist-log</a>
    </div>

    <div class="glass-header mb-4 shadow-sm">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex flex-column gap-2">

                    <h1 class="h4 mb-0">Distribution Log</h1>
                    <p class="mb-0" style="color: var(--text-secondary);">View all past distributions from warehouse to branches.</p>
                </div>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0 text-lg-end">
                <a href="{{ route('admin.inventory.distribute.index') }}" class="btn btn-primary btn-sm rounded-pill">
                    <i class="bi bi-plus-circle me-1"></i> New Distribution
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($distributions->isNotEmpty())
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small style="color: var(--text-secondary);" class="text-uppercase">Total Distributions</small>
                                <h3 class="mb-0 mt-1" style="color: var(--text-primary);">{{ number_format($distributions->total()) }}</h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="bi bi-box-seam text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small style="color: var(--text-secondary);" class="text-uppercase">Total Units Sent</small>
                                <h3 class="mb-0 mt-1" style="color: var(--text-primary);">{{ number_format($distributions->sum(fn($d) => $d->items->sum('quantity'))) }}</h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="bi bi-arrow-right-circle text-success fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small style="color: var(--text-secondary);" class="text-uppercase">Unique Items</small>
                                <h3 class="mb-0 mt-1" style="color: var(--text-primary);">{{ $distributions->flatMap(fn($d) => $d->items->pluck('inventory_item_id'))->unique()->count() }}</h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="bi bi-grid text-info fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small style="color: var(--text-secondary);" class="text-uppercase">Branches Served</small>
                                <h3 class="mb-0 mt-1" style="color: var(--text-primary);">{{ $distributions->flatMap(fn($d) => $d->items->pluck('branch_id'))->unique()->count() }}</h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="bi bi-shop text-warning fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if($distributions->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Items</th>
                                <th>Branches</th>
                                <th>Total Qty</th>
                                <th>Distributed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($distributions as $dist)
                                <tr class="distribution-row" data-bs-toggle="collapse" data-bs-target="#dist-{{ $dist->id }}" style="cursor: pointer;">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-chevron-right me-2 text-muted collapse-icon"></i>
                                            <div>
                                                <div class="fw-semibold">{{ $dist->distribution_date->format('M d, Y') }}</div>
                                                <small class="text-muted">{{ $dist->distribution_date->format('l') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">{{ $dist->reference_no }}</span>
                                    </td>
                                    <td>
                                        @if($dist->items->isNotEmpty())
                                            @php
                                                $uniqueItems = $dist->items->groupBy('inventory_item_id');
                                            @endphp
                                            <div class="small">
                                                @foreach($uniqueItems->take(2) as $itemId => $items)
                                                    @php
                                                        $firstItem = $items->first();
                                                        $totalQty = $items->sum('quantity');
                                                    @endphp
                                                    <div class="mb-1">
                                                        <strong>{{ $firstItem->item->name ?? 'Unknown' }}</strong>
                                                        <span class="text-muted">({{ number_format($totalQty) }} units)</span>
                                                    </div>
                                                @endforeach
                                                @if($uniqueItems->count() > 2)
                                                    <div class="text-muted">+{{ $uniqueItems->count() - 2 }} more items</div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">No items</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $branches = $dist->items->pluck('branch.name')->filter()->unique();
                                        @endphp
                                        @if($branches->isNotEmpty())
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($branches as $branchName)
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $branchName }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-primary">{{ number_format($dist->items->sum('quantity')) }}</strong>
                                        <small class="text-muted d-block">units total</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-circle me-2 text-muted"></i>
                                            <div>
                                                <div>{{ $dist->distributedBy->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $dist->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="collapse" id="dist-{{ $dist->id }}">
                                    <td colspan="6" class="bg-light">
                                        <div class="p-3">
                                            <h6 class="mb-3">Distribution Details</h6>
                                            <div class="row g-3">
                                                @php
                                                    $groupedByBranch = $dist->items->groupBy('branch_id');
                                                @endphp
                                                @foreach($groupedByBranch as $branchId => $branchItems)
                                                    <div class="col-md-6">
                                                        <div class="card border">
                                                            <div class="card-header bg-white">
                                                                <strong>{{ $branchItems->first()->branch->name ?? 'Unknown Branch' }}</strong>
                                                            </div>
                                                            <div class="card-body">
                                                                <table class="table table-sm mb-0">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Item</th>
                                                                            <th class="text-end">Quantity</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($branchItems as $item)
                                                                            <tr>
                                                                                <td>{{ $item->item->name ?? 'Unknown' }}</td>
                                                                                <td class="text-end"><strong>{{ number_format($item->quantity) }}</strong></td>
                                                                            </tr>
                                                                        @endforeach
                                                                        <tr class="table-light">
                                                                            <td><strong>Total</strong></td>
                                                                            <td class="text-end"><strong class="text-primary">{{ number_format($branchItems->sum('quantity')) }}</strong></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @if($dist->notes)
                                                <div class="mt-3 p-3 bg-white rounded border">
                                                    <strong>Notes:</strong> {{ $dist->notes }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($distributions->hasPages())
                    <div class="mt-4">
                        {{ $distributions->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3 mb-0">No distribution history yet.</p>
                    <a href="{{ route('admin.inventory.distribute.index') }}" class="btn btn-primary btn-sm rounded-pill mt-3">
                        <i class="bi bi-plus-circle me-1"></i> Create First Distribution
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
:root {
    --primary-blue: #0a58ca;
    --bg-color: #f8fafc;
}

.dashboard-modern-wrapper {
    background: var(--bg-color);
    border-radius: 1rem;
}

.glass-header {
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(10px);
    border-radius: 1.25rem;
    padding: 1.25rem 1.5rem;
    border: 1px solid rgba(148, 163, 184, 0.18);
}

.badge-status-live {
    background: rgba(220, 38, 38, 0.1);
    color: #dc2626;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.85rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #ef4444;
    animation: pulse-live 1.2s infinite;
}

@keyframes pulse-live {
    0%, 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    50% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
}

.v-divider {
    width: 1px;
    height: 20px;
    background: rgba(0, 0, 0, 0.1);
    display: inline-block;
}

.text-primary-blue {
    color: var(--primary-blue);
}

.table > :not(caption) > * > * {
    padding: 1rem 0.75rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(59, 130, 246, 0.05);
}

.distribution-row:hover {
    background-color: rgba(59, 130, 246, 0.08) !important;
}

.collapse-icon {
    transition: transform 0.2s ease;
}

.distribution-row[aria-expanded="true"] .collapse-icon {
    transform: rotate(90deg);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click event to distribution rows
    document.querySelectorAll('.distribution-row').forEach(row => {
        row.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
        });
    });
});
</script>
@endpush
