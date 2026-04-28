@extends('branch.layouts.app')

@section('title', 'Staff Details - ' . $user->name)

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $user->name }}</h2>
            <p class="text-muted small mb-0">{{ $user->position ?? 'Staff Member' }} • {{ $branch->name }}</p>
        </div>
        <a href="{{ route('branch.staff.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Staff List
        </a>
    </div>

    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs mb-4" id="staffTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                <i class="bi bi-person-circle me-2"></i>Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="payroll-tab" data-bs-toggle="tab" data-bs-target="#payroll" type="button" role="tab">
                <i class="bi bi-cash-stack me-2"></i>Payroll & Salary
            </button>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="staffTabsContent">
        {{-- Overview Tab --}}
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row g-4">
                {{-- Left Column - Profile Info --}}
                <div class="col-lg-4">
                    {{-- Profile Card --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4 text-center">
                            {{-- Photo --}}
                            <div class="mb-3">
                                @if($user->profile_photo_path)
                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                                        class="rounded-circle border border-3"
                                        style="width: 150px; height: 150px; object-fit: cover; border-color: {{ $user->is_active ? '#10B981' : '#6B7280' }} !important;">
                                @else
                                    <div class="rounded-circle border border-3 mx-auto d-flex align-items-center justify-content-center"
                                        style="width: 150px; height: 150px; background: linear-gradient(135deg, #3D3B6B 0%, #6366F1 100%); border-color: {{ $user->is_active ? '#10B981' : '#6B7280' }} !important;">
                                        <i class="bi bi-person text-white" style="font-size: 4rem;"></i>
                                    </div>
                                @endif
                            </div>

                            {{-- Name & Position --}}
                            <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                            <p class="text-muted mb-3">{{ $user->position ?? 'Staff Member' }}</p>

                            {{-- Status Badge --}}
                            <span class="badge mb-3" style="background: {{ $user->is_active ? '#10B981' : '#6B7280' }}; font-size: 0.9rem; padding: 0.5rem 1rem;">
                                <i class="bi bi-{{ $user->is_active ? 'check-circle' : 'pause-circle' }} me-1"></i>
                                {{ $user->is_active ? 'ACTIVE' : 'INACTIVE' }}
                            </span>
                        </div>
                    </div>

                    {{-- Contact Information --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header border-bottom py-3">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-person-lines-fill me-2 text-primary"></i>
                                Contact Information
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3 pb-3 border-bottom">
                                <small class="text-muted d-block mb-1">Email</small>
                                <strong class="d-block">{{ $user->email }}</strong>
                            </div>

                            @if($user->phone)
                            <div class="mb-3 pb-3 border-bottom">
                                <small class="text-muted d-block mb-1">Phone</small>
                                <strong class="d-block">{{ $user->phone }}</strong>
                            </div>
                            @endif

                            @if($user->address)
                            <div class="mb-3 pb-3 border-bottom">
                                <small class="text-muted d-block mb-1">Address</small>
                                <strong class="d-block">{{ $user->address }}</strong>
                            </div>
                            @endif

                            @if($user->employee_id)
                            <div>
                                <small class="text-muted d-block mb-1">Employee ID</small>
                                <span class="badge bg-light text-dark border">{{ $user->employee_id }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Employment Details --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-bottom py-3">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-briefcase me-2 text-primary"></i>
                                Employment Details
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">Hire Date</small>
                                <strong>{{ $user->hire_date ? $user->hire_date->format('F d, Y') : 'N/A' }}</strong>
                            </div>

                            @if($user->hire_date)
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">Tenure</small>
                                <strong>{{ $user->hire_date->diffForHumans(null, true) }}</strong>
                            </div>
                            @endif

                            <div>
                                <small class="text-muted d-block mb-1">Member Since</small>
                                <strong>{{ $user->created_at->format('F d, Y') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column - Attendance & Performance --}}
                <div class="col-lg-8">
                    {{-- Attendance Statistics --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-3 text-center">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                                        <i class="bi bi-calendar-check fs-4 text-primary"></i>
                                    </div>
                                    <h4 class="fw-bold mb-0">{{ $stats['total_days'] }}</h4>
                                    <small class="text-muted">Total Days</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-3 text-center">
                                    <div class="bg-success bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                                        <i class="bi bi-check-circle fs-4 text-success"></i>
                                    </div>
                                    <h4 class="fw-bold mb-0">{{ $stats['present_days'] }}</h4>
                                    <small class="text-muted">Present</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-3 text-center">
                                    <div class="bg-warning bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                                        <i class="bi bi-clock-history fs-4 text-warning"></i>
                                    </div>
                                    <h4 class="fw-bold mb-0">{{ number_format($stats['total_hours'], 1) }}</h4>
                                    <small class="text-muted">Total Hours</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-3 text-center">
                                    <div class="bg-info bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                                        <i class="bi bi-cash-stack fs-4 text-info"></i>
                                    </div>
                                    <h4 class="fw-bold mb-0">₱{{ number_format($stats['total_earnings'], 0) }}</h4>
                                    <small class="text-muted">Total Earnings</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Recent Attendance --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-bottom py-3">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-clock-history me-2 text-primary"></i>
                                Recent Attendance
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            @forelse($recent_attendance as $attendance)
                            <div class="p-3 border-bottom {{ $loop->last ? 'border-0' : '' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong class="d-block mb-1">{{ $attendance->attendance_date->format('F d, Y') }}</strong>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>{{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('h:i A') : 'N/A' }}
                                            @if($attendance->time_out)
                                                - {{ \Carbon\Carbon::parse($attendance->time_out)->format('h:i A') }}
                                            @endif
                                        </small>
                                        @if($attendance->hours_worked)
                                            <span class="mx-1">•</span>
                                            <small class="text-muted">{{ number_format($attendance->hours_worked, 1) }} hrs</small>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'absent' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($attendance->status) }}
                                        </span>
                                        @php
                                            $dailyPay = 0;
                                            if ($attendance->status === 'on_leave') {
                                                $dailyPay = 480;
                                            } elseif ($attendance->status !== 'absent') {
                                                $hours = $attendance->hours_worked ?? 0;
                                                if ($hours >= 8) $dailyPay = 480;
                                                elseif ($hours >= 4) $dailyPay = 240;
                                            }
                                        @endphp
                                        <strong class="d-block text-success">₱{{ number_format($dailyPay, 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="p-5 text-center">
                                <i class="bi bi-calendar-x" style="font-size: 3rem; opacity: 0.2;"></i>
                                <p class="text-muted mb-0 mt-2">No attendance records yet</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payroll Tab --}}
        <div class="tab-pane fade" id="payroll" role="tabpanel">
            <div class="row g-4">
                {{-- Salary Information --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-bottom py-3">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-wallet2 me-2 text-primary"></i>
                                Salary Information
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            @if($salaryInfo)
                                <div class="mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block mb-1">Salary Type</small>
                                    <strong>{{ ucfirst($salaryInfo->salary_type) }}</strong>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block mb-1">Base Rate</small>
                                    <h4 class="mb-0 text-success">₱{{ number_format($salaryInfo->base_rate, 2) }}</h4>
                                    <small class="text-muted">per {{ $salaryInfo->salary_type === 'monthly' ? 'month' : ($salaryInfo->salary_type === 'daily' ? 'day' : 'hour') }}</small>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block mb-1">Pay Period</small>
                                    <strong>{{ ucfirst($salaryInfo->pay_period) }}</strong>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block mb-1">Effectivity Date</small>
                                    <strong>{{ $salaryInfo->effectivity_date->format('M d, Y') }}</strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block mb-1">Status</small>
                                    <span class="badge" style="background: {{ $salaryInfo->is_active ? '#10B981' : '#6B7280' }}">
                                        {{ $salaryInfo->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-wallet2" style="font-size: 3rem; opacity: 0.2;"></i>
                                    <p class="text-muted mt-3 mb-0">No salary information set</p>
                                    <small class="text-muted">Using default rate (₱480/day)</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Payroll History --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-bottom py-3">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-clock-history me-2 text-primary"></i>
                                Payment History
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            @forelse($payrollHistory as $item)
                            <div class="p-3 border-bottom {{ $loop->last ? 'border-0' : '' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong class="d-block mb-1">{{ $item->payrollPeriod->period_label }}</strong>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i>{{ $item->payrollPeriod->date_from->format('M d') }} - {{ $item->payrollPeriod->date_to->format('M d, Y') }}
                                        </small>
                                        <div class="mt-2">
                                            <small class="text-muted">Days: {{ $item->days_worked }}</small>
                                            <span class="mx-1">•</span>
                                            <small class="text-muted">Hours: {{ $item->hours_worked }}</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $item->status === 'paid' ? 'success' : ($item->status === 'pending' ? 'warning' : 'primary') }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                        <div class="mt-2">
                                            <small class="text-muted d-block">Gross: ₱{{ number_format($item->gross_pay, 2) }}</small>
                                            @if($item->deductions > 0)
                                                <small class="text-danger d-block">Deductions: -₱{{ number_format($item->deductions, 2) }}</small>
                                            @endif
                                            @if($item->bonuses > 0)
                                                <small class="text-success d-block">Bonuses: +₱{{ number_format($item->bonuses, 2) }}</small>
                                            @endif
                                            <strong class="d-block mt-1 text-success" style="font-size: 1.1rem;">₱{{ number_format($item->net_pay, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="p-5 text-center">
                                <i class="bi bi-receipt" style="font-size: 3rem; opacity: 0.2;"></i>
                                <p class="text-muted mb-0 mt-2">No payment history yet</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Payroll Summary Stats --}}
                    @if($payrollHistory->count() > 0)
                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-3 text-center">
                                    <div class="bg-success bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                                        <i class="bi bi-cash-stack fs-4 text-success"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0">₱{{ number_format($payrollHistory->sum('net_pay'), 2) }}</h5>
                                    <small class="text-muted">Total Earned</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-3 text-center">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                                        <i class="bi bi-calendar-check fs-4 text-primary"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0">{{ $payrollHistory->where('status', 'paid')->count() }}</h5>
                                    <small class="text-muted">Payments Received</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-3 text-center">
                                    <div class="bg-info bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                                        <i class="bi bi-graph-up fs-4 text-info"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0">₱{{ number_format($payrollHistory->avg('net_pay'), 2) }}</h5>
                                    <small class="text-muted">Average Pay</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .nav-tabs .nav-link {
        color: var(--text-secondary);
        border: none;
        border-bottom: 2px solid transparent;
        padding: 0.75rem 1.5rem;
    }
    
    .nav-tabs .nav-link:hover {
        color: var(--text-primary);
        border-color: transparent;
    }
    
    .nav-tabs .nav-link.active {
        color: #3D3B6B;
        border-bottom-color: #3D3B6B;
        background-color: transparent;
        font-weight: 600;
    }
    
    .nav-tabs {
        border-bottom: 1px solid #dee2e6;
    }
</style>
@endpush
@endsection
