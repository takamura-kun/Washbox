@extends('admin.layouts.app')

@section('title', 'Cash Flow Analysis — WashBox')
@section('page-title', 'Cash Flow Analysis')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.finance.dashboard') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
            <h2 class="mb-1">Cash Flow Analysis</h2>
            <p class="text-muted mb-0">Daily cash flow tracking and analysis</p>
        </div>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('admin.finance.cash-flow.generate') }}" class="d-inline d-flex gap-2">
                @csrf
                <select name="branch_id" class="form-select" style="width: 200px;">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-inventory btn-inventory-primary">
                    <i class="bi bi-arrow-clockwise me-2"></i>Generate Today's Record
                </button>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-1">Today's Cash In</p>
                    <h4 class="mb-0 text-success">₱{{ number_format($summary['today_cash_in'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-1">Today's Cash Out</p>
                    <h4 class="mb-0 text-danger">₱{{ number_format($summary['today_cash_out'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-1">Net Cash Flow</p>
                    <h4 class="mb-0 text-{{ $summary['net_cash_flow'] >= 0 ? 'success' : 'danger' }}">
                        ₱{{ number_format($summary['net_cash_flow'], 2) }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <p class="text-muted mb-1">Current Balance</p>
                    <h4 class="mb-0">₱{{ number_format($summary['current_balance'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="inventory-card mb-4">
        <div class="inventory-card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
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
                    <input type="date" name="date_from" class="form-control" placeholder="Date From" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date_to" class="form-control" placeholder="Date To" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-inventory btn-inventory-primary flex-grow-1">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.finance.cash-flow.index') }}" class="btn-inventory btn-inventory-secondary">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="inventory-card">
        <div class="inventory-card-body">
            <div class="table-responsive">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Branch</th>
                            <th class="text-end">Opening Balance</th>
                            <th class="text-end">Cash In</th>
                            <th class="text-end">Cash Out</th>
                            <th class="text-end">Net Flow</th>
                            <th class="text-end">Closing Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td>{{ $record->record_date->format('M d, Y') }}</td>
                                <td>{{ $record->branch->name ?? 'All Branches' }}</td>
                                <td class="text-end">₱{{ number_format($record->opening_balance, 2) }}</td>
                                <td class="text-end text-success fw-semibold">₱{{ number_format($record->cash_inflow, 2) }}</td>
                                <td class="text-end text-danger fw-semibold">₱{{ number_format($record->cash_outflow, 2) }}</td>
                                <td class="text-end fw-bold text-{{ $record->net_cash_flow >= 0 ? 'success' : 'danger' }}">
                                    {{ $record->net_cash_flow >= 0 ? '+' : '' }}₱{{ number_format($record->net_cash_flow, 2) }}
                                </td>
                                <td class="text-end fw-semibold">₱{{ number_format($record->closing_balance, 2) }}</td>
                                <td>
                                    <span class="badge bg-success">Recorded</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No cash flow records found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($records->count() > 0)
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="3" class="text-end">Totals:</td>
                            <td class="text-end text-success">₱{{ number_format($records->sum('cash_inflow'), 2) }}</td>
                            <td class="text-end text-danger">₱{{ number_format($records->sum('cash_outflow'), 2) }}</td>
                            <td class="text-end">₱{{ number_format($records->sum('net_cash_flow'), 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            @if($records->hasPages())
                <div class="mt-4">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
