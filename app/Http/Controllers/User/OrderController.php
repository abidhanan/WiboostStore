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
    /**
     * Menampilkan halaman formulir pemesanan berdasarkan kategori.
     */
    public function showCategory($slug)
    {
        // 1. Cari kategori berdasarkan slug, sekalian ambil data anak (sub-kategori) dan produk aktif
        $category = Category::with(['children', 'products' => function($q) {
            $q->where('is_active', true)->orderBy('price', 'asc');
        }])->where('slug', $slug)->firstOrFail();

        // 2. CEK LOGIKA SUB-KATEGORI:
        // Jika kategori ini punya "Anak" (Contoh: Kategori Utama "Suntik Sosmed")
        if ($category->children->count() > 0) {
            // Arahkan ke halaman pemilihan Sub-Kategori (Nanti kita buat view-nya)
            return view('user.order_sub', compact('category'));
        }

        // 3. Jika tidak punya "Anak" (Contoh: Sub-Kategori "Instagram")
        // Maka langsung tampilkan halaman order produk seperti biasa
        $products = $category->products;
        return view('user.order', compact('category', 'products'));
    }

    /**
     * Memproses pembuatan pesanan, memotong saldo, ATAU menghasilkan Snap Token Midtrans.
     */
    public function processCheckout(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'product_id'     => 'required|exists:products,id',
            'target_data'    => 'required|string|min:3',
            'target_notes'   => 'nullable|string|max:500',
            'payment_method' => 'required|string|in:wallet,manual', // Pastikan metode valid
        ]);

        try {
            // 2. Ambil data produk dan user
            $product = Product::findOrFail($request->product_id);
            $user = Auth::user();

            // ==========================================
            // LOGIKA PEMBAYARAN: SALDO WIBOOST (WALLET)
            // ==========================================
            if ($request->payment_method === 'wallet') {
                
                // Cek ketersediaan saldo
                if ($user->balance < $product->price) {
                    return back()->with('error', 'Saldo Wiboost tidak mencukupi. Silakan top up terlebih dahulu.');
                }

                // Potong saldo
                $user->decrement('balance', $product->price);

                // Buat transaksi LUNAS
                $transaction = Transaction::create([
                    'invoice_number' => 'WIB-' . strtoupper(Str::random(12)),
                    'user_id'        => $user->id,
                    'product_id'     => $product->id,
                    'amount'         => $product->price,
                    'target_data'    => $request->target_data,
                    'target_notes'   => $request->target_notes,
                    'payment_status' => 'paid',
                    'order_status'   => 'processing',
                ]);

                // Tembak ke Provider (Opsional/Nanti)
                // $digiflazz = new \App\Services\DigiflazzService();
                // $digiflazz->placeOrder($transaction);

                return redirect()->route('user.history')->with('success', 'Pesanan berhasil dibayar menggunakan Saldo Wiboost!');
            }

            // ==========================================
            // LOGIKA PEMBAYARAN: MIDTRANS (QRIS/BANK)
            // ==========================================
            
            // Buat transaksi MENUNGGU BAYAR
            $transaction = Transaction::create([
                'invoice_number' => 'WIB-' . strtoupper(Str::random(12)),
                'user_id'        => $user->id,
                'product_id'     => $product->id,
                'amount'         => $product->price,
                'target_data'    => $request->target_data,
                'target_notes'   => $request->target_notes,
                'payment_status' => 'unpaid',
                'order_status'   => 'pending',
            ]);

            // Dapatkan Token Midtrans
            $midtransService = new MidtransService();
            $snapToken = $midtransService->getSnapToken($transaction);

            // Arahkan ke halaman konfirmasi
            return view('user.checkout', [
                'transaction' => $transaction,
                'snapToken'   => $snapToken
            ]);

        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }
}