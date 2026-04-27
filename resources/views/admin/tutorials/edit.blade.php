@extends('layouts.admin')
@section('title', 'Edit Tutorial')
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
        <a href="{{ route('admin.tutorials.index') }}" class="w-14 h-14 bg-white/90 backdrop-blur-sm rounded-[1.2rem] flex items-center justify-center text-[#5a76c8] hover:bg-[#e0fbfc] transition-transform active:scale-95 border-4 border-white shadow-lg shadow-[#bde0fe]/30 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <div class="inline-block px-4 py-1 bg-[#fff5eb] text-amber-500 font-black rounded-full mb-2 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
                Mode Edit
            </div>
            <h3 class="text-3xl md:text-4xl font-black text-[#2b3a67] tracking-tight drop-shadow-sm">Edit Tutorial ✍️</h3>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-5 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 flex items-start gap-4 relative z-10">
            <span class="text-3xl mt-1 drop-shadow-sm">⚠️</span>
            <div>
                <p class="mb-3 text-lg">Aduh, gagal memperbarui! Periksa dulu:</p>
                <ul class="list-disc list-inside text-sm font-bold bg-white/50 p-4 rounded-2xl border border-white">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.tutorials.update', $tutorial->id) }}" method="POST" enctype="multipart/form-data" class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-10 relative z-10">
        @csrf @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Judul Panduan</label>
                <input type="text" name="title" required value="{{ old('title', $tutorial->title) }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Kategori</label>
                <input type="text" name="category" required value="{{ old('category', $tutorial->category) }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]">
            </div>
        </div>

        <div class="mb-8">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Deskripsi Singkat</label>
            <input type="text" name="description" required value="{{ old('description', $tutorial->description) }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 p-6 bg-[#f8faff] rounded-[2rem] border-4 border-[#f0f5ff] shadow-sm">
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Gambar / Logo Aplikasi</label>
                @if($tutorial->image)
                    <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between bg-white p-4 rounded-[1.5rem] border-4 border-white max-w-sm shadow-sm">
                        <div class="flex items-center gap-4 mb-3 md:mb-0">
                            <img src="{{ Storage::url($tutorial->image) }}" class="h-16 w-auto object-cover rounded-xl border-2 border-[#bde0fe] shadow-sm">
                            <span class="text-[10px] font-black text-[#5a76c8] uppercase tracking-widest bg-[#f0f5ff] px-2 py-1 rounded-md border border-white">Gambar Aktif</span>
                        </div>
                        <label class="flex items-center justify-center gap-2 cursor-pointer group px-4 py-2 bg-[#ffe5e5] rounded-xl hover:bg-[#ffcccc] transition-colors border-2 border-white shadow-sm w-full md:w-auto">
                            <input type="checkbox" name="remove_image" value="1" class="w-4 h-4 text-rose-500 rounded cursor-pointer shadow-inner">
                            <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Hapus</span>
                        </label>
                    </div>
                @endif
                <input type="file" name="image" accept="image/*" class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:bg-[#e0fbfc] file:text-[#4bc6b9] file:shadow-sm cursor-pointer bg-white rounded-[1.5rem] p-2 border-4 border-white shadow-inner">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Atau Emoji (Bila Gambar Kosong)</label>
                <input type="text" name="icon" value="{{ old('icon', $tutorial->icon) }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition text-3xl text-center placeholder-[#a3bbfb]">
            </div>
        </div>

        <div class="p-6 bg-[#fffcf0] rounded-[2rem] border-4 border-white mb-8 shadow-md">
            <div class="mb-6">
                <label class="block text-sm font-black text-amber-600 mb-3 ml-2 flex items-center gap-2">🎥 Link Video YouTube</label>
                <input type="url" name="youtube_url" value="{{ old('youtube_url', $tutorial->youtube_url) }}" class="w-full bg-white border-4 border-white focus:border-amber-200 shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#d1d5db]">
            </div>

            <div>
                <label class="block text-sm font-black text-amber-600 mb-3 ml-2 flex items-center gap-2">📝 Teks Artikel Panduan</label>
                <textarea name="content" rows="6" class="w-full bg-white border-4 border-white focus:border-amber-200 shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-bold outline-none transition placeholder-[#d1d5db]">{{ old('content', $tutorial->content) }}</textarea>
            </div>
        </div>

        <div class="mb-8">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Status Tutorial</label>
            <select name="is_active" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                <option value="1" {{ $tutorial->is_active ? 'selected' : '' }}>🟢 Aktif (Tampilkan)</option>
                <option value="0" {{ !$tutorial->is_active ? 'selected' : '' }}>🔴 Sembunyikan</option>
            </select>
        </div>

        <button type="submit" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-xl py-5 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#4bc6b9]/40 border-4 border-white flex justify-center items-center gap-2">
            Simpan Perubahan ✨
        </button>
    </form>
</div>
@endsection