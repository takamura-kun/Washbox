#!/bin/bash

echo "========================================="
echo "WASHBOX MOBILE APP DIAGNOSTIC CHECK"
echo "========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check 1: Node and npm versions
echo "1. Checking Node.js and npm..."
node_version=$(node -v 2>&1)
npm_version=$(npm -v 2>&1)
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Node: $node_version"
    echo -e "${GREEN}✓${NC} npm: $npm_version"
else
    echo -e "${RED}✗${NC} Node.js or npm not found"
fi
echo ""

# Check 2: Dependencies installed
echo "2. Checking dependencies..."
if [ -d "node_modules" ]; then
    echo -e "${GREEN}✓${NC} node_modules directory exists"
    module_count=$(find node_modules -maxdepth 1 -type d | wc -l)
    echo "   Found $module_count modules"
else
    echo -e "${RED}✗${NC} node_modules not found - run 'npm install'"
fi
echo ""

# Check 3: Expo CLI
echo "3. Checking Expo..."
if command -v expo &> /dev/null; then
    expo_version=$(expo --version 2>&1)
    echo -e "${GREEN}✓${NC} Expo CLI: $expo_version"
else
    echo -e "${YELLOW}!${NC} Expo CLI not found globally (will use npx)"
fi
echo ""

# Check 4: Check for syntax errors in main files
echo "4. Checking JavaScript syntax..."
error_count=0

# Check main app files
for file in "app/(tabs)/index.js" "app/(tabs)/laundry.js" "app/(tabs)/pickup.js" "app/(tabs)/menu.js"; do
    if [ -f "$file" ]; then
        node -c "$file" 2>/dev/null
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✓${NC} $file"
        else
            echo -e "${RED}✗${NC} $file has syntax errors"
            ((error_count++))
        fi
    fi
done
echo ""

# Check 5: Configuration files
echo "5. Checking configuration files..."
for file in app.json package.json constants/config.js; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓${NC} $file exists"
    else
        echo -e "${RED}✗${NC} $file missing"
    fi
done
echo ""

# Check 6: API Configuration
echo "6. Checking API configuration..."
if [ -f "constants/config.js" ]; then
    api_url=$(grep "API_BASE_URL" constants/config.js | head -1)
    echo "   Current API: $api_url"
    
    # Extract IP from config
    ip_address=$(echo "$api_url" | grep -oP '\d+\.\d+\.\d+\.\d+' | head -1)
    if [ ! -z "$ip_address" ]; then
        echo "   Testing connection to $ip_address:8000..."
        if timeout 2 bash -c "cat < /dev/null > /dev/tcp/$ip_address/8000" 2>/dev/null; then
            echo -e "${GREEN}✓${NC} Backend server is reachable"
        else
            echo -e "${YELLOW}!${NC} Cannot reach backend server at $ip_address:8000"
            echo "   Make sure backend is running: cd backend && php artisan serve --host=0.0.0.0"
        fi
    fi
fi
echo ""

# Check 7: Assets
echo "7. Checking assets..."
asset_dirs=("assets/images" "assets/sounds" "assets/fonts")
for dir in "${asset_dirs[@]}"; do
    if [ -d "$dir" ]; then
        file_count=$(find "$dir" -type f | wc -l)
        echo -e "${GREEN}✓${NC} $dir ($file_count files)"
    else
        echo -e "${YELLOW}!${NC} $dir not found"
    fi
done
echo ""

# Check 8: Security vulnerabilities
echo "8. Checking for security issues..."
npm audit --audit-level=high 2>&1 | grep -E "(found|vulnerabilities)" | head -2
echo ""

# Check 9: Expo Doctor (if available)
echo "9. Running Expo diagnostics..."
if [ -f "node_modules/.bin/expo-doctor" ]; then
    npx expo-doctor 2>&1 | head -20
else
    echo -e "${YELLOW}!${NC} expo-doctor not available"
fi
echo ""

# Summary
echo "========================================="
echo "SUMMARY"
echo "========================================="
if [ $error_count -eq 0 ]; then
    echo -e "${GREEN}✓${NC} All checks passed!"
    echo ""
    echo "To start the app:"
    echo "  npm start          # Start Expo dev server"
    echo "  npm run android    # Run on Android"
    echo "  npm run ios        # Run on iOS"
else
    echo -e "${RED}✗${NC} Found $error_count error(s)"
    echo "Please fix the errors above before running the app"
fi
echo ""
