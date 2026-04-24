/**
 * Data Stabilizer - Prevents data loading/disappearing issues
 * Ensures dashboard data remains stable during performance optimizations
 */

class DataStabilizer {
    constructor() {
        this.dataCache = new Map();
        this.loadingStates = new Map();
        this.retryAttempts = new Map();
        this.maxRetries = 3;
        this.retryDelay = 1000;
        this.stabilityChecks = new Map();
    }

    /**
     * Cache critical dashboard data
     */
    cacheData(key, data, ttl = 300000) { // 5 minutes default TTL
        this.dataCache.set(key, {
            data,
            timestamp: Date.now(),
            ttl
        });
    }

    /**
     * Get cached data if still valid
     */
    getCachedData(key) {
        const cached = this.dataCache.get(key);
        if (!cached) return null;

        const isExpired = (Date.now() - cached.timestamp) > cached.ttl;
        if (isExpired) {
            this.dataCache.delete(key);
            return null;
        }

        return cached.data;
    }

    /**
     * Stabilize data loading with retry mechanism
     */
    async stabilizeDataLoad(key, loadFunction, options = {}) {
        const {
            retries = this.maxRetries,
            delay = this.retryDelay,
            fallbackData = null,
            onRetry = null,
            onSuccess = null,
            onFailure = null
        } = options;

        // Check if already loading
        if (this.loadingStates.get(key)) {
            console.log(`⏳ Data load already in progress for: ${key}`);
            return this.getCachedData(key) || fallbackData;
        }

        // Try to get cached data first
        const cachedData = this.getCachedData(key);
        if (cachedData) {
            console.log(`📦 Using cached data for: ${key}`);
            return cachedData;
        }

        this.loadingStates.set(key, true);
        let currentRetry = 0;

        const attemptLoad = async () => {
            try {
                console.log(`🔄 Loading data for: ${key} (attempt ${currentRetry + 1}/${retries + 1})`);
                
                const data = await loadFunction();
                
                if (data && this.validateData(data)) {
                    this.cacheData(key, data);
                    this.loadingStates.delete(key);
                    this.retryAttempts.delete(key);
                    
                    if (onSuccess) onSuccess(data);
                    console.log(`✅ Successfully loaded data for: ${key}`);
                    return data;
                }
                
                throw new Error('Invalid or empty data received');
                
            } catch (error) {
                console.warn(`⚠️ Failed to load data for: ${key}`, error);
                
                if (currentRetry < retries) {
                    currentRetry++;
                    this.retryAttempts.set(key, currentRetry);
                    
                    if (onRetry) onRetry(currentRetry, error);
                    
                    console.log(`🔄 Retrying in ${delay}ms... (${currentRetry}/${retries})`);
                    await new Promise(resolve => setTimeout(resolve, delay));
                    return attemptLoad();
                } else {
                    this.loadingStates.delete(key);
                    this.retryAttempts.delete(key);
                    
                    if (onFailure) onFailure(error);
                    console.error(`❌ Failed to load data for: ${key} after ${retries + 1} attempts`);
                    
                    // Return fallback data or cached data if available
                    return fallbackData || this.getCachedData(key);
                }
            }
        };

        return attemptLoad();
    }

    /**
     * Validate data integrity
     */
    validateData(data) {
        if (!data) return false;
        
        // Check for common data structures
        if (Array.isArray(data)) {
            return data.length >= 0; // Empty arrays are valid
        }
        
        if (typeof data === 'object') {
            return Object.keys(data).length >= 0; // Empty objects are valid
        }
        
        return true; // Primitive values are valid
    }

    /**
     * Monitor data stability
     */
    monitorDataStability(key, element, checkInterval = 5000) {
        if (this.stabilityChecks.has(key)) {
            clearInterval(this.stabilityChecks.get(key));
        }

        const checkStability = () => {
            if (!element || !element.isConnected) {
                console.warn(`🔍 Element for ${key} is no longer in DOM`);
                this.clearStabilityCheck(key);
                return;
            }

            // Check if element has expected content
            const hasContent = element.textContent.trim().length > 0 || 
                              element.children.length > 0;
            
            if (!hasContent) {
                console.warn(`🔍 Element for ${key} appears empty, attempting data refresh`);
                this.refreshData(key);
            }
        };

        const intervalId = setInterval(checkStability, checkInterval);
        this.stabilityChecks.set(key, intervalId);
        
        console.log(`👁️ Monitoring data stability for: ${key}`);
    }

    /**
     * Refresh data for a specific key
     */
    async refreshData(key) {
        // Clear cached data to force refresh
        this.dataCache.delete(key);
        
        // Emit refresh event
        window.dispatchEvent(new CustomEvent('dataRefreshRequested', {
            detail: { key }
        }));
    }

    /**
     * Clear stability check
     */
    clearStabilityCheck(key) {
        if (this.stabilityChecks.has(key)) {
            clearInterval(this.stabilityChecks.get(key));
            this.stabilityChecks.delete(key);
        }
    }

    /**
     * Stabilize DOM updates to prevent flickering
     */
    stabilizeDOMUpdate(element, updateFunction, options = {}) {
        const {
            preserveContent = true,
            fadeTransition = true,
            transitionDuration = 200
        } = options;

        if (!element) return;

        // Preserve current content if requested
        const originalContent = preserveContent ? element.innerHTML : '';
        
        // Apply fade out if requested
        if (fadeTransition) {
            element.style.transition = `opacity ${transitionDuration}ms ease`;
            element.style.opacity = '0.7';
        }

        // Use requestAnimationFrame for smooth updates
        requestAnimationFrame(() => {
            try {
                updateFunction();
                
                // Fade back in
                if (fadeTransition) {
                    requestAnimationFrame(() => {
                        element.style.opacity = '1';
                        
                        // Clean up transition after completion
                        setTimeout(() => {
                            element.style.transition = '';
                        }, transitionDuration);
                    });
                }
            } catch (error) {
                console.error('DOM update failed:', error);
                
                // Restore original content on error
                if (preserveContent && originalContent) {
                    element.innerHTML = originalContent;
                }
                
                // Reset opacity
                if (fadeTransition) {
                    element.style.opacity = '1';
                }
            }
        });
    }

    /**
     * Get loading statistics
     */
    getStats() {
        return {
            cachedItems: this.dataCache.size,
            activeLoads: this.loadingStates.size,
            monitoredElements: this.stabilityChecks.size,
            retryAttempts: Array.from(this.retryAttempts.entries())
        };
    }

    /**
     * Clear all caches and stop monitoring
     */
    cleanup() {
        this.dataCache.clear();
        this.loadingStates.clear();
        this.retryAttempts.clear();
        
        // Clear all stability checks
        this.stabilityChecks.forEach((intervalId) => {
            clearInterval(intervalId);
        });
        this.stabilityChecks.clear();
        
        console.log('🧹 Data stabilizer cleaned up');
    }
}

// Create singleton instance
const dataStabilizer = new DataStabilizer();

// Make available globally
window.dataStabilizer = dataStabilizer;

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { DataStabilizer, dataStabilizer };
}

console.log('🛡️ Data Stabilizer initialized - preventing data loading issues');