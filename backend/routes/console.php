<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // <-- Tambahkan import ini

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// ==================== DAFTAR JADWAL OTOMATIS (CRON JOB) ====================

Schedule::command('reminder:presensi-masuk')->dailyAt('07:00');
        
Schedule::command('presensi:check-alpha')->hourly();