<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Akses Terbuka)
|--------------------------------------------------------------------------
| Jalur ini bisa diakses oleh siapa saja tanpa perlu login.
*/
Route::get('/', function () {
    return view('welcome'); // Nanti kita ganti dengan desain Landing Page Wiboost Store
})->name('home');


/*
|--------------------------------------------------------------------------
| 2. GLOBAL AUTH ROUTES (Semua User Login)
|--------------------------------------------------------------------------
| Jalur untuk pengaturan profil bawaan Breeze. Semua role bisa akses ini.
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| 3. USER PANEL ROUTES (Khusus Pelanggan / Role: 5)
|--------------------------------------------------------------------------
| Jalur khusus pembeli untuk melihat dasbor, kategori, dan riwayat.
*/
Route::middleware(['auth', 'role:5'])->group(function () {
    
    Route::get('/dashboard', function () {
        return view('dashboard'); // Menampilkan dashboard pembeli bawaan Breeze untuk sementara
    })->name('dashboard');

    Route::get('/history', function () {
        return "Halaman Riwayat Transaksi & Nota Pembeli";
    })->name('user.history');

    Route::get('/kategori/{slug}', function ($slug) {
        return "Menampilkan form pemesanan untuk kategori: " . $slug;
    })->name('user.category');

});


/*
|--------------------------------------------------------------------------
| 4. ADMIN PANEL ROUTES (Super Admin, Admin, Office, Stok)
|--------------------------------------------------------------------------
| Prefix '/admin' akan otomatis ditambahkan ke URL.
| Name 'admin.' akan otomatis ditambahkan ke nama route.
*/
Route::middleware(['auth', 'role:1,2,3,4'])->prefix('admin')->name('admin.')->group(function () {

    // Dasbor Umum Manajemen (Bisa diakses role 1, 2, 3, 4)
    Route::get('/dashboard', function () {
        return "Selamat datang di Dasbor Manajemen Wiboost Store!";
    })->name('dashboard');


    // --- HAK AKSES SUPER ADMIN (1) ---
    Route::middleware(['role:1'])->group(function () {
        Route::get('/users', function () { return "Halaman Manajemen Staff & User"; })->name('users.index');
        Route::get('/categories', function () { return "Halaman Kelola Kategori Layanan"; })->name('categories.index');
        Route::get('/products', function () { return "Halaman Kelola Produk & Harga API"; })->name('products.index');
        Route::get('/settings', function () { return "Halaman Konfigurasi API (Midtrans, OrderSosmed, dll)"; })->name('settings');
    });


    // --- HAK AKSES: SUPER ADMIN (1) & STOK (4) ---
    Route::middleware(['role:1,4'])->group(function () {
        Route::get('/stocks', function () { return "Halaman Restok Akun Premium (Netflix, Canva, dll)"; })->name('stocks.index');
    });


    // --- HAK AKSES: SUPER ADMIN (1) & ADMIN (2) ---
    Route::middleware(['role:1,2'])->group(function () {
        Route::get('/manual-orders', function () { return "Halaman Eksekusi Pesanan Buzzer & Nomor Luar"; })->name('manual-orders.index');
    });


    // --- HAK AKSES: SUPER ADMIN (1), ADMIN (2), & OFFICE (3) ---
    Route::middleware(['role:1,2,3'])->group(function () {
        Route::get('/transactions', function () { return "Halaman Pantau Semua Transaksi"; })->name('transactions.index');
        Route::get('/reports', function () { return "Halaman Laporan & Analitik Pendapatan"; })->name('reports.index');
    });

});

// Memuat rute autentikasi bawaan Laravel Breeze (Login, Register, Logout)
require __DIR__.'/auth.php';