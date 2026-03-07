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
        // Mencari kategori berdasarkan slug (contoh: 'suntik-sosmed')
        $category = Category::where('slug', $slug)->firstOrFail();

        // Mengambil produk yang aktif di kategori tersebut, diurutkan dari harga termurah
        // Menggunakan kolom 'status' sesuai dengan file migration kita
        $products = Product::where('category_id', $category->id)
                            ->where('status', 'active')
                            ->orderBy('price', 'asc')
                            ->get();

        return view('user.order', compact('category', 'products'));
    }

    /**
     * Memproses pembuatan pesanan dan menghasilkan Snap Token Midtrans.
     */
    public function processCheckout(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'target_data'  => 'required|string|min:3',
            'target_notes' => 'nullable|string|max:500', // Untuk instruksi buzzer
        ]);

        try {
            // 2. Ambil data produk untuk mendapatkan harga terbaru
            $product = Product::findOrFail($request->product_id);

            // 3. Buat data transaksi di database dengan status 'unpaid'
            $transaction = Transaction::create([
                'invoice_number' => 'WIB-' . strtoupper(Str::random(12)),
                'user_id'        => Auth::id(),
                'product_id'     => $product->id,
                'amount'         => $product->price,
                'target_data'    => $request->target_data,
                'target_notes'   => $request->target_notes,
                'payment_status' => 'unpaid',
                'order_status'   => 'pending',
            ]);

            // 4. Inisialisasi Midtrans Service untuk mendapatkan Snap Token
            $midtransService = new MidtransService();
            $snapToken = $midtransService->getSnapToken($transaction);

            // 5. Arahkan ke halaman checkout pembayaran
            return view('user.checkout', [
                'transaction' => $transaction,
                'snapToken'   => $snapToken
            ]);

        } catch (Exception $e) {
            // Jika terjadi kesalahan (misal: API Midtrans mati atau Server Key salah)
            return back()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }
}