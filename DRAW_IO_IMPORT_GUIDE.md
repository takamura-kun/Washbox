# WashBox Data Flow Diagram - Draw.io Import Guide

## Files Created

1. **WashBox_DFD_PlantUML.txt** - PlantUML code for Draw.io
2. Located in: `/vercel/share/v0-project/diagrams/`

## How to Import to Draw.io

### Method 1: Using PlantUML Plugin (Recommended)

1. Go to https://draw.io
2. Click **File** → **New**
3. Click **Plugins** (bottom left)
4. Search for "PlantUML" and enable it
5. Click **Insert** → **PlantUML**
6. Copy the entire PlantUML code from WashBox_DFD_PlantUML.txt
7. Paste it into the PlantUML dialog
8. Click **Insert**
9. The diagram will automatically generate

### Method 2: Direct Import

1. Go to https://draw.io
2. Click **File** → **New**
3. Click **File** → **Import from**
4. Choose "URL" and enter the path to PlantUML file OR
5. Copy-paste the code directly into a new document

### Method 3: Using Online PlantUML Editor

1. Go to http://www.plantuml.com/plantuml/uml/
2. Paste the PlantUML code
3. Right-click the rendered diagram
4. Select "View SVG" or "Download PNG"
5. Import the image into Draw.io

## Diagram Components

### External Entities (Light Blue)
- Customer App
- Admin Panel
- Branch Staff
- Driver App
- Firebase FCM
- GCash API
- Email Service

### Core Processes (Yellow) - 12 Processes
- P1: Authentication & Profile
- P2: Service & Promotion Management
- P3: Pickup Request Management
- P4: Laundry Order Processing
- P5: Payment Verification
- P6: Inventory Management
- P7: Location Tracking
- P8: Status & Notifications
- P9: Financial Tracking
- P10: Reporting & Analytics
- P11: Device Token Management
- P12: Audit Logging

### Data Stores (Light Green) - 11 Databases
- D1: Customer Accounts (15K)
- D2: Services & Categories (300)
- D3: Pickup Requests (50K)
- D4: Laundry Orders (100K)
- D5: Payment Records (80K)
- D6: Inventory Items (5K)
- D7: Location History (1M)
- D8: Promotions (500)
- D9: Device Tokens (20K)
- D10: Financial Transactions (200K)
- D11: Audit Logs (1M)

## Data Flow Legend

| Symbol | Meaning |
|--------|---------|
| → | Direct data flow |
| ←→ | Bidirectional flow |
| -.→ | Trigger/async flow |
| → Label | Data transferred |

## Key Data Flows

### Authentication Flow
Customer App → P1 (login) → D1 (store)

### Pickup Workflow
Customer → P3 → Branch/Driver → P4 → P5 (payment)

### Payment Verification
Customer proof → P5 → GCash validation → D5 → Notification to P8

### Notification Distribution
Status changes in P3/P4/P5 → P8 → D9 (tokens) → Firebase → Mobile

### Inventory Management
P4 (order) → P6 → D6 (deduct stock) → Alert if low

### Location Tracking
Driver → P7 → D7 (store GPS) → P8 (real-time updates)

## Customization in Draw.io

After importing, you can:

1. **Add colors**
   - Right-click shape → Format
   - Change fill color, border, text color

2. **Rearrange**
   - Drag shapes to new positions
   - Auto-layout: Arrange → Layout

3. **Add details**
   - Double-click flows to add labels
   - Add notes: Insert → Comment

4. **Export**
   - File → Export as → PNG/SVG/PDF
   - File → Save as → draw.io format

## Tips

- Use View → Zoom to fit all shapes
- Use Ctrl+A to select all, then Arrange → Align for uniform layout
- Save frequently in Draw.io format
- Use View → Grid for alignment

## Contact & Support

For Draw.io support: https://support.draw.io
For WashBox diagram questions: Check SYSTEM_ARCHITECTURE_DIAGRAMS.md

