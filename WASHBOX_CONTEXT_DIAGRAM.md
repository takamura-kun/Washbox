# WashBox Context Diagram

## System Context Diagram Layout

```
┌────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                                                                                        │
│   ┌──────────────────────────┐                    ┌──────────────────────────┐                        │
│   │   Order Management       │                    │   Firebase Cloud         │                        │
│   │   - Inputs               │◄───────────────────┤   Messaging              │                        │
│   │   - Payment Info         │                    │   - Push Notifications   │                        │
│   │   - Order Confirmation   │                    │   - Device Tokens        │                        │
│   │                          │                    │   - FCM Events           │                        │
│   │ CASHIER/BRANCH STAFF     │                    │                          │                        │
│   │  (Point of Sale)         │                    │  EXTERNAL SYSTEM         │                        │
│   └──────────────────────────┘                    └──────────────────────────┘                        │
│          ▲ ▲ ▲                                            ▲ ▲                                         │
│          │ │ │                                            │ │                                         │
│          │ │ └──── Order Status                           │ └──── Notification Alerts                │
│          │ │       Receipt Data                           │       Push Events                        │
│          │ │       Payment Confirmation                   │       Authentication                    │
│          │ │                                              │                                         │
│          │ └──────── Order Details                        │                                         │
│          │           Transaction Logs                     │                                         │
│          │           Inventory Updates                    │                                         │
│          │                                                │                                         │
│          └────────────── Laundry Status                   │                                         │
│                          Bill Generation                  │                                         │
│                          Payment Records                  │                                         │
│                                                           │                                         │
│   ┌──────────────────────────────────────────────────────────────────────────────────────────┐      │
│   │                                                                                          │      │
│   │                    WASHBOX                                                               │      │
│   │              Laundry Management                                                          │      │
│   │                    Platform                                                              │      │
│   │                                                                                          │      │
│   │    • Order Management & Tracking                                                         │      │
│   │    • Payment Processing & Verification                                                   │      │
│   │    • Inventory Management                                                                │      │
│   │    • Route Optimization & Tracking                                                       │      │
│   │    • Customer & Branch Management                                                        │      │
│   │    • Analytics & Reporting                                                               │      │
│   │    • Financial Management                                                                │      │
│   │    • Notification Distribution                                                           │      │
│   │                                                                                          │      │
│   └──────────────────────────────────────────────────────────────────────────────────────────┘      │
│          ▲ ▲ ▲ ▲                                        ▲ ▲                                         │
│          │ │ │ │                                        │ │                                         │
│          │ │ │ │                                        │ │                                         │
│   ┌──────┘ │ │ └────────────────────────┐      ┌───────┘ └────────────┐                           │
│   │        │ │                          │      │                      │                           │
│   │        │ │                          │      │                      │                           │
│   │   ┌────┘ │                          │      │            ┌─────────┴─────────┐                 │
│   │   │      │                          │      │            │                   │                 │
│   │   │      │                          ▼      ▼            ▼                   ▼                 │
│   │   │      │        ┌──────────────────────────────┐   ┌────────────────────────────┐           │
│   │   │      │        │   GCash Payment System       │   │   OSRM / Google Maps       │           │
│   │   │      │        │   - QR Code Generation       │   │   - Route Optimization     │           │
│   │   │      │        │   - Payment Verification     │   │   - Distance Calculation   │           │
│   │   │      │        │   - Transaction Records      │   │   - GPS Coordinates        │           │
│   │   │      │        │                              │   │   - Delivery Tracking      │           │
│   │   │      │        │   EXTERNAL SYSTEM            │   │                            │           │
│   │   │      │        │   (Payment Gateway)          │   │   EXTERNAL SYSTEM          │           │
│   │   │      │        └──────────────────────────────┘   │   (Location Services)      │           │
│   │   │      │                                            └────────────────────────────┘           │
│   │   │      │                                                                                    │
│   │   ▼      │                                            Payment Proof Data                       │
│   │ ┌────────┴─────────┐                                  Pickup Coordinates                      │
│   │ │   CUSTOMER       │        Order Requests            Delivery Routes                         │
│   │ │   - Order Details◄────────Pickup Requests           Real-Time Location                      │
│   │ │   - Personal Info │        Payment Info                                                     │
│   │ │   - Addresses     │        Order Status                                                     │
│   │ │   - Payment Info  │        Notifications                                                    │
│   │ │                  │        Ratings & Feedback                                               │
│   │ │                  │                                                                         │
│   │ │  EXTERNAL ENTITY │                                                                         │
│   │ │  (Mobile App)    │                                                                         │
│   │ └──────────────────┘                                                                         │
│   │        ▲ ▲                                                                                    │
│   │        │ │                                                                                    │
│   │        │ │ Invoice/Receipt                                                                    │
│   │        │ │ Order Tracking                                                                     │
│   │        │ │                                                                                    │
│   │   ┌────┘ │                                                                                    │
│   │   │      │                                                                                    │
│   │   │      └────────────────────┐                                                               │
│   │   │                           │                                                               │
│   │   ▼                           ▼                                                               │
│   │ ┌──────────────────┐    ┌──────────────────────────┐                                         │
│   │ │  ADMIN USERS     │    │  BRANCH MANAGER/STAFF    │                                         │
│   │ │  - Dashboard     │    │  - Branch Operations     │                                         │
│   │ │  - Reports       │◄───┤  - Order Processing      │                                         │
│   │ │  - Settings      │    │  - Inventory Management  │                                         │
│   │ │  - Analytics     │    │  - Financial Tracking    │                                         │
│   │ │  - User Mgmt     │    │  - Staff Coordination    │                                         │
│   │ │                  │    │  - Payment Verification  │                                         │
│   │ │  EXTERNAL ENTITY │    │                          │                                         │
│   │ │  (Web Dashboard) │    │  EXTERNAL ENTITY         │                                         │
│   │ │                  │    │  (Web/Portal)            │                                         │
│   │ └──────────────────┘    └──────────────────────────┘                                         │
│   │        ▲                          ▲                                                           │
│   │        │                          │                                                           │
│   │        └──── System Config        │                                                           │
│   │              Settings Data        │                                                           │
│   │              Activity Logs         │                                                           │
│   │              Financial Reports     │                                                           │
│   │              Branch Performance    │                                                           │
│   │                                    │                                                           │
│   │                          Order Trends                                                         │
│   │                          Inventory Updates                                                    │
│   │                          Branch Reports                                                       │
│   │                          Payment Status                                                       │
│   │                                                                                               │
│   └───────────────────────────────────────────────────────────────────────────────────────────────┘
│
│   ┌──────────────────────┐
│   │  DELIVERY PERSONNEL  │         Pickup Requests
│   │  - GPS Tracking      │──────►  Real-Time Location
│   │  - Proof Photos      │◄────    Job Assignments
│   │  - Route Updates     │         Completion Status
│   │                      │
│   │  EXTERNAL ENTITY     │
│   │  (Mobile App)        │
│   └──────────────────────┘
│         ▲ ▲ ▲
│         │ │ │
│         │ │ └─── Route Optimization
│         │ │       Delivery Coordinates
│         │ │       Traffic Updates
│         │ │
│         │ └───── Order Assignments
│         │         Current Routes
│         │         ETA Updates
│         │
│         └────── Location Changes
│                 Delivery Proof
│                 Pickup Confirmation
│
└────────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Data Flow Summary by Actor

### **CUSTOMER (Mobile App User)**
**Inputs to System:**
- Order Details (services, add-ons, quantity, preferences)
- Pickup/Delivery Requests (address, time preferences)
- Payment Information (GCash QR payment proof)
- Ratings & Feedback (service quality, branch feedback)
- Saved Addresses & Payment Methods

**Outputs from System:**
- Order Status & Tracking updates
- Real-time notification alerts
- Invoice & Receipt
- Pickup status with estimated time
- Promotional offers & discounts
- Branch location & operating hours

---

### **CASHIER / BRANCH STAFF (Web Dashboard)**
**Inputs to System:**
- Order Management (process laundry items)
- Payment Confirmation
- Order Status Updates
- Inventory Adjustments
- Customer Interactions

**Outputs from System:**
- Pending Orders Queue
- Order Details & Instructions
- Payment Status
- Customer Information
- Inventory Status
- Notification Alerts (new orders, pickups)
- Transaction Records

---

### **DELIVERY PERSONNEL (Mobile App)**
**Inputs to System:**
- Pickup Requests Acceptance/Rejection
- Real-time GPS Location Updates
- Proof of Pickup Photos
- Delivery Proof Photos
- Route Completion Status
- Customer Signature/Confirmation

**Outputs from System:**
- Route Assignments
- Optimized delivery routes
- Order details for each stop
- Customer contact information
- Current GPS coordinates
- ETA calculations
- Real-time traffic updates

---

### **ADMIN USERS (Web Dashboard)**
**Inputs to System:**
- System Configuration & Settings
- User Access Control
- Branch Management
- Financial Configurations
- Promotion Creation & Management
- Inventory Purchasing & Distribution

**Outputs from System:**
- System Usage Reports
- User Activity Logs
- Configuration Confirmations
- Analytics & KPI Dashboard
- Financial Reports (P&L, Cash Flow)
- Order Management Analytics
- Staff Performance Metrics
- Branch Performance Metrics

---

### **BRANCH MANAGER (Web Portal)**
**Inputs to System:**
- Inventory Updates (stock levels, adjustments)
- Branch Settings & Operations
- Staff Management (attendance, leave)
- Payment Verification Approvals
- Local Financial Tracking
- Pricing Adjustments (branch-specific)

**Outputs from System:**
- Order Trends & Analytics
- Inventory Status (low stock alerts)
- Branch Performance Reports
- Financial Summary
- Staff Schedules & Attendance
- Payment Verification Queue
- Customer Issues & Complaints

---

### **EXTERNAL SYSTEM: Firebase Cloud Messaging**
**Inputs from System:**
- Push Notification Events
- FCM Device Tokens registration
- User Preferences (notification settings)

**Outputs to System:**
- Push delivery status
- Device token validation
- Notification engagement metrics

---

### **EXTERNAL SYSTEM: GCash Payment Gateway**
**Inputs from System:**
- Payment proof verification requests
- Transaction records
- QR code generation for payment

**Outputs to System:**
- Payment status confirmation
- Transaction verification result
- Payment method validation

---

### **EXTERNAL SYSTEM: OSRM / Google Maps**
**Inputs from System:**
- Route calculation requests (origin/destination coordinates)
- Multi-stop route optimization requests
- Distance & duration queries
- Address geocoding requests

**Outputs to System:**
- Optimal route coordinates
- Distance & duration estimates
- Geolocation data
- Address suggestions
- Traffic-adjusted routes

---

## Key Data Exchanged

| Data Flow | Source | Destination | Data Type |
|-----------|--------|-------------|-----------|
| **Order Creation** | Customer | System | Order details, services, add-ons |
| **Order Confirmation** | System | Customer | Order ID, estimated time |
| **Status Updates** | System | Customer/Staff | Order status, location, ETA |
| **Payment Proof** | Customer | System | Photo, amount, payment method |
| **Payment Verification** | System | Admin/Manager | Proof review queue, approval status |
| **Route Optimization** | System | OSRM/Maps | Pickup locations, constraints |
| **Delivery Routes** | OSRM/Maps | System | Optimized route, ETA, distance |
| **GPS Tracking** | Delivery Staff | System | Real-time coordinates, status |
| **Notifications** | System | Firebase | Event data, user ID, message content |
| **Push Notifications** | Firebase | Mobile Devices | Notification alerts |
| **Branch Reports** | System | Manager | Performance metrics, analytics |
| **Financial Reports** | System | Admin | Revenue, expenses, cash flow |
| **Inventory Updates** | Manager | System | Stock adjustments, transfers |
| **Pricing Data** | System | Customer | Service rates, delivery fees |

---

## System Boundaries

**Inside WashBox System:**
- Order Management & Processing
- Customer & Branch Management
- Inventory Tracking
- Financial Management & Accounting
- Notification Distribution
- Analytics & Reporting
- Route Optimization
- Payment Processing (Verification)

**Outside System (External):**
- Customer Mobile Devices
- Cashier/Staff Web Browsers
- Delivery Personnel Mobile Devices
- Admin/Manager Web Portals
- Firebase (Push Notifications)
- GCash (Payment Provider)
- OSRM/Google Maps (Routing Services)

---

## Notes
- All external entities communicate with the system through secure APIs
- Data flows are bidirectional (request/response)
- Real-time updates via WebSockets for tracking
- Mobile apps use Sanctum authentication tokens
- Admin/Branch dashboards use session-based authentication
