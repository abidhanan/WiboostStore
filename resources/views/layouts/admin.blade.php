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
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f4f9ff; }
        ::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
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
    class="min-h-screen bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white"
>
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

    <div class="flex min-h-screen">
        <button type="button" @click="sidebarOpen = true" class="fixed left-4 top-4 z-50 flex h-12 w-12 items-center justify-center rounded-2xl border-2 border-white bg-white/90 text-[#5a76c8] shadow-xl shadow-[#bde0fe]/35 backdrop-blur-md xl:hidden">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>

        <aside class="sticky top-0 hidden h-screen w-72 shrink-0 p-4 xl:block">
            <div class="flex h-full flex-col rounded-[2.5rem] border-4 border-white bg-white/90 shadow-xl shadow-[#bde0fe]/35 backdrop-blur-md">
                <div class="border-b-2 border-dashed border-[#f0f5ff] px-6 py-5">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/wiboost-logo.png') }}?v=20260426-full" alt="Logo Wiboost Store" class="h-10 w-10 shrink-0 object-contain drop-shadow-sm">
                        <p class="whitespace-nowrap text-[1.35rem] font-black leading-none text-[#2b3a67]">Wiboost <span class="text-[#5a76c8]">Store</span></p>
                    </div>
                </div>

                <nav x-ref="adminSidebarNav" @scroll.debounce.150ms="saveSidebarScroll()" class="flex-1 space-y-1.5 overflow-y-auto overscroll-contain px-4 py-4">
                    @foreach($adminNav as $item)
                        <a href="{{ route($item['route']) }}" @click="saveSidebarScroll()" class="flex items-center justify-between rounded-[1.25rem] px-5 py-3 text-sm font-black transition-colors {{ request()->routeIs($item['pattern']) ? 'border-2 border-white bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/25' : 'border-2 border-transparent text-[#5a76c8] hover:border-white hover:bg-[#f0f5ff]' }}">
                            <span>{{ $item['label'] }}</span>
                            <span>&rarr;</span>
                        </a>
                    @endforeach
                </nav>

                <div class="border-t-2 border-dashed border-[#f0f5ff] p-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center justify-center rounded-[1.25rem] border-2 border-white bg-[#ffe5e5] px-5 py-2.5 font-black text-[#ff6b6b] shadow-sm transition hover:bg-[#ffcccc]">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex min-h-screen min-w-0 flex-1 flex-col p-3 pt-16 md:p-4 md:pt-16 xl:p-5">
            <div class="sticky top-3 z-40 mb-5 rounded-[2rem] border-4 border-white bg-white/90 px-5 py-4 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-md md:px-7">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <h1 class="text-2xl font-black leading-tight text-[#2b3a67] md:text-3xl">@yield('title')</h1>
                        @hasSection('admin_header_subtitle')
                            <p class="mt-1 text-sm font-bold text-[#8faaf3]">@yield('admin_header_subtitle')</p>
                        @endif
                    </div>

                    @hasSection('admin_header_actions')
                        <div class="flex w-full flex-col gap-3 sm:flex-row sm:flex-wrap lg:w-auto lg:items-center lg:justify-end xl:flex-nowrap">
                            @yield('admin_header_actions')
                        </div>
                    @endif
                </div>
            </div>

            <main class="flex-1 rounded-[2rem]">
                @yield('content')
            </main>
        </div>
    </div>

    <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-[70] xl:hidden" aria-modal="true" role="dialog">
        <div x-show="sidebarOpen" class="absolute inset-0 bg-[#2b3a67]/55 backdrop-blur-sm" @click="sidebarOpen = false"></div>
        <aside x-show="sidebarOpen" class="absolute inset-y-3 left-3 max-h-[calc(100vh-1.5rem)] w-[min(22rem,calc(100vw-1.5rem))] overflow-y-auto overscroll-contain rounded-[2rem] border-4 border-white bg-white p-5 shadow-2xl">
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/wiboost-logo.png') }}?v=20260426-full" alt="Logo Wiboost Store" class="h-10 w-10 shrink-0 object-contain drop-shadow-sm">
                    <p class="whitespace-nowrap text-xl font-black leading-none text-[#2b3a67]">Wiboost <span class="text-[#5a76c8]">Store</span></p>
                </div>
                <button type="button" @click="sidebarOpen = false" class="flex h-11 w-11 items-center justify-center rounded-2xl border-2 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-sm">&times;</button>
            </div>

            <nav class="space-y-2">
                @foreach($adminNav as $item)
                    <a href="{{ route($item['route']) }}" @click="sidebarOpen = false" class="flex items-center justify-between rounded-[1.4rem] px-5 py-3.5 font-black transition-colors {{ request()->routeIs($item['pattern']) ? 'bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/20' : 'bg-[#f8fbff] text-[#2b3a67]' }}">
                        <span>{{ $item['label'] }}</span>
                        <span>&rarr;</span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-6 space-y-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center rounded-[1.4rem] border-2 border-white bg-[#ffe5e5] px-5 py-3 font-black text-[#ff6b6b] shadow-sm">
                        Keluar
                    </button>
                </form>
            </div>
        </aside>
    </div>
</body>
</html>
