// eventBus.js - Event-driven architecture for loose coupling

class EventBus {
    constructor() {
        this.events = {};
        this.maxListeners = 50;
    }

    /**
     * Subscribe to an event
     */
    on(event, callback, context = null) {
        if (!this.events[event]) {
            this.events[event] = [];
        }

        if (this.events[event].length >= this.maxListeners) {
            console.warn(`EventBus: Maximum listeners (${this.maxListeners}) reached for event '${event}'`);
            return;
        }

        this.events[event].push({
            callback,
            context,
            once: false
        });

        return this; // For chaining
    }

    /**
     * Subscribe to an event only once
     */
    once(event, callback, context = null) {
        if (!this.events[event]) {
            this.events[event] = [];
        }

        this.events[event].push({
            callback,
            context,
            once: true
        });

        return this;
    }

    /**
     * Emit an event
     */
    emit(event, data = null) {
        if (!this.events[event]) {
            return this;
        }

        // Create a copy to avoid issues if listeners are removed during emission
        const listeners = [...this.events[event]];

        listeners.forEach((listener, index) => {
            try {
                if (listener.context) {
                    listener.callback.call(listener.context, data);
                } else {
                    listener.callback(data);
                }

                // Remove one-time listeners
                if (listener.once) {
                    this.events[event].splice(this.events[event].indexOf(listener), 1);
                }
            } catch (error) {
                console.error(`EventBus: Error in listener for event '${event}':`, error);
            }
        });

        return this;
    }

    /**
     * Unsubscribe from an event
     */
    off(event, callback = null) {
        if (!this.events[event]) {
            return this;
        }

        if (!callback) {
            // Remove all listeners for this event
            delete this.events[event];
        } else {
            // Remove specific listener
            this.events[event] = this.events[event].filter(
                listener => listener.callback !== callback
            );

            if (this.events[event].length === 0) {
                delete this.events[event];
            }
        }

        return this;
    }

    /**
     * Get all events and their listener counts
     */
    getEvents() {
        const eventInfo = {};
        Object.keys(this.events).forEach(event => {
            eventInfo[event] = this.events[event].length;
        });
        return eventInfo;
    }

    /**
     * Clear all events
     */
    clear() {
        this.events = {};
        return this;
    }

    /**
     * Set maximum listeners per event
     */
    setMaxListeners(max) {
        this.maxListeners = max;
        return this;
    }
}

// Create singleton instance
export const eventBus = new EventBus();

// Event constants for better maintainability
export const EVENTS = {
    // Pickup events
    PICKUP_SELECTED: 'pickup:selected',
    PICKUP_DESELECTED: 'pickup:deselected',
    PICKUP_STATUS_CHANGED: 'pickup:statusChanged',
    PICKUP_CREATED: 'pickup:created',
    PICKUP_UPDATED: 'pickup:updated',
    PICKUP_DELETED: 'pickup:deleted',
    PICKUP_COMPLETED: 'pickup:completed',

    // Route events
    ROUTE_CALCULATED: 'route:calculated',
    ROUTE_CLEARED: 'route:cleared',
    ROUTE_OPTIMIZED: 'route:optimized',
    ROUTE_STARTED: 'route:started',
    ROUTE_PROGRESS_UPDATED: 'route:progressUpdated',

    // Map events
    MAP_INITIALIZED: 'map:initialized',
    MAP_MARKER_ADDED: 'map:markerAdded',
    MAP_MARKER_REMOVED: 'map:markerRemoved',
    MAP_BOUNDS_CHANGED: 'map:boundsChanged',

    // UI events
    UI_LOADING_START: 'ui:loadingStart',
    UI_LOADING_END: 'ui:loadingEnd',
    UI_ERROR: 'ui:error',
    UI_SUCCESS: 'ui:success',
    UI_STATUS_CLICKED: 'ui:statusClicked',

    // Data events
    DATA_REFRESHED: 'data:refreshed',
    DATA_SYNC_START: 'data:syncStart',
    DATA_SYNC_END: 'data:syncEnd',

    // Navigation events
    NAV_TAB_CHANGED: 'nav:tabChanged',
    NAV_MODAL_OPENED: 'nav:modalOpened',
    NAV_MODAL_CLOSED: 'nav:modalClosed',
    NAVIGATION_STARTED: 'navigation:started',
    NAVIGATION_STOPPED: 'navigation:stopped',

    // Tracking events
    TRACKING_CONNECTED: 'tracking:connected',
    TRACKING_ERROR: 'tracking:error',
    TRACKING_STARTED: 'tracking:started',
    TRACKING_STOPPED: 'tracking:stopped',
    MULTI_TRACKING_STARTED: 'tracking:multiStarted',
    LOCATION_UPDATED: 'tracking:locationUpdated',
    VEHICLE_LOCATION_UPDATED: 'tracking:vehicleLocationUpdated',
    DELIVERY_STATUS_UPDATED: 'tracking:deliveryStatusUpdated',
    DRIVER_STATUS_UPDATED: 'tracking:driverStatusUpdated',

    // Analytics events
    ANALYTICS_UPDATED: 'analytics:updated',
    ANALYTICS_ALERT: 'analytics:alert',
    PREDICTIONS_UPDATED: 'analytics:predictionsUpdated',

    // Loading events
    CONTENT_LOADED: 'loading:contentLoaded',
    BATCH_LOAD_STARTED: 'loading:batchStarted',
    BATCH_LOAD_PROGRESS: 'loading:batchProgress',
    BATCH_LOAD_COMPLETED: 'loading:batchCompleted',

    // Keyboard events
    SHORTCUT_EXECUTED: 'keyboard:shortcutExecuted',

    // Dashboard events
    DASHBOARD_INITIALIZED: 'dashboard:initialized',
    DASHBOARD_REFRESH: 'dashboard:refresh',
    TAB_CHANGED: 'dashboard:tabChanged',

    // Multi-navigation events
    MULTI_NAVIGATION_STARTED: 'navigation:multiStarted',
};

// Make it globally available for backward compatibility
window.eventBus = eventBus;
window.EVENTS = EVENTS;

console.log('✅ EventBus initialized');