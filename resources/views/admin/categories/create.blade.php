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
            <p class="text-[#8faaf3] font-bold text-sm mt-1">Bisa dipakai untuk kategori utama, sub-kategori, sampai kategori bertingkat.</p>
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
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Nama Kategori / Aplikasi</label>
                <input type="text" name="name" required value="{{ old('name') }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition" placeholder="Cth: Mobile Legends / Top Up Game">
            </div>
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Kategori Induk</label>
                <select name="parent_id" id="parent_id_select" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                    <option value="">⭐ Jadikan Kategori Utama</option>
                    @foreach($parentOptions as $option)
                        <option value="{{ $option['id'] }}" {{ (string) old('parent_id') === (string) $option['id'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
                <p class="text-[10px] font-bold text-amber-500 mt-2 ml-2">Pilih parent jika ingin membuat struktur seperti Suntik Sosmed / Like / Like Indonesia.</p>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Slug (URL Kategori)</label>
            <input type="text" name="slug" value="{{ old('slug') }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition" placeholder="Kosongkan agar otomatis dari Nama">
        </div>

        <div class="mb-6" id="fulfillment_container">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Tipe Fulfillment <span class="text-amber-500">(Khusus Kategori Utama)</span></label>
            <select name="fulfillment_type" id="fulfillment_type_select" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                <option value="auto_api" {{ old('fulfillment_type', 'auto_api') === 'auto_api' ? 'selected' : '' }}>API Otomatis</option>
                <option value="stock_based" {{ old('fulfillment_type') === 'stock_based' ? 'selected' : '' }}>Stok / Gudang Kredensial</option>
                <option value="manual_action" {{ old('fulfillment_type') === 'manual_action' ? 'selected' : '' }}>Manual Admin</option>
            </select>
            <p class="text-[10px] font-bold text-amber-500 mt-2 ml-2">Kategori turunan otomatis mengikuti tipe fulfillment dari parent teratasnya.</p>
        </div>

        <div class="mb-6" id="emote_container">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Emote <span class="text-amber-500">(Khusus Kategori Utama)</span></label>
            <input type="text" name="emote" value="{{ old('emote') }}" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition text-2xl" placeholder="Cth: 🎮 atau 📱">
        </div>

        <div class="mb-6" id="image_container" style="display: none;">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Logo/Ikon <span class="text-emerald-500">(Khusus Sub-Kategori)</span></label>
            <input type="file" name="image" accept="image/*" class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:bg-[#f0f5ff] file:text-[#5a76c8] bg-[#f4f9ff] rounded-[1.5rem] p-2 cursor-pointer hover:file:bg-[#e0ebff] file:transition-colors file:font-black">
            <p class="text-xs font-bold text-amber-500 mt-2 ml-2">💡 Disarankan gambar kotak (1:1) format PNG transparan maksimal 2MB.</p>
        </div>

        <div class="mb-8" id="description_container" style="display: none;">
            <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Deskripsi Layanan <span class="text-emerald-500">(Khusus Sub-Kategori)</span></label>
            <textarea name="description" rows="3" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition" placeholder="Deskripsi layanan untuk aplikasi ini...">{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-xl py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg border-2 border-white flex justify-center items-center gap-2">Simpan Kategori 🚀</button>
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
            // Jika Kategori Utama
            descContainer.style.display = "none";
            imageContainer.style.display = "none";
            emoteContainer.style.display = "block";
            fulfillmentContainer.style.display = "block";
        } else {
            // Jika Sub-Kategori
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
