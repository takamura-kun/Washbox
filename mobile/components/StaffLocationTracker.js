import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
  Switch,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import SimpleLocationTrackingService from '../services/simpleLocationTrackingService';

const COLORS = {
  background: '#06081A',
  surface: '#0F1332',
  primary: '#0EA5E9',
  pickup: '#10B981',
  danger: '#EF4444',
  textPrimary: '#F1F5F9',
  textSecondary: '#94A3B8',
  textMuted: '#64748B',
  borderLight: 'rgba(255, 255, 255, 0.06)',
};

export default function StaffLocationTracker({ pickupRequestId, onLocationUpdate }) {
  const [isTracking, setIsTracking] = useState(false);
  const [currentLocation, setCurrentLocation] = useState(null);
  const [error, setError] = useState(null);

  useEffect(() => {
    return () => {
      // Cleanup on unmount
      if (isTracking) {
        SimpleLocationTrackingService.stopTracking();
      }
    };
  }, [isTracking]);

  const startTracking = async () => {
    try {
      setError(null);
      const success = await SimpleLocationTrackingService.startTracking(pickupRequestId, 'staff');
      
      if (success) {
        setIsTracking(true);
        
        // Get initial location
        const location = await SimpleLocationTrackingService.getCurrentLocation();
        if (location) {
          setCurrentLocation(location);
          if (onLocationUpdate) {
            onLocationUpdate(location);
          }
        }
        
        Alert.alert('Tracking Started', 'Your location is now being shared with the customer.');
      } else {
        throw new Error('Failed to start location tracking');
      }
    } catch (err) {
      setError(err.message);
      Alert.alert('Error', err.message);
    }
  };

  const stopTracking = async () => {
    try {
      await SimpleLocationTrackingService.stopTracking();
      setIsTracking(false);
      setCurrentLocation(null);
      Alert.alert('Tracking Stopped', 'Location sharing has been disabled.');
    } catch (err) {
      Alert.alert('Error', 'Failed to stop tracking');
    }
  };

  const toggleTracking = () => {
    if (isTracking) {
      stopTracking();
    } else {
      startTracking();
    }
  };

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <View style={styles.headerLeft}>
          <Ionicons 
            name={isTracking ? "navigate" : "navigate-outline"} 
            size={20} 
            color={isTracking ? COLORS.pickup : COLORS.textMuted} 
          />
          <Text style={styles.headerTitle}>Location Sharing</Text>
        </View>
        <Switch
          value={isTracking}
          onValueChange={toggleTracking}
          trackColor={{ false: COLORS.surface, true: COLORS.pickup + '40' }}
          thumbColor={isTracking ? COLORS.pickup : COLORS.textMuted}
        />
      </View>

      {error && (
        <View style={styles.errorContainer}>
          <Ionicons name="warning" size={16} color={COLORS.danger} />
          <Text style={styles.errorText}>{error}</Text>
        </View>
      )}

      {isTracking && (
        <View style={styles.trackingInfo}>
          <View style={styles.statusRow}>
            <View style={styles.statusDot} />
            <Text style={styles.statusText}>Live tracking active</Text>
          </View>
          
          {currentLocation && (
            <View style={styles.locationInfo}>
              <Text style={styles.locationLabel}>Current Location:</Text>
              <Text style={styles.locationCoords}>
                {currentLocation.latitude.toFixed(6)}, {currentLocation.longitude.toFixed(6)}
              </Text>
              <Text style={styles.accuracyText}>
                Accuracy: ±{Math.round(currentLocation.accuracy)}m
              </Text>
            </View>
          )}
          
          <Text style={styles.infoText}>
            Your location is being shared with the customer in real-time
          </Text>
        </View>
      )}

      {!isTracking && (
        <View style={styles.inactiveInfo}>
          <Text style={styles.inactiveText}>
            Enable location sharing to let customers track your progress
          </Text>
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 16,
    marginVertical: 8,
    borderWidth: 1,
    borderColor: COLORS.borderLight,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  headerLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  headerTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textPrimary,
  },
  errorContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    backgroundColor: COLORS.danger + '20',
    padding: 8,
    borderRadius: 8,
    marginBottom: 12,
  },
  errorText: {
    fontSize: 12,
    color: COLORS.danger,
    flex: 1,
  },
  trackingInfo: {
    gap: 8,
  },
  statusRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  statusDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: COLORS.pickup,
  },
  statusText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.pickup,
  },
  locationInfo: {
    backgroundColor: COLORS.background,
    padding: 12,
    borderRadius: 8,
    gap: 4,
  },
  locationLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textMuted,
  },
  locationCoords: {
    fontSize: 13,
    fontFamily: 'monospace',
    color: COLORS.textPrimary,
  },
  accuracyText: {
    fontSize: 11,
    color: COLORS.textSecondary,
  },
  infoText: {
    fontSize: 12,
    color: COLORS.textSecondary,
    lineHeight: 16,
  },
  inactiveInfo: {
    padding: 8,
  },
  inactiveText: {
    fontSize: 12,
    color: COLORS.textMuted,
    textAlign: 'center',
    lineHeight: 16,
  },
});