# WASHBOX SYSTEM - UNIFIED DATA FLOW DIAGRAM (Single Diagram)

## Complete System Data Flow - All Components Aligned

```
┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│                              EXTERNAL ENTITIES (LEVEL 0)                                        │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                                 │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐    ┌──────────────┐                │
│  │   Customer   │    │    Admin     │    │   Branch     │    │    Driver    │                │
│  │              │    │    Staff     │    │    Staff     │    │              │                │
│  └──────────────┘    └──────────────┘    └──────────────┘    └──────────────┘                │
│         ▲                    ▲                    ▲                    ▲                        │
│         │                    │                    │                    │                        │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐    ┌──────────────┐                │
│  │   Firebase   │    │    GCash     │    │    Email     │    │  SMS/Twilio  │                │
│  │   (FCM)      │    │   Payment    │    │   Service    │    │   Gateway    │                │
│  └──────────────┘    └──────────────┘    └──────────────┘    └──────────────┘                │
│                                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────────────────────┘
                                          │
                                          ▼
┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│                        PROCESSES (LEVEL 1 - Core Functions)                                    │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                                 │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐    ┌──────────────┐                │
│  │ P1: Auth &   │    │ P2: Service  │    │ P3: Pickup   │    │ P4: Laundry  │                │
│  │ Profile Mgmt │    │ & Promo Mgmt │    │ Request Mgmt │    │ Order Mgmt   │                │
│  └──────────────┘    └──────────────┘    └──────────────┘    └──────────────┘                │
│         │                    │                    │                    │                        │
│         ▼                    ▼                    ▼                    ▼                        │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐    ┌──────────────┐                │
│  │ P5: Payment  │    │ P6: Inventory│    │ P7: Location │    │ P8: Status   │                │
│  │ Verification │    │ Management   │    │ Tracking     │    │ Notification │                │
│  └──────────────┘    └──────────────┘    └──────────────┘    └──────────────┘                │
│         │                    │                    │                    │                        │
│         └────────────────────┴────────────────────┴────────────────────┘                        │
│                                          │                                                      │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐    ┌──────────────┐                │
│  │ P9: Financial│    │P10: Reporting│    │P11: Device   │    │P12: Audit    │                │
│  │  Tracking    │    │& Analytics   │    │Token Mgmt    │    │Logging       │                │
│  └──────────────┘    └──────────────┘    └──────────────┘    └──────────────┘                │
│                                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────────────────────┘
                                          │
                                          ▼
┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│                           DATA STORES (Database Tables)                                        │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                                 │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐    ┌──────────────┐                │
│  │ D1: Customer │    │ D2: Service  │    │ D3: Pickup   │    │ D4: Laundry  │                │
│  │ Accounts     │    │ Items        │    │ Requests     │    │ Orders       │                │
│  └──────────────┘    └──────────────┘    └──────────────┘    └──────────────┘                │
│  (15,000 records)    (300 records)       (8,000/month)       (25,000/month)                   │
│                                                                                                 │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐    ┌──────────────┐                │
│  │ D5: Payment  │    │ D6: Inventory│    │ D7: Location │    │ D8: Promotions│               │
│  │ Proofs       │    │ Stock        │    │ Tracking     │    │ & Discounts   │               │
│  └──────────────┘    └──────────────┘    └──────────────┘    └──────────────┘                │
│  (20,000/month)      (3,000 items)       (2M+ daily pts)      (100 active)                   │
│                                                                                                 │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐                                    │
│  │ D9: Device   │    │D10: Financial│    │D11: Audit    │                                   │
│  │ Tokens (FCM) │    │ Transactions │    │ Logs         │                                   │
│  └──────────────┘    └──────────────┘    └──────────────┘                                   │
│  (50,000 tokens)     (100,000/month)     (10M+ records)                                      │
│                                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## DETAILED DATA FLOWS BY PROCESS

### PROCESS LAYER DETAILS

#### P1: Authentication & Profile Management
```
Input:  Customer → Login credentials, Profile updates
Process: Validate credentials, Hash passwords, Update profile, Create session
Output: Session tokens → Customer
        User data → D1 (Customer Accounts)
        Audit events → D11 (Audit Logs)
```

#### P2: Service & Promotion Management
```
Input:  Admin → Service definitions, Pricing, Promotions
Process: Validate service data, Calculate pricing tiers, Apply promotions
Output: Service catalog → Customer
        Promotion rules → P5 (Payment Verification)
        Service data → D2 (Services)
```

#### P3: Pickup Request Management
```
Input:  Customer → Pickup details (address, date, time)
        Branch Staff → Pickup assignment, status updates
Process: Validate address, Find nearest branch, Assign driver, Schedule
Output: Pickup confirmation → Customer & Driver
        Pickup data → D3 (Pickup Requests)
        Notification → Firebase (P8)
```

#### P4: Laundry Order Management
```
Input:  Pickup Request → Items for laundry
        Admin → Pricing updates, Service changes
Process: Calculate pricing, Apply promotions, Create order, Schedule delivery
Output: Order details → Customer
        Laundry data → D4 (Laundry Orders)
        Inventory adjustment → P6 (Inventory)
        Payment request → P5 (Payment)
```

#### P5: Payment Verification
```
Input:  Customer → Payment proof (screenshot, reference)
        Payment data → Amount, method, timestamp
        Promotion rules → From D8 (Promotions)
Process: Validate screenshot, Verify GCash reference, Check amount, 
         Apply promotions, Create payment record, Generate receipt
Output: Payment proof → D5 (Payment Proofs)
        Receipt → Customer
        Financial record → D10 (Financial Transactions)
        Notification → Firebase (P8)
        Audit entry → D11 (Audit Logs)
```

#### P6: Inventory Management
```
Input:  Laundry Order → Items & quantities
        Admin → Stock adjustments, Reorder levels
Process: Deduct stock, Check reorder threshold, Generate purchase orders
Output: Inventory update → D6 (Inventory Stock)
        Reorder alert → Admin
        Stock report → D10 (Financial) for costing
```

#### P7: Location Tracking
```
Input:  Driver → GPS coordinates, Status updates
        Pickup/Delivery → Route information
Process: Validate coordinates, Calculate ETA, Update route, Detect deviations
Output: Location data → D7 (Location Tracking)
        Tracking updates → Customer & Admin
        Route analytics → P10 (Reporting)
        Notification → Firebase (P8)
```

#### P8: Status & Notifications
```
Input:  All Processes → Status change events
        Device tokens → From P12 (Device Token Mgmt)
        Notification preferences → From D1 (Customer Accounts)
Process: Build notification message, Select channel (FCM/SMS), Queue message,
         Track delivery, Handle retries, Log results
Output: Notification → Firebase, Email, SMS
        Device token updates → D9 (Device Tokens)
        Delivery status → D11 (Audit Logs)
```

#### P9: Financial Tracking
```
Input:  Payment Verification → Confirmed payments
        Inventory → Stock costs
        Service Costs → Labor, supplies
Process: Record transaction, Calculate totals, Apply accounting rules
Output: Financial record → D10 (Financial Transactions)
        Reports → Admin
        Invoice data → Customer
```

#### P10: Reporting & Analytics
```
Input:  All data stores (D1-D11) → Raw transaction data
        Location Tracking → Route efficiency
        Payment data → Revenue analytics
Process: Aggregate data, Calculate KPIs, Generate reports, Trend analysis
Output: Reports → Admin/Branch
        Analytics → Admin Dashboard
        Alerts → Branch/Admin for anomalies
```

#### P11: Device Token Management
```
Input:  Customer app → FCM tokens (at login/app start)
        Logout events → Remove tokens
Process: Register/update token, Store device info, Validate token, Clean expired
Output: Device token → D9 (Device Tokens)
        Token validation response → Mobile app
```

#### P12: Audit Logging
```
Input:  All Processes → Status changes, operations, errors
Process: Serialize event data, Add timestamp, Determine severity
Output: Audit entry → D11 (Audit Logs)
        Alert (if critical) → Admin
        Compliance record → For retention
```

---

## DATA FLOW MATRIX

| From | To | Data Elements | Frequency | Volume |
|------|-----|---------------|-----------|--------|
| Customer | P1 | Login, profile | On-demand | 5K/day |
| P1 | D1 | User data | On update | Real-time |
| Admin | P2 | Services, pricing | Daily | 10-50 |
| P2 | D2 | Service catalog | On change | Real-time |
| Customer | P3 | Pickup request | 100/day | 100-500 |
| P3 | D3 | Pickup record | Create | Real-time |
| P3 | P4 | Linked order | Confirm | Real-time |
| P4 | D4 | Laundry order | Create | Real-time |
| Customer | P5 | Payment proof | 300/day | Real-time |
| P4 | P5 | Order amount | Real-time | Real-time |
| D8 | P5 | Promotion rules | On change | Real-time |
| P5 | D5 | Payment record | Store | Real-time |
| P5 | D10 | Financial entry | Record | Real-time |
| P4 | P6 | Items to stock | Real-time | Real-time |
| P6 | D6 | Stock update | Real-time | Real-time |
| Driver | P7 | GPS location | Every 10s | 2M/day |
| P7 | D7 | Location point | Store | Real-time |
| All | P8 | Status events | On change | 10K+/day |
| D9 | P8 | Device tokens | Fetch | Real-time |
| P8 | Firebase | Notifications | Send | Real-time |
| P5, P6, P9 | D10 | Transactions | Record | Real-time |
| All | P10 | All data | Query | Hourly |
| P10 | Admin | Reports | Deliver | Daily/Weekly |
| Customer App | P11 | FCM tokens | At launch | Real-time |
| P11 | D9 | Token store | Save | Real-time |
| All | P12 | Events | Log | Real-time |
| P12 | D11 | Audit entry | Store | Real-time |

---

## CRITICAL DATA PATHS (Highlighted)

### 1. PICKUP → LAUNDRY → PAYMENT → NOTIFICATION (Main Transaction)
```
Customer requests pickup (P3, D3)
    ↓
System creates laundry order (P4, D4)
    ↓
Customer submits payment proof (P5, D5)
    ↓
System sends confirmation notification (P8, Firebase)
    ↓
Driver receives location update (P7, D7)
    ↓
Customer receives real-time tracking (P8, Firebase)
```
**SLA: < 10 seconds end-to-end**

### 2. INVENTORY STOCK MANAGEMENT (Stock Deduction Flow)
```
Laundry order placed (P4)
    ↓
Check stock levels (P6, D6)
    ↓
Deduct items from inventory (P6)
    ↓
Check reorder threshold (P6)
    ↓
Trigger purchase order if needed (P6)
    ↓
Record financial cost (P9, D10)
```
**SLA: < 2 seconds**

### 3. PROMOTION VALIDATION (Multi-Step Verification)
```
Order amount received (P4)
    ↓
Fetch promotion rules (D8)
    ↓
Validate eligibility (P5)
    ↓
Apply discount (P5)
    ↓
Record adjusted amount (P5, D5)
    ↓
Update financial record (P9, D10)
```
**SLA: < 1 second**

---

## DATA STORE SCHEMA OVERVIEW

| Store | Purpose | Key Fields | Volume | Access Rate |
|-------|---------|-----------|--------|-------------|
| D1 | Customer data | ID, name, phone, email, address | 15K | 100/sec |
| D2 | Services | ID, name, price, category | 300 | 50/sec |
| D3 | Pickups | ID, customer, branch, date, status | 8K/month | 200/day |
| D4 | Laundry orders | ID, items, total_amount, status | 25K/month | 500/day |
| D5 | Payments | ID, laundry_id, proof, ref_num, status | 20K/month | 300/day |
| D6 | Inventory | Item_id, quantity, reorder_level | 3K items | 1K/day |
| D7 | Locations | ID, driver_id, coords, timestamp | 2M+ daily | 100/sec |
| D8 | Promotions | ID, code, discount%, rules | 100 active | 1K/day |
| D9 | Device tokens | ID, customer_id, token, device | 50K | 1K/day |
| D10 | Transactions | ID, amount, method, date | 100K/month | 1K/day |
| D11 | Audit logs | ID, action, user, timestamp | 10M+ | Real-time |

---

## EXTERNAL INTERFACE SPECIFICATIONS

### Customer (Mobile/Web)
- **Input**: Pickup requests, payment proofs, location (GPS), feedback
- **Output**: Notifications, order status, receipt, tracking
- **Protocol**: REST API + WebSocket for real-time
- **Frequency**: On-demand (user-driven)

### Admin Dashboard
- **Input**: Service definitions, promotions, reports request, status updates
- **Output**: Analytics, KPI dashboards, alerts, generated reports
- **Protocol**: REST API + Server-sent Events (SSE)
- **Frequency**: Real-time updates every 5 seconds

### Branch Staff
- **Input**: Pickup assignments, status updates, inventory adjustments
- **Output**: Daily orders, stock levels, pickup schedules, reports
- **Protocol**: REST API + Mobile Push Notifications
- **Frequency**: Multiple times per day

### Driver App
- **Input**: Pickup/delivery assignments, customer info, route
- **Output**: GPS location, status updates, proof of delivery
- **Protocol**: REST API + Periodic polling (every 10 seconds)
- **Frequency**: Continuous during active deliveries

### Firebase (FCM)
- **Input**: Notification messages, device tokens
- **Output**: Delivery status, failure reports
- **Protocol**: Firebase Cloud Messaging API
- **Frequency**: On-demand (real-time)

### GCash Payment Gateway
- **Input**: Payment proof (screenshot), reference number
- **Output**: Validation status, merchant confirmation
- **Protocol**: HTTPS REST API with webhook callbacks
- **Frequency**: Real-time during payment verification

---

## DATA CONSISTENCY RULES

| Critical Data | Consistency Level | Enforcement |
|---------------|------------------|-------------|
| Customer balance | ACID | Database transactions |
| Inventory stock | ACID | Pessimistic locking |
| Payment status | ACID | Saga pattern with compensating |
| Promotion rules | Strong Consistency | Cache invalidation on change |
| Device tokens | Eventual Consistency | TTL-based cleanup + validation |
| Audit logs | ACID | Write-ahead logging |
| Location data | Eventual Consistency | Real-time stream + buffer |

---

## PERFORMANCE REQUIREMENTS

| Operation | Response Time | Throughput | Concurrency |
|-----------|--------------|-----------|------------|
| Login | < 500ms | 1K/min | 500 users |
| Pickup request | < 1s | 100/min | 200 users |
| Payment submission | < 2s | 300/min | 100 users |
| Location update | < 500ms | 10K/min | 1K drivers |
| Notification send | < 5s | 1K/min | Async |
| Report generation | < 5s | 10/hour | 50 users |

