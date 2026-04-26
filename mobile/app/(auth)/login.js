import { useAuth } from '../../context/AuthContext';
import { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Alert,
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  Dimensions,
  Image,
  Animated,
} from 'react-native';
import { Link, router } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Ionicons } from '@expo/vector-icons';
import { StatusBar } from 'expo-status-bar';
import { API_BASE_URL } from '../../constants/config';
import { LinearGradient } from 'expo-linear-gradient';
import { registerForPushNotifications } from '../../utils/notification';
import TermsModal from '../../components/TermsModal';

const { width, height } = Dimensions.get('window');

export default function LoginScreen() {
  const { login } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});
  const [showTermsModal, setShowTermsModal] = useState(false);
  const [termsAccepted, setTermsAccepted] = useState(true);
  const [lockoutSeconds, setLockoutSeconds] = useState(0);
  const [attemptsLeft, setAttemptsLeft] = useState(null);
  const lockoutTimer = useRef(null);

  // Animation values
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const slideAnim = useRef(new Animated.Value(50)).current;
  const logoScale = useRef(new Animated.Value(0.5)).current;
  const logoPulse = useRef(new Animated.Value(1)).current;

  // Countdown timer for lockout
  useEffect(() => {
    if (lockoutSeconds <= 0) return;
    lockoutTimer.current = setInterval(() => {
      setLockoutSeconds(prev => {
        if (prev <= 1) {
          clearInterval(lockoutTimer.current);
          return 0;
        }
        return prev - 1;
      });
    }, 1000);
    return () => clearInterval(lockoutTimer.current);
  }, [lockoutSeconds]);

  useEffect(() => {
    // Check if terms were previously accepted
    const checkTermsAcceptance = async () => {
      const accepted = await AsyncStorage.getItem('@washbox:terms_accepted');
      if (!accepted) {
        setShowTermsModal(true);
        setTermsAccepted(false);
      }
    };
    checkTermsAcceptance();

    Animated.parallel([
      Animated.timing(fadeAnim, {
        toValue: 1,
        duration: 800,
        useNativeDriver: true,
      }),
      Animated.spring(slideAnim, {
        toValue: 0,
        tension: 50,
        friction: 7,
        useNativeDriver: true,
      }),
      Animated.spring(logoScale, {
        toValue: 1,
        tension: 40,
        friction: 5,
        useNativeDriver: true,
      }),
    ]).start();

    // Subtle pulse animation
    Animated.loop(
      Animated.sequence([
        Animated.timing(logoPulse, {
          toValue: 1.05,
          duration: 2000,
          useNativeDriver: true,
        }),
        Animated.timing(logoPulse, {
          toValue: 1,
          duration: 2000,
          useNativeDriver: true,
        }),
      ])
    ).start();
  }, [fadeAnim, slideAnim, logoScale, logoPulse]);

  // Validate inputs
  const validate = () => {
    const newErrors = {};
    const emailPattern = /\S+@\S+\.\S+/;
    const minPasswordLength = 8;

    if (!email) {
      newErrors.email = 'Email is required';
    } else if (!emailPattern.test(email)) {
      newErrors.email = 'Email is invalid';
    }

    if (!password) {
      newErrors.password = 'Password is required';
    } else if (password.length < minPasswordLength) {
      newErrors.password = `Password must be at least ${minPasswordLength} characters`;
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  // Handle login
  const handleLogin = async () => {
    if (loading || lockoutSeconds > 0 || !validate()) return;
    setLoading(true);

    try {
      const response = await fetch(`${API_BASE_URL}/v1/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ email: email.trim(), password }),
      });

      const data = await response.json();

      // Locked out
      if (response.status === 429 && data.locked_out) {
        setLockoutSeconds(data.retry_after);
        setAttemptsLeft(0);
        return;
      }

      if (data.requires_2fa) {
        router.push({ pathname: '/(auth)/verify-2fa', params: { email: email.trim(), password } });
        return;
      }

      if (response.ok && data.success) {
        setAttemptsLeft(null);
        RateLimiter: null; // reset UI
        await login(data.data.token, data.data.customer);
        registerForPushNotifications(data.data.token).catch(() => {});
      } else {
        // Show attempts left warning
        if (data.attempts_left !== undefined) {
          setAttemptsLeft(data.attempts_left);
        }
      }
    } catch (error) {
      Alert.alert('Connection Error', 'Unable to connect to server. Please check your internet connection and try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <StatusBar style="light" />
      
      {/* Terms Modal - Show first */}
      <TermsModal
        visible={showTermsModal}
        onClose={() => router.back()}
        onAccept={async () => {
          await AsyncStorage.setItem('@washbox:terms_accepted', 'true');
          setTermsAccepted(true);
          setShowTermsModal(false);
        }}
        showAcceptButton={true}
      />

      {/* Login Form */}
      <LinearGradient
        colors={['#0A0E27', '#1A1F3A', '#0A0E27']}
        style={styles.container}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
      >
        {/* Floating Orbs Background */}
        <View style={styles.backgroundOrbs}>
          <LinearGradient
            colors={['rgba(14, 165, 233, 0.15)', 'rgba(59, 130, 246, 0.05)']}
            style={[styles.orb, styles.orb1]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
          />
          <LinearGradient
            colors={['rgba(139, 92, 246, 0.15)', 'rgba(236, 72, 153, 0.05)']}
            style={[styles.orb, styles.orb2]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
          />
        </View>

        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
          style={styles.keyboardView}
        >
          <ScrollView
            contentContainerStyle={styles.scrollContent}
            keyboardShouldPersistTaps="handled"
            showsVerticalScrollIndicator={false}
          >
            <Animated.View 
              style={[
                styles.content,
                {
                  opacity: fadeAnim,
                  transform: [{ translateY: slideAnim }],
                },
              ]}
            >
              {/* Logo Section - Circular Logo */}
              <Animated.View 
                style={[
                  styles.logoSection,
                  {
                    transform: [
                      { scale: Animated.multiply(logoScale, logoPulse) }
                    ],
                  },
                ]}
              >
                <View style={styles.logoContainer}>
                  <Image
                    source={require('../../assets/images/logo.png')}
                    style={styles.logo}
                    resizeMode="contain"
                  />
                </View>
              </Animated.View>

              {/* Welcome Header */}
              <View style={styles.welcomeSection}>
                <Text style={styles.welcomeTitle}>Welcome Back</Text>
                <Text style={styles.welcomeSubtitle}>
                  Sign in to continue your laundry journey
                </Text>
              </View>

              {/* Login Form */}
              <View style={styles.formCard}>
                {/* Email Input */}
                <View style={styles.inputGroup}>
                  <Text style={styles.inputLabel}>Email Address</Text>
                  <View style={[styles.inputContainer, errors.email && styles.inputError]}>
                    <Ionicons 
                      name="mail" 
                      size={20} 
                      color={errors.email ? "#EF4444" : "#0EA5E9"} 
                      style={styles.inputIcon}
                    />
                    <TextInput
                      style={styles.input}
                      placeholder="your.email@example.com"
                      placeholderTextColor="#64748B"
                      value={email}
                      onChangeText={(text) => {
                        setEmail(text);
                        if (errors.email) setErrors({ ...errors, email: null });
                      }}
                      keyboardType="email-address"
                      autoCapitalize="none"
                      autoCorrect={false}
                      editable={!loading}
                    />
                  </View>
                  {errors.email && (
                    <View style={styles.errorContainer}>
                      <Ionicons name="alert-circle" size={14} color="#EF4444" />
                      <Text style={styles.errorText}>{errors.email}</Text>
                    </View>
                  )}
                </View>

                {/* Password Input */}
                <View style={styles.inputGroup}>
                  <View style={styles.passwordLabelRow}>
                    <Text style={styles.inputLabel}>Password</Text>
                    <TouchableOpacity 
                      activeOpacity={0.7}
                      onPress={() => router.push('/(auth)/forgot-password')}
                    >
                      <Text style={styles.forgotPassword}>Forgot Password?</Text>
                    </TouchableOpacity>
                  </View>
                  <View style={[styles.inputContainer, errors.password && styles.inputError]}>
                    <Ionicons 
                      name="lock-closed" 
                      size={20} 
                      color={errors.password ? "#EF4444" : "#0EA5E9"} 
                      style={styles.inputIcon}
                    />
                    <TextInput
                      style={[styles.input, styles.passwordInputText]}
                      placeholder="Enter your password"
                      placeholderTextColor="#64748B"
                      value={password}
                      onChangeText={(text) => {
                        setPassword(text);
                        if (errors.password) setErrors({ ...errors, password: null });
                      }}
                      secureTextEntry={!showPassword}
                      editable={!loading}
                    />
                    <TouchableOpacity
                      style={styles.eyeButton}
                      onPress={() => setShowPassword(!showPassword)}
                      activeOpacity={0.7}
                    >
                      <Ionicons
                        name={showPassword ? 'eye' : 'eye-off'}
                        size={20}
                        color="#64748B"
                      />
                    </TouchableOpacity>
                  </View>
                  {errors.password && (
                    <View style={styles.errorContainer}>
                      <Ionicons name="alert-circle" size={14} color="#EF4444" />
                      <Text style={styles.errorText}>{errors.password}</Text>
                    </View>
                  )}
                </View>

                {/* Attempts warning */}
                {attemptsLeft !== null && attemptsLeft > 0 && lockoutSeconds === 0 && (
                  <View style={styles.warningBox}>
                    <Ionicons name="warning" size={16} color="#F59E0B" />
                    <Text style={styles.warningText}>
                      {attemptsLeft === 1
                        ? 'Last attempt before lockout!'
                        : `${attemptsLeft} attempt(s) remaining before lockout.`}
                    </Text>
                  </View>
                )}

                {/* Lockout countdown */}
                {lockoutSeconds > 0 && (
                  <View style={styles.lockoutBox}>
                    <Ionicons name="lock-closed" size={16} color="#EF4444" />
                    <Text style={styles.lockoutText}>
                      Too many failed attempts. Try again in{' '}
                      <Text style={styles.lockoutTimer}>{lockoutSeconds}s</Text>
                    </Text>
                  </View>
                )}

                {/* Login Button */}
                <TouchableOpacity
                  style={[styles.loginButton, (loading || lockoutSeconds > 0) && styles.loginButtonDisabled]}
                  onPress={handleLogin}
                  disabled={loading || lockoutSeconds > 0}
                  activeOpacity={0.9}
                >
                  <LinearGradient
                    colors={loading || lockoutSeconds > 0 ? ['#64748B', '#475569'] : ['#0EA5E9', '#3B82F6']}
                    style={styles.loginButtonGradient}
                    start={{ x: 0, y: 0 }}
                    end={{ x: 1, y: 0 }}
                  >
                    {loading ? (
                      <ActivityIndicator color="#FFFFFF" size="small" />
                    ) : lockoutSeconds > 0 ? (
                      <>
                        <Ionicons name="lock-closed" size={18} color="#FFFFFF" />
                        <Text style={styles.loginButtonText}>Wait {lockoutSeconds}s</Text>
                      </>
                    ) : (
                      <>
                        <Text style={styles.loginButtonText}>Sign In</Text>
                        <Ionicons name="arrow-forward" size={20} color="#FFFFFF" />
                      </>
                    )}
                  </LinearGradient>
                </TouchableOpacity>
              </View>

              {/* Register Section */}
              <View style={styles.registerSection}>
                <Text style={styles.registerText}>Don&apos;t have an account? </Text>
                <Link href="/(auth)/register" asChild>
                  <TouchableOpacity disabled={loading} activeOpacity={0.7}>
                    <Text style={styles.registerLink}>Create Account</Text>
                  </TouchableOpacity>
                </Link>
              </View>
            </Animated.View>
          </ScrollView>
        </KeyboardAvoidingView>
      </LinearGradient>
    </>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  backgroundOrbs: {
    position: 'absolute',
    width: '100%',
    height: '100%',
  },
  orb: {
    position: 'absolute',
    borderRadius: 999,
  },
  orb1: {
    width: 300,
    height: 300,
    top: -100,
    right: -100,
  },
  orb2: {
    width: 250,
    height: 250,
    bottom: -50,
    left: -80,
  },
  keyboardView: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
    paddingHorizontal: 24,
    paddingTop: Platform.OS === 'ios' ? 80 : 60,
    paddingBottom: 40,
  },
  content: {
    flex: 1,
  },

  // Logo Section
  logoSection: {
    alignItems: 'center',
    marginBottom: 48,
  },
  logoContainer: {
    width: 140,
    height: 140,
    borderRadius: 70,
    elevation: 15,
    shadowColor: 'rgba(14, 165, 233, 0.5)',
    shadowOffset: { width: 0, height: 12 },
    shadowOpacity: 0.3,
    shadowRadius: 24,
  },
  logo: {
    width: 140,
    height: 140,
    borderRadius: 70,
  },

  // Welcome Section
  welcomeSection: {
    marginBottom: 32,
    alignItems: 'center',
  },
  welcomeTitle: {
    fontSize: 28,
    fontWeight: '700',
    color: '#FFFFFF',
    marginBottom: 8,
    letterSpacing: -0.5,
  },
  welcomeSubtitle: {
    fontSize: 15,
    color: '#94A3B8',
    lineHeight: 22,
    textAlign: 'center',
  },

  // Form Card
  formCard: {
    backgroundColor: 'rgba(28, 35, 64, 0.6)',
    borderRadius: 24,
    padding: 24,
    marginBottom: 24,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.1)',
  },

  // Input Styles
  inputGroup: {
    marginBottom: 20,
  },
  inputLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#E2E8F0',
    marginBottom: 10,
    letterSpacing: 0.3,
  },
  passwordLabelRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  forgotPassword: {
    fontSize: 13,
    fontWeight: '600',
    color: '#0EA5E9',
  },
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255, 255, 255, 0.05)',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.1)',
    paddingHorizontal: 16,
    height: 56,
  },
  inputError: {
    borderColor: '#EF4444',
    backgroundColor: 'rgba(239, 68, 68, 0.1)',
  },
  inputIcon: {
    marginRight: 12,
  },
  input: {
    flex: 1,
    fontSize: 15,
    color: '#FFFFFF',
    fontWeight: '500',
  },
  passwordInputText: {
    paddingRight: 40,
  },
  eyeButton: {
    position: 'absolute',
    right: 16,
    padding: 8,
  },
  errorContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 8,
    paddingLeft: 4,
  },
  errorText: {
    color: '#EF4444',
    fontSize: 13,
    marginLeft: 6,
    fontWeight: '500',
  },

  warningBox: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(245, 158, 11, 0.15)',
    borderRadius: 10,
    padding: 10,
    marginBottom: 12,
    gap: 8,
    borderWidth: 1,
    borderColor: 'rgba(245, 158, 11, 0.3)',
  },
  warningText: {
    color: '#F59E0B',
    fontSize: 13,
    fontWeight: '600',
    flex: 1,
  },
  lockoutBox: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(239, 68, 68, 0.15)',
    borderRadius: 10,
    padding: 10,
    marginBottom: 12,
    gap: 8,
    borderWidth: 1,
    borderColor: 'rgba(239, 68, 68, 0.3)',
  },
  lockoutText: {
    color: '#EF4444',
    fontSize: 13,
    fontWeight: '500',
    flex: 1,
  },
  lockoutTimer: {
    fontWeight: '800',
    fontSize: 14,
  },

  // Login Button
  loginButton: {
    borderRadius: 14,
    marginTop: 8,
    overflow: 'hidden',
    elevation: 8,
    shadowColor: 'rgba(14, 165, 233, 0.4)',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.3,
    shadowRadius: 16,
  },
  loginButtonDisabled: {
    opacity: 0.6,
  },
  loginButtonGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 18,
    gap: 10,
  },
  loginButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
    letterSpacing: 0.5,
  },

  // Register Section
  registerSection: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
  },
  registerText: {
    color: '#94A3B8',
    fontSize: 15,
    fontWeight: '500',
  },
  registerLink: {
    color: '#0EA5E9',
    fontSize: 15,
    fontWeight: '700',
  },
});
