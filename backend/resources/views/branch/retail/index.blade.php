@extends('branch.layouts.app')

@section('title', 'Retail Sales')

@push('styles')
<style>
.table-responsive {
    background: var(--card-bg) !important;
}
.table {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
}
.table tbody tr {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
}
.table thead th {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}
.table tbody td {
    background: var(--card-bg) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}
.card {
    background: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}
.card-body {
    background: var(--card-bg) !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0" style="color: var(--text-primary);">Retail Sales</h1>
            <p class="small mt-1" style="color: var(--text-secondary);">Sell inventory items to walk-in customers</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('branch.retail.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>New Sale
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $sale->customer_name ?? 'Walk-in' }}</td>
                                <td>{{ $sale->item_name }} ({{ $sale->quantity }})</td>
                                <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                                <td><span class="badge bg-success">{{ ucfirst($sale->payment_method) }}</span></td>
                                <td>
                                    <a href="{{ route('branch.retail.show', $sale) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4" style="color: var(--text-secondary);">No sales yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($sales->hasPages())
            <div class="card-footer">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
