<?php

namespace App\Notifications;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PartialDeliveryNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly GoodsReceipt $gr,
        private readonly PurchaseOrder $po,
        private readonly int $deliverySequence,
        private readonly int $totalReceived,
        private readonly int $totalOrdered,
        private readonly int $remaining,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $pct = $this->totalOrdered > 0
            ? round(($this->totalReceived / $this->totalOrdered) * 100)
            : 0;

        return [
            'type'              => 'partial_delivery',
            'icon'              => 'warning',
            'title'             => "Pengiriman Sebagian — {$this->po->po_number}",
            'message'           => "Pengiriman ke-{$this->deliverySequence}: {$this->totalReceived}/{$this->totalOrdered} unit diterima ({$pct}%). Sisa {$this->remaining} unit belum dikirim.",
            'gr_id'             => $this->gr->id,
            'gr_number'         => $this->gr->gr_number,
            'po_id'             => $this->po->id,
            'po_number'         => $this->po->po_number,
            'delivery_sequence' => $this->deliverySequence,
            'total_received'    => $this->totalReceived,
            'total_ordered'     => $this->totalOrdered,
            'remaining'         => $this->remaining,
            'pct'               => $pct,
            'url'               => route('web.goods-receipts.show', $this->gr),
            'action_url'        => route('web.invoices.supplier.create'),
            'action_label'      => 'Buat Invoice Sebagian',
        ];
    }
}
