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
        /* Tema Warna Background Biru Langit (Sky Blue) khas Wiboost */
        .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
        .bg-wiboost-card { background-color: rgba(255, 255, 255, 0.9); }
        
        /* Animasi mengambang untuk elemen awan/bintang */
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
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
                🎉 #1 Platform Top Up Termurah
            </div>
            <h1 class="text-5xl md:text-7xl font-black tracking-tight mb-6 text-[#2b3a67] drop-shadow-sm">
                Semua Kebutuhan Digital<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#5a76c8] to-[#9a8ce5]">Dalam Satu Klik.</span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-[#4a5f96] max-w-2xl mx-auto font-bold leading-relaxed">
                Dari Suntik Sosmed, Top Up Game, hingga Aplikasi Premium. Proses otomatis 24/7, super cepat, dan dijamin bergaransi!
            </p>
            
            <div class="mt-12 flex flex-col sm:flex-row justify-center gap-4">
                <a href="#kategori" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white font-extrabold text-lg px-10 py-4 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white flex items-center justify-center gap-3">
                    Lihat Layanan 🚀
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
            <div class="bg-white rounded-[2rem] shadow-xl shadow-[#bde0fe]/40 border-4 border-white p-8 md:p-10 flex flex-col md:flex-row justify-around items-center gap-8 animate-float">
                <div class="text-center">
                    <p class="text-sm font-black text-[#8faaf3] uppercase tracking-widest">Pengguna Aktif</p>
                    <p class="mt-2 text-5xl font-black text-[#5a76c8]">{{ number_format($totalUsers, 0, ',', '.') }}<span class="text-3xl text-[#a3bbfb]">+</span></p>
                </div>
                
                <div class="hidden md:block w-1.5 h-20 bg-[#f0f5ff] rounded-full"></div>
                
                <div class="text-center">
                    <p class="text-sm font-black text-[#8faaf3] uppercase tracking-widest">Layanan Tersedia</p>
                    <p class="mt-2 text-5xl font-black text-[#9a8ce5]">{{ number_format($activeProducts, 0, ',', '.') }}</p>
                </div>
                
                <div class="hidden md:block w-1.5 h-20 bg-[#f0f5ff] rounded-full"></div>

                <div class="text-center">
                    <p class="text-sm font-black text-[#8faaf3] uppercase tracking-widest">Transaksi Sukses</p>
                    <p class="mt-2 text-5xl font-black text-[#4bc6b9]">{{ number_format($totalTransactions, 0, ',', '.') }}<span class="text-3xl text-[#8ce0d7]">+</span></p>
                </div>
            </div>
        </div>
    </div>

    <section id="kategori" class="py-20 bg-[#f4f9ff] relative flex-1 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-sm border-2 border-white shadow-sm">Pilih Kategori</div>
                <h2 class="text-4xl font-black text-[#2b3a67] tracking-tight">Katalog Layanan Wiboost</h2>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 md:gap-8">
                @forelse($categories as $cat)
                    <a href="{{ route('login') }}" class="group block bg-wiboost-card rounded-[2rem] p-6 border-4 border-white shadow-lg shadow-[#bde0fe]/40 hover:-translate-y-3 hover:shadow-2xl hover:shadow-[#bde0fe]/60 hover:border-[#bde0fe] transition-all duration-300 text-center relative overflow-hidden">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-white/80 to-transparent rounded-full opacity-50 pointer-events-none"></div>
                        
                        <div class="w-20 h-20 mx-auto bg-gradient-to-br from-[#f0f5ff] to-[#e0ebff] rounded-2xl border-2 border-white shadow-inner flex items-center justify-center text-[#5a76c8] mb-5 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                        </div>
                        <h3 class="font-black text-[#2b3a67] text-xl mb-1">{{ $cat->name }}</h3>
                        <p class="text-sm text-[#8faaf3] font-bold bg-[#f0f5ff] inline-block px-3 py-1 rounded-full">{{ $cat->products_count }} Produk</p>
                    </a>
                @empty
                    <div class="col-span-full text-center py-16 bg-white rounded-[2rem] border-4 border-dashed border-[#bde0fe]">
                        <div class="text-6xl mb-4">🛠️</div>
                        <p class="text-[#5a76c8] font-black text-xl">Kategori sedang disiapkan...</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <footer class="bg-white pt-16 pb-8 border-t-[6px] border-[#f0f5ff] relative overflow-hidden">
        <div class="absolute -top-10 -right-10 text-8xl opacity-10">☁️</div>
        <div class="absolute -top-5 -left-10 text-6xl opacity-10">☁️</div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <div class="flex items-center justify-center gap-2 mb-6">
                <div class="w-10 h-10 bg-[#5a76c8] rounded-xl flex items-center justify-center text-white font-black text-xl shadow-inner border-2 border-[#bde0fe]">W</div>
                <span class="font-extrabold text-2xl tracking-tight text-[#2b3a67]">Wiboost<span class="text-[#8faaf3]">Store</span></span>
            </div>
            
            <p class="text-[#8faaf3] font-bold mb-8">Level Up Cepat, Harga Sahabat. Dipercaya ribuan gamers & content creator.</p>
            
            <div class="flex justify-center gap-6 mb-10">
                <div class="w-12 h-12 bg-[#f0f5ff] rounded-full flex items-center justify-center text-[#5a76c8] hover:bg-[#bde0fe] hover:text-white transition-colors cursor-pointer border-2 border-white shadow-sm">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                </div>
                <div class="w-12 h-12 bg-[#f0f5ff] rounded-full flex items-center justify-center text-[#5a76c8] hover:bg-[#bde0fe] hover:text-white transition-colors cursor-pointer border-2 border-white shadow-sm">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                </div>
            </div>

            <div class="border-t-2 border-[#f0f5ff] pt-8">
                <p class="text-sm font-bold text-[#a3bbfb]">&copy; {{ date('Y') }} Wiboost Store. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

</body>
</html>