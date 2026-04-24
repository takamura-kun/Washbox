import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
  Share,
  Platform,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router, useLocalSearchParams } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_BASE_URL, STORAGE_KEYS } from '../../constants/config';

const COLORS = {
  background: '#0A0E27',
  cardDark: '#1C2340',
  primary: '#0EA5E9',
  textPrimary: '#FFFFFF',
  textSecondary: '#94A3B8',
  success: '#10B981',
  warning: '#F59E0B',
  border: '#1E293B',
};

export default function ReceiptScreen() {
  const { id } = useLocalSearchParams();
  const [loading, setLoading] = useState(true);
  const [receipt, setReceipt] = useState(null);
  const [downloading, setDownloading] = useState(false);

  useEffect(() => {
    fetchReceipt();
  }, [id]);

  const fetchReceipt = async () => {
    try {
      setLoading(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      
      const response = await fetch(`${API_BASE_URL}/v1/laundries/${id}/receipt`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          setReceipt(data.data.receipt);
        }
      } else {
        Alert.alert('Error', 'Failed to load receipt');
      }
    } catch (error) {
      Alert.alert('Error', 'Unable to connect to server');
    } finally {
      setLoading(false);
    }
  };

  const shareReceipt = async () => {
    if (!receipt) return;
    
    try {
      setDownloading(true);

      // Create detailed receipt text
      const receiptText = `
🧺 WASHBOX RECEIPT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📋 RECEIPT #${receipt.tracking_number}
📅 Date: ${new Date(receipt.timeline.created_at).toLocaleDateString()}

👤 CUSTOMER INFORMATION
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Name: ${receipt.customer.name}${receipt.customer.phone ? `\nPhone: ${receipt.customer.phone}` : ''}

💰 BILL DETAILS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
${receipt.service.name}: ₱${receipt.pricing.subtotal.toFixed(2)}${receipt.addons && receipt.addons.length > 0 ? receipt.addons.map(addon => `\n${addon.name} (x${addon.quantity}): ₱${addon.total.toFixed(2)}`).join('') : ''}${receipt.pricing.pickup_fee > 0 ? `\nPickup Fee: ₱${receipt.pricing.pickup_fee.toFixed(2)}` : ''}${receipt.pricing.delivery_fee > 0 ? `\nDelivery Fee: ₱${receipt.pricing.delivery_fee.toFixed(2)}` : ''}${receipt.pricing.discount_amount > 0 ? `\nDiscount: -₱${receipt.pricing.discount_amount.toFixed(2)}` : ''}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
TOTAL AMOUNT: ₱${receipt.pricing.total_amount.toFixed(2)}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

💳 PAYMENT STATUS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Status: ${receipt.payment_status === 'paid' ? '✅ PAID' : '⏳ UNPAID'}${receipt.payment_method ? `\nMethod: ${receipt.payment_method.toUpperCase()}` : ''}${receipt.paid_at ? `\nPaid At: ${new Date(receipt.paid_at).toLocaleDateString()}` : ''}

📊 ORDER STATUS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Status: ${receipt.status.toUpperCase()}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🫧 Thank you for choosing WashBox!
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
      `;

      // Share the receipt text
      await Share.share({
        message: receiptText,
        title: `WashBox Receipt #${receipt.tracking_number}`,
      });

    } catch (error) {
      console.error('Error sharing receipt:', error);
      Alert.alert(
        'Share Failed',
        'Failed to share receipt. Please try again.',
        [{ text: 'OK' }]
      );
    } finally {
      setDownloading(false);
    }
  };

  if (loading) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Loading receipt...</Text>
      </View>
    );
  }

  if (!receipt) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <Ionicons name="receipt-outline" size={64} color={COLORS.textSecondary} />
        <Text style={styles.errorText}>Receipt not found</Text>
        <TouchableOpacity style={styles.backButton} onPress={() => router.back()}>
          <Text style={styles.backButtonText}>Go Back</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.headerButton}>
          <Ionicons name="arrow-back" size={24} color={COLORS.textPrimary} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Receipt</Text>
        <TouchableOpacity 
          onPress={shareReceipt} 
          style={[styles.headerButton, downloading && styles.headerButtonDisabled]}
          disabled={downloading}
        >
          {downloading ? (
            <ActivityIndicator size="small" color={COLORS.primary} />
          ) : (
            <Ionicons name="share-outline" size={24} color={COLORS.primary} />
          )}
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.scrollView}>
        <View style={styles.receiptCard}>
          <View style={styles.receiptHeader}>
            <Text style={styles.receiptTitle}>RECEIPT</Text>
            <Text style={styles.receiptSubtitle}>WashBox Laundry Service</Text>
            <Text style={styles.trackingNumber}>#{receipt.tracking_number}</Text>
          </View>

          <View style={styles.section}>
            <Text style={styles.sectionTitle}>CUSTOMER</Text>
            <Text style={styles.customerName}>{receipt.customer.name}</Text>
            {receipt.customer.phone && (
              <Text style={styles.customerDetail}>{receipt.customer.phone}</Text>
            )}
          </View>

          <View style={styles.section}>
            <Text style={styles.sectionTitle}>BILL DETAILS</Text>
            <View style={styles.billRow}>
              <Text style={styles.billLabel}>{receipt.service.name}</Text>
              <Text style={styles.billAmount}>₱{receipt.pricing.subtotal.toFixed(2)}</Text>
            </View>
            {receipt.addons && receipt.addons.length > 0 && receipt.addons.map((addon, index) => (
              <View key={index} style={styles.billRow}>
                <Text style={styles.billLabel}>{addon.name} (x{addon.quantity})</Text>
                <Text style={styles.billAmount}>₱{addon.total.toFixed(2)}</Text>
              </View>
            ))}
            {receipt.pricing.pickup_fee > 0 && (
              <View style={styles.billRow}>
                <Text style={styles.billLabel}>Pickup Fee</Text>
                <Text style={styles.billAmount}>₱{receipt.pricing.pickup_fee.toFixed(2)}</Text>
              </View>
            )}
            {receipt.pricing.delivery_fee > 0 && (
              <View style={styles.billRow}>
                <Text style={styles.billLabel}>Delivery Fee</Text>
                <Text style={styles.billAmount}>₱{receipt.pricing.delivery_fee.toFixed(2)}</Text>
              </View>
            )}
            {receipt.pricing.discount_amount > 0 && (
              <View style={styles.billRow}>
                <Text style={[styles.billLabel, { color: COLORS.success }]}>Discount</Text>
                <Text style={[styles.billAmount, { color: COLORS.success }]}>-₱{receipt.pricing.discount_amount.toFixed(2)}</Text>
              </View>
            )}
          </View>

          <View style={styles.totalSection}>
            <View style={styles.totalRow}>
              <Text style={styles.totalLabel}>TOTAL AMOUNT</Text>
              <Text style={styles.totalAmount}>₱{receipt.pricing.total_amount.toFixed(2)}</Text>
            </View>
          </View>

          <View style={styles.paymentSection}>
            <Text style={styles.sectionTitle}>PAYMENT STATUS</Text>
            <View style={styles.paymentRow}>
              <Text style={styles.paymentLabel}>Status:</Text>
              <View style={[styles.paymentBadge, { backgroundColor: receipt.payment_status === 'paid' ? COLORS.success : COLORS.warning }]}>
                <Text style={styles.paymentBadgeText}>
                  {receipt.payment_status === 'paid' ? '✅ PAID' : '⏳ UNPAID'}
                </Text>
              </View>
            </View>
            {receipt.payment_method && (
              <View style={styles.paymentRow}>
                <Text style={styles.paymentLabel}>Method:</Text>
                <Text style={styles.paymentValue}>{receipt.payment_method.toUpperCase()}</Text>
              </View>
            )}
            {receipt.paid_at && (
              <View style={styles.paymentRow}>
                <Text style={styles.paymentLabel}>Paid At:</Text>
                <Text style={styles.paymentValue}>{new Date(receipt.paid_at).toLocaleDateString()}</Text>
              </View>
            )}
          </View>

          <View style={styles.statusSection}>
            <View style={[styles.statusBadge, { backgroundColor: getStatusColor(receipt.status) }]}>
              <Text style={styles.statusText}>{receipt.status.toUpperCase()}</Text>
            </View>
          </View>

          <View style={styles.footer}>
            <Text style={styles.footerText}>Thank you for choosing WashBox!</Text>
            <Text style={styles.footerDate}>
              {new Date(receipt.timeline.created_at).toLocaleDateString()}
            </Text>
          </View>
        </View>
      </ScrollView>
    </View>
  );
}

const getStatusColor = (status) => {
  const colors = {
    received: COLORS.primary,
    processing: COLORS.warning,
    ready: COLORS.success,
    completed: COLORS.success,
    cancelled: '#EF4444',
  };
  return colors[status?.toLowerCase()] || COLORS.textSecondary;
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  centerContent: {
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  loadingText: {
    color: COLORS.textSecondary,
    marginTop: 12,
    fontSize: 14,
  },
  errorText: {
    color: COLORS.textSecondary,
    fontSize: 16,
    marginTop: 12,
    textAlign: 'center',
  },
  backButton: {
    marginTop: 20,
    paddingHorizontal: 20,
    paddingVertical: 12,
    backgroundColor: COLORS.primary,
    borderRadius: 8,
  },
  backButtonText: {
    color: COLORS.textPrimary,
    fontWeight: '600',
    fontSize: 14,
  },
  // Header Styles
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingTop: Platform.OS === 'ios' ? 50 : 40,
    paddingBottom: 20,
    backgroundColor: COLORS.background,
  },
  headerButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.cardDark,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  headerButtonDisabled: {
    opacity: 0.5,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: COLORS.textPrimary,
    textAlign: 'center',
  },
  // Content Styles
  scrollView: {
    flex: 1,
    paddingHorizontal: 20,
  },
  receiptCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 16,
    padding: 24,
    marginBottom: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 8,
    elevation: 5,
  },
  // Receipt Header
  receiptHeader: {
    alignItems: 'center',
    paddingBottom: 20,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    marginBottom: 24,
  },
  receiptTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: COLORS.textPrimary,
    letterSpacing: 1,
  },
  receiptSubtitle: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginTop: 4,
  },
  trackingNumber: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.primary,
    marginTop: 8,
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
  },
  // Section Styles
  section: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textSecondary,
    marginBottom: 12,
    letterSpacing: 1.5,
    textTransform: 'uppercase',
  },
  // Customer Styles
  customerName: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginBottom: 4,
  },
  customerDetail: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginTop: 2,
  },
  // Bill Details
  billRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
    paddingVertical: 2,
  },
  billLabel: {
    fontSize: 14,
    color: COLORS.textPrimary,
    flex: 1,
    marginRight: 12,
  },
  billAmount: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.textPrimary,
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
  },
  // Total Section
  totalSection: {
    paddingTop: 16,
    borderTopWidth: 2,
    borderTopColor: COLORS.border,
    marginBottom: 24,
    backgroundColor: COLORS.background + '20',
    marginHorizontal: -12,
    paddingHorizontal: 12,
    borderRadius: 8,
  },
  totalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 8,
  },
  totalLabel: {
    fontSize: 18,
    fontWeight: 'bold',
    color: COLORS.textPrimary,
    letterSpacing: 0.5,
  },
  totalAmount: {
    fontSize: 24,
    fontWeight: 'bold',
    color: COLORS.primary,
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
  },
  // Payment Section
  paymentSection: {
    marginBottom: 24,
    paddingTop: 16,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
  },
  paymentRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  paymentLabel: {
    fontSize: 14,
    color: COLORS.textSecondary,
    fontWeight: '600',
  },
  paymentValue: {
    fontSize: 14,
    color: COLORS.textPrimary,
    fontWeight: '600',
  },
  paymentBadge: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  paymentBadgeText: {
    fontSize: 12,
    fontWeight: 'bold',
    color: COLORS.textPrimary,
  },
  // Status Section
  statusSection: {
    alignItems: 'center',
    marginBottom: 24,
  },
  statusBadge: {
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  statusText: {
    fontSize: 12,
    fontWeight: 'bold',
    color: COLORS.textPrimary,
    letterSpacing: 1,
  },
  // Footer
  footer: {
    alignItems: 'center',
    paddingTop: 20,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
  },
  footerText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.textPrimary,
    textAlign: 'center',
  },
  footerDate: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginTop: 6,
    textAlign: 'center',
  },
});