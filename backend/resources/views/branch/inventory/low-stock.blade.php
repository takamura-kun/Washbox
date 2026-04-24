@extends('branch.layouts.app')

@section('title', 'Low Stock Alerts')

@section('content')
<div class="container-xl px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-exclamation-triangle text-warning me-2"></i>Low Stock Alerts
            </h2>
            <p class="text-muted mb-0">Items below reorder point</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestStockModal">
            <i class="bi bi-cart-plus me-2"></i>Request Stock
        </button>
    </div>

    {{-- Quick Navigation --}}
    <div class="btn-group mb-4 w-100" role="group">
        <a href="{{ route('branch.inventory.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-list me-1"></i>All Items
        </a>
        <a href="{{ route('branch.inventory.low-stock') }}" class="btn btn-warning active">
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

    {{-- Alert Summary --}}
    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
        <div>
            <strong>{{ $lowStocks->count() }} items</strong> are running low on stock and need to be reordered soon.
        </div>
    </div>

    {{-- Low Stock Items --}}
    <div class="row g-3">
        @forelse($lowStocks as $stock)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1">{{ $stock->inventoryItem->name }}</h5>
                                <small class="text-muted">{{ $stock->inventoryItem->category->name ?? 'Uncategorized' }}</small>
                            </div>
                            <span class="badge bg-warning">Low Stock</span>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Current Stock:</span>
                                <strong class="text-warning">{{ number_format($stock->current_stock) }} {{ $stock->inventoryItem->distribution_unit ?? 'pcs' }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Reorder Point:</span>
                                <strong>{{ number_format($stock->reorder_point) }}</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                @php
                                    $percentage = $stock->reorder_point > 0 ? ($stock->current_stock / $stock->reorder_point) * 100 : 0;
                                    $percentage = min($percentage, 100);
                                @endphp
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $percentage }}%"></div>
                            </div>
                            <small class="text-muted">{{ number_format($percentage, 1) }}% of reorder point</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-sm btn-primary" onclick="requestStock({{ $stock->inventory_item_id }}, '{{ $stock->inventoryItem->name }}', {{ $stock->reorder_point - $stock->current_stock }})">
                                <i class="bi bi-cart-plus me-1"></i>Request Stock
                            </button>
                            <a href="{{ route('branch.inventory.show', $stock->inventory_item_id) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-check-circle text-success display-1 mb-3"></i>
                        <h4>All Stock Levels are Good!</h4>
                        <p class="text-muted">No items are currently below their reorder point.</p>
                        <a href="{{ route('branch.inventory.index') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Inventory
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

{{-- Request Stock Modal --}}
<div class="modal fade" id="requestStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('branch.inventory.request-stock') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Request Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="inventory_item_id" id="request_item_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <input type="text" class="form-control" id="request_item_name" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity Needed</label>
                        <input type="number" name="quantity" id="request_quantity" class="form-control" min="1" required>
                        <small class="text-muted">Suggested quantity will be pre-filled</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason (Optional)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Why do you need this stock?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function requestStock(itemId, itemName, suggestedQty) {
    document.getElementById('request_item_id').value = itemId;
    document.getElementById('request_item_name').value = itemName;
    document.getElementById('request_quantity').value = Math.max(1, suggestedQty);
    
    const modal = new bootstrap.Modal(document.getElementById('requestStockModal'));
    modal.show();
}
</script>
@endpush
@endsection
