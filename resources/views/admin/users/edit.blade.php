@extends('layouts.admin')
@section('title', 'Edit Role Pengguna')
@section('content')
<style>
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
    @media (max-width: 767px) {
        body.wiboost-admin-shell .admin-user-edit-page {
            padding-bottom: max(7rem, env(safe-area-inset-bottom)) !important;
        }

        .admin-user-edit-page,
        .admin-user-edit-form {
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
            overscroll-behavior: auto;
            touch-action: pan-y;
        }

        body.wiboost-admin-shell .admin-user-edit-page .admin-user-role-select {
            position: relative !important;
            z-index: 1 !important;
        }

        body.wiboost-admin-shell .admin-user-edit-page .admin-user-role-select.is-open {
            z-index: 60 !important;
        }

        body.wiboost-admin-shell .admin-user-edit-page .admin-user-role-select-menu {
            position: static !important;
            inset: auto !important;
            display: none !important;
            width: 100% !important;
            max-height: 46dvh !important;
            margin-top: .75rem !important;
            overflow-y: auto !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            transform: none !important;
            touch-action: pan-y !important;
        }

        body.wiboost-admin-shell .admin-user-edit-page .admin-user-role-select.is-open .admin-user-role-select-menu {
            display: grid !important;
        }

        body.wiboost-admin-shell .admin-user-edit-page input,
        body.wiboost-admin-shell .admin-user-edit-page button,
        body.wiboost-admin-shell .admin-user-edit-page .admin-user-role-select-label {
            min-width: 0 !important;
            max-width: 100% !important;
        }
    }
</style>

<div class="admin-user-edit-page wiboost-font relative z-10 mx-auto w-full min-w-0 max-w-4xl pb-12">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.users.index'),
        'badge' => 'Mode Edit',
        'title' => 'Edit Pengguna',
        'description' => 'Ubah role akses untuk ' . $user->name . '.',
    ])

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-5 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 flex items-start gap-4 relative z-10">
            @include('partials.wiboost-icon', ['name' => 'alert', 'class' => 'mt-1 h-6 w-6 shrink-0'])
            <ul class="list-disc list-inside text-sm font-bold mt-2 bg-white/50 p-4 rounded-2xl border border-white">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @php
        $defaultAdminProfilePhotoUrl = \App\Models\User::defaultProfilePhotoUrl();
        $adminUserPhotoUrl = $user->profile_photo_url ?: $defaultAdminProfilePhotoUrl;
        $effectiveRoleLabel = $user->effectiveRoleLabel();
        $effectiveRoleStyle = $user->isMember()
            ? 'background:#ede9fe; color:#8b5cf6;'
            : match ((int) $user->role_id) {
                1 => 'background:#e6fff7; color:#22c55e;',
                3 => 'background:#fff7d6; color:#d99b16;',
                4 => 'background:#e0f2fe; color:#0ea5e9;',
                6 => 'background:#ccfbf1; color:#14b8a6;',
                7 => 'background:#fff7d6; color:#f59e0b;',
                8 => 'background:#e6fff7; color:#22c55e;',
                9 => 'background:#ccfbf1; color:#0f766e;',
                default => 'background:#e0fbfc; color:#5a76c8;',
            };
    @endphp

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="admin-user-edit-form relative z-10 overflow-visible rounded-[2rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:rounded-[2.5rem] sm:p-6 md:p-10">
        @csrf @method('PUT')

        <div class="mb-8 flex flex-col items-center text-center">
            <label class="mb-4 block text-sm font-black text-[#5a76c8]">Foto Profil</label>
            <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white ring-4 ring-white shadow-lg shadow-[#bde0fe]/40">
                <img src="{{ $adminUserPhotoUrl }}" onerror="this.onerror=null;this.src='{{ $defaultAdminProfilePhotoUrl }}';" alt="Foto profil {{ $user->name }}" class="h-full w-full object-cover">
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-black text-[#a3bbfb] mb-3 ml-2 flex items-center gap-2">Nama Lengkap</label>
                <input type="text" value="{{ $user->name }}" disabled class="w-full bg-[#f0f5ff] border-4 border-white rounded-[1.5rem] px-6 py-4 text-[#8faaf3] font-black outline-none cursor-not-allowed shadow-inner opacity-80">
            </div>
            <div>
                <label class="block text-sm font-black text-[#a3bbfb] mb-3 ml-2 flex items-center gap-2">Email</label>
                <input type="email" value="{{ $user->email }}" disabled class="w-full bg-[#f0f5ff] border-4 border-white rounded-[1.5rem] px-6 py-4 text-[#8faaf3] font-black outline-none cursor-not-allowed shadow-inner opacity-80">
            </div>
            <div>
                <label class="block text-sm font-black text-[#a3bbfb] mb-3 ml-2 flex items-center gap-2">Nomor Kontak</label>
                <input type="text" value="{{ $user->whatsapp }}" disabled class="w-full bg-[#f0f5ff] border-4 border-white rounded-[1.5rem] px-6 py-4 text-[#8faaf3] font-black outline-none cursor-not-allowed shadow-inner opacity-80">
            </div>
            <div>
                <label class="block text-sm font-black text-[#a3bbfb] mb-3 ml-2 flex items-center gap-2">Saldo Saat Ini</label>
                <input type="text" value="Rp {{ number_format($user->balance, 0, ',', '.') }}" disabled class="w-full bg-[#f0f5ff] border-4 border-white rounded-[1.5rem] px-6 py-4 text-[#4bc6b9] font-black outline-none cursor-not-allowed shadow-inner opacity-80">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2 flex items-center gap-2">Role</label>
                <div class="rounded-[1.5rem] border-4 border-white px-6 py-4 font-black shadow-inner" style="{{ $effectiveRoleStyle }}">
                    {{ $effectiveRoleLabel }}
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-black text-[#8b5cf6] mb-3 ml-2 flex items-center gap-2">Status Membership</label>
                <div class="rounded-[1.5rem] border-4 border-white bg-[#f5f3ff] px-6 py-4 font-black text-[#8b5cf6] shadow-inner">
                    @if($user->isMember())
                        Member aktif sampai {{ $user->membership_expires_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                    @else
                        Tidak aktif
                    @endif
                </div>
            </div>

            <div class="md:col-span-2 mt-4 p-8 bg-[#fffcf0] rounded-[2rem] border-4 border-white shadow-md relative z-30 overflow-visible">
                <div class="relative z-10">
                    <label class="block text-lg font-black text-amber-600 mb-4 ml-2">Ubah Role Akses</label>
                    @include('admin.users.partials.role-select', ['selectedRole' => old('role_id', $user->isMember() ? 'member' : (string) $user->role_id)])
                </div>
            </div>
        </div>
        
        <button type="submit" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-xl py-5 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#4bc6b9]/40 border-4 border-white flex justify-center items-center gap-2">
            Update Role Akses
        </button>
    </form>
</div>
@endsection
