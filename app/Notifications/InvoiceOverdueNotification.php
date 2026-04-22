<?php

namespace App\Notifications;

use App\Models\CustomerInvoice;
use App\Models\SupplierInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InvoiceOverdueNotification extends Notification
{
    use Queueable;

    private $invoice;
    private string $type;
    private int $daysOverdue;

    /**
     * Create a new notification instance.
     */
    public function __construct(SupplierInvoice|CustomerInvoice $invoice, string $invoiceType = '', int $daysOverdue = 0)
    {
        $this->invoice = $invoice;
        $this->type = $invoiceType ?: ($invoice instanceof SupplierInvoice ? 'AP' : 'AR');
        $this->daysOverdue = $daysOverdue;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Defaulting to database for the bell icon
    }

    /**
     * Get the array representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $amount = $this->invoice->total_amount - $this->invoice->paid_amount;
        $subject = $this->type === 'AP' ? 'Hutang Jatuh Tempo (Supplier)' : 'Piutang Jatuh Tempo (Klinik)';
        
        $partnerName = $this->type === 'AP' 
            ? ($this->invoice->supplier?->name ?? 'Unknown Supplier') 
            : ($this->invoice->organization?->name ?? 'Unknown Organization');
            
        $url = $this->type === 'AP'
            ? route('web.invoices.supplier.show', $this->invoice)
            : route('web.invoices.customer.show', $this->invoice);

        $overdueText = $this->daysOverdue > 0 ? " (telah {$this->daysOverdue} hari)" : '';

        return [
            'invoice_id' => $this->invoice->id,
            'title'      => 'Peringatan: ' . $subject,
            'message'    => "Faktur {$this->invoice->invoice_number} terkait {$partnerName} telah melewati batas jatuh tempo{$overdueText}. Sisa Tagihan: Rp " . number_format($amount, 0, ',', '.'),
            'url'        => $url,
            'icon'       => 'danger',
            'type'       => 'warning'
        ];
    }
}
