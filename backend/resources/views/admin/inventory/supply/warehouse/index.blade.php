@extends('admin.layouts.app')

@section('title', 'All Items - Inventory')
@section('page-title', 'Laundry Supply Manager')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    {{-- Navigation Tabs --}}
    <div class="supply-tabs">
        <a href="{{ route('admin.inventory.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.inventory.index') }}" class="active">All Items</a>
        <a href="{{ route('admin.inventory.manage') }}">Manage</a>
        <a href="{{ route('admin.inventory.purchases.index') }}">Purchases</a>
        <a href="{{ route('admin.inventory.distribute.index') }}">Distribute</a>
        <a href="{{ route('admin.inventory.branch-stock') }}">Branches</a>
        <a href="{{ route('admin.inventory.dist-log') }}">Dist-log</a>
    </div>

    {{-- Metrics --}}
    <div class="metrics-row">
        <div class="metric-box">
            <label>Categories</label>
            <div class="value">{{ $categories->count() }}</div>
        </div>
        <div class="metric-box">
            <label>Total SKUs</label>
            <div class="value">{{ $totalSkus }}</div>
        </div>
        <div class="metric-box">
            <label>Warehouse Stock</label>
            <div class="value">{{ number_format($totalUnits) }}</div>
        </div>
        <div class="metric-box">
            <label>Total Agent</label>
            <div class="value">₱{{ number_format($totalValue, 2) }}</div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="action-buttons">
        <a href="{{ route('admin.inventory.purchases.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i> Record purchase
        </a>
        <a href="{{ route('admin.inventory.distribute.index') }}" class="btn btn-primary">
            <i class="bi bi-arrow-left-right me-1"></i> Send to branches
        </a>
        <a href="{{ route('admin.inventory.movements.index') }}" class="btn btn-info">
            <i class="bi bi-arrow-left-right me-1"></i> Movement Report
        </a>
        <button type="button" class="btn btn-secondary" onclick="openCategoryModal()">
            <i class="bi bi-plus me-1"></i> New category
        </button>
    </div>

    {{-- Categories and Items --}}
    @forelse($categories as $category)
    <div class="category-section">
        <div class="category-header">
            <div class="d-flex align-items-center gap-3">
                <span class="category-badge" style="background: {{ $category->color ?? '#666' }}; color: #fff;">
                    {{ $category->name }}
                </span>
                <span class="text-muted small">{{ $category->items->count() }} items</span>
            </div>
            <div class="category-stats">
                <div>Value: <span>₱{{ number_format($category->items->sum(fn($i) => ($i->centralStock->current_stock ?? 0) * $i->unit_cost_price), 2) }}</span></div>
            </div>
        </div>

        @if($category->items->isNotEmpty())
        <table class="supply-table">
            <thead>
                <tr>
                    <th>ITEM</th>
                    <th>STOCK</th>
                    <th>COST/UNIT</th>
                    <th>VALUE</th>
                    <th>STATUS</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($category->items as $item)
                @php
                    $stock = $item->centralStock->current_stock ?? 0;
                    $costPerUnit = $item->unit_cost_price ?? 0;
                    $value = $stock * $costPerUnit;
                    $reorderPoint = $item->centralStock->reorder_point ?? 0;
                    $status = $stock <= 0 ? 'out' : ($stock <= $reorderPoint && $reorderPoint > 0 ? 'low' : 'ok');
                @endphp
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $item->name }}</div>
                        @if($item->brand)
                            <small class="text-muted">{{ $item->brand }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="fw-semibold">{{ number_format($stock) }}</span> {{ $item->distribution_unit }}s
                    </td>
                    <td class="text-muted">
                        {{ $costPerUnit > 0 ? '₱'.number_format($costPerUnit, 2).'/'.$item->distribution_unit : '—' }}
                    </td>
                    <td class="text-success fw-semibold">
                        {{ $value > 0 ? '₱'.number_format($value, 2) : '—' }}
                    </td>
                    <td>
                        <span class="status-badge {{ $status }}">{{ ucfirst($status) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.inventory.distribute.index', ['item' => $item->id]) }}" class="btn-send">
                            <i class="bi bi-send me-1"></i> Send
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @empty
    <div class="inventory-empty">
        <div class="inventory-empty-icon">
            <i class="bi bi-box-seam"></i>
        </div>
        <h3 class="inventory-empty-title">No Categories Yet</h3>
        <p class="inventory-empty-message">Start by creating your first supply category like Detergent, Fabric Conditioner, or Bleach.</p>
        <button type="button" class="btn btn-success" onclick="openCategoryModal()">
            <i class="bi bi-plus-circle me-2"></i> Create Your First Category
        </button>
    </div>
    @endforelse
</div>

{{-- New Category Modal --}}
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">Add new category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="categoryForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="categoryMethod" value="POST">
                <input type="hidden" name="category_id" id="categoryId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category name</label>
                        <input type="text" name="name" id="categoryName" class="form-control"
                               placeholder="e.g. Stain Remover" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Choose color theme</label>
                        <div id="colorSwatches" class="d-flex gap-2 mb-3"></div>
                        <input type="hidden" name="color" id="categoryColor" value="#2196F3">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Preview</label>
                        <div>
                            <span id="categoryPreview" class="badge" style="font-size: 0.9375rem; padding: 0.5rem 1.25rem;">Category name</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const COLOR_THEMES = [
    { name: 'Blue', color: '#2196F3' },
    { name: 'Pink', color: '#E91E63' },
    { name: 'Amber', color: '#FF9800' },
    { name: 'Teal', color: '#009688' },
    { name: 'Purple', color: '#9C27B0' },
    { name: 'Coral', color: '#FF5722' },
    { name: 'Green', color: '#4CAF50' },
    { name: 'Gray', color: '#607D8B' },
    { name: 'Red', color: '#F44336' }
];

let selectedColor = COLOR_THEMES[0].color;

function buildColorSwatches() {
    const container = document.getElementById('colorSwatches');
    container.innerHTML = COLOR_THEMES.map(theme => `
        <div class="color-swatch"
             style="width: 40px; height: 40px; border-radius: 0.5rem; background: ${theme.color}; cursor: pointer; border: 3px solid ${selectedColor === theme.color ? 'var(--primary-color)' : 'transparent'}; transition: all 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"
             onclick="selectColor('${theme.color}')"
             title="${theme.name}">
        </div>
    `).join('');
}

function selectColor(color) {
    selectedColor = color;
    document.getElementById('categoryColor').value = color;
    buildColorSwatches();
    updatePreview();
}

function updatePreview() {
    const name = document.getElementById('categoryName').value || 'Category name';
    const preview = document.getElementById('categoryPreview');
    preview.textContent = name;
    preview.style.background = selectedColor;
    preview.style.color = '#fff';
}

function openCategoryModal() {
    document.getElementById('categoryModalTitle').textContent = 'Add new category';
    document.getElementById('categoryForm').action = '{{ route('admin.inventory.categories.store') }}';
    document.getElementById('categoryMethod').value = 'POST';
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryName').value = '';
    selectedColor = COLOR_THEMES[0].color;
    buildColorSwatches();
    updatePreview();

    const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
    modal.show();
}

// Update preview on name change
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('categoryName');
    if (nameInput) {
        nameInput.addEventListener('input', updatePreview);
    }
});
</script>
@endpush
