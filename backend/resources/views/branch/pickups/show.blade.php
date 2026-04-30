@extends('branch.layouts.app')

@section('page-title', 'Pickup Request #' . $pickup->id)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
/* ── Design tokens ─────────────────────────────────────────────────── */
:root {
    --bshow-radius:      18px;
    --bshow-radius-sm:   11px;
    --bshow-pad:         1.35rem;
    --bshow-gap:         1.15rem;
    --bshow-lbl:         0.68rem;
    --bshow-ease:        0.2s ease;
}

/* ── Page wrapper ──────────────────────────────────────────────────── */
.bshow-page {
    display: flex;
    flex-direction: column;
    gap: var(--bshow-gap);
    animation: bshowIn 0.3s ease both;
}
@keyframes bshowIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Card base ─────────────────────────────────────────────────────── */
.bshow-card {
    background: var(--card-bg, #fff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: var(--bshow-radius);
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(15,23,42,0.05);
    transition: box-shadow var(--bshow-ease);
}
.bshow-card:hover { box-shadow: 0 6px 24px rgba(15,23,42,0.09); }

.bshow-card-hd {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.95rem var(--bshow-pad);
    border-bottom: 1px solid var(--border-color, #e2e8f0);
}
.bshow-hd-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
}
.bshow-card-hd h5,
.bshow-card-hd h6 {
    margin: 0;
    font-size: 0.88rem;
    font-weight: 800;
    color: var(--text-primary, #0f172a);
}
.bshow-card-bd { padding: var(--bshow-pad); }

/* ── Hero ──────────────────────────────────────────────────────────── */
.bshow-hero {
    position: relative;
    overflow: hidden;
    background: var(--card-bg, #fff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: var(--bshow-radius);
    padding: var(--bshow-pad);
    box-shadow: 0 4px 20px rgba(15,23,42,0.06);
}
.bshow-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgba(99,102,241,0.05) 0%, rgba(14,165,233,0.03) 60%, transparent 100%);
    pointer-events: none;
}
.bshow-hero-inner {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.9rem;
    margin-bottom: 1.15rem;
}
.bshow-back {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.77rem;
    font-weight: 600;
    color: var(--text-secondary, #64748b);
    text-decoration: none;
    border: 1px solid var(--border-color, #e2e8f0);
    background: transparent;
    padding: 0.28rem 0.7rem;
    border-radius: 8px;
    margin-bottom: 0.75rem;
    transition: all var(--bshow-ease);
}
.bshow-back:hover { background: var(--border-color, #e2e8f0); color: var(--text-primary, #0f172a); }
.bshow-title {
    font-size: clamp(1.3rem, 4vw, 1.75rem);
    font-weight: 900;
    color: var(--text-primary, #0f172a);
    margin: 0;
    letter-spacing: -0.02em;
    line-height: 1.15;
}
.bshow-title span { opacity: 0.45; }

/* ── Status chip ───────────────────────────────────────────────────── */
.bshow-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 1rem;
    border-radius: 999px;
    font-size: 0.74rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    flex-shrink: 0;
}
.bshow-chip.pending   { background: rgba(234,179,8,0.12);   color: #d97706; }
.bshow-chip.accepted  { background: rgba(14,165,233,0.12);  color: #0284c7; }
.bshow-chip.en_route  { background: rgba(99,102,241,0.12);  color: #6366f1; }
.bshow-chip.picked_up { background: rgba(16,185,129,0.12);  color: #059669; }
.bshow-chip.cancelled { background: rgba(239,68,68,0.12);   color: #dc2626; }

/* ── KPI row ───────────────────────────────────────────────────────── */
.bshow-kpis {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.7rem;
}
.bshow-kpi {
    background: var(--card-bg, #fff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: var(--bshow-radius-sm);
    padding: 0.75rem 0.9rem;
    position: relative;
    overflow: hidden;
}
.bshow-kpi::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, #6366f1, #0ea5e9);
    opacity: 0;
    transition: opacity var(--bshow-ease);
}
.bshow-kpi:hover::before { opacity: 1; }
.bshow-kpi-lbl {
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--text-secondary, #64748b);
    margin-bottom: 0.3rem;
}
.bshow-kpi-val {
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Fields ────────────────────────────────────────────────────────── */
.bshow-fields {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
.bshow-field-lbl {
    display: block;
    font-size: var(--bshow-lbl);
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--text-secondary, #64748b);
    margin-bottom: 0.2rem;
}
.bshow-field-val {
    font-size: 0.86rem;
    font-weight: 600;
    color: var(--text-primary, #0f172a);
    line-height: 1.5;
}
.bshow-divider {
    border: none;
    border-top: 1px solid var(--border-color, #e2e8f0);
    margin: 1rem 0;
}

/* ── Geo badge ─────────────────────────────────────────────────────── */
.bshow-geo {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.2rem 0.55rem;
    border-radius: 6px;
    background: rgba(16,185,129,0.1);
    color: #059669;
    border: 1px solid rgba(16,185,129,0.2);
    margin-top: 0.3rem;
}

/* ── Map btns ──────────────────────────────────────────────────────── */
.bshow-map-btns {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    margin-bottom: 0.8rem;
}
.bshow-map-btns .btn {
    font-size: 0.76rem;
    padding: 0.28rem 0.7rem;
    border-radius: 8px;
    font-weight: 600;
}

/* ── Map ───────────────────────────────────────────────────────────── */
.bshow-map {
    height: 230px;
    border-radius: var(--bshow-radius-sm);
    overflow: hidden;
    border: 1px solid var(--border-color, #e2e8f0);
}

/* ── Note box ──────────────────────────────────────────────────────── */
.bshow-note {
    border: 1px dashed var(--border-color, #e2e8f0);
    border-radius: var(--bshow-radius-sm);
    padding: 0.85rem;
    background: rgba(148,163,184,0.05);
}

/* ── Proof section ─────────────────────────────────────────────────── */
.bshow-proof-wrap {
    background: linear-gradient(135deg, rgba(14,165,233,0.05), rgba(16,185,129,0.05));
    border: 1px solid rgba(14,165,233,0.18);
    border-radius: 14px;
    padding: 1.15rem;
}
.bshow-proof-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(14,165,233,0.15));
    border: 1px solid rgba(16,185,129,0.25);
    border-radius: 9px;
    padding: 0.3rem 0.8rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin-bottom: 0.85rem;
}
.bshow-proof-badge i { color: #10b981; }
.bshow-proof-img-wrap {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    background: linear-gradient(135deg, rgba(14,165,233,0.12), rgba(16,185,129,0.12));
    padding: 2px;
}
.bshow-proof-img {
    width: 100%;
    max-height: 190px;
    object-fit: cover;
    border-radius: 10px;
    display: block;
    transition: transform var(--bshow-ease), filter var(--bshow-ease);
}
.bshow-proof-img-wrap:hover .bshow-proof-img {
    transform: scale(1.025);
    filter: brightness(1.08);
}
.bshow-proof-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.26);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity var(--bshow-ease);
}
.bshow-proof-img-wrap:hover .bshow-proof-overlay { opacity: 1; }
.bshow-proof-overlay i { color: #fff; font-size: 1.6rem; }
.bshow-proof-meta {
    margin-top: 0.8rem;
    padding-top: 0.8rem;
    border-top: 1px solid rgba(14,165,233,0.15);
    font-size: 0.79rem;
    color: var(--text-secondary, #64748b);
}

/* ── Timeline ──────────────────────────────────────────────────────── */
.bshow-tl { list-style: none; margin: 0; padding: 0; }
.bshow-tl-item {
    display: flex;
    gap: 0.85rem;
    padding-bottom: 1.1rem;
    position: relative;
}
.bshow-tl-item:last-child { padding-bottom: 0; }
.bshow-tl-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 9px; top: 20px; bottom: 0;
    width: 2px;
    background: var(--border-color, #e2e8f0);
}
.bshow-tl-dot {
    width: 20px; height: 20px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.6rem;
    border: 2px solid;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}
.bshow-tl-dot.created  { border-color: #10b981; color: #10b981; background: rgba(16,185,129,0.1); }
.bshow-tl-dot.accepted { border-color: #0ea5e9; color: #0ea5e9; background: rgba(14,165,233,0.1); }
.bshow-tl-dot.enroute  { border-color: #6366f1; color: #6366f1; background: rgba(99,102,241,0.1); }
.bshow-tl-dot.pickedup { border-color: #10b981; color: #10b981; background: rgba(16,185,129,0.1); }
.bshow-tl-dot.cancelled{ border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.1); }
.bshow-tl-label {
    font-size: 0.84rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    line-height: 1.2;
    margin-bottom: 0.18rem;
}
.bshow-tl-meta {
    font-size: 0.74rem;
    color: var(--text-secondary, #64748b);
}

/* ── Sidebar stack ─────────────────────────────────────────────────── */
.bshow-side { display: flex; flex-direction: column; gap: var(--bshow-gap); }

/* ── Action buttons ────────────────────────────────────────────────── */
.bshow-actions { display: flex; flex-direction: column; gap: 0.55rem; }
.bshow-actions .btn {
    border-radius: var(--bshow-radius-sm);
    font-size: 0.83rem;
    font-weight: 700;
    padding: 0.58rem 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    transition: all var(--bshow-ease);
}
.bshow-actions .btn:not(:disabled):hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.13);
}
.bshow-actions-note {
    font-size: 0.73rem;
    color: var(--text-secondary, #64748b);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

/* ── Alert ─────────────────────────────────────────────────────────── */
.bshow-alert {
    border-radius: var(--bshow-radius-sm);
    font-size: 0.83rem;
}

/* ── Proof modal ───────────────────────────────────────────────────── */
.bshow-modal-bg { background: rgba(0,0,0,0.95); }
.bshow-modal-body {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 80vh;
    padding: 1rem;
}
.bshow-modal-close {
    position: absolute;
    top: 1rem; right: 1rem;
    z-index: 1060;
    width: 40px; height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.18);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: background var(--bshow-ease);
}
.bshow-modal-close:hover { background: rgba(255,255,255,0.22); }

/* ── Responsive ────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
    .bshow-kpis { grid-template-columns: repeat(2,1fr); }
}
@media (max-width: 767px) {
    .bshow-hero,
    .bshow-card-bd { padding: 1rem; }
    .bshow-kpis   { grid-template-columns: repeat(2,1fr); gap: 0.6rem; }
    .bshow-fields { grid-template-columns: 1fr; gap: 0.8rem; }
    .bshow-map    { height: 195px; }
}
@media (max-width: 480px) {
    .bshow-title { font-size: 1.25rem; }
}
</style>
@endpush

@section('content')
<div class="bshow-page">

    {{-- ── Hero ──────────────────────────────────────────────────────── --}}
    <div class="bshow-hero">
        <div class="bshow-hero-inner">
            <div>
                <a href="{{ route('branch.pickups.index') }}" class="bshow-back">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
                <h1 class="bshow-title">Pickup <span>#</span>{{ $pickup->id }}</h1>
            </div>
            @php
                $chipMap = [
                    'pending'   => ['class'=>'pending',   'icon'=>'bi-clock-history',  'label'=>'Pending'],
                    'accepted'  => ['class'=>'accepted',  'icon'=>'bi-check-circle',   'label'=>'Accepted'],
                    'en_route'  => ['class'=>'en_route',  'icon'=>'bi-truck',           'label'=>'En Route'],
                    'picked_up' => ['class'=>'picked_up', 'icon'=>'bi-box-seam',        'label'=>'Picked Up'],
                    'cancelled' => ['class'=>'cancelled', 'icon'=>'bi-x-circle',        'label'=>'Cancelled'],
                ];
                $chip = $chipMap[$pickup->status] ?? ['class'=>'', 'icon'=>'bi-circle', 'label'=>ucfirst($pickup->status)];
            @endphp
            <span class="bshow-chip {{ $chip['class'] }}">
                <i class="bi {{ $chip['icon'] }}"></i>{{ $chip['label'] }}
            </span>
        </div>

        <div class="bshow-kpis">
            <div class="bshow-kpi">
                <div class="bshow-kpi-lbl">Customer</div>
                <div class="bshow-kpi-val">{{ $pickup->customer->name }}</div>
            </div>
            <div class="bshow-kpi">
                <div class="bshow-kpi-lbl">Preferred Date</div>
                <div class="bshow-kpi-val">{{ \Carbon\Carbon::parse($pickup->preferred_date)->format('M d, Y') }}</div>
            </div>
            <div class="bshow-kpi">
                <div class="bshow-kpi-lbl">Branch</div>
                <div class="bshow-kpi-val">{{ $pickup->branch->name }}</div>
            </div>
            <div class="bshow-kpi">
                <div class="bshow-kpi-lbl">Phone</div>
                <div class="bshow-kpi-val">{{ $pickup->phone_number }}</div>
            </div>
        </div>
    </div>

    {{-- ── Flash ──────────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show bshow-alert mb-0" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show bshow-alert mb-0" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Layout ──────────────────────────────────────────────────────── --}}
    <div class="row g-4">

        {{-- LEFT ────────────────────────────────────────────────────────── --}}
        <div class="col-lg-8 d-flex flex-column gap-4">

            {{-- Customer Information --}}
            <div class="bshow-card">
                <div class="bshow-card-hd">
                    <div class="bshow-hd-icon" style="background:rgba(99,102,241,0.1);color:#6366f1"><i class="bi bi-person"></i></div>
                    <h5>Customer Information</h5>
                </div>
                <div class="bshow-card-bd">
                    <div class="bshow-fields">
                        <div>
                            <span class="bshow-field-lbl">Full Name</span>
                            <div class="bshow-field-val">{{ $pickup->customer->name }}</div>
                        </div>
                        <div>
                            <span class="bshow-field-lbl">Customer ID</span>
                            <div class="bshow-field-val">#{{ $pickup->customer->id }}</div>
                        </div>
                        <div>
                            <span class="bshow-field-lbl">Email</span>
                            <div class="bshow-field-val">{{ $pickup->customer->email ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <span class="bshow-field-lbl">Phone</span>
                            <div class="bshow-field-val">{{ $pickup->phone_number }}</div>
                        </div>
                        <div>
                            <span class="bshow-field-lbl">Member Since</span>
                            <div class="bshow-field-val">{{ $pickup->customer->created_at->format('M d, Y') }}</div>
                        </div>
                        @if($pickup->customer->total_laundries !== null)
                        <div>
                            <span class="bshow-field-lbl">Total Laundries</span>
                            <div class="bshow-field-val">{{ $pickup->customer->total_laundries }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Pickup Details --}}
            <div class="bshow-card">
                <div class="bshow-card-hd">
                    <div class="bshow-hd-icon" style="background:rgba(14,165,233,0.1);color:#0ea5e9"><i class="bi bi-geo-alt"></i></div>
                    <h5>Pickup Details</h5>
                </div>
                <div class="bshow-card-bd">
                    <div class="bshow-fields">
                        <div>
                            <span class="bshow-field-lbl">Branch</span>
                            <div class="bshow-field-val">{{ $pickup->branch->name }}</div>
                        </div>
                        <div>
                            <span class="bshow-field-lbl">Preferred Date</span>
                            <div class="bshow-field-val">{{ \Carbon\Carbon::parse($pickup->preferred_date)->format('F d, Y') }}</div>
                        </div>
                        @if($pickup->preferred_time)
                        <div>
                            <span class="bshow-field-lbl">Preferred Time</span>
                            <div class="bshow-field-val">{{ $pickup->preferred_time }}</div>
                        </div>
                        @endif
                        @if($pickup->service)
                        <div>
                            <span class="bshow-field-lbl">Service Requested</span>
                            <div class="bshow-field-val">{{ $pickup->service->name }}</div>
                        </div>
                        @endif
                        @if($pickup->estimated_weight)
                        <div>
                            <span class="bshow-field-lbl">Estimated Weight</span>
                            <div class="bshow-field-val">{{ $pickup->estimated_weight }} kg</div>
                        </div>
                        @endif
                    </div>

                    @if($pickup->promotion)
                        <hr class="bshow-divider">
                        <div>
                            <span class="bshow-field-lbl">Promo Package</span>
                            <div class="bshow-field-val d-flex align-items-center gap-2 flex-wrap mt-1">
                                <i class="bi bi-tag-fill text-primary"></i>
                                <strong>{{ $pickup->promotion->name }}</strong>
                                @if($pickup->promotion->display_price)
                                    <span class="badge bg-primary">₱{{ $pickup->promotion->display_price }} {{ $pickup->promotion->price_unit ?? 'per load' }}</span>
                                @endif
                                @if($pickup->promo_code)
                                    <span class="badge bg-secondary">Code: {{ $pickup->promo_code }}</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <hr class="bshow-divider">

                    <div>
                        <span class="bshow-field-lbl">Pickup Address</span>
                        <div class="bshow-field-val mt-1">{{ $pickup->pickup_address }}</div>
                        @if($pickup->latitude && $pickup->longitude)
                            <div class="bshow-geo">
                                <i class="bi bi-geo-alt-fill"></i>
                                {{ number_format($pickup->latitude,6) }}, {{ number_format($pickup->longitude,6) }}
                            </div>
                        @endif
                    </div>

                    @if($pickup->latitude && $pickup->longitude)
                        <div class="bshow-map-btns mt-3">
                            <a href="https://www.google.com/maps?q={{ $pickup->latitude }},{{ $pickup->longitude }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-geo-alt me-1"></i>Google Maps
                            </a>
                            <a href="https://www.waze.com/ul?ll={{ $pickup->latitude }},{{ $pickup->longitude }}&navigate=yes" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-signpost me-1"></i>Waze
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="bshowCopyCoords(this, {{ $pickup->latitude }}, {{ $pickup->longitude }})">
                                <i class="bi bi-clipboard me-1"></i>Copy Coords
                            </button>
                        </div>
                        <div id="pickup-map" class="bshow-map"></div>
                    @endif

                    @if($pickup->landmark)
                        <hr class="bshow-divider">
                        <div>
                            <span class="bshow-field-lbl">Landmark</span>
                            <div class="bshow-field-val">{{ $pickup->landmark }}</div>
                        </div>
                    @endif

                    @if($pickup->special_instructions)
                        <hr class="bshow-divider">
                        <div class="bshow-note">
                            <span class="bshow-field-lbl">Special Instructions</span>
                            <div class="bshow-field-val text-muted mt-1">{{ $pickup->special_instructions }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Status Timeline --}}
            <div class="bshow-card">
                <div class="bshow-card-hd">
                    <div class="bshow-hd-icon" style="background:rgba(148,163,184,0.15);color:var(--text-secondary,#64748b)"><i class="bi bi-clock-history"></i></div>
                    <h5>Status Timeline</h5>
                </div>
                <div class="bshow-card-bd">
                    <ul class="bshow-tl">
                        <li class="bshow-tl-item">
                            <div class="bshow-tl-dot created"><i class="bi bi-check"></i></div>
                            <div>
                                <div class="bshow-tl-label">Created</div>
                                <div class="bshow-tl-meta">{{ $pickup->created_at->format('M d, Y · g:i A') }}</div>
                                <div class="bshow-tl-meta">{{ $pickup->created_at->diffForHumans() }}</div>
                            </div>
                        </li>
                        @if($pickup->accepted_at)
                        <li class="bshow-tl-item">
                            <div class="bshow-tl-dot accepted"><i class="bi bi-check"></i></div>
                            <div>
                                <div class="bshow-tl-label">Accepted</div>
                                <div class="bshow-tl-meta">{{ $pickup->accepted_at->format('M d, Y · g:i A') }}</div>
                                @if($pickup->assignedStaff)
                                    <div class="bshow-tl-meta">By: {{ $pickup->assignedStaff->name }}</div>
                                @endif
                            </div>
                        </li>
                        @endif
                        @if($pickup->en_route_at)
                        <li class="bshow-tl-item">
                            <div class="bshow-tl-dot enroute"><i class="bi bi-truck" style="font-size:.55rem"></i></div>
                            <div>
                                <div class="bshow-tl-label">En Route</div>
                                <div class="bshow-tl-meta">{{ $pickup->en_route_at->format('M d, Y · g:i A') }}</div>
                            </div>
                        </li>
                        @endif
                        @if($pickup->picked_up_at)
                        <li class="bshow-tl-item">
                            <div class="bshow-tl-dot pickedup"><i class="bi bi-check"></i></div>
                            <div>
                                <div class="bshow-tl-label">Picked Up</div>
                                <div class="bshow-tl-meta">{{ $pickup->picked_up_at->format('M d, Y · g:i A') }}</div>
                            </div>
                        </li>
                        @endif
                        @if($pickup->cancelled_at)
                        <li class="bshow-tl-item">
                            <div class="bshow-tl-dot cancelled"><i class="bi bi-x"></i></div>
                            <div>
                                <div class="bshow-tl-label">Cancelled</div>
                                <div class="bshow-tl-meta">{{ $pickup->cancelled_at->format('M d, Y · g:i A') }}</div>
                                @if($pickup->cancellation_reason)
                                    <div class="bshow-tl-meta mt-1">Reason: {{ $pickup->cancellation_reason }}</div>
                                @endif
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            {{-- Staff Proof Photo --}}
            @if(in_array($pickup->status, ['en_route', 'picked_up']))
            <div class="bshow-card">
                <div class="bshow-card-hd">
                    <div class="bshow-hd-icon" style="background:rgba(16,185,129,0.1);color:#10b981"><i class="bi bi-camera"></i></div>
                    <h5>{{ $pickup->pickup_proof_photo ? 'Staff Proof Photo' : 'Upload Staff Proof Photo' }}</h5>
                </div>
                <div class="bshow-card-bd">
                    @if($pickup->pickup_proof_photo)
                        <div class="bshow-proof-wrap">
                            <div class="bshow-proof-badge"><i class="bi bi-check-circle-fill"></i> Laundry Received</div>
                            <div class="bshow-proof-img-wrap" onclick="bshowOpenModal('{{ asset('storage/pickup-proofs/'.$pickup->pickup_proof_photo) }}')">
                                <img src="{{ asset('storage/pickup-proofs/'.$pickup->pickup_proof_photo) }}" alt="Pickup Proof" class="bshow-proof-img">
                                <div class="bshow-proof-overlay"><i class="bi bi-fullscreen"></i></div>
                            </div>
                            <div class="bshow-proof-meta">
                                <i class="bi bi-clock me-1"></i>Uploaded {{ $pickup->proof_uploaded_at ? \Carbon\Carbon::parse($pickup->proof_uploaded_at)->diffForHumans() : '' }}
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info bshow-alert mb-3">
                            <i class="bi bi-info-circle me-2"></i>Upload a photo when the laundry arrives at the shop.
                        </div>
                        <form action="{{ route('branch.pickups.upload-proof', $pickup->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <input type="file" name="proof_photo" class="form-control" accept="image/jpeg,image/png,image/jpg" required>
                                <small class="text-muted">Max 5 MB · JPEG or PNG</small>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-upload me-1"></i>Upload Proof Photo
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endif

            {{-- Customer Proof Photo --}}
            @if($pickup->customer_proof_photo)
            <div class="bshow-card">
                <div class="bshow-card-hd">
                    <div class="bshow-hd-icon" style="background:rgba(14,165,233,0.1);color:#0ea5e9"><i class="bi bi-image"></i></div>
                    <h5>Customer Proof Photo</h5>
                </div>
                <div class="bshow-card-bd">
                    <div class="bshow-proof-wrap">
                        <div class="bshow-proof-badge"><i class="bi bi-person-check-fill"></i> Customer Verification</div>
                        <div class="bshow-proof-img-wrap" onclick="bshowOpenModal('{{ asset('storage/customer-pickup-proofs/'.$pickup->customer_proof_photo) }}')">
                            <img src="{{ asset('storage/customer-pickup-proofs/'.$pickup->customer_proof_photo) }}" alt="Customer Proof" class="bshow-proof-img">
                            <div class="bshow-proof-overlay"><i class="bi bi-fullscreen"></i></div>
                        </div>
                        <div class="bshow-proof-meta">
                            <i class="bi bi-clock me-1"></i>
                            Uploaded {{ $pickup->customer_proof_uploaded_at ? $pickup->customer_proof_uploaded_at->diffForHumans() : 'at request time' }}
                            &nbsp;·&nbsp;
                            <i class="bi bi-info-circle me-1"></i>Verification photo of laundry items
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>{{-- /col-lg-8 --}}

        {{-- RIGHT ───────────────────────────────────────────────────────── --}}
        <div class="col-lg-4">
            <div class="bshow-side">

                {{-- Quick Actions --}}
                <div class="bshow-card">
                    <div class="bshow-card-hd">
                        <div class="bshow-hd-icon" style="background:rgba(234,179,8,0.12);color:#d97706"><i class="bi bi-lightning-charge"></i></div>
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="bshow-card-bd bshow-actions">
                        @if($pickup->status == 'pending')
                            <form action="{{ route('branch.pickups.accept', $pickup->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Accept and assign to yourself?')">
                                    <i class="bi bi-check-circle"></i> Accept Request
                                </button>
                            </form>
                        @endif

                        @if($pickup->status == 'accepted' && $pickup->assigned_to == auth()->id())
                            <form action="{{ route('branch.pickups.en-route', $pickup->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-truck"></i> Mark as En Route
                                </button>
                            </form>
                        @endif

                        @if($pickup->status == 'en_route' && $pickup->assigned_to == auth()->id())
                            @if($pickup->pickup_proof_photo)
                                <form action="{{ route('branch.pickups.picked-up', $pickup->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-box-seam"></i> Mark as Picked Up
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn btn-secondary w-100" disabled>
                                    <i class="bi bi-box-seam"></i> Mark as Picked Up
                                </button>
                                <div class="bshow-actions-note"><i class="bi bi-exclamation-circle"></i> Upload proof photo first.</div>
                            @endif

                            <button type="button" class="btn btn-outline-primary w-100" onclick="bshowUpdateGPS()">
                                <i class="bi bi-pin-map"></i> Update My Location
                            </button>
                        @endif

                        @if(in_array($pickup->status, ['pending', 'accepted']) && ($pickup->assigned_to == auth()->id() || $pickup->status == 'pending'))
                            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="bi bi-x-circle"></i> Cancel Pickup
                            </button>
                        @endif

                        @if($pickup->status == 'picked_up' && !$pickup->laundries_id)
                            <hr style="border-color:var(--border-color,#e2e8f0);margin:0.2rem 0">
                            <a href="{{ route('branch.laundries.create', ['pickup_id' => $pickup->id]) }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-plus-circle"></i> Create Laundry
                            </a>
                        @endif

                        <hr style="border-color:var(--border-color,#e2e8f0);margin:0.2rem 0">

                        <a href="tel:{{ $pickup->phone_number }}" class="btn btn-outline-success w-100">
                            <i class="bi bi-telephone"></i> Call Customer
                        </a>

                        @if($pickup->customer->email)
                            <a href="mailto:{{ $pickup->customer->email }}" class="btn btn-outline-info w-100">
                                <i class="bi bi-envelope"></i> Email Customer
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Assigned Staff --}}
                @if($pickup->status != 'cancelled')
                <div class="bshow-card">
                    <div class="bshow-card-hd">
                        <div class="bshow-hd-icon" style="background:rgba(99,102,241,0.1);color:#6366f1"><i class="bi bi-person-badge"></i></div>
                        <h6>Assigned Staff</h6>
                    </div>
                    <div class="bshow-card-bd">
                        @if($pickup->assignedStaff)
                            <div class="mb-2">
                                <span class="bshow-field-lbl">Name</span>
                                <div class="bshow-field-val">{{ $pickup->assignedStaff->name }}</div>
                            </div>
                            <div>
                                <span class="bshow-field-lbl">Email</span>
                                <div class="bshow-field-val" style="font-size:.81rem">{{ $pickup->assignedStaff->email }}</div>
                            </div>
                        @else
                            <p class="text-muted mb-0" style="font-size:.83rem">Not assigned yet.</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Linked Laundry --}}
                @if($pickup->laundries_id)
                <div class="bshow-card">
                    <div class="bshow-card-hd">
                        <div class="bshow-hd-icon" style="background:rgba(16,185,129,0.1);color:#10b981"><i class="bi bi-basket3"></i></div>
                        <h6>Linked Laundry</h6>
                    </div>
                    <div class="bshow-card-bd">
                        <div class="mb-3">
                            <span class="bshow-field-lbl">Laundry ID</span>
                            <div class="bshow-field-val">#{{ $pickup->laundries_id }}</div>
                        </div>
                        <a href="{{ route('branch.laundries.show', $pickup->laundries_id) }}" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-box-arrow-up-right me-1"></i>View Laundry
                        </a>
                    </div>
                </div>
                @endif

            </div>
        </div>

    </div>{{-- /row --}}
</div>{{-- /bshow-page --}}

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--bshow-radius);border:1px solid var(--border-color,#e2e8f0)">
            <form action="{{ route('branch.pickups.cancel', $pickup->id) }}" method="POST">
                @csrf
                <div class="modal-header" style="border-color:var(--border-color,#e2e8f0)">
                    <h5 class="modal-title">Cancel Pickup Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Provide a reason for cancellation…"></textarea>
                    </div>
                    <p class="text-danger mb-0" style="font-size:.82rem">
                        <i class="bi bi-exclamation-triangle me-1"></i>The customer will be notified.
                    </p>
                </div>
                <div class="modal-footer" style="border-color:var(--border-color,#e2e8f0)">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Proof Image Modal --}}
<div class="modal fade" id="proofImageModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bshow-modal-bg position-relative">
            <button type="button" class="bshow-modal-close" data-bs-dismiss="modal">
                <i class="bi bi-x-lg"></i>
            </button>
            <div class="bshow-modal-body modal-body">
                <img id="proofModalImage" src="" alt="Proof Photo" style="max-width:100%;max-height:90vh;object-fit:contain;border-radius:12px">
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function bshowOpenModal(src) {
    document.getElementById('proofModalImage').src = src;
    new bootstrap.Modal(document.getElementById('proofImageModal')).show();
}

function bshowCopyCoords(btn, lat, lng) {
    navigator.clipboard.writeText(`${lat}, ${lng}`).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
        btn.classList.replace('btn-outline-success','btn-success');
        setTimeout(() => { btn.innerHTML = orig; btn.classList.replace('btn-success','btn-outline-success'); }, 2000);
    });
}

function bshowUpdateGPS() {
    if (!navigator.geolocation) { alert('Geolocation not supported.'); return; }
    navigator.geolocation.getCurrentPosition(
        pos => fetch("{{ route('branch.pickups.update-location', $pickup->id) }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ latitude: pos.coords.latitude, longitude: pos.coords.longitude })
        }).then(r => r.json()).then(d => alert(d.success ? 'Location updated!' : 'Failed: ' + d.message)),
        () => alert('Unable to retrieve your location.'),
        { enableHighAccuracy: true }
    );
}
</script>

@if($pickup->latitude && $pickup->longitude)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const map = L.map('pickup-map', { zoomControl: true, attributionControl: false })
        .setView([{{ $pickup->latitude }}, {{ $pickup->longitude }}], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    L.marker([{{ $pickup->latitude }}, {{ $pickup->longitude }}])
        .addTo(map)
        .bindPopup('<b>Pickup Location</b><br>{{ addslashes($pickup->pickup_address) }}')
        .openPopup();
});
</script>
@endif
@endpush
