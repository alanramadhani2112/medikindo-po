<?php

namespace App\Notifications;

use App\Models\PaymentProof;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to Finance/Admin users when a Healthcare user submits a new payment proof.
 */
class PaymentProofSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly PaymentProof $proof) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $invoice      = $this->proof->customerInvoice;
        $organization = $invoice?->organization?->name ?? 'RS/Klinik';
        $type         = $this->proof->payment_type === 'partial' ? 'Sebagian (Cicilan)' : 'Penuh (Pelunasan)';
        $amount       = 'Rp ' . number_format((float) $this->proof->amount, 0, ',', '.');

        return [
            'title'   => '💳 Bukti Pembayaran Baru Menunggu Review',
            'message' => "{$organization} mengajukan bukti bayar {$type} sebesar {$amount} untuk invoice #{$invoice?->invoice_number}. Mohon segera diverifikasi.",
            'url'     => route('web.payment-proofs.show', $this->proof->id),
            'icon'    => 'warning',
            'type'    => 'payment_proof_submitted',
            'proof_id'       => $this->proof->id,
            'invoice_number' => $invoice?->invoice_number,
            'amount'         => $this->proof->amount,
            'payment_type'   => $this->proof->payment_type,
        ];
    }
}
