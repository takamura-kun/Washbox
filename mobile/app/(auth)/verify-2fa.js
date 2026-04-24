import { useState, useRef, useEffect } from 'react';
import {
  View, Text, TextInput, TouchableOpacity, StyleSheet,
  ActivityIndicator, KeyboardAvoidingView, Platform, Alert,
} from 'react-native';
import { router, useLocalSearchParams } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { StatusBar } from 'expo-status-bar';
import { LinearGradient } from 'expo-linear-gradient';
import { useAuth } from '../../context/AuthContext';
import { API_BASE_URL } from '../../constants/config';
import { registerForPushNotifications } from '../../utils/notification';

export default function Verify2FAScreen() {
  const { email, password } = useLocalSearchParams();
  const { login } = useAuth();
  const [code, setCode] = useState(['', '', '', '', '', '']);
  const [loading, setLoading] = useState(false);
  const [resending, setResending] = useState(false);
  const [countdown, setCountdown] = useState(60);
  const inputs = useRef([]);

  useEffect(() => {
    if (countdown <= 0) return;
    const timer = setTimeout(() => setCountdown(c => c - 1), 1000);
    return () => clearTimeout(timer);
  }, [countdown]);

  const handleChange = (text, index) => {
    const digit = text.replace(/[^0-9]/g, '').slice(-1);
    const newCode = [...code];
    newCode[index] = digit;
    setCode(newCode);
    if (digit && index < 5) inputs.current[index + 1]?.focus();
  };

  const handleKeyPress = (e, index) => {
    if (e.nativeEvent.key === 'Backspace' && !code[index] && index > 0) {
      inputs.current[index - 1]?.focus();
    }
  };

  const handleVerify = async () => {
    const otp = code.join('');
    if (otp.length < 6) {
      Alert.alert('Incomplete Code', 'Please enter the 6-digit code sent to your email.');
      return;
    }
    setLoading(true);
    try {
      const response = await fetch(`${API_BASE_URL}/v1/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ email, password, two_factor_code: otp }),
      });
      const data = await response.json();
      if (response.ok && data.success) {
        await login(data.data.token, data.data.customer);
        registerForPushNotifications(data.data.token).catch(() => {});
      } else {
        Alert.alert('Verification Failed', data.message || 'Invalid or expired code. Please try again.');
        setCode(['', '', '', '', '', '']);
        inputs.current[0]?.focus();
      }
    } catch {
      Alert.alert('Connection Error', 'Unable to connect. Please check your internet connection.');
    } finally {
      setLoading(false);
    }
  };

  const handleResend = async () => {
    if (countdown > 0) return;
    setResending(true);
    try {
      await fetch(`${API_BASE_URL}/v1/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ email, password }),
      });
      setCountdown(60);
      setCode(['', '', '', '', '', '']);
      inputs.current[0]?.focus();
      Alert.alert('Code Sent', 'A new verification code has been sent to your email.');
    } catch {
      Alert.alert('Error', 'Failed to resend code. Please try again.');
    } finally {
      setResending(false);
    }
  };

  return (
    <>
      <StatusBar style="light" />
      <LinearGradient
        colors={['#0A0E27', '#1A1F3A', '#0A0E27']}
        style={styles.container}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
      >
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
          style={styles.inner}
        >
          <TouchableOpacity style={styles.backButton} onPress={() => router.back()}>
            <Ionicons name="arrow-back" size={24} color="#94A3B8" />
          </TouchableOpacity>

          <View style={styles.iconWrap}>
            <LinearGradient colors={['#0EA5E9', '#3B82F6']} style={styles.iconCircle}>
              <Ionicons name="shield-checkmark" size={36} color="#fff" />
            </LinearGradient>
          </View>

          <Text style={styles.title}>Verify Your Identity</Text>
          <Text style={styles.subtitle}>
            Enter the 6-digit code sent to{'\n'}
            <Text style={styles.email}>{email}</Text>
          </Text>

          <View style={styles.otpRow}>
            {code.map((digit, i) => (
              <TextInput
                key={i}
                ref={r => (inputs.current[i] = r)}
                style={[styles.otpBox, digit ? styles.otpBoxFilled : null]}
                value={digit}
                onChangeText={t => handleChange(t, i)}
                onKeyPress={e => handleKeyPress(e, i)}
                keyboardType="number-pad"
                maxLength={1}
                selectTextOnFocus
                editable={!loading}
              />
            ))}
          </View>

          <TouchableOpacity
            style={[styles.verifyButton, loading && styles.verifyButtonDisabled]}
            onPress={handleVerify}
            disabled={loading}
            activeOpacity={0.9}
          >
            <LinearGradient
              colors={loading ? ['#64748B', '#475569'] : ['#0EA5E9', '#3B82F6']}
              style={styles.verifyGradient}
              start={{ x: 0, y: 0 }}
              end={{ x: 1, y: 0 }}
            >
              {loading ? (
                <ActivityIndicator color="#fff" size="small" />
              ) : (
                <>
                  <Text style={styles.verifyText}>Verify & Sign In</Text>
                  <Ionicons name="arrow-forward" size={20} color="#fff" />
                </>
              )}
            </LinearGradient>
          </TouchableOpacity>

          <View style={styles.resendRow}>
            <Text style={styles.resendLabel}>Didn&apos;t receive the code? </Text>
            <TouchableOpacity onPress={handleResend} disabled={countdown > 0 || resending}>
              {resending ? (
                <ActivityIndicator size="small" color="#0EA5E9" />
              ) : (
                <Text style={[styles.resendLink, countdown > 0 && styles.resendDisabled]}>
                  {countdown > 0 ? `Resend in ${countdown}s` : 'Resend'}
                </Text>
              )}
            </TouchableOpacity>
          </View>
        </KeyboardAvoidingView>
      </LinearGradient>
    </>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  inner: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 24,
  },
  backButton: {
    position: 'absolute',
    top: Platform.OS === 'ios' ? 60 : 40,
    left: 24,
    padding: 8,
  },
  iconWrap: { marginBottom: 24 },
  iconCircle: {
    width: 80,
    height: 80,
    borderRadius: 40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  title: {
    fontSize: 26,
    fontWeight: '700',
    color: '#FFFFFF',
    marginBottom: 12,
    letterSpacing: -0.5,
  },
  subtitle: {
    fontSize: 15,
    color: '#94A3B8',
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 36,
  },
  email: { color: '#0EA5E9', fontWeight: '600' },
  otpRow: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 36,
  },
  otpBox: {
    width: 48,
    height: 56,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: 'rgba(255,255,255,0.15)',
    backgroundColor: 'rgba(255,255,255,0.05)',
    color: '#FFFFFF',
    fontSize: 22,
    fontWeight: '700',
    textAlign: 'center',
  },
  otpBoxFilled: {
    borderColor: '#0EA5E9',
    backgroundColor: 'rgba(14,165,233,0.1)',
  },
  verifyButton: {
    width: '100%',
    borderRadius: 14,
    overflow: 'hidden',
    marginBottom: 20,
    elevation: 8,
    shadowColor: 'rgba(14,165,233,0.4)',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.3,
    shadowRadius: 16,
  },
  verifyButtonDisabled: { opacity: 0.6 },
  verifyGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 18,
    gap: 10,
  },
  verifyText: { color: '#FFFFFF', fontSize: 16, fontWeight: '700', letterSpacing: 0.5 },
  resendRow: { flexDirection: 'row', alignItems: 'center' },
  resendLabel: { color: '#94A3B8', fontSize: 14 },
  resendLink: { color: '#0EA5E9', fontSize: 14, fontWeight: '700' },
  resendDisabled: { color: '#475569' },
});
