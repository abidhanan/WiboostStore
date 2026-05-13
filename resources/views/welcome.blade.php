<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/wiboost-logo.png') }}?v=20260426-full">
    <title>Wiboost Store</title>

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
    </style>
</head>
<body class="wiboost-compact-ui wiboost-welcome-page bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white flex flex-col min-h-screen">

    <nav class="fixed w-full z-50 top-4 px-4 sm:px-6 lg:px-8 pointer-events-none">
        <div class="max-w-7xl mx-auto pointer-events-auto">
            <div class="bg-white/90 backdrop-blur-md border-4 border-white shadow-xl shadow-[#bde0fe]/50 rounded-[2rem] flex justify-between items-center h-20 px-6 sm:px-8">
                <a href="{{ Auth::check() ? (Auth::user()->isAdmin() ? route('admin.dashboard') : route('user.dashboard')) : route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/wiboost-logo.png') }}?v=20260426-full" alt="Logo Wiboost Store" class="h-12 w-12 shrink-0 object-contain drop-shadow-sm sm:h-14 sm:w-14">
                    <span class="font-extrabold text-2xl tracking-tight text-[#2b3a67]">Wiboost <span class="text-[#5a76c8]">Store</span></span>
                </a>
                <div class="flex items-center gap-3">
                    @auth
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="text-sm font-extrabold text-white bg-[#5a76c8] hover:bg-[#4760a9] px-6 py-2.5 rounded-full transition-transform active:scale-95 shadow-md">Panel Admin</a>
                        @else
                            <a href="{{ route('user.dashboard') }}" class="text-sm font-extrabold text-white bg-[#5a76c8] hover:bg-[#4760a9] px-6 py-2.5 rounded-full transition-transform active:scale-95 shadow-md">Dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('register') }}" class="text-sm font-extrabold text-[#5a76c8] hover:bg-[#f0f5ff] px-5 py-2.5 rounded-full transition hidden sm:block">Daftar</a>
                        <a href="{{ route('login') }}" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white text-sm font-extrabold px-7 py-2.5 rounded-full transition-transform active:scale-95 shadow-lg shadow-[#8faaf3]/40 border-2 border-white">Masuk</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- SECTION HERO & DATA REAL-TIME -->
    <main class="relative pt-40 pb-24 lg:pt-56 lg:pb-32 bg-wiboost-sky overflow-hidden border-b-[6px] border-[#f0f5ff]">
        <div class="absolute top-32 left-10 text-4xl animate-float opacity-70">☁️</div>
        <div class="absolute top-20 right-20 text-4xl animate-float-delayed opacity-70">✨</div>
        <div class="absolute bottom-40 left-1/4 text-3xl animate-float-delayed opacity-60">⭐</div>
        <div class="absolute bottom-20 right-1/4 text-5xl animate-float opacity-80">☁️</div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <div class="inline-block bg-white/80 backdrop-blur-sm text-[#5a76c8] font-black text-sm px-5 py-2 rounded-full mb-6 border-2 border-white shadow-sm">
                Pusat Kebutuhan Digitalmu!
            </div>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-6">
                <h1 class="text-7xl md:text-8xl lg:text-[8rem] font-black tracking-tight text-[#2b3a67] drop-shadow-sm">
                    Wiboost <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#5a76c8] to-[#9a8ce5]">Store</span>
                </h1>
            </div>

            <p class="mt-2 text-sm md:text-base text-[#4a5f96] max-w-5xl mx-auto font-bold leading-normal">
                Wiboost Store adalah platform layanan digital terintegrasi yang menyediakan katalog lengkap mencakup layanan Pemasaran Media Sosial (SMM) guna menunjang pertumbuhan metrik akun sosial media, top up game online secara instan, pembelian paket data operator, hingga penyediaan akses langganan aplikasi premium dengan harga kompetitif, di mana seluruh transaksi diproses secara otomatis dan didukung oleh pembayaran QRIS yang support semua metode pembayaran secara praktis dan aman.
            </p>
            
            <div class="mt-12 flex flex-col sm:flex-row justify-center gap-4">
                <a href="#kategori" onclick="event.preventDefault(); document.getElementById('kategori').scrollIntoView({behavior: 'smooth', block: 'center'});" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white font-extrabold text-lg px-10 py-4 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white flex items-center justify-center gap-3">
                    Beli Sekarang
                </a>
            </div>

            <div class="mt-24 lg:mt-32 max-w-5xl mx-auto">
                <div class="bg-white/90 backdrop-blur-md rounded-[2rem] shadow-xl shadow-[#bde0fe]/40 border-4 border-white p-6 md:p-8 flex flex-col md:flex-row justify-around items-center gap-8 animate-float">
                    <div class="text-center">
                        <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Total Pengguna</p>
                        <p class="mt-1 text-4xl font-black text-[#5a76c8]">{{ number_format($totalUsers, 0, ',', '.') }}<span class="text-2xl text-[#a3bbfb]">+</span></p>
                    </div>
                    <div class="hidden md:block w-1 h-12 bg-[#f0f5ff] rounded-full"></div>
                    <div class="text-center">
                        <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Total Layanan</p>
                        <p class="mt-1 text-4xl font-black text-[#9a8ce5]">{{ number_format($activeProducts, 0, ',', '.') }}</p>
                    </div>
                    <div class="hidden md:block w-1 h-12 bg-[#f0f5ff] rounded-full"></div>
                    <div class="text-center">
                        <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Total Transaksi</p>
                        <p class="mt-1 text-4xl font-black text-[#4bc6b9]">{{ number_format($totalTransactions, 0, ',', '.') }}<span class="text-2xl text-[#8ce0d7]">+</span></p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- SECTION KATEGORI (PUTIH) -->
    <section id="kategori" class="py-24 lg:py-32 bg-white relative flex-1 z-10 border-b-[6px] border-[#f0f5ff]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-6 text-xs border-2 border-white">Katalog Utama</div>
                <h2 class="text-3xl md:text-5xl font-black text-[#2b3a67] tracking-tight">Pilih Kategori</h2>
            </div>

            <div class="mx-auto flex max-w-5xl flex-wrap justify-center gap-4 md:gap-5">
                @forelse($categories as $cat)
                    @if(is_null($cat->parent_id))
                        <a href="{{ route('login') }}" class="group block w-[calc(50%-0.5rem)] sm:w-56 md:w-60 lg:w-56 bg-[#f4f9ff] rounded-[1.5rem] p-4 border-4 border-white shadow-lg shadow-[#bde0fe]/20 hover:-translate-y-2 hover:border-[#bde0fe] transition-all duration-300 text-center relative flex flex-col items-center">
                            <div class="w-16 h-16 bg-white rounded-2xl border-2 border-white shadow-sm flex items-center justify-center text-[#5a76c8] mb-3 group-hover:scale-110 transition-transform duration-300">
                                @if($cat->image)
                                    <img src="{{ Storage::url($cat->image) }}" alt="{{ $cat->name }}" class="h-full w-full object-contain drop-shadow-sm">
                                @else
                                @if($cat->emote)
                                    <span class="text-4xl drop-shadow-sm">{{ $cat->emote }}</span>
                                @else
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                @endif
                                @endif
                            </div>
                            <h3 class="font-black text-[#2b3a67] text-sm md:text-base leading-tight">{{ $cat->name }}</h3>
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-block px-4 py-1 bg-white text-[#4bc6b9] font-black rounded-full mb-6 text-xs shadow-sm">Keunggulan</div>
                <h2 class="text-3xl md:text-5xl font-black text-[#2b3a67] tracking-tight">Kenapa Harus Wiboost Store?</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <div class="bg-white rounded-[2rem] p-8 border-4 border-white shadow-xl shadow-[#bde0fe]/20 hover:-translate-y-2 transition-transform duration-300 text-center">
                    <div class="w-16 h-16 bg-[#f4f9ff] rounded-2xl border-2 border-[#e0fbfc] flex items-center justify-center text-[#5a76c8] mb-5 mx-auto text-3xl shadow-sm">⚡</div>
                    <h3 class="font-black text-[#2b3a67] text-lg mb-3">Proses Otomatis</h3>
                    <p class="text-sm text-[#4a5f96] font-bold leading-relaxed">Sistem kami berjalan 24/7. Transaksi diproses seketika tanpa perlu konfirmasi manual dari admin.</p>
                </div>
                <div class="bg-white rounded-[2rem] p-8 border-4 border-white shadow-xl shadow-[#bde0fe]/20 hover:-translate-y-2 transition-transform duration-300 text-center">
                    <div class="w-16 h-16 bg-[#f4f9ff] rounded-2xl border-2 border-[#e0fbfc] flex items-center justify-center text-[#4bc6b9] mb-5 mx-auto text-3xl shadow-sm">🛡️</div>
                    <h3 class="font-black text-[#2b3a67] text-lg mb-3">Aman & Terpercaya</h3>
                    <p class="text-sm text-[#4a5f96] font-bold leading-relaxed">Pembayaran menggunakan QRIS resmi yang aman, serta privasi akun media sosial Anda terjamin 100%.</p>
                </div>
                <div class="bg-white rounded-[2rem] p-8 border-4 border-white shadow-xl shadow-[#bde0fe]/20 hover:-translate-y-2 transition-transform duration-300 text-center">
                    <div class="w-16 h-16 bg-[#f4f9ff] rounded-2xl border-2 border-[#e0fbfc] flex items-center justify-center text-[#9a8ce5] mb-5 mx-auto text-3xl shadow-sm">📦</div>
                    <h3 class="font-black text-[#2b3a67] text-lg mb-3">Layanan Terlengkap</h3>
                    <p class="text-sm text-[#4a5f96] font-bold leading-relaxed">Mulai dari Top Up Game, SMM Panel, hingga Aplikasi Premium tersedia lengkap dalam satu platform.</p>
                </div>
                <div class="bg-white rounded-[2rem] p-8 border-4 border-white shadow-xl shadow-[#bde0fe]/20 hover:-translate-y-2 transition-transform duration-300 text-center">
                    <div class="w-16 h-16 bg-[#f4f9ff] rounded-2xl border-2 border-[#e0fbfc] flex items-center justify-center text-[#e1306c] mb-5 mx-auto text-3xl shadow-sm">💸</div>
                    <h3 class="font-black text-[#2b3a67] text-lg mb-3">Harga Kompetitif</h3>
                    <p class="text-sm text-[#4a5f96] font-bold leading-relaxed">Dapatkan layanan berkualitas dengan harga termurah. Sangat cocok untuk digunakan sendiri atau dijual kembali.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION ALUR PEMESANAN (PUTIH) -->
    <section id="alur-pemesanan" class="py-24 lg:py-32 bg-white relative z-10 border-b-[6px] border-[#f0f5ff]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-6 text-xs border-2 border-white">Cara Kerja</div>
                <h2 class="text-3xl md:text-5xl font-black text-[#2b3a67] tracking-tight">Alur Pemesanan</h2>
            </div>

            <div class="relative max-w-5xl mx-auto">
                <div class="hidden md:block absolute top-10 left-[12%] right-[12%] h-2 bg-[#f4f9ff] rounded-full shadow-inner z-0"></div>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-10 md:gap-4 relative z-10">
                    <div class="flex flex-col items-center text-center group">
                        <div class="w-20 h-20 bg-[#5a76c8] text-white font-black text-3xl rounded-3xl border-4 border-white shadow-xl shadow-[#5a76c8]/40 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">1</div>
                        <h3 class="font-black text-[#2b3a67] text-xl mb-2">Pilih Layanan</h3>
                        <p class="text-sm text-[#4a5f96] font-bold px-4">Telusuri katalog dan pilih layanan digital yang Anda butuhkan.</p>
                    </div>
                    <div class="flex flex-col items-center text-center group">
                        <div class="w-20 h-20 bg-[#5a76c8] text-white font-black text-3xl rounded-3xl border-4 border-white shadow-xl shadow-[#5a76c8]/40 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">2</div>
                        <h3 class="font-black text-[#2b3a67] text-xl mb-2">Isi Data</h3>
                        <p class="text-sm text-[#4a5f96] font-bold px-4">Masukkan data yang diminta seperti User ID game atau Link Sosial Media.</p>
                    </div>
                    <div class="flex flex-col items-center text-center group">
                        <div class="w-20 h-20 bg-[#5a76c8] text-white font-black text-3xl rounded-3xl border-4 border-white shadow-xl shadow-[#5a76c8]/40 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">3</div>
                        <h3 class="font-black text-[#2b3a67] text-xl mb-2">Pembayaran</h3>
                        <p class="text-sm text-[#4a5f96] font-bold px-4">Lakukan pembayaran dengan scan QRIS atau metode lainnya yang tersedia.</p>
                    </div>
                    <div class="flex flex-col items-center text-center group">
                        <div class="w-20 h-20 bg-[#5a76c8] text-white font-black text-3xl rounded-3xl border-4 border-white shadow-xl shadow-[#5a76c8]/40 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">4</div>
                        <h3 class="font-black text-[#2b3a67] text-xl mb-2">Selesai!</h3>
                        <p class="text-sm text-[#4a5f96] font-bold px-4">Pesanan Anda akan langsung diproses secara instan oleh sistem kami.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION PERTANYAAN UMUM (FAQ) (BIRU) -->
    <section id="faq" class="py-24 lg:py-32 bg-[#f4f9ff] border-b-[6px] border-white relative z-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-block px-4 py-1 bg-white text-[#4bc6b9] font-black rounded-full mb-6 text-xs shadow-sm">FAQ</div>
                <h2 class="text-3xl md:text-5xl font-black text-[#2b3a67] tracking-tight">Pertanyaan Umum</h2>
            </div>

            <div class="flex flex-col gap-4">
                <details class="bg-white rounded-[1.5rem] border-4 border-white shadow-lg shadow-[#bde0fe]/20 overflow-hidden cursor-pointer">
                    <summary class="flex justify-between items-center p-6 font-black text-[#2b3a67] text-base md:text-lg focus:outline-none">
                        <span>Berapa lama waktu yang dibutuhkan untuk proses pesanan?</span>
                        <span class="text-[#5a76c8]">
                            <svg class="faq-icon w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-sm text-[#4a5f96] font-bold leading-relaxed border-t-2 border-[#f0f5ff] pt-4 mt-1 bg-white mx-2 mb-2">
                        Sebagian besar layanan kami diproses secara otomatis dan instan dalam hitungan detik setelah pembayaran berhasil dikonfirmasi. Untuk layanan tertentu (seperti SMM dengan target tinggi), proses mungkin membutuhkan waktu yang sedikit lebih lama sesuai deskripsi produk.
                    </div>
                </details>

                <details class="bg-white rounded-[1.5rem] border-4 border-white shadow-lg shadow-[#bde0fe]/20 overflow-hidden cursor-pointer">
                    <summary class="flex justify-between items-center p-6 font-black text-[#2b3a67] text-base md:text-lg focus:outline-none">
                        <span>Apakah butuh password akun sosmed saya?</span>
                        <span class="text-[#5a76c8]">
                            <svg class="faq-icon w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-sm text-[#4a5f96] font-bold leading-relaxed border-t-2 border-[#f0f5ff] pt-4 mt-1 bg-white mx-2 mb-2">
                        Tidak! Layanan SMM (Social Media Marketing) kami 100% aman karena hanya membutuhkan <strong>Username</strong> atau <strong>Link/Tautan</strong> postingan Anda. Kami tidak akan pernah meminta password akun Anda.
                    </div>
                </details>

                <details class="bg-white rounded-[1.5rem] border-4 border-white shadow-lg shadow-[#bde0fe]/20 overflow-hidden cursor-pointer">
                    <summary class="flex justify-between items-center p-6 font-black text-[#2b3a67] text-base md:text-lg focus:outline-none">
                        <span>Metode pembayaran apa saja yang tersedia?</span>
                        <span class="text-[#5a76c8]">
                            <svg class="faq-icon w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-sm text-[#4a5f96] font-bold leading-relaxed border-t-2 border-[#f0f5ff] pt-4 mt-1 bg-white mx-2 mb-2">
                        Kami mendukung pembayaran via <strong>QRIS</strong> yang dapat di-scan menggunakan seluruh aplikasi E-Wallet (Dana, Ovo, Gopay, LinkAja, ShopeePay) maupun Mobile Banking (BCA, Mandiri, BRI, BNI, dll). Pembayaran diproses otomatis tanpa perlu mengirim bukti transfer.
                    </div>
                </details>

                <details class="bg-white rounded-[1.5rem] border-4 border-white shadow-lg shadow-[#bde0fe]/20 overflow-hidden cursor-pointer">
                    <summary class="flex justify-between items-center p-6 font-black text-[#2b3a67] text-base md:text-lg focus:outline-none">
                        <span>Bagaimana jika pesanan saya gagal?</span>
                        <span class="text-[#5a76c8]">
                            <svg class="faq-icon w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-sm text-[#4a5f96] font-bold leading-relaxed border-t-2 border-[#f0f5ff] pt-4 mt-1 bg-white mx-2 mb-2">
                        Jika terjadi gangguan teknis dari pusat yang menyebabkan pesanan Anda berstatus gagal (Error/Canceled), sistem kami akan secara otomatis mengembalikan dana Anda dalam bentuk saldo akun yang bisa Anda gunakan kembali untuk transaksi lainnya.
                    </div>
                </details>
            </div>
        </div>
    </section>
    
    <footer class="bg-white pt-12 pb-8 relative overflow-hidden mt-auto">
        <div class="max-w-6xl mx-auto px-6 sm:px-8 relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8 mb-10">
                
                <div class="text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start gap-2 mb-3">
                        <img src="{{ asset('images/wiboost-logo.png') }}?v=20260426-full" alt="Logo Wiboost Store" class="h-9 w-9 shrink-0 object-contain drop-shadow-sm">
                        <span class="font-black text-xl text-[#2b3a67]">Wiboost <span class="text-[#5a76c8]">Store</span></span> 
                    </div>
                    <p class="text-xs font-bold text-[#8faaf3] max-w-xs">Pusat Kebutuhan Digitalmu!</p>
                </div>

                <div class="flex flex-col items-center md:items-end gap-3">
                    <p class="text-[10px] font-black text-[#2b3a67] uppercase tracking-widest mb-1">Follow Us</p>
                    <div class="flex items-center gap-4">
                        <a href="https://instagram.com/ahawi_channel" target="_blank" class="w-11 h-11 bg-[#f4f9ff] text-[#e1306c] rounded-2xl flex items-center justify-center border-2 border-white shadow-sm hover:bg-[#5a76c8] hover:text-white transition-all">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        
                        <a href="https://tiktok.com/@ahawi_channel" target="_blank" class="w-11 h-11 bg-[#f4f9ff] text-[#2b3a67] rounded-2xl flex items-center justify-center border-2 border-white shadow-sm hover:bg-[#5a76c8] hover:text-white transition-all">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/></svg>
                        </a>
                        <a href="https://youtube.com/@ahawi_channel" target="_blank" class="w-11 h-11 bg-[#f4f9ff] text-[#FF0000] rounded-2xl flex items-center justify-center border-2 border-white shadow-sm hover:bg-[#5a76c8] hover:text-white transition-all">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t-2 border-[#f0f5ff] pt-8 text-center">
                <div class="mb-5 flex flex-row flex-wrap items-center justify-center gap-1.5 sm:gap-3 w-full px-2">
                    <a href="{{ route('legal.show', 'terms') }}" class="rounded-full bg-[#f4f9ff] px-3 py-1.5 sm:px-4 sm:py-2 text-[10px] sm:text-xs font-black text-[#5a76c8] transition hover:bg-[#e0fbfc] whitespace-nowrap">Syarat & Ketentuan</a>
                    <a href="{{ route('legal.show', 'privacy-policy') }}" class="rounded-full bg-[#f4f9ff] px-3 py-1.5 sm:px-4 sm:py-2 text-[10px] sm:text-xs font-black text-[#5a76c8] transition hover:bg-[#e0fbfc] whitespace-nowrap">Privasi</a>
                    <a href="{{ route('legal.show', 'refund-policy') }}" class="rounded-full bg-[#f4f9ff] px-3 py-1.5 sm:px-4 sm:py-2 text-[10px] sm:text-xs font-black text-[#5a76c8] transition hover:bg-[#e0fbfc] whitespace-nowrap">Refund</a>
                    <a href="{{ route('legal.show', 'contact') }}" class="rounded-full bg-[#f4f9ff] px-3 py-1.5 sm:px-4 sm:py-2 text-[10px] sm:text-xs font-black text-[#5a76c8] transition hover:bg-[#e0fbfc] whitespace-nowrap">Kontak Admin</a>
                </div>
                <p class="text-[10px] font-black text-[#a3bbfb] uppercase tracking-widest">&copy; {{ date('Y') }} Wiboost Store - Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    @include('partials.floating-admin-report')
</body>
</html>