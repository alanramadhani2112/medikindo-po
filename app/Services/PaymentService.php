<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\CustomerInvoice;
use App\Models\SupplierInvoice;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\SupplierInvoiceStatus;
use App\Events\PaymentCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DomainException;

class PaymentService
{
    public function __construct(private readonly AuditService $auditService) {}

    // ─────────────────────────────────────────────────────────────────────────
    // PRIMARY FLOW: Called by PaymentProofService on approval (AUTOMATIC)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Process an incoming payment (AR) — called automatically when Payment Proof is approved.
     * Also used for manual entry via PaymentWebController.
     */
    public function processIncomingPayment(array $data, CustomerInvoice $invoice): Payment
    {
        return DB::transaction(function () use ($data, $invoice) {
            $amount = (float) $data['amount'];

            if ($amount <= 0) {
                throw new DomainException('Jumlah pembayaran harus lebih dari 0.');
            }

            $outstanding = (float) $invoice->total_amount - (float) $invoice->paid_amount;
            if ($amount > $outstanding) {
                throw new DomainException(
                    "Jumlah pembayaran (Rp " . number_format($amount, 0, ',', '.') . ") " .
                    "melebihi sisa tagihan (Rp " . number_format($outstanding, 0, ',', '.') . ")."
                );
            }

            // Handle file upload if present
            $paymentProofPath = null;
            if (isset($data['payment_proof_file']) && $data['payment_proof_file'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $data['payment_proof_file'];
                $filename = 'payment_' . now()->format('YmdHis') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $paymentProofPath = $file->storeAs('payment_proofs', $filename, 'public');
            }

            $payment = Payment::create([
                'payment_number'        => 'PAY-IN-' . now()->format('YmdHis') . '-' . $invoice->id,
                'type'                  => 'incoming',
                'organization_id'       => $invoice->organization_id,
                'amount'                => $amount,
                'payment_date'          => $data['payment_date'] ?? now(),
                'payment_method'        => $data['payment_method'] ?? 'Bank Transfer',
                'sender_bank_name'      => $data['sender_bank_name'] ?? null,
                'sender_account_number' => $data['sender_account_number'] ?? null,
                'giro_number'           => $data['giro_number'] ?? null,
                'giro_due_date'         => $data['giro_due_date'] ?? null,
                'issuing_bank'          => $data['issuing_bank'] ?? null,
                'receipt_number'        => $data['receipt_number'] ?? null,
                'payment_proof_path'    => $paymentProofPath,
                'bank_account_id'       => $data['bank_account_id'] ?? null,
                'reference'             => $data['reference'] ?? $data['giro_reference'] ?? null,
                'notes'                 => $data['notes'] ?? "Pembayaran Invoice {$invoice->invoice_number}",
                'status'                => 'completed',
            ]);

            $allocation = $payment->allocations()->create([
                'customer_invoice_id' => $invoice->id,
                'allocated_amount'    => $amount,
            ]);

            // Update invoice paid_amount & status
            $invoice->paid_amount = (float) $invoice->paid_amount + $amount;
            $invoice->status = $invoice->paid_amount >= (float) $invoice->total_amount
                ? CustomerInvoiceStatus::PAID
                : CustomerInvoiceStatus::PARTIAL_PAID;
            $invoice->save();

            // Release credit control
            try {
                app(CreditControlService::class)->releaseCreditByAmount(
                    $invoice->organization_id,
                    clone $invoice->purchaseOrder,
                    $amount
                );
            } catch (\Exception $e) {
                Log::warning('Credit release failed (non-critical): ' . $e->getMessage());
            }

            $this->auditService->log(
                'payment.incoming',
                Payment::class,
                $payment->id,
                ['amount' => $amount, 'invoice_id' => $invoice->id, 'new_status' => $invoice->status->value]
            );

            event(new PaymentCreated($payment, $allocation, $invoice, 'customer'));

            return $payment;
        });
    }

    /**
     * Process an outgoing payment (AP) — called automatically by autoAllocatePaymentOut.
     * Also used for manual entry via PaymentWebController.
     *
     * Business Rule: Medikindo only pays supplier AFTER receiving payment from RS/Klinik.
     */
    public function processOutgoingPayment(array $data, SupplierInvoice $invoice): Payment
    {
        return DB::transaction(function () use ($data, $invoice) {
            $amount = (float) $data['amount'];

            if ($amount <= 0) {
                throw new DomainException('Jumlah pembayaran harus lebih dari 0.');
            }

            $outstanding = (float) $invoice->total_amount - (float) $invoice->paid_amount;
            if ($amount > $outstanding) {
                throw new DomainException(
                    "Jumlah pembayaran (Rp " . number_format($amount, 0, ',', '.') . ") " .
                    "melebihi sisa hutang (Rp " . number_format($outstanding, 0, ',', '.') . ")."
                );
            }

            // ── CRITICAL: Payment IN must be received before Payment OUT ──────
            // Business Rule: Medikindo only pays supplier AFTER RS/Klinik pays
            $customerInvoice = CustomerInvoice::where('purchase_order_id', $invoice->purchase_order_id)
                ->where('goods_receipt_id', $invoice->goods_receipt_id)
                ->first();

            if ($customerInvoice) {
                $totalPaymentOut = (float) $invoice->paid_amount + $amount;
                if ((float) $customerInvoice->paid_amount < $totalPaymentOut) {
                    $shortfall = $totalPaymentOut - (float) $customerInvoice->paid_amount;
                    throw new DomainException(
                        'Tidak dapat membayar supplier. RS/Klinik belum membayar cukup. ' .
                        'Pembayaran dari RS: Rp ' . number_format($customerInvoice->paid_amount, 0, ',', '.') . ', ' .
                        'Total ke supplier (termasuk ini): Rp ' . number_format($totalPaymentOut, 0, ',', '.') . '. ' .
                        'Kekurangan: Rp ' . number_format($shortfall, 0, ',', '.') . '.'
                    );
                }
            }

            $payment = Payment::create([
                'payment_number'  => 'PAY-OUT-' . now()->format('YmdHis') . '-' . $invoice->id,
                'type'            => 'outgoing',
                'supplier_id'     => $invoice->supplier_id,
                'amount'          => $amount,
                'payment_date'    => $data['payment_date'] ?? now(),
                'payment_method'  => $data['payment_method'] ?? 'Bank Transfer',
                'bank_account_id' => $data['bank_account_id'] ?? null,
                'bank_name_manual'=> $data['bank_name_manual'] ?? null,
                'reference'       => $data['reference'] ?? null,
                'description'     => $data['description'] ?? "Pembayaran ke Supplier Invoice {$invoice->invoice_number}",
                'surcharge_amount'    => $data['surcharge_amount'] ?? 0,
                'surcharge_percentage'=> $data['surcharge_percentage'] ?? 0,
                'status'          => 'completed',
            ]);

            $allocation = $payment->allocations()->create([
                'supplier_invoice_id' => $invoice->id,
                'allocated_amount'    => $amount,
            ]);

            // Update supplier invoice paid_amount & status
            $invoice->paid_amount = (float) $invoice->paid_amount + $amount;
            $invoice->status = $invoice->paid_amount >= (float) $invoice->total_amount
                ? SupplierInvoiceStatus::PAID
                : SupplierInvoiceStatus::VERIFIED;
            $invoice->save();

            $this->auditService->log(
                'payment.outgoing',
                Payment::class,
                $payment->id,
                [
                    'amount'               => $amount,
                    'invoice_id'           => $invoice->id,
                    'customer_invoice_id'  => $customerInvoice?->id,
                    'customer_paid_amount' => $customerInvoice?->paid_amount,
                    'supplier_total_paid'  => $invoice->paid_amount,
                    'cashflow_validated'   => $customerInvoice !== null,
                ]
            );

            event(new PaymentCreated($payment, $allocation, $invoice, 'supplier'));

            return $payment;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Get total allocated payments for an invoice.
     */
    public function getTotalPayments(CustomerInvoice|SupplierInvoice $invoice): float
    {
        return (float) $invoice->paymentAllocations()->sum('allocated_amount');
    }

    /**
     * Validate that paid_amount matches sum of allocations.
     */
    public function validatePaymentConsistency(CustomerInvoice|SupplierInvoice $invoice): bool
    {
        return abs((float) $invoice->paid_amount - $this->getTotalPayments($invoice)) < 0.01;
    }

    /**
     * Recalculate paid_amount from allocations if inconsistent.
     */
    public function recalculatePayments(CustomerInvoice|SupplierInvoice $invoice): void
    {
        $allocated = $this->getTotalPayments($invoice);

        if (abs((float) $invoice->paid_amount - $allocated) >= 0.01) {
            Log::warning('Payment inconsistency detected, recalculating', [
                'type'               => get_class($invoice),
                'id'                 => $invoice->id,
                'old_paid_amount'    => $invoice->paid_amount,
                'calculated_amount'  => $allocated,
            ]);
            $invoice->paid_amount = $allocated;
            $invoice->save();
        }
    }
}
