import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
  Modal,
  StatusBar,
  Platform,
} from 'react-native';
import { router } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { API_BASE_URL, STORAGE_KEYS, ENDPOINTS } from '../constants/config';

const COLORS = {
  background: '#0A0E27',
  backgroundLight: '#131937',
  cardDark: '#1C2340',
  cardLight: '#252D4C',
  primary: '#0EA5E9',
  primaryDark: '#0284C7',
  primaryLight: '#38BDF8',
  secondary: '#8B5CF6',
  accent: '#F59E0B',
  textPrimary: '#FFFFFF',
  textSecondary: '#94A3B8',
  textMuted: '#64748B',
  success: '#10B981',
  warning: '#F59E0B',
  danger: '#EF4444',
  border: '#1E293B',
  gradientPrimary: ['#0EA5E9', '#3B82F6'],
  gradientSecondary: ['#8B5CF6', '#EC4899'],
  gradientSuccess: ['#10B981', '#059669'],
  gradientDanger: ['#EF4444', '#DC2626'],
};

// Feature Integration Mappings
const FEATURE_INTEGRATIONS = [
  {
    id: 'addresses_pickup',
    title: 'Saved Addresses → Pickup Requests',
    description: 'Use your saved addresses when scheduling pickups',
    status: 'active',
    features: ['saved-addresses', 'pickup'],
    icon: 'location',
    color: COLORS.success,
    actions: [
      { label: 'Manage Addresses', route: '/saved-addresses' },
      { label: 'Schedule Pickup', route: '/(tabs)/pickup' },
    ],
  },
  {
    id: 'payment_laundry',
    title: 'Payment Methods → Laundry Details',
    description: 'Select payment methods for your laundry orders',
    status: 'pending',
    features: ['payment-methods', 'laundry'],
    icon: 'card',
    color: COLORS.primary,
    actions: [
      { label: 'Manage Payment Methods', route: '/payment-methods' },
      { label: 'View Laundries', route: '/(tabs)/laundry' },
    ],
  },
  {
    id: 'promotions_orders',
    title: 'Promotions → Laundry Discounts',
    description: 'Apply promotions and discounts to your orders',
    status: 'pending',
    features: ['promotions', 'laundry'],
    icon: 'pricetag',
    color: COLORS.accent,
    actions: [
      { label: 'View Promotions', route: '/promotions/index' },
      { label: 'View Laundries', route: '/(tabs)/laundry' },
    ],
  },
  {
    id: 'ratings_completed',
    title: 'Ratings → Completed Laundry',
    description: 'Rate your completed laundry services',
    status: 'active',
    features: ['ratings', 'laundry'],
    icon: 'star',
    color: COLORS.warning,
    actions: [
      { label: 'My Ratings', route: '/ratings/index' },
      { label: 'Rate Service', route: '/ratings/index?tab=rate_branch' },
    ],
  },
  {
    id: 'notifications_updates',
    title: 'Notifications → Laundry Updates',
    description: 'Get notified about order status changes',
    status: 'active',
    features: ['notifications', 'laundry', 'pickup'],
    icon: 'notifications',
    color: COLORS.secondary,
    actions: [
      { label: 'View Notifications', route: '/notifications' },
      { label: 'Notification Settings', route: '/privacy-security' },
    ],
  },
  {
    id: 'profile_data',
    title: 'Profile → All User Data',
    description: 'Manage your profile information across all features',
    status: 'active',
    features: ['profile', 'addresses', 'payment-methods', 'privacy'],
    icon: 'person',
    color: COLORS.primaryLight,
    actions: [
      { label: 'Edit Profile', route: '/profile/edit' },
      { label: 'Privacy Settings', route: '/privacy-security' },
    ],
  },
];

export default function FeatureIntegrationsScreen() {
  const [loading, setLoading] = useState(true);
  const [integrations, setIntegrations] = useState(FEATURE_INTEGRATIONS);
  const [selectedIntegration, setSelectedIntegration] = useState(null);
  const [showDetailModal, setShowDetailModal] = useState(false);

  useEffect(() => {
    checkIntegrationStatus();
  }, []);

  const checkIntegrationStatus = async () => {
    try {
      setLoading(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      
      if (!token) {
        setLoading(false);
        return;
      }

      // Check which integrations are working
      const updatedIntegrations = await Promise.all(
        FEATURE_INTEGRATIONS.map(async (integration) => {
          let status = 'pending';
          
          try {
            switch (integration.id) {
              case 'addresses_pickup':
                // Check if user has saved addresses
                const addressResponse = await fetch(`${API_BASE_URL}${ENDPOINTS.ADDRESSES}`, {
                  headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
                });
                if (addressResponse.ok) {
                  const addressData = await addressResponse.json();
                  status = addressData.data.addresses?.length > 0 ? 'active' : 'setup_needed';
                }
                break;
                
              case 'payment_laundry':
                // Check if user has payment methods
                const paymentResponse = await fetch(`${API_BASE_URL}${ENDPOINTS.PAYMENT_METHODS}`, {
                  headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
                });
                if (paymentResponse.ok) {
                  const paymentData = await paymentResponse.json();
                  status = paymentData.data.payment_methods?.length > 0 ? 'active' : 'setup_needed';
                }
                break;
                
              case 'ratings_completed':
                // Check if user has completed laundries to rate
                const laundryResponse = await fetch(`${API_BASE_URL}/v1/laundries`, {
                  headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
                });
                if (laundryResponse.ok) {
                  const laundryData = await laundryResponse.json();
                  const completedLaundries = laundryData.data?.laundries?.filter(l => l.status === 'completed') || [];
                  status = completedLaundries.length > 0 ? 'active' : 'no_data';
                }
                break;
                
              default:
                status = 'active';
            }
          } catch (error) {
            console.error(`Error checking ${integration.id}:`, error);
            status = 'error';
          }
          
          return { ...integration, status };
        })
      );
      
      setIntegrations(updatedIntegrations);
    } catch (error) {
      console.error('Error checking integration status:', error);
    } finally {
      setLoading(false);
    }
  };

  const getStatusInfo = (status) => {
    switch (status) {
      case 'active':
        return { icon: 'checkmark-circle', color: COLORS.success, label: 'Active' };
      case 'setup_needed':
        return { icon: 'settings', color: COLORS.warning, label: 'Setup Needed' };
      case 'no_data':
        return { icon: 'information-circle', color: COLORS.textMuted, label: 'No Data' };
      case 'error':
        return { icon: 'alert-circle', color: COLORS.danger, label: 'Error' };
      default:
        return { icon: 'time', color: COLORS.textMuted, label: 'Pending' };
    }
  };

  const handleIntegrationPress = (integration) => {
    setSelectedIntegration(integration);
    setShowDetailModal(true);
  };

  const handleActionPress = (route) => {
    setShowDetailModal(false);
    router.push(route);
  };

  const renderIntegrationCard = (integration) => {
    const statusInfo = getStatusInfo(integration.status);
    
    return (
      <TouchableOpacity
        key={integration.id}
        style={styles.integrationCard}
        onPress={() => handleIntegrationPress(integration)}
        activeOpacity={0.7}
      >
        <View style={styles.cardHeader}>
          <View style={[styles.integrationIcon, { backgroundColor: integration.color + '20' }]}>
            <Ionicons name={integration.icon} size={24} color={integration.color} />
          </View>
          <View style={styles.integrationInfo}>
            <Text style={styles.integrationTitle}>{integration.title}</Text>
            <Text style={styles.integrationDescription} numberOfLines={2}>
              {integration.description}
            </Text>
          </View>
          <View style={styles.statusContainer}>
            <View style={[styles.statusBadge, { backgroundColor: statusInfo.color + '20' }]}>
              <Ionicons name={statusInfo.icon} size={12} color={statusInfo.color} />
            </View>
            <Text style={[styles.statusText, { color: statusInfo.color }]}>
              {statusInfo.label}
            </Text>
          </View>
        </View>
        
        <View style={styles.featuresList}>
          {integration.features.map((feature, index) => (
            <View key={feature} style={styles.featureChip}>
              <Text style={styles.featureText}>{feature}</Text>
            </View>
          ))}
        </View>
      </TouchableOpacity>
    );
  };

  if (loading) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Checking integrations...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />
      
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => router.back()}
        >
          <Ionicons name="arrow-back" size={24} color={COLORS.textPrimary} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Feature Integrations</Text>
        <TouchableOpacity
          style={styles.refreshButton}
          onPress={checkIntegrationStatus}
        >
          <Ionicons name="refresh" size={20} color={COLORS.primary} />
        </TouchableOpacity>
      </View>

      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        <View style={styles.infoCard}>
          <Text style={styles.infoTitle}>Connected Features</Text>
          <Text style={styles.infoText}>
            These integrations connect different features to provide a seamless experience.
            Tap on any integration to see details and quick actions.
          </Text>
        </View>

        <View style={styles.integrationsList}>
          {integrations.map(renderIntegrationCard)}
        </View>
      </ScrollView>

      {/* Detail Modal */}
      <Modal
        visible={showDetailModal}
        animationType="slide"
        presentationStyle="pageSheet"
        onRequestClose={() => setShowDetailModal(false)}
      >
        {selectedIntegration && (
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <TouchableOpacity
                style={styles.modalCloseButton}
                onPress={() => setShowDetailModal(false)}
              >
                <Ionicons name="close" size={24} color={COLORS.textPrimary} />
              </TouchableOpacity>
              <Text style={styles.modalTitle}>{selectedIntegration.title}</Text>
              <View style={{ width: 40 }} />
            </View>

            <ScrollView style={styles.modalContent}>
              <View style={styles.modalIntegrationHeader}>
                <View style={[styles.modalIcon, { backgroundColor: selectedIntegration.color + '20' }]}>
                  <Ionicons name={selectedIntegration.icon} size={32} color={selectedIntegration.color} />
                </View>
                <Text style={styles.modalDescription}>{selectedIntegration.description}</Text>
                
                <View style={styles.modalStatus}>
                  {(() => {
                    const statusInfo = getStatusInfo(selectedIntegration.status);
                    return (
                      <View style={[styles.modalStatusBadge, { backgroundColor: statusInfo.color + '20' }]}>
                        <Ionicons name={statusInfo.icon} size={16} color={statusInfo.color} />
                        <Text style={[styles.modalStatusText, { color: statusInfo.color }]}>
                          {statusInfo.label}
                        </Text>
                      </View>
                    );
                  })()}
                </View>
              </View>

              <View style={styles.actionsSection}>
                <Text style={styles.actionsSectionTitle}>Quick Actions</Text>
                {selectedIntegration.actions.map((action, index) => (
                  <TouchableOpacity
                    key={index}
                    style={styles.actionButton}
                    onPress={() => handleActionPress(action.route)}
                    activeOpacity={0.7}
                  >
                    <LinearGradient
                      colors={[selectedIntegration.color + '10', selectedIntegration.color + '05']}
                      style={styles.actionGradient}
                    >
                      <Ionicons name="arrow-forward" size={18} color={selectedIntegration.color} />
                      <Text style={[styles.actionText, { color: selectedIntegration.color }]}>
                        {action.label}
                      </Text>
                    </LinearGradient>
                  </TouchableOpacity>
                ))}
              </View>

              <View style={styles.featuresSection}>
                <Text style={styles.featuresSectionTitle}>Connected Features</Text>
                <View style={styles.modalFeaturesList}>
                  {selectedIntegration.features.map((feature, index) => (
                    <View key={feature} style={styles.modalFeatureChip}>
                      <Text style={styles.modalFeatureText}>{feature}</Text>
                    </View>
                  ))}
                </View>
              </View>
            </ScrollView>
          </View>
        )}
      </Modal>
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
  loadingText: {
    color: COLORS.textSecondary,
    marginTop: 16,
    fontSize: 14,
    fontWeight: '600',
  },

  // Header
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: Platform.OS === 'ios' ? 60 : 48,
    paddingBottom: 16,
  },
  backButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.cardDark,
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.textPrimary,
  },
  refreshButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.cardDark,
    justifyContent: 'center',
    alignItems: 'center',
  },

  scrollView: { flex: 1 },
  scrollContent: { padding: 20 },

  // Info Card
  infoCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 16,
    padding: 20,
    marginBottom: 20,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  infoTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 8,
  },
  infoText: {
    fontSize: 13,
    color: COLORS.textSecondary,
    lineHeight: 18,
  },

  // Integrations List
  integrationsList: { gap: 16 },
  integrationCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 16,
    padding: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  cardHeader: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  integrationIcon: {
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  integrationInfo: {
    flex: 1,
  },
  integrationTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 4,
  },
  integrationDescription: {
    fontSize: 13,
    color: COLORS.textSecondary,
    lineHeight: 18,
  },
  statusContainer: {
    alignItems: 'center',
    marginLeft: 8,
  },
  statusBadge: {
    width: 24,
    height: 24,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 4,
  },
  statusText: {
    fontSize: 10,
    fontWeight: '600',
    textTransform: 'uppercase',
  },

  // Features List
  featuresList: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  featureChip: {
    backgroundColor: COLORS.cardLight,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 8,
  },
  featureText: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.textMuted,
  },

  // Modal
  modalContainer: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: Platform.OS === 'ios' ? 60 : 48,
    paddingBottom: 16,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  modalCloseButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.cardDark,
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.textPrimary,
  },
  modalContent: {
    flex: 1,
    padding: 20,
  },

  // Modal Integration Header
  modalIntegrationHeader: {
    alignItems: 'center',
    marginBottom: 32,
  },
  modalIcon: {
    width: 80,
    height: 80,
    borderRadius: 40,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
  },
  modalDescription: {
    fontSize: 16,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 16,
  },
  modalStatus: {
    alignItems: 'center',
  },
  modalStatusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 12,
  },
  modalStatusText: {
    fontSize: 12,
    fontWeight: '600',
    textTransform: 'uppercase',
  },

  // Actions Section
  actionsSection: {
    marginBottom: 32,
  },
  actionsSectionTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 16,
  },
  actionButton: {
    borderRadius: 12,
    overflow: 'hidden',
    marginBottom: 12,
  },
  actionGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    gap: 12,
  },
  actionText: {
    fontSize: 14,
    fontWeight: '600',
  },

  // Features Section
  featuresSection: {
    marginBottom: 20,
  },
  featuresSectionTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 16,
  },
  modalFeaturesList: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  modalFeatureChip: {
    backgroundColor: COLORS.cardDark,
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  modalFeatureText: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },
});