<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-mark alpha setiap hari jam 10:05 WIB (setelah batas terlambat)
Schedule::command('absensi:mark-alpha')
    ->dailyAt('10:05')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();
