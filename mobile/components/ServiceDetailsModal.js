import React from 'react';
import {
  View,
  Text,
  Modal,
  TouchableOpacity,
  Image,
  StyleSheet,
  Dimensions,
  ScrollView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

const COLORS = {
  background: '#06081A',
  surface: '#0F1332',
  surfaceLight: '#171D45',
  primary: '#0EA5E9',
  primarySoft: 'rgba(14, 165, 233, 0.08)',
  secondary: '#8B5CF6',
  success: '#10B981',
  warning: '#F59E0B',
  textPrimary: '#F1F5F9',
  textSecondary: '#94A3B8',
  textMuted: '#64748B',
  border: '#1E293B',
  borderLight: 'rgba(255, 255, 255, 0.06)',
};

export default function ServiceDetailsModal({ visible, service, onClose, onBookNow }) {
  if (!service) return null;

  const hasImage = service.image_url;

  return (
    <Modal
      visible={visible}
      transparent
      animationType="fade"
      onRequestClose={onClose}
    >
      <View style={styles.overlay}>
        <View style={styles.modalContainer}>
          <TouchableOpacity style={styles.closeButton} onPress={onClose} activeOpacity={0.8}>
            <View style={styles.closeCircle}>
              <Ionicons name="close" size={24} color="#fff" />
            </View>
          </TouchableOpacity>

          <ScrollView showsVerticalScrollIndicator={false}>
            {hasImage && (
              <View style={styles.imageContainer}>
                <Image source={{ uri: service.image_url }} style={styles.image} resizeMode="cover" />
                <LinearGradient
                  colors={['rgba(6,8,26,0.3)', 'rgba(6,8,26,0.9)']}
                  style={styles.imageGradient}
                />
              </View>
            )}

            <View style={styles.content}>
              {/* Service Name */}
              <Text style={styles.title}>{service.name}</Text>

              {/* Description */}
              {service.description && (
                <Text style={styles.description}>{service.description}</Text>
              )}

              {/* Pricing Section */}
              <View style={styles.pricingSection}>
                <Text style={styles.sectionTitle}>Pricing</Text>
                <View style={styles.pricingCards}>
                  {service.price_per_kilo && (
                    <View style={styles.priceCard}>
                      <Ionicons name="pricetag" size={20} color={COLORS.primary} />
                      <View style={styles.priceInfo}>
                        <Text style={styles.priceLabel}>Per Kilo</Text>
                        <Text style={styles.priceValue}>₱{parseFloat(service.price_per_kilo).toFixed(2)}</Text>
                      </View>
                    </View>
                  )}
                  {service.price_per_load && (
                    <View style={styles.priceCard}>
                      <Ionicons name="pricetag" size={20} color={COLORS.secondary} />
                      <View style={styles.priceInfo}>
                        <Text style={styles.priceLabel}>Per Load</Text>
                        <Text style={styles.priceValue}>₱{parseFloat(service.price_per_load).toFixed(2)}</Text>
                      </View>
                    </View>
                  )}
                </View>
              </View>

              {/* Turnaround Time */}
              {service.turnaround_time && (
                <View style={styles.infoRow}>
                  <View style={styles.infoIcon}>
                    <Ionicons name="time-outline" size={20} color={COLORS.success} />
                  </View>
                  <View style={styles.infoContent}>
                    <Text style={styles.infoLabel}>Turnaround Time</Text>
                    <Text style={styles.infoValue}>{service.turnaround_time}</Text>
                  </View>
                </View>
              )}

              {/* Service Type */}
              {service.service_type && (
                <View style={styles.infoRow}>
                  <View style={styles.infoIcon}>
                    <Ionicons name="layers-outline" size={20} color={COLORS.warning} />
                  </View>
                  <View style={styles.infoContent}>
                    <Text style={styles.infoLabel}>Service Type</Text>
                    <Text style={styles.infoValue}>{service.service_type}</Text>
                  </View>
                </View>
              )}

              {/* Additional Details */}
              {service.details && (
                <View style={styles.detailsSection}>
                  <Text style={styles.sectionTitle}>Details</Text>
                  <Text style={styles.detailsText}>{service.details}</Text>
                </View>
              )}

              {/* Book Now Button */}
              <TouchableOpacity
                style={styles.bookButton}
                onPress={() => {
                  onClose();
                  if (onBookNow) onBookNow(service);
                }}
                activeOpacity={0.85}
              >
                <LinearGradient
                  colors={[COLORS.primary, '#3B82F6']}
                  style={styles.bookButtonGradient}
                  start={{ x: 0, y: 0 }}
                  end={{ x: 1, y: 0 }}
                >
                  <Text style={styles.bookButtonText}>Book This Service</Text>
                  <Ionicons name="arrow-forward-circle" size={20} color="#fff" />
                </LinearGradient>
              </TouchableOpacity>
            </View>
          </ScrollView>
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.85)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  modalContainer: {
    width: SCREEN_WIDTH - 40,
    maxWidth: 500,
    maxHeight: '90%',
    backgroundColor: COLORS.surface,
    borderRadius: 24,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: COLORS.borderLight,
  },
  closeButton: {
    position: 'absolute',
    top: 16,
    right: 16,
    zIndex: 10,
  },
  closeCircle: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(0,0,0,0.6)',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.2)',
  },
  imageContainer: {
    width: '100%',
    height: 240,
    position: 'relative',
  },
  image: {
    width: '100%',
    height: '100%',
  },
  imageGradient: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    height: 120,
  },
  content: {
    padding: 24,
  },
  title: {
    fontSize: 26,
    fontWeight: '900',
    color: COLORS.textPrimary,
    marginBottom: 12,
    letterSpacing: -0.5,
  },
  description: {
    fontSize: 15,
    color: COLORS.textSecondary,
    lineHeight: 24,
    marginBottom: 24,
  },
  pricingSection: {
    marginBottom: 20,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 12,
  },
  pricingCards: {
    flexDirection: 'row',
    gap: 12,
  },
  priceCard: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    backgroundColor: COLORS.surfaceLight,
    padding: 16,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  priceInfo: {
    flex: 1,
  },
  priceLabel: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginBottom: 4,
    fontWeight: '600',
  },
  priceValue: {
    fontSize: 20,
    fontWeight: '900',
    color: COLORS.textPrimary,
    letterSpacing: -0.5,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    backgroundColor: COLORS.surfaceLight,
    padding: 16,
    borderRadius: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  infoIcon: {
    width: 40,
    height: 40,
    borderRadius: 12,
    backgroundColor: COLORS.surface,
    justifyContent: 'center',
    alignItems: 'center',
  },
  infoContent: {
    flex: 1,
  },
  infoLabel: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginBottom: 4,
    fontWeight: '600',
  },
  infoValue: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.textPrimary,
  },
  detailsSection: {
    marginTop: 8,
    marginBottom: 24,
  },
  detailsText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 22,
  },
  bookButton: {
    borderRadius: 16,
    overflow: 'hidden',
    marginTop: 8,
  },
  bookButtonGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 10,
    paddingVertical: 18,
  },
  bookButtonText: {
    fontSize: 16,
    fontWeight: '800',
    color: '#fff',
    letterSpacing: 0.3,
  },
});
