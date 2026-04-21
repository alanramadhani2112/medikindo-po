# 💡 FINANCE ENGINE — USAGE EXAMPLES

**How to integrate Finance Engine services into your controllers**

---

## 📋 1. PAYMENT CONTROLLER EXAMPLE

### Apply Payment to Customer Invoice

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Models\CustomerInvoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Apply payment to customer invoice
     *
     * POST /api/invoices/{invoice}/payments
     */
    public function applyPayment(Request $request, CustomerInvoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Create payment record
            $payment = Payment::create([
                'organization_id' => $invoice->organization_id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Apply payment using service
            $allocation = $this->paymentService->applyPaymentToCustomerInvoice(
                invoice: $invoice,
                amount: $validated['amount'],
                payment: $payment
            );

            DB::commit();

            return response()->json([
                'message' => 'Payment applied successfully',
                'data' => [
                    'payment' => $payment,
                    'allocation' => $allocation,
                    'invoice' => $invoice->fresh(),
                ],
            ], 201);

        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation error',
                'error' => $e->getMessage(),
            ], 422);

        } catch (\DomainException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Business logic error',
                'error' => $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to apply payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment history for invoice
     *
     * GET /api/invoices/{invoice}/payments
     */
    public function getPaymentHistory(CustomerInvoice $invoice)
    {
        $allocations = $invoice->paymentAllocations()
            ->with('payment')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => [
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->paid_amount,
                    'outstanding_amount' => $invoice->outstanding_amount,
                    'status' => $invoice->status->value,
                ],
                'payments' => $allocations->map(fn($allocation) => [
                    'id' => $allocation->id,
                    'amount' => $allocation->allocated_amount,
                    'payment_date' => $allocation->payment->payment_date,
                    'payment_method' => $allocation->payment->payment_method,
                    'reference_number' => $allocation->payment->reference_number,
                    'created_at' => $allocation->created_at,
                ]),
            ],
        ]);
    }
}
```

---

## 🚫 2. PURCHASE ORDER CONTROLLER — CREDIT CONTROL

### Check Credit Before Creating PO

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CreditControlService;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    protected CreditControlService $creditControl;

    public function __construct(CreditControlService $creditControl)
    {
        $this->creditControl = $creditControl;
    }

    /**
     * Create new Purchase Order with credit control check
     *
     * POST /api/purchase-orders
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'total_amount' => 'required|numeric|min:0',
            'items' => 'required|array',
            // ... other fields
        ]);

        // CREDIT CONTROL CHECK
        $creditCheck = $this->creditControl->canCreatePO(
            organizationId: $validated['organization_id'],
            poAmount: $validated['total_amount']
        );

        if (!$creditCheck['allowed']) {
            return response()->json([
                'message' => $creditCheck['message'],
                'reason' => $creditCheck['reason'],
                'details' => $creditCheck['details'],
            ], 403); // Forbidden
        }

        // Proceed with PO creation
        $po = PurchaseOrder::create($validated);

        return response()->json([
            'message' => 'Purchase Order created successfully',
            'data' => $po,
        ], 201);
    }

    /**
     * Check if organization can create PO
     *
     * GET /api/organizations/{organizationId}/credit-status
     */
    public function checkCreditStatus(int $organizationId)
    {
        $status = $this->creditControl->getCreditStatus($organizationId);

        return response()->json([
            'data' => $status,
        ]);
    }

    /**
     * Get blocked organizations
     *
     * GET /api/organizations/blocked
     */
    public function getBlockedOrganizations()
    {
        $blocked = $this->creditControl->getBlockedOrganizations();

        return response()->json([
            'data' => $blocked,
        ]);
    }
}
```

---

## 📊 3. REPORT CONTROLLER — AGING REPORT

### Generate Aging Report

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OverdueService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected OverdueService $overdueService;

    public function __construct(OverdueService $overdueService)
    {
        $this->overdueService = $overdueService;
    }

    /**
     * Get AR Aging Report
     *
     * GET /api/reports/ar-aging
     */
    public function getArAgingReport(Request $request)
    {
        $organizationId = $request->query('organization_id');

        $report = $this->overdueService->getAgingReport($organizationId);

        return response()->json([
            'data' => [
                'report' => $report,
                'summary' => [
                    'total_invoices' => array_sum(array_column($report, 'count')),
                    'total_outstanding' => array_sum(array_column($report, 'amount')),
                ],
            ],
        ]);
    }

    /**
     * Get overdue invoices for organization
     *
     * GET /api/organizations/{organizationId}/overdue-invoices
     */
    public function getOverdueInvoices(int $organizationId)
    {
        $overdueInvoices = $this->overdueService->getOverdueInvoicesByOrganization($organizationId);

        return response()->json([
            'data' => [
                'total_count' => $overdueInvoices->count(),
                'total_outstanding' => $overdueInvoices->sum('outstanding'),
                'invoices' => $overdueInvoices,
            ],
        ]);
    }

    /**
     * Check if organization has overdue invoices
     *
     * GET /api/organizations/{organizationId}/has-overdue
     */
    public function hasOverdueInvoices(int $organizationId)
    {
        $hasOverdue = $this->overdueService->hasOverdueInvoices($organizationId);

        return response()->json([
            'data' => [
                'has_overdue' => $hasOverdue,
            ],
        ]);
    }
}
```

---

## 🔄 4. INVOICE CONTROLLER — STATE TRANSITIONS

### Approve Invoice with Event Dispatch

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StateMachineService;
use App\Models\CustomerInvoice;
use App\Enums\CustomerInvoiceStatus;
use App\Events\InvoiceApproved;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected StateMachineService $stateMachine;

    public function __construct(StateMachineService $stateMachine)
    {
        $this->stateMachine = $stateMachine;
    }

    /**
     * Approve (issue) customer invoice
     *
     * POST /api/invoices/{invoice}/approve
     */
    public function approve(CustomerInvoice $invoice)
    {
        try {
            // Validate current status
            if (!$invoice->isDraft()) {
                return response()->json([
                    'message' => 'Only draft invoices can be approved',
                ], 422);
            }

            // Transition to ISSUED
            $this->stateMachine->transitionCustomerInvoice(
                invoice: $invoice,
                targetStatus: CustomerInvoiceStatus::ISSUED,
                context: [
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]
            );

            // Dispatch event (will auto-set due_date)
            event(new InvoiceApproved($invoice, 'customer'));

            return response()->json([
                'message' => 'Invoice approved successfully',
                'data' => $invoice->fresh(),
            ]);

        } catch (\App\Exceptions\InvalidStateTransitionException $e) {
            return response()->json([
                'message' => 'Invalid state transition',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get valid transitions for invoice
     *
     * GET /api/invoices/{invoice}/valid-transitions
     */
    public function getValidTransitions(CustomerInvoice $invoice)
    {
        $validTransitions = $this->stateMachine->getValidCustomerInvoiceTransitions($invoice);

        return response()->json([
            'data' => [
                'current_status' => $invoice->status->value,
                'valid_transitions' => array_map(
                    fn($status) => [
                        'value' => $status->value,
                        'label' => $status->getLabel(),
                    ],
                    $validTransitions
                ),
            ],
        ]);
    }
}
```

---

## 🎨 5. FRONTEND INTEGRATION EXAMPLES

### Vue.js Component — Payment Modal

```vue
<template>
  <div class="modal" v-if="show">
    <div class="modal-content">
      <h3>Tambah Pembayaran</h3>
      
      <div class="invoice-summary">
        <p>Invoice: {{ invoice.invoice_number }}</p>
        <p>Total: Rp {{ formatNumber(invoice.total_amount) }}</p>
        <p>Terbayar: Rp {{ formatNumber(invoice.paid_amount) }}</p>
        <p class="outstanding">
          Outstanding: Rp {{ formatNumber(invoice.outstanding_amount) }}
        </p>
      </div>

      <form @submit.prevent="submitPayment">
        <div class="form-group">
          <label>Jumlah Pembayaran</label>
          <input 
            type="number" 
            v-model="form.amount" 
            :max="invoice.outstanding_amount"
            required
          />
          <small v-if="form.amount > invoice.outstanding_amount" class="error">
            Jumlah melebihi outstanding
          </small>
        </div>

        <div class="form-group">
          <label>Tanggal Pembayaran</label>
          <input type="date" v-model="form.payment_date" required />
        </div>

        <div class="form-group">
          <label>Metode Pembayaran</label>
          <select v-model="form.payment_method" required>
            <option value="transfer">Transfer Bank</option>
            <option value="cash">Tunai</option>
            <option value="check">Cek</option>
          </select>
        </div>

        <div class="form-group">
          <label>Nomor Referensi</label>
          <input type="text" v-model="form.reference_number" />
        </div>

        <div class="form-actions">
          <button type="button" @click="close">Batal</button>
          <button type="submit" :disabled="loading">
            {{ loading ? 'Memproses...' : 'Simpan' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  props: ['invoice', 'show'],
  data() {
    return {
      form: {
        amount: null,
        payment_date: new Date().toISOString().split('T')[0],
        payment_method: 'transfer',
        reference_number: '',
      },
      loading: false,
    };
  },
  methods: {
    async submitPayment() {
      if (this.form.amount > this.invoice.outstanding_amount) {
        alert('Jumlah pembayaran melebihi outstanding');
        return;
      }

      this.loading = true;

      try {
        const response = await axios.post(
          `/api/invoices/${this.invoice.id}/payments`,
          this.form
        );

        alert('Pembayaran berhasil ditambahkan');
        this.$emit('payment-added', response.data.data);
        this.close();

      } catch (error) {
        alert(error.response?.data?.message || 'Gagal menambahkan pembayaran');
      } finally {
        this.loading = false;
      }
    },
    close() {
      this.$emit('close');
    },
    formatNumber(value) {
      return new Intl.NumberFormat('id-ID').format(value);
    },
  },
};
</script>
```

---

### Blade Template — Invoice Detail with Payment

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Invoice Detail: {{ $invoice->invoice_number }}</h3>
            {!! $invoice->getStatusBadge() !!}
        </div>

        <div class="card-body">
            {{-- Payment Summary --}}
            <div class="payment-summary">
                <h4>Ringkasan Pembayaran</h4>
                <table class="table">
                    <tr>
                        <td>Total Amount:</td>
                        <td class="text-end">Rp {{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Paid Amount:</td>
                        <td class="text-end text-success">Rp {{ number_format($invoice->paid_amount, 2) }}</td>
                    </tr>
                    <tr class="fw-bold">
                        <td>Outstanding:</td>
                        <td class="text-end text-danger">Rp {{ number_format($invoice->outstanding_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Due Date:</td>
                        <td class="text-end">
                            {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                            @if($invoice->isOverdueByDate())
                                <span class="badge bg-danger">{{ $invoice->days_overdue }} hari terlambat</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Aging Bucket:</td>
                        <td class="text-end">
                            <span class="badge bg-{{ $invoice->aging_bucket === 'current' ? 'success' : 'warning' }}">
                                {{ $invoice->aging_bucket }}
                            </span>
                        </td>
                    </tr>
                </table>

                @if($invoice->canConfirmPayment())
                    <button class="btn btn-primary" onclick="openPaymentModal()">
                        Tambah Pembayaran
                    </button>
                @endif
            </div>

            {{-- Payment History --}}
            <div class="payment-history mt-4">
                <h4>Riwayat Pembayaran</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Metode</th>
                            <th>Referensi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->paymentAllocations as $allocation)
                            <tr>
                                <td>{{ $allocation->payment->payment_date->format('d M Y') }}</td>
                                <td>Rp {{ number_format($allocation->allocated_amount, 2) }}</td>
                                <td>{{ $allocation->payment->payment_method }}</td>
                                <td>{{ $allocation->payment->reference_number ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Belum ada pembayaran</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
```

---

## 🧪 6. TESTING EXAMPLES

### Feature Test — Payment Application

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\CustomerInvoice;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Enums\CustomerInvoiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = app(PaymentService::class);
    }

    /** @test */
    public function it_can_apply_partial_payment_to_customer_invoice()
    {
        // Arrange
        $invoice = CustomerInvoice::factory()->create([
            'total_amount' => 10000000,
            'paid_amount' => 0,
            'status' => CustomerInvoiceStatus::ISSUED,
        ]);

        $payment = Payment::factory()->create([
            'amount' => 5000000,
        ]);

        // Act
        $allocation = $this->paymentService->applyPaymentToCustomerInvoice(
            $invoice,
            5000000,
            $payment
        );

        // Assert
        $this->assertEquals(5000000, $invoice->fresh()->paid_amount);
        $this->assertEquals(CustomerInvoiceStatus::PARTIAL_PAID, $invoice->fresh()->status);
        $this->assertEquals(5000000, $invoice->fresh()->outstanding_amount);
    }

    /** @test */
    public function it_can_apply_full_payment_to_customer_invoice()
    {
        // Arrange
        $invoice = CustomerInvoice::factory()->create([
            'total_amount' => 10000000,
            'paid_amount' => 5000000,
            'status' => CustomerInvoiceStatus::PARTIAL_PAID,
        ]);

        $payment = Payment::factory()->create([
            'amount' => 5000000,
        ]);

        // Act
        $allocation = $this->paymentService->applyPaymentToCustomerInvoice(
            $invoice,
            5000000,
            $payment
        );

        // Assert
        $this->assertEquals(10000000, $invoice->fresh()->paid_amount);
        $this->assertEquals(CustomerInvoiceStatus::PAID, $invoice->fresh()->status);
        $this->assertEquals(0, $invoice->fresh()->outstanding_amount);
    }

    /** @test */
    public function it_throws_exception_when_payment_exceeds_outstanding()
    {
        // Arrange
        $invoice = CustomerInvoice::factory()->create([
            'total_amount' => 10000000,
            'paid_amount' => 5000000,
            'status' => CustomerInvoiceStatus::PARTIAL_PAID,
        ]);

        $payment = Payment::factory()->create([
            'amount' => 6000000,
        ]);

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('exceeds outstanding balance');

        $this->paymentService->applyPaymentToCustomerInvoice(
            $invoice,
            6000000,
            $payment
        );
    }
}
```

---

## 🎯 SUMMARY

**Services are ready to use in:**
- ✅ API Controllers
- ✅ Web Controllers
- ✅ Console Commands
- ✅ Event Listeners
- ✅ Jobs/Queues

**Key Benefits:**
- ✅ Centralized business logic
- ✅ Transaction safety
- ✅ Event-driven architecture
- ✅ Comprehensive validation
- ✅ Easy to test
- ✅ Scalable & maintainable

**Next:** Implement frontend UI components! 🎨
