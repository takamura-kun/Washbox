/**
 * Simple Sound Player for Expo Go
 * Tests if your sound files work (without notifications)
 */

import React, { useState } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, Alert } from 'react-native';
import { Audio } from 'expo-av';

// Pre-import all sound files (required for React Native)
const soundFiles = {
    'order_update.mp3': require('../assets/sounds/order_update.mp3'),
    'pickup_alert.mp3': require('../assets/sounds/pickup_alert.mp3'),
    'promo_chime.mp3': require('../assets/sounds/promo_chime.mp3'),
};

const SoundPlayer = () => {
    const [isPlaying, setIsPlaying] = useState(false);

    const playSound = async (soundFile) => {
        try {
            setIsPlaying(true);
            
            // Get the pre-imported sound file
            const soundAsset = soundFiles[soundFile];
            if (!soundAsset) {
                throw new Error(`Sound file ${soundFile} not found`);
            }
            
            // Load and play sound
            const { sound } = await Audio.Sound.createAsync(soundAsset);
            
            await sound.playAsync();
            
            // Clean up when done
            sound.setOnPlaybackStatusUpdate((status) => {
                if (status.didJustFinish) {
                    sound.unloadAsync();
                    setIsPlaying(false);
                }
            });
            
        } catch (error) {
            console.error('Error playing sound:', error);
            Alert.alert('Error', `Could not play ${soundFile}`);
            setIsPlaying(false);
        }
    };

    const sounds = [
        { file: 'order_update.mp3', label: '🧺 Order Update' },
        { file: 'pickup_alert.mp3', label: '🚚 Pickup Alert' },
        { file: 'promo_chime.mp3', label: '🎉 Promo Chime' },
    ];

    return (
        <View style={styles.container}>
            <Text style={styles.title}>🔊 Test Sound Files</Text>
            <Text style={styles.subtitle}>Works in Expo Go</Text>
            
            {sounds.map((sound, index) => (
                <TouchableOpacity
                    key={index}
                    style={[styles.button, isPlaying && styles.buttonDisabled]}
                    onPress={() => playSound(sound.file)}
                    disabled={isPlaying}
                >
                    <Text style={styles.buttonText}>
                        {isPlaying ? '🔄 Playing...' : `▶️ ${sound.label}`}
                    </Text>
                </TouchableOpacity>
            ))}
            
            <View style={styles.note}>
                <Text style={styles.noteText}>
                    ℹ️ This only tests sound files.{'\n'}
                    For notification sounds, you need a development build.
                </Text>
            </View>
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#0F172A',
        padding: 20,
        justifyContent: 'center',
    },
    title: {
        fontSize: 24,
        fontWeight: 'bold',
        color: '#FFFFFF',
        textAlign: 'center',
        marginBottom: 10,
    },
    subtitle: {
        fontSize: 16,
        color: '#94A3B8',
        textAlign: 'center',
        marginBottom: 30,
    },
    button: {
        backgroundColor: '#0EA5E9',
        borderRadius: 12,
        padding: 16,
        marginBottom: 12,
        alignItems: 'center',
    },
    buttonText: {
        fontSize: 18,
        fontWeight: '600',
        color: '#FFFFFF',
    },
    buttonDisabled: {
        opacity: 0.5,
    },
    note: {
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        borderRadius: 8,
        padding: 16,
        marginTop: 20,
    },
    noteText: {
        fontSize: 14,
        color: '#60A5FA',
        textAlign: 'center',
        lineHeight: 20,
    },
});

export default SoundPlayer;