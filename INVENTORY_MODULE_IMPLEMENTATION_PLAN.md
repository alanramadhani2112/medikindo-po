# 📦 Inventory Module Implementation Plan

**Priority**: 🔴 HIGH  
**Status**: Planning  
**Date**: 2026-04-15

---

## 🎯 OBJECTIVE

Implement inventory tracking module to close the critical gap identified in system validation:

```
Current Flow: PO → Approval → GR → Invoice → Payment ✅
Missing Link: GR → Inventory ❌
```

---

## 📋 REQUIREMENTS

### **Business Requirements**

1. **Stock Tracking**: Track product quantities after goods receipt
2. **Batch Management**: Track inventory by batch number and expiry date
3. **Stock Movements**: Record all stock in/out transactions
4. **Stock Alerts**: Low stock and expiry warnings
5. **Stock Reports**: Current stock levels, movements, valuations

### **Technical Requirements**

1. **Database Tables**:
   - `inventory_items` - Current stock levels
   - `inventory_movements` - Stock transaction history
   - `inventory_batches` - Batch-level tracking

2. **Relationships**:
   - Inventory → Product
   - Inventory → Organization
   - Inventory → GoodsReceiptItem (stock in)
   - Inventory → CustomerInvoiceLineItem (stock out)

3. **Business Rules**:
   - Stock IN: Automatic from Goods Receipt
   - Stock OUT: Automatic from Customer Invoice
   - FIFO: First-In-First-Out for batch selection
   - Negative stock: Prevented

---

## 🗄️ DATABASE SCHEMA

### **1. inventory_items Table**

```sql
CREATE TABLE inventory_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    batch_no VARCHAR(100) NOT NULL,
    expiry_date DATE NULL,
    quantity_on_hand INT NOT NULL DEFAULT 0,
    quantity_reserved INT NOT NULL DEFAULT 0,
    quantity_available INT GENERATED ALWAYS AS (quantity_on_hand - quantity_reserved) STORED,
    unit_cost DECIMAL(15,2) NOT NULL,
    location VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    UNIQUE KEY unique_inventory (organization_id, product_id, batch_no),
    INDEX idx_expiry (expiry_date),
    INDEX idx_product (product_id),
    INDEX idx_organization (organization_id)
);
```

### **2. inventory_movements Table**

```sql
CREATE TABLE inventory_movements (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    movement_type ENUM('in', 'out', 'adjustment') NOT NULL,
    quantity INT NOT NULL,
    reference_type VARCHAR(100) NULL, -- GoodsReceiptItem, CustomerInvoiceLineItem, etc
    reference_id BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_reference (reference_type, reference_id),
    INDEX idx_created_at (created_at)
);
```

---

## 🔄 BUSINESS FLOW INTEGRATION

### **Current Flow**
```
PO → Approval → GR → Invoice → Payment
```

### **New Flow with Inventory**
```
PO → Approval → GR → [INVENTORY IN] → Invoice → [INVENTORY OUT] → Payment
                      ↓                           ↓
                Stock Increased            Stock Decreased
```

### **Stock Movement Triggers**

#### **1. Stock IN (from Goods Receipt)**
```
Trigger: GoodsReceipt status → 'completed'
Action: Create inventory_item + inventory_movement (type: 'in')
Quantity: goods_receipt_item.quantity_received
Batch: goods_receipt_item.batch_no
Expiry: goods_receipt_item.expiry_date
Cost: purchase_order_item.unit_price
```

#### **2. Stock OUT (from Customer Invoice)**
```
Trigger: CustomerInvoice created
Action: Reduce inventory_item + inventory_movement (type: 'out')
Quantity: customer_invoice_line_item.quantity
Selection: FIFO (oldest batch first)
Validation: Check available quantity before invoice creation
```

---

## 📊 FEATURES

### **Phase 1: Core Inventory (MVP)**

1. ✅ **Automatic Stock IN** from Goods Receipt
2. ✅ **Automatic Stock OUT** from Customer Invoice
3. ✅ **Current Stock View** per product/batch
4. ✅ **Stock Movement History**
5. ✅ **Low Stock Alerts**

### **Phase 2: Advanced Features**

6. ⏳ **Stock Adjustment** (manual corrections)
7. ⏳ **Stock Transfer** between locations
8. ⏳ **Expiry Alerts** (30/60/90 days)
9. ⏳ **Stock Valuation** (FIFO cost)
10. ⏳ **Stock Reports** (aging, turnover)

---

## 🎨 UI COMPONENTS

### **1. Inventory Dashboard**

```
┌─────────────────────────────────────────────────────────┐
│ Inventory Overview                                      │
├─────────────────────────────────────────────────────────┤
│ [Total Products: 150] [Total Stock: 5,420] [Value: Rp] │
│ [Low Stock: 12]       [Expiring Soon: 8]               │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ Stock by Product                                        │
├─────────────────────────────────────────────────────────┤
│ Product Name    | Batch    | Qty | Expiry   | Status   │
│ Paracetamol     | B001     | 100 | 2027-12  | ✅ OK    │
│ Amoxicillin     | B002     | 50  | 2026-06  | ⚠️ Low   │
│ Ibuprofen       | B003     | 20  | 2026-05  | 🔴 Expiring│
└─────────────────────────────────────────────────────────┘
```

### **2. Stock Movement Log**

```
┌─────────────────────────────────────────────────────────┐
│ Stock Movements                                         │
├─────────────────────────────────────────────────────────┤
│ Date       | Product      | Type | Qty | Ref          │
│ 2026-04-15 | Paracetamol  | IN   | +100| GR-001       │
│ 2026-04-14 | Amoxicillin  | OUT  | -50 | INV-CUST-001 │
│ 2026-04-13 | Ibuprofen    | IN   | +200| GR-002       │
└─────────────────────────────────────────────────────────┘
```

---

## 🔧 IMPLEMENTATION STEPS

### **Step 1: Database Migration**
```bash
php artisan make:migration create_inventory_tables
```

### **Step 2: Models**
- `InventoryItem.php`
- `InventoryMovement.php`

### **Step 3: Services**
- `InventoryService.php` - Core inventory logic
- `StockMovementService.php` - Movement tracking

### **Step 4: Integration**
- Update `GoodsReceiptService` - Add stock IN
- Update `InvoiceFromGRService` - Add stock OUT validation
- Update `InvoiceService` - Add stock OUT

### **Step 5: Controllers**
- `InventoryWebController.php` - Web UI
- `InventoryController.php` - API

### **Step 6: Views**
- `inventory/index.blade.php` - Stock list
- `inventory/show.blade.php` - Product stock detail
- `inventory/movements.blade.php` - Movement history

### **Step 7: Permissions**
- `view_inventory`
- `manage_inventory`
- `adjust_inventory`

---

## 📝 VALIDATION RULES

### **Stock IN (from GR)**
```php
- GR must be 'completed'
- Batch number required
- Quantity > 0
- Unit cost from PO
```

### **Stock OUT (from Invoice)**
```php
- Check available quantity
- FIFO batch selection
- Prevent negative stock
- Record movement
```

### **Stock Adjustment**
```php
- Reason required
- Approval required (if > threshold)
- Audit log
```

---

## 🎯 SUCCESS CRITERIA

1. ✅ Stock automatically updated from GR
2. ✅ Stock automatically reduced from Customer Invoice
3. ✅ Cannot create invoice if insufficient stock
4. ✅ FIFO batch selection working
5. ✅ Stock movement history complete
6. ✅ Low stock alerts functional
7. ✅ Expiry alerts functional

---

## 📊 ESTIMATED EFFORT

| Task | Effort | Priority |
|------|--------|----------|
| Database Migration | 2 hours | HIGH |
| Models + Relationships | 2 hours | HIGH |
| Services (Core Logic) | 4 hours | HIGH |
| GR Integration | 2 hours | HIGH |
| Invoice Integration | 3 hours | HIGH |
| Controllers | 2 hours | MEDIUM |
| Views (UI) | 4 hours | MEDIUM |
| Permissions | 1 hour | MEDIUM |
| Testing | 4 hours | HIGH |

**Total**: ~24 hours (3 days)

---

## 🚀 ROLLOUT PLAN

### **Phase 1: Core (Week 1)**
- Database + Models
- Stock IN from GR
- Stock OUT from Invoice
- Basic stock view

### **Phase 2: UI (Week 2)**
- Inventory dashboard
- Stock movement log
- Low stock alerts

### **Phase 3: Advanced (Week 3)**
- Stock adjustments
- Expiry alerts
- Stock reports

---

## 📚 DOCUMENTATION

After implementation, create:
1. `INVENTORY_MODULE_COMPLETE.md` - Implementation summary
2. `INVENTORY_USER_GUIDE.md` - User documentation
3. `INVENTORY_API_REFERENCE.md` - API documentation

---

**Status**: ✅ **READY TO IMPLEMENT**

**Next Action**: Create database migration and models

---

**Created**: 2026-04-15  
**Priority**: 🔴 HIGH  
**Estimated Completion**: 3 days
