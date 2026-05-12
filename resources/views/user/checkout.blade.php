<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran #{{ $transaction->invoice_number }} - Wiboost</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.vite-fallback')
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        html,
        body {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            overflow: hidden;
            overscroll-behavior: none;
        }
        body {
            display: grid;
            place-items: center;
            padding: 12px;
            box-sizing: border-box;
            font-family: 'Nunito', sans-serif; 
            background-color: #f4f9ff; 
            touch-action: none;
        }
        button,
        a {
            touch-action: manipulation;
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
        .checkout-shell {
            width: min(100%, 28rem);
            max-height: calc(100vh - 24px);
            transform-origin: center;
        }
        @supports (height: 100svh) {
            html,
            body {
                height: 100svh;
            }
            .checkout-shell {
                max-height: calc(100svh - 24px);
            }
        }
        @media (max-height: 560px) { .checkout-shell { transform: scale(0.92); } }
        @media (max-height: 480px) { .checkout-shell { transform: scale(0.84); } }
        @media (min-width: 1024px) {
            html,
            body {
                position: fixed;
                inset: 0;
                min-height: 100%;
                height: 100%;
                overflow: hidden;
                overscroll-behavior: none;
            }

            body {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px 36px;
                touch-action: none;
            }

            .checkout-shell {
                display: grid;
                grid-template-columns: minmax(0, 0.9fr) minmax(0, 1fr);
                align-items: stretch;
                gap: 22px;
                width: min(100%, 64rem);
                max-width: 64rem !important;
                max-height: calc(100vh - 40px);
                transform: none;
            }

            .checkout-hero {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100%;
                margin-bottom: 0 !important;
                padding: 2rem !important;
            }

            .checkout-hero:hover {
                transform: none !important;
            }

            .checkout-panel {
                display: flex;
                flex-direction: column;
                justify-content: center;
                height: 100%;
                padding: 1.75rem !important;
            }

            .checkout-safe-note {
                position: absolute;
                left: 2rem;
                bottom: 1.25rem;
                z-index: 20;
                width: calc(47% - 2rem);
                margin: 0 !important;
                color: rgba(255, 255, 255, 0.9) !important;
                text-shadow: 0 2px 8px rgba(43, 58, 103, 0.25);
            }
        }
        @media (min-width: 1024px) and (max-height: 760px) {
            body {
                padding: 12px 28px;
            }

            .checkout-shell {
                max-height: calc(100vh - 24px);
                gap: 16px;
            }

            .checkout-hero,
            .checkout-panel {
                padding: 1.35rem !important;
            }
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="relative selection:bg-[#7b9eed] selection:text-white">
    
    <div class="absolute -top-20 -left-20 w-64 h-64 bg-[#bde0fe] rounded-full mix-blend-multiply filter blur-3xl opacity-50 z-0"></div>
    <div class="absolute top-20 -right-20 w-72 h-72 bg-[#e0fbfc] rounded-full mix-blend-multiply filter blur-3xl opacity-50 z-0"></div>
    <div class="absolute -bottom-20 left-20 w-64 h-64 bg-[#e0ebff] rounded-full mix-blend-multiply filter blur-3xl opacity-50 z-0"></div>

    <div class="absolute top-12 left-10 text-5xl animate-float opacity-60 pointer-events-none z-0 hidden sm:block">☁️</div>
    <div class="absolute top-32 right-16 text-4xl animate-float-delayed opacity-60 pointer-events-none z-0 hidden sm:block">✨</div>
    <div class="absolute bottom-24 left-[20%] text-3xl animate-float-delayed opacity-50 pointer-events-none z-0 hidden sm:block">⭐</div>
    <div class="absolute bottom-10 right-[15%] text-6xl animate-float opacity-70 pointer-events-none z-0 hidden sm:block">☁️</div>

    <div class="checkout-shell max-w-md w-full max-h-full relative z-10">
        <div class="checkout-hero bg-gradient-to-br from-[#8faaf3] via-[#7695e8] to-[#5a76c8] rounded-[2rem] sm:rounded-[2.5rem] p-4 sm:p-6 text-center text-white shadow-xl shadow-[#5a76c8]/30 mb-3 sm:mb-4 border-4 border-white relative overflow-hidden transform hover:scale-[1.02] transition-transform duration-300">
            <div class="absolute -right-6 -bottom-6 text-8xl opacity-20 transform rotate-12 pointer-events-none animate-float">🛒</div>
            <div class="relative z-10">
                <div class="inline-block px-4 py-1 bg-white/20 backdrop-blur-sm text-white font-black rounded-full mb-2 text-[10px] border-2 border-white/50 shadow-inner uppercase tracking-widest">
                    Checkout
                </div>
                <h2 class="font-black text-2xl sm:text-3xl tracking-tight drop-shadow-sm">Checkout <span class="text-[#e0fbfc]">Tripay</span></h2>
                <p class="text-white/90 text-xs sm:text-sm mt-1.5 font-bold drop-shadow-sm">Satu invoice, satu pembayaran. Jangan buat tagihan baru sebelum invoice ini selesai.</p>
            </div>
        </div>

        <div class="checkout-panel bg-white/95 backdrop-blur-sm rounded-[2rem] sm:rounded-[2.5rem] shadow-2xl shadow-[#bde0fe]/40 border-4 border-white overflow-hidden p-4 sm:p-6">
            <div class="mb-4 rounded-[1.6rem] border-4 border-white bg-gradient-to-r from-[#e0fbfc] to-[#f8faff] p-4 shadow-inner">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status Pembayaran</p>
                        <p class="mt-1 text-lg font-black text-[#2b3a67]">Menunggu pembayaran</p>
                    </div>
                    <span class="rounded-full border-2 border-white bg-[#fff5eb] px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-amber-500 shadow-sm">Pending</span>
                </div>
            </div>
            
            <div class="flex items-center justify-between gap-3 mb-3 pb-3 sm:mb-4 sm:pb-4 border-b-4 border-dashed border-[#f4f9ff]">
                <span class="text-[#8faaf3] text-[10px] font-black uppercase tracking-widest">Nomor Invoice</span>
                <span class="font-black text-[#5a76c8] bg-[#f4f9ff] px-3 py-1.5 rounded-full text-xs sm:text-sm border-2 border-white shadow-sm">{{ $transaction->invoice_number }}</span>
            </div>

            <div class="flex items-center justify-between gap-3 mb-3 pb-3 sm:mb-4 sm:pb-4 border-b-4 border-dashed border-[#f4f9ff]">
                <span class="text-[#8faaf3] text-[10px] font-black uppercase tracking-widest">Layanan</span>
                <span class="font-black text-[#2b3a67] text-right max-w-[64%] leading-tight bg-[#f8faff] px-3 py-1.5 rounded-xl border border-white text-xs sm:text-sm line-clamp-2">{{ $transaction->product->name }}</span>
            </div>

            <div class="flex items-center justify-between gap-3 mb-4 sm:mb-5 bg-[#f8faff] p-4 rounded-[1.5rem] border-2 border-[#f0f5ff] shadow-inner">
                <span class="text-[#8faaf3] text-[10px] font-black uppercase tracking-widest">Total Tagihan</span>
                <span class="text-2xl sm:text-3xl font-black text-[#4bc6b9] tracking-tight drop-shadow-sm">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
            </div>

            <a id="pay-button" href="{{ $paymentUrl ?? $transaction->snap_token }}" data-tripay-pay-link class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black py-3 sm:py-4 rounded-full shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 flex justify-center items-center gap-2 border-4 border-white text-base sm:text-lg">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Bayar via Tripay
            </a>

            <form action="{{ route('user.checkout.cancel', $transaction) }}" method="POST" class="mt-3 sm:mt-4 text-center">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs sm:text-sm font-black text-[#8faaf3] hover:text-[#ff6b6b] hover:bg-[#ffe5e5] px-5 py-2.5 rounded-full transition-colors border-2 border-transparent hover:border-white inline-block shadow-sm active:scale-95">
                    Batal & Kembali
                </button>
            </form>
        </div>
        
        <p class="checkout-safe-note text-center text-[9px] sm:text-[10px] font-black text-[#8faaf3] mt-3 sm:mt-4 uppercase tracking-widest flex items-center justify-center gap-1 opacity-80">
            <svg style="width:14px;height:14px;display:inline-block;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            Pembayaran Aman Terenkripsi
        </p>
    </div>

    @include('partials.floating-admin-report')
    <script>
        document.querySelectorAll('[data-tripay-pay-link]').forEach((link) => {
            link.addEventListener('click', () => {
                if (link.dataset.clicked === '1') {
                    return;
                }

                link.dataset.clicked = '1';
                link.classList.add('pointer-events-none', 'opacity-70');
                link.innerHTML = 'Membuka Tripay...';
            });
        });
    </script>
</body>
</html>
