<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCredential;
use App\Models\Transaction; // <-- Import model Transaction untuk fitur Auto-Fulfill
use Illuminate\Http\Request;

class CredentialController extends Controller
{
    public function index(Product $product)
    {
        if (!in_array($product->process_type, ['account', 'number'])) {
            return redirect()->route('admin.products.index')->with('error', 'Produk ini tidak membutuhkan manajemen stok akun/nomor.');
        }

        $credentials = $product->credentials()->latest()->get();
        
        return view('admin.credentials.index', compact('product', 'credentials'));
    }

    public function store(Request $request, Product $product)
    {
        // 1. Validasi Dinamis Aplikasi Premium vs Nomor Luar
        if ($product->process_type == 'account') {
            $request->validate([
                'max_usage' => 'required|integer|min:1',
                'data_1'    => 'nullable|string',
                'data_2'    => 'nullable|string',
                'data_3'    => 'nullable|string',
                'data_4'    => 'nullable|string',
                'data_5'    => 'nullable|string',
            ]);
            $data_1 = $request->data_1 ?? '-'; // Cegah error DB jika kosong
            $max_usage = $request->max_usage;
        } else {
            $request->validate([
                'data_1' => 'required|string|max:255'
            ]);
            $data_1 = $request->data_1;
            $max_usage = 1; // Nomor selalu 1 kuota
        }

        // 2. Simpan Kredensial Baru ke Gudang
        $credential = $product->credentials()->create([
            'data_1'        => $data_1,
            'data_2'        => $request->data_2,
            'data_3'        => $request->data_3,
            'data_4'        => $request->data_4,
            'data_5'        => $request->data_5,
            'max_usage'     => $max_usage,
            'current_usage' => 0,
            'is_active'     => true,
        ]);

        // 3. FITUR AUTO-FULFILLMENT (Pengiriman Otomatis ke Antrean Pending)
        // Cari transaksi produk ini yang sudah lunas (paid) tapi statusnya masih pending (karena kehabisan stok)
        $pendingTransactions = Transaction::where('product_id', $product->id)
            ->where('payment_status', 'paid')
            ->where('order_status', 'pending')
            ->orderBy('created_at', 'asc') // First In First Out (Antrean)
            ->get();

        $fulfilledCount = 0;

        foreach ($pendingTransactions as $transaction) {
            // Cek apakah kredensial yang baru saja ditambahkan ini masih ada sisa kuotanya
            if ($credential->current_usage < $credential->max_usage) {
                
                $credential->increment('current_usage');
                
                // Distribusikan akun/nomor ke riwayat transaksi pelanggan
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
                $fulfilledCount++;
                
            } else {
                // Jika kuota baris kredensial ini habis, hentikan pembagian
                break;
            }
        }

        // 4. Siapkan Pesan Sukses
        $message = 'Stok data berhasil ditambahkan ke gudang! 📦';
        if ($fulfilledCount > 0) {
            $message .= " Dan $fulfilledCount pesanan pending otomatis berhasil dikirim ke pelanggan! 🚀";
        }

        return back()->with('success', $message);
    }

    public function destroy(ProductCredential $credential)
    {
        $credential->delete();
        return back()->with('success', 'Stok data berhasil dihapus! 🗑️');
    }
}