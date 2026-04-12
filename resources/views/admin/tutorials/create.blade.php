@extends('layouts.admin')
@section('title', 'Tambah Tutorial')
@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-12 max-w-4xl mx-auto">
    <div class="flex items-center gap-4 mb-8 pl-2">
        <a href="{{ route('admin.tutorials.index') }}" class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-[#5a76c8] hover:bg-[#f0f5ff] transition-colors border-2 border-white shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Tambah Tutorial ➕</h3>
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

    <form action="{{ route('admin.tutorials.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white p-6 md:p-8">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Judul Panduan</label>
                <input type="text" name="title" required value="{{ old('title') }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition" placeholder="Cth: Cara Login CapCut">
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Kategori</label>
                <input type="text" name="category" required value="{{ old('category', 'Aplikasi Premium') }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition" placeholder="Cth: Aplikasi Premium">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Deskripsi Singkat (Tampil di Card)</label>
            <input type="text" name="description" required value="{{ old('description') }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition" placeholder="Cth: Panduan lengkap cara login tanpa limit.">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 p-5 bg-[#f0f5ff] rounded-[1.5rem] border-2 border-dashed border-[#bde0fe]">
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Gambar / Logo Aplikasi</label>
                <input type="file" name="image" accept="image/*" class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-[#5a76c8] file:text-white cursor-pointer bg-white rounded-[1.5rem] p-2 border-2 border-white shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Atau Emoji (Jika Gambar Kosong)</label>
                <input type="text" name="icon" value="{{ old('icon', '📖') }}" class="w-full bg-white border-2 border-white focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-3 text-[#2b3a67] font-black outline-none transition text-2xl text-center shadow-sm">
            </div>
        </div>

        <div class="p-5 bg-[#fffcf0] rounded-[1.5rem] border-2 border-amber-100 mb-6 shadow-sm">
            <p class="text-xs font-black text-amber-600 mb-4">Isi Konten Panduan (Bisa Video, Teks, atau Keduanya):</p>
            
            <div class="mb-5">
                <label class="block text-sm font-black text-amber-500 mb-3 ml-2">🎥 Link Video YouTube (Opsional)</label>
                <input type="url" name="youtube_url" value="{{ old('youtube_url') }}" class="w-full bg-white border-2 border-amber-200 focus:border-amber-400 rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition" placeholder="https://youtube.com/watch?v=...">
            </div>

            <div>
                <label class="block text-sm font-black text-amber-500 mb-3 ml-2">📝 Teks Artikel Panduan (Opsional)</label>
                <textarea name="content" rows="6" class="w-full bg-white border-2 border-amber-200 focus:border-amber-400 rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-bold outline-none transition" placeholder="Langkah 1: Buka aplikasi...&#10;Langkah 2: Masukkan email...">{{ old('content') }}</textarea>
            </div>
        </div>

        <div class="mb-8">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Status Tutorial</label>
            <select name="is_active" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                <option value="1">Aktif (Tampilkan ke Pelanggan)</option>
                <option value="0">Sembunyikan</option>
            </select>
        </div>

        <button type="submit" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-xl py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg border-2 border-white">Simpan Tutorial ✨</button>
    </form>
</div>
@endsection