# Promotion Creation Form - Inventory Items Integration

## Updated File
`resources/views/admin/promotions/create-poster.blade.php`

## What Was Added

### 1. New Section: "Inventory Items (Auto-Deduct)"
Added a new card section after the "Design Options" section that allows admins to:
- Select inventory items from a dropdown
- Specify quantity to deduct per promotion use
- Add multiple items dynamically
- Remove items individually

### 2. UI Features

**Empty State:**
- Shows a friendly message when no items are added
- Icon and text prompt to add items

**Add Item Button:**
- Green "Add Inventory Item" button
- Dynamically adds new item rows

**Item Row Contains:**
- **Inventory Item Dropdown**: Select from all active inventory items
  - Shows item name and brand
  - Required field
- **Quantity Input**: Specify how much to deduct per use
  - Decimal input (e.g., 1.0, 0.5)
  - Default value: 1
  - Required field
- **Remove Button**: Delete the item row
  - Red trash icon button

### 3. JavaScript Functionality

**Dynamic Item Management:**
```javascript
// Adds new inventory item row
document.getElementById('add-inventory-item').addEventListener('click', ...)

// Removes item row
itemRow.querySelector('.remove-inventory-item').addEventListener('click', ...)

// Shows/hides empty state message
```

**Form Data Structure:**
```javascript
inventory_items[0][inventory_item_id] = 5
inventory_items[0][quantity] = 1.0
inventory_items[1][inventory_item_id] = 8
inventory_items[1][quantity] = 0.5
```

### 4. Integration with Backend

The form now sends inventory items data to the controller:
- Controller method `storePosterPromotion()` receives the data
- Calls `syncPromotionItems()` to save items to database
- Creates records in `promotion_items` table

## How It Works

### For Admin Users:

1. **Create Promotion**
   - Fill in basic promotion details
   - Scroll to "Inventory Items (Auto-Deduct)" section
   - Click "Add Inventory Item"

2. **Select Items**
   - Choose inventory item from dropdown (e.g., "Ariel Detergent")
   - Enter quantity per use (e.g., "1" for 1 bottle per load)
   - Add more items as needed

3. **Save Promotion**
   - Submit form
   - Items are linked to promotion
   - Ready for automatic deduction

### Example Use Case:

**Promotion: "₱179 Drop Off Promo"**

Inventory Items Included:
- Ariel Detergent (1.0 bottle per use)
- Downy Fabcon (0.5 bottle per use)
- Zonrox Bleach (0.25 bottle per use)

When a customer uses this promotion:
- System deducts 1 bottle of Ariel
- System deducts 0.5 bottle of Downy
- System deducts 0.25 bottle of Zonrox
- All from the branch's inventory

## Visual Design

The section matches the existing design:
- Same card styling as other sections
- Consistent colors and spacing
- Responsive layout
- Bootstrap icons
- Theme-aware (light/dark mode support)

## Validation

- Inventory item selection is required
- Quantity must be greater than 0
- Decimal values allowed (0.01 minimum)
- Form won't submit without valid data

## Next Steps

To complete the integration:

1. **Update Edit Form**: Add same section to `edit-poster.blade.php`
2. **Test Creation**: Create a promotion with items
3. **Verify Database**: Check `promotion_items` table
4. **Test Deduction**: Apply promotion to order and verify inventory deduction

## Benefits

✅ **User-Friendly**: Simple dropdown and quantity input
✅ **Flexible**: Add unlimited items
✅ **Visual Feedback**: Clear empty state and item list
✅ **Validation**: Prevents invalid data
✅ **Responsive**: Works on mobile and desktop
✅ **Consistent**: Matches existing UI design
