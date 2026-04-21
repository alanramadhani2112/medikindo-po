<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\PaymentProof;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent after Finance approves a payment proof.
 * - To Healthcare (submitter): inform that payment is confirmed + invoice status
 * - To Finance/Admin: inform that Payment IN & OUT have been auto-recorded
 */
class PaymentProofApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly PaymentProof $proof,
        private readonly string $recipientType, // 'healthcare' | 'finance'
        private readonly ?Payment $paymentIn = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $invoice      = $this->proof->customerInvoice;
        $organization = $invoice?->organization?->name ?? 'RS/Klinik';
        $amount       = 'Rp ' . number_format((float) $this->proof->amount, 0, ',', '.');
        $invoiceNo    = $invoice?->invoice_number ?? '-';

        // Determine invoice status after payment
        $invoiceStatus = match ($invoice?->status?->value ?? '') {
            'paid'         => 'LUNAS ✅',
            'partial_paid' => 'Sebagian Terbayar 🔄',
            default        => $invoice?->status?->getLabel() ?? '-',
        };

        if ($this->recipientType === 'healthcare') {
            return [
                'title'          => '✅ Bukti Pembayaran Disetujui',
                'message'        => "Bukti bayar #{$this->proof->id} sebesar {$amount} untuk invoice {$invoiceNo} telah disetujui. Status invoice: {$invoiceStatus}.",
                'url'            => route('web.payment-proofs.show', $this->proof->id),
                'icon'           => 'success',
                'type'           => 'payment_proof_approved_healthcare',
                'proof_id'       => $this->proof->id,
                'invoice_number' => $invoiceNo,
                'invoice_status' => $invoiceStatus,
                'amount'         => $this->proof->amount,
            ];
        }

        // Finance notification — emphasize auto-recorded Payment IN & OUT
        $paymentInNo = $this->paymentIn?->payment_number ?? 'N/A';

        return [
            'title'          => '🏦 Payment IN & OUT Otomatis Dicatat',
            'message'        => "Approved: Bukti bayar #{$this->proof->id} dari {$organization}. Payment IN ({$paymentInNo}) sebesar {$amount} telah dicatat. Payment OUT ke supplier dialokasikan otomatis.",
            'url'            => route('web.payment-proofs.show', $this->proof->id),
            'icon'           => 'success',
            'type'           => 'payment_proof_approved_finance',
            'proof_id'       => $this->proof->id,
            'payment_in_no'  => $paymentInNo,
            'invoice_number' => $invoiceNo,
            'organization'   => $organization,
            'amount'         => $this->proof->amount,
        ];
    }
}
