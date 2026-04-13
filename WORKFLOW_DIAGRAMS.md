# Workflow Diagrams
## Medikindo PO System - Visual Guide

**Last Updated**: 13 April 2026

---

## 🔄 Complete System Workflow

```
┌─────────────────────────────────────────────────────────────────────┐
│                    MEDIKINDO PO SYSTEM WORKFLOW                      │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│ HEALTHCARE   │  1. Create PO (draft)
│ USER         │  ────────────────────────────┐
│              │                               │
│              │  2. Submit PO                 ▼
│              │  ──────────────────────  ┌─────────┐
└──────────────┘                          │   PO    │
                                          │ (draft) │
                                          └─────────┘
                                               │
                                               │ submit
                                               ▼
┌──────────────┐                          ┌──────────┐
│  APPROVER    │  3. Approve PO           │    PO    │
│              │  ◄───────────────────────│(submitted)│
│              │                          └──────────┘
│              │  4. Mark Shipped              │
│              │  ─────────────────────────────┤
│              │                               │
│              │  5. Mark Delivered            │
│              │  ─────────────────────────────┤
└──────────────┘                               │
                                               ▼
┌──────────────┐                          ┌──────────┐
│ HEALTHCARE   │  6. Create Goods Receipt │    PO    │
│ USER         │  ◄───────────────────────│(delivered)│
│              │                          └──────────┘
└──────────────┘                               │
                                               │ goods receipt
                                               ▼
┌──────────────┐                          ┌──────────┐
│  FINANCE     │  7. Issue Invoice        │    PO    │
│              │  ◄───────────────────────│(completed)│
│              │                          └──────────┘
│              │  8. Approve Discrepancy       │
│              │     (if needed)               │
│              │  ─────────────────────────────┤
└──────────────┘                               │
                                               ▼
┌──────────────┐                          ┌─────────┐
│ HEALTHCARE   │  9. Confirm Payment      │ INVOICE │
│ USER         │  ──────────────────────► │ (issued)│
│              │                          └─────────┘
└──────────────┘                               │
                                               │
                                               ▼
┌──────────────┐                          ┌─────────┐
│  FINANCE     │  10. Verify Payment      │ INVOICE │
│              │  ◄───────────────────────│  (paid) │
│              │                          └─────────┘
└──────────────┘
```

---

## 📊 Purchase Order State Machine

```
┌─────────┐
│  DRAFT  │ ◄─────────────────────────┐
└─────────┘                            │
     │                                 │
     │ submit                          │ reject
     │ (Healthcare User)               │
     ▼                                 │
┌───────────┐                          │
│ SUBMITTED │                          │
└───────────┘                          │
     │                                 │
     │ approve                         │
     │ (Approver)                      │
     ▼                                 │
┌──────────┐                      ┌─────────┐
│ APPROVED │                      │REJECTED │
└──────────┘                      └─────────┘
     │
     │ mark shipped
     │ (Approver)
     ▼
┌─────────┐
│ SHIPPED │
└─────────┘
     │
     │ mark delivered
     │ (Approver)
     ▼
┌───────────┐
│ DELIVERED │
└───────────┘
     │
     │ create goods receipt
     │ (Healthcare User)
     ▼
┌───────────┐
│ COMPLETED │ ◄── Terminal State
└───────────┘
```

---

## 💰 Invoice State Machine

```
┌────────────────────┐
│ PENDING_APPROVAL   │ ◄─── If discrepancy detected
│ (Discrepancy > 1%  │      (variance > 1% OR > Rp 10,000)
│  OR > Rp 10,000)   │
└────────────────────┘
     │
     │ approve discrepancy
     │ (Finance)
     ▼
┌─────────┐
│ ISSUED  │ ◄─── If no discrepancy
└─────────┘
     │
     │ confirm payment
     │ (Healthcare User)
     ▼
┌────────────────────┐
│ PAYMENT_SUBMITTED  │
└────────────────────┘
     │
     │ verify payment
     │ (Finance)
     ▼
┌────────┐
│  PAID  │ ◄── Terminal State
└────────┘
```

---

## 🔐 RBAC Permission Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                         RBAC HIERARCHY                           │
└─────────────────────────────────────────────────────────────────┘

┌──────────────┐
│ SUPER ADMIN  │ ──► ALL 29 PERMISSIONS
└──────────────┘     │
                     ├─► Purchase Orders (Full CRUD)
                     ├─► Approvals (Full)
                     ├─► Goods Receipt (Full)
                     ├─► Invoices (Full)
                     ├─► Payments (Full)
                     ├─► Credit Control (Full)
                     └─► Master Data (Full CRUD)

┌──────────────┐
│ HEALTHCARE   │ ──► 12 PERMISSIONS
│ USER         │     │
└──────────────┘     ├─► Purchase Orders (Create, Edit draft, Submit)
                     ├─► Goods Receipt (Create, View)
                     └─► Payments (Confirm own org)

┌──────────────┐
│  APPROVER    │ ──► 4 PERMISSIONS
│              │     │
└──────────────┘     ├─► Purchase Orders (View all)
                     └─► Approvals (Approve/Reject, Ship, Deliver)

┌──────────────┐
│  FINANCE     │ ──► 11 PERMISSIONS
│              │     │
└──────────────┘     ├─► Invoices (View, Issue, Approve Discrepancy)
                     ├─► Payments (View, Verify)
                     └─► Credit Control (Manage)
```

---

## 💳 Credit Control Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                      CREDIT CONTROL FLOW                         │
└─────────────────────────────────────────────────────────────────┘

PO Submit
    │
    ▼
┌─────────────────┐
│ Check Available │
│ Credit          │
└─────────────────┘
    │
    ├─► Insufficient ──► ❌ Reject PO Submit
    │                    "Limit kredit tidak mencukupi"
    │
    └─► Sufficient
         │
         ▼
    ┌─────────────┐
    │ RESERVE     │ ──► Status: reserved
    │ Credit      │     Amount: PO total
    └─────────────┘
         │
         ▼
    PO Approved
         │
         ▼
    ┌─────────────┐
    │ BILL        │ ──► Status: billed
    │ Credit      │     Amount: PO total
    └─────────────┘
         │
         ▼
    Payment Verified
         │
         ▼
    ┌─────────────┐
    │ RELEASE     │ ──► Status: released
    │ Credit      │     Amount: Payment amount
    └─────────────┘

Alternative: PO Rejected
         │
         ▼
    ┌─────────────┐
    │ REVERSE     │ ──► Status: released
    │ Credit      │     Amount: 0
    └─────────────┘
```

---

## 🧮 Invoice Calculation Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                   INVOICE CALCULATION FLOW                       │
└─────────────────────────────────────────────────────────────────┘

Goods Receipt Items
    │
    ▼
┌──────────────────────┐
│ For Each Item:       │
│                      │
│ 1. Line Subtotal     │ ──► quantity × unit_price
│    = qty × price     │     (BCMath multiply)
│                      │
│ 2. Discount Amount   │ ──► subtotal × discount% / 100
│    = subtotal × %    │     (BCMath percentage)
│                      │
│ 3. Taxable Amount    │ ──► subtotal - discount
│    = subtotal - disc │     (BCMath subtract)
│                      │
│ 4. Tax Amount        │ ──► taxable × tax_rate / 100
│    = taxable × rate  │     (BCMath percentage)
│                      │
│ 5. Line Total        │ ──► taxable + tax
│    = taxable + tax   │     (BCMath add)
└──────────────────────┘
    │
    ▼
┌──────────────────────┐
│ Sum All Line Items:  │
│                      │
│ Invoice Subtotal     │ ──► sum(line_subtotals)
│ Invoice Discount     │ ──► sum(line_discounts)
│ Invoice Tax          │ ──► sum(line_taxes)
│ Invoice Total        │ ──► sum(line_totals)
└──────────────────────┘
    │
    ▼
┌──────────────────────┐
│ Tolerance Check:     │
│                      │
│ sum(line_totals)     │ ──► Must equal invoice_total
│ = invoice_total?     │     within ±0.01 tolerance
│                      │
│ ✅ Pass: Continue    │
│ ❌ Fail: Reject      │
└──────────────────────┘
    │
    ▼
┌──────────────────────┐
│ Discrepancy Check:   │
│                      │
│ Compare invoice_total│ ──► vs PO expected_total
│ with PO total        │
│                      │
│ Variance > 1%        │ ──► Flag for approval
│ OR > Rp 10,000?      │
│                      │
│ ✅ No: Issue         │
│ ⚠️ Yes: Pending      │
└──────────────────────┘
```

---

## 🔒 Immutability Enforcement

```
┌─────────────────────────────────────────────────────────────────┐
│                  IMMUTABILITY ENFORCEMENT                        │
└─────────────────────────────────────────────────────────────────┘

Invoice Update Attempt
    │
    ▼
┌──────────────────────┐
│ Observer Triggered   │ ──► CustomerInvoiceObserver
│ (updating event)     │     SupplierInvoiceObserver
└──────────────────────┘
    │
    ▼
┌──────────────────────┐
│ Get Dirty Attributes │ ──► Changed fields
│ (attempted changes)  │
└──────────────────────┘
    │
    ▼
┌──────────────────────┐
│ Check Invoice Status │
└──────────────────────┘
    │
    ├─► Draft ──────────────► ✅ Allow all changes
    │
    └─► Issued/Paid
         │
         ▼
    ┌──────────────────────┐
    │ Check Each Field:    │
    │                      │
    │ Immutable Fields:    │
    │ - total_amount       │ ──► ❌ BLOCK
    │ - subtotal_amount    │ ──► ❌ BLOCK
    │ - discount_amount    │ ──► ❌ BLOCK
    │ - tax_amount         │ ──► ❌ BLOCK
    │ - invoice_number     │ ──► ❌ BLOCK
    │ - due_date           │ ──► ❌ BLOCK
    │                      │
    │ Mutable Fields:      │
    │ - status             │ ──► ✅ ALLOW
    │ - paid_amount        │ ──► ✅ ALLOW
    │ - payment_reference  │ ──► ✅ ALLOW
    │ - verified_by        │ ──► ✅ ALLOW
    │ - notes              │ ──► ✅ ALLOW
    └──────────────────────┘
         │
         ├─► All Allowed ──► ✅ Continue Update
         │
         └─► Has Violations
              │
              ▼
         ┌──────────────────────┐
         │ Log Violation:       │
         │ - User ID            │
         │ - IP Address         │
         │ - Attempted Changes  │
         │ - Timestamp          │
         └──────────────────────┘
              │
              ▼
         ┌──────────────────────┐
         │ Throw Exception:     │
         │ ImmutabilityViolation│
         │ Exception            │
         └──────────────────────┘
              │
              ▼
         ❌ Update Blocked
         Transaction Rolled Back
```

---

## 🔍 Discrepancy Detection Logic

```
┌─────────────────────────────────────────────────────────────────┐
│                  DISCREPANCY DETECTION LOGIC                     │
└─────────────────────────────────────────────────────────────────┘

Invoice Issued
    │
    ▼
┌──────────────────────┐
│ Calculate Expected   │
│ Total from PO:       │
│                      │
│ For each PO item:    │
│   qty × unit_price   │ ──► Using BCMath
│                      │
│ Sum all items        │ ──► expected_total
└──────────────────────┘
    │
    ▼
┌──────────────────────┐
│ Calculate Variance:  │
│                      │
│ variance_amount =    │ ──► invoice_total - expected_total
│   invoice - expected │     (BCMath subtract)
│                      │
│ variance_percentage =│ ──► (variance / expected) × 100
│   (variance/expected)│     (BCMath percentage)
│   × 100              │
└──────────────────────┘
    │
    ▼
┌──────────────────────┐
│ Check Thresholds:    │
│                      │
│ Percentage > 1.00%?  │ ──► Yes ──┐
│ OR                   │           │
│ Amount > Rp 10,000?  │ ──► Yes ──┤
└──────────────────────┘           │
    │                              │
    │ No                           │ Yes
    ▼                              ▼
┌──────────────┐          ┌──────────────────┐
│ Set Status:  │          │ Set Status:      │
│ ISSUED       │          │ PENDING_APPROVAL │
└──────────────┘          └──────────────────┘
    │                              │
    │                              ▼
    │                     ┌──────────────────┐
    │                     │ Notify Finance:  │
    │                     │ "Discrepancy     │
    │                     │  detected,       │
    │                     │  approval needed"│
    │                     └──────────────────┐
    │                              │
    │                              ▼
    │                     ┌──────────────────┐
    │                     │ Finance Reviews: │
    │                     │                  │
    │                     │ ✅ Approve       │
    │                     │    with reason   │
    │                     │                  │
    │                     │ ❌ Reject        │
    │                     │    with reason   │
    │                     └──────────────────┘
    │                              │
    │                              │ approve
    │                              ▼
    └──────────────────────► ┌──────────────┐
                             │ Set Status:  │
                             │ ISSUED       │
                             └──────────────┘
```

---

## 📝 Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        DATA FLOW DIAGRAM                         │
└─────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   PRODUCT    │ ──► Master Data (price, supplier)
│   MASTER     │
└──────────────┘
       │
       │ price
       ▼
┌──────────────┐
│      PO      │ ──► Draft → Submitted → Approved
│    ITEMS     │     (quantity × unit_price)
└──────────────┘
       │
       │ delivered
       ▼
┌──────────────┐
│    GOODS     │ ──► Actual quantities received
│   RECEIPT    │
└──────────────┘
       │
       │ issue invoice
       ▼
┌──────────────┐
│   INVOICE    │ ──► BCMath calculations
│ LINE ITEMS   │     (qty × price - discount + tax)
└──────────────┘
       │
       │ sum
       ▼
┌──────────────┐
│   INVOICE    │ ──► Total amounts
│   HEADER     │     (subtotal, discount, tax, total)
└──────────────┘
       │
       │ compare
       ▼
┌──────────────┐
│ DISCREPANCY  │ ──► Variance detection
│  DETECTION   │     (invoice vs PO)
└──────────────┘
       │
       │ payment
       ▼
┌──────────────┐
│   PAYMENT    │ ──► Payment allocation
│ ALLOCATION   │     (link to invoice)
└──────────────┘
       │
       │ release
       ▼
┌──────────────┐
│   CREDIT     │ ──► Credit usage tracking
│   USAGE      │     (reserved → billed → released)
└──────────────┘
```

---

## 🎯 Key Takeaways

### State Machines
- ✅ Clearly defined states
- ✅ Enforced transitions
- ✅ No skipping allowed
- ✅ Terminal states identified

### RBAC
- ✅ 4 roles with clear responsibilities
- ✅ 29 permissions total
- ✅ Multi-tenant isolation
- ✅ Permission checks at route and service level

### Credit Control
- ✅ Reserve → Bill → Release flow
- ✅ Integrated with PO workflow
- ✅ Prevents over-limit orders
- ✅ Automatic release on payment

### Invoice Calculations
- ✅ BCMath for all operations
- ✅ Line-by-line calculations
- ✅ Tolerance check (±0.01)
- ✅ Discrepancy detection

### Immutability
- ✅ Observer-based enforcement
- ✅ Financial fields locked
- ✅ Violation logging
- ✅ Clear error messages

---

**Last Updated**: 13 April 2026  
**Status**: ✅ Production Ready
