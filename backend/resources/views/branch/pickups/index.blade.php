@extends('branch.layouts.app')

@section('title', 'Pickup Requests')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pickups.css') }}">
    <!-- Leaflet CSS - Local Assets -->
    <link rel="stylesheet" href="{{ asset('assets/leaflet/leaflet.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/leaflet/MarkerCluster.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/leaflet/MarkerCluster.Default.css') }}">
    <style>
        .pickup-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .pickup-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
        }
        /* Light mode */
        [data-theme="light"] .pickup-card {
            background-color: #ffffff !important;
            color: #111827 !important;
        }
        /* Dark mode */
        [data-theme="dark"] .pickup-card {
            background-color: #1F2937 !important;
            color: #F9FAFB !important;
        }

        /* Map View Styles */
        .view-toggle-btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            background: var(--card-bg);
            color: var(--text-primary);
            transition: all 0.2s;
        }
        .view-toggle-btn.active {
            background: linear-gradient(135deg, #3D3B6B 0%, #7C78C8 100%);
            color: white;
            border-color: #3D3B6B;
        }
        .view-toggle-btn:hover:not(.active) {
            background: var(--hover-bg);
        }

        #pickupMap {
            height: 600px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .map-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .map-control-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: white;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }

        .map-control-btn:hover {
            background: #f8f9fa;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .pickup-marker {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            font-size: 16px;
            color: white;
            font-weight: bold;
        }

        .marker-pending { background: #fbbf24; }
        .marker-accepted { background: #60a5fa; }
        .marker-en_route { background: #3b82f6; }
        .marker-picked_up { background: #10b981; }
        .marker-cancelled { background: #ef4444; }

        .leaflet-popup-content-wrapper {
            border-radius: 12px;
            padding: 0;
        }

        .popup-content {
            padding: 12px;
            min-width: 250px;
        }

        .popup-header {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 8px;
            color: #111827;
        }

        .popup-info {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .popup-actions {
            margin-top: 12px;
            display: flex;
            gap: 6px;
        }

        .route-line {
            stroke: #3D3B6B;
            stroke-width: 4;
            stroke-opacity: 0.8;
        }

        .map-legend {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: white;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            z-index: 1000;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .legend-item:last-child {
            margin-bottom: 0;
        }

        .legend-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
    </style>
@endpush

@section('content')
<div class="pk-page">

    {{-- ── Alerts ─────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Stat Cards ─────────────────────────────────────────── --}}
    <div class="pk-stats-grid" style="background-color: transparent !important;">
        <div class="pk-stat-card" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
            <div class="pk-stat-icon warning"><i class="bi bi-clock-history"></i></div>
            <div>
                <div class="pk-stat-label" style="color: var(--text-secondary) !important;">Pending</div>
                <div class="pk-stat-value" style="color: var(--text-primary) !important;">{{ $stats['pending'] ?? 0 }}</div>
            </div>
        </div>
        <div class="pk-stat-card" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
            <div class="pk-stat-icon info"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="pk-stat-label" style="color: var(--text-secondary) !important;">Accepted</div>
                <div class="pk-stat-value" style="color: var(--text-primary) !important;">{{ $stats['accepted'] ?? 0 }}</div>
            </div>
        </div>
        <div class="pk-stat-card" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
            <div class="pk-stat-icon primary"><i class="bi bi-truck"></i></div>
            <div>
                <div class="pk-stat-label" style="color: var(--text-secondary) !important;">En Route</div>
                <div class="pk-stat-value" style="color: var(--text-primary) !important;">{{ $stats['en_route'] ?? 0 }}</div>
            </div>
        </div>
        <div class="pk-stat-card" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
            <div class="pk-stat-icon success"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="pk-stat-label" style="color: var(--text-secondary) !important;">Picked Up</div>
                <div class="pk-stat-value" style="color: var(--text-primary) !important;">{{ $stats['picked_up'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    {{-- Tabs --}}
    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('branch.pickups.index', array_merge(request()->except('tab','page'), ['tab'=>'active'])) }}"
           class="btn {{ $tab === 'active' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi bi-truck me-1"></i>Active Pickups
            <span class="badge {{ $tab === 'active' ? 'bg-white text-primary' : 'bg-primary' }} ms-1">{{ $activeCount }}</span>
        </a>
        <a href="{{ route('branch.pickups.index', array_merge(request()->except('tab','page'), ['tab'=>'laundry'])) }}"
           class="btn {{ $tab === 'laundry' ? 'btn-success' : 'btn-outline-secondary' }}">
            <i class="bi bi-basket me-1"></i>Transferred to Laundry
            <span class="badge {{ $tab === 'laundry' ? 'bg-white text-success' : 'bg-success' }} ms-1">{{ $laundryCount }}</span>
        </a>
    </div>


    {{-- ── Filters ─────────────────────────────────────────────── --}}
    <div class="pk-filter-card" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <!-- View Toggle Buttons -->
            <div class="d-flex gap-2 align-items-center">
                <div class="btn-group" role="group">

                </div>
                <div id="mapControls" style="display:none;" class="d-flex gap-2">

                </div>
            </div>
        </div>
        <form method="GET" action="{{ route('branch.pickups.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="all" {{ !request('status')||request('status')==='all'?'selected':'' }}>All Status</option>
                    <option value="pending"  {{ request('status')==='pending'  ?'selected':'' }}>Pending</option>
                    <option value="accepted" {{ request('status')==='accepted' ?'selected':'' }}>Accepted</option>
                    <option value="en_route" {{ request('status')==='en_route' ?'selected':'' }}>En Route</option>
                    <option value="picked_up"{{ request('status')==='picked_up'?'selected':'' }}>Picked Up</option>
                    <option value="cancelled"{{ request('status')==='cancelled'?'selected':'' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Customer name or address" value="{{ request('search') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill" style="border-radius:8px;">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <a href="{{ route('branch.pickups.index') }}"
                   class="btn btn-outline-secondary" style="border-radius:8px;" title="Clear">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>


    {{-- ── Pickup Cards Grid ────────────────────────────────────────────────── --}}
    <div id="gridViewContainer">
    @if($pickups->count() > 0)
        <div class="row g-3" style="background-color: transparent !important;">
            @foreach($pickups as $pickup)
            @php
                $hasLaundry = $pickup->laundry !== null;
                $isMyPickup = $pickup->assigned_to === auth()->id();
                $adminAccepted = $pickup->isAccepted() && $pickup->assignedStaff && !$isMyPickup;
                $statusColors = [
                    'pending' => 'warning',
                    'accepted' => 'info',
                    'en_route' => 'primary',
                    'picked_up' => 'success',
                    'cancelled' => 'danger',
                ];
                $color = $statusColors[$pickup->status] ?? 'secondary';
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 pickup-card" style="background-color: var(--card-bg) !important;">
                    <div class="card-body p-3" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="pk-id">#{{ $pickup->id }}</span>
                                @if($hasLaundry)
                                    <div class="pk-laundry-done mt-1">
                                        <i class="bi bi-check2-all"></i> Laundry Created
                                    </div>
                                @endif
                            </div>
                            <span class="badge bg-{{ $color }}">
                                @if($pickup->status==='pending') <i class="bi bi-clock"></i>
                                @elseif($pickup->status==='accepted') <i class="bi bi-check-circle"></i>
                                @elseif($pickup->status==='en_route') <i class="bi bi-truck"></i>
                                @elseif($pickup->status==='picked_up') <i class="bi bi-box-seam"></i>
                                @elseif($pickup->status==='cancelled') <i class="bi bi-x-circle"></i>
                                @endif
                                {{ ucfirst(str_replace('_',' ',$pickup->status)) }}
                            </span>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-person-circle me-2 text-muted"></i>
                                <div>
                                    <div class="pk-customer-name">{{ $pickup->customer->name }}</div>
                                    @if($pickup->phone_number ?? $pickup->contact_phone ?? null)
                                        <small class="text-muted">{{ $pickup->phone_number ?? $pickup->contact_phone }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-2">
                                <i class="bi bi-geo-alt me-2 text-muted mt-1"></i>
                                <div class="flex-grow-1">
                                    <div class="pk-address">{{ Str::limit($pickup->pickup_address, 50) }}</div>
                                    @if($pickup->promotion)
                                        <span class="badge bg-primary mt-1" style="font-size:0.7rem;">
                                            <i class="bi bi-tag-fill me-1"></i>₱{{ $pickup->promotion->display_price }} {{ $pickup->promotion->price_unit ?? 'per load' }}
                                        </span>
                                    @endif
                                    @if($pickup->latitude && $pickup->longitude)
                                        <a href="{{ $pickup->map_url }}" target="_blank" class="small text-decoration-none">
                                            <i class="bi bi-map me-1"></i>View Map
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-calendar-event me-2 text-muted"></i>
                                <div>
                                    <span style="font-weight:600;font-size:.82rem;">{{ \Carbon\Carbon::parse($pickup->preferred_date)->format('M d, Y') }}</span>
                                    @if($pickup->preferred_time ?? $pickup->preferred_time_slot ?? null)
                                        <small class="text-muted ms-1">
                                            {{ $pickup->preferred_time ? date('g:i A', strtotime($pickup->preferred_time)) : ($pickup->preferred_time_slot ?? '') }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <div>
                                @if($pickup->assignedStaff)
                                    <small class="text-muted">Assigned to</small>
                                    <div style="font-size:.8rem;font-weight:600;">{{ $isMyPickup ? 'You' : $pickup->assignedStaff->name }}</div>
                                    @if($adminAccepted)
                                        <small class="badge bg-info bg-opacity-10 text-info"><i class="bi bi-shield-check"></i> Admin</small>
                                    @elseif($isMyPickup)
                                        <small class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-person-check"></i> You</small>
                                    @endif
                                @else
                                    <small class="text-muted">Unassigned</small>
                                @endif
                                <small class="text-muted d-block">{{ $pickup->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('branch.pickups.show', $pickup->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($pickup->status === 'pending')
                                <form action="{{ route('branch.pickups.accept', $pickup->id) }}" method="POST" class="flex-fill" onsubmit="return confirm('Accept this pickup request?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                        <i class="bi bi-check-circle"></i> Accept
                                    </button>
                                </form>
                            @endif
                            @if($pickup->status === 'accepted' && $isMyPickup)
                                <form action="{{ route('branch.pickups.en-route', $pickup->id) }}" method="POST" class="flex-fill">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary w-100">
                                        <i class="bi bi-truck"></i> En Route
                                    </button>
                                </form>
                            @endif
                            @if($pickup->status === 'en_route' && $isMyPickup)
                                <form action="{{ route('branch.pickups.picked-up', $pickup->id) }}" method="POST" class="flex-fill">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                        <i class="bi bi-box-seam"></i> Picked Up
                                    </button>
                                </form>
                            @endif
                            @if($pickup->status === 'picked_up' && !$hasLaundry)
                                <a href="{{ route('branch.laundries.create', ['pickup_id' => $pickup->id]) }}" class="btn btn-sm btn-success flex-fill">
                                    <i class="bi bi-plus-circle"></i> Create Laundry
                                </a>
                            @endif
                            @if($tab === 'laundry' && $hasLaundry)
                                <a href="{{ route('branch.laundries.show', $pickup->laundry->id) }}" class="btn btn-sm btn-success flex-fill">
                                    <i class="bi bi-basket"></i> View Laundry
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="card border-0 shadow-sm rounded-4 mt-3" style="background-color: var(--card-bg) !important;">
            <div class="card-body p-3" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
                {{ $pickups->appends(request()->query())->links() }}
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg) !important;">
            <div class="card-body p-5" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
                <div class="pk-empty">
                    <div><i class="bi bi-inbox"></i></div>
                    <p>No pickup requests found</p>
                </div>
            </div>
        </div>
    @endif
    </div>{{-- End gridViewContainer --}}

</div>
@endsection

@push('scripts')
<!-- Leaflet JS - Local Assets -->
<script src="{{ asset('assets/leaflet/leaflet.js') }}"></script>
<script src="{{ asset('assets/leaflet/leaflet.markercluster.js') }}"></script>
<script>
let pickupMap = null;
let pickupMarkers = [];
let pickupCluster = null;
let routeLayer = null;
let startMarker = null;
let endMarker = null;
let currentView = 'grid';
let selectedPickups = new Set();

// Pickup data from backend
const pickupsData = {!! json_encode($pickups->map(function($pickup) {
    return [
        'id' => $pickup->id,
        'customer_name' => optional($pickup->customer)->name ?? 'Unknown',
        'phone' => $pickup->phone_number ?? $pickup->contact_phone ?? 'N/A',
        'address' => $pickup->pickup_address ?? 'No address',
        'latitude' => $pickup->latitude ?? 0,
        'longitude' => $pickup->longitude ?? 0,
        'status' => $pickup->status,
        'preferred_date' => $pickup->preferred_date ? $pickup->preferred_date->format('M d, Y') : 'N/A',
        'preferred_time' => $pickup->preferred_time ? date('g:i A', strtotime($pickup->preferred_time)) : 'N/A',
        'created_at' => $pickup->created_at ? $pickup->created_at->diffForHumans() : 'N/A',
        'assigned_to' => optional($pickup->assignedStaff)->name ?? 'Unassigned',
        'has_laundry' => $pickup->laundry !== null,
    ];
})->values()) !!};

// Branch coordinates (default: Dumaguete City)
const branchCoords = [9.3068, 123.3054];

// Switch between grid and map view
function switchView(view) {
    currentView = view;

    if (view === 'map') {
        document.getElementById('gridViewContainer').style.display = 'none';
        document.getElementById('mapViewContainer').style.display = 'block';
        document.getElementById('gridViewBtn').classList.remove('active');
        document.getElementById('mapViewBtn').classList.add('active');
        document.getElementById('mapControls').style.display = 'flex';

        if (!pickupMap) {
            initMap();
        } else {
            pickupMap.invalidateSize();
        }
    } else {
        document.getElementById('gridViewContainer').style.display = 'block';
        document.getElementById('mapViewContainer').style.display = 'none';
        document.getElementById('gridViewBtn').classList.add('active');
        document.getElementById('mapViewBtn').classList.remove('active');
        document.getElementById('mapControls').style.display = 'none';
    }
}

// Initialize map
function initMap() {
    if (pickupMap) return;

    pickupMap = L.map('pickupMap').setView(branchCoords, 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(pickupMap);

    // Initialize marker cluster
    pickupCluster = L.markerClusterGroup({
        chunkedLoading: true,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true
    });
    pickupMap.addLayer(pickupCluster);

    // Add branch marker
    const branchIcon = L.divIcon({
        className: 'pickup-marker',
        html: '<div class="pickup-marker" style="background: #3D3B6B; width: 44px; height: 44px; border: 4px solid white;"><i class="bi bi-house-fill" style="color: white; font-size: 18px;"></i></div>',
        iconSize: [44, 44],
        iconAnchor: [22, 44]
    });

    L.marker(branchCoords, { icon: branchIcon })
        .addTo(pickupMap)
        .bindPopup('<div class="popup-content"><div class="popup-header"><i class="bi bi-house-fill me-2"></i>Branch Location</div></div>');

    // Add pickup markers
    renderPickupMarkers();

    // Fit bounds to show all markers
    setTimeout(() => fitAllPickups(), 300);
}

// Render pickup markers
function renderPickupMarkers() {
    // Clear existing markers
    if (pickupCluster) {
        pickupCluster.clearLayers();
    }
    pickupMarkers = [];

    pickupsData.forEach(pickup => {
        if (!pickup.latitude || !pickup.longitude) return;

        const statusColors = {
            'pending': '#fbbf24',
            'accepted': '#60a5fa',
            'en_route': '#3b82f6',
            'picked_up': '#10b981',
            'cancelled': '#ef4444'
        };

        const color = statusColors[pickup.status] || '#6b7280';
        const statusIcons = {
            'pending': 'clock',
            'accepted': 'check-circle',
            'en_route': 'truck',
            'picked_up': 'box-seam',
            'cancelled': 'x-circle'
        };
        const icon = statusIcons[pickup.status] || 'geo-alt';

        const isSelected = selectedPickups.has(pickup.id);
        const markerIcon = L.divIcon({
            className: 'pickup-marker',
            html: `<div class="pickup-marker marker-${pickup.status}" style="${isSelected ? 'border: 4px solid #0d6efd;' : ''}"><i class="bi bi-${icon}"></i></div>`,
            iconSize: [36, 36],
            iconAnchor: [18, 36]
        });

        const popupContent = `
            <div class="popup-content">
                <div class="popup-header">
                    <i class="bi bi-person-circle me-2"></i>${pickup.customer_name}
                    <span class="badge bg-${pickup.status === 'pending' ? 'warning' : pickup.status === 'accepted' ? 'info' : pickup.status === 'en_route' ? 'primary' : 'success'} ms-2" style="font-size: 10px;">
                        ${pickup.status.replace('_', ' ').toUpperCase()}
                    </span>
                </div>
                <div class="popup-info">
                    <i class="bi bi-telephone"></i>
                    <span>${pickup.phone}</span>
                </div>
                <div class="popup-info">
                    <i class="bi bi-geo-alt"></i>
                    <span>${pickup.address.substring(0, 60)}${pickup.address.length > 60 ? '...' : ''}</span>
                </div>
                <div class="popup-info">
                    <i class="bi bi-calendar"></i>
                    <span>${pickup.preferred_date} at ${pickup.preferred_time}</span>
                </div>
                <div class="popup-info">
                    <i class="bi bi-person-badge"></i>
                    <span>${pickup.assigned_to}</span>
                </div>
                <div class="popup-actions">
                    <a href="/branch/pickups/${pickup.id}" class="btn btn-sm btn-primary" style="font-size: 11px;">
                        <i class="bi bi-eye"></i> View
                    </a>
                    <button onclick="calculateRoute(${pickup.latitude}, ${pickup.longitude}, ${pickup.id})" class="btn btn-sm btn-outline-primary" style="font-size: 11px;">
                        <i class="bi bi-arrow-right-circle"></i> Route
                    </button>
                    <button onclick="togglePickupSelection(${pickup.id})" class="btn btn-sm ${isSelected ? 'btn-success' : 'btn-outline-secondary'}" style="font-size: 11px;">
                        <i class="bi bi-${isSelected ? 'check-square' : 'square'}"></i>
                    </button>
                </div>
            </div>
        `;

        const marker = L.marker([pickup.latitude, pickup.longitude], { icon: markerIcon })
            .bindPopup(popupContent, { maxWidth: 300 })
            .on('click', function(e) {
                if (e.originalEvent.ctrlKey || e.originalEvent.metaKey) {
                    togglePickupSelection(pickup.id);
                }
            });

        // Add to cluster instead of directly to map
        pickupCluster.addLayer(marker);
        pickupMarkers.push(marker);
    });
}

// Toggle pickup selection
function togglePickupSelection(pickupId) {
    if (selectedPickups.has(pickupId)) {
        selectedPickups.delete(pickupId);
    } else {
        selectedPickups.add(pickupId);
    }
    updateSelectionUI();
    renderPickupMarkers();
}

// Select all pending pickups
function selectAllPending() {
    pickupsData.filter(p => p.status === 'pending').forEach(p => selectedPickups.add(p.id));
    updateSelectionUI();
    renderPickupMarkers();
}

// Clear selections
function clearSelections() {
    selectedPickups.clear();
    updateSelectionUI();
    renderPickupMarkers();
}

// Update selection UI
function updateSelectionUI() {
    const count = selectedPickups.size;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('multiRouteBtn').style.display = count >= 2 ? 'inline-block' : 'none';
}

// Get optimized multi-route
async function getOptimizedMultiRoute() {
    if (selectedPickups.size < 2) {
        alert('Please select at least 2 pickups for route optimization');
        return;
    }

    try {
        const btn = document.getElementById('multiRouteBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Calculating...';
        btn.disabled = true;

        const selectedData = pickupsData.filter(p => selectedPickups.has(p.id));
        const waypoints = [
            [branchCoords[1], branchCoords[0]],
            ...selectedData.map(p => [p.longitude, p.latitude])
        ];

        const coordString = waypoints.map(w => `${w[0]},${w[1]}`).join(';');
        const url = `https://router.project-osrm.org/route/v1/driving/${coordString}?overview=full&geometries=polyline&steps=true`;

        const response = await fetch(url);
        const data = await response.json();

        if (data.code !== 'Ok' || !data.routes || !data.routes[0]) {
            throw new Error('Route calculation failed');
        }

        const route = data.routes[0];
        drawMultiStopRoute(route, selectedData);
        showMultiRouteSummary(route, selectedData);

        btn.innerHTML = originalText;
        btn.disabled = false;

    } catch (error) {
        console.error('Route optimization error:', error);
        alert('Failed to calculate optimized route: ' + error.message);

        const btn = document.getElementById('multiRouteBtn');
        btn.innerHTML = '<i class="bi bi-route"></i> Optimize (<span id="selectedCount">' + selectedPickups.size + '</span>)';
        btn.disabled = false;
    }
}

// Draw multi-stop route
function drawMultiStopRoute(route, pickups) {
    clearRoute();

    const coordinates = decodePolyline(route.geometry);

    routeLayer = L.polyline(coordinates, {
        color: '#8b5cf6',
        weight: 6,
        opacity: 0.8,
        lineJoin: 'round'
    }).addTo(pickupMap);

    startMarker = L.circleMarker(branchCoords, {
        radius: 10,
        fillColor: '#0d6efd',
        color: '#fff',
        weight: 3,
        fillOpacity: 1
    }).addTo(pickupMap).bindPopup('<b>Branch - Start Point</b>');

    pickups.forEach((pickup, index) => {
        const marker = L.marker([pickup.latitude, pickup.longitude], {
            icon: L.divIcon({
                className: 'stop-marker',
                html: `<div style="background:#ffc107;width:36px;height:36px;border-radius:50%;border:3px solid white;color:#000;font-weight:bold;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.3);">${index + 1}</div>`,
                iconSize: [36, 36],
                iconAnchor: [18, 36]
            })
        }).addTo(pickupMap).bindPopup(`<b>Stop ${index + 1}</b><br>${pickup.customer_name}<br><small>${pickup.address}</small>`);
    });

    pickupMap.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
}

// Show multi-route summary
function showMultiRouteSummary(route, pickups) {
    const distance = (route.distance / 1000).toFixed(2);
    const duration = Math.round(route.duration / 60);

    const panel = document.getElementById('routeDetailsPanel');
    panel.innerHTML = `
        <div class="card border-0 shadow-lg">
            <div class="card-header bg-purple text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-route me-2"></i>Optimized Route</h6>
                <button class="btn btn-sm btn-light" onclick="closeRouteDetails()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Total Distance</small>
                        <h5 class="text-primary">${distance} km</h5>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Total Time</small>
                        <h5 class="text-success">${duration} min</h5>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <small class="text-muted">Route Stops:</small>
                    <ol class="mt-2 ps-3">
                        <li><strong>Start:</strong> Branch Location</li>
                        ${pickups.map((p, i) => `<li><strong>Stop ${i + 1}:</strong> ${p.customer_name}</li>`).join('')}
                    </ol>
                </div>
                <hr>
                <div class="d-grid gap-2">
                    <button class="btn btn-success btn-sm" onclick="startMultiNavigation()">
                        <i class="bi bi-play-circle me-1"></i>Start Navigation
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="printRoute()">
                        <i class="bi bi-printer me-1"></i>Print Route
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="clearRoute()">
                        <i class="bi bi-x-circle me-1"></i>Clear Route
                    </button>
                </div>
            </div>
        </div>
    `;
    panel.style.display = 'block';
}

// Start multi-stop navigation
function startMultiNavigation() {
    const selectedData = pickupsData.filter(p => selectedPickups.has(p.id));
    const origin = `${branchCoords[0]},${branchCoords[1]}`;
    const waypoints = selectedData.map(p => `${p.latitude},${p.longitude}`).join('|');
    const url = `https://www.google.com/maps/dir/?api=1&origin=${origin}&destination=${origin}&waypoints=${waypoints}&travelmode=driving`;
    window.open(url, '_blank');
}

// Decode polyline
function decodePolyline(encoded) {
    const poly = [];
    let index = 0, len = encoded.length;
    let lat = 0, lng = 0;

    while (index < len) {
        let b, shift = 0, result = 0;
        do {
            b = encoded.charCodeAt(index++) - 63;
            result |= (b & 0x1f) << shift;
            shift += 5;
        } while (b >= 0x20);
        const dlat = ((result & 1) ? ~(result >> 1) : (result >> 1));
        lat += dlat;

        shift = 0;
        result = 0;
        do {
            b = encoded.charCodeAt(index++) - 63;
            result |= (b & 0x1f) << shift;
            shift += 5;
        } while (b >= 0x20);
        const dlng = ((result & 1) ? ~(result >> 1) : (result >> 1));
        lng += dlng;

        poly.push([lat / 1e5, lng / 1e5]);
    }
    return poly;
}

// Calculate route from branch to pickup
function calculateRoute(lat, lng, pickupId) {
    // Clear previous route
    clearRoute();

    const url = `https://router.project-osrm.org/route/v1/driving/${branchCoords[1]},${branchCoords[0]};${lng},${lat}?overview=full&geometries=geojson&steps=true`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.code !== 'Ok' || !data.routes || data.routes.length === 0) {
                alert('Could not calculate route');
                return;
            }

            const route = data.routes[0];
            const coords = route.geometry.coordinates.map(c => [c[1], c[0]]);

            // Draw route line
            routeLayer = L.polyline(coords, {
                color: '#3D3B6B',
                weight: 6,
                opacity: 0.8,
                lineJoin: 'round'
            }).addTo(pickupMap);

            // Add start marker (branch)
            startMarker = L.circleMarker(branchCoords, {
                radius: 10,
                fillColor: '#0EA5E9',
                color: '#fff',
                weight: 3,
                fillOpacity: 1
            }).addTo(pickupMap).bindPopup('<b>Branch Location</b>');

            // Add end marker (pickup)
            endMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: 'end-marker',
                    html: '<div style="background:#10B981;width:36px;height:36px;border-radius:50%;border:3px solid white;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.3);"><i class="bi bi-geo-alt-fill" style="color:white;font-size:16px;"></i></div>',
                    iconSize: [36, 36],
                    iconAnchor: [18, 36]
                })
            }).addTo(pickupMap).bindPopup('<b>Pickup Location</b>');

            // Fit map to route
            pickupMap.fitBounds(routeLayer.getBounds().pad(0.1));

            // Calculate distance and duration
            const distance = (route.distance / 1000).toFixed(2);
            const duration = Math.ceil(route.duration / 60);

            // Show route details panel
            showRouteDetails(distance, duration, route.legs[0].steps);
        })
        .catch(err => {
            console.error('Route calculation error:', err);
            alert('Failed to calculate route');
        });
}

// Show route details panel
function showRouteDetails(distance, duration, steps) {
    const panel = document.getElementById('routeDetailsPanel');
    if (!panel) return;

    const stepsHtml = steps.slice(0, 5).map((step, idx) => {
        const instruction = step.maneuver.instruction || 'Continue';
        const stepDistance = (step.distance / 1000).toFixed(1);
        return `<li class="small"><strong>${idx + 1}.</strong> ${instruction} (${stepDistance} km)</li>`;
    }).join('');

    panel.innerHTML = `
        <div class="card border-0 shadow-lg">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-signpost me-2"></i>Route Details</h6>
                <button class="btn btn-sm btn-light" onclick="closeRouteDetails()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Distance</small>
                        <h5 class="text-primary mb-0">${distance} km</h5>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Duration</small>
                        <h5 class="text-success mb-0">${duration} min</h5>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <small class="text-muted fw-bold">Turn-by-Turn Directions:</small>
                    <ol class="mt-2 ps-3 mb-0">
                        ${stepsHtml}
                        ${steps.length > 5 ? `<li class="small text-muted">... and ${steps.length - 5} more steps</li>` : ''}
                    </ol>
                </div>
                <hr>
                <div class="d-grid gap-2">
                    <button class="btn btn-success btn-sm" onclick="startNavigation()">
                        <i class="bi bi-play-circle me-1"></i> Start Navigation
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="printRoute()">
                        <i class="bi bi-printer me-1"></i> Print Directions
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="clearRoute()">
                        <i class="bi bi-x-circle me-1"></i> Clear Route
                    </button>
                </div>
            </div>
        </div>
    `;

    panel.style.display = 'block';
}

// Close route details panel
function closeRouteDetails() {
    const panel = document.getElementById('routeDetailsPanel');
    if (panel) {
        panel.style.display = 'none';
    }
}

// Clear route from map
function clearRoute() {
    if (routeLayer && pickupMap) {
        pickupMap.removeLayer(routeLayer);
        routeLayer = null;
    }
    if (startMarker && pickupMap) {
        pickupMap.removeLayer(startMarker);
        startMarker = null;
    }
    if (endMarker && pickupMap) {
        pickupMap.removeLayer(endMarker);
        endMarker = null;
    }
    closeRouteDetails();
}

// Start navigation (opens in Google Maps)
function startNavigation() {
    if (!endMarker) {
        alert('No route selected');
        return;
    }

    const latLng = endMarker.getLatLng();
    const url = `https://www.google.com/maps/dir/?api=1&origin=${branchCoords[0]},${branchCoords[1]}&destination=${latLng.lat},${latLng.lng}&travelmode=driving`;
    window.open(url, '_blank');
}

// Print route
function printRoute() {
    window.print();
}

// Map control functions
function centerMapOnBranch() {
    if (pickupMap) {
        pickupMap.flyTo(branchCoords, 15, { duration: 1 });
    }
}

function fitAllPickups() {
    if (!pickupMap) return;

    // If using cluster, fit to cluster bounds
    if (pickupCluster && pickupCluster.getLayers().length > 0) {
        const bounds = pickupCluster.getBounds();
        if (bounds.isValid()) {
            pickupMap.fitBounds(bounds.pad(0.1));
            return;
        }
    }

    // Fallback: fit to all markers
    if (pickupMarkers.length === 0) return;
    const group = L.featureGroup(pickupMarkers);
    pickupMap.fitBounds(group.getBounds().pad(0.1));
}

function refreshMap() {
    if (pickupMap) {
        pickupMap.invalidateSize();
        renderPickupMarkers();
        fitAllPickups();
    }
}

// Auto-refresh for pending pickups
@if(request('status') == 'pending' || !request('status'))
    setInterval(function() {
        if (document.visibilityState === 'visible' && currentView === 'grid') {
            window.location.reload();
        }
    }, 30000);
@endif
</script>
@endpush
