# CHAPTER IV: RESULTS AND DISCUSSION

This chapter presents the results of the development and implementation of the WashBox Laundry Management System (WLMS). It discusses the system's features, the outcomes of each major module, and how the implemented solution addresses the problems identified in the earlier chapters.

---

## System Implementation Overview

The WashBox Laundry Management System was successfully developed as a multi-platform solution consisting of three interconnected components: a Laravel-based backend API, a web-based administrative dashboard, and a React Native mobile application. The system is deployed and operational across three branch locations, with a centralized MySQL database managing all data.

The implementation covers the following core modules:

1. User Authentication and Role Management
2. Laundry Order Management
3. Pickup Request Management
4. Notification System
5. Payment and Pricing Management
6. Promotions and Discounts
7. Reporting and Analytics
8. Unclaimed Laundry Management

---

## Module 1: User Authentication and Role Management

### Implementation

The system implements a role-based access control (RBAC) model with three distinct user roles: **Owner/Admin**, **Staff**, and **Customer**. Authentication is handled through Laravel Sanctum, which issues personal access tokens for API requests from the mobile application, and session-based authentication for the web dashboard.

The middleware stack enforces role separation:
- `AdminMiddleware` — restricts access to owner-level dashboard routes
- `StaffMiddleware` — limits staff to their assigned branch operations
- `CustomerMiddleware` — secures mobile API endpoints for registered customers
- `BranchAccessMiddleware` — prevents staff from accessing data outside their assigned branch

Customer registration supports two pathways: self-registration through the mobile application with email verification, and Google OAuth for streamlined sign-in. Walk-in customers are manually registered by staff through the web dashboard.

### Results

The authentication system successfully enforces access boundaries across all three roles. Staff members can only view and manage laundry orders and pickup requests belonging to their assigned branch. Owners have full visibility across all branches including consolidated reports and analytics. Customers can only access their own orders, pickup requests, and notifications through the mobile application.

Password reset functionality is implemented via email using the `PasswordResetNotification` class, and activity logging through `ActivityLogMiddleware` maintains an audit trail of all significant user actions.

---

## Module 2: Laundry Order Management

### Implementation

Laundry orders are created by staff through the web dashboard. Each order is assigned a unique tracking number and linked to a customer, branch, service, and optionally a pickup request. The `Laundry` model manages the full order lifecycle with the following status pipeline:

```
received → washing → ready → paid → completed
                                  ↘ cancelled (any stage)
```

Each status transition is timestamped (`received_at`, `processing_at`, `ready_at`, `paid_at`, `completed_at`, `cancelled_at`) and recorded in the `laundry_status_histories` table through the `updateStatus()` method, providing a complete audit trail for every order.

Orders support add-ons through a many-to-many relationship via the `laundry_addon` pivot table, which stores the price at the time of purchase to preserve historical accuracy. Staff can assign orders to specific team members, and the system tracks which staff member created and processed each order.

### Results

The laundry order module successfully handles the complete order lifecycle. The `LaundryObserver` automatically triggers notifications to the customer, assigned staff, and admin at every status transition without requiring manual intervention from controllers. The status history table provides full traceability for each order, which is useful for dispute resolution and performance monitoring.

The system also handles edge cases such as service-nullable orders (where `service_id` can be null for custom laundry jobs) and correctly calculates totals when add-ons are modified after order creation.

---

## Module 3: Pickup Request Management

### Implementation

Customers submit pickup requests through the mobile application by pinning their location on an OpenStreetMap interface and specifying a preferred date and time. The `PickupRequest` model manages a four-stage status pipeline:

```
pending → accepted → en_route → picked_up
        ↘ cancelled (at pending or accepted)
```

The `GeocodingService` and `PhilippineGeocodingService` handle address-to-coordinate conversion for Philippine addresses. Real-time staff location tracking is implemented through the `LocationTrackingController`, which updates `staff_latitude` and `staff_longitude` on the pickup request record as the staff member travels to the customer.

Delivery fees are calculated using the `DeliveryFee` model, which stores branch-specific rates for `pickup_only`, `delivery_only`, and `both` service types, including a `both_discount` percentage and a `minimum_laundry_for_free` threshold.

Once the staff marks a pickup as `picked_up`, they create a corresponding laundry order in the system. The laundry order is linked back to the pickup request via `pickup_request_id`, and the pickup request is linked to the laundry via `laundries_id`, establishing a bidirectional relationship.

Staff can also upload a proof photo (`pickup_proof_photo`) upon collection, stored in the `storage/pickup-proofs/` directory.

### Results

The pickup request module successfully coordinates the full collection workflow between customers and staff. Real-time location tracking allows customers to monitor their rider's progress through the mobile application. The `PickupRequestObserver` automatically notifies all relevant parties at each status transition, including the customer, branch staff, and admin.

The cancellation flow correctly restricts cancellations to only `pending` and `accepted` states, preventing cancellation once a rider is already en route. The proof photo feature provides accountability for staff collections.

---

## Module 4: Notification System

### Implementation

The notification system operates on three levels simultaneously for every significant event:

| Target | Model | Delivery |
|---|---|---|
| Customer | `Notification` | In-app + FCM push |
| Staff | `StaffNotification` | In-app (web dashboard) |
| Admin/Owner | `AdminNotification` | In-app (web dashboard) |

Notifications are triggered automatically by two Eloquent Observers — `LaundryObserver` and `PickupRequestObserver` — which listen for model creation and status change events without requiring explicit calls from controllers.

Push notifications to customer mobile devices are delivered through `FCMService.php`, which uses the Kreait Firebase SDK to send `CloudMessage` objects to individual device tokens stored in the `device_tokens` table and the `fcm_token` field on the `Customer` model. The service supports sending to a single device (`sendToDevice()`), multiple devices (`sendToDevices()`), all staff in a branch (`sendToBranchStaff()`), and topic-based broadcasts (`sendToTopic()`).

The following notification types are implemented:

**Laundry notifications:** `laudry_received`, `washing_started`, `laundry_ready`, `payment_received`, `laundry_completed`, `laundry_cancelled`, `staff_assigned`, `laundry_assigned` (staff), `unclaimed_laundry`, `urgent_unclaimed`

**Pickup notifications:** `pickup_submitted`, `pickup_accepted`, `pickup_en_route`, `pickup_completed`, `pickup_cancelled`

**Unclaimed notifications:** `unclaimed_laundry` (Day 1–2, staff warning) and `urgent_unclaimed` (Day 3+, staff urgent + `unclaimed_urgent` admin alert) — triggered by `LaundryObserver` on status updates; the `SendUnclaimedReminders` Artisan command handles scheduled batch reminders

### Results

The notification system successfully delivers real-time updates to all three target groups without coupling notification logic to business logic. When a staff member updates a laundry status, the observer fires automatically and creates the appropriate database records and FCM messages in a single operation.

The system gracefully handles missing or invalid FCM tokens by logging a warning and skipping delivery rather than throwing an exception, ensuring that a single customer with an outdated token does not interrupt batch notification operations.

---

## Module 5: Payment and Pricing Management

### Implementation

The pricing engine calculates order totals using the following formula:

```
total_amount = subtotal - discount_amount + pickup_fee + delivery_fee + addons_total
```

Where `subtotal` is derived from the service's `pricing_type`:
- **per_load** (regular and full service): `price_per_load × number_of_loads`
- **per_piece** (special_item — e.g., comforters): `price_per_load × number_of_loads` where `number_of_loads` represents the count of pieces

Promotions are applied through two mechanisms stored on the `Laundry` model:
- `promotion_override_total` — replaces the entire subtotal with a fixed promotional price
- `promotion_price_per_load` — replaces the per-load rate with a discounted rate

Delivery fees are branch-specific and managed through the `DeliveryFee` model. When both pickup and delivery are selected, a `both_discount` percentage is applied. If the laundry subtotal meets or exceeds `minimum_laundry_for_free`, all delivery fees are waived.

Payment methods supported include cash and GCash (with QR code upload via `PaymentProof`). The `Payment` model records each transaction, and `CustomerPaymentMethod` stores saved payment preferences per customer.

### Results

The pricing module correctly handles all service type combinations and applies promotions, delivery fees, and discounts in the correct order. The `SyncOrderFees` Artisan command allows administrators to recalculate fees on existing orders when branch fee structures are updated.

The GCash payment proof upload feature allows customers to submit payment screenshots, which staff verify through the `PaymentVerificationController` before marking orders as paid.

---

## Module 6: Promotions and Discounts

### Implementation

Promotions are managed through the `Promotion` model and tracked via `PromotionUsage` to prevent duplicate application. The `PromotionObserver` monitors promotion lifecycle events. The `CalculatePromotionROI` Artisan command computes return-on-investment metrics for each promotion based on orders where the promotion was applied.

Promotions support:
- Fixed override pricing (`promotion_override_total`)
- Per-load rate discounts (`promotion_price_per_load`)
- Usage limits and date-based validity
- Branch-specific or system-wide applicability

### Results

The promotion system successfully prevents double application of promotions through the `PromotionUsage` table. The ROI calculation command provides owners with data-driven insights into which promotions are generating revenue versus reducing margins.

---

## Module 7: Reporting and Analytics

### Implementation

The reporting module is accessible exclusively to owners and admins through the web dashboard. The `ReportController` (Admin and Staff variants) and `AnalyticsController` aggregate data from the `laundries`, `payments`, `pickup_requests`, and `customers` tables.

The `DashboardSyncService` maintains cached dashboard metrics that are invalidated and refreshed whenever a laundry order or pickup request is created or updated, ensuring the dashboard reflects near-real-time data without executing expensive queries on every page load.

The `HasAnalytics` trait provides reusable analytics methods across models, and the `Exportable` trait enables CSV/Excel export of reports.

Reports available include:
- Revenue reports (daily, weekly, monthly) per branch and consolidated
- Order volume and completion rate reports
- Staff performance metrics
- Pickup request statistics
- Unclaimed laundry reports
- Promotion ROI reports
- Customer retention and activity reports

### Results

The analytics dashboard provides owners with a comprehensive view of operations across all three branches. Branch-specific filtering allows comparison of performance between locations. The export functionality enables offline analysis and record-keeping.

---

## Module 8: Unclaimed Laundry Management

### Implementation

The system tracks laundry orders that remain in `ready` status without payment. The `UnclaimedLaundry` model records when an order becomes unclaimed, and the `LaundryObserver` monitors the `days_unclaimed` accessor on the `Laundry` model.

The `LaundryObserver` fires unclaimed alerts on status updates:
- `unclaimed_laundry` — notifies branch staff when an order has been ready for 1–2 days
- `urgent_unclaimed` — notifies branch staff and admin when an order has been unclaimed for 3+ days

The `SendUnclaimedReminders` Artisan command handles scheduled batch reminders for orders that have not been collected.

After 7 days, a storage fee is calculated at a configurable rate per day (`unclaimed.storage_fee_per_day` config value). The `UnclaimedController` (Admin and Staff) allows staff to mark items as disposed and record the disposing staff member.

The unclaimed status is color-coded for urgency (from the `Laundry` model's `getUnclaimedStatusAttribute`):
- `normal` (0 days) → green
- `pending` (1 day) → blue
- `warning` (3–6 days) → yellow
- `urgent` (7–13 days) → orange
- `critical` (14+ days) → red

### Results

The unclaimed laundry module successfully reduces the risk of lost or forgotten orders. Automated reminders prompt customers to collect their laundry without requiring manual follow-up from staff. The storage fee mechanism provides a financial incentive for timely collection and compensates the branch for extended storage.

---

## System Performance and Reliability

The system implements several measures to ensure reliability:

- **Soft Deletes**: The `Laundry`, `PickupRequest`, `Customer`, and `Service` models use `SoftDeletes`, preserving historical records while removing items from active views
- **Database Transactions**: Complex operations such as order creation with fee calculation use atomic transactions to prevent partial writes
- **Activity Logging**: `ActivityLogMiddleware` records all significant actions for audit purposes
- **Error Handling**: `FCMService` catches all Firebase exceptions and logs errors without propagating failures to the user interface
- **Branch Isolation**: `BranchAccessMiddleware` and `BranchPolicy` enforce data isolation between branches at both the middleware and policy layers

---

## Summary

The WashBox Laundry Management System was successfully implemented as a fully functional multi-platform solution. All eight core modules operate as designed, with the Observer pattern ensuring automatic notification delivery, the Service layer encapsulating complex business logic, and the Policy layer enforcing access control. The system addresses the original problem of manual, paper-based laundry management by providing real-time order tracking, automated customer communication, location-based pickup coordination, and comprehensive business analytics across all three branch locations.

---

## Diagrams

### Figure 7: Entity Relationship Diagram (ERD)

```
┌─────────────────┐         ┌─────────────────────────────────────────┐
│    branches     │         │                 users                   │
│─────────────────│         │─────────────────────────────────────────│
│ PK id           │◄────────│ PK id                                   │
│    name         │  branch │    name, email, phone                   │
│    code         │         │    role (admin | staff)                 │
│    address      │         │ FK branch_id                            │
│    city         │         │    is_active, fcm_token                 │
│    latitude     │         └─────────────────────────────────────────┘
│    longitude    │
│    is_active    │         ┌─────────────────────────────────────────┐
└────────┬────────┘         │               customers                 │
         │                  │─────────────────────────────────────────│
         │                  │ PK id                                   │
         │                  │    name, phone, email                   │
         │                  │    registration_type                    │
         │                  │    fcm_token, status                    │
         │                  │ FK preferred_branch_id                  │
         │                  │ FK registered_by (users)                │
         │                  └──────────────┬──────────────────────────┘
         │                                 │
         │              ┌──────────────────┼──────────────────────┐
         │              │                  │                       │
         ▼              ▼                  ▼                       ▼
┌──────────────┐  ┌─────────────────────────────┐   ┌────────────────────────┐
│delivery_fees │  │         laundries            │   │    pickup_requests     │
│──────────────│  │─────────────────────────────│   │────────────────────────│
│ PK id        │  │ PK id                        │   │ PK id                  │
│ FK branch_id │  │    tracking_number           │   │ FK customer_id         │
│    pickup_fee│  │ FK customer_id               │   │ FK branch_id           │
│ delivery_fee │  │ FK branch_id                 │   │ FK service_id          │
│ both_discount│  │ FK service_id (nullable)     │   │ FK assigned_to (users) │
│ min_for_free │  │ FK staff_id (users)          │   │    pickup_address      │
└──────────────┘  │ FK created_by (users)        │   │    latitude, longitude │
                  │ FK pickup_request_id         │◄──│    preferred_date/time │
                  │ FK promotion_id              │   │    service_type        │
                  │    status                    │──►│    laundries_id        │
                  │    weight, number_of_loads   │   │    status              │
                  │    subtotal, addons_total    │   │    pickup_fee          │
                  │    discount_amount           │   │    delivery_fee        │
                  │    pickup_fee, delivery_fee  │   │    staff_lat/lng       │
                  │    total_amount              │   │    pickup_proof_photo  │
                  │    payment_status/method     │   └────────────────────────┘
                  │    received/washing/ready    │
                  │    paid/completed_at         │
                  └──────────────┬──────────────┘
                                 │
          ┌──────────────────────┼──────────────────────┐
          │                      │                       │
          ▼                      ▼                       ▼
┌──────────────────┐  ┌──────────────────────┐  ┌──────────────────────┐
│    payments      │  │  laundry_addon        │  │laundry_status_history│
│──────────────────│  │  (pivot)              │  │──────────────────────│
│ PK id            │  │──────────────────────│  │ PK id                │
│ FK laundries_id  │  │ FK laundries_id       │  │ FK laundries_id      │
│    method        │  │ FK add_on_id          │  │    status            │
│    amount        │  │    price_at_purchase  │  │    changed_by        │
│    receipt_number│  │    quantity           │  │    notes             │
│ FK received_by   │  └──────────────────────┘  │    created_at        │
└──────────────────┘                             └──────────────────────┘

┌──────────────────┐  ┌──────────────────────┐  ┌──────────────────────┐
│    add_ons       │  │     promotions        │  │    notifications     │
│──────────────────│  │──────────────────────│  │──────────────────────│
│ PK id            │  │ PK id                │  │ PK id                │
│    name, slug    │  │    name, type        │  │ FK customer_id       │
│    price         │  │    application_type  │  │ FK laundries_id      │
│    is_active     │  │    discount_type     │  │ FK pickup_request_id │
└──────────────────┘  │    discount_value    │  │    type, title, body │
                      │    promo_code        │  │    fcm_status        │
                      │    start_date        │  │    is_read           │
                      │    end_date          │  └──────────────────────┘
                      │ FK branch_id         │
                      │    max_usage         │  ┌──────────────────────┐
                      └──────────────────────┘  │   device_tokens      │
                                                 │──────────────────────│
                      ┌──────────────────────┐  │ PK id                │
                      │    services          │  │ FK customer_id       │
                      │──────────────────────│  │    token             │
                      │ PK id                │  │    device_type       │
                      │    name, slug        │  │    is_active         │
                      │    pricing_type      │  └──────────────────────┘
                      │    price_per_load    │
                      │    price_per_piece   │
                      │    service_type      │
                      │    category          │
                      │    is_active         │
                      └──────────────────────┘
```

### Figure 8: Use Case Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                  WASHBOX LAUNDRY MANAGEMENT SYSTEM                      │
│                                                                         │
│  ┌──────────────┐    ┌──────────────────────────────────────────────┐  │
│  │              │    │              CUSTOMER                        │  │
│  │   CUSTOMER   │───►│  • Register / Login (email or Google OAuth)  │  │
│  │              │    │  • View laundry order status                 │  │
│  └──────────────┘    │  • Submit pickup request (map pin)           │  │
│                      │  • Track rider location in real-time         │  │
│                      │  • View and pay via GCash / cash             │  │
│                      │  • Upload GCash payment proof                │  │
│                      │  • Receive push notifications (FCM)          │  │
│                      │  • View digital receipt                      │  │
│                      │  • Rate completed laundry order              │  │
│                      │  • View active promotions                    │  │
│                      │  • Manage saved addresses                    │  │
│                      └──────────────────────────────────────────────┘  │
│                                                                         │
│  ┌──────────────┐    ┌──────────────────────────────────────────────┐  │
│  │              │    │                STAFF                         │  │
│  │    STAFF     │───►│  • Login to web dashboard                    │  │
│  │              │    │  • Register walk-in customers                │  │
│  └──────────────┘    │  • Create and manage laundry orders          │  │
│                      │  • Update laundry status                     │  │
│                      │  • Accept and process pickup requests        │  │
│                      │  • Update en_route / picked_up status        │  │
│                      │  • Upload pickup proof photo                 │  │
│                      │  • Verify GCash payment proofs               │  │
│                      │  • Mark orders as paid / completed           │  │
│                      │  • Manage unclaimed laundry                  │  │
│                      │  • View branch-level reports                 │  │
│                      │  • Receive in-app staff notifications        │  │
│                      └──────────────────────────────────────────────┘  │
│                                                                         │
│  ┌──────────────┐    ┌──────────────────────────────────────────────┐  │
│  │              │    │              ADMIN / OWNER                   │  │
│  │    ADMIN /   │───►│  • All Staff capabilities                    │  │
│  │    OWNER     │    │  • Manage branches and delivery fees         │  │
│  │              │    │  • Manage services and add-ons               │  │
│  └──────────────┘    │  • Create and manage promotions              │  │
│                      │  • Manage staff accounts                     │  │
│                      │  • View consolidated multi-branch reports    │  │
│                      │  • View analytics and revenue dashboards     │  │
│                      │  • Export reports (CSV/Excel)                │  │
│                      │  • Manage system settings                    │  │
│                      │  • View activity logs                        │  │
│                      │  • Manage unclaimed and disposed laundry     │  │
│                      │  • Calculate promotion ROI                   │  │
│                      └──────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
```

### Figure 9: System Interface Flow Diagram

```
┌──────────────────────────────────────────────────────────────────────────┐
│                        SYSTEM INTERFACE FLOW                             │
└──────────────────────────────────────────────────────────────────────────┘

  MOBILE APP (Customer)              LARAVEL API              WEB DASHBOARD (Staff/Admin)
         │                               │                               │
         │  POST /api/login              │                               │
         │──────────────────────────────►│                               │
         │◄── Sanctum token ─────────────│                               │
         │                               │                               │
         │  POST /api/pickups            │                               │
         │  (location, date, time)       │                               │
         │──────────────────────────────►│                               │
         │                               │── PickupRequestObserver       │
         │                               │   creates notifications       │
         │                               │──────────────────────────────►│
         │◄── pickup_submitted notif ────│   (StaffNotification created) │
         │    (FCM push)                 │                               │
         │                               │                               │
         │                               │   Staff accepts pickup        │
         │                               │◄──────────────────────────────│
         │                               │   PATCH /staff/pickups/{id}   │
         │◄── pickup_accepted notif ─────│   status: accepted            │
         │    (FCM push)                 │                               │
         │                               │                               │
         │  GET /api/pickups/{id}        │   Staff goes en_route         │
         │  (poll staff location)        │◄──────────────────────────────│
         │──────────────────────────────►│   status: en_route            │
         │◄── staff_lat/lng returned ────│   (GPS updates every interval)│
         │                               │                               │
         │                               │   Staff marks picked_up       │
         │                               │◄──────────────────────────────│
         │◄── pickup_completed notif ────│   Staff creates laundry order │
         │    (FCM push)                 │──────────────────────────────►│
         │                               │   LaundryObserver fires       │
         │◄── laundry_received notif ────│                               │
         │    (FCM push)                 │                               │
         │                               │                               │
         │  GET /api/laundries           │   Staff updates status        │
         │  (track order)                │◄──────────────────────────────│
         │──────────────────────────────►│   washing → ready → paid      │
         │◄── order data returned ───────│   LaundryObserver fires       │
         │                               │   FCM push at each step       │
         │◄── washing_started notif ─────│                               │
         │◄── laundry_ready notif ───────│                               │
         │                               │                               │
         │  POST /api/payment-proof      │   Staff verifies payment      │
         │  (GCash screenshot)           │◄──────────────────────────────│
         │──────────────────────────────►│   PaymentVerificationCtrl     │
         │◄── payment_received notif ────│   marks order as paid         │
         │    (FCM push)                 │                               │
         │                               │                               │
         │                               │   Staff marks completed       │
         │◄── laundry_completed notif ───│◄──────────────────────────────│
         │    (FCM push)                 │                               │
         │                               │                               │
         │  POST /api/ratings            │                               │
         │  (rate completed order)       │                               │
         │──────────────────────────────►│                               │
```

### Figure 10: Database Relationship Overview

```
                    branches (1)
                        │
          ┌─────────────┼──────────────────┐
          │             │                  │
          ▼             ▼                  ▼
       users (N)   delivery_fees (1)   promotions (N)
          │
          │ (staff_id / created_by / assigned_to)
          ▼
     laundries (N) ◄────────────── customers (1)
          │    │                        │
          │    └──── pickup_requests (N)◄┘
          │
     ┌────┼────────────────┐
     │    │                │
     ▼    ▼                ▼
payments  laundry_addon   laundry_status
          (pivot)          histories
          │
          ▼
        add_ons

customers (1) ──► notifications (N)
customers (1) ──► device_tokens (N)
laundries (N) ──► notifications (N)
pickup_requests (N) ──► notifications (N)
pickup_requests (N) ──► pickup_status_histories (N)
laundries (N) ──► unclaimed_laundries (1)
laundries (N) ──► payment_proofs (N)
customers (1) ──► customer_ratings (N)
customers (1) ──► customer_addresses (N)
customers (1) ──► customer_payment_methods (N)
```
