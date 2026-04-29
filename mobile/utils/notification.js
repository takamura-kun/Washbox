/**
 * utils/notification.js
 *
 * WashBox Push Notification Utility
 * Handles FCM token registration, backend sync, and notification listeners.
 */

import { Platform, Alert } from 'react-native';
import { API_BASE_URL } from '../constants/config';

// Skip all notification logic on web
if (Platform.OS === 'web') {
  module.exports = {
    registerForPushNotifications: async () => null,
    clearFcmTokenOnLogout: async () => {},
    setupNotificationListeners: () => () => {},
    handleInitialNotification: async () => {},
    default: {
      registerForPushNotifications: async () => null,
      clearFcmTokenOnLogout: async () => {},
      setupNotificationListeners: () => () => {},
      handleInitialNotification: async () => {},
    }
  };
} else {
  // Native platform code
  const Device = require('expo-device');
  const Constants = require('expo-constants');
  
  // Detect Expo Go using executionEnvironment (works in SDK 49+)
  // 'storeClient' = Expo Go, 'bare' = dev build / production
  const executionEnv = Constants.default?.executionEnvironment ||
    Constants.executionEnvironment || '';
  const appOwnership = Constants.default?.appOwnership ||
    Constants.appOwnership || '';
  const isExpoGo = executionEnv === 'storeClient' || appOwnership === 'expo';
  
  let Notifications = null;
  let TaskManager = null;
  
  if (!isExpoGo) {
    try {
      Notifications = require('expo-notifications');
    } catch (e) {
      console.warn('[FCM] Failed to load expo-notifications:', e);
    }
    try {
      TaskManager = require('expo-task-manager');
    } catch (e) {
      console.warn('[FCM] Failed to load expo-task-manager:', e);
    }
  }

  const BACKGROUND_NOTIFICATION_TASK = 'WASHBOX_BACKGROUND_NOTIFICATION';

  // Register background notification task
  if (TaskManager && Notifications && !isExpoGo) {
    try {
      TaskManager.defineTask(BACKGROUND_NOTIFICATION_TASK, ({ data, error }) => {
        if (error) {
          console.error('[FCM] Background task error:', error);
          return;
        }
        if (data) {
          const { notification } = data;
          console.log('[FCM] Background notification received:', notification?.request?.content?.data?.type);
          // OS handles showing the notification — we just log here
        }
      });

      // Register the task with expo-notifications
      Notifications.registerTaskAsync(BACKGROUND_NOTIFICATION_TASK).catch(e => {
        console.warn('[FCM] Background task registration failed:', e);
      });
    } catch (e) {
      console.warn('[FCM] Background task setup failed:', e);
    }
  }

  // Must be called at module level, not inside async functions
  if (Notifications && !isExpoGo) {
    Notifications.setNotificationHandler({
      handleNotification: async () => ({
        shouldShowAlert: true,
        shouldShowBanner: true,
        shouldShowList: true,
        shouldPlaySound: true,
        shouldSetBadge: true,
      }),
    });
  } else {
    console.log('[FCM] Running in Expo Go - push notifications disabled');
  }

  async function registerForPushNotifications(authToken) {
    if (isExpoGo || !Notifications) {
      console.log('[FCM] Skipping — Expo Go does not support FCM. Use a dev build.');
      return null;
    }

    if (!Device.default.isDevice) {
      console.log('[FCM] Skipping — not a physical device (simulator/emulator)');
      return null;
    }

    if (Platform.OS === 'android') {
      try {
        await Notifications.setNotificationChannelAsync('washbox-default', {
          name: 'WashBox Notifications',
          importance: Notifications.AndroidImportance.MAX,
          vibrationPattern: [0, 250, 250, 250],
          lightColor: '#0EA5E9',
          sound: 'default',
        });

        await Notifications.setNotificationChannelAsync('washbox-orders', {
          name: 'Order Updates',
          importance: Notifications.AndroidImportance.HIGH,
          vibrationPattern: [0, 300, 200, 300],
          lightColor: '#10B981',
          sound: 'order_update',
        });

        await Notifications.setNotificationChannelAsync('washbox-pickup', {
          name: 'Pickup Notifications',
          importance: Notifications.AndroidImportance.HIGH,
          vibrationPattern: [0, 500, 100, 500],
          lightColor: '#F59E0B',
          sound: 'pickup_alert',
        });

        await Notifications.setNotificationChannelAsync('washbox-promo', {
          name: 'Promotions',
          importance: Notifications.AndroidImportance.HIGH,
          vibrationPattern: [0, 200, 100, 200],
          lightColor: '#EC4899',
          sound: 'promo_chime',
        });
      } catch (channelError) {
        console.warn('[FCM] Failed to set notification channel:', channelError);
      }
    }

    let finalStatus = 'denied';
    try {
      const { status: existingStatus } = await Notifications.getPermissionsAsync();
      finalStatus = existingStatus;

      if (existingStatus !== 'granted') {
        const { status } = await Notifications.requestPermissionsAsync();
        finalStatus = status;
      }
    } catch (permError) {
      console.error('[FCM] Permission error:', permError);
      return null;
    }

    if (finalStatus !== 'granted') {
      console.log('[FCM] Permission denied by user');
      return null;
    }

    let fcmToken = null;
    try {
      // Use @react-native-firebase/messaging for reliable FCM token on all Android devices
      // including OPPO, Xiaomi, Vivo which have issues with expo-notifications getDevicePushTokenAsync
      const messaging = require('@react-native-firebase/messaging').default;

      // Request permission via Firebase
      const authStatus = await messaging().requestPermission();
      const enabled =
        authStatus === messaging.AuthorizationStatus.AUTHORIZED ||
        authStatus === messaging.AuthorizationStatus.PROVISIONAL;

      if (!enabled) {
        console.log('[FCM] Firebase permission denied');
        return null;
      }

      fcmToken = await messaging().getToken();
      console.log('[FCM] Firebase token obtained:', fcmToken?.substring(0, 20) + '...');
    } catch (err) {
      console.error('[FCM] Firebase messaging failed:', err.message);
      // Fallback to expo-notifications
      try {
        const tokenData = await Notifications.getDevicePushTokenAsync();
        fcmToken = tokenData.data;
        console.log('[FCM] Expo fallback token obtained:', fcmToken?.substring(0, 20) + '...');
      } catch (err2) {
        console.error('[FCM] Both methods failed:', err2.message);
        Alert.alert('FCM Debug', `Error: ${err.message}`);
        return null;
      }
    }

    if (!fcmToken) {
      console.warn('[FCM] Token is empty');
      return null;
    }

    if (authToken) {
      await saveFcmTokenToBackend(fcmToken, authToken);
    } else {
      console.warn('[FCM] No auth token provided — cannot save FCM token to backend');
    }

    return fcmToken;
  }

  async function saveFcmTokenToBackend(fcmToken, authToken) {
    try {
      const response = await fetch(`${API_BASE_URL}/v1/customer/fcm-token`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${authToken}`,
        },
        body: JSON.stringify({ fcm_token: fcmToken }),
      });

      if (response.ok) {
        console.log('[FCM] Token saved to backend successfully');
      } else {
        const body = await response.text();
        console.warn('[FCM] Backend rejected token:', response.status, body);
      }
    } catch (err) {
      console.error('[FCM] Failed to save token to backend:', err);
    }
  }

  async function clearFcmTokenOnLogout(authToken) {
    if (!authToken) return;

    try {
      await fetch(`${API_BASE_URL}/v1/customer/fcm-token`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${authToken}`,
        },
      });
      console.log('[FCM] Token cleared on logout');
    } catch (err) {
      console.error('[FCM] Failed to clear token on logout:', err);
    }
  }

  function handleNotificationNavigation(router, data) {
    console.log('[FCM] Handling notification navigation:', data);
    
    const { type, laundry_id, laundries_id, pickup_id, promotion_id, branch_id } = data;
    const actualLaundryId = laundry_id || laundries_id;
    
    setTimeout(() => {
      try {
        switch (type) {
          case 'laundry_received':
          case 'laundry_ready':
          case 'laundry_completed':
          case 'laundry_cancelled':
          case 'payment_pending':
          case 'payment_received':
          case 'payment_verification':
          case 'payment_rejected':
            if (actualLaundryId) {
              console.log(`[FCM] Navigating to laundry details: /laundries/${actualLaundryId}`);
              router.push(`/laundries/${actualLaundryId}`);
            } else {
              console.log('[FCM] No laundry_id or laundries_id, going to laundry tab');
              router.push('/(tabs)/laundry');
            }
            break;
          
          case 'pickup_submitted':
          case 'pickup_accepted':
          case 'pickup_en_route':
          case 'pickup_completed':
          case 'pickup_cancelled':
          case 'delivery_scheduled':
          case 'delivery_en_route':
          case 'delivery_completed':
          case 'delivery_failed':
          case 'unclaimed_reminder':
            if (pickup_id) {
              console.log(`[FCM] Navigating to pickup tracking: /pickup-tracking?id=${pickup_id}`);
              router.push(`/pickup-tracking?id=${pickup_id}`);
            } else if (actualLaundryId) {
              console.log(`[FCM] No pickup_id, navigating to laundry: /laundries/${actualLaundryId}`);
              router.push(`/laundries/${actualLaundryId}`);
            } else {
              console.log('[FCM] No pickup_id or laundry_id, going to pickup tab');
              router.push('/(tabs)/pickup');
            }
            break;
          
          case 'promotion':
          case 'loyalty_reward':
          case 'birthday_greeting':
            if (promotion_id) {
              console.log(`[FCM] Navigating to promotions: /promotions?highlight=${promotion_id}`);
              router.push(`/promotions?highlight=${promotion_id}`);
            } else {
              console.log('[FCM] No promotion_id, going to promotions');
              router.push('/promotions/index');
            }
            break;
          
          case 'system_maintenance':
          case 'app_update':
          case 'branch_closure':
          case 'service_update':
          case 'emergency_alert':
            console.log('[FCM] System notification, going to notifications screen');
            router.push('/notifications');
            break;
          
          case 'feedback_request':
            if (actualLaundryId) {
              console.log(`[FCM] Navigating to ratings: /ratings?laundry_id=${actualLaundryId}`);
              router.push(`/ratings?laundry_id=${actualLaundryId}`);
            } else {
              console.log('[FCM] No laundry_id, going to ratings');
              router.push('/ratings/index');
            }
            break;
          
          case 'welcome':
            console.log('[FCM] Welcome notification, going to home');
            router.push('/(tabs)');
            break;
          
          default:
            console.log(`[FCM] Unknown notification type: ${type}, going to notifications`);
            router.push('/notifications');
            break;
        }
      } catch (error) {
        console.error('[FCM] Navigation error:', error);
        console.log('[FCM] Navigation failed, going to home screen');
        router.push('/(tabs)');
      }
    }, 100);
  }

  async function handleInitialNotification(router) {
    if (!Notifications || isExpoGo) return;

    try {
      const response = await Notifications.getLastNotificationResponseAsync();
      if (response) {
        const data = response.notification.request.content.data;
        console.log('[FCM] App cold-launched from notification:', data);
        handleNotificationNavigation(router, data);
      }
    } catch (e) {
      console.warn('[FCM] Could not get last notification response:', e);
    }
  }

  function setupNotificationListeners(router) {
    if (!Notifications || isExpoGo) {
      console.log('[FCM] Listeners not set up — running in Expo Go');
      return () => {};
    }

    const tapSubscription = Notifications.addNotificationResponseReceivedListener(response => {
      const data = response.notification.request.content.data;
      console.log('[FCM] Notification tapped:', data);
      handleNotificationNavigation(router, data);
    });

    const foregroundSubscription = Notifications.addNotificationReceivedListener(notification => {
      const data = notification.request.content.data;
      console.log('[FCM] Notification received in foreground, type:', data?.type);
      // Dispatch a global event so any screen can react (e.g. refresh notification list)
      if (typeof global.__onFCMNotification === 'function') {
        global.__onFCMNotification(data);
      }
    });

    return () => {
      try {
        tapSubscription.remove();
        foregroundSubscription.remove();
      } catch (e) {
        console.log('[FCM] Error removing listeners:', e);
      }
    };
  }

  module.exports = {
    registerForPushNotifications,
    clearFcmTokenOnLogout,
    setupNotificationListeners,
    handleInitialNotification,
    default: {
      registerForPushNotifications,
      clearFcmTokenOnLogout,
      setupNotificationListeners,
      handleInitialNotification,
    }
  };
}
