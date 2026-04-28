@extends('branch.layouts.app')

@section('title', 'Stock Requests')

@section('content')
<div class="container-xl px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Stock Requests</h2>
            <p class="text-muted mb-0">View your stock request history</p>
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
        <a href="{{ route('branch.inventory.requests') }}" class="btn btn-info active">
            <i class="fas fa-paper-plane me-1"></i>Requests
        </a>
        <a href="{{ route('branch.inventory.history') }}" class="btn btn-outline-secondary">
            <i class="fas fa-history me-1"></i>History
        </a>
    </div>

    {{-- Requests Table --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Requested By</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Approved By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr>
                                <td><span class="badge bg-secondary">#{{ $request->id }}</span></td>
                                <td>
                                    <strong>{{ $request->inventoryItem->name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $request->inventoryItem->category->name ?? '' }}</small>
                                </td>
                                <td><strong>{{ number_format($request->quantity) }}</strong></td>
                                <td>{{ $request->requestedBy->name ?? 'N/A' }}</td>
                                <td>{{ $request->created_at->format('M d, Y h:i A') }}</td>
                                <td>
                                    @if($request->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($request->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($request->status === 'completed')
                                        <span class="badge bg-info">Completed</span>
                                    @elseif($request->status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($request->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($request->approvedBy)
                                        {{ $request->approvedBy->name }}
                                        <br>
                                        <small class="text-muted">{{ $request->approved_at?->format('M d, Y') }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($request->notes)
                                        <small>{{ Str::limit($request->notes, 50) }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No stock requests found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($requests->hasPages())
                <div class="mt-4">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
