<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Wiboost Store</title>
    <link rel="icon" type="image/png" href="{{ asset('images/wiboost-logo.png') }}?v=20260426-full">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.vite-fallback')
    @include('partials.ui-performance-tuning')
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; overflow-x: hidden; }
        
        /* Custom Scrollbar Global */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f4f9ff; }
        ::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
        
        /* Hide Scrollbar khusus untuk Sidebar Navigasi agar lebih bersih */
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        
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
    class="wiboost-compact-ui min-h-screen bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white relative flex"
>
    @php
        $adminNav = [
            ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => '📊'],
            ['route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'label' => 'Pengguna', 'icon' => '👥'],
            ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'label' => 'Kategori', 'icon' => '📂'],
            ['route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'label' => 'Produk', 'icon' => '📦'],
            ['route' => 'admin.promos.index', 'pattern' => 'admin.promos.*', 'label' => 'Banner', 'icon' => '📢'],
            ['route' => 'admin.tutorials.index', 'pattern' => 'admin.tutorials.*', 'label' => 'Tutorial', 'icon' => '📖'],
            ['route' => 'admin.transactions.index', 'pattern' => 'admin.transactions.*', 'label' => 'Transaksi', 'icon' => '💳'],
            ['route' => 'admin.manual-orders.index', 'pattern' => 'admin.manual-orders.*', 'label' => 'Manual Order', 'icon' => '✍️'],
            ['route' => 'admin.deposits.index', 'pattern' => 'admin.deposits.*', 'label' => 'Deposit', 'icon' => '💰'],
            ['route' => 'admin.reports.index', 'pattern' => 'admin.reports.*', 'label' => 'Laporan', 'icon' => '📩'],
            ['route' => 'profile.edit', 'pattern' => 'profile.edit', 'label' => 'Profil', 'icon' => '👤'],
        ];

        $adminNav = array_values(array_filter(
            $adminNav,
            fn (array $item) => $item['route'] !== 'admin.manual-orders.index'
        ));
    @endphp

    <!-- Sidebar Desktop -->
    <aside class="sticky top-0 hidden h-screen w-80 shrink-0 p-5 xl:block z-40">
        <div class="flex h-full flex-col rounded-[2.5rem] border-4 border-white bg-white/80 shadow-2xl shadow-[#bde0fe]/30 backdrop-blur-xl">
            <div class="shrink-0 border-b-4 border-dashed border-[#f4f9ff] px-6 py-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/wiboost-logo.png') }}?v=20260426-full" alt="Logo Wiboost Store" class="h-12 w-12 shrink-0 object-contain drop-shadow-sm group-hover:scale-110 transition-transform">
                    <div class="min-w-0 flex-1">
                        <p class="text-xl font-black leading-tight text-[#2b3a67] break-words">Wiboost <span class="text-[#5a76c8]">Store</span></p>
                    </div>
                </a>
            </div>
            <nav x-ref="adminSidebarNav" @scroll.debounce.150ms="saveSidebarScroll()" class="flex-1 space-y-2 overflow-y-auto overscroll-contain px-5 py-6 hide-scroll">
                <p class="px-3 mb-2 text-[10px] font-black uppercase tracking-[0.25em] text-[#8faaf3]">Menu Utama</p>
                @foreach($adminNav as $item)
                    <a href="{{ route($item['route']) }}" @click="saveSidebarScroll()" class="flex items-center gap-3 rounded-full px-6 py-3.5 text-sm font-black transition-all duration-300 {{ request()->routeIs($item['pattern']) ? 'border-2 border-white bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30 scale-[1.02]' : 'border-2 border-transparent text-[#5a76c8] hover:border-white hover:bg-[#f4f9ff] hover:scale-[1.02]' }}">
                        <span class="text-lg">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="shrink-0 border-t-4 border-dashed border-[#f4f9ff] p-5">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center rounded-full border-4 border-white bg-[#ffe5e5] px-6 py-3.5 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 transition-all hover:bg-[#ffcccc] active:scale-95 gap-2">
                        Keluar Akun 🚪
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex min-h-screen min-w-0 flex-1 flex-col p-4 sm:p-6 xl:p-8 z-10">
        
        <!-- Header -->
        <div class="sticky top-4 z-40 mb-6 sm:mb-8 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/90 px-4 sm:px-6 py-4 shadow-xl shadow-[#bde0fe]/20 backdrop-blur-xl flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3 sm:gap-4 w-full lg:w-auto">
                <!-- Hamburger Button Mobile -->
                <button type="button" @click="sidebarOpen = true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-sm transition-transform active:scale-95 xl:hidden">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                
                <div class="min-w-0 flex-1">
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-black leading-tight text-[#2b3a67] drop-shadow-sm truncate">@yield('title')</h1>
                    @hasSection('admin_header_subtitle')
                        <p class="mt-1 text-[10px] sm:text-xs font-bold text-[#8faaf3] truncate">@yield('admin_header_subtitle')</p>
                    @endif
                </div>
            </div>

            <!-- Header Actions -->
            @hasSection('admin_header_actions')
                <div class="flex w-full shrink-0 flex-col gap-2 sm:flex-row sm:flex-wrap lg:w-auto lg:items-center lg:justify-end">
                    @yield('admin_header_actions')
                </div>
            @endif
        </div>

        <main class="flex-1 rounded-[2.5rem] min-w-0 w-full overflow-hidden">
            @yield('content')
        </main>
    </div>

    <!-- Sidebar Mobile Drawer -->
    <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-[70] xl:hidden" aria-modal="true" role="dialog">
        <!-- Backdrop bisa diklik untuk menutup sidebar tanpa tombol close -->
        <div x-show="sidebarOpen" x-transition.opacity class="absolute inset-0 bg-[#2b3a67]/60 backdrop-blur-md" @click="sidebarOpen = false"></div>
        
        <!-- Sidebar Container (Tanpa scroll di parent) -->
        <aside x-show="sidebarOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="absolute inset-y-4 left-4 max-h-[calc(100vh-2rem)] w-[min(20rem,calc(100vw-2.5rem))] rounded-[2.5rem] border-4 border-white bg-white p-5 shadow-2xl flex flex-col">
            
            <!-- Header Mobile Sidebar (Fixed/Tidak ikut scroll) -->
            <div class="shrink-0 mb-6 flex items-center gap-3 border-b-4 border-dashed border-[#f4f9ff] pb-6">
                <img src="{{ asset('images/wiboost-logo.png') }}?v=20260426-full" alt="Logo Wiboost Store" class="h-12 w-12 shrink-0 object-contain drop-shadow-sm">
                <div class="min-w-0 flex-1">
                    <p class="text-xl font-black leading-tight text-[#2b3a67] break-words">Wiboost <span class="text-[#5a76c8]">Store</span></p>
                </div>
            </div>
            
            <!-- Nav Menu Mobile Sidebar (Scrollable & Hide Scrollbar) -->
            <nav class="flex-1 space-y-2 overflow-y-auto overscroll-contain hide-scroll pr-1">
                @foreach($adminNav as $item)
                    <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-full px-5 py-3 text-sm font-black transition-all {{ request()->routeIs($item['pattern']) ? 'bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30 border-2 border-white' : 'bg-[#f4f9ff] text-[#2b3a67] border-2 border-transparent hover:border-white' }}">
                        <span class="text-lg">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            
            <!-- Footer Mobile Sidebar (Fixed/Tidak ikut scroll) -->
            <div class="shrink-0 mt-6 pt-6 border-t-4 border-dashed border-[#f4f9ff]">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center rounded-full border-4 border-white bg-[#ffe5e5] px-6 py-3.5 text-sm font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 active:scale-95 transition-transform gap-2">
                        Keluar Akun 🚪
                    </button>
                </form>
            </div>
        </aside>
    </div>

    <script>
        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || form.dataset.noSubmitLock === 'true') {
                return;
            }

            if (event.defaultPrevented) {
                return;
            }

            const spoofedMethod = form.querySelector('input[name="_method"]')?.value;
            const method = (spoofedMethod || form.method || 'get').toLowerCase();

            if (method === 'get') {
                return;
            }

            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            form.dataset.submitting = 'true';

            const button = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
            if (!button || button.dataset.keepEnabled === 'true') {
                return;
            }

            button.disabled = true;
            button.classList.add('cursor-wait', 'opacity-75');

            if (button.tagName === 'BUTTON' && !button.dataset.originalText) {
                button.dataset.originalText = button.textContent.trim();
                window.setTimeout(() => {
                    if (form.dataset.submitting === 'true' && document.body.contains(button)) {
                        button.textContent = 'Menyimpan...';
                    }
                }, 700);
            }
        });
    </script>
    @include('partials.page-loading')
</body>
</html>
