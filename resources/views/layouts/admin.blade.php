<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Admin Wiboost</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Nunito', sans-serif; } 
        /* Scrollbar custom agar estetik */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f4f9ff; }
        ::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
    </style>
</head>
<body class="bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white flex h-screen overflow-hidden">

    <aside class="w-72 bg-white/90 backdrop-blur-md border-r-4 border-white shadow-xl shadow-[#bde0fe]/40 hidden md:flex flex-col rounded-r-[2.5rem] z-20 my-4 ml-4">
        <div class="h-24 flex items-center px-8 border-b-2 border-dashed border-[#f0f5ff]">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] rounded-[1rem] flex items-center justify-center text-white font-black text-2xl shadow-inner border-2 border-white">W</div>
                <span class="font-black text-2xl tracking-tight text-[#2b3a67]">Admin<span class="text-[#8faaf3]">Panel</span></span>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-2">
            <p class="px-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-2">Menu Utama</p>
            
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-4 px-5 py-3.5 rounded-[1.5rem] font-black transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30 border-2 border-white' : 'text-[#5a76c8] hover:bg-[#f0f5ff] hover:border-2 border-2 border-transparent hover:border-white' }}">
                <span class="text-xl drop-shadow-sm">🏠</span> Dashboard
            </a>
            
            <p class="px-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mt-6 mb-2">Katalog Toko</p>
            
            <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-4 px-5 py-3.5 rounded-[1.5rem] font-black transition-all {{ request()->routeIs('admin.categories.*') ? 'bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30 border-2 border-white' : 'text-[#5a76c8] hover:bg-[#f0f5ff] hover:border-2 border-2 border-transparent hover:border-white' }}">
                <span class="text-xl drop-shadow-sm">🗂️</span> Kategori
            </a>
            <a href="{{ route('admin.products.index') }}" class="flex items-center gap-4 px-5 py-3.5 rounded-[1.5rem] font-black transition-all {{ request()->routeIs('admin.products.*') ? 'bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30 border-2 border-white' : 'text-[#5a76c8] hover:bg-[#f0f5ff] hover:border-2 border-2 border-transparent hover:border-white' }}">
                <span class="text-xl drop-shadow-sm">🛍️</span> Produk
            </a>
            <a href="{{ route('admin.promos.index') }}" class="flex items-center gap-4 px-5 py-3.5 rounded-[1.5rem] font-black transition-all {{ request()->routeIs('admin.promos.*') ? 'bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30 border-2 border-white' : 'text-[#5a76c8] hover:bg-[#f0f5ff] hover:border-2 border-2 border-transparent hover:border-white' }}">
                <span class="text-xl drop-shadow-sm">📢</span> Banner
            </a>

            <p class="px-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mt-6 mb-2">Keuangan</p>
            
            <a href="{{ route('admin.transactions.index') }}" class="flex items-center gap-4 px-5 py-3.5 rounded-[1.5rem] font-black transition-all {{ request()->routeIs('admin.transactions.*') ? 'bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30 border-2 border-white' : 'text-[#5a76c8] hover:bg-[#f0f5ff] hover:border-2 border-2 border-transparent hover:border-white' }}">
                <span class="text-xl drop-shadow-sm">🧾</span> Transaksi
            </a>
            <a href="{{ route('admin.deposits.index') }}" class="flex items-center gap-4 px-5 py-3.5 rounded-[1.5rem] font-black transition-all {{ request()->routeIs('admin.deposits.*') ? 'bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30 border-2 border-white' : 'text-[#5a76c8] hover:bg-[#f0f5ff] hover:border-2 border-2 border-transparent hover:border-white' }}">
                <span class="text-xl drop-shadow-sm">💰</span> Deposit
            </a>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-24 bg-white/80 backdrop-blur-md shadow-sm border-b-4 border-white flex items-center justify-between px-6 lg:px-10 z-10 rounded-b-[2.5rem] mx-4 mt-4">
            <h2 class="text-xl font-black text-[#2b3a67] hidden md:block">@yield('title')</h2>
            
            <button class="md:hidden p-2 bg-[#f0f5ff] rounded-xl text-[#5a76c8] border-2 border-white shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>

            <div class="flex items-center gap-3 md:gap-4">
                <a href="{{ route('profile.edit') }}" class="text-sm font-black transition-all px-4 py-2.5 rounded-full border-2 border-white flex items-center gap-2 {{ request()->routeIs('profile.edit') ? 'bg-[#5a76c8] text-white shadow-md' : 'bg-[#f0f5ff] text-[#5a76c8] hover:bg-[#e0fbfc] hover:-translate-y-0.5 shadow-inner' }}" title="Pengaturan Profil">
                    <span>👨‍💻</span> 
                    <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="bg-[#ffe5e5] hover:bg-[#ffcccc] text-[#ff6b6b] px-5 py-2.5 rounded-full font-black transition-transform active:scale-95 border-2 border-white shadow-sm">
                        Keluar
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 lg:p-10">
            @yield('content')
        </main>
    </div>

</body>
</html>