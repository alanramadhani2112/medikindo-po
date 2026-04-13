<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Discount Validator Service
 * 
 * Validates discount business rules for pharmaceutical-grade invoice management.
 * Ensures discounts are within acceptable ranges and follow business logic.
 * 
 * @package App\Services
 */
class DiscountValidatorService
{
    public function __construct(
        private readonly BCMathCalculatorService $calculator,
        private readonly AuditService $auditService
    ) {}

    /**
     * Validate discount parameters and calculate discount amount
     * 
     * Business Rules:
     * - discount_percentage must be between 0.00 and 100.00
     * - discount_amount must be between 0.00 and subtotal
     * - Cannot specify both percentage and amount
     * - If percentage provided, calculate amount
     * 
     * @param string $subtotal Subtotal amount before discount
     * @param string|null $discountPercentage Discount percentage (0-100)
     * @param string|null $discountAmount Discount amount
     * @return array ['discount_percentage' => string, 'discount_amount' => string]
     * @throws InvalidArgumentException If validation fails
     */
    public function validate(
        string $subtotal,
        ?string $discountPercentage = null,
        ?string $discountAmount = null
    ): array {
        // Validate subtotal is numeric and non-negative
        if (!is_numeric($subtotal) || $this->calculator->lessThan($subtotal, $this->calculator->zero())) {
            $this->logValidationFailure('subtotal_invalid', [
                'subtotal' => $subtotal,
            ], 'Subtotal must be a non-negative numeric value');
            
            throw new InvalidArgumentException('Subtotal must be a non-negative numeric value');
        }

        // Rule: Cannot specify both percentage and amount
        if ($discountPercentage !== null && $discountAmount !== null) {
            $this->logValidationFailure('both_discount_types', [
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
            ], 'Cannot specify both discount percentage and discount amount');
            
            throw new InvalidArgumentException(
                'Cannot specify both discount percentage and discount amount. Please provide only one.'
            );
        }

        // If no discount provided, return zeros
        if ($discountPercentage === null && $discountAmount === null) {
            return [
                'discount_percentage' => null,
                'discount_amount' => $this->calculator->zero(),
            ];
        }

        // Validate and process discount percentage
        if ($discountPercentage !== null) {
            return $this->validatePercentage($subtotal, $discountPercentage);
        }

        // Validate and process discount amount
        if ($discountAmount !== null) {
            return $this->validateAmount($subtotal, $discountAmount);
        }

        // Should never reach here
        return [
            'discount_percentage' => null,
            'discount_amount' => $this->calculator->zero(),
        ];
    }

    /**
     * Validate discount percentage and calculate amount
     * 
     * @param string $subtotal
     * @param string $discountPercentage
     * @return array
     * @throws InvalidArgumentException
     */
    private function validatePercentage(string $subtotal, string $discountPercentage): array
    {
        // Validate percentage is numeric
        if (!is_numeric($discountPercentage)) {
            $this->logValidationFailure('percentage_not_numeric', [
                'discount_percentage' => $discountPercentage,
            ], 'Discount percentage must be a numeric value');
            
            throw new InvalidArgumentException(
                "Discount percentage must be a numeric value. Got: {$discountPercentage}"
            );
        }

        // Validate percentage is non-negative
        if ($this->calculator->lessThan($discountPercentage, $this->calculator->zero())) {
            $this->logValidationFailure('percentage_negative', [
                'discount_percentage' => $discountPercentage,
            ], 'Discount percentage must be non-negative');
            
            throw new InvalidArgumentException(
                "Discount percentage must be non-negative. Got: {$discountPercentage}%"
            );
        }

        // Validate percentage is not greater than 100
        if ($this->calculator->greaterThan($discountPercentage, $this->calculator->hundred())) {
            $this->logValidationFailure('percentage_exceeds_100', [
                'discount_percentage' => $discountPercentage,
            ], 'Discount percentage cannot exceed 100%');
            
            throw new InvalidArgumentException(
                "Discount percentage cannot exceed 100%. Got: {$discountPercentage}%"
            );
        }

        // Calculate discount amount from percentage
        $discountAmount = $this->calculator->percentage($subtotal, $discountPercentage);

        return [
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
        ];
    }

    /**
     * Validate discount amount
     * 
     * @param string $subtotal
     * @param string $discountAmount
     * @return array
     * @throws InvalidArgumentException
     */
    private function validateAmount(string $subtotal, string $discountAmount): array
    {
        // Validate amount is numeric
        if (!is_numeric($discountAmount)) {
            $this->logValidationFailure('amount_not_numeric', [
                'discount_amount' => $discountAmount,
            ], 'Discount amount must be a numeric value');
            
            throw new InvalidArgumentException(
                "Discount amount must be a numeric value. Got: {$discountAmount}"
            );
        }

        // Validate amount is non-negative
        if ($this->calculator->lessThan($discountAmount, $this->calculator->zero())) {
            $this->logValidationFailure('amount_negative', [
                'discount_amount' => $discountAmount,
            ], 'Discount amount must be non-negative');
            
            throw new InvalidArgumentException(
                "Discount amount must be non-negative. Got: {$discountAmount}"
            );
        }

        // Validate amount does not exceed subtotal
        if ($this->calculator->greaterThan($discountAmount, $subtotal)) {
            $this->logValidationFailure('amount_exceeds_subtotal', [
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
            ], 'Discount amount cannot exceed subtotal');
            
            throw new InvalidArgumentException(
                "Discount amount cannot exceed subtotal. Subtotal: {$subtotal}, Discount: {$discountAmount}"
            );
        }

        return [
            'discount_percentage' => null,
            'discount_amount' => $discountAmount,
        ];
    }

    /**
     * Calculate discount percentage from amount
     * 
     * @param string $subtotal
     * @param string $discountAmount
     * @return string Discount percentage
     */
    public function calculatePercentageFromAmount(string $subtotal, string $discountAmount): string
    {
        // Avoid division by zero
        if ($this->calculator->equals($subtotal, $this->calculator->zero())) {
            return $this->calculator->zero();
        }

        // Calculate: (discount_amount / subtotal) * 100
        $ratio = $this->calculator->divide($discountAmount, $subtotal);
        return $this->calculator->multiply($ratio, $this->calculator->hundred());
    }

    /**
     * Calculate discount amount from percentage
     * 
     * @param string $subtotal
     * @param string $discountPercentage
     * @return string Discount amount
     */
    public function calculateAmountFromPercentage(string $subtotal, string $discountPercentage): string
    {
        return $this->calculator->percentage($subtotal, $discountPercentage);
    }

    /**
     * Log validation failure to audit trail
     * 
     * @param string $rule Validation rule that failed
     * @param array $inputs Input values
     * @param string $reason Failure reason
     */
    private function logValidationFailure(string $rule, array $inputs, string $reason): void
    {
        try {
            $userId = null;
            if (function_exists('auth') && auth()->check()) {
                $userId = auth()->id();
            }
            
            $this->auditService->log(
                action: 'discount.validation_failed',
                entityType: 'discount_validation',
                entityId: null,
                metadata: [
                    'rule' => $rule,
                    'inputs' => $inputs,
                    'reason' => $reason,
                ],
                userId: $userId
            );
        } catch (\Throwable $e) {
            // Silently fail if audit logging is not available (e.g., in unit tests)
        }
    }
}
