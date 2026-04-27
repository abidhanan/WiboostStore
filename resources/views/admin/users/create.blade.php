@extends('layouts.admin')
@section('title', 'Tambah Pengguna')
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
    
    <div class="absolute top-10 right-0 text-5xl animate-float opacity-30 pointer-events-none hidden md:block z-0">👤</div>
    <div class="absolute top-1/3 -left-10 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden md:block z-0">✨</div>
    <div class="absolute bottom-20 -right-5 text-6xl animate-float opacity-20 pointer-events-none hidden md:block z-0">☁️</div>

    <div class="flex flex-col md:flex-row items-start md:items-center gap-5 mb-10 pl-2 relative z-10">
        <a href="{{ route('admin.users.index') }}" class="w-14 h-14 bg-white/90 backdrop-blur-sm rounded-[1.2rem] flex items-center justify-center text-[#5a76c8] hover:bg-[#e0fbfc] transition-transform active:scale-95 border-4 border-white shadow-lg shadow-[#bde0fe]/30 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-2 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
                Registrasi
            </div>
            <h3 class="text-3xl md:text-4xl font-black text-[#2b3a67] tracking-tight drop-shadow-sm">Tambah Pengguna 👤</h3>
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

    <form action="{{ route('admin.users.store') }}" method="POST" class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-10 relative z-10">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Nama Lengkap</label>
                <input type="text" name="name" required value="{{ old('name') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="John Doe">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="john@email.com">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Nomor Kontak</label>
                <input type="text" name="whatsapp" required value="{{ old('whatsapp') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: 08123456789">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Kata Sandi</label>
                <input type="password" name="password" required class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Minimal 8 karakter">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Role Akses</label>
                <select name="role_id" required class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition appearance-none cursor-pointer">
                    <option value="2" {{ old('role_id') == 2 ? 'selected' : '' }}>👤 Buyer (Pelanggan)</option>
                    <option value="1" {{ old('role_id') == 1 ? 'selected' : '' }}>👑 Admin</option>
                </select>
            </div>
        </div>
        
        <div class="mb-8 p-4 bg-[#e6fff7] rounded-2xl border-2 border-emerald-100 shadow-sm flex gap-3 items-center">
            <span class="text-2xl">💡</span>
            <p class="text-xs font-bold text-emerald-600">Info: Saldo pengguna baru akan otomatis diatur menjadi <b>Rp 0</b>.</p>
        </div>

        <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-xl py-5 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white flex items-center justify-center gap-2">
            Simpan Pengguna Baru 🚀
        </button>
    </form>
</div>
@endsection