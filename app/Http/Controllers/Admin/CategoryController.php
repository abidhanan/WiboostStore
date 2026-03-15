<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage; // <-- Jangan lupa class Storage ini wajib

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

    // 3. Menyimpan data kategori baru & Upload Gambar
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048', // Tambahan validasi gambar
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        Category::create([
            'name' => $request->name,
            'slug' => $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name),
            'image' => $imagePath, // Simpan path gambar ke database
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori baru berhasil ditambahkan! 🎉');
    }

    // 4. Menampilkan halaman form edit kategori
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    // 5. Menyimpan perubahan data kategori & Update Gambar
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'slug' => $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name),
        ];

        // Cek apakah admin mengupload gambar baru saat Edit
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            // Simpan gambar yang baru
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui! ✨');
    }

    // 6. Menghapus kategori & Hapus File Gambar
    public function destroy(Category $category)
    {
        // Cek apakah kategori masih memiliki produk terkait sebelum dihapus
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Gagal hapus! Kategori ini masih memiliki produk aktif. Hapus atau pindahkan produknya terlebih dahulu.');
        }

        // Hapus file fisik gambar dari storage (jika punya)
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();
        return back()->with('success', 'Kategori berhasil dihapus dari sistem! 🗑️');
    }
}