@extends('admin.layouts.app')

@section('title', 'Record Purchase - Inventory')
@section('page-title', 'Supply Purchases')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
@endpush

@section('content')
<div class="container-xl px-4 py-4 dashboard-modern-wrapper">
    {{-- Navigation Tabs --}}
    <div class="supply-tabs mb-4">
        <a href="{{ route('admin.inventory.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.inventory.index') }}">All Items</a>
        <a href="{{ route('admin.inventory.manage') }}">Manage</a>
        <a href="{{ route('admin.inventory.purchases.index') }}" class="active">Purchases</a>
        <a href="{{ route('admin.inventory.distribute.index') }}">Distribute</a>
        <a href="{{ route('admin.inventory.branch-stock') }}">Branches</a>
        <a href="{{ route('admin.inventory.dist-log') }}">Dist-log</a>
    </div>

    <div class="glass-header mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex align-items-center gap-2">
                     
                        <span class="v-divider"></span>
                        <i class="bi bi-receipt text-primary-blue"></i>
                        
                    </div>
                    <h1 class="h4 mb-0">Record a new purchase</h1>
                    <p class="text-muted mb-0">Add new supplier purchases to warehouse inventory.</p>
                </div>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0 text-lg-end">
                <a href="{{ route('admin.inventory.purchases.index') }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="bi bi-arrow-left me-2"></i> Back to Purchases
                </a>
            </div>
        </div>
    </div>

    <div class="inventory-card mb-4">
        <div class="inventory-card-body">
            @if($errors->any())
                <div class="inventory-alert danger mb-4">
                    <i class="bi bi-exclamation-triangle"></i>
                    <div class="inventory-alert-content">
                        <div class="inventory-alert-title">Please fix the following errors:</div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.inventory.purchases.store') }}" id="purchaseForm">
                @csrf

                <div class="inventory-alert info mb-4">
                    <i class="bi bi-info-circle"></i>
                    <div class="inventory-alert-content">
                        <div class="inventory-alert-title">How it works</div>
                        <div class="inventory-alert-message">Record items you purchased from suppliers. Stock will be automatically added to your warehouse.</div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="inventory-form-label">Purchase Date <span class="required">*</span></label>
                        <input type="date" name="purchase_date" class="inventory-form-control" value="{{ old('purchase_date', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="inventory-form-label">Supplier Name</label>
                        <input type="text" name="supplier" class="inventory-form-control" value="{{ old('supplier') }}" placeholder="e.g., ABC Trading Co.">
                        <small class="inventory-form-help">Optional - who you bought from</small>
                    </div>
                    <div class="col-md-4">
                        <label class="inventory-form-label">Reference No</label>
                        <input type="text" class="inventory-form-control" value="{{ $referenceNo }}" readonly style="background: var(--bg-color);">
                        <small class="inventory-form-help">Auto-generated</small>
                    </div>
                </div>

                <div class="inventory-form-group">
                    <label class="inventory-form-label">Notes (optional)</label>
                    <textarea name="notes" class="inventory-form-control" rows="2" placeholder="Additional notes">{{ old('notes') }}</textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h6 mb-1 fw-semibold">Purchase Items</h2>
                        <small class="text-muted">Add all items you purchased in this transaction</small>
                    </div>
                    <button type="button" class="btn-inventory btn-inventory-success" onclick="addItem()">
                        <i class="bi bi-plus-circle"></i> Add Another Item
                    </button>
                </div>

                <div id="itemsContainer" class="row g-3">
                    <div class="col-12 item-row" data-index="0">
                        <div class="inventory-item-card">
                            <div class="p-3">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-5">
                                        <label class="inventory-form-label">Select Item <span class="required">*</span></label>
                                        <select name="items[0][inventory_item_id]" class="inventory-form-control item-select" data-index="0" required onchange="updateItemInfo(this)">
                                            <option value="">Choose an item...</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}" 
                                                    data-unit="{{ $item->purchase_unit }}" 
                                                    data-per-unit="{{ $item->units_per_purchase }}">
                                                    {{ $item->name }} @if($item->brand)({{ $item->brand }})@endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="inventory-form-help item-info-0"></small>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="inventory-form-label">Qty <span class="required">*</span></label>
                                        <input type="number" name="items[0][quantity]" class="inventory-form-control qty-input" data-index="0" min="1" value="1" required oninput="calculateRowTotal(0)">
                                        <small class="inventory-form-help">How many</small>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="inventory-form-label">Price/Unit <span class="required">*</span></label>
                                        <input type="number" name="items[0][cost_per_bulk]" class="inventory-form-control price-input" data-index="0" min="0" step="0.01" value="0" required oninput="calculateRowTotal(0)">
                                        <small class="inventory-form-help">₱ per unit</small>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="inventory-form-label">Line Total</label>
                                        <input type="text" class="inventory-form-control total-display" data-index="0" readonly value="₱0.00" style="background: var(--bg-color);">
                                        <small class="inventory-form-help">Auto-calc</small>
                                    </div>
                                    <div class="col-md-1 text-end">
                                        <button type="button" class="btn-inventory btn-inventory-danger" onclick="removeItem(this)" title="Remove item" style="padding: 0.5rem;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="inventory-card mt-3" style="background: var(--bg-color);">
                    <div class="p-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-0 fw-semibold">Grand Total</h5>
                                <small class="text-muted">Total amount for this purchase</small>
                            </div>
                            <div class="col-md-4 text-md-end mt-2 mt-md-0">
                                <h3 class="mb-0 inventory-text-success" id="grandTotal">₱0.00</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.inventory.purchases.index') }}" class="btn-inventory btn-inventory-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn-inventory btn-inventory-success">
                        <i class="bi bi-check-circle"></i> Record Purchase
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let itemIndex = 1;

function updateItemInfo(select) {
    const index = select.dataset.index;
    const option = select.options[select.selectedIndex];
    const unit = option.dataset.unit || '';
    const perUnit = option.dataset.perUnit || '';
    
    const infoEl = document.querySelector(`.item-info-${index}`);
    if (infoEl && unit && perUnit) {
        infoEl.textContent = `Purchased in ${unit} (${perUnit} pieces per ${unit})`;
    } else if (infoEl) {
        infoEl.textContent = '';
    }
}

function calculateRowTotal(index) {
    const qtyInput = document.querySelector(`.qty-input[data-index="${index}"]`);
    const priceInput = document.querySelector(`.price-input[data-index="${index}"]`);
    const totalDisplay = document.querySelector(`.total-display[data-index="${index}"]`);
    
    if (qtyInput && priceInput && totalDisplay) {
        const qty = parseFloat(qtyInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const total = qty * price;
        totalDisplay.value = '₱' + total.toFixed(2);
    }
    
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let grand = 0;
    document.querySelectorAll('.total-display').forEach(el => {
        const val = el.value.replace('₱', '').replace(',', '');
        grand += parseFloat(val) || 0;
    });
    document.getElementById('grandTotal').textContent = '₱' + grand.toFixed(2);
}

function addItem() {
    const container = document.getElementById('itemsContainer');
    const template = document.querySelector('.item-row');
    const newRow = template.cloneNode(true);
    newRow.dataset.index = itemIndex;

    newRow.querySelectorAll('[name], [data-index], .item-info-0').forEach(field => {
        if (field.hasAttribute('name')) {
            const name = field.getAttribute('name');
            const newName = name.replace(/items\[0\]/g, `items[${itemIndex}]`);
            field.setAttribute('name', newName);
        }
        
        if (field.hasAttribute('data-index')) {
            field.setAttribute('data-index', itemIndex);
        }
        
        if (field.classList.contains('item-info-0')) {
            field.classList.remove('item-info-0');
            field.classList.add(`item-info-${itemIndex}`);
            field.textContent = '';
        }

        if (field.tagName === 'SELECT') {
            field.selectedIndex = 0;
            field.setAttribute('onchange', 'updateItemInfo(this)');
        }
        
        if (field.classList.contains('qty-input')) {
            field.value = 1;
            field.setAttribute('oninput', `calculateRowTotal(${itemIndex})`);
        }
        
        if (field.classList.contains('price-input')) {
            field.value = 0;
            field.setAttribute('oninput', `calculateRowTotal(${itemIndex})`);
        }
        
        if (field.classList.contains('total-display')) {
            field.value = '₱0.00';
        }
    });

    container.appendChild(newRow);
    itemIndex++;
}

function removeItem(button) {
    const container = document.getElementById('itemsContainer');
    if (container.children.length > 1) {
        button.closest('.item-row').remove();
        calculateGrandTotal();
    } else {
        alert('At least one item is required.');
    }
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    calculateGrandTotal();
});
</script>
@endpush
