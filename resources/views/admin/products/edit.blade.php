@extends('layouts.admin')

@section('title', 'Edit Produk')

@section('content')
<div class="mx-auto max-w-5xl pb-12" style="font-family: 'Nunito', sans-serif;">
    <div class="mb-8 flex items-start gap-4 pl-2">
        <a href="{{ route('admin.products.index') }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border-2 border-white bg-white text-[#5a76c8] shadow-sm transition-colors hover:bg-[#f0f5ff]">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h3 class="text-3xl font-black tracking-tight text-[#2b3a67]">Edit Produk</h3>
            <p class="mt-1 text-sm font-bold text-[#8faaf3]">Perbarui konfigurasi produk untuk checkout, provider, dan operasional toko.</p>
        </div>
    </div>

    @include('admin.products.partials.form', [
        'action' => route('admin.products.update', $product->id),
        'method' => 'PUT',
        'submitLabel' => 'Simpan Perubahan',
        'product' => $product,
    ])
</div>
@endsection
