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
/* Responsive Pickup Details Page */
.pk-detail-page {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding: 0 0.75rem;
}

.pk-hero {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    padding: 1.5rem;
    position: relative;
}

.pk-header-top {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.pk-header-left {
    flex: 1;
}

.pk-back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding: 0.5rem 1rem;
    background: transparent;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.pk-back-link:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.pk-title {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
    font-size: clamp(1.5rem, 5vw, 2rem);
    font-weight: 700;
    line-height: 1.2;
}

.pk-subtitle {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.pk-status-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
    white-space: nowrap;
}

.pk-status-chip.pending {
    background: rgba(251, 146, 60, 0.15);
    color: #ea580c;
}

.pk-status-chip.accepted {
    background: rgba(59, 130, 246, 0.15);
    color: #1d4ed8;
}

.pk-status-chip.en_route {
    background: rgba(34, 197, 94, 0.15);
    color: #166534;
}

.pk-status-chip.picked_up {
    background: rgba(34, 197, 94, 0.15);
    color: #166534;
}

.pk-status-chip.cancelled {
    background: rgba(239, 68, 68, 0.15);
    color: #b91c1c;
}

/* Summary Grid - Responsive */
.pk-summary-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    margin-top: 1.5rem;
}

@media (min-width: 576px) {
    .pk-summary-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 992px) {
    .pk-summary-grid {
        grid-template-columns: repeat(4, 1fr);
    }
    
    .pk-header-top {
        flex-direction: row;
        align-items: flex-start;
        justify-content: space-between;
    }
}

.pk-summary-card {
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1rem;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(34, 197, 94, 0.05));
    transition: all 0.2s ease;
}

.pk-summary-card:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.pk-summary-label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--text-secondary);
}

.pk-summary-value {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    word-break: break-word;
}

/* Panel Styles */
.pk-panel {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.pk-panel-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border-color);
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(34, 197, 94, 0.05));
}

.pk-panel-header i {
    font-size: 1.25rem;
    color: var(--primary-color);
}

.pk-panel-header h5 {
    margin: 0;
    font-weight: 700;
    font-size: 1rem;
    color: var(--text-primary);
}

.pk-panel-body {
    padding: 1.25rem;
}

.pk-field {
    margin-bottom: 1.25rem;
}

.pk-field:last-child {
    margin-bottom: 0;
}

.pk-field-label {
    display: block;
    margin-bottom: 0.4rem;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--text-secondary);
}

.pk-field-value {
    color: var(--text-primary);
    font-weight: 500;
    line-height: 1.6;
    word-break: break-word;
}

/* Proof Image Styles */
.pk-proof-image {
    width: 100%;
    max-height: 300px;
    object-fit: cover;
    border-radius: 12px;
    transition: transform 0.2s ease, filter 0.2s ease;
}

.pk-proof-image:hover {
    transform: scale(1.02);
    filter: brightness(1.05);
}

.pk-proof-image-wrapper {
    position: relative;
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(34, 197, 94, 0.1));
}

.pk-proof-overlay {
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

.pk-proof-image-wrapper:hover .pk-proof-overlay {
    opacity: 1;
}

.pk-proof-overlay i {
    color: white;
    font-size: 2rem;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.pk-proof-section {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(34, 197, 94, 0.05));
    border-radius: 12px;
    padding: 1.25rem;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.pk-proof-badge {
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

.pk-proof-badge i {
    color: #16a34a;
}

/* Note Box */
.pk-note-box {
    border: 1px dashed var(--border-color);
    border-radius: 12px;
    padding: 1rem;
    background: rgba(100, 116, 139, 0.05);
}

/* Map and Actions */
.pk-map-actions-inline {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.pk-map-actions-inline .btn,
.pk-action-stack .btn {
    border-radius: 8px;
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    flex: 1;
    min-width: 140px;
}

@media (max-width: 576px) {
    .pk-map-actions-inline .btn {
        min-width: auto;
        flex: 1 1 calc(50% - 0.375rem);
    }
}

#pickup-map {
    height: 260px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--border-color);
    margin-bottom: 1rem;
}

/* Timeline */
.pk-timeline {
    list-style: none;
    margin: 0;
    padding: 0;
}

.pk-timeline-item {
    position: relative;
    margin-left: 0;
    padding: 0 0 1.25rem 2.5rem;
    border-left: 2px solid var(--border-color);
    margin-left: 0.5rem;
}

.pk-timeline-item:last-child {
    padding-bottom: 0;
    border-left: none;
}

.pk-timeline-dot {
    position: absolute;
    top: 0;
    left: -0.6rem;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--card-bg);
    border: 2px solid var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
}

.pk-timeline-meta {
    color: var(--text-secondary);
    font-size: 0.85rem;
}

/* Alerts */
.pk-alert-inline {
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

/* Modal */
.pk-modal-close-btn {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 1050;
    background: rgba(0, 0, 0, 0.6);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
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

/* Responsive Grid Layout */
.pk-content-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 992px) {
    .pk-content-grid {
        grid-template-columns: 2fr 1fr;
    }
    
    .pk-detail-page {
        padding: 0;
    }
}

/* Mobile First Adjustments */
@media (max-width: 576px) {
    .pk-detail-page {
        padding: 0;
        gap: 1rem;
    }
    
    .pk-hero {
        padding: 1rem;
        border-radius: 12px;
    }
    
    .pk-title {
        font-size: 1.3rem;
    }
    
    .pk-panel-body {
        padding: 1rem;
    }
    
    .pk-summary-card {
        padding: 0.875rem;
    }
    
    .pk-status-chip {
        padding: 0.625rem 1rem;
        font-size: 0.8rem;
    }
}

/* Print Styles */
@media print {
    .pk-back-link,
    .pk-action-stack,
    .pk-map-actions-inline {
        display: none;
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
    <!-- Hero Section -->
    <div class="pk-hero">
        <div class="pk-header-top">
            <div class="pk-header-left">
                <a href="{{ route('admin.pickups.index') }}" class="pk-back-link">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <h1 class="pk-title">Pickup Request #{{ $pickup->id }}</h1>
                <p class="pk-subtitle">View and manage pickup request details</p>
            </div>
            <div>
                <span class="pk-status-chip {{ $pickup->status }}">
                    <i class="bi {{ $statusIcons[$pickup->status] ?? 'bi-circle' }}"></i>
                    {{ $statusMeta[$pickup->status] ?? ucfirst(str_replace('_', ' ', $pickup->status)) }}
                </span>
            </div>
        </div>

        <!-- Summary Grid -->
        <div class="pk-summary-grid">
            <div class="pk-summary-card">
                <span class="pk-summary-label"><i class="bi bi-person me-1"></i>Customer</span>
                <div class="pk-summary-value">{{ $pickup->customer->name }}</div>
            </div>
            <div class="pk-summary-card">
                <span class="pk-summary-label"><i class="bi bi-calendar me-1"></i>Preferred Date</span>
                <div class="pk-summary-value">{{ $pickup->preferred_date->format('M d, Y') }}</div>
            </div>
            <div class="pk-summary-card">
                <span class="pk-summary-label"><i class="bi bi-shop me-1"></i>Branch</span>
                <div class="pk-summary-value">{{ $pickup->branch->name }}</div>
            </div>
            <div class="pk-summary-card">
                <span class="pk-summary-label"><i class="bi bi-cash me-1"></i>Total Fee</span>
                <div class="pk-summary-value">₱{{ number_format($pickup->total_fee ?? 0, 2) }}</div>
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

                        @if($pickup->status == 'picked_up' && !$pickup->laundries_id)
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

                @if($pickup->laundries_id)
                    <div class="pk-panel">
                        <div class="pk-panel-header">
                            <i class="bi bi-basket3"></i>
                            <h6>Linked Laundry</h6>
                        </div>
                        <div class="pk-panel-body">
                            <div class="pk-field">
                                <span class="pk-field-label">Laundry ID</span>
                                <div class="pk-field-value">#{{ $pickup->laundries_id }}</div>
                            </div>
                            <a href="{{ route('admin.laundries.show', $pickup->laundries_id) }}" class="btn btn-outline-primary btn-sm">
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
