import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  Image,
  Alert,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';

const COLORS = {
  surface: '#0F1332',
  surfaceElevated: '#1E2654',
  primary: '#0EA5E9',
  primarySoft: 'rgba(14, 165, 233, 0.08)',
  danger: '#EF4444',
  textPrimary: '#F1F5F9',
  textMuted: '#64748B',
  borderLight: 'rgba(255, 255, 255, 0.06)',
};

/**
 * Reusable Camera Capture Component
 * 
 * @param {Object} props
 * @param {Object|null} props.photo - Current photo object with uri
 * @param {Function} props.onPhotoCapture - Callback when photo is captured
 * @param {Function} props.onPhotoRemove - Callback when photo is removed
 * @param {string} props.label - Label text (optional)
 * @param {string} props.hint - Hint text (optional)
 * @param {boolean} props.required - Whether photo is required (optional)
 * @param {number} props.quality - Image quality 0-1 (default: 0.8)
 * @param {Array} props.aspect - Aspect ratio [width, height] (default: [4, 3])
 */
export default function CameraCapture({
  photo,
  onPhotoCapture,
  onPhotoRemove,
  label = 'Photo',
  hint = 'Take a photo',
  required = false,
  quality = 0.8,
  aspect = [4, 3],
}) {
  const handleTakePhoto = async () => {
    try {
      const { status } = await ImagePicker.requestCameraPermissionsAsync();
      if (status !== 'granted') {
        Alert.alert(
          'Permission Required',
          'Camera permission is needed to take photos'
        );
        return;
      }

      const result = await ImagePicker.launchCameraAsync({
        mediaTypes: ImagePicker.MediaType.Images,
        allowsEditing: true,
        aspect,
        quality,
      });

      if (!result.canceled && result.assets[0]) {
        onPhotoCapture(result.assets[0]);
      }
    } catch (error) {
      console.error('Error taking photo:', error);
      Alert.alert('Error', 'Failed to take photo. Please try again.');
    }
  };

  const handleRemovePhoto = () => {
    Alert.alert(
      'Remove Photo',
      'Are you sure you want to remove this photo?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Remove',
          style: 'destructive',
          onPress: () => onPhotoRemove(),
        },
      ]
    );
  };

  return (
    <View style={styles.container}>
      {label && (
        <Text style={styles.label}>
          {label}{' '}
          {required ? (
            <Text style={styles.requiredTag}>Required</Text>
          ) : (
            <Text style={styles.optionalTag}>Optional</Text>
          )}
        </Text>
      )}
      {hint && <Text style={styles.hint}>{hint}</Text>}

      {photo ? (
        <View style={styles.photoPreview}>
          <Image source={{ uri: photo.uri }} style={styles.photoImage} />
          <View style={styles.photoOverlay}>
            <TouchableOpacity
              style={styles.photoActionBtn}
              onPress={handleTakePhoto}
            >
              <Ionicons name="camera" size={18} color="#FFF" />
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.photoActionBtn, styles.photoRemoveBtn]}
              onPress={handleRemovePhoto}
            >
              <Ionicons name="trash" size={18} color="#FFF" />
            </TouchableOpacity>
          </View>
        </View>
      ) : (
        <TouchableOpacity
          style={styles.captureButton}
          onPress={handleTakePhoto}
          activeOpacity={0.7}
        >
          <View style={styles.captureIcon}>
            <Ionicons name="camera" size={32} color={COLORS.primary} />
          </View>
          <Text style={styles.captureText}>Take Photo</Text>
          <Text style={styles.captureHint}>Camera only - for verification</Text>
        </TouchableOpacity>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    marginBottom: 18,
  },
  label: {
    fontSize: 12,
    fontWeight: '700',
    color: COLORS.textPrimary,
    marginBottom: 8,
    letterSpacing: 0.3,
  },
  requiredTag: {
    fontSize: 10,
    fontWeight: '700',
    color: COLORS.danger,
  },
  optionalTag: {
    fontSize: 10,
    fontWeight: '500',
    color: COLORS.textMuted,
    fontStyle: 'italic',
  },
  hint: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginBottom: 10,
    marginTop: -4,
  },
  captureButton: {
    backgroundColor: COLORS.surfaceElevated,
    borderRadius: 14,
    padding: 20,
    alignItems: 'center',
    gap: 8,
    borderWidth: 1,
    borderColor: COLORS.borderLight,
  },
  captureIcon: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: COLORS.primarySoft,
    justifyContent: 'center',
    alignItems: 'center',
  },
  captureText: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.textPrimary,
  },
  captureHint: {
    fontSize: 11,
    color: COLORS.textMuted,
    fontStyle: 'italic',
  },
  photoPreview: {
    position: 'relative',
    borderRadius: 14,
    overflow: 'hidden',
    backgroundColor: COLORS.surfaceElevated,
  },
  photoImage: {
    width: '100%',
    height: 200,
    resizeMode: 'cover',
  },
  photoOverlay: {
    position: 'absolute',
    bottom: 12,
    right: 12,
    flexDirection: 'row',
    gap: 8,
  },
  photoActionBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(0, 0, 0, 0.7)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  photoRemoveBtn: {
    backgroundColor: COLORS.danger + 'CC',
  },
});
