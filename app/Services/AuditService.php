<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        array $metadata = [],
        ?int $userId = null,
    ): AuditLog {
        $finalUserId = $userId ?? Auth::id();
        $organizationId = null;

        if ($finalUserId) {
            $organizationId = \App\Models\User::find($finalUserId)?->organization_id;
        }

        return AuditLog::create([
            'user_id'         => $finalUserId,
            'organization_id' => $organizationId,
            'action'          => $action,
            'entity_type'     => $entityType,
            'entity_id'       => $entityId,
            'metadata'        => $metadata,
            'ip_address'      => request()->ip(),
            'user_agent'      => request()->userAgent(),
        ]);
    }

    // -----------------------------------------------------------------------
    // Invoice-Specific Audit Logging Methods
    // -----------------------------------------------------------------------

    /**
     * Log invoice calculation operation
     * 
     * @param string $operation Operation name (e.g., 'line_item_calculation', 'invoice_totals')
     * @param array $inputs Calculation inputs
     * @param array $output Calculation output
     * @param int|null $invoiceId Invoice ID (if applicable)
     * @param int|null $userId User ID
     * @return AuditLog
     */
    public function logCalculation(
        string $operation,
        array $inputs,
        array $output,
        ?int $invoiceId = null,
        ?int $userId = null
    ): AuditLog {
        return $this->log(
            action: 'invoice.calculation',
            entityType: 'invoice_calculation',
            entityId: $invoiceId,
            metadata: [
                'operation' => $operation,
                'inputs' => $inputs,
                'output' => $output,
                'timestamp' => now()->toIso8601String(),
            ],
            userId: $userId
        );
    }

    /**
     * Log validation failure
     * 
     * @param string $rule Validation rule that failed
     * @param array $inputs Validation inputs
     * @param string $reason Failure reason
     * @param int|null $invoiceId Invoice ID (if applicable)
     * @param int|null $userId User ID
     * @return AuditLog
     */
    public function logValidationFailure(
        string $rule,
        array $inputs,
        string $reason,
        ?int $invoiceId = null,
        ?int $userId = null
    ): AuditLog {
        return $this->log(
            action: 'invoice.validation_failure',
            entityType: 'invoice_validation',
            entityId: $invoiceId,
            metadata: [
                'rule' => $rule,
                'inputs' => $inputs,
                'reason' => $reason,
                'timestamp' => now()->toIso8601String(),
            ],
            userId: $userId
        );
    }

    /**
     * Log discrepancy detection
     * 
     * @param int $invoiceId Invoice ID
     * @param string $expectedTotal Expected total from PO
     * @param string $actualTotal Actual invoice total
     * @param string $varianceAmount Variance amount
     * @param string $variancePercentage Variance percentage
     * @param bool $flagged Whether discrepancy was flagged
     * @param int|null $userId User ID
     * @return AuditLog
     */
    public function logDiscrepancy(
        int $invoiceId,
        string $expectedTotal,
        string $actualTotal,
        string $varianceAmount,
        string $variancePercentage,
        bool $flagged,
        ?int $userId = null
    ): AuditLog {
        return $this->log(
            action: 'invoice.discrepancy_detected',
            entityType: 'invoice_discrepancy',
            entityId: $invoiceId,
            metadata: [
                'expected_total' => $expectedTotal,
                'actual_total' => $actualTotal,
                'variance_amount' => $varianceAmount,
                'variance_percentage' => $variancePercentage,
                'flagged' => $flagged,
                'threshold_percentage' => '1.00',
                'threshold_amount' => '10000.00',
                'timestamp' => now()->toIso8601String(),
            ],
            userId: $userId
        );
    }

    /**
     * Log immutability violation attempt
     * 
     * @param int $invoiceId Invoice ID
     * @param string $invoiceType Invoice type ('supplier' or 'customer')
     * @param array $attemptedChanges Attempted changes
     * @param array $violations Detected violations
     * @param int|null $userId User ID
     * @return AuditLog
     */
    public function logImmutabilityViolation(
        int $invoiceId,
        string $invoiceType,
        array $attemptedChanges,
        array $violations,
        ?int $userId = null
    ): AuditLog {
        return $this->log(
            action: 'invoice.immutability_violation',
            entityType: $invoiceType . '_invoice',
            entityId: $invoiceId,
            metadata: [
                'attempted_changes' => $attemptedChanges,
                'violations' => $violations,
                'violated_fields' => array_keys($violations),
                'ip_address' => request()->ip(),
                'timestamp' => now()->toIso8601String(),
            ],
            userId: $userId
        );
    }

    /**
     * Log concurrency conflict
     * 
     * @param int $invoiceId Invoice ID
     * @param string $invoiceType Invoice type ('supplier' or 'customer')
     * @param int $expectedVersion Expected version number
     * @param array $attemptedChanges Attempted changes
     * @param int|null $userId User ID
     * @return AuditLog
     */
    public function logConcurrencyConflict(
        int $invoiceId,
        string $invoiceType,
        int $expectedVersion,
        array $attemptedChanges,
        ?int $userId = null
    ): AuditLog {
        return $this->log(
            action: 'invoice.concurrency_conflict',
            entityType: $invoiceType . '_invoice',
            entityId: $invoiceId,
            metadata: [
                'expected_version' => $expectedVersion,
                'attempted_changes' => $attemptedChanges,
                'conflict_type' => 'optimistic_locking_failure',
                'timestamp' => now()->toIso8601String(),
            ],
            userId: $userId
        );
    }

    /**
     * Log line item creation
     * 
     * @param int $invoiceId Invoice ID
     * @param string $invoiceType Invoice type ('supplier' or 'customer')
     * @param array $lineItems Line items data
     * @param int|null $userId User ID
     * @return AuditLog
     */
    public function logLineItemsCreated(
        int $invoiceId,
        string $invoiceType,
        array $lineItems,
        ?int $userId = null
    ): AuditLog {
        return $this->log(
            action: 'invoice.line_items_created',
            entityType: $invoiceType . '_invoice',
            entityId: $invoiceId,
            metadata: [
                'line_items_count' => count($lineItems),
                'line_items' => $lineItems,
                'timestamp' => now()->toIso8601String(),
            ],
            userId: $userId
        );
    }

    /**
     * Log tolerance check result
     * 
     * @param int|null $invoiceId Invoice ID
     * @param string $calculatedTotal Calculated total from line items
     * @param string $invoiceTotal Invoice total
     * @param string $difference Difference amount
     * @param bool $passed Whether tolerance check passed
     * @param int|null $userId User ID
     * @return AuditLog
     */
    public function logToleranceCheck(
        ?int $invoiceId,
        string $calculatedTotal,
        string $invoiceTotal,
        string $difference,
        bool $passed,
        ?int $userId = null
    ): AuditLog {
        return $this->log(
            action: 'invoice.tolerance_check',
            entityType: 'invoice_calculation',
            entityId: $invoiceId,
            metadata: [
                'calculated_total' => $calculatedTotal,
                'invoice_total' => $invoiceTotal,
                'difference' => $difference,
                'tolerance' => '0.01',
                'passed' => $passed,
                'timestamp' => now()->toIso8601String(),
            ],
            userId: $userId
        );
    }

    /**
     * Query audit logs for specific invoice
     * 
     * @param int $invoiceId Invoice ID
     * @param string|null $action Filter by action
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getInvoiceAuditTrail(int $invoiceId, ?string $action = null)
    {
        $query = AuditLog::where('entity_id', $invoiceId)
            ->whereIn('entity_type', ['supplier_invoice', 'customer_invoice', 'invoice_calculation', 'invoice_validation', 'invoice_discrepancy'])
            ->orderBy('occurred_at', 'desc');

        if ($action) {
            $query->where('action', $action);
        }

        return $query->get();
    }

    /**
     * Query calculation audit logs
     * 
     * @param int|null $invoiceId Invoice ID (optional)
     * @param string|null $operation Operation type (optional)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCalculationAuditTrail(?int $invoiceId = null, ?string $operation = null)
    {
        $query = AuditLog::where('action', 'invoice.calculation')
            ->orderBy('occurred_at', 'desc');

        if ($invoiceId) {
            $query->where('entity_id', $invoiceId);
        }

        if ($operation) {
            $query->whereJsonContains('metadata->operation', $operation);
        }

        return $query->get();
    }

    /**
     * Query discrepancy audit logs
     * 
     * @param bool|null $flaggedOnly Only flagged discrepancies
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDiscrepancyAuditTrail(?bool $flaggedOnly = null)
    {
        $query = AuditLog::where('action', 'invoice.discrepancy_detected')
            ->orderBy('occurred_at', 'desc');

        if ($flaggedOnly === true) {
            $query->whereJsonContains('metadata->flagged', true);
        }

        return $query->get();
    }

    /**
     * Query immutability violation logs
     * 
     * @param int|null $userId User ID (optional)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getImmutabilityViolations(?int $userId = null)
    {
        $query = AuditLog::where('action', 'invoice.immutability_violation')
            ->orderBy('occurred_at', 'desc');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    /**
     * Query concurrency conflict logs
     * 
     * @param int|null $invoiceId Invoice ID (optional)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getConcurrencyConflicts(?int $invoiceId = null)
    {
        $query = AuditLog::where('action', 'invoice.concurrency_conflict')
            ->orderBy('occurred_at', 'desc');

        if ($invoiceId) {
            $query->where('entity_id', $invoiceId);
        }

        return $query->get();
    }
}
