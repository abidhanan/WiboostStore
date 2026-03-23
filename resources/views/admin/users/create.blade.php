@extends('layouts.admin')
@section('title', 'Tambah Pengguna')
@section('content')
<div class="pb-12 max-w-4xl mx-auto" style="font-family: 'Nunito', sans-serif;">
    <div class="flex items-center gap-4 mb-8 pl-2">
        <a href="{{ route('admin.users.index') }}" class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-[#5a76c8] hover:bg-[#f0f5ff] transition-colors border-2 border-white shadow-sm">❮</a>
        <div>
            <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Tambah Pengguna 👤</h3>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2rem] mb-8 font-black shadow-sm">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white p-8">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Nama Lengkap</label>
                <input type="text" name="name" required value="{{ old('name') }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">WhatsApp</label>
                <input type="text" name="whatsapp" required value="{{ old('whatsapp') }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition" placeholder="Cth: 08123456789">
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Kata Sandi</label>
                <input type="password" name="password" required class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Role Akses</label>
                <select name="role_id" required class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition appearance-none cursor-pointer">
                    <option value="2" {{ old('role_id') == 2 ? 'selected' : '' }}>👤 Buyer (Pelanggan)</option>
                    <option value="1" {{ old('role_id') == 1 ? 'selected' : '' }}>👑 Admin</option>
                </select>
            </div>
        </div>
        <p class="text-xs font-bold text-[#8faaf3] mb-6 ml-2">💡 Info: Saldo pengguna baru akan otomatis diatur menjadi Rp 0.</p>
        <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-lg py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg border-2 border-white mt-4">Simpan Pengguna Baru 🚀</button>
    </form>
</div>
@endsection