# WashBox Mobile App - Quick Start Guide

## ✅ System Status
All checks passed! The mobile app is ready to run.

## 🚀 Quick Start

### 1. Start Development Server
```bash
cd /home/nell/Downloads/WashBox/mobile
npm start
```

### 2. Run on Device/Emulator

**Android:**
```bash
npm run android
```

**iOS:**
```bash
npm run ios
```

**Web (for testing):**
```bash
npm run web
```

## 📱 Testing on Physical Device

### Using Expo Go App

1. **Install Expo Go:**
   - Android: [Play Store](https://play.google.com/store/apps/details?id=host.exp.exponent)
   - iOS: [App Store](https://apps.apple.com/app/expo-go/id982107779)

2. **Connect to Same Network:**
   - Ensure your phone and computer are on the same WiFi network

3. **Scan QR Code:**
   - Run `npm start` in the mobile directory
   - Scan the QR code with Expo Go (Android) or Camera app (iOS)

### Using USB Connection

**Android:**
```bash
# Enable USB debugging on your Android device
# Connect via USB
adb devices  # Verify device is connected
npm run android
```

## 🔧 Configuration

### API Endpoint
Current backend API: `http://192.168.1.9:8000/api`

To change the API endpoint:
1. Edit `mobile/constants/config.js`
2. Update `API_BASE_URL` to your backend server IP
3. Restart the development server

### Backend Server
Make sure the backend is running:
```bash
cd /home/nell/Downloads/WashBox/backend
php artisan serve --host=0.0.0.0 --port=8000
```

## 📋 Features Checklist

### ✅ Implemented Features
- [x] User Authentication (Login/Register)
- [x] Home Dashboard with stats
- [x] Laundry tracking
- [x] Pickup scheduling
- [x] Promotions display
- [x] Notifications
- [x] Profile management
- [x] Real-time location tracking
- [x] Payment methods
- [x] Rating system

### 🎨 UI/UX Features
- [x] Dark theme design
- [x] Smooth animations
- [x] Pull-to-refresh
- [x] Loading states
- [x] Empty states
- [x] Error handling

## 🐛 Troubleshooting

### Issue: "Cannot connect to backend"
**Solution:**
1. Check if backend is running: `curl http://192.168.1.9:8000/api/v1/branches`
2. Verify IP address in `constants/config.js`
3. Ensure both devices are on same network
4. Check firewall settings

### Issue: "Metro bundler not starting"
**Solution:**
```bash
# Clear cache and restart
npm start -- --clear
# or
npx expo start -c
```

### Issue: "Module not found"
**Solution:**
```bash
# Reinstall dependencies
rm -rf node_modules
npm install
```

### Issue: "Expo Go app shows error"
**Solution:**
1. Update Expo Go to latest version
2. Clear Expo Go cache (in app settings)
3. Restart development server with `npm start -- --clear`

### Issue: "Android build fails"
**Solution:**
```bash
# Clean Android build
cd android
./gradlew clean
cd ..
npm run android
```

## 📊 Performance Tips

1. **Enable Hermes Engine** (already configured in app.json)
2. **Use Production Build for Testing:**
   ```bash
   npx expo build:android
   npx expo build:ios
   ```

3. **Monitor Performance:**
   - Open React DevTools: Shake device → "Show Performance Monitor"
   - Check network requests in Expo DevTools

## 🔐 Security Notes

- Never commit API keys or secrets
- Use environment variables for sensitive data
- Enable SSL/HTTPS in production
- Implement proper authentication token refresh

## 📱 Building for Production

### Android APK
```bash
# Using EAS Build (recommended)
npx eas build --platform android --profile production

# Or local build
cd android
./gradlew assembleRelease
```

### iOS IPA
```bash
# Using EAS Build (requires Apple Developer account)
npx eas build --platform ios --profile production
```

## 🧪 Testing

### Run Diagnostics
```bash
./check_mobile_app.sh
```

### Test API Connection
```bash
# Test from mobile directory
curl http://192.168.1.9:8000/api/v1/branches
```

### Check Logs
```bash
# Expo logs
npx expo start

# Android logs
adb logcat | grep ReactNativeJS

# iOS logs
xcrun simctl spawn booted log stream --predicate 'processImagePath endswith "Expo"'
```

## 📞 Support

### Common Commands
```bash
# Start development server
npm start

# Clear cache
npm start -- --clear

# Update dependencies
npm update

# Check for issues
npm audit

# Fix vulnerabilities
npm audit fix
```

### Useful Links
- [Expo Documentation](https://docs.expo.dev/)
- [React Native Documentation](https://reactnative.dev/)
- [WashBox Backend API](http://192.168.1.9:8000/api/documentation)

## 🎯 Next Steps

1. **Test all features** on physical device
2. **Configure push notifications** (Firebase/APNs)
3. **Set up analytics** (optional)
4. **Prepare for production** deployment
5. **Submit to app stores** (Google Play/App Store)

---

**Last Updated:** March 19, 2025
**Status:** ✅ All systems operational
