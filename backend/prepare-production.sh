#!/bin/bash

# WashBox Production Preparation Script
# This script prepares the application for production deployment

echo "🚀 WashBox Production Preparation Script"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check if running in backend directory
if [ ! -f "artisan" ]; then
    print_error "Please run this script from the Laravel backend directory"
    exit 1
fi

echo "Step 1: Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
print_success "Caches cleared"
echo ""

echo "Step 2: Checking environment configuration..."
if [ ! -f ".env" ]; then
    print_error ".env file not found!"
    echo "Creating from .env.example..."
    cp .env.example .env
    print_warning "Please configure .env file with production values"
else
    print_success ".env file exists"
    
    # Check critical environment variables
    if grep -q "APP_ENV=local" .env; then
        print_warning "APP_ENV is set to 'local' - should be 'production'"
    fi
    
    if grep -q "APP_DEBUG=true" .env; then
        print_warning "APP_DEBUG is set to 'true' - should be 'false' in production"
    fi
    
    if grep -q "APP_KEY=$" .env || grep -q "APP_KEY=\"\"" .env; then
        print_warning "APP_KEY is not set - generating new key..."
        php artisan key:generate
        print_success "APP_KEY generated"
    fi
fi
echo ""

echo "Step 3: Installing/Updating dependencies..."
composer install --optimize-autoloader --no-dev
print_success "Dependencies installed"
echo ""

echo "Step 4: Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
print_success "Application optimized"
echo ""

echo "Step 5: Checking storage permissions..."
chmod -R 775 storage bootstrap/cache
print_success "Permissions set"
echo ""

echo "Step 6: Checking database connection..."
if php artisan db:show > /dev/null 2>&1; then
    print_success "Database connection successful"
else
    print_error "Database connection failed - please check your .env configuration"
fi
echo ""

echo "Step 7: Creating production checklist..."
cat > PRODUCTION_CHECKLIST.md << 'EOF'
# WashBox Production Deployment Checklist

## ✅ Pre-Deployment Tasks

### Environment Configuration
- [ ] Set `APP_ENV=production` in .env
- [ ] Set `APP_DEBUG=false` in .env
- [ ] Configure production database credentials
- [ ] Set proper `APP_URL` (e.g., https://yourdomain.com)
- [ ] Configure mail server (SMTP/SendGrid/Mailgun)
- [ ] Add Firebase credentials file path
- [ ] Configure AWS S3 credentials (if using)
- [ ] Set secure `SESSION_DOMAIN`
- [ ] Configure `SANCTUM_STATEFUL_DOMAINS`

### Security
- [ ] Generate new `APP_KEY` (php artisan key:generate)
- [ ] Review API routes authentication
- [ ] Configure CORS for production domains
- [ ] Enable HTTPS/SSL certificate
- [ ] Set secure session cookies (SESSION_SECURE_COOKIE=true)
- [ ] Configure rate limiting
- [ ] Review file upload restrictions

### Database
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed system settings: `php artisan db:seed --class=SystemSettingsSeeder`
- [ ] Backup database before deployment
- [ ] Configure automated backups

### Server Configuration
- [ ] Setup cron job for Laravel scheduler:
  ```
  * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
  ```
- [ ] Setup supervisor for queue worker:
  ```
  [program:washbox-worker]
  process_name=%(program_name)s_%(process_num)02d
  command=php /path-to-project/artisan queue:work --tries=3 --timeout=90
  autostart=true
  autorestart=true
  user=www-data
  numprocs=2
  redirect_stderr=true
  stdout_logfile=/path-to-project/storage/logs/worker.log
  ```
- [ ] Configure web server (Nginx/Apache)
- [ ] Setup SSL certificate (Let's Encrypt)
- [ ] Configure firewall rules
- [ ] Setup monitoring (UptimeRobot, New Relic)

### Performance
- [ ] Run: `php artisan optimize`
- [ ] Run: `php artisan config:cache`
- [ ] Run: `php artisan route:cache`
- [ ] Run: `php artisan view:cache`
- [ ] Run: `php artisan event:cache`
- [ ] Enable OPcache in PHP
- [ ] Configure Redis for cache/sessions (optional)

### Testing
- [ ] Test user registration/login
- [ ] Test pickup request creation
- [ ] Test laundry order flow
- [ ] Test payment processing
- [ ] Test notifications (FCM)
- [ ] Test map and routing features
- [ ] Test admin dashboard
- [ ] Test staff dashboard
- [ ] Test mobile app API endpoints
- [ ] Load testing (optional)

### Monitoring & Logging
- [ ] Configure log rotation
- [ ] Setup error tracking (Sentry, Bugsnag)
- [ ] Configure application monitoring
- [ ] Setup uptime monitoring
- [ ] Configure backup monitoring

### Documentation
- [ ] API documentation
- [ ] Deployment documentation
- [ ] User manual
- [ ] Admin guide
- [ ] Troubleshooting guide

## 🚀 Deployment Commands

```bash
# On production server
cd /path-to-project

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Restart services
sudo supervisorctl restart washbox-worker:*
php artisan queue:restart
```

## 📱 Mobile App Deployment

### Android
- [ ] Update API base URL in app config
- [ ] Build release APK/AAB
- [ ] Test on physical devices
- [ ] Upload to Google Play Console
- [ ] Submit for review

### iOS
- [ ] Update API base URL in app config
- [ ] Build release IPA
- [ ] Test on physical devices
- [ ] Upload to App Store Connect
- [ ] Submit for review

## 🔄 Post-Deployment

- [ ] Verify all features working
- [ ] Check error logs
- [ ] Monitor performance
- [ ] Test mobile app connectivity
- [ ] Verify scheduled tasks running
- [ ] Check queue workers status
- [ ] Test notification delivery
- [ ] Verify backup system

## 📞 Emergency Contacts

- Server Provider Support: _______________
- Database Admin: _______________
- DevOps Team: _______________
- Project Manager: _______________

## 🔐 Important URLs

- Production URL: _______________
- Admin Panel: _______________/admin
- Staff Panel: _______________/staff
- API Base: _______________/api
- Database Host: _______________

EOF

print_success "Production checklist created: PRODUCTION_CHECKLIST.md"
echo ""

echo "Step 8: Creating .env.production template..."
cat > .env.production.example << 'EOF'
# WashBox Production Environment Configuration
# Copy this to .env and fill in your production values

APP_NAME="WashBox Laundry"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=Asia/Manila

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=washbox_production
DB_USERNAME=your-db-username
DB_PASSWORD=your-secure-password

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=.yourdomain.com

CACHE_STORE=database
QUEUE_CONNECTION=database

# Broadcasting (Laravel Reverb)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=yourdomain.com
REVERB_PORT=443
REVERB_SCHEME=https

# Mail Configuration (Example: SendGrid)
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Firebase Configuration
FIREBASE_PROJECT=your-firebase-project
FIREBASE_CREDENTIALS=/path/to/firebase-credentials.json
FIREBASE_DATABASE_URL=https://your-project.firebaseio.com/

# AWS S3 Configuration (Optional)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=washbox-production
AWS_USE_PATH_STYLE_ENDPOINT=false
AWS_URL=https://your-bucket.s3.amazonaws.com

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com
SANCTUM_GUARD=web

# Logging
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=error
LOG_DEPRECATIONS_CHANNEL=null

# Security
BCRYPT_ROUNDS=12

# CORS Configuration
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://www.yourdomain.com
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization,X-Requested-With

# Rate Limiting
RATE_LIMIT_PER_MINUTE=60

# Google Maps (if using)
GOOGLE_MAPS_API_KEY=your-google-maps-key

# Payment Gateway (if applicable)
PAYMENT_GATEWAY_KEY=your-payment-key
PAYMENT_GATEWAY_SECRET=your-payment-secret

# Monitoring (Optional)
SENTRY_LARAVEL_DSN=your-sentry-dsn

EOF

print_success ".env.production.example created"
echo ""

echo "Step 9: Creating deployment documentation..."
cat > DEPLOYMENT.md << 'EOF'
# WashBox Deployment Guide

## Server Requirements

- PHP 8.2 or higher
- MySQL 8.0 or higher
- Composer 2.x
- Node.js 18+ (for asset compilation)
- Supervisor (for queue workers)
- Nginx or Apache
- SSL Certificate

## Recommended Server Specifications

### Small Scale (< 1000 users)
- 2 CPU cores
- 4GB RAM
- 50GB SSD storage
- 2TB bandwidth

### Medium Scale (1000-5000 users)
- 4 CPU cores
- 8GB RAM
- 100GB SSD storage
- 5TB bandwidth

### Large Scale (5000+ users)
- 8+ CPU cores
- 16GB+ RAM
- 200GB+ SSD storage
- 10TB+ bandwidth

## Deployment Steps

### 1. Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip php8.2-gd \
    php8.2-redis php8.2-intl

# Install MySQL
sudo apt install -y mysql-server

# Install Nginx
sudo apt install -y nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Supervisor
sudo apt install -y supervisor

# Install Certbot for SSL
sudo apt install -y certbot python3-certbot-nginx
```

### 2. Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/yourusername/washbox.git
cd washbox/backend
sudo chown -R www-data:www-data /var/www/washbox
```

### 3. Configure Application

```bash
# Copy environment file
cp .env.production.example .env

# Edit .env with your production values
nano .env

# Install dependencies
composer install --optimize-autoloader --no-dev

# Generate application key
php artisan key:generate

# Set permissions
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### 4. Database Setup

```bash
# Create database
mysql -u root -p
CREATE DATABASE washbox_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'washbox'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON washbox_production.* TO 'washbox'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force

# Seed system settings
php artisan db:seed --class=SystemSettingsSeeder
```

### 5. Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/washbox
```

Add this configuration:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/washbox/backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/washbox /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 6. Setup SSL Certificate

```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### 7. Configure Supervisor for Queue Workers

```bash
sudo nano /etc/supervisor/conf.d/washbox-worker.conf
```

Add:
```ini
[program:washbox-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/washbox/backend/artisan queue:work --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/washbox/backend/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start washbox-worker:*
```

### 8. Setup Cron Job for Scheduler

```bash
sudo crontab -e -u www-data
```

Add:
```
* * * * * cd /var/www/washbox/backend && php artisan schedule:run >> /dev/null 2>&1
```

### 9. Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 10. Setup Log Rotation

```bash
sudo nano /etc/logrotate.d/washbox
```

Add:
```
/var/www/washbox/backend/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

## Updating Application

```bash
cd /var/www/washbox/backend

# Enable maintenance mode
php artisan down

# Pull latest changes
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear and rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Restart queue workers
sudo supervisorctl restart washbox-worker:*
php artisan queue:restart

# Disable maintenance mode
php artisan up
```

## Monitoring

### Check Queue Workers
```bash
sudo supervisorctl status washbox-worker:*
```

### Check Logs
```bash
tail -f storage/logs/laravel.log
tail -f storage/logs/worker.log
```

### Check Scheduled Tasks
```bash
php artisan schedule:list
```

## Troubleshooting

### Permission Issues
```bash
sudo chown -R www-data:www-data /var/www/washbox
sudo chmod -R 775 storage bootstrap/cache
```

### Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Restart Services
```bash
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo supervisorctl restart washbox-worker:*
```

## Security Checklist

- [ ] Firewall configured (UFW)
- [ ] SSH key-based authentication only
- [ ] Fail2ban installed
- [ ] Database not accessible from outside
- [ ] Regular backups configured
- [ ] SSL certificate auto-renewal enabled
- [ ] File upload restrictions in place
- [ ] Rate limiting configured

## Backup Strategy

### Database Backup (Daily)
```bash
#!/bin/bash
mysqldump -u washbox -p washbox_production > /backups/db-$(date +%Y%m%d).sql
find /backups -name "db-*.sql" -mtime +7 -delete
```

### File Backup (Weekly)
```bash
#!/bin/bash
tar -czf /backups/files-$(date +%Y%m%d).tar.gz /var/www/washbox/backend/storage
find /backups -name "files-*.tar.gz" -mtime +30 -delete
```

EOF

print_success "Deployment documentation created: DEPLOYMENT.md"
echo ""

echo "=========================================="
echo "✅ Production preparation complete!"
echo ""
echo "📋 Next steps:"
echo "1. Review and edit .env file with production values"
echo "2. Review PRODUCTION_CHECKLIST.md"
echo "3. Follow DEPLOYMENT.md for server setup"
echo "4. Test thoroughly before going live"
echo ""
print_warning "Remember: Never commit .env file to version control!"
echo "=========================================="
