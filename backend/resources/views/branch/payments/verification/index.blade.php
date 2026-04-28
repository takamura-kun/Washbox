@extends('branch.layouts.app')

@section('page-title', 'Payment Verification')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
/* ============================================================
   PAYMENT VERIFICATION — Refined Financial Dashboard Aesthetic
   Clean precision meets understated luxury.
   ============================================================ */

:root {
    --pv-bg:          #f0f2f7;
    --pv-surface:     #ffffff;
    --pv-surface-2:   #f8f9fc;
    --pv-border:      #e4e8f0;
    --pv-border-2:    #d0d7e8;
    --pv-text-1:      #0d1117;
    --pv-text-2:      #4a5568;
    --pv-text-3:      #8896ae;
    --pv-accent:      #1a56db;
    --pv-accent-soft: rgba(26, 86, 219, 0.08);
    --pv-success:     #0d9373;
    --pv-success-soft:rgba(13, 147, 115, 0.1);
    --pv-warning:     #c27803;
    --pv-warning-soft:rgba(194, 120, 3, 0.1);
    --pv-danger:      #c81e1e;
    --pv-danger-soft: rgba(200, 30, 30, 0.1);
    --pv-shadow-sm:   0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    --pv-shadow-md:   0 4px 16px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
    --pv-shadow-lg:   0 12px 40px rgba(0,0,0,0.12), 0 4px 12px rgba(0,0,0,0.06);
    --pv-radius:      16px;
    --pv-radius-sm:   10px;
    --pv-font:        'Sora', sans-serif;
    --pv-mono:        'JetBrains Mono', monospace;
}

[data-theme="dark"] {
    --pv-bg:          #080c14;
    --pv-surface:     #0f1623;
    --pv-surface-2:   #141d2e;
    --pv-border:      #1e2d45;
    --pv-border-2:    #243450;
    --pv-text-1:      #e8edf5;
    --pv-text-2:      #8896ae;
    --pv-text-3:      #4a5a72;
    --pv-accent:      #4d7cfe;
    --pv-accent-soft: rgba(77, 124, 254, 0.1);
    --pv-success:     #10b981;
    --pv-success-soft:rgba(16, 185, 129, 0.1);
    --pv-warning:     #f59e0b;
    --pv-warning-soft:rgba(245, 158, 11, 0.1);
    --pv-danger:      #f05252;
    --pv-danger-soft: rgba(240, 82, 82, 0.1);
    --pv-shadow-sm:   0 1px 3px rgba(0,0,0,0.3);
    --pv-shadow-md:   0 4px 16px rgba(0,0,0,0.4);
    --pv-shadow-lg:   0 12px 40px rgba(0,0,0,0.6);
}

/* ---- Base ---- */
.pv-page {
    font-family: var(--pv-font);
    background: var(--pv-bg);
    min-height: 100vh;
    padding: 2rem 1.5rem 3rem;
}

/* ---- Page Header ---- */
.pv-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 2rem;
    gap: 1rem;
}

.pv-header-left {}

.pv-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--pv-accent);
    background: var(--pv-accent-soft);
    border: 1px solid rgba(26,86,219,0.15);
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    margin-bottom: 0.75rem;
}

[data-theme="dark"] .pv-eyebrow {
    border-color: rgba(77,124,254,0.2);
}

.pv-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--pv-text-1);
    letter-spacing: -0.03em;
    line-height: 1.2;
    margin: 0 0 0.35rem;
}

.pv-subtitle {
    font-size: 0.875rem;
    color: var(--pv-text-3);
    margin: 0;
    font-weight: 400;
}

/* ---- Stat Pills ---- */
.pv-stats {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: flex-start;
}

.pv-stat-pill {
    background: var(--pv-surface);
    border: 1px solid var(--pv-border);
    border-radius: var(--pv-radius-sm);
    padding: 0.6rem 1rem;
    box-shadow: var(--pv-shadow-sm);
    text-align: center;
    min-width: 80px;
}

.pv-stat-pill .stat-number {
    display: block;
    font-size: 1.3rem;
    font-weight: 700;
    line-height: 1;
    color: var(--pv-text-1);
    font-family: var(--pv-mono);
}

.pv-stat-pill .stat-label {
    display: block;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--pv-text-3);
    margin-top: 0.25rem;
    font-weight: 500;
}

.pv-stat-pill.pill-warning .stat-number { color: var(--pv-warning); }
.pv-stat-pill.pill-success .stat-number { color: var(--pv-success); }
.pv-stat-pill.pill-danger  .stat-number { color: var(--pv-danger);  }

/* ---- Filter Card ---- */
.pv-filter-card {
    background: var(--pv-surface);
    border: 1px solid var(--pv-border);
    border-radius: var(--pv-radius);
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--pv-shadow-sm);
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.pv-filter-label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--pv-text-3);
    white-space: nowrap;
}

.pv-filter-pills {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    flex: 1;
}

.pv-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.45rem 1rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 500;
    font-family: var(--pv-font);
    border: 1.5px solid var(--pv-border);
    background: transparent;
    color: var(--pv-text-2);
    cursor: pointer;
    text-decoration: none;
    transition: all 0.18s ease;
}

.pv-pill-btn:hover {
    border-color: var(--pv-accent);
    color: var(--pv-accent);
    background: var(--pv-accent-soft);
}

.pv-pill-btn.active {
    background: var(--pv-accent);
    border-color: var(--pv-accent);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(26,86,219,0.3);
}

[data-theme="dark"] .pv-pill-btn.active {
    box-shadow: 0 4px 12px rgba(77,124,254,0.35);
}

.pv-pill-btn .dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    display: inline-block;
}
.pv-pill-btn.pill-all .dot   { background: var(--pv-text-3); }
.pv-pill-btn.pill-pending .dot { background: var(--pv-warning); }
.pv-pill-btn.pill-approved .dot { background: var(--pv-success); }
.pv-pill-btn.pill-rejected .dot { background: var(--pv-danger); }
.pv-pill-btn.active .dot { background: rgba(255,255,255,0.8); }

/* ---- Main Table Card ---- */
.pv-table-card {
    background: var(--pv-surface);
    border: 1px solid var(--pv-border);
    border-radius: var(--pv-radius);
    box-shadow: var(--pv-shadow-md);
    overflow: hidden;
}

.pv-table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--pv-border);
    background: var(--pv-surface-2);
}

.pv-table-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--pv-text-1);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pv-table-title-icon {
    width: 28px; height: 28px;
    border-radius: 8px;
    background: var(--pv-accent-soft);
    display: flex; align-items: center; justify-content: center;
    color: var(--pv-accent);
    font-size: 0.8rem;
}

.pv-bulk-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1.1rem;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 600;
    font-family: var(--pv-font);
    background: var(--pv-success);
    border: none;
    color: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(13,147,115,0.3);
    letter-spacing: 0.01em;
}

.pv-bulk-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(13,147,115,0.4);
    background: #0b8067;
}

/* ---- Table ---- */
.pv-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}

.pv-table thead tr {
    background: var(--pv-surface-2);
    border-bottom: 2px solid var(--pv-border);
}

.pv-table thead th {
    padding: 0.85rem 1.1rem;
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--pv-text-3);
    white-space: nowrap;
    border: none;
}

.pv-table tbody tr {
    border-bottom: 1px solid var(--pv-border);
    transition: background 0.15s ease;
}

.pv-table tbody tr:last-child {
    border-bottom: none;
}

.pv-table tbody tr:hover {
    background: var(--pv-accent-soft);
}

.pv-table tbody td {
    padding: 1rem 1.1rem;
    color: var(--pv-text-1);
    vertical-align: middle;
    border: none;
}

/* ---- Custom Checkbox ---- */
.pv-checkbox {
    width: 16px; height: 16px;
    border: 2px solid var(--pv-border-2);
    border-radius: 5px;
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    background: transparent;
    transition: all 0.15s ease;
    position: relative;
    display: block;
}

.pv-checkbox:checked {
    background: var(--pv-accent);
    border-color: var(--pv-accent);
}

.pv-checkbox:checked::after {
    content: '';
    position: absolute;
    top: 1px; left: 4px;
    width: 5px; height: 8px;
    border: 2px solid white;
    border-top: none;
    border-left: none;
    transform: rotate(45deg);
}

.pv-checkbox:indeterminate {
    background: var(--pv-accent);
    border-color: var(--pv-accent);
}

.pv-checkbox:indeterminate::after {
    content: '';
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 8px; height: 2px;
    background: white;
    border-radius: 2px;
}

/* ---- Cell content ---- */
.cell-primary {
    font-weight: 600;
    color: var(--pv-text-1);
    font-size: 0.875rem;
    line-height: 1.3;
}

.cell-secondary {
    font-size: 0.75rem;
    color: var(--pv-text-3);
    margin-top: 0.2rem;
}

.cell-mono {
    font-family: var(--pv-mono);
    font-size: 0.8rem;
    color: var(--pv-text-2);
    background: var(--pv-surface-2);
    border: 1px solid var(--pv-border);
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    display: inline-block;
}

.cell-amount-primary {
    font-family: var(--pv-mono);
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--pv-text-1);
}

.cell-amount-secondary {
    font-family: var(--pv-mono);
    font-size: 0.72rem;
    color: var(--pv-text-3);
    margin-top: 0.15rem;
}

/* ---- Status Badges ---- */
.pv-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.pv-badge::before {
    content: '';
    width: 5px; height: 5px;
    border-radius: 50%;
    display: block;
}

.pv-badge-pending {
    background: var(--pv-warning-soft);
    color: var(--pv-warning);
    border: 1px solid rgba(194,120,3,0.2);
}
.pv-badge-pending::before { background: var(--pv-warning); }

.pv-badge-approved {
    background: var(--pv-success-soft);
    color: var(--pv-success);
    border: 1px solid rgba(13,147,115,0.2);
}
.pv-badge-approved::before { background: var(--pv-success); }

.pv-badge-rejected {
    background: var(--pv-danger-soft);
    color: var(--pv-danger);
    border: 1px solid rgba(200,30,30,0.2);
}
.pv-badge-rejected::before { background: var(--pv-danger); }

/* ---- Action Buttons ---- */
.pv-action-group {
    display: flex;
    gap: 0.4rem;
}

.pv-action-btn {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    border: 1.5px solid var(--pv-border);
    background: transparent;
    color: var(--pv-text-2);
    text-decoration: none;
    transition: all 0.18s ease;
    cursor: pointer;
}

.pv-action-btn:hover {
    transform: translateY(-1px);
    box-shadow: var(--pv-shadow-sm);
}

.pv-action-btn.btn-view:hover {
    background: var(--pv-accent-soft);
    border-color: var(--pv-accent);
    color: var(--pv-accent);
}

.pv-action-btn.btn-laundry:hover {
    background: var(--pv-success-soft);
    border-color: var(--pv-success);
    color: var(--pv-success);
}

/* ---- Empty State ---- */
.pv-empty {
    padding: 4rem 2rem;
    text-align: center;
}

.pv-empty-icon {
    width: 64px; height: 64px;
    border-radius: 20px;
    background: var(--pv-surface-2);
    border: 1px solid var(--pv-border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: var(--pv-text-3);
    margin: 0 auto 1.25rem;
}

.pv-empty-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--pv-text-2);
    margin-bottom: 0.35rem;
}

.pv-empty-sub {
    font-size: 0.825rem;
    color: var(--pv-text-3);
}

/* ---- Pagination ---- */
.pv-pagination {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--pv-border);
    background: var(--pv-surface-2);
}

/* ---- Modal ---- */
.pv-modal .modal-content {
    background: var(--pv-surface);
    border: 1px solid var(--pv-border);
    border-radius: var(--pv-radius);
    box-shadow: var(--pv-shadow-lg);
    overflow: hidden;
}

.pv-modal .modal-header {
    background: var(--pv-surface-2);
    border-bottom: 1px solid var(--pv-border);
    padding: 1.25rem 1.5rem;
}

.pv-modal .modal-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--pv-text-1);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pv-modal .modal-title-icon {
    width: 28px; height: 28px;
    border-radius: 8px;
    background: var(--pv-success-soft);
    display: flex; align-items: center; justify-content: center;
    color: var(--pv-success);
    font-size: 0.85rem;
}

.pv-modal .modal-body {
    padding: 1.5rem;
    color: var(--pv-text-2);
    font-size: 0.875rem;
}

.pv-modal .modal-footer {
    border-top: 1px solid var(--pv-border);
    padding: 1rem 1.5rem;
    background: var(--pv-surface-2);
    gap: 0.5rem;
}

.pv-modal .btn-cancel {
    padding: 0.5rem 1.2rem;
    border-radius: 10px;
    font-size: 0.825rem;
    font-weight: 500;
    font-family: var(--pv-font);
    background: transparent;
    border: 1.5px solid var(--pv-border);
    color: var(--pv-text-2);
    cursor: pointer;
    transition: all 0.15s ease;
}

.pv-modal .btn-cancel:hover {
    background: var(--pv-surface-2);
    border-color: var(--pv-border-2);
    color: var(--pv-text-1);
}

.pv-modal .btn-confirm {
    padding: 0.5rem 1.2rem;
    border-radius: 10px;
    font-size: 0.825rem;
    font-weight: 600;
    font-family: var(--pv-font);
    background: var(--pv-success);
    border: none;
    color: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(13,147,115,0.3);
}

.pv-modal .btn-confirm:hover {
    background: #0b8067;
    box-shadow: 0 4px 12px rgba(13,147,115,0.4);
    transform: translateY(-1px);
}

.pv-selected-info {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    border-radius: 10px;
    background: var(--pv-success-soft);
    border: 1px solid rgba(13,147,115,0.2);
    color: var(--pv-success);
    font-size: 0.825rem;
    font-weight: 500;
    margin-top: 0.75rem;
}

.pv-selected-info .count {
    font-family: var(--pv-mono);
    font-weight: 700;
    font-size: 1rem;
}

/* ---- Animations ---- */
@keyframes pv-fade-up {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

.pv-header        { animation: pv-fade-up 0.4s ease both; }
.pv-filter-card   { animation: pv-fade-up 0.4s ease 0.08s both; }
.pv-table-card    { animation: pv-fade-up 0.4s ease 0.16s both; }

/* ---- Responsive ---- */
@media (max-width: 768px) {
    .pv-header { flex-direction: column; }
    .pv-stats  { flex-direction: row; }
    .pv-title  { font-size: 1.4rem; }
    .pv-table-card { overflow-x: auto; }
}
</style>
@endpush

@section('content')
<div class="pv-page">

    {{-- Page Header --}}
    <div class="pv-header">
        <div class="pv-header-left">
            <div class="pv-eyebrow">
                <i class="bi bi-shield-check"></i>
                Staff Portal
            </div>
            <h1 class="pv-title">Payment Verification</h1>
            <p class="pv-subtitle">Review and verify GCash payment proofs submitted by customers</p>
            @if(!request('status') || request('status') === 'pending')
                <p class="pv-subtitle" style="margin-top: 0.5rem; color: var(--pv-accent); font-size: 0.75rem;">
                    <i class="bi bi-arrow-clockwise"></i> Auto-refreshing every 30 seconds for new payments
                </p>
            @endif
        </div>

        <div class="pv-stats">
            <div class="pv-stat-pill pill-warning">
                <span class="stat-number">{{ $stats['pending'] }}</span>
                <span class="stat-label">Pending</span>
            </div>
            <div class="pv-stat-pill pill-success">
                <span class="stat-number">{{ $stats['approved'] }}</span>
                <span class="stat-label">Approved</span>
            </div>
            <div class="pv-stat-pill pill-danger">
                <span class="stat-number">{{ $stats['rejected'] }}</span>
                <span class="stat-label">Rejected</span>
            </div>
            <div class="pv-stat-pill">
                <span class="stat-number">{{ $stats['total'] }}</span>
                <span class="stat-label">Total</span>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="pv-filter-card">
        <span class="pv-filter-label">Filter by</span>
        <div class="pv-filter-pills">
            <a href="{{ route('branch.payments.verification.index') }}"
               class="pv-pill-btn pill-all {{ !request('status') ? 'active' : '' }}">
                <span class="dot"></span> All
            </a>
            <a href="{{ route('branch.payments.verification.index', ['status' => 'pending']) }}"
               class="pv-pill-btn pill-pending {{ request('status') === 'pending' ? 'active' : '' }}">
                <span class="dot"></span> Pending
            </a>
            <a href="{{ route('branch.payments.verification.index', ['status' => 'approved']) }}"
               class="pv-pill-btn pill-approved {{ request('status') === 'approved' ? 'active' : '' }}">
                <span class="dot"></span> Approved
            </a>
            <a href="{{ route('branch.payments.verification.index', ['status' => 'rejected']) }}"
               class="pv-pill-btn pill-rejected {{ request('status') === 'rejected' ? 'active' : '' }}">
                <span class="dot"></span> Rejected
            </a>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="pv-table-card">
        <div class="pv-table-header">
            <div class="pv-table-title">
                <div class="pv-table-title-icon">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
                Payment Proofs
            </div>
            @if($stats['pending'] > 0)
                <button type="button" class="pv-bulk-btn" data-bs-toggle="modal" data-bs-target="#bulkApproveModal">
                    <i class="bi bi-check-all"></i>
                    Bulk Approve
                </button>
            @endif
        </div>

    {{-- Payment Cards Grid --}}
    @if($paymentProofs->count() > 0)
        <div class="row g-4">
            @foreach($paymentProofs as $proof)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="background: var(--pv-surface); border: 2px solid var(--pv-border) !important; transition: transform 0.2s, box-shadow 0.2s;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                @if($proof->status === 'pending')
                                    <input type="checkbox" name="payment_proof_ids[]" value="{{ $proof->id }}" class="pv-checkbox proof-checkbox me-2" style="width: 18px; height: 18px;">
                                @endif
                                <div style="font-size: 1rem; font-weight: 700; color: var(--pv-text-1); line-height: 1.3;">{{ $proof->laundry->tracking_number }}</div>
                                <div style="font-size: 0.85rem; color: var(--pv-text-3); margin-top: 0.25rem;">{{ $proof->laundry->service->name ?? 'N/A' }}</div>
                            </div>
                            @if($proof->status === 'pending')
                                <span class="pv-badge pv-badge-pending" style="font-size: 0.75rem; padding: 0.35rem 0.75rem;">Pending</span>
                            @elseif($proof->status === 'approved')
                                <span class="pv-badge pv-badge-approved" style="font-size: 0.75rem; padding: 0.35rem 0.75rem;">Approved</span>
                            @else
                                <span class="pv-badge pv-badge-rejected" style="font-size: 0.75rem; padding: 0.35rem 0.75rem;">Rejected</span>
                            @endif
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-person-circle me-2" style="color: var(--pv-text-3); font-size: 1.3rem;"></i>
                                <div>
                                    <div style="font-size: 0.95rem; font-weight: 600; color: var(--pv-text-1); line-height: 1.3;">{{ $proof->laundry->customer->name }}</div>
                                    <div style="font-size: 0.8rem; color: var(--pv-text-3); margin-top: 0.15rem;">{{ $proof->laundry->customer->phone }}</div>
                                </div>
                            </div>
                            @if($proof->reference_number)
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-hash me-2" style="color: var(--pv-text-3); font-size: 1rem;"></i>
                                    <span class="cell-mono" style="font-size: 0.85rem;">{{ $proof->reference_number }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-start pt-3 mb-3" style="border-top: 2px solid var(--pv-border);">
                            <div>
                                <small style="color: var(--pv-text-3); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600; display: block; margin-bottom: 0.4rem;">Amount Paid</small>
                                <div style="font-family: var(--pv-mono); font-weight: 700; font-size: 1.4rem; color: var(--pv-success); line-height: 1;">₱{{ number_format($proof->amount, 2) }}</div>
                                <div style="font-family: var(--pv-mono); font-size: 0.75rem; color: var(--pv-text-3); margin-top: 0.3rem;">Expected ₱{{ number_format($proof->laundry->total_amount, 2) }}</div>
                                @if($proof->amount != $proof->laundry->total_amount)
                                    <span class="pv-badge pv-badge-rejected" style="font-size: 0.65rem; padding: 0.25rem 0.6rem; margin-top: 0.4rem; display: inline-block;">Mismatch</span>
                                @endif
                            </div>
                            <div class="text-end">
                                <small style="color: var(--pv-text-3); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600; display: block; margin-bottom: 0.4rem;">Submitted</small>
                                <div style="font-size: 0.85rem; color: var(--pv-text-2); font-weight: 600;">{{ $proof->created_at->format('M d, Y') }}</div>
                                <div style="font-size: 0.75rem; color: var(--pv-text-3); margin-top: 0.15rem;">{{ $proof->created_at->format('h:i A') }}</div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="{{ route('branch.payments.verification.show', $proof) }}" class="btn btn-primary flex-fill" style="border-radius: 10px; font-size: 0.85rem; padding: 0.65rem; font-weight: 600;">
                                <i class="bi bi-receipt"></i> View Details
                            </a>
                            <a href="{{ route('branch.laundries.show', $proof->laundry) }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-size: 0.85rem; padding: 0.65rem;">
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
            <div class="pv-pagination" style="margin-top: 2rem; background: var(--pv-surface); border: 1px solid var(--pv-border); border-radius: var(--pv-radius); padding: 1.25rem;">
                {{ $paymentProofs->links() }}
            </div>
        @endif
    @else
        <div class="pv-empty">
            <div class="pv-empty-icon">
                <i class="bi bi-inbox"></i>
            </div>
            <div class="pv-empty-title">No payment proofs found</div>
            <div class="pv-empty-sub">Try adjusting your filter or check back later</div>
        </div>
    @endif
    </div>

</div>

{{-- Bulk Approve Modal --}}
<div class="modal fade pv-modal" id="bulkApproveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('branch.payments.verification.bulk-approve') }}" id="bulkApproveForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <div class="modal-title-icon">
                            <i class="bi bi-check-all"></i>
                        </div>
                        Bulk Approve Payments
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: var(--bs-btn-close-filter, none);"></button>
                </div>
                <div class="modal-body">
                    <p style="color: var(--pv-text-2); margin-bottom: 0.5rem;">
                        This will approve all selected payment proofs and update their laundry statuses accordingly.
                    </p>
                    <div id="selectedCount">
                        <div class="pv-selected-info">
                            <i class="bi bi-check-circle-fill"></i>
                            <span><span class="count" id="countNum">0</span> payment(s) selected</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-confirm">
                        <i class="bi bi-check-all me-1"></i>Approve Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll   = document.getElementById('selectAll');
    const checkboxes  = document.querySelectorAll('.proof-checkbox');
    const form        = document.getElementById('bulkApproveForm');
    const countNum    = document.getElementById('countNum');

    function getSelected() {
        return document.querySelectorAll('.proof-checkbox:checked');
    }

    function updateUI() {
        const selected = getSelected();
        const n = selected.length;
        if (countNum) countNum.textContent = n;
        if (selectAll) {
            selectAll.indeterminate = n > 0 && n < checkboxes.length;
            selectAll.checked       = checkboxes.length > 0 && n === checkboxes.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateUI();
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateUI));
    updateUI();

    if (form) {
        form.addEventListener('submit', function (e) {
            const selected = getSelected();

            // Remove stale hidden inputs
            form.querySelectorAll('input[name="payment_proof_ids[]"]').forEach(i => i.remove());

            if (selected.length === 0) {
                e.preventDefault();
                alert('Please select at least one payment proof to approve.');
                return;
            }

            selected.forEach(cb => {
                const input = document.createElement('input');
                input.type  = 'hidden';
                input.name  = 'payment_proof_ids[]';
                input.value = cb.value;
                form.appendChild(input);
            });
        });
    }

    // Auto-refresh for pending payments (every 30 seconds)
    const currentStatus = new URLSearchParams(window.location.search).get('status');
    if (!currentStatus || currentStatus === 'pending') {
        let refreshInterval = setInterval(function() {
            // Only refresh if user is viewing pending or all payments
            if (document.visibilityState === 'visible') {
                location.reload();
            }
        }, 30000); // 30 seconds

        // Clear interval when page is hidden to save resources
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden') {
                clearInterval(refreshInterval);
            }
        });
    }
});
</script>
@endpush

@endsection
