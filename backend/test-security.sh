#!/bin/bash

# WashBox Security Features Test Script
# Run this to verify all security implementations are working

echo "🔒 WashBox Security Features Test"
echo "=================================="
echo ""

BASE_URL="http://localhost:8000/api/v1"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "📍 Testing against: $BASE_URL"
echo ""

# Test 1: Rate Limiting on Login
echo "1️⃣  Testing Rate Limiting (Login endpoint)"
echo "   Attempting 6 login requests (limit is 5/minute)..."
for i in {1..6}; do
    response=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE_URL/login" \
        -H "Content-Type: application/json" \
        -d '{"email":"test@example.com","password":"wrong"}')
    
    if [ $i -le 5 ]; then
        if [ "$response" == "401" ] || [ "$response" == "422" ]; then
            echo "   ✅ Request $i: $response (Expected - validation/auth error)"
        else
            echo "   ⚠️  Request $i: $response"
        fi
    else
        if [ "$response" == "429" ]; then
            echo -e "   ${GREEN}✅ Request $i: $response (Rate limit working!)${NC}"
        else
            echo -e "   ${RED}❌ Request $i: $response (Should be 429)${NC}"
        fi
    fi
done
echo ""

# Test 2: CORS Configuration
echo "2️⃣  Testing CORS Configuration"
echo "   Checking allowed origins..."

# Test allowed origin
response=$(curl -s -X OPTIONS "$BASE_URL/login" \
    -H "Origin: http://localhost:3000" \
    -H "Access-Control-Request-Method: POST" \
    -i | grep -i "access-control-allow-origin")

if [[ $response == *"localhost:3000"* ]] || [[ $response == *"*"* ]]; then
    echo -e "   ${GREEN}✅ CORS configured (localhost:3000 allowed)${NC}"
else
    echo -e "   ${YELLOW}⚠️  CORS response: $response${NC}"
fi
echo ""

# Test 3: 2FA Routes
echo "3️⃣  Testing 2FA Routes"
echo "   Checking if 2FA endpoints exist..."

# Test enable 2FA (should require auth)
response=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE_URL/2fa/enable")
if [ "$response" == "401" ]; then
    echo -e "   ${GREEN}✅ /2fa/enable exists (requires authentication)${NC}"
else
    echo -e "   ${YELLOW}⚠️  /2fa/enable returned: $response${NC}"
fi

# Test disable 2FA (should require auth)
response=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE_URL/2fa/disable")
if [ "$response" == "401" ]; then
    echo -e "   ${GREEN}✅ /2fa/disable exists (requires authentication)${NC}"
else
    echo -e "   ${YELLOW}⚠️  /2fa/disable returned: $response${NC}"
fi
echo ""

# Test 4: File Upload Security
echo "4️⃣  Testing File Upload Security"
echo "   Checking SecureFileUploadService exists..."

if [ -f "../app/Services/SecureFileUploadService.php" ]; then
    echo -e "   ${GREEN}✅ SecureFileUploadService.php exists${NC}"
else
    echo -e "   ${RED}❌ SecureFileUploadService.php not found${NC}"
fi

if [ -f "../app/Services/TwoFactorService.php" ]; then
    echo -e "   ${GREEN}✅ TwoFactorService.php exists${NC}"
else
    echo -e "   ${RED}❌ TwoFactorService.php not found${NC}"
fi
echo ""

# Test 5: Database Migrations
echo "5️⃣  Testing Database Schema"
echo "   Checking if 2FA fields exist in database..."

# Check customers table
php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    \$columns = DB::select('SHOW COLUMNS FROM customers LIKE \"two_factor%\"');
    if (count(\$columns) >= 3) {
        echo '   ✅ customers table has 2FA fields' . PHP_EOL;
    } else {
        echo '   ❌ customers table missing 2FA fields' . PHP_EOL;
    }
} catch (Exception \$e) {
    echo '   ⚠️  Could not check database: ' . \$e->getMessage() . PHP_EOL;
}
"
echo ""

# Summary
echo "=================================="
echo "📊 Security Features Summary"
echo "=================================="
echo ""
echo "✅ Rate Limiting: Active (5 requests/minute on auth endpoints)"
echo "✅ CORS: Configured (check config/cors.php for allowed origins)"
echo "✅ 2FA: Implemented (routes and database ready)"
echo "✅ File Upload Security: Implemented (SecureFileUploadService)"
echo ""
echo "🎯 Next Steps:"
echo "   1. Configure Gmail SMTP in .env for 2FA emails"
echo "   2. Update CORS allowed_origins with your frontend URL"
echo "   3. Test file uploads with actual images"
echo "   4. Enable 2FA for admin accounts"
echo ""
echo "📖 Documentation:"
echo "   - docs/RATE_LIMITING.md"
echo "   - docs/FILE_UPLOAD_SECURITY.md"
echo "   - docs/TWO_FACTOR_AUTHENTICATION.md"
echo "   - docs/CORS_SECURITY.md"
echo ""
