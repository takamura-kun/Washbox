// charts.js - Dashboard chart management

import { DASHBOARD_CONFIG } from './config.js';

class ChartManager {
    constructor() {
        this.themeObserver = null;
    }

    /**
     * Initialize all charts with ultra-optimized batching
     */
    initializeCharts() {
        if (typeof Chart === "undefined") {
            console.warn("Chart.js is not loaded. Charts will not be displayed.");
            return;
        }

        // Pause performance monitoring during chart initialization
        if (window.performanceGuard) {
            window.performanceGuard.pauseMonitoring();
        }

        const t = this.getChartThemeColors();
        
        // Initialize charts in ultra-small batches to prevent blocking
        const chartInitializers = [
            () => this.initRevenueChart(t),
            () => this.initCustomerSourceChart(t),
            () => this.initCustomerBranchChart(t),
            () => this.initFullServiceChart(t),
            () => this.initSelfServiceChart(t),
            () => this.initAddonServiceChart(t),
            () => this.initDailyLaundryChart(t),
            () => this.initYoyRevenueChart(t),
            () => this.initChartThemeObserver()
        ];
        
        let currentIndex = 0;
        
        const initializeNextBatch = () => {
            const startTime = performance.now();
            
            // Initialize charts until we hit 5ms limit (reduced from 10ms)
            while (currentIndex < chartInitializers.length && (performance.now() - startTime) < 5) {
                try {
                    chartInitializers[currentIndex]();
                } catch (error) {
                    console.error(`Chart initialization error at index ${currentIndex}:`, error);
                }
                currentIndex++;
            }
            
            // Continue if more charts remain
            if (currentIndex < chartInitializers.length) {
                setTimeout(initializeNextBatch, 0);
            } else {
                // Resume performance monitoring after all charts are initialized
                if (window.performanceGuard) {
                    window.performanceGuard.resumeMonitoring();
                }
                console.log('✅ All charts initialized successfully');
            }
        };
        
        // Start initialization in next tick to prevent blocking current execution
        setTimeout(initializeNextBatch, 0);
    }

    /**
     * Get chart theme colors based on current theme
     */
    getChartThemeColors() {
        const dark = document.documentElement.getAttribute("data-theme") === "dark";
        return {
            isDark: dark,
            gridColor: dark ? "rgba(255, 255, 255, 0.06)" : "rgba(0, 0, 0, 0.05)",
            tickColor: dark ? "#9ca3af" : "#6b7280",
            legendColor: dark ? "#d1d5db" : "#374151",
            tooltipBg: dark ? "rgba(17, 24, 39, 0.95)" : "rgba(15, 23, 42, 0.9)",
            sliceBorder: dark ? "#1f2937" : "#ffffff",
        };
    }

    /**
     * Initialize revenue chart
     */
    initRevenueChart(t) {
        const revenueCtx = document.getElementById("revenueChart");
        if (revenueCtx && window.REVENUE_DATA) {
            DASHBOARD_CONFIG.charts.revenue = new Chart(revenueCtx, {
                type: "line",
                data: {
                    labels: window.REVENUE_DATA.labels || [],
                    datasets: [{
                        label: "Daily Revenue",
                        data: window.REVENUE_DATA.values || [],
                        borderColor: "#2563eb",
                        backgroundColor: t.isDark
                            ? "rgba(37, 99, 235, 0.18)"
                            : "rgba(37, 99, 235, 0.10)",
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: "#2563eb",
                        pointBorderColor: t.isDark ? "#1f2937" : "#ffffff",
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointHoverBorderColor: t.isDark ? "#111827" : "#ffffff",
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            mode: "index",
                            intersect: false,
                            backgroundColor: t.tooltipBg,
                            titleColor: "#ffffff",
                            bodyColor: "#e5e7eb",
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                label: (ctx) => "₱" + ctx.parsed.y.toLocaleString(),
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: t.gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: t.tickColor,
                                callback: (value) => "₱" + value.toLocaleString(),
                                font: { size: 11, family: "system-ui" },
                            },
                        },
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: {
                                color: t.tickColor,
                                font: { size: 11, family: "system-ui" },
                            },
                        },
                    },
                    interaction: { intersect: false, mode: "index" },
                },
            });
        }
    }

    /**
     * Initialize customer source chart
     */
    initCustomerSourceChart(t) {
        const customerSourceCtx = document.getElementById("customerSourceChart");
        if (customerSourceCtx && window.CUSTOMER_SOURCE_DATA) {
            DASHBOARD_CONFIG.charts.customerSource = new Chart(customerSourceCtx, {
                type: "doughnut",
                data: {
                    labels: ["Walk-in", "Mobile App"],
                    datasets: [{
                        data: [
                            window.CUSTOMER_SOURCE_DATA.walk_in || 0,
                            window.CUSTOMER_SOURCE_DATA.app || 0,
                        ],
                        backgroundColor: ["#2563eb", "#10b981"],
                        borderColor: t.sliceBorder,
                        borderWidth: t.isDark ? 2 : 0,
                        hoverOffset: 15,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: {
                                color: t.legendColor,
                                padding: 20,
                                usePointStyle: true,
                                font: { size: 12, family: "system-ui" },
                            },
                        },
                        tooltip: {
                            backgroundColor: t.tooltipBg,
                            titleColor: "#ffffff",
                            bodyColor: "#e5e7eb",
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                label: (ctx) => {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = Math.round((ctx.raw / total) * 100);
                                    return `${ctx.label}: ${ctx.raw} (${pct}%)`;
                                },
                            },
                        },
                    },
                    cutout: "70%",
                    animation: { animateScale: true, animateRotate: true },
                },
            });
        }
    }

    /**
     * Initialize customer branch chart
     */
    initCustomerBranchChart(t) {
        const customerBranchCtx = document.getElementById("customerBranchChart");
        if (customerBranchCtx) {
            // Calculate totals from customer branch pipeline data
            const totalWalkIn = window.CUSTOMER_BRANCH_DATA?.length 
                ? window.CUSTOMER_BRANCH_DATA.reduce((s, b) => s + (b.walk_in || 0), 0)
                : 0;
            const totalMobile = window.CUSTOMER_BRANCH_DATA?.length
                ? window.CUSTOMER_BRANCH_DATA.reduce((s, b) => s + (b.mobile || 0), 0)
                : 0;
            
            // If no data, show empty state message
            if (totalWalkIn === 0 && totalMobile === 0) {
                const parentCard = customerBranchCtx.closest('.cbp-chart-card');
                if (parentCard) {
                    const chartWrap = parentCard.querySelector('.cbp-donut-wrap');
                    if (chartWrap) {
                        chartWrap.innerHTML = `
                            <div class="text-center py-5">
                                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-0">No customer data available</p>
                                <small class="text-muted">Customer split will appear once customers are registered</small>
                            </div>
                        `;
                    }
                }
                return;
            }

            DASHBOARD_CONFIG.charts.customerBranch = new Chart(customerBranchCtx, {
                type: "doughnut",
                data: {
                    labels: ["Walk-In", "Self-Registered"],
                    datasets: [{
                        data: [totalWalkIn, totalMobile],
                        backgroundColor: ["#34d399", "#818cf8"],
                        borderColor: t.sliceBorder,
                        borderWidth: t.isDark ? 2 : 0,
                        hoverOffset: 14,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: "68%",
                    animation: { animateScale: true, animateRotate: true },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: t.tooltipBg,
                            titleColor: "#ffffff",
                            bodyColor: "#e5e7eb",
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                label: (ctx) => {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? Math.round((ctx.raw / total) * 100) : 0;
                                    return ` ${ctx.label}: ${ctx.raw.toLocaleString()} (${pct}%)`;
                                },
                            },
                        },
                    },
                },
            });
        }
    }

    /**
     * Initialize service charts
     */
    initFullServiceChart(t) {
        const fullServiceCtx = document.getElementById("fullServiceChart");
        if (fullServiceCtx && window.SERVICE_CHART_DATA?.drop_off?.labels?.length) {
            const fs = window.SERVICE_CHART_DATA.drop_off;
            const fullColors = ["#1d4ed8", "#2563eb", "#3b82f6", "#60a5fa", "#93c5fd", "#bfdbfe", "#dbeafe"];

            DASHBOARD_CONFIG.charts.fullService = new Chart(fullServiceCtx, {
                type: "doughnut",
                data: {
                    labels: fs.labels,
                    datasets: [{
                        data: fs.counts,
                        backgroundColor: fullColors.slice(0, fs.labels.length),
                        borderColor: t.sliceBorder,
                        borderWidth: t.isDark ? 2 : 0,
                        hoverOffset: 14,
                    }],
                },
                options: this.getServiceChartOptions(t, 'drop_off'),
            });
        }
    }

    initSelfServiceChart(t) {
        const selfServiceCtx = document.getElementById("selfServiceChart");
        if (selfServiceCtx && window.SERVICE_CHART_DATA?.self_service?.labels?.length) {
            const ss = window.SERVICE_CHART_DATA.self_service;
            const selfColors = ["#5b21b6", "#6d28d9", "#7c3aed", "#8b5cf6", "#a78bfa", "#c4b5fd", "#ede9fe"];

            DASHBOARD_CONFIG.charts.selfService = new Chart(selfServiceCtx, {
                type: "doughnut",
                data: {
                    labels: ss.labels,
                    datasets: [{
                        data: ss.counts,
                        backgroundColor: selfColors.slice(0, ss.labels.length),
                        borderColor: t.sliceBorder,
                        borderWidth: t.isDark ? 2 : 0,
                        hoverOffset: 14,
                    }],
                },
                options: this.getServiceChartOptions(t, 'self_service'),
            });
        }
    }

    initAddonServiceChart(t) {
        const addonServiceCtx = document.getElementById("addonServiceChart");
        if (addonServiceCtx && window.SERVICE_CHART_DATA?.addon?.labels?.length) {
            const ad = window.SERVICE_CHART_DATA.addon;
            const addonColors = ["#92400e", "#b45309", "#d97706", "#f59e0b", "#fbbf24", "#fcd34d", "#fde68a"];

            DASHBOARD_CONFIG.charts.addonService = new Chart(addonServiceCtx, {
                type: "doughnut",
                data: {
                    labels: ad.labels,
                    datasets: [{
                        data: ad.counts,
                        backgroundColor: addonColors.slice(0, ad.labels.length),
                        borderColor: t.sliceBorder,
                        borderWidth: t.isDark ? 2 : 0,
                        hoverOffset: 14,
                    }],
                },
                options: this.getServiceChartOptions(t, 'addon'),
            });
        }
    }

    /**
     * Get service chart options
     */
    getServiceChartOptions(t, serviceType) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "68%",
            animation: { animateScale: true, animateRotate: true },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: t.tooltipBg,
                    titleColor: "#ffffff",
                    bodyColor: "#e5e7eb",
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: (ctx) => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = total > 0 ? Math.round((ctx.raw / total) * 100) : 0;
                            return ` ${ctx.label}: ${ctx.raw.toLocaleString()} laundries (${pct}%)`;
                        },
                        afterLabel: (ctx) => {
                            const rev = window.SERVICE_CHART_DATA[serviceType].revenues[ctx.dataIndex] ?? 0;
                            return ` Revenue: ₱${rev.toLocaleString()}`;
                        },
                    },
                },
            },
        };
    }

    /**
     * Initialize daily laundry chart
     */
    initDailyLaundryChart(t) {
        const dailyLaundryCtx = document.getElementById("dailyLaundryChart");
        if (dailyLaundryCtx && window.DAILY_LAUNDRY_DATA) {
            DASHBOARD_CONFIG.charts.dailyLaundry = new Chart(dailyLaundryCtx, {
                type: "bar",
                data: {
                    labels: window.DAILY_LAUNDRY_DATA.labels || [],
                    datasets: [{
                        label: "Laundries",
                        data: window.DAILY_LAUNDRY_DATA.data || [],
                        backgroundColor: "rgba(59, 130, 246, 0.8)",
                        borderColor: "#3b82f6",
                        borderWidth: 1,
                        borderRadius: 4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: t.tooltipBg,
                            titleColor: "#ffffff",
                            bodyColor: "#e5e7eb",
                            padding: 12,
                            cornerRadius: 10,
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: t.gridColor, drawBorder: false },
                            ticks: { color: t.tickColor, font: { size: 11 } },
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: t.tickColor, font: { size: 11 } },
                        },
                    },
                },
            });
        }
    }

    /**
     * Initialize year-over-year revenue chart
     */
    initYoyRevenueChart(t) {
        const yoyRevenueCtx = document.getElementById("yoyRevenueChart");
        if (yoyRevenueCtx && window.YOY_REVENUE_DATA) {
            DASHBOARD_CONFIG.charts.yoyRevenue = new Chart(yoyRevenueCtx, {
                type: "line",
                data: {
                    labels: window.YOY_REVENUE_DATA.years || [],
                    datasets: [{
                        label: "Annual Revenue",
                        data: window.YOY_REVENUE_DATA.data || [],
                        borderColor: "#ef4444",
                        backgroundColor: "rgba(239, 68, 68, 0.1)",
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: "#ef4444",
                        pointBorderColor: t.isDark ? "#1f2937" : "#ffffff",
                        pointBorderWidth: 2,
                        pointRadius: 6,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: t.tooltipBg,
                            titleColor: "#ffffff",
                            bodyColor: "#e5e7eb",
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                label: (ctx) => "₱" + ctx.parsed.y.toLocaleString(),
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: t.gridColor, drawBorder: false },
                            ticks: {
                                color: t.tickColor,
                                callback: (value) => "₱" + (value / 1000000).toFixed(1) + "M",
                                font: { size: 11 },
                            },
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: t.tickColor, font: { size: 11 } },
                        },
                    },
                },
            });
        }
    }

    /**
     * Update charts for theme changes with optimized batching
     */
    updateChartsForTheme() {
        const t = this.getChartThemeColors();
        const chartKeys = Object.keys(DASHBOARD_CONFIG.charts).filter(key => DASHBOARD_CONFIG.charts[key]);
        
        if (chartKeys.length === 0) return;
        
        // Pause performance monitoring during theme updates
        if (window.performanceGuard) {
            window.performanceGuard.pauseMonitoring();
        }
        
        // Process charts in ultra-small batches to prevent blocking
        let currentIndex = 0;
        const batchSize = 1; // Process 1 chart per frame for maximum smoothness
        
        const processNextBatch = () => {
            const startTime = performance.now();
            
            // Process charts until we hit 3ms limit (reduced from 8ms)
            while (currentIndex < chartKeys.length && (performance.now() - startTime) < 3) {
                const key = chartKeys[currentIndex];
                const chart = DASHBOARD_CONFIG.charts[key];
                
                if (chart) {
                    this.updateChartTheme(chart, t, key);
                }
                currentIndex++;
            }
            
            // Continue processing if more charts remain
            if (currentIndex < chartKeys.length) {
                requestAnimationFrame(processNextBatch);
            } else {
                // Resume performance monitoring after theme updates
                if (window.performanceGuard) {
                    window.performanceGuard.resumeMonitoring();
                }
            }
        };
        
        requestAnimationFrame(processNextBatch);
    }

    /**
     * Update individual chart theme
     */
    updateChartTheme(chart, t, chartType) {
        // Batch all theme updates to minimize chart.update() calls
        const updates = [];
        
        // Update scales if they exist
        if (chart.options.scales) {
            if (chart.options.scales.y) {
                if (chart.options.scales.y.grid) {
                    updates.push(() => chart.options.scales.y.grid.color = t.gridColor);
                }
                if (chart.options.scales.y.ticks) {
                    updates.push(() => chart.options.scales.y.ticks.color = t.tickColor);
                }
            }
            if (chart.options.scales.x) {
                if (chart.options.scales.x.ticks) {
                    updates.push(() => chart.options.scales.x.ticks.color = t.tickColor);
                }
            }
        }

        // Update tooltip
        if (chart.options.plugins?.tooltip) {
            updates.push(() => chart.options.plugins.tooltip.backgroundColor = t.tooltipBg);
        }

        // Update legend
        if (chart.options.plugins?.legend?.labels) {
            updates.push(() => chart.options.plugins.legend.labels.color = t.legendColor);
        }

        // Update dataset-specific properties
        if (chart.data.datasets) {
            chart.data.datasets.forEach((dataset, index) => {
                // Update border colors for doughnut charts
                if (chart.config.type === 'doughnut') {
                    updates.push(() => {
                        dataset.borderColor = t.sliceBorder;
                        dataset.borderWidth = t.isDark ? 2 : 0;
                    });
                }
                
                // Update background colors for area charts
                if (chartType === 'revenue' || chartType === 'yoyRevenue') {
                    updates.push(() => {
                        if (chartType === 'revenue') {
                            dataset.backgroundColor = t.isDark
                                ? "rgba(37, 99, 235, 0.18)"
                                : "rgba(37, 99, 235, 0.10)";
                        } else if (chartType === 'yoyRevenue') {
                            dataset.backgroundColor = t.isDark
                                ? "rgba(239, 68, 68, 0.18)"
                                : "rgba(239, 68, 68, 0.10)";
                        }
                    });
                }
            });
        }

        // Apply all updates at once
        updates.forEach(update => update());
        
        // Single chart update with no animation for better performance
        chart.update('none');
    }

    /**
     * Initialize theme observer
     */
    initChartThemeObserver() {
        // Debounce theme changes to prevent rapid updates
        let themeUpdateTimeout;
        
        this.themeObserver = new MutationObserver((mutations) => {
            mutations.forEach((m) => {
                if (m.type === "attributes" && m.attributeName === "data-theme") {
                    // Clear previous timeout
                    if (themeUpdateTimeout) {
                        clearTimeout(themeUpdateTimeout);
                    }
                    
                    // Debounce theme updates by 100ms
                    themeUpdateTimeout = setTimeout(() => {
                        this.updateChartsForTheme();
                    }, 100);
                }
            });
        });
        
        this.themeObserver.observe(document.documentElement, { 
            attributes: true,
            attributeFilter: ['data-theme'] // Only watch for theme changes
        });
    }

    /**
     * Destroy all charts and cleanup
     */
    destroy() {
        Object.keys(DASHBOARD_CONFIG.charts).forEach(key => {
            if (DASHBOARD_CONFIG.charts[key]) {
                DASHBOARD_CONFIG.charts[key].destroy();
                DASHBOARD_CONFIG.charts[key] = null;
            }
        });

        if (this.themeObserver) {
            this.themeObserver.disconnect();
            this.themeObserver = null;
        }
    }
}

// Create singleton instance
export const chartManager = new ChartManager();

// Make it globally available for backward compatibility
window.chartManager = chartManager;