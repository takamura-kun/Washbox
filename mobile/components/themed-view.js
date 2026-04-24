import React from 'react';
import { View } from 'react-native';
import { useThemeColor } from '@/hooks/useThemeColor';

/**
 * @param {{
 *   style?: import('react-native').StyleProp<import('react-native').ViewStyle>;
 *   lightColor?: string;
 *   darkColor?: string;
 *   [key: string]: any;
 * }} props
 */
export function ThemedView({ style, lightColor, darkColor, ...rest }) {
  const backgroundColor = useThemeColor({ light: lightColor, dark: darkColor }, 'background');
  
  return <View style={[{ backgroundColor }, style]} {...rest} />;
}
