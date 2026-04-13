<?php

namespace App\Observers;

use App\Exceptions\ImmutabilityViolationException;
use App\Models\SupplierInvoice;
use App\Services\ImmutabilityGuardService;

/**
 * Supplier Invoice Observer
 * 
 * Enforces immutability rules on supplier invoices at the model level.
 * Prevents modifications to financial data after invoice issuance.
 * 
 * @package App\Observers
 */
class SupplierInvoiceObserver
{
    public function __construct(
        private readonly ?ImmutabilityGuardService $immutabilityGuard = null
    ) {}

    /**
     * Handle the SupplierInvoice "updating" event.
     * 
     * @param SupplierInvoice $invoice
     * @return void
     * @throws ImmutabilityViolationException
     */
    public function updating(SupplierInvoice $invoice): void
    {
        \Log::info('SupplierInvoiceObserver::updating called', [
            'invoice_id' => $invoice->id,
            'status' => $invoice->status,
            'dirty' => $invoice->getDirty(),
        ]);

        // Get the changed attributes (dirty attributes)
        $attemptedChanges = $invoice->getDirty();

        // Skip if no changes
        if (empty($attemptedChanges)) {
            return;
        }

        // Resolve service from container if not injected (for testing compatibility)
        $guard = $this->immutabilityGuard ?? app(ImmutabilityGuardService::class);

        // Check immutability and enforce rules
        $guard->enforce($invoice, $attemptedChanges);
    }
}
