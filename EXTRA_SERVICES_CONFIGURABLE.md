# ✅ Extra Services - FULLY CONFIGURABLE!

## 🎉 What's New:

Owners and staff can now **configure the prices** for all extra services!

---

## 📍 How to Configure Prices:

### Step 1: Go to Settings
1. Navigate to: **Admin → Settings**
2. Look for: **"Extra Services Settings"** link
3. OR directly visit: `/admin/settings/extra-services`

### Step 2: Update Prices
You'll see a page with all 4 extra services:

```
┌─────────────────────────────────────────┐
│ 💧 Extra Wash                           │
│ Additional wash cycle for heavy items   │
│                                         │
│ Price: ₱ [100.00]                       │
│ Display Order: [1]                      │
│ Active: ☑️                              │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ ☀️ Extra Dry                            │
│ Extended drying for thick fabrics       │
│                                         │
│ Price: ₱ [80.00]                        │
│ Display Order: [2]                      │
│ Active: ☑️                              │
└─────────────────────────────────────────┘

... and so on
```

### Step 3: Save Changes
- Click **"Save Changes"** button
- Prices update immediately!
- New prices apply to all new laundry orders

---

## 🎯 What You Can Configure:

1. **Price** - Set any price you want (₱0 to ₱999,999)
2. **Display Order** - Change the order services appear
3. **Active/Inactive** - Turn services on/off with toggle

---

## 💡 Example Scenarios:

### Scenario 1: Increase Extra Wash Price
- Current: ₱100
- Change to: ₱150
- Save → All new orders use ₱150

### Scenario 2: Disable Extra Rinse
- Toggle OFF the "Active" switch
- Save → Extra Rinse won't show on create form

### Scenario 3: Reorder Services
- Change Display Order numbers
- Save → Services appear in new order

---

## 🔄 How It Works:

### Database Storage:
```
extra_service_settings table:
- service_key: 'extra_wash'
- service_name: 'Extra Wash'
- description: 'Additional wash cycle...'
- price: 100.00
- icon: 'bi-droplet-fill'
- color: 'primary'
- is_active: true
- display_order: 1
```

### On Create Laundry Page:
- System loads prices from database
- Shows only active services
- Displays in order specified
- Uses current prices

### When Saving Laundry:
- Selected services saved as JSON
- Total calculated and added to order
- Stored in notes field for reference

---

## 📊 Default Prices:

| Service | Default Price | Description |
|---------|--------------|-------------|
| Extra Wash | ₱100 | Additional wash cycle |
| Extra Dry | ₱80 | Extended drying |
| Extra Rinse | ₱50 | Additional rinse |
| Extra Spin | ₱60 | Extra spin cycle |

---

## 🚀 Quick Access:

**Settings Page:**
```
/admin/settings/extra-services
```

**From Navigation:**
```
Admin → Settings → Extra Services Settings
```

---

## ✨ Benefits:

✅ **No code changes needed** - Update prices anytime
✅ **Instant updates** - Changes apply immediately
✅ **Full control** - Enable/disable services
✅ **Flexible pricing** - Set any price you want
✅ **Easy management** - Simple form interface

---

## 🎉 You're All Set!

1. ✅ Extra services created in database
2. ✅ Settings page ready
3. ✅ Create laundry form loads from database
4. ✅ Prices fully configurable
5. ✅ Staff can manage prices

**Go to Settings → Extra Services and try it out!** 🚀
