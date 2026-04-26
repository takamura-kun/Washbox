@extends('branch.layouts.app')

@section('title', 'Laundry Details')
@section('page-title', 'Laundry #' . $laundry->tracking_number)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/laundry.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dark-mode-fixes.css') }}">
    <style>
        .info-card { border-left: 4px solid #3D3B6B; }
        .section-card {
            background: var(--card-bg, #fff);
            border: 1px solid var(--border-color, #e5e7eb);
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 12px;
        }
        .section-card h6 {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color, #f0f0f0);
        }
        .meta-row { display: flex; flex-direction: column; margin-bottom: 8px; }
        .meta-row label { font-size: 0.68rem; color: #9ca3af; margin-bottom: 1px; }
        .meta-row .val { font-size: 0.82rem; font-weight: 600; }

        /* Timeline */
        .tl-wrap { position: relative; padding-left: 28px; }
        .tl-wrap .tl-item { position: relative; padding-bottom: 16px; }
        .tl-wrap .tl-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: -18px; top: 22px; bottom: 0;
            width: 2px;
            background: #e5e7eb;
        }
        .tl-dot {
            position: absolute;
            left: -26px; top: 4px;
            width: 18px; height: 18px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.55rem;
            color: #fff;
        }
        .tl-dot.done  { background: #10b981; }
        .tl-dot.pend  { background: #d1d5db; }
        .tl-label { font-size: 0.78rem; font-weight: 600; }
        .tl-time  { font-size: 0.68rem; color: #9ca3af; }

        /* Status history */
        .hist-item { display: flex; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--border-color, #f3f4f6); }
        .hist-item:last-child { border-bottom: none; }
        .hist-icon {
            width: 30px; height: 30px; min-width: 30px;
            border-radius: 50%;
            background: #f3f4f6;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem;
        }
        .hist-text .hw { font-size: 0.78rem; font-weight: 600; }
        .hist-text .hm { font-size: 0.68rem; color: #9ca3af; }

        /* Pricing table */
        .price-table td { padding: 5px 4px; font-size: 0.78rem; border: none; }
        .price-table tr.divider td { border-top: 1px solid var(--border-color, #e5e7eb); padding-top: 8px; }
        .price-table tr.total td { font-size: 0.9rem; font-weight: 700; border-top: 2px solid var(--border-color, #e5e7eb); padding-top: 8px; }

        /* Action buttons */
        .action-btn { font-size: 0.78rem; padding: 6px 10px; }
    </style>
@endpush

@section('content')
<div class="row g-3">

    {{-- ══════════════ LEFT COLUMN ══════════════ --}}
    <div class="col-lg-8">

        {{-- Laundry Information --}}
        <div class="section-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Laundry Information</h6>
                <span class="badge {{ $laundry->status === 'completed' ? 'bg-success' : ($laundry->status === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark') }}">
                    {{ $laundry->status_label }}
                </span>
            </div>
            <div class="row g-0">
                <div class="col-6 col-md-3">
                    <div class="meta-row">
                        <label>Tracking #</label>
                        <span class="val" style="font-size:0.75rem;">{{ $laundry->tracking_number }}</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="meta-row">
                        <label>Branch</label>
                        <span class="val"><span class="badge bg-secondary">{{ $laundry->branch->name }}</span></span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="meta-row">
                        <label>Service</label>
                        <span class="val">{{ $laundry->service ? $laundry->service->name : 'Promotion Only' }}</span>
                        @if($laundry->service)
                            <span style="font-size:0.68rem;color:#9ca3af;">{{ $laundry->service->service_type_label }}</span>
                        @endif
                    </div>
                </div>
                @if($laundry->number_of_loads)
                <div class="col-6 col-md-3">
                    <div class="meta-row">
                        <label>{{ $laundry->service && $laundry->service->service_type === 'special_item' ? 'Pieces' : 'Loads' }}</label>
                        <span class="val">{{ $laundry->number_of_loads }}</span>
                        @if($laundry->weight)
                            <span style="font-size:0.68rem;color:#9ca3af;">{{ number_format($laundry->weight,1) }} kg total</span>
                        @endif
                    </div>
                </div>
                @endif
                <div class="col-6 col-md-3">
                    <div class="meta-row">
                        <label>Created By</label>
                        <span class="val">{{ $laundry->createdBy->name }}</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="meta-row">
                        <label>Created At</label>
                        <span class="val">{{ $laundry->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
                @if($laundry->staff)
                <div class="col-6 col-md-3">
                    <div class="meta-row">
                        <label>Assigned Staff</label>
                        <span class="val">{{ $laundry->staff->name }}</span>
                    </div>
                </div>
                @endif
                @if($laundry->pickup_request_id)
                <div class="col-6 col-md-3">
                    <div class="meta-row">
                        <label>Pickup Request</label>
                        <span class="val">
                            <a href="{{ route('branch.pickups.show', $laundry->pickup_request_id) }}" class="badge bg-info text-decoration-none">
                                #{{ $laundry->pickup_request_id }}
                            </a>
                        </span>
                    </div>
                </div>
                @endif
            </div>
            @if($laundry->notes)
                <div class="alert alert-info py-2 px-3 mb-0 mt-2" style="font-size:0.78rem;">
                    <i class="bi bi-sticky me-1"></i><strong>Notes:</strong> {{ $laundry->notes }}
                </div>
            @endif
        </div>

        {{-- Customer Information --}}
        <div class="section-card">
            <h6>Customer Information</h6>
            <div class="d-flex align-items-center gap-3 mb-2">
                @if($laundry->customer->profile_photo_url)
                    <img src="{{ $laundry->customer->profile_photo_url }}" alt="{{ $laundry->customer->name }}"
                        class="rounded-circle" style="width:44px;height:44px;object-fit:cover;">
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width:44px;height:44px;background:#e5e7eb;font-size:1.1rem;font-weight:700;color:#6b7280;">
                        {{ strtoupper(substr($laundry->customer->name,0,1)) }}
                    </div>
                @endif
                <div>
                    <div class="fw-semibold" style="font-size:0.85rem;">{{ $laundry->customer->name }}</div>
                    <div class="text-muted" style="font-size:0.72rem;">{{ $laundry->customer->phone }}</div>
                    @if($laundry->customer->email)
                        <div class="text-muted" style="font-size:0.72rem;">{{ $laundry->customer->email }}</div>
                    @endif
                </div>
                <div class="ms-auto text-end">
                    <span class="badge bg-{{ $laundry->customer->isWalkIn() ? 'secondary' : 'primary' }}">
                        {{ $laundry->customer->registration_type_label }}
                    </span>
                    <div class="text-muted mt-1" style="font-size:0.68rem;">
                        {{ $laundry->customer->laundries()->count() }} laundries
                    </div>
                </div>
            </div>
        </div>

        {{-- Pricing Breakdown --}}
        <div class="section-card">
            <h6>Pricing Breakdown</h6>
            <table class="table price-table mb-0 w-100">
                @if($laundry->service)
                    @php
                        $isSpecial = $laundry->service->service_type === 'special_item';
                        $loads = $laundry->number_of_loads ?? 1;
                        $unit  = $isSpecial ? 'piece' : 'load';
                        $units = $isSpecial ? 'pieces' : 'loads';
                    @endphp
                    <tr>
                        <td>
                            <span class="fw-semibold">{{ $laundry->service->name }}</span>
                            <span class="text-muted ms-1">
                                {{ $loads }} {{ $loads > 1 ? $units : $unit }}
                                × ₱{{ number_format($laundry->service->pricing_type === 'per_piece' ? $laundry->service->price_per_piece : $laundry->service->price_per_load, 2) }}/{{ $laundry->service->pricing_type === 'per_piece' ? 'piece' : 'load' }}
                                @if($laundry->weight && !$isSpecial)
                                    &bull; {{ number_format($laundry->weight,1) }} kg
                                @endif
                            </span>
                        </td>
                        <td class="text-end fw-semibold">₱{{ number_format($laundry->subtotal,2) }}</td>
                    </tr>
                @elseif($laundry->promotion)
                    @php
                        $promoSubtotal = $laundry->promotion_override_total
                            ?? ($laundry->promotion_price_per_load * ($laundry->number_of_loads ?? 1))
                            ?? $laundry->subtotal;
                    @endphp
                    <tr>
                        <td>
                            <span class="fw-semibold">{{ $laundry->promotion->name }}</span>
                            @if($laundry->number_of_loads)
                                <span class="text-muted">({{ $laundry->number_of_loads }} load{{ $laundry->number_of_loads > 1 ? 's' : '' }})</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">₱{{ number_format($promoSubtotal,2) }}</td>
                    </tr>
                @endif

                {{-- Add-ons --}}
                @php
                    $addonCollection = ($laundry->inventoryItems && $laundry->inventoryItems->count())
                        ? $laundry->inventoryItems
                        : (($laundry->addons && $laundry->addons->count()) ? $laundry->addons : collect());
                    $hasAddons = $addonCollection->count() > 0;
                @endphp
                @if($hasAddons)
                    <tr class="divider">
                        <td colspan="2"><strong>Add-ons</strong></td>
                    </tr>
                    @foreach($addonCollection as $item)
                        <tr>
                            <td class="ps-2">
                                <i class="bi bi-plus-circle text-success me-1"></i>
                                {{ $item->name }}
                                @if(isset($item->brand) && $item->brand)
                                    <span class="badge bg-secondary">{{ $item->brand }}</span>
                                @endif
                                <span class="text-muted">
                                    ({{ $item->pivot->quantity }} {{ $item->distribution_unit ?? '' }} × ₱{{ number_format($item->pivot->price_at_purchase,2) }})
                                </span>
                            </td>
                            <td class="text-end">₱{{ number_format($item->pivot->price_at_purchase * $item->pivot->quantity,2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="text-end text-muted">Add-ons total</td>
                        <td class="text-end fw-semibold">₱{{ number_format($laundry->addons_total,2) }}</td>
                    </tr>
                @endif

                {{-- Pickup & Delivery --}}
                @if($laundry->pickup_fee > 0 || $laundry->delivery_fee > 0)
                    <tr class="divider">
                        <td colspan="2"><strong>Pickup &amp; Delivery</strong></td>
                    </tr>
                    @if($laundry->pickup_fee > 0)
                        <tr>
                            <td class="ps-2"><i class="bi bi-truck text-primary me-1"></i> Pickup Fee</td>
                            <td class="text-end">₱{{ number_format($laundry->pickup_fee,2) }}</td>
                        </tr>
                    @endif
                    @if($laundry->delivery_fee > 0)
                        <tr>
                            <td class="ps-2"><i class="bi bi-truck text-success me-1"></i> Delivery Fee</td>
                            <td class="text-end">₱{{ number_format($laundry->delivery_fee,2) }}</td>
                        </tr>
                    @endif
                @endif

                {{-- Promotion Discount --}}
                @if($laundry->promotion && $laundry->discount_amount > 0 && $laundry->promotion->application_type !== 'per_load_override')
                    <tr class="text-success">
                        <td><i class="bi bi-tag me-1"></i>{{ $laundry->promotion->name }}
                            @if($laundry->promotion->promo_code)
                                <span class="text-muted">({{ $laundry->promotion->promo_code }})</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">-₱{{ number_format($laundry->discount_amount,2) }}</td>
                    </tr>
                @endif

                {{-- Total --}}
                <tr class="total">
                    <td>Total Amount</td>
                    <td class="text-end text-primary">₱{{ number_format($laundry->total_amount,2) }}</td>
                </tr>

                {{-- Payment Status --}}
                <tr class="{{ $laundry->payment_status === 'paid' ? 'text-success' : 'text-danger' }}">
                    <td>
                        <i class="bi bi-{{ $laundry->payment_status === 'paid' ? 'check-circle' : 'exclamation-circle' }} me-1"></i>
                        Payment
                        <small class="d-block text-muted" style="font-size:0.68rem;">
                            @if($laundry->payment_status === 'paid')
                                via
                                @if($laundry->payment_method === 'cash')
                                    <span class="badge bg-success"><i class="bi bi-cash"></i> Cash</span>
                                @elseif($laundry->payment_method === 'gcash')
                                    <span class="badge bg-primary"><i class="bi bi-phone"></i> GCash</span>
                                @else
                                    {{ $laundry->payment_method ?? 'N/A' }}
                                @endif
                            @else
                                @if($laundry->payment_method === 'cash')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-cash"></i> Cash – pay at pickup</span>
                                @elseif($laundry->payment_method === 'gcash')
                                    <span class="badge bg-info"><i class="bi bi-phone"></i> GCash – awaiting proof</span>
                                @endif
                            @endif
                        </small>
                    </td>
                    <td class="text-end fw-semibold">
                        @if($laundry->payment_status === 'paid')
                            ₱{{ number_format($laundry->total_amount,2) }}
                            @if($laundry->paid_at)
                                <small class="d-block text-muted" style="font-size:0.68rem;">{{ $laundry->paid_at->format('M d, Y h:i A') }}</small>
                            @endif
                        @else
                            Unpaid
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        {{-- Status History --}}
        <div class="section-card">
            <h6>Status History</h6>
            @foreach($laundry->statusHistories as $history)
                @php
                    $icon = match($history->status) {
                        'received'   => 'inbox',
                        'processing' => 'gear',
                        'ready'      => 'check-circle',
                        'paid'       => 'currency-dollar',
                        'completed'  => 'check-all',
                        'cancelled'  => 'x-circle',
                        default      => 'clock'
                    };
                @endphp
                <div class="hist-item">
                    <div class="hist-icon"><i class="bi bi-{{ $icon }}"></i></div>
                    <div class="hist-text">
                        <div class="hw">
                            {{ ucfirst($history->status) }}
                            @if($history->status === 'paid')
                                <span class="badge bg-success ms-1" style="font-size:0.6rem;">Paid</span>
                            @endif
                        </div>
                        <div class="hm">
                            {{ $history->changedBy ? 'by ' . $history->changedBy->name : 'System' }}
                            &bull; {{ $history->created_at->format('M d, Y h:i A') }}
                        </div>
                        @if($history->notes)
                            <div class="hm mt-1">{{ $history->notes }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    </div>{{-- end col-lg-8 --}}

    {{-- ══════════════ RIGHT COLUMN ══════════════ --}}
    <div class="col-lg-4">

        {{-- Timeline --}}
        <div class="section-card">
            <h6>Laundry Timeline</h6>
            @php
                $timeline = $laundry->getTimeline();
                $pastDone = true;
            @endphp
            <div class="tl-wrap">
                @foreach(['received','processing','ready','paid','completed'] as $stage)
                    @php $isActive = $timeline[$stage] !== null; @endphp
                    <div class="tl-item">
                        <div class="tl-dot {{ $isActive ? 'done' : 'pend' }}">
                            <i class="bi bi-{{ $isActive ? 'check' : 'circle' }}"></i>
                        </div>
                        <div class="tl-label">{{ ucfirst($stage) }}</div>
                        <div class="tl-time">
                            @if($isActive)
                                {{ $timeline[$stage]->format('M d, h:i A') }}
                            @else
                                Pending
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="section-card">
            <h6>Quick Actions</h6>
            <div class="d-grid gap-2">
                @if($laundry->status === 'received')
                    <form action="{{ route('branch.laundries.update-status', $laundry) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="processing">
                        <button type="submit" class="btn btn-primary w-100 action-btn">
                            <i class="bi bi-play-circle me-1"></i>Start Processing
                        </button>
                    </form>
                @endif

                @if($laundry->status === 'processing')
                    <form action="{{ route('branch.laundries.update-status', $laundry) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="ready">
                        <button type="submit" class="btn btn-success w-100 action-btn">
                            <i class="bi bi-check-circle me-1"></i>Mark as Ready
                        </button>
                    </form>
                @endif

                @if($laundry->status === 'ready' && $laundry->payment_status !== 'paid')
                    @if($laundry->payment_method === 'cash')
                        <div class="alert alert-warning py-2 px-3 mb-0" style="font-size:0.75rem;">
                            <i class="bi bi-cash me-1"></i><strong>Cash Payment</strong> – collect cash at pickup.
                        </div>
                    @endif

                    @if($laundry->latestPaymentProof && $laundry->latestPaymentProof->status === 'pending')
                        <div class="alert alert-info py-2 px-3 mb-0" style="font-size:0.75rem;">
                            <i class="bi bi-exclamation-triangle me-1"></i><strong>GCash Proof Submitted</strong> – verify before recording.
                        </div>
                        <div class="row g-2">
                            @if($laundry->latestPaymentProof->proof_image)
                                <div class="col-6">
                                    <button type="button" class="btn btn-info w-100 action-btn" data-bs-toggle="modal" data-bs-target="#paymentProofModal">
                                        <i class="bi bi-eye me-1"></i>View Image
                                    </button>
                                </div>
                            @endif
                            <div class="col-6">
                                <a href="{{ route('branch.payments.verification.show', $laundry->latestPaymentProof) }}" class="btn btn-primary w-100 action-btn">
                                    <i class="bi bi-check-circle me-1"></i>Verify
                                </a>
                            </div>
                        </div>
                    @endif

                    <a href="#" onclick="event.preventDefault();document.getElementById('record-payment-form').submit();"
                       class="btn btn-primary w-100 action-btn">
                        <i class="bi bi-currency-dollar me-1"></i>
                        {{ $laundry->payment_method === 'cash' ? 'Record Cash Payment' : 'Record Payment' }}
                    </a>
                    <form id="record-payment-form" action="{{ route('branch.laundries.record-payment', $laundry) }}" method="POST" style="display:none;">@csrf</form>
                @endif

                @if($laundry->payment_status === 'paid' && !in_array($laundry->status, ['completed','cancelled']))
                    <form action="{{ route('branch.laundries.update-status', $laundry) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="btn btn-success w-100 action-btn">
                            <i class="bi bi-check-all me-1"></i>Mark as Completed
                        </button>
                    </form>
                @endif

                <a href="{{ route('branch.laundries.receipt', $laundry) }}" class="btn btn-outline-primary w-100 action-btn" target="_blank">
                    <i class="bi bi-receipt me-1"></i>View Receipt
                </a>

                @if(!in_array($laundry->status, ['completed','cancelled']))
                    <button type="button" class="btn btn-outline-danger w-100 action-btn" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="bi bi-x-circle me-1"></i>Cancel Laundry
                    </button>
                @endif
            </div>
        </div>

        {{-- Laundry Summary --}}
        <div class="section-card">
            <h6>Laundry Summary</h6>
            <div class="row g-0">
                <div class="col-6">
                    <div class="meta-row">
                        <label>Category</label>
                        <span class="val">
                            @if($laundry->service)
                                @php
                                    $catColor = match($laundry->service->category ?? 'drop_off') {
                                        'drop_off'     => '#3D3B6B',
                                        'self_service' => '#10B981',
                                        'addon'        => '#F59E0B',
                                        default        => '#6B7280',
                                    };
                                @endphp
                                <span class="badge" style="background:{{ $catColor }};">
                                    {{ $laundry->service->category_label ?? ucfirst($laundry->service->category ?? 'Drop Off') }}
                                </span>
                            @else
                                <span class="badge bg-secondary">N/A</span>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="meta-row">
                        <label>Service Type</label>
                        <span class="val">
                            @if($laundry->service)
                                @php
                                    $typeColors = ['regular_clothes'=>'primary','full_service'=>'primary','self_service'=>'success','special_item'=>'warning','addon'=>'info'];
                                @endphp
                                <span class="badge bg-{{ $typeColors[$laundry->service->service_type] ?? 'secondary' }}">
                                    {{ $laundry->service->service_type_label }}
                                </span>
                            @else
                                <span class="badge bg-secondary">Promotion</span>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="meta-row">
                        <label>Pricing Type</label>
                        <span class="val">
                            @if($laundry->service)
                                {{ ucfirst(str_replace('_',' ',$laundry->service->pricing_type ?? 'per_load')) }}
                            @else
                                Promo Fixed
                            @endif
                        </span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="meta-row">
                        <label>Turnaround</label>
                        <span class="val">
                            {{ $laundry->service ? $laundry->service->turnaround_time . 'h' : 'N/A' }}
                        </span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="meta-row">
                        <label>Laundry Age</label>
                        <span class="val">{{ $laundry->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @if($laundry->payment_status !== 'paid')
                <div class="col-6">
                    <div class="meta-row">
                        <label>Days Unclaimed</label>
                        <span class="val {{ $laundry->days_unclaimed >= 3 ? 'text-danger' : '' }}">
                            {{ $laundry->days_unclaimed }} days
                        </span>
                    </div>
                </div>
                @endif
                @if($laundry->promotion)
                <div class="col-12">
                    <div class="meta-row">
                        <label>Promotion Applied</label>
                        <span class="val text-success">
                            {{ $laundry->promotion->name }}
                            @if($laundry->promotion->promo_code)
                                <span class="text-muted" style="font-size:0.68rem;">({{ $laundry->promotion->promo_code }})</span>
                            @endif
                        </span>
                    </div>
                </div>
                @endif
            </div>
        </div>

    </div>{{-- end col-lg-4 --}}
</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('branch.laundries.update-status', $laundry) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="cancelled">
                <div class="modal-header py-2 px-3">
                    <h6 class="modal-title mb-0">Cancel Laundry</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-2 px-3">
                    <div class="alert alert-warning py-2 mb-2" style="font-size:0.78rem;">
                        <i class="bi bi-exclamation-triangle me-1"></i>This action cannot be undone.
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size:0.78rem;">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea name="notes" class="form-control form-control-sm" rows="3" required
                            placeholder="Please provide a reason for cancellation"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger btn-sm">Cancel Laundry</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Payment Proof Modal --}}
@if($laundry->latestPaymentProof && $laundry->latestPaymentProof->proof_image)
    <div class="modal fade" id="paymentProofModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header py-2 px-3">
                    <h6 class="modal-title mb-0">Payment Proof</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-3">
                    <img src="{{ $laundry->latestPaymentProof->proof_image_url }}" alt="Payment Proof"
                         class="img-fluid" style="max-height:65vh;">
                    <div class="mt-2" style="font-size:0.78rem;">
                        <span class="me-3"><strong>Amount:</strong> ₱{{ number_format($laundry->latestPaymentProof->amount,2) }}</span>
                        <span class="me-3"><strong>Method:</strong> {{ ucfirst($laundry->latestPaymentProof->payment_method) }}</span>
                        @if($laundry->latestPaymentProof->reference_number)
                            <span class="me-3"><strong>Ref:</strong> {{ $laundry->latestPaymentProof->reference_number }}</span>
                        @endif
                        <span><strong>Submitted:</strong> {{ $laundry->latestPaymentProof->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection