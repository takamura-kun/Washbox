// maps.js - Map management and functionality with offline support

import { MAP_CONFIG, STATUS_COLORS, BRANCH_COLORS } from './config.js';
import { appState } from './state.js';
import { showToast, validateCoordinates, getStatusColor } from './utils.js';
import { apiClient } from './api.js';

class MapManager {
    constructor() {
        this.searchResultMarker = null;
        this.currentTileLayer = null;
    }

    /**
     * Add tile layer with fallback support for offline mode
     */
    addTileLayerWithFallback(map) {
        if (!map) {
            console.error('Map instance is null or undefined');
            return null;
        }

        const tileUrls = [
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'https://b.tile.openstreetmap.org/{z}/{x}/{y}.png'
        ];

        try {
            let tileLayer = L.tileLayer(tileUrls[0], {
                attribution: MAP_CONFIG.TILE_ATTRIBUTION || 'OpenStreetMap contributors',
                maxZoom: MAP_CONFIG.MAX_ZOOM || 19,
                minZoom: MAP_CONFIG.MIN_ZOOM || 2,
                errorTileUrl: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256"><rect fill="%23f0f0f0" width="256" height="256"/><text x="128" y="128" text-anchor="middle" dy=".3em" font-size="12" fill="%23999">Offline</text></svg>'
            });

            tileLayer.on('tileerror', (error) => {
                console.warn('Primary tile server unavailable, attempting fallback');
                try {
                    if (this.currentTileLayer && map.hasLayer(this.currentTileLayer)) {
                        map.removeLayer(this.currentTileLayer);
                    }
                    
                    const cdnLayer = L.tileLayer(tileUrls[1], {
                        attribution: MAP_CONFIG.TILE_ATTRIBUTION || 'OpenStreetMap contributors',
                        maxZoom: MAP_CONFIG.MAX_ZOOM || 19,
                        minZoom: MAP_CONFIG.MIN_ZOOM || 2
                    }).addTo(map);
                    
                    this.currentTileLayer = cdnLayer;
                    console.log('Switched to fallback tile server');
                } catch (e) {
                    console.error('Error switching tile servers:', e);
                }
            });

            tileLayer.addTo(map);
            this.currentTileLayer = tileLayer;
            return tileLayer;
        } catch (error) {
            console.error('Error adding tile layer:', error);
            return null;
        }
    }

    /**
     * Initialize logistics map
     */
    initLogisticsMap() {
        const container = document.getElementById("logisticsMap");
        if (!container) {
            console.error("Map container 'logisticsMap' not found in DOM");
            return;
        }

        console.log('Initializing logistics map...', {
            containerWidth: container.offsetWidth,
            containerHeight: container.offsetHeight,
            containerVisible: container.offsetParent !== null
        });

        // Clear existing map
        if (appState.logisticsMapInstance) {
            try {
                appState.logisticsMapInstance.remove();
            } catch (e) {
                console.warn("Could not remove existing map instance", e);
            }
            appState.logisticsMapInstance = null;
        }

        try {
            // Check if Leaflet is loaded
            if (typeof L === 'undefined') {
                console.error('Leaflet library not loaded');
                container.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#dc2626;"><i class="bi bi-exclamation-triangle me-2"></i>Map library not loaded</div>';
                return;
            }

            // Initialize map with error handling
            appState.logisticsMapInstance = L.map("logisticsMap", {
                preferCanvas: true,
                zoomControl: true
            }).setView(
                appState.mapCenter, 
                appState.mapZoom
            );

            console.log('Map instance created successfully');

            // Add tiles with fallback support
            this.addTileLayerWithFallback(appState.logisticsMapInstance);

            // Initialize marker cluster
            appState.pickupCluster = L.markerClusterGroup({ chunkedLoading: true });
            appState.logisticsMapInstance.addLayer(appState.pickupCluster);

            // Add cluster toggle control
            this.createClusterToggleControl(
                appState.logisticsMapInstance,
                "clusterToggleAdmin",
                true
            );

            // Add branch markers
            this.addBranchMarkers();

            // Load pickup data
            this.loadPickupsAndRender();

            // Trigger map resize multiple times to ensure proper rendering
            // This fixes the issue where map doesn't display in small containers
            const resizeMap = () => {
                if (appState.logisticsMapInstance) {
                    appState.logisticsMapInstance.invalidateSize();
                    console.log('Map resized');
                }
            };
            
            setTimeout(resizeMap, 100);
            setTimeout(resizeMap, 300);
            setTimeout(resizeMap, 500);
            setTimeout(resizeMap, 1000);

            console.log("Logistics map initialized successfully");
        } catch (error) {
            console.error("Error initializing logistics map:", error);
            console.error("Stack:", error.stack);
            container.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#dc2626;"><i class="bi bi-exclamation-triangle me-2"></i>Failed to initialize map</div>';
        }
    }

    /**
     * Setup modal map
     */
    setupModalMap() {
        const modalEl = document.getElementById("mapModal");
        if (!modalEl) {
            console.warn("Map modal not found in DOM");
            return;
        }

        modalEl.addEventListener("shown.bs.modal", () => {
            try {
                const modalMapContainer = document.getElementById("modalLogisticsMap");
                if (!modalMapContainer) {
                    console.error("Modal map container not found");
                    return;
                }

                if (!appState.modalMapInstance) {
                    appState.modalMapInstance = L.map("modalLogisticsMap", {
                        preferCanvas: true,
                        zoomControl: true
                    }).setView(
                        appState.mapCenter,
                        appState.mapZoom
                    );
                    this.addTileLayerWithFallback(appState.modalMapInstance);
                }

                // Sync markers - optimized with minimal delay
                const syncMarkers = () => {
                    setTimeout(() => {
                        if (appState.modalMapInstance) {
                            appState.modalMapInstance.invalidateSize();
                            this.syncModalMapMarkers();
                        }
                    }, 0);
                };
                
                // Use requestIdleCallback if available, otherwise immediate execution
                if (window.requestIdleCallback) {
                    requestIdleCallback(syncMarkers, { timeout: 100 });
                } else {
                    syncMarkers();
                }
            } catch (error) {
                console.error("Error setting up modal map:", error);
                console.error("Stack:", error.stack);
            }
        });
    }

    /**
     * Add branch markers to map
     */
    addBranchMarkers() {
        appState.branches.forEach((branch, index) => {
            const color = BRANCH_COLORS[index % BRANCH_COLORS.length];

            const marker = L.marker(
                [parseFloat(branch.latitude), parseFloat(branch.longitude)],
                {
                    icon: L.divIcon({
                        className: "branch-marker",
                        html: `<div style="background:${color};width:40px;height:40px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><i class="bi bi-shop" style="color:white;font-size:18px;"></i></div>`,
                        iconSize: [40, 40],
                        iconAnchor: [20, 40],
                    }),
                }
            ).addTo(appState.logisticsMapInstance).bindPopup(`
                <div style="min-width:220px">
                    <h6 class="mb-1"><b>${branch.name}</b></h6>
                    <p class="mb-1 small text-muted">${branch.address || "Negros Oriental, Philippines"}</p>
                    ${branch.phone ? `<p class="mb-1 small"><i class="bi bi-telephone me-1"></i>${branch.phone}</p>` : ""}
                    <hr class="my-2">
                    <div class="d-grid gap-1">
                        <button class="btn btn-sm btn-primary" onclick="showBranchInfo(${branch.id})">
                            <i class="bi bi-info-circle"></i> Branch Info
                        </button>
                    </div>
                </div>
            `);

            appState.branchMarkers.push(marker);
            appState.pickupMarkers.push(marker);
        });

        // Fallback branch if none exist
        if (appState.branches.length === 0) {
            const fallback = L.marker(MAP_CONFIG.DEFAULT_CENTER, {
                icon: L.divIcon({
                    className: "branch-marker",
                    html: '<div style="background:#007BFF;width:40px;height:40px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><i class="bi bi-shop" style="color:white;font-size:18px;"></i></div>',
                    iconSize: [40, 40],
                    iconAnchor: [20, 40],
                }),
            })
                .addTo(appState.logisticsMapInstance)
                .bindPopup("<b>WashBox Laundry</b>");
            appState.pickupMarkers.push(fallback);
        }
    }

    /**
     * Add pickup marker to map
     */
    addPickupMarker(pickup) {
        const color = STATUS_COLORS[pickup.status] || "#6C757D";

        const marker = L.marker(
            [parseFloat(pickup.latitude), parseFloat(pickup.longitude)],
            {
                icon: L.divIcon({
                    className: "pickup-marker",
                    html: `<div style="background:${color};width:32px;height:32px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;" id="marker-${pickup.id}"><i class="bi bi-geo-alt-fill" style="color:white;font-size:14px;"></i></div>`,
                    iconSize: [32, 32],
                    iconAnchor: [16, 32],
                }),
            }
        ).bindPopup(this.createPickupPopup(pickup));

        // Store pickup ID on marker for later removal
        marker.pickupId = pickup.id;

        // Add to cluster or map
        if (appState.pickupCluster) {
            appState.pickupCluster.addLayer(marker);
        } else {
            marker.addTo(appState.logisticsMapInstance);
        }

        appState.pickupMarkers.push(marker);
    }

    /**
     * Create pickup popup content
     */
    createPickupPopup(pickup) {
        const isSelected = appState.selectedPickups.has(parseInt(pickup.id));
        const selectBtnClass = isSelected ? "btn-purple" : "btn-outline-purple";
        const selectBtnIcon = isSelected ? "bi-check-square-fill" : "bi-check-square";
        const selectBtnText = isSelected ? "Selected" : "Select for Multi-Route";

        return `
            <div style="min-width:250px" class="pickup-${pickup.id}">
                <h6><b>${pickup.customer?.name || "Customer"}</b></h6>
                <p class="mb-1 small">${pickup.pickup_address || "No address"}</p>
                <span class="badge bg-${getStatusColor(pickup.status)}">${pickup.status}</span>
                <hr class="my-2">
                <div class="d-grid gap-1">
                    <button class="btn btn-sm ${selectBtnClass}" onclick="togglePickupSelection(${pickup.id}); this.blur();">
                        <i class="bi ${selectBtnIcon} me-1"></i> ${selectBtnText}
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="getRouteToPickup(${pickup.id})">
                        <i class="bi bi-signpost me-1"></i> Direct Route
                    </button>
                    <button class="btn btn-sm btn-success" onclick="startNavigationForPickup(${pickup.id})">
                        <i class="bi bi-play-circle me-1"></i> Start Navigation
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="viewPickupDetails(${pickup.id})">
                        <i class="bi bi-eye me-1"></i> View Details
                    </button>
                </div>
            </div>
        `;
    }

    /**
     * Load and render pickup data
     */
    loadPickupsAndRender() {
        console.log("Loading pickup data...");

        const pickupData = window.PENDING_PICKUPS || [];
        console.log("Pickup data:", pickupData);

        if (!pickupData || pickupData.length === 0) {
            console.log("No pickup data available");
            return;
        }

        // Clear existing pickup markers
        this.clearPickupMarkers();

        // Add pickup markers (exclude picked_up and cancelled)
        pickupData.forEach((pickup) => {
            if (pickup.latitude && pickup.longitude && pickup.status !== 'picked_up' && pickup.status !== 'cancelled') {
                this.addPickupMarker(pickup);
            } else if (!pickup.latitude || !pickup.longitude) {
                console.warn("Pickup missing coordinates:", pickup);
            }
        });

        // Fit bounds to show all markers
        this.fitMapToMarkers();
    }

    /**
     * Clear pickup markers
     */
    clearPickupMarkers() {
        if (appState.pickupCluster && appState.logisticsMapInstance) {
            appState.pickupCluster.clearLayers();
        }

        appState.pickupMarkers.forEach((marker) => {
            if (marker && appState.logisticsMapInstance) {
                try {
                    appState.logisticsMapInstance.removeLayer(marker);
                } catch (e) {
                    // ignore
                }
            }
        });
        appState.pickupMarkers = [];
    }

    /**
     * Remove specific pickup marker by ID
     */
    removePickupMarker(pickupId) {
        const markerIndex = appState.pickupMarkers.findIndex(m => m.pickupId === pickupId);
        if (markerIndex !== -1) {
            const marker = appState.pickupMarkers[markerIndex];
            if (appState.pickupCluster) {
                appState.pickupCluster.removeLayer(marker);
            } else if (appState.logisticsMapInstance) {
                appState.logisticsMapInstance.removeLayer(marker);
            }
            appState.pickupMarkers.splice(markerIndex, 1);
        }
        
        // Also remove from modal map
        if (appState.modalMapInstance) {
            appState.modalMapInstance.eachLayer((layer) => {
                if (layer.pickupId === pickupId) {
                    appState.modalMapInstance.removeLayer(layer);
                }
            });
        }
    }

    /**
     * Fit map to show all markers
     */
    fitMapToMarkers() {
        if (appState.pickupCluster && appState.pickupCluster.getLayers().length > 0) {
            const bounds = appState.pickupCluster.getBounds();
            if (bounds.isValid && bounds.isValid()) {
                appState.logisticsMapInstance.fitBounds(bounds.pad(0.1));
                return;
            }
        }

        if (appState.pickupMarkers.length === 0) return;

        const group = new L.featureGroup(appState.pickupMarkers);
        appState.logisticsMapInstance.fitBounds(group.getBounds().pad(0.1));
    }

    /**
     * Create cluster toggle control
     */
    createClusterToggleControl(map, id, defaultOn = true) {
        const ClusterControl = L.Control.extend({
            onAdd: function (map) {
                const container = L.DomUtil.create(
                    "div",
                    "leaflet-bar cluster-toggle-control p-2 bg-white rounded shadow-sm"
                );
                container.innerHTML = `
                    <div class="form-check m-1">
                        <input class="form-check-input" type="checkbox" id="${id}" ${defaultOn ? "checked" : ""}>
                        <label class="form-check-label small ms-1" for="${id}">Cluster pickups</label>
                    </div>
                `;
                L.DomEvent.disableClickPropagation(container);
                return container;
            },
        });

        map.addControl(new ClusterControl({ position: "topright" }));

        // Wire up change handler - optimized with immediate setup
        const setupEventHandler = () => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener("change", (e) => {
                // Use requestAnimationFrame to prevent blocking
                requestAnimationFrame(() => {
                    if (e.target.checked) {
                        if (appState.pickupCluster) map.addLayer(appState.pickupCluster);
                    } else {
                        if (appState.pickupCluster) map.removeLayer(appState.pickupCluster);
                    }
                });
            });
        };
        
        // Setup immediately to avoid delays
        setTimeout(setupEventHandler, 0);
    }

    /**
     * Add draggable search marker
     */
    addDraggableSearchMarker(lat, lon, address) {
        // Remove previous marker
        if (this.searchResultMarker && appState.logisticsMapInstance) {
            appState.logisticsMapInstance.removeLayer(this.searchResultMarker);
        }

        // Create draggable marker
        this.searchResultMarker = L.marker([lat, lon], {
            icon: L.divIcon({
                className: "search-result-marker",
                html: `<div class="search-marker-pulse" style="background:#10B981;width:44px;height:44px;border-radius:50%;border:4px solid white;box-shadow:0 4px 12px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-geo-alt-fill" style="color:white;font-size:22px;"></i>
                       </div>`,
                iconSize: [44, 44],
                iconAnchor: [22, 44],
            }),
            draggable: true,
            autoPan: true,
        }).addTo(appState.logisticsMapInstance);

        // Handle drag events
        this.searchResultMarker.on("dragend", async (event) => {
            const newPos = event.target.getLatLng();
            await this.updateMarkerLocation(newPos.lat, newPos.lng);
        });

        // Add popup
        this.updateSearchMarkerPopup(lat, lon, address);
        this.searchResultMarker.openPopup();
    }

    /**
     * Update search marker popup
     */
    updateSearchMarkerPopup(lat, lon, address) {
        if (!this.searchResultMarker) return;

        const popupContent = `
            <div style="min-width: 280px;">
                <h6 class="mb-2"><b>📍 Selected Location</b></h6>
                <p class="mb-2 small text-muted">${address}</p>
                <div class="alert alert-info py-2 px-2 mb-2 small">
                    <strong>Coordinates:</strong><br>
                    Lat: ${lat.toFixed(6)}<br>
                    Lon: ${lon.toFixed(6)}
                </div>
                <p class="small text-muted mb-2">
                    <i class="bi bi-info-circle me-1"></i>Drag marker to adjust location
                </p>
                <hr class="my-2">
                <div class="d-grid gap-2">
                    <button class="btn btn-sm btn-success" onclick="createPickupAtLocation(${lat}, ${lon}, '${address.replace(/'/g, "\\'")}'">
                        <i class="bi bi-plus-circle me-1"></i> Create Pickup Here
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="getRouteToSearchLocation(${lat}, ${lon})">
                        <i class="bi bi-arrow-right-circle me-1"></i> Calculate Route
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="copyCoordinates(${lat}, ${lon})">
                        <i class="bi bi-clipboard me-1"></i> Copy Coordinates
                    </button>
                </div>
            </div>
        `;

        this.searchResultMarker.bindPopup(popupContent);
    }

    /**
     * Update marker location when dragged
     */
    async updateMarkerLocation(newLat, newLng) {
        try {
            const newAddress = await apiClient.reverseGeocode(newLat, newLng);

            // Batch DOM updates to prevent forced reflows
            requestAnimationFrame(() => {
                const coordsEl = document.getElementById("result-coords-text");
                if (coordsEl) {
                    coordsEl.textContent = `📍 ${newLat.toFixed(6)}, ${newLng.toFixed(6)}`;
                }
                
                // Update popup
                this.updateSearchMarkerPopup(newLat, newLng, newAddress);
            });

            showToast("📍 Location updated", "info");
        } catch (error) {
            console.error("Reverse geocoding error:", error);
            requestAnimationFrame(() => {
                this.updateSearchMarkerPopup(newLat, newLng, "Adjusted Location");
            });
        }
    }

    /**
     * Sync modal map markers
     */
    syncModalMapMarkers() {
        // Clear existing markers
        appState.modalMapInstance.eachLayer((layer) => {
            if (layer instanceof L.Marker || layer instanceof L.Polyline) {
                appState.modalMapInstance.removeLayer(layer);
            }
        });

        // Re-add tile layer
        this.addTileLayerWithFallback(appState.modalMapInstance);

        // Add branch markers
        this.addBranchesToModalMap();

        // Add pickup markers
        const pickupData = window.PENDING_PICKUPS || [];
        pickupData.forEach((pickup) => {
            if (pickup.latitude && pickup.longitude && pickup.status !== 'picked_up' && pickup.status !== 'cancelled') {
                this.addPickupMarkerToModalMap(pickup);
            }
        });
    }

    /**
     * Add branches to modal map
     */
    addBranchesToModalMap() {
        appState.branches.forEach((branch, index) => {
            const color = BRANCH_COLORS[index % BRANCH_COLORS.length];
            L.marker([parseFloat(branch.latitude), parseFloat(branch.longitude)], {
                icon: L.divIcon({
                    className: "branch-marker",
                    html: `<div style="background:${color};width:40px;height:40px;border-radius:50%;border:3px solid white;display:flex;align-items:center;justify-content:center;"><i class="bi bi-shop" style="color:white;"></i></div>`,
                    iconSize: [40, 40],
                }),
            })
                .addTo(appState.modalMapInstance)
                .bindPopup(`<b>${branch.name}</b><br><small>${branch.address || ""}</small>`);
        });

        if (appState.branches.length === 0) {
            L.marker(MAP_CONFIG.DEFAULT_CENTER, {
                icon: L.divIcon({
                    className: "branch-marker",
                    html: '<div style="background:#007BFF;width:40px;height:40px;border-radius:50%;border:3px solid white;display:flex;align-items:center;justify-content:center;"><i class="bi bi-shop" style="color:white;"></i></div>',
                    iconSize: [40, 40],
                }),
            })
                .addTo(appState.modalMapInstance)
                .bindPopup("<b>WashBox Laundry</b>");
        }
    }

    /**
     * Add pickup marker to modal map
     */
    addPickupMarkerToModalMap(pickup) {
        const color = STATUS_COLORS[pickup.status] || "#6C757D";

        const modalMarker = L.marker(
            [parseFloat(pickup.latitude), parseFloat(pickup.longitude)],
            {
                icon: L.divIcon({
                    className: "pickup-marker",
                    html: `<div style="background:${color};width:32px;height:32px;border-radius:50%;border:3px solid white;display:flex;align-items:center;justify-content:center;"><i class="bi bi-geo-alt-fill" style="color:white;font-size:14px;"></i></div>`,
                    iconSize: [32, 32],
                }),
            }
        ).bindPopup(this.createPickupPopup(pickup));

        // Store pickup ID on marker for later removal
        modalMarker.pickupId = pickup.id;

        if (appState.modalPickupCluster) {
            appState.modalPickupCluster.addLayer(modalMarker);
        } else {
            modalMarker.addTo(appState.modalMapInstance);
        }
    }

    /**
     * Refresh map markers
     */
    refreshMapMarkers() {
        if (appState.logisticsMapInstance) {
            this.loadPickupsAndRender();
            if (appState.branchMarkers.length > 1) {
                const group = new L.featureGroup(appState.branchMarkers);
                appState.logisticsMapInstance.fitBounds(group.getBounds().pad(0.2));
            } else {
                appState.logisticsMapInstance.setView(appState.mapCenter, appState.mapZoom);
            }
            showToast("Map refreshed", "info");
        }
    }

    /**
     * Update marker selection visual state
     */
    updateMarkerSelection(pickupId, isSelected) {
        // Use requestAnimationFrame to batch DOM updates
        requestAnimationFrame(() => {
            const markerEl = document.getElementById(`marker-${pickupId}`);
            if (markerEl) {
                if (isSelected) {
                    markerEl.style.cssText = 'border: 3px solid #6f42c1; box-shadow: 0 0 10px rgba(111, 66, 193, 0.5);';
                } else {
                    markerEl.style.cssText = 'border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);';
                }
            }
        });
    }

    /**
     * Clear all marker selections
     */
    clearAllMarkerSelections() {
        // Batch DOM operations to prevent forced reflows
        requestAnimationFrame(() => {
            const markers = document.querySelectorAll('[id^="marker-"]');
            const defaultStyle = 'border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);';
            
            markers.forEach(marker => {
                marker.style.cssText = defaultStyle;
            });
        });
    }

    /**
     * Update vehicle location on map
     */
    updateVehicleLocation(location) {
        // Placeholder for vehicle tracking marker update
        console.log('Vehicle location updated:', location);
    }

    /**
     * Draw single route on map
     */
    drawSingleRoute(routeData) {
        // Delegate to routing module
        if (window.routeManager) {
            window.routeManager.drawSingleRoute(routeData);
        }
    }

    /**
     * Draw multi-stop route on map
     */
    async drawMultiStopRoute(routeData) {
        // Delegate to routing module
        if (window.routeManager) {
            await window.routeManager.drawMultiStopRoute(routeData);
        }
    }

    /**
     * Clear route from map
     */
    clearRoute() {
        // Delegate to routing module
        if (window.routeManager) {
            window.routeManager.clearRoute();
        }
    }
}

// Create singleton instance
export const mapManager = new MapManager();

// Make it globally available for backward compatibility
window.mapManager = mapManager;
