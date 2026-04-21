<?php

namespace App\Listeners;

use App\Events\InvoiceOverdue;
use App\Notifications\InvoiceOverdueNotification;
use Illuminate\Support\Facades\Log;

class SendOverdueNotification
{
    /**
     * Handle the event.
     */
    public function handle(InvoiceOverdue $event): void
    {
        $invoice = $event->invoice;

        // Get users to notify (organization admins, finance team, etc.)
        $usersToNotify = $this->getUsersToNotify($invoice);

        foreach ($usersToNotify as $user) {
            try {
                $user->notify(new InvoiceOverdueNotification(
                    $invoice,
                    $event->invoiceType,
                    $event->daysOverdue
                ));

                Log::info('Overdue notification sent', [
                    'invoice_type' => $event->invoiceType,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'user_id' => $user->id,
                    'days_overdue' => $event->daysOverdue,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send overdue notification', [
                    'invoice_type' => $event->invoiceType,
                    'invoice_id' => $invoice->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get users who should be notified about overdue invoice
     *
     * @param \App\Models\SupplierInvoice|\App\Models\CustomerInvoice $invoice
     * @return \Illuminate\Support\Collection
     */
    protected function getUsersToNotify($invoice): \Illuminate\Support\Collection
    {
        // Get organization users with finance or admin roles
        return $invoice->organization->users()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'finance', 'manager']);
            })
            ->get();
    }
}
