import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  ActivityIndicator,
  StatusBar,
  Platform,
  Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { router } from 'expo-router';
import { API_BASE_URL } from '../constants/config';
import ServiceDetailsModal from '../components/ServiceDetailsModal';

const COLORS = {
  background: '#06081A',
  surface: '#0F1332',
  surfaceLight: '#171D45',
  primary: '#0EA5E9',
  primarySoft: 'rgba(14, 165, 233, 0.08)',
  secondary: '#8B5CF6',
  secondaryGlow: 'rgba(139, 92, 246, 0.12)',
  success: '#10B981',
  successGlow: 'rgba(16, 185, 129, 0.12)',
  accent: '#F59E0B',
  accentGlow: 'rgba(245, 158, 11, 0.12)',
  textPrimary: '#F1F5F9',
  textSecondary: '#94A3B8',
  textMuted: '#64748B',
  border: '#1E293B',
  borderLight: 'rgba(255, 255, 255, 0.06)',
  shadow: 'rgba(0, 0, 0, 0.25)',
};

export default function ServicesScreen() {
  const [loading, setLoading] = useState(true);
  const [services, setServices] = useState([]);
  const [selectedService, setSelectedService] = useState(null);
  const [showServiceModal, setShowServiceModal] = useState(false);

  useEffect(() => {
    fetchServices();
  }, []);

  const fetchServices = async () => {
    try {
      setLoading(true);
      const response = await fetch(`${API_BASE_URL}/v1/services`, {
        headers: { 'Accept': 'application/json' },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data) {
          const servicesWithImages = data.data.map(service => ({
            ...service,
            image_url: service.image_url || service.icon_url || null
          }));
          setServices(servicesWithImages);
        }
      }
    } catch (error) {
      console.error('Error fetching services:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Loading services...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />

      <ServiceDetailsModal
        visible={showServiceModal}
        service={selectedService}
        onClose={() => setShowServiceModal(false)}
        onBookNow={(service) => {
          router.push({
            pathname: '/(tabs)/pickup',
            params: {
              serviceId: service.id,
              serviceName: service.name,
              servicePrice: service.price_per_kilo || service.price_per_load || 0,
              servicePriceType: service.price_per_kilo ? 'per_kilo' : 'per_load',
            }
          });
        }}
      />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => router.back()}
          activeOpacity={0.7}
        >
          <Ionicons name="arrow-back" size={24} color={COLORS.textPrimary} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Our Services</Text>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        {services.length === 0 ? (
          <View style={styles.emptyState}>
            <Ionicons name="shirt-outline" size={64} color={COLORS.textMuted} />
            <Text style={styles.emptyTitle}>No Services Available</Text>
            <Text style={styles.emptyText}>Check back later for available services</Text>
          </View>
        ) : (
          <View style={styles.servicesGrid}>
            {services.map((service, index) => (
              <TouchableOpacity
                key={service.id || index}
                style={styles.serviceCard}
                onPress={() => {
                  setSelectedService(service);
                  setShowServiceModal(true);
                }}
                activeOpacity={0.8}
              >
                {service.image_url && (
                  <>
                    <Image
                      source={{ uri: service.image_url }}
                      style={styles.serviceBgImage}
                      resizeMode="cover"
                    />
                    <View style={styles.serviceOverlay} />
                  </>
                )}
                
                <LinearGradient
                  colors={[
                    service.image_url ? 'rgba(6,8,26,0.7)' : (
                      index % 4 === 0 ? COLORS.primarySoft : 
                      index % 4 === 1 ? COLORS.secondaryGlow : 
                      index % 4 === 2 ? COLORS.successGlow : 
                      COLORS.accentGlow
                    ),
                    service.image_url ? 'rgba(6,8,26,0.95)' : COLORS.surface
                  ]}
                  style={styles.serviceGradient}
                >
                  <View style={[
                    styles.serviceIconContainer,
                    {
                      backgroundColor: index % 4 === 0 ? COLORS.primarySoft : 
                                     index % 4 === 1 ? COLORS.secondaryGlow : 
                                     index % 4 === 2 ? COLORS.successGlow : 
                                     COLORS.accentGlow
                    }
                  ]}>
                    <Ionicons
                      name={
                        service.name?.toLowerCase().includes('wash') ? 'water-outline' :
                        service.name?.toLowerCase().includes('dry') ? 'sunny-outline' :
                        service.name?.toLowerCase().includes('iron') ? 'flame-outline' :
                        service.name?.toLowerCase().includes('fold') ? 'layers-outline' :
                        'shirt-outline'
                      }
                      size={32}
                      color={
                        index % 4 === 0 ? COLORS.primary : 
                        index % 4 === 1 ? COLORS.secondary : 
                        index % 4 === 2 ? COLORS.success : 
                        COLORS.accent
                      }
                    />
                  </View>

                  <Text style={styles.serviceName} numberOfLines={1}>
                    {service.name}
                  </Text>
                  
                  {service.description && (
                    <Text style={styles.serviceDesc} numberOfLines={2}>
                      {service.description}
                    </Text>
                  )}

                  <View style={styles.serviceFooter}>
                    {service.price_per_kilo && (
                      <View style={styles.servicePriceTag}>
                        <Ionicons name="pricetag" size={14} color={COLORS.primary} />
                        <Text style={styles.servicePrice}>
                          ₱{parseFloat(service.price_per_kilo).toFixed(2)}/kg
                        </Text>
                      </View>
                    )}
                    {service.price_per_load && (
                      <View style={styles.servicePriceTag}>
                        <Ionicons name="pricetag" size={14} color={COLORS.primary} />
                        <Text style={styles.servicePrice}>
                          ₱{parseFloat(service.price_per_load).toFixed(2)}/load
                        </Text>
                      </View>
                    )}
                    {service.turnaround_time && (
                      <View style={styles.serviceTimeTag}>
                        <Ionicons name="time-outline" size={12} color={COLORS.textMuted} />
                        <Text style={styles.serviceTime}>
                          {service.turnaround_time}
                        </Text>
                      </View>
                    )}
                  </View>
                </LinearGradient>
              </TouchableOpacity>
            ))}
          </View>
        )}
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
  loadingText: {
    color: COLORS.textSecondary,
    marginTop: 16,
    fontSize: 16,
    fontWeight: '500',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingTop: Platform.OS === 'ios' ? 60 : 40,
    paddingBottom: 20,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  backButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.surfaceLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.textPrimary,
  },
  scrollContent: {
    padding: 20,
  },
  servicesGrid: {
    gap: 16,
  },
  serviceCard: {
    borderRadius: 20,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: COLORS.borderLight,
    shadowColor: COLORS.shadow,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 12,
    elevation: 4,
    position: 'relative',
  },
  serviceBgImage: {
    position: 'absolute',
    width: '100%',
    height: '100%',
    top: 0,
    left: 0,
  },
  serviceOverlay: {
    position: 'absolute',
    width: '100%',
    height: '100%',
    backgroundColor: 'rgba(6,8,26,0.4)',
  },
  serviceGradient: {
    padding: 20,
    minHeight: 180,
  },
  serviceIconContainer: {
    width: 64,
    height: 64,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
  },
  serviceName: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 8,
  },
  serviceDesc: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
    marginBottom: 16,
  },
  serviceFooter: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginTop: 'auto',
  },
  servicePriceTag: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: COLORS.primarySoft,
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.primary + '20',
  },
  servicePrice: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.primary,
  },
  serviceTimeTag: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: COLORS.surfaceLight,
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 12,
  },
  serviceTime: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.textMuted,
  },
  emptyState: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 80,
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
    color: COLORS.textMuted,
    textAlign: 'center',
  },
});
