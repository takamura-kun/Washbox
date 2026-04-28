// pickupService.js - Business logic for pickup operations

import { apiClient } from '../modules/api.js';
import { eventBus, EVENTS } from '../utils/eventBus.js';
import { ValidationError } from '../utils/errorBoundary.js';
import { validateCoordinates } from '../modules/utils.js';
import { startTimer, endTimer } from '../utils/performanceMonitor.js';

class PickupService {
    constructor() {
        this.cache = new Map();
        this.cacheTimeout = 5 * 60 * 1000; // 5 minutes
        this.pendingRequests = new Map();
    }

    /**
     * Get pickup by ID with caching
     */
    async getPickup(id) {
        const cacheKey = `pickup_${id}`;
        
        // Check cache first
        const cached = this.cache.get(cacheKey);
        if (cached && Date.now() - cached.timestamp < this.cacheTimeout) {
            return cached.data;
        }

        // Check if request is already pending
        if (this.pendingRequests.has(cacheKey)) {
            return this.pendingRequests.get(cacheKey);
        }

        // Make new request
        const requestPromise = this.fetchPickup(id);
        this.pendingRequests.set(cacheKey, requestPromise);

        try {
            const pickup = await requestPromise;
            
            // Cache the result
            this.cache.set(cacheKey, {
                data: pickup,
                timestamp: Date.now()
            });

            return pickup;
        } finally {
            this.pendingRequests.delete(cacheKey);
        }
    }

    /**
     * Fetch pickup from server
     */
    async fetchPickup(id) {
        startTimer('pickup_fetch');
        
        try {
            const response = await apiClient.request(`/admin/pickups/${id}`);
            
            if (response.success) {
                endTimer('pickup_fetch');
                return response.pickup;
            } else {
                throw new Error(response.error || 'Failed to fetch pickup');
            }
        } catch (error) {
            endTimer('pickup_fetch');
            throw error;
        }
    }

    /**
     * Get all pending pickups
     */
    async getPendingPickups() {
        const cacheKey = 'pending_pickups';
        
        // Check cache
        const cached = this.cache.get(cacheKey);
        if (cached && Date.now() - cached.timestamp < this.cacheTimeout) {
            return cached.data;
        }

        startTimer('pending_pickups_fetch');

        try {
            const pickups = await apiClient.getPendingPickups();
            
            // Cache the result
            this.cache.set(cacheKey, {
                data: pickups,
                timestamp: Date.now()
            });

            endTimer('pending_pickups_fetch');
            eventBus.emit(EVENTS.DATA_REFRESHED, { type: 'pending_pickups', count: pickups.length });
            
            return pickups;
        } catch (error) {
            endTimer('pending_pickups_fetch');
            throw error;
        }
    }

    /**
     * Update pickup status
     */
    async updatePickupStatus(id, status, metadata = {}) {
        this.validateStatus(status);

        startTimer('pickup_status_update');

        try {
            const response = await apiClient.request(`/admin/pickups/${id}/status`, {
                method: 'PUT',
                body: JSON.stringify({ 
                    status, 
                    metadata,
                    timestamp: Date.now()
                })
            });

            if (response.success) {
                // Invalidate cache
                this.invalidatePickupCache(id);
                
                // Emit event
                eventBus.emit(EVENTS.PICKUP_STATUS_CHANGED, {
                    id,
                    status,
                    previousStatus: response.previousStatus,
                    metadata
                });

                endTimer('pickup_status_update');
                return response;
            } else {
                throw new Error(response.error || 'Failed to update pickup status');
            }
        } catch (error) {
            endTimer('pickup_status_update');
            throw error;
        }
    }

    /**
     * Create new pickup
     */
    async createPickup(pickupData) {
        this.validatePickupData(pickupData);

        startTimer('pickup_create');

        try {
            const response = await apiClient.request('/admin/pickups', {
                method: 'POST',
                body: JSON.stringify({
                    ...pickupData,
                    created_at: new Date().toISOString()
                })
            });

            if (response.success) {
                // Invalidate pending pickups cache
                this.cache.delete('pending_pickups');
                
                // Emit event
                eventBus.emit(EVENTS.PICKUP_CREATED, response.pickup);

                endTimer('pickup_create');
                return response.pickup;
            } else {
                throw new Error(response.error || 'Failed to create pickup');
            }
        } catch (error) {
            endTimer('pickup_create');
            throw error;
        }
    }

    /**
     * Update pickup details
     */
    async updatePickup(id, updateData) {
        this.validatePickupData(updateData, false); // Partial validation for updates

        startTimer('pickup_update');

        try {
            const response = await apiClient.request(`/admin/pickups/${id}`, {
                method: 'PUT',
                body: JSON.stringify({
                    ...updateData,
                    updated_at: new Date().toISOString()
                })
            });

            if (response.success) {
                // Invalidate cache
                this.invalidatePickupCache(id);
                
                // Emit event
                eventBus.emit(EVENTS.PICKUP_UPDATED, {
                    id,
                    pickup: response.pickup,
                    changes: updateData
                });

                endTimer('pickup_update');
                return response.pickup;
            } else {
                throw new Error(response.error || 'Failed to update pickup');
            }
        } catch (error) {
            endTimer('pickup_update');
            throw error;
        }
    }

    /**
     * Delete pickup
     */
    async deletePickup(id) {
        startTimer('pickup_delete');

        try {
            const response = await apiClient.request(`/admin/pickups/${id}`, {
                method: 'DELETE'
            });

            if (response.success) {
                // Invalidate cache
                this.invalidatePickupCache(id);
                this.cache.delete('pending_pickups');
                
                // Emit event
                eventBus.emit(EVENTS.PICKUP_DELETED, { id });

                endTimer('pickup_delete');
                return response;
            } else {
                throw new Error(response.error || 'Failed to delete pickup');
            }
        } catch (error) {
            endTimer('pickup_delete');
            throw error;
        }
    }

    /**
     * Bulk update pickup statuses
     */
    async bulkUpdateStatus(pickupIds, status, metadata = {}) {
        this.validateStatus(status);

        if (!Array.isArray(pickupIds) || pickupIds.length === 0) {
            throw new ValidationError('Pickup IDs must be a non-empty array');
        }

        startTimer('pickup_bulk_update');

        try {
            const response = await apiClient.request('/admin/pickups/bulk-status', {
                method: 'PUT',
                body: JSON.stringify({
                    pickup_ids: pickupIds,
                    status,
                    metadata,
                    timestamp: Date.now()
                })
            });

            if (response.success) {
                // Invalidate cache for all affected pickups
                pickupIds.forEach(id => this.invalidatePickupCache(id));
                this.cache.delete('pending_pickups');
                
                // Emit events for each pickup
                pickupIds.forEach(id => {
                    eventBus.emit(EVENTS.PICKUP_STATUS_CHANGED, {
                        id,
                        status,
                        metadata,
                        bulk: true
                    });
                });

                endTimer('pickup_bulk_update');
                return response;
            } else {
                throw new Error(response.error || 'Failed to bulk update pickup statuses');
            }
        } catch (error) {
            endTimer('pickup_bulk_update');
            throw error;
        }
    }

    /**
     * Search pickups
     */
    async searchPickups(query, filters = {}) {
        if (!query || query.trim().length < 2) {
            throw new ValidationError('Search query must be at least 2 characters');
        }

        startTimer('pickup_search');

        try {
            const response = await apiClient.request('/admin/pickups/search', {
                method: 'POST',
                body: JSON.stringify({
                    query: query.trim(),
                    filters,
                    limit: filters.limit || 50
                })
            });

            if (response.success) {
                endTimer('pickup_search');
                return response.pickups;
            } else {
                throw new Error(response.error || 'Search failed');
            }
        } catch (error) {
            endTimer('pickup_search');
            throw error;
        }
    }

    /**
     * Get pickup statistics
     */
    async getPickupStats(dateRange = null) {
        const cacheKey = `pickup_stats_${dateRange ? dateRange.start + '_' + dateRange.end : 'all'}`;
        
        // Check cache
        const cached = this.cache.get(cacheKey);
        if (cached && Date.now() - cached.timestamp < this.cacheTimeout) {
            return cached.data;
        }

        startTimer('pickup_stats');

        try {
            const response = await apiClient.request('/admin/pickups/stats', {
                method: 'POST',
                body: JSON.stringify({ dateRange })
            });

            if (response.success) {
                // Cache the result
                this.cache.set(cacheKey, {
                    data: response.stats,
                    timestamp: Date.now()
                });

                endTimer('pickup_stats');
                return response.stats;
            } else {
                throw new Error(response.error || 'Failed to get pickup statistics');
            }
        } catch (error) {
            endTimer('pickup_stats');
            throw error;
        }
    }

    /**
     * Validate pickup data
     */
    validatePickupData(data, isCreate = true) {
        if (isCreate) {
            if (!data.customer_id && !data.customer) {
                throw new ValidationError('Customer information is required');
            }

            if (!data.pickup_address) {
                throw new ValidationError('Pickup address is required');
            }
        }

        // Validate coordinates if provided
        if (data.latitude !== undefined && data.longitude !== undefined) {
            const validation = validateCoordinates(data.latitude, data.longitude);
            if (!validation.valid) {
                throw new ValidationError(validation.error, 'coordinates');
            }
        }

        // Validate status if provided
        if (data.status) {
            this.validateStatus(data.status);
        }

        // Validate pickup date
        if (data.pickup_date) {
            const pickupDate = new Date(data.pickup_date);
            if (isNaN(pickupDate.getTime())) {
                throw new ValidationError('Invalid pickup date format');
            }
            
            if (pickupDate < new Date()) {
                throw new ValidationError('Pickup date cannot be in the past');
            }
        }
    }

    /**
     * Validate pickup status
     */
    validateStatus(status) {
        const validStatuses = ['pending', 'accepted', 'en_route', 'picked_up', 'cancelled'];
        if (!validStatuses.includes(status)) {
            throw new ValidationError(`Invalid status. Must be one of: ${validStatuses.join(', ')}`);
        }
    }

    /**
     * Invalidate pickup cache
     */
    invalidatePickupCache(id) {
        this.cache.delete(`pickup_${id}`);
        this.cache.delete('pending_pickups');
    }

    /**
     * Clear all cache
     */
    clearCache() {
        this.cache.clear();
        this.pendingRequests.clear();
    }

    /**
     * Get cache statistics
     */
    getCacheStats() {
        return {
            size: this.cache.size,
            pendingRequests: this.pendingRequests.size,
            timeout: this.cacheTimeout
        };
    }
}

// Create singleton instance
export const pickupService = new PickupService();

// Make it globally available
window.pickupService = pickupService;