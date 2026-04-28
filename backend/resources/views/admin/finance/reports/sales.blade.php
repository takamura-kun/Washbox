@extends('admin.layouts.app')

@section('title', 'Sales Report')
@section('page-title', 'Sales Report')
@section('page-icon', 'bi-bar-chart')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">📊 Sales Report</h2>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
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
                <div class="col-md-5">
                    <label class="form-label">Month</label>
                    <input type="month" name="month" class="form-control" value="{{ $month }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">By Service Type</h5>
                </div>
                <div class="card-body">
                    @php $maxTotal = $byService->max('total') ?? 1; @endphp
                    @foreach($byService as $item)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ $item->service->name ?? 'N/A' }}</span>
                                <span class="fw-semibold">₱{{ number_format($item->total, 2) }}</span>
                            </div>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-primary" style="width: {{ ($item->total / $maxTotal * 100) }}%">
                                    {{ $totalSales > 0 ? number_format($item->total / $totalSales * 100, 1) : 0 }}%
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">By Branch</h5>
                </div>
                <div class="card-body">
                    @php $maxBranchTotal = $byBranch->max('total') ?? 1; @endphp
                    @foreach($byBranch as $item)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ $item['branch']->name }}</span>
                                <span class="fw-semibold">₱{{ number_format($item['total'], 2) }}</span>
                            </div>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: {{ ($item['total'] / $maxBranchTotal * 100) }}%">
                                    {{ $totalSales > 0 ? number_format($item['total'] / $totalSales * 100, 1) : 0 }}%
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
