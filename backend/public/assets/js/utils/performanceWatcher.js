// performanceWatcher.js - Monitor and prevent performance violations

class PerformanceWatcher {
    constructor() {
        this.longTaskThreshold = 50;
        this.timeoutWarnings = 0;
        this.init();
    }

    init() {
        this.wrapSetTimeout();
        this.wrapSetInterval();
        this.monitorLongTasks();
    }

    wrapSetTimeout() {
        const originalSetTimeout = window.setTimeout;
        const self = this;
        
        window.setTimeout = function(callback, delay, ...args) {
            const wrappedCallback = function() {
                const start = performance.now();
                const result = callback.apply(this, arguments);
                const duration = performance.now() - start;
                
                if (duration > self.longTaskThreshold) {
                    self.timeoutWarnings++;
                    console.warn(`Long setTimeout handler: ${duration.toFixed(1)}ms`);
                    
                    if (duration > 100) {
                        console.warn('Consider breaking this task into smaller chunks');
                    }
                }
                
                return result;
            };
            
            return originalSetTimeout.call(this, wrappedCallback, delay, ...args);
        };
    }

    wrapSetInterval() {
        const originalSetInterval = window.setInterval;
        const self = this;
        
        window.setInterval = function(callback, delay, ...args) {
            const wrappedCallback = function() {
                const start = performance.now();
                const result = callback.apply(this, arguments);
                const duration = performance.now() - start;
                
                if (duration > self.longTaskThreshold) {
                    console.warn(`Long setInterval handler: ${duration.toFixed(1)}ms`);
                }
                
                return result;
            };
            
            return originalSetInterval.call(this, wrappedCallback, delay, ...args);
        };
    }

    monitorLongTasks() {
        if (!window.PerformanceObserver) return;
        
        try {
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach(entry => {
                    if (entry.entryType === 'longtask') {
                        console.warn(`Long task detected: ${entry.duration.toFixed(1)}ms`);
                        
                        if (entry.duration > 100) {
                            console.warn('Consider using requestIdleCallback or breaking task into chunks');
                        }
                    }
                });
            });
            
            observer.observe({ entryTypes: ['longtask'] });
        } catch (e) {
            console.log('Long task monitoring not supported');
        }
    }

    getStats() {
        return {
            timeoutWarnings: this.timeoutWarnings,
            threshold: this.longTaskThreshold
        };
    }

    static async processInChunks(items, processor, chunkSize = 10, delay = 0) {
        for (let i = 0; i < items.length; i += chunkSize) {
            const chunk = items.slice(i, i + chunkSize);
            
            chunk.forEach(processor);
            
            if (i + chunkSize < items.length) {
                await new Promise(resolve => {
                    if (delay > 0) {
                        setTimeout(resolve, delay);
                    } else {
                        requestAnimationFrame(resolve);
                    }
                });
            }
        }
    }

    static scheduleWhenIdle(task, timeout = 5000) {
        if (window.requestIdleCallback) {
            requestIdleCallback(task, { timeout });
        } else {
            setTimeout(task, 0);
        }
    }
}

const performanceWatcher = new PerformanceWatcher();

window.PerformanceWatcher = PerformanceWatcher;
window.performanceWatcher = performanceWatcher;
