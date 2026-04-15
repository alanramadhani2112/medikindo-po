<?php

namespace App\Services;

use App\Exceptions\ImmutabilityViolationException;
use App\Models\CustomerInvoice;
use App\Models\InvoiceModificationAttempt;
use App\Models\SupplierInvoice;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Immutability Guard Service
 * 
 * Enforces immutability rules on issued invoices to ensure financial data integrity.
 * Once an invoice is issued, critical financial fields cannot be modified.
 * All violation attempts are logged for audit trail.
 * 
 * @package App\Services
 */
class ImmutabilityGuardService
{
    /**
     * Financial fields that are immutable after invoice issuance
     */
    private const IMMUTABLE_INVOICE_FIELDS = [
        'total_amount',
        'subtotal_amount',
        'discount_amount',
        'tax_amount',
        'invoice_number',
        'invoice_date',
        'due_date',
        'purchase_order_id',
        'goods_receipt_id',
    ];

    /**
     * Line item fields that are immutable after invoice issuance
     */
    private const IMMUTABLE_LINE_ITEM_FIELDS = [
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'line_total',
    ];

    /**
     * Fields that are allowed to be modified even after issuance
     */
    private const MUTABLE_FIELDS = [
        'status',
        'paid_amount',
        'payment_reference',
        'payment_date',
        'verified_by',
        'verified_at',
        'notes',
        'updated_at',
    ];

    /**
     * Statuses that indicate an invoice is issued and immutable.
     * Includes all AR Invoice statuses per Requirement 6.6.
     */
    private const IMMUTABLE_STATUSES = [
        // AR Invoice statuses (Requirement 6.6)
        'issued',
        'partial_paid',
        'paid',
        'void',
        // Legacy / AP statuses
        'pending_approval',
        'approved',
        'partially_paid',
        'verified',
        'overdue',
    ];

    public function __construct(
        private readonly AuditService $auditService
    ) {}

    /**
     * Check if invoice modifications violate immutability rules
     * 
     * @param Model $invoice SupplierInvoice or CustomerInvoice
     * @param array $attemptedChanges Changed attributes
     * @return array Validation result with violations
     */
    public function checkImmutability(Model $invoice, array $attemptedChanges): array
    {
        // Validate invoice type
        $this->validateInvoiceType($invoice);

        // Check if invoice is in immutable state
        if (!$this->isImmutable($invoice)) {
            return [
                'is_valid' => true,
                'violations' => [],
                'message' => 'Invoice is in draft state and can be modified.',
            ];
        }

        // Check for immutable field violations
        $violations = $this->detectViolations($attemptedChanges);

        $isValid = empty($violations);

        return [
            'is_valid' => $isValid,
            'violations' => $violations,
            'message' => $isValid 
                ? 'All changes are allowed.' 
                : 'Attempted to modify immutable fields: ' . implode(', ', array_keys($violations)),
            'invoice_status' => $invoice->status,
            'attempted_changes' => $attemptedChanges,
        ];
    }

    /**
     * Enforce immutability rules and throw exception if violated
     * 
     * @param Model $invoice Invoice model
     * @param array $attemptedChanges Changed attributes
     * @throws ImmutabilityViolationException If immutability is violated
     */
    public function enforce(Model $invoice, array $attemptedChanges): void
    {
        $result = $this->checkImmutability($invoice, $attemptedChanges);

        if (!$result['is_valid']) {
            // Log the violation attempt
            $this->logViolationAttempt($invoice, $attemptedChanges, $result['violations']);

            throw new ImmutabilityViolationException(
                $result['message'],
                $result['violations']
            );
        }
    }

    /**
     * Check if invoice is in immutable state
     * 
     * @param Model $invoice Invoice model
     * @return bool True if invoice is immutable
     */
    public function isImmutable(Model $invoice): bool
    {
        return in_array($invoice->status, self::IMMUTABLE_STATUSES, true);
    }

    /**
     * Check if a specific field is immutable
     * 
     * @param string $fieldName Field name
     * @return bool True if field is immutable
     */
    public function isFieldImmutable(string $fieldName): bool
    {
        return in_array($fieldName, self::IMMUTABLE_INVOICE_FIELDS, true);
    }

    /**
     * Check if a specific field is mutable
     * 
     * @param string $fieldName Field name
     * @return bool True if field is mutable
     */
    public function isFieldMutable(string $fieldName): bool
    {
        return in_array($fieldName, self::MUTABLE_FIELDS, true);
    }

    /**
     * Get list of immutable fields
     * 
     * @return array Immutable field names
     */
    public function getImmutableFields(): array
    {
        return self::IMMUTABLE_INVOICE_FIELDS;
    }

    /**
     * Get list of mutable fields
     * 
     * @return array Mutable field names
     */
    public function getMutableFields(): array
    {
        return self::MUTABLE_FIELDS;
    }

    /**
     * Detect violations in attempted changes
     * 
     * @param array $attemptedChanges Changed attributes
     * @return array Violations with field names and old/new values
     */
    private function detectViolations(array $attemptedChanges): array
    {
        $violations = [];

        foreach ($attemptedChanges as $field => $newValue) {
            // Skip mutable fields
            if ($this->isFieldMutable($field)) {
                continue;
            }

            // Check if field is immutable
            if ($this->isFieldImmutable($field)) {
                $violations[$field] = [
                    'field' => $field,
                    'new_value' => $newValue,
                    'reason' => 'Field is immutable after invoice issuance',
                ];
            }
        }

        return $violations;
    }

    /**
     * Log immutability violation attempt
     * 
     * @param Model $invoice Invoice model
     * @param array $attemptedChanges Attempted changes
     * @param array $violations Detected violations
     */
    private function logViolationAttempt(Model $invoice, array $attemptedChanges, array $violations): void
    {
        try {
            // Get user information
            $userId = null;
            $ipAddress = null;

            if (function_exists('auth') && auth()->check()) {
                $userId = auth()->id();
            }

            if (function_exists('request')) {
                $ipAddress = request()->ip();
            }

            // Determine invoice type
            $invoiceType = $invoice instanceof SupplierInvoice ? 'supplier' : 'customer';

            // Create modification attempt record
            InvoiceModificationAttempt::create([
                'invoice_type' => $invoiceType,
                'invoice_id' => $invoice->id,
                'user_id' => $userId,
                'attempted_at' => now(),
                'attempted_changes' => $attemptedChanges,
                'rejection_reason' => 'Immutability violation: ' . implode(', ', array_keys($violations)),
                'ip_address' => $ipAddress,
            ]);

            // Log to audit trail
            $this->auditService->log(
                action: 'invoice.immutability_violation',
                entityType: $invoiceType . '_invoice',
                entityId: $invoice->id,
                metadata: [
                    'operation' => 'immutability_check',
                    'invoice_number' => $invoice->invoice_number ?? 'N/A',
                    'invoice_status' => $invoice->status,
                    'attempted_changes' => $attemptedChanges,
                    'violations' => $violations,
                    'ip_address' => $ipAddress,
                ],
                userId: $userId
            );
        } catch (\Throwable $e) {
            // Silently fail if logging is not available
            // The exception will still be thrown to prevent the modification
        }
    }

    /**
     * Validate invoice type
     * 
     * @param Model $invoice Invoice model
     * @throws InvalidArgumentException If not a valid invoice type
     */
    private function validateInvoiceType(Model $invoice): void
    {
        if (!($invoice instanceof SupplierInvoice) && !($invoice instanceof CustomerInvoice)) {
            throw new InvalidArgumentException(
                'Invoice must be an instance of SupplierInvoice or CustomerInvoice. Got: ' . get_class($invoice)
            );
        }
    }

    /**
     * Check line item immutability
     * 
     * @param array $attemptedChanges Changed line item attributes
     * @return array Validation result
     */
    public function checkLineItemImmutability(array $attemptedChanges): array
    {
        $violations = [];

        foreach ($attemptedChanges as $field => $newValue) {
            if (in_array($field, self::IMMUTABLE_LINE_ITEM_FIELDS, true)) {
                $violations[$field] = [
                    'field' => $field,
                    'new_value' => $newValue,
                    'reason' => 'Line item field is immutable after invoice issuance',
                ];
            }
        }

        $isValid = empty($violations);

        return [
            'is_valid' => $isValid,
            'violations' => $violations,
            'message' => $isValid 
                ? 'All line item changes are allowed.' 
                : 'Attempted to modify immutable line item fields: ' . implode(', ', array_keys($violations)),
        ];
    }

    /**
     * Get human-readable violation message
     * 
     * @param array $violations Violations array
     * @return string Formatted message
     */
    public function formatViolationMessage(array $violations): string
    {
        if (empty($violations)) {
            return 'Tidak ada pelanggaran immutability.';
        }

        $fieldNames = array_keys($violations);
        $count = count($fieldNames);

        if ($count === 1) {
            return "Field '{$fieldNames[0]}' tidak dapat diubah setelah invoice diterbitkan.";
        }

        $lastField = array_pop($fieldNames);
        $fieldList = implode(', ', $fieldNames) . ' dan ' . $lastField;

        return "Fields {$fieldList} tidak dapat diubah setelah invoice diterbitkan.";
    }
}
