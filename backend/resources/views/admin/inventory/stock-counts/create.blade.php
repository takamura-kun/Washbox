@extends('admin.layouts.app')

@section('title', 'Physical Stock Count - Inventory')
@section('page-title', 'Inventory Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<style>
    .count-item-row {
        background: #f9fafb;
        border-left: 4px solid #e5e7eb;
        transition: all 0.2s ease;
    }
    .count-item-row:hover {
        background: #f3f4f6;
        border-left-color: #3b82f6;
    }
    .count-item-row.variance {
        background: #fef3c7;
        border-left-color: #f59e0b;
    }
    .variance-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .variance-positive { background: #dcfce7; color: #166534; }
    .variance-negative { background: #fee2e2; color: #7f1d1d; }
</style>
@endpush

@section('content')
<div class="container-xl px-4 py-4 dashboard-modern-wrapper">
    {{-- Navigation Tabs --}}
    <div class="supply-tabs mb-4">
        <a href="{{ route('admin.inventory.index') }}">All Items</a>
        <a href="{{ route('admin.inventory.manage') }}">Manage</a>
        <a href="{{ route('admin.inventory.purchases.index') }}">Purchases</a>
        <a href="{{ route('admin.inventory.distribute.index') }}">Distribute</a>
        <a href="{{ route('admin.inventory.branch-stock') }}">Branches</a>
        <a href="{{ route('admin.inventory.dist-log') }}">Dist-log</a>
        <a href="{{ route('admin.inventory.stock-counts.create') }}" class="active">Stock Count</a>
    </div>

    <div class="glass-header mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-status-live">
                            <span class="pulse-dot"></span> COUNT
                        </span>
                        <span class="v-divider"></span>
                        <i class="bi bi-clipboard-check text-primary-blue"></i>
                        <span class="fw-semibold">Physical inventory count</span>
                    </div>
                    <h1 class="h4 mb-0">Record Physical Stock Count</h1>
                    <p class="text-muted mb-0">Verify actual inventory against system records and identify discrepancies.</p>
                </div>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0 text-lg-end">
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="bi bi-arrow-left me-2"></i> Back to Inventory
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

            @if(session('success'))
                <div class="inventory-alert success mb-4">
                    <i class="bi bi-check-circle"></i>
                    <div class="inventory-alert-content">
                        <div class="inventory-alert-title">Success</div>
                        <div class="inventory-alert-message">{{ session('success') }}</div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.inventory.stock-counts.store') }}" id="countForm">
                @csrf

                <div class="inventory-alert info mb-4">
                    <i class="bi bi-info-circle"></i>
                    <div class="inventory-alert-content">
                        <div class="inventory-alert-title">Physical Count Process</div>
                        <div class="inventory-alert-message">1. Verify count location 2. Enter physical quantities 3. Review variances 4. Reconcile discrepancies</div>
                    </div>
                </div>

                {{-- Count Header --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="inventory-form-label">Count Date <span class="required">*</span></label>
                        <input type="date" name="count_date" class="inventory-form-control" value="{{ old('count_date', now()->format('Y-m-d')) }}" required>
                        @error('count_date')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="inventory-form-label">Counted By <span class="required">*</span></label>
                        <input type="text" class="inventory-form-control" value="{{ auth()->user()->name }}" disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="inventory-form-label">Count Location <span class="required">*</span></label>
                        <select name="location" class="inventory-form-control" id="locationSelect" required>
                            <option value="">-- Select Location --</option>
                            <option value="central" selected>Central Warehouse</option>
                            <option value="branch">Branch Location</option>
                        </select>
                        @error('location')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                {{-- Branch Selection (hidden by default) --}}
                <div class="row g-3 mb-4" id="branchSection" style="display: none;">
                    <div class="col-md-4">
                        <label class="inventory-form-label">Select Branch <span class="required">*</span></label>
                        <select name="branch_id" class="inventory-form-control">
                            <option value="">-- Select Branch --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                {{-- Stock Count Items --}}
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="inventory-form-label">Item Quantities <span class="required">*</span></label>
                        <div class="inventory-alert info mb-3">
                            <i class="bi bi-info-circle"></i>
                            <div class="inventory-alert-content">
                                <small>Enter the actual quantity you physically counted for each item. System will calculate variances.</small>
                            </div>
                        </div>

                        <div id="itemsContainer">
                            @foreach($items as $index => $item)
                                @php
                                    $systemStock = ($location === 'branch' && isset($branch))
                                        ? ($item->branchStocks()->where('branch_id', $branch->id)->first()?->current_stock ?? 0)
                                        : ($item->centralStock?->quantity ?? 0);
                                @endphp
                                <div class="card border-0 count-item-row mb-3">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                <div>
                                                    <h6 class="mb-0">{{ $item->name }}</h6>
                                                    <small class="text-muted">
                                                        Category: {{ $item->category?->name ?? 'Uncategorized' }}
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="col-md-2 text-center">
                                                <div class="text-muted small">System Stock</div>
                                                <div class="fw-bold">{{ $systemStock }}</div>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="inventory-form-label mb-2">Physical Count</label>
                                                <input type="number"
                                                    name="items[{{ $item->id }}]"
                                                    class="inventory-form-control physical-count"
                                                    data-item="{{ $item->id }}"
                                                    data-system="{{ $systemStock }}"
                                                    min="0"
                                                    placeholder="0"
                                                    value="{{ old('items.'. $item->id) }}">
                                            </div>

                                            <div class="col-md-2 text-center">
                                                <div class="text-muted small">Variance</div>
                                                <div class="variance-badge" id="variance-{{ $item->id }}">
                                                    <span class="variance-value">0</span>
                                                </div>
                                            </div>

                                            <div class="col-md-2 text-end">
                                                <code class="text-muted" id="status-{{ $item->id }}">
                                                    <i class="bi bi-dash-circle"></i>
                                                </code>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Summary --}}
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Count Summary</h6>
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="text-muted small">Items Counted</div>
                                        <div class="h5" id="itemsCountedTotal">0</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-muted small">Variances Detected</div>
                                        <div class="h5 text-warning" id="variancesTotal">0</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-muted small">Overstock</div>
                                        <div class="h5 text-success" id="overstockTotal">0</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-muted small">Understock</div>
                                        <div class="h5 text-danger" id="understockTotal">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="inventory-form-label">Count Notes</label>
                        <textarea name="notes" class="inventory-form-control" rows="3" placeholder="Any observations during the count..." style="height: 100px; resize: none;">{{ old('notes') }}</textarea>
                        @error('notes')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill">
                            <i class="bi bi-check-circle me-2"></i> Complete Count
                        </button>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary w-100 rounded-pill">
                            <i class="bi bi-x-circle me-2"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const locationSelect = document.getElementById('locationSelect');
    const branchSection = document.getElementById('branchSection');
    const countInputs = document.querySelectorAll('.physical-count');

    // Show/hide branch section
    locationSelect.addEventListener('change', function() {
        branchSection.style.display = this.value === 'branch' ? 'block' : 'none';
    });

    // Update variance calculations
    countInputs.forEach(input => {
        input.addEventListener('input', function() {
            updateVariance(this);
            updateSummary();
        });
    });

    function updateVariance(input) {
        const itemId = input.dataset.item;
        const systemStock = parseInt(input.dataset.system);
        const physicalCount = parseInt(input.value) || 0;
        const variance = physicalCount - systemStock;

        const varianceBadge = document.getElementById(`variance-${itemId}`);
        const statusIcon = document.getElementById(`status-${itemId}`);
        const itemRow = input.closest('.count-item-row');

        varianceBadge.querySelector('.variance-value').textContent = (variance >= 0 ? '+' : '') + variance;

        if (variance === 0) {
            varianceBadge.className = 'variance-badge';
            statusIcon.innerHTML = '<i class="bi bi-check-circle text-success"></i>';
            itemRow.classList.remove('variance');
        } else if (variance > 0) {
            varianceBadge.className = 'variance-badge variance-positive';
            statusIcon.innerHTML = '<i class="bi bi-arrow-up-circle text-success"></i>Overstock';
            itemRow.classList.add('variance');
        } else {
            varianceBadge.className = 'variance-badge variance-negative';
            statusIcon.innerHTML = '<i class="bi bi-arrow-down-circle text-danger"></i>Understock';
            itemRow.classList.add('variance');
        }
    }

    function updateSummary() {
        let counted = 0;
        let variances = 0;
        let overstock = 0;
        let understock = 0;

        countInputs.forEach(input => {
            const physicalCount = parseInt(input.value) || 0;
            if (physicalCount > 0) counted++;

            const systemStock = parseInt(input.dataset.system);
            const variance = physicalCount - systemStock;

            if (variance !== 0) variances++;
            if (variance > 0) overstock++;
            if (variance < 0) understock++;
        });

        document.getElementById('itemsCountedTotal').textContent = counted;
        document.getElementById('variancesTotal').textContent = variances;
        document.getElementById('overstockTotal').textContent = overstock;
        document.getElementById('understockTotal').textContent = understock;
    }
});
</script>
@endpush

@endsection
