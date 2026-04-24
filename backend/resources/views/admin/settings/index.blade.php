@use('App\Models\SystemSetting')
@use('Illuminate\Support\Facades\File')
@use('Illuminate\Support\Facades\Storage')
@extends('admin.layouts.app')

@section('page-title', 'System Settings')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
/* ============================================================
   SYSTEM SETTINGS — Clean Precision Admin UI
   Full light/dark mode via CSS custom properties ONLY.
   No inline styles, no body.dark-mode class hacks.
   ============================================================ */

/* ── Theme tokens ──────────────────────────────────────── */
:root {
    --ss-bg:         #F1F4F9;
    --ss-surface:    #FFFFFF;
    --ss-surface-2:  #F7F9FC;
    --ss-surface-3:  #EEF2F8;
    --ss-border:     #E3E8F0;
    --ss-border-2:   #C8D3E6;

    --ss-text-1:     #0D1526;
    --ss-text-2:     #3A4C6B;
    --ss-text-3:     #7A8FAD;
    --ss-text-4:     #B8C5D9;

    --ss-brand:      #3D3B6B;
    --ss-brand-mid:  #5553A0;
    --ss-brand-soft: rgba(61,59,107,0.09);
    --ss-brand-border: rgba(61,59,107,0.18);

    --ss-blue:       #2563EB;
    --ss-blue-soft:  rgba(37,99,235,0.09);
    --ss-green:      #059669;
    --ss-green-soft: rgba(5,150,105,0.09);
    --ss-amber:      #D97706;
    --ss-amber-soft: rgba(217,119,6,0.10);
    --ss-red:        #DC2626;
    --ss-red-soft:   rgba(220,38,38,0.09);
    --ss-cyan:       #0891B2;
    --ss-cyan-soft:  rgba(8,145,178,0.09);

    --ss-shadow-xs:  0 1px 2px rgba(13,21,38,0.05);
    --ss-shadow-sm:  0 2px 8px rgba(13,21,38,0.08);
    --ss-shadow-md:  0 6px 24px rgba(13,21,38,0.10);

    --ss-radius:     16px;
    --ss-radius-sm:  12px;
    --ss-radius-xs:  8px;
    --ss-t:          0.18s ease;
}

[data-theme="dark"] {
    --ss-bg:         #080C14;
    --ss-surface:    #0F1623;
    --ss-surface-2:  #141D2E;
    --ss-surface-3:  #192336;
    --ss-border:     #1E2D45;
    --ss-border-2:   #26384F;

    --ss-text-1:     #E2EAF5;
    --ss-text-2:     #7A90AD;
    --ss-text-3:     #435165;
    --ss-text-4:     #263344;

    --ss-brand:      #7B79C8;
    --ss-brand-mid:  #9A98E0;
    --ss-brand-soft: rgba(123,121,200,0.12);
    --ss-brand-border: rgba(123,121,200,0.22);

    --ss-blue:       #60A5FA;
    --ss-blue-soft:  rgba(96,165,250,0.10);
    --ss-green:      #34D399;
    --ss-green-soft: rgba(52,211,153,0.10);
    --ss-amber:      #FBBF24;
    --ss-amber-soft: rgba(251,191,36,0.10);
    --ss-red:        #F87171;
    --ss-red-soft:   rgba(248,113,113,0.10);
    --ss-cyan:       #22D3EE;
    --ss-cyan-soft:  rgba(34,211,238,0.10);

    --ss-shadow-xs:  0 1px 2px rgba(0,0,0,0.30);
    --ss-shadow-sm:  0 2px 8px rgba(0,0,0,0.40);
    --ss-shadow-md:  0 6px 24px rgba(0,0,0,0.50);
}

/* ── Base ───────────────────────────────────────────────── */
.ss-page {
    font-family: 'Geist', system-ui, sans-serif;
    background: var(--ss-bg);
    min-height: 100vh;
    padding: 0 0 4rem;
    color: var(--ss-text-1);
}

/* ── Page header ────────────────────────────────────────── */
.ss-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.ss-page-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--ss-text-1);
    letter-spacing: -0.03em;
    margin: 0 0 0.2rem;
}

.ss-page-sub {
    font-size: 0.825rem;
    color: var(--ss-text-3);
    margin: 0;
}

.ss-save-indicator {
    font-size: 0.75rem;
    color: var(--ss-text-3);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

/* ── Layout ─────────────────────────────────────────────── */
.ss-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 1.5rem;
    align-items: start;
}

/* ── Sidebar Nav ────────────────────────────────────────── */
.ss-sidebar {
    background: var(--ss-surface);
    border: 1px solid var(--ss-border);
    border-radius: var(--ss-radius);
    padding: 0.5rem;
    box-shadow: var(--ss-shadow-xs);
    position: sticky;
    top: 20px;
}

.ss-nav-item {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.75rem 1rem;
    border-radius: var(--ss-radius-sm);
    cursor: pointer;
    border: none;
    background: transparent;
    width: 100%;
    text-align: left;
    transition: all var(--ss-t);
    color: var(--ss-text-2);
    margin-bottom: 2px;
    font-family: 'Geist', sans-serif;
}

.ss-nav-item:last-child { margin-bottom: 0; }

.ss-nav-item:hover:not(.active) {
    background: var(--ss-surface-2);
    color: var(--ss-text-1);
}

.ss-nav-item.active {
    background: var(--ss-brand-soft);
    color: var(--ss-brand);
    border: 1px solid var(--ss-brand-border);
}

.ss-nav-ico {
    width: 34px; height: 34px;
    border-radius: var(--ss-radius-xs);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
    background: var(--ss-surface-3);
    color: var(--ss-text-3);
    transition: all var(--ss-t);
}

.ss-nav-item.active .ss-nav-ico {
    background: var(--ss-brand-soft);
    color: var(--ss-brand);
}

.ss-nav-item:hover:not(.active) .ss-nav-ico {
    background: var(--ss-surface-2);
    color: var(--ss-text-2);
}

.ss-nav-label {
    font-size: 0.825rem;
    font-weight: 600;
    line-height: 1.3;
}

.ss-nav-sublabel {
    font-size: 0.68rem;
    color: var(--ss-text-3);
    font-weight: 400;
    margin-top: 1px;
}

.ss-nav-item.active .ss-nav-sublabel { color: var(--ss-brand); opacity: 0.7; }

/* ── Content Area ───────────────────────────────────────── */
.ss-content {}

/* ── Card ───────────────────────────────────────────────── */
.ss-card {
    background: var(--ss-surface);
    border: 1px solid var(--ss-border);
    border-radius: var(--ss-radius);
    box-shadow: var(--ss-shadow-xs);
    overflow: hidden;
    margin-bottom: 1.25rem;
}

.ss-card-header {
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid var(--ss-border);
    background: var(--ss-surface-2);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.ss-card-header-left {}

.ss-card-title {
    font-size: 0.925rem;
    font-weight: 700;
    color: var(--ss-text-1);
    margin: 0 0 0.2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ss-card-title i { font-size: 0.875rem; }

.ss-card-sub {
    font-size: 0.75rem;
    color: var(--ss-text-3);
    margin: 0;
}

.ss-card-body { padding: 1.5rem; }

/* ── Form controls — all use CSS vars ───────────────────── */
.ss-input,
.ss-textarea,
.ss-select {
    background: var(--ss-surface-2);
    border: 1px solid var(--ss-border);
    border-radius: var(--ss-radius-xs);
    padding: 0.6rem 0.875rem;
    font-size: 0.85rem;
    color: var(--ss-text-1);
    font-family: 'Geist', sans-serif;
    width: 100%;
    outline: none;
    transition: border-color var(--ss-t), box-shadow var(--ss-t), background var(--ss-t);
}

.ss-input:focus, .ss-textarea:focus, .ss-select:focus {
    border-color: var(--ss-brand);
    box-shadow: 0 0 0 3px var(--ss-brand-soft);
    background: var(--ss-surface);
    color: var(--ss-text-1);
}

.ss-input::placeholder, .ss-textarea::placeholder { color: var(--ss-text-4); }

.ss-input-sm { height: 36px; padding: 0 0.75rem; font-size: 0.82rem; }

.ss-textarea { resize: none; line-height: 1.6; }

.ss-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.09em;
    color: var(--ss-text-3);
    margin-bottom: 0.4rem;
}

.ss-form-hint {
    font-size: 0.72rem;
    color: var(--ss-text-3);
    margin-top: 0.35rem;
}

.ss-input-group {
    display: flex;
    align-items: stretch;
}

.ss-input-group .ss-input {
    border-radius: var(--ss-radius-xs) 0 0 var(--ss-radius-xs);
    border-right: none;
}

.ss-input-addon {
    padding: 0.6rem 0.875rem;
    background: var(--ss-surface-3);
    border: 1px solid var(--ss-border);
    color: var(--ss-text-3);
    font-size: 0.82rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    white-space: nowrap;
}

.ss-input-addon.left  { border-radius: var(--ss-radius-xs) 0 0 var(--ss-radius-xs); border-right: none; }
.ss-input-addon.right { border-radius: 0 var(--ss-radius-xs) var(--ss-radius-xs) 0; border-left: none; }

.ss-input-group .ss-input.with-addon-right { border-radius: 0 var(--ss-radius-xs) var(--ss-radius-xs) 0; border-left: none; border-right: 1px solid var(--ss-border); }

/* monospace inputs */
.ss-mono { font-family: 'Geist Mono', monospace; font-size: 0.82rem; }

/* ── Toggle Switch ──────────────────────────────────────── */
.ss-toggle-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.5rem;
    padding: 1rem 1.25rem;
    background: var(--ss-surface-2);
    border: 1px solid var(--ss-border);
    border-radius: var(--ss-radius-sm);
    transition: background var(--ss-t);
}

.ss-toggle-row + .ss-toggle-row { margin-top: 0.75rem; }

.ss-toggle-row:hover { background: var(--ss-surface-3); }

.ss-toggle-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--ss-text-1);
    margin-bottom: 0.2rem;
}

.ss-toggle-sub {
    font-size: 0.75rem;
    color: var(--ss-text-3);
}

/* Custom toggle switch */
.ss-switch { position: relative; display: inline-block; width: 48px; height: 26px; flex-shrink: 0; }
.ss-switch input { opacity: 0; width: 0; height: 0; }
.ss-switch-slider {
    position: absolute; inset: 0;
    background: var(--ss-border-2);
    border-radius: 13px;
    cursor: pointer;
    transition: background 0.2s ease;
}
.ss-switch-slider::before {
    content: '';
    position: absolute;
    width: 20px; height: 20px;
    left: 3px; top: 3px;
    background: white;
    border-radius: 50%;
    transition: transform 0.2s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.ss-switch input:checked + .ss-switch-slider { background: var(--ss-brand); }
.ss-switch input:checked + .ss-switch-slider::before { transform: translateX(22px); }

/* ── Hours Table ────────────────────────────────────────── */
.ss-hours-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.ss-hours-table thead tr {
    background: var(--ss-surface-2);
}

.ss-hours-table thead th {
    padding: 0.625rem 1rem;
    font-size: 0.63rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--ss-text-3);
    border-bottom: 1px solid var(--ss-border);
    background: var(--ss-surface-2);
}

.ss-hours-table tbody tr {
    transition: background var(--ss-t);
    background: var(--ss-surface);
}

.ss-hours-table tbody tr:hover { background: var(--ss-surface-2); }

.ss-hours-table tbody tr.closed-row { opacity: 0.45; }

.ss-hours-table tbody td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--ss-border);
    color: var(--ss-text-1);
    vertical-align: middle;
}

.ss-hours-table tbody tr:last-child td { border-bottom: none; }

.ss-day-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--ss-text-1);
    text-transform: capitalize;
}

.ss-time-input {
    height: 36px;
    padding: 0 0.75rem;
    background: var(--ss-surface-2);
    border: 1px solid var(--ss-border);
    border-radius: var(--ss-radius-xs);
    font-size: 0.82rem;
    color: var(--ss-text-1);
    font-family: 'Geist Mono', monospace;
    outline: none;
    width: 130px;
    transition: border-color var(--ss-t), box-shadow var(--ss-t);
}

.ss-time-input:focus {
    border-color: var(--ss-brand);
    box-shadow: 0 0 0 3px var(--ss-brand-soft);
}

.ss-time-input:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ── Buttons ────────────────────────────────────────────── */
.ss-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    height: 36px;
    padding: 0 1rem;
    border-radius: var(--ss-radius-xs);
    font-size: 0.8rem;
    font-weight: 600;
    font-family: 'Geist', sans-serif;
    cursor: pointer;
    border: none;
    text-decoration: none;
    white-space: nowrap;
    transition: all var(--ss-t);
}

.ss-btn-primary {
    background: var(--ss-brand);
    color: #fff;
    border: 1px solid transparent;
}

.ss-btn-primary:hover { opacity: 0.88; transform: translateY(-1px); box-shadow: var(--ss-shadow-sm); color: #fff; text-decoration: none; }

.ss-btn-ghost {
    background: transparent;
    color: var(--ss-text-2);
    border: 1.5px solid var(--ss-border);
}

.ss-btn-ghost:hover { background: var(--ss-surface-2); border-color: var(--ss-border-2); color: var(--ss-text-1); text-decoration: none; }

.ss-btn-danger {
    background: var(--ss-red-soft);
    color: var(--ss-red);
    border: 1px solid rgba(220,38,38,0.2);
}

.ss-btn-success {
    background: var(--ss-green-soft);
    color: var(--ss-green);
    border: 1px solid rgba(5,150,105,0.2);
}

.ss-btn-lg {
    height: 44px;
    padding: 0 1.5rem;
    font-size: 0.9rem;
    border-radius: var(--ss-radius-sm);
}

/* ── Health Status Cards ────────────────────────────────── */
.ss-health-card {
    background: var(--ss-surface-2);
    border: 1px solid var(--ss-border);
    border-radius: var(--ss-radius-sm);
    padding: 1.5rem;
    text-align: center;
    transition: all var(--ss-t);
}

.ss-health-card:hover { box-shadow: var(--ss-shadow-sm); }

.ss-health-ico {
    font-size: 2.25rem;
    margin-bottom: 0.75rem;
    display: block;
}

.ss-health-label {
    font-size: 0.825rem;
    font-weight: 700;
    color: var(--ss-text-1);
    margin-bottom: 0.5rem;
}

.ss-health-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
}

.ss-health-badge.ok  { background: var(--ss-green-soft); color: var(--ss-green); border: 1px solid rgba(5,150,105,0.2); }
.ss-health-badge.err { background: var(--ss-red-soft);   color: var(--ss-red);   border: 1px solid rgba(220,38,38,0.2); }
.ss-health-badge.warn{ background: var(--ss-amber-soft); color: var(--ss-amber); border: 1px solid rgba(217,119,6,0.2); }

/* ── Backup table ───────────────────────────────────────── */
.ss-table {
    width: 100%;
    border-collapse: collapse;
}

.ss-table thead th {
    padding: 0.625rem 1.25rem;
    font-size: 0.63rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--ss-text-3);
    background: var(--ss-surface-2);
    border-bottom: 1px solid var(--ss-border);
}

.ss-table tbody td {
    padding: 0.875rem 1.25rem;
    border-bottom: 1px solid var(--ss-border);
    color: var(--ss-text-1);
    font-size: 0.85rem;
    background: var(--ss-surface);
}

.ss-table tbody tr:last-child td { border-bottom: none; }
.ss-table tbody tr:hover td { background: var(--ss-surface-2); }

.ss-file-badge {
    font-family: 'Geist Mono', monospace;
    font-size: 0.72rem;
    color: var(--ss-text-2);
    background: var(--ss-surface-3);
    border: 1px solid var(--ss-border);
    padding: 0.2rem 0.55rem;
    border-radius: 5px;
    display: inline-block;
}

.ss-size-badge {
    font-family: 'Geist Mono', monospace;
    font-size: 0.72rem;
    color: var(--ss-text-3);
    background: var(--ss-surface-3);
    border: 1px solid var(--ss-border);
    padding: 0.18rem 0.5rem;
    border-radius: 999px;
}

/* ── Alert ──────────────────────────────────────────────── */
.ss-alert {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-radius: var(--ss-radius-sm);
    font-size: 0.825rem;
    margin-bottom: 1.5rem;
}

.ss-alert.success {
    background: var(--ss-green-soft);
    border: 1px solid rgba(5,150,105,0.2);
    color: var(--ss-green);
}

.ss-alert.info {
    background: var(--ss-blue-soft);
    border: 1px solid rgba(37,99,235,0.18);
    color: var(--ss-blue);
}

.ss-alert.warning {
    background: var(--ss-amber-soft);
    border: 1px solid rgba(217,119,6,0.2);
    color: var(--ss-amber);
}

.ss-alert-body { flex: 1; color: var(--ss-text-1); }

.ss-alert-close {
    background: transparent;
    border: none;
    cursor: pointer;
    color: var(--ss-text-3);
    font-size: 0.9rem;
    padding: 0;
    line-height: 1;
    flex-shrink: 0;
    transition: color var(--ss-t);
}
.ss-alert-close:hover { color: var(--ss-text-1); }

/* ── Maintenance banner ─────────────────────────────────── */
.ss-maintenance-banner {
    background: var(--ss-amber-soft);
    border: 2px solid rgba(217,119,6,0.25);
    border-radius: var(--ss-radius-sm);
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.ss-maintenance-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--ss-text-1);
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ss-maintenance-title i { color: var(--ss-amber); }

.ss-maintenance-sub { font-size: 0.78rem; color: var(--ss-text-3); }

.ss-maintenance-status {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    flex-shrink: 0;
}

.ss-status-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

.ss-status-label.active  { color: var(--ss-red);   }
.ss-status-label.inactive{ color: var(--ss-green);  }

/* ── Scheduled window box ───────────────────────────────── */
.ss-schedule-box {
    background: var(--ss-surface-2);
    border: 1px solid var(--ss-border);
    border-radius: var(--ss-radius-sm);
    padding: 1.25rem;
}

/* ── Sticky footer ──────────────────────────────────────── */
.ss-footer {
    position: sticky;
    bottom: 0;
    background: var(--ss-surface);
    border-top: 1px solid var(--ss-border);
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    z-index: 10;
    margin-top: 1rem;
    border-radius: 0 0 var(--ss-radius) var(--ss-radius);
}

/* ── Tab pane animation ─────────────────────────────────── */
.tab-pane { animation: ssTabIn 0.25s ease both; }
@keyframes ssTabIn {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 992px) {
    .ss-layout { grid-template-columns: 1fr; }
    .ss-sidebar { position: static; }
    .ss-sidebar .nav { flex-direction: row; flex-wrap: wrap; gap: 4px; }
    .ss-nav-item { width: auto; flex: 0 0 auto; }
    .ss-nav-sublabel { display: none; }
}

@media (max-width: 576px) {
    .ss-card-body { padding: 1rem; }
    .ss-card-header { padding: 0.875rem 1rem; }
    .ss-hours-table { display: block; overflow-x: auto; }
}
</style>

<div class="ss-page">

    {{-- ── PAGE HEADER ─────────────────────────────────── --}}
    <div class="ss-page-header">
        <div>
            <h1 class="ss-page-title">System Settings</h1>
            <p class="ss-page-sub">Configure global rules, FCM notifications, and system maintenance.</p>
        </div>
        <div id="save-indicator" class="ss-save-indicator d-none">
            <i class="bi bi-clock-history"></i> Last saved: Just now
        </div>
    </div>

    {{-- ── SUCCESS ALERT ───────────────────────────────── --}}
    @if(session('success'))
        <div class="ss-alert success" id="successAlert">
            <i class="bi bi-check-circle-fill" style="font-size:1.1rem;color:var(--ss-green);flex-shrink:0;"></i>
            <div class="ss-alert-body">{{ session('success') }}</div>
            <button class="ss-alert-close" onclick="this.closest('.ss-alert').remove()"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    {{-- ── MAIN LAYOUT ──────────────────────────────────── --}}
    <div class="ss-layout">

        {{-- ── SIDEBAR NAV ──────────────────────────────── --}}
        <div class="ss-sidebar">
            <div class="nav flex-column" id="settingsTabs" role="tablist">
                @php
                    $navItems = [
                        ['target'=>'hours',        'icon'=>'bi-clock',          'label'=>'Business Hours',   'sub'=>'Operating Schedule'],
                        ['target'=>'pickup',        'icon'=>'bi-truck',          'label'=>'Pickup & Delivery','sub'=>'Service Settings'],
                        ['target'=>'unclaimed',     'icon'=>'bi-clock-history',  'label'=>'Unclaimed Rules',  'sub'=>'Thresholds & Policy'],
                        ['target'=>'notifications', 'icon'=>'bi-megaphone',      'label'=>'Notifications',    'sub'=>'FCM & Push Alerts'],
                        ['target'=>'maintenance',   'icon'=>'bi-tools',          'label'=>'Maintenance Mode', 'sub'=>'System Downtime'],
                        ['target'=>'status',        'icon'=>'bi-heart-pulse',    'label'=>'System Health',    'sub'=>'Server & DB Status'],
                        ['target'=>'backup',        'icon'=>'bi-database-up',    'label'=>'Backup & Data',    'sub'=>'Export & Security'],
                    ];
                @endphp
                @foreach($navItems as $i => $item)
                    <button class="ss-nav-item {{ $i === 0 ? 'active' : '' }}"
                            data-bs-toggle="pill"
                            data-bs-target="#{{ $item['target'] }}"
                            type="button"
                            role="tab">
                        <div class="ss-nav-ico">
                            <i class="bi {{ $item['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="ss-nav-label">{{ $item['label'] }}</div>
                            <div class="ss-nav-sublabel">{{ $item['sub'] }}</div>
                        </div>
                    </button>
                @endforeach
                <div class="ss-toggle-row">
                    <div>
                        <div class="ss-toggle-label">Require Customer Proof Photo</div>
                        <div class="ss-toggle-sub">Customers must upload a photo of their laundry items when requesting pickup to verify legitimacy.</div>
                    </div>
                    <label class="ss-switch">
                        <input type="checkbox" name="require_customer_proof_photo" {{ SystemSetting::get('require_customer_proof_photo', true) ? 'checked' : '' }}>
                        <span class="ss-switch-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        {{-- ── CONTENT ───────────────────────────────────── --}}
        <div class="ss-content">
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="tab-content">

                    {{-- ═══ BUSINESS HOURS ═══════════════════════════════ --}}
                    <div class="tab-pane fade show active" id="hours" role="tabpanel">
                        <div class="ss-card">
                            <div class="ss-card-header">
                                <div class="ss-card-header-left">
                                    <div class="ss-card-title">
                                        <i class="bi bi-clock" style="color:var(--ss-brand);"></i>
                                        Default Operating Hours
                                    </div>
                                    <p class="ss-card-sub">Applied to all branches on save. Individual branches can override.</p>
                                </div>
                                <button type="button" class="ss-btn ss-btn-ghost" onclick="applyHoursToBranches()">
                                    <i class="bi bi-building"></i> Apply to All Branches
                                </button>
                            </div>
                            <div class="ss-card-body" style="padding:0;">
                                @php
                                    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                                    $defaultOpen  = ['monday'=>'07:00','tuesday'=>'07:00','wednesday'=>'07:00','thursday'=>'07:00','friday'=>'07:00','saturday'=>'08:00','sunday'=>'08:00'];
                                    $defaultClose = ['monday'=>'20:00','tuesday'=>'20:00','wednesday'=>'20:00','thursday'=>'20:00','friday'=>'20:00','saturday'=>'18:00','sunday'=>'14:00'];
                                    $defaultClosed = ['sunday'];
                                @endphp
                                <div style="overflow-x:auto;">
                                    <table class="ss-hours-table">
                                        <thead>
                                            <tr>
                                                <th style="padding-left:1.5rem;width:140px;">Day</th>
                                                <th>Opens At</th>
                                                <th>Closes At</th>
                                                <th style="text-align:center;width:100px;">Open</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($days as $day)
                                                @php $isOpen = SystemSetting::get("hours_{$day}_open", !in_array($day, $defaultClosed)); @endphp
                                                <tr class="{{ !$isOpen ? 'closed-row' : '' }}">
                                                    <td style="padding-left:1.5rem;">
                                                        <span class="ss-day-label">{{ ucfirst($day) }}</span>
                                                    </td>
                                                    <td>
                                                        <input type="time" name="hours_{{ $day }}_start"
                                                               class="ss-time-input"
                                                               value="{{ SystemSetting::get("hours_{$day}_start", $defaultOpen[$day]) }}"
                                                               {{ !$isOpen ? 'disabled' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="time" name="hours_{{ $day }}_end"
                                                               class="ss-time-input"
                                                               value="{{ SystemSetting::get("hours_{$day}_end", $defaultClose[$day]) }}"
                                                               {{ !$isOpen ? 'disabled' : '' }}>
                                                    </td>
                                                    <td style="text-align:center;">
                                                        <label class="ss-switch">
                                                            <input type="checkbox"
                                                                   name="hours_{{ $day }}_open"
                                                                   class="hours-toggle"
                                                                   data-day="{{ $day }}"
                                                                   {{ $isOpen ? 'checked' : '' }}>
                                                            <span class="ss-switch-slider"></span>
                                                        </label>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ PICKUP & DELIVERY ════════════════════════════ --}}
                    <div class="tab-pane fade" id="pickup" role="tabpanel">

                        {{-- Service availability --}}
                        <div class="ss-card">
                            <div class="ss-card-header">
                                <div class="ss-card-title">
                                    <i class="bi bi-toggles" style="color:var(--ss-brand);"></i>
                                    Service Availability
                                </div>
                            </div>
                            <div class="ss-card-body">
                                <div class="ss-toggle-row">
                                    <div>
                                        <div class="ss-toggle-label">Enable Pickup Service</div>
                                        <div class="ss-toggle-sub">Allow customers to request laundry pickup via the mobile app.</div>
                                    </div>
                                    <label class="ss-switch">
                                        <input type="checkbox" name="enable_pickup" {{ SystemSetting::get('enable_pickup', true) ? 'checked' : '' }}>
                                        <span class="ss-switch-slider"></span>
                                    </label>
                                </div>
                                <div class="ss-toggle-row">
                                    <div>
                                        <div class="ss-toggle-label">Enable Delivery Service</div>
                                        <div class="ss-toggle-sub">Allow staff to deliver completed laundry to customer addresses.</div>
                                    </div>
                                    <label class="ss-switch">
                                        <input type="checkbox" name="enable_delivery" {{ SystemSetting::get('enable_delivery', true) ? 'checked' : '' }}>
                                        <span class="ss-switch-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Default fees --}}
                        <div class="ss-card">
                            <div class="ss-card-header">
                                <div>
                                    <div class="ss-card-title">
                                        <i class="bi bi-cash-stack" style="color:var(--ss-green);"></i>
                                        Default Fees
                                    </div>
                                    <p class="ss-card-sub">Used as defaults when creating a new pickup or delivery order.</p>
                                </div>
                            </div>
                            <div class="ss-card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="ss-label">Default Pickup Fee</label>
                                        <div class="ss-input-group">
                                            <span class="ss-input-addon left">₱</span>
                                            <input type="number" name="default_pickup_fee" class="ss-input with-addon-right" value="{{ SystemSetting::get('default_pickup_fee', 50) }}" min="0" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ss-label">Default Delivery Fee</label>
                                        <div class="ss-input-group">
                                            <span class="ss-input-addon left">₱</span>
                                            <input type="number" name="default_delivery_fee" class="ss-input with-addon-right" value="{{ SystemSetting::get('default_delivery_fee', 50) }}" min="0" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ss-label">Max Service Radius</label>
                                        <div class="ss-input-group">
                                            <input type="number" name="max_service_radius_km" class="ss-input" style="border-radius:var(--ss-radius-xs) 0 0 var(--ss-radius-xs);" value="{{ SystemSetting::get('max_service_radius_km', 10) }}" min="1">
                                            <span class="ss-input-addon right">km</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Schedule rules --}}
                        <div class="ss-card">
                            <div class="ss-card-header">
                                <div class="ss-card-title">
                                    <i class="bi bi-calendar-check" style="color:var(--ss-cyan);"></i>
                                    Booking Schedule Rules
                                </div>
                            </div>
                            <div class="ss-card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="ss-label">Minimum Advance Booking</label>
                                        <div class="ss-input-group">
                                            <input type="number" name="pickup_advance_days_min" class="ss-input" style="border-radius:var(--ss-radius-xs) 0 0 var(--ss-radius-xs);text-align:center;" value="{{ SystemSetting::get('pickup_advance_days_min', 1) }}" min="0">
                                            <span class="ss-input-addon right">Day(s) ahead</span>
                                        </div>
                                        <p class="ss-form-hint">Minimum days in advance a customer must book.</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ss-label">Maximum Advance Booking</label>
                                        <div class="ss-input-group">
                                            <input type="number" name="pickup_advance_days_max" class="ss-input" style="border-radius:var(--ss-radius-xs) 0 0 var(--ss-radius-xs);text-align:center;" value="{{ SystemSetting::get('pickup_advance_days_max', 7) }}" min="1">
                                            <span class="ss-input-addon right">Day(s) ahead</span>
                                        </div>
                                        <p class="ss-form-hint">How far in advance a pickup can be scheduled.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ UNCLAIMED RULES ══════════════════════════════ --}}
                    <div class="tab-pane fade" id="unclaimed" role="tabpanel">
                        <div class="ss-card">
                            <div class="ss-card-header">
                                <div class="ss-card-title" style="color:var(--ss-red);">
                                    <i class="bi bi-shield-exclamation"></i>
                                    Inventory Retention
                                </div>
                            </div>
                            <div class="ss-card-body">
                                <div class="row align-items-center mb-4">
                                    <div class="col-md-8">
                                        <div class="ss-toggle-label">Auto-Disposal Threshold</div>
                                        <p class="ss-toggle-sub mt-1 mb-0">How many days should an order remain unclaimed before marking for disposal?</p>
                                    </div>
                                    <div class="col-md-4 mt-3 mt-md-0">
                                        <div class="ss-input-group">
                                            <input type="number" name="disposal_threshold_days" class="ss-input" style="text-align:center;border-radius:var(--ss-radius-xs) 0 0 var(--ss-radius-xs);" value="{{ SystemSetting::get('disposal_threshold_days', 30) }}">
                                            <span class="ss-input-addon right">Days</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="ss-toggle-row">
                                    <div>
                                        <div class="ss-toggle-label">Automated Retention Alerts</div>
                                        <div class="ss-toggle-sub">Send daily reminders to customers with overdue laundry.</div>
                                    </div>
                                    <label class="ss-switch">
                                        <input type="checkbox" name="enable_unclaimed_notifications" {{ SystemSetting::get('enable_unclaimed_notifications') ? 'checked' : '' }}>
                                        <span class="ss-switch-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ NOTIFICATIONS ════════════════════════════════ --}}
                    <div class="tab-pane fade" id="notifications" role="tabpanel">
                        <div class="ss-card">
                            <div class="ss-card-header">
                                <div class="ss-card-title">
                                    <i class="bi bi-cloud-check" style="color:var(--ss-blue);"></i>
                                    Push Notifications (Firebase)
                                </div>
                                <p class="ss-card-sub">Firebase Cloud Messaging is configured via service account file.</p>
                            </div>
                            <div class="ss-card-body">
                                <div class="ss-alert info">
                                    <i class="bi bi-info-circle-fill" style="color:var(--ss-blue);flex-shrink:0;font-size:1.2rem;"></i>
                                    <div class="ss-alert-body">
                                        <strong>Firebase Configuration</strong><br>
                                        Push notifications are configured using Firebase Admin SDK with a service account file.<br>
                                        <strong>Location:</strong> <code style="background:var(--ss-surface-3);padding:0.2rem 0.5rem;border-radius:4px;font-family:var(--ss-mono);font-size:0.8rem;">storage/app/firebase/service-account.json</code><br>
                                        <strong>Status:</strong> 
                                        @if(File::exists(storage_path('app/firebase/service-account.json')))
                                            <span class="ss-health-badge ok" style="margin-top:0.5rem;">
                                                <i class="bi bi-check-circle-fill"></i>
                                                Service Account Configured
                                            </span>
                                        @else
                                            <span class="ss-health-badge err" style="margin-top:0.5rem;">
                                                <i class="bi bi-x-circle-fill"></i>
                                                Service Account Missing
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <h6 class="ss-label mb-3">
                                        <i class="bi bi-file-earmark-code me-2"></i>Service Account File
                                    </h6>
                                    <p class="small" style="color:var(--ss-text-3);margin-bottom:1rem;">
                                        To update the Firebase configuration, replace the service account JSON file at the location above.
                                        You can download this file from your Firebase Console → Project Settings → Service Accounts.
                                    </p>
                                    
                                    @if(File::exists(storage_path('app/firebase/service-account.json')))
                                        <div class="ss-toggle-row" style="background:var(--ss-success-soft);border-color:rgba(5,150,105,0.2);">
                                            <div>
                                                <div class="ss-toggle-label" style="color:var(--ss-green);">
                                                    <i class="bi bi-check-circle-fill me-2"></i>Firebase Connected
                                                </div>
                                                <div class="ss-toggle-sub">Push notifications are ready to send to mobile devices.</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="ss-toggle-row" style="background:var(--ss-red-soft);border-color:rgba(220,38,38,0.2);">
                                            <div>
                                                <div class="ss-toggle-label" style="color:var(--ss-red);">
                                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Firebase Not Configured
                                                </div>
                                                <div class="ss-toggle-sub">Upload service-account.json to enable push notifications.</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ MAINTENANCE MODE ═════════════════════════════ --}}
                    <div class="tab-pane fade" id="maintenance" role="tabpanel">
                        <div class="ss-card">
                            <div class="ss-card-header">
                                <div class="ss-card-title" style="color:var(--ss-amber);">
                                    <i class="bi bi-tools"></i>
                                    Maintenance Mode Control
                                </div>
                                <p class="ss-card-sub">Put the system in maintenance mode to prevent customer access during updates.</p>
                            </div>
                            <div class="ss-card-body">

                                {{-- Main toggle banner --}}
                                <div class="ss-maintenance-banner">
                                    <div>
                                        <div class="ss-maintenance-title">
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                            Maintenance Mode Status
                                        </div>
                                        <div class="ss-maintenance-sub">When enabled, only admins can access the system. Staff and customers will see the maintenance message.</div>
                                    </div>
                                    <div class="ss-maintenance-status">
                                        <span class="ss-status-label {{ SystemSetting::get('maintenance_mode', false) ? 'active' : 'inactive' }}" id="maintenanceLabel">
                                            {{ SystemSetting::get('maintenance_mode', false) ? 'ACTIVE' : 'INACTIVE' }}
                                        </span>
                                        <label class="ss-switch">
                                            <input type="checkbox" name="maintenance_mode" id="maintenanceToggle" {{ SystemSetting::get('maintenance_mode', false) ? 'checked' : '' }}>
                                            <span class="ss-switch-slider"></span>
                                        </label>
                                    </div>
                                </div>

                                {{-- Customer message --}}
                                <div class="mb-4">
                                    <label class="ss-label">
                                        <i class="bi bi-chat-left-text me-1"></i> Customer Message
                                    </label>
                                    <textarea name="maintenance_message" class="ss-textarea" rows="4" placeholder="Enter the message customers will see during maintenance…">{{ SystemSetting::get('maintenance_message', 'We are currently performing scheduled maintenance. Please check back soon!') }}</textarea>
                                    <p class="ss-form-hint">Displayed to customers and staff when they try to access the system.</p>
                                </div>

                                {{-- Allow admin access --}}
                                <div class="ss-toggle-row mb-4">
                                    <div>
                                        <div class="ss-toggle-label">Allow Admin Access</div>
                                        <div class="ss-toggle-sub">Permit administrators to access the system during maintenance mode.</div>
                                    </div>
                                    <label class="ss-switch">
                                        <input type="checkbox" name="maintenance_allow_admin" {{ SystemSetting::get('maintenance_allow_admin', true) ? 'checked' : '' }}>
                                        <span class="ss-switch-slider"></span>
                                    </label>
                                </div>

                                {{-- Scheduled window --}}
                                <div class="ss-schedule-box">
                                    <div class="ss-card-title mb-3">
                                        <i class="bi bi-calendar-event" style="color:var(--ss-brand);"></i>
                                        Scheduled Maintenance Window
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="ss-label">Start Date & Time</label>
                                            <input type="datetime-local" name="maintenance_start" class="ss-input" value="{{ SystemSetting::get('maintenance_start') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ss-label">End Date & Time</label>
                                            <input type="datetime-local" name="maintenance_end" class="ss-input" value="{{ SystemSetting::get('maintenance_end') }}">
                                        </div>
                                    </div>
                                    <div class="ss-alert info mt-3" style="margin-bottom:0;">
                                        <i class="bi bi-info-circle-fill" style="color:var(--ss-blue);flex-shrink:0;"></i>
                                        <div class="ss-alert-body" style="font-size:.75rem;">Set a scheduled window to automatically enable/disable maintenance mode. Leave empty for manual control only.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SYSTEM HEALTH ════════════════════════════════ --}}
                    <div class="tab-pane fade" id="status" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="ss-health-card">
                                    <i class="ss-health-ico bi bi-database-fill-check" style="color:{{ $health['database'] ? 'var(--ss-green)' : 'var(--ss-red)' }};"></i>
                                    <div class="ss-health-label">Database</div>
                                    <span class="ss-health-badge {{ $health['database'] ? 'ok' : 'err' }}">
                                        <i class="bi bi-{{ $health['database'] ? 'check-circle-fill' : 'x-circle-fill' }}"></i>
                                        {{ $health['database'] ? 'Healthy & Connected' : 'Connection Error' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="ss-health-card">
                                    <i class="ss-health-ico bi bi-broadcast-pin" style="color:{{ $health['fcm'] ? 'var(--ss-green)' : 'var(--ss-amber)' }};"></i>
                                    <div class="ss-health-label">FCM Status</div>
                                    <span class="ss-health-badge {{ $health['fcm'] ? 'ok' : 'warn' }}">
                                        <i class="bi bi-{{ $health['fcm'] ? 'check-circle-fill' : 'exclamation-triangle-fill' }}"></i>
                                        {{ $health['fcm'] ? 'Push Engine Ready' : 'Service Account Missing' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ BACKUP ════════════════════════════════════════ --}}
                    <div class="tab-pane fade" id="backup" role="tabpanel">
                        <div class="ss-card">
                            <div class="ss-card-header">
                                <div class="ss-card-title">
                                    <i class="bi bi-database-up" style="color:var(--ss-brand);"></i>
                                    Available Snapshots
                                </div>
                                <button type="button" class="ss-btn ss-btn-primary" onclick="generateBackup()">
                                    <i class="bi bi-plus-lg"></i> New Backup
                                </button>
                            </div>
                            <div style="overflow-x:auto;">
                                <table class="ss-table">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:1.5rem;">File ID</th>
                                            <th>Size</th>
                                            <th>Created</th>
                                            <th style="text-align:right;padding-right:1.5rem;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $backupPath  = storage_path('app/backups');
                                            $backupFiles = File::exists($backupPath)
                                                ? collect(File::files($backupPath))->sortByDesc(fn($f) => $f->getMTime())
                                                : collect();
                                        @endphp
                                        @forelse($backupFiles as $file)
                                            <tr>
                                                <td style="padding-left:1.5rem;">
                                                    <span class="ss-file-badge">{{ $file->getFilename() }}</span>
                                                </td>
                                                <td>
                                                    <span class="ss-size-badge">{{ number_format($file->getSize() / 1024, 1) }} KB</span>
                                                </td>
                                                <td style="color:var(--ss-text-3);font-size:.8rem;">
                                                    {{ date('M j, Y H:i', $file->getMTime()) }}
                                                </td>
                                                <td style="text-align:right;padding-right:1.5rem;">
                                                    <a href="{{ route('admin.settings.download-backup', $file->getFilename()) }}" class="ss-btn ss-btn-ghost" style="height:32px;padding:0 .75rem;font-size:.75rem;">
                                                        <i class="bi bi-download"></i> Download
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" style="text-align:center;padding:3rem;color:var(--ss-text-3);font-size:.85rem;">
                                                    <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                                                    No backup history available.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>{{-- /.tab-content --}}

                {{-- ── STICKY FOOTER ────────────────────────────── --}}
                <div class="ss-footer">
                    <button type="reset" class="ss-btn ss-btn-ghost ss-btn-lg">
                        Discard Changes
                    </button>
                    <button type="submit" class="ss-btn ss-btn-primary ss-btn-lg">
                        <i class="bi bi-check-lg"></i> Save Settings
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
// ── Sidebar pill sync ──────────────────────────────────────────
document.querySelectorAll('.ss-nav-item').forEach(btn => {
    btn.addEventListener('show.bs.tab', function () {
        document.querySelectorAll('.ss-nav-item').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
});

// ── Business hours toggle ──────────────────────────────────────
document.querySelectorAll('.hours-toggle').forEach(toggle => {
    toggle.addEventListener('change', function () {
        const day = this.dataset.day;
        const row = this.closest('tr');
        const inputs = row.querySelectorAll('input[type="time"]');
        inputs.forEach(input => input.disabled = !this.checked);
        row.classList.toggle('closed-row', !this.checked);
    });
});

// ── Maintenance label ──────────────────────────────────────────
const maintenanceToggle = document.getElementById('maintenanceToggle');
const maintenanceLabel  = document.getElementById('maintenanceLabel');

if (maintenanceToggle && maintenanceLabel) {
    maintenanceToggle.addEventListener('change', function () {
        maintenanceLabel.textContent = this.checked ? 'ACTIVE' : 'INACTIVE';
        maintenanceLabel.className   = 'ss-status-label ' + (this.checked ? 'active' : 'inactive');
    });
}

// ── Generate backup ────────────────────────────────────────────
function generateBackup() {
    fetch('{{ route("admin.settings.backup") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { alert(data.message); location.reload(); }
        else { alert('Error: ' + data.message); }
    })
    .catch(() => alert('Error creating backup'));
}

// ── Apply hours to branches ────────────────────────────────────
function applyHoursToBranches() {
    if (!confirm('This will update operating hours for ALL branches. Individual branch settings will be overwritten. Continue?')) return;
    fetch('{{ route("admin.settings.apply-hours-to-branches") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => alert(data.success ? data.message : 'Error: ' + data.message))
    .catch(() => alert('Error applying hours to branches'));
}
</script>
@endsection
