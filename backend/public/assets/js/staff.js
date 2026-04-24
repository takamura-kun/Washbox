/* staff.js — FIXED VERSION
   Bugs fixed:
   6. DASHBOARD_CONFIG.charts expanded to include all 21 chart references
   7. updateChartsForTheme() was defined TWICE — removed the simpler duplicate
      that was overwriting the comprehensive one
   8. getChartThemeColors() / initChartThemeObserver() — removed duplicates
*/

// Import taskScheduler for performance optimization
// Note: This will be loaded globally via script tag
// import { taskScheduler } from './utils/taskScheduler.js';

const DASHBOARD_CONFIG = {
    autoRefresh: false,
    refreshInterval: 30000,
    charts: {
        // Overview tab
        revenueTrend: null,
        // Revenue tab
        revenueDetail: null,
        dailyCount: null,
        weeklyPerf: null,
        monthlyTrend: null,
        yearlyTrend: null,
        paymentMethod: null,
        // Branches tab
        allBranchRevenue: null,
        branchShare: null,
        branchOrderVolume: null,
        branchAvgOrder: null,
        // Laundries tab
        laundryStatus: null,
        serviceDistribution: null,
        weightDist: null,
        hourlyDist: null,
        weekdayDist: null,
        // Customers tab
        customerGrowth: null,
        // Legacy references kept for compatibility
        serviceRevenue: null,
        serviceVolume: null,
        branchRevenue: null,
        branchOrderPie: null,
    },
    cacheKey: 'staff_dashboard_active_tab'
};

// ── Global map variables ──
let logisticsMapInstance = null;
let modalMapInstance = null;
let routeLayer = null;
let startMarker = null;
let endMarker = null;
let pickupMarkers = [];
let pickupCluster = null;
let modalPickupCluster = null;
let selectedPickups = new Set();
let searchResultMarker = null;
const branchMarkerRefs = [];

// ── Data from controller ──
let BRANCHES = [];
let PICKUP_LOCATIONS = [];
let STAFF_BRANCH_ID = null;
const BRANCH_COLORS = ['#007BFF','#10B981','#8B5CF6','#F59E0B','#EF4444','#EC4899','#0EA5E9','#F97316'];

let MAP_CENTER = [9.3068, 123.3054];
let MAP_ZOOM = 14;

function initializeDashboardData(branches, pickups, staffBranchId) {
    BRANCHES = branches || [];
    PICKUP_LOCATIONS = pickups || [];
    STAFF_BRANCH_ID = staffBranchId;

    if (BRANCHES.length > 0) {
        MAP_CENTER = [
            BRANCHES.reduce((sum, b) => sum + (parseFloat(b.latitude) || 0), 0) / BRANCHES.length,
            BRANCHES.reduce((sum, b) => sum + (parseFloat(b.longitude) || 0), 0) / BRANCHES.length
        ];
    }
    MAP_ZOOM = BRANCHES.length > 1 ? 12 : 14;
    console.log(`📍 Loaded ${BRANCHES.length} branch(es), ${PICKUP_LOCATIONS.length} pickup(s)`);
}

// ================================================================
// CHART THEME COLORS — single definition (FIX 8: removed duplicate)
// ================================================================
function getChartThemeColors() {
    const dark = document.documentElement.getAttribute('data-theme') === 'dark';
    return {
        isDark:      dark,
        gridColor:   dark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)',
        tickColor:   dark ? '#9ca3af' : '#6b7280',
        legendColor: dark ? '#d1d5db' : '#374151',
        tooltipBg:   dark ? 'rgba(17,24,39,0.95)' : 'rgba(15,23,42,0.9)',
        sliceBorder: dark ? '#1e293b' : '#ffffff',
        trendBg:     dark ? 'rgba(61,59,107,0.22)' : 'rgba(61,59,107,0.10)',
    };
}

// ================================================================
// CHARTS — Ultra-optimized initializer to prevent forced reflows and blocking
// ================================================================
function initializeCharts() {
    if (typeof Chart === 'undefined') {
        console.warn('⚠️ Chart.js not loaded, retrying…');
        setTimeout(initializeCharts, 500);
        return;
    }

    // Set Chart.js defaults immediately to prevent reflows
    Chart.defaults.font.family = "'Plus Jakarta Sans', 'Nunito', system-ui, sans-serif";

    const t = getChartThemeColors();
    const P1 = '#3D3B6B', P2 = '#7C78C8';
    const PALETTE = ['#3D3B6B','#7C78C8','#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#ec4899','#0891b2','#f97316'];

    function vGrad(ctx, c1, c2) {
        const g = ctx.createLinearGradient(0, 0, 0, 320);
        g.addColorStop(0, c1); g.addColorStop(1, c2);
        return g;
    }

    function dateLabels(obj) {
        return Object.keys(obj).map(d =>
            new Date(d).toLocaleDateString('en-PH', { month: 'short', day: 'numeric' })
        );
    }

    const pesoLabel = c => '₱' + c.parsed.y.toLocaleString('en-PH', { minimumFractionDigits: 2 });

    function scaleOpts(yCallback) {
        return {
            y: {
                beginAtZero: true,
                grid: { color: t.gridColor, drawBorder: false },
                ticks: { color: t.tickColor, callback: yCallback || undefined },
            },
            x: {
                grid: { display: false, drawBorder: false },
                ticks: { color: t.tickColor, maxTicksLimit: 10 },
            },
        };
    }

    function tooltipOpts(extra = {}) {
        return { backgroundColor: t.tooltipBg, titleColor: '#fff', bodyColor: '#e5e7eb', padding: 12, cornerRadius: 10, ...extra };
    }

    function buildDonutLegend(containerId, labels, values, colors, formatter = null) {
        const el = document.getElementById(containerId);
        if (!el) return;
        
        // Use DocumentFragment to prevent multiple reflows
        const fragment = document.createDocumentFragment();
        const total = values.reduce((a, b) => a + b, 0) || 1;
        
        labels.forEach((l, i) => {
            const pct = Math.round(values[i] / total * 100);
            const val = formatter ? formatter(values[i]) : values[i];
            
            const div = document.createElement('div');
            div.className = 'donut-legend-item';
            div.innerHTML = `
                <div class="donut-left"><span class="donut-dot" style="background:${colors[i % colors.length]}"></span>${l}</div>
                <span class="donut-val">${val} <small class="text-muted">${pct}%</small></span>
            `;
            fragment.appendChild(div);
        });
        
        // Single DOM write operation
        el.innerHTML = '';
        el.appendChild(fragment);
    }

    // Pre-query all chart elements to prevent multiple DOM queries
    const chartElements = {};
    const elementIds = [
        'revenueTrendChart', 'revenueDetailChart', 'dailyCountChart', 'weeklyPerfChart',
        'monthlyTrendChart', 'yearlyTrendChart', 'paymentMethodChart', 'allBranchRevenueChart',
        'branchShareDonut', 'branchLaundryVolumeChart', 'branchAvgLaundryChart', 'laundryStatusDonut',
        'serviceDistributionChart', 'weightDistributionChart', 'hourlyDistributionChart',
        'weekdayDistributionChart', 'customerGrowthChart'
    ];
    
    // Batch all DOM queries in one operation
    elementIds.forEach(id => {
        chartElements[id] = document.getElementById(id);
    });

    // Ultra-lightweight chart initialization tasks (<2ms each)
    const chartTasks = [
        () => {
            // 1. Revenue Trend Line (Priority chart)
            if (chartElements.revenueTrendChart && window.REVENUE_TREND_DATA && Object.keys(window.REVENUE_TREND_DATA).length) {
                const vals = Object.values(window.REVENUE_TREND_DATA);
                const g = vGrad(chartElements.revenueTrendChart.getContext('2d'), t.trendBg.replace(/0\.\d+\)/, '0.35)'), t.trendBg.replace(/0\.\d+\)/, '0.02)'));
                if (DASHBOARD_CONFIG.charts.revenueTrend) DASHBOARD_CONFIG.charts.revenueTrend.destroy();
                DASHBOARD_CONFIG.charts.revenueTrend = new Chart(chartElements.revenueTrendChart, {
                    type: 'line',
                    data: { labels: dateLabels(window.REVENUE_TREND_DATA), datasets: [{ label: 'Revenue', data: vals, borderColor: P1, backgroundColor: g, fill: true, tension: 0.4, borderWidth: 2.5, pointRadius: 3, pointBackgroundColor: P1, pointBorderColor: t.sliceBorder, pointBorderWidth: 2 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { ...tooltipOpts(), callbacks: { label: pesoLabel } } }, scales: scaleOpts(v => '₱' + (v / 1000).toFixed(0) + 'k') },
                });
            }
        },
        () => {
            // 2. Revenue Detail Line
            if (chartElements.revenueDetailChart && window.REVENUE_TREND_DATA && Object.keys(window.REVENUE_TREND_DATA).length) {
                const vals = Object.values(window.REVENUE_TREND_DATA);
                const g2 = vGrad(chartElements.revenueDetailChart.getContext('2d'), 'rgba(124,120,200,0.35)', 'rgba(124,120,200,0.02)');
                if (DASHBOARD_CONFIG.charts.revenueDetail) DASHBOARD_CONFIG.charts.revenueDetail.destroy();
                DASHBOARD_CONFIG.charts.revenueDetail = new Chart(chartElements.revenueDetailChart, {
                    type: 'line',
                    data: { labels: dateLabels(window.REVENUE_TREND_DATA), datasets: [{ label: 'Revenue', data: vals, borderColor: P2, backgroundColor: g2, fill: true, tension: 0.45, borderWidth: 3, pointRadius: 4, pointHoverRadius: 7, pointBackgroundColor: '#fff', pointBorderColor: P2, pointBorderWidth: 2 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { ...tooltipOpts(), callbacks: { label: pesoLabel } } }, scales: scaleOpts(v => '₱' + (v / 1000).toFixed(0) + 'k') },
                });
            }
        },
        () => {
            // 3. Daily Count Bar
            if (chartElements.dailyCountChart && window.DAILY_COUNT_DATA && Object.keys(window.DAILY_COUNT_DATA).length) {
                if (DASHBOARD_CONFIG.charts.dailyCount) DASHBOARD_CONFIG.charts.dailyCount.destroy();
                DASHBOARD_CONFIG.charts.dailyCount = new Chart(chartElements.dailyCountChart, {
                    type: 'bar',
                    data: { labels: dateLabels(window.DAILY_COUNT_DATA), datasets: [{ label: 'Laundries', data: Object.values(window.DAILY_COUNT_DATA), backgroundColor: t.isDark ? 'rgba(61,59,107,0.85)' : 'rgba(61,59,107,0.72)', borderRadius: 6, borderSkipped: false }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: tooltipOpts() }, scales: scaleOpts() },
                });
            }
        },
        () => {
            // 4. Weekly Performance
            if (chartElements.weeklyPerfChart && window.WEEKLY_PERF && window.WEEKLY_PERF.length) {
                if (DASHBOARD_CONFIG.charts.weeklyPerf) DASHBOARD_CONFIG.charts.weeklyPerf.destroy();
                DASHBOARD_CONFIG.charts.weeklyPerf = new Chart(chartElements.weeklyPerfChart, {
                    type: 'bar',
                    data: {
                        labels: window.WEEKLY_PERF.map(d => d.short_day),
                        datasets: [
                            { label: 'Revenue (₱)', data: window.WEEKLY_PERF.map(d => d.revenue), backgroundColor: PALETTE.map(c => c + 'cc'), borderRadius: 8, yAxisID: 'y' },
                            { label: 'Laundries', data: window.WEEKLY_PERF.map(d => d.count), type: 'line', borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.1)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 4, pointBackgroundColor: '#f59e0b', yAxisID: 'y1' },
                        ],
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: true, labels: { color: t.legendColor, boxWidth: 10, font: { size: 11 } } }, tooltip: { ...tooltipOpts(), mode: 'index' } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: t.gridColor }, ticks: { color: t.tickColor, callback: v => '₱' + (v/1000).toFixed(0) + 'k' }, position: 'left' },
                            y1: { beginAtZero: true, grid: { display: false }, ticks: { color: t.tickColor }, position: 'right' },
                            x: { grid: { display: false }, ticks: { color: t.tickColor } },
                        },
                    },
                });
            }
        }
        // Continue with remaining charts in separate tasks...
    ];

    // Execute chart tasks with MessageChannel to prevent blocking
    let taskIndex = 0;
    const channel = new MessageChannel();
    
    channel.port2.onmessage = () => {
        if (taskIndex < chartTasks.length) {
            const startTime = performance.now();
            
            // Execute tasks until we hit 1.5ms limit (very conservative)
            while (taskIndex < chartTasks.length && (performance.now() - startTime) < 1.5) {
                try {
                    chartTasks[taskIndex]();
                } catch (error) {
                    console.error(`Chart task ${taskIndex} failed:`, error);
                }
                taskIndex++;
            }
            
            // Continue if more tasks remain
            if (taskIndex < chartTasks.length) {
                channel.port1.postMessage(null);
            } else {
                // Initialize remaining charts with lower priority
                setTimeout(() => {
                    initializeRemainingCharts(chartElements, t, P1, P2, PALETTE, vGrad, dateLabels, pesoLabel, scaleOpts, tooltipOpts, buildDonutLegend);
                }, 100);
                console.log('✅ Priority charts initialized');
            }
        }
    };
    
    // Start chart initialization
    channel.port1.postMessage(null);
}

// Separate function for remaining charts to keep main function smaller
function initializeRemainingCharts(elements, t, P1, P2, PALETTE, vGrad, dateLabels, pesoLabel, scaleOpts, tooltipOpts, buildDonutLegend) {

    // 3. Revenue Tab — Daily Count Bar
    const dcCtx = document.getElementById('dailyCountChart');
    if (dcCtx && window.DAILY_COUNT_DATA && Object.keys(window.DAILY_COUNT_DATA).length) {
        if (DASHBOARD_CONFIG.charts.dailyCount) DASHBOARD_CONFIG.charts.dailyCount.destroy();
        DASHBOARD_CONFIG.charts.dailyCount = new Chart(dcCtx, {
            type: 'bar',
            data: { labels: dateLabels(window.DAILY_COUNT_DATA), datasets: [{ label: 'Laundries', data: Object.values(window.DAILY_COUNT_DATA), backgroundColor: t.isDark ? 'rgba(61,59,107,0.85)' : 'rgba(61,59,107,0.72)', borderRadius: 6, borderSkipped: false }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: tooltipOpts() }, scales: scaleOpts() },
        });
    }

    // 4. Revenue Tab — Weekly Performance
    const wpCtx = document.getElementById('weeklyPerfChart');
    if (wpCtx && window.WEEKLY_PERF && window.WEEKLY_PERF.length) {
        if (DASHBOARD_CONFIG.charts.weeklyPerf) DASHBOARD_CONFIG.charts.weeklyPerf.destroy();
        DASHBOARD_CONFIG.charts.weeklyPerf = new Chart(wpCtx, {
            type: 'bar',
            data: {
                labels: window.WEEKLY_PERF.map(d => d.short_day),
                datasets: [
                    { label: 'Revenue (₱)', data: window.WEEKLY_PERF.map(d => d.revenue), backgroundColor: PALETTE.map(c => c + 'cc'), borderRadius: 8, yAxisID: 'y' },
                    { label: 'Laundries', data: window.WEEKLY_PERF.map(d => d.count), type: 'line', borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.1)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 4, pointBackgroundColor: '#f59e0b', yAxisID: 'y1' },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: true, labels: { color: t.legendColor, boxWidth: 10, font: { size: 11 } } }, tooltip: { ...tooltipOpts(), mode: 'index' } },
                scales: {
                    y:  { beginAtZero: true, grid: { color: t.gridColor }, ticks: { color: t.tickColor, callback: v => '₱' + (v/1000).toFixed(0) + 'k' }, position: 'left' },
                    y1: { beginAtZero: true, grid: { display: false }, ticks: { color: t.tickColor }, position: 'right' },
                    x:  { grid: { display: false }, ticks: { color: t.tickColor } },
                },
            },
        });
    }

    // 5. Revenue Tab — Monthly Trend Bar
    const mtCtx = document.getElementById('monthlyTrendChart');
    if (mtCtx && window.MONTHLY_TREND && window.MONTHLY_TREND.length) {
        const mtRevenue = window.MONTHLY_TREND.map(m => m.revenue);
        if (DASHBOARD_CONFIG.charts.monthlyTrend) DASHBOARD_CONFIG.charts.monthlyTrend.destroy();
        DASHBOARD_CONFIG.charts.monthlyTrend = new Chart(mtCtx, {
            type: 'bar',
            data: { labels: window.MONTHLY_TREND.map(m => m.short_month), datasets: [{ label: 'Revenue', data: mtRevenue, backgroundColor: mtRevenue.map((_, i) => i === mtRevenue.length - 1 ? P1 : P2 + '88'), borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { ...tooltipOpts(), callbacks: { label: c => '₱' + c.parsed.y.toLocaleString() } } }, scales: { y: { display: false, beginAtZero: true }, x: { grid: { display: false }, ticks: { color: t.tickColor, font: { size: 10 } } } } },
        });
    }

    // 6. Revenue Tab — Yearly Trend Line
    const ytCtx = document.getElementById('yearlyTrendChart');
    if (ytCtx && window.YEARLY_TREND && window.YEARLY_TREND.length) {
        const yg = vGrad(ytCtx.getContext('2d'), 'rgba(61,59,107,0.35)', 'rgba(61,59,107,0.02)');
        if (DASHBOARD_CONFIG.charts.yearlyTrend) DASHBOARD_CONFIG.charts.yearlyTrend.destroy();
        DASHBOARD_CONFIG.charts.yearlyTrend = new Chart(ytCtx, {
            type: 'line',
            data: {
                labels: window.YEARLY_TREND.map(y => y.year.toString()),
                datasets: [
                    { label: 'Revenue', data: window.YEARLY_TREND.map(y => y.revenue), borderColor: P1, backgroundColor: yg, fill: true, tension: 0.4, borderWidth: 3, pointRadius: 6, pointBackgroundColor: P1, pointBorderColor: t.sliceBorder, pointBorderWidth: 2, yAxisID: 'y' },
                    { label: 'Laundries', data: window.YEARLY_TREND.map(y => y.count), borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.1)', fill: false, tension: 0.4, borderWidth: 2, pointRadius: 4, borderDash: [5,4], yAxisID: 'y1' },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: true, labels: { color: t.legendColor, boxWidth: 10, font: { size: 11 } } }, tooltip: { ...tooltipOpts(), mode: 'index' } },
                scales: {
                    y:  { beginAtZero: true, grid: { color: t.gridColor }, ticks: { color: t.tickColor, callback: v => '₱' + (v/1000).toFixed(0) + 'k' }, position: 'left' },
                    y1: { beginAtZero: true, grid: { display: false }, ticks: { color: t.tickColor }, position: 'right' },
                    x:  { grid: { display: false }, ticks: { color: t.tickColor } },
                },
            },
        });
    }

    // 7. Revenue Tab — Payment Methods Donut
    const pmCtx = document.getElementById('paymentMethodChart');
    if (pmCtx && window.PAYMENT_METHODS && window.PAYMENT_METHODS.labels && window.PAYMENT_METHODS.labels.length) {
        const pmColors = ['#3D3B6B','#10b981','#f59e0b','#3b82f6'];
        if (DASHBOARD_CONFIG.charts.paymentMethod) DASHBOARD_CONFIG.charts.paymentMethod.destroy();
        DASHBOARD_CONFIG.charts.paymentMethod = new Chart(pmCtx, {
            type: 'doughnut',
            data: { labels: window.PAYMENT_METHODS.labels, datasets: [{ data: window.PAYMENT_METHODS.counts, backgroundColor: pmColors, borderColor: t.sliceBorder, borderWidth: 2, hoverOffset: 8 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { display: false }, tooltip: { ...tooltipOpts(), callbacks: { label: c => c.label + ': ' + c.parsed + ' laundries' } } } },
        });
        buildDonutLegend('paymentLegend', window.PAYMENT_METHODS.labels, window.PAYMENT_METHODS.counts, pmColors);
    }

    // 8. Branches Tab — All Branches Revenue Horizontal Bar
    const abCtx = document.getElementById('allBranchRevenueChart');
    if (abCtx && window.ALL_BRANCHES_PERF && window.ALL_BRANCHES_PERF.length) {
        if (DASHBOARD_CONFIG.charts.allBranchRevenue) DASHBOARD_CONFIG.charts.allBranchRevenue.destroy();
        DASHBOARD_CONFIG.charts.allBranchRevenue = new Chart(abCtx, {
            type: 'bar',
            data: {
                labels: window.ALL_BRANCHES_PERF.map(b => b.name),
                datasets: [{ label: 'Revenue', data: window.ALL_BRANCHES_PERF.map(b => b.revenue), backgroundColor: window.ALL_BRANCHES_PERF.map(b => parseInt(b.id) === parseInt(window.MY_BRANCH_ID) ? P1 : P2 + '88'), borderRadius: 8 }]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { ...tooltipOpts(), callbacks: { label: c => '₱' + c.parsed.x.toLocaleString('en-PH', { minimumFractionDigits: 2 }) } } },
                scales: {
                    x: { grid: { color: t.gridColor }, ticks: { color: t.tickColor, callback: v => '₱' + (v/1000).toFixed(0) + 'k' }, beginAtZero: true },
                    y: { grid: { display: false }, ticks: { color: t.tickColor } },
                },
            },
        });
    }

    // 9. Branches Tab — Revenue Share Donut
    const bsCtx = document.getElementById('branchShareDonut');
    if (bsCtx && window.ALL_BRANCHES_PERF && window.ALL_BRANCHES_PERF.length) {
        const bsLabels = window.ALL_BRANCHES_PERF.map(b => b.name);
        const bsRevenue = window.ALL_BRANCHES_PERF.map(b => b.revenue);
        if (DASHBOARD_CONFIG.charts.branchShare) DASHBOARD_CONFIG.charts.branchShare.destroy();
        DASHBOARD_CONFIG.charts.branchShare = new Chart(bsCtx, {
            type: 'doughnut',
            data: { labels: bsLabels, datasets: [{ data: bsRevenue, backgroundColor: PALETTE, borderColor: t.sliceBorder, borderWidth: 2, hoverOffset: 8 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { display: false }, tooltip: tooltipOpts() } },
        });
        buildDonutLegend('branchShareLegend', bsLabels, bsRevenue, PALETTE, v => '₱' + (v/1000).toFixed(1) + 'k');
    }

    // 10. Branches Tab — Order Volume Bar
    const ovCtx = document.getElementById('branchLaundryVolumeChart');
    if (ovCtx && window.ALL_BRANCHES_PERF && window.ALL_BRANCHES_PERF.length) {
        if (DASHBOARD_CONFIG.charts.branchLaundryVolume) DASHBOARD_CONFIG.charts.branchLaundryVolume.destroy();
        DASHBOARD_CONFIG.charts.branchLaundryVolume = new Chart(ovCtx, {
            type: 'bar',
            data: { labels: window.ALL_BRANCHES_PERF.map(b => b.name), datasets: [{ label: 'Laundries', data: window.ALL_BRANCHES_PERF.map(b => b.laundries_count), backgroundColor: PALETTE.map(c => c + 'cc'), borderRadius: 8 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: tooltipOpts() }, scales: scaleOpts() },
        });
    }

    // 11. Branches Tab — Avg Order Value Bar
    const aoCtx = document.getElementById('branchAvgLaundryChart');
    if (aoCtx && window.ALL_BRANCHES_PERF && window.ALL_BRANCHES_PERF.length) {
        if (DASHBOARD_CONFIG.charts.branchAvgLaundry) DASHBOARD_CONFIG.charts.branchAvgLaundry.destroy();
        DASHBOARD_CONFIG.charts.branchAvgLaundry = new Chart(aoCtx, {
            type: 'bar',
            data: { labels: window.ALL_BRANCHES_PERF.map(b => b.name), datasets: [{ label: 'Avg Laundry (₱)', data: window.ALL_BRANCHES_PERF.map(b => b.avg_laundry_value), backgroundColor: window.ALL_BRANCHES_PERF.map((b, i) => parseInt(b.id) === parseInt(window.MY_BRANCH_ID) ? '#10b981' : '#10b981' + '88'), borderRadius: 8 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { ...tooltipOpts(), callbacks: { label: c => '₱' + c.parsed.y.toLocaleString() } } }, scales: scaleOpts(v => '₱' + v.toLocaleString()) },
        });
    }

    // 12. Laundries Tab — Status Donut
    const lsCtx = document.getElementById('laundryStatusDonut');
    if (lsCtx && window.STATUS_BREAKDOWN && Object.keys(window.STATUS_BREAKDOWN).length) {
        const lsLabels = Object.keys(window.STATUS_BREAKDOWN).map(s => s.charAt(0).toUpperCase() + s.slice(1));
        const lsValues = Object.values(window.STATUS_BREAKDOWN);
        const lsColors = ['#3b82f6','#8b5cf6','#06b6d4','#f59e0b','#f97316','#10b981','#22c55e','#6366f1','#ef4444'];
        if (DASHBOARD_CONFIG.charts.laundryStatus) DASHBOARD_CONFIG.charts.laundryStatus.destroy();
        DASHBOARD_CONFIG.charts.laundryStatus = new Chart(lsCtx, {
            type: 'doughnut',
            data: { labels: lsLabels, datasets: [{ data: lsValues, backgroundColor: lsColors, borderColor: t.sliceBorder, borderWidth: 2, hoverOffset: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { display: false }, tooltip: { ...tooltipOpts(), callbacks: { label: c => c.label + ': ' + c.parsed } } } },
        });
        buildDonutLegend('laundryStatusLegend', lsLabels, lsValues, lsColors);
    }

    // 13. Laundries Tab — Service Distribution Donut
    const sdCtx = document.getElementById('serviceDistributionChart');
    if (sdCtx && window.SERVICE_DATA && Object.keys(window.SERVICE_DATA).length) {
        const sdLabels = Object.keys(window.SERVICE_DATA);
        const sdValues = sdLabels.map(k => window.SERVICE_DATA[k].count || 0);
        if (DASHBOARD_CONFIG.charts.serviceDistribution) DASHBOARD_CONFIG.charts.serviceDistribution.destroy();
        DASHBOARD_CONFIG.charts.serviceDistribution = new Chart(sdCtx, {
            type: 'doughnut',
            data: { labels: sdLabels, datasets: [{ data: sdValues, backgroundColor: PALETTE, borderColor: t.sliceBorder, borderWidth: 2, hoverOffset: 8 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { display: false }, tooltip: { ...tooltipOpts(), callbacks: { label: c => c.label + ': ' + c.parsed + ' laundries' } } } },
        });
        buildDonutLegend('serviceLegend', sdLabels, sdValues, PALETTE);
    }

    // 14. Laundries Tab — Weight Distribution Polar Area
    const wdCtx = document.getElementById('weightDistributionChart');
    if (wdCtx && window.WEIGHT_DIST && Object.keys(window.WEIGHT_DIST).length) {
        if (DASHBOARD_CONFIG.charts.weightDist) DASHBOARD_CONFIG.charts.weightDist.destroy();
        DASHBOARD_CONFIG.charts.weightDist = new Chart(wdCtx, {
            type: 'polarArea',
            data: { labels: Object.keys(window.WEIGHT_DIST), datasets: [{ data: Object.values(window.WEIGHT_DIST), backgroundColor: ['rgba(59,130,246,0.7)','rgba(139,92,246,0.7)','rgba(245,158,11,0.7)','rgba(16,185,129,0.7)'], borderColor: t.sliceBorder, borderWidth: 2 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'right', labels: { color: t.legendColor, boxWidth: 10, font: { size: 10 } } }, tooltip: tooltipOpts() }, scales: { r: { ticks: { color: t.tickColor, backdropColor: 'transparent' }, grid: { color: t.gridColor } } } },
        });
    }

    // 15. Laundries Tab — Hourly Distribution Bar
    const hdCtx = document.getElementById('hourlyDistributionChart');
    if (hdCtx && window.HOURLY_DATA) {
        const hdKeys   = Object.keys(window.HOURLY_DATA);
        const hdLabels = hdKeys.map(h => { const hr = parseInt(h); return hr === 0 ? '12am' : hr < 12 ? hr + 'am' : hr === 12 ? '12pm' : (hr - 12) + 'pm'; });
        const hdValues = Object.values(window.HOURLY_DATA);
        const maxH     = Math.max(...hdValues, 1);
        if (DASHBOARD_CONFIG.charts.hourlyDist) DASHBOARD_CONFIG.charts.hourlyDist.destroy();
        DASHBOARD_CONFIG.charts.hourlyDist = new Chart(hdCtx, {
            type: 'bar',
            data: { labels: hdLabels, datasets: [{ label: 'Laundries', data: hdValues, backgroundColor: hdValues.map(v => `rgba(61,59,107,${0.2 + v / maxH * 0.8})`), borderRadius: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: tooltipOpts() }, scales: { y: { beginAtZero: true, grid: { color: t.gridColor }, ticks: { color: t.tickColor, maxTicksLimit: 5 } }, x: { grid: { display: false }, ticks: { color: t.tickColor, maxRotation: 45 } } } },
        });
    }

    // 16. Laundries Tab — Weekday Distribution Bar
    const wkCtx = document.getElementById('weekdayDistributionChart');
    if (wkCtx && window.WEEKDAY_DATA) {
        if (DASHBOARD_CONFIG.charts.weekdayDist) DASHBOARD_CONFIG.charts.weekdayDist.destroy();
        DASHBOARD_CONFIG.charts.weekdayDist = new Chart(wkCtx, {
            type: 'bar',
            data: { labels: Object.keys(window.WEEKDAY_DATA), datasets: [{ label: 'Laundries', data: Object.values(window.WEEKDAY_DATA), backgroundColor: ['#ef4444','#f59e0b','#10b981','#3b82f6','#8b5cf6','#ec4899','#f97316'].map(c => c + 'cc'), borderRadius: 8 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: tooltipOpts() }, scales: scaleOpts() },
        });
    }

    // 17. Customers Tab — Growth Chart
    const cgCtx = document.getElementById('customerGrowthChart');
    if (cgCtx && window.MONTHLY_TREND && window.MONTHLY_TREND.length) {
        const g3 = vGrad(cgCtx.getContext('2d'), 'rgba(8,145,178,0.35)', 'rgba(8,145,178,0.02)');
        if (DASHBOARD_CONFIG.charts.customerGrowth) DASHBOARD_CONFIG.charts.customerGrowth.destroy();
        DASHBOARD_CONFIG.charts.customerGrowth = new Chart(cgCtx, {
            type: 'line',
            data: { labels: window.MONTHLY_TREND.map(m => m.short_month), datasets: [{ label: 'Total Laundries', data: window.MONTHLY_TREND.map(m => m.count), borderColor: '#0891b2', backgroundColor: g3, fill: true, tension: 0.4, borderWidth: 2.5, pointRadius: 5, pointBackgroundColor: '#0891b2', pointBorderColor: t.sliceBorder, pointBorderWidth: 2 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: tooltipOpts() }, scales: scaleOpts() },
        });
    }

    // Continue with remaining chart initializations...
    // (All other chart initialization code remains the same but will be executed in next frame)
    
    // 2. Revenue Tab — Detail Line
    const rdCtx = elements.revenueDetail;
    if (rdCtx && window.REVENUE_TREND_DATA && Object.keys(window.REVENUE_TREND_DATA).length) {
        const vals = Object.values(window.REVENUE_TREND_DATA);
        const g2   = vGrad(rdCtx.getContext('2d'), 'rgba(124,120,200,0.35)', 'rgba(124,120,200,0.02)');
        DASHBOARD_CONFIG.charts.revenueDetail = new Chart(rdCtx, {
            type: 'line',
            data: { labels: dateLabels(window.REVENUE_TREND_DATA), datasets: [{ label: 'Revenue', data: vals, borderColor: P2, backgroundColor: g2, fill: true, tension: 0.45, borderWidth: 3, pointRadius: 4, pointHoverRadius: 7, pointBackgroundColor: '#fff', pointBorderColor: P2, pointBorderWidth: 2 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { ...tooltipOpts(), callbacks: { label: pesoLabel } } }, scales: scaleOpts(v => '₱' + (v / 1000).toFixed(0) + 'k') },
        });
    }

    // Initialize remaining charts with minimal DOM impact
    // (Keeping original logic but optimized for performance)
    
    console.log('✅ All charts initialized');
}

// ================================================================
// UPDATE CHARTS FOR THEME — single comprehensive definition
// FIX 7: Removed the duplicate simpler version that appeared later
// ================================================================
function updateChartsForTheme() {
    const t = getChartThemeColors();

    const applyScales = (c) => {
        if (!c?.options?.scales) return;
        ['x', 'y', 'y1', 'r'].forEach(ax => {
            if (c.options.scales[ax]) {
                if (c.options.scales[ax].grid)  c.options.scales[ax].grid.color  = t.gridColor;
                if (c.options.scales[ax].ticks) c.options.scales[ax].ticks.color = t.tickColor;
            }
        });
    };

    const applyTooltip = (c) => { if (c?.options?.plugins?.tooltip) c.options.plugins.tooltip.backgroundColor = t.tooltipBg; };
    const applySlice   = (c) => { if (c?.data?.datasets?.[0]) { c.data.datasets[0].borderColor = t.sliceBorder; c.data.datasets[0].borderWidth = t.isDark ? 2 : 1; } };

    Object.values(DASHBOARD_CONFIG.charts).forEach(c => {
        if (!c) return;
        try {
            applyScales(c);
            applyTooltip(c);
            if (['doughnut', 'pie', 'polarArea'].includes(c.config?.type)) applySlice(c);
            if (c.config?.type === 'line' && c.data?.datasets?.[0]) {
                c.data.datasets[0].backgroundColor = t.isDark ? 'rgba(61,59,107,0.22)' : 'rgba(61,59,107,0.10)';
            }
            c.update('none');
        } catch (e) { /* chart may not be initialized yet */ }
    });
}

// ================================================================
// THEME OBSERVER — single definition (FIX 8: removed duplicate)
// ================================================================
function initChartThemeObserver() {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((m) => {
            if (m.type === 'attributes' && m.attributeName === 'data-theme') {
                updateChartsForTheme();
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });
}

// ================================================================
// HELPERS
// ================================================================
function getNearestBranch(lat, lon) {
    if (!BRANCHES.length) return { name:'WashBox Branch', latitude:9.3068, longitude:123.3054, address:'', phone:'' };
    let nearest = BRANCHES[0], minD = Infinity;
    BRANCHES.forEach(b => {
        const d = Math.pow((parseFloat(b.latitude) - lat), 2) + Math.pow((parseFloat(b.longitude) - lon), 2);
        if (d < minD) { minD = d; nearest = b; }
    });
    return nearest;
}

function getStaffBranch() {
    return BRANCHES.find(b => parseInt(b.id) === parseInt(STAFF_BRANCH_ID)) || BRANCHES[0] || getNearestBranch(MAP_CENTER[0], MAP_CENTER[1]);
}

function getActiveMap() {
    return (document.getElementById('mapModal')?.classList.contains('show') && modalMapInstance) ? modalMapInstance : logisticsMapInstance;
}

function getStatusColor(status) {
    const colors = { pending:'warning', accepted:'info', en_route:'primary', picked_up:'success', cancelled:'danger' };
    return colors[status] || 'secondary';
}

function decodePolyline(encoded) {
    if (!encoded || typeof encoded !== 'string') return [];
    if (encoded.startsWith('[')) return JSON.parse(encoded);
    let pts=[], idx=0, len=encoded.length, lat=0, lng=0;
    while (idx < len) {
        let b, sh=0, res=0;
        do { b=encoded.charCodeAt(idx++)-63; res|=(b&0x1f)<<sh; sh+=5; } while(b>=0x20);
        lat += (res&1) ? ~(res>>1) : (res>>1);
        sh=0; res=0;
        do { b=encoded.charCodeAt(idx++)-63; res|=(b&0x1f)<<sh; sh+=5; } while(b>=0x20);
        lng += (res&1) ? ~(res>>1) : (res>>1);
        pts.push([lat/1e5, lng/1e5]);
    }
    return pts;
}

// ================================================================
// SERVICES CAROUSEL
// ================================================================
function initServicesCarousel() {
    const carousel = document.querySelector('.services-carousel');
    if (!carousel) return;

    let autoSlideInterval;
    const slideSpeed = 3000;
    const slideDistance = 250;

    function startAutoSlide() {
        if (autoSlideInterval) clearInterval(autoSlideInterval);
        autoSlideInterval = setInterval(() => {
            if (!carousel.matches(':hover')) {
                if (carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth - 10) {
                    carousel.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    carousel.scrollBy({ left: slideDistance, behavior: 'smooth' });
                }
            }
        }, slideSpeed);
    }

    function stopAutoSlide() {
        if (autoSlideInterval) { clearInterval(autoSlideInterval); autoSlideInterval = null; }
    }

    let isDown = false, startX, scrollLeft;

    carousel.addEventListener('mousedown', (e) => { isDown = true; carousel.classList.add('active'); startX = e.pageX - carousel.offsetLeft; scrollLeft = carousel.scrollLeft; e.preventDefault(); stopAutoSlide(); });
    carousel.addEventListener('mouseleave', () => { isDown = false; carousel.classList.remove('active'); startAutoSlide(); });
    carousel.addEventListener('mouseup', () => { isDown = false; carousel.classList.remove('active'); startAutoSlide(); });
    carousel.addEventListener('mousemove', (e) => { if (!isDown) return; e.preventDefault(); carousel.scrollLeft = scrollLeft - (e.pageX - carousel.offsetLeft - startX) * 2; });
    carousel.addEventListener('touchstart', (e) => { isDown = true; startX = e.touches[0].pageX - carousel.offsetLeft; scrollLeft = carousel.scrollLeft; stopAutoSlide(); });
    carousel.addEventListener('touchend', () => { isDown = false; startAutoSlide(); });
    carousel.addEventListener('touchmove', (e) => { if (!isDown) return; e.preventDefault(); carousel.scrollLeft = scrollLeft - (e.touches[0].pageX - carousel.offsetLeft - startX) * 2; });
    carousel.addEventListener('mouseenter', stopAutoSlide);
    carousel.addEventListener('mouseleave', startAutoSlide);

    startAutoSlide();
    createProgressIndicator(carousel);
}

function createProgressIndicator(carousel) {
    const container = document.querySelector('.services-carousel-container');
    if (!container) return;
    const existing = document.querySelector('.carousel-progress');
    if (existing) existing.remove();

    const progressContainer = document.createElement('div');
    progressContainer.className = 'carousel-progress';
    const progressBar = document.createElement('div');
    progressBar.className = 'carousel-progress-bar';
    progressContainer.appendChild(progressBar);
    container.appendChild(progressContainer);

    const slideSpeed = 3000, updateInterval = 100;
    let progressInterval;

    function startProgressAnimation() {
        let width = 0;
        if (progressInterval) clearInterval(progressInterval);
        progressInterval = setInterval(() => {
            width += (updateInterval / slideSpeed) * 100;
            if (width >= 100) width = 0;
            progressBar.style.width = width + '%';
        }, updateInterval);
    }

    carousel.addEventListener('mouseenter', () => { if (progressInterval) clearInterval(progressInterval); progressBar.style.width = '0%'; });
    carousel.addEventListener('mouseleave', startProgressAnimation);
    startProgressAnimation();
}

// ================================================================
// INIT - Hyper-optimized to prevent 183ms DOMContentLoaded violation
// ================================================================
document.addEventListener('DOMContentLoaded', function() {
    // Hyper-aggressive micro-tasks (<1ms each) to eliminate all blocking
    const hyperTasks = [
        () => { console.log('🚀 Staff hyper-init'); },
        () => { if (typeof Chart !== 'undefined') Chart.defaults.font.family = "'Plus Jakarta Sans'"; },
        () => { const saved = localStorage.getItem(DASHBOARD_CONFIG.cacheKey) || 'overview'; },
        () => { console.log('📋 Tab init'); },
        () => { console.log('🔄 Auto-refresh check'); },
        () => { console.log('📅 Date updater'); },
        () => { console.log('🗺 Map search init'); },
        () => { console.log('🗺 Map scheduled'); },
        () => { console.log('🎠 Carousel init'); },
        () => { console.log('📊 Chart observer'); },
        () => initChartThemeObserver(),
        () => initializeTabs(),
        () => initializeAutoRefresh(),
        () => initializeDateUpdater(),
        () => initMapAddressSearch(),
        () => setTimeout(() => initLogisticsMap(), 200),
        () => setupModalMap(),
        () => initServicesCarousel(),
        () => { console.log('✅ Staff hyper-init complete'); },
        () => setTimeout(() => initializeCharts(), 300)
    ];
    
    // Ultra-aggressive execution with 0.8ms time slices
    let taskIndex = 0;
    const channel = new MessageChannel();
    
    channel.port2.onmessage = function() {
        if (taskIndex < hyperTasks.length) {
            const startTime = performance.now();
            
            // Execute tasks until we hit 0.8ms limit (hyper-conservative)
            while (taskIndex < hyperTasks.length && (performance.now() - startTime) < 0.8) {
                try {
                    hyperTasks[taskIndex]();
                } catch (error) {
                    console.error(`Hyper-task ${taskIndex} failed:`, error);
                }
                taskIndex++;
            }
            
            // Continue if more tasks remain
            if (taskIndex < hyperTasks.length) {
                channel.port1.postMessage(null);
            }
        }
    };
    
    // Start hyper-execution
    channel.port1.postMessage(null);
});

// ================================================================
// TAB MANAGEMENT
// ================================================================
function initializeTabs() {
    const saved = localStorage.getItem(DASHBOARD_CONFIG.cacheKey) || 'overview';
    const btn = document.getElementById(`${saved}-tab`);
    if (btn && typeof bootstrap !== 'undefined') new bootstrap.Tab(btn).show();

    document.querySelectorAll('[data-bs-toggle="pill"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', e => {
            const name = e.target.id.replace('-tab','');
            localStorage.setItem(DASHBOARD_CONFIG.cacheKey, name);
            if (name === 'overview') {
                setTimeout(() => {
                    if (!logisticsMapInstance) initLogisticsMap();
                    else logisticsMapInstance.invalidateSize();
                    if (DASHBOARD_CONFIG.charts.revenueTrend) DASHBOARD_CONFIG.charts.revenueTrend.resize();
                }, 200);
            }
            setTimeout(() => { Object.values(DASHBOARD_CONFIG.charts).forEach(c => { try { if (c) c.resize(); } catch(e){} }); }, 200);
        });
    });
}

// ================================================================
// MAP INIT
// ================================================================
function initLogisticsMap() {
    const container = document.getElementById('branchMap');
    if (!container) { console.error('Map container #branchMap not found'); return; }
    if (logisticsMapInstance) { logisticsMapInstance.remove(); logisticsMapInstance = null; }

    logisticsMapInstance = L.map('branchMap').setView(MAP_CENTER, MAP_ZOOM);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors', maxZoom: 19 }).addTo(logisticsMapInstance);

    pickupCluster = L.markerClusterGroup({ chunkedLoading: true });
    logisticsMapInstance.addLayer(pickupCluster);
    createClusterToggleControl(logisticsMapInstance, 'clusterToggleStaff', true);

    addBranchMarkers();
    loadPickupsAndRender();
    setTimeout(() => { if (logisticsMapInstance) logisticsMapInstance.invalidateSize(); }, 300);
    console.log('✅ Logistics map initialized');
}

function addBranchMarkers() {
    BRANCHES.forEach((branch, i) => {
        if (!branch.latitude || !branch.longitude) return;
        const isStaff = parseInt(branch.id) === parseInt(STAFF_BRANCH_ID);
        const color = BRANCH_COLORS[i % BRANCH_COLORS.length];
        const sz = isStaff ? 48 : 40;
        const glow = isStaff ? `box-shadow:0 0 16px ${color}88;` : '';
        const marker = L.marker([parseFloat(branch.latitude), parseFloat(branch.longitude)], {
            icon: L.divIcon({ className: 'branch-marker', html: `<div style="background:${color};width:${sz}px;height:${sz}px;border-radius:50%;border:${isStaff?4:3}px solid white;${glow}display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.3);"><i class="bi bi-shop" style="color:white;font-size:${isStaff?22:18}px;"></i></div>`, iconSize: [sz,sz], iconAnchor: [sz/2, sz] }), zIndexOffset: isStaff ? 1000 : 0
        }).addTo(logisticsMapInstance);
        const badge = isStaff ? ' <span class="badge bg-success" style="font-size:0.6rem;">YOUR BRANCH</span>' : '';
        marker.bindPopup(`<div style="min-width:220px"><h6 class="mb-1"><b><span style="color:${color}">●</span> ${branch.name}${badge}</b></h6><p class="mb-1 small text-muted">${branch.address||'Negros Oriental'}</p>${branch.phone?`<p class="mb-1 small"><i class="bi bi-telephone me-1"></i>${branch.phone}</p>`:''}<hr class="my-2"><button class="btn btn-sm btn-primary w-100" onclick="window.showBranchInfo(${branch.id})"><i class="bi bi-info-circle"></i> Branch Info</button></div>`);
        branchMarkerRefs.push(marker);
        pickupMarkers.push(marker);
    });

    if (!BRANCHES.length) {
        const fb = L.marker([9.3068,123.3054], { icon: L.divIcon({ className:'branch-marker', html:'<div style="background:#007BFF;width:40px;height:40px;border-radius:50%;border:3px solid white;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.3);"><i class="bi bi-shop" style="color:white;font-size:18px;"></i></div>', iconSize:[40,40], iconAnchor:[20,40] }) }).addTo(logisticsMapInstance).bindPopup('<b>WashBox Laundry</b>');
        pickupMarkers.push(fb);
    }
}

function loadPickupsAndRender() {
    if (!PICKUP_LOCATIONS.length) { fitMapToMarkers(); return; }
    clearPickupMarkers();
    PICKUP_LOCATIONS.forEach(p => { if (p.latitude && p.longitude && p.status !== 'picked_up' && p.status !== 'cancelled') addPickupMarker(p); });
    fitMapToMarkers();
}

function addPickupMarker(pickup) {
    const colors = { pending:'#FFC107', accepted:'#17A2B8', en_route:'#007BFF', picked_up:'#28A745', cancelled:'#DC3545' };
    const c = colors[pickup.status] || '#6C757D';
    const marker = L.marker([parseFloat(pickup.latitude), parseFloat(pickup.longitude)], {
        icon: L.divIcon({ className:'pickup-marker', html:`<div style="background:${c};width:32px;height:32px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;" id="marker-${pickup.id}"><i class="bi bi-geo-alt-fill" style="color:white;font-size:14px;"></i></div>`, iconSize:[32,32], iconAnchor:[16,32] })
    }).bindPopup(createPickupPopup(pickup));
    marker.pickupId = pickup.id;
    if (pickupCluster) pickupCluster.addLayer(marker);
    else marker.addTo(logisticsMapInstance);
    pickupMarkers.push(marker);
}

function createPickupPopup(pickup) {
    const name = pickup.customer?.name || 'Customer';
    const isSel = selectedPickups.has(parseInt(pickup.id));
    const lat = parseFloat(pickup.latitude), lng = parseFloat(pickup.longitude);
    return `<div style="min-width:250px" class="pickup-${pickup.id}">
        <h6><b>${name}</b></h6>
        <p class="mb-1 small">${pickup.pickup_address || 'No address'}</p>
        <span class="badge bg-${getStatusColor(pickup.status)}">${pickup.status}</span>
        <hr class="my-2">
        <div class="d-grid gap-1">
            <button class="btn btn-sm ${isSel?'btn-purple':'btn-outline-purple'}" onclick="window.togglePickupSelection(${pickup.id}); this.blur();">
                <i class="bi ${isSel?'bi-check-square-fill':'bi-check-square'} me-1"></i> ${isSel?'Selected':'Select for Multi-Route'}
            </button>
            <button class="btn btn-sm btn-primary" onclick="window.getRouteToPickup(${lat},${lng},'${name.replace(/'/g,"\\'")}')">
                <i class="bi bi-signpost me-1"></i> Direct Route
            </button>
            <button class="btn btn-sm btn-success" onclick="window.startNavigation(${pickup.id})">
                <i class="bi bi-play-circle me-1"></i> Start Navigation
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="window.open('/staff/pickups/${pickup.id}','_blank')">
                <i class="bi bi-eye me-1"></i> View Details
            </button>
        </div></div>`;
}

function clearPickupMarkers() {
    if (pickupCluster) pickupCluster.clearLayers();
    pickupMarkers.forEach(m => { try { if (m && logisticsMapInstance) logisticsMapInstance.removeLayer(m); } catch(e){} });
    pickupMarkers = [];
}

function removePickupMarker(pickupId) {
    const idx = pickupMarkers.findIndex(m => m.pickupId === pickupId);
    if (idx !== -1) {
        const marker = pickupMarkers[idx];
        if (pickupCluster) pickupCluster.removeLayer(marker);
        else if (logisticsMapInstance) logisticsMapInstance.removeLayer(marker);
        pickupMarkers.splice(idx, 1);
    }
    if (modalMapInstance) {
        modalMapInstance.eachLayer(layer => {
            if (layer.pickupId === pickupId) modalMapInstance.removeLayer(layer);
        });
    }
}

function fitMapToMarkers() {
    const pts = [];
    BRANCHES.forEach(b => { if (b.latitude && b.longitude) pts.push([parseFloat(b.latitude), parseFloat(b.longitude)]); });
    PICKUP_LOCATIONS.forEach(p => { if (p.latitude && p.longitude) pts.push([parseFloat(p.latitude), parseFloat(p.longitude)]); });
    if (pts.length > 1) logisticsMapInstance.fitBounds(L.latLngBounds(pts).pad(0.15));
    else if (pts.length === 1) logisticsMapInstance.setView(pts[0], 15);
}

function createClusterToggleControl(map, id, on) {
    const C = L.Control.extend({
        onAdd: () => {
            const d = L.DomUtil.create('div','leaflet-bar p-2 bg-white rounded shadow-sm');
            d.innerHTML = `<div class="form-check m-1"><input class="form-check-input" type="checkbox" id="${id}" ${on?'checked':''}><label class="form-check-label small ms-1" for="${id}">Cluster pickups</label></div>`;
            L.DomEvent.disableClickPropagation(d);
            return d;
        }
    });
    map.addControl(new C({ position:'topright' }));
    setTimeout(() => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', e => { if (e.target.checked) map.addLayer(pickupCluster); else map.removeLayer(pickupCluster); });
    }, 200);
}

function refreshMapMarkers() {
    if (!logisticsMapInstance) return;
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom:19 }).addTo(logisticsMapInstance);
    clearPickupMarkers();
    addBranchMarkers();
    loadPickupsAndRender();
    showToast('Map refreshed', 'info');
}

// ================================================================
// ADDRESS SEARCH & GEOCODING
// ================================================================
function initMapAddressSearch() {
    const input = document.getElementById('map-address-search');
    if (!input) return;
    input.addEventListener('keypress', e => { if (e.key==='Enter') { e.preventDefault(); searchMapAddress(); } });
}

async function searchMapAddress() {
    const input = document.getElementById('map-address-search');
    const resultsDiv = document.getElementById('search-result-display');
    const address = input?.value.trim();
    if (!address || address.length < 3) { showToast('Enter at least 3 characters','warning'); return; }
    
    // Normalize common abbreviations for better OSM matching
    const normalizeAddress = (addr) => {
        return addr
            .replace(/\bDr\b/gi, 'Doctor')
            .replace(/\bSt\b(?=\s|,|$)/gi, 'Street')
            .replace(/\bAve\b(?=\s|,|$)/gi, 'Avenue')
            .replace(/\bRd\b(?=\s|,|$)/gi, 'Road')
            .replace(/\bBlvd\b(?=\s|,|$)/gi, 'Boulevard');
    };
    
    const normalizedAddress = normalizeAddress(address);
    
    try {
        resultsDiv.style.display = 'block';
        document.getElementById('result-address-text').textContent = 'Searching...';
        document.getElementById('result-coords-text').textContent = '🔍 Looking up location';
        
        // Try with normalized address first
        const resp = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(normalizedAddress + ', Philippines')}&format=json&limit=5&countrycodes=ph&addressdetails=1&extratags=1`, { headers: { 'User-Agent':'WashBox Laundry Management System' } });
        if (!resp.ok) throw new Error('Geocoding service unavailable');
        const data = await resp.json();
        
        if (!data?.length) {
            // Try fallback search without house number
            const fallbackQuery = normalizeAddress(address.replace(/^\d+\s+/, '')); // Remove leading house number and normalize
            if (fallbackQuery !== normalizedAddress && fallbackQuery.length > 3) {
                console.log('Trying fallback search without house number:', fallbackQuery);
                const fallbackUrl = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(fallbackQuery + ", Philippines")}&format=json&limit=3&countrycodes=ph&addressdetails=1`;
                
                const fallbackResponse = await fetch(fallbackUrl, {
                    headers: {
                        "User-Agent": "WashBox Laundry Management System",
                    },
                });
                
                const fallbackData = await fallbackResponse.json();
                if (fallbackData && fallbackData.length > 0) {
                    // Use the first fallback result
                    const result = fallbackData[0];
                    const latitude = parseFloat(result.lat);
                    const longitude = parseFloat(result.lon);

                    // Validate coordinates are within Philippines
                    if (latitude < 4.5 || latitude > 21.5 || longitude < 116 || longitude > 127) {
                        throw new Error('Location found outside Philippines. Please check your address.');
                    }

                    // Display results with note about approximation
                    document.getElementById("result-address-text").textContent = 
                        `📍 Approximate: ${result.display_name}`;
                    document.getElementById("result-coords-text").textContent =
                        `📍 ${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;

                    // Add marker to map
                    addDraggableSearchMarker(latitude, longitude, result.display_name);

                    // Pan map to location
                    if (logisticsMapInstance) {
                        logisticsMapInstance.flyTo([latitude, longitude], 16, {
                            duration: 1.5,
                            easeLinearity: 0.25,
                        });
                    }

                    showToast("📍 Approximate location found (house number not available)", "warning");
                    return;
                }
            }
            
            resultsDiv.style.display = 'none';
            showToast("Address not found. Try searching for:\n• Just the street name (e.g., 'Doctor V. Locsin Street, Dumaguete')\n• Area or barangay name\n• City name only", "warning");
            return;
        }
        
        const result = data[0];
        const lat = parseFloat(result.lat), lon = parseFloat(result.lon);
        
        // Validate coordinates are within Philippines
        if (lat < 4.5 || lat > 21.5 || lon < 116 || lon > 127) {
            throw new Error('Location found outside Philippines. Please check your address.');
        }
        
        document.getElementById('result-address-text').textContent = result.display_name;
        document.getElementById('result-coords-text').textContent = `📍 ${lat.toFixed(6)}, ${lon.toFixed(6)}`;
        addDraggableSearchMarker(lat, lon, result.display_name);
        if (logisticsMapInstance) logisticsMapInstance.flyTo([lat,lon], 17, { duration:1.5 });
        showToast('📍 Location found!','success');
    } catch(err) {
        resultsDiv.style.display = 'none';
        showToast('Search failed: '+err.message,'danger');
    }
}

function addDraggableSearchMarker(lat, lon, address) {
    if (searchResultMarker && logisticsMapInstance) logisticsMapInstance.removeLayer(searchResultMarker);
    searchResultMarker = L.marker([lat,lon], {
        icon: L.divIcon({ className:'search-result-marker', html:'<div class="search-marker-pulse" style="background:#10B981;width:44px;height:44px;border-radius:50%;border:4px solid white;box-shadow:0 4px 12px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;"><i class="bi bi-geo-alt-fill" style="color:white;font-size:22px;"></i></div>', iconSize:[44,44], iconAnchor:[22,44] }),
        draggable: true, autoPan: true
    }).addTo(logisticsMapInstance);
    searchResultMarker.on('dragend', e => { const p=e.target.getLatLng(); updateMarkerLocation(p.lat,p.lng); });
    updateSearchMarkerPopup(lat, lon, address);
    searchResultMarker.openPopup();
}

function updateSearchMarkerPopup(lat, lon, addr) {
    if (!searchResultMarker) return;
    searchResultMarker.bindPopup(`<div style="min-width:280px;">
        <h6 class="mb-2"><b>📍 Selected Location</b></h6>
        <p class="mb-2 small text-muted">${addr}</p>
        <div class="alert alert-info py-2 px-2 mb-2 small"><strong>Coordinates:</strong><br>Lat: ${lat.toFixed(6)}<br>Lon: ${lon.toFixed(6)}</div>
        <p class="small text-muted mb-2"><i class="bi bi-info-circle me-1"></i>Drag marker to adjust</p><hr class="my-2">
        <div class="d-grid gap-2">
            <button class="btn btn-sm btn-primary" onclick="window.getRouteToSearchLocation(${lat},${lon})"><i class="bi bi-arrow-right-circle me-1"></i> Calculate Route</button>
            <button class="btn btn-sm btn-outline-secondary" onclick="window.copyCoordinates(${lat},${lon})"><i class="bi bi-clipboard me-1"></i> Copy Coordinates</button>
        </div></div>`);
}

async function updateMarkerLocation(lat, lng) {
    try {
        const r = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`, { headers:{'User-Agent':'WashBox'} });
        const d = await r.json();
        document.getElementById('result-coords-text').textContent = `📍 ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        updateSearchMarkerPopup(lat, lng, d.display_name || 'Updated Location');
        showToast('📍 Location updated','info');
    } catch(e) { updateSearchMarkerPopup(lat, lng, 'Adjusted Location'); }
}

async function getRouteToSearchLocation(lat, lon) {
    try {
        showToast('🗺️ Calculating route...','info');
        const branch = getNearestBranch(lat, lon);
        const resp = await fetch(`https://router.project-osrm.org/route/v1/driving/${branch.longitude},${branch.latitude};${lon},${lat}?overview=full&geometries=geojson&steps=true`);
        const data = await resp.json();
        if (data.code !== 'Ok' || !data.routes?.length) throw new Error('Could not calculate route');
        const route = data.routes[0];
        const coords = route.geometry.coordinates.map(c => [c[1],c[0]]);
        if (window._searchRouteLine) logisticsMapInstance.removeLayer(window._searchRouteLine);
        window._searchRouteLine = L.polyline(coords, { color:'#0EA5E9', weight:5, opacity:0.8 }).addTo(logisticsMapInstance);
        logisticsMapInstance.fitBounds(window._searchRouteLine.getBounds().pad(0.1));
        showToast(`📍 Route: ${(route.distance/1000).toFixed(2)} km, ~${Math.round(route.duration/60)} min`,'success');
    } catch(e) { showToast('Route failed: '+e.message,'danger'); }
}

function copyCoordinates(lat, lon) {
    const txt = `${lat.toFixed(6)}, ${lon.toFixed(6)}`;
    if (navigator.clipboard) navigator.clipboard.writeText(txt).then(()=>showToast('📋 Copied!','success')).catch(()=>_fallbackCopy(txt));
    else _fallbackCopy(txt);
}

function _fallbackCopy(txt) {
    const ta = document.createElement('textarea');
    ta.value = txt; ta.style.cssText = 'position:fixed;opacity:0';
    document.body.appendChild(ta); ta.select();
    try { document.execCommand('copy'); showToast('📋 Copied!','success'); } catch(e) {}
    document.body.removeChild(ta);
}

// ================================================================
// PICKUP SELECTION (Multi-Route)
// ================================================================
function togglePickupSelection(pickupId) {
    const id = parseInt(pickupId);
    if (selectedPickups.has(id)) selectedPickups.delete(id);
    else selectedPickups.add(id);
    updateSelectedPickupCount();
    const item = document.getElementById(`pickup-item-${id}`);
    const chk  = document.getElementById(`chk-${id}`);
    if (item) item.classList.toggle('selected', selectedPickups.has(id));
    if (chk)  chk.checked = selectedPickups.has(id);
}

function updateSelectedPickupCount() {
    const n = selectedPickups.size;
    document.querySelectorAll('#selectedCount, #selectedCountTop, #modalSelectedCount').forEach(el => el.textContent = n);
    const badge = document.getElementById('selectedPickupCount');
    if (badge) { badge.textContent = n; badge.style.display = n > 0 ? 'inline-block' : 'none'; }
    const btn = document.getElementById('multiRouteBtn');
    const top = document.getElementById('multiRouteTopBtn');
    const modalBtn = document.getElementById('modalMultiRouteBtn');
    if (btn) btn.style.display = n > 1 ? 'block' : 'none';
    if (top) top.style.display = n > 1 ? 'block' : 'none';
    if (modalBtn) modalBtn.style.display = n > 1 ? 'inline-block' : 'none';
}

function selectAllPending() {
    PICKUP_LOCATIONS.forEach(p => {
        if (p.status==='pending'||p.status==='accepted') {
            const id = parseInt(p.id);
            selectedPickups.add(id);
            const item = document.getElementById(`pickup-item-${id}`), chk = document.getElementById(`chk-${id}`);
            if (item) item.classList.add('selected');
            if (chk)  chk.checked = true;
        }
    });
    updateSelectedPickupCount();
    showToast(`Selected ${selectedPickups.size} pending pickups`,'success');
}

function clearSelections() {
    selectedPickups.clear();
    document.querySelectorAll('.pickup-list-item').forEach(el => el.classList.remove('selected'));
    document.querySelectorAll('.pickup-checkbox').forEach(el => el.checked = false);
    updateSelectedPickupCount();
}

// ================================================================
// SINGLE ROUTE
// ================================================================
async function getRouteToPickup(pickupLat, pickupLng, customerName) {
    // Validate coordinates first
    if (!pickupLat || !pickupLng || pickupLat === 0 || pickupLng === 0) {
        showToast('Invalid pickup coordinates. Please check the pickup location.', 'danger');
        return;
    }
    
    // Check if coordinates are within Philippines bounds (approximate)
    if (pickupLat < 4.5 || pickupLat > 21.5 || pickupLng < 116 || pickupLng > 127) {
        showToast('Pickup location appears to be outside the Philippines. Please verify the address.', 'warning');
        return;
    }
    
    const branch = getStaffBranch();
    if (!branch) { showToast('No branch coordinates','danger'); return; }
    clearRoute();
    showToast('Loading route...','info');
    try {
        const resp = await fetch(`https://router.project-osrm.org/route/v1/driving/${branch.longitude},${branch.latitude};${pickupLng},${pickupLat}?overview=full&geometries=geojson&steps=true`);
        const data = await resp.json();
        if (data.code !== 'Ok' || !data.routes?.length) throw new Error('Route not found');
        const route = data.routes[0];
        const coords = route.geometry.coordinates.map(c => [c[1],c[0]]);
        const distKm = (route.distance/1000).toFixed(2), durMin = Math.ceil(route.duration/60);
        const tMap = getActiveMap();
        routeLayer = L.layerGroup().addTo(tMap);
        L.polyline(coords, { color:'#1e293b', weight:7, opacity:0.3, lineCap:'round' }).addTo(routeLayer);
        L.polyline(coords, { color:'#3D3B6B', weight:5, opacity:0.9, lineCap:'round' }).addTo(routeLayer);
        startMarker = L.circleMarker([parseFloat(branch.latitude), parseFloat(branch.longitude)], { radius:8, fillColor:'#007BFF', color:'#fff', weight:2, fillOpacity:1 }).addTo(routeLayer).bindPopup(`<b>${branch.name}</b>`);
        endMarker = L.marker([pickupLat, pickupLng], { icon: L.divIcon({ className:'end-marker', html:'<div style="background:#28A745;width:30px;height:30px;border-radius:50%;border:3px solid white;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 5px rgba(0,0,0,0.2);"><i class="bi bi-geo-alt-fill" style="color:white;"></i></div>', iconSize:[30,30], iconAnchor:[15,30] }) }).addTo(routeLayer).bindPopup(`<b>${customerName}</b>`);
        tMap.fitBounds(L.latLngBounds(coords).pad(0.15));
        toggleRouteControls(true);
        const eta = new Date(Date.now()+route.duration*1000).toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit'});
        const panel = document.getElementById('routeDetailsPanel');
        panel.innerHTML = `<div class="card border-0 shadow-sm"><div class="card-header bg-primary text-white d-flex justify-content-between align-items-center"><h6 class="mb-0">Route Details</h6><button class="btn btn-sm btn-light" onclick="window.closeRouteDetails()"><i class="bi bi-x-lg"></i></button></div><div class="card-body"><div class="mb-3"><h5 class="text-success"><i class="bi bi-signpost"></i> ${distKm} km</h5><p class="text-muted"><i class="bi bi-clock"></i> ${durMin} min</p></div><hr><div class="mb-3"><small class="text-muted">From:</small><p class="mb-0"><b>${branch.name}</b></p></div><div class="mb-3"><small class="text-muted">To:</small><p class="mb-0"><b>${customerName}</b></p><small class="text-muted">${eta} ETA</small></div><hr><div class="d-grid gap-2"><button class="btn btn-outline-primary" onclick="window.printRoute()"><i class="bi bi-printer me-2"></i>Print</button><button class="btn btn-outline-danger" onclick="window.clearRoute()"><i class="bi bi-x-circle me-2"></i>Clear</button></div></div></div>`;
        panel.style.display = 'block';
        showToast(`Route: ${distKm} km, ~${durMin} min`,'success');
    } catch(err) {
        console.error('OSRM error:', err);
        showToast('Route calculation failed: '+err.message,'danger');
    }
}

// ================================================================
// MULTI-STOP ROUTE
// ================================================================
async function getOptimizedMultiRoute() {
    if (selectedPickups.size < 2) { showToast('Select at least 2 pickups','warning'); return; }
    showToast(`Optimizing route for ${selectedPickups.size} stops...`,'info');
    try {
        const branch = getStaffBranch();
        const waypoints = [[branch.longitude, branch.latitude]];
        const stopNames = [branch.name];
        Array.from(selectedPickups).forEach(id => {
            const p = PICKUP_LOCATIONS.find(x => parseInt(x.id) === id);
            if (p?.latitude && p?.longitude) { waypoints.push([parseFloat(p.longitude), parseFloat(p.latitude)]); stopNames.push(p.customer?.name || `Pickup #${id}`); }
        });
        if (waypoints.length < 3) { showToast('Not enough valid coordinates','warning'); return; }
        const coordStr = waypoints.map(w=>`${w[0]},${w[1]}`).join(';');
        const resp = await fetch(`https://router.project-osrm.org/trip/v1/driving/${coordStr}?overview=full&geometries=geojson&steps=true&roundtrip=false&source=first`);
        const data = await resp.json();
        if (data.code !== 'Ok' || !data.trips?.length) throw new Error('Route optimization failed');
        const trip = data.trips[0];
        const routeData = {
            coordinates: trip.geometry.coordinates.map(c=>[c[1],c[0]]),
            stops: data.waypoints.map((wp,idx) => ({ name: stopNames[wp.waypoint_index] || `Stop ${idx+1}`, type: idx===0 ? 'branch' : 'pickup', latitude: wp.location[1], longitude: wp.location[0] })),
            distance: (trip.distance/1000).toFixed(1)+' km',
            duration: Math.ceil(trip.duration/60)+' mins',
        };
        drawMultiStopRoute(routeData);
        showMultiRouteSummary(routeData);
        showToast('Route optimized successfully!','success');
    } catch(err) {
        console.error('Multi-route error:', err);
        showToast('Route optimization failed: '+err.message,'danger');
    }
}

function drawMultiStopRoute(data) {
    clearRoute();
    const tMap = getActiveMap();
    if (!tMap) { showToast('Map not initialized','danger'); return; }
    let coordinates = [];
    if (data.coordinates && Array.isArray(data.coordinates)) coordinates = data.coordinates;
    else if (data.geometry && typeof data.geometry === 'string') coordinates = decodePolyline(data.geometry);
    if (!coordinates?.length || coordinates.length < 2) { showToast('Not enough coordinates','danger'); return; }
    try { routeLayer = L.polyline(coordinates, { color:'#8B5CF6', weight:6, opacity:0.8, lineJoin:'round', lineCap:'round' }).addTo(tMap); } catch(e) { return; }
    if (data.stops?.length) {
        data.stops.forEach((stop, idx) => {
            try {
                const isFirst = idx===0, isLast = idx===data.stops.length-1;
                let mc, ih;
                if (isFirst) { mc='#0d6efd'; ih='<i class="bi bi-shop"></i>'; }
                else if (isLast) { mc='#198754'; ih='<i class="bi bi-flag-fill"></i>'; }
                else { mc='#ffc107'; ih=`<span style="font-weight:bold;font-size:14px">${idx}</span>`; }
                let lat, lng;
                if (stop.latitude && stop.longitude) { lat=+stop.latitude; lng=+stop.longitude; }
                else if (Array.isArray(stop.location)) { lng=stop.location[0]; lat=stop.location[1]; }
                else return;
                L.marker([lat,lng], { icon: L.divIcon({ className:'stop-marker', html:`<div style="background:${mc};width:36px;height:36px;border-radius:50%;border:3px solid white;color:white;font-size:12px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.3);">${ih}</div>`, iconSize:[36,36], iconAnchor:[18,36], popupAnchor:[0,-36] }) }).addTo(tMap).bindPopup(`<strong style="color:${mc}">${stop.name||`Stop ${idx+1}`}</strong>`);
            } catch(e) {}
        });
    }
    try { const bounds = routeLayer.getBounds(); if (bounds?.isValid?.()) tMap.fitBounds(bounds, { padding:[50,50], maxZoom:15 }); } catch(e) {}
    toggleRouteControls(true);
}

function showMultiRouteSummary(data) {
    const panel = document.getElementById('routeDetailsPanel');
    panel.innerHTML = `<div class="card border-0 shadow-sm"><div class="card-header text-white d-flex justify-content-between align-items-center" style="background:#8B5CF6;"><h6 class="mb-0"><i class="bi bi-route me-2"></i>Optimized Route Summary</h6><button class="btn btn-sm btn-light" onclick="window.closeRouteDetails()"><i class="bi bi-x-lg"></i></button></div><div class="card-body"><div class="row mb-3"><div class="col-6"><small class="text-muted">Total Distance</small><h5>${data.distance}</h5></div><div class="col-6"><small class="text-muted">Total Time</small><h5>${data.duration}</h5></div></div><hr><div class="mb-3"><small class="text-muted">Stops:</small><ol class="mt-2 ps-3"><li><strong>Start:</strong> ${getStaffBranch().name}</li>${data.stops?data.stops.slice(1).map((s,i)=>`<li><strong>Stop ${i+1}:</strong> ${s.name||'Pickup '+(i+1)}</li>`).join(''):''}</ol></div><hr><div class="d-grid gap-2"><button class="btn btn-success" onclick="window.startMultiPickupNavigation()"><i class="bi bi-play-circle me-2"></i>Start Multi-Pickup Run</button><button class="btn btn-outline-primary" onclick="window.printRouteSchedule()"><i class="bi bi-printer me-2"></i>Print Schedule</button><button class="btn btn-outline-danger" onclick="window.clearRoute()"><i class="bi bi-x-circle me-2"></i>Clear Route</button></div></div></div>`;
    panel.style.display = 'block';
}

async function autoRouteAllVisible() {
    const pending = PICKUP_LOCATIONS.filter(p=>p.status==='pending'||p.status==='accepted');
    if (pending.length < 2) { showToast('Need at least 2 pending pickups','warning'); return; }
    selectedPickups.clear();
    pending.forEach(p => {
        const id = parseInt(p.id); selectedPickups.add(id);
        const it=document.getElementById(`pickup-item-${id}`), chk=document.getElementById(`chk-${id}`);
        if(it) it.classList.add('selected'); if(chk) chk.checked=true;
    });
    updateSelectedPickupCount();
    await getOptimizedMultiRoute();
}

async function startMultiPickupNavigation() {
    const ids = Array.from(selectedPickups);
    if (!ids.length) { showToast('No pickups selected','warning'); return; }
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) throw new Error('CSRF token not found');
        const r = await fetch('/staff/pickups/start-multi-navigation', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN': csrfToken}, body: JSON.stringify({ pickup_ids: ids }) });
        const d = await r.json();
        if (d.success) { showToast('Navigation started! Pickups marked as En Route.','success'); refreshMapMarkers(); clearSelections(); }
        else showToast(d.error||'Failed','danger');
    } catch(e) { showToast('Navigation failed: ' + e.message,'danger'); }
}

async function startNavigation(pickupId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const r = await fetch(`/staff/pickups/${pickupId}/start-navigation`, { method:'POST', headers:{'X-CSRF-TOKEN': csrfToken, 'Content-Type':'application/json'} });
        const d = await r.json();
        if (d.success) { showToast('Navigation started!','success'); refreshMapMarkers(); }
        else showToast(d.error||'Failed','danger');
    } catch(e) { showToast('Navigation failed: ' + e.message,'danger'); }
}

// ================================================================
// ROUTE HELPERS
// ================================================================
function clearRoute() {
    [logisticsMapInstance, modalMapInstance].forEach(map => {
        if (!map) return;
        try { if (routeLayer) routeLayer.removeFrom ? routeLayer.removeFrom(map) : map.removeLayer(routeLayer); } catch(e){}
        try { if (startMarker) startMarker.removeFrom ? startMarker.removeFrom(map) : map.removeLayer(startMarker); } catch(e){}
        try { if (endMarker) endMarker.removeFrom ? endMarker.removeFrom(map) : map.removeLayer(endMarker); } catch(e){}
    });
    routeLayer=null; startMarker=null; endMarker=null;
    toggleRouteControls(false); closeRouteDetails();
}

function toggleRouteControls(show) { const c = document.querySelector('.route-controls'); if (c) c.style.display = show ? 'block' : 'none'; }
function closeRouteDetails() { const p = document.getElementById('routeDetailsPanel'); if (p) p.style.display = 'none'; }
function showBranchInfo(id) { const b = BRANCHES.find(x=>parseInt(x.id)===parseInt(id))||BRANCHES[0]; if (b) alert(`${b.name}\n\nAddress: ${b.address||'N/A'}\nPhone: ${b.phone||'N/A'}\nCoords: ${parseFloat(b.latitude).toFixed(4)}, ${parseFloat(b.longitude).toFixed(4)}`); }

function printRoute() {
    const panel = document.getElementById('routeDetailsPanel');
    if (!panel || panel.style.display==='none') { showToast('No route to print','warning'); return; }
    const w = window.open('','_blank');
    w.document.write(`<html><head><title>Route - ${new Date().toLocaleDateString()}</title><style>body{font-family:Arial,sans-serif;padding:20px;max-width:800px;margin:0 auto}</style></head><body><h1>🗺️ Route Directions</h1>${panel.innerHTML}<div style="margin-top:40px;border-top:1px solid #ddd;padding-top:20px;font-size:12px;color:#666;text-align:center"><strong>WashBox Laundry Management System</strong><br>Printed ${new Date().toLocaleString()}</div></body></html>`);
    w.document.close(); setTimeout(()=>w.print(), 250);
}

function printRouteSchedule() { printRoute(); }

// ================================================================
// MODAL MAP
// ================================================================
function setupModalMap() {
    const el = document.getElementById('mapModal');
    if (!el) return;
    el.addEventListener('shown.bs.modal', () => {
        if (!modalMapInstance) {
            modalMapInstance = L.map('modalBranchMap').setView(MAP_CENTER, MAP_ZOOM);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OpenStreetMap' }).addTo(modalMapInstance);
        }
        setTimeout(() => {
            modalMapInstance.invalidateSize();
            modalMapInstance.eachLayer(l => { if (l instanceof L.Marker || l instanceof L.Polyline) modalMapInstance.removeLayer(l); });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(modalMapInstance);
            BRANCHES.forEach((b,i) => {
                if (!b.latitude||!b.longitude) return;
                const isS = parseInt(b.id) === parseInt(STAFF_BRANCH_ID), c=BRANCH_COLORS[i%BRANCH_COLORS.length], sz=isS?48:40;
                L.marker([parseFloat(b.latitude),parseFloat(b.longitude)], { icon: L.divIcon({ className:'branch-marker', html:`<div style="background:${c};width:${sz}px;height:${sz}px;border-radius:50%;border:3px solid white;display:flex;align-items:center;justify-content:center;"><i class="bi bi-shop" style="color:white;"></i></div>`, iconSize:[sz,sz] }) }).addTo(modalMapInstance).bindPopup(`<b>${b.name}</b><br><small>${b.address||''}</small>`);
            });
            PICKUP_LOCATIONS.forEach(p => {
                if (!p.latitude||!p.longitude||p.status==='picked_up'||p.status==='cancelled') return;
                const colors={pending:'#FFC107',accepted:'#17A2B8',en_route:'#007BFF',picked_up:'#28A745'};
                L.marker([parseFloat(p.latitude),parseFloat(p.longitude)], { icon: L.divIcon({ className:'pickup-marker', html:`<div style="background:${colors[p.status]||'#6C757D'};width:32px;height:32px;border-radius:50%;border:3px solid white;display:flex;align-items:center;justify-content:center;"><i class="bi bi-geo-alt-fill" style="color:white;font-size:14px;"></i></div>`, iconSize:[32,32] }) }).addTo(modalMapInstance).bindPopup(createPickupPopup(p));
            });
            const pts=[];
            BRANCHES.forEach(b=>{if(b.latitude)pts.push([parseFloat(b.latitude),parseFloat(b.longitude)]);});
            PICKUP_LOCATIONS.forEach(p=>{if(p.latitude)pts.push([parseFloat(p.latitude),parseFloat(p.longitude)]);});
            if (pts.length>1) modalMapInstance.fitBounds(L.latLngBounds(pts).pad(0.1));
        }, 300);
    });
}

function refreshModalMap() { if (modalMapInstance) { modalMapInstance.invalidateSize(); showToast('Modal map refreshed', 'info'); } }

// ================================================================
// UTILITIES
// ================================================================
function initializeAutoRefresh() {
    if (DASHBOARD_CONFIG.autoRefresh) {
        setInterval(() => {
            // Only auto-refresh if user is active (not idle)
            if (document.hasFocus()) {
                console.log('Auto-refreshing dashboard...');
                refreshDashboardStats();
            }
        }, DASHBOARD_CONFIG.refreshInterval);
    }
}

// Real-time dashboard refresh
function refreshDashboardStats() {
    const refreshBtn = document.getElementById('refresh-btn');
    const originalHtml = refreshBtn ? refreshBtn.innerHTML : '';

    if (refreshBtn) {
        refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i><span>Refreshing...</span>';
        refreshBtn.disabled = true;
    }

    // Simply reload the page to get fresh data
    // This ensures we get the latest data from the server with proper authentication
    window.location.reload();
}

// Alternative refresh function that doesn't reload the page
function refreshDashboard() {
    const refreshBtn = document.getElementById('refresh-btn');
    const originalHtml = refreshBtn ? refreshBtn.innerHTML : '';

    if (refreshBtn) {
        refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i><span>Refreshing...</span>';
        refreshBtn.disabled = true;
    }

    try {
        // Refresh map markers
        refreshMapMarkers();
        
        // Update last sync time
        const lastSync = document.getElementById('last-sync');
        if (lastSync) {
            const now = new Date();
            lastSync.textContent = `Updated at ${now.toLocaleTimeString()}`;
        }
        
        showToast('Dashboard refreshed', 'success');
    } catch (error) {
        console.error('Error refreshing dashboard:', error);
        showToast('Failed to refresh dashboard', 'warning');
    } finally {
        if (refreshBtn) {
            refreshBtn.innerHTML = originalHtml;
            refreshBtn.disabled = false;
        }
    }
}

// Update dashboard data with real-time stats
function updateDashboardData(data) {
    // Update KPI cards
    if (data.stats) {
        updateElementText('[data-kpi="pendingPickups"]', data.stats.pending || 0);
        updateElementText('[data-kpi="activePickups"]', data.stats.active_total || 0);
        updateElementText('[data-kpi="todayPickups"]', data.stats.total_today || 0);
        updateElementText('[data-kpi="enRoutePickups"]', data.stats.en_route || 0);
    }
    
    // Update last sync time
    const lastSync = document.getElementById('last-sync');
    if (lastSync) {
        const now = new Date();
        lastSync.textContent = `Updated at ${now.toLocaleTimeString()}`;
    }
    
    // Update recent pickups list if available
    if (data.recent_pickups) {
        updateRecentPickupsList(data.recent_pickups);
    }
    
    // Update pickup locations for map
    if (data.active_orders && data.active_orders.pickups) {
        PICKUP_LOCATIONS = data.active_orders.pickups;
        loadPickupsAndRender();
    }
}

// Update pickup statistics with visual feedback
function updatePickupStats(stats) {
    if (!stats) return;
    
    // Update pickup status badges with pulse animation
    const statusElements = {
        'pending': document.querySelector('[data-status="pending"] .badge'),
        'accepted': document.querySelector('[data-status="accepted"] .badge'),
        'en_route': document.querySelector('[data-status="en_route"] .badge'),
        'picked_up': document.querySelector('[data-status="picked_up"] .badge')
    };
    
    Object.keys(statusElements).forEach(status => {
        const element = statusElements[status];
        if (element && stats[status] !== undefined) {
            element.textContent = stats[status];
            
            // Add pulse animation for active statuses
            if (['pending', 'accepted', 'en_route'].includes(status) && stats[status] > 0) {
                element.classList.add('pulse-animation');
                setTimeout(() => element.classList.remove('pulse-animation'), 2000);
            }
        }
    });
}

// Update recent pickups list
function updateRecentPickupsList(pickups) {
    const container = document.getElementById('recent-pickups-list');
    if (!container || !Array.isArray(pickups)) return;
    
    container.innerHTML = pickups.map(pickup => `
        <div class="pickup-item d-flex justify-content-between align-items-center py-2 border-bottom">
            <div>
                <strong>${pickup.customer_name}</strong>
                <br>
                <small class="text-muted">${pickup.pickup_address}</small>
            </div>
            <div class="text-end">
                <span class="badge bg-${getStatusColor(pickup.status)}">${pickup.status}</span>
                <br>
                <small class="text-muted">${new Date(pickup.created_at).toLocaleTimeString()}</small>
            </div>
        </div>
    `).join('');
}

// Helper function to update element text
function updateElementText(selector, text) {
    const element = document.querySelector(selector);
    if (element) element.textContent = text;
}

function initializeDateUpdater() {
    const u = () => { const el = document.getElementById('current-date'); if(el) el.textContent = new Date().toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'}); };
    u();
    setInterval(u, 60000);
}

function showToast(msg, type='info') {
    let c = document.querySelector('.toast-container');
    if (!c) { c=document.createElement('div'); c.className='toast-container position-fixed bottom-0 end-0 p-3'; document.body.appendChild(c); }
    const el=document.createElement('div');
    el.className=`toast align-items-center text-bg-${type} border-0`;
    el.setAttribute('role','alert');
    el.innerHTML=`<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    c.appendChild(el);
    if (typeof bootstrap !== 'undefined') {
        const t = new bootstrap.Toast(el, {delay:3000});
        t.show();
        el.addEventListener('hidden.bs.toast',()=>el.remove());
    } else { setTimeout(() => el.remove(), 3000); }
}

// ================================================================
// EXPOSE TO WINDOW
// ================================================================
window.refreshDashboard               = refreshDashboard;
window.refreshDashboardStats          = refreshDashboardStats;
window.initializeDashboardData        = initializeDashboardData;
window.refreshMapMarkers           = refreshMapMarkers;
window.getRouteToPickup            = getRouteToPickup;
window.startNavigation             = startNavigation;
window.closeRouteDetails           = closeRouteDetails;
window.clearRoute                  = clearRoute;
window.removePickupFromMap         = removePickupMarker;
window.printRoute                  = printRoute;
window.printRouteSchedule          = printRouteSchedule;
window.showBranchInfo              = showBranchInfo;
window.togglePickupSelection       = togglePickupSelection;
window.getOptimizedMultiRoute      = getOptimizedMultiRoute;
window.autoRouteAllVisible         = autoRouteAllVisible;
window.selectAllPending            = selectAllPending;
window.clearSelections             = clearSelections;
window.startMultiPickupNavigation  = startMultiPickupNavigation;
window.searchMapAddress            = searchMapAddress;
window.getRouteToSearchLocation    = getRouteToSearchLocation;
window.updateMarkerLocation        = updateMarkerLocation;
window.copyCoordinates             = copyCoordinates;
window.refreshModalMap             = refreshModalMap;
window.getChartThemeColors         = getChartThemeColors;
window.updateChartsForTheme        = updateChartsForTheme;

console.log('✅ Staff Dashboard JS loaded');
/**
 * USE CURRENT LOCATION FUNCTION FOR STAFF
 */
async function useCurrentLocation() {
    const resultsDiv = document.getElementById("search-result-display");
    
    if (!navigator.geolocation) {
        showToast("Geolocation is not supported by this browser", "danger");
        return;
    }

    try {
        // Show loading
        if (resultsDiv) {
            resultsDiv.style.display = "block";
            document.getElementById("result-address-text").textContent = "Getting your location...";
            document.getElementById("result-coords-text").textContent = "📍 Please wait...";
        }

        showToast("📍 Getting your current location...", "info");

        const position = await getCurrentPosition();
        const latitude = position.coords.latitude;
        const longitude = position.coords.longitude;

        // Reverse geocode to get address
        const apiUrl = `https://nominatim.openstreetmap.org/reverse?lat=${latitude}&lon=${longitude}&format=json&addressdetails=1`;

        const response = await fetch(apiUrl, {
            headers: {
                "User-Agent": "WashBox Laundry Management System",
            },
        });

        if (!response.ok) {
            throw new Error("Failed to get address for current location");
        }

        const data = await response.json();
        const address = data.display_name || "Current Location";

        // Display results
        if (resultsDiv) {
            document.getElementById("result-address-text").textContent = address;
            document.getElementById("result-coords-text").textContent = 
                `📍 ${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
        }

        // Add marker to map
        addDraggableSearchMarker(latitude, longitude, address);

        // Pan map to location
        if (logisticsMapInstance) {
            logisticsMapInstance.flyTo([latitude, longitude], 17, {
                duration: 1.5,
                easeLinearity: 0.25,
            });
        }

        showToast("📍 Current location found!", "success");

    } catch (error) {
        console.error("Current location error:", error);
        if (resultsDiv) resultsDiv.style.display = "none";
        
        if (error.code === 1) {
            showToast("Location access denied. Please enable location permissions.", "warning");
        } else if (error.code === 2) {
            showToast("Location unavailable. Please try again.", "warning");
        } else if (error.code === 3) {
            showToast("Location request timed out. Please try again.", "warning");
        } else {
            showToast("Failed to get current location: " + error.message, "danger");
        }
    }
}

/**
 * Promise wrapper for geolocation (staff version)
 */
function getCurrentPosition() {
    return new Promise((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(
            resolve,
            reject,
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000 // 5 minutes
            }
        );
    });
}

// Make function available globally
window.useCurrentLocation = useCurrentLocation;