@extends('branch.layouts.app')

@section('title', 'Customer Ratings')
@section('page-title', 'CUSTOMER RATINGS')

@push('styles')
<style>
/* Modern Card Grid Ratings Design */
:root {
    --rating-bg: #f8fafc;
    --rating-card: #ffffff;
    --rating-border: #e2e8f0;
    --rating-text-1: #0f172a;
    --rating-text-2: #475569;
    --rating-text-3: #94a3b8;
    --rating-gold: #f59e0b;
    --rating-gold-light: #fef3c7;
    --rating-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    --rating-shadow-hover: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
}

[data-theme="dark"] {
    --rating-bg: #0f172a;
    --rating-card: #1e293b;
    --rating-border: #334155;
    --rating-text-1: #f1f5f9;
    --rating-text-2: #cbd5e1;
    --rating-text-3: #64748b;
    --rating-gold: #fbbf24;
    --rating-gold-light: rgba(251, 191, 36, 0.1);
    --rating-shadow: 0 4px 6px -1px rgba(0,0,0,0.3), 0 2px 4px -1px rgba(0,0,0,0.2);
    --rating-shadow-hover: 0 20px 25px -5px rgba(0,0,0,0.4), 0 10px 10px -5px rgba(0,0,0,0.3);
}

.ratings-page {
    background: var(--rating-bg);
    min-height: 100vh;
    padding: 2rem 1.5rem;
}

/* Header Section */
.ratings-header {
    margin-bottom: 2rem;
}

.ratings-title {
    font-size: 2rem;
    font-weight: 800;
    color: var(--rating-text-1);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.ratings-title i {
    color: var(--rating-gold);
}

.ratings-subtitle {
    color: var(--rating-text-2);
    font-size: 1rem;
}

/* Stats Bar */
.ratings-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--rating-card);
    border: 1px solid var(--rating-border);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: var(--rating-shadow);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--rating-shadow-hover);
}

.stat-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--rating-text-3);
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--rating-text-1);
    line-height: 1;
    margin-bottom: 0.5rem;
}

.stat-stars {
    display: flex;
    gap: 4px;
}

.stat-stars i {
    color: var(--rating-gold);
    font-size: 1rem;
}

/* Filters */
.ratings-filters {
    background: var(--rating-card);
    border: 1px solid var(--rating-border);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--rating-shadow);
}

.filter-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.filter-group label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--rating-text-3);
    margin-bottom: 0.5rem;
}

.filter-input,
.filter-select {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--rating-border);
    border-radius: 12px;
    background: var(--rating-bg);
    color: var(--rating-text-1);
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.filter-input:focus,
.filter-select:focus {
    outline: none;
    border-color: var(--rating-gold);
    box-shadow: 0 0 0 3px var(--rating-gold-light);
}

.filter-btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-btn-primary {
    background: var(--rating-gold);
    color: #000;
}

.filter-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--rating-shadow);
}

.filter-btn-secondary {
    background: var(--rating-border);
    color: var(--rating-text-2);
}

/* Ratings Grid */
.ratings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

/* Rating Card */
.rating-card {
    background: var(--rating-card);
    border: 1px solid var(--rating-border);
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: var(--rating-shadow);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    position: relative;
    overflow: hidden;
}

.rating-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--rating-gold), #fbbf24);
}

.rating-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--rating-shadow-hover);
    border-color: var(--rating-gold);
}

/* Customer Info */
.rating-customer {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.customer-avatar {
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

.customer-info {
    flex: 1;
    min-width: 0;
}

.customer-name {
    font-size: 1rem;
    font-weight: 700;
    color: var(--rating-text-1);
    margin-bottom: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.customer-email {
    font-size: 0.75rem;
    color: var(--rating-text-3);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Rating Stars */
.rating-stars-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    background: var(--rating-gold-light);
    border-radius: 12px;
}

.rating-stars {
    display: flex;
    gap: 4px;
}

.rating-stars i {
    font-size: 1.25rem;
    color: var(--rating-gold);
}

.rating-stars i.empty {
    color: var(--rating-border);
}

.rating-score {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--rating-text-1);
}

.rating-count-label {
    font-size: 0.75rem;
    color: var(--rating-text-3);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Comment */
.rating-comment {
    flex: 1;
    padding: 1rem;
    background: var(--rating-bg);
    border-radius: 12px;
    border-left: 3px solid var(--rating-gold);
}

.comment-label {
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--rating-text-3);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.comment-text {
    font-size: 0.875rem;
    color: var(--rating-text-2);
    line-height: 1.6;
    font-style: italic;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Card Footer */
.rating-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 1rem;
    border-top: 1px solid var(--rating-border);
}

.rating-date {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.date-main {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--rating-text-2);
}

.date-time {
    font-size: 0.75rem;
    color: var(--rating-text-3);
}

.rating-branch {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: var(--rating-text-3);
    padding: 0.5rem 0.75rem;
    background: var(--rating-bg);
    border-radius: 8px;
}

.view-all-ratings-btn {
    padding: 0.75rem 1.25rem;
    background: var(--rating-gold);
    color: #000;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.view-all-ratings-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--rating-shadow);
}

/* Modal Styles */
.ratings-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.ratings-modal.active {
    display: flex;
}

.modal-content {
    background: var(--rating-card);
    border-radius: 20px;
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.modal-header {
    padding: 2rem;
    border-bottom: 1px solid var(--rating-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--rating-text-1);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.modal-close {
    width: 40px;
    height: 40px;
    border: none;
    background: var(--rating-bg);
    border-radius: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: var(--rating-text-2);
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: var(--rating-border);
    transform: rotate(90deg);
}

.modal-body {
    padding: 2rem;
    overflow-y: auto;
}

/* Timeline Styles */
.ratings-timeline {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.timeline-rating-item {
    background: var(--rating-bg);
    border: 1px solid var(--rating-border);
    border-left: 4px solid var(--rating-gold);
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.2s ease;
}

.timeline-rating-item:hover {
    transform: translateX(4px);
    box-shadow: var(--rating-shadow);
}

.timeline-rating-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.timeline-stars {
    display: flex;
    gap: 4px;
}

.timeline-stars i {
    font-size: 1rem;
    color: var(--rating-gold);
}

.timeline-stars i.empty {
    color: var(--rating-border);
}

.timeline-date {
    font-size: 0.875rem;
    color: var(--rating-text-3);
    font-weight: 600;
}

.timeline-context {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.context-badge {
    padding: 0.375rem 0.75rem;
    background: var(--rating-gold-light);
    color: var(--rating-text-2);
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

.timeline-comment {
    font-size: 0.875rem;
    color: var(--rating-text-2);
    line-height: 1.6;
    font-style: italic;
}

/* Empty State */
.ratings-empty {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--rating-card);
    border: 1px solid var(--rating-border);
    border-radius: 20px;
    box-shadow: var(--rating-shadow);
}

.empty-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: var(--rating-gold-light);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: var(--rating-gold);
}

.empty-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--rating-text-1);
    margin-bottom: 0.5rem;
}

.empty-text {
    font-size: 1rem;
    color: var(--rating-text-3);
}

/* Pagination */
.ratings-pagination {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

/* Responsive */
@media (max-width: 768px) {
    .ratings-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-row {
        grid-template-columns: 1fr;
    }
    
    .ratings-stats {
        grid-template-columns: 1fr;
    }
    
    .modal-content {
        margin: 1rem;
    }
    
    .modal-header,
    .modal-body {
        padding: 1.5rem;
    }
}

@media (max-width: 1024px) {
    .ratings-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
}
</style>
@endpush

@section('content')
<div class="ratings-page">
    
    <!-- Header -->
    <div class="ratings-header">
        <h1 class="ratings-title">
            <i class="bi bi-star-fill"></i>
            Customer Ratings
        </h1>
        <p class="ratings-subtitle">
            Feedback and reviews for {{ auth()->guard('branch')->user()->name ?? 'your branch' }}
        </p>
    </div>

    <!-- Stats -->
    <div class="ratings-stats">
        <div class="stat-card">
            <div class="stat-label">Average Rating</div>
            <div class="stat-value">{{ number_format($stats['average_rating'], 1) }}</div>
            <div class="stat-stars">
                @for($i = 1; $i <= 5; $i++)
                    <i class="bi bi-star{{ $i <= round($stats['average_rating']) ? '-fill' : '' }}"></i>
                @endfor
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Total Reviews</div>
            <div class="stat-value">{{ number_format($stats['total_ratings']) }}</div>
            <div class="stat-label">All time</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">5 Star Reviews</div>
            <div class="stat-value">{{ number_format($stats['five_star']) }}</div>
            <div class="stat-label">{{ $stats['total_ratings'] > 0 ? number_format(($stats['five_star']/$stats['total_ratings'])*100, 1) : 0 }}% of total</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Low Ratings (1-2★)</div>
            <div class="stat-value">{{ number_format($stats['one_star'] + $stats['two_star']) }}</div>
            <div class="stat-label">Need attention</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="ratings-filters">
        <form method="GET" class="filter-row">
            <div class="filter-group">
                <label>Search Customer</label>
                <input type="text" 
                       name="search" 
                       class="filter-input" 
                       value="{{ request('search') }}"
                       placeholder="Name, phone, or email...">
            </div>

            <div class="filter-group">
                <label>Star Rating</label>
                <select name="rating" class="filter-select">
                    <option value="">All ratings</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                            {{ $i }} Star{{ $i > 1 ? 's' : '' }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="filter-group">
                <label>From Date</label>
                <input type="date" name="date_from" class="filter-input" value="{{ request('date_from') }}">
            </div>

            <div class="filter-group">
                <label>To Date</label>
                <input type="date" name="date_to" class="filter-input" value="{{ request('date_to') }}">
            </div>

            <div class="filter-group">
                <label style="visibility: hidden;">Actions</label>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="filter-btn filter-btn-primary">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <a href="{{ route('branch.ratings.index') }}" class="filter-btn filter-btn-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Ratings Grid -->
    @if($customersWithRatings->count() > 0)
        <div class="ratings-grid">
            @foreach($customersWithRatings as $customerData)
                @php
                    $initial = strtoupper(substr($customerData->customer->name, 0, 1));
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
                @endphp

                <div class="rating-card">
                    <!-- Customer -->
                    <div class="rating-customer">
                        <div class="customer-avatar" style="background: linear-gradient(135deg, {{ $color1 }} 0%, {{ $color2 }} 100%);">
                            {{ $initial }}
                        </div>
                        <div class="customer-info">
                            <div class="customer-name">{{ $customerData->customer->name }}</div>
                            <div class="customer-email">{{ $customerData->customer->email }}</div>
                        </div>
                    </div>

                    <!-- Stars -->
                    <div class="rating-stars-section">
                        <div>
                            <div class="rating-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= round($customerData->average_rating) ? '-fill' : '' }}{{ $i > round($customerData->average_rating) ? ' empty' : '' }}"></i>
                                @endfor
                            </div>
                            <div class="rating-count-label">{{ $customerData->rating_count }} {{ $customerData->rating_count == 1 ? 'rating' : 'ratings' }}</div>
                        </div>
                        <div class="rating-score">{{ $customerData->average_rating }}/5</div>
                    </div>

                    <!-- Comment -->
                    @if($customerData->latest_rating->comment)
                        <div class="rating-comment">
                            <div class="comment-label">
                                <i class="bi bi-chat-left-quote"></i> Latest Comment
                            </div>
                            <div class="comment-text">"{{ $customerData->latest_rating->comment }}"</div>
                        </div>
                    @else
                        <div class="rating-comment">
                            <div class="comment-text" style="color: var(--rating-text-3); font-style: normal;">
                                No comment provided
                            </div>
                        </div>
                    @endif

                    <!-- Footer -->
                    <div class="rating-footer">
                        <div class="rating-date">
                            <div class="date-main">{{ $customerData->latest_rating->created_at->format('M j, Y') }}</div>
                            <div class="date-time">Latest rating</div>
                        </div>
                        @if($customerData->rating_count > 1)
                            <button class="view-all-ratings-btn" onclick="openRatingsModal({{ $customerData->customer->id }})">
                                <i class="bi bi-list-ul"></i> View All ({{ $customerData->rating_count }})
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Ratings Modal -->
        <div class="ratings-modal" id="ratingsModal">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">
                        <i class="bi bi-star-fill" style="color: var(--rating-gold);"></i>
                        <span id="modalCustomerName"></span>
                        <span id="modalAvgRating" style="font-size: 1.25rem; color: var(--rating-text-3);"></span>
                    </div>
                    <button class="modal-close" onclick="closeRatingsModal()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="ratings-timeline" id="ratingsTimeline"></div>
                </div>
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="ratings-empty">
            <div class="empty-icon">
                <i class="bi bi-star"></i>
            </div>
            <h3 class="empty-title">No Ratings Yet</h3>
            <p class="empty-text">No customer ratings match your current filters.</p>
        </div>
    @endif

</div>

<script>
const customersData = @json($customersWithRatings);

function openRatingsModal(customerId) {
    const customerData = customersData.find(c => c.customer.id === customerId);
    if (!customerData) return;
    
    document.getElementById('modalCustomerName').textContent = customerData.customer.name;
    document.getElementById('modalAvgRating').textContent = `(${customerData.average_rating}/5 avg)`;
    
    const timeline = document.getElementById('ratingsTimeline');
    timeline.innerHTML = '';
    
    customerData.all_ratings.forEach(rating => {
        const ratingItem = document.createElement('div');
        ratingItem.className = 'timeline-rating-item';
        
        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            starsHtml += `<i class="bi bi-star${i <= rating.rating ? '-fill' : ''}${i > rating.rating ? ' empty' : ''}"></i>`;
        }
        
        const date = new Date(rating.created_at);
        const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        const formattedTime = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        
        let contextBadges = '';
        if (rating.laundry_id) {
            contextBadges += `<span class="context-badge"><i class="bi bi-basket"></i> Laundry #${rating.laundry_id}</span>`;
        }
        contextBadges += `<span class="context-badge"><i class="bi bi-geo-alt-fill"></i> ${rating.branch.name}</span>`;
        
        ratingItem.innerHTML = `
            <div class="timeline-rating-header">
                <div class="timeline-stars">${starsHtml}</div>
                <div class="timeline-date">${formattedDate} at ${formattedTime}</div>
            </div>
            <div class="timeline-context">${contextBadges}</div>
            <div class="timeline-comment">${rating.comment ? `"${rating.comment}"` : '<span style="color: var(--rating-text-3); font-style: normal;">No comment provided</span>'}</div>
        `;
        
        timeline.appendChild(ratingItem);
    });
    
    document.getElementById('ratingsModal').classList.add('active');
}

function closeRatingsModal() {
    document.getElementById('ratingsModal').classList.remove('active');
}

// Close modal on outside click
document.getElementById('ratingsModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRatingsModal();
    }
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRatingsModal();
    }
});
</script>
@endsection
