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
            'badge_text'  => 'required|string|max:50',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'emoji'       => 'nullable|string|max:10',
            'theme'       => 'required|in:blue,teal,orange,rose',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'link'        => 'nullable|url|max:500', 
            'is_active'   => 'required|boolean',
        ]);

        $data = $request->all();
        
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
            'badge_text'  => 'required|string|max:50',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'emoji'       => 'nullable|string|max:10',
            'theme'       => 'required|in:blue,teal,orange,rose',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'link'        => 'nullable|url|max:500',
            'is_active'   => 'required|boolean',
        ]);

        // Gunakan except agar remove_image tidak ikut ter-assign secara otomatis
        $data = $request->except(['image', 'remove_image']);

        // LOGIKA MENGHAPUS GAMBAR JIKA CHECKBOX "HAPUS" DICENTANG
        if ($request->has('remove_image') && $request->remove_image == 1) {
            if ($promo->image) {
                Storage::disk('public')->delete($promo->image);
            }
            $data['image'] = null; // Set image jadi null di database
        }

        // LOGIKA UPLOAD GAMBAR BARU
        if ($request->hasFile('image')) {
            // Hapus gambar yang lama agar file server tidak menumpuk
            if ($promo->image) {
                Storage::disk('public')->delete($promo->image);
            }
            $data['image'] = $request->file('image')->store('promos', 'public');
        }

        $promo->update($data);

        return redirect()->route('admin.promos.index')->with('success', 'Banner Promo berhasil diperbarui! ✨');
    }

    public function destroy(Promo $promo)
    {
        if ($promo->image) {
            Storage::disk('public')->delete($promo->image);
        }
        
        $promo->delete();
        
        return back()->with('success', 'Banner Promo berhasil dihapus! 🗑️');
    }
}