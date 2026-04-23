<?php

namespace App\Services;

use App\Models\SupplierInvoice;
use App\Models\CustomerInvoice;
use App\Enums\SupplierInvoiceStatus;
use App\Enums\CustomerInvoiceStatus;
use App\StateMachines\StateMachineRegistry;

/**
 * StateMachineService — thin wrapper kept for backward compatibility.
 * All logic now delegates to StateMachineRegistry + formal state machines.
 *
 * @deprecated Use StateMachineRegistry::for($entity)->validate(...) directly.
 */
class StateMachineService
{
    public function transitionSupplierInvoice(
        SupplierInvoice $invoice,
        SupplierInvoiceStatus $targetStatus,
        array $context = []
    ): bool {
        StateMachineRegistry::for(SupplierInvoice::class)->validate(
            from:    $invoice->status->value,
            to:      $targetStatus->value,
            entity:  $invoice,
            context: $context,
        );

        $invoice->status = $targetStatus;
        $invoice->save();

        return true;
    }

    public function transitionCustomerInvoice(
        CustomerInvoice $invoice,
        CustomerInvoiceStatus $targetStatus,
        array $context = []
    ): bool {
        StateMachineRegistry::for(CustomerInvoice::class)->validate(
            from:    $invoice->status->value,
            to:      $targetStatus->value,
            entity:  $invoice,
            context: $context,
        );

        $invoice->status = $targetStatus;
        $invoice->save();

        return true;
    }

    public function getValidSupplierInvoiceTransitions(SupplierInvoice $invoice): array
    {
        return StateMachineRegistry::for(SupplierInvoice::class)
            ->validNextStates($invoice->status->value);
    }

    public function getValidCustomerInvoiceTransitions(CustomerInvoice $invoice): array
    {
        return StateMachineRegistry::for(CustomerInvoice::class)
            ->validNextStates($invoice->status->value);
    }

    public function canTransition($invoice, $targetStatus): bool
    {
        $statusValue = $targetStatus instanceof \BackedEnum ? $targetStatus->value : $targetStatus;
        return StateMachineRegistry::for($invoice)->can(
            from:   $invoice->status->value,
            to:     $statusValue,
            entity: $invoice,
        );
    }

    public function getStateTransitionMap(string $invoiceType): array
    {
        $class = $invoiceType === 'supplier' ? SupplierInvoice::class : CustomerInvoice::class;
        $machine = StateMachineRegistry::for($class);
        $map = [];
        $states = $invoiceType === 'supplier'
            ? SupplierInvoiceStatus::cases()
            : CustomerInvoiceStatus::cases();

        foreach ($states as $status) {
            $map[$status->value] = $machine->validNextStates($status->value);
        }

        return $map;
    }
}
