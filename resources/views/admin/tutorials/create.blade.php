@extends('layouts.admin')
@section('title', 'Tambah Tutorial')

@section('content')
<div class="wiboost-font pb-12 max-w-4xl mx-auto relative z-10">
    <div class="flex flex-col md:flex-row items-start md:items-center gap-5 mb-10 pl-2 relative z-10">
        <a href="{{ route('admin.tutorials.index') }}" class="w-14 h-14 bg-white/90 backdrop-blur-sm rounded-[1.2rem] flex items-center justify-center text-[#5a76c8] hover:bg-[#e0fbfc] transition-transform active:scale-95 border-4 border-white shadow-lg shadow-[#bde0fe]/30 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-2 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
                Buat Baru
            </div>
            <h3 class="text-3xl md:text-4xl font-black text-[#2b3a67] tracking-tight drop-shadow-sm">Tambah Tutorial</h3>
            <p class="text-[#8faaf3] font-bold text-sm mt-2 bg-white/50 px-4 py-2 rounded-xl border border-white shadow-sm inline-block">Strukturnya mengikuti kategori: utama, kategori kedua, lalu isi tutorial.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-5 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 flex items-start gap-4 relative z-10">
            <span class="text-2xl mt-1 drop-shadow-sm">!</span>
            <div>
                <p class="mb-3 text-lg">Gagal menyimpan. Periksa bagian ini:</p>
                <ul class="list-disc list-inside text-sm font-bold bg-white/50 p-4 rounded-2xl border border-white">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form id="tutorial-create-form" action="{{ route('admin.tutorials.store') }}" method="POST" enctype="multipart/form-data" class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-10 relative z-10">
        @csrf
        <input type="hidden" name="submission_token" value="{{ $submissionToken }}">

        <div class="mb-8 rounded-[2rem] border-4 border-[#f0f5ff] bg-[#f8faff] p-6 shadow-sm">
            <div class="mb-5">
                <span class="inline-flex rounded-full border-2 border-white bg-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-[#5a76c8] shadow-sm">
                    Struktur Kategori Tutorial
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Kategori Utama</label>
                    <input type="text" name="main_category" required autocomplete="off" value="{{ old('main_category', 'Aplikasi Premium') }}" class="w-full bg-white border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: Aplikasi Premium">
                    <p class="text-[10px] font-black text-[#8faaf3] mt-2 ml-2 uppercase tracking-widest">Muncul sebagai filter utama di halaman user.</p>
                </div>

                <div>
                    <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Kategori Kedua <span class="text-[10px] text-amber-500">(Opsional)</span></label>
                    <input type="text" name="sub_category" autocomplete="off" value="{{ old('sub_category') }}" class="w-full bg-white border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Kosongkan agar tutorial langsung muncul di dashboard user">
                    <p class="text-[10px] font-black text-[#8faaf3] mt-2 ml-2 uppercase tracking-widest">Jika dikosongkan, akordion tutorial muncul langsung setelah kategori utama.</p>
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Logo Kategori Kedua <span class="text-[10px] text-amber-500">(Opsional)</span></label>
                <input type="file" name="sub_category_image" accept="image/*" class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:bg-[#e0fbfc] file:text-[#4bc6b9] file:shadow-sm cursor-pointer bg-white rounded-[1.5rem] p-2 border-4 border-white shadow-inner">
                <p class="text-[10px] font-black text-amber-500 mt-2 ml-2 uppercase tracking-widest">Boleh kosong. Jika kategori kedua sudah pernah punya logo, logo lama akan dipakai ulang.</p>
            </div>
        </div>

        <div class="mb-8">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Judul Tutorial</label>
            <input type="text" name="title" required value="{{ old('title') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: Cara Login CapCut">
        </div>

        <div class="mb-8">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Deskripsi Singkat</label>
            <input type="text" name="description" value="{{ old('description') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: Panduan lengkap cara login tanpa limit device.">
            <p class="text-[10px] font-black text-[#8faaf3] mt-2 ml-2 uppercase tracking-widest">Tampil di bagian ringkas akordion tutorial.</p>
        </div>

        <div class="p-6 bg-[#fffcf0] rounded-[2rem] border-4 border-white mb-8 shadow-md">
            <p class="text-sm font-black text-amber-600 mb-5 ml-2 bg-amber-100/50 inline-block px-4 py-2 rounded-xl border border-amber-200">Isi Tutorial</p>

            <div class="mb-6">
                <label class="block text-sm font-black text-amber-600 mb-3 ml-2">Link Video YouTube</label>
                <input type="url" name="youtube_url" value="{{ old('youtube_url') }}" class="w-full bg-white border-4 border-white focus:border-amber-200 shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#d1d5db]" placeholder="https://youtube.com/watch?v=...">
            </div>

            <div>
                <label class="block text-sm font-black text-amber-600 mb-3 ml-2">Teks Artikel Panduan</label>
                <textarea name="content" rows="7" class="w-full bg-white border-4 border-white focus:border-amber-200 shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-bold outline-none transition placeholder-[#d1d5db]" placeholder="Langkah 1: Buka aplikasi...&#10;Langkah 2: Masukkan email...">{{ old('content') }}</textarea>
            </div>
        </div>

        <div class="mb-8">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Status Tutorial</label>
            <select name="is_active" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                <option value="1" {{ old('is_active', '1') === '1' ? 'selected' : '' }}>Aktif, tampilkan ke pelanggan</option>
                <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Sembunyikan</option>
            </select>
        </div>

        <button id="tutorial-create-submit" type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-xl py-5 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white flex justify-center items-center gap-2">
            Simpan Tutorial
        </button>
    </form>
</div>

@endsection
