# 🧺 WashBox - Laundry Management System

A comprehensive laundry management system with web admin/staff dashboards and mobile app support.

## 📋 Features

### 🎯 Core Features
- **Customer Management** - Registration, profiles, order history
- **Laundry Orders** - Create, track, and manage laundry orders
- **Pickup Requests** - Schedule and manage pickup/delivery
- **Real-time Tracking** - GPS-based tracking with map integration
- **Route Optimization** - AI-powered route planning for pickups
- **Payment Processing** - Multiple payment methods support
- **Notifications** - Push notifications via Firebase FCM
- **Rating System** - Customer feedback and ratings
- **Promotions** - Discount codes and promotional campaigns
- **Unclaimed Reminders** - Automated reminders (Day 3, 5, 7)

### 👨‍💼 Admin Dashboard
- Real-time analytics and statistics
- Customer and order management
- Staff management
- Service and pricing configuration
- Route optimization and mapping
- Financial reports
- System settings

### 👷 Staff Dashboard
- Pickup request management
- Order processing workflow
- Route navigation
- Customer communication
- Task management

### 📱 Mobile App Support
- RESTful API for Flutter/React Native apps
- Firebase authentication
- Push notifications
- Real-time order tracking
- In-app payments

## 🛠️ Technology Stack

### Backend
- **Framework:** Laravel 12.52.0
- **PHP:** 8.4.18
- **Database:** MySQL 8.0+
- **Queue:** Database driver
- **Broadcasting:** Laravel Reverb
- **Cache:** Database/Redis

### Frontend
- **JavaScript:** ES6+ Modules
- **Maps:** Leaflet.js + OSRM
- **Charts:** Chart.js
- **UI:** Bootstrap 5
- **Icons:** Bootstrap Icons

### Mobile
- **API:** RESTful JSON API
- **Auth:** Laravel Sanctum
- **Notifications:** Firebase Cloud Messaging

### Third-party Services
- **Firebase** - Authentication & Push Notifications
- **OSRM** - Route optimization
- **OpenStreetMap** - Mapping
- **Google Maps** (Optional) - Geocoding

## 📦 Installation

### Prerequisites
- PHP 8.2 or higher
- Composer 2.x
- MySQL 8.0+
- Node.js 18+ (optional, for asset compilation)

### Local Development Setup

1. **Clone the repository**
```bash
git clone https://github.com/yourusername/washbox.git
cd washbox/backend
```

2. **Install dependencies**
```bash
composer install
```

3. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Edit .env file**
```env
DB_DATABASE=washbox
DB_USERNAME=root
DB_PASSWORD=your_password

FIREBASE_CREDENTIALS=path/to/firebase-credentials.json
```

5. **Run migrations and seeders**
```bash
php artisan migrate
php artisan db:seed
```

6. **Create storage link**
```bash
php artisan storage:link
```

7. **Start development server**
```bash
php artisan serve
```

8. **Start queue worker** (in separate terminal)
```bash
php artisan queue:work
```

9. **Start scheduler** (for development)
```bash
php artisan schedule:work
```

## 🚀 Production Deployment

### Quick Start
```bash
cd backend
chmod +x prepare-production.sh
./prepare-production.sh
```

This will:
- Clear all caches
- Check environment configuration
- Install optimized dependencies
- Cache configurations
- Create deployment documentation

### Detailed Instructions
See [DEPLOYMENT.md](DEPLOYMENT.md) for complete deployment guide.

### Production Checklist
See [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md) for pre-deployment tasks.

## 📱 Mobile App Setup

### API Base URL
Configure in your mobile app:
```
Production: https://yourdomain.com/api
Development: http://localhost:8000/api
```

### Authentication
The API uses Laravel Sanctum for authentication:
```
POST /api/login
POST /api/register
POST /api/logout
```

### API Documentation
See [API.md](API.md) for complete API reference.

## 🔧 Configuration

### Environment Variables

#### Required
```env
APP_NAME="WashBox Laundry"
APP_ENV=production
APP_KEY=base64:...
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=washbox
DB_USERNAME=root
DB_PASSWORD=

FIREBASE_CREDENTIALS=firebase-credentials.json
```

#### Optional
```env
# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525

# AWS S3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=

# Broadcasting
BROADCAST_CONNECTION=reverb
```

### Firebase Setup

1. Create Firebase project at https://console.firebase.google.com
2. Download service account credentials JSON
3. Place in project root or configure path in .env
4. Enable Cloud Messaging in Firebase Console

### Scheduled Tasks

The following tasks run automatically:
- **Unclaimed Reminders** - Daily at 9:00 AM
- **Database Cleanup** - Daily at 2:00 AM
- **Report Generation** - Daily at 11:00 PM

Configure in `routes/console.php`

## 🧪 Testing

### Run Tests
```bash
php artisan test
```

### Manual Testing Checklist
- [ ] User registration and login
- [ ] Create laundry order
- [ ] Pickup request flow
- [ ] Payment processing
- [ ] Notifications delivery
- [ ] Admin dashboard features
- [ ] Staff dashboard features
- [ ] Map and routing
- [ ] Mobile API endpoints

## 📊 Database Schema

### Main Tables
- `users` - System users (admin, staff)
- `customers` - Customer accounts
- `branches` - Laundry branches
- `services` - Laundry services
- `addons` - Service add-ons
- `laundries` - Laundry orders
- `pickup_requests` - Pickup/delivery requests
- `notifications` - Push notifications log
- `promotions` - Discount promotions
- `customer_ratings` - Customer feedback

## 🔐 Security

### Best Practices Implemented
- ✅ CSRF protection
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS protection
- ✅ Password hashing (bcrypt)
- ✅ API rate limiting
- ✅ Input validation
- ✅ File upload restrictions
- ✅ Secure session handling

### Production Security Checklist
- [ ] Enable HTTPS/SSL
- [ ] Set APP_DEBUG=false
- [ ] Configure firewall
- [ ] Restrict database access
- [ ] Enable fail2ban
- [ ] Regular security updates
- [ ] Backup encryption

## 📈 Performance Optimization

### Implemented
- Route caching
- Config caching
- View caching
- Query optimization
- Eager loading relationships
- Database indexing
- Asset minification

### Recommended
- Redis for cache/sessions
- CDN for static assets
- Database query optimization
- Image optimization
- Load balancing (for scale)

## 🐛 Troubleshooting

### Common Issues

**Queue not processing**
```bash
php artisan queue:restart
sudo supervisorctl restart washbox-worker:*
```

**Permissions error**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

**Cache issues**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**Database connection failed**
- Check .env database credentials
- Verify MySQL is running
- Check firewall rules

## 📝 API Endpoints

### Authentication
```
POST   /api/register
POST   /api/login
POST   /api/logout
GET    /api/user
```

### Laundry Orders
```
GET    /api/laundries
POST   /api/laundries
GET    /api/laundries/{id}
PUT    /api/laundries/{id}
DELETE /api/laundries/{id}
```

### Pickup Requests
```
GET    /api/pickup-requests
POST   /api/pickup-requests
GET    /api/pickup-requests/{id}
PUT    /api/pickup-requests/{id}/status
```

See [API.md](API.md) for complete documentation.

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📄 License

This project is proprietary software. All rights reserved.

## 👥 Team

- **Project Manager:** [Name]
- **Lead Developer:** [Name]
- **Backend Developer:** [Name]
- **Mobile Developer:** [Name]
- **UI/UX Designer:** [Name]

## 📞 Support

- **Email:** support@washbox.com
- **Phone:** +63 XXX XXX XXXX
- **Website:** https://washbox.com

## 🗺️ Roadmap

### Version 1.0 (Current)
- ✅ Core laundry management
- ✅ Pickup/delivery system
- ✅ Route optimization
- ✅ Mobile app API
- ✅ Push notifications

### Version 1.1 (Planned)
- [ ] Multi-language support
- [ ] Advanced analytics
- [ ] Customer loyalty program
- [ ] Inventory management
- [ ] Staff performance tracking

### Version 2.0 (Future)
- [ ] AI-powered demand forecasting
- [ ] IoT machine integration
- [ ] Blockchain payment options
- [ ] AR try-on features
- [ ] Voice assistant integration

## 📚 Documentation

- [Deployment Guide](DEPLOYMENT.md)
- [Production Checklist](PRODUCTION_CHECKLIST.md)
- [API Documentation](API.md)
- [User Manual](USER_MANUAL.md)
- [Admin Guide](ADMIN_GUIDE.md)

## 🙏 Acknowledgments

- Laravel Framework
- OpenStreetMap
- Firebase
- Bootstrap
- Chart.js
- Leaflet.js

---

**Made with ❤️ by the WashBox Team**
