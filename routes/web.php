<?php

use Illuminate\Support\Facades\Route;

// --- 1. CONTROLLER IMPORTS ---
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FrontendController;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\ManualOrderController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;

// User / Customer Controllers
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\TransactionHistoryController;
use App\Http\Controllers\User\OrderController;


/*
|--------------------------------------------------------------------------
| 2. PUBLIC ROUTES (Akses Terbuka)
|--------------------------------------------------------------------------
*/
Route::get('/', [FrontendController::class, 'index'])->name('home');


/*
|--------------------------------------------------------------------------
| 3. GLOBAL AUTH ROUTES (Bawaan Breeze)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| 4. USER PANEL ROUTES (Khusus Pelanggan / Role: 5)
|--------------------------------------------------------------------------
*/
// Perhatikan penggunaan name('user.') di sini, ini akan otomatis menambahkan prefix 'user.' ke semua nama rute di dalamnya.
Route::middleware(['auth', 'role:5'])->prefix('user')->name('user.')->group(function () {
    
    // Dasbor Pembeli (Pilih Kategori)
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // Riwayat Transaksi
    Route::get('/history', [TransactionHistoryController::class, 'index'])->name('history');

    // Form Pemesanan Berdasarkan Kategori
    Route::get('/order/{slug}', [OrderController::class, 'showCategory'])->name('order.category');
    
    // Proses Checkout
    Route::post('/checkout/process', [OrderController::class, 'processCheckout'])->name('checkout.process');

});


/*
|--------------------------------------------------------------------------
| 5. ADMIN PANEL ROUTES (Super Admin, Admin, Office, Stok)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:1,2,3,4'])->prefix('admin')->name('admin.')->group(function () {

    // Dasbor Umum Admin
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // --- HAK AKSES: SUPER ADMIN (1) ---
    Route::middleware(['role:1'])->group(function () {
        Route::resource('users', AdminUserController::class);
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