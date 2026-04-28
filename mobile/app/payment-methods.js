import React, { useState, useEffect, useCallback } from 'react';
import {
  View, Text, ScrollView, StyleSheet, TouchableOpacity,
  Alert, ActivityIndicator, RefreshControl, Modal,
  TextInput, StatusBar, Platform,
} from 'react-native';
import { router } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { API_BASE_URL, STORAGE_KEYS, ENDPOINTS } from '../constants/config';

const COLORS = {
  background: '#06081A', surface: '#0F1332', surfaceLight: '#171D45',
  surfaceElevated: '#1E2654',
  primary: '#0EA5E9', primarySoft: 'rgba(14,165,233,0.08)',
  secondary: '#8B5CF6', success: '#10B981', successGlow: 'rgba(16,185,129,0.12)',
  warning: '#F59E0B', danger: '#EF4444',
  textPrimary: '#F1F5F9', textSecondary: '#94A3B8', textMuted: '#64748B',
  border: '#1E293B', borderLight: 'rgba(255,255,255,0.06)',
};

const TYPE_CONFIG = {
  gcash: {
    label: 'GCash',
    icon: 'phone-portrait-outline',
    gradient: ['#007DFF', '#0062CC'],
    color: '#007DFF',
    desc: 'Pay via GCash QR code',
    fields: [
      { key: 'account_number', label: 'GCash Number', placeholder: '09XX XXX XXXX', keyboard: 'phone-pad', maxLength: 13 },
      { key: 'account_name',   label: 'Account Name',  placeholder: 'Full name on GCash', keyboard: 'default' },
    ],
  },
  cash: {
    label: 'Cash',
    icon: 'cash-outline',
    gradient: ['#10B981', '#059669'],
    color: '#10B981',
    desc: 'Pay in cash at branch or on delivery',
    fields: [],
  },
  bank_transfer: {
    label: 'Bank Transfer',
    icon: 'business-outline',
    gradient: ['#8B5CF6', '#7C3AED'],
    color: '#8B5CF6',
    desc: 'Pay via bank transfer',
    fields: [
      { key: 'bank_name',      label: 'Bank Name',      placeholder: 'e.g. BDO, BPI, Metrobank', keyboard: 'default' },
      { key: 'account_number', label: 'Account Number', placeholder: 'Bank account number', keyboard: 'numeric' },
      { key: 'account_name',   label: 'Account Name',   placeholder: 'Name on account', keyboard: 'default' },
    ],
  },
};

const TYPES = Object.keys(TYPE_CONFIG);

export default function PaymentMethodsScreen() {
  const [loading, setLoading]       = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [methods, setMethods]       = useState([]);
  const [showModal, setShowModal]   = useState(false);
  const [editing, setEditing]       = useState(null);
  const [submitting, setSubmitting] = useState(false);

  const [form, setForm] = useState({ type: 'gcash', name: '', details: {} });

  useEffect(() => { fetchMethods(); }, []);

  const fetchMethods = async () => {
    try {
      setLoading(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      const res = await fetch(`${API_BASE_URL}${ENDPOINTS.PAYMENT_METHODS}`, {
        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      });
      if (res.ok) {
        const data = await res.json();
        setMethods(data.data.payment_methods || []);
      } else if (res.status === 401) {
        router.replace('/(auth)/login');
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = useCallback(() => { setRefreshing(true); fetchMethods(); }, []);

  const openAdd = useCallback(() => {
    setEditing(null);
    setForm({ type: 'gcash', name: '', details: {} });
    setShowModal(true);
  }, []);

  const openEdit = useCallback((method) => {
    setEditing(method);
    setForm({ type: method.type, name: method.name, details: method.details || {} });
    setShowModal(true);
  }, []);

  const setDetail = useCallback((key, value) => {
    setForm(prev => ({ ...prev, details: { ...prev.details, [key]: value } }));
  }, []);

  const validate = () => {
    const cfg = TYPE_CONFIG[form.type];
    if (!form.name.trim()) {
      Alert.alert('Missing Info', 'Please enter a label for this payment method (e.g. "My GCash")');
      return false;
    }
    for (const field of cfg.fields) {
      if (!form.details[field.key]?.trim()) {
        Alert.alert('Missing Info', `Please enter your ${field.label}`);
        return false;
      }
    }
    // GCash number validation
    if (form.type === 'gcash') {
      const num = (form.details.account_number || '').replace(/\s/g, '');
      if (!/^(09|\+639)\d{9}$/.test(num)) {
        Alert.alert('Invalid Number', 'Enter a valid PH GCash number (09XX XXX XXXX)');
        return false;
      }
    }
    return true;
  };

  const handleSubmit = async () => {
    if (!validate()) return;
    try {
      setSubmitting(true);
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      const url = editing
        ? `${API_BASE_URL}${ENDPOINTS.PAYMENT_METHODS}/${editing.id}`
        : `${API_BASE_URL}${ENDPOINTS.PAYMENT_METHODS}`;
      const res = await fetch(url, {
        method: editing ? 'PUT' : 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(form),
      });
      const data = await res.json();
      if (res.ok) {
        setShowModal(false);
        await fetchMethods();
      } else {
        Alert.alert('Error', data.message || 'Failed to save');
      }
    } catch (e) {
      Alert.alert('Error', 'Failed to save payment method');
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = useCallback((method) => {
    Alert.alert(
      'Remove Payment Method',
      `Remove "${method.name}"?`,
      [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Remove', style: 'destructive', onPress: async () => {
          try {
            const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
            const res = await fetch(`${API_BASE_URL}${ENDPOINTS.PAYMENT_METHODS}/${method.id}`, {
              method: 'DELETE',
              headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
            });
            if (res.ok) await fetchMethods();
            else Alert.alert('Error', 'Failed to remove');
          } catch { Alert.alert('Error', 'Failed to remove'); }
        }},
      ]
    );
  }, []);

  const handleSetDefault = useCallback(async (id) => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      const res = await fetch(`${API_BASE_URL}${ENDPOINTS.PAYMENT_METHODS}/${id}/set-default`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      });
      if (res.ok) await fetchMethods();
    } catch { Alert.alert('Error', 'Failed to set default'); }
  }, []);

  if (loading) {
    return (
      <View style={[styles.container, { justifyContent: 'center', alignItems: 'center' }]}>
        <StatusBar barStyle="light-content" />
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Loading...</Text>
      </View>
    );
  }

  const cfg = TYPE_CONFIG[form.type] || TYPE_CONFIG.gcash;

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.backBtn} onPress={() => router.back()}>
          <Ionicons name="arrow-back" size={22} color={COLORS.textPrimary} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Payment Methods</Text>
        <TouchableOpacity style={styles.addBtn} onPress={openAdd}>
          <Ionicons name="add" size={22} color="#FFF" />
        </TouchableOpacity>
      </View>

      <ScrollView
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={COLORS.primary} />}
        contentContainerStyle={{ padding: 20, paddingBottom: 60 }}
      >
        {/* Info banner */}
        <View style={styles.infoBanner}>
          <Ionicons name="information-circle-outline" size={16} color={COLORS.primary} />
          <Text style={styles.infoText}>
            Save your preferred payment methods here. When paying for a laundry order, your saved details will be ready to use.
          </Text>
        </View>

        {methods.length === 0 ? (
          <View style={styles.empty}>
            <View style={styles.emptyIcon}>
              <Ionicons name="card-outline" size={40} color={COLORS.textMuted} />
            </View>
            <Text style={styles.emptyTitle}>No Saved Methods</Text>
            <Text style={styles.emptyText}>Add GCash, Cash, or Bank Transfer details for faster checkout.</Text>
            <TouchableOpacity style={styles.emptyBtn} onPress={openAdd} activeOpacity={0.8}>
              <LinearGradient colors={['#0EA5E9', '#3B82F6']} style={styles.emptyBtnGradient}>
                <Ionicons name="add" size={18} color="#FFF" />
                <Text style={styles.emptyBtnText}>Add Payment Method</Text>
              </LinearGradient>
            </TouchableOpacity>
          </View>
        ) : (
          <View style={styles.list}>
            {methods.map((method) => {
              const mc = TYPE_CONFIG[method.type] || TYPE_CONFIG.cash;
              return (
                <View key={method.id} style={[styles.card, method.is_default && styles.cardDefault]}>
                  <LinearGradient colors={[mc.color + '18', mc.color + '06']} style={styles.cardGradient}>
                    <View style={styles.cardTop}>
                      <View style={[styles.cardIcon, { backgroundColor: mc.color + '20' }]}>
                        <Ionicons name={mc.icon} size={22} color={mc.color} />
                      </View>
                      <View style={styles.cardInfo}>
                        <View style={styles.cardNameRow}>
                          <Text style={styles.cardName}>{method.name}</Text>
                          {method.is_default && (
                            <View style={styles.defaultBadge}>
                              <Ionicons name="star" size={10} color={COLORS.warning} />
                              <Text style={styles.defaultText}>Default</Text>
                            </View>
                          )}
                        </View>
                        <Text style={[styles.cardType, { color: mc.color }]}>{mc.label}</Text>
                        {/* Show stored details */}
                        {method.details?.account_number && (
                          <Text style={styles.cardDetail}>
                            <Ionicons name="phone-portrait-outline" size={11} color={COLORS.textMuted} /> {method.details.account_number}
                          </Text>
                        )}
                        {method.details?.account_name && (
                          <Text style={styles.cardDetail}>
                            <Ionicons name="person-outline" size={11} color={COLORS.textMuted} /> {method.details.account_name}
                          </Text>
                        )}
                        {method.details?.bank_name && (
                          <Text style={styles.cardDetail}>
                            <Ionicons name="business-outline" size={11} color={COLORS.textMuted} /> {method.details.bank_name}
                          </Text>
                        )}
                        {method.type === 'cash' && (
                          <Text style={styles.cardDetail}>Pay at branch or on delivery</Text>
                        )}
                      </View>
                    </View>

                    <View style={styles.cardActions}>
                      {!method.is_default && (
                        <TouchableOpacity style={styles.actionChip} onPress={() => handleSetDefault(method.id)} activeOpacity={0.7}>
                          <Ionicons name="star-outline" size={14} color={COLORS.warning} />
                          <Text style={[styles.actionChipText, { color: COLORS.warning }]}>Set Default</Text>
                        </TouchableOpacity>
                      )}
                      <TouchableOpacity style={styles.actionChip} onPress={() => openEdit(method)} activeOpacity={0.7}>
                        <Ionicons name="pencil-outline" size={14} color={COLORS.primary} />
                        <Text style={[styles.actionChipText, { color: COLORS.primary }]}>Edit</Text>
                      </TouchableOpacity>
                      <TouchableOpacity style={[styles.actionChip, styles.actionChipDanger]} onPress={() => handleDelete(method)} activeOpacity={0.7}>
                        <Ionicons name="trash-outline" size={14} color={COLORS.danger} />
                        <Text style={[styles.actionChipText, { color: COLORS.danger }]}>Remove</Text>
                      </TouchableOpacity>
                    </View>
                  </LinearGradient>
                </View>
              );
            })}
          </View>
        )}
      </ScrollView>

      {/* Add / Edit Modal */}
      <Modal visible={showModal} animationType="slide" presentationStyle="pageSheet" onRequestClose={() => setShowModal(false)}>
        <View style={styles.modal}>
          <View style={styles.modalHeader}>
            <TouchableOpacity style={styles.backBtn} onPress={() => setShowModal(false)}>
              <Ionicons name="close" size={22} color={COLORS.textPrimary} />
            </TouchableOpacity>
            <Text style={styles.headerTitle}>{editing ? 'Edit Method' : 'Add Payment Method'}</Text>
            <View style={{ width: 40 }} />
          </View>

          <ScrollView contentContainerStyle={{ padding: 20, paddingBottom: 60 }} keyboardShouldPersistTaps="handled">

            {/* Type selector — only when adding */}
            {!editing && (
              <>
                <Text style={styles.fieldLabel}>Payment Type</Text>
                <View style={styles.typeRow}>
                  {TYPES.map((t) => {
                    const tc = TYPE_CONFIG[t];
                    const selected = form.type === t;
                    return (
                      <TouchableOpacity
                        key={t}
                        style={[styles.typeCard, selected && { borderColor: tc.color, backgroundColor: tc.color + '12' }]}
                        onPress={() => setForm(prev => ({ ...prev, type: t, details: {} }))}
                        activeOpacity={0.7}
                      >
                        <Ionicons name={tc.icon} size={24} color={selected ? tc.color : COLORS.textMuted} />
                        <Text style={[styles.typeLabel, selected && { color: tc.color }]}>{tc.label}</Text>
                      </TouchableOpacity>
                    );
                  })}
                </View>
                <Text style={styles.typeDesc}>{cfg.desc}</Text>
              </>
            )}

            {/* Label */}
            <Text style={styles.fieldLabel}>Label</Text>
            <View style={styles.inputWrap}>
              <Ionicons name="pricetag-outline" size={18} color={COLORS.textMuted} style={styles.inputIcon} />
              <TextInput
                style={styles.input}
                value={form.name}
                onChangeText={(v) => setForm(prev => ({ ...prev, name: v }))}
                placeholder={`e.g. My ${cfg.label}`}
                placeholderTextColor={COLORS.textMuted}
                maxLength={50}
              />
            </View>

            {/* Type-specific fields */}
            {cfg.fields.map((field) => (
              <View key={field.key}>
                <Text style={styles.fieldLabel}>{field.label}</Text>
                <View style={styles.inputWrap}>
                  <Ionicons
                    name={field.key === 'account_number' ? 'keypad-outline' : field.key === 'bank_name' ? 'business-outline' : 'person-outline'}
                    size={18} color={COLORS.textMuted} style={styles.inputIcon}
                  />
                  <TextInput
                    style={styles.input}
                    value={form.details[field.key] || ''}
                    onChangeText={(v) => setDetail(field.key, v)}
                    placeholder={field.placeholder}
                    placeholderTextColor={COLORS.textMuted}
                    keyboardType={field.keyboard}
                    maxLength={field.maxLength}
                  />
                </View>
              </View>
            ))}

            {/* Cash info */}
            {form.type === 'cash' && (
              <View style={styles.cashInfo}>
                <Ionicons name="information-circle-outline" size={16} color={COLORS.success} />
                <Text style={styles.cashInfoText}>
                  Cash payment is collected at the branch or by the delivery staff when your laundry is ready.
                </Text>
              </View>
            )}

            <TouchableOpacity
              style={[styles.submitBtn, submitting && { opacity: 0.7 }]}
              onPress={handleSubmit}
              disabled={submitting}
              activeOpacity={0.85}
            >
              <LinearGradient colors={['#0EA5E9', '#3B82F6']} style={styles.submitGradient}>
                {submitting
                  ? <ActivityIndicator color="#FFF" size="small" />
                  : <Text style={styles.submitText}>{editing ? 'Save Changes' : 'Add Method'}</Text>}
              </LinearGradient>
            </TouchableOpacity>
          </ScrollView>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: COLORS.background },
  loadingText: { color: COLORS.textSecondary, marginTop: 12, fontSize: 14 },
  header: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: 20, paddingTop: Platform.OS === 'ios' ? 60 : 48, paddingBottom: 16,
    borderBottomWidth: 1, borderBottomColor: COLORS.borderLight,
  },
  backBtn: { width: 40, height: 40, borderRadius: 12, backgroundColor: COLORS.surface, justifyContent: 'center', alignItems: 'center' },
  headerTitle: { fontSize: 18, fontWeight: '700', color: COLORS.textPrimary },
  addBtn: { width: 40, height: 40, borderRadius: 12, backgroundColor: COLORS.primary, justifyContent: 'center', alignItems: 'center' },

  infoBanner: {
    flexDirection: 'row', alignItems: 'flex-start', gap: 10,
    backgroundColor: COLORS.primarySoft, borderRadius: 12, padding: 14,
    marginBottom: 20, borderWidth: 1, borderColor: COLORS.primary + '20',
  },
  infoText: { flex: 1, fontSize: 13, color: COLORS.textSecondary, lineHeight: 18 },

  list: { gap: 14 },
  card: { borderRadius: 18, overflow: 'hidden', borderWidth: 1, borderColor: COLORS.borderLight },
  cardDefault: { borderColor: COLORS.warning + '40' },
  cardGradient: { padding: 16 },
  cardTop: { flexDirection: 'row', alignItems: 'flex-start', gap: 14, marginBottom: 14 },
  cardIcon: { width: 46, height: 46, borderRadius: 14, justifyContent: 'center', alignItems: 'center' },
  cardInfo: { flex: 1 },
  cardNameRow: { flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 2 },
  cardName: { fontSize: 16, fontWeight: '700', color: COLORS.textPrimary },
  defaultBadge: { flexDirection: 'row', alignItems: 'center', gap: 4, backgroundColor: COLORS.warning + '20', paddingHorizontal: 7, paddingVertical: 3, borderRadius: 8 },
  defaultText: { fontSize: 10, fontWeight: '700', color: COLORS.warning },
  cardType: { fontSize: 12, fontWeight: '600', marginBottom: 4 },
  cardDetail: { fontSize: 12, color: COLORS.textMuted, marginTop: 2 },
  cardActions: { flexDirection: 'row', gap: 8, flexWrap: 'wrap' },
  actionChip: {
    flexDirection: 'row', alignItems: 'center', gap: 5,
    backgroundColor: COLORS.surfaceElevated, paddingHorizontal: 10, paddingVertical: 7,
    borderRadius: 10, borderWidth: 1, borderColor: COLORS.borderLight,
  },
  actionChipDanger: { borderColor: COLORS.danger + '30', backgroundColor: COLORS.danger + '10' },
  actionChipText: { fontSize: 12, fontWeight: '600' },

  empty: { alignItems: 'center', paddingVertical: 60 },
  emptyIcon: { width: 80, height: 80, borderRadius: 40, backgroundColor: COLORS.surface, justifyContent: 'center', alignItems: 'center', marginBottom: 16 },
  emptyTitle: { fontSize: 18, fontWeight: '700', color: COLORS.textPrimary, marginBottom: 8 },
  emptyText: { fontSize: 14, color: COLORS.textMuted, textAlign: 'center', marginBottom: 24, lineHeight: 20 },
  emptyBtn: { borderRadius: 14, overflow: 'hidden' },
  emptyBtnGradient: { flexDirection: 'row', alignItems: 'center', gap: 8, paddingHorizontal: 24, paddingVertical: 14 },
  emptyBtnText: { fontSize: 15, fontWeight: '700', color: '#FFF' },

  modal: { flex: 1, backgroundColor: COLORS.background },
  modalHeader: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: 20, paddingTop: Platform.OS === 'ios' ? 60 : 48, paddingBottom: 16,
    borderBottomWidth: 1, borderBottomColor: COLORS.borderLight,
  },

  fieldLabel: { fontSize: 13, fontWeight: '700', color: COLORS.textSecondary, marginBottom: 8, marginTop: 20, textTransform: 'uppercase', letterSpacing: 0.5 },
  typeRow: { flexDirection: 'row', gap: 10 },
  typeCard: {
    flex: 1, alignItems: 'center', paddingVertical: 14,
    backgroundColor: COLORS.surface, borderRadius: 14,
    borderWidth: 2, borderColor: COLORS.borderLight,
  },
  typeLabel: { fontSize: 11, fontWeight: '700', color: COLORS.textMuted, marginTop: 6 },
  typeDesc: { fontSize: 12, color: COLORS.textMuted, marginTop: 8, marginBottom: 4 },

  inputWrap: {
    flexDirection: 'row', alignItems: 'center',
    backgroundColor: COLORS.surface, borderRadius: 14,
    borderWidth: 1, borderColor: COLORS.borderLight, paddingHorizontal: 14,
  },
  inputIcon: { marginRight: 10 },
  input: { flex: 1, fontSize: 15, color: COLORS.textPrimary, paddingVertical: 14 },

  cashInfo: {
    flexDirection: 'row', alignItems: 'flex-start', gap: 10,
    backgroundColor: COLORS.successGlow, borderRadius: 12, padding: 14, marginTop: 16,
    borderWidth: 1, borderColor: COLORS.success + '30',
  },
  cashInfoText: { flex: 1, fontSize: 13, color: COLORS.success, lineHeight: 18 },

  submitBtn: { marginTop: 28, borderRadius: 14, overflow: 'hidden' },
  submitGradient: { paddingVertical: 16, alignItems: 'center', justifyContent: 'center' },
  submitText: { fontSize: 16, fontWeight: '700', color: '#FFF' },
});
