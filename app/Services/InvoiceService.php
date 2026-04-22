<?php

namespace App\Services;

use App\Models\CustomerInvoice;
use App\Models\CustomerInvoiceLineItem;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\SupplierInvoice;
use App\Models\SupplierInvoiceLineItem;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        private readonly AuditService                $auditService,
        private readonly InvoiceCalculationService   $calculationService,
        private readonly DiscrepancyDetectionService $discrepancyDetector,
        private readonly DocumentNumberService       $documentNumberService,
    ) {}

    // -----------------------------------------------------------------------
    // Issue Invoice (Finance / Super Admin only)
    // Gate: PO must be 'completed'
    // Gate: actor must have 'manage_invoice' permission
    // -----------------------------------------------------------------------

    public function issueInvoice(PurchaseOrder $po, GoodsReceipt $gr, User $actor, string $dueDate): array
    {
        // Permission gate
        if (! $actor->can('manage_invoice')) {
            throw new DomainException('Only Finance staff or Super Admin can issue invoices.');
        }

        // State gate — PO must be completed (goods fully received)
        if (! $po->isCompleted()) {
            throw new DomainException(
                "Invoice can only be issued after PO is completed. Current status: [{$po->status}]."
            );
        }

        return DB::transaction(function () use ($po, $gr, $actor, $dueDate) {
            // Idempotency + concurrency safety:
            // for the same PO+GR pair, return existing invoices instead of creating duplicates.
            $supplierInvoice = SupplierInvoice::where('purchase_order_id', $po->id)
                ->where('goods_receipt_id', $gr->id)
                ->lockForUpdate()
                ->first();

            $customerInvoice = CustomerInvoice::where('purchase_order_id', $po->id)
                ->where('goods_receipt_id', $gr->id)
                ->lockForUpdate()
                ->first();

            // If invoices already exist, return them
            if ($supplierInvoice && $customerInvoice) {
                return ['supplier_invoice' => $supplierInvoice, 'customer_invoice' => $customerInvoice];
            }

            // Get organization's default tax rate and discount percentage
            $organization = $po->organization;
            $defaultTaxRate = $organization->default_tax_rate ?? '0.00';
            $defaultDiscountPercentage = $organization->default_discount_percentage ?? '0.00';

            // Prepare line items data from goods receipt
            // SUPPLIER INVOICE: Use cost_price (from PO)
            // CUSTOMER INVOICE: Use selling_price (from Product)
            $supplierLineItemsData = [];
            $customerLineItemsData = [];
            
            foreach ($gr->items as $grItem) {
                $poItem = $grItem->purchaseOrderItem;
                $product = $poItem->product;
                
                // Supplier Invoice (AP) - Use cost_price from PO
                $supplierLineItemsData[] = [
                    'product_id' => $poItem->product_id,
                    'product_name' => $product->name ?? 'Unknown Product',
                    'quantity' => (string) $grItem->quantity_received,
                    'unit_price' => (string) $poItem->unit_price, // Cost price from PO
                    'discount_percentage' => $defaultDiscountPercentage,
                    'tax_rate' => $defaultTaxRate,
                ];
                
                // Customer Invoice (AR) - Use selling_price from Product
                $customerLineItemsData[] = [
                    'product_id' => $poItem->product_id,
                    'product_name' => $product->name ?? 'Unknown Product',
                    'quantity' => (string) $grItem->quantity_received,
                    'unit_price' => (string) ($product->selling_price ?? $product->price), // Selling price from Product
                    'discount_percentage' => $defaultDiscountPercentage,
                    'tax_rate' => $defaultTaxRate,
                ];
            }

            // Calculate invoices separately (different prices)
            $supplierInvoiceCalculation = $this->calculationService->calculateCompleteInvoice($supplierLineItemsData);
            $customerInvoiceCalculation = $this->calculationService->calculateCompleteInvoice($customerLineItemsData);

            // Run discrepancy detection for supplier invoice
            $discrepancyResult = $this->discrepancyDetector->detect(
                $supplierInvoiceCalculation['invoice_totals']['total_amount'],
                $po
            );

            // Determine initial status based on discrepancy
            $initialStatus = $discrepancyResult['discrepancy_detected'] 
                ? 'pending_approval' 
                : \App\Enums\SupplierInvoiceStatus::DRAFT;

            // --- Supplier Invoice (AP) ---
            if (! $supplierInvoice) {
                $supplierInvoice = SupplierInvoice::create([
                    'invoice_number'    => 'SI-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                    'organization_id'   => $po->organization_id,
                    'supplier_id'       => $po->supplier_id,
                    'purchase_order_id' => $po->id,
                    'goods_receipt_id'  => $gr->id,
                    'total_amount'      => $supplierInvoiceCalculation['invoice_totals']['total_amount'],
                    'subtotal_amount'   => $supplierInvoiceCalculation['invoice_totals']['subtotal_amount'],
                    'discount_amount'   => $supplierInvoiceCalculation['invoice_totals']['discount_amount'],
                    'tax_amount'        => $supplierInvoiceCalculation['invoice_totals']['tax_amount'],
                    'paid_amount'       => '0.00',
                    'status'            => \App\Enums\SupplierInvoiceStatus::DRAFT,
                    'issued_by'         => $actor->id,
                    'issued_at'         => now(),
                    'due_date'          => $dueDate,
                    'version'           => 0,
                    // Discrepancy tracking
                    'discrepancy_detected' => $discrepancyResult['discrepancy_detected'],
                    'expected_total'    => $discrepancyResult['expected_total'],
                    'variance_amount'   => $discrepancyResult['variance_amount'],
                    'variance_percentage' => $discrepancyResult['variance_percentage'],
                ]);

                // Create supplier invoice line items
                foreach ($supplierInvoiceCalculation['line_items'] as $lineItem) {
                    SupplierInvoiceLineItem::create([
                        'supplier_invoice_id' => $supplierInvoice->id,
                        'product_id'          => $lineItem['product_id'],
                        'product_name'        => $lineItem['product_name'],
                        'quantity'            => $lineItem['quantity'],
                        'unit_price'          => $lineItem['unit_price'],
                        'discount_percentage' => $lineItem['discount_percentage'] ?? '0.00',
                        'discount_amount'     => $lineItem['discount_amount'],
                        'tax_rate'            => $lineItem['tax_rate'] ?? '0.00',
                        'tax_amount'          => $lineItem['tax_amount'],
                        'line_total'          => $lineItem['line_total'],
                    ]);
                }
            }

            // --- Customer Invoice (AR) ---
            if (! $customerInvoice) {
                $customerInvoice = CustomerInvoice::create([
                    'invoice_number'    => $this->documentNumberService->generateInvoiceNumber($po->organization_id),
                    'organization_id'   => $po->organization_id,
                    'purchase_order_id' => $po->id,
                    'goods_receipt_id'  => $gr->id,
                    'supplier_invoice_id' => $supplierInvoice->id, // Fix: link anti-phantom
                    'total_amount'      => $customerInvoiceCalculation['invoice_totals']['total_amount'],
                    'subtotal_amount'   => $customerInvoiceCalculation['invoice_totals']['subtotal_amount'],
                    'discount_amount'   => $customerInvoiceCalculation['invoice_totals']['discount_amount'],
                    'tax_amount'        => $customerInvoiceCalculation['invoice_totals']['tax_amount'],
                    'paid_amount'       => '0.00',
                    'status'            => \App\Enums\CustomerInvoiceStatus::ISSUED,
                    'issued_by'         => $actor->id,
                    'issued_at'         => now(),
                    'due_date'          => $dueDate,
                    'version'           => 0,
                    // Discrepancy tracking
                    'discrepancy_detected' => $discrepancyResult['discrepancy_detected'],
                    'expected_total'    => $discrepancyResult['expected_total'],
                    'variance_amount'   => $discrepancyResult['variance_amount'],
                    'variance_percentage' => $discrepancyResult['variance_percentage'],
                ]);

                // Create customer invoice line items
                foreach ($customerInvoiceCalculation['line_items'] as $lineItem) {
                    CustomerInvoiceLineItem::create([
                        'customer_invoice_id' => $customerInvoice->id,
                        'product_id'          => $lineItem['product_id'],
                        'product_name'        => $lineItem['product_name'],
                        'quantity'            => $lineItem['quantity'],
                        'unit_price'          => $lineItem['unit_price'],
                        'discount_percentage' => $lineItem['discount_percentage'] ?? '0.00',
                        'discount_amount'     => $lineItem['discount_amount'],
                        'tax_rate'            => $lineItem['tax_rate'] ?? '0.00',
                        'tax_amount'          => $lineItem['tax_amount'],
                        'line_total'          => $lineItem['line_total'],
                    ]);
                }
            }

            if ($supplierInvoice->wasRecentlyCreated || $customerInvoice->wasRecentlyCreated) {
                $this->auditService->log(
                    action:     'invoice.issued',
                    entityType: CustomerInvoice::class,
                    entityId:   $customerInvoice->id,
                    metadata:   [
                        'po_number'              => $po->po_number,
                        'before_status'          => null,
                        'after_status'           => $initialStatus,
                        'supplier_total_amount'  => $supplierInvoiceCalculation['invoice_totals']['total_amount'],
                        'customer_total_amount'  => $customerInvoiceCalculation['invoice_totals']['total_amount'],
                        'profit_amount'          => bcsub(
                            (string) $customerInvoiceCalculation['invoice_totals']['total_amount'],
                            (string) $supplierInvoiceCalculation['invoice_totals']['total_amount'],
                            2
                        ),
                        'line_items_count'       => count($customerInvoiceCalculation['line_items']),
                        'supplier_invoice_id'    => $supplierInvoice->id,
                        'discrepancy_detected'   => $discrepancyResult['discrepancy_detected'],
                        'variance_amount'        => $discrepancyResult['variance_amount'],
                        'variance_percentage'    => $discrepancyResult['variance_percentage'],
                    ],
                    userId: $actor->id,
                );

                // Notify Finance + Healthcare User only when a new invoice is created.
                // If discrepancy detected, notify about approval requirement
                $notificationMessage = $discrepancyResult['discrepancy_detected']
                    ? 'Invoice requires approval due to discrepancy'
                    : 'New invoice issued';

                User::role(['Super Admin', 'Healthcare User'])->get()
                    ->filter(fn($u) => $u->hasRole('Super Admin') || $u->organization_id === $po->organization_id)
                    ->each(fn($u) => $u->notify(new \App\Notifications\NewInvoiceNotification($customerInvoice)));
            }

            return [
                'supplier_invoice' => $supplierInvoice,
                'customer_invoice' => $customerInvoice,
                'discrepancy_result' => $discrepancyResult,
            ];
        });
    }

    // -----------------------------------------------------------------------
    // Approve Discrepancy (Finance / Super Admin only)
    // Gate: invoice status must be 'pending_approval'
    // Gate: actor must have 'approve_invoice_discrepancy' permission
    // Transition: pending_approval → issued
    // -----------------------------------------------------------------------

    public function approveDiscrepancy(
        CustomerInvoice $invoice,
        User $actor,
        string $approvalReason
    ): CustomerInvoice {
        // Permission gate
        if (! $actor->can('approve_invoice_discrepancy')) {
            throw new DomainException('Only Finance staff or Super Admin can approve invoice discrepancies.');
        }

        // Status gate - safely extract enum value for comparison and string interpolation
        $statusValue = $invoice->status instanceof \BackedEnum ? $invoice->status->value : $invoice->status;
        if ($statusValue !== 'pending_approval') {
            throw new DomainException(
                "Invoice must be in 'pending_approval' status. Current status: [{$statusValue}]."
            );
        }

        // Discrepancy gate
        if (! $invoice->discrepancy_detected) {
            throw new DomainException('Invoice does not have a discrepancy to approve.');
        }

        return DB::transaction(function () use ($invoice, $actor, $approvalReason) {
            $fresh = CustomerInvoice::lockForUpdate()->findOrFail($invoice->id);

            $before = $fresh->status;

            $fresh->update([
                'status' => \App\Enums\CustomerInvoiceStatus::ISSUED,
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'approval_reason' => $approvalReason,
            ]);

            // Also update corresponding supplier invoice
            $supplierInvoice = SupplierInvoice::where('purchase_order_id', $fresh->purchase_order_id)
                ->where('goods_receipt_id', $fresh->goods_receipt_id)
                ->first();

            if ($supplierInvoice) {
                $supplierInvoice->update([
                    'status' => \App\Enums\SupplierInvoiceStatus::VERIFIED,
                    'approved_by' => $actor->id,
                    'approved_at' => now(),
                    'approval_reason' => $approvalReason,
                ]);
            }

            $this->auditService->log(
                action:     'invoice.discrepancy_approved',
                entityType: CustomerInvoice::class,
                entityId:   $fresh->id,
                metadata:   [
                    'before_status'       => $before,
                    'after_status'        => CustomerInvoice::STATUS_ISSUED,
                    'approved_by'         => $actor->id,
                    'approval_reason'     => $approvalReason,
                    'variance_amount'     => $fresh->variance_amount,
                    'variance_percentage' => $fresh->variance_percentage,
                    'expected_total'      => $fresh->expected_total,
                    'actual_total'        => $fresh->total_amount,
                ],
                userId: $actor->id,
            );

            // Notify Healthcare User that invoice has been approved
            $po = $fresh->purchaseOrder;
            if ($po) {
                User::role(['Healthcare User'])->get()
                    ->filter(fn($u) => $u->organization_id === $fresh->organization_id)
                    ->each(fn($u) => $u->notify(new \App\Notifications\NewInvoiceNotification($fresh)));
            }

            return $fresh->fresh();
        });
    }

    // -----------------------------------------------------------------------
    // Reject Discrepancy (Finance / Super Admin only)
    // Gate: invoice status must be 'pending_approval'
    // Gate: actor must have 'approve_invoice_discrepancy' permission
    // Transition: pending_approval → rejected
    // -----------------------------------------------------------------------

    public function rejectDiscrepancy(
        CustomerInvoice $invoice,
        User $actor,
        string $rejectionReason
    ): CustomerInvoice {
        // Permission gate
        if (! $actor->can('approve_invoice_discrepancy')) {
            throw new DomainException('Only Finance staff or Super Admin can reject invoice discrepancies.');
        }

        // Status gate - safely extract enum value for comparison and string interpolation
        $statusValue = $invoice->status instanceof \BackedEnum ? $invoice->status->value : $invoice->status;
        if ($statusValue !== 'pending_approval') {
            throw new DomainException(
                "Invoice must be in 'pending_approval' status. Current status: [{$statusValue}]."
            );
        }

        // Discrepancy gate
        if (! $invoice->discrepancy_detected) {
            throw new DomainException('Invoice does not have a discrepancy to reject.');
        }

        return DB::transaction(function () use ($invoice, $actor, $rejectionReason) {
            $fresh = CustomerInvoice::lockForUpdate()->findOrFail($invoice->id);

            $before = $fresh->status;

            $fresh->update([
                'status' => 'rejected',
                'rejected_by' => $actor->id,
                'rejected_at' => now(),
                'rejection_reason' => $rejectionReason,
            ]);

            // Also update corresponding supplier invoice
            $supplierInvoice = SupplierInvoice::where('purchase_order_id', $fresh->purchase_order_id)
                ->where('goods_receipt_id', $fresh->goods_receipt_id)
                ->first();

            if ($supplierInvoice) {
                $supplierInvoice->update([
                    'status' => 'rejected',
                    'rejected_by' => $actor->id,
                    'rejected_at' => now(),
                    'rejection_reason' => $rejectionReason,
                ]);
            }

            $this->auditService->log(
                action:     'invoice.discrepancy_rejected',
                entityType: CustomerInvoice::class,
                entityId:   $fresh->id,
                metadata:   [
                    'before_status'       => $before,
                    'after_status'        => 'rejected',
                    'rejected_by'         => $actor->id,
                    'rejection_reason'    => $rejectionReason,
                    'variance_amount'     => $fresh->variance_amount,
                    'variance_percentage' => $fresh->variance_percentage,
                    'expected_total'      => $fresh->expected_total,
                    'actual_total'        => $fresh->total_amount,
                ],
                userId: $actor->id,
            );

            // Notify Healthcare User and Finance that invoice has been rejected
            $po = $fresh->purchaseOrder;
            if ($po) {
                User::role(['Healthcare User', 'Finance', 'Super Admin'])->get()
                    ->filter(fn($u) => $u->hasRole('Super Admin') || $u->organization_id === $fresh->organization_id)
                    ->each(fn($u) => $u->notify(new \App\Notifications\NewInvoiceNotification($fresh)));
            }

            return $fresh->fresh();
        });
    }

    // -----------------------------------------------------------------------
    // Confirm Payment (Healthcare User only)
    // Gate: invoice status must be 'issued' or 'overdue'
    // Gate: actor must have 'confirm_payment' permission
    // Prevent: duplicate submissions using row lock
    // Transition: issued → payment_submitted
    // -----------------------------------------------------------------------

    public function confirmPayment(CustomerInvoice $invoice, User $actor, array $data): CustomerInvoice
    {
        // Permission gate
        if (! $actor->can('confirm_payment')) {
            throw new DomainException('Only Healthcare User (Clinic Admin) can confirm payment.');
        }

        // Multi-tenant check — organization can only pay their own invoice
        if ($invoice->organization_id !== $actor->organization_id && ! $actor->hasRole('Super Admin')) {
            throw new DomainException('You do not have access to this invoice.');
        }

        return DB::transaction(function () use ($invoice, $actor, $data) {
            // Row lock to prevent concurrent submission
            $fresh = CustomerInvoice::lockForUpdate()->findOrFail($invoice->id);

            if (! $fresh->canConfirmPayment()) {
                throw new DomainException(
                    "Payment cannot be submitted. Invoice status: [{$fresh->status}]."
                );
            }

            $before = $fresh->status;

            $fresh->update([
                'status'                => \App\Enums\CustomerInvoiceStatus::ISSUED, // Transition to Issued if it was draft, but usually it is already Issued
                'payment_reference'     => $data['payment_reference'] ?? null,
                'payment_submitted_at'  => now(),
                'paid_amount'           => $data['paid_amount'] ?? $fresh->total_amount,
            ]);

            $this->auditService->log(
                action:     'invoice.payment_submitted',
                entityType: CustomerInvoice::class,
                entityId:   $fresh->id,
                metadata:   [
                    'before_status'     => $before,
                    'after_status'      => 'payment_submitted', // Keep as string for audit metadata if needed, or use enum value
                    'payment_reference' => $fresh->payment_reference,
                ],
                userId: $actor->id,
            );

            // Notify Finance to verify
            User::role(['Finance', 'Super Admin'])->get()
                ->each(fn($u) => $u->notify(new \App\Notifications\NewInvoiceNotification($fresh)));

            return $fresh->fresh();
        });
    }

    // -----------------------------------------------------------------------
    // Verify Payment (Finance only)
    // Gate: invoice status must be 'payment_submitted'
    // Gate: actor must have 'verify_payment' permission
    // Uses: DB row lock to prevent race conditions
    // Transition: payment_submitted → paid
    // -----------------------------------------------------------------------

    public function verifyPayment(CustomerInvoice $invoice, User $actor): CustomerInvoice
    {
        // Permission gate
        if (! $actor->can('verify_payment')) {
            throw new DomainException('Only Finance staff or Super Admin can verify payments.');
        }

        return DB::transaction(function () use ($invoice, $actor) {
            // Acquire row-level lock — prevents concurrent verification of the same invoice
            $fresh = CustomerInvoice::lockForUpdate()->findOrFail($invoice->id);

            if (! $fresh->canBeVerified()) {
                throw new DomainException(
                    "Cannot verify payment. Invoice status: [{$fresh->status}]. Expected: payment_submitted."
                );
            }

            $before = $fresh->status;

            $fresh->update([
                'status'      => \App\Enums\CustomerInvoiceStatus::PAID,
                'verified_by' => $actor->id,
                'verified_at' => now(),
            ]);

            $this->auditService->log(
                action:     'invoice.paid',
                entityType: CustomerInvoice::class,
                entityId:   $fresh->id,
                metadata:   [
                    'before_status' => $before,
                    'after_status'  => 'paid',
                    'verified_by'   => $actor->id,
                ],
                userId: $actor->id,
            );

            // Notify Healthcare User that payment has been verified
            $po = $fresh->purchaseOrder;
            if ($po) {
                User::role(['Healthcare User'])->get()
                    ->filter(fn($u) => $u->organization_id === $fresh->organization_id)
                    ->each(fn($u) => $u->notify(new \App\Notifications\NewInvoiceNotification($fresh)));
            }

            return $fresh->fresh();
        });
    }
}
