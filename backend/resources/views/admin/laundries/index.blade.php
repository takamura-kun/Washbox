@extends('admin.layouts.app')

@section('page-title', 'LAUNDRY MANAGEMENT')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/laundry.css') }}">
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted small mb-0">Track and manage all laundry</p>
        </div>
        {{-- BUG FIX: replaced inline style="background:#3D3B6B" with btn-primary (dark mode handles it) --}}
        <a href="{{ route('admin.laundries.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-2"></i>Create New Laundry
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Overview --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 text-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                        <i class="bi bi-box-seam fs-4 text-primary"></i>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $stats['total'] }}</h4>
                    <small class="text-muted">Total Laundries</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 text-center">
                    <div class="bg-warning bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                        <i class="bi bi-clock fs-4 text-warning"></i>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $stats['pending'] }}</h4>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 text-center">
                    <div class="bg-info bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                        <i class="bi bi-arrow-repeat fs-4 text-info"></i>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $stats['processing'] }}</h4>
                    <small class="text-muted">Processing</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 text-center">
                    <div class="bg-success bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                        <i class="bi bi-check-circle fs-4 text-success"></i>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $stats['ready'] }}</h4>
                    <small class="text-muted">Ready</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 text-center">
                    <div class="bg-dark bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                        <i class="bi bi-check-all fs-4"></i>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $stats['completed'] }}</h4>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-success border-4">
                <div class="card-body p-3 text-center">
                    <div class="bg-success bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                        <i class="bi bi-cash-stack fs-4 text-success"></i>
                    </div>
                    <h5 class="fw-bold mb-0 small">₱{{ number_format($stats['total_revenue'], 0) }}</h5>
                    <small class="text-muted">Revenue</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Search..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        @foreach(['received','processing','ready','paid','completed','cancelled'] as $st)
                            <option value="{{ $st }}" {{ request('status')===$st?'selected':'' }}>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id')==$branch->id?'selected':'' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="service_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Services</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ request('service_id')==$service->id?'selected':'' }}>{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control form-control-sm"
                        value="{{ request('date_from') }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.laundries.index') }}" class="btn btn-sm btn-light border w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Laundries List --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            @forelse($laundries as $laundry)
            @php
                $statusColors = [
                    'completed'  => 'success',
                    'ready'      => 'info',
                    'processing' => 'warning',
                    'paid'       => 'primary',
                    'cancelled'  => 'danger',
                    'received'   => 'secondary',
                    'pending'    => 'warning',
                ];
                $color = $statusColors[$laundry->status] ?? 'secondary';
            @endphp
            <div class="d-flex align-items-center px-4 py-3 border-bottom laundry-row">
                {{-- Tracking + pickup link --}}
                <div style="min-width:175px;">
                    <a href="{{ route('admin.laundries.show', $laundry) }}" class="tracking-number fw-bold">
                        {{ $laundry->tracking_number }}
                    </a>
                    @if($laundry->pickupRequest)
                        <div class="text-muted small mt-1">
                            <i class="bi bi-truck me-1"></i>Pickup #{{ $laundry->pickupRequest->id }}
                        </div>
                    @endif
                </div>

                {{-- Customer --}}
                <div style="min-width:140px;" class="me-3">
                    <div class="fw-semibold">{{ $laundry->customer->name ?? 'N/A' }}</div>
                    <div class="text-muted small">{{ $laundry->customer->phone ?? '' }}</div>
                </div>

                {{-- Branch badge --}}
                <div style="min-width:130px;" class="me-3">
                    <span class="badge bg-secondary">{{ $laundry->branch->name ?? 'N/A' }}</span>
                </div>

                {{-- Loads / pieces --}}
                <div style="min-width:90px;" class="me-3 text-muted small">
                    @if($laundry->weight > 0)
                        <div>{{ number_format($laundry->weight, 2) }} kg</div>
                    @endif
                    @if($laundry->number_of_loads)
                        {{ $laundry->number_of_loads }}
                        {{ ($laundry->service?->pricing_type === 'per_piece') ? 'pc(s)' : 'load(s)' }}
                    @endif
                    @if(!$laundry->weight && !$laundry->number_of_loads) — @endif
                </div>

                {{-- Amount --}}
                <div style="min-width:110px;" class="me-3">
                    <strong class="amount">₱{{ number_format($laundry->total_amount, 2) }}</strong>
                    @if($laundry->addons_total > 0)
                        <div class="text-success small">+₱{{ number_format($laundry->addons_total, 2) }} add-ons</div>
                    @endif
                    @if($laundry->promotion)
                        <div class="text-info small"><i class="bi bi-tag me-1"></i>{{ Str::limit($laundry->promotion->name,15) }}</div>
                    @endif
                </div>

                {{-- Status --}}
                <div style="min-width:110px;" class="me-3">
                    <span class="badge bg-{{ $color }}">{{ ucfirst($laundry->status) }}</span>
                </div>

                {{-- Date --}}
                <div class="text-muted small me-3" style="min-width:90px;">
                    {{ $laundry->created_at->format('M d, Y') }}<br>
                    {{ $laundry->created_at->format('h:i A') }}
                </div>

                {{-- Actions --}}
                <div class="ms-auto">
                    <a href="{{ route('admin.laundries.show', $laundry) }}" class="action-btn view" title="View">
                        <i class="bi bi-eye"></i>
                    </a>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div><i class="bi bi-inbox"></i></div>
                <p>
                    @if(request()->anyFilled(['search','status','branch_id','service_id','date_from']))
                        No laundries match your filters<br>
                        <a href="{{ route('admin.laundries.index') }}" class="btn btn-link">Clear all filters</a>
                    @else
                        No laundries found
                    @endif
                </p>
                <a href="{{ route('admin.laundries.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>Create First Laundry
                </a>
            </div>
            @endforelse
        </div>

        @if($laundries->hasPages())
        <div class="card-footer border-top">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $laundries->firstItem() }}–{{ $laundries->lastItem() }}
                    of {{ $laundries->total() }} laundries
                </small>
                {{ $laundries->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
/* row-level border in dark mode */
[data-theme="dark"] .laundry-row { border-color: #334155 !important; }
[data-theme="dark"] .laundry-row:hover { background: rgba(255,255,255,0.03); }
</style>
@endpush
@endsection
