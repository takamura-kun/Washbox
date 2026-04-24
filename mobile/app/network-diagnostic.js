import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  Alert,
  ActivityIndicator,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { API_BASE_URL } from '../constants/config';

const COLORS = {
  background: '#0A0E27',
  cardDark: '#1C2340',
  primary: '#0EA5E9',
  success: '#10B981',
  danger: '#EF4444',
  warning: '#F59E0B',
  textPrimary: '#FFFFFF',
  textSecondary: '#94A3B8',
  textMuted: '#64748B',
  border: '#1E293B',
};

// Different API URLs to test
const TEST_URLS = [
  { name: 'Current Config', url: API_BASE_URL },
  { name: 'Localhost', url: 'http://localhost:8000/api' },
  { name: 'Android Emulator', url: 'http://10.0.2.2:8000/api' },
  { name: 'Local IP', url: 'http://192.168.1.3:8000/api' },
  { name: '127.0.0.1', url: 'http://127.0.0.1:8000/api' },
];

export default function NetworkDiagnostic() {
  const [results, setResults] = useState({});
  const [testing, setTesting] = useState(false);

  const testConnection = async (name, url) => {
    try {
      console.log(`Testing ${name}: ${url}/v1/test`);
      
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout
      
      const response = await fetch(`${url}/v1/test`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        signal: controller.signal,
      });
      
      clearTimeout(timeoutId);
      
      if (response.ok) {
        const data = await response.json();
        return {
          status: 'success',
          message: `✅ Connected successfully`,
          details: `Server time: ${data.server_time}`,
          statusCode: response.status,
        };
      } else {
        return {
          status: 'error',
          message: `❌ HTTP ${response.status}`,
          details: response.statusText,
          statusCode: response.status,
        };
      }
    } catch (error) {
      console.log(`Error testing ${name}:`, error.message);
      
      if (error.name === 'AbortError') {
        return {
          status: 'timeout',
          message: '⏱️ Connection timeout',
          details: 'Request took longer than 5 seconds',
        };
      }
      
      return {
        status: 'error',
        message: '❌ Connection failed',
        details: error.message,
      };
    }
  };

  const runAllTests = async () => {
    setTesting(true);
    setResults({});
    
    for (const testUrl of TEST_URLS) {
      const result = await testConnection(testUrl.name, testUrl.url);
      setResults(prev => ({
        ...prev,
        [testUrl.name]: result,
      }));
    }
    
    setTesting(false);
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'success': return COLORS.success;
      case 'error': return COLORS.danger;
      case 'timeout': return COLORS.warning;
      default: return COLORS.textMuted;
    }
  };

  const getStatusIcon = (status) => {
    switch (status) {
      case 'success': return 'checkmark-circle';
      case 'error': return 'close-circle';
      case 'timeout': return 'time';
      default: return 'help-circle';
    }
  };

  useEffect(() => {
    runAllTests();
  }, []);

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>Network Diagnostic</Text>
        <TouchableOpacity
          style={styles.refreshButton}
          onPress={runAllTests}
          disabled={testing}
        >
          {testing ? (
            <ActivityIndicator size="small" color={COLORS.primary} />
          ) : (
            <Ionicons name="refresh" size={20} color={COLORS.primary} />
          )}
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.scrollView}>
        <View style={styles.infoCard}>
          <Text style={styles.infoTitle}>Current Configuration</Text>
          <Text style={styles.infoText}>API URL: {API_BASE_URL}</Text>
          <Text style={styles.infoText}>
            Testing connectivity to backend server...
          </Text>
        </View>

        {TEST_URLS.map((testUrl) => {
          const result = results[testUrl.name];
          
          return (
            <View key={testUrl.name} style={styles.testCard}>
              <View style={styles.testHeader}>
                <Text style={styles.testName}>{testUrl.name}</Text>
                {result && (
                  <Ionicons
                    name={getStatusIcon(result.status)}
                    size={20}
                    color={getStatusColor(result.status)}
                  />
                )}
              </View>
              
              <Text style={styles.testUrl}>{testUrl.url}</Text>
              
              {result ? (
                <View style={styles.resultContainer}>
                  <Text style={[styles.resultMessage, { color: getStatusColor(result.status) }]}>
                    {result.message}
                  </Text>
                  {result.details && (
                    <Text style={styles.resultDetails}>{result.details}</Text>
                  )}
                  {result.statusCode && (
                    <Text style={styles.statusCode}>Status: {result.statusCode}</Text>
                  )}
                </View>
              ) : testing ? (
                <View style={styles.loadingContainer}>
                  <ActivityIndicator size="small" color={COLORS.primary} />
                  <Text style={styles.loadingText}>Testing...</Text>
                </View>
              ) : null}
            </View>
          );
        })}

        <View style={styles.troubleshootCard}>
          <Text style={styles.troubleshootTitle}>Troubleshooting Tips</Text>
          <Text style={styles.troubleshootText}>
            1. Make sure Laravel server is running: php artisan serve --host=0.0.0.0 --port=8000
          </Text>
          <Text style={styles.troubleshootText}>
            2. Check your firewall settings
          </Text>
          <Text style={styles.troubleshootText}>
            3. Ensure your device and computer are on the same network
          </Text>
          <Text style={styles.troubleshootText}>
            4. Try different URLs above to find the working one
          </Text>
          <Text style={styles.troubleshootText}>
            5. For Android Emulator, use 10.0.2.2 instead of localhost
          </Text>
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
    padding: 20,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 20,
  },
  title: {
    fontSize: 24,
    fontWeight: '700',
    color: COLORS.textPrimary,
  },
  refreshButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.cardDark,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scrollView: {
    flex: 1,
  },
  infoCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  infoTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginBottom: 8,
  },
  infoText: {
    fontSize: 13,
    color: COLORS.textSecondary,
    marginBottom: 4,
  },
  testCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  testHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  testName: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textPrimary,
  },
  testUrl: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginBottom: 12,
    fontFamily: 'monospace',
  },
  resultContainer: {
    marginTop: 8,
  },
  resultMessage: {
    fontSize: 14,
    fontWeight: '600',
    marginBottom: 4,
  },
  resultDetails: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginBottom: 2,
  },
  statusCode: {
    fontSize: 11,
    color: COLORS.textMuted,
  },
  loadingContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  loadingText: {
    fontSize: 12,
    color: COLORS.textSecondary,
  },
  troubleshootCard: {
    backgroundColor: COLORS.cardDark,
    borderRadius: 12,
    padding: 16,
    marginTop: 8,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  troubleshootTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textPrimary,
    marginBottom: 12,
  },
  troubleshootText: {
    fontSize: 13,
    color: COLORS.textSecondary,
    marginBottom: 8,
    lineHeight: 18,
  },
});