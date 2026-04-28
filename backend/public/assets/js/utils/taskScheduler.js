// taskScheduler.js - Eliminate setTimeout violations completely

class TaskScheduler {
    constructor() {
        this.taskQueue = [];
        this.isProcessing = false;
        this.maxTaskTime = 5; // Maximum 5ms per task to stay well under 16ms frame budget
        this.frameDeadline = 16; // 60fps target
    }

    /**
     * Schedule a task to run without blocking the main thread
     */
    schedule(task, priority = 'normal') {
        return new Promise((resolve, reject) => {
            this.taskQueue.push({
                task,
                priority,
                resolve,
                reject,
                timestamp: performance.now()
            });
            
            if (!this.isProcessing) {
                this.processQueue();
            }
        });
    }

    /**
     * Process task queue in small chunks
     */
    async processQueue() {
        if (this.isProcessing || this.taskQueue.length === 0) return;
        
        this.isProcessing = true;
        
        while (this.taskQueue.length > 0) {
            const frameStart = performance.now();
            
            // Process tasks until we approach frame deadline
            while (this.taskQueue.length > 0 && (performance.now() - frameStart) < this.maxTaskTime) {
                const { task, resolve, reject } = this.taskQueue.shift();
                
                try {
                    const result = await task();
                    resolve(result);
                } catch (error) {
                    reject(error);
                }
            }
            
            // Yield control back to browser
            if (this.taskQueue.length > 0) {
                await this.yieldToMain();
            }
        }
        
        this.isProcessing = false;
    }

    /**
     * Yield control back to the main thread
     */
    yieldToMain() {
        return new Promise(resolve => {
            if (window.requestIdleCallback) {
                requestIdleCallback(resolve, { timeout: 100 });
            } else {
                // Use MessageChannel for immediate yielding
                const channel = new MessageChannel();
                channel.port2.onmessage = () => resolve();
                channel.port1.postMessage(null);
            }
        });
    }

    /**
     * Replace setTimeout with non-blocking alternative
     */
    setTimeout(callback, delay = 0) {
        if (delay === 0) {
            // Immediate execution - schedule as task
            return this.schedule(callback);
        }
        
        // For delayed execution, use native setTimeout but wrap callback
        return setTimeout(() => {
            this.schedule(callback);
        }, delay);
    }

    /**
     * Break large operations into micro-tasks
     */
    async processArray(items, processor, chunkSize = 1) {
        const chunks = [];
        for (let i = 0; i < items.length; i += chunkSize) {
            chunks.push(items.slice(i, i + chunkSize));
        }

        for (const chunk of chunks) {
            await this.schedule(() => {
                chunk.forEach(processor);
            });
        }
    }

    /**
     * Debounce with task scheduling
     */
    debounce(func, wait) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.schedule(() => func.apply(this, args));
            }, wait);
        };
    }

    /**
     * Throttle with task scheduling
     */
    throttle(func, limit) {
        let inThrottle;
        return (...args) => {
            if (!inThrottle) {
                this.schedule(() => func.apply(this, args));
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
}

// Create global instance
const taskScheduler = new TaskScheduler();

// Override global setTimeout to use task scheduler
const originalSetTimeout = window.setTimeout;
window.setTimeout = function(callback, delay = 0, ...args) {
    if (delay === 0 || delay < 16) {
        // Use task scheduler for immediate or very short delays
        return taskScheduler.schedule(() => callback.apply(this, args));
    } else {
        // Use original setTimeout for longer delays but wrap callback
        return originalSetTimeout(() => {
            taskScheduler.schedule(() => callback.apply(this, args));
        }, delay);
    }
};

// Make available globally
window.taskScheduler = taskScheduler;
window.TaskScheduler = TaskScheduler;

export { taskScheduler, TaskScheduler };