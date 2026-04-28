@extends('layouts.app')

@section('content')
<div class="container-fluid pt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.inventory.purchases.index') }}">Purchases</a></li>
            <li class="breadcrumb-item active">{{ $purchase->reference_no }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-3">
            <!-- Purchase Summary Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="card-title text-muted text-uppercase mb-3">Purchase Summary</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Reference:</dt>
                        <dd class="col-sm-6"><strong>{{ $purchase->reference_no }}</strong></dd>

                        <dt class="col-sm-6">PO Number:</dt>
                        <dd class="col-sm-6">{{ $purchase->purchase_order_number ?? '-' }}</dd>

                        <dt class="col-sm-6">Date:</dt>
                        <dd class="col-sm-6">{{ $purchase->purchase_date->format('M d, Y') ?? '-' }}</dd>

                        <dt class="col-sm-6">Supplier:</dt>
                        <dd class="col-sm-6">{{ $purchase->supplier?->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-6">Purchased By:</dt>
                        <dd class="col-sm-6">{{ $purchase->purchasedBy?->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-6">Branch:</dt>
                        <dd class="col-sm-6">{{ $purchase->branch?->name ?? 'Central Stock' }}</dd>
                    </dl>
                </div>
            </div>

            <!-- Total Cost Card -->
            <div class="card border-success mb-4">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted text-uppercase mb-2">Total Cost</h6>
                    <h3 class="text-success">₱{{ number_format($purchase->grand_total ?? $purchase->total_cost, 2) }}</h3>
                    <small class="text-muted">Grand Total</small>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <!-- Purchase Items -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Purchase Items</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Cost per Unit</th>
                                <th>Cost per Bulk</th>
                                <th>Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchase->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->item?->name ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $item->item?->category?->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td>{{ $item->purchase_unit ?? '-' }}</td>
                                    <td class="text-end">₱{{ number_format($item->cost_per_unit, 2) }}</td>
                                    <td class="text-end">₱{{ number_format($item->cost_per_bulk, 2) }}</td>
                                    <td class="text-end fw-bold">₱{{ number_format($item->total_cost, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No items found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-light">
                    <div class="row">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <dl class="row mb-0">
                                <dt class="col-sm-6">Grand Total:</dt>
                                <dd class="col-sm-6 text-end fw-bold text-success">₱{{ number_format($purchase->grand_total ?? $purchase->total_cost, 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($purchase->notes)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Notes</h5>
                    </div>
                    <div class="card-body">
                        {{ $purchase->notes }}
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="mt-4">
                <a href="{{ route('admin.inventory.purchases.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Purchases
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
