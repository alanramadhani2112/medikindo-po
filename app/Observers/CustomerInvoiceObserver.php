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

        // Sanitize enums before any processing to prevent "Object could not be converted to string" errors
        $sanitizedChanges = $this->sanitizeEnums($attemptedChanges);

        \Log::info('CustomerInvoiceObserver::updating called', [
            'invoice_id' => $invoice->id,
            'status' => $invoice->status instanceof \BackedEnum ? $invoice->status->value : $invoice->status,
            'dirty' => $sanitizedChanges,
        ]);

        // Resolve service from container if not injected (for testing compatibility)
        $guard = $this->immutabilityGuard ?? app(ImmutabilityGuardService::class);

        // Check immutability and enforce rules
        $guard->enforce($invoice, $attemptedChanges);
    }

    /**
     * Sanitize enum objects to their string values for safe serialization
     * 
     * @param array $data Array that may contain enum values
     * @return array Array with enums converted to strings
     */
    private function sanitizeEnums(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if ($value instanceof \BackedEnum) {
                $sanitized[$key] = $value->value;
            } elseif ($value instanceof \UnitEnum) {
                $sanitized[$key] = $value->name;
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeEnums($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
