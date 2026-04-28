@extends('admin.layouts.app')

@section('title', 'Payroll Details')

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
<div class="container-xl px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Payroll Details</h1>
        <div>
            @if($period->status == 'draft')
                <form action="{{ route('admin.finance.payroll.approve', $period) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Approve this payroll? An expense will be created.')">
                        <i class="bi bi-check-circle me-2"></i>Approve
                    </button>
                </form>
            @elseif($period->status == 'approved')
                <form action="{{ route('admin.finance.payroll.mark-paid', $period) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cash me-2"></i>Mark as Paid
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.finance.payroll.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <div class="card shadow-sm mb-4" style="background: var(--card-bg); border-color: var(--border-color);">
        <div class="card-header" style="background: var(--primary-color); color: white;">
            <h5 class="mb-0">Period Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <strong style="color: var(--text-primary);">Period:</strong>
                    <p class="mb-0" style="color: var(--text-primary);">{{ $period->period_label }}</p>
                </div>
                <div class="col-md-2">
                    <strong style="color: var(--text-primary);">Date Range:</strong>
                    <p class="mb-0" style="color: var(--text-primary);">{{ $period->date_from->format('M d') }} - {{ $period->date_to->format('M d, Y') }}</p>
                    <small style="color: var(--text-secondary);">{{ $period->date_from->diffInDays($period->date_to) + 1 }} days</small>
                </div>
                <div class="col-md-2">
                    <strong style="color: var(--text-primary);">Branch:</strong>
                    <p class="mb-0" style="color: var(--text-primary);">{{ $period->branch->name ?? 'All Branches' }}</p>
                </div>
                <div class="col-md-2">
                    <strong style="color: var(--text-primary);">Pay Date:</strong>
                    <p class="mb-0" style="color: var(--text-primary);">{{ $period->pay_date->format('F d, Y') }}</p>
                </div>
                <div class="col-md-2">
                    <strong style="color: var(--text-primary);">Total Staff:</strong>
                    <p class="mb-0" style="color: var(--text-primary);">{{ $period->items->count() }} staff</p>
                </div>
                <div class="col-md-2">
                    <strong style="color: var(--text-primary);">Status:</strong>
                    <p class="mb-0">
                        @if($period->status == 'draft')
                            <span class="badge bg-secondary">Draft</span>
                        @elseif($period->status == 'approved')
                            <span class="badge bg-warning">Approved</span>
                        @else
                            <span class="badge bg-success">Paid</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Statistics --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: var(--card-bg); border-color: var(--border-color);">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="bi bi-calendar-check fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1" style="color: var(--text-secondary);">Total Days Worked</h6>
                            <h3 class="mb-0" style="color: var(--text-primary);">{{ number_format($period->items->sum('days_worked'), 1) }}</h3>
                            <small style="color: var(--text-secondary);">across all staff</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: var(--card-bg); border-color: var(--border-color);">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1" style="color: var(--text-secondary);">Total Hours Worked</h6>
                            <h3 class="mb-0" style="color: var(--text-primary);">{{ number_format($period->items->sum('hours_worked'), 1) }}h</h3>
                            <small style="color: var(--text-secondary);">regular hours</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: var(--card-bg); border-color: var(--border-color);">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="bi bi-clock fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1" style="color: var(--text-secondary);">Total Overtime</h6>
                            <h3 class="mb-0" style="color: var(--text-primary);">{{ number_format($period->items->sum('overtime_hours'), 1) }}h</h3>
                            <small style="color: var(--text-secondary);">₱{{ number_format($period->items->sum('overtime_pay'), 2) }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: var(--card-bg); border-color: var(--border-color);">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="bi bi-cash-stack fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1" style="color: var(--text-secondary);">Total Payroll</h6>
                            <h3 class="mb-0" style="color: var(--text-primary);">₱{{ number_format($period->total_amount, 2) }}</h3>
                            <small style="color: var(--text-secondary);">net pay</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm" style="background: var(--card-bg); border-color: var(--border-color);">
        <div class="card-header" style="background: var(--border-color); color: var(--text-primary);">
            <h5 class="mb-0">Payroll Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" style="color: var(--text-primary);">
                    <thead style="background: var(--border-color);">
                        <tr>
                            <th style="color: var(--text-primary);">Staff</th>
                            <th style="color: var(--text-primary);">Branch</th>
                            <th style="color: var(--text-primary);">Days</th>
                            <th style="color: var(--text-primary);">Hours</th>
                            <th style="color: var(--text-primary);">OT Hours</th>
                            <th style="color: var(--text-primary);">Base Rate</th>
                            <th style="color: var(--text-primary);">Gross Pay</th>
                            <th style="color: var(--text-primary);">OT Pay</th>
                            <th style="color: var(--text-primary);">Deductions</th>
                            <th style="color: var(--text-primary);">Bonuses</th>
                            <th style="color: var(--text-primary);">Net Pay</th>
                            <th style="color: var(--text-primary);">Status</th>
                            @if($period->status == 'draft')
                                <th style="color: var(--text-primary);">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($period->items as $item)
                            <tr style="color: var(--text-primary);">
                                <td>{{ $item->user->name }}</td>
                                <td>{{ $item->branch->name }}</td>
                                <td>
                                    @if($period->status == 'draft')
                                        <input type="number" class="form-control form-control-sm" value="{{ $item->days_worked }}" 
                                               data-item-id="{{ $item->id }}" data-field="days_worked" min="0">
                                    @else
                                        {{ $item->days_worked }}
                                    @endif
                                </td>
                                <td>
                                    @if($period->status == 'draft')
                                        <input type="number" class="form-control form-control-sm" value="{{ $item->hours_worked }}" 
                                               data-item-id="{{ $item->id }}" data-field="hours_worked" min="0" step="0.5">
                                    @else
                                        {{ $item->hours_worked }}
                                    @endif
                                </td>
                                <td>
                                    @if($period->status == 'draft')
                                        <input type="number" class="form-control form-control-sm" value="{{ $item->overtime_hours }}" 
                                               data-item-id="{{ $item->id }}" data-field="overtime_hours" min="0" step="0.5"
                                               placeholder="0">
                                    @else
                                        {{ $item->overtime_hours }}
                                    @endif
                                </td>
                                <td>₱{{ number_format($item->base_rate, 2) }}</td>
                                <td>₱{{ number_format($item->gross_pay, 2) }}</td>
                                <td>₱{{ number_format($item->overtime_pay, 2) }}</td>
                                <td>
                                    @if($period->status == 'draft')
                                        <input type="number" class="form-control form-control-sm" value="{{ $item->deductions }}" 
                                               data-item-id="{{ $item->id }}" data-field="deductions" min="0" step="0.01">
                                    @else
                                        ₱{{ number_format($item->deductions, 2) }}
                                    @endif
                                </td>
                                <td>
                                    @if($period->status == 'draft')
                                        <input type="number" class="form-control form-control-sm" value="{{ $item->bonuses }}" 
                                               data-item-id="{{ $item->id }}" data-field="bonuses" min="0" step="0.01">
                                    @else
                                        ₱{{ number_format($item->bonuses, 2) }}
                                    @endif
                                </td>
                                <td><strong class="net-pay-display">₱{{ number_format($item->net_pay, 2) }}</strong></td>
                                <td>
                                    @if($item->status == 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @else
                                        <span class="badge bg-success">Paid</span>
                                    @endif
                                </td>
                                @if($period->status == 'draft')
                                    <td>
                                        <form action="{{ route('admin.finance.payroll.update-item', $item) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="days_worked" class="days-worked-{{ $item->id }}">
                                            <input type="hidden" name="hours_worked" class="hours-worked-{{ $item->id }}">
                                            <input type="hidden" name="overtime_hours" class="overtime-hours-{{ $item->id }}">
                                            <input type="hidden" name="deductions" class="deductions-{{ $item->id }}">
                                            <input type="hidden" name="bonuses" class="bonuses-{{ $item->id }}">
                                            <button type="submit" class="btn btn-sm btn-primary" onclick="return updateHiddenFields({{ $item->id }})">
                                                <i class="bi bi-save"></i>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: var(--border-color);">
                            <th colspan="10" class="text-end" style="color: var(--text-primary);">Total:</th>
                            <th colspan="{{ $period->status == 'draft' ? '3' : '2' }}" style="color: var(--text-primary);">₱{{ number_format($period->total_amount, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@if($period->status == 'draft')
@push('scripts')
<script>
const OT_RATE = 40; // ₱40 per hour
const HOURS_PER_DAY = 8; // Standard hours per day

// Function to update hidden fields before form submission
function updateHiddenFields(itemId) {
    const row = document.querySelector(`[data-item-id="${itemId}"]`).closest('tr');
    
    document.querySelector(`.days-worked-${itemId}`).value = row.querySelector('[data-field="days_worked"]').value;
    document.querySelector(`.hours-worked-${itemId}`).value = row.querySelector('[data-field="hours_worked"]').value;
    document.querySelector(`.overtime-hours-${itemId}`).value = row.querySelector('[data-field="overtime_hours"]').value;
    document.querySelector(`.deductions-${itemId}`).value = row.querySelector('[data-field="deductions"]').value;
    document.querySelector(`.bonuses-${itemId}`).value = row.querySelector('[data-field="bonuses"]').value;
    
    return true;
}

// Real-time cascading calculation
document.querySelectorAll('input[data-field]').forEach(input => {
    input.addEventListener('input', function() {
        const row = this.closest('tr');
        const field = this.dataset.field;
        
        // Get base rate from the table
        const baseRateText = row.querySelector('td:nth-child(6)').textContent;
        const baseRate = parseFloat(baseRateText.replace('₱', '').replace(/,/g, '')) || 0;
        
        // Get current values
        let daysWorked = parseFloat(row.querySelector('[data-field="days_worked"]').value) || 0;
        let hoursWorked = parseFloat(row.querySelector('[data-field="hours_worked"]').value) || 0;
        let overtimeHours = parseFloat(row.querySelector('[data-field="overtime_hours"]').value) || 0;
        const deductions = parseFloat(row.querySelector('[data-field="deductions"]').value) || 0;
        const bonuses = parseFloat(row.querySelector('[data-field="bonuses"]').value) || 0;
        
        // CASCADING LOGIC: If days changed, auto-adjust hours
        if (field === 'days_worked') {
            // Auto-calculate hours based on days (8 hours per day)
            hoursWorked = daysWorked * HOURS_PER_DAY;
            row.querySelector('[data-field="hours_worked"]').value = hoursWorked.toFixed(1);
            
            // Auto-calculate overtime (hours beyond standard)
            const standardHours = daysWorked * HOURS_PER_DAY;
            overtimeHours = Math.max(0, hoursWorked - standardHours);
            row.querySelector('[data-field="overtime_hours"]').value = overtimeHours.toFixed(1);
        }
        
        // CASCADING LOGIC: If hours changed, auto-adjust overtime
        if (field === 'hours_worked') {
            // Auto-calculate overtime based on hours
            const standardHours = daysWorked * HOURS_PER_DAY;
            overtimeHours = Math.max(0, hoursWorked - standardHours);
            row.querySelector('[data-field="overtime_hours"]').value = overtimeHours.toFixed(1);
        }
        
        // Calculate pay components
        const regularPay = baseRate * daysWorked; // Base rate × days
        const overtimePay = overtimeHours * OT_RATE; // OT hours × ₱40
        const grossPay = regularPay + overtimePay; // Regular + OT
        const netPay = grossPay - deductions + bonuses; // Gross - deductions + bonuses
        
        // Update display with formatted values
        row.querySelector('td:nth-child(7)').textContent = '₱' + grossPay.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        row.querySelector('td:nth-child(8)').textContent = '₱' + overtimePay.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        row.querySelector('.net-pay-display').textContent = '₱' + netPay.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        
        // Visual feedback - highlight changed row
        row.style.backgroundColor = '#fff3cd';
        setTimeout(() => {
            row.style.backgroundColor = '';
        }, 500);
    });
});
</script>
@endpush
@endif
@endsection
