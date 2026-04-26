import React, { useState, useEffect } from 'react';
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
  Clipboard,
  Alert,
  Modal,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { router } from 'expo-router';
import { API_BASE_URL } from '../../constants/config';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

const COLORS = {
  background: '#0A0E27',
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
  gradientWarning: ['#F59E0B', '#EF4444'],
};

export default function PromotionsScreen() {
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [promotions, setPromotions] = useState([]);
  const [filter, setFilter] = useState('all'); // all, active, featured
  const [fadeAnim] = useState(new Animated.Value(0));
  const [selectedPromo, setSelectedPromo] = useState(null);
  const [showDetails, setShowDetails] = useState(false);
  const [detailsLoading, setDetailsLoading] = useState(false);

  useEffect(() => {
    fetchPromotions();
  }, []);

  useEffect(() => {
    if (!loading) {
      Animated.timing(fadeAnim, {
        toValue: 1,
        duration: 600,
        useNativeDriver: true,
      }).start();
    }
  }, [loading]);

  const fetchPromotions = async () => {
    try {
      setLoading(true);
      
      const response = await fetch(`${API_BASE_URL}/v1/promotions`, {
        headers: { 'Accept': 'application/json' },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data.promotions) {
          setPromotions(data.data.promotions);
        }
      }
    } catch (error) {
      console.error('Error fetching promotions:', error);
    } finally {
      setLoading(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchPromotions();
    setRefreshing(false);
  };

  const getFilteredPromotions = () => {
    switch (filter) {
      case 'active':
        return promotions.filter(p => p.is_active);
      case 'featured':
        return promotions.filter(p => p.is_featured);
      default:
        return promotions;
    }
  };

  const filteredPromotions = getFilteredPromotions();

  const openDetails = async (promo) => {
    setSelectedPromo(promo);
    setShowDetails(true);
    setDetailsLoading(true);
    try {
      const response = await fetch(`${API_BASE_URL}/v1/promotions/${promo.id}`, {
        headers: { 'Accept': 'application/json' },
      });
      if (response.ok) {
        const data = await response.json();
        if (data.success) setSelectedPromo(data.data.promotion);
      }
    } catch (e) {
      console.error('Error fetching promo details:', e);
    } finally {
      setDetailsLoading(false);
    }
  };

  const handleUsePromo = (promo) => {
    if (promo.promo_code) {
      Clipboard.setString(promo.promo_code);
      Alert.alert(
        '✅ Code Copied!',
        `Promo code "${promo.promo_code}" copied to clipboard.\n\nUse it when scheduling your pickup.`,
        [
          { text: 'Schedule Pickup', onPress: () => router.push('/(tabs)/pickup') },
          { text: 'OK', style: 'cancel' },
        ]
      );
    } else {
      // No code needed — auto-applied promotion
      Alert.alert(
        '🎉 Offer Applied!',
        `"${promo.name}" will be automatically applied to your next order.`,
        [
          { text: 'Schedule Pickup', onPress: () => router.push('/(tabs)/pickup') },
          { text: 'OK', style: 'cancel' },
        ]
      );
    }
  };

  if (loading) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Loading promotions...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />

      {/* Header */}
      <LinearGradient
        colors={['rgba(14, 165, 233, 0.15)', 'rgba(59, 130, 246, 0.05)']}
        style={styles.header}
      >
        <View style={styles.headerContent}>
          <TouchableOpacity 
            style={styles.backButton}
            onPress={() => router.back()}
          >
            <Ionicons name="arrow-back" size={24} color={COLORS.textPrimary} />
          </TouchableOpacity>
          
          <View style={styles.headerTextContainer}>
            <Text style={styles.headerTitle}>Promo Packages</Text>
            <Text style={styles.headerSubtitle}>
              {filteredPromotions.length} {filteredPromotions.length === 1 ? 'offer' : 'offers'} available
            </Text>
          </View>

          <View style={styles.headerSpacer} />
        </View>
      </LinearGradient>

      <ScrollView
        style={styles.content}
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
        <Animated.View style={{ opacity: fadeAnim }}>
          {/* Filters */}
          <View style={styles.filtersContainer}>
            <ScrollView
              horizontal
              showsHorizontalScrollIndicator={false}
              contentContainerStyle={styles.filtersScroll}
            >
              <TouchableOpacity
                style={[styles.filterChip, filter === 'all' && styles.filterChipActive]}
                onPress={() => setFilter('all')}
              >
                <Text style={[styles.filterText, filter === 'all' && styles.filterTextActive]}>
                  All Offers
                </Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[styles.filterChip, filter === 'active' && styles.filterChipActive]}
                onPress={() => setFilter('active')}
              >
                <Text style={[styles.filterText, filter === 'active' && styles.filterTextActive]}>
                  Active
                </Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[styles.filterChip, filter === 'featured' && styles.filterChipActive]}
                onPress={() => setFilter('featured')}
              >
                <Ionicons 
                  name="star" 
                  size={14} 
                  color={filter === 'featured' ? COLORS.textPrimary : COLORS.textSecondary} 
                  style={{ marginRight: 4 }}
                />
                <Text style={[styles.filterText, filter === 'featured' && styles.filterTextActive]}>
                  Featured
                </Text>
              </TouchableOpacity>
            </ScrollView>
          </View>

          {/* Promotions Grid */}
          {filteredPromotions.length > 0 ? (
            <View style={styles.promotionsGrid}>
              {filteredPromotions.map((promo, index) => {
                const hasBackgroundImage = promo.banner_image;
                const gradientColors = index % 2 === 0 ? COLORS.gradientPrimary : COLORS.gradientSecondary;
                
                return (
                  <TouchableOpacity
                    key={promo.id}
                    style={styles.promoCard}
                    activeOpacity={0.9}
                    onPress={() => openDetails(promo)}
                  >
                    {hasBackgroundImage ? (
                      <View style={styles.promoImageContainer}>
                        <Image
                          source={{ uri: promo.banner_image }}
                          style={styles.promoBackgroundImage}
                          resizeMode="cover"
                        />
                        <LinearGradient
                          colors={['rgba(0,0,0,0.3)', 'rgba(0,0,0,0.7)']}
                          style={styles.promoImageOverlay}
                          start={{ x: 0, y: 0 }}
                          end={{ x: 0, y: 1 }}
                        >
                          <View style={styles.promoContentWithImage}>
                            {/* Badges */}
                            <View style={styles.promoBadges}>
                              {promo.is_featured && (
                                <View style={styles.featuredBadge}>
                                  <Ionicons name="star" size={10} color={COLORS.warning} />
                                  <Text style={styles.featuredBadgeText}>FEATURED</Text>
                                </View>
                              )}
                              {promo.is_active && (
                                <View style={styles.activeBadge}>
                                  <View style={styles.activeDot} />
                                  <Text style={styles.activeBadgeText}>ACTIVE</Text>
                                </View>
                              )}
                            </View>

                            {/* Content */}
                            <View style={styles.promoContent}>
                              <Text style={styles.promoTitle} numberOfLines={2}>
                                {promo.poster_title || promo.name}
                              </Text>
                              
                              <Text style={styles.promoSubtitle} numberOfLines={2}>
                                {promo.poster_subtitle || promo.description}
                              </Text>

                              {/* Price */}
                              {promo.display_price && (
                                <View style={styles.promoPriceContainer}>
                                  <Text style={styles.promoPrice}>₱{promo.display_price}</Text>
                                  {promo.original_price && (
                                    <Text style={styles.promoOriginalPrice}>
                                      ₱{promo.original_price}
                                    </Text>
                                  )}
                                </View>
                              )}

                              {/* Features */}
                              {promo.poster_features && promo.poster_features.length > 0 && (
                                <View style={styles.promoFeatures}>
                                  {promo.poster_features.slice(0, 2).map((feature, idx) => (
                                    <View key={idx} style={styles.featureRow}>
                                      <Ionicons 
                                        name="checkmark-circle" 
                                        size={12} 
                                        color="rgba(255,255,255,0.9)" 
                                      />
                                      <Text style={styles.featureText} numberOfLines={1}>
                                        {feature}
                                      </Text>
                                    </View>
                                  ))}
                                </View>
                              )}

                              {/* Valid Until */}
                              {promo.end_date && (
                                <View style={styles.validityContainer}>
                                  <Ionicons name="time-outline" size={12} color="rgba(255,255,255,0.7)" />
                                  <Text style={styles.validityText}>
                                    Valid until {new Date(promo.end_date).toLocaleDateString()}
                                  </Text>
                                </View>
                              )}
                            </View>

                            {/* Use Button */}
                            <TouchableOpacity
                              style={styles.useButton}
                              onPress={() => handleUsePromo(promo)}
                              activeOpacity={0.85}
                            >
                              {promo.promo_code ? (
                                <>
                                  <Ionicons name="copy-outline" size={16} color="#FFF" />
                                  <Text style={styles.useButtonText}>Copy Code: {promo.promo_code}</Text>
                                </>
                              ) : (
                                <>
                                  <Ionicons name="checkmark-circle" size={16} color="#FFF" />
                                  <Text style={styles.useButtonText}>Use This Offer</Text>
                                </>
                              )}
                            </TouchableOpacity>
                          </View>
                        </LinearGradient>
                      </View>
                    ) : (
                      <LinearGradient
                        colors={gradientColors}
                        style={styles.promoGradient}
                        start={{ x: 0, y: 0 }}
                        end={{ x: 1, y: 1 }}
                      >
                        {/* Badges */}
                        <View style={styles.promoBadges}>
                          {promo.is_featured && (
                            <View style={styles.featuredBadge}>
                              <Ionicons name="star" size={10} color={COLORS.warning} />
                              <Text style={styles.featuredBadgeText}>FEATURED</Text>
                            </View>
                          )}
                          {promo.is_active && (
                            <View style={styles.activeBadge}>
                              <View style={styles.activeDot} />
                              <Text style={styles.activeBadgeText}>ACTIVE</Text>
                            </View>
                          )}
                        </View>

                        {/* Content */}
                        <View style={styles.promoContent}>
                          <Text style={styles.promoTitle} numberOfLines={2}>
                            {promo.poster_title || promo.name}
                          </Text>
                          
                          <Text style={styles.promoSubtitle} numberOfLines={2}>
                            {promo.poster_subtitle || promo.description}
                          </Text>

                          {/* Price */}
                          {promo.display_price && (
                            <View style={styles.promoPriceContainer}>
                              <Text style={styles.promoPrice}>₱{promo.display_price}</Text>
                              {promo.original_price && (
                                <Text style={styles.promoOriginalPrice}>
                                  ₱{promo.original_price}
                                </Text>
                              )}
                            </View>
                          )}

                          {/* Features */}
                          {promo.poster_features && promo.poster_features.length > 0 && (
                            <View style={styles.promoFeatures}>
                              {promo.poster_features.slice(0, 2).map((feature, idx) => (
                                <View key={idx} style={styles.featureRow}>
                                  <Ionicons 
                                    name="checkmark-circle" 
                                    size={12} 
                                    color="rgba(255,255,255,0.9)" 
                                  />
                                  <Text style={styles.featureText} numberOfLines={1}>
                                    {feature}
                                  </Text>
                                </View>
                              ))}
                            </View>
                          )}

                          {/* Valid Until */}
                          {promo.end_date && (
                            <View style={styles.validityContainer}>
                              <Ionicons name="time-outline" size={12} color="rgba(255,255,255,0.7)" />
                              <Text style={styles.validityText}>
                                Valid until {new Date(promo.end_date).toLocaleDateString()}
                              </Text>
                            </View>
                          )}
                        </View>

                        {/* Use Button */}
                        <TouchableOpacity
                          style={styles.useButton}
                          onPress={() => handleUsePromo(promo)}
                          activeOpacity={0.85}
                        >
                          {promo.promo_code ? (
                            <>
                              <Ionicons name="copy-outline" size={16} color="#FFF" />
                              <Text style={styles.useButtonText}>Copy Code: {promo.promo_code}</Text>
                            </>
                          ) : (
                            <>
                              <Ionicons name="checkmark-circle" size={16} color="#FFF" />
                              <Text style={styles.useButtonText}>Use This Offer</Text>
                            </>
                          )}
                        </TouchableOpacity>
                      </LinearGradient>
                    )}
                  </TouchableOpacity>
                );
              })}
            </View>
          ) : (
            // Empty State
            <View style={styles.emptyStateContainer}>
              <LinearGradient
                colors={['rgba(14, 165, 233, 0.1)', 'rgba(59, 130, 246, 0.05)']}
                style={styles.emptyStateCard}
              >
                <View style={styles.emptyStateIcon}>
                  <Ionicons name="gift-outline" size={48} color={COLORS.primary} />
                </View>
                <Text style={styles.emptyStateTitle}>No Offers Found</Text>
                <Text style={styles.emptyStateText}>
                  {filter === 'all' 
                    ? 'Check back later for exciting promotions!' 
                    : `No ${filter} offers available at the moment.`}
                </Text>
                {filter !== 'all' && (
                  <TouchableOpacity
                    style={styles.emptyStateButton}
                    onPress={() => setFilter('all')}
                  >
                    <Text style={styles.emptyStateButtonText}>View All Offers</Text>
                  </TouchableOpacity>
                )}
              </LinearGradient>
            </View>
          )}

          {/* Bottom Spacing */}
          <View style={{ height: 40 }} />
        </Animated.View>
      </ScrollView>

      {/* ─── Promo Details Modal ─── */}
      <Modal
        visible={showDetails}
        animationType="slide"
        presentationStyle="pageSheet"
        onRequestClose={() => setShowDetails(false)}
      >
        <View style={styles.detailsContainer}>
          {/* Header */}
          <View style={styles.detailsHeader}>
            <TouchableOpacity
              style={styles.detailsCloseBtn}
              onPress={() => setShowDetails(false)}
            >
              <Ionicons name="close" size={22} color={COLORS.textPrimary} />
            </TouchableOpacity>
            <Text style={styles.detailsHeaderTitle}>Promo Details</Text>
            <View style={{ width: 40 }} />
          </View>

          {detailsLoading ? (
            <View style={styles.detailsLoading}>
              <ActivityIndicator size="large" color={COLORS.primary} />
              <Text style={{ color: COLORS.textSecondary, marginTop: 12 }}>Loading details...</Text>
            </View>
          ) : selectedPromo ? (
            <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ paddingBottom: 40 }}>
              {/* Banner with price overlay centered */}
              {selectedPromo.banner_image ? (
                <View style={styles.detailsBannerWrap}>
                  <Image
                    source={{ uri: selectedPromo.banner_image }}
                    style={styles.detailsBanner}
                    resizeMode="cover"
                  />
                  <View style={styles.detailsBannerOverlay}>
                    <Text style={styles.detailsOverlayOnly}>ONLY</Text>
                    <View style={styles.detailsOverlayPriceRow}>
                      <Text style={styles.detailsOverlayCurrency}>₱</Text>
                      <Text style={styles.detailsOverlayPrice}>{selectedPromo.display_price}</Text>
                    </View>
                    <Text style={styles.detailsOverlayUnit}>
                      {selectedPromo.price_unit || 'per load'}
                    </Text>
                  </View>
                </View>
              ) : (
                <LinearGradient
                  colors={COLORS.gradientPrimary}
                  style={styles.detailsBannerGradient}
                  start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }}
                >
                  <Text style={styles.detailsOverlayOnly}>ONLY</Text>
                  <View style={styles.detailsOverlayPriceRow}>
                    <Text style={styles.detailsOverlayCurrency}>₱</Text>
                    <Text style={styles.detailsOverlayPrice}>{selectedPromo.display_price}</Text>
                  </View>
                  <Text style={styles.detailsOverlayUnit}>
                    {selectedPromo.price_unit || 'per load'}
                  </Text>
                </LinearGradient>
              )}

              <View style={styles.detailsBody}>
                {/* Badges */}
                <View style={styles.promoBadges}>
                  {selectedPromo.is_featured && (
                    <View style={styles.featuredBadge}>
                      <Ionicons name="star" size={10} color={COLORS.warning} />
                      <Text style={styles.featuredBadgeText}>FEATURED</Text>
                    </View>
                  )}
                  {selectedPromo.is_active && (
                    <View style={styles.activeBadge}>
                      <View style={styles.activeDot} />
                      <Text style={styles.activeBadgeText}>ACTIVE</Text>
                    </View>
                  )}
                </View>

                {/* Title & Price */}
                <Text style={styles.detailsTitle}>
                  {selectedPromo.poster_title || selectedPromo.name}
                </Text>

                {/* original_price shown below if exists */}
                {selectedPromo.original_price && (
                  <Text style={[styles.detailsOriginalPrice, { textAlign: 'center', marginBottom: 16 }]}>
                    Regular price: ₱{selectedPromo.original_price}
                  </Text>
                )}

                {/* Description */}
                {selectedPromo.description && (
                  <View style={styles.detailsSection}>
                    <Text style={styles.detailsSectionTitle}>About this Package</Text>
                    <Text style={styles.detailsDescription}>{selectedPromo.description}</Text>
                  </View>
                )}

                {/* What's Included */}
                {selectedPromo.poster_features?.length > 0 && (
                  <View style={styles.detailsSection}>
                    <Text style={styles.detailsSectionTitle}>What's Included</Text>
                    <View style={styles.detailsItemsList}>
                      {selectedPromo.poster_features.map((item, i) => (
                        <View key={i} style={styles.detailsItem}>
                          <View style={styles.detailsFreeBadge}>
                            <Text style={styles.detailsFreeBadgeText}>FREE</Text>
                          </View>
                          <Text style={styles.detailsItemText}>{item}</Text>
                        </View>
                      ))}
                    </View>
                  </View>
                )}

                {/* Notes */}
                {selectedPromo.poster_notes && (
                  <View style={styles.detailsNotesCard}>
                    <Ionicons name="information-circle" size={18} color={COLORS.warning} />
                    <Text style={styles.detailsNotesText}>{selectedPromo.poster_notes}</Text>
                  </View>
                )}

                {/* Validity */}
                {(selectedPromo.start_date || selectedPromo.end_date) && (
                  <View style={styles.detailsSection}>
                    <Text style={styles.detailsSectionTitle}>Validity</Text>
                    <View style={styles.detailsValidityRow}>
                      <Ionicons name="calendar-outline" size={16} color={COLORS.primary} />
                      <Text style={styles.detailsValidityText}>
                        {selectedPromo.start_date} → {selectedPromo.end_date}
                      </Text>
                    </View>
                  </View>
                )}

                {/* Branch */}
                {selectedPromo.branch && (
                  <View style={styles.detailsBranchRow}>
                    <Ionicons name="business-outline" size={16} color={COLORS.textMuted} />
                    <Text style={styles.detailsBranchText}>{selectedPromo.branch.name}</Text>
                  </View>
                )}

                {/* Use Button */}
                <TouchableOpacity
                  style={styles.detailsUseBtn}
                  onPress={async () => {
                    setShowDetails(false);
                    // Store promo in AsyncStorage since tab screens don't receive router params
                    const AsyncStorage = require('@react-native-async-storage/async-storage').default;
                    await AsyncStorage.setItem('@washbox:pending_promo', JSON.stringify({
                      promoId: selectedPromo.id,
                      promoName: selectedPromo.poster_title || selectedPromo.name,
                      promoPrice: selectedPromo.display_price,
                      promoPriceUnit: selectedPromo.price_unit || 'per load',
                      promoCode: selectedPromo.promo_code || '',
                      promoBanner: selectedPromo.banner_image || '',
                    }));
                    router.push('/(tabs)/pickup');
                  }}
                  activeOpacity={0.85}
                >
                  <LinearGradient
                    colors={COLORS.gradientPrimary}
                    style={styles.detailsUseBtnGradient}
                    start={{ x: 0, y: 0 }} end={{ x: 1, y: 0 }}
                  >
                    <Ionicons
                      name={selectedPromo.promo_code ? 'copy-outline' : 'checkmark-circle'}
                      size={20}
                      color="#FFF"
                    />
                    <Text style={styles.detailsUseBtnText}>
                      {selectedPromo.promo_code
                        ? `Copy Code: ${selectedPromo.promo_code}`
                        : 'Use This Package'}
                    </Text>
                  </LinearGradient>
                </TouchableOpacity>
              </View>
            </ScrollView>
          ) : null}
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
    paddingTop: Platform.OS === 'ios' ? 60 : 50,
    paddingBottom: 20,
    paddingHorizontal: 20,
    borderBottomLeftRadius: 24,
    borderBottomRightRadius: 24,
  },
  headerContent: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  backButton: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: 'rgba(255,255,255,0.1)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerTextContainer: {
    flex: 1,
    marginLeft: 16,
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: '800',
    color: COLORS.textPrimary,
    marginBottom: 2,
  },
  headerSubtitle: {
    fontSize: 13,
    color: COLORS.textSecondary,
    fontWeight: '500',
  },
  headerSpacer: {
    width: 44,
  },

  // Content
  content: {
    flex: 1,
  },
  scrollContent: {
    paddingTop: 20,
  },

  // Filters
  filtersContainer: {
    marginBottom: 24,
  },
  filtersScroll: {
    paddingHorizontal: 20,
    gap: 12,
  },
  filterChip: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 20,
    backgroundColor: COLORS.cardDark,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  filterChipActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  filterText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },
  filterTextActive: {
    color: COLORS.textPrimary,
  },

  // Promotions Grid
  promotionsGrid: {
    paddingHorizontal: 20,
    gap: 16,
  },
  promoCard: {
    borderRadius: 20,
    overflow: 'hidden',
    marginBottom: 4,
    elevation: 6,
    shadowColor: COLORS.primary,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 12,
  },
  promoGradient: {
    padding: 24,
    minHeight: 320,
  },
  promoImageContainer: {
    position: 'relative',
    minHeight: 320,
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
    justifyContent: 'space-between',
  },
  promoBadges: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 16,
  },
  featuredBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(245, 158, 11, 0.2)',
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 12,
    gap: 4,
  },
  featuredBadgeText: {
    color: COLORS.warning,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 0.5,
  },
  activeBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(16, 185, 129, 0.2)',
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 12,
    gap: 4,
  },
  activeDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: COLORS.success,
  },
  activeBadgeText: {
    color: COLORS.success,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 0.5,
  },
  promoContent: {
    flex: 1,
  },
  promoTitle: {
    fontSize: 26,
    fontWeight: '800',
    color: COLORS.textPrimary,
    marginBottom: 10,
    lineHeight: 32,
  },
  promoSubtitle: {
    fontSize: 15,
    color: 'rgba(255, 255, 255, 0.9)',
    marginBottom: 18,
    lineHeight: 22,
  },
  promoPriceContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginBottom: 14,
  },
  promoPrice: {
    fontSize: 36,
    fontWeight: '800',
    color: COLORS.textPrimary,
  },
  promoOriginalPrice: {
    fontSize: 20,
    color: 'rgba(255, 255, 255, 0.6)',
    textDecorationLine: 'line-through',
  },
  promoFeatures: {
    gap: 10,
    marginBottom: 14,
  },
  featureRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  featureText: {
    flex: 1,
    color: 'rgba(255, 255, 255, 0.9)',
    fontSize: 13,
    fontWeight: '500',
  },
  validityContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginTop: 8,
  },
  validityText: {
    fontSize: 11,
    color: 'rgba(255, 255, 255, 0.7)',
    fontWeight: '500',
  },
  promoArrow: {
    alignSelf: 'flex-end',
    marginTop: 12,
  },
  useButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    backgroundColor: 'rgba(255,255,255,0.2)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.3)',
    paddingVertical: 10,
    paddingHorizontal: 16,
    borderRadius: 12,
    marginTop: 18,
    alignSelf: 'flex-end',
  },
  useButtonText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#FFF',
    letterSpacing: 0.3,
  },

  // Empty State
  emptyStateContainer: {
    paddingHorizontal: 20,
  },
  emptyStateCard: {
    borderRadius: 20,
    padding: 40,
    alignItems: 'center',
  },
  emptyStateIcon: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: 'rgba(14, 165, 233, 0.15)',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
  },
  emptyStateTitle: {
    fontSize: 20,
    fontWeight: '800',
    color: COLORS.textPrimary,
    marginBottom: 8,
  },
  emptyStateText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: 24,
  },
  emptyStateButton: {
    backgroundColor: COLORS.primary,
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 12,
  },
  emptyStateButtonText: {
    color: COLORS.textPrimary,
    fontSize: 14,
    fontWeight: '700',
  },

  // Details Modal
  detailsContainer: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  detailsHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingTop: Platform.OS === 'ios' ? 56 : 44,
    paddingBottom: 16,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  detailsCloseBtn: {
    width: 40, height: 40, borderRadius: 20,
    backgroundColor: COLORS.cardDark,
    justifyContent: 'center', alignItems: 'center',
  },
  detailsHeaderTitle: {
    fontSize: 17, fontWeight: '700', color: COLORS.textPrimary,
  },
  detailsLoading: {
    flex: 1, justifyContent: 'center', alignItems: 'center',
  },
  detailsBanner: {
    width: '100%', height: 260,
  },
  detailsBannerWrap: {
    position: 'relative',
    width: '100%',
    height: 260,
  },
  detailsBannerOverlay: {
    position: 'absolute',
    top: 0, left: 0, right: 0, bottom: 0,
    backgroundColor: 'rgba(0,0,0,0.45)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  detailsBannerGradient: {
    height: 260,
    justifyContent: 'center',
    alignItems: 'center',
  },
  detailsOverlayOnly: {
    fontSize: 13, fontWeight: '900',
    color: 'rgba(255,255,255,0.85)',
    letterSpacing: 4,
    marginBottom: 2,
  },
  detailsOverlayPriceRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  detailsOverlayCurrency: {
    fontSize: 28, fontWeight: '900',
    color: '#FFF', marginTop: 10,
  },
  detailsOverlayPrice: {
    fontSize: 88, fontWeight: '900',
    color: '#FFF', lineHeight: 96,
  },
  detailsOverlayUnit: {
    fontSize: 16, fontWeight: '700',
    color: 'rgba(255,255,255,0.85)',
    marginTop: 4, letterSpacing: 1,
    textTransform: 'uppercase',
  },
  detailsBody: {
    padding: 24,
  },
  detailsTitle: {
    fontSize: 26, fontWeight: '800',
    color: COLORS.textPrimary,
    marginBottom: 12, lineHeight: 32,
  },
  detailsPriceRow: {
    flexDirection: 'row', alignItems: 'baseline',
    gap: 10, marginBottom: 20,
  },
  detailsPrice: {
    fontSize: 36, fontWeight: '800', color: COLORS.primary,
  },
  detailsOriginalPrice: {
    fontSize: 18, color: COLORS.textMuted,
    textDecorationLine: 'line-through',
  },
  detailsPriceUnit: {
    fontSize: 14, color: COLORS.textSecondary, fontWeight: '500',
  },
  detailsSection: {
    marginBottom: 24,
  },
  detailsSectionTitle: {
    fontSize: 13, fontWeight: '800',
    color: COLORS.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 1,
    marginBottom: 12,
  },
  detailsDescription: {
    fontSize: 15, color: COLORS.textSecondary,
    lineHeight: 24,
  },
  detailsItemsList: {
    gap: 10,
  },
  detailsItem: {
    flexDirection: 'row', alignItems: 'center',
    backgroundColor: COLORS.cardDark,
    borderRadius: 12, padding: 14, gap: 12,
  },
  detailsItemDot: {
    width: 8, height: 8, borderRadius: 4,
    backgroundColor: COLORS.primary,
  },
  detailsItemText: {
    fontSize: 15, fontWeight: '600', color: COLORS.textPrimary,
  },
  detailsNotesCard: {
    flexDirection: 'row', alignItems: 'flex-start',
    gap: 10, backgroundColor: COLORS.warning + '15',
    borderRadius: 12, padding: 14,
    borderWidth: 1, borderColor: COLORS.warning + '30',
    marginBottom: 24,
  },
  detailsNotesText: {
    flex: 1, fontSize: 13,
    color: COLORS.textSecondary, lineHeight: 20,
  },
  detailsValidityRow: {
    flexDirection: 'row', alignItems: 'center', gap: 8,
  },
  detailsValidityText: {
    fontSize: 14, color: COLORS.textSecondary, fontWeight: '500',
  },
  detailsBranchRow: {
    flexDirection: 'row', alignItems: 'center',
    gap: 8, marginBottom: 24,
  },
  detailsBigPrice: {
    alignItems: 'center',
    backgroundColor: COLORS.cardDark,
    borderRadius: 20,
    padding: 24,
    marginBottom: 24,
    borderWidth: 1,
    borderColor: COLORS.primary + '30',
  },
  detailsBigPriceLabel: {
    fontSize: 12, fontWeight: '800',
    color: COLORS.textMuted,
    letterSpacing: 2,
    marginBottom: 4,
  },
  detailsBigPriceRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  detailsBigPriceCurrency: {
    fontSize: 28, fontWeight: '800',
    color: COLORS.primary, marginTop: 8,
  },
  detailsBigPriceValue: {
    fontSize: 72, fontWeight: '900',
    color: COLORS.primary, lineHeight: 80,
  },
  detailsBigPriceUnit: {
    fontSize: 16, fontWeight: '700',
    color: COLORS.textSecondary, marginTop: 4,
  },
  detailsOriginalPrice: {
    fontSize: 13, color: COLORS.textMuted,
    textDecorationLine: 'line-through', marginTop: 6,
  },
  detailsFreeBadge: {
    backgroundColor: COLORS.success + '20',
    borderRadius: 8,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderWidth: 1,
    borderColor: COLORS.success + '40',
  },
  detailsFreeBadgeText: {
    fontSize: 10, fontWeight: '800',
    color: COLORS.success, letterSpacing: 0.5,
  },
  detailsBranchText: {
    fontSize: 13, color: COLORS.textMuted,
  },
  detailsUseBtn: {
    borderRadius: 16, overflow: 'hidden', marginTop: 8,
  },
  detailsUseBtnGradient: {
    flexDirection: 'row', alignItems: 'center',
    justifyContent: 'center', gap: 10,
    paddingVertical: 16,
  },
  detailsUseBtnText: {
    fontSize: 16, fontWeight: '700', color: '#FFF',
  },
});