@extends('branch.layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Payslip Details</h1>
            <p class="text-muted mb-0">{{ $payrollItem->payrollPeriod->period_name }}</p>
        </div>
        <div>
            <a href="{{ route('branch.payroll.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Payroll
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-1"></i>Print Payslip
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Payslip Card -->
            <div class="card border-0 shadow-sm" id="payslip">
                <!-- Header -->
                <div class="card-header bg-primary text-white py-4">
                    <div class="text-center">
                        <h3 class="mb-1">PAYSLIP</h3>
                        <p class="mb-0">{{ $payrollItem->payrollPeriod->period_name }}</p>
                        <small>
                            {{ \Carbon\Carbon::parse($payrollItem->payrollPeriod->period_start)->format('F d, Y') }} - 
                            {{ \Carbon\Carbon::parse($payrollItem->payrollPeriod->period_end)->format('F d, Y') }}
                        </small>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Employee Information -->
                    <div class="row mb-4 pb-4 border-bottom">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">EMPLOYEE INFORMATION</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" width="40%">Name:</td>
                                    <td class="fw-semibold">{{ $payrollItem->user->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email:</td>
                                    <td>{{ $payrollItem->user->email }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Branch:</td>
                                    <td>{{ $payrollItem->branch->name }}</td>
                                </tr>
                                @if($payrollItem->user->salaryInfo)
                                <tr>
                                    <td class="text-muted">Salary Type:</td>
                                    <td>
                                        <span class="badge bg-primary">{{ ucfirst($payrollItem->user->salaryInfo->salary_type) }}</span>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">PAYMENT INFORMATION</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" width="40%">Payslip ID:</td>
                                    <td class="fw-semibold">#{{ str_pad($payrollItem->id, 6, '0', STR_PAD_LEFT) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status:</td>
                                    <td>
                                        @if($payrollItem->status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($payrollItem->status === 'approved')
                                            <span class="badge bg-warning">Approved</span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Generated:</td>
                                    <td>{{ $payrollItem->created_at->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Work Summary -->
                    <div class="mb-4 pb-4 border-bottom">
                        <h6 class="text-muted mb-3">WORK SUMMARY</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-light border-0">
                                    <div class="card-body text-center">
                                        <i class="fas fa-calendar-day text-primary fa-2x mb-2"></i>
                                        <h4 class="mb-0">{{ $payrollItem->days_worked ?? 0 }}</h4>
                                        <small class="text-muted">Days Worked</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light border-0">
                                    <div class="card-body text-center">
                                        <i class="fas fa-clock text-info fa-2x mb-2"></i>
                                        <h4 class="mb-0">{{ $payrollItem->hours_worked ?? 0 }}</h4>
                                        <small class="text-muted">Hours Worked</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light border-0">
                                    <div class="card-body text-center">
                                        <i class="fas fa-money-bill-wave text-success fa-2x mb-2"></i>
                                        <h4 class="mb-0">₱{{ number_format($payrollItem->base_rate, 2) }}</h4>
                                        <small class="text-muted">Base Rate</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Earnings & Deductions -->
                    <div class="mb-4">
                        <div class="row">
                            <!-- Earnings -->
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">EARNINGS</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Gross Pay</td>
                                        <td class="text-end fw-semibold">₱{{ number_format($payrollItem->gross_pay, 2) }}</td>
                                    </tr>
                                    @if($payrollItem->bonuses > 0)
                                    <tr class="text-success">
                                        <td>Bonuses</td>
                                        <td class="text-end fw-semibold">+₱{{ number_format($payrollItem->bonuses, 2) }}</td>
                                    </tr>
                                    @endif
                                    <tr class="border-top">
                                        <td class="fw-bold">Total Earnings</td>
                                        <td class="text-end fw-bold text-success">₱{{ number_format($payrollItem->gross_pay + $payrollItem->bonuses, 2) }}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Deductions -->
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">DEDUCTIONS</h6>
                                <table class="table table-sm">
                                    @if($payrollItem->deductions > 0)
                                    <tr class="text-danger">
                                        <td>Total Deductions</td>
                                        <td class="text-end fw-semibold">-₱{{ number_format($payrollItem->deductions, 2) }}</td>
                                    </tr>
                                    @else
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No deductions</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Net Pay -->
                    <div class="bg-primary bg-opacity-10 rounded p-4 text-center">
                        <h6 class="text-muted mb-2">NET PAY</h6>
                        <h2 class="text-primary mb-0">₱{{ number_format($payrollItem->net_pay, 2) }}</h2>
                        <small class="text-muted">Amount to be received</small>
                    </div>

                    <!-- Notes -->
                    @if($payrollItem->notes)
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="text-muted mb-2">NOTES</h6>
                        <p class="mb-0">{{ $payrollItem->notes }}</p>
                    </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="card-footer bg-light text-center text-muted">
                    <small>This is a computer-generated payslip. No signature required.</small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .btn, .navbar, .sidebar, nav, footer {
            display: none !important;
        }
        
        #payslip {
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
        }
        
        body {
            background: white !important;
        }
    }
</style>
@endpush
@endsection
