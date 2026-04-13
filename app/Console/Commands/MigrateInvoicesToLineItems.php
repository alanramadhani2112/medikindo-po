<?php

namespace App\Console\Commands;

use App\Models\CustomerInvoice;
use App\Models\CustomerInvoiceLineItem;
use App\Models\SupplierInvoice;
use App\Models\SupplierInvoiceLineItem;
use App\Services\BCMathCalculatorService;
use App\Services\InvoiceCalculationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateInvoicesToLineItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:migrate-to-line-items
                            {--dry-run : Run in dry-run mode without making changes}
                            {--batch-size=50 : Number of invoices to process per batch}
                            {--invoice-id= : Migrate specific invoice by ID}
                            {--type=all : Invoice type to migrate (all, supplier, customer)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing invoices to new line items structure with BCMath recalculation';

    private BCMathCalculatorService $calculator;
    private InvoiceCalculationService $calculationService;
    private array $stats = [
        'supplier_processed' => 0,
        'supplier_migrated' => 0,
        'supplier_skipped' => 0,
        'supplier_errors' => 0,
        'customer_processed' => 0,
        'customer_migrated' => 0,
        'customer_skipped' => 0,
        'customer_errors' => 0,
        'discrepancies_found' => 0,
    ];

    public function __construct(
        BCMathCalculatorService $calculator,
        InvoiceCalculationService $calculationService
    ) {
        parent::__construct();
        $this->calculator = $calculator;
        $this->calculationService = $calculationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch-size');
        $invoiceId = $this->option('invoice-id');
        $type = $this->option('type');

        $this->info('=================================================');
        $this->info('Invoice Line Items Migration');
        $this->info('=================================================');
        $this->info('Mode: ' . ($isDryRun ? 'DRY RUN (no changes will be made)' : 'LIVE'));
        $this->info('Batch Size: ' . $batchSize);
        $this->info('Type: ' . $type);
        if ($invoiceId) {
            $this->info('Invoice ID: ' . $invoiceId);
        }
        $this->newLine();

        if ($isDryRun) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be made to the database');
            $this->newLine();
        }

        // Confirm before proceeding in live mode
        if (!$isDryRun && !$this->confirm('This will modify invoice data. Have you backed up your database?')) {
            $this->error('Migration cancelled. Please backup your database first.');
            return self::FAILURE;
        }

        try {
            // Migrate supplier invoices
            if (in_array($type, ['all', 'supplier'])) {
                $this->info('📦 Migrating Supplier Invoices...');
                $this->migrateSupplierInvoices($isDryRun, $batchSize, $invoiceId);
                $this->newLine();
            }

            // Migrate customer invoices
            if (in_array($type, ['all', 'customer'])) {
                $this->info('📄 Migrating Customer Invoices...');
                $this->migrateCustomerInvoices($isDryRun, $batchSize, $invoiceId);
                $this->newLine();
            }

            // Display summary
            $this->displaySummary($isDryRun);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }

    /**
     * Migrate supplier invoices to line items format
     */
    private function migrateSupplierInvoices(bool $isDryRun, int $batchSize, ?string $invoiceId): void
    {
        $query = SupplierInvoice::with(['purchaseOrder.items.product', 'goodsReceipt.items.purchaseOrderItem.product']);

        if ($invoiceId) {
            $query->where('id', $invoiceId);
        }

        $totalCount = $query->count();
        $this->info("Found {$totalCount} supplier invoices to process");

        if ($totalCount === 0) {
            return;
        }

        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        $query->chunk($batchSize, function ($invoices) use ($isDryRun, $bar) {
            foreach ($invoices as $invoice) {
                $this->stats['supplier_processed']++;

                try {
                    // Skip if already has line items
                    if ($invoice->lineItems()->exists()) {
                        $this->stats['supplier_skipped']++;
                        $bar->advance();
                        continue;
                    }

                    // Skip if no goods receipt
                    if (!$invoice->goodsReceipt) {
                        $this->stats['supplier_skipped']++;
                        $bar->advance();
                        continue;
                    }

                    // Get organization defaults
                    $organization = $invoice->purchaseOrder->organization;
                    $defaultTaxRate = $organization->default_tax_rate ?? '0.00';
                    $defaultDiscountPercentage = $organization->default_discount_percentage ?? '0.00';

                    // Prepare line items data from goods receipt
                    $lineItemsData = [];
                    foreach ($invoice->goodsReceipt->items as $grItem) {
                        $poItem = $grItem->purchaseOrderItem;
                        
                        $lineItemsData[] = [
                            'product_id' => $poItem->product_id,
                            'product_name' => $poItem->product->name ?? 'Unknown Product',
                            'quantity' => (string) $grItem->quantity_received,
                            'unit_price' => (string) $poItem->unit_price,
                            'discount_percentage' => $defaultDiscountPercentage,
                            'tax_rate' => $defaultTaxRate,
                        ];
                    }

                    // Calculate complete invoice with line items
                    $invoiceCalculation = $this->calculationService->calculateCompleteInvoice($lineItemsData);

                    // Check tolerance
                    $toleranceCheck = $this->calculationService->verifyToleranceCheck(
                        $invoiceCalculation['line_items'],
                        $invoice->total_amount
                    );

                    if (!$toleranceCheck['passed']) {
                        $this->stats['discrepancies_found']++;
                        $this->newLine();
                        $this->warn("⚠️  Discrepancy found in Supplier Invoice #{$invoice->id}:");
                        $this->warn("   Expected: {$invoice->total_amount}");
                        $this->warn("   Calculated: {$invoiceCalculation['total_amount']}");
                        $this->warn("   Difference: {$toleranceCheck['difference']}");
                    }

                    if (!$isDryRun) {
                        DB::transaction(function () use ($invoice, $invoiceCalculation, $lineItemsData) {
                            // Update invoice totals
                            $invoice->update([
                                'subtotal_amount' => $invoiceCalculation['subtotal_amount'],
                                'discount_amount' => $invoiceCalculation['discount_amount'],
                                'tax_amount' => $invoiceCalculation['tax_amount'],
                                // Keep original total_amount to preserve historical data
                            ]);

                            // Create line items
                            foreach ($invoiceCalculation['line_items'] as $lineItem) {
                                SupplierInvoiceLineItem::create([
                                    'supplier_invoice_id' => $invoice->id,
                                    'product_id' => $lineItem['product_id'],
                                    'product_name' => $lineItem['product_name'],
                                    'quantity' => $lineItem['quantity'],
                                    'unit_price' => $lineItem['unit_price'],
                                    'discount_percentage' => $lineItem['discount_percentage'] ?? '0.00',
                                    'discount_amount' => $lineItem['discount_amount'],
                                    'tax_rate' => $lineItem['tax_rate'] ?? '0.00',
                                    'tax_amount' => $lineItem['tax_amount'],
                                    'line_total' => $lineItem['line_total'],
                                ]);
                            }
                        });
                    }

                    $this->stats['supplier_migrated']++;
                } catch (\Exception $e) {
                    $this->stats['supplier_errors']++;
                    $this->newLine();
                    $this->error("❌ Error migrating Supplier Invoice #{$invoice->id}: " . $e->getMessage());
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
    }

    /**
     * Migrate customer invoices to line items format
     */
    private function migrateCustomerInvoices(bool $isDryRun, int $batchSize, ?string $invoiceId): void
    {
        $query = CustomerInvoice::with(['purchaseOrder.items.product', 'goodsReceipt.items.purchaseOrderItem.product']);

        if ($invoiceId) {
            $query->where('id', $invoiceId);
        }

        $totalCount = $query->count();
        $this->info("Found {$totalCount} customer invoices to process");

        if ($totalCount === 0) {
            return;
        }

        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        $query->chunk($batchSize, function ($invoices) use ($isDryRun, $bar) {
            foreach ($invoices as $invoice) {
                $this->stats['customer_processed']++;

                try {
                    // Skip if already has line items
                    if ($invoice->lineItems()->exists()) {
                        $this->stats['customer_skipped']++;
                        $bar->advance();
                        continue;
                    }

                    // Skip if no goods receipt
                    if (!$invoice->goodsReceipt) {
                        $this->stats['customer_skipped']++;
                        $bar->advance();
                        continue;
                    }

                    // Get organization defaults
                    $organization = $invoice->purchaseOrder->organization;
                    $defaultTaxRate = $organization->default_tax_rate ?? '0.00';
                    $defaultDiscountPercentage = $organization->default_discount_percentage ?? '0.00';

                    // Prepare line items data from goods receipt
                    $lineItemsData = [];
                    foreach ($invoice->goodsReceipt->items as $grItem) {
                        $poItem = $grItem->purchaseOrderItem;
                        
                        $lineItemsData[] = [
                            'product_id' => $poItem->product_id,
                            'product_name' => $poItem->product->name ?? 'Unknown Product',
                            'quantity' => (string) $grItem->quantity_received,
                            'unit_price' => (string) $poItem->unit_price,
                            'discount_percentage' => $defaultDiscountPercentage,
                            'tax_rate' => $defaultTaxRate,
                        ];
                    }

                    // Calculate complete invoice with line items
                    $invoiceCalculation = $this->calculationService->calculateCompleteInvoice($lineItemsData);

                    // Check tolerance
                    $toleranceCheck = $this->calculationService->verifyToleranceCheck(
                        $invoiceCalculation['line_items'],
                        $invoice->total_amount
                    );

                    if (!$toleranceCheck['passed']) {
                        $this->stats['discrepancies_found']++;
                        $this->newLine();
                        $this->warn("⚠️  Discrepancy found in Customer Invoice #{$invoice->id}:");
                        $this->warn("   Expected: {$invoice->total_amount}");
                        $this->warn("   Calculated: {$invoiceCalculation['total_amount']}");
                        $this->warn("   Difference: {$toleranceCheck['difference']}");
                    }

                    if (!$isDryRun) {
                        DB::transaction(function () use ($invoice, $invoiceCalculation, $lineItemsData) {
                            // Update invoice totals
                            $invoice->update([
                                'subtotal_amount' => $invoiceCalculation['subtotal_amount'],
                                'discount_amount' => $invoiceCalculation['discount_amount'],
                                'tax_amount' => $invoiceCalculation['tax_amount'],
                                // Keep original total_amount to preserve historical data
                            ]);

                            // Create line items
                            foreach ($invoiceCalculation['line_items'] as $lineItem) {
                                CustomerInvoiceLineItem::create([
                                    'customer_invoice_id' => $invoice->id,
                                    'product_id' => $lineItem['product_id'],
                                    'product_name' => $lineItem['product_name'],
                                    'quantity' => $lineItem['quantity'],
                                    'unit_price' => $lineItem['unit_price'],
                                    'discount_percentage' => $lineItem['discount_percentage'] ?? '0.00',
                                    'discount_amount' => $lineItem['discount_amount'],
                                    'tax_rate' => $lineItem['tax_rate'] ?? '0.00',
                                    'tax_amount' => $lineItem['tax_amount'],
                                    'line_total' => $lineItem['line_total'],
                                ]);
                            }
                        });
                    }

                    $this->stats['customer_migrated']++;
                } catch (\Exception $e) {
                    $this->stats['customer_errors']++;
                    $this->newLine();
                    $this->error("❌ Error migrating Customer Invoice #{$invoice->id}: " . $e->getMessage());
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
    }

    /**
     * Display migration summary
     */
    private function displaySummary(bool $isDryRun): void
    {
        $this->info('=================================================');
        $this->info('Migration Summary');
        $this->info('=================================================');
        
        $this->table(
            ['Metric', 'Supplier', 'Customer', 'Total'],
            [
                [
                    'Processed',
                    $this->stats['supplier_processed'],
                    $this->stats['customer_processed'],
                    $this->stats['supplier_processed'] + $this->stats['customer_processed'],
                ],
                [
                    'Migrated',
                    $this->stats['supplier_migrated'],
                    $this->stats['customer_migrated'],
                    $this->stats['supplier_migrated'] + $this->stats['customer_migrated'],
                ],
                [
                    'Skipped',
                    $this->stats['supplier_skipped'],
                    $this->stats['customer_skipped'],
                    $this->stats['supplier_skipped'] + $this->stats['customer_skipped'],
                ],
                [
                    'Errors',
                    $this->stats['supplier_errors'],
                    $this->stats['customer_errors'],
                    $this->stats['supplier_errors'] + $this->stats['customer_errors'],
                ],
            ]
        );

        $this->newLine();
        $this->info("Discrepancies Found: {$this->stats['discrepancies_found']}");
        
        if ($isDryRun) {
            $this->newLine();
            $this->warn('⚠️  This was a DRY RUN - no changes were made');
            $this->info('Run without --dry-run to apply changes');
        } else {
            $this->newLine();
            $this->info('✅ Migration completed successfully!');
        }

        if ($this->stats['discrepancies_found'] > 0) {
            $this->newLine();
            $this->warn('⚠️  Some invoices had calculation discrepancies.');
            $this->warn('   This is expected if organization defaults were not set during original invoice creation.');
            $this->warn('   Original total_amount has been preserved for historical accuracy.');
        }

        if ($this->stats['supplier_errors'] + $this->stats['customer_errors'] > 0) {
            $this->newLine();
            $this->error('❌ Some invoices failed to migrate. Check the error messages above.');
        }
    }
}
