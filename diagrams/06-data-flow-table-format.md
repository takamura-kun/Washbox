# WashBox - Data Flow Diagram (Table Format - Aligned)

## Process to Data Store Matrix

### Data Flow Routing Table

```
╔═══════════════════════════════════════════════════════════════════════════╗
║                    PROCESS TO DATA STORE MAPPING                          ║
╠═══════════╦═════════════════════════════════════════════════════════════╣
║ PROCESS   ║ INPUT DATA      │ PROCESS              │ OUTPUT DATA      ║
╠═══════════╬═════════════════╬══════════════════════╬══════════════════╣
║           │                 │                      │                  ║
║ P1:AUTH   ║ Login Creds     │ Validate Against DB  │ Auth Token       ║
║           ║ (Username/Pass) │ Hash & Compare       │ User Profile     ║
║           │                 │ Create Session       │                  ║
║ ┌─────────┴─────────────────┴──────────────────────┴──────────────────┐
║ │ DATA STORE: D1 (Users)                                               │
║ │ - customer_id, email, password_hash, phone, profile, created_at     │
║ └──────────────────────────────────────────────────────────────────────┘
║
║ P2:SERVICE║ Service ID      │ Fetch Service Info   │ Service Details  ║
║ MGMT      ║ Category        │ Check Availability   │ Pricing          ║
║           ║ Query Params    │ Apply Promotion      │ Discount Applied ║
║ ┌─────────┴─────────────────┴──────────────────────┴──────────────────┐
║ │ DATA STORE: D5 (Services)                                            │
║ │ - service_id, name, category, base_price, duration, items          │
║ │ DATA STORE: D5 (Promotions)                                          │
║ │ - promo_id, code, discount, eligibility, expiry                     │
║ └──────────────────────────────────────────────────────────────────────┘
║
║ P3:PICKUP │ Address         │ Validate Address     │ Pickup Request   ║
║ REQUEST   ║ Preferred Date  │ Assign Branch        │ Driver Assigned  ║
║           ║ Customer ID     │ Find Available Driver│ Confirmation     ║
║ ┌─────────┴─────────────────┴──────────────────────┴──────────────────┐
║ │ DATA STORE: D2 (Pickups)                                             │
║ │ - pickup_id, customer_id, address, date, status, branch_id, driver_id
║ │ DATA STORE: D1 (Users) - Driver lookup                               │
║ └──────────────────────────────────────────────────────────────────────┘
║
║ P4:LAUNDRY│ Service List    │ Create Order Items   │ Order Confirmed  ║
║ ORDER     ║ Quantity        │ Calculate Total      │ Order ID         ║
║           ║ Instructions    │ Apply Promos         │ Total Amount     ║
║ ┌─────────┴─────────────────┴──────────────────────┴──────────────────┐
║ │ DATA STORE: D3 (Laundries)                                           │
║ │ - laundry_id, pickup_id, items, status, total_amount, instructions │
║ └──────────────────────────────────────────────────────────────────────┘
║
║ P5:PAYMENT│ Image File      │ Extract QR Code      │ Payment Verified ║
║ VERIFY    ║ Amount          │ Validate Amount      │ Status: Approved ║
║           ║ Reference       │ Check Timestamp      │ Transaction ID   ║
║ ┌─────────┴─────────────────┴──────────────────────┴──────────────────┐
║ │ DATA STORE: D4 (Payments)                                            │
║ │ - payment_id, laundry_id, amount, reference, proof_image, status   │
║ │ DATA STORE: D9 (Transactions)                                        │
║ │ - transaction_id, amount, timestamp, merchant, gateway_response     │
║ └──────────────────────────────────────────────────────────────────────┘
║
║ P6:INVENT │ Item IDs        │ Check Stock Level    │ Stock Deducted   ║
║ ARY MGMT  ║ Quantities      │ Reserve Items        │ Movement Record  ║
║           ║ Laundry ID      │ Update Stock         │ Reorder Alert    ║
║ ┌─────────┴─────────────────┴──────────────────────┴──────────────────┐
║ │ DATA STORE: D6 (Inventory)                                           │
║ │ - item_id, quantity, reorder_point, last_updated                    │
║ │ DATA STORE: D7 (Stock Movements)                                     │
║ │ - movement_id, item_id, type (deduct/add), qty, reason, timestamp   │
║ └──────────────────────────────────────────────────────────────────────┘
║
║ P7:TRACK  │ GPS Latitude    │ Real-time Update     │ Route Optimized  ║
║ ING       ║ Longitude       │ Calculate Distance   │ ETA Updated      ║
║           ║ Pickup ID       │ Route Optimization   │ Status Changed   ║
║ ┌─────────┴─────────────────┴──────────────────────┴──────────────────┐
║ │ DATA STORE: D2 (Pickups)                                             │
║ │ - pickup_id, current_lat, current_lng, eta, status, updated_at      │
║ └──────────────────────────────────────────────────────────────────────┘
║
║ P8:NOTIF  │ Event Type      │ Queue Event          │ Notification     ║
║ DIST      ║ Entity ID       │ Get Device Tokens    │ FCM Sent         ║
║           ║ Data Payload    │ Send via FCM         │ Delivery Status  ║
║ ┌─────────┴─────────────────┴──────────────────────┴──────────────────┐
║ │ DATA STORE: D7 (Notifications)                                       │
║ │ - notif_id, type, recipient_id, payload, status, sent_at           │
║ │ DATA STORE: D8 (DeviceTokens)                                        │
║ │ - token_id, customer_id, fcm_token, device_type, is_active          │
║ └──────────────────────────────────────────────────────────────────────┘
║
║ P9:REPORT │ Date Range      │ Aggregate Data       │ Dashboard Data   ║
║ S         ║ Filter Params   │ Calculate Metrics    │ Report Export    ║
║           ║ User Role       │ Generate Charts      │ Analytics Output ║
║ ┌─────────┴─────────────────┴──────────────────────┴──────────────────┐
║ │ DATA STORE: All Stores                                               │
║ │ - Pulls from D1, D2, D3, D4, D6, D9 for comprehensive reporting     │
║ └──────────────────────────────────────────────────────────────────────┘
║
╚═══════════╩═════════════════════════════════════════════════════════════╝
```

---

## External Entity Data Exchange

```
╔════════════════════════════════════════════════════════════════════════════╗
║                    EXTERNAL ENTITY DATA FLOWS                              ║
╠════════════════════════════════════════════════════════════════════════════╣
║
║ CUSTOMER (Mobile App User)
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │ ► SENDS TO SYSTEM                                                        │
║ │   • Login credentials (email, password)                                  │
║ │   • Pickup request (address, date, phone)                                │
║ │   • Service selection (item list, quantities)                            │
║ │   • Payment proof (screenshot, reference number, amount)                 │
║ │   • Location permission (GPS access)                                     │
║ │   • FCM token registration                                               │
║ │
║ │ ◄ RECEIVES FROM SYSTEM                                                   │
║ │   • Pickup request confirmation                                          │
║ │   • Status notifications (in progress, ready, delivered)                 │
║ │   • Order tracking updates                                               │
║ │   • Payment verification status                                          │
║ │   • Receipt/Invoice                                                      │
║ └──────────────────────────────────────────────────────────────────────────┘
║
║ ADMIN (Dashboard User)
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │ ► SENDS TO SYSTEM                                                        │
║ │   • Service management (create, update, delete)                          │
║ │   • Promotion management                                                 │
║ │   • Payment verification approval/rejection                              │
║ │   • Branch management                                                    │
║ │   • Staff management                                                     │
║ │   • Report queries                                                       │
║ │
║ │ ◄ RECEIVES FROM SYSTEM                                                   │
║ │   • Pending payment verifications                                        │
║ │   • System reports and analytics                                         │
║ │   • Low inventory alerts                                                 │
║ │   • Performance metrics                                                  │
║ │   • User activity logs                                                   │
║ └──────────────────────────────────────────────────────────────────────────┘
║
║ BRANCH STAFF (Operational User)
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │ ► SENDS TO SYSTEM                                                        │
║ │   • Accept/reject pickup requests                                        │
║ │   • Update laundry status (received, processing, ready)                  │
║ │   • Assign drivers                                                       │
║ │   • Update inventory                                                     │
║ │   • Submit delivery reports                                              │
║ │
║ │ ◄ RECEIVES FROM SYSTEM                                                   │
║ │   • New pickup requests                                                  │
║ │   • Inventory levels                                                     │
║ │   • Driver assignments                                                   │
║ │   • Daily task list                                                      │
║ │   • Stock alerts                                                         │
║ └──────────────────────────────────────────────────────────────────────────┘
║
║ DRIVER (Delivery Personnel)
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │ ► SENDS TO SYSTEM                                                        │
║ │   • GPS location updates (continuous)                                    │
║ │   • Accept/reject delivery                                               │
║ │   • Photo proof of pickup/delivery                                       │
║ │   • Delivery completion status                                           │
║ │   • Customer signature/confirmation                                      │
║ │
║ │ ◄ RECEIVES FROM SYSTEM                                                   │
║ │   • Delivery assignments                                                 │
║ │   • Route optimizations                                                  │
║ │   • Customer contact details                                             │
║ │   • Delivery status updates                                              │
║ │   • Real-time navigation                                                 │
║ └──────────────────────────────────────────────────────────────────────────┘
║
║ EXTERNAL: FIREBASE (FCM Service)
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │ ► SENDS TO FIREBASE                                                      │
║ │   • Device token registration                                            │
║ │   • Push notification payload                                            │
║ │   • Device management updates                                            │
║ │
║ │ ◄ RECEIVES FROM FIREBASE                                                 │
║ │   • Token validation response                                            │
║ │   • Delivery status confirmation                                         │
║ │   • Failed delivery notifications                                        │
║ └──────────────────────────────────────────────────────────────────────────┘
║
║ EXTERNAL: GCASH (Payment Gateway)
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │ ► SENDS TO GCASH                                                         │
║ │   • Payment verification request                                         │
║ │   • QR code validation query                                             │
║ │   • Transaction lookup                                                   │
║ │
║ │ ◄ RECEIVES FROM GCASH                                                    │
║ │   • Payment status (confirmed/failed)                                    │
║ │   • Transaction reference verification                                   │
║ │   • Merchant validation                                                  │
║ └──────────────────────────────────────────────────────────────────────────┘
║
║ EXTERNAL: GOOGLE MAPS (Location Service)
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │ ► SENDS TO GOOGLE MAPS                                                   │
║ │   • Location queries (lat/lng)                                           │
║ │   • Distance/duration requests                                           │
║ │   • Route optimization requests                                          │
║ │   • Geocoding requests (address to coordinates)                          │
║ │
║ │ ◄ RECEIVES FROM GOOGLE MAPS                                              │
║ │   • Distance matrix                                                      │
║ │   • Estimated time of arrival                                            │
║ │   • Optimized route waypoints                                            │
║ │   • Geocoding results                                                    │
║ └──────────────────────────────────────────────────────────────────────────┘
║
╚════════════════════════════════════════════════════════════════════════════╝
```

---

## Data Store Schema Overview

```
╔════════════════════════════════════════════════════════════════════════════╗
║                         DATA STORE DETAILS                                 ║
╠════════════════════════════════════════════════════════════════════════════╣
║
║ D1: USERS TABLE
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │ PK: customer_id                                                          │
║ │ Fields: email, password_hash, phone, name, address, latitude, longitude │
║ │ Indexes: email (unique), phone, created_at                               │
║ │ Related: pickups, laundries, payments, device_tokens                     │
║ │ Frequency: Very High (every login, profile view)                         │
║ │ Size Estimate: 50,000 records, 250 MB                                    │
║ └──────────────────────────────────────────────────────────────────────────┘
║
║ D2: PICKUPS TABLE
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │ PK: pickup_id                                                            │
║ │ FK: customer_id, branch_id, driver_id                                    │
║ │ Fields: address, preferred_date, status, total_fee, current_lat,         │
║ │         current_lng, eta, notes, created_at, updated_at                  │
║ │ Indexes: customer_id, status, branch_id, driver_id, preferred_date       │
║ │ Related: laundries, location_tracking, notifications                     │
║ │ Frequency: High (2,000 pickups/day)                                      │
║ │ Size Estimate: 500,000 records, 600 MB                                   │
║ └──────────────────────────────────────────────────────────────────────────┘
║
║ D3: LAUNDRIES TABLE
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │ PK: laundry_id                                                           │
║ │ FK: pickup_id, customer_id, service_id, branch_id                        │
║ │ Fields: status, total_amount, items, instructions, received_date,        │
║ │         completed_date, notes, created_at, updated_at                    │
║ │ Indexes: pickup_id, customer_id, status, branch_id, received_date        │
║ │ Related: laundry_items, payments, inventory_movements                    │
║ │ Frequency: High (8,000 laundries/day)                                    │
║ │ Size Estimate: 1,000,000 records, 1.2 GB                                 │
║ └──────────────────────────────────────────────────────────────────────────┘
║
║ D4: PAYMENTS TABLE
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │ PK: payment_id                                                           │
║ │ FK: laundry_id, customer_id                                              │
║ │ Fields: amount, reference_number, proof_image, status, submitted_at,     │
║ │         approved_at, rejected_at, notes, admin_notes, created_at         │
║ │ Indexes: laundry_id, customer_id, status, submitted_at                   │
║ │ Related: laundries, payment_events, transactions                         │
║ │ Frequency: High (4,000 payments/day)                                     │
║ │ Size Estimate: 400,000 records, 800 MB (includes image refs)             │
║ └──────────────────────────────────────────────────────────────────────────┘
║
║ D5: SERVICES TABLE
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │ PK: service_id                                                           │
║ │ Fields: name, category, description, base_price, duration, items,        │
║ │         is_active, created_at, updated_at                                │
║ │ Indexes: category, is_active, name                                       │
║ │ Related: laundry_items, promotions, branch_services                      │
║ │ Frequency: Medium (cached - 1 hour TTL)                                  │
║ │ Size Estimate: 100 records, 50 KB                                        │
║ └──────────────────────────────────────────────────────────────────────────┘
║
║ D6: INVENTORY TABLE
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │ PK: item_id + branch_id                                                  │
║ │ Fields: item_name, quantity, reorder_point, last_stock_date, notes,      │
║ │         created_at, updated_at                                           │
║ │ Indexes: branch_id, quantity, last_stock_date                            │
║ │ Related: inventory_movements, inventory_purchases                        │
║ │ Frequency: High (updated during laundry processing)                      │
║ │ Size Estimate: 5,000 records (50 items × 100 branches), 100 MB           │
║ └──────────────────────────────────────────────────────────────────────────┘
║
║ D7: NOTIFICATIONS TABLE
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │ PK: notification_id                                                      │
║ │ FK: recipient_id                                                         │
║ │ Fields: type, title, message, payload, status, sent_at, delivered_at,    │
║ │         clicked_at, created_at                                           │
║ │ Indexes: recipient_id, status, type, created_at                          │
║ │ Related: device_tokens, notification_events                              │
║ │ Frequency: Very High (50,000 notifications/day)                          │
║ │ Size Estimate: 2,000,000 records, 2 GB                                   │
║ └──────────────────────────────────────────────────────────────────────────┘
║
║ D8: DEVICE_TOKENS TABLE
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │ PK: token_id                                                             │
║ │ FK: customer_id                                                          │
║ │ Fields: fcm_token, device_type (ios/android), device_name, is_active,    │
║ │         last_used_at, created_at, updated_at                             │
║ │ Indexes: customer_id, fcm_token, is_active                               │
║ │ Related: users, notifications                                            │
║ │ Frequency: Medium (refresh on app launch)                                │
║ │ Size Estimate: 150,000 records (3 devices/user avg), 300 MB              │
║ └──────────────────────────────────────────────────────────────────────────┘
║
║ D9: TRANSACTIONS TABLE
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │ PK: transaction_id                                                       │
║ │ FK: payment_id, customer_id                                              │
║ │ Fields: amount, reference_number, merchant_id, status, gateway_response, │
║ │         error_code, created_at, verified_at                              │
║ │ Indexes: payment_id, customer_id, status, created_at                     │
║ │ Related: payments, payment_events                                        │
║ │ Frequency: Medium (payment verification)                                 │
║ │ Size Estimate: 400,000 records, 500 MB                                   │
║ └──────────────────────────────────────────────────────────────────────────┘
║
╚════════════════════════════════════════════════════════════════════════════╝
```

---

## Critical Data Flow Timing

```
╔════════════════════════════════════════════════════════════════════════════╗
║              DATA FLOW TIMING & SERVICE LEVEL AGREEMENTS                    ║
╠════════════════════════════════════════════════════════════════════════════╣
║
║ PROCESS LATENCY REQUIREMENTS
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │
║ │ P1: Authentication
║ │ ├─ Target: < 2 seconds
║ │ ├─ Database Lookups: 1 (user validation)
║ │ └─ Service Calls: 0
║ │
║ │ P3: Pickup Request Creation
║ │ ├─ Target: < 5 seconds
║ │ ├─ Database Lookups: 3 (customer, branch, services)
║ │ ├─ Service Calls: 1 (Google Maps for distance)
║ │ └─ Notification: Async
║ │
║ │ P5: Payment Verification
║ │ ├─ Target: < 5 seconds
║ │ ├─ Database Lookups: 2 (laundry, previous payments)
║ │ ├─ Service Calls: 1 (GCash verification)
║ │ ├─ Image Processing: QR extraction
║ │ └─ Response: Sync with async notification
║ │
║ │ P7: Location Tracking Update
║ │ ├─ Target: < 2 seconds
║ │ ├─ Database Lookups: 1 (pickup)
║ │ ├─ Database Updates: 1 (location)
║ │ ├─ Service Calls: 1 (Google Maps for ETA)
║ │ └─ Notification: Async to customers
║ │
║ │ P8: Notification Dispatch
║ │ ├─ Target: < 10 seconds (queue to send)
║ │ ├─ Database Lookups: 2 (customer, device tokens)
║ │ ├─ Service Calls: 1 (Firebase FCM)
║ │ └─ Retry: 3 attempts with exponential backoff
║ │
║ └──────────────────────────────────────────────────────────────────────────┘
║
║ DATA CONSISTENCY GUARANTEES
║ ┌──────────────────────────────────────────────────────────────────────────┐
║ │
║ │ CRITICAL: Payment Processing (P5)
║ │ ├─ Atomicity: All or nothing (ACID)
║ │ ├─ Consistency: Verify before update
║ │ ├─ Isolation: Transaction locks on laundry record
║ │ ├─ Durability: Write-through to disk
║ │ └─ Idempotency: Detect duplicate submissions
║ │
║ │ CRITICAL: Inventory Deduction (P6)
║ │ ├─ Atomicity: Stock + movement record together
║ │ ├─ Consistency: No negative stock allowed
║ │ ├─ Isolation: Row-level locks during deduction
║ │ ├─ Durability: Write-through to disk
║ │ └─ Audit: Full movement tracking for reorder
║ │
║ │ IMPORTANT: Pickup Status (P3, P2)
║ │ ├─ Consistency: State machine enforcement
║ │ ├─ Isolation: Optimistic locks on concurrent updates
║ │ └─ Durability: Standard disk write
║ │
║ │ EVENTUAL: Notifications (P8)
║ │ ├─ Consistency: May have delays
║ │ ├─ Retry: Up to 3 attempts
║ │ └─ Fallback: In-app notification + email
║ │
║ └──────────────────────────────────────────────────────────────────────────┘
║
╚════════════════════════════════════════════════════════════════════════════╝
```
