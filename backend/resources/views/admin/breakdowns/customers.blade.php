@extends('admin.layouts.app')

@section('title', 'Active Customers Breakdown')

@section('content')

<div class="container-xl px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Active Customers Breakdown</h1>
            <small class="text-muted">{{ now()->format('l, F j, Y') }}</small>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    {{-- Total Customers Card --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-gradient" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <h5 class="card-title mb-0">Total Active Customers</h5>
                    <h2 class="display-4 fw-bold mt-2">{{ number_format($totalActiveCustomers) }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Customers by Registration Type --}}
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">By Registration Type</h5>
                </div>
                <div class="card-body">
                    @if($byRegistrationType->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th class="text-end">Count</th>
                                        <th class="text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byRegistrationType as $type)
                                        @php
                                            $percentage = ($type->count / $totalActiveCustomers) * 100;
                                            $typeColors = [
                                                'walk_in' => 'info',
                                                'self_registered' => 'success',
                                                'mobile_app' => 'primary'
                                            ];
                                            $typeColor = $typeColors[strtolower($type->registration_type)] ?? 'secondary';
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $typeColor }}">
                                                    {{ ucfirst(str_replace('_', ' ', $type->registration_type)) }}
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold">{{ $type->count }}</td>
                                            <td class="text-end">{{ round($percentage, 1) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No registration type data available</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Customers by Branch --}}
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
                                        <th class="text-end">Count</th>
                                        <th class="text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byBranch as $branch)
                                        @php
                                            $percentage = ($branch->count / $totalActiveCustomers) * 100;
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.branches.show', $branch->branch_id) }}" class="text-decoration-none">
                                                    {{ $branch->branch_name }}
                                                </a>
                                            </td>
                                            <td class="text-end fw-bold">{{ $branch->count }}</td>
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
    </div>

    {{-- Customers by Activity Level --}}
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">By Activity Level (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    @if($byActivityLevel->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Activity Level</th>
                                        <th class="text-end">Count</th>
                                        <th class="text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byActivityLevel as $activity)
                                        @php
                                            $percentage = ($activity->count / $totalActiveCustomers) * 100;
                                            $activityColors = [
                                                'Very Active (5+ orders)' => 'success',
                                                'Active (2-4 orders)' => 'info',
                                                'Moderate (1 order)' => 'warning',
                                                'Inactive (0 orders)' => 'danger'
                                            ];
                                            $activityColor = $activityColors[$activity->activity_level] ?? 'secondary';
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $activityColor }}">
                                                    {{ $activity->activity_level }}
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold">{{ $activity->count }}</td>
                                            <td class="text-end">{{ round($percentage, 1) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No activity data available</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Customers by Rating --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">By Customer Rating</h5>
                </div>
                <div class="card-body">
                    @if($byRating->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Rating</th>
                                        <th class="text-end">Count</th>
                                        <th class="text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byRating as $rating)
                                        @php
                                            $percentage = ($rating->count / $totalActiveCustomers) * 100;
                                            $ratingColors = [
                                                5 => 'success',
                                                4 => 'info',
                                                3 => 'warning',
                                                2 => 'orange',
                                                1 => 'danger'
                                            ];
                                            $ratingColor = $ratingColors[$rating->rating] ?? 'secondary';
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $ratingColor }}">
                                                    {{ $rating->rating }} <i class="bi bi-star-fill"></i>
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold">{{ $rating->count }}</td>
                                            <td class="text-end">{{ round($percentage, 1) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No rating data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Top 10 Customers by Spending --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">Top 10 Customers by Lifetime Spending</h5>
                </div>
                <div class="card-body">
                    @if($topCustomers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Customer Name</th>
                                        <th class="text-end">Total Spent</th>
                                        <th class="text-end">Orders</th>
                                        <th class="text-end">Avg Order</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topCustomers as $index => $customer)
                                        @php
                                            $orderCount = $customer->laundries->count();
                                            $avgOrder = $orderCount > 0 ? $customer->total_spent / $orderCount : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.customers.show', $customer->id) }}" class="text-decoration-none">
                                                    {{ $customer->name }}
                                                </a>
                                            </td>
                                            <td class="text-end fw-bold">₱{{ number_format($customer->total_spent, 2) }}</td>
                                            <td class="text-end">{{ $orderCount }}</td>
                                            <td class="text-end">₱{{ number_format($avgOrder, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No customer data available</p>
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
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-people me-2"></i>View All Customers
                </a>
            </div>
        </div>
    </div>

</div>

@endsection
