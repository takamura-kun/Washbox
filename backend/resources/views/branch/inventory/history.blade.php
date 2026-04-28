@extends('branch.layouts.app')

@section('title', 'Stock Movement History')

@section('content')
<div class="container-xl px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Stock Movement History</h2>
            <p class="text-muted mb-0">Track all inventory movements</p>
        </div>
    </div>

    {{-- Quick Navigation --}}
    <div class="btn-group mb-4 w-100" role="group">
        <a href="{{ route('branch.inventory.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-list me-1"></i>All Items
        </a>
        <a href="{{ route('branch.inventory.low-stock') }}" class="btn btn-outline-warning">
            <i class="fas fa-exclamation-triangle me-1"></i>Low Stock
        </a>
        <a href="{{ route('branch.inventory.out-of-stock') }}" class="btn btn-outline-danger">
            <i class="fas fa-times-circle me-1"></i>Out of Stock
        </a>
        <a href="{{ route('branch.inventory.requests') }}" class="btn btn-outline-info">
            <i class="fas fa-paper-plane me-1"></i>Requests
        </a>
        <a href="{{ route('branch.inventory.history') }}" class="btn btn-secondary active">
            <i class="fas fa-history me-1"></i>History
        </a>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Filter by Item</label>
                    <select name="item_id" class="form-select">
                        <option value="">All Items</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ $itemId == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="{{ route('branch.inventory.history') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- History Table --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Item</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Balance After</th>
                            <th>User</th>
                            <th>Reference</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $log)
                            <tr>
                                <td>
                                    {{ $log->created_at->format('M d, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $log->inventoryItem->name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $log->inventoryItem->category->name ?? '' }}</small>
                                </td>
                                <td>
                                    @if($log->type === 'in')
                                        <span class="badge bg-success">
                                            <i class="bi bi-arrow-down-circle me-1"></i>Stock In
                                        </span>
                                    @elseif($log->type === 'out')
                                        <span class="badge bg-danger">
                                            <i class="bi bi-arrow-up-circle me-1"></i>Stock Out
                                        </span>
                                    @elseif($log->type === 'usage')
                                        <span class="badge bg-warning">
                                            <i class="bi bi-basket me-1"></i>Usage
                                        </span>
                                    @elseif($log->type === 'adjustment')
                                        <span class="badge bg-info">
                                            <i class="bi bi-arrow-repeat me-1"></i>Adjustment
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($log->type) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="{{ $log->quantity >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $log->quantity >= 0 ? '+' : '' }}{{ number_format($log->quantity) }}
                                    </strong>
                                </td>
                                <td><strong>{{ number_format($log->balance_after) }}</strong></td>
                                <td>{{ $log->user->name ?? 'System' }}</td>
                                <td>
                                    @if($log->reference_type && $log->reference_id)
                                        <small class="text-muted">
                                            {{ class_basename($log->reference_type) }} #{{ $log->reference_id }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->notes)
                                        <small>{{ Str::limit($log->notes, 40) }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-clock-history display-4 d-block mb-2"></i>
                                    No movement history found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($history->hasPages())
                <div class="mt-4">
                    {{ $history->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
