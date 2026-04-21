<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceApproved
{
    use Dispatchable, SerializesModels;

    public $invoice;
    public string $invoiceType;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\SupplierInvoice|\App\Models\CustomerInvoice $invoice
     * @param string $invoiceType 'supplier' or 'customer'
     */
    public function __construct($invoice, string $invoiceType)
    {
        $this->invoice = $invoice;
        $this->invoiceType = $invoiceType;
    }
}
