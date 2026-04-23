<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent when a Payment IN or Payment OUT is recorded.
 * - Payment IN: notifies Finance + Healthcare User
 * - Payment OUT: notifies Finance + Supplier (if applicable)
 */
class PaymentRecordedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Payment $payment,
        private readonly string $recipientType, // 'finance' | 'healthcare'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $amount    = 'Rp ' . number_format((float) $this->payment->amount, 0, ',', '.');
        $isIncoming = $this->payment->type === 'incoming';

        if ($isIncoming) {
            $title   = '💰 Payment IN Dicatat';
            $message = "Pembayaran masuk {$amount} telah dicatat dengan nomor {$this->payment->payment_number}.";
            $icon    = 'success';
        } else {
            $title   = '💸 Payment OUT Dicatat';
            $message = "Pembayaran keluar ke supplier {$amount} telah dicatat dengan nomor {$this->payment->payment_number}.";
            $icon    = 'info';
        }

        return [
            'title'          => $title,
            'message'        => $message,
            'url'            => route('web.payments.show', $this->payment),
            'icon'           => $icon,
            'type'           => $isIncoming ? 'payment_in_recorded' : 'payment_out_recorded',
            'payment_id'     => $this->payment->id,
            'payment_number' => $this->payment->payment_number,
            'payment_type'   => $this->payment->type,
            'amount'         => $this->payment->amount,
        ];
    }
}
