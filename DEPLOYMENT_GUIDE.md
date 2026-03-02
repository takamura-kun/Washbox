# WashBox Deployment Guide

## Backend Deployment (Railway.app)

### Prerequisites
- GitHub account
- Railway account (sign up at railway.app)

### Step 1: Prepare Backend
```bash
cd backend

# Ensure .env has production settings
cp .env.example .env.production
```

### Step 2: Update .env.production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.railway.app

DB_CONNECTION=mysql
# Railway will auto-inject these:
# DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

### Step 3: Deploy to Railway

**Option A: Via Railway Dashboard (Easiest)**
1. Go to https://railway.app
2. Click "New Project" → "Deploy from GitHub repo"
3. Select your WashBox repository
4. Railway auto-detects Laravel (uses your railway.json)
5. Add MySQL database: Click "New" → "Database" → "MySQL"
6. Set environment variables in Railway dashboard
7. Deploy automatically starts

**Option B: Via Railway CLI**
```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Initialize project
railway init

# Link to MySQL database
railway add --database mysql

# Deploy
railway up

# Get your URL
railway domain
```

### Step 4: Run Migrations
```bash
# In Railway dashboard, go to your service
# Click "Settings" → "Deploy" → Add this to build command:
php artisan migrate --force
php artisan db:seed --force
```

### Step 5: Access Admin Panel
```
Admin: https://your-app.railway.app/admin/login
Staff: https://your-app.railway.app/staff/login
API: https://your-app.railway.app/api/v1/
```

---

## Mobile App Deployment (Expo EAS)

### Step 1: Update API URL
```bash
cd mobile

# Edit constants/config.js
# Change API_BASE_URL to your Railway URL
```

### Step 2: Configure EAS
```bash
# Install EAS CLI
npm install -g eas-cli

# Login to Expo
eas login

# Configure project
eas build:configure
```

### Step 3: Build Apps
```bash
# Build Android APK (for testing)
eas build --platform android --profile preview

# Build iOS (requires Apple Developer account)
eas build --platform ios --profile preview

# Production builds
eas build --platform all --profile production
```

### Step 4: Submit to Stores
```bash
# Google Play
eas submit --platform android

# App Store
eas submit --platform ios
```

---

## Alternative: Render.com Deployment

### Backend on Render
1. Go to https://render.com
2. New → Web Service
3. Connect GitHub repo
4. Build Command: `composer install && php artisan migrate --force`
5. Start Command: `php artisan serve --host=0.0.0.0 --port=$PORT`
6. Add PostgreSQL database
7. Set environment variables

---

## Environment Variables Needed

### Backend (Railway/Render)
```
APP_KEY=base64:... (generate with: php artisan key:generate)
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

FIREBASE_CREDENTIALS={"type":"service_account",...}
FIREBASE_DATABASE_URL=https://your-project.firebaseio.com

GOOGLE_MAPS_API_KEY=your_key_here
```

### Mobile App
Update `mobile/constants/config.js`:
```javascript
export const API_BASE_URL = 'https://your-app.railway.app/api';
```

---

## Cost Estimate

### Free Tier Usage
- **Railway**: $5 free credit (lasts ~1 month for small app)
- **Render**: 750 hours/month free (1 service)
- **Expo EAS**: Unlimited dev builds, free OTA updates
- **Firebase**: Free tier (Spark plan)

### After Free Tier
- **Railway**: ~$5-10/month
- **Render**: $7/month (Starter plan)
- **Google Play**: $25 one-time
- **Apple App Store**: $99/year

---

## Quick Start Commands

```bash
# 1. Deploy Backend
cd backend
railway login
railway init
railway up

# 2. Get backend URL
railway domain

# 3. Update mobile config
cd ../mobile
# Edit constants/config.js with Railway URL

# 4. Build mobile app
eas build --platform android --profile preview

# 5. Test
# Download APK from EAS and install on Android device
```

---

## Troubleshooting

### Backend Issues
- Check logs: `railway logs`
- Verify database connection in Railway dashboard
- Ensure migrations ran: `railway run php artisan migrate:status`

### Mobile App Issues
- Clear cache: `npx expo start -c`
- Rebuild: `eas build --platform android --clear-cache`
- Check API_BASE_URL in config.js

---

## Next Steps After Deployment

1. Create admin account via database seeder
2. Login to admin panel
3. Configure settings (Firebase, Google Maps)
4. Add branches, services, staff
5. Test mobile app with real backend
6. Submit apps to stores
