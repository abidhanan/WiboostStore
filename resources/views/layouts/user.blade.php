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
        /* Gradasi Khas Wiboost */
        .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
        /* Scrollbar custom agar estetik */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f4f9ff; }
        ::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
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

    <main class="flex-1 w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mb-16">
        @yield('content')
    </main>

    <footer class="bg-white py-8 border-t-4 border-white mt-auto rounded-t-[3rem] shadow-[0_-10px_20px_rgba(189,224,254,0.2)]">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <div class="flex items-center justify-center gap-2 mb-4">
                <div class="w-8 h-8 bg-[#5a76c8] rounded-xl flex items-center justify-center text-white font-black text-sm shadow-inner border-2 border-[#bde0fe]">W</div>
                <span class="font-extrabold text-xl tracking-tight text-[#2b3a67]">Wiboost</span>
            </div>
            <p class="text-sm font-black text-[#8faaf3]">&copy; {{ date('Y') }} Wiboost Store. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</body>
</html>