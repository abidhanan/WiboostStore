@extends('layouts.admin')

@section('title', 'Tambah Banner Baru')

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
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2rem] mb-8 font-black shadow-sm flex items-start gap-4">
            <span class="text-2xl mt-1">⚠️</span>
            <ul class="list-disc list-inside text-sm font-bold">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.promos.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white p-8">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Judul Promo / Headline</label>
                <input type="text" name="title" required value="{{ old('title') }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition" placeholder="Cth: Diskon 50% Top Up MLBB!">
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Teks Badge (Label Kecil)</label>
                <input type="text" name="badge_text" required value="{{ old('badge_text', 'INFO PROMO') }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition" placeholder="Cth: HOT DEALS">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Deskripsi Singkat</label>
            <textarea name="description" required rows="3" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition" placeholder="Gunakan kode promo WIBOOSTGG saat checkout...">{{ old('description') }}</textarea>
        </div>

        <div class="mb-8 p-5 bg-[#f0f5ff] rounded-[1.5rem] border-2 border-dashed border-[#bde0fe]">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Gambar Custom Banner (Opsional)</label>
            <input type="file" name="image" accept="image/*" class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:bg-white file:text-[#5a76c8] cursor-pointer bg-white rounded-[1.5rem] p-2 border-2 border-transparent shadow-sm">
            <p class="text-xs font-bold text-[#8faaf3] mt-3 ml-2">💡 Jika kamu mengunggah gambar, maka desain Emoji & Warna Tema di bawah tidak akan dipakai.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Emoji (Bila tanpa gambar)</label>
                <input type="text" name="emoji" value="{{ old('emoji', '✨') }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition text-2xl" placeholder="Cth: 🔥">
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Warna Tema</label>
                <select name="theme" required class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                    <option value="blue" {{ old('theme') == 'blue' ? 'selected' : '' }}>🔵 Biru Langit</option>
                    <option value="teal" {{ old('theme') == 'teal' ? 'selected' : '' }}>🟢 Tosca / Hijau</option>
                    <option value="orange" {{ old('theme') == 'orange' ? 'selected' : '' }}>🟠 Oranye</option>
                    <option value="rose" {{ old('theme') == 'rose' ? 'selected' : '' }}>🔴 Merah Muda</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Status Banner</label>
                <select name="is_active" required class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>🟢 Aktif (Tampilkan)</option>
                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>🔴 Sembunyikan</option>
                </select>
            </div>
        </div>

        <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-xl py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg border-2 border-white">Simpan Banner Promo 🚀</button>
    </form>
</div>
@endsection