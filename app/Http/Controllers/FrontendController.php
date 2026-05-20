<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Product;
use App\Support\WiboostCatalog;

class FrontendController extends Controller
{
    /**
     * Menampilkan Landing Page dengan data dinamis.
     */
    public function index()
    {
        if (auth()->check()) {
            return auth()->user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('user.dashboard');
        }

        $catalogSlugs = WiboostCatalog::coreCategorySlugs();
        $categoryOrderSql = WiboostCatalog::categoryOrderSql();

        // 1. Mengambil data untuk statistik metrik real-time
        $totalUsers = User::where('role_id', 2)->count();
        $totalTransactions = Transaction::where('payment_status', 'paid')->count();
        $activeProducts = Product::where('is_active', true)
            ->whereIn('process_type', WiboostCatalog::activeProductProcessTypes())
            ->count();
        
        // 2. Mengambil semua kategori beserta jumlah produk yang ada di dalamnya
        $categories = Category::whereIn('slug', $catalogSlugs)
            ->withCount(['products' => function ($query) {
                $query->where('is_active', true)
                    ->whereIn('process_type', WiboostCatalog::activeProductProcessTypes());
            }])
            ->orderByRaw($categoryOrderSql)
            ->get();

        // 3. Melempar semua data ke file tampilan 'welcome.blade.php'
        return view('welcome', compact('totalUsers', 'totalTransactions', 'activeProducts', 'categories'));
    }

    public function legal(string $page)
    {
        $pages = [
            'terms' => [
                'title' => 'Syarat & Ketentuan',
                'badge' => 'Aturan Transaksi',
                'intro' => 'Panduan penggunaan layanan Wiboost Store agar transaksi berjalan aman dan jelas untuk buyer maupun admin.',
                'sections' => [
                    ['title' => 'Akun dan data buyer', 'body' => 'Buyer wajib mengisi data pesanan dengan benar, termasuk username, link, User ID, Zone ID, nomor handphone, atau email khusus aplikasi bila diminta oleh produk. Kesalahan input dapat menyebabkan pesanan gagal atau tertunda.'],
                    ['title' => 'Pemrosesan pesanan', 'body' => 'Produk API diproses melalui provider seperti Digiflazz dan OrderSosmed. Produk aplikasi premium diproses dari stok kredensial yang tersedia di website.'],
                    ['title' => 'Larangan penggunaan', 'body' => 'Layanan tidak boleh digunakan untuk penipuan, spam, penyalahgunaan akun, aktivitas ilegal, atau tindakan yang merugikan pihak lain. Wiboost berhak menolak pesanan yang melanggar aturan.'],
                ],
            ],
            'privacy-policy' => [
                'title' => 'Kebijakan Privasi',
                'badge' => 'Data & Keamanan',
                'intro' => 'Kami hanya memakai data pelanggan untuk menjalankan transaksi, dukungan pelanggan, keamanan akun, dan kebutuhan operasional Wiboost Store.',
                'sections' => [
                    ['title' => 'Data yang dikumpulkan', 'body' => 'Data dapat mencakup nama, email, WhatsApp, histori transaksi, target pesanan, dan informasi pembayaran dari payment gateway.'],
                    ['title' => 'Penggunaan data', 'body' => 'Data dipakai untuk membuat invoice, memproses pesanan, mengirim email reset password, mengirim email sukses transaksi, dan membantu admin menyelesaikan kendala.'],
                    ['title' => 'Perlindungan data', 'body' => 'Kami tidak menjual data pelanggan. Akses data dibatasi untuk kebutuhan operasional dan keamanan website.'],
                ],
            ],
            'refund-policy' => [
                'title' => 'Kebijakan Refund',
                'badge' => 'Garansi Saldo',
                'intro' => 'Refund dilakukan dalam bentuk saldo Wiboost ketika sistem mendeteksi pesanan gagal atau memenuhi aturan refund otomatis.',
                'sections' => [
                    ['title' => 'Refund otomatis', 'body' => 'Jika pesanan API gagal diproses provider setelah pembayaran berhasil, sistem otomatis mengembalikan saldo ke akun buyer. Pesanan paid yang pending lebih dari 24 jam juga masuk auto-refund.'],
                    ['title' => 'Bantuan kendala', 'body' => 'Jika pesanan API atau stok premium bermasalah, admin dapat membantu pengecekan melalui riwayat pesanan atau tombol lapor admin.'],
                    ['title' => 'Batasan refund', 'body' => 'Kesalahan data yang dimasukkan buyer, target private/tidak valid, atau perubahan target setelah order dapat membuat refund perlu ditinjau admin terlebih dahulu.'],
                ],
            ],
            'contact' => [
                'title' => 'Kontak Admin',
                'badge' => 'Bantuan Pesanan',
                'intro' => 'Hubungi admin jika ada kendala pembayaran, pesanan pending, salah input, atau butuh bantuan akses produk premium.',
                'sections' => [
                    ['title' => 'Jam bantuan', 'body' => 'Admin akan memprioritaskan pesanan paid, pesanan gagal, dan laporan buyer dari halaman riwayat atau tombol lapor admin.'],
                    ['title' => 'Data yang perlu disiapkan', 'body' => 'Sertakan invoice, email akun Wiboost, nama produk, target pesanan, dan screenshot kendala agar admin cepat mengecek.'],
                    ['title' => 'Channel utama', 'body' => 'Gunakan kontak resmi Wiboost dan sertakan detail invoice agar admin bisa membantu pengecekan dengan cepat.'],
                ],
            ],
        ];

        abort_unless(isset($pages[$page]), 404);

        return view('legal.show', ['page' => $pages[$page], 'slug' => $page]);
    }
}
