@extends('admin.layouts.app')

@section('title', 'New Retail Sale')

@section('content')
<div class="container-xl px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">New Retail Sale</h1>
        <a href="{{ route('admin.finance.retail-sales.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.finance.retail-sales.store') }}" method="POST" id="saleForm">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Branch <span class="text-danger">*</span></label>
                        <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Sale Date <span class="text-danger">*</span></label>
                        <input type="date" name="sale_date" class="form-control @error('sale_date') is-invalid @enderror"
                               value="{{ old('sale_date', date('Y-m-d')) }}" required>
                        @error('sale_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr>

                <h5 class="mb-3">Sale Items</h5>

                <div id="items-container">
                    <div class="sale-item border rounded p-3 mb-3">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Item <span class="text-danger">*</span></label>
                                <select name="items[0][inventory_item_id]" class="form-select item-select" required disabled>
                                    <option value="">Select Branch First</option>
                                </select>
                                <small class="text-muted">Select a branch to load available items</small>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="items[0][quantity]" class="form-control quantity-input" min="1" required>
                                <small class="text-muted stock-info"></small>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" name="items[0][unit_cost]" class="form-control unit-cost-input" step="0.01" min="0" required>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Total</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" class="form-control item-total" readonly>
                                </div>
                            </div>

                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-danger remove-item" style="display:none;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-secondary mb-4" id="add-item">
                    <i class="bi bi-plus-circle me-2"></i>Add Another Item
                </button>

                <div class="card bg-light">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 text-end">
                                <h4>Grand Total:</h4>
                            </div>
                            <div class="col-md-4">
                                <h4 class="text-success">₱<span id="grand-total">0.00</span></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Record Sale
                    </button>
                    <a href="{{ route('admin.finance.retail-sales.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let itemIndex = 1;

// Load items when branch is selected
document.getElementById('branch_id').addEventListener('change', async function() {
    const branchId = this.value;
    const itemSelects = document.querySelectorAll('.item-select');
    
    if (!branchId) {
        itemSelects.forEach(select => {
            select.innerHTML = '<option value="">Select Branch First</option>';
            select.disabled = true;
        });
        return;
    }
    
    try {
        const response = await fetch(`/admin/finance/retail-sales/items/${branchId}`);
        const items = await response.json();
        
        itemSelects.forEach(select => {
            select.innerHTML = '<option value="">Select Item</option>';
            items.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = `${item.name} ${item.brand ? '(' + item.brand + ')' : ''} - Stock: ${item.available_stock} ${item.distribution_unit}`;
                option.dataset.price = item.unit_cost;
                option.dataset.stock = item.available_stock;
                option.dataset.unit = item.distribution_unit;
                select.appendChild(option);
            });
            select.disabled = false;
        });
    } catch (error) {
        console.error('Error loading items:', error);
        alert('Failed to load items. Please try again.');
    }
});

document.getElementById('add-item').addEventListener('click', function() {
    const container = document.getElementById('items-container');
    const template = container.querySelector('.sale-item').cloneNode(true);

    template.querySelectorAll('select, input').forEach(field => {
        const name = field.getAttribute('name');
        if (name) {
            field.setAttribute('name', name.replace(/\[\d+\]/, `[${itemIndex}]`));
            field.value = '';
        }
    });

    template.querySelector('.remove-item').style.display = 'block';
    container.appendChild(template);
    itemIndex++;

    attachItemListeners(template);
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-item')) {
        e.target.closest('.sale-item').remove();
        calculateGrandTotal();
    }
});

function attachItemListeners(item) {
    const select = item.querySelector('.item-select');
    const quantityInput = item.querySelector('.quantity-input');
    const unitCostInput = item.querySelector('.unit-cost-input');
    const itemTotal = item.querySelector('.item-total');
    const stockInfo = item.querySelector('.stock-info');

    select.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        unitCostInput.value = option.dataset.price || 0;
        stockInfo.textContent = option.dataset.stock ? `Stock: ${option.dataset.stock} ${option.dataset.unit || ''}` : '';
        calculateItemTotal(item);
    });

    quantityInput.addEventListener('input', () => calculateItemTotal(item));
    unitCostInput.addEventListener('input', () => calculateItemTotal(item));
}

function calculateItemTotal(item) {
    const quantity = parseFloat(item.querySelector('.quantity-input').value) || 0;
    const unitCost = parseFloat(item.querySelector('.unit-cost-input').value) || 0;
    const total = quantity * unitCost;

    item.querySelector('.item-total').value = total.toFixed(2);
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let total = 0;
    document.querySelectorAll('.item-total').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    document.getElementById('grand-total').textContent = total.toFixed(2);
}

attachItemListeners(document.querySelector('.sale-item'));
</script>
@endpush
@endsection
