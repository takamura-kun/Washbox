@extends('admin.layouts.app')

@section('page-title', 'Branch Details')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
.bd { font-family: inherit; padding-bottom: 2rem; }

.bd-crumb { display: flex; align-items: center; gap: 0.625rem; margin-bottom: 1rem; }

.bd-back {
    width: 30px; height: 30px;
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid var(--border-color, #e3e8f0);
    background: transparent;
    color: var(--text-secondary, #6b7280);
    text-decoration: none;
    font-size: 0.8rem;
    transition: all 0.15s;
    flex-shrink: 0;
}
.bd-back:hover { color: var(--text-primary, #111); text-decoration: none; }

.bd-title {
    font-size: 1rem; font-weight: 700;
    color: var(--text-primary, #111);
    margin: 0; letter-spacing: -0.02em;
}

.bd-code-pill {
    margin-left: auto;
    font-family: monospace;
    font-size: 0.68rem; font-weight: 600;
    letter-spacing: 0.1em; text-transform: uppercase;
    color: var(--text-secondary, #6b7280);
    border: 1px solid var(--border-color, #e3e8f0);
    padding: 0.2rem 0.65rem; border-radius: 999px;
}

.bd-grid {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 1rem;
    align-items: start;
}

.bd-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.bd-metric {
    border: 1px solid var(--border-color, #e3e8f0);
    border-radius: 11px;
    padding: 1rem;
    background: transparent;
}

.bd-metric-icon {
    width: 40px; height: 40px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
}

.bd-metric-icon.primary { background: rgba(61,59,107,0.1); color: #3d3b6b; }
.bd-metric-icon.success { background: rgba(16,185,129,0.1); color: #10b981; }
.bd-metric-icon.warning { background: rgba(245,158,11,0.1); color: #f59e0b; }
.bd-metric-icon.danger { background: rgba(239,68,68,0.1); color: #ef4444; }

.bd-metric-label {
    font-size: 0.7rem; font-weight: 600;
    color: var(--text-secondary, #6b7280);
    text-transform: uppercase; letter-spacing: 0.05em;
    margin-bottom: 0.3rem;
}

.bd-metric-value {
    font-size: 1.5rem; font-weight: 800;
    color: var(--text-primary, #111);
    line-height: 1;
}

.bd-metric-sub {
    font-size: 0.65rem;
    color: var(--text-secondary, #6b7280);
    margin-top: 0.25rem;
}

.bd-card {
    border: 1px solid var(--border-color, #e3e8f0);
    border-radius: 11px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.bd-card-head {
    padding: 0.6rem 0.75rem;
    border-bottom: 1px solid var(--border-color, #e3e8f0);
    background: var(--bg-color, #f8fafc);
}
.bd-card-head.compact {
    padding: 0.5rem 0.75rem;
}

.bd-card-title {
    font-size: 0.8rem; font-weight: 700;
    color: var(--text-primary, #111);
    display: flex; align-items: center; gap: 0.3rem;
    margin: 0;
}
.bd-card-title i { opacity: 0.6; }

.bd-card-body { padding: 0.75rem; }
.bd-card-body.compact { padding: 0.5rem 0.75rem; }

.bd-info-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.35rem 0;
    border-bottom: 1px solid var(--border-color, #e3e8f0);
    gap: 1rem;
}
.bd-info-row:last-child { border-bottom: none; }

.bd-info-label {
    font-size: 0.72rem;
    color: var(--text-secondary, #6b7280);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    min-width: 80px;
}

.bd-info-value {
    font-size: 0.8rem;
    color: var(--text-primary, #111);
    font-weight: 600;
    text-align: right;
    flex: 1;
}

.bd-info-value.success { color: #10b981; }
.bd-info-value.warning { color: #f59e0b; }
.bd-info-value.danger { color: #ef4444; }

.bd-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.3rem 0.6rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.bd-status-badge.active {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.bd-status-badge.inactive {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.bd-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8rem;
}

.bd-table thead th {
    padding: 0.625rem;
    font-size: 0.7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.05em;
    color: var(--text-secondary, #6b7280);
    border-bottom: 1px solid var(--border-color, #e3e8f0);
    text-align: left;
}

.bd-table tbody td {
    padding: 0.625rem;
    border-bottom: 1px solid var(--border-color, #e3e8f0);
    color: var(--text-primary, #111);
}
.bd-table tbody tr:last-child td { border-bottom: none; }

.bd-table tbody tr:hover {
    background: var(--bg-color, #f8fafc);
}

#branch-map-display { height: 280px; width: 100%; border-radius: 8px; }

.bd-coord-display {
    display: flex; align-items: center; justify-content: center;
    padding: 0.75rem;
    border-top: 1px solid var(--border-color, #e3e8f0);
    gap: 0.5rem;
}

.bd-coord-chip {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.3rem 0.6rem; border-radius: 999px;
    font-family: monospace; font-size: 0.7rem; font-weight: 500;
    border: 1px solid var(--border-color, #e3e8f0);
    color: var(--text-secondary, #6b7280);
}
.bd-coord-chip.set { border-color: rgba(5,150,105,0.3); color: #059669; }

.bd-hours-table { width: 100%; border-collapse: collapse; font-size: 0.75rem; }

.bd-hours-table thead th {
    padding: 0.5rem;
    font-size: 0.65rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.05em;
    color: var(--text-secondary, #6b7280);
    border-bottom: 1px solid var(--border-color, #e3e8f0);
}

.bd-hours-table tbody tr { transition: opacity 0.2s; }
.bd-hours-table tbody tr.closed { opacity: 0.4; }

.bd-hours-table tbody td {
    padding: 0.5rem;
    border-bottom: 1px solid var(--border-color, #e3e8f0);
    color: var(--text-primary, #111);
}
.bd-hours-table tbody tr:last-child td { border-bottom: none; }

.bd-day-name { font-weight: 600; text-transform: capitalize; }

.bd-time-display { font-family: monospace; font-size: 0.75rem; }

.bd-sidebar {
    border: 1px solid var(--border-color, #e3e8f0);
    border-radius: 11px; overflow: hidden; margin-bottom: 1rem;
}

.bd-sidebar-header {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border-color, #e3e8f0);
    background: var(--bg-color, #f8fafc);
    font-size: 0.8rem; font-weight: 700;
    color: var(--text-primary, #111);
    display: flex; align-items: center; gap: 0.4rem;
}
.bd-sidebar-header i { opacity: 0.6; }

.bd-sidebar-body { padding: 1rem; }

.bd-stat-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border-color, #e3e8f0);
}
.bd-stat-row:last-child { border-bottom: none; }

.bd-stat-label { font-size: 0.75rem; color: var(--text-secondary, #6b7280); }
.bd-stat-value { font-size: 0.85rem; font-weight: 700; color: var(--text-primary, #111); }
.bd-stat-value.success { color: #10b981; }
.bd-stat-value.warning { color: #f59e0b; }
.bd-stat-value.danger { color: #ef4444; }

.bd-actions { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem; }

.bd-btn {
    display: flex; align-items: center; justify-content: center; gap: 0.35rem;
    width: 100%; padding: 0.6rem 0.875rem; border-radius: 8px;
    font-size: 0.8rem; font-weight: 600; font-family: inherit;
    cursor: pointer; border: none; text-decoration: none; transition: all 0.15s;
}
.bd-btn:hover { text-decoration: none; }

.bd-btn-edit { background: #3d3b6b; color: white; }
.bd-btn-edit:hover { background: #2d2b5f; color: white; box-shadow: 0 3px 8px rgba(61,59,107,0.25); }

.bd-btn-back { background: transparent; color: var(--text-secondary, #6b7280); border: 1px solid var(--border-color, #e3e8f0); }
.bd-btn-back:hover { color: var(--text-primary, #111); border-color: var(--text-secondary, #9ca3af); }

.bd-meta-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-secondary, #6b7280); margin-bottom: 0.2rem; }
.bd-meta-value { font-size: 0.8rem; font-weight: 600; color: var(--text-primary, #111); margin-bottom: 0.5rem; }
.bd-meta-value:last-of-type { margin-bottom: 0; }

.bd-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

@media (max-width: 1024px) { .bd-grid { grid-template-columns: 1fr 250px; } }
@media (max-width: 860px) { .bd-grid { grid-template-columns: 1fr; } .bd-metrics { grid-template-columns: repeat(2, 1fr); } .bd-info-grid { grid-template-columns: 1fr; } }
@media (max-width: 576px) { .bd-metrics { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="bd">

    {{-- BREADCRUMB --}}
    <div class="bd-crumb">
        <a href="{{ route('admin.branches.index') }}" class="bd-back"><i class="bi bi-arrow-left"></i></a>
        <h1 class="bd-title">Branch Details</h1>
        <span class="bd-code-pill"><i class="bi bi-hash"></i>{{ $branch->code }}</span>
    </div>

    {{-- KEY METRICS --}}
    <div class="bd-metrics">
        <div class="bd-metric">
            <div class="bd-metric-icon primary"><i class="bi bi-people"></i></div>
            <div class="bd-metric-label">Total Customers</div>
            <div class="bd-metric-value">{{ $stats['total_customers'] ?? 0 }}</div>
            <div class="bd-metric-sub">Active customers</div>
        </div>

        <div class="bd-metric">
            <div class="bd-metric-icon success"><i class="bi bi-bag-check"></i></div>
            <div class="bd-metric-label">Total Laundries</div>
            <div class="bd-metric-value">{{ $stats['total_laundries'] ?? 0 }}</div>
            <div class="bd-metric-sub">All time</div>
        </div>

        <div class="bd-metric">
            <div class="bd-metric-icon warning"><i class="bi bi-cash-coin"></i></div>
            <div class="bd-metric-label">Total Revenue</div>
            <div class="bd-metric-value">₱{{ number_format($stats['total_revenue'] ?? 0, 0) }}</div>
            <div class="bd-metric-sub">All time</div>
        </div>

        <div class="bd-metric">
            <div class="bd-metric-icon primary"><i class="bi bi-calendar-month"></i></div>
            <div class="bd-metric-label">This Month</div>
            <div class="bd-metric-value">₱{{ number_format($stats['revenue_mtd'] ?? 0, 0) }}</div>
            <div class="bd-metric-sub">{{ $stats['laundries_mtd'] ?? 0 }} laundries</div>
        </div>

        <div class="bd-metric">
            <div class="bd-metric-icon success"><i class="bi bi-people-fill"></i></div>
            <div class="bd-metric-label">Active Staff</div>
            <div class="bd-metric-value">{{ $stats['active_staff'] ?? 0 }}</div>
            <div class="bd-metric-sub">Team members</div>
        </div>

        <div class="bd-metric">
            <div class="bd-metric-icon danger"><i class="bi bi-inbox"></i></div>
            <div class="bd-metric-label">Unclaimed</div>
            <div class="bd-metric-value">{{ $stats['unclaimed_count'] ?? 0 }}</div>
            <div class="bd-metric-sub">Pending pickup</div>
        </div>
    </div>

    <div class="bd-grid">

        {{-- ══ LEFT COLUMN ══ --}}
        <div>

            {{-- BRANCH INFO + LOCATION SIDE BY SIDE --}}
            <div class="bd-info-grid">

                {{-- BRANCH INFORMATION --}}
                <div class="bd-card">
                    <div class="bd-card-head compact">
                        <div class="bd-card-title"><i class="bi bi-building"></i> Branch Information</div>
                    </div>
                    <div class="bd-card-body compact">
                        <div class="bd-info-row">
                            <span class="bd-info-label">Name</span>
                            <span class="bd-info-value">{{ $branch->name }}</span>
                        </div>
                        <div class="bd-info-row">
                            <span class="bd-info-label">Code</span>
                            <span class="bd-info-value" style="font-family:monospace;">{{ $branch->code }}</span>
                        </div>
                        <div class="bd-info-row">
                            <span class="bd-info-label">Status</span>
                            <span class="bd-status-badge {{ $branch->is_active ? 'active' : 'inactive' }}">
                                <i class="bi bi-{{ $branch->is_active ? 'check-circle' : 'x-circle' }}"></i>
                                {{ $branch->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="bd-info-row">
                            <span class="bd-info-label">Manager</span>
                            <span class="bd-info-value">{{ $branch->manager ?: 'N/A' }}</span>
                        </div>
                        <div class="bd-info-row">
                            <span class="bd-info-label">Phone</span>
                            <span class="bd-info-value">{{ $branch->phone }}</span>
                        </div>
                        <div class="bd-info-row">
                            <span class="bd-info-label">Email</span>
                            <span class="bd-info-value">{{ $branch->email ?: 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                {{-- ADDRESS & LOCATION --}}
                <div class="bd-card">
                    <div class="bd-card-head compact">
                        <div class="bd-card-title"><i class="bi bi-geo-alt"></i> Location</div>
                    </div>
                    <div class="bd-card-body compact">
                        <div class="bd-info-row">
                            <span class="bd-info-label">Address</span>
                            <span class="bd-info-value">{{ $branch->address }}</span>
                        </div>
                        <div class="bd-info-row">
                            <span class="bd-info-label">City</span>
                            <span class="bd-info-value">{{ $branch->city }}</span>
                        </div>
                        <div class="bd-info-row">
                            <span class="bd-info-label">Province</span>
                            <span class="bd-info-value">{{ $branch->province }}</span>
                        </div>
                    </div>
                </div>

            </div>{{-- /.bd-info-grid --}}

            {{-- MAP --}}
            @if($branch->latitude && $branch->longitude)
            <div class="bd-card">
                <div class="bd-card-head">
                    <div class="bd-card-title"><i class="bi bi-map"></i> Map View</div>
                </div>
                <div id="branch-map-display"></div>
                <div class="bd-coord-display">
                    <span class="bd-coord-chip set">
                        <i class="bi bi-pin-map-fill"></i>
                        {{ $branch->latitude }}, {{ $branch->longitude }}
                    </span>
                </div>
            </div>
            @endif

            {{-- OPERATING HOURS + GCASH SIDE BY SIDE --}}
            <div class="bd-info-grid">

                {{-- OPERATING HOURS --}}
                <div class="bd-card" style="margin-bottom:0;">
                    <div class="bd-card-head">
                        <div class="bd-card-title"><i class="bi bi-clock"></i> Operating Hours</div>
                    </div>
                    <div class="bd-card-body">
                        @php
                            $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                            $branchHours = $branch->operating_hours ?? [];
                        @endphp
                        <table class="bd-hours-table">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Hours</th>
                                    <th style="text-align:center;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($days as $day)
                                    @php
                                        $dayHours = $branchHours[$day] ?? null;
                                        $isOpen = $dayHours !== 'closed' && $dayHours !== null;
                                        $openTime = is_array($dayHours) ? ($dayHours['open'] ?? '07:00') : '07:00';
                                        $closeTime = is_array($dayHours) ? ($dayHours['close'] ?? '20:00') : '20:00';
                                    @endphp
                                    <tr class="{{ !$isOpen ? 'closed' : '' }}">
                                        <td class="bd-day-name">{{ ucfirst(substr($day,0,3)) }}</td>
                                        <td>
                                            @if($isOpen)
                                                <span class="bd-time-display">{{ $openTime }} - {{ $closeTime }}</span>
                                            @else
                                                <span style="color: var(--text-secondary, #6b7280); font-style: italic;">Closed</span>
                                            @endif
                                        </td>
                                        <td style="text-align:center;">
                                            <i class="bi bi-{{ $isOpen ? 'check-circle text-success' : 'x-circle text-muted' }}"></i>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- GCASH PAYMENT --}}
                <div class="bd-card" style="margin-bottom:0;">
                    <div class="bd-card-head">
                        <div class="bd-card-title"><i class="bi bi-qr-code"></i> GCash Payment</div>
                    </div>
                    <div class="bd-card-body">
                        <div class="bd-info-row">
                            <span class="bd-info-label">Account Name</span>
                            <span class="bd-info-value">{{ $branch->gcash_account_name ?: 'Not set' }}</span>
                        </div>
                        <div class="bd-info-row">
                            <span class="bd-info-label">Account Number</span>
                            <span class="bd-info-value" style="font-family:monospace;">{{ $branch->gcash_account_number ?: 'Not set' }}</span>
                        </div>
                        @if($branch->gcash_qr_image)
                        <div class="bd-info-row">
                            <span class="bd-info-label">QR Code</span>
                            <img src="{{ asset('storage/gcash-qr/' . $branch->gcash_qr_image) }}" alt="QR Code" style="width: 80px; height: 80px; border-radius: 8px; border: 1px solid var(--border-color, #e3e8f0);">
                        </div>
                        @endif
                    </div>
                </div>

            </div>{{-- /.bd-info-grid --}}

        </div>{{-- /.left --}}

        {{-- ══ RIGHT SIDEBAR ══ --}}
        <div>

            {{-- QUICK STATS --}}
            <div class="bd-sidebar">
                <div class="bd-sidebar-header"><i class="bi bi-graph-up-arrow"></i> Quick Stats</div>
                <div class="bd-sidebar-body">
                    <div class="bd-stat-row">
                        <span class="bd-stat-label">Avg. Laundries/Day</span>
                        <span class="bd-stat-value">{{ $stats['avg_laundries_per_day'] ?? 0 }}</span>
                    </div>
                    <div class="bd-stat-row">
                        <span class="bd-stat-label">Avg. Revenue/Day</span>
                        <span class="bd-stat-value success">₱{{ number_format($stats['avg_revenue_per_day'] ?? 0, 0) }}</span>
                    </div>
                    <div class="bd-stat-row">
                        <span class="bd-stat-label">Repeat Customers</span>
                        <span class="bd-stat-value">{{ $stats['repeat_customers'] ?? 0 }}%</span>
                    </div>
                    <div class="bd-stat-row">
                        <span class="bd-stat-label">Avg. Rating</span>
                        <span class="bd-stat-value">{{ $stats['avg_rating'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="bd-actions">
                <a href="{{ route('admin.branches.edit', $branch->id) }}" class="bd-btn bd-btn-edit">
                    <i class="bi bi-pencil"></i> Edit Branch
                </a>
                <a href="{{ route('admin.branches.index') }}" class="bd-btn bd-btn-back">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>

            {{-- HISTORICAL DATA --}}
            <div class="bd-sidebar">
                <div class="bd-sidebar-header"><i class="bi bi-clock-history"></i> Historical Data</div>
                <div class="bd-sidebar-body">
                    <div class="bd-meta-label">Created At</div>
                    <div class="bd-meta-value">{{ \Carbon\Carbon::parse($branch->created_at)->format('M d, Y h:i A') }}</div>
                    <div class="bd-meta-label">Last Updated</div>
                    <div class="bd-meta-value">{{ \Carbon\Carbon::parse($branch->updated_at)->format('M d, Y h:i A') }}</div>
                    <div class="bd-meta-label">Days Operating</div>
                    <div class="bd-meta-value">{{ \Carbon\Carbon::parse($branch->created_at)->diffInDays(now()) }} days</div>
                </div>
            </div>

        </div>{{-- /.sidebar --}}

    </div>{{-- /.bd-grid --}}
</div>
@endsection

@push('scripts')
@if($branch->latitude && $branch->longitude)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lat = {{ $branch->latitude }};
    const lng = {{ $branch->longitude }};
    
    const map = L.map('branch-map-display').setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 19
    }).addTo(map);
    
    L.marker([lat, lng]).addTo(map);
    
    setTimeout(() => map.invalidateSize(), 300);
});
</script>
@endif
@endpush