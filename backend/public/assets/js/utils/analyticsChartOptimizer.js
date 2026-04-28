// analyticsChartOptimizer.js - Non-blocking chart initialization for analytics

class AnalyticsChartOptimizer {
    constructor() {
        this.charts = new Map();
        this.initQueue = [];
        this.isProcessing = false;
        this.maxProcessingTime = 8; // Max 8ms per batch
    }

    /**
     * Queue chart for initialization
     */
    queueChart(config) {
        this.initQueue.push(config);
        if (!this.isProcessing) {
            this.processQueue();
        }
    }

    /**
     * Process chart initialization queue in small batches
     */
    async processQueue() {
        if (this.isProcessing || this.initQueue.length === 0) return;
        
        this.isProcessing = true;
        
        while (this.initQueue.length > 0) {
            const startTime = performance.now();
            
            // Process charts until we hit time limit
            while (this.initQueue.length > 0 && (performance.now() - startTime) < this.maxProcessingTime) {
                const config = this.initQueue.shift();
                try {
                    await this.initializeChart(config);
                } catch (error) {
                    console.error(`Failed to initialize chart ${config.id}:`, error);
                }
            }
            
            // Yield control if more charts remain
            if (this.initQueue.length > 0) {
                await new Promise(resolve => {
                    if (window.requestIdleCallback) {
                        requestIdleCallback(resolve, { timeout: 100 });
                    } else {
                        setTimeout(resolve, 0);
                    }
                });
            }
        }
        
        this.isProcessing = false;
    }

    /**
     * Initialize a single chart
     */
    async initializeChart(config) {
        const ctx = document.getElementById(config.id);
        if (!ctx) {
            console.warn(`Chart canvas ${config.id} not found`);
            return;
        }

        const chart = new Chart(ctx, config.chartConfig);
        this.charts.set(config.id, chart);
        
        // Register for theme updates if needed
        if (config.registerForUpdates) {
            this.registerChart(config.id, chart);
        }
    }

    /**
     * Register chart for live updates
     */
    registerChart(key, chartInstance) {
        if (window.LIVE_CHARTS) {
            window.LIVE_CHARTS[key] = chartInstance;
        }
    }

    /**
     * Get theme colors
     */
    getTheme() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark' ||
                      document.body.getAttribute('data-theme') === 'dark';
        
        return {
            grid: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)',
            tick: isDark ? '#94a3b8' : '#6b7280',
            legend: isDark ? '#cbd5e1' : '#374151',
            tooltipBg: isDark ? '#1e293b' : '#1f2937',
            border: isDark ? '#334155' : 'rgba(255,255,255,0.8)',
        };
    }

    /**
     * Get default axis configuration
     */
    getAxisDefaults(opts = {}) {
        const theme = this.getTheme();
        return {
            grid: { color: theme.grid, drawBorder: false },
            ticks: { color: theme.tick, font: { size: 11 } },
            ...opts
        };
    }

    /**
     * Get default tooltip configuration
     */
    getTooltipDefaults() {
        const theme = this.getTheme();
        return {
            backgroundColor: theme.tooltipBg,
            titleColor: '#fff',
            bodyColor: '#e5e7eb',
            padding: 12,
            cornerRadius: 10,
            mode: 'index',
            intersect: false,
        };
    }

    /**
     * Create sparkline chart
     */
    createSparkline(id, data, color = '#fff') {
        this.queueChart({
            id,
            chartConfig: {
                type: 'line',
                data: {
                    labels: data.map((_, i) => i),
                    datasets: [{
                        data,
                        borderColor: 'rgba(255,255,255,0.7)',
                        borderWidth: 2,
                        pointRadius: 0,
                        fill: false,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    animation: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    },
                    scales: {
                        x: { display: false },
                        y: { display: false }
                    }
                }
            }
        });
    }

    /**
     * Create revenue chart
     */
    createRevenueChart(labels, data) {
        const theme = this.getTheme();
        
        this.queueChart({
            id: 'revenueChart',
            registerForUpdates: true,
            chartConfig: {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Revenue',
                        data,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.12)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.45,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            ...this.getTooltipDefaults(),
                            callbacks: {
                                label: ctx => ' ₱' + ctx.parsed.y.toLocaleString()
                            }
                        }
                    },
                    scales: {
                        y: {
                            ...this.getAxisDefaults(),
                            beginAtZero: true,
                            ticks: {
                                ...this.getAxisDefaults().ticks,
                                callback: v => '₱' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v)
                            }
                        },
                        x: {
                            ...this.getAxisDefaults(),
                            grid: { display: false }
                        }
                    }
                }
            }
        });
    }

    /**
     * Create doughnut chart
     */
    createDoughnutChart(id, labels, data, colors, registerForUpdates = false) {
        const theme = this.getTheme();
        
        this.queueChart({
            id,
            registerForUpdates,
            chartConfig: {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data,
                        backgroundColor: colors,
                        borderColor: theme.border,
                        borderWidth: 2,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '68%',
                    animation: { animateScale: true },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: theme.legend,
                                padding: 14,
                                usePointStyle: true,
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            ...this.getTooltipDefaults(),
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.raw}`
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Create bar chart
     */
    createBarChart(id, labels, datasets, options = {}) {
        this.queueChart({
            id,
            chartConfig: {
                type: 'bar',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: this.getTooltipDefaults()
                    },
                    scales: {
                        y: {
                            ...this.getAxisDefaults(),
                            beginAtZero: true
                        },
                        x: {
                            ...this.getAxisDefaults(),
                            grid: { display: false }
                        }
                    },
                    ...options
                }
            }
        });
    }

    /**
     * Destroy all charts
     */
    destroyAll() {
        this.charts.forEach(chart => {
            try {
                chart.destroy();
            } catch (error) {
                console.warn('Error destroying chart:', error);
            }
        });
        this.charts.clear();
        this.initQueue = [];
        this.isProcessing = false;
    }
}

// Create global instance
window.analyticsChartOptimizer = new AnalyticsChartOptimizer();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AnalyticsChartOptimizer;
}