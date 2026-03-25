@extends('layouts.admin')

@section('title', 'Edit Banner')

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
            <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Edit Banner ✍️</h3>
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

    <form action="{{ route('admin.promos.update', $promo->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white p-8">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Judul Promo / Headline</label>
                <input type="text" name="title" required value="{{ old('title', $promo->title) }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Teks Badge</label>
                <input type="text" name="badge_text" required value="{{ old('badge_text', $promo->badge_text) }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Deskripsi Lengkap</label>
            <textarea name="description" rows="3" required class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">{{ old('description', $promo->description) }}</textarea>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Link Tujuan / URL Promo (Opsional)</label>
            <input type="url" name="link" value="{{ old('link', $promo->link) }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: https://wiboost.com/order/kategori">
            <p class="text-[10px] font-bold text-[#8faaf3] mt-2 ml-2">Kosongkan jika banner tidak ingin bisa diklik.</p>
        </div>

        <div class="mb-8 p-5 bg-[#f0f5ff] rounded-[1.5rem] border-2 border-dashed border-[#bde0fe]">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Ganti Gambar Custom Banner (Opsional)</label>
            
            @if($promo->image)
                <div class="mb-4 ml-2 flex items-center justify-between bg-white p-3 rounded-2xl border-2 border-transparent max-w-md shadow-sm">
                    <div class="flex items-center gap-4">
                        <img src="{{ Storage::url($promo->image) }}" class="h-16 w-auto object-cover rounded-lg border-2 border-[#bde0fe] shadow-sm">
                        <div>
                            <span class="text-xs font-black text-[#5a76c8] block">Gambar Aktif</span>
                        </div>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer group px-3 py-2 bg-[#ffe5e5] rounded-xl hover:bg-[#ffcccc] transition-colors border-2 border-white">
                        <input type="checkbox" name="remove_image" value="1" class="w-4 h-4 rounded border-gray-300 text-rose-500 focus:ring-rose-500 cursor-pointer">
                        <span class="text-[10px] font-black text-rose-500 uppercase">Hapus</span>
                    </label>
                </div>
            @endif

            <input type="file" name="image" accept="image/*" class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:bg-white file:text-[#5a76c8] cursor-pointer bg-white rounded-[1.5rem] p-2 border-2 border-transparent shadow-sm">
            <p class="text-xs font-bold text-[#8faaf3] mt-3 ml-2">💡 Jika kamu mengunggah gambar, maka desain Emoji & Warna Tema akan diabaikan.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Emoji</label>
                <input type="text" name="emoji" value="{{ old('emoji', $promo->emoji) }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition text-2xl">
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Warna Tema Banner</label>
                <select name="theme" required class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                    <option value="blue" {{ old('theme', $promo->theme) == 'blue' ? 'selected' : '' }}>🔵 Biru Langit</option>
                    <option value="teal" {{ old('theme', $promo->theme) == 'teal' ? 'selected' : '' }}>🟢 Tosca</option>
                    <option value="orange" {{ old('theme', $promo->theme) == 'orange' ? 'selected' : '' }}>🟠 Oranye</option>
                    <option value="rose" {{ old('theme', $promo->theme) == 'rose' ? 'selected' : '' }}>🔴 Merah Muda</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Status Banner</label>
                <select name="is_active" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                    <option value="1" {{ old('is_active', $promo->is_active) == 1 ? 'selected' : '' }}>Aktif (Tampilkan)</option>
                    <option value="0" {{ old('is_active', $promo->is_active) == 0 ? 'selected' : '' }}>Sembunyikan</option>
                </select>
            </div>
        </div>

        <button type="submit" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-xl py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg border-2 border-white">Simpan Perubahan ✨</button>
    </form>
</div>
@endsection