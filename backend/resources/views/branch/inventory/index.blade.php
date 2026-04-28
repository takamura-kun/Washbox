@extends('branch.layouts.app')

@section('title', 'Inventory - ' . $branch->name)

@push('styles')
<style>
.card {
    background: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}
.card-body {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
}
.table-responsive {
    background: var(--card-bg) !important;
}
.table {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
}
.table tbody tr {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
}
.table thead th {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}
.table tbody td {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0" style="color: var(--text-primary);"><i class="fas fa-warehouse me-2"></i>{{ $branch->name }} - Inventory</h1>
            <p class="small mt-1" style="color: var(--text-secondary);">View inventory for your branch</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('branch.inventory.export') }}" class="btn btn-outline-secondary">
                <i class="fas fa-download me-2"></i>Export
            </a>
        </div>
    </div>

    {{-- Quick Navigation --}}
    <div class="btn-group mb-4 w-100" role="group">
        <a href="{{ route('branch.inventory.index') }}" class="btn btn-primary active">
            <i class="fas fa-list me-1"></i>All Items
        </a>
        <a href="{{ route('branch.inventory.low-stock') }}" class="btn btn-outline-warning">
            <i class="fas fa-exclamation-triangle me-1"></i>Low Stock
        </a>
        <a href="{{ route('branch.inventory.out-of-stock') }}" class="btn btn-outline-danger">
            <i class="fas fa-times-circle me-1"></i>Out of Stock
        </a>
        <a href="{{ route('branch.inventory.requests') }}" class="btn btn-outline-info">
            <i class="fas fa-paper-plane me-1"></i>Requests
        </a>
        <a href="{{ route('branch.inventory.history') }}" class="btn btn-outline-secondary">
            <i class="fas fa-history me-1"></i>History
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="small mb-1" style="color: var(--text-secondary);">Total Items</p>
                    <h4 class="mb-0" style="color: var(--text-primary);">{{ $items->total() }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="small mb-1" style="color: var(--text-secondary);">Low Stock</p>
                    <h4 class="mb-0 text-warning">{{ $items->getCollection()->where('stock_status', 'low_stock')->count() }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="small mb-1" style="color: var(--text-secondary);">Out of Stock</p>
                    <h4 class="mb-0 text-danger">{{ $items->getCollection()->where('stock_status', 'out_of_stock')->count() }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="small mb-1" style="color: var(--text-secondary);">Total Value</p>
                    <h4 class="mb-0" style="color: var(--text-primary);">₱{{ number_format($items->getCollection()->sum('total_value'), 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search items..." value="{{ $search }}">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="low_stock" {{ $status === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ $status === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Stock</th>
                        <th>Reorder</th>
                        <th>Unit Cost</th>
                        <th>Total Value</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td><strong>{{ $item->name }}</strong></td>
                            <td>{{ $item->category->name ?? 'N/A' }}</td>
                            <td>{{ $item->unit }}</td>
                            <td><span class="badge bg-info">{{ $item->current_stock }}</span></td>
                            <td>{{ $item->reorder_point }}</td>
                            <td>₱{{ number_format($item->unit_cost, 2) }}</td>
                            <td><strong>₱{{ number_format($item->total_value, 2) }}</strong></td>
                            <td>
                                @if($item->stock_status === 'out_of_stock')
                                    <span class="badge bg-danger">Out</span>
                                @elseif($item->stock_status === 'low_stock')
                                    <span class="badge bg-warning">Low</span>
                                @else
                                    <span class="badge bg-success">OK</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('branch.inventory.show', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4" style="color: var(--text-secondary);">No items found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($items->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $items->links() }}
        </div>
    @endif
</div>
@endsection
