<?php

namespace App\Notifications;

use App\Models\PaymentProof;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to Healthcare submitter when Finance rejects their payment proof.
 */
class PaymentProofRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly PaymentProof $proof,
        private readonly string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $invoice  = $this->proof->customerInvoice;
        $amount   = 'Rp ' . number_format((float) $this->proof->amount, 0, ',', '.');
        $invoiceNo = $invoice?->invoice_number ?? '-';

        return [
            'title'          => '❌ Bukti Pembayaran Ditolak',
            'message'        => "Bukti bayar #{$this->proof->id} sebesar {$amount} untuk invoice {$invoiceNo} ditolak. Alasan: {$this->reason}. Silakan ajukan ulang dengan dokumen yang benar.",
            'url'            => route('web.payment-proofs.show', $this->proof->id),
            'icon'           => 'danger',
            'type'           => 'payment_proof_rejected',
            'proof_id'       => $this->proof->id,
            'invoice_number' => $invoiceNo,
            'amount'         => $this->proof->amount,
            'reason'         => $this->reason,
        ];
    }
}
