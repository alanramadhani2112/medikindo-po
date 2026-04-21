<?php

namespace App\Console\Commands;

use App\Services\OverdueService;
use App\Events\InvoiceOverdue;
use App\Models\SupplierInvoice;
use App\Models\CustomerInvoice;
use App\Enums\CustomerInvoiceStatus;
use Illuminate\Console\Command;

class UpdateOverdueInvoicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:update-overdue
                            {--notify : Send notifications for newly overdue invoices}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan and update overdue invoices, optionally send notifications';

    protected OverdueService $overdueService;

    public function __construct(OverdueService $overdueService)
    {
        parent::__construct();
        $this->overdueService = $overdueService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Scanning for overdue invoices...');
        $this->newLine();

        // Update Supplier Invoices (AP)
        $this->info('📋 Checking Supplier Invoices (AP)...');
        $supplierStats = $this->overdueService->updateOverdueSupplierInvoices();
        
        $this->line("  ✓ Checked: {$supplierStats['total_checked']}");
        $this->line("  ✓ Updated to OVERDUE: {$supplierStats['updated']}");
        if ($supplierStats['skipped'] > 0) {
            $this->warn("  ⚠ Skipped: {$supplierStats['skipped']}");
        }
        $this->newLine();

        // Scan Customer Invoices (AR)
        $this->info('📋 Checking Customer Invoices (AR)...');
        $customerStats = $this->overdueService->scanOverdueCustomerInvoices();
        
        $this->line("  ✓ Total Overdue: {$customerStats['total_overdue']}");
        $this->line("  ✓ Outstanding Amount: Rp " . number_format($customerStats['total_outstanding'], 2));
        $this->newLine();

        // Show aging breakdown
        if ($customerStats['total_overdue'] > 0) {
            $this->info('📊 Aging Breakdown (Customer Invoices):');
            foreach ($customerStats['aging_breakdown'] as $bucket => $count) {
                if ($count > 0) {
                    $this->line("  • {$bucket} days: {$count} invoices");
                }
            }
            $this->newLine();
        }

        // Send notifications if requested
        if ($this->option('notify')) {
            $this->info('📧 Sending overdue notifications...');
            $this->sendOverdueNotifications();
            $this->newLine();
        }

        $this->info('✅ Overdue invoice scan completed!');
        
        return Command::SUCCESS;
    }

    /**
     * Send notifications for overdue invoices
     */
    protected function sendOverdueNotifications(): void
    {
        // Notify for overdue Supplier Invoices
        $overdueSupplierInvoices = SupplierInvoice::query()
            ->where('status', \App\Enums\SupplierInvoiceStatus::OVERDUE->value)
            ->get();

        foreach ($overdueSupplierInvoices as $invoice) {
            event(new InvoiceOverdue($invoice, 'supplier', $invoice->days_overdue));
        }

        // Notify for overdue Customer Invoices
        $overdueCustomerInvoices = CustomerInvoice::query()
            ->whereIn('status', [
                CustomerInvoiceStatus::ISSUED->value,
                CustomerInvoiceStatus::PARTIAL_PAID->value,
            ])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->get();

        foreach ($overdueCustomerInvoices as $invoice) {
            event(new InvoiceOverdue($invoice, 'customer', $invoice->days_overdue));
        }

        $totalNotifications = $overdueSupplierInvoices->count() + $overdueCustomerInvoices->count();
        $this->line("  ✓ Sent {$totalNotifications} notifications");
    }
}
