# WashBox - Technology Stack

## Backend

### Core Framework
- **Laravel**: 12.52.0
- **PHP**: 8.4.18
- **Composer**: 2.x (dependency manager)

### Database
- **MySQL**: 8.0+
- **SQLite**: For development/testing

### Key Libraries
- **Laravel Sanctum**: API authentication
- **Laravel Reverb**: WebSocket broadcasting
- **Firebase Admin SDK**: Push notifications and authentication
- **Eloquent ORM**: Database abstraction
- **Validation**: Built-in request validation
- **Queue**: Database driver for async jobs
- **Cache**: Database/Redis support

### Frontend (Admin/Staff Dashboards)
- **JavaScript**: ES6+ modules
- **Bootstrap 5**: UI framework
- **Bootstrap Icons**: Icon library
- **Chart.js**: Data visualization
- **Leaflet.js**: Map rendering
- **OSRM**: Route optimization API
- **Axios**: HTTP client
- **Tailwind CSS**: Utility-first CSS (optional)

### Development Tools
- **Vite**: Asset bundler
- **Laravel Vite Plugin**: Integration with Laravel
- **PHPUnit**: Testing framework
- **Artisan**: CLI tool

## Mobile

### Framework & Runtime
- **Expo**: 54.0.0 (React Native framework)
- **React**: 19.1.0
- **React Native**: 0.81.5
- **Node.js**: 18+ (development)

### Navigation & UI
- **Expo Router**: File-based routing
- **React Navigation**: Navigation library
- **React Native Gesture Handler**: Gesture support
- **React Native Reanimated**: Animation library
- **Lucide React Native**: Icon library
- **React Native Vector Icons**: Additional icons

### Maps & Location
- **Expo Location**: GPS and location services
- **React Native Maps**: Map component
- **Leaflet**: Map library (web)
- **Expo Task Manager**: Background tasks

### Storage & State
- **AsyncStorage**: Local data persistence
- **Zustand**: State management
- **Expo File System**: File operations

### Notifications & Media
- **Expo Notifications**: Push notifications
- **Expo AV**: Audio/video playback
- **Expo Image Picker**: Image selection
- **Expo Haptics**: Haptic feedback

### Authentication
- **Expo Auth Session**: OAuth flow
- **Firebase Authentication**: User authentication

### Development
- **TypeScript**: Type safety (optional)
- **ESLint**: Code linting
- **Expo CLI**: Development server

## Third-Party Services

### Firebase
- **Authentication**: User sign-up and login
- **Cloud Messaging (FCM)**: Push notifications
- **Service Account**: Admin SDK integration

### Mapping & Routing
- **OpenStreetMap**: Map tiles and geocoding
- **OSRM (Open Source Routing Machine)**: Route optimization
- **Google Maps** (optional): Geocoding fallback

## Development Commands

### Backend
```bash
# Install dependencies
composer install

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Start development server
php artisan serve

# Run queue worker
php artisan queue:work

# Run scheduler
php artisan schedule:work

# Run tests
php artisan test

# Cache configuration
php artisan config:cache

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Mobile
```bash
# Install dependencies
npm install

# Start development server
npm start
# or
npx expo start

# Run on Android
npm run android
# or
npx expo run:android

# Run on iOS
npm run ios
# or
npx expo run:ios

# Run on web
npm run web
# or
npx expo start --web

# Lint code
npm run lint
```

### Frontend (Admin/Staff)
```bash
# Development
npm run dev

# Production build
npm run build
```

## Environment Configuration

### Backend (.env)
```env
APP_NAME="WashBox Laundry"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=washbox
DB_USERNAME=root
DB_PASSWORD=

FIREBASE_CREDENTIALS=firebase-credentials.json

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database
CACHE_DRIVER=database
```

### Mobile (app.json)
- API base URL configuration
- Firebase configuration
- App name and version
- Permissions and capabilities

## Performance Considerations

### Backend Optimization
- Route caching
- Config caching
- View caching
- Query optimization with eager loading
- Database indexing
- Redis for cache/sessions (optional)

### Mobile Optimization
- Code splitting
- Image optimization
- Lazy loading
- Efficient state management
- Background task optimization

### Frontend Optimization
- Asset minification
- CSS/JS bundling
- Image optimization
- CDN for static assets (optional)

## Security Features

### Backend
- CSRF protection
- SQL injection prevention (Eloquent ORM)
- XSS protection
- Password hashing (bcrypt)
- API rate limiting
- Input validation
- File upload restrictions
- Secure session handling

### Mobile
- Secure token storage
- HTTPS enforcement
- Certificate pinning (optional)
- Secure local storage

### API
- Laravel Sanctum token authentication
- CORS configuration
- Request validation
- Authorization policies
