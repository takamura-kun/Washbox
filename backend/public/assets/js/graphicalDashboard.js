// graphicalDashboard.js - Chart initialization for redesigned graphical dashboard

/**
 * Initialize all graphical dashboard charts
 */
function initializeGraphicalDashboard() {
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js not loaded yet, retrying...');
        setTimeout(initializeGraphicalDashboard, 200);
        return;
    }

    console.log('🎨 Initializing Graphical Dashboard Charts...');

    // Initialize hero stat charts
    initializeHeroStatCharts();

    // Initialize main visual charts
    initializeMainVisualCharts();

    // Animate chart elements
    animateChartElements();

    console.log('✅ Graphical Dashboard Charts initialized');
}

/**
 * Initialize hero stat mini charts (donut, sparkline, ring, gauge)
 */
function initializeHeroStatCharts() {
    const chartColors = {
        blue: '#667eea',
        green: '#10b981',
        purple: '#8b5cf6',
        red: '#ef4444',
        white: 'rgba(255, 255, 255, 0.9)'
    };

    // Laundry Donut Chart
    const laundryDonutCtx = document.getElementById('laundryDonutChart');
    if (laundryDonutCtx) {
        const todayLaundries = window.DASHBOARD_STATS?.todayLaundries || 0;
        const totalLaundries = window.DASHBOARD_STATS?.totalLaundries || 100;
        const remaining = Math.max(totalLaundries - todayLaundries, 0);

        new Chart(laundryDonutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Today', 'Others'],
                datasets: [{
                    data: [todayLaundries, remaining],
                    backgroundColor: [chartColors.white, 'rgba(255, 255, 255, 0.2)'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
    }

    // Revenue Sparkline Chart
    const revenueSparkCtx = document.getElementById('revenueSparkChart');
    if (revenueSparkCtx && window.REVENUE_DATA) {
        new Chart(revenueSparkCtx, {
            type: 'line',
            data: {
                labels: window.REVENUE_DATA.labels || [],
                datasets: [{
                    data: window.REVENUE_DATA.values || [],
                    borderColor: chartColors.white,
                    backgroundColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });
    }

    // Customer Ring Chart
    const customerRingCtx = document.getElementById('customerRingChart');
    if (customerRingCtx) {
        const activeCustomers = window.DASHBOARD_STATS?.activeCustomers || 0;
        const totalCustomers = window.DASHBOARD_STATS?.totalCustomers || 1000;
        const inactive = Math.max(totalCustomers - activeCustomers, 0);

        new Chart(customerRingCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Inactive'],
                datasets: [{
                    data: [activeCustomers, inactive],
                    backgroundColor: [chartColors.white, 'rgba(255, 255, 255, 0.2)'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '75%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
    }

    // Unclaimed Gauge Chart
    const unclaimedGaugeCtx = document.getElementById('unclaimedGaugeChart');
    if (unclaimedGaugeCtx) {
        const unclaimedCount = window.DASHBOARD_STATS?.unclaimedLaundry || 0;
        const maxUnclaimed = 50; // Threshold
        const remaining = Math.max(maxUnclaimed - unclaimedCount, 0);

        new Chart(unclaimedGaugeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Unclaimed', 'Safe'],
                datasets: [{
                    data: [unclaimedCount, remaining],
                    backgroundColor: [chartColors.white, 'rgba(255, 255, 255, 0.2)'],
                    borderWidth: 0,
                    circumference: 180,
                    rotation: 270
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
    }
}

/**
 * Initialize main visual charts (revenue trend, service pie, branch bar, payment doughnut)
 */
function initializeMainVisualCharts() {
    const chartColors = {
        blue: '#3b82f6',
        green: '#10b981',
        purple: '#8b5cf6',
        orange: '#f59e0b',
        red: '#ef4444',
        cyan: '#06b6d4',
        indigo: '#4f46e5',
        pink: '#ec4899'
    };

    // Main Revenue & Orders Trend Chart
    const mainRevenueCtx = document.getElementById('mainRevenueChart');
    if (mainRevenueCtx && window.REVENUE_DATA) {
        new Chart(mainRevenueCtx, {
            type: 'line',
            data: {
                labels: window.REVENUE_DATA.labels || ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    {
                        label: 'Revenue',
                        data: window.REVENUE_DATA.values || [12000, 15000, 13500, 18000, 16500, 21000, 19500],
                        borderColor: chartColors.green,
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointBackgroundColor: chartColors.green,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Orders',
                        data: window.DAILY_LAUNDRY_DATA?.counts || [45, 52, 48, 61, 55, 68, 63],
                        borderColor: chartColors.blue,
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointBackgroundColor: chartColors.blue,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14, weight: '600' },
                        bodyFont: { size: 13 },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    if (context.datasetIndex === 0) {
                                        label += '₱' + context.parsed.y.toLocaleString();
                                    } else {
                                        label += context.parsed.y + ' orders';
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: { size: 11 },
                            callback: function(value) {
                                return '₱' + (value / 1000) + 'k';
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: { size: 11, weight: '600' }
                        }
                    }
                }
            }
        });
    }

    // Service Mix Pie Chart
    const servicePieCtx = document.getElementById('servicePieChart');
    if (servicePieCtx && window.SERVICE_CHART_DATA) {
        const serviceData = window.SERVICE_CHART_DATA.all_services || [];
        const topServices = serviceData.slice(0, 5);

        new Chart(servicePieCtx, {
            type: 'pie',
            data: {
                labels: topServices.map(s => s.name),
                datasets: [{
                    data: topServices.map(s => s.count),
                    backgroundColor: [
                        chartColors.blue,
                        chartColors.purple,
                        chartColors.orange,
                        chartColors.green,
                        chartColors.pink
                    ],
                    borderColor: '#fff',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 12, weight: '600' },
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return `${context.label}: ${context.parsed} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Branch Performance Bar Chart
    const branchBarCtx = document.getElementById('branchBarChart');
    if (branchBarCtx && window.BRANCHES) {
        const branchData = window.BRANCHES.slice(0, 5);

        new Chart(branchBarCtx, {
            type: 'bar',
            data: {
                labels: branchData.map(b => b.name),
                datasets: [{
                    label: 'Revenue',
                    data: branchData.map(b => b.revenue || Math.random() * 50000 + 10000),
                    backgroundColor: [
                        chartColors.blue,
                        chartColors.purple,
                        chartColors.cyan,
                        chartColors.green,
                        chartColors.orange
                    ],
                    borderRadius: 10,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: ₱' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: { size: 11 },
                            callback: function(value) {
                                return '₱' + (value / 1000) + 'k';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: { size: 11, weight: '600' }
                        }
                    }
                }
            }
        });
    }

    // Payment Methods Doughnut Chart
    const paymentDoughnutCtx = document.getElementById('paymentDoughnutChart');
    if (paymentDoughnutCtx && window.PAYMENT_METHODS_DATA) {
        const paymentData = window.PAYMENT_METHODS_DATA;

        new Chart(paymentDoughnutCtx, {
            type: 'doughnut',
            data: {
                labels: paymentData.map(p => p.method),
                datasets: [{
                    data: paymentData.map(p => p.amount),
                    backgroundColor: [
                        chartColors.green,
                        chartColors.blue,
                        chartColors.orange,
                        chartColors.purple
                    ],
                    borderColor: '#fff',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 12, weight: '600' },
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return `${context.label}: ₱${context.parsed.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
}

/**
 * Animate chart elements on load
 */
function animateChartElements() {
    // Animate hero stat cards
    const heroCards = document.querySelectorAll('.hero-stat-card');
    heroCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });

    // Animate visual cards
    const visualCards = document.querySelectorAll('.visual-card');
    visualCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        }, (heroCards.length * 100) + (index * 150));
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeGraphicalDashboard);
} else {
    // DOM already loaded, initialize after a short delay to ensure Chart.js is loaded
    setTimeout(initializeGraphicalDashboard, 500);
}

// Export for use in other modules
window.initializeGraphicalDashboard = initializeGraphicalDashboard;
