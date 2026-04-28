@extends('admin.layouts.app')

@section('title', 'Manage — Laundry Supply Manager')
@section('paget-title', 'Inventory Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.inventory-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    gap: 1rem;
}

.inventory-tabs {
    display: flex;
    gap: 0.5rem;
    border-bottom: 2px solid var(--table-border);
    margin-bottom: 2rem;
}

.inventory-tab {
    padding: 0.75rem 1.5rem;
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    font-weight: 500;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.inventory-tab:hover {
    color: var(--text-primary);
}

.inventory-tab.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
}

.inventory-content {
    display: none;
}

.inventory-content.active {
    display: block;
}

.kpi-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.kpi-card {
    background: var(--card-bg);
    border: 1px solid var(--table-border);
    border-radius: 8px;
    padding: 1.5rem;
}

.kpi-label {
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.kpi-value {
    font-size: 1.75rem;
    font-weight: bold;
    color: var(--text-primary);
}

.kpi-subtext {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-top: 0.5rem;
}

.two-column {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

@media (max-width: 1024px) {
    .two-column {
        grid-template-columns: 1fr;
    }
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.list-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--table-border);
}

.list-item:last-child {
    border-bottom: none;
}

.list-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.list-value {
    font-weight: 600;
    color: var(--text-primary);
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.category-item {
    background: var(--card-bg);
    border: 1px solid var(--table-border);
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.category-item:hover {
    border-color: #3b82f6;
    transform: translateY(-2px);
}

.category-badge {
    display: inline-block;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-bottom: 0.5rem;
}

.category-name {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.category-count {
    font-size: 0.8rem;
    color: var(--text-secondary);
}
</style>
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    {{-- Navigation Tabs --}}
    <div class="supply-tabs mb-4">
        <a href="{{ route('admin.inventory.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.inventory.index') }}">All Items</a>
        <a href="{{ route('admin.inventory.manage') }}" class="active">Manage</a>
        <a href="{{ route('admin.inventory.purchases.index') }}">Purchases</a>
        <a href="{{ route('admin.inventory.distribute.index') }}">Distribute</a>
        <a href="{{ route('admin.inventory.branch-stock') }}">Branches</a>
        <a href="{{ route('admin.inventory.dist-log') }}">Dist-log</a>
    </div>

    {{-- Header --}}
    <div class="inventory-header">
        <h2 style="margin: 0; font-size: 1.5rem;">Inventory</h2>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('admin.inventory.items.create') }}" class="btn-inventory btn-inventory-success">
                <i class="bi bi-box-seam me-2"></i>Add Items
            </a>
            <button type="button" class="btn-inventory btn-inventory-primary" onclick="promptCreateCategory()">
                <i class="bi bi-plus-lg me-2"></i>Add Category
            </button>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-label">Categories</div>
            <div class="kpi-value">{{ $categories->count() }}</div>
            <div class="kpi-subtext">Total categories</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Total SKUs</div>
            <div class="kpi-value">{{ $categories->sum(fn($c) => $c->items->count()) }}</div>
            <div class="kpi-subtext">Items in stock</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Warehouse Stock</div>
            <div class="kpi-value">{{ number_format($categories->sum(fn($c) => $c->items->sum(fn($i) => $i->centralStock->current_stock ?? 0))) }}</div>
            <div class="kpi-subtext">Total units</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Total Value</div>
            <div class="kpi-value">₱{{ number_format($categories->sum(fn($c) => $c->items->sum(fn($i) => ($i->centralStock->current_stock ?? 0) * ($i->default_cost ?? 0))), 0) }}</div>
            <div class="kpi-subtext">Inventory value</div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="inventory-tabs">
        <button class="inventory-tab active" onclick="switchTab('categories')">Categories</button>
        <button class="inventory-tab" onclick="switchTab('items')">Items</button>
        <button class="inventory-tab" onclick="switchTab('stock')">Stock</button>
    </div>

    {{-- Categories Tab --}}
    <div id="categories" class="inventory-content active">
        <div class="inventory-card">
            <div class="inventory-card-body">
                <div class="section-title">All categories</div>
                <div class="category-grid">
                    @foreach($categories as $category)
                        @php
                            $categoryStock = $category->items->sum(fn($item) => $item->centralStock->current_stock ?? 0);
                            $badgeColor = $category->color ?: '#6c757d';
                        @endphp
                        <div class="category-item">
                            <div class="category-badge" style="background: {{ $badgeColor }};"></div>
                            <div class="category-name">{{ $category->name }}</div>
                            <div class="category-count">{{ $category->items->count() }} items</div>
                            <div class="category-count">{{ $categoryStock }} units</div>
                            <div style="display: flex; gap: 0.5rem; margin-top: 0.75rem; justify-content: center;">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" style="font-size: 0.75rem;" onclick="openCategoryEditor({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ $category->color }}')">Edit</button>
                                <form method="POST" action="{{ route('admin.inventory.categories.destroy', $category) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" style="font-size: 0.75rem;" onclick="return confirm('Delete this category?')">Del</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Items Tab --}}
    <div id="items" class="inventory-content">
        <div class="inventory-card">
            <div class="inventory-card-body">
                <div class="section-title">All Items</div>
                <div class="table-responsive">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Unit</th>
                                <th>Cost</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories->flatMap(fn($c) => $c->items) as $item)
                                @php
                                    $itemStock = $item->centralStock->current_stock ?? 0;
                                    $status = $itemStock <= 0 ? 'out' : ($itemStock < 5 ? 'low' : 'ok');
                                @endphp
                                <tr>
                                    <td><span class="badge bg-secondary">{{ $item->category->name }}</span></td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->brand ?? '—' }}</td>
                                    <td>{{ $item->unit_label ?? 'pcs' }}</td>
                                    <td>₱{{ number_format($item->default_cost ?? 0, 2) }}</td>
                                    <td>{{ $itemStock }}</td>
                                    <td>
                                        <span class="badge {{ $status === 'ok' ? 'bg-success' : ($status === 'low' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.inventory.items.edit', $item) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No items found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Stock Tab --}}
    <div id="stock" class="inventory-content">
        <div class="two-column">
            {{-- Stock by Category --}}
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <div class="section-title">Stock by category</div>
                    @forelse($categories as $category)
                        @php
                            $categoryStock = $category->items->sum(fn($item) => $item->centralStock->current_stock ?? 0);
                        @endphp
                        <div class="list-item">
                            <span class="list-label">{{ $category->name }}</span>
                            <span class="list-value">{{ $categoryStock }} units</span>
                        </div>
                    @empty
                        <p class="text-muted">No categories</p>
                    @endforelse
                </div>
            </div>

            {{-- Low Stock Items --}}
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <div class="section-title">Low Stock Items</div>
                    @php
                        $lowStockItems = $categories->flatMap(fn($c) => $c->items)->filter(fn($i) => ($i->centralStock->current_stock ?? 0) < 5);
                    @endphp
                    @forelse($lowStockItems as $item)
                        <div class="list-item">
                            <span class="list-label">{{ $item->name }}</span>
                            <span class="list-value" style="color: #ef4444;">{{ $item->centralStock->current_stock ?? 0 }} units</span>
                        </div>
                    @empty
                        <p class="text-muted">All items well stocked</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<form id="categoryForm" method="POST" style="display:none">
    @csrf
    <input type="hidden" name="name" id="categoryNameInput">
    <input type="hidden" name="color" id="categoryColorInput">
</form>

@push('scripts')
<script>
function switchTab(tabName) {
    document.querySelectorAll('.inventory-content').forEach(el => {
        el.classList.remove('active');
    });
    
    document.querySelectorAll('.inventory-tab').forEach(el => {
        el.classList.remove('active');
    });
    
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}

function promptCreateCategory() {
    const name = prompt('Enter category name:');
    if (!name) return;

    const color = prompt('Enter category color hex code (e.g. #7c3aed):', '#6c757d');
    if (!color) return;

    const form = document.getElementById('categoryForm');
    form.action = '{{ route('admin.inventory.categories.store') }}';
    form.method = 'POST';
    document.getElementById('categoryNameInput').value = name;
    document.getElementById('categoryColorInput').value = color;
    form.submit();
}

function openCategoryEditor(id, name, color) {
    const newName = prompt('Update category name:', name);
    if (!newName) return;

    const newColor = prompt('Update category color hex code:', color || '#6c757d');
    if (!newColor) return;

    const form = document.getElementById('categoryForm');
    form.action = `/admin/inventory-categories/${id}`;
    form.method = 'POST';
    const existingMethodInput = form.querySelector('input[name="_method"]');
    if (existingMethodInput) {
        existingMethodInput.remove();
    }
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'PUT';
    form.appendChild(methodInput);
    document.getElementById('categoryNameInput').value = newName;
    document.getElementById('categoryColorInput').value = newColor;
    form.submit();
}
</script>
@endpush

@endsection
