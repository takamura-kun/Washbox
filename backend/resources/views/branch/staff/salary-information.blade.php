@extends('branch.layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Staff Salary Information</h1>
            <p class="text-muted mb-0">View salary details for all staff in your branch</p>
        </div>
        <div>
            <a href="{{ route('branch.staff.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Staff List
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Staff</p>
                            <h3 class="mb-0">{{ $staffWithSalary->count() }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-users text-primary fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">With Salary Info</p>
                            <h3 class="mb-0">{{ $staffWithSalary->count() }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Est. Monthly Payroll</p>
                            <h3 class="mb-0">₱{{ number_format($estimatedMonthlyPayroll, 2) }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-money-bill-wave text-info fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff with Salary Information -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Staff Salary Information</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Staff</th>
                            <th>Branch</th>
                            <th>Salary Type</th>
                            <th>Base Rate</th>
                            <th>Pay Period</th>
                            <th>Effectivity Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffWithSalary as $staff)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                        <span class="text-primary fw-bold">{{ strtoupper(substr($staff->name, 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $staff->name }}</div>
                                        <small class="text-muted">{{ $staff->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $staff->branch->name }}</span>
                            </td>
                            <td>
                                @if($staff->salaryInfo->salary_type === 'monthly')
                                    <span class="badge bg-primary">Monthly</span>
                                @elseif($staff->salaryInfo->salary_type === 'daily')
                                    <span class="badge bg-info">Daily</span>
                                @else
                                    <span class="badge bg-warning">Hourly</span>
                                @endif
                            </td>
                            <td class="fw-semibold">₱{{ number_format($staff->salaryInfo->base_rate, 2) }}</td>
                            <td>{{ ucfirst($staff->salaryInfo->pay_period) }}</td>
                            <td>{{ \Carbon\Carbon::parse($staff->salaryInfo->effectivity_date)->format('M d, Y') }}</td>
                            <td>
                                @if($staff->salaryInfo->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('branch.staff.show', $staff->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No staff with salary information</h5>
                                <p class="text-muted mb-0">There are no staff members with salary information set.</p>
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
