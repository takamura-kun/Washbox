// performanceOptimizer.js - Utilities to prevent performance violations

import { taskScheduler } from './taskScheduler.js';

class PerformanceOptimizer {
    constructor() {
        this.taskQueue = [];
        this.isProcessing = false;
        this.frameDeadline = 16; // ~60fps
    }

    /**
     * Batch DOM read operations to prevent forced reflows
     */
    batchRead(readOperations) {
        const results = [];
        
        // Perform all reads in one frame
        requestAnimationFrame(() => {
            readOperations.forEach((readFn, index) => {
                results[index] = readFn();
            });
        });
        
        return results;
    }

    /**
     * Batch DOM write operations to prevent forced reflows
     */
    batchWrite(writeOperations) {
        requestAnimationFrame(() => {
            writeOperations.forEach(writeFn => writeFn());
        });
    }

    /**
     * Schedule a task to run when the browser is idle
     */
    scheduleIdleTask(task, options = {}) {
        const { timeout = 1000, priority = 'normal' } = options;
        
        if (window.requestIdleCallback) {
            requestIdleCallback((deadline) => {
                if (deadline.timeRemaining() > 0 || deadline.didTimeout) {
                    task();
                }
            }, { timeout });
        } else {
            // Fallback for browsers without requestIdleCallback
            const delay = priority === 'high' ? 0 : priority === 'low' ? 100 : 16;
            taskScheduler.schedule(task, delay);
        }
    }

    /**
     * Break up long-running tasks into smaller chunks
     */
    async processInChunks(items, processor, chunkSize = 10) {
        const chunks = [];
        for (let i = 0; i < items.length; i += chunkSize) {
            chunks.push(items.slice(i, i + chunkSize));
        }

        for (const chunk of chunks) {
            await new Promise(resolve => {
                requestAnimationFrame(() => {
                    chunk.forEach(processor);
                    resolve();
                });
            });
        }
    }

    /**
     * Debounce function calls to prevent excessive execution
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = taskScheduler.schedule(later, wait);
        };
    }

    /**
     * Throttle function calls to limit execution frequency
     */
    throttle(func, limit) {
        let inThrottle;
        return function executedFunction(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                taskScheduler.schedule(() => inThrottle = false, limit);
            }
        };
    }

    /**
     * Optimize animations to prevent jank
     */
    optimizeAnimation(element, properties, duration = 300) {
        return new Promise(resolve => {
            const startTime = performance.now();
            const startValues = {};
            const endValues = {};

            // Read all start values at once
            Object.keys(properties).forEach(prop => {
                startValues[prop] = parseFloat(getComputedStyle(element)[prop]) || 0;
                endValues[prop] = properties[prop];
            });

            const animate = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Easing function (ease-out)
                const easeOut = 1 - Math.pow(1 - progress, 3);

                // Apply all changes in one frame
                requestAnimationFrame(() => {
                    Object.keys(properties).forEach(prop => {
                        const start = startValues[prop];
                        const end = endValues[prop];
                        const current = start + (end - start) * easeOut;
                        
                        if (prop === 'width' || prop === 'height') {
                            element.style[prop] = current + '%';
                        } else {
                            element.style[prop] = current;
                        }
                    });
                });

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    resolve();
                }
            };

            requestAnimationFrame(animate);
        });
    }

    /**
     * Monitor performance and warn about violations
     */
    monitorPerformance() {
        if (!window.PerformanceObserver) return;

        const observer = new PerformanceObserver((list) => {
            const entries = list.getEntries();
            entries.forEach(entry => {
                if (entry.entryType === 'longtask') {
                    console.warn(`⚠️ Long task detected: ${entry.duration}ms`, entry);
                }
                
                if (entry.entryType === 'layout-shift' && entry.value > 0.1) {
                    console.warn(`⚠️ Layout shift detected: ${entry.value}`, entry);
                }
            });
        });

        try {
            observer.observe({ entryTypes: ['longtask', 'layout-shift'] });
        } catch (e) {
            console.log('Performance monitoring not fully supported');
        }
    }

    /**
     * Optimize setTimeout/setInterval usage
     */
    optimizedTimeout(callback, delay) {
        if (delay < 16) {
            // Use requestAnimationFrame for very short delays
            return requestAnimationFrame(callback);
        } else {
            return taskScheduler.schedule(callback, delay);
        }
    }

    /**
     * Get performance recommendations
     */
    getPerformanceRecommendations() {
        const recommendations = [];
        
        // Check for common performance issues
        const longTasks = performance.getEntriesByType('longtask');
        if (longTasks.length > 0) {
            recommendations.push('Consider breaking up long-running JavaScript tasks');
        }
        
        const layoutShifts = performance.getEntriesByType('layout-shift');
        if (layoutShifts.some(shift => shift.value > 0.1)) {
            recommendations.push('Reduce layout shifts by reserving space for dynamic content');
        }
        
        return recommendations;
    }
}

// Create singleton instance
export const performanceOptimizer = new PerformanceOptimizer();

// Initialize performance monitoring
performanceOptimizer.monitorPerformance();

// Make it globally available
window.performanceOptimizer = performanceOptimizer;