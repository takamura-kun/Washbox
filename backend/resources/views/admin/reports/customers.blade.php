@extends('admin.layouts.app')

@section('page-title', 'Customers Report')

@section('content')
<div class="container-fluid px-4 py-5">
    {{-- Header Section --}}
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-end">
            <div>
                <p class="text-muted mb-0" style="font-size: 0.95rem;">Customer demographics, activity, retention, ratings, and engagement data</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </a>
                <form method="POST" action="{{ route('admin.reports.export') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="type" value="customers">
                    <input type="hidden" name="filter" value="{{ request('filter', 'this_month') }}">
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download me-2"></i>Export
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="card border-0 shadow-sm mb-5 cr-card">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.reports.customers') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-600 cr-label mb-2">Date Range</label>
                    <select name="filter" class="form-select form-select-sm cr-input" id="filterSelect">
                        <option value="this_month" {{ request('filter') == 'this_month' ? 'selected' : '' }}>This Month (February)</option>
                        <option value="last_month" {{ request('filter') == 'last_month' ? 'selected' : '' }}>Last Month (January)</option>
                        <option value="last_3_months" {{ request('filter') == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                        <option value="last_6_months" {{ request('filter') == 'last_6_months' ? 'selected' : '' }}>Last 6 Months</option>
                        <option value="this_year" {{ request('filter') == 'this_year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ request('filter') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-600 cr-label mb-2">Rating Filter</label>
                    <select name="rating" class="form-select form-select-sm cr-input">
                        <option value="">All Ratings</option>
                        <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5 Stars)</option>
                        <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>⭐⭐⭐⭐ (4 Stars)</option>
                        <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>⭐⭐⭐ (3 Stars)</option>
                        <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>⭐⭐ (2 Stars)</option>
                        <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>⭐ (1 Star)</option>
                        <option value="0" {{ request('rating') == '0' ? 'selected' : '' }}>No Rating</option>
                    </select>
                </div>

                {{-- Custom Date Inputs --}}
                <div class="col-md-2" id="customDateFrom" style="display: {{ request('filter') == 'custom' ? 'block' : 'none' }};">
                    <label class="form-label fw-600 cr-label mb-2">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm cr-input" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2" id="customDateTo" style="display: {{ request('filter') == 'custom' ? 'block' : 'none' }};">
                    <label class="form-label fw-600 cr-label mb-2">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm cr-input" value="{{ request('date_to') }}">
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-funnel me-2"></i>Apply Filter
                    </button>
                </div>

                @if(request('filter') || request('date_from') || request('date_to') || request('rating'))
                    <div class="col-md-12">
                        <a href="{{ route('admin.reports.customers') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-2"></i>Clear Filters
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Key Metrics Cards --}}
    <div class="row g-3 mb-5">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm cr-stat-card h-100" data-accent="info">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="cr-stat-label mb-1">TOTAL CUSTOMERS</p>
                            <h3 class="mb-0 fw-bold cr-stat-value">{{ number_format($metrics['total_customers'] ?? 0) }}</h3>
                        </div>
                        <div class="cr-stat-icon cr-icon-info">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center text-success small">
                        <i class="bi bi-arrow-up-right me-1"></i>
                        <span>{{ $metrics['new_customers_period'] ?? 0 }} new</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm cr-stat-card h-100" data-accent="success">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="cr-stat-label mb-1">ACTIVE CUSTOMERS</p>
                            <h3 class="mb-0 fw-bold cr-stat-value">{{ number_format($metrics['active_customers'] ?? 0) }}</h3>
                        </div>
                        <div class="cr-stat-icon cr-icon-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center text-success small">
                        <i class="bi bi-arrow-up-right me-1"></i>
                        <span>{{ ($metrics['total_customers'] ?? 0) > 0 ? round(($metrics['active_customers'] ?? 0) / $metrics['total_customers'] * 100) : 0 }}% of total</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm cr-stat-card h-100" data-accent="primary">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="cr-stat-label mb-1">AVG CUSTOMER VALUE</p>
                            <h3 class="mb-0 fw-bold cr-stat-value">₱{{ number_format($metrics['avg_customer_value'] ?? 0, 2) }}</h3>
                        </div>
                        <div class="cr-stat-icon cr-icon-primary">
                            <i class="bi bi-currency-peso"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center text-muted small">
                        <i class="bi bi-dash me-1"></i>
                        <span>Lifetime value</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm cr-stat-card h-100" data-accent="warning">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="cr-stat-label mb-1">AVG RATING</p>
                            <h3 class="mb-0 fw-bold cr-stat-value">{{ number_format($metrics['avg_rating'] ?? 0, 1) }} ⭐</h3>
                        </div>
                        <div class="cr-stat-icon cr-icon-warning">
                            <i class="bi bi-star-fill"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center text-success small">
                        <i class="bi bi-arrow-up-right me-1"></i>
                        <span>{{ $metrics['rated_customers_count'] ?? 0 }} ratings</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Analytics Section --}}
    <div class="row g-3 mb-5">
        {{-- Demographics --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm cr-card">
                <div class="card-header border-0 p-4 cr-card-header">
                    <h6 class="mb-0 fw-bold cr-heading">
                        <i class="bi bi-pie-chart text-primary me-2"></i>Customer Demographics
                    </h6>
                </div>
                <div class="card-body p-4">
                    @php
                        $totalForBars = max(1, ($metrics['new_customers_period'] ?? 0) + ($metrics['returning_customers'] ?? 0) + ($metrics['inactive_customers'] ?? 0));
                    @endphp
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">New Customers</span>
                            <span class="fw-bold cr-text">{{ $metrics['new_customers_period'] ?? 0 }}</span>
                        </div>
                        <div class="progress cr-progress" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar bg-success" style="width: {{ round(($metrics['new_customers_period'] ?? 0) / $totalForBars * 100) }}%"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Returning Customers</span>
                            <span class="fw-bold cr-text">{{ $metrics['returning_customers'] ?? 0 }}</span>
                        </div>
                        <div class="progress cr-progress" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar bg-primary" style="width: {{ round(($metrics['returning_customers'] ?? 0) / $totalForBars * 100) }}%"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Inactive Customers</span>
                            <span class="fw-bold cr-text">{{ $metrics['inactive_customers'] ?? 0 }}</span>
                        </div>
                        <div class="progress cr-progress" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar bg-danger" style="width: {{ round(($metrics['inactive_customers'] ?? 0) / $totalForBars * 100) }}%"></div>
                        </div>
                    </div>
                    <hr class="cr-divider">
                    <div class="small text-muted">
                        <p class="mb-2"><strong>By Location:</strong> Data comes from customer addresses and branch associations</p>
                        <p class="mb-0"><strong>By Segment:</strong> Categorized by spending level and service frequency</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Activity Metrics --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm cr-card">
                <div class="card-header border-0 p-4 cr-card-header">
                    <h6 class="mb-0 fw-bold cr-heading">
                        <i class="bi bi-activity text-success me-2"></i>Activity & Rating Metrics
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 cr-metric-tile">
                                <p class="text-muted small mb-2">Avg Laundries / Customer</p>
                                <h4 class="mb-0 fw-bold cr-text">{{ $metrics['avg_laundries_per_customer'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 cr-metric-tile">
                                <p class="text-muted small mb-2">Laundries This Period</p>
                                <h4 class="mb-0 fw-bold cr-text">{{ $metrics['total_laundries_period'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 cr-metric-tile">
                                <p class="text-muted small mb-2">Total Revenue</p>
                                <h4 class="mb-0 fw-bold cr-text">₱{{ number_format($metrics['total_revenue_period'] ?? 0, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 cr-metric-tile">
                                <p class="text-muted small mb-2">Avg Spend / Customer</p>
                                <h4 class="mb-0 fw-bold cr-text">₱{{ number_format($metrics['avg_spend_period'] ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Customer Ratings Grid Section --}}
    @php
        $customersWithRatings = $customers->filter(function($customer) {
            return ($customer->ratings?->count() ?? 0) > 0;
        });
    @endphp

    @if($customersWithRatings->count() > 0)
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 fw-bold cr-heading">
                <i class="bi bi-star-fill text-warning me-2"></i>Customer Ratings & Reviews
            </h5>
            <span class="badge bg-primary fs-6 px-3 py-2">
                {{ $customersWithRatings->sum(function($c) { return $c->ratings?->count() ?? 0; }) }} Total Ratings
            </span>
        </div>

        <div class="ratings-grid-customers">
            @foreach($customersWithRatings as $customer)
                @php
                    $initial = strtoupper(substr($customer->name, 0, 1));
                    $colors = [
                        'A' => '#667eea', 'B' => '#764ba2', 'C' => '#f093fb', 'D' => '#4facfe',
                        'E' => '#00f2fe', 'F' => '#43e97b', 'G' => '#38f9d7', 'H' => '#fa709a',
                        'I' => '#fee140', 'J' => '#30cfd0', 'K' => '#a8edea', 'L' => '#fed6e3',
                        'M' => '#c471f5', 'N' => '#fa71cd', 'O' => '#f7971e', 'P' => '#ffd200',
                        'Q' => '#667eea', 'R' => '#f093fb', 'S' => '#4facfe', 'T' => '#00f2fe',
                        'U' => '#43e97b', 'V' => '#38f9d7', 'W' => '#fa709a', 'X' => '#fee140',
                        'Y' => '#30cfd0', 'Z' => '#a8edea'
                    ];
                    $color1 = $colors[$initial] ?? '#667eea';
                    $color2 = '#764ba2';
                    $avgRating = $customer->ratings->avg('rating');
                    $ratingCount = $customer->ratings->count();
                    $latestRating = $customer->ratings->sortByDesc('created_at')->first();
                @endphp

                <div class="rating-card-customers">
                    <!-- Customer -->
                    <div class="rating-customer-customers">
                        <div class="customer-avatar-customers" style="background: linear-gradient(135deg, {{ $color1 }} 0%, {{ $color2 }} 100%);">
                            {{ $initial }}
                        </div>
                        <div class="customer-info-customers">
                            <div class="customer-name-customers">{{ $customer->name }}</div>
                            <div class="customer-email-customers">{{ $customer->email }}</div>
                        </div>
                    </div>

                    <!-- Average Stars -->
                    <div class="rating-stars-section-customers">
                        <div>
                            <div class="rating-stars-customers">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= round($avgRating) ? '-fill' : '' }}{{ $i > round($avgRating) ? ' empty' : '' }}"></i>
                                @endfor
                            </div>
                            <div class="rating-count-label">{{ $ratingCount }} rating{{ $ratingCount > 1 ? 's' : '' }}</div>
                        </div>
                        <div class="rating-score-customers">{{ number_format($avgRating, 1) }}/5</div>
                    </div>

                    <!-- Latest Comment Preview -->
                    @if($latestRating->comment)
                        <div class="rating-comment-customers">
                            <div class="comment-label-customers">
                                <i class="bi bi-chat-left-quote"></i> Latest Comment
                            </div>
                            <div class="comment-text-customers">"{{ Str::limit($latestRating->comment, 100) }}"</div>
                        </div>
                    @else
                        <div class="rating-comment-customers">
                            <div class="comment-text-customers" style="color: #94a3b8; font-style: normal;">
                                No comments provided
                            </div>
                        </div>
                    @endif

                    <!-- Footer with View All Button -->
                    <div class="rating-footer-customers">
                        <div class="rating-date-customers">
                            <div class="date-main-customers">Latest: {{ $latestRating->created_at->format('M j, Y') }}</div>
                            <div class="date-time-customers">{{ $latestRating->created_at->format('g:i A') }}</div>
                        </div>
                        <button class="btn btn-sm btn-outline-primary view-all-ratings-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#allRatingsModal{{ $customer->id }}">
                            <i class="bi bi-eye"></i> View All ({{ $ratingCount }})
                        </button>
                    </div>
                </div>

                {{-- Modal with All Ratings --}}
                <div class="modal fade" id="allRatingsModal{{ $customer->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title">{{ $customer->name }} - All Ratings</h5>
                                    <p class="mb-0 small text-muted">{{ $ratingCount }} total rating{{ $ratingCount > 1 ? 's' : '' }} • Average: {{ number_format($avgRating, 1) }}/5</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="ratings-timeline">
                                    @foreach($customer->ratings->sortByDesc('created_at') as $rating)
                                        <div class="timeline-rating-item">
                                            <div class="timeline-rating-header">
                                                <div class="timeline-stars">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="bi bi-star{{ $i <= $rating->rating ? '-fill' : '' }}" style="color: #f59e0b;"></i>
                                                    @endfor
                                                    <span class="ms-2 fw-bold">{{ $rating->rating }}/5</span>
                                                </div>
                                                <div class="timeline-date">
                                                    <i class="bi bi-calendar3"></i>
                                                    {{ $rating->created_at->format('M j, Y') }} at {{ $rating->created_at->format('g:i A') }}
                                                </div>
                                            </div>
                                            
                                            <div class="timeline-context">
                                                @if($rating->laundry_id)
                                                    <span class="badge bg-info">
                                                        <i class="bi bi-basket"></i> Laundry #{{ $rating->laundry_id }}
                                                    </span>
                                                @elseif($rating->branch_id)
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-geo-alt-fill"></i> {{ $rating->branch->name ?? 'N/A' }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-star"></i> General Rating
                                                    </span>
                                                @endif
                                            </div>

                                            @if($rating->comment)
                                                <div class="timeline-comment">
                                                    <i class="bi bi-chat-left-quote text-muted"></i>
                                                    <p class="mb-0">"{{ $rating->comment }}"</p>
                                                </div>
                                            @else
                                                <div class="timeline-comment text-muted" style="font-style: normal;">
                                                    <i class="bi bi-chat-left text-muted"></i>
                                                    <p class="mb-0">No comment provided</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Customers Table --}}
    <div class="card border-0 shadow-sm cr-card">
        <div class="card-header border-0 p-4 d-flex justify-content-between align-items-center cr-card-header">
            <h6 class="mb-0 fw-bold cr-heading">
                <i class="bi bi-table me-2"></i>Detailed Customer List
            </h6>
            <form method="POST" action="{{ route('admin.reports.export') }}" class="d-inline">
                @csrf
                <input type="hidden" name="type" value="customers">
                <input type="hidden" name="filter" value="{{ request('filter', 'this_month') }}">
                <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                <button type="submit" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-download me-2"></i>Export CSV
                </button>
            </form>
        </div>
        <div class="card-body p-0">
            @if($customers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0 cr-table">
                        <thead>
                            <tr>
                                <th class="fw-600">Customer Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th class="text-center">Laundries</th>
                                <th class="text-end">Spent</th>
                                <th class="text-center">Avg Rating</th>
                                <th class="text-center">Rating Count</th>
                                <th class="text-center">Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                                @php
                                    $totalSpent  = $customer->laundries()->where('payment_status', 'paid')->sum('total_amount');
                                    $isActive    = $customer->laundries()->where('created_at', '>=', now()->subDays(30))->exists();
                                    $avgRating   = $customer->ratings?->avg('rating') ?? 0;
                                    $ratingCount = $customer->ratings?->count() ?? 0;
                                    $ratingClass = $avgRating > 0 ? ($avgRating >= 4 ? 'bg-success' : ($avgRating >= 3 ? 'bg-warning text-dark' : 'bg-danger')) : 'bg-secondary';
                                @endphp
                                <tr class="cr-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="cr-avatar me-3">
                                                <span>{{ substr($customer->name, 0, 1) }}</span>
                                            </div>
                                            <a href="{{ route('admin.customers.show', $customer->id) }}" class="cr-link fw-500">
                                                {{ $customer->name }}
                                            </a>
                                        </div>
                                    </td>
                                    <td><small class="text-muted">{{ $customer->email }}</small></td>
                                    <td><small>{{ $customer->phone }}</small></td>
                                    <td class="text-center">
                                        <span class="badge cr-badge-neutral">{{ $customer->laundries_count ?? 0 }}</span>
                                    </td>
                                    <td class="text-end fw-600 cr-text">₱{{ number_format($totalSpent, 2) }}</td>
                                    <td class="text-center">
                                        @if($ratingCount > 0)
                                            <button type="button" class="badge {{ $ratingClass }} border-0"
                                                data-bs-toggle="modal" data-bs-target="#ratingModal{{ $customer->id }}"
                                                style="cursor: pointer;">
                                                {{ number_format($avgRating, 1) }} ⭐
                                            </button>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($ratingCount > 0)
                                            <span class="badge bg-info">{{ $ratingCount }}</span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($isActive)
                                            <span class="badge cr-badge-active">Active</span>
                                        @else
                                            <span class="badge cr-badge-inactive">Inactive</span>
                                        @endif
                                    </td>
                                    <td><small class="text-muted">{{ $customer->created_at->format('M d, Y') }}</small></td>
                                </tr>

                                {{-- Rating Details Modal --}}
                                @if($ratingCount > 0)
                                    <div class="modal fade" id="ratingModal{{ $customer->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-sm">
                                            <div class="modal-content cr-modal">
                                                <div class="modal-header cr-modal-header">
                                                    <h6 class="modal-title cr-heading">{{ $customer->name }} — Ratings</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body cr-modal-body">
                                                    @foreach($customer->ratings->sortByDesc('created_at') as $rating)
                                                        <div class="mb-3 pb-3 cr-rating-row">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <span class="text-warning">
                                                                    @for($i = 0; $i < $rating->rating; $i++)
                                                                        <i class="bi bi-star-fill"></i>
                                                                    @endfor
                                                                    @for($i = $rating->rating; $i < 5; $i++)
                                                                        <i class="bi bi-star"></i>
                                                                    @endfor
                                                                </span>
                                                                <small class="text-muted">{{ $rating->created_at->format('M d, Y') }}</small>
                                                            </div>
                                                            <small class="text-muted d-block mb-2">
                                                                @if($rating->laundry_id)
                                                                    Laundry #{{ $rating->laundry_id }}
                                                                @elseif($rating->branch_id)
                                                                    Branch: {{ $rating->branch->name ?? 'N/A' }}
                                                                @else
                                                                    N/A
                                                                @endif
                                                            </small>
                                                            <small class="cr-text">{{ $rating->comment }}</small>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-top cr-pagination-border">
                    {{ $customers->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">No customers found for the selected period</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
/* ================================================================
   CUSTOMERS REPORT — cr-* component styles
   Light + Dark Mode via [data-theme="dark"]
   ================================================================ */

/* ── Shared tokens ───────────────────────────────────────────────── */
.fw-600 { font-weight: 600; }

/* ── Cards ───────────────────────────────────────────────────────── */
.cr-card {
    border-radius: 12px;
    background: #ffffff;
    color: #1e293b;
}

.cr-card-header {
    background: #f8fafc !important;
    border-bottom: 1px solid #f1f5f9 !important;
}

.cr-heading  { color: #1e293b; }
.cr-text     { color: #1e293b; }
.cr-label    { color: #1e293b; }
.cr-divider  { border-color: #e2e8f0; opacity: 1; }

/* ── Stat cards ──────────────────────────────────────────────────── */
.cr-stat-card {
    border-radius: 12px;
    background: #ffffff;
    position: relative;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.cr-stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    border-radius: 12px 12px 0 0;
}

.cr-stat-card[data-accent="info"]::before    { background: linear-gradient(90deg, #0dcaf0, transparent); }
.cr-stat-card[data-accent="success"]::before { background: linear-gradient(90deg, #198754, transparent); }
.cr-stat-card[data-accent="primary"]::before { background: linear-gradient(90deg, #0d6efd, transparent); }
.cr-stat-card[data-accent="warning"]::before { background: linear-gradient(90deg, #ffc107, transparent); }

.cr-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.10) !important;
}

.cr-stat-label {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.4px;
    color: #64748b;
    text-transform: uppercase;
}

.cr-stat-value { color: #1e293b; }

/* Stat icons */
.cr-stat-icon {
    width: 48px; height: 48px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}
.cr-icon-info    { background: linear-gradient(135deg, #0dcaf0, #0aa2c0); }
.cr-icon-success { background: linear-gradient(135deg, #198754, #146c43); }
.cr-icon-primary { background: linear-gradient(135deg, #0d6efd, #0a58ca); }
.cr-icon-warning { background: linear-gradient(135deg, #ffc107, #e0a800); }

/* ── Metric tiles (activity section) ────────────────────────────── */
.cr-metric-tile {
    background: #f8fafc;
    border: 1px solid #f1f5f9;
}

/* ── Progress bar track ──────────────────────────────────────────── */
.cr-progress { background: #e2e8f0; }

/* ── Form inputs ─────────────────────────────────────────────────── */
.cr-input {
    background: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #1e293b !important;
    border-radius: 8px;
}
.cr-input:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.15) !important;
}
.cr-input option { background: #ffffff; color: #1e293b; }

/* ── Table ───────────────────────────────────────────────────────── */
.cr-table {
    --bs-table-color:        #1e293b;
    --bs-table-bg:           transparent;
    --bs-table-border-color: #f1f5f9;
    --bs-table-hover-bg:     #f8fafc;
    --bs-table-hover-color:  #1e293b;
}

.cr-table thead th {
    background: #f1f5f9 !important;
    color: #64748b !important;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    border-bottom: 1px solid #e2e8f0 !important;
    padding: 0.875rem 1rem;
    white-space: nowrap;
}

.cr-table tbody td {
    padding: 0.875rem 1rem;
    vertical-align: middle;
    border-color: #f1f5f9 !important;
    color: #1e293b;
}

.cr-row:hover td { background: #f8fafc !important; }

/* ── Avatar ──────────────────────────────────────────────────────── */
.cr-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: #e2e8f0;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem;
    font-weight: 700;
    color: #475569;
    flex-shrink: 0;
}

/* ── Links ───────────────────────────────────────────────────────── */
.cr-link {
    color: #2563eb;
    text-decoration: none;
}
.cr-link:hover { color: #1d4ed8; text-decoration: underline; }

/* ── Badges ──────────────────────────────────────────────────────── */
.cr-badge-neutral {
    background: #e2e8f0 !important;
    color: #475569 !important;
}

.cr-badge-active {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16,185,129,0.2);
}

.cr-badge-inactive {
    background: rgba(100, 116, 139, 0.12);
    color: #64748b;
    border: 1px solid rgba(100,116,139,0.2);
}

/* ── Pagination border ───────────────────────────────────────────── */
.cr-pagination-border { border-color: #f1f5f9 !important; }

/* ── Modal ───────────────────────────────────────────────────────── */
.cr-modal        { background: #ffffff; border-color: #e2e8f0; }
.cr-modal-header { background: #f8fafc !important; border-color: #e2e8f0 !important; }
.cr-modal-body   { background: #ffffff; color: #1e293b; }
.cr-rating-row   { border-bottom: 1px solid #f1f5f9; }

/* ================================================================
   DARK MODE
   ================================================================ */

[data-theme="dark"] .cr-card {
    background: #1e293b;
    color: #f1f5f9;
    border-color: #334155 !important;
}

[data-theme="dark"] .cr-card-header {
    background: #0f172a !important;
    border-color: #334155 !important;
}

[data-theme="dark"] .cr-heading  { color: #f1f5f9; }
[data-theme="dark"] .cr-text     { color: #f1f5f9; }
[data-theme="dark"] .cr-label    { color: #cbd5e1; }
[data-theme="dark"] .cr-divider  { border-color: #334155; }

/* Stat cards */
[data-theme="dark"] .cr-stat-card {
    background: #1e293b;
    border-color: #334155 !important;
}

[data-theme="dark"] .cr-stat-card:hover {
    box-shadow: 0 12px 24px rgba(0,0,0,0.35) !important;
}

[data-theme="dark"] .cr-stat-label  { color: #94a3b8; }
[data-theme="dark"] .cr-stat-value  { color: #f1f5f9; }

/* Metric tiles */
[data-theme="dark"] .cr-metric-tile {
    background: #0f172a;
    border-color: #334155;
}

/* Progress bar */
[data-theme="dark"] .cr-progress { background: #334155; }

/* Form inputs */
[data-theme="dark"] .cr-input {
    background: #0f172a !important;
    border-color: #334155 !important;
    color: #f1f5f9 !important;
}
[data-theme="dark"] .cr-input:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.2) !important;
}
[data-theme="dark"] .cr-input option { background: #1e293b; color: #f1f5f9; }

/* Table */
[data-theme="dark"] .cr-table {
    --bs-table-color:        #f1f5f9;
    --bs-table-bg:           transparent;
    --bs-table-border-color: #334155;
    --bs-table-hover-bg:     rgba(255,255,255,0.04);
    --bs-table-hover-color:  #f1f5f9;
}

[data-theme="dark"] .cr-table thead th {
    background: #0f172a !important;
    color: #94a3b8 !important;
    border-color: #334155 !important;
}

[data-theme="dark"] .cr-table tbody td {
    color: #f1f5f9 !important;
    border-color: #334155 !important;
}

[data-theme="dark"] .cr-row:hover td { background: rgba(255,255,255,0.04) !important; }

/* Avatar */
[data-theme="dark"] .cr-avatar {
    background: #334155;
    color: #94a3b8;
}

/* Links */
[data-theme="dark"] .cr-link { color: #60a5fa; }
[data-theme="dark"] .cr-link:hover { color: #93c5fd; }

/* Badges */
[data-theme="dark"] .cr-badge-neutral {
    background: #334155 !important;
    color: #94a3b8 !important;
}

[data-theme="dark"] .cr-badge-active {
    background: rgba(16,185,129,0.15);
    color: #4ade80;
    border-color: rgba(16,185,129,0.3);
}

[data-theme="dark"] .cr-badge-inactive {
    background: rgba(100,116,139,0.15);
    color: #94a3b8;
    border-color: rgba(100,116,139,0.3);
}

/* Pagination */
[data-theme="dark"] .cr-pagination-border { border-color: #334155 !important; }

/* Bootstrap page-link */
[data-theme="dark"] .page-link {
    background: #1e293b;
    border-color: #334155;
    color: #94a3b8;
}
[data-theme="dark"] .page-item.active .page-link {
    background: #3D3B6B;
    border-color: #3D3B6B;
    color: #fff;
}
[data-theme="dark"] .page-link:hover { background: #334155; color: #f1f5f9; }
[data-theme="dark"] .page-item.disabled .page-link { background: #1e293b; color: #475569; }

/* Modal */
[data-theme="dark"] .cr-modal        { background: #1e293b; border-color: #334155; }
[data-theme="dark"] .cr-modal-header { background: #0f172a !important; border-color: #334155 !important; }
[data-theme="dark"] .cr-modal-body   { background: #1e293b; color: #f1f5f9; }
[data-theme="dark"] .cr-rating-row   { border-color: #334155; }
[data-theme="dark"] .cr-modal .btn-close { filter: invert(1) brightness(1.5); }

/* Bootstrap badge overrides in dark */
[data-theme="dark"] .badge.bg-info    { background: rgba(59,130,246,0.25) !important; color: #60a5fa !important; }
[data-theme="dark"] .badge.bg-success { background: rgba(16,185,129,0.25) !important; color: #4ade80 !important; }
[data-theme="dark"] .badge.bg-warning { background: rgba(245,158,11,0.25) !important; color: #fbbf24 !important; }
[data-theme="dark"] .badge.bg-danger  { background: rgba(239,68,68,0.25)  !important; color: #f87171 !important; }
[data-theme="dark"] .badge.bg-secondary { background: #475569 !important; color: #f1f5f9 !important; }

/* Outline buttons */
[data-theme="dark"] .btn-outline-secondary {
    border-color: #475569; color: #94a3b8;
}
[data-theme="dark"] .btn-outline-secondary:hover { background: #334155; color: #f1f5f9; }

[data-theme="dark"] .btn-outline-primary {
    border-color: #6366f1; color: #a5b4fc;
}
[data-theme="dark"] .btn-outline-primary:hover { background: #6366f1; color: #fff; }

[data-theme="dark"] .btn-outline-success {
    border-color: #10b981; color: #4ade80;
}
[data-theme="dark"] .btn-outline-success:hover { background: #10b981; color: #fff; }

/* Text helpers */
[data-theme="dark"] .text-muted       { color: #94a3b8 !important; }
[data-theme="dark"] .text-success     { color: #4ade80 !important; }
[data-theme="dark"] .text-dark        { color: #f1f5f9 !important; }

/* progress bar text */
[data-theme="dark"] .fw-bold.cr-text  { color: #f1f5f9; }

/* ================================================================
   CUSTOMER RATINGS GRID - Modern Card Layout
   ================================================================ */

.ratings-grid-customers {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.rating-card-customers {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    position: relative;
    overflow: hidden;
}

.rating-card-customers::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #f59e0b, #fbbf24);
}

.rating-card-customers:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
    border-color: #f59e0b;
}

.rating-customer-customers {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.customer-avatar-customers {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
}

.customer-info-customers {
    flex: 1;
    min-width: 0;
}

.customer-name-customers {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.customer-email-customers {
    font-size: 0.75rem;
    color: #94a3b8;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rating-stars-section-customers {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    background: rgba(251, 191, 36, 0.1);
    border-radius: 12px;
}

.rating-stars-customers {
    display: flex;
    gap: 4px;
}

.rating-stars-customers i {
    font-size: 1.25rem;
    color: #f59e0b;
}

.rating-stars-customers i.empty {
    color: #e2e8f0;
}

.rating-score-customers {
    font-size: 1.5rem;
    font-weight: 800;
    color: #0f172a;
}

.rating-comment-customers {
    flex: 1;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 12px;
    border-left: 3px solid #f59e0b;
}

.comment-label-customers {
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.comment-text-customers {
    font-size: 0.875rem;
    color: #475569;
    line-height: 1.6;
    font-style: italic;
}

.rating-footer-customers {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

.rating-date-customers {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.date-main-customers {
    font-size: 0.875rem;
    font-weight: 600;
    color: #475569;
}

.date-time-customers {
    font-size: 0.75rem;
    color: #94a3b8;
}

.rating-branch-customers {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: #94a3b8;
    padding: 0.5rem 0.75rem;
    background: #f8fafc;
    border-radius: 8px;
}

.rating-count-label {
    font-size: 0.7rem;
    color: #94a3b8;
    margin-top: 0.25rem;
    font-weight: 500;
}

.view-all-ratings-btn {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    white-space: nowrap;
}

/* Timeline Modal Styles */
.ratings-timeline {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.timeline-rating-item {
    padding: 1.25rem;
    background: #f8fafc;
    border-radius: 12px;
    border-left: 4px solid #f59e0b;
    transition: all 0.2s ease;
}

.timeline-rating-item:hover {
    background: #f1f5f9;
    transform: translateX(4px);
}

.timeline-rating-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.timeline-stars {
    display: flex;
    align-items: center;
    gap: 2px;
}

.timeline-stars i {
    font-size: 1rem;
}

.timeline-date {
    font-size: 0.8rem;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.timeline-context {
    margin-bottom: 0.75rem;
}

.timeline-comment {
    padding: 0.75rem 1rem;
    background: white;
    border-radius: 8px;
    font-size: 0.875rem;
    color: #475569;
    font-style: italic;
    display: flex;
    gap: 0.75rem;
    align-items: start;
}

.timeline-comment i {
    font-size: 1rem;
    margin-top: 0.125rem;
    flex-shrink: 0;
}

/* Dark mode for customer ratings grid */
[data-theme="dark"] .rating-card-customers {
    background: #1e293b;
    border-color: #334155;
}

[data-theme="dark"] .customer-name-customers {
    color: #f1f5f9;
}

[data-theme="dark"] .customer-email-customers {
    color: #64748b;
}

[data-theme="dark"] .rating-stars-section-customers {
    background: rgba(251, 191, 36, 0.1);
}

[data-theme="dark"] .rating-score-customers {
    color: #f1f5f9;
}

[data-theme="dark"] .rating-comment-customers {
    background: #0f172a;
}

[data-theme="dark"] .comment-text-customers {
    color: #cbd5e1;
}

[data-theme="dark"] .rating-footer-customers {
    border-top-color: #334155;
}

[data-theme="dark"] .date-main-customers {
    color: #cbd5e1;
}

[data-theme="dark"] .date-time-customers {
    color: #64748b;
}

[data-theme="dark"] .rating-branch-customers {
    background: #0f172a;
    color: #64748b;
}

[data-theme="dark"] .rating-count-label {
    color: #64748b;
}

[data-theme="dark"] .view-all-ratings-btn {
    background: #0f172a;
    border-color: #334155;
    color: #94a3b8;
}

[data-theme="dark"] .view-all-ratings-btn:hover {
    background: #334155;
    color: #f1f5f9;
}

/* Timeline Dark Mode */
[data-theme="dark"] .modal-content {
    background: #1e293b;
    color: #f1f5f9;
}

[data-theme="dark"] .modal-header {
    background: #0f172a;
    border-bottom-color: #334155;
}

[data-theme="dark"] .modal-title {
    color: #f1f5f9;
}

[data-theme="dark"] .modal-body {
    background: #1e293b;
}

[data-theme="dark"] .timeline-rating-item {
    background: #0f172a;
    border-left-color: #fbbf24;
}

[data-theme="dark"] .timeline-rating-item:hover {
    background: #1e293b;
}

[data-theme="dark"] .timeline-date {
    color: #94a3b8;
}

[data-theme="dark"] .timeline-comment {
    background: #1e293b;
    color: #cbd5e1;
}

[data-theme="dark"] .btn-close {
    filter: invert(1) brightness(1.5);
}

/* Responsive */
@media (max-width: 768px) {
    .ratings-grid-customers {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 1024px) {
    .ratings-grid-customers {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
}
</style>

<script>
document.getElementById('filterSelect').addEventListener('change', function() {
    const customDateFrom = document.getElementById('customDateFrom');
    const customDateTo   = document.getElementById('customDateTo');
    if (this.value === 'custom') {
        customDateFrom.style.display = 'block';
        customDateTo.style.display   = 'block';
    } else {
        customDateFrom.style.display = 'none';
        customDateTo.style.display   = 'none';
    }
});
</script>
@endsection
