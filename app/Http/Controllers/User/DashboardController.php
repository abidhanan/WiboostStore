<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Promo;
use App\Models\Tutorial;
use App\Services\TripayDepositSyncService;
use App\Services\XoftwarePaymentService;
use App\Support\WiboostCatalog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(TripayDepositSyncService $depositSyncService)
    {
        $depositSyncService->syncForUser((int) Auth::id());

        $user = Auth::user()->fresh();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $catalogSlugs = WiboostCatalog::coreCategorySlugs();
        $categoryOrderSql = WiboostCatalog::categoryOrderSql();

        $categories = Category::whereNull('parent_id')
            ->whereIn('slug', $catalogSlugs)
            ->withCount(['children', 'products'])
            ->orderByRaw($categoryOrderSql)
            ->get();

        $promos = Promo::where('is_active', true)->oldest()->get();

        $totalThisMonth = Transaction::where('user_id', $user->id)
                            ->where('payment_status', 'paid')
                            ->where('order_status', '!=', 'failed')
                            ->whereMonth('created_at', $currentMonth)
                            ->whereYear('created_at', $currentYear)
                            ->count();

        $totalSpent = Transaction::where('user_id', $user->id)
                            ->where('payment_status', 'paid')
                            ->where('order_status', '!=', 'failed')
                            ->whereMonth('created_at', $currentMonth)
                            ->whereYear('created_at', $currentYear)
                            ->sum('amount');

        return view('user.dashboard', compact(
            'categories', 
            'promos', 
            'totalThisMonth', 
            'totalSpent'
        ));
    }

    public function tutorials()
    {
        $tutorials = Tutorial::where('is_active', true)->latest()->get();
        $tutorialGroups = $this->buildTutorialGroups($tutorials);

        return view('user.tutorials', compact('tutorials', 'tutorialGroups'));
    }

    public function membership(TripayDepositSyncService $depositSyncService)
    {
        $depositSyncService->syncForUser((int) Auth::id());

        $user = Auth::user();
        $user?->syncExpiredMembershipRole();
        $user = $user?->fresh();
        $isMember = $user?->isMember() ?? false;
        $membershipExpiresAt = $user?->membership_expires_at;
        $remainingTotalSeconds = $isMember && $membershipExpiresAt
            ? (int) floor(max(0, now()->diffInSeconds($membershipExpiresAt, false)))
            : 0;
        $remainingDays = intdiv($remainingTotalSeconds, 86400);
        $remainingHours = intdiv($remainingTotalSeconds % 86400, 3600);
        $remainingMinutes = intdiv($remainingTotalSeconds % 3600, 60);
        $remainingSeconds = $remainingTotalSeconds % 60;
        $membershipTickerProducts = $this->membershipTickerProducts();

        return view('user.membership', compact(
            'user',
            'isMember',
            'membershipExpiresAt',
            'remainingTotalSeconds',
            'remainingDays',
            'remainingHours',
            'remainingMinutes',
            'remainingSeconds',
            'membershipTickerProducts'
        ));
    }

    public function joinMembership(Request $request, XoftwarePaymentService $paymentService)
    {
        $user = $request->user();
        $user?->syncExpiredMembershipRole();
        $user = $user?->fresh();
        $amount = 30000;
        $lockKey = 'membership-payment:' . $user->id;

        try {
            app(TripayDepositSyncService::class)->syncForUser((int) $user->id);

            if (! Cache::add($lockKey, true, now()->addSeconds(20))) {
                usleep(500000);
                $transaction = $this->findReusableMembershipTransaction((int) $user->id);

                if ($transaction) {
                    return redirect()->route('user.qris.show', $transaction->invoice_number);
                }

                return back()->with('error', 'Tagihan QRIS membership sedang disiapkan. Tunggu sebentar lalu coba lagi.');
            }

            $transaction = $this->findReusableMembershipTransaction((int) $user->id) ?: Transaction::create([
                'user_id' => $user->id,
                'invoice_number' => 'MEM-' . strtoupper(Str::random(10)),
                'product_id' => null,
                'amount' => $amount,
                'target_data' => 'Membership Wiboost 30 Hari',
                'order_input_data' => [
                    'fields' => [
                        [
                            'name' => 'membership_period',
                            'label' => 'Masa Membership',
                            'value' => '30 Hari',
                            'target_summary' => true,
                        ],
                    ],
                ],
                'payment_status' => 'unpaid',
                'order_status' => 'pending',
                'payment_method' => 'tripay',
                'response_data' => [
                    'purpose' => 'membership',
                    'membership_days' => 30,
                ],
            ]);

            if (blank(data_get($transaction->response_data, 'xoftware_create_response'))) {
                $payment = $paymentService->createTransactionPayment($transaction->fresh('user'), $user);

                $transaction->update([
                    'snap_token' => route('user.qris.show', $transaction->invoice_number),
                    'payment_method' => $payment['payment_method'] ?? 'tripay',
                    'response_data' => array_merge($transaction->response_data ?? [], [
                        'purpose' => 'membership',
                        'membership_days' => 30,
                        'payment_gateway' => 'xoftware',
                        'xoftware_reference' => $payment['reference'] ?? null,
                        'tripay_reference' => $payment['reference'] ?? null,
                        'xoftware_create_response' => $payment['raw'] ?? null,
                    ]),
                ]);
            }

            return redirect()->route('user.qris.show', $transaction->invoice_number);
        } catch (\Throwable $exception) {
            return back()->with('error', 'Gagal menyiapkan pembayaran membership: ' . $exception->getMessage());
        } finally {
            Cache::forget($lockKey);
        }
    }

    protected function findReusableMembershipTransaction(int $userId): ?Transaction
    {
        $expiresInMinutes = max(1, (int) config('xoftware.expired_minutes', 60));

        return Transaction::query()
            ->where('user_id', $userId)
            ->whereIn('payment_status', ['unpaid', 'pending'])
            ->where('amount', 30000)
            ->where('invoice_number', 'like', 'MEM-%')
            ->where('created_at', '>=', now()->subMinutes($expiresInMinutes))
            ->latest()
            ->get()
            ->first(function (Transaction $transaction) {
                $hasPreparedQris = filled($transaction->snap_token)
                    || filled(data_get($transaction->response_data, 'xoftware_create_response'))
                    || filled(data_get($transaction->response_data, 'data.qris'));

                return $hasPreparedQris && $transaction->tripay_admin_fee > 0;
            });
    }

    public function tutorialCategory(Request $request)
    {
        $mainCategory = trim((string) $request->query('main', ''));
        $subCategory = trim((string) $request->query('sub', ''));

        abort_if($mainCategory === '' || $subCategory === '', 404);

        $tutorials = Tutorial::query()
            ->where('is_active', true)
            ->where(function ($query) use ($mainCategory) {
                $query->where('main_category', $mainCategory)
                    ->orWhere(function ($fallbackQuery) use ($mainCategory) {
                        $fallbackQuery->whereNull('main_category')
                            ->where('category', $mainCategory);
                    });
            })
            ->where(function ($query) use ($subCategory) {
                $query->where('sub_category', $subCategory)
                    ->orWhere(function ($fallbackQuery) use ($subCategory) {
                        $fallbackQuery->whereNull('sub_category')
                            ->where('category', $subCategory);
                    });
            })
            ->latest()
            ->get();

        abort_if($tutorials->isEmpty(), 404);

        $firstTutorial = $tutorials->first();
        $subCategoryImage = $firstTutorial?->sub_category_image ?: $firstTutorial?->image;

        return view('user.tutorial-category', compact(
            'mainCategory',
            'subCategory',
            'subCategoryImage',
            'tutorials'
        ));
    }

    protected function buildTutorialGroups($tutorials)
    {
        $tutorialMainOrder = [
            'suntik sosmed' => 0,
            'top up game' => 1,
            'paket data' => 2,
            'kuota murah' => 2,
            'aplikasi premium' => 3,
        ];

        return $tutorials
            ->groupBy(fn (Tutorial $tutorial) => $tutorial->main_category ?: ($tutorial->category ?: 'Umum'))
            ->map(function ($mainTutorials, $mainCategory) {
                $directTutorials = $mainTutorials
                    ->filter(fn (Tutorial $tutorial) => blank($tutorial->sub_category))
                    ->values();
                $subcategoryTutorials = $mainTutorials
                    ->filter(fn (Tutorial $tutorial) => filled($tutorial->sub_category));

                return [
                    'name' => $mainCategory,
                    'tutorials' => $directTutorials,
                    'subcategories' => $subcategoryTutorials
                        ->groupBy(fn (Tutorial $tutorial) => $tutorial->sub_category)
                        ->map(function ($subTutorials, $subCategory) {
                            $firstTutorial = $subTutorials->first();

                            return [
                                'name' => $subCategory,
                                'image' => $firstTutorial?->sub_category_image ?: $firstTutorial?->image,
                                'tutorials' => $subTutorials->values(),
                            ];
                        })
                        ->values(),
                ];
            })
            ->sortBy(fn (array $group) => $tutorialMainOrder[strtolower((string) $group['name'])] ?? 99)
            ->values();
    }

    protected function membershipTickerProducts()
    {
        return Product::query()
            ->with('category.parent.parent.parent')
            ->where('is_active', true)
            ->where('status', 'active')
            ->whereIn('process_type', WiboostCatalog::activeProductProcessTypes())
            ->latest('updated_at')
            ->limit(120)
            ->get()
            ->map(function (Product $product) {
                $rootCategory = $product->category;

                while ($rootCategory?->parent) {
                    $rootCategory = $rootCategory->parent;
                }

                return [
                    'name' => $product->name,
                    'price' => (int) round($product->membership_selling_price),
                    'root_slug' => $rootCategory?->slug ?: '',
                    'root_name' => $rootCategory?->name ?: '',
                ];
            })
            ->filter(fn (array $item) => $item['price'] > 0)
            ->sort(function (array $first, array $second) {
                $priority = ($first['root_slug'] === 'aplikasi-premium' ? 0 : 1) <=> ($second['root_slug'] === 'aplikasi-premium' ? 0 : 1);

                if ($priority !== 0) {
                    return $priority;
                }

                $categoryOrder = strnatcasecmp($first['root_name'], $second['root_name']);

                return $categoryOrder !== 0 ? $categoryOrder : strnatcasecmp($first['name'], $second['name']);
            })
            ->take(32)
            ->values();
    }
}
