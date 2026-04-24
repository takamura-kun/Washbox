/**
 * Add this to any existing screen to test sounds
 * Copy and paste this code into your current screen
 */

// Add these imports at the top of your file
import { Alert, TouchableOpacity, Text } from 'react-native';
import { testNotificationSound } from '../utils/testNotificationSounds';

// Add this button anywhere in your JSX
const TestSoundButton = () => {
  const quickTest = async () => {
    try {
      await testNotificationSound('laundry_ready');
      Alert.alert(
        'Test Sent!', 
        'Did you hear a sound and feel vibration?\n\n✅ Yes = Working!\n❌ No = Check volume/permissions'
      );
    } catch (error) {
      Alert.alert('Test Failed', error.message);
    }
  };

  return (
    <TouchableOpacity 
      onPress={quickTest}
      style={{
        backgroundColor: '#10B981',
        padding: 12,
        borderRadius: 8,
        margin: 10,
        alignItems: 'center'
      }}
    >
      <Text style={{ color: 'white', fontWeight: 'bold' }}>
        🔊 Test Sound
      </Text>
    </TouchableOpacity>
  );
};

// Use it in your component like:
// <TestSoundButton />