@extends('branch.layouts.app')

@section('page-title', 'Attendance Management')

@push('styles')
<style>
    .card {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
    }
    .card-body, .card-header {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    .card-header.bg-white {
        background: var(--card-bg) !important;
    }
    .table {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
    }
    .table thead th, .table tbody td {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    .table tbody tr:hover td {
        background: var(--hover-bg, rgba(0,0,0,0.05)) !important;
    }
    [data-theme="dark"] .table tbody tr:hover td {
        background: rgba(255,255,255,0.05) !important;
    }
    .modal-content {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
    }
    .modal-header, .modal-footer {
        border-color: var(--border-color) !important;
    }
    .form-control, .form-select, textarea {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Attendance Management</h2>
        <div class="d-flex gap-2">
            <input type="date" class="form-control" id="attendanceDate" value="{{ $date }}" onchange="window.location.href='?date='+this.value">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#timeInModal">
                <i class="bi bi-clock"></i> Time In
            </button>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#manualEntryModal">
                <i class="bi bi-pencil"></i> Manual Entry
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#leaveRequestModal">
                <i class="bi bi-calendar-plus"></i> Request Leave
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="bi bi-people fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1" style="color: var(--text-secondary);">Total Staff</h6>
                            <h3 class="mb-0">{{ $summary['total_staff'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="bi bi-check-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1" style="color: var(--text-secondary);">Present</h6>
                            <h3 class="mb-0">{{ $summary['present'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 text-danger rounded p-3">
                                <i class="bi bi-x-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1" style="color: var(--text-secondary);">Absent</h6>
                            <h3 class="mb-0">{{ $summary['absent'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1" style="color: var(--text-secondary);">Late</h6>
                            <h3 class="mb-0">{{ $summary['late'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Attendance Table --}}
    <div class="card border-0 shadow-sm attendance-card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Today's Attendance</h5>
        </div>
        <div class="card-body attendance-card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Hours</th>
                            <th>Status</th>
                            <th>Shift</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                        {{ substr($attendance->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $attendance->user->name }}</div>
                                        <small style="color: var(--text-secondary);">{{ $attendance->user->email }}</small>
                                    </div>
                                </div>
                            </td>
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
                            <td>
                                @if(!$attendance->time_out)
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#timeOutModal" onclick="setTimeOutId({{ $attendance->id }})">
                                    <i class="bi bi-clock"></i> Time Out
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-inbox fs-1" style="color: var(--text-secondary);"></i>
                                <p class="mt-2" style="color: var(--text-secondary);">No attendance records for this date</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Time In Modal --}}
<div class="modal fade" id="timeInModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('branch.attendance.time-in') }}" method="POST" enctype="multipart/form-data" id="timeInForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Time In Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Staff Member ({{ $staff->count() }} available)</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Select Staff</option>
                            @foreach($staff as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Take Photo <span class="text-danger">*</span></label>
                        <div class="text-center">
                            <video id="camera" width="100%" height="300" autoplay style="display:none; border-radius: 8px;"></video>
                            <canvas id="canvas" style="display:none;"></canvas>
                            <img id="photo" style="display:none; width:100%; border-radius: 8px;" />
                            <input type="hidden" name="photo_data" id="photoData" required>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-primary" id="startCamera" onclick="window.startCamera()">
                                <i class="bi bi-camera"></i> Open Camera
                            </button>
                            <button type="button" class="btn btn-success" id="captureBtn" onclick="window.capturePhoto()" style="display:none;">
                                <i class="bi bi-camera-fill"></i> Capture
                            </button>
                            <button type="button" class="btn btn-warning" id="retakeBtn" onclick="window.retakePhoto()" style="display:none;">
                                <i class="bi bi-arrow-clockwise"></i> Retake
                            </button>
                        </div>
                        <small style="color: var(--text-secondary);">Photo is required for time-in verification</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitTimeIn">Time In</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Manual Entry Modal --}}
<div class="modal fade" id="manualEntryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('branch.attendance.manual-entry') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Manual Attendance Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Staff Member ({{ $staff->count() }} available)</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Select Staff</option>
                            @foreach($staff as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
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
                        <textarea name="notes" class="form-control" rows="2"></textarea>
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

{{-- Time Out Modal --}}
<div class="modal fade" id="timeOutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="timeOutForm">
                @csrf
                <input type="hidden" id="attendanceId" name="attendance_id">
                <div class="modal-header">
                    <h5 class="modal-title">Time Out Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Take Photo <span class="text-danger">*</span></label>
                        <div class="text-center">
                            <video id="cameraOut" width="100%" height="300" autoplay style="display:none; border-radius: 8px;"></video>
                            <canvas id="canvasOut" style="display:none;"></canvas>
                            <img id="photoOut" style="display:none; width:100%; border-radius: 8px;" />
                            <input type="hidden" name="photo_data" id="photoDataOut" required>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-primary" id="startCameraOut" onclick="window.startCameraOut()">
                                <i class="bi bi-camera"></i> Open Camera
                            </button>
                            <button type="button" class="btn btn-success" id="captureBtnOut" onclick="window.capturePhotoOut()" style="display:none;">
                                <i class="bi bi-camera-fill"></i> Capture
                            </button>
                            <button type="button" class="btn btn-warning" id="retakeBtnOut" onclick="window.retakePhotoOut()" style="display:none;">
                                <i class="bi bi-arrow-clockwise"></i> Retake
                            </button>
                        </div>
                        <small style="color: var(--text-secondary);">Photo is required for time-out verification</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitTimeOut()">Time Out</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Leave Request Modal --}}
<div class="modal fade" id="leaveRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('branch.attendance.submit-leave') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Submit Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Staff Member <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Select Staff</option>
                            @foreach($staff as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">From Date <span class="text-danger">*</span></label>
                            <input type="date" name="leave_date_from" class="form-control" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">To Date <span class="text-danger">*</span></label>
                            <input type="date" name="leave_date_to" class="form-control" required min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="sick">Sick Leave</option>
                            <option value="vacation">Vacation Leave</option>
                            <option value="emergency">Emergency Leave</option>
                            <option value="unpaid">Unpaid Leave</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Please provide a detailed reason for your leave request..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Attachment (Optional)</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <small style="color: var(--text-secondary);">Upload medical certificate or supporting document (PDF, JPG, PNG - Max 2MB)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Define camera functions globally IMMEDIATELY (not inside DOMContentLoaded)
let stream = null;
let streamOut = null;

// Time In Camera Functions
window.startCamera = function() {
    const video = document.getElementById('camera');
    const startBtn = document.getElementById('startCamera');
    const captureBtn = document.getElementById('captureBtn');

    // Try to access camera
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'user',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        })
        .then(function(mediaStream) {
            stream = mediaStream;
            video.srcObject = stream;
            video.style.display = 'block';
            startBtn.style.display = 'none';
            captureBtn.style.display = 'inline-block';
        })
        .catch(function(err) {
            console.error('Camera error:', err);
            alert('Camera access denied or not available: ' + err.message);
        });
    } else {
        alert('Camera access is not supported in this browser. Please use a modern browser.');
    }
};

window.capturePhoto = function() {
    const video = document.getElementById('camera');
    const canvas = document.getElementById('canvas');
    const photo = document.getElementById('photo');
    const photoData = document.getElementById('photoData');
    const captureBtn = document.getElementById('captureBtn');
    const retakeBtn = document.getElementById('retakeBtn');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0);

    const imageData = canvas.toDataURL('image/jpeg', 0.8);
    photo.src = imageData;
    photo.style.display = 'block';
    photoData.value = imageData;

    video.style.display = 'none';
    captureBtn.style.display = 'none';
    retakeBtn.style.display = 'inline-block';

    // Stop camera
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
};

window.retakePhoto = function() {
    const photo = document.getElementById('photo');
    const photoData = document.getElementById('photoData');
    const retakeBtn = document.getElementById('retakeBtn');
    const startBtn = document.getElementById('startCamera');

    photo.style.display = 'none';
    photo.src = '';
    photoData.value = '';
    retakeBtn.style.display = 'none';
    startBtn.style.display = 'inline-block';
};

// Time Out Camera Functions
window.startCameraOut = function() {
    const video = document.getElementById('cameraOut');
    const startBtn = document.getElementById('startCameraOut');
    const captureBtn = document.getElementById('captureBtnOut');

    // Try to access camera
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'user',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        })
        .then(function(mediaStream) {
            streamOut = mediaStream;
            video.srcObject = streamOut;
            video.style.display = 'block';
            startBtn.style.display = 'none';
            captureBtn.style.display = 'inline-block';
        })
        .catch(function(err) {
            console.error('Camera error:', err);
            alert('Camera access denied or not available: ' + err.message);
        });
    } else {
        alert('Camera access is not supported in this browser. Please use a modern browser.');
    }
};

window.capturePhotoOut = function() {
    const video = document.getElementById('cameraOut');
    const canvas = document.getElementById('canvasOut');
    const photo = document.getElementById('photoOut');
    const photoData = document.getElementById('photoDataOut');
    const captureBtn = document.getElementById('captureBtnOut');
    const retakeBtn = document.getElementById('retakeBtnOut');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0);

    const imageData = canvas.toDataURL('image/jpeg', 0.8);
    photo.src = imageData;
    photo.style.display = 'block';
    photoData.value = imageData;

    video.style.display = 'none';
    captureBtn.style.display = 'none';
    retakeBtn.style.display = 'inline-block';

    // Stop camera
    if (streamOut) {
        streamOut.getTracks().forEach(track => track.stop());
    }
};

window.retakePhotoOut = function() {
    const photo = document.getElementById('photoOut');
    const photoData = document.getElementById('photoDataOut');
    const retakeBtn = document.getElementById('retakeBtnOut');
    const startBtn = document.getElementById('startCameraOut');

    photo.style.display = 'none';
    photo.src = '';
    photoData.value = '';
    retakeBtn.style.display = 'none';
    startBtn.style.display = 'inline-block';
};

window.setTimeOutId = function(attendanceId) {
    document.getElementById('attendanceId').value = attendanceId;
};

window.submitTimeOut = function() {
    const attendanceId = document.getElementById('attendanceId').value;
    const photoData = document.getElementById('photoDataOut').value;

    if (!photoData) {
        alert('Please take a photo before submitting');
        return;
    }

    fetch(`/branch/attendance/${attendanceId}/time-out`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            photo_data: photoData
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
};

window.timeOut = function(attendanceId) {
    if(confirm('Record time out for this staff member?')) {
        fetch(`/branch/attendance/${attendanceId}/time-out`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
};

// Clean up camera when modals are closed
document.addEventListener('DOMContentLoaded', function() {
    const timeInModal = document.getElementById('timeInModal');
    const timeOutModal = document.getElementById('timeOutModal');

    if (timeInModal) {
        timeInModal.addEventListener('hidden.bs.modal', function () {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            const video = document.getElementById('camera');
            const photo = document.getElementById('photo');
            const startBtn = document.getElementById('startCamera');
            const captureBtn = document.getElementById('captureBtn');
            const retakeBtn = document.getElementById('retakeBtn');
            const photoData = document.getElementById('photoData');

            if (video) video.style.display = 'none';
            if (photo) photo.style.display = 'none';
            if (startBtn) startBtn.style.display = 'inline-block';
            if (captureBtn) captureBtn.style.display = 'none';
            if (retakeBtn) retakeBtn.style.display = 'none';
            if (photoData) photoData.value = '';
        });
    }

    if (timeOutModal) {
        timeOutModal.addEventListener('hidden.bs.modal', function () {
            if (streamOut) {
                streamOut.getTracks().forEach(track => track.stop());
                streamOut = null;
            }
            const video = document.getElementById('cameraOut');
            const photo = document.getElementById('photoOut');
            const startBtn = document.getElementById('startCameraOut');
            const captureBtn = document.getElementById('captureBtnOut');
            const retakeBtn = document.getElementById('retakeBtnOut');
            const photoData = document.getElementById('photoDataOut');

            if (video) video.style.display = 'none';
            if (photo) photo.style.display = 'none';
            if (startBtn) startBtn.style.display = 'inline-block';
            if (captureBtn) captureBtn.style.display = 'none';
            if (retakeBtn) retakeBtn.style.display = 'none';
            if (photoData) photoData.value = '';
        });
    }
});
</script>
@endsection
