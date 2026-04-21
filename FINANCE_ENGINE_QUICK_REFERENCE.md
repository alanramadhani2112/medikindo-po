# ⚡ FINANCE ENGINE — QUICK REFERENCE

**Fast lookup for developers**

---

## 🚀 SERVICES

### PaymentService
```php
use App\Services\PaymentService;

$paymentService = app(PaymentService::class);

// Apply payment to Customer Invoice (AR)
$allocation = $paymentService->applyPaymentToCustomerInvoice(
    invoice: $customerInvoice,
    amount: 5000000,
    payment: $payment
);

// Apply payment to Supplier Invoice (AP)
$allocation = $paymentService->applyPaymentToSupplierInvoice(
    invoice: $supplierInvoice,
    amount: 3000000,
    payment: $payment
);

// Get total payments
$total = $paymentService->getTotalPayments($invoice);

// Validate consistency
$isValid = $paymentService->validatePaymentConsistency($invoice);

// Recalculate if inconsistent
$paymentService->recalculatePayments($invoice);
```

---

### OverdueService
```php
use App\Services\OverdueService;

$overdueService = app(OverdueService::class);

// Update overdue Supplier Invoices
$stats = $overdueService->updateOverdueSupplierInvoices();
// Returns: ['total_checked' => 10, 'updated' => 3, 'skipped' => 0]

// Scan overdue Customer Invoices
$stats = $overdueService->scanOverdueCustomerInvoices();
// Returns: ['total_overdue' => 5, 'total_outstanding' => 50000000, ...]

// Get overdue by organization
$invoices = $overdueService->getOverdueInvoicesByOrganization($orgId);

// Get aging report
$report = $overdueService->getAgingReport($orgId);
// Returns: ['current' => [...], '1-30' => [...], ...]

// Check if has overdue
$hasOverdue = $overdueService->hasOverdueInvoices($orgId);
```

---

### CreditControlService
```php
use App\Services\CreditControlService;

$creditControl = app(CreditControlService::class);

// Check if can create PO
$result = $creditControl->canCreatePO($orgId, $poAmount);
if (!$result['allowed']) {
    // BLOCKED
    // $result['reason'] = 'overdue_invoices' or 'credit_limit_exceeded'
    // $result['message'] = 'Tidak dapat membuat PO...'
    // $result['details'] = [...]
}

// Check credit limit only
$result = $creditControl->checkCreditLimit($orgId, $amount);

// Get current outstanding
$outstanding = $creditControl->getCurrentOutstanding($orgId);

// Get credit status
$status = $creditControl->getCreditStatus($orgId);
// Returns: credit_limit, current_outstanding, available_credit, etc.

// Get blocked organizations
$blocked = $creditControl->getBlockedOrganizations();
```

---

### StateMachineService
```php
use App\Services\StateMachineService;
use App\Enums\CustomerInvoiceStatus;

$stateMachine = app(StateMachineService::class);

// Transition Customer Invoice
$stateMachine->transitionCustomerInvoice(
    invoice: $invoice,
    targetStatus: CustomerInvoiceStatus::ISSUED,
    context: ['approved_by' => auth()->id()]
);

// Transition Supplier Invoice
$stateMachine->transitionSupplierInvoice(
    invoice: $invoice,
    targetStatus: SupplierInvoiceStatus::VERIFIED,
    context: ['verified_by' => auth()->id()]
);

// Get valid transitions
$validStates = $stateMachine->getValidCustomerInvoiceTransitions($invoice);

// Check if can transition
$canTransition = $stateMachine->canTransition($invoice, $targetStatus);

// Get state machine map
$map = $stateMachine->getStateTransitionMap('customer');
```

---

## 🔔 EVENTS

### Dispatch Events
```php
use App\Events\InvoiceApproved;
use App\Events\PaymentCreated;
use App\Events\InvoiceOverdue;

// Invoice approved
event(new InvoiceApproved($invoice, 'customer'));
// → Listener: SetInvoiceDueDate (auto-set due_date)

// Payment created
event(new PaymentCreated($payment, $allocation, $invoice, 'customer'));
// → (Optional listeners can be added)

// Invoice overdue
event(new InvoiceOverdue($invoice, 'customer', $daysOverdue));
// → Listener: SendOverdueNotification
```

---

## 📋 COMMANDS

### Run Commands
```bash
# Update overdue invoices (no notifications)
php artisan invoices:update-overdue

# Update overdue invoices (with notifications)
php artisan invoices:update-overdue --notify

# Check scheduler
php artisan schedule:list

# Run scheduler manually
php artisan schedule:run
```

---

## 🎨 STATUS ENUMS

### CustomerInvoiceStatus
```php
use App\Enums\CustomerInvoiceStatus;

CustomerInvoiceStatus::DRAFT         // 'draft'
CustomerInvoiceStatus::ISSUED        // 'issued'
CustomerInvoiceStatus::PARTIAL_PAID  // 'partial_paid'
CustomerInvoiceStatus::PAID          // 'paid'
CustomerInvoiceStatus::VOID          // 'void'

// Methods
$status->getLabel();           // 'Menunggu Pembayaran'
$status->getBadgeClass();      // 'badge-light-warning'
$status->canAcceptPayment();   // true/false
$status->isImmutable();        // true/false
$status->canTransitionTo($target);  // true/false
```

### SupplierInvoiceStatus
```php
use App\Enums\SupplierInvoiceStatus;

SupplierInvoiceStatus::DRAFT      // 'draft'
SupplierInvoiceStatus::VERIFIED   // 'verified'
SupplierInvoiceStatus::PAID       // 'paid'
SupplierInvoiceStatus::OVERDUE    // 'overdue'

// Methods
$status->getLabel();           // 'Diverifikasi'
$status->getBadgeClass();      // 'badge-light-info'
$status->isFinal();            // true/false
$status->canTransitionTo($target);  // true/false
$status->getValidTransitions();     // array of valid next states
```

---

## 🔄 STATE MACHINES

### Customer Invoice (AR)
```
DRAFT → ISSUED → PARTIAL_PAID → PAID ✅
  ↓       ↓           ↓
VOID ← VOID ←──────── VOID ❌
```

### Supplier Invoice (AP)
```
DRAFT → VERIFIED → PAID ✅
  ↓         ↓
OVERDUE → VERIFIED
  ↓
PAID ✅
```

---

## 📊 MODEL ATTRIBUTES

### Invoice Computed Attributes
```php
// Outstanding amount
$invoice->outstanding_amount  // total - paid

// Days overdue
$invoice->days_overdue  // 0 if not overdue

// Aging bucket
$invoice->aging_bucket  // 'current', '1-30', '31-60', '61-90', '90+'

// Check overdue
$invoice->isOverdueByDate()  // true/false

// Status checks
$invoice->isDraft()
$invoice->isIssued()
$invoice->isPartialPaid()
$invoice->isPaid()
$invoice->isVoid()
$invoice->canConfirmPayment()
```

---

## 🧪 VALIDATION

### Payment Validation
```php
// Amount must be > 0
if ($amount <= 0) {
    throw new \InvalidArgumentException('Amount must be greater than 0');
}

// Amount cannot exceed outstanding
if ($amount > $invoice->outstanding_amount) {
    throw new \InvalidArgumentException('Amount exceeds outstanding');
}

// Invoice must accept payment
if (!$invoice->canConfirmPayment()) {
    throw new \DomainException('Invoice cannot accept payment');
}
```

### State Transition Validation
```php
// Check if can transition
if (!$invoice->canTransitionTo($targetStatus)) {
    throw new InvalidStateTransitionException('Invalid transition');
}

// Use service for safe transition
$stateMachine->transitionCustomerInvoice($invoice, $targetStatus);
```

---

## 🎯 COMMON PATTERNS

### Apply Payment Pattern
```php
DB::transaction(function () use ($invoice, $amount) {
    // 1. Create payment
    $payment = Payment::create([...]);
    
    // 2. Apply using service
    $allocation = $paymentService->applyPaymentToCustomerInvoice(
        $invoice, $amount, $payment
    );
    
    // 3. Service handles:
    //    - Update paid_amount
    //    - Auto-transition status
    //    - Dispatch event
    //    - Log operation
});
```

### Credit Control Pattern
```php
// Before creating PO
$result = $creditControl->canCreatePO($orgId, $poAmount);

if (!$result['allowed']) {
    return response()->json([
        'message' => $result['message'],
        'reason' => $result['reason'],
        'details' => $result['details'],
    ], 403);
}

// Proceed with PO creation
$po = PurchaseOrder::create([...]);
```

### Approve Invoice Pattern
```php
// 1. Validate status
if (!$invoice->isDraft()) {
    throw new \DomainException('Only draft can be approved');
}

// 2. Transition status
$stateMachine->transitionCustomerInvoice(
    $invoice,
    CustomerInvoiceStatus::ISSUED
);

// 3. Dispatch event (auto-set due_date)
event(new InvoiceApproved($invoice, 'customer'));
```

---

## 🔍 DEBUGGING

### Check Payment Consistency
```php
// Validate
$isValid = $paymentService->validatePaymentConsistency($invoice);

if (!$isValid) {
    // Recalculate
    $paymentService->recalculatePayments($invoice);
}
```

### Check Logs
```bash
# Laravel log
tail -f storage/logs/laravel.log

# Search for payment logs
grep "Payment applied" storage/logs/laravel.log

# Search for overdue logs
grep "OVERDUE" storage/logs/laravel.log
```

---

## 📞 QUICK HELP

### Need to:
- **Apply payment?** → Use `PaymentService`
- **Check overdue?** → Use `OverdueService`
- **Block PO?** → Use `CreditControlService`
- **Change status?** → Use `StateMachineService`
- **Auto-set due date?** → Dispatch `InvoiceApproved` event
- **Send notification?** → Dispatch `InvoiceOverdue` event

### Files to check:
- Services: `app/Services/`
- Events: `app/Events/`
- Listeners: `app/Listeners/`
- Commands: `app/Console/Commands/`
- Enums: `app/Enums/`

---

**⚡ Keep this reference handy for quick lookups!**
