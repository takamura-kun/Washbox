import React from 'react';
import {
  View,
  Text,
  Modal,
  TouchableOpacity,
  Image,
  StyleSheet,
  Dimensions,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

export default function PromotionModal({ visible, promotion, onClose }) {
  if (!promotion) return null;

  const hasImage = promotion.banner_image;

  return (
    <Modal
      visible={visible}
      transparent
      animationType="fade"
      onRequestClose={onClose}
    >
      <View style={styles.overlay}>
        <View style={styles.modalContainer}>
          <TouchableOpacity style={styles.closeButton} onPress={onClose} activeOpacity={0.8}>
            <View style={styles.closeCircle}>
              <Ionicons name="close" size={24} color="#fff" />
            </View>
          </TouchableOpacity>

          {hasImage ? (
            // Full-screen image layout with overlay content
            <View style={styles.imageLayout}>
              <Image source={{ uri: promotion.banner_image }} style={styles.fullImage} resizeMode="cover" />
              <LinearGradient
                colors={['rgba(5,7,26,0.2)', 'rgba(5,7,26,0.7)', 'rgba(5,7,26,0.95)']}
                style={styles.imageOverlay}
              >
                <View style={styles.imageContent}>
                  <Text style={styles.imageTitle}>{promotion.poster_title || promotion.name}</Text>
                  {promotion.poster_subtitle && (
                    <Text style={styles.imageSubtitle}>{promotion.poster_subtitle}</Text>
                  )}

                  {promotion.display_price && (
                    <View style={styles.imagePriceBadge}>
                      <Text style={styles.imagePriceText}>₱{parseFloat(promotion.display_price).toFixed(0)}</Text>
                      {promotion.price_unit && <Text style={styles.imageUnitText}>/{promotion.price_unit}</Text>}
                    </View>
                  )}

                  {promotion.poster_features && promotion.poster_features.length > 0 && (
                    <View style={styles.imageFeaturesContainer}>
                      {promotion.poster_features.map((feature, index) => (
                        <View key={index} style={styles.imageFeatureRow}>
                          <Ionicons name="checkmark-circle" size={18} color="#10B981" />
                          <Text style={styles.imageFeatureText}>{feature}</Text>
                        </View>
                      ))}
                    </View>
                  )}

                  {promotion.promo_code && (
                    <View style={styles.imageCodeContainer}>
                      <Text style={styles.imageCodeLabel}>CODE:</Text>
                      <View style={styles.imageCodeBadge}>
                        <Text style={styles.imageCodeText}>{promotion.promo_code}</Text>
                      </View>
                    </View>
                  )}

                  {promotion.end_date && (
                    <View style={styles.imageValidRow}>
                      <Ionicons name="time-outline" size={14} color="rgba(255,255,255,0.6)" />
                      <Text style={styles.imageValidText}>Valid until {new Date(promotion.end_date).toLocaleDateString()}</Text>
                    </View>
                  )}
                </View>
              </LinearGradient>
            </View>
          ) : (
            // Original layout without image
            <LinearGradient
              colors={['rgba(14,165,233,0.15)', 'rgba(5,7,26,0.95)']}
              style={styles.content}
            >
              <Text style={styles.title}>{promotion.poster_title || promotion.name}</Text>
              <Text style={styles.description}>{promotion.poster_subtitle || promotion.description}</Text>

              {promotion.display_price && (
                <View style={styles.priceBadge}>
                  <Text style={styles.priceText}>₱{parseFloat(promotion.display_price).toFixed(0)}</Text>
                  {promotion.price_unit && <Text style={styles.unitText}>/{promotion.price_unit}</Text>}
                </View>
              )}

              {promotion.poster_features && promotion.poster_features.length > 0 && (
                <View style={styles.featuresContainer}>
                  {promotion.poster_features.map((feature, index) => (
                    <View key={index} style={styles.featureRow}>
                      <Ionicons name="checkmark-circle" size={16} color="#10B981" />
                      <Text style={styles.featureText}>{feature}</Text>
                    </View>
                  ))}
                </View>
              )}

              {promotion.promo_code && (
                <View style={styles.codeContainer}>
                  <Text style={styles.codeLabel}>Promo Code:</Text>
                  <View style={styles.codeBadge}>
                    <Text style={styles.codeText}>{promotion.promo_code}</Text>
                  </View>
                </View>
              )}

              {promotion.end_date && (
                <View style={styles.validRow}>
                  <Ionicons name="time-outline" size={16} color="#64748B" />
                  <Text style={styles.validText}>Valid until {new Date(promotion.end_date).toLocaleDateString()}</Text>
                </View>
              )}

              {promotion.poster_notes && (
                <Text style={styles.notes}>{promotion.poster_notes}</Text>
              )}
            </LinearGradient>
          )}
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.85)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  modalContainer: {
    width: SCREEN_WIDTH - 48,
    maxWidth: 400,
    backgroundColor: '#0C1030',
    borderRadius: 24,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: 'rgba(56,189,248,0.2)',
  },
  closeButton: {
    position: 'absolute',
    top: 16,
    right: 16,
    zIndex: 10,
  },
  closeCircle: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(0,0,0,0.6)',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.2)',
  },
  image: {
    width: '100%',
    height: 280,
  },
  // Image layout styles
  imageLayout: {
    width: '100%',
    height: 500,
    position: 'relative',
  },
  fullImage: {
    width: '100%',
    height: '100%',
    position: 'absolute',
  },
  imageOverlay: {
    width: '100%',
    height: '100%',
    justifyContent: 'flex-end',
  },
  imageContent: {
    padding: 24,
    paddingBottom: 32,
  },
  imageTitle: {
    fontSize: 28,
    fontWeight: '900',
    color: '#FFFFFF',
    marginBottom: 8,
    letterSpacing: -0.5,
    textShadowColor: 'rgba(0,0,0,0.5)',
    textShadowOffset: { width: 0, height: 2 },
    textShadowRadius: 4,
  },
  imageSubtitle: {
    fontSize: 15,
    color: 'rgba(255,255,255,0.9)',
    marginBottom: 16,
    fontWeight: '600',
  },
  imagePriceBadge: {
    flexDirection: 'row',
    alignItems: 'baseline',
    alignSelf: 'flex-start',
    backgroundColor: 'rgba(255,255,255,0.95)',
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 24,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 8,
  },
  imagePriceText: {
    fontSize: 32,
    fontWeight: '900',
    color: '#0EA5E9',
    letterSpacing: -1,
  },
  imageUnitText: {
    fontSize: 16,
    fontWeight: '700',
    color: '#0EA5E9',
    marginLeft: 4,
  },
  imageFeaturesContainer: {
    marginBottom: 16,
    gap: 10,
  },
  imageFeatureRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    backgroundColor: 'rgba(255,255,255,0.15)',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.2)',
  },
  imageFeatureText: {
    fontSize: 14,
    color: '#FFFFFF',
    flex: 1,
    fontWeight: '600',
  },
  imageCodeContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginBottom: 12,
    backgroundColor: 'rgba(167,139,250,0.2)',
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: 'rgba(167,139,250,0.4)',
  },
  imageCodeLabel: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.8)',
    fontWeight: '700',
    letterSpacing: 1,
  },
  imageCodeBadge: {
    backgroundColor: 'rgba(255,255,255,0.95)',
    paddingHorizontal: 14,
    paddingVertical: 6,
    borderRadius: 12,
  },
  imageCodeText: {
    fontSize: 16,
    fontWeight: '900',
    color: '#8B5CF6',
    letterSpacing: 1.5,
  },
  imageValidRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  imageValidText: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.7)',
    fontWeight: '600',
  },
  content: {
    padding: 24,
  },
  title: {
    fontSize: 24,
    fontWeight: '900',
    color: '#F8FAFC',
    marginBottom: 12,
    letterSpacing: -0.5,
  },
  description: {
    fontSize: 14,
    color: '#CBD5E1',
    lineHeight: 22,
    marginBottom: 16,
  },
  priceBadge: {
    flexDirection: 'row',
    alignItems: 'baseline',
    alignSelf: 'flex-start',
    backgroundColor: 'rgba(56,189,248,0.15)',
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: 'rgba(56,189,248,0.3)',
    marginBottom: 16,
  },
  priceText: {
    fontSize: 24,
    fontWeight: '900',
    color: '#38BDF8',
    letterSpacing: -0.5,
  },
  unitText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#38BDF8',
    marginLeft: 4,
  },
  featuresContainer: {
    marginBottom: 16,
    gap: 8,
  },
  featureRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  featureText: {
    fontSize: 13,
    color: '#CBD5E1',
    flex: 1,
  },
  codeContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginBottom: 16,
  },
  codeLabel: {
    fontSize: 13,
    color: '#64748B',
    fontWeight: '600',
  },
  codeBadge: {
    backgroundColor: 'rgba(167,139,250,0.15)',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: 'rgba(167,139,250,0.3)',
  },
  codeText: {
    fontSize: 14,
    fontWeight: '800',
    color: '#A78BFA',
    letterSpacing: 1,
  },
  validRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  validText: {
    fontSize: 12,
    color: '#64748B',
    fontWeight: '600',
  },
  notes: {
    fontSize: 11,
    color: '#64748B',
    fontStyle: 'italic',
    marginTop: 12,
    lineHeight: 16,
  },
});
