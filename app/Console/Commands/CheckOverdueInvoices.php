<?php

namespace App\Console\Commands;

use App\Models\CustomerInvoice;
use App\Models\SupplierInvoice;
use App\Models\User;
use App\Notifications\InvoiceOverdueNotification;
use Illuminate\Console\Command;

class CheckOverdueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-overdue-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pemeriksaan otomatis faktur (AP/AR) yang belum lunas dan telah melewati batas jatuh tempo (overdue).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting overdue check...');
        $today = now()->startOfDay();

        // Cari Admin Keuangan / Super Admin untuk dikirimi notifikasi
        // Misalnya kita kirim ke semua user yang punya role 'Super Admin' 
        // atau role khusus 'Finance Admin'. Di Medikindo kita fallback ke Super Admin/Medikindo Admin
        $financeUsers = User::role(['Super Admin'])->get();
        if ($financeUsers->isEmpty()) {
            $this->warn('No finance users found to notify.');
            return 1;
        }

        // Cek Supplier Invoices (AP)
        $apOverdue = SupplierInvoice::whereIn('status', ['unpaid', 'partial'])
            ->whereDate('due_date', '<', $today)
            ->get();

        foreach ($apOverdue as $inv) {
            foreach ($financeUsers as $user) {
                // To avoid duplicate spam every minute, we could track if notified recently,
                // but for MVP we just send. Standard Laravel Notifications appends a row.
                $user->notify(new InvoiceOverdueNotification($inv));
            }
        }
        $this->info('Checked AP Invoices: ' . $apOverdue->count() . ' overdue.');

        // Cek Customer Invoices (AR)
        $arOverdue = CustomerInvoice::whereIn('status', ['unpaid', 'partial'])
            ->whereDate('due_date', '<', $today)
            ->get();

        foreach ($arOverdue as $inv) {
            foreach ($financeUsers as $user) {
                $user->notify(new InvoiceOverdueNotification($inv));
            }
        }
        $this->info('Checked AR Invoices: ' . $arOverdue->count() . ' overdue.');

        return 0;
    }
}
