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
use App\Http\Controllers\Admin\CredentialController; 
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\ManualOrderController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\DepositController as AdminDepositController;
use App\Http\Controllers\Admin\PromoController;
use App\Http\Controllers\Admin\TutorialController; // <-- IMPORT BARU UNTUK TUTORIAL

// User / Customer Controllers
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\TransactionHistoryController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\WalletController; 

// Webhook Controller
use App\Http\Controllers\PaymentCallbackController;

/*
|--------------------------------------------------------------------------
| 2. PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/legal/{page}', [FrontendController::class, 'legal'])
    ->whereIn('page', ['terms', 'privacy-policy', 'refund-policy', 'contact'])
    ->name('legal.show');

/*
|--------------------------------------------------------------------------
| 3. GLOBAL AUTH ROUTES (Breeze Profile)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return auth()->user()?->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('user.dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| 4. USER PANEL ROUTES (Role: Buyer / 2)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:buyer'])->prefix('user')->name('user.')->group(function () {
    // Dashboard & History
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/history', [TransactionHistoryController::class, 'index'])->name('history');
    
    // Order & Checkout
    Route::get('/order/{slug}', [OrderController::class, 'showCategory'])->name('order.category');
    Route::post('/checkout/process', [OrderController::class, 'processCheckout'])->name('checkout.process');

    // Wallet, Deposit & Loyalty Points
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/topup', [WalletController::class, 'store'])->name('wallet.topup');
    Route::get('/wallet/pay/{invoice_number}', [WalletController::class, 'pay'])->name('wallet.pay');
    Route::post('/wallet/exchange-points', [WalletController::class, 'exchangePoints'])->name('wallet.exchange');
});

/*
|--------------------------------------------------------------------------
| 5. ADMIN PANEL ROUTES (Role: Admin / 1)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Master Data (CRUD)
    Route::resource('users', AdminUserController::class);
    Route::resource('categories', CategoryController::class);
    
    // Produk & Manajemen Stok Kredensial (Akun Premium/Nomor Luar)
    Route::post('/products/sync/digiflazz', [ProductController::class, 'syncDigiflazz'])->name('products.sync.digiflazz');
    Route::post('/products/sync/ordersosmed', [ProductController::class, 'syncOrderSosmed'])->name('products.sync.ordersosmed');
    Route::resource('products', ProductController::class);
    Route::get('/products/{product}/credentials', [CredentialController::class, 'index'])->name('credentials.index');
    Route::post('/products/{product}/credentials', [CredentialController::class, 'store'])->name('credentials.store');
    Route::delete('/credentials/{credential}', [CredentialController::class, 'destroy'])->name('credentials.destroy');

    // Promos, Stocks, & TUTORIAL
    Route::resource('promos', PromoController::class);
    Route::resource('stocks', StockController::class);
    Route::resource('tutorials', TutorialController::class); // <-- ROUTE BARU TUTORIAL

    // Transaksi & Laporan
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/export-pdf', [AdminTransactionController::class, 'exportPdf'])->name('transactions.export_pdf');
    Route::patch('/transactions/{id}/update', [AdminTransactionController::class, 'updateStatus'])->name('transactions.update');
    
    Route::get('/reports', [AdminTransactionController::class, 'reports'])->name('reports.index');

    // Deposit Management
    Route::get('/deposits', [AdminDepositController::class, 'index'])->name('deposits.index');
    Route::patch('/deposits/{id}', [AdminDepositController::class, 'update'])->name('deposits.update');

    // Manual Orders
    Route::get('/manual-orders', [ManualOrderController::class, 'index'])->name('manual-orders.index');
    Route::post('/manual-orders/{id}/complete', [ManualOrderController::class, 'markAsComplete'])->name('manual-orders.complete');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| 6. WEBHOOK / CALLBACK ROUTES (TIDAK BOLEH PAKAI AUTH)
|--------------------------------------------------------------------------
*/
Route::post('/midtrans/callback', [PaymentCallbackController::class, 'handleNotification'])->name('midtrans.callback');
