@extends('admin.layouts.app')

@section('title', "Today's Revenue Breakdown")

@section('content')

<div class="container-xl px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Today's Revenue Breakdown</h1>
            <small class="text-muted">{{ now()->format('l, F j, Y') }}</small>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    {{-- Total Revenue Card --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h5 class="card-title mb-0">Total Revenue Today</h5>
                    <h2 class="display-4 fw-bold mt-2">₱{{ number_format($totalRevenue, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue by Payment Method --}}
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">By Payment Method</h5>
                </div>
                <div class="card-body">
                    @if($byPaymentMethod->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Method</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Count</th>
                                        <th class="text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byPaymentMethod as $payment)
                                        @php
                                            $percentage = ($payment->total / $totalRevenue) * 100;
                                            $colors = [
                                                'cash' => '#10b981',
                                                'gcash' => '#f59e0b',
                                                'card' => '#3b82f6',
                                                'bank' => '#8b5cf6'
                                            ];
                                            $color = $colors[strtolower($payment->payment_method)] ?? '#6b7280';
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge" style="background-color: {{ $color }};">
                                                    {{ ucfirst($payment->payment_method) }}
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold">₱{{ number_format($payment->total, 2) }}</td>
                                            <td class="text-end">{{ $payment->count }}</td>
                                            <td class="text-end">{{ round($percentage, 1) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No revenue data available</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Revenue by Service Type --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">By Service Type</h5>
                </div>
                <div class="card-body">
                    @if($byServiceType->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Service</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Count</th>
                                        <th class="text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byServiceType as $service)
                                        @php
                                            $percentage = ($service->total / $totalRevenue) * 100;
                                        @endphp
                                        <tr>
                                            <td>{{ $service->service_name }}</td>
                                            <td class="text-end fw-bold">₱{{ number_format($service->total, 2) }}</td>
                                            <td class="text-end">{{ $service->count }}</td>
                                            <td class="text-end">{{ round($percentage, 1) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No service data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue by Branch --}}
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">By Branch</h5>
                </div>
                <div class="card-body">
                    @if($byBranch->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Branch</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Count</th>
                                        <th class="text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byBranch as $branch)
                                        @php
                                            $percentage = ($branch->total / $totalRevenue) * 100;
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.branches.show', $branch->branch_id) }}" class="text-decoration-none">
                                                    {{ $branch->branch_name }}
                                                </a>
                                            </td>
                                            <td class="text-end fw-bold">₱{{ number_format($branch->total, 2) }}</td>
                                            <td class="text-end">{{ $branch->count }}</td>
                                            <td class="text-end">{{ round($percentage, 1) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No branch data available</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Revenue by Payment Status --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">By Payment Status</h5>
                </div>
                <div class="card-body">
                    @if($byPaymentStatus->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Status</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Count</th>
                                        <th class="text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byPaymentStatus as $status)
                                        @php
                                            $percentage = ($status->total / $totalRevenue) * 100;
                                            $statusColors = [
                                                'paid' => 'success',
                                                'pending' => 'warning',
                                                'failed' => 'danger'
                                            ];
                                            $statusColor = $statusColors[strtolower($status->payment_status)] ?? 'secondary';
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $statusColor }}">
                                                    {{ ucfirst($status->payment_status) }}
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold">₱{{ number_format($status->total, 2) }}</td>
                                            <td class="text-end">{{ $status->count }}</td>
                                            <td class="text-end">{{ round($percentage, 1) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No payment status data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="row">
        <div class="col-12">
            <div class="d-flex gap-2">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                </a>
                <a href="{{ route('admin.reports.revenue') }}" class="btn btn-outline-primary">
                    <i class="bi bi-file-earmark-pdf me-2"></i>View Full Report
                </a>
            </div>
        </div>
    </div>

</div>

@endsection
