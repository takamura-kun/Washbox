// performanceMonitor.js - Performance tracking and monitoring

import { eventBus, EVENTS } from './eventBus.js';

class PerformanceMonitor {
    constructor() {
        this.metrics = new Map();
        this.performanceData = [];
        this.isEnabled = true;
        this.maxDataPoints = 100;
        this.thresholds = {
            slow: 2000, // 2 seconds (increased for API calls)
            critical: 5000 // 5 seconds
        };
        
        this.setupPerformanceObserver();
    }

    /**
     * Setup Performance Observer for automatic monitoring
     */
    setupPerformanceObserver() {
        if ('PerformanceObserver' in window) {
            try {
                // Monitor navigation timing
                const navObserver = new PerformanceObserver((list) => {
                    list.getEntries().forEach((entry) => {
                        this.recordNavigationTiming(entry);
                    });
                });
                navObserver.observe({ entryTypes: ['navigation'] });

                // Monitor resource loading
                const resourceObserver = new PerformanceObserver((list) => {
                    list.getEntries().forEach((entry) => {
                        this.recordResourceTiming(entry);
                    });
                });
                resourceObserver.observe({ entryTypes: ['resource'] });

                // Monitor long tasks
                if ('longtask' in PerformanceObserver.supportedEntryTypes) {
                    const longTaskObserver = new PerformanceObserver((list) => {
                        list.getEntries().forEach((entry) => {
                            this.recordLongTask(entry);
                        });
                    });
                    longTaskObserver.observe({ entryTypes: ['longtask'] });
                }

                console.log('✅ Performance Observer initialized');
            } catch (error) {
                console.warn('Performance Observer setup failed:', error);
            }
        }
    }

    /**
     * Start timing a custom operation
     */
    startTimer(name, metadata = {}) {
        if (!this.isEnabled) return;

        const startTime = performance.now();
        this.metrics.set(name, {
            startTime,
            metadata,
            type: 'custom'
        });

        return startTime;
    }

    /**
     * End timing and record the result
     */
    endTimer(name) {
        if (!this.isEnabled) return null;

        const timerData = this.metrics.get(name);
        if (!timerData) {
            console.warn(`Performance timer '${name}' not found`);
            return null;
        }

        const endTime = performance.now();
        const duration = endTime - timerData.startTime;

        const performanceEntry = {
            name,
            duration,
            startTime: timerData.startTime,
            endTime,
            type: timerData.type,
            metadata: timerData.metadata,
            timestamp: Date.now()
        };

        this.recordMetric(performanceEntry);
        this.metrics.delete(name);

        return duration;
    }

    /**
     * Record a metric directly
     */
    recordMetric(entry) {
        if (!this.isEnabled) return;

        // Add to performance data
        this.performanceData.push(entry);

        // Limit data points to prevent memory issues
        if (this.performanceData.length > this.maxDataPoints) {
            this.performanceData.shift();
        }

        // Check thresholds and emit warnings
        this.checkThresholds(entry);

        // Report to analytics if available
        this.reportToAnalytics(entry);

        console.log(`⏱️ ${entry.name}: ${entry.duration.toFixed(2)}ms`);
    }

    /**
     * Record navigation timing
     */
    recordNavigationTiming(entry) {
        const metrics = {
            name: 'page_load',
            duration: entry.loadEventEnd - entry.navigationStart,
            domContentLoaded: entry.domContentLoadedEventEnd - entry.navigationStart,
            firstPaint: entry.responseEnd - entry.requestStart,
            type: 'navigation',
            timestamp: Date.now()
        };

        this.recordMetric(metrics);
    }

    /**
     * Record resource timing
     */
    recordResourceTiming(entry) {
        // Only track important resources
        if (this.isImportantResource(entry.name)) {
            const metrics = {
                name: `resource_${this.getResourceType(entry.name)}`,
                duration: entry.responseEnd - entry.requestStart,
                size: entry.transferSize || 0,
                type: 'resource',
                url: entry.name,
                timestamp: Date.now()
            };

            this.recordMetric(metrics);
        }
    }

    /**
     * Record long tasks
     */
    recordLongTask(entry) {
        const metrics = {
            name: 'long_task',
            duration: entry.duration,
            startTime: entry.startTime,
            type: 'longtask',
            timestamp: Date.now()
        };

        this.recordMetric(metrics);

        // Emit warning for long tasks
        eventBus.emit(EVENTS.UI_ERROR, {
            type: 'performance',
            message: `Long task detected: ${entry.duration.toFixed(2)}ms`,
            severity: 'warning'
        });
    }

    /**
     * Check performance thresholds
     */
    checkThresholds(entry) {
        if (entry.duration > this.thresholds.critical) {
            eventBus.emit(EVENTS.UI_ERROR, {
                type: 'performance',
                message: `Critical performance issue: ${entry.name} took ${entry.duration.toFixed(2)}ms`,
                severity: 'critical'
            });
        } else if (entry.duration > this.thresholds.slow) {
            console.warn(`Slow operation detected: ${entry.name} took ${entry.duration.toFixed(2)}ms`);
        }
    }

    /**
     * Report to analytics service
     */
    reportToAnalytics(entry) {
        // Google Analytics 4
        if (window.gtag) {
            gtag('event', 'timing_complete', {
                name: entry.name,
                value: Math.round(entry.duration),
                custom_parameter_1: entry.type
            });
        }

        // Custom analytics endpoint
        if (this.shouldReportToServer(entry)) {
            this.sendToServer(entry);
        }
    }

    /**
     * Send performance data to server
     */
    async sendToServer(entry) {
        try {
            await fetch('/api/performance', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    ...entry,
                    userAgent: navigator.userAgent,
                    url: window.location.href
                })
            });
        } catch (error) {
            console.warn('Failed to send performance data:', error);
        }
    }

    /**
     * Get performance statistics
     */
    getStats() {
        const stats = {
            totalMetrics: this.performanceData.length,
            averageDuration: 0,
            slowOperations: 0,
            criticalOperations: 0,
            byType: {}
        };

        if (this.performanceData.length === 0) return stats;

        // Calculate averages and counts
        let totalDuration = 0;
        this.performanceData.forEach(entry => {
            totalDuration += entry.duration;

            if (entry.duration > this.thresholds.critical) {
                stats.criticalOperations++;
            } else if (entry.duration > this.thresholds.slow) {
                stats.slowOperations++;
            }

            // Group by type
            if (!stats.byType[entry.type]) {
                stats.byType[entry.type] = { count: 0, totalDuration: 0 };
            }
            stats.byType[entry.type].count++;
            stats.byType[entry.type].totalDuration += entry.duration;
        });

        stats.averageDuration = totalDuration / this.performanceData.length;

        // Calculate averages by type
        Object.keys(stats.byType).forEach(type => {
            stats.byType[type].average = stats.byType[type].totalDuration / stats.byType[type].count;
        });

        return stats;
    }

    /**
     * Get recent performance data
     */
    getRecentData(limit = 10) {
        return this.performanceData.slice(-limit);
    }

    /**
     * Clear performance data
     */
    clearData() {
        this.performanceData = [];
        this.metrics.clear();
    }

    /**
     * Enable/disable monitoring
     */
    setEnabled(enabled) {
        this.isEnabled = enabled;
    }

    /**
     * Set performance thresholds
     */
    setThresholds(slow, critical) {
        this.thresholds.slow = slow;
        this.thresholds.critical = critical;
    }

    /**
     * Helper methods
     */
    isImportantResource(url) {
        return url.includes('.js') || url.includes('.css') || url.includes('/api/');
    }

    getResourceType(url) {
        if (url.includes('.js')) return 'javascript';
        if (url.includes('.css')) return 'stylesheet';
        if (url.includes('/api/')) return 'api';
        return 'other';
    }

    shouldReportToServer(entry) {
        // Only report critical issues or important operations
        return entry.duration > this.thresholds.critical || 
               ['route_calculation', 'map_initialization', 'chart_render'].includes(entry.name);
    }
}

// Create singleton instance
export const performanceMonitor = new PerformanceMonitor();

// Convenience functions
export const startTimer = (name, metadata) => performanceMonitor.startTimer(name, metadata);
export const endTimer = (name) => performanceMonitor.endTimer(name);
export const recordMetric = (entry) => performanceMonitor.recordMetric(entry);

// Make it globally available
window.performanceMonitor = performanceMonitor;
window.startTimer = startTimer;
window.endTimer = endTimer;