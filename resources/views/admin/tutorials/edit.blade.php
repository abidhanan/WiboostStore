@extends('layouts.admin')
@section('title', 'Edit Tutorial')
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
            <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Edit Tutorial ✍️</h3>
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

    <form action="{{ route('admin.tutorials.update', $tutorial->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white p-6 md:p-8">
        @csrf @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Judul Panduan</label>
                <input type="text" name="title" required value="{{ old('title', $tutorial->title) }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Kategori</label>
                <input type="text" name="category" required value="{{ old('category', $tutorial->category) }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Deskripsi Singkat</label>
            <input type="text" name="description" required value="{{ old('description', $tutorial->description) }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 p-5 bg-[#f0f5ff] rounded-[1.5rem] border-2 border-dashed border-[#bde0fe]">
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Gambar / Logo Aplikasi</label>
                @if($tutorial->image)
                    <div class="mb-3 flex items-center justify-between bg-white p-3 rounded-2xl border-2 border-transparent shadow-sm">
                        <img src="{{ Storage::url($tutorial->image) }}" class="h-10 w-auto object-cover rounded-lg border border-[#bde0fe]">
                        <label class="flex items-center gap-2 cursor-pointer bg-[#ffe5e5] px-3 py-1.5 rounded-xl hover:bg-[#ffcccc]">
                            <input type="checkbox" name="remove_image" value="1" class="w-4 h-4 text-rose-500 rounded cursor-pointer">
                            <span class="text-[10px] font-black text-rose-500 uppercase">Hapus</span>
                        </label>
                    </div>
                @endif
                <input type="file" name="image" accept="image/*" class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-[#5a76c8] file:text-white cursor-pointer bg-white rounded-[1.5rem] p-2 border-2 border-white shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Atau Emoji (Jika Gambar Kosong)</label>
                <input type="text" name="icon" value="{{ old('icon', $tutorial->icon) }}" class="w-full bg-white border-2 border-white focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-3 text-[#2b3a67] font-black outline-none transition text-2xl text-center shadow-sm">
            </div>
        </div>

        <div class="p-5 bg-[#fffcf0] rounded-[1.5rem] border-2 border-amber-100 mb-6 shadow-sm">
            <div class="mb-5">
                <label class="block text-sm font-black text-amber-500 mb-3 ml-2">🎥 Link Video YouTube</label>
                <input type="url" name="youtube_url" value="{{ old('youtube_url', $tutorial->youtube_url) }}" class="w-full bg-white border-2 border-amber-200 focus:border-amber-400 rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">
            </div>

            <div>
                <label class="block text-sm font-black text-amber-500 mb-3 ml-2">📝 Teks Artikel Panduan</label>
                <textarea name="content" rows="6" class="w-full bg-white border-2 border-amber-200 focus:border-amber-400 rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-bold outline-none transition">{{ old('content', $tutorial->content) }}</textarea>
            </div>
        </div>

        <div class="mb-8">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Status Tutorial</label>
            <select name="is_active" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                <option value="1" {{ $tutorial->is_active ? 'selected' : '' }}>Aktif (Tampilkan)</option>
                <option value="0" {{ !$tutorial->is_active ? 'selected' : '' }}>Sembunyikan</option>
            </select>
        </div>

        <button type="submit" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-xl py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg border-2 border-white">Simpan Perubahan ✨</button>
    </form>
</div>
@endsection