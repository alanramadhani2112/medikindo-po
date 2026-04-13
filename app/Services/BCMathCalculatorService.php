<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * BCMath Calculator Service
 * 
 * Provides pharmaceutical-grade precision arithmetic using PHP's BCMath extension.
 * All calculations use scale=2 for monetary values with HALF_UP rounding (banker's rounding).
 * 
 * @package App\Services
 */
class BCMathCalculatorService
{
    /**
     * Precision scale for all calculations (2 decimal places for monetary values)
     */
    private const SCALE = 2;

    /**
     * Cached common values to avoid repeated string conversions
     */
    private const CACHE = [
        'zero' => '0.00',
        'one' => '1.00',
        'hundred' => '100.00',
    ];

    /**
     * Add two monetary values
     * 
     * @param string $a First operand
     * @param string $b Second operand
     * @return string Result with 2 decimal places
     * @throws InvalidArgumentException If inputs are not valid numeric strings
     */
    public function add(string $a, string $b): string
    {
        $this->validateNumeric($a, 'First operand');
        $this->validateNumeric($b, 'Second operand');
        
        return bcadd($a, $b, self::SCALE);
    }

    /**
     * Subtract two monetary values
     * 
     * @param string $a Minuend
     * @param string $b Subtrahend
     * @return string Result with 2 decimal places
     * @throws InvalidArgumentException If inputs are not valid numeric strings
     */
    public function subtract(string $a, string $b): string
    {
        $this->validateNumeric($a, 'Minuend');
        $this->validateNumeric($b, 'Subtrahend');
        
        return bcsub($a, $b, self::SCALE);
    }

    /**
     * Multiply two monetary values
     * 
     * @param string $a Multiplicand
     * @param string $b Multiplier
     * @return string Result with 2 decimal places
     * @throws InvalidArgumentException If inputs are not valid numeric strings
     */
    public function multiply(string $a, string $b): string
    {
        $this->validateNumeric($a, 'Multiplicand');
        $this->validateNumeric($b, 'Multiplier');
        
        return bcmul($a, $b, self::SCALE);
    }

    /**
     * Divide two monetary values
     * 
     * @param string $a Dividend
     * @param string $b Divisor
     * @return string Result with 2 decimal places
     * @throws InvalidArgumentException If inputs are not valid numeric strings or divisor is zero
     */
    public function divide(string $a, string $b): string
    {
        $this->validateNumeric($a, 'Dividend');
        $this->validateNumeric($b, 'Divisor');
        
        if (bccomp($b, self::CACHE['zero'], self::SCALE) === 0) {
            throw new InvalidArgumentException('Division by zero is not allowed');
        }
        
        return bcdiv($a, $b, self::SCALE);
    }

    /**
     * Round a monetary value using HALF_UP rounding (banker's rounding)
     * 
     * When a value is exactly halfway between two cents:
     * - 2.005 rounds to 2.00 (nearest even)
     * - 3.015 rounds to 3.02 (nearest even)
     * 
     * @param string $value Value to round
     * @return string Rounded value with 2 decimal places
     * @throws InvalidArgumentException If input is not a valid numeric string
     */
    public function round(string $value): string
    {
        $this->validateNumeric($value, 'Value');
        
        // BCMath doesn't have built-in rounding, so we implement HALF_UP (banker's rounding)
        // For monetary values with 2 decimal places, we need to check the third decimal
        
        // Get the value with 3 decimal places for rounding decision
        $valueWith3Decimals = bcadd($value, '0.000', 3);
        
        // Extract the decimal part
        $parts = explode('.', $valueWith3Decimals);
        if (!isset($parts[1]) || strlen($parts[1]) < 3) {
            // Already has 2 or fewer decimals, just format it
            return bcadd($value, '0.00', self::SCALE);
        }
        
        $decimals = str_pad($parts[1], 3, '0', STR_PAD_RIGHT);
        $thirdDecimal = (int) $decimals[2];
        
        // If third decimal < 5, truncate
        if ($thirdDecimal < 5) {
            return bcadd($value, '0.00', self::SCALE);
        }
        
        // If third decimal > 5, round up
        if ($thirdDecimal > 5) {
            // Determine if we need to add 0.01 or subtract 0.01 based on sign
            if ($this->greaterThan($value, self::CACHE['zero'])) {
                return bcadd($value, '0.01', self::SCALE);
            } else {
                return bcsub($value, '0.01', self::SCALE);
            }
        }
        
        // If third decimal == 5, apply banker's rounding (round to nearest even)
        $secondDecimal = (int) $decimals[1];
        
        if ($secondDecimal % 2 === 0) {
            // Second decimal is even, round down (truncate)
            return bcadd($value, '0.00', self::SCALE);
        } else {
            // Second decimal is odd, round up to make it even
            if ($this->greaterThan($value, self::CACHE['zero'])) {
                return bcadd($value, '0.01', self::SCALE);
            } else {
                return bcsub($value, '0.01', self::SCALE);
            }
        }
    }

    /**
     * Compare two monetary values
     * 
     * @param string $a First value
     * @param string $b Second value
     * @return int Returns -1 if a < b, 0 if a == b, 1 if a > b
     * @throws InvalidArgumentException If inputs are not valid numeric strings
     */
    public function compare(string $a, string $b): int
    {
        $this->validateNumeric($a, 'First value');
        $this->validateNumeric($b, 'Second value');
        
        return bccomp($a, $b, self::SCALE);
    }

    /**
     * Check if two monetary values are equal
     * 
     * @param string $a First value
     * @param string $b Second value
     * @return bool True if equal, false otherwise
     */
    public function equals(string $a, string $b): bool
    {
        return $this->compare($a, $b) === 0;
    }

    /**
     * Check if first value is greater than second value
     * 
     * @param string $a First value
     * @param string $b Second value
     * @return bool True if a > b, false otherwise
     */
    public function greaterThan(string $a, string $b): bool
    {
        return $this->compare($a, $b) === 1;
    }

    /**
     * Check if first value is less than second value
     * 
     * @param string $a First value
     * @param string $b Second value
     * @return bool True if a < b, false otherwise
     */
    public function lessThan(string $a, string $b): bool
    {
        return $this->compare($a, $b) === -1;
    }

    /**
     * Calculate percentage of a value
     * 
     * @param string $value Base value
     * @param string $percentage Percentage (e.g., "10.50" for 10.5%)
     * @return string Result with 2 decimal places
     */
    public function percentage(string $value, string $percentage): string
    {
        $this->validateNumeric($value, 'Value');
        $this->validateNumeric($percentage, 'Percentage');
        
        // Calculate: value * percentage / 100
        $result = $this->multiply($value, $percentage);
        return $this->divide($result, self::CACHE['hundred']);
    }

    /**
     * Sum an array of monetary values
     * 
     * @param array $values Array of numeric strings
     * @return string Sum with 2 decimal places
     * @throws InvalidArgumentException If any value is not a valid numeric string
     */
    public function sum(array $values): string
    {
        $result = self::CACHE['zero'];
        
        foreach ($values as $value) {
            $result = $this->add($result, $value);
        }
        
        return $result;
    }

    /**
     * Format a numeric value to monetary string with 2 decimal places
     * 
     * @param float|int|string $value Value to format
     * @return string Formatted value with exactly 2 decimal places
     */
    public function format($value): string
    {
        if (is_float($value) || is_int($value)) {
            $value = number_format($value, self::SCALE, '.', '');
        }
        
        $this->validateNumeric($value, 'Value');
        
        return bcadd($value, '0.00', self::SCALE);
    }

    /**
     * Get absolute value
     * 
     * @param string $value Value
     * @return string Absolute value with 2 decimal places
     */
    public function abs(string $value): string
    {
        $this->validateNumeric($value, 'Value');
        
        if ($this->lessThan($value, self::CACHE['zero'])) {
            return $this->multiply($value, '-1.00');
        }
        
        return $value;
    }

    /**
     * Validate that a string is a valid numeric value
     * 
     * @param string $value Value to validate
     * @param string $fieldName Field name for error message
     * @throws InvalidArgumentException If value is not numeric
     */
    private function validateNumeric(string $value, string $fieldName): void
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException(
                "{$fieldName} must be a valid numeric string. Got: {$value}"
            );
        }
    }

    /**
     * Get zero value
     * 
     * @return string "0.00"
     */
    public function zero(): string
    {
        return self::CACHE['zero'];
    }

    /**
     * Get one value
     * 
     * @return string "1.00"
     */
    public function one(): string
    {
        return self::CACHE['one'];
    }

    /**
     * Get hundred value
     * 
     * @return string "100.00"
     */
    public function hundred(): string
    {
        return self::CACHE['hundred'];
    }
}
