@extends('admin.layouts.app')

@section('title', 'Add Item — Inventory')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    <div class="inventory-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="inventory-badge-live">
                        <span class="inventory-pulse-dot"></span> LIVE
                    </span>
                    <span class="inventory-divider"></span>
                    <i class="bi bi-box-seam"></i>
                    <span>Add new item</span>
                </div>
                <h1 class="inventory-title">Create Inventory Item</h1>
                <p class="inventory-subtitle">Add a new supply item to your warehouse inventory</p>
            </div>
            <div>
                <a href="{{ route('admin.inventory.manage') }}" class="btn-inventory btn-inventory-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Inventory
                </a>
            </div>
        </div>
    </div>

    <div class="inventory-card">
        <form action="{{ route('admin.inventory.items.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="inventory-card-body">
                @if($errors->any())
                <div class="inventory-alert inventory-alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <div>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <!-- Basic Information -->
                <div class="inventory-section-header">
                    <h5><i class="bi bi-info-circle"></i>Basic Information</h5>
                    <small>Essential details about the item</small>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="inventory-form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="inventory-form-control" required>
                            <option value="">Select category...</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', request('category_id')) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                        <small class="inventory-form-hint">What type of item is this?</small>
                    </div>

                    <div class="col-md-6">
                        <label class="inventory-form-label">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="inventory-form-control" value="{{ old('name') }}" placeholder="e.g., Tide Detergent Powder" required>
                        <small class="inventory-form-hint">Full product name</small>
                    </div>

                    <div class="col-md-6">
                        <label class="inventory-form-label">Brand</label>
                        <input type="text" name="brand" class="inventory-form-control" value="{{ old('brand') }}" placeholder="e.g., Tide, Ariel, Downy">
                        <small class="inventory-form-hint">Optional brand name</small>
                    </div>

                    <div class="col-md-6">
                        <label class="inventory-form-label">SKU/Item Code</label>
                        <input type="text" name="sku" class="inventory-form-control" value="{{ old('sku') }}" placeholder="Leave empty to auto-generate">
                        <small class="inventory-form-hint">Auto-generated if left blank</small>
                    </div>

                    <div class="col-12">
                        <label class="inventory-form-label">Description</label>
                        <textarea name="description" class="inventory-form-control" rows="2" placeholder="Brief description of the item">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- Supplier Information -->
                <div class="inventory-section-header">
                    <h5><i class="bi bi-truck"></i>Supplier Details</h5>
                    <small>Optional supplier information</small>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="inventory-form-label">Supplier Name</label>
                        <input type="text" name="supplier_name" class="inventory-form-control" value="{{ old('supplier_name') }}" placeholder="e.g., ABC Trading Co.">
                        <small class="inventory-form-hint">Who supplies this item</small>
                    </div>

                    <div class="col-md-6">
                        <label class="inventory-form-label">Supplier Contact</label>
                        <input type="text" name="supplier_contact" class="inventory-form-control" value="{{ old('supplier_contact') }}" placeholder="Phone or email">
                        <small class="inventory-form-hint">Contact information</small>
                    </div>
                </div>

                <!-- Purchase & Unit Configuration -->
                <div class="inventory-section-header">
                    <h5><i class="bi bi-box-seam"></i>How You Buy & Distribute This Item</h5>
                    <small>Configure purchase units and distribution units</small>
                </div>
                
                <div class="inventory-alert inventory-alert-info">
                    <div class="d-flex">
                        <i class="bi bi-lightbulb me-3 fs-4"></i>
                        <div>
                            <strong>Simple Example:</strong>
                            <p class="mb-2">You buy <strong>"Detergent Powder"</strong> in <strong>boxes</strong> from supplier. Each box costs <strong>₱500</strong> and contains <strong>10 sachets</strong>.</p>
                            <ul class="mb-0">
                                <li><strong>Purchase Unit:</strong> box</li>
                                <li><strong>Units per Purchase:</strong> 10</li>
                                <li><strong>Distribution Unit:</strong> sachet</li>
                                <li><strong>Cost per Purchase Unit:</strong> ₱500.00</li>
                            </ul>
                            <p class="mt-2 mb-0"><em>System will calculate: Cost per sachet = ₱50.00</em></p>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="inventory-form-label">Supply Type <span class="text-danger">*</span></label>
                        <input type="text" name="supply_type" class="inventory-form-control" id="supplyType" value="{{ old('supply_type', 'bulk') }}" placeholder="e.g., bulk, direct, wholesale" required>
                        <small class="inventory-form-hint">Type: bulk (for packages), direct (for single items), or custom type</small>
                    </div>

                    <div class="col-md-6">
                        <label class="inventory-form-label">Cost per Purchase Unit (₱) <span class="text-danger">*</span></label>
                        <input type="number" name="bulk_cost_price" class="inventory-form-control" id="bulkCost" value="{{ old('bulk_cost_price') }}" step="0.01" min="0" placeholder="e.g., 500.00" required>
                        <small class="inventory-form-hint">How much you pay per purchase unit</small>
                    </div>

                    <div class="col-md-4" id="purchaseUnitField">
                        <label class="inventory-form-label">Purchase Unit <span class="text-danger">*</span></label>
                        <input type="text" name="purchase_unit" class="inventory-form-control" value="{{ old('purchase_unit') }}" placeholder="e.g., box, dozen, case" required>
                        <small class="inventory-form-hint">How you buy it (box, dozen, case, etc.)</small>
                    </div>

                    <div class="col-md-4" id="unitsPerPurchaseField">
                        <label class="inventory-form-label">Units per Purchase <span class="text-danger">*</span></label>
                        <input type="number" name="units_per_purchase" class="inventory-form-control" id="unitsPerPurchase" value="{{ old('units_per_purchase', 1) }}" min="1" required>
                        <small class="inventory-form-hint">How many pieces in one purchase unit</small>
                    </div>

                    <div class="col-md-4">
                        <label class="inventory-form-label">Distribution Unit <span class="text-danger">*</span></label>
                        <input type="text" name="distribution_unit" class="inventory-form-control" value="{{ old('distribution_unit') }}" placeholder="e.g., sachet, piece, bottle" required>
                        <small class="inventory-form-hint">Individual unit name (sachet, piece, bottle)</small>
                    </div>

                    <div class="col-md-12">
                        <div class="inventory-alert inventory-alert-success" id="costCalculation" style="display: none;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-calculator me-3 fs-4"></i>
                                <div>
                                    <strong>Calculated Cost per Distribution Unit:</strong> <span id="unitCostDisplay" class="fs-5 text-success">₱0.00</span>
                                    <p class="mb-0 mt-1 small">This is the cost per individual unit that will be used for inventory valuation</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Management -->
                <div class="inventory-section-header">
                    <h5><i class="bi bi-graph-up"></i>Stock Alerts & Limits</h5>
                    <small>Set reorder points and maximum stock levels</small>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="inventory-form-label">Reorder Point (units)</label>
                        <input type="number" name="reorder_point" class="inventory-form-control" value="{{ old('reorder_point', 0) }}" min="0" placeholder="e.g., 50">
                        <small class="inventory-form-hint">Alert when stock reaches this level</small>
                    </div>

                    <div class="col-md-4">
                        <label class="inventory-form-label">Maximum Stock Level (units)</label>
                        <input type="number" name="max_level" class="inventory-form-control" value="{{ old('max_level', 0) }}" min="0" placeholder="e.g., 500">
                        <small class="inventory-form-hint">Maximum stock to maintain</small>
                    </div>

                    <div class="col-md-4">
                        <label class="inventory-form-label">Lead Time (days)</label>
                        <input type="number" name="lead_time_days" class="inventory-form-control" value="{{ old('lead_time_days', 0) }}" min="0" placeholder="e.g., 7">
                        <small class="inventory-form-hint">Days to restock from supplier</small>
                    </div>
                </div>

                <!-- Storage & Additional Info -->
                <div class="inventory-section-header">
                    <h5><i class="bi bi-archive"></i>Additional Information</h5>
                    <small>Storage location, notes, and settings</small>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="inventory-form-label">Storage Location</label>
                        <input type="text" name="storage_location" class="inventory-form-control" value="{{ old('storage_location') }}" placeholder="e.g., Shelf A-12, Warehouse Section B">
                        <small class="inventory-form-hint">Where this item is stored</small>
                    </div>

                    <div class="col-md-6">
                        <label class="inventory-form-label">Barcode</label>
                        <input type="text" name="barcode" class="inventory-form-control" value="{{ old('barcode') }}" placeholder="Optional barcode">
                        <small class="inventory-form-hint">Product barcode if available</small>
                    </div>

                    <div class="col-md-6">
                        <label class="inventory-form-label">Item Image</label>
                        <input type="file" name="image" class="inventory-form-control" accept="image/*">
                        <small class="inventory-form-hint">Upload product image (optional)</small>
                    </div>

                    <div class="col-12">
                        <label class="inventory-form-label">Notes</label>
                        <textarea name="notes" class="inventory-form-control" rows="2" placeholder="Any additional notes or instructions">{{ old('notes') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="has_expiration" class="form-check-input" id="has_expiration" value="1" {{ old('has_expiration') ? 'checked' : '' }}>
                            <label class="form-check-label" for="has_expiration">This item has expiration date</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active (available for use)</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="inventory-card-footer">
                <a href="{{ route('admin.inventory.manage') }}" class="btn-inventory btn-inventory-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
                <button type="submit" class="btn-inventory btn-inventory-primary">
                    <i class="bi bi-check-circle"></i> Create Item
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Calculate unit cost automatically
    function calculateUnitCost() {
        const bulkCost = parseFloat(document.getElementById('bulkCost').value) || 0;
        const unitsPerPurchase = parseInt(document.getElementById('unitsPerPurchase').value) || 1;
        
        if (bulkCost > 0 && unitsPerPurchase > 0) {
            const unitCost = (bulkCost / unitsPerPurchase).toFixed(2);
            document.getElementById('unitCostDisplay').textContent = '₱' + unitCost;
            document.getElementById('costCalculation').style.display = 'block';
        } else {
            document.getElementById('costCalculation').style.display = 'none';
        }
    }

    // Event listeners
    document.getElementById('bulkCost').addEventListener('input', calculateUnitCost);
    document.getElementById('unitsPerPurchase').addEventListener('input', calculateUnitCost);

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        calculateUnitCost();
    });
</script>
@endpush
@endsection
