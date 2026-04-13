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
<body x-data="{ sidebarOpen: false }" class="min-h-screen bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white">
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
        ];
    @endphp

    <div class="flex min-h-screen">
        <aside class="sticky top-0 hidden h-screen w-72 shrink-0 p-4 xl:block">
            <div class="flex h-full flex-col rounded-[2.5rem] border-4 border-white bg-white/90 shadow-xl shadow-[#bde0fe]/35 backdrop-blur-md">
                <div class="border-b-2 border-dashed border-[#f0f5ff] px-8 py-7">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-[1rem] border-2 border-white bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] text-2xl font-black text-white shadow-inner">W</div>
                        <div>
                            <p class="text-2xl font-black text-[#2b3a67]">Admin Panel</p>
                            <p class="text-[10px] font-black uppercase tracking-[0.35em] text-[#8faaf3]">Wiboost Store</p>
                        </div>
                    </div>
                </div>

                <nav class="flex-1 space-y-2 overflow-y-auto px-4 py-6">
                    @foreach($adminNav as $item)
                        <a href="{{ route($item['route']) }}" class="flex items-center justify-between rounded-[1.4rem] px-5 py-3.5 font-black transition {{ request()->routeIs($item['pattern']) ? 'border-2 border-white bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/25' : 'border-2 border-transparent text-[#5a76c8] hover:border-white hover:bg-[#f0f5ff]' }}">
                            <span>{{ $item['label'] }}</span>
                            <span>→</span>
                        </a>
                    @endforeach
                </nav>

                <div class="border-t-2 border-dashed border-[#f0f5ff] p-4">
                    <a href="{{ route('home') }}" class="mb-3 flex items-center justify-center rounded-[1.4rem] border-2 border-white bg-[#f8fbff] px-5 py-3 font-black text-[#5a76c8] shadow-sm">
                        Lihat Website
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center justify-center rounded-[1.4rem] border-2 border-white bg-[#ffe5e5] px-5 py-3 font-black text-[#ff6b6b] shadow-sm transition hover:bg-[#ffcccc]">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex min-h-screen min-w-0 flex-1 flex-col p-3 md:p-4 xl:p-5">
            <header class="sticky top-3 z-40 mb-4 rounded-[2rem] border-4 border-white bg-white/90 px-4 py-4 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-md md:px-6">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="sidebarOpen = true" class="flex h-11 w-11 items-center justify-center rounded-2xl border-2 border-white bg-[#f0f5ff] text-[#5a76c8] shadow-sm xl:hidden">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Admin Workspace</p>
                            <h1 class="text-xl font-black text-[#2b3a67] md:text-2xl">@yield('title')</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('profile.edit') }}" class="hidden rounded-full border-2 border-white bg-[#f0f5ff] px-5 py-2.5 text-sm font-black text-[#5a76c8] shadow-sm sm:inline-flex">
                            {{ Auth::user()->name }}
                        </a>
                        <a href="{{ route('home') }}" class="rounded-full border-2 border-white bg-[#f8fbff] px-4 py-2.5 text-sm font-black text-[#5a76c8] shadow-sm">
                            Website
                        </a>
                    </div>
                </div>
            </header>

            <main class="flex-1 rounded-[2rem]">
                @yield('content')
            </main>
        </div>
    </div>

    <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-[70] xl:hidden" aria-modal="true" role="dialog">
        <div x-show="sidebarOpen" x-transition.opacity class="absolute inset-0 bg-[#2b3a67]/55 backdrop-blur-sm" @click="sidebarOpen = false"></div>
        <aside x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-6 opacity-0" x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="-translate-x-6 opacity-0" class="absolute inset-y-3 left-3 w-[min(22rem,calc(100vw-1.5rem))] rounded-[2rem] border-4 border-white bg-white p-5 shadow-2xl">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Navigasi Admin</p>
                    <h2 class="text-2xl font-black text-[#2b3a67]">{{ Auth::user()->name }}</h2>
                </div>
                <button type="button" @click="sidebarOpen = false" class="flex h-11 w-11 items-center justify-center rounded-2xl border-2 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-sm">✕</button>
            </div>

            <nav class="space-y-3">
                @foreach($adminNav as $item)
                    <a href="{{ route($item['route']) }}" class="flex items-center justify-between rounded-[1.4rem] px-5 py-4 font-black {{ request()->routeIs($item['pattern']) ? 'bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/20' : 'bg-[#f8fbff] text-[#2b3a67]' }}">
                        <span>{{ $item['label'] }}</span>
                        <span>→</span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-6 space-y-3">
                <a href="{{ route('profile.edit') }}" class="flex items-center justify-center rounded-[1.4rem] border-2 border-white bg-[#f0f5ff] px-5 py-3 font-black text-[#5a76c8] shadow-sm">
                    Profil Admin
                </a>
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
