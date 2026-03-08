@extends('layouts.admin')

@section('title', 'Kelola Produk')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h3 class="text-lg font-bold text-gray-800">Daftar Produk & Layanan</h3>
    <a href="{{ route('admin.products.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-xl font-bold hover:bg-indigo-700 transition">
        + Tambah Produk
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Produk</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Kategori</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Provider ID</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Harga</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($products as $product)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-6 py-4 font-bold text-gray-800">{{ $product->name }}</td>
                <td class="px-6 py-4"><span class="bg-blue-50 text-blue-600 px-2 py-1 rounded-md text-xs font-bold">{{ $product->category->name }}</span></td>
                <td class="px-6 py-4 font-mono text-sm text-gray-500">{{ $product->provider_product_id }}</td>
                <td class="px-6 py-4 font-bold text-gray-900">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                <td class="px-6 py-4 text-center">
                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Hapus produk ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-sm uppercase">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic border-t border-gray-100">Belum ada produk. Silakan tambah produk baru.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection