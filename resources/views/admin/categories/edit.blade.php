@extends('layouts.admin')

@section('title', 'Edit Kategori')

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
            <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Edit Kategori ✍️</h3>
            <p class="text-[#8faaf3] font-bold text-sm mt-1">Mengubah data: {{ $category->name }}</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2rem] mb-8 font-black shadow-sm flex items-start gap-4">
            <span class="text-2xl mt-1">⚠️</span>
            <div>
                <ul class="list-disc list-inside text-sm font-bold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white p-8">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Nama Kategori</label>
                <input type="text" name="name" required value="{{ old('name', $category->name) }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Kategori Induk</label>
                <select name="parent_id" id="parent_id_select" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                    <option value="">⭐ Jadikan Kategori Utama</option>
                    @foreach($parentOptions as $option)
                        <option value="{{ $option['id'] }}" {{ (string) old('parent_id', $category->parent_id) === (string) $option['id'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Slug (URL Kategori)</label>
            <input type="text" name="slug" required value="{{ old('slug', $category->slug) }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">
        </div>

        <div class="mb-6" id="fulfillment_container">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Tipe Fulfillment <span class="text-amber-500">(Kategori Utama)</span></label>
            <select name="fulfillment_type" id="fulfillment_type_select" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                <option value="auto_api" {{ old('fulfillment_type', $category->fulfillment_type) === 'auto_api' ? 'selected' : '' }}>API Otomatis</option>
                <option value="stock_based" {{ old('fulfillment_type', $category->fulfillment_type) === 'stock_based' ? 'selected' : '' }}>Stok / Gudang Kredensial</option>
                <option value="manual_action" {{ old('fulfillment_type', $category->fulfillment_type) === 'manual_action' ? 'selected' : '' }}>Manual Admin</option>
            </select>
            <p class="text-[10px] font-bold text-amber-500 mt-2 ml-2">Jika kategori ini menjadi sub-kategori, tipe fulfillment akan mengikuti kategori induk.</p>
        </div>

        <div class="mb-6" id="emote_container">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Emote <span class="text-amber-500">(Kategori Utama)</span></label>
            <input type="text" name="emote" value="{{ old('emote', $category->emote) }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition text-2xl">
        </div>

        <div class="mb-6" id="image_container" style="display: none;">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Logo/Ikon <span class="text-emerald-500">(Sub-Kategori)</span></label>
            @if($category->image)
                <div class="mb-4 ml-2 flex items-center justify-between bg-[#f4f9ff] p-3 rounded-2xl border-2 border-transparent max-w-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-white rounded-[1rem] shadow-sm flex items-center justify-center p-2 overflow-hidden border-2 border-[#bde0fe]">
                            <img src="{{ Storage::url($category->image) }}" alt="Logo" class="max-w-full max-h-full object-contain">
                        </div>
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
            <input type="file" name="image" accept="image/*" class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:bg-[#f0f5ff] file:text-[#5a76c8] bg-[#f4f9ff] rounded-[1.5rem] p-2 cursor-pointer hover:file:bg-[#e0ebff] file:transition-colors file:font-black">
        </div>

        <div class="mb-8" id="description_container" style="display: none;">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Deskripsi Layanan <span class="text-emerald-500">(Sub-Kategori)</span></label>
            <textarea name="description" rows="3" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">{{ old('description', $category->description) }}</textarea>
        </div>

        <button type="submit" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-xl py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg border-2 border-white flex justify-center items-center gap-2">Simpan Perubahan ✨</button>
    </form>
</div>

<script>
    const parentSelect = document.getElementById('parent_id_select');
    const descContainer = document.getElementById('description_container');
    const imageContainer = document.getElementById('image_container');
    const emoteContainer = document.getElementById('emote_container');
    const fulfillmentContainer = document.getElementById('fulfillment_container');
    
    function toggleCategoryType() {
        if(parentSelect.value === "") {
            descContainer.style.display = "none";
            imageContainer.style.display = "none";
            emoteContainer.style.display = "block";
            fulfillmentContainer.style.display = "block";
        } else {
            descContainer.style.display = "block";
            imageContainer.style.display = "block";
            emoteContainer.style.display = "none";
            fulfillmentContainer.style.display = "none";
        }
    }

    parentSelect.addEventListener('change', toggleCategoryType);
    toggleCategoryType(); 
</script>
@endsection
