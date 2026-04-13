<?php

namespace App\Notifications;

use App\Models\CustomerInvoice;
use App\Models\SupplierInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewInvoiceNotification extends Notification
{
    use Queueable;

    private string $type;

    public function __construct(private readonly SupplierInvoice|CustomerInvoice $invoice)
    {
        $this->type = $invoice instanceof SupplierInvoice ? 'AP' : 'AR';
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $subject = $this->type === 'AP' ? 'Tagihan Supplier Baru' : 'Tagihan Pelanggan Baru';
        $partner = $this->type === 'AP' 
            ? ($this->invoice->supplier?->name ?? 'Supplier') 
            : ($this->invoice->organization?->name ?? 'Organisasi');

        $url = $this->type === 'AP'
            ? route('web.invoices.supplier.show', $this->invoice)
            : route('web.invoices.customer.show', $this->invoice);

        return [
            'invoice_id'     => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'title'          => $subject,
            'message'        => "Invoice #{$this->invoice->invoice_number} ({$partner}) telah diterbitkan senilai Rp " . number_format($this->invoice->total_amount, 0, ',', '.'),
            'url'            => $url,
            'icon'           => 'info',
            'type'           => 'info'
        ];
    }
}
