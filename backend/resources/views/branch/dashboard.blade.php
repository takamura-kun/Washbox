@extends('branch.layouts.app')

@section('page-title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/leaflet/leaflet.css') }}">
<link rel="stylesheet" href="{{ asset('assets/leaflet/MarkerCluster.css') }}">
<link rel="stylesheet" href="{{ asset('assets/leaflet/MarkerCluster.Default.css') }}">
<style>
    * { box-sizing: border-box; }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 13px;
    }

    /* ── Topbar ── */
    .dash-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        gap: 12px;
    }

    .dash-topbar h1 {
        font-size: 16px;
        font-weight: 600;
        margin: 0;
    }

    .dash-topbar .page-sub {
        font-size: 11px;
        opacity: 0.7;
        margin: 0;
    }

    .search-wrap {
        flex: 1;
        max-width: 340px;
        position: relative;
    }

    .search-wrap .search-input {
        width: 100%;
        padding: 7px 12px 7px 32px;
        font-size: 13px;
        border: 0.5px solid #d1d5db;
        border-radius: 8px;
        transition: border-color 0.2s;
    }

    .search-wrap .search-input:focus {
        outline: none;
        border-color: #9ca3af;
    }

    .search-wrap .search-ico {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-primary, inherit);
        font-size: 13px;
    }

    /* ── KPI Grid ── */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 10px;
    }

    .kcard {
        background: var(--card-bg, #ffffff);
        border: 0.5px solid var(--border-color, rgba(0,0,0,0.08));
        border-radius: 12px;
        padding: 12px 14px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        transition: border-color 0.15s;
        text-decoration: none;
        color: var(--text-primary, inherit);
    }

    .kcard:hover { border-color: rgba(0,0,0,0.15); color: inherit; text-decoration: none; }

    .kcard-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 4px;
    }

    .klabel {
        font-size: 10px;
        font-weight: 500;
        letter-spacing: 0.2px;
        opacity: 0.7;
    }

    .kval {
        font-size: 20px;
        font-weight: 600;
        line-height: 1.2;
    }

    .kicon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    .kbadge {
        font-size: 10px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 6px;
        border-radius: 99px;
    }

    .kbadge-success { background: #d1fae5; color: #065f46; }
    .kbadge-danger  { background: #fee2e2; color: #991b1b; }
    .kbadge-warning { background: #fef3c7; color: #92400e; }
    .kbadge-neutral { background: #f3f4f6; color: #374151; }

    /* ── Main Grid ── */
    .main-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 220px;
        gap: 10px;
        margin-bottom: 10px;
    }

    .left-col { display: flex; flex-direction: column; gap: 10px; }

    /* ── Action Cards ── */
    .actions-row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .acard {
        background: var(--card-bg, #ffffff);
        border: 0.5px solid var(--border-color, rgba(0,0,0,0.08));
        border-radius: 12px;
        padding: 12px;
        cursor: pointer;
        transition: border-color 0.15s;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        color: var(--text-primary, inherit);
    }

    .acard:hover { border-color: rgba(0,0,0,0.15); }

    .aico {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .acard .atitle { font-size: 12px; font-weight: 600; }
    .acard .adesc  { font-size: 10px; opacity: 0.7; margin-top: 2px; }

    /* ── Panel (generic card) ── */
    .panel {
        background: var(--card-bg, #ffffff);
        border: 0.5px solid var(--border-color, rgba(0,0,0,0.08));
        border-radius: 12px;
        overflow: hidden;
    }

    .panel-hd {
        padding: 10px 14px;
        border-bottom: 0.5px solid rgba(0,0,0,0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .panel-hd h2 {
        font-size: 12px;
        font-weight: 600;
        margin: 0;
    }

    .panel-hd a, .panel-hd .panel-link {
        font-size: 10px;
        color: #6b7280;
        text-decoration: none;
        cursor: pointer;
    }

    .panel-hd a:hover, .panel-hd .panel-link:hover { }

    /* ── Pipeline ── */
    .pipe-row {
        padding: 7px 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: background 0.1s;
        text-decoration: none;
        color: inherit;
    }

    .pipe-row:hover { }

    .pipe-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .pipe-label { font-size: 12px; flex: 1; }
    .pipe-count { font-size: 12px; font-weight: 600; margin-right: 8px; }

    .pipe-bar-wrap {
        width: 54px;
        height: 4px;
        background: rgba(0,0,0,0.1);
        border-radius: 2px;
        overflow: hidden;
    }

    .pipe-bar-fill { height: 100%; border-radius: 2px; }

    /* ── Inventory ── */
    .inv-search-wrap {
        padding: 10px 16px;
        border-bottom: 0.5px solid rgba(0,0,0,0.08);
    }

    .inv-search {
        width: 100%;
        padding: 6px 10px;
        font-size: 12px;
        border: 0.5px solid #d1d5db;
        border-radius: 8px;
    }

    .inv-search:focus { outline: none; border-color: #9ca3af; }

    .inv-list { max-height: 260px; overflow-y: auto; }

    .inv-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 7px 14px;
        border-bottom: 0.5px solid rgba(0,0,0,0.06);
        transition: background 0.1s;
    }

    .inv-row:last-child { border-bottom: none; }
    .inv-row:hover { }

    .inv-name {
        font-size: 11px;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .inv-bar-wrap {
        width: 48px;
        height: 4px;

        border-radius: 2px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .inv-bar-fill { height: 100%; border-radius: 2px; }
    .inv-qty { font-size: 11px; font-weight: 600; min-width: 28px; text-align: right; }
    .inv-unit { font-size: 10px; opacity: 0.7; min-width: 28px; }

    .inv-chip {
        font-size: 9px;
        font-weight: 500;
        padding: 2px 6px;
        border-radius: 99px;
        white-space: nowrap;
    }

    /* ── Revenue Chart ── */
    .chart-area { padding: 12px 16px 8px; }

    /* ── Orders Table ── */
    .orders-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    .orders-table thead th {
        padding: 7px 14px;
        text-align: left;
        font-size: 10px;
        font-weight: 600;
        opacity: 0.7;
        background: rgba(0,0,0,0.03);
        letter-spacing: 0.2px;
        text-transform: uppercase;
    }

    .orders-table tbody td {
        padding: 9px 14px;
        border-top: 0.5px solid rgba(0,0,0,0.06);
        color: var(--text-primary, inherit);
        vertical-align: middle;
    }

    .orders-table tbody tr:hover td { }

    .status-pill {
        font-size: 9px;
        font-weight: 600;
        padding: 3px 7px;
        border-radius: 99px;
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .s-completed { background: #d1fae5; color: #065f46; }
    .s-ready     { background: #fef3c7; color: #92400e; }
    .s-processing{ background: #dbeafe; color: #1e40af; }
    .s-received  { background: #f3f4f6; color: #374151; }
    .s-paid      { background: #d1fae5; color: #065f46; }

    .btn-outline-sm {
        font-size: 10px;
        padding: 3px 7px;
        border: 0.5px solid #d1d5db;
        border-radius: 6px;
        background: transparent;
        color: #6b7280;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-outline-sm:hover { }

    /* ── Bottom Grid ── */
    .bottom-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
    }

    /* ── Logistics Map Styles ── */
    .branch-map-container {
        height: 400px;
        border-radius: 12px;
        overflow: hidden;
    }

    .pickup-list-item {
        transition: background 0.15s;
    }

    .pickup-list-item:hover {
        background: rgba(0,0,0,0.03);
    }

    .sp-pending { background: #fee2e2; color: #991b1b; }
    .sp-accepted { background: #dbeafe; color: #1e40af; }
    .sp-en_route { background: #fef3c7; color: #92400e; }
    .sp-processing { background: #fef3c7; color: #92400e; }
    .sp-ready { background: #d1fae5; color: #065f46; }

    .btn-purple {
        background: #8b5cf6;
        color: white;
        border: none;
    }

    .btn-purple:hover {
        background: #7c3aed;
        color: white;
    }

    .btn-outline-purple {
        border: 1px solid #8b5cf6;
        color: #8b5cf6;
        background: transparent;
    }

    .btn-outline-purple:hover {
        background: #8b5cf6;
        color: white;
    }

    .logistics-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr);
        gap: 10px;
        margin-bottom: 10px;
    }

    /* ── Retail Panel ── */
    .rpval {
        font-size: 20px;
        font-weight: 600;
        padding: 12px 14px 0;
    }

    .rpsub {
        font-size: 10px;
        color: #059669;
        padding: 2px 14px 12px;
        border-bottom: 0.5px solid rgba(0,0,0,0.08);
    }

    .prod-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 14px;
        border-top: 0.5px solid rgba(0,0,0,0.06);
    }

    .prod-name { font-size: 11px; }
    .prod-meta { font-size: 10px; opacity: 0.7; }
    .prod-rev  { font-size: 11px; font-weight: 600; }

    .section-label {
        font-size: 9px;
        font-weight: 600;
        opacity: 0.7;
        letter-spacing: 0.5px;
        padding: 7px 14px 4px;
        text-transform: uppercase;
    }

    /* ── Top Customers ── */
    .cust-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 14px;
        border-top: 0.5px solid rgba(0,0,0,0.06);
        transition: background 0.1s;
    }

    .cust-row:first-child { border-top: none; }
    .cust-row:hover { }

    .cust-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 600;
        flex-shrink: 0;
    }

    .cust-info { flex: 1; min-width: 0; }
    .cust-name { font-size: 11px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cust-orders { font-size: 10px; opacity: 0.7; }
    .cust-spent { font-size: 11px; font-weight: 600; white-space: nowrap; }

    .rank-badge {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        font-weight: 600;
        flex-shrink: 0;
    }

    /* ── Right sidebar column ── */
    .right-sidebar {
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-self: start;
    }

    /* ── Modal overrides ── */
    .modal-content { border: none; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
    .modal-header { border-bottom: 1px solid #e5e7eb; padding: 20px 24px; }
    .modal-title { font-size: 16px; font-weight: 600; }
    .modal-body { padding: 24px; }
    .modal-footer { border-top: 1px solid #e5e7eb; padding: 16px 24px; }
    .form-label { font-size: 13px; font-weight: 500; margin-bottom: 6px; }
    .form-control, .form-select { border: 1px solid #d1d5db; border-radius: 6px; padding: 8px 12px; font-size: 14px; }
    .form-control:focus, .form-select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

    .btn-primary-dash { background: #2563eb; color: white; border: none; border-radius: 6px; padding: 8px 16px; font-size: 13px; font-weight: 500; cursor: pointer; }
    .btn-primary-dash:hover { background: #1d4ed8; }
    .btn-outline-dash { background: white; border: 1px solid #d1d5db; color: #374151; border-radius: 6px; padding: 8px 16px; font-size: 13px; font-weight: 500; cursor: pointer; }
    .btn-outline-dash:hover { }

    /* ── FAB ── */
    .fab {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #111827;
        color: white;
        border: none;
        cursor: move;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        font-size: 18px;
        transition: transform 0.1s ease, box-shadow 0.3s ease;
        user-select: none;
        touch-action: none;
    }

    .fab:hover:not(.dragging) {
        background: #374151;
        transform: scale(1.05);
    }

    .fab.dragging {
        cursor: grabbing;
        transform: scale(1.1);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
    }

    /* ── Feedback Widget ── */
    .feedback-widget {
        position: fixed;
        bottom: 90px;
        right: 24px;
        width: 280px;
        background: var(--card-bg, #ffffff);
        border: 1px solid var(--border-color, rgba(0,0,0,0.08));
        border-radius: 10px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        z-index: 1040;
        overflow: hidden;
        transition: all 0.3s ease;
        user-select: none;
    }

    .feedback-widget.collapsed {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        cursor: pointer;
    }

    .feedback-widget.collapsed .feedback-header {
        padding: 0;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        justify-content: center;
    }

    .feedback-widget.collapsed .feedback-header h3 span,
    .feedback-widget.collapsed .feedback-controls {
        display: none;
    }

    .feedback-widget.collapsed .feedback-body {
        display: none;
    }

    .feedback-widget.minimized {
        width: 180px;
        height: auto;
    }

    .feedback-widget.hidden {
        opacity: 0;
        transform: translateY(20px);
        pointer-events: none;
    }

    .feedback-widget.dragging {
        cursor: grabbing;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
    }

    .feedback-header {
        padding: 10px 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        cursor: move;
        display: flex;
        align-items: center;
        justify-content: space-between;
        user-select: none;
    }

    .feedback-header h3 {
        font-size: 12px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .feedback-controls {
        display: flex;
        gap: 6px;
        align-items: center;
    }

    .feedback-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 20px;
        height: 20px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        transition: background 0.2s;
    }

    .feedback-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .feedback-body {
        padding: 12px;
        max-height: 320px;
        overflow-y: auto;
    }

    .feedback-body.minimized {
        display: none;
    }

    .feedback-item {
        padding: 10px;
        background: rgba(0, 0, 0, 0.02);
        border-radius: 6px;
        margin-bottom: 10px;
        border-left: 2px solid #10b981;
    }

    .feedback-item:last-child {
        margin-bottom: 0;
    }

    .feedback-customer {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .feedback-rating {
        display: flex;
        gap: 1px;
        font-size: 10px;
    }

    .feedback-rating .star {
        color: #fbbf24;
    }

    .feedback-comment {
        font-size: 11px;
        opacity: 0.8;
        margin-top: 5px;
        line-height: 1.4;
    }

    .feedback-time {
        font-size: 9px;
        opacity: 0.6;
        margin-top: 5px;
    }

    .feedback-summary {
        display: flex;
        justify-content: space-around;
        padding: 10px;
        background: rgba(0, 0, 0, 0.02);
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .feedback-stat {
        text-align: center;
    }

    .feedback-stat-value {
        font-size: 16px;
        font-weight: 700;
        color: #10b981;
    }

    .feedback-stat-label {
        font-size: 9px;
        opacity: 0.7;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-top: 2px;
    }

    .feedback-empty {
        text-align: center;
        padding: 20px;
        opacity: 0.6;
    }

    .feedback-empty i {
        font-size: 28px;
        margin-bottom: 6px;
        display: block;
    }

    /* ── Responsive ── */
    @media (max-width: 992px) {
        .kpi-grid { grid-template-columns: repeat(3, 1fr); }
        .main-grid { grid-template-columns: 1fr; }
        .bottom-grid { grid-template-columns: 1fr; }
        .actions-row { grid-template-columns: repeat(2, 1fr); }
        .logistics-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 576px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr); }
        .actions-row { grid-template-columns: repeat(2, 1fr); }
        .feedback-widget { width: 200px; }
        #feedbackWidget { bottom: 80px; right: 8px; }
        #branchRatingWidget { bottom: 170px; right: 8px; }
    }
</style>

{{-- FAB Drag Functionality --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // FAB Dragging
    const fab = document.querySelector('.fab');
    if (fab) {
        let isDragging = false;
        let currentX, currentY, initialX, initialY;
        let xOffset = 0, yOffset = 0;

        fab.addEventListener('mousedown', dragStart);
        fab.addEventListener('touchstart', dragStart);
        document.addEventListener('mousemove', drag);
        document.addEventListener('touchmove', drag);
        document.addEventListener('mouseup', dragEnd);
        document.addEventListener('touchend', dragEnd);

        function dragStart(e) {
            if (e.type === 'touchstart') {
                initialX = e.touches[0].clientX - xOffset;
                initialY = e.touches[0].clientY - yOffset;
            } else {
                initialX = e.clientX - xOffset;
                initialY = e.clientY - yOffset;
            }

            if (e.target === fab || fab.contains(e.target)) {
                isDragging = true;
                fab.classList.add('dragging');
            }
        }

        function drag(e) {
            if (isDragging) {
                e.preventDefault();

                if (e.type === 'touchmove') {
                    currentX = e.touches[0].clientX - initialX;
                    currentY = e.touches[0].clientY - initialY;
                } else {
                    currentX = e.clientX - initialX;
                    currentY = e.clientY - initialY;
                }

                xOffset = currentX;
                yOffset = currentY;

                setTranslate(currentX, currentY, fab);
            }
        }

        function dragEnd(e) {
            if (isDragging) {
                initialX = currentX;
                initialY = currentY;
                isDragging = false;
                fab.classList.remove('dragging');
            }
        }

        function setTranslate(xPos, yPos, el) {
            el.style.transform = `translate3d(${xPos}px, ${yPos}px, 0)`;
        }
    }

    // Feedback Widget Dragging
    const feedbackWidget = document.getElementById('feedbackWidget');
    const feedbackHeader = document.getElementById('feedbackHeader');
    const minimizeBtn = document.getElementById('minimizeBtn');
    const closeBtn = document.getElementById('closeBtn');
    const feedbackBody = document.getElementById('feedbackBody');

    if (feedbackWidget && feedbackHeader) {
        let isDragging = false;
        let currentX, currentY, initialX, initialY;
        let xOffset = 0, yOffset = 0;

        feedbackHeader.addEventListener('mousedown', dragStart);
        feedbackHeader.addEventListener('touchstart', dragStart);
        document.addEventListener('mousemove', drag);
        document.addEventListener('touchmove', drag);
        document.addEventListener('mouseup', dragEnd);
        document.addEventListener('touchend', dragEnd);

        function dragStart(e) {
            if (e.type === 'touchstart') {
                initialX = e.touches[0].clientX - xOffset;
                initialY = e.touches[0].clientY - yOffset;
            } else {
                initialX = e.clientX - xOffset;
                initialY = e.clientY - yOffset;
            }

            if (e.target === feedbackHeader || feedbackHeader.contains(e.target)) {
                if (!e.target.closest('.feedback-btn')) {
                    isDragging = true;
                    feedbackWidget.classList.add('dragging');
                }
            }
        }

        function drag(e) {
            if (isDragging) {
                e.preventDefault();

                if (e.type === 'touchmove') {
                    currentX = e.touches[0].clientX - initialX;
                    currentY = e.touches[0].clientY - initialY;
                } else {
                    currentX = e.clientX - initialX;
                    currentY = e.clientY - initialY;
                }

                xOffset = currentX;
                yOffset = currentY;

                setTranslate(currentX, currentY, feedbackWidget);
            }
        }

        function dragEnd(e) {
            if (isDragging) {
                initialX = currentX;
                initialY = currentY;
                isDragging = false;
                feedbackWidget.classList.remove('dragging');
            }
        }

        function setTranslate(xPos, yPos, el) {
            el.style.transform = `translate3d(${xPos}px, ${yPos}px, 0)`;
        }
    }

    // Minimize/Maximize functionality
    if (minimizeBtn && feedbackBody) {
        minimizeBtn.addEventListener('click', function() {
            feedbackWidget.classList.toggle('minimized');
            feedbackBody.classList.toggle('minimized');
            const icon = minimizeBtn.querySelector('i');
            if (feedbackWidget.classList.contains('minimized')) {
                icon.className = 'bi bi-plus';
                minimizeBtn.title = 'Maximize';
            } else {
                icon.className = 'bi bi-dash';
                minimizeBtn.title = 'Minimize';
            }
        });
    }

    // Close functionality
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            feedbackWidget.classList.add('hidden');
            localStorage.setItem('feedbackWidgetClosed', 'true');
        });
    }

    // Check if widget was closed
    if (localStorage.getItem('feedbackWidgetClosed') === 'true') {
        feedbackWidget.classList.add('hidden');
    }

    // Optional: Add a way to reopen the widget
    window.showFeedbackWidget = function() {
        feedbackWidget.classList.remove('hidden');
        localStorage.removeItem('feedbackWidgetClosed');
    };
});
</script>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Topbar --}}
    <div class="dash-topbar">
        <div>
            <h1>Branch Dashboard</h1>
            <p class="page-sub">{{ now()->format('l, F j, Y') }} &mdash; {{ now()->format('g:i A') }}</p>
        </div>
        <div class="search-wrap">
            <i class="bi bi-search search-ico"></i>
            <input type="text" class="search-input" placeholder="Search tracking #, customer, phone…" id="quickSearch">
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-grid">
        {{-- Revenue --}}
        <div class="kcard" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#revenueBreakdownModal">
            <div class="kcard-top">
                <span class="klabel">Today's revenue</span>
                <div class="kicon" style="background:#dbeafe; color:#1d4ed8;">
                    <i class="bi bi-cash-coin"></i>
                </div>
            </div>
            <div class="kval">₱{{ number_format($kpis['today_revenue']['value'] ?? 0) }}</div>
            <span class="kbadge kbadge-success"><i class="bi bi-arrow-up"></i> 12% vs yesterday</span>
        </div>

        {{-- Active Orders --}}
        <div class="kcard" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#activeOrdersModal">
            <div class="kcard-top">
                <span class="klabel">Active orders</span>
                <div class="kicon" style="background:#d1fae5; color:#059669;">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
            <div class="kval">{{ $kpis['active_laundries']['value'] ?? 0 }}</div>
            <span class="kbadge kbadge-success"><i class="bi bi-arrow-up"></i> 8 new today</span>
        </div>

        {{-- Ready for Pickup --}}
        <div class="kcard" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#readyForPickupModal">
            <div class="kcard-top">
                <span class="klabel">Ready for pickup</span>
                <div class="kicon" style="background:#fef3c7; color:#b45309;">
                    <i class="bi bi-bag-check"></i>
                </div>
            </div>
            <div class="kval">{{ $kpis['ready_for_pickup']['value'] ?? 0 }}</div>
            <span class="kbadge kbadge-warning">
                <i class="bi bi-clock"></i> avg {{ number_format($kpis['ready_for_pickup']['avg_wait_days'] ?? 0, 1) }}d wait
            </span>
        </div>

        {{-- Pending Payment --}}
        @php
            $branchId = auth()->guard('branch')->user()->id;
            $pendingPaymentCount = \App\Models\Laundry::where('branch_id', $branchId)
                ->where('status', 'ready')
                ->where('payment_status', '!=', 'paid')
                ->count();
            $pendingPaymentAmount = \App\Models\Laundry::where('branch_id', $branchId)
                ->where('status', 'ready')
                ->where('payment_status', '!=', 'paid')
                ->sum('total_amount');
        @endphp
        <div class="kcard" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#pendingPaymentModal">
            <div class="kcard-top">
                <span class="klabel">Pending payment</span>
                <div class="kicon" style="background:{{ $pendingPaymentCount > 0 ? '#fee2e2' : '#d1fae5' }}; color:{{ $pendingPaymentCount > 0 ? '#dc2626' : '#059669' }};">
                    <i class="bi bi-{{ $pendingPaymentCount > 0 ? 'credit-card' : 'check2-all' }}"></i>
                </div>
            </div>
            <div class="kval">{{ $pendingPaymentCount }}</div>
            @if($pendingPaymentCount > 0)
                <span class="kbadge kbadge-danger"><i class="bi bi-currency-dollar"></i> ₱{{ number_format($pendingPaymentAmount, 0) }} due</span>
            @else
                <span class="kbadge kbadge-success"><i class="bi bi-check-circle"></i> All paid</span>
            @endif
        </div>

        {{-- Pickup Requests --}}
        <div class="kcard" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#pickupRequestsModal">
            <div class="kcard-top">
                <span class="klabel">Pickup requests</span>
                <div class="kicon" style="background:{{ ($pickups['pending'] ?? 0) > 0 ? '#e0e7ff' : '#d1fae5' }}; color:{{ ($pickups['pending'] ?? 0) > 0 ? '#4f46e5' : '#059669' }};">
                    <i class="bi bi-{{ ($pickups['pending'] ?? 0) > 0 ? 'truck' : 'check2-all' }}"></i>
                </div>
            </div>
            <div class="kval">{{ $pickups['pending'] ?? 0 }}</div>
            @if(($pickups['pending'] ?? 0) > 0)
                <span class="kbadge kbadge-neutral"><i class="bi bi-truck"></i> {{ $pickups['en_route'] ?? 0 }} en route</span>
            @else
                <span class="kbadge kbadge-success"><i class="bi bi-check-circle"></i> All done</span>
            @endif
        </div>

        {{-- Ready to Deliver --}}
        @php
            $readyToDeliverCount = \App\Models\Laundry::where('branch_id', $branchId)
                ->where('status', 'ready')
                ->whereHas('pickupRequest')
                ->count();
            $readyToDeliverOldest = \App\Models\Laundry::where('branch_id', $branchId)
                ->where('status', 'ready')
                ->whereHas('pickupRequest')
                ->orderBy('updated_at', 'asc')
                ->first();
            $oldestDays = $readyToDeliverOldest ? now()->diffInDays($readyToDeliverOldest->updated_at) : 0;
        @endphp
        <div class="kcard" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#readyToDeliverModal">
            <div class="kcard-top">
                <span class="klabel">Ready to deliver</span>
                <div class="kicon" style="background:{{ $readyToDeliverCount > 0 ? '#ede9fe' : '#d1fae5' }}; color:{{ $readyToDeliverCount > 0 ? '#7c3aed' : '#059669' }};">
                    <i class="bi bi-{{ $readyToDeliverCount > 0 ? 'box-arrow-right' : 'check2-all' }}"></i>
                </div>
            </div>
            <div class="kval">{{ $readyToDeliverCount }}</div>
            @if($readyToDeliverCount > 0)
                <span class="kbadge" style="background:#ede9fe; color:#7c3aed;"><i class="bi bi-clock"></i> oldest: {{ $oldestDays }}d</span>
            @else
                <span class="kbadge kbadge-success"><i class="bi bi-check-circle"></i> All delivered</span>
            @endif
        </div>

        {{-- Past Due --}}
        <a href="{{ route('branch.unclaimed.index', ['urgency' => 'critical']) }}" class="kcard">
            <div class="kcard-top">
                <span class="klabel">Past due (7+ days)</span>
                <div class="kicon" style="background:{{ ($unclaimed['categorized']['7+ days'] ?? 0) > 0 ? '#fee2e2' : '#d1fae5' }}; color:{{ ($unclaimed['categorized']['7+ days'] ?? 0) > 0 ? '#dc2626' : '#059669' }};">
                    <i class="bi bi-{{ ($unclaimed['categorized']['7+ days'] ?? 0) > 0 ? 'clock-history' : 'check2-all' }}"></i>
                </div>
            </div>
            <div class="kval">{{ $unclaimed['categorized']['7+ days'] ?? 0 }}</div>
            @if(($unclaimed['categorized']['7+ days'] ?? 0) > 0)
                <span class="kbadge kbadge-danger"><i class="bi bi-clock-history"></i> oldest: {{ $unclaimed['oldest_days'] ?? 0 }}d ↗</span>
            @else
                <span class="kbadge kbadge-success"><i class="bi bi-check-circle"></i> None ↗</span>
            @endif
        </a>
    </div>

    {{-- Main Grid: Left Col + Right Sidebar --}}
    <div class="main-grid">

        {{-- Left Column --}}
        <div class="left-col">

            {{-- Quick Actions --}}
            <div class="actions-row">
                <a href="{{ route('branch.laundries.create') }}" class="acard">
                    <div class="aico" style="background:#dbeafe; color:#1d4ed8;"><i class="bi bi-plus-circle"></i></div>
                    <div>
                        <div class="atitle">Create order</div>
                        <div class="adesc">New laundry job</div>
                    </div>
                </a>

                <div class="acard" data-bs-toggle="modal" data-bs-target="#attendanceModal">
                    <div class="aico" style="background:#d1fae5; color:#059669;"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <div class="atitle">Time in / out</div>
                        <div class="adesc">Staff attendance</div>
                    </div>
                </div>

                <div class="acard" data-bs-toggle="modal" data-bs-target="#retailPosModal">
                    <div class="aico" style="background:#fef3c7; color:#b45309;"><i class="bi bi-shop"></i></div>
                    <div>
                        <div class="atitle">Quick sale</div>
                        <div class="adesc">Retail products</div>
                    </div>
                </div>

                <a href="{{ route('branch.customers.create') }}" class="acard">
                    <div class="aico" style="background:#f3f4f6; color:#374151;"><i class="bi bi-person-plus"></i></div>
                    <div>
                        <div class="atitle">Add customer</div>
                        <div class="adesc">New registration</div>
                    </div>
                </a>
            </div>

            {{-- Financial Analytics & Service Quality Metrics --}}
            @include('branch.dashboard_analytics')

            {{-- Revenue Chart (Last 7 Days - Full Width) --}}
            <div class="panel" style="margin-bottom: 10px;">
                <div class="panel-hd" style="padding: 8px 14px;">
                    <h2 style="font-size: 13px;">Revenue &mdash; last 7 days</h2>
                    <span style="font-size:10px; color:#6b7280;">Updated now</span>
                </div>
                <div class="chart-area" style="overflow-x: auto; overflow-y: hidden; padding: 16px 20px;">
                    <div style="min-width: 400px;">
                        <canvas id="revenueChart" height="100"></canvas>
                    </div>
                </div>
            </div>

        </div>{{-- /left-col --}}

        {{-- Right Sidebar: Pipeline + Inventory Items + Retail Today --}}
        <div class="right-sidebar">

            {{-- Pipeline --}}
            <div class="panel">
                <div class="panel-hd">
                    <h2>Laundry pipeline</h2>
                    <span style="font-size:11px; color:#6b7280;">Live</span>
                </div>
                <div style="padding: 4px 0;">
                    @php
                        $pipelineTotal = max(array_sum($pipeline ?? []), 1);
                        $pipelineItems = [
                            ['status' => 'received',   'label' => 'Received',       'color' => '#378ADD'],
                            ['status' => 'processing', 'label' => 'Processing',     'color' => '#8B5CF6'],
                            ['status' => 'ready',      'label' => 'Ready',          'color' => '#F59E0B'],
                            ['status' => 'paid',       'label' => 'Paid',           'color' => '#10B981'],
                            ['status' => 'completed',  'label' => 'Completed today','color' => '#06B6D4'],
                        ];
                    @endphp
                    @foreach($pipelineItems as $item)
                    @php $count = $pipeline[$item['status']] ?? 0; $pct = round(($count / $pipelineTotal) * 100); @endphp
                    <a href="{{ route('branch.laundries.index', ['status' => $item['status']]) }}" class="pipe-row">
                        <span class="pipe-dot" style="background: {{ $item['color'] }};"></span>
                        <span class="pipe-label">{{ $item['label'] }}</span>
                        <span class="pipe-count">{{ $count }}</span>
                        <div class="pipe-bar-wrap">
                            <div class="pipe-bar-fill" style="width: {{ $pct }}%; background: {{ $item['color'] }};"></div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Inventory Items --}}
            <div class="panel">
                <div class="panel-hd">
                    <h2>Inventory Items</h2>
                    <a href="{{ route('branch.inventory.index') }}">View all ↗</a>
                </div>
                <div class="inv-search-wrap">
                    <input type="text" class="inv-search" placeholder="Search items..." onkeyup="filterInventory(this.value)">
                </div>
                <div class="inv-list">
                    @forelse($inventory_items ?? [] as $item)
                    @php
                        $stockPct = $item->reorder_level > 0 ? min(($item->quantity / $item->reorder_level) * 100, 100) : 100;
                        $chipColor = $stockPct <= 25 ? ['bg'=>'#fee2e2','tc'=>'#991b1b'] : ($stockPct <= 50 ? ['bg'=>'#fef3c7','tc'=>'#92400e'] : ['bg'=>'#d1fae5','tc'=>'#065f46']);
                        $barColor = $stockPct <= 25 ? '#ef4444' : ($stockPct <= 50 ? '#f59e0b' : '#10b981');
                    @endphp
                    <div class="inv-row inventory-item" data-name="{{ strtolower($item->name) }}">
                        <div class="inv-name">{{ $item->name }}</div>
                        <div class="inv-bar-wrap">
                            <div class="inv-bar-fill" style="width: {{ $stockPct }}%; background: {{ $barColor }};"></div>
                        </div>
                        <div class="inv-qty">{{ number_format($item->quantity, 1) }}</div>
                        <div class="inv-unit">{{ $item->unit }}</div>
                        <span class="inv-chip" style="background: {{ $chipColor['bg'] }}; color: {{ $chipColor['tc'] }};">
                            {{ $stockPct <= 25 ? 'Low' : ($stockPct <= 50 ? 'Med' : 'OK') }}
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted" style="font-size: 12px;">
                        No inventory items
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Retail Today --}}
            <div class="panel">
                <div class="panel-hd">
                    <h2>Retail today</h2>
                    <a href="{{ route('branch.retail.index') }}">View all ↗</a>
                </div>
                <div class="rpval">₱{{ number_format($retail_sales_today['revenue'] ?? 0) }}</div>
                <div class="rpsub"><i class="bi bi-arrow-up"></i> {{ $retail_sales_today['items_sold'] ?? 0 }} sold</div>
                <div class="section-label">Top products</div>
                @forelse($retail_top_products ?? [] as $product)
                <div class="prod-row">
                    <div>
                        <div class="prod-name">{{ $product->item_name }}</div>
                        <div class="prod-meta">{{ $product->total_sold }}x sold</div>
                    </div>
                    <div class="prod-rev">₱{{ number_format($product->revenue, 0) }}</div>
                </div>
                @empty
                <div class="text-center py-4 text-muted" style="font-size: 12px;">
                    No sales today
                </div>
                @endforelse
            </div>

        </div>{{-- /right-sidebar --}}
    </div>{{-- /main-grid --}}

    {{-- ============================================================
         LOGISTICS MAP & PICKUP MANAGEMENT
    ============================================================ --}}
    <div class="logistics-grid" style="margin-bottom: 10px;">
        {{-- Pickup Management --}}
        <div class="panel">
            <div class="panel-hd">
                <div>
                    <h2>Pickup Management</h2>
                    <small style="font-size: 11px; color: #6b7280;">Select pickups for optimized routing</small>
                </div>
                <span id="selectedPickupCount" class="badge" style="display:none;background:#8b5cf6;color:white;border-radius:999px;font-size:10px;">0</span>
            </div>
            <div style="padding: 12px 16px;">
                <div id="multiRouteBtn" class="d-grid mb-3" style="display:none!important;">
                    <button class="btn btn-purple btn-sm shadow-sm" onclick="window.getOptimizedMultiRoute()">
                        <i class="bi bi-route me-2"></i>Optimize Route (<span id="selectedCount">0</span>)
                    </button>
                </div>
                <div class="d-grid mb-3">
                    <button class="btn btn-sm" style="border-radius:8px;background:#2563eb;color:white;border:none;padding:8px;font-weight:600;font-size:12px;" onclick="window.autoRouteAllVisible()">
                        <i class="bi bi-magic me-2"></i>Auto-Optimize All Pending
                    </button>
                </div>
                <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-sm btn-outline-purple flex-fill" style="font-size:11px;" onclick="window.selectAllPending()">
                        <i class="bi bi-check-square me-1"></i>Select All
                    </button>
                    <button class="btn btn-sm btn-outline-danger flex-fill" style="font-size:11px;" onclick="window.clearSelections()">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </button>
                </div>
                <h6 class="mb-2" style="font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">Active Pickups</h6>
                <div style="max-height:260px;overflow-y:auto;" id="pickupListContainer">
                    @forelse($pickupLocations ?? [] as $pickup)
                        <label class="pickup-list-item d-flex align-items-center gap-2 p-2 mb-1 rounded" for="chk-{{ $pickup->id }}" style="cursor:pointer;font-size:12px;">
                            <input type="checkbox" class="form-check-input pickup-checkbox m-0" id="chk-{{ $pickup->id }}" onclick="window.togglePickupSelection({{ $pickup->id }})">
                            <div class="flex-grow-1">
                                <div style="font-weight:600;color:#111827;">{{ $pickup->customer->name ?? 'Customer' }}</div>
                                <small style="color:#6b7280;">{{ $pickup->customer->phone ?? '' }}</small>
                            </div>
                            <span class="status-pill sp-{{ $pickup->status === 'en_route' ? 'processing' : 'ready' }}">
                                {{ ucfirst(str_replace('_', ' ', $pickup->status)) }}
                            </span>
                        </label>
                    @empty
                        <div class="text-center py-4" style="color:#6b7280;">
                            <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
                            <span style="font-size:12px;">No active pickup requests</span>
                        </div>
                    @endforelse
                </div>
                <hr style="border-color:#e5e7eb;opacity:0.5;margin:16px 0;">
                <div class="d-flex justify-content-between text-center" style="font-size:11px;">
                    <div>
                        <div style="font-size:18px;font-weight:800;color:#111827;font-family:'DM Mono',monospace;">{{ $pickups['pending'] ?? 0 }}</div>
                        <small style="color:#6b7280;">Pending</small>
                    </div>
                    <div>
                        <div style="font-size:18px;font-weight:800;color:#111827;font-family:'DM Mono',monospace;">{{ $pickups['en_route'] ?? 0 }}</div>
                        <small style="color:#6b7280;">En Route</small>
                    </div>
                    <div>
                        <div style="font-size:18px;font-weight:800;color:#10b981;font-family:'DM Mono',monospace;">{{ $pickups['completed_today'] ?? 0 }}</div>
                        <small style="color:#6b7280;">Done</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Map --}}
        <div class="panel">
            <div class="panel-hd">
                <h2>Logistics Map</h2>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm" style="border-radius:6px;background:#f3f4f6;border:1px solid #d1d5db;color:#374151;padding:4px 8px;font-size:11px;" onclick="window.refreshMapMarkers()">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <button class="btn btn-sm" style="border-radius:6px;background:#2563eb;border:none;color:white;padding:4px 8px;font-size:11px;" data-bs-toggle="modal" data-bs-target="#mapModal">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                </div>
            </div>
            <div style="padding:0;position:relative;">
                <div id="address-search-overlay" style="position:absolute;top:12px;right:12px;z-index:1000;max-width:280px;">
                    <div class="card border-0 shadow-lg" style="border-radius:8px;">
                        <div class="card-body p-2">
                            <div class="input-group input-group-sm">
                                <input type="text" id="map-address-search" class="form-control" placeholder="Search address..." style="font-size:12px;border-radius:6px 0 0 6px;border:1px solid #d1d5db;">
                                <button class="btn btn-sm" style="background:#2563eb;color:white;border:none;border-radius:0 6px 6px 0;" onclick="window.searchMapAddress()">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="branchMap" class="branch-map-container"></div>
            </div>
        </div>
    </div>

    {{-- MAP MODAL --}}
    <div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content" style="border:none;">
                <div class="modal-header border-bottom shadow-sm py-2" style="background:#2563eb;color:white;">
                    <h5 class="modal-title fw-bold" style="font-size:14px;"><i class="bi bi-map me-2"></i>Logistics Map</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-warning" id="modalMultiRouteBtn" style="display:none;font-size:11px;" onclick="getOptimizedMultiRoute()">
                            <i class="bi bi-route me-1"></i>Optimize (<span id="modalSelectedCount">0</span>)
                        </button>
                        <button class="btn btn-sm btn-info" style="font-size:11px;" onclick="autoRouteAllVisible()">
                            <i class="bi bi-magic me-1"></i>Auto-Optimize
                        </button>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0 position-relative">
                    <div id="modalBranchMap" style="height:100%;width:100%;"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="routeDetailsPanel" class="route-details-panel" style="display:none;"></div>

    {{-- Recent Orders & Top Customers --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
        {{-- Recent Orders --}}
        <div class="panel">
            <div class="panel-hd">
                <h2>Recent laundries</h2>
                <a href="{{ route('branch.laundries.index') }}">View all ↗</a>
            </div>
            <div class="table-responsive">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Tracking</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_laundries ?? [] as $laundry)
                        <tr>
                            <td><strong>{{ $laundry->tracking_number }}</strong></td>
                            <td>{{ $laundry->customer->name ?? 'N/A' }}</td>
                            <td>{{ $laundry->service->name ?? '—' }}</td>
                            <td><strong>₱{{ number_format($laundry->total_amount, 2) }}</strong></td>
                            <td>
                                <span class="status-pill s-{{ $laundry->status }}">{{ ucfirst($laundry->status) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('branch.laundries.show', $laundry->id) }}" class="btn-outline-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No orders yet
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Customers --}}
        <div class="panel">
            <div class="panel-hd">
                <h2>Top customers</h2>
                <a href="{{ route('branch.customers.index') }}">View all ↗</a>
            </div>
            @php
                $avatarColors = [
                    ['bg'=>'#dbeafe','tc'=>'#1d4ed8'],
                    ['bg'=>'#d1fae5','tc'=>'#059669'],
                    ['bg'=>'#fef3c7','tc'=>'#b45309'],
                    ['bg'=>'#ede9fe','tc'=>'#6d28d9'],
                    ['bg'=>'#fce7f3','tc'=>'#9d174d'],
                    ['bg'=>'#d1fae5','tc'=>'#065f46'],
                ];
                $rankColors = [
                    ['bg'=>'#fef3c7','tc'=>'#b45309'],
                    ['bg'=>'#f3f4f6','tc'=>'#374151'],
                    ['bg'=>'#fee2e2','tc'=>'#991b1b'],
                ];
            @endphp
            @forelse($top_customers ?? [] as $index => $customer)
            @php
                $av = $avatarColors[$index % count($avatarColors)];
                $rk = $rankColors[$index] ?? ['bg'=>'#f3f4f6','tc'=>'#374151'];
                $initials = collect(explode(' ', $customer['name']))->take(2)->map(fn($w) => strtoupper(substr($w,0,1)))->join('');
            @endphp
            <div class="cust-row">
                <span class="rank-badge" style="background: {{ $rk['bg'] }}; color: {{ $rk['tc'] }};">{{ $index + 1 }}</span>
                <div class="cust-avatar" style="background: {{ $av['bg'] }}; color: {{ $av['tc'] }};">{{ $initials }}</div>
                <div class="cust-info">
                    <div class="cust-name">{{ $customer['name'] }}</div>
                    <div class="cust-orders">{{ $customer['orders_count'] }} orders this month</div>
                </div>
                <div class="cust-spent">₱{{ number_format($customer['total_spent'], 0) }}</div>
            </div>
            @empty
            <div class="text-center py-4 text-muted" style="font-size: 12px;">
                No customer data yet
            </div>
            @endforelse
        </div>
    </div>

</div>{{-- /container-fluid --}}

{{-- Customer Ratings Widget --}}
<div class="feedback-widget collapsed" id="feedbackWidget">
    <div class="feedback-header" id="feedbackHeader">
        <h3>
            <i class="bi bi-star-fill"></i>
            <span>Customer Ratings</span>
        </h3>
        <div class="feedback-controls">
            <button class="feedback-btn" id="minimizeBtn" title="Minimize">
                <i class="bi bi-dash"></i>
            </button>
            <button class="feedback-btn" id="closeBtn" title="Close">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>
    <div class="feedback-body" id="feedbackBody">
        @php
            $branchId = auth()->guard('branch')->user()->id;
            $allRatings = \App\Models\CustomerRating::where('branch_id', $branchId)->get();
            $avgRating = $allRatings->count() > 0 ? round($allRatings->avg('rating'), 1) : 0;
            $todayRatings = $allRatings->filter(fn($r) => $r->created_at->isToday())->count();
            $positiveCount = $allRatings->filter(fn($r) => $r->rating >= 4)->count();
            $positivePct = $allRatings->count() > 0 ? round(($positiveCount / $allRatings->count()) * 100) : 0;
            $recentReviews = \App\Models\CustomerRating::where('branch_id', $branchId)
                ->whereNotNull('comment')
                ->with('customer:id,name')
                ->orderByDesc('created_at')
                ->limit(3)
                ->get();
        @endphp
        {{-- Summary Stats --}}
        <div class="feedback-summary">
            <div class="feedback-stat">
                <div class="feedback-stat-value">{{ number_format($avgRating, 1) }}</div>
                <div class="feedback-stat-label">Avg Rating</div>
            </div>
            <div class="feedback-stat">
                <div class="feedback-stat-value">{{ $todayRatings }}</div>
                <div class="feedback-stat-label">Today</div>
            </div>
            <div class="feedback-stat">
                <div class="feedback-stat-value">{{ $positivePct }}%</div>
                <div class="feedback-stat-label">Positive</div>
            </div>
        </div>

        {{-- Branch Satisfaction Bar --}}
        <div style="padding: 10px; background: linear-gradient(135deg, rgba(16,185,129,0.1) 0%, rgba(5,150,105,0.05) 100%); border-radius: 6px; margin-bottom: 10px; border: 1px solid rgba(16,185,129,0.2);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                <span style="font-size:11px; font-weight:600; color:#059669;">
                    <i class="bi bi-building"></i> Branch Satisfaction
                </span>
                <span style="font-size:14px; font-weight:700; color:#10b981;">{{ $positivePct }}%</span>
            </div>
            <div style="width:100%; height:6px; background:rgba(0,0,0,0.1); border-radius:3px; overflow:hidden;">
                <div style="width:{{ $positivePct }}%; height:100%; background:linear-gradient(90deg,#10b981 0%,#059669 100%); border-radius:3px;"></div>
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:6px;">
                <span style="font-size:9px; opacity:0.7;">This Month</span>
                <span style="font-size:9px; opacity:0.7;">Target: 95%</span>
            </div>
        </div>

        {{-- Recent Reviews --}}
        @forelse($recentReviews as $review)
        <div class="feedback-item">
            <div class="feedback-customer">
                <span>{{ $review->customer->name ?? 'Anonymous' }}</span>
                <div class="feedback-rating">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }} star"></i>
                    @endfor
                </div>
            </div>
            @if($review->comment)
            <div class="feedback-comment">{{ Str::limit($review->comment, 80) }}</div>
            @endif
            <div class="feedback-time">{{ $review->created_at->diffForHumans() }}</div>
        </div>
        @empty
        <div style="text-align:center; padding:12px; font-size:11px; opacity:0.6;">No reviews yet</div>
        @endforelse
    </div>
</div>

{{-- Branch Ratings Widget --}}
<div class="feedback-widget collapsed" id="branchRatingWidget" style="bottom: 185px; right: 24px;">
    <div class="feedback-header" id="branchRatingHeader" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); cursor:move;">
        <h3>
            <i class="bi bi-building"></i>
            <span>Branch Ratings</span>
        </h3>
        <div class="feedback-controls">
            <button class="feedback-btn" id="branchRatingMinimizeBtn" title="Minimize">
                <i class="bi bi-dash"></i>
            </button>
            <button class="feedback-btn" id="branchRatingCloseBtn" title="Close">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>
    <div class="feedback-body" id="branchRatingBody">
        @php
            $branchOverall = isset($allRatings) && $allRatings->count() > 0 ? round($allRatings->avg('rating'), 1) : 0;
            $branchName = auth()->guard('branch')->user()->name ?? 'This Branch';
        @endphp
        <div class="feedback-summary" style="justify-content:center; padding:12px 0; flex-direction:column; align-items:center; gap:4px;">
            <div class="feedback-stat-value" style="font-size:2rem; color:#f59e0b;">{{ number_format($branchOverall, 1) }}</div>
            <div style="color:#f59e0b; font-size:0.9rem;">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= floor($branchOverall))
                        <i class="bi bi-star-fill"></i>
                    @elseif($i == ceil($branchOverall) && $branchOverall - floor($branchOverall) >= 0.5)
                        <i class="bi bi-star-half"></i>
                    @else
                        <i class="bi bi-star"></i>
                    @endif
                @endfor
            </div>
            <div class="feedback-stat-label">Overall Rating</div>
        </div>
        <div class="feedback-item" style="border-top:1px solid var(--border-color, rgba(0,0,0,0.08));">
            <div class="feedback-customer">
                <span style="font-weight:600;">{{ $branchName }}</span>
                <span style="font-size:11px; color:#f59e0b; font-weight:700;">{{ number_format($branchOverall, 1) }}</span>
            </div>
            <div class="feedback-time">{{ isset($allRatings) ? $allRatings->count() : 0 }} total reviews</div>
        </div>
    </div>
</div>


{{-- ══════════════════ MODALS ══════════════════ --}}

{{-- Revenue Breakdown Modal --}}
<div class="modal fade" id="revenueBreakdownModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-cash-coin text-success me-2"></i>Today's Revenue Breakdown
                    </h5>
                    <small class="text-muted">{{ now()->format('l, F j, Y') }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $todayStart = today();
                    $todayEnd = today()->endOfDay();
                    $branchId = auth()->guard('branch')->user()->id;

                    // Laundry Services Revenue - only paid/completed
                    $laundryRevenue = \App\Models\Laundry::where('branch_id', $branchId)
                        ->whereDate('created_at', $todayStart)
                        ->whereIn('status', ['paid', 'completed'])
                        ->sum('total_amount');

                    $laundryByService = \App\Models\Laundry::where('branch_id', $branchId)
                        ->whereDate('created_at', $todayStart)
                        ->whereIn('status', ['paid', 'completed'])
                        ->with(['service:id,name', 'customer:id,name'])
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->map(function($laundry) {
                            $addons = \DB::table('laundry_inventory_items')
                                ->join('inventory_items', 'laundry_inventory_items.inventory_item_id', '=', 'inventory_items.id')
                                ->leftJoin('inventory_categories', 'inventory_items.category_id', '=', 'inventory_categories.id')
                                ->where('laundry_inventory_items.laundries_id', $laundry->id)
                                ->selectRaw('inventory_categories.name as category_name, SUM(laundry_inventory_items.quantity) as total_qty')
                                ->groupBy('inventory_categories.name')
                                ->get()
                                ->pluck('total_qty', 'category_name');

                            $laundry->detergent_qty = $addons->get('Detergent', 0);
                            $laundry->fabcon_qty = $addons->get('Fabcon', 0);
                            $laundry->bleach_qty = $addons->get('Bleach', 0);
                            $laundry->plastics_qty = $addons->get('Plastics', 0);

                            return $laundry;
                        });

                    // Retail Sales Revenue
                    $retailRevenue = \App\Models\RetailSale::where('branch_id', $branchId)
                        ->whereDate('created_at', $todayStart)
                        ->sum('total_amount');

                    $retailByItem = \App\Models\RetailSale::where('branch_id', $branchId)
                        ->whereDate('created_at', $todayStart)
                        ->selectRaw('item_name, inventory_item_id, SUM(total_amount) as revenue, SUM(quantity) as total_qty')
                        ->groupBy('item_name', 'inventory_item_id')
                        ->with('inventoryItem.category')
                        ->get();

                    // Total Revenue
                    $totalRevenue = $laundryRevenue + $retailRevenue;
                @endphp

                {{-- Total Revenue Summary --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-cash-stack" style="font-size: 1.5rem; color: #10b981;"></i>
                                <div>
                                    <small class="text-muted" style="font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Total Revenue</small>
                                    <h4 class="mb-0 fw-bold" style="color: #10b981;">&#8369;{{ number_format($totalRevenue, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3);">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-basket3" style="font-size: 1.5rem; color: #3b82f6;"></i>
                                <div>
                                    <small class="text-muted" style="font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Laundry Services</small>
                                    <h4 class="mb-0 fw-bold" style="color: #3b82f6;">&#8369;{{ number_format($laundryRevenue, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3);">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-cart-check" style="font-size: 1.5rem; color: #f59e0b;"></i>
                                <div>
                                    <small class="text-muted" style="font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Retail Sales</small>
                                    <h4 class="mb-0 fw-bold" style="color: #f59e0b;">&#8369;{{ number_format($retailRevenue, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Laundry Services Breakdown --}}
                @if($laundryByService->count() > 0)
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-basket3 text-primary me-2"></i>Laundry Services Breakdown
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead style="background: rgba(59, 130, 246, 0.1);">
                                <tr>
                                    <th class="text-muted" style="font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">#</th>
                                    <th class="text-muted" style="font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Customer</th>
                                    <th class="text-muted" style="font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Service</th>
                                    <th class="text-center text-muted" style="font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Loads/Pieces</th>
                                    <th class="text-center text-muted" style="font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Detergent</th>
                                    <th class="text-center text-muted" style="font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Fabcon</th>
                                    <th class="text-center text-muted" style="font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Bleach</th>
                                    <th class="text-center text-muted" style="font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Plastics</th>
                                    <th class="text-end text-muted" style="font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Revenue</th>
                                    <th class="text-end text-muted" style="font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($laundryByService as $item)
                                <tr>
                                    <td style="padding: 0.75rem;">
                                        <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #2563eb; font-size: 0.7rem;">#{{ $item->id }}</span>
                                    </td>
                                    <td style="padding: 0.75rem;">{{ $item->customer->name ?? 'N/A' }}</td>
                                    <td style="padding: 0.75rem;"><span class="fw-600">{{ $item->service->name ?? 'N/A' }}</span></td>
                                    <td class="text-center" style="padding: 0.75rem;">
                                        <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #2563eb; font-size: 0.7rem;">{{ $item->number_of_loads ?? 0 }}</span>
                                    </td>
                                    <td class="text-center" style="padding: 0.75rem;">
                                        <span class="badge" style="background: rgba(59, 130, 246, 0.15); color: #2563eb; font-size: 0.7rem;">{{ number_format($item->detergent_qty, 1) }}</span>
                                    </td>
                                    <td class="text-center" style="padding: 0.75rem;">
                                        <span class="badge" style="background: rgba(139, 92, 246, 0.15); color: #7c3aed; font-size: 0.7rem;">{{ number_format($item->fabcon_qty, 1) }}</span>
                                    </td>
                                    <td class="text-center" style="padding: 0.75rem;">
                                        <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #d97706; font-size: 0.7rem;">{{ number_format($item->bleach_qty, 1) }}</span>
                                    </td>
                                    <td class="text-center" style="padding: 0.75rem;">
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; font-size: 0.7rem;">{{ number_format($item->plastics_qty, 1) }}</span>
                                    </td>
                                    <td class="text-end" style="padding: 0.75rem;">
                                        <span class="fw-bold" style="color: #10b981;">&#8369;{{ number_format($item->total_amount, 2) }}</span>
                                    </td>
                                    <td class="text-end" style="padding: 0.75rem;">
                                        <span class="text-muted">{{ $totalRevenue > 0 ? number_format(($item->total_amount / $totalRevenue) * 100, 1) : 0 }}%</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Retail Sales Breakdown --}}
                @if($retailByItem->count() > 0)
                <div>
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-cart-check text-warning me-2"></i>Retail Sales Breakdown
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead style="background: rgba(245, 158, 11, 0.1);">
                                <tr>
                                    <th class="text-muted" style="font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Item Name</th>
                                    <th class="text-center text-muted" style="font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Category</th>
                                    <th class="text-center text-muted" style="font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Quantity</th>
                                    <th class="text-end text-muted" style="font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Revenue</th>
                                    <th class="text-end text-muted" style="font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($retailByItem as $item)
                                <tr>
                                    <td style="padding: 0.75rem;"><span class="fw-600">{{ $item->item_name }}</span></td>
                                    <td class="text-center" style="padding: 0.75rem;">
                                        <span class="badge" style="background: rgba(245, 158, 11, 0.2); color: #d97706; font-size: 0.7rem;">{{ $item->inventoryItem->category->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-center" style="padding: 0.75rem;">
                                        <span class="text-muted">{{ number_format($item->total_qty, 1) }}</span>
                                    </td>
                                    <td class="text-end" style="padding: 0.75rem;">
                                        <span class="fw-bold" style="color: #10b981;">&#8369;{{ number_format($item->revenue, 2) }}</span>
                                    </td>
                                    <td class="text-end" style="padding: 0.75rem;">
                                        <span class="text-muted">{{ $totalRevenue > 0 ? number_format(($item->revenue / $totalRevenue) * 100, 1) : 0 }}%</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline-dash" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Attendance Modal --}}
<div class="modal fade" id="attendanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Time In Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Staff Member</label>
                    <select class="form-select" id="staffSelect">
                        <option value="">Select Staff</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Take Photo <span class="text-danger">*</span></label>
                    <div class="text-center">
                        <video id="attendanceVideo" width="100%" height="300" autoplay style="border-radius: 8px; background: #000;"></video>
                        <canvas id="attendanceCanvas" style="display:none;"></canvas>
                        <img id="capturedPhoto" style="display:none; width:100%; border-radius: 8px;" />
                    </div>
                    <div class="mt-3 d-grid gap-2">
                        <button type="button" class="btn-primary-dash" id="captureBtn">
                            <i class="bi bi-camera-fill me-2"></i>Capture Photo
                        </button>
                        <button type="button" class="btn-outline-dash" id="retakeBtn" style="display:none;">
                            <i class="bi bi-arrow-clockwise me-2"></i>Retake
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline-dash" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-primary-dash" id="timeInBtn">Time In</button>
            </div>
        </div>
    </div>
</div>

{{-- Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Collect Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tracking Number</label>
                    <input type="text" class="form-control" placeholder="Enter or scan tracking number">
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <select class="form-select">
                        <option>Cash</option>
                        <option>GCash</option>
                        <option>Card</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline-dash" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-primary-dash">Process Payment</button>
            </div>
        </div>
    </div>
</div>

{{-- Retail POS Modal --}}
<div class="modal fade" id="retailPosModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-shop me-2"></i>Quick Sale — Retail POS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-7">
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 600;">Search Product</label>
                            <input type="text" class="form-control" id="productSearch" placeholder="Scan barcode or search product name…">
                        </div>
                        <div class="row g-2" id="productGrid" style="max-height: 400px; overflow-y: auto;">
                            @forelse($retail_products ?? [] as $product)
                            <div class="col-6">
                                <div class="card" style="cursor: pointer; border: 1px solid #e5e7eb; transition: all 0.2s;"
                                     onclick="addToCart('{{ $product['name'] }}', {{ $product['price'] }}, {{ $product['id'] }})"
                                     onmouseover="this.style.borderColor='#2563eb'"
                                     onmouseout="this.style.borderColor='#e5e7eb'">
                                    <div class="card-body p-3 text-center">
                                        <div style="background: #f3f4f6; width: 52px; height: 52px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">
                                            <i class="bi bi-box-seam fs-4" style="color: #2563eb;"></i>
                                        </div>
                                        <div style="font-size: 13px; font-weight: 600; color: #111827; margin-bottom: 4px;">{{ $product['name'] }}</div>
                                        <div style="font-size: 15px; font-weight: 700; color: #2563eb; margin-bottom: 4px;">₱{{ number_format($product['price'], 2) }}</div>
                                        <div style="font-size: 11px; font-weight: 500; color: #059669; background: #d1fae5; padding: 3px 8px; border-radius: 6px; display: inline-block;">
                                            Stock: {{ $product['stock'] }} {{ $product['unit'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 40px;"></i>
                                <p class="mt-2" style="font-size: 13px;">No products available</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card" style="border: 1px solid #e5e7eb;">
                            <div class="card-body">
                                <h6 style="font-size: 15px; font-weight: 600; margin-bottom: 16px;">Cart Items</h6>
                                <div id="cartItems" style="max-height: 280px; overflow-y: auto; margin-bottom: 16px;">
                                    <div class="text-center text-muted py-4">
                                        <i class="bi bi-cart3" style="font-size: 40px;"></i>
                                        <p class="mt-2" style="font-size: 13px;">No items in cart</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-2">
                                    <span style="font-size: 13px; color: #374151;">Subtotal:</span>
                                    <span style="font-size: 13px; font-weight: 600;" id="subtotal">₱0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3 pb-3" style="border-bottom: 1px solid #e5e7eb;">
                                    <span style="font-size: 16px; font-weight: 700;">Total:</span>
                                    <span style="font-size: 16px; font-weight: 700; color: #2563eb;" id="total">₱0.00</span>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600;">Payment Method</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="cash">Cash</option>
                                        <option value="gcash">GCash</option>
                                        <option value="card">Card</option>
                                    </select>
                                </div>
                                <button class="btn-primary-dash w-100" onclick="completeSale()" style="padding: 12px; font-size: 14px;">
                                    <i class="bi bi-check-circle me-2"></i>Complete Sale
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- FAB --}}
<button class="fab" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="bi bi-lightning-charge-fill"></i>
</button>
<ul class="dropdown-menu dropdown-menu-end shadow" style="min-width: 220px;">
    <li><h6 class="dropdown-header"><i class="bi bi-lightning-charge me-1"></i>Quick Actions</h6></li>

    {{-- Laundry Actions --}}
    <li>
        <a class="dropdown-item" href="{{ route('branch.laundries.create') }}">
            <i class="bi bi-plus-circle me-2" style="color:#10b981;"></i>New Laundry Order
        </a>
    </li>
    <li>
        <a class="dropdown-item" href="#" onclick="document.getElementById('quickSearch').focus(); return false;">
            <i class="bi bi-search me-2" style="color:#3b82f6;"></i>Search Order
        </a>
    </li>
    <li><hr class="dropdown-divider"></li>

    {{-- Customer & Pickup Actions --}}
    <li>
        <a class="dropdown-item" href="{{ route('branch.customers.create') }}">
            <i class="bi bi-person-plus me-2" style="color:#8b5cf6;"></i>Add Customer
        </a>
    </li>
    <li>
        <a class="dropdown-item" href="{{ route('branch.pickups.index') }}">
            <i class="bi bi-truck me-2" style="color:#f59e0b;"></i>Pickup Requests
        </a>
    </li>
    <li><hr class="dropdown-divider"></li>

    {{-- Payment & Finance --}}
    <li>
        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#paymentModal">
            <i class="bi bi-cash-coin me-2" style="color:#10b981;"></i>Collect Payment
        </a>
    </li>
    <li>
        <a class="dropdown-item" href="{{ route('branch.finance.expenses') }}">
            <i class="bi bi-receipt me-2" style="color:#ef4444;"></i>Record Expense
        </a>
    </li>
    <li><hr class="dropdown-divider"></li>

    {{-- Inventory Actions --}}
    <li>
        <a class="dropdown-item" href="{{ route('branch.inventory.index') }}">
            <i class="bi bi-box-seam me-2" style="color:#06b6d4;"></i>Check Inventory
        </a>
    </li>
    <li>
        <a class="dropdown-item" href="{{ route('branch.inventory.requests') }}">
            <i class="bi bi-cart-plus me-2" style="color:#f97316;"></i>Request Stock
        </a>
    </li>
    <li><hr class="dropdown-divider"></li>

    {{-- Staff & Attendance --}}
    <li>
        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#attendanceModal">
            <i class="bi bi-clock me-2" style="color:#6366f1;"></i>Time In/Out
        </a>
    </li>
    <li>
        <a class="dropdown-item" href="{{ route('branch.staff.index') }}">
            <i class="bi bi-people me-2" style="color:#8b5cf6;"></i>View Staff
        </a>
    </li>
    <li><hr class="dropdown-divider"></li>

    {{-- Reports --}}
    <li>
        <a class="dropdown-item" href="{{ route('branch.finance.daily-cash-report') }}">
            <i class="bi bi-file-earmark-text me-2" style="color:#64748b;"></i>Daily Report
        </a>
    </li>
</ul>

@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="{{ asset('assets/jquery/jquery-3.7.0.min.js') }}"></script>
<script src="{{ asset('assets/leaflet/leaflet.js') }}"></script>
<script src="{{ asset('assets/leaflet/leaflet.markercluster.js') }}"></script>
<script src="{{ asset('assets/js/staff.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.initializeDashboardData === 'function') {
            window.initializeDashboardData(
                @json($allBranches ?? []),
                @json($pickupLocations ?? []),
                {{ auth()->guard('branch')->user()->id }}
            );
        }
    });
</script>

<script>
    // ── Revenue Chart ──
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        const weeklyData = @json($weeklyPerformance ?? []);
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: weeklyData.map(d => d.short_day),
                datasets: [{
                    label: 'Revenue',
                    data: weeklyData.map(d => d.revenue),
                    borderColor: '#378ADD',
                    backgroundColor: 'rgba(55, 138, 221, 0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#378ADD',
                    pointBorderColor: 'transparent'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111827',
                        padding: 10,
                        titleColor: '#fff',
                        bodyColor: 'rgba(255,255,255,0.75)',
                        displayColors: false,
                        callbacks: {
                            label: ctx => '₱' + ctx.parsed.y.toLocaleString('en-PH', { minimumFractionDigits: 2 })
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.06)' },
                        ticks: {
                            color: '#9ca3af',
                            font: { size: 11 },
                            callback: v => '₱' + v.toLocaleString()
                        },
                        border: { display: false }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9ca3af', font: { size: 11 } },
                        border: { display: false }
                    }
                }
            }
        });
    }

    // ── Inventory Filter ──
    function filterInventory(query) {
        const q = query.toLowerCase();
        document.querySelectorAll('.inventory-item').forEach(row => {
            row.style.display = row.dataset.name.includes(q) ? '' : 'none';
        });
    }

    // ── Analytics Filter ──
    function filterAnalytics(query) {
        const q = query.toLowerCase();
        document.querySelectorAll('.analytics-item').forEach(row => {
            row.style.display = row.dataset.name.includes(q) ? '' : 'none';
        });
    }

    // ── Quick Search ──
    document.getElementById('quickSearch')?.addEventListener('keypress', function (e) {
        if (e.key === 'Enter' && this.value) {
            window.location.href = `/branch/laundries?search=${encodeURIComponent(this.value)}`;
        }
    });

    // ── Attendance Modal ──
    let videoStream = null;
    const attendanceModal = document.getElementById('attendanceModal');
    const video          = document.getElementById('attendanceVideo');
    const canvas         = document.getElementById('attendanceCanvas');
    const capturedPhoto  = document.getElementById('capturedPhoto');
    const captureBtn     = document.getElementById('captureBtn');
    const retakeBtn      = document.getElementById('retakeBtn');

    attendanceModal?.addEventListener('shown.bs.modal', async () => {
        try {
            videoStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: 640, height: 480 } });
            video.srcObject = videoStream;
        } catch (e) { console.error('Camera error:', e); }
    });

    attendanceModal?.addEventListener('hidden.bs.modal', () => {
        videoStream?.getTracks().forEach(t => t.stop());
        videoStream = null;
        video.style.display = 'block';
        capturedPhoto.style.display = 'none';
        captureBtn.style.display = 'block';
        retakeBtn.style.display = 'none';
    });

    captureBtn?.addEventListener('click', () => {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        capturedPhoto.src = canvas.toDataURL('image/jpeg', 0.8);
        video.style.display = 'none';
        capturedPhoto.style.display = 'block';
        captureBtn.style.display = 'none';
        retakeBtn.style.display = 'block';
    });

    retakeBtn?.addEventListener('click', () => {
        video.style.display = 'block';
        capturedPhoto.style.display = 'none';
        captureBtn.style.display = 'block';
        retakeBtn.style.display = 'none';
    });

    // ── Retail POS Cart ──
    let cart = [];

    function addToCart(name, price, productId) {
        const existing = cart.find(i => i.id === productId);
        existing ? existing.quantity++ : cart.push({ id: productId, name, price, quantity: 1 });
        updateCart();
    }

    function removeFromCart(index) { cart.splice(index, 1); updateCart(); }

    function updateQuantity(index, change) {
        if (!cart[index]) return;
        cart[index].quantity += change;
        if (cart[index].quantity <= 0) cart.splice(index, 1);
        updateCart();
    }

    function updateCart() {
        const cartItems = document.getElementById('cartItems');
        const subtotalEl = document.getElementById('subtotal');
        const totalEl    = document.getElementById('total');
        if (!cartItems) return;

        if (cart.length === 0) {
            cartItems.innerHTML = `<div class="text-center text-muted py-4"><i class="bi bi-cart3" style="font-size:40px;"></i><p class="mt-2" style="font-size:13px;">No items in cart</p></div>`;
            subtotalEl.textContent = '₱0.00';
            totalEl.textContent = '₱0.00';
            return;
        }

        let html = '';
        let totalAmount = 0;
        cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            totalAmount += itemTotal;
            html += `
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom:1px solid #e5e7eb;">
                    <div style="flex:1;">
                        <div style="font-size:13px;font-weight:600;">${item.name}</div>
                        <div style="font-size:12px;color:#6b7280;">₱${item.price.toFixed(2)} each</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary" style="padding:3px 8px;" onclick="updateQuantity(${index},-1)"><i class="bi bi-dash"></i></button>
                            <button class="btn btn-outline-secondary" disabled style="padding:3px 10px;background:white;">${item.quantity}</button>
                            <button class="btn btn-outline-secondary" style="padding:3px 8px;" onclick="updateQuantity(${index},1)"><i class="bi bi-plus"></i></button>
                        </div>
                        <span style="font-size:13px;font-weight:700;min-width:64px;text-align:right;">₱${itemTotal.toFixed(2)}</span>
                        <button class="btn btn-sm btn-outline-danger" style="padding:3px 7px;" onclick="removeFromCart(${index})"><i class="bi bi-trash"></i></button>
                    </div>
                </div>`;
        });

        cartItems.innerHTML = html;
        subtotalEl.textContent = `₱${totalAmount.toFixed(2)}`;
        totalEl.textContent    = `₱${totalAmount.toFixed(2)}`;
    }

    function completeSale() {
        if (cart.length === 0) { alert('Cart is empty!'); return; }
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('items', JSON.stringify(cart));
        formData.append('payment_method', document.querySelector('#retailPosModal select[name="payment_method"]')?.value || 'cash');

        fetch('{{ route("branch.retail.quick-sale") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Sale completed successfully!');
                cart = [];
                updateCart();
                bootstrap.Modal.getInstance(document.getElementById('retailPosModal')).hide();
                location.reload();
            } else {
                alert(data.message || 'Failed to complete sale');
            }
        })
        .catch(() => alert('Failed to complete sale. Please try again.'));
    }
</script>
@endpush

{{-- Ready to Deliver Modal --}}
<div class="modal fade" id="readyToDeliverModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #0f172a; border: 1px solid #334155;">
            <div class="modal-header border-bottom" style="border-color: #334155 !important;">
                <div>
                    <h5 class="modal-title fw-bold" style="color: #f1f5f9;">
                        <i class="bi bi-box-arrow-right text-purple me-2" style="color: #a78bfa;"></i>Ready to Deliver
                    </h5>
                    <small style="color: #94a3b8;">Laundries from pickup requests ready for delivery</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $branchId = auth()->guard('branch')->user()->id;
                    $readyToDeliverLaundries = \App\Models\Laundry::where('branch_id', $branchId)
                        ->where('status', 'ready')
                        ->whereHas('pickupRequest')
                        ->with(['customer:id,name,phone,email,address', 'service:id,name', 'pickupRequest'])
                        ->orderBy('updated_at', 'asc')
                        ->get();
                @endphp

                @if($readyToDeliverLaundries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm" style="color: #cbd5e1;">
                        <thead style="background: rgba(139, 92, 246, 0.1); border-bottom: 2px solid #334155;">
                            <tr>
                                <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">ID</th>
                                <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Customer</th>
                                <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Contact</th>
                                <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Delivery Address</th>
                                <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Service</th>
                                <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Weight</th>
                                <th class="text-end" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Amount</th>
                                <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Payment</th>
                                <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Ready Since</th>
                                <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Wait Time</th>
                                <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($readyToDeliverLaundries as $laundry)
                            @php
                                $waitDays = now()->diffInDays($laundry->updated_at);
                                $waitHours = now()->diffInHours($laundry->updated_at) % 24;
                                $urgencyColor = $waitDays >= 3 ? '#ef4444' : ($waitDays >= 1 ? '#f59e0b' : '#10b981');
                                $urgencyBg = $waitDays >= 3 ? 'rgba(239, 68, 68, 0.1)' : ($waitDays >= 1 ? 'rgba(245, 158, 11, 0.1)' : 'rgba(16, 185, 129, 0.1)');
                                $deliveryAddress = $laundry->pickupRequest->pickup_address ?? $laundry->customer->address ?? 'N/A';
                            @endphp
                            <tr style="border-bottom: 1px solid #1e293b;">
                                <td style="padding: 0.75rem;">
                                    <span class="badge" style="background: rgba(139, 92, 246, 0.2); color: #a78bfa; font-size: 0.75rem;">
                                        #{{ $laundry->id }}
                                    </span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span class="fw-600" style="color: #f1f5f9;">{{ $laundry->customer->name ?? 'N/A' }}</span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <div style="font-size: 0.75rem; color: #94a3b8;">
                                        <i class="bi bi-telephone"></i> {{ $laundry->customer->phone ?? 'N/A' }}
                                    </div>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span style="color: #cbd5e1; font-size: 0.8rem;">{{ Str::limit($deliveryAddress, 40) }}</span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span style="color: #cbd5e1;">{{ $laundry->service->name ?? 'N/A' }}</span>
                                </td>
                                <td class="text-center" style="padding: 0.75rem;">
                                    <span style="color: #94a3b8;">{{ number_format($laundry->weight, 2) }} kg</span>
                                </td>
                                <td class="text-end" style="padding: 0.75rem;">
                                    <span class="fw-bold" style="color: #10b981;">₱{{ number_format($laundry->total_amount, 2) }}</span>
                                </td>
                                <td class="text-center" style="padding: 0.75rem;">
                                    @if($laundry->payment_status === 'paid')
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #10b981; font-size: 0.7rem;">Paid</span>
                                    @else
                                        <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #ef4444; font-size: 0.7rem;">COD</span>
                                    @endif
                                </td>
                                <td class="text-center" style="padding: 0.75rem;">
                                    <span style="color: #94a3b8; font-size: 0.75rem;">{{ $laundry->updated_at->format('M d, Y') }}</span>
                                    <div style="color: #64748b; font-size: 0.7rem;">{{ $laundry->updated_at->format('h:i A') }}</div>
                                </td>
                                <td class="text-center" style="padding: 0.75rem;">
                                    <span class="badge" style="background: {{ $urgencyBg }}; color: {{ $urgencyColor }}; font-size: 0.7rem;">
                                        @if($waitDays > 0)
                                            {{ $waitDays }}d {{ $waitHours }}h
                                        @else
                                            {{ $waitHours }}h
                                        @endif
                                    </span>
                                </td>
                                <td class="text-center" style="padding: 0.75rem;">
                                    <a href="{{ route('branch.laundries.show', $laundry->id) }}" class="btn btn-sm" style="background: rgba(139, 92, 246, 0.2); color: #a78bfa; border: none; font-size: 0.75rem;">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Summary Stats --}}
                <div class="row g-3 mt-3">
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Total Ready</small>
                            <h4 class="mb-0 fw-bold" style="color: #a78bfa;">{{ $readyToDeliverLaundries->count() }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Paid</small>
                            <h4 class="mb-0 fw-bold" style="color: #10b981;">{{ $readyToDeliverLaundries->where('payment_status', 'paid')->count() }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">COD</small>
                            <h4 class="mb-0 fw-bold" style="color: #ef4444;">{{ $readyToDeliverLaundries->where('payment_status', '!=', 'paid')->count() }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Urgent (3+ days)</small>
                            <h4 class="mb-0 fw-bold" style="color: #ef4444;">{{ $readyToDeliverLaundries->filter(fn($l) => now()->diffInDays($l->updated_at) >= 3)->count() }}</h4>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-check-circle" style="font-size: 4rem; color: #10b981;"></i>
                    <h5 class="mt-3" style="color: #94a3b8;">All deliveries completed!</h5>
                    <p style="color: #64748b; font-size: 0.875rem;">No pending deliveries at this time.</p>
                </div>
                @endif
            </div>
            <div class="modal-footer" style="border-color: #334155 !important;">
                <button type="button" class="btn-outline-dash" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Pending Payment Modal --}}
<div class="modal fade" id="pendingPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #0f172a; border: 1px solid #334155;">
            <div class="modal-header border-bottom" style="border-color: #334155 !important;">
                <div>
                    <h5 class="modal-title fw-bold" style="color: #f1f5f9;">
                        <i class="bi bi-credit-card text-warning me-2"></i>Pending Payment
                    </h5>
                    <small style="color: #94a3b8;">Laundries ready but awaiting payment</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $branchId = auth()->guard('branch')->user()->id;
                    $pendingPaymentLaundries = \App\Models\Laundry::where('branch_id', $branchId)
                        ->where('status', 'ready')
                        ->where('payment_status', '!=', 'paid')
                        ->with(['customer:id,name,phone,email', 'service:id,name'])
                        ->orderBy('updated_at', 'asc')
                        ->get();
                @endphp

                @if($pendingPaymentLaundries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm" style="color: #cbd5e1;">
                        <thead style="background: rgba(245, 158, 11, 0.1); border-bottom: 2px solid #334155;">
                            <tr>
                                <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">ID</th>
                                <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Customer</th>
                                <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Contact</th>
                                <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Service</th>
                                <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Weight</th>
                                <th class="text-end" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Amount Due</th>
                                <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Ready Since</th>
                                <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Wait Time</th>
                                <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingPaymentLaundries as $laundry)
                            @php
                                $waitDays = now()->diffInDays($laundry->updated_at);
                                $waitHours = now()->diffInHours($laundry->updated_at) % 24;
                                $urgencyColor = $waitDays >= 7 ? '#ef4444' : ($waitDays >= 3 ? '#f59e0b' : '#10b981');
                                $urgencyBg = $waitDays >= 7 ? 'rgba(239, 68, 68, 0.1)' : ($waitDays >= 3 ? 'rgba(245, 158, 11, 0.1)' : 'rgba(16, 185, 129, 0.1)');
                            @endphp
                            <tr style="border-bottom: 1px solid #1e293b;">
                                <td style="padding: 0.75rem;">
                                    <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; font-size: 0.75rem;">
                                        #{{ $laundry->id }}
                                    </span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span class="fw-600" style="color: #f1f5f9;">{{ $laundry->customer->name ?? 'N/A' }}</span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <div style="font-size: 0.75rem; color: #94a3b8;">
                                        <i class="bi bi-telephone"></i> {{ $laundry->customer->phone ?? 'N/A' }}
                                    </div>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span style="color: #cbd5e1;">{{ $laundry->service->name ?? 'N/A' }}</span>
                                </td>
                                <td class="text-center" style="padding: 0.75rem;">
                                    <span style="color: #94a3b8;">{{ number_format($laundry->weight, 2) }} kg</span>
                                </td>
                                <td class="text-end" style="padding: 0.75rem;">
                                    <span class="fw-bold" style="color: #f59e0b;">₱{{ number_format($laundry->total_amount, 2) }}</span>
                                </td>
                                <td class="text-center" style="padding: 0.75rem;">
                                    <span style="color: #94a3b8; font-size: 0.75rem;">{{ $laundry->updated_at->format('M d, Y') }}</span>
                                    <div style="color: #64748b; font-size: 0.7rem;">{{ $laundry->updated_at->format('h:i A') }}</div>
                                </td>
                                <td class="text-center" style="padding: 0.75rem;">
                                    <span class="badge" style="background: {{ $urgencyBg }}; color: {{ $urgencyColor }}; font-size: 0.7rem;">
                                        @if($waitDays > 0)
                                            {{ $waitDays }}d {{ $waitHours }}h
                                        @else
                                            {{ $waitHours }}h
                                        @endif
                                    </span>
                                </td>
                                <td class="text-center" style="padding: 0.75rem;">
                                    <a href="{{ route('branch.laundries.show', $laundry->id) }}" class="btn btn-sm" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: none; font-size: 0.75rem;">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Summary Stats --}}
                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Total Pending</small>
                            <h4 class="mb-0 fw-bold" style="color: #f59e0b;">{{ $pendingPaymentLaundries->count() }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Total Amount Due</small>
                            <h4 class="mb-0 fw-bold" style="color: #ef4444;">₱{{ number_format($pendingPaymentLaundries->sum('total_amount'), 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Urgent (7+ days)</small>
                            <h4 class="mb-0 fw-bold" style="color: #ef4444;">{{ $pendingPaymentLaundries->filter(fn($l) => now()->diffInDays($l->updated_at) >= 7)->count() }}</h4>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-check-circle" style="font-size: 4rem; color: #10b981;"></i>
                    <h5 class="mt-3" style="color: #94a3b8;">All payments collected!</h5>
                    <p style="color: #64748b; font-size: 0.875rem;">No pending payments at this time.</p>
                </div>
                @endif
            </div>
            <div class="modal-footer" style="border-color: #334155 !important;">
                <button type="button" class="btn-outline-dash" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Ready for Pickup Modal --}}
<div class="modal fade" id="readyForPickupModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #0f172a; border: 1px solid #334155;">
            <div class="modal-header border-bottom" style="border-color: #334155 !important;">
                <div>
                    <h5 class="modal-title fw-bold" style="color: #f1f5f9;">
                        <i class="bi bi-bag-check text-warning me-2"></i>Laundries Ready for Pickup
                    </h5>
                    <small style="color: #94a3b8;">{{ $kpis['ready_for_pickup']['value'] ?? 0 }} items waiting for customer pickup</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $branchId = auth()->guard('branch')->user()->id;
                    $readyLaundries = \App\Models\Laundry::where('branch_id', $branchId)
                        ->where('status', 'ready')
                        ->with(['customer:id,name,phone,email', 'service:id,name'])
                        ->orderBy('updated_at', 'asc')
                        ->get();
                @endphp

                @if($readyLaundries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm" style="color: #cbd5e1;">
                        <thead style="background: rgba(245, 158, 11, 0.1); border-bottom: 2px solid #334155;">
                            <tr>
                                <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Laundry ID</th>
                                <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Customer</th>
                                <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Contact</th>
                                <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Service</th>
                                <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Weight</th>
                                <th class="text-end" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Amount</th>
                                <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Ready Since</th>
                                <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Wait Time</th>
                                <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($readyLaundries as $laundry)
                            @php
                                $waitDays = now()->diffInDays($laundry->updated_at);
                                $waitHours = now()->diffInHours($laundry->updated_at) % 24;
                                $urgencyColor = $waitDays >= 7 ? '#ef4444' : ($waitDays >= 3 ? '#f59e0b' : '#10b981');
                                $urgencyBg = $waitDays >= 7 ? 'rgba(239, 68, 68, 0.1)' : ($waitDays >= 3 ? 'rgba(245, 158, 11, 0.1)' : 'rgba(16, 185, 129, 0.1)');
                            @endphp
                            <tr style="border-bottom: 1px solid #1e293b;">
                                <td style="padding: 0.75rem;">
                                    <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; font-size: 0.75rem;">
                                        #{{ $laundry->id }}
                                    </span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <div>
                                        <span class="fw-600" style="color: #f1f5f9;">{{ $laundry->customer->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <div style="font-size: 0.75rem;">
                                        <div style="color: #94a3b8;">
                                            <i class="bi bi-telephone"></i> {{ $laundry->customer->phone ?? 'N/A' }}
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span style="color: #cbd5e1;">{{ $laundry->service->name ?? 'N/A' }}</span>
                                </td>
                                <td class="text-center" style="padding: 0.75rem;">
                                    <span style="color: #94a3b8;">{{ number_format($laundry->weight, 2) }} kg</span>
                                </td>
                                <td class="text-end" style="padding: 0.75rem;">
                                    <span class="fw-bold" style="color: #10b981;">₱{{ number_format($laundry->total_amount, 2) }}</span>
                                </td>
                                <td class="text-center" style="padding: 0.75rem;">
                                    <span style="color: #94a3b8; font-size: 0.75rem;">{{ $laundry->updated_at->format('M d, Y') }}</span>
                                    <div style="color: #64748b; font-size: 0.7rem;">{{ $laundry->updated_at->format('h:i A') }}</div>
                                </td>
                                <td class="text-center" style="padding: 0.75rem;">
                                    <span class="badge" style="background: {{ $urgencyBg }}; color: {{ $urgencyColor }}; font-size: 0.7rem;">
                                        @if($waitDays > 0)
                                            {{ $waitDays }}d {{ $waitHours }}h
                                        @else
                                            {{ $waitHours }}h
                                        @endif
                                    </span>
                                </td>
                                <td class="text-center" style="padding: 0.75rem;">
                                    <a href="{{ route('branch.laundries.show', $laundry->id) }}" class="btn btn-sm" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: none; font-size: 0.75rem;">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Summary Stats --}}
                <div class="row g-3 mt-3">
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Total Items</small>
                            <h4 class="mb-0 fw-bold" style="color: #10b981;">{{ $readyLaundries->count() }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Total Value</small>
                            <h4 class="mb-0 fw-bold" style="color: #3b82f6;">₱{{ number_format($readyLaundries->sum('total_amount'), 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Avg Wait Time</small>
                            <h4 class="mb-0 fw-bold" style="color: #f59e0b;">{{ number_format($kpis['ready_for_pickup']['avg_wait_days'] ?? 0, 1) }}d</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Urgent (7+ days)</small>
                            <h4 class="mb-0 fw-bold" style="color: #ef4444;">{{ $readyLaundries->filter(fn($l) => now()->diffInDays($l->updated_at) >= 7)->count() }}</h4>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-bag-check" style="font-size: 4rem; color: #334155;"></i>
                    <h5 class="mt-3" style="color: #94a3b8;">No laundries ready for pickup</h5>
                    <p style="color: #64748b; font-size: 0.875rem;">All laundries have been picked up or are still in progress.</p>
                </div>
                @endif
            </div>
            <div class="modal-footer" style="border-color: #334155 !important;">
                <button type="button" class="btn-outline-dash" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


{{-- Active Orders Modal --}}
<div class="modal fade" id="activeOrdersModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #0f172a; border: 1px solid #334155;">
            <div class="modal-header border-bottom" style="border-color: #334155 !important;">
                <div>
                    <h5 class="modal-title fw-bold" style="color: #f1f5f9;">
                        <i class="bi bi-box-seam text-success me-2"></i>Active Laundries
                    </h5>
                    <small style="color: #94a3b8;">{{ $kpis['active_laundries']['value'] ?? 0 }} orders in progress</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $branchId = auth()->guard('branch')->user()->id;
                    $activeLaundries = \App\Models\Laundry::where('branch_id', $branchId)
                        ->whereIn('status', ['received', 'processing', 'ready', 'paid'])
                        ->with(['customer:id,name,phone,email', 'service:id,name'])
                        ->orderBy('created_at', 'desc')
                        ->get();

                    $groupedByStatus = $activeLaundries->groupBy('status');
                @endphp

                @if($activeLaundries->count() > 0)
                {{-- Status Tabs --}}
                <ul class="nav nav-pills mb-3" style="gap: 0.5rem;">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#allActive" style="background: rgba(16, 185, 129, 0.2); color: #10b981; border: none; font-size: 0.875rem;">
                            All ({{ $activeLaundries->count() }})
                        </button>
                    </li>
                    @if($groupedByStatus->has('received'))
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#receivedTab" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: none; font-size: 0.875rem;">
                            Received ({{ $groupedByStatus['received']->count() }})
                        </button>
                    </li>
                    @endif
                    @if($groupedByStatus->has('processing'))
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#processingTab" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: none; font-size: 0.875rem;">
                            Processing ({{ $groupedByStatus['processing']->count() }})
                        </button>
                    </li>
                    @endif
                    @if($groupedByStatus->has('ready'))
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#readyTab" style="background: rgba(139, 92, 246, 0.2); color: #a78bfa; border: none; font-size: 0.875rem;">
                            Ready ({{ $groupedByStatus['ready']->count() }})
                        </button>
                    </li>
                    @endif
                    @if($groupedByStatus->has('paid'))
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#paidTab" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: none; font-size: 0.875rem;">
                            Paid ({{ $groupedByStatus['paid']->count() }})
                        </button>
                    </li>
                    @endif
                </ul>

                <div class="tab-content">
                    {{-- All Active Orders --}}
                    <div class="tab-pane fade show active" id="allActive">
                        <div class="table-responsive">
                            <table class="table table-sm" style="color: #cbd5e1;">
                                <thead style="background: rgba(16, 185, 129, 0.1); border-bottom: 2px solid #334155;">
                                    <tr>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">ID</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Customer</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Service</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Status</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Weight</th>
                                        <th class="text-end" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Amount</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Created</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeLaundries as $laundry)
                                    @php
                                        $statusColors = [
                                            'received' => ['bg' => 'rgba(59, 130, 246, 0.2)', 'color' => '#60a5fa'],
                                            'processing' => ['bg' => 'rgba(245, 158, 11, 0.2)', 'color' => '#fbbf24'],
                                            'ready' => ['bg' => 'rgba(139, 92, 246, 0.2)', 'color' => '#a78bfa'],
                                            'paid' => ['bg' => 'rgba(16, 185, 129, 0.2)', 'color' => '#34d399'],
                                        ];
                                        $statusStyle = $statusColors[$laundry->status] ?? ['bg' => 'rgba(100, 116, 139, 0.2)', 'color' => '#94a3b8'];
                                    @endphp
                                    <tr style="border-bottom: 1px solid #1e293b;">
                                        <td style="padding: 0.75rem;">
                                            <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; font-size: 0.75rem;">
                                                #{{ $laundry->id }}
                                            </span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <div>
                                                <span class="fw-600" style="color: #f1f5f9;">{{ $laundry->customer->name ?? 'N/A' }}</span>
                                                <div style="font-size: 0.7rem; color: #64748b;">
                                                    <i class="bi bi-telephone"></i> {{ $laundry->customer->phone ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span style="color: #cbd5e1;">{{ $laundry->service->name ?? 'N/A' }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <span class="badge" style="background: {{ $statusStyle['bg'] }}; color: {{ $statusStyle['color'] }}; font-size: 0.7rem; text-transform: capitalize;">
                                                {{ $laundry->status }}
                                            </span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <span style="color: #94a3b8;">{{ number_format($laundry->weight, 2) }} kg</span>
                                        </td>
                                        <td class="text-end" style="padding: 0.75rem;">
                                            <span class="fw-bold" style="color: #10b981;">₱{{ number_format($laundry->total_amount, 2) }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <span style="color: #94a3b8; font-size: 0.75rem;">{{ $laundry->created_at->format('M d, Y') }}</span>
                                            <div style="color: #64748b; font-size: 0.7rem;">{{ $laundry->created_at->diffForHumans() }}</div>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <a href="{{ route('branch.laundries.show', $laundry->id) }}" class="btn btn-sm" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: none; font-size: 0.75rem;">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Received Tab --}}
                    @if($groupedByStatus->has('received'))
                    <div class="tab-pane fade" id="receivedTab">
                        <div class="table-responsive">
                            <table class="table table-sm" style="color: #cbd5e1;">
                                <thead style="background: rgba(59, 130, 246, 0.1); border-bottom: 2px solid #334155;">
                                    <tr>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">ID</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Customer</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Service</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Weight</th>
                                        <th class="text-end" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Amount</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groupedByStatus['received'] as $laundry)
                                    <tr style="border-bottom: 1px solid #1e293b;">
                                        <td style="padding: 0.75rem;">
                                            <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; font-size: 0.75rem;">#{{ $laundry->id }}</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span class="fw-600" style="color: #f1f5f9;">{{ $laundry->customer->name ?? 'N/A' }}</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span style="color: #cbd5e1;">{{ $laundry->service->name ?? 'N/A' }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <span style="color: #94a3b8;">{{ number_format($laundry->weight, 2) }} kg</span>
                                        </td>
                                        <td class="text-end" style="padding: 0.75rem;">
                                            <span class="fw-bold" style="color: #10b981;">₱{{ number_format($laundry->total_amount, 2) }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <a href="{{ route('branch.laundries.show', $laundry->id) }}" class="btn btn-sm" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: none; font-size: 0.75rem;">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Processing Tab --}}
                    @if($groupedByStatus->has('processing'))
                    <div class="tab-pane fade" id="processingTab">
                        <div class="table-responsive">
                            <table class="table table-sm" style="color: #cbd5e1;">
                                <thead style="background: rgba(245, 158, 11, 0.1); border-bottom: 2px solid #334155;">
                                    <tr>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">ID</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Customer</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Service</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Weight</th>
                                        <th class="text-end" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Amount</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groupedByStatus['processing'] as $laundry)
                                    <tr style="border-bottom: 1px solid #1e293b;">
                                        <td style="padding: 0.75rem;">
                                            <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; font-size: 0.75rem;">#{{ $laundry->id }}</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span class="fw-600" style="color: #f1f5f9;">{{ $laundry->customer->name ?? 'N/A' }}</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span style="color: #cbd5e1;">{{ $laundry->service->name ?? 'N/A' }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <span style="color: #94a3b8;">{{ number_format($laundry->weight, 2) }} kg</span>
                                        </td>
                                        <td class="text-end" style="padding: 0.75rem;">
                                            <span class="fw-bold" style="color: #10b981;">₱{{ number_format($laundry->total_amount, 2) }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <a href="{{ route('branch.laundries.show', $laundry->id) }}" class="btn btn-sm" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: none; font-size: 0.75rem;">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Ready Tab --}}
                    @if($groupedByStatus->has('ready'))
                    <div class="tab-pane fade" id="readyTab">
                        <div class="table-responsive">
                            <table class="table table-sm" style="color: #cbd5e1;">
                                <thead style="background: rgba(139, 92, 246, 0.1); border-bottom: 2px solid #334155;">
                                    <tr>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">ID</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Customer</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Service</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Weight</th>
                                        <th class="text-end" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Amount</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groupedByStatus['ready'] as $laundry)
                                    <tr style="border-bottom: 1px solid #1e293b;">
                                        <td style="padding: 0.75rem;">
                                            <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; font-size: 0.75rem;">#{{ $laundry->id }}</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span class="fw-600" style="color: #f1f5f9;">{{ $laundry->customer->name ?? 'N/A' }}</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span style="color: #cbd5e1;">{{ $laundry->service->name ?? 'N/A' }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <span style="color: #94a3b8;">{{ number_format($laundry->weight, 2) }} kg</span>
                                        </td>
                                        <td class="text-end" style="padding: 0.75rem;">
                                            <span class="fw-bold" style="color: #10b981;">₱{{ number_format($laundry->total_amount, 2) }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <a href="{{ route('branch.laundries.show', $laundry->id) }}" class="btn btn-sm" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: none; font-size: 0.75rem;">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Paid Tab --}}
                    @if($groupedByStatus->has('paid'))
                    <div class="tab-pane fade" id="paidTab">
                        <div class="table-responsive">
                            <table class="table table-sm" style="color: #cbd5e1;">
                                <thead style="background: rgba(16, 185, 129, 0.1); border-bottom: 2px solid #334155;">
                                    <tr>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">ID</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Customer</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Service</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Weight</th>
                                        <th class="text-end" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Amount</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groupedByStatus['paid'] as $laundry)
                                    <tr style="border-bottom: 1px solid #1e293b;">
                                        <td style="padding: 0.75rem;">
                                            <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; font-size: 0.75rem;">#{{ $laundry->id }}</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span class="fw-600" style="color: #f1f5f9;">{{ $laundry->customer->name ?? 'N/A' }}</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span style="color: #cbd5e1;">{{ $laundry->service->name ?? 'N/A' }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <span style="color: #94a3b8;">{{ number_format($laundry->weight, 2) }} kg</span>
                                        </td>
                                        <td class="text-end" style="padding: 0.75rem;">
                                            <span class="fw-bold" style="color: #10b981;">₱{{ number_format($laundry->total_amount, 2) }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <a href="{{ route('branch.laundries.show', $laundry->id) }}" class="btn btn-sm" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: none; font-size: 0.75rem;">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Summary Stats --}}
                <div class="row g-3 mt-3">
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Total Active</small>
                            <h4 class="mb-0 fw-bold" style="color: #10b981;">{{ $activeLaundries->count() }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Total Value</small>
                            <h4 class="mb-0 fw-bold" style="color: #3b82f6;">₱{{ number_format($activeLaundries->sum('total_amount'), 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Total Weight</small>
                            <h4 class="mb-0 fw-bold" style="color: #f59e0b;">{{ number_format($activeLaundries->sum('weight'), 2) }} kg</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Avg Amount</small>
                            <h4 class="mb-0 fw-bold" style="color: #a78bfa;">₱{{ $activeLaundries->count() > 0 ? number_format($activeLaundries->avg('total_amount'), 2) : '0.00' }}</h4>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-box-seam" style="font-size: 4rem; color: #334155;"></i>
                    <h5 class="mt-3" style="color: #94a3b8;">No active orders</h5>
                    <p style="color: #64748b; font-size: 0.875rem;">All orders have been completed or there are no orders yet.</p>
                </div>
                @endif
            </div>
            <div class="modal-footer" style="border-color: #334155 !important;">
                <button type="button" class="btn-outline-dash" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


{{-- Pickup Requests Modal --}}
<div class="modal fade" id="pickupRequestsModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #0f172a; border: 1px solid #334155;">
            <div class="modal-header border-bottom" style="border-color: #334155 !important;">
                <div>
                    <h5 class="modal-title fw-bold" style="color: #f1f5f9;">
                        <i class="bi bi-truck text-primary me-2"></i>Pickup Requests
                    </h5>
                    <small style="color: #94a3b8;">{{ $pickups['pending'] ?? 0 }} pending, {{ $pickups['en_route'] ?? 0 }} en route</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $branchId = auth()->guard('branch')->user()->id;
                    $allPickups = \App\Models\PickupRequest::where('branch_id', $branchId)
                        ->with(['customer:id,name,phone,email'])
                        ->orderBy('created_at', 'desc')
                        ->get();

                    $groupedByStatus = $allPickups->groupBy('status');
                    $pendingPickups = $groupedByStatus->get('pending', collect());
                    $acceptedPickups = $groupedByStatus->get('accepted', collect());
                    $enRoutePickups = $groupedByStatus->get('en_route', collect());
                    $completedPickups = $groupedByStatus->get('completed', collect())->take(10);
                @endphp

                {{-- Status Tabs --}}
                <ul class="nav nav-pills mb-3" style="gap: 0.5rem;">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pendingPickups" style="background: rgba(239, 68, 68, 0.2); color: #ef4444; border: none; font-size: 0.875rem;">
                            Pending ({{ $pendingPickups->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#acceptedPickups" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: none; font-size: 0.875rem;">
                            Accepted ({{ $acceptedPickups->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#enRoutePickups" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: none; font-size: 0.875rem;">
                            En Route ({{ $enRoutePickups->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#completedPickups" style="background: rgba(16, 185, 129, 0.2); color: #10b981; border: none; font-size: 0.875rem;">
                            Completed ({{ $completedPickups->count() }})
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    {{-- Pending Pickups --}}
                    <div class="tab-pane fade show active" id="pendingPickups">
                        @if($pendingPickups->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm" style="color: #cbd5e1;">
                                <thead style="background: rgba(239, 68, 68, 0.1); border-bottom: 2px solid #334155;">
                                    <tr>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">ID</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Customer</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Contact</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Address</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Requested</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Wait Time</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingPickups as $pickup)
                                    @php
                                        $waitHours = now()->diffInHours($pickup->created_at);
                                        $urgencyColor = $waitHours >= 24 ? '#ef4444' : ($waitHours >= 6 ? '#f59e0b' : '#10b981');
                                    @endphp
                                    <tr style="border-bottom: 1px solid #1e293b;">
                                        <td style="padding: 0.75rem;">
                                            <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; font-size: 0.75rem;">
                                                #{{ $pickup->id }}
                                            </span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span class="fw-600" style="color: #f1f5f9;">{{ $pickup->customer->name ?? 'N/A' }}</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <div style="font-size: 0.75rem; color: #94a3b8;">
                                                <i class="bi bi-telephone"></i> {{ $pickup->customer->phone ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span style="color: #cbd5e1; font-size: 0.8rem;">{{ Str::limit($pickup->pickup_address ?? 'N/A', 40) }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <span style="color: #94a3b8; font-size: 0.75rem;">{{ $pickup->created_at->format('M d, h:i A') }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <span class="badge" style="background: rgba({{ $urgencyColor == '#ef4444' ? '239, 68, 68' : ($urgencyColor == '#f59e0b' ? '245, 158, 11' : '16, 185, 129') }}, 0.2); color: {{ $urgencyColor }}; font-size: 0.7rem;">
                                                {{ $pickup->created_at->diffForHumans() }}
                                            </span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <a href="{{ route('branch.pickups.show', $pickup->id) }}" class="btn btn-sm" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: none; font-size: 0.75rem;">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle" style="font-size: 3rem; color: #10b981;"></i>
                            <h6 class="mt-2" style="color: #94a3b8;">No pending pickup requests</h6>
                         </div>
                        @endif
                    </div>

                    {{-- Accepted Pickups --}}
                    <div class="tab-pane fade" id="acceptedPickups">
                        @if($acceptedPickups->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm" style="color: #cbd5e1;">
                                <thead style="background: rgba(59, 130, 246, 0.1); border-bottom: 2px solid #334155;">
                                    <tr>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">ID</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Customer</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Contact</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Address</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Accepted At</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($acceptedPickups as $pickup)
                                    <tr style="border-bottom: 1px solid #1e293b;">
                                        <td style="padding: 0.75rem;">
                                            <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; font-size: 0.75rem;">#{{ $pickup->id }}</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span class="fw-600" style="color: #f1f5f9;">{{ $pickup->customer->name ?? 'N/A' }}</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <div style="font-size: 0.75rem; color: #94a3b8;">
                                                <i class="bi bi-telephone"></i> {{ $pickup->customer->phone ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span style="color: #cbd5e1; font-size: 0.8rem;">{{ Str::limit($pickup->pickup_address ?? 'N/A', 40) }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <span style="color: #94a3b8; font-size: 0.75rem;">{{ $pickup->updated_at->format('M d, h:i A') }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <a href="{{ route('branch.pickups.show', $pickup->id) }}" class="btn btn-sm" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: none; font-size: 0.75rem;">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #334155;"></i>
                            <h6 class="mt-2" style="color: #94a3b8;">No accepted pickups</h6>
                        </div>
                        @endif
                    </div>

                    {{-- En Route Pickups --}}
                    <div class="tab-pane fade" id="enRoutePickups">
                        @if($enRoutePickups->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm" style="color: #cbd5e1;">
                                <thead style="background: rgba(245, 158, 11, 0.1); border-bottom: 2px solid #334155;">
                                    <tr>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">ID</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Customer</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Contact</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Address</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Started</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enRoutePickups as $pickup)
                                    <tr style="border-bottom: 1px solid #1e293b;">
                                        <td style="padding: 0.75rem;">
                                            <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; font-size: 0.75rem;">#{{ $pickup->id }}</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span class="fw-600" style="color: #f1f5f9;">{{ $pickup->customer->name ?? 'N/A' }}</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <div style="font-size: 0.75rem; color: #94a3b8;">
                                                <i class="bi bi-telephone"></i> {{ $pickup->customer->phone ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span style="color: #cbd5e1; font-size: 0.8rem;">{{ Str::limit($pickup->pickup_address ?? 'N/A', 40) }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <span style="color: #94a3b8; font-size: 0.75rem;">{{ $pickup->updated_at->diffForHumans() }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <a href="{{ route('branch.pickups.show', $pickup->id) }}" class="btn btn-sm" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: none; font-size: 0.75rem;">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #334155;"></i>
                            <h6 class="mt-2" style="color: #94a3b8;">No pickups en route</h6>
                        </div>
                        @endif
                    </div>

                    {{-- Completed Pickups --}}
                    <div class="tab-pane fade" id="completedPickups">
                        @if($completedPickups->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm" style="color: #cbd5e1;">
                                <thead style="background: rgba(16, 185, 129, 0.1); border-bottom: 2px solid #334155;">
                                    <tr>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">ID</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Customer</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Contact</th>
                                        <th style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Address</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Completed At</th>
                                        <th class="text-center" style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; padding: 0.75rem;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($completedPickups as $pickup)
                                    <tr style="border-bottom: 1px solid #1e293b;">
                                        <td style="padding: 0.75rem;">
                                            <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; font-size: 0.75rem;">#{{ $pickup->id }}</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span class="fw-600" style="color: #f1f5f9;">{{ $pickup->customer->name ?? 'N/A' }}</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <div style="font-size: 0.75rem; color: #94a3b8;">
                                                <i class="bi bi-telephone"></i> {{ $pickup->customer->phone ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <span style="color: #cbd5e1; font-size: 0.8rem;">{{ Str::limit($pickup->pickup_address ?? 'N/A', 40) }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <span style="color: #94a3b8; font-size: 0.75rem;">{{ $pickup->picked_up_at ? $pickup->picked_up_at->format('M d, h:i A') : 'N/A' }}</span>
                                        </td>
                                        <td class="text-center" style="padding: 0.75rem;">
                                            <a href="{{ route('branch.pickups.show', $pickup->id) }}" class="btn btn-sm" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: none; font-size: 0.75rem;">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #334155;"></i>
                            <h6 class="mt-2" style="color: #94a3b8;">No completed pickups</h6>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Summary Stats --}}
                <div class="row g-3 mt-3">
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Pending</small>
                            <h4 class="mb-0 fw-bold" style="color: #ef4444;">{{ $pendingPickups->count() }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Accepted</small>
                            <h4 class="mb-0 fw-bold" style="color: #3b82f6;">{{ $acceptedPickups->count() }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">En Route</small>
                            <h4 class="mb-0 fw-bold" style="color: #f59e0b;">{{ $enRoutePickups->count() }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);">
                            <small style="color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">Completed Today</small>
                            <h4 class="mb-0 fw-bold" style="color: #10b981;">{{ \App\Models\PickupRequest::where('branch_id', $branchId)->whereDate('picked_up_at', today())->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-color: #334155 !important;">
                <button type="button" class="btn-outline-dash" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
