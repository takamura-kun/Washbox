// admin.js - Modular Admin Dashboard
// This file orchestrates all the modular components

import { DASHBOARD_CONFIG, MAP_CONFIG, STATUS_COLORS, BRANCH_COLORS, ROUTE_COLORS } from './modules/config.js';
import * as Utils from './modules/utils.js';
import { appState as State } from './modules/state.js';
import { apiClient as API } from './modules/api.js';
import { mapManager as Maps } from './modules/maps.js';
import { routeManager as Routing } from './modules/routing.js';
import { chartManager as Charts } from './modules/charts.js';
import { eventBus as EventBus } from './utils/eventBus.js';
import { errorBoundary as ErrorBoundary } from './utils/errorBoundary.js';
import { performanceMonitor as PerformanceMonitor } from './utils/performanceMonitor.js';
import { taskScheduler } from './utils/taskScheduler.js';
import { pickupService as PickupService } from './services/pickupService.js';
import { trackingService } from './services/trackingService.js';
import { routeOptimizer } from './services/routeOptimizer.js';
import { analyticsService } from './services/analyticsService.js';
import { loadingManager } from './utils/loadingManager.js';
import { keyboardManager } from './utils/keyboardManager.js';
import { accessibilityManager } from './utils/accessibilityManager.js';
import * as Components from './components/components.js';

class AdminDashboard {
    constructor() {
        this.initialized = false;
        this.modules = {};
        this.init();
    }

    async init() {
        try {
            PerformanceMonitor.startTimer('dashboard_init');
            
            // Error boundary is already initialized when imported
            
            // Initialize core modules
            await this.initializeModules();
            
            // Setup event listeners
            this.setupEventListeners();
            
            // Initialize dashboard when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.initializeDashboard());
            } else {
                this.initializeDashboard();
            }
            
            PerformanceMonitor.endTimer('dashboard_init');
            this.initialized = true;
            
            console.log('✅ Modular Admin Dashboard initialized successfully');
            
        } catch (error) {
            ErrorBoundary.handleError(error, 'AdminDashboard.init');
        }
    }

    async initializeModules() {
        // Initialize modules in dependency order
        this.modules.utils = Utils;
        this.modules.state = State;
        this.modules.api = API;
        this.modules.maps = Maps;
        this.modules.routing = Routing;
        this.modules.charts = Charts;
        this.modules.pickupService = PickupService;
        
        // Initialize advanced services
        this.modules.tracking = trackingService;
        this.modules.routeOptimizer = routeOptimizer;
        this.modules.analytics = analyticsService;
        
        // Initialize UX enhancements
        this.modules.loading = loadingManager;
        this.modules.keyboard = keyboardManager;
        this.modules.accessibility = accessibilityManager;
        
        // Initialize components
        // Components are available as classes, not a singleton
        console.log('📦 Components available:', Object.keys(Components));
        
        // Initialize dashboard data if provided and state module is ready
        if (window.BRANCHES && this.modules.state) {
            this.initializeDashboardData(window.BRANCHES, window.DASHBOARD_STATS);
        }
        
        console.log('📦 All modules initialized');
    }

    setupEventListeners() {
        // Dashboard events
        EventBus.on('DASHBOARD_REFRESH', () => this.refreshDashboard());
        EventBus.on('TAB_CHANGED', (data) => this.handleTabChange(data));
        
        // Map events
        EventBus.on('MAP_INITIALIZED', () => this.onMapReady());
        EventBus.on('PICKUP_SELECTED', (data) => this.handlePickupSelection(data));
        EventBus.on('ROUTE_CALCULATED', (data) => this.handleRouteCalculated(data));
        
        // Advanced features events
        EventBus.on('TRACKING_STARTED', (data) => this.handleTrackingStarted(data));
        EventBus.on('LOCATION_UPDATED', (data) => this.handleLocationUpdate(data));
        EventBus.on('ROUTE_OPTIMIZED', (data) => this.handleRouteOptimized(data));
        EventBus.on('ANALYTICS_UPDATED', (data) => this.handleAnalyticsUpdate(data));
        EventBus.on('ANALYTICS_ALERT', (data) => this.handleAnalyticsAlert(data));
        
        // Error events
        EventBus.on('ERROR_OCCURRED', (error) => this.handleError(error));
        
        console.log('🔗 Event listeners setup complete');
    }

    initializeDashboard() {
        // Defer all dashboard initialization to window.load to prevent blocking DOMContentLoaded
        const initWhenReady = () => {
            setTimeout(() => {
                if (window.performanceGuard) {
                    window.performanceGuard.pauseMonitoring();
                }
                
                const dashboardTasks = [
                    () => this.initializeCharts(),
                    () => this.initializeTabs(),
                    () => this.initializeAutoRefresh(),
                    () => this.initializeDateUpdater(),
                    () => this.initMapAddressSearch(),
                    () => {
                        if (document.getElementById('operations-tab')?.classList.contains('active')) {
                            taskScheduler.schedule(() => this.modules.maps.initLogisticsMap(), 500);
                        }
                    },
                    () => this.modules.maps.setupModalMap(),
                    () => EventBus.emit('DASHBOARD_INITIALIZED'),
                    () => {
                        if (window.performanceGuard) {
                            window.performanceGuard.resumeMonitoring();
                        }
                    }
                ];
                
                let taskIndex = 0;
                
                const executeNextBatch = () => {
                    if (taskIndex >= dashboardTasks.length) return;
                    
                    // Use requestIdleCallback for better performance
                    if ('requestIdleCallback' in window) {
                        requestIdleCallback(() => {
                            if (taskIndex < dashboardTasks.length) {
                                try {
                                    dashboardTasks[taskIndex]();
                                } catch (error) {
                                    ErrorBoundary.handleError(error, `AdminDashboard.initializeDashboard task ${taskIndex}`);
                                }
                                taskIndex++;
                                executeNextBatch();
                            }
                        }, { timeout: 100 });
                    } else {
                        setTimeout(() => {
                            if (taskIndex < dashboardTasks.length) {
                                try {
                                    dashboardTasks[taskIndex]();
                                } catch (error) {
                                    ErrorBoundary.handleError(error, `AdminDashboard.initializeDashboard task ${taskIndex}`);
                                }
                                taskIndex++;
                                executeNextBatch();
                            }
                        }, 0);
                    }
                };
                
                executeNextBatch();
            }, 200);
        };
        
        // Wait for window.load instead of DOMContentLoaded
        if (document.readyState === 'complete') {
            initWhenReady();
        } else {
            window.addEventListener('load', initWhenReady);
        }
    }

    initializeDashboardData(branches, stats) {
        if (!this.modules.state) {
            console.error('State module not initialized');
            return;
        }
        
        this.modules.state.initializeDashboardData(branches, stats);
        
        console.log(`📍 Loaded ${branches.length} branch location(s) for map`);
    }

    initializeCharts() {
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js is not loaded. Charts will not be displayed.');
            return;
        }
        
        this.modules.charts.initializeCharts();
    }

    initializeTabs() {
        const activeTab = localStorage.getItem(DASHBOARD_CONFIG.cacheKey) || 'overview';
        const tabButton = document.getElementById(`${activeTab}-tab`);

        if (tabButton && typeof bootstrap !== 'undefined') {
            const tab = new bootstrap.Tab(tabButton);
            tab.show();
        }

        document.querySelectorAll('[data-bs-toggle="pill"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', (event) => {
                const activeTabName = event.target.getAttribute('id').replace('-tab', '');
                localStorage.setItem(DASHBOARD_CONFIG.cacheKey, activeTabName);
                
                EventBus.emit('TAB_CHANGED', { tab: activeTabName });
            });
        });

        console.log('✅ Tab management initialized');
    }

    handleTabChange(data) {
        const { tab } = data;
        
        // Use task scheduler for all tab operations
        const performTabOperations = async () => {
            // Initialize map if operations tab is shown
            if (tab === 'operations') {
                await taskScheduler.schedule(() => {
                    if (!this.modules.maps.logisticsMapInstance) {
                        this.modules.maps.initLogisticsMap();
                    } else {
                        this.modules.maps.logisticsMapInstance.invalidateSize();
                        this.modules.maps.refreshMapMarkers();
                    }
                });
            }

            // Animate rating bars when customers tab is shown
            if (tab === 'customers') {
                await taskScheduler.schedule(() => this.animateRatingBars());
            }

            // Animate service bars when laundries tab is shown
            if (tab === 'laundries') {
                await taskScheduler.schedule(() => this.animateServiceBars());
            }
        };
        
        // Schedule all operations as non-blocking tasks
        taskScheduler.schedule(performTabOperations);
    }

    initMapAddressSearch() {
        const searchInput = document.getElementById('map-address-search');
        if (!searchInput) return;

        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.searchMapAddress();
            }
        });

        console.log('✅ Map address search initialized');
    }

    async searchMapAddress() {
        const input = document.getElementById('map-address-search');
        const address = input.value.trim();

        if (!address || address.length < 3) {
            this.modules.utils.showToast('Please enter at least 3 characters', 'warning');
            return;
        }

        try {
            const result = await this.modules.api.geocodeAddress(address);
            
            if (result) {
                this.displaySearchResult({
                    lat: parseFloat(result.lat),
                    lng: parseFloat(result.lon),
                    address: result.display_name
                });
                this.modules.maps.addDraggableSearchMarker(
                    parseFloat(result.lat), 
                    parseFloat(result.lon), 
                    result.display_name
                );
                this.modules.utils.showToast('📍 Location found!', 'success');
            } else {
                this.modules.utils.showToast('Address not found. Try a different search term.', 'warning');
            }
        } catch (error) {
            ErrorBoundary.handleError(error, 'AdminDashboard.searchMapAddress');
            this.modules.utils.showToast('Search failed: ' + error.message, 'danger');
        }
    }

    displaySearchResult(result) {
        const resultsDiv = document.getElementById('search-result-display');
        if (resultsDiv) {
            resultsDiv.style.display = 'block';
            document.getElementById('result-address-text').textContent = result.address;
            document.getElementById('result-coords-text').textContent = 
                `📍 ${result.lat.toFixed(6)}, ${result.lng.toFixed(6)}`;
        }
    }

    useCurrentLocation() {
        let currentPosition;
        this.modules.utils.getCurrentPosition()
            .then(position => {
                currentPosition = position;
                const { latitude, longitude } = position.coords;
                return this.modules.api.reverseGeocode(latitude, longitude);
            })
            .then(address => {
                this.displaySearchResult({
                    lat: currentPosition.coords.latitude,
                    lng: currentPosition.coords.longitude,
                    address: address
                });
                this.modules.maps.addDraggableSearchMarker(
                    currentPosition.coords.latitude, 
                    currentPosition.coords.longitude, 
                    address
                );
                this.modules.utils.showToast('📍 Current location found!', 'success');
            })
            .catch(error => {
                ErrorBoundary.handleError(error, 'AdminDashboard.useCurrentLocation');
                this.modules.utils.showToast('Failed to get current location', 'danger');
            });
    }

    // Route management
    async getRouteToPickup(pickupId) {
        try {
            PerformanceMonitor.startTimer('route_calculation');
            
            const routeData = await this.modules.routing.getRouteToPickup(pickupId);
            
            if (routeData) {
                this.modules.utils.showToast('Route loaded successfully!', 'success');
            }
            
            PerformanceMonitor.endTimer('route_calculation');
            
        } catch (error) {
            ErrorBoundary.handleError(error, 'AdminDashboard.getRouteToPickup');
            this.modules.utils.showToast('Failed to calculate route', 'danger');
        }
    }

    async getOptimizedMultiRoute() {
        const selectedPickups = this.modules.state.getSelectedPickupIds();
        
        if (selectedPickups.length < 2) {
            this.modules.utils.showToast('Please select at least 2 pickups for route optimization', 'warning');
            return;
        }

        try {
            PerformanceMonitor.startTimer('multi_route_optimization');
            
            // Get pickup data
            const pickupData = await this.modules.api.getPickupsByIds(selectedPickups);
            
            // Use advanced route optimizer
            const optimizedRoute = await this.modules.routeOptimizer.optimizeRoute(pickupData, {
                vehicleId: 'default',
                prioritizeTime: true,
                considerTraffic: true,
                allowReordering: true
            });
            
            if (optimizedRoute) {
                await this.modules.maps.drawMultiStopRoute(optimizedRoute);
                this.showAdvancedRouteSummary(optimizedRoute);
                this.modules.utils.showToast('Route optimized with AI! 🧠', 'success');
            }
            
        } catch (error) {
            ErrorBoundary.handleError(error, 'AdminDashboard.getOptimizedMultiRoute');
            this.modules.utils.showToast('Failed to optimize route', 'danger');
        } finally {
            PerformanceMonitor.endTimer('multi_route_optimization');
        }
    }

    // Pickup management
    togglePickupSelection(pickupId) {
        const wasSelected = this.modules.state.togglePickupSelection(pickupId);
        this.updateSelectedPickupCount();
        
        // Update marker visual state
        this.modules.maps.updateMarkerSelection(pickupId, !wasSelected);
        
        EventBus.emit('PICKUP_SELECTED', { pickupId, selected: !wasSelected });
    }

    updateSelectedPickupCount() {
        const count = this.modules.state.getSelectedCount();
        
        // Batch all DOM updates in a single requestAnimationFrame to prevent multiple reflows
        requestAnimationFrame(() => {
            const updates = [];
            
            // Collect all elements first
            const countElements = document.querySelectorAll('#selectedCount, #selectedCountTop');
            const countBadge = document.getElementById('selectedPickupCount');
            const multiRouteBtn = document.getElementById('multiRouteBtn');
            const multiRouteTopBtn = document.getElementById('multiRouteTopBtn');
            const modalMultiRouteBtn = document.getElementById('modalMultiRouteBtn');
            const modalSelectedCount = document.getElementById('modalSelectedCount');
            
            // Prepare all updates
            const showMultiRoute = count > 1 ? 'block' : 'none';
            const showMultiRouteInline = count > 1 ? 'inline-block' : 'none';
            
            // Apply all updates at once
            countElements.forEach(el => {
                if (el) el.textContent = count;
            });
            
            if (countBadge) {
                countBadge.textContent = count;
                countBadge.style.display = count > 0 ? 'inline-block' : 'none';
            }
            
            if (multiRouteBtn) multiRouteBtn.style.display = showMultiRoute;
            if (multiRouteTopBtn) multiRouteTopBtn.style.display = showMultiRoute;
            if (modalMultiRouteBtn) modalMultiRouteBtn.style.display = showMultiRouteInline;
            if (modalSelectedCount) modalSelectedCount.textContent = count;
        });
    }

    clearSelections() {
        this.modules.state.clearSelections();
        this.updateSelectedPickupCount();
        this.modules.maps.clearAllMarkerSelections();
    }

    selectAllPending() {
        const pickupData = window.PENDING_PICKUPS || [];
        pickupData.forEach(pickup => {
            if (pickup.status === 'pending' || pickup.status === 'accepted') {
                this.modules.state.addSelection(parseInt(pickup.id));
            }
        });
        this.updateSelectedPickupCount();
        const count = this.modules.state.getSelectedCount();
        this.modules.utils.showToast(`Selected ${count} pending pickups`, 'info');
    }

    // Navigation
    async startNavigation(pickupId) {
        try {
            // Start tracking first
            await this.modules.tracking.startTracking(pickupId);
            
            const result = await this.modules.api.startNavigation(pickupId);
            
            if (result.success) {
                this.modules.utils.showToast('📡 Navigation & tracking started!', 'success');
                this.modules.maps.refreshMapMarkers();
                EventBus.emit('NAVIGATION_STARTED', { pickupId });
            } else {
                this.modules.utils.showToast('Failed to start navigation: ' + result.message, 'danger');
            }
        } catch (error) {
            ErrorBoundary.handleError(error, 'AdminDashboard.startNavigation');
            this.modules.utils.showToast('Failed to start navigation', 'danger');
        }
    }

    async startMultiPickupNavigation() {
        const selectedPickups = this.modules.state.getSelectedPickupIds();

        if (selectedPickups.length === 0) {
            this.modules.utils.showToast('No pickups selected', 'warning');
            return;
        }

        try {
            // Start multi-tracking
            await this.modules.tracking.startMultiTracking(selectedPickups);
            
            const result = await this.modules.api.startMultiPickupNavigation(selectedPickups);
            
            if (result.success) {
                this.modules.utils.showToast('📡 Multi-pickup navigation & tracking started!', 'success');
                this.modules.maps.refreshMapMarkers();
                this.clearSelections();
                EventBus.emit('MULTI_NAVIGATION_STARTED', { pickupIds: selectedPickups });
            } else {
                this.modules.utils.showToast(result.error || 'Failed to start navigation', 'danger');
            }
        } catch (error) {
            ErrorBoundary.handleError(error, 'AdminDashboard.startMultiPickupNavigation');
            this.modules.utils.showToast('Failed to start navigation', 'danger');
        }
    }

    // Route details and UI
    showSingleRouteDetails(routeData, pickupId) {
        const detailsPanel = document.getElementById('routeDetailsPanel');
        if (!detailsPanel) return;

        const branch = this.modules.state.getNearestBranch(routeData.start?.lat, routeData.start?.lng);
        
        detailsPanel.innerHTML = `
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Route Details</h6>
                    <button class="btn btn-sm btn-light" onclick="dashboard.closeRouteDetails()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5 class="text-success">
                            <i class="bi bi-signpost"></i> ${routeData.distance || '0 km'}
                        </h5>
                        <p class="text-muted">
                            <i class="bi bi-clock"></i> ${routeData.duration || '0 min'}
                        </p>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <small class="text-muted">From (Branch):</small>
                        <p class="mb-0"><b>${branch?.name || 'WashBox Branch'}</b></p>
                        <small class="text-muted">Negros Oriental</small>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">To (Pickup):</small>
                        <p class="mb-0"><b>Customer Location</b></p>
                        <small class="text-muted">${routeData.estimated_arrival || 'ETA calculating...'}</small>
                    </div>
                    <hr>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="dashboard.startNavigation(${pickupId})">
                            <i class="bi bi-play-circle me-2"></i> Start Navigation
                        </button>
                        <button class="btn btn-outline-primary" onclick="dashboard.printRoute()">
                            <i class="bi bi-printer me-2"></i> Print Directions
                        </button>
                        <button class="btn btn-outline-danger" onclick="dashboard.clearRoute()">
                            <i class="bi bi-x-circle me-2"></i> Clear Route
                        </button>
                    </div>
                </div>
            </div>
        `;

        detailsPanel.style.display = 'block';
    }

    showAdvancedRouteSummary(routeData) {
        const summary = `
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-purple text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-route me-2"></i>AI-Optimized Route</h6>
                    <button class="btn btn-sm btn-light" onclick="dashboard.closeRouteDetails()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted">Total Distance</small>
                            <h5>${(routeData.totalDistance || 0).toFixed(1)} km</h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Total Time</small>
                            <h5>${Math.round((routeData.totalDuration || 0) / 60)} min</h5>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted">Stops</small>
                            <h5>${routeData.stops?.length || 0}</h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Savings</small>
                            <h5 class="text-success">${routeData.metrics?.savings?.percentage?.toFixed(1) || 0}%</h5>
                        </div>
                    </div>
                    ${routeData.returnToBase ? '<div class="alert alert-info py-2 px-3 mb-3 small"><i class="bi bi-arrow-repeat me-1"></i> Route includes return to branch</div>' : ''}
                    <hr>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="dashboard.startMultiPickupNavigation()">
                            <i class="bi bi-play-circle me-2"></i>Start Navigation
                        </button>
                        <button class="btn btn-outline-primary" onclick="dashboard.printRouteSchedule()">
                            <i class="bi bi-printer me-2"></i>Print Schedule
                        </button>
                        <button class="btn btn-outline-danger" onclick="dashboard.clearRoute()">
                            <i class="bi bi-x-circle me-2"></i>Clear Route
                        </button>
                    </div>
                </div>
            </div>
        `;

        const routeDetails = document.getElementById('routeDetailsPanel');
        if (routeDetails) {
            routeDetails.innerHTML = summary;
            routeDetails.style.display = 'block';
        }
    }

    updateTrackingUI(pickupId, status) {
        // Update tracking status in UI
        console.log(`Tracking UI updated for pickup ${pickupId}: ${status}`);
    }

    updateTrackingPanel(data) {
        // Update tracking panel with location data
        console.log('Tracking panel updated:', data);
    }

    updateOptimizationMetrics(data) {
        // Update optimization metrics display
        console.log('Optimization metrics updated:', data);
    }

    updateDashboardMetrics(metrics) {
        // Update dashboard metrics
        console.log('Dashboard metrics updated:', metrics);
    }

    refreshAnalyticsCharts(data) {
        // Refresh analytics charts
        console.log('Analytics charts refreshed:', data);
    }

    updateAlertsPanel(alerts) {
        // Update alerts panel
        console.log('Alerts panel updated:', alerts);
    }

    closeRouteDetails() {
        const panel = document.getElementById('routeDetailsPanel');
        if (panel) panel.style.display = 'none';
    }

    clearRoute() {
        this.modules.maps.clearRoute();
        this.closeRouteDetails();
    }

    printRoute() {
        this.modules.utils.printRoute();
    }

    printRouteSchedule() {
        this.modules.utils.printRouteSchedule();
    }

    // Dashboard refresh and updates - Optimized
    refreshDashboard() {
        const refreshBtn = document.getElementById('refresh-btn');
        if (refreshBtn) {
            // Batch DOM updates to prevent forced reflow
            requestAnimationFrame(() => {
                refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i><span>Refreshing...</span>';
                refreshBtn.disabled = true;
                
                // Use a small delay to allow UI update to render
                setTimeout(() => {
                    window.location.reload();
                }, 100);
            });
        } else {
            // If no refresh button, reload immediately
            window.location.reload();
        }
    }

    initializeAutoRefresh() {
        if (DASHBOARD_CONFIG.autoRefresh) {
            // Use a longer interval to reduce performance impact
            const refreshInterval = Math.max(DASHBOARD_CONFIG.refreshInterval, 30000); // Minimum 30 seconds
            setInterval(() => {
                this.refreshDashboard();
            }, refreshInterval);
        }
    }

    initializeDateUpdater() {
        const updateDate = () => {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const dateEl = document.getElementById('current-date');
            if (dateEl) {
                // Use requestAnimationFrame for DOM updates
                requestAnimationFrame(() => {
                    dateEl.textContent = now.toLocaleDateString('en-US', options);
                });
            }
        };
        updateDate();
        // Update every minute instead of every second for better performance
        setInterval(updateDate, 60000);
    }

    // Animation helpers - Optimized to prevent forced reflows
    animateRatingBars() {
        const bars = document.querySelectorAll('.trc-star-fill');
        if (bars.length === 0) return;
        
        // Batch read operations first (no reflow)
        const widths = Array.from(bars).map(bar => bar.getAttribute('data-width') || '0');
        
        // Then batch write operations (single reflow)
        requestAnimationFrame(() => {
            bars.forEach((bar, index) => {
                bar.style.width = '0%';
                bar.style.transition = 'none';
            });
            
            requestAnimationFrame(() => {
                bars.forEach((bar, index) => {
                    bar.style.transition = 'width 0.8s ease-out';
                    bar.style.width = widths[index] + '%';
                });
            });
        });
    }

    animateServiceBars() {
        const bars = document.querySelectorAll('.service-bar-fill');
        if (bars.length === 0) return;
        
        // Batch read operations first (no reflow)
        const widths = Array.from(bars).map(bar => bar.getAttribute('data-width') || '0');
        
        // Then batch write operations (single reflow)
        requestAnimationFrame(() => {
            bars.forEach((bar, index) => {
                bar.style.width = '0%';
                bar.style.transition = 'none';
            });
            
            requestAnimationFrame(() => {
                bars.forEach((bar, index) => {
                    bar.style.transition = 'width 0.8s ease-out';
                    bar.style.width = widths[index] + '%';
                });
            });
        });
    }

    // Event handlers
    onMapReady() {
        console.log('🗺️ Map is ready');
        this.modules.maps.loadPickupsAndRender();
    }

    handlePickupSelection(data) {
        console.log('📍 Pickup selected:', data);
    }

    handleRouteCalculated(data) {
        console.log('🛣️ Route calculated:', data);
    }

    // Advanced features event handlers
    handleTrackingStarted(data) {
        console.log('📡 Tracking started:', data);
        this.modules.utils.showToast(`📡 Tracking started for pickup ${data.pickupId}`, 'info');
        
        // Update UI to show tracking status
        this.updateTrackingUI(data.pickupId, 'active');
    }

    handleLocationUpdate(data) {
        // Update map with new location
        if (this.modules.maps.logisticsMapInstance) {
            this.modules.maps.updateVehicleLocation(data.location);
        }
        
        // Update tracking panel if visible
        this.updateTrackingPanel(data);
    }

    handleRouteOptimized(data) {
        console.log('🧠 Route optimized:', data);
        
        if (data.savings && data.savings.percentage > 10) {
            this.modules.utils.showToast(
                `🎆 Route optimized! Saved ${data.savings.percentage.toFixed(1)}% distance`, 
                'success'
            );
        }
        
        // Update optimization metrics
        this.updateOptimizationMetrics(data);
    }

    handleAnalyticsUpdate(data) {
        // Update dashboard metrics
        this.updateDashboardMetrics(data.metrics);
        
        // Update charts if analytics tab is active
        if (document.getElementById('analytics-tab')?.classList.contains('active')) {
            this.refreshAnalyticsCharts(data);
        }
    }

    handleAnalyticsAlert(data) {
        // Show critical alerts immediately
        data.alerts.forEach(alert => {
            if (alert.severity === 'critical') {
                this.modules.utils.showToast(
                    `⚠️ ${alert.message}`, 
                    'danger'
                );
            }
        });
        
        // Update alerts panel
        this.updateAlertsPanel(data.alerts);
    }

    handleError(error) {
        console.error('❌ Dashboard error:', error);
        this.modules.utils.showToast('An error occurred: ' + error.message, 'danger');
    }

    // Utility methods for backward compatibility
    createPickupAtLocation(lat, lon, address) {
        try {
            sessionStorage.setItem('pickup_location', JSON.stringify({
                latitude: lat,
                longitude: lon,
                address: address
            }));

            const url = `/admin/pickups/create?lat=${lat}&lon=${lon}&address=${encodeURIComponent(address)}`;
            window.location.href = url;
        } catch (error) {
            ErrorBoundary.handleError(error, 'AdminDashboard.createPickupAtLocation');
            this.modules.utils.showToast('Failed to prepare pickup creation', 'danger');
        }
    }

    viewPickupDetails(pickupId) {
        window.open(`/admin/pickups/${pickupId}`, '_blank');
    }

    showBranchInfo(branchId) {
        const branch = this.modules.state.getBranchById(branchId);
        if (!branch) {
            alert('Branch information not available.');
            return;
        }
        alert(`${branch.name}\n\nAddress: ${branch.address || 'N/A'}\nPhone: ${branch.phone || 'N/A'}\nCoordinates: ${parseFloat(branch.latitude).toFixed(4)}, ${parseFloat(branch.longitude).toFixed(4)}`);
    }

    // Export functionality
    exportData(format) {
        alert('Export to ' + format + ' functionality to be implemented');
    }
}

// Initialize dashboard
const dashboard = new AdminDashboard();

// Make dashboard available globally for backward compatibility
window.dashboard = dashboard;

// Legacy global functions for backward compatibility
window.initializeDashboardData = (branches, stats) => {
    if (dashboard.modules && dashboard.modules.state) {
        dashboard.initializeDashboardData(branches, stats);
    } else {
        console.warn('Dashboard not ready, deferring data initialization');
        taskScheduler.schedule(() => window.initializeDashboardData(branches, stats), 100);
    }
};
window.refreshDashboard = () => dashboard.refreshDashboard();
window.getRouteToPickup = (pickupId) => dashboard.getRouteToPickup(pickupId);
window.startNavigation = (pickupId) => dashboard.startNavigation(pickupId);
window.startNavigationForPickup = (pickupId) => dashboard.startNavigation(pickupId);
window.togglePickupSelection = (pickupId) => dashboard.togglePickupSelection(pickupId);
window.getOptimizedMultiRoute = () => dashboard.getOptimizedMultiRoute();
window.startMultiPickupNavigation = () => dashboard.startMultiPickupNavigation();
window.clearSelections = () => dashboard.clearSelections();
window.selectAllPending = () => dashboard.selectAllPending();
window.closeRouteDetails = () => dashboard.closeRouteDetails();
window.clearRoute = () => dashboard.clearRoute();
window.printRoute = () => dashboard.printRoute();
window.printRouteSchedule = () => dashboard.printRouteSchedule();
window.searchMapAddress = () => dashboard.searchMapAddress();
window.useCurrentLocation = () => dashboard.useCurrentLocation();
window.createPickupAtLocation = (lat, lon, address) => dashboard.createPickupAtLocation(lat, lon, address);
window.viewPickupDetails = (pickupId) => dashboard.viewPickupDetails(pickupId);
window.showBranchInfo = (branchId) => dashboard.showBranchInfo(branchId);
window.exportData = (format) => dashboard.exportData(format);
window.getRouteToSearchLocation = (lat, lon) => {
    console.log('Route to search location:', lat, lon);
    dashboard.modules.utils.showToast('Route calculation from search location coming soon', 'info');
};
window.copyCoordinates = (lat, lon) => {
    const coords = `${lat.toFixed(6)}, ${lon.toFixed(6)}`;
    navigator.clipboard.writeText(coords).then(() => {
        dashboard.modules.utils.showToast('📋 Coordinates copied!', 'success');
    }).catch(() => {
        dashboard.modules.utils.showToast('Failed to copy coordinates', 'danger');
    });
};
window.refreshMapMarkers = () => {
    if (dashboard.modules && dashboard.modules.maps) {
        dashboard.modules.maps.refreshMapMarkers();
        dashboard.modules.utils.showToast('🗺️ Map refreshed!', 'success');
    }
};

window.removePickupFromMap = (pickupId) => {
    if (dashboard.modules && dashboard.modules.maps) {
        dashboard.modules.maps.removePickupMarker(pickupId);
    }
};

window.autoRouteAllVisible = () => {
    const pickupData = window.PENDING_PICKUPS || [];
    const visiblePickups = pickupData.filter(p => p.status === 'pending');
    
    if (visiblePickups.length === 0) {
        dashboard.modules.utils.showToast('No visible pickups to route', 'warning');
        return;
    }
    
    if (visiblePickups.length === 1) {
        dashboard.getRouteToPickup(visiblePickups[0].id);
    } else {
        visiblePickups.forEach(pickup => {
            if (!dashboard.modules.state.selectedPickups.has(parseInt(pickup.id))) {
                dashboard.togglePickupSelection(pickup.id);
            }
        });
        taskScheduler.schedule(() => dashboard.getOptimizedMultiRoute(), 500);
    }
};

console.log('✅ Modular Admin Dashboard loaded successfully');