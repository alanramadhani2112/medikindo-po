<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceOverdue
{
    use Dispatchable, SerializesModels;

    public $invoice;
    public string $invoiceType;
    public int $daysOverdue;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\SupplierInvoice|\App\Models\CustomerInvoice $invoice
     * @param string $invoiceType 'supplier' or 'customer'
     * @param int $daysOverdue
     */
    public function __construct($invoice, string $invoiceType, int $daysOverdue)
    {
        $this->invoice = $invoice;
        $this->invoiceType = $invoiceType;
        $this->daysOverdue = $daysOverdue;
    }
}
