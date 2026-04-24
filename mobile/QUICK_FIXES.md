# WashBox Mobile - Quick Fixes

## ⚠️ Common Issues and Solutions

### Issue 1: "ENOSPC: System limit for number of file watchers reached"

**Error Message:**
```
Error: ENOSPC: System limit for number of file watchers reached
```

**Solution (Choose one):**

#### Option A: Permanent Fix (Recommended)
```bash
echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf
sudo sysctl -p
```

#### Option B: Temporary Fix (Until Reboot)
```bash
sudo sysctl fs.inotify.max_user_watches=524288
```

#### Option C: Manual Configuration
1. Open the config file:
   ```bash
   sudo nano /etc/sysctl.conf
   ```
2. Add this line at the end:
   ```
   fs.inotify.max_user_watches=524288
   ```
3. Save and exit (Ctrl+X, Y, Enter)
4. Apply changes:
   ```bash
   sudo sysctl -p
   ```

**After applying the fix:**
```bash
npx expo start
```

---

### Issue 2: "Failed to resolve plugin for module 'expo-router'"

**Solution:**
```bash
npm install expo-router@latest --legacy-peer-deps
```

---

### Issue 3: "The /android project does not contain any URI schemes"

**This is just a warning, not an error.** The app will still work.

**To fix (optional):**
Add to `app.json`:
```json
{
  "expo": {
    "scheme": "washbox"
  }
}
```

---

### Issue 4: Metro Bundler Not Starting

**Solution:**
```bash
# Clear cache
npx expo start --clear

# Or
npm start -- --clear
```

---

### Issue 5: Module Not Found Errors

**Solution:**
```bash
# Clean install
rm -rf node_modules
npm install
npx expo start --clear
```

---

### Issue 6: Cannot Connect to Backend

**Check:**
1. Backend is running:
   ```bash
   cd /home/nell/Downloads/WashBox/backend
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. IP address is correct in `constants/config.js`

3. Both devices on same network

4. Test connection:
   ```bash
   curl http://192.168.1.9:8000/api/v1/branches
   ```

---

### Issue 7: Port Already in Use

**Solution:**
```bash
# Kill process on port 8081
npx kill-port 8081

# Or find and kill manually
lsof -ti:8081 | xargs kill -9
```

---

### Issue 8: Android Build Fails

**Solution:**
```bash
cd android
./gradlew clean
cd ..
npm run android
```

---

### Issue 9: Expo Go App Shows Error

**Solution:**
1. Update Expo Go to latest version
2. Clear Expo Go cache (in app settings)
3. Restart with cache clear:
   ```bash
   npx expo start --clear
   ```

---

### Issue 10: "Unable to resolve module"

**Solution:**
```bash
# Clear all caches
rm -rf node_modules
npm cache clean --force
npm install
npx expo start --clear
```

---

## 🚀 Quick Start Commands

### Start Development Server
```bash
cd /home/nell/Downloads/WashBox/mobile
npx expo start
```

### Start with Cache Clear
```bash
npx expo start --clear
```

### Run on Android
```bash
npm run android
```

### Run on iOS
```bash
npm run ios
```

### Check System Status
```bash
./check_mobile_app.sh
```

---

## 📱 Testing on Physical Device

### Using Expo Go
1. Install Expo Go from Play Store/App Store
2. Connect phone and computer to same WiFi
3. Run `npx expo start`
4. Scan QR code with Expo Go (Android) or Camera (iOS)

### Using USB (Android)
```bash
# Enable USB debugging on phone
adb devices
npm run android
```

---

## 🔧 Useful Commands

```bash
# Check current file watcher limit
cat /proc/sys/fs/inotify/max_user_watches

# Check Expo version
npx expo --version

# Update Expo
npm install expo@latest

# Check for outdated packages
npm outdated

# Update all packages
npm update

# Security audit
npm audit

# Fix vulnerabilities
npm audit fix
```

---

## 📞 Need Help?

1. Check `MOBILE_QUICK_START.md` for detailed guide
2. Check `MOBILE_STATUS_REPORT.md` for system status
3. Run `./check_mobile_app.sh` for diagnostics
4. Run `./fix_file_watchers.sh` for file watcher fix

---

**Last Updated:** March 19, 2025
