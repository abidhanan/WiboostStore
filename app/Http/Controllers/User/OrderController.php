<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\ProductCredential;
use App\Services\MidtransService;
use App\Services\OrderSosmedService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class OrderController extends Controller
{
    public function showCategory($slug)
    {
        $category = Category::with(['children', 'products' => function($q) {
            $q->where('is_active', true)->orderBy('price', 'asc');
        }])->where('slug', $slug)->firstOrFail();

        if ($category->children->count() > 0) {
            return view('user.order_sub', compact('category'));
        }

        $products = $category->products;
        return view('user.order', compact('category', 'products'));
    }

    public function processCheckout(Request $request)
    {
        try {
            $product = Product::findOrFail($request->product_id);
            $user = Auth::user();

            $rules = [
                'product_id'     => 'required|exists:products,id',
                'payment_method' => 'required|string|in:wallet,manual', 
            ];

            if (in_array($product->process_type, ['api', 'manual'])) {
                $rules['target_data'] = 'required|string|min:3';
            } else {
                $rules['target_data'] = 'nullable|string';
            }

            $request->validate($rules);
            $targetData = $request->target_data ?? '- (Menunggu Akun/Nomor Dikirim)';

            if ($request->payment_method === 'wallet') {
                if ($user->balance < $product->price) {
                    return back()->with('error', 'Saldo Wiboost tidak mencukupi. Silakan top up terlebih dahulu.');
                }

                $user->decrement('balance', $product->price);

                $transaction = Transaction::create([
                    'invoice_number' => 'WIB-' . strtoupper(Str::random(12)),
                    'user_id'        => $user->id,
                    'product_id'     => $product->id,
                    'amount'         => $product->price,
                    'target_data'    => $targetData,
                    'payment_status' => 'paid',
                    'order_status'   => 'processing',
                    'payment_method' => 'wallet',
                ]);

                $this->processFulfillment($transaction);

                // Mengirim ID Transaksi ke session untuk pemicu Auto-Open Modal
                return redirect()->route('user.history')
                                 ->with('success', 'Pesanan berhasil dibayar menggunakan Saldo Wiboost!')
                                 ->with('new_trx_id', $transaction->id);
            }

            $transaction = Transaction::create([
                'invoice_number' => 'WIB-' . strtoupper(Str::random(12)),
                'user_id'        => $user->id,
                'product_id'     => $product->id,
                'amount'         => $product->price,
                'target_data'    => $targetData,
                'payment_status' => 'unpaid',
                'order_status'   => 'pending',
                'payment_method' => 'manual',
            ]);

            $midtransService = new MidtransService();
            $snapToken = $midtransService->getSnapToken($transaction);
            $transaction->update(['snap_token' => $snapToken]);

            return view('user.checkout', [
                'transaction' => $transaction,
                'snapToken'   => $snapToken
            ]);

        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }

    private function processFulfillment($transaction)
    {
        $product = $transaction->product;

        if ($product->process_type === 'api') {
            $orderSosmed = new OrderSosmedService();
            $quantity = 1000; 
            $apiResponse = $orderSosmed->placeOrder(
                $product->provider_product_id, 
                $transaction->target_data, 
                $quantity
            );
            if ($apiResponse['success']) {
                $transaction->update(['order_status' => 'success']);
            } else {
                $transaction->update([
                    'order_status' => 'failed',
                    'target_notes' => 'Gagal hit API: ' . $apiResponse['message']
                ]);
            }
        } 
        elseif ($product->process_type === 'account' || $product->process_type === 'number') {
            $credential = ProductCredential::where('product_id', $product->id)
                ->where('is_active', true)
                ->whereColumn('current_usage', '<', 'max_usage')
                ->first();

            if ($credential) {
                $credential->increment('current_usage');
                
                // Merekam data ke dalam brankas JSON
                $transaction->update([
                    'order_status' => 'success',
                    'credential_data' => json_encode([
                        'email'    => ($credential->data_1 !== '-' && $credential->data_1 !== null) ? $credential->data_1 : null,
                        'password' => $credential->data_2,
                        'profile'  => $credential->data_3,
                        'pin'      => $credential->data_4,
                        'link'     => $credential->data_5,
                        'type'     => $product->process_type
                    ])
                ]);
            } else {
                $transaction->update(['order_status' => 'pending']);
            }
        } 
        elseif ($product->process_type === 'manual') {
            $transaction->update(['order_status' => 'processing']);
        }
    }
}