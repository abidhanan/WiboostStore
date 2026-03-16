<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wiboost Store - Top Up & Kebutuhan Digital Termurah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

        .popup-enter { transform: translateY(0); opacity: 1; pointer-events: auto; }
        .popup-leave { transform: translateY(150%); opacity: 0; pointer-events: none; }
    </style>
</head>
<body class="bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white flex flex-col min-h-screen">

    <nav class="fixed w-full z-50 top-4 px-4 sm:px-6 lg:px-8 pointer-events-none">
        <div class="max-w-7xl mx-auto pointer-events-auto">
            <div class="bg-white/90 backdrop-blur-md border-4 border-white shadow-xl shadow-[#bde0fe]/50 rounded-[2rem] flex justify-between items-center h-20 px-6 sm:px-8">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-[#8faaf3] to-[#7189d8] rounded-2xl flex items-center justify-center text-white font-black text-2xl shadow-inner border-2 border-white">W</div>
                    <span class="font-extrabold text-2xl tracking-tight text-[#5a76c8]">Wiboost<span class="text-[#8faaf3]">Store</span></span>
                </div>
                <div class="flex items-center gap-3">
                    @auth
                        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
                            <a href="{{ route('admin.dashboard') }}" class="text-sm font-extrabold text-white bg-[#5a76c8] hover:bg-[#4760a9] px-6 py-2.5 rounded-full transition-transform active:scale-95 shadow-md">Panel Admin</a>
                        @else
                            <a href="{{ route('user.dashboard') }}" class="text-sm font-extrabold text-white bg-[#5a76c8] hover:bg-[#4760a9] px-6 py-2.5 rounded-full transition-transform active:scale-95 shadow-md">Dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-extrabold text-[#5a76c8] hover:bg-[#f0f5ff] px-5 py-2.5 rounded-full transition hidden sm:block">Masuk</a>
                        <a href="{{ route('register') }}" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white text-sm font-extrabold px-7 py-2.5 rounded-full transition-transform active:scale-95 shadow-lg shadow-[#8faaf3]/40 border-2 border-white">Daftar</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="relative pt-40 pb-32 lg:pt-56 lg:pb-48 bg-wiboost-sky overflow-hidden">
        <div class="absolute top-32 left-10 text-4xl animate-float opacity-70">☁️</div>
        <div class="absolute top-20 right-20 text-4xl animate-float-delayed opacity-70">✨</div>
        <div class="absolute bottom-40 left-1/4 text-3xl animate-float-delayed opacity-60">⭐</div>
        <div class="absolute bottom-20 right-1/4 text-5xl animate-float opacity-80">☁️</div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <div class="inline-block bg-white/80 backdrop-blur-sm text-[#5a76c8] font-black text-sm px-5 py-2 rounded-full mb-6 border-2 border-white shadow-sm">
                🎉 Platform Top Up & Digital Favoritmu
            </div>
            <h1 class="text-5xl md:text-7xl font-black tracking-tight mb-6 text-[#2b3a67] drop-shadow-sm">
                Semua Kebutuhan Digital<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#5a76c8] to-[#9a8ce5]">Dalam Satu Klik.</span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-[#4a5f96] max-w-2xl mx-auto font-bold leading-relaxed">
                Proses otomatis 24/7, super cepat, dan dijamin bergaransi!
            </p>
            
            <div class="mt-12 flex flex-col sm:flex-row justify-center gap-4">
                <a href="#kategori" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white font-extrabold text-lg px-10 py-4 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white flex items-center justify-center gap-3">
                    Mulai Belanja 🚀
                </a>
            </div>
        </div>

        <div class="absolute inset-x-0 bottom-0">
            <svg viewBox="0 0 1440 120" class="w-full h-auto text-[#f4f9ff] fill-current" preserveAspectRatio="none">
                <path d="M0,64L48,74.7C96,85,192,107,288,101.3C384,96,480,64,576,64C672,64,768,96,864,106.7C960,117,1056,107,1152,85.3C1248,64,1344,32,1392,16L1440,0L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
            </svg>
        </div>
    </main>

    <div class="bg-[#f4f9ff] pb-10 relative z-20 -mt-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2rem] shadow-xl shadow-[#bde0fe]/40 border-4 border-white p-6 md:p-8 flex flex-col md:flex-row justify-around items-center gap-8 animate-float">
                <div class="text-center">
                    <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Pengguna Aktif</p>
                    <p class="mt-1 text-4xl font-black text-[#5a76c8]">{{ number_format($totalUsers, 0, ',', '.') }}<span class="text-2xl text-[#a3bbfb]">+</span></p>
                </div>
                <div class="hidden md:block w-1 h-12 bg-[#f0f5ff] rounded-full"></div>
                <div class="text-center">
                    <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Layanan</p>
                    <p class="mt-1 text-4xl font-black text-[#9a8ce5]">{{ number_format($activeProducts, 0, ',', '.') }}</p>
                </div>
                <div class="hidden md:block w-1 h-12 bg-[#f0f5ff] rounded-full"></div>
                <div class="text-center">
                    <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Transaksi</p>
                    <p class="mt-1 text-4xl font-black text-[#4bc6b9]">{{ number_format($totalTransactions, 0, ',', '.') }}<span class="text-2xl text-[#8ce0d7]">+</span></p>
                </div>
            </div>
        </div>
    </div>

    <section id="kategori" class="py-16 bg-[#f4f9ff] relative flex-1 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-xs border-2 border-white">Katalog Utama</div>
                <h2 class="text-3xl font-black text-[#2b3a67] tracking-tight">Pilih Kategori Jajan</h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 md:gap-5">
                @forelse($categories as $cat)
                    @if(is_null($cat->parent_id))
                        <a href="{{ route('login') }}" class="group block bg-white rounded-[1.5rem] p-4 border-4 border-white shadow-lg shadow-[#bde0fe]/30 hover:-translate-y-2 hover:border-[#bde0fe] transition-all duration-300 text-center relative flex flex-col items-center">
                            <div class="w-16 h-16 bg-[#f4f9ff] rounded-2xl border-2 border-white shadow-inner flex items-center justify-center text-[#5a76c8] mb-3 group-hover:scale-110 transition-transform duration-300">
                                @if($cat->emote)
                                    <span class="text-4xl drop-shadow-sm">{{ $cat->emote }}</span>
                                @else
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                @endif
                            </div>
                            <h3 class="font-black text-[#2b3a67] text-sm md:text-base leading-tight">{{ $cat->name }}</h3>
                        </a>
                    @endif
                @empty
                    <div class="col-span-full text-center py-10 bg-white rounded-[2rem] border-4 border-dashed border-[#bde0fe]">
                        <p class="text-[#5a76c8] font-black">Layanan sedang dipersiapkan...</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <div id="fomo-toast" class="fixed bottom-6 left-4 md:left-8 z-50 bg-white border-4 border-white shadow-2xl shadow-[#bde0fe]/50 rounded-[2rem] p-4 pr-8 flex items-center gap-4 transition-all duration-700 popup-leave max-w-[300px]">
        <div class="w-10 h-10 bg-[#e6fff7] rounded-xl flex items-center justify-center text-xl border-2 border-white shadow-inner shrink-0">🛒</div>
        <div>
            <p class="text-[9px] text-[#8faaf3] font-black uppercase tracking-widest" id="fomo-name">Seseorang</p>
            <p class="text-xs text-[#2b3a67] font-black leading-tight mt-0.5">Membeli <span class="text-[#5a76c8]" id="fomo-product">Produk</span></p>
        </div>
    </div>

    <footer class="bg-white pt-12 pb-10 border-t-[6px] border-[#f0f5ff] relative overflow-hidden">
        <div class="absolute -top-10 -right-10 text-8xl opacity-10">☁️</div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                
                <div class="text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start gap-2 mb-4">
                        <div class="w-10 h-10 bg-[#5a76c8] rounded-xl flex items-center justify-center text-white font-black text-xl shadow-inner border-2 border-[#bde0fe]">W</div>
                        <span class="font-extrabold text-2xl tracking-tight text-[#2b3a67]">Wiboost<span class="text-[#8faaf3]">Store</span></span>
                    </div>
                    <p class="text-[#8faaf3] font-bold text-sm">&copy; {{ date('Y') }} Wiboost Store. Hak Cipta Dilindungi.</p>
                </div>

                <div class="flex items-center gap-4">
                    <p class="hidden lg:block text-xs font-black text-[#8faaf3] uppercase tracking-widest mr-2">Ikuti Kami:</p>
                    
                    <a href="https://instagram.com/ahawi_channel" target="_blank" class="w-12 h-12 bg-[#f0f5ff] rounded-2xl flex items-center justify-center text-[#5a76c8] hover:bg-[#e0fbfc] hover:text-[#4bc6b9] transition-all hover:-translate-y-1 shadow-sm border-2 border-white group" title="Instagram Ahawi Channel">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>

                    <a href="https://tiktok.com/@ahawi_channel" target="_blank" class="w-12 h-12 bg-[#f0f5ff] rounded-2xl flex items-center justify-center text-[#5a76c8] hover:bg-[#e0fbfc] hover:text-[#4bc6b9] transition-all hover:-translate-y-1 shadow-sm border-2 border-white group" title="TikTok Ahawi Channel">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.9-.32-1.98-.23-2.81.36-.54.38-.89.98-1.03 1.64-.13.47-.12.95-.12 1.44.02.5.11.99.3 1.45.33.8.96 1.47 1.74 1.84.57.28 1.19.38 1.81.35 1.14-.03 2.25-.56 2.9-1.51.4-.59.58-1.3.62-2.01.05-3.15.03-6.3.03-9.44z"/></svg>
                    </a>
                </div>

            </div>
        </div>
    </footer>

    <script>
        const fakeNames = ["Ganjar", "Gibran", "Prabowo", "Jokowi", "Mulyono", "Fuad", "Gatot", "Fufufafa"];
        const fakeProducts = ["86 Diamond MLBB", "1000 Followers IG", "Netflix Premium", "Spotify Family", "PUBG 250 UC"];
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
    </script>
</body>
</html>