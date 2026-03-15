@extends('layouts.admin')

@section('title', 'Tambah Kategori')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-12 max-w-4xl mx-auto">
    <div class="flex items-center gap-4 mb-8 pl-2">
        <a href="{{ route('admin.categories.index') }}" class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-[#5a76c8] hover:bg-[#f0f5ff] transition-colors border-2 border-white shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Tambah Kategori 📁</h3>
            <p class="text-[#8faaf3] font-bold text-sm mt-1">Buat Kategori Utama atau Sub-Kategori baru.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2rem] mb-8 font-black shadow-sm flex items-start gap-4">
            <span class="text-2xl mt-1">⚠️</span>
            <div>
                <p class="mb-2">Aduh, gagal menyimpan! Cek dulu bagian ini:</p>
                <ul class="list-disc list-inside text-sm font-bold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white p-8">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Nama Kategori / Layanan</label>
                <input type="text" name="name" required value="{{ old('name') }}"
                       class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" 
                       placeholder="Contoh: Instagram / Suntik Sosmed">
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Kategori Induk</label>
                <select name="parent_id" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                    <option value="">⭐ Jadikan Kategori Utama</option>
                    @foreach($mainCategories as $main)
                        <option value="{{ $main->id }}" {{ old('parent_id') == $main->id ? 'selected' : '' }}>Sub-Kategori dari: {{ $main->name }}</option>
                    @endforeach
                </select>
                <p class="text-[10px] font-bold text-amber-500 mt-2 ml-2">Pilih jika ingin membuat "Instagram" di dalam "Suntik Sosmed".</p>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Deskripsi Kategori (Disarankan untuk Sub-Kategori)</label>
            <textarea name="description" rows="3"
                      class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" 
                      placeholder="Contoh: Pilih layanan Followers, Likes, dan Views khusus Instagram dengan kualitas server terbaik dan bergaransi...">{{ old('description') }}</textarea>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Slug (URL Kategori)</label>
            <input type="text" name="slug" value="{{ old('slug') }}"
                   class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" 
                   placeholder="Kosongkan agar otomatis dari Nama">
        </div>

        <div class="mb-8">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Ikon / Logo Kategori (Opsional)</label>
            <input type="file" name="image" accept="image/*"
                   class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:text-sm file:font-black file:bg-[#f0f5ff] file:text-[#5a76c8] hover:file:bg-[#e0ebff] file:transition-colors cursor-pointer bg-[#f4f9ff] rounded-[1.5rem] p-2 border-2 border-transparent">
            <p class="text-xs font-bold text-amber-500 mt-2 ml-2">💡 Disarankan gambar kotak (1:1) format PNG transparan maksimal 2MB.</p>
        </div>

        <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-xl py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg shadow-[#5a76c8]/30 border-2 border-white flex justify-center items-center gap-2">
            Simpan Kategori 🚀
        </button>
    </form>
</div>
@endsection