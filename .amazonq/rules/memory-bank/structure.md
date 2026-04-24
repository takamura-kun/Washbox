# WashBox - Project Structure

## Directory Organization

```
WashBox/
├── backend/                    # Laravel 12 backend application
│   ├── app/
│   │   ├── Console/           # Artisan commands
│   │   ├── Events/            # Event classes
│   │   ├── Http/
│   │   │   ├── Controllers/   # API and web controllers
│   │   │   ├── Middleware/    # HTTP middleware
│   │   │   └── Requests/      # Form request validation
│   │   ├── Mail/              # Mailable classes
│   │   ├── Models/            # Eloquent models
│   │   ├── Notifications/     # Notification classes
│   │   ├── Observers/         # Model observers
│   │   ├── Policies/          # Authorization policies
│   │   ├── Providers/         # Service providers
│   │   ├── Services/          # Business logic services
│   │   └── Traits/            # Reusable traits
│   ├── bootstrap/             # Application bootstrap
│   ├── config/                # Configuration files
│   ├── database/
│   │   ├── factories/         # Model factories
│   │   ├── migrations/        # Database migrations
│   │   └── seeders/           # Database seeders
│   ├── docs/                  # Documentation
│   ├── public/
│   │   ├── assets/            # CSS, JS, images
│   │   └── images/            # Static images
│   ├── resources/
│   │   ├── css/               # Stylesheets
│   │   ├── js/                # JavaScript files
│   │   └── views/             # Blade templates
│   ├── routes/
│   │   ├── api.php            # API routes
│   │   ├── web.php            # Web routes
│   │   ├── channels.php       # Broadcasting channels
│   │   ├── console.php        # Scheduled tasks
│   │   └── location-tracking.php
│   ├── storage/               # Application storage
│   ├── tests/                 # Test files
│   └── vendor/                # Composer dependencies
│
├── mobile/                    # Expo React Native app
│   ├── app/
│   │   ├── (auth)/            # Authentication screens
│   │   ├── (tabs)/            # Tab navigation screens
│   │   ├── laundries/         # Laundry order screens
│   │   ├── pickups/           # Pickup request screens
│   │   ├── profile/           # User profile screens
│   │   ├── promotions/        # Promotions screens
│   │   ├── ratings/           # Rating screens
│   │   ├── receipt/           # Receipt screens
│   │   └── utils/             # Utility screens
│   ├── components/
│   │   ├── common/            # Shared components
│   │   ├── pickup-delivery/   # Pickup/delivery components
│   │   └── ui/                # UI components
│   ├── constants/             # App constants
│   ├── context/               # React context
│   ├── hooks/                 # Custom hooks
│   ├── services/              # API and location services
│   ├── store/                 # State management
│   ├── styles/                # Global styles
│   ├── utils/                 # Utility functions
│   ├── assets/                # Images, fonts, sounds
│   └── android/               # Android native code
│
└── documentation/             # Project documentation
    ├── ERD.md                 # Entity relationship diagram
    ├── requirements_modeling.md
    └── chapter_*.md           # Technical documentation
```

## Core Components

### Backend Architecture
- **Controllers**: Handle HTTP requests and responses
- **Models**: Eloquent ORM models for database entities
- **Services**: Business logic encapsulation
- **Middleware**: Request/response processing
- **Events/Listeners**: Event-driven architecture
- **Notifications**: Push notifications via Firebase
- **Policies**: Authorization and access control

### Mobile Architecture
- **Screens**: File-based routing with Expo Router
- **Components**: Reusable UI components
- **Services**: API client, location tracking, routing
- **Context**: Global state management
- **Hooks**: Custom React hooks for logic reuse

### Database Schema
Key entities:
- `users` - System users (admin, staff)
- `customers` - Customer accounts
- `branches` - Laundry branches
- `services` - Laundry services offered
- `addons` - Service add-ons
- `laundries` - Laundry orders
- `pickup_requests` - Pickup/delivery requests
- `notifications` - Push notification logs
- `promotions` - Discount promotions
- `customer_ratings` - Customer feedback

## Architectural Patterns

### MVC Pattern (Backend)
- Models handle data access
- Controllers handle business logic and routing
- Views render responses

### Service Layer Pattern
- Business logic separated into service classes
- Controllers delegate to services
- Promotes code reusability and testability

### Observer Pattern
- Model observers for automatic event handling
- Triggers notifications and updates

### Repository Pattern (Implicit)
- Eloquent models act as repositories
- Encapsulates data access logic

### API-First Design
- RESTful API for mobile and web clients
- JSON responses
- Laravel Sanctum for authentication

## Integration Points

### External Services
- **Firebase**: Authentication and push notifications
- **OSRM**: Route optimization and distance calculation
- **OpenStreetMap**: Map rendering and geocoding
- **Google Maps** (optional): Geocoding services

### Internal Communication
- API endpoints for mobile app
- WebSocket broadcasting via Laravel Reverb
- Queue system for async tasks
- Scheduled tasks via Laravel Scheduler
