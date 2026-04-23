<?php

namespace App\Services;

use App\Exceptions\AntiPhantomBillingException;
use App\Exceptions\DuplicateMirrorException;
use App\Models\CustomerInvoice;
use App\Models\CustomerInvoiceLineItem;
use App\Models\SupplierInvoice;
use App\Models\TaxConfiguration;
use App\Models\User;
use App\Notifications\NewInvoiceNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Mirror Generation Service
 *
 * Automates creation of a draft AR CustomerInvoice from a verified AP SupplierInvoice.
 * Implements the "Mirror Model" — AP is the single source of truth for physical goods movement.
 *
 * @package App\Services
 */
class MirrorGenerationService
{
    public function __construct(
        private readonly PriceListService          $priceListService,
        private readonly InvoiceCalculationService $calculationService,
        private readonly DocumentNumberService     $documentNumberService,
    ) {}

    /**
     * Check whether a draft CustomerInvoice already exists for the given SupplierInvoice.
     *
     * @param int $supplierInvoiceId
     * @return bool
     */
    public function draftExists(int $supplierInvoiceId): bool
    {
        return CustomerInvoice::where('supplier_invoice_id', $supplierInvoiceId)
            ->whereNotIn('status', [\App\Enums\CustomerInvoiceStatus::VOID->value])
            ->exists();
    }

    /**
     * Generate a draft AR CustomerInvoice from a verified AP SupplierInvoice.
     *
     * Algorithm:
     *   1. Guard: draftExists() → log warning + throw DuplicateMirrorException
     *   2. Guard: apInvoice->status must be 'verified' or 'paid'
     *   3. DB::transaction:
     *      a. Create CustomerInvoice header (status=DRAFT)
     *      b. Loop each SupplierInvoiceLineItem:
     *         - Lookup selling_price via PriceListService
     *         - Get tax rate from TaxConfiguration
     *         - Calculate DPP = selling_price * qty
     *         - Calculate tax = floor(DPP * rate / 100)
     *         - Copy batch_no, expiry_date, quantity, uom from AP
     *         - Save supplier_invoice_item_id (Mirror Link) and cost_price
     *      c. Calculate grand total via InvoiceCalculationService::calculateGrandTotal()
     *      d. Update CustomerInvoice header with totals
     *   4. Dispatch NewInvoiceNotification to finance staff
     *   5. Return CustomerInvoice
     *
     * @param SupplierInvoice $apInvoice  The verified AP invoice to mirror
     * @param int             $customerId The organization_id of the customer (RS/Klinik)
     * @return CustomerInvoice
     * @throws DuplicateMirrorException    If a draft already exists
     * @throws AntiPhantomBillingException If AP status is not verified/paid
     */
    public function generateARFromAP(SupplierInvoice $apInvoice, int $customerId): CustomerInvoice
    {
        // Guard 1: duplicate check
        if ($this->draftExists($apInvoice->id)) {
            Log::warning('MirrorGenerationService: Draft AR already exists for SupplierInvoice', [
                'supplier_invoice_id' => $apInvoice->id,
                'invoice_number'      => $apInvoice->invoice_number,
            ]);
            throw new DuplicateMirrorException(
                "Draft CustomerInvoice sudah ada untuk SupplierInvoice #{$apInvoice->invoice_number}"
            );
        }

        // Guard 2: anti-phantom billing — AP must be verified or paid
        // Safe enum extraction for comparison and string interpolation
        $statusValue = $apInvoice->status instanceof \BackedEnum ? $apInvoice->status->value : $apInvoice->status;
        $allowedStatuses = ['verified', 'paid'];
        if (!in_array($statusValue, $allowedStatuses, true)) {
            throw new AntiPhantomBillingException(
                "SupplierInvoice belum diverifikasi (status: {$statusValue})"
            );
        }

        // Guard 3: block expired items from being invoiced
        $this->validateNoExpiredItems($apInvoice);

        $customerInvoice = DB::transaction(function () use ($apInvoice, $customerId) {
            // Step 3a: Create CustomerInvoice header
            $customerInvoice = CustomerInvoice::create([
                'invoice_number'      => $this->documentNumberService->generateInvoiceNumber($customerId),
                'organization_id'     => $customerId,
                'supplier_invoice_id' => $apInvoice->id,
                'purchase_order_id'   => $apInvoice->purchase_order_id,
                'goods_receipt_id'    => $apInvoice->goods_receipt_id,
                'status'              => \App\Enums\CustomerInvoiceStatus::DRAFT,
                'due_date'            => now()->addDays(30)->toDateString(),
                'total_amount'        => '0.00',
                'subtotal_amount'     => '0.00',
                'discount_amount'     => '0.00',
                'tax_amount'          => '0.00',
                'surcharge'           => '0.00',
                'ematerai_fee'        => '0.00',
                'paid_amount'         => '0.00',
            ]);

            // Step 3b: Loop each AP line item
            $taxRate    = TaxConfiguration::getActivePPNRate();
            $lineItemsForCalc = [];

            $apInvoice->loadMissing('lineItems.product');

            foreach ($apInvoice->lineItems as $apLine) {
                $sellingPrice = $this->priceListService->lookup(
                    $customerId,
                    $apLine->product_id
                );

                $qty = (string) $apLine->quantity;

                // DPP = selling_price * quantity
                $dpp = bcmul($sellingPrice, $qty, 10);

                // Tax = floor(DPP * rate / 100)
                $taxAmount = $this->calculationService->calculateTaxFloor($dpp, $taxRate);

                // Line total = DPP + tax (no discount at line level for mirror)
                $lineTotal = bcadd($dpp, $taxAmount, 2);

                CustomerInvoiceLineItem::create([
                    'customer_invoice_id'      => $customerInvoice->id,
                    'supplier_invoice_item_id' => $apLine->id,          // Mirror Link
                    'product_id'               => $apLine->product_id,
                    'product_name'             => $apLine->product?->name ?? '',
                    'quantity'                 => $qty,
                    'unit_price'               => $sellingPrice,
                    'cost_price'               => (string) $apLine->unit_price, // from AP
                    'discount_percentage'      => '0.00',
                    'discount_amount'          => '0.00',
                    'tax_rate'                 => $taxRate,
                    'tax_amount'               => $taxAmount,
                    'line_total'               => $lineTotal,
                    'batch_no'                 => $apLine->batch_no,  // copy identically
                    'expiry_date'              => $apLine->expiry_date,   // copy identically
                    'uom'                      => $apLine->uom ?? null,
                ]);

                $lineItemsForCalc[] = [
                    'line_subtotal'   => number_format((float) $dpp, 2, '.', ''),
                    'discount_amount' => '0.00',
                    'tax_amount'      => $taxAmount,
                    'line_total'      => $lineTotal,
                ];
            }

            // Step 3c: Calculate grand total
            $totals = $this->calculationService->calculateGrandTotal($lineItemsForCalc, '0.00');

            // Step 3d: Update header with totals
            $customerInvoice->update([
                'subtotal_amount' => $totals['subtotal'],
                'discount_amount' => $totals['discount'],
                'tax_amount'      => $totals['tax_total'],
                'ematerai_fee'    => $totals['ematerai_fee'],
                'total_amount'    => $totals['grand_total'],
            ]);

            return $customerInvoice;
        });

        // Step 4: Dispatch notification to finance staff
        $this->notifyFinanceStaff($customerInvoice);

        return $customerInvoice;
    }

    /**
     * Validate that no line items in the AP invoice have expired batches.
     *
     * @param SupplierInvoice $apInvoice
     * @throws AntiPhantomBillingException if any line item has an expired batch
     */
    private function validateNoExpiredItems(SupplierInvoice $apInvoice): void
    {
        $apInvoice->loadMissing('lineItems.product');

        foreach ($apInvoice->lineItems as $line) {
            if ($line->expiry_date !== null && now()->gte(Carbon::parse($line->expiry_date)->endOfDay())) {
                $productName = $line->product?->name ?? "Product #{$line->product_id}";
                $batchNo     = $line->batch_no ?? '-';
                $expiryDate  = Carbon::parse($line->expiry_date)->format('d/m/Y');

                throw new AntiPhantomBillingException(
                    "Tidak dapat membuat invoice: batch '{$batchNo}' produk '{$productName}' sudah kadaluarsa pada {$expiryDate}"
                );
            }
        }
    }

    /**
     * Dispatch notifications when Customer Invoice is generated.
     * - Finance: NewInvoiceNotification (for review)
     * - Healthcare User (org): CustomerInvoiceIssuedNotification (must pay)
     */
    private function notifyFinanceStaff(CustomerInvoice $invoice): void
    {
        try {
            // Notify Finance/Admin Pusat/Super Admin
            $financeUsers = \App\Models\User::permission('view_customer_invoices')->get();
            if ($financeUsers->isNotEmpty()) {
                Notification::send($financeUsers, new NewInvoiceNotification($invoice));
            }

            // Notify Healthcare User of the organization — invoice issued, must pay
            \App\Models\User::role(['Healthcare User'])->get()
                ->filter(fn($u) => $u->organization_id === $invoice->organization_id)
                ->each(fn($u) => $u->notify(
                    new \App\Notifications\CustomerInvoiceIssuedNotification($invoice)
                ));
        } catch (\Throwable $e) {
            Log::warning('MirrorGenerationService: Failed to dispatch notification', [
                'invoice_id' => $invoice->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
