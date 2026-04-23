<?php

namespace App\StateMachines;

use App\Exceptions\InvalidStateTransitionException;
use App\Models\User;

/**
 * Abstract Finite State Machine
 *
 * Every entity state machine must extend this class and define:
 *  - transitions(): the full transition table
 *
 * Each transition entry is:
 * [
 *   'to'         => string,           // target state
 *   'trigger'    => string,           // action name (e.g. 'issue_invoice')
 *   'roles'      => string[],         // roles allowed to trigger (empty = system only)
 *   'guard'      => callable|null,    // fn(entity, actor, context): bool — hard rule
 *   'guard_msg'  => string,           // message when guard fails
 *   'reversible' => bool,             // can this transition be undone?
 * ]
 */
abstract class AbstractStateMachine
{
    /**
     * Return the full transition table.
     * Format: [ 'from_state' => [ [...transition], [...transition] ] ]
     */
    abstract protected function transitions(): array;

    /**
     * Check if a transition is allowed without throwing.
     */
    public function can(
        string $from,
        string $to,
        ?User $actor = null,
        mixed $entity = null,
        array $context = [],
    ): bool {
        try {
            $this->validate($from, $to, $actor, $entity, $context);
            return true;
        } catch (InvalidStateTransitionException) {
            return false;
        }
    }

    /**
     * Validate a transition — throws InvalidStateTransitionException if not allowed.
     */
    public function validate(
        string $from,
        string $to,
        ?User $actor = null,
        mixed $entity = null,
        array $context = [],
    ): void {
        $table = $this->transitions();

        // 1. Does the from-state exist?
        if (! isset($table[$from])) {
            throw new InvalidStateTransitionException(
                "State [{$from}] is not defined in " . static::class
            );
        }

        // 2. Find the matching transition
        $transition = collect($table[$from])->first(fn($t) => $t['to'] === $to);

        if (! $transition) {
            throw new InvalidStateTransitionException(
                "Transition [{$from}] → [{$to}] is not allowed. " .
                "Valid transitions from [{$from}]: " .
                implode(', ', array_column($table[$from], 'to'))
            );
        }

        // 3. Role check
        $allowedRoles = $transition['roles'] ?? [];
        if (! empty($allowedRoles) && $actor !== null) {
            if (! $actor->hasAnyRole($allowedRoles)) {
                throw new InvalidStateTransitionException(
                    "Transition [{$from}] → [{$to}] requires one of roles: [" .
                    implode(', ', $allowedRoles) . "]. " .
                    "Actor [{$actor->name}] has: [" .
                    implode(', ', $actor->getRoleNames()->toArray()) . "]"
                );
            }
        }

        // 4. Guard check (hard business rule)
        if (isset($transition['guard']) && is_callable($transition['guard'])) {
            $passed = ($transition['guard'])($entity, $actor, $context);
            if (! $passed) {
                $msg = $transition['guard_msg'] ?? "Guard failed for transition [{$from}] → [{$to}]";
                throw new InvalidStateTransitionException($msg);
            }
        }
    }

    /**
     * Get all valid next states from a given state.
     */
    public function validNextStates(string $from): array
    {
        return array_column($this->transitions()[$from] ?? [], 'to');
    }

    /**
     * Get the trigger name for a specific transition.
     */
    public function getTrigger(string $from, string $to): ?string
    {
        $transition = collect($this->transitions()[$from] ?? [])
            ->first(fn($t) => $t['to'] === $to);

        return $transition['trigger'] ?? null;
    }

    /**
     * Check if a state is terminal (no outgoing transitions).
     */
    public function isTerminal(string $state): bool
    {
        return empty($this->transitions()[$state] ?? []);
    }
}
