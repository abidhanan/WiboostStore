<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Admin Wiboost</title>
    <link rel="icon" type="image/png" href="{{ asset('images/wiboost-logo.png') }}?v=20260426-full">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f4f9ff; }
        ::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
        
        /* Floating Animations */
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body
    x-data="{
        sidebarOpen: false,
        restoreSidebarScroll() {
            this.$nextTick(() => {
                try {
                    const savedScroll = window.localStorage.getItem('wiboost-admin-sidebar-scroll');
                    if (savedScroll && this.$refs.adminSidebarNav) {
                        this.$refs.adminSidebarNav.scrollTop = Number(savedScroll);
                    }
                } catch (error) {}
            });
        },
        saveSidebarScroll() {
            try {
                if (this.$refs.adminSidebarNav) {
                    window.localStorage.setItem('wiboost-admin-sidebar-scroll', this.$refs.adminSidebarNav.scrollTop);
                }
            } catch (error) {}
        }
    }"
    x-init="restoreSidebarScroll()"
    class="min-h-screen bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white relative overflow-x-hidden"
>
    <div class="fixed top-20 right-10 text-4xl animate-float opacity-50 pointer-events-none z-0 hidden lg:block">☁️</div>
    <div class="fixed bottom-20 right-1/4 text-5xl animate-float-delayed opacity-40 pointer-events-none z-0 hidden lg:block">✨</div>
    <div class="fixed top-1/2 right-[10%] text-3xl animate-float opacity-30 pointer-events-none z-0 hidden lg:block">⭐</div>

    @php
        $adminNav = [
            ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Dashboard'],
            ['route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'label' => 'Pengguna'],
            ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'label' => 'Kategori'],
            ['route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'label' => 'Produk'],
            ['route' => 'admin.promos.index', 'pattern' => 'admin.promos.*', 'label' => 'Banner'],
            ['route' => 'admin.tutorials.index', 'pattern' => 'admin.tutorials.*', 'label' => 'Tutorial'],
            ['route' => 'admin.transactions.index', 'pattern' => 'admin.transactions.*', 'label' => 'Transaksi'],
            ['route' => 'admin.manual-orders.index', 'pattern' => 'admin.manual-orders.*', 'label' => 'Manual Order'],
            ['route' => 'admin.deposits.index', 'pattern' => 'admin.deposits.*', 'label' => 'Deposit'],
            ['route' => 'admin.reports.index', 'pattern' => 'admin.reports.*', 'label' => 'Laporan'],
            ['route' => 'profile.edit', 'pattern' => 'profile.edit', 'label' => 'Profil'],
        ];
    @endphp

    <div class="flex min-h-screen relative z-10">
        <button type="button" @click="sidebarOpen = true" class="fixed left-4 top-4 z-50 flex h-14 w-14 items-center justify-center rounded-2xl border-4 border-white bg-white/95 text-[#5a76c8] shadow-xl shadow-[#bde0fe]/35 backdrop-blur-md xl:hidden transition-transform active:scale-95">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>

        <aside class="sticky top-0 hidden h-screen w-80 shrink-0 p-5 xl:block">
            <div class="flex h-full flex-col rounded-[2.5rem] border-4 border-white bg-white/80 shadow-2xl shadow-[#bde0fe]/30 backdrop-blur-xl">
                <div class="border-b-4 border-dashed border-[#f4f9ff] px-8 py-6">
                    <a href="{{ route('home') }}" class="flex items-center gap-4 group">
                        <img src="{{ asset('images/wiboost-logo.png') }}?v=20260426-full" alt="Logo Wiboost Store" class="h-12 w-12 shrink-0 object-contain drop-shadow-sm group-hover:scale-110 transition-transform">
                        <p class="whitespace-nowrap text-[1.5rem] font-black leading-none text-[#2b3a67]">Wiboost <span class="text-[#5a76c8]">Admin</span></p>
                    </a>
                </div>

                <nav x-ref="adminSidebarNav" @scroll.debounce.150ms="saveSidebarScroll()" class="flex-1 space-y-2 overflow-y-auto overscroll-contain px-5 py-6">
                    <p class="px-3 mb-2 text-[10px] font-black uppercase tracking-[0.25em] text-[#8faaf3]">Menu Utama</p>
                    @foreach($adminNav as $item)
                        <a href="{{ route($item['route']) }}" @click="saveSidebarScroll()" class="flex items-center justify-between rounded-full px-6 py-3.5 text-sm font-black transition-all duration-300 {{ request()->routeIs($item['pattern']) ? 'border-2 border-white bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30 scale-[1.02]' : 'border-2 border-transparent text-[#5a76c8] hover:border-white hover:bg-[#f4f9ff] hover:scale-[1.02]' }}">
                            <span>{{ $item['label'] }}</span>
                            <span>&rarr;</span>
                        </a>
                    @endforeach
                </nav>

                <div class="border-t-4 border-dashed border-[#f4f9ff] p-5">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center justify-center rounded-full border-4 border-white bg-[#ffe5e5] px-6 py-3.5 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 transition-all hover:bg-[#ffcccc] active:scale-95 gap-2">
                            Keluar Akun 🚪
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex min-h-screen min-w-0 flex-1 flex-col p-4 pt-20 md:p-6 md:pt-20 xl:p-8">
            <div class="sticky top-4 z-40 mb-8 rounded-[2.5rem] border-4 border-white bg-white/80 px-6 py-5 shadow-xl shadow-[#bde0fe]/20 backdrop-blur-xl md:px-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <h1 class="text-2xl font-black leading-tight text-[#2b3a67] md:text-3xl drop-shadow-sm">@yield('title')</h1>
                        @hasSection('admin_header_subtitle')
                            <p class="mt-1.5 text-sm font-bold text-[#8faaf3]">@yield('admin_header_subtitle')</p>
                        @endif
                    </div>

                    @hasSection('admin_header_actions')
                        <div class="flex w-full flex-col gap-3 sm:flex-row sm:flex-wrap lg:w-auto lg:items-center lg:justify-end xl:flex-nowrap">
                            @yield('admin_header_actions')
                        </div>
                    @endif
                </div>
            </div>

            <main class="flex-1 rounded-[2.5rem]">
                @yield('content')
            </main>
        </div>
    </div>

    <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-[70] xl:hidden" aria-modal="true" role="dialog">
        <div x-show="sidebarOpen" x-transition.opacity class="absolute inset-0 bg-[#2b3a67]/60 backdrop-blur-md" @click="sidebarOpen = false"></div>
        <aside x-show="sidebarOpen" 
               x-transition:enter="transition ease-out duration-300" 
               x-transition:enter-start="-translate-x-full" 
               x-transition:enter-end="translate-x-0" 
               x-transition:leave="transition ease-in duration-200" 
               x-transition:leave-start="translate-x-0" 
               x-transition:leave-end="-translate-x-full" 
               class="absolute inset-y-4 left-4 max-h-[calc(100vh-2rem)] w-[min(22rem,calc(100vw-2rem))] overflow-y-auto overscroll-contain rounded-[2.5rem] border-4 border-white bg-white p-6 shadow-2xl flex flex-col">
            
            <div class="mb-6 flex items-center justify-between border-b-4 border-dashed border-[#f4f9ff] pb-6">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/wiboost-logo.png') }}?v=20260426-full" alt="Logo Wiboost Store" class="h-12 w-12 shrink-0 object-contain drop-shadow-sm">
                    <p class="whitespace-nowrap text-xl font-black leading-none text-[#2b3a67]">Wiboost <span class="text-[#5a76c8]">Admin</span></p>
                </div>
                <button type="button" @click="sidebarOpen = false" class="flex h-12 w-12 items-center justify-center rounded-2xl border-2 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-sm text-xl font-black transition-transform active:scale-95">&times;</button>
            </div>

            <nav class="space-y-2 flex-1">
                @foreach($adminNav as $item)
                    <a href="{{ route($item['route']) }}" @click="sidebarOpen = false" class="flex items-center justify-between rounded-full px-6 py-4 font-black transition-all {{ request()->routeIs($item['pattern']) ? 'bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30 border-2 border-white' : 'bg-[#f4f9ff] text-[#2b3a67] border-2 border-transparent hover:border-white' }}">
                        <span>{{ $item['label'] }}</span>
                        <span>&rarr;</span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-6 pt-6 border-t-4 border-dashed border-[#f4f9ff]">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center rounded-full border-4 border-white bg-[#ffe5e5] px-6 py-4 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 active:scale-95 transition-transform gap-2">
                        Keluar Akun 🚪
                    </button>
                </form>
            </div>
        </aside>
    </div>
</body>
</html>
