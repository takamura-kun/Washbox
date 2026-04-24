import { Platform } from 'react-native';
import { API_BASE_URL } from '../constants/config';

// Web platform doesn't support push notifications
export async function registerForPushNotifications(authToken) {
  if (Platform.OS === 'web') {
    console.log('[FCM] Web platform - push notifications not supported');
    return null;
  }
  
  // Delegate to native implementation
  return null;
}

export async function clearFcmTokenOnLogout(authToken) {
  if (Platform.OS === 'web') {
    return;
  }
}

export function setupNotificationListeners(router) {
  if (Platform.OS === 'web') {
    return () => {};
  }
  
  return () => {};
}

export default {
  registerForPushNotifications,
  clearFcmTokenOnLogout,
  setupNotificationListeners
};
