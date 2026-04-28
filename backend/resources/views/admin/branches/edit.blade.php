@extends('admin.layouts.app')

@section('page-title', 'Edit Branch')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
/* ============================================================
   EDIT BRANCH — Precision Admin Form
   NO background-color overrides — layout handles all theming.
   Only structural/layout/component-specific styles here.
   ============================================================ */

.eb-page {
    font-family: 'Geist', system-ui, sans-serif;
    padding: 0 0 1rem;
}

/* ── Page header ── */
.eb-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.eb-back-btn {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    border: 1.5px solid var(--border-color, #e3e8f0);
    background: transparent;
    color: var(--text-secondary, #6b7280);
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.18s ease;
    flex-shrink: 0;
}

.eb-back-btn:hover {
    background: var(--card-bg, #fff);
    color: var(--text-primary, #0d1526);
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.eb-page-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--text-primary, #0d1526);
    letter-spacing: -0.03em;
    margin: 0;
}

.eb-branch-code {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    border: 1.5px solid var(--border-color, #e3e8f0);
    font-family: 'Geist Mono', monospace;
    font-size: 0.68rem;
    font-weight: 600;
    color: var(--text-secondary, #6b7280);
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

/* ── Layout grid ── */
.eb-grid {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 1rem;
    align-items: start;
}

/* ── Section card ── */
.eb-section {
    border: 1px solid var(--border-color, #e3e8f0);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 0.875rem;
    transition: box-shadow 0.18s ease;
}

.eb-section:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.07);
}

.eb-section-header {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border-color, #e3e8f0);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.eb-section-title {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text-primary, #0d1526);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.eb-section-title i { font-size: 0.8rem; opacity: 0.7; display: none; }

.eb-section-body {
    padding: 1rem;
}

/* ── Form field ── */
.eb-field { margin-bottom: 0.875rem; }
.eb-field:last-child { margin-bottom: 0; }

.eb-label {
    display: block;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-secondary, #6b7280);
    margin-bottom: 0.3rem;
}

.eb-label .req { color: #ef4444; margin-left: 2px; }

.eb-input,
.eb-textarea,
.eb-select {
    display: block;
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1.5px solid var(--border-color, #e3e8f0);
    border-radius: 8px;
    font-size: 0.82rem;
    font-family: 'Geist', sans-serif;
    color: var(--text-primary, #0d1526);
    background: transparent;
    outline: none;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
    appearance: none;
    -webkit-appearance: none;
}

.eb-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath fill='%236b7280' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.875rem center;
    padding-right: 2.25rem;
    cursor: pointer;
}

.eb-textarea { resize: vertical; line-height: 1.5; min-height: 60px; }

.eb-input:focus,
.eb-textarea:focus,
.eb-select:focus {
    border-color: #3d3b6b;
    box-shadow: 0 0 0 3px rgba(61,59,107,0.10);
}

.eb-input.is-invalid,
.eb-textarea.is-invalid,
.eb-select.is-invalid {
    border-color: #ef4444;
}

.eb-invalid-msg {
    font-size: 0.72rem;
    color: #ef4444;
    margin-top: 0.3rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.eb-hint {
    font-size: 0.72rem;
    color: var(--text-secondary, #6b7280);
    margin-top: 0.3rem;
}

.eb-input:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── Input group ── */
.eb-input-group {
    display: flex;
    align-items: stretch;
}

.eb-input-group .eb-input {
    border-radius: 0 9px 9px 0;
    border-left: none;
    flex: 1;
}

.eb-input-addon {
    display: flex;
    align-items: center;
    padding: 0 0.875rem;
    border: 1.5px solid var(--border-color, #e3e8f0);
    border-right: none;
    border-radius: 9px 0 0 9px;
    font-size: 0.8rem;
    color: var(--text-secondary, #6b7280);
    white-space: nowrap;
    background: transparent;
}

/* ── Toggle switch ── */
.eb-switch { position: relative; display: inline-block; width: 44px; height: 24px; flex-shrink: 0; }
.eb-switch input { opacity: 0; width: 0; height: 0; }
.eb-switch-slider {
    position: absolute; inset: 0;
    background: var(--border-color, #d1d5db);
    border-radius: 12px;
    cursor: pointer;
    transition: background 0.2s ease;
}
.eb-switch-slider::before {
    content: '';
    position: absolute;
    width: 18px; height: 18px;
    left: 3px; top: 3px;
    background: white;
    border-radius: 50%;
    transition: transform 0.2s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.eb-switch input:checked + .eb-switch-slider { background: #3d3b6b; }
.eb-switch input:checked + .eb-switch-slider::before { transform: translateX(20px); }

/* ── Hours table ── */
.eb-hours-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.85rem;
}

.eb-hours-table thead th {
    padding: 0.5rem 1rem;
    font-size: 0.63rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-secondary, #6b7280);
    border-bottom: 1px solid var(--border-color, #e3e8f0);
}

.eb-hours-table tbody tr {
    transition: opacity 0.2s ease;
}

.eb-hours-table tbody tr.closed { opacity: 0.4; }

.eb-hours-table tbody td {
    padding: 0.625rem 1rem;
    border-bottom: 1px solid var(--border-color, #e3e8f0);
    vertical-align: middle;
    color: var(--text-primary, #0d1526);
}

.eb-hours-table tbody tr:last-child td { border-bottom: none; }

.eb-day-name {
    font-weight: 600;
    text-transform: capitalize;
    font-size: 0.875rem;
}

.eb-time-input {
    width: 130px;
    height: 34px;
    padding: 0 0.75rem;
    border: 1.5px solid var(--border-color, #e3e8f0);
    border-radius: 7px;
    font-size: 0.8rem;
    font-family: 'Geist Mono', monospace;
    color: var(--text-primary, #0d1526);
    background: transparent;
    outline: none;
    transition: border-color 0.18s ease;
}

.eb-time-input:focus {
    border-color: #3d3b6b;
    box-shadow: 0 0 0 3px rgba(61,59,107,0.10);
}

.eb-time-input:disabled { opacity: 0.4; cursor: not-allowed; }

/* ── Map ── */
.eb-map-search {
    display: flex;
    gap: 0.4rem;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border-color, #e3e8f0);
    flex-wrap: wrap;
}

.eb-map-search .eb-input { flex: 1; min-width: 180px; margin: 0; }

.eb-map-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    height: 34px;
    padding: 0 0.875rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    font-family: 'Geist', sans-serif;
    border: none;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.18s ease;
}

.eb-map-btn-primary { background: #3d3b6b; color: white; }
.eb-map-btn-primary:hover { background: #2d2b5f; transform: translateY(-1px); }

.eb-map-btn-success { background: #059669; color: white; }
.eb-map-btn-success:hover { background: #047857; transform: translateY(-1px); }

.eb-map-btn-ghost {
    background: transparent;
    color: #ef4444;
    border: 1.5px solid rgba(239,68,68,0.3);
}
.eb-map-btn-ghost:hover { background: rgba(239,68,68,0.08); }

#geocode-status {
    font-size: 0.75rem;
    padding: 0 1.25rem 0.75rem;
    display: none;
}
.geocode-success { color: #059669; }
.geocode-error   { color: #ef4444; }
.geocode-loading { color: #3d3b6b; }

#branch-map {
    height: 240px;
    width: 100%;
    cursor: crosshair;
}

.eb-coord-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.625rem 1rem;
    border-top: 1px solid var(--border-color, #e3e8f0);
    flex-wrap: wrap;
    gap: 0.4rem;
}

.eb-coord-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.875rem;
    border-radius: 999px;
    font-family: 'Geist Mono', monospace;
    font-size: 0.72rem;
    font-weight: 500;
    border: 1.5px solid var(--border-color, #e3e8f0);
    color: var(--text-secondary, #6b7280);
    transition: all 0.2s ease;
}

.eb-coord-chip.set {
    border-color: rgba(5,150,105,0.3);
    color: #059669;
    background: rgba(5,150,105,0.06);
}

/* ── QR preview ── */
.eb-qr-preview {
    display: inline-flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.4rem;
    margin-bottom: 0.875rem;
}

.eb-qr-preview img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 10px;
    border: 1.5px solid var(--border-color, #e3e8f0);
}

.eb-qr-label {
    font-size: 0.68rem;
    color: var(--text-secondary, #6b7280);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

/* ── File input ── */
.eb-file-input {
    display: block;
    width: 100%;
    padding: 0.5rem 0.875rem;
    border: 1.5px dashed var(--border-color, #e3e8f0);
    border-radius: 9px;
    font-size: 0.82rem;
    font-family: 'Geist', sans-serif;
    color: var(--text-secondary, #6b7280);
    cursor: pointer;
    transition: border-color 0.18s ease;
}

.eb-file-input:hover { border-color: #3d3b6b; }

/* ── Info / Warning boxes ── */
.eb-info-box {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 0.625rem 0.75rem;
    border-radius: 8px;
    font-size: 0.72rem;
    line-height: 1.4;
    margin-top: 0.75rem;
}

.eb-info-box i { font-size: 0.85rem; flex-shrink: 0; margin-top: 1px; }

.eb-info-box.info {
    background: rgba(37,99,235,0.07);
    border: 1px solid rgba(37,99,235,0.15);
    color: var(--text-primary, #0d1526);
}

.eb-info-box.info i { color: #2563eb; }

.eb-info-box.warning {
    background: rgba(217,119,6,0.07);
    border: 1px solid rgba(217,119,6,0.18);
    color: var(--text-primary, #0d1526);
}

.eb-info-box.warning i { color: #d97706; }

.eb-info-box.danger {
    background: rgba(220,38,38,0.07);
    border: 1px solid rgba(220,38,38,0.15);
    color: var(--text-primary, #0d1526);
}

.eb-info-box.danger i { color: #dc2626; }

/* ── Right sidebar cards ── */
.eb-sidebar-card {
    border: 1px solid var(--border-color, #e3e8f0);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 0.875rem;
}

.eb-sidebar-header {
    padding: 0.625rem 1rem;
    border-bottom: 1px solid var(--border-color, #e3e8f0);
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--text-primary, #0d1526);
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.eb-sidebar-header i { opacity: 0.6; font-size: 0.8rem; }

.eb-sidebar-body { padding: 0.875rem 1rem; }

.eb-stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.35rem 0;
    border-bottom: 1px solid var(--border-color, #e3e8f0);
}

.eb-stat-row:last-child { border-bottom: none; }

.eb-stat-label {
    font-size: 0.72rem;
    color: var(--text-secondary, #6b7280);
}

.eb-stat-value {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--text-primary, #0d1526);
    font-family: 'Geist Mono', monospace;
}

.eb-stat-value.green { color: #059669; }

.eb-meta-label {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-secondary, #6b7280);
    margin-bottom: 0.2rem;
}

.eb-meta-value {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-primary, #0d1526);
    margin-bottom: 0.625rem;
    font-family: 'Geist Mono', monospace;
}

.eb-meta-value:last-child { margin-bottom: 0; }

/* ── Action buttons ── */
.eb-actions { display: flex; flex-direction: column; gap: 0.5rem; }

.eb-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    width: 100%;
    padding: 0.625rem 0.875rem;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    font-family: 'Geist', sans-serif;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: all 0.18s ease;
}

.eb-btn-primary {
    background: #3d3b6b;
    color: white;
}
.eb-btn-primary:hover {
    background: #2d2b5f;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(61,59,107,0.3);
    color: white;
    text-decoration: none;
}

.eb-btn-ghost {
    background: transparent;
    color: var(--text-secondary, #6b7280);
    border: 1.5px solid var(--border-color, #e3e8f0);
}
.eb-btn-ghost:hover {
    color: var(--text-primary, #0d1526);
    border-color: var(--text-secondary, #9ca3af);
    text-decoration: none;
}

.eb-btn-deactivate {
    background: transparent;
    color: #dc2626;
    border: 1.5px solid rgba(220,38,38,0.25);
}
.eb-btn-deactivate:hover {
    background: rgba(220,38,38,0.07);
    border-color: rgba(220,38,38,0.4);
}

.eb-btn-activate {
    background: transparent;
    color: #059669;
    border: 1.5px solid rgba(5,150,105,0.25);
}
.eb-btn-activate:hover {
    background: rgba(5,150,105,0.07);
    border-color: rgba(5,150,105,0.4);
}

/* ── Error alert ── */
.eb-error-alert {
    display: flex;
    align-items: flex-start;
    gap: 0.625rem;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    background: rgba(220,38,38,0.07);
    border: 1px solid rgba(220,38,38,0.2);
    margin-bottom: 1rem;
}

.eb-error-alert i { color: #dc2626; font-size: 1rem; flex-shrink: 0; margin-top: 1px; }

.eb-error-alert ul {
    margin: 0;
    padding-left: 1rem;
    font-size: 0.75rem;
    color: var(--text-primary, #0d1526);
}

/* ── Modal ── */
.eb-modal .modal-content {
    border: 1px solid var(--border-color, #e3e8f0);
    border-radius: 16px;
    box-shadow: 0 16px 48px rgba(0,0,0,0.15);
    overflow: hidden;
}

.eb-modal .modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border-color, #e3e8f0);
}

.eb-modal .modal-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-primary, #0d1526);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.eb-modal .modal-body {
    padding: 1.5rem;
    font-size: 0.875rem;
    color: var(--text-primary, #0d1526);
}

.eb-modal .modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color, #e3e8f0);
    gap: 0.5rem;
}

.eb-modal-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    height: 38px;
    padding: 0 1.1rem;
    border-radius: 9px;
    font-size: 0.825rem;
    font-weight: 600;
    font-family: 'Geist', sans-serif;
    cursor: pointer;
    border: none;
    transition: all 0.18s ease;
}

.eb-modal-btn-cancel {
    background: transparent;
    color: var(--text-secondary, #6b7280);
    border: 1.5px solid var(--border-color, #e3e8f0);
}
.eb-modal-btn-cancel:hover { background: var(--bg-color, #f8fafc); }

.eb-modal-btn-danger { background: #dc2626; color: white; }
.eb-modal-btn-danger:hover { background: #b91c1c; }

.eb-modal-btn-success { background: #059669; color: white; }
.eb-modal-btn-success:hover { background: #047857; }

/* ── Divider ── */
.eb-divider {
    border: none;
    border-top: 1px solid var(--border-color, #e3e8f0);
    margin: 1rem 0;
}

/* ── Responsive ── */
@media (max-width: 992px) {
    .eb-grid { grid-template-columns: 1fr; }
}

@media (max-width: 576px) {
    .eb-section-body { padding: 1rem; }
    .eb-map-search { flex-direction: column; }
    .eb-map-search .eb-input { min-width: 100%; }
}
</style>
@endpush

@section('content')
<div class="eb-page">

    {{-- ── BREADCRUMB / PAGE HEADER ── --}}
    <div class="eb-breadcrumb">
        <a href="{{ route('admin.branches.index') }}" class="eb-back-btn">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="eb-page-title">Branch Configuration</h1>
        <span class="eb-branch-code">
            <i class="bi bi-hash"></i>{{ $branch->code }}
        </span>
    </div>

    {{-- ── VALIDATION ERRORS ── --}}
    @if($errors->any())
        <div class="eb-error-alert">
            <i class="bi bi-exclamation-circle-fill"></i>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.branches.update', $branch->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="eb-grid">

            {{-- ══════════════════════════════════════
                 LEFT COLUMN — Main form sections
            ══════════════════════════════════════ --}}
            <div>

                {{-- ── GENERAL INFORMATION ── --}}
                <div class="eb-section">
                    <div class="eb-section-header">
                        <div>
                            <div class="eb-section-title">
                                <i class="bi bi-building"></i> General Information
                            </div>

                        </div>
                    </div>
                    <div class="eb-section-body">
                        <div class="row g-2 mb-0">

                            <div class="col-md-6">
                                <div class="eb-field">
                                    <label class="eb-label">Branch Name <span class="req">*</span></label>
                                    <input type="text"
                                           name="name"
                                           class="eb-input @error('name') is-invalid @enderror"
                                           value="{{ old('name', $branch->name) }}"
                                           required
                                           placeholder="e.g., WashBox Dumaguete">
                                    @error('name')
                                        <div class="eb-invalid-msg"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="eb-field">
                                    <label class="eb-label">Branch Code <span class="req">*</span></label>
                                    <input type="text"
                                           name="code"
                                           class="eb-input @error('code') is-invalid @enderror"
                                           value="{{ old('code', $branch->code) }}"
                                           required
                                           maxlength="10"
                                           placeholder="e.g., DGT"
                                           style="font-family:'Geist Mono',monospace;text-transform:uppercase;">
                                    <div class="eb-hint">Unique short code — max 10 characters</div>
                                    @error('code')
                                        <div class="eb-invalid-msg"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="eb-field">
                                    <label class="eb-label">Address <span class="req">*</span></label>
                                    <textarea name="address"
                                              class="eb-textarea @error('address') is-invalid @enderror"
                                              rows="2"
                                              required
                                              placeholder="Street address, barangay…">{{ old('address', $branch->address) }}</textarea>
                                    @error('address')
                                        <div class="eb-invalid-msg"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="eb-field">
                                    <label class="eb-label">City <span class="req">*</span></label>
                                    <input type="text" name="city"
                                           class="eb-input @error('city') is-invalid @enderror"
                                           value="{{ old('city', $branch->city) }}"
                                           required placeholder="e.g., Dumaguete City">
                                    @error('city')
                                        <div class="eb-invalid-msg"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="eb-field">
                                    <label class="eb-label">Province <span class="req">*</span></label>
                                    <input type="text" name="province"
                                           class="eb-input @error('province') is-invalid @enderror"
                                           value="{{ old('province', $branch->province) }}"
                                           required placeholder="e.g., Negros Oriental">
                                    @error('province')
                                        <div class="eb-invalid-msg"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="eb-field">
                                    <label class="eb-label">Phone Number <span class="req">*</span></label>
                                    <input type="text" name="phone"
                                           class="eb-input @error('phone') is-invalid @enderror"
                                           value="{{ old('phone', $branch->phone) }}"
                                           required placeholder="09XXXXXXXXX">
                                    @error('phone')
                                        <div class="eb-invalid-msg"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="eb-field">
                                    <label class="eb-label">Email Address</label>
                                    <input type="email" name="email"
                                           class="eb-input @error('email') is-invalid @enderror"
                                           value="{{ old('email', $branch->email) }}"
                                           placeholder="branch@washbox.com">
                                    @error('email')
                                        <div class="eb-invalid-msg"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="eb-field">
                                    <label class="eb-label">Branch Manager</label>
                                    <input type="text" name="manager"
                                           class="eb-input @error('manager') is-invalid @enderror"
                                           value="{{ old('manager', $branch->manager) }}"
                                           placeholder="Full name">
                                    @error('manager')
                                        <div class="eb-invalid-msg"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="eb-field">
                                    <label class="eb-label">Operating Status</label>
                                    <select name="is_active" class="eb-select @error('is_active') is-invalid @enderror">
                                        <option value="1" {{ old('is_active', $branch->is_active) ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ !old('is_active', $branch->is_active) ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('is_active')
                                        <div class="eb-invalid-msg"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="alert alert-info mt-3">
                                    <h6 class="fw-bold mb-2"><i class="bi bi-shield-lock me-2"></i>Branch Login Credentials</h6>
                                    <p class="mb-2 small"><strong>Username:</strong> {{ $branch->username ?? 'Not set' }}</p>
                                    <p class="mb-0 small text-muted">All staff at this branch use these credentials to login. To change the password, use the button below.</p>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                        <i class="bi bi-key me-1"></i>Reset Branch Password
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ── BRANCH LOCATION / MAP ── --}}
                <div class="eb-section">
                    <div class="eb-section-header">
                        <div>
                            <div class="eb-section-title">
                                <i class="bi bi-geo-alt"></i> Branch Location
                            </div>

                        </div>
                    </div>

                    {{-- Search bar --}}
                    <div class="eb-map-search">
                        <input type="text"
                               id="map-search-input"
                               class="eb-input"
                               placeholder="Search location… e.g. Robinsons Dumaguete"
                               style="height:40px;">
                        <button type="button" class="eb-map-btn eb-map-btn-primary" id="btn-search-map">
                            <i class="bi bi-search"></i> Search
                        </button>
                        <button type="button" class="eb-map-btn eb-map-btn-success" id="btn-locate-address">
                            <i class="bi bi-crosshair"></i> Locate Address
                        </button>
                    </div>

                    <div id="geocode-status"></div>

                    <div id="branch-map"></div>

                    <div class="eb-coord-bar">
                        <span class="eb-coord-chip {{ ($branch->latitude && $branch->longitude) ? 'set' : '' }}" id="coord-display">
                            <i class="bi bi-pin-map{{ ($branch->latitude && $branch->longitude) ? '-fill' : '' }}"></i>
                            @if($branch->latitude && $branch->longitude)
                                {{ $branch->latitude }}, {{ $branch->longitude }}
                            @else
                                No location set
                            @endif
                        </span>
                        <button type="button"
                                class="eb-map-btn eb-map-btn-ghost"
                                id="btn-clear-location"
                                style="height:32px;padding:0 .75rem;font-size:.75rem;{{ ($branch->latitude && $branch->longitude) ? '' : 'display:none;' }}">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    </div>

                    <input type="hidden" name="latitude"  id="latitude"  value="{{ old('latitude',  $branch->latitude)  }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $branch->longitude) }}">
                </div>

            </div>{{-- /.left column --}}

            {{-- ══════════════════════════════════════
                 RIGHT SIDEBAR
            ══════════════════════════════════════ --}}
            <div>

                {{-- ── Branch Insights ── --}}
                <div class="eb-sidebar-card">
                    <div class="eb-sidebar-header">
                        <i class="bi bi-graph-up-arrow"></i> Branch Insights
                    </div>
                    <div class="eb-sidebar-body">
                        <div class="eb-stat-row">
                            <span class="eb-stat-label">Laundries (MTD)</span>
                            <span class="eb-stat-value">{{ $branch->laundries_mtd }}</span>
                        </div>
                        <div class="eb-stat-row">
                            <span class="eb-stat-label">Revenue (MTD)</span>
                            <span class="eb-stat-value green">₱{{ number_format($branch->revenue_mtd, 2) }}</span>
                        </div>
                        <div class="eb-stat-row">
                            <span class="eb-stat-label">Active Staff</span>
                            <span class="eb-stat-value">{{ $branch->active_staff }} members</span>
                        </div>
                    </div>
                </div>

                {{-- ── Historical Data ── --}}
                <div class="eb-sidebar-card">
                    <div class="eb-sidebar-header">
                        <i class="bi bi-clock-history"></i> Historical Data
                    </div>
                    <div class="eb-sidebar-body">
                        <div class="eb-meta-label">Created At</div>
                        <div class="eb-meta-value">{{ \Carbon\Carbon::parse($branch->created_at)->format('M d, Y h:i A') }}</div>
                        <div class="eb-meta-label">Last Updated</div>
                        <div class="eb-meta-value">{{ \Carbon\Carbon::parse($branch->updated_at)->format('M d, Y h:i A') }}</div>
                        <div class="eb-info-box warning" style="margin-top:0.5rem;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>Changing the branch code may affect tracking and reporting. Use caution.</span>
                        </div>
                    </div>
                </div>

                {{-- ── GCash Payment ── --}}
                <div class="eb-sidebar-card">
                    <div class="eb-sidebar-header">
                        <i class="bi bi-qr-code"></i> GCash Payment
                    </div>
                    <div class="eb-sidebar-body">
                        <div class="eb-field">
                            <label class="eb-label">Account Name</label>
                            <input type="text"
                                   name="gcash_account_name"
                                   class="eb-input @error('gcash_account_name') is-invalid @enderror"
                                   value="{{ old('gcash_account_name', $branch->gcash_account_name) }}"
                                   placeholder="e.g., WashBox Bais">
                            @error('gcash_account_name')
                                <div class="eb-invalid-msg"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="eb-field">
                            <label class="eb-label">Account Number</label>
                            <input type="text"
                                   name="gcash_account_number"
                                   class="eb-input @error('gcash_account_number') is-invalid @enderror"
                                   value="{{ old('gcash_account_number', $branch->gcash_account_number) }}"
                                   placeholder="09XXXXXXXXX"
                                   style="font-family:'Geist Mono',monospace;">
                            @error('gcash_account_number')
                                <div class="eb-invalid-msg"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="eb-field">
                            <label class="eb-label">QR Code Image</label>
                            @if($branch->gcash_qr_image)
                                <div class="eb-qr-preview">
                                    <img src="{{ asset('storage/gcash-qr/' . $branch->gcash_qr_image) }}"
                                         alt="Current QR Code">
                                    <span class="eb-qr-label">Current QR</span>
                                </div>
                            @endif
                            <input type="file"
                                   name="gcash_qr_image"
                                   class="eb-file-input @error('gcash_qr_image') is-invalid @enderror"
                                   accept="image/*">
                            <div class="eb-hint">JPG/PNG. Leave empty to keep current.</div>
                            @error('gcash_qr_image')
                                <div class="eb-invalid-msg"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="eb-info-box info" style="font-size:0.68rem;padding:0.5rem 0.625rem;margin-top:0.5rem;">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>If empty, uses default payment info</span>
                        </div>
                    </div>
                </div>

                {{-- ── Action Buttons ── --}}
                <div class="eb-sidebar-card">
                    <div class="eb-sidebar-body">
                        <div class="eb-actions">
                            <button type="submit" class="eb-btn eb-btn-primary">
                                <i class="bi bi-check-circle"></i> Save Changes
                            </button>
                            <a href="{{ route('admin.branches.index') }}" class="eb-btn eb-btn-ghost">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <hr class="eb-divider" style="margin:0.625rem 0;">
                            @if($branch->is_active)
                                <button type="button"
                                        class="eb-btn eb-btn-deactivate"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deactivateModal">
                                    <i class="bi bi-power"></i> Deactivate Branch
                                </button>
                            @else
                                <button type="button"
                                        class="eb-btn eb-btn-activate"
                                        data-bs-toggle="modal"
                                        data-bs-target="#activateModal">
                                    <i class="bi bi-power"></i> Activate Branch
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

            </div>{{-- /.sidebar --}}

        </div>{{-- /.eb-grid --}}
    </form>
</div>

{{-- ── DEACTIVATE MODAL ── --}}
<div class="modal fade eb-modal" id="deactivateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="bi bi-power" style="color:#dc2626;"></i> Confirm Deactivation
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="eb-info-box danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>Deactivating this branch will prevent new laundries and restrict access for branch users.</span>
                </div>
                <p style="font-size:.875rem;margin-top:1rem;margin-bottom:0;">
                    Are you sure you want to deactivate <strong>{{ $branch->name }}</strong>?
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="eb-modal-btn eb-modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.branches.deactivate', $branch->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="eb-modal-btn eb-modal-btn-danger">
                        <i class="bi bi-power me-1"></i> Deactivate
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── ACTIVATE MODAL ── --}}
<div class="modal fade eb-modal" id="activateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="bi bi-power" style="color:#059669;"></i> Confirm Activation
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.875rem;margin:0;">
                    Are you sure you want to activate <strong>{{ $branch->name }}</strong>?
                    This will allow new orders and restore access for branch users.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="eb-modal-btn eb-modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.branches.activate', $branch->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="eb-modal-btn eb-modal-btn-success">
                        <i class="bi bi-power me-1"></i> Activate
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Map setup ──────────────────────────────────────────
    const latInput      = document.getElementById('latitude');
    const lngInput      = document.getElementById('longitude');
    const coordDisplay  = document.getElementById('coord-display');
    const statusDiv     = document.getElementById('geocode-status');
    const clearBtn      = document.getElementById('btn-clear-location');
    const searchInput   = document.getElementById('map-search-input');
    const addressInput  = document.querySelector('textarea[name="address"]');
    const cityInput     = document.querySelector('input[name="city"]');
    const provinceInput = document.querySelector('input[name="province"]');

    const EXISTING_LAT = {{ $branch->latitude  ?? 9.3068  }};
    const EXISTING_LNG = {{ $branch->longitude ?? 123.3054 }};
    const HAS_COORDS   = {{ ($branch->latitude && $branch->longitude) ? 'true' : 'false' }};
    const DEFAULT_LAT  = 9.3068;
    const DEFAULT_LNG  = 123.3054;

    const map = L.map('branch-map').setView([EXISTING_LAT, EXISTING_LNG], HAS_COORDS ? 16 : 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap', maxZoom: 19
    }).addTo(map);

    let marker = null;

    function setMarker(lat, lng, flyTo = true) {
        lat = parseFloat(lat);
        lng = parseFloat(lng);
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                updateCoordinates(pos.lat, pos.lng);
            });
        }
        if (flyTo) map.flyTo([lat, lng], 17, { duration: 1.2 });
        updateCoordinates(lat, lng);
    }

    function updateCoordinates(lat, lng) {
        latInput.value = parseFloat(lat).toFixed(6);
        lngInput.value = parseFloat(lng).toFixed(6);
        coordDisplay.innerHTML = `<i class="bi bi-pin-map-fill"></i> ${parseFloat(lat).toFixed(6)}, ${parseFloat(lng).toFixed(6)}`;
        coordDisplay.className = 'eb-coord-chip set';
        clearBtn.style.display = 'inline-flex';
    }

    function showStatus(message, type) {
        statusDiv.style.display = 'block';
        statusDiv.className = `small mt-1 px-5 pb-2 geocode-${type}`;
        const icon = type === 'loading' ? 'bi-arrow-repeat' : type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle';
        statusDiv.innerHTML = `<i class="bi ${icon} me-1"></i>${message}`;
        if (type !== 'loading') setTimeout(() => { statusDiv.style.display = 'none'; }, 5000);
    }

    map.on('click', function (e) {
        setMarker(e.latlng.lat, e.latlng.lng, false);
        showStatus('Location set. Drag the marker to fine-tune.', 'success');
    });

    async function geocodeQuery(query) {
        showStatus('Searching…', 'loading');
        try {
            const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=1&countrycodes=ph`;
            const resp = await fetch(url, { headers: { 'User-Agent': 'WashBox Laundry Management' } });
            const data = await resp.json();
            if (!data || data.length === 0) {
                showStatus(`No results for "${query}". Try a different search or click the map.`, 'error');
                return false;
            }
            setMarker(data[0].lat, data[0].lon);
            const name = data[0].display_name.length > 80
                ? data[0].display_name.substring(0, 80) + '…'
                : data[0].display_name;
            showStatus(`Found: ${name}`, 'success');
            return true;
        } catch {
            showStatus('Geocoding failed. Click the map to set location manually.', 'error');
            return false;
        }
    }

    document.getElementById('btn-locate-address').addEventListener('click', function () {
        const parts = [
            addressInput?.value.trim(),
            cityInput?.value.trim(),
            provinceInput?.value.trim(),
            'Philippines'
        ].filter(Boolean);
        if (parts.length <= 1) {
            showStatus('Please fill in the Address and City fields first.', 'error');
            return;
        }
        geocodeQuery(parts.join(', '));
    });

    document.getElementById('btn-search-map').addEventListener('click', function () {
        const query = searchInput.value.trim();
        if (!query) return;
        geocodeQuery(query + ', Negros Oriental, Philippines');
    });

    searchInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btn-search-map').click(); }
    });

    clearBtn.addEventListener('click', function () {
        if (marker) { map.removeLayer(marker); marker = null; }
        latInput.value = '';
        lngInput.value = '';
        coordDisplay.innerHTML = '<i class="bi bi-pin-map"></i> No location set';
        coordDisplay.className = 'eb-coord-chip';
        clearBtn.style.display = 'none';
        map.setView([DEFAULT_LAT, DEFAULT_LNG], 13);
    });

    if (HAS_COORDS) setMarker(EXISTING_LAT, EXISTING_LNG, false);
    setTimeout(() => map.invalidateSize(), 300);


});
</script>
@endpush

{{-- Password Reset Modal --}}
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.branches.reset-password', $branch) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-key me-2"></i>Reset Branch Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This will reset the password for <strong>{{ $branch->name }}</strong>. All staff at this branch will need to use the new password to login.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
