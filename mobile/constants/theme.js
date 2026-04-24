// Design System - Unified constants for consistent UI
// Use this across all screens for consistency

export const COLORS = {
  // Background
  background: '#0A0E27',
  surface: '#131937',
  surfaceLight: '#1C2340',
  surfaceElevated: '#252D4C',

  // Primary
  primary: '#0EA5E9',
  primaryDark: '#0284C7',
  primaryLight: '#38BDF8',

  // Accent Colors
  secondary: '#8B5CF6',
  success: '#10B981',
  warning: '#F59E0B',
  danger: '#EF4444',
  info: '#3B82F6',

  // Text
  textPrimary: '#FFFFFF',
  textSecondary: '#94A3B8',
  textMuted: '#64748B',

  // Borders
  border: '#1E293B',
  borderLight: 'rgba(255, 255, 255, 0.06)',

  // Gradients
  gradientPrimary: ['#0EA5E9', '#3B82F6'],
  gradientSecondary: ['#8B5CF6', '#EC4899'],
  gradientSuccess: ['#10B981', '#059669'],
  gradientDanger: ['#EF4444', '#DC2626'],
};

export const SPACING = {
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 20,
  xxl: 24,
  xxxl: 32,
};

export const TYPOGRAPHY = {
  // Headers
  h1: {
    fontSize: 32,
    fontWeight: '800',
    letterSpacing: -0.5,
  },
  h2: {
    fontSize: 28,
    fontWeight: '700',
    letterSpacing: -0.3,
  },
  h3: {
    fontSize: 24,
    fontWeight: '700',
    letterSpacing: -0.2,
  },
  h4: {
    fontSize: 20,
    fontWeight: '700',
  },
  h5: {
    fontSize: 18,
    fontWeight: '600',
  },
  h6: {
    fontSize: 16,
    fontWeight: '600',
  },

  // Body
  body: {
    fontSize: 14,
    fontWeight: '400',
  },
  bodyLarge: {
    fontSize: 16,
    fontWeight: '400',
  },
  bodySmall: {
    fontSize: 12,
    fontWeight: '400',
  },

  // Labels
  label: {
    fontSize: 14,
    fontWeight: '600',
  },
  labelSmall: {
    fontSize: 12,
    fontWeight: '600',
  },

  // Caption
  caption: {
    fontSize: 11,
    fontWeight: '500',
  },
};

export const RADIUS = {
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 20,
  xxl: 24,
  full: 9999,
};

export const SHADOWS = {
  sm: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  md: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 8,
    elevation: 4,
  },
  lg: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.2,
    shadowRadius: 16,
    elevation: 8,
  },
};

export const LAYOUT = {
  // Header heights
  headerHeight: 100,
  headerHeightSmall: 80,
  
  // Tab bar
  tabBarHeight: 70,
  
  // Safe area padding
  screenPaddingHorizontal: 20,
  screenPaddingTop: 60, // iOS safe area
  screenPaddingTopAndroid: 48,
  
  // Card padding
  cardPadding: 16,
  cardPaddingLarge: 20,
};

// Common component styles
export const COMMON_STYLES = {
  // Cards
  card: {
    backgroundColor: COLORS.surface,
    borderRadius: RADIUS.lg,
    padding: LAYOUT.cardPadding,
    borderWidth: 1,
    borderColor: COLORS.border,
    ...SHADOWS.sm,
  },
  
  cardLarge: {
    backgroundColor: COLORS.surface,
    borderRadius: RADIUS.xl,
    padding: LAYOUT.cardPaddingLarge,
    borderWidth: 1,
    borderColor: COLORS.border,
    ...SHADOWS.md,
  },

  // Buttons
  button: {
    borderRadius: RADIUS.md,
    paddingVertical: 14,
    paddingHorizontal: 20,
    alignItems: 'center',
    justifyContent: 'center',
  },

  buttonLarge: {
    borderRadius: RADIUS.lg,
    paddingVertical: 16,
    paddingHorizontal: 24,
    alignItems: 'center',
    justifyContent: 'center',
  },

  // Input
  input: {
    backgroundColor: COLORS.surfaceLight,
    borderRadius: RADIUS.md,
    borderWidth: 1,
    borderColor: COLORS.border,
    paddingHorizontal: 16,
    paddingVertical: 14,
    fontSize: 15,
    color: COLORS.textPrimary,
  },

  // Icon containers
  iconContainer: {
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
  },

  iconContainerSmall: {
    width: 36,
    height: 36,
    borderRadius: 18,
    justifyContent: 'center',
    alignItems: 'center',
  },
};

export default {
  COLORS,
  SPACING,
  TYPOGRAPHY,
  RADIUS,
  SHADOWS,
  LAYOUT,
  COMMON_STYLES,
};
