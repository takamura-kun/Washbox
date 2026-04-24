{{-- ════════════════════════════════════════════════════════════════
     dashboard_people_rankings.blade.php
     People & Rankings Section - Dark Theme Cards
════════════════════════════════════════════════════════════════ --}}

<div class="row g-3 mb-3">
    <div class="col-12">
        <div style="color:#6b7280;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">
            PEOPLE & RANKINGS
        </div>
    </div>
</div>

<div class="row g-3 mb-3">

    {{-- Branch Rankings Card --}}
    <div class="col-lg-6">
        <div class="modern-card">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size:1.2rem;">🏆</span>
                    <div>
                        <h6 class="mb-0 fw-bold" style="font-size:0.85rem;">Branch rankings</h6>
                        <small class="text-muted" style="font-size:0.65rem;">{{ now()->format('F Y') }}</small>
                    </div>
                </div>
                <a href="{{ route('admin.branches.index') }}"
                   style="color:#2563eb;font-size:0.7rem;text-decoration:none;padding:4px 10px;border:1px solid rgba(37,99,235,0.2);border-radius:6px;background:rgba(59,130,246,0.1);">
                    View all
                </a>
            </div>
            <div class="card-body-modern" style="padding:16px;">
                @php
                    $branchRankings = \App\Models\Branch::withSum(['laundries' => function($q) {
                        $q->whereIn('status', ['paid', 'completed'])
                          ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    }], 'total_amount')
                    ->orderByDesc('laundries_sum_total_amount')
                    ->limit(3)
                    ->get();
                    $totalBranchRevenue = $branchRankings->sum('laundries_sum_total_amount');
                    $rankIcons = ['🥇', '🥈', '🥉'];
                @endphp

                @forelse($branchRankings as $idx => $branch)
                @php
                    $rev = $branch->laundries_sum_total_amount ?? 0;
                    $pct = $totalBranchRevenue > 0 ? round(($rev / $totalBranchRevenue) * 100, 1) : 0;
                @endphp
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span style="font-size:1.2rem;">{{ $rankIcons[$idx] ?? '🏢' }}</span>
                            <div>
                                <div style="font-size:0.85rem;font-weight:500;">{{ $branch->name }}</div>
                                <small class="text-muted" style="font-size:0.7rem;">This month</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <div style="font-size:0.9rem;font-weight:700;color:#16a34a;">₱{{ number_format($rev / 1000, 0) }}k</div>
                            <small class="text-muted" style="font-size:0.7rem;">{{ $pct }}% share</small>
                        </div>
                    </div>
                    <div style="height:4px;background:rgba(0,0,0,0.1);border-radius:2px;overflow:hidden;">
                        <div style="width:{{ $pct }}%;height:100%;background:#16a34a;border-radius:2px;"></div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="bi bi-building text-muted" style="font-size:2rem;opacity:0.5;"></i>
                    <p class="text-muted" style="font-size:0.75rem;margin-top:8px;margin-bottom:0;">No branch data yet</p>
                </div>
                @endforelse

                @if($branchRankings->count() > 0)
                <div class="mt-3 pt-3" style="border-top:1px solid rgba(0,0,0,0.1);">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted" style="font-size:0.7rem;">Total this month</small>
                        <span style="font-size:0.95rem;font-weight:700;color:#10b981;">₱{{ number_format($totalBranchRevenue / 1000, 0) }}k</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Top Customers Card --}}
    <div class="col-lg-6">
        <div class="modern-card">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size:1.2rem;">⭐</span>
                    <h6 class="mb-0 fw-bold" style="font-size:0.85rem;">Top customers</h6>
                </div>
                <a href="{{ route('admin.customers.index') }}"
                   style="color:#2563eb;font-size:0.7rem;text-decoration:none;padding:4px 10px;border:1px solid rgba(37,99,235,0.2);border-radius:6px;background:rgba(59,130,246,0.1);">
                    View all
                </a>
            </div>
            <div class="card-body-modern" style="padding:16px;">
                @php
                    $topCustomers = \App\Models\Customer::withSum(['laundries' => function($q) {
                        $q->whereIn('status', ['paid', 'completed']);
                    }], 'total_amount')
                    ->withCount('laundries')
                    ->orderByDesc('laundries_sum_total_amount')
                    ->limit(5)
                    ->get();
                @endphp

                @forelse($topCustomers as $idx => $customer)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:32px;height:32px;border-radius:50%;background:rgba(0,0,0,0.05);
                                display:flex;align-items:center;justify-content:center;
                                font-size:0.75rem;font-weight:600;flex-shrink:0;" class="text-muted">
                        #{{ $idx + 1 }}
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div style="font-size:0.85rem;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $customer->name }}
                        </div>
                        <div class="text-muted" style="font-size:0.7rem;">{{ $customer->laundries_count }} orders</div>
                    </div>
                    <div style="font-size:0.9rem;font-weight:700;color:#16a34a;flex-shrink:0;">
                        ₱{{ number_format(($customer->laundries_sum_total_amount ?? 0) / 1000, 1) }}k
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="bi bi-people text-muted" style="font-size:2rem;opacity:0.5;"></i>
                    <p class="text-muted" style="font-size:0.75rem;margin-top:8px;margin-bottom:0;">No customer data yet</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>


{{-- ══════════════════════════════════════════
     RECENT ORDERS & ACTIVITIES
══════════════════════════════════════════ --}}
<div class="row g-3 mb-3">
    {{-- Recent Orders --}}
    <div class="col-lg-8">
        <div class="modern-card">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:30px;height:30px;border-radius:7px;background:rgba(59,130,246,0.15);
                                color:#3b82f6;display:flex;align-items:center;justify-content:center;font-size:0.9rem;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-slate-800">Recent Laundry</h6>
                        <small class="text-muted">Latest laundries across all branches</small>
                    </div>
                </div>
                <a href="{{ route('admin.laundries.index') }}" class="dash-btn dash-btn-info" style="font-size:0.6rem;padding:3px 8px;">
                    <i class="bi bi-list me-1"></i>View all
                </a>
            </div>
            <div class="card-body-modern">
                @php
                    $branchId = request('branch_id');
                    $recentOrders = \App\Models\Laundry::with(['customer', 'branch', 'service'])
                        ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get();

                    $statusConfig = [
                        'received'  => ['label' => 'Received',  'color' => '#60a5fa', 'bg' => 'rgba(96,165,250,0.1)', 'icon' => 'bi-inbox-fill'],
                        'washing'   => ['label' => 'Washing',   'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.1)', 'icon' => 'bi-droplet-fill'],
                        'ready'     => ['label' => 'Ready',     'color' => '#22d3ee', 'bg' => 'rgba(34,211,238,0.1)', 'icon' => 'bi-check-circle-fill'],
                        'paid'      => ['label' => 'Paid',      'color' => '#10b981', 'bg' => 'rgba(16,185,129,0.1)', 'icon' => 'bi-credit-card-fill'],
                        'completed' => ['label' => 'Completed', 'color' => '#34d399', 'bg' => 'rgba(52,211,153,0.1)', 'icon' => 'bi-check2-all'],
                        'cancelled' => ['label' => 'Cancelled', 'color' => '#f87171', 'bg' => 'rgba(248,113,113,0.1)', 'icon' => 'bi-x-circle-fill'],
                    ];
                @endphp

                @if($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover" style="margin-bottom:0;">
                            <thead>
                                <tr style="border-bottom:2px solid rgba(0,0,0,0.1);">
                                    <th style="font-size:0.7rem;font-weight:600;color:#64748b;padding:10px 12px;">Order ID</th>
                                    <th style="font-size:0.7rem;font-weight:600;color:#64748b;padding:10px 12px;">Customer</th>
                                    <th style="font-size:0.7rem;font-weight:600;color:#64748b;padding:10px 12px;">Branch</th>
                                    <th style="font-size:0.7rem;font-weight:600;color:#64748b;padding:10px 12px;">Service</th>
                                    <th style="font-size:0.7rem;font-weight:600;color:#64748b;padding:10px 12px;text-align:right;">Amount</th>
                                    <th style="font-size:0.7rem;font-weight:600;color:#64748b;padding:10px 12px;text-align:center;">Status</th>
                                    <th style="font-size:0.7rem;font-weight:600;color:#64748b;padding:10px 12px;">Time</th>
                                    <th style="font-size:0.7rem;font-weight:600;color:#64748b;padding:10px 12px;text-align:center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                @php
                                    $statusDef = $statusConfig[$order->status] ?? ['label' => ucfirst($order->status), 'color' => '#94a3b8', 'bg' => 'rgba(148,163,184,0.1)', 'icon' => 'bi-circle-fill'];
                                @endphp
                                <tr style="border-bottom:1px solid rgba(0,0,0,0.05);transition:background 0.2s;"
                                    onmouseover="this.style.background='rgba(0,0,0,0.02)'"
                                    onmouseout="this.style.background='transparent'">
                                    <td style="padding:12px;">
                                        <a href="{{ route('admin.laundries.show', $order->id) }}"
                                           class="text-decoration-none"
                                           style="font-size:0.75rem;font-weight:600;color:#3b82f6;">
                                            #{{ $order->id }}
                                        </a>
                                        @if($order->pickup_request_id)
                                        <span class="badge" style="background:rgba(245,158,11,0.15);color:#f59e0b;font-size:0.6rem;padding:2px 5px;margin-left:4px;">
                                            <i class="bi bi-truck" style="font-size:0.55rem;"></i> Pickup
                                        </span>
                                        @endif
                                    </td>
                                    <td style="padding:12px;">
                                        <div style="font-size:0.75rem;font-weight:500;color:#1e293b;">
                                            {{ $order->customer->name ?? 'Walk-in' }}
                                        </div>
                                        @if($order->customer && $order->customer->phone)
                                        <div style="font-size:0.62rem;color:#64748b;">
                                            <i class="bi bi-telephone" style="font-size:0.6rem;"></i> {{ $order->customer->phone }}
                                        </div>
                                        @endif
                                    </td>
                                    <td style="padding:12px;">
                                        <span style="font-size:0.72rem;color:#475569;">
                                            {{ $order->branch->name ?? '—' }}
                                        </span>
                                    </td>
                                    <td style="padding:12px;">
                                        <span style="font-size:0.72rem;color:#1e293b;">
                                            {{ $order->service->name ?? 'Custom' }}
                                        </span>
                                        @if($order->number_of_loads)
                                        <div style="font-size:0.6rem;color:#64748b;">
                                            {{ $order->number_of_loads }} {{ $order->number_of_loads > 1 ? 'loads' : 'load' }}
                                        </div>
                                        @endif
                                    </td>
                                    <td style="padding:12px;text-align:right;">
                                        <span style="font-size:0.8rem;font-weight:600;color:#10b981;">
                                            ₱{{ number_format($order->total_amount, 2) }}
                                        </span>
                                    </td>
                                    <td style="padding:12px;text-align:center;">
                                        <span class="badge d-inline-flex align-items-center gap-1"
                                              style="background:{{ $statusDef['bg'] }};color:{{ $statusDef['color'] }};border:1px solid {{ $statusDef['color'] }}33;font-size:0.65rem;padding:4px 8px;">
                                            <i class="bi {{ $statusDef['icon'] }}" style="font-size:0.65rem;"></i>
                                            {{ $statusDef['label'] }}
                                        </span>
                                    </td>
                                    <td style="padding:12px;">
                                        <div style="font-size:0.7rem;color:#64748b;">
                                            {{ $order->created_at->diffForHumans() }}
                                        </div>
                                        <div style="font-size:0.6rem;color:#94a3b8;">
                                            {{ $order->created_at->format('M j, g:i A') }}
                                        </div>
                                    </td>
                                    <td style="padding:12px;text-align:center;">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('admin.laundries.show', $order->id) }}"
                                               class="btn btn-sm"
                                               style="font-size:0.65rem;padding:3px 8px;background:rgba(59,130,246,0.1);color:#3b82f6;border:1px solid rgba(59,130,246,0.2);">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($order->status !== 'completed' && $order->status !== 'cancelled')
                                            <a href="{{ route('admin.laundries.edit', $order->id) }}"
                                               class="btn btn-sm"
                                               style="font-size:0.65rem;padding:3px 8px;background:rgba(245,158,11,0.1);color:#f59e0b;border:1px solid rgba(245,158,11,0.2);">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size:2.5rem;color:#cbd5e1;opacity:0.5;"></i>
                        <p style="font-size:0.8rem;color:#64748b;margin-top:12px;margin-bottom:0;">No recent orders found</p>
                        <a href="{{ route('admin.laundries.create') }}" class="btn btn-sm btn-primary mt-3">
                            <i class="bi bi-plus-circle me-1"></i>Create new order
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Recent Activities --}}
    <div class="col-lg-4">
        <div class="modern-card">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:30px;height:30px;border-radius:7px;background:rgba(139,92,246,0.15);
                                color:#8b5cf6;display:flex;align-items:center;justify-content:center;font-size:0.9rem;">
                        <i class="bi bi-activity"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-slate-800">Recent activities</h6>
                        <small class="text-muted">Live system events</small>
                    </div>
                </div>
            </div>
            <div class="card-body-modern" style="padding:12px;max-height:600px;overflow-y:auto;">
                @php
                    $activities = collect();

                    // Recent payments
                    $recentPayments = \App\Models\Laundry::with('customer')
                        ->whereIn('status', ['paid', 'completed'])
                        ->whereNotNull('paid_at')
                        ->orderBy('paid_at', 'desc')
                        ->limit(5)
                        ->get()
                        ->map(fn($l) => [
                            'type' => 'payment',
                            'icon' => 'bi-credit-card-fill',
                            'color' => '#10b981',
                            'bg' => 'rgba(16,185,129,0.1)',
                            'title' => 'Payment received',
                            'description' => ($l->customer->name ?? 'Customer') . ' paid ₱' . number_format($l->total_amount, 2),
                            'time' => $l->paid_at,
                        ]);

                    // Recent pickups
                    $recentPickups = \App\Models\PickupRequest::with('customer')
                        ->whereNotNull('picked_up_at')
                        ->orderBy('picked_up_at', 'desc')
                        ->limit(5)
                        ->get()
                        ->map(fn($p) => [
                            'type' => 'pickup',
                            'icon' => 'bi-truck',
                            'color' => '#f59e0b',
                            'bg' => 'rgba(245,158,11,0.1)',
                            'title' => 'Pickup completed',
                            'description' => 'Picked up from ' . ($p->customer->name ?? 'Customer'),
                            'time' => $p->picked_up_at,
                        ]);

                    // New customers
                    $newCustomers = \App\Models\Customer::orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get()
                        ->map(fn($c) => [
                            'type' => 'customer',
                            'icon' => 'bi-person-plus-fill',
                            'color' => '#3b82f6',
                            'bg' => 'rgba(59,130,246,0.1)',
                            'title' => 'New customer',
                            'description' => $c->name . ' registered',
                            'time' => $c->created_at,
                        ]);

                    // Status changes (ready orders)
                    $readyOrders = \App\Models\Laundry::with('customer')
                        ->where('status', 'ready')
                        ->orderBy('updated_at', 'desc')
                        ->limit(5)
                        ->get()
                        ->map(fn($l) => [
                            'type' => 'ready',
                            'icon' => 'bi-check-circle-fill',
                            'color' => '#22d3ee',
                            'bg' => 'rgba(34,211,238,0.1)',
                            'title' => 'Order ready',
                            'description' => 'Order #' . $l->id . ' ready for ' . ($l->customer->name ?? 'Customer'),
                            'time' => $l->updated_at,
                        ]);

                    // Merge and sort by time
                    $activities = $activities
                        ->merge($recentPayments)
                        ->merge($recentPickups)
                        ->merge($newCustomers)
                        ->merge($readyOrders)
                        ->sortByDesc('time')
                        ->take(15);
                @endphp

                @forelse($activities as $activity)
                <div class="d-flex gap-2 mb-3 pb-3" style="border-bottom:1px solid rgba(0,0,0,0.05);">
                    <div style="width:32px;height:32px;border-radius:8px;background:{{ $activity['bg'] }};
                                color:{{ $activity['color'] }};display:flex;align-items:center;justify-content:center;
                                font-size:0.85rem;flex-shrink:0;">
                        <i class="bi {{ $activity['icon'] }}"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div style="font-size:0.75rem;font-weight:600;color:#1e293b;margin-bottom:2px;">
                            {{ $activity['title'] }}
                        </div>
                        <div style="font-size:0.7rem;color:#64748b;margin-bottom:4px;">
                            {{ $activity['description'] }}
                        </div>
                        <div style="font-size:0.62rem;color:#94a3b8;">
                            <i class="bi bi-clock" style="font-size:0.6rem;"></i> {{ $activity['time']->diffForHumans() }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="bi bi-activity" style="font-size:2rem;color:#cbd5e1;opacity:0.5;"></i>
                    <p style="font-size:0.75rem;color:#64748b;margin-top:12px;margin-bottom:0;">No recent activities</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
