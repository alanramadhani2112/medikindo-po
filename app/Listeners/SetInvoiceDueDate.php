<?php

namespace App\Listeners;

use App\Events\InvoiceApproved;
use Illuminate\Support\Facades\Log;

class SetInvoiceDueDate
{
    /**
     * Default payment terms in days
     */
    protected int $defaultPaymentTerms = 30;

    /**
     * Handle the event.
     */
    public function handle(InvoiceApproved $event): void
    {
        $invoice = $event->invoice;

        // Only set due_date if not already set
        if ($invoice->due_date === null) {
            $invoice->due_date = now()->addDays($this->defaultPaymentTerms);
            $invoice->save();

            Log::info('Due date set for invoice', [
                'invoice_type' => $event->invoiceType,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'payment_terms' => $this->defaultPaymentTerms,
            ]);
        }
    }
}
