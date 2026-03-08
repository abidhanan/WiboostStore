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
use App\Http\Controllers\User\WalletController; // <--- Import Wallet Controller Baru


/*
|--------------------------------------------------------------------------
| 2. PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/', [FrontendController::class, 'index'])->name('home');


/*
|--------------------------------------------------------------------------
| 3. GLOBAL AUTH ROUTES (Breeze)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| 4. USER PANEL ROUTES (Role: 5)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:5'])->prefix('user')->name('user.')->group(function () {
    
    // Dasbor & Transaksi
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/history', [TransactionHistoryController::class, 'index'])->name('history');
    
    // Pemesanan
    Route::get('/order/{slug}', [OrderController::class, 'showCategory'])->name('order.category');
    Route::post('/checkout/process', [OrderController::class, 'processCheckout'])->name('checkout.process');

    // Wiboost Wallet (Top Up Saldo)
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/topup', [WalletController::class, 'store'])->name('wallet.topup');

});


/*
|--------------------------------------------------------------------------
| 5. ADMIN PANEL ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:1,2,3,4'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // --- SUPER ADMIN ONLY (1) ---
    Route::middleware(['role:1'])->group(function () {
        Route::resource('users', AdminUserController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
    });

    // --- STOK (4) ---
    Route::middleware(['role:1,4'])->group(function () {
        Route::resource('stocks', StockController::class);
    });

    // --- MANUAL PROCESS (2) ---
    Route::middleware(['role:1,2'])->group(function () {
        Route::get('/manual-orders', [ManualOrderController::class, 'index'])->name('manual-orders.index');
        Route::post('/manual-orders/{id}/complete', [ManualOrderController::class, 'markAsComplete'])->name('manual-orders.complete');
    });

    // --- TRANSAKSI & REPORTS (1,2,3) ---
    Route::middleware(['role:1,2,3'])->group(function () {
        Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
        Route::patch('/transactions/{id}/update', [AdminTransactionController::class, 'updateStatus'])->name('transactions.update'); // Rute Update Status
        Route::get('/reports', [AdminTransactionController::class, 'reports'])->name('reports.index');
    });

});

require __DIR__.'/auth.php';