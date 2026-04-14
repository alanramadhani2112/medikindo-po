<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\SupplierInvoice;
use App\Models\CustomerInvoice;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Service for creating invoices from Goods Receipts
 * 
 * CRITICAL RULES:
 * - Invoice MUST be created from GR (not PO)
 * - Batch & Expiry are READ-ONLY from GR
 * - Quantity MUST NOT exceed remaining GR quantity
 * - GR is the source of truth for goods data
 */
class InvoiceFromGRService
{
    public function __construct(
        private readonly InvoiceCalculationService $calculationService,
        private readonly AuditService $auditService,
    ) {}

    /**
     * Create Supplier Invoice from Goods Receipt
     * 
     * @param GoodsReceipt $gr
     * @param User $actor
     * @param array $items Format: [['goods_receipt_item_id' => int, 'quantity' => int, 'unit_price' => float, 'discount_percentage' => float]]
     * @param array $metadata Additional invoice data (notes, due_date, etc)
     * @return SupplierInvoice
     * @throws DomainException
     */
    public function createSupplierInvoiceFromGR(
        GoodsReceipt $gr,
        User $actor,
        array $items,
        array $metadata = []
    ): SupplierInvoice {
        // Gate: GR must be completed
        if (!$gr->isCompleted()) {
            throw new DomainException(
                "Cannot create invoice from GR with status [{$gr->status}]. GR must be 'completed'."
            );
        }

        // Gate: GR must belong to a supplier
        $po = $gr->purchaseOrder;
        if (!$po || !$po->supplier_id) {
            throw new DomainException("Goods Receipt must have a valid Purchase Order with supplier.");
        }

        // Validate quantities
        $this->validateQuantities($gr, $items);

        // Validate batch & expiry consistency
        $this->validateBatchExpiry($gr, $items);

        return DB::transaction(function () use ($gr, $po, $actor, $items, $metadata) {
            // Prepare line items with GR data
            $lineItemsData = $this->prepareLineItems($gr, $items);

            // Calculate invoice totals using existing pricing engine
            $calculation = $this->calculationService->calculateCompleteInvoice($lineItemsData);

            // Detect discrepancies (GR vs Invoice, PO vs Invoice)
            $discrepancies = $this->detectDiscrepancies($gr, $po, $lineItemsData, $calculation);

            // Create invoice
            $invoice = SupplierInvoice::create([
                'invoice_number'            => $metadata['internal_invoice_number'] ?? $this->generateInvoiceNumber(),
                'distributor_invoice_number' => $metadata['distributor_invoice_number'] ?? null,
                'distributor_invoice_date'  => $metadata['distributor_invoice_date'] ?? null,
                'organization_id'           => $gr->organization_id,
                'supplier_id'               => $po->supplier_id,
                'purchase_order_id'         => $po->id,
                'goods_receipt_id'          => $gr->id,
                'status'                    => SupplierInvoice::STATUS_ISSUED,
                'total_amount'              => $calculation['invoice_totals']['total_amount'],
                'subtotal_amount'           => $calculation['invoice_totals']['subtotal_amount'],
                'discount_amount'           => $calculation['invoice_totals']['discount_amount'],
                'tax_amount'                => $calculation['invoice_totals']['tax_amount'],
                'paid_amount'               => 0,
                'discrepancy_detected'      => $discrepancies['has_discrepancy'],
                'expected_total'            => $discrepancies['expected_total'] ?? null,
                'variance_amount'           => $discrepancies['variance_amount'] ?? null,
                'variance_percentage'       => $discrepancies['variance_percentage'] ?? null,
                'due_date'                  => $metadata['due_date'] ?? now()->addDays(30),
                'notes'                     => $metadata['notes'] ?? null,
                'issued_by'                 => $actor->id,
                'issued_at'                 => now(),
                'version'                   => 1,
            ]);

            // Create line items with GR references
            foreach ($calculation['line_items'] as $index => $lineCalc) {
                $itemData = $lineItemsData[$index];
                $grItem = GoodsReceiptItem::find($itemData['goods_receipt_item_id']);

                $invoice->lineItems()->create([
                    'goods_receipt_item_id' => $grItem->id,
                    'product_id'            => $grItem->purchaseOrderItem->product_id,
                    'product_name'          => $itemData['product_name'],
                    'product_sku'           => $itemData['product_sku'],
                    'batch_no'              => $grItem->batch_no, // READ-ONLY from GR
                    'expiry_date'           => $grItem->expiry_date, // READ-ONLY from GR
                    'quantity'              => $itemData['quantity'],
                    'unit_price'            => $itemData['unit_price'],
                    'discount_percentage'   => $itemData['discount_percentage'] ?? 0,
                    'discount_amount'       => $lineCalc['discount_amount'],
                    'tax_rate'              => $itemData['tax_rate'] ?? 0,
                    'tax_amount'            => $lineCalc['tax_amount'],
                    'line_total'            => $lineCalc['line_total'],
                ]);
            }

            // Audit log
            $this->auditService->log(
                action:     'supplier_invoice.created_from_gr',
                entityType: SupplierInvoice::class,
                entityId:   $invoice->id,
                metadata:   [
                    'invoice_number'      => $invoice->invoice_number,
                    'goods_receipt_id'    => $gr->id,
                    'gr_number'           => $gr->gr_number,
                    'purchase_order_id'   => $po->id,
                    'po_number'           => $po->po_number,
                    'total_amount'        => $invoice->total_amount,
                    'discrepancy_detected' => $discrepancies['has_discrepancy'],
                    'item_count'          => count($items),
                ],
                userId: $actor->id,
            );

            return $invoice->load(['lineItems', 'goodsReceipt', 'purchaseOrder', 'supplier']);
        });
    }

    /**
     * Validate that invoice quantities do not exceed remaining GR quantities
     * 
     * @param GoodsReceipt $gr
     * @param array $items
     * @throws DomainException
     */
    private function validateQuantities(GoodsReceipt $gr, array $items): void
    {
        foreach ($items as $item) {
            $grItem = GoodsReceiptItem::find($item['goods_receipt_item_id']);

            if (!$grItem) {
                throw new DomainException("Goods Receipt Item ID {$item['goods_receipt_item_id']} not found.");
            }

            if ($grItem->goods_receipt_id !== $gr->id) {
                throw new DomainException("Goods Receipt Item ID {$grItem->id} does not belong to GR {$gr->gr_number}.");
            }

            $remainingQty = $grItem->remaining_quantity;
            $requestedQty = $item['quantity'];

            if ($requestedQty > $remainingQty) {
                $productName = $grItem->purchaseOrderItem->product->name ?? 'Unknown';
                throw new DomainException(
                    "Invoice quantity ({$requestedQty}) exceeds remaining quantity ({$remainingQty}) for product [{$productName}]."
                );
            }

            if ($requestedQty <= 0) {
                throw new DomainException("Invoice quantity must be greater than 0.");
            }
        }
    }

    /**
     * Validate batch and expiry consistency (must match GR exactly)
     * 
     * @param GoodsReceipt $gr
     * @param array $items
     * @throws DomainException
     */
    private function validateBatchExpiry(GoodsReceipt $gr, array $items): void
    {
        foreach ($items as $item) {
            $grItem = GoodsReceiptItem::find($item['goods_receipt_item_id']);

            // If user tries to override batch/expiry (should not happen in UI, but validate anyway)
            if (isset($item['batch_no']) && $item['batch_no'] !== $grItem->batch_no) {
                throw new DomainException("Batch number cannot be modified. Must match GR exactly.");
            }

            if (isset($item['expiry_date']) && $item['expiry_date'] !== $grItem->expiry_date?->format('Y-m-d')) {
                throw new DomainException("Expiry date cannot be modified. Must match GR exactly.");
            }
        }
    }

    /**
     * Prepare line items data for calculation
     * 
     * @param GoodsReceipt $gr
     * @param array $items
     * @return array
     */
    private function prepareLineItems(GoodsReceipt $gr, array $items): array
    {
        $lineItems = [];

        foreach ($items as $item) {
            $grItem = GoodsReceiptItem::with('purchaseOrderItem.product')->find($item['goods_receipt_item_id']);
            $poItem = $grItem->purchaseOrderItem;
            $product = $poItem->product;

            $lineItems[] = [
                'goods_receipt_item_id' => $grItem->id,
                'product_id'            => $product->id,
                'product_name'          => $product->name,
                'product_sku'           => $product->sku,
                'quantity'              => $item['quantity'],
                // CRITICAL: Price, discount, and tax MUST come from PO item (not user input)
                'unit_price'            => $poItem->unit_price,
                'discount_percentage'   => $poItem->discount_percent ?? 0,
                'tax_rate'              => $poItem->tax_percent ?? 0,
            ];
        }

        return $lineItems;
    }

    /**
     * Detect discrepancies between GR, PO, and Invoice
     * 
     * @param GoodsReceipt $gr
     * @param mixed $po
     * @param array $lineItems
     * @param array $calculation
     * @return array
     */
    private function detectDiscrepancies($gr, $po, array $lineItems, array $calculation): array
    {
        $hasDiscrepancy = false;
        $expectedTotal = null;
        $varianceAmount = null;
        $variancePercentage = null;

        // Compare invoice prices with PO prices
        foreach ($lineItems as $item) {
            $grItem = GoodsReceiptItem::find($item['goods_receipt_item_id']);
            $poItem = $grItem->purchaseOrderItem;

            // Price mismatch detection
            if (abs($item['unit_price'] - $poItem->unit_price) > 0.01) {
                $hasDiscrepancy = true;
            }
        }

        // Calculate expected total from PO
        if ($hasDiscrepancy) {
            $expectedTotal = $po->total_amount;
            $actualTotal = $calculation['invoice_totals']['total_amount'];
            $varianceAmount = $actualTotal - $expectedTotal;
            $variancePercentage = $expectedTotal > 0 ? ($varianceAmount / $expectedTotal) * 100 : 0;
        }

        return [
            'has_discrepancy'     => $hasDiscrepancy,
            'expected_total'      => $expectedTotal,
            'variance_amount'     => $varianceAmount,
            'variance_percentage' => $variancePercentage,
        ];
    }

    /**
     * Create Customer Invoice from Goods Receipt
     * 
     * @param GoodsReceipt $gr
     * @param User $actor
     * @param array $items Format: [['goods_receipt_item_id' => int, 'quantity' => int, 'unit_price' => float, 'discount_percentage' => float]]
     * @param array $metadata Additional invoice data (notes, due_date, etc)
     * @return CustomerInvoice
     * @throws DomainException
     */
    public function createCustomerInvoiceFromGR(
        GoodsReceipt $gr,
        User $actor,
        array $items,
        array $metadata = []
    ): CustomerInvoice {
        // Gate: GR must be completed
        if (!$gr->isCompleted()) {
            throw new DomainException(
                "Cannot create invoice from GR with status [{$gr->status}]. GR must be 'completed'."
            );
        }

        // Gate: GR must belong to an organization (RS/Klinik)
        $po = $gr->purchaseOrder;
        if (!$po || !$po->organization_id) {
            throw new DomainException("Goods Receipt must have a valid Purchase Order with organization.");
        }

        // Validate quantities
        $this->validateQuantities($gr, $items);

        // Validate batch & expiry consistency
        $this->validateBatchExpiry($gr, $items);

        return DB::transaction(function () use ($gr, $po, $actor, $items, $metadata) {
            // Prepare line items with GR data
            $lineItemsData = $this->prepareLineItems($gr, $items);

            // Calculate invoice totals using existing pricing engine
            $calculation = $this->calculationService->calculateCompleteInvoice($lineItemsData);

            // Detect discrepancies (GR vs Invoice, PO vs Invoice)
            $discrepancies = $this->detectDiscrepancies($gr, $po, $lineItemsData, $calculation);

            // Create customer invoice
            $invoice = CustomerInvoice::create([
                'invoice_number'       => $metadata['custom_invoice_number'] ?? $this->generateCustomerInvoiceNumber(),
                'organization_id'      => $po->organization_id,
                'purchase_order_id'    => $po->id,
                'goods_receipt_id'     => $gr->id,
                'status'               => CustomerInvoice::STATUS_ISSUED,
                'total_amount'         => $calculation['invoice_totals']['total_amount'],
                'subtotal_amount'      => $calculation['invoice_totals']['subtotal_amount'],
                'discount_amount'      => $calculation['invoice_totals']['discount_amount'],
                'tax_amount'           => $calculation['invoice_totals']['tax_amount'],
                'paid_amount'          => 0,
                'discrepancy_detected' => $discrepancies['has_discrepancy'],
                'expected_total'       => $discrepancies['expected_total'] ?? null,
                'variance_amount'      => $discrepancies['variance_amount'] ?? null,
                'variance_percentage'  => $discrepancies['variance_percentage'] ?? null,
                'due_date'             => $metadata['due_date'] ?? now()->addDays(30),
                'issued_by'            => $actor->id,
                'issued_at'            => now(),
                'notes'                => $metadata['notes'] ?? null,
                'version'              => 1,
            ]);

            // Create line items with GR references
            foreach ($calculation['line_items'] as $index => $lineCalc) {
                $itemData = $lineItemsData[$index];
                $grItem = GoodsReceiptItem::find($itemData['goods_receipt_item_id']);

                $invoice->lineItems()->create([
                    'goods_receipt_item_id' => $grItem->id,
                    'product_id'            => $grItem->purchaseOrderItem->product_id,
                    'product_name'          => $itemData['product_name'],
                    'product_sku'           => $itemData['product_sku'],
                    'batch_no'              => $grItem->batch_no, // READ-ONLY from GR
                    'expiry_date'           => $grItem->expiry_date, // READ-ONLY from GR
                    'quantity'              => $itemData['quantity'],
                    'unit'                  => $grItem->purchaseOrderItem->product->unit ?? 'pcs',
                    'unit_price'            => $itemData['unit_price'],
                    'discount_percentage'   => $itemData['discount_percentage'] ?? 0,
                    'discount_amount'       => $lineCalc['discount_amount'],
                    'tax_rate'              => $itemData['tax_rate'] ?? 0,
                    'tax_amount'            => $lineCalc['tax_amount'],
                    'line_total'            => $lineCalc['line_total'],
                ]);
            }

            // Audit log
            $this->auditService->log(
                action:     'customer_invoice.created_from_gr',
                entityType: CustomerInvoice::class,
                entityId:   $invoice->id,
                metadata:   [
                    'invoice_number'      => $invoice->invoice_number,
                    'goods_receipt_id'    => $gr->id,
                    'gr_number'           => $gr->gr_number,
                    'purchase_order_id'   => $po->id,
                    'po_number'           => $po->po_number,
                    'organization_id'     => $po->organization_id,
                    'total_amount'        => $invoice->total_amount,
                    'discrepancy_detected' => $discrepancies['has_discrepancy'],
                    'item_count'          => count($items),
                ],
                userId: $actor->id,
            );

            return $invoice->load(['lineItems', 'goodsReceipt', 'purchaseOrder', 'organization']);
        });
    }

    /**
     * Generate unique supplier invoice number (internal Medikindo)
     * 
     * @return string
     */
    private function generateInvoiceNumber(): string
    {
        return 'INV-SUP-' . strtoupper(substr(uniqid(), -5));
    }

    /**
     * Generate unique customer invoice number
     * 
     * @return string
     */
    private function generateCustomerInvoiceNumber(): string
    {
        return 'INV-CUST-' . strtoupper(substr(uniqid(), -5));
    }

    /**
     * Get remaining quantity for a GR item
     * 
     * @param GoodsReceiptItem $grItem
     * @return int
     */
    public function getRemainingQuantity(GoodsReceiptItem $grItem): int
    {
        return $grItem->remaining_quantity;
    }

    /**
     * Check if GR has remaining quantity for invoicing
     * 
     * @param GoodsReceipt $gr
     * @return bool
     */
    public function hasRemainingQuantity(GoodsReceipt $gr): bool
    {
        return $gr->hasRemainingQuantity();
    }
}
