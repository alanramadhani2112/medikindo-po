<?php

namespace App\Observers;

use App\Exceptions\ImmutabilityViolationException;
use App\Models\CustomerInvoice;
use App\Services\ImmutabilityGuardService;

/**
 * Customer Invoice Observer
 * 
 * Enforces immutability rules on customer invoices at the model level.
 * Prevents modifications to financial data after invoice issuance.
 * 
 * @package App\Observers
 */
class CustomerInvoiceObserver
{
    public function __construct(
        private readonly ?ImmutabilityGuardService $immutabilityGuard = null
    ) {}

    /**
     * Handle the CustomerInvoice "updating" event.
     * 
     * @param CustomerInvoice $invoice
     * @return void
     * @throws ImmutabilityViolationException
     */
    public function updating(CustomerInvoice $invoice): void
    {
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
