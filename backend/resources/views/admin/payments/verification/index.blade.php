@extends('admin.layouts.app')

@section('title', 'Payment Verification')
@section('page-title', 'Payment Verification')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/payment-verification.css') }}">
    <style>
        .payment-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid var(--border-color) !important;
            cursor: default !important;
        }
        .payment-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15) !important;
        }
        /* Light mode */
        [data-theme="light"] .payment-card {
            background-color: #ffffff !important;
            color: #111827 !important;
            border-color: #e5e7eb !important;
        }
        /* Dark mode */
        [data-theme="dark"] .payment-card {
            background-color: #1F2937 !important;
            color: #F9FAFB !important;
            border-color: #374151 !important;
        }
        .payment-tracking {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .payment-service {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }
        .payment-customer-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        .payment-amount-main {
            font-size: 1.3rem;
            font-weight: 700;
            color: #10b981;
        }
        .payment-amount-sub {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }
        .btn {
            cursor: pointer !important;
        }
        .form-check-input {
            cursor: pointer !important;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <p class="page-subtitle">Review and verify GCash payment proofs</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-card-modern">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="status-filter" class="filter-label">Status</label>
                <select id="status-filter" name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="branch-filter" class="filter-label">Branch</label>
                <select id="branch-filter" name="branch_id" class="filter-select">
                    <option value="">All Branches</option>
                    @foreach(\App\Models\Branch::all() as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="filter-btn filter-btn-primary me-2">Filter</button>
                <a href="{{ route('admin.payments.verification.index') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>

    {{-- Payment Proofs Grid --}}
    @if($paymentProofs->count() > 0)
        <div class="row g-4" style="background-color: transparent !important;">
            @foreach($paymentProofs as $proof)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 payment-card" style="cursor: default;">
                    <div class="card-body p-4" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                @if($proof->status === 'pending')
                                    <label for="proof-{{ $proof->id }}" class="d-flex align-items-center">
                                        <input type="checkbox" id="proof-{{ $proof->id }}" name="payment_proof_ids[]" value="{{ $proof->id }}" class="form-check-input proof-checkbox me-2" style="width: 18px; height: 18px; cursor: pointer;">
                                        <span class="sr-only">Select payment proof {{ $proof->id }}</span>
                                    </label>
                                @endif
                                <div class="payment-tracking">{{ $proof->laundry->tracking_number }}</div>
                                <div class="payment-service">{{ $proof->laundry->service->name ?? 'N/A' }}</div>
                            </div>
                            @if($proof->status === 'pending')
                                <span class="badge bg-warning" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">Pending</span>
                            @elseif($proof->status === 'approved')
                                <span class="badge bg-success" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">Approved</span>
                            @else
                                <span class="badge bg-danger" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">Rejected</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-person-circle me-2 text-muted" style="font-size: 1.2rem;"></i>
                                <div>
                                    <div class="payment-customer-name">{{ $proof->laundry->customer->name }}</div>
                                    <small class="text-muted">{{ $proof->laundry->customer->phone }}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-shop me-2 text-muted" style="font-size: 1rem;"></i>
                                <span class="badge bg-secondary" style="font-size: 0.8rem;">{{ $proof->laundry->branch->name }}</span>
                            </div>
                            @if($proof->reference_number)
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-hash me-2 text-muted" style="font-size: 1rem;"></i>
                                    <small class="font-monospace" style="font-size: 0.85rem;">{{ $proof->reference_number }}</small>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-start pt-3 border-top mb-3">
                            <div>
                                <small class="text-muted d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Amount Paid</small>
                                <div class="payment-amount-main">₱{{ number_format($proof->amount, 2) }}</div>
                                <div class="payment-amount-sub">Expected: ₱{{ number_format($proof->laundry->total_amount, 2) }}</div>
                                @if($proof->amount != $proof->laundry->total_amount)
                                    <span class="badge bg-danger mt-1" style="font-size: 0.7rem;">Amount Mismatch</span>
                                @endif
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Submitted</small>
                                <div style="font-size: 0.85rem; font-weight: 600;">{{ $proof->created_at->format('M d, Y') }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $proof->created_at->format('h:i A') }}</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.payments.verification.show', $proof) }}" class="btn btn-primary flex-fill" style="font-size: 0.85rem; padding: 0.6rem; cursor: pointer;">
                                <i class="bi bi-receipt"></i> View Details
                            </a>
                            <a href="{{ route('admin.laundries.show', $proof->laundry) }}" class="btn btn-outline-secondary" style="font-size: 0.85rem; padding: 0.6rem; cursor: pointer;">
                                <i class="bi bi-box-seam"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($paymentProofs->hasPages())
            <div class="card border-0 shadow-sm rounded-4 mt-4" style="background-color: var(--card-bg) !important;">
                <div class="card-body p-3" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
                    {{ $paymentProofs->links() }}
                </div>
            </div>
        @endif
    @else
        <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg) !important;">
            <div class="card-body p-5" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h5>No payment proofs found</h5>
                    <p>No payment proofs match your current filters</p>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Bulk Approve Modal --}}
<div class="modal fade" id="bulkApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.payments.verification.bulk-approve') }}" id="bulkApproveForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Approve Payments</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve the selected payment proofs?</p>
                    <div id="selectedCount" class="text-muted"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get elements with null checks
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.proof-checkbox');
    const bulkApproveForm = document.getElementById('bulkApproveForm');
    const selectedCount = document.getElementById('selectedCount');

    // Only proceed if we have checkboxes
    if (checkboxes.length === 0) {
        console.log('No payment proof checkboxes found');
        return;
    }

    // Select all functionality - only if selectAll element exists
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }

    // Individual checkbox change
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    function updateSelectedCount() {
        const selected = document.querySelectorAll('.proof-checkbox:checked');
        
        if (selectedCount) {
            selectedCount.textContent = `${selected.length} payment(s) selected`;
        }

        // Update select all checkbox if it exists
        if (selectAll) {
            selectAll.indeterminate = selected.length > 0 && selected.length < checkboxes.length;
            selectAll.checked = selected.length === checkboxes.length;
        }
    }

    // Bulk approve form submission - only if form exists
    if (bulkApproveForm) {
        bulkApproveForm.addEventListener('submit', function(e) {
            const selected = document.querySelectorAll('.proof-checkbox:checked');

            // Remove existing hidden inputs
            bulkApproveForm.querySelectorAll('input[name="payment_proof_ids[]"]').forEach(input => {
                input.remove();
            });

            // Add selected IDs as hidden inputs
            selected.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'payment_proof_ids[]';
                input.value = checkbox.value;
                bulkApproveForm.appendChild(input);
            });

            if (selected.length === 0) {
                e.preventDefault();
                alert('Please select at least one payment proof to approve.');
            }
        });
    }
});
</script>
@endpush

@endsection
