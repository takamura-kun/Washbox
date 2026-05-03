# WashBox System - Architecture Diagrams

**System:** WashBox Laundry Management Platform  
**Version:** 1.0  
**Last Updated:** May 2026  
**Scope:** Complete system architecture with data flows and business processes

---

## Table of Contents

1. [Context Diagram](#1-context-diagram)
2. [Data Flow Diagram (DFD Level 0)](#2-data-flow-diagram-dfd-level-0)
3. [System Flowchart](#3-system-flowchart)
4. [Program Flowchart (Highlights)](#4-program-flowchart-highlights)

---

## 1. Context Diagram

### System Boundary Overview

This diagram shows the WashBox platform at the center, interacting with external systems and user actors. It defines the system boundary and primary data flows.

```mermaid
graph TB
    subgraph External["External Systems & Services"]
        FCM["🔔 Firebase Cloud Messaging<br/>(Push Notifications)"]
        MAPS["📍 OSRM / Google Maps<br/>(Route Optimization)"]
        GCASH["💳 GCash Payment Gateway<br/>(Payment Verification)"]
        MAIL["📧 Email Service<br/>(Notifications)"]
    end

    subgraph Users["User Actors"]
        CUSTOMER["👤 Customer<br/>(Mobile App)"]
        ADMIN["👨‍💼 Admin User<br/>(Web Dashboard)"]
        BRANCH["🏪 Branch Manager/Staff<br/>(Branch Portal)"]
        DELIVERY["🚗 Delivery/Pickup Staff<br/>(Mobile Tracking)"]
    end

    subgraph Core["WashBox Core System"]
        WASHBOX["<b>WashBox Platform</b><br/>━━━━━━━━━━━━━━━━<br/>✓ Order Management<br/>✓ Payment Processing<br/>✓ Inventory Control<br/>✓ Route Optimization<br/>✓ Financial Management<br/>✓ Analytics & Reporting<br/>✓ Notification Engine"]
    end

    CUSTOMER -->|Pickup Request| WASHBOX
    CUSTOMER -->|Payment Proof| WASHBOX
    CUSTOMER -->|Location Data| WASHBOX
    WASHBOX -->|Order Status| CUSTOMER
    WASHBOX -->|Notifications| FCM
    FCM -->|Push Alert| CUSTOMER

    ADMIN -->|System Config| WASHBOX
    ADMIN -->|Reports Request| WASHBOX
    WASHBOX -->|Analytics Data| ADMIN

    BRANCH -->|Order Processing| WASHBOX
    BRANCH -->|Payment Verification| WASHBOX
    BRANCH -->|Inventory Update| WASHBOX
    WASHBOX -->|Branch Queue| BRANCH
    WASHBOX -->|Notifications| MAIL

    DELIVERY -->|Location Updates| WASHBOX
    DELIVERY -->|Pickup Completion| WASHBOX
    WASHBOX -->|Route Assignment| DELIVERY

    WASHBOX -->|Coordinate Request| MAPS
    MAPS -->|Route/Distance| WASHBOX

    WASHBOX -->|Verify Payment| GCASH
    GCASH -->|Payment Status| WASHBOX
```

### Key Interactions:
- **Customers** submit pickup requests, payment proofs, and location data
- **Admin** manages system configuration and requests reports
- **Branch Staff** processes orders, verifies payments, and manages inventory
- **Delivery Staff** receive route assignments and submit location updates
- **External Services** provide push notifications, mapping, payments, and email

---

## 2. Data Flow Diagram (DFD Level 0)

### Process Decomposition

This DFD shows the main processes and data stores, breaking down the system into 10 major functional areas.

```mermaid
graph TB
    CUST["👤 Customer"]
    ADMIN["👨‍💼 Admin"]
    STAFF["🏪 Branch Staff"]
    DRIVER["🚗 Driver"]
    FCM["🔔 FCM"]
    MAPS["📍 Maps"]
    GCASH["💳 GCash"]

    P1["<b>1.0</b><br/>Account &<br/>Authentication"]
    P2["<b>2.0</b><br/>Service &<br/>Promotion Mgmt"]
    P3["<b>3.0</b><br/>Pickup Request<br/>Management"]
    P4["<b>4.0</b><br/>Laundry Order<br/>Processing"]
    P5["<b>5.0</b><br/>Payment<br/>Verification"]
    P6["<b>6.0</b><br/>Inventory &<br/>Stock Mgmt"]
    P7["<b>7.0</b><br/>Route &<br/>Location Tracking"]
    P8["<b>8.0</b><br/>Financial<br/>Tracking"]
    P9["<b>9.0</b><br/>Reporting &<br/>Analytics"]
    P10["<b>10.0</b><br/>Notification<br/>Distribution"]

    D1["💾<br/>Users &<br/>Customers"]
    D2["💾<br/>Branches &<br/>Services"]
    D3["💾<br/>Pickup<br/>Requests"]
    D4["💾<br/>Laundries &<br/>Status"]
    D5["💾<br/>Payment<br/>Records"]
    D6["💾<br/>Inventory<br/>Items"]
    D7["💾<br/>Promotions &<br/>Usage"]
    D8["💾<br/>Financial<br/>Records"]
    D9["💾<br/>Activity Logs &<br/>Notifications"]

    CUST -->|Register/Login| P1
    P1 -->|Profile Data| D1
    D1 -->|User Info| P1
    P1 -->|Authenticated| CUST

    ADMIN -->|Create Service| P2
    ADMIN -->|Add Promotion| P2
    P2 -->|Service Data| D2
    P2 -->|Promo Rules| D7
    D2 -->|Available Services| CUST
    D7 -->|Active Promos| CUST

    CUST -->|Submit Pickup| P3
    STAFF -->|Accept Pickup| P3
    P3 -->|Pickup Data| D3
    D3 -->|Pickup Status| DRIVER
    DRIVER -->|Update Location| P7
    P7 -->|Coordinates| MAPS
    MAPS -->|Route/ETA| P7
    P7 -->|Location Data| D3
    D3 -->|Delivery Info| CUST

    STAFF -->|Create Laundry| P4
    P4 -->|Weight/Service| P6
    P6 -->|Deduct Stock| D6
    P4 -->|Laundry Record| D4
    D4 -->|Apply Promo| P4
    D7 -->|Promo Rules| P4
    P4 -->|Deduction| D8
    P4 -->|Status Updates| CUST

    CUST -->|Upload Proof| P5
    STAFF -->|Verify Payment| P5
    P5 -->|Verify via| GCASH
    GCASH -->|Payment Status| P5
    P5 -->|Payment Record| D5
    D5 -->|Link to Laundry| D4
    P5 -->|Confirmation| CUST

    STAFF -->|Stock Requests| P6
    ADMIN -->|Inventory Setup| P6
    P6 -->|Stock Data| D6
    D6 -->|Available Items| P4
    D6 -->|Stock Report| P9

    ADMIN -->|Financial Review| P8
    P8 -->|Income/Expense| D8
    D8 -->|Financial Data| P9
    P9 -->|Dashboards| ADMIN

    ADMIN -->|Report Request| P9
    STAFF -->|Branch Report| P9
    D4 -->|Laundry Stats| P9
    D5 -->|Payment Stats| P9
    D6 -->|Inventory Stats| P9
    D8 -->|Financial Stats| P9
    P9 -->|Analytics| ADMIN
    P9 -->|Branch Metrics| STAFF

    P9 -->|Notification Event| P10
    P10 -->|Alert Payload| FCM
    FCM -->|Push Notification| CUST
    P10 -->|Email Alert| CUST
    D9 -->|Activity Log| P10
    P10 -->|Log Entry| D9
```

### Process Summary:

| ID | Process | Input | Output |
|----|---------| ------|--------|
| 1.0 | Account & Authentication | User credentials | User session |
| 2.0 | Service & Promotion Management | Service details, Promo rules | Service/Promo records |
| 3.0 | Pickup Request Management | Pickup submission | Assigned route |
| 4.0 | Laundry Order Processing | Service type, Weight | Laundry record with pricing |
| 5.0 | Payment Verification | Payment proof | Verified payment |
| 6.0 | Inventory & Stock Management | Stock data, Service requirements | Deducted inventory |
| 7.0 | Route & Location Tracking | Location updates | Optimized routes |
| 8.0 | Financial Tracking | Transaction data | Financial records |
| 9.0 | Reporting & Analytics | Raw data | Dashboards & reports |
| 10.0 | Notification Distribution | Events | Sent notifications |

---

## 3. System Flowchart

### End-to-End Business Process

This comprehensive flowchart shows the complete customer journey from order initiation to completion, including all major decision points and system interactions.

```mermaid
flowchart TD
    START([Customer Initiates Order])
    
    subgraph Auth["Authentication & Setup"]
        A1{Logged In?}
        A2["Customer Registration/<br/>Login via Mobile App"]
        A3["Verify OTP/Email"]
        A4{Success?}
    end

    subgraph Browse["Service Discovery"]
        B1["Browse Services &<br/>View Promotions"]
        B2{Service<br/>Available?}
        B3["View Branch Info &<br/>Pricing"]
    end

    subgraph PickupReq["Pickup Request"]
        P1["Submit Pickup Request"]
        P2["Select Date/Time/Location"]
        P3["Choose Service & Add-ons"]
        P4["Save Request to Database"]
        P5["Notify Admin/Branch Staff"]
        P6{Pickup Accepted<br/>within SLA?}
        P7["Reject & Offer Alternative"]
    end

    subgraph Pickup["Pickup Execution"]
        PU1["Assign Delivery Staff"]
        PU2["Generate Optimized Route"]
        PU3["Send Driver Assignment"]
        PU4["Track Location in Real-time"]
        PU5["Arrive at Customer Location"]
        PU6["Capture Pickup Proof"]
        PU7["Mark as Picked Up"]
    end

    subgraph Laundry["Laundry Processing"]
        L1["Receive at Branch"]
        L2["Weigh & Sort Items"]
        L3["Assign Service Type"]
        L4["Calculate Pricing"]
        L5{Apply Promotion?}
        L6["Deduct Inventory Stock"]
        L7["Create Laundry Record"]
        L8["Update Status: Processing"]
        L9["Process Items"]
        L10["Mark Ready for Delivery"]
    end

    subgraph Payment["Payment Processing"]
        PAY1{Payment Required?}
        PAY2["Generate Invoice"]
        PAY3["Show Payment Methods"]
        PAY4["Customer Uploads<br/>Proof Image"]
        PAY5["Branch Staff<br/>Verifies Proof"]
        PAY6{Valid Proof?}
        PAY7["Notify Customer<br/>to Re-upload"]
        PAY8["Confirm Payment"]
        PAY9["Update Laundry Status: Paid"]
    end

    subgraph Delivery["Delivery/Completion"]
        D1["Prepare Laundry<br/>for Delivery"]
        D2["Assign Delivery Staff"]
        D3["Update Status: Out for Delivery"]
        D4["Track Delivery"]
        D5["Deliver to Customer"]
        D6["Capture Delivery Proof"]
        D7["Get Customer Signature/Confirmation"]
    end

    subgraph Complete["Completion & Follow-up"]
        C1["Mark as Completed"]
        C2["Send Receipt to Customer"]
        C3["Prompt Customer Rating"]
        C4["Update Analytics &<br/>Activity Logs"]
        C5["Generate Financial Record"]
        C6["Update Branch Dashboard"]
    end

    subgraph Notify["Notifications Throughout"]
        N1["Send Pickup Confirmation"]
        N2["Send Status Updates"]
        N3["Send Payment Reminder"]
        N4["Send Delivery Notification"]
        N5["Send Completion Alert"]
    end

    START --> Auth
    Auth --> A1
    A1 -->|No| A2 --> A3 --> A4
    A1 -->|Yes| A4
    A4 -->|Failed| Auth
    A4 -->|Success| Browse
    
    Browse --> B1 --> B2
    B2 -->|No| B1
    B2 -->|Yes| B3 --> PickupReq
    
    PickupReq --> P1 --> P2 --> P3 --> P4 --> P5 --> P6
    P6 -->|No| P7 --> PickupReq
    P6 -->|Yes| Pickup
    
    Pickup --> PU1 --> PU2 --> PU3 --> PU4 --> PU5 --> PU6 --> PU7 --> Laundry
    
    Laundry --> L1 --> L2 --> L3 --> L4 --> L5
    L5 -->|Yes| L6
    L5 -->|No| L7
    L6 --> L7 --> L8 --> L9 --> L10
    
    L10 --> Payment
    Payment --> PAY1
    PAY1 -->|No| PAY9
    PAY1 -->|Yes| PAY2 --> PAY3 --> PAY4 --> PAY5 --> PAY6
    PAY6 -->|Invalid| PAY7 --> PAY4
    PAY6 -->|Valid| PAY8 --> PAY9
    
    PAY9 --> Delivery
    Delivery --> D1 --> D2 --> D3 --> D4 --> D5 --> D6 --> D7
    
    D7 --> Complete
    Complete --> C1 --> C2 --> C3 --> C4 --> C5 --> C6
    
    P1 -.->|Async| Notify
    P5 -.->|Async| Notify
    PAY1 -.->|Async| Notify
    D1 -.->|Async| Notify
    C1 -.->|Async| Notify
    
    C6 --> END([Order Complete])
```

### Key Stages:

1. **Authentication & Setup** - User login/registration
2. **Service Discovery** - Browse available services
3. **Pickup Request** - Schedule pickup with SLA
4. **Pickup Execution** - Real-time tracking and proof capture
5. **Laundry Processing** - Weight, pricing, inventory deduction
6. **Payment Processing** - Proof verification with retry logic
7. **Delivery** - Assignment, tracking, delivery proof
8. **Completion** - Receipt, rating, financial recording

---

## 4. Program Flowchart (Highlights)

### Key Functional Flows

#### 4.1 Payment Verification Process

Detailed flow for handling payment proof uploads with multi-step validation.

```mermaid
flowchart TD
    START([Payment Proof Submitted])
    
    P1["Receive Image File"]
    P2{File Valid?<br/>Size, Format}
    P3["Reject & Notify<br/>Invalid Format"]
    
    P4["Store Image in<br/>Storage Service"]
    P5["Extract Image<br/>Metadata"]
    
    P6["Match with<br/>GCash Reference"]
    P7{Reference<br/>Found?}
    P8["Check Transaction<br/>Amount"]
    
    P9{Amount<br/>Matches?}
    P10["Check QR Code<br/>Validity"]
    
    P11{QR Valid &<br/>Not Expired?}
    P12["Cross-verify<br/>Timestamp"]
    
    P13{Timestamp<br/>Valid?}
    P14["Mark as VERIFIED"]
    P15["Update Laundry<br/>Status: PAID"]
    
    P16["Send Confirmation<br/>Notification"]
    P17["Update Payment<br/>Record in DB"]
    
    P18["Log Transaction<br/>in Activity Logs"]
    P19["Update Financial<br/>Dashboard"]
    
    P20["Generate Receipt"]
    P21["Send to Customer<br/>& Branch"]
    
    END([Verification Complete])
    
    START --> P1 --> P2
    P2 -->|No| P3 --> END
    P2 -->|Yes| P4 --> P5 --> P6 --> P7
    P7 -->|No| P8 --> P9
    P7 -->|Yes| P9
    P9 -->|No| P3
    P9 -->|Yes| P10 --> P11
    P11 -->|No| P3
    P11 -->|Yes| P12 --> P13
    P13 -->|No| P3
    P13 -->|Yes| P14 --> P15 --> P16 --> P17 --> P18 --> P19 --> P20 --> P21 --> END
```

---

#### 4.2 Inventory Stock Deduction Flow

Process for managing inventory when a laundry order is created.

```mermaid
flowchart TD
    START([Laundry Order Created])
    
    I1["Get Service Type"]
    I2["Fetch Required Items<br/>from Service_Supplies"]
    I3["Loop Through Each Item"]
    
    I4["Check Current Stock<br/>in Branch"]
    I5{Stock Available?}
    I6["Cancel Order &<br/>Notify Customer"]
    
    I7["Calculate Quantity<br/>Needed"]
    I8["Check Reorder Point"]
    I9{Stock Below<br/>Reorder Point?}
    I10["Create Stock Alert<br/>for Branch Manager"]
    
    I11["Deduct from<br/>Branch Stock"]
    I12["Deduct from<br/>Central Stock"]
    I13["Create Stock<br/>History Record"]
    
    I14["Update Inventory<br/>Cost per Laundry"]
    I15["Calculate COGS"]
    I16["Update Financial<br/>Record"]
    
    I17["Next Item?"]
    I18["All Items<br/>Processed"]
    
    I19["Mark Laundry as<br/>STOCK_DEDUCTED"]
    I20["Update Dashboard"]
    
    END([Inventory Updated])
    
    START --> I1 --> I2 --> I3 --> I4 --> I5
    I5 -->|No| I6 --> END
    I5 -->|Yes| I7 --> I8 --> I9
    I9 -->|Yes| I10
    I9 -->|No| I11
    I10 --> I11 --> I12 --> I13 --> I14 --> I15 --> I16
    I16 --> I17
    I17 -->|Yes| I3
    I17 -->|No| I18 --> I19 --> I20 --> END
```

---

#### 4.3 Location Tracking & Route Optimization

Real-time GPS tracking and route management for delivery staff.

```mermaid
flowchart TD
    START([Pickup Assigned to Driver])
    
    L1["Get Customer Location<br/>from Pickup Request"]
    L2["Get Branch Location"]
    L3["Query Route Service<br/>OSRM/Google Maps"]
    
    L4["Receive Route Data"]
    L5["Extract Distance & ETA"]
    L6["Store Optimized Route<br/>in Database"]
    
    L7["Send Route to Driver<br/>Mobile App"]
    L8["Driver Starts Navigation"]
    
    L9["Real-time Location<br/>Updates via GPS"]
    L10["Validate Location Data"]
    L11["Update Current Position<br/>in Location_Updates Table"]
    
    L12["Calculate Progress<br/>to Destination"]
    L13{Arrived at<br/>Location?}
    L14["Continue Tracking"]
    
    L15["Notify Customer<br/>Driver Nearby"]
    L16["Capture Proof Photo"]
    L17["Get Customer<br/>Confirmation"]
    
    L18["Mark Pickup/Delivery<br/>as COMPLETED"]
    L19["Calculate Actual<br/>Time vs. ETA"]
    
    L20["Update Performance<br/>Metrics"]
    L21["Log in Activity<br/>Tracking"]
    
    END([Location Tracking Complete])
    
    START --> L1 --> L2 --> L3 --> L4 --> L5 --> L6 --> L7 --> L8
    L8 --> L9 --> L10 --> L11 --> L12 --> L13
    L13 -->|No| L14 --> L12
    L13 -->|Yes| L15 --> L16 --> L17 --> L18 --> L19 --> L20 --> L21 --> END
```

---

#### 4.4 Promotion Application Logic

Comprehensive validation of promotion codes and discount calculation.

```mermaid
flowchart TD
    START([Customer Applies Promotion])
    
    PR1["Get Promotion Code"]
    PR2["Search in Promotions<br/>Table"]
    PR3{Promotion<br/>Found?}
    PR4["Return Error<br/>Invalid Code"]
    
    PR5["Check Promotion<br/>Status: ACTIVE?"]
    PR6{Status<br/>Valid?}
    PR7["Return Error<br/>Expired/Inactive"]
    
    PR8["Check Date Range"]
    PR9{Within Valid<br/>Dates?}
    
    PR10["Get Applied Rules"]
    PR11["Check Usage Limit"]
    PR12{Max Uses<br/>Exceeded?}
    
    PR13["Check Customer<br/>Usage"]
    PR14{Customer<br/>Used Before?}
    PR15["Check Per-Customer<br/>Limit"]
    
    PR16["Check Service<br/>Category Match"]
    PR17{Service<br/>Allowed?}
    
    PR18["Check Min/Max<br/>Weight Requirements"]
    PR19{Weight<br/>Eligible?}
    
    PR20["Calculate Discount"]
    PR21["Apply to Laundry<br/>Total"]
    
    PR22["Create Promotion<br/>Usage Record"]
    PR23["Store in DB"]
    PR24["Update Laundry<br/>Discount Field"]
    
    PR25["Recalculate Final<br/>Amount"]
    PR26["Send Discount<br/>Confirmation"]
    
    END([Promotion Applied])
    
    START --> PR1 --> PR2 --> PR3
    PR3 -->|No| PR4 --> END
    PR3 -->|Yes| PR5 --> PR6
    PR6 -->|No| PR7 --> END
    PR6 -->|Yes| PR8 --> PR9
    PR9 -->|No| PR7
    PR9 -->|Yes| PR11 --> PR12
    PR12 -->|Yes| PR4
    PR12 -->|No| PR13 --> PR14
    PR14 -->|Yes| PR15
    PR14 -->|No| PR15
    PR15 -->|Exceeded| PR4
    PR15 -->|OK| PR16 --> PR17
    PR17 -->|No| PR4
    PR17 -->|Yes| PR18 --> PR19
    PR19 -->|No| PR4
    PR19 -->|Yes| PR20 --> PR21 --> PR22 --> PR23 --> PR24 --> PR25 --> PR26 --> END
```

---

#### 4.5 Notification Distribution Pipeline

Multi-channel notification system with preferences and delivery tracking.

```mermaid
flowchart TD
    START([Event Triggered])
    
    N1["Determine Event Type"]
    N2{Event Category?}
    
    subgraph Events["Event Types"]
        N3["OrderCreated"]
        N4["PaymentReceived"]
        N5["StatusUpdated"]
        N6["PickupArranged"]
        N7["DeliveryScheduled"]
    end
    
    N8["Get Recipients:<br/>Customer, Admin, Staff"]
    N9["Get Notification<br/>Preferences"]
    
    N10{Customer Opted<br/>In?}
    N11["Skip Notification"]
    
    N12["Get Device Token<br/>from DB"]
    N13{Device Token<br/>Available?}
    
    N14["Format Message<br/>Payload"]
    N15["Translate to<br/>User Language"]
    N16["Personalize Content"]
    
    N17["Send to FCM<br/>Push Service"]
    N18{FCM<br/>Successful?}
    
    N19["Log Failure"]
    N20["Retry Queue"]
    
    N21["Update FCM_SENT_AT"]
    N22["Mark as PUSHED"]
    
    N23{Send Email<br/>Also?}
    N24["Format Email<br/>Template"]
    N25["Send via Mail<br/>Service"]
    
    N26["Create Notification<br/>Record in DB"]
    N27["Log in Activity<br/>Logs"]
    
    N28["Update Notification<br/>Dashboard"]
    
    END([Notification Sent])
    
    START --> N1 --> N2
    N2 --> N3 & N4 & N5 & N6 & N7
    N3 & N4 & N5 & N6 & N7 --> N8 --> N9 --> N10
    N10 -->|No| N11 --> END
    N10 -->|Yes| N12 --> N13
    N13 -->|No| N11
    N13 -->|Yes| N14 --> N15 --> N16 --> N17 --> N18
    N18 -->|No| N19 --> N20
    N18 -->|Yes| N21 --> N22
    N20 --> N23
    N22 --> N23
    N23 -->|Yes| N24 --> N25
    N23 -->|No| N26
    N25 --> N26 --> N27 --> N28 --> END
```

---

## System Architecture Summary

### Core Components

| Component | Type | Responsibility |
|-----------|------|-----------------|
| **Mobile App** | Client | Customer pickup requests, payment proofs, location tracking |
| **Admin Dashboard** | Client | System configuration, reports, analytics |
| **Branch Portal** | Client | Order processing, payment verification, inventory management |
| **API Backend** | Server | REST API for all operations, business logic, data management |
| **Database** | Storage | PostgreSQL for persistent data storage |
| **Cache Layer** | Performance | Redis for session management and frequently accessed data |
| **FCM Integration** | External | Push notifications to mobile devices |
| **Payment Gateway** | External | GCash payment verification |
| **Maps Service** | External | Route optimization via OSRM/Google Maps |
| **File Storage** | External | Secure storage for payment proofs and documents |

### Data Flow Summary

```
Customer Mobile App
    ↓
REST API (Laravel)
    ↓
Database (PostgreSQL)
    ↓
Admin/Branch Dashboards
    ↓
Analytics & Reporting
    ↓
Notifications via FCM/Email
```

### Key Integration Points

1. **FCM** - Push notifications for order updates
2. **GCash** - Payment verification and confirmation
3. **OSRM/Google Maps** - Route optimization and ETA calculation
4. **Email Service** - Transactional and promotional emails
5. **File Storage** - Secure proof image storage

---

## Data Stores

### Master Data
- Users, Customers, Branches, Services, Categories

### Transactional Data
- PickupRequests, Laundries, Payments, Promotions

### Operational Data
- LocationUpdates, InventoryItems, BranchStocks, PaymentProofs

### Analytical Data
- FinancialTransactions, ActivityLogs, Reports, Analytics

---

**End of Architecture Diagrams Document**
