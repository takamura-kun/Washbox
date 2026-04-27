@extends('branch.layouts.app')

@section('page-title', 'MY LAUNDRIES')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/laundry.css') }}">
    <style>
        .hover-lift {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
        }
        [data-theme="light"] .hover-lift {
            background-color: #ffffff !important;
            color: #111827 !important;
        }
        [data-theme="dark"] .hover-lift {
            background-color: #1F2937 !important;
            color: #F9FAFB !important;
        }
        [data-theme="light"] .laundry-filter-card {
            background-color: #ffffff !important;
        }
        [data-theme="dark"] .laundry-filter-card {
            background-color: #1F2937 !important;
        }
        [data-theme="light"] .hover-lift .text-white { color: #111827 !important; }
        [data-theme="light"] .hover-lift .text-white-50 { color: #6b7280 !important; }
        [data-theme="light"] .hover-lift .tracking-number { color: #111827 !important; }
        [data-theme="light"] .hover-lift .amount { color: #111827 !important; }
        [data-theme="light"] .hover-lift .border-top { border-color: #e5e7eb !important; }
        [data-theme="dark"] .hover-lift .text-white { color: #F9FAFB !important; }
        [data-theme="dark"] .hover-lift .text-white-50 { color: #9ca3af !important; }
    </style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted small mb-0">Track and manage all laundry for your branch</p>
        </div>
        <a href="{{ route('branch.laundries.create') }}" class="btn btn-primary shadow-sm">
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
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Overview --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body p-3 text-center text-white">
                    <div class="bg-white bg-opacity-25 p-2 rounded-3 d-inline-block mb-2">
                        <i class="bi bi-box-seam fs-4 text-white"></i>
                    </div>
                    <h4 class="fw-bold mb-0 text-white">{{ $stats['total'] }}</h4>
                    <small class="text-white-50">Total Laundries</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body p-3 text-center text-white">
                    <div class="bg-white bg-opacity-25 p-2 rounded-3 d-inline-block mb-2">
                        <i class="bi bi-clock fs-4 text-white"></i>
                    </div>
                    <h4 class="fw-bold mb-0 text-white">{{ $stats['pending'] ?? $stats['received'] }}</h4>
                    <small class="text-white-50">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body p-3 text-center text-white">
                    <div class="bg-white bg-opacity-25 p-2 rounded-3 d-inline-block mb-2">
                        <i class="bi bi-arrow-repeat fs-4 text-white"></i>
                    </div>
                    <h4 class="fw-bold mb-0 text-white">{{ $stats['processing'] }}</h4>
                    <small class="text-white-50">Processing</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body p-3 text-center text-white">
                    <div class="bg-white bg-opacity-25 p-2 rounded-3 d-inline-block mb-2">
                        <i class="bi bi-check-circle fs-4 text-white"></i>
                    </div>
                    <h4 class="fw-bold mb-0 text-white">{{ $stats['ready'] }}</h4>
                    <small class="text-white-50">Ready</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                <div class="card-body p-3 text-center text-dark">
                    <div class="bg-dark bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                        <i class="bi bi-check-all fs-4 text-dark"></i>
                    </div>
                    <h4 class="fw-bold mb-0 text-dark">{{ $stats['completed'] }}</h4>
                    <small class="text-dark opacity-75">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="card-body p-3 text-center text-white">
                    <div class="bg-white bg-opacity-25 p-2 rounded-3 d-inline-block mb-2">
                        <i class="bi bi-cash-stack fs-4 text-white"></i>
                    </div>
                    <h5 class="fw-bold mb-0 small text-white">₱{{ number_format($stats['total_revenue'], 0) }}</h5>
                    <small class="text-white-50">Revenue</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('branch.laundries.index', array_merge(request()->except('tab','page'), ['tab'=>'all'])) }}"
           class="btn {{ $tab === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi bi-list-ul me-1"></i>All Laundries
            <span class="badge {{ $tab === 'all' ? 'bg-white text-primary' : 'bg-primary' }} ms-1">{{ $tabCounts['all'] }}</span>
        </a>
        <a href="{{ route('branch.laundries.index', array_merge(request()->except('tab','page'), ['tab'=>'walkin'])) }}"
           class="btn {{ $tab === 'walkin' ? 'btn-secondary' : 'btn-outline-secondary' }}">
            <i class="bi bi-person-walking me-1"></i>Walk-in / Drop-off
            <span class="badge {{ $tab === 'walkin' ? 'bg-white text-dark' : 'bg-secondary' }} ms-1">{{ $tabCounts['walkin'] }}</span>
        </a>
        <a href="{{ route('branch.laundries.index', array_merge(request()->except('tab','page'), ['tab'=>'delivery'])) }}"
           class="btn {{ $tab === 'delivery' ? 'btn-success' : 'btn-outline-secondary' }}">
            <i class="bi bi-truck me-1"></i>Pickup &amp; Delivery
            <span class="badge {{ $tab === 'delivery' ? 'bg-white text-success' : 'bg-success' }} ms-1">{{ $tabCounts['delivery'] }}</span>
        </a>
    </div>

    {{-- Filters (No branch filter for branch users) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 laundry-filter-card">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Search..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        @foreach(['received'=>'Received','processing'=>'Processing','ready'=>'Ready','out_for_delivery'=>'Out for Delivery','delivered'=>'Delivered','paid'=>'Paid','completed'=>'Completed','cancelled'=>'Cancelled'] as $val=>$label)
                            <option value="{{ $val }}" {{ request('status')===$val?'selected':'' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
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
                <div class="col-md-1">
                    <a href="{{ route('branch.laundries.index', ['tab' => $tab]) }}" class="btn btn-sm btn-light border w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Laundries Grid --}}
    @forelse($laundries as $laundry)
        @if($loop->first)
        <div class="row g-3">
        @endif
            @php
                $statusColors = [
                    'received'         => 'secondary',
                    'processing'       => 'warning',
                    'ready'            => 'info',
                    'out_for_delivery' => 'primary',
                    'delivered'        => 'success',
                    'paid'             => 'primary',
                    'completed'        => 'success',
                    'cancelled'        => 'danger',
                ];
                $color = $statusColors[$laundry->status] ?? 'secondary';
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift">
                    <div class="card-body p-3 text-white">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <a href="{{ route('branch.laundries.show', $laundry) }}" class="tracking-number fw-bold text-decoration-none text-white">
                                    {{ $laundry->tracking_number }}
                                </a>
                                @if($laundry->pickupRequest)
                                    <br><small class="text-white-50"><i class="bi bi-truck me-1"></i>Pickup #{{ $laundry->pickupRequest->id }}
                                        @if(in_array($laundry->pickupRequest->service_type, ['both','delivery_only']))
                                            <span class="badge bg-success ms-1" style="font-size:0.6rem;">Delivery</span>
                                        @else
                                            <span class="badge bg-light text-dark ms-1" style="font-size:0.6rem;">Walk-in</span>
                                        @endif
                                    </small>
                                @endif
                            </div>
                            <span class="badge bg-{{ $color }}">{{ $laundry->status_label }}</span>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-person-circle me-2 text-white-50"></i>
                                <div>
                                    <div class="fw-semibold text-white">{{ $laundry->customer->name ?? 'N/A' }}</div>
                                    <small class="text-white-50">{{ $laundry->customer->phone ?? '' }}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-shop me-2 text-white-50 flex-shrink-0"></i>
                                <span class="badge bg-secondary text-truncate" style="font-size:0.72rem; font-weight:500; max-width:100%;">{{ $laundry->branch->name ?? 'N/A' }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-droplet me-2 text-white-50"></i>
                                <div>
                                    <span class="text-white">{{ $laundry->service->name ?? 'N/A' }}</span>
                                    @if($laundry->addons_total > 0)
                                        <br><small class="text-success">+₱{{ number_format($laundry->addons_total, 2) }} add-ons</small>
                                    @endif
                                    @if($laundry->promotion)
                                        <br><small class="text-info"><i class="bi bi-tag me-1"></i>{{ Str::limit($laundry->promotion->name, 15) }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="border-color: rgba(255,255,255,0.2) !important;">
                            <div>
                                @if($laundry->weight > 0)
                                    <small class="text-white-50">{{ number_format($laundry->weight, 2) }} kg</small>
                                @endif
                                @if($laundry->number_of_loads)
                                    <small class="text-white-50"> {{ $laundry->number_of_loads }} {{ ($laundry->service?->pricing_type === 'per_piece') ? 'pc(s)' : 'load(s)' }}</small>
                                @endif
                                <br><small class="text-white-50">{{ $laundry->created_at->format('M d, Y h:i A') }}</small>
                            </div>
                            <div class="text-end">
                                <strong class="amount d-block text-white">₱{{ number_format($laundry->total_amount, 2) }}</strong>
                                @if($laundry->discount_amount > 0)
                                    <small class="text-success">-₱{{ number_format($laundry->discount_amount, 2) }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @if($loop->last)
        </div>
        @endif
    @empty
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-5">
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>
                        @if(request()->anyFilled(['search','status','service_id','date_from']))
                            No laundries match your filters<br>
                            <a href="{{ route('branch.laundries.index', ['tab' => $tab]) }}" class="btn btn-link">Clear all filters</a>
                        @else
                            No laundries found
                        @endif
                    </p>
                    <a href="{{ route('branch.laundries.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Create First Laundry
                    </a>
                </div>
            </div>
        </div>
    @endforelse

    @if($laundries->hasPages())
    <div class="card border-0 shadow-sm rounded-4 mt-3 laundry-filter-card">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $laundries->firstItem() }}–{{ $laundries->lastItem() }}
                    of {{ $laundries->total() }} laundries
                </small>
                {{ $laundries->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
