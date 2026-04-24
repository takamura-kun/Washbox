/**
 * app/test-audio.js
 * Test audio files in Expo Go
 */

import React from 'react';
import { View, StyleSheet } from 'react-native';
import SoundPlayer from '../components/SoundPlayer';

export default function TestAudioPage() {
    return (
        <View style={styles.container}>
            <SoundPlayer />
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#0F172A',
    },
});