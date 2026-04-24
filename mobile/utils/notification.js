/**
 * utils/notification.js
 *
 * WashBox Push Notification Utility
 * Handles FCM token registration, backend sync, and notification listeners.
 *
 * NOTE: This file lives inside app/utils/ so Expo Router requires a
 * default export. The export below satisfies that requirement.
 */

import * as Device from 'expo-device';
import Constants from 'expo-constants';
import { Platform, Alert } from 'react-native';
import { API_BASE_URL } from '../constants/config';

// ─────────────────────────────────────────────────────────────
// Detect Expo Go — FCM remote notifications not supported there
// ─────────────────────────────────────────────────────────────
const isExpoGo = Constants.appOwnership === 'expo';

// Dynamically import expo-notifications ONLY when NOT in Expo Go
// This prevents the import error from occurring
let Notifications = null;
let notificationsModule = null;

if (!isExpoGo) {
    // Use require instead of import to avoid hoisting issues
    notificationsModule = require('expo-notifications');
    Notifications = notificationsModule;
    
    // Configure foreground notification appearance
    Notifications.setNotificationHandler({
        handleNotification: async () => ({
            shouldShowBanner: true,
            shouldShowList: true,
            shouldPlaySound: true,
            shouldSetBadge: true,
        }),
    });
} else {
    console.log('[FCM] Running in Expo Go - push notifications disabled');
}

/**
 * Ask permission + get FCM token + save to backend.
 * Call this once after the customer logs in successfully.
 *
 * @param {string} authToken  - Bearer token from login response (data.data.token)
 * @returns {string|null}     - The FCM token, or null if registration failed
 */
export async function registerForPushNotifications(authToken) {
    // FCM not supported in Expo Go — requires a development build
    if (isExpoGo || !Notifications) {
        console.log('[FCM] Skipping — Expo Go does not support FCM. Use a dev build.');
        return null;
    }

    // Only works on real devices, not simulators
    if (!Device.isDevice) {
        console.log('[FCM] Skipping — not a physical device (simulator/emulator)');
        return null;
    }

    // Android needs a notification channel
    if (Platform.OS === 'android') {
        try {
            // Default channel
            await Notifications.setNotificationChannelAsync('washbox-default', {
                name: 'WashBox Notifications',
                importance: Notifications.AndroidImportance.MAX,
                vibrationPattern: [0, 250, 250, 250],
                lightColor: '#0EA5E9',
                sound: 'default',
            });

            // Order updates channel
            await Notifications.setNotificationChannelAsync('washbox-orders', {
                name: 'Order Updates',
                importance: Notifications.AndroidImportance.HIGH,
                vibrationPattern: [0, 300, 200, 300],
                lightColor: '#10B981',
                sound: 'order_update',
            });

            // Pickup notifications channel
            await Notifications.setNotificationChannelAsync('washbox-pickup', {
                name: 'Pickup Notifications',
                importance: Notifications.AndroidImportance.HIGH,
                vibrationPattern: [0, 500, 100, 500],
                lightColor: '#F59E0B',
                sound: 'pickup_alert',
            });

            // Promotional channel
            await Notifications.setNotificationChannelAsync('washbox-promo', {
                name: 'Promotions',
                importance: Notifications.AndroidImportance.DEFAULT,
                vibrationPattern: [0, 200, 100, 200],
                lightColor: '#EC4899',
                sound: 'promo_chime',
            });
        } catch (channelError) {
            console.warn('[FCM] Failed to set notification channel:', channelError);
        }
    }

    // Check / request permission
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

    // Get the raw FCM device token
    let fcmToken = null;
    try {
        const tokenData = await Notifications.getDevicePushTokenAsync();
        fcmToken = tokenData.data;
        console.log('[FCM] Token obtained:', fcmToken?.substring(0, 20) + '...');
    } catch (err) {
        console.error('[FCM] Failed to get device token:', err);
        return null;
    }

    if (!fcmToken) {
        console.warn('[FCM] Token is empty');
        return null;
    }

    // Save to Laravel backend
    if (authToken) {
        await saveFcmTokenToBackend(fcmToken, authToken);
    } else {
        console.warn('[FCM] No auth token provided — cannot save FCM token to backend');
    }

    return fcmToken;
}

/**
 * Save the FCM token to Laravel so the backend can send notifications.
 * Called automatically by registerForPushNotifications().
 */
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

/**
 * Clear the FCM token from the backend on logout.
 * Call this in your AuthContext logout function.
 *
 * @param {string} authToken  - Laravel Bearer token
 */
export async function clearFcmTokenOnLogout(authToken) {
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

/**
 * Enhanced notification navigation handler
 * Routes users to appropriate screens based on notification data
 */
function handleNotificationNavigation(router, data) {
    console.log('[FCM] Handling notification navigation:', data);
    
    // Handle both laundry_id and laundries_id for compatibility
    const { type, laundry_id, laundries_id, pickup_id, promotion_id, branch_id } = data;
    const actualLaundryId = laundry_id || laundries_id;
    
    // Small delay to ensure navigation is ready
    setTimeout(() => {
        try {
            switch (type) {
                // Laundry-related notifications
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
                
                // Pickup-related notifications
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
                        console.log(`[FCM] Navigating to pickup tracking: /pickup-tracking?pickup_id=${pickup_id}`);
                        router.push(`/pickup-tracking?pickup_id=${pickup_id}`);
                    } else if (actualLaundryId) {
                        console.log(`[FCM] No pickup_id, navigating to laundry: /laundries/${actualLaundryId}`);
                        router.push(`/laundries/${actualLaundryId}`);
                    } else {
                        console.log('[FCM] No pickup_id or laundry_id, going to pickup tab');
                        router.push('/(tabs)/pickup');
                    }
                    break;
                
                // Promotional notifications
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
                
                // System notifications
                case 'system_maintenance':
                case 'app_update':
                case 'branch_closure':
                case 'service_update':
                case 'emergency_alert':
                    console.log('[FCM] System notification, going to notifications screen');
                    router.push('/notifications');
                    break;
                
                // Feedback requests
                case 'feedback_request':
                    if (actualLaundryId) {
                        console.log(`[FCM] Navigating to ratings: /ratings?laundry_id=${actualLaundryId}`);
                        router.push(`/ratings?laundry_id=${actualLaundryId}`);
                    } else {
                        console.log('[FCM] No laundry_id, going to ratings');
                        router.push('/ratings/index');
                    }
                    break;
                
                // Welcome notifications
                case 'welcome':
                    console.log('[FCM] Welcome notification, going to home');
                    router.push('/(tabs)');
                    break;
                
                // Default: go to notifications screen
                default:
                    console.log(`[FCM] Unknown notification type: ${type}, going to notifications`);
                    router.push('/notifications');
                    break;
            }
        } catch (error) {
            console.error('[FCM] Navigation error:', error);
            // Fallback to home screen
            console.log('[FCM] Navigation failed, going to home screen');
            router.push('/(tabs)');
        }
    }, 100);
}

/**
 * Set up listeners for notification tap events.
 * Call this once in your root _layout.js.
 *
 * @param {object} router  - Expo Router's router object
 * @returns {function}     - Cleanup function (call on unmount)
 */
export function setupNotificationListeners(router) {
    // Return empty cleanup function if notifications not available
    if (!Notifications || isExpoGo) {
        console.log('[FCM] Listeners not set up — running in Expo Go');
        return () => {};
    }

    // Fired when user TAPS a notification (app in background or closed)
    const tapSubscription = Notifications.addNotificationResponseReceivedListener(response => {
        const data = response.notification.request.content.data;
        console.log('[FCM] Notification tapped:', data);
        handleNotificationNavigation(router, data);
    });

    // Fired when notification arrives while app is OPEN
    const foregroundSubscription = Notifications.addNotificationReceivedListener(notification => {
        const data = notification.request.content.data;
        console.log('[FCM] Notification received in foreground, type:', data?.type);
        
        // Optional: Show in-app notification or update badge
        // You can add custom logic here for foreground notifications
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

// Add a default export to satisfy Expo Router (required for files in app/utils)
export default {
    registerForPushNotifications,
    clearFcmTokenOnLogout,
    setupNotificationListeners
};