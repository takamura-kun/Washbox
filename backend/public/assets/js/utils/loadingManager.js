// loadingManager.js - Progressive loading states and skeleton screens

import { eventBus, EVENTS } from '../utils/eventBus.js';
import { performanceMonitor } from '../utils/performanceMonitor.js';
import { taskScheduler } from '../utils/taskScheduler.js';

class LoadingManager {
    constructor() {
        this.loadingStates = new Map();
        this.skeletonTemplates = new Map();
        this.intersectionObserver = null;
        this.lazyElements = new Set();
        this.loadingQueue = [];
        this.maxConcurrentLoads = 3;
        this.currentLoads = 0;
        
        this.init();
    }

    init() {
        this.setupIntersectionObserver();
        this.setupSkeletonTemplates();
        this.setupProgressiveImageLoading();
        console.log('⏳ Progressive loading manager initialized');
    }

    /**
     * Setup intersection observer for lazy loading
     */
    setupIntersectionObserver() {
        if ('IntersectionObserver' in window) {
            this.intersectionObserver = new IntersectionObserver(
                (entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.loadElement(entry.target);
                            this.intersectionObserver.unobserve(entry.target);
                        }
                    });
                },
                {
                    rootMargin: '50px 0px',
                    threshold: 0.1
                }
            );
        }
    }

    /**
     * Setup skeleton screen templates
     */
    setupSkeletonTemplates() {
        // Card skeleton
        this.skeletonTemplates.set('card', `
            <div class="skeleton-card animate-pulse">
                <div class="skeleton-header">
                    <div class="skeleton-avatar"></div>
                    <div class="skeleton-text-lines">
                        <div class="skeleton-line skeleton-line-title"></div>
                        <div class="skeleton-line skeleton-line-subtitle"></div>
                    </div>
                </div>
                <div class="skeleton-content">
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line skeleton-line-short"></div>
                </div>
                <div class="skeleton-actions">
                    <div class="skeleton-button"></div>
                    <div class="skeleton-button"></div>
                </div>
            </div>
        `);

        // Table skeleton
        this.skeletonTemplates.set('table', `
            <div class="skeleton-table animate-pulse">
                <div class="skeleton-table-header">
                    <div class="skeleton-line skeleton-line-header"></div>
                    <div class="skeleton-line skeleton-line-header"></div>
                    <div class="skeleton-line skeleton-line-header"></div>
                    <div class="skeleton-line skeleton-line-header"></div>
                </div>
                <div class="skeleton-table-rows">
                    ${Array(5).fill().map(() => `
                        <div class="skeleton-table-row">
                            <div class="skeleton-line"></div>
                            <div class="skeleton-line"></div>
                            <div class="skeleton-line"></div>
                            <div class="skeleton-line"></div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `);

        // Chart skeleton
        this.skeletonTemplates.set('chart', `
            <div class="skeleton-chart animate-pulse">
                <div class="skeleton-chart-header">
                    <div class="skeleton-line skeleton-line-title"></div>
                </div>
                <div class="skeleton-chart-body">
                    <div class="skeleton-chart-bars">
                        ${Array(6).fill().map((_, i) => `
                            <div class="skeleton-bar" style="height: ${20 + Math.random() * 60}%"></div>
                        `).join('')}
                    </div>
                    <div class="skeleton-chart-legend">
                        <div class="skeleton-legend-item"></div>
                        <div class="skeleton-legend-item"></div>
                        <div class="skeleton-legend-item"></div>
                    </div>
                </div>
            </div>
        `);

        // Map skeleton
        this.skeletonTemplates.set('map', `
            <div class="skeleton-map animate-pulse">
                <div class="skeleton-map-controls">
                    <div class="skeleton-control"></div>
                    <div class="skeleton-control"></div>
                    <div class="skeleton-control"></div>
                </div>
                <div class="skeleton-map-content">
                    <div class="skeleton-map-markers">
                        ${Array(8).fill().map(() => `
                            <div class="skeleton-marker" style="
                                top: ${Math.random() * 80}%; 
                                left: ${Math.random() * 80}%;
                            "></div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `);

        // List skeleton
        this.skeletonTemplates.set('list', `
            <div class="skeleton-list animate-pulse">
                ${Array(8).fill().map(() => `
                    <div class="skeleton-list-item">
                        <div class="skeleton-avatar-small"></div>
                        <div class="skeleton-text-content">
                            <div class="skeleton-line skeleton-line-title"></div>
                            <div class="skeleton-line skeleton-line-subtitle"></div>
                        </div>
                        <div class="skeleton-badge"></div>
                    </div>
                `).join('')}
            </div>
        `);
    }

    /**
     * Show loading state for an element
     */
    showLoading(elementId, type = 'spinner', options = {}) {
        const element = typeof elementId === 'string' ? 
            document.getElementById(elementId) : elementId;
        
        if (!element) return;

        const loadingState = {
            element,
            type,
            options,
            originalContent: element.innerHTML,
            startTime: Date.now()
        };

        this.loadingStates.set(elementId, loadingState);

        // Apply loading state
        switch (type) {
            case 'skeleton':
                this.showSkeleton(element, options.skeletonType || 'card');
                break;
            case 'spinner':
                this.showSpinner(element, options);
                break;
            case 'progressive':
                this.showProgressiveLoader(element, options);
                break;
            case 'shimmer':
                this.showShimmer(element, options);
                break;
        }

        // Add loading class
        element.classList.add('loading-state');
        element.setAttribute('aria-busy', 'true');
        element.setAttribute('aria-live', 'polite');
    }

    /**
     * Hide loading state
     */
    hideLoading(elementId, newContent = null) {
        const loadingState = this.loadingStates.get(elementId);
        if (!loadingState) return;

        const { element, originalContent, startTime } = loadingState;
        const loadTime = Date.now() - startTime;

        // Ensure minimum loading time for smooth UX (300ms)
        const minLoadTime = 300;
        const remainingTime = Math.max(0, minLoadTime - loadTime);

        taskScheduler.schedule(() => {
            // Restore content
            if (newContent !== null) {
                element.innerHTML = newContent;
            } else {
                element.innerHTML = originalContent;
            }

            // Remove loading classes
            element.classList.remove('loading-state', 'skeleton-loading', 'shimmer-loading');
            element.removeAttribute('aria-busy');
            
            // Add loaded animation
            element.classList.add('content-loaded');
            taskScheduler.schedule(() => {
                element.classList.remove('content-loaded');
            }, 300);

            // Clean up
            this.loadingStates.delete(elementId);

            // Emit loaded event
            eventBus.emit(EVENTS.CONTENT_LOADED, { 
                elementId, 
                loadTime: loadTime + remainingTime 
            });

        }, remainingTime);
    }

    /**
     * Show skeleton screen
     */
    showSkeleton(element, skeletonType) {
        const template = this.skeletonTemplates.get(skeletonType);
        if (template) {
            element.innerHTML = template;
            element.classList.add('skeleton-loading');
        }
    }

    /**
     * Show spinner
     */
    showSpinner(element, options = {}) {
        const {
            size = 'normal',
            message = 'Loading...',
            overlay = false,
            color = 'primary'
        } = options;

        const sizeClass = size === 'small' ? 'spinner-sm' : 
                         size === 'large' ? 'spinner-lg' : '';

        const spinnerHTML = `
            <div class="loading-spinner-container ${overlay ? 'loading-overlay' : ''}">
                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                    <div class="spinner-border text-${color} ${sizeClass}" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    ${message ? `<div class="loading-message mt-2">${message}</div>` : ''}
                </div>
            </div>
        `;

        element.innerHTML = spinnerHTML;
    }

    /**
     * Show progressive loader with steps
     */
    showProgressiveLoader(element, options = {}) {
        const {
            steps = ['Loading...'],
            currentStep = 0,
            showProgress = true
        } = options;

        const progressHTML = `
            <div class="progressive-loader">
                <div class="progressive-loader-content">
                    <div class="progressive-loader-icon">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div class="progressive-loader-text">
                        <div class="current-step">${steps[currentStep] || 'Loading...'}</div>
                        ${showProgress ? `
                            <div class="progress mt-2">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: ${((currentStep + 1) / steps.length) * 100}%"
                                     aria-valuenow="${currentStep + 1}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="${steps.length}">
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
                <div class="progressive-loader-steps">
                    ${steps.map((step, index) => `
                        <div class="step-indicator ${index <= currentStep ? 'completed' : ''} ${index === currentStep ? 'active' : ''}">
                            <div class="step-number">${index + 1}</div>
                            <div class="step-label">${step}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;

        element.innerHTML = progressHTML;
    }

    /**
     * Update progressive loader step
     */
    updateProgressiveStep(elementId, stepIndex, stepText = null) {
        const element = typeof elementId === 'string' ? 
            document.getElementById(elementId) : elementId;
        
        if (!element) return;

        const currentStepEl = element.querySelector('.current-step');
        const progressBar = element.querySelector('.progress-bar');
        const stepIndicators = element.querySelectorAll('.step-indicator');
        const totalSteps = stepIndicators.length;

        if (currentStepEl && stepText) {
            currentStepEl.textContent = stepText;
        }

        if (progressBar) {
            const progress = ((stepIndex + 1) / totalSteps) * 100;
            progressBar.style.width = `${progress}%`;
            progressBar.setAttribute('aria-valuenow', stepIndex + 1);
        }

        // Update step indicators
        stepIndicators.forEach((indicator, index) => {
            indicator.classList.remove('active', 'completed');
            if (index < stepIndex) {
                indicator.classList.add('completed');
            } else if (index === stepIndex) {
                indicator.classList.add('active');
            }
        });
    }

    /**
     * Show shimmer effect
     */
    showShimmer(element, options = {}) {
        const { lines = 3, showAvatar = false } = options;
        
        const shimmerHTML = `
            <div class="shimmer-container">
                ${showAvatar ? '<div class="shimmer-avatar"></div>' : ''}
                <div class="shimmer-content">
                    ${Array(lines).fill().map((_, i) => `
                        <div class="shimmer-line ${i === lines - 1 ? 'shimmer-line-short' : ''}"></div>
                    `).join('')}
                </div>
            </div>
        `;

        element.innerHTML = shimmerHTML;
        element.classList.add('shimmer-loading');
    }

    /**
     * Setup progressive image loading
     */
    setupProgressiveImageLoading() {
        // Observe all images with data-src attribute
        document.addEventListener('DOMContentLoaded', () => {
            this.observeImages();
        });

        // Re-observe when new content is added
        eventBus.on(EVENTS.CONTENT_LOADED, () => {
            taskScheduler.schedule(() => this.observeImages(), 100);
        });
    }

    /**
     * Observe images for lazy loading
     */
    observeImages() {
        const lazyImages = document.querySelectorAll('img[data-src]:not([data-observed])');
        
        lazyImages.forEach(img => {
            img.setAttribute('data-observed', 'true');
            
            // Add placeholder
            if (!img.src) {
                img.src = this.generatePlaceholder(
                    img.dataset.width || 300, 
                    img.dataset.height || 200
                );
                img.classList.add('lazy-loading');
            }

            if (this.intersectionObserver) {
                this.intersectionObserver.observe(img);
            }
        });
    }

    /**
     * Load element (image or other lazy content)
     */
    async loadElement(element) {
        if (element.tagName === 'IMG') {
            await this.loadImage(element);
        } else if (element.dataset.lazyLoad) {
            await this.loadLazyContent(element);
        }
    }

    /**
     * Load image progressively
     */
    async loadImage(img) {
        return new Promise((resolve) => {
            const actualSrc = img.dataset.src;
            if (!actualSrc) {
                resolve();
                return;
            }

            // Create new image to preload
            const newImg = new Image();
            
            newImg.onload = () => {
                // Fade transition
                img.style.opacity = '0';
                img.style.transition = 'opacity 0.3s ease';
                
                taskScheduler.schedule(() => {
                    img.src = actualSrc;
                    img.style.opacity = '1';
                    img.classList.remove('lazy-loading');
                    img.classList.add('lazy-loaded');
                    resolve();
                }, 50);
            };

            newImg.onerror = () => {
                img.classList.add('lazy-error');
                resolve();
            };

            newImg.src = actualSrc;
        });
    }

    /**
     * Load lazy content
     */
    async loadLazyContent(element) {
        const loadFunction = element.dataset.lazyLoad;
        const loadParams = element.dataset.lazyParams ? 
            JSON.parse(element.dataset.lazyParams) : {};

        try {
            // Show loading state
            this.showLoading(element, 'skeleton', { 
                skeletonType: element.dataset.skeletonType || 'card' 
            });

            // Execute load function
            if (window[loadFunction]) {
                const content = await window[loadFunction](loadParams);
                this.hideLoading(element, content);
            }
        } catch (error) {
            console.error('Failed to load lazy content:', error);
            this.hideLoading(element, '<div class="alert alert-warning">Failed to load content</div>');
        }
    }

    /**
     * Generate placeholder image
     */
    generatePlaceholder(width, height, text = '') {
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        
        const ctx = canvas.getContext('2d');
        
        // Background
        ctx.fillStyle = '#f8f9fa';
        ctx.fillRect(0, 0, width, height);
        
        // Border
        ctx.strokeStyle = '#dee2e6';
        ctx.lineWidth = 1;
        ctx.strokeRect(0, 0, width, height);
        
        // Text
        if (text) {
            ctx.fillStyle = '#6c757d';
            ctx.font = '14px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(text, width / 2, height / 2);
        }
        
        return canvas.toDataURL();
    }

    /**
     * Batch load multiple elements
     */
    async batchLoad(elements, options = {}) {
        const { 
            batchSize = 3, 
            delay = 100,
            showProgress = false 
        } = options;

        const batches = [];
        for (let i = 0; i < elements.length; i += batchSize) {
            batches.push(elements.slice(i, i + batchSize));
        }

        if (showProgress) {
            eventBus.emit(EVENTS.BATCH_LOAD_STARTED, { 
                totalBatches: batches.length,
                totalElements: elements.length 
            });
        }

        for (let i = 0; i < batches.length; i++) {
            const batch = batches[i];
            
            // Load batch in parallel
            await Promise.all(
                batch.map(element => this.loadElement(element))
            );

            if (showProgress) {
                eventBus.emit(EVENTS.BATCH_LOAD_PROGRESS, { 
                    currentBatch: i + 1,
                    totalBatches: batches.length,
                    progress: ((i + 1) / batches.length) * 100
                });
            }

            // Delay between batches
            if (i < batches.length - 1 && delay > 0) {
                await new Promise(resolve => taskScheduler.schedule(resolve, delay));
            }
        }

        if (showProgress) {
            eventBus.emit(EVENTS.BATCH_LOAD_COMPLETED, { 
                totalElements: elements.length 
            });
        }
    }

    /**
     * Preload critical resources
     */
    preloadCriticalResources() {
        const criticalResources = [
            '/assets/css/critical.css',
            '/assets/js/vendor/chart.min.js',
            '/assets/js/vendor/leaflet.min.js'
        ];

        criticalResources.forEach(resource => {
            const link = document.createElement('link');
            link.rel = 'preload';
            
            if (resource.endsWith('.css')) {
                link.as = 'style';
            } else if (resource.endsWith('.js')) {
                link.as = 'script';
            }
            
            link.href = resource;
            document.head.appendChild(link);
        });
    }

    /**
     * Get loading statistics
     */
    getLoadingStats() {
        return {
            activeLoading: this.loadingStates.size,
            lazyElements: this.lazyElements.size,
            queuedLoads: this.loadingQueue.length,
            currentLoads: this.currentLoads
        };
    }
}

// Create singleton instance
export const loadingManager = new LoadingManager();

// Make it globally available
window.loadingManager = loadingManager;