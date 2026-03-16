@extends('layouts.admin')

@section('title', 'Tambah Produk')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-12 max-w-4xl mx-auto">
    <div class="flex items-center gap-4 mb-8 pl-2">
        <a href="{{ route('admin.products.index') }}" class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-[#5a76c8] hover:bg-[#f0f5ff] transition-colors border-2 border-white shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Tambah Produk Baru 🛍️</h3>
            <p class="text-[#8faaf3] font-bold text-sm mt-1">Masukkan detail layanan yang ingin dijual.</p>
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

    <form action="{{ route('admin.products.store') }}" method="POST" class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white p-6 md:p-8">
        @csrf
        
        <div class="space-y-6">
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Nama Produk / Layanan</label>
                <input type="text" name="name" required value="{{ old('name') }}"
                       class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" 
                       placeholder="Contoh: 1000 Followers Instagram">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Pilih Kategori</label>
                    <select name="category_id" required class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                        <option value="" disabled selected>-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Provider ID / SKU (Opsional)</label>
                    <input type="text" name="provider_product_id" value="{{ old('provider_product_id') }}"
                           class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" 
                           placeholder="Contoh: ML86 (Kosongkan jika manual)">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Harga Jual (Rp)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-6 font-black text-[#5a76c8]">Rp</span>
                        <input type="number" name="price" required value="{{ old('price') }}" min="0"
                               class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] pl-14 pr-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" 
                               placeholder="15000">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Status Layanan</label>
                    <select name="is_active" required class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>🟢 Aktif (Bisa dibeli)</option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>🔴 Nonaktif (Sembunyikan)</option>
                    </select>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row gap-4">
                <button type="submit" class="flex-1 bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-lg py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg shadow-[#5a76c8]/30 border-2 border-white flex justify-center items-center gap-2">
                    Simpan Produk 🚀
                </button>
                <a href="{{ route('admin.products.index') }}" class="flex-none bg-white border-4 border-[#f0f5ff] text-[#8faaf3] font-black text-lg py-4 px-8 rounded-[1.5rem] hover:bg-[#f4f9ff] transition-colors text-center">
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>
@endsection