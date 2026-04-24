@extends('layouts.app')

@section('title', $item->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">{{ $item->name }}</h1>
            <p class="text-muted small mt-1">Inventory Details</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('branch.inventory.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-1">Current Stock</p>
                    <h3 class="mb-0">{{ $item->current_stock }}</h3>
                    <small class="text-muted">{{ $item->unit }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-1">Unit Cost</p>
                    <h3 class="mb-0">₱{{ number_format($item->unit_cost, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total Value</p>
                    <h3 class="mb-0">₱{{ number_format($item->total_value, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-1">Status</p>
                    <h5 class="mb-0">
                        @if($item->stock_status === 'out_of_stock')
                            <span class="badge bg-danger">Out of Stock</span>
                        @elseif($item->stock_status === 'low_stock')
                            <span class="badge bg-warning">Low Stock</span>
                        @else
                            <span class="badge bg-success">Normal</span>
                        @endif
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0">Item Details</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Category:</dt>
                        <dd class="col-sm-7">{{ $item->category->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-5">Unit:</dt>
                        <dd class="col-sm-7">{{ $item->unit }}</dd>

                        <dt class="col-sm-5">Reorder:</dt>
                        <dd class="col-sm-7">{{ $item->reorder_point }}</dd>

                        <dt class="col-sm-5">Max Level:</dt>
                        <dd class="col-sm-7">{{ $item->max_level }}</dd>

                        <dt class="col-sm-5">Status:</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $item->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('branch.adjustments.create') }}" class="btn btn-warning w-100 mb-2">
                        <i class="bi bi-exclamation-triangle me-2"></i>Report Adjustment
                    </a>
                    <small class="text-muted d-block mt-2">
                        Report damaged, expired, lost, or stolen items through the stock adjustment system.
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0">Stock History</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date & Time</th>
                                <th>Reason</th>
                                <th>Quantity Changed</th>
                                <th>Stock After</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td><small>{{ $log->created_at->format('M d, Y H:i') }}</small></td>
                                    <td><span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $log->type)) }}</span></td>
                                    <td>
                                        @if($log->quantity > 0)
                                            <span class="text-success">+{{ $log->quantity }}</span>
                                        @else
                                            <span class="text-danger">{{ $log->quantity }}</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $log->balance_after }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No history</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($logs->hasPages())
                    <div class="card-footer bg-light border-top">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
