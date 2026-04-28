import React, { useState, useEffect, useCallback, useRef } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  Dimensions,
  StatusBar,
  Platform,
  Animated,
  Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { router } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_BASE_URL, STORAGE_KEYS } from '../../constants/config';
import PromotionModal from '../../components/PromotionModal';
import ServiceDetailsModal from '../../components/ServiceDetailsModal';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

// ─────────────────────────────────────────────
// PROFESSIONAL DESIGN SYSTEM
// ─────────────────────────────────────────────
const COLORS = {
  // Base - Dark theme
  background: '#06081A',
  surface: '#0F1332',
  surfaceLight: '#171D45',
  surfaceElevated: '#1E2654',

  // Brand - Professional blue palette
  primary: '#0EA5E9',
  primaryDark: '#0284C7',
  primaryLight: '#3B82F6',
  primarySoft: 'rgba(14, 165, 233, 0.08)',
  primaryGlow: 'rgba(14, 165, 233, 0.12)',

  // Accents - Refined color palette
  secondary: '#8B5CF6',
  secondaryLight: '#7C3AED',
  secondaryGlow: 'rgba(139, 92, 246, 0.12)',
  success: '#10B981',
  successLight: '#059669',
  successGlow: 'rgba(16, 185, 129, 0.12)',
  warning: '#F59E0B',
  warningLight: '#D97706',
  danger: '#EF4444',
  dangerLight: '#DC2626',
  info: '#0891B2',
  accent: '#F59E0B',
  accentGlow: 'rgba(245, 158, 11, 0.12)',
  pink: '#EC4899',

  // Text - High contrast for readability
  textPrimary: '#F1F5F9',
  textSecondary: '#94A3B8',
  textMuted: '#64748B',
  textLight: '#CBD5E1',

  // Borders - Subtle and clean
  border: '#1E293B',
  borderLight: 'rgba(255, 255, 255, 0.06)',
  borderMuted: '#334155',

  // Shadows
  shadow: 'rgba(0, 0, 0, 0.25)',
  shadowMedium: 'rgba(0, 0, 0, 0.35)',
  shadowStrong: 'rgba(0, 0, 0, 0.45)',

  // Gradients - Subtle and professional
  gradientPrimary: ['#0EA5E9', '#3B82F6'],
  gradientSecondary: ['#8B5CF6', '#7C3AED'],
  gradientSuccess: ['#10B981', '#059669'],
  gradientWarning: ['#F59E0B', '#F97316'],
  gradientSurface: ['#0F1332', '#171D45'],
};

// ─────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────
const getGreeting = () => {
  const hour = new Date().getHours();
  if (hour < 12) return 'Good Morning';
  if (hour < 17) return 'Good Afternoon';
  return 'Good Evening';
};

const getStatusColor = (status) => {
  const s = status?.toLowerCase();
  const map = {
    received: COLORS.primary,
    processing: COLORS.secondary,
    washing: COLORS.secondary,
    drying: COLORS.secondary,
    ironing: COLORS.accent,
    folding: COLORS.accent,
    ready: COLORS.success,
    ready_for_pickup: COLORS.success,
    completed: COLORS.success,
    cancelled: COLORS.danger,
    paid: COLORS.success,
  };
  return map[s] || COLORS.textMuted;
};

const getStatusIcon = (status) => {
  const s = status?.toLowerCase();
  const map = {
    received: 'receipt-outline',
    processing: 'sync-outline',
    washing: 'water-outline',
    drying: 'sunny-outline',
    ironing: 'shirt-outline',
    folding: 'layers-outline',
    ready: 'checkmark-circle-outline',
    ready_for_pickup: 'bag-check-outline',
    completed: 'checkmark-done-outline',
    cancelled: 'close-circle-outline',
    paid: 'card-outline',
  };
  return map[s] || 'ellipse-outline';
};

const formatStatus = (status) => {
  if (!status) return 'Unknown';
  return status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
};

// ─────────────────────────────────────────────
// MAIN COMPONENT
// ─────────────────────────────────────────────
export default function HomeScreen() {
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  // Data
  const [customer, setCustomer] = useState(null);
  const [laundries, setLaundries] = useState([]);
  const [services, setServices] = useState([]);
  const [selectedService, setSelectedService] = useState(null);
  const [showServiceModal, setShowServiceModal] = useState(false);
  const [promotions, setPromotions] = useState([]);
  const [showPromo, setShowPromo] = useState(false);
  const [activePromo, setActivePromo] = useState(null);
  const [unreadCount, setUnreadCount] = useState(0);
  const [stats, setStats] = useState({
    totalLaundries: 0,
    totalSpent: 0,
    pendingLaundries: 0,
    activePickups: 0,
  });

  // Animations
  const [fadeAnim] = useState(new Animated.Value(0));
  const [slideAnim] = useState(new Animated.Value(40));
  const serviceScrollRef = useRef(null);
  const serviceScrollIndex = useRef(0);
  const promoScrollRef = useRef(null);

  useEffect(() => {
    fetchAllData();
  }, []);


  useEffect(() => {
    if (!loading) {
      Animated.parallel([
        Animated.timing(fadeAnim, { toValue: 1, duration: 500, useNativeDriver: true }),
        Animated.spring(slideAnim, { toValue: 0, useNativeDriver: true, tension: 60, friction: 12 }),
      ]).start();
    }
  }, [loading]);

  // Auto-slide for Our Services
  const [serviceActiveIndex, setServiceActiveIndex] = useState(0);
  const SERVICE_CARD_WIDTH = 260 + 12; // card width + gap

  useEffect(() => {
    if (services.length <= 1) return;
    const interval = setInterval(() => {
      const nextIndex = (serviceScrollIndex.current + 1) % services.length;
      serviceScrollRef.current?.scrollTo({ x: nextIndex * SERVICE_CARD_WIDTH, animated: true });
      serviceScrollIndex.current = nextIndex;
      setServiceActiveIndex(nextIndex);
    }, 3000);
    return () => clearInterval(interval);
  }, [services.length]);

  // ─── Data Fetching ───

  const fetchAllData = async () => {
    try {
      setLoading(true);
      await Promise.all([
        fetchCustomer(),
        fetchLaundries(),
        fetchServices(),
        fetchPromotions(),
        fetchNotificationCount(),
        fetchStats(),
      ]);
    } catch (error) {
      console.error('Error loading home data:', error);
    } finally {
      setLoading(false);
    }
  };

  const onRefresh = useCallback(async () => {
    setRefreshing(true);
    await fetchAllData();
    setRefreshing(false);
  }, []);

  const fetchCustomer = async () => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return;

      const response = await fetch(`${API_BASE_URL}/v1/user`, {
        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data.customer) {
          setCustomer(data.data.customer);
          await AsyncStorage.setItem(STORAGE_KEYS.CUSTOMER, JSON.stringify(data.data.customer));
        }
      } else if (response.status === 401) {
        await AsyncStorage.multiRemove([STORAGE_KEYS.TOKEN, STORAGE_KEYS.CUSTOMER]);
        router.replace('/(auth)/login');
      }
    } catch (error) {
      // Try cached
      const cached = await AsyncStorage.getItem(STORAGE_KEYS.CUSTOMER);
      if (cached) setCustomer(JSON.parse(cached));
    }
  };

  const fetchLaundries = async () => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return;

      const response = await fetch(`${API_BASE_URL}/v1/laundries`, {
        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data.laundries) {
          setLaundries(data.data.laundries);
        }
      }
    } catch (error) {
      console.error('Error fetching laundries:', error);
    }
  };

  const fetchServices = async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/v1/services`, {
        headers: { 'Accept': 'application/json' },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data) {
          // Map services and ensure image URLs are properly formatted
          const servicesWithImages = data.data.map(service => ({
            ...service,
            image_url: service.image_url || service.icon_url || null
          }));
          setServices(servicesWithImages);
        }
      }
    } catch (error) {
      console.error('Error fetching services:', error);
    }
  };


  const fetchPromotions = async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/v1/promotions/featured`, {
        headers: { 'Accept': 'application/json' },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data.promotions && data.data.promotions.length > 0) {
          setPromotions(data.data.promotions);
          setActivePromo(data.data.promotions[0]);
        }
      }
    } catch (error) {
      console.error('Error fetching promotions:', error);
    }
  };

  const fetchNotificationCount = async () => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return;

      const response = await fetch(`${API_BASE_URL}/v1/notifications/unread-count`, {
        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          setUnreadCount(data.data?.unread_count ?? 0);
        }
      }
    } catch (error) {
      console.error('Error fetching notifications:', error);
    }
  };

  const fetchStats = async () => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return;

      const response = await fetch(`${API_BASE_URL}/v1/customer/stats`, {
        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data.stats) {
          setStats(data.data.stats);
        }
      }
    } catch (error) {
      console.error('Error fetching stats:', error);
    }
  };

  // ─── Derived Data ───

  const activeLaundries = laundries.filter(l =>
    !['completed', 'cancelled'].includes(l.status?.toLowerCase())
  ).slice(0, 5);

  const featuredPromos = promotions.filter(p => p.is_active).slice(0, 5);

  const firstName = customer?.name?.split(' ')[0] || 'there';

  // ─── Loading State ───

  if (loading) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />
        <View style={styles.loadingContainer}>
          <Image
            source={require('../../assets/images/logo.png')}
            style={styles.loadingLogo}
            resizeMode="contain"
          />
          <ActivityIndicator size="large" color={COLORS.primary} style={{ marginTop: 20 }} />
          <Text style={styles.loadingText}>Loading your dashboard...</Text>
        </View>
      </View>
    );
  }

  // ─── Render ───

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />

      <PromotionModal
        visible={showPromo}
        promotion={activePromo}
        onClose={() => setShowPromo(false)}
      />

      <ServiceDetailsModal
        visible={showServiceModal}
        service={selectedService}
        onClose={() => setShowServiceModal(false)}
        onBookNow={(service) => {
          // Navigate to pickup/booking screen with selected service
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

      <ScrollView
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor={COLORS.primary}
            colors={[COLORS.primary]}
            progressBackgroundColor={COLORS.surface}
          />
        }
        contentContainerStyle={{ paddingBottom: 100 }}
      >
        <Animated.View style={{ opacity: fadeAnim, transform: [{ translateY: slideAnim }] }}>

          {/* ═══════════════════════════════════════
              MODERN HEADER
          ═══════════════════════════════════════ */}
          <View style={styles.header}>
            <View style={styles.headerContent}>
              <View style={styles.headerTop}>
                <View style={styles.headerLeft}>
                  <View style={styles.avatarContainer}>
                    <Text style={styles.avatarText}>
                      {firstName.charAt(0).toUpperCase()}
                    </Text>
                  </View>
                  <View style={styles.greetingContainer}>
                    <Text style={styles.greetingTime}>{getGreeting()}</Text>
                    <Text style={styles.greetingName}>{firstName}</Text>
                  </View>
                </View>
                
                <View style={styles.headerActions}>
                  <TouchableOpacity
                    style={styles.actionButton}
                    onPress={() => router.push('/notifications')}
                    activeOpacity={0.7}
                  >
                    <Ionicons name="notifications-outline" size={20} color={COLORS.textSecondary} />
                    {unreadCount > 0 && (
                      <View style={styles.notificationBadge}>
                        <Text style={styles.notificationBadgeText}>
                          {unreadCount > 9 ? '9+' : unreadCount}
                        </Text>
                      </View>
                    )}
                  </TouchableOpacity>
                </View>
              </View>
              
              {/* Welcome Card */}
              <View style={styles.welcomeCard}>
                <LinearGradient
                  colors={COLORS.gradientPrimary}
                  style={styles.welcomeGradient}
                  start={{ x: 0, y: 0 }}
                  end={{ x: 1, y: 1 }}
                >
                  <View style={styles.welcomeContent}>
                    <Text style={styles.welcomeTitle}>Ready for fresh laundry?</Text>
                    <Text style={styles.welcomeSubtitle}>
                      Schedule a pickup and let us handle the rest
                    </Text>
                    <TouchableOpacity
                      style={styles.welcomeButton}
                      onPress={() => router.push('/(tabs)/pickup')}
                      activeOpacity={0.8}
                    >
                      <Text style={styles.welcomeButtonText}>Schedule Pickup</Text>
                      <Ionicons name="arrow-forward" size={16} color={COLORS.primary} />
                    </TouchableOpacity>
                  </View>
                  <View style={styles.welcomeIcon}>
                    <Ionicons name="car-outline" size={32} color="rgba(255,255,255,0.9)" />
                  </View>
                </LinearGradient>
              </View>
            </View>
          </View>


          {/* ═══════════════════════════════════════
              QUICK SERVICES
          ═══════════════════════════════════════ */}
          <View style={styles.servicesSection}>
            <Text style={styles.sectionTitle}>Quick Services</Text>
            <View style={styles.servicesGrid}>
              <ServiceCard
                icon="car-outline"
                title="Pickup"
                subtitle="Schedule now"
                color={COLORS.primary}
                onPress={() => router.push('/(tabs)/pickup')}
              />
              <ServiceCard
                icon="time-outline"
                title="History"
                subtitle="Past pickups"
                color={COLORS.success}
                onPress={() => router.push('/pickups')}
              />
              <ServiceCard
                icon="shirt-outline"
                title="Laundry"
                subtitle="Track orders"
                color={COLORS.secondary}
                onPress={() => router.push('/(tabs)/laundry')}
              />
              <ServiceCard
                icon="gift-outline"
                title="Offers"
                subtitle="Save money"
                color={COLORS.warning}
                onPress={() => router.push('/promotions')}
                badge={featuredPromos.length > 0 ? featuredPromos.length : null}
              />
            </View>
          </View>


          {/* ═══════════════════════════════════════
              SERVICES OFFER
          ═══════════════════════════════════════ */}
          {services.length > 0 && (
            <View style={styles.servicesOfferSection}>
              <View style={styles.sectionHeader}>
                <Text style={styles.sectionTitle}>Our Services</Text>
                <TouchableOpacity
                  style={styles.seeAllButton}
                  onPress={() => router.push('/services')}
                >
                  <Text style={styles.seeAllText}>See All</Text>
                  <Ionicons name="chevron-forward" size={16} color={COLORS.primary} />
                </TouchableOpacity>
              </View>

              <ScrollView
                ref={serviceScrollRef}
                horizontal
                showsHorizontalScrollIndicator={false}
                contentContainerStyle={styles.servicesScrollContent}
                onMomentumScrollEnd={(e) => {
                  const index = Math.round(e.nativeEvent.contentOffset.x / SERVICE_CARD_WIDTH);
                  serviceScrollIndex.current = index;
                  setServiceActiveIndex(index);
                }}
              >
                {services.map((service, index) => (
                  <TouchableOpacity
                    key={service.id || index}
                    style={styles.serviceOfferCard}
                    onPress={() => {
                      setSelectedService(service);
                      setShowServiceModal(true);
                    }}
                    activeOpacity={0.8}
                  >
                    {/* Background Image */}
                    {service.image_url && (
                      <>
                        <Image
                          source={{ uri: service.image_url }}
                          style={styles.serviceOfferBgImage}
                          resizeMode="cover"
                        />
                        {/* Dark overlay for better text readability */}
                        <View style={styles.serviceOfferOverlay} />
                      </>
                    )}
                    
                    <LinearGradient
                      colors={[
                        index % 4 === 0 ? '#0EA5E920' :
                        index % 4 === 1 ? '#8B5CF620' :
                        index % 4 === 2 ? '#10B98120' :
                        '#F59E0B20',
                        COLORS.surface
                      ]}
                      style={styles.serviceOfferGradient}
                    >
                      <View style={[
                        styles.serviceOfferIconContainer,
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

                      <Text style={styles.serviceOfferName} numberOfLines={1}>
                        {service.name}
                      </Text>
                      
                      {service.description && (
                        <Text style={styles.serviceOfferDesc} numberOfLines={2}>
                          {service.description}
                        </Text>
                      )}

                      <View style={styles.serviceOfferFooter}>
                        {service.price_per_kilo && (
                          <View style={styles.serviceOfferPriceTag}>
                            <Ionicons name="pricetag" size={12} color={COLORS.primary} />
                            <Text style={styles.serviceOfferPrice}>
                              ₱{parseFloat(service.price_per_kilo).toFixed(2)}/kg
                            </Text>
                          </View>
                        )}
                        {service.price_per_load && (
                          <View style={styles.serviceOfferPriceTag}>
                            <Ionicons name="pricetag" size={12} color={COLORS.primary} />
                            <Text style={styles.serviceOfferPrice}>
                              ₱{parseFloat(service.price_per_load).toFixed(2)}/load
                            </Text>
                          </View>
                        )}
                      </View>
                    </LinearGradient>
                  </TouchableOpacity>
                ))}
              </ScrollView>

              {/* Dot indicators */}
              {services.length > 1 && (
                <View style={styles.serviceDots}>
                  {services.map((_, i) => (
                    <View
                      key={i}
                      style={[
                        styles.serviceDot,
                        i === serviceActiveIndex && styles.serviceDotActive,
                      ]}
                    />
                  ))}
                </View>
              )}
            </View>
          )}


          {/* ═══════════════════════════════════════
              ACTIVE LAUNDRY
          ═══════════════════════════════════════ */}
          {activeLaundries.length > 0 && (
            <View style={styles.activeSection}>
              <View style={styles.activeSectionHeader}>
                <Text style={styles.sectionTitle}>Active Laundries</Text>
                <TouchableOpacity
                  style={styles.activeSeeAllButton}
                  onPress={() => router.push('/(tabs)/laundry')}
                >
                  <Text style={styles.activeSeeAllText}>See All</Text>
                  <Ionicons name="chevron-forward" size={16} color={COLORS.primary} />
                </TouchableOpacity>
              </View>

              <View style={styles.laundryContainer}>
                {activeLaundries.map((laundry, index) => (
                  <LaundryCard key={laundry.id || index} laundry={laundry} />
                ))}
              </View>
            </View>
          )}

          {/* Empty State */}
          {activeLaundries.length === 0 && (
            <View style={styles.emptySection}>
              <Text style={styles.sectionTitle}>Active Laundries</Text>
              <View style={styles.emptyStateCard}>
                <View style={styles.emptyStateIcon}>
                  <Ionicons name="shirt-outline" size={32} color={COLORS.textMuted} />
                </View>
                <Text style={styles.emptyStateTitle}>No active laundries</Text>
                <Text style={styles.emptyStateText}>
                  Start your first laundry order today
                </Text>
                <TouchableOpacity
                  style={styles.emptyStateButton}
                  onPress={() => router.push('/(tabs)/pickup')}
                  activeOpacity={0.8}
                >
                  <Text style={styles.emptyStateButtonText}>Schedule Pickup</Text>
                </TouchableOpacity>
              </View>
            </View>
          )}


          {/* ═══════════════════════════════════════
              PROMOTIONS
          ═══════════════════════════════════════ */}
          {featuredPromos.length > 0 && (
            <View style={styles.section}>
              <View style={styles.sectionHeader}>
                <View style={styles.sectionTitleRow}>
                  <View style={[styles.sectionDot, { backgroundColor: COLORS.pink }]} />
                  <Text style={styles.sectionTitle}>Promo Packages</Text>
                </View>
                <TouchableOpacity
                  style={styles.seeAllButton}
                  onPress={() => router.push('/promotions')}
                >
                  <Text style={styles.seeAllText}>See All</Text>
                  <Ionicons name="chevron-forward" size={14} color={COLORS.primary} />
                </TouchableOpacity>
              </View>

              <ScrollView
                ref={promoScrollRef}
                horizontal
                showsHorizontalScrollIndicator={false}
                contentContainerStyle={styles.promoScroll}
                decelerationRate="fast"
                snapToInterval={SCREEN_WIDTH * 0.78 + 12}
                snapToAlignment="start"
              >
                {featuredPromos.map((promo, index) => (
                  <PromoCard key={promo.id || index} promo={promo} index={index} />
                ))}
              </ScrollView>
            </View>
          )}


          {/* ═══════════════════════════════════════
              OVERVIEW STATS
          ═══════════════════════════════════════ */}
          <View style={styles.overviewSection}>
            <Text style={styles.sectionTitle}>Overview</Text>
            <View style={styles.overviewGrid}>
              <StatCard
                icon="shirt-outline"
                label="Total Laundries"
                value={stats.totalLaundries || laundries.length}
                color={COLORS.primary}
              />
              <StatCard
                icon="wallet-outline"
                label="Total Spent"
                value={`₱${Number(stats.totalSpent || 0).toLocaleString()}`}
                color={COLORS.success}
              />
              <StatCard
                icon="time-outline"
                label="In Progress"
                value={stats.pendingLaundries || activeLaundries.length}
                color={COLORS.warning}
              />
              <StatCard
                icon="car-outline"
                label="Pickups"
                value={stats.activePickups || 0}
                color={COLORS.secondary}
              />
            </View>
          </View>

        </Animated.View>
      </ScrollView>
    </View>
  );
}


// ─────────────────────────────────────────────
// SERVICE CARD COMPONENT
// ─────────────────────────────────────────────
const ServiceCard = ({ icon, title, subtitle, color, onPress, badge }) => (
  <TouchableOpacity style={styles.serviceCard} onPress={onPress} activeOpacity={0.7}>
    <View style={[styles.serviceIconContainer, { backgroundColor: color + '10' }]}>
      <Ionicons name={icon} size={24} color={color} />
      {badge && (
        <View style={styles.serviceBadge}>
          <Text style={styles.serviceBadgeText}>{badge}</Text>
        </View>
      )}
    </View>
    <Text style={styles.serviceTitle}>{title}</Text>
    <Text style={styles.serviceSubtitle}>{subtitle}</Text>
  </TouchableOpacity>
);


// ─────────────────────────────────────────────
// MODERN LAUNDRY CARD
// ─────────────────────────────────────────────
const LaundryCard = ({ laundry }) => {
  const statusColor = getStatusColor(laundry.status);
  const isActive = !['completed', 'cancelled'].includes(laundry.status?.toLowerCase());
  const hasPickup = !!laundry.pickup_request_id;

  return (
    <TouchableOpacity
      style={styles.modernLaundryCard}
      onPress={() => router.push(`/laundries/${laundry.tracking_number || laundry.id}`)}
      activeOpacity={0.7}
    >
      <View style={styles.laundryCardHeader}>
        <View style={styles.laundryCardLeft}>
          <View style={[styles.laundryStatusIndicator, { backgroundColor: statusColor }]} />
          <View>
            <Text style={styles.laundryTrackingNumber}>
              {laundry.tracking_number || `#${laundry.id}`}
            </Text>
            <Text style={styles.laundryServiceName}>
              {laundry.service_name || 'Laundry Service'}
            </Text>
          </View>
        </View>
        <View style={[styles.laundryStatusBadge, { backgroundColor: statusColor + '15' }]}>
          <Text style={[styles.laundryStatusText, { color: statusColor }]}>
            {formatStatus(laundry.status)}
          </Text>
        </View>
      </View>
      
      <View style={styles.laundryCardFooter}>
        {laundry.branch_name && (
          <View style={styles.laundryBranchInfo}>
            <Ionicons name="location-outline" size={14} color={COLORS.textMuted} />
            <Text style={styles.laundryBranchText}>{laundry.branch_name}</Text>
          </View>
        )}
        <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8 }}>
          {laundry.total_amount > 0 && (
            <Text style={styles.laundryAmount}>
              ₱{Number(laundry.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}
            </Text>
          )}
          {isActive && hasPickup && (
            <TouchableOpacity
              style={styles.trackChip}
              onPress={(e) => { e.stopPropagation(); router.push(`/pickup-tracking?id=${laundry.pickup_request_id}`); }}
              activeOpacity={0.7}
            >
              <Ionicons name="navigate" size={12} color={COLORS.success} />
              <Text style={styles.trackChipText}>Track</Text>
            </TouchableOpacity>
          )}
        </View>
      </View>
    </TouchableOpacity>
  );
};


// ─────────────────────────────────────────────
// PROMOTION CARD
// ─────────────────────────────────────────────
const PROMO_GRADIENTS = [
  ['#0EA5E9', '#3B82F6'],
  ['#8B5CF6', '#7C3AED'],
  ['#EC4899', '#F43F5E'],
  ['#F59E0B', '#F97316'],
  ['#10B981', '#059669'],
];

const PromoCard = ({ promo, index }) => {
  const colors = PROMO_GRADIENTS[index % PROMO_GRADIENTS.length];
  const hasBackgroundImage = promo.banner_image_url || promo.banner_image;

  return (
    <TouchableOpacity
      style={styles.promoCard}
      activeOpacity={0.85}
      onPress={() => router.push('/promotions')}
    >
      {hasBackgroundImage ? (
        <View style={styles.promoImageContainer}>
          <Image
            source={{ uri: promo.banner_image_url || promo.banner_image }}
            style={styles.promoBackgroundImage}
            resizeMode="cover"
          />
          <LinearGradient
            colors={['rgba(0,0,0,0.3)', 'rgba(0,0,0,0.7)']}
            style={styles.promoImageOverlay}
            start={{ x: 0, y: 0 }}
            end={{ x: 0, y: 1 }}
          >
            {/* Content with image background */}
            <View style={styles.promoContentWithImage}>

            {/* Badges */}
            <View style={styles.promoBadges}>
              {promo.is_featured && (
                <View style={styles.promoBadge}>
                  <Ionicons name="star" size={10} color="#FFC107" />
                  <Text style={styles.promoBadgeText}>FEATURED</Text>
                </View>
              )}
              {promo.is_active && (
                <View style={[styles.promoBadge, { backgroundColor: 'rgba(16, 185, 129, 0.25)' }]}>
                  <View style={{ width: 5, height: 5, borderRadius: 2.5, backgroundColor: '#10B981' }} />
                  <Text style={[styles.promoBadgeText, { color: '#6EE7B7' }]}>ACTIVE</Text>
                </View>
              )}
            </View>

            {/* Content */}
            <Text style={styles.promoTitle} numberOfLines={2}>
              {promo.poster_title || promo.name}
            </Text>
            <Text style={styles.promoSubtitle} numberOfLines={2}>
              {promo.poster_subtitle || promo.description}
            </Text>

            {/* Price */}
            {promo.display_price && (
              <View style={styles.promoPriceRow}>
                <Text style={styles.promoPrice}>₱{promo.display_price}</Text>
                {promo.original_price && (
                  <Text style={styles.promoOriginal}>₱{promo.original_price}</Text>
                )}
              </View>
            )}

            {/* Features */}
            {promo.poster_features?.length > 0 && (
              <View style={styles.promoFeatures}>
                {promo.poster_features.slice(0, 2).map((f, i) => (
                  <View key={i} style={styles.promoFeatureRow}>
                    <Ionicons name="checkmark-circle" size={12} color="rgba(255,255,255,0.85)" />
                    <Text style={styles.promoFeatureText} numberOfLines={1}>{f}</Text>
                  </View>
                ))}
              </View>
            )}

            {/* Valid until */}
            {promo.valid_until && (
              <View style={styles.promoValidity}>
                <Ionicons name="time-outline" size={11} color="rgba(255,255,255,0.6)" />
                <Text style={styles.promoValidityText}>
                  Valid until {new Date(promo.valid_until).toLocaleDateString()}
                </Text>
              </View>
            )}
          </View>
        </LinearGradient>
        </View>
      ) : (
        <LinearGradient
          colors={colors}
          style={styles.promoGradient}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
        >
          {/* Decorative circles */}
          <View style={[styles.promoCircle, styles.promoCircle1]} />
          <View style={[styles.promoCircle, styles.promoCircle2]} />

          {/* Badges */}
          <View style={styles.promoBadges}>
            {promo.is_featured && (
              <View style={styles.promoBadge}>
                <Ionicons name="star" size={10} color="#FFC107" />
                <Text style={styles.promoBadgeText}>FEATURED</Text>
              </View>
            )}
            {promo.is_active && (
              <View style={[styles.promoBadge, { backgroundColor: 'rgba(16, 185, 129, 0.25)' }]}>
                <View style={{ width: 5, height: 5, borderRadius: 2.5, backgroundColor: '#10B981' }} />
                <Text style={[styles.promoBadgeText, { color: '#6EE7B7' }]}>ACTIVE</Text>
              </View>
            )}
          </View>

          {/* Content */}
          <Text style={styles.promoTitle} numberOfLines={2}>
            {promo.poster_title || promo.name}
          </Text>
          <Text style={styles.promoSubtitle} numberOfLines={2}>
            {promo.poster_subtitle || promo.description}
          </Text>

          {/* Price */}
          {promo.display_price && (
            <View style={styles.promoPriceRow}>
              <Text style={styles.promoPrice}>₱{promo.display_price}</Text>
              {promo.original_price && (
                <Text style={styles.promoOriginal}>₱{promo.original_price}</Text>
              )}
            </View>
          )}

          {/* Features */}
          {promo.poster_features?.length > 0 && (
            <View style={styles.promoFeatures}>
              {promo.poster_features.slice(0, 2).map((f, i) => (
                <View key={i} style={styles.promoFeatureRow}>
                  <Ionicons name="checkmark-circle" size={12} color="rgba(255,255,255,0.85)" />
                  <Text style={styles.promoFeatureText} numberOfLines={1}>{f}</Text>
                </View>
              ))}
            </View>
          )}

          {/* Valid until */}
          {promo.valid_until && (
            <View style={styles.promoValidity}>
              <Ionicons name="time-outline" size={11} color="rgba(255,255,255,0.6)" />
              <Text style={styles.promoValidityText}>
                Valid until {new Date(promo.valid_until).toLocaleDateString()}
              </Text>
            </View>
          )}
        </LinearGradient>
      )}
    </TouchableOpacity>
  );
};


// ─────────────────────────────────────────────
// MODERN STAT CARD
// ─────────────────────────────────────────────
const StatCard = ({ icon, label, value, color }) => (
  <View style={styles.modernStatCard}>
    <View style={[styles.statIconContainer, { backgroundColor: color + '10' }]}>
      <Ionicons name={icon} size={20} color={color} />
    </View>
    <View style={styles.statContent}>
      <Text style={styles.statValue}>{value}</Text>
      <Text style={styles.statLabel}>{label}</Text>
    </View>
  </View>
);


// ─────────────────────────────────────────────
// STYLES
// ─────────────────────────────────────────────
const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  centerContent: {
    justifyContent: 'center',
    alignItems: 'center',
  },

  // Loading
  loadingContainer: {
    alignItems: 'center',
    padding: 40,
  },
  loadingLogo: {
    width: 64,
    height: 64,
    borderRadius: 16,
  },
  loadingText: {
    color: COLORS.textSecondary,
    marginTop: 16,
    fontSize: 16,
    fontWeight: '500',
  },

  // Modern Header
  header: {
    backgroundColor: COLORS.surface,
    paddingTop: Platform.OS === 'ios' ? 50 : 40,
    paddingBottom: 20,
    borderBottomLeftRadius: 24,
    borderBottomRightRadius: 24,
    shadowColor: COLORS.shadow,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 4,
  },
  headerContent: {
    paddingHorizontal: 20,
  },
  headerTop: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 20,
  },
  headerLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  avatarContainer: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: COLORS.primary,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  avatarText: {
    fontSize: 20,
    fontWeight: '700',
    color: '#FFFFFF',
  },
  greetingContainer: {
    flex: 1,
  },
  greetingTime: {
    fontSize: 14,
    color: COLORS.textMuted,
    fontWeight: '500',
  },
  greetingName: {
    fontSize: 24,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginTop: 2,
  },
  headerActions: {
    flexDirection: 'row',
    gap: 8,
  },
  actionButton: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: COLORS.surfaceElevated,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: COLORS.borderLight,
  },
  notificationBadge: {
    position: 'absolute',
    top: 8,
    right: 8,
    minWidth: 18,
    height: 18,
    borderRadius: 9,
    backgroundColor: COLORS.danger,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 4,
  },
  notificationBadgeText: {
    fontSize: 10,
    fontWeight: '700',
    color: '#FFFFFF',
  },

  // Welcome Card
  welcomeCard: {
    borderRadius: 16,
    overflow: 'hidden',
    shadowColor: COLORS.shadow,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 12,
    elevation: 6,
  },
  welcomeGradient: {
    padding: 20,
    flexDirection: 'row',
    alignItems: 'center',
  },
  welcomeContent: {
    flex: 1,
  },
  welcomeTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#FFFFFF',
    marginBottom: 4,
  },
  welcomeSubtitle: {
    fontSize: 14,
    color: 'rgba(255, 255, 255, 0.8)',
    marginBottom: 16,
    lineHeight: 20,
  },
  welcomeButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 12,
    alignSelf: 'flex-start',
    gap: 6,
  },
  welcomeButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.primary,
  },
  welcomeIcon: {
    marginLeft: 16,
  },

  // Services Section
  servicesSection: {
    paddingHorizontal: 20,
    paddingTop: 24,
  },
  sectionTitleBase: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 16,
  },
  servicesGrid: {
    flexDirection: 'row',
    gap: 8,
  },

  // Services Offer Section
  servicesOfferSection: {
    paddingTop: 24,
    paddingBottom: 8,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    marginBottom: 16,
  },
  servicesScrollContent: {
    paddingHorizontal: 20,
    gap: 12,
  },
  serviceOfferCard: {
    width: 260,
    borderRadius: 20,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.1)',
    backgroundColor: COLORS.surface,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.2,
    shadowRadius: 14,
    elevation: 5,
    position: 'relative',
  },
  serviceOfferBgImage: {
    position: 'absolute',
    width: '100%',
    height: '100%',
    top: 0,
    left: 0,
  },
  serviceOfferOverlay: {
    position: 'absolute',
    width: '100%',
    height: '100%',
    backgroundColor: 'rgba(6,8,26,0.4)',
  },
  serviceOfferGradient: {
    padding: 20,
    minHeight: 200,
    backgroundColor: 'transparent',
  },
  serviceOfferIconContainer: {
    width: 60,
    height: 60,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 14,
  },
  serviceOfferName: {
    fontSize: 17,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 6,
  },
  serviceOfferDesc: {
    fontSize: 13,
    color: COLORS.textSecondary,
    lineHeight: 18,
    marginBottom: 14,
  },
  serviceOfferFooter: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginTop: 'auto',
  },
  serviceOfferPriceTag: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: COLORS.primarySoft,
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.primary + '20',
  },
  serviceOfferPrice: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.primary,
  },
  serviceOfferTimeTag: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: COLORS.surfaceLight,
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 12,
  },
  serviceOfferTime: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textMuted,
  },
  serviceDots: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    gap: 6,
    marginTop: 12,
  },
  serviceDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: COLORS.borderMuted,
  },
  serviceDotActive: {
    width: 18,
    backgroundColor: COLORS.primary,
  },

  serviceCard: {
    flex: 1,
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    padding: 14,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: COLORS.borderLight,
    shadowColor: COLORS.shadow,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 3,
  },
  serviceIconContainer: {
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
    position: 'relative',
  },
  serviceBadge: {
    position: 'absolute',
    top: -4,
    right: -4,
    minWidth: 18,
    height: 18,
    borderRadius: 9,
    backgroundColor: COLORS.danger,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 4,
  },
  serviceBadgeText: {
    fontSize: 10,
    fontWeight: '700',
    color: '#FFFFFF',
  },
  serviceTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginBottom: 2,
  },
  serviceSubtitle: {
    fontSize: 12,
    color: COLORS.textMuted,
    textAlign: 'center',
  },

  // ─── Sections ───
  section: {
    marginTop: 28,
  },

  sectionTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  sectionDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.textPrimary,
    letterSpacing: -0.2,
  },
 seeAllButton : {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 2,
  },
  seeAllText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.primary,
  },

  // Modern Laundry Cards
  modernLaundryCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
    shadowColor: COLORS.shadow,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  laundryCardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  laundryCardLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  laundryStatusIndicator: {
    width: 4,
    height: 32,
    borderRadius: 2,
    marginRight: 12,
  },
  laundryTrackingNumber: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.textPrimary,
    fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace',
  },
  laundryServiceName: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginTop: 2,
  },
  laundryStatusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 8,
  },
  laundryStatusText: {
    fontSize: 11,
    fontWeight: '600',
  },
  laundryCardFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  laundryBranchInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  laundryBranchText: {
    fontSize: 12,
    color: COLORS.textMuted,
  },
  laundryAmount: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.textPrimary,
  },
  trackChip: {
    flexDirection: 'row', alignItems: 'center', gap: 4,
    backgroundColor: COLORS.success + '15',
    paddingHorizontal: 8, paddingVertical: 4, borderRadius: 8,
    borderWidth: 1, borderColor: COLORS.success + '30',
  },
  trackChipText: { fontSize: 11, fontWeight: '600', color: COLORS.success },

  // ─── Empty CTA ───
  emptyCta: {
    marginHorizontal: 20,
    borderRadius: 20,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: COLORS.borderLight,
  },
  emptyCtaGradient: {
    padding: 28,
    alignItems: 'center',
  },
  emptyCtaIcon: {
    width: 60,
    height: 60,
    borderRadius: 20,
    backgroundColor: COLORS.primaryGlow,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
  },
  emptyCtaTitle: {
    fontSize: 17,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 6,
  },
  emptyCtaText: {
    fontSize: 13,
    color: COLORS.textMuted,
    textAlign: 'center',
    lineHeight: 18,
    marginBottom: 18,
  },
  emptyCtaButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: COLORS.primaryGlow,
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.primary + '30',
  },
  emptyCtaButtonText: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.primary,
  },

  // ─── Promo Cards ───
  promoScroll: {
    paddingHorizontal: 20,
    gap: 12,
  },
  promoCard: {
    width: SCREEN_WIDTH * 0.78,
    borderRadius: 20,
    overflow: 'hidden',
    elevation: 6,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 12,
  },
  promoImageContainer: {
    position: 'relative',
    minHeight: 190,
  },
  promoBackgroundImage: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    width: '100%',
    height: '100%',
  },
  promoImageOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    justifyContent: 'flex-end',
  },
  promoContentWithImage: {
    padding: 20,
    flex: 1,
    justifyContent: 'flex-end',
  },
  promoGradient: {
    padding: 20,
    minHeight: 190,
    position: 'relative',
    overflow: 'hidden',
  },
  promoCircle: {
    position: 'absolute',
    borderRadius: 999,
    backgroundColor: 'rgba(255, 255, 255, 0.06)',
  },
  promoCircle1: {
    width: 140,
    height: 140,
    top: -40,
    right: -30,
  },
  promoCircle2: {
    width: 80,
    height: 80,
    bottom: -20,
    left: -20,
  },
  promoBadges: {
    flexDirection: 'row',
    gap: 6,
    marginBottom: 14,
  },
  promoBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: 'rgba(255, 193, 7, 0.2)',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 8,
  },
  promoBadgeText: {
    fontSize: 9,
    fontWeight: '800',
    color: '#FFC107',
    letterSpacing: 0.5,
  },
  promoTitle: {
    fontSize: 20,
    fontWeight: '800',
    color: '#FFF',
    marginBottom: 6,
    lineHeight: 26,
  },
  promoSubtitle: {
    fontSize: 13,
    color: 'rgba(255, 255, 255, 0.85)',
    lineHeight: 18,
    marginBottom: 12,
  },
  promoPriceRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginBottom: 10,
  },
  promoPrice: {
    fontSize: 28,
    fontWeight: '800',
    color: '#FFF',
  },
  promoOriginal: {
    fontSize: 16,
    color: 'rgba(255, 255, 255, 0.5)',
    textDecorationLine: 'line-through',
  },
  promoFeatures: {
    gap: 6,
    marginBottom: 8,
  },
  promoFeatureRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  promoFeatureText: {
    flex: 1,
    fontSize: 11,
    fontWeight: '500',
    color: 'rgba(255, 255, 255, 0.85)',
  },
  promoValidity: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    marginTop: 6,
  },
  promoValidityText: {
    fontSize: 10,
    color: 'rgba(255, 255, 255, 0.55)',
    fontWeight: '500',
  },

  // Overview Section
  overviewSection: {
    paddingHorizontal: 20,
    paddingTop: 24,
  },
  overviewGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  modernStatCard: {
    width: (SCREEN_WIDTH - 52) / 2,
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 16,
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: COLORS.border,
    shadowColor: COLORS.shadow,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  statIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  statContent: {
    flex: 1,
  },
  statValue: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 2,
  },
  statLabel: {
    fontSize: 12,
    color: COLORS.textMuted,
    fontWeight: '500',
  },

  // Section Styles
  activeSection: {
    paddingHorizontal: 20,
    paddingTop: 24,
  },
  emptySection: {
    paddingHorizontal: 20,
    paddingTop: 24,
  },
  activeSectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 16,
  },
  activeSeeAllButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  activeSeeAllText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.primary,
  },
  laundryContainer: {
    gap: 12,
  },

  // Empty State
  emptyStateCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    padding: 32,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: COLORS.border,
    shadowColor: COLORS.shadow,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  emptyStateIcon: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: COLORS.surfaceElevated,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
  },
  emptyStateTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 8,
  },
  emptyStateText: {
    fontSize: 14,
    color: COLORS.textMuted,
    textAlign: 'center',
    marginBottom: 20,
    lineHeight: 20,
  },
  emptyStateButton: {
    backgroundColor: COLORS.primary,
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 12,
  },
  emptyStateButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#FFFFFF',
  },
});
