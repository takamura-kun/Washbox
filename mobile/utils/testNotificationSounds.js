import { Platform, Vibration } from 'react-native';

export async function checkNotificationPermissions() {
  if (Platform.OS === 'web') {
    return { granted: typeof Notification === 'undefined' || Notification.permission !== 'denied' };
  }

  try {
    const Notifications = require('expo-notifications');
    const permissions = await Notifications.getPermissionsAsync();
    return { granted: permissions.granted || permissions.status === 'granted' };
  } catch (error) {
    return { granted: false, error: error.message };
  }
}

const SOUND_MAP = {
  laundry_received: 'order_update.mp3',
  laundry_ready: 'order_update.mp3',
  laundry_completed: 'order_update.mp3',
  laundry_cancelled: 'order_update.mp3',
  payment_pending: 'order_update.mp3',
  payment_received: 'order_update.mp3',
  payment_verification: 'order_update.mp3',
  payment_rejected: 'order_update.mp3',
  pickup_submitted: 'pickup_alert.mp3',
  pickup_accepted: 'pickup_alert.mp3',
  pickup_en_route: 'pickup_alert.mp3',
  pickup_completed: 'pickup_alert.mp3',
  pickup_cancelled: 'pickup_alert.mp3',
  delivery_scheduled: 'pickup_alert.mp3',
  delivery_en_route: 'pickup_alert.mp3',
  delivery_completed: 'pickup_alert.mp3',
  delivery_failed: 'pickup_alert.mp3',
  unclaimed_reminder: 'pickup_alert.mp3',
  promotion: 'promo_chime.mp3',
  welcome: 'promo_chime.mp3',
  feedback_request: 'promo_chime.mp3',
  loyalty_reward: 'promo_chime.mp3',
  birthday_greeting: 'promo_chime.mp3',
  app_update: 'promo_chime.mp3',
};

export async function testNotificationSound(type = 'default') {
  if (Platform.OS !== 'web') {
    Vibration.vibrate(type === 'laundry_ready' ? [0, 300, 150, 300] : 250);
  }

  try {
    const Notifications = require('expo-notifications');
    const sound = SOUND_MAP[type] || 'default';

    await Notifications.scheduleNotificationAsync({
      content: {
        title: 'WashBox Test Notification',
        body: `Testing notification sound for ${type}.`,
        sound,
        data: { type },
      },
      trigger: null,
    });

    return true;
  } catch (error) {
    if (Platform.OS === 'web') {
      return true;
    }

    throw error;
  }
}
