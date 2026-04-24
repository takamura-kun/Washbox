@extends('admin.layouts.app')

@section('title', 'Distribute — Laundry Supply Manager')
@section('page-title', 'Distribute to Branches')

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
        <a href="{{ route('admin.inventory.purchases.index') }}">Purchases</a>
        <a href="{{ route('admin.inventory.distribute.index') }}" class="active">Distribute</a>
        <a href="{{ route('admin.inventory.branch-stock') }}">Branches</a>
        <a href="{{ route('admin.inventory.dist-log') }}">Dist-log</a>
    </div>

    <div class="d-flex gap-2 justify-content-end align-items-center flex-wrap mb-4">
        <a href="{{ route('admin.inventory.branch-stock') }}" class="btn btn-sm rounded-pill btn-outline-secondary d-flex align-items-center">
            <i class="bi bi-shop me-1"></i>Branch Inventory
        </a>
        <a href="{{ route('admin.inventory.dist-log') }}" class="btn btn-sm rounded-pill btn-danger d-flex align-items-center">
            <i class="bi bi-clock-history me-1"></i>Distribution Log
        </a>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <h2 class="h5 mb-1">Distribute Supplies to Branches</h2>
                    <p class="text-muted mb-4">Select an item from warehouse and specify how many units to send to each branch.</p>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.inventory.distribute.store') }}" id="distForm">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="inventory-form-label">Filter by Category</label>
                                <select id="dCatFilter" class="inventory-form-control" onchange="filterDistItems()">
                                    <option value="">All categories</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <small class="inventory-form-hint">Optional - narrow down items</small>
                            </div>
                            <div class="col-md-6">
                                <label class="inventory-form-label">Select Item to Distribute <span class="text-danger">*</span></label>
                                <select name="supply_item_id" id="dItemSelect" class="inventory-form-control" onchange="onDistItemChange()" required>
                                    <option value="">Choose an item from warehouse...</option>
                                    @foreach($allItems as $item)
                                        <option value="{{ $item->id }}"
                                            data-cat="{{ $item->category_id }}"
                                            data-unit="{{ $item->unit_label }}"
                                            data-stock="{{ $item->warehouse_stock }}"
                                            @if(request('item') == $item->id) selected @endif>
                                            {{ $item->name }} @if($item->brand)({{ $item->brand }})@endif — {{ number_format($item->warehouse_stock) }} {{ $item->unit_label }}s available
                                        </option>
                                    @endforeach
                                </select>
                                <small class="inventory-form-hint">Shows available warehouse stock</small>
                            </div>
                        </div>

                        <div id="availBar" class="alert alert-success border-0 mb-4" style="display:none">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>Warehouse Stock Available</strong>
                                    <p class="mb-0 small text-muted">Total units you can distribute</p>
                                </div>
                                <h4 class="mb-0 text-success" id="availCount"></h4>
                            </div>
                            <div class="progress" style="height:12px">
                                <div class="progress-bar bg-success" id="availBarFill" style="width:100%"></div>
                            </div>
                            <small class="text-muted mt-2 d-block">Progress bar shows remaining stock as you allocate</small>
                        </div>

                        <div class="mb-4">
                            <label class="inventory-form-label">Allocate Quantities to Branches</label>
                            <p class="text-muted small mb-3">Enter how many units to send to each branch (leave 0 to skip)</p>
                            <div class="row g-3" id="branchGrid">
                                @foreach($branches as $branch)
                                    <div class="col-sm-6 col-xl-4">
                                        <div class="inventory-card h-100" style="border-left: 4px solid {{ $branch['color'] }};">
                                            <div class="inventory-card-body">
                                                <div class="mb-3">
                                                    <h6 class="mb-1" style="color: {{ $branch['color'] }}">{{ $branch['name'] }}</h6>
                                                    <div id="branchCur_{{ $branch['id'] }}" class="text-muted small">Current stock: loading...</div>
                                                </div>
                                                <input type="number"
                                                       name="quantities[{{ $branch['id'] }}]"
                                                       id="branchInput_{{ $branch['id'] }}"
                                                       class="inventory-form-control"
                                                       min="0"
                                                       value="0"
                                                       placeholder="Units to send"
                                                       oninput="updateDistTotal()">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div id="distTotal" class="inventory-card mb-4" style="display:none">
                            <div class="inventory-card-body">
                                <h6 class="mb-3">Distribution Summary</h6>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="small text-muted mb-1">Total Sending</div>
                                        <div class="h5 mb-0 text-primary" id="totalSending">0 units</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="small text-muted mb-1">Remaining in Warehouse</div>
                                        <div class="h5 mb-0" id="totalRemaining">—</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 align-items-end mb-3">
                            <div class="col-md-6">
                                <label class="inventory-form-label">Distribution Date <span class="text-danger">*</span></label>
                                <input type="date" name="distributed_at" class="inventory-form-control" value="{{ now()->format('Y-m-d') }}" required>
                                <small class="inventory-form-hint">Date of this distribution</small>
                            </div>
                            <div class="col-md-6">
                                <div id="overStockWarning" class="alert alert-danger mb-0 py-2" style="display:none">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Cannot exceed available warehouse stock!</strong>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between flex-column flex-md-row gap-3 mt-4">
                            <a href="{{ route('admin.inventory.index') }}" class="btn-inventory btn-inventory-secondary">Cancel</a>
                            <button type="submit" class="btn-inventory btn-inventory-primary" id="distSubmit">
                                <i class="bi bi-send-fill me-2"></i> Confirm & Distribute
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <aside class="col-xl-4">
            <div class="inventory-card mb-4">
                <div class="inventory-card-body">
                    <h5 class="mb-3">Distribution summary</h5>
                    <p class="text-muted mb-4">Preview branch stock totals while preparing the transfer.</p>
                    <div class="list-group list-group-flush">
                        @foreach($branches as $branch)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-0">
                                <div>
                                    <div class="fw-semibold">{{ $branch['name'] }}</div>
                                    <small class="text-muted">Total branch units</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-semibold">{{ number_format($branch['total_units']) }}</div>
                                    <small class="text-success">₱{{ number_format($branch['total_value'], 2) }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="inventory-card">
                <div class="inventory-card-body">
                    <h6 class="mb-3 text-uppercase text-muted" style="font-size:0.75rem;letter-spacing:0.15em">How it works</h6>
                    <ul class="list-unstyled mb-0" style="line-height:1.8">
                        <li><i class="bi bi-check2-circle text-success me-2"></i>Select a warehouse item.</li>
                        <li><i class="bi bi-check2-circle text-success me-2"></i>Enter quantities for each branch.</li>
                        <li><i class="bi bi-check2-circle text-success me-2"></i>Confirm to deduct from central stock.</li>
                    </ul>
                </div>
            </div>
        </aside>
    </div>
</div>

@endsection

@push('scripts')
<script>
const branchItemStocks = @json($branchItemStocks);
const branchIds = @json(array_column($branches, 'id'));

function filterDistItems() {
    const catId = document.getElementById('dCatFilter').value;
    const sel = document.getElementById('dItemSelect');
    Array.from(sel.options).forEach(opt => {
        opt.hidden = catId && opt.dataset.cat !== catId;
    });
}

function onDistItemChange() {
    const sel = document.getElementById('dItemSelect');
    const opt = sel.options[sel.selectedIndex];

    if (!opt.value) {
        document.getElementById('availBar').style.display = 'none';
        document.getElementById('distTotal').style.display = 'none';
        return;
    }

    const avail = parseInt(opt.dataset.stock) || 0;
    const unit = opt.dataset.unit || 'unit';
    const itemId = opt.value;

    document.getElementById('availCount').textContent = `${avail.toLocaleString()} ${unit}${avail === 1 ? '' : 's'}`;
    document.getElementById('availBar').style.display = 'block';
    document.getElementById('distTotal').style.display = 'block';

    branchIds.forEach(bid => {
        const cur = (branchItemStocks[bid] && branchItemStocks[bid][itemId]) || 0;
        document.getElementById(`branchCur_${bid}`).textContent = `Current stock: ${cur.toLocaleString()} ${unit}${cur === 1 ? '' : 's'}`;
        document.getElementById(`branchInput_${bid}`).value = 0;
    });

    updateDistTotal();
}

function updateDistTotal() {
    const sel = document.getElementById('dItemSelect');
    const opt = sel.options[sel.selectedIndex];
    if (!opt?.value) return;

    const avail = parseInt(opt.dataset.stock) || 0;
    const unit = opt.dataset.unit || 'unit';

    let total = 0;
    branchIds.forEach(bid => {
        total += parseInt(document.getElementById(`branchInput_${bid}`)?.value) || 0;
    });

    const remaining = avail - total;
    const pct = avail > 0 ? Math.max(Math.min((remaining / avail) * 100, 100), 0) : 0;
    document.getElementById('availBarFill').style.width = `${pct}%`;
    document.getElementById('totalSending').textContent = `${total.toLocaleString()} ${unit}${total === 1 ? '' : 's'}`;
    document.getElementById('totalRemaining').textContent = `${remaining.toLocaleString()} ${unit}${remaining === 1 ? '' : 's'}`;

    const over = remaining < 0;
    const warning = document.getElementById('overStockWarning');
    warning.style.display = over ? 'block' : 'none';
    document.getElementById('distSubmit').disabled = over;
    document.getElementById('totalRemaining').style.color = over ? 'var(--bs-danger)' : '';
}

window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('dItemSelect');
    if (sel.value) {
        onDistItemChange();
    }
});
</script>
@endpush
