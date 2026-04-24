#!/bin/bash

set -e

echo "🚀 WashBox Mobile App - Startup Script"
echo "======================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if in mobile directory
if [ ! -f "package.json" ]; then
  echo -e "${RED}❌ Error: package.json not found. Please run from mobile directory.${NC}"
  exit 1
fi

echo -e "${YELLOW}Step 1: Checking prerequisites...${NC}"
if ! command -v node &> /dev/null; then
  echo -e "${RED}❌ Node.js not found. Please install Node.js 18+${NC}"
  exit 1
fi
echo -e "${GREEN}✓ Node.js found: $(node --version)${NC}"

if ! command -v npm &> /dev/null; then
  echo -e "${RED}❌ npm not found.${NC}"
  exit 1
fi
echo -e "${GREEN}✓ npm found: $(npm --version)${NC}"

echo ""
echo -e "${YELLOW}Step 2: Cleaning caches...${NC}"
rm -rf .expo node_modules/.cache .next dist build 2>/dev/null || true
npm cache clean --force 2>&1 | grep -v "npm warn" || true
echo -e "${GREEN}✓ Caches cleaned${NC}"

echo ""
echo -e "${YELLOW}Step 3: Installing dependencies...${NC}"
npm install
echo -e "${GREEN}✓ Dependencies installed${NC}"

echo ""
echo -e "${YELLOW}Step 4: Verifying critical files...${NC}"
files=("app/_layout.js" "index.js" "babel.config.js" "metro.config.js" "app.json")
for file in "${files[@]}"; do
  if [ -f "$file" ]; then
    echo -e "${GREEN}✓ $file exists${NC}"
  else
    echo -e "${RED}❌ $file missing${NC}"
    exit 1
  fi
done

echo ""
echo -e "${YELLOW}Step 5: Checking backend...${NC}"
if curl -s http://localhost:8000/api/v1/branches > /dev/null 2>&1; then
  echo -e "${GREEN}✓ Backend is running on http://localhost:8000${NC}"
else
  echo -e "${YELLOW}⚠ Backend not responding. Make sure to run:${NC}"
  echo -e "${YELLOW}  php artisan serve --host=0.0.0.0 --port=8000${NC}"
fi

echo ""
echo -e "${GREEN}======================================"
echo "✅ Setup complete!"
echo "=====================================${NC}"
echo ""
echo "Choose how to start:"
echo ""
echo "1️⃣  For Expo Go (Android/iOS):"
echo "   npm start"
echo ""
echo "2️⃣  For Web Chrome:"
echo "   npm run web"
echo ""
echo "3️⃣  For Expo Go with cleanup:"
echo "   npm run start:clean"
echo ""
echo "4️⃣  For Web with cleanup:"
echo "   npm run web:clean"
echo ""
