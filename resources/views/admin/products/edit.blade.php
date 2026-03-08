@extends('layouts.admin')

@section('title', 'Edit Produk')

@section('content')
<div class="mb-6">
    <h3 class="text-xl font-bold text-gray-800">Edit Produk: {{ $product->name }}</h3>
</div>

<div class="max-w-3xl bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Nama Produk</label>
                <input type="text" name="name" value="{{ $product->name }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Kategori</label>
                    <select name="category_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Provider ID</label>
                    <input type="text" name="provider_product_id" value="{{ $product->provider_product_id }}" class="w-full px-4 py-3 rounded-xl border border-gray-200" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Harga Jual (Rp)</label>
                    <input type="number" name="price" value="{{ $product->price }}" class="w-full px-4 py-3 rounded-xl border border-gray-200" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Ganti Gambar (Opsional)</label>
                    <input type="file" name="image" class="w-full text-sm text-gray-500">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" id="is_active" {{ $product->is_active ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 rounded">
                <label for="is_active" class="text-sm font-bold text-gray-700">Produk Aktif (Muncul di Toko)</label>
            </div>

            <div class="pt-4">
                <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-indigo-700 transition">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.products.index') }}" class="ml-4 text-gray-500 font-bold">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection