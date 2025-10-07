<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Fase 2 - Widget Scheduler
// Demo widget: hvert 10. sekund (for testing)
Schedule::command('widgets:refresh demo.clock')->everyTenSeconds();

// Generell widget refresh: hvert minutt (sjekker om de trenger refresh)
Schedule::command('widgets:refresh')->everyMinute();

