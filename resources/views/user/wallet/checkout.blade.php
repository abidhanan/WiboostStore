<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Top Up #{{ $deposit->invoice_number }} - Wiboost</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
    </style>
    
    <script type="text/javascript" src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
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
        <div class="bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] rounded-[2rem] sm:rounded-[2.5rem] p-4 sm:p-6 text-center text-white shadow-xl shadow-[#5a76c8]/30 mb-3 sm:mb-4 border-4 border-white relative overflow-hidden transform hover:scale-[1.02] transition-transform duration-300">
            <div class="absolute -right-6 -bottom-6 text-8xl opacity-20 transform rotate-12 pointer-events-none animate-float">💳</div>
            <div class="relative z-10">
                <div class="inline-block px-4 py-1 bg-white/20 backdrop-blur-sm text-white font-black rounded-full mb-2 text-[10px] border-2 border-white/50 shadow-inner uppercase tracking-widest">
                    Checkout
                </div>
                <h2 class="font-black text-2xl sm:text-3xl tracking-tight drop-shadow-sm">Pembayaran <span class="text-[#e0fbfc]">Top Up</span></h2>
                <p class="text-white/90 text-xs sm:text-sm mt-1.5 font-bold drop-shadow-sm">Selesaikan pembayaran untuk mengisi saldo Wiboost kamu.</p>
            </div>
        </div>

        <div class="bg-white/95 backdrop-blur-sm rounded-[2rem] sm:rounded-[2.5rem] shadow-2xl shadow-[#bde0fe]/40 border-4 border-white overflow-hidden p-4 sm:p-6">
            
            <div class="flex items-center justify-between gap-3 mb-3 pb-3 sm:mb-4 sm:pb-4 border-b-4 border-dashed border-[#f4f9ff]">
                <span class="text-[#8faaf3] text-[10px] font-black uppercase tracking-widest">Nomor Invoice</span>
                <span class="font-black text-[#5a76c8] bg-[#f4f9ff] px-3 py-1.5 rounded-full text-xs sm:text-sm border-2 border-white shadow-sm">{{ $deposit->invoice_number }}</span>
            </div>

            <div class="flex items-center justify-between gap-3 mb-3 pb-3 sm:mb-4 sm:pb-4 border-b-4 border-dashed border-[#f4f9ff]">
                <span class="text-[#8faaf3] text-[10px] font-black uppercase tracking-widest">Layanan</span>
                <span class="font-black text-[#2b3a67] text-right bg-[#f8faff] px-3 py-1.5 rounded-full text-xs sm:text-sm border border-white">Top Up Saldo Wiboost</span>
            </div>

            <div class="flex items-center justify-between gap-3 mb-4 sm:mb-5 bg-[#f8faff] p-4 rounded-[1.5rem] border-2 border-[#f0f5ff] shadow-inner">
                <span class="text-[#8faaf3] text-[10px] font-black uppercase tracking-widest">Total Deposit</span>
                <span class="text-2xl sm:text-3xl font-black text-[#4bc6b9] tracking-tight drop-shadow-sm">Rp {{ number_format($deposit->amount, 0, ',', '.') }}</span>
            </div>

            <button id="pay-button" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black py-3 sm:py-4 rounded-full shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 flex justify-center items-center gap-2 border-4 border-white text-base sm:text-lg">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Pilih Metode Bayar
            </button>

            <div class="mt-3 sm:mt-4 text-center">
                <a href="{{ route('user.wallet.index') }}" class="text-xs sm:text-sm font-black text-[#8faaf3] hover:text-[#ff6b6b] hover:bg-[#ffe5e5] px-5 py-2.5 rounded-full transition-colors border-2 border-transparent hover:border-white inline-block shadow-sm">Batal & Kembali</a>
            </div>
        </div>
        
        <p class="text-center text-[9px] sm:text-[10px] font-black text-[#8faaf3] mt-3 sm:mt-4 uppercase tracking-widest flex items-center justify-center gap-1 opacity-80">
            <svg style="width:14px;height:14px;display:inline-block;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            Pembayaran Aman Terenkripsi
        </p>
    </div>

    <script type="text/javascript">
        var payButton = document.getElementById('pay-button');
        payButton.addEventListener('click', function () {
            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function(result){
                    window.location.href = "{{ route('user.wallet.index') }}";
                },
                onPending: function(result){
                    window.location.href = "{{ route('user.wallet.index') }}";
                },
                onError: function(result){
                    alert("Pembayaran gagal. Silakan coba lagi.");
                }
            });
        });
    </script>

    @include('partials.floating-admin-report')
</body>
</html>
