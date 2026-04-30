@extends('admin.layouts.app')

@section('page-title', 'Create Stock Adjustment')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold" style="color: var(--text-primary);">Create Stock Adjustment</h4>
            <p class="text-muted small mb-0">Manually adjust stock for any branch (auto-approved)</p>
        </div>
        <a href="{{ route('admin.inventory.adjustments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="row">
        {{-- Form --}}
        <div class="col-lg-8">
            <form action="{{ route('admin.inventory.adjustments.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: var(--card-bg);">
                    <div class="card-header border-bottom py-3" style="background-color: var(--card-bg);">
                        <h6 class="mb-0 fw-bold" style="color: var(--text-primary);">
                            <i class="bi bi-clipboard-data me-2" style="color: #3D3B6B;"></i>
                            Adjustment Details
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        {{-- Branch Selection --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="color: var(--text-primary);">
                                Select Branch <span class="text-danger">*</span>
                            </label>
                            <select name="branch_id" id="branchSelect" class="form-select @error('branch_id') is-invalid @enderror" required>
                                <option value="">-- Select Branch --</option>
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

                        {{-- Item Selection --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="color: var(--text-primary);">
                                Select Item <span class="text-danger">*</span>
                            </label>
                            <select name="inventory_item_id" id="itemSelect" class="form-select @error('inventory_item_id') is-invalid @enderror" required disabled>
                                <option value="">-- Select branch first --</option>
                            </select>
                            @error('inventory_item_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Select branch first to load available items</small>
                        </div>

                        {{-- Stock Info Display --}}
                        <div id="stockInfo" class="alert alert-info d-none mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Current Stock:</strong> <span id="currentStock">0</span> <span id="stockUnit"></span>
                        </div>

                        {{-- Adjustment Type --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="color: var(--text-primary);">
                                Adjustment Type <span class="text-danger">*</span>
                            </label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">-- Select Type --</option>
                                <option value="damaged" {{ old('type') === 'damaged' ? 'selected' : '' }}>Damaged - Physical damage</option>
                                <option value="expired" {{ old('type') === 'expired' ? 'selected' : '' }}>Expired - Past expiration date</option>
                                <option value="lost" {{ old('type') === 'lost' ? 'selected' : '' }}>Lost - Missing/unaccounted for</option>
                                <option value="theft" {{ old('type') === 'theft' ? 'selected' : '' }}>Theft - Stolen items</option>
                                <option value="spoilage" {{ old('type') === 'spoilage' ? 'selected' : '' }}>Spoilage - Contaminated/spoiled</option>
                                <option value="found" {{ old('type') === 'found' ? 'selected' : '' }}>Found - Discovered items (positive)</option>
                                <option value="correction" {{ old('type') === 'correction' ? 'selected' : '' }}>Correction - Inventory count fix</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Quantity --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="color: var(--text-primary);">
                                Quantity <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="quantity" id="quantityInput" class="form-control @error('quantity') is-invalid @enderror" 
                                   value="{{ old('quantity') }}" min="1" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Enter a positive number. The adjustment type determines if stock is added or deducted.</small>
                            <div id="valueLossDisplay" class="mt-2 d-none">
                                <small class="text-danger">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Estimated Value Loss: <strong>₱<span id="valueLoss">0.00</span></strong>
                                </small>
                            </div>
                        </div>

                        {{-- Reason --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="color: var(--text-primary);">
                                Reason <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror" 
                                   value="{{ old('reason') }}" maxlength="255" required
                                   placeholder="e.g., Inventory count correction">
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Notes --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="color: var(--text-primary);">
                                Additional Notes
                            </label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                      rows="3" maxlength="1000">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Photo Proof --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="color: var(--text-primary);">
                                Photo Proof
                            </label>
                            <input type="file" name="photo_proof" id="photoInput" class="form-control @error('photo_proof') is-invalid @enderror" 
                                   accept="image/jpeg,image/jpg,image/png">
                            @error('photo_proof')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Optional (max 5MB)</small>
                            
                            {{-- Photo Preview --}}
                            <div id="photoPreview" class="mt-3 d-none">
                                <img id="previewImage" src="" class="img-thumbnail" style="max-width: 300px;">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-5 shadow-sm" style="background: #3D3B6B; border: none;">
                        <i class="bi bi-check-circle me-2"></i>Create Adjustment (Auto-Approved)
                    </button>
                    <a href="{{ route('admin.inventory.adjustments.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>

        {{-- Info Sidebar --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-3" style="background-color: var(--card-bg);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color: var(--text-primary);">
                        <i class="bi bi-info-circle text-info me-2"></i>Admin Adjustments
                    </h6>
                    <ul class="small mb-0" style="color: var(--text-secondary);">
                        <li class="mb-2">Admin adjustments are automatically approved</li>
                        <li class="mb-2">Stock changes are applied immediately</li>
                        <li class="mb-2"><strong>Damaged, Expired, Lost, Theft, Spoilage</strong> — deduct from stock</li>
                        <li class="mb-2"><strong>Found, Correction</strong> — add to stock</li>
                        <li class="mb-0">All adjustments are logged for audit purposes</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color: var(--text-primary);">
                        <i class="bi bi-lightbulb text-warning me-2"></i>When to Use
                    </h6>
                    <div class="small" style="color: var(--text-secondary);">
                        <p class="mb-2"><strong>Corrections:</strong> Fix inventory count errors</p>
                        <p class="mb-2"><strong>Found Items:</strong> Add discovered items</p>
                        <p class="mb-2"><strong>Direct Adjustments:</strong> Immediate stock changes</p>
                        <p class="mb-0"><strong>Emergency:</strong> Urgent adjustments needed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const branchSelect = document.getElementById('branchSelect');
    const itemSelect = document.getElementById('itemSelect');
    const stockInfo = document.getElementById('stockInfo');
    const currentStock = document.getElementById('currentStock');
    const stockUnit = document.getElementById('stockUnit');
    const quantityInput = document.getElementById('quantityInput');
    const valueLossDisplay = document.getElementById('valueLossDisplay');
    const valueLoss = document.getElementById('valueLoss');
    const photoInput = document.getElementById('photoInput');
    const photoPreview = document.getElementById('photoPreview');
    const previewImage = document.getElementById('previewImage');

    let selectedItemCost = 0;

    // Branch selection change - load items
    branchSelect.addEventListener('change', function() {
        const branchId = this.value;
        if (branchId) {
            itemSelect.disabled = true;
            itemSelect.innerHTML = '<option value="">Loading...</option>';
            
            fetch(`/admin/inventory/adjustments/branch/${branchId}/items`)
                .then(response => response.json())
                .then(data => {
                    itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = `${item.name} ${item.brand ? '(' + item.brand + ')' : ''} - Stock: ${item.current_stock} ${item.distribution_unit}`;
                        option.dataset.stock = item.current_stock;
                        option.dataset.unit = item.distribution_unit;
                        option.dataset.cost = item.unit_cost;
                        itemSelect.appendChild(option);
                    });
                    itemSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error loading items:', error);
                    itemSelect.innerHTML = '<option value="">Error loading items</option>';
                });
        } else {
            itemSelect.disabled = true;
            itemSelect.innerHTML = '<option value="">-- Select branch first --</option>';
            stockInfo.classList.add('d-none');
        }
    });

    // Item selection change
    itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (this.value) {
            const stock = selectedOption.dataset.stock;
            const unit = selectedOption.dataset.unit;
            selectedItemCost = parseFloat(selectedOption.dataset.cost);
            
            currentStock.textContent = stock;
            stockUnit.textContent = unit;
            stockInfo.classList.remove('d-none');
            
            if (quantityInput.value) {
                calculateValueLoss();
            }
        } else {
            stockInfo.classList.add('d-none');
            valueLossDisplay.classList.add('d-none');
        }
    });

    // Quantity input change
    quantityInput.addEventListener('input', function() {
        if (itemSelect.value && this.value) {
            calculateValueLoss();
        } else {
            valueLossDisplay.classList.add('d-none');
        }
    });

    const typeSelect = document.querySelector('select[name="type"]');
    const DEDUCTION_TYPES = ['damaged', 'expired', 'lost', 'theft', 'spoilage'];

    function calculateValueLoss() {
        const qty = parseInt(quantityInput.value) || 0;
        const type = typeSelect ? typeSelect.value : '';
        if (qty > 0 && DEDUCTION_TYPES.includes(type)) {
            const loss = qty * selectedItemCost;
            valueLoss.textContent = loss.toFixed(2);
            valueLossDisplay.classList.remove('d-none');
        } else {
            valueLossDisplay.classList.add('d-none');
        }
    }

    // Photo preview
    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                photoPreview.classList.remove('d-none');
            }
            reader.readAsDataURL(file);
        } else {
            photoPreview.classList.add('d-none');
        }
    });
});
</script>
@endpush
@endsection
