// config.js - Configuration and constants

export const DASHBOARD_CONFIG = {
    autoRefresh: false, // Disabled to prevent interference with route selection
    refreshInterval: 30000,
    pickupPolling: true, // Poll for pickup updates
    pickupPollingInterval: 15000, // 15 seconds
    charts: {
        revenue: null,
        customerSource: null,
        customerBranch: null,
        fullService: null,
        selfService: null,
        addonService: null,
        dailyLaundry: null,
        yoyRevenue: null,
    },
    cacheKey: "dashboard_active_tab",
};

export const MAP_CONFIG = {
    DEFAULT_CENTER: [9.3068, 123.3054],
    DEFAULT_ZOOM: 13,
    TILE_URL: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
    TILE_ATTRIBUTION: "© OpenStreetMap contributors",
    MAX_ZOOM: 19,
    MIN_ZOOM: 2,
    PHILIPPINES_BOUNDS: {
        minLat: 4.5,
        maxLat: 21.5,
        minLng: 116,
        maxLng: 127
    }
};

export const STATUS_COLORS = {
    pending: "#FFC107",
    accepted: "#17A2B8",
    en_route: "#007BFF",
    picked_up: "#28A745",
    cancelled: "#DC3545",
};

export const BRANCH_COLORS = [
    "#007BFF",
    "#10B981",
    "#8B5CF6",
    "#F59E0B",
    "#EF4444",
];

export const ROUTE_COLORS = {
    single: "#3D3B6B",
    multi: "#8B5CF6",
    search: "#0EA5E9"
};
