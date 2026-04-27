@extends('layouts.admin')

@section('title', 'Tambah Kategori')

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

    <div class="absolute top-10 right-0 text-5xl animate-float opacity-30 pointer-events-none hidden md:block z-0">📁</div>
    <div class="absolute top-1/3 -left-10 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden md:block z-0">✨</div>
    <div class="absolute bottom-20 -right-5 text-6xl animate-float opacity-20 pointer-events-none hidden md:block z-0">☁️</div>

    <div class="flex flex-col md:flex-row items-start md:items-center gap-5 mb-10 pl-2 relative z-10">
        <a href="{{ route('admin.categories.index') }}" class="w-14 h-14 bg-white/90 backdrop-blur-sm rounded-[1.2rem] flex items-center justify-center text-[#5a76c8] hover:bg-[#e0fbfc] transition-transform active:scale-95 border-4 border-white shadow-lg shadow-[#bde0fe]/30 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-2 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
                Buat Baru
            </div>
            <h3 class="text-3xl md:text-4xl font-black text-[#2b3a67] tracking-tight drop-shadow-sm">Tambah Kategori 🌟</h3>
            <p class="text-[#8faaf3] font-bold text-sm mt-2 bg-white/50 px-4 py-2 rounded-xl border border-white shadow-sm inline-block">Bisa dipakai untuk kategori utama, sub-kategori, sampai kategori bertingkat.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-5 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 flex items-start gap-4 relative z-10">
            <span class="text-3xl mt-1 drop-shadow-sm">⚠️</span>
            <div>
                <p class="mb-3 text-lg">Aduh, gagal menyimpan! Cek dulu bagian ini:</p>
                <ul class="list-disc list-inside text-sm font-bold bg-white/50 p-4 rounded-2xl border border-white">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-10 relative z-10">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Nama Kategori / Aplikasi</label>
                <input type="text" name="name" required value="{{ old('name') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: Mobile Legends / Top Up Game">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Kategori Induk</label>
                <select name="parent_id" id="parent_id_select" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                    <option value="">⭐ Jadikan Kategori Utama</option>
                    @foreach($parentOptions as $option)
                        <option value="{{ $option['id'] }}" {{ (string) old('parent_id') === (string) $option['id'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
                <p class="text-[10px] font-black text-amber-500 mt-3 ml-2 uppercase tracking-widest bg-amber-50 inline-block px-3 py-1 rounded-md border border-amber-100">💡 Pilih parent untuk struktur sub-kategori.</p>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Slug (URL Kategori)</label>
            <input type="text" name="slug" value="{{ old('slug') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Kosongkan agar otomatis dari Nama">
        </div>

        <div class="mb-6" id="fulfillment_container">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Tipe Fulfillment <span class="text-amber-500 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-100 text-[10px] uppercase">(Khusus Kategori Utama)</span></label>
            <select name="fulfillment_type" id="fulfillment_type_select" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                <option value="auto_api" {{ old('fulfillment_type', 'auto_api') === 'auto_api' ? 'selected' : '' }}>API Otomatis</option>
                <option value="stock_based" {{ old('fulfillment_type') === 'stock_based' ? 'selected' : '' }}>Stok / Gudang Kredensial</option>
                <option value="manual_action" {{ old('fulfillment_type') === 'manual_action' ? 'selected' : '' }}>Manual Admin</option>
            </select>
            <p class="text-[10px] font-black text-[#8faaf3] mt-3 ml-2 uppercase tracking-widest bg-[#f4f9ff] inline-block px-3 py-1 rounded-md border border-white shadow-sm">Kategori turunan akan mengikuti parent teratasnya.</p>
        </div>

        <div class="mb-6" id="emote_container">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Emote <span class="text-amber-500 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-100 text-[10px] uppercase">(Khusus Kategori Utama)</span></label>
            <input type="text" name="emote" value="{{ old('emote') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition text-2xl placeholder-[#a3bbfb]" placeholder="Cth: 🎮 atau 📱">
        </div>

        <div class="mb-6" id="image_container" style="display: none;">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Logo/Ikon <span class="text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100 text-[10px] uppercase">(Khusus Sub-Kategori)</span></label>
            <input type="file" name="image" accept="image/*" class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:bg-[#e0fbfc] file:text-[#4bc6b9] file:shadow-sm bg-[#f4f9ff] border-4 border-white shadow-inner rounded-[1.5rem] p-2 cursor-pointer hover:file:bg-[#bde0fe] file:transition-colors file:font-black">
            <p class="text-[10px] font-black text-amber-500 mt-3 ml-2 uppercase tracking-widest bg-amber-50 inline-block px-3 py-1 rounded-md border border-amber-100">💡 Disarankan gambar kotak (1:1) format PNG transparan maksimal 2MB.</p>
        </div>

        <div class="mb-10" id="description_container" style="display: none;">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Deskripsi Layanan <span class="text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100 text-[10px] uppercase">(Khusus Sub-Kategori)</span></label>
            <textarea name="description" rows="4" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Tuliskan deskripsi atau panduan singkat layanan ini...">{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-xl py-5 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white flex justify-center items-center gap-2">
            Simpan Kategori 🚀
        </button>
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