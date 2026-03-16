<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('provider_product_id', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(20);
        
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        // Hanya ambil Sub-Kategori (yang punya parent_id) ATAU kategori yang tidak punya anak
        $categories = Category::whereNotNull('parent_id')->orDoesntHave('children')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'provider_product_id' => 'nullable|string|max:255',
            'is_active' => 'required|in:0,1',
        ]);

        Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(5),
            'category_id' => $request->category_id,
            'price' => $request->price,
            'provider_product_id' => $request->provider_product_id,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan ke katalog! 🚀');
    }

    public function edit(Product $product)
    {
        $categories = Category::whereNotNull('parent_id')->orDoesntHave('children')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'provider_product_id' => 'nullable|string|max:255',
            'is_active' => 'required|in:0,1',
        ]);

        $data = $request->only(['name', 'category_id', 'price', 'provider_product_id', 'is_active']);
        $data['slug'] = Str::slug($request->name) . '-' . Str::random(5);

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Perubahan produk berhasil disimpan! ✨');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Produk berhasil dihapus dari katalog! 🗑️');
    }
}