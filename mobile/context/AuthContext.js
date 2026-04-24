import React, { createContext, useState, useContext, useEffect } from 'react';
import { Platform, Alert } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { STORAGE_KEYS } from '../constants/config';
import { getItem, setItem, removeItem } from '../utils/secureStorage';
import { setLogoutCallback } from '../utils/api';

const AuthContext = createContext({});

// Non-sensitive keys stay in AsyncStorage (no size limit)
const ASYNC_KEYS = [STORAGE_KEYS.PREFERRED_BRANCH, STORAGE_KEYS.DEVICE_TOKEN];

export const AuthProvider = ({ children }) => {
  const [hasToken, setHasToken] = useState(false);
  const [customer, setCustomer] = useState(null);
  const [isReady, setIsReady] = useState(false);

  useEffect(() => {
    const init = async () => {
      try {
        // Root/jailbreak detection
        if (Platform.OS !== 'web') {
          try {
            const Device = require('expo-device');
            if (!Device.isDevice) {
              // Running on emulator — warn but don't block
              console.warn('[Security] Running on emulator/simulator');
            }
          } catch (e) {}
        }

        const token = await getItem(STORAGE_KEYS.TOKEN);
        const customerStr = await getItem(STORAGE_KEYS.CUSTOMER);

        if (token) {
          setHasToken(true);
          if (customerStr) {
            try { setCustomer(JSON.parse(customerStr)); } catch (e) {}
          }
        }
      } catch (e) {
        console.error('[Auth] Init error:', e);
      } finally {
        setIsReady(true);
      }
    };

    init();

    // Wire 401 auto-logout
    setLogoutCallback(() => {
      setHasToken(false);
      setCustomer(null);
    });
  }, []);

  const login = async (token, customerData) => {
    // Store sensitive data in SecureStore
    await setItem(STORAGE_KEYS.TOKEN, token);
    await setItem(STORAGE_KEYS.CUSTOMER, JSON.stringify(customerData));
    setCustomer(customerData);
    setHasToken(true);
  };

  const logout = async () => {
    await removeItem(STORAGE_KEYS.TOKEN);
    await removeItem(STORAGE_KEYS.CUSTOMER);
    // Clear non-sensitive keys too
    await AsyncStorage.multiRemove(ASYNC_KEYS);
    setCustomer(null);
    setHasToken(false);
  };

  const getToken = () => getItem(STORAGE_KEYS.TOKEN);

  return (
    <AuthContext.Provider value={{ hasToken, isReady, customer, login, logout, getToken }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
