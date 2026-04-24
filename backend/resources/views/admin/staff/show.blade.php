@extends('admin.layouts.app')

@section('page-title', 'Staff Profile - ' . $staff->name)

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted small mb-0" style="color: var(--text-secondary) !important;">{{ $staff->position ?? 'Staff Member' }} • {{ $staff->branch->name ?? 'No Branch' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.staff.edit', $staff) }}" class="btn btn-primary shadow-sm" style="background: #3D3B6B; border: none;">
                <i class="bi bi-pencil me-2"></i>Edit Profile
            </a>
            <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Staff
            </a>
        </div>
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
                <i class="bi bi-cash-stack me-2"></i>Payroll
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
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: var(--card-bg) !important;">
                <div class="card-body p-4 text-center" style="background-color: var(--card-bg) !important;">
                    {{-- Photo --}}
                    <div class="mb-3">
                        @if($staff->profile_photo_path)
                            <img src="{{ asset('storage/' . $staff->profile_photo_path) }}"
                                class="rounded-circle border border-3"
                                style="width: 150px; height: 150px; object-fit: cover; border-color: {{ $staff->is_active ? '#10B981' : '#6B7280' }} !important;">
                        @else
                            <div class="rounded-circle border border-3 mx-auto d-flex align-items-center justify-content-center"
                                style="width: 150px; height: 150px; background: linear-gradient(135deg, #3D3B6B 0%, #6366F1 100%); border-color: {{ $staff->is_active ? '#10B981' : '#6B7280' }} !important;">
                                <i class="bi bi-person text-white" style="font-size: 4rem;"></i>
                            </div>
                        @endif
                    </div>

                    {{-- Name & Position --}}
                    <h5 class="fw-bold mb-1" style="color: var(--text-primary) !important;">{{ $staff->name }}</h5>
                    <p class="text-muted mb-3" style="color: var(--text-secondary) !important;">{{ $staff->position ?? 'Staff Member' }}</p>

                    {{-- Status Badge --}}
                    <span class="badge mb-3" style="background: {{ $staff->is_active ? '#10B981' : '#6B7280' }}; font-size: 0.9rem; padding: 0.5rem 1rem;">
                        <i class="bi bi-{{ $staff->is_active ? 'check-circle' : 'pause-circle' }} me-1"></i>
                        {{ $staff->is_active ? 'ACTIVE' : 'INACTIVE' }}
                    </span>

                    {{-- Branch Badge --}}
                    @if($staff->branch)
                        <div class="mb-3">
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-building me-1"></i>{{ $staff->branch->name }}
                            </span>
                        </div>
                    @endif

                    {{-- Quick Actions --}}
                    <div class="d-flex gap-2 mb-3 flex-column">
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.staff.edit', $staff) }}" class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form action="{{ route('admin.staff.toggle-status', $staff) }}" method="POST" class="flex-fill">
                                @csrf
                                <button type="submit" class="btn btn-outline-{{ $staff->is_active ? 'warning' : 'success' }} btn-sm w-100">
                                    <i class="bi bi-{{ $staff->is_active ? 'pause' : 'play' }}-circle"></i>
                                    {{ $staff->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </div>
                        <form action="{{ route('admin.staff.destroy', $staff) }}" method="POST" class="w-100" onsubmit="return confirm('Are you sure you want to delete {{ $staff->name }}? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: var(--card-bg) !important;">
                <div class="card-header border-bottom py-3" style="background-color: var(--card-bg) !important;">
                    <h6 class="mb-0 fw-bold" style="color: var(--text-primary) !important;">
                        <i class="bi bi-person-lines-fill me-2" style="color: #3D3B6B;"></i>
                        Contact Information
                    </h6>
                </div>
                <div class="card-body p-4" style="background-color: var(--card-bg) !important;">
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block mb-1" style="color: var(--text-secondary) !important;">Email</small>
                        <strong class="d-block" style="color: var(--text-primary) !important;">{{ $staff->email }}</strong>
                    </div>

                    @if($staff->phone)
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block mb-1" style="color: var(--text-secondary) !important;">Phone</small>
                        <strong class="d-block" style="color: var(--text-primary) !important;">{{ $staff->phone }}</strong>
                    </div>
                    @endif

                    @if($staff->address)
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block mb-1" style="color: var(--text-secondary) !important;">Address</small>
                        <strong class="d-block" style="color: var(--text-primary) !important;">{{ $staff->address }}</strong>
                    </div>
                    @endif

                    @if($staff->employee_id)
                    <div>
                        <small class="text-muted d-block mb-1" style="color: var(--text-secondary) !important;">Employee ID</small>
                        <span class="badge bg-light text-dark border">{{ $staff->employee_id }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Emergency Contact --}}
            @if($staff->emergency_contact || $staff->emergency_phone)
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: var(--card-bg) !important;">
                <div class="card-header border-bottom py-3" style="background-color: var(--card-bg) !important;">
                    <h6 class="mb-0 fw-bold" style="color: var(--text-primary) !important;">
                        <i class="bi bi-telephone-plus me-2" style="color: #3D3B6B;"></i>
                        Emergency Contact
                    </h6>
                </div>
                <div class="card-body p-4" style="background-color: var(--card-bg) !important;">
                    @if($staff->emergency_contact)
                    <div class="mb-2">
                        <small class="text-muted d-block mb-1" style="color: var(--text-secondary) !important;">Contact Name</small>
                        <strong style="color: var(--text-primary) !important;">{{ $staff->emergency_contact }}</strong>
                    </div>
                    @endif

                    @if($staff->emergency_phone)
                    <div>
                        <small class="text-muted d-block mb-1" style="color: var(--text-secondary) !important;">Contact Phone</small>
                        <strong style="color: var(--text-primary) !important;">{{ $staff->emergency_phone }}</strong>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Employment Details --}}
            <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg) !important;">
                <div class="card-header border-bottom py-3" style="background-color: var(--card-bg) !important;">
                    <h6 class="mb-0 fw-bold" style="color: var(--text-primary) !important;">
                        <i class="bi bi-briefcase me-2" style="color: #3D3B6B;"></i>
                        Employment Details
                    </h6>
                </div>
                <div class="card-body p-4" style="background-color: var(--card-bg) !important;">
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1" style="color: var(--text-secondary) !important;">Hire Date</small>
                        <strong style="color: var(--text-primary) !important;">{{ $staff->hire_date ? $staff->hire_date->format('F d, Y') : 'N/A' }}</strong>
                    </div>

                    @if($staff->hire_date)
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1" style="color: var(--text-secondary) !important;">Tenure</small>
                        <strong style="color: var(--text-primary) !important;">{{ $staff->hire_date->diffForHumans(null, true) }}</strong>
                    </div>
                    @endif

                    <div>
                        <small class="text-muted d-block mb-1" style="color: var(--text-secondary) !important;">Member Since</small>
                        <strong style="color: var(--text-primary) !important;">{{ $staff->created_at->format('F d, Y') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column - Attendance & Performance --}}
        <div class="col-lg-8">
            {{-- Attendance Statistics --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100" style="background-color: var(--card-bg) !important;">
                        <div class="card-body p-3 text-center" style="background-color: var(--card-bg) !important;">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                                <i class="bi bi-calendar-check fs-4 text-primary"></i>
                            </div>
                            <h4 class="fw-bold mb-0" style="color: var(--text-primary) !important;">{{ $stats['total_days'] }}</h4>
                            <small class="text-muted" style="color: var(--text-secondary) !important;">Total Days</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100" style="background-color: var(--card-bg) !important;">
                        <div class="card-body p-3 text-center" style="background-color: var(--card-bg) !important;">
                            <div class="bg-success bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                                <i class="bi bi-check-circle fs-4 text-success"></i>
                            </div>
                            <h4 class="fw-bold mb-0" style="color: var(--text-primary) !important;">{{ $stats['present_days'] }}</h4>
                            <small class="text-muted" style="color: var(--text-secondary) !important;">Present</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100" style="background-color: var(--card-bg) !important;">
                        <div class="card-body p-3 text-center" style="background-color: var(--card-bg) !important;">
                            <div class="bg-warning bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                                <i class="bi bi-clock-history fs-4 text-warning"></i>
                            </div>
                            <h4 class="fw-bold mb-0" style="color: var(--text-primary) !important;">{{ number_format($stats['total_hours'], 1) }}</h4>
                            <small class="text-muted" style="color: var(--text-secondary) !important;">Total Hours</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100" style="background-color: var(--card-bg) !important;">
                        <div class="card-body p-3 text-center" style="background-color: var(--card-bg) !important;">
                            <div class="bg-info bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                                <i class="bi bi-cash-stack fs-4 text-info"></i>
                            </div>
                            <h4 class="fw-bold mb-0" style="color: var(--text-primary) !important;">₱{{ number_format($stats['total_earnings'], 0) }}</h4>
                            <small class="text-muted" style="color: var(--text-secondary) !important;">Total Earnings</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Attendance Summary --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: var(--card-bg) !important;">
                <div class="card-header border-bottom py-3" style="background-color: var(--card-bg) !important;">
                    <h6 class="mb-0 fw-bold" style="color: var(--text-primary) !important;">
                        <i class="bi bi-graph-up me-2" style="color: #3D3B6B;"></i>
                        Attendance Summary
                    </h6>
                </div>
                <div class="card-body p-4" style="background-color: var(--card-bg) !important;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted" style="color: var(--text-secondary) !important;">Attendance Rate</span>
                                <strong style="color: var(--text-primary) !important;">{{ $stats['total_days'] > 0 ? round(($stats['present_days']/$stats['total_days'])*100) : 0 }}%</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ $stats['total_days'] > 0 ? ($stats['present_days']/$stats['total_days'])*100 : 0 }}%"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted" style="color: var(--text-secondary) !important;">Average Hours/Day</span>
                                <strong style="color: var(--text-primary) !important;">{{ $stats['present_days'] > 0 ? number_format($stats['total_hours']/$stats['present_days'], 1) : 0 }} hrs</strong>
                            </div>
                            <small class="text-muted" style="color: var(--text-secondary) !important;">Based on present days</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Attendance --}}
            <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg) !important;">
                <div class="card-header border-bottom py-3" style="background-color: var(--card-bg) !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold" style="color: var(--text-primary) !important;">
                            <i class="bi bi-clock-history me-2" style="color: #3D3B6B;"></i>
                            Recent Attendance
                        </h6>
                        <a href="{{ route('admin.attendance.report', ['staff_id' => $staff->id]) }}" class="btn btn-sm btn-outline-secondary">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body p-0" style="background-color: var(--card-bg) !important;">
                    @forelse($recent_attendance as $attendance)
                    <div class="p-3 border-bottom {{ $loop->last ? 'border-0' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong class="d-block mb-1" style="color: var(--text-primary) !important;">{{ $attendance->attendance_date->format('F d, Y') }}</strong>
                                <small class="text-muted" style="color: var(--text-secondary) !important;">
                                    <i class="bi bi-clock me-1"></i>{{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('h:i A') : 'N/A' }}
                                    @if($attendance->time_out)
                                        - {{ \Carbon\Carbon::parse($attendance->time_out)->format('h:i A') }}
                                    @endif
                                </small>
                                @if($attendance->hours_worked)
                                    <span class="mx-1">•</span>
                                    <small class="text-muted" style="color: var(--text-secondary) !important;">{{ number_format($attendance->hours_worked, 1) }} hrs</small>
                                @endif
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'absent' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                                @if($attendance->shift_type)
                                    <div class="small text-muted mt-1" style="color: var(--text-secondary) !important;">{{ ucfirst(str_replace('_', ' ', $attendance->shift_type)) }}</div>
                                @endif
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
                                <strong class="d-block" style="color: #3D3B6B;">₱{{ number_format($dailyPay, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-5 text-center">
                        <i class="bi bi-calendar-x" style="font-size: 3rem; opacity: 0.2;"></i>
                        <p class="text-muted mb-0 mt-2" style="color: var(--text-secondary) !important;">No attendance records yet</p>
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
                    <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg) !important;">
                        <div class="card-header border-bottom py-3" style="background-color: var(--card-bg) !important;">
                            <h6 class="mb-0 fw-bold" style="color: var(--text-primary) !important;">
                                <i class="bi bi-wallet2 me-2" style="color: #3D3B6B;"></i>
                                Salary Information
                            </h6>
                        </div>
                        <div class="card-body p-4" style="background-color: var(--card-bg) !important;">
                            @if($salaryInfo)
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span></span>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#salaryModal">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block mb-1" style="color: var(--text-secondary) !important;">Salary Type</small>
                                    <strong style="color: var(--text-primary) !important;">{{ ucfirst($salaryInfo->salary_type) }}</strong>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block mb-1" style="color: var(--text-secondary) !important;">Base Rate</small>
                                    <h4 class="mb-0" style="color: #3D3B6B;">₱{{ number_format($salaryInfo->base_rate, 2) }}</h4>
                                    <small class="text-muted" style="color: var(--text-secondary) !important;">per {{ $salaryInfo->salary_type === 'monthly' ? 'month' : ($salaryInfo->salary_type === 'daily' ? 'day' : 'hour') }}</small>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block mb-1" style="color: var(--text-secondary) !important;">Pay Period</small>
                                    <strong style="color: var(--text-primary) !important;">{{ ucfirst($salaryInfo->pay_period) }}</strong>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block mb-1" style="color: var(--text-secondary) !important;">Effectivity Date</small>
                                    <strong style="color: var(--text-primary) !important;">{{ $salaryInfo->effectivity_date->format('M d, Y') }}</strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block mb-1" style="color: var(--text-secondary) !important;">Status</small>
                                    <span class="badge" style="background: {{ $salaryInfo->is_active ? '#10B981' : '#6B7280' }}">
                                        {{ $salaryInfo->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-wallet2" style="font-size: 3rem; opacity: 0.2;"></i>
                                    <p class="text-muted mt-3 mb-0" style="color: var(--text-secondary) !important;">No salary information set</p>
                                    <button class="btn btn-sm btn-primary mt-3" style="background: #3D3B6B; border: none;" data-bs-toggle="modal" data-bs-target="#salaryModal">
                                        <i class="bi bi-plus-circle me-1"></i>Set Salary Info
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Payroll History --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg) !important;">
                        <div class="card-header border-bottom py-3" style="background-color: var(--card-bg) !important;">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold" style="color: var(--text-primary) !important;">
                                    <i class="bi bi-clock-history me-2" style="color: #3D3B6B;"></i>
                                    Payment History
                                </h6>
                                <a href="{{ route('admin.finance.payroll.index') }}" class="btn btn-sm btn-outline-secondary">
                                    View All Payroll
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0" style="background-color: var(--card-bg) !important;">
                            @forelse($payrollHistory as $item)
                            <div class="p-3 border-bottom {{ $loop->last ? 'border-0' : '' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong class="d-block mb-1" style="color: var(--text-primary) !important;">{{ $item->payrollPeriod->period_label }}</strong>
                                        <small class="text-muted" style="color: var(--text-secondary) !important;">
                                            <i class="bi bi-calendar me-1"></i>{{ $item->payrollPeriod->date_from->format('M d') }} - {{ $item->payrollPeriod->date_to->format('M d, Y') }}
                                        </small>
                                        <div class="mt-2">
                                            <small class="text-muted" style="color: var(--text-secondary) !important;">Days: {{ $item->days_worked }}</small>
                                            <span class="mx-1">•</span>
                                            <small class="text-muted" style="color: var(--text-secondary) !important;">Hours: {{ $item->hours_worked }}</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $item->status === 'paid' ? 'success' : ($item->status === 'pending' ? 'warning' : 'primary') }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                        <div class="mt-2">
                                            <small class="text-muted d-block" style="color: var(--text-secondary) !important;">Gross: ₱{{ number_format($item->gross_pay, 2) }}</small>
                                            @if($item->deductions > 0)
                                                <small class="text-danger d-block">Deductions: -₱{{ number_format($item->deductions, 2) }}</small>
                                            @endif
                                            @if($item->bonuses > 0)
                                                <small class="text-success d-block">Bonuses: +₱{{ number_format($item->bonuses, 2) }}</small>
                                            @endif
                                            <strong class="d-block mt-1" style="color: #3D3B6B; font-size: 1.1rem;">₱{{ number_format($item->net_pay, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="p-5 text-center">
                                <i class="bi bi-receipt" style="font-size: 3rem; opacity: 0.2;"></i>
                                <p class="text-muted mb-0 mt-2" style="color: var(--text-secondary) !important;">No payment history yet</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Payroll Summary Stats --}}
                    @if($payrollHistory->count() > 0)
                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg) !important;">
                                <div class="card-body p-3 text-center">
                                    <div class="bg-success bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                                        <i class="bi bi-cash-stack fs-4 text-success"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0" style="color: var(--text-primary) !important;">₱{{ number_format($payrollHistory->sum('net_pay'), 2) }}</h5>
                                    <small class="text-muted" style="color: var(--text-secondary) !important;">Total Earned</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg) !important;">
                                <div class="card-body p-3 text-center">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                                        <i class="bi bi-calendar-check fs-4 text-primary"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0" style="color: var(--text-primary) !important;">{{ $payrollHistory->where('status', 'paid')->count() }}</h5>
                                    <small class="text-muted" style="color: var(--text-secondary) !important;">Payments Received</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg) !important;">
                                <div class="card-body p-3 text-center">
                                    <div class="bg-info bg-opacity-10 p-2 rounded-3 d-inline-block mb-2">
                                        <i class="bi bi-graph-up fs-4 text-info"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0" style="color: var(--text-primary) !important;">₱{{ number_format($payrollHistory->avg('net_pay'), 2) }}</h5>
                                    <small class="text-muted" style="color: var(--text-secondary) !important;">Average Pay</small>
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
        background-color: var(--bg-color);
    }
    
    .nav-tabs .nav-link.active {
        color: #3D3B6B;
        border-bottom-color: #3D3B6B;
        background-color: transparent;
        font-weight: 600;
    }
    
    .nav-tabs {
        border-bottom: 1px solid var(--border-color);
    }
</style>
@endpush

@push('scripts')
<script>
// Auto-open payroll tab if coming from staff index
document.addEventListener('DOMContentLoaded', function() {
    const activeTab = localStorage.getItem('activeTab');
    if (activeTab === 'payroll') {
        const payrollTab = document.getElementById('payroll-tab');
        if (payrollTab) {
            const tab = new bootstrap.Tab(payrollTab);
            tab.show();
        }
        localStorage.removeItem('activeTab');
    }
    
    // Handle hash in URL
    if (window.location.hash === '#payroll-tab') {
        const payrollTab = document.getElementById('payroll-tab');
        if (payrollTab) {
            const tab = new bootstrap.Tab(payrollTab);
            tab.show();
        }
    }
});
</script>
@endpush

{{-- Salary Information Modal --}}
<div class="modal fade" id="salaryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--card-bg);">
            <form action="{{ route('admin.staff.update-salary', $staff) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom" style="border-color: var(--border-color) !important;">
                    <h5 class="modal-title" style="color: var(--text-primary);">{{ $salaryInfo ? 'Update' : 'Set' }} Salary Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-primary);">Salary Type <span class="text-danger">*</span></label>
                        <select name="salary_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="monthly" {{ $salaryInfo && $salaryInfo->salary_type === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="daily" {{ $salaryInfo && $salaryInfo->salary_type === 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="hourly" {{ $salaryInfo && $salaryInfo->salary_type === 'hourly' ? 'selected' : '' }}>Hourly</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-primary);">Base Rate (₱) <span class="text-danger">*</span></label>
                        <input type="number" name="base_rate" class="form-control" step="0.01" min="0" value="{{ $salaryInfo ? $salaryInfo->base_rate : '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-primary);">Pay Period <span class="text-danger">*</span></label>
                        <select name="pay_period" class="form-select" required>
                            <option value="">Select Period</option>
                            <option value="weekly" {{ $salaryInfo && $salaryInfo->pay_period === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="bi-weekly" {{ $salaryInfo && $salaryInfo->pay_period === 'bi-weekly' ? 'selected' : '' }}>Bi-Weekly</option>
                            <option value="monthly" {{ $salaryInfo && $salaryInfo->pay_period === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-primary);">Effectivity Date <span class="text-danger">*</span></label>
                        <input type="date" name="effectivity_date" class="form-control" value="{{ $salaryInfo ? $salaryInfo->effectivity_date->format('Y-m-d') : date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer border-top" style="border-color: var(--border-color) !important;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background: #3D3B6B; border: none;">Save Salary Info</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
