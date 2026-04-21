<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\InvoiceApproved;
use App\Events\PaymentCreated;
use App\Events\InvoiceOverdue;
use App\Listeners\SetInvoiceDueDate;
use App\Listeners\SendOverdueNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        InvoiceApproved::class => [
            SetInvoiceDueDate::class,
        ],
        InvoiceOverdue::class => [
            SendOverdueNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
