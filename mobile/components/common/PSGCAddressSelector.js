import React, { useState, useEffect } from 'react';
import {
  View, Text, TouchableOpacity, Modal, FlatList,
  TextInput, ActivityIndicator, StyleSheet,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

const PSGC = 'https://psgc.gitlab.io/api';
const NEGROS_ORIENTAL_CODE = '074600000';

// Postal codes for all Negros Oriental cities/municipalities
const POSTAL_CODES = {
  '074601000': '6211', // Amlan
  '074602000': '6210', // Ayungon
  '074603000': '6201', // Bacong
  '074604000': '6206', // City of Bais
  '074605000': '6222', // Basay
  '074606000': '6221', // City of Bayawan
  '074607000': '6209', // Bindoy
  '074608000': '6217', // City of Canlaon
  '074609000': '6217', // Dauin
  '074610000': '6200', // City of Dumaguete
  '074611000': '6214', // City of Guihulngan
  '074612000': '6212', // Jimalalud
  '074613000': '6213', // La Libertad
  '074614000': '6207', // Mabinay
  '074615000': '6208', // Manjuyod
  '074616000': '6219', // Pamplona
  '074617000': '6218', // San Jose
  '074618000': '6220', // Santa Catalina
  '074619000': '6222', // Siaton
  '074620000': '6202', // Sibulan
  '074621000': '6204', // City of Tanjay
  '074622000': '6211', // Tayasan
  '074623000': '6215', // Valencia
  '074624000': '6216', // Vallehermoso
  '074625000': '6223', // Zamboanguita
};

// Known streets in Dumaguete City
const DUMAGUETE_STREETS = [
  'Aldecoa Drive',
  'Burgos Street',
  'Cervantes Street',
  'Colon Street',
  'Colon Street Extension',
  'Dr. V. Locsin Street',
  'Dr. Vicente Locsin Street',
  'Flores Avenue',
  'Governor Perdices Street',
  'Hibbard Avenue',
  'Katada Street',
  'Legaspi Street',
  'Luzuriaga Street',
  'Magsaysay Street',
  'Natividad Street',
  'Noblefranca Street',
  'North Road',
  'Perdices Street',
  'Pinili Street',
  'Real Street',
  'Rizal Boulevard',
  'Rovira Road',
  'San Juan Street',
  'Santa Rosa Street',
  'Silliman Avenue',
  'South Road',
  'St. Francis Street',
  'Sta. Catalina Street',
  'Taclobo Road',
  'Tubod Road',
].map((name, i) => ({ code: `street_${i}`, name }));

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
function DropdownPicker({ label, value, placeholder, items, loading, disabled, onSelect, allowCustom = false }) {
  const [visible, setVisible] = useState(false);
  const [search, setSearch] = useState('');
  const [customValue, setCustomValue] = useState('');
  const [showCustom, setShowCustom] = useState(false);

  const filtered = items.filter(i =>
    i.name.toLowerCase().includes(search.toLowerCase())
  );

  const handleCustomSubmit = () => {
    if (customValue.trim()) {
      onSelect({ code: `custom_${Date.now()}`, name: customValue.trim() });
      setVisible(false);
      setSearch('');
      setCustomValue('');
      setShowCustom(false);
    }
  };

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
            <TouchableOpacity onPress={() => { setVisible(false); setSearch(''); setShowCustom(false); }}>
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

          {/* Custom entry option for streets */}
          {allowCustom && (
            <TouchableOpacity
              style={styles.customToggle}
              onPress={() => setShowCustom(!showCustom)}
            >
              <Ionicons name="create-outline" size={16} color={COLORS.primary} />
              <Text style={styles.customToggleText}>
                {showCustom ? 'Choose from list' : 'Type street manually'}
              </Text>
            </TouchableOpacity>
          )}

          {showCustom ? (
            <View style={styles.customInputRow}>
              <TextInput
                style={[styles.searchInput, styles.customInput]}
                placeholder="Type street name..."
                placeholderTextColor={COLORS.textMuted}
                value={customValue}
                onChangeText={setCustomValue}
                autoFocus
              />
              <TouchableOpacity
                style={styles.customSubmitBtn}
                onPress={handleCustomSubmit}
              >
                <Ionicons name="checkmark" size={20} color="#FFF" />
              </TouchableOpacity>
            </View>
          ) : (
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
              ListEmptyComponent={
                <View style={styles.emptyList}>
                  <Text style={styles.emptyText}>No results found</Text>
                </View>
              }
            />
          )}
        </View>
      </Modal>
    </View>
  );
}

// ── Main Component ───────────────────────────────────────────────
export default function PSGCAddressSelector({ onChange, initialValues }) {
  const [cities, setCities] = useState([]);
  const [barangays, setBarangays] = useState([]);
  const [loadingCities, setLoadingCities] = useState(false);
  const [loadingBarangays, setLoadingBarangays] = useState(false);

  const [selectedCity, setSelectedCity] = useState(null);
  const [selectedBarangay, setSelectedBarangay] = useState(null);
  const [selectedStreet, setSelectedStreet] = useState(null);
  const [houseNumber, setHouseNumber] = useState(initialValues?.house_number || '');

  // Streets — Dumaguete has list, others just allow custom entry
  const isDumaguete = selectedCity?.code === '074610000';
  const streetItems = isDumaguete ? DUMAGUETE_STREETS : [];

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
    setSelectedStreet(null);
    setBarangays([]);
    fetch(`${PSGC}/cities-municipalities/${selectedCity.code}/barangays.json`)
      .then(r => r.json())
      .then(data => setBarangays(data.sort((a, b) => a.name.localeCompare(b.name))))
      .catch(() => setBarangays([]))
      .finally(() => setLoadingBarangays(false));
  }, [selectedCity]);

  // Notify parent whenever any field changes
  useEffect(() => {
    const postalCode = selectedCity ? (POSTAL_CODES[selectedCity.code] || '') : '';
    const parts = [houseNumber, selectedStreet?.name, selectedBarangay?.name, selectedCity?.name]
      .filter(Boolean);

    onChange?.({
      house_number: houseNumber,
      street: selectedStreet?.name || '',
      barangay: selectedBarangay?.name || '',
      city: selectedCity?.name || '',
      province: 'Negros Oriental',
      postal_code: postalCode,
      full_address: parts.join(', '),
    });
  }, [houseNumber, selectedStreet, selectedBarangay, selectedCity]);

  const postalCode = selectedCity ? (POSTAL_CODES[selectedCity.code] || '') : '';

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

      {/* Street */}
      <DropdownPicker
        label="Street"
        value={selectedStreet?.name}
        placeholder={selectedCity ? 'Select or type street' : 'Select city first'}
        items={streetItems}
        loading={false}
        disabled={!selectedCity}
        allowCustom={true}
        onSelect={setSelectedStreet}
      />

      {/* House Number */}
      <View style={styles.fieldGroup}>
        <Text style={styles.fieldLabel}>House / Unit No.</Text>
        <TextInput
          style={styles.textInput}
          value={houseNumber}
          onChangeText={setHouseNumber}
          placeholder="e.g. 183, Unit 2B, Block 5 Lot 3"
          placeholderTextColor={COLORS.textMuted}
        />
      </View>

      {/* Postal Code — auto filled */}
      {postalCode ? (
        <View style={styles.postalRow}>
          <Ionicons name="mail-outline" size={14} color={COLORS.primary} />
          <Text style={styles.postalText}>Postal Code: <Text style={styles.postalValue}>{postalCode}</Text></Text>
        </View>
      ) : null}

      {/* Full Address Preview */}
      {(houseNumber || selectedStreet || selectedBarangay || selectedCity) && (
        <View style={styles.preview}>
          <Ionicons name="location" size={14} color={COLORS.success} />
          <Text style={styles.previewText} numberOfLines={3}>
            {[houseNumber, selectedStreet?.name, selectedBarangay?.name, selectedCity?.name, 'Negros Oriental']
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
  textInput: {
    backgroundColor: COLORS.card,
    borderRadius: 12, padding: 14,
    fontSize: 14, color: COLORS.textPrimary,
    borderWidth: 1, borderColor: COLORS.border,
  },
  postalRow: {
    flexDirection: 'row', alignItems: 'center',
    gap: 8, marginBottom: 12,
    backgroundColor: 'rgba(14,165,233,0.1)',
    padding: 10, borderRadius: 10,
  },
  postalText: { fontSize: 13, color: COLORS.textSecondary },
  postalValue: { fontWeight: '700', color: COLORS.primary },
  preview: {
    flexDirection: 'row', alignItems: 'flex-start',
    gap: 8, backgroundColor: 'rgba(16,185,129,0.1)',
    padding: 12, borderRadius: 10, marginTop: 4,
  },
  previewText: { flex: 1, fontSize: 12, color: COLORS.success, fontWeight: '600', lineHeight: 18 },

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
  customToggle: {
    flexDirection: 'row', alignItems: 'center',
    gap: 8, marginHorizontal: 16, marginBottom: 8,
    padding: 10, borderRadius: 10,
    backgroundColor: 'rgba(14,165,233,0.1)',
  },
  customToggleText: { fontSize: 13, fontWeight: '600', color: COLORS.primary },
  customInputRow: {
    flexDirection: 'row', alignItems: 'center',
    gap: 10, marginHorizontal: 16, marginBottom: 16,
  },
  customInput: {
    flex: 1, backgroundColor: COLORS.card,
    borderRadius: 12, paddingHorizontal: 14, paddingVertical: 12,
    borderWidth: 1, borderColor: COLORS.border,
  },
  customSubmitBtn: {
    backgroundColor: COLORS.primary,
    width: 44, height: 44, borderRadius: 12,
    justifyContent: 'center', alignItems: 'center',
  },
  listItem: {
    flexDirection: 'row', alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20, paddingVertical: 14,
  },
  listItemText: { fontSize: 15, color: COLORS.textPrimary },
  separator: { height: 1, backgroundColor: COLORS.border, marginHorizontal: 20 },
  emptyList: { padding: 40, alignItems: 'center' },
  emptyText: { color: COLORS.textMuted, fontSize: 14 },
});
