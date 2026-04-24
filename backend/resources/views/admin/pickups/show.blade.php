@extends('admin.layouts.app')

@section('page-title', 'Pickup Request #' . $pickup->id)

@push('styles')
<style>
:root {
    --pickup-primary: #3b82f6;
    --pickup-success: #10b981;
    --pickup-warning: #f59e0b;
    --pickup-danger: #ef4444;
    --pickup-info: #06b6d4;
}

.container-fluid {
    background: var(--bg-color);
    padding: 2rem 1.5rem;
}

.card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    color: var(--text-primary);
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.card-header {
    border-bottom: 1px solid var(--border-color);
    border-radius: 16px 16px 0 0 !important;
    padding: 1.25rem 1.5rem;
    font-weight: 600;
}

.card-body {
    padding: 1.5rem;
}

.card-body p {
    margin-bottom: 0.75rem;
    line-height: 1.6;
}

.card-body strong {
    color: var(--text-primary);
    font-weight: 600;
}

.text-muted {
    color: var(--text-secondary) !important;
}

.badge {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
}

.btn {
    border-radius: 10px;
    padding: 0.625rem 1.25rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-sm {
    padding: 0.375rem 0.875rem;
    font-size: 0.875rem;
}

.alert {
    border-radius: 12px;
    border: none;
    padding: 1rem 1.25rem;
}

.modal-content {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    color: var(--text-primary);
}

.modal-header {
    border-bottom: 1px solid var(--border-color);
    padding: 1.5rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid var(--border-color);
    padding: 1.25rem 1.5rem;
}

.form-control, .form-select {
    background: var(--bg-color);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 0.625rem 1rem;
    color: var(--text-primary);
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    background: var(--bg-color);
    border-color: var(--pickup-primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    color: var(--text-primary);
}

.form-label {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

/* Timeline Styling */
.list-unstyled li {
    padding: 0.75rem 0;
    border-left: 3px solid var(--border-color);
    padding-left: 1.5rem;
    margin-left: 0.5rem;
    position: relative;
}

.list-unstyled li i.bi-circle-fill {
    position: absolute;
    left: -0.6rem;
    background: var(--card-bg);
    padding: 0.25rem;
}

/* Status Badge Colors */
.bg-warning {
    background-color: var(--pickup-warning) !important;
    color: #000 !important;
}

.bg-info {
    background-color: var(--pickup-info) !important;
}

.bg-primary {
    background-color: var(--pickup-primary) !important;
}

.bg-success {
    background-color: var(--pickup-success) !important;
}

.bg-danger {
    background-color: var(--pickup-danger) !important;
}

.bg-secondary {
    background-color: #6b7280 !important;
}

/* Image Styling */
img.img-fluid {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Header Styling */
h1, h3, h5, h6 {
    color: var(--text-primary);
}

.h3 {
    font-weight: 700;
}

/* Responsive */
@media (max-width: 768px) {
    .container-fluid {
        padding: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.pickups.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
            <h1 class="h3 mb-0">Pickup Request #{{ $pickup->id }}</h1>
        </div>
        <div>
            @if($pickup->status == 'pending')
                <span class="badge bg-warning fs-6">Pending</span>
            @elseif($pickup->status == 'accepted')
                <span class="badge bg-info fs-6">Accepted</span>
            @elseif($pickup->status == 'en_route')
                <span class="badge bg-primary fs-6">En Route</span>
            @elseif($pickup->status == 'picked_up')
                <span class="badge bg-success fs-6">Picked Up</span>
            @elseif($pickup->status == 'cancelled')
                <span class="badge bg-danger fs-6">Cancelled</span>
            @endif
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Main Details --}}
        <div class="col-lg-8">
            {{-- Customer Information --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $pickup->customer->name }}</p>
                            <p><strong>Email:</strong> {{ $pickup->customer->email }}</p>
                            <p><strong>Phone:</strong> {{ $pickup->phone_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Customer ID:</strong> #{{ $pickup->customer->id }}</p>
                            <p><strong>Member Since:</strong> {{ $pickup->customer->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pickup Details --}}
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Pickup Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Branch:</strong> {{ $pickup->branch->name }}</p>
                            <p><strong>Preferred Date:</strong> {{ $pickup->preferred_date->format('F d, Y') }}</p>
                            @if($pickup->preferred_time)
                                <p><strong>Preferred Time:</strong> {{ date('g:i A', strtotime($pickup->preferred_time)) }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($pickup->service)
                                <p><strong>Service Requested:</strong> {{ $pickup->service->name }}</p>
                            @endif
                            @if($pickup->estimated_weight)
                                <p><strong>Estimated Weight:</strong> {{ $pickup->estimated_weight }} kg</p>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <p><strong>Pickup Address:</strong></p>
                    <p class="mb-2">{{ $pickup->pickup_address }}</p>

                    @if($pickup->latitude && $pickup->longitude)
                        <a href="{{ $pickup->map_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-geo-alt"></i> Open in Google Maps
                        </a>
                    @endif

                    @if($pickup->notes)
                        <hr>
                        <p><strong>Notes:</strong></p>
                        <p class="text-muted">{{ $pickup->notes }}</p>
                    @endif
                </div>
            </div>

            {{-- Pickup Proof Photo --}}
            @if(in_array($pickup->status, ['en_route', 'picked_up']))
                <div class="card mb-4">
                    <div class="card-header {{ $pickup->pickup_proof_photo ? 'bg-success' : 'bg-warning' }} text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-camera"></i> 
                            {{ $pickup->pickup_proof_photo ? 'Staff Proof Photo' : 'Upload Staff Proof Photo' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($pickup->pickup_proof_photo)
                            <img src="{{ asset('storage/pickup-proofs/' . $pickup->pickup_proof_photo) }}" 
                                 alt="Pickup Proof" 
                                 class="img-fluid rounded mb-3" 
                                 style="max-height: 400px; width: 100%; object-fit: cover;">
                            <p class="text-muted mb-0">
                                <i class="bi bi-clock"></i> 
                                Uploaded {{ $pickup->proof_uploaded_at->diffForHumans() }}
                            </p>
                        @else
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle"></i> 
                                Please upload a photo of the laundry when it arrives at the shop.
                            </div>
                            <form action="{{ route('admin.pickups.upload-proof', $pickup->id) }}" 
                                  method="POST" 
                                  enctype="multipart/form-data" 
                                  id="proofUploadForm">
                                @csrf
                                <div class="mb-3">
                                    <input type="file" 
                                           name="proof_photo" 
                                           id="proof_photo" 
                                           class="form-control" 
                                           accept="image/jpeg,image/png,image/jpg" 
                                           required>
                                    <small class="text-muted">Max 5MB, JPEG/PNG only</small>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-upload"></i> Upload Proof Photo
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Customer Proof Photo --}}
            @if($pickup->customer_proof_photo)
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-image"></i> Customer Proof Photo
                        </h5>
                    </div>
                    <div class="card-body">
                        <img src="{{ asset('storage/customer-pickup-proofs/' . $pickup->customer_proof_photo) }}" 
                             alt="Customer Proof" 
                             class="img-fluid rounded mb-3" 
                             style="max-height: 400px; width: 100%; object-fit: cover;">
                        <p class="text-muted mb-0">
                            <i class="bi bi-clock"></i> 
                            Uploaded by customer {{ $pickup->customer_proof_uploaded_at ? $pickup->customer_proof_uploaded_at->diffForHumans() : 'at request time' }}
                        </p>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle"></i> This photo was uploaded by the customer when requesting pickup to verify their laundry items.
                        </small>
                    </div>
                </div>
            @endif

            {{-- Timeline --}}
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Status Timeline</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="bi bi-circle-fill text-success"></i>
                            <strong>Created</strong>
                            <span class="text-muted float-end">{{ $pickup->created_at->format('M d, Y g:i A') }}</span>
                        </li>

                        @if($pickup->accepted_at)
                            <li class="mb-3">
                                <i class="bi bi-circle-fill text-info"></i>
                                <strong>Accepted</strong>
                                <span class="text-muted float-end">{{ $pickup->accepted_at->format('M d, Y g:i A') }}</span>
                            </li>
                        @endif

                        @if($pickup->en_route_at)
                            <li class="mb-3">
                                <i class="bi bi-circle-fill text-primary"></i>
                                <strong>En Route</strong>
                                <span class="text-muted float-end">{{ $pickup->en_route_at->format('M d, Y g:i A') }}</span>
                            </li>
                        @endif

                        @if($pickup->picked_up_at)
                            <li class="mb-3">
                                <i class="bi bi-circle-fill text-success"></i>
                                <strong>Picked Up</strong>
                                <span class="text-muted float-end">{{ $pickup->picked_up_at->format('M d, Y g:i A') }}</span>
                            </li>
                        @endif

                        @if($pickup->cancelled_at)
                            <li class="mb-3">
                                <i class="bi bi-circle-fill text-danger"></i>
                                <strong>Cancelled</strong>
                                <span class="text-muted float-end">{{ $pickup->cancelled_at->format('M d, Y g:i A') }}</span>
                                @if($pickup->cancellation_reason)
                                    <br><small class="text-muted">Reason: {{ $pickup->cancellation_reason }}</small>
                                @endif
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        {{-- Actions Sidebar --}}
        <div class="col-lg-4">
            {{-- Quick Actions --}}
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    @if($pickup->canBeAccepted())
                        <form action="{{ route('admin.pickups.accept', $pickup->id) }}" method="POST" class="mb-2">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Accept this pickup request? Customer will be notified.')">
                                <i class="bi bi-check-circle"></i> Accept Pickup Request
                            </button>
                        </form>
                    @endif

                    @if($pickup->canMarkEnRoute())
                        <form action="{{ route('admin.pickups.en-route', $pickup->id) }}" method="POST" class="mb-2">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-truck"></i> Mark as En Route
                            </button>
                        </form>
                    @endif

                    @if($pickup->canMarkPickedUp())
                        @if($pickup->pickup_proof_photo)
                            <form action="{{ route('admin.pickups.picked-up', $pickup->id) }}" method="POST" class="mb-2">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-box-seam"></i> Mark as Picked Up
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-secondary w-100 mb-2" disabled title="Upload proof photo first">
                                <i class="bi bi-box-seam"></i> Mark as Picked Up
                            </button>
                            <small class="text-muted d-block mb-2">
                                <i class="bi bi-exclamation-circle"></i> Upload proof photo first
                            </small>
                        @endif
                    @endif

                    @if($pickup->canBeCancelled())
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="bi bi-x-circle"></i> Cancel Pickup
                        </button>
                    @endif

                    @if($pickup->status == 'picked_up' && !$pickup->laundries_id)
                        <hr>
                        <a href="{{ route('admin.laundries.create', ['pickup_id' => $pickup->id]) }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-plus-circle"></i> Create Laundry from Pickup
                        </a>
                    @endif
                </div>
            </div>

            {{-- Assignment --}}
            @if($pickup->status != 'cancelled')
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Assigned Staff</h6>
                    </div>
                    <div class="card-body">
                        @if($pickup->assignedStaff)
                            <p><strong>{{ $pickup->assignedStaff->name }}</strong></p>
                            <p class="text-muted">{{ $pickup->assignedStaff->email }}</p>
                        @else
                            <p class="text-muted">Not assigned yet</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Linked Laundry --}}
            @if($pickup->laundries_id)
                <div class="card mb-4">
                    <div class="card-header bg-warning">
                        <h6 class="mb-0">Linked Laundry</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Laundry #{{ $pickup->laundries_id }}</strong></p>
                        <a href="{{ route('admin.laundries.show', $pickup->laundries_id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-box-arrow-up-right"></i> View Laundry
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.pickups.cancel', $pickup->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Pickup Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cancellation Reason *</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                    <p class="text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Customer will be notified about the cancellation.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Pickup Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
