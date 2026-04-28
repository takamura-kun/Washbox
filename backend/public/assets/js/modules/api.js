// api.js - API client for server communications

import { showToast } from './utils.js';

class ApiClient {
    constructor() {
        this.baseUrl = '';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    /**
     * Get CSRF token
     */
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    /**
     * Make HTTP request with error handling
     */
    async request(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                ...options.headers
            }
        };

        const config = { ...defaultOptions, ...options };

        try {
            const response = await fetch(url, config);
            
            if (!response.ok) {
                throw new Error(`HTTP error ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    }

    /**
     * GET request
     */
    async get(url, params = {}) {
        const queryString = Object.keys(params).length > 0 
            ? '?' + new URLSearchParams(params).toString()
            : '';
        
        return this.request(url + queryString, {
            method: 'GET'
        });
    }

    /**
     * POST request
     */
    async post(url, data = {}) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    /**
     * PUT request
     */
    async put(url, data = {}) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    /**
     * DELETE request
     */
    async delete(url) {
        return this.request(url, {
            method: 'DELETE'
        });
    }

    /**
     * Get route to pickup
     */
    async getRouteToPickup(pickupId) {
        try {
            showToast("Loading route...", "info");
            
            const data = await this.request(`/admin/pickups/${pickupId}/route`);
            
            if (data.success && data.route) {
                return data;
            } else {
                throw new Error(data.error || data.message || 'Unable to calculate route');
            }
        } catch (error) {
            let errorMessage = error.message;
            
            if (error.message.includes('Invalid pickup coordinates')) {
                errorMessage = 'This pickup has invalid coordinates. Please edit the pickup and set a valid address.';
            } else if (error.message.includes('HTTP error')) {
                errorMessage = 'Server error while fetching route. Please try again.';
            }
            
            showToast(errorMessage, "danger");
            throw error;
        }
    }

    /**
     * Optimize multi-pickup route
     */
    async optimizeRoute(pickupIds, branchId = null) {
        try {
            showToast(`Optimizing route for ${pickupIds.length} stops...`, "info");

            const body = { pickup_ids: pickupIds };
            if (branchId) body.branch_id = branchId;

            const data = await this.request("/admin/logistics/optimize-route", {
                method: "POST",
                body: JSON.stringify(body)
            });

            if (data.success) {
                showToast("Route optimized successfully!", "success");
                return data.optimization || data;
            } else {
                throw new Error(data.error || data.message || "Failed to optimize route");
            }
        } catch (error) {
            const errorMessage = error.message.includes('Network') 
                ? `Network error: ${error.message}` 
                : error.message;
            showToast(errorMessage, "danger");
            throw error;
        }
    }

    /**
     * Start navigation for pickup
     */
    async startNavigation(pickupId) {
        try {
            const data = await this.request(`/admin/pickups/${pickupId}/start-navigation`, {
                method: "POST"
            });

            if (data.success) {
                showToast("Navigation started!", "success");
                return data;
            } else {
                throw new Error(data.message || "Failed to start navigation");
            }
        } catch (error) {
            showToast(`Failed to start navigation: ${error.message}`, "danger");
            throw error;
        }
    }

    /**
     * Start multi-pickup navigation
     */
    async startMultiPickupNavigation(pickupIds) {
        try {
            showToast("Starting multi-pickup navigation...", "info");

            const data = await this.request("/admin/logistics/start-multi-pickup", {
                method: "POST",
                body: JSON.stringify({ pickup_ids: pickupIds })
            });

            if (data.success) {
                showToast('Multi-pickup navigation started! All selected pickups are now marked as "En Route".', "success");
                return data;
            } else {
                throw new Error(data.error || "Failed to start navigation");
            }
        } catch (error) {
            showToast(`Failed to start navigation: ${error.message}`, "danger");
            throw error;
        }
    }

    /**
     * Get pending pickups
     */
    async getPendingPickups() {
        try {
            const data = await this.request("/admin/logistics/pending-pickups");
            
            if (data.success && data.pickups) {
                return data.pickups;
            } else {
                throw new Error("Failed to load pending pickups");
            }
        } catch (error) {
            showToast("Failed to load pending pickups", "danger");
            throw error;
        }
    }

    /**
     * Get pickups by IDs
     */
    async getPickupsByIds(pickupIds) {
        try {
            const data = await this.post("/admin/pickups/by-ids", {
                pickup_ids: pickupIds
            });
            
            if (data.success && data.pickups) {
                return data.pickups;
            } else if (data.pickups) {
                return data.pickups;
            } else {
                throw new Error("Failed to load pickups");
            }
        } catch (error) {
            console.warn("Failed to load pickups by IDs, using fallback", error);
            // Fallback: return mock data structure
            return pickupIds.map(id => ({ id, pickup_latitude: 0, pickup_longitude: 0 }));
        }
    }

    /**
     * Geocode address using Nominatim
     */
    async geocodeAddress(address) {
        try {
            const normalizedAddress = this.normalizeAddress(address);
            const searchQuery = encodeURIComponent(normalizedAddress + ", Philippines");
            const apiUrl = `https://nominatim.openstreetmap.org/search?q=${searchQuery}&format=json&limit=5&countrycodes=ph&addressdetails=1&extratags=1`;

            const response = await fetch(apiUrl, {
                headers: {
                    "User-Agent": "WashBox Laundry Management System",
                },
            });

            if (!response.ok) {
                throw new Error("Geocoding service unavailable");
            }

            const data = await response.json();
            
            if (!data || data.length === 0) {
                // Try fallback search without house number
                const fallbackQuery = this.normalizeAddress(address.replace(/^\d+\s+/, ''));
                if (fallbackQuery !== address && fallbackQuery.length > 3) {
                    return this.geocodeAddress(fallbackQuery);
                }
                throw new Error("Address not found");
            }

            return data[0];
        } catch (error) {
            console.error("Geocoding error:", error);
            throw error;
        }
    }

    /**
     * Reverse geocode coordinates to address
     */
    async reverseGeocode(lat, lng) {
        try {
            const apiUrl = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&addressdetails=1`;

            const response = await fetch(apiUrl, {
                headers: {
                    "User-Agent": "WashBox Laundry Management System",
                },
            });

            if (!response.ok) {
                throw new Error("Reverse geocoding failed");
            }

            const data = await response.json();
            return data.display_name || "Unknown Location";
        } catch (error) {
            console.error("Reverse geocoding error:", error);
            return "Updated Location";
        }
    }

    /**
     * Calculate route using OSRM
     */
    async calculateRoute(startCoords, endCoords) {
        try {
            // Use faster routing profile and simplified geometry
            const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${startCoords[1]},${startCoords[0]};${endCoords[1]},${endCoords[0]}?overview=simplified&geometries=geojson&steps=false`;

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout

            const response = await fetch(osrmUrl, { signal: controller.signal });
            clearTimeout(timeoutId);
            
            const data = await response.json();

            if (data.code !== "Ok" || !data.routes || data.routes.length === 0) {
                throw new Error("Could not calculate route");
            }

            return data.routes[0];
        } catch (error) {
            if (error.name === 'AbortError') {
                console.error('Routing request timed out');
                throw new Error('Route calculation timed out');
            }
            console.error("Routing error:", error);
            throw error;
        }
    }

    /**
     * Normalize address for better geocoding
     */
    normalizeAddress(addr) {
        return addr
            .replace(/\bDr\b/gi, 'Doctor')
            .replace(/\bSt\b(?=\s|,|$)/gi, 'Street')
            .replace(/\bAve\b(?=\s|,|$)/gi, 'Avenue')
            .replace(/\bRd\b(?=\s|,|$)/gi, 'Road')
            .replace(/\bBlvd\b(?=\s|,|$)/gi, 'Boulevard');
    }
}

// Create singleton instance
export const apiClient = new ApiClient();

// Make it globally available for backward compatibility
window.apiClient = apiClient;