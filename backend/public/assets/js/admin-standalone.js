// admin-standalone.js - Non-module version to eliminate ES6 import violations
// This version doesn't use ES6 imports to prevent module loading performance issues

(function() {
    'use strict';
    
    // Fallback configurations if modules aren't available
    const DASHBOARD_CONFIG = window.DASHBOARD_CONFIG || {
        autoRefresh: false,
        refreshInterval: 30000,
        charts: {},
        cacheKey: "dashboard_active_tab"
    };

    class AdminDashboardStandalone {
        constructor() {
            this.initialized = false;
            this.modules = {};
            this.violations = [];
            this.init();
        }

        async init() {
            try {
                console.log('🚀 Standalone Admin Dashboard initializing...');
                
                // Initialize with minimal blocking
                this.setupBasicFunctionality();
                
                // Initialize dashboard when DOM is ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => this.initializeDashboardMinimal());
                } else {
                    this.initializeDashboardMinimal();
                }
                
                this.initialized = true;
                console.log('✅ Standalone Admin Dashboard initialized');
                
            } catch (error) {
                console.error('❌ Admin Dashboard initialization failed:', error);
            }
        }

        setupBasicFunctionality() {
            // Basic functionality without module dependencies
            this.setupErrorHandling();
            this.setupPerformanceMonitoring();
        }

        setupErrorHandling() {
            window.addEventListener('error', (event) => {
                console.error('Global error:', event.error);
            });
            
            window.addEventListener('unhandledrejection', (event) => {
                console.error('Unhandled promise rejection:', event.reason);
            });
        }

        setupPerformanceMonitoring() {
            // Simple performance monitoring
            this.performanceStart = performance.now();
            
            // Monitor long tasks
            if ('PerformanceObserver' in window) {
                try {
                    const observer = new PerformanceObserver((list) => {
                        for (const entry of list.getEntries()) {
                            if (entry.duration > 50) {
                                console.warn(`Long task detected: ${entry.duration}ms`);
                            }
                        }
                    });
                    observer.observe({ entryTypes: ['longtask'] });
                } catch (e) {
                    console.warn('Performance observer not supported');
                }
            }
        }

        initializeDashboardMinimal() {
            // Ultra-minimal dashboard initialization to prevent violations
            const minimalTasks = [
                () => { console.log('📊 Minimal dashboard init'); },
                () => this.initializeBasicCharts(),
                () => this.initializeBasicTabs(),
                () => this.initializeBasicRefresh(),
                () => this.setupBasicEventListeners(),
                () => { console.log('✅ Minimal dashboard ready'); }
            ];
            
            // Execute with extreme time slicing (0.5ms per task)
            this.executeMinimalTasks(minimalTasks);
        }

        executeMinimalTasks(tasks) {
            let taskIndex = 0;
            const channel = new MessageChannel();
            
            channel.port2.onmessage = () => {
                if (taskIndex < tasks.length) {
                    const startTime = performance.now();
                    
                    // Execute tasks with 0.5ms limit
                    while (taskIndex < tasks.length && (performance.now() - startTime) < 0.5) {
                        try {
                            tasks[taskIndex]();
                        } catch (error) {
                            console.error(`Minimal task ${taskIndex} failed:`, error);
                        }
                        taskIndex++;
                    }
                    
                    if (taskIndex < tasks.length) {
                        channel.port1.postMessage(null);
                    }
                }
            };
            
            channel.port1.postMessage(null);
        }

        initializeBasicCharts() {
            // Only initialize if Chart.js is available and no violations
            if (typeof Chart === 'undefined') {
                console.log('Chart.js not available, skipping charts');
                return;
            }
            
            // Defer chart initialization to prevent blocking
            setTimeout(() => {
                this.initializeChartsDeferred();
            }, 1000);
        }

        initializeChartsDeferred() {
            try {
                // Set basic Chart.js defaults
                Chart.defaults.font.family = \"'Plus Jakarta Sans', sans-serif\";\n                \n                // Initialize only critical charts\n                this.initializeCriticalCharts();\n                \n            } catch (error) {\n                console.error('Chart initialization failed:', error);\n            }\n        }\n\n        initializeCriticalCharts() {\n            // Only initialize the most important chart to prevent violations\n            const revenueChart = document.getElementById('revenueTrendChart');\n            if (revenueChart && window.REVENUE_TREND_DATA) {\n                try {\n                    new Chart(revenueChart, {\n                        type: 'line',\n                        data: {\n                            labels: Object.keys(window.REVENUE_TREND_DATA),\n                            datasets: [{\n                                label: 'Revenue',\n                                data: Object.values(window.REVENUE_TREND_DATA),\n                                borderColor: '#3D3B6B',\n                                backgroundColor: 'rgba(61,59,107,0.1)',\n                                tension: 0.4\n                            }]\n                        },\n                        options: {\n                            responsive: true,\n                            maintainAspectRatio: false,\n                            plugins: {\n                                legend: { display: false }\n                            }\n                        }\n                    });\n                } catch (error) {\n                    console.error('Revenue chart failed:', error);\n                }\n            }\n        }\n\n        initializeBasicTabs() {\n            // Basic tab functionality without violations\n            const activeTab = localStorage.getItem(DASHBOARD_CONFIG.cacheKey) || 'overview';\n            const tabButton = document.getElementById(`${activeTab}-tab`);\n\n            if (tabButton && typeof bootstrap !== 'undefined') {\n                try {\n                    const tab = new bootstrap.Tab(tabButton);\n                    tab.show();\n                } catch (error) {\n                    console.error('Tab initialization failed:', error);\n                }\n            }\n\n            // Add tab change listeners with minimal impact\n            document.querySelectorAll('[data-bs-toggle=\"pill\"]').forEach(tab => {\n                tab.addEventListener('shown.bs.tab', (event) => {\n                    const activeTabName = event.target.getAttribute('id').replace('-tab', '');\n                    localStorage.setItem(DASHBOARD_CONFIG.cacheKey, activeTabName);\n                });\n            });\n        }\n\n        initializeBasicRefresh() {\n            // Basic refresh functionality\n            const refreshBtn = document.getElementById('refresh-btn');\n            if (refreshBtn) {\n                refreshBtn.addEventListener('click', () => {\n                    refreshBtn.innerHTML = '<i class=\"bi bi-arrow-clockwise\"></i> Refreshing...';\n                    refreshBtn.disabled = true;\n                    \n                    setTimeout(() => {\n                        window.location.reload();\n                    }, 100);\n                });\n            }\n        }\n\n        setupBasicEventListeners() {\n            // Basic event listeners without module dependencies\n            \n            // Date updater\n            const updateDate = () => {\n                const dateEl = document.getElementById('current-date');\n                if (dateEl) {\n                    const now = new Date();\n                    dateEl.textContent = now.toLocaleDateString('en-US', {\n                        weekday: 'long',\n                        year: 'numeric',\n                        month: 'long',\n                        day: 'numeric'\n                    });\n                }\n            };\n            updateDate();\n            setInterval(updateDate, 60000);\n            \n            // Basic search functionality\n            const searchInput = document.getElementById('map-address-search');\n            if (searchInput) {\n                searchInput.addEventListener('keypress', (e) => {\n                    if (e.key === 'Enter') {\n                        e.preventDefault();\n                        this.basicAddressSearch();\n                    }\n                });\n            }\n        }\n\n        basicAddressSearch() {\n            const input = document.getElementById('map-address-search');\n            const address = input?.value.trim();\n            \n            if (!address || address.length < 3) {\n                this.showBasicToast('Please enter at least 3 characters', 'warning');\n                return;\n            }\n            \n            this.showBasicToast('Address search functionality requires full module loading', 'info');\n        }\n\n        showBasicToast(message, type = 'info') {\n            // Basic toast without dependencies\n            const toast = document.createElement('div');\n            toast.className = `alert alert-${type} position-fixed`;\n            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';\n            toast.innerHTML = `\n                ${message}\n                <button type=\"button\" class=\"btn-close\" onclick=\"this.parentElement.remove()\"></button>\n            `;\n            \n            document.body.appendChild(toast);\n            \n            setTimeout(() => {\n                if (toast.parentElement) {\n                    toast.remove();\n                }\n            }, 5000);\n        }\n\n        // Basic global functions for compatibility\n        refreshDashboard() {\n            window.location.reload();\n        }\n\n        // Placeholder methods for compatibility\n        getRouteToPickup(pickupId) {\n            this.showBasicToast('Route calculation requires full module loading', 'info');\n        }\n\n        startNavigation(pickupId) {\n            this.showBasicToast('Navigation requires full module loading', 'info');\n        }\n\n        togglePickupSelection(pickupId) {\n            console.log('Pickup selection:', pickupId);\n        }\n\n        clearSelections() {\n            console.log('Selections cleared');\n        }\n\n        closeRouteDetails() {\n            const panel = document.getElementById('routeDetailsPanel');\n            if (panel) panel.style.display = 'none';\n        }\n\n        clearRoute() {\n            console.log('Route cleared');\n        }\n\n        searchMapAddress() {\n            this.basicAddressSearch();\n        }\n\n        useCurrentLocation() {\n            this.showBasicToast('Current location requires full module loading', 'info');\n        }\n\n        showBranchInfo(branchId) {\n            alert('Branch info requires full module loading');\n        }\n\n        exportData(format) {\n            this.showBasicToast(`Export to ${format} requires full module loading`, 'info');\n        }\n    }\n\n    // Initialize standalone dashboard\n    const dashboard = new AdminDashboardStandalone();\n\n    // Make available globally\n    window.dashboard = dashboard;\n\n    // Global functions for compatibility\n    window.refreshDashboard = () => dashboard.refreshDashboard();\n    window.getRouteToPickup = (pickupId) => dashboard.getRouteToPickup(pickupId);\n    window.startNavigation = (pickupId) => dashboard.startNavigation(pickupId);\n    window.startNavigationForPickup = (pickupId) => dashboard.startNavigation(pickupId);\n    window.togglePickupSelection = (pickupId) => dashboard.togglePickupSelection(pickupId);\n    window.getOptimizedMultiRoute = () => dashboard.showBasicToast('Multi-route requires full module loading', 'info');\n    window.startMultiPickupNavigation = () => dashboard.showBasicToast('Multi-pickup navigation requires full module loading', 'info');\n    window.clearSelections = () => dashboard.clearSelections();\n    window.selectAllPending = () => dashboard.showBasicToast('Select all requires full module loading', 'info');\n    window.closeRouteDetails = () => dashboard.closeRouteDetails();\n    window.clearRoute = () => dashboard.clearRoute();\n    window.printRoute = () => dashboard.showBasicToast('Print route requires full module loading', 'info');\n    window.printRouteSchedule = () => dashboard.showBasicToast('Print schedule requires full module loading', 'info');\n    window.searchMapAddress = () => dashboard.searchMapAddress();\n    window.useCurrentLocation = () => dashboard.useCurrentLocation();\n    window.createPickupAtLocation = (lat, lon, address) => dashboard.showBasicToast('Create pickup requires full module loading', 'info');\n    window.viewPickupDetails = (pickupId) => window.open(`/admin/pickups/${pickupId}`, '_blank');\n    window.showBranchInfo = (branchId) => dashboard.showBranchInfo(branchId);\n    window.exportData = (format) => dashboard.exportData(format);\n    window.getRouteToSearchLocation = (lat, lon) => dashboard.showBasicToast('Route to search location requires full module loading', 'info');\n    window.copyCoordinates = (lat, lon) => {\n        const coords = `${lat.toFixed(6)}, ${lon.toFixed(6)}`;\n        if (navigator.clipboard) {\n            navigator.clipboard.writeText(coords).then(() => {\n                dashboard.showBasicToast('📋 Coordinates copied!', 'success');\n            }).catch(() => {\n                dashboard.showBasicToast('Failed to copy coordinates', 'danger');\n            });\n        }\n    };\n    window.refreshMapMarkers = () => dashboard.showBasicToast('Map refresh requires full module loading', 'info');\n    window.removePickupFromMap = (pickupId) => console.log('Remove pickup:', pickupId);\n    window.autoRouteAllVisible = () => dashboard.showBasicToast('Auto-route requires full module loading', 'info');\n    window.initializeDashboardData = (branches, stats) => {\n        console.log('Dashboard data initialized:', branches?.length, 'branches');\n    };\n\n    console.log('✅ Standalone Admin Dashboard loaded successfully');\n})();