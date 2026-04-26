@extends('branch.layouts.app')

@section('page-title', 'Pickup Request #' . $pickup->id)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
.container-fluid {
    background: var(--bg-color);
    color: var(--text-primary);
}

.card {
    background: var(--card-bg);
    border-color: var(--border-color);
    color: var(--text-primary);
}

.card-header {
    border-bottom-color: var(--border-color);
}

.text-muted {
    color: var(--text-secondary) !important;
}

.modal-content {
    background: var(--card-bg);
    color: var(--text-primary);
}

.modal-header {
    border-bottom-color: var(--border-color);
}

.modal-footer {
    border-top-color: var(--border-color);
}

.form-control {
    background: var(--bg-color);
    border-color: var(--border-color);
    color: var(--text-primary);
}

.form-control:focus {
    background: var(--bg-color);
    border-color: var(--primary-color);
    color: var(--text-primary);
}

.proof-image {
    max-height: 200px;
    width: 100%;
    object-fit: cover;
    border-radius: 16px;
    cursor: pointer;
    transition: transform 0.2s ease, filter 0.2s ease;
}

.proof-image:hover {
    transform: scale(1.02);
    filter: brightness(1.1);
}

.proof-image-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
    border-radius: 16px;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(16, 185, 129, 0.1));
    padding: 2px;
}

.proof-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 16px;
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
    font-size: 2rem;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.proof-section {
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.05), rgba(16, 185, 129, 0.05));
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid rgba(14, 165, 233, 0.2);
}

.proof-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(14, 165, 233, 0.2));
    color: var(--text-primary);
    padding: 0.5rem 1rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 1rem;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.proof-badge i {
    color: #10B981;
}

.modal-backdrop-dark {
    background: rgba(0, 0, 0, 0.95) !important;
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('branch.pickups.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
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
