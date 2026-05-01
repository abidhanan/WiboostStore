@extends('layouts.admin')
@section('title', 'Tambah Produk Baru')
{{-- Tidak menggunakan admin_header_subtitle di header atas karena kita buat kustom di dalam body --}}

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-12 relative z-10 w-full min-w-0 max-w-4xl mx-auto">
    
    <div class="mb-5 sm:mb-6">
        <a href="{{ route('admin.products.index') }}" class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-white rounded-2xl border-4 border-white shadow-lg shadow-[#bde0fe]/40 text-[#5a76c8] hover:bg-[#f4f9ff] hover:-translate-x-1 active:scale-95 transition-all" title="Kembali">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
    </div>

    <div class="mb-8 sm:mb-10">
        <span class="inline-block px-4 py-1.5 bg-[#e6fff7] text-emerald-500 font-black rounded-full mb-3 text-[9px] sm:text-[10px] tracking-widest uppercase border-2 border-white shadow-sm">
            Katalog Baru
        </span>
        <h2 class="text-3xl sm:text-4xl font-black text-[#2b3a67] tracking-tight mb-3 drop-shadow-sm">Tambah Produk ✨</h2>
        <p class="text-xs sm:text-sm font-bold text-[#8faaf3] bg-white/70 backdrop-blur-sm p-4 rounded-2xl border-2 border-white shadow-sm inline-block max-w-2xl leading-relaxed">
            Atur provider, metode pemrosesan, dan detail checkout produk dari satu tempat.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-6 sm:mb-8 p-5 sm:p-6 bg-[#ffe5e5] border-4 border-white rounded-[2rem] shadow-lg shadow-[#ff6b6b]/20">
            <div class="flex items-center gap-2 mb-2 text-[#ff6b6b]">
                <span class="text-xl">⚠️</span>
                <p class="font-black text-sm uppercase tracking-widest">Terdapat Kesalahan</p>
            </div>
            <ul class="list-disc list-inside text-xs sm:text-sm font-bold text-rose-500/80">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 sm:space-y-8">
        @csrf

        <div class="bg-white/90 backdrop-blur-md rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white shadow-xl shadow-[#bde0fe]/20 p-6 sm:p-8">
            <div class="mb-6">
                <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] text-[#8faaf3] mb-1">Info Utama</p>
                <h3 class="text-xl sm:text-2xl font-black text-[#2b3a67]">Identitas Produk</h3>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="block text-[10px] sm:text-xs font-black uppercase tracking-widest text-[#5a76c8] mb-2 pl-2">Nama Produk / Layanan <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full bg-[#f4f9ff] border-4 border-white rounded-[1.2rem] sm:rounded-[1.5rem] px-5 py-3.5 sm:py-4 text-[#2b3a67] font-black focus:border-[#bde0fe] outline-none shadow-inner text-xs sm:text-sm placeholder-[#a3bbfb] transition-colors"
                        placeholder="Contoh: 1000 Followers Instagram HQ">
                </div>

                <div>
                    <label class="block text-[10px] sm:text-xs font-black uppercase tracking-widest text-[#5a76c8] mb-2 pl-2">Deskripsi Layanan</label>
                    <textarea name="description" rows="4"
                        class="w-full bg-[#f4f9ff] border-4 border-white rounded-[1.2rem] sm:rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-bold focus:border-[#bde0fe] outline-none shadow-inner text-xs sm:text-sm placeholder-[#a3bbfb] transition-colors resize-none leading-relaxed"
                        placeholder="Jelaskan detail layanan, ketentuan, manfaat, atau instruksi penting yang perlu dibaca pembeli.">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[10px] sm:text-xs font-black uppercase tracking-widest text-[#5a76c8] mb-2 pl-2">Kategori <span class="text-rose-500">*</span></label>
                        <select name="category_id" required
                            class="w-full bg-[#f4f9ff] border-4 border-white rounded-[1.2rem] sm:rounded-[1.5rem] px-5 py-3.5 sm:py-4 text-[#2b3a67] font-black focus:border-[#bde0fe] outline-none shadow-inner text-xs sm:text-sm transition-colors cursor-pointer appearance-none">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->breadcrumb_name ?? $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] sm:text-xs font-black uppercase tracking-widest text-[#5a76c8] mb-2 pl-2">Tipe Proses Order <span class="text-rose-500">*</span></label>
                        <select name="process_type" required
                            class="w-full bg-[#f4f9ff] border-4 border-white rounded-[1.2rem] sm:rounded-[1.5rem] px-5 py-3.5 sm:py-4 text-[#2b3a67] font-black focus:border-[#bde0fe] outline-none shadow-inner text-xs sm:text-sm transition-colors cursor-pointer appearance-none">
                            <option value="api" {{ old('process_type') == 'api' ? 'selected' : '' }}>Otomatis (API Provider)</option>
                            <option value="manual" {{ old('process_type') == 'manual' ? 'selected' : '' }}>Manual (Kerjakan Sendiri)</option>
                            <option value="account" {{ old('process_type') == 'account' ? 'selected' : '' }}>Kirim Akun (Stok Gudang)</option>
                            <option value="number" {{ old('process_type') == 'number' ? 'selected' : '' }}>Kirim Nomor (Stok Gudang)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white/90 backdrop-blur-md rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white shadow-xl shadow-[#bde0fe]/20 p-6 sm:p-8">
            <div class="mb-6">
                <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] text-[#8faaf3] mb-1">Pengaturan Tarif</p>
                <h3 class="text-xl sm:text-2xl font-black text-[#2b3a67]">Provider & Harga</h3>
            </div>

            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[10px] sm:text-xs font-black uppercase tracking-widest text-[#5a76c8] mb-2 pl-2">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="price" value="{{ old('price', 0) }}" required min="0"
                            class="w-full bg-[#e6fff7] border-4 border-white rounded-[1.2rem] sm:rounded-[1.5rem] px-5 py-3.5 sm:py-4 text-emerald-600 font-black focus:border-emerald-200 outline-none shadow-inner text-sm transition-colors"
                            placeholder="Contoh: 15000">
                    </div>

                    <div>
                        <label class="block text-[10px] sm:text-xs font-black uppercase tracking-widest text-[#5a76c8] mb-2 pl-2">ID/Kode Provider</label>
                        <input type="text" name="provider_product_id" value="{{ old('provider_product_id') }}"
                            class="w-full bg-[#f4f9ff] border-4 border-white rounded-[1.2rem] sm:rounded-[1.5rem] px-5 py-3.5 sm:py-4 text-[#2b3a67] font-mono font-black focus:border-[#bde0fe] outline-none shadow-inner text-xs sm:text-sm placeholder-[#a3bbfb] transition-colors"
                            placeholder="Contoh: S1234">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[10px] sm:text-xs font-black uppercase tracking-widest text-[#5a76c8] mb-2 pl-2">Sumber Provider</label>
                        <select name="provider_source"
                            class="w-full bg-[#f4f9ff] border-4 border-white rounded-[1.2rem] sm:rounded-[1.5rem] px-5 py-3.5 sm:py-4 text-[#2b3a67] font-black focus:border-[#bde0fe] outline-none shadow-inner text-xs sm:text-sm transition-colors cursor-pointer appearance-none">
                            <option value="">-- Pilih Provider --</option>
                            <option value="digiflazz" {{ old('provider_source') == 'digiflazz' ? 'selected' : '' }}>Digiflazz</option>
                            <option value="ordersosmed" {{ old('provider_source') == 'ordersosmed' ? 'selected' : '' }}>OrderSosmed</option>
                            <option value="manual" {{ old('provider_source') == 'manual' ? 'selected' : '' }}>Manual / Gudang</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] sm:text-xs font-black uppercase tracking-widest text-[#5a76c8] mb-2 pl-2">Kuantitas (Khusus SMM) <span class="text-rose-500">*</span></label>
                        <input type="number" name="provider_quantity" value="{{ old('provider_quantity', 1) }}" required min="1"
                            class="w-full bg-[#f4f9ff] border-4 border-white rounded-[1.2rem] sm:rounded-[1.5rem] px-5 py-3.5 sm:py-4 text-[#2b3a67] font-black focus:border-[#bde0fe] outline-none shadow-inner text-sm transition-colors"
                            placeholder="Default: 1">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white/90 backdrop-blur-md rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white shadow-xl shadow-[#bde0fe]/20 p-6 sm:p-8">
            <div class="mb-6">
                <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] text-[#8faaf3] mb-1">Tampilan</p>
                <h3 class="text-xl sm:text-2xl font-black text-[#2b3a67]">Visual & Status</h3>
            </div>

            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[10px] sm:text-xs font-black uppercase tracking-widest text-[#5a76c8] mb-2 pl-2">Ikon Emoji</label>
                        <input type="text" name="emote" value="{{ old('emote') }}"
                            class="w-full bg-[#f4f9ff] border-4 border-white rounded-[1.2rem] sm:rounded-[1.5rem] px-5 py-3.5 sm:py-4 text-[#2b3a67] font-black text-xl focus:border-[#bde0fe] outline-none shadow-inner transition-colors"
                            placeholder="📦">
                        <p class="text-[9px] sm:text-[10px] font-bold text-[#8faaf3] mt-2 pl-2">Gunakan 1 karakter emoji saja.</p>
                    </div>

                    <div>
                        <label class="block text-[10px] sm:text-xs font-black uppercase tracking-widest text-[#5a76c8] mb-2 pl-2">Status Produk</label>
                        <select name="is_active"
                            class="w-full bg-[#f4f9ff] border-4 border-white rounded-[1.2rem] sm:rounded-[1.5rem] px-5 py-3.5 sm:py-4 text-[#2b3a67] font-black focus:border-[#bde0fe] outline-none shadow-inner text-xs sm:text-sm transition-colors cursor-pointer appearance-none">
                            <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>Aktif (Tampil)</option>
                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Nonaktif (Sembunyikan)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] sm:text-xs font-black uppercase tracking-widest text-[#5a76c8] mb-2 pl-2">Gambar Produk (Opsional)</label>
                    <div class="w-full bg-[#f4f9ff] border-4 border-dashed border-[#bde0fe] rounded-[1.2rem] sm:rounded-[1.5rem] p-4 text-center hover:bg-[#e0fbfc] transition-colors relative">
                        <input type="file" name="image" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <span class="text-3xl block mb-1">🖼️</span>
                        <span class="text-xs sm:text-sm font-black text-[#5a76c8]">Pilih File Gambar</span>
                        <p class="text-[9px] sm:text-[10px] font-bold text-[#8faaf3] mt-1">Format: JPG, PNG, WEBP. Maks: 2MB.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-[#5a76c8] hover:bg-[#4760a9] text-white py-4 sm:py-5 rounded-[1.5rem] sm:rounded-[2rem] font-black text-sm sm:text-base border-4 border-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95">
                <span class="text-lg sm:text-xl">💾</span> Simpan Produk Baru
            </button>
        </div>
    </form>
</div>
@endsection