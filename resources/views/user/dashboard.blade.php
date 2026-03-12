@extends('layouts.user')

@section('title', 'Dashboard Saya')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
</style>

<div class="wiboost-font">
    <div class="relative overflow-hidden bg-wiboost-sky rounded-[2.5rem] p-8 md:p-10 mb-10 shadow-xl shadow-[#bde0fe]/50 border-4 border-white flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div class="absolute -right-10 -top-10 text-8xl opacity-40">☁️</div>
        <div class="absolute bottom-5 right-1/3 text-4xl opacity-50">✨</div>

        <div class="relative z-10">
            <p class="text-[#5a76c8] font-black tracking-wide mb-1 uppercase text-sm">Dashboard Pelanggan</p>
            <h1 class="text-3xl md:text-4xl font-black text-[#2b3a67] mb-2">Halo, {{ Auth::user()->name }}! 👋</h1>
            <p class="text-[#4a5f96] max-w-md font-bold">Siap buat sosmed dan game kamu makin GG hari ini? Yuk, pilih layanan di bawah.</p>
        </div>
        
        <div class="relative z-10 bg-white/80 backdrop-blur-md px-8 py-6 rounded-[2rem] border-2 border-white w-full md:w-auto text-center md:text-right shadow-sm">
            <p class="text-[#8faaf3] text-xs font-black uppercase tracking-widest mb-1">Saldo Wiboost</p>
            <p class="text-3xl md:text-4xl font-black text-[#5a76c8] tracking-tight mb-4">Rp {{ number_format(Auth::user()->balance, 0, ',', '.') }}</p>
            <a href="{{ route('user.wallet.index') }}" class="inline-flex items-center justify-center gap-2 bg-[#5a76c8] text-white px-6 py-2.5 rounded-full text-sm font-extrabold hover:bg-[#4760a9] hover:-translate-y-1 transition-all shadow-lg shadow-[#5a76c8]/30 border-2 border-white w-full md:w-auto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Top Up Saldo
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-10">
        <div class="bg-white p-6 rounded-[2rem] border-4 border-white shadow-lg shadow-[#bde0fe]/30 flex items-center gap-5 hover:-translate-y-1 transition">
            <div class="w-14 h-14 bg-[#f0f5ff] rounded-2xl flex items-center justify-center text-[#5a76c8] border-2 border-white shadow-inner">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <div>
                <p class="text-xs font-black text-[#8faaf3] uppercase tracking-wider">Total Pesanan</p>
                <p class="text-2xl font-black text-[#2b3a67]">{{ $totalAllTime }}</p>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-[2rem] border-4 border-white shadow-lg shadow-[#bde0fe]/30 flex items-center gap-5 hover:-translate-y-1 transition">
            <div class="w-14 h-14 bg-[#fff5eb] rounded-2xl flex items-center justify-center text-amber-500 border-2 border-white shadow-inner">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-black text-amber-400 uppercase tracking-wider">Bulan Ini</p>
                <p class="text-2xl font-black text-[#2b3a67]">{{ $totalThisMonth }}</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-[2rem] border-4 border-white shadow-lg shadow-[#bde0fe]/30 flex items-center gap-5 hover:-translate-y-1 transition">
            <div class="w-14 h-14 bg-[#e6fff7] rounded-2xl flex items-center justify-center text-emerald-500 border-2 border-white shadow-inner">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-black text-emerald-400 uppercase tracking-wider">Pengeluaran</p>
                <p class="text-xl font-black text-[#2b3a67]">Rp {{ number_format($totalSpent, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 mb-6 pl-2">
        <span class="text-2xl">🎯</span>
        <h3 class="text-2xl font-black text-[#2b3a67]">Pilih Kategori</h3>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @foreach($categories as $category)
        <a href="{{ route('user.order.category', $category->slug) }}" class="group bg-white p-6 rounded-[2rem] border-4 border-white hover:border-[#bde0fe] shadow-lg shadow-[#bde0fe]/20 hover:-translate-y-2 transition-all text-center">
            <div class="w-16 h-16 bg-[#f0f5ff] rounded-2xl mx-auto mb-4 flex items-center justify-center group-hover:scale-110 group-hover:bg-[#e0fbfc] text-[#5a76c8] transition-all border-2 border-white shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <span class="text-sm font-black text-[#2b3a67]">{{ $category->name }}</span>
        </a>
        @endforeach
    </div>
</div>
@endsection