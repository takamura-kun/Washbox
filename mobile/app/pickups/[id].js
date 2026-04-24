import React, { useState, useEffect, useCallback, Fragment } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  Platform,
  Image,
  Alert,
  Modal,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router, useLocalSearchParams } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { LinearGradient } from 'expo-linear-gradient';
import { API_BASE_URL, STORAGE_KEYS } from '../../constants/config';

// ─── Design System ───
const COLORS = {
  background: '#06081A',
  surface: '#0F1332',
  surfaceLight: '#171D45',
  surfaceElevated: '#1E2654',
  primary: '#0EA5E9',
  primaryDark: '#0284C7',
  primarySoft: 'rgba(14, 165, 233, 0.08)',
  primaryGlow: 'rgba(14, 165, 233, 0.15)',
  pickup: '#10B981',
  pickupGlow: 'rgba(16, 185, 129, 0.15)',
  warning: '#F59E0B',
  warningGlow: 'rgba(245, 158, 11, 0.15)',
  danger: '#EF4444',
  dangerGlow: 'rgba(239, 68, 68, 0.15)',
  purple: '#8B5CF6',
  purpleGlow: 'rgba(139, 92, 246, 0.15)',
  textPrimary: '#F1F5F9',
  textSecondary: '#94A3B8',
  textMuted: '#64748B',
  border: '#1E293B',
  borderLight: 'rgba(255, 255, 255, 0.06)',
};

const STATUS_CONFIG = {
  pending:              { color: '#F59E0B', glow: 'rgba(245,158,11,0.15)',   icon: 'time-outline',           label: 'Pending' },
  accepted:             { color: '#0EA5E9', glow: 'rgba(14,165,233,0.15)',   icon: 'checkmark-circle-outline',label: 'Accepted' },
  en_route:             { color: '#10B981', glow: 'rgba(16,185,129,0.15)',   icon: 'navigate-outline',        label: 'En Route' },
  picked_up:            { color: '#8B5CF6', glow: 'rgba(139,92,246,0.15)',   icon: 'bag-handle-outline',      label: 'Picked Up' },
  cancelled:            { color: '#EF4444', glow: 'rgba(239,68,68,0.15)',    icon: 'close-circle-outline',    label: 'Cancelled' },
};

// ─── Status Timeline Steps (Pickup Only) ───
const TIMELINE_STEPS = [
  'pending',
  'accepted',
  'en_route',
  'picked_up',
];

export default function PickupDetailScreen() {
  const { id } = useLocalSearchParams();
  const [pickup, setPickup] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [cancelling, setCancelling] = useState(false);
  const [showFullImage, setShowFullImage] = useState(false);

  const fetchPickup = useCallback(async () => {
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
        console.log('Pickup API Response:', JSON.stringify(data, null, 2)); // Debug log
        if (data.success && data.data) {
          // Extract pickup from nested structure
          const pickupData = data.data.pickup || data.data;
          const laundryData = data.data.laundry || null;
          console.log('Pickup Data:', JSON.stringify(pickupData, null, 2)); // Debug log
          console.log('Laundry Data:', JSON.stringify(laundryData, null, 2)); // Debug log
          
          // Combine pickup and laundry data
          setPickup({
            ...pickupData,
            laundry: laundryData,
            service: pickupData.service || null, // Include service data
          });
        }
      } else if (response.status === 401) {
        router.replace('/(auth)/login');
      } else if (response.status === 404) {
        router.back();
      }
    } catch (error) {
      console.error('Error fetching pickup:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [id]);

  useEffect(() => {
    fetchPickup();
    
    // Auto-refresh every 15 seconds when viewing pickup details
    const interval = setInterval(() => {
      fetchPickup();
    }, 15000);
    
    return () => clearInterval(interval);
  }, [fetchPickup]);

  const onRefresh = () => {
    setRefreshing(true);
    fetchPickup();
  };

  const handleCancel = async () => {
    if (cancelling) return;
    
    // Show confirmation alert
    Alert.alert(
      'Cancel Pickup Request',
      'Are you sure you want to cancel this pickup request? This action cannot be undone.',
      [
        {
          text: 'No, Keep It',
          style: 'cancel',
        },
        {
          text: 'Yes, Cancel',
          style: 'destructive',
          onPress: async () => {
            setCancelling(true);
            try {
              const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
              const response = await fetch(`${API_BASE_URL}/v1/pickups/${id}/cancel`, {
                method: 'PUT',
                headers: {
                  'Authorization': `Bearer ${token}`,
                  'Content-Type': 'application/json',
                  'Accept': 'application/json',
                },
              });

              const data = await response.json();

              if (response.ok && data.success) {
                Alert.alert(
                  'Pickup Cancelled',
                  'Your pickup request has been cancelled successfully.',
                  [
                    {
                      text: 'OK',
                      onPress: () => {
                        // Refresh the pickup data to show updated status
                        fetchPickup();
                      },
                    },
                  ]
                );
              } else {
                Alert.alert(
                  'Cannot Cancel',
                  data.message || 'This pickup request cannot be cancelled at this time.',
                  [{ text: 'OK' }]
                );
              }
            } catch (error) {
              console.error('Error cancelling pickup:', error);
              Alert.alert(
                'Error',
                'Failed to cancel pickup request. Please try again.',
                [{ text: 'OK' }]
              );
            } finally {
              setCancelling(false);
            }
          },
        },
      ]
    );
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      weekday: 'long', month: 'long', day: 'numeric', year: 'numeric',
    });
  };

  const formatTime = (timeString) => {
    if (!timeString) return '';
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour > 12 ? hour - 12 : hour === 0 ? 12 : hour;
    return `${displayHour}:${minutes} ${ampm}`;
  };

  const formatCreatedAt = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      month: 'short', day: 'numeric', year: 'numeric',
      hour: '2-digit', minute: '2-digit',
    });
  };

  // ─── Loading ───
  if (loading) {
    return (
      <View style={[styles.container, styles.centered]}>
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Loading pickup details...</Text>
      </View>
    );
  }

  if (!pickup) {
    return (
      <View style={[styles.container, styles.centered]}>
        <Ionicons name="alert-circle-outline" size={48} color={COLORS.danger} />
        <Text style={styles.errorText}>Pickup not found</Text>
        <TouchableOpacity style={styles.backBtn} onPress={() => router.back()}>
          <Text style={styles.backBtnText}>Go Back</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const statusCfg = STATUS_CONFIG[pickup.status] || STATUS_CONFIG.pending;
  const isActive = !['picked_up', 'cancelled'].includes(pickup.status);
  const isCancellable = ['pending'].includes(pickup.status);

  // Use pickup status only
  const currentStatus = pickup.status;
  const currentStatusCfg = STATUS_CONFIG[currentStatus] || statusCfg;

  // Which step index is active in the timeline
  const currentStepIdx = TIMELINE_STEPS.indexOf(currentStatus);

  return (
    <View style={styles.container}>
      {/* ─── Header ─── */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.headerBack} onPress={() => router.back()}>
          <Ionicons name="arrow-back" size={22} color={COLORS.textPrimary} />
        </TouchableOpacity>
        <View>
          <Text style={styles.headerTitle}>Pickup #{pickup.id}</Text>
          <Text style={styles.headerSub}>{formatCreatedAt(pickup.created_at)}</Text>
        </View>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView
        style={styles.scroll}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor={COLORS.primary}
            colors={[COLORS.primary]}
          />
        }
      >
        {/* ─── Status Banner ─── */}
        <View style={[styles.statusBanner, { backgroundColor: currentStatusCfg.glow, borderColor: currentStatusCfg.color + '30' }]}>
          <View style={[styles.statusIconCircle, { backgroundColor: currentStatusCfg.color + '20' }]}>
            <Ionicons name={currentStatusCfg.icon} size={28} color={currentStatusCfg.color} />
          </View>
          <View style={styles.statusBannerText}>
            <Text style={styles.statusBannerLabel}>Current Status</Text>
            <Text style={[styles.statusBannerValue, { color: currentStatusCfg.color }]}>
              {currentStatusCfg.label}
            </Text>
          </View>
          {isActive && currentStatus !== 'picked_up' && (
            <View style={[styles.activePill, { backgroundColor: COLORS.pickup + '20' }]}>
              <View style={[styles.activeDot, { backgroundColor: COLORS.pickup }]} />
              <Text style={[styles.activePillText, { color: COLORS.pickup }]}>Active</Text>
            </View>
          )}
        </View>

        {/* ─── Timeline (Pickup Only - Horizontal) ─── */}
        {pickup.status !== 'cancelled' && (
          <View style={styles.card}>
            <Text style={styles.sectionTitle}>Pickup Progress</Text>
            <View style={styles.horizontalTimeline}>
              {TIMELINE_STEPS.map((step, idx) => {
                const stepCfg = STATUS_CONFIG[step];
                const isDone = idx < currentStepIdx;
                const isCurrent = idx === currentStepIdx;
                const isUpcoming = idx > currentStepIdx;

                return (
                  <React.Fragment key={step}>
                    <View style={styles.horizontalStep}>
                      <View style={[
                        styles.horizontalDot,
                        isDone && { backgroundColor: COLORS.pickup, borderColor: COLORS.pickup },
                        isCurrent && { backgroundColor: stepCfg.color, borderColor: stepCfg.color, borderWidth: 3 },
                        isUpcoming && { backgroundColor: COLORS.surfaceElevated, borderColor: COLORS.borderLight },
                      ]}>
                        {isDone && (
                          <Ionicons name="checkmark" size={16} color="#FFF" />
                        )}
                        {isCurrent && (
                          <Ionicons name={stepCfg.icon} size={16} color={stepCfg.color} />
                        )}
                      </View>
                      <Text style={[
                        styles.horizontalLabel,
                        isCurrent && { color: stepCfg.color, fontWeight: '700' },
                        isDone && { color: COLORS.textSecondary },
                        isUpcoming && { color: COLORS.textMuted },
                      ]}>
                        {stepCfg.label}
                      </Text>
                    </View>
                    {idx < TIMELINE_STEPS.length - 1 && (
                      <View style={[
                        styles.horizontalLine,
                        { backgroundColor: isDone ? COLORS.pickup : COLORS.borderLight },
                      ]} />
                    )}
                  </React.Fragment>
                );
              })}
            </View>
          </View>
        )}

        {/* ─── Pickup Proof Photo ─── */}
        {pickup.pickup_proof_photo_url && (
          <View style={styles.card}>
            <View style={styles.proofHeader}>
              <Ionicons name="camera" size={18} color={COLORS.pickup} />
              <Text style={styles.sectionTitle}>Proof Photo</Text>
            </View>
            <View style={styles.proofBanner}>
              <Ionicons name="checkmark-circle" size={20} color={COLORS.pickup} />
              <Text style={styles.proofBannerText}>Your laundry has arrived at our shop!</Text>
            </View>
            <TouchableOpacity
              style={styles.proofImageContainer}
              onPress={() => setShowFullImage(true)}
              activeOpacity={0.8}
            >
              <Image
                source={{ uri: pickup.pickup_proof_photo_url }}
                style={styles.proofImage}
                resizeMode="cover"
              />
              <View style={styles.proofOverlay}>
                <Ionicons name="expand" size={24} color="#FFF" />
              </View>
            </TouchableOpacity>
            {pickup.proof_uploaded_at && (
              <View style={styles.proofFooter}>
                <Ionicons name="time-outline" size={14} color={COLORS.textMuted} />
                <Text style={styles.proofTime}>
                  Uploaded: {formatCreatedAt(pickup.proof_uploaded_at)}
                </Text>
              </View>
            )}
          </View>
        )}

        {/* ─── Full Image Modal ─── */}
        <Modal
          visible={showFullImage}
          transparent={true}
          animationType="fade"
          onRequestClose={() => setShowFullImage(false)}
        >
          <View style={styles.modalContainer}>
            <TouchableOpacity
              style={styles.modalBackdrop}
              onPress={() => setShowFullImage(false)}
              activeOpacity={1}
            />
            <View style={styles.modalContent}>
              <TouchableOpacity
                style={styles.modalClose}
                onPress={() => setShowFullImage(false)}
              >
                <Ionicons name="close" size={28} color="#FFF" />
              </TouchableOpacity>
              <Image
                source={{ uri: pickup.pickup_proof_photo_url }}
                style={styles.fullImage}
                resizeMode="contain"
              />
            </View>
          </View>
        </Modal>

        {/* ─── Address ─── */}
        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Address</Text>
          <View style={styles.addressRow}>
            <View style={[styles.addressIcon, { backgroundColor: COLORS.pickupGlow }]}>
              <Ionicons name="location" size={18} color={COLORS.pickup} />
            </View>
            <View style={styles.addressContent}>
              <Text style={styles.addressText}>{pickup.pickup_address || 'Not specified'}</Text>
              <View style={styles.returnNote}>
                <Ionicons name="refresh-outline" size={11} color={COLORS.primary} />
                <Text style={styles.returnNoteText}>Pickup & delivery to same address</Text>
              </View>
            </View>
          </View>
        </View>

        {/* ─── Schedule ─── */}
        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Schedule</Text>
          <View style={styles.infoRow}>
            <View style={[styles.infoIcon, { backgroundColor: COLORS.primarySoft }]}>
              <Ionicons name="calendar-outline" size={16} color={COLORS.primary} />
            </View>
            <View>
              <Text style={styles.infoLabel}>Preferred Date</Text>
              <Text style={styles.infoValue}>{formatDate(pickup.preferred_date)}</Text>
            </View>
          </View>
          <View style={styles.infoRow}>
            <View style={[styles.infoIcon, { backgroundColor: COLORS.primarySoft }]}>
              <Ionicons name="time-outline" size={16} color={COLORS.primary} />
            </View>
            <View>
              <Text style={styles.infoLabel}>Preferred Time</Text>
              <Text style={styles.infoValue}>{formatTime(pickup.preferred_time)}</Text>
            </View>
          </View>
        </View>

        {/* ─── Service Information ─── */}
        {pickup.service && (
          <View style={styles.card}>
            <View style={styles.serviceHeader}>
              <Ionicons name="shirt-outline" size={18} color={COLORS.purple} />
              <Text style={styles.sectionTitle}>Selected Service</Text>
            </View>
            <View style={styles.serviceBanner}>
              <View style={styles.serviceIconCircle}>
                <Ionicons 
                  name={
                    pickup.service.name?.toLowerCase().includes('wash') ? 'water' :
                    pickup.service.name?.toLowerCase().includes('dry') ? 'sunny' :
                    pickup.service.name?.toLowerCase().includes('iron') ? 'flame' :
                    pickup.service.name?.toLowerCase().includes('fold') ? 'layers' :
                    'shirt'
                  } 
                  size={24} 
                  color={COLORS.purple} 
                />
              </View>
              <View style={styles.serviceContent}>
                <Text style={styles.serviceName}>{pickup.service.name}</Text>
                {pickup.service.description && (
                  <Text style={styles.serviceDescription} numberOfLines={2}>
                    {pickup.service.description}
                  </Text>
                )}
                <View style={styles.servicePricing}>
                  {pickup.service.price_per_kilo && (
                    <View style={styles.servicePriceTag}>
                      <Ionicons name="pricetag" size={12} color={COLORS.purple} />
                      <Text style={styles.servicePriceText}>
                        ₱{parseFloat(pickup.service.price_per_kilo).toFixed(2)} / kg
                      </Text>
                    </View>
                  )}
                  {pickup.service.price_per_load && (
                    <View style={styles.servicePriceTag}>
                      <Ionicons name="pricetag" size={12} color={COLORS.purple} />
                      <Text style={styles.servicePriceText}>
                        ₱{parseFloat(pickup.service.price_per_load).toFixed(2)} / load
                      </Text>
                    </View>
                  )}
                  {pickup.service.turnaround_time && (
                    <View style={styles.serviceTimeTag}>
                      <Ionicons name="time-outline" size={12} color={COLORS.textMuted} />
                      <Text style={styles.serviceTimeText}>
                        {pickup.service.turnaround_time}
                      </Text>
                    </View>
                  )}
                </View>
              </View>
            </View>
          </View>
        )}

        {/* ─── Linked Laundry Order ─── */}
        {pickup.laundries_id && (
          <View style={styles.card}>
            <View style={styles.linkedHeader}>
              <Ionicons name="link" size={18} color={COLORS.primary} />
              <Text style={styles.sectionTitle}>Linked Laundry Order</Text>
            </View>
            <View style={styles.linkedBanner}>
              <View style={styles.linkedIconCircle}>
                <Ionicons name="basket" size={24} color={COLORS.primary} />
              </View>
              <View style={styles.linkedContent}>
                <Text style={styles.linkedLabel}>Your laundry order has been created</Text>
                <Text style={styles.linkedId}>Order #{pickup.laundries_id}</Text>
                {pickup.laundry && (
                  <Text style={styles.linkedStatus}>
                    Status: {pickup.laundry.status.replace('_', ' ').toUpperCase()}
                  </Text>
                )}
              </View>
            </View>
            <TouchableOpacity
              style={styles.viewLaundryBtn}
              onPress={() => router.push(`/laundries/${pickup.laundries_id}`)}
              activeOpacity={0.8}
            >
              <LinearGradient
                colors={[COLORS.primary, COLORS.primaryDark]}
                style={styles.viewLaundryGradient}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 0 }}
              >
                <Ionicons name="eye" size={18} color="#FFF" />
                <Text style={styles.viewLaundryText}>View Laundry Order</Text>
                <Ionicons name="arrow-forward" size={16} color="#FFF" />
              </LinearGradient>
            </TouchableOpacity>
          </View>
        )}

        {/* ─── Contact & Branch ─── */}
        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Details</Text>
          {pickup.phone_number && (
            <View style={styles.infoRow}>
              <View style={[styles.infoIcon, { backgroundColor: COLORS.primarySoft }]}>
                <Ionicons name="call-outline" size={16} color={COLORS.primary} />
              </View>
              <View>
                <Text style={styles.infoLabel}>Phone Number</Text>
                <Text style={styles.infoValue}>{pickup.phone_number}</Text>
              </View>
            </View>
          )}
          {pickup.branch && (
            <View style={styles.infoRow}>
              <View style={[styles.infoIcon, { backgroundColor: COLORS.primarySoft }]}>
                <Ionicons name="business-outline" size={16} color={COLORS.primary} />
              </View>
              <View>
                <Text style={styles.infoLabel}>Branch</Text>
                <Text style={styles.infoValue}>{pickup.branch.name}</Text>
                {pickup.branch.address && (
                  <Text style={styles.infoSub}>{pickup.branch.address}</Text>
                )}
              </View>
            </View>
          )}
          {pickup.estimated_weight && (
            <View style={styles.infoRow}>
              <View style={[styles.infoIcon, { backgroundColor: COLORS.primarySoft }]}>
                <Ionicons name="scale-outline" size={16} color={COLORS.primary} />
              </View>
              <View>
                <Text style={styles.infoLabel}>Estimated Weight</Text>
                <Text style={styles.infoValue}>{pickup.estimated_weight} kg</Text>
              </View>
            </View>
          )}
          {pickup.notes && (
            <View style={styles.infoRow}>
              <View style={[styles.infoIcon, { backgroundColor: COLORS.primarySoft }]}>
                <Ionicons name="document-text-outline" size={16} color={COLORS.primary} />
              </View>
              <View style={{ flex: 1 }}>
                <Text style={styles.infoLabel}>Notes</Text>
                <Text style={styles.infoValue}>{pickup.notes}</Text>
              </View>
            </View>
          )}
        </View>

        {/* ─── Cancel Button ─── */}
        {isCancellable && (
          <TouchableOpacity
            style={[styles.cancelBtn, cancelling && styles.cancelBtnDisabled]}
            onPress={handleCancel}
            disabled={cancelling}
            activeOpacity={0.8}
          >
            {cancelling ? (
              <ActivityIndicator size="small" color={COLORS.danger} />
            ) : (
              <>
                <Ionicons name="close-circle-outline" size={18} color={COLORS.danger} />
                <Text style={styles.cancelBtnText}>Cancel Pickup</Text>
              </>
            )}
          </TouchableOpacity>
        )}

        <View style={{ height: 40 }} />
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: COLORS.background },
  centered: { justifyContent: 'center', alignItems: 'center' },
  loadingText: { color: COLORS.textSecondary, marginTop: 12, fontSize: 14 },
  errorText: { color: COLORS.textSecondary, marginTop: 12, fontSize: 16, fontWeight: '600' },
  backBtn: {
    marginTop: 20, paddingHorizontal: 24, paddingVertical: 10,
    backgroundColor: COLORS.surfaceElevated, borderRadius: 12,
  },
  backBtnText: { color: COLORS.primary, fontWeight: '600' },

  // Header
  header: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingTop: Platform.OS === 'ios' ? 60 : 48,
    paddingBottom: 16,
    borderBottomWidth: 1, borderBottomColor: COLORS.borderLight,
  },
  headerBack: {
    width: 40, height: 40, borderRadius: 12,
    backgroundColor: COLORS.surfaceElevated,
    justifyContent: 'center', alignItems: 'center',
  },
  headerTitle: { fontSize: 18, fontWeight: '800', color: COLORS.textPrimary, textAlign: 'center' },
  headerSub: { fontSize: 11, color: COLORS.textMuted, textAlign: 'center', marginTop: 2 },

  scroll: { flex: 1 },
  scrollContent: { padding: 20 },

  // Status Banner
  statusBanner: {
    flexDirection: 'row', alignItems: 'center',
    padding: 16, borderRadius: 16, borderWidth: 1,
    marginBottom: 16, gap: 14,
  },
  statusIconCircle: {
    width: 52, height: 52, borderRadius: 16,
    justifyContent: 'center', alignItems: 'center',
  },
  statusBannerText: { flex: 1 },
  statusBannerLabel: { fontSize: 11, color: COLORS.textMuted, fontWeight: '600', marginBottom: 3 },
  statusBannerValue: { fontSize: 17, fontWeight: '800' },
  statusBannerSub: { fontSize: 11, color: COLORS.textSecondary, marginTop: 2 },
  activePill: {
    flexDirection: 'row', alignItems: 'center', gap: 4,
    paddingHorizontal: 8, paddingVertical: 4, borderRadius: 8,
  },
  activeDot: { width: 6, height: 6, borderRadius: 3 },
  activePillText: { fontSize: 10, fontWeight: '700' },

  // Card
  card: {
    backgroundColor: COLORS.surface, borderRadius: 16,
    padding: 16, marginBottom: 16,
    borderWidth: 1, borderColor: COLORS.borderLight,
  },
  sectionTitle: {
    fontSize: 13, fontWeight: '700', color: COLORS.textMuted,
    letterSpacing: 0.5, marginBottom: 14,
  },

  // Horizontal Timeline
  horizontalTimeline: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 8,
  },
  horizontalStep: {
    alignItems: 'center',
    flex: 1,
  },
  horizontalDot: {
    width: 40,
    height: 40,
    borderRadius: 20,
    borderWidth: 2,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
  },
  horizontalLine: {
    height: 2,
    flex: 1,
    marginHorizontal: -8,
    marginBottom: 32,
  },
  horizontalLabel: {
    fontSize: 11,
    fontWeight: '500',
    textAlign: 'center',
    lineHeight: 14,
  },

  // Address
  addressRow: { flexDirection: 'row', alignItems: 'flex-start', gap: 12 },
  addressIcon: {
    width: 36, height: 36, borderRadius: 10,
    justifyContent: 'center', alignItems: 'center', marginTop: 2,
  },
  addressContent: { flex: 1 },
  addressText: { fontSize: 14, fontWeight: '600', color: COLORS.textPrimary, lineHeight: 20 },
  returnNote: {
    flexDirection: 'row', alignItems: 'center', gap: 4,
    marginTop: 8, backgroundColor: COLORS.primarySoft,
    paddingHorizontal: 8, paddingVertical: 5,
    borderRadius: 6, alignSelf: 'flex-start',
  },
  returnNoteText: { fontSize: 10, color: COLORS.textSecondary, fontWeight: '500' },

  // Info Rows
  infoRow: {
    flexDirection: 'row', alignItems: 'flex-start', gap: 12, marginBottom: 14,
  },
  infoIcon: {
    width: 34, height: 34, borderRadius: 10,
    justifyContent: 'center', alignItems: 'center', marginTop: 2,
  },
  infoLabel: { fontSize: 11, color: COLORS.textMuted, fontWeight: '600', marginBottom: 3 },
  infoValue: { fontSize: 14, fontWeight: '600', color: COLORS.textPrimary },
  infoSub: { fontSize: 12, color: COLORS.textSecondary, marginTop: 2 },

  // Cancel
  cancelBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
    gap: 8, paddingVertical: 14, borderRadius: 14,
    borderWidth: 1.5, borderColor: COLORS.danger + '50',
    backgroundColor: COLORS.dangerGlow, marginBottom: 12,
  },
  cancelBtnDisabled: { opacity: 0.5 },
  cancelBtnText: { fontSize: 15, fontWeight: '700', color: COLORS.danger },

  // Proof Photo
  proofHeader: {
    flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 14,
  },
  proofBanner: {
    flexDirection: 'row', alignItems: 'center', gap: 10,
    backgroundColor: COLORS.pickupGlow, padding: 12, borderRadius: 12,
    borderWidth: 1, borderColor: COLORS.pickup + '30', marginBottom: 16,
  },
  proofBannerText: {
    flex: 1, fontSize: 13, fontWeight: '600', color: COLORS.pickup,
  },
  proofImageContainer: {
    borderRadius: 12, overflow: 'hidden',
    backgroundColor: COLORS.surfaceElevated,
    borderWidth: 1, borderColor: COLORS.borderLight,
    position: 'relative',
  },
  proofImage: {
    width: '100%', height: 160,
  },
  proofOverlay: {
    position: 'absolute',
    top: 0, left: 0, right: 0, bottom: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.3)',
    justifyContent: 'center', alignItems: 'center',
  },
  proofFooter: {
    flexDirection: 'row', alignItems: 'center', gap: 6,
    marginTop: 12, paddingTop: 12,
    borderTopWidth: 1, borderTopColor: COLORS.borderLight,
  },
  proofTime: {
    fontSize: 12, color: COLORS.textMuted, fontWeight: '500',
  },

  // Linked Laundry
  linkedHeader: {
    flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 14,
  },
  linkedBanner: {
    flexDirection: 'row', alignItems: 'center', gap: 14,
    backgroundColor: COLORS.primarySoft, padding: 14, borderRadius: 12,
    borderWidth: 1, borderColor: COLORS.primary + '30', marginBottom: 14,
  },
  linkedIconCircle: {
    width: 48, height: 48, borderRadius: 12,
    backgroundColor: COLORS.primary + '20',
    justifyContent: 'center', alignItems: 'center',
  },
  linkedContent: { flex: 1 },
  linkedLabel: {
    fontSize: 12, color: COLORS.textSecondary, fontWeight: '600', marginBottom: 3,
  },
  linkedId: {
    fontSize: 16, fontWeight: '800', color: COLORS.primary,
  },
  linkedStatus: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginTop: 4,
    fontWeight: '600',
  },
  viewLaundryBtn: {
    borderRadius: 12, overflow: 'hidden',
  },
  viewLaundryGradient: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
    gap: 8, paddingVertical: 14,
  },
  viewLaundryText: {
    fontSize: 15, fontWeight: '700', color: '#FFF',
  },

  // Modal
  modalContainer: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.95)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalBackdrop: {
    position: 'absolute',
    top: 0, left: 0, right: 0, bottom: 0,
  },
  modalContent: {
    width: '100%',
    height: '100%',
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 20,
  },
  modalClose: {
    position: 'absolute',
    top: Platform.OS === 'ios' ? 60 : 40,
    right: 20,
    zIndex: 10,
    width: 44, height: 44,
    borderRadius: 22,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  fullImage: {
    width: '100%',
    height: '80%',
  },

  // Service Information
  serviceHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 14,
  },
  serviceBanner: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 14,
    backgroundColor: COLORS.purpleGlow,
    padding: 14,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.purple + '30',
  },
  serviceIconCircle: {
    width: 48,
    height: 48,
    borderRadius: 12,
    backgroundColor: COLORS.purple + '20',
    justifyContent: 'center',
    alignItems: 'center',
  },
  serviceContent: {
    flex: 1,
  },
  serviceName: {
    fontSize: 16,
    fontWeight: '800',
    color: COLORS.textPrimary,
    marginBottom: 4,
  },
  serviceDescription: {
    fontSize: 13,
    color: COLORS.textSecondary,
    lineHeight: 18,
    marginBottom: 10,
  },
  servicePricing: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  servicePriceTag: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: COLORS.purple + '20',
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: COLORS.purple + '40',
  },
  servicePriceText: {
    fontSize: 12,
    fontWeight: '700',
    color: COLORS.purple,
  },
  serviceTimeTag: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: COLORS.surfaceElevated,
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 8,
  },
  serviceTimeText: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textMuted,
  },
});