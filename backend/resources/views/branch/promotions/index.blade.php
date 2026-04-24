@extends('branch.layouts.app')

@section('title', 'Promotions Management')
@section('page-title', 'Promotions & Discounts')

@push('styles')
<style>
/* ── Dark mode: Promotions page ── */
/* Plain .card dark mode is handled by the layout globally.
   Only override promotion-cards that have their own image/gradient bg. */
/* Force image/gradient backgrounds to show in dark mode.
   We use the inline style via JS instead of CSS to beat the layout's !important card rule. */
[data-theme="dark"] .promotion-card[data-has-bg="true"] {
    border-color: transparent !important;
}
/* card-body/header dark styles handled by layout */
[data-theme="dark"] h4.fw-bold { color: #f1f5f9 !important; }
[data-theme="dark"] .text-muted { color: #94a3b8 !important; }
[data-theme="dark"] .form-check-label { color: #f1f5f9 !important; }
/* search */
[data-theme="dark"] .input-group-text { background: #334155 !important; border-color: #334155 !important; color: #94a3b8 !important; }
[data-theme="dark"] .form-control.bg-light { background: #1e293b !important; border-color: #334155 !important; color: #f1f5f9 !important; }
[data-theme="dark"] .form-control.bg-light::placeholder { color: #64748b; }
[data-theme="dark"] .form-control.bg-light:focus { background: #0f172a !important; border-color: #5452a0 !important; box-shadow: 0 0 0 0.25rem rgba(84,82,160,0.2) !important; color: #f1f5f9 !important; }
/* stat icon tints */
[data-theme="dark"] .bg-primary.bg-opacity-10 { background: rgba(99,102,241,0.15) !important; }
[data-theme="dark"] .bg-success.bg-opacity-10 { background: rgba(16,185,129,0.15) !important; }
[data-theme="dark"] .bg-info.bg-opacity-10    { background: rgba(59,130,246,0.15)  !important; }
[data-theme="dark"] .bg-warning.bg-opacity-10 { background: rgba(245,158,11,0.15)  !important; }
/* badges */
[data-theme="dark"] .badge.bg-light { background: #334155 !important; color: #e2e8f0 !important; border-color: #475569 !important; }
[data-theme="dark"] .badge.bg-light.text-dark { color: #e2e8f0 !important; }
[data-theme="dark"] .badge.bg-success   { background: rgba(16,185,129,0.2) !important; color: #4ade80 !important; }
[data-theme="dark"] .badge.bg-secondary { background: rgba(100,116,139,0.2) !important; color: #94a3b8 !important; }
[data-theme="dark"] .badge.bg-primary   { background: rgba(99,102,241,0.2)  !important; color: #a5b4fc !important; }
[data-theme="dark"] .badge.bg-info      { background: rgba(59,130,246,0.2)  !important; color: #60a5fa !important; }
[data-theme="dark"] .badge.bg-warning   { background: rgba(245,158,11,0.2)  !important; color: #fbbf24 !important; }
/* buttons */
[data-theme="dark"] .btn-outline-info    { border-color: #3b82f6; color: #60a5fa; }
[data-theme="dark"] .btn-outline-info:hover { background: #3b82f6; color: #fff; }
[data-theme="dark"] .btn-outline-primary { border-color: #6366f1; color: #a5b4fc; }
[data-theme="dark"] .btn-outline-primary:hover { background: #6366f1; color: #fff; }
[data-theme="dark"] .btn-outline-secondary { border-color: #475569; color: #94a3b8; }
[data-theme="dark"] .btn-outline-secondary:hover { background: #334155; color: #f1f5f9; }
/* ── modal ── */
[data-theme="dark"] .modal-content { background: #1e293b !important; border-color: #334155 !important; }
[data-theme="dark"] .modal-body    { background: #1e293b !important; color: #f1f5f9 !important; }
[data-theme="dark"] .modal-footer  { background: #0f172a !important; border-color: #334155 !important; }
[data-theme="dark"] .modal-header.bg-info { background: #1d4ed8 !important; }
[data-theme="dark"] .modal-title   { color: #fff !important; }
[data-theme="dark"] .btn-close     { filter: invert(1) brightness(1.5); }
/* bg-light info panels inside modal */
[data-theme="dark"] .modal-body .bg-light { background: #0f172a !important; border: 1px solid #334155; }
[data-theme="dark"] .modal-body .bg-light small.text-muted { color: #64748b !important; }
[data-theme="dark"] .modal-body .bg-light h5,
[data-theme="dark"] .modal-body .bg-light strong,
[data-theme="dark"] .modal-body .bg-light p { color: #f1f5f9 !important; }
[data-theme="dark"] .modal-body code { background: #334155; color: #a5b4fc !important; padding: 0.1rem 0.4rem; border-radius: 4px; }
/* discount value */
[data-theme="dark"] #viewPromotionDiscountValue.text-info    { color: #60a5fa !important; }
[data-theme="dark"] #viewPromotionDiscountValue.text-warning { color: #fbbf24 !important; }
[data-theme="dark"] #viewPromotionDisplayPrice { color: #94a3b8 !important; }
/* empty state */
[data-theme="dark"] h5.text-muted { color: #64748b !important; }

/* ── Card base ── */
.card { border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s; }
.card:hover { transform: translateY(-2px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important; }
.btn-group-sm .btn { border-radius: 0.375rem !important; margin-left: 0.1rem; padding: 0.25rem 0.5rem; }

/* ── Promotion Grid ── */
.promotion-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
    padding: 1.5rem;
}

.promotion-card {
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    min-height: 280px;
    display: flex;
    flex-direction: column;
    position: relative;
}

.promotion-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

/* Image overlay */
.promo-image-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.6));
    z-index: 0;
    pointer-events: none;
}

[data-theme="dark"] .promo-image-overlay {
    background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.4));
}

.promotion-header {
    padding: 1.25rem;
    border-radius: 16px 16px 0 0;
    background: #fff;
}

/* ── Cards with image/gradient bg: inner sections MUST be transparent
   in both light AND dark mode so the background shows through ── */
.promotion-card[data-has-bg="true"] .promotion-header {
    background: transparent !important;
    border-bottom: 1px solid rgba(255,255,255,0.15) !important;
    position: relative;
    z-index: 1;
}
.promotion-card[data-has-bg="true"] .promotion-body {
    background: transparent !important;
    position: relative;
    z-index: 1;
}
.promotion-card[data-has-bg="true"] .promotion-footer {
    background: rgba(0,0,0,0.35) !important;
    border-top: 1px solid rgba(255,255,255,0.1) !important;
    position: relative;
    z-index: 1;
}
.promotion-card[data-has-bg="true"] .inclusion-item {
    border-bottom: 1px dashed rgba(255,255,255,0.2) !important;
    color: rgba(255,255,255,0.9) !important;
}

.badge-promotion {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.price-tag {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
}

.price-unit {
    font-size: 0.75rem;
    opacity: 0.8;
    margin-top: 2px;
}

.promotion-body {
    padding: 0 1.25rem;
    flex: 1;
    background: #fff;
}

.inclusion-item {
    display: flex;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px dashed #e5e7eb;
    font-size: 0.875rem;
}

.inclusion-item:last-child { border-bottom: none; }

.inclusion-icon {
    width: 16px;
    height: 16px;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.promotion-footer {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
    margin-top: auto;
}

/* ── Dark mode: PLAIN cards only (no banner image / gradient) ─────────────
   The key fix: we use data-has-bg="true" on cards that have a background image
   or gradient. Dark mode styles only apply when data-has-bg is absent/false.
   This completely avoids the fragile :not([style*="..."]) string-matching hack.
   ── */
[data-theme="dark"] .promotion-card[data-has-bg="false"] {
    background: #1e293b !important;
    border-color: #334155 !important;
}

[data-theme="dark"] .promotion-card[data-has-bg="false"] .promotion-header {
    background: #1e293b !important;
    color: #f1f5f9 !important;
}

[data-theme="dark"] .promotion-card[data-has-bg="false"] .promotion-body {
    background: #1e293b !important;
    color: #f1f5f9 !important;
}

[data-theme="dark"] .promotion-card[data-has-bg="false"] .promotion-footer {
    background: #0f172a !important;
    border-color: #334155 !important;
    color: #f1f5f9 !important;
}

[data-theme="dark"] .promotion-card[data-has-bg="false"] .inclusion-item {
    border-color: #334155 !important;
    color: #f1f5f9 !important;
}

/* Responsive */
@media (max-width: 768px) {
    .promotion-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 1rem;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Promotions & Discounts</h4>
            <p class="text-muted small mb-0">View and manage active promotions for laundry services</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="showInactivePromotions" checked>
                <label class="form-check-label small fw-semibold" for="showInactivePromotions">Show Inactive</label>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="background-color: var(--card-bg) !important;">
                <div class="card-body p-4" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-megaphone text-primary fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 small" style="color: var(--text-secondary) !important;">Total Promotions</h6>
                            <h3 class="fw-bold mb-0" style="color: var(--text-primary) !important;">{{ $promotions->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="background-color: var(--card-bg) !important;">
                <div class="card-body p-4" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-tag text-success fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 small" style="color: var(--text-secondary) !important;">Active Promotions</h6>
                            <h3 class="fw-bold mb-0" style="color: var(--text-primary) !important;">{{ $promotions->where('is_active', true)->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="background-color: var(--card-bg) !important;">
                <div class="card-body p-4" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-percent text-info fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 small" style="color: var(--text-secondary) !important;">Percentage Off</h6>
                            <h3 class="fw-bold mb-0" style="color: var(--text-primary) !important;">{{ $promotions->where('discount_type', 'percentage')->where('is_active', true)->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="background-color: var(--card-bg) !important;">
                <div class="card-body p-4" style="background-color: var(--card-bg) !important; color: var(--text-primary) !important;">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-coin text-warning fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 small" style="color: var(--text-secondary) !important;">Fixed Amount</h6>
                            <h3 class="fw-bold mb-0" style="color: var(--text-primary) !important;">{{ $promotions->where('discount_type', 'fixed')->where('is_active', true)->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Promotions Cards --}}
    <div class="card border-0 shadow-sm" style="background-color: var(--card-bg) !important;">
        <div class="card-header border-bottom" style="background-color: var(--border-color) !important; color: var(--text-primary) !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-1" style="color: var(--text-primary) !important;">
                        <i class="bi bi-megaphone me-2 text-primary"></i>All Promotions
                    </h6>
                    <p class="text-muted small mb-0" style="color: var(--text-secondary) !important;">List of all promotions created by admin</p>
                </div>
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text bg-light border-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control bg-light border-0" id="searchPromotions" placeholder="Search promotions...">
                </div>
            </div>
        </div>
        <div class="card-body p-0" style="background-color: var(--card-bg) !important;">
            @if($promotions->count() > 0)
            <div class="promotion-grid mb-5" id="promotionCards" style="background-color: transparent !important;">
                @foreach($promotions as $promo)
                @php
                    $isPosterPromo = $promo->type === 'poster_promo' && $promo->display_price;

                    $colorGradient = match($promo->color_theme) {
                        'blue'   => 'linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%)',
                        'purple' => 'linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%)',
                        'green'  => 'linear-gradient(135deg, #10B981 0%, #059669 100%)',
                        default  => 'linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%)',
                    };

                    // Does this card have a visual background (image or gradient)?
                    $hasBg = $promo->banner_image || $isPosterPromo;

                    $status = 'Active';
                    if (!$promo->is_active) {
                        $status = 'Inactive';
                    } elseif (now() < $promo->start_date) {
                        $status = 'Scheduled';
                    } elseif (now() > $promo->end_date) {
                        $status = 'Expired';
                    } elseif ($promo->max_usage && $promo->usage_count >= $promo->max_usage) {
                        $status = 'Maxed Out';
                    }

                    $statusColor = match($status) {
                        'Active'    => '#10B981',
                        'Scheduled' => '#0EA5E9',
                        'Expired'   => '#6B7280',
                        'Maxed Out' => '#F59E0B',
                        'Inactive'  => '#EF4444',
                        default     => '#6B7280',
                    };
                @endphp

                <div class="promotion-row" data-active="{{ $promo->is_active ? '1' : '0' }}" data-id="{{ $promo->id }}">
                    {{-- ↓ data-has-bg is the key fix: CSS dark-mode styles key off this attribute --}}
                    <div class="card promotion-card border-0 shadow-sm h-100 {{ !$promo->is_active ? 'opacity-75' : '' }}"
                         data-has-bg="{{ $hasBg ? 'true' : 'false' }}"
                         @if($promo->banner_image)
                             style="background: url('{{ asset('storage/' . $promo->banner_image) }}') center/cover;"
                         @elseif($isPosterPromo)
                             style="background: {{ $colorGradient }};"
                         @endif>

                        @if($promo->banner_image)
                            <div class="promo-image-overlay"></div>
                        @endif

                        {{-- Header --}}
                        <div class="promotion-header" style="@if(!$hasBg) border-left: 5px solid {{ $statusColor }}; @endif">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge-promotion"
                                          style="background: {{ $hasBg ? 'rgba(255,255,255,0.2)' : $statusColor.'20' }}; color: {{ $hasBg ? 'white' : $statusColor }};">
                                        @if($isPosterPromo)
                                            <i class="bi bi-image me-1"></i>Poster
                                        @elseif($promo->discount_type === 'percentage')
                                            <i class="bi bi-percent me-1"></i>Discount
                                        @else
                                            <i class="bi bi-tag me-1"></i>Promotion
                                        @endif
                                    </span>
                                    <h5 class="fw-bold mt-2 mb-1" @if($hasBg) style="color: white;" @endif>
                                        {{ $promo->name }}
                                    </h5>
                                    @if($promo->promo_code)
                                        <small @if($hasBg) style="color: rgba(255,255,255,0.8);" @else class="text-muted" @endif>
                                            Code: {{ $promo->promo_code }}
                                        </small>
                                    @endif
                                    @if($promo->featured)
                                        <span class="ms-2" style="color: #FFC107;"><i class="bi bi-star-fill"></i></span>
                                    @endif
                                </div>
                                <div class="text-end">
                                    @if($isPosterPromo)
                                        <div class="price-tag" style="color: {{ $hasBg ? 'white' : $statusColor }};">
                                            ₱{{ number_format($promo->display_price, 0) }}
                                        </div>
                                        <div class="price-unit" @if($hasBg) style="color: rgba(255,255,255,0.8);" @endif>
                                            {{ $promo->price_unit }}
                                        </div>
                                    @else
                                        <div class="price-tag" style="color: {{ $hasBg ? 'white' : $statusColor }};">
                                            @if($promo->discount_type === 'percentage')
                                                {{ $promo->discount_value }}% OFF
                                            @elseif($promo->discount_type === 'fixed')
                                                ₱{{ number_format($promo->discount_value, 0) }} OFF
                                            @else
                                                Special Offer
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="promotion-body">

                            @if($promo->branch)
                                <div class="inclusion-item"
>
                                    <i class="bi bi-geo-alt-fill inclusion-icon" style="color: #0EA5E9;"></i>
                                    <span>{{ $promo->branch->name }} only</span>
                                </div>
                            @else
                                <div class="inclusion-item"
>
                                    <i class="bi bi-globe inclusion-icon" style="color: #10B981;"></i>
                                    <span>All branches</span>
                                </div>
                            @endif

                            <div class="inclusion-item"
                                 @if($hasBg) style="border-bottom: 1px dashed rgba(255,255,255,0.2); color: rgba(255,255,255,0.9);" @endif>
                                <i class="bi bi-calendar-event inclusion-icon" style="color: #F59E0B;"></i>
                                <span>{{ $promo->start_date->format('M d') }} - {{ $promo->end_date->format('M d, Y') }}</span>
                            </div>

                            @if($promo->min_amount > 0)
                                <div class="inclusion-item"
>
                                    <i class="bi bi-cash inclusion-icon" style="color: #8B5CF6;"></i>
                                    <span>Min ₱{{ number_format($promo->min_amount, 0) }}</span>
                                </div>
                            @endif

                            @if($promo->description)
                                <div class="inclusion-item"
>
                                    <i class="bi bi-info-circle-fill inclusion-icon" style="color: {{ $hasBg ? '#0EA5E9' : '#0d6efd' }};"></i>
                                    <span>{{ Str::limit($promo->description, 60) }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Footer --}}
                        <div class="promotion-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge {{ $status === 'Active' ? 'bg-success' : ($status === 'Scheduled' ? 'bg-info' : ($status === 'Expired' ? 'bg-secondary' : ($status === 'Maxed Out' ? 'bg-warning' : 'bg-danger'))) }}" style="font-size:11px;">
                                        {{ $status }}
                                    </span>
                                    <span class="badge bg-light text-muted" style="font-size:11px;">
                                        <i class="bi bi-graph-up me-1"></i>{{ $promo->usage_count ?? 0 }}/{{ $promo->max_usage ?? '∞' }}
                                    </span>
                                </div>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-info py-1 px-2 view-promotion"
                                            title="View Details"
                                            data-bs-toggle="modal" data-bs-target="#viewPromotionModal"
                                            data-promotion-id="{{ $promo->id }}"
                                            data-promotion-name="{{ $promo->name }}"
                                            data-promotion-code="{{ $promo->promo_code ?? 'N/A' }}"
                                            data-promotion-description="{{ $promo->description }}"
                                            data-promotion-discount-type="{{ $promo->discount_type }}"
                                            data-promotion-discount-value="{{ $promo->discount_value }}"
                                            data-promotion-application-type="{{ $promo->application_type }}"
                                            data-promotion-display-price="{{ $promo->display_price }}"
                                            data-promotion-start-date="{{ $promo->start_date ? $promo->start_date->format('F d, Y') : 'Not set' }}"
                                            data-promotion-end-date="{{ $promo->end_date ? $promo->end_date->format('F d, Y') : 'Not set' }}"
                                            data-promotion-is-active="{{ $promo->is_active ? '1' : '0' }}"
                                            data-promotion-created="{{ $promo->created_at->format('F d, Y') }}"
                                            data-promotion-usage="{{ $promo->usage_count ?? 0 }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('branch.promotions.show', $promo) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="View Details">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-5">
                <div class="py-4">
                    <i class="bi bi-megaphone display-1 text-muted opacity-25"></i>
                    <h5 class="text-muted mb-2 mt-3">No promotions yet</h5>
                    <p class="text-muted mb-4">Admin hasn't created any promotions yet. Check back later!</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- View Promotion Modal --}}
<div class="modal fade" id="viewPromotionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-eye me-2"></i>Promotion Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="rounded-3 mx-auto d-flex align-items-center justify-content-center mb-3"
                        style="width:100px;height:100px;background:linear-gradient(135deg,#0d6efd 0%,#0a58ca 100%);">
                        <i class="bi bi-megaphone text-white" style="font-size:3rem;"></i>
                    </div>
                    <span id="viewPromotionStatus" class="badge mb-2" style="font-size:0.9rem;padding:0.5rem 1rem;background:#198754;">
                        <i class="bi bi-check-circle me-1"></i>
                        <span id="viewPromotionStatusText">ACTIVE</span>
                    </span>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Promotion Name</small>
                            <h5 id="viewPromotionName" class="fw-bold mb-0">-</h5>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Promotion Code</small>
                            <code id="viewPromotionCode" class="fw-semibold">-</code>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Description</small>
                            <p id="viewPromotionDescription" class="mb-0">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Discount Type</small>
                            <div id="viewPromotionDiscountType" class="mb-0">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Discount Value</small>
                            <strong id="viewPromotionDiscountValue" class="d-block fs-5">-</strong>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Application Type</small>
                            <div id="viewPromotionApplicationType" class="mb-0">-</div>
                            <div id="viewPromotionDisplayPrice" class="small text-muted mt-1" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Valid From</small>
                            <strong id="viewPromotionStartDate" class="d-block">-</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Valid Until</small>
                            <strong id="viewPromotionEndDate" class="d-block">-</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Usage Count</small>
                            <strong id="viewPromotionUsage" class="d-block fs-5">-</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Created</small>
                            <strong id="viewPromotionCreated" class="d-block">-</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Fix: Re-apply inline backgrounds on theme switch ────
    // The layout's [data-theme="dark"] .card rule uses !important which
    // can override inline style backgrounds. Setting it via JS style property
    // always wins because JS inline styles have the highest specificity.
    function reapplyCardBackgrounds() {
        document.querySelectorAll('.promotion-card[data-has-bg="true"]').forEach(function(card) {
            const bg = card.getAttribute('data-bg-value');
            if (bg) {
                card.style.setProperty('background', bg, 'important');
            }
        });
    }

    // Store the original background value in a data attribute so we can restore it
    document.querySelectorAll('.promotion-card[data-has-bg="true"]').forEach(function(card) {
        // Read the inline style background that was set by Blade
        const inlineBg = card.getAttribute('style');
        if (inlineBg) {
            // Extract the background value from style="background: ...;"
            const match = inlineBg.match(/background:\s*([^;]+)/);
            if (match) {
                card.setAttribute('data-bg-value', match[1].trim());
            }
        }
    });

    // Apply immediately on load
    reapplyCardBackgrounds();

    // Re-apply whenever theme changes (observe the html element's data-theme attribute)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'data-theme') {
                reapplyCardBackgrounds();
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });

    // ── Search ──────────────────────────────────────────────
    const searchInput = document.getElementById('searchPromotions');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const val = this.value.toLowerCase();
            document.querySelectorAll('.promotion-row').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
            });
        });
    }

    // ── Show/hide inactive ──────────────────────────────────
    const showInactive = document.getElementById('showInactivePromotions');
    if (showInactive) {
        showInactive.addEventListener('change', function () {
            document.querySelectorAll('.promotion-row').forEach(row => {
                row.style.display = (!this.checked && row.dataset.active === '0') ? 'none' : '';
            });
        });
        showInactive.dispatchEvent(new Event('change'));
    }

    // ── View promotion modal ────────────────────────────────
    const viewModal = document.getElementById('viewPromotionModal');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (event) {
            const b = event.relatedTarget;
            const discountType    = b.getAttribute('data-promotion-discount-type');
            const discountValue   = b.getAttribute('data-promotion-discount-value');
            const applicationType = b.getAttribute('data-promotion-application-type');
            const displayPrice    = b.getAttribute('data-promotion-display-price');
            const isActive        = b.getAttribute('data-promotion-is-active') === '1';
            const usage           = b.getAttribute('data-promotion-usage') || '0';

            document.getElementById('viewPromotionName').textContent        = b.getAttribute('data-promotion-name');
            document.getElementById('viewPromotionCode').textContent        = b.getAttribute('data-promotion-code');
            document.getElementById('viewPromotionDescription').textContent = b.getAttribute('data-promotion-description') || 'No description provided';
            document.getElementById('viewPromotionStartDate').textContent   = b.getAttribute('data-promotion-start-date');
            document.getElementById('viewPromotionEndDate').textContent     = b.getAttribute('data-promotion-end-date');
            document.getElementById('viewPromotionUsage').textContent       = `${usage} time${usage != 1 ? 's' : ''}`;
            document.getElementById('viewPromotionCreated').textContent     = b.getAttribute('data-promotion-created');

            document.getElementById('viewPromotionDiscountType').innerHTML =
                `<span class="badge bg-${discountType === 'percentage' ? 'info' : 'warning'}">${discountType.charAt(0).toUpperCase() + discountType.slice(1)}</span>`;

            const dvEl = document.getElementById('viewPromotionDiscountValue');
            dvEl.textContent = discountType === 'percentage'
                ? `${discountValue}% OFF`
                : `₱${parseFloat(discountValue).toFixed(2)} OFF`;
            dvEl.className = `d-block fs-5 text-${discountType === 'percentage' ? 'info' : 'warning'}`;

            const appEl   = document.getElementById('viewPromotionApplicationType');
            const priceEl = document.getElementById('viewPromotionDisplayPrice');
            if (applicationType === 'per_load_override') {
                appEl.innerHTML       = '<span class="badge bg-primary">Per Load Override</span>';
                priceEl.style.display = 'block';
                priceEl.textContent   = `Display Price: ₱${parseFloat(displayPrice).toFixed(2)}/load`;
            } else if (applicationType === 'all_services') {
                appEl.innerHTML       = '<span class="badge bg-success">All Services</span>';
                priceEl.style.display = 'none';
            } else {
                appEl.innerHTML       = '<span class="badge bg-info">Specific Services</span>';
                priceEl.style.display = 'none';
            }

            const statusEl   = document.getElementById('viewPromotionStatus');
            const statusText = document.getElementById('viewPromotionStatusText');
            statusEl.style.background = isActive ? '#198754' : '#6c757d';
            statusText.textContent    = isActive ? 'ACTIVE' : 'INACTIVE';
        });
    }
});
</script>
@endpush
@endsection
