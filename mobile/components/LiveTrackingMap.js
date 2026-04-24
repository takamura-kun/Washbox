import React, { useState, useEffect, useRef } from 'react';
import { View, Text, StyleSheet, Alert, ActivityIndicator } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import MapView, { Marker, Polyline } from 'react-native-maps';
import LocationTrackingService from '../services/locationTrackingService';

const COLORS = {
  background: '#06081A',
  surface: '#0F1332',
  primary: '#0EA5E9',
  pickup: '#10B981',
  danger: '#EF4444',
  textPrimary: '#F1F5F9',
  textSecondary: '#94A3B8',
};

export default function LiveTrackingMap({ pickupRequestId, userType = 'customer', onLocationUpdate }) {
  const [isTracking, setIsTracking] = useState(false);
  const [staffLocation, setStaffLocation] = useState(null);
  const [customerLocation, setCustomerLocation] = useState(null);
  const [mapRegion, setMapRegion] = useState(null);
  const [error, setError] = useState(null);
  const mapRef = useRef(null);

  useEffect(() => {
    initializeTracking();
    return () => {
      LocationTrackingService.stopTracking();
    };
  }, [pickupRequestId]);

  const initializeTracking = async () => {
    try {
      setError(null);
      
      // Start location tracking
      const success = await LocationTrackingService.startTracking(pickupRequestId, userType);
      if (!success) {
        throw new Error('Failed to start location tracking');
      }

      setIsTracking(true);

      // Listen for location updates from others
      LocationTrackingService.onLocationUpdate((locationData) => {
        if (locationData.user_type === 'staff') {
          setStaffLocation({
            latitude: locationData.latitude,
            longitude: locationData.longitude,
            accuracy: locationData.accuracy,
            timestamp: locationData.timestamp,
          });
        } else if (locationData.user_type === 'customer') {
          setCustomerLocation({
            latitude: locationData.latitude,
            longitude: locationData.longitude,
            accuracy: locationData.accuracy,
            timestamp: locationData.timestamp,
          });
        }

        // Update map region to show both locations
        updateMapRegion(locationData);
        
        // Notify parent component
        if (onLocationUpdate) {
          onLocationUpdate(locationData);
        }
      });

      // Get initial location
      const currentLocation = await LocationTrackingService.getCurrentLocation();
      if (currentLocation) {
        setMapRegion({
          ...currentLocation,
          latitudeDelta: 0.01,
          longitudeDelta: 0.01,
        });

        if (userType === 'customer') {
          setCustomerLocation(currentLocation);
        }
      }

    } catch (err) {
      setError(err.message);
      Alert.alert('Tracking Error', err.message);
    }
  };

  const updateMapRegion = (newLocation) => {
    if (!mapRef.current) return;

    const locations = [];
    if (staffLocation) locations.push(staffLocation);
    if (customerLocation) locations.push(customerLocation);
    if (newLocation) locations.push(newLocation);

    if (locations.length > 1) {
      // Fit map to show all locations
      mapRef.current.fitToCoordinates(locations, {
        edgePadding: { top: 50, right: 50, bottom: 50, left: 50 },
        animated: true,
      });
    } else if (locations.length === 1) {
      // Center on single location
      mapRef.current.animateToRegion({
        ...locations[0],
        latitudeDelta: 0.01,
        longitudeDelta: 0.01,
      });
    }
  };

  const getDistanceBetweenPoints = (point1, point2) => {
    if (!point1 || !point2) return null;
    
    const R = 6371; // Earth's radius in km
    const dLat = (point2.latitude - point1.latitude) * Math.PI / 180;
    const dLon = (point2.longitude - point1.longitude) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(point1.latitude * Math.PI / 180) * Math.cos(point2.latitude * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c; // Distance in km
  };

  const distance = getDistanceBetweenPoints(staffLocation, customerLocation);

  if (error) {
    return (
      <View style={styles.errorContainer}>
        <Ionicons name="warning" size={48} color={COLORS.danger} />
        <Text style={styles.errorText}>{error}</Text>
      </View>
    );
  }

  if (!mapRegion) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Initializing tracking...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* Status Bar */}
      <View style={styles.statusBar}>
        <View style={styles.statusItem}>
          <View style={[styles.statusDot, { backgroundColor: isTracking ? COLORS.pickup : COLORS.danger }]} />
          <Text style={styles.statusText}>
            {isTracking ? 'Live Tracking' : 'Tracking Offline'}
          </Text>
        </View>
        
        {distance && (
          <View style={styles.statusItem}>
            <Ionicons name="navigate" size={14} color={COLORS.primary} />
            <Text style={styles.statusText}>
              {distance < 1 ? `${Math.round(distance * 1000)}m` : `${distance.toFixed(1)}km`} apart
            </Text>
          </View>
        )}
      </View>

      {/* Map */}
      <MapView
        ref={mapRef}
        style={styles.map}
        initialRegion={mapRegion}
        showsUserLocation={false}
        showsMyLocationButton={false}
        mapType="standard"
      >
        {/* Staff Location */}
        {staffLocation && (
          <Marker
            coordinate={staffLocation}
            title="Staff Member"
            description="Pickup driver location"
            pinColor={COLORS.primary}
          >
            <View style={[styles.markerContainer, { backgroundColor: COLORS.primary }]}>
              <Ionicons name="car" size={20} color="white" />
            </View>
          </Marker>
        )}

        {/* Customer Location */}
        {customerLocation && (
          <Marker
            coordinate={customerLocation}
            title="Your Location"
            description="Customer location"
            pinColor={COLORS.pickup}
          >
            <View style={[styles.markerContainer, { backgroundColor: COLORS.pickup }]}>
              <Ionicons name="person" size={20} color="white" />
            </View>
          </Marker>
        )}

        {/* Route Line */}
        {staffLocation && customerLocation && (
          <Polyline
            coordinates={[staffLocation, customerLocation]}
            strokeColor={COLORS.primary}
            strokeWidth={3}
            lineDashPattern={[5, 5]}
          />
        )}
      </MapView>

      {/* Location Info */}
      <View style={styles.infoPanel}>
        {staffLocation && (
          <View style={styles.infoItem}>
            <Ionicons name="car" size={16} color={COLORS.primary} />
            <Text style={styles.infoText}>
              Driver: {new Date(staffLocation.timestamp).toLocaleTimeString()}
            </Text>
          </View>
        )}
        
        {customerLocation && (
          <View style={styles.infoItem}>
            <Ionicons name="person" size={16} color={COLORS.pickup} />
            <Text style={styles.infoText}>
              You: {new Date(customerLocation.timestamp).toLocaleTimeString()}
            </Text>
          </View>
        )}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  statusBar: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(255,255,255,0.1)',
  },
  statusItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  statusDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
  },
  statusText: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },
  map: {
    flex: 1,
  },
  markerContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 3,
    borderColor: 'white',
  },
  infoPanel: {
    backgroundColor: COLORS.surface,
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderTopWidth: 1,
    borderTopColor: 'rgba(255,255,255,0.1)',
  },
  infoItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 4,
  },
  infoText: {
    fontSize: 12,
    color: COLORS.textSecondary,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: COLORS.background,
  },
  loadingText: {
    marginTop: 12,
    fontSize: 14,
    color: COLORS.textSecondary,
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: COLORS.background,
    padding: 20,
  },
  errorText: {
    marginTop: 12,
    fontSize: 14,
    color: COLORS.danger,
    textAlign: 'center',
  },
});