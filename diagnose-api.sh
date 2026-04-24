#!/bin/bash

# WashBox API Connection Diagnostic Script
# This script helps diagnose network connectivity issues

echo "╔════════════════════════════════════════════════════════════╗"
echo "║     WashBox API Connection Diagnostic Tool                ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get local IP address
echo "🔍 Detecting network configuration..."
if command -v hostname &> /dev/null; then
    LOCAL_IP=$(hostname -I 2>/dev/null | awk '{print $1}')
    if [ -z "$LOCAL_IP" ]; then
        LOCAL_IP=$(ip addr show 2>/dev/null | grep "inet " | grep -v 127.0.0.1 | awk '{print $2}' | cut -d/ -f1 | head -n1)
    fi
else
    LOCAL_IP="Unable to detect"
fi

echo -e "${GREEN}✓${NC} Your local IP address: ${YELLOW}$LOCAL_IP${NC}"
echo ""

# Check if backend directory exists
echo "📁 Checking backend directory..."
if [ -d "/home/nell/Downloads/WashBox/backend" ]; then
    echo -e "${GREEN}✓${NC} Backend directory found"
else
    echo -e "${RED}✗${NC} Backend directory not found"
    exit 1
fi
echo ""

# Check if Laravel is installed
echo "🔧 Checking Laravel installation..."
cd /home/nell/Downloads/WashBox/backend
if [ -f "artisan" ]; then
    echo -e "${GREEN}✓${NC} Laravel artisan found"
else
    echo -e "${RED}✗${NC} Laravel artisan not found"
    exit 1
fi
echo ""

# Check if server is running on localhost
echo "🌐 Testing localhost:8000..."
LOCALHOST_TEST=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/api/v1/test 2>/dev/null)
if [ "$LOCALHOST_TEST" = "200" ]; then
    echo -e "${GREEN}✓${NC} Server is running on localhost:8000"
else
    echo -e "${RED}✗${NC} Server is NOT running on localhost:8000"
    echo -e "${YELLOW}ℹ${NC} Start server with: ${YELLOW}php artisan serve --host=0.0.0.0 --port=8000${NC}"
fi
echo ""

# Test network IP if available
if [ "$LOCAL_IP" != "Unable to detect" ]; then
    echo "🌐 Testing $LOCAL_IP:8000..."
    NETWORK_TEST=$(curl -s -o /dev/null -w "%{http_code}" http://$LOCAL_IP:8000/api/v1/test 2>/dev/null)
    if [ "$NETWORK_TEST" = "200" ]; then
        echo -e "${GREEN}✓${NC} Server is accessible from network IP"
    else
        echo -e "${RED}✗${NC} Server is NOT accessible from network IP"
        echo -e "${YELLOW}ℹ${NC} Make sure to start server with: ${YELLOW}--host=0.0.0.0${NC}"
    fi
    echo ""
fi

# Test branches endpoint
echo "📋 Testing branches API endpoint..."
BRANCHES_TEST=$(curl -s http://localhost:8000/api/v1/branches 2>/dev/null)
if echo "$BRANCHES_TEST" | grep -q "success"; then
    BRANCH_COUNT=$(echo "$BRANCHES_TEST" | grep -o '"count":[0-9]*' | grep -o '[0-9]*')
    echo -e "${GREEN}✓${NC} Branches endpoint working (Found $BRANCH_COUNT branches)"
else
    echo -e "${RED}✗${NC} Branches endpoint not responding correctly"
fi
echo ""

# Check mobile config
echo "📱 Checking mobile app configuration..."
MOBILE_CONFIG="/home/nell/Downloads/WashBox/mobile/constants/config.js"
if [ -f "$MOBILE_CONFIG" ]; then
    CURRENT_API=$(grep "API_BASE_URL" "$MOBILE_CONFIG" | grep -v "//" | head -n1)
    echo -e "${GREEN}✓${NC} Mobile config found"
    echo -e "   Current setting: ${YELLOW}$CURRENT_API${NC}"
else
    echo -e "${RED}✗${NC} Mobile config not found"
fi
echo ""

# Summary and recommendations
echo "╔════════════════════════════════════════════════════════════╗"
echo "║                    RECOMMENDATIONS                         ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

if [ "$LOCALHOST_TEST" != "200" ]; then
    echo -e "${YELLOW}1.${NC} Start the backend server:"
    echo -e "   ${GREEN}cd /home/nell/Downloads/WashBox/backend${NC}"
    echo -e "   ${GREEN}php artisan serve --host=0.0.0.0 --port=8000${NC}"
    echo ""
fi

if [ "$LOCAL_IP" != "Unable to detect" ]; then
    echo -e "${YELLOW}2.${NC} Update mobile app configuration:"
    echo -e "   Edit: ${GREEN}mobile/constants/config.js${NC}"
    echo -e "   Set: ${GREEN}export const API_BASE_URL = 'http://$LOCAL_IP:8000/api';${NC}"
    echo ""
fi

echo -e "${YELLOW}3.${NC} Ensure your phone and computer are on the ${GREEN}same WiFi network${NC}"
echo ""

echo -e "${YELLOW}4.${NC} After making changes, restart the mobile app"
echo ""

# Test command suggestions
echo "╔════════════════════════════════════════════════════════════╗"
echo "║                    QUICK TEST COMMANDS                     ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""
echo "Test from browser:"
if [ "$LOCAL_IP" != "Unable to detect" ]; then
    echo -e "  ${GREEN}http://$LOCAL_IP:8000/api/v1/test${NC}"
    echo -e "  ${GREEN}http://$LOCAL_IP:8000/api/v1/branches${NC}"
fi
echo ""

echo "Test from terminal:"
echo -e "  ${GREEN}curl http://localhost:8000/api/v1/test${NC}"
if [ "$LOCAL_IP" != "Unable to detect" ]; then
    echo -e "  ${GREEN}curl http://$LOCAL_IP:8000/api/v1/branches${NC}"
fi
echo ""

echo "✅ Diagnostic complete!"
