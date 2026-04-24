#!/bin/bash

echo "========================================="
echo "WashBox Mobile - Expo Go Fix Helper"
echo "========================================="
echo ""
echo "Your Expo server is running! 🎉"
echo ""
echo "Issue: Expo Go app needs to be updated"
echo ""
echo "========================================="
echo "CHOOSE YOUR SOLUTION:"
echo "========================================="
echo ""
echo "1. Update Expo Go app (EASIEST - Recommended)"
echo "   - Open Play Store/App Store"
echo "   - Search 'Expo Go'"
echo "   - Tap Update"
echo "   - Scan QR code again"
echo ""
echo "2. Run on Web Browser (QUICK TEST)"
echo "   - No phone needed"
echo "   - Test in browser"
echo ""
echo "3. Downgrade Expo SDK (COMPATIBILITY FIX)"
echo "   - Makes it work with older Expo Go"
echo "   - Takes a few minutes"
echo ""
echo "========================================="
echo ""
read -p "Enter your choice (1, 2, or 3): " choice
echo ""

case $choice in
    1)
        echo "✅ Great choice!"
        echo ""
        echo "Steps:"
        echo "1. On your phone, open Play Store (Android) or App Store (iOS)"
        echo "2. Search for 'Expo Go'"
        echo "3. Tap 'Update' button"
        echo "4. After update, scan the QR code again"
        echo ""
        echo "The QR code is still showing in your terminal!"
        echo ""
        ;;
    2)
        echo "✅ Starting web version..."
        echo ""
        npx expo start --web
        ;;
    3)
        echo "✅ Downgrading Expo SDK to 51..."
        echo ""
        echo "This will take a few minutes..."
        npm install expo@~51.0.0 --legacy-peer-deps
        echo ""
        echo "Fixing dependencies..."
        npx expo install --fix
        echo ""
        echo "Starting Expo with cache clear..."
        npx expo start --clear
        ;;
    *)
        echo "❌ Invalid choice. Please run the script again and choose 1, 2, or 3."
        ;;
esac
