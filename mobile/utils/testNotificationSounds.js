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

export async function testNotificationSound(type = 'default') {
  if (Platform.OS !== 'web') {
    Vibration.vibrate(type === 'laundry_ready' ? [0, 300, 150, 300] : 250);
  }

  try {
    const Notifications = require('expo-notifications');

    await Notifications.scheduleNotificationAsync({
      content: {
        title: 'WashBox Test Notification',
        body: `Testing notification sound for ${type}.`,
        sound: 'default',
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
