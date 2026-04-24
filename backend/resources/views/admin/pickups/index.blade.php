@extends('admin.layouts.app')

@section('page-title', 'Pickup Requests')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pickups.css') }}">
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
        @php
            $statsMeta = [
                ['label'=>'Pending',   'status'=>'pending',   'icon'=>'bi-clock-history',  'color'=>'warning'],
                ['label'=>'Accepted',  'status'=>'accepted',  'icon'=>'bi-check-circle',   'color'=>'info'],
                ['label'=>'En Route',  'status'=>'en_route',  'icon'=>'bi-truck',           'color'=>'primary'],
                ['label'=>'Picked Up', 'status'=>'picked_up', 'icon'=>'bi-box-seam',        'color'=>'success'],
            ];
        @endphp
        @foreach($statsMeta as $s)
        <div class="pk-stat-card" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
            <div class="pk-stat-icon {{ $s['color'] }}">
                <i class="bi {{ $s['icon'] }}"></i>
            </div>
            <div>
                <div class="pk-stat-label" style="color: var(--text-secondary) !important;">{{ $s['label'] }}</div>
                <div class="pk-stat-value" style="color: var(--text-primary) !important;">{{ $pickups->where('status', $s['status'])->count() }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Filters ─────────────────────────────────────────────── --}}
    <div class="pk-filter-card" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
        <form method="GET" action="{{ route('admin.pickups.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="all"      {{ request('status','all')=='all'      ?'selected':'' }}>All Status</option>
                    <option value="pending"  {{ request('status')=='pending'  ?'selected':'' }}>Pending</option>
                    <option value="accepted" {{ request('status')=='accepted' ?'selected':'' }}>Accepted</option>
                    <option value="en_route" {{ request('status')=='en_route' ?'selected':'' }}>En Route</option>
                    <option value="picked_up"{{ request('status')=='picked_up'?'selected':'' }}>Picked Up</option>
                    <option value="cancelled"{{ request('status')=='cancelled'?'selected':'' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Branch</label>
                <select name="branch_id" class="form-select">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id')==$branch->id?'selected':'' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name or address" value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill" style="border-radius:8px;">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <a href="{{ route('admin.pickups.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;" title="Clear">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- ── Pickup Cards Grid ────────────────────────────────────── --}}
    @if($pickups->count() > 0)
        <div class="row g-3" style="background-color: transparent !important;">
            @foreach($pickups as $pickup)
            @php
                $hasLaundry = $pickup->laundry !== null;
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
                                    @if($pickup->contact_phone ?? $pickup->phone_number)
                                        <small class="text-muted">{{ $pickup->contact_phone ?? $pickup->phone_number }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-2">
                                <i class="bi bi-geo-alt me-2 text-muted mt-1"></i>
                                <div class="flex-grow-1">
                                    <div class="pk-address">{{ Str::limit($pickup->pickup_address, 50) }}</div>
                                    @if($pickup->landmark ?? null)
                                        <small class="text-muted"><i class="bi bi-geo me-1"></i>{{ $pickup->landmark }}</small>
                                    @endif
                                    @if($pickup->latitude && $pickup->longitude)
                                        <a href="{{ $pickup->map_url }}" target="_blank" class="small text-decoration-none">
                                            <i class="bi bi-map me-1"></i>View Map
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-shop me-2 text-muted"></i>
                                <span class="badge bg-secondary">{{ $pickup->branch->name }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-calendar-event me-2 text-muted"></i>
                                <div>
                                    <span style="font-weight:600;font-size:.82rem;">{{ $pickup->preferred_date->format('M d, Y') }}</span>
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
                                    <div style="font-size:.8rem;font-weight:600;">{{ $pickup->assignedStaff->name }}</div>
                                @else
                                    <small class="text-muted">Unassigned</small>
                                @endif
                                <small class="text-muted d-block">{{ $pickup->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="text-end">
                                <div class="pk-fee-total">₱{{ number_format($pickup->total_fee, 2) }}</div>
                                <small class="text-muted">P:₱{{ number_format($pickup->pickup_fee, 0) }} | D:₱{{ number_format($pickup->delivery_fee, 0) }}</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('admin.pickups.show', $pickup->id) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                <i class="bi bi-eye"></i> View
                            </a>
                            @if($pickup->status === 'picked_up')
                                @if(!$hasLaundry)
                                    <a href="{{ route('admin.laundries.create', ['pickup_id' => $pickup->id]) }}" class="btn btn-sm btn-success flex-fill">
                                        <i class="bi bi-plus-circle"></i> Create Laundry
                                    </a>
                                @else
                                    <a href="{{ route('admin.laundries.show', $pickup->laundry->id) }}" class="btn btn-sm btn-info flex-fill">
                                        <i class="bi bi-box-seam"></i> View Laundry
                                    </a>
                                @endif
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

</div>
@endsection

@push('scripts')
<script>
@if(request('status') == 'pending' || !request('status'))
    setInterval(function() {
        if (document.visibilityState === 'visible') window.location.reload();
    }, 30000);
@endif
</script>
@endpush
