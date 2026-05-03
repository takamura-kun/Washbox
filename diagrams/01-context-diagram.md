# Context Diagram - WashBox System

## Visual Context Diagram (System Boundary)

```mermaid
graph TB
    Customer["👤 Customer"]
    Admin["🔑 Admin Staff"]
    Branch["🏪 Branch Manager"]
    Driver["🚗 Delivery Driver"]
    
    FCM["📱 Firebase Cloud Messaging"]
    GoogleMaps["🗺️ Google Maps API"]
    GCash["💳 GCash Payment Gateway"]
    Email["📧 Email Service"]
    
    WashBox["<b>WashBox System</b><br/>Laundry Management Platform"]
    
    Customer -->|Login, Place Orders, Track, Pay| WashBox
    Admin -->|Manage System, Reports, Users| WashBox
    Branch -->|Manage Pickups, Inventory, Orders| WashBox
    Driver -->|Update Location, Mark Pickups| WashBox
    
    WashBox -->|Send Notifications| FCM
    WashBox -->|Route Optimization| GoogleMaps
    WashBox -->|Process Payments| GCash
    WashBox -->|Send Alerts| Email
    
    FCM -.->|Push to Mobile| Customer
    FCM -.->|Push to Mobile| Driver
    GoogleMaps -.->|Location Data| WashBox
    GCash -.->|Payment Status| WashBox
    Email -.->|Delivery Status| Customer
    
    style WashBox fill:#3b82f6,stroke:#1e40af,stroke-width:4px,color:#fff
    style Customer fill:#10b981,stroke:#047857,stroke-width:2px,color:#fff
    style Admin fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style Branch fill:#8b5cf6,stroke:#6d28d9,stroke-width:2px,color:#fff
    style Driver fill:#06b6d4,stroke:#0891b2,stroke-width:2px,color:#fff
    style FCM fill:#ec4899,stroke:#be185d,stroke-width:2px,color:#fff
    style GoogleMaps fill:#f97316,stroke:#c2410c,stroke-width:2px,color:#fff
    style GCash fill:#06b6d4,stroke:#0891b2,stroke-width:2px,color:#fff
    style Email fill:#6366f1,stroke:#4f46e5,stroke-width:2px,color:#fff
```

## Key Interactions

| Actor | System Interface | Primary Functions |
|-------|-----------------|-------------------|
| **Customer** | Mobile App | Browse services, request pickups, pay, track orders |
| **Admin Staff** | Web Dashboard | System management, user control, reports |
| **Branch Manager** | Web Dashboard | Inventory, order processing, staff management |
| **Delivery Driver** | Mobile App | Real-time tracking, pickup confirmation, location updates |

## External Services

| Service | Purpose | Data Exchange |
|---------|---------|----------------|
| **Firebase** | Push notifications | Token registration, notification payload |
| **Google Maps** | Location & routing | GPS coordinates, route optimization |
| **GCash** | Payment processing | Transaction ID, payment status |
| **Email** | Notifications | Alerts, receipts, status updates |

## System Scope

- **In Scope**: Customer registration, order management, pickup scheduling, inventory tracking, payment verification, delivery management
- **Out of Scope**: Actual cash handling, laundry processing machines, physical logistics

---

**Note**: This context diagram shows the WashBox system as a black box with external actors and services interacting at the boundaries.
