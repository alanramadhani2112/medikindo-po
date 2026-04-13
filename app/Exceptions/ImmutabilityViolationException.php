<?php

namespace App\Exceptions;

use Exception;

/**
 * Immutability Violation Exception
 * 
 * Thrown when an attempt is made to modify immutable invoice data.
 * 
 * @package App\Exceptions
 */
class ImmutabilityViolationException extends Exception
{
    /**
     * Violations details
     * 
     * @var array
     */
    private array $violations;

    /**
     * Create a new exception instance
     * 
     * @param string $message Exception message
     * @param array $violations Violations details
     * @param int $code Exception code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message = 'Attempted to modify immutable invoice data',
        array $violations = [],
        int $code = 403,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->violations = $violations;
    }

    /**
     * Get violations details
     * 
     * @return array Violations
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    /**
     * Get violation field names
     * 
     * @return array Field names
     */
    public function getViolatedFields(): array
    {
        return array_keys($this->violations);
    }

    /**
     * Check if a specific field was violated
     * 
     * @param string $fieldName Field name
     * @return bool True if field was violated
     */
    public function hasViolation(string $fieldName): bool
    {
        return isset($this->violations[$fieldName]);
    }

    /**
     * Convert exception to array for API responses
     * 
     * @return array Exception data
     */
    public function toArray(): array
    {
        return [
            'error' => 'immutability_violation',
            'message' => $this->getMessage(),
            'violations' => $this->violations,
            'violated_fields' => $this->getViolatedFields(),
        ];
    }
}
