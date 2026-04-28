@extends('branch.layouts.app')

@section('title', 'New Retail Sale')

@push('styles')
<style>
    .card {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
    }
    .card-body, .card-header {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    .card-header.bg-light {
        background: var(--card-bg) !important;
    }
    .form-control, .form-select, textarea {
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
            <h1 class="h3 mb-0">New Retail Sale</h1>
            <p class="small mt-1" style="color: var(--text-secondary);">Record a sale to walk-in customer</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('branch.retail.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <form action="{{ route('branch.retail.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Customer Information (Optional)</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" name="customer_name" class="form-control" placeholder="Walk-in customer">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="customer_contact" class="form-control" placeholder="+63">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Sale Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Item <span class="text-danger">*</span></label>
                            <select name="inventory_item_id" id="item-select" class="form-select" required>
                                <option value="">Select item</option>
                                @foreach($items as $stock)
                                    <option value="{{ $stock->inventory_item_id }}" 
                                            data-price="{{ $stock->inventoryItem->unit_cost_price }}" 
                                            data-stock="{{ $stock->current_stock }}">
                                        {{ $stock->inventoryItem->name }} (Stock: {{ $stock->current_stock }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantity" class="form-control" min="1" value="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                                <input type="number" name="unit_price" id="unit-price" class="form-control" step="0.01" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Payment</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                                <option value="card">Card</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-3">Total: ₱<span id="total-amount">0.00</span></h5>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle me-2"></i>Complete Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('item-select').addEventListener('change', function() {
    const option = this.selectedOptions[0];
    const price = option.dataset.price || 0;
    document.getElementById('unit-price').value = price;
    calculateTotal();
});

document.getElementById('quantity').addEventListener('input', calculateTotal);
document.getElementById('unit-price').addEventListener('input', calculateTotal);

function calculateTotal() {
    const qty = parseFloat(document.getElementById('quantity').value) || 0;
    const price = parseFloat(document.getElementById('unit-price').value) || 0;
    const total = qty * price;
    document.getElementById('total-amount').textContent = total.toFixed(2);
}
</script>
@endsection
