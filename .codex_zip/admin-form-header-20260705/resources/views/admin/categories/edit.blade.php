@extends('layouts.admin')

@section('title', 'Edit Kategori')

@section('content')
<style>
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="wiboost-font pb-12 max-w-4xl mx-auto relative z-10">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.categories.index'),
        'badge' => 'Mode Edit',
        'title' => 'Edit Kategori',
        'description' => 'Mengubah data ' . $category->name . '.',
    ])

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-5 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 flex items-start gap-4 relative z-10">
            <span class="text-3xl mt-1 drop-shadow-sm">⚠️</span>
            <div>
                <p class="mb-3 text-lg">Aduh, gagal memperbarui! Cek dulu bagian ini:</p>
                <ul class="list-disc list-inside text-sm font-bold bg-white/50 p-4 rounded-2xl border border-white">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @php
        $selectedParentId = (string) old('parent_id', $category->parent_id ?? '');
        $parentSelectOptions = collect([['value' => '', 'label' => 'Jadikan Kategori Utama']])
            ->merge(collect($parentOptions)->map(fn ($option) => [
                'value' => (string) $option['id'],
                'label' => $option['label'],
            ]))
            ->values()
            ->all();

        $fulfillmentSelectOptions = [
            ['value' => 'auto_api', 'label' => 'API Otomatis'],
            ['value' => 'stock_based', 'label' => 'Stok / Gudang Kredensial'],
        ];
    @endphp
    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data" class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-10 relative z-10 overflow-visible">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Nama Kategori</label>
                <input type="text" name="name" required value="{{ old('name', $category->name) }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Kategori Induk</label>
                @include('admin.categories.partials.theme-select', [
                    'name' => 'parent_id',
                    'id' => 'parent_id_select',
                    'options' => $parentSelectOptions,
                    'selectedValue' => $selectedParentId,
                    'placeholder' => 'Jadikan Kategori Utama',
                ])
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Slug (URL Kategori)</label>
            <input type="text" name="slug" required value="{{ old('slug', $category->slug) }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]">
        </div>

        <div class="mb-6" id="fulfillment_container">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Tipe Fulfillment <span class="text-amber-500 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-100 text-[10px] uppercase">(Kategori Utama)</span></label>
            @include('admin.categories.partials.theme-select', [
                'name' => 'fulfillment_type',
                'id' => 'fulfillment_type_select',
                'options' => $fulfillmentSelectOptions,
                'selectedValue' => (string) old('fulfillment_type', $category->fulfillment_type ?: 'auto_api'),
                'placeholder' => 'API Otomatis',
            ])
            <p class="text-[10px] font-black text-[#8faaf3] mt-3 ml-2 uppercase tracking-widest bg-[#f4f9ff] inline-block px-3 py-1 rounded-md border border-white shadow-sm">Jika kategori ini menjadi sub-kategori, tipe fulfillment akan mengikuti parent.</p>
        </div>

        <div class="mb-6" id="emote_container">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Emote <span class="text-amber-500 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-100 text-[10px] uppercase">(Opsional)</span></label>
            <input type="text" name="emote" value="{{ old('emote', $category->emote) }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition text-2xl placeholder-[#a3bbfb]">
        </div>

        <div class="mb-6" id="image_container">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Logo/Ikon <span class="text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100 text-[10px] uppercase">(Opsional)</span></label>
            
            @if($category->image)
                <div class="mb-4 ml-2 flex items-center justify-between bg-[#f8faff] p-4 rounded-[1.5rem] border-4 border-white shadow-sm max-w-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-white rounded-2xl shadow-inner flex items-center justify-center p-2 overflow-hidden border-2 border-[#bde0fe]">
                            <img src="{{ Storage::url($category->image) }}" alt="Logo" class="max-w-full max-h-full object-contain">
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-[#5a76c8] block bg-white px-2 py-1 rounded-md border border-[#e0fbfc]">Gambar Aktif</span>
                        </div>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer group px-4 py-2 bg-[#ffe5e5] rounded-[1rem] hover:bg-[#ffcccc] transition-colors border-2 border-white shadow-sm active:scale-95">
                        <input type="checkbox" name="remove_image" value="1" class="w-4 h-4 rounded border-gray-300 text-rose-500 focus:ring-rose-500 cursor-pointer shadow-inner">
                        <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Hapus</span>
                    </label>
                </div>
            @endif
            
            <input type="file" name="image" accept="image/*" class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:bg-[#e0fbfc] file:text-[#4bc6b9] file:shadow-sm bg-[#f4f9ff] border-4 border-white shadow-inner rounded-[1.5rem] p-2 cursor-pointer hover:file:bg-[#bde0fe] file:transition-colors file:font-black">
        </div>

        <div class="mb-10" id="description_container" style="display: none;">
            <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Deskripsi Layanan <span class="text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100 text-[10px] uppercase">(Sub-Kategori)</span></label>
            <textarea name="description" rows="4" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]">{{ old('description', $category->description) }}</textarea>
        </div>

        <button type="submit" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-xl py-5 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#4bc6b9]/40 border-4 border-white flex justify-center items-center gap-2">
            Simpan Perubahan
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
        imageContainer.style.display = "block";
        emoteContainer.style.display = "block";

        if(parentSelect.value === "") {
            descContainer.style.display = "none";
            fulfillmentContainer.style.display = "block";
        } else {
            descContainer.style.display = "block";
            fulfillmentContainer.style.display = "none";
        }
    }

    parentSelect.addEventListener('change', toggleCategoryType);
    toggleCategoryType(); 
</script>
@endsection
