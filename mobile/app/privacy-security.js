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
  TextInput,
  Switch,
  StatusBar,
  Platform,
} from 'react-native';
import { router } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { API_BASE_URL, STORAGE_KEYS, ENDPOINTS } from '../constants/config';
import { useAuth } from '../context/AuthContext';

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

export default function PrivacySecurityScreen() {
  const { logout } = useAuth();
  const [loading, setLoading] = useState(false);
  const [showPasswordModal, setShowPasswordModal] = useState(false);
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [changingPassword, setChangingPassword] = useState(false);
  const [deletingAccount, setDeletingAccount] = useState(false);
  
  // Notification preferences
  const [notificationSettings, setNotificationSettings] = useState({
    orderUpdates: true,
    promotions: true,
    pickupReminders: true,
    systemAlerts: true,
  });

  // Password form
  const [passwordForm, setPasswordForm] = useState({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
  });

  // Delete account form
  const [deleteForm, setDeleteForm] = useState({
    password: '',
    confirmation: '',
  });

  const [showPasswords, setShowPasswords] = useState({
    current: false,
    new: false,
    confirm: false,
  });

  useEffect(() => {
    loadNotificationSettings();
  }, []);

  const loadNotificationSettings = async () => {
    try {
      const settings = await AsyncStorage.getItem('notification_settings');
      if (settings) {
        setNotificationSettings(JSON.parse(settings));
      }
    } catch (error) {
      console.error('Error loading notification settings:', error);
    }
  };

  const saveNotificationSettings = async (newSettings) => {
    try {
      await AsyncStorage.setItem('notification_settings', JSON.stringify(newSettings));
      setNotificationSettings(newSettings);
      
      // TODO: Send to backend API when available
      // await updateNotificationPreferences(newSettings);
    } catch (error) {
      console.error('Error saving notification settings:', error);
      Alert.alert('Error', 'Failed to save notification settings');
    }
  };

  const handleNotificationToggle = (key) => {
    const newSettings = {
      ...notificationSettings,
      [key]: !notificationSettings[key],
    };
    saveNotificationSettings(newSettings);
  };

  const handleChangePassword = async () => {
    if (!passwordForm.current_password) {
      Alert.alert('Error', 'Please enter your current password');
      return;
    }
    if (!passwordForm.new_password) {
      Alert.alert('Error', 'Please enter a new password');
      return;
    }
    if (passwordForm.new_password.length < 8) {
      Alert.alert('Error', 'New password must be at least 8 characters long');
      return;
    }
    if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
      Alert.alert('Error', 'New passwords do not match');
      return;
    }

    try {
      setChangingPassword(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      
      const response = await fetch(`${API_BASE_URL}/v1/password`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(passwordForm),
      });

      const data = await response.json();

      if (response.ok) {
        Alert.alert('Success', 'Password changed successfully');
        setShowPasswordModal(false);
        setPasswordForm({
          current_password: '',
          new_password: '',
          new_password_confirmation: '',
        });
      } else {
        Alert.alert('Error', data.message || 'Failed to change password');
      }
    } catch (error) {
      console.error('Error changing password:', error);
      Alert.alert('Error', 'Failed to change password');
    } finally {
      setChangingPassword(false);
    }
  };

  const handleDeleteAccount = () => {
    Alert.alert(
      'Delete Account',
      'Are you sure you want to delete your account? This action cannot be undone and all your data will be permanently removed.',
      [
        { text: 'Cancel', style: 'cancel' },
        { 
          text: 'Continue', 
          style: 'destructive', 
          onPress: () => setShowDeleteModal(true)
        },
      ]
    );
  };

  const confirmDeleteAccount = async () => {
    if (!deleteForm.password) {
      Alert.alert('Error', 'Please enter your password');
      return;
    }
    if (deleteForm.confirmation !== 'DELETE_MY_ACCOUNT') {
      Alert.alert('Error', 'Please type "DELETE_MY_ACCOUNT" to confirm');
      return;
    }

    try {
      setDeletingAccount(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      
      const response = await fetch(`${API_BASE_URL}/v1/account`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(deleteForm),
      });

      const data = await response.json();

      if (response.ok) {
        // Clear all stored data using AuthContext
        await logout();
        
        // Clear additional app-specific data
        await AsyncStorage.multiRemove([
          'notification_settings',
          'saved_addresses',
          'payment_methods',
        ]);

        Alert.alert(
          'Account Deleted',
          data.message || 'Your account has been permanently deleted.',
          [
            {
              text: 'OK',
              onPress: () => {
                // Navigate to welcome/login screen
                router.replace('/welcome');
              }
            }
          ]
        );
      } else {
        Alert.alert('Error', data.message || 'Failed to delete account');
      }
    } catch (error) {
      console.error('Error deleting account:', error);
      Alert.alert('Error', 'Failed to delete account. Please check your connection and try again.');
    } finally {
      setDeletingAccount(false);
    }
  };

  const handleDataExport = () => {
    Alert.alert(
      'Export Data',
      'We will prepare your data export and send it to your registered email address within 24 hours.',
      [
        { text: 'Cancel', style: 'cancel' },
        { 
          text: 'Request Export', 
          onPress: () => {
            // TODO: Implement data export API call
            Alert.alert('Coming Soon', 'Data export will be available soon. Please contact support for assistance.');
          }
        },
      ]
    );
  };

  const securityItems = [
    {
      icon: 'key-outline',
      title: 'Change Password',
      subtitle: 'Update your account password',
      color: COLORS.primary,
      action: () => setShowPasswordModal(true),
    },
  ];

  const privacyItems = [
    {
      icon: 'download-outline',
      title: 'Export My Data',
      subtitle: 'Download a copy of your data',
      color: COLORS.accent,
      action: handleDataExport,
    },
    {
      icon: 'trash-outline',
      title: 'Delete Account',
      subtitle: 'Permanently delete your account',
      color: COLORS.danger,
      action: handleDeleteAccount,
    },
  ];

  const notificationItems = [
    {
      key: 'orderUpdates',
      title: 'Order Updates',
      subtitle: 'Notifications about your laundry status',
    },
    {
      key: 'promotions',
      title: 'Promotions & Offers',
      subtitle: 'Special deals and discounts',
    },
    {
      key: 'pickupReminders',
      title: 'Pickup Reminders',
      subtitle: 'Reminders for scheduled pickups',
    },
    {
      key: 'systemAlerts',
      title: 'System Alerts',
      subtitle: 'Important system notifications',
    },
  ];

  const renderSecurityItem = (item, index) => (
    <TouchableOpacity
      key={index}
      style={styles.menuItem}
      onPress={item.action}
      activeOpacity={0.7}
    >
      <View style={[styles.menuIconContainer, { backgroundColor: item.color + '20' }]}>
        <Ionicons name={item.icon} size={22} color={item.color} />
      </View>
      <View style={styles.menuContent}>
        <View style={styles.menuTitleRow}>
          <Text style={styles.menuTitle}>{item.title}</Text>
          {item.badge && (
            <View style={styles.comingSoonBadge}>
              <Text style={styles.comingSoonText}>{item.badge}</Text>
            </View>
          )}
        </View>
        <Text style={styles.menuSubtitle}>{item.subtitle}</Text>
      </View>
      <Ionicons name="chevron-forward" size={20} color={COLORS.textMuted} />
    </TouchableOpacity>
  );

  const renderNotificationItem = (item, index) => (
    <View key={item.key} style={styles.notificationItem}>
      <View style={styles.notificationInfo}>
        <Text style={styles.notificationTitle}>{item.title}</Text>
        <Text style={styles.notificationSubtitle}>{item.subtitle}</Text>
      </View>
      <Switch
        value={notificationSettings[item.key]}
        onValueChange={() => handleNotificationToggle(item.key)}
        trackColor={{ false: COLORS.border, true: COLORS.primary + '40' }}
        thumbColor={notificationSettings[item.key] ? COLORS.primary : COLORS.textMuted}
        ios_backgroundColor={COLORS.border}
      />
    </View>
  );

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
        <Text style={styles.headerTitle}>Privacy & Security</Text>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        {/* Security Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Security</Text>
          <View style={styles.menuCard}>
            {securityItems.map(renderSecurityItem)}
          </View>
        </View>

        {/* Notification Preferences */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Notification Preferences</Text>
          <View style={styles.menuCard}>
            {notificationItems.map(renderNotificationItem)}
          </View>
        </View>

        {/* Privacy Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Privacy</Text>
          <View style={styles.menuCard}>
            {privacyItems.map(renderSecurityItem)}
          </View>
        </View>

        {/* Info Section */}
        <View style={styles.infoSection}>
          <Text style={styles.infoTitle}>Your Privacy Matters</Text>
          <Text style={styles.infoText}>
            We take your privacy seriously. Your personal information is encrypted and stored securely. 
            We never share your data with third parties without your explicit consent.
          </Text>
        </View>
      </ScrollView>

      {/* Change Password Modal */}
      <Modal
        visible={showPasswordModal}
        animationType="slide"
        presentationStyle="pageSheet"
        onRequestClose={() => setShowPasswordModal(false)}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalHeader}>
            <TouchableOpacity
              style={styles.modalCloseButton}
              onPress={() => setShowPasswordModal(false)}
            >
              <Ionicons name="close" size={24} color={COLORS.textPrimary} />
            </TouchableOpacity>
            <Text style={styles.modalTitle}>Change Password</Text>
            <View style={{ width: 40 }} />
          </View>

          <ScrollView style={styles.modalContent}>
            {/* Current Password */}
            <Text style={styles.fieldLabel}>Current Password</Text>
            <View style={styles.passwordInputContainer}>
              <TextInput
                style={styles.passwordInput}
                value={passwordForm.current_password}
                onChangeText={(text) => setPasswordForm({ ...passwordForm, current_password: text })}
                placeholder="Enter current password"
                placeholderTextColor={COLORS.textMuted}
                secureTextEntry={!showPasswords.current}
                autoCapitalize="none"
              />
              <TouchableOpacity
                style={styles.passwordToggle}
                onPress={() => setShowPasswords({ ...showPasswords, current: !showPasswords.current })}
              >
                <Ionicons 
                  name={showPasswords.current ? 'eye-off-outline' : 'eye-outline'} 
                  size={20} 
                  color={COLORS.textMuted} 
                />
              </TouchableOpacity>
            </View>

            {/* New Password */}
            <Text style={styles.fieldLabel}>New Password</Text>
            <View style={styles.passwordInputContainer}>
              <TextInput
                style={styles.passwordInput}
                value={passwordForm.new_password}
                onChangeText={(text) => setPasswordForm({ ...passwordForm, new_password: text })}
                placeholder="Enter new password"
                placeholderTextColor={COLORS.textMuted}
                secureTextEntry={!showPasswords.new}
                autoCapitalize="none"
              />
              <TouchableOpacity
                style={styles.passwordToggle}
                onPress={() => setShowPasswords({ ...showPasswords, new: !showPasswords.new })}
              >
                <Ionicons 
                  name={showPasswords.new ? 'eye-off-outline' : 'eye-outline'} 
                  size={20} 
                  color={COLORS.textMuted} 
                />
              </TouchableOpacity>
            </View>

            {/* Confirm New Password */}
            <Text style={styles.fieldLabel}>Confirm New Password</Text>
            <View style={styles.passwordInputContainer}>
              <TextInput
                style={styles.passwordInput}
                value={passwordForm.new_password_confirmation}
                onChangeText={(text) => setPasswordForm({ ...passwordForm, new_password_confirmation: text })}
                placeholder="Confirm new password"
                placeholderTextColor={COLORS.textMuted}
                secureTextEntry={!showPasswords.confirm}
                autoCapitalize="none"
              />
              <TouchableOpacity
                style={styles.passwordToggle}
                onPress={() => setShowPasswords({ ...showPasswords, confirm: !showPasswords.confirm })}
              >
                <Ionicons 
                  name={showPasswords.confirm ? 'eye-off-outline' : 'eye-outline'} 
                  size={20} 
                  color={COLORS.textMuted} 
                />
              </TouchableOpacity>
            </View>

            {/* Password Requirements */}
            <View style={styles.requirementsContainer}>
              <Text style={styles.requirementsTitle}>Password Requirements:</Text>
              <Text style={styles.requirementText}>• At least 8 characters long</Text>
              <Text style={styles.requirementText}>• Mix of letters and numbers recommended</Text>
              <Text style={styles.requirementText}>• Avoid using personal information</Text>
            </View>

            {/* Submit Button */}
            <TouchableOpacity
              style={styles.submitButton}
              onPress={handleChangePassword}
              disabled={changingPassword}
            >
              <LinearGradient
                colors={COLORS.gradientPrimary}
                style={styles.submitGradient}
              >
                {changingPassword ? (
                  <ActivityIndicator color={COLORS.textPrimary} />
                ) : (
                  <Text style={styles.submitText}>Change Password</Text>
                )}
              </LinearGradient>
            </TouchableOpacity>
          </ScrollView>
        </View>
      </Modal>

      {/* Delete Account Modal */}
      <Modal
        visible={showDeleteModal}
        animationType="slide"
        presentationStyle="pageSheet"
        onRequestClose={() => setShowDeleteModal(false)}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalHeader}>
            <TouchableOpacity
              style={styles.modalCloseButton}
              onPress={() => setShowDeleteModal(false)}
            >
              <Ionicons name="close" size={24} color={COLORS.textPrimary} />
            </TouchableOpacity>
            <Text style={styles.modalTitle}>Delete Account</Text>
            <View style={{ width: 40 }} />
          </View>

          <ScrollView style={styles.modalContent}>
            {/* Warning */}
            <View style={styles.warningContainer}>
              <Ionicons name="warning" size={32} color={COLORS.danger} />
              <Text style={styles.warningTitle}>This action cannot be undone!</Text>
              <Text style={styles.warningText}>
                Deleting your account will permanently remove:
                {"\n"}• All your personal information
                {"\n"}• Order history and receipts
                {"\n"}• Saved addresses and payment methods
                {"\n"}• Ratings and reviews
                {"\n"}• All app preferences
              </Text>
            </View>

            {/* Password Confirmation */}
            <Text style={styles.fieldLabel}>Enter your password to confirm</Text>
            <View style={styles.passwordInputContainer}>
              <TextInput
                style={styles.passwordInput}
                value={deleteForm.password}
                onChangeText={(text) => setDeleteForm({ ...deleteForm, password: text })}
                placeholder="Enter your password"
                placeholderTextColor={COLORS.textMuted}
                secureTextEntry={true}
                autoCapitalize="none"
              />
            </View>

            {/* Confirmation Text */}
            <Text style={styles.fieldLabel}>Type &quot;DELETE_MY_ACCOUNT&quot; to confirm</Text>
            <TextInput
              style={styles.confirmationInput}
              value={deleteForm.confirmation}
              onChangeText={(text) => setDeleteForm({ ...deleteForm, confirmation: text })}
              placeholder="DELETE_MY_ACCOUNT"
              placeholderTextColor={COLORS.textMuted}
              autoCapitalize="characters"
              autoCorrect={false}
            />

            {/* Delete Button */}
            <TouchableOpacity
              style={styles.deleteButton}
              onPress={confirmDeleteAccount}
              disabled={deletingAccount || deleteForm.confirmation !== 'DELETE_MY_ACCOUNT'}
            >
              <LinearGradient
                colors={COLORS.gradientDanger}
                style={[styles.submitGradient, { opacity: deleteForm.confirmation !== 'DELETE_MY_ACCOUNT' ? 0.5 : 1 }]}
              >
                {deletingAccount ? (
                  <ActivityIndicator color={COLORS.textPrimary} />
                ) : (
                  <Text style={styles.submitText}>Delete My Account Forever</Text>
                )}
              </LinearGradient>
            </TouchableOpacity>

            {/* Cancel Button */}
            <TouchableOpacity
              style={styles.cancelButton}
              onPress={() => setShowDeleteModal(false)}
              disabled={deletingAccount}
            >
              <Text style={styles.cancelButtonText}>Cancel</Text>
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

  scrollView: { flex: 1 },
  scrollContent: { padding: 20 },

  // Sections
  section: { marginBottom: 24 },
  sectionTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.textSecondary,
    textTransform: 'uppercase',
    letterSpacing: 1,
    marginBottom: 12,
    marginLeft: 4,
  },

  // Menu Cards
  menuCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 16,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  menuIconContainer: {
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  menuContent: { flex: 1 },
  menuTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 2,
  },
  menuTitle: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.textPrimary,
  },
  menuSubtitle: {
    fontSize: 12,
    color: COLORS.textSecondary,
  },
  comingSoonBadge: {
    backgroundColor: COLORS.accent + '20',
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 6,
    marginLeft: 8,
  },
  comingSoonText: {
    fontSize: 9,
    fontWeight: '600',
    color: COLORS.accent,
    textTransform: 'uppercase',
  },

  // Notification Items
  notificationItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  notificationInfo: { flex: 1 },
  notificationTitle: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginBottom: 2,
  },
  notificationSubtitle: {
    fontSize: 12,
    color: COLORS.textSecondary,
  },

  // Info Section
  infoSection: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 16,
    padding: 20,
    borderWidth: 1,
    borderColor: COLORS.border,
    marginBottom: 20,
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
  passwordInputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.cardDark,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  passwordInput: {
    flex: 1,
    padding: 16,
    fontSize: 16,
    color: COLORS.textPrimary,
  },
  passwordToggle: {
    padding: 16,
  },

  // Requirements
  requirementsContainer: {
    backgroundColor: COLORS.cardLight,
    borderRadius: 12,
    padding: 16,
    marginTop: 16,
  },
  requirementsTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginBottom: 8,
  },
  requirementText: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginBottom: 4,
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

  // Delete Account Modal
  warningContainer: {
    backgroundColor: COLORS.danger + '10',
    borderRadius: 12,
    padding: 20,
    alignItems: 'center',
    marginBottom: 24,
    borderWidth: 1,
    borderColor: COLORS.danger + '30',
  },
  warningTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.danger,
    marginTop: 12,
    marginBottom: 8,
  },
  warningText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 20,
  },
  confirmationInput: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
    padding: 16,
    fontSize: 16,
    color: COLORS.textPrimary,
    fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace',
  },
  deleteButton: {
    marginTop: 32,
    marginBottom: 16,
    borderRadius: 12,
    overflow: 'hidden',
  },
  cancelButton: {
    backgroundColor: COLORS.cardLight,
    borderRadius: 12,
    paddingVertical: 16,
    alignItems: 'center',
    marginBottom: 20,
  },
  cancelButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },
});
