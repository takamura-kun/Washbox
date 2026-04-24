# Extra Wash Feature - Test Guide

## How to See the Extra Wash Option

### Step 1: Go to Create Laundry Page
- Navigate to: **Admin → Laundries → Create New Laundry**
- Or directly: `http://your-domain/admin/laundries/create`

### Step 2: Fill the Form
1. **Select Customer**: Choose any customer
2. **Select Branch**: Choose any branch
3. **Select Service**: Choose a service with max weight (e.g., "Wash & Fold - 8kg max")

### Step 3: Trigger the Extra Weight Warning
1. **Enter Weight**: Enter a weight that EXCEEDS the service's max weight
   - Example: If service max is 8kg, enter **10kg** or **12kg**
2. **Set Loads**: Keep loads at 1

### Step 4: See the Options
Once weight exceeds the limit, you'll see:

```
⚠️ Extra Load Required:
Weight (10.0kg) exceeds 8kg per load.

Choose one:
○ Add Extra Loads (Auto-calculated)
○ Pay Extra Fee
  ₱ [100.00]
  e.g., Extra Wash fee
```

### Step 5: Test Both Options

**Option A: Add Extra Loads**
- Select "Add Extra Loads"
- System auto-calculates: 2 loads × ₱200 = ₱400

**Option B: Pay Extra Fee**
- Select "Pay Extra Fee"
- Change amount if needed (default ₱100)
- Total: 1 load (₱200) + Extra Fee (₱100) = ₱300

---

## If You Don't See It

### Check Your Service Settings
The service MUST have a `max_weight` value set:

```sql
-- Check services with max weight
SELECT id, name, max_weight, price_per_load 
FROM services 
WHERE max_weight IS NOT NULL AND max_weight > 0;
```

### Common Issues:
1. ❌ Service has no max_weight → No warning appears
2. ❌ Weight is BELOW max → No warning appears
3. ❌ Using per-piece service → Weight is hidden
4. ❌ JavaScript not loaded → Check browser console

---

## Quick Test Example

### Service: "Wash & Fold" 
- Max Weight: 8kg
- Price: ₱200/load

### Test Case 1: Normal Weight
- Weight: 7kg
- Loads: 1
- Result: ✅ No warning, Total = ₱200

### Test Case 2: Excess Weight - Extra Loads
- Weight: 12kg
- Loads: 1 (auto-adjusted to 2)
- Option: "Add Extra Loads"
- Result: ⚠️ Warning shown, Total = ₱400 (2 loads)

### Test Case 3: Excess Weight - Extra Fee
- Weight: 12kg
- Loads: 1
- Option: "Pay Extra Fee" (₱100)
- Result: ⚠️ Warning shown, Total = ₱300 (1 load + ₱100 fee)

---

## Browser Console Check

Open browser console (F12) and check for:
```
✅ Laundry Create Manager initialized
```

If you see errors, the JavaScript might not be loading properly.

---

## Need Help?

If the option still doesn't appear:
1. Clear browser cache (Ctrl+Shift+R)
2. Check if JavaScript file is loaded: `/assets/js/laundry-create.js`
3. Verify service has max_weight in database
4. Check browser console for errors
