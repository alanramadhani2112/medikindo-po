<?php

namespace App\Console\Commands;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\SupplierInvoiceStatus;
use App\Models\CustomerInvoice;
use App\Models\SupplierInvoice;
use App\Models\User;
use App\Notifications\InvoiceOverdueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'app:check-overdue-invoices';
    protected $description = 'Update status overdue dan kirim notifikasi untuk invoice AP/AR yang melewati jatuh tempo.';

    public function handle(): int
    {
        $this->info('Checking overdue invoices — ' . now()->toDateTimeString());
        $today = now()->startOfDay();

        // ── AR: Customer Invoices ──────────────────────────────────────────
        $arUpdated = CustomerInvoice::whereIn('status', [
                CustomerInvoiceStatus::ISSUED->value,
                CustomerInvoiceStatus::PARTIAL_PAID->value,
            ])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->get();

        $arCount = 0;
        foreach ($arUpdated as $inv) {
            // CustomerInvoiceStatus tidak punya OVERDUE — tandai dengan flag overdue_at
            // Status tetap issued/partial_paid, tapi kita set overdue_notified_at
            DB::table('customer_invoices')
                ->where('id', $inv->id)
                ->whereNull('overdue_notified_at')
                ->update(['overdue_notified_at' => now()]);
            $arCount++;
        }

        // ── AP: Supplier Invoices ──────────────────────────────────────────
        $apToOverdue = SupplierInvoice::whereIn('status', [
                SupplierInvoiceStatus::DRAFT->value,
                SupplierInvoiceStatus::VERIFIED->value,
            ])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->get();

        $apCount = 0;
        foreach ($apToOverdue as $inv) {
            $inv->update(['status' => SupplierInvoiceStatus::OVERDUE->value]);
            $apCount++;
        }

        // ── Notify Finance / Super Admin ───────────────────────────────────
        $financeUsers = User::role(['Super Admin'])->get();

        if ($financeUsers->isNotEmpty()) {
            $allOverdue = collect();

            // AR overdue (issued/partial_paid past due)
            CustomerInvoice::whereIn('status', [
                    CustomerInvoiceStatus::ISSUED->value,
                    CustomerInvoiceStatus::PARTIAL_PAID->value,
                ])
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', $today)
                ->get()
                ->each(fn($inv) => $allOverdue->push($inv));

            // AP overdue
            SupplierInvoice::where('status', SupplierInvoiceStatus::OVERDUE->value)
                ->get()
                ->each(fn($inv) => $allOverdue->push($inv));

            foreach ($allOverdue as $inv) {
                foreach ($financeUsers as $user) {
                    $user->notify(new InvoiceOverdueNotification($inv));
                }
            }
        }

        $this->info("AR overdue flagged: {$arCount}");
        $this->info("AP status → overdue: {$apCount}");
        $this->info('Done.');

        return 0;
    }
}
