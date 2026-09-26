<?php

use App\Http\Controllers\Admin\AdminReportController as AdminAdminReportController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CredentialController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepositController as AdminDepositController;
use App\Http\Controllers\Admin\DisplayController;
use App\Http\Controllers\Admin\FormController as AdminFormController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromoController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Admin\ResellerController as AdminResellerController;
use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\DurationController as AdminDurationController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\TutorialController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicFormController;
use App\Http\Controllers\User\AdminReportController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\QrisPaymentController;
use App\Http\Controllers\User\ResellerController;
use App\Http\Controllers\User\TransactionHistoryController;
use App\Http\Controllers\User\WalletController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

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

Route::get('/apple-touch-icon-{dimensions}.png', function (string $dimensions) {
    $path = public_path('images/wiboost-icon-512.png');

    abort_unless(is_file($path), 404);

    return Response::file($path, [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->where('dimensions', '[0-9]+x[0-9]+');

Route::get('/apple-touch-icon-{dimensions}-precomposed.png', function (string $dimensions) {
    $path = public_path('images/wiboost-icon-512.png');

    abort_unless(is_file($path), 404);

    return Response::file($path, [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->where('dimensions', '[0-9]+x[0-9]+');

Route::get('/images/wiboost-icon-{size}.png', function (string $size) {
    abort_unless(in_array($size, ['16', '32', '48', '64', '180', '192', '512'], true), 404);

    $path = public_path("images/wiboost-icon-{$size}.png");

    abort_unless(is_file($path), 404);

    return Response::file($path, [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'public, max-age=0, must-revalidate',
    ]);
})->whereNumber('size');

Route::get('/images/wiboost-logo-nav.png', function () {
    $path = public_path('images/wiboost-logo-nav.png');

    abort_unless(is_file($path), 404);

    return Response::file($path, [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
});

Route::get('/this.onerror=null;this.src={fallback}', function () {
    return redirect(asset('images/wiboost-logo-nav.png') . '?v=20260613-nav2', 301);
})->where('fallback', '.*')
    ->withoutMiddleware([
        \App\Http\Middleware\LogActivity::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ValidateCsrfToken::class,
    ])
    ->name('legacy.logo-onerror');

Route::get('/assets/storage/{path}', function (string $path) {
    $path = trim(str_replace('\\', '/', $path), '/');

    if ($path === '' || str_contains($path, '..')) {
        return response('Not found', 404, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300, must-revalidate',
        ]);
    }

    $filename = basename($path);
    $candidates = [
        public_path('storage/' . $path),
        storage_path('app/public/' . $path),
    ];

    if (str_starts_with($path, 'images/')) {
        foreach (['products', 'categories', 'promos', 'tutorials', 'profile-photos'] as $directory) {
            $candidates[] = public_path("storage/{$directory}/{$filename}");
            $candidates[] = storage_path("app/public/{$directory}/{$filename}");
        }
    }

    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            return Response::file($candidate, [
                'Cache-Control' => 'public, max-age=2592000, must-revalidate',
            ]);
        }
    }

    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (in_array($extension, ['avif', 'gif', 'jpg', 'jpeg', 'png', 'svg', 'webp'], true)) {
        return response()->noContent(204, [
            'Cache-Control' => 'public, max-age=300, must-revalidate',
        ]);
    }

    return response('Not found', 404, [
        'Content-Type' => 'text/plain; charset=UTF-8',
        'Cache-Control' => 'public, max-age=300, must-revalidate',
    ]);
})->where('path', '.*')
    ->withoutMiddleware([
        \App\Http\Middleware\LogActivity::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ValidateCsrfToken::class,
    ])
    ->name('legacy.assets.storage');

Route::get('/robots.txt', function () {
    $path = public_path('robots.txt');

    abort_unless(is_file($path), 404);

    return Response::file($path, [
        'Content-Type' => 'text/plain; charset=UTF-8',
        'Cache-Control' => 'public, max-age=3600, must-revalidate',
    ]);
});

Route::get('/sitemap.xml', function () {
    $path = public_path('sitemap.xml');

    abort_unless(is_file($path), 404);

    return Response::file($path, [
        'Content-Type' => 'application/xml; charset=UTF-8',
        'Cache-Control' => 'public, max-age=3600, must-revalidate',
    ]);
});

Route::get('/llms.txt', function () {
    $path = public_path('llms.txt');

    abort_unless(is_file($path), 404);

    return Response::file($path, [
        'Content-Type' => 'text/plain; charset=UTF-8',
        'Cache-Control' => 'public, max-age=3600, must-revalidate',
    ]);
});

Route::get('/.well-known/assetlinks.json', function () {
    $path = public_path('.well-known/assetlinks.json');

    if (is_file($path)) {
        return Response::file($path, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600, must-revalidate',
        ]);
    }

    return response('[]', 200, [
        'Content-Type' => 'application/json; charset=UTF-8',
        'Cache-Control' => 'public, max-age=3600, must-revalidate',
    ]);
})->withoutMiddleware([
    \App\Http\Middleware\LogActivity::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ValidateCsrfToken::class,
])->name('well-known.assetlinks');

Route::get('/.well-known/apple-app-site-association', function () {
    $path = public_path('.well-known/apple-app-site-association');

    if (is_file($path)) {
        return Response::file($path, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600, must-revalidate',
        ]);
    }

    return response()->json([
        'applinks' => ['apps' => [], 'details' => []],
        'webcredentials' => ['apps' => []],
        'appclips' => ['apps' => []],
    ], 200, [
        'Cache-Control' => 'public, max-age=3600, must-revalidate',
    ]);
})->withoutMiddleware([
    \App\Http\Middleware\LogActivity::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ValidateCsrfToken::class,
])->name('well-known.apple-app-site-association');

Route::get('/.well-known/openid-configuration', fn () => response()->json([
    'error' => 'not_configured',
    'message' => 'Wiboost Store is not an OpenID Connect issuer.',
], 404, [
    'Cache-Control' => 'public, max-age=3600, must-revalidate',
]))->withoutMiddleware([
    \App\Http\Middleware\LogActivity::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ValidateCsrfToken::class,
])->name('well-known.openid-configuration');

Route::get('/.well-known/security.txt', function () {
    $path = public_path('.well-known/security.txt');

    if (is_file($path)) {
        return Response::file($path, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600, must-revalidate',
        ]);
    }

    return response("Contact: https://wiboostore.com/legal/contact\nPreferred-Languages: id,en\nCanonical: https://wiboostore.com/.well-known/security.txt\n", 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
        'Cache-Control' => 'public, max-age=3600, must-revalidate',
    ]);
})->withoutMiddleware([
    \App\Http\Middleware\LogActivity::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ValidateCsrfToken::class,
])->name('well-known.security');

Route::get('/ads.txt', function () {
    $path = public_path('ads.txt');

    if (is_file($path)) {
        return Response::file($path, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600, must-revalidate',
        ]);
    }

    return response("# Wiboost Store does not publish an ads.txt inventory file.\n", 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
        'Cache-Control' => 'public, max-age=3600, must-revalidate',
    ]);
})->withoutMiddleware([
    \App\Http\Middleware\LogActivity::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ValidateCsrfToken::class,
])->name('ads');

Route::get('/meta.json', fn () => response()->json([
    'name' => 'Wiboost Store',
    'url' => route('home'),
    'login_url' => route('login'),
    'register_url' => route('register'),
], 200, [
    'Cache-Control' => 'public, max-age=3600, must-revalidate',
]))->withoutMiddleware([
    \App\Http\Middleware\LogActivity::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ValidateCsrfToken::class,
])->name('site.meta');

Route::get('/app-ads.txt', fn () => response("# Wiboost Store does not publish an app-ads.txt inventory file.\n", 200, [
    'Content-Type' => 'text/plain; charset=UTF-8',
    'Cache-Control' => 'public, max-age=3600, must-revalidate',
]))->withoutMiddleware([
    \App\Http\Middleware\LogActivity::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ValidateCsrfToken::class,
])->name('app-ads');

Route::get('/sellers.json', fn () => response()->json([
    'version' => '1.0',
    'sellers' => [],
], 200, [
    'Cache-Control' => 'public, max-age=3600, must-revalidate',
]))->withoutMiddleware([
    \App\Http\Middleware\LogActivity::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ValidateCsrfToken::class,
])->name('sellers');

Route::get('/dsrdelete.json', fn () => response()->json([
    'status' => 'not_configured',
], 200, [
    'Cache-Control' => 'public, max-age=3600, must-revalidate',
]))->withoutMiddleware([
    \App\Http\Middleware\LogActivity::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ValidateCsrfToken::class,
])->name('dsr-delete');

Route::match(['GET', 'HEAD', 'POST'], '/{probe}', fn () => response('Not found', 404, [
    'Content-Type' => 'text/plain; charset=UTF-8',
    'Cache-Control' => 'public, max-age=300, must-revalidate',
]))->where('probe', 'wp|old|blog|backup|wordpress|new')
    ->withoutMiddleware([
        \App\Http\Middleware\LogActivity::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ValidateCsrfToken::class,
    ])
    ->name('scanner.probe.ignore');

Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::post('/', fn () => redirect()->route('home', [], 303))
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('home.post');

Route::get('/admin', function () {
    $user = auth()->user();

    if ($user?->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    if ($user) {
        return redirect()->route('user.dashboard');
    }

    return redirect()->route('admin.login');
})->name('admin.entry');

Route::get('/user', function () {
    $user = auth()->user();

    if (! $user) {
        return redirect()->route('login');
    }

    return $user->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('user.dashboard');
})->name('user.entry');

Route::redirect('/client/privacy-policy', '/legal/privacy-policy', 301);
Route::redirect('/client/register', '/register', 301);
Route::redirect('/about', '/', 301);
Route::redirect('/team', '/', 301);
Route::redirect('/contact', '/legal/contact', 301);
Route::redirect('/contact-us', '/legal/contact', 301);

Route::get('/order/{slug}', [OrderController::class, 'showGuestCategory'])->name('guest.order.category');
Route::get('/legal/{page}', [FrontendController::class, 'legal'])
    ->whereIn('page', ['terms', 'privacy-policy', 'refund-policy', 'contact'])
    ->name('legal.show');
Route::get('/formulir/{form:slug}', [PublicFormController::class, 'show'])->name('public.forms.show');
Route::post('/formulir/{form:slug}', [PublicFormController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('public.forms.store');
Route::get('/formulir/{form:slug}/selesai/{submission:public_token}', [PublicFormController::class, 'thanks'])
    ->name('public.forms.thanks');

Route::get('/client/top-money', fn () => response()->noContent())
    ->name('legacy.top-money.ping');
Route::post('/client/top-money', [PaymentCallbackController::class, 'handleXoftwareCallback'])
    ->name('legacy.top-money.callback');

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
    Route::post('/history/{transaction}/logout-proof', [TransactionHistoryController::class, 'storeLogoutProof'])->name('history.logout-proof');
    Route::delete('/history/{transaction}/logout-proof', [TransactionHistoryController::class, 'destroyLogoutProof'])->name('history.logout-proof.destroy');
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
    Route::get('/membership', [UserDashboardController::class, 'membership'])->name('membership.index');
    Route::post('/membership/join', [UserDashboardController::class, 'joinMembership'])->name('membership.join');
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
| 5. ADMIN PANEL ROUTES (Super Admin & Staff)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard')->middleware('admin.access:dashboard');
    Route::get('/dashboard/weekly-chart', [AdminDashboardController::class, 'weeklyChart'])->name('dashboard.weekly_chart')->middleware('admin.access:dashboard');
    Route::get('/dashboard/export-excel', [AdminDashboardController::class, 'exportExcel'])->name('dashboard.export_excel')->middleware('admin.access:dashboard');
    Route::get('/dashboard/contact-numbers', fn () => redirect()->route('admin.reports.index'))->middleware('admin.access:reports');
    Route::post('/dashboard/contact-numbers', [AdminDashboardController::class, 'updateContactNumbers'])->name('dashboard.contact-numbers.update')->middleware('admin.access:reports');

    Route::resource('users', AdminUserController::class)->middleware('admin.access:users');
    Route::resource('categories', CategoryController::class)->middleware('admin.access:categories');
    Route::get('/resellers', [AdminUserController::class, 'legacyResellers'])->name('resellers.index')->middleware('admin.access:users');
    Route::get('/members', [AdminUserController::class, 'legacyMembers'])->name('members.index')->middleware('admin.access:users');
    Route::get('/admins', [AdminUserController::class, 'legacyAdmins'])->name('admins.index')->middleware('admin.access:users');

    // Komisi
    Route::get('/commissions', [AdminResellerController::class, 'commissions'])->name('commissions.index')->middleware('admin.access:commissions');
    Route::get('/commissions/export', [AdminResellerController::class, 'exportCommissions'])->name('commissions.export')->middleware('admin.access:commissions');
    Route::get('/withdrawals', [AdminResellerController::class, 'withdrawals'])->name('withdrawals.index')->middleware('admin.access:withdrawals');
    Route::get('/withdrawals/export-excel', [AdminResellerController::class, 'exportWithdrawals'])->name('withdrawals.export_excel')->middleware('admin.access:withdrawals');
    Route::patch('/withdrawals/{commission}', [AdminResellerController::class, 'updateWithdrawal'])->name('withdrawals.update')->middleware('admin.access:withdrawals');

    // Produk & Manajemen Stok Kredensial (Akun Premium)
    Route::post('/products/sync/digiflazz', [ProductController::class, 'syncDigiflazz'])->name('products.sync.digiflazz')->middleware('admin.access:products');
    Route::post('/products/sync/ordersosmed', [ProductController::class, 'syncOrderSosmed'])->name('products.sync.ordersosmed')->middleware('admin.access:products');
    Route::resource('products', ProductController::class)->middleware('admin.access:products');
    Route::get('/products/{product}/credentials', [CredentialController::class, 'index'])->name('credentials.index')->middleware('admin.access:products');
    Route::post('/products/{product}/credentials', [CredentialController::class, 'store'])->name('credentials.store')->middleware('admin.access:products');
    Route::delete('/credentials/{credential}', [CredentialController::class, 'destroy'])->name('credentials.destroy')->middleware('admin.access:products');
    Route::get('/durations', [AdminDurationController::class, 'index'])->name('durations.index')->middleware('admin.access:products');
    Route::get('/durations/export-excel', [AdminDurationController::class, 'exportExcel'])->name('durations.export_excel')->middleware('admin.access:products');

    // Promos, Stocks, & TUTORIAL
    Route::resource('promos', PromoController::class)->middleware('admin.access:promos');
    Route::resource('displays', DisplayController::class)->except(['show'])->middleware('admin.access:displays');
    Route::resource('forms', AdminFormController::class)->middleware('admin.access:forms');
    Route::resource('stocks', StockController::class)->middleware('admin.access:products');
    Route::resource('tutorials', TutorialController::class)->middleware('admin.access:tutorials');

    // Transaksi
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index')->middleware('admin.access:transactions');
    Route::get('/transactions/refresh-provider-statuses', fn () => redirect()->route('admin.transactions.index'))->middleware('admin.access:transactions');
    Route::post('/transactions/refresh-provider-statuses', [AdminTransactionController::class, 'refreshProviderStatuses'])->name('transactions.refresh-provider-statuses')->middleware('admin.access:transactions');
    Route::get('/transactions/export-pdf', [AdminTransactionController::class, 'exportPdf'])->name('transactions.export_pdf')->middleware('admin.access:transactions');
    Route::get('/transactions/export-excel', [AdminTransactionController::class, 'exportExcel'])->name('transactions.export_excel')->middleware('admin.access:transactions');
    Route::patch('/transactions/{id}/update', [AdminTransactionController::class, 'updateStatus'])->name('transactions.update')->middleware('admin.access:transactions');
    Route::get('/refunds', [AdminRefundController::class, 'index'])->name('refunds.index')->middleware('admin.access:refunds');
    Route::get('/refunds/export-excel', [AdminRefundController::class, 'exportExcel'])->name('refunds.export_excel')->middleware('admin.access:refunds');
    Route::get('/refunds/create', [AdminRefundController::class, 'create'])->name('refunds.create')->middleware('admin.access:refunds');
    Route::post('/refunds', [AdminRefundController::class, 'store'])->name('refunds.store')->middleware('admin.access:refunds');
    Route::get('/refunds/{refund}/edit', [AdminRefundController::class, 'edit'])->name('refunds.edit')->middleware('admin.access:refunds');
    Route::patch('/refunds/{refund}', [AdminRefundController::class, 'update'])->name('refunds.update')->middleware('admin.access:refunds');
    Route::delete('/refunds/{refund}', [AdminRefundController::class, 'destroy'])->name('refunds.destroy')->middleware('admin.access:refunds');

    // Deposit Management
    Route::get('/deposits', [AdminDepositController::class, 'index'])->name('deposits.index')->middleware('admin.access:deposits');
    Route::get('/deposits/export-excel', [AdminDepositController::class, 'exportExcel'])->name('deposits.export_excel')->middleware('admin.access:deposits');
    Route::patch('/deposits/{id}', [AdminDepositController::class, 'update'])->name('deposits.update')->middleware('admin.access:deposits');
    Route::get('/reports', [AdminAdminReportController::class, 'index'])->name('reports.index')->middleware('admin.access:reports');
    Route::get('/reports/export-excel', [AdminAdminReportController::class, 'exportExcel'])->name('reports.export_excel')->middleware('admin.access:reports');
    Route::patch('/reports/{report}', [AdminAdminReportController::class, 'update'])->name('reports.update')->middleware('admin.access:reports');

    // Log Aktivitas (khusus Super Admin)
    Route::get('/logs', [AdminActivityLogController::class, 'index'])->name('logs.index')->middleware('admin.access:logs');

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
