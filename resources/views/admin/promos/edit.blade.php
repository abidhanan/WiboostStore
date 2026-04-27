@extends('layouts.admin')

@section('title', 'Edit Banner')

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
        <a href="{{ route('admin.promos.index') }}" class="w-14 h-14 bg-white/90 backdrop-blur-sm rounded-[1.2rem] flex items-center justify-center text-[#5a76c8] hover:bg-[#e0fbfc] transition-transform active:scale-95 border-4 border-white shadow-lg shadow-[#bde0fe]/30 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <div class="inline-block px-4 py-1 bg-[#fff5eb] text-amber-500 font-black rounded-full mb-2 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
                Mode Edit
            </div>
            <h3 class="text-3xl md:text-4xl font-black text-[#2b3a67] tracking-tight drop-shadow-sm">Edit Banner ✍️</h3>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-5 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 flex items-start gap-4 relative z-10">
            <span class="text-3xl mt-1 drop-shadow-sm">⚠️</span>
            <div>
                <p class="mb-3 text-lg">Ada data yang keliru:</p>
                <ul class="list-disc list-inside text-sm font-bold bg-white/50 p-4 rounded-2xl border border-white">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.promos.update', $promo->id) }}" method="POST" enctype="multipart/form-data" class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-10 relative z-10">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Judul Promo / Headline</label>
                <input type="text" name="title" required value="{{ old('title', $promo->title) }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Teks Badge</label>
                <input type="text" name="badge_text" required value="{{ old('badge_text', $promo->badge_text) }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Deskripsi Lengkap</label>
            <textarea name="description" rows="3" required class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]">{{ old('description', $promo->description) }}</textarea>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Link Tujuan / URL Promo (Opsional)</label>
            <input type="url" name="link" value="{{ old('link', $promo->link) }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: https://wiboost.com/order/kategori">
            <p class="text-[10px] font-black text-[#8faaf3] mt-3 ml-2 uppercase tracking-widest bg-[#f4f9ff] inline-block px-3 py-1 rounded-md border border-white shadow-sm">Kosongkan jika banner tidak bisa diklik.</p>
        </div>

        <div class="mb-8 p-6 bg-[#f8faff] rounded-[2rem] border-4 border-[#f0f5ff] shadow-sm">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Ganti Gambar Custom Banner (Opsional)</label>
            
            @if($promo->image)
                <div class="mb-5 ml-2 flex flex-col md:flex-row md:items-center justify-between bg-white p-4 rounded-[1.5rem] border-4 border-white max-w-lg shadow-sm">
                    <div class="flex items-center gap-4 mb-3 md:mb-0">
                        <img src="{{ Storage::url($promo->image) }}" class="h-20 w-auto object-cover rounded-xl border-2 border-[#bde0fe] shadow-sm">
                        <span class="text-[10px] font-black text-[#5a76c8] uppercase tracking-widest bg-[#f0f5ff] px-2 py-1 rounded-md border border-white">Gambar Aktif</span>
                    </div>
                    <label class="flex items-center justify-center gap-2 cursor-pointer group px-4 py-2 bg-[#ffe5e5] rounded-xl hover:bg-[#ffcccc] transition-colors border-2 border-white shadow-sm w-full md:w-auto">
                        <input type="checkbox" name="remove_image" value="1" class="w-4 h-4 rounded border-gray-300 text-rose-500 focus:ring-rose-500 cursor-pointer shadow-inner">
                        <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Hapus</span>
                    </label>
                </div>
            @endif

            <input type="file" name="image" accept="image/*" class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:bg-[#e0fbfc] file:text-[#4bc6b9] file:shadow-sm cursor-pointer bg-white rounded-[1.5rem] p-2 border-4 border-white shadow-inner">
            <p class="text-xs font-bold text-amber-500 mt-4 ml-2 bg-amber-50 px-3 py-2 rounded-xl border border-amber-100">💡 Desain Emoji & Warna Tema akan diabaikan jika ada gambar.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Emoji</label>
                <input type="text" name="emoji" value="{{ old('emoji', $promo->emoji) }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition text-2xl placeholder-[#a3bbfb]">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Warna Tema Banner</label>
                <select name="theme" required class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                    <option value="blue" {{ old('theme', $promo->theme) == 'blue' ? 'selected' : '' }}>🔵 Biru Langit</option>
                    <option value="teal" {{ old('theme', $promo->theme) == 'teal' ? 'selected' : '' }}>🟢 Tosca</option>
                    <option value="orange" {{ old('theme', $promo->theme) == 'orange' ? 'selected' : '' }}>🟠 Oranye</option>
                    <option value="rose" {{ old('theme', $promo->theme) == 'rose' ? 'selected' : '' }}>🔴 Merah Muda</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Status Banner</label>
                <select name="is_active" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                    <option value="1" {{ old('is_active', $promo->is_active) == 1 ? 'selected' : '' }}>🟢 Aktif (Tampil)</option>
                    <option value="0" {{ old('is_active', $promo->is_active) == 0 ? 'selected' : '' }}>🔴 Sembunyikan</option>
                </select>
            </div>
        </div>

        <button type="submit" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-xl py-5 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#4bc6b9]/40 border-4 border-white flex items-center justify-center gap-2">
            Simpan Perubahan ✨
        </button>
    </form>
</div>
@endsection