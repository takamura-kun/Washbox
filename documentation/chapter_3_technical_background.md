# CHAPTER III: TECHNICAL BACKGROUND

This section discusses and presents different sets of diagrams and charts pertaining to the WashBox Laundry Management System (WLMS) that is being developed by the proponents.

## Technicality of the Project

### System Architecture Overview

The WashBox Laundry Management System employs a modern three-tier architecture consisting of a presentation layer, application layer, and data layer. The system is designed as a distributed application with web-based administrative interfaces and mobile client applications communicating through RESTful APIs.

**Architecture Components:**
- **Frontend Layer**: Responsive web interface using Bootstrap 5 framework with dark/light theme support
- **Backend Layer**: Laravel PHP framework providing RESTful API services and web application logic
- **Database Layer**: MySQL relational database management system for persistent data storage
- **Mobile Layer**: React Native (Expo) cross-platform application with JavaScript/TypeScript implementation
- **Notification Layer**: Firebase Cloud Messaging (FCM) for real-time push notifications
- **Mapping Layer**: OpenStreetMap integration for location-based services

### Technical Complexity Analysis

The system addresses multiple technical challenges inherent in multi-branch service management:

**Data Synchronization**: Real-time synchronization across three branch locations requires careful database design and transaction management to ensure data consistency and prevent conflicts during concurrent operations.

**Location-Based Services**: Integration of OpenStreetMap APIs for pickup request mapping and route planning involves coordinate transformation, geocoding, and spatial data processing.

**Multi-Platform Communication**: Seamless data exchange between web interfaces and mobile applications requires robust API design with proper authentication, data validation, and error handling mechanisms.

**Notification Management**: Automated push notification delivery through Firebase FCM involves device token management, message queuing, and delivery status tracking across multiple user devices.

**Pricing Algorithm Complexity**: The dual pricing structure (per-load and per-piece) combined with branch-specific delivery fees and discount calculations requires sophisticated algorithmic implementation to ensure accuracy and consistency.

## Details of the Technologies to be Used

### Backend Technologies

**Laravel Framework (PHP 8.2+, Laravel 12)**
Laravel 12 serves as the primary backend framework, providing:
- **Eloquent ORM**: Object-relational mapping for database interactions with models for Customer, Order, Pickup, Payment, and DeliveryFee entities
- **Artisan CLI**: Command-line interface for database migrations, cache management, and system maintenance
- **Blade Templating**: Server-side rendering for web interfaces with component-based architecture
- **Authentication System**: Built-in user authentication with role-based access control (Owner, Staff, Customer)
- **API Resources**: RESTful API development with resource controllers and JSON response formatting

**MySQL Database (8.0+) / SQLite (Development)**
Relational database management providing:
- **ACID Compliance**: Ensures data integrity across multi-branch operations
- **Indexing**: Optimized query performance for customer lookups and order tracking
- **Foreign Key Constraints**: Maintains referential integrity between related entities
- **Transaction Support**: Atomic operations for complex business processes like order creation with fee calculations

### Frontend Technologies

**Bootstrap 5 Framework**
Responsive CSS framework enabling:
- **Grid System**: Flexible layout management across desktop, tablet, and mobile viewports
- **Component Library**: Pre-built UI components for forms, tables, modals, and navigation
- **Theme Customization**: CSS custom properties for dark/light mode implementation
- **Cross-Browser Compatibility**: Consistent rendering across modern web browsers

**JavaScript (ES6+)**
Client-side scripting for:
- **DOM Manipulation**: Dynamic content updates and user interface interactions
- **AJAX Communication**: Asynchronous data exchange with backend APIs
- **Theme Management**: Dark/light mode switching with localStorage persistence
- **Form Validation**: Client-side input validation before server submission

### Mobile Technologies

**React Native with Expo (SDK 54)**
Cross-platform mobile development utilizing:
- **Expo Router**: File-based navigation with tab and stack layouts for different app screens
- **React Navigation**: Bottom tabs and stack navigation for laundry orders and pickup requests
- **Fetch API**: HTTP client for RESTful API communication with the Laravel backend
- **expo-notifications**: Push notification integration and device token management
- **react-native-maps / Leaflet**: Mapping solution with OpenStreetMap integration for location services

### Integration Technologies

**Firebase Cloud Messaging (FCM)**
Push notification service providing:
- **Device Token Management**: Unique identifier assignment for notification targeting
- **Message Queuing**: Reliable delivery of notifications even when devices are offline
- **Delivery Analytics**: Tracking of notification delivery success rates and user engagement
- **Cross-Platform Support**: Unified notification system for Android and potential iOS expansion

**OpenStreetMap (OSM) APIs**
Location-based services offering:
- **Geocoding Services**: Address-to-coordinate conversion for pickup locations
- **Reverse Geocoding**: Coordinate-to-address conversion for location display
- **Tile Rendering**: Map visualization with customizable styling and markers
- **Route Calculation**: Basic routing algorithms for pickup optimization

## How the Project Will Work

### System Operation Flow

**Customer Registration and Authentication**
1. Walk-in customers provide information at branch counters, staff manually registers them in the web system
2. Mobile app users self-register through the React Native mobile application with email verification or Google OAuth
3. Authentication tokens are generated for secure API access and session management
4. Role-based permissions determine accessible features (Customer, Staff, Owner)

**Laundry Order Processing Workflow**
1. **Order Creation**: Staff creates laundry orders at branch locations, entering item details, weight, and service type
2. **Pricing Calculation**: System automatically calculates fees using the DeliveryFee model with branch-specific rates and discount algorithms
3. **Status Tracking**: Orders progress through five stages (Received → Washing → Ready → Paid → Completed)
4. **Notification Delivery**: Automated push notifications sent to customers at each status transition
5. **Payment Processing**: Customers make payments through mobile app or at branch locations
6. **Order Completion**: Final status update triggers completion notification and order archival

**Pickup Request Management**
1. **Request Submission**: Customers use mobile app to submit pickup requests with location pinning via OpenStreetMap
2. **Fee Calculation**: System calculates delivery fees based on distance, branch rates, and applicable discounts
3. **Staff Notification**: Pickup requests appear on web dashboard with mapped locations for route planning
4. **Collection Process**: Staff collects laundry items and creates corresponding laundry orders in the system
5. **Status Synchronization**: Pickup request status updates automatically sync with created laundry orders

**Multi-Branch Data Management**
1. **Centralized Database**: Single MySQL database serves all three branch locations with branch-specific data segregation
2. **Real-Time Synchronization**: All branch operations update the central database immediately for consistency
3. **Reporting and Analytics**: Consolidated reporting across branches with branch-specific filtering capabilities
4. **User Access Control**: Staff access limited to their assigned branch, owners have multi-branch visibility

## Theoretical/Conceptual Framework

### Model-View-Controller (MVC) Architecture Pattern

The WashBox system implements the MVC architectural pattern through Laravel's framework structure, providing clear separation of concerns and maintainable code organization.

**Model Layer (Data Management)**
- **Eloquent Models**: Customer, Order, Pickup, Payment, DeliveryFee, and User models encapsulate business logic and database interactions
- **Database Relationships**: One-to-many relationships between customers and orders, many-to-many relationships for order items
- **Data Validation**: Model-level validation rules ensure data integrity and business rule compliance
- **Query Optimization**: Eager loading and database indexing strategies minimize query execution time

**View Layer (Presentation)**
- **Blade Templates**: Server-side rendering with component reusability and template inheritance
- **Responsive Design**: Bootstrap grid system ensures optimal display across device form factors
- **Theme Management**: CSS custom properties for dark/light mode switching with localStorage persistence
- **Mobile Responsiveness**: Adaptive layouts ensuring consistent user experience across device form factors
- **Component Reusability**: Blade components and partials reduce code duplication across views

**Controller Layer (Business Logic)**
- **Resource Controllers**: Handle HTTP requests and coordinate between models and views for each entity
- **API Controllers**: Dedicated controllers for mobile app communication with JSON response formatting
- **Middleware**: Authentication checks, role verification, and request preprocessing before controller execution
- **Form Requests**: Encapsulated validation logic for clean and reusable input handling

### Repository Pattern

Beyond standard MVC, the system applies the Repository Pattern to abstract database access logic from business logic, enabling:
- **Testability**: Business logic can be unit tested independently of the database layer
- **Flexibility**: Database implementation can be swapped without affecting application logic
- **Code Reusability**: Common query patterns are centralized and reused across controllers

### Observer Pattern for Notifications

The notification system leverages the Observer Pattern through Laravel's event and listener system:
- **Events**: Status change events (OrderProcessed, OrderReady, OrderCompleted) are dispatched when order states transition
- **Listeners**: Notification listeners respond to events and trigger Firebase FCM push notifications
- **Decoupling**: Business logic remains independent of notification delivery mechanisms
- **Extensibility**: New notification channels (email, SMS) can be added as listeners without modifying core logic

### Service Layer Pattern

Complex business operations are encapsulated in dedicated service classes:
- **FCMService**: Manages device token retrieval, message preparation, and Firebase FCM API communication
- **NotificationService / LaundryNotificationService**: Coordinates notification dispatch across customer, staff, and admin targets
- **GeocodingService / PhilippineGeocodingService**: Handles address-to-coordinate conversion for Philippine pickup locations
- **DashboardSyncService**: Maintains cached dashboard metrics and invalidates them on order or pickup state changes
- **RouteService**: Provides routing and distance calculation for pickup optimization

## Diagrams

### Figure 1: System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        WASHBOX SYSTEM ARCHITECTURE                      │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│   PRESENTATION LAYER                                                    │
│   ┌──────────────────────────┐     ┌──────────────────────────┐        │
│   │      WEB DASHBOARD       │     │    MOBILE APPLICATION    │        │
│   │  (Bootstrap 5 + Blade)   │     │  (React Native / Expo)   │        │
│   │  Owner / Staff / Admin   │     │    Customer Access        │        │
│   └────────────┬─────────────┘     └────────────┬─────────────┘        │
│                │  HTTP/HTTPS                     │  REST API (JSON)     │
├────────────────┼─────────────────────────────────┼──────────────────────┤
│   APPLICATION LAYER                              │                      │
│   ┌────────────▼─────────────────────────────────▼──────────────┐      │
│   │              LARAVEL BACKEND (PHP 8.2+, Laravel 12)           │      │
│   │  ┌─────────────┐  ┌─────────────┐  ┌──────────────────────┐ │      │
│   │  │ Controllers │  │  Services   │  │      Middleware       │ │      │
│   │  │ Admin/Staff │  │ FCMService  │  │ Auth / Role / Branch  │ │      │
│   │  │ Api/        │  │ GeocodingSvc│  │ ActivityLog / CORS    │ │      │
│   │  └─────────────┘  └─────────────┘  └──────────────────────┘ │      │
│   │  ┌─────────────┐  ┌─────────────┐  ┌──────────────────────┐ │      │
│   │  │   Models    │  │  Observers  │  │       Policies       │ │      │
│   │  │  (Eloquent) │  │  Laundry /  │  │  Branch / Customer   │ │      │
│   │  │             │  │  Pickup /   │  │  Order / Pickup      │ │      │
│   │  │             │  │  Promotion  │  │                      │ │      │
│   │  └─────────────┘  └─────────────┘  └──────────────────────┘ │      │
│   └──────────────────────────┬──────────────────────────────────┘      │
│                               │                                         │
├───────────────────────────────┼─────────────────────────────────────────┤
│   DATA LAYER                  │                                         │
│   ┌───────────────────────────▼─────────────────────────────────┐      │
│   │                   MySQL DATABASE (8.0+)                      │      │
│   │  customers    │ laundries       │ pickup_requests │ payments  │      │
│   │  branches     │ users           │ notifications   │ services  │      │
│   │  device_tokens│ delivery_fees   │ promotions      │ add_ons   │      │
│   └─────────────────────────────────────────────────────────────┘      │
│                                                                         │
├─────────────────────────────────────────────────────────────────────────┤
│   INTEGRATION LAYER                                                     │
│   ┌────────────────────────┐     ┌────────────────────────┐            │
│   │  Firebase FCM          │     │  OpenStreetMap API     │            │
│   │  (Push Notifications   │     │  (Geocoding / Reverse  │            │
│   │   via FCMService.php)  │     │   Geocoding / Tiles)   │            │
│   └────────────────────────┘     └────────────────────────┘            │
└─────────────────────────────────────────────────────────────────────────┘
```

### Figure 2: MVC Architecture Pattern Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    MVC ARCHITECTURE — WASHBOX                   │
└─────────────────────────────────────────────────────────────────┘

         USER REQUEST (Web Browser / Mobile App)
              │
              ▼
   ┌──────────────────────────────────────────┐
   │   ROUTES  (web.php / api.php)            │
   │   Admin Routes / Staff Routes / API      │
   └──────────────────┬───────────────────────┘
                      │
                      ▼
   ┌──────────────────────────────────────────┐
   │   MIDDLEWARE                             │
   │   AdminMiddleware / StaffMiddleware      │
   │   CustomerMiddleware / RoleMiddleware    │
   │   BranchAccessMiddleware / CORS          │
   └──────────────────┬───────────────────────┘
                      │
                      ▼
   ┌──────────────────────────────────────────┐
   │   CONTROLLER                             │
   │   Admin/  │  Staff/  │  Api/             │
   │   LaundryController  PickupController    │
   │   PaymentController  NotificationCtrl   │
   └──────┬───────────────────────┬───────────┘
          │                       │
          ▼                       ▼
   ┌──────────────────┐  ┌────────────────────────┐
   │  MODEL           │  │  VIEW                  │
   │  (Eloquent ORM)  │  │  Blade Templates       │
   │  Laundry         │  │  (Web Dashboard)       │
   │  PickupRequest   │  │  ────────────────────  │
   │  Customer        │  │  JSON API Response     │
   │  Payment         │  │  (Mobile App)          │
   │  DeliveryFee     │  └────────────────────────┘
   │  Promotion       │
   │  DeviceToken     │
   └────────┬─────────┘
            │
            ▼
   ┌──────────────────┐     ┌──────────────────────┐
   │  MySQL Database  │     │  Observers           │
   │  (Persistent     │     │  LaundryObserver     │
   │   Storage)       │     │  PickupRequestObsvr  │
   └──────────────────┘     │  PromotionObserver   │
                            └──────────────────────┘
```

### Figure 3: Laundry Order Status Flow Diagram

```
  ┌──────────┐     ┌─────────┐     ┌───────┐     ┌──────┐     ┌───────────┐
  │ RECEIVED │────►│ WASHING │────►│ READY │────►│ PAID │────►│ COMPLETED │
  └──────────┘     └─────────┘     └───────┘     └──────┘     └───────────┘
       │                │               │              │              │
       ▼                ▼               ▼              ▼              ▼
  [Notify:         [Notify:        [Notify:       [Notify:       [Notify:
   Customer]        Customer]       Customer]      Customer]      Customer]
  [Notify:         [Notify:        [Notify:       [Notify:       [Notify:
   Staff(branch)]   Staff(assigned)] Staff(branch)] Staff(assigned)] Staff(assigned)]
  [Notify:                         [Notify:       [Notify:       [Notify:
   Admin]                           Admin]         Admin]         Admin]
       │
       │ (any status)
       ▼
  ┌────────────┐
  │ CANCELLED  │──► [Notify: Customer + Admin + Staff(assigned)]
  └────────────┘

  Unclaimed Tracking (status = ready, unpaid):
  ┌──────────────────────────────────────────────────────────────────┐
  │  Day 1–2 unclaimed → Notify Staff (branch): warning             │
  │  Day 3+  unclaimed → Notify Staff (branch): urgent + Admin alert │
  └──────────────────────────────────────────────────────────────────┘
```

### Figure 4: Pickup Request Process Diagram

```
   CUSTOMER (Mobile App)         SYSTEM (Laravel)            STAFF (Web Dashboard)
          │                             │                               │
          │── Submit Pickup Request ───►│                               │
          │   (Location, Date, Time)    │── Store in DB                 │
          │                             │── Calculate pickup_fee /      │
          │                             │   delivery_fee (DeliveryFee)  │
          │◄─ Confirmation Notification─│                               │
          │   (pickup_submitted)        │── Notify Staff (branch) ─────►│
          │                             │── Notify Admin                │
          │                             │                               │
          │                             │       [Staff accepts]         │
          │◄─ Pickup Confirmed ─────────│◄─ status: accepted ───────────│
          │   (pickup_accepted)         │                               │
          │                             │                               │
          │                             │       [Staff en route]        │
          │◄─ Rider On The Way ─────────│◄─ status: en_route ───────────│
          │   (pickup_en_route)         │   [staff_latitude/longitude   │
          │                             │    updated in real-time]      │
          │                             │                               │
          │                             │       [Staff arrives]         │
          │◄─ Laundry Picked Up ────────│◄─ status: picked_up ──────────│
          │   (pickup_completed)        │── Notify Admin                │
          │                             │                               │
          │                             │   [Staff creates order]       │
          │                             │◄─ Create Laundry Order ───────│
          │                             │   (pickup_request_id linked)  │
          │◄─ Order Received ───────────│                               │
          │   (laundry_received)        │                               │
          │                             │                               │
          │      [Customer / Staff cancels at pending or accepted]      │
          │◄─ Pickup Cancelled ─────────│◄─ status: cancelled ──────────│
          │   (pickup_cancelled)        │── Notify Admin                │
```

### Figure 5: Notification System Flow Diagram

```
                    STATUS CHANGE (Laundry / PickupRequest)
                               │
                               ▼
                    ┌──────────────────────┐
                    │  Eloquent Observer   │
                    │  LaundryObserver /   │
                    │  PickupRequestObsvr  │
                    └──────┬───────────────┘
                           │
          ┌────────────────┼────────────────┐
          │                │                │
          ▼                ▼                ▼
  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
  │  CUSTOMER    │ │    STAFF     │ │    ADMIN     │
  │ Notification │ │ Notification │ │ Notification │
  │  (DB record) │ │  (DB record) │ │  (DB record) │
  └──────┬───────┘ └──────┬───────┘ └──────────────┘
         │                │
         ▼                ▼
  ┌──────────────────────────────┐
  │  FCMService.php              │
  │  sendToCustomer()            │
  │  sendToBranchStaff()         │
  └──────────────┬───────────────┘
                 │
                 ▼
  ┌──────────────────────────────┐
  │  Get FCM Token               │
  │  customer.fcm_token /        │
  │  DeviceToken (device_tokens) │
  └──────────────┬───────────────┘
                 │
         ┌───────▼────────┐
         │  Token valid?  │
         └───┬────────┬───┘
           YES│        │NO
              ▼        ▼
  ┌─────────────────┐  ┌──────────────────┐
  │ Send via        │  │ Log warning:     │
  │ Firebase FCM    │  │ empty/invalid    │
  │ CloudMessage    │  │ token — skip     │
  └────────┬────────┘  └──────────────────┘
           │
           ▼
  ┌─────────────────┐
  │ Log result      │
  │ (success /      │
  │  FCM error)     │
  └────────┬────────┘
           │
           ▼
  ┌───────────────────────────┐
  │ Customer / Staff receives │
  │ push notification on      │
  │ mobile / web device       │
  └───────────────────────────┘
```

### Figure 6: Pricing Calculation Algorithm Diagram

```
                        START
                          │
                          ▼
             ┌────────────────────────────┐
             │  Get Service,              │
             │  number_of_loads,          │
             │  branch_id, service_type,  │
             │  promotion                 │
             └────────────┬───────────────┘
                          │
             ┌────────────▼───────────────┐
             │  service.pricing_type?     │
             └──┬─────────────────────┬───┘
                │                     │
           per_load               per_piece
          (regular /             (special_item:
          full_service)           comforters etc.)
                │                     │
                ▼                     ▼
        ┌───────────────┐    ┌─────────────────────┐
        │ subtotal =    │    │ subtotal =           │
        │ price_per_load│    │ price_per_load       │
        │ × number_of_  │    │ × number_of_loads    │
        │ loads         │    │ (loads = # of pieces)│
        └───────┬───────┘    └──────────┬──────────┘
                └──────────┬────────────┘
                           │
                           ▼
             ┌─────────────────────────────┐
             │  Add add-ons total          │
             │  (laundry_addon pivot table)│
             └─────────────┬───────────────┘
                           │
             ┌─────────────▼───────────────┐
             │  Promotion applied?         │
             └──┬──────────────────────┬───┘
              YES│                     │NO
                 ▼                     │
        ┌────────────────────┐         │
        │ Apply promotion:   │         │
        │ promotion_override_│         │
        │ total OR           │         │
        │ promotion_price_   │         │
        │ per_load discount  │         │
        └────────┬───────────┘         │
                 └──────────┬──────────┘
                            │
             ┌──────────────▼──────────────┐
             │  Pickup / Delivery service? │
             │  (DeliveryFee model)        │
             └──┬──────────────────────┬───┘
              YES│                     │NO
                 ▼                     │
        ┌────────────────────┐         │
        │ service_type:      │         │
        │ pickup_only →      │         │
        │   add pickup_fee   │         │
        │ delivery_only →    │         │
        │   add delivery_fee │         │
        │ both →             │         │
        │   add both fees    │         │
        │   apply both_      │         │
        │   discount (%)     │         │
        │ amount ≥ minimum_  │         │
        │ laundry_for_free → │         │
        │   fees = 0         │         │
        └────────┬───────────┘         │
                 └──────────┬──────────┘
                            │
             ┌──────────────▼──────────────┐
             │  total_amount =             │
             │  subtotal                   │
             │  - discount_amount          │
             │  + pickup_fee               │
             │  + delivery_fee             │
             │  + addons_total             │
             └──────────────┬──────────────┘
                            │
                            ▼
                           END
```

### Figure 7: Theoretical/Conceptual Framework Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                  THEORETICAL / CONCEPTUAL FRAMEWORK                         │
│                  WashBox Laundry Management System                          │
└─────────────────────────────────────────────────────────────────────────────┘

  ┌─────────────────────────────────────────────────────────────────────────┐
  │                          DESIGN THEORIES                                │
  │                                                                         │
  │  ┌───────────────────┐  ┌───────────────────┐  ┌───────────────────┐   │
  │  │  MODEL-VIEW-      │  │    OBSERVER       │  │    SERVICE        │   │
  │  │  CONTROLLER (MVC) │  │    PATTERN        │  │    LAYER PATTERN  │   │
  │  │                   │  │                   │  │                   │   │
  │  │ Separates data,   │  │ Automatically     │  │ Encapsulates      │   │
  │  │ logic, and UI     │  │ triggers events   │  │ complex business  │   │
  │  │ into 3 layers     │  │ on model changes  │  │ logic in reusable │   │
  │  │                   │  │                   │  │ service classes   │   │
  │  │ Model → Database  │  │ LaundryObserver   │  │                   │   │
  │  │ View  → UI/API    │  │ PickupObserver    │  │ FCMService        │   │
  │  │ Controller→ Logic │  │ PromotionObserver │  │ NotificationSvc   │   │
  │  └────────┬──────────┘  └────────┬──────────┘  └────────┬──────────┘   │
  │           │                      │                       │              │
  └───────────┼──────────────────────┼───────────────────────┼──────────────┘
              │                      │                       │
              └──────────────────────┼───────────────────────┘
                                     │
                                     ▼
  ┌─────────────────────────────────────────────────────────────────────────┐
  │                         SYSTEM DEVELOPMENT                              │
  │                                                                         │
  │   INPUT                    PROCESS                      OUTPUT          │
  │  ┌──────────────┐        ┌──────────────────────┐     ┌─────────────┐  │
  │  │ Customer     │        │ 1. Authentication &  │     │ Digital     │  │
  │  │ Data         │───────►│    Role Management   │────►│ Receipts    │  │
  │  │              │        │                      │     │             │  │
  │  │ Laundry      │        │ 2. Laundry Order     │     │ Real-time   │  │
  │  │ Items &      │───────►│    Processing        │────►│ Status      │  │
  │  │ Weight       │        │                      │     │ Updates     │  │
  │  │              │        │ 3. Pickup Request    │     │             │  │
  │  │ Pickup       │───────►│    Management        │────►│ Push        │  │
  │  │ Location     │        │                      │     │ Notif-      │  │
  │  │              │        │ 4. Pricing &         │     │ ications    │  │
  │  │ Payment      │───────►│    Fee Calculation   │────►│             │  │
  │  │ Information  │        │                      │     │ Analytics   │  │
  │  │              │        │ 5. Notification      │     │ & Reports   │  │
  │  │ Staff        │───────►│    Delivery (FCM)    │────►│             │  │
  │  │ Actions      │        │                      │     │ Payment     │  │
  │  │              │        │ 6. Reporting &       │     │ Confirm-    │  │
  │  │ Branch       │───────►│    Analytics         │────►│ ations      │  │
  │  │ Settings     │        │                      │     │             │  │
  │  └──────────────┘        └──────────────────────┘     └─────────────┘  │
  └─────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
  ┌─────────────────────────────────────────────────────────────────────────┐
  │                        TECHNOLOGY FRAMEWORK                             │
  │                                                                         │
  │   ┌──────────────┐   ┌──────────────┐   ┌──────────────────────────┐   │
  │   │   FRONTEND   │   │   BACKEND    │   │       INTEGRATION        │   │
  │   │              │   │              │   │                          │   │
  │   │ React Native │   │ Laravel 12   │   │ Firebase FCM             │   │
  │   │ (Mobile App) │   │ (PHP 8.2+)   │   │ (Push Notifications)     │   │
  │   │              │   │              │   │                          │   │
  │   │ Bootstrap 5  │   │ MySQL 8.0+   │   │ OpenStreetMap            │   │
  │   │ (Web UI)     │   │ (Database)   │   │ (Location Services)      │   │
  │   │              │   │              │   │                          │   │
  │   │ JavaScript   │   │ Sanctum      │   │ Gmail SMTP               │   │
  │   │ (ES6+)       │   │ (Auth/API)   │   │ (Email Notifications)    │   │
  │   └──────────────┘   └──────────────┘   └──────────────────────────┘   │
  └─────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
  ┌─────────────────────────────────────────────────────────────────────────┐
  │                          SYSTEM USERS                                   │
  │                                                                         │
  │   ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐    │
  │   │    CUSTOMER      │  │      STAFF       │  │   ADMIN/OWNER    │    │
  │   │                  │  │                  │  │                  │    │
  │   │ • Track orders   │  │ • Process orders │  │ • Manage all     │    │
  │   │ • Request pickup │  │ • Accept pickups │  │   branches       │    │
  │   │ • Make payments  │  │ • Update status  │  │ • View reports   │    │
  │   │ • Receive notifs │  │ • Verify payment │  │ • Manage staff   │    │
  │   │ • Rate orders    │  │ • View reports   │  │ • System config  │    │
  │   └──────────────────┘  └──────────────────┘  └──────────────────┘    │
  └─────────────────────────────────────────────────────────────────────────┘
```

## Summary

The WashBox Laundry Management System is built on a solid technical foundation combining proven frameworks, modern architectural patterns, and integrated third-party services. The Laravel backend provides robust API services and web interfaces, while the React Native mobile application delivers a seamless customer experience. Firebase FCM ensures reliable real-time notifications, and OpenStreetMap integration enables location-based pickup services. The MVC architecture, complemented by Repository, Observer, and Service Layer patterns, ensures the system is maintainable, scalable, and extensible to accommodate future enhancements such as AI-driven demand forecasting and IoT machine integration.