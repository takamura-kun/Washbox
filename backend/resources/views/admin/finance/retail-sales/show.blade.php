@extends('admin.layouts.app')

@section('title', 'Retail Sale Details')

@push('styles')
<style>
    /* Force table theme support */
    .table-responsive,
    .table,
    .table tbody,
    .table tbody tr,
    .table tbody tr td,
    .table thead,
    .table thead tr,
    .table thead tr th,
    .table tfoot,
    .table tfoot tr,
    .table tfoot tr th {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    
    .table thead {
        background: var(--border-color) !important;
    }
    
    .table tfoot {
        background: var(--border-color) !important;
    }
    
    .table tbody tr:hover {
        background: var(--border-color) !important;
        opacity: 0.95;
    }
    
    /* Ensure card body has proper background */
    .card-body {
        background: var(--card-bg) !important;
    }
</style>
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Retail Sale Details</h1>
        <a href="{{ route('admin.finance.retail-sales.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4" style="background: var(--card-bg); border-color: var(--border-color);">
                <div class="card-header" style="background: var(--primary-color); color: white;">
                    <h5 class="mb-0">Sale Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong style="color: var(--text-primary);">Sale Number:</strong>
                            <p class="mb-0" style="color: var(--text-primary);">{{ $sale->sale_number ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong style="color: var(--text-primary);">Sale Date:</strong>
                            <p class="mb-0" style="color: var(--text-primary);">{{ $sale->created_at ? $sale->created_at->format('F d, Y') : 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong style="color: var(--text-primary);">Branch:</strong>
                            <p class="mb-0" style="color: var(--text-primary);">{{ $sale->branch?->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong style="color: var(--text-primary);">Sold By:</strong>
                            <p class="mb-0" style="color: var(--text-primary);">{{ $sale->seller?->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong style="color: var(--text-primary);">Payment Method:</strong>
                            <p class="mb-0" style="color: var(--text-primary);">{{ ucfirst($sale->payment_method ?? 'cash') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong style="color: var(--text-primary);">Notes:</strong>
                            <p class="mb-0" style="color: var(--text-primary);">{{ $sale->notes ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm" style="background: var(--card-bg); border-color: var(--border-color);">
                <div class="card-header" style="background: var(--border-color); color: var(--text-primary);">
                    <h5 class="mb-0">Sale Item</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $sale->item_name ?? ($sale->inventoryItem?->name ?? 'N/A') }}</td>
                                    <td>{{ $sale->quantity ?? 0 }}</td>
                                    <td>₱{{ number_format($sale->unit_price ?? 0, 2) }}</td>
                                    <td>₱{{ number_format($sale->total_amount ?? 0, 2) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="table-secondary">
                                    <th colspan="3" class="text-end">Grand Total:</th>
                                    <th>₱{{ number_format($sale->total_amount ?? 0, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm" style="background: var(--card-bg); border-color: var(--border-color);">
                <div class="card-header" style="background: var(--border-color); color: var(--text-primary);">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.finance.retail-sales.destroy', $sale) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Delete this sale? Stock will be restored.')">
                            <i class="bi bi-trash me-2"></i>Delete Sale
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
