@extends('layouts.admin')

@section('title', 'Tambah Promo Baru')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-12 max-w-4xl mx-auto">
    <div class="flex items-center gap-4 mb-8 pl-2">
        <a href="{{ route('admin.promos.index') }}" class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-[#5a76c8] hover:bg-[#f0f5ff] transition-colors border-2 border-white shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Buat Banner Baru 📢</h3>
            <p class="text-[#8faaf3] font-bold text-sm mt-1">Banner ini akan muncul di slider dashboard pelanggan.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2rem] mb-8 font-black shadow-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.promos.store') }}" method="POST" class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white p-8">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Teks Badge (Maks 15 Huruf)</label>
                <input type="text" name="badge_text" required value="{{ old('badge_text') }}"
                       class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" 
                       placeholder="Contoh: INFO PROMO">
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Emoji Latar Belakang</label>
                <input type="text" name="emoji" required value="{{ old('emoji', '✨') }}"
                       class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" 
                       placeholder="Contoh: 🔥 / 💎 / 🚀">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Judul Promo / Pengumuman</label>
            <input type="text" name="title" required value="{{ old('title') }}"
                   class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" 
                   placeholder="Contoh: Diskon 50% Top Up MLBB!">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Deskripsi Lengkap</label>
            <textarea name="description" rows="3" required
                      class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" 
                      placeholder="Gunakan kode promo WIBOOSTGG saat checkout...">{{ old('description') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Warna Tema Banner</label>
                <select name="theme" required class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                    <option value="blue" {{ old('theme') == 'blue' ? 'selected' : '' }}>🔵 Biru Langit (Wiboost Sky)</option>
                    <option value="teal" {{ old('theme') == 'teal' ? 'selected' : '' }}>🟢 Tosca (Success)</option>
                    <option value="orange" {{ old('theme') == 'orange' ? 'selected' : '' }}>🟠 Oranye (Warning)</option>
                    <option value="rose" {{ old('theme') == 'rose' ? 'selected' : '' }}>🔴 Merah Muda (Danger)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Status Banner</label>
                <select name="is_active" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif (Tampilkan)</option>
                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Sembunyikan</option>
                </select>
            </div>
        </div>

        <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-xl py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg shadow-[#5a76c8]/30 border-2 border-white flex justify-center items-center gap-2">
            Simpan Banner 🚀
        </button>
    </form>
</div>
@endsection