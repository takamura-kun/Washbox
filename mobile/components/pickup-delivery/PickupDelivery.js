import React, { useState, useEffect, useRef } from 'react';
import { View, StyleSheet, Text, TouchableOpacity, Alert, Modal, TextInput, KeyboardAvoidingView, Platform } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import OSMMap from '../common/OSMMap';
import { LocationService } from '../../services/locationService';
import { RoutingService } from '../../services/routingService';
import LocationCard from './LocationCard';

// Dumaguete City center — default fallback for WashBox
const DUMAGUETE_DEFAULT = {
  latitude: 9.3068,
  longitude: 123.3054,
  latitudeDelta: 0.05,
  longitudeDelta: 0.05,
};

const PickupDeliveryMap = ({
  pickupLocation,
  deliveryLocation,   // kept for order-tracking screens; null in pickup modal
  driverLocation,
  showRoute = true,
  onLocationSelect,
  interactive = true,
  style,
}) => {
  const mapRef = useRef(null);
  const [region, setRegion] = useState(null);
  const [route, setRoute] = useState(null);
  const [eta, setEta] = useState(null);
  const [selectedMarker, setSelectedMarker] = useState(null);
  const [userLocation, setUserLocation] = useState(null);
  const [tappedLocation, setTappedLocation] = useState(null); // Track tapped location
  const [centerLocation, setCenterLocation] = useState(null); // Track map center for pin
  const [showAddressEditModal, setShowAddressEditModal] = useState(false);
  const [editableAddress, setEditableAddress] = useState('');
  const [userInputAddress, setUserInputAddress] = useState(''); // Store user's typed address

  useEffect(() => {
    initializeMap();
  }, [pickupLocation, deliveryLocation]);

  const initializeMap = async () => {
    try {
      const location = await LocationService.getCurrentLocation();
      setUserLocation(location);

      let initialRegion;

      if (pickupLocation && deliveryLocation) {
        // Both locations — fit them plus user
        initialRegion = calculateRegionForCoordinates([pickupLocation, deliveryLocation, location]);
      } else if (pickupLocation) {
        // Pickup only (standard pickup modal case)
        initialRegion = { ...pickupLocation, latitudeDelta: 0.01, longitudeDelta: 0.01 };
      } else {
        // No locations yet — center on user
        initialRegion = { ...location, latitudeDelta: 0.02, longitudeDelta: 0.02 };
      }

      setRegion(initialRegion);

      // Only calculate route when we have both a pickup and a driver/delivery location
      if (pickupLocation && deliveryLocation && showRoute) {
        calculateRoute();
      }
    } catch (error) {
      console.error('Error initializing map:', error);
      // Default to Dumaguete City, not San Francisco
      setRegion(DUMAGUETE_DEFAULT);
    }
  };

  const calculateRoute = async () => {
    try {
      const points = [
        pickupLocation,
        ...(driverLocation ? [driverLocation] : []),
        deliveryLocation,
      ];

      const routeData = await RoutingService.getRoute(points);
      setRoute(routeData);

      const etaData = await RoutingService.getETA(
        driverLocation || pickupLocation,
        deliveryLocation
      );
      setEta(etaData);
    } catch (error) {
      console.error('Error calculating route:', error);
    }
  };

  const calculateRegionForCoordinates = (coordinates) => {
    const lats = coordinates.map(c => c.latitude);
    const lngs = coordinates.map(c => c.longitude);

    const minLat = Math.min(...lats);
    const maxLat = Math.max(...lats);
    const minLng = Math.min(...lngs);
    const maxLng = Math.max(...lngs);

    return {
      latitude: (minLat + maxLat) / 2,
      longitude: (minLng + maxLng) / 2,
      latitudeDelta: Math.max((maxLat - minLat) * 1.4, 0.01),
      longitudeDelta: Math.max((maxLng - minLng) * 1.4, 0.01),
    };
  };

  const handleMarkerPress = (marker) => {
    setSelectedMarker(marker);
    onLocationSelect?.(marker);
  };

  const handleMapPress = async (event) => {
    if (!interactive) return;
    
    // react-native-maps provides coordinate in event.nativeEvent
    const coordinate = event?.nativeEvent?.coordinate;
    
    if (!coordinate) {
      console.log('No coordinate from map press event');
      return;
    }
    
    console.log('Map tapped at:', coordinate);
    
    // Get address from coordinates (reverse geocoding)
    let address = 'Selected Location';
    try {
      address = await LocationService.getAddressFromCoordinate(coordinate);
    } catch (error) {
      console.error('Error getting address:', error);
    }
    
    // Store the tapped location with geotag data
    const geotaggedLocation = {
      ...coordinate,
      timestamp: new Date().toISOString(),
      accuracy: 'manual', // User tapped on map
      address,
    };
    
    setTappedLocation(geotaggedLocation);
    
    // Create a marker object for the tapped location with geotag info
    const marker = {
      coordinate,
      title: address,
      type: 'pickup',
      geotag: geotaggedLocation,
    };
    
    setSelectedMarker(marker);
    onLocationSelect?.(marker);
  };

  const handleRegionChange = (newRegion) => {
    setRegion(newRegion);
    // Track center for the center pin feature
    if (newRegion && interactive) {
      setCenterLocation({
        latitude: newRegion.latitude,
        longitude: newRegion.longitude,
      });
    }
  };

  const handleConfirmCenterLocation = async () => {
    if (!centerLocation) return;
    
    console.log('Center location confirmed:', centerLocation);
    
    // Get address from coordinates (reverse geocoding)
    let address = 'Selected Location';
    try {
      address = await LocationService.getAddressFromCoordinate(centerLocation);
    } catch (error) {
      console.error('Error getting address:', error);
    }
    
    // Store as tapped location with geotag data
    const geotaggedLocation = {
      ...centerLocation,
      timestamp: new Date().toISOString(),
      accuracy: 'manual', // User manually selected
      address,
    };
    
    setTappedLocation(geotaggedLocation);
    
    // Create marker with geotag info
    const marker = {
      coordinate: centerLocation,
      title: address,
      type: 'pickup',
      geotag: geotaggedLocation,
    };
    
    setSelectedMarker(marker);
    onLocationSelect?.(marker);
  };

  const handleCenterUser = async () => {
    try {
      const location = await LocationService.getCurrentLocation();
      mapRef.current?.animateToRegion(
        { ...location, latitudeDelta: 0.01, longitudeDelta: 0.01 },
        500
      );
    } catch {
      Alert.alert('Error', 'Unable to get your location');
    }
  };

  const handleFitAll = () => {
    const coords = [
      pickupLocation,
      deliveryLocation,
      userLocation,
    ].filter(Boolean);

    if (coords.length > 0) {
      mapRef.current?.animateToRegion(calculateRegionForCoordinates(coords), 500);
    }
  };

  const getMarkers = () => {
    const markers = [];

    if (userLocation) {
      markers.push({
        coordinate: userLocation,
        title: 'Your Location',
        type: 'user',
        customIcon: true,
        isActive: true,
      });
    }

    // Show tapped location marker if exists
    if (tappedLocation) {
      markers.push({
        coordinate: tappedLocation,
        title: 'Selected Location',
        type: 'pickup',
        customIcon: true,
        isActive: true,
      });
    }

    // Pickup marker — always shown when available
    if (pickupLocation && !tappedLocation) {
      markers.push({
        coordinate: pickupLocation,
        title: 'Pickup Location',
        type: 'pickup',
        customIcon: true,
        isActive: true,
      });
    }

    // Delivery marker — only used in order-tracking context, not in pickup modal
    // (delivery is same address as pickup so we don't show a separate pin)
    if (deliveryLocation) {
      markers.push({
        coordinate: deliveryLocation,
        title: 'Delivery Location',
        type: 'pickup',   // reuse pickup pin style since address is the same
        customIcon: true,
        isActive: true,
      });
    }

    if (driverLocation) {
      markers.push({
        coordinate: driverLocation,
        title: 'Driver',
        type: 'driver',
        customIcon: true,
        isActive: true,
      });
    }

    return markers;
  };

  const getPolylines = () => {
    const polylines = [];

    if (route?.coordinates) {
      polylines.push({
        coordinates: route.coordinates,
        color: '#0EA5E9',
        width: 4,
      });
    }

    // Dashed line from driver to pickup
    if (driverLocation && pickupLocation) {
      polylines.push({
        coordinates: [driverLocation, pickupLocation],
        color: '#10B981',
        width: 2,
        dashed: true,
      });
    }

    return polylines;
  };

  const getCircles = () => {
    if (!pickupLocation) return [];

    return [{
      center: pickupLocation,
      radius: 5000,
      fillColor: 'rgba(16, 185, 129, 0.04)',
      strokeColor: 'rgba(16, 185, 129, 0.25)',
      strokeWidth: 1,
    }];
  };

  const hasLocations = !!(pickupLocation || userLocation);

  return (
    <View style={[styles.container, style]}>
      <OSMMap
        ref={mapRef}
        initialRegion={region}
        markers={getMarkers()}
        polylines={getPolylines()}
        circles={getCircles()}
        onMarkerPress={handleMarkerPress}
        onMapPress={handleMapPress}
        onRegionChange={handleRegionChange}
        zoomEnabled={interactive}
        scrollEnabled={interactive}
        style={styles.map}
      />

      {/* Center Pin with Edit Icon - Tap to input address */}
      {interactive && !tappedLocation && (
        <View style={styles.centerPinContainer} pointerEvents="box-none">
          <TouchableOpacity 
            style={styles.centerPin}
            onPress={() => {
              console.log('Pin icon tapped, opening address input');
              setEditableAddress(userInputAddress || '');
              setShowAddressEditModal(true);
            }}
            activeOpacity={0.7}
          >
            <View style={styles.pinIconWrapper}>
              <Ionicons name="location" size={40} color="#10B981" />
              <View style={styles.pinEditBadge}>
                <Ionicons name="pencil" size={14} color="#FFF" />
              </View>
            </View>
          </TouchableOpacity>
          <View style={styles.centerPinShadow} pointerEvents="none" />
        </View>
      )}

      {/* Live Coordinates Display and Pin Button */}
      {interactive && centerLocation && !tappedLocation && (
        <View style={styles.confirmCenterWrapper}>
          <View style={styles.geotagInfo}>
            <View style={styles.geotagRow}>
              <Ionicons name="navigate" size={14} color="#10B981" />
              <Text style={styles.geotagText}>
                {centerLocation.latitude.toFixed(6)}, {centerLocation.longitude.toFixed(6)}
              </Text>
            </View>
            {userInputAddress ? (
              <View style={styles.geotagRow}>
                <Ionicons name="document-text" size={14} color="#0EA5E9" />
                <Text style={styles.addressPreview} numberOfLines={1}>
                  {userInputAddress}
                </Text>
              </View>
            ) : (
              <View style={styles.geotagRow}>
                <Ionicons name="hand-left-outline" size={14} color="#94A3B8" />
                <Text style={styles.geotagSubtext}>Tap pin icon to enter address</Text>
              </View>
            )}
          </View>
          
          {userInputAddress && (
            <TouchableOpacity 
              style={styles.confirmCenterButton}
              onPress={() => {
                if (!centerLocation || !userInputAddress) return;
                
                console.log('Pin This Location tapped:', {
                  address: userInputAddress,
                  coordinates: centerLocation,
                });
                
                // Create geotagged location with user's manually typed address
                const geotaggedLocation = {
                  latitude: centerLocation.latitude,
                  longitude: centerLocation.longitude,
                  timestamp: new Date().toISOString(),
                  accuracy: 'manual',
                  address: userInputAddress,
                  manuallyEdited: true, // CRITICAL: Mark as manually edited
                  geotag: {
                    latitude: centerLocation.latitude,
                    longitude: centerLocation.longitude,
                    address: userInputAddress,
                    timestamp: new Date().toISOString(),
                    accuracy: 'manual',
                    manuallyEdited: true, // CRITICAL: Mark as manually edited
                  },
                };
                
                setTappedLocation(geotaggedLocation);
                
                // Create marker with complete geotag info
                const marker = {
                  coordinate: centerLocation,
                  title: userInputAddress,
                  type: 'pickup',
                  geotag: geotaggedLocation.geotag,
                };
                
                console.log('Marker being sent to parent:', marker);
                setSelectedMarker(marker);
                onLocationSelect?.(marker);
              }}
              activeOpacity={0.85}
            >
              <View style={styles.confirmCenterContent}>
                <Ionicons name="checkmark-circle" size={20} color="#FFF" />
                <Text style={styles.confirmCenterText}>Pin This Location</Text>
              </View>
            </TouchableOpacity>
          )}
        </View>
      )}

      {/* Geotagged Location Confirmation */}
      {tappedLocation && tappedLocation.geotag && (
        <View style={styles.geotagConfirmation}>
          <View style={styles.geotagConfirmHeader}>
            <Ionicons name="checkmark-circle" size={20} color="#10B981" />
            <Text style={styles.geotagConfirmTitle}>Location Pinned</Text>
            <TouchableOpacity 
              style={styles.editAddressButton}
              onPress={() => {
                setEditableAddress(tappedLocation.address || '');
                setShowAddressEditModal(true);
              }}
            >
              <Ionicons name="pencil" size={16} color="#0EA5E9" />
            </TouchableOpacity>
          </View>
          <View style={styles.geotagDetails}>
            <View style={styles.geotagDetailRow}>
              <Ionicons name="location" size={14} color="#0EA5E9" />
              <Text style={styles.geotagDetailText} numberOfLines={2}>
                {tappedLocation.address || 'Selected Location'}
              </Text>
            </View>
            <View style={styles.geotagDetailRow}>
              <Ionicons name="navigate" size={14} color="#10B981" />
              <Text style={styles.geotagDetailText}>
                {tappedLocation.latitude.toFixed(6)}, {tappedLocation.longitude.toFixed(6)}
              </Text>
            </View>
            <View style={styles.geotagDetailRow}>
              <Ionicons name="time-outline" size={14} color="#94A3B8" />
              <Text style={styles.geotagDetailText}>
                {new Date(tappedLocation.timestamp).toLocaleTimeString()}
              </Text>
            </View>
          </View>
        </View>
      )}

      {/* Address Edit Modal */}
      <Modal
        visible={showAddressEditModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowAddressEditModal(false)}
      >
        <KeyboardAvoidingView 
          style={styles.addressModalOverlay}
          behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        >
          <View style={styles.addressModalContent}>
            <View style={styles.addressModalHeader}>
              <View style={styles.addressModalTitleRow}>
                <Ionicons name="create-outline" size={24} color="#0EA5E9" />
                <Text style={styles.addressModalTitle}>Edit Address</Text>
              </View>
              <TouchableOpacity 
                onPress={() => setShowAddressEditModal(false)}
                style={styles.addressModalClose}
              >
                <Ionicons name="close" size={24} color="#94A3B8" />
              </TouchableOpacity>
            </View>

            <View style={styles.addressInputContainer}>
              <Text style={styles.addressInputLabel}>Full Address</Text>
              <TextInput
                style={styles.addressInput}
                value={editableAddress}
                onChangeText={setEditableAddress}
                placeholder="Enter complete address..."
                placeholderTextColor="#64748B"
                multiline
                numberOfLines={4}
                textAlignVertical="top"
              />
              <Text style={styles.addressInputHint}>
                📍 Enter your complete address. You can adjust the pin position on the map after saving.
              </Text>
            </View>

            {centerLocation && (
              <View style={styles.coordinatesDisplay}>
                <Ionicons name="navigate" size={16} color="#10B981" />
                <Text style={styles.coordinatesText}>
                  Current Pin: {centerLocation.latitude.toFixed(6)}, {centerLocation.longitude.toFixed(6)}
                </Text>
              </View>
            )}

            <View style={styles.addressModalActions}>
              <TouchableOpacity 
                style={styles.addressCancelButton}
                onPress={() => setShowAddressEditModal(false)}
              >
                <Text style={styles.addressCancelText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity 
                style={styles.addressSaveButton}
                onPress={() => {
                  if (editableAddress.trim()) {
                    setUserInputAddress(editableAddress.trim());
                    setShowAddressEditModal(false);
                    Alert.alert(
                      'Address Saved',
                      'Now drag the map to adjust the pin position, then tap "Pin This Location" to confirm.',
                      [{ text: 'OK' }]
                    );
                  } else {
                    Alert.alert('Invalid Address', 'Please enter a valid address');
                  }
                }}
              >
                <Ionicons name="checkmark-circle" size={20} color="#FFF" />
                <Text style={styles.addressSaveText}>Save Address</Text>
              </TouchableOpacity>
            </View>
          </View>
        </KeyboardAvoidingView>
      </Modal>

      {/* Controls */}
      <View style={styles.controls}>
        <TouchableOpacity style={styles.controlButton} onPress={handleCenterUser}>
          <Ionicons name="locate" size={22} color="#0EA5E9" />
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.controlButton, !hasLocations && styles.controlDisabled]}
          onPress={handleFitAll}
          disabled={!hasLocations}
        >
          <Ionicons name="expand" size={22} color={hasLocations ? '#0EA5E9' : '#64748B'} />
        </TouchableOpacity>
      </View>

      {/* ETA — only shown for order tracking (when driver is present) */}
      {eta && driverLocation && (
        <View style={styles.etaContainer}>
          <View style={styles.etaRow}>
            <Ionicons name="time-outline" size={16} color="#0EA5E9" />
            <Text style={styles.etaText}>
              {eta.formattedDuration}  ·  {eta.formattedDistance}
            </Text>
          </View>
          <Text style={styles.etaSubText}>
            Est. arrival {eta.arrivalTime.formatted}
          </Text>
        </View>
      )}

      {/* Selected Location Card */}
      {selectedMarker && (
        <LocationCard
          location={selectedMarker}
          onClose={() => setSelectedMarker(null)}
        />
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  map: {
    flex: 1,
  },
  controls: {
    position: 'absolute',
    top: 16,
    right: 16,
    gap: 8,
  },
  controlButton: {
    backgroundColor: '#0F1332',
    width: 42,
    height: 42,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.08)',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 4,
  },
  controlDisabled: {
    opacity: 0.4,
  },
  etaContainer: {
    position: 'absolute',
    bottom: 16,
    left: 16,
    right: 16,
    backgroundColor: '#0F1332',
    padding: 14,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.08)',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 6,
    elevation: 5,
  },
  etaRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginBottom: 4,
  },
  etaText: {
    fontSize: 15,
    fontWeight: '700',
    color: '#F1F5F9',
  },
  etaSubText: {
    fontSize: 12,
    color: '#94A3B8',
    marginLeft: 22,
  },
  centerPinContainer: {
    position: 'absolute',
    top: '50%',
    left: '50%',
    marginLeft: -20,
    marginTop: -40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  centerPin: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 5,
  },
  pinIconWrapper: {
    position: 'relative',
  },
  pinEditBadge: {
    position: 'absolute',
    bottom: 2,
    right: -2,
    width: 22,
    height: 22,
    borderRadius: 11,
    backgroundColor: '#0EA5E9',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#0F1332',
  },
  centerPinShadow: {
    width: 20,
    height: 8,
    borderRadius: 10,
    backgroundColor: 'rgba(0, 0, 0, 0.2)',
    marginTop: -8,
  },
  confirmCenterWrapper: {
    position: 'absolute',
    bottom: 80,
    left: 16,
    right: 16,
    gap: 8,
  },
  geotagInfo: {
    backgroundColor: '#0F1332',
    borderRadius: 12,
    padding: 12,
    borderWidth: 1,
    borderColor: 'rgba(16, 185, 129, 0.3)',
    gap: 6,
  },
  geotagRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  geotagText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#10B981',
    fontFamily: 'monospace',
  },
  geotagSubtext: {
    fontSize: 11,
    color: '#94A3B8',
  },
  addressPreview: {
    fontSize: 12,
    color: '#0EA5E9',
    fontWeight: '600',
    flex: 1,
  },
  confirmCenterButton: {
    backgroundColor: '#10B981',
    borderRadius: 14,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 6,
    elevation: 6,
  },
  confirmCenterContent: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    paddingVertical: 14,
  },
  confirmCenterText: {
    fontSize: 15,
    fontWeight: '700',
    color: '#FFF',
  },
  geotagConfirmation: {
    position: 'absolute',
    bottom: 80,
    left: 16,
    right: 16,
    backgroundColor: '#0F1332',
    borderRadius: 14,
    padding: 16,
    borderWidth: 1,
    borderColor: 'rgba(16, 185, 129, 0.4)',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 6,
    elevation: 6,
  },
  geotagConfirmHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 12,
  },
  geotagConfirmTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#10B981',
    flex: 1,
  },
  editAddressButton: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: 'rgba(14, 165, 233, 0.15)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  geotagDetails: {
    gap: 8,
  },
  geotagDetailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  geotagDetailText: {
    fontSize: 12,
    color: '#F1F5F9',
    flex: 1,
  },
  addressModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.7)',
    justifyContent: 'flex-end',
  },
  addressModalContent: {
    backgroundColor: '#0F1332',
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    padding: 24,
    paddingBottom: Platform.OS === 'ios' ? 40 : 24,
    maxHeight: '80%',
  },
  addressModalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 24,
  },
  addressModalTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  addressModalTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#F1F5F9',
  },
  addressModalClose: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: '#171D45',
    justifyContent: 'center',
    alignItems: 'center',
  },
  addressInputContainer: {
    marginBottom: 20,
  },
  addressInputLabel: {
    fontSize: 13,
    fontWeight: '600',
    color: '#94A3B8',
    marginBottom: 8,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  addressInput: {
    backgroundColor: '#171D45',
    borderRadius: 12,
    padding: 16,
    color: '#F1F5F9',
    fontSize: 15,
    minHeight: 120,
    borderWidth: 1,
    borderColor: 'rgba(14, 165, 233, 0.3)',
  },
  addressInputHint: {
    fontSize: 12,
    color: '#64748B',
    marginTop: 8,
    fontStyle: 'italic',
  },
  coordinatesDisplay: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    backgroundColor: 'rgba(16, 185, 129, 0.1)',
    padding: 12,
    borderRadius: 10,
    marginBottom: 20,
  },
  coordinatesText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#10B981',
    fontFamily: 'monospace',
  },
  addressModalActions: {
    flexDirection: 'row',
    gap: 12,
  },
  addressCancelButton: {
    flex: 1,
    backgroundColor: '#171D45',
    paddingVertical: 14,
    borderRadius: 12,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#1E293B',
  },
  addressCancelText: {
    fontSize: 15,
    fontWeight: '600',
    color: '#94A3B8',
  },
  addressSaveButton: {
    flex: 2,
    backgroundColor: '#0EA5E9',
    paddingVertical: 14,
    borderRadius: 12,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
  },
  addressSaveText: {
    fontSize: 15,
    fontWeight: '700',
    color: '#FFF',
  },
});

export default PickupDeliveryMap;