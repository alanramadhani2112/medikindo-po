# 🔍 PHASE 0 — SYSTEM DISCOVERY REPORT

**Generated:** 2026-04-21  
**Status:** ✅ COMPLETED

---

## 📊 1. MODEL MAPPING

### ✅ SupplierInvoice (AP - Accounts Payable)
**File:** `app/Models/SupplierInvoice.php`

**Fields:**
- ✅ `total_amount` (decimal:2)
- ✅ `paid_amount` (decimal:2) — **SUDAH ADA**
- ✅ `due_date` (date) — **SUDAH ADA**
- ✅ `status` (enum: SupplierInvoiceStatus)
- ✅ `subtotal_amount` (decimal:2)
- ✅ `discount_amount` (decimal:2)
- ✅ `tax_amount` (decimal:2)
- ✅ `issued_at` (datetime)
- ✅ `payment_submitted_at` (datetime)
- ✅ `verified_at` (datetime)

**Computed Attributes:**
- ✅ `outstanding_amount` — `max(0, total_amount - paid_amount)`
- ✅ `days_overdue` — calculated from due_date
- ✅ `aging_bucket` — 'current', '1-30', '31-60', '61-90', '90+'

**Relationships:**
- organization, supplier, purchaseOrder, goodsReceipt
- ✅ `paymentAllocations()` — HasMany
- ✅ `lineItems()` — HasMany

**Methods:**
- ✅ `canTransitionTo(status)` — state machine validation
- ✅ `isOverdueByDate()` — check if past due date
- ✅ `applyCreditNote()` — apply credit to reduce balance

---

### ✅ CustomerInvoice (AR - Accounts Receivable)
**File:** `app/Models/CustomerInvoice.php`

**Fields:**
- ✅ `total_amount` (decimal:2)
- ✅ `paid_amount` (decimal:2) — **SUDAH ADA**
- ✅ `due_date` (date) — **SUDAH ADA**
- ✅ `status` (enum: CustomerInvoiceStatus)
- ✅ `subtotal_amount` (decimal:2)
- ✅ `discount_amount` (decimal:2)
- ✅ `tax_amount` (decimal:2)
- ✅ `surcharge` (decimal:2)
- ✅ `ematerai_fee` (decimal:2)
- ✅ `issued_at` (datetime)
- ✅ `payment_submitted_at` (datetime)
- ✅ `verified_at` (datetime)

**Computed Attributes:**
- ✅ `outstanding_amount` — `max(0, total_amount - paid_amount)`
- ✅ `days_overdue` — calculated from due_date
- ✅ `aging_bucket` — 'current', '1-30', '31-60', '61-90', '90+'

**Relationships:**
- organization, purchaseOrder, goodsReceipt, supplierInvoice
- ✅ `paymentAllocations()` — HasMany
- ✅ `lineItems()` — HasMany
- ✅ `paymentProofs()` — HasMany
- ✅ `creditNotes()` — HasMany

**Methods:**
- ✅ `canTransitionTo(status)` — state machine validation
- ✅ `transitionTo(status)` — enforce state machine
- ✅ `isOverdueByDate()` — check if past due date
- ✅ `applyCreditNote()` — apply credit to reduce balance
- ✅ `getTotalCreditNoteAmount()` — sum of applied credit notes
- ✅ `getRemainingBalance()` — after payments and credits

---

### ✅ Payment
**File:** `app/Models/Payment.php`

**Fields:**
- ✅ `payment_date` (date)
- ✅ `amount` (decimal:2)
- ✅ `organization_id`
- ✅ `supplier_id`

**Relationships:**
- ✅ `allocations()` — HasMany PaymentAllocation

**Status:** ✅ **SUDAH ADA** — Model payment sudah exist

---

### ✅ PaymentAllocation
**File:** `app/Models/PaymentAllocation.php`

**Fields:**
- ✅ `payment_id`
- ✅ `supplier_invoice_id` (nullable)
- ✅ `customer_invoice_id` (nullable)
- ✅ `allocated_amount` (decimal:2)

**Relationships:**
- payment, supplierInvoice, customerInvoice

**Status:** ✅ **SUDAH ADA** — Allocation system sudah exist

---

### ✅ PurchaseOrder
**File:** `app/Models/PurchaseOrder.php`

**Status Constants:**
```php
DRAFT → SUBMITTED → APPROVED → PARTIALLY_RECEIVED → COMPLETED
                  ↓
                REJECTED → DRAFT
```

**Fields:**
- `po_number`, `total_amount`, `status`
- `requested_date`, `expected_delivery_date`
- `has_narcotics`, `requires_extra_approval`

---

### ✅ GoodsReceipt
**File:** `app/Models/GoodsReceipt.php`

**Status Constants:**
- `PARTIAL` — sebagian diterima
- `COMPLETED` — semua diterima

**Fields:**
- `gr_number`, `received_date`, `status`
- `received_by`, `confirmed_by`

**Methods:**
- `hasRemainingQuantity()` — check if ada sisa untuk AP invoice
- `hasRemainingArQuantity()` — check if ada sisa untuk AR invoice
- `isFullyInvoiced()` — check if sudah fully invoiced

---

## 📋 2. STATUS ENUM MAPPING

### SupplierInvoiceStatus (AP)
**File:** `app/Enums/SupplierInvoiceStatus.php`

```php
enum SupplierInvoiceStatus: string
{
    case DRAFT = 'draft';
    case VERIFIED = 'verified';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
}
```

**State Machine:**
```
DRAFT → VERIFIED → PAID (terminal)
  ↓         ↓
OVERDUE → VERIFIED
  ↓
PAID (terminal)
```

**Badge Classes:**
- DRAFT → `badge-light-primary`
- VERIFIED → `badge-light-info`
- PAID → `badge-light-success` ✅
- OVERDUE → `badge-light-danger` ⚠️

---

### CustomerInvoiceStatus (AR)
**File:** `app/Enums/CustomerInvoiceStatus.php`

```php
enum CustomerInvoiceStatus: string
{
    case DRAFT = 'draft';
    case ISSUED = 'issued';
    case PARTIAL_PAID = 'partial_paid';
    case PAID = 'paid';
    case VOID = 'void';
}
```

**State Machine:**
```
DRAFT → ISSUED → PARTIAL_PAID → PAID (terminal)
  ↓       ↓           ↓
VOID ← VOID ←──────── VOID (terminal)
```

**Badge Classes:**
- DRAFT → `badge-light-secondary`
- ISSUED → `badge-light-warning` ⚠️
- PARTIAL_PAID → `badge-light-info` 🔵
- PAID → `badge-light-success` ✅
- VOID → `badge-light-danger` ❌

**Key Methods:**
- `canAcceptPayment()` — true for ISSUED, PARTIAL_PAID
- `isImmutable()` — true for PAID, VOID

---

### PaymentProofStatus
**File:** `app/Enums/PaymentProofStatus.php`

```php
enum PaymentProofStatus: string
{
    case SUBMITTED = 'submitted';
    case VERIFIED = 'verified';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case RECALLED = 'recalled';
}
```

---

## ✅ 3. EXISTING FEATURES CHECKLIST

### Database Structure
- ✅ `paid_amount` field — **SUDAH ADA** di supplier_invoices & customer_invoices
- ✅ `due_date` field — **SUDAH ADA** di supplier_invoices & customer_invoices
- ✅ `outstanding_amount` — **COMPUTED ATTRIBUTE** (tidak perlu kolom)
- ✅ `payments` table — **SUDAH ADA**
- ✅ `payment_allocations` table — **SUDAH ADA**

### Model Features
- ✅ State machine validation — `canTransitionTo()`
- ✅ Overdue detection — `isOverdueByDate()`
- ✅ Aging calculation — `days_overdue`, `aging_bucket`
- ✅ Outstanding calculation — `outstanding_amount` attribute
- ✅ Credit note handling — `applyCreditNote()`
- ✅ Payment allocation system — PaymentAllocation model

### Business Logic
- ✅ Invoice status transitions enforced
- ✅ Immutability protection (PAID, VOID)
- ✅ Payment acceptance rules
- ✅ Aging buckets (current, 1-30, 31-60, 61-90, 90+)

---

## 🎯 4. GAPS & REQUIREMENTS

### ❌ Missing Components

#### 1. **Payment Service** — BELUM ADA
**Need:** Centralized service untuk apply payment
- `applyPayment(invoice, amount)` → update paid_amount, status
- Auto-calculate outstanding
- Auto-transition status (ISSUED → PARTIAL_PAID → PAID)

#### 2. **Overdue Engine** — BELUM ADA
**Need:** Automated overdue detection & status update
- Scheduled job untuk scan invoices
- Auto-update status ke OVERDUE jika past due_date
- Notification trigger

#### 3. **Credit Control Service** — BELUM ADA
**Need:** Block PO creation jika ada overdue
- `canCreatePO(customer_id)` → check overdue invoices
- `canCreatePO(customer_id)` → check credit limit
- Return BLOCK/ALLOW decision

#### 4. **State Machine Service** — PARTIAL
**Need:** Centralized state validation
- Currently scattered di model methods
- Need unified service untuk enforce transitions

#### 5. **Event System** — BELUM ADA
**Need:** Event-driven architecture
- `InvoiceApproved` → set due_date
- `PaymentCreated` → update invoice
- `InvoiceOverdue` → trigger notification

#### 6. **Scheduler** — BELUM ADA
**Need:** Daily job untuk maintenance
- `UpdateOverdueInvoicesJob` → scan & update
- Run setiap hari

#### 7. **Frontend Components** — BELUM ADA
**Need:** UI untuk payment & aging
- Payment summary section
- Payment action modal
- Status badges
- Aging indicators
- Filters (overdue, unpaid, paid)

---

## 🚀 5. IMPLEMENTATION STRATEGY

### ✅ PHASE 1: DATABASE (SKIP)
**Status:** ✅ **COMPLETE** — All required fields already exist
- `paid_amount` ✅
- `due_date` ✅
- `payments` table ✅
- `payment_allocations` table ✅

### 🔧 PHASE 2: BACKEND ENGINE (PRIORITY)
**Status:** 🔴 **REQUIRED**
1. PaymentService
2. OverdueService
3. CreditControlService
4. StateMachineService (enhance existing)

### 🔁 PHASE 3: EVENT SYSTEM (PRIORITY)
**Status:** 🔴 **REQUIRED**
1. Events: InvoiceApproved, PaymentCreated, InvoiceOverdue
2. Listeners: UpdateInvoiceStatus, SendNotification

### ⏱️ PHASE 4: SCHEDULER (PRIORITY)
**Status:** 🔴 **REQUIRED**
1. UpdateOverdueInvoicesJob (daily)

### 🎨 PHASE 5: FRONTEND (MEDIUM)
**Status:** 🟡 **RECOMMENDED**
1. Payment UI components
2. Status badges
3. Aging indicators
4. Filters

### 🧪 PHASE 6: TESTING (FINAL)
**Status:** 🟡 **VALIDATION**
1. Payment flow testing
2. State transition testing
3. Overdue detection testing
4. Credit control testing

---

## ✅ DISCOVERY COMPLETE

**Summary:**
- ✅ Database structure: **COMPLETE** (no migration needed)
- ✅ Model foundation: **STRONG** (good base to build on)
- ✅ State machine: **EXISTS** (needs enhancement)
- ❌ Services: **MISSING** (need to create)
- ❌ Events: **MISSING** (need to create)
- ❌ Scheduler: **MISSING** (need to create)
- ❌ Frontend: **MISSING** (need to create)

**Next Step:** Proceed to PHASE 2 — Backend Engine Implementation

---

**🎯 READY TO PROCEED:** YES ✅
