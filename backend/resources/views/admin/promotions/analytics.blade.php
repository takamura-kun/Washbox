@extends('admin.layouts.app')
@section('page-title', 'Promotions Management')

@section('content')
<div class="container-fluid px-4 py-4">
    <h2 class="fw-bold mb-4">Marketing Analytics by Branch</h2>

    <div class="row">
        <div class="col-xl-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Revenue vs. Discounts</h5>
                </div>
                <div class="card-body">
                    <canvas id="branchChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Performance Stats</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($branchStats as $stat)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ $stat->branch_name }}</div>
                                <small class="text-muted">{{ $stat->usage_count }} promos used</small>
                            </div>
                            <div class="text-end">
                                <div class="text-success fw-bold">₱{{ number_format($stat->total_revenue, 2) }}</div>
                                <div class="text-danger small">-₱{{ number_format($stat->total_discounts, 2) }}</div>
                                @if(isset($stat->roi_percentage) && $stat->roi_percentage !== null)
                                    <div class="text-primary small">ROI: {{ number_format($stat->roi_percentage, 1) }}%</div>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Dark Mode Fixes for Promotions Analytics */
[data-theme="dark"] h2,
[data-theme="dark"] h5 {
    color: var(--text-primary) !important;
}

[data-theme="dark"] .fw-bold {
    color: var(--text-primary) !important;
}

[data-theme="dark"] .text-muted {
    color: var(--text-secondary) !important;
}

[data-theme="dark"] .card-header {
    background: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}

[data-theme="dark"] .list-group-item {
    background: var(--card-bg) !important;
    border-color: var(--border-color) !important;
    color: var(--text-primary) !important;
}
</style>
@endpush

<script src="{{ asset('assets/chart.js/chart.umd.min.js') }}"></script>
<script>
    const ctx = document.getElementById('branchChart').getContext('2d');
    const branchStats = @json($branchStats);
    
    // Check if dark mode is active
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    
    const chartOptions = {
        responsive: true,
        plugins: {
            legend: {
                labels: {
                    color: isDark ? '#e2e8f0' : '#374151'
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: isDark ? '#94a3b8' : '#6b7280'
                },
                grid: {
                    color: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)'
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => '₱' + value,
                    color: isDark ? '#94a3b8' : '#6b7280'
                },
                grid: {
                    color: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)'
                }
            }
        }
    };

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: branchStats.map(s => s.branch_name),
            datasets: [{
                label: 'Revenue (Final Amount)',
                data: branchStats.map(s => s.total_revenue),
                backgroundColor: '#198754', // Success Green
                borderRadius: 5
            }, {
                label: 'Discounts Given',
                data: branchStats.map(s => s.total_discounts),
                backgroundColor: '#dc3545', // Danger Red
                borderRadius: 5
            }]
        },
        options: chartOptions
    });
</script>
@endsection
