@extends('branch.layouts.app')

@section('page-title', 'Attendance Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Attendance Report</h2>
        <a href="{{ route('branch.attendance.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Back to Attendance
        </a>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Staff Member</label>
                    <select name="user_id" class="form-select">
                        <option value="">All Staff</option>
                        @foreach($staff as $member)
                        <option value="{{ $member->id }}" {{ $userId == $member->id ? 'selected' : '' }}>
                            {{ $member->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $summary['total_days'] }}</h3>
                    <small class="text-muted">Total Days</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-success">{{ $summary['present_days'] }}</h3>
                    <small class="text-muted">Present</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-danger">{{ $summary['absent_days'] }}</h3>
                    <small class="text-muted">Absent</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-info">{{ $summary['half_days'] }}</h3>
                    <small class="text-muted">Half Days</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($summary['total_hours'], 1) }}h</h3>
                    <small class="text-muted">Total Hours</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-0">₱{{ number_format($summary['total_pay'], 0) }}</h3>
                    <small class="text-muted">Total Pay</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Attendance Records --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Staff</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Hours</th>
                            <th>Status</th>
                            <th>Shift</th>
                            <th>Daily Pay</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('M d, Y') }}</td>
                            <td>{{ $attendance->user->name }}</td>
                            <td>{{ $attendance->formatted_time_in }}</td>
                            <td>{{ $attendance->formatted_time_out ?? '-' }}</td>
                            <td>{{ number_format($attendance->hours_worked, 2) }}h</td>
                            <td>
                                <span class="badge bg-{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $attendance->shift_type === 'full_day' ? 'primary' : 'info' }}">
                                    {{ ucfirst(str_replace('_', ' ', $attendance->shift_type)) }}
                                </span>
                            </td>
                            <td>₱{{ number_format($attendance->calculateDailyPay(), 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No attendance records found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
