@extends('admin.layouts.app')

@section('page-title', 'Staff Salary Management')

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
    .table thead tr th {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    
    .table thead {
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Staff Salary Management</h2>
            <p class="text-muted small mb-0">Manage staff salaries and compensation</p>
        </div>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkSalaryModal">
            <i class="bi bi-cash-stack me-2"></i>Bulk Update
        </button>
    </div>

    {{-- Quick Navigation --}}
    <div class="btn-group mb-4 w-100" role="group" style="box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-info">
            <i class="bi bi-people me-1"></i>Staff List
        </a>
        <a href="{{ route('admin.finance.payroll.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-list-ul me-1"></i>Payroll Periods
        </a>
        <a href="{{ route('admin.staff.salary-management') }}" class="btn btn-success">
            <i class="bi bi-cash-stack me-1"></i>Salary Management
        </a>
        <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-calendar-check me-1"></i>Attendance
        </a>
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
                            <h6 class="text-muted mb-1">Total Staff</h6>
                            <h3 class="mb-0">{{ $summary['total_staff'] }}</h3>
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
                            <h6 class="text-muted mb-1">With Salary Info</h6>
                            <h3 class="mb-0">{{ $summary['with_salary'] }}</h3>
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
                                <i class="bi bi-exclamation-triangle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Without Salary</h6>
                            <h3 class="mb-0">{{ $summary['without_salary'] }}</h3>
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
                                <i class="bi bi-cash-stack fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Monthly</h6>
                            <h3 class="mb-0">₱{{ number_format($summary['total_monthly'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Default Rate Info --}}
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle fs-4 me-3"></i>
            <div>
                <strong>Default Attendance-Based Rate:</strong> ₱480 per day (Full Day), ₱240 per day (Half Day)
                <br>
                <small>Staff without custom salary info will use this default rate for payroll calculation.</small>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Branch</label>
                    <select name="branch_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Salary Type</label>
                    <select name="salary_type" class="form-select" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="monthly" {{ request('salary_type') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="daily" {{ request('salary_type') === 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="hourly" {{ request('salary_type') === 'hourly' ? 'selected' : '' }}>Hourly</option>
                        <option value="none" {{ request('salary_type') === 'none' ? 'selected' : '' }}>No Salary Info</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.staff.salary-management') }}" class="btn btn-light border w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i>Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Staff Salary Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Staff Salary Information</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0" style="color: var(--text-primary); background: var(--card-bg); border-color: var(--border-color);">
                    <thead style="background: var(--border-color);">
                        <tr>
                            <th style="color: var(--text-primary); border-color: var(--border-color); padding: 12px;">Staff</th>
                            <th style="color: var(--text-primary); border-color: var(--border-color); padding: 12px;">Branch</th>
                            <th style="color: var(--text-primary); border-color: var(--border-color); padding: 12px;">Salary Type</th>
                            <th style="color: var(--text-primary); border-color: var(--border-color); padding: 12px;">Base Rate</th>
                            <th style="color: var(--text-primary); border-color: var(--border-color); padding: 12px;">Pay Period</th>
                            <th style="color: var(--text-primary); border-color: var(--border-color); padding: 12px;">Effectivity Date</th>
                            <th style="color: var(--text-primary); border-color: var(--border-color); padding: 12px;">Status</th>
                            <th style="color: var(--text-primary); border-color: var(--border-color); padding: 12px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $member)
                        <tr style="color: var(--text-primary); background: var(--card-bg); border-color: var(--border-color);">
                            <td style="border-color: var(--border-color); padding: 12px;">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                        {{ substr($member->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="color: var(--text-primary);">{{ $member->name }}</div>
                                        <small style="color: var(--text-secondary);">{{ $member->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td style="border-color: var(--border-color); padding: 12px;">
                                @if($member->branch)
                                    <span class="badge bg-light text-dark border">
                                        {{ $member->branch->name }}
                                    </span>
                                @else
                                    <span style="color: var(--text-secondary);">No Branch</span>
                                @endif
                            </td>
                            <td style="border-color: var(--border-color); padding: 12px;">
                                @if($member->salaryInfo)
                                    <span class="badge bg-primary">
                                        {{ ucfirst($member->salaryInfo->salary_type) }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        Default (Daily)
                                    </span>
                                @endif
                            </td>
                            <td style="border-color: var(--border-color); padding: 12px;">
                                @if($member->salaryInfo)
                                    <strong style="color: var(--text-primary);">₱{{ number_format($member->salaryInfo->base_rate, 2) }}</strong>
                                    <br>
                                    <small style="color: var(--text-secondary);">
                                        per {{ $member->salaryInfo->salary_type === 'monthly' ? 'month' : ($member->salaryInfo->salary_type === 'daily' ? 'day' : 'hour') }}
                                    </small>
                                @else
                                    <strong style="color: var(--text-primary);">₱480.00</strong>
                                    <br>
                                    <small style="color: var(--text-secondary);">per day (default)</small>
                                @endif
                            </td>
                            <td style="border-color: var(--border-color); padding: 12px;">
                                @if($member->salaryInfo)
                                    <span style="color: var(--text-primary);">{{ ucfirst($member->salaryInfo->pay_period) }}</span>
                                @else
                                    <span style="color: var(--text-secondary);">Weekly (default)</span>
                                @endif
                            </td>
                            <td style="border-color: var(--border-color); padding: 12px;">
                                @if($member->salaryInfo)
                                    <span style="color: var(--text-primary);">{{ $member->salaryInfo->effectivity_date->format('M d, Y') }}</span>
                                @else
                                    <span style="color: var(--text-secondary);">-</span>
                                @endif
                            </td>
                            <td style="border-color: var(--border-color); padding: 12px;">
                                @if($member->salaryInfo)
                                    <span class="badge bg-{{ $member->salaryInfo->is_active ? 'success' : 'secondary' }}">
                                        {{ $member->salaryInfo->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                @else
                                    <span class="badge bg-warning">Not Set</span>
                                @endif
                            </td>
                            <td style="border-color: var(--border-color); padding: 12px;">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editSalary({{ $member->id }}, '{{ $member->name }}', {{ $member->salaryInfo ? json_encode($member->salaryInfo) : 'null' }})" title="Edit Salary">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="{{ route('admin.staff.show', $member) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($member->salaryInfo)
                                        <form action="{{ route('admin.staff.delete-salary', $member) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove salary information for {{ $member->name }}? They will use default rate.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Salary">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr style="background: var(--card-bg);">
                            <td colspan="8" class="text-center py-5" style="color: var(--text-secondary); border-color: var(--border-color); padding: 12px;">
                                <i class="bi bi-inbox fs-1" style="color: var(--text-secondary);"></i>
                                <p style="color: var(--text-secondary);" class="mt-2">No staff members found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($staff->hasPages())
            <div class="card-footer bg-white">
                {{ $staff->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Edit Salary Modal --}}
<div class="modal fade" id="editSalaryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editSalaryForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Update Salary Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Staff:</strong> <span id="staffName"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Salary Type <span class="text-danger">*</span></label>
                        <select name="salary_type" id="salaryType" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="monthly">Monthly</option>
                            <option value="daily">Daily</option>
                            <option value="hourly">Hourly</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Base Rate (₱) <span class="text-danger">*</span></label>
                        <input type="number" name="base_rate" id="baseRate" class="form-control" step="0.01" min="0" required>
                        <small class="text-muted">Enter the base salary amount</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pay Period <span class="text-danger">*</span></label>
                        <select name="pay_period" id="payPeriod" class="form-select" required>
                            <option value="custom">Custom Period</option>
                        </select>
                        <small class="text-muted">Payroll will be calculated based on attendance records</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Effectivity Date <span class="text-danger">*</span></label>
                        <input type="date" name="effectivity_date" id="effectivityDate" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Bulk Salary Update Modal --}}
<div class="modal fade" id="bulkSalaryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.staff.bulk-salary-update') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Salary Update</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This will update salary information for all staff in the selected branch.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Branch <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-select" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Salary Type <span class="text-danger">*</span></label>
                        <select name="salary_type" class="form-select" required>
                            <option value="monthly">Monthly</option>
                            <option value="daily">Daily</option>
                            <option value="hourly">Hourly</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Base Rate (₱) <span class="text-danger">*</span></label>
                        <input type="number" name="base_rate" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pay Period <span class="text-danger">*</span></label>
                        <select name="pay_period" class="form-select" required>
                            <option value="custom">Custom Period</option>
                        </select>
                        <small class="text-muted">Payroll will be calculated based on attendance records</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Effectivity Date <span class="text-danger">*</span></label>
                        <input type="date" name="effectivity_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Update All</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editSalary(userId, staffName, salaryInfo) {
    document.getElementById('staffName').textContent = staffName;
    document.getElementById('editSalaryForm').action = `/admin/staff/${userId}/salary`;

    if (salaryInfo) {
        document.getElementById('salaryType').value = salaryInfo.salary_type;
        document.getElementById('baseRate').value = salaryInfo.base_rate;
        document.getElementById('payPeriod').value = salaryInfo.pay_period;
        document.getElementById('effectivityDate').value = salaryInfo.effectivity_date;
    } else {
        document.getElementById('salaryType').value = 'daily';
        document.getElementById('baseRate').value = '480';
        document.getElementById('payPeriod').value = 'weekly';
        document.getElementById('effectivityDate').value = new Date().toISOString().split('T')[0];
    }

    const modal = new bootstrap.Modal(document.getElementById('editSalaryModal'));
    modal.show();
}
</script>
@endpush
@endsection
