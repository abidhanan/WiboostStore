@extends('layouts.user')

@section('title', 'Membership - Wiboost Store')

@section('content')
@php
    $user = auth()->user();
    $isReseller = $user?->isReseller() ?? false;
@endphp

<section class="space-y-8">
    <header class="relative mb-8 flex flex-col items-start gap-6 overflow-hidden rounded-[2.5rem] border-4 border-white p-8 shadow-xl md:p-10" style="background: linear-gradient(135deg, #ddd6fe 0%, #ede9fe 48%, #fff7ff 100%); box-shadow: 0 18px 35px rgba(139, 92, 246, 0.24);">
        <span class="mb-3 inline-block rounded-full border-2 border-white bg-white px-4 py-1 text-xs font-black shadow-sm" style="color: #8b5cf6;">Membership</span>
        <div class="relative z-10 min-w-0">
            <h1 class="wiboost-dashboard-greeting mb-2 font-black leading-tight text-[#2b3a67] drop-shadow-sm" style="font-size: clamp(1.45rem, 7vw, 3rem); line-height: 1.08;">
                Yuk Jadi Member!
                <span class="inline-flex align-middle" style="color:#8b5cf6; transform: translateY(-0.06em);">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.5 4.5h11L21 9l-9 11L3 9l3.5-4.5Z M3 9h18" />
                    </svg>
                </span>
            </h1>
            <p class="wiboost-dashboard-subtitle max-w-3xl text-sm font-extrabold leading-relaxed sm:text-base" style="color: #8b5cf6;">
                Nikmati keuntungan potongan harga produk dan layanan yang jauh lebih murah.
            </p>
        </div>
    </header>

    <div class="grid gap-6">
        <div class="rounded-[2rem] border-4 border-white bg-[#f8fbff] p-6 shadow-xl shadow-[#bde0fe]/20 sm:p-8">
            <p class="text-sm font-black uppercase tracking-[0.2em]" style="color: #8b5cf6;">Biaya Membership</p>
            <p class="mt-3 text-lg font-black text-[#8faaf3]" style="text-decoration: line-through;">Rp 50.000</p>
            <h2 class="mt-1 text-3xl font-black text-[#2b3a67]">Rp 30.000 / bulan.</h2>
        </div>

        <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/20 sm:p-8">
            <p class="text-sm font-black uppercase tracking-[0.2em]" style="color: #8b5cf6;">Benefit</p>
            <div class="mt-5 grid gap-3 text-sm font-bold text-[#5a76c8]">
                <p class="rounded-[1.25rem] bg-[#f4f9ff] px-5 py-4">Banyak potongan harga.</p>
                <p class="rounded-[1.25rem] bg-[#f4f9ff] px-5 py-4">Harga produk dan layanan jauh lebih murah.</p>
                <p class="rounded-[1.25rem] bg-[#f4f9ff] px-5 py-4">Lebih banyak bonus lainnya.</p>
            </div>
        </div>
    </div>

    <a href="{{ route('user.reseller.index') }}" class="flex w-full items-center justify-center rounded-full border-4 border-white px-7 py-4 text-sm font-black transition active:scale-95" style="background-color: #8b5cf6; color: #ffffff; box-shadow: 0 18px 30px rgba(139, 92, 246, 0.32);">
        {{ $isReseller ? 'Kelola Reseller' : 'Gabung Membership' }}
    </a>
</section>
@endsection
