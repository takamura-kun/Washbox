@extends('admin.layouts.app')

@section('page-title', 'Customers Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/customers.css') }}">
    <style>
        .customer-card {
            transition: transform 0.2s, box-shadow 0.2s;
            background: var(--card-bg) !important;
            color: var(--text-primary) !important;
            border-color: var(--border-color) !important;
        }
        .customer-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
        }
        .card {
            background: var(--card-bg) !important;
            color: var(--text-primary) !important;
            border-color: var(--border-color) !important;
        }
        .card-body {
            background: var(--card-bg) !important;
            color: var(--text-primary) !important;
        }
        .border-top {
            border-color: var(--border-color) !important;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 page-header">
        <div>
            <p class="mb-0" style="color: var(--text-secondary);">Manage walk-in and mobile app registered customers across all branches.</p>
        </div>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary shadow-sm px-4">
            <i class="bi bi-person-plus-fill me-2"></i>Create New Customer
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="d-flex align-items-start justify-content-between">
                <div class="flex-grow-1">
                    <div class="stat-label">Total Customers</div>
                    <div class="stat-value">{{ number_format($stats['total']) }}</div>
                    <div class="stat-trend up">
                        <i class="bi bi-plus-circle"></i> {{ number_format($stats['new_today']) }} new today
                    </div>
                </div>
                <div class="stat-icon primary">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="d-flex align-items-start justify-content-between">
                <div class="flex-grow-1">
                    <div class="stat-label">Walk-in Customers</div>
                    <div class="stat-value">{{ number_format($stats['walk_in']) }}</div>
                    <div class="stat-trend">
                        {{ number_format(($stats['walk_in'] / max($stats['total'], 1)) * 100, 1) }}% of total
                    </div>
                </div>
                <div class="stat-icon warning">
                    <i class="bi bi-person-fill"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="d-flex align-items-start justify-content-between">
                <div class="flex-grow-1">
                    <div class="stat-label">Mobile App Users</div>
                    <div class="stat-value">{{ number_format($stats['self_registered']) }}</div>
                    <div class="stat-trend">
                        {{ number_format(($stats['self_registered'] / max($stats['total'], 1)) * 100, 1) }}% of total
                    </div>
                </div>
                <div class="stat-icon success">
                    <i class="bi bi-phone-fill"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="d-flex align-items-start justify-content-between">
                <div class="flex-grow-1">
                    <div class="stat-label">New Today</div>
                    <div class="stat-value">{{ number_format($stats['new_today']) }}</div>
                    <div class="stat-trend">
                        @if($stats['new_today'] > 0)
                            <i class="bi bi-graph-up-arrow"></i> Growing
                        @else
                            <i class="bi bi-dash-circle"></i> No new signups
                        @endif
                    </div>
                </div>
                <div class="stat-icon secondary">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters - FIXED SEARCH BAR --}}
    <div class="filter-card">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-lg-2 col-md-4">
                <label class="filter-label">Registration Type</label>
                <select name="registration_type" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="walk_in" {{ request('registration_type') === 'walk_in' ? 'selected' : '' }}>Walk-in</option>
                    <option value="self_registered" {{ request('registration_type') === 'self_registered' ? 'selected' : '' }}>Mobile App</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-4">
                <label class="filter-label">Branch</label>
                <select name="branch_id" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2 col-md-4">
                <label class="filter-label">Status</label>
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="col-lg-4 col-md-8">
                <label class="filter-label">Search</label>
                <div class="d-flex gap-2">
                    <div class="flex-grow-1 position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="z-index: 10;"></i>
                        <input type="text" 
                               name="search" 
                               class="filter-input" 
                               placeholder="Search by name, phone, or email..." 
                               value="{{ request('search') }}"
                               style="padding-left: 2.5rem;">
                    </div>
                    <button class="filter-btn filter-btn-primary" type="submit">
                        <i class="bi bi-search"></i> Search
                    </button>
                    @if(request()->anyFilled(['search', 'branch_id', 'status', 'registration_type']))
                        <a href="{{ route('admin.customers.index') }}" class="filter-btn filter-btn-clear">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Active Filters Display --}}
        @if(request()->anyFilled(['search', 'branch_id', 'status', 'registration_type']))
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted small">Active filters:</span>
                    @if(request('search'))
                        <span class="filter-badge">
                            <i class="bi bi-search me-1"></i> "{{ request('search') }}"
                            <a href="{{ request()->fullUrlWithoutQuery(['search']) }}" class="remove-filter">
                                <i class="bi bi-x"></i>
                            </a>
                        </span>
                    @endif
                    @if(request('registration_type'))
                        <span class="filter-badge">
                            <i class="bi bi-person me-1"></i> {{ request('registration_type') === 'walk_in' ? 'Walk-in' : 'Mobile App' }}
                            <a href="{{ request()->fullUrlWithoutQuery(['registration_type']) }}" class="remove-filter">
                                <i class="bi bi-x"></i>
                            </a>
                        </span>
                    @endif
                    @if(request('branch_id'))
                        @php $branch = $branches->firstWhere('id', request('branch_id')); @endphp
                        @if($branch)
                            <span class="filter-badge">
                                <i class="bi bi-geo-alt me-1"></i> {{ $branch->name }}
                                <a href="{{ request()->fullUrlWithoutQuery(['branch_id']) }}" class="remove-filter">
                                    <i class="bi bi-x"></i>
                                </a>
                            </span>
                        @endif
                    @endif
                    @if(request('status'))
                        <span class="filter-badge">
                            <i class="bi bi-{{ request('status') === 'active' ? 'check-circle' : 'x-circle' }} me-1"></i> {{ ucfirst(request('status')) }}
                            <a href="{{ request()->fullUrlWithoutQuery(['status']) }}" class="remove-filter">
                                <i class="bi bi-x"></i>
                            </a>
                        </span>
                    @endif
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-link text-danger p-0 ms-2">
                        Clear all
                    </a>
                </div>
            </div>
        @endif
    </div>

    {{-- Customers Grid --}}
    @if($customers->count() > 0)
        <div class="row g-3">
            @foreach($customers as $customer)
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 customer-card">
                    <div class="card-body p-2" style="font-size: 0.813rem;">
                        <div class="d-flex align-items-start mb-2">
                            @if($customer->profile_photo_url)
                                <img src="{{ $customer->profile_photo_url }}" alt="{{ $customer->name }}" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                            @else
                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: linear-gradient(135deg, #2D2B5F, #FF5C35); color: white; font-weight: 600; font-size: 0.813rem;">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold text-truncate" style="font-size: 0.813rem; line-height: 1.2;">{{ $customer->name }}</div>
                                <small style="color: var(--text-secondary); font-size: 0.688rem;">#CUST-{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</small>
                                <div style="margin-top: 0.25rem;">
                                    @if($customer->registration_type == 'walk_in')
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size: 0.625rem; padding: 0.125rem 0.375rem;">
                                            <i class="bi bi-person" style="font-size: 0.625rem;"></i> Walk-in
                                        </span>
                                    @else
                                        <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size: 0.625rem; padding: 0.125rem 0.375rem;">
                                            <i class="bi bi-phone" style="font-size: 0.625rem;"></i> Mobile
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mb-2" style="font-size: 0.688rem;">
                            <div class="d-flex align-items-center" style="margin-bottom: 0.25rem;">
                                <i class="bi bi-telephone me-1" style="color: var(--text-secondary); font-size: 0.688rem;"></i>
                                <small class="text-truncate" style="color: var(--text-secondary); font-size: 0.688rem;">{{ $customer->phone }}</small>
                            </div>
                            @if($customer->email)
                                <div class="d-flex align-items-center" style="margin-bottom: 0.25rem;">
                                    <i class="bi bi-envelope me-1" style="color: var(--text-secondary); font-size: 0.688rem;"></i>
                                    <small class="text-truncate" style="color: var(--text-secondary); font-size: 0.688rem;">{{ Str::limit($customer->email, 22) }}</small>
                                </div>
                            @endif
                            @if($customer->preferredBranch)
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-geo-alt me-1" style="color: var(--text-secondary); font-size: 0.688rem;"></i>
                                    <small class="text-truncate" style="color: var(--text-secondary); font-size: 0.688rem;">{{ $customer->preferredBranch->name }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top" style="margin-top: 0.5rem;">
                            <div>
                                <small class="d-block" style="color: var(--text-secondary); font-size: 0.625rem;">Laundries</small>
                                <strong style="font-size: 0.875rem;">{{ $customer->laundries_count ?? $customer->laundries()->count() }}</strong>
                            </div>
                            <div class="text-end">
                                <small class="d-block" style="color: var(--text-secondary); font-size: 0.625rem;">Spent</small>
                                <strong class="text-success" style="font-size: 0.813rem;">₱{{ number_format($customer->getTotalSpent(), 0) }}</strong>
                            </div>
                            <div>
                                @if($customer->is_active)
                                    <span class="badge bg-success" style="font-size: 0.625rem; padding: 0.125rem 0.375rem;">
                                        <i class="bi bi-check-circle" style="font-size: 0.625rem;"></i>
                                    </span>
                                @else
                                    <span class="badge bg-danger" style="font-size: 0.625rem; padding: 0.125rem 0.375rem;">
                                        <i class="bi bi-x-circle" style="font-size: 0.625rem;"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-1" style="margin-top: 0.5rem;">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline-primary flex-fill" style="font-size: 0.688rem; padding: 0.25rem 0.5rem;">
                                <i class="bi bi-eye" style="font-size: 0.688rem;"></i> View
                            </a>
                            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-outline-secondary flex-fill" style="font-size: 0.688rem; padding: 0.25rem 0.5rem;">
                                <i class="bi bi-pencil" style="font-size: 0.688rem;"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        {{-- Pagination --}}
        @if($customers->hasPages())
            <div class="card border-0 shadow-sm rounded-4 mt-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small style="color: var(--text-secondary);">
                            Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} customers
                        </small>
                        {{ $customers->links() }}
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-5">
                <div class="empty-state">
                    <i class="bi bi-people"></i>
                    <h5>No customers found</h5>
                    <p>No customers match your current filters. Try adjusting your search criteria.</p>
                    @if(request()->anyFilled(['search', 'branch_id', 'status', 'registration_type']))
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Clear all filters
                        </a>
                    @else
                        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                            <i class="bi bi-person-plus me-1"></i> Create First Customer
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection