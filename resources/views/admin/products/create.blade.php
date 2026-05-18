@extends('layouts.admin')

@section('title', 'Tambah Produk Baru')

@section('content')
<div class="wiboost-font mx-auto max-w-5xl pb-12 relative z-10">
    <div class="mb-10 flex items-start gap-5 pl-2 relative z-10">
        <a href="{{ route('admin.products.index') }}" class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.2rem] border-4 border-white bg-white/90 backdrop-blur-sm text-[#5a76c8] shadow-lg shadow-[#bde0fe]/30 transition-transform active:scale-95 hover:bg-[#e0fbfc]">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <div class="inline-block px-4 py-1 bg-[#e6fff7] text-emerald-500 font-black rounded-full mb-2 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
                Katalog Baru
            </div>
            <h3 class="text-3xl md:text-4xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm">Tambah Produk</h3>
            <p class="mt-2 text-sm font-bold text-[#8faaf3] bg-white/50 px-4 py-2 rounded-xl border border-white shadow-sm inline-block">Atur produk API provider atau stok akun premium dari satu form yang sama.</p>
        </div>
    </div>

    @include('admin.products.partials.form', [
        'action' => route('admin.products.store'),
        'method' => 'POST',
        'submitLabel' => 'Simpan Produk',
    ])
</div>
@endsection
