<?php

namespace App\Notifications;

use App\Models\SupplierInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to Finance/Admin Pusat when a Supplier Invoice is ready to be verified.
 * Triggered after GR is confirmed and invoices are auto-generated.
 */
class SupplierInvoiceReadyNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly SupplierInvoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $supplier = $this->invoice->supplier?->name ?? 'Supplier';
        $amount   = 'Rp ' . number_format((float) $this->invoice->total_amount, 0, ',', '.');

        return [
            'title'          => '📄 Invoice Supplier Siap Diverifikasi',
            'message'        => "Invoice AP #{$this->invoice->invoice_number} dari {$supplier} senilai {$amount} telah dibuat dan menunggu verifikasi.",
            'url'            => route('web.invoices.supplier.show', $this->invoice),
            'icon'           => 'info',
            'type'           => 'supplier_invoice_ready',
            'invoice_id'     => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount'         => $this->invoice->total_amount,
        ];
    }
}
