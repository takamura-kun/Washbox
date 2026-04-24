#!/bin/bash

echo "🔧 WashBox Network Troubleshooting"
echo "=================================="
echo ""

# Check if backend is running
echo "1. Checking if Laravel backend is running..."
if curl -s http://localhost:8000/api/v1/test > /dev/null; then
    echo "✅ Backend is running on localhost:8000"
else
    echo "❌ Backend is not running. Starting it now..."
    cd /home/nell/Downloads/WashBox/backend
    php artisan serve --host=0.0.0.0 --port=8000 &
    echo "🚀 Backend started on all interfaces (0.0.0.0:8000)"
fi

echo ""

# Get current IP
IP=$(hostname -I | awk '{print $1}')
echo "2. Your current IP address: $IP"

# Test API endpoints
echo ""
echo "3. Testing API endpoints..."

echo "   Testing localhost..."
if curl -s http://localhost:8000/api/v1/test > /dev/null; then
    echo "   ✅ http://localhost:8000/api - Working"
else
    echo "   ❌ http://localhost:8000/api - Failed"
fi

echo "   Testing IP address..."
if curl -s http://$IP:8000/api/v1/test > /dev/null; then
    echo "   ✅ http://$IP:8000/api - Working"
else
    echo "   ❌ http://$IP:8000/api - Failed"
fi

echo "   Testing Android emulator address..."
if curl -s http://10.0.2.2:8000/api/v1/test > /dev/null 2>&1; then
    echo "   ✅ http://10.0.2.2:8000/api - Working"
else
    echo "   ❌ http://10.0.2.2:8000/api - Failed (normal if not using Android emulator)"
fi

echo ""
echo "4. Mobile App Configuration:"
echo "   Current config: http://$IP:8000/api"
echo ""
echo "5. Recommended solutions:"
echo ""
echo "   For Physical Device/Expo Go:"
echo "   - Use: http://$IP:8000/api"
echo "   - Make sure your phone and computer are on the same WiFi network"
echo ""
echo "   For Android Emulator:"
echo "   - Use: http://10.0.2.2:8000/api"
echo ""
echo "   For iOS Simulator:"
echo "   - Use: http://localhost:8000/api"
echo ""
echo "6. To test connectivity from mobile app:"
echo "   - Navigate to /network-diagnostic in the app"
echo "   - This will test all possible URLs automatically"
echo ""
echo "7. If still having issues:"
echo "   - Check firewall settings"
echo "   - Restart Expo development server: npx expo start --clear"
echo "   - Try using Expo tunnel: npx expo start --tunnel"

echo ""
echo "✅ Troubleshooting complete!"