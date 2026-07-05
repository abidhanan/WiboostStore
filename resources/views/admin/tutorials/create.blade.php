@extends('layouts.admin')
@section('title', 'Tambah Tutorial')

@section('content')
<div class="wiboost-font pb-12 max-w-4xl mx-auto relative z-10">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.tutorials.index'),
        'badge' => 'Buat Baru',
        'title' => 'Tambah Tutorial',
        'description' => 'Atur data tutorial.',
    ])

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-5 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 flex items-start gap-4 relative z-10">
            <span class="text-2xl mt-1 drop-shadow-sm">!</span>
            <div>
                <p class="mb-3 text-lg">Periksa data berikut:</p>
                <ul class="list-disc list-inside text-sm font-bold bg-white/50 p-4 rounded-2xl border border-white">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form id="tutorial-create-form" action="{{ route('admin.tutorials.store') }}" method="POST" enctype="multipart/form-data" class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-10 relative z-10 overflow-visible">
        @csrf
        <input type="hidden" name="submission_token" value="{{ $submissionToken }}">

        <div class="mb-8 rounded-[2rem] border-4 border-[#f0f5ff] bg-[#f8faff] p-6 shadow-sm">
            <div class="mb-5">
                <span class="inline-flex rounded-full border-2 border-white bg-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-[#5a76c8] shadow-sm">
                    Kategori Tutorial
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Kategori Utama</label>
                    <input type="text" name="main_category" required autocomplete="off" value="{{ old('main_category', 'Aplikasi Premium') }}" class="w-full bg-white border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: Aplikasi Premium">
                </div>

                <div>
                    <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Kategori Kedua <span class="text-[10px] text-amber-500">(Opsional)</span></label>
                    <input type="text" name="sub_category" autocomplete="off" value="{{ old('sub_category') }}" class="w-full bg-white border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Opsional">
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Logo Kategori Kedua <span class="text-[10px] text-amber-500">(Opsional)</span></label>
                <input type="file" name="sub_category_image" accept="image/*" class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:bg-[#e0fbfc] file:text-[#4bc6b9] file:shadow-sm cursor-pointer bg-white rounded-[1.5rem] p-2 border-4 border-white shadow-inner">
            </div>
        </div>

        <div class="mb-8">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Judul Tutorial</label>
            <input type="text" name="title" required value="{{ old('title') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: Cara Login CapCut">
        </div>

        <div class="mb-8">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Deskripsi Singkat</label>
            <input type="text" name="description" value="{{ old('description') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: Cara login akun">
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
            @include('admin.partials.theme-select', [
                'name' => 'is_active',
                'id' => 'tutorial_create_is_active',
                'selectedValue' => old('is_active', '1'),
                'placeholder' => 'Pilih status tutorial',
                'options' => [
                    ['value' => '1', 'label' => 'Aktif, tampilkan ke pelanggan'],
                    ['value' => '0', 'label' => 'Sembunyikan'],
                ],
            ])
        </div>

        <button id="tutorial-create-submit" type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-xl py-5 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white flex justify-center items-center gap-2">
            Simpan Tutorial
        </button>
    </form>
</div>

@endsection
