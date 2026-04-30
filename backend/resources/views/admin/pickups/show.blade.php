@extends('admin.layouts.app')

@section('page-title', 'Pickup Request #' . $pickup->id)
@section('page-icon', 'bi-truck')

@section('breadcrumbs')
    <a href="{{ route('admin.pickups.index') }}" class="breadcrumb-item">Pickup Requests</a>
    <span class="breadcrumb-divider">/</span>
    <span class="breadcrumb-item active">#{{ $pickup->id }}</span>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pickups.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
.pk-detail-page {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.pk-hero,
.pk-panel {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
}

.pk-hero {
    padding: 1.5rem;
}

.pk-back-link {
    margin-bottom: 1rem;
}

.pk-title {
    margin: 0;
    color: var(--text-primary);
    font-size: 1.85rem;
    font-weight: 800;
}

.pk-subtitle {
    margin: 0.4rem 0 0;
    color: var(--text-secondary);
}

.pk-status-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.7rem 1rem;
    border-radius: 999px;
    font-size: 0.92rem;
    font-weight: 800;
}

.pk-status-chip.pending {
    background: var(--pk-badge-pending-bg);
    color: var(--pk-badge-pending-text);
}

.pk-status-chip.accepted {
    background: var(--pk-badge-accepted-bg);
    color: var(--pk-badge-accepted-text);
}

.pk-status-chip.en_route {
    background: var(--pk-badge-enroute-bg);
    color: var(--pk-badge-enroute-text);
}

.pk-status-chip.picked_up {
    background: var(--pk-badge-pickup-bg);
    color: var(--pk-badge-pickup-text);
}

.pk-status-chip.cancelled {
    background: var(--pk-badge-cancel-bg);
    color: var(--pk-badge-cancel-text);
}

.pk-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
    margin-top: 1.25rem;
}

.pk-summary-card {
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 1rem 1.1rem;
    background: linear-gradient(180deg, rgba(61, 59, 107, 0.08), rgba(61, 59, 107, 0.02));
}

.pk-summary-label {
    margin-bottom: 0.4rem;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--text-secondary);
}

.pk-summary-value {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
}

.pk-panel {
    overflow: hidden;
}

.pk-panel-header {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 1.1rem 1.35rem;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-primary);
}

.pk-panel-header i {
    font-size: 1.05rem;
    color: var(--primary-color);
}

.pk-panel-header h5,
.pk-panel-header h6 {
    margin: 0;
    font-weight: 700;
}

.pk-panel-body {
    padding: 1.35rem;
}

.pk-field {
    margin-bottom: 1rem;
}

.pk-field:last-child {
    margin-bottom: 0;
}

.pk-field-label {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.74rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--text-secondary);
}

.pk-field-value {
    color: var(--text-primary);
    font-weight: 600;
    line-height: 1.65;
}

.pk-note-box {
    border: 1px dashed var(--border-color);
    border-radius: 14px;
    padding: 1rem;
    background: rgba(148, 163, 184, 0.08);
}

.pk-map-actions,
.pk-action-stack {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.pk-map-actions-inline {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.pk-map-actions-inline .btn,
.pk-action-stack .btn {
    border-radius: 12px;
}

.pk-proof-image {
    width: 100%;
    max-height: 200px;
    object-fit: cover;
    border-radius: 16px;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
    cursor: pointer;
    transition: transform 0.2s ease, filter 0.2s ease;
}

.pk-proof-image:hover {
    transform: scale(1.02);
    filter: brightness(1.1);
}

.pk-proof-image-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
    border-radius: 16px;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(16, 185, 129, 0.1));
    padding: 2px;
}

.pk-proof-overlay {
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

.pk-proof-image-wrapper:hover .pk-proof-overlay {
    opacity: 1;
}

.pk-proof-overlay i {
    color: white;
    font-size: 2rem;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.pk-proof-section {
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.05), rgba(16, 185, 129, 0.05));
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid rgba(14, 165, 233, 0.2);
}

.pk-proof-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid rgba(14, 165, 233, 0.15);
}

.pk-proof-header i {
    font-size: 1.25rem;
    color: var(--primary-color);
}

.pk-proof-header h5 {
    margin: 0;
    font-weight: 700;
    color: var(--text-primary);
}

.pk-proof-badge {
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

.pk-proof-badge i {
    color: #10B981;
}

.pk-timeline {
    list-style: none;
    margin: 0;
    padding: 0;
}

.pk-timeline-item {
    position: relative;
    margin-left: 0.8rem;
    padding: 0 0 1.25rem 2rem;
    border-left: 2px solid var(--border-color);
}

.pk-timeline-item:last-child {
    padding-bottom: 0;
}

.pk-timeline-dot {
    position: absolute;
    top: 0.15rem;
    left: -0.72rem;
    width: 22px;
    height: 22px;
    border-radius: 999px;
    background: var(--card-bg);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.pk-timeline-meta {
    color: var(--text-secondary);
    font-size: 0.85rem;
}

.pk-side-stack {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.pk-side-stack hr {
    border-color: var(--border-color);
}

.pk-alert-inline {
    border-radius: 14px;
    border: 1px solid transparent;
}

.pk-modal-close-btn {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 1050;
    background: rgba(0, 0, 0, 0.5);
    border: none;
    color: white;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s ease;
}

.pk-modal-close-btn:hover {
    background: rgba(0, 0, 0, 0.8);
}

@media (max-width: 991px) {
    .pk-summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 576px) {
    .pk-hero,
    .pk-panel-body {
        padding: 1rem;
    }

    .pk-summary-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@section('content')
@php
    $statusIcons = [
        'pending' => 'bi-clock-history',
        'accepted' => 'bi-check-circle',
        'en_route' => 'bi-truck',
        'picked_up' => 'bi-box-seam',
        'cancelled' => 'bi-x-circle',
    ];

    $statusMeta = [
        'pending' => 'Pending',
        'accepted' => 'Accepted',
        'en_route' => 'En Route',
        'picked_up' => 'Picked Up',
        'cancelled' => 'Cancelled',
    ];
@endphp

<div class="pk-detail-page">
    <div class="pk-hero">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
            <div>
                <a href="{{ route('admin.pickups.index') }}" class="btn btn-outline-secondary btn-sm pk-back-link">
                    <i class="bi bi-arrow-left"></i> Back to Pickup Requests
                </a>
                <h1 class="pk-title">Pickup Request #{{ $pickup->id }}</h1>
                <p class="pk-subtitle">This detail view now matches the newer pickup module design used across the admin dashboard.</p>
            </div>
            <div>
                <span class="pk-status-chip {{ $pickup->status }}">
                    <i class="bi {{ $statusIcons[$pickup->status] ?? 'bi-circle' }}"></i>
                    {{ $statusMeta[$pickup->status] ?? ucfirst(str_replace('_', ' ', $pickup->status)) }}
                </span>
            </div>
        </div>

        <div class="pk-summary-grid">
            <div class="pk-summary-card">
                <div class="pk-summary-label">Customer</div>
                <div class="pk-summary-value">{{ $pickup->customer->name }}</div>
            </div>
            <div class="pk-summary-card">
                <div class="pk-summary-label">Preferred Date</div>
                <div class="pk-summary-value">{{ $pickup->preferred_date->format('M d, Y') }}</div>
            </div>
            <div class="pk-summary-card">
                <div class="pk-summary-label">Branch</div>
                <div class="pk-summary-value">{{ $pickup->branch->name }}</div>
            </div>
            <div class="pk-summary-card">
                <div class="pk-summary-label">Total Fee</div>
                <div class="pk-summary-value">Php {{ number_format($pickup->total_fee ?? 0, 2) }}</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show pk-alert-inline mb-0" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show pk-alert-inline mb-0" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="pk-panel">
                <div class="pk-panel-header">
                    <i class="bi bi-person"></i>
                    <h5>Customer Information</h5>
                </div>
                <div class="pk-panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="pk-field">
                                <span class="pk-field-label">Name</span>
                                <div class="pk-field-value">{{ $pickup->customer->name }}</div>
                            </div>
                            <div class="pk-field">
                                <span class="pk-field-label">Email</span>
                                <div class="pk-field-value">{{ $pickup->customer->email ?? 'N/A' }}</div>
                            </div>
                            <div class="pk-field">
                                <span class="pk-field-label">Phone</span>
                                <div class="pk-field-value">{{ $pickup->phone_number }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="pk-field">
                                <span class="pk-field-label">Customer ID</span>
                                <div class="pk-field-value">#{{ $pickup->customer->id }}</div>
                            </div>
                            <div class="pk-field">
                                <span class="pk-field-label">Member Since</span>
                                <div class="pk-field-value">{{ $pickup->customer->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pk-panel">
                <div class="pk-panel-header">
                    <i class="bi bi-geo-alt"></i>
                    <h5>Pickup Details</h5>
                </div>
                <div class="pk-panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="pk-field">
                                <span class="pk-field-label">Branch</span>
                                <div class="pk-field-value">{{ $pickup->branch->name }}</div>
                            </div>
                            <div class="pk-field">
                                <span class="pk-field-label">Preferred Date</span>
                                <div class="pk-field-value">{{ $pickup->preferred_date->format('F d, Y') }}</div>
                            </div>
                            @if($pickup->preferred_time)
                                <div class="pk-field">
                                    <span class="pk-field-label">Preferred Time</span>
                                    <div class="pk-field-value">{{ date('g:i A', strtotime($pickup->preferred_time)) }}</div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($pickup->service)
                                <div class="pk-field">
                                    <span class="pk-field-label">Service Requested</span>
                                    <div class="pk-field-value">{{ $pickup->service->name }}</div>
                                </div>
                            @endif
                            @if($pickup->promotion)
                                <div class="pk-field">
                                    <span class="pk-field-label">Promo Package</span>
                                    <div class="pk-field-value">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <i class="bi bi-tag-fill text-primary"></i>
                                            <strong>{{ $pickup->promotion->name }}</strong>
                                            @if($pickup->promotion->display_price)
                                                <span class="badge bg-primary">₱{{ $pickup->promotion->display_price }} {{ $pickup->promotion->price_unit ?? 'per load' }}</span>
                                            @endif
                                            @if($pickup->promo_code)
                                                <span class="badge bg-secondary">Code: {{ $pickup->promo_code }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($pickup->estimated_weight)
                                <div class="pk-field">
                                    <span class="pk-field-label">Estimated Weight</span>
                                    <div class="pk-field-value">{{ $pickup->estimated_weight }} kg</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="pk-field">
                        <span class="pk-field-label">Pickup Address</span>
                        @if($pickup->manual_address && $pickup->address_manually_edited)
                            <div class="pk-field-value">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi bi-pencil-square text-primary mt-1"></i>
                                    <div>
                                        <div>{{ $pickup->manual_address }}</div>
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>Manually entered by customer
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="pk-field-value">{{ $pickup->pickup_address }}</div>
                        @endif
                        @if($pickup->latitude && $pickup->longitude)
                            <div class="mt-2">
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-geo-alt-fill me-1"></i>
                                    Geotagged: {{ number_format($pickup->latitude, 6) }}, {{ number_format($pickup->longitude, 6) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    @if($pickup->latitude && $pickup->longitude)
                        <div class="pk-map-actions-inline mb-3">
                            <a href="https://www.google.com/maps?q={{ $pickup->latitude }},{{ $pickup->longitude }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-geo-alt me-1"></i>Open in Google Maps
                            </a>
                            <a href="https://www.waze.com/ul?ll={{ $pickup->latitude }},{{ $pickup->longitude }}&navigate=yes" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-signpost me-1"></i>Open in Waze
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="copyCoords({{ $pickup->latitude }}, {{ $pickup->longitude }})">
                                <i class="bi bi-clipboard me-1"></i>Copy Coords
                            </button>
                        </div>
                        <div id="pickup-map" style="height:260px; border-radius:14px; overflow:hidden; border:1px solid var(--border-color);"></div>
                    @endif

                    @if($pickup->notes)
                        <hr>
                        <div class="pk-note-box">
                            <span class="pk-field-label">Notes</span>
                            <div class="pk-field-value text-muted">{{ $pickup->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>

            @if(in_array($pickup->status, ['en_route', 'picked_up']))
                <div class="pk-panel">
                    <div class="pk-panel-header">
                        <i class="bi bi-camera"></i>
                        <h5>{{ $pickup->pickup_proof_photo ? 'Staff Proof Photo' : 'Upload Staff Proof Photo' }}</h5>
                    </div>
                    <div class="pk-panel-body">
                        @if($pickup->pickup_proof_photo)
                            <div class="pk-proof-section">
                                <div class="pk-proof-badge">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Laundry Received
                                </div>
                                <div class="pk-proof-image-wrapper" onclick="openProofModal('{{ asset('storage/pickup-proofs/' . $pickup->pickup_proof_photo) }}')">
                                    <img src="{{ asset('storage/pickup-proofs/' . $pickup->pickup_proof_photo) }}"
                                         alt="Pickup Proof"
                                         class="pk-proof-image">
                                    <div class="pk-proof-overlay">
                                        <i class="bi bi-expand"></i>
                                    </div>
                                </div>
                                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(14, 165, 233, 0.15);">
                                    <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                        <i class="bi bi-clock me-2"></i>Uploaded {{ $pickup->proof_uploaded_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info pk-alert-inline mb-3">
                                <i class="bi bi-info-circle me-2"></i>Please upload a photo of the laundry when it arrives at the shop.
                            </div>
                            <form action="{{ route('admin.pickups.upload-proof', $pickup->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <input type="file"
                                           name="proof_photo"
                                           class="form-control"
                                           accept="image/jpeg,image/png,image/jpg"
                                           required>
                                    <small class="text-muted">Max 5MB, JPEG/PNG only</small>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-upload me-1"></i>Upload Proof Photo
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            @if($pickup->customer_proof_photo)
                <div class="pk-panel">
                    <div class="pk-panel-header">
                        <i class="bi bi-image"></i>
                        <h5>Customer Proof Photo</h5>
                    </div>
                    <div class="pk-panel-body">
                        <div class="pk-proof-section">
                            <div class="pk-proof-badge">
                                <i class="bi bi-person-check-fill"></i>
                                Customer Verification
                            </div>
                            <div class="pk-proof-image-wrapper" onclick="openProofModal('{{ asset('storage/customer-pickup-proofs/' . $pickup->customer_proof_photo) }}')">
                                <img src="{{ asset('storage/customer-pickup-proofs/' . $pickup->customer_proof_photo) }}"
                                     alt="Customer Proof"
                                     class="pk-proof-image">
                                <div class="pk-proof-overlay">
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

            <div class="pk-panel">
                <div class="pk-panel-header">
                    <i class="bi bi-clock-history"></i>
                    <h5>Status Timeline</h5>
                </div>
                <div class="pk-panel-body">
                    <ul class="pk-timeline">
                        <li class="pk-timeline-item">
                            <span class="pk-timeline-dot"><i class="bi bi-circle-fill text-success"></i></span>
                            <div class="fw-semibold text-primary-emphasis">Created</div>
                            <div class="pk-timeline-meta">{{ $pickup->created_at->format('M d, Y g:i A') }}</div>
                        </li>

                        @if($pickup->accepted_at)
                            <li class="pk-timeline-item">
                                <span class="pk-timeline-dot"><i class="bi bi-circle-fill text-info"></i></span>
                                <div class="fw-semibold text-primary-emphasis">Accepted</div>
                                <div class="pk-timeline-meta">{{ $pickup->accepted_at->format('M d, Y g:i A') }}</div>
                            </li>
                        @endif

                        @if($pickup->en_route_at)
                            <li class="pk-timeline-item">
                                <span class="pk-timeline-dot"><i class="bi bi-circle-fill text-primary"></i></span>
                                <div class="fw-semibold text-primary-emphasis">En Route</div>
                                <div class="pk-timeline-meta">{{ $pickup->en_route_at->format('M d, Y g:i A') }}</div>
                            </li>
                        @endif

                        @if($pickup->picked_up_at)
                            <li class="pk-timeline-item">
                                <span class="pk-timeline-dot"><i class="bi bi-circle-fill text-success"></i></span>
                                <div class="fw-semibold text-primary-emphasis">Picked Up</div>
                                <div class="pk-timeline-meta">{{ $pickup->picked_up_at->format('M d, Y g:i A') }}</div>
                            </li>
                        @endif

                        @if($pickup->cancelled_at)
                            <li class="pk-timeline-item">
                                <span class="pk-timeline-dot"><i class="bi bi-circle-fill text-danger"></i></span>
                                <div class="fw-semibold text-primary-emphasis">Cancelled</div>
                                <div class="pk-timeline-meta">{{ $pickup->cancelled_at->format('M d, Y g:i A') }}</div>
                                @if($pickup->cancellation_reason)
                                    <div class="pk-timeline-meta mt-1">Reason: {{ $pickup->cancellation_reason }}</div>
                                @endif
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="pk-side-stack">
                <div class="pk-panel">
                    <div class="pk-panel-header">
                        <i class="bi bi-lightning"></i>
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="pk-panel-body pk-action-stack">
                        @if($pickup->canBeAccepted())
                            <form action="{{ route('admin.pickups.accept', $pickup->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Accept this pickup request? Customer will be notified.')">
                                    <i class="bi bi-check-circle me-1"></i>Accept Pickup Request
                                </button>
                            </form>
                        @endif

                        @if($pickup->canMarkEnRoute())
                            <form action="{{ route('admin.pickups.en-route', $pickup->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-truck me-1"></i>Mark as En Route
                                </button>
                            </form>
                        @endif

                        @if($pickup->canMarkPickedUp())
                            @if($pickup->pickup_proof_photo)
                                <form action="{{ route('admin.pickups.picked-up', $pickup->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-box-seam me-1"></i>Mark as Picked Up
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn btn-secondary w-100" disabled title="Upload proof photo first">
                                    <i class="bi bi-box-seam me-1"></i>Mark as Picked Up
                                </button>
                                <small class="text-muted"><i class="bi bi-exclamation-circle me-1"></i>Upload proof photo first.</small>
                            @endif
                        @endif

                        @if($pickup->canBeCancelled())
                            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="bi bi-x-circle me-1"></i>Cancel Pickup
                            </button>
                        @endif

                        @if($pickup->status == 'picked_up' && !$pickup->laundry)
                            <hr>
                            <a href="{{ route('admin.laundries.create', ['pickup_id' => $pickup->id]) }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-plus-circle me-1"></i>Create Laundry from Pickup
                            </a>
                        @endif
                    </div>
                </div>

                @if($pickup->status != 'cancelled')
                    <div class="pk-panel">
                        <div class="pk-panel-header">
                            <i class="bi bi-person-badge"></i>
                            <h6>Assigned Staff</h6>
                        </div>
                        <div class="pk-panel-body">
                            @if($pickup->assignedStaff)
                                <div class="pk-field">
                                    <span class="pk-field-label">Name</span>
                                    <div class="pk-field-value">{{ $pickup->assignedStaff->name }}</div>
                                </div>
                                <div class="pk-field">
                                    <span class="pk-field-label">Email</span>
                                    <div class="pk-field-value">{{ $pickup->assignedStaff->email }}</div>
                                </div>
                            @else
                                <p class="text-muted mb-0">Not assigned yet.</p>
                            @endif
                        </div>
                    </div>
                @endif

                @if($pickup->laundry)
                    <div class="pk-panel">
                        <div class="pk-panel-header">
                            <i class="bi bi-basket3"></i>
                            <h6>Linked Laundry</h6>
                        </div>
                        <div class="pk-panel-body">
                            <div class="pk-field">
                                <span class="pk-field-label">Tracking #</span>
                                <div class="pk-field-value">{{ $pickup->laundry->tracking_number }}</div>
                            </div>
                            <div class="pk-field">
                                <span class="pk-field-label">Status</span>
                                <div class="pk-field-value">
                                    <span class="badge bg-{{ in_array($pickup->laundry->status, ['completed','paid']) ? 'success' : (in_array($pickup->laundry->status, ['cancelled']) ? 'danger' : 'primary') }}">
                                        {{ $pickup->laundry->status_label }}
                                    </span>
                                </div>
                            </div>
                            <div class="pk-field">
                                <span class="pk-field-label">Total</span>
                                <div class="pk-field-value">₱{{ number_format($pickup->laundry->total_amount, 2) }}</div>
                            </div>
                            <a href="{{ route('admin.laundries.show', $pickup->laundry->id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-box-arrow-up-right me-1"></i>View Laundry
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

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
                    <p class="text-danger mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>Customer will be notified about the cancellation.
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

<div class="modal fade" id="proofImageModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content pk-modal-backdrop">
            <button type="button" class="btn-close btn-close-white pk-modal-close-btn" data-bs-dismiss="modal"></button>
            <div class="modal-body pk-modal-body">
                <img id="proofModalImage" src="" alt="Proof Photo" style="max-width: 100%; max-height: 90vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<script>
function openProofModal(imageSrc) {
    const modal = new bootstrap.Modal(document.getElementById('proofImageModal'));
    document.getElementById('proofModalImage').src = imageSrc;
    modal.show();
}

function copyCoords(lat, lng) {
    navigator.clipboard.writeText(`${lat}, ${lng}`).then(() => {
        const btn = event.target.closest('button');
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
        btn.classList.replace('btn-outline-success', 'btn-success');
        setTimeout(() => { btn.innerHTML = orig; btn.classList.replace('btn-success', 'btn-outline-success'); }, 2000);
    });
}

@if($pickup->latitude && $pickup->longitude)
document.addEventListener('DOMContentLoaded', function () {
    const map = L.map('pickup-map', { zoomControl: true, attributionControl: false })
        .setView([{{ $pickup->latitude }}, {{ $pickup->longitude }}], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    L.marker([{{ $pickup->latitude }}, {{ $pickup->longitude }}])
        .addTo(map)
        .bindPopup('<b>Pickup Location</b><br>{{ addslashes($pickup->pickup_address) }}')
        .openPopup();
});
@endif
</script>
@endsection
