<?php

use Illuminate\Support\Facades\Route;

// --- CONTROLLER IMPORTS ---
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FrontendController;

// Admin Controllers (Nanti kita buat file-nya)
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\ManualOrderController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;

// User Controllers (Nanti kita buat file-nya)
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\TransactionHistoryController;
use App\Http\Controllers\User\OrderController;


/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Akses Terbuka)
|--------------------------------------------------------------------------
| Menampilkan Landing Page dengan statistik real-time.
*/
Route::get('/', [FrontendController::class, 'index'])->name('home');


/*
|--------------------------------------------------------------------------
| 2. GLOBAL AUTH ROUTES (Semua User Login)
|--------------------------------------------------------------------------
| Bawaan Laravel Breeze untuk mengatur email dan password.
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
| Area khusus pembeli Wiboost Store.
*/
Route::middleware(['auth', 'role:5'])->prefix('user')->name('user.')->group(function () {
    
    // Dasbor Pembeli (Pilih Kategori)
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // Riwayat Transaksi & Nota
    Route::get('/history', [TransactionHistoryController::class, 'index'])->name('history');

    // Form Pemesanan Berdasarkan Kategori
    Route::get('/order/{slug}', [OrderController::class, 'showCategory'])->name('order.category');
    
    // Proses Checkout (Saat tombol Pesan Sekarang dipencet)
    Route::post('/checkout', [OrderController::class, 'processCheckout'])->name('checkout.process');

});


/*
|--------------------------------------------------------------------------
| 4. ADMIN PANEL ROUTES (Super Admin, Admin, Office, Stok)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:1,2,3,4'])->prefix('admin')->name('admin.')->group(function () {

    // Dasbor Umum Manajemen (Grafik & Statistik)
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');


    // --- HAK AKSES SUPER ADMIN (1) ---
    Route::middleware(['role:1'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
    });


    // --- HAK AKSES: SUPER ADMIN (1) & STOK (4) ---
    Route::middleware(['role:1,4'])->group(function () {
        Route::resource('stocks', StockController::class);
    });


    // --- HAK AKSES: SUPER ADMIN (1) & ADMIN (2) ---
    Route::middleware(['role:1,2'])->group(function () {
        Route::get('/manual-orders', [ManualOrderController::class, 'index'])->name('manual-orders.index');
        Route::post('/manual-orders/{id}/complete', [ManualOrderController::class, 'markAsComplete'])->name('manual-orders.complete');
    });


    // --- HAK AKSES: SUPER ADMIN (1), ADMIN (2), & OFFICE (3) ---
    Route::middleware(['role:1,2,3'])->group(function () {
        Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
        Route::get('/reports', [AdminTransactionController::class, 'reports'])->name('reports.index');
    });

});

// Memuat rute autentikasi bawaan Laravel Breeze (Login, Register, Logout)
require __DIR__.'/auth.php';