<?php

namespace App\Services;

use App\Models\CustomerInvoice;
use App\Models\User;

/**
 * Margin Protection Service
 *
 * Validates that no AR invoice line item has a selling_price below its cost_price.
 * Prevents Medikindo from accidentally billing below cost due to data entry errors.
 *
 * @package App\Services
 */
class MarginProtectionService
{
    public function __construct(
        private readonly BCMathCalculatorService $calculator,
        private readonly AuditService $auditService,
    ) {}

    /**
     * Check all line items of a CustomerInvoice for margin violations.
     *
     * Returns an array of violations. Each violation contains:
     *   - product_name  (string)
     *   - selling_price (string)
     *   - cost_price    (string)
     *   - diff          (string) — negative value indicating the shortfall
     *
     * @param CustomerInvoice $invoice
     * @return array Violations array (empty if all margins are OK)
     */
    public function check(CustomerInvoice $invoice): array
    {
        $violations = [];

        $invoice->loadMissing('lineItems.product');

        foreach ($invoice->lineItems as $lineItem) {
            $sellingPrice = (string) $lineItem->unit_price;
            $costPrice    = (string) ($lineItem->cost_price ?? '0.00');

            // selling_price < cost_price → violation
            if ($this->calculator->lessThan($sellingPrice, $costPrice)) {
                $diff = $this->calculator->subtract($sellingPrice, $costPrice); // negative

                $violations[] = [
                    'line_item_id'  => $lineItem->id,
                    'product_name'  => $lineItem->product?->name ?? $lineItem->product_name ?? 'Unknown',
                    'selling_price' => $sellingPrice,
                    'cost_price'    => $costPrice,
                    'diff'          => $diff,
                ];
            }
        }

        return $violations;
    }

    /**
     * Check whether a user has permission to override margin protection.
     *
     * @param User $user
     * @return bool
     */
    public function canOverride(User $user): bool
    {
        return $user->can('override_margin_protection');
    }

    /**
     * Log a margin protection override to the audit trail.
     *
     * @param CustomerInvoice $invoice
     * @param User            $user
     * @param string          $reason
     * @return void
     */
    public function logOverride(CustomerInvoice $invoice, User $user, string $reason): void
    {
        $violations = $this->check($invoice);

        $this->auditService->log(
            action: 'invoice.margin_override',
            entityType: 'customer_invoice',
            entityId: $invoice->id,
            metadata: [
                'invoice_number' => $invoice->invoice_number,
                'user_id'        => $user->id,
                'user_name'      => $user->name,
                'reason'         => $reason,
                'violations'     => $violations,
                'timestamp'      => now()->toIso8601String(),
            ],
            userId: $user->id
        );
    }
}
