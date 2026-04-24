import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';

const COLORS = {
  background: '#06081A',
  surface: '#0F1332',
  surfaceLight: '#171D45',
  primary: '#0EA5E9',
  success: '#10B981',
  warning: '#F59E0B',
  danger: '#EF4444',
  textPrimary: '#F1F5F9',
  textSecondary: '#94A3B8',
  textMuted: '#64748B',
  border: '#1E293B',
  borderLight: 'rgba(255, 255, 255, 0.06)',
};

/**
 * WeightValidationWarning Component
 * 
 * Displays weight limit information and validation status
 * 
 * Props:
 * - weight: Current weight entered (number)
 * - service: Service object with min_weight and max_weight
 * - onWeightChange: Callback when weight needs to be adjusted
 */
export default function WeightValidationWarning({ weight, service, onWeightChange }) {
  if (!service || (!service.min_weight && !service.max_weight)) {
    return null;
  }

  const minWeight = service.min_weight || 0;
  const maxWeight = service.max_weight || Infinity;
  const currentWeight = weight || 0;

  // Determine validation status
  const isValid = currentWeight >= minWeight && currentWeight <= maxWeight;
  const isBelowMin = currentWeight > 0 && currentWeight < minWeight;
  const isAboveMax = currentWeight > maxWeight;

  // Get status color and icon
  const getStatusInfo = () => {
    if (currentWeight === 0) {
      return {
        color: COLORS.textMuted,
        icon: 'information-circle-outline',
        status: 'info',
        message: `Weight limit: ${minWeight}-${maxWeight} kg per load`,
      };
    }
    if (isValid) {
      return {
        color: COLORS.success,
        icon: 'checkmark-circle-outline',
        status: 'valid',
        message: `✓ Weight is within limits (${minWeight}-${maxWeight} kg)`,
      };
    }
    if (isBelowMin) {
      return {
        color: COLORS.warning,
        icon: 'alert-circle-outline',
        status: 'warning',
        message: `⚠ Weight is below minimum (${minWeight} kg required)`,
      };
    }
    if (isAboveMax) {
      return {
        color: COLORS.danger,
        icon: 'close-circle-outline',
        status: 'error',
        message: `✗ Cannot create laundry - weight exceeds maximum (${maxWeight} kg max)`,
      };
    }
  };

  const statusInfo = getStatusInfo();

  // Determine background color based on status
  const getBackgroundColor = () => {
    switch (statusInfo.status) {
      case 'valid':
        return COLORS.success + '15';
      case 'warning':
        return COLORS.warning + '15';
      case 'error':
        return COLORS.danger + '15';
      default:
        return COLORS.primary + '15';
    }
  };

  const getBorderColor = () => {
    switch (statusInfo.status) {
      case 'valid':
        return COLORS.success + '40';
      case 'warning':
        return COLORS.warning + '40';
      case 'error':
        return COLORS.danger + '40';
      default:
        return COLORS.primary + '40';
    }
  };

  return (
    <View style={styles.container}>
      {/* Main Warning Card */}
      <View
        style={[
          styles.warningCard,
          {
            backgroundColor: getBackgroundColor(),
            borderColor: getBorderColor(),
          },
        ]}
      >
        <View style={styles.warningContent}>
          <View style={[styles.iconContainer, { backgroundColor: statusInfo.color + '20' }]}>
            <Ionicons
              name={statusInfo.icon}
              size={20}
              color={statusInfo.color}
            />
          </View>

          <View style={styles.textContainer}>
            <Text
              style={[
                styles.warningMessage,
                { color: statusInfo.color },
              ]}
            >
              {statusInfo.message}
            </Text>

            {/* Weight Range Info */}
            <View style={styles.weightRangeContainer}>
              <Text style={styles.weightRangeLabel}>Allowed Range:</Text>
              <Text style={styles.weightRangeValue}>
                {minWeight} kg - {maxWeight} kg per load
              </Text>
            </View>

            {/* Current Weight Display */}
            {currentWeight > 0 && (
              <View style={styles.currentWeightContainer}>
                <Text style={styles.currentWeightLabel}>Your Weight:</Text>
                <Text
                  style={[
                    styles.currentWeightValue,
                    {
                      color: isValid ? COLORS.success : COLORS.danger,
                    },
                  ]}
                >
                  {currentWeight} kg
                </Text>
              </View>
            )}
          </View>
        </View>

        {/* Error Action Button */}
        {isAboveMax && (
          <View style={styles.actionContainer}>
            <TouchableOpacity
              style={styles.actionButton}
              onPress={() => onWeightChange && onWeightChange(maxWeight)}
              activeOpacity={0.7}
            >
              <Ionicons name="pencil" size={16} color={COLORS.danger} />
              <Text style={[styles.actionButtonText, { color: COLORS.danger }]}>
                Adjust to Max
              </Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={[styles.actionButton, styles.splitButton]}
              onPress={() => onWeightChange && onWeightChange(0)}
              activeOpacity={0.7}
            >
              <Ionicons name="information-circle-outline" size={16} color={COLORS.textSecondary} />
              <Text style={[styles.actionButtonText, { color: COLORS.textSecondary }]}>
                Split Order
              </Text>
            </TouchableOpacity>
          </View>
        )}
      </View>

      {/* Weight Limit Explanation */}
      {isAboveMax && (
        <View style={styles.explanationCard}>
          <View style={styles.explanationHeader}>
            <Ionicons name="bulb-outline" size={16} color={COLORS.warning} />
            <Text style={styles.explanationTitle}>Why is there a limit?</Text>
          </View>
          <Text style={styles.explanationText}>
            Each load has a maximum weight capacity of {maxWeight} kg to ensure quality service and proper handling of your laundry.
          </Text>
          <Text style={styles.explanationText}>
            You can split your order into multiple loads:
          </Text>
          <View style={styles.exampleContainer}>
            <Text style={styles.exampleText}>
              • Load 1: {maxWeight} kg
            </Text>
            <Text style={styles.exampleText}>
              • Load 2: {(currentWeight - maxWeight).toFixed(1)} kg
            </Text>
          </View>
        </View>
      )}

      {/* Success Message */}
      {isValid && currentWeight > 0 && (
        <View style={styles.successCard}>
          <Ionicons name="checkmark-circle" size={16} color={COLORS.success} />
          <Text style={styles.successText}>
            Ready to create laundry order
          </Text>
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    gap: 12,
  },

  // Main Warning Card
  warningCard: {
    borderRadius: 16,
    borderWidth: 1,
    padding: 16,
    gap: 12,
  },

  warningContent: {
    flexDirection: 'row',
    gap: 12,
  },

  iconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
    flexShrink: 0,
  },

  textContainer: {
    flex: 1,
    gap: 8,
  },

  warningMessage: {
    fontSize: 14,
    fontWeight: '600',
    lineHeight: 20,
  },

  weightRangeContainer: {
    backgroundColor: 'rgba(0, 0, 0, 0.2)',
    borderRadius: 8,
    padding: 8,
    gap: 4,
  },

  weightRangeLabel: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },

  weightRangeValue: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.textPrimary,
  },

  currentWeightContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: 'rgba(0, 0, 0, 0.2)',
    borderRadius: 8,
    paddingHorizontal: 10,
    paddingVertical: 6,
  },

  currentWeightLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },

  currentWeightValue: {
    fontSize: 14,
    fontWeight: '700',
  },

  // Action Buttons
  actionContainer: {
    flexDirection: 'row',
    gap: 8,
  },

  actionButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    backgroundColor: 'rgba(0, 0, 0, 0.2)',
    borderRadius: 10,
    paddingVertical: 10,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.1)',
  },

  splitButton: {
    backgroundColor: 'rgba(0, 0, 0, 0.1)',
  },

  actionButtonText: {
    fontSize: 12,
    fontWeight: '600',
  },

  // Explanation Card
  explanationCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 12,
    borderWidth: 1,
    borderColor: COLORS.borderLight,
    gap: 8,
  },

  explanationHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },

  explanationTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.textPrimary,
  },

  explanationText: {
    fontSize: 12,
    color: COLORS.textSecondary,
    lineHeight: 18,
  },

  exampleContainer: {
    backgroundColor: 'rgba(0, 0, 0, 0.2)',
    borderRadius: 8,
    padding: 10,
    gap: 4,
  },

  exampleText: {
    fontSize: 12,
    color: COLORS.textSecondary,
    fontFamily: 'monospace',
  },

  // Success Card
  successCard: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    backgroundColor: COLORS.success + '15',
    borderRadius: 12,
    paddingHorizontal: 12,
    paddingVertical: 10,
    borderWidth: 1,
    borderColor: COLORS.success + '40',
  },

  successText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.success,
  },
});
