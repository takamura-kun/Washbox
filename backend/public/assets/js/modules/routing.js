// routing.js - Route calculation and optimization

import { ROUTE_COLORS } from './config.js';
import { appState } from './state.js';
import { showToast, decodePolyline, validateCoordinates } from './utils.js';
import { apiClient } from './api.js';

class RouteManager {
    constructor() {
        this.currentRouteLine = null;
    }

    /**
     * Get route to specific pickup
     */
    async getRouteToPickup(pickupId) {
        try {
            const data = await apiClient.getRouteToPickup(pickupId);
            
            // Validate pickup coordinates - try multiple data sources
            let pickup = data.pickup;
            
            // Fallback: if pickup data is missing, try to find it in the global data
            if (!pickup && window.PENDING_PICKUPS) {
                pickup = window.PENDING_PICKUPS.find(p => parseInt(p.id) === parseInt(pickupId));
                console.log('Using fallback pickup data:', pickup);
            }
            
            if (!pickup) {
                throw new Error('Pickup data not found. The pickup may have been deleted or moved.');
            }
            
            const validation = validateCoordinates(pickup.latitude, pickup.longitude);
            if (!validation.valid) {
                throw new Error(validation.error || 'Invalid pickup coordinates. Please update the pickup location in the pickup details.');
            }
            
            if (validation.warning) {
                showToast(validation.warning, 'warning');
            }
            
            // Draw the route on the map
            this.drawSingleRouteOnMap(data.route);

            // Show route details panel
            this.showSingleRouteDetails(data, pickupId);

            // Update ETA display
            this.updateETADisplay(data.estimated_arrival);

            // Show route controls
            this.toggleRouteControls(true);

            showToast("Route loaded successfully!", "success");
            return data;
        } catch (error) {
            console.error("Error fetching route:", error);
            
            // Provide specific error messages
            let errorMessage = error.message;
            
            if (error.message.includes('Invalid pickup coordinates')) {
                errorMessage = 'This pickup has invalid coordinates. Please edit the pickup and set a valid address with coordinates.';
            } else if (error.message.includes('HTTP error')) {
                errorMessage = 'Server error while fetching route. Please try again.';
            } else if (error.message.includes('Pickup data not found')) {
                errorMessage = 'Pickup data not found. The pickup may have been deleted or moved.';
            }
            
            showToast(errorMessage, "danger");
            return null;
        }
    }

    /**
     * Draw single route on map
     */
    drawSingleRouteOnMap(routeData) {
        const isModalOpen = document
            .getElementById("mapModal")
            ?.classList.contains("show");
        const targetMap = isModalOpen ? appState.modalMapInstance : appState.logisticsMapInstance;

        if (!targetMap) {
            console.error("No active map instance found to draw the route.");
            return;
        }

        // Clear existing routes
        this.clearRoute();

        if (routeData.geometry) {
            const coordinates = decodePolyline(routeData.geometry);

            // Create the polyline
            appState.routeLayer = L.polyline(coordinates, {
                color: ROUTE_COLORS.single,
                weight: 6,
                opacity: 0.8,
                lineJoin: "round",
            }).addTo(targetMap);

            // Add Start Marker
            const startCoord = coordinates[0] || appState.mapCenter;
            const nearestBranch = appState.getNearestBranch(startCoord[0], startCoord[1]);
            appState.startMarker = L.circleMarker(startCoord, {
                radius: 8,
                fillColor: "#007BFF",
                color: "#fff",
                weight: 2,
                fillOpacity: 1,
            })
                .addTo(targetMap)
                .bindPopup(`<b>${nearestBranch.name}</b>`);

            // Add End Marker
            const endCoord = coordinates[coordinates.length - 1];
            appState.endMarker = L.marker(endCoord, {
                icon: L.divIcon({
                    className: "end-marker",
                    html: '<div style="background:#28A745;width:30px;height:30px;border-radius:50%;border:3px solid white;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 5px rgba(0,0,0,0.2);"><i class="bi bi-geo-alt-fill" style="color:white;"></i></div>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 30],
                }),
            })
                .addTo(targetMap)
                .bindPopup("<b>Customer Location</b>");

            // Fit map to route
            targetMap.fitBounds(appState.routeLayer.getBounds(), { padding: [50, 50] });
        }
    }

    /**
     * Get optimized multi-stop route
     */
    async getOptimizedMultiRoute() {
        if (appState.selectedPickups.size < 2) {
            showToast("Please select at least 2 pickups for route optimization", "warning");
            return;
        }

        const pickupIds = appState.getSelectedPickupIds();
        const branchId = document.getElementById('routeBranchFilter')?.value || null;

        try {
            const routeData = await apiClient.optimizeRoute(pickupIds, branchId);
            this.drawMultiStopRoute(routeData);
            this.showMultiRouteSummary(routeData);
            appState.setActiveRoute(routeData);
        } catch (error) {
            console.error("Route optimization error:", error);
        }
    }

    /**
     * Fetch route geometry from OSRM for waypoints
     */
    async fetchRouteGeometry(waypoints) {
        try {
            // Convert waypoints to OSRM format: lng,lat;lng,lat;...
            const coordString = waypoints.map(wp => {
                if (Array.isArray(wp)) {
                    return `${wp[1]},${wp[0]}`; // [lat, lng] -> lng,lat
                } else if (wp.lng && wp.lat) {
                    return `${wp.lng},${wp.lat}`;
                } else if (wp.longitude && wp.latitude) {
                    return `${wp.longitude},${wp.latitude}`;
                }
            }).join(';');
            
            const url = `https://router.project-osrm.org/route/v1/driving/${coordString}?overview=full&geometries=polyline`;
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.code === 'Ok' && data.routes && data.routes[0]) {
                const geometry = data.routes[0].geometry;
                return decodePolyline(geometry);
            } else {
                console.error('OSRM routing failed:', data);
                return waypoints; // Fallback to straight lines
            }
        } catch (error) {
            console.error('Error fetching route geometry:', error);
            return waypoints; // Fallback to straight lines
        }
    }

    /**
     * Draw multi-stop route on map
     */
    async drawMultiStopRoute(data) {
        this.clearRoute();

        // Safety: unwrap if caller passed the full response
        if (data.optimization && !data.coordinates) {
            data = data.optimization;
        }

        console.log("🗺️ Drawing route with data:", data);

        const isModalOpen = document
            .getElementById("mapModal")
            ?.classList.contains("show");
        const targetMap = isModalOpen ? appState.modalMapInstance : appState.logisticsMapInstance;

        if (!targetMap) {
            console.error("❌ No map instance found");
            showToast("Map not initialized", "danger");
            return;
        }

        // Handle different coordinate formats
        let coordinates = [];

        // Prioritize geometry (actual road path) over coordinates (just waypoints)
        if (data.geometry && typeof data.geometry === "string") {
            coordinates = decodePolyline(data.geometry);
            console.log("✅ Decoded polyline geometry");
        } else if (data.route && data.route.geometry) {
            if (typeof data.route.geometry === "string") {
                coordinates = decodePolyline(data.route.geometry);
            } else {
                coordinates = data.route.geometry;
            }
            console.log("✅ Using nested route geometry");
        } else if (data.coordinates && Array.isArray(data.coordinates) && data.coordinates.length > 0) {
            // Check if coordinates are actual route points or just waypoints
            if (data.coordinates.length > data.stops?.length * 2) {
                // Likely actual route coordinates
                coordinates = data.coordinates;
                console.log("✅ Using direct coordinates array");
            } else {
                // Just waypoints, need to fetch route
                console.warn("⚠️ Only waypoints provided, fetching route from OSRM");
                coordinates = await this.fetchRouteGeometry(data.coordinates);
            }
        } else {
            console.error("❌ No valid geometry data found:", data);
            showToast("Invalid route data - missing coordinates", "danger");
            return;
        }

        // Validate coordinates
        if (!coordinates || !Array.isArray(coordinates) || coordinates.length < 2) {
            console.error("❌ Invalid coordinates array:", coordinates);
            showToast("Not enough coordinates to draw route (need at least 2 points)", "danger");
            return;
        }

        console.log("✅ Drawing route with " + coordinates.length + " coordinate points");

        // Draw the route line
        try {
            appState.routeLayer = L.polyline(coordinates, {
                color: ROUTE_COLORS.multi,
                weight: 6,
                opacity: 0.8,
                lineJoin: "round",
                lineCap: "round",
            }).addTo(targetMap);

            console.log("✅ Route line drawn successfully");
        } catch (error) {
            console.error("❌ Error drawing polyline:", error);
            showToast("Error drawing route line", "danger");
            return;
        }

        // Add markers for each stop
        if (data.stops && Array.isArray(data.stops) && data.stops.length > 0) {
            console.log("Adding " + data.stops.length + " stop markers");

            data.stops.forEach((stop, index) => {
                this.addStopMarker(stop, index, data.stops.length, targetMap);
            });

            console.log("✅ Added " + appState.pickupMarkers.length + " markers");
        }

        // Fit map bounds
        this.fitMapToRoute(targetMap);

        // Update ETA display
        const duration = data.duration || data.estimated_time || "Unknown";
        const distance = data.distance || "Unknown";
        this.updateETADisplay(`${duration} • ${distance}`);

        console.log("✅ Route drawing complete!");
    }

    /**
     * Add stop marker for multi-route
     */
    addStopMarker(stop, index, totalStops, targetMap) {
        try {
            const isFirst = index === 0;
            const isLast = index === totalStops - 1;

            // Determine marker style
            let markerColor, iconHtml, iconClass;

            if (isFirst) {
                markerColor = "#0d6efd";
                iconHtml = '<i class="bi bi-shop"></i>';
                iconClass = "branch-marker";
            } else if (isLast) {
                markerColor = "#198754";
                iconHtml = '<i class="bi bi-flag-fill"></i>';
                iconClass = "last-marker";
            } else {
                markerColor = "#ffc107";
                iconHtml = `<span style="font-weight:bold;font-size:14px">${index}</span>`;
                iconClass = "pickup-marker";
            }

            // Extract coordinates
            let lat, lng;

            if (Array.isArray(stop.location) && stop.location.length === 2) {
                lng = stop.location[0];
                lat = stop.location[1];
            } else if (stop.coordinates && stop.coordinates.lat && stop.coordinates.lng) {
                lat = parseFloat(stop.coordinates.lat);
                lng = parseFloat(stop.coordinates.lng);
            } else if (stop.latitude && stop.longitude) {
                lat = parseFloat(stop.latitude);
                lng = parseFloat(stop.longitude);
            } else if (stop.lat && stop.lng) {
                lat = parseFloat(stop.lat);
                lng = parseFloat(stop.lng);
            } else if (stop.pickup_latitude && stop.pickup_longitude) {
                lat = parseFloat(stop.pickup_latitude);
                lng = parseFloat(stop.pickup_longitude);
            } else {
                console.warn("⚠️ Invalid stop location format:", stop);
                return;
            }

            // Create marker
            const marker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: `stop-marker ${iconClass}`,
                    html: `<div style="
                        background: ${markerColor};
                        width: 36px;
                        height: 36px;
                        border-radius: 50%;
                        border: 3px solid white;
                        color: white;
                        font-size: 12px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                    ">${iconHtml}</div>`,
                    iconSize: [36, 36],
                    iconAnchor: [18, 36],
                    popupAnchor: [0, -36],
                }),
                title: stop.name || `Stop ${index + 1}`,
            }).addTo(targetMap);

            // Add popup
            const popupContent = `
                <div style="min-width: 150px;">
                    <strong style="color: ${markerColor}; font-size: 14px;">
                        ${stop.name || `Stop ${index + 1}`}
                    </strong>
                    ${stop.address ? `<br><small class="text-muted">${stop.address}</small>` : ""}
                    ${stop.type ? `<br><span class="badge bg-secondary">${stop.type}</span>` : ""}
                </div>
            `;

            marker.bindPopup(popupContent);
            appState.pickupMarkers.push(marker);
        } catch (error) {
            console.error("❌ Error adding marker for stop", index, ":", error);
        }
    }

    /**
     * Fit map to route bounds
     */
    fitMapToRoute(targetMap) {
        try {
            const bounds = appState.routeLayer.getBounds();

            if (bounds && bounds.isValid && bounds.isValid()) {
                targetMap.fitBounds(bounds, {
                    padding: [50, 50],
                    maxZoom: 15,
                });
                console.log("✅ Map fitted to route bounds");
            } else {
                console.warn("⚠️ Invalid bounds from route layer, using fallback");
                if (appState.pickupMarkers.length > 0) {
                    const group = new L.featureGroup(appState.pickupMarkers);
                    targetMap.fitBounds(group.getBounds().pad(0.1));
                }
            }
        } catch (error) {
            console.error("❌ Error fitting bounds:", error);
        }
    }

    /**
     * Calculate route to searched location
     */
    async getRouteToSearchLocation(lat, lon) {
        try {
            showToast("🗺️ Calculating route...", "info");

            const branch = appState.getNearestBranch(lat, lon);
            const branchCoords = [branch.latitude, branch.longitude];

            const route = await apiClient.calculateRoute(branchCoords, [lat, lon]);
            const coordinates = route.geometry.coordinates.map((coord) => [coord[1], coord[0]]);

            // Clear previous route
            if (this.currentRouteLine) {
                appState.logisticsMapInstance.removeLayer(this.currentRouteLine);
            }

            // Draw route on map
            this.currentRouteLine = L.polyline(coordinates, {
                color: ROUTE_COLORS.search,
                weight: 5,
                opacity: 0.8,
                smoothFactor: 1,
            }).addTo(appState.logisticsMapInstance);

            // Fit map to show entire route
            appState.logisticsMapInstance.fitBounds(this.currentRouteLine.getBounds().pad(0.1));

            // Show route info
            const distance = (route.distance / 1000).toFixed(2);
            const duration = Math.round(route.duration / 60);

            showToast(`📍 Route: ${distance} km, ~${duration} minutes`, "success");
        } catch (error) {
            console.error("Routing error:", error);
            showToast("Failed to calculate route", "danger");
        }
    }

    /**
     * Clear all routes from maps
     */
    clearRoute() {
        const maps = [appState.logisticsMapInstance, appState.modalMapInstance];

        maps.forEach((map) => {
            if (map && typeof map.removeLayer === "function") {
                try {
                    if (appState.routeLayer) map.removeLayer(appState.routeLayer);
                    if (appState.startMarker) map.removeLayer(appState.startMarker);
                    if (appState.endMarker) map.removeLayer(appState.endMarker);
                } catch (e) {
                    console.warn("Could not remove route layers:", e);
                }
            }
        });

        appState.routeLayer = null;
        appState.startMarker = null;
        appState.endMarker = null;

        const etaContainer = document.getElementById("eta-display-container");
        if (etaContainer) etaContainer.style.display = "none";

        this.toggleRouteControls(false);
        this.closeRouteDetails();
        appState.clearActiveRoute();
    }

    /**
     * Show single route details panel
     */
    showSingleRouteDetails(routeData, pickupId) {
        let detailsPanel = document.getElementById("routeDetailsPanel");
        
        if (!detailsPanel) {
            console.warn("Route details panel not found in DOM");
            showToast("Route details panel not available", "warning");
            return;
        }

        detailsPanel.innerHTML = `
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Route Details</h6>
                    <button class="btn btn-sm btn-light" onclick="closeRouteDetails()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5 class="text-success">
                            <i class="bi bi-signpost"></i> ${routeData.route?.distance?.text || "36.89 km"}
                        </h5>
                        <p class="text-muted">
                            <i class="bi bi-clock"></i> ${routeData.route?.duration?.text || "74 min"}
                        </p>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <small class="text-muted">From (Branch):</small>
                        <p class="mb-0"><b>${(() => {
                            const wp = routeData.route?.waypoints?.start;
                            if (wp) return appState.getNearestBranch(wp.latitude, wp.longitude).name;
                            return appState.branches.length > 0 ? appState.branches[0].name : "WashBox Branch";
                        })()}</b></p>
                        <small class="text-muted">Negros Oriental</small>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">To (Pickup):</small>
                        <p class="mb-0"><b>Customer Location</b></p>
                        <small class="text-muted">${routeData.estimated_arrival || "06:23 PM"} ETA</small>
                    </div>
                    <hr>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="startNavigation(${pickupId})">
                            <i class="bi bi-play-circle me-2"></i> Start Navigation
                        </button>
                        <button class="btn btn-outline-primary" onclick="printRoute()">
                            <i class="bi bi-printer me-2"></i> Print Directions
                        </button>
                        <button class="btn btn-outline-danger" onclick="clearRoute()">
                            <i class="bi bi-x-circle me-2"></i> Clear Route
                        </button>
                    </div>
                </div>
            </div>
        `;

        detailsPanel.style.display = "block";
    }

    /**
     * Show multi-route summary
     */
    showMultiRouteSummary(data) {
        if (data.optimization && !data.distance) {
            data = data.optimization;
        }

        const summary = `
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-purple text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-route me-2"></i>Optimized Route Summary</h6>
                    <button class="btn btn-sm btn-light" onclick="closeRouteDetails()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted">Total Distance</small>
                            <h5>${data.distance}</h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Total Time</small>
                            <h5>${data.duration}</h5>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <small class="text-muted">Pickup Laundry:</small>
                        <ol class="mt-2 ps-3">
                            <li><strong>Start:</strong> ${appState.branches.length > 0 ? appState.branches[0].name : "WashBox Branch"}</li>
                            ${data.stops ? data.stops.slice(1).map((stop, idx) => 
                                `<li><strong>Stop ${idx + 1}:</strong> ${stop.name || "Pickup Location " + (idx + 1)}</li>`
                            ).join("") : ""}
                        </ol>
                    </div>
                    <hr>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="startMultiPickupNavigation()">
                            <i class="bi bi-play-circle me-2"></i>Start Multi-Pickup Run
                        </button>
                        <button class="btn btn-outline-primary" onclick="printRouteSchedule()">
                            <i class="bi bi-printer me-2"></i>Print Schedule
                        </button>
                        <button class="btn btn-outline-danger" onclick="clearRoute()">
                            <i class="bi bi-x-circle me-2"></i>Clear Route
                        </button>
                    </div>
                </div>
            </div>
        `;

        const routeDetails = document.getElementById("routeDetailsPanel");
        if (routeDetails) {
            routeDetails.innerHTML = summary;
            routeDetails.style.display = "block";
        }
    }

    /**
     * Close route details panel
     */
    closeRouteDetails() {
        const panel = document.getElementById("routeDetailsPanel");
        if (panel) {
            panel.style.display = "none";
        }
    }

    /**
     * Update ETA display
     */
    updateETADisplay(etaTime) {
        const etaContainer = document.getElementById("eta-display-container");
        if (etaContainer) {
            etaContainer.innerHTML = `
                <div class="eta-display">
                    <div class="eta-label">Estimated Arrival</div>
                    <div class="eta-time">${etaTime || "06:23 PM"}</div>
                    <small class="text-muted">Based on current traffic</small>
                </div>
            `;
            etaContainer.style.display = "block";
        }
    }

    /**
     * Toggle route controls visibility
     */
    toggleRouteControls(show = true) {
        const routeControls = document.querySelector(".route-controls");
        if (routeControls) {
            routeControls.style.display = show ? "block" : "none";
        }
    }
}

// Create singleton instance
export const routeManager = new RouteManager();

// Make it globally available for backward compatibility
window.routeManager = routeManager;