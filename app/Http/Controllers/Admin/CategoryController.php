<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    // 1. Menampilkan daftar kategori
    public function index()
    {
        // Tarik data sekalian dengan relasi parent-nya biar admin tahu ini sub-kategori dari mana
        $categories = Category::with('parent')->latest()->get();
        return view('admin.categories.index', compact('categories'));
    }

    // 2. Menampilkan halaman form tambah kategori
    public function create()
    {
        // Ambil hanya kategori UTAMA (yang parent_id nya kosong) untuk dijadikan pilihan
        $mainCategories = Category::whereNull('parent_id')->get();
        return view('admin.categories.create', compact('mainCategories'));
    }

    // 3. Menyimpan data kategori baru & Upload Gambar
    public function store(Request $request)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:categories,id', // Validasi Parent
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string', // Validasi Deskripsi
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        Category::create([
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'slug' => $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name),
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori baru berhasil ditambahkan! 🎉');
    }

    // 4. Menampilkan halaman form edit kategori
    public function edit(Category $category)
    {
        // Ambil kategori utama selain dirinya sendiri (mencegah infinite loop)
        $mainCategories = Category::whereNull('parent_id')->where('id', '!=', $category->id)->get();
        return view('admin.categories.edit', compact('category', 'mainCategories'));
    }

    // 5. Menyimpan perubahan data kategori
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
        ]);

        $data = [
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'slug' => $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name),
            'description' => $request->description,
        ];

        // Cek apakah admin mengupload gambar baru
        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui! ✨');
    }

    // 6. Menghapus kategori & Hapus File Gambar
    public function destroy(Category $category)
    {
        // Cek apakah kategori masih memiliki produk ATAU Sub-Kategori
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