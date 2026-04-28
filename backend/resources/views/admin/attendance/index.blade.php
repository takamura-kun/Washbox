@extends('admin.layouts.app')

@section('page-title', 'Attendance Management')

@push('styles')
<style>
    /* Force table theme support */
    .table-responsive,
    .table,
    .table tbody,
    .table tbody tr,
    .table tbody tr td,
    .table thead,
    .table thead tr,
    .table thead tr th,
    .table tfoot,
    .table tfoot tr,
    .table tfoot tr th {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    
    .table thead {
        background: var(--border-color) !important;
    }
    
    .table tfoot {
        background: var(--border-color) !important;
    }
    
    .table tbody tr:hover {
        background: var(--border-color) !important;
        opacity: 0.95;
    }
    
    /* Ensure card body has proper background */
    .card-body {
        background: var(--card-bg) !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-size: 1.5rem;">Attendance Management</h2>
            <p class="text-muted small mb-0" style="font-size: 0.8rem;">Monitor and verify staff attendance across all branches</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.leave-requests.index') }}" class="btn btn-warning shadow-sm">
                <i class="bi bi-calendar-x me-2"></i>Leave Requests
                @if($summary['pending_leaves'] ?? 0 > 0)
                    <span class="badge bg-danger ms-1">{{ $summary['pending_leaves'] }}</span>
                @endif
            </a>
            <a href="{{ route('admin.attendance.report') }}" class="btn btn-info shadow-sm">
                <i class="bi bi-file-earmark-bar-graph me-2"></i>Reports
            </a>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#manualEntryModal">
                <i class="bi bi-pencil me-2"></i>Manual Entry
            </button>
        </div>
    </div>

    {{-- Quick Navigation --}}
    <div class="btn-group mb-3 w-100" role="group" style="box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-info">
            <i class="bi bi-people me-1"></i>Staff List
        </a>
        <a href="{{ route('admin.finance.payroll.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-list-ul me-1"></i>Payroll Periods
        </a>
        <a href="{{ route('admin.staff.salary-management') }}" class="btn btn-outline-success">
            <i class="bi bi-cash-stack me-1"></i>Salary Management
        </a>
        <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">
            <i class="bi bi-calendar-check me-1"></i>Attendance
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="card metric-card-compact border-0 shadow-sm h-100" style="border-left: 3px solid #10b981 !important;">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size: 0.7rem; color: var(--text-secondary);">Present Today</span>
                        <i class="bi bi-check-circle text-success" style="font-size: 1rem;"></i>
                    </div>
                    <h3 class="mb-0 fw-bold" style="font-size: 1.5rem;">{{ $summary['present'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card metric-card-compact border-0 shadow-sm h-100" style="border-left: 3px solid #ef4444 !important;">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size: 0.7rem; color: var(--text-secondary);">Absent Today</span>
                        <i class="bi bi-x-circle text-danger" style="font-size: 1rem;"></i>
                    </div>
                    <h3 class="mb-0 fw-bold" style="font-size: 1.5rem;">{{ $summary['absent'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card metric-card-compact border-0 shadow-sm h-100" style="border-left: 3px solid #f59e0b !important;">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size: 0.7rem; color: var(--text-secondary);">Late Today</span>
                        <i class="bi bi-clock-history text-warning" style="font-size: 1rem;"></i>
                    </div>
                    <h3 class="mb-0 fw-bold" style="font-size: 1.5rem;">{{ $summary['late'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card metric-card-compact border-0 shadow-sm h-100" style="border-left: 3px solid #3b82f6 !important;">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size: 0.7rem; color: var(--text-secondary);">On Leave</span>
                        <i class="bi bi-calendar-check text-info" style="font-size: 1rem;"></i>
                    </div>
                    <h3 class="mb-0 fw-bold" style="font-size: 1.5rem;">{{ $summary['on_leave'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1" style="font-size: 0.75rem;">Date</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1" style="font-size: 0.75rem;">Branch</label>
                    <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1" style="font-size: 0.75rem;">Status</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Present</option>
                        <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Late</option>
                        <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="on_leave" {{ request('status') === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-light border btn-sm w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i>Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Attendance Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--border-color);">
            <h6 class="mb-0" style="font-size: 0.9rem;">Attendance Records - {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</h6>
            <div>
                <button class="btn btn-sm btn-outline-primary" onclick="selectAll()" style="font-size: 0.75rem;">
                    <i class="bi bi-check-square"></i> Select All
                </button>
                <button class="btn btn-sm btn-success" onclick="bulkVerify()" style="font-size: 0.75rem;">
                    <i class="bi bi-check-circle"></i> Verify Selected
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="40" style="padding: 8px; font-size: 0.8rem;"><input type="checkbox" id="selectAllCheckbox" onclick="selectAll()"></th>
                            <th style="padding: 8px; font-size: 0.8rem;">Staff</th>
                            <th style="padding: 8px; font-size: 0.8rem;">Branch</th>
                            <th style="padding: 8px; font-size: 0.8rem;">Time In</th>
                            <th style="padding: 8px; font-size: 0.8rem;">Time Out</th>
                            <th style="padding: 8px; font-size: 0.8rem;">Hours</th>
                            <th style="padding: 8px; font-size: 0.8rem;">Status</th>
                            <th style="padding: 8px; font-size: 0.8rem;">Shift</th>
                            <th style="padding: 8px; font-size: 0.8rem;">Verified</th>
                            <th style="padding: 8px; font-size: 0.8rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                        <tr>
                            <td style="padding: 8px;">
                                <input type="checkbox" class="attendance-checkbox" value="{{ $attendance->id }}">
                            </td>
                            <td style="padding: 8px; font-size: 0.8rem;">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.75rem;">
                                        {{ substr($attendance->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="font-size: 0.8rem;">{{ $attendance->user->name }}</div>
                                        <small class="text-muted" style="font-size: 0.7rem;">{{ $attendance->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 8px; font-size: 0.8rem;">
                                <span class="badge bg-light text-dark border" style="font-size: 0.7rem;">
                                    {{ $attendance->branch->name }}
                                </span>
                            </td>
                            <td style="padding: 8px; font-size: 0.8rem;">
                                {{ $attendance->formatted_time_in }}
                                @if($attendance->time_in_photo)
                                    <a href="{{ asset('storage/' . $attendance->time_in_photo) }}" target="_blank" class="ms-1">
                                        <i class="bi bi-camera text-primary"></i>
                                    </a>
                                @endif
                            </td>
                            <td style="padding: 8px; font-size: 0.8rem;">
                                {{ $attendance->formatted_time_out ?? '-' }}
                                @if($attendance->time_out_photo)
                                    <a href="{{ asset('storage/' . $attendance->time_out_photo) }}" target="_blank" class="ms-1">
                                        <i class="bi bi-camera text-primary"></i>
                                    </a>
                                @endif
                            </td>
                            <td style="padding: 8px; font-size: 0.8rem;">{{ number_format($attendance->hours_worked, 2) }}h</td>
                            <td style="padding: 8px; font-size: 0.8rem;">
                                <span class="badge bg-{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : ($attendance->status === 'on_leave' ? 'info' : 'danger')) }}" style="font-size: 0.7rem;">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                            <td style="padding: 8px; font-size: 0.8rem;">
                                <span class="badge bg-{{ $attendance->shift_type === 'full_day' ? 'primary' : 'secondary' }}" style="font-size: 0.7rem;">
                                    {{ ucfirst(str_replace('_', ' ', $attendance->shift_type)) }}
                                </span>
                            </td>
                            <td style="padding: 8px; font-size: 0.8rem;">
                                @if($attendance->is_verified)
                                    <span class="badge bg-success" style="font-size: 0.7rem;">
                                        <i class="bi bi-check-circle"></i> Verified
                                    </span>
                                @else
                                    <span class="badge bg-warning" style="font-size: 0.7rem;">
                                        <i class="bi bi-clock"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 8px; font-size: 0.8rem;">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewPhotos({{ $attendance->id }}, '{{ $attendance->time_in_photo }}', '{{ $attendance->time_out_photo }}', '{{ $attendance->user->name }}', '{{ $attendance->formatted_time_in }}', '{{ $attendance->formatted_time_out }}')" title="View Photos">
                                        <i class="bi bi-camera"></i>
                                    </button>
                                    @if(!$attendance->is_verified)
                                        <form action="{{ route('admin.attendance.verify', $attendance) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Verify">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <button class="btn btn-sm btn-outline-info" onclick="viewDetails({{ $attendance->id }})" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5" style="padding: 8px; font-size: 0.8rem;">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No attendance records for this date</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($attendances->hasPages())
            <div class="p-3 border-top">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Photo Preview Modal --}}
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content attendance-card">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-camera me-2"></i>
                    Attendance Photos - <span id="staffName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body attendance-card-body">
                <div class="row g-4">
                    {{-- Time In Photo --}}
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm attendance-card">
                            <div class="card-header bg-success bg-opacity-10 text-success">
                                <h6 class="mb-0">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Time In Photo
                                </h6>
                                <small id="timeInLabel"></small>
                            </div>
                            <div class="card-body attendance-card-body p-0">
                                <div id="timeInPhotoContainer" class="text-center p-4 photo-container">
                                    <img id="timeInPhoto" src="" class="img-fluid rounded" style="max-height: 400px; object-fit: contain;">
                                    <p id="noTimeInPhoto" class="text-muted mt-3" style="display: none;">
                                        <i class="bi bi-camera-video-off fs-1"></i>
                                        <br>No photo available
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Time Out Photo --}}
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm attendance-card">
                            <div class="card-header bg-danger bg-opacity-10 text-danger">
                                <h6 class="mb-0">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Time Out Photo
                                </h6>
                                <small id="timeOutLabel"></small>
                            </div>
                            <div class="card-body attendance-card-body p-0">
                                <div id="timeOutPhotoContainer" class="text-center p-4 photo-container">
                                    <img id="timeOutPhoto" src="" class="img-fluid rounded" style="max-height: 400px; object-fit: contain;">
                                    <p id="noTimeOutPhoto" class="text-muted mt-3" style="display: none;">
                                        <i class="bi bi-camera-video-off fs-1"></i>
                                        <br>No photo available
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Verification Info --}}
                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Verification Tips:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Verify that the person in both photos is the same staff member</li>
                        <li>Check if the photos were taken at appropriate times</li>
                        <li>Look for any signs of buddy punching or fraud</li>
                        <li>Ensure photos are clear and not manipulated</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Close
                </button>
                <button type="button" class="btn btn-success" id="verifyFromModal" onclick="verifyAttendance()">
                    <i class="bi bi-check-circle me-2"></i>Verify Attendance
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Manual Entry Modal --}}
<div class="modal fade" id="manualEntryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.attendance.manual-entry') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Manual Attendance Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Staff Member</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Select Staff</option>
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}">{{ $member->name }} - {{ $member->branch->name ?? 'No Branch' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" class="form-select" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="attendance_date" class="form-control" value="{{ $date }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Time In</label>
                            <input type="time" name="time_in" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Time Out</label>
                            <input type="time" name="time_out" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="half_day">Half Day</option>
                            <option value="absent">Absent</option>
                            <option value="on_leave">On Leave</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentAttendanceId = null;

function viewPhotos(id, timeInPhoto, timeOutPhoto, staffName, timeIn, timeOut) {
    currentAttendanceId = id;

    // Set staff name
    document.getElementById('staffName').textContent = staffName;
    document.getElementById('timeInLabel').textContent = timeIn || 'N/A';
    document.getElementById('timeOutLabel').textContent = timeOut || 'Not yet';

    // Handle Time In Photo
    const timeInPhotoEl = document.getElementById('timeInPhoto');
    const noTimeInPhotoEl = document.getElementById('noTimeInPhoto');

    if (timeInPhoto && timeInPhoto !== '') {
        timeInPhotoEl.src = '/storage/' + timeInPhoto;
        timeInPhotoEl.style.display = 'block';
        noTimeInPhotoEl.style.display = 'none';
    } else {
        timeInPhotoEl.style.display = 'none';
        noTimeInPhotoEl.style.display = 'block';
    }

    // Handle Time Out Photo
    const timeOutPhotoEl = document.getElementById('timeOutPhoto');
    const noTimeOutPhotoEl = document.getElementById('noTimeOutPhoto');

    if (timeOutPhoto && timeOutPhoto !== '') {
        timeOutPhotoEl.src = '/storage/' + timeOutPhoto;
        timeOutPhotoEl.style.display = 'block';
        noTimeOutPhotoEl.style.display = 'none';
    } else {
        timeOutPhotoEl.style.display = 'none';
        noTimeOutPhotoEl.style.display = 'block';
    }

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('photoModal'));
    modal.show();
}

function verifyAttendance() {
    if (!currentAttendanceId) {
        alert('No attendance selected');
        return;
    }

    if (!confirm('Verify this attendance record?')) {
        return;
    }

    // Create a form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/attendance/${currentAttendanceId}/verify`;

    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);

    document.body.appendChild(form);
    form.submit();
}

function selectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.attendance-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

function bulkVerify() {
    const checkboxes = document.querySelectorAll('.attendance-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);

    if (ids.length === 0) {
        alert('Please select at least one attendance record to verify');
        return;
    }

    if (!confirm(`Verify ${ids.length} attendance record(s)?`)) {
        return;
    }

    fetch('{{ route("admin.attendance.bulk-verify") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ attendance_ids: ids })
    })
    .then(response => response.json())
    .then(data => {
        location.reload();
    })
    .catch(error => {
        alert('Error verifying attendance records');
    });
}

function viewDetails(id) {
    // You can implement a modal to show full attendance details
    alert('View details for attendance ID: ' + id);
}
</script>
@endpush
@endsection
