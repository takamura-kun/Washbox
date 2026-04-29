import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  Alert,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_BASE_URL, STORAGE_KEYS } from '../constants/config';

const COLORS = {
  background: '#0A1128',
  cardDark: '#1A2847',
  primary: '#0EA5E9',
  textPrimary: '#FFFFFF',
  textSecondary: '#94A3B8',
  success: '#10B981',
  info: '#3B82F6',
  warning: '#F59E0B',
  danger: '#EF4444',
};

export default function NotificationsScreen() {
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [notifications, setNotifications] = useState([]);
  const [filter, setFilter] = useState('all');

  useEffect(() => {
    fetchNotifications();
    
    // Create a unique handler for this component instance
    const handleFCMNotification = (data) => {
      console.log('[Notifications] Received new notification in real-time:', data);
      // Immediately refresh notifications list
      fetchNotifications(true);
    };
    
    // Store the handler on the global object if it doesn't exist
    if (!global.__fcmNotificationHandlers) {
      global.__fcmNotificationHandlers = [];
    }
    global.__fcmNotificationHandlers.push(handleFCMNotification);
    
    // Set up the FCM listener callback (only once)
    if (!global.__onFCMNotification || typeof global.__onFCMNotification !== 'function') {
      global.__onFCMNotification = (data) => {
        // Call all registered handlers
        if (global.__fcmNotificationHandlers && Array.isArray(global.__fcmNotificationHandlers)) {
          global.__fcmNotificationHandlers.forEach(handler => {
            try {
              handler(data);
            } catch (e) {
              console.error('[Notifications] Handler error:', e);
            }
          });
        }
      };
    }
    
    const interval = setInterval(() => fetchNotifications(true), 30000);
    
    return () => {
      clearInterval(interval);
      // Remove this handler from the list
      if (global.__fcmNotificationHandlers) {
        const index = global.__fcmNotificationHandlers.indexOf(handleFCMNotification);
        if (index > -1) {
          global.__fcmNotificationHandlers.splice(index, 1);
        }
      }
    };
  }, []);

  const fetchNotifications = async (isRefresh = false) => {
    try {
      if (!isRefresh) setLoading(true);

      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (!token) {
        router.replace('/(auth)/login');
        return;
      }

      const url = filter === 'unread'
        ? `${API_BASE_URL}/v1/notifications?unread_only=true`
        : `${API_BASE_URL}/v1/notifications`;

      const response = await fetch(url, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data?.notifications) {
          setNotifications(data.data.notifications);
        }
      } else if (response.status === 401) {
        await AsyncStorage.removeItem(STORAGE_KEYS.TOKEN);
        router.replace('/(auth)/login');
      }
    } catch (error) {
      console.error('Error fetching notifications:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    fetchNotifications(true);
  };

  const markAsRead = async (notificationId) => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      const response = await fetch(
        `${API_BASE_URL}/v1/notifications/${notificationId}/read`,
        {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
          },
        }
      );

      if (response.ok) {
        // Update local state
        setNotifications(prev =>
          prev.map(n => 
            n.id === notificationId 
              ? { ...n, is_read: true, read_at: new Date().toISOString() }
              : n
          )
        );
      }
    } catch (error) {
      console.error('Error marking as read:', error);
    }
  };

  const markAllAsRead = async () => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      const response = await fetch(
        `${API_BASE_URL}/v1/notifications/mark-all-read`,
        {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
          },
        }
      );

      if (response.ok) {
        setNotifications(prev => prev.map(n => ({ ...n, is_read: true, read_at: new Date().toISOString() })));
      }
    } catch (error) {
      console.error('Error marking all as read:', error);
    }
  };

  const deleteNotification = async (notificationId) => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      const response = await fetch(
        `${API_BASE_URL}/v1/notifications/${notificationId}`,
        {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
          },
        }
      );

      if (response.ok) {
        // Remove from local state
        setNotifications(prev => prev.filter(n => n.id !== notificationId));
      }
    } catch (error) {
      console.error('Error deleting notification:', error);
    }
  };

  const clearAllRead = async () => {
    Alert.alert(
      'Clear Read Notifications',
      'Are you sure you want to delete all read notifications?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Delete',
          style: 'destructive',
          onPress: async () => {
            try {
              const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
              const response = await fetch(
                `${API_BASE_URL}/v1/notifications/clear-read`,
                {
                  method: 'DELETE',
                  headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                  },
                }
              );

              if (response.ok) {
                await fetchNotifications();
                Alert.alert('Success', 'Read notifications deleted');
              }
            } catch (error) {
              console.error('Error clearing read:', error);
              Alert.alert('Error', 'Failed to clear notifications');
            }
          },
        },
      ]
    );
  };

  const getNotificationIcon = (type) => {
    const icons = {
      // Laundry lifecycle
      'laundry_received': 'cube',
      'laundry_ready': 'checkmark-done-circle',
      'laundry_completed': 'gift',
      'laundry_cancelled': 'close-circle',
      
      // Payment notifications
      'payment_pending': 'card',
      'payment_received': 'checkmark-circle',
      'payment_verification': 'time',
      'payment_approved': 'checkmark-circle',
      'payment_rejected': 'close-circle',
      
      // Pickup & Delivery
      'pickup_submitted': 'paper-plane',
      'pickup_accepted': 'checkmark-circle',
      'pickup_en_route': 'car',
      'pickup_completed': 'checkbox',
      'pickup_cancelled': 'close-circle',
      'delivery_scheduled': 'calendar',
      'delivery_en_route': 'car',
      'delivery_completed': 'checkmark-done',
      'delivery_failed': 'close-circle',
      
      // System & Business
      'system_maintenance': 'construct',
      'app_update': 'download',
      'branch_closure': 'business',
      'service_update': 'information-circle',
      
      // Customer engagement
      'feedback_request': 'star',
      'loyalty_reward': 'trophy',
      'birthday_greeting': 'gift',
      
      // Emergency & Important
      'emergency_alert': 'warning',
      'unclaimed_reminder': 'time',
      
      // Promotional
      'promotion': 'pricetag',
      'welcome': 'hand-left',
      'general': 'megaphone',
    };
    return icons[type] || 'notifications';
  };

  const getNotificationColor = (type) => {
    const colors = {
      // Laundry lifecycle
      'laundry_received': COLORS.info,
      'laundry_ready': COLORS.success,
      'laundry_completed': COLORS.success,
      'laundry_cancelled': COLORS.danger,
      
      // Payment notifications
      'payment_pending': COLORS.warning,
      'payment_received': COLORS.success,
      'payment_verification': COLORS.info,
      'payment_approved': COLORS.success,
      'payment_rejected': COLORS.danger,
      
      // Pickup & Delivery
      'pickup_submitted': COLORS.info,
      'pickup_accepted': COLORS.success,
      'pickup_en_route': COLORS.primary,
      'pickup_completed': COLORS.success,
      'pickup_cancelled': COLORS.danger,
      'delivery_scheduled': COLORS.info,
      'delivery_en_route': COLORS.primary,
      'delivery_completed': COLORS.success,
      'delivery_failed': COLORS.danger,
      
      // System & Business
      'system_maintenance': COLORS.warning,
      'app_update': COLORS.primary,
      'branch_closure': COLORS.warning,
      'service_update': COLORS.info,
      
      // Customer engagement
      'feedback_request': COLORS.primary,
      'loyalty_reward': COLORS.success,
      'birthday_greeting': COLORS.primary,
      
      // Emergency & Important
      'emergency_alert': COLORS.danger,
      'unclaimed_reminder': COLORS.warning,
      
      // Promotional
      'promotion': COLORS.primary,
      'welcome': COLORS.primary,
      'general': COLORS.textSecondary,
    };
    return colors[type] || COLORS.primary;
  };

  const formatTimestamp = (timestamp) => {
    if (!timestamp) return 'Unknown';
    
    const date = new Date(timestamp);
    
    // Check if date is valid
    if (isNaN(date.getTime())) {
      return 'Unknown';
    }
    
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  };

  const unreadCount = notifications.filter(n => !n.is_read).length;

  if (loading && !refreshing) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Loading notifications...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.push('/(tabs)')} style={styles.backButton}>
          <Ionicons name="arrow-back" size={24} color={COLORS.textPrimary} />
        </TouchableOpacity>
        <View style={styles.headerCenter}>
          <Text style={styles.headerTitle}>Notifications</Text>
          {unreadCount > 0 && (
            <View style={styles.headerBadge}>
              <Text style={styles.headerBadgeText}>{unreadCount}</Text>
            </View>
          )}
        </View>
        <TouchableOpacity onPress={clearAllRead} style={styles.headerAction}>
          <Ionicons name="trash-outline" size={22} color={COLORS.textSecondary} />
        </TouchableOpacity>
      </View>

      {/* Filter Tabs */}
      <View style={styles.filterContainer}>
        <TouchableOpacity
          style={[styles.filterTab, filter === 'all' && styles.filterTabActive]}
          onPress={() => setFilter('all')}
        >
          <Text style={[styles.filterText, filter === 'all' && styles.filterTextActive]}>
            All
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.filterTab, filter === 'unread' && styles.filterTabActive]}
          onPress={() => setFilter('unread')}
        >
          <Text style={[styles.filterText, filter === 'unread' && styles.filterTextActive]}>
            Unread ({unreadCount})
          </Text>
        </TouchableOpacity>
        {unreadCount > 0 && (
          <TouchableOpacity style={styles.markAllButton} onPress={markAllAsRead}>
            <Text style={styles.markAllText}>Mark all read</Text>
          </TouchableOpacity>
        )}
      </View>

      {/* Notifications List */}
      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={COLORS.primary} />
        }
      >
        {notifications.length === 0 ? (
          <View style={styles.emptyState}>
            <Ionicons name="notifications-off-outline" size={64} color={COLORS.textSecondary} />
            <Text style={styles.emptyTitle}>No Notifications</Text>
            <Text style={styles.emptyText}>
              {filter === 'unread' 
                ? "You're all caught up!" 
                : "You haven't received any notifications yet"}
            </Text>
          </View>
        ) : (
          <View style={styles.notificationsList}>
            {notifications.map((notification) => (
              <TouchableOpacity
                key={notification.id}
                style={[
                  styles.notificationCard,
                  !notification.is_read && styles.notificationUnread,
                ]}
                onPress={() => {
                  if (!notification.is_read) markAsRead(notification.id);
                  if (notification.pickup_request_id) {
                    router.push(`/pickups/${notification.pickup_request_id}`);
                  } else if (notification.laundries_id) {
                    router.push(`/laundries/${notification.laundries_id}`);
                  }
                }}
                onLongPress={() => {
                  Alert.alert(
                    'Delete Notification',
                    'Are you sure you want to delete this notification?',
                    [
                      { text: 'Cancel', style: 'cancel' },
                      {
                        text: 'Delete',
                        style: 'destructive',
                        onPress: () => deleteNotification(notification.id),
                      },
                    ]
                  );
                }}
              >
                <View
                  style={[
                    styles.notificationIcon,
                    { backgroundColor: getNotificationColor(notification.type) + '20' },
                  ]}
                >
                  <Ionicons
                    name={getNotificationIcon(notification.type)}
                    size={24}
                    color={getNotificationColor(notification.type)}
                  />
                </View>
                <View style={styles.notificationContent}>
                  <View style={styles.notificationHeader}>
                    <Text style={styles.notificationTitle}>{notification.title}</Text>
                    {!notification.is_read && <View style={styles.unreadDot} />}
                  </View>
                  <Text style={styles.notificationBody}>{notification.body}</Text>
                  <Text style={styles.notificationTime}>
                    {formatTimestamp(notification.created_at)}
                  </Text>
                </View>
              </TouchableOpacity>
            ))}
          </View>
        )}

        <View style={{ height: 100 }} />
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
    marginTop: 12,
    fontSize: 14,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingTop: 50,
    paddingBottom: 20,
  },
  backButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.cardDark,
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerCenter: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: COLORS.textPrimary,
  },
  headerBadge: {
    backgroundColor: COLORS.danger,
    minWidth: 20,
    height: 20,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 6,
  },
  headerBadgeText: {
    color: '#FFF',
    fontSize: 11,
    fontWeight: 'bold',
  },
  headerAction: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.cardDark,
    justifyContent: 'center',
    alignItems: 'center',
  },
  filterContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 20,
    gap: 8,
    marginBottom: 16,
  },
  filterTab: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: COLORS.cardDark,
  },
  filterTabActive: {
    backgroundColor: COLORS.primary,
  },
  filterText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    fontWeight: '600',
  },
  filterTextActive: {
    color: '#FFF',
  },
  markAllButton: {
    marginLeft: 'auto',
  },
  markAllText: {
    fontSize: 12,
    color: COLORS.primary,
    fontWeight: '600',
  },
  scrollView: {
    flex: 1,
  },
  notificationsList: {
    paddingHorizontal: 20,
  },
  notificationCard: {
    flexDirection: 'row',
    backgroundColor: COLORS.cardDark,
    borderRadius: 16,
    padding: 16,
    marginBottom: 12,
    gap: 12,
  },
  notificationUnread: {
    borderLeftWidth: 3,
    borderLeftColor: COLORS.primary,
  },
  notificationIcon: {
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
  },
  notificationContent: {
    flex: 1,
  },
  notificationHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 4,
  },
  notificationTitle: {
    fontSize: 15,
    fontWeight: 'bold',
    color: COLORS.textPrimary,
    flex: 1,
  },
  unreadDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: COLORS.primary,
  },
  notificationBody: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
    marginBottom: 8,
  },
  notificationTime: {
    fontSize: 12,
    color: COLORS.textSecondary,
  },
  emptyState: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 80,
    paddingHorizontal: 40,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: COLORS.textPrimary,
    marginTop: 16,
    marginBottom: 8,
  },
  emptyText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 20,
  },
});
