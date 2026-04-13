<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Invoice Calculation Service
 * 
 * Comprehensive service for all invoice calculations using pharmaceutical-grade precision.
 * Integrates BCMath, Discount Validator, and Tax Calculator services.
 * 
 * @package App\Services
 */
class InvoiceCalculationService
{
    public function __construct(
        private readonly BCMathCalculatorService $calculator,
        private readonly DiscountValidatorService $discountValidator,
        private readonly TaxCalculatorService $taxCalculator,
        private readonly AuditService $auditService
    ) {}

    /**
     * Calculate a single line item with discount and tax
     * 
     * @param string $quantity Quantity (supports 3 decimals)
     * @param string $unitPrice Unit price
     * @param string|null $discountPercentage Discount percentage (0-100)
     * @param string|null $discountAmount Discount amount
     * @param string|null $taxRate Tax rate percentage (0-100)
     * @return array Line item calculation breakdown
     * @throws InvalidArgumentException If validation fails
     */
    public function calculateLineItem(
        string $quantity,
        string $unitPrice,
        ?string $discountPercentage = null,
        ?string $discountAmount = null,
        ?string $taxRate = null
    ): array {
        // Validate inputs
        if (!is_numeric($quantity) || $this->calculator->lessThan($quantity, $this->calculator->zero())) {
            throw new InvalidArgumentException("Quantity must be a non-negative numeric value. Got: {$quantity}");
        }

        if (!is_numeric($unitPrice) || $this->calculator->lessThan($unitPrice, $this->calculator->zero())) {
            throw new InvalidArgumentException("Unit price must be a non-negative numeric value. Got: {$unitPrice}");
        }

        // Step 1: Calculate line subtotal (quantity * unit_price)
        $lineSubtotal = $this->calculator->multiply($quantity, $unitPrice);

        // Step 2: Validate and calculate discount
        $discountResult = $this->discountValidator->validate(
            $lineSubtotal,
            $discountPercentage,
            $discountAmount
        );

        // Step 3: Calculate tax on discounted amount
        $taxResult = $this->taxCalculator->calculateOnDiscountedAmount(
            $lineSubtotal,
            $discountResult['discount_amount'],
            $taxRate
        );

        // Step 4: Calculate line total (taxable_amount + tax_amount)
        $lineTotal = $this->calculator->add(
            $taxResult['taxable_amount'],
            $taxResult['tax_amount']
        );

        $result = [
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_subtotal' => $lineSubtotal,
            'discount_percentage' => $discountResult['discount_percentage'],
            'discount_amount' => $discountResult['discount_amount'],
            'taxable_amount' => $taxResult['taxable_amount'],
            'tax_rate' => $taxResult['tax_rate'],
            'tax_amount' => $taxResult['tax_amount'],
            'line_total' => $lineTotal,
        ];

        // Log calculation
        $this->logLineItemCalculation($result);

        return $result;
    }

    /**
     * Calculate invoice totals from multiple line items
     * 
     * @param array $lineItems Array of line item calculations
     * @return array Invoice totals breakdown
     */
    public function calculateInvoiceTotals(array $lineItems): array
    {
        if (empty($lineItems)) {
            return [
                'subtotal_amount' => $this->calculator->zero(),
                'discount_amount' => $this->calculator->zero(),
                'tax_amount' => $this->calculator->zero(),
                'total_amount' => $this->calculator->zero(),
                'line_count' => 0,
            ];
        }

        $subtotals = [];
        $discounts = [];
        $taxes = [];
        $totals = [];

        foreach ($lineItems as $item) {
            $subtotals[] = $item['line_subtotal'];
            $discounts[] = $item['discount_amount'];
            $taxes[] = $item['tax_amount'];
            $totals[] = $item['line_total'];
        }

        $invoiceSubtotal = $this->calculator->sum($subtotals);
        $invoiceDiscount = $this->calculator->sum($discounts);
        $invoiceTax = $this->calculator->sum($taxes);
        $invoiceTotal = $this->calculator->sum($totals);

        $result = [
            'subtotal_amount' => $invoiceSubtotal,
            'discount_amount' => $invoiceDiscount,
            'tax_amount' => $invoiceTax,
            'total_amount' => $invoiceTotal,
            'line_count' => count($lineItems),
        ];

        // Log calculation
        $this->logInvoiceTotalsCalculation($result);

        return $result;
    }

    /**
     * Verify tolerance check: sum of line totals must equal invoice total within tolerance
     * 
     * Tolerance: ±0.01 (1 cent)
     * 
     * @param array $lineItems Array of line item calculations
     * @param string $invoiceTotal Expected invoice total
     * @return array Tolerance check result
     */
    public function verifyToleranceCheck(array $lineItems, string $invoiceTotal): array
    {
        // Calculate sum of line totals
        $lineTotals = array_map(fn($item) => $item['line_total'], $lineItems);
        $calculatedTotal = $this->calculator->sum($lineTotals);

        // Calculate difference
        $difference = $this->calculator->subtract($calculatedTotal, $invoiceTotal);
        $absoluteDifference = $this->calculator->abs($difference);

        // Tolerance: 0.01
        $tolerance = '0.01';
        $withinTolerance = $this->calculator->lessThan($absoluteDifference, $tolerance) 
                        || $this->calculator->equals($absoluteDifference, $tolerance);

        $result = [
            'calculated_total' => $calculatedTotal,
            'expected_total' => $invoiceTotal,
            'difference' => $difference,
            'absolute_difference' => $absoluteDifference,
            'tolerance' => $tolerance,
            'within_tolerance' => $withinTolerance,
            'passed' => $withinTolerance,
        ];

        // Log tolerance check
        $this->logToleranceCheck($result);

        if (!$withinTolerance) {
            throw new InvalidArgumentException(
                "Line item totals ({$calculatedTotal}) do not match invoice total ({$invoiceTotal}). " .
                "Difference: {$difference}, Tolerance: ±{$tolerance}"
            );
        }

        return $result;
    }

    /**
     * Calculate complete invoice from line item data
     * 
     * @param array $lineItemsData Array of line item data with quantity, unit_price, discount, tax
     * @return array Complete invoice calculation
     */
    public function calculateCompleteInvoice(array $lineItemsData): array
    {
        $lineItems = [];

        foreach ($lineItemsData as $itemData) {
            $lineItem = $this->calculateLineItem(
                $itemData['quantity'],
                $itemData['unit_price'],
                $itemData['discount_percentage'] ?? null,
                $itemData['discount_amount'] ?? null,
                $itemData['tax_rate'] ?? null
            );

            // Preserve product_id and product_name from input
            $lineItem['product_id'] = $itemData['product_id'] ?? null;
            $lineItem['product_name'] = $itemData['product_name'] ?? 'Unknown Product';

            $lineItems[] = $lineItem;
        }

        $invoiceTotals = $this->calculateInvoiceTotals($lineItems);

        // Verify tolerance
        $toleranceCheck = $this->verifyToleranceCheck($lineItems, $invoiceTotals['total_amount']);

        return [
            'line_items' => $lineItems,
            'invoice_totals' => $invoiceTotals,
            'tolerance_check' => $toleranceCheck,
        ];
    }

    /**
     * Recalculate line item from existing data (for verification)
     * 
     * @param array $lineItemData Existing line item data
     * @return array Recalculated line item
     */
    public function recalculateLineItem(array $lineItemData): array
    {
        return $this->calculateLineItem(
            $lineItemData['quantity'],
            $lineItemData['unit_price'],
            $lineItemData['discount_percentage'] ?? null,
            $lineItemData['discount_amount'] ?? null,
            $lineItemData['tax_rate'] ?? null
        );
    }

    /**
     * Verify invoice calculation integrity
     * 
     * Checks:
     * 1. Line subtotals sum to invoice subtotal
     * 2. Line discounts sum to invoice discount
     * 3. Line taxes sum to invoice tax
     * 4. Line totals sum to invoice total (within tolerance)
     * 
     * @param array $lineItems Line items
     * @param array $invoiceTotals Invoice totals
     * @return array Integrity check results
     */
    public function verifyCalculationIntegrity(array $lineItems, array $invoiceTotals): array
    {
        $calculated = $this->calculateInvoiceTotals($lineItems);

        $checks = [
            'subtotal_match' => $this->calculator->equals(
                $calculated['subtotal_amount'],
                $invoiceTotals['subtotal_amount']
            ),
            'discount_match' => $this->calculator->equals(
                $calculated['discount_amount'],
                $invoiceTotals['discount_amount']
            ),
            'tax_match' => $this->calculator->equals(
                $calculated['tax_amount'],
                $invoiceTotals['tax_amount']
            ),
            'total_match' => $this->calculator->equals(
                $calculated['total_amount'],
                $invoiceTotals['total_amount']
            ),
        ];

        $allPassed = $checks['subtotal_match'] 
                  && $checks['discount_match'] 
                  && $checks['tax_match'] 
                  && $checks['total_match'];

        return [
            'passed' => $allPassed,
            'checks' => $checks,
            'calculated' => $calculated,
            'provided' => $invoiceTotals,
        ];
    }

    /**
     * Log line item calculation to audit trail
     */
    private function logLineItemCalculation(array $calculation): void
    {
        try {
            $userId = null;
            if (function_exists('auth') && auth()->check()) {
                $userId = auth()->id();
            }

            $this->auditService->log(
                action: 'invoice.line_item_calculated',
                entityType: 'invoice_calculation',
                entityId: null,
                metadata: [
                    'operation' => 'calculate_line_item',
                    'inputs' => [
                        'quantity' => $calculation['quantity'],
                        'unit_price' => $calculation['unit_price'],
                        'discount_percentage' => $calculation['discount_percentage'],
                        'discount_amount' => $calculation['discount_amount'],
                        'tax_rate' => $calculation['tax_rate'],
                    ],
                    'outputs' => [
                        'line_subtotal' => $calculation['line_subtotal'],
                        'taxable_amount' => $calculation['taxable_amount'],
                        'tax_amount' => $calculation['tax_amount'],
                        'line_total' => $calculation['line_total'],
                    ],
                ],
                userId: $userId
            );
        } catch (\Throwable $e) {
            // Silently fail if audit logging is not available
        }
    }

    /**
     * Log invoice totals calculation to audit trail
     */
    private function logInvoiceTotalsCalculation(array $totals): void
    {
        try {
            $userId = null;
            if (function_exists('auth') && auth()->check()) {
                $userId = auth()->id();
            }

            $this->auditService->log(
                action: 'invoice.totals_calculated',
                entityType: 'invoice_calculation',
                entityId: null,
                metadata: [
                    'operation' => 'calculate_invoice_totals',
                    'outputs' => $totals,
                ],
                userId: $userId
            );
        } catch (\Throwable $e) {
            // Silently fail if audit logging is not available
        }
    }

    /**
     * Log tolerance check to audit trail
     */
    private function logToleranceCheck(array $result): void
    {
        try {
            $userId = null;
            if (function_exists('auth') && auth()->check()) {
                $userId = auth()->id();
            }

            $this->auditService->log(
                action: 'invoice.tolerance_check',
                entityType: 'invoice_calculation',
                entityId: null,
                metadata: [
                    'operation' => 'verify_tolerance_check',
                    'result' => $result,
                ],
                userId: $userId
            );
        } catch (\Throwable $e) {
            // Silently fail if audit logging is not available
        }
    }
}
