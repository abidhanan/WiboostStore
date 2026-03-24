<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\ProductCredential;
use App\Models\User;
use App\Models\WalletHistory; // <-- Import WalletHistory
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

                // 1. Potong Saldo
                $user->decrement('balance', $product->price);

                // 2. Buat Transaksi
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

                // 3. CATAT LOG PEMBELIAN (-)
                WalletHistory::create([
                    'user_id' => $user->id,
                    'type' => 'purchase',
                    'amount' => $product->price,
                    'description' => 'Pembelian Produk: ' . $product->name,
                    'invoice_number' => $transaction->invoice_number,
                ]);

                $this->processFulfillment($transaction);

                return redirect()->route('user.history')
                                 ->with('success', 'Pesanan berhasil diproses!')
                                 ->with('new_trx_id', $transaction->id);
            }

            // Pembayaran via Midtrans (Belum bayar, jadi belum ada WalletHistory)
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
            $apiResponse = $orderSosmed->placeOrder($product->provider_product_id, $transaction->target_data, $quantity);
            
            if ($apiResponse['success']) {
                $transaction->update(['order_status' => 'success']);
            } else {
                // LOGIKA REFUND OTOMATIS JIKA API GAGAL HIT
                $transaction->update([
                    'order_status' => 'failed',
                    'target_notes' => 'Gagal hit API: ' . $apiResponse['message'] . ' (Saldo Otomatis Dikembalikan)'
                ]);
                
                if ($transaction->payment_status === 'paid') {
                    $user = User::find($transaction->user_id);
                    if ($user) {
                        $user->increment('balance', $transaction->amount);
                        
                        // CATAT LOG REFUND (+)
                        WalletHistory::create([
                            'user_id' => $user->id,
                            'type' => 'refund',
                            'amount' => $transaction->amount,
                            'description' => 'Refund Pembelian Gagal: ' . $product->name,
                            'invoice_number' => $transaction->invoice_number,
                        ]);
                    }
                }
            }
        } 
        elseif ($product->process_type === 'account' || $product->process_type === 'number') {
            $credential = ProductCredential::where('product_id', $product->id)
                ->where('is_active', true)
                ->whereColumn('current_usage', '<', 'max_usage')
                ->first();

            if ($credential) {
                $credential->increment('current_usage');
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