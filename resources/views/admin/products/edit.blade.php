@extends('layouts.admin')
@section('title', 'Edit Produk')
@section('content')
<div class="pb-12 max-w-4xl mx-auto" style="font-family: 'Nunito', sans-serif;">
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
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white p-6 md:p-8">
        @csrf @method('PUT')
        
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
                    <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Tipe Proses Order</label>
                    <select name="process_type" id="process_type" required class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                        <option value="api" {{ old('process_type', $product->process_type) == 'api' ? 'selected' : '' }}>⚡ Otomatis API</option>
                        <option value="account" {{ old('process_type', $product->process_type) == 'account' ? 'selected' : '' }}>📦 Akun Aplikasi (Shared/Private)</option>
                        <option value="number" {{ old('process_type', $product->process_type) == 'number' ? 'selected' : '' }}>📱 Nomor Luar</option>
                        <option value="manual" {{ old('process_type', $product->process_type) == 'manual' ? 'selected' : '' }}>✍️ Proses Manual</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Harga Jual (Rp)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-6 font-black text-[#5a76c8]">Rp</span>
                        <input type="number" name="price" required value="{{ old('price', $product->price) }}" min="0"
                               class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] pl-14 pr-6 py-4 text-[#2b3a67] font-black outline-none transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Provider SKU</label>
                    <input type="text" name="provider_product_id" value="{{ old('provider_product_id', $product->provider_product_id) }}"
                           class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Status Layanan</label>
                    <select name="is_active" required class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition cursor-pointer appearance-none">
                        <option value="1" {{ old('is_active', $product->is_active) == '1' ? 'selected' : '' }}>🟢 Aktif</option>
                        <option value="0" {{ old('is_active', $product->is_active) == '0' ? 'selected' : '' }}>🔴 Nonaktif</option>
                    </select>
                </div>
                
                <div id="stock_reminder_container" style="display: none;">
                    <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Pengingat Stok Minimum</label>
                    <input type="number" name="stock_reminder" value="{{ old('stock_reminder', $product->stock_reminder) }}" min="0"
                           class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition">
                    <p class="text-[10px] font-bold text-[#8faaf3] mt-2 ml-2">Munculkan alert jika sisa stok menyentuh angka ini.</p>
                </div>
            </div>

            <div id="image_upload_container" style="display: none;">
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Gambar / Logo Bendera</label>
                @if($product->image)
                    <div class="mb-3 ml-2 flex items-center gap-3">
                        <img src="{{ Storage::url($product->image) }}" alt="Preview" class="h-14 rounded-xl shadow-sm border-2 border-[#f0f5ff] object-cover">
                        <span class="text-xs font-bold text-[#8faaf3]">Gambar saat ini</span>
                    </div>
                @endif
                <input type="file" name="image" accept="image/*"
                       class="w-full bg-[#f4f9ff] border-2 border-dashed border-[#bde0fe] hover:border-[#5a76c8] rounded-[1.5rem] px-6 py-3 text-[#2b3a67] font-black outline-none transition cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-[#5a76c8] file:text-white hover:file:bg-[#4760a9]">
                <p class="text-[10px] font-bold text-[#8faaf3] mt-2 ml-2">Biarkan kosong jika tidak ingin mengubah gambar.</p>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row gap-4">
                <button type="submit" class="flex-1 bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-lg py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg shadow-[#4bc6b9]/30 border-2 border-white flex justify-center items-center gap-2">
                    Simpan Perubahan ✨
                </button>
                <a href="{{ route('admin.products.index') }}" class="flex-none bg-white border-4 border-[#f0f5ff] text-[#8faaf3] font-black text-lg py-4 px-8 rounded-[1.5rem] hover:bg-[#f4f9ff] transition-colors text-center">Batal</a>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const processTypeSelect = document.getElementById('process_type');
        const stockReminderContainer = document.getElementById('stock_reminder_container');
        const imageUploadContainer = document.getElementById('image_upload_container');

        function toggleFields() {
            const type = processTypeSelect.value;
            stockReminderContainer.style.display = (type === 'account' || type === 'number') ? 'block' : 'none';
            imageUploadContainer.style.display = (type === 'number') ? 'block' : 'none';
        }

        processTypeSelect.addEventListener('change', toggleFields);
        toggleFields(); 
    });
</script>
@endsection