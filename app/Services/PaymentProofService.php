<?php

namespace App\Services;

use App\Models\PaymentProof;
use App\Models\PaymentDocument;
use App\Models\CustomerInvoice;
use App\Models\SupplierInvoice;
use App\Models\User;
use App\Enums\PaymentProofStatus;
use App\Notifications\PaymentProofApprovedNotification;
use App\Notifications\PaymentProofSubmittedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\UploadedFile;
use DomainException;

class PaymentProofService
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly AuditService $auditService,
        private readonly DocumentStorageService $documentStorageService
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Submit
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Submit a new payment proof for a customer invoice.
     */
    public function submitPaymentProof(array $data, User $actor, ?UploadedFile $file = null): PaymentProof
    {
        $invoice = CustomerInvoice::findOrFail($data['customer_invoice_id']);

        $statusValue = $invoice->status instanceof \BackedEnum ? $invoice->status->value : $invoice->status;
        if ($statusValue === 'paid') {
            throw new DomainException('Invoice ini sudah lunas.');
        }

        $outstanding = (float) $invoice->total_amount - (float) $invoice->paid_amount;
        if ($outstanding <= 0) {
            throw new DomainException('Invoice ini sudah lunas dan tidak menerima pembayaran baru.');
        }

        $paymentType = $data['payment_type'] ?? 'full';

        // For full payment, always use the exact outstanding amount (prevent floating-point mismatch)
        $amount = $paymentType === 'full'
            ? $outstanding
            : (float) $data['amount'];

        if ($paymentType === 'partial' && $amount >= $outstanding) {
            throw new DomainException('Bayar sebagian harus kurang dari total tagihan tersisa.');
        }

        $proof = DB::transaction(function () use ($invoice, $actor, $data, $file, $amount, $paymentType) {
            $proof = PaymentProof::create([
                'customer_invoice_id'    => $invoice->id,
                'submitted_by'           => $actor->id,
                'amount'                 => $amount,
                'payment_type'           => $paymentType,
                'payment_date'           => $data['payment_date'],
                'payment_method'         => $data['payment_method'] ?? 'Bank Transfer',
                'bank_account_id'        => $invoice->bank_account_id ?? null, // Auto from invoice
                'sender_bank_name'       => $data['sender_bank_name'] ?? null,
                'sender_account_number'  => $data['sender_account_number'] ?? null,
                'giro_number'            => $data['giro_number'] ?? null,
                'giro_due_date'          => $data['giro_due_date'] ?? null,
                'bank_reference'         => $data['bank_reference'] ?? null,
                'notes'                  => $data['notes'] ?? null,
                'status'                 => PaymentProofStatus::SUBMITTED,
            ]);

            if ($file) {
                $this->uploadDocument($proof, $file, $actor);
            }

            $this->auditService->log(
                'payment_proof.submitted',
                PaymentProof::class,
                $proof->id,
                ['invoice_id' => $invoice->id, 'amount' => $amount, 'payment_type' => $paymentType],
                $actor->id
            );

            return $proof;
        });

        // Notify Finance/Admin users about the new submission
        $this->notifyFinanceOnSubmit($proof);

        return $proof;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Verify
    // ─────────────────────────────────────────────────────────────────────────

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

    // ─────────────────────────────────────────────────────────────────────────
    // Approve
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Approve a payment proof and process the actual payment.
     * Returns the PaymentProof and the created Payment IN record.
     */
    public function approvePaymentProof(PaymentProof $proof, User $actor, array $data = []): PaymentProof
    {
        if ($proof->status !== PaymentProofStatus::VERIFIED && $proof->status !== PaymentProofStatus::SUBMITTED) {
            throw new DomainException('Bukti bayar tidak dalam status yang dapat disetujui.');
        }

        $paymentIn = null;

        DB::transaction(function () use ($proof, $actor, $data, &$paymentIn) {
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
                'reference'      => "Bukti Bayar #{$proof->id}: " . ($proof->bank_reference ?? ''),
            ], $proof->customerInvoice);

            // 2. Automated Allocation for Payment OUT (to Supplier)
            // Business Rule: Medikindo pays supplier AFTER receiving from RS
            $this->autoAllocatePaymentOut($proof->customerInvoice, $proof->amount);

            $this->auditService->log('payment_proof.approved', PaymentProof::class, $proof->id, [
                'payment_in_id' => $paymentIn->id,
                'payment_in_no' => $paymentIn->payment_number,
            ], $actor->id);
        });

        // Notify submitter (Healthcare) + Finance about the approval
        $proof->refresh();
        $this->notifyOnApproval($proof, $paymentIn);

        return $proof;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Recall (Healthcare withdraws their own submission)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Recall (cancel) a submitted payment proof.
     * Only allowed while status is SUBMITTED (not yet approved/rejected).
     */
    public function recallPaymentProof(PaymentProof $proof, User $actor, string $reason): PaymentProof
    {
        if (!$proof->canBeRecalled()) {
            throw new DomainException(
                'Bukti pembayaran tidak dapat ditarik kembali. ' .
                'Hanya bukti yang masih berstatus "Menunggu Review" yang dapat ditarik.'
            );
        }

        $proof->update([
            'status'       => PaymentProofStatus::RECALLED,
            'recall_reason' => $reason,
            'recalled_at'  => now(),
        ]);

        $this->auditService->log(
            'payment_proof.recalled',
            PaymentProof::class,
            $proof->id,
            ['reason' => $reason],
            $actor->id
        );

        return $proof;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Super Admin Correction
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Super Admin correction: void the approved payment proof and create a replacement.
     * This reverses the Payment IN/OUT and creates a fresh submission for re-approval.
     *
     * NOTE: This is a high-risk operation — only Super Admin can execute.
     */
    public function correctPaymentProof(PaymentProof $original, User $actor, array $data): PaymentProof
    {
        if (!$original->canBeCorrected()) {
            throw new DomainException('Hanya payment proof yang sudah "Approved" yang dapat dikoreksi.');
        }

        return DB::transaction(function () use ($original, $actor, $data) {
            // 1. Reverse the paid_amount on the customer invoice
            $invoice = $original->customerInvoice;
            $invoice->paid_amount = max(0, (float) $invoice->paid_amount - (float) $original->amount);

            // Revert invoice status
            if ($invoice->paid_amount <= 0) {
                $invoice->status = \App\Enums\CustomerInvoiceStatus::ISSUED;
            } elseif ($invoice->paid_amount < $invoice->total_amount) {
                $invoice->status = \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID;
            }
            $invoice->save();

            // 2. Mark original as recalled with correction note
            $original->update([
                'status'        => PaymentProofStatus::RECALLED,
                'recall_reason' => '[KOREKSI ADMIN] ' . ($data['correction_reason'] ?? 'Koreksi oleh Super Admin'),
                'recalled_at'   => now(),
            ]);

            // 3. Create a new correction submission (pre-filled, needs re-approval)
            $newProof = PaymentProof::create([
                'customer_invoice_id' => $invoice->id,
                'submitted_by'        => $original->submitted_by,
                'amount'              => $data['corrected_amount'] ?? $original->amount,
                'payment_type'        => $data['corrected_payment_type'] ?? $original->payment_type,
                'payment_date'        => $data['corrected_payment_date'] ?? $original->payment_date,
                'bank_reference'      => $data['bank_reference'] ?? $original->bank_reference,
                'notes'               => '[KOREKSI dari #' . $original->id . '] ' . ($data['notes'] ?? ''),
                'status'              => PaymentProofStatus::SUBMITTED,
                'correction_of_id'    => $original->id,
            ]);

            $this->auditService->log('payment_proof.corrected', PaymentProof::class, $original->id, [
                'corrected_by'    => $actor->id,
                'new_proof_id'    => $newProof->id,
                'original_amount' => $original->amount,
                'new_amount'      => $newProof->amount,
                'reason'          => $data['correction_reason'] ?? '-',
            ], $actor->id);

            // Notify Finance that a correction was made and needs re-approval
            $this->notifyFinanceOnSubmit($newProof);

            return $newProof;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Reject
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Reject a payment proof.
     */
    public function rejectPaymentProof(PaymentProof $proof, User $actor, string $reason): PaymentProof
    {
        $proof->update([
            'status'           => PaymentProofStatus::REJECTED,
            'rejection_reason' => $reason,
        ]);

        $this->auditService->log('payment_proof.rejected', PaymentProof::class, $proof->id, ['reason' => $reason], $actor->id);

        // Notify submitter of rejection
        $submitter = $proof->submittedBy;
        if ($submitter) {
            $submitter->notify(new PaymentProofApprovedNotification($proof, 'healthcare'));
        }

        return $proof;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Document
    // ─────────────────────────────────────────────────────────────────────────

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

    // ─────────────────────────────────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Auto-allocate Payment OUT to Supplier after Customer Invoice is paid.
     */
    private function autoAllocatePaymentOut(CustomerInvoice $customerInvoice, float $paidAmount): void
    {
        $supplierInvoice = SupplierInvoice::where('purchase_order_id', $customerInvoice->purchase_order_id)
            ->where('goods_receipt_id', $customerInvoice->goods_receipt_id)
            ->where('status', '!=', 'paid')
            ->first();

        if ($supplierInvoice) {
            try {
                $remainingToPay = $supplierInvoice->total_amount - $supplierInvoice->paid_amount;
                $amountToPay    = min($paidAmount, $remainingToPay);

                if ($amountToPay > 0) {
                    $this->paymentService->processOutgoingPayment([
                        'amount'         => $amountToPay,
                        'payment_date'   => now(),
                        'payment_method' => 'Internal Allocation',
                        'reference'      => "Auto-alloc dari Customer Invoice #{$customerInvoice->invoice_number}",
                    ], $supplierInvoice);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning(
                    "Auto-allocation to Supplier Invoice failed: " . $e->getMessage()
                );
            }
        }
    }

    /**
     * Notify Finance/Admin users when a new payment proof is submitted.
     */
    private function notifyFinanceOnSubmit(PaymentProof $proof): void
    {
        try {
            $financeUsers = User::role(['Finance', 'Super Admin', 'Admin Pusat'])
                ->where('is_active', true)
                ->get();

            Notification::send($financeUsers, new PaymentProofSubmittedNotification($proof));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to send payment proof submitted notification: " . $e->getMessage());
        }
    }

    /**
     * Notify both Finance and Healthcare submitter when a proof is approved.
     */
    private function notifyOnApproval(PaymentProof $proof, ?\App\Models\Payment $paymentIn): void
    {
        try {
            // Notify Healthcare submitter
            $submitter = $proof->submittedBy;
            if ($submitter) {
                $submitter->notify(new PaymentProofApprovedNotification($proof, 'healthcare', $paymentIn));
            }

            // Notify Finance/Admin about auto-recorded Payment IN & OUT
            $financeUsers = User::role(['Finance', 'Super Admin', 'Admin Pusat'])
                ->where('is_active', true)
                ->get();

            Notification::send($financeUsers, new PaymentProofApprovedNotification($proof, 'finance', $paymentIn));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to send payment proof approved notification: " . $e->getMessage());
        }
    }
}
