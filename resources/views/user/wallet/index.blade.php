@extends('layouts.user')

@section('title', 'Wiboost Wallet')

@section('content')
<style>
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    .wallet-scroll-panel {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior-y: auto;
        -webkit-overflow-scrolling: touch;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .wallet-scroll-panel::-webkit-scrollbar { display: none; }
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="wiboost-font pb-12 max-w-7xl mx-auto relative z-10">
    <div class="mb-8 text-center md:text-left pl-2">
        <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
            Keuangan
        </div>
        <h1 class="flex items-center justify-center gap-2 text-3xl sm:text-4xl font-black text-[#2b3a67] tracking-tight drop-shadow-sm md:justify-start">
            <span class="text-[#5a76c8]">@include('partials.wiboost-icon', ['name' => 'wallet', 'class' => 'h-8 w-8'])</span>
            <span>Dompet Anda</span>
        </h1>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-lg shadow-emerald-100/50">
            <span class="drop-shadow-sm">@include('partials.wiboost-icon', ['name' => 'success', 'class' => 'h-7 w-7'])</span> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-lg shadow-[#ff6b6b]/20">
            <span class="drop-shadow-sm">@include('partials.wiboost-icon', ['name' => 'alert', 'class' => 'h-7 w-7'])</span> {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-3">
        <div class="lg:col-span-1 space-y-8" data-wallet-left-column>
            
            <div class="bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] rounded-[2.5rem] p-8 shadow-xl shadow-[#5a76c8]/30 text-white relative overflow-hidden border-4 border-white hover:scale-[1.02] transition-transform duration-300">
                <div class="relative z-10">
                    <p class="text-[#e0fbfc] text-xs font-black uppercase tracking-widest mb-2">Total Saldo Tersedia</p>
                    <h2 class="text-4xl font-black tracking-tight mb-5 drop-shadow-md">Rp {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }}</h2>
                    <div class="inline-flex items-center gap-2 text-[10px] font-black bg-white/20 px-4 py-2 rounded-full backdrop-blur-sm border-2 border-white/40 shadow-inner uppercase tracking-wider">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Aman & Terenkripsi
                    </div>
                </div>
            </div>


            <div class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] p-8 border-4 border-white shadow-xl shadow-[#bde0fe]/30" data-wallet-topup-box>
                <div class="flex items-center gap-2 mb-6 ml-2">
                    <span class="text-[#5a76c8]">@include('partials.wiboost-icon', ['name' => 'zap', 'class' => 'h-7 w-7'])</span>
                    <h2 class="font-black text-[#2b3a67] text-xl">Isi Saldo Cepat</h2>
                </div>
                <form action="{{ route('user.wallet.topup') }}" method="POST" data-single-submit>
                    @csrf
                    <div class="mb-6">
                        <div class="grid grid-cols-2 gap-3 mb-5">
                            <button type="button" onclick="setNominal(10000)" class="py-3 bg-[#f4f9ff] border-2 border-white hover:border-[#bde0fe] shadow-sm hover:bg-[#e0fbfc] rounded-[1.2rem] text-sm font-black text-[#5a76c8] transition-all active:scale-95">10K</button>
                            <button type="button" onclick="setNominal(20000)" class="py-3 bg-[#f4f9ff] border-2 border-white hover:border-[#bde0fe] shadow-sm hover:bg-[#e0fbfc] rounded-[1.2rem] text-sm font-black text-[#5a76c8] transition-all active:scale-95">20K</button>
                            <button type="button" onclick="setNominal(50000)" class="py-3 bg-[#f4f9ff] border-2 border-white hover:border-[#bde0fe] shadow-sm hover:bg-[#e0fbfc] rounded-[1.2rem] text-sm font-black text-[#5a76c8] transition-all active:scale-95">50K</button>
                            <button type="button" onclick="setNominal(100000)" class="py-3 bg-[#f4f9ff] border-2 border-white hover:border-[#bde0fe] shadow-sm hover:bg-[#e0fbfc] rounded-[1.2rem] text-sm font-black text-[#5a76c8] transition-all active:scale-95">100K</button>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-5 font-black text-[#5a76c8]">Rp</span>
                            <input type="number" name="amount" id="amount_input" min="10000" required
                                   class="w-full pl-12 pr-5 py-4 bg-[#f4f9ff] rounded-[1.5rem] border-4 border-white shadow-inner focus:border-[#bde0fe] outline-none font-black text-lg text-[#2b3a67] transition placeholder-[#a3bbfb]" 
                                   placeholder="ketik nominal tanpa titik">
                        </div>
                        <p class="text-[10px] font-black text-amber-500 mt-3 ml-3">*Min. isi Rp 10.000</p>
                    </div>
                    <button type="submit" data-submit-label="Menyiapkan QRIS..." class="w-full bg-[#5a76c8] text-white py-4 rounded-full font-black hover:bg-[#4760a9] transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white flex justify-center items-center gap-2">
                        Bayar
                    </button>
                </form>
            </div>
        </div>

        <div class="min-h-0 lg:col-span-2">
            <div class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white h-full flex flex-col overflow-hidden relative" data-wallet-mutation-box>
                
                <div class="px-8 py-6 border-b-4 border-dashed border-[#f4f9ff] flex flex-col md:flex-row md:justify-between items-start md:items-center bg-[#ffffff] gap-3 relative z-10" data-wallet-mutation-header>
                    <h2 class="flex items-center gap-2 font-black text-xl text-[#2b3a67]">
                        Mutasi Saldo
                        <span class="text-[#4bc6b9]">@include('partials.wiboost-icon', ['name' => 'cash', 'class' => 'h-6 w-6'])</span>
                    </h2>
                </div>
                
                @php
                    $walletLogs = App\Models\WalletHistory::where('user_id', Auth::id())->latest()->take(20)->get();
                @endphp

                <div class="wallet-scroll-panel space-y-4 bg-[#f8faff]/50 p-6 relative z-10 {{ $walletLogs->isEmpty() ? 'flex' : '' }}" data-wallet-mutation-scroll>

                    @forelse($walletLogs as $log)
                        @php
                            $displayDesc = $log->description;
                            
                            if ($log->type === 'topup') {
                                $paidAmount = null;

                                if (class_exists('\App\Models\Deposit')) {
                                    $deposit = \App\Models\Deposit::where('invoice_number', $log->invoice_number)->first();
                                    if ($deposit && $deposit->payment_method) {
                                        $method = $deposit->display_payment_method;
                                        if (strtolower($method) == 'gopay') $method = 'GoPay';
                                        if (strtolower($method) == 'shopeepay') $method = 'ShopeePay';
                                        
                                        $displayDesc = "Top Up Saldo via " . $method;
                                        $paidAmount = $deposit->paid_amount ?? null;
                                    }
                                }
                            }

                            $displayDesc = preg_replace('/\b(QRIS\s*REAL\s*TIME|QRISREALTIME|TRIPAY)\b/i', 'QRIS', $displayDesc) ?: $displayDesc;

                            $iconClass = '';
                            $iconChar = '';
                            if($log->type == 'refund') {
                                $iconClass = 'bg-[#e6fff7] text-emerald-500'; $iconChar = '↺';
                            } elseif ($log->type == 'purchase') {
                                $iconClass = 'bg-[#ffe5e5] text-[#ff6b6b]'; $iconChar = '−';
                            } else {
                                $iconClass = 'bg-[#f0f5ff] text-[#5a76c8]'; $iconChar = '+';
                            }
                        @endphp

                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between p-5 bg-white border-2 border-white rounded-[1.5rem] hover:border-[#bde0fe] hover:-translate-y-1 transition-all duration-300 group shadow-md shadow-[#bde0fe]/10">
                            
                            <div class="flex min-w-0 items-center gap-3 mb-3 md:mb-0">
                                <div class="w-11 h-11 md:w-12 md:h-12 shrink-0 rounded-[1rem] flex items-center justify-center text-lg md:text-xl font-black border-2 border-white shadow-inner group-hover:scale-110 transition-transform {{ $iconClass }}">
                                    {{ $iconChar }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-black text-[#2b3a67] text-sm md:text-base group-hover:text-[#5a76c8] transition-colors leading-tight break-words md:line-clamp-2">
                                        {{ $displayDesc }}
                                    </h3>
                                    <p class="mt-1.5 flex min-w-0 flex-nowrap items-center gap-1 whitespace-nowrap text-[8px] font-black uppercase tracking-[0.05em] text-[#8faaf3] sm:text-[9px] md:text-[10px] md:tracking-widest">
                                        <span class="shrink-0 rounded border border-white bg-[#f4f9ff] px-1.5 py-0.5 sm:px-2">{{ $log->invoice_number ?? '-' }}</span>
                                        <span class="shrink-0">&bull;</span>
                                        <span class="shrink-0">{{ $log->created_at->format('d M, H:i') }}</span>
                                    </p>
                                    @if(isset($paidAmount) && $paidAmount && (float) $paidAmount !== (float) $log->amount)
                                        <p class="text-[10px] font-black text-[#4bc6b9] uppercase tracking-widest mt-1">
                                            Dibayar via QRIS: Rp {{ number_format($paidAmount, 0, ',', '.') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex w-full shrink-0 flex-row items-center justify-between pl-14 text-left md:w-auto md:min-w-[9rem] md:flex-col md:items-end md:justify-end md:pl-0 md:text-right">
                                <p class="mb-1 whitespace-nowrap text-lg font-black leading-none {{ $log->type == 'purchase' ? 'text-[#ff6b6b]' : 'text-emerald-500' }} drop-shadow-sm xl:text-xl">
                                    {{ $log->type == 'purchase' ? '-' : '+' }} Rp {{ number_format($log->amount, 0, ',', '.') }}
                                </p>
                                <span class="text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-full bg-[#f4f9ff] text-[#8faaf3] border-2 border-white shadow-sm inline-block">
                                    {{ $log->type_label }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="flex min-h-full w-full flex-1 items-center justify-center rounded-[2rem] border-4 border-dashed border-[#bde0fe] bg-white/50 px-6 py-10 text-center">
                            <div>
                            <div class="mb-4 flex justify-center text-[#5a76c8] animate-float">
                                @include('partials.wiboost-icon', ['name' => 'wallet', 'class' => 'h-16 w-16'])
                            </div>
                            <h3 class="text-xl font-black text-[#5a76c8] mb-1">Dompet Masih Kosong</h3>
                            <p class="text-[#8faaf3] font-bold text-sm">Belum ada mutasi saldo masuk ataupun keluar.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function setNominal(amount) {
        document.getElementById('amount_input').value = amount;
    }

    function syncWalletMutationHeight() {
        const leftColumn = document.querySelector('[data-wallet-left-column]');
        const topupBox = document.querySelector('[data-wallet-topup-box]');
        const mutationBox = document.querySelector('[data-wallet-mutation-box]');
        const mutationHeader = document.querySelector('[data-wallet-mutation-header]');
        const mutationScroll = document.querySelector('[data-wallet-mutation-scroll]');

        if (!leftColumn || !mutationBox) {
            return;
        }

        const sourceBox = window.innerWidth < 1024 ? topupBox : leftColumn;
        const height = Math.round(sourceBox?.getBoundingClientRect().height || 0);
        const fixedHeight = height > 0 ? `${height}px` : '';
        const headerHeight = Math.round(mutationHeader?.getBoundingClientRect().height || 94);

        if (window.innerWidth < 1024) {
            mutationBox.style.height = fixedHeight;
            mutationBox.style.maxHeight = fixedHeight;
            if (mutationScroll) {
                mutationScroll.style.maxHeight = height > 0 ? `${Math.max(height - headerHeight, 180)}px` : '';
            }
            return;
        }

        mutationBox.style.height = fixedHeight;
        mutationBox.style.maxHeight = fixedHeight;
        if (mutationScroll) {
            mutationScroll.style.maxHeight = height > 0 ? `${Math.max(height - headerHeight, 240)}px` : '';
        }
    }

    function prepareWalletScrollChaining(scrollArea) {
        if (!scrollArea || scrollArea.dataset.scrollChainingReady === '1') {
            return;
        }

        scrollArea.dataset.scrollChainingReady = '1';
        scrollArea.addEventListener('wheel', (event) => {
            const atTop = scrollArea.scrollTop <= 0;
            const atBottom = Math.ceil(scrollArea.scrollTop + scrollArea.clientHeight) >= scrollArea.scrollHeight;

            if ((event.deltaY < 0 && atTop) || (event.deltaY > 0 && atBottom)) {
                scrollArea.style.overflowY = 'hidden';
                requestAnimationFrame(() => {
                    scrollArea.style.overflowY = 'auto';
                });
            }
        }, { passive: true });
    }

    prepareWalletScrollChaining(document.querySelector('[data-wallet-mutation-scroll]'));

    const walletHeightObserver = window.ResizeObserver && document.querySelector('[data-wallet-left-column]')
        ? new ResizeObserver(syncWalletMutationHeight)
        : null;

    if (walletHeightObserver) {
        walletHeightObserver.observe(document.querySelector('[data-wallet-left-column]'));
        const walletTopupBox = document.querySelector('[data-wallet-topup-box]');
        if (walletTopupBox) {
            walletHeightObserver.observe(walletTopupBox);
        }
    }

    window.addEventListener('load', syncWalletMutationHeight, { once: true });
    window.addEventListener('resize', syncWalletMutationHeight, { passive: true });
    requestAnimationFrame(syncWalletMutationHeight);
    setTimeout(syncWalletMutationHeight, 250);

    document.querySelectorAll('form[data-single-submit]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.submitted === '1') {
                event.preventDefault();
                return;
            }

            form.dataset.submitted = '1';
            const button = form.querySelector('[type="submit"]');
            if (button) {
                button.disabled = true;
                button.classList.add('cursor-not-allowed', 'opacity-70');
                button.innerHTML = button.dataset.submitLabel || 'Memproses...';
            }
        });
    });
</script>
@endsection
