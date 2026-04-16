<?php

namespace App\Services;

use App\Models\CreditNote;
use App\Models\CreditNoteLineItem;
use App\Models\CustomerInvoice;
use App\Models\SupplierInvoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditNoteService
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    /**
     * Create credit note for customer invoice
     */
    public function createForCustomerInvoice(
        CustomerInvoice $invoice,
        array $data,
        User $user
    ): CreditNote {
        return DB::transaction(function () use ($invoice, $data, $user) {
            $creditNote = CreditNote::create([
                'cn_number' => CreditNote::generateCnNumber(),
                'organization_id' => $invoice->organization_id,
                'customer_invoice_id' => $invoice->id,
                'type' => $data['type'],
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Create line items
            foreach ($data['items'] as $item) {
                $lineItem = CreditNoteLineItem::create([
                    'credit_note_id' => $creditNote->id,
                    'product_id' => $item['product_id'] ?? null,
                    'customer_invoice_line_item_id' => $item['invoice_line_item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'reason' => $item['reason'] ?? null,
                ]);

                $lineItem->calculateAmounts();
                $lineItem->save();
            }

            $creditNote->recalculateTotals();

            $this->auditService->log(
                action: 'credit_note.created',
                entityType: 'credit_note',
                entityId: $creditNote->id,
                metadata: [
                    'cn_number' => $creditNote->cn_number,
                    'customer_invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'type' => $creditNote->type,
                    'amount' => $creditNote->total_amount,
                    'created_by' => $user->id,
                ]
            );

            return $creditNote;
        });
    }

    /**
     * Create credit note for supplier invoice
     */
    public function createForSupplierInvoice(
        SupplierInvoice $invoice,
        array $data,
        User $user
    ): CreditNote {
        return DB::transaction(function () use ($invoice, $data, $user) {
            $creditNote = CreditNote::create([
                'cn_number' => CreditNote::generateCnNumber(),
                'organization_id' => $invoice->organization_id,
                'supplier_invoice_id' => $invoice->id,
                'type' => $data['type'],
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Create line items
            foreach ($data['items'] as $item) {
                $lineItem = CreditNoteLineItem::create([
                    'credit_note_id' => $creditNote->id,
                    'product_id' => $item['product_id'] ?? null,
                    'supplier_invoice_line_item_id' => $item['invoice_line_item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'reason' => $item['reason'] ?? null,
                ]);

                $lineItem->calculateAmounts();
                $lineItem->save();
            }

            $creditNote->recalculateTotals();

            $this->auditService->log(
                action: 'credit_note.created',
                entityType: 'credit_note',
                entityId: $creditNote->id,
                metadata: [
                    'cn_number' => $creditNote->cn_number,
                    'supplier_invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'type' => $creditNote->type,
                    'amount' => $creditNote->total_amount,
                    'created_by' => $user->id,
                ]
            );

            return $creditNote;
        });
    }

    /**
     * Issue credit note
     */
    public function issue(CreditNote $creditNote, User $user): void
    {
        DB::transaction(function () use ($creditNote, $user) {
            $creditNote->issue($user);

            $this->auditService->log(
                action: 'credit_note.issued',
                entityType: 'credit_note',
                entityId: $creditNote->id,
                metadata: [
                    'cn_number' => $creditNote->cn_number,
                    'issued_by' => $user->id,
                    'issued_at' => $creditNote->issued_at,
                ]
            );
        });
    }

    /**
     * Apply credit note to invoice
     */
    public function apply(CreditNote $creditNote, User $user): void
    {
        DB::transaction(function () use ($creditNote, $user) {
            $creditNote->apply();

            $this->auditService->log(
                action: 'credit_note.applied',
                entityType: 'credit_note',
                entityId: $creditNote->id,
                metadata: [
                    'cn_number' => $creditNote->cn_number,
                    'applied_by' => $user->id,
                    'applied_at' => $creditNote->applied_at,
                ]
            );
        });
    }

    /**
     * Cancel credit note
     */
    public function cancel(CreditNote $creditNote, User $user, string $reason): void
    {
        DB::transaction(function () use ($creditNote, $user, $reason) {
            $creditNote->update([
                'status' => CreditNote::STATUS_CANCELLED,
                'notes' => ($creditNote->notes ? $creditNote->notes . "\n\n" : '') . 
                          "Cancelled: {$reason}"
            ]);

            $this->auditService->log(
                action: 'credit_note.cancelled',
                entityType: 'credit_note',
                entityId: $creditNote->id,
                metadata: [
                    'cn_number' => $creditNote->cn_number,
                    'cancelled_by' => $user->id,
                    'reason' => $reason,
                ]
            );
        });
    }
}