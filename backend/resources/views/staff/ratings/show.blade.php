@extends('staff.layouts.staff')

@section('title', 'Rating Details')
@section('page-title', 'RATING DETAILS')

@push('styles')
<style>
/* ============================================================
   WASHBOX DESIGN SYSTEM — Rating Details
   Matches layout font, colors, card style, badge patterns.
   Full light / dark mode via CSS custom properties on [data-theme].
   ============================================================ */

:root {
    --wb-bg:              #f4f6f9;
    --wb-surface:         #ffffff;
    --wb-surface-2:       #f8f9fb;
    --wb-surface-3:       #f0f3f6;
    --wb-border:          #e8ecf0;
    --wb-border-subtle:   #f0f3f6;

    --wb-text-primary:    #1a2332;
    --wb-text-secondary:  #5a6a7e;
    --wb-text-muted:      #8fa0b4;

    --wb-blue:            #2563eb;
    --wb-blue-hover:      #1d4ed8;
    --wb-blue-light:      #eff6ff;
    --wb-blue-text:       #1d4ed8;

    --wb-navy:            #1e293b;
    --wb-navy-hover:      #0f172a;

    --wb-gold:            #f59e0b;
    --wb-gold-empty:      #d1d9e2;

    --wb-green:           #16a34a;
    --wb-green-bg:        #dcfce7;
    --wb-slate-bg:        #f1f5f9;
    --wb-slate-text:      #64748b;

    --wb-shadow-sm:  0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    --wb-shadow-md:  0 4px 16px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04);

    --wb-radius:    12px;
    --wb-radius-sm: 8px;
    --wb-radius-xs: 6px;
    --wb-t: 0.18s ease;
}

[data-theme="dark"] {
    --wb-bg:              #0f1117;
    --wb-surface:         #1a1d27;
    --wb-surface-2:       #1f2330;
    --wb-surface-3:       #242837;
    --wb-border:          #2a2f3e;
    --wb-border-subtle:   #242837;

    --wb-text-primary:    #e8edf5;
    --wb-text-secondary:  #8896aa;
    --wb-text-muted:      #555f72;

    --wb-blue:            #3b82f6;
    --wb-blue-hover:      #60a5fa;
    --wb-blue-light:      #172036;
    --wb-blue-text:       #60a5fa;

    --wb-navy:            #334155;
    --wb-navy-hover:      #475569;

    --wb-gold:            #fbbf24;
    --wb-gold-empty:      #2a2f3e;

    --wb-green:           #4ade80;
    --wb-green-bg:        #052e16;
    --wb-slate-bg:        #1e293b;
    --wb-slate-text:      #94a3b8;

    --wb-shadow-sm:  0 1px 3px rgba(0,0,0,0.30);
    --wb-shadow-md:  0 4px 16px rgba(0,0,0,0.40);
}

/* ---- PAGE ------------------------------------------------- */
.wbd-page { padding: 0 0 3rem; }

/* ---- BREADCRUMB BAR (Back button + title row) ------------- */
.wbd-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.wbd-back {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    height: 36px;
    padding: 0 1rem;
    border-radius: var(--wb-radius-sm);
    font-size: 0.85rem;
    font-weight: 600;
    font-family: inherit;
    background: var(--wb-surface);
    border: 1px solid var(--wb-border);
    color: var(--wb-text-secondary);
    text-decoration: none;
    cursor: pointer;
    transition: background var(--wb-t), color var(--wb-t),
                border-color var(--wb-t), box-shadow var(--wb-t);
}

.wbd-back:hover {
    background: var(--wb-surface-2);
    color: var(--wb-text-primary);
    border-color: var(--wb-text-muted);
    text-decoration: none;
}

/* ---- HERO CARD ------------------------------------------- */
.wbd-hero {
    background: var(--wb-surface);
    border: 1px solid var(--wb-border);
    border-radius: var(--wb-radius);
    box-shadow: var(--wb-shadow-md);
    padding: 2rem;
    margin-bottom: 1.5rem;
    transition: background var(--wb-t), border-color var(--wb-t);
}

.wbd-hero-inner {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

/* Avatar — large version matching existing avatar style */
.wbd-avatar-lg {
    width: 64px;
    height: 64px;
    border-radius: 14px;
    background: var(--wb-navy);
    color: #fff;
    font-size: 1.6rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background var(--wb-t);
}

[data-theme="dark"] .wbd-avatar-lg { background: #334155; }

.wbd-hero-info { flex: 1; min-width: 0; }

.wbd-hero-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--wb-text-primary);
    margin: 0 0 0.2rem;
    transition: color var(--wb-t);
}

.wbd-hero-email {
    font-size: 0.875rem;
    color: var(--wb-text-secondary);
    margin: 0 0 0.1rem;
    transition: color var(--wb-t);
}

.wbd-hero-phone {
    font-size: 0.82rem;
    color: var(--wb-text-muted);
    margin: 0;
    transition: color var(--wb-t);
}

/* Score block on the right */
.wbd-hero-score {
    flex-shrink: 0;
    text-align: center;
    background: var(--wb-surface-2);
    border: 1px solid var(--wb-border);
    border-radius: var(--wb-radius-sm);
    padding: 1rem 1.5rem;
    min-width: 140px;
    transition: background var(--wb-t), border-color var(--wb-t);
}

.wbd-score-num {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--wb-text-primary);
    line-height: 1;
    margin-bottom: 0.35rem;
    transition: color var(--wb-t);
}

.wbd-score-num span {
    font-size: 1rem;
    font-weight: 500;
    color: var(--wb-text-muted);
}

.wbd-hero-stars {
    display: flex;
    justify-content: center;
    gap: 3px;
    margin-bottom: 0.35rem;
}

.wbd-hero-stars i {
    font-size: 1rem;
    color: var(--wb-gold);
    transition: color var(--wb-t);
}

.wbd-hero-stars i.e { color: var(--wb-gold-empty); }

/* Star badge pill */
.wbd-sbadge {
    display: inline-flex;
    align-items: center;
    gap: 0.28rem;
    padding: 0.22rem 0.65rem;
    border-radius: 99px;
    font-size: 0.72rem;
    font-weight: 600;
    transition: background var(--wb-t), color var(--wb-t);
}

.wbd-sbadge.s5 { background:#dcfce7; color:#16a34a; }
.wbd-sbadge.s4 { background:#dbeafe; color:#2563eb; }
.wbd-sbadge.s3 { background:#fef9c3; color:#b45309; }
.wbd-sbadge.s2 { background:#ffedd5; color:#ea580c; }
.wbd-sbadge.s1 { background:#fee2e2; color:#dc2626; }

[data-theme="dark"] .wbd-sbadge.s5 { background:#052e16; color:#4ade80; }
[data-theme="dark"] .wbd-sbadge.s4 { background:#1e3a5f; color:#60a5fa; }
[data-theme="dark"] .wbd-sbadge.s3 { background:#292204; color:#fcd34d; }
[data-theme="dark"] .wbd-sbadge.s2 { background:#2c1204; color:#fb923c; }
[data-theme="dark"] .wbd-sbadge.s1 { background:#2d0a0a; color:#f87171; }

/* ---- GRID LAYOUT ----------------------------------------- */
.wbd-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 1.5rem;
    align-items: start;
}

/* ---- SECTION CARD ---------------------------------------- */
.wbd-card {
    background: var(--wb-surface);
    border: 1px solid var(--wb-border);
    border-radius: var(--wb-radius);
    box-shadow: var(--wb-shadow-sm);
    overflow: hidden;
    margin-bottom: 1.5rem;
    transition: background var(--wb-t), border-color var(--wb-t);
}

.wbd-card:last-child { margin-bottom: 0; }

.wbd-card-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--wb-border);
    background: var(--wb-surface-2);
    transition: background var(--wb-t), border-color var(--wb-t);
}

.wbd-card-header-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--wb-text-primary);
    margin: 0;
    transition: color var(--wb-t);
}

.wbd-card-header i {
    color: var(--wb-text-muted);
    font-size: 0.9rem;
    transition: color var(--wb-t);
}

.wbd-card-body { padding: 1.5rem; }

/* ---- COMMENT BLOCK --------------------------------------- */
.wbd-comment-block {
    border-left: 3px solid var(--wb-blue);
    background: var(--wb-blue-light);
    border-radius: 0 var(--wb-radius-sm) var(--wb-radius-sm) 0;
    padding: 1.25rem 1.5rem;
    position: relative;
    transition: background var(--wb-t), border-color var(--wb-t);
}

.wbd-comment-quote {
    position: absolute;
    top: 0.5rem;
    right: 1.25rem;
    font-size: 3rem;
    line-height: 1;
    color: var(--wb-border);
    font-family: Georgia, serif;
    font-style: italic;
    user-select: none;
    pointer-events: none;
    transition: color var(--wb-t);
}

.wbd-comment-text {
    font-size: 0.9rem;
    line-height: 1.75;
    color: var(--wb-text-secondary);
    font-style: italic;
    margin: 0;
    transition: color var(--wb-t);
}

/* ---- INFO ROWS (Branch / Date) --------------------------- */
.wbd-info-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.wbd-info-item {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    background: var(--wb-surface-2);
    border: 1px solid var(--wb-border);
    border-radius: var(--wb-radius-sm);
    padding: 1rem 1.25rem;
    transition: background var(--wb-t), border-color var(--wb-t);
}

.wbd-info-icon {
    width: 38px;
    height: 38px;
    border-radius: var(--wb-radius-xs);
    background: var(--wb-navy);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
    transition: background var(--wb-t);
}

[data-theme="dark"] .wbd-info-icon { background: #334155; }

.wbd-info-label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: var(--wb-text-muted);
    margin: 0 0 0.2rem;
    transition: color var(--wb-t);
}

.wbd-info-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--wb-text-primary);
    margin: 0;
    transition: color var(--wb-t);
}

/* ---- CUSTOMER STAT TILES --------------------------------- */
.wbd-stat-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.875rem;
}

.wbd-stat-tile {
    background: var(--wb-surface-2);
    border: 1px solid var(--wb-border);
    border-radius: var(--wb-radius-sm);
    padding: 1rem;
    text-align: center;
    transition: background var(--wb-t), border-color var(--wb-t);
}

.wbd-stat-value {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--wb-text-primary);
    line-height: 1;
    margin-bottom: 0.3rem;
    transition: color var(--wb-t);
}

.wbd-stat-label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: var(--wb-text-muted);
    transition: color var(--wb-t);
}

/* Member since row — spans full width */
.wbd-member-row {
    grid-column: span 2;
    background: var(--wb-surface-2);
    border: 1px solid var(--wb-border);
    border-radius: var(--wb-radius-sm);
    padding: 0.875rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: background var(--wb-t), border-color var(--wb-t);
}

.wbd-member-label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: var(--wb-text-muted);
    margin: 0 0 0.2rem;
    transition: color var(--wb-t);
}

.wbd-member-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--wb-text-primary);
    margin: 0;
    transition: color var(--wb-t);
}

/* Status badge — matches "Active" badge in customer list */
.wbd-status {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.65rem;
    border-radius: 99px;
    font-size: 0.72rem;
    font-weight: 600;
    transition: background var(--wb-t), color var(--wb-t);
}

.wbd-status.active {
    background: var(--wb-green-bg);
    color: var(--wb-green);
}

.wbd-status.inactive {
    background: var(--wb-slate-bg);
    color: var(--wb-slate-text);
}

/* ---- BRANCH SUMMARY -------------------------------------- */
.wbd-branch-score {
    text-align: center;
    padding: 0.5rem 0;
}

.wbd-branch-num {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--wb-text-primary);
    line-height: 1;
    margin-bottom: 0.5rem;
    transition: color var(--wb-t);
}

.wbd-branch-stars {
    display: flex;
    justify-content: center;
    gap: 3px;
    margin-bottom: 0.5rem;
}

.wbd-branch-stars i {
    font-size: 1rem;
    color: var(--wb-gold);
    transition: color var(--wb-t);
}

.wbd-branch-stars i.e { color: var(--wb-gold-empty); }

.wbd-branch-sub {
    font-size: 0.8rem;
    color: var(--wb-text-muted);
    margin: 0;
    transition: color var(--wb-t);
}

/* Progress bars for star breakdown */
.wbd-bar-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.5rem;
}

.wbd-bar-label {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--wb-text-muted);
    width: 30px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 2px;
    transition: color var(--wb-t);
}

.wbd-bar-track {
    flex: 1;
    height: 6px;
    background: var(--wb-border);
    border-radius: 99px;
    overflow: hidden;
    transition: background var(--wb-t);
}

.wbd-bar-fill {
    height: 100%;
    border-radius: 99px;
    transition: width 0.6s cubic-bezier(0.4,0,0.2,1), background var(--wb-t);
}

.wbd-bar-fill.s5 { background: #16a34a; }
.wbd-bar-fill.s4 { background: #2563eb; }
.wbd-bar-fill.s3 { background: #b45309; }
.wbd-bar-fill.s2 { background: #ea580c; }
.wbd-bar-fill.s1 { background: #dc2626; }

[data-theme="dark"] .wbd-bar-fill.s5 { background: #4ade80; }
[data-theme="dark"] .wbd-bar-fill.s4 { background: #60a5fa; }
[data-theme="dark"] .wbd-bar-fill.s3 { background: #fcd34d; }
[data-theme="dark"] .wbd-bar-fill.s2 { background: #fb923c; }
[data-theme="dark"] .wbd-bar-fill.s1 { background: #f87171; }

.wbd-bar-count {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--wb-text-muted);
    width: 24px;
    text-align: right;
    flex-shrink: 0;
    transition: color var(--wb-t);
}

.wbd-divider {
    border: none;
    border-top: 1px solid var(--wb-border);
    margin: 1.25rem 0;
    transition: border-color var(--wb-t);
}

/* ---- RESPONSIVE ------------------------------------------ */
@media (max-width: 900px) {
    .wbd-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .wbd-hero-inner { gap: 1rem; }
    .wbd-score-num { font-size: 2rem; }
    .wbd-info-row { grid-template-columns: 1fr; }
    .wbd-hero-score { min-width: 0; width: 100%; }
}
</style>
@endpush

@section('content')
<div class="wbd-page">

    {{-- ── TOP BAR ─────────────────────────────────────────────── --}}
    <div class="wbd-topbar">
        <a href="{{ route('staff.ratings.index') }}" class="wbd-back">
            <i class="bi bi-arrow-left"></i>
            Back to Ratings
        </a>
    </div>

    {{-- ── HERO CARD ───────────────────────────────────────────── --}}
    <div class="wbd-hero">
        <div class="wbd-hero-inner">

            {{-- Avatar --}}
            <div class="wbd-avatar-lg">
                {{ strtoupper(substr($rating->customer->name, 0, 1)) }}
            </div>

            {{-- Customer info --}}
            <div class="wbd-hero-info">
                <p class="wbd-hero-name">{{ $rating->customer->name }}</p>
                <p class="wbd-hero-email">
                    <i class="bi bi-envelope me-1" style="color:var(--wb-text-muted);font-size:0.78rem;"></i>
                    {{ $rating->customer->email }}
                </p>
                @if($rating->customer->phone)
                <p class="wbd-hero-phone">
                    <i class="bi bi-telephone me-1" style="font-size:0.78rem;"></i>
                    {{ $rating->customer->phone }}
                </p>
                @endif
            </div>

            {{-- Score block --}}
            <div class="wbd-hero-score">
                <div class="wbd-score-num">
                    {{ $rating->rating }}<span>/5</span>
                </div>
                <div class="wbd-hero-stars">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star{{ $i <= $rating->rating ? '-fill' : '' }}{{ $i > $rating->rating ? ' e' : '' }}"></i>
                    @endfor
                </div>
                @php $sc = $rating->rating; @endphp
                <span class="wbd-sbadge s{{ $sc }}">
                    <i class="bi bi-star-fill" style="font-size:0.58rem;"></i>
                    {{ $sc }} Star{{ $sc > 1 ? 's' : '' }}
                </span>
            </div>

        </div>
    </div>

    {{-- ── TWO-COLUMN GRID ─────────────────────────────────────── --}}
    <div class="wbd-grid">

        {{-- LEFT COLUMN --}}
        <div>

            {{-- Comment card --}}
            @if($rating->comment)
            <div class="wbd-card">
                <div class="wbd-card-header">
                    <i class="bi bi-chat-left-quote"></i>
                    <h2 class="wbd-card-header-title">Customer Feedback</h2>
                </div>
                <div class="wbd-card-body">
                    <div class="wbd-comment-block">
                        <span class="wbd-comment-quote">"</span>
                        <p class="wbd-comment-text">{{ $rating->comment }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Rating information --}}
            <div class="wbd-card">
                <div class="wbd-card-header">
                    <i class="bi bi-info-circle"></i>
                    <h2 class="wbd-card-header-title">Rating Information</h2>
                </div>
                <div class="wbd-card-body">
                    <div class="wbd-info-row">

                        <div class="wbd-info-item">
                            <div class="wbd-info-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div>
                                <p class="wbd-info-label">Branch Location</p>
                                <p class="wbd-info-value">{{ $rating->branch->name }}</p>
                            </div>
                        </div>

                        <div class="wbd-info-item">
                            <div class="wbd-info-icon">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <div>
                                <p class="wbd-info-label">Date Submitted</p>
                                <p class="wbd-info-value">{{ $rating->created_at->format('M j, Y') }}</p>
                                <p style="font-size:0.75rem;color:var(--wb-text-muted);margin:0;transition:color var(--wb-t);">
                                    {{ $rating->created_at->format('g:i A') }}
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN --}}
        <div>

            {{-- Customer Statistics --}}
            <div class="wbd-card">
                <div class="wbd-card-header">
                    <i class="bi bi-person"></i>
                    <h2 class="wbd-card-header-title">Customer Statistics</h2>
                </div>
                <div class="wbd-card-body">
                    <div class="wbd-stat-grid">

                        <div class="wbd-stat-tile">
                            <div class="wbd-stat-value">{{ $rating->customer->laundries()->count() }}</div>
                            <div class="wbd-stat-label">Total Orders</div>
                        </div>

                        <div class="wbd-stat-tile">
                            <div class="wbd-stat-value" style="font-size:1.15rem;">
                                ₱{{ number_format($rating->customer->laundries()->sum('total_amount'), 0) }}
                            </div>
                            <div class="wbd-stat-label">Total Spent</div>
                        </div>

                        <div class="wbd-member-row">
                            <div>
                                <p class="wbd-member-label">Member Since</p>
                                <p class="wbd-member-value">{{ $rating->customer->created_at->format('M Y') }}</p>
                            </div>
                            <span class="wbd-status {{ $rating->customer->is_active ? 'active' : 'inactive' }}">
                                <i class="bi bi-circle-fill" style="font-size:0.45rem;"></i>
                                {{ $rating->customer->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Branch Rating Summary --}}
            @php
                $branchTotal = \App\Models\CustomerRating::where('branch_id', $rating->branch_id)->count();
                $branchAvg   = \App\Models\CustomerRating::where('branch_id', $rating->branch_id)->avg('rating') ?? 0;
                $starCounts  = [];
                for ($s = 5; $s >= 1; $s--) {
                    $starCounts[$s] = \App\Models\CustomerRating::where('branch_id', $rating->branch_id)
                                        ->where('rating', $s)->count();
                }
            @endphp

            <div class="wbd-card">
                <div class="wbd-card-header">
                    <i class="bi bi-bar-chart"></i>
                    <h2 class="wbd-card-header-title">Branch Rating Summary</h2>
                </div>
                <div class="wbd-card-body">

                    {{-- Average score --}}
                    <div class="wbd-branch-score">
                        <div class="wbd-branch-num">{{ number_format($branchAvg, 1) }}</div>
                        <div class="wbd-branch-stars">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= round($branchAvg) ? '-fill' : '' }}{{ $i > round($branchAvg) ? ' e' : '' }}"></i>
                            @endfor
                        </div>
                        <p class="wbd-branch-sub">
                            Based on {{ $branchTotal }} review{{ $branchTotal !== 1 ? 's' : '' }}
                        </p>
                    </div>

                    <hr class="wbd-divider">

                    {{-- Star distribution bars --}}
                    @for($s = 5; $s >= 1; $s--)
                    @php $pct = $branchTotal > 0 ? ($starCounts[$s] / $branchTotal) * 100 : 0; @endphp
                    <div class="wbd-bar-row">
                        <div class="wbd-bar-label">
                            {{ $s }}<i class="bi bi-star-fill" style="font-size:0.55rem;color:var(--wb-gold);"></i>
                        </div>
                        <div class="wbd-bar-track">
                            <div class="wbd-bar-fill s{{ $s }}" style="width:{{ $pct }}%;"></div>
                        </div>
                        <div class="wbd-bar-count">{{ $starCounts[$s] }}</div>
                    </div>
                    @endfor

                </div>
            </div>

        </div>
    </div>

</div>
@endsection
