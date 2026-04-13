<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Tax Calculator Service
 * 
 * Calculates tax on discounted amounts for pharmaceutical-grade invoice management.
 * Uses BCMath for precision and applies HALF_UP rounding.
 * 
 * @package App\Services
 */
class TaxCalculatorService
{
    public function __construct(
        private readonly BCMathCalculatorService $calculator,
        private readonly AuditService $auditService
    ) {}

    /**
     * Calculate tax amount on taxable amount
     * 
     * Formula: tax_amount = (taxable_amount * tax_rate / 100)
     * 
     * @param string $taxableAmount Amount subject to tax (after discount)
     * @param string|null $taxRate Tax rate percentage (e.g., "11.00" for 11%)
     * @return array ['tax_rate' => string, 'tax_amount' => string]
     * @throws InvalidArgumentException If validation fails
     */
    public function calculate(string $taxableAmount, ?string $taxRate = null): array
    {
        // Validate taxable amount
        if (!is_numeric($taxableAmount)) {
            throw new InvalidArgumentException(
                "Taxable amount must be a numeric value. Got: {$taxableAmount}"
            );
        }

        if ($this->calculator->lessThan($taxableAmount, $this->calculator->zero())) {
            throw new InvalidArgumentException(
                "Taxable amount must be non-negative. Got: {$taxableAmount}"
            );
        }

        // Handle NULL or zero tax rate
        if ($taxRate === null) {
            return [
                'tax_rate' => $this->calculator->zero(),
                'tax_amount' => $this->calculator->zero(),
            ];
        }

        // Validate tax rate first before using calculator
        if (!is_numeric($taxRate)) {
            throw new InvalidArgumentException(
                "Tax rate must be a numeric value. Got: {$taxRate}"
            );
        }

        if ($this->calculator->equals($taxRate, $this->calculator->zero())) {
            return [
                'tax_rate' => $taxRate,
                'tax_amount' => $this->calculator->zero(),
            ];
        }

        if ($this->calculator->lessThan($taxRate, $this->calculator->zero())) {
            throw new InvalidArgumentException(
                "Tax rate must be non-negative. Got: {$taxRate}%"
            );
        }

        if ($this->calculator->greaterThan($taxRate, $this->calculator->hundred())) {
            throw new InvalidArgumentException(
                "Tax rate cannot exceed 100%. Got: {$taxRate}%"
            );
        }

        // Calculate tax amount: (taxable_amount * tax_rate / 100)
        $taxAmount = $this->calculator->percentage($taxableAmount, $taxRate);
        
        // Apply HALF_UP rounding
        $taxAmount = $this->calculator->round($taxAmount);

        // Log calculation to audit trail
        $this->logCalculation($taxableAmount, $taxRate, $taxAmount);

        return [
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
        ];
    }

    /**
     * Calculate tax on subtotal after discount
     * 
     * Formula: 
     * - taxable_amount = subtotal - discount
     * - tax_amount = (taxable_amount * tax_rate / 100)
     * 
     * @param string $subtotal Subtotal before discount
     * @param string $discountAmount Discount amount
     * @param string|null $taxRate Tax rate percentage
     * @return array ['taxable_amount' => string, 'tax_rate' => string, 'tax_amount' => string]
     */
    public function calculateOnDiscountedAmount(
        string $subtotal,
        string $discountAmount,
        ?string $taxRate = null
    ): array {
        // Calculate taxable amount (subtotal - discount)
        $taxableAmount = $this->calculator->subtract($subtotal, $discountAmount);

        // Ensure taxable amount is not negative
        if ($this->calculator->lessThan($taxableAmount, $this->calculator->zero())) {
            throw new InvalidArgumentException(
                "Taxable amount cannot be negative. Subtotal: {$subtotal}, Discount: {$discountAmount}"
            );
        }

        // Calculate tax
        $result = $this->calculate($taxableAmount, $taxRate);

        return [
            'taxable_amount' => $taxableAmount,
            'tax_rate' => $result['tax_rate'],
            'tax_amount' => $result['tax_amount'],
        ];
    }

    /**
     * Calculate total amount including tax
     * 
     * Formula: total = taxable_amount + tax_amount
     * 
     * @param string $taxableAmount Amount before tax
     * @param string $taxAmount Tax amount
     * @return string Total amount
     */
    public function calculateTotal(string $taxableAmount, string $taxAmount): string
    {
        return $this->calculator->add($taxableAmount, $taxAmount);
    }

    /**
     * Calculate tax-inclusive amount from tax-exclusive amount
     * 
     * Formula: tax_inclusive = tax_exclusive * (1 + tax_rate/100)
     * 
     * @param string $taxExclusiveAmount Amount without tax
     * @param string $taxRate Tax rate percentage
     * @return array ['tax_inclusive_amount' => string, 'tax_amount' => string]
     */
    public function calculateTaxInclusive(string $taxExclusiveAmount, string $taxRate): array
    {
        // Calculate tax amount
        $result = $this->calculate($taxExclusiveAmount, $taxRate);
        
        // Calculate tax-inclusive amount
        $taxInclusiveAmount = $this->calculator->add($taxExclusiveAmount, $result['tax_amount']);

        return [
            'tax_inclusive_amount' => $taxInclusiveAmount,
            'tax_amount' => $result['tax_amount'],
        ];
    }

    /**
     * Calculate tax-exclusive amount from tax-inclusive amount
     * 
     * Formula: tax_exclusive = tax_inclusive / (1 + tax_rate/100)
     * 
     * @param string $taxInclusiveAmount Amount with tax
     * @param string $taxRate Tax rate percentage
     * @return array ['tax_exclusive_amount' => string, 'tax_amount' => string]
     */
    public function calculateTaxExclusive(string $taxInclusiveAmount, string $taxRate): array
    {
        // Handle zero or null tax rate
        if ($taxRate === null || $this->calculator->equals($taxRate, $this->calculator->zero())) {
            return [
                'tax_exclusive_amount' => $taxInclusiveAmount,
                'tax_amount' => $this->calculator->zero(),
            ];
        }

        // Calculate divisor: 1 + (tax_rate / 100)
        $taxRateDecimal = $this->calculator->divide($taxRate, $this->calculator->hundred());
        $divisor = $this->calculator->add($this->calculator->one(), $taxRateDecimal);

        // Calculate tax-exclusive amount
        $taxExclusiveAmount = $this->calculator->divide($taxInclusiveAmount, $divisor);
        $taxExclusiveAmount = $this->calculator->round($taxExclusiveAmount);

        // Calculate tax amount
        $taxAmount = $this->calculator->subtract($taxInclusiveAmount, $taxExclusiveAmount);

        return [
            'tax_exclusive_amount' => $taxExclusiveAmount,
            'tax_amount' => $taxAmount,
        ];
    }

    /**
     * Validate tax rate is within acceptable range
     * 
     * @param string $taxRate Tax rate to validate
     * @return bool True if valid
     * @throws InvalidArgumentException If invalid
     */
    public function validateTaxRate(string $taxRate): bool
    {
        if (!is_numeric($taxRate)) {
            throw new InvalidArgumentException(
                "Tax rate must be a numeric value. Got: {$taxRate}"
            );
        }

        if ($this->calculator->lessThan($taxRate, $this->calculator->zero())) {
            throw new InvalidArgumentException(
                "Tax rate must be non-negative. Got: {$taxRate}%"
            );
        }

        if ($this->calculator->greaterThan($taxRate, $this->calculator->hundred())) {
            throw new InvalidArgumentException(
                "Tax rate cannot exceed 100%. Got: {$taxRate}%"
            );
        }

        return true;
    }

    /**
     * Log tax calculation to audit trail
     * 
     * @param string $taxableAmount
     * @param string $taxRate
     * @param string $taxAmount
     */
    private function logCalculation(string $taxableAmount, string $taxRate, string $taxAmount): void
    {
        try {
            $userId = null;
            if (function_exists('auth') && auth()->check()) {
                $userId = auth()->id();
            }

            $this->auditService->log(
                action: 'tax.calculated',
                entityType: 'tax_calculation',
                entityId: null,
                metadata: [
                    'operation' => 'calculate_tax',
                    'inputs' => [
                        'taxable_amount' => $taxableAmount,
                        'tax_rate' => $taxRate,
                    ],
                    'output' => [
                        'tax_amount' => $taxAmount,
                    ],
                ],
                userId: $userId
            );
        } catch (\Throwable $e) {
            // Silently fail if audit logging is not available
        }
    }
}
