@extends('admin.layouts.app')

@section('page-title', 'Promotions Management')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted small">Manage discounts and banners for Sibulan, Dumaguete, and Bais branches.</p>
        </div>

        {{-- Enhanced Create Button with Dropdown --}}
        <div class="dropdown">
            <button class="btn btn-primary px-4 shadow-sm dropdown-toggle"
                style="background: #3D3B6B; border: none;"
                type="button"
                data-bs-toggle="dropdown">
                <i class="bi bi-plus-lg me-2"></i>Create Promotion
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                <li>
                    <a class="dropdown-item py-2" href="{{ route('admin.promotions.create', ['mode' => 'poster']) }}">
                        <i class="bi bi-image text-primary me-2"></i>
                        <strong>Poster Promotion</strong>
                        <small class="d-block text-muted ms-4">Visual poster with pricing (₱179, ₱209, etc.)</small>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>

                </li>
            </ul>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-success border-4" style="background-color: var(--card-bg) !important;">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="color: var(--text-primary) !important;">{{ $stats['active'] }}</h3>
                        <small class="text-muted fw-semibold" style="color: var(--text-secondary) !important;">Active Now</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-primary border-4" style="background-color: var(--card-bg) !important;">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                        <i class="bi bi-calendar-event fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="color: var(--text-primary) !important;">{{ $stats['scheduled'] }}</h3>
                        <small class="text-muted fw-semibold" style="color: var(--text-secondary) !important;">Scheduled</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-warning border-4" style="background-color: var(--card-bg) !important;">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="color: var(--text-primary) !important;">{{ $stats['total_usage'] }}</h3>
                        <small class="text-muted fw-semibold" style="color: var(--text-secondary) !important;">Total Redemptions</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-danger border-4" style="background-color: var(--card-bg) !important;">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-danger bg-opacity-10 text-danger p-3 rounded-3 me-3">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="color: var(--text-primary) !important;">{{ $stats['expired'] }}</h3>
                        <small class="text-muted fw-semibold" style="color: var(--text-secondary) !important;">Expired</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: var(--card-bg) !important;">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name or code..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Branches</option>
                        @foreach(\App\Models\Branch::all() as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Promotion Types</option>
                        <option value="percentage_discount" {{ request('type') === 'percentage_discount' ? 'selected' : '' }}>Percentage Discount</option>
                        <option value="fixed_discount" {{ request('type') === 'fixed_discount' ? 'selected' : '' }}>Fixed Amount</option>
                        <option value="poster_promo" {{ request('type') === 'poster_promo' ? 'selected' : '' }}>Poster Promotion</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <a href="{{ route('admin.promotions.index') }}" class="btn btn-sm btn-light border text-secondary w-100" style="background-color: var(--bg-color) !important; border-color: var(--border-color) !important; color: var(--text-secondary) !important;">Clear Filters</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background-color: var(--card-bg) !important;">
        <div class="promotion-grid mb-5" id="promotionCards">
            @forelse($promotions as $promo)
            @php
                // Fix: Check if it's a poster promotion OR fixed-price promotion with display_price
                $isPosterPromo = ($promo->type === 'poster_promo' || $promo->application_type === 'per_load_override') && $promo->display_price;
                // Fix: Get color gradient or use default
                $colorGradient = $promo->color_theme == 'blue'
                    ? 'linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%)'
                    : ($promo->color_theme == 'purple'
                        ? 'linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%)'
                        : ($promo->color_theme == 'green'
                            ? 'linear-gradient(135deg, #10B981 0%, #059669 100%)'
                            : 'linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%)'));

                // Calculate status
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
                    'Active' => '#10B981',
                    'Scheduled' => '#0EA5E9',
                    'Expired' => '#6B7280',
                    'Maxed Out' => '#F59E0B',
                    'Inactive' => '#EF4444',
                    default => '#6B7280'
                };
            @endphp
            <div class="promotion-row" data-active="{{ $promo->is_active ? '1' : '0' }}" data-id="{{ $promo->id }}">
                <div class="card promotion-card border-0 shadow-sm h-100 {{ !$promo->is_active ? 'opacity-75' : '' }}"
                     style="@if($promo->banner_image) background: url('{{ $promo->banner_image_url }}') center/cover; @elseif($isPosterPromo) background: {{ $colorGradient }}; @endif">

                    @if($promo->banner_image)
                    <div class="promo-image-overlay"></div>
                    @endif

                    <div class="promotion-header" style="@if($promo->banner_image || $isPosterPromo) background: transparent; border-bottom: 1px solid rgba(255,255,255,0.1); position: relative; z-index: 1; @else border-left: 5px solid {{ $statusColor }}; @endif">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge-promotion" style="background: {{ ($promo->banner_image || $isPosterPromo) ? 'rgba(255,255,255,0.2)' : $statusColor.'20' }}; color: {{ ($promo->banner_image || $isPosterPromo) ? 'white' : $statusColor }};">
                                    @if($isPosterPromo)
                                        <i class="bi bi-image me-1"></i>Poster
                                    @elseif($promo->discount_type === 'percentage')
                                        <i class="bi bi-percent me-1"></i>Discount
                                    @else
                                        <i class="bi bi-tag me-1"></i>Promotion
                                    @endif
                                </span>
                                <h5 class="fw-bold mt-2 mb-1" @if($promo->banner_image || $isPosterPromo) style="color: white;" @endif>{{ $promo->name }}</h5>
                                @if($promo->promo_code)
                                    <small @if($promo->banner_image || $isPosterPromo) style="color: rgba(255,255,255,0.8);" @else class="text-muted" @endif>
                                        Code: {{ $promo->promo_code }}
                                    </small>
                                @endif
                                @if($promo->featured)
                                    <span class="ms-2" style="color: #FFC107;"><i class="bi bi-star-fill"></i></span>
                                @endif
                            </div>
                            <div class="text-end">
                                @if($isPosterPromo)
                                    <div class="price-tag" style="color: {{ ($promo->banner_image || $isPosterPromo) ? 'white' : $statusColor }};">₱{{ number_format($promo->display_price, 0) }}</div>
                                    <div class="price-unit" @if($promo->banner_image || $isPosterPromo) style="color: rgba(255,255,255,0.8);" @endif>{{ $promo->price_unit }}</div>
                                @else
                                    <div class="price-tag" style="color: {{ ($promo->banner_image || $isPosterPromo) ? 'white' : $statusColor }};">
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

                    <div class="promotion-body" @if($promo->banner_image || $isPosterPromo) style="background: transparent; position: relative; z-index: 1;" @endif>
                        @if($promo->branch)
                            <div class="inclusion-item" @if($promo->banner_image || $isPosterPromo) style="border-bottom: 1px dashed rgba(255,255,255,0.2); color: rgba(255,255,255,0.9);" @endif>
                                <i class="bi bi-geo-alt-fill inclusion-icon" style="color: {{ ($promo->banner_image || $isPosterPromo) ? '#0EA5E9' : '#0EA5E9' }};"></i>
                                <span>{{ $promo->branch->name }} only</span>
                            </div>
                        @else
                            <div class="inclusion-item" @if($promo->banner_image || $isPosterPromo) style="border-bottom: 1px dashed rgba(255,255,255,0.2); color: rgba(255,255,255,0.9);" @endif>
                                <i class="bi bi-globe inclusion-icon" style="color: {{ ($promo->banner_image || $isPosterPromo) ? '#10B981' : '#10B981' }};"></i>
                                <span>All branches</span>
                            </div>
                        @endif

                        <div class="inclusion-item" @if($promo->banner_image || $isPosterPromo) style="border-bottom: 1px dashed rgba(255,255,255,0.2); color: rgba(255,255,255,0.9);" @endif>
                            <i class="bi bi-calendar-event inclusion-icon" style="color: {{ ($promo->banner_image || $isPosterPromo) ? '#F59E0B' : '#F59E0B' }};"></i>
                            <span>{{ $promo->start_date->format('M d') }} - {{ $promo->end_date->format('M d, Y') }}</span>
                        </div>

                        @if($promo->min_amount > 0)
                            <div class="inclusion-item" @if($promo->banner_image || $isPosterPromo) style="border-bottom: 1px dashed rgba(255,255,255,0.2); color: rgba(255,255,255,0.9);" @endif>
                                <i class="bi bi-cash inclusion-icon" style="color: {{ ($promo->banner_image || $isPosterPromo) ? '#8B5CF6' : '#8B5CF6' }};"></i>
                                <span>Min ₱{{ number_format($promo->min_amount, 0) }}</span>
                            </div>
                        @endif

                        @if($promo->description)
                            <div class="inclusion-item" @if($promo->banner_image || $isPosterPromo) style="border-bottom: 1px dashed rgba(255,255,255,0.2); color: rgba(255,255,255,0.9);" @endif>
                                <i class="bi bi-info-circle-fill inclusion-icon" style="color: {{ ($promo->banner_image || $isPosterPromo) ? '#0EA5E9' : '#0d6efd' }};"></i>
                                <span>{{ Str::limit($promo->description, 60) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="promotion-footer" @if($promo->banner_image || $isPosterPromo) style="background: rgba(0,0,0,0.3); border-top: 1px solid rgba(255,255,255,0.1); position: relative; z-index: 1;" @endif>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $status === 'Active' ? 'bg-success' : ($status === 'Scheduled' ? 'bg-info' : ($status === 'Expired' ? 'bg-secondary' : ($status === 'Maxed Out' ? 'bg-warning' : 'bg-danger'))) }}" style="font-size:11px;">
                                    {{ $status }}
                                </span>
                                <div class="toggle-switch {{ $promo->is_active ? 'active' : '' }} promotion-status-toggle"
                                     data-id="{{ $promo->id }}" style="transform:scale(.7);"></div>
                                <span class="badge bg-light text-muted" style="font-size:11px;">
                                    <i class="bi bi-graph-up me-1"></i>{{ $promo->usage_count }}/{{ $promo->max_usage ?? '∞' }}
                                </span>
                                @if($promo->roi_percentage !== null)
                                    @php $roiStatus = $promo->getROIStatus(); @endphp
                                    <span class="badge bg-{{ $roiStatus['color'] }}" style="font-size:11px;" title="Return on Investment">
                                        <i class="bi bi-currency-dollar me-1"></i>{{ $promo->getFormattedROI() }} ROI
                                    </span>
                                @endif
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.promotions.show', $promo) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="View Report">
                                    <i class="bi bi-bar-chart"></i>
                                </a>
                                <a href="{{ route('admin.promotions.edit', $promo) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.promotions.destroy', $promo) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this promotion?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Delete">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="empty-state text-center py-5" style="background-color: var(--card-bg) !important;">
                    <div class="text-muted mb-3"><i class="bi bi-megaphone display-4 opacity-25"></i></div>
                    <h6 class="fw-bold" style="color: var(--text-primary) !important;">No Promotions Found</h6>
                    <p class="small text-muted mb-3" style="color: var(--text-secondary) !important;">Try adjusting your filters or create a new campaign.</p>
                    <div class="btn-group">
                        <a href="{{ route('admin.promotions.create', ['mode' => 'poster']) }}"
                            class="btn btn-sm btn-primary" style="background: #3D3B6B; border: none;">
                            <i class="bi bi-image"></i> Poster Promotion
                        </a>
                        <a href="{{ route('admin.promotions.create', ['mode' => 'simple']) }}"
                            class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-percent"></i> Simple Discount
                        </a>
                    </div>
                </div>
            </div>
            @endforelse
        </div>
        @if($promotions->hasPages())
            <div class="card-footer py-3 border-top-0" style="background-color: var(--card-bg) !important;">
                {{ $promotions->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .x-small { font-size: 0.75rem; }
    .btn-white { background: #fff; }
    .btn-white:hover { background: #f8f9fa; }
    .table-hover tbody tr:hover { background-color: rgba(61, 59, 107, 0.02); }
    .stats-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; }
    .badge-sm { padding: 0.25rem 0.5rem; }

    /* Dropdown Menu Styling */
    .dropdown-menu {
        min-width: 300px;
        padding: 0.5rem 0;
    }

    .dropdown-item:hover {
        background-color: rgba(61, 59, 107, 0.05);
    }

    /* Promotion Grid Layout */
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

    /* Image overlay - theme aware */
    .promo-image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
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

    [data-theme="dark"] .promotion-header {
        background: var(--card-bg);
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

    [data-theme="dark"] .promotion-body {
        background: var(--card-bg);
    }

    .inclusion-item {
        display: flex;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px dashed #e5e7eb;
        font-size: 0.875rem;
    }

    [data-theme="dark"] .inclusion-item {
        border-bottom-color: var(--border-color);
    }

    .inclusion-item:last-child {
        border-bottom: none;
    }

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

    [data-theme="dark"] .promotion-footer {
        background: var(--bg-color);
        border-top-color: var(--border-color);
    }

    /* Toggle Switch */
    .toggle-switch {
        width: 40px;
        height: 20px;
        background: #cbd5e1;
        border-radius: 10px;
        position: relative;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .toggle-switch::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        background: white;
        border-radius: 50%;
        top: 2px;
        left: 2px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .toggle-switch.active {
        background: #10b981;
    }

    .toggle-switch.active::after {
        left: 22px;
    }

    /* Empty State */
    .empty-state {
        padding: 3rem;
        text-align: center;
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Promotion Status Toggle
    document.querySelectorAll('.promotion-status-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const promotionId = this.getAttribute('data-id');
            const isActive = this.classList.contains('active');
            const row = this.closest('.promotion-row');
            const statusBadge = row.querySelector('.badge.bg-success, .badge.bg-info, .badge.bg-secondary, .badge.bg-warning, .badge.bg-danger');

            // Optimistic update
            if (isActive) {
                this.classList.remove('active');
                statusBadge.classList.remove('bg-success', 'bg-info');
                statusBadge.classList.add('bg-danger');
                statusBadge.textContent = 'Inactive';
                row.dataset.active = '0';
            } else {
                this.classList.add('active');
                statusBadge.classList.remove('bg-danger', 'bg-secondary');
                statusBadge.classList.add('bg-success');
                statusBadge.textContent = 'Active';
                row.dataset.active = '1';
            }

            fetch(`/admin/promotions/${promotionId}/toggle-status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ is_active: !isActive })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.message || 'Error updating status'); });
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Error updating status');
                // Show success message if needed
                console.log('Promotion status updated successfully!');
            })
            .catch(error => {
                console.error('Error:', error);
                // Revert changes
                if (isActive) {
                    this.classList.add('active');
                    statusBadge.classList.add('bg-success');
                    statusBadge.classList.remove('bg-danger');
                    statusBadge.textContent = 'Active';
                    row.dataset.active = '1';
                } else {
                    this.classList.remove('active');
                    statusBadge.classList.remove('bg-success');
                    statusBadge.classList.add('bg-danger');
                    statusBadge.textContent = 'Inactive';
                    row.dataset.active = '0';
                }
                alert('Error updating promotion status: ' + error.message);
            });
        });
    });
});
</script>
@endsection
