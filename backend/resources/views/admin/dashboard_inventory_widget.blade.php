{{-- ════════════════════════════════════════════════════════════════
     dashboard_inventory_widget.blade.php
     Inventory Stock Monitoring Widget for Admin Dashboard
════════════════════════════════════════════════════════════════ --}}

<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="modern-card">
            <div class="card-header-modern d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:32px;height:32px;border-radius:8px;background:rgba(245,158,11,0.15);
                                color:#fbbf24;display:flex;align-items:center;justify-content:center;font-size:1rem;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-slate-800">
                            <i class="bi bi-box-seam text-warning me-2"></i>Inventory Stock Monitor
                        </h6>
                        <small style="color:#64748b;">Real-time stock levels across all branches</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    {{-- Stock Status Legend --}}
                    <div class="d-none d-lg-flex gap-3 me-3">
                        <span style="display:flex;align-items:center;gap:4px;font-size:10px;color:#64748b;">
                            <span style="width:8px;height:8px;border-radius:50%;background:#ef4444;"></span>
                            Critical
                        </span>
                        <span style="display:flex;align-items:center;gap:4px;font-size:10px;color:#64748b;">
                            <span style="width:8px;height:8px;border-radius:50%;background:#f59e0b;"></span>
                            Low
                        </span>
                        <span style="display:flex;align-items:center;gap:4px;font-size:10px;color:#64748b;">
                            <span style="width:8px;height:8px;border-radius:50%;background:#10b981;"></span>
                            Good
                        </span>
                    </div>
                    <a href="{{ route('admin.inventory.index') }}" class="dash-btn dash-btn-warning" style="font-size:0.7rem;padding:4px 10px;">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Manage Inventory
                    </a>
                </div>
            </div>
            <div class="card-body-modern">
                @php
                    // Get inventory stocks grouped by branch
                    $inventoryStocks = \App\Models\BranchStock::with(['branch', 'inventoryItem'])
                        ->when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                        ->orderBy('branch_id')
                        ->orderBy('current_stock', 'asc')
                        ->get()
                        ->groupBy('branch_id');
                    
                    // Calculate summary stats
                    $totalItems = \App\Models\BranchStock::when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))->count();
                    $criticalItems = \App\Models\BranchStock::when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                        ->whereRaw('current_stock <= reorder_point * 0.5')
                        ->where('current_stock', '>', 0)
                        ->count();
                    $lowStockItems = \App\Models\BranchStock::when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                        ->whereRaw('current_stock > reorder_point * 0.5 AND current_stock <= reorder_point')
                        ->count();
                    $outOfStock = \App\Models\BranchStock::when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                        ->where('current_stock', '<=', 0)
                        ->count();
                    $totalValue = \App\Models\BranchStock::when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                        ->selectRaw('SUM(current_stock * cost_price) as total')
                        ->value('total') ?? 0;
                @endphp

                {{-- Summary Cards --}}
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded" style="background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.2);">
                            <div style="color:#64748b;font-size:0.6rem;text-transform:uppercase;margin-bottom:2px;">Total Items</div>
                            <div style="font-size:1.3rem;font-weight:700;color:#3b82f6;">{{ $totalItems }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);">
                            <div style="color:#64748b;font-size:0.6rem;text-transform:uppercase;margin-bottom:2px;">Critical</div>
                            <div style="font-size:1.3rem;font-weight:700;color:#ef4444;">{{ $criticalItems }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);">
                            <div style="color:#64748b;font-size:0.6rem;text-transform:uppercase;margin-bottom:2px;">Low Stock</div>
                            <div style="font-size:1.3rem;font-weight:700;color:#f59e0b;">{{ $lowStockItems }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);">
                            <div style="color:#64748b;font-size:0.6rem;text-transform:uppercase;margin-bottom:2px;">Total Value</div>
                            <div style="font-size:1.3rem;font-weight:700;color:#10b981;">₱{{ number_format($totalValue, 0) }}</div>
                        </div>
                    </div>
                </div>

                @if($inventoryStocks->isEmpty())
                    <div class="text-center py-4" style="color:#64748b;">
                        <i class="bi bi-box-seam" style="font-size:2.5rem;opacity:0.3;"></i>
                        <p style="font-size:0.8rem;margin-top:10px;margin-bottom:0;">No inventory items found</p>
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-sm btn-outline-warning mt-2">
                            <i class="bi bi-plus-circle me-1"></i>Add Inventory Items
                        </a>
                    </div>
                @else
                    {{-- Inventory Table by Branch --}}
                    @foreach($inventoryStocks as $branchId => $stocks)
                        @php
                            $branch = $stocks->first()->branch;
                            $branchCritical = $stocks->filter(fn($s) => $s->current_stock <= $s->reorder_point * 0.5 && $s->current_stock > 0)->count();
                            $branchLow = $stocks->filter(fn($s) => $s->current_stock > $s->reorder_point * 0.5 && $s->current_stock <= $s->reorder_point)->count();
                        @endphp
                        
                        <div class="mb-3">
                            {{-- Branch Header --}}
                            <div class="d-flex align-items-center justify-content-between mb-2 p-2 rounded" 
                                 style="background:rgba(255,255,255,0.03);border-left:3px solid #3b82f6;">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:24px;height:24px;border-radius:50%;background:#3b82f6;
                                                color:#fff;display:flex;align-items:center;justify-content:center;
                                                font-size:0.7rem;font-weight:600;">
                                        {{ strtoupper(substr($branch->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-size:0.8rem;font-weight:600;color:#1e293b;">{{ $branch->name ?? 'Unknown Branch' }}</div>
                                        <div style="font-size:0.65rem;color:#64748b;">{{ $stocks->count() }} items</div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    @if($branchCritical > 0)
                                        <span class="badge" style="background:rgba(239,68,68,0.2);color:#ef4444;font-size:0.65rem;">
                                            {{ $branchCritical }} Critical
                                        </span>
                                    @endif
                                    @if($branchLow > 0)
                                        <span class="badge" style="background:rgba(245,158,11,0.2);color:#f59e0b;font-size:0.65rem;">
                                            {{ $branchLow }} Low
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Stock Items Table --}}
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0" style="font-size:0.72rem;">
                                    <thead>
                                        <tr style="background:rgba(255,255,255,0.02);border-bottom:1px solid #e2e8f0;">
                                            <th style="color:#64748b;font-weight:600;padding:8px;width:5%;"></th>
                                            <th style="color:#64748b;font-weight:600;padding:8px;">Item Name</th>
                                            <th class="text-center" style="color:#64748b;font-weight:600;padding:8px;width:12%;">Current Stock</th>
                                            <th class="text-center" style="color:#64748b;font-weight:600;padding:8px;width:12%;">Reorder Point</th>
                                            <th class="text-center" style="color:#64748b;font-weight:600;padding:8px;width:10%;">Unit</th>
                                            <th class="text-end" style="color:#64748b;font-weight:600;padding:8px;width:12%;">Cost Price</th>
                                            <th class="text-end" style="color:#64748b;font-weight:600;padding:8px;width:12%;">Stock Value</th>
                                            <th class="text-center" style="color:#64748b;font-weight:600;padding:8px;width:10%;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stocks->sortBy(fn($s) => $s->current_stock / max($s->reorder_point, 1)) as $stock)
                                            @php
                                                $item = $stock->inventoryItem;
                                                $stockPercentage = $stock->reorder_point > 0 ? ($stock->current_stock / $stock->reorder_point) * 100 : 100;
                                                
                                                if ($stock->current_stock <= 0) {
                                                    $statusColor = '#64748b';
                                                    $statusBg = 'rgba(100,116,139,0.1)';
                                                    $statusText = 'Out of Stock';
                                                    $statusIcon = 'x-circle-fill';
                                                } elseif ($stock->current_stock <= $stock->reorder_point * 0.5) {
                                                    $statusColor = '#ef4444';
                                                    $statusBg = 'rgba(239,68,68,0.1)';
                                                    $statusText = 'Critical';
                                                    $statusIcon = 'exclamation-triangle-fill';
                                                } elseif ($stock->current_stock <= $stock->reorder_point) {
                                                    $statusColor = '#f59e0b';
                                                    $statusBg = 'rgba(245,158,11,0.1)';
                                                    $statusText = 'Low';
                                                    $statusIcon = 'exclamation-circle-fill';
                                                } else {
                                                    $statusColor = '#10b981';
                                                    $statusBg = 'rgba(16,185,129,0.1)';
                                                    $statusText = 'Good';
                                                    $statusIcon = 'check-circle-fill';
                                                }
                                                
                                                $stockValue = $stock->current_stock * $stock->cost_price;
                                            @endphp
                                            <tr style="border-bottom:1px solid rgba(226,232,240,0.5);">
                                                <td style="padding:10px;">
                                                    <i class="bi bi-{{ $statusIcon }}" style="color:{{ $statusColor }};font-size:0.8rem;"></i>
                                                </td>
                                                <td style="padding:10px;">
                                                    <div style="font-weight:500;color:#1e293b;">{{ $item->name ?? 'Unknown Item' }}</div>
                                                    @if($item->sku)
                                                        <div style="font-size:0.65rem;color:#94a3b8;">SKU: {{ $item->sku }}</div>
                                                    @endif
                                                </td>
                                                <td class="text-center" style="padding:10px;">
                                                    <span class="badge" style="background:{{ $statusBg }};color:{{ $statusColor }};font-size:0.75rem;padding:4px 10px;font-weight:600;">
                                                        {{ number_format($stock->current_stock, 2) }}
                                                    </span>
                                                </td>
                                                <td class="text-center" style="padding:10px;color:#64748b;">
                                                    {{ number_format($stock->reorder_point, 2) }}
                                                </td>
                                                <td class="text-center" style="padding:10px;color:#64748b;">
                                                    {{ $item->unit ?? 'pcs' }}
                                                </td>
                                                <td class="text-end" style="padding:10px;color:#64748b;">
                                                    ₱{{ number_format($stock->cost_price, 2) }}
                                                </td>
                                                <td class="text-end" style="padding:10px;">
                                                    <span style="font-weight:600;color:#1e293b;">
                                                        ₱{{ number_format($stockValue, 2) }}
                                                    </span>
                                                </td>
                                                <td class="text-center" style="padding:10px;">
                                                    <span class="badge" style="background:{{ $statusBg }};color:{{ $statusColor }};font-size:0.65rem;padding:3px 8px;">
                                                        {{ $statusText }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="background:rgba(255,255,255,0.02);border-top:2px solid #e2e8f0;">
                                            <td colspan="6" class="text-end fw-bold" style="color:#1e293b;padding:10px;">
                                                Branch Total:
                                            </td>
                                            <td class="text-end fw-bold" style="color:#10b981;padding:10px;">
                                                ₱{{ number_format($stocks->sum(fn($s) => $s->current_stock * $s->cost_price), 2) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endforeach

                    {{-- Out of Stock Items Alert --}}
                    @if($outOfStock > 0)
                        <div class="alert alert-danger d-flex align-items-center mb-0" style="font-size:0.75rem;padding:10px;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div>
                                <strong>{{ $outOfStock }} item(s) are out of stock!</strong>
                                <a href="{{ route('admin.inventory.index') }}" class="alert-link ms-2">Restock now</a>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
