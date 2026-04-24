import * as Location from 'expo-location';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_BASE_URL, STORAGE_KEYS } from '../constants/config';

class SimpleLocationTrackingService {
  constructor() {
    this.watchId = null;
    this.isTracking = false;
    this.currentPickupId = null;
    this.updateInterval = null;
  }

  // Start tracking for pickup request
  async startTracking(pickupRequestId, userType = 'customer') {
    try {
      // Request location permissions
      const { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') {
        throw new Error('Location permission denied');
      }

      this.currentPickupId = pickupRequestId;
      this.isTracking = true;

      // Start location watching
      this.watchId = await Location.watchPositionAsync(
        {
          accuracy: Location.Accuracy.High,
          timeInterval: 10000, // Update every 10 seconds
          distanceInterval: 20, // Update every 20 meters
        },
        (location) => {
          this.sendLocationUpdate(location, userType);
        }
      );

      // Also start HTTP tracking session
      await this.startTrackingSession(pickupRequestId, userType);

      return true;
    } catch (error) {
      console.error('Failed to start tracking:', error);
      return false;
    }
  }

  // Start tracking session via HTTP
  async startTrackingSession(pickupRequestId, userType) {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return false;

      const response = await fetch(`${API_BASE_URL}/v1/location-tracking/start`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          pickup_request_id: pickupRequestId,
          user_type: userType,
        }),
      });

      return response.ok;
    } catch (error) {
      console.error('Failed to start tracking session:', error);
      return false;
    }
  }

  // Send location update via HTTP
  async sendLocationUpdate(location, userType) {
    if (!this.currentPickupId) return;

    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return;

      const locationData = {
        pickup_request_id: this.currentPickupId,
        user_type: userType,
        latitude: location.coords.latitude,
        longitude: location.coords.longitude,
        accuracy: location.coords.accuracy,
        speed: location.coords.speed || 0,
        heading: location.coords.heading || 0,
      };

      await fetch(`${API_BASE_URL}/v1/location-tracking/update`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(locationData),
      });

    } catch (error) {
      console.error('Failed to send location update:', error);
    }
  }

  // Stop tracking
  async stopTracking() {
    if (this.watchId) {
      this.watchId.remove();
      this.watchId = null;
    }

    if (this.updateInterval) {
      clearInterval(this.updateInterval);
      this.updateInterval = null;
    }

    if (this.currentPickupId) {
      await this.stopTrackingSession();
    }

    this.isTracking = false;
    this.currentPickupId = null;
  }

  // Stop tracking session via HTTP
  async stopTrackingSession() {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return;

      await fetch(`${API_BASE_URL}/v1/location-tracking/stop`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          pickup_request_id: this.currentPickupId,
        }),
      });
    } catch (error) {
      console.error('Failed to stop tracking session:', error);
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

  // Get locations for pickup request
  async getPickupLocations(pickupRequestId) {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return null;

      const response = await fetch(`${API_BASE_URL}/v1/location-tracking/pickup/${pickupRequestId}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (response.ok) {
        const result = await response.json();
        // Handle both old and new response formats
        return result.success ? result.data : result;
      }
      return null;
    } catch (error) {
      console.error('Failed to get pickup locations:', error);
      return null;
    }
  }

  // Cleanup
  disconnect() {
    this.stopTracking();
  }
}

export default new SimpleLocationTrackingService();