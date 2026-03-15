@extends('layouts.user')

@section('title', 'Dashboard Saya')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
    
    /* Hide scrollbar for slider */
    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    
    /* Animasi masuk/keluar untuk Popup Notifikasi */
    .popup-enter { transform: translateY(0); opacity: 1; pointer-events: auto; }
    .popup-leave { transform: translateY(150%); opacity: 0; pointer-events: none; }
</style>

<div class="wiboost-font relative">

    <div class="relative overflow-hidden bg-wiboost-sky rounded-[2.5rem] p-8 md:p-10 mb-8 shadow-xl shadow-[#bde0fe]/50 border-4 border-white flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div class="absolute -right-10 -top-10 text-8xl opacity-40 pointer-events-none">☁️</div>
        <div class="absolute bottom-5 right-1/3 text-4xl opacity-50 pointer-events-none">✨</div>

        <div class="relative z-10">
            <p class="text-[#5a76c8] font-black tracking-wide mb-1 uppercase text-sm">Dashboard Pelanggan</p>
            <h1 class="text-3xl md:text-4xl font-black text-[#2b3a67] mb-2">Halo, {{ Auth::user()->name }}! 👋</h1>
            <p class="text-[#4a5f96] max-w-md font-bold">Siap buat sosmed dan game kamu makin GG hari ini? Yuk, jajan sekarang.</p>
        </div>
        
        <div class="relative z-10 bg-white/80 backdrop-blur-md px-8 py-6 rounded-[2rem] border-2 border-white w-full md:w-auto text-center md:text-right shadow-sm hover:border-[#bde0fe] transition-colors">
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
            <div class="w-14 h-14 bg-[#f0f5ff] rounded-2xl flex items-center justify-center text-[#5a76c8] border-2 border-white shadow-inner shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <div>
                <p class="text-xs font-black text-[#8faaf3] uppercase tracking-wider">Total Pesanan</p>
                <p class="text-2xl font-black text-[#2b3a67]">{{ $totalAllTime ?? 0 }}</p>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-[2rem] border-4 border-white shadow-lg shadow-[#bde0fe]/30 flex items-center gap-5 hover:-translate-y-1 transition">
            <div class="w-14 h-14 bg-[#fff5eb] rounded-2xl flex items-center justify-center text-amber-500 border-2 border-white shadow-inner shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-black text-amber-400 uppercase tracking-wider">Bulan Ini</p>
                <p class="text-2xl font-black text-[#2b3a67]">{{ $totalThisMonth ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-[2rem] border-4 border-white shadow-lg shadow-[#bde0fe]/30 flex items-center gap-5 hover:-translate-y-1 transition">
            <div class="w-14 h-14 bg-[#e6fff7] rounded-2xl flex items-center justify-center text-emerald-500 border-2 border-white shadow-inner shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="truncate">
                <p class="text-xs font-black text-emerald-400 uppercase tracking-wider">Pengeluaran</p>
                <p class="text-xl font-black text-[#2b3a67] truncate">Rp {{ number_format($totalSpent ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="mb-10 relative rounded-[2rem] overflow-hidden shadow-lg shadow-[#bde0fe]/30 border-4 border-white group">
        <div id="promo-slider" class="flex overflow-x-auto snap-x snap-mandatory hide-scroll scroll-smooth">

            @forelse($promos as $promo)
                @php
                    $bgClass = 'from-[#8faaf3] to-[#5a76c8]';
                    $badgeClass = 'bg-[#e0fbfc] text-[#5a76c8]';
                    $textClass = 'text-[#e0fbfc]';

                    if($promo->theme == 'teal') {
                        $bgClass = 'from-[#4bc6b9] to-[#3ba398]';
                        $badgeClass = 'bg-[#e6fff7] text-[#3ba398]';
                        $textClass = 'text-[#e6fff7]';
                    } elseif($promo->theme == 'orange') {
                        $bgClass = 'from-[#fbbf24] to-[#d97706]';
                        $badgeClass = 'bg-[#fff5eb] text-[#d97706]';
                        $textClass = 'text-[#fff5eb]';
                    } elseif($promo->theme == 'rose') {
                        $bgClass = 'from-[#fb7185] to-[#e11d48]';
                        $badgeClass = 'bg-[#ffe5e5] text-[#e11d48]';
                        $textClass = 'text-[#ffe5e5]';
                    }
                @endphp

                <div class="snap-center shrink-0 w-full bg-gradient-to-r {{ $bgClass }} p-8 md:p-10 text-white relative overflow-hidden flex items-center justify-between">
                    <div class="relative z-10">
                        <span class="{{ $badgeClass }} text-xs font-black px-3 py-1 rounded-full mb-3 inline-block shadow-sm uppercase">{{ $promo->badge_text }}</span>
                        <h2 class="text-2xl md:text-3xl font-black mb-2 drop-shadow-sm">{{ $promo->title }}</h2>
                        <p class="font-bold {{ $textClass }} text-sm md:text-base max-w-md">{{ $promo->description }}</p>
                    </div>
                    <div class="text-7xl opacity-50 absolute right-5 transform {{ $loop->iteration % 2 == 0 ? '-rotate-12' : 'rotate-12' }} pointer-events-none">
                        {{ $promo->emoji }}
                    </div>
                </div>
            @empty
                <div class="snap-center shrink-0 w-full bg-gradient-to-r from-[#4bc6b9] to-[#3ba398] p-8 md:p-10 text-white relative overflow-hidden flex items-center justify-between">
                    <div class="relative z-10">
                        <span class="bg-[#e6fff7] text-[#3ba398] text-xs font-black px-3 py-1 rounded-full mb-3 inline-block shadow-sm">PENGUMUMAN</span>
                        <h2 class="text-2xl md:text-3xl font-black mb-2 drop-shadow-sm">Selamat Datang di Wiboost! 🚀</h2>
                        <p class="font-bold text-[#e6fff7] text-sm md:text-base max-w-md">Nikmati layanan top up tercepat dan termurah se-Indonesia hanya di sini.</p>
                    </div>
                    <div class="text-7xl opacity-50 absolute right-5 transform -rotate-12 pointer-events-none">✨</div>
                </div>
            @endforelse
            
        </div>
        
        @if(isset($promos) && $promos->count() > 1)
            <button onclick="slidePromo(-1)" class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/50 hover:bg-white backdrop-blur-md rounded-full flex items-center justify-center text-[#5a76c8] transition shadow-sm opacity-0 group-hover:opacity-100 z-10">❮</button>
            <button onclick="slidePromo(1)" class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/50 hover:bg-white backdrop-blur-md rounded-full flex items-center justify-center text-[#5a76c8] transition shadow-sm opacity-0 group-hover:opacity-100 z-10">❯</button>
        @endif
    </div>

    <div class="flex items-center gap-3 mb-6 pl-2">
        <span class="text-2xl">🎯</span>
        <h3 class="text-2xl font-black text-[#2b3a67]">Pilih Kategori Jajan</h3>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-12">
        @forelse($categories as $category)
        <a href="{{ route('user.order.category', $category->slug) }}" class="group bg-white p-6 rounded-[2rem] border-4 border-white hover:border-[#bde0fe] shadow-lg shadow-[#bde0fe]/20 hover:-translate-y-2 transition-all text-center flex flex-col items-center justify-center">
            
            <div class="w-16 h-16 bg-[#f0f5ff] rounded-2xl mb-4 flex items-center justify-center group-hover:scale-110 group-hover:bg-[#e0fbfc] text-[#5a76c8] transition-all border-2 border-white shadow-inner shrink-0 overflow-hidden">
                @if($category->image)
                    <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                @endif
            </div>
            
            <span class="text-sm font-black text-[#2b3a67]">{{ $category->name }}</span>
            <span class="text-[10px] text-[#8faaf3] font-bold mt-1">{{ $category->products_count }} Layanan</span>
        </a>
        @empty
        <div class="col-span-full text-center py-10">
            <p class="text-[#8faaf3] font-black">Belum ada kategori layanan.</p>
        </div>
        @endforelse
    </div>
</div>

<div id="fomo-toast" class="fixed bottom-6 left-4 md:left-8 z-50 bg-white border-4 border-white shadow-2xl shadow-[#bde0fe]/50 rounded-[2rem] p-4 pr-8 flex items-center gap-4 transition-all duration-700 popup-leave max-w-[320px]">
    <div class="w-12 h-12 bg-[#e6fff7] rounded-[1rem] flex items-center justify-center text-2xl border-2 border-white shadow-inner shrink-0">🛒</div>
    <div>
        <p class="text-[10px] text-[#8faaf3] font-black uppercase tracking-widest" id="fomo-name">Seseorang</p>
        <p class="text-sm text-[#2b3a67] font-black leading-tight mt-0.5">Membeli <span class="text-[#5a76c8]" id="fomo-product">Produk</span></p>
        <p class="text-[10px] text-amber-500 font-bold mt-1" id="fomo-time">Baru saja</p>
    </div>
</div>

<a href="https://wa.me/6285326513324?text=Halo%20Admin%20Wiboost,%20saya%20butuh%20bantuan%20terkait%20pesanan%20saya..." 
   target="_blank" 
   title="Hubungi Admin"
   class="fixed bottom-6 right-4 md:right-8 z-50 bg-[#25D366] text-white w-16 h-16 rounded-full flex items-center justify-center shadow-lg shadow-[#25D366]/40 border-4 border-white hover:scale-110 transition-transform animate-bounce" style="animation-duration: 3s;">
    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 .004 5.385.004 12.033c0 2.128.555 4.195 1.611 6.01L0 24l6.115-1.603c1.748.966 3.735 1.475 5.912 1.475 6.643 0 12.022-5.385 12.022-12.031C24.048 5.385 18.667 0 12.031 0zm3.585 17.26c-.162.457-.93.863-1.32.915-.39.052-1.026.114-3.21-1.127-2.616-1.488-4.286-4.168-4.417-4.34-.131-.173-1.053-1.405-1.053-2.68 0-1.275.666-1.905.901-2.164.235-.259.51-.326.68-.326.17 0 .34 0 .484.008.156.009.366-.059.574.404.215.485.692 1.698.75 1.819.06.12.099.261.02.434-.08.172-.12.28-.24.419-.12.139-.253.298-.36.406-.12.12-.246.252-.11.485.136.234.606 1.009 1.135 1.477.681.603 1.435.838 1.669.957.235.12.373.099.513-.06.14-.158.606-.708.769-.951.163-.243.326-.202.542-.12.215.081 1.365.642 1.601.761.234.12.391.18.448.28.057.1.057.579-.105 1.036z"/></svg>
</a>


<script>
    const slider = document.getElementById('promo-slider');
    
    if(slider && slider.children.length > 1) {
        let autoSlide = setInterval(() => slidePromo(1), 5000);

        function slidePromo(direction) {
            const scrollAmount = slider.clientWidth;
            slider.scrollBy({ left: scrollAmount * direction, behavior: 'smooth' });
            
            if (direction === 1 && slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 10) {
                slider.scrollTo({ left: 0, behavior: 'smooth' });
            }
            
            clearInterval(autoSlide);
            autoSlide = setInterval(() => slidePromo(1), 5000);
        }
    }

    const fakeNames = ["Jokowi", "Prabowo", "Gibran", "Mulyono", "Fuad", "Gatot", "Fufufafa"];
    const fakeProducts = ["86 Diamond MLBB", "1000 Followers IG", "Netflix Premium 1 Bulan", "Spotify 1 Bulan", "PUBG 250 UC"];
    const fomoToast = document.getElementById('fomo-toast');
    const fomoName = document.getElementById('fomo-name');
    const fomoProduct = document.getElementById('fomo-product');

    function showFomo() {
        const randomName = fakeNames[Math.floor(Math.random() * fakeNames.length)];
        const randomProduct = fakeProducts[Math.floor(Math.random() * fakeProducts.length)];
        
        fomoName.innerText = randomName;
        fomoProduct.innerText = randomProduct;

        fomoToast.classList.remove('popup-leave');
        fomoToast.classList.add('popup-enter');

        setTimeout(() => {
            fomoToast.classList.remove('popup-enter');
            fomoToast.classList.add('popup-leave');
        }, 4000);
    }

    setTimeout(() => {
        showFomo();
        setInterval(showFomo, Math.floor(Math.random() * (20000 - 10000 + 1) + 10000));
    }, 3000);
</script>

@endsection