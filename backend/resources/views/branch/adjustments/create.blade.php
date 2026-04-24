@extends('branch.layouts.app')

@section('page-title', 'Report Stock Adjustment')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold" style="color: var(--text-primary);">Report Stock Adjustment</h4>
            <p class="text-muted small mb-0">Report damaged, expired, lost, or stolen inventory items</p>
        </div>
        <a href="{{ route('branch.adjustments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="row">
        {{-- Form --}}
        <div class="col-lg-8">
            <form action="{{ route('branch.adjustments.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: var(--card-bg);">
                    <div class="card-header border-bottom py-3" style="background-color: var(--card-bg);">
                        <h6 class="mb-0 fw-bold" style="color: var(--text-primary);">
                            <i class="bi bi-clipboard-data me-2" style="color: #3D3B6B;"></i>
                            Adjustment Details
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        {{-- Item Selection --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="color: var(--text-primary);">
                                Select Item <span class="text-danger">*</span>
                            </label>
                            <select name="inventory_item_id" id="itemSelect" class="form-select @error('inventory_item_id') is-invalid @enderror" required>
                                <option value="">-- Select Item --</option>
                                @foreach($items as $stock)
                                    <option value="{{ $stock->inventory_item_id }}" 
                                            data-stock="{{ $stock->current_stock }}"
                                            data-unit="{{ $stock->inventoryItem->distribution_unit }}"
                                            data-cost="{{ $stock->cost_price ?? $stock->inventoryItem->unit_cost_price }}"
                                            {{ old('inventory_item_id') == $stock->inventory_item_id ? 'selected' : '' }}>
                                        {{ $stock->inventoryItem->name }} 
                                        @if($stock->inventoryItem->brand)
                                            ({{ $stock->inventoryItem->brand }})
                                        @endif
                                        - Stock: {{ $stock->current_stock }} {{ $stock->inventoryItem->distribution_unit }}
                                    </option>
                                @endforeach
                            </select>
                            @error('inventory_item_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Select the item you want to adjust</small>
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
                                <option value="damaged" {{ old('type') === 'damaged' ? 'selected' : '' }}>Damaged - Physical damage (torn, broken, leaked)</option>
                                <option value="expired" {{ old('type') === 'expired' ? 'selected' : '' }}>Expired - Past expiration date</option>
                                <option value="lost" {{ old('type') === 'lost' ? 'selected' : '' }}>Lost - Missing/unaccounted for</option>
                                <option value="theft" {{ old('type') === 'theft' ? 'selected' : '' }}>Theft - Stolen items</option>
                                <option value="spoilage" {{ old('type') === 'spoilage' ? 'selected' : '' }}>Spoilage - Contaminated/spoiled</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Quantity --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="color: var(--text-primary);">
                                Quantity to Deduct <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="quantity" id="quantityInput" class="form-control @error('quantity') is-invalid @enderror" 
                                   value="{{ old('quantity') }}" min="1" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Enter the number of items to deduct from stock</small>
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
                                   placeholder="e.g., Sachets have holes, chemical leaked out">
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Brief description of the issue (max 255 characters)</small>
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
                            <small class="text-muted">Detailed explanation (optional, max 1000 characters)</small>
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
                            <small class="text-muted">Upload photo of damaged items (recommended, max 5MB)</small>
                            
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
                        <i class="bi bi-send me-2"></i>Submit for Approval
                    </button>
                    <a href="{{ route('branch.adjustments.index') }}" class="btn btn-outline-secondary px-4">
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
                        <i class="bi bi-info-circle text-info me-2"></i>Important Information
                    </h6>
                    <ul class="small mb-0" style="color: var(--text-secondary);">
                        <li class="mb-2">All adjustments require admin approval before stock is deducted</li>
                        <li class="mb-2">Upload clear photos of damaged items to speed up approval</li>
                        <li class="mb-2">Be accurate with quantities - incorrect reports may be rejected</li>
                        <li class="mb-2">Provide detailed reasons to help admin understand the issue</li>
                        <li class="mb-0">You'll be notified once your adjustment is approved or rejected</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color: var(--text-primary);">
                        <i class="bi bi-question-circle text-primary me-2"></i>Adjustment Types
                    </h6>
                    <div class="small" style="color: var(--text-secondary);">
                        <p class="mb-2"><strong>Damaged:</strong> Items with physical damage (torn packaging, broken bottles, leaked sachets)</p>
                        <p class="mb-2"><strong>Expired:</strong> Items past their expiration date</p>
                        <p class="mb-2"><strong>Lost:</strong> Items that are missing or unaccounted for</p>
                        <p class="mb-2"><strong>Theft:</strong> Items that were stolen</p>
                        <p class="mb-0"><strong>Spoilage:</strong> Items that are contaminated or spoiled</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
            
            // Set max quantity
            quantityInput.max = stock;
            
            // Calculate value loss if quantity is entered
            if (quantityInput.value) {
                calculateValueLoss();
            }
        } else {
            stockInfo.classList.add('d-none');
            valueLossDisplay.classList.add('d-none');
            quantityInput.max = '';
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

    function calculateValueLoss() {
        const qty = parseInt(quantityInput.value) || 0;
        const loss = qty * selectedItemCost;
        valueLoss.textContent = loss.toFixed(2);
        valueLossDisplay.classList.remove('d-none');
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
