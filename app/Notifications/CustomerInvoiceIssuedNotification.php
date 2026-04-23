<?php

namespace App\Notifications;

use App\Models\CustomerInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to Healthcare User when their Customer Invoice is issued and payment is required.
 * Triggered after Supplier Invoice is verified (discrepancy approved or no discrepancy).
 */
class CustomerInvoiceIssuedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly CustomerInvoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $amount  = 'Rp ' . number_format((float) $this->invoice->total_amount, 0, ',', '.');
        $dueDate = $this->invoice->due_date?->format('d M Y') ?? '-';

        return [
            'title'          => '🧾 Invoice Tagihan Diterbitkan — Harap Segera Bayar',
            'message'        => "Invoice AR #{$this->invoice->invoice_number} senilai {$amount} telah diterbitkan. Jatuh tempo: {$dueDate}. Segera upload bukti pembayaran.",
            'url'            => route('web.invoices.customer.show', $this->invoice),
            'icon'           => 'warning',
            'type'           => 'customer_invoice_issued',
            'invoice_id'     => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount'         => $this->invoice->total_amount,
            'due_date'       => $dueDate,
        ];
    }
}
