@extends('branch.layouts.app')

@section('title', 'Staff & Payroll')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--text-primary);">Staff & Payroll</h2>
            <p class="small mb-0" style="color: var(--text-secondary);">View your branch staff and their salary information</p>
        </div>
        <div>
            <a href="{{ route('branch.staff.salary-information') }}" class="btn btn-primary">
                <i class="bi bi-table me-1"></i>Salary Information Table
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="bi bi-people fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1" style="color: var(--text-secondary);">Total Staff</h6>
                            <h3 class="mb-0" style="color: var(--text-primary);">{{ $stats['total'] }}</h3>
                            <small style="color: var(--text-secondary);">In your branch</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="bi bi-check-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1" style="color: var(--text-secondary);">Active Staff</h6>
                            <h3 class="mb-0" style="color: var(--text-primary);">{{ $stats['active'] }}</h3>
                            <small style="color: var(--text-secondary);">Currently working</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="bi bi-cash-stack fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1" style="color: var(--text-secondary);">With Salary Info</h6>
                            <h3 class="mb-0" style="color: var(--text-primary);">{{ $stats['with_salary'] }}</h3>
                            <small style="color: var(--text-secondary);">Configured</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                <i class="bi bi-calculator fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1" style="color: var(--text-secondary);">Est. Monthly</h6>
                            <h3 class="mb-0" style="color: var(--text-primary);">₱{{ number_format($stats['total_monthly'], 0) }}</h3>
                            <small style="color: var(--text-secondary);">Payroll estimate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Info Alert --}}
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle fs-4 me-3"></i>
            <div>
                <strong>View Only Access:</strong> You can view staff information and payroll details. 
                Contact admin to update salary information or manage payroll.
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small">Search Staff</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="{{ route('branch.staff.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Staff Cards --}}
    <div class="row g-4">
        @forelse($staff as $member)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                {{-- Photo --}}
                <div class="position-relative text-center pt-4">
                    @if($member->profile_photo_path)
                        <img src="{{ asset('storage/' . $member->profile_photo_path) }}"
                            class="rounded-circle border border-3"
                            style="width: 100px; height: 100px; object-fit: cover; border-color: {{ $member->is_active ? '#10B981' : '#6B7280' }} !important;">
                    @else
                        <div class="rounded-circle border border-3 mx-auto d-flex align-items-center justify-content-center"
                            style="width: 100px; height: 100px; background: linear-gradient(135deg, #3D3B6B 0%, #6366F1 100%); border-color: {{ $member->is_active ? '#10B981' : '#6B7280' }} !important;">
                            <i class="bi bi-person text-white" style="font-size: 2.5rem;"></i>
                        </div>
                    @endif

                    {{-- Status Badge --}}
                    <span class="badge position-absolute top-0 end-0 m-3"
                        style="background: {{ $member->is_active ? '#10B981' : '#6B7280' }}; font-size: 0.7rem;">
                        <i class="bi bi-{{ $member->is_active ? 'check-circle' : 'pause-circle' }} me-1"></i>
                        {{ $member->is_active ? 'ACTIVE' : 'INACTIVE' }}
                    </span>
                </div>

                {{-- Info --}}
                <div class="card-body p-4 text-center">
                    <h6 class="fw-bold mb-1" style="color: var(--text-primary);">{{ $member->name }}</h6>
                    <p class="small mb-2" style="color: var(--text-secondary);">{{ $member->position ?? 'Staff Member' }}</p>

                    {{-- Contact --}}
                    <div class="text-start mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center small mb-1" style="color: var(--text-secondary);">
                            <i class="bi bi-envelope me-2 text-primary"></i>
                            <span class="text-truncate">{{ Str::limit($member->email, 25) }}</span>
                        </div>
                        @if($member->phone)
                        <div class="d-flex align-items-center small" style="color: var(--text-secondary);">
                            <i class="bi bi-telephone me-2 text-primary"></i>
                            <span>{{ $member->phone }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Salary Info --}}
                    <div class="mb-3 pb-3 border-bottom">
                        @if($member->salaryInfo)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small style="color: var(--text-secondary);">Salary Type:</small>
                                <span class="badge bg-primary">{{ ucfirst($member->salaryInfo->salary_type) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small style="color: var(--text-secondary);">Base Rate:</small>
                                <strong class="text-success">₱{{ number_format($member->salaryInfo->base_rate, 2) }}</strong>
                            </div>
                            <small class="d-block mt-1" style="color: var(--text-secondary);">
                                per {{ $member->salaryInfo->salary_type === 'monthly' ? 'month' : ($member->salaryInfo->salary_type === 'daily' ? 'day' : 'hour') }}
                            </small>
                        @else
                            <div class="text-center py-2">
                                <i class="bi bi-wallet2" style="font-size: 2rem; opacity: 0.3; color: var(--text-secondary);"></i>
                                <p class="small mb-0 mt-2" style="color: var(--text-secondary);">No salary info set</p>
                                <small style="color: var(--text-secondary);">Using default rate</small>
                            </div>
                        @endif
                    </div>

                    {{-- Stats --}}
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="bg-light rounded p-2">
                                <div class="fw-bold text-primary">{{ $member->laundries_count }}</div>
                                <small style="color: var(--text-secondary);">Laundries</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-2">
                                <div class="fw-bold text-info">
                                    @if($member->hire_date)
                                        {{ (int) \Carbon\Carbon::parse($member->hire_date)->diffInMonths(now()) }}m
                                    @else
                                        N/A
                                    @endif
                                </div>
                                <small style="color: var(--text-secondary);">Tenure</small>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-grid">
                        <a href="{{ route('branch.staff.show', $member) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-eye me-1"></i>View Details & Payroll
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    <i class="bi bi-people" style="font-size: 4rem; opacity: 0.2;"></i>
                    <h5 class="fw-bold mt-3">No Staff Members Found</h5>
                    <p class="text-muted mb-0">No staff assigned to your branch yet</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($staff->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $staff->links() }}
        </div>
    @endif
</div>

@push('styles')
<style>
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
    }

    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
    }

    .card-body {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
    }

    .bg-light {
        background: var(--card-bg) !important;
        border: 1px solid var(--border-color) !important;
    }
</style>
@endpush
@endsection
