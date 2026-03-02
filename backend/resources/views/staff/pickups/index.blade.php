@extends('staff.layouts.staff')

@section('title', 'Pickup Requests')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pickups.css') }}">
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
    <div class="pk-stats-grid">
        <div class="pk-stat-card">
            <div class="pk-stat-icon warning"><i class="bi bi-clock-history"></i></div>
            <div>
                <div class="pk-stat-label">Pending</div>
                <div class="pk-stat-value">{{ $stats['pending'] ?? 0 }}</div>
            </div>
        </div>
        <div class="pk-stat-card">
            <div class="pk-stat-icon info"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="pk-stat-label">Accepted</div>
                <div class="pk-stat-value">{{ $stats['accepted'] ?? 0 }}</div>
            </div>
        </div>
        <div class="pk-stat-card">
            <div class="pk-stat-icon primary"><i class="bi bi-truck"></i></div>
            <div>
                <div class="pk-stat-label">En Route</div>
                <div class="pk-stat-value">{{ $stats['en_route'] ?? 0 }}</div>
            </div>
        </div>
        <div class="pk-stat-card">
            <div class="pk-stat-icon success"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="pk-stat-label">Picked Up</div>
                <div class="pk-stat-value">{{ $stats['picked_up'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    {{-- ── Filters ─────────────────────────────────────────────── --}}
    <div class="pk-filter-card">
        <form method="GET" action="{{ route('staff.pickups.index') }}" class="row g-3 align-items-end">
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
                <a href="{{ route('staff.pickups.index') }}"
                   class="btn btn-outline-secondary" style="border-radius:8px;" title="Clear">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- ── Table ────────────────────────────────────────────────── --}}
    <div class="pk-table-card">
        @if($pickups->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Pickup Address</th>
                        <th>Preferred Date</th>
                        <th>Status</th>
                        <th>Assigned</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pickups as $pickup)
                    @php
                        $hasLaundry   = $pickup->laundry !== null;
                        $isMyPickup   = $pickup->assigned_to === auth()->id();
                        $adminAccepted = $pickup->isAccepted() && $pickup->assignedStaff && !$isMyPickup;
                    @endphp
                    <tr>
                        <td><span class="pk-id">#{{ $pickup->id }}</span></td>

                        <td>
                            <div class="pk-customer-name">{{ $pickup->customer->name }}</div>
                            @if($pickup->phone_number ?? $pickup->contact_phone ?? null)
                                <div class="pk-customer-phone">
                                    <i class="bi bi-telephone me-1"></i>{{ $pickup->phone_number ?? $pickup->contact_phone }}
                                </div>
                            @endif
                        </td>

                        <td>
                            <div class="pk-address">{{ Str::limit($pickup->pickup_address, 40) }}</div>
                            @if($pickup->latitude && $pickup->longitude)
                                <a href="{{ $pickup->map_url }}" target="_blank"
                                   class="pk-landmark text-decoration-none">
                                    <i class="bi bi-geo-alt me-1"></i>Map
                                </a>
                            @endif
                        </td>

                        <td>
                            <div style="font-weight:600;font-size:.82rem;">
                                {{ \Carbon\Carbon::parse($pickup->preferred_date)->format('M d, Y') }}
                            </div>
                            @if($pickup->preferred_time ?? $pickup->preferred_time_slot ?? null)
                                <div class="pk-timestamp">
                                    {{ $pickup->preferred_time
                                        ? date('g:i A', strtotime($pickup->preferred_time))
                                        : ($pickup->preferred_time_slot ?? '') }}
                                </div>
                            @endif
                        </td>

                        <td>
                            <span class="pk-badge {{ $pickup->status }}">
                                @if($pickup->status==='pending')   <i class="bi bi-clock"></i>
                                @elseif($pickup->status==='accepted')  <i class="bi bi-check-circle"></i>
                                @elseif($pickup->status==='en_route')  <i class="bi bi-truck"></i>
                                @elseif($pickup->status==='picked_up') <i class="bi bi-box-seam"></i>
                                @elseif($pickup->status==='cancelled') <i class="bi bi-x-circle"></i>
                                @endif
                                {{ ucfirst(str_replace('_',' ',$pickup->status)) }}
                            </span>

                            {{-- Laundry created indicator --}}
                            @if($hasLaundry)
                                <div class="pk-laundry-done mt-1">
                                    <i class="bi bi-check2-all"></i> Laundry Created
                                </div>
                            @endif
                        </td>

                        <td>
                            @if($pickup->assignedStaff)
                                <div style="font-size:.8rem;font-weight:600;">
                                    {{ $isMyPickup ? 'You' : $pickup->assignedStaff->name }}
                                </div>
                                {{--
                                    Show an "Admin Accepted" badge when the pickup
                                    was accepted/assigned by an admin user, so staff
                                    knows it didn't come in blank.
                                --}}
                                @if($adminAccepted)
                                    <div class="pk-admin-badge">
                                        <i class="bi bi-shield-check"></i> Admin Assigned
                                    </div>
                                @elseif($isMyPickup)
                                    <div class="pk-admin-badge" style="background:rgba(34,197,94,0.10);color:#15803d;">
                                        <i class="bi bi-person-check"></i> Assigned to You
                                    </div>
                                @endif
                            @else
                                <span class="pk-timestamp">Unassigned</span>
                            @endif
                        </td>

                        <td>
                            <div class="pk-timestamp" title="{{ $pickup->created_at->format('Y-m-d H:i:s') }}">
                                {{ $pickup->created_at->diffForHumans() }}
                            </div>
                        </td>

                        <td>
                            <div class="pk-actions">

                                {{-- View --}}
                                <a href="{{ route('staff.pickups.show', $pickup->id) }}"
                                   class="pk-btn pk-btn-view" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>

                                {{-- Accept (pending only) --}}
                                @if($pickup->status === 'pending')
                                    <form action="{{ route('staff.pickups.accept', $pickup->id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Accept this pickup request?')">
                                        @csrf
                                        <button type="submit" class="pk-btn pk-btn-accept">
                                            <i class="bi bi-check-circle"></i> Accept
                                        </button>
                                    </form>
                                @endif

                                {{-- En Route (accepted + assigned to me) --}}
                                @if($pickup->status === 'accepted' && $isMyPickup)
                                    <form action="{{ route('staff.pickups.en-route', $pickup->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="pk-btn pk-btn-enroute">
                                            <i class="bi bi-truck"></i> En Route
                                        </button>
                                    </form>
                                @endif

                                {{-- Picked Up (en_route + assigned to me) --}}
                                @if($pickup->status === 'en_route' && $isMyPickup)
                                    <form action="{{ route('staff.pickups.picked-up', $pickup->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="pk-btn pk-btn-pickedup">
                                            <i class="bi bi-box-seam"></i> Picked Up
                                        </button>
                                    </form>
                                @endif

                                {{--
                                    Create Laundry / View Laundry
                                    BUG FIX: the original check was `!$pickup->laundries_id`
                                    which is a typo — always null, always true.
                                    Now uses the eager-loaded `laundry` relationship.
                                    - Show "Create Laundry" only if picked_up AND no laundry yet
                                    - Show "View Laundry" once a laundry record exists
                                --}}
                                @if($pickup->status === 'picked_up')
                                    @if(!$hasLaundry)
                                        <a href="{{ route('staff.laundries.create', ['pickup_id' => $pickup->id]) }}"
                                           class="pk-btn pk-btn-create">
                                            <i class="bi bi-plus-circle"></i> Create Laundry
                                        </a>
                                    @else
                                        <a href="{{ route('staff.laundries.show', $pickup->laundry->id) }}"
                                           class="pk-btn pk-btn-view-laundry">
                                            <i class="bi bi-box-seam"></i> View Laundry
                                        </a>
                                    @endif
                                @endif

                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $pickups->appends(request()->query())->links() }}
        </div>
        @else
        <div class="pk-empty">
            <div><i class="bi bi-inbox"></i></div>
            <p>No pickup requests found</p>
        </div>
        @endif
    </div>

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
