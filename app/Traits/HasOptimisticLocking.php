<?php

namespace App\Traits;

use App\Exceptions\ConcurrencyException;
use App\Services\AuditService;

/**
 * Has Optimistic Locking Trait
 * 
 * Implements optimistic locking using version column to detect concurrent modifications.
 * Prevents lost updates when multiple users edit the same record simultaneously.
 * 
 * Requirements:
 * - Model must have 'version' column (integer, default 0)
 * 
 * @package App\Traits
 */
trait HasOptimisticLocking
{
    /**
     * Original version when model was loaded
     * 
     * @var int|null
     */
    private ?int $originalVersion = null;

    /**
     * Boot the trait
     */
    protected static function bootHasOptimisticLocking(): void
    {
        // Store original version when model is retrieved
        static::retrieved(function ($model) {
            $model->originalVersion = $model->version ?? 0;
        });
    }

    /**
     * Save the model with optimistic locking
     * 
     * @param array $options Save options
     * @return bool True if saved successfully
     * @throws ConcurrencyException If concurrent modification detected
     */
    public function save(array $options = []): bool
    {
        // For new records, just save normally
        if (!$this->exists) {
            $this->version = 0;
            return parent::save($options);
        }

        // Skip optimistic locking if no changes
        if (!$this->isDirty()) {
            return true;
        }

        // Get the original version
        $expectedVersion = $this->originalVersion ?? $this->getOriginal('version') ?? 0;
        
        // Increment version
        $newVersion = $expectedVersion + 1;

        // Set version on model FIRST
        $this->setAttribute('version', $newVersion);
        
        // Now get dirty attributes (will include version)
        $dirtyAttributes = $this->getDirty();

        // Perform the update with version check
        $affected = $this->newQuery()
            ->where($this->getKeyName(), $this->getKey())
            ->where('version', $expectedVersion)
            ->update($dirtyAttributes);

        // If no rows were affected, concurrent modification occurred
        if ($affected === 0) {
            $this->handleConcurrencyConflict($expectedVersion);
        }
        
        // Sync the model with database - mark all attributes as clean
        $this->syncOriginal();
        
        // Update original version to new version
        $this->originalVersion = $newVersion;

        // Fire model events
        $this->fireModelEvent('saved', false);
        $this->fireModelEvent('updated', false);

        return true;
    }

    /**
     * Handle concurrency conflict
     * 
     * @param int $expectedVersion Expected version number
     * @throws ConcurrencyException
     */
    private function handleConcurrencyConflict(int $expectedVersion): void
    {
        // Log the conflict
        $this->logConcurrencyConflict($expectedVersion);

        // Get entity type
        $entityType = strtolower(class_basename($this));

        // Throw exception
        throw new ConcurrencyException(
            message: "Concurrent modification detected for {$entityType} ID {$this->getKey()}. " .
                     "Expected version {$expectedVersion}, but record has been modified by another user. " .
                     "Please reload and try again.",
            entityType: $entityType,
            entityId: $this->getKey(),
            expectedVersion: $expectedVersion
        );
    }

    /**
     * Log concurrency conflict to audit trail
     * 
     * @param int $expectedVersion Expected version
     */
    private function logConcurrencyConflict(int $expectedVersion): void
    {
        try {
            $auditService = app(AuditService::class);
            
            $userId = null;
            if (function_exists('auth') && auth()->check()) {
                $userId = auth()->id();
            }

            $entityType = strtolower(class_basename($this));

            $auditService->log(
                action: "{$entityType}.concurrency_conflict",
                entityType: $entityType,
                entityId: $this->getKey(),
                metadata: [
                    'operation' => 'optimistic_locking_failure',
                    'expected_version' => $expectedVersion,
                    'attempted_changes' => $this->getDirty(),
                    'current_attributes' => $this->getAttributes(),
                ],
                userId: $userId
            );
        } catch (\Throwable $e) {
            // Silently fail if audit logging is not available
        }
    }

    /**
     * Get the current version
     * 
     * @return int Current version
     */
    public function getVersion(): int
    {
        return $this->version ?? 0;
    }

    /**
     * Get the original version when model was loaded
     * 
     * @return int Original version
     */
    public function getOriginalVersion(): int
    {
        if ($this->originalVersion !== null) {
            return $this->originalVersion;
        }
        
        // If originalVersion is not set, use the original attribute
        $original = $this->getOriginal('version');
        return $original !== null ? (int) $original : 0;
    }
}
