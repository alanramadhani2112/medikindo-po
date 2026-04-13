<?php

namespace App\Exceptions;

use Exception;

/**
 * Concurrency Exception
 * 
 * Thrown when a concurrent modification is detected (optimistic locking failure).
 * Indicates that another user has modified the record since it was loaded.
 * 
 * @package App\Exceptions
 */
class ConcurrencyException extends Exception
{
    /**
     * Entity type that was being modified
     * 
     * @var string
     */
    private string $entityType;

    /**
     * Entity ID that was being modified
     * 
     * @var int
     */
    private int $entityId;

    /**
     * Expected version number
     * 
     * @var int
     */
    private int $expectedVersion;

    /**
     * Create a new exception instance
     * 
     * @param string $message Exception message
     * @param string $entityType Entity type (e.g., 'supplier_invoice')
     * @param int $entityId Entity ID
     * @param int $expectedVersion Expected version number
     * @param int $code Exception code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message = 'Concurrent modification detected',
        string $entityType = '',
        int $entityId = 0,
        int $expectedVersion = 0,
        int $code = 409,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->expectedVersion = $expectedVersion;
    }

    /**
     * Get entity type
     * 
     * @return string Entity type
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * Get entity ID
     * 
     * @return int Entity ID
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * Get expected version
     * 
     * @return int Expected version
     */
    public function getExpectedVersion(): int
    {
        return $this->expectedVersion;
    }

    /**
     * Convert exception to array for API responses
     * 
     * @return array Exception data
     */
    public function toArray(): array
    {
        return [
            'error' => 'concurrency_conflict',
            'message' => $this->getMessage(),
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'expected_version' => $this->expectedVersion,
            'suggestion' => 'Please reload the record and try again.',
        ];
    }
}
