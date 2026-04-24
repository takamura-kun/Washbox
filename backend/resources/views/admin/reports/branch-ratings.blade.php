@extends('admin.layouts.app')

@section('page-title', 'Branch Ratings Report')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Geist:wght@300;400;500;600;700;800&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
/* ============================================================
   BRANCH RATINGS REPORT — Warm Editorial Luxury
   Tone: High-end analytics journal. Gold stars earn their weight.
   Typography: Instrument Serif (display) + Geist (body) + Geist Mono (data)
   ============================================================ */

:root {
    --br-bg:           #F9F7F4;
    --br-surface:      #FFFFFF;
    --br-surface-2:    #F5F2EE;
    --br-surface-3:    #EDE9E3;
    --br-border:       #E4DDD3;
    --br-border-2:     #D6CFC4;

    --br-text-1:       #1A1208;
    --br-text-2:       #4A3F2F;
    --br-text-3:       #8C7B65;
    --br-text-4:       #C4B49E;

    --br-gold:         #B45309;
    --br-gold-fill:    #D97706;
    --br-gold-bright:  #F59E0B;
    --br-gold-soft:    rgba(217,119,6,0.10);
    --br-gold-border:  rgba(180,83,9,0.18);

    --br-green:        #065F46;
    --br-green-fill:   #059669;
    --br-green-soft:   rgba(5,150,105,0.09);

    --br-blue:         #1D4ED8;
    --br-blue-fill:    #2563EB;
    --br-blue-soft:    rgba(37,99,235,0.09);

    --br-cyan:         #0E7490;
    --br-cyan-soft:    rgba(14,116,144,0.09);

    --br-red:          #991B1B;
    --br-red-fill:     #DC2626;
    --br-red-soft:     rgba(220,38,38,0.08);

    --br-shadow-xs:    0 1px 2px rgba(26,18,8,0.05);
    --br-shadow-sm:    0 2px 8px rgba(26,18,8,0.07), 0 1px 2px rgba(26,18,8,0.04);
    --br-shadow-md:    0 6px 24px rgba(26,18,8,0.10), 0 2px 6px rgba(26,18,8,0.05);
    --br-shadow-lg:    0 16px 48px rgba(26,18,8,0.14), 0 4px 12px rgba(26,18,8,0.06);
    --br-shadow-xl:    0 24px 64px rgba(26,18,8,0.20), 0 8px 20px rgba(26,18,8,0.08);

    --br-radius:       20px;
    --br-radius-sm:    12px;
    --br-radius-xs:    8px;
    --br-t:            0.18s ease;
}

[data-theme="dark"] {
    --br-bg:           #111827;
    --br-surface:      #1F2937;
    --br-surface-2:    #374151;
    --br-surface-3:    #4B5563;
    --br-border:       #374151;
    --br-border-2:     #4B5563;

    --br-text-1:       #F9FAFB;
    --br-text-2:       #D1D5DB;
    --br-text-3:       #9CA3AF;
    --br-text-4:       #6B7280;

    --br-gold:         #F59E0B;
    --br-gold-fill:    #FBBF24;
    --br-gold-bright:  #FCD34D;
    --br-gold-soft:    rgba(245,158,11,0.12);
    --br-gold-border:  rgba(245,158,11,0.22);

    --br-green:        #34D399;
    --br-green-fill:   #10B981;
    --br-green-soft:   rgba(16,185,129,0.10);

    --br-blue:         #60A5FA;
    --br-blue-fill:    #3B82F6;
    --br-blue-soft:    rgba(59,130,246,0.12);

    --br-cyan:         #22D3EE;
    --br-cyan-soft:    rgba(34,211,238,0.10);

    --br-red:          #F87171;
    --br-red-fill:     #EF4444;
    --br-red-soft:     rgba(239,68,68,0.10);

    --br-shadow-xs:    0 1px 2px rgba(0,0,0,0.30);
    --br-shadow-sm:    0 2px 8px rgba(0,0,0,0.40);
    --br-shadow-md:    0 6px 24px rgba(0,0,0,0.50);
    --br-shadow-lg:    0 16px 48px rgba(0,0,0,0.60);
    --br-shadow-xl:    0 24px 64px rgba(0,0,0,0.70);
}

/* ---- Page ---- */
.br-page {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: var(--br-bg);
    padding: 0 0 4rem;
    color: var(--br-text-1);
}

/* ---- Page Header ---- */
.br-page-header {
    margin-bottom: 2.5rem;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.br-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--br-gold);
    background: var(--br-gold-soft);
    border: 1px solid var(--br-gold-border);
    padding: 0.28rem 0.75rem;
    border-radius: 999px;
    margin-bottom: 0.75rem;
}

.br-page-title {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 2.25rem;
    font-weight: 400;
    color: var(--br-text-1);
    letter-spacing: -0.02em;
    line-height: 1.15;
    margin: 0 0 0.4rem;
}

.br-page-sub {
    font-size: 0.875rem;
    color: var(--br-text-3);
    margin: 0;
}

.br-header-actions {
    display: flex;
    gap: 0.6rem;
    flex-shrink: 0;
}

.br-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    height: 38px;
    padding: 0 1rem;
    border-radius: var(--br-radius-xs);
    font-size: 0.8rem;
    font-weight: 600;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    cursor: pointer;
    border: none;
    text-decoration: none;
    white-space: nowrap;
    transition: all var(--br-t);
}

.br-btn-ghost {
    background: transparent;
    color: var(--br-text-2);
    border: 1.5px solid var(--br-border);
}

.br-btn-ghost:hover {
    background: var(--br-surface-2);
    border-color: var(--br-border-2);
    color: var(--br-text-1);
    text-decoration: none;
}

.br-btn-gold {
    background: var(--br-gold-fill);
    color: #fff;
    border: 1.5px solid transparent;
    box-shadow: 0 2px 8px rgba(217,119,6,0.28);
}

.br-btn-gold:hover {
    background: var(--br-gold);
    color: #fff;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(180,83,9,0.35);
}

/* ---- Filter Card ---- */
.br-filter-card {
    background: var(--br-surface);
    border: 1px solid var(--br-border);
    border-radius: var(--br-radius);
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--br-shadow-xs);
}

.br-filter-row {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
    flex-wrap: wrap;
}

.br-fg { display: flex; flex-direction: column; gap: 0.3rem; flex: 1; min-width: 130px; }
.br-fg.wide { flex: 2; }
.br-fg.narrow { flex: 0 0 120px; }
.br-fg.act { flex: 0 0 auto; }

.br-field-label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--br-text-3);
}

.br-select, .br-input {
    height: 40px;
    background: var(--br-surface-2);
    border: 1px solid var(--br-border);
    border-radius: var(--br-radius-xs);
    padding: 0 0.875rem;
    font-size: 0.85rem;
    color: var(--br-text-1);
    width: 100%;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    outline: none;
    transition: border-color var(--br-t), box-shadow var(--br-t);
    appearance: none;
    -webkit-appearance: none;
}

.br-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath fill='%238C7B65' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    padding-right: 2.25rem;
    cursor: pointer;
}

.br-select:focus, .br-input:focus {
    border-color: var(--br-gold-fill);
    box-shadow: 0 0 0 3px var(--br-gold-soft);
}

.br-apply-btn {
    height: 40px;
    padding: 0 1.25rem;
    border-radius: var(--br-radius-xs);
    font-size: 0.825rem;
    font-weight: 700;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: var(--br-text-1);
    color: var(--br-bg);
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: all var(--br-t);
    white-space: nowrap;
}

.br-apply-btn:hover {
    opacity: 0.85;
    transform: translateY(-1px);
    box-shadow: var(--br-shadow-sm);
}

.br-clear-link {
    height: 40px;
    padding: 0 1rem;
    border-radius: var(--br-radius-xs);
    font-size: 0.8rem;
    font-weight: 600;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: transparent;
    color: var(--br-text-3);
    border: 1.5px solid var(--br-border);
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    text-decoration: none;
    transition: all var(--br-t);
}

.br-clear-link:hover {
    background: var(--br-surface-2);
    color: var(--br-text-1);
    text-decoration: none;
}

/* ---- Summary Stats ---- */
.br-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.br-stat {
    background: var(--br-surface);
    border: 1px solid var(--br-border);
    border-radius: var(--br-radius-sm);
    padding: 1.25rem;
    box-shadow: var(--br-shadow-xs);
    position: relative;
    overflow: hidden;
    transition: transform var(--br-t), box-shadow var(--br-t);
}

.br-stat::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 2px;
    border-radius: 2px 2px 0 0;
    transition: opacity var(--br-t);
    opacity: 0;
}

.br-stat:hover {
    transform: translateY(-3px);
    box-shadow: var(--br-shadow-md);
}

.br-stat:hover::after { opacity: 1; }

.br-stat.s-cyan::after   { background: var(--br-cyan-fill, var(--br-cyan)); }
.br-stat.s-gold::after   { background: var(--br-gold-fill); }
.br-stat.s-green::after  { background: var(--br-green-fill); }
.br-stat.s-blue::after   { background: var(--br-blue-fill); }

.br-stat-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.br-stat-ico {
    width: 40px; height: 40px;
    border-radius: var(--br-radius-xs);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.05rem;
}

.br-stat.s-cyan  .br-stat-ico { background: var(--br-cyan-soft);  color: var(--br-cyan);  }
.br-stat.s-gold  .br-stat-ico { background: var(--br-gold-soft);  color: var(--br-gold-fill);  }
.br-stat.s-green .br-stat-ico { background: var(--br-green-soft); color: var(--br-green-fill); }
.br-stat.s-blue  .br-stat-ico { background: var(--br-blue-soft);  color: var(--br-blue-fill);  }

.br-stat-label {
    font-size: 0.63rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--br-text-3);
    margin-bottom: 0.3rem;
}

.br-stat-value {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 1.85rem;
    font-weight: 700;
    color: var(--br-text-1);
    line-height: 1;
    letter-spacing: -0.03em;
}

.br-stat-sub {
    font-size: 0.72rem;
    color: var(--br-text-3);
    margin-top: 0.4rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* ---- Mid-section: Distribution + Performance ---- */
.br-mid-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.br-panel {
    background: var(--br-surface);
    border: 1px solid var(--br-border);
    border-radius: var(--br-radius-sm);
    overflow: hidden;
    box-shadow: var(--br-shadow-xs);
}

.br-panel-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--br-border);
    background: var(--br-surface-2);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.br-panel-header-ico {
    width: 26px; height: 26px;
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem;
}

.br-panel-header h6 {
    font-size: 0.825rem;
    font-weight: 700;
    color: var(--br-text-1);
    margin: 0;
}

.br-panel-body { padding: 1.25rem; }

/* Star distribution bars */
.br-dist-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.br-dist-row:last-child { margin-bottom: 0; }

.br-dist-stars {
    display: flex;
    gap: 2px;
    width: 90px;
    flex-shrink: 0;
}

.br-dist-stars i {
    font-size: 0.7rem;
    color: var(--br-gold-fill);
}

.br-dist-track {
    flex: 1;
    height: 8px;
    background: var(--br-surface-3);
    border-radius: 4px;
    overflow: hidden;
}

.br-dist-fill {
    height: 100%;
    border-radius: 4px;
    background: var(--br-gold-fill);
    transition: width 0.8s cubic-bezier(0.34,1.56,0.64,1);
}

.br-dist-count {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 0.72rem;
    color: var(--br-text-2);
    min-width: 70px;
    text-align: right;
}

/* Branch perf lists */
.br-perf-section h6 {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--br-text-3);
    margin-bottom: 0.75rem;
}

.br-perf-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-radius: var(--br-radius-xs);
    background: var(--br-surface-2);
    transition: background var(--br-t);
}

.br-perf-item:hover { background: var(--br-surface-3); }

.br-perf-name {
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--br-text-1);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
}

.br-perf-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 700;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    white-space: nowrap;
}

.br-perf-badge.good { background: var(--br-green-soft);  color: var(--br-green-fill); border: 1px solid rgba(5,150,105,0.18); }
.br-perf-badge.warn { background: var(--br-gold-soft);   color: var(--br-gold);       border: 1px solid var(--br-gold-border); }

/* ---- Branch Cards Grid ---- */
.br-section-title {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 1.5rem;
    font-weight: 400;
    color: var(--br-text-1);
    letter-spacing: -0.02em;
    margin: 0 0 0.25rem;
}

.br-section-sub {
    font-size: 0.8rem;
    color: var(--br-text-3);
    margin: 0 0 1.5rem;
}

.br-branch-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.25rem;
    margin-bottom: 3rem;
}

.br-branch-card {
    background: var(--br-surface);
    border: 1px solid var(--br-border);
    border-radius: var(--br-radius-sm);
    padding: 1.5rem;
    box-shadow: var(--br-shadow-xs);
    transition: transform var(--br-t), box-shadow var(--br-t), border-color var(--br-t);
    position: relative;
    overflow: hidden;
}

.br-branch-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--br-gold-fill), var(--br-gold-bright));
    border-radius: 3px 3px 0 0;
}

.br-branch-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--br-shadow-md);
    border-color: var(--br-border-2);
}

.br-branch-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.25rem;
}

.br-branch-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--br-text-1);
    margin: 0 0 0.2rem;
    line-height: 1.3;
}

.br-branch-code {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 0.68rem;
    color: var(--br-text-3);
}

.br-branch-score {
    text-align: right;
    flex-shrink: 0;
}

.br-branch-score-num {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--br-gold-fill);
    line-height: 1;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.br-branch-score-num i { font-size: 0.9rem; }

.br-branch-score-sub {
    font-size: 0.65rem;
    color: var(--br-text-3);
    margin-top: 0.2rem;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Mini distribution */
.br-mini-dist { margin-bottom: 1.25rem; }

.br-mini-dist-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.35rem;
}

.br-mini-dist-label {
    font-size: 0.7rem;
    color: var(--br-text-3);
    width: 20px;
    flex-shrink: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.br-mini-dist-track {
    flex: 1;
    height: 5px;
    background: var(--br-surface-3);
    border-radius: 3px;
    overflow: hidden;
}

.br-mini-dist-fill {
    height: 100%;
    border-radius: 3px;
    background: var(--br-gold-fill);
}

.br-mini-dist-count {
    font-size: 0.68rem;
    color: var(--br-text-3);
    width: 18px;
    text-align: right;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Branch trend mini */
.br-trend-section {
    border-top: 1px solid var(--br-border);
    padding-top: 1rem;
    margin-top: 0.25rem;
}

.br-trend-label {
    font-size: 0.62rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--br-text-3);
    margin-bottom: 0.625rem;
}

.br-trend-row {
    display: flex;
    gap: 0.4rem;
}

.br-trend-item {
    flex: 1;
    text-align: center;
}

.br-trend-month {
    font-size: 0.58rem;
    color: var(--br-text-3);
    margin-bottom: 0.25rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.br-trend-score {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.2rem 0.3rem;
    border-radius: 5px;
    display: inline-block;
}

.score-5 { background: var(--br-green-soft);  color: var(--br-green-fill); }
.score-4 { background: var(--br-blue-soft);   color: var(--br-blue-fill);  }
.score-3 { background: var(--br-gold-soft);   color: var(--br-gold);       }
.score-2, .score-1 { background: var(--br-red-soft); color: var(--br-red-fill); }

/* ---- Ratings Grid ---- */
.br-ratings-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.br-count-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.875rem;
    border-radius: 999px;
    background: var(--br-gold-soft);
    border: 1px solid var(--br-gold-border);
    color: var(--br-gold);
    font-size: 0.72rem;
    font-weight: 700;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.br-ratings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: 1.25rem;
}

/* Rating Card */
.br-rating-card {
    background: var(--br-surface);
    border: 1px solid var(--br-border);
    border-radius: var(--br-radius-sm);
    padding: 1.5rem;
    box-shadow: var(--br-shadow-xs);
    display: flex;
    flex-direction: column;
    gap: 1rem;
    transition: transform var(--br-t), box-shadow var(--br-t), border-color var(--br-t);
    position: relative;
    overflow: hidden;
}

.br-rating-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--br-gold-fill), var(--br-gold-bright));
}

.br-rating-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--br-shadow-md);
    border-color: var(--br-gold-border);
}

/* Customer row */
.br-cust-row {
    display: flex;
    align-items: center;
    gap: 0.875rem;
}

.br-avatar {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    font-weight: 800;
    color: #fff;
    flex-shrink: 0;
    letter-spacing: -0.02em;
}

.br-cust-name {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--br-text-1);
    margin: 0 0 0.15rem;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

.br-cust-email {
    font-size: 0.72rem;
    color: var(--br-text-3);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* Stars row */
.br-stars-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.875rem 1rem;
    background: var(--br-gold-soft);
    border: 1px solid var(--br-gold-border);
    border-radius: var(--br-radius-xs);
}

.br-stars {
    display: flex;
    gap: 3px;
}

.br-stars i {
    font-size: 0.9rem;
    color: var(--br-gold-fill);
}

.br-stars i.empty { color: var(--br-border-2); }

.br-stars-right {}

.br-score-num {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--br-text-1);
    line-height: 1;
}

.br-score-sub {
    font-size: 0.62rem;
    color: var(--br-text-3);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 600;
    margin-top: 2px;
    text-align: right;
}

/* Comment */
.br-comment-box {
    flex: 1;
    background: var(--br-surface-2);
    border: 1px solid var(--br-border);
    border-left: 3px solid var(--br-gold-fill);
    border-radius: 0 var(--br-radius-xs) var(--br-radius-xs) 0;
    padding: 0.75rem 0.875rem;
}

.br-comment-label {
    font-size: 0.6rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--br-text-3);
    margin-bottom: 0.35rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.br-comment-text {
    font-size: 0.82rem;
    color: var(--br-text-2);
    line-height: 1.65;
    font-style: italic;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.br-comment-empty {
    font-size: 0.8rem;
    color: var(--br-text-4);
    font-style: normal;
}

/* Footer */
.br-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 0.875rem;
    border-top: 1px solid var(--br-border);
}

.br-date-main { font-size: 0.8rem; font-weight: 600; color: var(--br-text-2); }
.br-date-sub  { font-size: 0.68rem; color: var(--br-text-3); margin-top: 1px; }

.br-view-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.45rem 0.875rem;
    border-radius: var(--br-radius-xs);
    font-size: 0.72rem;
    font-weight: 700;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: var(--br-gold-soft);
    color: var(--br-gold);
    border: 1px solid var(--br-gold-border);
    cursor: pointer;
    transition: all var(--br-t);
}

.br-view-btn:hover {
    background: var(--br-gold-fill);
    color: #fff;
    border-color: var(--br-gold-fill);
    transform: translateY(-1px);
}

/* ---- Empty State ---- */
.br-empty {
    text-align: center;
    padding: 5rem 2rem;
}

.br-empty-ico {
    width: 72px; height: 72px;
    border-radius: 20px;
    background: var(--br-surface-2);
    border: 1px solid var(--br-border);
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem;
    color: var(--br-gold-bright);
    margin: 0 auto 1.25rem;
}

.br-empty h5 {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 1.25rem;
    font-weight: 400;
    color: var(--br-text-2);
    margin-bottom: 0.35rem;
}

.br-empty p { font-size: 0.85rem; color: var(--br-text-3); margin: 0; }

/* ---- Modal ---- */
.br-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(26,18,8,0.65);
    backdrop-filter: blur(4px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.br-modal-overlay.active { display: flex; }

.br-modal-box {
    background: var(--br-surface);
    border: 1px solid var(--br-border);
    border-radius: var(--br-radius);
    max-width: 760px;
    width: 100%;
    max-height: 88vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: var(--br-shadow-xl);
    animation: modalIn 0.25s ease both;
}

@keyframes modalIn {
    from { opacity: 0; transform: translateY(16px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.br-modal-head {
    padding: 1.5rem 1.75rem;
    border-bottom: 1px solid var(--br-border);
    background: var(--br-surface-2);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.br-modal-title {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 1.35rem;
    font-weight: 400;
    color: var(--br-text-1);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    letter-spacing: -0.01em;
}

.br-modal-title i { color: var(--br-gold-fill); font-size: 1rem; }

.br-modal-close {
    width: 36px; height: 36px;
    border: 1.5px solid var(--br-border);
    background: transparent;
    border-radius: var(--br-radius-xs);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    color: var(--br-text-3);
    transition: all var(--br-t);
}

.br-modal-close:hover { background: var(--br-surface-3); color: var(--br-text-1); transform: rotate(90deg); }

.br-modal-body {
    padding: 1.5rem 1.75rem;
    overflow-y: auto;
    flex: 1;
}

/* Timeline items */
.br-timeline { display: flex; flex-direction: column; gap: 1rem; }

.br-timeline-item {
    background: var(--br-surface-2);
    border: 1px solid var(--br-border);
    border-left: 3px solid var(--br-gold-fill);
    border-radius: 0 var(--br-radius-xs) var(--br-radius-xs) 0;
    padding: 1.25rem 1.5rem;
    transition: all var(--br-t);
}

.br-timeline-item:hover {
    transform: translateX(3px);
    box-shadow: var(--br-shadow-sm);
}

.br-tl-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.br-tl-stars {
    display: flex;
    gap: 3px;
}

.br-tl-stars i { font-size: 0.85rem; color: var(--br-gold-fill); }
.br-tl-stars i.empty { color: var(--br-border-2); }

.br-tl-date {
    font-size: 0.75rem;
    color: var(--br-text-3);
    font-weight: 600;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.br-tl-badges {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    flex-wrap: wrap;
}

.br-tl-badge {
    padding: 0.25rem 0.625rem;
    background: var(--br-gold-soft);
    border: 1px solid var(--br-gold-border);
    color: var(--br-text-2);
    border-radius: var(--br-radius-xs);
    font-size: 0.7rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.br-tl-comment {
    font-size: 0.82rem;
    color: var(--br-text-2);
    line-height: 1.65;
    font-style: italic;
}

.br-tl-no-comment { font-size: 0.8rem; color: var(--br-text-4); font-style: normal; }

/* ---- Animations ---- */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

.br-page-header   { animation: fadeUp 0.4s ease both; }
.br-filter-card   { animation: fadeUp 0.4s ease 0.06s both; }
.br-stats-grid    { animation: fadeUp 0.4s ease 0.12s both; }
.br-mid-grid      { animation: fadeUp 0.4s ease 0.18s both; }
.br-branch-grid   { animation: fadeUp 0.4s ease 0.24s both; }
.br-ratings-grid  { animation: fadeUp 0.4s ease 0.30s both; }

/* ---- Responsive ---- */
@media (max-width: 1100px) {
    .br-stats-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .br-mid-grid { grid-template-columns: 1fr; }
    .br-stats-grid { grid-template-columns: 1fr 1fr; }
    .br-page-title { font-size: 1.65rem; }
    .br-filter-row { flex-direction: column; }
    .br-fg, .br-fg.wide, .br-fg.narrow { flex: 1 1 100%; min-width: 100%; }
    .br-modal-box { margin: 0.5rem; }
    .br-modal-head, .br-modal-body { padding: 1.25rem; }
}

@media (max-width: 480px) {
    .br-stats-grid { grid-template-columns: 1fr; }
    .br-ratings-grid, .br-branch-grid { grid-template-columns: 1fr; }
}
</style>

<div class="br-page">

    {{-- ── PAGE HEADER ─────────────────────────────────────── --}}
    <div class="br-page-header">
        <div>
            <div class="br-eyebrow">
                <i class="bi bi-star-fill"></i> Admin Report
            </div>
            <h1 class="br-page-title">Branch Ratings Report</h1>
            <p class="br-page-sub">Monitor customer satisfaction across all branches</p>
        </div>
        <div class="br-header-actions">
            <a href="{{ route('admin.reports.index') }}" class="br-btn br-btn-ghost">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <a href="{{ route('admin.reports.branch-ratings.export', request()->all()) }}" class="br-btn br-btn-gold">
                <i class="bi bi-download"></i> Export
            </a>
        </div>
    </div>

    {{-- ── FILTERS ──────────────────────────────────────────── --}}
    <div class="br-filter-card">
        <form method="GET" action="{{ route('admin.reports.branch-ratings') }}">
            <div class="br-filter-row">

                <div class="br-fg wide">
                    <label class="br-field-label">Date Range</label>
                    <select name="filter" class="br-select" id="filterSelect">
                        <option value="today"        {{ request('filter') == 'today'        ? 'selected' : '' }}>Today</option>
                        <option value="this_week"    {{ request('filter') == 'this_week'    ? 'selected' : '' }}>This Week</option>
                        <option value="this_month"   {{ request('filter') == 'this_month'   ? 'selected' : '' }}>This Month</option>
                        <option value="last_month"   {{ request('filter') == 'last_month'   ? 'selected' : '' }}>Last Month</option>
                        <option value="last_3_months"{{ request('filter') == 'last_3_months'? 'selected' : '' }}>Last 3 Months</option>
                        <option value="last_6_months"{{ request('filter') == 'last_6_months'? 'selected' : '' }}>Last 6 Months</option>
                        <option value="this_year"    {{ request('filter') == 'this_year'    ? 'selected' : '' }}>This Year</option>
                        <option value="custom"       {{ request('filter') == 'custom'       ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>

                <div class="br-fg narrow">
                    <label class="br-field-label">Branch</label>
                    <select name="branch_id" class="br-select">
                        <option value="">All Branches</option>
                        @foreach($allBranches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="br-fg narrow">
                    <label class="br-field-label">Rating</label>
                    <select name="rating" class="br-select">
                        <option value="">All Ratings</option>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                                {{ $i }} Star{{ $i > 1 ? 's' : '' }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="br-fg narrow" id="customDateFrom" style="display:{{ request('filter') == 'custom' ? 'flex' : 'none' }};flex-direction:column;gap:.3rem;">
                    <label class="br-field-label">From Date</label>
                    <input type="date" name="date_from" class="br-input" value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
                </div>

                <div class="br-fg narrow" id="customDateTo" style="display:{{ request('filter') == 'custom' ? 'flex' : 'none' }};flex-direction:column;gap:.3rem;">
                    <label class="br-field-label">To Date</label>
                    <input type="date" name="date_to" class="br-input" value="{{ request('date_to', now()->format('Y-m-d')) }}">
                </div>

                <div class="br-fg act" style="display:flex;flex-direction:column;gap:.3rem;">
                    <label class="br-field-label" style="visibility:hidden;">—</label>
                    <div style="display:flex;gap:.5rem;align-items:center;">
                        <button type="submit" class="br-apply-btn">
                            <i class="bi bi-funnel"></i> Apply
                        </button>
                        @if(request()->anyFilled(['filter','branch_id','rating','date_from','date_to']))
                            <a href="{{ route('admin.reports.branch-ratings') }}" class="br-clear-link">
                                <i class="bi bi-x"></i> Clear
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </form>
    </div>

    {{-- ── SUMMARY STATS ────────────────────────────────────── --}}
    <div class="br-stats-grid">

        <div class="br-stat s-cyan">
            <div class="br-stat-top">
                <div>
                    <div class="br-stat-label">Total Ratings</div>
                    <div class="br-stat-value">{{ number_format($summary['total_ratings']) }}</div>
                </div>
                <div class="br-stat-ico"><i class="bi bi-chat-text"></i></div>
            </div>
            <div class="br-stat-sub">
                <i class="bi bi-building"></i>
                {{ $summary['branches_with_ratings'] }} branches rated
            </div>
        </div>

        <div class="br-stat s-gold">
            <div class="br-stat-top">
                <div>
                    <div class="br-stat-label">Overall Average</div>
                    <div class="br-stat-value">{{ number_format($summary['avg_rating'], 2) }}</div>
                </div>
                <div class="br-stat-ico"><i class="bi bi-star-fill"></i></div>
            </div>
            <div class="br-stat-sub">
                <i class="bi bi-star"></i> out of 5.00 stars
            </div>
        </div>

        <div class="br-stat s-green">
            <div class="br-stat-top">
                <div>
                    <div class="br-stat-label">With Comments</div>
                    <div class="br-stat-value">{{ number_format($summary['total_comments']) }}</div>
                </div>
                <div class="br-stat-ico"><i class="bi bi-chat-quote"></i></div>
            </div>
            <div class="br-stat-sub">
                <i class="bi bi-percent"></i>
                @php $fbRate = $summary['total_ratings'] > 0 ? round(($summary['total_comments'] / $summary['total_ratings']) * 100) : 0; @endphp
                {{ $fbRate }}% feedback rate
            </div>
        </div>

        <div class="br-stat s-blue">
            <div class="br-stat-top">
                <div>
                    <div class="br-stat-label">Satisfaction Rate</div>
                    @php
                        $fiveStarCount = $summary['rating_distribution'][5]['count'] ?? 0;
                        $fourStarCount = $summary['rating_distribution'][4]['count'] ?? 0;
                        $satisfaction  = ($fiveStarCount + $fourStarCount) / max($summary['total_ratings'], 1) * 100;
                    @endphp
                    <div class="br-stat-value">{{ number_format($satisfaction, 1) }}<span style="font-size:1rem;font-weight:500;color:var(--br-text-3);">%</span></div>
                </div>
                <div class="br-stat-ico"><i class="bi bi-emoji-smile"></i></div>
            </div>
            <div class="br-stat-sub">
                <i class="bi bi-star-fill" style="color:var(--br-gold-fill);"></i>
                4–5 star ratings
            </div>
        </div>

    </div>

    {{-- ── DISTRIBUTION + BRANCH PERFORMANCE ───────────────── --}}
    <div class="br-mid-grid">

        {{-- Distribution --}}
        <div class="br-panel">
            <div class="br-panel-header">
                <div class="br-panel-header-ico" style="background:var(--br-gold-soft);color:var(--br-gold);">
                    <i class="bi bi-bar-chart-fill"></i>
                </div>
                <h6>Rating Distribution</h6>
            </div>
            <div class="br-panel-body">
                @foreach(range(5, 1, -1) as $star)
                    @php
                        $count = $summary['rating_distribution'][$star]['count'] ?? 0;
                        $pct   = $summary['rating_distribution'][$star]['percentage'] ?? 0;
                    @endphp
                    <div class="br-dist-row">
                        <div class="br-dist-stars">
                            @for($i = 0; $i < $star; $i++)
                                <i class="bi bi-star-fill"></i>
                            @endfor
                            @for($i = $star; $i < 5; $i++)
                                <i class="bi bi-star" style="color:var(--br-border-2);"></i>
                            @endfor
                        </div>
                        <div class="br-dist-track">
                            <div class="br-dist-fill" style="width:{{ $pct }}%;opacity:{{ 0.4 + ($star / 5) * 0.6 }};"></div>
                        </div>
                        <div class="br-dist-count">{{ $count }} <span style="color:var(--br-text-4);">({{ $pct }}%)</span></div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Branch Performance --}}
        <div class="br-panel">
            <div class="br-panel-header">
                <div class="br-panel-header-ico" style="background:var(--br-gold-soft);color:var(--br-gold);">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <h6>Branch Performance</h6>
            </div>
            <div class="br-panel-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="br-perf-section">
                            <h6>🏆 Top Rated</h6>
                            @forelse($topBranches as $branch)
                                <div class="br-perf-item">
                                    <span class="br-perf-name">{{ $branch->name }}</span>
                                    <span class="br-perf-badge good">
                                        <i class="bi bi-star-fill" style="font-size:.55rem;"></i>
                                        {{ number_format($branch->average_rating, 1) }}
                                    </span>
                                </div>
                            @empty
                                <p style="font-size:.78rem;color:var(--br-text-3);">No data</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="br-perf-section">
                            <h6>⚠ Needs Attention</h6>
                            @forelse($needsImprovement as $branch)
                                <div class="br-perf-item">
                                    <span class="br-perf-name">{{ $branch->name }}</span>
                                    <span class="br-perf-badge warn">
                                        <i class="bi bi-star-fill" style="font-size:.55rem;"></i>
                                        {{ number_format($branch->average_rating, 1) }}
                                    </span>
                                </div>
                            @empty
                                <p style="font-size:.78rem;color:var(--br-text-3);">No data</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── BRANCH CARDS ─────────────────────────────────────── --}}
    @php $ratedBranches = $branches->filter(fn($b) => $b['total_ratings'] > 0); @endphp

    @if(count($ratedBranches) > 0)
        <div class="mb-2">
            <h2 class="br-section-title">By Branch</h2>
            <p class="br-section-sub">Detailed breakdown per branch location</p>
        </div>

        <div class="br-branch-grid">
            @foreach($branches as $branch)
                @if($branch['total_ratings'] > 0)
                    <div class="br-branch-card">
                        <div class="br-branch-top">
                            <div>
                                <div class="br-branch-name">{{ $branch['name'] }}</div>
                                <div class="br-branch-code">{{ $branch['code'] }}</div>
                            </div>
                            <div class="br-branch-score">
                                <div class="br-branch-score-num">
                                    {{ $branch['average_rating'] }}<i class="bi bi-star-fill" style="font-size:.8rem;color:var(--br-gold-fill);"></i>
                                </div>
                                <div class="br-branch-score-sub">{{ $branch['total_ratings'] }} ratings</div>
                            </div>
                        </div>

                        <div class="br-mini-dist">
                            @foreach(range(5, 1, -1) as $star)
                                @php
                                    $cnt = $branch['distribution'][$star] ?? 0;
                                    $pct = $branch['total_ratings'] > 0 ? ($cnt / $branch['total_ratings']) * 100 : 0;
                                @endphp
                                <div class="br-mini-dist-row">
                                    <span class="br-mini-dist-label">{{ $star }}★</span>
                                    <div class="br-mini-dist-track">
                                        <div class="br-mini-dist-fill" style="width:{{ $pct }}%;"></div>
                                    </div>
                                    <span class="br-mini-dist-count">{{ $cnt }}</span>
                                </div>
                            @endforeach
                        </div>

                        @if(count($branch['trend']) > 0)
                            <div class="br-trend-section">
                                <div class="br-trend-label">Monthly Trend</div>
                                <div class="br-trend-row">
                                    @foreach($branch['trend'] as $trend)
                                        <div class="br-trend-item">
                                            <div class="br-trend-month">{{ $trend['month'] }}</div>
                                            <span class="br-trend-score score-{{ round($trend['rating']) }}">
                                                {{ $trend['rating'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    {{-- ── RECENT RATINGS GRID ──────────────────────────────── --}}
    <div class="br-ratings-header">
        <div>
            <h2 class="br-section-title">Customer Reviews</h2>
            <p class="br-section-sub">Individual ratings and comments</p>
        </div>
        <div class="br-count-chip">
            <i class="bi bi-people"></i>
            {{ $customersWithRatings->count() }} customers
        </div>
    </div>

    @if($customersWithRatings->count() > 0)
        <div class="br-ratings-grid">
            @foreach($customersWithRatings as $customerData)
                @php
                    $initial = strtoupper(substr($customerData->customer->name, 0, 1));
                    $avatarColors = [
                        'A'=>'135deg,#1e3a5f,#2563eb','B'=>'135deg,#1a1a4e,#7c3aed','C'=>'135deg,#0f4c35,#059669',
                        'D'=>'135deg,#0e3d55,#0891b2','E'=>'135deg,#3d1a1a,#dc2626','F'=>'135deg,#2a1a40,#8b5cf6',
                        'G'=>'135deg,#0d2e1f,#10b981','H'=>'135deg,#1a0e2e,#6366f1','I'=>'135deg,#3b2200,#d97706',
                        'J'=>'135deg,#1e1044,#4f46e5','K'=>'135deg,#032030,#0891b2','L'=>'135deg,#0f2c10,#16a34a',
                        'M'=>'135deg,#2c0a1e,#db2777','N'=>'135deg,#1e2c00,#65a30d','O'=>'135deg,#2c1400,#f97316',
                        'P'=>'135deg,#0e1e4e,#1d4ed8','Q'=>'135deg,#1a0030,#9333ea','R'=>'135deg,#2e0404,#b91c1c',
                        'S'=>'135deg,#003030,#0f766e','T'=>'135deg,#200a2a,#a21caf','U'=>'135deg,#0a1a3e,#1e40af',
                        'V'=>'135deg,#101a00,#4d7c0f','W'=>'135deg,#2a0814,#be185d','X'=>'135deg,#1c0a00,#c2410c',
                        'Y'=>'135deg,#001828,#0369a1','Z'=>'135deg,#20001a,#be123c',
                    ];
                    $grad = $avatarColors[$initial] ?? '135deg,#1a1a4e,#7c3aed';
                @endphp

                <div class="br-rating-card">

                    {{-- Customer --}}
                    <div class="br-cust-row">
                        <div class="br-avatar" style="background:linear-gradient({{ $grad }});">{{ $initial }}</div>
                        <div style="flex:1;min-width:0;">
                            <div class="br-cust-name">{{ $customerData->customer->name }}</div>
                            <div class="br-cust-email">{{ $customerData->customer->email }}</div>
                        </div>
                    </div>

                    {{-- Stars --}}
                    <div class="br-stars-row">
                        <div>
                            <div class="br-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= round($customerData->average_rating) ? '-fill' : '' }}{{ $i > round($customerData->average_rating) ? ' empty' : '' }}"></i>
                                @endfor
                            </div>
                            <div style="font-size:.62rem;color:var(--br-text-3);font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-top:.25rem;">
                                {{ $customerData->rating_count }} {{ $customerData->rating_count == 1 ? 'rating' : 'ratings' }}
                            </div>
                        </div>
                        <div class="br-stars-right">
                            <div class="br-score-num">{{ $customerData->average_rating }}<span style="font-size:.75rem;color:var(--br-text-3);font-weight:400;">/5</span></div>
                        </div>
                    </div>

                    {{-- Comment --}}
                    <div class="br-comment-box">
                        <div class="br-comment-label"><i class="bi bi-chat-left-quote"></i> Latest Comment</div>
                        @if($customerData->latest_rating->comment)
                            <div class="br-comment-text">"{{ $customerData->latest_rating->comment }}"</div>
                        @else
                            <div class="br-comment-empty">No comment provided</div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="br-card-footer">
                        <div>
                            <div class="br-date-main">{{ $customerData->latest_rating->created_at->format('M j, Y') }}</div>
                            <div class="br-date-sub">Latest rating</div>
                        </div>
                        @if($customerData->rating_count > 1)
                            <button class="br-view-btn" onclick="openBrModal({{ $customerData->customer->id }})">
                                <i class="bi bi-list-ul"></i> View All ({{ $customerData->rating_count }})
                            </button>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>

    @else
        <div class="br-empty">
            <div class="br-empty-ico"><i class="bi bi-star"></i></div>
            <h5>No Ratings Found</h5>
            <p>No ratings match the selected filters. Try adjusting your criteria.</p>
        </div>
    @endif

</div>

{{-- ── MODAL ────────────────────────────────────────────────── --}}
<div class="br-modal-overlay" id="brModal">
    <div class="br-modal-box">
        <div class="br-modal-head">
            <div class="br-modal-title">
                <i class="bi bi-star-fill"></i>
                <span id="brModalName"></span>
                <span id="brModalAvg" style="font-size:.95rem;color:var(--br-text-3);font-family:'Geist Mono',monospace;font-weight:400;"></span>
            </div>
            <button class="br-modal-close" onclick="closeBrModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="br-modal-body">
            <div class="br-timeline" id="brTimeline"></div>
        </div>
    </div>
</div>

<script>
// Filter toggle
document.getElementById('filterSelect')?.addEventListener('change', function() {
    const show = this.value === 'custom';
    document.getElementById('customDateFrom').style.display = show ? 'flex' : 'none';
    document.getElementById('customDateTo').style.display   = show ? 'flex' : 'none';
});

const brCustomersData = @json($customersWithRatings);

function openBrModal(customerId) {
    const d = brCustomersData.find(c => c.customer.id === customerId);
    if (!d) return;

    document.getElementById('brModalName').textContent = d.customer.name;
    document.getElementById('brModalAvg').textContent  = `(${d.average_rating}/5 avg)`;

    const timeline = document.getElementById('brTimeline');
    timeline.innerHTML = '';

    d.all_ratings.forEach(rating => {
        const item = document.createElement('div');
        item.className = 'br-timeline-item';

        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            starsHtml += `<i class="bi bi-star${i <= rating.rating ? '-fill' : ''}${i > rating.rating ? ' empty' : ''}"></i>`;
        }

        const dt = new Date(rating.created_at);
        const dateStr = dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        const timeStr = dt.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });

        let badges = '';
        if (rating.laundry_id) badges += `<span class="br-tl-badge"><i class="bi bi-basket"></i> Laundry #${rating.laundry_id}</span>`;
        badges += `<span class="br-tl-badge"><i class="bi bi-geo-alt-fill"></i> ${rating.branch.name}</span>`;

        item.innerHTML = `
            <div class="br-tl-header">
                <div class="br-tl-stars">${starsHtml}</div>
                <div class="br-tl-date">${dateStr} · ${timeStr}</div>
            </div>
            <div class="br-tl-badges">${badges}</div>
            <div class="${rating.comment ? 'br-tl-comment' : 'br-tl-no-comment'}">
                ${rating.comment ? `"${rating.comment}"` : 'No comment provided'}
            </div>
        `;

        timeline.appendChild(item);
    });

    document.getElementById('brModal').classList.add('active');
}

function closeBrModal() {
    document.getElementById('brModal').classList.remove('active');
}

document.getElementById('brModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeBrModal();
});

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeBrModal(); });
</script>
@endsection
