@extends('layouts.user')

@section('title', 'Dashboard')

@section('content')
<div class="relative overflow-hidden bg-indigo-600 rounded-3xl p-8 mb-10 shadow-xl shadow-indigo-100">
    <div class="relative z-10">
        <h1 class="text-3xl font-bold text-white mb-2">Halo, {{ Auth::user()->name }}! 👋</h1>
        <p class="text-indigo-100 max-w-md">Siap buat sosmed kamu makin populer hari ini? Pilih kategori layanan di bawah.</p>
    </div>
    <div class="absolute -right-10 -bottom-10 opacity-10">
        <svg width="300" height="300" viewBox="0 0 24 24" fill="white"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
    <div class="bg-white p-6 rounded-2xl border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Pesanan</p>
            <p class="text-2xl font-bold">{{ $totalAllTime }}</p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-2xl border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Pesanan Bulan Ini</p>
            <p class="text-2xl font-bold">{{ $totalThisMonth }}</p>
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