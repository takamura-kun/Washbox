import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
  RefreshControl,
  Platform,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router, useLocalSearchParams } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { LinearGradient } from 'expo-linear-gradient';
import LocationTrackingService from '../services/simpleLocationTrackingService';
import { API_BASE_URL, STORAGE_KEYS } from '../constants/config';

const COLORS = {
  background: '#06081A',
  surface: '#0F1332',
  surfaceLight: '#171D45',
  primary: '#0EA5E9',
  pickup: '#10B981',
  warning: '#F59E0B',
  danger: '#EF4444',
  textPrimary: '#F1F5F9',
  textSecondary: '#94A3B8',
  textMuted: '#64748B',
  borderLight: 'rgba(255, 255, 255, 0.06)',
  gradientPrimary: ['#0EA5E9', '#3B82F6'],
  gradientPickup: ['#10B981', '#059669'],
};

export default function PickupTrackingScreen() {
  const { id } = useLocalSearchParams();
  const [pickup, setPickup] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [staffLocation, setStaffLocation] = useState(null);
  const [distance, setDistance] = useState(null);
  const [estimatedArrival, setEstimatedArrival] = useState(null);

  useEffect(() => {
    fetchPickupDetails();
    const interval = setInterval(fetchPickupDetails, 30000);

    // Auto-refresh when FCM notification arrives for this pickup
    global.__onFCMNotification = (data) => {
      if (data?.type?.includes('pickup_')) fetchPickupDetails();
    };

    return () => {
      clearInterval(interval);
      global.__onFCMNotification = null;
    };
  }, [id]);

  const fetchPickupDetails = async () => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) {
        router.replace('/(auth)/login');
        return;
      }

      const response = await fetch(`${API_BASE_URL}/v1/pickups/${id}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (response.ok) {
        const data = await response.json();
        // Handle nested response structure
        const pickupData = data.data?.pickup || data.data;
        setPickup(pickupData);
        
        // Get location data if staff is assigned
        if (pickupData?.assigned_to) {
          fetchLocationData();
        }
      } else {
        Alert.alert('Error', 'Failed to load pickup details');
      }
    } catch (error) {
      console.error('Error fetching pickup:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const fetchLocationData = async () => {
    try {
      const locationData = await LocationTrackingService.getPickupLocations(id);
      console.log('Location data received:', locationData);
      
      // Handle both response formats
      const data = locationData?.data || locationData;
      
      if (data && data.staff_location) {
        setStaffLocation(data.staff_location);
        calculateDistance(data.staff_location);
      } else {
        console.log('No staff location available yet');
      }
    } catch (error) {
      console.error('Error fetching location data:', error);
    }
  };

  const calculateDistance = (staffLoc) => {
    if (!staffLoc || !pickup?.latitude || !pickup?.longitude) {
      console.log('Missing location data:', { staffLoc, pickupLat: pickup?.latitude, pickupLon: pickup?.longitude });
      return;
    }

    const R = 6371; // Earth's radius in km
    const dLat = (pickup.latitude - staffLoc.latitude) * Math.PI / 180;
    const dLon = (pickup.longitude - staffLoc.longitude) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(staffLoc.latitude * Math.PI / 180) * Math.cos(pickup.latitude * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    const dist = R * c;

    setDistance(dist);
    
    // Estimate arrival time (assuming 30 km/h average speed)
    const estimatedMinutes = Math.round((dist / 30) * 60);
    setEstimatedArrival(estimatedMinutes);
  };

  const handleLocationUpdate = useCallback((locationData) => {
    if (locationData.user_type === 'staff') {
      setStaffLocation({
        latitude: locationData.latitude,
        longitude: locationData.longitude,
        updated_at: locationData.timestamp,
      });
      calculateDistance({
        latitude: locationData.latitude,
        longitude: locationData.longitude,
      });
    }
  }, [pickup]);

  const onRefresh = useCallback(() => {
    setRefreshing(true);
    fetchPickupDetails();
  }, []);

  const getStatusColor = (status) => {
    switch (status) {
      case 'pending': return COLORS.warning;
      case 'accepted': return COLORS.primary;
      case 'confirmed': return COLORS.primary;
      case 'en_route': return COLORS.pickup;
      case 'picked_up': return COLORS.pickup;
      case 'cancelled': return COLORS.danger;
      default: return COLORS.textMuted;
    }
  };

  const getStatusText = (status) => {
    switch (status) {
      case 'pending': return 'Waiting for driver';
      case 'accepted': return 'Driver assigned';
      case 'confirmed': return 'Driver assigned';
      case 'en_route': return 'Driver on the way';
      case 'picked_up': return 'Picked up';
      case 'cancelled': return 'Cancelled';
      default: return status;
    }
  };

  if (loading) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Loading pickup details...</Text>
      </View>
    );
  }

  if (!pickup) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <Ionicons name="alert-circle" size={48} color={COLORS.danger} />
        <Text style={styles.errorText}>Pickup not found</Text>
        <TouchableOpacity style={styles.backButton} onPress={() => router.back()}>
          <Text style={styles.backButtonText}>Go Back</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const showLiveTracking = pickup.status === 'en_route' || (pickup.status === 'accepted' && staffLocation);

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.backBtn} onPress={() => router.back()}>
          <Ionicons name="arrow-back" size={24} color={COLORS.textPrimary} />
        </TouchableOpacity>
        <View style={styles.headerContent}>
          <Text style={styles.headerTitle}>Pickup Tracking</Text>
          <Text style={styles.headerSubtitle}>#{pickup.id}</Text>
        </View>
        <TouchableOpacity style={styles.refreshBtn} onPress={onRefresh}>
          <Ionicons name="refresh" size={20} color={COLORS.primary} />
        </TouchableOpacity>
      </View>

      <ScrollView
        style={styles.scrollView}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        showsVerticalScrollIndicator={false}
      >
        {/* Status Card */}
        <View style={styles.statusCard}>
          <LinearGradient
            colors={[getStatusColor(pickup.status) + '20', getStatusColor(pickup.status) + '05']}
            style={styles.statusGradient}
          >
            <View style={styles.statusHeader}>
              <View style={[styles.statusDot, { backgroundColor: getStatusColor(pickup.status) }]} />
              <Text style={[styles.statusText, { color: getStatusColor(pickup.status) }]}>
                {getStatusText(pickup.status)}
              </Text>
            </View>
            
            {pickup.assigned_staff && (
              <View style={styles.driverInfo}>
                <Ionicons name="person" size={16} color={COLORS.textSecondary} />
                <Text style={styles.driverText}>
                  Driver: {pickup.assigned_staff.name}
                </Text>
              </View>
            )}

            {distance && (
              <View style={styles.distanceInfo}>
                <Ionicons name="navigate" size={16} color={COLORS.primary} />
                <Text style={styles.distanceText}>
                  {distance < 1 ? `${Math.round(distance * 1000)}m away` : `${distance.toFixed(1)}km away`}
                </Text>
                {estimatedArrival && (
                  <Text style={styles.etaText}>
                    • ETA: {estimatedArrival} min
                  </Text>
                )}
              </View>
            )}
          </LinearGradient>
        </View>

        {/* Live Tracking Map */}
        {showLiveTracking && (
          <View style={styles.mapCard}>
            <View style={styles.mapHeader}>
              <Ionicons name="location" size={20} color={COLORS.pickup} />
              <Text style={styles.mapTitle}>Live Tracking</Text>
              {staffLocation && (
                <View style={styles.liveIndicator}>
                  <View style={styles.liveDot} />
                  <Text style={styles.liveText}>LIVE</Text>
                </View>
              )}
            </View>
            <View style={styles.mapContainer}>
              <View style={styles.locationDisplay}>
                {staffLocation ? (
                  <>
                    <Ionicons name="checkmark-circle" size={32} color={COLORS.pickup} style={{ marginBottom: 8 }} />
                    <Text style={styles.locationText}>Driver location active</Text>
                    <Text style={styles.locationSubtext}>
                      Last updated: {staffLocation.updated_at ? new Date(staffLocation.updated_at).toLocaleTimeString() : 'Just now'}
                    </Text>
                  </>
                ) : (
                  <>
                    <Ionicons name="time-outline" size={32} color={COLORS.textMuted} style={{ marginBottom: 8 }} />
                    <Text style={styles.locationText}>Waiting for driver location</Text>
                    <Text style={styles.locationSubtext}>Location will appear when driver starts tracking</Text>
                  </>
                )}
              </View>
            </View>
          </View>
        )}

        {/* Pickup Details */}
        <View style={styles.detailsCard}>
          <Text style={styles.cardTitle}>Pickup Details</Text>
          
          <View style={styles.detailRow}>
            <Ionicons name="location" size={16} color={COLORS.pickup} />
            <View style={styles.detailContent}>
              <Text style={styles.detailLabel}>Pickup Address</Text>
              <Text style={styles.detailValue}>{pickup.pickup_address}</Text>
            </View>
          </View>

          <View style={styles.detailRow}>
            <Ionicons name="calendar" size={16} color={COLORS.primary} />
            <View style={styles.detailContent}>
              <Text style={styles.detailLabel}>Scheduled</Text>
              <Text style={styles.detailValue}>
                {new Date(pickup.preferred_date).toLocaleDateString()} at {pickup.preferred_time}
              </Text>
            </View>
          </View>

          <View style={styles.detailRow}>
            <Ionicons name="business" size={16} color={COLORS.textMuted} />
            <View style={styles.detailContent}>
              <Text style={styles.detailLabel}>Branch</Text>
              <Text style={styles.detailValue}>{pickup.branch?.name}</Text>
            </View>
          </View>

          {pickup.special_instructions && (
            <View style={styles.detailRow}>
              <Ionicons name="chatbox-ellipses" size={16} color={COLORS.textMuted} />
              <View style={styles.detailContent}>
                <Text style={styles.detailLabel}>Instructions</Text>
                <Text style={styles.detailValue}>{pickup.special_instructions}</Text>
              </View>
            </View>
          )}
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  centerContent: {
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: Platform.OS === 'ios' ? 60 : 48,
    paddingBottom: 16,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  backBtn: {
    width: 40,
    height: 40,
    borderRadius: 12,
    backgroundColor: COLORS.surface,
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerContent: {
    flex: 1,
    marginLeft: 16,
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.textPrimary,
  },
  headerSubtitle: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginTop: 2,
  },
  refreshBtn: {
    width: 40,
    height: 40,
    borderRadius: 12,
    backgroundColor: COLORS.surface,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scrollView: {
    flex: 1,
  },
  statusCard: {
    margin: 20,
    borderRadius: 16,
    overflow: 'hidden',
  },
  statusGradient: {
    padding: 20,
  },
  statusHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  statusDot: {
    width: 12,
    height: 12,
    borderRadius: 6,
    marginRight: 8,
  },
  statusText: {
    fontSize: 18,
    fontWeight: '700',
  },
  driverInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  driverText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginLeft: 8,
  },
  distanceInfo: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  distanceText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.primary,
    marginLeft: 8,
  },
  etaText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginLeft: 4,
  },
  mapCard: {
    backgroundColor: COLORS.surface,
    marginHorizontal: 20,
    marginBottom: 20,
    borderRadius: 16,
    overflow: 'hidden',
  },
  mapHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  mapTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginLeft: 8,
    flex: 1,
  },
  liveIndicator: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  liveDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: COLORS.danger,
    marginRight: 4,
  },
  liveText: {
    fontSize: 10,
    fontWeight: '700',
    color: COLORS.danger,
  },
  mapContainer: {
    height: 300,
  },
  locationDisplay: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: COLORS.surfaceLight,
  },
  locationText: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginBottom: 8,
  },
  locationSubtext: {
    fontSize: 12,
    color: COLORS.textSecondary,
  },
  detailsCard: {
    backgroundColor: COLORS.surface,
    marginHorizontal: 20,
    marginBottom: 20,
    borderRadius: 16,
    padding: 20,
  },
  cardTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 16,
  },
  detailRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 16,
  },
  detailContent: {
    flex: 1,
    marginLeft: 12,
  },
  detailLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textMuted,
    marginBottom: 4,
  },
  detailValue: {
    fontSize: 14,
    color: COLORS.textPrimary,
    lineHeight: 20,
  },
  loadingText: {
    marginTop: 12,
    fontSize: 14,
    color: COLORS.textSecondary,
  },
  errorText: {
    marginTop: 12,
    fontSize: 16,
    color: COLORS.danger,
    textAlign: 'center',
  },
  backButton: {
    marginTop: 20,
    backgroundColor: COLORS.primary,
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 8,
  },
  backButtonText: {
    color: 'white',
    fontWeight: '600',
  },
});