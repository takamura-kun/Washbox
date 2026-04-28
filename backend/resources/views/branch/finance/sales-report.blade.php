@extends('branch.layouts.app')

@section('title', 'Sales Report')

@push('styles')
<style>
    .card {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
    }
    .card-body, .card-header {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    .card-header.bg-light {
        background: var(--card-bg) !important;
    }
    .table {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
    }
    .table thead th, .table tbody td, .table tfoot th {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    .table tbody tr:hover td {
        background: var(--hover-bg, rgba(0,0,0,0.05)) !important;
    }
    [data-theme="dark"] .table tbody tr:hover td {
        background: rgba(255,255,255,0.05) !important;
    }
    .table tfoot {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
    }
    .table-secondary {
        background: var(--card-bg) !important;
    }
    .form-control {
        background: var(--card-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    .border-bottom {
        border-color: var(--border-color) !important;
    }
</style>
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Sales Report</h2>
            <p class="mb-0" style="color: var(--text-secondary);">Detailed sales transactions and analysis</p>
        </div>
        <button type="button" class="btn btn-success" onclick="exportToCSV()">
            <i class="bi bi-file-earmark-excel me-2"></i>Export
        </button>
    </div>

    {{-- Quick Navigation --}}
    <div class="btn-group mb-4 w-100" role="group">
        <a href="{{ route('branch.finance.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </a>
        <a href="{{ route('branch.finance.expenses') }}" class="btn btn-outline-danger">
            <i class="bi bi-receipt me-1"></i>Expenses
        </a>
        <a href="{{ route('branch.finance.daily-cash-report') }}" class="btn btn-outline-success">
            <i class="bi bi-cash-coin me-1"></i>Daily Cash
        </a>
        <a href="{{ route('branch.finance.weekly-summary') }}" class="btn btn-outline-info">
            <i class="bi bi-graph-up me-1"></i>Weekly Summary
        </a>
        <a href="{{ route('branch.finance.sales-report') }}" class="btn btn-secondary active">
            <i class="bi bi-file-earmark-text me-1"></i>Sales Report
        </a>
    </div>

    {{-- Date Range Filter --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('branch.finance.sales-report') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1" style="color: var(--text-secondary);">Total Sales</p>
                            <h3 class="mb-0 text-success">₱{{ number_format($summary['total_sales'], 2) }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-cash-stack text-success fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1" style="color: var(--text-secondary);">Total Orders</p>
                            <h3 class="mb-0 text-primary">{{ $summary['total_orders'] }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-basket text-primary fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1" style="color: var(--text-secondary);">Average Order</p>
                            <h3 class="mb-0 text-info">₱{{ number_format($summary['average_order'], 2) }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-calculator text-info fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales Transactions Table --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>Sales Transactions
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="salesTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Tracking #</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Weight/Qty</th>
                            <th class="text-end">Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laundries as $laundry)
                            <tr>
                                <td>
                                    {{ $laundry->paid_at->format('M d, Y') }}
                                    <br>
                                    <small style="color: var(--text-secondary);">{{ $laundry->paid_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('branch.laundries.show', $laundry) }}" class="text-primary">
                                        <strong>{{ $laundry->tracking_number }}</strong>
                                    </a>
                                </td>
                                <td>
                                    <strong>{{ $laundry->customer->name ?? 'Walk-in' }}</strong>
                                    @if($laundry->customer)
                                        <br>
                                        <small style="color: var(--text-secondary);">{{ $laundry->customer->phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $laundry->service->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @if($laundry->weight)
                                        {{ $laundry->weight }} kg
                                    @elseif($laundry->quantity)
                                        {{ $laundry->quantity }} pcs
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong class="text-success">₱{{ number_format($laundry->total_amount, 2) }}</strong>
                                </td>
                                <td>
                                    @if($laundry->payment_method === 'cash')
                                        <span class="badge bg-success">Cash</span>
                                    @elseif($laundry->payment_method === 'gcash')
                                        <span class="badge bg-info">GCash</span>
                                    @elseif($laundry->payment_method === 'card')
                                        <span class="badge bg-primary">Card</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($laundry->payment_method ?? 'N/A') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($laundry->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($laundry->status === 'paid')
                                        <span class="badge bg-info">Paid</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($laundry->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4" style="color: var(--text-secondary);">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No sales found for the selected period
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($laundries->count() > 0)
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="5" class="text-end">Total:</th>
                                <th class="text-end">₱{{ number_format($summary['total_sales'], 2) }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            {{-- Pagination --}}
            @if($laundries->hasPages())
                <div class="mt-4">
                    {{ $laundries->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Payment Method Breakdown --}}
    <div class="row g-3 mt-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-credit-card me-2"></i>Payment Method Breakdown
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $paymentMethods = $laundries->groupBy('payment_method')->map(function($group) {
                            return [
                                'count' => $group->count(),
                                'total' => $group->sum('total_amount')
                            ];
                        });
                    @endphp
                    
                    @forelse($paymentMethods as $method => $data)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <strong>{{ ucfirst($method ?? 'Unknown') }}</strong>
                                <br>
                                <small style="color: var(--text-secondary);">{{ $data['count'] }} transactions</small>
                            </div>
                            <h5 class="mb-0 text-success">₱{{ number_format($data['total'], 2) }}</h5>
                        </div>
                    @empty
                        <p class="text-center mb-0" style="color: var(--text-secondary);">No data available</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>Top Services
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $topServices = $laundries->groupBy('service.name')->map(function($group) {
                            return [
                                'count' => $group->count(),
                                'total' => $group->sum('total_amount')
                            ];
                        })->sortByDesc('total')->take(5);
                    @endphp
                    
                    @forelse($topServices as $service => $data)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <strong>{{ $service ?? 'Unknown' }}</strong>
                                <br>
                                <small style="color: var(--text-secondary);">{{ $data['count'] }} orders</small>
                            </div>
                            <h5 class="mb-0 text-primary">₱{{ number_format($data['total'], 2) }}</h5>
                        </div>
                    @empty
                        <p class="text-center mb-0" style="color: var(--text-secondary);">No data available</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function exportToCSV() {
    const table = document.getElementById('salesTable');
    let csv = [];
    
    // Headers
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => {
        headers.push(th.textContent.trim());
    });
    csv.push(headers.join(','));
    
    // Rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            row.push('"' + td.textContent.trim().replace(/"/g, '""') + '"');
        });
        if (row.length > 0) {
            csv.push(row.join(','));
        }
    });
    
    // Download
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'sales_report_{{ date("Y-m-d") }}.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
@endpush
@endsection
