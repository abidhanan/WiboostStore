@extends('layouts.admin')
@section('title', 'Edit Role Pengguna')
@section('content')
<div class="pb-12 max-w-4xl mx-auto" style="font-family: 'Nunito', sans-serif;">
    <div class="flex items-center gap-4 mb-8 pl-2">
        <a href="{{ route('admin.users.index') }}" class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-[#5a76c8] hover:bg-[#f0f5ff] transition-colors border-2 border-white shadow-sm">❮</a>
        <div>
            <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Edit Role Pengguna ✏️</h3>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2rem] mb-8 font-black shadow-sm">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white p-8">
        @csrf @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Nama Lengkap (Terkunci)</label>
                <input type="text" value="{{ $user->name }}" disabled class="w-full bg-[#f0f5ff] border-2 border-transparent rounded-[1.5rem] px-6 py-4 text-[#8faaf3] font-black outline-none cursor-not-allowed">
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Email (Terkunci)</label>
                <input type="email" value="{{ $user->email }}" disabled class="w-full bg-[#f0f5ff] border-2 border-transparent rounded-[1.5rem] px-6 py-4 text-[#8faaf3] font-black outline-none cursor-not-allowed">
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">WhatsApp (Terkunci)</label>
                <input type="text" value="{{ $user->whatsapp }}" disabled class="w-full bg-[#f0f5ff] border-2 border-transparent rounded-[1.5rem] px-6 py-4 text-[#8faaf3] font-black outline-none cursor-not-allowed">
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Saldo Saat Ini (Terkunci)</label>
                <input type="text" value="Rp {{ number_format($user->balance, 0, ',', '.') }}" disabled class="w-full bg-[#f0f5ff] border-2 border-transparent rounded-[1.5rem] px-6 py-4 text-[#8faaf3] font-black outline-none cursor-not-allowed">
            </div>
            
            <div class="md:col-span-2 mt-4 p-6 bg-[#f4f9ff] rounded-[2rem] border-2 border-dashed border-[#bde0fe]">
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Ubah Role Akses</label>
                <select name="role_id" required class="w-full bg-white border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition appearance-none cursor-pointer shadow-sm">
                    <option value="2" {{ old('role_id', $user->role_id) == 2 ? 'selected' : '' }}>👤 Buyer (Pelanggan)</option>
                    <option value="1" {{ old('role_id', $user->role_id) == 1 ? 'selected' : '' }}>👑 Admin</option>
                </select>
                <p class="text-xs font-bold text-[#8faaf3] mt-3 ml-2">Peringatan: Mengubah menjadi Admin akan memberikan akses penuh ke dashboard ini.</p>
            </div>
        </div>
        
        <button type="submit" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-lg py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg border-2 border-white mt-2">Update Role Akses ✨</button>
    </form>
</div>
@endsection