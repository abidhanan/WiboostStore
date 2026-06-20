<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.site-icons')
    <title>Wiboost Store</title>
    <link rel="preload" as="image" href="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" fetchpriority="high">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.vite-fallback')
    @include('partials.ui-performance-tuning')
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
         body { font-family: 'Nunito', sans-serif; }
         .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
         .bg-wiboost-card { background-color: rgba(255, 255, 255, 0.9); }
         
         .animate-float { animation: float 6s ease-in-out infinite; }
         .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
         @keyframes float {
             0% { transform: translateY(0px); }
             50% { transform: translateY(-15px); }
             100% { transform: translateY(0px); }
         }
         .hide-scroll::-webkit-scrollbar { display: none; }
         .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
         .welcome-section-inner {
             width: 100%;
             margin-left: auto;
             margin-right: auto;
             display: flex;
             flex-direction: column;
             align-items: center;
             text-align: center;
         }
         .welcome-cta-gap {
             margin-bottom: clamp(4.5rem, 9vw, 7.5rem);
         }
         .welcome-hero-title {
             font-size: 2.6rem;
             line-height: 0.98;
             letter-spacing: 0;
             white-space: nowrap;
         }
         .welcome-live-stats {
             width: min(100%, 64rem);
             margin-left: auto;
             margin-right: auto;
         }
         .welcome-stats-grid {
             display: grid;
             grid-template-columns: repeat(2, minmax(0, 1fr));
             gap: 1rem;
             width: 100%;
             align-items: center;
         }
         .welcome-stat-card {
             text-align: center;
             padding: 0.5rem 0.25rem;
         }
         .welcome-stat-users {
             order: 1;
         }
         .welcome-stat-transactions {
             order: 2;
         }
         .welcome-stat-services {
             order: 3;
             grid-column: 1 / -1;
         }
         .order-flow {
             position: relative;
             width: 100%;
         }
         .order-step-card {
             position: relative;
         }
         .order-step-node {
             position: relative;
             z-index: 2;
             display: flex;
             align-items: center;
             justify-content: center;
             width: 3.25rem;
             height: 3.25rem;
             border-radius: 1.2rem;
             border: 4px solid #ffffff;
             background: #5a76c8;
             color: #ffffff;
             font-weight: 900;
             font-size: 1.35rem;
             box-shadow: 0 16px 30px rgba(90, 118, 200, 0.28);
             flex-shrink: 0;
         }
         .order-step-content {
             flex: 1;
             border: 4px solid #ffffff;
             border-radius: 1.6rem;
             background: #f4f9ff;
             padding: 1.1rem 1.2rem;
             box-shadow: 0 18px 40px rgba(189, 224, 254, 0.35);
         }
        .welcome-faq-list {
            width: min(100%, 64rem);
        }
        .welcome-faq-list > details {
            display: block;
            width: 100%;
            min-width: 100%;
            max-width: 100%;
            align-self: stretch;
            box-sizing: border-box;
            text-align: left;
        }
        .welcome-faq-list > details > summary {
            width: 100%;
            box-sizing: border-box;
        }
         .welcome-category-icon {
             width: 4.75rem;
             height: 4.75rem;
             flex: 0 0 4.75rem;
             display: flex;
             align-items: center;
             justify-content: center;
             overflow: hidden;
             border-radius: 1.25rem;
             border: 4px solid #ffffff;
             background: #f4f9ff;
             box-shadow: inset 0 0 0 2px rgba(189, 224, 254, 0.45), 0 8px 18px rgba(189, 224, 254, 0.28);
             padding: 0;
         }
         .welcome-category-title {
             font-size: 0.75rem;
             line-height: 1.15;
         }
         .welcome-category-icon img {
             width: 100%;
             height: 100%;
             object-fit: cover;
             object-position: center;
             border-radius: 1rem;
         }
         .discount-corner-ribbon {
             pointer-events: none;
             position: absolute;
             right: -1px;
             top: -1px;
             z-index: 20;
             width: 7rem;
             height: 7rem;
             overflow: hidden;
             border-top-right-radius: 1.55rem;
         }
         .discount-corner-ribbon__text {
             position: absolute;
             right: -2.55rem;
             top: 1.2rem;
             display: block;
             width: 9.75rem;
             transform: rotate(45deg);
             transform-origin: center;
             background: #5a76c8;
             padding: 0.42rem 0;
             text-align: center;
             font-size: 0.5rem;
             font-weight: 900;
             letter-spacing: 0.14em;
             color: white;
             text-transform: uppercase;
             box-shadow: 0 12px 26px rgba(90, 118, 200, 0.28);
         }

         @media (pointer: coarse) {
             .welcome-category-card {
                 width: calc(50% - 0.5rem) !important;
                 max-width: 16rem;
             }
         }
         @media (min-width: 640px) {
             .welcome-hero-title {
                 font-size: 4.5rem;
             }
             .welcome-category-icon {
                 width: 6rem;
                 height: 6rem;
                 flex-basis: 6rem;
                 margin-bottom: 1.25rem;
             }
             .welcome-category-title {
                 font-size: 1.125rem;
             }
         }
         @media (min-width: 768px) {
             .welcome-hero-title {
                 font-size: 5.8rem;
             }
             .welcome-category-title {
                 font-size: 1rem;
                 line-height: 1.2;
                 white-space: nowrap;
             }
         }
         @media (min-width: 1024px) {
             .welcome-hero-title {
                 font-size: 8rem;
             }
         }
         .welcome-feature-icon-wrap {
             width: 6rem;
             height: 6rem;
             flex: 0 0 6rem;
             display: flex;
             align-items: center;
             justify-content: center;
             overflow: hidden;
             border-radius: 1.25rem;
             border: 4px solid #ffffff;
             background: #ffffff;
             box-shadow: inset 0 0 0 2px rgba(189, 224, 254, 0.45), 0 8px 18px rgba(189, 224, 254, 0.28);
             padding: 0;
         }
         .welcome-feature-icon {
             width: 100%;
             height: 100%;
             object-fit: contain;
             object-position: center;
             border-radius: 0.95rem;
         }
         .welcome-why-heading {
             white-space: nowrap;
          }
         @media (max-width: 767px) {
             .welcome-why-heading {
                 font-size: clamp(1.25rem, 6.4vw, 1.7rem) !important;
                 line-height: 1.12;
             }
             .welcome-live-stats {
                 width: min(100%, 24rem);
             }
             .welcome-stats-grid {
                 background: transparent;
                 border: 0;
                 box-shadow: none;
                 padding: 0;
                 row-gap: 1.2rem;
             }
             .welcome-stat-card {
                  min-height: 6.25rem;
                  display: flex;
                  flex-direction: column;
                  justify-content: center;
                  background: rgba(255, 255, 255, 0.9);
                  border: 4px solid #ffffff;
                  border-radius: 1.35rem;
                  box-shadow: 0 16px 34px rgba(189, 224, 254, 0.32);
                  padding: 1rem 0.75rem;
             }
             .order-flow {
                 max-width: 34rem;
                 margin-inline: auto;
             }
             .order-flow::before {
                 content: "";
                 position: absolute;
                 left: 1.55rem;
                 top: 1.6rem;
                 bottom: 1.6rem;
                 width: 4px;
                 border-radius: 999px;
                 background: linear-gradient(180deg, #5a76c8, #4bc6b9);
                 opacity: 0.28;
             }
             .order-step-card {
                 display: flex;
                 gap: 1rem;
                  align-items: center;
                 text-align: left;
             }
             .order-step-card + .order-step-card {
                 margin-top: 1rem;
             }
             .order-step-content h3,
             .order-step-content p {
                 text-align: left;
             }
         }

         @media (min-width: 768px) {
             .welcome-stats-grid {
                 grid-template-columns: repeat(3, minmax(0, 1fr));
                 background: rgba(255, 255, 255, 0.9);
                 backdrop-filter: blur(12px);
                 border: 4px solid #ffffff;
                 border-radius: 2rem;
                 box-shadow: 0 22px 45px rgba(189, 224, 254, 0.4);
                 padding: 2rem;
             }
             .welcome-stat-services {
                 order: initial;
                 grid-column: auto;
             }
             .welcome-stat-users,
             .welcome-stat-transactions {
                 order: initial;
             }
             .order-flow-grid {
                 display: grid;
                 grid-template-columns: repeat(4, minmax(0, 1fr));
                 gap: 1.25rem;
             }
             .order-step-card {
                 display: flex;
                 min-height: 18rem;
                 flex-direction: column;
                 align-items: center;
                 justify-content: flex-start;
                 text-align: center;
                 border: 4px solid #ffffff;
                 border-radius: 2rem;
                 background: linear-gradient(180deg, #ffffff 0%, #f4f9ff 100%);
                 padding: 1.5rem 1.1rem;
                 box-shadow: 0 22px 45px rgba(189, 224, 254, 0.35);
                 transition: transform 0.25s ease, box-shadow 0.25s ease;
             }
             .order-step-card:hover {
                 transform: translateY(-6px);
                 box-shadow: 0 28px 55px rgba(90, 118, 200, 0.18);
             }
             .order-step-node {
                 width: 5rem;
                 height: 5rem;
                 border-radius: 1.7rem;
                 font-size: 2rem;
                 margin-bottom: 1.2rem;
             }
             .order-step-content {
                 border: 0;
                 background: transparent;
                 padding: 0;
                 box-shadow: none;
             }
             .order-step-content p {
                 padding-inline: 0;
             }
         }
         
         /* Animasi mulus untuk FAQ accordion */
         details > summary { list-style: none; }
         details > summary::-webkit-details-marker { display: none; }
         
         /* Panah FAQ berputar menunjuk ke atas saat elemen terbuka */
         details[open] summary .faq-icon {
             transform: rotate(180deg);
         }
         .faq-icon {
             transition: transform 0.3s ease-in-out;
         }
         @media (min-width: 1024px) {
             nav > div,
             .welcome-section-inner,
             footer > div {
                 max-width: min(1600px, calc(100vw - 8rem)) !important;
             }

             .welcome-section-inner {
                 padding-left: 2rem !important;
                 padding-right: 2rem !important;
             }
         }
    </style>
    @include('partials.ios-layout-fixes')
</head>
@php
    $categoryDiscountLabel = \App\Models\Product::currentDiscountLabel();
@endphp
<body class="wiboost-compact-ui wiboost-welcome-page bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white flex flex-col min-h-screen">
    <nav class="fixed w-full z-50 top-4 px-4 sm:px-6 lg:px-8 pointer-events-none">
        <div class="max-w-7xl mx-auto pointer-events-auto">
            <div class="flex items-center justify-between rounded-[2rem] border-4 border-white bg-white/90 px-4 py-3 shadow-xl shadow-[#bde0fe]/50 backdrop-blur-md sm:px-6">
                <a href="{{ Auth::check() ? (Auth::user()->isAdmin() ? route('admin.dashboard') : route('user.dashboard')) : route('home') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" onerror="this.onerror=null;this.src='{{ asset('images/wiboost-logo.png') }}?v=20260426-full';" alt="Logo Wiboost Store" width="192" height="192" fetchpriority="high" loading="eager" decoding="async" class="h-12 w-12 shrink-0 rounded-full object-contain drop-shadow-sm transition-transform group-hover:scale-105 sm:h-14 sm:w-14">
                    <p class="text-lg font-black leading-none text-[#2b3a67] drop-shadow-sm sm:text-2xl">Wiboost <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#5a76c8] to-[#9a8ce5]">Store</span></p>
                </a>
                <div class="flex items-center gap-3">
                    @auth
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="text-sm font-extrabold text-white bg-[#5a76c8] hover:bg-[#4760a9] px-6 py-2.5 rounded-full transition-transform active:scale-95 shadow-md">Panel Admin</a>
                        @else
                            <a href="{{ route('user.dashboard') }}" class="text-sm font-extrabold text-white bg-[#5a76c8] hover:bg-[#4760a9] px-6 py-2.5 rounded-full transition-transform active:scale-95 shadow-md">Dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('register') }}" class="hidden rounded-full border-2 border-white bg-[#f0f5ff] px-6 py-2.5 text-sm font-extrabold text-[#5a76c8] shadow-md shadow-[#bde0fe]/30 transition-transform hover:-translate-y-0.5 hover:bg-white active:scale-95 sm:block">Daftar</a>
                        <a href="{{ route('login') }}" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white text-sm font-extrabold px-7 py-2.5 rounded-full transition-transform active:scale-95 shadow-lg shadow-[#8faaf3]/40 border-2 border-white">Masuk</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- SECTION HERO & DATA REAL-TIME -->
    <main class="relative flex items-center justify-center pt-40 pb-24 lg:pt-56 lg:pb-32 bg-wiboost-sky overflow-hidden border-b-[6px] border-[#f0f5ff]">
        <div class="welcome-section-inner max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="inline-block bg-white/80 backdrop-blur-sm text-[#5a76c8] font-black text-sm px-5 py-2 rounded-full mb-6 border-2 border-white shadow-sm">
                Pusat Kebutuhan Digitalmu!
            </div>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-6">
                <h1 class="welcome-hero-title font-black text-[#2b3a67] drop-shadow-sm">
                    Wiboost <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#5a76c8] to-[#9a8ce5]">Store</span>
                </h1>
            </div>

            <p class="mt-2 text-sm md:text-base text-[#4a5f96] max-w-5xl mx-auto font-bold leading-normal">
                Wiboost Store adalah platform layanan digital terintegrasi yang menyediakan katalog lengkap mencakup layanan Pemasaran Media Sosial (SMM) guna menunjang pertumbuhan metrik akun sosial media, top up game online secara instan, pembelian paket data operator, hingga penyediaan akses langganan aplikasi premium dengan harga kompetitif, di mana seluruh transaksi diproses secara otomatis dan didukung oleh pembayaran QRIS yang support semua metode pembayaran secara praktis dan aman.
            </p>
            
            <div class="welcome-cta-gap mt-12 flex flex-col sm:flex-row justify-center gap-4">
                <a href="#kategori" onclick="event.preventDefault(); document.getElementById('kategori').scrollIntoView({behavior: 'smooth', block: 'center'});" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white font-extrabold text-lg px-10 py-4 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white flex items-center justify-center gap-3">
                    Beli Sekarang
                </a>
            </div>

            <div class="welcome-live-stats">
                <div class="welcome-stats-grid">
                    <div class="welcome-stat-card welcome-stat-users">
                        <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Total Pengguna</p>
                        <p class="mt-1 text-4xl font-black text-[#5a76c8]">{{ number_format($totalUsers, 0, ',', '.') }}</p>
                    </div>
                    <div class="welcome-stat-card welcome-stat-services">
                        <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Total Layanan</p>
                        <p class="mt-1 text-4xl font-black text-[#9a8ce5]">{{ number_format($activeProducts, 0, ',', '.') }}</p>
                    </div>
                    <div class="welcome-stat-card welcome-stat-transactions">
                        <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Total Transaksi</p>
                        <p class="mt-1 text-4xl font-black text-[#4bc6b9]">{{ number_format($totalTransactions, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- SECTION KATEGORI (PUTIH) -->
    <section id="kategori" class="py-24 lg:py-32 bg-white relative flex-1 z-10 border-b-[6px] border-[#f0f5ff]">
        <div class="welcome-section-inner max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-6 text-xs border-2 border-white">Katalog Utama</div>
                <h2 class="text-3xl md:text-5xl font-black text-[#2b3a67] tracking-tight">Pilih Kategori</h2>
            </div>

            <div class="welcome-category-grid mx-auto flex max-w-5xl flex-wrap justify-center gap-4 md:gap-5">
                @forelse($categories as $cat)
                    @if(is_null($cat->parent_id))
                        <a href="{{ route('guest.order.category', $cat->slug) }}" class="welcome-category-card group relative flex aspect-square w-[calc(50%-0.5rem)] flex-col items-center justify-center overflow-hidden bg-[#f4f9ff] rounded-[1.5rem] p-2 sm:p-6 md:p-8 border-4 border-white shadow-lg shadow-[#bde0fe]/20 hover:-translate-y-2 hover:border-[#bde0fe] transition-all duration-300 text-center sm:w-56 md:w-60 lg:w-56">
                            @if($categoryDiscountLabel)
                                <span class="discount-corner-ribbon">
                                    <span class="discount-corner-ribbon__text">
                                        {{ $categoryDiscountLabel }}
                                    </span>
                                </span>
                            @endif
                            <div class="welcome-category-icon mb-3 text-[#5a76c8] transition-transform duration-300 group-hover:scale-110">
                                @if($cat->image)
                                    <img src="{{ Storage::url($cat->image) }}" alt="{{ $cat->name }}" class="drop-shadow-sm">
                                @else
                                @if($cat->emote)
                                    <span class="text-3xl drop-shadow-sm">{{ $cat->emote }}</span>
                                @else
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                @endif
                                @endif
                            </div>
                            <h3 class="welcome-category-title max-w-full break-words px-1 font-black text-[#2b3a67]">{{ $cat->name }}</h3>
                        </a>
                    @endif
                @empty
                    <div class="w-full max-w-md text-center py-10 bg-[#f4f9ff] rounded-[2rem] border-4 border-dashed border-[#bde0fe]">
                        <p class="text-[#5a76c8] font-black">Layanan sedang dipersiapkan...</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- SECTION KENAPA HARUS WIBOOST STORE (BIRU) -->
    <section id="fitur" class="py-24 lg:py-32 bg-[#f4f9ff] border-b-[6px] border-white relative z-10">
        <div class="welcome-section-inner max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-block px-4 py-1 bg-white text-[#4bc6b9] font-black rounded-full mb-6 text-xs shadow-sm">Keunggulan</div>
                <h2 class="welcome-why-heading text-3xl md:text-5xl font-black text-[#2b3a67] tracking-tight">Kenapa Harus Wiboost Store?</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <div class="bg-white rounded-[2rem] p-8 border-4 border-white shadow-xl shadow-[#bde0fe]/20 hover:-translate-y-2 transition-transform duration-300 text-center">
                    <div class="welcome-feature-icon-wrap mb-5 mx-auto">
                        <img src="{{ asset('images/feature-lightning.jpeg') }}?v=20260513" alt="Proses otomatis" class="welcome-feature-icon">
                    </div>
                    <h3 class="font-black text-[#2b3a67] text-lg mb-3">Proses Otomatis</h3>
                    <p class="text-sm text-[#4a5f96] font-bold leading-relaxed">Sistem kami berjalan 24/7. Transaksi diproses seketika tanpa perlu konfirmasi manual dari admin.</p>
                </div>
                <div class="bg-white rounded-[2rem] p-8 border-4 border-white shadow-xl shadow-[#bde0fe]/20 hover:-translate-y-2 transition-transform duration-300 text-center">
                    <div class="welcome-feature-icon-wrap mb-5 mx-auto">
                        <img src="{{ asset('images/feature-shield.jpeg') }}?v=20260513" alt="Aman dan terpercaya" class="welcome-feature-icon">
                    </div>
                    <h3 class="font-black text-[#2b3a67] text-lg mb-3">Aman & Terpercaya</h3>
                    <p class="text-sm text-[#4a5f96] font-bold leading-relaxed">Pembayaran menggunakan QRIS resmi yang aman, serta privasi akun media sosial Anda terjamin 100%.</p>
                </div>
                <div class="bg-white rounded-[2rem] p-8 border-4 border-white shadow-xl shadow-[#bde0fe]/20 hover:-translate-y-2 transition-transform duration-300 text-center">
                    <div class="welcome-feature-icon-wrap mb-5 mx-auto">
                        <img src="{{ asset('images/feature-boxes.jpeg') }}?v=20260513" alt="Layanan terlengkap" class="welcome-feature-icon">
                    </div>
                    <h3 class="font-black text-[#2b3a67] text-lg mb-3">Layanan Terlengkap</h3>
                    <p class="text-sm text-[#4a5f96] font-bold leading-relaxed">Mulai dari Top Up Game, SMM Panel, hingga Aplikasi Premium tersedia lengkap dalam satu platform.</p>
                </div>
                <div class="bg-white rounded-[2rem] p-8 border-4 border-white shadow-xl shadow-[#bde0fe]/20 hover:-translate-y-2 transition-transform duration-300 text-center">
                    <div class="welcome-feature-icon-wrap mb-5 mx-auto">
                        <img src="{{ asset('images/feature-wallet.jpeg') }}?v=20260513" alt="Harga kompetitif" class="welcome-feature-icon">
                    </div>
                    <h3 class="font-black text-[#2b3a67] text-lg mb-3">Harga Kompetitif</h3>
                    <p class="text-sm text-[#4a5f96] font-bold leading-relaxed">Dapatkan layanan berkualitas dengan harga termurah. Sangat cocok untuk digunakan sendiri atau dijual kembali.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION ALUR PEMESANAN (PUTIH) -->
    <section id="alur-pemesanan" class="py-24 lg:py-32 bg-white relative z-10 border-b-[6px] border-[#f0f5ff]">
        <div class="welcome-section-inner max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-6 text-xs border-2 border-white">Cara Kerja</div>
                <h2 class="text-3xl md:text-5xl font-black text-[#2b3a67] tracking-tight">Alur Pemesanan</h2>
            </div>

            <div class="order-flow max-w-6xl mx-auto">
                <div class="order-flow-grid relative z-10">
                    <div class="order-step-card group">
                        <div class="order-step-node">1</div>
                        <div class="order-step-content">
                            <h3 class="font-black text-[#2b3a67] text-xl mb-2">Pilih Layanan</h3>
                            <p class="text-sm text-[#4a5f96] font-bold">Telusuri katalog dan pilih layanan digital yang Anda butuhkan.</p>
                        </div>
                    </div>
                    <div class="order-step-card group">
                        <div class="order-step-node">2</div>
                        <div class="order-step-content">
                            <h3 class="font-black text-[#2b3a67] text-xl mb-2">Isi Data</h3>
                            <p class="text-sm text-[#4a5f96] font-bold">Masukkan data yang diminta seperti User ID game atau Link Sosial Media.</p>
                        </div>
                    </div>
                    <div class="order-step-card group">
                        <div class="order-step-node">3</div>
                        <div class="order-step-content">
                            <h3 class="font-black text-[#2b3a67] text-xl mb-2">Pembayaran</h3>
                            <p class="text-sm text-[#4a5f96] font-bold">Lakukan pembayaran dengan scan QRIS atau metode lainnya yang tersedia.</p>
                        </div>
                    </div>
                    <div class="order-step-card group">
                        <div class="order-step-node">4</div>
                        <div class="order-step-content">
                            <h3 class="font-black text-[#2b3a67] text-xl mb-2">Selesai!</h3>
                            <p class="text-sm text-[#4a5f96] font-bold">Pesanan Anda akan langsung diproses secara instan oleh sistem kami.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION PERTANYAAN UMUM (FAQ) (BIRU) -->
    <section id="faq" class="py-24 lg:py-32 bg-[#f4f9ff] border-b-[6px] border-white relative z-10">
        <div class="welcome-section-inner max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-block px-4 py-1 bg-white text-[#4bc6b9] font-black rounded-full mb-6 text-xs shadow-sm">FAQ</div>
                <h2 class="text-3xl md:text-5xl font-black text-[#2b3a67] tracking-tight">Pertanyaan Umum</h2>
            </div>

            <div class="welcome-faq-list flex flex-col gap-4">
                <details class="w-full bg-white rounded-[1.5rem] border-4 border-white shadow-lg shadow-[#bde0fe]/20 overflow-hidden cursor-pointer">
                    <summary class="flex justify-between items-center p-6 font-black text-[#2b3a67] text-base md:text-lg focus:outline-none text-left">
                        <span>Berapa lama waktu yang dibutuhkan untuk proses pesanan?</span>
                        <span class="text-[#5a76c8]">
                            <svg class="faq-icon w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-sm text-[#4a5f96] font-bold leading-relaxed border-t-2 border-[#f0f5ff] pt-4 mt-1 bg-white text-left">
                        Sebagian besar layanan kami diproses secara otomatis dan instan dalam hitungan detik setelah pembayaran berhasil dikonfirmasi. Untuk layanan tertentu (seperti SMM dengan target tinggi), proses mungkin membutuhkan waktu yang sedikit lebih lama sesuai deskripsi produk.
                    </div>
                </details>

                <details class="w-full bg-white rounded-[1.5rem] border-4 border-white shadow-lg shadow-[#bde0fe]/20 overflow-hidden cursor-pointer">
                    <summary class="flex justify-between items-center p-6 font-black text-[#2b3a67] text-base md:text-lg focus:outline-none text-left">
                        <span>Apakah butuh password akun sosmed saya?</span>
                        <span class="text-[#5a76c8]">
                            <svg class="faq-icon w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-sm text-[#4a5f96] font-bold leading-relaxed border-t-2 border-[#f0f5ff] pt-4 mt-1 bg-white text-left">
                        Tidak! Layanan SMM (Social Media Marketing) kami 100% aman karena hanya membutuhkan <strong>Username</strong> atau <strong>Link/Tautan</strong> postingan Anda. Kami tidak akan pernah meminta password akun Anda.
                    </div>
                </details>

                <details class="w-full bg-white rounded-[1.5rem] border-4 border-white shadow-lg shadow-[#bde0fe]/20 overflow-hidden cursor-pointer">
                    <summary class="flex justify-between items-center p-6 font-black text-[#2b3a67] text-base md:text-lg focus:outline-none text-left">
                        <span>Metode pembayaran apa saja yang tersedia?</span>
                        <span class="text-[#5a76c8]">
                            <svg class="faq-icon w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-sm text-[#4a5f96] font-bold leading-relaxed border-t-2 border-[#f0f5ff] pt-4 mt-1 bg-white text-left">
                        Kami mendukung pembayaran via <strong>QRIS</strong> yang dapat di-scan menggunakan seluruh aplikasi E-Wallet (Dana, Ovo, Gopay, LinkAja, ShopeePay) maupun Mobile Banking (BCA, Mandiri, BRI, BNI, dll). Pembayaran diproses otomatis tanpa perlu mengirim bukti transfer.
                    </div>
                </details>

                <details class="w-full bg-white rounded-[1.5rem] border-4 border-white shadow-lg shadow-[#bde0fe]/20 overflow-hidden cursor-pointer">
                    <summary class="flex justify-between items-center p-6 font-black text-[#2b3a67] text-base md:text-lg focus:outline-none text-left">
                        <span>Bagaimana jika pesanan saya gagal?</span>
                        <span class="text-[#5a76c8]">
                            <svg class="faq-icon w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-sm text-[#4a5f96] font-bold leading-relaxed border-t-2 border-[#f0f5ff] pt-4 mt-1 bg-white text-left">
                        Jika terjadi gangguan teknis dari pusat yang menyebabkan pesanan Anda berstatus gagal (Error/Canceled), sistem kami akan secara otomatis mengembalikan dana Anda dalam bentuk saldo akun yang bisa Anda gunakan kembali untuk transaksi lainnya.
                    </div>
                </details>
            </div>
        </div>
    </section>
    
    <footer class="bg-white pt-12 pb-8 relative overflow-hidden mt-auto">
        <div class="max-w-6xl mx-auto px-6 sm:px-8 relative z-10">
            <div class="flex flex-col items-center gap-6 text-center md:flex-row md:justify-between md:gap-8 md:text-left mb-10">
                
                <div class="min-w-0 text-center md:text-left">
                    <div class="flex items-center justify-center gap-2 mb-3 md:justify-start">
                        <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" onerror="this.onerror=null;this.src='{{ asset('images/wiboost-logo.png') }}?v=20260426-full';" alt="Logo Wiboost Store" width="192" height="192" loading="lazy" decoding="async" class="h-10 w-10 shrink-0 object-contain drop-shadow-sm md:h-12 md:w-12">
                        <span class="whitespace-nowrap font-black text-lg text-[#2b3a67] sm:text-xl md:text-2xl">Wiboost <span class="text-[#5a76c8]">Store</span></span>
                    </div>
                    <p class="mx-auto max-w-xs text-[10px] font-bold text-[#8faaf3] sm:text-xs md:mx-0 md:min-w-max md:max-w-none md:whitespace-nowrap md:text-sm">Pusat Layanan dan Produk Digital dalam Satu Platform.</p>
                </div>

                <div class="flex shrink-0 flex-col items-center gap-3 md:items-end">
                    <p class="mb-1 text-xs font-black text-[#2b3a67] md:text-sm">Ikuti Kami</p>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <a href="https://instagram.com/ahawi_channel" target="_blank" class="w-10 h-10 sm:w-12 sm:h-12 bg-[#f4f9ff] text-[#e1306c] rounded-2xl flex items-center justify-center border-2 border-white shadow-sm hover:bg-[#5a76c8] hover:text-white transition-all md:h-14 md:w-14">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        
                        <a href="https://tiktok.com/@ahawi_channel" target="_blank" class="w-10 h-10 sm:w-12 sm:h-12 bg-[#f4f9ff] text-[#2b3a67] rounded-2xl flex items-center justify-center border-2 border-white shadow-sm hover:bg-[#5a76c8] hover:text-white transition-all md:h-14 md:w-14">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/></svg>
                        </a>
                        <a href="https://youtube.com/@ahawi_channel" target="_blank" class="w-10 h-10 sm:w-12 sm:h-12 bg-[#f4f9ff] text-[#FF0000] rounded-2xl flex items-center justify-center border-2 border-white shadow-sm hover:bg-[#5a76c8] hover:text-white transition-all md:h-14 md:w-14">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t-2 border-[#f0f5ff] pt-8 text-center">
                <div class="mb-5 flex w-full flex-nowrap items-center justify-between gap-0.5 px-0 sm:justify-center sm:gap-2 sm:px-2">
                    <a href="{{ route('legal.show', 'terms') }}" class="flex min-w-0 flex-1 items-center justify-center rounded-full bg-[#f4f9ff] px-0.5 py-1 text-center text-[6px] font-black text-[#5a76c8] transition hover:bg-[#e0fbfc] min-[380px]:text-[7px] sm:flex-none sm:px-3 sm:py-1.5 sm:text-[10px] md:px-3 md:text-xs whitespace-nowrap">Syarat & Ketentuan</a>
                    <a href="{{ route('legal.show', 'privacy-policy') }}" class="flex min-w-0 flex-1 items-center justify-center rounded-full bg-[#f4f9ff] px-0.5 py-1 text-center text-[6px] font-black text-[#5a76c8] transition hover:bg-[#e0fbfc] min-[380px]:text-[7px] sm:flex-none sm:px-3 sm:py-1.5 sm:text-[10px] md:px-3 md:text-xs whitespace-nowrap">Privasi</a>
                    <a href="{{ route('legal.show', 'refund-policy') }}" class="flex min-w-0 flex-1 items-center justify-center rounded-full bg-[#f4f9ff] px-0.5 py-1 text-center text-[6px] font-black text-[#5a76c8] transition hover:bg-[#e0fbfc] min-[380px]:text-[7px] sm:flex-none sm:px-3 sm:py-1.5 sm:text-[10px] md:px-3 md:text-xs whitespace-nowrap">Refund</a>
                    <a href="{{ route('legal.show', 'contact') }}" class="flex min-w-0 flex-1 items-center justify-center rounded-full bg-[#f4f9ff] px-0.5 py-1 text-center text-[6px] font-black text-[#5a76c8] transition hover:bg-[#e0fbfc] min-[380px]:text-[7px] sm:flex-none sm:px-3 sm:py-1.5 sm:text-[10px] md:px-3 md:text-xs whitespace-nowrap">Kontak Admin</a>
                </div>
                <p class="text-[10px] font-black text-[#a3bbfb] md:text-sm lg:text-base">&copy; {{ date('Y') }} Wiboost Store - Dibuat oleh <a href="https://www.abidhanan.my.id/" target="_blank" rel="noopener noreferrer" style="color: #5a76c8;" onmouseover="this.style.color='#4bc6b9'" onmouseout="this.style.color='#5a76c8'" class="transition">Abid Hanan Wicaksono</a>.</p>
            </div>
        </div>
    </footer>

</body>
</html>
