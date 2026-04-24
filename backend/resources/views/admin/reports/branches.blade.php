@extends('admin.layouts.app')

@section('title', 'Branches Report')

@section('content')

<style>
/* ============================================================
   BRANCHES ANALYTICS — Precision Operations Dashboard
   Tone: Sharp, data-forward, structured authority.
   Typography: Instrument Serif (display) + Geist (body) + Geist Mono (numbers)
   Palette: Deep slate navy, teal accent, clean data whites
   ============================================================ */

:root {
    --ba-bg:          #F0F4F8;
    --ba-surface:     #FFFFFF;
    --ba-surface-2:   #F5F8FC;
    --ba-surface-3:   #EBF0F7;
    --ba-border:      #DDE3EE;
    --ba-border-2:    #C8D2E3;

    --ba-text-1:      #0D1526;
    --ba-text-2:      #3A4C6B;
    --ba-text-3:      #7A8FAD;
    --ba-text-4:      #B8C5D9;

    --ba-teal:        #0D7377;
    --ba-teal-fill:   #0F9EA5;
    --ba-teal-bright: #14B8A6;
    --ba-teal-soft:   rgba(15,158,165,0.09);
    --ba-teal-border: rgba(13,115,119,0.18);

    --ba-blue:        #1D4ED8;
    --ba-blue-fill:   #2563EB;
    --ba-blue-soft:   rgba(37,99,235,0.09);

    --ba-indigo:      #4338CA;
    --ba-indigo-soft: rgba(67,56,202,0.09);

    --ba-green:       #065F46;
    --ba-green-fill:  #059669;
    --ba-green-soft:  rgba(5,150,105,0.09);

    --ba-amber:       #B45309;
    --ba-amber-fill:  #D97706;
    --ba-amber-soft:  rgba(217,119,6,0.09);

    --ba-purple:      #6D28D9;
    --ba-purple-fill: #7C3AED;
    --ba-purple-soft: rgba(124,58,237,0.09);

    --ba-red:         #B91C1C;
    --ba-red-fill:    #DC2626;
    --ba-red-soft:    rgba(220,38,38,0.08);

    /* Rank colors */
    --rank-1: #D97706;
    --rank-2: #6B7280;
    --rank-3: #B45309;

    --ba-shadow-xs: 0 1px 2px rgba(13,21,38,0.05);
    --ba-shadow-sm: 0 2px 8px rgba(13,21,38,0.08), 0 1px 2px rgba(13,21,38,0.04);
    --ba-shadow-md: 0 6px 24px rgba(13,21,38,0.10), 0 2px 6px rgba(13,21,38,0.05);
    --ba-shadow-lg: 0 16px 48px rgba(13,21,38,0.14), 0 4px 12px rgba(13,21,38,0.06);

    --ba-radius:    20px;
    --ba-radius-sm: 14px;
    --ba-radius-xs: 9px;
    --ba-t:         0.18s ease;
}

[data-theme="dark"] {
    --ba-bg:          #111827;
    --ba-surface:     #1F2937;
    --ba-surface-2:   #374151;
    --ba-surface-3:   #4B5563;
    --ba-border:      #374151;
    --ba-border-2:    #4B5563;

    --ba-text-1:      #F9FAFB;
    --ba-text-2:      #D1D5DB;
    --ba-text-3:      #9CA3AF;
    --ba-text-4:      #6B7280;

    --ba-teal:        #2DD4BF;
    --ba-teal-fill:   #14B8A6;
    --ba-teal-bright: #5EEAD4;
    --ba-teal-soft:   rgba(20,184,166,0.10);
    --ba-teal-border: rgba(45,212,191,0.20);

    --ba-blue:        #60A5FA;
    --ba-blue-fill:   #3B82F6;
    --ba-blue-soft:   rgba(59,130,246,0.10);

    --ba-green:       #34D399;
    --ba-green-fill:  #10B981;
    --ba-green-soft:  rgba(16,185,129,0.10);

    --ba-amber:       #FCD34D;
    --ba-amber-fill:  #F59E0B;
    --ba-amber-soft:  rgba(245,158,11,0.10);

    --ba-purple:      #C4B5FD;
    --ba-purple-fill: #8B5CF6;
    --ba-purple-soft: rgba(139,92,246,0.10);

    --ba-red:         #FCA5A5;
    --ba-red-fill:    #EF4444;
    --ba-red-soft:    rgba(239,68,68,0.10);

    --rank-1: #F59E0B;
    --rank-2: #94A3B8;
    --rank-3: #D97706;

    --ba-shadow-xs: 0 1px 2px rgba(0,0,0,0.30);
    --ba-shadow-sm: 0 2px 8px rgba(0,0,0,0.40);
    --ba-shadow-md: 0 6px 24px rgba(0,0,0,0.50);
    --ba-shadow-lg: 0 16px 48px rgba(0,0,0,0.60);
}

/* ── Base ───────────────────────────────────────────────── */
.ba-page {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: var(--ba-bg);
    padding: 0 0 5rem;
    color: var(--ba-text-1);
}

/* ── Page Header ────────────────────────────────────────── */
.ba-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1.5rem;
    margin-bottom: 2.5rem;
    flex-wrap: wrap;
}

.ba-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.63rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--ba-teal-fill);
    background: var(--ba-teal-soft);
    border: 1px solid var(--ba-teal-border);
    padding: 0.26rem 0.72rem;
    border-radius: 999px;
    margin-bottom: 0.7rem;
}

.ba-page-title {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 2.2rem;
    font-weight: 400;
    color: var(--ba-text-1);
    letter-spacing: -0.03em;
    line-height: 1.15;
    margin: 0 0 0.35rem;
}

.ba-page-sub {
    font-size: 0.875rem;
    color: var(--ba-text-3);
    margin: 0;
}

.ba-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    height: 38px;
    padding: 0 1rem;
    border-radius: var(--ba-radius-xs);
    font-size: 0.8rem;
    font-weight: 600;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: transparent;
    color: var(--ba-text-2);
    border: 1.5px solid var(--ba-border);
    text-decoration: none;
    transition: all var(--ba-t);
    white-space: nowrap;
}

.ba-back-btn:hover {
    background: var(--ba-surface);
    border-color: var(--ba-border-2);
    color: var(--ba-text-1);
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: var(--ba-shadow-sm);
}

/* ── Filter Card ────────────────────────────────────────── */
.ba-filter-card {
    background: var(--ba-surface);
    border: 1px solid var(--ba-border);
    border-radius: var(--ba-radius-sm);
    padding: 1.375rem 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--ba-shadow-xs);
    display: flex;
    align-items: flex-end;
    gap: 1rem;
    flex-wrap: wrap;
}

.ba-filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    flex: 1;
    min-width: 150px;
}

.ba-filter-label {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--ba-text-3);
}

.ba-filter-input {
    height: 40px;
    background: var(--ba-surface-2);
    border: 1px solid var(--ba-border);
    border-radius: var(--ba-radius-xs);
    padding: 0 0.875rem;
    font-size: 0.85rem;
    color: var(--ba-text-1);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    outline: none;
    transition: border-color var(--ba-t), box-shadow var(--ba-t);
    width: 100%;
}

.ba-filter-input:focus {
    border-color: var(--ba-teal-fill);
    box-shadow: 0 0 0 3px var(--ba-teal-soft);
}

.ba-filter-btn {
    height: 40px;
    padding: 0 1.375rem;
    border-radius: var(--ba-radius-xs);
    font-size: 0.825rem;
    font-weight: 700;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: var(--ba-text-1);
    color: var(--ba-bg);
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: all var(--ba-t);
    white-space: nowrap;
    flex-shrink: 0;
}

.ba-filter-btn:hover {
    opacity: 0.85;
    transform: translateY(-1px);
    box-shadow: var(--ba-shadow-sm);
}

/* ── Summary Stats ──────────────────────────────────────── */
.ba-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.ba-stat {
    background: var(--ba-surface);
    border: 1px solid var(--ba-border);
    border-radius: var(--ba-radius-sm);
    padding: 1.375rem 1.25rem;
    box-shadow: var(--ba-shadow-xs);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform var(--ba-t), box-shadow var(--ba-t);
    position: relative;
    overflow: hidden;
}

.ba-stat::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    opacity: 0;
    transition: opacity var(--ba-t);
}

.ba-stat:hover { transform: translateY(-3px); box-shadow: var(--ba-shadow-md); }
.ba-stat:hover::before { opacity: 1; }

.ba-stat.s-teal::before   { background: var(--ba-teal-fill);   }
.ba-stat.s-green::before  { background: var(--ba-green-fill);  }
.ba-stat.s-blue::before   { background: var(--ba-blue-fill);   }
.ba-stat.s-amber::before  { background: var(--ba-amber-fill);  }

.ba-stat-ico {
    width: 46px; height: 46px;
    border-radius: var(--ba-radius-xs);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}

.ba-stat.s-teal  .ba-stat-ico { background: var(--ba-teal-soft);   color: var(--ba-teal-fill);   }
.ba-stat.s-green .ba-stat-ico { background: var(--ba-green-soft);  color: var(--ba-green-fill);  }
.ba-stat.s-blue  .ba-stat-ico { background: var(--ba-blue-soft);   color: var(--ba-blue-fill);   }
.ba-stat.s-amber .ba-stat-ico { background: var(--ba-amber-soft);  color: var(--ba-amber-fill);  }

.ba-stat-body {}

.ba-stat-label {
    font-size: 0.63rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--ba-text-3);
    margin-bottom: 0.25rem;
}

.ba-stat-value {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 1.65rem;
    font-weight: 700;
    color: var(--ba-text-1);
    line-height: 1;
    letter-spacing: -0.03em;
}

/* ── Main Layout: Chart + Leaderboard ───────────────────── */
.ba-main-row {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 1.25rem;
    margin-bottom: 2rem;
}

/* Chart card */
.ba-chart-card {
    background: var(--ba-surface);
    border: 1px solid var(--ba-border);
    border-radius: var(--ba-radius-sm);
    overflow: hidden;
    box-shadow: var(--ba-shadow-xs);
}

.ba-card-header {
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid var(--ba-border);
    background: var(--ba-surface-2);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.ba-card-header-ico {
    width: 32px; height: 32px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.ba-card-header h6 {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--ba-text-1);
    margin: 0 0 0.1rem;
}

.ba-card-header small { font-size: 0.72rem; color: var(--ba-text-3); }

.ba-chart-body {
    padding: 1.5rem;
}

.ba-chart-wrap {
    position: relative;
    height: 280px;
    width: 100%;
}

/* Leaderboard card */
.ba-lb-card {
    background: var(--ba-surface);
    border: 1px solid var(--ba-border);
    border-radius: var(--ba-radius-sm);
    overflow: hidden;
    box-shadow: var(--ba-shadow-xs);
    display: flex;
    flex-direction: column;
}

.ba-lb-list { padding: 0.5rem 0; flex: 1; }

.ba-lb-item {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.875rem 1.25rem;
    border-bottom: 1px solid var(--ba-border);
    transition: background var(--ba-t);
}

.ba-lb-item:last-child { border-bottom: none; }
.ba-lb-item:hover { background: var(--ba-surface-2); }

.ba-lb-rank {
    width: 32px; height: 32px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem;
    font-weight: 800;
    flex-shrink: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.ba-lb-rank.r-1 { background: rgba(217,119,6,0.12);  color: var(--rank-1); font-size: 1rem; }
.ba-lb-rank.r-2 { background: rgba(107,114,128,0.10); color: var(--rank-2); font-size: 0.95rem; }
.ba-lb-rank.r-3 { background: rgba(180,83,9,0.12);   color: var(--rank-3); font-size: 0.9rem; }
.ba-lb-rank.r-n { background: var(--ba-surface-3);   color: var(--ba-text-3); }

.ba-lb-info { flex: 1; min-width: 0; }

.ba-lb-name {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--ba-text-1);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 0.15rem;
}

.ba-lb-code {
    display: inline-block;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 0.62rem;
    font-weight: 500;
    color: var(--ba-text-3);
    background: var(--ba-surface-3);
    border: 1px solid var(--ba-border);
    padding: 0.1rem 0.4rem;
    border-radius: 4px;
}

.ba-lb-right { flex-shrink: 0; text-align: right; }

.ba-lb-revenue {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--ba-text-1);
    margin-bottom: 0.3rem;
}

.ba-lb-pct {
    font-size: 0.65rem;
    color: var(--ba-text-3);
    margin-bottom: 0.35rem;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.ba-lb-track {
    width: 80px;
    height: 4px;
    background: var(--ba-surface-3);
    border-radius: 4px;
    overflow: hidden;
    margin-left: auto;
}

.ba-lb-fill {
    height: 100%;
    border-radius: 4px;
    background: var(--ba-teal-fill);
    transition: width 0.7s cubic-bezier(0.34,1.56,0.64,1);
}

.ba-lb-fill.r-1 { background: linear-gradient(90deg, var(--ba-amber-fill), #FCD34D); }
.ba-lb-fill.r-2 { background: linear-gradient(90deg, #6B7280, #9CA3AF); }
.ba-lb-fill.r-3 { background: linear-gradient(90deg, var(--ba-amber), #D97706); }
.ba-lb-fill.r-n { background: var(--ba-teal-fill); }

/* ── Branch Detail Cards ────────────────────────────────── */
.ba-branches-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 1.25rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.ba-section-title {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 1.6rem;
    font-weight: 400;
    color: var(--ba-text-1);
    letter-spacing: -0.02em;
    margin: 0 0 0.2rem;
}

.ba-section-sub {
    font-size: 0.8rem;
    color: var(--ba-text-3);
    margin: 0;
}

.ba-branch-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
    gap: 1.25rem;
}

.ba-branch-card {
    background: var(--ba-surface);
    border: 1px solid var(--ba-border);
    border-radius: var(--ba-radius-sm);
    padding: 1.5rem;
    box-shadow: var(--ba-shadow-xs);
    transition: transform var(--ba-t), box-shadow var(--ba-t), border-color var(--ba-t);
    position: relative;
    overflow: hidden;
}

.ba-branch-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--ba-teal-fill), var(--ba-teal-bright));
}

.ba-branch-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--ba-shadow-md);
    border-color: var(--ba-border-2);
}

.ba-branch-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 1.25rem;
}

.ba-branch-name {
    font-size: 1rem;
    font-weight: 700;
    color: var(--ba-text-1);
    margin: 0 0 0.4rem;
    line-height: 1.3;
}

.ba-branch-code-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 0.65rem;
    font-weight: 600;
    background: var(--ba-teal-soft);
    color: var(--ba-teal-fill);
    border: 1px solid var(--ba-teal-border);
}

.ba-branch-shop-ico {
    width: 42px; height: 42px;
    border-radius: var(--ba-radius-xs);
    background: var(--ba-surface-3);
    border: 1px solid var(--ba-border);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    color: var(--ba-text-3);
    flex-shrink: 0;
}

/* Metric rows */
.ba-metric-row {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--ba-border);
}

.ba-metric-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

.ba-metric-label {
    font-size: 0.63rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--ba-text-3);
    margin-bottom: 0.2rem;
}

.ba-metric-value {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--ba-text-1);
    line-height: 1;
    letter-spacing: -0.02em;
    margin-bottom: 0.2rem;
}

.ba-metric-value.green { color: var(--ba-green-fill); }
.ba-metric-value.teal  { color: var(--ba-teal-fill);  }

.ba-metric-sub {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.68rem;
    font-weight: 600;
    padding: 0.18rem 0.5rem;
    border-radius: 999px;
    background: var(--ba-green-soft);
    color: var(--ba-green-fill);
    border: 1px solid rgba(5,150,105,0.15);
}

/* ── Animations ─────────────────────────────────────────── */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}

.ba-header       { animation: fadeUp 0.4s ease both; }
.ba-filter-card  { animation: fadeUp 0.4s ease 0.06s both; }
.ba-stats-grid   { animation: fadeUp 0.4s ease 0.12s both; }
.ba-main-row     { animation: fadeUp 0.4s ease 0.18s both; }
.ba-branch-grid  { animation: fadeUp 0.4s ease 0.24s both; }

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 1100px) {
    .ba-main-row { grid-template-columns: 1fr; }
    .ba-stats-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .ba-stats-grid { grid-template-columns: 1fr 1fr; }
    .ba-page-title { font-size: 1.65rem; }
    .ba-filter-card { flex-direction: column; }
    .ba-filter-group { min-width: 100%; }
    .ba-filter-btn { width: 100%; justify-content: center; }
}

@media (max-width: 480px) {
    .ba-stats-grid { grid-template-columns: 1fr; }
    .ba-branch-grid { grid-template-columns: 1fr; }
}
</style>

<div class="ba-page">

    {{-- ── HEADER ──────────────────────────────────────────── --}}
    <div class="ba-header">
        <div>
            <div class="ba-eyebrow">
                <i class="bi bi-shop"></i> Admin Analytics
            </div>
            <h1 class="ba-page-title">Branches Analytics</h1>
            <p class="ba-page-sub">Branch performance comparison and revenue analysis</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="ba-back-btn">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
    </div>

    {{-- ── DATE FILTER ─────────────────────────────────────── --}}
    <form method="GET">
        <div class="ba-filter-card">
            <div class="ba-filter-group">
                <label class="ba-filter-label">Start Date</label>
                <input type="date" name="start_date" class="ba-filter-input" value="{{ $startDate->format('Y-m-d') }}">
            </div>
            <div class="ba-filter-group">
                <label class="ba-filter-label">End Date</label>
                <input type="date" name="end_date" class="ba-filter-input" value="{{ $endDate->format('Y-m-d') }}">
            </div>
            <button type="submit" class="ba-filter-btn">
                <i class="bi bi-funnel"></i> Apply Filter
            </button>
        </div>
    </form>

    {{-- ── SUMMARY STATS ────────────────────────────────────── --}}
    <div class="ba-stats-grid">
        <div class="ba-stat s-teal">
            <div class="ba-stat-ico"><i class="bi bi-shop-window"></i></div>
            <div class="ba-stat-body">
                <div class="ba-stat-label">Total Branches</div>
                <div class="ba-stat-value">{{ $branches->count() }}</div>
            </div>
        </div>

        <div class="ba-stat s-green">
            <div class="ba-stat-ico"><i class="bi bi-cash-coin"></i></div>
            <div class="ba-stat-body">
                <div class="ba-stat-label">Combined Revenue</div>
                <div class="ba-stat-value" style="font-size:1.35rem;">₱{{ number_format($totalRevenue, 0) }}</div>
            </div>
        </div>

        <div class="ba-stat s-blue">
            <div class="ba-stat-ico"><i class="bi bi-basket2"></i></div>
            <div class="ba-stat-body">
                <div class="ba-stat-label">Total Laundries</div>
                <div class="ba-stat-value">{{ number_format($totalLaundries) }}</div>
            </div>
        </div>

        <div class="ba-stat s-amber">
            <div class="ba-stat-ico"><i class="bi bi-receipt-cutoff"></i></div>
            <div class="ba-stat-body">
                <div class="ba-stat-label">Avg Order Value</div>
                <div class="ba-stat-value" style="font-size:1.35rem;">₱{{ $totalLaundries > 0 ? number_format($totalRevenue / $totalLaundries, 0) : '0' }}</div>
            </div>
        </div>
    </div>

    {{-- ── CHART + LEADERBOARD ──────────────────────────────── --}}
    <div class="ba-main-row">

        {{-- Revenue Chart --}}
        <div class="ba-chart-card">
            <div class="ba-card-header">
                <div class="ba-card-header-ico" style="background:var(--ba-teal-soft);color:var(--ba-teal-fill);">
                    <i class="bi bi-bar-chart-line-fill"></i>
                </div>
                <div>
                    <h6>Branch Revenue Comparison</h6>
                    <small>All branches — completed revenue this period</small>
                </div>
            </div>
            <div class="ba-chart-body">
                <div class="ba-chart-wrap">
                    <canvas id="branchRevenueChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Leaderboard --}}
        <div class="ba-lb-card">
            <div class="ba-card-header">
                <div class="ba-card-header-ico" style="background:var(--ba-amber-soft);color:var(--ba-amber-fill);">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <div>
                    <h6>Performance Leaderboard</h6>
                    <small>Ranked by revenue contribution</small>
                </div>
            </div>
            <div class="ba-lb-list">
                @forelse($leaderboard as $branch)
                    @php
                        $rankClass = $branch['rank'] <= 3 ? 'r-' . $branch['rank'] : 'r-n';
                        $rankIcon = match($branch['rank']) {
                            1 => '<i class="bi bi-trophy-fill"></i>',
                            2 => '<i class="bi bi-award-fill"></i>',
                            3 => '<i class="bi bi-star-fill"></i>',
                            default => $branch['rank'],
                        };
                    @endphp
                    <div class="ba-lb-item">
                        <div class="ba-lb-rank {{ $rankClass }}">{!! $rankIcon !!}</div>
                        <div class="ba-lb-info">
                            <div class="ba-lb-name">{{ $branch['name'] }}</div>
                            <span class="ba-lb-code">{{ $branch['code'] ?? 'N/A' }}</span>
                        </div>
                        <div class="ba-lb-right">
                            <div class="ba-lb-revenue">₱{{ number_format($branch['revenue'], 0) }}</div>
                            <div class="ba-lb-pct">{{ $branch['revenue_percentage'] }}%</div>
                            <div class="ba-lb-track">
                                <div class="ba-lb-fill {{ $rankClass }}"
                                     style="width:0%"
                                     data-target="{{ $branch['revenue_percentage'] }}"></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5" style="color:var(--ba-text-3);">
                        <i class="bi bi-trophy" style="font-size:2.5rem;display:block;margin-bottom:.75rem;"></i>
                        <span style="font-size:.85rem;">No branch data available</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── BRANCH DETAIL CARDS ─────────────────────────────── --}}
    <div class="ba-branches-header">
        <div>
            <h2 class="ba-section-title">Branch Breakdown</h2>
            <p class="ba-section-sub">Individual performance metrics per location</p>
        </div>
    </div>

    <div class="ba-branch-grid">
        @foreach($branches as $branch)
            <div class="ba-branch-card">
                <div class="ba-branch-top">
                    <div>
                        <div class="ba-branch-name">{{ $branch['name'] }}</div>
                        <span class="ba-branch-code-badge">{{ $branch['code'] ?? 'N/A' }}</span>
                    </div>
                    <div class="ba-branch-shop-ico"><i class="bi bi-shop"></i></div>
                </div>

                <div class="ba-metric-row">
                    <div class="ba-metric-label">Total Laundries</div>
                    <div class="ba-metric-value">{{ number_format($branch['laundries_count']) }}</div>
                    <span class="ba-metric-sub">
                        <i class="bi bi-pie-chart-fill" style="font-size:.55rem;"></i>
                        {{ $branch['laundries_percentage'] }}% of total
                    </span>
                </div>

                <div class="ba-metric-row">
                    <div class="ba-metric-label">Total Revenue</div>
                    <div class="ba-metric-value green">₱{{ number_format($branch['revenue'], 2) }}</div>
                    <span class="ba-metric-sub">
                        <i class="bi bi-graph-up-arrow" style="font-size:.55rem;"></i>
                        {{ $branch['revenue_percentage'] }}% of total
                    </span>
                </div>

                <div class="ba-metric-row">
                    <div class="ba-metric-label">Avg Order Value</div>
                    <div class="ba-metric-value teal">₱{{ number_format($branch['avg_laundry_value'], 2) }}</div>
                </div>
            </div>
        @endforeach
    </div>

</div>

@push('scripts')
<script src="{{ asset('assets/chart.js/chart.umd.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Detect theme ─────────────────────────────────────────
    const isDark = () => document.documentElement.getAttribute('data-theme') === 'dark';

    const gridColor  = () => isDark() ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
    const tickColor  = () => isDark() ? '#435165' : '#7A8FAD';
    const tooltipBg  = () => isDark() ? '#131C2B' : '#0D1526';

    // ── Bar chart colors ──────────────────────────────────────
    const PALETTE = [
        '#0F9EA5','#2563EB','#7C3AED','#D97706',
        '#DC2626','#059669','#DB2777','#0891B2',
    ];

    // ── Revenue Chart ─────────────────────────────────────────
    const ctx = document.getElementById('branchRevenueChart');
    if (ctx) {
        const branches  = @json($branches);
        const labels    = branches.map(b => b.name);
        const revenues  = branches.map(b => b.revenue);
        const bgColors  = PALETTE.slice(0, labels.length);
        const bgAlpha   = bgColors.map(c => c + 'CC'); // slight transparency

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Revenue',
                    data: revenues,
                    backgroundColor: bgAlpha,
                    borderColor: bgColors,
                    borderWidth: 1.5,
                    borderRadius: 8,
                    borderSkipped: false,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: tooltipBg(),
                        titleColor: '#fff',
                        bodyColor: '#94a3b8',
                        padding: 14,
                        cornerRadius: 10,
                        titleFont: { family: 'Geist', weight: '700', size: 13 },
                        bodyFont: { family: 'Geist Mono', size: 12 },
                        callbacks: {
                            label: ctx => '  ₱' + ctx.parsed.y.toLocaleString('en-PH', { minimumFractionDigits: 0 }),
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor(), drawBorder: false },
                        border: { display: false },
                        ticks: {
                            color: tickColor(),
                            font: { family: 'Geist Mono', size: 11 },
                            callback: v => '₱' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v),
                        },
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: tickColor(),
                            font: { family: 'Geist', size: 11, weight: '500' },
                            maxRotation: 30,
                        },
                    },
                },
                animation: {
                    duration: 800,
                    easing: 'easeOutQuart',
                },
            },
        });
    }

    // ── Animate leaderboard bars ──────────────────────────────
    const fills = document.querySelectorAll('.ba-lb-fill[data-target]');
    setTimeout(() => {
        fills.forEach(bar => {
            const target = parseFloat(bar.getAttribute('data-target')) || 0;
            bar.style.transition = 'width 0.7s cubic-bezier(0.34,1.56,0.64,1)';
            bar.style.width = target + '%';
        });
    }, 300);
});
</script>
@endpush
@endsection
