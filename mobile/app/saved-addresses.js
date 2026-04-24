import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
  RefreshControl,
  Modal,
  TextInput,
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

const ADDRESS_LABELS = [
  { value: 'Home', icon: 'home-outline', color: COLORS.success },
  { value: 'Office', icon: 'business-outline', color: COLORS.primary },
  { value: 'School', icon: 'school-outline', color: COLORS.secondary },
  { value: 'Other', icon: 'location-outline', color: COLORS.accent },
];

export default function SavedAddressesScreen() {
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [addresses, setAddresses] = useState([]);
  const [showAddModal, setShowAddModal] = useState(false);
  const [editingAddress, setEditingAddress] = useState(null);
  const [submitting, setSubmitting] = useState(false);

  // Form state
  const [formData, setFormData] = useState({
    label: 'Home',
    full_address: '',
    street: '',
    barangay: '',
    city: '',
    province: '',
    postal_code: '',
    contact_person: '',
    contact_phone: '',
    delivery_notes: '',
  });

  useEffect(() => {
    fetchAddresses();
  }, []);

  const fetchAddresses = async () => {
    try {
      setLoading(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      
      const response = await fetch(`${API_BASE_URL}${ENDPOINTS.ADDRESSES}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (response.ok) {
        const data = await response.json();
        setAddresses(data.data.addresses || []);
      } else if (response.status === 401) {
        router.replace('/(auth)/login');
      }
    } catch (error) {
      console.error('Error fetching addresses:', error);
      Alert.alert('Error', 'Failed to load addresses');
    } finally {
      setLoading(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchAddresses();
    setRefreshing(false);
  };

  const handleAddAddress = () => {
    setEditingAddress(null);
    setFormData({
      label: 'Home',
      full_address: '',
      street: '',
      barangay: '',
      city: '',
      province: '',
      postal_code: '',
      contact_person: '',
      contact_phone: '',
      delivery_notes: '',
    });
    setShowAddModal(true);
  };

  const handleEditAddress = (address) => {
    setEditingAddress(address);
    setFormData({
      label: address.label,
      full_address: address.full_address,
      street: address.street || '',
      barangay: address.barangay || '',
      city: address.city,
      province: address.province,
      postal_code: address.postal_code || '',
      contact_person: address.contact_person || '',
      contact_phone: address.contact_phone || '',
      delivery_notes: address.delivery_notes || '',
    });
    setShowAddModal(true);
  };

  const handleSubmit = async () => {
    if (!formData.full_address.trim()) {
      Alert.alert('Error', 'Please enter the full address');
      return;
    }
    if (!formData.city.trim()) {
      Alert.alert('Error', 'Please enter the city');
      return;
    }
    if (!formData.province.trim()) {
      Alert.alert('Error', 'Please enter the province');
      return;
    }

    try {
      setSubmitting(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      
      const url = editingAddress 
        ? `${API_BASE_URL}${ENDPOINTS.ADDRESSES}/${editingAddress.id}`
        : `${API_BASE_URL}${ENDPOINTS.ADDRESSES}`;
      
      const method = editingAddress ? 'PUT' : 'POST';

      const response = await fetch(url, {
        method,
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (response.ok) {
        Alert.alert('Success', data.message);
        setShowAddModal(false);
        await fetchAddresses();
      } else {
        Alert.alert('Error', data.message || 'Failed to save address');
      }
    } catch (error) {
      console.error('Error saving address:', error);
      Alert.alert('Error', 'Failed to save address');
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = (address) => {
    Alert.alert(
      'Delete Address',
      `Are you sure you want to delete "${address.label}"?`,
      [
        { text: 'Cancel', style: 'cancel' },
        { 
          text: 'Delete', 
          style: 'destructive', 
          onPress: () => deleteAddress(address.id) 
        },
      ]
    );
  };

  const deleteAddress = async (id) => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      
      const response = await fetch(`${API_BASE_URL}${ENDPOINTS.ADDRESSES}/${id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      const data = await response.json();

      if (response.ok) {
        Alert.alert('Success', data.message);
        await fetchAddresses();
      } else {
        Alert.alert('Error', data.message || 'Failed to delete address');
      }
    } catch (error) {
      console.error('Error deleting address:', error);
      Alert.alert('Error', 'Failed to delete address');
    }
  };

  const handleSetDefault = async (id) => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      
      const response = await fetch(`${API_BASE_URL}${ENDPOINTS.ADDRESSES}/${id}/set-default`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      const data = await response.json();

      if (response.ok) {
        await fetchAddresses();
      } else {
        Alert.alert('Error', data.message || 'Failed to set default address');
      }
    } catch (error) {
      console.error('Error setting default:', error);
      Alert.alert('Error', 'Failed to set default address');
    }
  };

  const getAddressLabelConfig = (label) => {
    return ADDRESS_LABELS.find(l => l.value === label) || ADDRESS_LABELS[3];
  };

  const renderAddress = (address, index) => {
    const config = getAddressLabelConfig(address.label);
    
    return (
      <View key={address.id} style={styles.addressCard}>
        <View style={styles.addressHeader}>
          <View style={[styles.addressIcon, { backgroundColor: config.color + '20' }]}>
            <Ionicons name={config.icon} size={24} color={config.color} />
          </View>
          <View style={styles.addressInfo}>
            <View style={styles.addressTitleRow}>
              <Text style={styles.addressLabel}>{address.label}</Text>
              {address.is_default && (
                <View style={styles.defaultBadge}>
                  <Text style={styles.defaultText}>Default</Text>
                </View>
              )}
            </View>
            <Text style={styles.addressText} numberOfLines={2}>
              {address.full_address}
            </Text>
            <Text style={styles.addressLocation}>
              {address.city}, {address.province}
            </Text>
            {address.contact_person && (
              <Text style={styles.addressContact}>
                Contact: {address.contact_person}
                {address.contact_phone && ` • ${address.contact_phone}`}
              </Text>
            )}
          </View>
        </View>
        
        <View style={styles.addressActions}>
          {!address.is_default && (
            <TouchableOpacity
              style={styles.actionButton}
              onPress={() => handleSetDefault(address.id)}
            >
              <Ionicons name="star-outline" size={18} color={COLORS.textMuted} />
              <Text style={styles.actionText}>Set Default</Text>
            </TouchableOpacity>
          )}
          <TouchableOpacity
            style={styles.actionButton}
            onPress={() => handleEditAddress(address)}
          >
            <Ionicons name="pencil-outline" size={18} color={COLORS.textMuted} />
            <Text style={styles.actionText}>Edit</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={styles.actionButton}
            onPress={() => handleDelete(address)}
          >
            <Ionicons name="trash-outline" size={18} color={COLORS.danger} />
            <Text style={[styles.actionText, { color: COLORS.danger }]}>Delete</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  };

  if (loading) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Loading addresses...</Text>
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
        <Text style={styles.headerTitle}>Saved Addresses</Text>
        <TouchableOpacity
          style={styles.addButton}
          onPress={handleAddAddress}
        >
          <Ionicons name="add" size={24} color={COLORS.textPrimary} />
        </TouchableOpacity>
      </View>

      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor={COLORS.primary}
            colors={[COLORS.primary]}
          />
        }
        contentContainerStyle={styles.scrollContent}
      >
        {addresses.length > 0 ? (
          <View style={styles.addressesList}>
            {addresses.map(renderAddress)}
          </View>
        ) : (
          <View style={styles.emptyState}>
            <Ionicons name="location-outline" size={64} color={COLORS.textMuted} />
            <Text style={styles.emptyTitle}>No Saved Addresses</Text>
            <Text style={styles.emptyText}>
              Add your delivery addresses to make pickup requests easier
            </Text>
            <TouchableOpacity
              style={styles.emptyButton}
              onPress={handleAddAddress}
            >
              <LinearGradient
                colors={COLORS.gradientPrimary}
                style={styles.emptyButtonGradient}
              >
                <Ionicons name="add" size={20} color={COLORS.textPrimary} />
                <Text style={styles.emptyButtonText}>Add Address</Text>
              </LinearGradient>
            </TouchableOpacity>
          </View>
        )}
      </ScrollView>

      {/* Add/Edit Modal */}
      <Modal
        visible={showAddModal}
        animationType="slide"
        presentationStyle="pageSheet"
        onRequestClose={() => setShowAddModal(false)}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalHeader}>
            <TouchableOpacity
              style={styles.modalCloseButton}
              onPress={() => setShowAddModal(false)}
            >
              <Ionicons name="close" size={24} color={COLORS.textPrimary} />
            </TouchableOpacity>
            <Text style={styles.modalTitle}>
              {editingAddress ? 'Edit Address' : 'Add Address'}
            </Text>
            <View style={{ width: 40 }} />
          </View>

          <ScrollView style={styles.modalContent}>
            {/* Address Label Selection */}
            <Text style={styles.fieldLabel}>Label</Text>
            <View style={styles.labelGrid}>
              {ADDRESS_LABELS.map((label) => (
                <TouchableOpacity
                  key={label.value}
                  style={[
                    styles.labelCard,
                    formData.label === label.value && styles.labelCardSelected,
                  ]}
                  onPress={() => setFormData({ ...formData, label: label.value })}
                >
                  <Ionicons 
                    name={label.icon} 
                    size={20} 
                    color={formData.label === label.value ? label.color : COLORS.textMuted} 
                  />
                  <Text style={[
                    styles.labelText,
                    formData.label === label.value && { color: label.color }
                  ]}>
                    {label.value}
                  </Text>
                </TouchableOpacity>
              ))}
            </View>

            {/* Full Address */}
            <Text style={styles.fieldLabel}>Full Address *</Text>
            <TextInput
              style={[styles.textInput, styles.textArea]}
              value={formData.full_address}
              onChangeText={(text) => setFormData({ ...formData, full_address: text })}
              placeholder="Enter complete address"
              placeholderTextColor={COLORS.textMuted}
              multiline
              numberOfLines={3}
            />

            {/* Street */}
            <Text style={styles.fieldLabel}>Street</Text>
            <TextInput
              style={styles.textInput}
              value={formData.street}
              onChangeText={(text) => setFormData({ ...formData, street: text })}
              placeholder="Street name/number"
              placeholderTextColor={COLORS.textMuted}
            />

            {/* Barangay */}
            <Text style={styles.fieldLabel}>Barangay</Text>
            <TextInput
              style={styles.textInput}
              value={formData.barangay}
              onChangeText={(text) => setFormData({ ...formData, barangay: text })}
              placeholder="Barangay"
              placeholderTextColor={COLORS.textMuted}
            />

            {/* City & Province Row */}
            <View style={styles.rowFields}>
              <View style={styles.halfField}>
                <Text style={styles.fieldLabel}>City *</Text>
                <TextInput
                  style={styles.textInput}
                  value={formData.city}
                  onChangeText={(text) => setFormData({ ...formData, city: text })}
                  placeholder="City"
                  placeholderTextColor={COLORS.textMuted}
                />
              </View>
              <View style={styles.halfField}>
                <Text style={styles.fieldLabel}>Province *</Text>
                <TextInput
                  style={styles.textInput}
                  value={formData.province}
                  onChangeText={(text) => setFormData({ ...formData, province: text })}
                  placeholder="Province"
                  placeholderTextColor={COLORS.textMuted}
                />
              </View>
            </View>

            {/* Postal Code */}
            <Text style={styles.fieldLabel}>Postal Code</Text>
            <TextInput
              style={styles.textInput}
              value={formData.postal_code}
              onChangeText={(text) => setFormData({ ...formData, postal_code: text })}
              placeholder="Postal code"
              placeholderTextColor={COLORS.textMuted}
              keyboardType="numeric"
            />

            {/* Contact Person & Phone Row */}
            <View style={styles.rowFields}>
              <View style={styles.halfField}>
                <Text style={styles.fieldLabel}>Contact Person</Text>
                <TextInput
                  style={styles.textInput}
                  value={formData.contact_person}
                  onChangeText={(text) => setFormData({ ...formData, contact_person: text })}
                  placeholder="Contact name"
                  placeholderTextColor={COLORS.textMuted}
                />
              </View>
              <View style={styles.halfField}>
                <Text style={styles.fieldLabel}>Contact Phone</Text>
                <TextInput
                  style={styles.textInput}
                  value={formData.contact_phone}
                  onChangeText={(text) => setFormData({ ...formData, contact_phone: text })}
                  placeholder="Phone number"
                  placeholderTextColor={COLORS.textMuted}
                  keyboardType="phone-pad"
                />
              </View>
            </View>

            {/* Delivery Notes */}
            <Text style={styles.fieldLabel}>Delivery Notes</Text>
            <TextInput
              style={[styles.textInput, styles.textArea]}
              value={formData.delivery_notes}
              onChangeText={(text) => setFormData({ ...formData, delivery_notes: text })}
              placeholder="Special instructions for delivery (optional)"
              placeholderTextColor={COLORS.textMuted}
              multiline
              numberOfLines={2}
            />

            {/* Submit Button */}
            <TouchableOpacity
              style={styles.submitButton}
              onPress={handleSubmit}
              disabled={submitting}
            >
              <LinearGradient
                colors={COLORS.gradientPrimary}
                style={styles.submitGradient}
              >
                {submitting ? (
                  <ActivityIndicator color={COLORS.textPrimary} />
                ) : (
                  <Text style={styles.submitText}>
                    {editingAddress ? 'Update Address' : 'Add Address'}
                  </Text>
                )}
              </LinearGradient>
            </TouchableOpacity>
          </ScrollView>
        </View>
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
  addButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.primary,
    justifyContent: 'center',
    alignItems: 'center',
  },

  scrollView: { flex: 1 },
  scrollContent: { padding: 20 },

  // Addresses List
  addressesList: { gap: 16 },
  addressCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 16,
    padding: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  addressHeader: {
    flexDirection: 'row',
    marginBottom: 12,
  },
  addressIcon: {
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
  },
  addressInfo: {
    flex: 1,
    marginLeft: 12,
  },
  addressTitleRow: {
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
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 8,
    marginLeft: 8,
  },
  defaultText: {
    fontSize: 10,
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
    fontSize: 13,
    color: COLORS.textMuted,
    marginBottom: 4,
  },
  addressContact: {
    fontSize: 12,
    color: COLORS.textMuted,
  },
  addressActions: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
  },
  actionButton: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 8,
    paddingHorizontal: 12,
    gap: 6,
  },
  actionText: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textMuted,
  },

  // Empty State
  emptyState: {
    alignItems: 'center',
    paddingVertical: 60,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginTop: 16,
    marginBottom: 8,
  },
  emptyText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: 24,
  },
  emptyButton: {
    borderRadius: 12,
    overflow: 'hidden',
  },
  emptyButtonGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 24,
    paddingVertical: 12,
    gap: 8,
  },
  emptyButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.textPrimary,
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

  // Form Fields
  fieldLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginBottom: 8,
    marginTop: 16,
  },
  labelGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
    marginBottom: 8,
  },
  labelCard: {
    flex: 1,
    minWidth: '22%',
    backgroundColor: COLORS.cardDark,
    borderRadius: 12,
    padding: 12,
    alignItems: 'center',
    borderWidth: 2,
    borderColor: 'transparent',
  },
  labelCardSelected: {
    borderColor: COLORS.primary,
    backgroundColor: COLORS.primary + '10',
  },
  labelText: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.textSecondary,
    marginTop: 4,
  },
  textInput: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 12,
    padding: 16,
    fontSize: 16,
    color: COLORS.textPrimary,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  textArea: {
    height: 80,
    textAlignVertical: 'top',
  },
  rowFields: {
    flexDirection: 'row',
    gap: 12,
  },
  halfField: {
    flex: 1,
  },

  // Submit Button
  submitButton: {
    marginTop: 32,
    marginBottom: 20,
    borderRadius: 12,
    overflow: 'hidden',
  },
  submitGradient: {
    paddingVertical: 16,
    alignItems: 'center',
  },
  submitText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.textPrimary,
  },
});