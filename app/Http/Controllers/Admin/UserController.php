<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $roleFilter = (string) $request->query('role_filter', 'all');
        $roleFilter = $roleFilter === 'admin' ? 'super_admin' : $roleFilter;
        $allowedRoleFilters = ['all', 'buyer', 'reseller', 'member', 'super_admin', 'keuangan', 'gudang', 'marketing', 'customer_service'];

        if (! in_array($roleFilter, $allowedRoleFilters, true)) {
            $roleFilter = 'all';
        }

        $users = User::query()
            ->when($roleFilter === 'super_admin', fn ($query) => $query->where('role_id', User::ROLE_SUPER_ADMIN))
            ->when($roleFilter === 'keuangan', fn ($query) => $query->where('role_id', User::ROLE_FINANCE))
            ->when($roleFilter === 'gudang', fn ($query) => $query->where('role_id', User::ROLE_WAREHOUSE))
            ->when($roleFilter === 'marketing', fn ($query) => $query->where('role_id', User::ROLE_MARKETING))
            ->when($roleFilter === 'customer_service', fn ($query) => $query->where('role_id', User::ROLE_CUSTOMER_SERVICE))
            ->when($roleFilter === 'member', fn ($query) => $query->where('membership_expires_at', '>', now()))
            ->when($roleFilter === 'reseller', function ($query) {
                $query->where('role_id', User::ROLE_RESELLER)
                    ->where(function ($innerQuery) {
                        $innerQuery->whereNull('membership_expires_at')
                            ->orWhere('membership_expires_at', '<=', now());
                    });
            })
            ->when($roleFilter === 'buyer', function ($query) {
                $query->whereIn('role_id', User::BUYER_ROLE_IDS)
                    ->where(function ($innerQuery) {
                        $innerQuery->whereNull('membership_expires_at')
                            ->orWhere('membership_expires_at', '<=', now());
                    });
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('whatsapp', 'like', "%{$search}%");

                    if (str_contains(strtolower($search), 'super admin') || str_contains(strtolower($search), 'admin')) {
                        $innerQuery->orWhere('role_id', User::ROLE_SUPER_ADMIN);
                    }

                    if (str_contains(strtolower($search), 'keuangan') || str_contains(strtolower($search), 'kuangan')) {
                        $innerQuery->orWhere('role_id', User::ROLE_FINANCE);
                    }

                    if (str_contains(strtolower($search), 'gudang')) {
                        $innerQuery->orWhere('role_id', User::ROLE_WAREHOUSE);
                    }

                    if (str_contains(strtolower($search), 'marketing')) {
                        $innerQuery->orWhere('role_id', User::ROLE_MARKETING);
                    }

                    if (str_contains(strtolower($search), 'customer service') || str_contains(strtolower($search), 'cs')) {
                        $innerQuery->orWhere('role_id', User::ROLE_CUSTOMER_SERVICE);
                    }

                    if (str_contains(strtolower($search), 'buyer') || str_contains(strtolower($search), 'pelanggan')) {
                        $innerQuery->orWhereIn('role_id', User::BUYER_ROLE_IDS);
                    }

                    if (str_contains(strtolower($search), 'reseller')) {
                        $innerQuery->orWhere('role_id', User::ROLE_RESELLER);
                    }

                    if (str_contains(strtolower($search), 'member') || str_contains(strtolower($search), 'membership')) {
                        $innerQuery->orWhere('membership_expires_at', '>', now());
                    }
                });
            })
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('admin.users.index', compact('users', 'search', 'roleFilter'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function legacyResellers()
    {
        return redirect()->route('admin.users.index', ['role_filter' => 'reseller']);
    }

    public function legacyMembers()
    {
        return redirect()->route('admin.users.index', ['role_filter' => 'member']);
    }

    public function legacyAdmins()
    {
        return redirect()->route('admin.users.index', ['role_filter' => 'super_admin']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'whatsapp' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'role_id' => 'required|in:1,2,3,4,6,7,8',
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

        return redirect()->route('admin.users.index')->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // Admin SEKARANG HANYA BISA MENGUBAH ROLE
        $request->validate([
            'role_id' => 'required|in:1,2,3,4,6,7,8',
        ]);

        $user->role_id = $request->role_id;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Role akses pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() == $user->id) {
            return back()->with('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        $user->delete();
        return back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
