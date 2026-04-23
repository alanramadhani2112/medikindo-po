<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the PO creator when a new PO is saved as draft.
 */
class POCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly PurchaseOrder $po) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => '📋 PO Draft Berhasil Dibuat',
            'message' => "Purchase Order #{$this->po->po_number} berhasil disimpan sebagai draft. Lengkapi item dan ajukan untuk persetujuan.",
            'url'     => route('web.po.show', $this->po),
            'icon'    => 'info',
            'type'    => 'po_created',
            'po_id'   => $this->po->id,
            'po_number' => $this->po->po_number,
        ];
    }
}
