/**
 * secureStorage.js
 * Secure storage wrapper — uses expo-secure-store on native, AsyncStorage on web.
 */
import { Platform } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

let SecureStore = null;
if (Platform.OS !== 'web') {
  try {
    SecureStore = require('expo-secure-store');
  } catch (e) {}
}

export const setItem = async (key, value) => {
  if (SecureStore) {
    await SecureStore.setItemAsync(key, value);
  } else {
    await AsyncStorage.setItem(key, value);
  }
};

export const getItem = async (key) => {
  if (SecureStore) {
    return await SecureStore.getItemAsync(key);
  }
  return await AsyncStorage.getItem(key);
};

export const removeItem = async (key) => {
  if (SecureStore) {
    await SecureStore.deleteItemAsync(key);
  } else {
    await AsyncStorage.removeItem(key);
  }
};
