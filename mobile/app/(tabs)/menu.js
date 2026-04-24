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
  Animated,
  Dimensions,
  StatusBar,
  Platform,
} from 'react-native';
import { router } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { API_BASE_URL, STORAGE_KEYS, ENDPOINTS } from '../../constants/config';
import { useAuth } from '../../context/AuthContext';
import TermsModal from '../../components/TermsModal';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

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
  purple: '#8B5CF6',
  pink: '#EC4899',
  cyan: '#06B6D4',
  border: '#1E293B',
  gradientPrimary: ['#0EA5E9', '#3B82F6'],
  gradientSecondary: ['#8B5CF6', '#EC4899'],
  gradientSuccess: ['#10B981', '#059669'],
  gradientWarning: ['#F59E0B', '#EF4444'],
};

// ─── Services config ───────────────────────────────────────────────────────
const CATEGORY_ORDER = ['drop_off', 'self_service', 'addon'];

const CATEGORY_CONFIG = {
  drop_off: {
    label: 'Drop Off',
    icon: 'shirt-outline',
    gradient: ['#0EA5E9', '#3B82F6'],
  },
  self_service: {
    label: 'Self Service',
    icon: 'settings-outline',
    gradient: ['#10B981', '#059669'],
  },
  addon: {
    label: 'Add-ons',
    icon: 'add-circle-outline',
    gradient: ['#F59E0B', '#D97706'],
  },
};

const SERVICE_TYPE_LABELS = {
  regular_clothes: 'Regular Clothes',
  full_service:    'Full Service',
  special_item:    'Comforter / Blanket',
  self_service:    'Self Service',
  addon:           'Add-on',
};
// ──────────────────────────────────────────────────────────────────────────

export default function MenuScreen() {
  const { logout } = useAuth();
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [loggingOut, setLoggingOut] = useState(false);
  const [customer, setCustomer] = useState(null);
  const [branch, setBranch]     = useState(null);
  const [showTermsModal, setShowTermsModal] = useState(false);
  const [stats, setStats] = useState({
    totalLaundries: 0,
    totalSpent: 0,
    rating: 0,
    completionRate: 0,
    pendingLaundries: 0,
    activePickups: 0,
  });

  // Services state
  const [services, setServices] = useState({});          // { drop_off: [...], ... }
  const [servicesLoading, setServicesLoading] = useState(false);
  const [expandedCategory, setExpandedCategory] = useState(null);

  // Branches state
  const [branches, setBranches] = useState([]);
  const [branchesLoading, setBranchesLoading] = useState(false);
  const [branchesExpanded, setBranchesExpanded] = useState(false);
  const [currentTime, setCurrentTime] = useState(new Date());

  // Animations
  const [fadeAnim] = useState(new Animated.Value(0));
  const [slideAnim] = useState(new Animated.Value(30));

  useEffect(() => {
    fetchMenuData();
    
    // Update time every minute to refresh open/closed status
    const timeInterval = setInterval(() => {
      setCurrentTime(new Date());
    }, 60000);
    
    return () => clearInterval(timeInterval);
  }, []);

  useEffect(() => {
    if (!loading) {
      Animated.parallel([
        Animated.timing(fadeAnim, {
          toValue: 1,
          duration: 500,
          useNativeDriver: true,
        }),
        Animated.spring(slideAnim, {
          toValue: 0,
          tension: 50,
          friction: 8,
          useNativeDriver: true,
        }),
      ]).start();
    }
  }, [loading]);

  const fetchMenuData = async () => {
    try {
      setLoading(true);
      await Promise.all([
        fetchCustomerProfile(),
        fetchCustomerStats(),
        fetchServices(),
        fetchBranches(),
      ]);
    } catch (error) {
      console.error('Error fetching menu data:', error);
    } finally {
      setLoading(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchMenuData();
    setCurrentTime(new Date()); // Force time update on refresh
    setRefreshing(false);
  };

  const fetchCustomerProfile = async () => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return;

      const response = await fetch(`${API_BASE_URL}/v1/user`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data.customer) {
          setCustomer(data.data.customer);
          await AsyncStorage.setItem(STORAGE_KEYS.CUSTOMER, JSON.stringify(data.data.customer));
          // Fetch the branch the customer registered at
          const branchId = data.data.customer.branch_id;
          if (branchId) await fetchBranch(branchId, token);
        }
      } else if (response.status === 401) {
        await logout();
      }
    } catch (error) {
      const cachedCustomer = await AsyncStorage.getItem(STORAGE_KEYS.CUSTOMER);
      if (cachedCustomer) setCustomer(JSON.parse(cachedCustomer));
    }
  };

  const fetchBranch = async (branchId, token) => {
    try {
      const response = await fetch(`${API_BASE_URL}/v1/branches/${branchId}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });
      if (response.ok) {
        const data = await response.json();
        const b = data.data?.branch ?? data.data ?? data.branch ?? null;
        if (b) {
          setBranch(b);
          await AsyncStorage.setItem('cached_branch', JSON.stringify(b));
        }
      } else {
        const cached = await AsyncStorage.getItem('cached_branch');
        if (cached) setBranch(JSON.parse(cached));
      }
    } catch (error) {
      console.log('Branch fetch error:', error);
      const cached = await AsyncStorage.getItem('cached_branch');
      if (cached) setBranch(JSON.parse(cached));
    }
  };

  const fetchCustomerStats = async () => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return;

      const response = await fetch(`${API_BASE_URL}/v1/customer/stats`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data.stats) {
          setStats(data.data.stats);
        }
      }
    } catch (error) {
      console.log('Stats fetch error:', error);
    }
  };

  // ─── Fetch + group services by category ─────────────────────────────────
  const fetchServices = async () => {
    try {
      setServicesLoading(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);

      // Use ENDPOINTS constant; strip any accidental trailing slash from base
      const baseUrl = API_BASE_URL.replace(/\/+$/, '');
      const url     = `${baseUrl}${ENDPOINTS.SERVICES}`;

      console.log('[fetchServices] GET', url);

      const response = await fetch(url, {
        headers: {
          'Authorization': token ? `Bearer ${token}` : '',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      });

      console.log('[fetchServices] status', response.status, response.headers.get('content-type'));

      // Guard: make sure we actually got JSON before parsing
      const contentType = response.headers.get('content-type') ?? '';
      if (!contentType.includes('application/json')) {
        const raw = await response.text();
        console.warn('[fetchServices] Non-JSON response (first 300 chars):', raw.substring(0, 300));
        // Fall back to cache
        const cached = await AsyncStorage.getItem('cached_services');
        if (cached) setServices(JSON.parse(cached));
        return;
      }

      if (response.ok) {
        const data = await response.json();
        const list = data.data?.services ?? data.data ?? data.services ?? [];

        // Group by category field
        const grouped = {};
        list.forEach(svc => {
          const cat = svc.category || 'drop_off';
          if (!grouped[cat]) grouped[cat] = [];
          grouped[cat].push(svc);
        });

        console.log('[fetchServices] loaded', list.length, 'services in', Object.keys(grouped), 'categories');
        setServices(grouped);
        await AsyncStorage.setItem('cached_services', JSON.stringify(grouped));
      } else {
        const errBody = await response.json().catch(() => ({}));
        console.warn('[fetchServices] Error response:', response.status, errBody);
        const cached = await AsyncStorage.getItem('cached_services');
        if (cached) setServices(JSON.parse(cached));
      }
    } catch (error) {
      console.log('Services fetch error:', error);
      const cached = await AsyncStorage.getItem('cached_services');
      if (cached) setServices(JSON.parse(cached));
    } finally {
      setServicesLoading(false);
    }
  };

  // ─── Fetch branches ─────────────────────────────────────────────────────
  const fetchBranches = async () => {
    try {
      setBranchesLoading(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);

      // Clear cache first to ensure fresh data
      await AsyncStorage.removeItem('cached_branches');

      const response = await fetch(`${API_BASE_URL}/v1/branches?t=${Date.now()}`, {
        headers: {
          'Authorization': token ? `Bearer ${token}` : '',
          'Accept': 'application/json',
          'Cache-Control': 'no-cache',
        },
      });

      if (response.ok) {
        const data = await response.json();
        const branchList = data.data?.branches ?? data.data ?? [];
        setBranches(branchList);
        await AsyncStorage.setItem('cached_branches', JSON.stringify(branchList));
      } else {
        const cached = await AsyncStorage.getItem('cached_branches');
        if (cached) setBranches(JSON.parse(cached));
      }
    } catch (error) {
      console.log('Branches fetch error:', error);
      const cached = await AsyncStorage.getItem('cached_branches');
      if (cached) setBranches(JSON.parse(cached));
    } finally {
      setBranchesLoading(false);
    }
  };
  // ──────────────────────────────────────────────────────────────────────────

  const performLogout = async () => {
    try {
      setLoggingOut(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);

      if (token) {
        await fetch(`${API_BASE_URL}/v1/logout`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
          },
        });
      }
      await logout();
    } catch (error) {
      console.error('Logout error:', error);
      await logout();
    } finally {
      setLoggingOut(false);
    }
  };

  const handleLogout = () => {
    Alert.alert(
      'Log Out',
      'Are you sure you want to log out?',
      [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Log Out', style: 'destructive', onPress: performLogout },
      ]
    );
  };

  const getInitials = (name) => {
    if (!name) return '??';
    return name.split(' ')
      .map(n => n[0])
      .join('')
      .toUpperCase()
      .substring(0, 2);
  };

  const formatCurrency = (amount) => {
    return `₱${parseFloat(amount || 0).toLocaleString('en-PH', { 
      minimumFractionDigits: 0,
      maximumFractionDigits: 0 
    })}`;
  };

  const formatTo12Hour = (time24) => {
    const [hours, minutes] = time24.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    return `${hour12}:${minutes} ${ampm}`;
  };

  const formatServicePrice = (service) => {
    const price = service.price_per_load ?? service.price ?? 0;
    const isSpecial = service.service_type === 'special_item';
    return `₱${parseFloat(price).toLocaleString('en-PH', { minimumFractionDigits: 0 })}/${isSpecial ? 'piece' : 'load'}`;
  };

  const formatOperatingHours = (hours) => {
    if (!hours || typeof hours !== 'object') return 'Hours not available';
    
    const today = new Date().toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
    const todayHours = hours[today];
    
    if (todayHours && todayHours.open && todayHours.close) {
      const openTime = formatTo12Hour(todayHours.open);
      const closeTime = formatTo12Hour(todayHours.close);
      return `Today: ${openTime} - ${closeTime}`;
    }
    
    return 'Hours not available';
  };

  const isCurrentlyOpen = (operatingHours) => {
    if (!operatingHours || typeof operatingHours !== 'object') return false;
    
    const today = new Date().toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
    const todayHours = operatingHours[today];
    
    if (!todayHours || todayHours.status !== 'open') return false;
    
    try {
      const now = new Date();
      const currentTime = now.getHours() * 60 + now.getMinutes();
      
      const [openHour, openMin] = todayHours.open.split(':').map(Number);
      const [closeHour, closeMin] = todayHours.close.split(':').map(Number);
      
      const openMinutes = openHour * 60 + openMin;
      const closeMinutes = closeHour * 60 + closeMin;
      
      return currentTime >= openMinutes && currentTime <= closeMinutes;
    } catch (error) {
      return false;
    }
  };

  // Menu items
  const primaryMenuItems = [
    {
      icon: 'person-outline',
      label: 'Edit Profile',
      subtitle: 'Update your information',
      color: COLORS.primary,
      action: () => router.push('/profile/edit'),
    },
    {
      icon: 'business-outline',
      label: 'Rate a Branch',
      subtitle: 'Share your feedback about our branches',
      color: COLORS.success,
      action: () => router.push('/ratings?tab=rate_branch'),
    },
    {
      icon: 'star-outline',
      label: 'My Ratings',
      subtitle: 'View and manage your ratings',
      color: COLORS.warning,
      action: () => router.push('/ratings'),
    },
    {
      icon: 'notifications-outline',
      label: 'Notifications',
      subtitle: 'Push notifications & alerts',
      color: COLORS.purple,
      action: () => router.push('/notifications'),
    },
  ];

  const secondaryMenuItems = [
    {
      icon: 'pricetag-outline',
      label: 'Promotions',
      subtitle: 'Deals and discounts',
      color: COLORS.success,
      action: () => router.push('/promotions'),
    },
    {
      icon: 'card-outline',
      label: 'Payment Methods',
      subtitle: 'Manage payment options',
      color: COLORS.cyan,
      action: () => router.push('/payment-methods'),
    },
    {
      icon: 'location-outline',
      label: 'Saved Addresses',
      subtitle: 'Manage delivery locations',
      color: COLORS.primaryLight,
      action: () => router.push('/saved-addresses'),
    },
  ];

  const supportMenuItems = [
    {
      icon: 'shield-checkmark-outline',
      label: 'Privacy & Security',
      subtitle: 'Password & account security',
      color: COLORS.secondary,
      action: () => router.push('/privacy-security'),
    },
    {
      icon: 'help-circle-outline',
      label: 'Help Center',
      subtitle: 'FAQs and support',
      color: COLORS.primary,
      action: () => {
        const phone = branch?.phone ?? '(035) 123-4567';
        const email = branch?.email ?? 'support@washbox.com';
        const name  = branch?.name  ?? 'WashBox';
        Alert.alert(
          `${name} Support`,
          `📞 ${phone}\n✉️ ${email}`,
          [
            { text: 'Network Diagnostic', onPress: () => router.push('/network-diagnostic') },
            { text: 'Feature Integrations', onPress: () => router.push('/feature-integrations') },
            { text: 'OK' }
          ]
        );
      },
    },
    {
      icon: 'document-text-outline',
      label: 'Terms & Privacy',
      subtitle: 'Legal information',
      color: COLORS.textSecondary,
      action: () => setShowTermsModal(true),
    },
  ];

  if (loading) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Loading...</Text>
      </View>
    );
  }

  // ─── Renderers ────────────────────────────────────────────────────────────

  const renderMenuItem = (item, index, isLast) => (
    <React.Fragment key={index}>
      <TouchableOpacity
        style={styles.menuItem}
        onPress={item.action}
        activeOpacity={0.7}
      >
        <View style={[styles.menuIconContainer, { backgroundColor: item.color + '20' }]}>
          <Ionicons name={item.icon} size={22} color={item.color} />
        </View>
        <View style={styles.menuContent}>
          <Text style={styles.menuLabel}>{item.label}</Text>
          <Text style={styles.menuSubtitle}>{item.subtitle}</Text>
        </View>
        <Ionicons name="chevron-forward" size={20} color={COLORS.textMuted} />
      </TouchableOpacity>
      {!isLast && <View style={styles.menuDivider} />}
    </React.Fragment>
  );

  // Single service row inside an expanded category
  const renderServiceRow = (service, index, isLast) => {
    const isSpecial = service.service_type === 'special_item';
    const typeLabel = SERVICE_TYPE_LABELS[service.service_type] || service.service_type;
    const maxWeight = service.max_weight ? ` · up to ${service.max_weight} kg` : '';

    return (
      <React.Fragment key={service.id ?? index}>
        <View style={styles.serviceRow}>
          <View style={[
            styles.serviceTypeDot,
            { backgroundColor: isSpecial ? COLORS.warning + '25' : COLORS.primary + '20' },
          ]}>
            <Ionicons
              name={isSpecial ? 'layers-outline' : 'checkmark-outline'}
              size={14}
              color={isSpecial ? COLORS.warning : COLORS.primary}
            />
          </View>
          <View style={styles.serviceRowInfo}>
            <Text style={styles.serviceRowName}>{service.name}</Text>
            <Text style={styles.serviceRowMeta}>{typeLabel}{maxWeight}</Text>
          </View>
          <Text style={styles.serviceRowPrice}>{formatServicePrice(service)}</Text>
        </View>
        {!isLast && <View style={styles.serviceRowDivider} />}
      </React.Fragment>
    );
  };

  // Collapsible category card
  const renderCategoryCard = (categoryKey, catServices) => {
    const config = CATEGORY_CONFIG[categoryKey] ?? CATEGORY_CONFIG.drop_off;
    const isOpen = expandedCategory === categoryKey;
    const count  = catServices?.length ?? 0;

    return (
      <View key={categoryKey} style={styles.categoryCard}>
        <TouchableOpacity
          style={styles.categoryHeader}
          activeOpacity={0.75}
          onPress={() => setExpandedCategory(isOpen ? null : categoryKey)}
        >
          <LinearGradient
            colors={config.gradient}
            style={styles.categoryIcon}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
          >
            <Ionicons name={config.icon} size={20} color="#fff" />
          </LinearGradient>
          <View style={styles.categoryMeta}>
            <Text style={styles.categoryLabel}>{config.label}</Text>
            <Text style={styles.categoryCount}>
              {count} service{count !== 1 ? 's' : ''}
            </Text>
          </View>
          <Ionicons
            name={isOpen ? 'chevron-up' : 'chevron-down'}
            size={18}
            color={COLORS.textMuted}
          />
        </TouchableOpacity>

        {isOpen && count > 0 && (
          <View style={styles.serviceList}>
            <View style={styles.serviceListDivider} />
            {catServices.map((svc, idx) =>
              renderServiceRow(svc, idx, idx === catServices.length - 1)
            )}
          </View>
        )}

        {isOpen && count === 0 && (
          <View style={styles.serviceListEmpty}>
            <Text style={styles.serviceListEmptyText}>No services in this category</Text>
          </View>
        )}
      </View>
    );
  };



  const hasServices = Object.keys(services).length > 0;

  // ─── Main render ──────────────────────────────────────────────────────────
  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />

      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Menu</Text>
        <View style={{ width: 40 }} />
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
        <Animated.View style={{ 
          opacity: fadeAnim,
          transform: [{ translateY: slideAnim }]
        }}>

          {/* Profile Card */}
          <TouchableOpacity
            style={styles.profileCard}
            activeOpacity={0.8}
            onPress={() => router.push('/profile/edit')}
          >
            <View style={styles.profileRow}>
              <LinearGradient
                colors={COLORS.gradientPrimary}
                style={styles.avatar}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 1 }}
              >
                <Text style={styles.avatarText}>{getInitials(customer?.name)}</Text>
              </LinearGradient>
              <View style={styles.profileInfo}>
                <Text style={styles.profileName}>{customer?.name || 'User'}</Text>
                <Text style={styles.profileEmail}>{customer?.email || 'View your profile'}</Text>
              </View>
              <View style={styles.profileNotificationBadge}>
                <Ionicons name="chevron-forward" size={20} color={COLORS.textMuted} />
              </View>
            </View>

            <View style={styles.miniStatsRow}>
              <View style={styles.miniStat}>
                <Text style={styles.miniStatValue}>{stats.totalLaundries}</Text>
                <Text style={styles.miniStatLabel}>Laundries</Text>
              </View>
              <View style={styles.miniStatDivider} />
              <View style={styles.miniStat}>
                <Text style={styles.miniStatValue}>{formatCurrency(stats.totalSpent)}</Text>
                <Text style={styles.miniStatLabel}>Spent</Text>
              </View>
              <View style={styles.miniStatDivider} />
              <View style={styles.miniStat}>
                <View style={styles.miniRating}>
                  <Ionicons name="star" size={14} color={COLORS.warning} />
                  <Text style={styles.miniStatValue}>{(stats.rating || 0).toFixed(1)}</Text>
                </View>
                <Text style={styles.miniStatLabel}>Rating</Text>
              </View>
            </View>
          </TouchableOpacity>

          {/* Primary Menu */}
          <View style={styles.menuSection}>
            <View style={styles.menuCard}>
              {primaryMenuItems.map((item, index) => 
                renderMenuItem(item, index, index === primaryMenuItems.length - 1)
              )}
            </View>
          </View>

          {/* ══════ SERVICES SECTION ══════ */}
          <View style={styles.menuSection}>
            <View style={styles.sectionTitleRow}>
              <Text style={styles.sectionTitle}>Our Services</Text>
              {servicesLoading && (
                <ActivityIndicator
                  size="small"
                  color={COLORS.primary}
                  style={{ marginLeft: 8 }}
                />
              )}
            </View>

            {hasServices ? (
              <View style={styles.servicesContainer}>
                {/* Render in defined order, then any extras */}
                {[
                  ...CATEGORY_ORDER,
                  ...Object.keys(services).filter(k => !CATEGORY_ORDER.includes(k)),
                ]
                  .filter(cat => services[cat]?.length > 0)
                  .map(cat => renderCategoryCard(cat, services[cat]))}
              </View>
            ) : servicesLoading ? (
              <View style={styles.servicesPlaceholder}>
                <ActivityIndicator color={COLORS.primary} />
                <Text style={styles.servicesPlaceholderText}>Loading services...</Text>
              </View>
            ) : (
              <View style={styles.servicesPlaceholder}>
                <Ionicons name="water-outline" size={32} color={COLORS.textMuted} />
                <Text style={styles.servicesPlaceholderText}>No services available</Text>
              </View>
            )}
          </View>
          {/* ══════════════════════════════ */}

          {/* Branches Section */}
          <View style={styles.menuSection}>
            <View style={styles.branchesCard}>
              <TouchableOpacity
                style={styles.branchesHeader}
                activeOpacity={0.75}
                onPress={() => {
                  setBranchesExpanded(!branchesExpanded);
                  setCurrentTime(new Date()); // Update time when expanding
                }}
              >
                <LinearGradient
                  colors={['#10B981', '#059669']}
                  style={styles.branchesIcon}
                  start={{ x: 0, y: 0 }}
                  end={{ x: 1, y: 1 }}
                >
                  <Ionicons name="business" size={20} color="#fff" />
                </LinearGradient>
                <View style={styles.branchesMeta}>
                  <Text style={styles.branchesLabel}>Our Branches</Text>
                  <Text style={styles.branchesCount}>
                    {branches.length} branch{branches.length !== 1 ? 'es' : ''}
                  </Text>
                </View>
                {branchesLoading ? (
                  <ActivityIndicator size="small" color={COLORS.primary} />
                ) : (
                  <Ionicons
                    name={branchesExpanded ? 'chevron-up' : 'chevron-down'}
                    size={18}
                    color={COLORS.textMuted}
                  />
                )}
              </TouchableOpacity>

              {branchesExpanded && branches.length > 0 && (
                <View style={styles.branchesList}>
                  <View style={styles.branchesListDivider} />
                  {branches.map((branch, index) => (
                    <React.Fragment key={branch.id ?? index}>
                      <View style={styles.branchRow}>
                        <View style={styles.branchRowIcon}>
                          <Ionicons name="location" size={14} color={COLORS.primary} />
                        </View>
                        <View style={styles.branchRowInfo}>
                          <Text style={styles.branchRowName}>{branch.name}</Text>
                          <Text style={styles.branchRowAddress}>{branch.address}</Text>
                          <Text style={styles.branchRowHours}>
                            {formatOperatingHours(branch.operating_hours)}
                          </Text>
                        </View>
                        <View style={[styles.branchRowStatus, { 
                          backgroundColor: isCurrentlyOpen(branch.operating_hours) ? COLORS.success + '20' : COLORS.textMuted + '20' 
                        }]}>
                          <Text style={[styles.branchRowStatusText, { 
                            color: isCurrentlyOpen(branch.operating_hours) ? COLORS.success : COLORS.textMuted 
                          }]}>
                            {isCurrentlyOpen(branch.operating_hours) ? 'Open' : 'Closed'}
                          </Text>
                        </View>
                      </View>
                      {index < branches.length - 1 && <View style={styles.branchRowDivider} />}
                    </React.Fragment>
                  ))}
                </View>
              )}

              {branchesExpanded && branches.length === 0 && (
                <View style={styles.branchesListEmpty}>
                  <Text style={styles.branchesListEmptyText}>No branches available</Text>
                </View>
              )}
            </View>
          </View>

          {/* Secondary Menu */}
          <View style={styles.menuSection}>
            <Text style={styles.sectionTitle}>More</Text>
            <View style={styles.menuCard}>
              {secondaryMenuItems.map((item, index) => 
                renderMenuItem(item, index, index === secondaryMenuItems.length - 1)
              )}
            </View>
          </View>

          {/* Support Section */}
          <View style={styles.menuSection}>
            <Text style={styles.sectionTitle}>Support & Legal</Text>
            <View style={styles.menuCard}>
              {supportMenuItems.map((item, index) => 
                renderMenuItem(item, index, index === supportMenuItems.length - 1)
              )}
            </View>
          </View>

          {/* Logout */}
          <View style={styles.logoutSection}>
            <TouchableOpacity
              style={styles.logoutButton}
              onPress={handleLogout}
              disabled={loggingOut}
              activeOpacity={0.8}
            >
              <LinearGradient
                colors={['rgba(239, 68, 68, 0.1)', 'rgba(239, 68, 68, 0.05)']}
                style={styles.logoutGradient}
              >
                {loggingOut ? (
                  <ActivityIndicator color={COLORS.danger} size="small" />
                ) : (
                  <>
                    <Ionicons name="log-out-outline" size={22} color={COLORS.danger} />
                    <Text style={styles.logoutText}>Log Out</Text>
                  </>
                )}
              </LinearGradient>
            </TouchableOpacity>
          </View>

          {/* App Info */}
          <View style={styles.appInfoSection}>
            <Text style={styles.appInfoTitle}>WashBox v2.1.0</Text>
            <Text style={styles.appInfoText}>© {new Date().getFullYear()} WashBox Laundry Services</Text>
          </View>

          <View style={{ height: 40 }} />
        </Animated.View>
      </ScrollView>

      <TermsModal
        visible={showTermsModal}
        onClose={() => setShowTermsModal(false)}
      />
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
  headerTitle: {
    fontSize: 32,
    fontWeight: '800',
    color: COLORS.textPrimary,
    letterSpacing: -0.5,
  },
  headerIconButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.cardDark,
    justifyContent: 'center',
    alignItems: 'center',
  },

  scrollView: { flex: 1 },
  scrollContent: { paddingHorizontal: 20 },

  // Profile Card
  profileCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 24,
    padding: 24,
    marginBottom: 24,
    borderWidth: 1,
    borderColor: COLORS.border,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 12,
    elevation: 8,
  },
  profileRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 24,
  },
  avatar: {
    width: 72,
    height: 72,
    borderRadius: 36,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 3,
    borderColor: 'rgba(255,255,255,0.1)',
    shadowColor: COLORS.primary,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 4,
  },
  avatarText: {
    fontSize: 28,
    fontWeight: '900',
    color: COLORS.textPrimary,
    letterSpacing: 1.2,
    textShadowColor: 'rgba(0,0,0,0.3)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 2,
  },
  profileInfo: {
    flex: 1,
    marginLeft: 20,
    justifyContent: 'center',
  },
  profileName: {
    fontSize: 22,
    fontWeight: '800',
    color: COLORS.textPrimary,
    marginBottom: 6,
    letterSpacing: -0.3,
  },
  profileEmail: {
    fontSize: 14,
    color: COLORS.textSecondary,
    fontWeight: '500',
  },
  profileNotificationBadge: {
    marginLeft: 12,
    backgroundColor: 'rgba(255,255,255,0.08)',
    borderRadius: 20,
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
  },

  miniStatsRow: {
    flexDirection: 'row',
    backgroundColor: 'rgba(14,165,233,0.08)',
    borderRadius: 18,
    padding: 18,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: 'rgba(14,165,233,0.15)',
  },
  miniStat: {
    flex: 1,
    alignItems: 'center',
  },
  miniStatDivider: {
    width: 1,
    height: 36,
    backgroundColor: 'rgba(255,255,255,0.15)',
  },
  miniStatValue: {
    fontSize: 18,
    fontWeight: '800',
    color: COLORS.textPrimary,
    marginBottom: 4,
    letterSpacing: -0.2,
  },
  miniStatLabel: {
    fontSize: 11,
    color: COLORS.textSecondary,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 0.8,
  },
  miniRating: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },

  // Sections
  menuSection: { marginBottom: 20 },
  sectionTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 10,
    marginLeft: 4,
  },
  sectionTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.textSecondary,
    textTransform: 'uppercase',
    letterSpacing: 1,
  },

  // Generic menu card (existing items)
  menuCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 20,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    gap: 14,
  },
  menuIconContainer: {
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
  },
  menuContent: { flex: 1 },
  menuLabel: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginBottom: 2,
  },
  menuSubtitle: {
    fontSize: 12,
    color: COLORS.textSecondary,
  },
  menuDivider: {
    height: 1,
    backgroundColor: COLORS.border,
    marginLeft: 74,
  },

  // ─── Services ───────────────────────────────────────────────────────────
  servicesContainer: { gap: 10 },

  // Category card
  categoryCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
    overflow: 'hidden',
  },
  categoryHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    gap: 14,
  },
  categoryIcon: {
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
  },
  categoryMeta: { flex: 1 },
  categoryLabel: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 2,
  },
  categoryCount: {
    fontSize: 12,
    color: COLORS.textSecondary,
  },

  // Expanded service list
  serviceList: {
    paddingHorizontal: 16,
    paddingBottom: 8,
  },
  serviceListDivider: {
    height: 1,
    backgroundColor: COLORS.border,
    marginBottom: 4,
  },
  serviceListEmpty: {
    paddingHorizontal: 16,
    paddingBottom: 14,
    alignItems: 'center',
  },
  serviceListEmptyText: {
    fontSize: 12,
    color: COLORS.textMuted,
    fontStyle: 'italic',
  },

  // Individual service row
  serviceRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    gap: 12,
  },
  serviceTypeDot: {
    width: 32,
    height: 32,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
  },
  serviceRowInfo: { flex: 1 },
  serviceRowName: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginBottom: 2,
  },
  serviceRowMeta: {
    fontSize: 11,
    color: COLORS.textMuted,
  },
  serviceRowPrice: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.primary,
  },
  serviceRowDivider: {
    height: 1,
    backgroundColor: COLORS.border,
    marginLeft: 44,
  },

  // Services placeholder (loading / empty)
  servicesPlaceholder: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
    padding: 32,
    alignItems: 'center',
    gap: 10,
  },
  servicesPlaceholderText: {
    fontSize: 13,
    color: COLORS.textMuted,
  },

  // Logout
  logoutSection: { marginBottom: 20 },
  logoutButton: {
    borderRadius: 16,
    overflow: 'hidden',
  },
  logoutGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 18,
    gap: 12,
  },
  logoutText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.danger,
  },

  // App Info
  appInfoSection: {
    alignItems: 'center',
    paddingVertical: 8,
  },
  appInfoTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.textMuted,
    marginBottom: 4,
  },
  appInfoText: {
    fontSize: 11,
    color: COLORS.textMuted,
  },

  // ─── Branches ───────────────────────────────────────────────────────────
  branchesCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
    overflow: 'hidden',
  },
  branchesHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    gap: 14,
  },
  branchesIcon: {
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
  },
  branchesMeta: {
    flex: 1,
  },
  branchesLabel: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 2,
  },
  branchesCount: {
    fontSize: 12,
    color: COLORS.textSecondary,
  },
  branchesList: {
    paddingHorizontal: 16,
    paddingBottom: 8,
  },
  branchesListDivider: {
    height: 1,
    backgroundColor: COLORS.border,
    marginBottom: 4,
  },
  branchesListEmpty: {
    paddingHorizontal: 16,
    paddingBottom: 14,
    alignItems: 'center',
  },
  branchesListEmptyText: {
    fontSize: 12,
    color: COLORS.textMuted,
    fontStyle: 'italic',
  },
  branchRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    paddingVertical: 12,
    gap: 12,
  },
  branchRowIcon: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: COLORS.primary + '20',
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 2,
  },
  branchRowInfo: {
    flex: 1,
  },
  branchRowName: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginBottom: 2,
  },
  branchRowAddress: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginBottom: 2,
  },
  branchRowHours: {
    fontSize: 11,
    color: COLORS.textMuted,
  },
  branchRowStatus: {
    paddingHorizontal: 6,
    paddingVertical: 3,
    borderRadius: 6,
    marginTop: 2,
  },
  branchRowStatusText: {
    fontSize: 10,
    fontWeight: '600',
    textTransform: 'uppercase',
  },
  branchRowDivider: {
    height: 1,
    backgroundColor: COLORS.border,
    marginLeft: 44,
  },
});