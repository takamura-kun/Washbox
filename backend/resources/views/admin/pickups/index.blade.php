@extends('admin.layouts.app')

@section('page-title', 'Pickup Requests')

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
        @php
            $statsMeta = [
                ['label'=>'Pending',   'status'=>'pending',   'icon'=>'bi-clock-history',  'color'=>'warning'],
                ['label'=>'Accepted',  'status'=>'accepted',  'icon'=>'bi-check-circle',   'color'=>'info'],
                ['label'=>'En Route',  'status'=>'en_route',  'icon'=>'bi-truck',           'color'=>'primary'],
                ['label'=>'Picked Up', 'status'=>'picked_up', 'icon'=>'bi-box-seam',        'color'=>'success'],
            ];
        @endphp
        @foreach($statsMeta as $s)
        <div class="pk-stat-card">
            <div class="pk-stat-icon {{ $s['color'] }}">
                <i class="bi {{ $s['icon'] }}"></i>
            </div>
            <div>
                <div class="pk-stat-label">{{ $s['label'] }}</div>
                <div class="pk-stat-value">{{ $pickups->where('status', $s['status'])->count() }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Filters ─────────────────────────────────────────────── --}}
    <div class="pk-filter-card">
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

    {{-- ── Table ────────────────────────────────────────────────── --}}
    <div class="pk-table-card">
        @if($pickups->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Branch</th>
                        <th>Pickup Address</th>
                        <th>Preferred Date</th>
                        <th>Fees</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pickups as $pickup)
                    @php
                        $hasLaundry = $pickup->laundry !== null;
                    @endphp
                    <tr>
                        <td><span class="pk-id">#{{ $pickup->id }}</span></td>

                        <td>
                            <div class="pk-customer-name">{{ $pickup->customer->name }}</div>
                            @if($pickup->contact_phone ?? $pickup->phone_number)
                                <div class="pk-customer-phone">
                                    <i class="bi bi-telephone me-1"></i>{{ $pickup->contact_phone ?? $pickup->phone_number }}
                                </div>
                            @endif
                        </td>

                        <td>
                            <span style="font-size:.82rem;font-weight:600;">{{ $pickup->branch->name }}</span>
                        </td>

                        <td>
                            <div class="pk-address">{{ Str::limit($pickup->pickup_address, 40) }}</div>
                            @if($pickup->landmark ?? null)
                                <div class="pk-landmark"><i class="bi bi-geo me-1"></i>{{ $pickup->landmark }}</div>
                            @endif
                            @if($pickup->latitude && $pickup->longitude)
                                <a href="{{ $pickup->map_url }}" target="_blank"
                                   class="pk-landmark text-decoration-none">
                                    <i class="bi bi-geo-alt me-1"></i>Map
                                </a>
                            @endif
                        </td>

                        <td>
                            <div style="font-weight:600;font-size:.82rem;">
                                {{ $pickup->preferred_date->format('M d, Y') }}
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
                            <div class="pk-fee-total">₱{{ number_format($pickup->total_fee, 2) }}</div>
                            <div class="pk-fee-breakdown">
                                P:₱{{ number_format($pickup->pickup_fee, 0) }}
                                | D:₱{{ number_format($pickup->delivery_fee, 0) }}
                            </div>
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
                            @if($hasLaundry)
                                <div class="pk-laundry-done mt-1">
                                    <i class="bi bi-check2-all"></i> Laundry Created
                                </div>
                            @endif
                        </td>

                        <td>
                            @if($pickup->assignedStaff)
                                <div style="font-size:.8rem;font-weight:600;">{{ $pickup->assignedStaff->name }}</div>
                                <div class="pk-admin-badge">
                                    <i class="bi bi-person-badge"></i> Staff
                                </div>
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
                                <a href="{{ route('admin.pickups.show', $pickup->id) }}"
                                   class="pk-btn pk-btn-view" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>

                                {{--
                                    Create Laundry / View Laundry
                                    BUG FIX: was checking !$pickup->laundries_id (typo, always null).
                                    Now uses the eager-loaded `laundry` relationship:
                                    - Show "Create Laundry" only when picked_up AND no laundry exists yet
                                    - Show "View Laundry" when laundry has been created
                                --}}
                                @if($pickup->status === 'picked_up')
                                    @if(!$hasLaundry)
                                        <a href="{{ route('admin.laundries.create', ['pickup_id' => $pickup->id]) }}"
                                           class="pk-btn pk-btn-create">
                                            <i class="bi bi-plus-circle"></i> Create Laundry
                                        </a>
                                    @else
                                        <a href="{{ route('admin.laundries.show', $pickup->laundry->id) }}"
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
