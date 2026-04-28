// optimizedAnalyticsInit.js - Non-blocking analytics initialization

// Load the chart optimizer
const script = document.createElement('script');
script.src = '/assets/js/utils/analyticsChartOptimizer.js';
script.onload = function() {
    initializeAnalyticsOptimized();
};
document.head.appendChild(script);

function initializeAnalyticsOptimized() {
    // Use requestIdleCallback or setTimeout to defer initialization
    const initCharts = () => {
        const optimizer = window.analyticsChartOptimizer;
        if (!optimizer) {
            console.error('Analytics chart optimizer not loaded');
            return;
        }

        // Get data from PHP (these should already be available)
        const revLabels = window.revLabels || [];
        const revData = window.revData || [];
        const statusLbls = window.statusLbls || [];
        const statusData = window.statusData || [];
        const custGrowthL = window.custGrowthL || [];
        const custGrowthD = window.custGrowthD || [];
        const custRegSrc = window.custRegSrc || { walk_in: 0, self_registered: 0 };
        
        const COLORS = ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444','#ec4899','#6366f1','#14b8a6','#f97316'];

        // Initialize sparklines first (lightweight)
        optimizer.createSparkline('sparkRevenue', revData);
        optimizer.createSparkline('sparkCustomers', custGrowthD);

        // Initialize main charts
        optimizer.createRevenueChart(revLabels, revData);
        
        optimizer.createDoughnutChart(
            'laundryStatusChart',
            statusLbls,
            statusData,
            COLORS,
            true // register for updates
        );

        optimizer.createDoughnutChart(
            'customerTypeChart',
            ['Walk-In', 'Self-Registered'],
            [custRegSrc.walk_in || 0, custRegSrc.self_registered || 0],
            ['#34d399','#818cf8'],
            true // register for updates
        );

        // Initialize other charts based on available data
        if (window.branchLbls && window.branchRev) {
            initializeBranchCharts(optimizer);
        }

        if (window.svcLbls && window.svcOrds) {
            initializeServiceCharts(optimizer);
        }

        if (window.topCustN && window.topCustV) {
            initializeCustomerCharts(optimizer);
        }

        if (window.promoLbls && window.promoUsage) {
            initializePromotionCharts(optimizer);
        }

        console.log('✅ Analytics charts initialized with optimization');
    };

    // Use the most appropriate scheduling method
    if (window.requestIdleCallback) {
        requestIdleCallback(initCharts, { timeout: 2000 });
    } else {
        setTimeout(initCharts, 100);
    }
}

function initializeBranchCharts(optimizer) {
    const branchLbls = window.branchLbls || [];
    const branchRev = window.branchRev || [];
    const branchOrds = window.branchOrds || [];
    const branchNames = window.branchNames || branchLbls;
    const COLORS = ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444'];

    // Waterfall chart
    if (document.getElementById('revenueWaterfallChart')) {
        const sortedBranches = branchRev.map((v, i) => ({
            name: branchNames[i] || branchLbls[i],
            rev: v
        })).sort((a, b) => b.rev - a.rev);

        const offsets = [];
        let running = 0;
        sortedBranches.forEach(b => {
            offsets.push(running);
            running += b.rev;
        });

        optimizer.queueChart({
            id: 'revenueWaterfallChart',
            chartConfig: {
                type: 'bar',
                data: {
                    labels: sortedBranches.map(b => b.name),
                    datasets: [
                        {
                            label: 'Base',
                            data: offsets,
                            backgroundColor: 'transparent',
                            borderWidth: 0,
                            stack: 'w'
                        },
                        {
                            label: 'Revenue',
                            data: sortedBranches.map(b => b.rev),
                            backgroundColor: COLORS.slice(0, sortedBranches.length),
                            borderRadius: 6,
                            borderSkipped: false,
                            stack: 'w'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            ...optimizer.getTooltipDefaults(),
                            filter: item => item.datasetIndex === 1,
                            callbacks: {
                                label: ctx => ' ₱' + ctx.raw.toLocaleString()
                            }
                        }
                    },
                    scales: {
                        y: {
                            ...optimizer.getAxisDefaults(),
                            beginAtZero: true,
                            stacked: true,
                            ticks: {
                                ...optimizer.getAxisDefaults().ticks,
                                callback: v => '₱' + (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v)
                            }
                        },
                        x: {
                            ...optimizer.getAxisDefaults(),
                            grid: { display: false },
                            stacked: true
                        }
                    }
                }
            }
        });
    }

    // Other branch charts...
    if (document.getElementById('branchGroupedChart')) {
        optimizer.queueChart({
            id: 'branchGroupedChart',
            chartConfig: {
                type: 'bar',
                data: {
                    labels: branchNames,
                    datasets: [
                        {
                            label: 'Revenue (₱)',
                            data: branchRev,
                            backgroundColor: 'rgba(59,130,246,0.85)',
                            borderRadius: 6,
                            yAxisID: 'yRev'
                        },
                        {
                            label: 'Laundries',
                            data: branchOrds,
                            backgroundColor: 'rgba(139,92,246,0.85)',
                            borderRadius: 6,
                            yAxisID: 'yOrd'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: optimizer.getTheme().legend,
                                usePointStyle: true,
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            ...optimizer.getTooltipDefaults(),
                            callbacks: {
                                label: ctx => ctx.datasetIndex === 0 
                                    ? ` Revenue: ₱${ctx.raw.toLocaleString()}`
                                    : ` Laundries: ${ctx.raw}`
                            }
                        }
                    },
                    scales: {
                        yRev: {
                            ...optimizer.getAxisDefaults(),
                            beginAtZero: true,
                            position: 'left',
                            ticks: {
                                ...optimizer.getAxisDefaults().ticks,
                                callback: v => '₱' + (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v)
                            }
                        },
                        yOrd: {
                            ...optimizer.getAxisDefaults(),
                            beginAtZero: true,
                            position: 'right',
                            grid: { display: false }
                        },
                        x: {
                            ...optimizer.getAxisDefaults(),
                            grid: { display: false }
                        }
                    }
                }
            }
        });
    }
}

function initializeServiceCharts(optimizer) {
    const svcLbls = window.svcLbls || [];
    const svcOrds = window.svcOrds || [];
    const svcRev = window.svcRev || [];
    const COLORS = ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444'];

    // Service mix pie chart
    if (document.getElementById('serviceMixChart')) {
        optimizer.queueChart({
            id: 'serviceMixChart',
            chartConfig: {
                type: 'pie',
                data: {
                    labels: svcLbls,
                    datasets: [{
                        data: svcRev,
                        backgroundColor: COLORS,
                        borderColor: optimizer.getTheme().border,
                        borderWidth: 2,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { animateScale: true },
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: optimizer.getTheme().legend,
                                padding: 14,
                                usePointStyle: true,
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            ...optimizer.getTooltipDefaults(),
                            callbacks: {
                                label: ctx => ` ₱${ctx.raw.toLocaleString()}`
                            }
                        }
                    }
                }
            }
        });
    }

    // Service funnel chart
    if (document.getElementById('serviceFunnelChart')) {
        const sortIdx = svcOrds.map((v, i) => ({ v, i })).sort((a, b) => b.v - a.v);
        
        optimizer.queueChart({
            id: 'serviceFunnelChart',
            chartConfig: {
                type: 'bar',
                data: {
                    labels: sortIdx.map(s => svcLbls[s.i] || ''),
                    datasets: [{
                        label: 'Laundries',
                        data: sortIdx.map(s => s.v),
                        backgroundColor: sortIdx.map((_, i) => COLORS[i % COLORS.length]),
                        borderRadius: 8
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            ...optimizer.getTooltipDefaults(),
                            callbacks: {
                                label: ctx => ` ${ctx.raw} laundries`
                            }
                        }
                    },
                    scales: {
                        x: {
                            ...optimizer.getAxisDefaults(),
                            beginAtZero: true
                        },
                        y: {
                            ...optimizer.getAxisDefaults(),
                            grid: { display: false }
                        }
                    }
                }
            }
        });
    }
}

function initializeCustomerCharts(optimizer) {
    const custGrowthL = window.custGrowthL || [];
    const custGrowthD = window.custGrowthD || [];
    const topCustN = window.topCustN || [];
    const topCustV = window.topCustV || [];
    const COLORS = ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444'];

    // Customer growth chart
    if (document.getElementById('customerGrowthChart')) {
        optimizer.queueChart({
            id: 'customerGrowthChart',
            registerForUpdates: true,
            chartConfig: {
                type: 'line',
                data: {
                    labels: custGrowthL,
                    datasets: [{
                        label: 'New Customers',
                        data: custGrowthD,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.12)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.45,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            ...optimizer.getTooltipDefaults(),
                            callbacks: {
                                label: ctx => ` ${ctx.raw} new customers`
                            }
                        }
                    },
                    scales: {
                        y: {
                            ...optimizer.getAxisDefaults(),
                            beginAtZero: true
                        },
                        x: {
                            ...optimizer.getAxisDefaults(),
                            grid: { display: false }
                        }
                    }
                }
            }
        });
    }

    // Top customers chart
    if (document.getElementById('topCustomersChart')) {
        optimizer.queueChart({
            id: 'topCustomersChart',
            chartConfig: {
                type: 'bar',
                data: {
                    labels: topCustN,
                    datasets: [{
                        label: 'Total Spend',
                        data: topCustV,
                        backgroundColor: topCustN.map((_, i) => COLORS[i % COLORS.length]),
                        borderRadius: 8
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            ...optimizer.getTooltipDefaults(),
                            callbacks: {
                                label: ctx => ` ₱${ctx.raw.toLocaleString()}`
                            }
                        }
                    },
                    scales: {
                        x: {
                            ...optimizer.getAxisDefaults(),
                            beginAtZero: true,
                            ticks: {
                                ...optimizer.getAxisDefaults().ticks,
                                callback: v => '₱' + (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v)
                            }
                        },
                        y: {
                            ...optimizer.getAxisDefaults(),
                            grid: { display: false }
                        }
                    }
                }
            }
        });
    }
}

function initializePromotionCharts(optimizer) {
    const promoLbls = window.promoLbls || [];
    const promoUsage = window.promoUsage || [];
    const promoRevArr = window.promoRevArr || [];
    const promoDisArr = window.promoDisArr || [];
    const COLORS = ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444'];

    // Promotion usage chart
    if (document.getElementById('promoUsageChart')) {
        optimizer.queueChart({
            id: 'promoUsageChart',
            chartConfig: {
                type: 'bar',
                data: {
                    labels: promoLbls,
                    datasets: [{
                        label: 'Usage Count',
                        data: promoUsage,
                        backgroundColor: COLORS.slice(0, promoLbls.length),
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            ...optimizer.getTooltipDefaults(),
                            callbacks: {
                                label: ctx => ` ${ctx.raw} uses`
                            }
                        }
                    },
                    scales: {
                        y: {
                            ...optimizer.getAxisDefaults(),
                            beginAtZero: true
                        },
                        x: {
                            ...optimizer.getAxisDefaults(),
                            grid: { display: false }
                        }
                    }
                }
            }
        });
    }

    // Promotion ROI chart
    if (document.getElementById('promoRoiChart')) {
        optimizer.queueChart({
            id: 'promoRoiChart',
            chartConfig: {
                type: 'bar',
                data: {
                    labels: promoLbls,
                    datasets: [
                        {
                            label: 'Revenue',
                            data: promoRevArr,
                            backgroundColor: 'rgba(59,130,246,0.85)',
                            borderRadius: 6
                        },
                        {
                            label: 'Discount',
                            data: promoDisArr,
                            backgroundColor: 'rgba(239,68,68,0.7)',
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: optimizer.getTheme().legend,
                                usePointStyle: true,
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            ...optimizer.getTooltipDefaults(),
                            callbacks: {
                                label: ctx => ` ₱${ctx.raw.toLocaleString()}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            ...optimizer.getAxisDefaults(),
                            beginAtZero: true,
                            ticks: {
                                ...optimizer.getAxisDefaults().ticks,
                                callback: v => '₱' + (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v)
                            }
                        },
                        x: {
                            ...optimizer.getAxisDefaults(),
                            grid: { display: false }
                        }
                    }
                }
            }
        });
    }
}