@extends('layouts.admin')
@section('title', 'Tambah Tutorial')
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

    <div class="absolute top-10 right-0 text-5xl animate-float opacity-30 pointer-events-none hidden md:block z-0">📖</div>
    <div class="absolute top-1/3 -left-10 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden md:block z-0">✨</div>
    <div class="absolute bottom-20 -right-5 text-6xl animate-float opacity-20 pointer-events-none hidden md:block z-0">☁️</div>

    <div class="flex flex-col md:flex-row items-start md:items-center gap-5 mb-10 pl-2 relative z-10">
        <a href="{{ route('admin.tutorials.index') }}" class="w-14 h-14 bg-white/90 backdrop-blur-sm rounded-[1.2rem] flex items-center justify-center text-[#5a76c8] hover:bg-[#e0fbfc] transition-transform active:scale-95 border-4 border-white shadow-lg shadow-[#bde0fe]/30 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-2 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
                Pusat Bantuan
            </div>
            <h3 class="text-3xl md:text-4xl font-black text-[#2b3a67] tracking-tight drop-shadow-sm">Tambah Tutorial ➕</h3>
            <p class="text-[#8faaf3] font-bold text-sm mt-2 bg-white/50 px-4 py-2 rounded-xl border border-white shadow-sm inline-block">Buat panduan login atau penggunaan untuk bantu pelanggan.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-5 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 flex items-start gap-4 relative z-10">
            <span class="text-3xl mt-1 drop-shadow-sm">⚠️</span>
            <div>
                <p class="mb-3 text-lg">Aduh, gagal menyimpan! Periksa dulu:</p>
                <ul class="list-disc list-inside text-sm font-bold bg-white/50 p-4 rounded-2xl border border-white">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.tutorials.store') }}" method="POST" enctype="multipart/form-data" class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-10 relative z-10">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Judul Panduan</label>
                <input type="text" name="title" required value="{{ old('title') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: Cara Login CapCut">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Kategori</label>
                <input type="text" name="category" required value="{{ old('category', 'Aplikasi Premium') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]">
            </div>
        </div>

        <div class="mb-8">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Deskripsi Singkat (Tampil di Card)</label>
            <input type="text" name="description" required value="{{ old('description') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: Panduan lengkap cara login tanpa limit device.">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 p-6 bg-[#f8faff] rounded-[2rem] border-4 border-[#f0f5ff] shadow-sm">
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Gambar / Logo Aplikasi</label>
                <input type="file" name="image" accept="image/*" class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:bg-[#e0fbfc] file:text-[#4bc6b9] file:shadow-sm cursor-pointer bg-white rounded-[1.5rem] p-2 border-4 border-white shadow-inner">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Atau Emoji (Jika Gambar Kosong)</label>
                <input type="text" name="icon" value="{{ old('icon', '📖') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition text-3xl text-center placeholder-[#a3bbfb]">
            </div>
        </div>

        <div class="p-6 bg-[#fffcf0] rounded-[2rem] border-4 border-white mb-8 shadow-md">
            <p class="text-sm font-black text-amber-600 mb-5 ml-2 bg-amber-100/50 inline-block px-4 py-2 rounded-xl border border-amber-200">Isi Konten Panduan (Bisa Video, Teks, atau Keduanya):</p>
            
            <div class="mb-6">
                <label class="block text-sm font-black text-amber-600 mb-3 ml-2 flex items-center gap-2">🎥 Link Video YouTube</label>
                <input type="url" name="youtube_url" value="{{ old('youtube_url') }}" class="w-full bg-white border-4 border-white focus:border-amber-200 shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#d1d5db]" placeholder="https://youtube.com/watch?v=...">
            </div>

            <div>
                <label class="block text-sm font-black text-amber-600 mb-3 ml-2 flex items-center gap-2">📝 Teks Artikel Panduan</label>
                <textarea name="content" rows="6" class="w-full bg-white border-4 border-white focus:border-amber-200 shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-bold outline-none transition placeholder-[#d1d5db]" placeholder="Langkah 1: Buka aplikasi...&#10;Langkah 2: Masukkan email...">{{ old('content') }}</textarea>
            </div>
        </div>

        <div class="mb-8">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Status Tutorial</label>
            <select name="is_active" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                <option value="1">🟢 Aktif (Tampilkan ke Pelanggan)</option>
                <option value="0">🔴 Sembunyikan</option>
            </select>
        </div>

        <button type="submit" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-xl py-5 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#4bc6b9]/40 border-4 border-white flex justify-center items-center gap-2">
            Simpan Tutorial ✨
        </button>
    </form>
</div>
@endsection