@extends('admin.layouts.app')

@section('title', 'Edit Service Type')
@section('page-title', 'EDIT SERVICE TYPE')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted small mb-0">Update service type template</p>
        </div>
        <a href="{{ route('admin.service-types.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Service Types
        </a>
    </div>

    <form action="{{ route('admin.service-types.update', $serviceType) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-4">
            {{-- BASIC INFORMATION --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header border-bottom py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-info-circle me-2" style="color: #3D3B6B;"></i>Basic Information
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $serviceType->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                <option value="">Select Category</option>
                                <option value="drop_off" {{ old('category', $serviceType->category) == 'drop_off' ? 'selected' : '' }}>🛍 Drop Off</option>
                                <option value="self_service" {{ old('category', $serviceType->category) == 'self_service' ? 'selected' : '' }}>🧺 Self Service</option>
                            </select>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="3">{{ old('description', $serviceType->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Icon (Bootstrap Icon class)</label>
                            <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror"
                                   value="{{ old('icon', $serviceType->icon) }}" placeholder="bi-box-seam">
                            <small class="text-muted">e.g., bi-droplet, bi-bag, bi-stars</small>
                            @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Display Order</label>
                            <input type="number" name="display_order" class="form-control @error('display_order') is-invalid @enderror"
                                   value="{{ old('display_order', $serviceType->display_order) }}" min="0">
                            @error('display_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- DEFAULT VALUES --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header border-bottom py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-sliders me-2" style="color: #3D3B6B;"></i>Default Values
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Default Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" name="defaults[price]" step="0.01" min="0"
                                       class="form-control @error('defaults.price') is-invalid @enderror"
                                       value="{{ old('defaults.price', $serviceType->defaults['price'] ?? 0) }}">
                            </div>
                            @error('defaults.price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pricing Type</label>
                            <select name="defaults[pricing_type]" class="form-select @error('defaults.pricing_type') is-invalid @enderror">
                                <option value="per_load" {{ old('defaults.pricing_type', $serviceType->defaults['pricing_type'] ?? 'per_load') == 'per_load' ? 'selected' : '' }}>Per Load</option>
                                <option value="per_piece" {{ old('defaults.pricing_type', $serviceType->defaults['pricing_type'] ?? 'per_load') == 'per_piece' ? 'selected' : '' }}>Per Piece</option>
                            </select>
                            @error('defaults.pricing_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Max Weight (kg)</label>
                            <input type="number" name="defaults[max_weight]" step="0.1" min="0"
                                   class="form-control @error('defaults.max_weight') is-invalid @enderror"
                                   value="{{ old('defaults.max_weight', $serviceType->defaults['max_weight'] ?? '') }}"
                                   placeholder="Leave blank for no limit">
                            @error('defaults.max_weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Turnaround Time (hours)</label>
                            <input type="number" name="defaults[turnaround]" min="0"
                                   class="form-control @error('defaults.turnaround') is-invalid @enderror"
                                   value="{{ old('defaults.turnaround', $serviceType->defaults['turnaround'] ?? 24) }}">
                            @error('defaults.turnaround')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- SETTINGS + ACTIONS --}}
            <div class="col-lg-4">
                {{-- SETTINGS CARD --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header border-bottom py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-gear me-2" style="color: #3D3B6B;"></i>Settings
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                   value="1" {{ old('is_active', $serviceType->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                        <small class="text-muted d-block mt-2">Active service types will appear in service creation</small>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="d-flex flex-column gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm" style="background: #3D3B6B; border: none;">
                        <i class="bi bi-check-circle me-2"></i>Update
                    </button>
                    <a href="{{ route('admin.service-types.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
.card-body {
    background-color: var(--card-bg) !important;
    color: var(--text-primary) !important;
}

.card-header {
    background-color: var(--border-color) !important;
    color: var(--text-primary) !important;
}

.form-control, .form-select {
    background-color: var(--input-bg) !important;
    color: var(--input-text) !important;
    border-color: var(--input-border) !important;
}

.input-group-text {
    background-color: var(--border-color) !important;
    color: var(--text-primary) !important;
    border-color: var(--input-border) !important;
}
</style>
@endpush
@endsection
