// state.js - Application state management

class AppState {
    constructor() {
        this.selectedPickups = new Set();
        this.branches = [];
        this.mapCenter = [9.3068, 123.3054];
        this.mapZoom = 13;
        this.activeRoute = null;
        this.searchResultMarker = null;
        
        // Map instances
        this.logisticsMapInstance = null;
        this.modalMapInstance = null;
        
        // Map layers
        this.routeLayer = null;
        this.startMarker = null;
        this.endMarker = null;
        this.pickupMarkers = [];
        this.pickupCluster = null;
        this.modalPickupCluster = null;
        this.branchMarkers = [];
        
        // Load persisted state
        this.restore();
    }

    /**
     * Initialize with data from server
     */
    initializeDashboardData(branches, stats) {
        this.branches = branches || [];

        // Calculate map center
        if (this.branches.length > 0) {
            this.mapCenter = [
                this.branches.reduce(
                    (sum, b) => sum + (parseFloat(b.latitude) || 0),
                    0,
                ) / this.branches.length,
                this.branches.reduce(
                    (sum, b) => sum + (parseFloat(b.longitude) || 0),
                    0,
                ) / this.branches.length,
            ];
            this.mapZoom = this.branches.length > 1 ? 12 : 13;
        }

        console.log(`📍 Loaded ${this.branches.length} branch location(s) for map`);
        this.save();
    }

    /**
     * Find the nearest branch to a given lat/lon
     */
    getNearestBranch(lat, lon) {
        if (this.branches.length === 0) {
            return {
                name: "WashBox Branch",
                latitude: 9.3068,
                longitude: 123.3054,
                address: "",
                phone: "",
            };
        }

        let nearest = this.branches[0];
        let minDist = Infinity;

        this.branches.forEach((b) => {
            const d =
                Math.pow(parseFloat(b.latitude) - lat, 2) +
                Math.pow(parseFloat(b.longitude) - lon, 2);
            if (d < minDist) {
                minDist = d;
                nearest = b;
            }
        });

        return nearest;
    }

    /**
     * Get branch by database ID
     */
    getBranchById(id) {
        return (
            this.branches.find((b) => parseInt(b.id) === parseInt(id)) ||
            this.branches[0] ||
            null
        );
    }

    /**
     * Toggle pickup selection
     */
    togglePickupSelection(pickupId) {
        const id = parseInt(pickupId);
        if (this.selectedPickups.has(id)) {
            this.selectedPickups.delete(id);
        } else {
            this.selectedPickups.add(id);
        }
        this.save();
        return this.selectedPickups.has(id);
    }

    /**
     * Clear all selections
     */
    clearSelections() {
        this.selectedPickups.clear();
        this.save();
    }

    /**
     * Get selected pickup count
     */
    getSelectedCount() {
        return this.selectedPickups.size;
    }

    /**
     * Get selected pickup IDs as array
     */
    getSelectedPickupIds() {
        return Array.from(this.selectedPickups);
    }

    /**
     * Set active route
     */
    setActiveRoute(routeData) {
        this.activeRoute = routeData;
        this.save();
    }

    /**
     * Clear active route
     */
    clearActiveRoute() {
        this.activeRoute = null;
        this.save();
    }

    /**
     * Save state to localStorage
     */
    save() {
        try {
            const stateToSave = {
                selectedPickups: Array.from(this.selectedPickups),
                branches: this.branches,
                mapCenter: this.mapCenter,
                mapZoom: this.mapZoom,
                activeRoute: this.activeRoute,
                timestamp: Date.now()
            };
            localStorage.setItem('washbox_state', JSON.stringify(stateToSave));
        } catch (error) {
            console.warn('Failed to save state:', error);
        }
    }

    /**
     * Restore state from localStorage
     */
    restore() {
        try {
            const saved = localStorage.getItem('washbox_state');
            if (saved) {
                const state = JSON.parse(saved);
                
                // Only restore if not too old (1 hour)
                if (Date.now() - state.timestamp < 3600000) {
                    this.selectedPickups = new Set(state.selectedPickups || []);
                    this.branches = state.branches || [];
                    this.mapCenter = state.mapCenter || [9.3068, 123.3054];
                    this.mapZoom = state.mapZoom || 13;
                    this.activeRoute = state.activeRoute || null;
                    
                    console.log('State restored from localStorage');
                }
            }
        } catch (error) {
            console.warn('Failed to restore state:', error);
        }
    }

    /**
     * Clear persisted state
     */
    clearPersistedState() {
        localStorage.removeItem('washbox_state');
    }
}

// Create singleton instance
export const appState = new AppState();

// Make it globally available for backward compatibility
window.appState = appState;