<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\CustomerInvoice;
use App\Models\SupplierInvoice;
use Illuminate\Support\Facades\DB;
use DomainException;

class PaymentService
{
    public function __construct(private readonly AuditService $auditService) {}

    public function processIncomingPayment(array $data, CustomerInvoice $invoice): Payment
    {
        return DB::transaction(function () use ($data, $invoice) {
            $amount = $data['amount'];
            
            // Prevent over-paying the invoice
            if ($amount > ($invoice->total_amount - $invoice->paid_amount)) {
                throw new DomainException("Payment amount exceeds outstanding invoice balance.");
            }

            $payment = Payment::create([
                'payment_number' => 'PAY-IN-' . time(),
                'type'           => 'incoming',
                'organization_id' => $invoice->organization_id,
                'amount'         => $amount,
                'payment_date'   => $data['payment_date'] ?? now(),
                'payment_method' => $data['payment_method'],
                'reference'      => $data['reference'] ?? null,
                'status'         => 'completed',
            ]);

            // Track detailed allocation logic
            $payment->allocations()->create([
                'customer_invoice_id' => $invoice->id,
                'allocated_amount'    => $amount,
            ]);

            // Update invoice state machine
            $invoice->paid_amount += $amount;
            $invoice->status = $invoice->paid_amount >= $invoice->total_amount ? 'paid' : 'partial';
            $invoice->save();

            // Link with Financial Control to release organization credit
            app(CreditControlService::class)->releaseCreditByAmount($invoice->organization_id, clone $invoice->purchaseOrder, $amount);

            $this->auditService->log('payment.incoming', Payment::class, $payment->id, ['amount' => $amount, 'invoice_id' => $invoice->id]);

            return $payment;
        });
    }

    public function processOutgoingPayment(array $data, SupplierInvoice $invoice): Payment
    {
        return DB::transaction(function () use ($data, $invoice) {
            $amount = $data['amount'];
            
            if ($amount > ($invoice->total_amount - $invoice->paid_amount)) {
                throw new DomainException("Payment amount exceeds outstanding invoice balance.");
            }

            // -----------------------------------------------------------------------
            // CRITICAL VALIDATION: Payment IN must be received before Payment OUT
            // Business Rule: Medikindo only pays supplier AFTER receiving payment from RS/Klinik
            // -----------------------------------------------------------------------
            
            $customerInvoice = CustomerInvoice::where('purchase_order_id', $invoice->purchase_order_id)
                ->where('goods_receipt_id', $invoice->goods_receipt_id)
                ->first();

            if (!$customerInvoice) {
                throw new DomainException(
                    'Tidak dapat membayar supplier. Customer invoice tidak ditemukan untuk PO dan GR yang sama.'
                );
            }

            // Calculate total payment OUT so far (including this payment)
            $totalPaymentOut = $invoice->paid_amount + $amount;

            // Validate: Total Payment IN must be >= Total Payment OUT
            if ($customerInvoice->paid_amount < $totalPaymentOut) {
                $shortfall = $totalPaymentOut - $customerInvoice->paid_amount;
                throw new DomainException(
                    'Tidak dapat membayar supplier. RS/Klinik belum membayar cukup. ' .
                    'Pembayaran dari RS: Rp ' . number_format($customerInvoice->paid_amount, 0, ',', '.') . ', ' .
                    'Total pembayaran ke supplier (termasuk ini): Rp ' . number_format($totalPaymentOut, 0, ',', '.') . '. ' .
                    'Kekurangan: Rp ' . number_format($shortfall, 0, ',', '.') . '. ' .
                    'Harap tunggu pembayaran dari RS terlebih dahulu.'
                );
            }

            $payment = Payment::create([
                'payment_number' => 'PAY-OUT-' . time(),
                'type'           => 'outgoing',
                'supplier_id'    => $invoice->supplier_id,
                'amount'         => $amount,
                'payment_date'   => $data['payment_date'] ?? now(),
                'payment_method' => $data['payment_method'],
                'reference'      => $data['reference'] ?? null,
                'status'         => 'completed',
            ]);

            $payment->allocations()->create([
                'supplier_invoice_id' => $invoice->id,
                'allocated_amount'    => $amount,
            ]);

            $invoice->paid_amount += $amount;
            $invoice->status = $invoice->paid_amount >= $invoice->total_amount ? 'paid' : 'partial';
            $invoice->save();

            $this->auditService->log(
                'payment.outgoing', 
                Payment::class, 
                $payment->id, 
                [
                    'amount' => $amount, 
                    'invoice_id' => $invoice->id,
                    'customer_invoice_id' => $customerInvoice->id,
                    'customer_paid_amount' => $customerInvoice->paid_amount,
                    'supplier_total_paid' => $invoice->paid_amount,
                    'cashflow_validated' => true,
                ]
            );

            return $payment;
        });
    }
}
