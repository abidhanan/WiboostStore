<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentCallbackController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Di sini tempat kamu mendaftarkan rute untuk aplikasi atau layanan luar 
| (seperti Midtrans) yang ingin mengirim data ke Wiboost Store.
*/

// Rute untuk menerima notifikasi otomatis dari Midtrans
Route::post('/midtrans/callback', [PaymentCallbackController::class, 'handleNotification']);