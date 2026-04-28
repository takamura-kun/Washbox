@extends('branch.layouts.app')

@section('page-title', 'Unclaimed Laundry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/unclaimed.css') }}">
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-primary);">
                <i class="bi bi-exclamation-triangle text-warning me-2"></i>Unclaimed Laundry
            </h4>
            <p class="small mb-0" style="color: var(--text-secondary);">Manage and follow up on unclaimed laundries to recover revenue for {{ $currentBranch->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('branch.unclaimed.export') }}" class="btn btn-outline-success btn-sm shadow-sm">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
            <button type="button" class="btn btn-primary btn-sm shadow-sm" id="bulkReminderBtn" disabled>
                <i class="bi bi-send me-1"></i>Send Selected
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show uc-alert shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show uc-alert shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show uc-alert shadow-sm mb-4">
            <i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Key Metrics --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="metric-card-gradient">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-2 opacity-75">Total Value at Risk</h6>
                        <h2 class="fw-bold mb-1">₱{{ number_format($stats['total_value'], 0) }}</h2>
                        <small class="opacity-75">{{ $stats['total'] }} laundries</small>
                    </div>
                    <div class="bg-white bg-opacity-20 p-3 rounded-3">
                        <i class="bi bi-currency-dollar fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="metric-card-modern">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="metric-label">Critical (14+ days)</h6>
                        <h2 class="fw-bold text-danger mb-1">{{ $stats['critical'] }}</h2>
                        <small class="metric-sub">₱{{ number_format($stats['critical_value'] ?? 0, 0) }}</small>
                    </div>
                    <div class="metric-icon-wrapper danger">
                        <i class="bi bi-exclamation-octagon fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="metric-card-modern">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="metric-label">Urgent (7-13 days)</h6>
                        <h2 class="fw-bold mb-1">{{ $stats['urgent'] }}</h2>
                        <small class="metric-sub">Needs attention</small>
                    </div>
                    <div class="metric-icon-wrapper warning">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="metric-card-modern">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="metric-label">Reminders Today</h6>
                        <h2 class="metric-value">{{ $stats['reminders_today'] }}</h2>
                        <small class="metric-sub">Notifications sent</small>
                    </div>
                    <div class="metric-icon-wrapper primary">
                        <i class="bi bi-bell fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Urgency Breakdown --}}
    <div class="urgency-grid mb-4">
        <div class="row text-center g-0">
            <div class="col">
                <a href="{{ route('branch.unclaimed.index', ['urgency' => 'critical']) }}"
                   class="urgency-pill {{ request('urgency') == 'critical' ? 'active-critical' : '' }}">
                    <span class="badge bg-danger fs-6 mb-1">{{ $stats['critical'] }}</span>
                    <strong>🚨 Critical</strong>
                    <span>14+ days</span>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('branch.unclaimed.index', ['urgency' => 'urgent']) }}"
                   class="urgency-pill {{ request('urgency') == 'urgent' ? 'active-urgent' : '' }}">
                    <span class="badge bg-warning text-dark fs-6 mb-1">{{ $stats['urgent'] }}</span>
                    <strong>⚠️ Urgent</strong>
                    <span>7–13 days</span>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('branch.unclaimed.index', ['urgency' => 'warning']) }}"
                   class="urgency-pill {{ request('urgency') == 'warning' ? 'active-warning' : '' }}">
                    <span class="badge bg-info fs-6 mb-1">{{ $stats['warning'] }}</span>
                    <strong>⏰ Warning</strong>
                    <span>3–6 days</span>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('branch.unclaimed.index', ['urgency' => 'pending']) }}"
                   class="urgency-pill {{ request('urgency') == 'pending' ? 'active-pending' : '' }}">
                    <span class="badge bg-secondary fs-6 mb-1">{{ $stats['pending'] }}</span>
                    <strong>📌 Pending</strong>
                    <span>1–2 days</span>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('branch.unclaimed.index') }}"
                   class="urgency-pill {{ !request('urgency') ? 'active-all' : '' }}">
                    <span class="badge bg-primary fs-6 mb-1">{{ $stats['total'] }}</span>
                    <strong>📊 All</strong>
                    <span>Total</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-card-modern mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="filter-label">Search</label>
                <input type="text" name="search" class="filter-input"
                       placeholder="Tracking #, customer, phone..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="filter-label">Urgency</label>
                <select name="urgency" class="filter-select">
                    <option value="">All</option>
                    <option value="critical" {{ request('urgency') == 'critical' ? 'selected' : '' }}>🚨 Critical (14+)</option>
                    <option value="urgent" {{ request('urgency') == 'urgent' ? 'selected' : '' }}>⚠️ Urgent (7-13)</option>
                    <option value="warning" {{ request('urgency') == 'warning' ? 'selected' : '' }}>⏰ Warning (3-6)</option>
                    <option value="pending" {{ request('urgency') == 'pending' ? 'selected' : '' }}>📌 Pending (1-2)</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="filter-label">Min Days</label>
                <input type="number" name="min_days" class="filter-input"
                       placeholder="0" min="0" value="{{ request('min_days') }}">
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="filter-label">Max Days</label>
                <input type="number" name="max_days" class="filter-input"
                       placeholder="30" min="0" value="{{ request('max_days') }}">
            </div>
            <div class="col-lg-2 col-md-12">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('branch.unclaimed.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="unclaimed-table-card">
        <div class="unclaimed-table-header">
            <h6 class="mb-0 fw-bold" style="color: var(--text-primary);">
                <i class="bi bi-list-ul me-2 text-primary"></i>Unclaimed Laundry
            </h6>
            <button type="button" class="btn btn-sm btn-primary" id="bulkReminderBtn2" disabled>
                <i class="bi bi-send me-1"></i>Send Selected
            </button>
        </div>
        <div class="card-body p-0">
            <form id="bulkForm" action="{{ route('branch.unclaimed.bulk-reminders') }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table align-middle mb-0 unclaimed-table">
                        <thead>
                            <tr>
                                <th width="40" class="ps-4">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th>Laundry</th>
                                <th>Customer</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Days</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laundries as $laundry)
                                @php
                                    $days    = $laundry->days_unclaimed ?? 0;
                                    $urgency = $laundry->unclaimed_status ?? 'normal';
                                    $color   = $laundry->unclaimed_color ?? 'secondary';
                                @endphp
                                <tr class="{{ $urgency === 'critical' ? 'row-critical' : ($urgency === 'urgent' ? 'row-warning-row' : '') }}">
                                    <td class="ps-4">
                                        <input type="checkbox" class="form-check-input laundry-checkbox"
                                               name="laundry_ids[]" value="{{ $laundry->id }}">
                                    </td>
                                    <td>
                                        <a href="{{ route('branch.unclaimed.show', $laundry) }}" class="fw-semibold" style="color: var(--primary-color); text-decoration: none;">
                                            {{ $laundry->tracking_number }}
                                        </a>
                                        <div class="small" style="color: var(--text-secondary);">
                                            Ready: {{ $laundry->ready_at?->format('M d, Y') ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold" style="color: var(--text-primary);">{{ $laundry->customer->name ?? 'N/A' }}</div>
                                        <div class="small" style="color: var(--text-secondary);">
                                            <i class="bi bi-telephone me-1"></i>{{ $laundry->customer->phone ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="amount-display">₱{{ number_format($laundry->total_amount, 2) }}</div>
                                        @php $storageFee = $laundry->calculated_storage_fee ?? 0; @endphp
                                        @if($storageFee > 0)
                                            <div class="storage-fee">
                                                +₱{{ number_format($storageFee, 2) }} fee
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="days-badge">{{ $days }}</span>
                                    </td>
                                    <td class="text-center">
                                        @switch($urgency)
                                            @case('critical')
                                                <span class="status-badge-modern critical">🚨 Critical</span>
                                                @break
                                            @case('urgent')
                                                <span class="status-badge-modern urgent">⚠️ Urgent</span>
                                                @break
                                            @case('warning')
                                                <span class="status-badge-modern warning">⏰ Warning</span>
                                                @break
                                            @default
                                                <span class="status-badge-modern pending">📌 Pending</span>
                                        @endswitch
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="action-btn-group">
                                            {{-- Send Reminder --}}
                                            <form action="{{ route('branch.unclaimed.send-reminder', $laundry->id) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="action-btn btn-remind" title="Send Reminder">
                                                    <i class="bi bi-bell"></i>
                                                </button>
                                            </form>

                                            {{-- Mark Claimed --}}
                                            <form action="{{ route('branch.unclaimed.mark-claimed', $laundry->id) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="action-btn btn-claim"
                                                        title="Mark Claimed"
                                                        onclick="return confirm('Mark as claimed?')">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>

                                            {{-- Dispose (only if 30+ days) --}}
                                            <form action="{{ route('branch.unclaimed.mark-disposed', $laundry->id) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                        class="action-btn btn-dispose"
                                                        title="Dispose"
                                                        {{ $days < ($disposalThreshold ?? 30) ? 'disabled' : '' }}
                                                        onclick="return confirm('Dispose this laundry? This cannot be undone.')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="bi bi-emoji-smile fs-1 text-success d-block mb-3"></i>
                                            <h5 class="text-success fw-bold">Excellent!</h5>
                                            <p class="uc-text-muted mb-0">No unclaimed laundry found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

        @if($laundries->hasPages())
            <div class="pagination-footer">
                {{ $laundries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('selectAll')?.addEventListener('change', function () {
    document.querySelectorAll('.laundry-checkbox').forEach(cb => cb.checked = this.checked);
    updateBulkBtn();
});
document.querySelectorAll('.laundry-checkbox').forEach(cb => cb.addEventListener('change', updateBulkBtn));

function updateBulkBtn() {
    const n   = document.querySelectorAll('.laundry-checkbox:checked').length;
    const btn = document.getElementById('bulkReminderBtn');
    const btn2 = document.getElementById('bulkReminderBtn2');

    [btn, btn2].forEach(button => {
        if (button) {
            button.disabled = n === 0;
            button.innerHTML = n > 0
                ? `<i class="bi bi-send me-1"></i> Send (${n})`
                : `<i class="bi bi-send me-1"></i> Send Selected`;
        }
    });
}

[document.getElementById('bulkReminderBtn'), document.getElementById('bulkReminderBtn2')].forEach(btn => {
    btn?.addEventListener('click', function () {
        if (confirm('Send reminders to all selected customers?'))
            document.getElementById('bulkForm').submit();
    });
});
</script>
@endpush
