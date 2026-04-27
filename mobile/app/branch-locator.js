import React, { useState, useEffect } from 'react';
import {
  View, Text, ScrollView, StyleSheet, TouchableOpacity,
  ActivityIndicator, RefreshControl, Linking, Platform,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';
import * as Location from 'expo-location';
import { API_BASE_URL } from '../constants/config';

const COLORS = {
  background: '#06081A', surface: '#0F1332', surfaceLight: '#171D45',
  primary: '#0EA5E9', success: '#10B981', warning: '#F59E0B', danger: '#EF4444',
  textPrimary: '#F1F5F9', textSecondary: '#94A3B8', textMuted: '#64748B',
  border: '#1E293B', borderLight: 'rgba(255,255,255,0.06)',
};

const haversine = (lat1, lon1, lat2, lon2) => {
  const R = 6371;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLon = (lon2 - lon1) * Math.PI / 180;
  const a = Math.sin(dLat / 2) ** 2 +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
};

const formatDistance = (km) => {
  if (km == null) return null;
  return km < 1 ? `${Math.round(km * 1000)} m away` : `${km.toFixed(1)} km away`;
};

const isOpen = (hours) => {
  if (!hours || typeof hours !== 'object') return null;
  const day = new Date().toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
  const today = hours[day];
  if (!today || today.status !== 'open') return false;
  try {
    const now = new Date();
    const cur = now.getHours() * 60 + now.getMinutes();
    const [oh, om] = today.open.split(':').map(Number);
    const [ch, cm] = today.close.split(':').map(Number);
    return cur >= oh * 60 + om && cur <= ch * 60 + cm;
  } catch { return false; }
};

const todayHours = (hours) => {
  if (!hours || typeof hours !== 'object') return null;
  const day = new Date().toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
  const today = hours[day];
  if (!today || today.status !== 'open') return 'Closed today';
  const fmt = (t) => {
    const [h, m] = t.split(':').map(Number);
    const ap = h >= 12 ? 'PM' : 'AM';
    return `${h % 12 || 12}:${String(m).padStart(2, '0')} ${ap}`;
  };
  return `${fmt(today.open)} – ${fmt(today.close)}`;
};

export default function BranchLocatorScreen() {
  const [branches, setBranches] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [userLocation, setUserLocation] = useState(null);
  const [locationError, setLocationError] = useState(false);

  useEffect(() => {
    getUserLocation();
    fetchBranches();
  }, []);

  const getUserLocation = async () => {
    try {
      const { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') { setLocationError(true); return; }
      const loc = await Location.getCurrentPositionAsync({ accuracy: Location.Accuracy.Balanced });
      setUserLocation({ lat: loc.coords.latitude, lon: loc.coords.longitude });
    } catch {
      setLocationError(true);
    }
  };

  const fetchBranches = async () => {
    try {
      const res = await fetch(`${API_BASE_URL}/v1/branches`, {
        headers: { 'Accept': 'application/json' },
      });
      if (res.ok) {
        const data = await res.json();
        setBranches(data.data?.branches || data.data || []);
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => { setRefreshing(true); fetchBranches(); getUserLocation(); };

  const openMaps = (branch) => {
    if (!branch.latitude || !branch.longitude) {
      Linking.openURL(`https://maps.google.com/?q=${encodeURIComponent(branch.address)}`);
      return;
    }
    const url = Platform.OS === 'ios'
      ? `maps:0,0?q=${branch.latitude},${branch.longitude}`
      : `geo:${branch.latitude},${branch.longitude}?q=${branch.latitude},${branch.longitude}`;
    Linking.openURL(url);
  };

  const callBranch = (phone) => Linking.openURL(`tel:${phone}`);

  const branchesWithDistance = branches.map(b => ({
    ...b,
    distance: (userLocation && b.latitude && b.longitude)
      ? haversine(userLocation.lat, userLocation.lon, parseFloat(b.latitude), parseFloat(b.longitude))
      : null,
    open: isOpen(b.operating_hours),
    hours: todayHours(b.operating_hours),
  })).sort((a, b) => {
    if (a.distance == null && b.distance == null) return 0;
    if (a.distance == null) return 1;
    if (b.distance == null) return -1;
    return a.distance - b.distance;
  });

  if (loading) {
    return (
      <View style={[styles.container, { justifyContent: 'center', alignItems: 'center' }]}>
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={{ color: COLORS.textSecondary, marginTop: 12 }}>Finding branches...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity style={styles.backBtn} onPress={() => router.back()}>
          <Ionicons name="arrow-back" size={22} color={COLORS.textPrimary} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Branch Locator</Text>
        <View style={{ width: 40 }} />
      </View>

      {locationError && (
        <View style={styles.locationBanner}>
          <Ionicons name="location-outline" size={16} color={COLORS.warning} />
          <Text style={styles.locationBannerText}>Enable location to see distances</Text>
          <TouchableOpacity onPress={getUserLocation}>
            <Text style={styles.locationBannerLink}>Enable</Text>
          </TouchableOpacity>
        </View>
      )}

      <ScrollView
        showsVerticalScrollIndicator={false}
        contentContainerStyle={{ padding: 20, paddingBottom: 60 }}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={COLORS.primary} />}
      >
        <Text style={styles.subtitle}>{branches.length} branch{branches.length !== 1 ? 'es' : ''} available</Text>

        {branchesWithDistance.map((branch) => (
          <View key={branch.id} style={styles.card}>
            {/* Top row */}
            <View style={styles.cardTop}>
              <View style={styles.branchIconWrap}>
                <Ionicons name="business" size={22} color={COLORS.primary} />
              </View>
              <View style={styles.branchInfo}>
                <Text style={styles.branchName}>{branch.name}</Text>
                {branch.distance != null && (
                  <Text style={styles.distance}>{formatDistance(branch.distance)}</Text>
                )}
              </View>
              <View style={[
                styles.statusBadge,
                { backgroundColor: branch.open === true ? COLORS.success + '20' : branch.open === false ? COLORS.danger + '20' : COLORS.textMuted + '20' }
              ]}>
                <View style={[styles.statusDot, {
                  backgroundColor: branch.open === true ? COLORS.success : branch.open === false ? COLORS.danger : COLORS.textMuted
                }]} />
                <Text style={[styles.statusText, {
                  color: branch.open === true ? COLORS.success : branch.open === false ? COLORS.danger : COLORS.textMuted
                }]}>
                  {branch.open === true ? 'Open' : branch.open === false ? 'Closed' : 'Unknown'}
                </Text>
              </View>
            </View>

            {/* Details */}
            <View style={styles.detailRows}>
              {branch.address && (
                <View style={styles.detailRow}>
                  <Ionicons name="location-outline" size={14} color={COLORS.textMuted} />
                  <Text style={styles.detailText}>{branch.address}</Text>
                </View>
              )}
              {branch.hours && (
                <View style={styles.detailRow}>
                  <Ionicons name="time-outline" size={14} color={COLORS.textMuted} />
                  <Text style={styles.detailText}>{branch.hours}</Text>
                </View>
              )}
              {branch.phone && (
                <View style={styles.detailRow}>
                  <Ionicons name="call-outline" size={14} color={COLORS.textMuted} />
                  <Text style={styles.detailText}>{branch.phone}</Text>
                </View>
              )}
            </View>

            {/* Actions */}
            <View style={styles.actions}>
              <TouchableOpacity style={styles.actionBtn} onPress={() => openMaps(branch)} activeOpacity={0.7}>
                <Ionicons name="navigate-outline" size={16} color={COLORS.primary} />
                <Text style={styles.actionBtnText}>Directions</Text>
              </TouchableOpacity>
              {branch.phone && (
                <TouchableOpacity style={[styles.actionBtn, styles.actionBtnGreen]} onPress={() => callBranch(branch.phone)} activeOpacity={0.7}>
                  <Ionicons name="call-outline" size={16} color={COLORS.success} />
                  <Text style={[styles.actionBtnText, { color: COLORS.success }]}>Call</Text>
                </TouchableOpacity>
              )}
            </View>
          </View>
        ))}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: COLORS.background },
  header: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: 20, paddingTop: Platform.OS === 'ios' ? 60 : 48, paddingBottom: 16,
    borderBottomWidth: 1, borderBottomColor: COLORS.borderLight,
  },
  backBtn: { width: 40, height: 40, borderRadius: 12, backgroundColor: COLORS.surface, justifyContent: 'center', alignItems: 'center' },
  headerTitle: { fontSize: 18, fontWeight: '700', color: COLORS.textPrimary },
  locationBanner: {
    flexDirection: 'row', alignItems: 'center', gap: 8,
    backgroundColor: COLORS.warning + '15', paddingHorizontal: 20, paddingVertical: 10,
    borderBottomWidth: 1, borderBottomColor: COLORS.warning + '30',
  },
  locationBannerText: { flex: 1, fontSize: 13, color: COLORS.warning },
  locationBannerLink: { fontSize: 13, fontWeight: '700', color: COLORS.primary },
  subtitle: { fontSize: 13, color: COLORS.textMuted, marginBottom: 16 },
  card: {
    backgroundColor: COLORS.surface, borderRadius: 16, marginBottom: 16,
    borderWidth: 1, borderColor: COLORS.borderLight, overflow: 'hidden',
  },
  cardTop: { flexDirection: 'row', alignItems: 'center', padding: 16, gap: 12 },
  branchIconWrap: {
    width: 44, height: 44, borderRadius: 22,
    backgroundColor: COLORS.primary + '15', justifyContent: 'center', alignItems: 'center',
  },
  branchInfo: { flex: 1 },
  branchName: { fontSize: 16, fontWeight: '700', color: COLORS.textPrimary, marginBottom: 2 },
  distance: { fontSize: 12, color: COLORS.primary, fontWeight: '600' },
  statusBadge: { flexDirection: 'row', alignItems: 'center', gap: 5, paddingHorizontal: 10, paddingVertical: 5, borderRadius: 10 },
  statusDot: { width: 7, height: 7, borderRadius: 3.5 },
  statusText: { fontSize: 11, fontWeight: '700' },
  detailRows: { paddingHorizontal: 16, paddingBottom: 12, gap: 8 },
  detailRow: { flexDirection: 'row', alignItems: 'flex-start', gap: 8 },
  detailText: { flex: 1, fontSize: 13, color: COLORS.textSecondary, lineHeight: 18 },
  actions: {
    flexDirection: 'row', gap: 10, padding: 16,
    borderTopWidth: 1, borderTopColor: COLORS.borderLight,
  },
  actionBtn: {
    flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6,
    backgroundColor: COLORS.primary + '15', paddingVertical: 10, borderRadius: 10,
    borderWidth: 1, borderColor: COLORS.primary + '30',
  },
  actionBtnGreen: { backgroundColor: COLORS.success + '15', borderColor: COLORS.success + '30' },
  actionBtnText: { fontSize: 13, fontWeight: '600', color: COLORS.primary },
});
