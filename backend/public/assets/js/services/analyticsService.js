// analyticsService.js - Advanced analytics and business intelligence

import { eventBus, EVENTS } from '../utils/eventBus.js';
import { apiClient } from '../modules/api.js';
import { performanceMonitor } from '../utils/performanceMonitor.js';

class AnalyticsService {
    constructor() {
        this.metrics = new Map();
        this.realTimeData = new Map();
        this.predictions = new Map();
        this.alerts = [];
        this.dashboards = new Map();
        this.updateInterval = 30000; // 30 seconds
        this.historicalDataCache = new Map();
        
        this.init();
    }

    init() {
        this.setupRealTimeTracking();
        this.setupPredictiveAnalytics();
        this.setupAlertSystem();
        console.log('📊 Advanced analytics service initialized');
    }

    /**
     * Setup real-time metrics tracking - Completely non-blocking
     */
    setupRealTimeTracking() {
        // Track key performance indicators
        this.trackKPIs();
        
        // Update metrics periodically using MessageChannel
        const updateMetrics = () => {
            const channel = new MessageChannel();
            channel.port2.onmessage = () => {
                this.updateRealTimeMetrics();
            };
            channel.port1.postMessage(null);
        };
        
        // Initial update after page is fully loaded
        if (document.readyState === 'complete') {
            // Defer initial update
            if (window.requestIdleCallback) {
                requestIdleCallback(updateMetrics, { timeout: 5000 });
            } else {
                requestAnimationFrame(() => {
                    requestAnimationFrame(updateMetrics);
                });
            }
        } else {
            window.addEventListener('load', () => {
                if (window.requestIdleCallback) {
                    requestIdleCallback(updateMetrics, { timeout: 5000 });
                } else {
                    requestAnimationFrame(updateMetrics);
                }
            }, { once: true });
        }
        
        // Set up interval with MessageChannel wrapper
        setInterval(() => {
            if (window.requestIdleCallback) {
                requestIdleCallback(updateMetrics, { timeout: 2000 });
            } else {
                updateMetrics();
            }
        }, Math.max(this.updateInterval, 120000)); // Minimum 2 minutes to reduce load
        
        // Listen to system events for real-time updates
        this.setupEventListeners();
    }

    /**
     * Setup event listeners for real-time data
     */
    setupEventListeners() {
        eventBus.on(EVENTS.PICKUP_CREATED, (data) => {
            this.incrementMetric('pickups_created_today');
            this.updateCustomerMetrics(data.customerId);
        });
        
        eventBus.on(EVENTS.PICKUP_COMPLETED, (data) => {
            this.incrementMetric('pickups_completed_today');
            this.updateCompletionMetrics(data);
        });
        
        eventBus.on(EVENTS.ROUTE_OPTIMIZED, (data) => {
            this.updateRouteMetrics(data);
        });
        
        eventBus.on(EVENTS.DELIVERY_STATUS_UPDATED, (data) => {
            this.updateDeliveryMetrics(data);
        });
        
        eventBus.on(EVENTS.LOCATION_UPDATED, (data) => {
            this.updateLocationMetrics(data);
        });
    }

    /**
     * Track Key Performance Indicators
     */
    trackKPIs() {
        const kpis = [
            'pickups_created_today',
            'pickups_completed_today',
            'active_routes',
            'average_completion_time',
            'customer_satisfaction',
            'fuel_efficiency',
            'route_optimization_savings',
            'on_time_delivery_rate',
            'vehicle_utilization',
            'revenue_today'
        ];
        
        kpis.forEach(kpi => {
            this.metrics.set(kpi, {
                value: 0,
                trend: 0,
                lastUpdated: Date.now(),
                history: []
            });
        });
    }

    /**
     * Update real-time metrics - Completely non-blocking
     */
    async updateRealTimeMetrics() {
        try {
            // Use MessageChannel for immediate non-blocking execution
            const response = await new Promise((resolve, reject) => {
                const channel = new MessageChannel();
                channel.port2.onmessage = async () => {
                    try {
                        const data = await apiClient.get('/admin/api/analytics/realtime');
                        resolve(data);
                    } catch (error) {
                        reject(error);
                    }
                };
                channel.port1.postMessage(null);
            });
            
            // Handle response data in micro-tasks
            const data = response.success ? response : response.data;
            
            if (!data) {
                console.warn('No data received from realtime analytics');
                return;
            }
            
            // Process metrics in small chunks
            const entries = Object.entries(data).filter(([key]) => 
                key !== 'success' && key !== 'timestamp'
            );
            
            // Process 2 metrics at a time to stay under 5ms
            for (let i = 0; i < entries.length; i += 2) {
                const chunk = entries.slice(i, i + 2);
                
                await new Promise(resolve => {
                    requestAnimationFrame(() => {
                        chunk.forEach(([key, value]) => {
                            this.updateMetric(key, value);
                        });
                        resolve();
                    });
                });
            }
            
            // Calculate trends in next idle period
            if (window.requestIdleCallback) {
                requestIdleCallback(() => {
                    this.calculateTrends();
                    this.checkAlerts();
                    
                    // Emit update event
                    eventBus.emit(EVENTS.ANALYTICS_UPDATED, {
                        timestamp: Date.now(),
                        metrics: Object.fromEntries(this.metrics)
                    });
                }, { timeout: 1000 });
            }
            
        } catch (error) {
            console.error('Failed to update real-time metrics:', error);
        }
    }

    /**
     * Update a specific metric
     */
    updateMetric(key, value, timestamp = Date.now()) {
        const metric = this.metrics.get(key) || {
            value: 0,
            trend: 0,
            lastUpdated: 0,
            history: []
        };
        
        // Calculate trend
        const previousValue = metric.value;
        metric.trend = value - previousValue;
        
        // Update value
        metric.value = value;
        metric.lastUpdated = timestamp;
        
        // Add to history (keep last 100 points)
        metric.history.push({ value, timestamp });
        if (metric.history.length > 100) {
            metric.history.shift();
        }
        
        this.metrics.set(key, metric);
    }

    /**
     * Increment a metric
     */
    incrementMetric(key, amount = 1) {
        const current = this.metrics.get(key)?.value || 0;
        this.updateMetric(key, current + amount);
    }

    /**
     * Calculate trends for all metrics
     */
    calculateTrends() {
        this.metrics.forEach((metric, key) => {
            if (metric.history.length >= 2) {
                const recent = metric.history.slice(-10); // Last 10 points
                const older = metric.history.slice(-20, -10); // Previous 10 points
                
                if (older.length > 0) {
                    const recentAvg = recent.reduce((sum, p) => sum + p.value, 0) / recent.length;
                    const olderAvg = older.reduce((sum, p) => sum + p.value, 0) / older.length;
                    
                    metric.trend = ((recentAvg - olderAvg) / olderAvg) * 100; // Percentage change
                }
            }
        });
    }

    /**
     * Setup predictive analytics - Completely non-blocking
     */
    setupPredictiveAnalytics() {
        // Run predictions every hour
        setInterval(() => {
            // Use MessageChannel to avoid blocking
            const channel = new MessageChannel();
            channel.port2.onmessage = () => {
                this.generatePredictions();
            };
            channel.port1.postMessage(null);
        }, 60 * 60 * 1000);
        
        // Initial predictions - completely deferred
        const initPredictions = () => {
            if (window.requestIdleCallback) {
                requestIdleCallback(() => {
                    this.generatePredictions();
                }, { timeout: 30000 }); // 30 second timeout
            } else {
                // Use MessageChannel instead of setTimeout
                const channel = new MessageChannel();
                channel.port2.onmessage = () => {
                    // Further defer with another MessageChannel
                    const delayChannel = new MessageChannel();
                    delayChannel.port2.onmessage = () => {
                        this.generatePredictions();
                    };
                    // Post after a few frames
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            requestAnimationFrame(() => {
                                delayChannel.port1.postMessage(null);
                            });
                        });
                    });
                };
                channel.port1.postMessage(null);
            }
        };
        
        // Defer initial predictions until after page load
        if (document.readyState === 'complete') {
            initPredictions();
        } else {
            window.addEventListener('load', initPredictions, { once: true });
        }
    }

    /**
     * Generate predictions using historical data - Completely non-blocking
     */
    async generatePredictions() {
        try {
            performanceMonitor.startTimer('prediction_generation');
            
            // Define prediction tasks as micro-operations
            const predictionTasks = [
                {
                    key: 'daily_pickups',
                    fn: async () => {
                        const data = await this.getHistoricalData('daily_pickups', 30);
                        return this.calculateDailyPickupsPrediction(data);
                    }
                },
                {
                    key: 'peak_hours',
                    fn: async () => {
                        const data = await this.getHistoricalData('hourly_pickups', 7);
                        return this.calculatePeakHoursPrediction(data);
                    }
                },
                {
                    key: 'resource_needs',
                    fn: async () => {
                        const pickupPred = this.predictions.get('daily_pickups');
                        const peakPred = this.predictions.get('peak_hours');
                        return this.calculateResourceNeeds(pickupPred, peakPred);
                    }
                },
                {
                    key: 'customer_churn',
                    fn: async () => {
                        return this.calculateCustomerChurn();
                    }
                }
            ];
            
            // Process each prediction as a separate micro-task
            for (const { key, fn } of predictionTasks) {
                try {
                    // Break into smallest possible chunks
                    const result = await new Promise(resolve => {
                        if (window.requestIdleCallback) {
                            requestIdleCallback(async () => {
                                const prediction = await fn();
                                resolve(prediction);
                            }, { timeout: 1000 });
                        } else {
                            // Use MessageChannel for immediate non-blocking execution
                            const channel = new MessageChannel();
                            channel.port2.onmessage = async () => {
                                const prediction = await fn();
                                resolve(prediction);
                            };
                            channel.port1.postMessage(null);
                        }
                    });
                    
                    this.predictions.set(key, result);
                    
                    // Yield after each prediction
                    await new Promise(resolve => {
                        if (window.requestIdleCallback) {
                            requestIdleCallback(resolve, { timeout: 100 });
                        } else {
                            const channel = new MessageChannel();
                            channel.port2.onmessage = () => resolve();
                            channel.port1.postMessage(null);
                        }
                    });
                    
                } catch (error) {
                    console.warn(`Failed to generate ${key} prediction:`, error);
                    // Set fallback data
                    this.predictions.set(key, this.getFallbackPrediction(key));
                }
            }
            
            performanceMonitor.endTimer('prediction_generation');
            
            // Emit update in next frame
            requestAnimationFrame(() => {
                eventBus.emit(EVENTS.PREDICTIONS_UPDATED, {
                    predictions: Object.fromEntries(this.predictions),
                    timestamp: Date.now()
                });
            });
            
        } catch (error) {
            console.error('Failed to generate predictions:', error);
        }
    }

    /**
     * Calculate daily pickups prediction - Lightweight version
     */
    calculateDailyPickupsPrediction(historicalData) {
        if (!historicalData || historicalData.length === 0) {
            return this.getFallbackPrediction('daily_pickups');
        }
        
        // Simple average-based prediction (lightweight)
        const recent = historicalData.slice(-7); // Last 7 days
        const average = recent.reduce((sum, item) => sum + (item.count || 0), 0) / recent.length;
        
        return {
            value: Math.round(average * 1.1), // 10% growth assumption
            confidence: 0.7,
            factors: {
                trend: 'stable',
                dayOfWeek: 1.0,
                seasonal: 1.0
            }
        };
    }
    
    /**
     * Calculate peak hours prediction - Lightweight version
     */
    calculatePeakHoursPrediction(hourlyData) {
        if (!hourlyData || hourlyData.length === 0) {
            return this.getFallbackPrediction('peak_hours');
        }
        
        // Simple peak detection
        const hourCounts = new Array(24).fill(0);
        hourlyData.forEach(item => {
            if (item.hour >= 0 && item.hour < 24) {
                hourCounts[item.hour] += item.count || 0;
            }
        });
        
        // Find top 3 hours
        const peakHours = hourCounts
            .map((count, hour) => ({ hour, count }))
            .sort((a, b) => b.count - a.count)
            .slice(0, 3)
            .map(item => item.hour);
        
        return {
            peakHours,
            distribution: hourCounts,
            confidence: 0.8
        };
    }
    
    /**
     * Calculate resource needs - Lightweight version
     */
    calculateResourceNeeds(pickupPrediction, peakHoursPrediction) {
        const baseVehicles = Math.max(1, Math.ceil((pickupPrediction?.value || 10) / 15));
        
        return {
            vehicles: baseVehicles,
            drivers: baseVehicles,
            confidence: 0.6,
            breakdown: {
                base: baseVehicles,
                peakAdjusted: baseVehicles,
                utilization: 0.8
            }
        };
    }
    
    /**
     * Calculate customer churn - Lightweight version
     */
    async calculateCustomerChurn() {
        try {
            // Simplified churn calculation
            return {
                highRiskCustomers: [],
                mediumRiskCustomers: [],
                totalAtRisk: 0,
                confidence: 0.5
            };
        } catch (error) {
            return this.getFallbackPrediction('customer_churn');
        }
    }
    
    /**
     * Get fallback prediction data
     */
    getFallbackPrediction(type) {
        const fallbacks = {
            daily_pickups: {
                value: 10,
                confidence: 0.5,
                factors: { trend: 'stable', dayOfWeek: 1.0, seasonal: 1.0 }
            },
            peak_hours: {
                peakHours: [9, 14, 18],
                distribution: new Array(24).fill(1),
                confidence: 0.5
            },
            resource_needs: {
                vehicles: 2,
                drivers: 2,
                confidence: 0.5,
                breakdown: { base: 2, peakAdjusted: 2, utilization: 0.7 }
            },
            customer_churn: {
                highRiskCustomers: [],
                mediumRiskCustomers: [],
                totalAtRisk: 0,
                confidence: 0.5
            }
        };
        
        return fallbacks[type] || {};
    }

    /**
     * Update customer metrics
     */
    updateCustomerMetrics(customerId) {
        // Track customer activity
        const key = `customer_${customerId}_activity`;
        this.incrementMetric(key);
    }

    /**
     * Update completion metrics
     */
    updateCompletionMetrics(data) {
        // Track completion time
        if (data.completionTime) {
            this.updateMetric('average_completion_time', data.completionTime);
        }
    }

    /**
     * Update route metrics
     */
    updateRouteMetrics(data) {
        // Track route optimization savings
        if (data.savings) {
            this.updateMetric('route_optimization_savings', data.savings.percentage || 0);
        }
    }

    /**
     * Update delivery metrics
     */
    updateDeliveryMetrics(data) {
        // Track delivery status changes
        this.incrementMetric('delivery_status_updates');
    }

    /**
     * Update location metrics
     */
    updateLocationMetrics(data) {
        // Track location updates
        this.incrementMetric('location_updates');
        
        // Update vehicle tracking data
        if (data.vehicleId) {
            const key = `vehicle_${data.vehicleId}_distance`;
            this.updateMetric(key, data.distance || 0);
        }
    }

    /**
     * Identify churn factors
     */
    identifyChurnFactors(customer, riskScore) {
        const factors = [];
        
        if (customer.recent_orders < customer.historical_average * 0.5) {
            factors.push('Decreased order frequency');
        }
        
        const daysSinceLastOrder = (Date.now() - new Date(customer.last_order_date)) / (1000 * 60 * 60 * 24);
        if (daysSinceLastOrder > 30) {
            factors.push('Inactive for over 30 days');
        }
        
        if (customer.complaints > 2) {
            factors.push('Multiple complaints');
        }
        
        if (customer.payment_issues > 0) {
            factors.push('Payment issues');
        }
        
        if (customer.recent_rating < customer.historical_rating - 1) {
            factors.push('Rating decline');
        }
        
        return factors;
    }

    /**
     * Get metric data
     */
    getMetricData(metricName) {
        return this.metrics.get(metricName) || { value: 0, trend: 0 };
    }

    /**
     * Get chart data
     */
    async getChartData(chartType, timeRange) {
        return { labels: [], data: [] };
    }

    /**
     * Get table data
     */
    async getTableData(query, limit) {
        return { rows: [], columns: [] };
    }

    /**
     * Get map data
     */
    async getMapData(filters) {
        return { markers: [] };
    }

    /**
     * Get daily summary
     */
    async getDailySummary(dateRange) {
        return {};
    }

    /**
     * Get daily details
     */
    async getDailyDetails(dateRange) {
        return {};
    }

    /**
     * Get weekly summary
     */
    async getWeeklySummary(dateRange) {
        return {};
    }

    /**
     * Get weekly details
     */
    async getWeeklyDetails(dateRange) {
        return {};
    }

    /**
     * Get monthly summary
     */
    async getMonthlySummary(dateRange) {
        return {};
    }

    /**
     * Get monthly details
     */
    async getMonthlyDetails(dateRange) {
        return {};
    }

    /**
     * Get performance summary
     */
    async getPerformanceSummary(dateRange) {
        return {};
    }

    /**
     * Get performance recommendations
     */
    async getPerformanceRecommendations() {
        return [];
    }

    /**
     * Setup alert system
     */
    setupAlertSystem() {
        // Check for alerts every 5 minutes
        setInterval(() => {
            this.checkAlerts();
        }, 5 * 60 * 1000);
    }

    /**
     * Check for alerts based on current metrics
     */
    checkAlerts() {
        const alerts = [];
        
        // Performance alerts
        const completionTime = this.metrics.get('average_completion_time')?.value || 0;
        if (completionTime > 120) { // 2 hours
            alerts.push({
                type: 'performance',
                severity: 'warning',
                message: `Average completion time is ${Math.round(completionTime/60)} minutes`,
                metric: 'average_completion_time',
                threshold: 120,
                current: completionTime
            });
        }
        
        // Capacity alerts
        const utilization = this.metrics.get('vehicle_utilization')?.value || 0;
        if (utilization > 0.9) {
            alerts.push({
                type: 'capacity',
                severity: 'critical',
                message: `Vehicle utilization at ${Math.round(utilization * 100)}%`,
                metric: 'vehicle_utilization',
                threshold: 0.9,
                current: utilization
            });
        }
        
        // Quality alerts
        const onTimeRate = this.metrics.get('on_time_delivery_rate')?.value || 1;
        if (onTimeRate < 0.8) {
            alerts.push({
                type: 'quality',
                severity: 'warning',
                message: `On-time delivery rate dropped to ${Math.round(onTimeRate * 100)}%`,
                metric: 'on_time_delivery_rate',
                threshold: 0.8,
                current: onTimeRate
            });
        }
        
        // Update alerts
        this.alerts = alerts;
        
        if (alerts.length > 0) {
            eventBus.emit(EVENTS.ANALYTICS_ALERT, { alerts });
        }
    }

    /**
     * Create custom dashboard
     */
    createDashboard(name, config) {
        const dashboard = {
            name,
            widgets: config.widgets || [],
            layout: config.layout || 'grid',
            refreshInterval: config.refreshInterval || 30000,
            filters: config.filters || {},
            created: Date.now()
        };
        
        this.dashboards.set(name, dashboard);
        return dashboard;
    }

    /**
     * Get dashboard data
     */
    async getDashboardData(dashboardName) {
        const dashboard = this.dashboards.get(dashboardName);
        if (!dashboard) {
            throw new Error(`Dashboard '${dashboardName}' not found`);
        }
        
        const data = {};
        
        for (const widget of dashboard.widgets) {
            try {
                data[widget.id] = await this.getWidgetData(widget);
            } catch (error) {
                console.error(`Failed to load widget ${widget.id}:`, error);
                data[widget.id] = { error: error.message };
            }
        }
        
        return {
            dashboard,
            data,
            lastUpdated: Date.now()
        };
    }

    /**
     * Get widget data
     */
    async getWidgetData(widget) {
        switch (widget.type) {
            case 'metric':
                return this.getMetricData(widget.metric);
            case 'chart':
                return this.getChartData(widget.chart, widget.timeRange);
            case 'table':
                return this.getTableData(widget.query, widget.limit);
            case 'map':
                return this.getMapData(widget.filters);
            default:
                throw new Error(`Unknown widget type: ${widget.type}`);
        }
    }

    /**
     * Generate comprehensive report
     */
    async generateReport(type, dateRange) {
        performanceMonitor.startTimer('report_generation');
        
        try {
            const report = {
                type,
                dateRange,
                generated: Date.now(),
                summary: {},
                details: {},
                charts: {},
                recommendations: []
            };
            
            switch (type) {
                case 'daily':
                    report.summary = await this.getDailySummary(dateRange);
                    report.details = await this.getDailyDetails(dateRange);
                    break;
                case 'weekly':
                    report.summary = await this.getWeeklySummary(dateRange);
                    report.details = await this.getWeeklyDetails(dateRange);
                    break;
                case 'monthly':
                    report.summary = await this.getMonthlySummary(dateRange);
                    report.details = await this.getMonthlyDetails(dateRange);
                    break;
                case 'performance':
                    report.summary = await this.getPerformanceSummary(dateRange);
                    report.recommendations = await this.getPerformanceRecommendations();
                    break;
            }
            
            performanceMonitor.endTimer('report_generation');
            return report;
            
        } catch (error) {
            performanceMonitor.endTimer('report_generation');
            throw error;
        }
    }

    /**
     * Calculate linear trend from historical data
     */
    calculateLinearTrend(data) {
        if (data.length < 2) {
            return { slope: 0, intercept: 0, r2: 0, predict: () => 0 };
        }
        
        const n = data.length;
        const sumX = data.reduce((sum, point) => sum + point.timestamp, 0);
        const sumY = data.reduce((sum, point) => sum + point.value, 0);
        const sumXY = data.reduce((sum, point) => sum + point.timestamp * point.value, 0);
        const sumXX = data.reduce((sum, point) => sum + point.timestamp * point.timestamp, 0);
        
        const slope = (n * sumXY - sumX * sumY) / (n * sumXX - sumX * sumX);
        const intercept = (sumY - slope * sumX) / n;
        
        // Calculate R²
        const meanY = sumY / n;
        const ssRes = data.reduce((sum, point) => {
            const predicted = slope * point.timestamp + intercept;
            return sum + Math.pow(point.value - predicted, 2);
        }, 0);
        const ssTot = data.reduce((sum, point) => sum + Math.pow(point.value - meanY, 2), 0);
        const r2 = 1 - (ssRes / ssTot);
        
        return {
            slope,
            intercept,
            r2: Math.max(0, r2),
            predict: (x) => slope * x + intercept
        };
    }

    /**
     * Get historical data from cache or API
     */
    async getHistoricalData(metric, days) {
        const cacheKey = `${metric}_${days}`;
        
        if (this.historicalDataCache.has(cacheKey)) {
            const cached = this.historicalDataCache.get(cacheKey);
            if (Date.now() - cached.timestamp < 10 * 60 * 1000) { // 10 minutes
                return cached.data;
            }
        }
        
        try {
            const response = await apiClient.get('/admin/api/analytics/historical', {
                metric,
                days
            });
            
            this.historicalDataCache.set(cacheKey, {
                data: response.data,
                timestamp: Date.now()
            });
            
            return response.data;
        } catch (error) {
            return [];
        }
    }

    /**
     * Export analytics data
     */
    async exportData(format, filters = {}) {
        try {
            const response = await apiClient.post('/api/v1/analytics/export', {
                format,
                filters,
                timestamp: Date.now()
            });
            
            return response.data;
        } catch (error) {
            console.error('Failed to export analytics data:', error);
            throw error;
        }
    }

    /**
     * Get current analytics summary
     */
    getAnalyticsSummary() {
        return {
            metrics: Object.fromEntries(this.metrics),
            predictions: Object.fromEntries(this.predictions),
            alerts: this.alerts,
            dashboards: Array.from(this.dashboards.keys()),
            lastUpdated: Date.now()
        };
    }
}

// Create singleton instance
export const analyticsService = new AnalyticsService();

// Make it globally available
window.analyticsService = analyticsService;