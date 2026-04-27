@extends('layouts.admin')
@section('title', 'Edit Role Pengguna')
@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="wiboost-font pb-12 max-w-4xl mx-auto relative z-10">

    <div class="absolute top-10 right-0 text-5xl animate-float opacity-30 pointer-events-none hidden md:block z-0">✏️</div>
    <div class="absolute top-1/3 -left-10 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden md:block z-0">✨</div>
    <div class="absolute bottom-20 -right-5 text-6xl animate-float opacity-20 pointer-events-none hidden md:block z-0">☁️</div>

    <div class="flex flex-col md:flex-row items-start md:items-center gap-5 mb-10 pl-2 relative z-10">
        <a href="{{ route('admin.users.index') }}" class="w-14 h-14 bg-white/90 backdrop-blur-sm rounded-[1.2rem] flex items-center justify-center text-[#5a76c8] hover:bg-[#e0fbfc] transition-transform active:scale-95 border-4 border-white shadow-lg shadow-[#bde0fe]/30 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <div class="inline-block px-4 py-1 bg-[#fff5eb] text-amber-500 font-black rounded-full mb-2 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
                Mode Edit Role
            </div>
            <h3 class="text-3xl md:text-4xl font-black text-[#2b3a67] tracking-tight drop-shadow-sm">Edit Pengguna ✏️</h3>
            <p class="text-[#8faaf3] font-bold text-sm mt-2 bg-white/50 px-4 py-2 rounded-xl border border-white shadow-sm inline-block">Data dari: <span class="font-black text-[#5a76c8]">{{ $user->name }}</span></p>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-5 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 flex items-start gap-4 relative z-10">
            <span class="text-3xl mt-1 drop-shadow-sm">⚠️</span>
            <ul class="list-disc list-inside text-sm font-bold mt-2 bg-white/50 p-4 rounded-2xl border border-white">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-10 relative z-10">
        @csrf @method('PUT')
        
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
            
            <div class="md:col-span-2 mt-4 p-8 bg-[#fffcf0] rounded-[2rem] border-4 border-white shadow-md relative overflow-hidden">
                <div class="absolute -right-4 -top-4 text-7xl opacity-20">🔑</div>
                <div class="relative z-10">
                    <label class="block text-lg font-black text-amber-600 mb-4 ml-2">Ubah Role Akses</label>
                    <select name="role_id" required class="w-full bg-white border-4 border-white focus:border-amber-200 rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition appearance-none cursor-pointer shadow-inner">
                        <option value="2" {{ old('role_id', $user->role_id) == 2 ? 'selected' : '' }}>👤 Buyer (Pelanggan)</option>
                        <option value="1" {{ old('role_id', $user->role_id) == 1 ? 'selected' : '' }}>👑 Admin</option>
                    </select>
                    <p class="text-xs font-bold text-amber-600 mt-4 ml-2 bg-amber-100/50 inline-block px-3 py-1.5 rounded-lg border border-amber-200">
                        ⚠️ <b>Peringatan:</b> Mengubah menjadi Admin akan memberikan akses penuh ke dashboard pengelolaan.
                    </p>
                </div>
            </div>
        </div>
        
        <button type="submit" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-xl py-5 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#4bc6b9]/40 border-4 border-white flex justify-center items-center gap-2">
            Update Role Akses ✨
        </button>
    </form>
</div>
@endsection