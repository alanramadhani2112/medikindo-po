<?php

namespace App\StateMachines;

use App\Models\PurchaseOrder;
use App\Models\CustomerInvoice;
use App\Models\SupplierInvoice;
use App\Models\PaymentProof;
use InvalidArgumentException;

/**
 * Registry — resolves the correct state machine for a given entity class.
 *
 * Usage:
 *   StateMachineRegistry::for(PurchaseOrder::class)->validate($from, $to, $actor, $entity);
 *   StateMachineRegistry::for($po)->can($from, $to, $actor);
 */
class StateMachineRegistry
{
    private static array $map = [
        PurchaseOrder::class  => PurchaseOrderStateMachine::class,
        CustomerInvoice::class => CustomerInvoiceStateMachine::class,
        SupplierInvoice::class => SupplierInvoiceStateMachine::class,
        PaymentProof::class   => PaymentProofStateMachine::class,
    ];

    /**
     * Resolve state machine for a class name or model instance.
     */
    public static function for(string|object $entity): AbstractStateMachine
    {
        $class = is_object($entity) ? get_class($entity) : $entity;

        if (! isset(self::$map[$class])) {
            throw new InvalidArgumentException(
                "No state machine registered for [{$class}]. " .
                "Register it in " . self::class . "::\$map"
            );
        }

        return new (self::$map[$class])();
    }

    /**
     * Register a custom state machine (useful for testing or extensions).
     */
    public static function register(string $entityClass, string $machineClass): void
    {
        self::$map[$entityClass] = $machineClass;
    }
}
