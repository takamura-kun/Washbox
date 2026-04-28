@extends('admin.layouts.app')

@section('title', 'Retail Sales')

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
        <h1 class="h3 mb-0">Retail Sales (Walk-in)</h1>
        <a href="{{ route('admin.finance.retail-sales.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>New Sale
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('admin.finance.retail-sales.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Reference No</th>
                            <th>Date</th>
                            <th>Branch</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Sold By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td><strong>{{ $sale->reference_no }}</strong></td>
                                <td>{{ $sale->sale_date ? $sale->sale_date->format('M d, Y') : ($sale->created_at ? $sale->created_at->format('M d, Y') : 'N/A') }}</td>
                                <td>{{ $sale->branch?->name ?? 'N/A' }}</td>
                                <td>{{ $sale->items?->count() ?? 0 }} items</td>
                                <td><strong class="text-success">₱{{ number_format($sale->total_amount ?? 0, 2) }}</strong></td>
                                <td>{{ $sale->soldBy?->name ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.finance.retail-sales.show', $sale) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.finance.retail-sales.destroy', $sale) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this sale?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">No retail sales found</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-secondary">
                            <th colspan="4" class="text-end">Total:</th>
                            <th colspan="3">₱{{ number_format($sales->sum('total_amount'), 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-3">
                {{ $sales->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
