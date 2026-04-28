import React, { useState, useCallback } from 'react';
import {
  View, Text, ScrollView, StyleSheet, TouchableOpacity,
  TextInput, Platform, Linking,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';

const COLORS = {
  background: '#06081A', surface: '#0F1332', surfaceLight: '#171D45',
  primary: '#0EA5E9', success: '#10B981', warning: '#F59E0B',
  textPrimary: '#F1F5F9', textSecondary: '#94A3B8', textMuted: '#64748B',
  border: '#1E293B', borderLight: 'rgba(255,255,255,0.06)',
};

const FAQS = [
  {
    category: 'Getting Started',
    icon: 'rocket-outline', color: COLORS.primary,
    items: [
      {
        q: 'How do I schedule a pickup?',
        a: 'Go to the Pickup tab, set your address, choose a date and time, then tap "Request Pickup". We\'ll confirm shortly.',
      },
      {
        q: 'What areas do you service?',
        a: 'We currently service areas near our registered branches. Your address must be within the delivery zone of your assigned branch.',
      },
      {
        q: 'How do I track my laundry?',
        a: 'Go to the Laundry tab and tap any order to see its real-time status. Active pickups also have a "Track" button.',
      },
    ],
  },
  {
    category: 'Pricing & Payment',
    icon: 'card-outline', color: COLORS.success,
    items: [
      {
        q: 'How is pricing calculated?',
        a: 'Pricing is based on the service type — either per kilo or per load. You can see the exact price on each service card before booking.',
      },
      {
        q: 'What payment methods are accepted?',
        a: 'We accept GCash (scan QR code and upload proof) and Cash (pay at the branch or to the delivery staff).',
      },
      {
        q: 'When do I pay?',
        a: 'For walk-in orders, pay when your laundry is ready for pickup. For delivery orders, pay when the laundry is delivered to you.',
      },
      {
        q: 'How long does GCash verification take?',
        a: 'Usually within a few hours during business hours. Upload a clear, uncropped screenshot of your payment confirmation.',
      },
    ],
  },
  {
    category: 'Pickup & Delivery',
    icon: 'car-outline', color: '#8B5CF6',
    items: [
      {
        q: 'Is pickup and delivery free?',
        a: 'Pickup and delivery fees depend on your distance from the branch. Some branches offer free pickup within a certain radius.',
      },
      {
        q: 'Can I cancel a pickup request?',
        a: 'Yes, you can cancel a pickup while it\'s still "Pending". Once accepted by the branch, cancellation is no longer available.',
      },
      {
        q: 'What if I miss the pickup?',
        a: 'Contact your branch directly using the phone number shown in the app. You can reschedule a new pickup request.',
      },
      {
        q: 'Will my laundry be delivered to the same address?',
        a: 'Yes! We pick up and deliver back to the same address you provided in your pickup request.',
      },
    ],
  },
  {
    category: 'Account & Profile',
    icon: 'person-outline', color: COLORS.warning,
    items: [
      {
        q: 'How do I change my address?',
        a: 'Go to Menu → Saved Addresses to add or update your addresses. You can also edit your profile for your default address.',
      },
      {
        q: 'Can I change my branch?',
        a: 'Contact support to change your assigned branch. Branch assignment affects which staff handles your orders.',
      },
      {
        q: 'How do I reset my password?',
        a: 'On the login screen, tap "Forgot Password?" and enter your email. You\'ll receive a 6-digit reset code.',
      },
    ],
  },
];

export default function FAQScreen() {
  const [search, setSearch] = useState('');
  const [expanded, setExpanded] = useState({});

  const toggle = useCallback((key) => setExpanded(prev => ({ ...prev, [key]: !prev[key] })), []);

  const filtered = FAQS.map(cat => ({
    ...cat,
    items: cat.items.filter(
      item =>
        !search ||
        item.q.toLowerCase().includes(search.toLowerCase()) ||
        item.a.toLowerCase().includes(search.toLowerCase())
    ),
  })).filter(cat => cat.items.length > 0);

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity style={styles.backBtn} onPress={() => router.back()}>
          <Ionicons name="arrow-back" size={22} color={COLORS.textPrimary} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Help Center</Text>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ padding: 20, paddingBottom: 60 }}>
        {/* Search */}
        <View style={styles.searchBox}>
          <Ionicons name="search-outline" size={18} color={COLORS.textMuted} />
          <TextInput
            style={styles.searchInput}
            placeholder="Search questions..."
            placeholderTextColor={COLORS.textMuted}
            value={search}
            onChangeText={setSearch}
          />
          {search.length > 0 && (
            <TouchableOpacity onPress={() => setSearch('')}>
              <Ionicons name="close-circle" size={18} color={COLORS.textMuted} />
            </TouchableOpacity>
          )}
        </View>

        {filtered.length === 0 && (
          <View style={styles.empty}>
            <Ionicons name="search-outline" size={48} color={COLORS.textMuted} />
            <Text style={styles.emptyText}>No results for "{search}"</Text>
          </View>
        )}

        {filtered.map((cat) => (
          <View key={cat.category} style={styles.section}>
            <View style={styles.catHeader}>
              <View style={[styles.catIcon, { backgroundColor: cat.color + '20' }]}>
                <Ionicons name={cat.icon} size={16} color={cat.color} />
              </View>
              <Text style={styles.catTitle}>{cat.category}</Text>
            </View>

            <View style={styles.card}>
              {cat.items.map((item, idx) => {
                const key = `${cat.category}-${idx}`;
                const open = !!expanded[key];
                return (
                  <View key={key}>
                    <TouchableOpacity style={styles.faqRow} onPress={() => toggle(key)} activeOpacity={0.7}>
                      <Text style={styles.question}>{item.q}</Text>
                      <Ionicons name={open ? 'chevron-up' : 'chevron-down'} size={18} color={COLORS.textMuted} />
                    </TouchableOpacity>
                    {open && (
                      <View style={styles.answerBox}>
                        <Text style={styles.answer}>{item.a}</Text>
                      </View>
                    )}
                    {idx < cat.items.length - 1 && <View style={styles.divider} />}
                  </View>
                );
              })}
            </View>
          </View>
        ))}

        {/* Contact Support */}
        <View style={styles.contactCard}>
          <Ionicons name="headset-outline" size={28} color={COLORS.primary} />
          <Text style={styles.contactTitle}>Still need help?</Text>
          <Text style={styles.contactText}>Contact your branch directly or reach us via email.</Text>
          <TouchableOpacity style={styles.contactBtn} onPress={() => Linking.openURL('mailto:support@washbox.com')}>
            <Ionicons name="mail-outline" size={16} color="#FFF" />
            <Text style={styles.contactBtnText}>Email Support</Text>
          </TouchableOpacity>
        </View>
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
  searchBox: {
    flexDirection: 'row', alignItems: 'center', gap: 10,
    backgroundColor: COLORS.surface, borderRadius: 14, paddingHorizontal: 14, paddingVertical: 12,
    marginBottom: 24, borderWidth: 1, borderColor: COLORS.borderLight,
  },
  searchInput: { flex: 1, fontSize: 14, color: COLORS.textPrimary },
  empty: { alignItems: 'center', paddingVertical: 60 },
  emptyText: { color: COLORS.textMuted, marginTop: 12, fontSize: 14 },
  section: { marginBottom: 20 },
  catHeader: { flexDirection: 'row', alignItems: 'center', gap: 10, marginBottom: 10 },
  catIcon: { width: 32, height: 32, borderRadius: 10, justifyContent: 'center', alignItems: 'center' },
  catTitle: { fontSize: 14, fontWeight: '700', color: COLORS.textSecondary, textTransform: 'uppercase', letterSpacing: 0.5 },
  card: { backgroundColor: COLORS.surface, borderRadius: 16, borderWidth: 1, borderColor: COLORS.borderLight, overflow: 'hidden' },
  faqRow: { flexDirection: 'row', alignItems: 'center', padding: 16, gap: 12 },
  question: { flex: 1, fontSize: 14, fontWeight: '600', color: COLORS.textPrimary, lineHeight: 20 },
  answerBox: { paddingHorizontal: 16, paddingBottom: 16, paddingTop: 0 },
  answer: { fontSize: 13, color: COLORS.textSecondary, lineHeight: 20 },
  divider: { height: 1, backgroundColor: COLORS.borderLight },
  contactCard: {
    backgroundColor: COLORS.surface, borderRadius: 16, padding: 24,
    alignItems: 'center', borderWidth: 1, borderColor: COLORS.borderLight, marginTop: 8,
  },
  contactTitle: { fontSize: 16, fontWeight: '700', color: COLORS.textPrimary, marginTop: 12, marginBottom: 6 },
  contactText: { fontSize: 13, color: COLORS.textMuted, textAlign: 'center', marginBottom: 16 },
  contactBtn: {
    flexDirection: 'row', alignItems: 'center', gap: 8,
    backgroundColor: COLORS.primary, paddingHorizontal: 20, paddingVertical: 12, borderRadius: 12,
  },
  contactBtnText: { fontSize: 14, fontWeight: '700', color: '#FFF' },
});
