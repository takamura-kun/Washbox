## 🔍 WHERE TO SEE THE EXTRA WASH OPTION

### Quick Test Steps:

1. **Go to**: http://localhost/admin/laundries/create (or your domain)

2. **Fill these fields**:
   - Customer: Select any
   - Branch: Select any
   - Service: Select "FULL SERVICE" (Max: 8kg, ₱209/load)

3. **Enter EXCESS weight**:
   - Weight: Type **10** (or any number > 8)
   - Loads: Keep at **1**

4. **Look for the YELLOW WARNING BOX** that appears below the weight field:

```
┌─────────────────────────────────────────────────────────┐
│ ⚠️ Extra Load Required:                                 │
│ Weight (10.0kg) exceeds 8kg per load.                   │
│                                                          │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ ○ Add Extra Loads (Auto-calculated)                 │ │
│ │                                                      │ │
│ │ ○ Pay Extra Fee                                     │ │
│ │   ₱ [100.00]                                        │ │
│ │   e.g., Extra Wash fee                              │ │
│ └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

### ⚠️ IMPORTANT: The option ONLY appears when:
- ✅ Service has max_weight (FULL SERVICE = 8kg)
- ✅ Weight entered EXCEEDS max_weight (e.g., 10kg > 8kg)
- ✅ You're on the CREATE laundry page

### If you don't see it:
1. **Clear browser cache**: Press `Ctrl + Shift + R` (or `Cmd + Shift + R` on Mac)
2. **Check weight**: Make sure you entered MORE than 8kg
3. **Check service**: Make sure "FULL SERVICE" is selected
4. **Check console**: Press F12, look for errors

### Test it now:
1. Open: `/admin/laundries/create`
2. Select: FULL SERVICE
3. Enter weight: **10**
4. The yellow warning box should appear immediately!

---

## 📊 What Happens:

### Option 1: Add Extra Loads (Default)
- System calculates: 10kg ÷ 8kg = 2 loads needed
- Price: 2 loads × ₱209 = **₱418**

### Option 2: Pay Extra Fee
- Keep: 1 load × ₱209 = ₱209
- Add: Extra Wash Fee = ₱100
- Total: **₱309**

The shop saves money with Option 2! 💰
