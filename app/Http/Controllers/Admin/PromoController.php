<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PromoController extends Controller
{
    public function index()
    {
        $promos = Promo::latest()->get();
        return view('admin.promos.index', compact('promos'));
    }

    public function create()
    {
        return view('admin.promos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'badge_text' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'emoji' => 'nullable|string|max:10',
            'theme' => 'required|in:blue,teal,orange,rose',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072', // Validasi Gambar Banner Maks 3MB
            'is_active' => 'required|boolean',
        ]);

        $data = $request->all();
        
        // Simpan gambar jika Admin melakukan upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('promos', 'public');
        }

        Promo::create($data);

        return redirect()->route('admin.promos.index')->with('success', 'Banner Promo berhasil ditambahkan! 🚀');
    }

    public function edit(Promo $promo)
    {
        return view('admin.promos.edit', compact('promo'));
    }

    public function update(Request $request, Promo $promo)
    {
        $request->validate([
            'badge_text' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'emoji' => 'nullable|string|max:10',
            'theme' => 'required|in:blue,teal,orange,rose',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'is_active' => 'required|boolean',
        ]);

        $data = $request->all();

        // Ganti gambar jika Admin melakukan upload saat Edit
        if ($request->hasFile('image')) {
            if ($promo->image) {
                Storage::disk('public')->delete($promo->image); // Hapus file lama
            }
            $data['image'] = $request->file('image')->store('promos', 'public'); // Simpan file baru
        }

        $promo->update($data);

        return redirect()->route('admin.promos.index')->with('success', 'Banner Promo berhasil diperbarui! ✨');
    }

    public function destroy(Promo $promo)
    {
        // Hapus file gambar dari Storage saat Promo dihapus
        if ($promo->image) {
            Storage::disk('public')->delete($promo->image);
        }
        $promo->delete();
        return back()->with('success', 'Banner Promo berhasil dihapus! 🗑️');
    }
}