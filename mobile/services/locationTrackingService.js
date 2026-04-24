import * as Location from 'expo-location';
import { io } from 'socket.io-client';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_BASE_URL, STORAGE_KEYS } from '../constants/config';

class LocationTrackingService {
  constructor() {
    this.socket = null;
    this.watchId = null;
    this.isTracking = false;
    this.currentPickupId = null;
  }

  // Initialize socket connection
  async initializeSocket() {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) throw new Error('No auth token');

      this.socket = io(`${API_BASE_URL}`, {
        auth: { token },
        transports: ['websocket']
      });

      this.socket.on('connect', () => {
        console.log('Location tracking connected');
      });

      this.socket.on('disconnect', () => {
        console.log('Location tracking disconnected');
      });

      return true;
    } catch (error) {
      console.error('Socket initialization failed:', error);
      return false;
    }
  }

  // Start tracking for pickup request
  async startTracking(pickupRequestId, userType = 'staff') {
    try {
      // Request location permissions
      const { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') {
        throw new Error('Location permission denied');
      }

      // Initialize socket if not connected
      if (!this.socket?.connected) {
        await this.initializeSocket();
      }

      this.currentPickupId = pickupRequestId;
      this.isTracking = true;

      // Start location watching
      this.watchId = await Location.watchPositionAsync(
        {
          accuracy: Location.Accuracy.High,
          timeInterval: 5000, // Update every 5 seconds
          distanceInterval: 10, // Update every 10 meters
        },
        (location) => {
          this.sendLocationUpdate(location, userType);
        }
      );

      return true;
    } catch (error) {
      console.error('Failed to start tracking:', error);
      return false;
    }
  }

  // Send location update via socket
  sendLocationUpdate(location, userType) {
    if (!this.socket?.connected || !this.currentPickupId) return;

    const locationData = {
      pickup_request_id: this.currentPickupId,
      user_type: userType, // 'staff' or 'customer'
      latitude: location.coords.latitude,
      longitude: location.coords.longitude,
      accuracy: location.coords.accuracy,
      timestamp: new Date().toISOString(),
      speed: location.coords.speed || 0,
      heading: location.coords.heading || 0,
    };

    this.socket.emit('location_update', locationData);
  }

  // Stop tracking
  stopTracking() {
    if (this.watchId) {
      this.watchId.remove();
      this.watchId = null;
    }
    
    if (this.socket?.connected && this.currentPickupId) {
      this.socket.emit('stop_tracking', { pickup_request_id: this.currentPickupId });
    }

    this.isTracking = false;
    this.currentPickupId = null;
  }

  // Listen for location updates from others
  onLocationUpdate(callback) {
    if (this.socket) {
      this.socket.on('location_broadcast', callback);
    }
  }

  // Get current location once
  async getCurrentLocation() {
    try {
      const { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') return null;

      const location = await Location.getCurrentPositionAsync({
        accuracy: Location.Accuracy.High,
      });

      return {
        latitude: location.coords.latitude,
        longitude: location.coords.longitude,
        accuracy: location.coords.accuracy,
      };
    } catch (error) {
      console.error('Failed to get current location:', error);
      return null;
    }
  }

  // Cleanup
  disconnect() {
    this.stopTracking();
    if (this.socket) {
      this.socket.disconnect();
      this.socket = null;
    }
  }
}

export default new LocationTrackingService();