# WashBox Laundry Management System (WLMS)
## Requirements Modeling

### Input-Process-Output (IPO) Model

#### **INPUT**
- **Customer Data**: Name, contact information, address, registration details
- **Laundry Items**: Type, weight, quantity, service specifications
- **Pickup Requests**: Location coordinates, preferred time, service type
- **Service Parameters**: Pricing rates, delivery fees, branch settings
- **Payment Information**: Amount, payment method, transaction details
- **Staff Actions**: Status updates, processing confirmations, notifications

#### **PROCESS**
- **Customer Registration**: Validate and store customer information
- **Laundry Order Creation**: Calculate pricing, assign tracking numbers
- **Status Tracking**: Update laundry through processing stages
- **Fee Calculation**: Compute per-load, per-piece, and delivery charges
- **Pickup Coordination**: Map locations, schedule collections
- **Notification Management**: Send automated push notifications
- **Payment Processing**: Handle transactions and receipts
- **Reporting**: Generate analytics and performance metrics

#### **OUTPUT**
- **Digital Receipts**: Itemized billing with tracking numbers
- **Status Updates**: Real-time laundry processing notifications
- **Location Maps**: Pickup request locations with route planning
- **Reports**: Branch performance, revenue, and operational analytics
- **Notifications**: Push messages for status changes and completions
- **Payment Confirmations**: Transaction receipts and records

### **PERFORMANCE REQUIREMENTS**
- **Response Time**: Web interface < 3 seconds, Mobile app < 2 seconds
- **Availability**: 99.5% uptime during business hours
- **Concurrent Users**: Support 50+ simultaneous users across branches
- **Data Storage**: Handle 10,000+ customer records and transactions
- **Notification Delivery**: Push notifications within 30 seconds

### **CONTROL REQUIREMENTS**
- **Authentication**: Role-based access (Owner, Staff, Customer)
- **Data Validation**: Input verification and error handling
- **Security**: Encrypted data transmission and storage
- **Backup**: Daily automated database backups
- **Audit Trail**: Transaction logging and user activity tracking

---

## Data and Process Modeling

### **Context Diagram**

```
                    WASHBOX LAUNDRY MANAGEMENT SYSTEM (WLMS)
                                    
    CUSTOMERS                                                    STAFF/OWNERS
        |                                                            |
        | Registration Data                                          | Laundry Processing
        | Pickup Requests                                            | Status Updates
        | Payment Info                                               | Reports Access
        |                                                            |
        v                                                            v
    ┌─────────────────────────────────────────────────────────────────────┐
    │                                                                     │
    │                    WASHBOX LAUNDRY                                  │
    │                   MANAGEMENT SYSTEM                                 │
    │                        (WLMS)                                       │
    │                                                                     │
    └─────────────────────────────────────────────────────────────────────┘
        ^                                                            ^
        |                                                            |
        | Status Notifications                                       | Analytics Reports
        | Digital Receipts                                           | Performance Metrics
        | Pickup Confirmations                                       | Customer Data
        |                                                            |
    MOBILE APP                                                   WEB DASHBOARD
    NOTIFICATIONS                                               MANAGEMENT SYSTEM
```

### **Level 0 Data Flow Diagram (System Overview)**

```
                           CUSTOMERS
                               |
                    Registration Data, Pickup Requests
                               |
                               v
    ┌─────────────────────────────────────────────────────────┐
    │  1.0                                                    │
    │  CUSTOMER                    Customer Data              │  ┌─────────────────┐
    
    │  MANAGEMENT          ────────────────────────────────►  │  │                 │
    │                                                         │  │   D1: CUSTOMER  │
    │                                                         │  │   DATABASE      │
    └─────────────────────────────────────────────────────────┘  │                 │
                               │                                  └─────────────────┘
                    Validated Customer Info                              │
                               │                                         │
                               v                                Customer Records
    ┌─────────────────────────────────────────────────────────┐         │
    │  2.0                                                    │         │
    │  LAUNDRY ORDER              Order Data                  │  ┌─────────────────┐
    │  PROCESSING          ────────────────────────────────►  │  │                 │
    │                                                         │  │   D2: ORDERS    │
    │                                                         │  │   DATABASE      │
    └─────────────────────────────────────────────────────────┘  │                 │
                               │                                  └─────────────────┘
                    Processing Updates                                   │
                               │                                         │
                               v                                Order Records
    ┌─────────────────────────────────────────────────────────┐         │
    │  3.0                                                    │         │
    │  NOTIFICATION               Notification Data           │  ┌─────────────────┐
    │  SYSTEM              ────────────────────────────────►  │  │                 │
    │                                                         │  │   D3: NOTIFICATIONS │
    │                                                         │  │   LOG           │
    └─────────────────────────────────────────────────────────┘  │                 │
                               │                                  └─────────────────┘
                    Push Notifications
                               │
                               v
                           CUSTOMERS
                        (Mobile Devices)
```

### **Level 1 Data Flow Diagram (Detailed Process)**

```
CUSTOMERS                    STAFF                           OWNERS
    │                         │                               │
    │ Registration            │ Laundry Items                 │ Report Requests
    │ Pickup Requests         │ Status Updates                │
    │                         │                               │
    v                         v                               v
┌─────────────┐         ┌─────────────┐                 ┌─────────────┐
│    1.1      │         │    2.1      │                 │    4.1      │
│  CUSTOMER   │         │  LAUNDRY    │                 │  REPORTING  │
│REGISTRATION │         │ PROCESSING  │                 │   SYSTEM    │
└─────────────┘         └─────────────┘                 └─────────────┘
    │                         │                               │
    │ Customer Data           │ Order Data                    │ Analytics Data
    │                         │                               │
    v                         v                               v
┌─────────────────────────────────────────────────────────────────────┐
│                        D1: DATABASE                                 │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐ │
│  │ CUSTOMERS   │  │   ORDERS    │  │  PICKUPS    │  │  PAYMENTS   │ │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘ │
└─────────────────────────────────────────────────────────────────────┘
    │                         │                               │
    │ Pricing Data            │ Status Data                   │ Report Data
    │                         │                               │
    v                         v                               v
┌─────────────┐         ┌─────────────┐                 ┌─────────────┐
│    1.2      │         │    3.1      │                 │    4.2      │
│   PRICING   │         │NOTIFICATION │                 │   ANALYTICS │
│CALCULATION  │         │   MANAGER   │                 │  GENERATOR  │
└─────────────┘         └─────────────┘                 └─────────────┘
    │                         │                               │
    │ Calculated Fees         │ Push Notifications            │ Reports
    │                         │                               │
    v                         v                               v
CUSTOMERS                 CUSTOMERS                       OWNERS
(Receipts)            (Mobile Notifications)           (Dashboard)
```

---

## System Flowchart

### **Main System Flow**

```
                            START
                              │
                              v
                    ┌─────────────────┐
                    │   USER LOGIN    │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  AUTHENTICATE   │
                    │     USER        │
                    └─────────────────┘
                              │
                              v
                         ┌─────────┐
                         │ VALID?  │
                         └─────────┘
                           │     │
                      YES  │     │ NO
                           │     │
                           v     v
                    ┌─────────────────┐
                    │  DETERMINE      │
                    │  USER ROLE      │
                    └─────────────────┘
                              │
                    ┌─────────┼─────────┐
                    │         │         │
                    v         v         v
            ┌─────────────┐ ┌─────────────┐ ┌─────────────┐
            │  CUSTOMER   │ │    STAFF    │ │   OWNER     │
            │  DASHBOARD  │ │  DASHBOARD  │ │  DASHBOARD  │
            └─────────────┘ └─────────────┘ └─────────────┘
                    │         │         │
                    v         v         v
            ┌─────────────┐ ┌─────────────┐ ┌─────────────┐
            │• Track      │ │• Process    │ │• View       │
            │  Laundry    │ │  Orders     │ │  Reports    │
            │• Request    │ │• Update     │ │• Manage     │
            │  Pickup     │ │  Status     │ │  Settings   │
            │• Make       │ │• Send       │ │• Monitor    │
            │  Payment    │ │  Notifications│ │  Operations │
            └─────────────┘ └─────────────┘ └─────────────┘
                    │         │         │
                    └─────────┼─────────┘
                              │
                              v
                    ┌─────────────────┐
                    │   UPDATE        │
                    │   DATABASE      │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │   SEND          │
                    │ NOTIFICATIONS   │
                    └─────────────────┘
                              │
                              v
                            END
```

### **Pickup Request Process Flow**

```
                            START
                              │
                              v
                    ┌─────────────────┐
                    │  CUSTOMER       │
                    │  OPENS MOBILE   │
                    │  APP            │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  SELECT         │
                    │  "REQUEST       │
                    │  PICKUP"        │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  ENTER PICKUP   │
                    │  DETAILS        │
                    │  (Location,     │
                    │   Time, etc.)   │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  CALCULATE      │
                    │  DELIVERY FEES  │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  SUBMIT         │
                    │  REQUEST        │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  STORE IN       │
                    │  DATABASE       │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  NOTIFY STAFF   │
                    │  VIA WEB        │
                    │  DASHBOARD      │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  STAFF VIEWS    │
                    │  PICKUP MAP     │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  STAFF          │
                    │  COLLECTS       │
                    │  LAUNDRY        │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  CREATE         │
                    │  LAUNDRY ORDER  │
                    │  IN SYSTEM      │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  CUSTOMER       │
                    │  RECEIVES       │
                    │  CONFIRMATION   │
                    └─────────────────┘
                              │
                              v
                            END
```

---

## Program Flowchart Highlights

### **Pricing Calculation Algorithm**

```
                            START
                              │
                              v
                    ┌─────────────────┐
                    │  GET LAUNDRY    │
                    │  ITEMS & WEIGHT │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  DETERMINE      │
                    │  SERVICE TYPE   │
                    └─────────────────┘
                              │
                    ┌─────────┼─────────┐
                    │         │         │
                    v         v         v
            ┌─────────────┐ ┌─────────────┐ ┌─────────────┐
            │  PER-LOAD   │ │ PER-PIECE   │ │   MIXED     │
            │  PRICING    │ │  PRICING    │ │  PRICING    │
            └─────────────┘ └─────────────┘ └─────────────┘
                    │         │         │
                    └─────────┼─────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  CALCULATE      │
                    │  BASE PRICE     │
                    └─────────────────┘
                              │
                              v
                         ┌─────────┐
                         │PICKUP/  │
                         │DELIVERY?│
                         └─────────┘
                           │     │
                      YES  │     │ NO
                           │     │
                           v     │
                    ┌─────────────────┐ │
                    │  ADD DELIVERY   │ │
                    │  FEES BASED ON  │ │
                    │  BRANCH RATES   │ │
                    └─────────────────┘ │
                              │         │
                              v         │
                    ┌─────────────────┐ │
                    │  APPLY          │ │
                    │  DISCOUNTS      │ │
                    │  (IF ANY)       │ │
                    └─────────────────┘ │
                              │         │
                              └─────────┼─────────┐
                                        │         │
                                        v         │
                              ┌─────────────────┐ │
                              │  CALCULATE      │ │
                              │  FINAL TOTAL    │ │
                              └─────────────────┘ │
                                        │         │
                                        v         │
                              ┌─────────────────┐ │
                              │  RETURN PRICE   │ │
                              │  BREAKDOWN      │ │
                              └─────────────────┘ │
                                        │         │
                                        v         │
                                      END ←───────┘
```

### **Notification System Flow**

```
                            START
                              │
                              v
                    ┌─────────────────┐
                    │  STATUS CHANGE  │
                    │  TRIGGERED      │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  GET CUSTOMER   │
                    │  DEVICE TOKEN   │
                    └─────────────────┘
                              │
                              v
                         ┌─────────┐
                         │ TOKEN   │
                         │ VALID?  │
                         └─────────┘
                           │     │
                      YES  │     │ NO
                           │     │
                           v     v
                    ┌─────────────────┐
                    │  PREPARE        │
                    │  NOTIFICATION   │
                    │  MESSAGE        │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  SEND VIA       │
                    │  FIREBASE FCM   │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  LOG            │
                    │  NOTIFICATION   │
                    │  STATUS         │
                    └─────────────────┘
                              │
                              v
                            END
```

### **Laundry Status Tracking Flow**

```
                            START
                              │
                              v
                    ┌─────────────────┐
                    │  LAUNDRY ORDER  │
                    │  CREATED        │
                    │  (Status:       │
                    │   RECEIVED)     │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  STAFF UPDATES  │
                    │  STATUS TO      │
                    │  "PROCESSED"    │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  SEND           │
                    │  NOTIFICATION   │
                    │  TO CUSTOMER    │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  STAFF UPDATES  │
                    │  STATUS TO      │
                    │  "READY FOR     │
                    │   PICKUP"       │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  SEND           │
                    │  NOTIFICATION   │
                    │  TO CUSTOMER    │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  CUSTOMER       │
                    │  MAKES PAYMENT  │
                    │  (Status: PAID) │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  STAFF UPDATES  │
                    │  STATUS TO      │
                    │  "COMPLETED"    │
                    └─────────────────┘
                              │
                              v
                    ┌─────────────────┐
                    │  SEND FINAL     │
                    │  NOTIFICATION   │
                    │  TO CUSTOMER    │
                    └─────────────────┘
                              │
                              v
                            END
```

This comprehensive requirements modeling covers all essential aspects of the WashBox Laundry Management System, providing clear visualization of data flow, system processes, and key algorithmic components.