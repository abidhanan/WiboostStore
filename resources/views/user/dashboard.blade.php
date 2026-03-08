@extends('layouts.user')

@section('title', 'Dashboard')

@section('content')
<div class="relative overflow-hidden bg-indigo-600 rounded-3xl p-8 mb-10 shadow-xl shadow-indigo-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
    <div class="relative z-10">
        <h1 class="text-3xl font-bold text-white mb-2">Halo, {{ Auth::user()->name }}! 👋</h1>
        <p class="text-indigo-100 max-w-md">Siap buat sosmed dan game kamu makin GG hari ini? Pilih kategori di bawah.</p>
    </div>
    
    <div class="relative z-10 bg-white/20 backdrop-blur-md px-6 py-5 rounded-2xl border border-white/30 w-full md:w-auto text-left md:text-right shadow-inner">
        <p class="text-indigo-100 text-sm font-bold uppercase tracking-widest mb-1">Saldo Wiboost</p>
        <p class="text-3xl font-extrabold text-white tracking-tight mb-3">Rp {{ number_format(Auth::user()->balance, 0, ',', '.') }}</p>
        <a href="{{ route('user.wallet.index') }}" class="inline-flex items-center gap-2 bg-white text-indigo-600 px-5 py-2 rounded-full text-sm font-bold hover:bg-indigo-50 hover:scale-105 transition-all shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Top Up Saldo
        </a>
    </div>

    <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
        <svg width="300" height="300" viewBox="0 0 24 24" fill="white"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <div class="bg-white p-6 rounded-2xl border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Pesanan</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalAllTime }}</p>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-2xl border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Pesanan Bulan Ini</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalThisMonth }}</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Pengeluaran</p>
            <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalSpent, 0, ',', '.') }}</p>
        </div>
    </div>
</div>

<h3 class="text-xl font-bold text-gray-900 mb-6">Pilih Kategori</h3>
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
    @foreach($categories as $category)
    <a href="{{ route('user.order.category', $category->slug) }}" class="group bg-white p-6 rounded-2xl border border-gray-100 hover:border-indigo-500 transition-all hover:shadow-lg text-center">
        <div class="w-14 h-14 bg-gray-50 rounded-full mx-auto mb-4 flex items-center justify-center group-hover:bg-indigo-50 group-hover:text-indigo-600 transition">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <span class="text-sm font-bold text-gray-800">{{ $category->name }}</span>
    </a>
    @endforeach
</div>
@endsection