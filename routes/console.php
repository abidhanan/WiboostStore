<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // <-- Penting untuk penjadwalan

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// =========================================================
// JADWAL ROBOT WIBOOST STORE 🤖
// =========================================================

// Robot Auto-Refund: Berjalan setiap 1 Jam untuk mengecek antrean pesanan
// yang statusnya PENDING > 24 jam untuk direfund secara otomatis.
Schedule::command('wiboost:auto-refund')->hourly();