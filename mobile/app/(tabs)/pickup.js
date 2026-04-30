import React, { useState, useEffect, useRef, useMemo } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  TextInput,
  Alert,
  ActivityIndicator,
  Platform,
  Modal,
  Dimensions,
  Animated,
  KeyboardAvoidingView,
  Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import DateTimePicker from '@react-native-community/datetimepicker';
import { router, useLocalSearchParams, useFocusEffect } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { LinearGradient } from 'expo-linear-gradient';
import * as ImagePicker from 'expo-image-picker';
import { API_BASE_URL, STORAGE_KEYS, ENDPOINTS } from '../../constants/config';

// Import map components
import PickupDeliveryMap from '../../components/pickup-delivery/PickupDelivery';
import LocationSearch from '../../components/pickup-delivery/LocationSearch';
import { LocationService } from '../../services/locationService';
import { useLocationStore } from '../../store/locationStore';
import PSGCAddressSelector from '../../components/common/PSGCAddressSelector';

const { width, height } = Dimensions.get('window');

// ─────────────────────────────────────────────
// DESIGN SYSTEM
// ─────────────────────────────────────────────
const COLORS = {
  background: '#06081A',
  surface: '#0F1332',
  surfaceLight: '#171D45',
  surfaceElevated: '#1E2654',
  primary: '#0EA5E9',
  primaryDark: '#0284C7',
  primaryGlow: 'rgba(14, 165, 233, 0.15)',
  primarySoft: 'rgba(14, 165, 233, 0.08)',
  pickup: '#10B981',
  pickupGlow: 'rgba(16, 185, 129, 0.15)',
  success: '#10B981',
  warning: '#F59E0B',
  danger: '#EF4444',
  textPrimary: '#F1F5F9',
  textSecondary: '#94A3B8',
  textMuted: '#64748B',
  textInverse: '#0F172A',
  border: '#1E293B',
  borderLight: 'rgba(255, 255, 255, 0.06)',
  gradientPickup: ['#10B981', '#059669'],
  gradientPrimary: ['#0EA5E9', '#3B82F6'],
  gradientSurface: ['#0F1332', '#171D45'],
};

// ─────────────────────────────────────────────
// STEP INDICATOR
// ─────────────────────────────────────────────
const StepIndicator = ({ currentStep, steps }) => (
  <View style={stepStyles.container}>
    {steps.map((step, index) => {
      const isActive = index === currentStep;
      const isCompleted = index < currentStep;
      const isLast = index === steps.length - 1;
      return (
        <View key={index} style={stepStyles.stepRow}>
          <View style={[
            stepStyles.dot,
            isActive && stepStyles.dotActive,
            isCompleted && stepStyles.dotCompleted,
          ]}>
            {isCompleted ? (
              <Ionicons name="checkmark" size={12} color="#FFF" />
            ) : (
              <Text style={[
                stepStyles.dotNumber,
                (isActive || isCompleted) && stepStyles.dotNumberActive,
              ]}>
                {index + 1}
              </Text>
            )}
            {isActive && <View style={stepStyles.dotPulse} />}
          </View>
          <Text style={[
            stepStyles.label,
            isActive && stepStyles.labelActive,
            isCompleted && stepStyles.labelCompleted,
          ]}>
            {step}
          </Text>
          {!isLast && (
            <View style={[stepStyles.connector, isCompleted && stepStyles.connectorCompleted]} />
          )}
        </View>
      );
    })}
  </View>
);

const stepStyles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 24,
    paddingVertical: 16,
  },
  stepRow: { flexDirection: 'row', alignItems: 'center' },
  dot: {
    width: 26, height: 26, borderRadius: 13,
    backgroundColor: COLORS.surfaceElevated,
    justifyContent: 'center', alignItems: 'center',
    borderWidth: 2, borderColor: COLORS.border,
  },
  dotActive: { backgroundColor: COLORS.primary, borderColor: COLORS.primary },
  dotCompleted: { backgroundColor: COLORS.pickup, borderColor: COLORS.pickup },
  dotPulse: {
    position: 'absolute', width: 38, height: 38, borderRadius: 19,
    backgroundColor: 'rgba(14, 165, 233, 0.2)',
  },
  dotNumber: { fontSize: 11, fontWeight: '700', color: COLORS.textMuted },
  dotNumberActive: { color: '#FFF' },
  label: { fontSize: 11, fontWeight: '600', color: COLORS.textMuted, marginLeft: 6, letterSpacing: 0.3 },
  labelActive: { color: COLORS.primary },
  labelCompleted: { color: COLORS.pickup },
  connector: { width: 20, height: 2, backgroundColor: COLORS.border, marginHorizontal: 6, borderRadius: 1 },
  connectorCompleted: { backgroundColor: COLORS.pickup },
});

// ─────────────────────────────────────────────
// PICKUP LOCATION CARD with Saved Addresses
// ─────────────────────────────────────────────
const PickupCard = ({ pickupAddress, pickupCoords, onPickupPress, onAddressSelect, savedAddresses, selectedAddressId, deliveryFeeInfo }) => {
  const selectedAddress = savedAddresses.find(addr => addr.id === selectedAddressId);
  
  return (
    <View style={routeStyles.card}>
      {/* Pickup row */}
      <TouchableOpacity style={routeStyles.locationRow} onPress={onPickupPress} activeOpacity={0.7}>
        <View style={routeStyles.iconColumn}>
          <View style={[routeStyles.iconCircle, { backgroundColor: COLORS.pickupGlow }]}>
            <View style={[routeStyles.iconDot, { backgroundColor: COLORS.pickup }]} />
          </View>
        </View>
        <View style={routeStyles.locationContent}>
          <View style={routeStyles.locationHeader}>
            <Text style={routeStyles.locationLabel}>PICKUP FROM</Text>
            {selectedAddress && (
              <View style={[routeStyles.addressBadge, { backgroundColor: selectedAddress.icon === 'home-outline' ? COLORS.success + '20' : COLORS.primary + '20' }]}>
                <Ionicons 
                  name={selectedAddress.icon} 
                  size={10} 
                  color={selectedAddress.icon === 'home-outline' ? COLORS.success : COLORS.primary} 
                />
                <Text style={[routeStyles.addressBadgeText, { color: selectedAddress.icon === 'home-outline' ? COLORS.success : COLORS.primary }]}>
                  {selectedAddress.label}
                </Text>
              </View>
            )}
          </View>
          {pickupAddress ? (
            <Text style={routeStyles.locationAddress} numberOfLines={2}>{pickupAddress}</Text>
          ) : (
            <Text style={routeStyles.locationPlaceholder}>Tap to set your pickup location</Text>
          )}
          {pickupCoords && (
            <View style={routeStyles.coordRow}>
              <Ionicons name="navigate-outline" size={10} color={COLORS.pickup} />
              <Text style={[routeStyles.coordText, { color: COLORS.pickup }]}>
                {pickupCoords.latitude.toFixed(4)}, {pickupCoords.longitude.toFixed(4)}
              </Text>
            </View>
          )}
        </View>
        <View style={[routeStyles.actionChip, { borderColor: COLORS.pickup + '40' }]}>
          <Ionicons name={pickupAddress ? 'pencil' : 'add'} size={14} color={COLORS.pickup} />
        </View>
      </TouchableOpacity>

      {/* Quick Address Selection */}
      {savedAddresses.length > 0 && (
        <View style={routeStyles.quickAddresses}>
          <Text style={routeStyles.quickAddressesLabel}>Quick Select:</Text>
          <ScrollView horizontal showsHorizontalScrollIndicator={false} style={routeStyles.addressScroll}>
            {savedAddresses.slice(0, 3).map((address) => (
              <TouchableOpacity
                key={address.id}
                style={[
                  routeStyles.quickAddressChip,
                  selectedAddressId === address.id && routeStyles.quickAddressChipSelected
                ]}
                onPress={() => onAddressSelect(address)}
              >
                <Ionicons 
                  name={address.icon} 
                  size={12} 
                  color={selectedAddressId === address.id ? COLORS.primary : COLORS.textMuted} 
                />
                <Text style={[
                  routeStyles.quickAddressText,
                  selectedAddressId === address.id && { color: COLORS.primary }
                ]}>
                  {address.label}
                </Text>
              </TouchableOpacity>
            ))}
            {savedAddresses.length > 3 && (
              <TouchableOpacity style={routeStyles.moreAddressesChip} onPress={onPickupPress}>
                <Text style={routeStyles.moreAddressesText}>+{savedAddresses.length - 3} more</Text>
              </TouchableOpacity>
            )}
          </ScrollView>
        </View>
      )}

      {/* Delivery info banner */}
      {pickupAddress && (
        <View style={routeStyles.deliveryBanner}>
          <Ionicons name="refresh-outline" size={14} color={COLORS.primary} />
          <Text style={routeStyles.deliveryBannerText}>
            Your laundry will be delivered back to the same address
          </Text>
        </View>
      )}

      {/* Delivery Fee Info */}
      {deliveryFeeInfo && (
        <View style={[
          routeStyles.feeBanner,
          deliveryFeeInfo.is_free ? { backgroundColor: COLORS.pickupGlow, borderColor: COLORS.pickup + '40' } : { backgroundColor: COLORS.warning + '15', borderColor: COLORS.warning + '40' }
        ]}>
          <Ionicons 
            name={deliveryFeeInfo.is_free ? 'pricetag' : 'cash-outline'} 
            size={14} 
            color={deliveryFeeInfo.is_free ? COLORS.pickup : COLORS.warning} 
          />
          <Text style={[
            routeStyles.feeBannerText,
            { color: deliveryFeeInfo.is_free ? COLORS.pickup : COLORS.warning }
          ]}>
            {deliveryFeeInfo.message}
          </Text>
        </View>
      )}
    </View>
  );
};

const routeStyles = StyleSheet.create({
  card: {
    backgroundColor: COLORS.surface,
    borderRadius: 20,
    padding: 20,
    marginHorizontal: 20,
    borderWidth: 1,
    borderColor: COLORS.borderLight,
    gap: 16,
  },
  locationRow: { flexDirection: 'row', alignItems: 'flex-start', gap: 14 },
  iconColumn: { alignItems: 'center', width: 32 },
  iconCircle: { width: 32, height: 32, borderRadius: 16, justifyContent: 'center', alignItems: 'center' },
  iconDot: { width: 10, height: 10, borderRadius: 5 },
  locationContent: { flex: 1, paddingBottom: 6 },
  locationHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 4,
  },
  locationLabel: {
    fontSize: 10, fontWeight: '800', color: COLORS.textMuted,
    letterSpacing: 1.2,
  },
  addressBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 6,
  },
  addressBadgeText: {
    fontSize: 9,
    fontWeight: '600',
    textTransform: 'uppercase',
  },
  locationAddress: { fontSize: 14, fontWeight: '600', color: COLORS.textPrimary, lineHeight: 20 },
  locationPlaceholder: { fontSize: 14, color: COLORS.textMuted, fontStyle: 'italic' },
  coordRow: { flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: 4 },
  coordText: {
    fontSize: 10,
    fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace',
    fontWeight: '600',
  },
  actionChip: {
    width: 32, height: 32, borderRadius: 10, borderWidth: 1.5,
    justifyContent: 'center', alignItems: 'center', marginTop: 2,
  },
  
  // Quick Address Selection
  quickAddresses: {
    paddingTop: 8,
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
  },
  quickAddressesLabel: {
    fontSize: 10,
    fontWeight: '700',
    color: COLORS.textMuted,
    letterSpacing: 0.5,
    marginBottom: 8,
  },
  addressScroll: {
    flexDirection: 'row',
  },
  quickAddressChip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: COLORS.surfaceElevated,
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 12,
    marginRight: 8,
    borderWidth: 1,
    borderColor: 'transparent',
  },
  quickAddressChipSelected: {
    backgroundColor: COLORS.primarySoft,
    borderColor: COLORS.primary + '40',
  },
  quickAddressText: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textMuted,
  },
  moreAddressesChip: {
    backgroundColor: COLORS.surfaceElevated,
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 12,
    justifyContent: 'center',
  },
  moreAddressesText: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.textMuted,
  },
  
  deliveryBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    backgroundColor: COLORS.primarySoft,
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: 12,
  },
  deliveryBannerText: {
    fontSize: 12,
    fontWeight: '500',
    color: COLORS.textSecondary,
    flex: 1,
    lineHeight: 17,
  },
  feeBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: 12,
    borderWidth: 1,
  },
  feeBannerText: {
    fontSize: 12,
    fontWeight: '600',
    flex: 1,
    lineHeight: 17,
  },
});

// ─────────────────────────────────────────────
// SUMMARY ROW
// ─────────────────────────────────────────────
const SummaryRow = ({ icon, label, value, accent, numberOfLines = 0 }) => (
  <View style={summaryRowStyles.row}>
    <View style={summaryRowStyles.iconWrap}>
      <Ionicons name={icon} size={14} color={accent ? COLORS.primary : COLORS.textMuted} />
    </View>
    <Text style={summaryRowStyles.label}>{label}</Text>
    <Text
      style={[summaryRowStyles.value, accent && { color: COLORS.primary, fontWeight: '700' }]}
      numberOfLines={numberOfLines}
    >
      {value}
    </Text>
  </View>
);

const summaryRowStyles = StyleSheet.create({
  row: {
    flexDirection: 'row', alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1, borderBottomColor: COLORS.borderLight,
    gap: 10,
  },
  iconWrap: {
    width: 28, height: 28, borderRadius: 8,
    backgroundColor: COLORS.primarySoft,
    justifyContent: 'center', alignItems: 'center',
  },
  label: { fontSize: 12, fontWeight: '600', color: COLORS.textMuted, width: 60 },
  value: { flex: 1, fontSize: 13, fontWeight: '500', color: COLORS.textPrimary, lineHeight: 18 },
});

// ─────────────────────────────────────────────
// MAIN SCREEN
// ─────────────────────────────────────────────
export default function PickupRequestScreen() {
  // Get service params from navigation
  const params = useLocalSearchParams();
  const selectedServiceId = params.serviceId;
  const selectedServiceName = params.serviceName;
  const selectedServicePrice = params.servicePrice;
  const selectedServicePriceType = params.servicePriceType;

  const [promoId, setPromoId] = useState('');
  const [promoName, setPromoName] = useState('');
  const [promoPrice, setPromoPrice] = useState('');
  const [promoPriceUnit, setPromoPriceUnit] = useState('');
  const [promoCode, setPromoCode] = useState('');
  const [promoBanner, setPromoBanner] = useState('');

  // Load pending promo from AsyncStorage when screen is focused
  useFocusEffect(
    React.useCallback(() => {
      AsyncStorage.getItem('@washbox:pending_promo').then(data => {
        if (data) {
          const promo = JSON.parse(data);
          setPromoId(promo.promoId || '');
          setPromoName(promo.promoName || '');
          setPromoPrice(promo.promoPrice || '');
          setPromoPriceUnit(promo.promoPriceUnit || '');
          setPromoCode(promo.promoCode || '');
          setPromoBanner(promo.promoBanner || '');
          AsyncStorage.removeItem('@washbox:pending_promo');
        }
      });
    }, [])
  );

  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [currentStep, setCurrentStep] = useState(0);

  const [fadeAnim] = useState(new Animated.Value(0));
  const [slideAnim] = useState(new Animated.Value(30));

  // Service Availability
  const [serviceStatus, setServiceStatus] = useState(null);
  const [pickupEnabled, setPickupEnabled] = useState(true);
  const [deliveryEnabled, setDeliveryEnabled] = useState(true);
  const [requireProofPhoto, setRequireProofPhoto] = useState(false);

  // Map & Location
  const [showMapModal, setShowMapModal] = useState(false);
  const [isLoadingLocation, setIsLoadingLocation] = useState(false);
  const [mapRegion, setMapRegion] = useState(null);

  // Form
  const [customerBranch, setCustomerBranch] = useState(null);
  const [branchLoading, setBranchLoading] = useState(true);
  const [branchError, setBranchError] = useState(null);
  const [pickupAddress, setPickupAddress] = useState('');
  const [pickupCoordinates, setPickupCoordinates] = useState(null);
  const [pickupDate, setPickupDate] = useState(new Date());
  const [pickupTime, setPickupTime] = useState('09:00');
  const [phoneNumber, setPhoneNumber] = useState('');
  const [notes, setNotes] = useState('');
  const [proofPhoto, setProofPhoto] = useState(null);
  const [deliveryFeeInfo, setDeliveryFeeInfo] = useState(null);
  const [checkingFee, setCheckingFee] = useState(false);

  // Saved Addresses Integration
  const [savedAddresses, setSavedAddresses] = useState([]);
  const [showAddressModal, setShowAddressModal] = useState(false);
  const [showPSGCModal, setShowPSGCModal] = useState(false);
  const [selectedAddressId, setSelectedAddressId] = useState(null);
  const [success, setSuccess] = useState(null);

  const [showDatePicker, setShowDatePicker] = useState(false);
  const [showTimePicker, setShowTimePicker] = useState(false);

  const locationStore = useLocationStore();
  const scrollRef = useRef(null);

  const STEPS = ['Location', 'Details', 'Confirm'];

  // ─── Memoized values ───
  const formComplete = useMemo(() => {
    // Must have branch, address, and phone
    const basicComplete = !!(pickupAddress && customerBranch && phoneNumber && !branchLoading && !branchError);
    if (requireProofPhoto) {
      return basicComplete && !!proofPhoto;
    }
    return basicComplete;
  }, [pickupAddress, customerBranch, phoneNumber, requireProofPhoto, proofPhoto, branchLoading, branchError]);

  const timePickerValue = useMemo(() => {
    const [h, m] = (pickupTime || '09:00').split(':');
    const d = new Date();
    d.setHours(parseInt(h) || 9, parseInt(m) || 0, 0);
    return d;
  }, [pickupTime]);

  useEffect(() => { fetchInitialData(); }, []);

  useEffect(() => {
    if (!loading) {
      Animated.parallel([
        Animated.timing(fadeAnim, { toValue: 1, duration: 500, useNativeDriver: true }),
        Animated.spring(slideAnim, { toValue: 0, useNativeDriver: true, tension: 60, friction: 12 }),
      ]).start();
    }
  }, [loading]);

  // Step indicator — driven by form completion, not auto-advance
  useEffect(() => {
    if (formComplete) {
      setCurrentStep(2);
    } else if (pickupAddress) {
      setCurrentStep(1);
    } else {
      setCurrentStep(0);
    }
  }, [pickupAddress, formComplete]);

  // ─── API ───

  const fetchInitialData = async () => {
    try {
      setLoading(true);
      await initializeLocation();
      await Promise.all([
        fetchServiceStatus(),
        fetchCustomerBranch(), 
        fetchCustomerData(), 
        fetchSavedAddresses()
      ]);
    } catch (error) {
      console.error('Error fetching initial data:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchServiceStatus = async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/v1/service-config`, {
        headers: { 'Accept': 'application/json' },
      });
      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data) {
          setServiceStatus(data.data);
          setPickupEnabled(data.data.pickup?.enabled ?? true);
          setDeliveryEnabled(data.data.delivery?.enabled ?? true);
          setRequireProofPhoto(data.data.pickup?.require_proof_photo ?? false);
        }
      }
    } catch (error) {
      console.error('Error fetching service status:', error);
      // Default to enabled on error
      setPickupEnabled(true);
      setDeliveryEnabled(true);
      setRequireProofPhoto(false);
    }
  };

  const initializeLocation = async () => {
    try {
      setIsLoadingLocation(true);
      const location = await LocationService.getCurrentLocation();
      setMapRegion({ ...location, latitudeDelta: 0.05, longitudeDelta: 0.05 });
      locationStore.setUserLocation(location);
    } catch {
      // Default to Dumaguete City
      setMapRegion({ latitude: 9.3068, longitude: 123.3054, latitudeDelta: 0.1, longitudeDelta: 0.1 });
    } finally {
      setIsLoadingLocation(false);
    }
  };

  const fetchCustomerBranch = async () => {
    try {
      setBranchLoading(true);
      setBranchError(null);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      const customerData = await AsyncStorage.getItem(STORAGE_KEYS.CUSTOMER);
      
      if (!token) {
        setBranchError('Not authenticated');
        setBranchLoading(false);
        return;
      }
      
      // First, try to get branch from stored customer data
      if (customerData) {
        try {
          const customer = JSON.parse(customerData);
          if (customer.branch) {
            setCustomerBranch(customer.branch);
            setBranchLoading(false);
            return; // Branch found in local storage, no need to fetch
          }
        } catch (e) {
          console.log('Could not parse customer data:', e);
        }
      }
      
      // If not in local storage, fetch from API
      const response = await fetch(`${API_BASE_URL}/v1/my-branch`, {
        headers: { 
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json' 
        },
      });
      
      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data) {
          setCustomerBranch(data.data);
        } else {
          setBranchError('No branch data received');
        }
      } else if (response.status === 404) {
        setBranchError('No branch assigned');
        Alert.alert(
          'No Branch Assigned',
          'You need to be registered with a branch to request pickups. Please contact support.',
          [{ text: 'OK' }]
        );
      } else {
        setBranchError('Failed to load branch');
      }
    } catch (error) {
      console.error('Error fetching customer branch:', error);
      setBranchError('Connection error');
    } finally {
      setBranchLoading(false);
    }
  };

  const fetchCustomerData = async () => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return;
      const response = await fetch(`${API_BASE_URL}/v1/user`, {
        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      });
      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data?.customer) {
          const customer = data.data.customer;
          // Update local storage with latest customer data including branch
          await AsyncStorage.setItem(STORAGE_KEYS.CUSTOMER, JSON.stringify(customer));
          
          if (customer.address) setPickupAddress(customer.address);
          if (customer.phone) setPhoneNumber(customer.phone);
          
          // Set branch if available
          if (customer.branch) {
            setCustomerBranch(customer.branch);
            setBranchLoading(false);
          }
        }
      }
    } catch (error) {
      console.error('Error fetching customer data:', error);
    }
  };

  const fetchSavedAddresses = async () => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return;
      
      const response = await fetch(`${API_BASE_URL}${ENDPOINTS.ADDRESSES}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });
      
      if (response.ok) {
        const data = await response.json();
        setSavedAddresses(data.data.addresses || []);
        
        // Auto-select default address if available and no address is set
        if (!pickupAddress && data.data.addresses?.length > 0) {
          const defaultAddress = data.data.addresses.find(addr => addr.is_default);
          if (defaultAddress) {
            handleSelectSavedAddress(defaultAddress);
          }
        }
      }
    } catch (error) {
      console.error('Error fetching saved addresses:', error);
    }
  };

  // ─── Location ───

  const handleLocationSelect = async (location) => {
    try {
      if (!location?.coordinate) return;

      let address;
      let coordinates = location.coordinate;

      if (location.geotag?.manuallyEdited) {
        // User typed address + pinned on map
        address = location.geotag.address;
        coordinates = { latitude: location.geotag.latitude, longitude: location.geotag.longitude };
      } else if (location.geotag?.address) {
        // Pinned on map (reverse geocoded)
        address = location.geotag.address;
        coordinates = { latitude: location.geotag.latitude, longitude: location.geotag.longitude };
      } else if (location.displayName || location.name) {
        // Search result from LocationSearch — use the name directly, no extra reverse geocode
        address = location.displayName || location.name;
      } else {
        address = await LocationService.getAddressFromCoordinate(location.coordinate);
      }

      setPickupAddress(address);
      setPickupCoordinates(coordinates);
      setShowMapModal(false);

      if (customerBranch) {
        await checkDeliveryFee(address, customerBranch.id);
      }
    } catch (error) {
      console.error('Error in handleLocationSelect:', error);
      Alert.alert('Error', 'Could not process location selection');
    }
  };

  const handleUseCurrentLocation = async () => {
    try {
      setIsLoadingLocation(true);

      // Request permission explicitly
      const perms = await LocationService.requestPermissions();

      if (perms.foreground !== 'granted') {
        Alert.alert(
          'Permission Required',
          'Location permission is needed. Please enable it in Settings → Apps → WashBox → Permissions → Location.',
          [
            { text: 'Open Settings', onPress: () => LocationService.openLocationSettings() },
            { text: 'Cancel', style: 'cancel' },
          ]
        );
        return;
      }

      const location = await LocationService.getCurrentLocation();

      if (location.isFallback || location.source === 'default') {
        Alert.alert(
          'Location Unavailable',
          'Could not detect your exact location. Make sure GPS is turned on and try again, or select your address manually.',
          [
            { text: 'Try Again', onPress: handleUseCurrentLocation },
            { text: 'Select Manually', onPress: () => { setShowAddressModal(false); setShowPSGCModal(true); } },
            { text: 'Cancel', style: 'cancel' },
          ]
        );
        return;
      }

      setPickupCoordinates(location);
      setSelectedAddressId(null);
      setShowAddressModal(false);
      setShowMapModal(false);

      const address = await LocationService.getAddressFromCoordinate(location);
      setPickupAddress(address);
      setShowPSGCModal(true);

    } catch (error) {
      console.error('Error getting current location:', error);
      Alert.alert(
        'Location Error',
        'Unable to get your location. Make sure GPS is enabled on your phone.',
        [
          { text: 'Try Again', onPress: handleUseCurrentLocation },
          { text: 'Select Manually', onPress: () => { setShowAddressModal(false); setShowPSGCModal(true); } },
          { text: 'Cancel', style: 'cancel' },
        ]
      );
    } finally {
      setIsLoadingLocation(false);
    }
  };

  const handleSelectSavedAddress = (address) => {
    setPickupAddress(address.full_address);
    setSelectedAddressId(address.id);
    
    // Set coordinates if available
    if (address.coordinates) {
      setPickupCoordinates({
        latitude: address.coordinates.lat,
        longitude: address.coordinates.lng,
      });
    }
    
    // Set contact info if available
    if (address.contact_phone && !phoneNumber) {
      setPhoneNumber(address.contact_phone);
    }
    
    // Set delivery notes if available
    if (address.delivery_notes && !notes) {
      setNotes(address.delivery_notes);
    }
    
    // Check delivery fee
    if (customerBranch) {
      checkDeliveryFee(address.full_address, customerBranch.id);
    }
    
    setShowAddressModal(false);
  };

  const handleManualAddressEntry = () => {
    setSelectedAddressId(null);
    setShowMapModal(true);
    setShowAddressModal(false);
  };

  // ─── Date/Time ───

  const handleDateChange = (event, selected) => {
    setShowDatePicker(false);
    if (selected) setPickupDate(selected);
  };

  const handleTimeChange = (event, selected) => {
    setShowTimePicker(false);
    if (selected) {
      const hours = selected.getHours().toString().padStart(2, '0');
      const minutes = selected.getMinutes().toString().padStart(2, '0');
      setPickupTime(`${hours}:${minutes}`);
    }
  };

  // ─── Photo Capture ───

  const checkDeliveryFee = async (address, branchId) => {
    try {
      setCheckingFee(true);
      const response = await fetch(`${API_BASE_URL}/v1/check-delivery-fee`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          address,
          branch_id: parseInt(branchId),
        }),
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          setDeliveryFeeInfo(data.data);
        }
      }
    } catch (error) {
      console.error('Error checking delivery fee:', error);
    } finally {
      setCheckingFee(false);
    }
  };

  // ─── Photo Capture ───

  const handleTakePhoto = async () => {
    try {
      const { status } = await ImagePicker.requestCameraPermissionsAsync();
      if (status !== 'granted') {
        Alert.alert('Permission Required', 'Camera permission is needed to take photos');
        return;
      }

      const result = await ImagePicker.launchCameraAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsEditing: true,
        aspect: [4, 3],
        quality: 0.8,
      });

      if (!result.canceled && result.assets[0]) {
        setProofPhoto(result.assets[0]);
      }
    } catch (error) {
      console.error('Error taking photo:', error);
      Alert.alert('Error', 'Failed to take photo');
    }
  };

  const handleRemovePhoto = () => {
    Alert.alert(
      'Remove Photo',
      'Are you sure you want to remove this photo?',
      [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Remove', style: 'destructive', onPress: () => setProofPhoto(null) },
      ]
    );
  };

  // ─── Validation ───

  const validateForm = () => {
    if (branchLoading) {
      Alert.alert('Please Wait', 'Loading branch information...');
      return false;
    }
    if (branchError || !customerBranch) {
      Alert.alert(
        'No Branch Assigned',
        'You need to be registered with a branch to request pickups. Please contact support to get assigned to a branch.',
        [{ text: 'OK' }]
      );
      return false;
    }
    if (!pickupAddress.trim()) {
      Alert.alert('Missing Info', 'Please set your pickup location');
      return false;
    }
    if (!phoneNumber.trim()) {
      Alert.alert('Missing Info', 'Please enter your phone number');
      return false;
    }
    // PH phone number validation
    const phoneRegex = /^(09|\+639)\d{9}$/;
    if (!phoneRegex.test(phoneNumber.replace(/[-\s]/g, ''))) {
      Alert.alert('Invalid Phone', 'Please enter a valid PH number (09XX-XXX-XXXX)');
      return false;
    }
    // Business hours: 7am – 8pm
    const [h] = pickupTime.split(':');
    const hour = parseInt(h);
    if (hour < 7 || hour >= 20) {
      Alert.alert('Invalid Time', 'Pickup is only available between 7:00 AM and 8:00 PM');
      return false;
    }
    // Proof photo validation
    if (requireProofPhoto && !proofPhoto) {
      Alert.alert('Missing Photo', 'Please upload a photo of your laundry items');
      return false;
    }
    return true;
  };

  // ─── Submit ───

  const handleSubmit = async () => {
    if (!validateForm()) return;
    try {
      setSubmitting(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) {
        Alert.alert('Error', 'Please login to continue');
        router.replace('/(auth)/login');
        return;
      }

      const formData = new FormData();
      formData.append('branch_id', customerBranch.id);
      formData.append('pickup_address', pickupAddress);
      formData.append('latitude', pickupCoordinates?.latitude || 0);
      formData.append('longitude', pickupCoordinates?.longitude || 0);
      formData.append('preferred_date', pickupDate.toISOString().split('T')[0]);
      formData.append('preferred_time', pickupTime);
      formData.append('phone_number', phoneNumber);
      if (notes) formData.append('notes', notes);
      
      // Add selected service if available
      if (selectedServiceId) {
        formData.append('service_id', selectedServiceId);
      }

      // Add promo if available
      if (promoId && String(promoId).trim() !== '') {
        formData.append('promotion_id', String(promoId));
        if (promoCode && String(promoCode).trim() !== '') {
          formData.append('promo_code', String(promoCode));
        }
      }

      if (proofPhoto) {
        const filename = proofPhoto.uri.split('/').pop();
        const match = /\.([\w]+)$/.exec(filename);
        const type = match ? `image/${match[1]}` : 'image/jpeg';
        formData.append('customer_proof_photo', {
          uri: proofPhoto.uri,
          name: filename,
          type,
        });
      }

      const response = await fetch(`${API_BASE_URL}/v1/pickups`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
        body: formData,
      });

      console.log('Pickup submission response status:', response.status);
      const data = await response.json();
      console.log('Pickup submission response data:', JSON.stringify(data, null, 2));

      if (response.ok && data.success) {
        // Clear form
        setPickupAddress('');
        setPickupCoordinates(null);
        setNotes('');
        setProofPhoto(null);
        setCurrentStep(0);

        const pickupId = data.data?.pickup?.id || data.data?.id;
        Alert.alert(
          '🎉 Pickup Scheduled!',
          "We'll confirm your request shortly and deliver your laundry back to the same address.",
          [{
            text: 'View Pickup',
            onPress: () => pickupId
              ? router.replace(`/pickups/${pickupId}`)
              : router.replace('/pickups'),
          }]
        );
      } else {
        console.error('Pickup submission failed:', data.message);
        Alert.alert('Error', data.message || 'Failed to submit pickup request');
      }
    } catch (error) {
      console.error('Error submitting pickup request:', error);
      Alert.alert('Error', 'Failed to submit request. Please try again.');
    } finally {
      setSubmitting(false);
    }
  };

  // ─── Helpers ───

  const formatPickupDate = () => pickupDate.toLocaleDateString('en-US', {
    weekday: 'short', month: 'short', day: 'numeric',
  });

  const formatPickupTime = () => {
    const [h, m] = pickupTime.split(':');
    const hour = parseInt(h);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour > 12 ? hour - 12 : hour === 0 ? 12 : hour;
    return `${displayHour}:${m} ${ampm}`;
  };

  // ─── Loading ───

  if (loading) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <View style={styles.loadingContainer}>
          <View style={styles.loadingIconWrap}>
            <Ionicons name="car-outline" size={32} color={COLORS.primary} />
          </View>
          <ActivityIndicator size="large" color={COLORS.primary} style={{ marginTop: 20 }} />
          <Text style={styles.loadingText}>Preparing your pickup...</Text>
        </View>
      </View>
    );
  }

  // ─── Service Disabled ───

  if (!pickupEnabled) {
    return (
      <View style={styles.container}>
        {/* Header */}
        <View style={styles.header}>
          <View style={styles.headerTop}>
            <Text style={styles.headerTitle}>Schedule Pickup</Text>
            <TouchableOpacity
              style={styles.historyButton}
              onPress={() => router.push('/pickups')}
            >
              <Ionicons name="time-outline" size={18} color={COLORS.primary} />
              <Text style={styles.historyText}>History</Text>
            </TouchableOpacity>
          </View>
        </View>

        <View style={styles.disabledContainer}>
          <View style={styles.disabledIconWrap}>
            <Ionicons name="close-circle" size={64} color={COLORS.danger} />
          </View>
          <Text style={styles.disabledTitle}>Pickup Service Unavailable</Text>
          <Text style={styles.disabledMessage}>
            Our pickup service is temporarily disabled. Please contact us directly or visit our branch to drop off your laundry.
          </Text>
          
          {serviceStatus?.contact_info && (
            <View style={styles.contactCard}>
              <View style={styles.contactRow}>
                <Ionicons name="call" size={20} color={COLORS.primary} />
                <Text style={styles.contactText}>{serviceStatus.contact_info.contact_number || 'Contact us'}</Text>
              </View>
              <View style={styles.contactRow}>
                <Ionicons name="business" size={20} color={COLORS.primary} />
                <Text style={styles.contactText}>{serviceStatus.contact_info.shop_name || 'WashBox'}</Text>
              </View>
            </View>
          )}

          <TouchableOpacity
            style={styles.refreshButton}
            onPress={fetchServiceStatus}
          >
            <LinearGradient
              colors={COLORS.gradientPrimary}
              style={styles.refreshGradient}
              start={{ x: 0, y: 0 }}
              end={{ x: 1, y: 0 }}
            >
              <Ionicons name="refresh" size={20} color="#FFF" />
              <Text style={styles.refreshText}>Check Again</Text>
            </LinearGradient>
          </TouchableOpacity>
        </View>
      </View>
    );
  }

  // ─── Render ───

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <View style={styles.headerTop}>
          <Text style={styles.headerTitle}>Schedule Pickup</Text>
          <TouchableOpacity
            style={styles.historyButton}
            onPress={() => router.push('/pickups')}
          >
            <Ionicons name="time-outline" size={18} color={COLORS.primary} />
            <Text style={styles.historyText}>History</Text>
          </TouchableOpacity>
        </View>
        <Text style={styles.headerSubtitle}>
          We&apos;ll pick up and deliver back to your address - free of charge
        </Text>
      </View>

      {/* Promo Package Banner */}
      {!!promoId && (
        <View style={styles.promoBannerWrap}>
          <LinearGradient
            colors={['#0EA5E9', '#3B82F6']}
            style={styles.promoBannerGradient}
            start={{ x: 0, y: 0 }} end={{ x: 1, y: 0 }}
          >
            {!!promoBanner && (
              <Image
                source={{ uri: decodeURIComponent(promoBanner) }}
                style={StyleSheet.absoluteFillObject}
                resizeMode="cover"
              />
            )}
            {!!promoBanner && (
              <View style={[StyleSheet.absoluteFillObject, { backgroundColor: 'rgba(0,0,0,0.5)' }]} />
            )}
            <View style={styles.promoBannerContent}>
              <View style={styles.promoBannerLeft}>
                <Text style={styles.promoBannerLabel}>🎉 PROMO APPLIED</Text>
                <Text style={styles.promoBannerName} numberOfLines={1}>{promoName}</Text>
                <Text style={styles.promoBannerPrice}>
                  ₱{promoPrice} <Text style={styles.promoBannerUnit}>{promoPriceUnit}</Text>
                </Text>
              </View>
              <TouchableOpacity
                onPress={() => {
                  setPromoId('');
                  setPromoName('');
                  setPromoPrice('');
                  setPromoPriceUnit('');
                  setPromoCode('');
                  setPromoBanner('');
                }}
                style={styles.promoBannerRemove}
              >
                <Ionicons name="close-circle" size={22} color="rgba(255,255,255,0.9)" />
              </TouchableOpacity>
            </View>
          </LinearGradient>
        </View>
      )}

      {/* Selected Service Banner */}
      {selectedServiceName && (
        <View style={styles.selectedServiceBanner}>
          <View style={styles.selectedServiceContent}>
            <View style={styles.selectedServiceIcon}>
              <Ionicons name="checkmark-circle" size={20} color={COLORS.success} />
            </View>
            <View style={styles.selectedServiceInfo}>
              <Text style={styles.selectedServiceLabel}>Selected Service</Text>
              <Text style={styles.selectedServiceName}>{selectedServiceName}</Text>
              {selectedServicePrice && (
                <Text style={styles.selectedServicePrice}>
                  ₱{parseFloat(selectedServicePrice).toFixed(2)} {selectedServicePriceType === 'per_kilo' ? '/ kg' : '/ load'}
                </Text>
              )}
            </View>
          </View>
          <TouchableOpacity
            style={styles.changeServiceButton}
            onPress={() => router.back()}
          >
            <Text style={styles.changeServiceText}>Change</Text>
          </TouchableOpacity>
        </View>
      )}

      {/* Steps */}
      <StepIndicator currentStep={currentStep} steps={STEPS} />

      <KeyboardAvoidingView
        style={{ flex: 1 }}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      >
        <ScrollView
          ref={scrollRef}
          style={styles.scrollView}
          showsVerticalScrollIndicator={false}
          contentContainerStyle={{ paddingBottom: 120 }}
          keyboardShouldPersistTaps="handled"
        >
          <Animated.View style={{ opacity: fadeAnim, transform: [{ translateY: slideAnim }] }}>

            {/* Service Status Banner */}
            {!deliveryEnabled && (
              <View style={styles.warningBanner}>
                <Ionicons name="warning" size={18} color={COLORS.warning} />
                <Text style={styles.warningText}>
                  Delivery service is currently unavailable. You&apos;ll need to pick up your laundry from the branch.
                </Text>
              </View>
            )}

            {/* ─── SECTION 1: Location ─── */}
            <View style={styles.sectionHeader}>
              <View style={styles.sectionBadge}>
                <Ionicons name="location" size={14} color={COLORS.primary} />
              </View>
              <Text style={styles.sectionTitle}>Your Location</Text>
              {pickupAddress && (
                <View style={styles.sectionCheck}>
                  <Ionicons name="checkmark-circle" size={16} color={COLORS.pickup} />
                </View>
              )}
            </View>

            <PickupCard
              pickupAddress={pickupAddress}
              pickupCoords={pickupCoordinates}
              onPickupPress={() => setShowAddressModal(true)}
              onAddressSelect={handleSelectSavedAddress}
              savedAddresses={savedAddresses}
              selectedAddressId={selectedAddressId}
              deliveryFeeInfo={deliveryFeeInfo}
            />

            {/* Current Location Quick Action - More Prominent */}
            {!pickupAddress && (
              <View style={styles.currentLocationBanner}>
                <View style={styles.bannerContent}>
                  <View style={[styles.bannerIcon, { backgroundColor: COLORS.pickup + '20' }]}>
                    <Ionicons name="locate" size={20} color={COLORS.pickup} />
                  </View>
                  <View style={styles.bannerText}>
                    <Text style={styles.bannerTitle}>Having trouble?</Text>
                    <Text style={styles.bannerSubtitle}>Use your current location</Text>
                  </View>
                </View>
                <TouchableOpacity
                  style={styles.bannerButton}
                  onPress={handleUseCurrentLocation}
                  disabled={isLoadingLocation}
                >
                  {isLoadingLocation ? (
                    <ActivityIndicator size="small" color="#FFF" />
                  ) : (
                    <>
                      <Ionicons name="locate" size={14} color="#FFF" />
                      <Text style={styles.bannerButtonText}>Use Now</Text>
                    </>
                  )}
                </TouchableOpacity>
              </View>
            )}

            {/* ─── SECTION 2: Schedule & Details ─── */}
            <View style={[styles.sectionHeader, { marginTop: 28 }]}>
              <View style={styles.sectionBadge}>
                <Ionicons name="calendar" size={14} color={COLORS.primary} />
              </View>
              <Text style={styles.sectionTitle}>Schedule & Details</Text>
              {customerBranch && phoneNumber && (
                <View style={styles.sectionCheck}>
                  <Ionicons name="checkmark-circle" size={16} color={COLORS.pickup} />
                </View>
              )}
            </View>

            <View style={styles.formCard}>
              {/* Branch - Read Only */}
              <View style={styles.fieldGroup}>
                <Text style={styles.fieldLabel}>Your Branch</Text>
                {branchLoading ? (
                  <View style={styles.branchReadOnly}>
                    <ActivityIndicator size="small" color={COLORS.primary} />
                    <Text style={styles.branchLoading}>Loading your branch...</Text>
                  </View>
                ) : branchError ? (
                  <View style={[styles.branchReadOnly, { borderColor: COLORS.danger + '40' }]}>
                    <View style={[styles.branchIconWrap, { backgroundColor: COLORS.danger + '20' }]}>
                      <Ionicons name="alert-circle" size={20} color={COLORS.danger} />
                    </View>
                    <View style={styles.branchInfo}>
                      <Text style={[styles.branchName, { color: COLORS.danger }]}>Branch Error</Text>
                      <Text style={styles.branchAddress}>{branchError}</Text>
                    </View>
                    <TouchableOpacity onPress={fetchCustomerBranch} style={styles.retryButton}>
                      <Ionicons name="refresh" size={16} color={COLORS.primary} />
                    </TouchableOpacity>
                  </View>
                ) : customerBranch ? (
                  <View style={styles.branchReadOnly}>
                    <View style={styles.branchIconWrap}>
                      <Ionicons name="business" size={20} color={COLORS.primary} />
                    </View>
                    <View style={styles.branchInfo}>
                      <Text style={styles.branchName}>{customerBranch.name}</Text>
                      {customerBranch.address && (
                        <Text style={styles.branchAddress} numberOfLines={1}>
                          {customerBranch.address}
                        </Text>
                      )}
                    </View>
                    <View style={styles.branchBadge}>
                      <Text style={styles.branchBadgeText}>Registered</Text>
                    </View>
                  </View>
                ) : (
                  <View style={[styles.branchReadOnly, { borderColor: COLORS.warning + '40' }]}>
                    <View style={[styles.branchIconWrap, { backgroundColor: COLORS.warning + '20' }]}>
                      <Ionicons name="warning" size={20} color={COLORS.warning} />
                    </View>
                    <Text style={styles.branchLoading}>No branch assigned</Text>
                  </View>
                )}
              </View>

              {/* Date & Time */}
              <View style={styles.dateTimeRow}>
                <TouchableOpacity
                  style={[styles.dateTimeButton, { flex: 1 }]}
                  onPress={() => setShowDatePicker(true)}
                >
                  <Ionicons name="calendar-outline" size={18} color={COLORS.primary} />
                  <View>
                    <Text style={styles.dateTimeLabel}>DATE</Text>
                    <Text style={styles.dateTimeValue}>{formatPickupDate()}</Text>
                  </View>
                </TouchableOpacity>

                <TouchableOpacity
                  style={[styles.dateTimeButton, { flex: 0.7 }]}
                  onPress={() => setShowTimePicker(true)}
                >
                  <Ionicons name="time-outline" size={18} color={COLORS.primary} />
                  <View>
                    <Text style={styles.dateTimeLabel}>TIME</Text>
                    <Text style={styles.dateTimeValue}>{formatPickupTime()}</Text>
                  </View>
                </TouchableOpacity>
              </View>

              {/* Business hours hint */}
              <View style={styles.hoursHint}>
                <Ionicons name="information-circle-outline" size={13} color={COLORS.textMuted} />
                <Text style={styles.hoursHintText}>Available 7:00 AM – 8:00 PM</Text>
              </View>

              {/* Phone */}
              <View style={[styles.fieldGroup, { marginTop: 8 }]}>
                <Text style={styles.fieldLabel}>Phone Number</Text>
                <View style={styles.inputWrapper}>
                  <Ionicons name="call-outline" size={18} color={COLORS.textMuted} style={styles.fieldIcon} />
                  <TextInput
                    style={styles.textField}
                    placeholder="09XX-XXX-XXXX"
                    placeholderTextColor={COLORS.textMuted}
                    value={phoneNumber}
                    onChangeText={setPhoneNumber}
                    keyboardType="phone-pad"
                    maxLength={13}
                  />
                </View>
              </View>

              {/* Notes */}
              <View style={styles.fieldGroup}>
                <Text style={styles.fieldLabel}>
                  Notes <Text style={styles.optionalTag}>Optional</Text>
                </Text>
                <View style={[styles.inputWrapper, { alignItems: 'flex-start' }]}>
                  <Ionicons
                    name="chatbox-ellipses-outline" size={18}
                    color={COLORS.textMuted}
                    style={[styles.fieldIcon, { marginTop: 14 }]}
                  />
                  <TextInput
                    style={[styles.textField, { minHeight: 72, textAlignVertical: 'top', paddingTop: 14 }]}
                    placeholder="Gate code, landmarks, special instructions..."
                    placeholderTextColor={COLORS.textMuted}
                    value={notes}
                    onChangeText={setNotes}
                    multiline
                    numberOfLines={3}
                  />
                </View>
              </View>

              {/* Proof Photo */}
              <View style={styles.fieldGroup}>
                <Text style={styles.fieldLabel}>
                  Proof Photo {requireProofPhoto ? <Text style={styles.requiredTag}>Required</Text> : <Text style={styles.optionalTag}>Optional</Text>}
                </Text>
                <Text style={styles.fieldHint}>Upload a photo of your laundry items</Text>
                
                {proofPhoto ? (
                  <View style={styles.photoPreview}>
                    <Image source={{ uri: proofPhoto.uri }} style={styles.photoImage} />
                    <View style={styles.photoOverlay}>
                      <TouchableOpacity style={styles.photoActionBtn} onPress={handleTakePhoto}>
                        <Ionicons name="camera" size={18} color="#FFF" />
                      </TouchableOpacity>
                      <TouchableOpacity style={[styles.photoActionBtn, styles.photoRemoveBtn]} onPress={handleRemovePhoto}>
                        <Ionicons name="trash" size={18} color="#FFF" />
                      </TouchableOpacity>
                    </View>
                  </View>
                ) : (
                  <TouchableOpacity style={styles.photoButtonSingle} onPress={handleTakePhoto}>
                    <View style={styles.photoButtonIcon}>
                      <Ionicons name="camera" size={32} color={COLORS.primary} />
                    </View>
                    <Text style={styles.photoButtonText}>Take Photo</Text>
                    <Text style={styles.photoButtonHint}>Camera only - for verification</Text>
                  </TouchableOpacity>
                )}
              </View>
            </View>

            {/* ─── SECTION 3: Summary ─── */}
            {formComplete && (
              <>
                <View style={[styles.sectionHeader, { marginTop: 28 }]}>
                  <View style={styles.sectionBadge}>
                    <Ionicons name="receipt" size={14} color={COLORS.primary} />
                  </View>
                  <Text style={styles.sectionTitle}>Summary</Text>
                </View>

                <View style={styles.summaryCard}>
                  <LinearGradient
                    colors={['#111640', '#171D45']}
                    style={styles.summaryGradient}
                    start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }}
                  >
                    <SummaryRow icon="business" label="Branch" value={branchLoading ? 'Loading...' : (customerBranch?.name || 'No branch')} />
                    <SummaryRow icon="location" label="Address" value={pickupAddress} numberOfLines={2} />
                    <SummaryRow icon="refresh" label="Delivery" value="Back to same address" />
                    <SummaryRow icon="calendar" label="Schedule" value={`${formatPickupDate()} at ${formatPickupTime()}`} />
                    <SummaryRow icon="call" label="Phone" value={phoneNumber} />
                    {notes ? <SummaryRow icon="chatbox-ellipses" label="Notes" value={notes} numberOfLines={2} /> : null}

                    <View style={styles.freeBanner}>
                      <LinearGradient
                        colors={COLORS.gradientPickup}
                        style={styles.freeBannerGradient}
                        start={{ x: 0, y: 0 }} end={{ x: 1, y: 0 }}
                      >
                        <Ionicons name="pricetag" size={16} color="#FFF" />
                        <Text style={styles.freeBannerText}>FREE Pickup & Delivery</Text>
                      </LinearGradient>
                    </View>
                  </LinearGradient>
                </View>
              </>
            )}

          </Animated.View>
        </ScrollView>
      </KeyboardAvoidingView>

      {/* ─── Bottom Submit Bar ─── */}
      <View style={styles.bottomBar}>
        <TouchableOpacity
          style={[styles.submitButton, (!formComplete || submitting || !pickupEnabled) && styles.submitDisabled]}
          onPress={handleSubmit}
          disabled={!formComplete || submitting || !pickupEnabled}
          activeOpacity={0.85}
        >
          <LinearGradient
            colors={formComplete && pickupEnabled ? COLORS.gradientPrimary : [COLORS.surfaceElevated, COLORS.surfaceElevated]}
            style={styles.submitGradient}
            start={{ x: 0, y: 0 }} end={{ x: 1, y: 0 }}
          >
            {submitting ? (
              <ActivityIndicator size="small" color="#FFF" />
            ) : (
              <>
                <Ionicons name="paper-plane" size={20} color={formComplete && pickupEnabled ? '#FFF' : COLORS.textMuted} />
                <Text style={[styles.submitText, (!formComplete || !pickupEnabled) && { color: COLORS.textMuted }]}>
                  {!pickupEnabled ? 'Service Unavailable' : 'Request Pickup'}
                </Text>
              </>
            )}
          </LinearGradient>
        </TouchableOpacity>
      </View>

      {/* ─── Map Modal ─── */}
      <Modal
        visible={showMapModal}
        animationType="slide"
        presentationStyle="pageSheet"
        onRequestClose={() => setShowMapModal(false)}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalHeader}>
            <TouchableOpacity onPress={() => setShowMapModal(false)} style={styles.modalCloseBtn}>
              <Ionicons name="close" size={22} color={COLORS.textPrimary} />
            </TouchableOpacity>
            <View style={styles.modalTitleWrap}>
              <View style={[styles.modalTitleDot, { backgroundColor: COLORS.pickup }]} />
              <Text style={styles.modalTitle}>Set Pickup Location</Text>
            </View>
            <TouchableOpacity
              style={styles.currentLocBtn}
              onPress={handleUseCurrentLocation}
              disabled={isLoadingLocation}
            >
              {isLoadingLocation ? (
                <ActivityIndicator size="small" color={COLORS.primary} />
              ) : (
                <>
                  <Ionicons name="locate" size={16} color={COLORS.primary} />
                  <Text style={styles.currentLocText}>Current</Text>
                </>
              )}
            </TouchableOpacity>
          </View>

          <View style={styles.modalSearchWrap}>
            <LocationSearch
              placeholder="Search your pickup address..."
              onLocationSelect={handleLocationSelect}
              currentLocationButton={false}
            />
            <View style={styles.tapHint}>
              <Ionicons name="hand-left-outline" size={14} color={COLORS.primary} />
              <Text style={styles.tapHintText}>Pin your location on the map, then fill in address details below</Text>
            </View>
          </View>

          {mapRegion && (
            <PickupDeliveryMap
              pickupLocation={pickupCoordinates}
              deliveryLocation={null}
              onLocationSelect={(marker) => {
                if (marker.coordinate) {
                  setPickupCoordinates(marker.coordinate);
                }
              }}
              style={styles.map}
            />
          )}

          {/* PSGC Address form shown after pinning */}
          {pickupCoordinates && (
            <ScrollView
              style={{ maxHeight: 320, backgroundColor: COLORS.background }}
              keyboardShouldPersistTaps="handled"
              showsVerticalScrollIndicator={false}
            >
              <View style={{ padding: 16 }}>
                <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 12 }}>
                  <Ionicons name="checkmark-circle" size={16} color={COLORS.pickup} />
                  <Text style={{ color: COLORS.pickup, fontWeight: '700', fontSize: 13 }}>
                    Location pinned! Now confirm your address:
                  </Text>
                </View>
                <PSGCAddressSelector
                  onChange={(addr) => {
                    if (addr.full_address) {
                      setPickupAddress(addr.full_address);
                    }
                  }}
                />
              </View>
            </ScrollView>
          )}

          <View style={styles.modalFooter}>
            <TouchableOpacity
              style={styles.confirmBtn}
              onPress={() => {
                if (!pickupCoordinates) {
                  Alert.alert('No Pin', 'Please tap on the map to pin your location first');
                  return;
                }
                if (!pickupAddress) {
                  Alert.alert('No Address', 'Please select your city and barangay after pinning');
                  return;
                }
                setShowMapModal(false);
                if (customerBranch) checkDeliveryFee(pickupAddress, customerBranch.id);
              }}
              activeOpacity={0.85}
            >
              <LinearGradient
                colors={COLORS.gradientPickup}
                style={styles.confirmGradient}
                start={{ x: 0, y: 0 }} end={{ x: 1, y: 0 }}
              >
                <Ionicons name="checkmark-circle" size={20} color="#FFF" />
                <Text style={styles.confirmText}>Confirm Pickup Location</Text>
              </LinearGradient>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>

      {/* ─── Address Selection Modal ─── */}
      <Modal
        visible={showAddressModal}
        animationType="slide"
        presentationStyle="pageSheet"
        onRequestClose={() => setShowAddressModal(false)}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalHeader}>
            <TouchableOpacity onPress={() => setShowAddressModal(false)} style={styles.modalCloseBtn}>
              <Ionicons name="close" size={22} color={COLORS.textPrimary} />
            </TouchableOpacity>
            <View style={styles.modalTitleWrap}>
              <View style={[styles.modalTitleDot, { backgroundColor: COLORS.pickup }]} />
              <Text style={styles.modalTitle}>Select Address</Text>
            </View>
            <TouchableOpacity
              style={styles.addAddressBtn}
              onPress={() => {
                setShowAddressModal(false);
                router.push('/saved-addresses');
              }}
            >
              <Ionicons name="add" size={16} color={COLORS.primary} />
              <Text style={styles.addAddressText}>Add New</Text>
            </TouchableOpacity>
          </View>

          <ScrollView style={styles.addressList} showsVerticalScrollIndicator={false}>
            {/* Saved Addresses */}
            {savedAddresses.map((address) => (
              <TouchableOpacity
                key={address.id}
                style={[
                  styles.addressItem,
                  selectedAddressId === address.id && styles.addressItemSelected
                ]}
                onPress={() => handleSelectSavedAddress(address)}
                activeOpacity={0.7}
              >
                <View style={[styles.addressIcon, { backgroundColor: address.icon === 'home-outline' ? COLORS.success + '20' : COLORS.primary + '20' }]}>
                  <Ionicons 
                    name={address.icon} 
                    size={20} 
                    color={address.icon === 'home-outline' ? COLORS.success : COLORS.primary} 
                  />
                </View>
                <View style={styles.addressInfo}>
                  <View style={styles.addressHeader}>
                    <Text style={styles.addressLabel}>{address.label}</Text>
                    {address.is_default && (
                      <View style={styles.defaultBadge}>
                        <Text style={styles.defaultText}>Default</Text>
                      </View>
                    )}
                  </View>
                  <Text style={styles.addressText} numberOfLines={2}>{address.full_address}</Text>
                  <Text style={styles.addressLocation}>{address.city}, {address.province}</Text>
                  {address.contact_person && (
                    <Text style={styles.addressContact}>
                      Contact: {address.contact_person}
                      {address.contact_phone && ` - ${address.contact_phone}`}
                    </Text>
                  )}
                </View>
                {selectedAddressId === address.id && (
                  <View style={styles.selectedIndicator}>
                    <Ionicons name="checkmark-circle" size={20} color={COLORS.pickup} />
                  </View>
                )}
              </TouchableOpacity>
            ))}

            {/* Manual Entry Options */}
            <View style={styles.manualSection}>
              <Text style={styles.manualSectionTitle}>Or choose manually:</Text>
              
              <TouchableOpacity
                style={styles.manualOption}
                onPress={handleUseCurrentLocation}
                disabled={isLoadingLocation}
                activeOpacity={0.7}
              >
                <View style={[styles.manualIcon, { backgroundColor: COLORS.pickup + '20' }]}>
                  {isLoadingLocation ? (
                    <ActivityIndicator size="small" color={COLORS.pickup} />
                  ) : (
                    <Ionicons name="locate" size={20} color={COLORS.pickup} />
                  )}
                </View>
                <View style={styles.manualInfo}>
                  <Text style={styles.manualTitle}>Use Current Location</Text>
                  <Text style={styles.manualSubtitle}>Automatically detect your location</Text>
                </View>
                <Ionicons name="chevron-forward" size={16} color={COLORS.textMuted} />
              </TouchableOpacity>

              <TouchableOpacity
                style={styles.manualOption}
                onPress={() => {
                  setShowAddressModal(false);
                  setShowPSGCModal(true);
                }}
                activeOpacity={0.7}
              >
                <View style={[styles.manualIcon, { backgroundColor: COLORS.success + '20' }]}>
                  <Ionicons name="list" size={20} color={COLORS.success} />
                </View>
                <View style={styles.manualInfo}>
                  <Text style={styles.manualTitle}>Select by City & Barangay</Text>
                  <Text style={styles.manualSubtitle}>Choose from Philippine address list</Text>
                </View>
                <Ionicons name="chevron-forward" size={16} color={COLORS.textMuted} />
              </TouchableOpacity>

              <TouchableOpacity
                style={styles.manualOption}
                onPress={handleManualAddressEntry}
                activeOpacity={0.7}
              >
                <View style={[styles.manualIcon, { backgroundColor: COLORS.primary + '20' }]}>
                  <Ionicons name="map" size={20} color={COLORS.primary} />
                </View>
                <View style={styles.manualInfo}>
                  <Text style={styles.manualTitle}>Choose on Map</Text>
                  <Text style={styles.manualSubtitle}>Pin your location on the map</Text>
                </View>
                <Ionicons name="chevron-forward" size={16} color={COLORS.textMuted} />
              </TouchableOpacity>
            </View>

            {savedAddresses.length === 0 && (
              <View style={styles.emptyAddresses}>
                <Ionicons name="location-outline" size={48} color={COLORS.textMuted} />
                <Text style={styles.emptyTitle}>No Saved Addresses</Text>
                <Text style={styles.emptyText}>Add addresses to make pickup requests faster</Text>
                <TouchableOpacity
                  style={styles.emptyButton}
                  onPress={() => {
                    setShowAddressModal(false);
                    router.push('/saved-addresses');
                  }}
                >
                  <LinearGradient
                    colors={COLORS.gradientPrimary}
                    style={styles.emptyButtonGradient}
                  >
                    <Ionicons name="add" size={16} color="#FFF" />
                    <Text style={styles.emptyButtonText}>Add Address</Text>
                  </LinearGradient>
                </TouchableOpacity>
              </View>
            )}
          </ScrollView>
        </View>
      </Modal>

      {/* ─── PSGC Address Modal ─── */}
      <Modal
        visible={showPSGCModal}
        animationType="slide"
        presentationStyle="pageSheet"
        onRequestClose={() => setShowPSGCModal(false)}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalHeader}>
            <TouchableOpacity onPress={() => setShowPSGCModal(false)} style={styles.modalCloseBtn}>
              <Ionicons name="close" size={22} color={COLORS.textPrimary} />
            </TouchableOpacity>
            <View style={styles.modalTitleWrap}>
              <View style={[styles.modalTitleDot, { backgroundColor: COLORS.success }]} />
              <Text style={styles.modalTitle}>Select Address</Text>
            </View>
            <View style={{ width: 38 }} />
          </View>

          <ScrollView
            style={{ flex: 1, padding: 20 }}
            keyboardShouldPersistTaps="handled"
            showsVerticalScrollIndicator={false}
          >
            <PSGCAddressSelector
              onChange={(addr) => {
                if (addr.full_address) {
                  setPickupAddress(addr.full_address);
                  setSelectedAddressId(null);
                  if (customerBranch) checkDeliveryFee(addr.full_address, customerBranch.id);
                }
              }}
            />

            <TouchableOpacity
              style={[styles.confirmBtn, { marginTop: 24, marginBottom: 40 }]}
              onPress={() => {
                if (pickupAddress) {
                  setShowPSGCModal(false);
                } else {
                  Alert.alert('Incomplete', 'Please select at least a city and barangay');
                }
              }}
              activeOpacity={0.85}
            >
              <LinearGradient
                colors={COLORS.gradientPickup}
                style={styles.confirmGradient}
                start={{ x: 0, y: 0 }} end={{ x: 1, y: 0 }}
              >
                <Ionicons name="checkmark-circle" size={20} color="#FFF" />
                <Text style={styles.confirmText}>Use This Address</Text>
              </LinearGradient>
            </TouchableOpacity>
          </ScrollView>
        </View>
      </Modal>

      {/* ─── Date Picker ─── */}
      {showDatePicker && (
        <DateTimePicker
          value={pickupDate}
          mode="date"
          display={Platform.OS === 'ios' ? 'spinner' : 'default'}
          onChange={handleDateChange}
          minimumDate={new Date()}
        />
      )}

      {/* Time Picker */}
      {showTimePicker && (
        <DateTimePicker
          value={timePickerValue}
          mode="time"
          display={Platform.OS === 'ios' ? 'spinner' : 'default'}
          onChange={handleTimeChange}
          minimumDate={new Date(new Date().setHours(7, 0, 0))}
          maximumDate={new Date(new Date().setHours(20, 0, 0))}
        />
      )}
    </View>
  );
}

// ─────────────────────────────────────────────
// MAIN STYLES
// ─────────────────────────────────────────────
const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: COLORS.background },
  centerContent: { justifyContent: 'center', alignItems: 'center' },

  loadingContainer: { alignItems: 'center' },
  loadingIconWrap: {
    width: 72, height: 72, borderRadius: 36,
    backgroundColor: COLORS.primaryGlow,
    justifyContent: 'center', alignItems: 'center',
  },
  loadingText: { color: COLORS.textSecondary, marginTop: 12, fontSize: 14, fontWeight: '500' },

  header: {
    paddingHorizontal: 24,
    paddingTop: Platform.OS === 'ios' ? 60 : 48,
    paddingBottom: 8,
  },
  headerTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 },
  headerTitle: { fontSize: 26, fontWeight: '800', color: COLORS.textPrimary, letterSpacing: -0.5 },
  headerSubtitle: { fontSize: 13, color: COLORS.textMuted, marginTop: 2 },
  historyButton: {
    flexDirection: 'row', alignItems: 'center', gap: 5,
    backgroundColor: COLORS.primarySoft,
    paddingHorizontal: 12, paddingVertical: 7, borderRadius: 10,
  },
  historyText: { fontSize: 13, fontWeight: '600', color: COLORS.primary },

  scrollView: { flex: 1 },

  sectionHeader: {
    flexDirection: 'row', alignItems: 'center',
    paddingHorizontal: 24, marginBottom: 12, gap: 8,
  },
  sectionBadge: {
    width: 28, height: 28, borderRadius: 8,
    backgroundColor: COLORS.primarySoft,
    justifyContent: 'center', alignItems: 'center',
  },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: COLORS.textPrimary, flex: 1 },
  sectionCheck: { marginLeft: 'auto' },

  currentLocationBanner: {
    marginHorizontal: 20,
    marginTop: 12,
    marginBottom: 20,
    backgroundColor: COLORS.pickupGlow,
    borderRadius: 16,
    padding: 14,
    borderWidth: 1,
    borderColor: COLORS.pickup + '40',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  bannerContent: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
    gap: 12,
  },
  bannerIcon: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  bannerText: {
    flex: 1,
  },
  bannerTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.pickup,
    marginBottom: 2,
  },
  bannerSubtitle: {
    fontSize: 11,
    color: COLORS.textSecondary,
  },
  bannerButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: COLORS.pickup,
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 10,
  },
  bannerButtonText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#FFF',
  },

  formCard: {
    backgroundColor: COLORS.surface,
    marginHorizontal: 20, borderRadius: 20, padding: 20,
    borderWidth: 1, borderColor: COLORS.borderLight,
  },
  fieldGroup: { marginBottom: 18 },
  fieldLabel: { fontSize: 12, fontWeight: '700', color: COLORS.textSecondary, marginBottom: 8, letterSpacing: 0.3 },
  optionalTag: { fontSize: 10, fontWeight: '500', color: COLORS.textMuted, fontStyle: 'italic' },
  requiredTag: { fontSize: 10, fontWeight: '700', color: COLORS.danger },
  fieldHint: { fontSize: 11, color: COLORS.textMuted, marginBottom: 10, marginTop: -4 },
  pickerWrapper: {
    backgroundColor: COLORS.surfaceElevated, borderRadius: 14,
    overflow: 'hidden', flexDirection: 'row', alignItems: 'center',
    minHeight: 52,
  },
  fieldIcon: { marginLeft: 14 },
  branchReadOnly: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surfaceElevated,
    borderRadius: 14,
    padding: 14,
    gap: 12,
    borderWidth: 1,
    borderColor: COLORS.primary + '20',
  },
  branchIconWrap: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.primarySoft,
    justifyContent: 'center',
    alignItems: 'center',
  },
  branchInfo: {
    flex: 1,
  },
  branchName: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 2,
  },
  branchAddress: {
    fontSize: 12,
    color: COLORS.textMuted,
  },
  branchBadge: {
    backgroundColor: COLORS.pickup + '20',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 8,
  },
  branchBadgeText: {
    fontSize: 10,
    fontWeight: '700',
    color: COLORS.pickup,
    textTransform: 'uppercase',
  },
  branchLoading: {
    fontSize: 13,
    color: COLORS.textMuted,
    marginLeft: 12,
  },
  retryButton: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: COLORS.primarySoft,
    justifyContent: 'center',
    alignItems: 'center',
  },
  inputWrapper: { flexDirection: 'row', alignItems: 'center', backgroundColor: COLORS.surfaceElevated, borderRadius: 14 },
  textField: { flex: 1, padding: 14, color: COLORS.textPrimary, fontSize: 14 },

  dateTimeRow: { flexDirection: 'row', gap: 12, marginBottom: 4 },
  dateTimeButton: {
    flexDirection: 'row', alignItems: 'center', gap: 10,
    backgroundColor: COLORS.surfaceElevated, borderRadius: 14, padding: 14,
  },
  dateTimeLabel: { fontSize: 10, fontWeight: '700', color: COLORS.textMuted, letterSpacing: 0.5 },
  dateTimeValue: { fontSize: 14, fontWeight: '600', color: COLORS.textPrimary, marginTop: 1 },

  hoursHint: {
    flexDirection: 'row', alignItems: 'center', gap: 5,
    marginBottom: 16, paddingLeft: 2,
  },
  hoursHintText: { fontSize: 11, color: COLORS.textMuted },

  summaryCard: {
    marginHorizontal: 20, borderRadius: 20,
    overflow: 'hidden', borderWidth: 1, borderColor: COLORS.borderLight,
  },
  summaryGradient: { padding: 20 },
  freeBanner: { marginTop: 16, borderRadius: 12, overflow: 'hidden' },
  freeBannerGradient: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
    gap: 8, paddingVertical: 12,
  },
  freeBannerText: { fontSize: 14, fontWeight: '800', color: '#FFF', letterSpacing: 0.3 },

  bottomBar: {
    position: 'absolute', bottom: 0, left: 0, right: 0,
    backgroundColor: COLORS.background,
    paddingHorizontal: 20, paddingTop: 12,
    paddingBottom: Platform.OS === 'ios' ? 34 : 20,
    borderTopWidth: 1, borderTopColor: COLORS.borderLight,
  },
  submitButton: {},
  submitDisabled: { opacity: 0.7 },
  submitGradient: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
    gap: 10, paddingVertical: 16, borderRadius: 16,
  },
  submitText: { fontSize: 16, fontWeight: '800', color: '#FFF', letterSpacing: 0.3 },

  modalContainer: { flex: 1, backgroundColor: COLORS.background },
  modalHeader: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingTop: Platform.OS === 'ios' ? 56 : 44,
    paddingBottom: 14,
    borderBottomWidth: 1, borderBottomColor: COLORS.borderLight,
  },
  modalCloseBtn: {
    width: 38, height: 38, borderRadius: 12,
    backgroundColor: COLORS.surfaceElevated,
    justifyContent: 'center', alignItems: 'center',
  },
  modalTitleWrap: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  modalTitleDot: { width: 10, height: 10, borderRadius: 5 },
  modalTitle: { fontSize: 17, fontWeight: '700', color: COLORS.textPrimary },
  currentLocBtn: {
    flexDirection: 'row', alignItems: 'center', gap: 5,
    backgroundColor: COLORS.primarySoft,
    paddingHorizontal: 12, paddingVertical: 8, borderRadius: 10,
  },
  currentLocText: { color: COLORS.primary, fontSize: 13, fontWeight: '600' },
  addAddressBtn: {
    flexDirection: 'row', alignItems: 'center', gap: 5,
    backgroundColor: COLORS.primarySoft,
    paddingHorizontal: 12, paddingVertical: 8, borderRadius: 10,
  },
  addAddressText: { color: COLORS.primary, fontSize: 13, fontWeight: '600' },
  
  // Address Selection Modal
  addressList: {
    flex: 1,
    padding: 20,
  },
  addressItem: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    padding: 16,
    marginBottom: 12,
    borderWidth: 2,
    borderColor: 'transparent',
  },
  addressItemSelected: {
    borderColor: COLORS.pickup + '40',
    backgroundColor: COLORS.pickup + '08',
  },
  addressIcon: {
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  addressInfo: {
    flex: 1,
  },
  addressHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 4,
  },
  addressLabel: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.textPrimary,
  },
  defaultBadge: {
    backgroundColor: COLORS.success + '20',
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 6,
    marginLeft: 8,
  },
  defaultText: {
    fontSize: 9,
    fontWeight: '600',
    color: COLORS.success,
    textTransform: 'uppercase',
  },
  addressText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
    marginBottom: 4,
  },
  addressLocation: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginBottom: 4,
  },
  addressContact: {
    fontSize: 11,
    color: COLORS.textMuted,
  },
  selectedIndicator: {
    marginLeft: 8,
    marginTop: 2,
  },
  
  // Manual Options
  manualSection: {
    marginTop: 20,
    paddingTop: 20,
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
  },
  manualSectionTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.textSecondary,
    marginBottom: 12,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  manualOption: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 14,
    marginBottom: 8,
  },
  manualIcon: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  manualInfo: {
    flex: 1,
  },
  manualTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginBottom: 2,
  },
  manualSubtitle: {
    fontSize: 12,
    color: COLORS.textSecondary,
  },
  
  // Empty State
  emptyAddresses: {
    alignItems: 'center',
    paddingVertical: 40,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginTop: 16,
    marginBottom: 8,
  },
  emptyText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    marginBottom: 24,
  },
  emptyButton: {
    borderRadius: 12,
    overflow: 'hidden',
  },
  emptyButtonGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 12,
    gap: 8,
  },
  emptyButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#FFF',
  },
  modalSearchWrap: { paddingHorizontal: 20, paddingVertical: 14, backgroundColor: COLORS.background },
  tapHint: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginTop: 8,
    paddingHorizontal: 12,
    paddingVertical: 8,
    backgroundColor: COLORS.primarySoft,
    borderRadius: 10,
  },
  tapHintText: {
    fontSize: 12,
    fontWeight: '500',
    color: COLORS.primary,
    flex: 1,
  },
  map: { flex: 1 },
  modalFooter: {
    padding: 20,
    paddingBottom: Platform.OS === 'ios' ? 34 : 20,
    backgroundColor: COLORS.background,
    borderTopWidth: 1, borderTopColor: COLORS.borderLight,
  },
  confirmBtn: { borderRadius: 16, overflow: 'hidden' },
  confirmGradient: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
    gap: 8, paddingVertical: 16,
  },
  confirmText: { color: '#FFF', fontSize: 16, fontWeight: '700' },

  // Service Disabled State
  disabledContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 32,
  },
  disabledIconWrap: {
    marginBottom: 24,
  },
  disabledTitle: {
    fontSize: 22,
    fontWeight: '800',
    color: COLORS.textPrimary,
    marginBottom: 12,
    textAlign: 'center',
  },
  disabledMessage: {
    fontSize: 15,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 32,
  },
  contactCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    padding: 20,
    width: '100%',
    marginBottom: 24,
    borderWidth: 1,
    borderColor: COLORS.borderLight,
    gap: 16,
  },
  contactRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  contactText: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.textPrimary,
  },
  refreshButton: {
    borderRadius: 14,
    overflow: 'hidden',
    width: '100%',
  },
  refreshGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    paddingVertical: 14,
  },
  refreshText: {
    fontSize: 15,
    fontWeight: '700',
    color: '#FFF',
  },

  // Warning Banner
  warningBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    backgroundColor: COLORS.warning + '15',
    borderWidth: 1,
    borderColor: COLORS.warning + '40',
    borderRadius: 14,
    padding: 16,
    marginHorizontal: 20,
    marginBottom: 20,
  },
  warningText: {
    flex: 1,
    fontSize: 13,
    fontWeight: '500',
    color: COLORS.textSecondary,
    lineHeight: 18,
  },

  // Proof Photo
  photoButtonSingle: {
    backgroundColor: COLORS.surfaceElevated,
    borderRadius: 14,
    padding: 20,
    alignItems: 'center',
    gap: 8,
    borderWidth: 1,
    borderColor: COLORS.borderLight,
  },
  photoButtonIcon: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: COLORS.primarySoft,
    justifyContent: 'center',
    alignItems: 'center',
  },
  photoButtonText: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.textPrimary,
  },
  photoButtonHint: {
    fontSize: 11,
    color: COLORS.textMuted,
    fontStyle: 'italic',
  },
  photoPreview: {
    position: 'relative',
    borderRadius: 14,
    overflow: 'hidden',
    backgroundColor: COLORS.surfaceElevated,
  },
  photoImage: {
    width: '100%',
    height: 200,
    resizeMode: 'cover',
  },
  photoOverlay: {
    position: 'absolute',
    bottom: 12,
    right: 12,
    flexDirection: 'row',
    gap: 8,
  },
  photoActionBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(0, 0, 0, 0.7)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  photoRemoveBtn: {
    backgroundColor: COLORS.danger + 'CC',
  },

  // Promo Banner
  promoBannerWrap: {
    marginHorizontal: 20,
    marginBottom: 12,
    borderRadius: 16,
    overflow: 'hidden',
    elevation: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
  },
  promoBannerImageWrap: {
    height: 110,
    position: 'relative',
  },
  promoBannerImage: {
    width: '100%',
    height: '100%',
  },
  promoBannerOverlay: {
    position: 'absolute',
    top: 0, left: 0, right: 0, bottom: 0,
    backgroundColor: 'rgba(0,0,0,0.55)',
    justifyContent: 'center',
    padding: 16,
  },
  promoBannerGradient: {
    padding: 16,
    minHeight: 90,
    justifyContent: 'center',
  },
  promoBannerContent: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  promoBannerLeft: { flex: 1 },
  promoBannerLabel: {
    fontSize: 10, fontWeight: '800',
    color: 'rgba(255,255,255,0.7)',
    letterSpacing: 1.5, marginBottom: 2,
  },
  promoBannerName: {
    fontSize: 15, fontWeight: '700',
    color: '#FFF', marginBottom: 2,
  },
  promoBannerPrice: {
    fontSize: 20, fontWeight: '900', color: '#FFF',
  },
  promoBannerUnit: {
    fontSize: 12, fontWeight: '500',
    color: 'rgba(255,255,255,0.8)',
  },
  promoBannerRemove: {
    padding: 4,
  },

  // Selected Service Banner
  selectedServiceBanner: {
    marginHorizontal: 20,
    marginBottom: 16,
    backgroundColor: COLORS.success + '15',
    borderRadius: 16,
    padding: 16,
    borderWidth: 1,
    borderColor: COLORS.success + '40',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  selectedServiceContent: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
    gap: 12,
  },
  selectedServiceIcon: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.success + '20',
    justifyContent: 'center',
    alignItems: 'center',
  },
  selectedServiceInfo: {
    flex: 1,
  },
  selectedServiceLabel: {
    fontSize: 10,
    fontWeight: '700',
    color: COLORS.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 2,
  },
  selectedServiceName: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 2,
  },
  selectedServicePrice: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.success,
  },
  changeServiceButton: {
    backgroundColor: COLORS.surfaceElevated,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 8,
  },
  changeServiceText: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.primary,
  },
});
