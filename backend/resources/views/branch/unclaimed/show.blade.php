@extends('branch.layouts.app')

@section('title', 'Unclaimed Laundry Details')
@section('page-title', 'Unclaimed Laundry')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <a href="{{ route('branch.unclaimed.index') }}" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left me-1"></i> Back to Unclaimed List
            </a>
            <h4 class="fw-bold mt-2 mb-1">Laundry #{{ $laundry->tracking_number }}</h4>
            <div class="d-flex align-items-center gap-2">
                @php
                    $days = $laundry->days_unclaimed;
                    $urgency = $laundry->unclaimed_status;
                    $color = $laundry->unclaimed_color;
                @endphp
                <span class="badge bg-{{ $color }} fs-6">
                    {{ $days }} Days Unclaimed
                </span>
                @switch($urgency)
                    @case('critical')
                        <span class="badge bg-danger">🚨 Critical</span>
                        @break
                    @case('urgent')
                        <span class="badge bg-warning text-dark">⚠️ Urgent</span>
                        @break
                    @case('warning')
                        <span class="badge bg-info">⏰ Warning</span>
                        @break
                    @default
                        <span class="badge bg-secondary">📌 Pending</span>
                @endswitch
            </div>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('branch.unclaimed.send-reminder', $laundry) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-bell me-1"></i> Send Reminder
                </button>
            </form>
            <form action="{{ route('branch.unclaimed.mark-claimed', $laundry) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-success" onclick="return confirm('Mark as claimed?')">
                    <i class="bi bi-check-lg me-1"></i> Mark Claimed
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        {{-- Left Column --}}
        <div class="col-lg-8">
            {{-- Alert for Critical Laundries --}}
            @if($urgency === 'critical')
                <div class="alert alert-danger d-flex align-items-center mb-4">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                    <div>
                        <strong>Critical Alert!</strong> This laundry has been unclaimed for {{ $days }} days.
                        According to policy, items may be disposed after 30 days.
                        <strong>{{ max(0, 30 - $days) }} days remaining.</strong>
                    </div>
                </div>
            @elseif($urgency === 'urgent')
                <div class="alert alert-warning d-flex align-items-center mb-4">
                    <i class="bi bi-exclamation-circle-fill fs-4 me-3"></i>
                    <div>
                        <strong>Urgent!</strong> This laundry has been unclaimed for {{ $days }} days.
                        Storage fees of ₱{{ number_format($laundry->calculated_storage_fee, 2) }} may apply.
                    </div>
                </div>
            @endif

            {{-- Laundry Details --}}
            <div class="card border-0 shadow-sm mb-4" style="background-color: var(--surface);">
                <div class="card-header py-3" style="background-color: var(--surface); border-bottom: 1px solid var(--border);">
                    <h5 class="mb-0" style="color: var(--text-1);"><i class="bi bi-box me-2"></i>Laundry Details</h5>
                </div>
                <div class="card-body" style="color: var(--text-1);">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Tracking Number</label>
                            <p class="fw-bold mb-2" style="color: var(--text-1);">{{ $laundry->tracking_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Service</label>
                            <p class="mb-2" style="color: var(--text-1);">{{ $laundry->service->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Weight</label>
                            <p class="mb-2" style="color: var(--text-1);">{{ $laundry->formatted_weight }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Total Amount</label>
                            <p class="fw-bold text-primary fs-5 mb-2" style="color: var(--text-1);">{{ $laundry->formatted_total }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Received Date</label>
                            <p class="mb-2" style="color: var(--text-1);">{{ $laundry->received_at?->format('M d, Y h:i A') ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Ready Date</label>
                            <p class="mb-2" style="color: var(--text-1);">{{ $laundry->ready_at?->format('M d, Y h:i A') ?? 'N/A' }}</p>
                        </div>
                    </div>

                    @if($laundry->calculated_storage_fee > 0)
                        <hr>
                        <div class="bg-warning bg-opacity-10 rounded p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Storage Fee ({{ $days - 7 }} days @ ₱10/day)</span>
                                <span class="fw-bold text-warning">₱{{ number_format($laundry->calculated_storage_fee, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                <span class="fw-bold">Total with Storage Fee</span>
                                <span class="fw-bold text-danger fs-5">₱{{ number_format($laundry->total_amount + $laundry->calculated_storage_fee, 2) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Customer Details --}}
            <div class="card border-0 shadow-sm mb-4" style="background-color: var(--surface);">
                <div class="card-header py-3" style="background-color: var(--surface); border-bottom: 1px solid var(--border);">
                    <h5 class="mb-0" style="color: var(--text-1);"><i class="bi bi-person me-2"></i>Customer Information</h5>
                </div>
                <div class="card-body" style="color: var(--text-1);">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Name</label>
                            <p class="fw-bold mb-2" style="color: var(--text-1);">{{ $laundry->customer->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Phone</label>
                            <p class="mb-2" style="color: var(--text-1);">{{ $laundry->customer->phone }}</p>
                        </div>
                        @if($laundry->customer->email)
                            <div class="col-md-6">
                                <label class="text-muted small">Email</label>
                                <p class="mb-2">
                                    <a href="mailto:{{ $laundry->customer->email }}" class="text-decoration-none" style="color: var(--text-1);">{{ $laundry->customer->email }}</a>
                                </p>
                            </div>
                        @endif
                        @if($laundry->customer->address)
                            <div class="col-12">
                                <label class="text-muted small">Address</label>
                                <p class="mb-2" style="color: var(--text-1);">{{ $laundry->customer->address }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Unclaimed Timeline --}}
            <div class="card border-0 shadow-sm" style="background-color: var(--surface);">
                <div class="card-header py-3" style="background-color: var(--surface); border-bottom: 1px solid var(--border);">
                    <h5 class="mb-0" style="color: var(--text-1);"><i class="bi bi-clock-history me-2"></i>Unclaimed Timeline</h5>
                </div>
                <div class="card-body" style="color: var(--text-1);">
                    @php
                        $timeline = $laundry->getUnclaimedTimeline();
                    @endphp

                    <div class="timeline">
                        @foreach($timeline as $milestone)
                            <div class="timeline-item d-flex mb-3">
                                <div class="timeline-marker me-3">
                                    @if($milestone['completed'])
                                        <span class="badge rounded-pill bg-{{ $milestone['status'] }}">
                                            <i class="bi bi-check"></i>
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-light text-dark border">
                                            {{ $milestone['day'] }}
                                        </span>
                                    @endif
                                </div>
                                <div class="timeline-content flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <strong class="{{ $milestone['completed'] ? 'text-' . $milestone['status'] : 'text-muted' }}" style="color: var(--text-1);">
                                            Day {{ $milestone['day'] }}: {{ $milestone['label'] }}
                                        </strong>
                                        <small class="text-muted">{{ $milestone['date']->format('M d, Y') }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-lg-4">
            {{-- Quick Stats --}}
            <div class="card border-0 shadow-sm mb-4" style="background-color: var(--surface);">
                <div class="card-header py-3" style="background-color: var(--surface); border-bottom: 1px solid var(--border);">
                    <h6 class="mb-0" style="color: var(--text-1);"><i class="bi bi-graph-up me-2"></i>Quick Stats</h6>
                </div>
                <div class="card-body" style="color: var(--text-1);">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Days Unclaimed</span>
                        <span class="fw-bold text-{{ $color }}" style="color: var(--text-1);">{{ $days }} days</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Reminders Sent</span>
                        <span class="fw-bold" style="color: var(--text-1);">{{ $laundry->reminder_count ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Last Reminder</span>
                        <span style="color: var(--text-1);">{{ $laundry->last_reminder_at?->diffForHumans() ?? 'Never' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Storage Fee</span>
                        <span class="fw-bold text-warning" style="color: var(--text-1);">₱{{ number_format($laundry->calculated_storage_fee, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Days Until Disposal</span>
                        <span class="fw-bold text-{{ $laundry->days_until_disposal <= 7 ? 'danger' : 'secondary' }}" style="color: var(--text-1);">
                            {{ $laundry->days_until_disposal }} days
                        </span>
                    </div>
                </div>
            </div>

            {{-- Reminder History --}}
            <div class="card border-0 shadow-sm mb-4" style="background-color: var(--surface);">
                <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background-color: var(--surface); border-bottom: 1px solid var(--border);">
                    <h6 class="mb-0" style="color: var(--text-1);"><i class="bi bi-bell-history me-2"></i>Reminder History</h6>
                    <span class="badge bg-secondary">{{ $reminderHistory->count() }}</span>
                </div>
                <div class="card-body p-0" style="color: var(--text-1);">
                    @forelse($reminderHistory as $reminder)
                        <div class="p-3 border-bottom" style="border-bottom-color: var(--border) !important;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold small" style="color: var(--text-1);">{{ $reminder->title }}</div>
                                    <div class="text-muted small">{{ Str::limit($reminder->body, 60) }}</div>
                                </div>
                                <div class="text-end">
                                    @if($reminder->fcm_status === 'sent')
                                        <span class="badge bg-success">Sent</span>
                                    @elseif($reminder->fcm_status === 'failed')
                                        <span class="badge bg-danger">Failed</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </div>
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="bi bi-clock me-1"></i>{{ $reminder->created_at->format('M d, Y h:i A') }}
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                            <small>No reminders sent yet</small>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Activity Log --}}
            <div class="card border-0 shadow-sm" style="background-color: var(--surface);">
                <div class="card-header py-3" style="background-color: var(--surface); border-bottom: 1px solid var(--border);">
                    <h6 class="mb-0" style="color: var(--text-1);"><i class="bi bi-activity me-2"></i>Activity Log</h6>
                </div>
                <div class="card-body p-0" style="max-height: 300px; overflow-y: auto; color: var(--text-1);">
                    @forelse($laundry->statusHistories->take(10) as $history)
                        <div class="p-3 border-bottom" style="border-bottom-color: var(--border) !important;">
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-secondary">{{ ucfirst($history->status) }}</span>
                                <small class="text-muted">{{ $history->created_at->diffForHumans() }}</small>
                            </div>
                            @if($history->notes)
                                <div class="small mt-1" style="color: var(--text-1);">{{ $history->notes }}</div>
                            @endif
                            @if($history->changedBy)
                                <div class="small text-muted">By: {{ $history->changedBy->name }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">
                            <small>No activity logged</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-marker {
    width: 30px;
    text-align: center;
}
</style>
@endsection
