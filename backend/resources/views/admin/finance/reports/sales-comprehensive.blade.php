@extends('admin.layouts.app')

@section('title', 'Comprehensive Sales Report')

@section('content')
<div class="container-xl px-4 py-4">
    <h1 class="h3 mb-4">Comprehensive Sales Report</h1>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches ?? [] as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from', date('Y-m-01')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to', date('Y-m-d')) }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Generate</button>
                    <a href="{{ route('admin.finance.reports.sales-comprehensive') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Laundry Service Sales</h6>
                    <h3 class="text-primary">₱{{ number_format($laundrySales ?? 0, 2) }}</h3>
                    <small class="text-muted">{{ $laundryCount ?? 0 }} orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Retail Sales</h6>
                    <h3 class="text-success">₱{{ number_format($retailSales ?? 0, 2) }}</h3>
                    <small class="text-muted">{{ $retailCount ?? 0 }} transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Sales</h6>
                    <h3 class="text-info">₱{{ number_format(($laundrySales ?? 0) + ($retailSales ?? 0), 2) }}</h3>
                    <small class="text-muted">Combined revenue</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Laundry Sales by Service</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Orders</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($laundryByService ?? [] as $service)
                                    <tr>
                                        <td>{{ $service->name }}</td>
                                        <td>{{ $service->orders_count }}</td>
                                        <td>₱{{ number_format($service->total_revenue, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Retail Sales by Item</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($retailByItem ?? [] as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->total_quantity }}</td>
                                        <td>₱{{ number_format($item->total_revenue, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($salesByBranch))
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Sales by Branch</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Branch</th>
                            <th>Laundry Sales</th>
                            <th>Retail Sales</th>
                            <th>Total Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salesByBranch as $branch)
                            <tr>
                                <td>{{ $branch->name }}</td>
                                <td>₱{{ number_format($branch->laundry_sales, 2) }}</td>
                                <td>₱{{ number_format($branch->retail_sales, 2) }}</td>
                                <td><strong>₱{{ number_format($branch->laundry_sales + $branch->retail_sales, 2) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
