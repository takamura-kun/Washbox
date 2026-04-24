@extends('branch.layouts.app')

@section('page-title', 'Dashboard')

@push('styles')
<style>
    :root {
        --primary: #2563eb;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #06b6d4;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-900: #111827;
    }

    body {
        background: var(--gray-50);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        font-size: 14px;
        color: var(--gray-900);
    }

    /* Header */
    .dashboard-header {
        background: white;
        border-bottom: 1px solid var(--gray-200);
        padding: 16px 24px;
        margin: -24px -24px 24px -24px;
    }

    .page-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
    }

    .page-subtitle {
        font-size: 13px;
        color: var(--gray-600);
        margin: 0;
    }

    /* Search Bar */
    .search-container {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 20px;
    }

    .search-input {
        border: 1px solid var(--gray-300);
        border-radius: 6px;
        padding: 8px 12px 8px 36px;
        font-size: 14px;
        width: 100%;
        transition: all 0.2s;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-600);
    }

    /* Stats Cards */
    .stat-card {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        padding: 16px;
        transition: all 0.2s;
    }

    .stat-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .stat-label {
        font-size: 13px;
        color: var(--gray-600);
        font-weight: 500;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 4px;
    }

    .stat-change {
        font-size: 12px;
        font-weight: 500;
    }

    .stat-change.positive {
        color: var(--success);
    }

    .stat-change.negative {
        color: var(--danger);
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    /* Action Cards */
    .action-card {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .action-card:hover {
        border-color: var(--primary);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .action-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 12px;
    }

    .action-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 4px;
    }

    .action-desc {
        font-size: 12px;
        color: var(--gray-600);
        margin: 0;
    }

    /* Table */
    .data-table {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        overflow: hidden;
    }

    .table-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--gray-200);
    }

    .table-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
    }

    .table {
        margin: 0;
        font-size: 13px;
    }

    .table thead th {
        background: var(--gray-50);
        border-bottom: 1px solid var(--gray-200);
        color: var(--gray-700);
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 20px;
    }

    .table tbody td {
        padding: 12px 20px;
        border-bottom: 1px solid var(--gray-100);
        vertical-align: middle;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .table tbody tr:hover {
        background: var(--gray-50);
    }

    /* Badge */
    .badge-custom {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }

    /* Button */
    .btn-custom {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary-custom {
        background: var(--primary);
        color: white;
    }

    .btn-primary-custom:hover {
        background: #1d4ed8;
    }

    .btn-outline-custom {
        background: white;
        border: 1px solid var(--gray-300);
        color: var(--gray-700);
    }

    .btn-outline-custom:hover {
        background: var(--gray-50);
    }

    /* Modal */
    .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        border-bottom: 1px solid var(--gray-200);
        padding: 20px 24px;
    }

    .modal-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--gray-900);
    }

    .modal-body {
        padding: 24px;
    }

    .modal-footer {
        border-top: 1px solid var(--gray-200);
        padding: 16px 24px;
    }

    /* Form */
    .form-label {
        font-size: 13px;
        font-weight: 500;
        color: var(--gray-700);
        margin-bottom: 6px;
    }

    .form-control, .form-select {
        border: 1px solid var(--gray-300);
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 14px;
        transition: all 0.2s;
    }

    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    /* Floating Action Button */
    .fab {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        border: none;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: all 0.2s;
        z-index: 1000;
    }

    .fab:hover {
        background: #1d4ed8;
        transform: scale(1.05);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 12px 16px;
            margin: -16px -16px 16px -16px;
        }

        .page-title {
            font-size: 18px;
        }

        .stat-value {
            font-size: 20px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    
    {{-- Header --}}
    <div class="dashboard-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Welcome back, {{ auth()->user()->name }}</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn-outline-custom btn-custom">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <button class="btn-primary-custom btn-custom">
                    <i class="bi bi-plus-lg me-1"></i> New Order
                </button>
            </div>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="search-container">
        <div class="position-relative">
            <i class="bi bi-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search by tracking number, customer name, or phone..." id="quickSearch">
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Today's Revenue</div>
                        <div class="stat-value">₱{{ number_format($kpis['today_revenue']['value'] ?? 0) }}</div>
                        <div class="stat-change positive">
                            <i class="bi bi-arrow-up"></i> 12% from yesterday
                        </div>
                    </div>
                    <div class="stat-icon" style="background: #dbeafe; color: var(--primary);">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Active Orders</div>
                        <div class="stat-value">{{ $kpis['active_laundries']['value'] ?? 0 }}</div>
                        <div class="stat-change positive">
                            <i class="bi bi-arrow-up"></i> 8 new today
                        </div>
                    </div>
                    <div class="stat-icon" style="background: #d1fae5; color: var(--success);">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Ready for Pickup</div>
                        <div class="stat-value">{{ $kpis['ready_for_pickup']['value'] ?? 0 }}</div>
                        <div class="stat-change">
                            <i class="bi bi-clock"></i> Avg 2.3 days wait
                        </div>
                    </div>
                    <div class="stat-icon" style="background: #fef3c7; color: var(--warning);">
                        <i class="bi bi-bag-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Completed Today</div>
                        <div class="stat-value">{{ $kpis['completed_today']['value'] ?? 0 }}</div>
                        <div class="stat-change positive">
                            <i class="bi bi-check-circle"></i> On track
                        </div>
                    </div>
                    <div class="stat-icon" style="background: #e0e7ff; color: #4f46e5;">
                        <i class="bi bi-check2-all"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <a href="{{ route('branch.laundries.create') }}" class="action-card">
                <div class="action-icon" style="background: #dbeafe; color: var(--primary);">
                    <i class="bi bi-plus-circle"></i>
                </div>
                <div class="action-title">Create Order</div>
                <p class="action-desc">Start a new laundry order</p>
            </a>
        </div>

        <div class="col-md-3">
            <div class="action-card" data-bs-toggle="modal" data-bs-target="#attendanceModal">
                <div class="action-icon" style="background: #d1fae5; color: var(--success);">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="action-title">Time In/Out</div>
                <p class="action-desc">Mark staff attendance</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="action-card" data-bs-toggle="modal" data-bs-target="#paymentModal">
                <div class="action-icon" style="background: #fef3c7; color: var(--warning);">
                    <i class="bi bi-credit-card"></i>
                </div>
                <div class="action-title">Collect Payment</div>
                <p class="action-desc">Process customer payment</p>
            </div>
        </div>

        <div class="col-md-3">
            <a href="{{ route('branch.customers.create') }}" class="action-card">
                <div class="action-icon" style="background: #e0e7ff; color: #4f46e5;">
                    <i class="bi bi-person-plus"></i>
                </div>
                <div class="action-title">Add Customer</div>
                <p class="action-desc">Register new customer</p>
            </a>
        </div>
    </div>

    {{-- Recent Orders Table --}}
    <div class="data-table mb-4">
        <div class="table-header d-flex justify-content-between align-items-center">
            <h2 class="table-title">Recent Orders</h2>
            <a href="{{ route('branch.laundries.index') }}" class="btn-outline-custom btn-custom">
                View All <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tracking #</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_laundries ?? [] as $laundry)
                    <tr>
                        <td><strong>{{ $laundry->tracking_number }}</strong></td>
                        <td>{{ $laundry->customer->name ?? 'N/A' }}</td>
                        <td>{{ $laundry->service->name ?? '—' }}</td>
                        <td><strong>₱{{ number_format($laundry->total_amount, 2) }}</strong></td>
                        <td>
                            <span class="badge-custom badge-{{ $laundry->status === 'completed' ? 'success' : ($laundry->status === 'ready' ? 'warning' : 'info') }}">
                                {{ ucfirst($laundry->status) }}
                            </span>
                        </td>
                        <td>{{ $laundry->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('branch.laundries.show', $laundry->id) }}" class="btn-outline-custom btn-custom btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No orders yet
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Attendance Modal --}}
<div class="modal fade" id="attendanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Time In Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Staff Member</label>
                    <select class="form-select" id="staffSelect">
                        <option value="">Select Staff</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Take Photo <span class="text-danger">*</span></label>
                    <div class="text-center">
                        <video id="attendanceVideo" width="100%" height="300" autoplay style="border-radius: 8px; background: #000;"></video>
                        <canvas id="attendanceCanvas" style="display:none;"></canvas>
                        <img id="capturedPhoto" style="display:none; width:100%; border-radius: 8px;" />
                    </div>
                    <div class="mt-3 d-grid gap-2">
                        <button type="button" class="btn-primary-custom btn-custom" id="captureBtn">
                            <i class="bi bi-camera-fill me-2"></i>Capture Photo
                        </button>
                        <button type="button" class="btn-outline-custom btn-custom" id="retakeBtn" style="display:none;">
                            <i class="bi bi-arrow-clockwise me-2"></i>Retake
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline-custom btn-custom" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-primary-custom btn-custom" id="timeInBtn">Time In</button>
            </div>
        </div>
    </div>
</div>

{{-- Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Collect Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tracking Number</label>
                    <input type="text" class="form-control" placeholder="Enter or scan tracking number">
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <select class="form-select">
                        <option>Cash</option>
                        <option>GCash</option>
                        <option>Card</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline-custom btn-custom" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-primary-custom btn-custom">Process Payment</button>
            </div>
        </div>
    </div>
</div>

{{-- Floating Action Button --}}
<button class="fab" data-bs-toggle="dropdown">
    <i class="bi bi-lightning-charge-fill fs-5"></i>
</button>
<ul class="dropdown-menu dropdown-menu-end shadow">
    <li><h6 class="dropdown-header">Quick Actions</h6></li>
    <li><a class="dropdown-item" href="#" onclick="document.getElementById('quickSearch').focus()">
        <i class="bi bi-search me-2"></i>Search Order
    </a></li>
    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="# paymentModal">
        <i class="bi bi-cash-coin me-2"></i>Collect Payment
    </a></li>
    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#attendanceModal">
        <i class="bi bi-clock me-2"></i>Time In/Out
    </a></li>
</ul>

@endsection

@push('scripts')
<script>
    // Attendance Modal Handler
    let videoStream = null;
    const attendanceModal = document.getElementById('attendanceModal');
    const video = document.getElementById('attendanceVideo');
    const canvas = document.getElementById('attendanceCanvas');
    const capturedPhoto = document.getElementById('capturedPhoto');
    const captureBtn = document.getElementById('captureBtn');
    const retakeBtn = document.getElementById('retakeBtn');
    const timeInBtn = document.getElementById('timeInBtn');

    attendanceModal?.addEventListener('shown.bs.modal', async function () {
        try {
            videoStream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'user', width: 640, height: 480 } 
            });
            video.srcObject = videoStream;
        } catch (error) {
            console.error('Camera error:', error);
        }
    });

    attendanceModal?.addEventListener('hidden.bs.modal', function () {
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }
        video.style.display = 'block';
        capturedPhoto.style.display = 'none';
        captureBtn.style.display = 'block';
        retakeBtn.style.display = 'none';
    });

    captureBtn?.addEventListener('click', function () {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        
        capturedPhoto.src = canvas.toDataURL('image/jpeg', 0.8);
        video.style.display = 'none';
        capturedPhoto.style.display = 'block';
        captureBtn.style.display = 'none';
        retakeBtn.style.display = 'block';
    });

    retakeBtn?.addEventListener('click', function () {
        video.style.display = 'block';
        capturedPhoto.style.display = 'none';
        captureBtn.style.display = 'block';
        retakeBtn.style.display = 'none';
    });

    // Quick Search
    document.getElementById('quickSearch')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value;
            if (query) {
                window.location.href = `/branch/laundries?search=${encodeURIComponent(query)}`;
            }
        }
    });
</script>
@endpush
