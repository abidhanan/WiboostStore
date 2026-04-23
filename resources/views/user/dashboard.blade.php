@extends('layouts.user')

@section('title', 'Dashboard Saya')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    .popup-enter { transform: translateY(0); opacity: 1; pointer-events: auto; }
    .popup-leave { transform: translateY(150%); opacity: 0; pointer-events: none; }
    .modal-active { opacity: 1 !important; pointer-events: auto !important; }
    .modal-content-active { transform: scale(1) !important; }
</style>

<div class="wiboost-font relative">
    <div class="relative overflow-hidden bg-wiboost-sky rounded-[2.5rem] p-8 md:p-10 mb-8 shadow-xl shadow-[#bde0fe]/50 border-4 border-white flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div class="relative z-10">
            <p class="text-[#5a76c8] font-black tracking-wide mb-1 uppercase text-sm">Dashboard Pelanggan</p>
            <h1 class="text-3xl md:text-4xl font-black text-[#2b3a67] mb-2">Halo, {{ Auth::user()->name }}!</h1>
            <p class="text-[#4a5f96] max-w-md font-bold">Siap buat sosmed dan game kamu makin aktif hari ini? Yuk, lanjut belanja.</p>
        </div>

        <div class="relative z-10 bg-white/80 backdrop-blur-md px-8 py-6 rounded-[2rem] border-2 border-white w-full md:w-auto text-center md:text-right shadow-sm hover:border-[#bde0fe] transition-colors">
            <p class="text-[#8faaf3] text-xs font-black uppercase tracking-widest mb-1">Saldo Wiboost</p>
            <p class="text-3xl md:text-4xl font-black text-[#5a76c8] tracking-tight mb-4">Rp {{ number_format(Auth::user()->balance, 0, ',', '.') }}</p>
            <a href="{{ route('user.wallet.index') }}" class="inline-flex items-center justify-center gap-2 bg-[#5a76c8] text-white px-6 py-2.5 rounded-full text-sm font-extrabold hover:bg-[#4760a9] hover:-translate-y-1 transition-all shadow-lg shadow-[#5a76c8]/30 border-2 border-white w-full md:w-auto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Top Up Saldo
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-10">
        <div class="bg-white p-6 rounded-[2rem] border-4 border-white shadow-lg shadow-[#bde0fe]/30 flex items-center gap-5 hover:-translate-y-1 transition">
            <div class="w-14 h-14 bg-[#f0f5ff] rounded-2xl flex items-center justify-center text-[#5a76c8] border-2 border-white shadow-inner shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <div>
                <p class="text-xs font-black text-[#8faaf3] uppercase tracking-wider">Pesanan Bulan Ini</p>
                <p class="text-2xl font-black text-[#2b3a67]">{{ $totalThisMonth ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-[2rem] border-4 border-white shadow-lg shadow-[#bde0fe]/30 flex items-center gap-5 hover:-translate-y-1 transition">
            <div class="w-14 h-14 bg-[#e6fff7] rounded-2xl flex items-center justify-center text-emerald-500 border-2 border-white shadow-inner shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0-2.08-.402-2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="truncate">
                <p class="text-xs font-black text-emerald-400 uppercase tracking-wider">Pengeluaran Bulan Ini</p>
                <p class="text-xl font-black text-[#2b3a67] truncate">Rp {{ number_format($totalSpent ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="mb-10 relative rounded-[2.5rem] overflow-hidden shadow-lg border-4 border-white group">
        <div id="promo-slider" class="flex overflow-x-auto snap-x snap-mandatory hide-scroll scroll-smooth">
            @forelse($promos as $promo)
                <div class="snap-center shrink-0 w-full p-8 md:p-12 text-white relative overflow-hidden flex items-center h-[250px] md:h-[300px] group/slide
                    @if(!$promo->image)
                        @if($promo->theme == 'teal') bg-gradient-to-r from-[#4bc6b9] to-[#3ba398]
                        @elseif($promo->theme == 'orange') bg-gradient-to-r from-[#fbbf24] to-[#d97706]
                        @elseif($promo->theme == 'rose') bg-gradient-to-r from-[#fb7185] to-[#e11d48]
                        @else bg-gradient-to-r from-[#8faaf3] to-[#5a76c8] @endif
                    @else bg-[#2b3a67] @endif">

                    @if(!empty($promo->link))
                        <a href="{{ $promo->link }}" target="_blank" class="absolute inset-0 z-30 cursor-pointer"></a>
                        <div class="absolute top-6 right-6 z-20 bg-white/20 backdrop-blur-md p-3 rounded-full opacity-0 group-hover/slide:opacity-100 transition-opacity duration-300 pointer-events-none">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </div>
                    @endif

                    @if($promo->image)
                        <img src="{{ Storage::url($promo->image) }}" class="absolute inset-0 w-full h-full object-cover z-0 opacity-70 group-hover/slide:scale-105 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-r from-black/80 to-transparent z-10"></div>
                    @endif

                    <div class="relative z-20 pointer-events-none">
                        <span class="bg-[#e0fbfc] text-[#5a76c8] text-xs font-black px-3 py-1 rounded-full mb-3 inline-block shadow-sm uppercase tracking-widest">{{ $promo->badge_text }}</span>
                        <h2 class="text-3xl md:text-5xl font-black mb-2 drop-shadow-md">{{ $promo->title }}</h2>
                        <p class="font-bold text-white/90 text-sm md:text-lg max-w-lg drop-shadow-md">{{ $promo->description }}</p>
                    </div>

                    @if(!$promo->image)
                        <div class="text-7xl md:text-8xl opacity-50 absolute right-10 transform {{ $loop->iteration % 2 == 0 ? '-rotate-12' : 'rotate-12' }} pointer-events-none group-hover/slide:scale-110 transition-transform duration-500">
                            {{ $promo->emoji }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="snap-center shrink-0 w-full bg-gradient-to-r from-[#8faaf3] to-[#5a76c8] p-8 md:p-12 text-white relative overflow-hidden flex items-center justify-center h-[250px] md:h-[300px]">
                    <div class="text-center relative z-20">
                        <span class="text-6xl mb-4 block">PROMO</span>
                        <h2 class="text-3xl md:text-4xl font-black mb-2 drop-shadow-md">Belum Ada Promo</h2>
                        <p class="font-bold text-white/90 text-sm md:text-lg drop-shadow-md">Nantikan penawaran menarik dari Wiboost.</p>
                    </div>
                </div>
            @endforelse
        </div>

        @if(count($promos) > 1)
            <button onclick="slidePromo(-1)" class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/50 hover:bg-white backdrop-blur-md rounded-full flex items-center justify-center text-[#5a76c8] transition shadow-sm opacity-0 group-hover:opacity-100 z-40">&lt;</button>
            <button onclick="slidePromo(1)" class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/50 hover:bg-white backdrop-blur-md rounded-full flex items-center justify-center text-[#5a76c8] transition shadow-sm opacity-0 group-hover:opacity-100 z-40">&gt;</button>
        @endif
    </div>

    <div class="flex items-center gap-3 mb-6 pl-2">
        <span class="text-2xl">Katalog</span>
        <h3 class="text-2xl font-black text-[#2b3a67]">Pilih Kategori</h3>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6 mb-14">
        @forelse($categories as $category)
            <a href="{{ route('user.order.category', $category->slug) }}" class="group bg-white p-6 md:p-8 rounded-[2.5rem] border-4 border-white hover:border-[#bde0fe] shadow-lg shadow-[#bde0fe]/20 hover:-translate-y-2 transition-all text-center flex flex-col items-center justify-center">
                <div class="text-5xl md:text-6xl mb-4 group-hover:scale-110 group-hover:rotate-6 transition-transform drop-shadow-sm">
                    {{ $category->emote ?? 'CAT' }}
                </div>
                <span class="text-base font-black text-[#2b3a67]">{{ $category->name }}</span>
            </a>
        @empty
            <div class="col-span-full text-center py-16 bg-white rounded-[2.5rem] border-4 border-dashed border-[#bde0fe]">
                <p class="text-[#8faaf3] font-black text-xl">Belum ada kategori layanan yang aktif.</p>
            </div>
        @endforelse
    </div>

    <div class="flex items-center justify-between mb-6 pl-2">
        <div class="flex items-center gap-3">
            <span class="text-2xl">Bantuan</span>
            <h3 class="text-2xl font-black text-[#2b3a67]">Pusat Tutorial</h3>
        </div>
    </div>

    <div class="flex gap-3 overflow-x-auto hide-scroll mb-6 pl-2 pb-2">
        <button onclick="filterTutorial('all')" id="btn-tut-all" class="tut-filter-btn px-5 py-2 rounded-full font-black text-sm transition-all bg-[#5a76c8] text-white shadow-md border-2 border-white shrink-0">Semua Kategori</button>
        @foreach($tutorialCategories ?? [] as $cat)
            <button onclick="filterTutorial('{{ $cat }}')" id="btn-tut-{{ Str::slug($cat) }}" class="tut-filter-btn px-5 py-2 rounded-full font-black text-sm transition-all bg-white text-[#5a76c8] hover:bg-[#f0f5ff] shadow-sm border-2 border-transparent shrink-0">{{ $cat }}</button>
        @endforeach
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-12" id="tutorial-grid">
        @forelse($tutorials ?? [] as $tutorial)
            <div class="tutorial-card bg-white p-6 md:p-8 rounded-[2.5rem] border-4 border-white hover:border-[#bde0fe] shadow-lg shadow-[#bde0fe]/20 hover:-translate-y-2 transition-all flex flex-col h-full group" data-category="{{ $tutorial->category }}">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-14 h-14 bg-[#f4f9ff] rounded-[1.2rem] flex items-center justify-center text-3xl border-2 border-white shadow-inner overflow-hidden shrink-0 group-hover:scale-110 transition-transform">
                        @if($tutorial->image)
                            <img src="{{ Storage::url($tutorial->image) }}" class="w-full h-full object-cover">
                        @else
                            {{ $tutorial->icon ?? 'DOC' }}
                        @endif
                    </div>
                    <span class="bg-[#f0f5ff] text-[#5a76c8] text-[9px] font-black px-2 py-1 rounded-lg uppercase tracking-widest text-right">{{ $tutorial->category }}</span>
                </div>

                <h4 class="font-black text-lg text-[#2b3a67] mb-2 leading-tight">{{ $tutorial->title }}</h4>
                <p class="text-xs font-bold text-[#8faaf3] mb-6 flex-1 line-clamp-3">{{ $tutorial->description }}</p>

                <button onclick="openTutorialModal('tut-{{ $tutorial->id }}')" class="w-full bg-[#f4f9ff] hover:bg-[#5a76c8] text-[#5a76c8] hover:text-white font-black text-sm py-3.5 rounded-[1.2rem] transition-colors border-2 border-white shadow-sm flex items-center justify-center gap-2">
                    Lihat Panduan
                </button>
            </div>
        @empty
            <div class="col-span-full text-center py-16 bg-white rounded-[2.5rem] border-4 border-dashed border-[#bde0fe]">
                <div class="text-6xl mb-3 opacity-50">DOC</div>
                <p class="text-[#8faaf3] font-black text-lg">Belum ada tutorial tersedia.</p>
            </div>
        @endforelse
    </div>
</div>

<div id="fomo-toast" class="fixed bottom-6 left-4 md:left-8 z-50 bg-white border-4 border-white shadow-2xl shadow-[#bde0fe]/50 rounded-[2rem] p-4 pr-8 flex items-center gap-4 transition-all duration-700 popup-leave max-w-[320px]">
    <div class="w-12 h-12 bg-[#e6fff7] rounded-[1rem] flex items-center justify-center text-2xl border-2 border-white shadow-inner shrink-0">BUY</div>
    <div>
        <p class="text-[10px] text-[#8faaf3] font-black uppercase tracking-widest" id="fomo-name">Member Wiboost</p>
        <p class="text-sm text-[#2b3a67] font-black leading-tight mt-0.5">Membeli <span class="text-[#5a76c8]" id="fomo-product">Produk</span></p>
        <p class="text-[10px] text-amber-500 font-bold mt-1" id="fomo-time">Baru saja</p>
    </div>
</div>

@foreach($tutorials ?? [] as $tutorial)
    <div id="tut-{{ $tutorial->id }}" class="fixed inset-0 flex items-center justify-center bg-[#2b3a67]/80 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300 p-4" style="z-index: 999999;">
        <div class="absolute inset-0 w-full h-full" onclick="closeTutorialModal('tut-{{ $tutorial->id }}')"></div>

        <div class="relative bg-white w-full max-w-2xl rounded-[2.5rem] p-6 md:p-8 shadow-2xl transform scale-95 transition-transform duration-300 flex flex-col overflow-hidden max-h-[90vh]">
            <div class="flex justify-between items-center mb-6 pb-4 border-b-2 border-dashed border-[#f0f5ff]">
                <h3 class="text-xl font-black text-[#2b3a67] flex items-center gap-3">
                    @if($tutorial->image)
                        <img src="{{ Storage::url($tutorial->image) }}" class="w-8 h-8 object-cover rounded-md border border-[#f0f5ff]">
                    @else
                        <span>{{ $tutorial->icon ?? 'DOC' }}</span>
                    @endif
                    {{ $tutorial->title }}
                </h3>
                <button onclick="closeTutorialModal('tut-{{ $tutorial->id }}')" class="w-10 h-10 bg-[#ffe5e5] text-[#ff6b6b] hover:bg-[#ff6b6b] hover:text-white rounded-xl flex items-center justify-center transition-colors font-black border-2 border-white shrink-0">X</button>
            </div>

            <div class="flex-1 overflow-y-auto hide-scroll pr-2 text-left">
                @if($tutorial->youtube_url)
                    <a href="{{ $tutorial->youtube_url }}" target="_blank" class="w-full bg-[#ffe5e5] hover:bg-[#ff6b6b] text-[#ff6b6b] hover:text-white p-4 rounded-2xl flex items-center justify-center gap-2 mb-6 font-black transition-colors border-2 border-white shadow-sm">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        Tonton Video di YouTube
                    </a>
                @endif

                @if($tutorial->content)
                    <div class="text-[#2b3a67] font-bold text-sm md:text-base leading-relaxed whitespace-pre-wrap text-left block w-full">
{{ $tutorial->content }}
                    </div>
                @else
                    @if(!$tutorial->youtube_url)
                        <p class="text-center text-gray-400 italic">Isi panduan belum tersedia.</p>
                    @endif
                @endif
            </div>

            <div class="mt-6 pt-4 border-t-2 border-dashed border-[#f0f5ff] shrink-0">
                <button onclick="closeTutorialModal('tut-{{ $tutorial->id }}')" class="w-full bg-[#f4f9ff] hover:bg-[#e0ebff] text-[#5a76c8] py-4 rounded-[1.5rem] font-black transition-colors border-2 border-white shadow-sm text-lg">Tutup Panduan</button>
            </div>
        </div>
    </div>
@endforeach

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[id^="tut-"]').forEach(modal => document.body.appendChild(modal));

        const fomo = document.getElementById('fomo-toast');
        if (fomo) {
            document.body.appendChild(fomo);
        }

        const slider = document.getElementById('promo-slider');
        if (slider && slider.children.length > 1) {
            let autoSlide = setInterval(() => slidePromo(1), 5000);

            window.slidePromo = function (direction) {
                const scrollAmount = slider.clientWidth;
                slider.scrollBy({ left: scrollAmount * direction, behavior: 'smooth' });

                if (direction === 1 && slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 10) {
                    slider.scrollTo({ left: 0, behavior: 'smooth' });
                } else if (direction === -1 && slider.scrollLeft <= 10) {
                    slider.scrollTo({ left: slider.scrollWidth, behavior: 'smooth' });
                }

                clearInterval(autoSlide);
                autoSlide = setInterval(() => slidePromo(1), 5000);
            };
        }
    });

    function filterTutorial(category) {
        document.querySelectorAll('.tut-filter-btn').forEach(btn => {
            btn.classList.remove('bg-[#5a76c8]', 'text-white', 'shadow-md');
            btn.classList.add('bg-white', 'text-[#5a76c8]', 'shadow-sm');
        });

        const slug = category.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
        const activeBtn = category === 'all'
            ? document.getElementById('btn-tut-all')
            : document.getElementById('btn-tut-' + slug);

        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'text-[#5a76c8]', 'shadow-sm');
            activeBtn.classList.add('bg-[#5a76c8]', 'text-white', 'shadow-md');
        }

        document.querySelectorAll('.tutorial-card').forEach(card => {
            card.style.display = category === 'all' || card.getAttribute('data-category') === category ? 'flex' : 'none';
        });
    }

    const fomoPurchases = @json($recentFomoPurchases ?? []);
    const fomoToast = document.getElementById('fomo-toast');
    const fomoName = document.getElementById('fomo-name');
    const fomoProduct = document.getElementById('fomo-product');

    function showFomo() {
        if (!fomoName || !fomoProduct || !fomoToast || fomoPurchases.length === 0) {
            return;
        }

        const purchase = fomoPurchases[Math.floor(Math.random() * fomoPurchases.length)];
        fomoName.innerText = purchase.name || 'Member Wiboost';
        fomoProduct.innerText = purchase.product || 'Produk Wiboost';

        fomoToast.classList.remove('popup-leave');
        fomoToast.classList.add('popup-enter');

        setTimeout(() => {
            fomoToast.classList.remove('popup-enter');
            fomoToast.classList.add('popup-leave');
        }, 4000);
    }

    if (fomoPurchases.length > 0) {
        setTimeout(() => {
            showFomo();
            setInterval(showFomo, Math.floor(Math.random() * (20000 - 10000 + 1) + 10000));
        }, 3000);
    }

    function openTutorialModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('modal-active');
            modal.querySelector('.transform').classList.add('modal-content-active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeTutorialModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('modal-active');
            modal.querySelector('.transform').classList.remove('modal-content-active');
            document.body.style.overflow = '';
        }
    }
</script>
@endsection
