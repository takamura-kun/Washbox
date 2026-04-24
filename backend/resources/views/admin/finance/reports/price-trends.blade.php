@extends('admin.layouts.app')

@section('title', 'Item Price Trends')
@section('page-title', 'Price Trends')
@section('page-icon', 'bi-graph-up')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">📈 Item Price Trends</h2>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-10">
                    <label class="form-label">Select Item</label>
                    <select name="item_id" class="form-select">
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ $selectedItem && $selectedItem->id == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>View
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($selectedItem)
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0">Price History — {{ $selectedItem->name }}</h5>
            </div>
        </div>

        @foreach($trends as $branchName => $history)
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0">{{ $branchName }}</h6>
                </div>
                <div class="card-body">
                    @if($history->count() > 0)
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            @foreach($history->reverse() as $record)
                                <div class="text-center">
                                    <div class="text-muted small">{{ \Carbon\Carbon::parse($record->effective_date)->format('M Y') }}</div>
                                    <div class="fw-semibold text-primary">₱{{ number_format($record->unit_cost, 2) }}</div>
                                </div>
                                @if(!$loop->last)
                                    <i class="bi bi-arrow-right text-muted"></i>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No price history available</p>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
