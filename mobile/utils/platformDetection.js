import { Platform } from 'react-native';

export const isWeb = Platform.OS === 'web';
export const isNative = !isWeb;

export const getPlatform = () => {
  if (typeof window !== 'undefined') {
    return 'web';
  }
  return Platform.OS;
};
