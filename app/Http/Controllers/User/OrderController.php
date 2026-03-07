<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;

class OrderController extends Controller
{
    public function showCategory($slug)
    {
        // Cari kategori berdasarkan slug (misal: suntik-sosmed)
        $category = Category::where('slug', $slug)->firstOrFail();

        // Ambil produk yang ada di dalam kategori tersebut
        $products = Product::where('category_id', $category->id)
                            ->where('status', 'active')
                            ->orderBy('price', 'asc')
                            ->get();

        return view('user.order', compact('category', 'products'));
    }

    public function processCheckout(Request $request)
    {
        // Logika checkout akan kita buat setelah tampilan form selesai
        return "Pesanan diterima! Sistem sedang memproses pembayaran...";
    }
}