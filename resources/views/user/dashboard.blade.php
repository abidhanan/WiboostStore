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
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0-2.08-.402-2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="truncate">
                <p class="text-xs font-black text-emerald-400 uppercase tracking-wider">Pengeluaran</p>
                <p class="text-xl font-black text-[#2b3a67] truncate">Rp {{ number_format($totalSpent ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="mb-10 relative rounded-[2.5rem] overflow-hidden shadow-lg border-4 border-white group">
        <div id="promo-slider" class="flex overflow-x-auto snap-x snap-mandatory hide-scroll scroll-smooth">

            <div class="snap-center shrink-0 w-full bg-gradient-to-r from-[#8faaf3] to-[#5a76c8] p-8 md:p-12 text-white relative overflow-hidden flex items-center h-[250px] md:h-[300px]">
                <div class="relative z-20">
                    <span class="bg-[#e0fbfc] text-[#5a76c8] text-xs font-black px-3 py-1 rounded-full mb-3 inline-block shadow-sm uppercase tracking-widest">PENGUMUMAN</span>
                    <h2 class="text-3xl md:text-5xl font-black mb-2 drop-shadow-md">Selamat Datang di Wiboost! 🚀</h2>
                    <p class="font-bold text-white/90 text-sm md:text-lg max-w-lg drop-shadow-md">Nikmati layanan top up tercepat dan termurah se-Indonesia hanya di sini.</p>
                </div>
                <div class="text-7xl md:text-8xl opacity-50 absolute right-10 transform -rotate-12 pointer-events-none">✨</div>
            </div>

            @foreach($promos as $promo)
                <div class="snap-center shrink-0 w-full p-8 md:p-12 text-white relative overflow-hidden flex items-center h-[250px] md:h-[300px]
                    @if(!$promo->image)
                        @if($promo->theme == 'teal') bg-gradient-to-r from-[#4bc6b9] to-[#3ba398]
                        @elseif($promo->theme == 'orange') bg-gradient-to-r from-[#fbbf24] to-[#d97706]
                        @elseif($promo->theme == 'rose') bg-gradient-to-r from-[#fb7185] to-[#e11d48]
                        @else bg-gradient-to-r from-[#8faaf3] to-[#5a76c8] @endif
                    @else bg-[#2b3a67] @endif">
                    
                    @if($promo->image)
                        <img src="{{ Storage::url($promo->image) }}" class="absolute inset-0 w-full h-full object-cover z-0 opacity-70">
                        <div class="absolute inset-0 bg-gradient-to-r from-black/80 to-transparent z-10"></div>
                    @endif

                    <div class="relative z-20">
                        <span class="bg-[#e0fbfc] text-[#5a76c8] text-xs font-black px-3 py-1 rounded-full mb-3 inline-block shadow-sm uppercase tracking-widest">{{ $promo->badge_text }}</span>
                        <h2 class="text-3xl md:text-5xl font-black mb-2 drop-shadow-md">{{ $promo->title }}</h2>
                        <p class="font-bold text-white/90 text-sm md:text-lg max-w-lg drop-shadow-md">{{ $promo->description }}</p>
                    </div>
                    
                    @if(!$promo->image)
                        <div class="text-7xl md:text-8xl opacity-50 absolute right-10 transform {{ $loop->iteration % 2 == 0 ? '-rotate-12' : 'rotate-12' }} pointer-events-none">
                            {{ $promo->emoji }}
                        </div>
                    @endif
                </div>
            @endforeach
            
        </div>
        
        @if(count($promos) >= 1)
            <button onclick="slidePromo(-1)" class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/50 hover:bg-white backdrop-blur-md rounded-full flex items-center justify-center text-[#5a76c8] transition shadow-sm opacity-0 group-hover:opacity-100 z-10">❮</button>
            <button onclick="slidePromo(1)" class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/50 hover:bg-white backdrop-blur-md rounded-full flex items-center justify-center text-[#5a76c8] transition shadow-sm opacity-0 group-hover:opacity-100 z-10">❯</button>
        @endif
    </div>

    <div class="flex items-center gap-3 mb-6 pl-2">
        <span class="text-2xl">🎯</span>
        <h3 class="text-2xl font-black text-[#2b3a67]">Pilih Kategori Jajan</h3>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6 mb-12">
        @forelse($categories as $category)
        <a href="{{ route('user.order.category', $category->slug) }}" class="group bg-white p-6 md:p-8 rounded-[2.5rem] border-4 border-white hover:border-[#bde0fe] shadow-lg shadow-[#bde0fe]/20 hover:-translate-y-2 transition-all text-center flex flex-col items-center justify-center">
            
            <div class="text-5xl md:text-6xl mb-4 group-hover:scale-110 group-hover:rotate-6 transition-transform drop-shadow-sm">
                {{ $category->emote ?? '✨' }}
            </div>
            
            <span class="text-base font-black text-[#2b3a67]">{{ $category->name }}</span>
        </a>
        @empty
        <div class="col-span-full text-center py-16 bg-white rounded-[2.5rem] border-4 border-dashed border-[#bde0fe]">
            <p class="text-[#8faaf3] font-black text-xl">Belum ada kategori layanan yang aktif.</p>
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

<div class="fixed bottom-6 right-4 md:right-8 z-50 flex flex-col items-end">
    
    <div id="wa-menu" class="mb-4 w-64 bg-white rounded-[1.5rem] shadow-2xl shadow-[#bde0fe]/50 border-4 border-white overflow-hidden transition-all duration-300 origin-bottom-right scale-0 opacity-0 pointer-events-none flex flex-col">
        <div class="bg-[#25D366] text-white font-black text-[10px] px-4 py-3 tracking-widest uppercase text-center shadow-inner">
            Ada Kendala Apa?
        </div>
        
        <a href="https://wa.me/6281234567890?text=Halo%20Admin%20OTP,%20saya%20butuh%20bantuan..." target="_blank" class="px-5 py-4 font-black text-sm text-[#2b3a67] hover:bg-[#f4f9ff] hover:text-[#25D366] transition-colors border-b-2 border-dashed border-[#f0f5ff] flex items-center gap-3">
            <span class="text-2xl drop-shadow-sm">📩</span> OTP
        </a>
        
        <a href="https://wa.me/6281234567891?text=Halo%20Admin%20Garansi,%20pesanan%20suntik%20sosmed%20saya..." target="_blank" class="px-5 py-4 font-black text-sm text-[#2b3a67] hover:bg-[#f4f9ff] hover:text-[#25D366] transition-colors border-b-2 border-dashed border-[#f0f5ff] flex items-center gap-3">
            <span class="text-2xl drop-shadow-sm">❤️</span> Suntik Sosmed
        </a>
        
        <a href="https://wa.me/6281234567892?text=Halo%20Admin,%20saya%20klaim%20garansi%20aplikasi%20premium..." target="_blank" class="px-5 py-4 font-black text-sm text-[#2b3a67] hover:bg-[#f4f9ff] hover:text-[#25D366] transition-colors border-b-2 border-dashed border-[#f0f5ff] flex items-center gap-3">
            <span class="text-2xl drop-shadow-sm">📺</span> Aplikasi Premium
        </a>
        
        <a href="https://wa.me/6285326513324?text=Halo%20Admin%20CS,%20saya%20butuh%20bantuan%20transaksi..." target="_blank" class="px-5 py-4 font-black text-sm text-[#2b3a67] hover:bg-[#f4f9ff] hover:text-[#25D366] transition-colors flex items-center gap-3">
            <span class="text-2xl drop-shadow-sm">💸</span> Kuota, Top Up, & Deposit
        </a>
    </div>

    <button onclick="toggleWaMenu()" class="bg-[#25D366] text-white w-16 h-16 rounded-full flex items-center justify-center shadow-lg shadow-[#25D366]/40 border-4 border-white hover:scale-110 transition-transform animate-bounce outline-none" style="animation-duration: 3s;">
        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 .004 5.385.004 12.033c0 2.128.555 4.195 1.611 6.01L0 24l6.115-1.603c1.748.966 3.735 1.475 5.912 1.475 6.643 0 12.022-5.385 12.022-12.031C24.048 5.385 18.667 0 12.031 0zm3.585 17.26c-.162.457-.93.863-1.32.915-.39.052-1.026.114-3.21-1.127-2.616-1.488-4.286-4.168-4.417-4.34-.131-.173-1.053-1.405-1.053-2.68 0-1.275.666-1.905.901-2.164.235-.259.51-.326.68-.326.17 0 .34 0 .484.008.156.009.366-.059.574.404.215.485.692 1.698.75 1.819.06.12.099.261.02.434-.08.172-.12.28-.24.419-.12.139-.253.298-.36.406-.12.12-.246.252-.11.485.136.234.606 1.009 1.135 1.477.681.603 1.435.838 1.669.957.235.12.373.099.513-.06.14-.158.606-.708.769-.951.163-.243.326-.202.542-.12.215.081 1.365.642 1.601.761.234.12.391.18.448.28.057.1.057.579-.105 1.036z"/></svg>
    </button>

</div>

<script>
    // --- Slider Promo Logic ---
    const slider = document.getElementById('promo-slider');
    
    if(slider && slider.children.length > 1) {
        let autoSlide = setInterval(() => slidePromo(1), 5000);

        function slidePromo(direction) {
            const scrollAmount = slider.clientWidth;
            slider.scrollBy({ left: scrollAmount * direction, behavior: 'smooth' });
            
            // Infinite scroll logic
            if (direction === 1 && slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 10) {
                slider.scrollTo({ left: 0, behavior: 'smooth' });
            } else if (direction === -1 && slider.scrollLeft <= 10) {
                slider.scrollTo({ left: slider.scrollWidth, behavior: 'smooth' });
            }
            
            clearInterval(autoSlide);
            autoSlide = setInterval(() => slidePromo(1), 5000);
        }
    }

    // --- FOMO Toast Logic ---
    const fakeNames = ["Ganjar", "Gibran", "Prabowo", "Jokowi", "Mulyono"];
    const fakeProducts = ["1000 Followers IG", "86 Diamond MLBB", "Netflix Premium", "Spotify Family", "100 Likes TikTok", "PUBG 250 UC"];
    const fomoToast = document.getElementById('fomo-toast');
    const fomoName = document.getElementById('fomo-name');
    const fomoProduct = document.getElementById('fomo-product');

    function showFomo() {
        fomoName.innerText = fakeNames[Math.floor(Math.random() * fakeNames.length)];
        fomoProduct.innerText = fakeProducts[Math.floor(Math.random() * fakeProducts.length)];
        
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

    // --- WhatsApp Menu Toggle Logic ---
    function toggleWaMenu() {
        const menu = document.getElementById('wa-menu');
        if (menu.classList.contains('scale-0')) {
            // Tampilkan Menu
            menu.classList.remove('scale-0', 'opacity-0', 'pointer-events-none');
            menu.classList.add('scale-100', 'opacity-100', 'pointer-events-auto');
        } else {
            // Sembunyikan Menu
            menu.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto');
            menu.classList.add('scale-0', 'opacity-0', 'pointer-events-none');
        }
    }

    // Menutup menu jika klik di luar area menu
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('wa-menu');
        const waBtnContainer = menu.parentElement;
        
        if (!waBtnContainer.contains(event.target) && !menu.classList.contains('scale-0')) {
            toggleWaMenu();
        }
    });
</script>

@endsection