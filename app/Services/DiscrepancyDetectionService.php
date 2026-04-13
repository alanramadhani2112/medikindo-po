<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use InvalidArgumentException;

/**
 * Discrepancy Detection Service
 * 
 * Detects and flags discrepancies between invoice amounts and purchase order amounts.
 * Flags invoices that exceed variance thresholds for approval workflow.
 * 
 * Thresholds:
 * - Variance percentage > 1.00%
 * - OR variance amount > Rp 10,000.00
 * 
 * @package App\Services
 */
class DiscrepancyDetectionService
{
    // Variance thresholds
    private const VARIANCE_PERCENTAGE_THRESHOLD = '1.00'; // 1%
    private const VARIANCE_AMOUNT_THRESHOLD = '10000.00'; // Rp 10,000

    public function __construct(
        private readonly BCMathCalculatorService $calculator,
        private readonly AuditService $auditService
    ) {}

    /**
     * Detect discrepancy between invoice and purchase order
     * 
     * @param string $invoiceTotal Actual invoice total
     * @param PurchaseOrder $purchaseOrder Purchase order to compare against
     * @return array Discrepancy detection result
     */
    public function detect(string $invoiceTotal, PurchaseOrder $purchaseOrder): array
    {
        // Calculate expected total from PO
        $expectedTotal = $this->calculateExpectedTotal($purchaseOrder);

        // Calculate variance
        $varianceAmount = $this->calculator->subtract($invoiceTotal, $expectedTotal);
        $variancePercentage = $this->calculateVariancePercentage($varianceAmount, $expectedTotal);

        // Determine if discrepancy should be flagged
        $discrepancyDetected = $this->shouldFlagDiscrepancy($varianceAmount, $variancePercentage);

        $result = [
            'invoice_total' => $invoiceTotal,
            'expected_total' => $expectedTotal,
            'variance_amount' => $varianceAmount,
            'variance_percentage' => $variancePercentage,
            'discrepancy_detected' => $discrepancyDetected,
            'requires_approval' => $discrepancyDetected,
            'thresholds' => [
                'percentage_threshold' => self::VARIANCE_PERCENTAGE_THRESHOLD,
                'amount_threshold' => self::VARIANCE_AMOUNT_THRESHOLD,
            ],
        ];

        // Log discrepancy detection
        $this->logDiscrepancyDetection($result, $purchaseOrder);

        return $result;
    }

    /**
     * Calculate expected total from purchase order line items
     * 
     * @param PurchaseOrder $purchaseOrder
     * @return string Expected total amount
     */
    public function calculateExpectedTotal(PurchaseOrder $purchaseOrder): string
    {
        $lineItemTotals = [];

        foreach ($purchaseOrder->items as $item) {
            // Calculate: quantity × unit_price
            $lineTotal = $this->calculator->multiply(
                (string) $item->quantity,
                (string) $item->unit_price
            );
            $lineItemTotals[] = $lineTotal;
        }

        return $this->calculator->sum($lineItemTotals);
    }

    /**
     * Calculate variance percentage
     * 
     * Formula: (variance_amount / expected_total) × 100
     * 
     * @param string $varianceAmount Variance amount (can be negative)
     * @param string $expectedTotal Expected total
     * @return string Variance percentage
     */
    public function calculateVariancePercentage(string $varianceAmount, string $expectedTotal): string
    {
        // Handle zero expected total
        if ($this->calculator->equals($expectedTotal, $this->calculator->zero())) {
            return $this->calculator->zero();
        }

        // Calculate absolute variance percentage
        // Use higher scale (4) for intermediate calculation to preserve precision
        $absoluteVariance = $this->calculator->abs($varianceAmount);
        
        // Use bcdiv and bcmul directly with scale=4 for intermediate calculations
        $ratio = bcdiv($absoluteVariance, $expectedTotal, 4);
        $percentage = bcmul($ratio, '100.00', 2); // Final result with scale=2

        return $percentage;
    }

    /**
     * Determine if discrepancy should be flagged
     * 
     * Flags if:
     * - Variance percentage > 1.00%
     * - OR variance amount > Rp 10,000.00 (absolute value)
     * 
     * @param string $varianceAmount Variance amount (can be negative)
     * @param string $variancePercentage Variance percentage (absolute)
     * @return bool True if should be flagged
     */
    public function shouldFlagDiscrepancy(string $varianceAmount, string $variancePercentage): bool
    {
        $absoluteVariance = $this->calculator->abs($varianceAmount);

        // Check percentage threshold
        $percentageExceeded = $this->calculator->greaterThan(
            $variancePercentage,
            self::VARIANCE_PERCENTAGE_THRESHOLD
        );

        // Check amount threshold
        $amountExceeded = $this->calculator->greaterThan(
            $absoluteVariance,
            self::VARIANCE_AMOUNT_THRESHOLD
        );

        return $percentageExceeded || $amountExceeded;
    }

    /**
     * Detect discrepancy with detailed breakdown
     * 
     * @param string $invoiceTotal Invoice total
     * @param PurchaseOrder $purchaseOrder Purchase order
     * @return array Detailed discrepancy analysis
     */
    public function detectWithBreakdown(string $invoiceTotal, PurchaseOrder $purchaseOrder): array
    {
        $result = $this->detect($invoiceTotal, $purchaseOrder);

        // Add detailed breakdown
        $lineItemBreakdown = [];
        foreach ($purchaseOrder->items as $item) {
            $lineTotal = $this->calculator->multiply(
                (string) $item->quantity,
                (string) $item->unit_price
            );

            $lineItemBreakdown[] = [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'Unknown',
                'quantity' => (string) $item->quantity,
                'unit_price' => (string) $item->unit_price,
                'line_total' => $lineTotal,
            ];
        }

        $result['line_item_breakdown'] = $lineItemBreakdown;
        $result['line_item_count'] = count($lineItemBreakdown);

        return $result;
    }

    /**
     * Check if variance is within acceptable range
     * 
     * @param string $varianceAmount Variance amount
     * @param string $variancePercentage Variance percentage
     * @return bool True if within acceptable range
     */
    public function isWithinAcceptableRange(string $varianceAmount, string $variancePercentage): bool
    {
        return !$this->shouldFlagDiscrepancy($varianceAmount, $variancePercentage);
    }

    /**
     * Get discrepancy severity level
     * 
     * @param string $varianceAmount Variance amount
     * @param string $variancePercentage Variance percentage
     * @return string Severity level: 'none', 'low', 'medium', 'high'
     */
    public function getDiscrepancySeverity(string $varianceAmount, string $variancePercentage): string
    {
        $absoluteVariance = $this->calculator->abs($varianceAmount);

        // No discrepancy
        if (!$this->shouldFlagDiscrepancy($varianceAmount, $variancePercentage)) {
            return 'none';
        }

        // High severity: > 5% OR > Rp 50,000
        if ($this->calculator->greaterThan($variancePercentage, '5.00') ||
            $this->calculator->greaterThan($absoluteVariance, '50000.00')) {
            return 'high';
        }

        // Medium severity: > 2% OR > Rp 25,000
        if ($this->calculator->greaterThan($variancePercentage, '2.00') ||
            $this->calculator->greaterThan($absoluteVariance, '25000.00')) {
            return 'medium';
        }

        // Low severity: flagged but below medium thresholds
        return 'low';
    }

    /**
     * Format discrepancy for display
     * 
     * @param array $discrepancy Discrepancy detection result
     * @return array Formatted discrepancy
     */
    public function formatForDisplay(array $discrepancy): array
    {
        $severity = $this->getDiscrepancySeverity(
            $discrepancy['variance_amount'],
            $discrepancy['variance_percentage']
        );

        return [
            'invoice_total' => 'Rp ' . number_format((float) $discrepancy['invoice_total'], 2, ',', '.'),
            'expected_total' => 'Rp ' . number_format((float) $discrepancy['expected_total'], 2, ',', '.'),
            'variance_amount' => 'Rp ' . number_format((float) $discrepancy['variance_amount'], 2, ',', '.'),
            'variance_percentage' => $discrepancy['variance_percentage'] . '%',
            'severity' => $severity,
            'requires_approval' => $discrepancy['requires_approval'],
            'message' => $this->getDiscrepancyMessage($discrepancy, $severity),
        ];
    }

    /**
     * Get human-readable discrepancy message
     * 
     * @param array $discrepancy Discrepancy result
     * @param string $severity Severity level
     * @return string Message
     */
    private function getDiscrepancyMessage(array $discrepancy, string $severity): string
    {
        if ($severity === 'none') {
            return 'Invoice amount matches purchase order within acceptable range.';
        }

        $variance = (float) $discrepancy['variance_amount'];
        $direction = $variance > 0 ? 'higher' : 'lower';
        $absoluteVariance = abs($variance);

        return sprintf(
            'Invoice total is Rp %s %s than expected (%.2f%% variance). %s approval required.',
            number_format($absoluteVariance, 2, ',', '.'),
            $direction,
            (float) $discrepancy['variance_percentage'],
            ucfirst($severity)
        );
    }

    /**
     * Validate discrepancy detection inputs
     * 
     * @param string $invoiceTotal Invoice total
     * @param PurchaseOrder $purchaseOrder Purchase order
     * @throws InvalidArgumentException If validation fails
     */
    public function validateInputs(string $invoiceTotal, PurchaseOrder $purchaseOrder): void
    {
        if (!is_numeric($invoiceTotal)) {
            throw new InvalidArgumentException(
                "Invoice total must be a numeric value. Got: {$invoiceTotal}"
            );
        }

        if ($this->calculator->lessThan($invoiceTotal, $this->calculator->zero())) {
            throw new InvalidArgumentException(
                "Invoice total must be non-negative. Got: {$invoiceTotal}"
            );
        }

        if ($purchaseOrder->items->isEmpty()) {
            throw new InvalidArgumentException(
                "Purchase order must have at least one line item. PO ID: {$purchaseOrder->id}"
            );
        }
    }

    /**
     * Log discrepancy detection to audit trail
     * 
     * @param array $result Discrepancy detection result
     * @param PurchaseOrder $purchaseOrder Purchase order
     */
    private function logDiscrepancyDetection(array $result, PurchaseOrder $purchaseOrder): void
    {
        try {
            $userId = null;
            if (function_exists('auth') && auth()->check()) {
                $userId = auth()->id();
            }

            $this->auditService->log(
                action: 'invoice.discrepancy_detected',
                entityType: 'discrepancy_detection',
                entityId: $purchaseOrder->id,
                metadata: [
                    'operation' => 'detect_discrepancy',
                    'purchase_order_id' => $purchaseOrder->id,
                    'purchase_order_number' => $purchaseOrder->po_number,
                    'result' => $result,
                ],
                userId: $userId
            );
        } catch (\Throwable $e) {
            // Silently fail if audit logging is not available
        }
    }
}
