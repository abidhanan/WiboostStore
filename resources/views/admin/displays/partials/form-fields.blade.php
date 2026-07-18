@php
    $display = $display ?? null;
@endphp

<div class="mb-8 rounded-[2rem] border-4 border-[#f0f5ff] bg-[#f8faff] p-6 shadow-sm">
    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Gambar Display 4:5</label>
    @if($display?->image)
        <div class="mb-5 ml-2 flex max-w-lg flex-col justify-between rounded-[1.5rem] border-4 border-white bg-white p-4 shadow-sm md:flex-row md:items-center">
            <div class="mb-3 flex items-center gap-4 md:mb-0">
                <img src="{{ Storage::url($display->image) }}" class="h-28 w-24 rounded-xl border-2 border-[#bde0fe] object-cover shadow-sm" alt="Gambar display aktif">
            </div>
            <label class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-white bg-[#ffe5e5] px-4 py-2 shadow-sm transition-colors hover:bg-[#ffcccc] md:w-auto">
                <input type="checkbox" name="remove_image" value="1" class="h-4 w-4 cursor-pointer rounded border-gray-300 text-rose-500 shadow-inner focus:ring-rose-500">
                <span class="text-[10px] font-black uppercase tracking-widest text-rose-500">Hapus</span>
            </label>
        </div>
    @endif
    <input type="file" name="image" accept="image/*" class="w-full cursor-pointer rounded-[1.5rem] border-4 border-white bg-white p-2 text-sm font-bold text-[#8faaf3] shadow-inner file:mr-4 file:rounded-full file:border-0 file:bg-[#e0fbfc] file:px-6 file:py-3 file:text-[#4bc6b9] file:shadow-sm">
</div>

<div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Judul Display (Opsional)</label>
        <input type="text" name="title" value="{{ old('title', $display?->title) }}" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]" placeholder="Cth: Promo Spesial Hari Ini">
    </div>
    <div>
        <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Teks Badge (Opsional)</label>
        <input type="text" name="badge_text" value="{{ old('badge_text', $display?->badge_text) }}" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]" placeholder="Cth: INFO">
    </div>
</div>

<div class="mb-6">
    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Deskripsi Singkat (Opsional)</label>
    <textarea name="description" rows="3" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]" placeholder="Kosongkan jika gambar sudah memuat semua informasi.">{{ old('description', $display?->description) }}</textarea>
</div>

<div class="mb-6">
    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Link Tujuan / URL (Opsional)</label>
    <input type="url" name="link" value="{{ old('link', $display?->link) }}" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]" placeholder="Cth: https://wiboostore.com/user/dashboard">
</div>

<div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Emoji (Opsional)</label>
        <input type="text" name="emoji" value="{{ old('emoji', $display?->emoji) }}" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 text-2xl font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]" placeholder="Opsional">
    </div>
    <div>
        <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Warna Tema</label>
        @include('admin.promos.partials.theme-select', [
            'name' => 'theme',
            'id' => $themeSelectId,
            'options' => $themeOptions,
            'selectedValue' => (string) old('theme', $display?->theme ?: 'blue'),
            'placeholder' => 'Biru Langit',
        ])
    </div>
</div>
