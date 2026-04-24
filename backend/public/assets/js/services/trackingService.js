// trackingService.js - Real-time vehicle tracking service

import { eventBus, EVENTS } from '../utils/eventBus.js';
import { apiClient } from '../modules/api.js';
import { appState } from '../modules/state.js';
import { taskScheduler } from '../utils/taskScheduler.js';

class TrackingService {
    constructor() {
        this.activeTracking = new Map();
        this.websocket = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 1000;
        this.trackingInterval = 30000; // 30 seconds
        this.geolocationWatchId = null;
        this.pollingSetup = false;
        
        this.init();
    }

    init() {
        this.setupWebSocket();
        this.setupEventListeners();
        console.log('📡 Tracking service initialized');
    }

    /**
     * Setup WebSocket connection for real-time updates
     */
    setupWebSocket() {
        // Use Laravel Echo with Reverb
        if (window.Echo) {
            console.log('📡 Connecting to Reverb WebSocket...');
            
            window.Echo.channel('tracking')
                .listen('.location.updated', (data) => {
                    console.log('📍 Location update received:', data);
                    this.handleWebSocketMessage(data);
                });
            
            console.log('✅ WebSocket connected via Laravel Echo');
            return;
        }
        
        // Fallback to polling if Echo not available
        console.log('📡 Laravel Echo not available, using polling');
        this.setupPolling();
    }

    /**
     * Handle WebSocket messages
     */
    handleWebSocketMessage(data) {
        switch (data.type) {
            case 'location_update':
                this.updateVehicleLocation(data.vehicleId, data.location);
                break;
            case 'route_progress':
                this.updateRouteProgress(data.vehicleId, data.progress);
                break;
            case 'delivery_status':
                this.updateDeliveryStatus(data.pickupId, data.status);
                break;
            case 'driver_status':
                this.updateDriverStatus(data.driverId, data.status);
                break;
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        eventBus.on(EVENTS.NAVIGATION_STARTED, (data) => {
            this.startTracking(data.pickupId);
        });
        
        eventBus.on(EVENTS.MULTI_NAVIGATION_STARTED, (data) => {
            this.startMultiTracking(data.pickupIds);
        });
        
        eventBus.on(EVENTS.NAVIGATION_STOPPED, (data) => {
            this.stopTracking(data.pickupId);
        });
    }

    /**
     * Start tracking a single pickup
     */
    async startTracking(pickupId) {
        try {
            // Get current location
            const position = await this.getCurrentPosition();
            
            const trackingData = {
                pickupId,
                startTime: Date.now(),
                currentLocation: {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    timestamp: position.timestamp
                },
                status: 'en_route'
            };
            
            this.activeTracking.set(pickupId, trackingData);
            
            // Start location watching
            this.startLocationWatching();
            
            // Notify server
            await apiClient.post('/api/tracking/start', {
                pickup_id: pickupId,
                location: trackingData.currentLocation
            });
            
            eventBus.emit(EVENTS.TRACKING_STARTED, { pickupId, location: trackingData.currentLocation });
            
        } catch (error) {
            console.error('Failed to start tracking:', error);
            
            // Provide user-friendly error message
            let errorMessage = 'Failed to start tracking';
            if (error.code === 1) {
                errorMessage = 'Location access denied or not available on this connection. For development, use localhost or enable HTTPS.';
            } else if (error.code === 2) {
                errorMessage = 'Location unavailable. Please check your device settings.';
            } else if (error.code === 3) {
                errorMessage = 'Location request timed out. Using last known position.';
                // Try to continue with cached position
                this.startTrackingWithoutLocation(pickupId);
                return;
            } else if (error.message && error.message.includes('secure origins')) {
                errorMessage = 'Geolocation requires HTTPS or localhost. For development, use localhost:8000 instead of IP address.';
            }
            
            eventBus.emit(EVENTS.TRACKING_ERROR, { error: errorMessage, pickupId });
        }
    }

    /**
     * Start tracking without initial location (fallback)
     */
    startTrackingWithoutLocation(pickupId) {
        const trackingData = {
            pickupId,
            startTime: Date.now(),
            currentLocation: null,
            status: 'en_route'
        };
        
        this.activeTracking.set(pickupId, trackingData);
        
        // Start location watching - it will update when location becomes available
        this.startLocationWatching();
        
        eventBus.emit(EVENTS.TRACKING_STARTED, { 
            pickupId, 
            location: null,
            message: 'Tracking started without initial location' 
        });
    }

    /**
     * Start tracking multiple pickups
     */
    async startMultiTracking(pickupIds) {
        try {
            const position = await this.getCurrentPosition();
            
            for (const pickupId of pickupIds) {
                const trackingData = {
                    pickupId,
                    startTime: Date.now(),
                    currentLocation: {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                        timestamp: position.timestamp
                    },
                    status: 'en_route'
                };
                
                this.activeTracking.set(pickupId, trackingData);
            }
            
            this.startLocationWatching();
            
            await apiClient.post('/api/tracking/start-multi', {
                pickup_ids: pickupIds,
                location: {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    timestamp: position.timestamp
                }
            });
            
            eventBus.emit(EVENTS.MULTI_TRACKING_STARTED, { pickupIds });
            
        } catch (error) {
            console.error('Failed to start multi-tracking:', error);
            
            // Provide user-friendly error message
            let errorMessage = 'Failed to start multi-tracking';
            if (error.code === 1) {
                errorMessage = 'Location access denied or not available on this connection. For development, use localhost or enable HTTPS.';
            } else if (error.code === 2) {
                errorMessage = 'Location unavailable. Please check your device settings.';
            } else if (error.code === 3) {
                errorMessage = 'Location request timed out. Starting tracking without initial location.';
                // Try to continue without location
                this.startMultiTrackingWithoutLocation(pickupIds);
                return;
            } else if (error.message && error.message.includes('secure origins')) {
                errorMessage = 'Geolocation requires HTTPS or localhost. For development, use localhost:8000 instead of IP address.';
            }
            
            eventBus.emit(EVENTS.TRACKING_ERROR, { error: errorMessage, pickupIds });
        }
    }

    /**
     * Start multi-tracking without initial location (fallback)
     */
    startMultiTrackingWithoutLocation(pickupIds) {
        for (const pickupId of pickupIds) {
            const trackingData = {
                pickupId,
                startTime: Date.now(),
                currentLocation: null,
                status: 'en_route'
            };
            
            this.activeTracking.set(pickupId, trackingData);
        }
        
        this.startLocationWatching();
        
        eventBus.emit(EVENTS.MULTI_TRACKING_STARTED, { 
            pickupIds,
            message: 'Tracking started without initial location' 
        });
    }

    /**
     * Stop tracking
     */
    async stopTracking(pickupId) {
        if (!this.activeTracking.has(pickupId)) return;
        
        try {
            const trackingData = this.activeTracking.get(pickupId);
            
            await apiClient.post('/api/tracking/stop', {
                pickup_id: pickupId,
                end_time: Date.now(),
                final_location: trackingData.currentLocation
            });
            
            this.activeTracking.delete(pickupId);
            
            // Stop location watching if no active tracking
            if (this.activeTracking.size === 0) {
                this.stopLocationWatching();
            }
            
            eventBus.emit(EVENTS.TRACKING_STOPPED, { pickupId });
            
        } catch (error) {
            console.error('Failed to stop tracking:', error);
        }
    }

    /**
     * Start watching location changes
     */
    startLocationWatching() {
        if (this.geolocationWatchId) return;
        
        const options = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 5000
        };
        
        this.geolocationWatchId = navigator.geolocation.watchPosition(
            (position) => this.handleLocationUpdate(position),
            (error) => this.handleLocationError(error),
            options
        );
    }

    /**
     * Stop watching location changes
     */
    stopLocationWatching() {
        if (this.geolocationWatchId) {
            navigator.geolocation.clearWatch(this.geolocationWatchId);
            this.geolocationWatchId = null;
        }
    }

    /**
     * Handle location updates
     */
    async handleLocationUpdate(position) {
        const location = {
            lat: position.coords.latitude,
            lng: position.coords.longitude,
            accuracy: position.coords.accuracy,
            timestamp: position.timestamp,
            speed: position.coords.speed,
            heading: position.coords.heading
        };
        
        // Update all active tracking
        for (const [pickupId, trackingData] of this.activeTracking) {
            trackingData.currentLocation = location;
            trackingData.lastUpdate = Date.now();
            
            // Calculate distance traveled
            if (trackingData.previousLocation) {
                trackingData.distanceTraveled = (trackingData.distanceTraveled || 0) + 
                    this.calculateDistance(trackingData.previousLocation, location);
            }
            trackingData.previousLocation = location;
        }
        
        // Send to server
        try {
            await apiClient.post('/api/tracking/update', {
                active_pickups: Array.from(this.activeTracking.keys()),
                location: location
            });
            
            eventBus.emit(EVENTS.LOCATION_UPDATED, { location, activePickups: this.activeTracking.size });
            
        } catch (error) {
            console.error('Failed to update location:', error);
        }
    }

    /**
     * Handle location errors
     */
    handleLocationError(error) {
        let message = 'Location access error';
        
        switch (error.code) {
            case error.PERMISSION_DENIED:
                message = 'Location access denied. Please enable location services.';
                break;
            case error.POSITION_UNAVAILABLE:
                message = 'Location information unavailable.';
                break;
            case error.TIMEOUT:
                message = 'Location request timed out.';
                break;
        }
        
        console.error('Location error:', message);
        eventBus.emit(EVENTS.TRACKING_ERROR, { error: message });
    }

    /**
     * Get current position
     */
    getCurrentPosition() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Geolocation not supported'));
                return;
            }
            
            // Check if running on non-secure origin (not localhost or HTTPS)
            const isSecureOrigin = window.location.protocol === 'https:' || 
                                   window.location.hostname === 'localhost' || 
                                   window.location.hostname === '127.0.0.1';
            
            if (!isSecureOrigin) {
                const error = new GeolocationPositionError();
                error.code = 1;
                error.message = 'Only secure origins are allowed (see: https://goo.gl/Y0ZkNV). Please use HTTPS or localhost for geolocation.';
                reject(error);
                return;
            }
            
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: false, // Changed to false for faster response
                timeout: 10000, // Reduced to 10 seconds
                maximumAge: 60000 // Allow cached position up to 60 seconds old
            });
        });
    }

    /**
     * Update vehicle location from WebSocket
     */
    updateVehicleLocation(vehicleId, location) {
        eventBus.emit(EVENTS.VEHICLE_LOCATION_UPDATED, { vehicleId, location });
    }

    /**
     * Update route progress
     */
    updateRouteProgress(vehicleId, progress) {
        eventBus.emit(EVENTS.ROUTE_PROGRESS_UPDATED, { vehicleId, progress });
    }

    /**
     * Update delivery status
     */
    updateDeliveryStatus(pickupId, status) {
        if (this.activeTracking.has(pickupId)) {
            this.activeTracking.get(pickupId).status = status;
        }
        
        eventBus.emit(EVENTS.DELIVERY_STATUS_UPDATED, { pickupId, status });
    }

    /**
     * Update driver status
     */
    updateDriverStatus(driverId, status) {
        eventBus.emit(EVENTS.DRIVER_STATUS_UPDATED, { driverId, status });
    }

    /**
     * Calculate distance between two points
     */
    calculateDistance(pos1, pos2) {
        const R = 6371e3; // Earth's radius in meters
        const φ1 = pos1.lat * Math.PI/180;
        const φ2 = pos2.lat * Math.PI/180;
        const Δφ = (pos2.lat-pos1.lat) * Math.PI/180;
        const Δλ = (pos2.lng-pos1.lng) * Math.PI/180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

        return R * c; // Distance in meters
    }

    /**
     * Handle reconnection
     */
    handleReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            const delay = this.reconnectDelay * Math.pow(2, this.reconnectAttempts - 1);
            
            // Only log first attempt
            if (this.reconnectAttempts === 1) {
                console.log('Attempting WebSocket reconnection...');
            }
            
            taskScheduler.schedule(() => {
                this.setupWebSocket();
            }, delay);
        } else {
            // Silently fall back to polling after max attempts
            if (!this.pollingSetup) {
                console.log('📡 Using polling for tracking updates');
                this.setupPolling();
                this.pollingSetup = true;
            }
        }
    }

    /**
     * Setup polling as fallback
     */
    setupPolling() {
        setInterval(async () => {
            if (this.activeTracking.size > 0) {
                try {
                    const position = await this.getCurrentPosition();
                    this.handleLocationUpdate(position);
                } catch (error) {
                    // Silently ignore geolocation errors in polling
                    // Only log if it's not a timeout
                    if (error.code !== 3) {
                        console.warn('Polling location update failed:', error.message);
                    }
                }
            }
        }, this.trackingInterval);
    }

    /**
     * Get tracking status
     */
    getTrackingStatus() {
        return {
            activeTracking: this.activeTracking.size,
            isConnected: this.websocket?.readyState === WebSocket.OPEN,
            trackingData: Array.from(this.activeTracking.entries())
        };
    }

    /**
     * Get tracking history
     */
    async getTrackingHistory(pickupId, startDate, endDate) {
        try {
            const response = await apiClient.get('/api/tracking/history', {
                pickup_id: pickupId,
                start_date: startDate,
                end_date: endDate
            });
            
            return response.data;
        } catch (error) {
            console.error('Failed to get tracking history:', error);
            throw error;
        }
    }
}

// Create singleton instance
export const trackingService = new TrackingService();

// Make it globally available
window.trackingService = trackingService;