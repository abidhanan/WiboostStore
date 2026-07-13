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
</style>

<div class="wiboost-font pb-12 max-w-4xl mx-auto relative z-10">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.users.index'),
        'badge' => 'Mode Edit',
        'title' => 'Edit Pengguna',
        'description' => 'Ubah role akses untuk ' . $user->name . '.',
    ])

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-5 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 flex items-start gap-4 relative z-10">
            <span class="text-3xl mt-1 drop-shadow-sm">⚠️</span>
            <ul class="list-disc list-inside text-sm font-bold mt-2 bg-white/50 p-4 rounded-2xl border border-white">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @php
        $defaultAdminProfilePhotoUrl = \App\Models\User::defaultProfilePhotoUrl();
        $adminUserPhotoUrl = $user->profile_photo_url ?: $defaultAdminProfilePhotoUrl;
    @endphp

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-10 relative z-10 overflow-visible">
        @csrf @method('PUT')

        <div class="mb-8 flex flex-col items-center text-center">
            <label class="mb-4 block text-sm font-black text-[#5a76c8]">Foto Profil</label>
            <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white ring-4 ring-white shadow-lg shadow-[#bde0fe]/40">
                <img src="{{ $adminUserPhotoUrl }}" onerror="this.onerror=null;this.src='{{ $defaultAdminProfilePhotoUrl }}';" alt="Foto profil {{ $user->name }}" class="h-full w-full object-cover">
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-black text-[#a3bbfb] mb-3 ml-2 flex items-center gap-2">Nama Lengkap 🔒</label>
                <input type="text" value="{{ $user->name }}" disabled class="w-full bg-[#f0f5ff] border-4 border-white rounded-[1.5rem] px-6 py-4 text-[#8faaf3] font-black outline-none cursor-not-allowed shadow-inner opacity-80">
            </div>
            <div>
                <label class="block text-sm font-black text-[#a3bbfb] mb-3 ml-2 flex items-center gap-2">Email 🔒</label>
                <input type="email" value="{{ $user->email }}" disabled class="w-full bg-[#f0f5ff] border-4 border-white rounded-[1.5rem] px-6 py-4 text-[#8faaf3] font-black outline-none cursor-not-allowed shadow-inner opacity-80">
            </div>
            <div>
                <label class="block text-sm font-black text-[#a3bbfb] mb-3 ml-2 flex items-center gap-2">Nomor Kontak 🔒</label>
                <input type="text" value="{{ $user->whatsapp }}" disabled class="w-full bg-[#f0f5ff] border-4 border-white rounded-[1.5rem] px-6 py-4 text-[#8faaf3] font-black outline-none cursor-not-allowed shadow-inner opacity-80">
            </div>
            <div>
                <label class="block text-sm font-black text-[#a3bbfb] mb-3 ml-2 flex items-center gap-2">Saldo Saat Ini 🔒</label>
                <input type="text" value="Rp {{ number_format($user->balance, 0, ',', '.') }}" disabled class="w-full bg-[#f0f5ff] border-4 border-white rounded-[1.5rem] px-6 py-4 text-[#4bc6b9] font-black outline-none cursor-not-allowed shadow-inner opacity-80">
            </div>
            
            <div class="md:col-span-2 mt-4 p-8 bg-[#fffcf0] rounded-[2rem] border-4 border-white shadow-md relative z-30 overflow-visible">
                <div class="relative z-10">
                    <label class="block text-lg font-black text-amber-600 mb-4 ml-2">Ubah Role Akses</label>
                    @include('admin.users.partials.role-select', ['selectedRole' => old('role_id', (string) $user->role_id)])
                    <p class="text-xs font-bold text-amber-600 mt-4 ml-2 bg-amber-100/50 inline-block px-3 py-1.5 rounded-lg border border-amber-200">
                        ⚠️ <b>Peringatan:</b> Mengubah menjadi Admin akan memberikan akses penuh ke dashboard pengelolaan.
                    </p>
                </div>
            </div>
        </div>
        
        <button type="submit" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-xl py-5 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#4bc6b9]/40 border-4 border-white flex justify-center items-center gap-2">
            Update Role Akses
        </button>
    </form>
</div>
@endsection
