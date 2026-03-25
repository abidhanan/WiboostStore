<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->latest()->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $mainCategories = Category::whereNull('parent_id')->get();
        return view('admin.categories.create', compact('mainCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_id'   => 'nullable|exists:categories,id',
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'emote'       => 'nullable|string|max:20', // Validasi Emote
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        Category::create([
            'parent_id'   => $request->parent_id,
            'name'        => $request->name,
            'slug'        => $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name),
            'description' => $request->description,
            'emote'       => $request->emote, // Menyimpan Emote
            'image'       => $imagePath,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori baru berhasil ditambahkan! 🎉');
    }

    public function edit(Category $category)
    {
        $mainCategories = Category::whereNull('parent_id')->where('id', '!=', $category->id)->get();
        return view('admin.categories.edit', compact('category', 'mainCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'parent_id'   => 'nullable|exists:categories,id',
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'emote'       => 'nullable|string|max:20',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
        ]);

        $data = [
            'parent_id'   => $request->parent_id,
            'name'        => $request->name,
            'slug'        => $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name),
            'description' => $request->description,
            'emote'       => $request->emote,
        ];

        // LOGIKA MENGHAPUS GAMBAR (Jika Checkbox Hapus Dicentang)
        if ($request->has('remove_image') && $request->remove_image == 1) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = null; // Kosongkan data gambar di database
        }

        // LOGIKA UPLOAD GAMBAR BARU
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada biar server nggak penuh
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        // LOGIKA PEMBERSIHAN DATA BERDASARKAN TIPE KATEGORI
        if (empty($data['parent_id'])) {
            // Jika dijadikan Kategori Utama (Parent = null)
            $data['parent_id'] = null;
            $data['description'] = null; // Kategori utama tidak pakai deskripsi
            
            // Jika hapus gambar dicentang atau tidak ada upload gambar baru, pastikan image null
            if ($request->has('remove_image') || !$request->hasFile('image')) {
                 if ($category->image) Storage::disk('public')->delete($category->image);
                 $data['image'] = null;
            }
        } else {
            // Jika dijadikan Sub-Kategori
            $data['emote'] = null; // Sub-kategori tidak pakai emote
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui! ✨');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0 || $category->children()->count() > 0) {
            return back()->with('error', 'Gagal hapus! Kategori ini masih memiliki Sub-Kategori atau Produk aktif.');
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();
        return back()->with('success', 'Kategori berhasil dihapus dari sistem! 🗑️');
    }
}