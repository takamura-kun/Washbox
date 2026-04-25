import React, { useState, useEffect } from 'react';
import {
  View, Text, TouchableOpacity, Modal, FlatList,
  TextInput, ActivityIndicator, StyleSheet,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

const PSGC = 'https://psgc.gitlab.io/api';
const NEGROS_ORIENTAL_CODE = '074600000';

const COLORS = {
  background: '#0A0E27',
  card: '#1C2340',
  cardLight: '#252D4C',
  primary: '#0EA5E9',
  success: '#10B981',
  textPrimary: '#FFFFFF',
  textSecondary: '#94A3B8',
  textMuted: '#64748B',
  border: '#1E293B',
};

// ── Dropdown Picker ──────────────────────────────────────────────
function DropdownPicker({ label, value, placeholder, items, loading, disabled, onSelect }) {
  const [visible, setVisible] = useState(false);
  const [search, setSearch] = useState('');

  const filtered = items.filter(i =>
    i.name.toLowerCase().includes(search.toLowerCase())
  );

  return (
    <View style={styles.fieldGroup}>
      <Text style={styles.fieldLabel}>{label}</Text>
      <TouchableOpacity
        style={[styles.picker, disabled && styles.pickerDisabled]}
        onPress={() => !disabled && setVisible(true)}
        activeOpacity={0.7}
      >
        {loading ? (
          <ActivityIndicator size="small" color={COLORS.primary} />
        ) : (
          <Text style={[styles.pickerText, !value && styles.pickerPlaceholder]} numberOfLines={1}>
            {value || placeholder}
          </Text>
        )}
        <Ionicons name="chevron-down" size={16} color={COLORS.textMuted} />
      </TouchableOpacity>

      <Modal visible={visible} animationType="slide" presentationStyle="pageSheet">
        <View style={styles.modalContainer}>
          <View style={styles.modalHeader}>
            <Text style={styles.modalTitle}>Select {label}</Text>
            <TouchableOpacity onPress={() => { setVisible(false); setSearch(''); }}>
              <Ionicons name="close" size={24} color={COLORS.textPrimary} />
            </TouchableOpacity>
          </View>

          <View style={styles.searchBox}>
            <Ionicons name="search" size={16} color={COLORS.textMuted} />
            <TextInput
              style={styles.searchInput}
              placeholder={`Search ${label}...`}
              placeholderTextColor={COLORS.textMuted}
              value={search}
              onChangeText={setSearch}
              autoFocus
            />
          </View>

          <FlatList
            data={filtered}
            keyExtractor={item => item.code}
            renderItem={({ item }) => (
              <TouchableOpacity
                style={styles.listItem}
                onPress={() => { onSelect(item); setVisible(false); setSearch(''); }}
              >
                <Text style={styles.listItemText}>{item.name}</Text>
                {value === item.name && (
                  <Ionicons name="checkmark-circle" size={18} color={COLORS.success} />
                )}
              </TouchableOpacity>
            )}
            ItemSeparatorComponent={() => <View style={styles.separator} />}
          />
        </View>
      </Modal>
    </View>
  );
}

// ── Main Component ───────────────────────────────────────────────
export default function PSGCAddressSelector({ onChange }) {
  const [cities, setCities] = useState([]);
  const [barangays, setBarangays] = useState([]);
  const [loadingCities, setLoadingCities] = useState(false);
  const [loadingBarangays, setLoadingBarangays] = useState(false);

  const [selectedCity, setSelectedCity] = useState(null);
  const [selectedBarangay, setSelectedBarangay] = useState(null);
  const [houseNumber, setHouseNumber] = useState('');
  const [street, setStreet] = useState('');

  // Fetch cities on mount
  useEffect(() => {
    setLoadingCities(true);
    fetch(`${PSGC}/provinces/${NEGROS_ORIENTAL_CODE}/cities-municipalities.json`)
      .then(r => r.json())
      .then(data => setCities(data.sort((a, b) => a.name.localeCompare(b.name))))
      .catch(() => setCities([]))
      .finally(() => setLoadingCities(false));
  }, []);

  // Fetch barangays when city changes
  useEffect(() => {
    if (!selectedCity) return;
    setLoadingBarangays(true);
    setSelectedBarangay(null);
    setBarangays([]);
    fetch(`${PSGC}/cities-municipalities/${selectedCity.code}/barangays.json`)
      .then(r => r.json())
      .then(data => setBarangays(data.sort((a, b) => a.name.localeCompare(b.name))))
      .catch(() => setBarangays([]))
      .finally(() => setLoadingBarangays(false));
  }, [selectedCity]);

  // Notify parent whenever any field changes
  useEffect(() => {
    const parts = [houseNumber, street, selectedBarangay?.name, selectedCity?.name]
      .filter(Boolean);
    onChange?.({
      house_number: houseNumber,
      street,
      barangay: selectedBarangay?.name || '',
      city: selectedCity?.name || '',
      province: 'Negros Oriental',
      full_address: parts.join(', '),
    });
  }, [houseNumber, street, selectedBarangay, selectedCity]);

  return (
    <View>
      {/* City / Municipality */}
      <DropdownPicker
        label="City / Municipality"
        value={selectedCity?.name}
        placeholder="Select city or municipality"
        items={cities}
        loading={loadingCities}
        onSelect={setSelectedCity}
      />

      {/* Barangay */}
      <DropdownPicker
        label="Barangay"
        value={selectedBarangay?.name}
        placeholder={selectedCity ? 'Select barangay' : 'Select city first'}
        items={barangays}
        loading={loadingBarangays}
        disabled={!selectedCity}
        onSelect={setSelectedBarangay}
      />

      {/* House Number + Street */}
      <View style={styles.row}>
        <View style={{ flex: 0.35 }}>
          <Text style={styles.fieldLabel}>House No.</Text>
          <TextInput
            style={styles.textInput}
            value={houseNumber}
            onChangeText={setHouseNumber}
            placeholder="e.g. 183"
            placeholderTextColor={COLORS.textMuted}
          />
        </View>
        <View style={{ flex: 0.65 }}>
          <Text style={styles.fieldLabel}>Street</Text>
          <TextInput
            style={styles.textInput}
            value={street}
            onChangeText={setStreet}
            placeholder="e.g. Dr. V. Locsin St."
            placeholderTextColor={COLORS.textMuted}
          />
        </View>
      </View>

      {/* Preview */}
      {(houseNumber || street || selectedBarangay || selectedCity) && (
        <View style={styles.preview}>
          <Ionicons name="location" size={14} color={COLORS.success} />
          <Text style={styles.previewText} numberOfLines={2}>
            {[houseNumber, street, selectedBarangay?.name, selectedCity?.name, 'Negros Oriental']
              .filter(Boolean).join(', ')}
          </Text>
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  fieldGroup: { marginBottom: 12 },
  fieldLabel: {
    fontSize: 12, fontWeight: '700',
    color: COLORS.textSecondary,
    marginBottom: 6, letterSpacing: 0.3,
  },
  picker: {
    flexDirection: 'row', alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: COLORS.card,
    borderRadius: 12, padding: 14,
    borderWidth: 1, borderColor: COLORS.border,
  },
  pickerDisabled: { opacity: 0.5 },
  pickerText: { fontSize: 14, color: COLORS.textPrimary, flex: 1 },
  pickerPlaceholder: { color: COLORS.textMuted },
  row: { flexDirection: 'row', gap: 10, marginBottom: 12 },
  textInput: {
    backgroundColor: COLORS.card,
    borderRadius: 12, padding: 14,
    fontSize: 14, color: COLORS.textPrimary,
    borderWidth: 1, borderColor: COLORS.border,
  },
  preview: {
    flexDirection: 'row', alignItems: 'flex-start',
    gap: 8, backgroundColor: 'rgba(16,185,129,0.1)',
    padding: 12, borderRadius: 10, marginTop: 4,
  },
  previewText: { flex: 1, fontSize: 12, color: COLORS.success, fontWeight: '600' },

  // Modal
  modalContainer: { flex: 1, backgroundColor: COLORS.background },
  modalHeader: {
    flexDirection: 'row', justifyContent: 'space-between',
    alignItems: 'center', padding: 20, paddingTop: 56,
    borderBottomWidth: 1, borderBottomColor: COLORS.border,
  },
  modalTitle: { fontSize: 18, fontWeight: '700', color: COLORS.textPrimary },
  searchBox: {
    flexDirection: 'row', alignItems: 'center',
    gap: 10, margin: 16,
    backgroundColor: COLORS.card,
    borderRadius: 12, paddingHorizontal: 14, paddingVertical: 12,
    borderWidth: 1, borderColor: COLORS.border,
  },
  searchInput: { flex: 1, fontSize: 14, color: COLORS.textPrimary },
  listItem: {
    flexDirection: 'row', alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20, paddingVertical: 14,
  },
  listItemText: { fontSize: 15, color: COLORS.textPrimary },
  separator: { height: 1, backgroundColor: COLORS.border, marginHorizontal: 20 },
});
