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
        $categories = Category::whereNotNull('parent_id')->orDoesntHave('children')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'category_id'         => 'required|exists:categories,id',
            'price'               => 'required|numeric|min:0',
            'provider_product_id' => 'nullable|string|max:255',
            'process_type'        => 'required|in:api,account,number,manual',
            'stock_reminder'      => 'nullable|integer|min:0',
            'is_active'           => 'required|in:0,1',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'emote'               => 'nullable|string|max:50', 
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create([
            'name'                => $request->name,
            'slug'                => Str::slug($request->name) . '-' . Str::random(5),
            'category_id'         => $request->category_id,
            'price'               => $request->price,
            'provider_product_id' => $request->provider_product_id,
            'process_type'        => $request->process_type,
            'stock_reminder'      => $request->stock_reminder ?? 0,
            'image'               => $imagePath,
            'emote'               => $request->emote, 
            'is_active'           => $request->is_active,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan! 🚀');
    }

    public function edit(Product $product)
    {
        $categories = Category::whereNotNull('parent_id')->orDoesntHave('children')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'category_id'         => 'required|exists:categories,id',
            'price'               => 'required|numeric|min:0',
            'provider_product_id' => 'nullable|string|max:255',
            'process_type'        => 'required|in:api,account,number,manual',
            'stock_reminder'      => 'nullable|integer|min:0',
            'is_active'           => 'required|in:0,1',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'emote'               => 'nullable|string|max:50',
        ]);

        $data = $request->only(['name', 'category_id', 'price', 'provider_product_id', 'process_type', 'stock_reminder', 'is_active', 'emote']);
        
        if (!in_array($request->process_type, ['account', 'number'])) {
            $data['stock_reminder'] = 0;
        }

        // LOGIKA HAPUS GAMBAR
        if ($request->has('remove_image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
                $data['image'] = null;
            }
        } 
        // LOGIKA UPDATE GAMBAR (Hanya jika tidak sedang menghapus & ada file baru)
        elseif ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $data['slug'] = Str::slug($request->name) . '-' . Str::random(5);
        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Perubahan produk berhasil disimpan! ✨');
    }

    public function destroy(Product $product)
    {
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        return back()->with('success', 'Produk berhasil dihapus! 🗑️');
    }
}