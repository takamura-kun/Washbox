# WashBox - Detailed Data Flow Diagram (Level 0 & Level 1)

## Level 0 - System Context Data Flow

```mermaid
graph TB
    subgraph External["External Entities"]
        Customer["👤 Customer<br/>Mobile App User"]
        Admin["👨‍💼 Admin<br/>Dashboard User"]
        BranchStaff["👨‍💼 Branch Staff<br/>Operational User"]
        Driver["🚗 Driver<br/>Delivery Personnel"]
        Firebase["🔔 Firebase<br/>FCM Service"]
        GCash["💳 GCash<br/>Payment Gateway"]
        GoogleMaps["🗺️ Google Maps<br/>Location Service"]
    end
    
    subgraph System["WashBox System"]
        WashBox["🏢 WashBox<br/>Platform"]
    end
    
    Customer -->|Login, Pickup Requests| WashBox
    WashBox -->|Notifications, Status| Customer
    
    Admin -->|Manage System| WashBox
    WashBox -->|Reports, Analytics| Admin
    
    BranchStaff -->|Accept, Process| WashBox
    WashBox -->|Tasks, Inventory| BranchStaff
    
    Driver -->|Accept Pickup/Delivery| WashBox
    WashBox -->|Route, Location| Driver
    
    Firebase -.->|Send Push Notifications| WashBox
    WashBox -->|Token Registration| Firebase
    
    GCash -.->|Payment Confirmation| WashBox
    WashBox -->|Process Payment| GCash
    
    GoogleMaps -.->|Distance/Duration Data| WashBox
    WashBox -->|Location Queries| GoogleMaps
```

---

## Level 1 - Detailed Process Decomposition

```mermaid
graph TB
    subgraph External["External Entities"]
        Customer["👤 Customer"]
        Admin["👨‍💼 Admin"]
        BranchStaff["👨‍💼 Branch"]
        Driver["🚗 Driver"]
        Firebase["🔔 Firebase"]
        GCash["💳 GCash"]
        Maps["🗺️ Maps"]
    end
    
    subgraph ProcessAuth["P1: Authentication & Account Management"]
        P1A["P1.1 User Registration"]
        P1B["P1.2 Login/Logout"]
        P1C["P1.3 Profile Management"]
    end
    
    subgraph ProcessService["P2: Service & Promotion Management"]
        P2A["P2.1 Manage Services"]
        P2B["P2.2 Manage Promotions"]
        P2C["P2.3 Apply Discounts"]
    end
    
    subgraph ProcessPickup["P3: Pickup Request Management"]
        P3A["P3.1 Create Pickup Request"]
        P3B["P3.2 Manage Pickup Status"]
        P3C["P3.3 Assign to Driver"]
    end
    
    subgraph ProcessLaundry["P4: Laundry Order Processing"]
        P4A["P4.1 Create Laundry Order"]
        P4B["P4.2 Track Laundry Status"]
        P4C["P4.3 Update Progress"]
    end
    
    subgraph ProcessPayment["P5: Payment Verification"]
        P5A["P5.1 Submit Payment Proof"]
        P5B["P5.2 Verify Payment"]
        P5C["P5.3 Update Payment Status"]
    end
    
    subgraph ProcessInventory["P6: Inventory Management"]
        P6A["P6.1 Track Stock Levels"]
        P6B["P6.2 Process Deductions"]
        P6C["P6.3 Reorder Management"]
    end
    
    subgraph ProcessTracking["P7: Location & Route Tracking"]
        P7A["P7.1 Real-time GPS Tracking"]
        P7B["P7.2 Route Optimization"]
        P7C["P7.3 ETA Calculation"]
    end
    
    subgraph ProcessNotification["P8: Notification Distribution"]
        P8A["P8.1 Queue Notifications"]
        P8B["P8.2 Send via FCM"]
        P8C["P8.3 Track Delivery"]
    end
    
    subgraph ProcessAnalytics["P9: Reporting & Analytics"]
        P9A["P9.1 Generate Reports"]
        P9B["P9.2 Analytics Processing"]
        P9C["P9.3 Dashboard Updates"]
    end
    
    subgraph DataStores["Data Stores"]
        D1["D1: Users"]
        D2["D2: Pickups"]
        D3["D3: Laundries"]
        D4["D4: Payments"]
        D5["D5: Services"]
        D6["D6: Inventory"]
        D7["D7: Notifications"]
        D8["D8: DeviceTokens"]
        D9["D9: Transactions"]
    end
    
    %% Authentication Flows
    Customer -->|Login Credentials| P1B
    P1B -->|Authentication| D1
    D1 -->|Token| P1B
    P1B -->|Auth Status| Customer
    
    %% Pickup Flows
    Customer -->|Request Pickup| P3A
    P3A -->|Create Request| D2
    D2 -->|Pickup Data| P3B
    P3B -->|Accept/Reject| BranchStaff
    BranchStaff -->|Assign Driver| P3C
    P3C -->|Update Status| D2
    D2 -->|Notification Event| P8A
    
    %% Service & Promotion
    Admin -->|Manage| P2A
    P2A -->|Store| D5
    D5 -->|Available Services| Customer
    Customer -->|Browse Services| P2B
    P2B -->|Check Eligibility| P2C
    P2C -->|Apply Discount| D5
    
    %% Laundry Order
    P3A -->|Create Order| P4A
    P4A -->|Store Order| D3
    D3 -->|Order Status| P4B
    BranchStaff -->|Update Process| P4C
    P4C -->|Status Change| D3
    
    %% Payment Processing
    Customer -->|Submit Proof| P5A
    P5A -->|Verify Details| P5B
    P5B -->|Check QR/Amount| GCash
    GCash -->|Payment Status| P5B
    P5B -->|Update Status| D4
    D4 -->|Payment Confirmation| P5C
    P5C -->|Record Transaction| D9
    
    %% Inventory Management
    BranchStaff -->|Request Stock| P6A
    P6A -->|Check Levels| D6
    D6 -->|Stock Data| P6B
    P4C -->|Deduct Items| P6B
    P6B -->|Update Stock| D6
    D6 -->|Low Stock Alert| P6C
    P6C -->|Reorder Notification| Admin
    
    %% Location Tracking
    Driver -->|GPS Data| P7A
    P7A -->|Real-time Update| D2
    D2 -->|Track Status| Maps
    Maps -->|Distance Data| P7B
    P7B -->|Optimized Route| D2
    Maps -->|ETA Data| P7C
    P7C -->|Update ETA| P8A
    
    %% Notifications
    D2 -->|Status Change| P8A
    D3 -->|Order Update| P8A
    D4 -->|Payment Update| P8A
    P8A -->|Queue Event| D7
    D7 -->|Get Token| D8
    D8 -->|Device Token| P8B
    P8B -->|Send Notification| Firebase
    Firebase -->|Push Message| Customer
    P8C -->|Delivery Status| D7
    
    %% Analytics & Reporting
    D1 -->|User Data| P9A
    D2 -->|Pickup Data| P9A
    D3 -->|Laundry Data| P9A
    D4 -->|Payment Data| P9A
    D9 -->|Transaction Data| P9A
    P9A -->|Generate Reports| P9B
    P9B -->|Process Analytics| P9C
    P9C -->|Dashboard Data| Admin

```

---

## Level 2 - Payment Verification Process Detail

```mermaid
graph TB
    subgraph Input["Input"]
        I1["Customer<br/>Payment Proof"]
    end
    
    subgraph P5_Detail["P5: Payment Verification Process"]
        P5_1["P5.1.1<br/>Receive Proof"]
        P5_1 -->|Image Data| P5_2["P5.1.2<br/>Extract QR Code"]
        
        P5_2 -->|QR Data| P5_3["P5.1.3<br/>Validate QR"]
        P5_3 -->|Valid?| P5_4{Decision:<br/>Valid QR?}
        P5_4 -->|No| P5_Reject["❌ Reject Proof"]
        P5_4 -->|Yes| P5_5["P5.2.1<br/>Extract Amount"]
        
        P5_5 -->|Amount| P5_6["P5.2.2<br/>Verify Amount"]
        P5_6 -->|Match?| P5_7{Decision:<br/>Correct Amount?}
        P5_7 -->|No| P5_Reject
        P5_7 -->|Yes| P5_8["P5.2.3<br/>Check Timestamp"]
        
        P5_8 -->|Time| P5_9["P5.2.4<br/>Validate Time"]
        P5_9 -->|Valid?| P5_10{Decision:<br/>Within Window?}
        P5_10 -->|No| P5_Reject
        P5_10 -->|Yes| P5_11["P5.3.1<br/>Verify Merchant"]
        
        P5_11 -->|Merchant Data| P5_12["P5.3.2<br/>Check Merchant"]
        P5_12 -->|Valid?| P5_13{Decision:<br/>Correct Merchant?}
        P5_13 -->|No| P5_Reject
        P5_13 -->|Yes| P5_14["✅ Approve Proof"]
    end
    
    subgraph DataStores["Data Stores"]
        D3["D3: Laundries"]
        D4["D4: Payments"]
        D5["D5: Services"]
    end
    
    subgraph Output["Output"]
        O1["Payment Status<br/>Updated"]
        O2["Notification<br/>Sent"]
    end
    
    I1 -->|Proof Image| P5_1
    D3 -->|Order Amount| P5_5
    D5 -->|Merchant Info| P5_11
    
    P5_Reject -->|Status: Rejected| D4
    P5_14 -->|Status: Approved| D4
    D4 -->|Update| O1
    D4 -->|Trigger| O2
```

---

## Level 2 - Pickup Request Management Detail

```mermaid
graph TB
    subgraph Input["Input"]
        I1["Customer<br/>Pickup Request"]
    end
    
    subgraph P3_Detail["P3: Pickup Request Management"]
        P3_1["P3.1.1<br/>Validate Request"]
        P3_1 -->|Address Data| P3_2["P3.1.2<br/>Verify Address"]
        
        P3_2 -->|Valid?| P3_3{Decision:<br/>Valid Address?}
        P3_3 -->|No| P3_Reject["❌ Reject Request"]
        P3_3 -->|Yes| P3_4["P3.1.3<br/>Create Request"]
        
        P3_4 -->|Request Data| P3_5["P3.2.1<br/>Assign Branch"]
        P3_5 -->|Branch ID| P3_6["P3.2.2<br/>Notify Branch"]
        
        P3_6 -->|Branch Notification| P3_7{Decision:<br/>Branch Accept?}
        P3_7 -->|No| P3_Reject
        P3_7 -->|Yes| P3_8["P3.3.1<br/>Select Driver"]
        
        P3_8 -->|Driver ID| P3_9["P3.3.2<br/>Calculate Fee"]
        P3_9 -->|Fee| P3_10["P3.3.3<br/>Notify Driver"]
        
        P3_10 -->|Driver Accept?| P3_11{Decision:<br/>Driver Accept?}
        P3_11 -->|No| P3_8
        P3_11 -->|Yes| P3_12["✅ Confirm Request"]
    end
    
    subgraph DataStores["Data Stores"]
        D1["D1: Users"]
        D2["D2: Pickups"]
        D5["D5: Services"]
        D6["D6: Drivers"]
        D8["D8: Notifications"]
    end
    
    subgraph Output["Output"]
        O1["Pickup Confirmed"]
        O2["Driver Assigned"]
        O3["Notifications Sent"]
    end
    
    I1 -->|Request Data| P3_1
    D1 -->|Customer Info| P3_2
    D5 -->|Service Info| P3_4
    D6 -->|Driver List| P3_8
    
    P3_Reject -->|Status: Rejected| D2
    P3_12 -->|Status: Confirmed| D2
    D2 -->|Update| O1
    D2 -->|Driver| O2
    D8 -->|Notifications| O3
```

---

## Level 2 - Inventory Stock Deduction Detail

```mermaid
graph TB
    subgraph Input["Input"]
        I1["Laundry Order<br/>Items"]
    end
    
    subgraph P6_Detail["P6: Inventory Stock Deduction"]
        P6_1["P6.2.1<br/>Get Order Items"]
        P6_1 -->|Item List| P6_2["P6.2.2<br/>Check Stock"]
        
        P6_2 -->|Stock Level| P6_3{Decision:<br/>Stock Available?}
        P6_3 -->|No| P6_Alert["⚠️ Low Stock Alert"]
        P6_3 -->|Yes| P6_4["P6.2.3<br/>Reserve Items"]
        
        P6_4 -->|Reserved| P6_5["P6.2.4<br/>Deduct Stock"]
        P6_5 -->|Updated Level| P6_6["P6.2.5<br/>Record Movement"]
        
        P6_6 -->|Check Threshold| P6_7{Decision:<br/>Below Minimum?}
        P6_7 -->|Yes| P6_8["P6.3.1<br/>Create Reorder"]
        P6_7 -->|No| P6_9["✅ Complete Deduction"]
        
        P6_8 -->|Reorder| P6_9
    end
    
    subgraph DataStores["Data Stores"]
        D3["D3: Laundries"]
        D6["D6: Inventory"]
        D7["D7: Stock Movements"]
        D8["D8: Reorder Queue"]
    end
    
    subgraph Output["Output"]
        O1["Stock Updated"]
        O2["Movement Recorded"]
        O3["Reorder Created"]
    end
    
    I1 -->|Items| P6_1
    D3 -->|Order ID| P6_1
    D6 -->|Stock Levels| P6_2
    
    P6_9 -->|Status: Deducted| D6
    D6 -->|Update| O1
    P6_6 -->|Movement Data| D7
    D7 -->|Record| O2
    P6_8 -->|Reorder| D8
    D8 -->|Create| O3
```

---

## Data Store Dictionary

| ID | Store Name | Primary Data | Access Frequency |
|----|-----------|--------------|------------------|
| D1 | Users | Customer, Admin, Driver profiles | Very High |
| D2 | Pickups | Pickup requests, status, assignments | High |
| D3 | Laundries | Order details, items, status | High |
| D4 | Payments | Payment proofs, status, verification | High |
| D5 | Services | Service definitions, pricing, promos | Medium |
| D6 | Inventory | Stock levels, movements, reorders | High |
| D7 | Notifications | Notification queue, delivery status | Very High |
| D8 | DeviceTokens | FCM tokens, device info | Medium |
| D9 | Transactions | Financial records, audit trail | Medium |

---

## Data Flow Summary

### Primary Data Flows
1. **Authentication Flow** - Login credentials → User validation → Auth token
2. **Pickup Flow** - Request → Validation → Assignment → Confirmation
3. **Payment Flow** - Proof submission → Verification → Approval → Notification
4. **Inventory Flow** - Order items → Stock check → Deduction → Movement record
5. **Notification Flow** - Event trigger → Queue → Token retrieval → FCM send
6. **Tracking Flow** - GPS data → Route optimization → ETA update → Status change

### Data Volume Estimates (Daily)
- **Pickups:** 2,000 requests/day
- **Laundries:** 8,000 orders/day
- **Payments:** 4,000 proofs/day
- **Notifications:** 50,000 messages/day
- **Location Updates:** 100,000 GPS points/day

### Critical Data Paths
- Payment verification (< 5 second response)
- Pickup assignment (< 30 second response)
- Real-time tracking (< 2 second update)
- Inventory deduction (atomic, no partial updates)
