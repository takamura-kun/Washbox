/**
 * Sound Verification Checklist
 * Quick way to verify if sounds are working
 */

import React, { useState } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, Alert } from 'react-native';
import { testNotificationSound, checkNotificationPermissions } from '../utils/testNotificationSounds';

const SoundVerification = () => {
    const [results, setResults] = useState({});

    const runVerification = async () => {
        const checks = {};
        
        // 1. Check permissions
        const permissions = await checkNotificationPermissions();
        checks.permissions = permissions.granted;
        
        // 2. Send test notification
        try {
            await testNotificationSound('laundry_ready');
            checks.notificationSent = true;
        } catch (error) {
            checks.notificationSent = false;
            checks.error = error.message;
        }
        
        setResults(checks);
        
        // Show results
        const message = `
Permissions: ${checks.permissions ? '✅' : '❌'}
Notification Sent: ${checks.notificationSent ? '✅' : '❌'}
${checks.error ? `Error: ${checks.error}` : ''}

${checks.permissions && checks.notificationSent ? 
    '🎉 If you heard a sound and felt vibration, it\'s working!' : 
    '❌ Something is not working properly.'
}`;
        
        Alert.alert('Verification Results', message);
    };

    return (
        <View style={styles.container}>
            <Text style={styles.title}>🔍 Quick Sound Check</Text>
            
            <TouchableOpacity style={styles.button} onPress={runVerification}>
                <Text style={styles.buttonText}>▶️ Test Now</Text>
            </TouchableOpacity>
            
            <View style={styles.checklist}>
                <Text style={styles.checklistTitle}>What to Look For:</Text>
                <Text style={styles.checklistItem}>🔊 Hear the notification sound</Text>
                <Text style={styles.checklistItem}>📳 Feel device vibration</Text>
                <Text style={styles.checklistItem}>📱 See notification appear</Text>
                <Text style={styles.checklistItem}>✅ No error messages</Text>
            </View>
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        padding: 20,
        backgroundColor: '#0F172A',
    },
    title: {
        fontSize: 20,
        fontWeight: 'bold',
        color: '#FFFFFF',
        textAlign: 'center',
        marginBottom: 20,
    },
    button: {
        backgroundColor: '#10B981',
        borderRadius: 12,
        padding: 16,
        alignItems: 'center',
        marginBottom: 20,
    },
    buttonText: {
        fontSize: 18,
        fontWeight: '600',
        color: '#FFFFFF',
    },
    checklist: {
        backgroundColor: 'rgba(255, 255, 255, 0.05)',
        borderRadius: 12,
        padding: 16,
    },
    checklistTitle: {
        fontSize: 16,
        fontWeight: '600',
        color: '#FFFFFF',
        marginBottom: 12,
    },
    checklistItem: {
        fontSize: 14,
        color: '#CBD5E1',
        marginBottom: 8,
        paddingLeft: 10,
    },
});

export default SoundVerification;