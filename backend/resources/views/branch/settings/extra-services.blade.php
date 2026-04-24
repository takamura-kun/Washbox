@extends('branch.layouts.app')

@section('page-title', 'Extra Services Settings')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Extra Services Settings</h2>
            <p class="text-muted">Configure pricing for extra laundry services</p>
        </div>
        <a href="{{ route('branch.settings.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Settings
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('branch.settings.extra-services.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-3">
                    @foreach($extraServices as $service)
                    <div class="col-md-6">
                        <div class="card border">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="{{ $service->icon }} text-{{ $service->color }} fs-3 me-3"></i>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-0">{{ $service->service_name }}</h5>
                                        <small class="text-muted">{{ $service->description }}</small>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               name="services[{{ $service->id }}][is_active]" 
                                               value="1"
                                               {{ $service->is_active ? 'checked' : '' }}>
                                    </div>
                                </div>
                                
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="services[{{ $service->id }}][price]" 
                                                   value="{{ $service->price }}" 
                                                   step="0.01" 
                                                   min="0" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Display Order</label>
                                        <input type="number" 
                                               class="form-control" 
                                               name="services[{{ $service->id }}][display_order]" 
                                               value="{{ $service->display_order }}" 
                                               min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
