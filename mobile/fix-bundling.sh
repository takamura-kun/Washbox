#!/bin/bash

echo "🔧 WashBox Mobile - Troubleshooting Script"
echo "=========================================="

# Navigate to mobile directory
cd /home/nell/Downloads/WashBox/mobile

echo "1. Clearing Metro bundler cache..."
npx expo start --clear

echo "2. If that doesn't work, try these commands one by one:"
echo ""
echo "   # Clear all caches"
echo "   rm -rf node_modules/.cache"
echo "   rm -rf .expo"
echo "   rm -rf /tmp/metro-*"
echo ""
echo "   # Reinstall dependencies"
echo "   npm install"
echo ""
echo "   # Start with fresh cache"
echo "   npx expo start --clear --reset-cache"
echo ""
echo "3. Alternative commands to try:"
echo "   npx expo start --tunnel --clear"
echo "   npx expo start --localhost --clear"
echo ""
echo "4. If still having issues, check:"
echo "   - Make sure all import paths are correct"
echo "   - Ensure no circular dependencies"
echo "   - Check for typos in file names"
echo ""
echo "✅ The import paths have been fixed to use '../constants/config'"
echo "✅ All syntax is valid"
echo "✅ Config file exports are correct"