@extends('layouts.admin')

@section('title', 'Edit Produk')

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

<div class="wiboost-font mx-auto max-w-5xl pb-12 relative z-10">
    
    <div class="absolute top-10 right-0 text-5xl animate-float opacity-30 pointer-events-none hidden md:block z-0">✏️</div>
    <div class="absolute top-1/3 -left-10 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden md:block z-0">✨</div>
    <div class="absolute bottom-20 -right-5 text-6xl animate-float opacity-20 pointer-events-none hidden md:block z-0">☁️</div>

    <div class="mb-10 flex items-start gap-5 pl-2 relative z-10">
        <a href="{{ route('admin.products.index') }}" class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.2rem] border-4 border-white bg-white/90 backdrop-blur-sm text-[#5a76c8] shadow-lg shadow-[#bde0fe]/30 transition-transform active:scale-95 hover:bg-[#e0fbfc]">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <div class="inline-block px-4 py-1 bg-[#fff5eb] text-amber-500 font-black rounded-full mb-2 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
                Mode Edit
            </div>
            <h3 class="text-3xl md:text-4xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm">Edit Produk 📝</h3>
            <p class="mt-2 text-sm font-bold text-[#8faaf3] bg-white/50 px-4 py-2 rounded-xl border border-white shadow-sm inline-block">Mengubah konfigurasi untuk: <span class="font-black text-[#5a76c8]">{{ $product->name }}</span></p>
        </div>
    </div>

    <div class="relative z-10">
        @include('admin.products.partials.form', [
            'action' => route('admin.products.update', $product->id),
            'method' => 'PUT',
            'submitLabel' => 'Simpan Perubahan ✨',
            'product' => $product,
        ])
    </div>
</div>
@endsection