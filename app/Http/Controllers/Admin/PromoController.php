<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;

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
            'emoji' => 'required|string|max:10',
            'theme' => 'required|in:blue,teal,orange,rose',
            'is_active' => 'required|boolean',
        ]);

        Promo::create($request->all());

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
            'emoji' => 'required|string|max:10',
            'theme' => 'required|in:blue,teal,orange,rose',
            'is_active' => 'required|boolean',
        ]);

        $promo->update($request->all());

        return redirect()->route('admin.promos.index')->with('success', 'Banner Promo berhasil diperbarui! ✨');
    }

    public function destroy(Promo $promo)
    {
        $promo->delete();
        return back()->with('success', 'Banner Promo berhasil dihapus! 🗑️');
    }
}