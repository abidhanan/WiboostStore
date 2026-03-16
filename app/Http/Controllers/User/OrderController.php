<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\MidtransService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
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
        // 1. Validasi Input (Tanpa Notes)
        $request->validate([
            'product_id'     => 'required|exists:products,id',
            'target_data'    => 'required|string|min:3',
            'payment_method' => 'required|string|in:wallet,manual', 
        ]);

        try {
            $product = Product::findOrFail($request->product_id);
            $user = Auth::user();

            // LOGIKA WALLET
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
                    'target_data'    => $request->target_data,
                    'payment_status' => 'paid',
                    'order_status'   => 'processing',
                ]);

                return redirect()->route('user.history')->with('success', 'Pesanan berhasil dibayar menggunakan Saldo Wiboost!');
            }

            // LOGIKA MIDTRANS
            $transaction = Transaction::create([
                'invoice_number' => 'WIB-' . strtoupper(Str::random(12)),
                'user_id'        => $user->id,
                'product_id'     => $product->id,
                'amount'         => $product->price,
                'target_data'    => $request->target_data,
                'payment_status' => 'unpaid',
                'order_status'   => 'pending',
            ]);

            $midtransService = new MidtransService();
            $snapToken = $midtransService->getSnapToken($transaction);

            return view('user.checkout', [
                'transaction' => $transaction,
                'snapToken'   => $snapToken
            ]);

        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }
}