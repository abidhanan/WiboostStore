<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // 1. Menampilkan daftar produk
    public function index(Request $request)
    {
        $query = Product::with('category')->latest();

        // Fitur Pencarian berdasarkan nama produk atau provider_id
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('provider_product_id', 'like', '%' . $request->search . '%');
        }

        // Gunakan paginate karena jumlah produk biasanya banyak (ratusan/ribuan)
        $products = $query->paginate(20);
        
        return view('admin.products.index', compact('products'));
    }

    // 2. Menampilkan form tambah produk
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    // 3. Menyimpan produk baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'provider_product_id' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'required|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(5), // Tambahkan random string agar slug pasti unik
            'category_id' => $request->category_id,
            'price' => $request->price,
            'provider_product_id' => $request->provider_product_id,
            'description' => $request->description,
            'image' => $imagePath,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan ke katalog! 🚀');
    }

    // 4. Menampilkan form edit produk
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    // 5. Menyimpan pembaruan produk
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'provider_product_id' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Checkbox HTML tidak mengirim nilai jika tidak dicentang, 
            // jadi kita tidak perlu validasi strict untuk is_active saat update
        ]);

        $data = $request->except('image');
        $data['slug'] = Str::slug($request->name) . '-' . Str::random(5);
        
        // Cek apakah checkbox 'is_active' dicentang (bernilai 1) atau tidak (bernilai null/false)
        $data['is_active'] = $request->has('is_active') ? true : false;

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Perubahan produk berhasil disimpan! ✨');
    }

    // 6. Menghapus produk
    public function destroy(Product $product)
    {
        // Hapus file gambar dari storage jika produk dihapus
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        
        $product->delete();
        
        return back()->with('success', 'Produk berhasil dihapus dari katalog! 🗑️');
    }
}