@extends('admin.layouts.app')

@section('title', 'Stock Movement Report')

@section('content')
<div class="container-xl px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Stock Movement Report</h1>
            <p class="text-muted small mt-1">Track where your inventory is going</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Item</label>
                    <select name="item_id" class="form-select">
                        <option value="">All Items</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ $itemId == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ substr($dateFrom, 0, 10) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ substr($dateTo, 0, 10) }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted small mb-1">Retail Sales</p>
                            <h4 class="mb-0">{{ number_format($summary['retail_sales']['quantity']) }}</h4>
                            <small class="text-success">₱{{ number_format($summary['retail_sales']['value'], 2) }}</small>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-cart-plus fs-1 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted small mb-1">Laundry Usage</p>
                            <h4 class="mb-0">{{ number_format($summary['laundry_usage']['quantity']) }}</h4>
                            <small class="text-muted">Used in services</small>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-basket fs-1 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted small mb-1">Damaged/Expired</p>
                            <h4 class="mb-0">{{ number_format($summary['damaged']['quantity'] + $summary['expired']['quantity']) }}</h4>
                            <small class="text-danger">₱{{ number_format($summary['damaged']['value'] + $summary['expired']['value'], 2) }}</small>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-exclamation-triangle fs-1 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted small mb-1">Lost/Theft</p>
                            <h4 class="mb-0">{{ number_format($summary['lost']['quantity'] + $summary['theft']['quantity']) }}</h4>
                            <small class="text-danger">₱{{ number_format($summary['lost']['value'] + $summary['theft']['value'], 2) }}</small>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-shield-x fs-1 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Breakdown Table --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">Movement Breakdown</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Value Loss</th>
                        <th class="text-end">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><i class="bi bi-cart-plus text-success me-2"></i>Retail Sales</td>
                        <td class="text-end">{{ number_format($summary['retail_sales']['quantity']) }}</td>
                        <td class="text-end text-success">₱{{ number_format($summary['retail_sales']['value'], 2) }}</td>
                        <td class="text-end">
                            {{ $summary['total_outgoing']['quantity'] > 0 ? number_format(($summary['retail_sales']['quantity'] / $summary['total_outgoing']['quantity']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    <tr>
                        <td><i class="bi bi-basket text-primary me-2"></i>Laundry Usage</td>
                        <td class="text-end">{{ number_format($summary['laundry_usage']['quantity']) }}</td>
                        <td class="text-end text-muted">-</td>
                        <td class="text-end">
                            {{ $summary['total_outgoing']['quantity'] > 0 ? number_format(($summary['laundry_usage']['quantity'] / $summary['total_outgoing']['quantity']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    <tr>
                        <td><i class="bi bi-exclamation-circle text-warning me-2"></i>Damaged</td>
                        <td class="text-end">{{ number_format($summary['damaged']['quantity']) }}</td>
                        <td class="text-end text-danger">₱{{ number_format($summary['damaged']['value'], 2) }}</td>
                        <td class="text-end">
                            {{ $summary['total_outgoing']['quantity'] > 0 ? number_format(($summary['damaged']['quantity'] / $summary['total_outgoing']['quantity']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    <tr>
                        <td><i class="bi bi-calendar-x text-warning me-2"></i>Expired</td>
                        <td class="text-end">{{ number_format($summary['expired']['quantity']) }}</td>
                        <td class="text-end text-danger">₱{{ number_format($summary['expired']['value'], 2) }}</td>
                        <td class="text-end">
                            {{ $summary['total_outgoing']['quantity'] > 0 ? number_format(($summary['expired']['quantity'] / $summary['total_outgoing']['quantity']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    <tr>
                        <td><i class="bi bi-question-circle text-danger me-2"></i>Lost</td>
                        <td class="text-end">{{ number_format($summary['lost']['quantity']) }}</td>
                        <td class="text-end text-danger">₱{{ number_format($summary['lost']['value'], 2) }}</td>
                        <td class="text-end">
                            {{ $summary['total_outgoing']['quantity'] > 0 ? number_format(($summary['lost']['quantity'] / $summary['total_outgoing']['quantity']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    <tr>
                        <td><i class="bi bi-shield-x text-danger me-2"></i>Theft</td>
                        <td class="text-end">{{ number_format($summary['theft']['quantity']) }}</td>
                        <td class="text-end text-danger">₱{{ number_format($summary['theft']['value'], 2) }}</td>
                        <td class="text-end">
                            {{ $summary['total_outgoing']['quantity'] > 0 ? number_format(($summary['theft']['quantity'] / $summary['total_outgoing']['quantity']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <th>Total Outgoing</th>
                        <th class="text-end">{{ number_format($summary['total_outgoing']['quantity']) }}</th>
                        <th class="text-end">₱{{ number_format($summary['total_outgoing']['value'], 2) }}</th>
                        <th class="text-end">100%</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Recent Movements --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0">Recent Movements (Last 100)</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Item</th>
                        <th>Branch</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Value</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td>{{ $movement['date']->format('M d, Y H:i') }}</td>
                            <td>
                                @if($movement['type'] == 'Retail Sale')
                                    <span class="badge bg-success">{{ $movement['type'] }}</span>
                                @elseif($movement['type'] == 'Laundry Usage')
                                    <span class="badge bg-primary">{{ $movement['type'] }}</span>
                                @elseif(in_array($movement['type'], ['Damaged', 'Expired']))
                                    <span class="badge bg-warning">{{ $movement['type'] }}</span>
                                @else
                                    <span class="badge bg-danger">{{ $movement['type'] }}</span>
                                @endif
                            </td>
                            <td>{{ $movement['item'] }}</td>
                            <td>{{ $movement['branch'] }}</td>
                            <td class="text-end">{{ number_format($movement['quantity']) }}</td>
                            <td class="text-end">
                                @if($movement['value'])
                                    ₱{{ number_format($movement['value'], 2) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><small>{{ $movement['reference'] }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No movements found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
