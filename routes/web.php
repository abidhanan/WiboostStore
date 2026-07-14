<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

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
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\DepositController as AdminDepositController;
use App\Http\Controllers\Admin\AdminReportController as AdminAdminReportController;
use App\Http\Controllers\Admin\PromoController;
use App\Http\Controllers\Admin\TutorialController; // <-- IMPORT BARU UNTUK TUTORIAL
use App\Http\Controllers\Admin\ResellerController as AdminResellerController;

// User / Customer Controllers
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\TransactionHistoryController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\QrisPaymentController;
use App\Http\Controllers\User\WalletController; 
use App\Http\Controllers\User\AdminReportController;
use App\Http\Controllers\User\ResellerController;

// Webhook Controller
use App\Http\Controllers\PaymentCallbackController;

/*
|--------------------------------------------------------------------------
| 2. PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/favicon.ico', function () {
    $path = public_path('favicon.ico');

    abort_unless(is_file($path), 404);

    return Response::file($path, [
        'Content-Type' => 'image/x-icon',
        'Cache-Control' => 'public, max-age=0, must-revalidate',
    ]);
});

Route::get('/apple-touch-icon.png', function () {
    $path = public_path('images/wiboost-icon-180.png');

    abort_unless(is_file($path), 404);

    return Response::file($path, [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'public, max-age=0, must-revalidate',
    ]);
});

Route::get('/apple-touch-icon-precomposed.png', function () {
    $path = public_path('images/wiboost-icon-180.png');

    abort_unless(is_file($path), 404);

    return Response::file($path, [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'public, max-age=0, must-revalidate',
    ]);
});

Route::get('/images/wiboost-icon-{size}.png', function (string $size) {
    abort_unless(in_array($size, ['16', '32', '48', '64', '180', '192', '512'], true), 404);

    $path = public_path("images/wiboost-icon-{$size}.png");

    abort_unless(is_file($path), 404);

    return Response::file($path, [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'public, max-age=0, must-revalidate',
    ]);
})->whereNumber('size');

Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/order/{slug}', [OrderController::class, 'showGuestCategory'])->name('guest.order.category');
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
| 4. USER PANEL ROUTES (Role: Buyer / 2, Reseller / 3)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:buyer,reseller'])->prefix('user')->name('user.')->group(function () {
    // Dashboard & History
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/tutorial', [UserDashboardController::class, 'tutorials'])->name('tutorials.index');
    Route::get('/tutorials', [UserDashboardController::class, 'tutorialCategory'])->name('tutorials.category');
    Route::get('/history', [TransactionHistoryController::class, 'index'])->name('history');
    Route::post('/history/{transaction}/login-proof', [TransactionHistoryController::class, 'storeLoginProof'])->name('history.login-proof');
    Route::delete('/history/{transaction}/login-proof', [TransactionHistoryController::class, 'destroyLoginProof'])->name('history.login-proof.destroy');
    Route::get('/report-admin', [AdminReportController::class, 'create'])->name('report.admin');
    Route::post('/report-admin', [AdminReportController::class, 'store'])->name('report.admin.store');
    
    // Order & Checkout
    Route::get('/order/category/{category}/ordersosmed-services', [OrderController::class, 'ordersosmedCategoryServices'])->name('order.ordersosmed-services');
    Route::get('/order/service/{product}/ordersosmed', [OrderController::class, 'ordersosmedServiceMeta'])->name('order.ordersosmed-service');
    Route::get('/order/{slug}', [OrderController::class, 'showCategory'])->name('order.category');
    Route::post('/checkout/process', [OrderController::class, 'processCheckout'])->name('checkout.process');
    Route::delete('/checkout/{transaction}/cancel', [OrderController::class, 'cancelTripayCheckout'])->name('checkout.cancel');
    Route::get('/qris/{invoice_number}/status', [QrisPaymentController::class, 'status'])->name('qris.status');
    Route::get('/qris/{invoice_number}', [QrisPaymentController::class, 'show'])->name('qris.show');

    // Wallet, Deposit & Loyalty Points
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/points', [WalletController::class, 'points'])->name('points.index');
    Route::post('/wallet/topup', [WalletController::class, 'store'])->name('wallet.topup');
    Route::get('/wallet/pay/{invoice_number}', [WalletController::class, 'pay'])->name('wallet.pay');
    Route::delete('/wallet/deposits/{deposit}/cancel', [WalletController::class, 'cancelTripayDeposit'])->name('wallet.deposits.cancel');
    Route::post('/wallet/exchange-points', [WalletController::class, 'exchangePoints'])->name('wallet.exchange');

    // Reseller
    Route::get('/reseller', [ResellerController::class, 'index'])->name('reseller.index');
    Route::post('/reseller/join', [ResellerController::class, 'join'])->name('reseller.join');
    Route::patch('/reseller/payout', [ResellerController::class, 'updatePayout'])->name('reseller.payout.update');
    Route::post('/reseller/withdraw', [ResellerController::class, 'withdraw'])->name('reseller.withdraw');
    Route::delete('/reseller/leave', [ResellerController::class, 'leave'])->name('reseller.leave');
});

/*
|--------------------------------------------------------------------------
| 5. ADMIN PANEL ROUTES (Role: Admin / 1)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/contact-numbers', fn () => redirect()->route('admin.dashboard'));
    Route::post('/dashboard/contact-numbers', [AdminDashboardController::class, 'updateContactNumbers'])->name('dashboard.contact-numbers.update');

    // Master Data (CRUD)
    Route::resource('users', AdminUserController::class);
    Route::resource('categories', CategoryController::class);

    // Reseller
    Route::get('/resellers', [AdminResellerController::class, 'index'])->name('resellers.index');
    Route::patch('/resellers/withdrawals/{commission}', [AdminResellerController::class, 'updateWithdrawal'])->name('resellers.withdrawals.update');
    
    // Produk & Manajemen Stok Kredensial (Akun Premium)
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

    // Transaksi
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/refresh-provider-statuses', fn () => redirect()->route('admin.transactions.index'));
    Route::post('/transactions/refresh-provider-statuses', [AdminTransactionController::class, 'refreshProviderStatuses'])->name('transactions.refresh-provider-statuses');
    Route::get('/transactions/export-pdf', [AdminTransactionController::class, 'exportPdf'])->name('transactions.export_pdf');
    Route::get('/transactions/export-excel', [AdminTransactionController::class, 'exportExcel'])->name('transactions.export_excel');
    Route::patch('/transactions/{id}/update', [AdminTransactionController::class, 'updateStatus'])->name('transactions.update');
    
    // Deposit Management
    Route::get('/deposits', [AdminDepositController::class, 'index'])->name('deposits.index');
    Route::patch('/deposits/{id}', [AdminDepositController::class, 'update'])->name('deposits.update');
    Route::get('/reports', [AdminAdminReportController::class, 'index'])->name('reports.index');
    Route::patch('/reports/{report}', [AdminAdminReportController::class, 'update'])->name('reports.update');

});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| 6. WEBHOOK / CALLBACK ROUTES (TIDAK BOLEH PAKAI AUTH)
|--------------------------------------------------------------------------
*/
Route::post('/midtrans/callback', [PaymentCallbackController::class, 'handleNotification'])->name('midtrans.callback');
Route::post('/tripay/callback', [PaymentCallbackController::class, 'handleTripayCallback'])->name('tripay.callback');
Route::post('/xoftware/callback', [PaymentCallbackController::class, 'handleXoftwareCallback'])->name('xoftware.callback');
