@extends('branch.layouts.app')

@section('page-title', 'Pickup Request #' . $pickup->id)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
/* Responsive Branch Pickups Show Page */
:root {
    --primary-color: #3b82f6;
    --success-color: #22c55e;
    --warning-color: #f97316;
    --danger-color: #ef4444;
    --info-color: #06b6d4;
    --bg-color: #f8fafc;
    --card-bg: #ffffff;
    --text-primary: #1e293b;
    --text-secondary: #64748b;
    --border-color: #e2e8f0;
}

body {
    background: var(--bg-color);
    color: var(--text-primary);
}

.container-fluid {
    padding: 1rem;
    max-width: 100%;
}

/* Header Responsive */
.pk-header {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 1rem;
    margin-bottom: 2rem;
    align-items: start;
}

@media (max-width: 640px) {
    .pk-header {
        grid-template-columns: 1fr;
    }
}

.pk-header-left a {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    margin-bottom: 1rem;
    background: transparent;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.pk-header-left a:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.pk-header-left h1 {
    margin: 0;
    font-size: clamp(1.5rem, 5vw, 2rem);
    font-weight: 700;
    color: var(--text-primary);
}

.pk-status-badge {
    display: inline-block;
    padding: 0.75rem 1.25rem;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 600;
    white-space: nowrap;
}

.pk-status-badge.pending {
    background: rgba(251, 146, 60, 0.15);
    color: #ea580c;
}

.pk-status-badge.accepted {
    background: rgba(59, 130, 246, 0.15);
    color: #1d4ed8;
}

.pk-status-badge.en_route {
    background: rgba(34, 197, 94, 0.15);
    color: #166534;
}

.pk-status-badge.picked_up {
    background: rgba(34, 197, 94, 0.15);
    color: #166534;
}

.pk-status-badge.cancelled {
    background: rgba(239, 68, 68, 0.15);
    color: #b91c1c;
}

/* Cards */
.card {
    background: var(--card-bg);
    border-color: var(--border-color);
    color: var(--text-primary);
    border-radius: 12px;
    border: 1px solid var(--border-color);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(34, 197, 94, 0.05));
    border-bottom-color: var(--border-color);
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border-color);
}

.card-header h5 {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
}

.card-header i {
    font-size: 1.1rem;
    color: var(--primary-color);
}

.card-body {
    padding: 1.25rem;
}

.text-muted {
    color: var(--text-secondary) !important;
}

/* Form Controls */
.form-control,
.form-select {
    background: var(--bg-color);
    border-color: var(--border-color);
    color: var(--text-primary);
    border-radius: 8px;
    transition: all 0.2s ease;
}

.form-control:focus,
.form-select:focus {
    background: var(--card-bg);
    border-color: var(--primary-color);
    color: var(--text-primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Proof Images */
.proof-image {
    max-height: 300px;
    width: 100%;
    object-fit: cover;
    border-radius: 12px;
    cursor: pointer;
    transition: transform 0.2s ease, filter 0.2s ease;
}

.proof-image:hover {
    transform: scale(1.02);
    filter: brightness(1.05);
}

.proof-image-wrapper {
    position: relative;
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(34, 197, 94, 0.1));
    margin-bottom: 1rem;
}

.proof-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease;
    cursor: pointer;
}

.proof-image-wrapper:hover .proof-overlay {
    opacity: 1;
}

.proof-overlay i {
    color: white;
    font-size: 2.5rem;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.proof-section {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(34, 197, 94, 0.05));
    border-radius: 12px;
    padding: 1.25rem;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.proof-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(59, 130, 246, 0.2));
    color: var(--text-primary);
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 1rem;
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.proof-badge i {
    color: #16a34a;
}

/* Alerts */
.alert {
    border-radius: 12px;
    border: 1px solid transparent;
    margin-bottom: 1.5rem;
}

/* Modal */
.modal-backdrop-dark {
    background: rgba(0, 0, 0, 0.95) !important;
}

.modal-content {
    background: var(--card-bg);
    color: var(--text-primary);
    border-radius: 12px;
    border-color: var(--border-color);
}

.modal-header {
    border-bottom-color: var(--border-color);
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(34, 197, 94, 0.05));
}

.modal-footer {
    border-top-color: var(--border-color);
}

.modal-body-fullscreen {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 80vh;
    padding: 0;
}

.modal-body-fullscreen img {
    max-width: 100%;
    max-height: 90vh;
    object-fit: contain;
}

/* Responsive Grid */
.pk-content-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 992px) {
    .pk-content-grid {
        grid-template-columns: 2fr 1fr;
    }
}

#pickup-map {
    height: 260px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--border-color);
    margin-bottom: 1rem;
}

/* Buttons */
.btn {
    border-radius: 8px;
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.85rem;
}

/* Timeline */
.pk-timeline {
    list-style: none;
    margin: 0;
    padding: 0;
}

.pk-timeline li {
    margin-bottom: 1.25rem;
    padding-left: 2.5rem;
    position: relative;
}

.pk-timeline i {
    position: absolute;
    left: 0;
    top: 0.2rem;
    font-size: 1.1rem;
}

/* Mobile Responsive */
@media (max-width: 640px) {
    .container-fluid {
        padding: 0.75rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .card-header h5 {
        font-size: 0.95rem;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .btn-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
}

/* Print Styles */
@media print {
    .btn,
    .form-control,
    .card-header .btn,
    [onclick] {
        display: none;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="pk-header">
        <div class="pk-header-left">
            <a href="{{ route('branch.pickups.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Back to Pickups
            </a>
            <h1>Pickup Request #{{ $pickup->id }}</h1>
        </div>
        <div>
            <span class="pk-status-badge {{ $pickup->status }}">
                @if($pickup->status == 'pending')
                    <i class="bi bi-clock me-2"></i>Pending
                @elseif($pickup->status == 'accepted')
                    <i class="bi bi-check-circle me-2"></i>Accepted
                @elseif($pickup->status == 'en_route')
                    <i class="bi bi-truck me-2"></i>En Route
                @elseif($pickup->status == 'picked_up')
                    <i class="bi bi-box-seam me-2"></i>Picked Up
                @elseif($pickup->status == 'cancelled')
                    <i class="bi bi-x-circle me-2"></i>Cancelled
                @endif
            </span>
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
                            <p><strong>Email:</strong> {{ $pickup->customer->email ?? 'N/A' }}</p>
                            <p><strong>Phone:</strong> {{ $pickup->contact_phone }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Customer ID:</strong> #{{ $pickup->customer->id }}</p>
                            <p><strong>Member Since:</strong> {{ $pickup->customer->created_at->format('M d, Y') }}</p>
                            @if($pickup->customer->total_laundries !== null)
                                <p><strong>Total Laundries:</strong> {{ $pickup->customer->total_laundries }}</p>
                            @endif
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
                            <p><strong>Preferred Date:</strong> {{ \Carbon\Carbon::parse($pickup->preferred_date)->format('F d, Y') }}</p>
                            @if($pickup->preferred_time_slot)
                                <p><strong>Preferred Time:</strong> {{ $pickup->preferred_time_slot }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($pickup->service)
                                <p><strong>Service Requested:</strong> {{ $pickup->service->name }}</p>
                            @endif
                            @if($pickup->promotion)
                                <div class="alert alert-info py-2 px-3 mt-2 mb-0" style="border-radius:8px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-tag-fill text-primary"></i>
                                        <div>
                                            <strong>Promo Package:</strong> {{ $pickup->promotion->name }}
                                            @if($pickup->promotion->display_price)
                                                <span class="badge bg-primary ms-1">₱{{ $pickup->promotion->display_price }} {{ $pickup->promotion->price_unit ?? 'per load' }}</span>
                                            @endif
                                            @if($pickup->promo_code)
                                                <span class="badge bg-secondary ms-1">Code: {{ $pickup->promo_code }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
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
                        <div class="mb-3">
                            <span class="badge bg-success">
                                <i class="bi bi-geo-alt-fill me-1"></i>
                                Geotagged Location
                            </span>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-pin-map me-1"></i>
                                    Coordinates: {{ number_format($pickup->latitude, 6) }}, {{ number_format($pickup->longitude, 6) }}
                                </small>
                            </div>
                        </div>
                        <div id="pickup-map" style="height:260px; border-radius:14px; overflow:hidden; border:1px solid var(--border-color); margin-bottom:1rem;"></div>
                    @endif

                    @if($pickup->landmark)
                        <p class="mb-2">
                            <strong>Landmark:</strong> {{ $pickup->landmark }}
                        </p>
                    @endif

                    @if($pickup->latitude && $pickup->longitude)
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="https://www.google.com/maps?q={{ $pickup->latitude }},{{ $pickup->longitude }}"
                               target="_blank"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-geo-alt"></i> Open in Google Maps
                            </a>
                            <a href="https://www.waze.com/ul?ll={{ $pickup->latitude }},{{ $pickup->longitude }}&navigate=yes"
                               target="_blank"
                               class="btn btn-sm btn-outline-info">
                                <i class="bi bi-signpost"></i> Open in Waze
                            </a>
                            <button type="button" 
                                    class="btn btn-sm btn-outline-success"
                                    onclick="copyCoordinates({{ $pickup->latitude }}, {{ $pickup->longitude }})">
                                <i class="bi bi-clipboard"></i> Copy Coordinates
                            </button>
                        </div>
                    @endif

                    @if($pickup->special_instructions)
                        <hr>
                        <p><strong>Special Instructions:</strong></p>
                        <p class="text-muted">{{ $pickup->special_instructions }}</p>
                    @endif
                </div>
            </div>

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
                            <br>
                            <small class="text-muted ms-3">{{ $pickup->created_at->diffForHumans() }}</small>
                        </li>

                        @if($pickup->accepted_at)
                            <li class="mb-3">
                                <i class="bi bi-circle-fill text-info"></i>
                                <strong>Accepted</strong>
                                <span class="text-muted float-end">{{ $pickup->accepted_at->format('M d, Y g:i A') }}</span>
                                @if($pickup->assignedStaff)
                                    <br>
                                    <small class="text-muted ms-3">By: {{ $pickup->assignedStaff->name }}</small>
                                @endif
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
                                    <br><small class="text-muted ms-3">Reason: {{ $pickup->cancellation_reason }}</small>
                                @endif
                            </li>
                        @endif
                    </ul>
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
                            <div class="proof-section">
                                <div class="proof-badge">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Laundry Received
                                </div>
                                <div class="proof-image-wrapper" onclick="openProofModal('{{ asset('storage/pickup-proofs/' . $pickup->pickup_proof_photo) }}')">
                                    <img src="{{ asset('storage/pickup-proofs/' . $pickup->pickup_proof_photo) }}" 
                                         alt="Pickup Proof" 
                                         class="proof-image">
                                    <div class="proof-overlay">
                                        <i class="bi bi-expand"></i>
                                    </div>
                                </div>
                                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(14, 165, 233, 0.15);">
                                    <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                        <i class="bi bi-clock me-2"></i> 
                                        Uploaded {{ $pickup->proof_uploaded_at ? \Carbon\Carbon::parse($pickup->proof_uploaded_at)->diffForHumans() : '' }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle"></i> 
                                Please upload a photo of the laundry when it arrives at the shop.
                            </div>
                            <form action="{{ route('branch.pickups.upload-proof', $pickup->id) }}" 
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
                        <div class="proof-section">
                            <div class="proof-badge">
                                <i class="bi bi-person-check-fill"></i>
                                Customer Verification
                            </div>
                            <div class="proof-image-wrapper" onclick="openProofModal('{{ asset('storage/customer-pickup-proofs/' . $pickup->customer_proof_photo) }}')">
                                <img src="{{ asset('storage/customer-pickup-proofs/' . $pickup->customer_proof_photo) }}" 
                                     alt="Customer Proof" 
                                     class="proof-image">
                                <div class="proof-overlay">
                                    <i class="bi bi-expand"></i>
                                </div>
                            </div>
                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(14, 165, 233, 0.15);">
                                <p class="text-muted mb-1" style="font-size: 0.9rem;">
                                    <i class="bi bi-clock me-2"></i> 
                                    Uploaded by customer {{ $pickup->customer_proof_uploaded_at ? $pickup->customer_proof_uploaded_at->diffForHumans() : 'at request time' }}
                                </p>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>Verification photo of laundry items
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Real-time Location Tracking removed - using main map above --}}
        </div>

        {{-- Actions Sidebar --}}
        <div class="col-lg-4">
            {{-- Quick Actions --}}
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    @if($pickup->status == 'pending')
                        <form action="{{ route('branch.pickups.accept', $pickup->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Accept this pickup request and assign it to yourself?')">
                                <i class="bi bi-check-circle"></i> Accept Pickup Request
                            </button>
                        </form>
                    @endif

                    @if($pickup->status == 'accepted' && $pickup->assigned_to == auth()->id())
                        <form action="{{ route('branch.pickups.en-route', $pickup->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-truck"></i> Mark as En Route
                            </button>
                        </form>
                    @endif

                    @if($pickup->status == 'en_route' && $pickup->assigned_to == auth()->id())
                        @if($pickup->pickup_proof_photo)
                            <form action="{{ route('branch.pickups.picked-up', $pickup->id) }}" method="POST" class="mb-2">
                                @csrf
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

                        {{-- GPS Update Button --}}
                        <button type="button" class="btn btn-outline-primary w-100 mb-2" onclick="updateGPSLocation()">
                            <i class="bi bi-pin-map"></i> Update My Location
                        </button>
                    @endif

                    @if(in_array($pickup->status, ['pending', 'accepted']) && ($pickup->assigned_to == auth()->id() || $pickup->status == 'pending'))
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="bi bi-x-circle"></i> Cancel Pickup
                        </button>
                    @endif

                    @if($pickup->status == 'picked_up' && !$pickup->laundries_id)
                        <hr>
                        <a href="{{ route('branch.laundries.create', ['pickup_id' => $pickup->id]) }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-plus-circle"></i> Create Laundry from Pickup
                        </a>
                    @endif

                    <hr>

                    {{-- Contact Customer --}}
                    <a href="tel:{{ $pickup->contact_phone }}" class="btn btn-outline-success w-100 mb-2">
                        <i class="bi bi-telephone"></i> Call Customer
                    </a>

                    @if($pickup->customer->email)
                        <a href="mailto:{{ $pickup->customer->email }}" class="btn btn-outline-info w-100">
                            <i class="bi bi-envelope"></i> Email Customer
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
                        <a href="{{ route('branch.laundries.show', $pickup->laundries_id) }}" class="btn btn-sm btn-outline-primary">
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
            <form action="{{ route('branch.pickups.cancel', $pickup->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Pickup Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reason" class="form-control" rows="3" required
                                  placeholder="Please provide a reason for cancellation"></textarea>
                    </div>
                    <p class="text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        The customer will be notified about this cancellation.
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

{{-- Proof Image Modal --}}
<div class="modal fade" id="proofImageModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content modal-backdrop-dark">
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="position: absolute; top: 1rem; right: 1rem; z-index: 1050;"></button>
            <div class="modal-body modal-body-fullscreen">
                <img id="proofModalImage" src="" alt="Proof Photo">
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openProofModal(imageSrc) {
    const modal = new bootstrap.Modal(document.getElementById('proofImageModal'));
    document.getElementById('proofModalImage').src = imageSrc;
    modal.show();
}

function openProofModal(imageSrc) {
    const modal = new bootstrap.Modal(document.getElementById('proofImageModal'));
    document.getElementById('proofModalImage').src = imageSrc;
    modal.show();
}

function copyCoordinates(lat, lng) {
    const coords = `${lat}, ${lng}`;
    navigator.clipboard.writeText(coords).then(() => {
        // Show success feedback
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
        btn.classList.remove('btn-outline-success');
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-success');
        }, 2000);
    }).catch(err => {
        alert('Failed to copy coordinates');
    });
}
</script>
<script>
(function() {
    const updateUrl = "{{ route('branch.pickups.update-location', $pickup->id) }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const startBtn = document.getElementById('startTrackingBtn');
    const stopBtn = document.getElementById('stopTrackingBtn');
    const statusEl = document.getElementById('locationStatus');
    const lastUpdatedEl = document.getElementById('lastUpdatedText');

    const pickupLat = {{ $pickup->latitude ?? 'null' }};
    const pickupLng = {{ $pickup->longitude ?? 'null' }};

    let watchId = null;
    let map = null;
    let currentMarker = null;
    let pickupMarker = null;

    function setStatus(message, isError = false) {
        statusEl.textContent = message;
        statusEl.classList.toggle('text-danger', isError);
        statusEl.classList.toggle('text-muted', !isError);
    }

    function setLastUpdated(timestamp) {
        if (!timestamp) {
            lastUpdatedEl.textContent = '';
            return;
        }
        const d = new Date(timestamp);
        lastUpdatedEl.textContent = 'Last update: ' + d.toLocaleString();
    }

    function initMap() {
        if (!window.L) return;

        map = L.map('map', { zoomControl: true }).setView([pickupLat || 0, pickupLng || 0], pickupLat && pickupLng ? 14 : 2);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        if (pickupLat && pickupLng) {
            pickupMarker = L.marker([pickupLat, pickupLng], { title: 'Pickup Location' })
                .addTo(map)
                .bindPopup('Pickup Location');
        }
    }

    function updateCurrentMarker(lat, lng) {
        if (!map) return;
        if (!currentMarker) {
            currentMarker = L.circleMarker([lat, lng], {
                radius: 8,
                color: '#198754',
                fillColor: '#198754',
                fillOpacity: 0.7
            }).addTo(map);
        } else {
            currentMarker.setLatLng([lat, lng]);
        }
        if (!pickupLat || !pickupLng) {
            map.setView([lat, lng], 16);
        } else {
            const bounds = L.latLngBounds([[lat, lng], [pickupLat, pickupLng]]);
            map.fitBounds(bounds.pad(0.3));
        }
    }

    async function sendLocation(lat, lng) {
        try {
            const res = await fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ latitude: lat, longitude: lng })
            });
            const json = await res.json();

            if (!json.success) {
                setStatus('Failed to send location: ' + (json.message || 'unknown error'), true);
            } else {
                setStatus('Location updated successfully.');
                setLastUpdated(new Date().toISOString());
            }
        } catch (err) {
            console.error('Location update failed', err);
            setStatus('Unable to send location. Check your connection.', true);
        }
    }

    function startTracking() {
        if (!navigator.geolocation) {
            setStatus('Geolocation not supported by your browser.', true);
            return;
        }

        startBtn.disabled = true;
        stopBtn.style.display = 'inline-block';

        watchId = navigator.geolocation.watchPosition(
            (pos) => {
                const { latitude, longitude } = pos.coords;
                updateCurrentMarker(latitude, longitude);
                sendLocation(latitude, longitude);
            },
            (err) => {
                console.warn('Geolocation error', err);
                const msg = err.code === err.PERMISSION_DENIED
                    ? 'Location permission denied.'
                    : err.code === err.POSITION_UNAVAILABLE
                        ? 'Location unavailable.'
                        : 'Timeout obtaining location.';
                setStatus(msg, true);
            },
            { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 }
        );

        setStatus('Tracking started. Sharing your location...', false);
    }

    function stopTracking() {
        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }
        startBtn.disabled = false;
        stopBtn.style.display = 'none';
        setStatus('Tracking paused. Click "Start Tracking" to resume.');
    }

    startBtn.addEventListener('click', startTracking);
    stopBtn.addEventListener('click', stopTracking);

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            // stop tracking when tab is hidden to save battery
            stopTracking();
        }
    });

    initMap();
})();
</script>

@if($pickup->latitude && $pickup->longitude)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const pickupMap = L.map('pickup-map', { zoomControl: true, attributionControl: false })
        .setView([{{ $pickup->latitude }}, {{ $pickup->longitude }}], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(pickupMap);
    L.marker([{{ $pickup->latitude }}, {{ $pickup->longitude }}])
        .addTo(pickupMap)
        .bindPopup('<b>Pickup Location</b><br>{{ addslashes($pickup->pickup_address) }}')
        .openPopup();
});
</script>
@endif
@endpush
