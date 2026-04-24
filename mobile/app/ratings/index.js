import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  TextInput,
  ActivityIndicator,
  RefreshControl,
  Alert,
  Modal,
  Animated,
  Platform,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router, useLocalSearchParams } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { LinearGradient } from 'expo-linear-gradient';
import { API_BASE_URL, STORAGE_KEYS } from '../../constants/config';

const COLORS = {
  background: '#0A0E27',
  cardDark: '#1C2340',
  cardLight: '#252D4C',
  primary: '#0EA5E9',
  primaryDark: '#0284C7',
  primaryLight: '#38BDF8',
  textPrimary: '#FFFFFF',
  textSecondary: '#94A3B8',
  textMuted: '#64748B',
  success: '#10B981',
  warning: '#F59E0B',
  danger: '#EF4444',
  purple: '#8B5CF6',
  border: '#1E293B',
  star: '#F59E0B',
  starEmpty: '#334155',
  gradientPrimary: ['#0EA5E9', '#3B82F6'],
};

export default function RatingsScreen() {
  const params = useLocalSearchParams();
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [activeTab, setActiveTab] = useState('my_ratings');
  const [ratings, setRatings] = useState([]);
  const [unratedLaundries, setUnratedLaundries] = useState([]);
  const [branches, setBranches] = useState([]);
  const [ratedBranches, setRatedBranches] = useState([]);
  const [fadeAnim] = useState(new Animated.Value(0));

  // Rating Modal State
  const [ratingModalVisible, setRatingModalVisible] = useState(false);
  const [ratingType, setRatingType] = useState('laundry'); // 'laundry' or 'branch'
  const [selectedLaundry, setSelectedLaundry] = useState(null);
  const [selectedBranch, setSelectedBranch] = useState(null);
  const [selectedRating, setSelectedRating] = useState(0);
  const [ratingComment, setRatingComment] = useState('');
  const [submitting, setSubmitting] = useState(false);

  // Stats
  const [ratingStats, setRatingStats] = useState({
    averageRating: 0,
    totalRatings: 0,
    laundryRatings: 0,
    branchRatings: 0,
    distribution: { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 },
  });

  useEffect(() => {
    // Check for tab parameter
    if (params.tab === 'rate_branch') {
      setActiveTab('rate_branch');
    }
    fetchData();
  }, [params.tab]);

  useEffect(() => {
    if (!loading) {
      Animated.timing(fadeAnim, {
        toValue: 1,
        duration: 500,
        useNativeDriver: true,
      }).start();
    }
  }, [loading]);

  const fetchData = async () => {
    try {
      setLoading(true);
      await Promise.all([
        fetchMyRatings(),
        fetchUnratedLaundries(),
        fetchBranches(),
      ]);
    } catch (error) {
      console.error('Error fetching ratings data:', error);
    } finally {
      setLoading(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchData();
    setRefreshing(false);
  };

  const fetchMyRatings = async () => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return;

      const response = await fetch(`${API_BASE_URL}/v1/customer/ratings`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          setRatings(data.data.ratings || []);
          // Extract rated branch IDs
          const branchRatings = data.data.ratings.filter(r => r.type === 'branch');
          setRatedBranches(branchRatings.map(r => r.branch_id));
          if (data.data.stats) {
            setRatingStats(data.data.stats);
          }
        }
      } else if (response.status === 401) {
        await AsyncStorage.multiRemove([STORAGE_KEYS.TOKEN, STORAGE_KEYS.CUSTOMER]);
        router.replace('/(auth)/login');
      }
    } catch (error) {
      console.error('Error fetching ratings:', error);
    }
  };

  const fetchUnratedLaundries = async () => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) return;

      const response = await fetch(`${API_BASE_URL}/v1/customer/unrated-laundries`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          setUnratedLaundries(data.data.laundries || []);
        }
      }
    } catch (error) {
      console.error('Error fetching unrated laundries:', error);
    }
  };

  const fetchBranches = async () => {
    try {
      console.log('Fetching branches...');
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) {
        console.log('No token found');
        return;
      }

      const response = await fetch(`${API_BASE_URL}/v1/customer/branches`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      console.log('Branches response status:', response.status);

      if (response.ok) {
        const data = await response.json();
        console.log('Branches data:', data);
        if (data.success) {
          const branchesData = data.data?.branches || [];
          console.log('Branches loaded:', branchesData);
          setBranches(branchesData);
        }
      }
    } catch (error) {
      console.error('Error fetching branches:', error);
    }
  };

  const submitLaundryRating = async () => {
    if (selectedRating === 0) {
      Alert.alert('Rating Required', 'Please select a star rating.');
      return;
    }

    try {
      setSubmitting(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);

      const response = await fetch(`${API_BASE_URL}/v1/customer/ratings`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          laundry_id: selectedLaundry.id,
          rating: selectedRating,
          comment: ratingComment.trim() || null,
        }),
      });

      const data = await response.json();

      if (data.success) {
        Alert.alert('Thank You!', 'Your rating has been submitted successfully.');
        closeRatingModal();
        await fetchData();
      } else {
        Alert.alert('Error', data.message || 'Failed to submit rating.');
      }
    } catch (error) {
      Alert.alert('Error', 'Failed to submit rating. Please check your connection.');
    } finally {
      setSubmitting(false);
    }
  };

  const submitBranchRating = async () => {
    if (selectedRating === 0) {
      Alert.alert('Rating Required', 'Please select a star rating.');
      return;
    }

    if (!selectedBranch) {
      Alert.alert('Branch Required', 'Please select a branch.');
      return;
    }

    try {
      setSubmitting(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);

      console.log('Submitting branch rating:', {
        branch_id: selectedBranch.id,
        rating: selectedRating,
        comment: ratingComment.trim() || null,
      });

      const response = await fetch(`${API_BASE_URL}/v1/customer/branch-ratings`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          branch_id: selectedBranch.id,
          rating: selectedRating,
          comment: ratingComment.trim() || null,
        }),
      });

      console.log('Branch rating response status:', response.status);
      const data = await response.json();
      console.log('Branch rating response data:', data);

      if (data.success) {
        Alert.alert('Thank You!', 'Your branch rating has been submitted successfully.');
        closeRatingModal();
        await Promise.all([fetchMyRatings(), fetchBranches()]);
      } else {
        Alert.alert('Error', data.message || 'Failed to submit branch rating.');
      }
    } catch (error) {
      console.error('Error submitting branch rating:', error);
      Alert.alert('Error', 'Failed to submit rating. Please check your connection.');
    } finally {
      setSubmitting(false);
    }
  };

  const handleSubmitRating = () => {
    if (ratingType === 'laundry') {
      submitLaundryRating();
    } else {
      submitBranchRating();
    }
  };

  const closeRatingModal = () => {
    setRatingModalVisible(false);
    setSelectedLaundry(null);
    setSelectedBranch(null);
    setSelectedRating(0);
    setRatingComment('');
  };

  const openLaundryRatingModal = (laundry) => {
    setRatingType('laundry');
    setSelectedLaundry(laundry);
    setSelectedBranch(null);
    setSelectedRating(0);
    setRatingComment('');
    setRatingModalVisible(true);
  };

  const openBranchRatingModal = () => {
    setRatingType('branch');
    setSelectedLaundry(null);
    setSelectedBranch(null);
    setSelectedRating(0);
    setRatingComment('');
    setRatingModalVisible(true);
    fetchBranches();
  };

  // Single formatDate declaration
  const formatDate = (dateString) => {
    if (!dateString) return 'Unknown date';
    try {
      const date = new Date(dateString);
      if (isNaN(date.getTime())) return 'Invalid date';
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      });
    } catch (error) {
      return 'Invalid date';
    }
  };

  const formatPrice = (amount) => {
    if (!amount) return '₱0.00';
    return `₱${parseFloat(amount).toFixed(2)}`;
  };

  const getOrderNumber = (laundry) => {
    const raw =
      laundry.tracking_number ||
      laundry.laundry_number ||
      laundry.order_number ||
      laundry.id;
    const str = String(raw);
    return str.startsWith('#') || str.toLowerCase().startsWith('order')
      ? str
      : `Order #${str}`;
  };

  const getRatingLabel = (rating) => {
    const labels = {
      1: 'Poor',
      2: 'Fair',
      3: 'Good',
      4: 'Very Good',
      5: 'Excellent',
    };
    return labels[rating] || '';
  };

  const getRatingColor = (rating) => {
    if (rating >= 4) return COLORS.success;
    if (rating >= 3) return COLORS.warning;
    return COLORS.danger;
  };

  const StarRating = ({ rating, size = 20, interactive = false, onRate = null }) => (
    <View style={styles.starRow}>
      {[1, 2, 3, 4, 5].map((star) => (
        <TouchableOpacity
          key={star}
          disabled={!interactive}
          onPress={() => interactive && onRate && onRate(star)}
          activeOpacity={interactive ? 0.7 : 1}
          style={interactive ? styles.starTouchable : null}
        >
          <Ionicons
            name={star <= rating ? 'star' : 'star-outline'}
            size={size}
            color={star <= rating ? COLORS.star : COLORS.starEmpty}
          />
        </TouchableOpacity>
      ))}
    </View>
  );



  const renderStars = (rating, onPress = null, size = 24) => {
    return (
      <View style={styles.starsContainer}>
        {[1, 2, 3, 4, 5].map((star) => (
          <TouchableOpacity
            key={star}
            onPress={() => onPress && onPress(star)}
            disabled={!onPress}
            style={styles.starButton}
          >
            <Ionicons
              name={star <= rating ? 'star' : 'star-outline'}
              size={size}
              color={star <= rating ? COLORS.star : COLORS.starEmpty}
            />
          </TouchableOpacity>
        ))}
      </View>
    );
  };

  const renderMyRatings = () => {
    if (ratings.length === 0) {
      return (
        <View style={styles.emptyState}>
          <Ionicons name="star-outline" size={64} color={COLORS.textMuted} />
          <Text style={styles.emptyTitle}>No Ratings Yet</Text>
          <Text style={styles.emptySubtitle}>
            Complete some laundry orders to leave ratings
          </Text>
        </View>
      );
    }

    return (
      <ScrollView
        style={styles.scrollView}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
      >
        {/* Stats Card */}
        <View style={styles.statsCard}>
          <Text style={styles.statsTitle}>Your Rating Summary</Text>
          <View style={styles.statsRow}>
            <View style={styles.statItem}>
              <Text style={styles.statValue}>{ratingStats.averageRating.toFixed(1)}</Text>
              <Text style={styles.statLabel}>Average</Text>
            </View>
            <View style={styles.statItem}>
              <Text style={styles.statValue}>{ratingStats.totalRatings}</Text>
              <Text style={styles.statLabel}>Total</Text>
            </View>
            <View style={styles.statItem}>
              <Text style={styles.statValue}>{ratingStats.laundryRatings}</Text>
              <Text style={styles.statLabel}>Laundry</Text>
            </View>
            <View style={styles.statItem}>
              <Text style={styles.statValue}>{ratingStats.branchRatings}</Text>
              <Text style={styles.statLabel}>Branch</Text>
            </View>
          </View>
        </View>

        {/* Ratings List */}
        {ratings.map((rating) => (
          <View key={`${rating.type}-${rating.id}`} style={styles.ratingCard}>
            <View style={styles.ratingHeader}>
              <View style={styles.ratingInfo}>
                <Text style={styles.ratingTitle}>
                  {rating.type === 'laundry' ? rating.service_name || 'Unknown Service' : rating.branch_name || 'Unknown Branch'}
                </Text>
                <Text style={styles.ratingSubtitle}>
                  {rating.type === 'laundry' ? 'Service Rating' : 'Branch Rating'} • {formatDate(rating.created_at)}
                </Text>
              </View>
              <View style={styles.ratingBadge}>
                <Text style={styles.ratingBadgeText}>{rating.type}</Text>
              </View>
            </View>

            {renderStars(rating.rating, null, 20)}

            {rating.comment && (
              <Text style={styles.ratingComment}>{rating.comment}</Text>
            )}
          </View>
        ))}
      </ScrollView>
    );
  };

  const renderToRate = () => {
    if (unratedLaundries.length === 0) {
      return (
        <View style={styles.emptyState}>
          <Ionicons name="checkmark-circle-outline" size={64} color={COLORS.success} />
          <Text style={styles.emptyTitle}>All Caught Up!</Text>
          <Text style={styles.emptySubtitle}>
            You've rated all your completed orders
          </Text>
        </View>
      );
    }

    return (
      <ScrollView
        style={styles.scrollView}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
      >
        {unratedLaundries.map((laundry) => (
          <View key={laundry.id} style={styles.laundryCard}>
            <View style={styles.laundryHeader}>
              <View style={styles.laundryInfo}>
                <Text style={styles.laundryTitle}>{laundry.service_name || 'Unknown Service'}</Text>
                <Text style={styles.laundrySubtitle}>
                  {laundry.branch_name || 'Unknown Branch'} • {formatDate(laundry.completed_at)}
                </Text>
                <Text style={styles.laundryPrice}>₱{laundry.total_amount || '0.00'}</Text>
              </View>
              <TouchableOpacity
                style={styles.rateButton}
                onPress={() => openLaundryRatingModal(laundry)}
              >
                <Text style={styles.rateButtonText}>Rate</Text>
              </TouchableOpacity>
            </View>
          </View>
        ))}
      </ScrollView>
    );
  };

  const renderRateBranch = () => {
    if (branches.length === 0) {
      return (
        <View style={styles.emptyState}>
          <Ionicons name="business-outline" size={64} color={COLORS.textMuted} />
          <Text style={styles.emptyTitle}>No Branches Available</Text>
          <Text style={styles.emptySubtitle}>
            No branches found to rate
          </Text>
        </View>
      );
    }

    return (
      <ScrollView
        style={styles.scrollView}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
      >
        <View style={styles.instructionCard}>
          <Ionicons name="information-circle" size={24} color={COLORS.primary} />
          <Text style={styles.instructionText}>
            Rate your experience with our branches to help us improve our services.
          </Text>
        </View>

        {branches.map((branch) => (
          <View key={branch.id} style={styles.branchCard}>
            <View style={styles.branchHeader}>
              <View style={styles.branchInfo}>
                <Text style={styles.branchTitle}>{branch.name || 'Unknown Branch'}</Text>
                <Text style={styles.branchSubtitle}>
                  {branch.address || 'No address available'}
                </Text>
                {branch.average_rating && (
                  <View style={styles.branchRating}>
                    <Ionicons name="star" size={16} color={COLORS.star} />
                    <Text style={styles.branchRatingText}>
                      {parseFloat(branch.average_rating).toFixed(1)} ({branch.ratings_count || 0} reviews)
                    </Text>
                  </View>
                )}
              </View>
              {ratedBranches.includes(branch.id) ? (
                <View style={[styles.rateButton, { backgroundColor: COLORS.success }]}>
                  <Text style={styles.rateButtonText}>Rated</Text>
                </View>
              ) : (
                <TouchableOpacity
                  style={styles.rateButton}
                  onPress={() => {
                    setSelectedBranch(branch);
                    openBranchRatingModal();
                  }}
                >
                  <Text style={styles.rateButtonText}>Rate</Text>
                </TouchableOpacity>
              )}
            </View>
          </View>
        ))}
      </ScrollView>
    );
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Loading ratings...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => router.back()}
        >
          <Ionicons name="arrow-back" size={24} color={COLORS.textPrimary} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Ratings & Reviews</Text>
      </View>

      {/* Tabs */}
      <View style={styles.tabContainer}>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'my_ratings' && styles.activeTab]}
          onPress={() => setActiveTab('my_ratings')}
        >
          <Text style={[styles.tabText, activeTab === 'my_ratings' && styles.activeTabText]}>
            My Ratings
          </Text>
          {ratings.length > 0 && (
            <View style={styles.tabBadge}>
              <Text style={styles.tabBadgeText}>{ratings.length}</Text>
            </View>
          )}
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'to_rate' && styles.activeTab]}
          onPress={() => setActiveTab('to_rate')}
        >
          <Text style={[styles.tabText, activeTab === 'to_rate' && styles.activeTabText]}>
            To Rate
          </Text>
          {unratedLaundries.length > 0 && (
            <View style={styles.tabBadge}>
              <Text style={styles.tabBadgeText}>{unratedLaundries.length}</Text>
            </View>
          )}
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'rate_branch' && styles.activeTab]}
          onPress={() => setActiveTab('rate_branch')}
        >
          <Text style={[styles.tabText, activeTab === 'rate_branch' && styles.activeTabText]}>
            Rate Branch
          </Text>
        </TouchableOpacity>
      </View>

      {/* Content */}
      <Animated.View style={[styles.content, { opacity: fadeAnim }]}>
        {activeTab === 'my_ratings' && renderMyRatings()}
        {activeTab === 'to_rate' && renderToRate()}
        {activeTab === 'rate_branch' && renderRateBranch()}
      </Animated.View>

      {/* Rating Modal */}
      <Modal
        visible={ratingModalVisible}
        transparent
        animationType="slide"
        onRequestClose={closeRatingModal}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>
                Rate {ratingType === 'laundry' ? 'Service' : 'Branch'}
              </Text>
              <TouchableOpacity onPress={closeRatingModal}>
                <Ionicons name="close" size={24} color={COLORS.textSecondary} />
              </TouchableOpacity>
            </View>

            {ratingType === 'laundry' && selectedLaundry && (
              <View style={styles.modalInfo}>
                <Text style={styles.modalInfoTitle}>{selectedLaundry.service_name}</Text>
                <Text style={styles.modalInfoSubtitle}>
                  {selectedLaundry.branch?.name} • ₱{selectedLaundry.total_amount}
                </Text>
              </View>
            )}

            {ratingType === 'branch' && (
              <View style={styles.branchSelector}>
                <Text style={styles.selectorLabel}>Select Branch:</Text>
                <ScrollView style={styles.branchList}>
                  {branches.map((branch) => (
                    <TouchableOpacity
                      key={branch.id}
                      style={[
                        styles.branchOption,
                        selectedBranch?.id === branch.id && styles.selectedBranchOption
                      ]}
                      onPress={() => setSelectedBranch(branch)}
                    >
                      <Text style={[
                        styles.branchOptionText,
                        selectedBranch?.id === branch.id && styles.selectedBranchOptionText
                      ]}>
                        {branch.name}
                      </Text>
                      {selectedBranch?.id === branch.id && (
                        <Ionicons name="checkmark" size={20} color={COLORS.primary} />
                      )}
                    </TouchableOpacity>
                  ))}
                </ScrollView>
              </View>
            )}

            <View style={styles.ratingSection}>
              <Text style={styles.ratingLabel}>Your Rating:</Text>
              {renderStars(selectedRating, setSelectedRating, 32)}
            </View>

            <View style={styles.commentSection}>
              <Text style={styles.commentLabel}>Comment (Optional):</Text>
              <TextInput
                style={styles.commentInput}
                placeholder="Share your experience..."
                placeholderTextColor={COLORS.textMuted}
                value={ratingComment}
                onChangeText={setRatingComment}
                multiline
                maxLength={500}
              />
            </View>

            <View style={styles.modalActions}>
              <TouchableOpacity
                style={styles.cancelButton}
                onPress={closeRatingModal}
              >
                <Text style={styles.cancelButtonText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.submitButton, submitting && styles.disabledButton]}
                onPress={handleSubmitRating}
                disabled={submitting || selectedRating === 0 || (ratingType === 'branch' && !selectedBranch)}
              >
                {submitting ? (
                  <ActivityIndicator size="small" color={COLORS.textPrimary} />
                ) : (
                  <Text style={styles.submitButtonText}>Submit Rating</Text>
                )}
              </TouchableOpacity>
            </View>
          </View>
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
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: COLORS.background,
  },
  loadingText: {
    color: COLORS.textSecondary,
    marginTop: 16,
    fontSize: 16,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: Platform.OS === 'ios' ? 60 : 40,
    paddingBottom: 20,
    backgroundColor: COLORS.cardDark,
  },
  backButton: {
    marginRight: 16,
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: '600',
    color: COLORS.textPrimary,
  },
  tabContainer: {
    flexDirection: 'row',
    backgroundColor: COLORS.cardDark,
    paddingHorizontal: 20,
    paddingBottom: 20,
  },
  tab: {
    flex: 1,
    paddingVertical: 12,
    alignItems: 'center',
    borderRadius: 8,
    marginHorizontal: 4,
    flexDirection: 'row',
    justifyContent: 'center',
  },
  activeTab: {
    backgroundColor: COLORS.primary,
  },
  tabText: {
    fontSize: 14,
    fontWeight: '500',
    color: COLORS.textSecondary,
  },
  activeTabText: {
    color: COLORS.textPrimary,
  },
  content: {
    flex: 1,
  },
  scrollView: {
    flex: 1,
    padding: 20,
  },
  emptyState: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 40,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginTop: 16,
    textAlign: 'center',
  },
  emptySubtitle: {
    fontSize: 16,
    color: COLORS.textSecondary,
    marginTop: 8,
    textAlign: 'center',
    lineHeight: 24,
  },
  statsCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 16,
    padding: 20,
    marginBottom: 20,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  statsTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginBottom: 16,
  },
  statsRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  statItem: {
    alignItems: 'center',
  },
  statValue: {
    fontSize: 24,
    fontWeight: 'bold',
    color: COLORS.primary,
  },
  statLabel: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginTop: 4,
  },
  ratingCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 16,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  ratingHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  ratingInfo: {
    flex: 1,
  },
  ratingTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textPrimary,
  },
  ratingSubtitle: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginTop: 4,
  },
  ratingBadge: {
    backgroundColor: COLORS.primary,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 6,
  },
  ratingBadgeText: {
    fontSize: 12,
    fontWeight: '500',
    color: COLORS.textPrimary,
    textTransform: 'capitalize',
  },
  starsContainer: {
    flexDirection: 'row',
    marginBottom: 8,
  },
  starButton: {
    marginRight: 4,
  },
  ratingComment: {
    fontSize: 14,
    color: COLORS.textSecondary,
    fontStyle: 'italic',
    marginTop: 8,
  },
  laundryCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 16,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  laundryHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  laundryInfo: {
    flex: 1,
  },
  laundryTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textPrimary,
  },
  laundrySubtitle: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginTop: 4,
  },
  laundryPrice: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.success,
    marginTop: 4,
  },
  rateButton: {
    backgroundColor: COLORS.primary,
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 8,
  },
  rateButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.textPrimary,
  },
  instructionCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 16,
    padding: 16,
    marginBottom: 20,
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  instructionText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginLeft: 12,
    flex: 1,
    lineHeight: 20,
  },
  branchCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 16,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  branchHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  branchInfo: {
    flex: 1,
  },
  branchTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textPrimary,
  },
  branchSubtitle: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginTop: 4,
  },
  branchRating: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 8,
  },
  branchRatingText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginLeft: 4,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.8)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  modalContent: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 20,
    padding: 24,
    width: '100%',
    maxHeight: '80%',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 20,
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: '600',
    color: COLORS.textPrimary,
  },
  modalInfo: {
    marginBottom: 20,
  },
  modalInfoTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textPrimary,
  },
  modalInfoSubtitle: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginTop: 4,
  },
  branchSelector: {
    marginBottom: 20,
  },
  selectorLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginBottom: 12,
  },
  branchList: {
    maxHeight: 150,
  },
  branchOption: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 12,
    borderRadius: 8,
    marginBottom: 8,
    backgroundColor: COLORS.cardLight,
  },
  selectedBranchOption: {
    backgroundColor: COLORS.primary + '20',
    borderWidth: 1,
    borderColor: COLORS.primary,
  },
  branchOptionText: {
    fontSize: 14,
    color: COLORS.textSecondary,
  },
  selectedBranchOptionText: {
    color: COLORS.textPrimary,
    fontWeight: '500',
  },
  ratingSection: {
    marginBottom: 20,
  },
  ratingLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginBottom: 12,
  },
  commentSection: {
    marginBottom: 24,
  },
  commentLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginBottom: 12,
  },
  commentInput: {
    backgroundColor: COLORS.cardLight,
    borderRadius: 12,
    padding: 16,
    color: COLORS.textPrimary,
    fontSize: 14,
    minHeight: 80,
    textAlignVertical: 'top',
  },
  modalActions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  cancelButton: {
    flex: 1,
    paddingVertical: 12,
    alignItems: 'center',
    borderRadius: 12,
    marginRight: 8,
    backgroundColor: COLORS.cardLight,
  },
  cancelButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },
  submitButton: {
    flex: 1,
    paddingVertical: 12,
    alignItems: 'center',
    borderRadius: 12,
    marginLeft: 8,
    backgroundColor: COLORS.primary,
  },
  disabledButton: {
    opacity: 0.6,
  },
  submitButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textPrimary,
  },
  starRow: {
    flexDirection: 'row',
    gap: 4,
  },
  starTouchable: {
    padding: 4,
  },
  tabBadge: {
    backgroundColor: COLORS.danger,
    borderRadius: 10,
    minWidth: 20,
    height: 20,
    justifyContent: 'center',
    alignItems: 'center',
    marginLeft: 6,
  },
  tabBadgeText: {
    color: COLORS.textPrimary,
    fontSize: 12,
    fontWeight: 'bold',
  },
});
