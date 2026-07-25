<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use App\Services\OrderFulfillmentService;
use App\Services\XoftwarePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Support\WiboostCatalog;
use App\Support\WiboostInputRules;
use Throwable;

class OrderController extends Controller
{
    public function showGuestCategory($slug)
    {
        return $this->showCategory($slug, true);
    }

    public function showCategory($slug, bool $isGuestCatalog = false)
    {
        abort_if(in_array($slug, WiboostCatalog::removedCategorySlugs(), true), 404);

        $isGuestCatalog = $isGuestCatalog || ! Auth::check();

        $category = Category::with([
            'parent',
            'children' => function ($query) {
                $query->withCount([
                    'children',
                    'products as active_products_count' => function ($productQuery) {
                        $productQuery->where('is_active', true)
                            ->whereIn('process_type', WiboostCatalog::activeProductProcessTypes());
                    },
                ])->orderBy('name');
            },
        ])->where('slug', $slug)->firstOrFail();

        $rootCategorySlug = $this->resolveRootCategorySlug($category);
        $rootCategoryName = $this->resolveRootCategoryName($category);
        $isSuntikSosmedOrder = $this->isSuntikSosmedCatalogCategory($category, $rootCategorySlug);

        if ($category->children->count() > 0) {
            return view('user.order_sub', compact('category', 'isGuestCatalog'));
        }

        if (! Auth::check() && $this->guestSuntikSosmedCategoryRequiresLogin($category, $rootCategorySlug)) {
            return redirect()->route('login');
        }

        $products = $this->loadCategoryProducts($category, $rootCategorySlug);
        $this->cacheRootCategoryForProducts($products, $rootCategorySlug, $rootCategoryName);
        $isTopUpGameOrder = $rootCategorySlug === 'top-up-game';
        $isCompactServiceCardOrder = in_array($rootCategorySlug, ['suntik-sosmed', 'top-up-game', 'kuota-murah', 'paket-data', 'aplikasi-premium'], true);

        if ($isSuntikSosmedOrder) {
            $products = $this->sortSuntikSosmedProducts($products);
        } else {
            $products = $this->sortProductsByDisplayPrice($products);
        }

        $category->setRelation('products', $products);

        return view('user.order', compact('category', 'products', 'rootCategorySlug', 'isSuntikSosmedOrder', 'isTopUpGameOrder', 'isCompactServiceCardOrder', 'isGuestCatalog'));
    }

    public function processCheckout(
        Request $request,
        XoftwarePaymentService $paymentService,
        OrderFulfillmentService $orderFulfillmentService
    ) {
        $product = Product::findOrFail($request->product_id);
        abort_if(
            ! $product->is_active || ! in_array($product->process_type, WiboostCatalog::activeProductProcessTypes(), true),
            404
        );

        $user = Auth::user();

        if (! WiboostInputRules::validName($user?->name)) {
            return redirect()
                ->route('profile.edit')
                ->with('error', WiboostInputRules::invalidNameWarning());
        }

        $checkoutFields = $product->checkout_fields;

        $rules = [
            'product_id' => 'required|exists:products,id',
            'payment_method' => 'required|string|in:wallet,manual,tripay',
        ];

        $attributes = [
            'product_id' => 'produk',
            'payment_method' => 'metode pembayaran',
        ];

        if (blank($user?->whatsapp)) {
            $rules['checkout_whatsapp'] = WiboostInputRules::numberRules(20);
            $attributes['checkout_whatsapp'] = 'nomor WhatsApp';
        }

        foreach ($checkoutFields as $field) {
            $rules[$field['name']] = $field['rules'] ?? ['required', 'string', 'min:3', 'max:255'];
            $attributes[$field['name']] = strtolower($field['label']);
        }

        $validated = $request->validate($rules, WiboostInputRules::validationMessages(), $attributes);

        if (blank($user?->whatsapp) && filled($validated['checkout_whatsapp'] ?? null)) {
            $user->forceFill([
                'whatsapp' => trim((string) $validated['checkout_whatsapp']),
            ])->save();
            $user->refresh();
        }

        unset($validated['checkout_whatsapp']);
        $targetData = (string) ($product->summarizeOrderInput($validated) ?? '');
        $builtOrderInputData = $product->buildOrderInputData($validated);
        $orderInputData = is_array($builtOrderInputData) ? $builtOrderInputData : [];
        $checkoutAmount = $product->calculateCheckoutAmount($validated);

        if ($validated['payment_method'] === 'tripay' && (float) $checkoutAmount < 1000) {
            return back()
                ->withInput()
                ->with('error', 'QRIS hanya tersedia untuk pesanan minimal Rp 1.000');
        }

        $checkoutFingerprint = sha1($user->id . '|' . $product->id . '|' . $checkoutAmount . '|' . $targetData . '|' . json_encode($orderInputData));

        try {
            if ($validated['payment_method'] === 'wallet') {
                $checkoutNonce = trim((string) $request->input('checkout_nonce', Str::uuid()->toString()));
                $walletLockKey = 'wallet-checkout:' . sha1($checkoutFingerprint . '|' . $checkoutNonce);

                if (! Cache::add($walletLockKey, true, now()->addSeconds(10))) {
                    usleep(500000);

                    $existingWalletTransaction = $this->findRecentWalletTransaction(
                        $user,
                        $product,
                        $checkoutAmount,
                        $targetData,
                        $orderInputData
                    );

                    if ($existingWalletTransaction) {
                        return redirect()->route('user.history', ['auto_open' => $existingWalletTransaction->id])
                            ->with('new_trx_id', $existingWalletTransaction->id);
                    }

                    return redirect()->route('user.history');
                }

                $transaction = DB::transaction(function () use ($user, $product, $targetData, $orderInputData, $checkoutAmount) {
                    $freshUser = User::query()->lockForUpdate()->findOrFail($user->id);

                    if ((float) $freshUser->balance < (float) $checkoutAmount) {
                        throw new \RuntimeException('Saldo Wiboost tidak mencukupi. Silakan top up terlebih dahulu.');
                    }

                    $freshUser->decrement('balance', (float) $checkoutAmount);

                    $transaction = Transaction::create([
                        'invoice_number' => 'WIB-' . strtoupper(Str::random(12)),
                        'user_id' => $freshUser->id,
                        'product_id' => $product->id,
                        'product_name_snapshot' => $product->name,
                        'amount' => $checkoutAmount,
                        'target_data' => $targetData,
                        'order_input_data' => $orderInputData,
                        'payment_status' => 'paid',
                        'order_status' => 'pending',
                        'payment_method' => 'wallet',
                    ]);

                    WalletHistory::create([
                        'user_id' => $freshUser->id,
                        'type' => 'purchase',
                        'amount' => $checkoutAmount,
                        'description' => 'Pembelian Produk: ' . $product->name,
                        'invoice_number' => $transaction->invoice_number,
                    ]);

                    return $transaction;
                });

                $orderFulfillmentService->handlePaidTransaction($transaction->fresh(['product', 'user']));

                return redirect()->route('user.history', ['auto_open' => $transaction->id])
                    ->with('success', 'Pesanan berhasil dibuat.')
                    ->with('new_trx_id', $transaction->id);
            }

            $existingTransaction = $this->findReusableTripayTransaction(
                $user,
                $product,
                $checkoutAmount,
                $targetData,
                $orderInputData
            );

            if ($existingTransaction) {
                return redirect()->away((string) $existingTransaction->snap_token);
            }

            $lockKey = 'tripay-checkout:' . $checkoutFingerprint;

            if (! Cache::add($lockKey, true, now()->addSeconds(20))) {
                usleep(500000);
                $existingTransaction = $this->findReusableTripayTransaction(
                    $user,
                    $product,
                    $checkoutAmount,
                    $targetData,
                    $orderInputData
                );

                if ($existingTransaction) {
                    return redirect()->away((string) $existingTransaction->snap_token);
                }

                return back()
                    ->withInput()
                    ->with('error', 'Tagihan QRIS sedang disiapkan. Tunggu sebentar lalu cek riwayat pesanan.');
            }

            $transaction = Transaction::create([
                'invoice_number' => 'WIB-' . strtoupper(Str::random(12)),
                'user_id' => $user->id,
                'product_id' => $product->id,
                'product_name_snapshot' => $product->name,
                'amount' => $checkoutAmount,
                'target_data' => $targetData,
                'order_input_data' => $orderInputData,
                'payment_status' => 'unpaid',
                'order_status' => 'pending',
                'payment_method' => 'tripay',
            ]);

            try {
                $payment = $paymentService->createTransactionPayment($transaction->fresh(['product', 'user']), $user);
            } catch (Throwable $paymentException) {
                $transaction->update([
                    'payment_status' => 'failed',
                    'order_status' => 'failed',
                    'target_notes' => $paymentException->getMessage(),
                    'response_data' => array_merge($transaction->response_data ?? [], [
                        'payment_gateway' => 'xoftware',
                        'payment_create_failed_at' => now()->toDateTimeString(),
                        'payment_create_error' => $paymentException->getMessage(),
                    ]),
                ]);

                throw $paymentException;
            }

            $transaction->update([
                'snap_token' => $payment['checkout_url'],
                'response_data' => array_merge($transaction->response_data ?? [], [
                    'payment_gateway' => 'xoftware',
                    'xoftware_reference' => $payment['reference'] ?? null,
                    'xoftware_create_response' => $payment['raw'] ?? null,
                    'tripay_reference' => $payment['reference'] ?? null,
                    'tripay_create_response' => $payment['raw'] ?? null,
                ]),
            ]);

            return redirect()->away((string) $payment['checkout_url']);
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }

    public function cancelTripayCheckout(Transaction $transaction)
    {
        abort_if((int) $transaction->user_id !== (int) Auth::id(), 403);

        if ($transaction->payment_method === 'tripay' && in_array($transaction->payment_status, ['unpaid', 'pending'], true)) {
            $transaction->update([
                'payment_status' => 'failed',
                'order_status' => 'failed',
                'target_notes' => 'Pembayaran QRIS dibatalkan oleh pengguna.',
            ]);

            return redirect()
                ->route('user.history', ['auto_open' => $transaction->id]);
        }

        return redirect()->route('user.history', ['auto_open' => $transaction->id]);
    }

    public function ordersosmedServiceMeta(Product $product)
    {
        abort_if(
            ! $product->is_active || ! in_array($product->process_type, WiboostCatalog::activeProductProcessTypes(), true),
            404
        );

        $product->loadMissing('category.parent.parent.parent');
        abort_if(! $product->category, 404);

        $rootCategorySlug = $this->resolveRootCategorySlug($product->category);
        $rootCategoryName = $this->resolveRootCategoryName($product->category);

        abort_if($rootCategorySlug !== 'suntik-sosmed' || ! $product->is_ordersosmed_service, 404);

        $product->cachePrimaryCategory($rootCategorySlug, $rootCategoryName);
        $cacheKey = implode(':', [
            'ordersosmed-service-meta',
            $product->id,
            optional($product->updated_at)->timestamp ?? '0',
            md5((string) Product::currentDiscountLabel()),
            Auth::user()?->isMember() ? 'member' : 'regular',
        ]);

        $payload = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($product) {
            $this->attachSuccessfulAverageTime($product);

            return $this->ordersosmedProductPayload($product);
        });

        return response()->json($payload);
    }

    public function ordersosmedCategoryServices(Category $category)
    {
        $category->loadMissing('parent.parent.parent');

        abort_if($this->resolveRootCategorySlug($category) !== 'suntik-sosmed', 404);

        $services = Product::query()
            ->select(['id', 'name'])
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->whereIn('process_type', WiboostCatalog::activeProductProcessTypes())
            ->where(function ($query) {
                $query->where('provider_source', 'ordersosmed')
                    ->orWhere('provider_id', 'ordersosmed');
            })
            ->orderByRaw('CASE WHEN COALESCE(provider_quantity, 0) > 0 THEN (price / provider_quantity) * 1000 ELSE price END ASC')
            ->orderBy('price', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'services' => $services
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                ])
                ->values(),
        ]);
    }

    protected function resolveRootCategorySlug(Category $category): ?string
    {
        $category->loadMissing('parent.parent.parent');

        while ($category->parent) {
            $category = $category->parent;
        }

        return $category->slug;
    }

    protected function resolveRootCategoryName(Category $category): ?string
    {
        $category->loadMissing('parent.parent.parent');

        while ($category->parent) {
            $category = $category->parent;
        }

        return $category->name;
    }

    protected function guestFinalOrderCategoryRequiresLogin(Category $category, ?string $rootCategorySlug = null): bool
    {
        $category->loadMissing('parent.parent.parent.parent.parent');

        if ($category->children->isNotEmpty()) {
            return false;
        }

        return true;
    }

    protected function guestSuntikSosmedCategoryRequiresLogin(Category $category, ?string $rootCategorySlug = null): bool
    {
        $category->loadMissing('parent.parent.parent.parent.parent');

        if ($category->children->isNotEmpty()) {
            return false;
        }

        if ($this->isSuntikSosmedCatalogCategory($category, $rootCategorySlug)) {
            return true;
        }

        return Product::query()
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->whereIn('process_type', WiboostCatalog::activeProductProcessTypes())
            ->where(function ($query) {
                $query->where('provider_source', 'ordersosmed')
                    ->orWhere('provider_id', 'ordersosmed');
            })
            ->exists();
    }

    protected function isSuntikSosmedCatalogCategory(Category $category, ?string $rootCategorySlug = null): bool
    {
        if ($rootCategorySlug === 'suntik-sosmed') {
            return true;
        }

        $category->loadMissing('parent.parent.parent.parent.parent');

        $segments = [];
        $current = $category;

        while ($current) {
            $segments[] = Str::lower((string) $current->slug);
            $segments[] = Str::lower((string) $current->name);
            $current = $current->parent;
        }

        $text = implode(' ', array_filter($segments));

        if (preg_match('/\bsosmed-|suntik\s+sosmed\b/u', $text)) {
            return true;
        }

        $platformPattern = '/\b(instagram|tiktok|youtube|telegram|twitter|facebook|tweet|x-twitter)\b/u';
        $servicePattern = '/\b(followers?|likes?|views?|viewers?|subscribers?|comments?|komentar|share|save|members?|traffic|visit|rating|review)\b/u';

        return (bool) (preg_match($platformPattern, $text) && preg_match($servicePattern, $text));
    }

    protected function cacheRootCategoryForProducts($products, ?string $rootCategorySlug, ?string $rootCategoryName): void
    {
        $products->each(function (Product $product) use ($rootCategorySlug, $rootCategoryName) {
            $product->cachePrimaryCategory($rootCategorySlug, $rootCategoryName);
        });
    }

    protected function loadCategoryProducts(Category $category, ?string $rootCategorySlug = null)
    {
        $query = Product::query()
            ->select('products.*')
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->whereIn('process_type', WiboostCatalog::activeProductProcessTypes());

        if ($rootCategorySlug === 'aplikasi-premium') {
            $query->selectSub(function ($stockQuery) {
                $stockQuery->from('product_credentials')
                    ->selectRaw('COALESCE(SUM(max_usage - current_usage), 0)')
                    ->whereColumn('product_credentials.product_id', 'products.id')
                    ->where('is_active', true);
            }, 'available_stock_cache');
        }

        if ($rootCategorySlug === 'suntik-sosmed') {
            $query->select([
                'products.id',
                'products.category_id',
                'products.process_type',
                'products.provider_id',
                'products.provider_product_id',
                'products.provider_source',
                'products.provider_quantity',
                'products.name',
                'products.price',
                'products.status',
                'products.is_active',
                'products.updated_at',
            ]);
            $query->orderByRaw('CASE WHEN COALESCE(provider_quantity, 0) > 0 THEN (price / provider_quantity) * 1000 ELSE price END ASC');
            $query->orderBy('price', 'asc');
        } else {
            $query->with('category.parent.parent.parent');
            $query->orderBy('price', 'asc');
        }

        return $query->orderBy('name', 'asc')->get();
    }

    protected function ordersosmedProductPayload(Product $product): array
    {
        $plainDescription = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $product->description)));

        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $plainDescription !== '' ? $plainDescription : '-',
            'min' => (int) $product->ordersosmed_min_quantity,
            'max' => (int) $product->ordersosmed_max_quantity,
            'unit_price' => (float) $product->ordersosmed_unit_price,
            'price_per_1000' => (float) $product->ordersosmed_price_per_thousand,
            'original_unit_price' => (float) $product->original_ordersosmed_unit_price,
            'original_price_per_1000' => (float) $product->original_ordersosmed_price_per_thousand,
            'discount_label' => $product->active_discount_label,
            'average_time' => $product->ordersosmed_average_time,
        ];
    }

    protected function attachSuccessfulAverageTime(Product $product): void
    {
        $cacheKey = 'ordersosmed_success_average_time:product:' . $product->id . ':v2';

        $average = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($product) {
            $row = Transaction::query()
                ->selectRaw('COUNT(*) as success_order_count')
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as average_seconds')
                ->where('product_id', $product->id)
                ->where('order_status', 'success')
                ->whereNotNull('created_at')
                ->whereNotNull('updated_at')
                ->whereColumn('updated_at', '>=', 'created_at')
                ->first();

            return [
                'success_order_count' => (int) ($row->success_order_count ?? 0),
                'average_seconds' => $row && $row->average_seconds !== null ? (float) $row->average_seconds : null,
            ];
        });

        $product->setAttribute(
            'ordersosmed_success_average_seconds_cache',
            is_numeric($average['average_seconds'] ?? null) ? (int) round((float) $average['average_seconds']) : null
        );
        $product->setAttribute('ordersosmed_success_order_count_cache', (int) ($average['success_order_count'] ?? 0));
    }

    protected function attachSuccessfulAverageTimes(Category $category, $products): void
    {
        $productIds = $products->pluck('id')->filter()->values();

        if ($productIds->isEmpty()) {
            return;
        }

        $cacheKey = 'ordersosmed_success_average_times:category:' . $category->id . ':v2';

        $averages = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($productIds) {
            return Transaction::query()
                ->select('product_id')
                ->selectRaw('COUNT(*) as success_order_count')
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as average_seconds')
                ->whereIn('product_id', $productIds)
                ->where('order_status', 'success')
                ->whereNotNull('created_at')
                ->whereNotNull('updated_at')
                ->whereColumn('updated_at', '>=', 'created_at')
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id')
                ->map(fn ($average) => [
                    'success_order_count' => (int) $average->success_order_count,
                    'average_seconds' => (float) $average->average_seconds,
                ])
                ->all();
        });

        $products->each(function (Product $product) use ($averages) {
            $average = $averages[$product->id] ?? null;

            $product->setAttribute('ordersosmed_success_average_seconds_cache', $average ? (int) round((float) $average['average_seconds']) : null);
            $product->setAttribute('ordersosmed_success_order_count_cache', $average ? (int) $average['success_order_count'] : 0);
        });
    }

    protected function findReusableTripayTransaction(
        User $user,
        Product $product,
        $checkoutAmount,
        string $targetData,
        ?array $orderInputData
    ): ?Transaction {
        $orderInputData = $orderInputData ?? [];
        $expiresInMinutes = max(1, (int) config('xoftware.expired_minutes', 60));

        return Transaction::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->whereIn('payment_status', ['unpaid', 'pending'])
            ->where('payment_method', 'tripay')
            ->where('amount', $checkoutAmount)
            ->where('target_data', $targetData)
            ->whereNotNull('snap_token')
            ->where('created_at', '>=', now()->subMinutes($expiresInMinutes))
            ->latest()
            ->get()
            ->first(fn (Transaction $transaction) => $transaction->order_input_data == $orderInputData);
    }

    protected function findRecentWalletTransaction(
        User $user,
        Product $product,
        $checkoutAmount,
        string $targetData,
        ?array $orderInputData
    ): ?Transaction {
        $orderInputData = $orderInputData ?? [];

        return Transaction::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('payment_method', 'wallet')
            ->where('amount', $checkoutAmount)
            ->where('target_data', $targetData)
            ->where('created_at', '>=', now()->subMinutes(3))
            ->latest()
            ->get()
            ->first(fn (Transaction $transaction) => $transaction->order_input_data == $orderInputData);
    }

    protected function sortSuntikSosmedProducts($products)
    {
        return $products
            ->sort(function (Product $left, Product $right) {
                $leftPrice = $this->suntikSosmedPricePerThousand($left);
                $rightPrice = $this->suntikSosmedPricePerThousand($right);

                if ($leftPrice === $rightPrice) {
                    $priceCompare = (float) $left->price <=> (float) $right->price;

                    return $priceCompare !== 0 ? $priceCompare : strnatcasecmp($left->name, $right->name);
                }

                return $leftPrice <=> $rightPrice;
            })
            ->values();
    }

    protected function suntikSosmedPricePerThousand(Product $product): float
    {
        $pricePerThousand = (float) $product->ordersosmed_price_per_thousand;

        if ($pricePerThousand > 0) {
            return $pricePerThousand;
        }

        $quantity = max(1, (int) ($product->provider_quantity ?: 1));

        return ((float) $product->selling_price / $quantity) * 1000;
    }

    protected function sortProductsByDisplayPrice($products)
    {
        return $products
            ->sort(function (Product $left, Product $right) {
                $leftPrice = (float) $left->selling_price;
                $rightPrice = (float) $right->selling_price;

                if ($leftPrice === $rightPrice) {
                    return strnatcasecmp($left->name, $right->name);
                }

                return $leftPrice <=> $rightPrice;
            })
            ->values();
    }
}
