/**
 * secureStorage.js
 * Secure storage wrapper.
 * TODO: Replace with expo-secure-store when upgrading to expo@55+
 */
import AsyncStorage from '@react-native-async-storage/async-storage';

export const setItem = (key, value) => AsyncStorage.setItem(key, value);
export const getItem = (key) => AsyncStorage.getItem(key);
export const removeItem = (key) => AsyncStorage.removeItem(key);
