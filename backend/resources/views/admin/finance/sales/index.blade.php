@extends('admin.layouts.app')

@section('title', 'Sales Report — WashBox')
@section('page-title', 'Sales Report')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
@endpush

@section('content')

<div class="container-xl px-4 py-4">
    {{-- Header with Filters --}}
    <div class="row mb-4 g-3">
        <div class="col-md-8">
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <form method="GET" class="d-flex gap-2">
                    <select name="period" class="inventory-form-control" onchange="this.form.submit()">
                        <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                        <option value="year" {{ $period === 'year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.finance.dashboard') }}" class="btn-inventory btn-inventory-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <small class="text-muted d-block mb-1">Total Sales</small>
                    <h3 class="mb-0 text-success">₱{{ number_format($summary['totalSales'], 2) }}</h3>
                    <small class="text-muted mt-2 d-block">{{ $summary['laundryCount'] + $summary['retailCount'] }} transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <small class="text-muted d-block mb-1">Laundry Sales</small>
                    <h3 class="mb-0">₱{{ number_format($summary['laundrySales'], 2) }}</h3>
                    <small class="text-muted mt-2 d-block">{{ $summary['laundryCount'] }} orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <small class="text-muted d-block mb-1">Retail Sales</small>
                    <h3 class="mb-0">₱{{ number_format($summary['retailSales'], 2) }}</h3>
                    <small class="text-muted mt-2 d-block">{{ $summary['retailCount'] }} items sold</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <small class="text-muted d-block mb-1">Fees</small>
                    <h3 class="mb-0">₱{{ number_format($summary['pickupDeliveryFees'], 2) }}</h3>
                    <small class="text-muted mt-2 d-block">Pickup & Delivery</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales Table --}}
    <div class="inventory-card">
        <div class="inventory-card-body">
            <h5 class="mb-3">Sales Transactions</h5>
            <div class="table-responsive">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Branch</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td>{{ $sale->paid_at?->format('M d, Y') ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.laundries.show', $sale->id) }}" class="text-primary text-decoration-none">
                                        #{{ $sale->reference_number }}
                                    </a>
                                </td>
                                <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                                <td>{{ $sale->branch->name ?? 'N/A' }}</td>
                                <td class="fw-semibold">₱{{ number_format($sale->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge" style="background-color: {{ $sale->status === 'paid' ? '#10b981' : '#f59e0b' }}">
                                        {{ ucfirst($sale->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No sales found for this period</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($sales->hasPages())
                <div class="mt-3">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
