@extends('branch.layouts.app')

@section('title', 'Sale Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Sale #{{ $sale->id }}</h1>
            <p class="text-muted small mt-1">{{ $sale->created_at->format('M d, Y H:i A') }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('branch.retail.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Sale Details</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Item:</strong> {{ $sale->item_name }}
                        </div>
                        <div class="col-md-3">
                            <strong>Quantity:</strong> {{ $sale->quantity }}
                        </div>
                        <div class="col-md-3">
                            <strong>Unit Price:</strong> ₱{{ number_format($sale->unit_price, 2) }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12 text-end">
                            <h4><strong>Total Amount:</strong> ₱{{ number_format($sale->total_amount, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Customer</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $sale->customer_name ?? 'Walk-in Customer' }}</strong></p>
                    @if($sale->customer_contact)
                        <p class="text-muted small mb-0">{{ $sale->customer_contact }}</p>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Payment</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Method:</strong> 
                        <span class="badge bg-success">{{ ucfirst($sale->payment_method) }}</span>
                    </p>
                    @if($sale->notes)
                        <p class="mb-0"><strong>Notes:</strong><br>{{ $sale->notes }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
