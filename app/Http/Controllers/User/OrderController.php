<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use App\Services\OrderFulfillmentService;
use App\Services\TripayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Support\WiboostCatalog;
use Throwable;

class OrderController extends Controller
{
    public function showCategory($slug)
    {
        abort_if(in_array($slug, WiboostCatalog::removedCategorySlugs(), true), 404);

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
            'products' => function ($query) {
                $query->where('is_active', true)
                    ->whereIn('process_type', WiboostCatalog::activeProductProcessTypes())
                    ->with('category.parent.parent.parent')
                    ->orderBy('price', 'asc')
                    ->orderBy('name', 'asc');
            },
        ])->where('slug', $slug)->firstOrFail();

        if ($category->children->count() > 0) {
            return view('user.order_sub', compact('category'));
        }

        $products = $category->products;
        $rootCategorySlug = $this->resolveRootCategorySlug($category);
        $isSuntikSosmedOrder = $rootCategorySlug === 'suntik-sosmed'
            && $products->contains(fn (Product $product) => $product->is_ordersosmed_service);
        $isTopUpGameOrder = $rootCategorySlug === 'top-up-game';
        $isCompactServiceCardOrder = in_array($rootCategorySlug, ['top-up-game', 'kuota-murah', 'aplikasi-premium'], true);

        if ($isSuntikSosmedOrder) {
            $products = $this->sortSuntikSosmedProducts($products);
            $category->setRelation('products', $products);
            $this->attachSuccessfulAverageTimes($products);
        }

        return view('user.order', compact('category', 'products', 'isSuntikSosmedOrder', 'isTopUpGameOrder', 'isCompactServiceCardOrder'));
    }

    public function processCheckout(
        Request $request,
        TripayService $tripayService,
        OrderFulfillmentService $orderFulfillmentService
    ) {
        $product = Product::findOrFail($request->product_id);
        abort_if(
            ! $product->is_active || ! in_array($product->process_type, WiboostCatalog::activeProductProcessTypes(), true),
            404
        );

        $user = Auth::user();
        $checkoutFields = $product->checkout_fields;

        $rules = [
            'product_id' => 'required|exists:products,id',
            'payment_method' => 'required|string|in:wallet,manual,tripay',
        ];

        $attributes = [
            'product_id' => 'produk',
            'payment_method' => 'metode pembayaran',
        ];

        foreach ($checkoutFields as $field) {
            $rules[$field['name']] = $field['rules'] ?? ['required', 'string', 'min:3', 'max:255'];
            $attributes[$field['name']] = strtolower($field['label']);
        }

        $validated = $request->validate($rules, [], $attributes);
        $targetData = (string) ($product->summarizeOrderInput($validated) ?? '');
        $builtOrderInputData = $product->buildOrderInputData($validated);
        $orderInputData = is_array($builtOrderInputData) ? $builtOrderInputData : [];
        $checkoutAmount = $product->calculateCheckoutAmount($validated);

        try {
            if ($validated['payment_method'] === 'wallet') {
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
                    ->with('success', 'Pesanan berhasil dibuat dan langsung diproses.')
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
                return view('user.checkout', [
                    'transaction' => $existingTransaction->fresh(['product', 'user']),
                    'paymentUrl' => $existingTransaction->snap_token,
                ]);
            }

            $lockKey = 'tripay-checkout:' . sha1($user->id . '|' . $product->id . '|' . $checkoutAmount . '|' . $targetData . '|' . json_encode($orderInputData));

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
                    return view('user.checkout', [
                        'transaction' => $existingTransaction->fresh(['product', 'user']),
                        'paymentUrl' => $existingTransaction->snap_token,
                    ]);
                }

                return back()
                    ->withInput()
                    ->with('error', 'Tagihan QRIS sedang disiapkan. Tunggu sebentar lalu cek riwayat pesanan.');
            }

            $transaction = Transaction::create([
                'invoice_number' => 'WIB-' . strtoupper(Str::random(12)),
                'user_id' => $user->id,
                'product_id' => $product->id,
                'amount' => $checkoutAmount,
                'target_data' => $targetData,
                'order_input_data' => $orderInputData,
                'payment_status' => 'unpaid',
                'order_status' => 'pending',
                'payment_method' => 'tripay',
            ]);

            $payment = $tripayService->createTransactionPayment($transaction->fresh(['product', 'user']), $user);
            $transaction->update([
                'snap_token' => $payment['checkout_url'],
                'response_data' => array_merge($transaction->response_data ?? [], [
                    'payment_gateway' => 'tripay',
                    'tripay_reference' => $payment['reference'] ?? null,
                    'tripay_create_response' => $payment['raw'] ?? null,
                ]),
            ]);

            return view('user.checkout', [
                'transaction' => $transaction->fresh(['product', 'user']),
                'paymentUrl' => $payment['checkout_url'],
            ]);
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
            $transaction->delete();

            return redirect()
                ->route('user.dashboard');
        }

        return redirect()->route('user.history', ['auto_open' => $transaction->id]);
    }

    protected function resolveRootCategorySlug(Category $category): ?string
    {
        $category->loadMissing('parent.parent.parent');

        while ($category->parent) {
            $category = $category->parent;
        }

        return $category->slug;
    }

    protected function attachSuccessfulAverageTimes($products): void
    {
        $productIds = $products->pluck('id')->filter()->values();

        if ($productIds->isEmpty()) {
            return;
        }

        $durations = Transaction::query()
            ->whereIn('product_id', $productIds)
            ->where('order_status', 'success')
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->get(['product_id', 'created_at', 'updated_at'])
            ->map(function (Transaction $transaction) {
                if (! $transaction->created_at || ! $transaction->updated_at || $transaction->updated_at->lessThan($transaction->created_at)) {
                    return null;
                }

                return [
                    'product_id' => $transaction->product_id,
                    'seconds' => $transaction->created_at->diffInSeconds($transaction->updated_at),
                ];
            })
            ->filter()
            ->groupBy('product_id');

        $products->each(function (Product $product) use ($durations) {
            $productDurations = $durations->get($product->id, collect());
            $seconds = $productDurations->pluck('seconds');

            $product->setAttribute('ordersosmed_success_average_seconds_cache', $seconds->isNotEmpty() ? (int) round($seconds->avg()) : null);
            $product->setAttribute('ordersosmed_success_order_count_cache', $seconds->count());
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
        $expiresInMinutes = max(1, (int) config('tripay.expired_minutes', 60));

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

    protected function sortSuntikSosmedProducts($products)
    {
        return $products
            ->sort(function (Product $left, Product $right) {
                $leftPrice = (float) $left->ordersosmed_price_per_thousand;
                $rightPrice = (float) $right->ordersosmed_price_per_thousand;

                if ($leftPrice === $rightPrice) {
                    return strnatcasecmp($left->name, $right->name);
                }

                return $leftPrice <=> $rightPrice;
            })
            ->values();
    }
}
