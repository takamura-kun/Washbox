// utils/polyfills.js
import { Platform } from 'react-native';

// Polyfill window object for native platforms
if (Platform.OS !== 'web' && typeof window === 'undefined') {
  global.window = {
    addEventListener: () => {},
    removeEventListener: () => {},
    location: { href: '' },
    document: {
      addEventListener: () => {},
      removeEventListener: () => {},
    },
  };
}

export default {};
