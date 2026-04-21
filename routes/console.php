<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Finance Engine: Daily overdue invoice check
Schedule::command('app:check-overdue-invoices')->dailyAt('08:00');
Schedule::command('invoices:update-overdue --notify')->dailyAt('09:00');
