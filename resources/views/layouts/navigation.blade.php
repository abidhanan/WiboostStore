<nav x-data="{ open: false }" class="bg-white/90 backdrop-blur-md border-b-4 border-white shadow-xl shadow-[#bde0fe]/30 sm:mx-6 sm:mt-4 sm:rounded-[2.5rem] relative z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="shrink-0 flex items-center gap-3 group hover:scale-105 transition-transform">
                    <img src="{{ asset('images/wiboost-logo.png') }}?v=20260426" class="block h-12 w-12 drop-shadow-sm" alt="Logo" />
                    <span class="text-2xl font-black tracking-tight text-[#2b3a67] hidden sm:block">Wiboost <span class="text-[#5a76c8]">Store</span></span>
                </a>

                <div class="hidden space-x-2 sm:ml-8 sm:flex">
                    <a href="{{ route('dashboard') }}" class="rounded-full px-5 py-2.5 text-sm font-black transition-all hover:scale-105 {{ request()->routeIs('dashboard') ? 'border-2 border-white bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/30' : 'text-[#8faaf3] hover:bg-[#f0f5ff] hover:text-[#5a76c8]' }}">
                        {{ __('Dashboard') }}
                    </a>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-5 py-2.5 border-4 border-white rounded-[1.5rem] text-sm font-black text-[#2b3a67] bg-[#f4f9ff] hover:bg-[#e0fbfc] hover:text-[#4bc6b9] shadow-sm transition-all focus:outline-none active:scale-95">
                            <div>{{ Auth::user()->name }}</div>
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="p-2 border-4 border-white bg-white rounded-[1.5rem] shadow-xl shadow-[#bde0fe]/40">
                            <x-dropdown-link :href="route('profile.edit')" class="rounded-xl font-black text-[#5a76c8] hover:bg-[#f4f9ff]">
                                ⚙️ {{ __('Profil') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();"
                                        class="rounded-xl font-black text-[#ff6b6b] hover:bg-[#ffe5e5] mt-1">
                                    🚪 {{ __('Keluar') }}
                                </x-dropdown-link>
                            </form>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-3 rounded-[1.2rem] border-2 border-white bg-[#f4f9ff] text-[#5a76c8] hover:text-white hover:bg-[#5a76c8] focus:outline-none transition-all shadow-sm active:scale-95">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" stroke-width="2.5" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t-4 border-dashed border-[#f4f9ff] bg-white rounded-b-[2rem]">
        <div class="pt-4 pb-3 space-y-2 px-4">
            <a href="{{ route('dashboard') }}" class="block w-full px-5 py-3 rounded-2xl font-black {{ request()->routeIs('dashboard') ? 'bg-[#5a76c8] text-white shadow-md border-2 border-white' : 'text-[#8faaf3] hover:bg-[#f4f9ff]' }}">
                {{ __('Dashboard') }}
            </a>
        </div>

        <div class="pt-4 pb-4 border-t-4 border-dashed border-[#f4f9ff]">
            <div class="px-6 mb-4">
                <div class="font-black text-lg text-[#2b3a67]">{{ Auth::user()->name }}</div>
                <div class="font-bold text-sm text-[#8faaf3]">{{ Auth::user()->email }}</div>
            </div>

            <div class="space-y-2 px-4">
                <a href="{{ route('profile.edit') }}" class="block w-full px-5 py-3 rounded-2xl font-black text-[#5a76c8] hover:bg-[#f4f9ff] border-2 border-transparent">
                    ⚙️ {{ __('Profil') }}
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-5 py-3 rounded-2xl font-black text-[#ff6b6b] hover:bg-[#ffe5e5] border-2 border-transparent mt-1">
                        🚪 {{ __('Keluar') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>