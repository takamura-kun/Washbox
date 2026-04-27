import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  Alert,
  Linking,
  Animated,
  Platform,
  Dimensions,
  Modal,
  Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router, useLocalSearchParams } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_BASE_URL, STORAGE_KEYS } from '../../constants/config';
import { LinearGradient } from 'expo-linear-gradient';
import * as ImagePicker from 'expo-image-picker';
import * as FileSystem from 'expo-file-system';

import RatingModal from '../../components/RatingModal';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

const COLORS = {
  background: '#06081A',
  surface: '#0F1332',
  surfaceLight: '#171D45',
  surfaceElevated: '#1E2654',
  primary: '#0EA5E9',
  primaryDark: '#0284C7',
  primaryGlow: 'rgba(14, 165, 233, 0.12)',
  primarySoft: 'rgba(14, 165, 233, 0.08)',
  secondary: '#8B5CF6',
  secondaryGlow: 'rgba(139, 92, 246, 0.12)',
  accent: '#F59E0B',
  accentGlow: 'rgba(245, 158, 11, 0.12)',
  success: '#10B981',
  successGlow: 'rgba(16, 185, 129, 0.12)',
  danger: '#EF4444',
  dangerGlow: 'rgba(239, 68, 68, 0.12)',
  textPrimary: '#F1F5F9',
  textSecondary: '#94A3B8',
  textMuted: '#64748B',
  border: '#1E293B',
  borderLight: 'rgba(255, 255, 255, 0.06)',
  gradientPrimary: ['#0EA5E9', '#3B82F6'],
  gradientSecondary: ['#8B5CF6', '#7C3AED'],
  gradientSuccess: ['#10B981', '#059669'],
  gradientWarning: ['#F59E0B', '#D97706'],
  gradientDanger: ['#EF4444', '#DC2626'],
};

const STATUS_MAP = {
  received:         { gradient: ['#3B82F6', '#2563EB'], color: '#3B82F6', icon: 'receipt-outline',              label: 'Received',            description: 'Your laundry has been received and is being prepared' },
  processing:       { gradient: ['#8B5CF6', '#7C3AED'], color: '#8B5CF6', icon: 'sync-outline',                label: 'Processing',          description: 'Your laundry is being washed and cared for' },
  washing:          { gradient: ['#06B6D4', '#0891B2'], color: '#06B6D4', icon: 'water-outline',               label: 'Washing',             description: 'Your clothes are in the washing machine' },
  drying:           { gradient: ['#F59E0B', '#D97706'], color: '#F59E0B', icon: 'sunny-outline',               label: 'Drying',              description: 'Your clothes are being dried' },
  ironing:          { gradient: ['#EC4899', '#DB2777'], color: '#EC4899', icon: 'shirt-outline',               label: 'Ironing',             description: 'Your clothes are being pressed and ironed' },
  folding:          { gradient: ['#F97316', '#EA580C'], color: '#F97316', icon: 'layers-outline',              label: 'Folding',             description: 'Your clothes are being neatly folded' },
  ready:            { gradient: ['#F59E0B', '#D97706'], color: '#F59E0B', icon: 'bag-check-outline',           label: 'Ready for Pickup',    description: 'Your laundry is clean and ready to collect!' },
  ready_for_pickup: { gradient: ['#F59E0B', '#D97706'], color: '#F59E0B', icon: 'bag-check-outline',           label: 'Ready for Pickup',    description: 'Your laundry is clean and ready to collect!' },
  out_for_delivery: { gradient: ['#0EA5E9', '#0284C7'], color: '#0EA5E9', icon: 'bicycle-outline',             label: 'Out for Delivery',    description: 'Your laundry is on its way to you!' },
  delivered:        { gradient: ['#10B981', '#059669'], color: '#10B981', icon: 'home-outline',                label: 'Delivered',           description: 'Your laundry has been delivered!' },
  paid:             { gradient: ['#10B981', '#059669'], color: '#10B981', icon: 'card-outline',                label: 'Paid',                description: 'Payment confirmed — thank you!' },
  completed:        { gradient: ['#10B981', '#059669'], color: '#10B981', icon: 'checkmark-done-circle-outline',label: 'Completed',           description: 'Your laundry has been completed successfully' },
  cancelled:        { gradient: ['#EF4444', '#DC2626'], color: '#EF4444', icon: 'close-circle-outline',        label: 'Cancelled',           description: 'This laundry order has been cancelled' },
};

const getStatusConfig = (status, isDelivery = false) => {
  const cfg = STATUS_MAP[status?.toLowerCase()] || STATUS_MAP.received;
  // Override label/description for ready status based on flow type
  if (status?.toLowerCase() === 'ready' && isDelivery) {
    return { ...cfg, label: 'Ready for Delivery', description: 'Your laundry is ready and will be delivered soon!' };
  }
  return cfg;
};

// Walk-in timeline stages
const WALKIN_STAGES = [
  { key: 'received',   label: 'Received',   icon: 'receipt-outline' },
  { key: 'processing', label: 'Processing', icon: 'sync-outline' },
  { key: 'ready',      label: 'Ready',      icon: 'bag-check-outline' },
  { key: 'paid',       label: 'Paid',       icon: 'card-outline' },
  { key: 'completed',  label: 'Done',       icon: 'checkmark-done-circle-outline' },
];

// Delivery timeline stages
const DELIVERY_STAGES = [
  { key: 'received',         label: 'Received',     icon: 'receipt-outline' },
  { key: 'processing',       label: 'Processing',   icon: 'sync-outline' },
  { key: 'ready',            label: 'Ready',        icon: 'bag-check-outline' },
  { key: 'out_for_delivery', label: 'On the Way',   icon: 'bicycle-outline' },
  { key: 'delivered',        label: 'Delivered',    icon: 'home-outline' },
  { key: 'completed',        label: 'Done',         icon: 'checkmark-done-circle-outline' },
];

const WALKIN_ORDER    = ['received','processing','washing','drying','ironing','folding','ready','ready_for_pickup','paid','completed'];
const DELIVERY_ORDER  = ['received','processing','washing','drying','ironing','folding','ready','ready_for_pickup','out_for_delivery','delivered','completed'];

const getStageIndex = (status, isDelivery) => {
  const s = status?.toLowerCase();
  if (isDelivery) {
    const idx = DELIVERY_ORDER.indexOf(s);
    if (idx <= 1) return idx;        // received=0, processing=1
    if (idx <= 5) return 1;          // washing/drying/ironing/folding → still Processing
    if (idx <= 7) return 2;          // ready/ready_for_pickup
    if (idx === 8) return 3;         // out_for_delivery
    if (idx === 9) return 4;         // delivered
    if (idx === 10) return 5;        // completed
    return 0;
  }
  const idx = WALKIN_ORDER.indexOf(s);
  if (idx <= 1) return idx;
  if (idx <= 5) return 1;
  if (idx <= 7) return 2;
  if (idx === 8) return 3;
  if (idx === 9) return 4;
  return 0;
};

const HorizontalTimeline = ({ currentStatus, isDelivery }) => {
  const stages     = isDelivery ? DELIVERY_STAGES : WALKIN_STAGES;
  const activeIdx  = getStageIndex(currentStatus, isDelivery);
  const isCancelled = currentStatus?.toLowerCase() === 'cancelled';

  return (
    <View style={tlStyles.container}>
      {stages.map((stage, index) => {
        const isCompleted = !isCancelled && index < activeIdx;
        const isCurrent   = !isCancelled && index === activeIdx;
        const isLast      = index === stages.length - 1;

        return (
          <View key={stage.key} style={tlStyles.stepWrapper}>
            <View style={[
              tlStyles.dot,
              isCompleted && tlStyles.dotCompleted,
              isCurrent   && tlStyles.dotCurrent,
              isCancelled && tlStyles.dotCancelled,
            ]}>
              {isCompleted
                ? <Ionicons name="checkmark" size={14} color="#FFF" />
                : <Ionicons name={stage.icon} size={14} color={isCurrent ? '#FFF' : COLORS.textMuted} />
              }
              {isCurrent && <View style={tlStyles.dotPulse} />}
            </View>
            <Text style={[
              tlStyles.label,
              isCompleted && tlStyles.labelCompleted,
              isCurrent   && tlStyles.labelCurrent,
            ]} numberOfLines={1}>{stage.label}</Text>
            {!isLast && (
              <View style={[tlStyles.connector, isCompleted && tlStyles.connectorCompleted]} />
            )}
          </View>
        );
      })}
    </View>
  );
};

const tlStyles = StyleSheet.create({
  container:          { flexDirection: 'row', alignItems: 'flex-start', justifyContent: 'center', paddingHorizontal: 8, paddingVertical: 4 },
  stepWrapper:        { alignItems: 'center', flexDirection: 'column', position: 'relative', flex: 1 },
  dot:                { width: 32, height: 32, borderRadius: 16, backgroundColor: COLORS.surfaceElevated, borderWidth: 2, borderColor: COLORS.border, justifyContent: 'center', alignItems: 'center', zIndex: 2 },
  dotCompleted:       { backgroundColor: COLORS.success, borderColor: COLORS.success },
  dotCurrent:         { backgroundColor: COLORS.primary, borderColor: COLORS.primary },
  dotCancelled:       { backgroundColor: COLORS.dangerGlow, borderColor: COLORS.danger },
  dotPulse:           { position: 'absolute', width: 44, height: 44, borderRadius: 22, backgroundColor: 'rgba(14,165,233,0.18)', zIndex: -1 },
  label:              { fontSize: 9, fontWeight: '700', color: COLORS.textMuted, marginTop: 6, textAlign: 'center', letterSpacing: 0.2 },
  labelCompleted:     { color: COLORS.success },
  labelCurrent:       { color: COLORS.primary },
  connector:          { position: 'absolute', top: 15, left: '60%', right: '-40%', height: 2, backgroundColor: COLORS.border, zIndex: 1 },
  connectorCompleted: { backgroundColor: COLORS.success },
});

const DetailRow = ({ icon, label, value, color, mono }) => (
  <View style={drStyles.row}>
    <View style={[drStyles.iconWrap, { backgroundColor: (color || COLORS.primary) + '14' }]}>
      <Ionicons name={icon} size={16} color={color || COLORS.primary} />
    </View>
    <Text style={drStyles.label}>{label}</Text>
    <Text style={[drStyles.value, mono && drStyles.mono]} numberOfLines={1}>{value || '—'}</Text>
  </View>
);

const drStyles = StyleSheet.create({
  row:      { flexDirection: 'row', alignItems: 'center', paddingVertical: 12, gap: 12, borderBottomWidth: 1, borderBottomColor: COLORS.borderLight },
  iconWrap: { width: 32, height: 32, borderRadius: 10, justifyContent: 'center', alignItems: 'center' },
  label:    { fontSize: 13, fontWeight: '500', color: COLORS.textMuted, width: 80 },
  value:    { flex: 1, fontSize: 14, fontWeight: '600', color: COLORS.textPrimary, textAlign: 'right' },
  mono:     { fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace', letterSpacing: 0.5 },
});

const PricingRow = ({ label, sublabel, value, icon, discount, bold, large }) => (
  <View style={[prStyles.row, large && prStyles.rowLarge]}>
    <View style={prStyles.labelSide}>
      {icon && <Ionicons name={icon} size={14} color={discount ? COLORS.success : COLORS.textMuted} style={{ marginRight: 6 }} />}
      <View>
        <Text style={[prStyles.label, bold && prStyles.labelBold, large && prStyles.labelLarge]}>{label}</Text>
        {sublabel && <Text style={prStyles.sublabel}>{sublabel}</Text>}
      </View>
    </View>
    <Text style={[prStyles.value, discount && prStyles.valueDiscount, bold && prStyles.valueBold, large && prStyles.valueLarge]}>
      {discount ? '-' : ''}{value}
    </Text>
  </View>
);

const prStyles = StyleSheet.create({
  row:           { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 8 },
  rowLarge:      { paddingVertical: 12 },
  labelSide:     { flexDirection: 'row', alignItems: 'center', flex: 1 },
  label:         { fontSize: 13, color: COLORS.textSecondary },
  labelBold:     { fontWeight: '700', color: COLORS.textPrimary },
  labelLarge:    { fontSize: 16 },
  sublabel:      { fontSize: 11, color: COLORS.textMuted, marginTop: 1 },
  value:         { fontSize: 14, fontWeight: '600', color: COLORS.textPrimary },
  valueDiscount: { color: COLORS.success },
  valueBold:     { fontWeight: '800' },
  valueLarge:    { fontSize: 22, color: COLORS.primary, letterSpacing: -0.3 },
});

export default function LaundryDetailsScreen() {
  const { id } = useLocalSearchParams();
  const [loading, setLoading]     = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [laundry, setLaundry]     = useState(null);
  const [fadeAnim]  = useState(new Animated.Value(0));
  const [slideAnim] = useState(new Animated.Value(30));
  const [paymentModalVisible, setPaymentModalVisible] = useState(false);
  const [paymentMethodModalVisible, setPaymentMethodModalVisible] = useState(false);
  const [gcashQRData, setGcashQRData] = useState(null);
  const [uploadingProof, setUploadingProof] = useState(false);
  const [paymentProof, setPaymentProof] = useState(null);
  const [loadingQR, setLoadingQR] = useState(false);
  const [selectedImage, setSelectedImage] = useState(null);
  const [confirmationModalVisible, setConfirmationModalVisible] = useState(false);
  const [showRatingModal, setShowRatingModal] = useState(false);
  const [hasShownRating, setHasShownRating] = useState(false);

  useEffect(() => { 
    fetchLaundryDetails();
    fetchPaymentProof();
  }, [id]);

  // Show rating modal when laundry is completed
  useEffect(() => {
    if (laundry && !hasShownRating) {
      const isCompleted = laundry.status?.toLowerCase() === 'completed';
      const hasRating = laundry.rating && laundry.rating > 0;
      
      // Show rating modal if completed and not yet rated
      if (isCompleted && !hasRating) {
        // Delay to allow the screen to fully load
        const timer = setTimeout(() => {
          setShowRatingModal(true);
          setHasShownRating(true);
        }, 1000);
        
        return () => clearTimeout(timer);
      }
    }
  }, [laundry, hasShownRating]);

  useEffect(() => {
    if (laundry) {
      Animated.parallel([
        Animated.timing(fadeAnim,  { toValue: 1, duration: 500, useNativeDriver: true }),
        Animated.spring(slideAnim, { toValue: 0, useNativeDriver: true, tension: 60, friction: 12 }),
      ]).start();
    }
  }, [laundry]);

  const fetchLaundryDetails = async () => {
    try {
      setLoading(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) { router.replace('/(auth)/login'); return; }

      const response = await fetch(`${API_BASE_URL}/v1/laundries/${id}`, {
        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data) {
          setLaundry(data.data.laundry || data.data);
        }
      } else if (response.status === 401) {
        await AsyncStorage.multiRemove([STORAGE_KEYS.TOKEN, STORAGE_KEYS.CUSTOMER]);
        router.replace('/(auth)/login');
      } else {
        Alert.alert('Error', 'Laundry not found');
        router.back();
      }
    } catch (error) {
      console.error('Error fetching laundry details:', error);
      Alert.alert('Error', 'Failed to load laundry details');
    } finally {
      setLoading(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchLaundryDetails();
    await fetchPaymentProof();
    setRefreshing(false);
  };

  const fetchPaymentProof = async () => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return;

      const response = await fetch(`${API_BASE_URL}/v1/customer/laundries/${id}/payment-proof`, {
        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          setPaymentProof(data.data);
        }
      }
    } catch (error) {
      console.error('Error fetching payment proof:', error);
    }
  };

  const fetchGCashQR = async (branchId) => {
    try {
      setLoadingQR(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return;

      console.log('Fetching GCash QR for branch:', branchId);
      const response = await fetch(`${API_BASE_URL}/v1/customer/gcash/qr/${branchId}`, {
        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      });

      console.log('GCash QR response status:', response.status);
      
      if (response.ok) {
        const data = await response.json();
        console.log('GCash QR data received:', data);
        
        if (data.success) {
          console.log('QR Code URL:', data.data.qr_code_url);
          console.log('Has custom QR:', data.data.has_custom_qr);
          setGcashQRData(data.data);
        } else {
          console.error('GCash QR API returned success=false:', data);
          Alert.alert('Error', data.message || 'Failed to load payment information');
        }
      } else {
        const errorText = await response.text();
        console.error('GCash QR API error:', response.status, errorText);
        Alert.alert('Error', 'Failed to load payment information');
      }
    } catch (error) {
      console.error('Error fetching GCash QR:', error);
      Alert.alert('Error', 'Failed to load payment information. Please check your internet connection.');
    } finally {
      setLoadingQR(false);
    }
  };

  const handlePaymentMethodSelect = async () => {
    setPaymentMethodModalVisible(true);
  };

  const handleGCashPayment = async () => {
    if (!laundry?.branch_id) {
      Alert.alert('Error', 'Branch information not available');
      return;
    }
    
    setPaymentMethodModalVisible(false);
    
    // Clear previous QR data
    setGcashQRData(null);
    
    try {
      await fetchGCashQR(laundry.branch_id);
      // The fetchGCashQR function will set gcashQRData if successful
      // We'll show the modal after a brief delay to ensure state is updated
      setTimeout(() => {
        setPaymentModalVisible(true);
      }, 100);
    } catch (error) {
      console.error('Error in handleGCashPayment:', error);
    }
  };

  const handleCashPayment = async () => {
    setPaymentMethodModalVisible(false);
    
    Alert.alert(
      'Cash Payment',
      'Please pay in cash when you pick up your laundry at the branch. The staff will mark your order as paid upon payment.',
      [
        {
          text: 'Cancel',
          style: 'cancel'
        },
        {
          text: 'Confirm',
          onPress: async () => {
            try {
              const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
              if (!token) return;

              const response = await fetch(`${API_BASE_URL}/v1/laundries/${id}/payment-method`, {
                method: 'POST',
                headers: {
                  'Authorization': `Bearer ${token}`,
                  'Accept': 'application/json',
                  'Content-Type': 'application/json',
                },
                body: JSON.stringify({ payment_method: 'cash' }),
              });

              if (response.ok) {
                Alert.alert(
                  'Cash Payment Selected', 
                  'Payment method set to Cash. The branch staff has been notified and will be ready to collect payment when you pick up your laundry.'
                );
                await fetchLaundryDetails();
              } else {
                Alert.alert('Error', 'Failed to set payment method');
              }
            } catch (error) {
              console.error('Error setting cash payment:', error);
              Alert.alert('Error', 'Failed to set payment method');
            }
          }
        }
      ]
    );
  };

  const handleUploadProof = async () => {
    try {
      // Show options for camera or gallery
      Alert.alert(
        'Upload Payment Proof',
        'Choose how you want to add your payment proof:',
        [
          {
            text: 'Take Photo',
            onPress: () => takePhotoFromCamera(),
          },
          {
            text: 'Choose from Gallery',
            onPress: () => pickImageFromGallery(),
          },
          {
            text: 'Cancel',
            style: 'cancel',
          },
        ]
      );
    } catch (error) {
      console.error('Error showing upload options:', error);
      Alert.alert('Error', 'Failed to show upload options');
    }
  };

  const takePhotoFromCamera = async () => {
    try {
      const { status } = await ImagePicker.requestCameraPermissionsAsync();
      if (status !== 'granted') {
        Alert.alert('Permission Required', 'Please grant camera permissions to take a photo.');
        return;
      }

      const result = await ImagePicker.launchCameraAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsEditing: false,  // No cropping for full receipt capture
        quality: 0.9,  // Higher quality for receipt text clarity
      });

      if (!result.canceled && result.assets[0]) {
        const asset = result.assets[0];
        setSelectedImage(asset);
        setConfirmationModalVisible(true);
      }
    } catch (error) {
      console.error('Error taking photo:', error);
      Alert.alert('Error', 'Failed to take photo');
    }
  };

  const pickImageFromGallery = async () => {
    try {
      const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
      if (status !== 'granted') {
        Alert.alert('Permission Required', 'Please grant camera roll permissions to upload payment proof.');
        return;
      }

      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsEditing: false,  // Disable cropping to allow full photo
        quality: 0.8,
        allowsMultipleSelection: false,
      });

      if (!result.canceled && result.assets[0]) {
        const asset = result.assets[0];
        setSelectedImage(asset);
        setConfirmationModalVisible(true);
      }
    } catch (error) {
      console.error('Error picking image:', error);
      Alert.alert('Error', 'Failed to select image');
    }
  };

  const handleConfirmUpload = async () => {
    if (selectedImage) {
      setConfirmationModalVisible(false);
      await uploadPaymentProof(selectedImage);
      setSelectedImage(null);
    }
  };

  const handleCancelUpload = () => {
    setConfirmationModalVisible(false);
    setSelectedImage(null);
  };

  const uploadPaymentProof = async (imageAsset) => {
    try {
      setUploadingProof(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return;

      const formData = new FormData();
      formData.append('amount', laundry.total_amount.toString());
      formData.append('proof_image', {
        uri: imageAsset.uri,
        type: 'image/jpeg',
        name: 'payment_proof.jpg',
      });

      const response = await fetch(`${API_BASE_URL}/v1/customer/laundries/${id}/payment-proof`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
        body: formData,
      });

      const responseText = await response.text();
      console.log('Response status:', response.status);
      console.log('Response text:', responseText);
      
      let data;
      try {
        data = JSON.parse(responseText);
      } catch (parseError) {
        console.error('JSON parse error:', parseError);
        console.error('Response was:', responseText);
        Alert.alert('Error', 'Server returned invalid response. Please try again.');
        return;
      }
      
      if (response.ok && data.success) {
        Alert.alert('Success', 'Payment proof uploaded successfully! Please wait for admin verification.');
        setPaymentModalVisible(false);
        await fetchLaundryDetails();
        await fetchPaymentProof();
      } else {
        Alert.alert('Error', data.message || 'Failed to upload payment proof');
      }
    } catch (error) {
      console.error('Error uploading payment proof:', error);
      Alert.alert('Error', 'Failed to upload payment proof');
    } finally {
      setUploadingProof(false);
    }
  };

  const formatPrice    = (p)  => `₱${parseFloat(p || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  const formatDate     = (d)  => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';
  const formatDateTime = (d)  => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
  const handleCall     = (ph) => Linking.openURL(`tel:${ph}`);

  if (loading) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <View style={styles.loadingWrap}>
          <Ionicons name="shirt-outline" size={32} color={COLORS.primary} />
        </View>
        <ActivityIndicator size="large" color={COLORS.primary} style={{ marginTop: 20 }} />
        <Text style={styles.loadingText}>Loading details...</Text>
      </View>
    );
  }

  if (!laundry) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <View style={styles.errorIcon}>
          <Ionicons name="alert-circle-outline" size={56} color={COLORS.danger} />
        </View>
        <Text style={styles.errorTitle}>Laundry Not Found</Text>
        <Text style={styles.errorText}>We could not find this laundry. Check the tracking number and try again.</Text>
        <TouchableOpacity style={styles.errorBtn} onPress={() => router.back()}>
          <Ionicons name="arrow-back" size={18} color="#FFF" />
          <Text style={styles.errorBtnText}>Go Back</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const isDelivery     = !!laundry.pickup_request_id && ['both','delivery_only'].includes(laundry.service_type);
  const statusCfg      = getStatusConfig(laundry.status, isDelivery);
  const isCancelled    = laundry.status?.toLowerCase() === 'cancelled';
  const isCompleted    = laundry.status?.toLowerCase() === 'completed';
  const isPaid         = laundry.payment_status === 'paid' || isCompleted;
  const isPendingVerification = laundry.payment_status === 'pending_verification';
  const isCashPayment  = laundry.payment_method === 'cash';
  const isReady        = ['ready', 'ready_for_pickup'].includes(laundry.status?.toLowerCase());
  // For delivery: payment happens after delivered; for walk-in: after ready
  const canPay         = !isPaid && !isPendingVerification && !isCancelled && !isCompleted &&
                         (isDelivery ? ['ready','ready_for_pickup','out_for_delivery','delivered'].includes(laundry.status?.toLowerCase()) : isReady);
  const hasPickupFee   = parseFloat(laundry.pickup_fee) > 0;
  const hasDeliveryFee = parseFloat(laundry.delivery_fee) > 0;
  const hasDiscount    = parseFloat(laundry.discount_amount) > 0;
  const showCallButton = ['ready','ready_for_pickup','paid'].includes(laundry.status?.toLowerCase()) && laundry.branch_phone;

  // Debug logging
  console.log('Laundry Debug:', {
    status: laundry.status,
    payment_status: laundry.payment_status,
    payment_method: laundry.payment_method,
    isReady,
    canPay,
    isPaid,
    isCompleted,
    isPendingVerification,
    isCancelled
  });
  
  // Show debug alert
  console.log('PAYMENT BUTTON DEBUG:', {
    'Should show payment button (canPay)': canPay,
    'Status is ready': isReady,
    'Is paid': isPaid,
    'Is pending verification': isPendingVerification,
    'Is cancelled': isCancelled,
    'Is completed': isCompleted
  });

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.backBtn} onPress={() => router.back()}>
          <Ionicons name="chevron-back" size={22} color={COLORS.textPrimary} />
        </TouchableOpacity>
        <View style={styles.headerCenter}>
          <Text style={styles.headerTitle}>Laundry Details</Text>
        </View>
        <TouchableOpacity style={styles.refreshBtn} onPress={onRefresh}>
          <Ionicons name="refresh-outline" size={20} color={COLORS.primary} />
        </TouchableOpacity>
      </View>

      <ScrollView
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={COLORS.primary} />}
        contentContainerStyle={{ paddingBottom: showCallButton ? 110 : 40 }}
      >
        <Animated.View style={{ opacity: fadeAnim, transform: [{ translateY: slideAnim }] }}>

          {/* Status Hero */}
          <View style={styles.heroCard}>
            <LinearGradient colors={statusCfg.gradient} style={styles.heroGradient} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }}>
              <View style={styles.heroCircle1} />
              <View style={styles.heroCircle2} />
              <View style={styles.heroRow}>
                <View style={styles.heroIconWrap}>
                  <Ionicons name={statusCfg.icon} size={32} color="#FFF" />
                </View>
                <View style={styles.heroTextWrap}>
                  <Text style={styles.heroLabel}>{statusCfg.label}</Text>
                  <Text style={styles.heroDesc}>{statusCfg.description}</Text>
                </View>
              </View>
              <View style={styles.trackingPill}>
                <Ionicons name="barcode-outline" size={14} color="rgba(255,255,255,0.85)" />
                <Text style={styles.trackingText}>{laundry.tracking_number}</Text>
              </View>
            </LinearGradient>
          </View>

          {/* Timeline */}
          {!isCancelled && (
            <View style={styles.card}>
              <View style={styles.cardHead}>
                <Ionicons name="git-commit-outline" size={18} color={COLORS.primary} />
                <Text style={styles.cardTitle}>Progress</Text>
                {isDelivery && (
                  <View style={styles.deliveryBadge}>
                    <Ionicons name="bicycle-outline" size={12} color={COLORS.primary} />
                    <Text style={styles.deliveryBadgeText}>Delivery</Text>
                  </View>
                )}
              </View>
              <HorizontalTimeline currentStatus={laundry.status} isDelivery={isDelivery} />
            </View>
          )}

          {isCancelled && (
            <View style={styles.cancelledBanner}>
              <Ionicons name="close-circle" size={20} color={COLORS.danger} />
              <Text style={styles.cancelledText}>This laundry has been cancelled</Text>
            </View>
          )}

          {/* Order Info */}
          <View style={styles.card}>
            <View style={styles.cardHead}>
              <Ionicons name="information-circle-outline" size={18} color={COLORS.primary} />
              <Text style={styles.cardTitle}>Laundry Information</Text>
            </View>
            <DetailRow icon="pricetag-outline" label="Service"  value={laundry.service_name} />
            <DetailRow icon="scale-outline"    label={laundry.pricing_type === 'per_piece' ? 'Pieces' : 'Weight'}   value={laundry.pricing_type === 'per_piece' ? `${laundry.number_of_loads || 1} pieces` : `${parseFloat(laundry.weight || 0).toFixed(2)} kg`} />
            <DetailRow icon="business-outline" label="Branch"   value={laundry.branch_name} />
            <DetailRow icon="calendar-outline" label="Placed"   value={formatDate(laundry.created_at)} />
            {laundry.estimated_completion && (
              <DetailRow icon="time-outline" label="Est. Done" value={formatDate(laundry.estimated_completion)} color={COLORS.accent} />
            )}
            <DetailRow icon="sync-outline" label="Updated" value={formatDateTime(laundry.updated_at)} />
          </View>

          {/* Pricing */}
          <View style={styles.card}>
            <View style={styles.cardHead}>
              <Ionicons name="receipt-outline" size={18} color={COLORS.primary} />
              <Text style={styles.cardTitle}>Bill Details</Text>
              <View style={styles.cardActions}>
                <TouchableOpacity 
                  style={styles.receiptBtn} 
                  onPress={() => router.push(`/receipt/${id}`)}
                  activeOpacity={0.7}
                >
                  <Ionicons name="document-text-outline" size={14} color={COLORS.primary} />
                  <Text style={styles.receiptBtnText}>View Receipt</Text>
                </TouchableOpacity>
                <View style={[styles.payBadge, { backgroundColor: isPaid ? COLORS.successGlow : isPendingVerification ? COLORS.accentGlow : COLORS.dangerGlow }]}>
                  <View style={[styles.payDot, { backgroundColor: isPaid ? COLORS.success : isPendingVerification ? COLORS.accent : COLORS.danger }]} />
                  <Text style={[styles.payText, { color: isPaid ? COLORS.success : isPendingVerification ? COLORS.accent : COLORS.danger }]}>
                    {isPaid ? 'Paid' : isPendingVerification ? 'Pending Verification' : 'Unpaid'}
                  </Text>
                </View>
              </View>
            </View>
            <View style={styles.receiptBody}>
              <PricingRow
                label={laundry.promotion_name ? `${laundry.service_name} (${laundry.promotion_name})` : (laundry.service_name || 'Service')}
                sublabel={`${laundry.number_of_loads || 1} ${(laundry.number_of_loads || 1) === 1 ? (laundry.service_type === 'special_item' ? 'piece' : 'load') : (laundry.service_type === 'special_item' ? 'pieces' : 'loads')} × ₱${(() => {
                  if (laundry.promotion_name && laundry.promotion_price_per_load > 0) {
                    return parseFloat(laundry.promotion_price_per_load).toFixed(2);
                  }
                  if (laundry.pricing_type === 'per_piece' && laundry.price_per_piece > 0) {
                    return parseFloat(laundry.price_per_piece).toFixed(2);
                  }
                  if (laundry.price_per_load > 0) {
                    return parseFloat(laundry.price_per_load).toFixed(2);
                  }
                  const subtotal = parseFloat(laundry.subtotal || laundry.total_amount || 0);
                  const loads = parseFloat(laundry.number_of_loads || 1);
                  return subtotal > 0 && loads > 0 ? (subtotal / loads).toFixed(2) : '0.00';
                })()}/${laundry.service_type === 'special_item' ? 'piece' : 'load'}`}
                value={formatPrice((() => {
                  if (laundry.promotion_name && laundry.promotion_price_per_load > 0) {
                    return (laundry.number_of_loads || 1) * laundry.promotion_price_per_load;
                  }
                  return laundry.subtotal || laundry.total_amount || 0;
                })())}
              />
              <PricingRow label="Service Subtotal" value={formatPrice((() => {
                if (laundry.promotion_name && laundry.promotion_price_per_load > 0) {
                  return (laundry.number_of_loads || 1) * laundry.promotion_price_per_load;
                }
                return laundry.subtotal || laundry.total_amount || 0;
              })())} bold />
              
              {/* Add-ons from API data */}
              {laundry.addons && laundry.addons.length > 0 && (
                <>
                  <View style={styles.addonsHeader}>
                    <Text style={styles.addonsTitle}>Add-ons</Text>
                  </View>
                  {laundry.addons.map((addon, index) => (
                    <PricingRow
                      key={index}
                      label={addon.name || 'Add-on'}
                      sublabel={`(${addon.quantity || 1} × ₱${parseFloat(addon.price || 0).toFixed(2)})`}
                      value={formatPrice(addon.total || (addon.quantity * addon.price))}
                    />
                  ))}
                  <PricingRow 
                    label="Add-ons Total" 
                    value={formatPrice(laundry.addons.reduce((sum, addon) => sum + (addon.total || (addon.quantity * addon.price)), 0))} 
                    bold 
                  />
                </>
              )}
              
              {laundry.promotion_name && !laundry.promotion_price_per_load && (
                <PricingRow
                  label="Promotion Applied"
                  sublabel={laundry.promotion_name}
                  icon="pricetag-outline"
                  value={`-₱${parseFloat(laundry.promotion_discount || 0).toFixed(2)}`}
                  discount
                />
              )}
              {hasPickupFee   && <PricingRow label="Pickup Fee"   icon="car-outline"     value={formatPrice(laundry.pickup_fee)} />}
              {hasDeliveryFee && <PricingRow label="Delivery Fee" icon="bicycle-outline"  value={formatPrice(laundry.delivery_fee)} />}
              {hasDiscount    && <PricingRow label="Discount"     icon="pricetag-outline" value={formatPrice(laundry.discount_amount)} discount />}
              <View style={styles.receiptDivider}>
                {[...Array(Math.floor((SCREEN_WIDTH - 80) / 8))].map((_, i) => (
                  <View key={i} style={styles.receiptDash} />
                ))}
              </View>
              <PricingRow label="Grand Total" value={formatPrice(laundry.total_amount)} bold large />
              
              {/* Payment Section */}
              {canPay && (
                <View style={styles.paymentSection}>
                  <TouchableOpacity style={styles.paymentBtn} onPress={handlePaymentMethodSelect} activeOpacity={0.8}>
                    <LinearGradient colors={COLORS.gradientPrimary} style={styles.paymentGradient} start={{ x: 0, y: 0 }} end={{ x: 1, y: 0 }}>
                      <Ionicons name="card-outline" size={20} color="#FFF" />
                      <Text style={styles.paymentBtnText}>Select Payment Method</Text>
                    </LinearGradient>
                  </TouchableOpacity>
                </View>
              )}
              
              {isPendingVerification && paymentProof && (
                <View style={styles.verificationSection}>
                  <View style={styles.verificationHeader}>
                    <Ionicons name="time-outline" size={16} color={COLORS.accent} />
                    <Text style={styles.verificationText}>Payment proof submitted. Awaiting verification.</Text>
                  </View>
                  <Text style={styles.verificationDate}>Submitted: {formatDateTime(paymentProof.submitted_at)}</Text>
                </View>
              )}
              
              {isCashPayment && !isPaid && (
                <View style={styles.cashPaymentSection}>
                  <View style={styles.cashPaymentHeader}>
                    <Ionicons name="cash-outline" size={16} color={COLORS.success} />
                    <Text style={styles.cashPaymentText}>Cash payment selected. Pay at the branch when picking up.</Text>
                  </View>
                </View>
              )}
            </View>
          </View>

          {/* Branch */}
          {(laundry.branch_name || laundry.branch_address) && (
            <View style={styles.card}>
              <View style={styles.cardHead}>
                <Ionicons name="location-outline" size={18} color={COLORS.primary} />
                <Text style={styles.cardTitle}>Branch</Text>
              </View>
              <View style={styles.branchRow}>
                <View style={styles.branchIcon}>
                  <Ionicons name="business" size={24} color={COLORS.primary} />
                </View>
                <View style={styles.branchInfo}>
                  <Text style={styles.branchName}>{laundry.branch_name}</Text>
                  {laundry.branch_address && <Text style={styles.branchAddr}>{laundry.branch_address}</Text>}
                </View>
              </View>
              {laundry.branch_phone && (
                <TouchableOpacity style={styles.callRow} onPress={() => handleCall(laundry.branch_phone)} activeOpacity={0.7}>
                  <View style={styles.callIconWrap}>
                    <Ionicons name="call" size={16} color={COLORS.success} />
                  </View>
                  <Text style={styles.callPhone}>{laundry.branch_phone}</Text>
                  <View style={styles.callChip}>
                    <Text style={styles.callChipText}>Call</Text>
                  </View>
                </TouchableOpacity>
              )}
            </View>
          )}

          {/* Notes */}
          {laundry.notes && (
            <View style={styles.card}>
              <View style={styles.cardHead}>
                <Ionicons name="document-text-outline" size={18} color={COLORS.primary} />
                <Text style={styles.cardTitle}>Notes</Text>
              </View>
              <View style={styles.notesBox}>
                <Text style={styles.notesText}>{laundry.notes}</Text>
              </View>
            </View>
          )}

          {/* Timestamps */}
          <View style={styles.timestamps}>
            <View style={styles.tsRow}>
              <Text style={styles.tsLabel}>Created</Text>
              <Text style={styles.tsValue}>{formatDateTime(laundry.created_at)}</Text>
            </View>
            <View style={styles.tsDot} />
            <View style={styles.tsRow}>
              <Text style={styles.tsLabel}>Updated</Text>
              <Text style={styles.tsValue}>{formatDateTime(laundry.updated_at)}</Text>
            </View>
          </View>

        </Animated.View>
      </ScrollView>

      {/* Bottom Call Bar */}
      {showCallButton && (
        <View style={styles.bottomBar}>
          <TouchableOpacity style={styles.bottomBtn} onPress={() => handleCall(laundry.branch_phone)} activeOpacity={0.85}>
            <LinearGradient colors={COLORS.gradientPrimary} style={styles.bottomGradient} start={{ x: 0, y: 0 }} end={{ x: 1, y: 0 }}>
              <Ionicons name="call" size={20} color="#FFF" />
              <Text style={styles.bottomBtnText}>Call to Confirm Pickup</Text>
            </LinearGradient>
          </TouchableOpacity>
        </View>
      )}

      {/* Payment Method Selection Modal */}
      <Modal
        visible={paymentMethodModalVisible}
        animationType="slide"
        presentationStyle="pageSheet"
        onRequestClose={() => setPaymentMethodModalVisible(false)}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalHeader}>
            <TouchableOpacity style={styles.modalCloseBtn} onPress={() => setPaymentMethodModalVisible(false)}>
              <Ionicons name="close" size={24} color={COLORS.textPrimary} />
            </TouchableOpacity>
            <Text style={styles.modalTitle}>Select Payment Method</Text>
            <View style={{ width: 40 }} />
          </View>

          <View style={styles.paymentMethodContent}>
            <Text style={styles.paymentMethodSubtitle}>Choose how you want to pay</Text>
            
            {/* GCash Option */}
            <TouchableOpacity style={styles.paymentMethodCard} onPress={handleGCashPayment} activeOpacity={0.8}>
              <LinearGradient colors={['#007DFF', '#0062CC']} style={styles.paymentMethodGradient} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }}>
                <View style={styles.paymentMethodIcon}>
                  <Ionicons name="phone-portrait-outline" size={32} color="#FFF" />
                </View>
                <View style={styles.paymentMethodInfo}>
                  <Text style={styles.paymentMethodName}>GCash</Text>
                  <Text style={styles.paymentMethodDesc}>Pay online via QR code</Text>
                </View>
                <Ionicons name="chevron-forward" size={24} color="rgba(255,255,255,0.7)" />
              </LinearGradient>
            </TouchableOpacity>

            {/* Cash Option */}
            <TouchableOpacity style={styles.paymentMethodCard} onPress={handleCashPayment} activeOpacity={0.8}>
              <LinearGradient colors={['#10B981', '#059669']} style={styles.paymentMethodGradient} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }}>
                <View style={styles.paymentMethodIcon}>
                  <Ionicons name="cash-outline" size={32} color="#FFF" />
                </View>
                <View style={styles.paymentMethodInfo}>
                  <Text style={styles.paymentMethodName}>Cash</Text>
                  <Text style={styles.paymentMethodDesc}>Pay at the branch on pickup (staff will be notified)</Text>
                </View>
                <Ionicons name="chevron-forward" size={24} color="rgba(255,255,255,0.7)" />
              </LinearGradient>
            </TouchableOpacity>

            <View style={styles.paymentMethodNote}>
              <Ionicons name="information-circle-outline" size={16} color={COLORS.textMuted} />
              <Text style={styles.paymentMethodNoteText}>
                For GCash: Upload payment proof after paying{"\n"}
                For Cash: Pay when you pick up your laundry
              </Text>
            </View>
          </View>
        </View>
      </Modal>

      {/* Payment Modal */}
      <Modal
        visible={paymentModalVisible}
        animationType="slide"
        presentationStyle="pageSheet"
        onRequestClose={() => setPaymentModalVisible(false)}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalHeader}>
            <TouchableOpacity style={styles.modalCloseBtn} onPress={() => setPaymentModalVisible(false)}>
              <Ionicons name="close" size={24} color={COLORS.textPrimary} />
            </TouchableOpacity>
            <Text style={styles.modalTitle}>GCash Payment</Text>
            <View style={{ width: 40 }} />
          </View>

          <ScrollView style={styles.modalContent} showsVerticalScrollIndicator={false}>
            {loadingQR ? (
              <View style={styles.loadingContainer}>
                <ActivityIndicator size="large" color={COLORS.primary} />
                <Text style={styles.loadingText}>Loading payment information...</Text>
              </View>
            ) : gcashQRData ? (
              <>
                <View style={styles.qrSection}>
                  <Text style={styles.qrTitle}>Scan QR Code</Text>
                  <Text style={styles.qrSubtitle}>{gcashQRData.branch_name} Branch</Text>
                  
                  <View style={styles.qrContainer}>
                    {gcashQRData.qr_code_url ? (
                      <Image 
                        source={{ uri: gcashQRData.qr_code_url }} 
                        style={styles.qrImage} 
                        resizeMode="contain"
                        onError={(error) => {
                          console.error('QR Image load error:', error.nativeEvent?.error || error);
                          Alert.alert(
                            'Image Load Error', 
                            'Failed to load QR code image. Please try closing and reopening the payment screen, or contact the branch for manual payment details.',
                            [
                              { text: 'Close', onPress: () => setPaymentModalVisible(false) },
                              { text: 'Retry', onPress: () => fetchGCashQR(laundry.branch_id) }
                            ]
                          );
                        }}
                        onLoad={() => console.log('QR Image loaded successfully')}
                      />
                    ) : (
                      <View style={styles.qrPlaceholder}>
                        <Ionicons name="qr-code-outline" size={64} color={COLORS.textMuted} />
                        <Text style={styles.qrPlaceholderText}>QR Code not available</Text>
                      </View>
                    )}
                  </View>
                  
                  {!gcashQRData.has_custom_qr && (
                    <View style={styles.placeholderNotice}>
                      <Ionicons name="information-circle-outline" size={16} color={COLORS.warning} />
                      <Text style={styles.placeholderNoticeText}>
                        This branch is using a placeholder QR code. Please contact the branch directly for the correct GCash payment details.
                      </Text>
                    </View>
                  )}
                  
                  <View style={styles.accountInfo}>
                    <Text style={styles.accountLabel}>Account Name</Text>
                    <Text style={styles.accountValue}>{gcashQRData.account_name}</Text>
                    <Text style={styles.accountLabel}>Account Number</Text>
                    <Text style={styles.accountValue}>{gcashQRData.account_number}</Text>
                  </View>
                </View>

                <View style={styles.amountSection}>
                  <Text style={styles.amountLabel}>Amount to Pay</Text>
                  <Text style={styles.amountValue}>{formatPrice(laundry.total_amount)}</Text>
                </View>

                <View style={styles.instructionsSection}>
                  <Text style={styles.instructionsTitle}>Instructions</Text>
                  {gcashQRData.instructions.map((instruction, index) => (
                    <View key={index} style={styles.instructionItem}>
                      <Text style={styles.instructionNumber}>{index + 1}</Text>
                      <Text style={styles.instructionText}>{instruction}</Text>
                    </View>
                  ))}
                  
                  <View style={styles.receiptTip}>
                    <Ionicons name="bulb-outline" size={16} color={COLORS.warning} />
                    <Text style={styles.receiptTipText}>
                      <Text style={styles.receiptTipBold}>Tip:</Text> Take a clear photo of your complete payment confirmation screen or receipt. Don&apos;t crop the image - capture the full screen for faster verification.
                    </Text>
                  </View>
                </View>

                <TouchableOpacity 
                  style={[styles.uploadBtn, uploadingProof && styles.uploadBtnDisabled]} 
                  onPress={handleUploadProof}
                  disabled={uploadingProof}
                  activeOpacity={0.8}
                >
                  <LinearGradient colors={uploadingProof ? ['#64748B', '#475569'] : COLORS.gradientSuccess} style={styles.uploadGradient}>
                    {uploadingProof ? (
                      <ActivityIndicator size="small" color="#FFF" />
                    ) : (
                      <Ionicons name="camera-outline" size={20} color="#FFF" />
                    )}
                    <Text style={styles.uploadBtnText}>
                      {uploadingProof ? 'Uploading...' : 'Upload Payment Receipt'}
                    </Text>
                  </LinearGradient>
                </TouchableOpacity>

                <View style={styles.uploadInstructions}>
                  <Ionicons name="information-circle-outline" size={16} color={COLORS.textMuted} />
                  <Text style={styles.uploadInstructionsText}>
                    Take a clear photo of your complete payment receipt or screenshot. No cropping needed - capture the full receipt for faster verification.
                  </Text>
                </View>
              </>
            ) : (
              <View style={styles.errorContainer}>
                <Ionicons name="alert-circle-outline" size={48} color={COLORS.danger} />
                <Text style={styles.errorText}>Payment information not available</Text>
                <TouchableOpacity 
                  style={styles.retryBtn} 
                  onPress={() => fetchGCashQR(laundry.branch_id)}
                  activeOpacity={0.8}
                >
                  <Text style={styles.retryBtnText}>Retry</Text>
                </TouchableOpacity>
              </View>
            )}
          </ScrollView>
        </View>
      </Modal>

      {/* Payment Proof Confirmation Modal */}
      <Modal
        visible={confirmationModalVisible}
        animationType="slide"
        presentationStyle="pageSheet"
        onRequestClose={handleCancelUpload}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalHeader}>
            <TouchableOpacity style={styles.modalCloseBtn} onPress={handleCancelUpload}>
              <Ionicons name="close" size={24} color={COLORS.textPrimary} />
            </TouchableOpacity>
            <Text style={styles.modalTitle}>Confirm Payment Proof</Text>
            <View style={{ width: 40 }} />
          </View>

          <ScrollView style={styles.modalContent} showsVerticalScrollIndicator={false}>
            <View style={styles.confirmationSection}>
              <Text style={styles.confirmationTitle}>Review Your Payment Proof</Text>
              <Text style={styles.confirmationSubtitle}>
                Please review the image below and make sure it clearly shows your payment details
              </Text>
              
              {selectedImage && (
                <View style={styles.imagePreviewContainer}>
                  <Image 
                    source={{ uri: selectedImage.uri }} 
                    style={styles.imagePreview} 
                    resizeMode="contain"
                  />
                </View>
              )}
              
              <View style={styles.confirmationDetails}>
                <View style={styles.confirmationRow}>
                  <Text style={styles.confirmationLabel}>Amount:</Text>
                  <Text style={styles.confirmationValue}>{formatPrice(laundry?.total_amount || 0)}</Text>
                </View>
                <View style={styles.confirmationRow}>
                  <Text style={styles.confirmationLabel}>Laundry ID:</Text>
                  <Text style={styles.confirmationValue}>{laundry?.tracking_number}</Text>
                </View>
              </View>
              
              <View style={styles.confirmationTips}>
                <Ionicons name="checkmark-circle-outline" size={16} color={COLORS.success} />
                <Text style={styles.confirmationTipsText}>
                  <Text style={styles.confirmationTipsBold}>Good photo checklist:</Text>{"\n"}
                  • Payment amount is clearly visible{"\n"}
                  • Transaction details are readable{"\n"}
                  • Image is not blurry or cropped{"\n"}
                  • Full receipt/screenshot is captured
                </Text>
              </View>
              
              <View style={styles.confirmationButtons}>
                <TouchableOpacity 
                  style={styles.retakeBtn} 
                  onPress={handleCancelUpload}
                  activeOpacity={0.8}
                >
                  <Ionicons name="camera-outline" size={18} color={COLORS.textSecondary} />
                  <Text style={styles.retakeBtnText}>Retake Photo</Text>
                </TouchableOpacity>
                
                <TouchableOpacity 
                  style={[styles.confirmBtn, uploadingProof && styles.confirmBtnDisabled]} 
                  onPress={handleConfirmUpload}
                  disabled={uploadingProof}
                  activeOpacity={0.8}
                >
                  <LinearGradient colors={uploadingProof ? ['#64748B', '#475569'] : COLORS.gradientSuccess} style={styles.confirmGradient}>
                    {uploadingProof ? (
                      <ActivityIndicator size="small" color="#FFF" />
                    ) : (
                      <Ionicons name="checkmark-outline" size={18} color="#FFF" />
                    )}
                    <Text style={styles.confirmBtnText}>
                      {uploadingProof ? 'Uploading...' : 'Confirm & Upload'}
                    </Text>
                  </LinearGradient>
                </TouchableOpacity>
              </View>
            </View>
          </ScrollView>
        </View>
      </Modal>

      {/* Rating Modal */}
      <RatingModal
        visible={showRatingModal}
        onClose={() => setShowRatingModal(false)}
        laundry={laundry}
        onRatingSubmitted={() => {
          fetchLaundryDetails();
        }}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container:     { flex: 1, backgroundColor: COLORS.background },
  centerContent: { justifyContent: 'center', alignItems: 'center', padding: 32 },
  loadingWrap:   { width: 72, height: 72, borderRadius: 24, backgroundColor: COLORS.primaryGlow, justifyContent: 'center', alignItems: 'center' },
  loadingText:   { color: COLORS.textSecondary, marginTop: 12, fontSize: 14, fontWeight: '500' },
  errorIcon:     { width: 88, height: 88, borderRadius: 28, backgroundColor: COLORS.dangerGlow, justifyContent: 'center', alignItems: 'center', marginBottom: 20 },
  errorTitle:    { fontSize: 22, fontWeight: '800', color: COLORS.textPrimary, marginBottom: 8 },
  errorText:     { fontSize: 14, color: COLORS.textMuted, textAlign: 'center', lineHeight: 20, marginBottom: 28 },
  errorBtn:      { flexDirection: 'row', alignItems: 'center', gap: 8, backgroundColor: COLORS.primary, paddingHorizontal: 28, paddingVertical: 14, borderRadius: 14 },
  errorBtnText:  { color: '#FFF', fontSize: 15, fontWeight: '700' },
  header:        { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 16, paddingTop: Platform.OS === 'ios' ? 58 : 44, paddingBottom: 12, gap: 8 },
  backBtn:       { width: 40, height: 40, borderRadius: 13, backgroundColor: COLORS.surface, justifyContent: 'center', alignItems: 'center', borderWidth: 1, borderColor: COLORS.borderLight },
  headerCenter:  { flex: 1, alignItems: 'center' },
  headerTitle:   { fontSize: 17, fontWeight: '700', color: COLORS.textPrimary },
  refreshBtn:    { width: 40, height: 40, borderRadius: 13, backgroundColor: COLORS.primarySoft, justifyContent: 'center', alignItems: 'center' },
  heroCard:      { marginHorizontal: 16, marginTop: 8, marginBottom: 16, borderRadius: 22, overflow: 'hidden', elevation: 8, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.25, shadowRadius: 12 },
  heroGradient:  { padding: 22, position: 'relative', overflow: 'hidden' },
  heroCircle1:   { position: 'absolute', width: 120, height: 120, borderRadius: 60, backgroundColor: 'rgba(255,255,255,0.06)', top: -30, right: -20 },
  heroCircle2:   { position: 'absolute', width: 70, height: 70, borderRadius: 35, backgroundColor: 'rgba(255,255,255,0.04)', bottom: -15, left: -10 },
  heroRow:       { flexDirection: 'row', alignItems: 'center', gap: 16, marginBottom: 16 },
  heroIconWrap:  { width: 56, height: 56, borderRadius: 18, backgroundColor: 'rgba(255,255,255,0.18)', justifyContent: 'center', alignItems: 'center' },
  heroTextWrap:  { flex: 1 },
  heroLabel:     { fontSize: 22, fontWeight: '800', color: '#FFF', letterSpacing: -0.3 },
  heroDesc:      { fontSize: 13, color: 'rgba(255,255,255,0.8)', marginTop: 3, lineHeight: 18 },
  trackingPill:  { flexDirection: 'row', alignItems: 'center', alignSelf: 'flex-start', gap: 6, backgroundColor: 'rgba(255,255,255,0.15)', paddingHorizontal: 14, paddingVertical: 8, borderRadius: 20 },
  trackingText:  { fontSize: 13, fontWeight: '700', color: '#FFF', fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace', letterSpacing: 0.8 },
  card:          { backgroundColor: COLORS.surface, marginHorizontal: 16, marginBottom: 12, borderRadius: 18, padding: 18, borderWidth: 1, borderColor: COLORS.borderLight },
  cardHead:      { flexDirection: 'row', alignItems: 'center', marginBottom: 14, gap: 8 },
  cardTitle:     { fontSize: 15, fontWeight: '700', color: COLORS.textPrimary, flex: 1 },
  cardActions:   { flexDirection: 'row', alignItems: 'center', gap: 8 },
  receiptBtn:    { flexDirection: 'row', alignItems: 'center', gap: 4, backgroundColor: COLORS.primarySoft, paddingHorizontal: 10, paddingVertical: 6, borderRadius: 8 },
  receiptBtnText:{ fontSize: 11, fontWeight: '600', color: COLORS.primary },
  cancelledBanner: { flexDirection: 'row', alignItems: 'center', gap: 10, marginHorizontal: 16, marginBottom: 12, backgroundColor: COLORS.dangerGlow, paddingHorizontal: 18, paddingVertical: 14, borderRadius: 14, borderWidth: 1, borderColor: COLORS.danger + '30' },
  cancelledText: { fontSize: 14, fontWeight: '600', color: COLORS.danger },
  payBadge:      { flexDirection: 'row', alignItems: 'center', gap: 5, paddingHorizontal: 10, paddingVertical: 5, borderRadius: 10 },
  payDot:        { width: 6, height: 6, borderRadius: 3 },
  payText:       { fontSize: 11, fontWeight: '700', letterSpacing: 0.3 },
  receiptBody:   { paddingTop: 2 },
  receiptDivider:{ flexDirection: 'row', alignItems: 'center', justifyContent: 'center', marginVertical: 10, gap: 4 },
  receiptDash:   { width: 4, height: 1.5, backgroundColor: COLORS.border, borderRadius: 1 },
  branchRow:     { flexDirection: 'row', alignItems: 'center', gap: 14, marginBottom: 14 },
  branchIcon:    { width: 50, height: 50, borderRadius: 16, backgroundColor: COLORS.primaryGlow, justifyContent: 'center', alignItems: 'center' },
  branchInfo:    { flex: 1 },
  branchName:    { fontSize: 16, fontWeight: '700', color: COLORS.textPrimary, marginBottom: 3 },
  branchAddr:    { fontSize: 13, color: COLORS.textMuted, lineHeight: 18 },
  callRow:       { flexDirection: 'row', alignItems: 'center', gap: 10, backgroundColor: COLORS.surfaceElevated, paddingHorizontal: 14, paddingVertical: 12, borderRadius: 14 },
  callIconWrap:  { width: 32, height: 32, borderRadius: 10, backgroundColor: COLORS.successGlow, justifyContent: 'center', alignItems: 'center' },
  callPhone:     { flex: 1, fontSize: 14, fontWeight: '600', color: COLORS.textPrimary },
  callChip:      { backgroundColor: COLORS.successGlow, paddingHorizontal: 14, paddingVertical: 6, borderRadius: 8 },
  callChipText:  { fontSize: 12, fontWeight: '700', color: COLORS.success },
  notesBox:      { backgroundColor: COLORS.surfaceElevated, borderRadius: 14, padding: 16 },
  notesText:     { fontSize: 13, color: COLORS.textSecondary, lineHeight: 20 },
  timestamps:    { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingHorizontal: 16, paddingTop: 8, paddingBottom: 4, gap: 12 },
  tsRow:         { alignItems: 'center' },
  tsLabel:       { fontSize: 10, fontWeight: '700', color: COLORS.textMuted, letterSpacing: 0.5, textTransform: 'uppercase', marginBottom: 2 },
  tsValue:       { fontSize: 11, color: COLORS.textSecondary, fontWeight: '500' },
  tsDot:         { width: 4, height: 4, borderRadius: 2, backgroundColor: COLORS.border },
  addonsHeader:  { paddingVertical: 8, marginTop: 4 },
  addonsTitle:   { fontSize: 14, fontWeight: '700', color: COLORS.textPrimary },
  deliveryBadge: { flexDirection: 'row', alignItems: 'center', gap: 4, backgroundColor: COLORS.primarySoft, paddingHorizontal: 8, paddingVertical: 4, borderRadius: 8 },
  deliveryBadgeText: { fontSize: 10, fontWeight: '700', color: COLORS.primary, textTransform: 'uppercase', letterSpacing: 0.5 },
  bottomBar:     { position: 'absolute', bottom: 0, left: 0, right: 0, backgroundColor: COLORS.background, paddingHorizontal: 16, paddingTop: 12, paddingBottom: Platform.OS === 'ios' ? 34 : 20, borderTopWidth: 1, borderTopColor: COLORS.borderLight },
  bottomBtn:     { borderRadius: 16, overflow: 'hidden' },
  bottomGradient:{ flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 10, paddingVertical: 16 },
  bottomBtnText: { fontSize: 16, fontWeight: '700', color: '#FFF' },
  
  // Payment Section
  paymentSection: { marginTop: 16, paddingTop: 16, borderTopWidth: 1, borderTopColor: COLORS.borderLight },
  paymentBtn:     { borderRadius: 14, overflow: 'hidden' },
  paymentGradient:{ flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 10, paddingVertical: 14 },
  paymentBtnText: { fontSize: 15, fontWeight: '700', color: '#FFF' },
  
  verificationSection: { marginTop: 16, paddingTop: 16, borderTopWidth: 1, borderTopColor: COLORS.borderLight },
  verificationHeader:  { flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 4 },
  verificationText:    { fontSize: 13, fontWeight: '600', color: COLORS.accent, flex: 1 },
  verificationDate:    { fontSize: 11, color: COLORS.textMuted },
  
  cashPaymentSection:  { marginTop: 16, paddingTop: 16, borderTopWidth: 1, borderTopColor: COLORS.borderLight },
  cashPaymentHeader:   { flexDirection: 'row', alignItems: 'center', gap: 8 },
  cashPaymentText:     { fontSize: 13, fontWeight: '600', color: COLORS.success, flex: 1 },
  
  // Modal Styles
  modalContainer:  { flex: 1, backgroundColor: COLORS.background },
  modalHeader:     { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 20, paddingTop: Platform.OS === 'ios' ? 60 : 40, paddingBottom: 20, borderBottomWidth: 1, borderBottomColor: COLORS.borderLight },
  modalCloseBtn:   { width: 40, height: 40, borderRadius: 13, backgroundColor: COLORS.surface, justifyContent: 'center', alignItems: 'center' },
  modalTitle:      { fontSize: 18, fontWeight: '700', color: COLORS.textPrimary },
  modalContent:    { flex: 1, paddingHorizontal: 20 },
  
  qrSection:       { alignItems: 'center', paddingVertical: 24 },
  qrTitle:         { fontSize: 20, fontWeight: '800', color: COLORS.textPrimary, marginBottom: 4 },
  qrSubtitle:      { fontSize: 14, color: COLORS.textMuted, marginBottom: 24 },
  qrContainer:     { width: 200, height: 200, backgroundColor: '#FFF', borderRadius: 16, padding: 16, marginBottom: 24, elevation: 4, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 8, justifyContent: 'center', alignItems: 'center' },
  qrImage:         { width: '100%', height: '100%' },
  qrPlaceholder:   { alignItems: 'center', justifyContent: 'center', flex: 1 },
  qrPlaceholderText: { fontSize: 12, color: COLORS.textMuted, marginTop: 8, textAlign: 'center' },
  
  placeholderNotice: { flexDirection: 'row', alignItems: 'flex-start', gap: 8, backgroundColor: COLORS.accentGlow, padding: 12, borderRadius: 12, marginBottom: 16, borderWidth: 1, borderColor: COLORS.warning + '30' },
  placeholderNoticeText: { flex: 1, fontSize: 12, color: COLORS.textSecondary, lineHeight: 16 },
  
  accountInfo:     { alignItems: 'center', gap: 8 },
  accountLabel:    { fontSize: 12, fontWeight: '600', color: COLORS.textMuted, textTransform: 'uppercase', letterSpacing: 0.5 },
  accountValue:    { fontSize: 16, fontWeight: '700', color: COLORS.textPrimary, marginBottom: 8 },
  
  amountSection:   { backgroundColor: COLORS.surface, borderRadius: 16, padding: 20, marginBottom: 24, alignItems: 'center', borderWidth: 1, borderColor: COLORS.borderLight },
  amountLabel:     { fontSize: 14, fontWeight: '600', color: COLORS.textMuted, marginBottom: 8 },
  amountValue:     { fontSize: 28, fontWeight: '800', color: COLORS.primary, letterSpacing: -0.5 },
  
  instructionsSection: { marginBottom: 32 },
  instructionsTitle:   { fontSize: 16, fontWeight: '700', color: COLORS.textPrimary, marginBottom: 16 },
  instructionItem:     { flexDirection: 'row', alignItems: 'flex-start', gap: 12, marginBottom: 12 },
  instructionNumber:   { width: 24, height: 24, borderRadius: 12, backgroundColor: COLORS.primaryGlow, color: COLORS.primary, fontSize: 12, fontWeight: '700', textAlign: 'center', lineHeight: 24 },
  instructionText:     { flex: 1, fontSize: 14, color: COLORS.textSecondary, lineHeight: 20 },
  
  receiptTip:          { flexDirection: 'row', alignItems: 'flex-start', gap: 8, backgroundColor: COLORS.accentGlow, padding: 12, borderRadius: 12, marginTop: 16, borderWidth: 1, borderColor: COLORS.warning + '30' },
  receiptTipText:      { flex: 1, fontSize: 13, color: COLORS.textSecondary, lineHeight: 18 },
  receiptTipBold:      { fontWeight: '700', color: COLORS.warning },
  
  uploadBtn:        { borderRadius: 16, overflow: 'hidden', marginBottom: 16 },
  uploadBtnDisabled:{ opacity: 0.7 },
  uploadGradient:   { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 10, paddingVertical: 16 },
  uploadBtnText:    { fontSize: 16, fontWeight: '700', color: '#FFF' },
  
  uploadInstructions: { flexDirection: 'row', alignItems: 'flex-start', gap: 8, backgroundColor: COLORS.surface, padding: 12, borderRadius: 12, marginBottom: 24 },
  uploadInstructionsText: { flex: 1, fontSize: 12, color: COLORS.textMuted, lineHeight: 16 },
  
  // Payment Method Selection Styles
  paymentMethodContent:  { flex: 1, paddingHorizontal: 20, paddingTop: 20 },
  paymentMethodSubtitle: { fontSize: 16, fontWeight: '600', color: COLORS.textSecondary, marginBottom: 24, textAlign: 'center' },
  paymentMethodCard:     { marginBottom: 16, borderRadius: 18, overflow: 'hidden', elevation: 4, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.15, shadowRadius: 8 },
  paymentMethodGradient: { flexDirection: 'row', alignItems: 'center', padding: 20, gap: 16 },
  paymentMethodIcon:     { width: 56, height: 56, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.2)', justifyContent: 'center', alignItems: 'center' },
  paymentMethodInfo:     { flex: 1 },
  paymentMethodName:     { fontSize: 18, fontWeight: '800', color: '#FFF', marginBottom: 4 },
  paymentMethodDesc:     { fontSize: 13, color: 'rgba(255,255,255,0.85)' },
  paymentMethodNote:     { flexDirection: 'row', alignItems: 'flex-start', gap: 8, backgroundColor: COLORS.surface, padding: 16, borderRadius: 12, marginTop: 8 },
  paymentMethodNoteText: { flex: 1, fontSize: 12, color: COLORS.textMuted, lineHeight: 18 },
  
  // Loading and Error States
  loadingContainer:      { flex: 1, justifyContent: 'center', alignItems: 'center', paddingVertical: 60 },
  paymentLoadingText:    { fontSize: 14, color: COLORS.textSecondary, marginTop: 16 },
  errorContainer:        { flex: 1, justifyContent: 'center', alignItems: 'center', paddingVertical: 60 },
  paymentErrorText:      { fontSize: 16, color: COLORS.textSecondary, marginTop: 16, textAlign: 'center' },
  retryBtn:              { backgroundColor: COLORS.primary, paddingHorizontal: 24, paddingVertical: 12, borderRadius: 12, marginTop: 20 },
  retryBtnText:          { fontSize: 14, fontWeight: '600', color: '#FFF' },
  
  // Confirmation Modal Styles
  confirmationSection:   { paddingVertical: 20 },
  confirmationTitle:     { fontSize: 20, fontWeight: '800', color: COLORS.textPrimary, textAlign: 'center', marginBottom: 8 },
  confirmationSubtitle:  { fontSize: 14, color: COLORS.textSecondary, textAlign: 'center', marginBottom: 24, lineHeight: 20 },
  imagePreviewContainer: { backgroundColor: COLORS.surface, borderRadius: 16, padding: 16, marginBottom: 24, borderWidth: 1, borderColor: COLORS.borderLight },
  imagePreview:          { width: '100%', height: 300, borderRadius: 12 },
  confirmationDetails:   { backgroundColor: COLORS.surface, borderRadius: 16, padding: 16, marginBottom: 24, borderWidth: 1, borderColor: COLORS.borderLight },
  confirmationRow:       { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
  confirmationLabel:     { fontSize: 14, fontWeight: '600', color: COLORS.textMuted },
  confirmationValue:     { fontSize: 14, fontWeight: '700', color: COLORS.textPrimary },
  confirmationTips:      { flexDirection: 'row', alignItems: 'flex-start', gap: 8, backgroundColor: COLORS.successGlow, padding: 16, borderRadius: 12, marginBottom: 24, borderWidth: 1, borderColor: COLORS.success + '30' },
  confirmationTipsText:  { flex: 1, fontSize: 13, color: COLORS.textSecondary, lineHeight: 18 },
  confirmationTipsBold:  { fontWeight: '700', color: COLORS.success },
  confirmationButtons:   { flexDirection: 'row', gap: 12 },
  retakeBtn:             { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, backgroundColor: COLORS.surface, paddingVertical: 14, borderRadius: 12, borderWidth: 1, borderColor: COLORS.borderLight },
  retakeBtnText:         { fontSize: 14, fontWeight: '600', color: COLORS.textSecondary },
  confirmBtn:            { flex: 2, borderRadius: 12, overflow: 'hidden' },
  confirmBtnDisabled:    { opacity: 0.7 },
  confirmGradient:       { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, paddingVertical: 14 },
  confirmBtnText:        { fontSize: 14, fontWeight: '700', color: '#FFF' },
});
