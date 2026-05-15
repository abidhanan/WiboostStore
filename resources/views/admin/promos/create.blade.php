@extends('layouts.admin')

@section('title', 'Tambah Banner Baru')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-12 max-w-4xl mx-auto relative z-10">
    <div class="flex flex-col md:flex-row items-start md:items-center gap-5 mb-10 pl-2 relative z-10">
        <a href="{{ route('admin.promos.index') }}" class="w-14 h-14 bg-white/90 backdrop-blur-sm rounded-[1.2rem] flex items-center justify-center text-[#5a76c8] hover:bg-[#e0fbfc] transition-transform active:scale-95 border-4 border-white shadow-lg shadow-[#bde0fe]/30 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-2 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
                Buat Baru
            </div>
            <h3 class="text-3xl md:text-4xl font-black text-[#2b3a67] tracking-tight drop-shadow-sm">Tambah Banner</h3>
            <p class="text-[#8faaf3] font-bold text-sm mt-2 bg-white/50 px-4 py-2 rounded-xl border border-white shadow-sm inline-block">
                Banner akan muncul di slider dashboard pelanggan. Semua teks opsional jika admin hanya ingin memakai gambar desain jadi.
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-5 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 relative z-10">
            <p class="mb-3 text-lg">Gagal menyimpan. Periksa dulu:</p>
            <ul class="list-disc list-inside text-sm font-bold bg-white/50 p-4 rounded-2xl border border-white">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form id="promo-create-form" action="{{ route('admin.promos.store') }}" method="POST" enctype="multipart/form-data" class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-10 relative z-10">
        @csrf
        <input type="hidden" name="submission_token" value="{{ $submissionToken }}">

        <div class="mb-8 p-6 bg-[#f8faff] rounded-[2rem] border-4 border-[#f0f5ff] shadow-sm">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Gambar Custom Banner</label>
            <input type="file" name="image" accept="image/*" class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:bg-[#e0fbfc] file:text-[#4bc6b9] file:shadow-sm cursor-pointer bg-white rounded-[1.5rem] p-2 border-4 border-white shadow-inner">
            <p class="text-xs font-bold text-amber-500 mt-4 ml-2 bg-amber-50 px-3 py-2 rounded-xl border border-amber-100">
                Rekomendasi gambar: 1600 x 700 px (rasio 16:7). Rasio ini dipakai untuk desktop dan mobile supaya desain tidak ribet dan tetap rapi.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Judul Promo / Headline (Opsional)</label>
                <input type="text" name="title" value="{{ old('title') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: Diskon 50% Top Up!">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Teks Badge (Opsional)</label>
                <input type="text" name="badge_text" value="{{ old('badge_text') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: HOT DEALS">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Deskripsi Singkat (Opsional)</label>
            <textarea name="description" rows="3" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Gunakan kode promo WIBOOSTGG saat checkout...">{{ old('description') }}</textarea>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Link Tujuan / URL Promo (Opsional)</label>
            <input type="url" name="link" value="{{ old('link') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: https://wiboost.com/order/kategori">
            <p class="text-[10px] font-black text-[#8faaf3] mt-3 ml-2 uppercase tracking-widest bg-[#f4f9ff] inline-block px-3 py-1 rounded-md border border-white shadow-sm">Kosongkan jika banner tidak diklik.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Emoji (Opsional)</label>
                <input type="text" name="emoji" value="{{ old('emoji') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition text-2xl placeholder-[#a3bbfb]" placeholder="Opsional">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Warna Tema</label>
                <select name="theme" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                    <option value="blue" {{ old('theme', 'blue') == 'blue' ? 'selected' : '' }}>Biru Langit</option>
                    <option value="teal" {{ old('theme') == 'teal' ? 'selected' : '' }}>Tosca / Hijau</option>
                    <option value="orange" {{ old('theme') == 'orange' ? 'selected' : '' }}>Oranye</option>
                    <option value="rose" {{ old('theme') == 'rose' ? 'selected' : '' }}>Merah Muda</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Status Banner</label>
                <select name="is_active" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif (Tampil)</option>
                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Sembunyikan</option>
                </select>
            </div>
        </div>

        <button id="promo-create-submit" type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-xl py-5 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white flex justify-center items-center">
            Simpan Banner
        </button>
    </form>
</div>
<script>
    const promoCreateForm = document.getElementById('promo-create-form');
    const promoCreateSubmit = document.getElementById('promo-create-submit');

    promoCreateForm?.addEventListener('submit', function () {
        if (!promoCreateSubmit || promoCreateSubmit.disabled) {
            return;
        }

        promoCreateSubmit.disabled = true;
        promoCreateSubmit.classList.add('cursor-not-allowed', 'opacity-70');
        promoCreateSubmit.textContent = 'Menyimpan...';
    });
</script>
@endsection
