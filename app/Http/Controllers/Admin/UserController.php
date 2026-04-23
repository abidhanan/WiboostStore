<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'whatsapp' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'role_id' => 'required|in:1,2',
        ], [], [
            'name' => 'nama lengkap',
            'email' => 'email',
            'whatsapp' => 'nomor kontak',
            'password' => 'kata sandi',
            'role_id' => 'role akses',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp' => $request->whatsapp,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'balance' => 0, // Saldo otomatis di-set 0
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna baru berhasil ditambahkan! 🎉');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // Admin SEKARANG HANYA BISA MENGUBAH ROLE
        $request->validate([
            'role_id' => 'required|in:1,2',
        ]);

        $user->role_id = $request->role_id;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Role akses pengguna berhasil diperbarui! ✨');
    }

    public function destroy(User $user)
    {
        if (auth()->id() == $user->id) {
            return back()->with('error', 'Kamu tidak bisa menghapus akunmu sendiri! ⚠️');
        }

        $user->delete();
        return back()->with('success', 'Pengguna berhasil dihapus! 🗑️');
    }
}
