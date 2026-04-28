import React, { useState, useEffect, useCallback } from 'react';
import {
  View, Text, ScrollView, StyleSheet, TouchableOpacity,
  Switch, ActivityIndicator, Alert, Platform,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_BASE_URL, STORAGE_KEYS } from '../constants/config';

const COLORS = {
  background: '#06081A', surface: '#0F1332', surfaceLight: '#171D45',
  primary: '#0EA5E9', success: '#10B981', warning: '#F59E0B',
  danger: '#EF4444', secondary: '#8B5CF6',
  textPrimary: '#F1F5F9', textSecondary: '#94A3B8', textMuted: '#64748B',
  border: '#1E293B', borderLight: 'rgba(255,255,255,0.06)',
};

const SETTINGS = [
  {
    group: 'Laundry Updates',
    icon: 'shirt-outline', color: COLORS.primary,
    items: [
      { key: 'laundry_received',   label: 'Laundry Received',     desc: 'When your laundry arrives at the branch' },
      { key: 'laundry_ready',      label: 'Ready for Pickup',      desc: 'When your laundry is clean and ready' },
      { key: 'laundry_completed',  label: 'Order Completed',       desc: 'When your order is fully completed' },
      { key: 'laundry_cancelled',  label: 'Order Cancelled',       desc: 'When an order is cancelled' },
    ],
  },
  {
    group: 'Pickup & Delivery',
    icon: 'car-outline', color: COLORS.success,
    items: [
      { key: 'pickup_accepted',  label: 'Pickup Accepted',   desc: 'When branch accepts your pickup request' },
      { key: 'pickup_en_route',  label: 'Driver En Route',   desc: 'When driver is on the way to you' },
      { key: 'pickup_completed', label: 'Pickup Completed',  desc: 'When laundry has been picked up' },
      { key: 'delivery_en_route',label: 'Out for Delivery',  desc: 'When your laundry is being delivered' },
    ],
  },
  {
    group: 'Payments',
    icon: 'card-outline', color: COLORS.warning,
    items: [
      { key: 'payment_approved',  label: 'Payment Approved',  desc: 'When your GCash payment is verified' },
      { key: 'payment_rejected',  label: 'Payment Rejected',  desc: 'When payment proof needs resubmission' },
    ],
  },
  {
    group: 'Promotions',
    icon: 'pricetag-outline', color: COLORS.secondary,
    items: [
      { key: 'promotion',         label: 'Promo Alerts',      desc: 'New deals and discounts' },
    ],
  },
];

const STORAGE_KEY = '@washbox:notification_settings';

export default function NotificationSettingsScreen() {
  const [settings, setSettings] = useState({});
  const [saving, setSaving] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => { loadSettings(); }, []);

  const loadSettings = async () => {
    try {
      // Load from local storage first (fast)
      const cached = await AsyncStorage.getItem(STORAGE_KEY);
      const defaults = {};
      SETTINGS.forEach(g => g.items.forEach(i => { defaults[i.key] = true; }));

      if (cached) {
        setSettings({ ...defaults, ...JSON.parse(cached) });
      } else {
        // Try API
        const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
        if (token) {
          const res = await fetch(`${API_BASE_URL}/v1/customer/notification-preferences`, {
            headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
          });
          if (res.ok) {
            const data = await res.json();
            const prefs = data.data?.preferences || {};
            setSettings({ ...defaults, ...prefs });
            await AsyncStorage.setItem(STORAGE_KEY, JSON.stringify({ ...defaults, ...prefs }));
            return;
          }
        }
        setSettings(defaults);
      }
    } catch {
      const defaults = {};
      SETTINGS.forEach(g => g.items.forEach(i => { defaults[i.key] = true; }));
      setSettings(defaults);
    } finally {
      setLoading(false);
    }
  };

  const toggleItem = useCallback((key) => {
    setSettings(prev => ({ ...prev, [key]: !prev[key] }));
  }, []);

  const toggleGroupByKey = useCallback((group) => {
    const keys = group.items.map(i => i.key);
    setSettings(prev => {
      const allOn = keys.every(k => prev[k]);
      const update = {};
      keys.forEach(k => { update[k] = !allOn; });
      return { ...prev, ...update };
    });
  }, []);

  const toggle = toggleItem;
  const toggleGroup = toggleGroupByKey;

  const save = async () => {
    setSaving(true);
    try {
      await AsyncStorage.setItem(STORAGE_KEY, JSON.stringify(settings));
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (token) {
        await fetch(`${API_BASE_URL}/v1/customer/notification-preferences`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify({ preferences: settings }),
        });
      }
      Alert.alert('Saved', 'Notification preferences updated.');
    } catch {
      Alert.alert('Error', 'Failed to save. Settings saved locally.');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <View style={[styles.container, { justifyContent: 'center', alignItems: 'center' }]}>
        <ActivityIndicator size="large" color={COLORS.primary} />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity style={styles.backBtn} onPress={() => router.back()}>
          <Ionicons name="arrow-back" size={22} color={COLORS.textPrimary} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Notification Settings</Text>
        <TouchableOpacity style={styles.saveBtn} onPress={save} disabled={saving}>
          {saving
            ? <ActivityIndicator size="small" color={COLORS.primary} />
            : <Text style={styles.saveBtnText}>Save</Text>}
        </TouchableOpacity>
      </View>

      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ padding: 20, paddingBottom: 60 }}>
        <Text style={styles.subtitle}>Choose which notifications you want to receive.</Text>

        {SETTINGS.map((group) => {
          const allOn = group.items.every(i => settings[i.key]);
          return (
            <View key={group.group} style={styles.card}>
              <TouchableOpacity style={styles.groupHeader} onPress={() => toggleGroup(group)} activeOpacity={0.7}>
                <View style={[styles.groupIcon, { backgroundColor: group.color + '20' }]}>
                  <Ionicons name={group.icon} size={18} color={group.color} />
                </View>
                <Text style={styles.groupTitle}>{group.group}</Text>
                <Switch
                  value={allOn}
                  onValueChange={() => toggleGroup(group)}
                  trackColor={{ false: COLORS.border, true: group.color + '60' }}
                  thumbColor={allOn ? group.color : COLORS.textMuted}
                />
              </TouchableOpacity>

              <View style={styles.divider} />

              {group.items.map((item, idx) => (
                <View key={item.key}>
                  <View style={styles.itemRow}>
                    <View style={styles.itemText}>
                      <Text style={styles.itemLabel}>{item.label}</Text>
                      <Text style={styles.itemDesc}>{item.desc}</Text>
                    </View>
                    <Switch
                      value={!!settings[item.key]}
                      onValueChange={() => toggle(item.key)}
                      trackColor={{ false: COLORS.border, true: COLORS.primary + '60' }}
                      thumbColor={settings[item.key] ? COLORS.primary : COLORS.textMuted}
                    />
                  </View>
                  {idx < group.items.length - 1 && <View style={styles.itemDivider} />}
                </View>
              ))}
            </View>
          );
        })}
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
  saveBtn: { paddingHorizontal: 16, paddingVertical: 8, backgroundColor: COLORS.primary, borderRadius: 10 },
  saveBtnText: { fontSize: 14, fontWeight: '700', color: '#FFF' },
  subtitle: { fontSize: 13, color: COLORS.textMuted, marginBottom: 20, lineHeight: 18 },
  card: { backgroundColor: COLORS.surface, borderRadius: 16, marginBottom: 16, borderWidth: 1, borderColor: COLORS.borderLight, overflow: 'hidden' },
  groupHeader: { flexDirection: 'row', alignItems: 'center', padding: 16, gap: 12 },
  groupIcon: { width: 36, height: 36, borderRadius: 10, justifyContent: 'center', alignItems: 'center' },
  groupTitle: { flex: 1, fontSize: 15, fontWeight: '700', color: COLORS.textPrimary },
  divider: { height: 1, backgroundColor: COLORS.borderLight },
  itemRow: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 16, paddingVertical: 14, gap: 12 },
  itemText: { flex: 1 },
  itemLabel: { fontSize: 14, fontWeight: '600', color: COLORS.textPrimary, marginBottom: 2 },
  itemDesc: { fontSize: 12, color: COLORS.textMuted, lineHeight: 16 },
  itemDivider: { height: 1, backgroundColor: COLORS.borderLight, marginLeft: 16 },
});
