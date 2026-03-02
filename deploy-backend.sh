#!/bin/bash

echo "🚀 WashBox Deployment Script"
echo "=============================="
echo ""

# Check if Railway CLI is installed
if ! command -v railway &> /dev/null; then
    echo "📦 Installing Railway CLI..."
    npm install -g @railway/cli
fi

# Navigate to backend
cd backend || exit

echo "🔐 Logging into Railway..."
railway login

echo "🎯 Initializing Railway project..."
railway init

echo "🗄️  Adding MySQL database..."
railway add --database mysql

echo "⚙️  Setting environment variables..."
railway variables set APP_ENV=production
railway variables set APP_DEBUG=false
railway variables set SESSION_DRIVER=database
railway variables set QUEUE_CONNECTION=database

echo "🚀 Deploying backend..."
railway up

echo "🌐 Getting your deployment URL..."
RAILWAY_URL=$(railway domain)

echo ""
echo "✅ Backend deployed successfully!"
echo "📍 URL: $RAILWAY_URL"
echo ""
echo "🔑 Admin Panel: $RAILWAY_URL/admin/login"
echo "👥 Staff Panel: $RAILWAY_URL/staff/login"
echo "📱 API Endpoint: $RAILWAY_URL/api/v1/"
echo ""
echo "⚠️  IMPORTANT: Update mobile/constants/config.js with:"
echo "   export const API_BASE_URL = '$RAILWAY_URL/api';"
echo ""
echo "📝 Next steps:"
echo "   1. Run migrations in Railway dashboard"
echo "   2. Update mobile app config"
echo "   3. Build mobile app with: eas build"
