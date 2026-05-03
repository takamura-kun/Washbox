# Program Flowchart - Critical Functional Flows

## 1. Payment Verification Flow

```mermaid
graph TD
    Start1([Payment Proof<br/>Received])
    
    Validate1{Image<br/>Valid?}
    ValidateExit1["❌ Reject Proof<br/>- Invalid format<br/>- Notify customer"]
    
    ExtractQR["📸 Extract QR Code<br/>from Screenshot"]
    QRValid{QR Code<br/>Valid?}
    QRFail["❌ QR Invalid<br/>- Decode failed<br/>- Request retry"]
    
    DecodeQR["🔍 Decode QR Data<br/>- Transaction ID<br/>- Timestamp<br/>- Merchant"]
    
    CheckAmount{Amount<br/>Matches?}
    AmountFail["❌ Amount Mismatch<br/>- Log discrepancy<br/>- Manual review"]
    
    CheckTimestamp{Within<br/>24 hrs?}
    TimeFail["❌ Expired<br/>- Too old<br/>- Request new"]
    
    CheckMerchant{Merchant<br/>Valid?}
    MerchantFail["❌ Wrong Merchant<br/>- Not GCash partner<br/>- Reject"]
    
    VerifyGCash["☁️ Query GCash API<br/>- Check transaction"]
    GCashMatch{Status<br/>Confirmed?}
    GCashFail["❌ GCash Mismatch<br/>- Transaction not found"]
    
    Approve["✅ APPROVED<br/>- Update payment status<br/>- Unlock order<br/>- Send notification"]
    
    End1([Payment Verified])
    
    Start1 --> Validate1
    Validate1 -->|No| ValidateExit1
    Validate1 -->|Yes| ExtractQR
    
    ExtractQR --> QRValid
    QRValid -->|No| QRFail
    QRValid -->|Yes| DecodeQR
    
    DecodeQR --> CheckAmount
    CheckAmount -->|No| AmountFail
    CheckAmount -->|Yes| CheckTimestamp
    
    CheckTimestamp -->|No| TimeFail
    CheckTimestamp -->|Yes| CheckMerchant
    
    CheckMerchant -->|No| MerchantFail
    CheckMerchant -->|Yes| VerifyGCash
    
    VerifyGCash --> GCashMatch
    GCashMatch -->|No| GCashFail
    GCashMatch -->|Yes| Approve
    
    Approve --> End1
    
    ValidateExit1 --> End1
    QRFail --> End1
    AmountFail --> End1
    TimeFail --> End1
    MerchantFail --> End1
    GCashFail --> End1
    
    style Start1 fill:#3b82f6,stroke:#1e40af,stroke-width:2px,color:#fff
    style End1 fill:#10b981,stroke:#047857,stroke-width:2px,color:#fff
    style Approve fill:#10b981,stroke:#047857,stroke-width:2px,color:#fff
    style Validate1 fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style QRValid fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style CheckAmount fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style CheckTimestamp fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style CheckMerchant fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style GCashMatch fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
```

## 2. Inventory Stock Deduction Flow

```mermaid
graph TD
    Start2([Order<br/>Confirmed])
    
    GetItems["📦 Get Order Items<br/>- Service type<br/>- Quantity<br/>- Special needs"]
    
    CheckStock{Sufficient<br/>Stock?}
    NoStock["❌ Out of Stock<br/>- Cancel order<br/>- Refund customer"]
    
    Reserve["🔒 Reserve Stock<br/>- Lock quantity<br/>- Create hold record"]
    
    UpdateMain["📊 Update Main Inventory<br/>- Deduct from general stock<br/>- Record deduction"]
    
    UpdateBranch["🏪 Update Branch Stock<br/>- Deduct from branch<br/>- Log location"]
    
    CheckReorder{Below<br/>Reorder<br/>Point?}
    
    Reorder["⚠️ Create Reorder Alert<br/>- Notify manager<br/>- Generate PO"]
    
    LogAudit["📝 Log Audit Trail<br/>- Timestamp<br/>- User<br/>- Quantity<br/>- Reason"]
    
    Notify["📬 Notify Branch<br/>- Items reserved<br/>- Ready to process"]
    
    End2([Stock<br/>Deducted])
    
    Start2 --> GetItems
    GetItems --> CheckStock
    
    CheckStock -->|No| NoStock
    NoStock --> End2
    
    CheckStock -->|Yes| Reserve
    Reserve --> UpdateMain
    UpdateMain --> UpdateBranch
    UpdateBranch --> CheckReorder
    
    CheckReorder -->|Yes| Reorder
    Reorder --> LogAudit
    
    CheckReorder -->|No| LogAudit
    LogAudit --> Notify
    Notify --> End2
    
    style Start2 fill:#3b82f6,stroke:#1e40af,stroke-width:2px,color:#fff
    style End2 fill:#10b981,stroke:#047857,stroke-width:2px,color:#fff
    style CheckStock fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style CheckReorder fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style NoStock fill:#ef4444,stroke:#991b1b,stroke-width:2px,color:#fff
    style Reserve fill:#3b82f6,stroke:#1e40af,stroke-width:2px,color:#fff
    style Reorder fill:#f97316,stroke:#c2410c,stroke-width:2px,color:#fff
```

## 3. Location Tracking & Route Optimization

```mermaid
graph TD
    Start3([Driver Updates<br/>Location])
    
    GetCoord["🗺️ Get GPS Coordinates<br/>- Latitude<br/>- Longitude<br/>- Timestamp"]
    
    ValidateGPS{Coordinates<br/>Valid?}
    InvalidGPS["❌ Invalid GPS<br/>- Request retry<br/>- Mark failed"]
    
    StoreLocation["💾 Store Location Data<br/>- Database record<br/>- Cache entry"]
    
    CalcDistance["📏 Calculate Distance<br/>- Current to dest<br/>- Remaining distance"]
    
    UpdateETA["⏱️ Update ETA<br/>- Route optimization<br/>- Traffic analysis"]
    
    CheckDeviations{Route<br/>Deviation?}
    
    AlertDeviation["⚠️ Alert Customer<br/>- Unusual route<br/>- Driver reassignment"]
    
    SendNotif["📱 Send Location Update<br/>- Customer app<br/>- Real-time map"]
    
    LogTrail["📝 Log Location Trail<br/>- Audit history<br/>- Analytics data"]
    
    End3([Location<br/>Updated])
    
    Start3 --> GetCoord
    GetCoord --> ValidateGPS
    
    ValidateGPS -->|No| InvalidGPS
    InvalidGPS --> End3
    
    ValidateGPS -->|Yes| StoreLocation
    StoreLocation --> CalcDistance
    CalcDistance --> UpdateETA
    UpdateETA --> CheckDeviations
    
    CheckDeviations -->|Yes| AlertDeviation
    AlertDeviation --> SendNotif
    
    CheckDeviations -->|No| SendNotif
    SendNotif --> LogTrail
    LogTrail --> End3
    
    style Start3 fill:#06b6d4,stroke:#0891b2,stroke-width:2px,color:#fff
    style End3 fill:#10b981,stroke:#047857,stroke-width:2px,color:#fff
    style ValidateGPS fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style CheckDeviations fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style AlertDeviation fill:#f97316,stroke:#c2410c,stroke-width:2px,color:#fff
```

## 4. Promotion Application & Validation

```mermaid
graph TD
    Start4([Customer<br/>Applies Promo])
    
    GetPromo["🎟️ Get Promo Code<br/>- Validate format<br/>- Lookup code"]
    
    CodeExists{Promo<br/>Exists?}
    NoCode["❌ Invalid Code<br/>- Not found<br/>- Suggest valid codes"]
    
    CheckStatus{Status<br/>Active?}
    Inactive["❌ Promo Inactive<br/>- Expired<br/>- Not yet active"]
    
    CheckDates{Within<br/>Date Range?}
    OutOfDate["❌ Out of Date Range<br/>- Promo expired"]
    
    CheckUsage{Usage<br/>Limit OK?}
    ExceededUsage["❌ Limit Exceeded<br/>- Already used<br/>- Or max reached"]
    
    CheckEligible{Customer<br/>Eligible?}
    NotEligible["❌ Not Eligible<br/>- First-time only<br/>- Min spend not met"]
    
    CheckService{Service<br/>Applicable?}
    NotApplicable["❌ Not Applicable<br/>- Different category<br/>- Excluded service"]
    
    CalcDiscount["💰 Calculate Discount<br/>- Fixed/Percentage<br/>- Apply constraints"]
    
    ApplyPromo["✅ APPLIED<br/>- Update order total<br/>- Log transaction<br/>- Notify customer"]
    
    End4([Promo<br/>Applied])
    
    Start4 --> GetPromo
    GetPromo --> CodeExists
    
    CodeExists -->|No| NoCode
    NoCode --> End4
    
    CodeExists -->|Yes| CheckStatus
    CheckStatus -->|No| Inactive
    Inactive --> End4
    
    CheckStatus -->|Yes| CheckDates
    CheckDates -->|No| OutOfDate
    OutOfDate --> End4
    
    CheckDates -->|Yes| CheckUsage
    CheckUsage -->|No| ExceededUsage
    ExceededUsage --> End4
    
    CheckUsage -->|Yes| CheckEligible
    CheckEligible -->|No| NotEligible
    NotEligible --> End4
    
    CheckEligible -->|Yes| CheckService
    CheckService -->|No| NotApplicable
    NotApplicable --> End4
    
    CheckService -->|Yes| CalcDiscount
    CalcDiscount --> ApplyPromo
    ApplyPromo --> End4
    
    style Start4 fill:#3b82f6,stroke:#1e40af,stroke-width:2px,color:#fff
    style End4 fill:#10b981,stroke:#047857,stroke-width:2px,color:#fff
    style ApplyPromo fill:#10b981,stroke:#047857,stroke-width:2px,color:#fff
    style CodeExists fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style CheckStatus fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style CheckDates fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style CheckUsage fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style CheckEligible fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style CheckService fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
```

## 5. Notification Distribution Flow

```mermaid
graph TD
    Start5([Event<br/>Triggered])
    
    GetEvent["📌 Identify Event Type<br/>- Order confirmed<br/>- Payment received<br/>- Ready for delivery<br/>- etc."]
    
    GetRecipients["👥 Get Recipients<br/>- Customer ID<br/>- Driver ID<br/>- Branch staff<br/>- Admin alerts"]
    
    GetDevices["📱 Get Device Tokens<br/>- Active tokens<br/>- Preferred channels<br/>- Language"]
    
    FormatMsg["✍️ Format Message<br/>- Localization<br/>- Personalization<br/>- Variables inject"]
    
    ForEachDevice{For Each<br/>Device<br/>Token}
    
    SendFCM["🔔 Send via FCM<br/>- Queue message<br/>- Set priority<br/>- Add retry"]
    
    FCMSuccess{Sent<br/>OK?}
    FCMFail["⚠️ FCM Failed<br/>- Log error<br/>- Queue for retry"]
    
    SendEmail{Send Email<br/>Too?}
    EmailSend["📧 Send Email<br/>- Format HTML<br/>- Queue SMTP<br/>- Track delivery"]
    
    LogEvent["📝 Log Delivery<br/>- Timestamp<br/>- Status<br/>- Channel<br/>- Recipient"]
    
    LoopEnd{All<br/>Devices<br/>Done?}
    
    SendDLR["📊 Send DLR<br/>- Delivery receipt<br/>- Success count<br/>- Failures"]
    
    End5([Notifications<br/>Sent])
    
    Start5 --> GetEvent
    GetEvent --> GetRecipients
    GetRecipients --> GetDevices
    GetDevices --> FormatMsg
    
    FormatMsg --> ForEachDevice
    ForEachDevice -->|Yes| SendFCM
    
    SendFCM --> FCMSuccess
    FCMSuccess -->|No| FCMFail
    FCMSuccess -->|Yes| SendEmail
    
    FCMFail --> LogEvent
    SendEmail -->|Yes| EmailSend
    SendEmail -->|No| LogEvent
    
    EmailSend --> LogEvent
    LogEvent --> LoopEnd
    
    LoopEnd -->|No| ForEachDevice
    LoopEnd -->|Yes| SendDLR
    SendDLR --> End5
    
    style Start5 fill:#ec4899,stroke:#be185d,stroke-width:2px,color:#fff
    style End5 fill:#10b981,stroke:#047857,stroke-width:2px,color:#fff
    style SendFCM fill:#06b6d4,stroke:#0891b2,stroke-width:2px,color:#fff
    style EmailSend fill:#6366f1,stroke:#4f46e5,stroke-width:2px,color:#fff
    style ForEachDevice fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style LoopEnd fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style FCMSuccess fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
    style SendEmail fill:#f59e0b,stroke:#d97706,stroke-width:2px,color:#fff
```

---

## Summary

| Flow | Purpose | Key Validations | Success Criteria |
|------|---------|-----------------|-----------------|
| **Payment Verification** | Validate payment proof accuracy | Image quality, QR code, amount, timestamp, merchant | Payment approved & order unlocked |
| **Inventory Management** | Track and manage stock levels | Stock availability, reorder points | Items reserved & branch notified |
| **Location Tracking** | Real-time driver tracking | GPS validity, route optimization | Location updated & customer notified |
| **Promotion Validation** | Apply discounts correctly | Code validity, eligibility, usage limits | Discount calculated & applied |
| **Notification Distribution** | Deliver messages across channels | Device availability, message formatting | All recipients notified via FCM/Email |

**Note**: These program flows show critical business logic with decision branches, error handling, and async operations.
