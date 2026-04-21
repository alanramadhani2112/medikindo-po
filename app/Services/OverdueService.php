<?php

namespace App\Services;

use App\Models\SupplierInvoice;
use App\Models\CustomerInvoice;
use App\Enums\SupplierInvoiceStatus;
use App\Enums\CustomerInvoiceStatus;
use App\Events\InvoiceOverdue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class OverdueService
{
    /**
     * Scan and update all overdue Supplier Invoices
     *
     * @return array Statistics of updated invoices
     */
    public function updateOverdueSupplierInvoices(): array
    {
        $updated = 0;
        $skipped = 0;

        $overdueInvoices = SupplierInvoice::query()
            ->whereNotIn('status', [SupplierInvoiceStatus::PAID->value])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->where('status', '!=', SupplierInvoiceStatus::OVERDUE->value)
            ->get();

        foreach ($overdueInvoices as $invoice) {
            if ($invoice->outstanding_amount > 0) {
                try {
                    DB::transaction(function () use ($invoice) {
                        if ($invoice->canTransitionTo(SupplierInvoiceStatus::OVERDUE)) {
                            $invoice->status = SupplierInvoiceStatus::OVERDUE;
                            $invoice->save();

                            Log::info('Supplier Invoice marked as OVERDUE', [
                                'invoice_id' => $invoice->id,
                                'invoice_number' => $invoice->invoice_number,
                                'due_date' => $invoice->due_date->format('Y-m-d'),
                                'days_overdue' => $invoice->days_overdue,
                                'outstanding' => $invoice->outstanding_amount,
                            ]);

                            // Dispatch event
                            event(new InvoiceOverdue($invoice, 'supplier', $invoice->days_overdue));
                        }
                    });
                    $updated++;
                } catch (\Exception $e) {
                    Log::error('Failed to mark Supplier Invoice as overdue', [
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage(),
                    ]);
                    $skipped++;
                }
            }
        }

        return [
            'type' => 'supplier_invoices',
            'total_checked' => $overdueInvoices->count(),
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    /**
     * Scan and update all overdue Customer Invoices
     * Note: CustomerInvoice doesn't have OVERDUE status, but we track it via isOverdueByDate()
     *
     * @return array Statistics of overdue invoices
     */
    public function scanOverdueCustomerInvoices(): array
    {
        $overdueInvoices = CustomerInvoice::query()
            ->whereIn('status', [
                CustomerInvoiceStatus::ISSUED->value,
                CustomerInvoiceStatus::PARTIAL_PAID->value,
            ])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->get();

        $stats = [
            'type' => 'customer_invoices',
            'total_overdue' => $overdueInvoices->count(),
            'total_outstanding' => $overdueInvoices->sum('outstanding_amount'),
            'aging_breakdown' => [
                '1-30' => 0,
                '31-60' => 0,
                '61-90' => 0,
                '90+' => 0,
            ],
        ];

        foreach ($overdueInvoices as $invoice) {
            $bucket = $invoice->aging_bucket;
            if (isset($stats['aging_breakdown'][$bucket])) {
                $stats['aging_breakdown'][$bucket]++;
            }
        }

        Log::info('Customer Invoice overdue scan completed', $stats);

        return $stats;
    }

    /**
     * Get all overdue invoices for a specific organization
     *
     * @param int $organizationId
     * @return Collection
     */
    public function getOverdueInvoicesByOrganization(int $organizationId): Collection
    {
        $supplierInvoices = SupplierInvoice::query()
            ->where('organization_id', $organizationId)
            ->where('status', SupplierInvoiceStatus::OVERDUE->value)
            ->get()
            ->map(fn($invoice) => [
                'type' => 'supplier',
                'id' => $invoice->id,
                'number' => $invoice->invoice_number,
                'due_date' => $invoice->due_date,
                'days_overdue' => $invoice->days_overdue,
                'outstanding' => $invoice->outstanding_amount,
                'aging_bucket' => $invoice->aging_bucket,
            ]);

        $customerInvoices = CustomerInvoice::query()
            ->where('organization_id', $organizationId)
            ->whereIn('status', [
                CustomerInvoiceStatus::ISSUED->value,
                CustomerInvoiceStatus::PARTIAL_PAID->value,
            ])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->get()
            ->map(fn($invoice) => [
                'type' => 'customer',
                'id' => $invoice->id,
                'number' => $invoice->invoice_number,
                'due_date' => $invoice->due_date,
                'days_overdue' => $invoice->days_overdue,
                'outstanding' => $invoice->outstanding_amount,
                'aging_bucket' => $invoice->aging_bucket,
            ]);

        return $supplierInvoices->concat($customerInvoices);
    }

    /**
     * Get aging report for all invoices
     *
     * @param int|null $organizationId
     * @return array
     */
    public function getAgingReport(?int $organizationId = null): array
    {
        $query = CustomerInvoice::query()
            ->whereIn('status', [
                CustomerInvoiceStatus::ISSUED->value,
                CustomerInvoiceStatus::PARTIAL_PAID->value,
            ]);

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $invoices = $query->get();

        $report = [
            'current' => ['count' => 0, 'amount' => 0],
            '1-30' => ['count' => 0, 'amount' => 0],
            '31-60' => ['count' => 0, 'amount' => 0],
            '61-90' => ['count' => 0, 'amount' => 0],
            '90+' => ['count' => 0, 'amount' => 0],
        ];

        foreach ($invoices as $invoice) {
            $bucket = $invoice->aging_bucket;
            $report[$bucket]['count']++;
            $report[$bucket]['amount'] += $invoice->outstanding_amount;
        }

        return $report;
    }

    /**
     * Check if an organization has any overdue invoices
     *
     * @param int $organizationId
     * @return bool
     */
    public function hasOverdueInvoices(int $organizationId): bool
    {
        $hasOverdueSupplier = SupplierInvoice::query()
            ->where('organization_id', $organizationId)
            ->where('status', SupplierInvoiceStatus::OVERDUE->value)
            ->exists();

        $hasOverdueCustomer = CustomerInvoice::query()
            ->where('organization_id', $organizationId)
            ->whereIn('status', [
                CustomerInvoiceStatus::ISSUED->value,
                CustomerInvoiceStatus::PARTIAL_PAID->value,
            ])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->exists();

        return $hasOverdueSupplier || $hasOverdueCustomer;
    }
}
