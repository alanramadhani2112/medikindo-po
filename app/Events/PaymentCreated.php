<?php

namespace App\Events;

use App\Models\Payment;
use App\Models\PaymentAllocation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCreated
{
    use Dispatchable, SerializesModels;

    public Payment $payment;
    public PaymentAllocation $allocation;
    public $invoice;
    public string $invoiceType;

    /**
     * Create a new event instance.
     *
     * @param Payment $payment
     * @param PaymentAllocation $allocation
     * @param \App\Models\SupplierInvoice|\App\Models\CustomerInvoice $invoice
     * @param string $invoiceType 'supplier' or 'customer'
     */
    public function __construct(
        Payment $payment,
        PaymentAllocation $allocation,
        $invoice,
        string $invoiceType
    ) {
        $this->payment = $payment;
        $this->allocation = $allocation;
        $this->invoice = $invoice;
        $this->invoiceType = $invoiceType;
    }
}
