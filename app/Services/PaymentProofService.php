<?php

namespace App\Services;

use App\Models\PaymentProof;
use App\Models\PaymentDocument;
use App\Models\CustomerInvoice;
use App\Models\SupplierInvoice;
use App\Models\User;
use App\Enums\PaymentProofStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use DomainException;

class PaymentProofService
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly AuditService $auditService,
        private readonly DocumentStorageService $documentStorageService
    ) {}

    /**
     * Submit a new payment proof for a customer invoice.
     */
    public function submitPaymentProof(array $data, User $actor, ?UploadedFile $file = null): PaymentProof
    {
        $invoice = CustomerInvoice::findOrFail($data['customer_invoice_id']);

        if ($invoice->status === 'paid') {
            throw new DomainException('Invoice ini sudah lunas.');
        }

        return DB::transaction(function () use ($invoice, $actor, $data, $file) {
            $proof = PaymentProof::create([
                'customer_invoice_id' => $invoice->id,
                'submitted_by'        => $actor->id,
                'amount'              => $data['amount'],
                'payment_date'        => $data['payment_date'],
                'bank_reference'      => $data['bank_reference'] ?? null,
                'notes'               => $data['notes'] ?? null,
                'status'              => PaymentProofStatus::SUBMITTED,
            ]);

            if ($file) {
                $this->uploadDocument($proof, $file, $actor);
            }

            $this->auditService->log(
                'payment_proof.submitted',
                PaymentProof::class,
                $proof->id,
                ['invoice_id' => $invoice->id, 'amount' => $data['amount']],
                $actor->id
            );

            return $proof;
        });
    }

    /**
     * Verify a payment proof (Finance step).
     */
    public function verifyPaymentProof(PaymentProof $proof, User $actor, array $data = []): PaymentProof
    {
        if ($proof->status !== PaymentProofStatus::SUBMITTED) {
            throw new DomainException('Hanya bukti bayar berstatus "Submitted" yang dapat diverifikasi.');
        }

        $proof->update([
            'status'      => PaymentProofStatus::VERIFIED,
            'verified_by' => $actor->id,
            'verified_at' => now(),
            'notes'       => $data['verification_notes'] ?? $proof->notes,
        ]);

        $this->auditService->log('payment_proof.verified', PaymentProof::class, $proof->id, [], $actor->id);

        return $proof;
    }

    /**
     * Approve a payment proof and process the actual payment.
     */
    public function approvePaymentProof(PaymentProof $proof, User $actor, array $data = []): PaymentProof
    {
        if ($proof->status !== PaymentProofStatus::VERIFIED && $proof->status !== PaymentProofStatus::SUBMITTED) {
            throw new DomainException('Bukti bayar tidak dalam status yang dapat disetujui.');
        }

        return DB::transaction(function () use ($proof, $actor, $data) {
            $proof->update([
                'status'      => PaymentProofStatus::APPROVED,
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'notes'       => $data['approval_notes'] ?? $proof->notes,
            ]);

            // 1. Process Incoming Payment (Payment IN)
            $paymentIn = $this->paymentService->processIncomingPayment([
                'amount'         => $proof->amount,
                'payment_date'   => $proof->payment_date,
                'payment_method' => 'Transfer',
                'reference'      => "Ref Proof #{$proof->id}: " . ($proof->bank_reference ?? ''),
            ], $proof->customerInvoice);

            // 2. Automated Allocation for Payment OUT (to Supplier)
            // Business Rule: Medikindo pays supplier AFTER receiving from RS
            $this->autoAllocatePaymentOut($proof->customerInvoice, $proof->amount);

            $this->auditService->log('payment_proof.approved', PaymentProof::class, $proof->id, [
                'payment_id' => $paymentIn->id
            ], $actor->id);

            return $proof;
        });
    }

    /**
     * Automatically allocate payment to Supplier Invoice when Customer Invoice is paid.
     */
    private function autoAllocatePaymentOut(CustomerInvoice $customerInvoice, float $paidAmount): void
    {
        // Find corresponding Supplier Invoice
        $supplierInvoice = SupplierInvoice::where('purchase_order_id', $customerInvoice->purchase_order_id)
            ->where('goods_receipt_id', $customerInvoice->goods_receipt_id)
            ->where('status', '!=', 'paid')
            ->first();

        if ($supplierInvoice) {
            try {
                // Determine how much we can pay the supplier from this incoming amount
                $remainingToPay = $supplierInvoice->total_amount - $supplierInvoice->paid_amount;
                $amountToPay = min($paidAmount, $remainingToPay);

                if ($amountToPay > 0) {
                    $this->paymentService->processOutgoingPayment([
                        'amount'         => $amountToPay,
                        'payment_date'   => now(),
                        'payment_method' => 'Internal Allocation',
                        'reference'      => "Auto-alloc from Customer Inv #{$customerInvoice->invoice_number}",
                    ], $supplierInvoice);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Auto-allocation to Supplier Invoice failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Reject a payment proof.
     */
    public function rejectPaymentProof(PaymentProof $proof, User $actor, string $reason): PaymentProof
    {
        $proof->update([
            'status'          => PaymentProofStatus::REJECTED,
            'rejection_reason' => $reason,
        ]);

        $this->auditService->log('payment_proof.rejected', PaymentProof::class, $proof->id, ['reason' => $reason], $actor->id);

        return $proof;
    }

    /**
     * Upload a document for a payment proof.
     */
    public function uploadDocument(PaymentProof $proof, UploadedFile $file, User $actor): PaymentDocument
    {
        $path = $this->documentStorageService->store($file, 'payment_proofs/' . $proof->id);

        return PaymentDocument::create([
            'payment_proof_id'  => $proof->id,
            'file_path'         => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type'         => $file->getMimeType(),
            'file_size'         => $file->getSize(),
            'uploaded_by'       => $actor->id,
        ]);
    }
}
