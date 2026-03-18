<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Wiboost Store</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Nunito', sans-serif; } 
        .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f4f9ff; }
        ::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
        
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white flex flex-col min-h-screen">

    <nav class="fixed w-full z-50 top-4 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">
            <div class="bg-white/90 backdrop-blur-md border-4 border-white shadow-xl shadow-[#bde0fe]/50 rounded-[2.5rem] flex justify-between items-center h-20 px-4 sm:px-8 overflow-x-auto overflow-y-hidden no-scrollbar">
                
                <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0 mr-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] rounded-[1rem] flex items-center justify-center text-white font-black text-2xl shadow-inner border-2 border-white">W</div>
                    <span class="font-black text-2xl tracking-tight text-[#2b3a67] hidden sm:block">Wiboost</span>
                </a>

                <div class="flex items-center gap-1 sm:gap-3 shrink-0">
                    @if(Auth::check() && Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="text-xs font-black bg-[#ffe5e5] text-[#ff6b6b] px-4 py-2 rounded-full transition-transform active:scale-95 shadow-sm border border-white hover:bg-[#ffcccc]">Panel Admin</a>
                    @endif
                    <a href="{{ route('user.dashboard') }}" class="text-sm font-black px-4 py-2 rounded-full transition-colors {{ request()->routeIs('user.dashboard') ? 'bg-[#f0f5ff] text-[#5a76c8] border-2 border-white shadow-sm' : 'text-[#8faaf3] hover:text-[#5a76c8]' }}">Home</a>
                    <a href="{{ route('user.history') }}" class="text-sm font-black px-4 py-2 rounded-full transition-colors {{ request()->routeIs('user.history') ? 'bg-[#f0f5ff] text-[#5a76c8] border-2 border-white shadow-sm' : 'text-[#8faaf3] hover:text-[#5a76c8]' }}">Riwayat</a>
                    
                    <a href="{{ route('profile.edit') }}" class="text-sm font-black px-4 py-2 rounded-full transition-colors {{ request()->routeIs('profile.edit') ? 'bg-[#f0f5ff] text-[#5a76c8] border-2 border-white shadow-sm' : 'text-[#8faaf3] hover:text-[#5a76c8]' }}">Profil</a>
                    
                    <div class="w-1 h-8 bg-[#e0fbfc] mx-1 sm:mx-2 rounded-full hidden sm:block"></div>
                    
                    <form method="POST" action="{{ route('logout') }}" class="m-0 p-0 ml-1">
                        @csrf
                        <button type="submit" class="bg-[#ffe5e5] hover:bg-[#ffcccc] text-[#ff6b6b] text-sm font-black px-5 py-2.5 rounded-full transition-transform active:scale-95 shadow-sm border-2 border-white">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="h-32"></div>

    <main class="flex-1 w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mb-16 relative z-10">
        @yield('content')
    </main>

    <footer class="bg-white pt-12 pb-8 border-t-[6px] border-[#f0f5ff] mt-auto relative overflow-hidden z-20">
        <div class="max-w-6xl mx-auto px-6 sm:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8 mb-10">
                
                <div class="text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start gap-2 mb-3">
                        <div class="w-8 h-8 bg-[#5a76c8] rounded-lg flex items-center justify-center text-white font-black text-sm shadow-inner border-2 border-[#bde0fe]">W</div>
                        <span class="font-black text-xl text-[#2b3a67]">Wiboost<span class="text-[#5a76c8]">Store</span></span>
                    </div>
                    <p class="text-xs font-bold text-[#8faaf3] max-w-xs">Penyedia layanan sosial media dan top up game termurah & tercepat di Indonesia.</p>
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
                <p class="text-[10px] font-black text-[#a3bbfb] uppercase tracking-widest">&copy; {{ date('Y') }} Wiboost Store. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>
</body>
</html>