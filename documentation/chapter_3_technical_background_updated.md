# CHAPTER III: TECHNICAL BACKGROUND

This section discusses and presents different sets of diagrams and charts pertaining to the WashBox Laundry Management System (WLMS) that is being developed by the proponents. The chapter covers the technicality of the project, details of the technologies to be used, how the project will work, and the theoretical/conceptual framework that guides the system development.

## Technicality of the Project

### System Architecture Overview

The WashBox Laundry Management System employs a modern three-tier architecture designed to handle multi-branch laundry operations across Sibulan, Siaton, and Bais City locations in Negros Oriental. The system integrates web-based administrative interfaces with mobile customer applications through a centralized backend infrastructure.

The architecture consists of three primary layers: the presentation layer comprising both web and mobile interfaces, the business logic layer powered by Laravel framework, and the data layer utilizing MySQL database management. This separation ensures scalability, maintainability, and efficient resource management across all branch operations.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    WASHBOX LAUNDRY MANAGEMENT SYSTEM                        │
│                          SYSTEM ARCHITECTURE                               │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                           PRESENTATION LAYER                               │
├─────────────────────────────────┬───────────────────────────────────────────┤
│        WEB INTERFACE            │           MOBILE APPLICATION              │
│                                 │                                           │
│  ┌─────────────────────────────┐│    ┌─────────────────────────────────────┐│
│  │   Laravel Blade Dashboard   ││    │      React Native (Expo)            ││
│  │   Bootstrap 5 Framework     ││    │      Customer Mobile App            ││
│  │                             ││    │                                     ││
│  │ • Branch Staff Interface    ││    │ • Customer Registration             ││
│  │ • Owner Multi-Branch View   ││    │ • Order Tracking                    ││
│  │ • Order Management          ││    │ • Pickup Requests                   ││
│  │ • Pricing Configuration     ││    │ • Push Notifications                ││
│  │ • Route Planning            ││    │ • Payment Integration               ││
│  │ • Analytics Dashboard       ││    │ • Location Services                 ││
│  └─────────────────────────────┘│    └─────────────────────────────────────┘│
└─────────────────────────────────┴───────────────────────────────────────────┘
                               │
                    ┌──────────┼──────────┐
                    │          │          │
                    ▼          ▼          ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                          BUSINESS LOGIC LAYER                              │
├─────────────────────────────────────────────────────────────────────────────┤
│                        Laravel Framework (PHP 8.2+)                        │
│                         Apache Server via XAMPP                            │
│                                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │Controllers  │  │  Services   │  │   Models    │  │    Middleware       │ │
│  │             │  │             │  │ (Eloquent)  │  │                     │ │
│  │• AuthCtrl   │  │• FCMService │  │• Customer   │  │• Authentication     │ │
│  │• LaundryCtrl│  │• PricingSvc │  │• Laundry    │  │• Role-Based Access  │ │
│  │• PickupCtrl │  │• NotifSvc   │  │• Pickup     │  │• Branch Access      │ │
│  │• BranchCtrl │  │• LocationSvc│  │• Payment    │  │• API Rate Limiting  │ │
│  │• PaymentCtrl│  │• ReportSvc  │  │• Branch     │  │• CORS Policy        │ │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────────────┘ │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────────┐ │
│  │                      RESTful API Endpoints                              │ │
│  │                                                                         │ │
│  │ • /api/v1/auth/*          • /api/v1/laundries/*                         │ │
│  │ • /api/v1/customers/*     • /api/v1/pickups/*                           │ │
│  │ • /api/v1/branches/*      • /api/v1/notifications/*                     │ │
│  │ • /api/v1/services/*      • /api/v1/payments/*                          │ │
│  │ • /api/v1/promotions/*    • /api/v1/analytics/*                         │ │
│  └─────────────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                             DATA LAYER                                     │
├─────────────────────────────────────────────────────────────────────────────┤
│                           MySQL Database                                   │
│                                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │Core Tables  │  │Transaction  │  │System Tables│  │   Audit Tables      │ │
│  │             │  │   Tables    │  │             │  │                     │ │
│  │• customers  │  │• laundries  │  │• branches   │  │• activity_logs      │ │
│  │• users      │  │• pickups    │  │• services   │  │• notifications      │ │
│  │• branches   │  │• payments   │  │• promotions │  │• device_tokens      │ │
│  │• services   │  │• ratings    │  │• settings   │  │• status_histories   │ │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                         EXTERNAL SERVICES                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────────────────┐ │
│  │Firebase Cloud   │  │ OpenStreetMap   │  │      GCash Payment          │ │
│  │   Messaging     │  │   + Leaflet.js  │  │        Gateway              │ │
│  │                 │  │                 │  │                             │ │
│  │• Push Notifs    │  │• Map Tiles      │  │• QR Code Generation         │ │
│  │• FCM Tokens     │  │• Geocoding      │  │• Payment Verification       │ │
│  │• Device Mgmt    │  │• Route Planning │  │• Transaction Records        │ │
│  │• Delivery Status│  │• Location Track │  │• Receipt Generation         │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────┘
```

**Figure 1. WashBox Laundry Management System — System Architecture Diagram**

### Technical Complexity Analysis

The system addresses multiple technical challenges inherent in multi-branch service management operations. Data synchronization across three branch locations requires careful database design and transaction management to ensure consistency during concurrent operations. The integration of location-based services through OpenStreetMap APIs involves coordinate transformation, geocoding, and spatial data processing for accurate pickup request mapping.

Multi-platform communication between web interfaces and mobile applications demands robust API design with proper authentication, data validation, and error handling mechanisms. The automated notification system through Firebase FCM requires device token management, message queuing, and delivery status tracking across multiple user devices simultaneously.

## Details of the Technologies to be Used

The WashBox system utilizes a comprehensive technology stack designed for scalability, reliability, and maintainability across multi-branch operations.

### Backend Technologies

**Laravel Framework (PHP 8.2+)**
Laravel serves as the primary backend framework providing Model-View-Controller architecture, Eloquent ORM for database interactions, Artisan CLI for system maintenance, Blade templating for web interfaces, built-in authentication with role-based access control, and RESTful API development capabilities with JSON response formatting.

**MySQL Database Management System**
MySQL provides ACID compliance ensuring data integrity across multi-branch operations, optimized indexing for customer lookups and order tracking, foreign key constraints maintaining referential integrity, and transaction support for atomic operations during complex business processes like order creation with fee calculations.

### Frontend Technologies

**Bootstrap 5 Framework**
Bootstrap enables responsive grid system for flexible layout management across desktop, tablet, and mobile viewports, comprehensive component library for forms, tables, modals, and navigation, theme customization through CSS custom properties for dark/light mode implementation, and cross-browser compatibility ensuring consistent rendering.

**JavaScript (ES6+)**
Client-side scripting handles DOM manipulation for dynamic content updates, AJAX communication for asynchronous data exchange with backend APIs, theme management for dark/light mode switching with localStorage persistence, and form validation before server submission.

### Mobile Technologies

**React Native with Expo Framework**
Cross-platform mobile development utilizes Expo Router for file-based navigation with tab and stack layouts, React Navigation for bottom tabs and stack navigation between app screens, Fetch API for HTTP client communication with Laravel backend, expo-notifications for push notification integration and device token management, and react-native-maps with Leaflet for OpenStreetMap integration.

### Integration Technologies

**Firebase Cloud Messaging (FCM)**
Push notification service provides device token management with unique identifier assignment, message queuing for reliable delivery even when devices are offline, delivery analytics for tracking notification success rates and user engagement, and cross-platform support for unified notification system.

**OpenStreetMap APIs**
Location-based services offer geocoding services for address-to-coordinate conversion, reverse geocoding for coordinate-to-address conversion, tile rendering for map visualization with customizable styling and markers, and route calculation algorithms for pickup optimization.

## How the Project Will Work

### System Operation Workflow

The WashBox system operates through integrated workflows connecting customer interactions, staff operations, and administrative management across three branch locations.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    WASHBOX OPERATIONAL WORKFLOW                             │
└─────────────────────────────────────────────────────────────────────────────┘

    WALK-IN SERVICE PATHWAY                    MOBILE PICKUP SERVICE PATHWAY
    
┌─────────────────────────┐                 ┌─────────────────────────────────┐
│   Customer Arrives      │                 │   Customer Opens Mobile App     │
│   at Branch Counter     │                 │   Submits Pickup Request        │
└───────────┬─────────────┘                 └───────────┬─────────────────────┘
            │                                           │
            ▼                                           ▼
┌─────────────────────────┐                 ┌─────────────────────────────────┐
│   Staff Registers       │                 │   System Calculates             │
│   Customer in System    │                 │   Pickup/Delivery Fees          │
└───────────┬─────────────┘                 └───────────┬─────────────────────┘
            │                                           │
            ▼                                           ▼
┌─────────────────────────┐                 ┌─────────────────────────────────┐
│   Staff Creates         │                 │   Staff Receives Notification   │
│   Laundry Order         │                 │   on Web Dashboard              │
└───────────┬─────────────┘                 └───────────┬─────────────────────┘
            │                                           │
            ▼                                           ▼
┌─────────────────────────┐                 ┌─────────────────────────────────┐
│   System Calculates     │                 │   Staff Accepts Pickup          │
│   Pricing Automatically │                 │   Updates Status to "En Route"  │
└───────────┬─────────────┘                 └───────────┬─────────────────────┘
            │                                           │
            ▼                                           ▼
┌─────────────────────────┐                 ┌─────────────────────────────────┐
│   Customer Receives     │                 │   Staff Collects Laundry        │
│   Order Confirmation    │                 │   Creates Order in System       │
└───────────┬─────────────┘                 └───────────┬─────────────────────┘
            │                                           │
            └─────────────────┬─────────────────────────┘
                              │
                              ▼
            ┌─────────────────────────────────────────────┐
            │         UNIFIED ORDER PROCESSING            │
            │                                             │
            │  RECEIVED → WASHING → READY → PAID → DONE   │
            │                                             │
            │  Each status change triggers:               │
            │  • Customer push notification               │
            │  • Staff dashboard update                   │
            │  • Admin analytics update                   │
            └─────────────────────────────────────────────┘
```

**Figure 2. WashBox Operational Workflow — Integrated Service Pathways**

### Laundry Status Lifecycle Management

Every laundry order progresses through five defined status stages with automated notifications and staff coordination.

```
                    LAUNDRY ORDER STATUS LIFECYCLE
                    
  ┌──────────┐     ┌─────────┐     ┌───────┐     ┌──────┐     ┌───────────┐
  │ RECEIVED │────►│ WASHING │────►│ READY │────►│ PAID │────►│ COMPLETED │
  └────┬─────┘     └────┬────┘     └───┬───┘     └──┬───┘     └─────┬─────┘
       │                │                │           │               │
       ▼                ▼                ▼           ▼               ▼
   [Customer]       [Customer]       [Customer]   [Customer]    [Customer]
   [Staff]          [Staff]          [Staff]      [Staff]       [Staff]
   [Admin]                           [Admin]      [Admin]       [Admin]
       │                                                            │
       │ (cancellation possible at any stage)                      │
       ▼                                                            ▼
  ┌────────────┐                                            ┌─────────────┐
  │ CANCELLED  │                                            │   ARCHIVED  │
  └────────────┘                                            └─────────────┘
       │                                                            │
       ▼                                                            ▼
   [All Users]                                               [System Auto]
   
   UNCLAIMED TRACKING (status = ready, unpaid):
   ┌──────────────────────────────────────────────────────────────────┐
   │  Day 1-2: Staff warning notification                            │
   │  Day 3+:  Urgent staff + admin alert                           │
   │  Day 7+:  Auto-move to unclaimed_laundries table               │
   └──────────────────────────────────────────────────────────────────┘
```

**Figure 3. Laundry Order Status Lifecycle with Notification Triggers**

### Multi-Branch Management System

The system provides centralized management capabilities while maintaining branch-specific operations and data segregation.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    MULTI-BRANCH MANAGEMENT ARCHITECTURE                     │
└─────────────────────────────────────────────────────────────────────────────┘

                            OWNER DASHBOARD
                         (Centralized Control)
                    ┌─────────────────────────────┐
                    │  • All Branch Analytics     │
                    │  • Cross-Branch Reports     │
                    │  • Staff Performance        │
                    │  • Revenue Consolidation    │
                    │  • Route Optimization       │
                    └─────────────┬───────────────┘
                                  │
                    ┌─────────────┼─────────────┐
                    │             │             │
                    ▼             ▼             ▼
        ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐
        │  SIBULAN BRANCH │ │  SIATON BRANCH  │ │   BAIS BRANCH   │
        │                 │ │                 │ │                 │
        │ Staff Dashboard │ │ Staff Dashboard │ │ Staff Dashboard │
        │ • Local Orders  │ │ • Local Orders  │ │ • Local Orders  │
        │ • Local Pickups │ │ • Local Pickups │ │ • Local Pickups │
        │ • Local Reports │ │ • Local Reports │ │ • Local Reports │
        │ • Branch Config │ │ • Branch Config │ │ • Branch Config │
        └─────────────────┘ └─────────────────┘ └─────────────────┘
                    │             │             │
                    └─────────────┼─────────────┘
                                  │
                                  ▼
                    ┌─────────────────────────────┐
                    │     CENTRALIZED DATABASE    │
                    │                             │
                    │ • Branch-specific data      │
                    │ • Cross-branch analytics    │
                    │ • Unified customer records  │
                    │ • Consolidated reporting    │
                    └─────────────────────────────┘
```

**Figure 4. Multi-Branch Management and Data Flow Architecture**

## Theoretical/Conceptual Framework

The WashBox Laundry Management System is built upon established theoretical foundations that guide its design, implementation, and operational effectiveness.

### Technology Acceptance Model (TAM)

The Technology Acceptance Model, developed by Davis (1989), provides the theoretical foundation for understanding user adoption of the WashBox system. TAM posits that user acceptance is determined by perceived usefulness and perceived ease of use, both critical factors for successful implementation across diverse user groups.

For branch staff, perceived usefulness is addressed through automated pricing calculations, one-click status updates, elimination of manual logbooks, and real-time order tracking capabilities. Perceived ease of use is achieved through intuitive Blade web interfaces requiring minimal training, familiar Bootstrap components, and streamlined workflows that mirror existing manual processes.

For customers, perceived usefulness is delivered through real-time order tracking, automated push notifications replacing unreliable phone calls, convenient pickup scheduling, and transparent pricing information. Perceived ease of use is ensured through simplified React Native interfaces, straightforward registration processes, and map-based pickup submission requiring minimal technical knowledge.

### Service Operations Management Theory

Service Operations Management theory focuses on the systematic design, management, and continuous improvement of service processes to deliver efficient and high-quality services. This theoretical framework supports the WashBox system's structured approach to laundry service workflow management.

The system implements clearly defined service stages including customer registration, order placement, laundry processing, payment recording, and pickup scheduling. Through digital workflow management, staff can efficiently track order status, compute service costs, and manage pickup requests across multiple branches. This systematic approach improves operational efficiency, reduces service delays, and ensures consistent service delivery, ultimately enhancing customer satisfaction and business performance.

### Information Systems Theory

Information Systems Theory explains how organizations utilize information technology to collect, process, store, and distribute data for supporting decision-making and improving operational efficiency. This theoretical foundation underlies the WashBox system's comprehensive data management capabilities.

The system collects input data including customer information, laundry orders, payment records, and pickup requests. This data is processed through automated functions including order tracking, payment recording, status updates, and notification delivery. The processed information generates useful outputs such as digital receipts, service notifications, management reports, and analytics dashboards. This systematic data handling enhances operational efficiency, reduces manual errors, and enables data-driven decision making for business improvement.

### Model-View-Controller (MVC) Architecture Pattern

The MVC architectural pattern provides the structural foundation for the WashBox system, ensuring separation of concerns and maintainable code organization. The Model layer handles data management through Eloquent ORM with Customer, Laundry, Pickup, and Payment models encapsulating business logic and database interactions. The View layer manages presentation through Blade templates for web interfaces and JSON API responses for mobile applications. The Controller layer coordinates business logic through resource controllers handling HTTP requests and API controllers managing mobile app communication.

### Observer Pattern for Event-Driven Notifications

The Observer pattern implementation through Laravel's event and listener system enables automated notification delivery. Status change events are dispatched when laundry orders transition between stages, triggering notification listeners that send Firebase FCM push notifications to relevant users. This pattern ensures loose coupling between business logic and notification mechanisms while enabling extensible notification channels.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                  THEORETICAL / CONCEPTUAL FRAMEWORK                         │
│                  WashBox Laundry Management System                          │
└─────────────────────────────────────────────────────────────────────────────┘

  ┌─────────────────────────────────────────────────────────────────────────┐
  │                          THEORETICAL FOUNDATIONS                        │
  │                                                                         │
  │  ┌───────────────────┐  ┌───────────────────┐  ┌───────────────────┐   │
  │  │  TECHNOLOGY       │  │    SERVICE        │  │   INFORMATION     │   │
  │  │  ACCEPTANCE       │  │   OPERATIONS      │  │    SYSTEMS        │   │
  │  │  MODEL (TAM)      │  │   MANAGEMENT      │  │    THEORY         │   │
  │  │                   │  │                   │  │                   │   │
  │  │ • Perceived       │  │ • Workflow        │  │ • Data Collection │   │
  │  │   Usefulness      │  │   Optimization    │  │ • Information     │   │
  │  │ • Perceived       │  │ • Service Quality │  │   Processing      │   │
  │  │   Ease of Use     │  │ • Process         │  │ • Decision        │   │
  │  │ • User Adoption   │  │   Standardization │  │   Support         │   │
  │  └────────┬──────────┘  └────────┬──────────┘  └────────┬──────────┘   │
  │           │                      │                       │              │
  └───────────┼──────────────────────┼───────────────────────┼──────────────┘
              │                      │                       │
              └──────────────────────┼───────────────────────┘
                                     │
                                     ▼
  ┌─────────────────────────────────────────────────────────────────────────┐
  │                         SYSTEM IMPLEMENTATION                           │
  │                                                                         │
  │   INPUT                    PROCESS                      OUTPUT          │
  │  ┌──────────────┐        ┌──────────────────────┐     ┌─────────────┐  │
  │  │ Customer     │        │ 1. Authentication &  │     │ Digital     │  │
  │  │ Registration │───────►│    Authorization     │────►│ Receipts    │  │
  │  │              │        │                      │     │             │  │
  │  │ Laundry      │        │ 2. Order Processing  │     │ Real-time   │  │
  │  │ Items &      │───────►│    & Status Tracking │────►│ Status      │  │
  │  │ Specifications│        │                      │     │ Updates     │  │
  │  │              │        │ 3. Pickup Request    │     │             │  │
  │  │ Pickup       │───────►│    Management        │────►│ Push        │  │
  │  │ Locations    │        │                      │     │ Notifications│  │
  │  │              │        │ 4. Automated Pricing │     │             │  │
  │  │ Payment      │───────►│    & Fee Calculation │────►│ Analytics   │  │
  │  │ Information  │        │                      │     │ & Reports   │  │
  │  │              │        │ 5. Multi-Branch      │     │             │  │
  │  │ Staff        │───────►│    Coordination      │────►│ Management  │  │
  │  │ Operations   │        │                      │     │ Dashboards  │  │
  │  │              │        │ 6. Notification      │     │             │  │
  │  │ System       │───────►│    & Communication   │────►│ Performance │  │
  │  │ Configuration│        │                      │     │ Metrics     │  │
  │  └──────────────┘        └──────────────────────┘     └─────────────┘  │
  └─────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
  ┌─────────────────────────────────────────────────────────────────────────┐
  │                        TECHNICAL ARCHITECTURE                           │
  │                                                                         │
  │   ┌──────────────┐   ┌──────────────┐   ┌──────────────────────────┐   │
  │   │   FRONTEND   │   │   BACKEND    │   │       INTEGRATION        │   │
  │   │              │   │              │   │                          │   │
  │   │ React Native │   │ Laravel      │   │ Firebase FCM             │   │
  │   │ Mobile App   │   │ Framework    │   │ Push Notifications       │   │
  │   │              │   │              │   │                          │   │
  │   │ Bootstrap 5  │   │ MySQL        │   │ OpenStreetMap            │   │
  │   │ Web Interface│   │ Database     │   │ Location Services        │   │
  │   │              │   │              │   │                          │   │
  │   │ JavaScript   │   │ RESTful      │   │ GCash Payment            │   │
  │   │ ES6+         │   │ APIs         │   │ Gateway Integration      │   │
  │   └──────────────┘   └──────────────┘   └──────────────────────────┘   │
  └─────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
  ┌─────────────────────────────────────────────────────────────────────────┐
  │                          STAKEHOLDER BENEFITS                           │
  │                                                                         │
  │   ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐    │
  │   │    CUSTOMERS     │  │      STAFF       │  │   OWNERS/ADMIN   │    │
  │   │                  │  │                  │  │                  │    │
  │   │ • Real-time      │  │ • Automated      │  │ • Multi-branch   │    │
  │   │   order tracking │  │   workflows      │  │   oversight      │    │
  │   │ • Convenient     │  │ • Reduced        │  │ • Consolidated   │    │
  │   │   pickup service │  │   manual errors  │  │   reporting      │    │
  │   │ • Transparent    │  │ • Efficient      │  │ • Performance    │    │
  │   │   pricing        │  │   communication  │  │   analytics      │    │
  │   │ • Digital        │  │ • Streamlined    │  │ • Revenue        │    │
  │   │   receipts       │  │   operations     │  │   optimization   │    │
  │   └──────────────────┘  └──────────────────┘  └──────────────────┘    │
  └─────────────────────────────────────────────────────────────────────────┘
```

**Figure 5. Theoretical/Conceptual Framework of the WashBox System**

### Conceptual Framework Integration

The conceptual framework integrates theoretical foundations with practical implementation through the Input-Process-Output model. Input components include customer registration data, laundry specifications, pickup locations, payment information, staff operations, and system configurations. Processing components encompass authentication and authorization, order processing and status tracking, pickup request management, automated pricing and fee calculation, multi-branch coordination, and notification and communication systems.

Output components deliver digital receipts, real-time status updates, push notifications, analytics and reports, management dashboards, and performance metrics. This integrated approach ensures that theoretical principles translate into practical benefits for all stakeholders while maintaining system efficiency and user satisfaction.

## Summary

The WashBox Laundry Management System represents a comprehensive technical solution built on solid theoretical foundations and modern technology stack. The three-tier architecture ensures scalability and maintainability, while the integration of Laravel backend, React Native mobile application, and external services provides robust functionality for multi-branch operations.

The theoretical framework combining Technology Acceptance Model, Service Operations Management, and Information Systems Theory guides the system design to ensure user adoption, operational efficiency, and effective information management. The technical implementation through MVC architecture, Observer pattern, and Service Layer pattern ensures code maintainability and system extensibility.

This technical foundation supports the system's primary objectives of automating laundry operations, improving customer satisfaction, enhancing operational efficiency, and providing comprehensive management capabilities across WashBox's three branch locations in Negros Oriental.