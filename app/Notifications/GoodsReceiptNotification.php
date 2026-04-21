<?php

namespace App\Notifications;

use App\Models\GoodsReceipt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class GoodsReceiptNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly GoodsReceipt $gr) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $poNumber = $this->gr->purchaseOrder?->po_number ?? 'Unknown-PO';
        
        return (new MailMessage)
            ->subject("Goods Received — PO #{$poNumber}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Goods have been successfully received for Purchase Order #{$poNumber}.")
            ->line("**GR Number:** {$this->gr->gr_number}")
            ->line("**Received Date:** {$this->gr->received_date->format('d M Y H:i')}")
            ->line("**Received By:** {$this->gr->receivedBy?->name}")
            ->action('View Goods Receipt', route('web.goods-receipts.show', $this->gr))
            ->line("An automated invoice has been generated for this receipt.")
            ->salutation("Regards, Medikindo PO System");
    }

    public function toArray(object $notifiable): array
    {
        $poNumber  = $this->gr->purchaseOrder?->po_number ?? 'Unknown-PO';
        $isPartial = $this->gr->status === \App\Models\GoodsReceipt::STATUS_PARTIAL;

        return [
            'gr_id'     => $this->gr->id,
            'gr_number' => $this->gr->gr_number,
            'po_number' => $poNumber,
            'title'     => $isPartial ? 'Barang Diterima Sebagian' : 'Barang Telah Diterima Penuh',
            'message'   => $isPartial
                ? "GR #{$this->gr->gr_number} untuk PO #{$poNumber}: pengiriman sebagian telah dikonfirmasi. Masih ada sisa yang belum dikirim."
                : "GR #{$this->gr->gr_number} untuk PO #{$poNumber}: semua barang telah diterima lengkap.",
            'url'       => route('web.goods-receipts.show', $this->gr),
            'icon'      => $isPartial ? 'warning' : 'success',
            'type'      => $isPartial ? 'warning' : 'success',
        ];
    }
}
