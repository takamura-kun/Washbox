@extends('admin.layouts.app')

@section('page-title', 'Customer Ratings Report')

@section('content')
<div class="container-fluid px-4 py-5">

    {{-- Header Section --}}
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-end">
            <div>
                <p class="text-muted mb-0" style="font-size: 0.95rem;">Track key metrics and business performance</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" title="Refresh data">
                    <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                </button>
                <button class="btn btn-outline-primary btn-sm" title="Export reports">
                    <i class="bi bi-download me-2"></i>Export
                </button>
            </div>
        </div>
    </div>

    {{-- Report Types Section --}}
    <div>
        <h5 class="mb-4 fw-bold rp-section-title">Available Reports</h5>
        <div class="row g-3">



            {{-- Branch Ratings Report --}}
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rp-report-card h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="rp-report-icon rp-report-icon--primary mb-4">
                            <i class="bi bi-building"></i>
                        </div>
                        <h6 class="mb-2 fw-bold rp-card-title">Branch Ratings Report</h6>
                        <p class="text-muted small mb-auto">View customer satisfaction ratings by branch</p>
                        <div class="d-flex justify-content-between align-items-center mt-2 mb-2">
                            <span class="badge bg-primary">{{ number_format($stats['total_ratings'] ?? 0) }} ratings</span>
                        </div>
                        <a href="{{ route('admin.reports.branch-ratings') }}" class="btn btn-primary btn-sm w-100 mt-2">
                            View Report <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Customers Report --}}
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rp-report-card h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="rp-report-icon rp-report-icon--info mb-4">
                            <i class="bi bi-people"></i>
                        </div>
                        <h6 class="mb-2 fw-bold rp-card-title">Customers Report</h6>
                        <p class="text-muted small mb-auto">Customer demographics, activity, retention, and engagement data</p>
                        <a href="{{ route('admin.reports.customers') }}" class="btn btn-info btn-sm w-100 mt-3">
                            View Report <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>

<style>
/* ================================================================
   REPORTS INDEX — rp-* component styles
   Light + Dark Mode via [data-theme="dark"]
   ================================================================ */

.fw-500 { font-weight: 500; }

/* ── Section title ───────────────────────────────────────────────── */
.rp-section-title { color: #1e293b; }

/* ── Stat cards ──────────────────────────────────────────────────── */
.rp-stat-card {
    border-radius: 12px;
    background: #ffffff;
    position: relative;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.rp-stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    border-radius: 12px 12px 0 0;
}

.rp-stat-card[data-accent="primary"]::before { background: linear-gradient(90deg, #0d6efd, transparent); }
.rp-stat-card[data-accent="success"]::before { background: linear-gradient(90deg, #198754, transparent); }
.rp-stat-card[data-accent="info"]::before    { background: linear-gradient(90deg, #0dcaf0, transparent); }
.rp-stat-card[data-accent="warning"]::before { background: linear-gradient(90deg, #ffc107, transparent); }

.rp-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.10) !important;
}

.rp-stat-label {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    color: #64748b;
}

.rp-stat-value { color: #1e293b; }

/* Stat icons */
.rp-stat-icon {
    width: 48px; height: 48px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}
.rp-icon-primary { background: linear-gradient(135deg, #0d6efd, #0a58ca); }
.rp-icon-success { background: linear-gradient(135deg, #198754, #146c43); }
.rp-icon-info    { background: linear-gradient(135deg, #0dcaf0, #0aa2c0); }
.rp-icon-warning { background: linear-gradient(135deg, #ffc107, #e0a800); color: #333; }

/* ── Report cards ────────────────────────────────────────────────── */
.rp-report-card {
    border-radius: 12px;
    background: #ffffff;
    transition: transform 0.3s cubic-bezier(0.4,0,0.2,1),
                box-shadow 0.3s cubic-bezier(0.4,0,0.2,1);
}

.rp-report-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 32px rgba(0,0,0,0.12) !important;
}

.rp-card-title { color: #1e293b; }

/* Report icon wrapper */
.rp-report-icon {
    width: 56px; height: 56px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem;
    transition: transform 0.25s ease, background 0.25s ease;
}

.rp-report-icon--success { background: rgba(25,135,84,0.08);   color: #198754; }
.rp-report-icon--primary { background: rgba(13,110,253,0.08);  color: #0d6efd; }
.rp-report-icon--info    { background: rgba(13,202,240,0.10);  color: #0aa2c0; }
.rp-report-icon--warning { background: rgba(255,193,7,0.12);   color: #e0a800; }

.rp-report-card:hover .rp-report-icon--success { background: rgba(25,135,84,0.15);  transform: scale(1.08); }
.rp-report-card:hover .rp-report-icon--primary { background: rgba(13,110,253,0.15); transform: scale(1.08); }
.rp-report-card:hover .rp-report-icon--info    { background: rgba(13,202,240,0.18); transform: scale(1.08); }
.rp-report-card:hover .rp-report-icon--warning { background: rgba(255,193,7,0.20);  transform: scale(1.08); }

/* Button hover lift */
.btn-primary:hover,
.btn-success:hover,
.btn-info:hover,
.btn-warning:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* ================================================================
   DARK MODE
   ================================================================ */

/* Section title */
[data-theme="dark"] .rp-section-title { color: #f1f5f9; }

/* Stat cards */
[data-theme="dark"] .rp-stat-card {
    background: #1e293b;
    border-color: #334155 !important;
}

[data-theme="dark"] .rp-stat-card:hover {
    box-shadow: 0 12px 24px rgba(0,0,0,0.35) !important;
}

[data-theme="dark"] .rp-stat-label { color: #94a3b8; }
[data-theme="dark"] .rp-stat-value { color: #f1f5f9; }

/* Report cards */
[data-theme="dark"] .rp-report-card {
    background: #1e293b;
    border-color: #334155 !important;
}

[data-theme="dark"] .rp-report-card:hover {
    box-shadow: 0 16px 32px rgba(0,0,0,0.4) !important;
}

[data-theme="dark"] .rp-card-title { color: #f1f5f9; }

/* Report icon wrappers in dark */
[data-theme="dark"] .rp-report-icon--success { background: rgba(34,197,94,0.12);  color: #4ade80; }
[data-theme="dark"] .rp-report-icon--primary { background: rgba(59,130,246,0.12); color: #60a5fa; }
[data-theme="dark"] .rp-report-icon--info    { background: rgba(6,182,212,0.12);  color: #22d3ee; }
[data-theme="dark"] .rp-report-icon--warning { background: rgba(245,158,11,0.12); color: #fbbf24; }

[data-theme="dark"] .rp-report-card:hover .rp-report-icon--success { background: rgba(34,197,94,0.22);  }
[data-theme="dark"] .rp-report-card:hover .rp-report-icon--primary { background: rgba(59,130,246,0.22); }
[data-theme="dark"] .rp-report-card:hover .rp-report-icon--info    { background: rgba(6,182,212,0.22);  }
[data-theme="dark"] .rp-report-card:hover .rp-report-icon--warning { background: rgba(245,158,11,0.22); }

/* Text helpers */
[data-theme="dark"] .text-muted   { color: #94a3b8 !important; }
[data-theme="dark"] .text-dark    { color: #f1f5f9 !important; }
[data-theme="dark"] .text-success { color: #4ade80 !important; }

/* Outline buttons */
[data-theme="dark"] .btn-outline-secondary {
    border-color: #475569; color: #94a3b8;
}
[data-theme="dark"] .btn-outline-secondary:hover { background: #334155; color: #f1f5f9; border-color: #475569; }

[data-theme="dark"] .btn-outline-primary {
    border-color: #6366f1; color: #a5b4fc;
}
[data-theme="dark"] .btn-outline-primary:hover { background: #6366f1; color: #fff; }

/* Solid buttons */
[data-theme="dark"] .btn-primary { background: #3D3B6B; border-color: #3D3B6B; }
[data-theme="dark"] .btn-primary:hover { background: #2d2b4f; border-color: #2d2b4f; }

[data-theme="dark"] .btn-info { background: #0891b2; border-color: #0891b2; color: #fff; }
[data-theme="dark"] .btn-info:hover { background: #0e7490; border-color: #0e7490; }

[data-theme="dark"] .btn-warning { background: #d97706; border-color: #d97706; color: #fff; }
[data-theme="dark"] .btn-warning:hover { background: #b45309; border-color: #b45309; }

/* Badge */
[data-theme="dark"] .badge.bg-primary { background: #3D3B6B !important; color: #c4b5fd; }
</style>
@endsection
