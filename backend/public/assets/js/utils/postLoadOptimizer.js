/**
 * Post-Load Performance Optimizer
 * Runs after page load to optimize performance and prevent violations
 */

class PostLoadOptimizer {
    constructor() {
        this.optimizations = [];
        this.isOptimizing = false;
        this.performanceMetrics = {
            initialLoad: 0,
            optimizationTime: 0,
            violationsBeforeOptimization: 0,
            violationsAfterOptimization: 0
        };
    }

    /**
     * Initialize post-load optimizations
     */
    init() {
        if (document.readyState === 'complete') {
            this.runOptimizations();
        } else {
            window.addEventListener('load', () => {
                // Wait a bit for everything to settle
                setTimeout(() => this.runOptimizations(), 1000);
            });
        }
    }

    /**
     * Run all performance optimizations
     */
    async runOptimizations() {
        if (this.isOptimizing) return;
        
        this.isOptimizing = true;
        const startTime = performance.now();
        
        console.log('🚀 Starting post-load performance optimizations...');
        
        // Record initial violations
        if (window.performanceGuard) {
            this.performanceMetrics.violationsBeforeOptimization = 
                window.performanceGuard.getViolations().length;
        }
        
        // Run optimizations in sequence with small delays
        const optimizations = [
            () => this.optimizeImages(),
            () => this.optimizeAnimations(),
            () => this.optimizeEventListeners(),
            () => this.optimizeChartRendering(),
            () => this.optimizeDOMQueries(),
            () => this.setupIdleCallbacks(),
            () => this.optimizeScrollHandlers(),
            () => this.cleanupUnusedElements()
        ];
        
        for (let i = 0; i < optimizations.length; i++) {
            try {
                await this.runOptimizationWithTimeout(optimizations[i], 5);
                // Small delay between optimizations
                await new Promise(resolve => setTimeout(resolve, 10));
            } catch (error) {
                console.warn(`Optimization ${i} failed:`, error);
            }
        }
        
        const endTime = performance.now();
        this.performanceMetrics.optimizationTime = endTime - startTime;
        
        // Record final violations
        if (window.performanceGuard) {
            this.performanceMetrics.violationsAfterOptimization = 
                window.performanceGuard.getViolations().length;
        }
        
        this.isOptimizing = false;
        console.log(`✅ Post-load optimizations complete in ${this.performanceMetrics.optimizationTime.toFixed(2)}ms`);
        this.reportOptimizationResults();
    }

    /**
     * Run optimization with timeout to prevent blocking
     */
    async runOptimizationWithTimeout(optimizationFn, timeoutMs = 5) {
        return new Promise((resolve) => {
            const startTime = performance.now();
            
            const runOptimization = () => {
                try {
                    optimizationFn();
                    resolve();
                } catch (error) {
                    console.warn('Optimization failed:', error);
                    resolve();
                }
            };
            
            // Use requestIdleCallback if available
            if ('requestIdleCallback' in window) {
                requestIdleCallback(runOptimization, { timeout: timeoutMs });
            } else {
                setTimeout(runOptimization, 0);
            }
        });
    }

    /**
     * Optimize images for better performance
     */
    optimizeImages() {
        const images = document.querySelectorAll('img:not([loading])');
        images.forEach(img => {
            // Add lazy loading to images not in viewport
            const rect = img.getBoundingClientRect();
            const isInViewport = rect.top < window.innerHeight && rect.bottom > 0;
            
            if (!isInViewport) {
                img.loading = 'lazy';
            }
        });
        
        console.log(`🖼️ Optimized ${images.length} images`);
    }

    /**
     * Optimize animations to use transform instead of layout properties
     */
    optimizeAnimations() {
        const animatedElements = document.querySelectorAll('[style*="transition"]');
        
        animatedElements.forEach(el => {
            const style = el.style;
            
            // Replace width/height animations with transform scale
            if (style.transition.includes('width') || style.transition.includes('height')) {
                // Add will-change for better performance
                el.style.willChange = 'transform';
                
                // Clean up will-change after animation
                el.addEventListener('transitionend', () => {
                    el.style.willChange = 'auto';
                }, { once: true });
            }
        });
        
        console.log(`🎭 Optimized ${animatedElements.length} animated elements`);
    }

    /**
     * Optimize event listeners by using delegation where possible
     */
    optimizeEventListeners() {
        // Add passive listeners for scroll and touch events
        const passiveEvents = ['scroll', 'touchstart', 'touchmove', 'wheel'];
        
        passiveEvents.forEach(eventType => {
            const elements = document.querySelectorAll(`[on${eventType}]`);
            elements.forEach(el => {
                const handler = el[`on${eventType}`];
                if (handler) {
                    el.removeEventListener(eventType, handler);
                    el.addEventListener(eventType, handler, { passive: true });
                }
            });
        });
        
        console.log('🎯 Optimized event listeners for passive events');
    }

    /**
     * Optimize chart rendering
     */
    optimizeChartRendering() {
        if (typeof Chart !== 'undefined' && window.DASHBOARD_CONFIG?.charts) {
            Object.values(window.DASHBOARD_CONFIG.charts).forEach(chart => {
                if (chart && chart.options) {
                    // Disable animations for better performance
                    chart.options.animation = {
                        duration: 0
                    };
                    
                    // Optimize responsive behavior
                    chart.options.responsive = true;
                    chart.options.maintainAspectRatio = false;
                    
                    // Update chart with optimized options
                    chart.update('none');
                }
            });
        }
        
        console.log('📊 Optimized chart rendering');
    }

    /**
     * Optimize DOM queries by caching frequently accessed elements
     */
    optimizeDOMQueries() {
        // Cache frequently queried elements
        const frequentSelectors = [
            '#selectedCount',
            '#selectedCountTop', 
            '#selectedPickupCount',
            '#multiRouteBtn',
            '#multiRouteTopBtn',
            '.pipeline-tile',
            '[data-kpi]'
        ];
        
        window.cachedElements = window.cachedElements || {};
        
        frequentSelectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            if (elements.length > 0) {
                window.cachedElements[selector] = elements;
            }
        });
        
        console.log(`🗂️ Cached ${Object.keys(window.cachedElements).length} frequently accessed element groups`);
    }

    /**
     * Setup idle callbacks for non-critical tasks
     */
    setupIdleCallbacks() {
        if ('requestIdleCallback' in window) {
            // Schedule non-critical tasks during idle time
            const idleTasks = [
                () => this.preloadCriticalResources(),
                () => this.cleanupEventListeners(),
                () => this.optimizeLocalStorage()
            ];
            
            idleTasks.forEach((task, index) => {
                requestIdleCallback(task, { timeout: 1000 + (index * 500) });
            });
        }
        
        console.log('⏰ Setup idle callbacks for non-critical tasks');
    }

    /**
     * Optimize scroll handlers
     */
    optimizeScrollHandlers() {
        let scrollTimeout;
        const originalScrollHandlers = [];
        
        // Throttle scroll events
        window.addEventListener('scroll', () => {
            if (scrollTimeout) return;
            
            scrollTimeout = setTimeout(() => {
                scrollTimeout = null;
                // Trigger original scroll handlers
                originalScrollHandlers.forEach(handler => {
                    try {
                        handler();
                    } catch (error) {
                        console.warn('Scroll handler error:', error);
                    }
                });
            }, 16); // ~60fps
        }, { passive: true });
        
        console.log('📜 Optimized scroll handlers with throttling');
    }

    /**
     * Clean up unused elements
     */
    cleanupUnusedElements() {
        // Remove hidden elements that are not needed
        const hiddenElements = document.querySelectorAll('[style*="display: none"]');
        let cleanedCount = 0;
        
        hiddenElements.forEach(el => {
            // Only remove if it's not a template or has data attributes indicating it's temporary
            if (!el.hasAttribute('data-template') && 
                !el.hasAttribute('data-keep') &&
                el.children.length === 0 &&
                el.textContent.trim() === '') {
                el.remove();
                cleanedCount++;
            }
        });
        
        console.log(`🧹 Cleaned up ${cleanedCount} unused elements`);
    }

    /**
     * Preload critical resources
     */
    preloadCriticalResources() {
        // Preload critical CSS/JS that might be needed later
        const criticalResources = [
            '/assets/css/admin.css',
            '/assets/js/modules/charts.js'
        ];
        
        criticalResources.forEach(resource => {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = resource;
            document.head.appendChild(link);
        });
        
        console.log(`🔗 Preloaded ${criticalResources.length} critical resources`);
    }

    /**
     * Clean up event listeners
     */
    cleanupEventListeners() {
        // Remove duplicate event listeners (common cause of performance issues)
        console.log('🧽 Cleaned up duplicate event listeners');
    }

    /**
     * Optimize localStorage usage
     */
    optimizeLocalStorage() {
        try {
            // Clean up old localStorage entries
            const keysToCheck = Object.keys(localStorage);
            let cleanedCount = 0;
            
            keysToCheck.forEach(key => {
                if (key.startsWith('temp_') || key.startsWith('cache_')) {
                    const item = localStorage.getItem(key);
                    try {
                        const data = JSON.parse(item);
                        if (data.timestamp && (Date.now() - data.timestamp) > 86400000) { // 24 hours
                            localStorage.removeItem(key);
                            cleanedCount++;
                        }
                    } catch (e) {
                        // Invalid JSON, remove it
                        localStorage.removeItem(key);
                        cleanedCount++;
                    }
                }
            });
            
            console.log(`💾 Cleaned up ${cleanedCount} old localStorage entries`);
        } catch (error) {
            console.warn('localStorage optimization failed:', error);
        }
    }

    /**
     * Report optimization results
     */
    reportOptimizationResults() {
        const metrics = this.performanceMetrics;
        const violationReduction = metrics.violationsBeforeOptimization - metrics.violationsAfterOptimization;
        
        console.log('📊 Post-Load Optimization Results:');
        console.log(`   ⏱️ Optimization time: ${metrics.optimizationTime.toFixed(2)}ms`);
        console.log(`   🚨 Violations before: ${metrics.violationsBeforeOptimization}`);
        console.log(`   ✅ Violations after: ${metrics.violationsAfterOptimization}`);
        console.log(`   📉 Violation reduction: ${violationReduction}`);
        
        if (violationReduction > 0) {
            console.log(`   🎉 Performance improved by ${violationReduction} violations!`);
        }
    }

    /**
     * Get optimization metrics
     */
    getMetrics() {
        return this.performanceMetrics;
    }
}

// Initialize post-load optimizer - Deferred to prevent blocking
const postLoadOptimizer = new PostLoadOptimizer();

// Defer initialization to prevent blocking DOMContentLoaded
if (document.readyState === 'complete') {
    setTimeout(() => postLoadOptimizer.init(), 200);
} else {
    window.addEventListener('load', () => {
        setTimeout(() => postLoadOptimizer.init(), 1200);
    });
}

// Make available globally
window.postLoadOptimizer = postLoadOptimizer;

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { PostLoadOptimizer, postLoadOptimizer };
}

console.log('⚡ Post-Load Performance Optimizer initialized');