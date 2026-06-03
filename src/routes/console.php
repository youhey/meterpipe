<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$command = 'meterpipe:sync-costs --days=7';

foreach (['08:30', '18:00'] as $time) {
    Schedule::command($command)
        ->dailyAt($time)
        ->timezone('Asia/Tokyo')
        ->name("meterpipe:sync-costs:{$time}")
        ->withoutOverlapping(30);
}
