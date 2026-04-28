@extends('admin.layouts.app')

@section('title', 'Inventory Dashboard - Health & Alerts')
@section('page-title', 'Inventory Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<style>
    .kpi-card-inventory {
        border-radius: 12px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 1px solid #e5e7eb;
        padding: 24px;
        transition: all 0.3s ease;
    }
    .kpi-card-inventory:hover {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }
    .kpi-value-large {
        font-size: 2.5rem;
        font-weight: 800;
        margin: 10px 0;
        display: block;
    }
    .kpi-label-small {
        font-size: 0.9rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    .status-critical { background: #ef4444; animation: pulse-critical 2s infinite; }
    .status-warning { background: #f59e0b; animation: pulse-warning 2s infinite; }
    .status-ok { background: #10b981; }

    @keyframes pulse-critical {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    @keyframes pulse-warning {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    .alert-item {
        border-left: 4px solid #ef4444;
        background: #fef2f2;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 12px;
    }
    .alert-item.warning {
        border-left-color: #f59e0b;
        background: #fffbeb;
    }
    .alert-item.info {
        border-left-color: #3b82f6;
        background: #eff6ff;
    }

    .inventory-table-row {
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 0;
    }
    .inventory-table-row:hover {
        background: #f9fafb;
    }
</style>
@endpush

@section('content')
<div class="container-xl px-4 py-4 dashboard-modern-wrapper">
    {{-- Header --}}
    <div class="glass-header mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex flex-column gap-2">
                    <h1 class="h4 mb-0">Inventory Dashboard</h1>
                    <p class="text-muted mb-0">Monitor stock levels, identify alerts, and track inventory performance.</p>
                </div>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0 text-lg-end">
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary rounded-pill me-2">
                    <i class="bi bi-arrow-left me-2"></i> Back
                </a>
                <a href="{{ route('admin.inventory.adjustments.create') }}" class="btn btn-primary rounded-pill">
                    <i class="bi bi-plus-circle me-2"></i> Adjust Stock
                </a>
            </div>
        </div>
    </div>

    {{-- Navigation Tabs --}}
    <div class="supply-tabs mb-4">
        <a href="{{ route('admin.inventory.dashboard') }}" class="active">Dashboard</a>
        <a href="{{ route('admin.inventory.index') }}">All Items</a>
        <a href="{{ route('admin.inventory.manage') }}">Manage</a>
        <a href="{{ route('admin.inventory.purchases.index') }}">Purchases</a>
        <a href="{{ route('admin.inventory.distribute.index') }}">Distribute</a>
        <a href="{{ route('admin.inventory.branch-stock') }}">Branches</a>
        <a href="{{ route('admin.inventory.dist-log') }}">Dist-log</a>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="kpi-card-inventory">
                <div class="kpi-label-small">Total Items</div>
                <span class="kpi-value-large text-slate-900">{{ $stats['total_items'] ?? 0 }}</span>
                <small class="text-muted">In inventory system</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="kpi-card-inventory">
                <div class="kpi-label-small">
                    <span class="status-indicator status-ok"></span> In Stock
                </div>
                <span class="kpi-value-large text-success">{{ $stats['items_in_stock'] ?? 0 }}</span>
                <small class="text-muted">Active items</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="kpi-card-inventory">
                <div class="kpi-label-small">
                    <span class="status-indicator status-warning"></span> Low Stock
                </div>
                <span class="kpi-value-large text-warning">{{ $stats['low_stock_count'] ?? 0 }}</span>
                <small class="text-muted">Below reorder point</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="kpi-card-inventory">
                <div class="kpi-label-small">
                    <span class="status-indicator status-critical"></span> Out of Stock
                </div>
                <span class="kpi-value-large text-danger">{{ $stats['out_of_stock_count'] ?? 0 }}</span>
                <small class="text-muted">Needs immediate action</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- LEFT COLUMN: ALERTS & WARNINGS --}}
        <div class="col-lg-6">
            <div class="inventory-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-circle text-danger me-2"></i>Out of Stock Items
                    </h5>
                    <span class="badge bg-danger">{{ count($stats['out_of_stock'] ?? []) }}</span>
                </div>
                <div class="inventory-card-body">
                    @if(!empty($stats['out_of_stock']))
                        @foreach($stats['out_of_stock'] as $item)
                            <div class="alert-item">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-1">{{ $item->name }}</h6>
                                        <small class="text-muted">
                                            Category: {{ $item->category?->name ?? 'Uncategorized' }}
                                            <br>
                                            Reorder Point: {{ $item->reorder_point ?? 0 }} units
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <a href="{{ route('admin.inventory.purchases.create') }}" class="btn btn-sm btn-danger">
                                            <i class="bi bi-cart-plus me-1"></i> Buy
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                            <p class="mt-2">All items in stock ✓</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: LOW STOCK WARNINGS --}}
        <div class="col-lg-6">
            <div class="inventory-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>Low Stock Items
                    </h5>
                    <span class="badge bg-warning">{{ count($stats['low_stock'] ?? []) }}</span>
                </div>
                <div class="inventory-card-body">
                    @if(!empty($stats['low_stock']))
                        @foreach($stats['low_stock'] as $item)
                            <div class="alert-item warning">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-1">{{ $item->name }}</h6>
                                        <small class="text-muted">
                                            Current: {{ $item->centralStock?->quantity ?? 0 }}
                                            | Reorder: {{ $item->reorder_point ?? 0 }}
                                            | Max: {{ $item->max_level ?? 0 }}
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <a href="{{ route('admin.inventory.purchases.create') }}" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-cart-plus me-1"></i>Order
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                            <p class="mt-2">All stock levels healthy ✓</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- EXPIRING ITEMS (if tracking enabled) --}}
    @if(!empty($stats['expiring_soon']))
        <div class="row g-4 mt-2">
            <div class="col-lg-12">
                <div class="inventory-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="bi bi-hourglass-end text-danger me-2"></i>Expiring Soon
                        </h5>
                        <span class="badge bg-danger">{{ count($stats['expiring_soon'] ?? []) }}</span>
                    </div>
                    <div class="inventory-card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr class="border-bottom-2">
                                        <th>Item</th>
                                        <th>Batch</th>
                                        <th>Quantity</th>
                                        <th>Expires</th>
                                        <th>Days Left</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats['expiring_soon'] as $purchase)
                                        <tr class="inventory-table-row">
                                            <td>{{ $purchase->inventoryItem?->name ?? 'Unknown' }}</td>
                                            <td><code>{{ $purchase->batch_number ?? 'N/A' }}</code></td>
                                            <td><strong>{{ $purchase->quantity_remaining ?? 0 }}</strong></td>
                                            <td>{{ $purchase->expiration_date?->format('M d, Y') ?? 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $daysLeft = now()->diff($purchase->expiration_date)->days;
                                                    $class = $daysLeft <= 3 ? 'text-danger' : 'text-warning';
                                                @endphp
                                                <span class="{{ $class }} fw-bold">{{ $daysLeft }} days</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.inventory.adjustments.create') }}" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-dash-circle"></i> Remove
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- INVENTORY VALUE --}}
    <div class="row g-4 mt-2">
        <div class="col-lg-6">
            <div class="inventory-card">
                <h5 class="mb-3">
                    <i class="bi bi-cash-coin text-success me-2"></i>Inventory Value
                </h5>
                <div class="inventory-card-body">
                    <div class="text-center">
                        <div class="text-muted small mb-2">Total Inventory Value</div>
                        <div style="font-size: 2rem; font-weight: bold; color: #10b981;">
                            ₱{{ number_format($stats['total_inventory_value'] ?? 0, 2) }}
                        </div>
                        <small class="text-muted d-block mt-3">
                            Based on {{ $stats['total_items'] ?? 0 }} items
                            <br>
                            Average Value: ₱{{ number_format(($stats['total_inventory_value'] ?? 0) / max(1, $stats['total_items'] ?? 1), 2) }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="inventory-card">
                <h5 class="mb-3">
                    <i class="bi bi-arrow-repeat text-info me-2"></i>Recent Adjustments
                </h5>
                <div class="inventory-card-body">
                    @if(!empty($stats['recent_adjustments']))
                        <div style="max-height: 250px; overflow-y: auto;">
                            @foreach($stats['recent_adjustments'] as $adjustment)
                                <div class="pb-2 mb-2 border-bottom small">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $adjustment->item?->name ?? 'Unknown' }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ ucfirst($adjustment->type) }}:
                                                <span class="badge bg-secondary">{{ $adjustment->quantity }}x</span>
                                            </small>
                                        </div>
                                        <small class="text-muted">{{ $adjustment->created_at?->diffForHumans() ?? 'Recently' }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No recent adjustments</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="inventory-card">
                <h5 class="mb-3">Quick Actions</h5>
                <div class="inventory-card-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <a href="{{ route('admin.inventory.adjustments.create') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-arrow-left-right me-2"></i> Adjust Stock
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.inventory.purchases.create') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-cart-plus me-2"></i> New Purchase
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.inventory.distribute.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-truck me-2"></i> Distribute
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Floating Action Button --}}
<button class="btn btn-primary shadow-lg" onclick="window.location.href='{{ route('admin.inventory.adjustments.create') }}'"
    style="position: fixed; bottom: 24px; right: 24px; width: 56px; height: 56px; border-radius: 50%; z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 0;">
    <i class="bi bi-plus-lg" style="font-size: 1.5rem;"></i>
</button>

@push('scripts')
<script>
// Auto-refresh dashboard every 60 seconds
setTimeout(function() {
    location.reload();
}, 60000);
</script>
@endpush

@endsection
