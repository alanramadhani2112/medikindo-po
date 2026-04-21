<?php

namespace App\Services;

use App\Models\SupplierInvoice;
use App\Models\CustomerInvoice;
use App\Enums\SupplierInvoiceStatus;
use App\Enums\CustomerInvoiceStatus;
use App\Exceptions\InvalidStateTransitionException;
use Illuminate\Support\Facades\Log;

class StateMachineService
{
    /**
     * Validate and execute state transition for Supplier Invoice
     *
     * @param SupplierInvoice $invoice
     * @param SupplierInvoiceStatus $targetStatus
     * @param array $context Additional context for logging
     * @return bool
     * @throws InvalidStateTransitionException
     */
    public function transitionSupplierInvoice(
        SupplierInvoice $invoice,
        SupplierInvoiceStatus $targetStatus,
        array $context = []
    ): bool {
        $currentStatus = $invoice->status;

        if (!$invoice->canTransitionTo($targetStatus)) {
            throw new InvalidStateTransitionException(
                "Tidak dapat mengubah status Supplier Invoice dari '{$currentStatus->getLabel()}' ke '{$targetStatus->getLabel()}'"
            );
        }

        $invoice->status = $targetStatus;
        $invoice->save();

        Log::info('Supplier Invoice state transition', array_merge([
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'from_status' => $currentStatus->value,
            'to_status' => $targetStatus->value,
        ], $context));

        return true;
    }

    /**
     * Validate and execute state transition for Customer Invoice
     *
     * @param CustomerInvoice $invoice
     * @param CustomerInvoiceStatus $targetStatus
     * @param array $context Additional context for logging
     * @return bool
     * @throws InvalidStateTransitionException
     */
    public function transitionCustomerInvoice(
        CustomerInvoice $invoice,
        CustomerInvoiceStatus $targetStatus,
        array $context = []
    ): bool {
        $currentStatus = $invoice->status;

        if (!$invoice->canTransitionTo($targetStatus)) {
            throw new InvalidStateTransitionException(
                "Tidak dapat mengubah status Customer Invoice dari '{$currentStatus->getLabel()}' ke '{$targetStatus->getLabel()}'"
            );
        }

        $invoice->status = $targetStatus;
        $invoice->save();

        Log::info('Customer Invoice state transition', array_merge([
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'from_status' => $currentStatus->value,
            'to_status' => $targetStatus->value,
        ], $context));

        return true;
    }

    /**
     * Get valid next states for Supplier Invoice
     *
     * @param SupplierInvoice $invoice
     * @return array
     */
    public function getValidSupplierInvoiceTransitions(SupplierInvoice $invoice): array
    {
        return $invoice->status->getValidTransitions();
    }

    /**
     * Get valid next states for Customer Invoice
     *
     * @param CustomerInvoice $invoice
     * @return array
     */
    public function getValidCustomerInvoiceTransitions(CustomerInvoice $invoice): array
    {
        $currentStatus = $invoice->status;
        $validTransitions = [];

        foreach (CustomerInvoiceStatus::cases() as $status) {
            if ($currentStatus->canTransitionTo($status)) {
                $validTransitions[] = $status;
            }
        }

        return $validTransitions;
    }

    /**
     * Check if transition is valid without executing it
     *
     * @param SupplierInvoice|CustomerInvoice $invoice
     * @param SupplierInvoiceStatus|CustomerInvoiceStatus $targetStatus
     * @return bool
     */
    public function canTransition($invoice, $targetStatus): bool
    {
        return $invoice->canTransitionTo($targetStatus);
    }

    /**
     * Get state transition history visualization
     *
     * @param string $invoiceType 'supplier' or 'customer'
     * @return array
     */
    public function getStateTransitionMap(string $invoiceType): array
    {
        if ($invoiceType === 'supplier') {
            return [
                'DRAFT' => ['VERIFIED', 'OVERDUE'],
                'VERIFIED' => ['PAID'],
                'OVERDUE' => ['VERIFIED', 'PAID'],
                'PAID' => [],
            ];
        }

        if ($invoiceType === 'customer') {
            return [
                'DRAFT' => ['ISSUED', 'VOID'],
                'ISSUED' => ['PARTIAL_PAID', 'PAID', 'VOID'],
                'PARTIAL_PAID' => ['PAID', 'VOID'],
                'PAID' => [],
                'VOID' => [],
            ];
        }

        return [];
    }
}
