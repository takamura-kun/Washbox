@extends('admin.layouts.app')

@section('title', 'Branches Management')
@section('page-title', 'BRANCH MANAGEMENT')

@section('content')
<div class="container-fluid px-4 py-2">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <p class="text-muted small mb-0" style="font-size:0.8rem;">Manage your laundry service locations across Negros Oriental</p>
        </div>
        <div>
            <a href="{{ route('admin.branches.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-2"></i>Add New Branch
            </a>

        </div>
    </div>

    {{-- Stats Overview --}}
    <div class="row g-2 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-primary text-white">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div class="bg-white bg-opacity-20 p-1 rounded-2" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-building" style="font-size:1rem;"></i>
                        </div>
                        @php
                            $activeBranches = $branches->where('is_active', true)->count();
                            $totalBranches = $branches->count();
                            $activePercentage = $totalBranches > 0 ? round(($activeBranches / $totalBranches) * 100) : 0;
                        @endphp
                        <span class="badge bg-success">{{ $activePercentage }}%</span>
                    </div>
                    <h6 class="mb-1 opacity-75" style="font-size:0.7rem;">Active Branches</h6>
                    <h3 class="fw-bold mb-0" style="font-size:1.5rem;">{{ $activeBranches }}/{{ $totalBranches }}</h3>
                    <small class="opacity-75" style="font-size: 0.68rem;">{{ $branches->where('is_active', false)->count() }} inactive</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-primary border-3 stat-card">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div class="bg-primary bg-opacity-10 p-1 rounded-2" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-box-seam text-primary" style="font-size:1rem;"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1" style="font-size:0.7rem;">Total Laundries</h6>
                    <h3 class="fw-bold mb-0" style="font-size:1.5rem;">{{ number_format($total_laundries) }}</h3>
                    <small class="text-muted" style="font-size: 0.68rem;">Network-wide</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-success border-3 stat-card">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div class="bg-success bg-opacity-10 p-1 rounded-2" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-cash-stack text-success" style="font-size:1rem;"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1" style="font-size:0.7rem;">Total Revenue</h6>
                    <h3 class="fw-bold mb-0" style="font-size:1.5rem;">₱{{ number_format($total_revenue, 2) }}</h3>
                    <small class="text-muted" style="font-size: 0.68rem;">Combined earnings</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-info border-3 stat-card">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div class="bg-info bg-opacity-10 p-1 rounded-2" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-people text-info" style="font-size:1rem;"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1" style="font-size:0.7rem;">Total Staff</h6>
                    <h3 class="fw-bold mb-0" style="font-size:1.5rem;">{{ $total_staff }}</h3>
                    <small class="text-muted" style="font-size: 0.68rem;">All branches</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Branch Cards --}}
    <div class="row g-3 mb-3">
        @forelse($branches as $branch)
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 branch-card branch-item-card">
                {{-- Branch Image --}}
                <div class="position-relative overflow-hidden" style="height: 160px; border-radius: 1rem 1rem 0 0;">
                    @if($branch->photo_url)
                        <img src="{{ Storage::url($branch->photo_url) }}" class="w-100 h-100" style="object-fit: cover;">
                    @else
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-primary">
                            <i class="bi bi-building text-white" style="font-size: 4rem; opacity: 0.3;"></i>
                        </div>
                    @endif

                    {{-- Status Badge --}}
                    <span class="badge position-absolute top-0 start-0 m-2 px-2 py-1"
                        style="background: {{ $branch->is_active ? '#10B981' : '#6B7280' }}; font-size: 0.68rem; font-weight: 600;">
                        <i class="bi bi-{{ $branch->is_active ? 'check-circle' : 'pause-circle' }} me-1"></i>
                        {{ $branch->is_active ? 'ACTIVE' : 'INACTIVE' }}
                    </span>

                    {{-- Quick Actions --}}
                    <div class="position-absolute top-0 end-0 m-2">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown" style="width: 32px; height: 32px; padding:0;">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('admin.branches.edit', $branch->id) }}">
                                    <i class="bi bi-pencil me-2"></i>Edit Details
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.branches.show', $branch->id) }}">
                                    <i class="bi bi-eye me-2"></i>View Analytics
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    @if($branch->is_active)
                                    <form action="{{ route('admin.branches.deactivate', $branch->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dropdown-item text-warning">
                                            <i class="bi bi-pause-circle me-2"></i>Deactivate
                                        </button>
                                    </form>
                                    @else
                                    <form action="{{ route('admin.branches.activate', $branch->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dropdown-item text-success">
                                            <i class="bi bi-play-circle me-2"></i>Activate
                                        </button>
                                    </form>
                                    @endif
                                </li>
                                <li>
                                    <form action="{{ route('admin.branches.destroy', $branch->id) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this branch? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-trash me-2"></i>Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Analytics Widget Overlay --}}
                    <div class="position-absolute bottom-0 end-0 m-2">
                        <div class="card shadow-sm" style="background: white; border-radius: 8px; border: none; min-width: 140px;">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold" style="font-size: 0.7rem; color: #333;">Analytics</h6>
                                    <a href="{{ route('admin.branches.analytics', $branch->id) }}" class="text-decoration-none" style="font-size: 0.65rem; color: #ff5c35;">View →</a>
                                </div>
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div style="flex: 1;">
                                            <div style="height: 2px; background: #e9ecef; border-radius: 2px; overflow: hidden;">
                                                <div style="height: 100%; width: {{ min(($branch->laundries_mtd / 100) * 100, 100) }}%; background: linear-gradient(90deg, #ff5c35, #ff8c42); border-radius: 2px;"></div>
                                            </div>
                                        </div>
                                        <div class="ms-2 text-end">
                                            <div class="fw-bold" style="font-size: 0.75rem; color: #333;">{{ $branch->laundries_mtd ?? 0 }}</div>
                                            <div style="font-size: 0.55rem; color: #6c757d;">Orders</div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div style="flex: 1;">
                                            <div style="height: 2px; background: #e9ecef; border-radius: 2px; overflow: hidden;">
                                                <div style="height: 100%; width: {{ min(($branch->revenue_mtd / 50000) * 100, 100) }}%; background: linear-gradient(90deg, #28a745, #5cb85c); border-radius: 2px;"></div>
                                            </div>
                                        </div>
                                        <div class="ms-2 text-end">
                                            <div class="fw-bold" style="font-size: 0.75rem; color: #333;">₱{{ number_format($branch->revenue_mtd ?? 0, 0) }}</div>
                                            <div style="font-size: 0.55rem; color: #6c757d;">Revenue</div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div style="flex: 1;">
                                            <div style="height: 2px; background: #e9ecef; border-radius: 2px; overflow: hidden;">
                                                <div style="height: 100%; width: {{ min(($branch->active_staff / 10) * 100, 100) }}%; background: linear-gradient(90deg, #17a2b8, #5bc0de); border-radius: 2px;"></div>
                                            </div>
                                        </div>
                                        <div class="ms-2 text-end">
                                            <div class="fw-bold" style="font-size: 0.75rem; color: #333;">{{ $branch->active_staff ?? 0 }}</div>
                                            <div style="font-size: 0.55rem; color: #6c757d;">Staff</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Branch Info --}}
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-1" style="font-size:1rem;">{{ $branch->name }}</h5>
                            <p class="text-muted small mb-0" style="font-size:0.75rem;">
                                <i class="bi bi-geo-alt-fill me-1"></i>
                                {{ Str::limit($branch->address, 30) }}, {{ $branch->city }}
                            </p>
                        </div>
                        <span class="badge bg-light text-dark border" style="font-size:0.68rem;">
                            {{ $branch->code ?? 'N/A' }}
                        </span>
                    </div>

                    {{-- ── Branch Rating ─────────────────────────────────────── --}}
                    @if($branch->total_ratings > 0)
                    <div class="branch-rating-box mb-2">
                        {{-- Score + stars + count --}}
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="branch-rating-score">{{ $branch->avg_rating }}</span>
                            <div class="d-flex gap-1">
                                @php $filled = (int) round($branch->avg_rating); @endphp
                                @for($s = 1; $s <= 5; $s++)
                                    <i class="bi bi-star{{ $s <= $filled ? '-fill' : '' }}"
                                       style="font-size: .8rem; color: {{ $s <= $filled ? '#f59e0b' : '#d1d5db' }};"></i>
                                @endfor
                            </div>
                            <span class="text-muted" style="font-size: .72rem;">
                                {{ $branch->total_ratings }} {{ $branch->total_ratings == 1 ? 'review' : 'reviews' }}
                            </span>
                        </div>
                        {{-- Per-star bar breakdown --}}
                        @foreach($branch->rating_distribution as $star => $count)
                        @php
                            $pct      = $branch->total_ratings > 0 ? round(($count / $branch->total_ratings) * 100) : 0;
                            $barColor = $star >= 4 ? '#f59e0b' : ($star == 3 ? '#94a3b8' : '#ef4444');
                        @endphp
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="branch-star-lbl">{{ $star }}<i class="bi bi-star-fill ms-1" style="font-size: .5rem; color: {{ $barColor }};"></i></span>
                            <div class="branch-bar-track flex-grow-1">
                                <div class="branch-bar-fill" style="width: {{ $pct }}%; background: {{ $barColor }};"></div>
                            </div>
                            <span class="branch-bar-count">{{ $count }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="mb-2">
                        <span class="text-muted" style="font-size: .78rem; font-style: italic;">
                            <i class="bi bi-star me-1"></i>No ratings yet
                        </span>
                    </div>
                    @endif
                    {{-- ── End Branch Rating ─────────────────────────────────── --}}

                    {{-- Contact Info --}}
                    <div class="mb-2 pb-2 border-bottom">
                        <div class="d-flex align-items-center text-muted mb-1" style="font-size:0.75rem;">
                            <i class="bi bi-telephone-fill me-2 text-primary"></i>
                            <span>{{ $branch->phone ?? 'N/A' }}</span>
                        </div>
                        @if($branch->email)
                        <div class="d-flex align-items-center text-muted mb-1" style="font-size:0.75rem;">
                            <i class="bi bi-envelope-fill me-2 text-primary"></i>
                            <span>{{ $branch->email }}</span>
                        </div>
                        @endif
                        @if($branch->manager)
                        <div class="d-flex align-items-center text-muted mb-1" style="font-size:0.75rem;">
                            <i class="bi bi-person-badge-fill me-2 text-primary"></i>
                            <span>Manager: {{ $branch->manager }}</span>
                        </div>
                        @endif
                        {{-- Operating Hours --}}
                        <div class="d-flex align-items-center text-muted" style="font-size:0.75rem;">
                            <i class="bi bi-clock-fill me-2 text-primary"></i>
                            <span class="{{ $branch->isOpen() ? 'text-success' : 'text-danger' }}">
                                @if($branch->isOpen())
                                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>Open Now
                                @else
                                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>Closed
                                @endif
                            </span>
                            <span class="ms-2">{{ $branch->getTodayHoursFormatted() }}</span>
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <div class="text-center p-2 bg-light rounded">
                                <div class="fw-bold" style="font-size:0.85rem;">{{ number_format($branch->laundries_mtd ?? 0) }}</div>
                                <small class="text-muted" style="font-size:0.68rem;">MTD Laundries</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-2 bg-light rounded">
                                <div class="fw-bold text-success" style="font-size:0.85rem;">₱{{ number_format($branch->revenue_mtd ?? 0, 0) }}</div>
                                <small class="text-muted" style="font-size:0.68rem;">MTD Revenue</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-2 bg-light rounded">
                                <div class="fw-bold text-primary" style="font-size:0.85rem;">{{ $branch->active_staff ?? 0 }}</div>
                                <small class="text-muted" style="font-size:0.68rem;">Active Staff</small>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.branches.analytics', $branch->id) }}"
                            class="btn btn-outline-primary btn-sm flex-fill" style="font-size:0.75rem;padding:0.375rem 0.5rem;">
                            <i class="bi bi-graph-up me-1"></i> Analytics
                        </a>
                        <a href="{{ route('admin.branches.edit', $branch->id) }}"
                            class="btn btn-outline-secondary btn-sm flex-fill" style="font-size:0.75rem;padding:0.375rem 0.5rem;">
                            <i class="bi bi-gear me-1"></i> Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 empty-state-card">
                <div class="card-body p-5 text-center">
                    <i class="bi bi-building" style="font-size: 4rem; opacity: 0.2;"></i>
                    <h5 class="fw-bold mt-3">No Branches Yet</h5>
                    <p class="text-muted mb-3">Start by adding your first branch location</p>
                    <a href="{{ route('admin.branches.create') }}" class="btn btn-primary me-2">
                        <i class="bi bi-plus-circle me-2"></i>Add Your First Branch
                    </a>

                </div>
            </div>
        </div>
        @endforelse
    </div>

    {{-- System Health Card --}}
    <div class="card border-0 shadow-sm rounded-4 health-card">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-success bg-opacity-10 p-2 rounded-3 me-3">
                            <i class="bi bi-shield-check fs-3 text-success"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1" style="font-size:1rem;">System-Wide Health</h5>
                            <h3 class="fw-bold text-success mb-0" style="font-size:1.5rem;">{{ $activePercentage }}% Operational</h3>
                        </div>
                    </div>
                    <p class="text-muted mb-0" style="font-size:0.8rem;">
                        @if($activePercentage == 100)
                        All branches are currently active with no reported issues. All systems operational and running smoothly.
                        @elseif($activePercentage >= 70)
                        Most branches are operational. {{ $branches->where('is_active', false)->count() }} branch(es) currently inactive.
                        @else
                        {{ $branches->where('is_active', false)->count() }} out of {{ $totalBranches }} branches are currently inactive.
                        @endif
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="fw-bold fs-5">{{ number_format($total_laundries) }}</div>
                                <small class="text-muted text-uppercase" style="font-size:0.68rem;">Total Laundries</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="fw-bold fs-5 text-success">₱{{ number_format($total_revenue / 1000, 1) }}k</div>
                                <small class="text-muted text-uppercase" style="font-size:0.68rem;">Total Revenue</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="fw-bold fs-5 text-primary">{{ $totalBranches }}</div>
                                <small class="text-muted text-uppercase" style="font-size:0.68rem;">Branches</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="fw-bold fs-5 text-info">{{ $total_staff }}</div>
                                <small class="text-muted text-uppercase" style="font-size:0.68rem;">Staff</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Floating Action Button --}}
<button class="btn btn-primary shadow-lg" id="fabButton" data-bs-toggle="modal" data-bs-target="#addBranchModal"
    style="position: fixed; bottom: 24px; right: 24px; width: 56px; height: 56px; border-radius: 50%; z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 0;">
    <i class="bi bi-plus-lg" style="font-size: 1.5rem;"></i>
</button>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate rating bars on page load
    document.querySelectorAll('.branch-bar-fill').forEach(function(el) {
        var target = el.style.width;
        el.style.width = '0';
        requestAnimationFrame(function() { el.style.width = target; });
    });

    // Format phone number in modal
    const phoneInput = document.querySelector('#addBranchModal input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 4) {
                    value = value;
                } else if (value.length <= 7) {
                    value = value.slice(0, 4) + ' ' + value.slice(4);
                } else if (value.length <= 11) {
                    value = value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7);
                } else {
                    value = value.slice(0, 11);
                }
            }
            e.target.value = value;
        });
    }

    // Handle modal errors
    @if($errors->any() && !session('from_modal'))
    setTimeout(() => {
        const modal = new bootstrap.Modal(document.getElementById('addBranchModal'));
        modal.show();
    }, 100);
    @endif
});
</script>
@endpush

@push('styles')
<style>
    /* Force card backgrounds for light mode */
    .stat-card,
    .branch-item-card,
    .empty-state-card,
    .health-card {
        background-color: #ffffff;
        color: #000000;
    }

    .stat-card .card-body,
    .branch-item-card .card-body,
    .empty-state-card .card-body,
    .health-card .card-body {
        background-color: #ffffff;
    }

    /* Dark theme overrides */
    [data-theme="dark"] .stat-card,
    [data-theme="dark"] .branch-item-card,
    [data-theme="dark"] .empty-state-card,
    [data-theme="dark"] .health-card {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }

    [data-theme="dark"] .stat-card .card-body,
    [data-theme="dark"] .branch-item-card .card-body,
    [data-theme="dark"] .empty-state-card .card-body,
    [data-theme="dark"] .health-card .card-body {
        background-color: #1e293b !important;
        color: #f1f5f9 !important;
    }

    [data-theme="dark"] .stat-card h2,
    [data-theme="dark"] .stat-card h5,
    [data-theme="dark"] .stat-card h6,
    [data-theme="dark"] .stat-card .fw-bold,
    [data-theme="dark"] .branch-item-card h5,
    [data-theme="dark"] .branch-item-card .fw-bold,
    [data-theme="dark"] .empty-state-card h5,
    [data-theme="dark"] .health-card h5 {
        color: #f1f5f9 !important;
    }

    [data-theme="dark"] .bg-light {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }

    [data-theme="dark"] .badge.bg-light {
        background-color: rgba(255, 255, 255, 0.1) !important;
        color: #e2e8f0 !important;
        border-color: rgba(255, 255, 255, 0.2) !important;
    }

    .branch-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .branch-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
    }

    .badge {
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* ── Branch Rating Block ───────────────────────────── */
    .branch-rating-box {
        background: #fffbeb;
        border: 1px solid #fef3c7;
        border-radius: 12px;
        padding: 10px 12px;
    }

    [data-theme="dark"] .branch-rating-box {
        background: rgba(245, 158, 11, 0.08);
        border-color: rgba(245, 158, 11, 0.2);
    }

    .branch-rating-score {
        font-size: 1.4rem;
        font-weight: 900;
        color: #f59e0b;
        line-height: 1;
    }

    .branch-star-lbl {
        font-size: .65rem;
        font-weight: 700;
        width: 20px;
        text-align: right;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 2px;
    }

    [data-theme="light"] .branch-star-lbl {
        color: #64748b;
    }

    [data-theme="dark"] .branch-star-lbl {
        color: #94a3b8;
    }

    .branch-bar-track {
        height: 6px;
        border-radius: 99px;
        overflow: hidden;
    }

    [data-theme="light"] .branch-bar-track {
        background: #e2e8f0;
    }

    [data-theme="dark"] .branch-bar-track {
        background: #334155;
    }

    .branch-bar-fill {
        height: 100%;
        border-radius: 99px;
        transition: width 0.7s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .branch-bar-count {
        font-size: .65rem;
        font-weight: 700;
        width: 16px;
        text-align: right;
        flex-shrink: 0;
    }

    [data-theme="light"] .branch-bar-count {
        color: #94a3b8;
    }

    [data-theme="dark"] .branch-bar-count {
        color: #64748b;
    }
</style>
@endpush
@endsection
