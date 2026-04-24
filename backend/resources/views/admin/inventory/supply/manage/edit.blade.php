@extends('admin.layouts.app')

@section('title', 'Edit Item — Inventory')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    <div class="mb-4">
        <h1 class="h4 mb-1">Edit Inventory Item</h1>
        <p class="text-muted">Update supply item information</p>
    </div>

    <div class="card shadow-sm">
        <form action="{{ route('admin.inventory.items.update', $item) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Basic Information -->
                <h5 class="mb-3 text-primary"><i class="bi bi-info-circle me-2"></i>Basic Information</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select category...</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Item Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $item->name) }}" placeholder="e.g., Tide Detergent Sachet" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" class="form-control" value="{{ old('brand', $item->brand) }}" placeholder="e.g., Tide, Ariel">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">SKU/Item Code</label>
                        <input type="text" name="sku" class="form-control" value="{{ old('sku', $item->sku) }}" readonly>
                        <small class="text-muted">Cannot be changed</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Barcode</label>
                        <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $item->barcode) }}" placeholder="Optional">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional description">{{ old('description', $item->description) }}</textarea>
                    </div>
                </div>

                <!-- Supplier Information -->
                <h5 class="mb-3 text-primary"><i class="bi bi-truck me-2"></i>Supplier Information</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" name="supplier_name" class="form-control" value="{{ old('supplier_name', $item->supplier_name) }}" placeholder="e.g., ABC Trading">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Supplier Contact</label>
                        <input type="text" name="supplier_contact" class="form-control" value="{{ old('supplier_contact', $item->supplier_contact) }}" placeholder="Phone or email">
                    </div>
                </div>

                <!-- Purchase & Unit Configuration -->
                <h5 class="mb-3 text-primary"><i class="bi bi-box-seam me-2"></i>Purchase & Unit Configuration</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Supply Type *</label>
                        <select name="supply_type" class="form-control" id="supplyType" required>
                            <option value="bulk" {{ old('supply_type', $item->supply_type) == 'bulk' ? 'selected' : '' }}>Bulk (buy in packages, distribute by pieces)</option>
                            <option value="direct" {{ old('supply_type', $item->supply_type) == 'direct' ? 'selected' : '' }}>Direct (buy and use as-is)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Cost per Purchase Unit (₱) *</label>
                        <input type="number" name="bulk_cost_price" class="form-control" id="bulkCost" value="{{ old('bulk_cost_price', $item->bulk_cost_price) }}" step="0.01" min="0" required>
                    </div>

                    <div class="col-md-4" id="purchaseUnitField">
                        <label class="form-label">Purchase Unit *</label>
                        <input type="text" name="purchase_unit" class="form-control" value="{{ old('purchase_unit', $item->purchase_unit) }}" placeholder="e.g., dozen, box, case" required>
                    </div>

                    <div class="col-md-4" id="unitsPerPurchaseField">
                        <label class="form-label">Units per Purchase *</label>
                        <input type="number" name="units_per_purchase" class="form-control" id="unitsPerPurchase" value="{{ old('units_per_purchase', $item->units_per_purchase) }}" min="1" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Distribution Unit *</label>
                        <input type="text" name="distribution_unit" class="form-control" value="{{ old('distribution_unit', $item->distribution_unit) }}" placeholder="e.g., sachet, piece, bottle" required>
                    </div>

                    <div class="col-md-12">
                        <div class="alert alert-success" id="costCalculation">
                            <strong>Calculated Cost per Unit:</strong> <span id="unitCostDisplay">₱{{ number_format($item->unit_cost_price, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Stock Management -->
                <h5 class="mb-3 text-primary"><i class="bi bi-graph-up me-2"></i>Stock Management</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Reorder Point (units)</label>
                        <input type="number" name="reorder_point" class="form-control" value="{{ old('reorder_point', $item->reorder_point) }}" min="0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Maximum Stock Level (units)</label>
                        <input type="number" name="max_level" class="form-control" value="{{ old('max_level', $item->max_level) }}" min="0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Lead Time (days)</label>
                        <input type="number" name="lead_time_days" class="form-control" value="{{ old('lead_time_days', $item->lead_time_days) }}" min="0">
                    </div>
                </div>

                <!-- Storage & Additional Info -->
                <h5 class="mb-3 text-primary"><i class="bi bi-archive me-2"></i>Storage & Additional Info</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Storage Location</label>
                        <input type="text" name="storage_location" class="form-control" value="{{ old('storage_location', $item->storage_location) }}" placeholder="e.g., Shelf A-12">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Item Image</label>
                        @if($item->image_path)
                        <div class="mb-2">
                            <img src="{{ asset($item->image_path) }}" alt="{{ $item->name }}" class="img-thumbnail" style="max-height: 100px;">
                        </div>
                        @endif
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Upload new image to replace current</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any additional notes">{{ old('notes', $item->notes) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="checkbox" name="has_expiration" class="form-check-input" id="has_expiration" value="1" {{ old('has_expiration', $item->has_expiration) ? 'checked' : '' }}>
                            <label class="form-check-label" for="has_expiration">This item has expiration date</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('admin.inventory.manage') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update Item</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function calculateUnitCost() {
        const bulkCost = parseFloat(document.getElementById('bulkCost').value) || 0;
        const unitsPerPurchase = parseInt(document.getElementById('unitsPerPurchase').value) || 1;
        
        if (bulkCost > 0 && unitsPerPurchase > 0) {
            const unitCost = (bulkCost / unitsPerPurchase).toFixed(2);
            document.getElementById('unitCostDisplay').textContent = '₱' + unitCost;
        }
    }

    function toggleSupplyFields() {
        const supplyType = document.getElementById('supplyType').value;
        const purchaseField = document.getElementById('purchaseUnitField');
        const unitsField = document.getElementById('unitsPerPurchaseField');
        
        if (supplyType === 'direct') {
            purchaseField.style.display = 'none';
            unitsField.style.display = 'none';
        } else {
            purchaseField.style.display = 'block';
            unitsField.style.display = 'block';
        }
    }

    document.getElementById('bulkCost').addEventListener('input', calculateUnitCost);
    document.getElementById('unitsPerPurchase').addEventListener('input', calculateUnitCost);
    document.getElementById('supplyType').addEventListener('change', toggleSupplyFields);

    document.addEventListener('DOMContentLoaded', function() {
        toggleSupplyFields();
    });
</script>
@endpush
@endsection
