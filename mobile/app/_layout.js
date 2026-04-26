// app/_layout.js
import { useEffect } from 'react';
import { Stack, useRouter, useSegments } from 'expo-router';
import * as SplashScreen from 'expo-splash-screen';
import { AuthProvider, useAuth } from '../context/AuthContext';
import { Platform } from 'react-native';
import { setupNotificationListeners, registerForPushNotifications, handleInitialNotification } from '../utils/notification';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { STORAGE_KEYS } from '../constants/config';

// Polyfill window object for native platforms BEFORE any imports that use it
if (Platform.OS !== 'web') {
  if (typeof window === 'undefined') {
    global.window = {};
  }
  
  // Add required window properties
  if (!global.window.addEventListener) {
    global.window.addEventListener = () => {};
  }
  if (!global.window.removeEventListener) {
    global.window.removeEventListener = () => {};
  }
  if (!global.window.location) {
    global.window.location = { href: '' };
  }
  if (!global.window.history) {
    global.window.history = { pushState: () => {}, replaceState: () => {} };
  }
  if (!global.window.document) {
    global.window.document = {};
  }
}

// Import web CSS only on web platform
if (Platform.OS === 'web') {
  require('../styles/globals.css');
}

// Prevent splash screen from auto-hiding
SplashScreen.preventAutoHideAsync();

function RootLayoutNav() {
  const { hasToken, isReady } = useAuth();
  const segments = useSegments();
  const router = useRouter();

  useEffect(() => {
    if (!isReady) return;

    const inAuthGroup = segments[0] === '(auth)';
    const inWelcome = segments[0] === 'welcome';

    if (!hasToken && !inAuthGroup && !inWelcome) {
      // Force unauthenticated users to welcome page
      router.replace('/welcome');
    } else if (hasToken && (inAuthGroup || inWelcome)) {
      // Redirect authenticated users away from login/register/welcome
      router.replace('/(tabs)');
    }
    
    SplashScreen.hideAsync();
  }, [hasToken, isReady, segments]);

  // Register FCM token and setup notification listeners when authenticated
  useEffect(() => {
    if (!hasToken || !isReady) return;
    
    console.log('[FCM] Setting up notification listeners');
    const cleanup = setupNotificationListeners(router);

    // Register FCM token with backend
    AsyncStorage.getItem(STORAGE_KEYS.TOKEN).then(authToken => {
      if (authToken) registerForPushNotifications(authToken);
    });

    // Handle notification that cold-launched the app (app was fully killed)
    handleInitialNotification(router);
    
    return cleanup;
  }, [hasToken, isReady, router]);

  if (!isReady) return null;

  return (
    <Stack screenOptions={{ 
      headerShown: false,
      contentStyle: {
        backgroundColor: '#0A1128',
      },
      animation: 'fade',
    }}>
      {/* Welcome Screen - Landing page before auth */}
      <Stack.Screen name="welcome" />
      
      {/* Auth Group - Login/Register */}
      <Stack.Screen name="(auth)" />
      
      {/* 2FA Verification */}
      <Stack.Screen
        name="(auth)/verify-2fa"
        options={{
          presentation: 'card',
          animation: 'slide_from_right',
        }}
      />
      
      {/* Main Tabs - Home, Orders, Profile */}
      <Stack.Screen name="(tabs)" />
      
      {/* Laundry Details - matches app/laundries/[id].js */}
      <Stack.Screen 
        name="laundries/[id]" 
        options={{
          presentation: 'card',
          animation: 'slide_from_right',
        }}
      />
      
      {/* Pickups History - matches app/pickups/index.js */}
      <Stack.Screen 
        name="pickups/index" 
        options={{
          presentation: 'card',
          animation: 'slide_from_right',
        }}
      />
      
      {/* Notifications - matches app/notifications.js */}
      <Stack.Screen 
        name="notifications" 
        options={{
          presentation: 'card',
          animation: 'slide_from_right',
        }}
      />
      
      {/* Laundry Confirm - matches app/laundry-confirm.js */}
      <Stack.Screen 
        name="laundry-confirm" 
        options={{
          presentation: 'modal',
          animation: 'slide_from_bottom',
        }}
      />
      
      {/* Profile Edit - matches app/profile/edit.js */}
      <Stack.Screen 
        name="profile/edit" 
        options={{
          presentation: 'card',
          animation: 'slide_from_right',
        }}
      />
      
      {/* Promotions - matches app/promotions/index.js */}
      <Stack.Screen 
        name="promotions/index" 
        options={{
          presentation: 'card',
          animation: 'slide_from_right',
        }}
      />
      
      {/* Ratings - matches app/ratings/index.js */}
      <Stack.Screen 
        name="ratings/index" 
        options={{
          presentation: 'card',
          animation: 'slide_from_right',
        }}
      />
      
      {/* Payment Methods - matches app/payment-methods.js */}
      <Stack.Screen 
        name="payment-methods" 
        options={{
          presentation: 'card',
          animation: 'slide_from_right',
        }}
      />
      
      {/* Saved Addresses - matches app/saved-addresses.js */}
      <Stack.Screen 
        name="saved-addresses" 
        options={{
          presentation: 'card',
          animation: 'slide_from_right',
        }}
      />
      
      {/* Privacy & Security - matches app/privacy-security.js */}
      <Stack.Screen 
        name="privacy-security" 
        options={{
          presentation: 'card',
          animation: 'slide_from_right',
        }}
      />
      
      {/* Network Diagnostic - matches app/network-diagnostic.js */}
      <Stack.Screen 
        name="network-diagnostic" 
        options={{
          presentation: 'card',
          animation: 'slide_from_right',
        }}
      />
      
      {/* Feature Integrations - matches app/feature-integrations.js */}
      <Stack.Screen 
        name="feature-integrations" 
        options={{
          presentation: 'card',
          animation: 'slide_from_right',
        }}
      />
      
      {/* Pickup Tracking - matches app/pickup-tracking.js */}
      <Stack.Screen 
        name="pickup-tracking" 
        options={{
          presentation: 'card',
          animation: 'slide_from_right',
        }}
      />
    </Stack>
  );
}

export default function RootLayout() {
  return (
    <AuthProvider>
      <RootLayoutNav />
    </AuthProvider>
  );
}
