<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // 1. Menampilkan daftar kategori
    public function index()
    {
        $categories = Category::latest()->get();
        return view('admin.categories.index', compact('categories'));
    }

    // 2. Menampilkan halaman form tambah kategori
    public function create()
    {
        return view('admin.categories.create');
    }

    // 3. Menyimpan data kategori baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
        ]);

        Category::create([
            'name' => $request->name,
            // Jika user tidak mengisi slug, otomatis buat dari nama
            'slug' => $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori baru berhasil ditambahkan! 🎉');
    }

    // 4. Menampilkan halaman form edit kategori
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    // 5. Menyimpan perubahan data kategori
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui! ✨');
    }

    // 6. Menghapus kategori
    public function destroy(Category $category)
    {
        // Cek apakah kategori masih memiliki produk terkait sebelum dihapus (Mencegah error Relasi DB)
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Gagal hapus! Kategori ini masih memiliki produk aktif. Hapus atau pindahkan produknya terlebih dahulu.');
        }

        $category->delete();
        return back()->with('success', 'Kategori berhasil dihapus dari sistem! 🗑️');
    }
}