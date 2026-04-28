export const MAP_CONFIG = {
  // Use a reliable tile server
  tileUrl: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
  
  tileServers: [
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
    'https://b.tile.openstreetmap.org/{z}/{x}/{y}.png',
  ],
  
  // OSRM Routing
  routingServer: 'https://router.project-osrm.org',
  
  // Geocoding
  geocodingServer: 'https://nominatim.openstreetmap.org',
  
  // Map defaults
  defaultRegion: {
    latitude: 9.3068,   // Dumaguete City
    longitude: 123.3054,
    latitudeDelta: 0.05,
    longitudeDelta: 0.05,
  },
  
  // Delivery settings
  maxDeliveryDistance: 50000, // 50km
  pickupRadius: 5000, // 5km
  deliveryRadius: 5000, // 5km
  
  // Colors
  colors: {
    pickup: '#4CAF50',
    delivery: '#FF9800',
    driver: '#2196F3',
    user: '#9C27B0',
    route: '#007AFF',
  },
  
  // Attribution
  attributionText: '© OpenStreetMap contributors',
  
  // Cache
  cacheEnabled: true,
  cacheDuration: 7 * 24 * 60 * 60 * 1000, // 7 days
};