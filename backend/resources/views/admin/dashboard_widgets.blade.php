{{-- ════════════════════════════════════════════════════════════════
     dashboard_widgets.blade.php
     Included at the bottom of dashboard.blade.php
     Contains: Branch Pipeline, Pickup Pipeline, Laundry Status Chart
════════════════════════════════════════════════════════════════ --}}


{{-- ══════════════════════════════════════════
     LAUNDRY STATUS + PIPELINE BY BRANCH
══════════════════════════════════════════ --}}
<div class="row g-3 mb-3">

    {{-- Laundries Status Chart --}}
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-slate-800">
                        <i class="bi bi-cart me-2"></i>Laundry status trends
                    </h6>
                    <small>Lifecycle tracking this week</small>
                </div>
            </div>
            <div class="card-body-modern">
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="laundriesStatusChart"
                        role="img"
                        aria-label="Line chart showing laundry status trends per status over the week">
                        Laundry status trend for the past 7 days by status type.
                    </canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Laundry Pipeline by Branch --}}
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:28px;height:28px;border-radius:6px;background:rgba(59,130,246,0.15);
                                color:#3b82f6;display:flex;align-items:center;justify-content:center;font-size:0.85rem;">
                        <i class="bi bi-shop"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Pipeline by branch</h6>
                        <small class="text-muted">Laundry status per branch</small>
                    </div>
                </div>
                <a href="{{ route('admin.laundries.index') }}" class="dash-btn dash-btn-info" style="font-size:0.6rem;padding:3px 8px;">View all</a>
            </div>
            <div class="card-body-modern">
                @if(empty($stats['branchPipeline']))
                    <div class="text-center py-4">
                        <i class="bi bi-shop text-muted" style="font-size:2rem;"></i>
                        <p class="text-muted" style="font-size:0.72rem;margin-top:8px;margin-bottom:0;">No branch pipeline data</p>
                    </div>
                @else
                    @php
                        $pipelineStatuses = [
                            'received'  => ['label' => 'Received',  'color' => '#60a5fa'],
                            'ready'     => ['label' => 'Ready',     'color' => '#22d3ee'],
                            'paid'      => ['label' => 'Paid',      'color' => '#10b981'],
                            'completed' => ['label' => 'Completed', 'color' => '#34d399'],
                            'cancelled' => ['label' => 'Cancelled', 'color' => '#f87171'],
                        ];
                        $branchColors = ['#3b82f6','#6366f1','#06b6d4','#10b981','#f59e0b','#ef4444'];
                    @endphp
                    @foreach($stats['branchPipeline'] as $branch)
                    @php
                        $accent     = $branchColors[$loop->index % count($branchColors)];
                        $branchTot  = max($branch['total'], 1);
                    @endphp
                    <div class="mb-3 p-2" style="border-radius:8px;border-left:3px solid {{ $accent }};background:rgba(0,0,0,0.02);">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:24px;height:24px;border-radius:50%;background:{{ $accent }};
                                            color:#fff;display:flex;align-items:center;justify-content:center;
                                            font-size:0.65rem;font-weight:600;flex-shrink:0;">
                                    {{ strtoupper(substr($branch['name'], 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-size:0.75rem;font-weight:600;">{{ $branch['name'] }}</div>
                                    <div class="text-muted" style="font-size:0.62rem;">{{ $branch['total'] }} laundries</div>
                                </div>
                            </div>
                            <a href="{{ route('admin.laundries.index', ['branch' => $branch['id']]) }}"
                               style="font-size:0.6rem;color:#3b82f6;">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </div>
                        {{-- Stacked progress bar --}}
                        <div style="height:5px;border-radius:3px;overflow:hidden;background:rgba(0,0,0,0.1);display:flex;margin-bottom:6px;">
                            @foreach($pipelineStatuses as $statusKey => $def)
                            @php $pct = round(($branch['statuses'][$statusKey] / $branchTot) * 100, 1); @endphp
                            @if($pct > 0)
                            <div style="width:{{ $pct }}%;background:{{ $def['color'] }};height:100%;"></div>
                            @endif
                            @endforeach
                        </div>
                        {{-- Status counts --}}
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach($pipelineStatuses as $statusKey => $def)
                            @php $cnt = $branch['statuses'][$statusKey] ?? 0; @endphp
                            <a href="{{ route('admin.laundries.index', ['branch' => $branch['id'], 'status' => $statusKey]) }}"
                               class="text-decoration-none"
                               style="font-size:0.6rem;color:{{ $def['color'] }};background:rgba(0,0,0,0.02);
                                      padding:1px 5px;border-radius:3px;border:1px solid {{ $def['color'] }}33;">
                                {{ $def['label'] }}: {{ $cnt }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════
     PICKUP PIPELINE BY BRANCH

<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="modern-card" style="background:#0f172a;">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:30px;height:30px;border-radius:7px;background:rgba(245,158,11,0.15);
                                color:#fbbf24;display:flex;align-items:center;justify-content:center;font-size:0.9rem;">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold" style="color:#f1f5f9;">Pickup pipeline by branch</h6>
                        <small style="color:#94a3b8;">Live pickup request status per branch</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @php
                        $pbpDefs = [
                            'pending'   => ['label' => 'Pending',   'color' => '#fbbf24'],
                            'accepted'  => ['label' => 'Accepted',  'color' => '#22d3ee'],
                            'en_route'  => ['label' => 'En route',  'color' => '#818cf8'],
                            'picked_up' => ['label' => 'Picked up', 'color' => '#34d399'],
                            'cancelled' => ['label' => 'Cancelled', 'color' => '#f87171'],
                        ];
                    @endphp
                    <div class="d-none d-lg-flex gap-3">
                        @foreach($pbpDefs as $def)
                        <span style="display:flex;align-items:center;gap:4px;font-size:10px;color:#475569;">
                            <span style="width:7px;height:7px;border-radius:50%;background:{{ $def['color'] }};display:inline-block;"></span>
                            {{ $def['label'] }}
                        </span>
                        @endforeach
                    </div>
                    <a href="{{ route('admin.pickups.index') }}" class="dash-btn dash-btn-info" style="font-size:0.6rem;padding:3px 8px;">All pickups</a>
                </div>
            </div>
            <div class="card-body-modern">
                @if(empty($stats['pickupBranchPipeline']) || collect($stats['pickupBranchPipeline'])->sum('total') === 0)
                    <div class="text-center py-4">
                        <i class="bi bi-truck" style="font-size:2rem;color:#334155;"></i>
                        <p style="font-size:0.72rem;color:#475569;margin-top:8px;margin-bottom:0;">No pickup requests yet</p>
                    </div>
                @else
                    @php $pbpAccents = ['#f59e0b','#6366f1','#06b6d4','#10b981','#ef4444','#8b5cf6']; @endphp
                    <div class="row g-3">
                        @foreach($stats['pickupBranchPipeline'] as $branch)
                        @php
                            $accent    = $pbpAccents[$loop->index % count($pbpAccents)];
                            $bTotal    = max($branch['total'], 1);
                        @endphp
                        <div class="col-xl-4 col-md-6">
                            <div class="p-2" style="background:rgba(255,255,255,0.04);border-radius:8px;border-left:3px solid {{ $accent }};">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:24px;height:24px;border-radius:50%;background:{{ $accent }};
                                                    color:#fff;display:flex;align-items:center;justify-content:center;
                                                    font-size:0.65rem;font-weight:600;flex-shrink:0;">
                                            {{ strtoupper(substr($branch['name'], 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-size:0.75rem;font-weight:600;color:#f1f5f9;">{{ $branch['name'] }}</div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span style="font-size:0.62rem;color:#475569;">{{ $branch['total'] }} requests</span>
                                                @if(($branch['active'] ?? 0) > 0)
                                                <span style="font-size:0.6rem;background:rgba(74,222,128,0.15);color:#4ade80;
                                                             padding:1px 5px;border-radius:3px;">
                                                    <span style="width:5px;height:5px;border-radius:50%;background:#4ade80;
                                                                 display:inline-block;margin-right:2px;"></span>
                                                    {{ $branch['active'] }} active
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ route('admin.pickups.index', ['branch' => $branch['id']]) }}"
                                       style="font-size:0.6rem;color:#60a5fa;">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </div>
                             
                                <div style="height:5px;border-radius:3px;overflow:hidden;background:#1e293b;display:flex;margin-bottom:6px;">
                                    @foreach($pbpDefs as $statusKey => $def)
                                    @php $pct = $bTotal > 1 ? round(($branch['statuses'][$statusKey] / $bTotal) * 100, 1) : 0; @endphp
                                    @if($pct > 0)
                                    <div style="width:{{ $pct }}%;background:{{ $def['color'] }};height:100%;"></div>
                                    @endif
                                    @endforeach
                                </div>
                               
                                <div class="d-flex gap-1 flex-wrap">
                                    @foreach($pbpDefs as $statusKey => $def)
                                    @php $cnt = $branch['statuses'][$statusKey] ?? 0; @endphp
                                    <a href="{{ route('admin.pickups.index', ['branch' => $branch['id'], 'status' => $statusKey]) }}"
                                       class="text-decoration-none text-center"
                                       style="font-size:0.58rem;color:{{ $def['color'] }};background:rgba(255,255,255,0.04);
                                              padding:2px 6px;border-radius:3px;border:1px solid {{ $def['color'] }}33;flex:1;min-width:48px;">
                                        <div style="font-size:0.8rem;font-weight:700;color:{{ $def['color'] }};">{{ $cnt }}</div>
                                        <div>{{ $def['label'] }}</div>
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

══════════════════════════════════════════ --}}
{{-- ══════════════════════════════════════════
     LOGISTICS MAP — Operations Panel
══════════════════════════════════════════ --}}
<div class="row g-3 mb-3">

    {{-- Pickup Management --}}
    <div class="col-lg-4">
        <div class="modern-card h-100">
            <div class="card-header-modern">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-truck text-warning me-2"></i>Pickup management
                </h6>
                <small>Select pickups for route optimization</small>
            </div>
            <div class="card-body-modern">
                <div class="d-grid gap-2 mb-3">
                    <button class="btn btn-sm btn-primary" onclick="autoRouteAllVisible()">
                        <i class="bi bi-magic me-1"></i>Auto-optimize all pending
                    </button>
                </div>
                <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-sm btn-outline-secondary flex-fill" onclick="selectAllPending()">
                        <i class="bi bi-check-square me-1"></i>Select all
                    </button>
                    <button class="btn btn-sm btn-outline-danger flex-fill" onclick="clearSelections()">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </button>
                </div>

                <div style="font-size:0.72rem;font-weight:600;color:#94a3b8;margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.3px;">
                    Status summary
                </div>

                @php
                    $pickupStatusLabels = [
                        'pending'   => ['label' => 'Pending',   'color' => '#fbbf24'],
                        'accepted'  => ['label' => 'Accepted',  'color' => '#22d3ee'],
                        'en_route'  => ['label' => 'En route',  'color' => '#818cf8'],
                        'picked_up' => ['label' => 'Picked up', 'color' => '#34d399'],
                        'cancelled' => ['label' => 'Cancelled', 'color' => '#f87171'],
                    ];
                @endphp
                @foreach($pickupStatusLabels as $statusKey => $cfg)
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2"
                    style="border-bottom:1px solid #1e293b;">
                    <div class="d-flex align-items-center gap-2">
                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $cfg['color'] }};
                                     display:inline-block;flex-shrink:0;"></span>
                        <div>
                            <div style="font-size:0.75rem;font-weight:500;color:#e2e8f0;">{{ $cfg['label'] }}</div>
                            <small style="font-size:0.6rem;color:#475569;">Pickup requests</small>
                        </div>
                    </div>
                    <div style="font-size:1.1rem;font-weight:700;color:{{ $cfg['color'] }};">
                        {{ $stats['pickupStats'][$statusKey] ?? 0 }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Logistics Map --}}
    <div class="col-lg-8">
        <div class="modern-card h-100">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-slate-800">
                    <i class="bi bi-geo-alt text-info me-2"></i>Logistics map
                </h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary" onclick="refreshMapMarkers()">
                        <i class="bi bi-geo-alt"></i> Refresh
                    </button>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mapModal">
                        <i class="bi bi-arrows-fullscreen"></i> Fullscreen
                    </button>
                </div>
            </div>
            <div class="card-body-modern p-0 position-relative" style="min-height:320px;">
                {{-- Address search overlay --}}
                <div id="address-search-overlay"
                    style="position:absolute;top:10px;right:10px;z-index:1000;max-width:340px;">
                    <div class="card shadow" style="background:#1e293b;border:1px solid #334155;">
                        <div class="card-body p-2">
                            <div class="input-group input-group-sm">
                                <input type="text" id="map-address-search"
                                    class="form-control"
                                    placeholder="Search address..."
                                    style="font-size:11px;background:#0f172a;border-color:#334155;color:#e2e8f0;">
                                <button class="btn btn-primary" onclick="searchMapAddress()">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </button>
                            </div>
                            <div id="search-result-display" class="mt-1" style="display:none;">
                                <div class="alert alert-success mb-0 py-1 px-2" style="font-size:11px;background:rgba(16,185,129,0.1);border-color:rgba(16,185,129,0.3);color:#4ade80;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong id="result-address-text" class="d-block"></strong>
                                            <small id="result-coords-text" class="text-muted"></small>
                                        </div>
                                        <button class="btn btn-sm btn-link p-0 text-decoration-none"
                                            onclick="document.getElementById('search-result-display').style.display='none'">
                                            <i class="bi bi-x-lg" style="color:#94a3b8;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="logisticsMap" class="admin-logistics-map" style="min-height:400px;height:400px;width:100%;border-radius:0 0 8px 8px;background:#f8f9fa;"></div>

                <div id="map-controls-container" style="position:absolute;top:10px;left:10px;z-index:1000;">
                    <div id="eta-display-container" style="display:none;margin-bottom:8px;"></div>
                    <div class="route-controls" style="display:none;">
                        <button class="route-btn btn-clear-route btn btn-sm btn-danger" onclick="clearRoute()">
                            <i class="bi bi-x-circle me-1"></i>Clear route
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════
     Laundries Status Chart — Script
══════════════════════════════════════════ --}}
<script>
    (function() {
        // Wait for Chart.js to load
        if (typeof Chart === 'undefined') {
            console.log('Chart.js not loaded yet, retrying...');
            return setTimeout(arguments.callee, 100);
        }

        const lsEl = document.getElementById('laundriesStatusChart');
        if (!lsEl) {
            console.error('laundriesStatusChart canvas not found');
            return;
        }
        
        // Check if data exists
        if (!window.LAUNDRIES_STATUS_DATA) {
            console.warn('LAUNDRIES_STATUS_DATA not found in window, using empty data');
            lsEl.closest('.card-body-modern').innerHTML =
                '<div class="text-center py-4" style="color:#475569;"><i class="bi bi-cart" style="font-size:1.5rem;opacity:.3;"></i><p style="font-size:0.72rem;margin-top:6px;">No laundry status data yet</p></div>';
            return;
        }

        const ls = window.LAUNDRIES_STATUS_DATA;
        
        if (!ls.datasets || ls.datasets.length === 0) {
            lsEl.closest('.card-body-modern').innerHTML =
                '<div class="text-center py-4" style="color:#475569;"><i class="bi bi-cart" style="font-size:1.5rem;opacity:.3;"></i><p style="font-size:0.72rem;margin-top:6px;">No laundry status data yet</p></div>';
            return;
        }

        const statusColors = {
            'Received':  '#818cf8',
            'Washing':   '#f59e0b',
            'Ready':     '#22d3ee',
            'Paid':      '#10b981',
            'Completed': '#4ade80',
            'Cancelled': '#f87171'
        };

        try {
            new Chart(lsEl, {
            type: 'line',
            data: {
                labels: ls.labels || [],
                datasets: ls.datasets.map((ds, i) => ({
                    label: ds.label,
                    data: ds.data,
                    borderColor: statusColors[ds.label] || '#94a3b8',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: statusColors[ds.label] || '#94a3b8',
                    pointBorderColor: '#0b1120',
                    pointBorderWidth: 2,
                    borderDash: i > 0 ? [5, 4] : []
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 10, font: { size: 10 }, color: '#475569' }
                    },
                    tooltip: { backgroundColor: 'rgba(10,14,28,0.95)', padding: 10 }
                },
                interaction: { intersect: false, mode: 'index' },
                scales: {
                    x: { grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false }, ticks: { color: '#334155', font: { size: 10 } } },
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
                         ticks: { color: '#334155', font: { size: 10 }, stepSize: 1, callback: v => Number.isInteger(v) ? v : '' } }
                }
            }
        });
        console.log('Chart rendered successfully');
        } catch (error) {
            console.error('Error rendering chart:', error);
            lsEl.closest('.card-body-modern').innerHTML =
                '<div class="text-center py-4" style="color:#dc2626;"><i class="bi bi-exclamation-triangle" style="font-size:1.5rem;"></i><p style="font-size:0.72rem;margin-top:6px;">Error loading chart</p></div>';
        }
    })();
</script>