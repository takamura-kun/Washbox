# WashBox System Diagrams Index

## Complete Visual Architecture Documentation

All diagrams have been created using **Mermaid** - a diagram-as-code tool that renders as beautiful, interactive SVG graphics.

---

## 📋 Diagram Collection

### 1. **Context Diagram** 
📍 Location: `diagrams/01-context-diagram.md`

**Purpose**: Shows the WashBox system as a black box with all external actors and services.

**Visual Elements**:
- Central WashBox system (blue)
- 4 External Actors: Customer, Admin, Branch Manager, Delivery Driver
- 4 External Services: Firebase, Google Maps, GCash, Email
- Data flows showing interactions at system boundaries

**Key Interactions**:
- Customer: Browse, request pickups, pay, track orders
- Admin: System management, user control, reports
- Branch: Inventory, order processing, staff management
- Driver: Location tracking, pickup confirmation

---

### 2. **Data Flow Diagram (DFD Level 0)**
📍 Location: `diagrams/02-data-flow-diagram.md`

**Purpose**: Decompose the system into 10 major processes with data flows and storage.

**10 Core Processes**:
1. **P1: Authentication & Auth** - Login, registration, token management
2. **P2: Service & Promotion Mgmt** - Service catalog, pricing, promotions
3. **P3: Pickup Request Mgmt** - Schedule, confirm, track pickups
4. **P4: Laundry Order Processing** - Order creation, pricing, validation
5. **P5: Payment Verification** - QR scanning, amount validation, approval
6. **P6: Inventory Management** - Stock tracking, reorder alerts
7. **P7: Location Tracking** - GPS logs, route optimization
8. **P8: Financial Tracking** - Revenue, expenses, settlements
9. **P9: Reporting & Analytics** - Dashboards, analytics, insights
10. **P10: Notification Distribution** - Multi-channel notification delivery

**9 Data Stores**:
- D1: Customer Data
- D2: Service Catalog
- D3: Pickup Requests
- D4: Laundry Orders
- D5: Payment Records
- D6: Inventory Stock
- D7: Location Trail
- D8: Financial Data
- D9: Notification Logs

---

### 3. **System Flowchart**
📍 Location: `diagrams/03-system-flowchart.md`

**Purpose**: Complete end-to-end customer journey from app login to order completion.

**8 System Stages**:
1. **Authentication** (< 1 min) - Login/Register
2. **Service Discovery** (Variable) - Browse services, apply promotions
3. **Pickup Request** (5-30 min) - Schedule pickup, confirm address
4. **Pickup Execution** (10-30 min) - Driver collects items
5. **Processing** (24-48 hrs) - Laundry service execution
6. **Ready for Delivery** (1-2 hrs) - Package and prepare
7. **Delivery** (30 min - 2 hrs) - Driver delivery with tracking
8. **Completion** (< 1 min) - Delivery confirmation, rating

**Async Notifications** throughout each stage:
- Order confirmation
- Ready for delivery
- Out for delivery
- Delivery complete

**Key Decision Points**:
- User Logged In?
- Payment Method? (GCash or Cash)
- Payment Approved?
- User Action? (Browse/Pickup/Payment/Track)

---

### 4. **Program Flowchart - Critical Functions** 
📍 Location: `diagrams/04-program-flowchart.md`

**Purpose**: Deep-dive into 5 critical business logic flows with detailed decision branches.

#### **Flow 1: Payment Verification**
- Image validation
- QR code extraction & decoding
- Amount verification
- Timestamp validation
- Merchant validation
- GCash API verification
- 6 validation checkpoints

#### **Flow 2: Inventory Stock Deduction**
- Item retrieval
- Stock availability check
- Stock reservation & locking
- Main inventory update
- Branch inventory update
- Reorder point check
- Automatic alerts

#### **Flow 3: Location Tracking & Route Optimization**
- GPS coordinate retrieval
- GPS validation
- Location storage (DB + cache)
- Distance calculation
- ETA estimation
- Route deviation detection
- Customer notifications

#### **Flow 4: Promotion Application & Validation**
- Code validation
- Promo existence check
- Status verification
- Date range check
- Usage limit check
- Customer eligibility check
- Service applicability check
- Discount calculation

#### **Flow 5: Notification Distribution**
- Event identification
- Recipient collection
- Device token retrieval
- Message formatting & localization
- Per-device FCM delivery
- Email fallback delivery
- Delivery logging
- DLR (Delivery Receipt) tracking

---

## 🎨 Color Coding System

All diagrams use consistent color coding for easy identification:

| Color | Meaning | Examples |
|-------|---------|----------|
| 🔵 Blue | Core Processes | Authentication, Order Processing |
| 🟢 Green | Success/Completion | Approved, Completed, Stored |
| 🟠 Orange | Decision Points | Validation, Checks, Conditions |
| 🔴 Red | Errors/Failures | Rejected, Failed, Invalid |
| 🟣 Purple | Management | Dashboard, Admin, Reports |
| 🌊 Cyan | External Services | Maps, Location, Tracking |
| 🩷 Pink | Notifications | Alerts, Messages, Events |
| 🟡 Yellow | Warnings | Reorder alerts, Deviations |

---

## 📊 How to Use These Diagrams

### For Developers
- **System Flowchart**: Understand the complete customer journey
- **Program Flowcharts**: Implement critical business logic with proper validation
- **DFD**: Understand data flow between components

### For Project Managers
- **Context Diagram**: Show stakeholders the system scope
- **System Flowchart**: Present project timeline and milestones
- **Data Flow**: Identify critical dependencies and risks

### For Business Analysts
- **Context Diagram**: Define system boundaries and scope
- **DFD Level 0**: Document major business processes
- **System Flowchart**: Trace customer journeys and pain points

### For System Architects
- **Data Flow Diagram**: Identify data stores and processes
- **Program Flowcharts**: Design robust error handling
- **All Diagrams**: Document system behavior and interactions

---

## 🔄 Integration Points

### External Integrations
1. **Firebase Cloud Messaging** - Push notifications (FCM)
2. **Google Maps API** - Location services and route optimization
3. **GCash Payment Gateway** - Payment processing and verification
4. **Email Service** - Transactional emails and alerts

### Internal Integrations
- Mobile app ↔ API backend
- Admin dashboard ↔ API backend
- Branch management system ↔ API backend
- Notification service ↔ All systems

---

## 📈 Key Metrics & KPIs

From these diagrams, you can derive:

| Metric | From | Purpose |
|--------|------|---------|
| **Order-to-Delivery Time** | System Flowchart | Measure operational efficiency |
| **Payment Verification Rate** | Program Flowchart 1 | Track payment success rate |
| **Inventory Accuracy** | Program Flowchart 2 | Measure stock management |
| **Delivery Success Rate** | Program Flowchart 3 | Track delivery reliability |
| **Promotion Redemption** | Program Flowchart 4 | Measure marketing effectiveness |
| **Notification Delivery Rate** | Program Flowchart 5 | Track communication reliability |

---

## 🚀 Deployment & Scalability Considerations

Based on these diagrams:

1. **Microservices Candidates**:
   - Notification Service (P10)
   - Payment Verification (P5)
   - Location Tracking (P7)
   - Reporting & Analytics (P9)

2. **Caching Opportunities**:
   - Service Catalog (D2)
   - Promotions (D2)
   - Customer Profiles (D1)
   - Location cache (D7)

3. **Async Processing**:
   - Payment verification (multi-step)
   - Notification distribution (batch)
   - Reporting queries (scheduled)

4. **Database Optimization**:
   - Location Trail (time-series data)
   - Payment Records (immutable audit log)
   - Notifications (high volume, TTL)

---

## 📝 Revision History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-05-04 | Initial comprehensive diagram suite |

---

## 🔗 Related Documentation

- `SYSTEM_ARCHITECTURE_DIAGRAMS.md` - Text-based architecture documentation
- `PROJECT_FIX_DELIVERY.md` - System fixes and improvements
- `NOTIFICATION_CONSOLIDATION_ROADMAP.md` - Notification system details
- `FIXES_IMPLEMENTATION_GUIDE.md` - Implementation guidance

---

**All diagrams are created with Mermaid and can be rendered in any markdown viewer or committed to version control for easy tracking.**

View diagrams by opening the individual markdown files in any markdown viewer or GitHub/GitLab interface.
