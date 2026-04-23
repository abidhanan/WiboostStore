<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use App\Services\MidtransService;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class OrderController extends Controller
{
    public function showCategory($slug)
    {
        $category = Category::with([
            'parent',
            'children' => function ($query) {
                $query->withCount([
                    'children',
                    'products as active_products_count' => function ($productQuery) {
                        $productQuery->where('is_active', true);
                    },
                ])->orderBy('name');
            },
            'products' => function ($query) {
                $query->where('is_active', true)
                    ->with('category.parent')
                    ->orderBy('price', 'asc');
            },
        ])->where('slug', $slug)->firstOrFail();

        if ($category->children->count() > 0) {
            return view('user.order_sub', compact('category'));
        }

        $products = $category->products;

        return view('user.order', compact('category', 'products'));
    }

    public function processCheckout(
        Request $request,
        MidtransService $midtransService,
        OrderFulfillmentService $orderFulfillmentService
    ) {
        $product = Product::findOrFail($request->product_id);
        $user = Auth::user();
        $checkoutFields = $product->checkout_fields;

        $rules = [
            'product_id' => 'required|exists:products,id',
            'payment_method' => 'required|string|in:wallet,manual',
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
        $targetData = $product->summarizeOrderInput($validated);
        $orderInputData = $product->buildOrderInputData($validated);

        try {
            if ($validated['payment_method'] === 'wallet') {
                $transaction = DB::transaction(function () use ($user, $product, $targetData, $orderInputData) {
                    $freshUser = User::query()->lockForUpdate()->findOrFail($user->id);

                    if ((float) $freshUser->balance < (float) $product->price) {
                        throw new \RuntimeException('Saldo Wiboost tidak mencukupi. Silakan top up terlebih dahulu.');
                    }

                    $freshUser->decrement('balance', (float) $product->price);

                    $transaction = Transaction::create([
                        'invoice_number' => 'WIB-' . strtoupper(Str::random(12)),
                        'user_id' => $freshUser->id,
                        'product_id' => $product->id,
                        'amount' => $product->price,
                        'target_data' => $targetData,
                        'order_input_data' => $orderInputData,
                        'payment_status' => 'paid',
                        'order_status' => 'pending',
                        'payment_method' => 'wallet',
                    ]);

                    WalletHistory::create([
                        'user_id' => $freshUser->id,
                        'type' => 'purchase',
                        'amount' => $product->price,
                        'description' => 'Pembelian Produk: ' . $product->name,
                        'invoice_number' => $transaction->invoice_number,
                    ]);

                    return $transaction;
                });

                $orderFulfillmentService->handlePaidTransaction($transaction->fresh(['product', 'user']));

                return redirect()->route('user.history')
                    ->with('success', 'Pesanan berhasil dibuat dan langsung diproses.')
                    ->with('new_trx_id', $transaction->id);
            }

            $transaction = Transaction::create([
                'invoice_number' => 'WIB-' . strtoupper(Str::random(12)),
                'user_id' => $user->id,
                'product_id' => $product->id,
                'amount' => $product->price,
                'target_data' => $targetData,
                'order_input_data' => $orderInputData,
                'payment_status' => 'unpaid',
                'order_status' => 'pending',
                'payment_method' => 'manual',
            ]);

            $snapToken = $midtransService->getTransactionSnapToken($transaction->fresh(['product', 'user']), $user);
            $transaction->update(['snap_token' => $snapToken]);

            return view('user.checkout', [
                'transaction' => $transaction->fresh(['product', 'user']),
                'snapToken' => $snapToken,
            ]);
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }
}
