// domOptimizer.js - DOM operation optimization utilities

class DOMOptimizer {
    constructor() {
        this.readQueue = [];
        this.writeQueue = [];
        this.isScheduled = false;
    }

    /**
     * Schedule DOM reads to be batched together
     */
    scheduleRead(callback) {
        this.readQueue.push(callback);
        this.scheduleFlush();
    }

    /**
     * Schedule DOM writes to be batched together
     */
    scheduleWrite(callback) {
        this.writeQueue.push(callback);
        this.scheduleFlush();
    }

    /**
     * Schedule the flush of queued operations
     */
    scheduleFlush() {
        if (this.isScheduled) return;
        
        this.isScheduled = true;
        requestAnimationFrame(() => {
            this.flush();
        });
    }

    /**
     * Execute all queued operations in the correct order
     */
    flush() {
        // Execute all reads first
        while (this.readQueue.length > 0) {
            const readCallback = this.readQueue.shift();
            try {
                readCallback();
            } catch (error) {
                console.error('DOM read error:', error);
            }
        }

        // Then execute all writes
        while (this.writeQueue.length > 0) {
            const writeCallback = this.writeQueue.shift();
            try {
                writeCallback();
            } catch (error) {
                console.error('DOM write error:', error);
            }
        }

        this.isScheduled = false;
    }

    /**
     * Batch multiple DOM operations
     */
    batch(operations) {
        requestAnimationFrame(() => {
            operations.forEach(op => {
                try {
                    op();
                } catch (error) {
                    console.error('Batched operation error:', error);
                }
            });
        });
    }

    /**
     * Optimize element style updates
     */
    updateStyles(element, styles) {
        if (!element) return;
        
        this.scheduleWrite(() => {
            // Use cssText for better performance
            const cssText = Object.entries(styles)
                .map(([prop, value]) => `${prop.replace(/([A-Z])/g, '-$1').toLowerCase()}: ${value}`)
                .join('; ');
            element.style.cssText += '; ' + cssText;
        });
    }

    /**
     * Optimize multiple element updates
     */
    updateMultipleElements(elements, updateFn) {
        if (!elements || elements.length === 0) return;

        this.scheduleWrite(() => {
            elements.forEach(element => {
                try {
                    updateFn(element);
                } catch (error) {
                    console.error('Element update error:', error);
                }
            });
        });
    }

    /**
     * Measure element dimensions without causing reflow
     */
    measureElement(element, callback) {
        this.scheduleRead(() => {
            if (!element) return;
            
            const rect = element.getBoundingClientRect();
            const computedStyle = window.getComputedStyle(element);
            
            callback({
                width: rect.width,
                height: rect.height,
                top: rect.top,
                left: rect.left,
                computedStyle: computedStyle
            });
        });
    }
}

// Create singleton instance
export const domOptimizer = new DOMOptimizer();

// Make it globally available
window.domOptimizer = domOptimizer;

// Utility functions for common operations
export const optimizedDOM = {
    /**
     * Update text content without causing reflow
     */
    setText(element, text) {
        if (!element) return;
        domOptimizer.scheduleWrite(() => {
            element.textContent = text;
        });
    },

    /**
     * Update innerHTML without causing reflow
     */
    setHTML(element, html) {
        if (!element) return;
        domOptimizer.scheduleWrite(() => {
            element.innerHTML = html;
        });
    },

    /**
     * Show/hide elements efficiently
     */
    setVisibility(element, visible) {
        if (!element) return;
        domOptimizer.scheduleWrite(() => {
            element.style.display = visible ? '' : 'none';
        });
    },

    /**
     * Add/remove classes efficiently
     */
    toggleClass(element, className, add) {
        if (!element) return;
        domOptimizer.scheduleWrite(() => {
            if (add) {
                element.classList.add(className);
            } else {
                element.classList.remove(className);
            }
        });
    },

    /**
     * Update multiple elements with the same operation
     */
    updateAll(selector, updateFn) {
        domOptimizer.scheduleRead(() => {
            const elements = document.querySelectorAll(selector);
            domOptimizer.updateMultipleElements(elements, updateFn);
        });
    }
};

console.log('✅ DOM Optimizer initialized');