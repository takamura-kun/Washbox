@extends('branch.layouts.app')

@section('title', 'Out of Stock Items')

@section('content')
<div class="container-xl px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-exclamation-octagon text-danger me-2"></i>Out of Stock Items
            </h2>
            <p class="text-muted mb-0">Items with zero stock - immediate action required</p>
        </div>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#bulkRequestModal">
            <i class="bi bi-cart-plus me-2"></i>Request All
        </button>
    </div>

    {{-- Quick Navigation --}}
    <div class="btn-group mb-4 w-100" role="group">
        <a href="{{ route('branch.inventory.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-list me-1"></i>All Items
        </a>
        <a href="{{ route('branch.inventory.low-stock') }}" class="btn btn-outline-warning">
            <i class="fas fa-exclamation-triangle me-1"></i>Low Stock
        </a>
        <a href="{{ route('branch.inventory.out-of-stock') }}" class="btn btn-danger active">
            <i class="fas fa-times-circle me-1"></i>Out of Stock
        </a>
        <a href="{{ route('branch.inventory.requests') }}" class="btn btn-outline-info">
            <i class="fas fa-paper-plane me-1"></i>Requests
        </a>
        <a href="{{ route('branch.inventory.history') }}" class="btn btn-outline-secondary">
            <i class="fas fa-history me-1"></i>History
        </a>
    </div>

    {{-- Critical Alert --}}
    @if($outOfStocks->count() > 0)
        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-octagon-fill me-3 fs-4"></i>
            <div>
                <strong>CRITICAL:</strong> {{ $outOfStocks->count() }} items are completely out of stock. 
                Services may be affected. Please request stock immediately.
            </div>
        </div>
    @endif

    {{-- Out of Stock Items --}}
    <div class="row g-3">
        @forelse($outOfStocks as $stock)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-danger h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1">{{ $stock->inventoryItem->name }}</h5>
                                <small class="text-muted">{{ $stock->inventoryItem->category->name ?? 'Uncategorized' }}</small>
                            </div>
                            <span class="badge bg-danger">Out of Stock</span>
                        </div>

                        <div class="alert alert-danger py-2 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-x-circle-fill me-2"></i>
                                <div>
                                    <strong>0 {{ $stock->inventoryItem->distribution_unit ?? 'pcs' }}</strong>
                                    <br>
                                    <small>Available</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Reorder Point:</span>
                                <strong>{{ number_format($stock->reorder_point) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Last Updated:</span>
                                <small>{{ $stock->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-sm btn-danger" onclick="requestStock({{ $stock->inventory_item_id }}, '{{ $stock->inventoryItem->name }}', {{ $stock->reorder_point }})">
                                <i class="bi bi-cart-plus me-1"></i>Request Stock Now
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
                        <h4>No Out of Stock Items!</h4>
                        <p class="text-muted">All items have stock available. Great job maintaining inventory levels!</p>
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
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2"></i>Urgent Stock Request
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="inventory_item_id" id="request_item_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <input type="text" class="form-control" id="request_item_name" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity Needed <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="request_quantity" class="form-control" min="1" required>
                        <small class="text-muted">Minimum reorder quantity will be pre-filled</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Urgency Reason</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Explain why this is urgent..." required></textarea>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>This item is out of stock. Your request will be marked as urgent.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-send me-1"></i>Submit Urgent Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Bulk Request Modal --}}
<div class="modal fade" id="bulkRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cart-plus me-2"></i>Request All Out of Stock Items
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>You are about to request stock for <strong>{{ $outOfStocks->count() }} items</strong> that are currently out of stock.</p>
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Suggested Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($outOfStocks as $stock)
                                <tr>
                                    <td>{{ $stock->inventoryItem->name }}</td>
                                    <td>{{ number_format($stock->reorder_point) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Each item will be requested with its reorder point quantity. You can modify individual requests later.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitBulkRequest()">
                    <i class="bi bi-send me-1"></i>Submit All Requests
                </button>
            </div>
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

function submitBulkRequest() {
    // In a real implementation, this would submit multiple requests
    alert('Bulk request feature coming soon! Please request items individually for now.');
}
</script>
@endpush
@endsection
