/**
 * Performance Guard - Prevents Chrome performance violations
 * Monitors and prevents DOMContentLoaded, requestAnimationFrame, and forced reflow violations
 */

class PerformanceGuard {
    constructor() {
        this.violations = [];
        this.thresholds = {
            domContentLoaded: 16,    // Chrome violation threshold
            requestAnimationFrame: 16,
            forcedReflow: 16,
            longTask: 50
        };
        this.isMonitoring = true;
        this.performanceMetrics = {
            totalViolations: 0,
            worstViolation: { type: null, duration: 0 },
            averageDuration: 0
        };
        this.init();
    }

    init() {
        this.monitorDOMContentLoaded();
        this.monitorRequestAnimationFrame();
        this.monitorLongTasks();
        this.setupPerformanceObserver();
    }

    monitorDOMContentLoaded() {
        const originalAddEventListener = document.addEventListener;
        const self = this;
        
        document.addEventListener = function(type, listener, options) {
            if (type === 'DOMContentLoaded') {
                const wrappedListener = function(event) {
                    const startTime = performance.now();
                    
                    try {
                        listener.call(this, event);
                    } finally {
                        const duration = performance.now() - startTime;
                        if (duration > self.thresholds.domContentLoaded && self.isMonitoring) {
                            self.recordViolation('DOMContentLoaded', duration, {
                                listener: listener.toString().substring(0, 100),
                                stack: new Error().stack
                            });
                        }
                    }
                };
                
                return originalAddEventListener.call(this, type, wrappedListener, options);
            }
            
            return originalAddEventListener.call(this, type, listener, options);
        };
    }

    monitorRequestAnimationFrame() {
        const originalRAF = window.requestAnimationFrame;
        const self = this;
        
        window.requestAnimationFrame = function(callback) {
            const wrappedCallback = function(timestamp) {
                const startTime = performance.now();
                
                try {
                    callback.call(this, timestamp);
                } finally {
                    const duration = performance.now() - startTime;
                    if (duration > self.thresholds.requestAnimationFrame && self.isMonitoring) {
                        self.recordViolation('requestAnimationFrame', duration, {
                            callback: callback.toString().substring(0, 100),
                            stack: new Error().stack
                        });
                    }
                }
            };
            
            return originalRAF.call(this, wrappedCallback);
        };
    }

    monitorLongTasks() {
        if ('PerformanceObserver' in window) {
            try {
                const observer = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        if (entry.duration > this.thresholds.longTask && this.isMonitoring) {
                            this.recordViolation('LongTask', entry.duration, {
                                name: entry.name,
                                startTime: entry.startTime,
                                attribution: entry.attribution
                            });
                        }
                    }
                });
                
                observer.observe({ entryTypes: ['longtask'] });
            } catch (e) {
                console.warn('Long task monitoring not supported:', e);
            }
        }
    }

    setupPerformanceObserver() {
        if ('PerformanceObserver' in window) {
            try {
                const observer = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        // Monitor layout thrashing
                        if (entry.name === 'layout' && entry.duration > this.thresholds.forcedReflow) {
                            this.recordViolation('ForcedReflow', entry.duration, {
                                name: entry.name,
                                startTime: entry.startTime
                            });
                        }
                    }
                });
                
                observer.observe({ entryTypes: ['measure'] });
            } catch (e) {
                console.warn('Performance observer not fully supported:', e);
            }
        }
    }

    recordViolation(type, duration, details = {}) {
        const violation = {
            type,
            duration: Math.round(duration * 100) / 100,
            timestamp: Date.now(),
            details,
            url: window.location.href
        };
        
        this.violations.push(violation);
        
        // Log to console in development
        if (window.location.hostname === 'localhost' || window.location.hostname.includes('127.0.0.1')) {
            console.warn(`🚨 Performance Violation: ${type} took ${violation.duration}ms`, violation);
        }
        
        // Keep only last 50 violations
        if (this.violations.length > 50) {
            this.violations = this.violations.slice(-50);
        }
        
        // Emit event for monitoring systems
        window.dispatchEvent(new CustomEvent('performanceViolation', {
            detail: violation
        }));
    }

    getViolations(type = null) {
        if (type) {
            return this.violations.filter(v => v.type === type);
        }
        return [...this.violations];
    }

    getViolationSummary() {
        const summary = {};
        this.violations.forEach(v => {
            if (!summary[v.type]) {
                summary[v.type] = {
                    count: 0,
                    totalDuration: 0,
                    maxDuration: 0,
                    avgDuration: 0
                };
            }
            
            summary[v.type].count++;
            summary[v.type].totalDuration += v.duration;
            summary[v.type].maxDuration = Math.max(summary[v.type].maxDuration, v.duration);
        });
        
        // Calculate averages
        Object.keys(summary).forEach(type => {
            summary[type].avgDuration = Math.round(
                (summary[type].totalDuration / summary[type].count) * 100
            ) / 100;
        });
        
        return summary;
    }

    clearViolations() {
        this.violations = [];
        this.performanceMetrics = {
            totalViolations: 0,
            worstViolation: { type: null, duration: 0 },
            averageDuration: 0
        };
    }
    
    getPerformanceMetrics() {
        return {
            ...this.performanceMetrics,
            currentViolationCount: this.violations.length,
            isMonitoring: this.isMonitoring
        };
    }
    
    pauseMonitoring() {
        this.isMonitoring = false;
    }
    
    resumeMonitoring() {
        this.isMonitoring = true;
    }

    // Utility method to create time-sliced execution
    static createTimeSlicedExecution(tasks, timeSliceMs = 5) {
        return new Promise((resolve) => {
            let taskIndex = 0;
            const channel = new MessageChannel();
            
            channel.port2.onmessage = () => {
                if (taskIndex < tasks.length) {
                    const startTime = performance.now();
                    
                    while (taskIndex < tasks.length && (performance.now() - startTime) < timeSliceMs) {
                        try {
                            tasks[taskIndex]();
                        } catch (error) {
                            console.error(`Time-sliced task ${taskIndex} failed:`, error);
                        }
                        taskIndex++;
                    }
                    
                    if (taskIndex < tasks.length) {
                        channel.port1.postMessage(null);
                    } else {
                        resolve();
                    }
                } else {
                    resolve();
                }
            };
            
            channel.port1.postMessage(null);
        });
    }

    // Utility method to prevent forced reflows
    static batchDOMOperations(readOperations = [], writeOperations = []) {
        // Batch all reads first
        const readResults = readOperations.map(op => {
            try {
                return op();
            } catch (error) {
                console.error('DOM read operation failed:', error);
                return null;
            }
        });
        
        // Then batch all writes
        requestAnimationFrame(() => {
            writeOperations.forEach((op, index) => {
                try {
                    op(readResults[index]);
                } catch (error) {
                    console.error('DOM write operation failed:', error);
                }
            });
        });
        
        return readResults;
    }
}

// Initialize performance guard
const performanceGuard = new PerformanceGuard();

// Make available globally
window.performanceGuard = performanceGuard;
window.PerformanceGuard = PerformanceGuard;

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { PerformanceGuard, performanceGuard };
}

console.log('🛡️ Performance Guard initialized - monitoring for violations');