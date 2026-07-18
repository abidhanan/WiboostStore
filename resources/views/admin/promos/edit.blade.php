@extends('layouts.admin')

@section('title', 'Edit Banner')

@section('content')
<div class="wiboost-font pb-12 max-w-4xl mx-auto relative z-10">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.promos.index'),
        'badge' => 'Mode Edit',
        'title' => 'Edit Banner',
        'description' => 'Atur data banner.',
    ])

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-5 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 relative z-10">
            <p class="mb-3 text-lg">Periksa data berikut:</p>
            <ul class="list-disc list-inside text-sm font-bold bg-white/50 p-4 rounded-2xl border border-white">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @php
        $themeOptions = [
            ['value' => 'blue', 'label' => 'Biru Langit'],
            ['value' => 'teal', 'label' => 'Tosca / Hijau'],
            ['value' => 'orange', 'label' => 'Oranye'],
            ['value' => 'rose', 'label' => 'Merah Muda'],
        ];
    @endphp

    <form action="{{ route('admin.promos.update', $promo->id) }}" method="POST" enctype="multipart/form-data" class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-10 relative z-10 overflow-visible">
        @csrf
        @method('PUT')

        <div class="mb-8 p-6 bg-[#f8faff] rounded-[2rem] border-4 border-[#f0f5ff] shadow-sm">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Gambar Custom Banner</label>

            @if($promo->image)
                <div class="mb-5 ml-2 flex flex-col md:flex-row md:items-center justify-between bg-white p-4 rounded-[1.5rem] border-4 border-white max-w-lg shadow-sm">
                    <div class="flex items-center gap-4 mb-3 md:mb-0">
                        <img src="{{ Storage::url($promo->image) }}" class="h-20 w-auto object-cover rounded-xl border-2 border-[#bde0fe] shadow-sm" alt="Gambar banner aktif">
                        <span class="text-[10px] font-black text-[#5a76c8] uppercase tracking-widest bg-[#f0f5ff] px-2 py-1 rounded-md border border-white">Gambar Aktif</span>
                    </div>
                    <label class="flex items-center justify-center gap-2 cursor-pointer group px-4 py-2 bg-[#ffe5e5] rounded-xl hover:bg-[#ffcccc] transition-colors border-2 border-white shadow-sm w-full md:w-auto">
                        <input type="checkbox" name="remove_image" value="1" class="w-4 h-4 rounded border-gray-300 text-rose-500 focus:ring-rose-500 cursor-pointer shadow-inner">
                        <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Hapus</span>
                    </label>
                </div>
            @endif

            <input type="file" name="image" accept="image/*" class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:bg-[#e0fbfc] file:text-[#4bc6b9] file:shadow-sm cursor-pointer bg-white rounded-[1.5rem] p-2 border-4 border-white shadow-inner">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Judul Promo / Headline (Opsional)</label>
                <input type="text" name="title" value="{{ old('title', $promo->title) }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: Diskon 50% Top Up!">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Teks Badge (Opsional)</label>
                <input type="text" name="badge_text" value="{{ old('badge_text', $promo->badge_text) }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: HOT DEALS">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Deskripsi Singkat (Opsional)</label>
            <textarea name="description" rows="3" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Kosongkan jika gambar sudah memuat semua informasi.">{{ old('description', $promo->description) }}</textarea>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Link Tujuan / URL Promo (Opsional)</label>
            <input type="url" name="link" value="{{ old('link', $promo->link) }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: https://wiboost.com/order/kategori">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Emoji (Opsional)</label>
                <input type="text" name="emoji" value="{{ old('emoji', $promo->emoji) }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition text-2xl placeholder-[#a3bbfb]" placeholder="Opsional">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Warna Tema</label>
                @include('admin.promos.partials.theme-select', [
                    'name' => 'theme',
                    'id' => 'promo_theme_select',
                    'options' => $themeOptions,
                    'selectedValue' => (string) old('theme', $promo->theme ?: 'blue'),
                    'placeholder' => 'Biru Langit',
                ])
            </div>
        </div>

        <button type="submit" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-xl py-5 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#4bc6b9]/40 border-4 border-white flex items-center justify-center">
            Simpan Perubahan
        </button>
    </form>
</div>
@endsection
