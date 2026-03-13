@extends('layouts.admin')

@section('title', 'Edit Produk')

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
            <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Edit Produk ✍️</h3>
            <p class="text-[#8faaf3] font-bold text-sm mt-1">Mengubah data: {{ $product->name }}</p>
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

    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white p-6 md:p-8">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <div>
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Nama Produk / Layanan</label>
                <input type="text" name="name" required value="{{ old('name', $product->name) }}"
                       class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Kategori</label>
                    <select name="category_id" required class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (old('category_id', $product->category_id) == $cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Provider ID / SKU</label>
                    <input type="text" name="provider_product_id" value="{{ old('provider_product_id', $product->provider_product_id) }}"
                           class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Harga Jual (Rp)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-6 font-black text-[#5a76c8]">Rp</span>
                        <input type="number" name="price" required value="{{ old('price', $product->price) }}"
                               class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] pl-14 pr-6 py-4 text-[#2b3a67] font-black outline-none transition">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Ganti Gambar (Opsional)</label>
                    <input type="file" name="image" class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:text-sm file:font-black file:bg-[#f0f5ff] file:text-[#5a76c8] hover:file:bg-[#e0ebff] file:transition-colors cursor-pointer bg-[#f4f9ff] rounded-[1.5rem] p-2 border-2 border-transparent">
                </div>
            </div>

            <label class="relative bg-gradient-to-r from-[#f0f5ff] to-white border-2 border-[#e0fbfc] rounded-[1.5rem] p-5 flex items-center gap-4 cursor-pointer hover:border-[#bde0fe] transition-colors mt-4">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="sr-only peer">
                
                <div class="w-6 h-6 rounded-md border-2 border-[#8faaf3] peer-checked:bg-[#4bc6b9] peer-checked:border-[#4bc6b9] flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                </div>
                
                <div>
                    <p class="font-black text-[#2b3a67]">Produk Aktif</p>
                    <p class="text-xs font-bold text-[#8faaf3]">Munculkan produk ini di halaman toko</p>
                </div>
            </label>

            <div class="pt-6 flex flex-col sm:flex-row gap-4">
                <button type="submit" class="flex-1 bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-lg py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg shadow-[#4bc6b9]/30 border-2 border-white flex justify-center items-center gap-2">
                    Simpan Perubahan ✨
                </button>
                <a href="{{ route('admin.products.index') }}" class="flex-none bg-white border-4 border-[#f0f5ff] text-[#8faaf3] font-black text-lg py-4 px-8 rounded-[1.5rem] hover:bg-[#f4f9ff] transition-colors text-center">
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>
@endsection