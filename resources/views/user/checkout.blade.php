<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran #{{ $transaction->invoice_number }} - Wiboost</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Nunito', sans-serif; 
            background-color: #f4f9ff; 
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
    </style>
    
    <script type="text/javascript" src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
</head>
<body class="min-h-screen flex items-center justify-center py-10 px-4 relative overflow-hidden selection:bg-[#7b9eed] selection:text-white">
    
    <div class="absolute -top-20 -left-20 w-64 h-64 bg-[#bde0fe] rounded-full mix-blend-multiply filter blur-3xl opacity-50 z-0"></div>
    <div class="absolute top-20 -right-20 w-72 h-72 bg-[#e0fbfc] rounded-full mix-blend-multiply filter blur-3xl opacity-50 z-0"></div>
    <div class="absolute -bottom-20 left-20 w-64 h-64 bg-[#e0ebff] rounded-full mix-blend-multiply filter blur-3xl opacity-50 z-0"></div>

    <div class="absolute top-12 left-10 text-5xl animate-float opacity-60 pointer-events-none z-0 hidden sm:block">☁️</div>
    <div class="absolute top-32 right-16 text-4xl animate-float-delayed opacity-60 pointer-events-none z-0 hidden sm:block">✨</div>
    <div class="absolute bottom-24 left-[20%] text-3xl animate-float-delayed opacity-50 pointer-events-none z-0 hidden sm:block">⭐</div>
    <div class="absolute bottom-10 right-[15%] text-6xl animate-float opacity-70 pointer-events-none z-0 hidden sm:block">☁️</div>

    <div class="max-w-md w-full relative z-10">
        <div class="bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] rounded-[2.5rem] p-8 text-center text-white shadow-xl shadow-[#5a76c8]/30 mb-6 border-4 border-white relative overflow-hidden transform hover:scale-[1.02] transition-transform duration-300">
            <div class="absolute -right-6 -bottom-6 text-8xl opacity-20 transform rotate-12 pointer-events-none animate-float">🛒</div>
            <div class="relative z-10">
                <div class="inline-block px-4 py-1 bg-white/20 backdrop-blur-sm text-white font-black rounded-full mb-3 text-[10px] border-2 border-white/50 shadow-inner uppercase tracking-widest">
                    Checkout
                </div>
                <h2 class="font-black text-3xl tracking-tight drop-shadow-sm">Selesaikan <span class="text-[#e0fbfc]">Pembayaran!</span></h2>
                <p class="text-white/90 text-sm mt-2 font-bold drop-shadow-sm">Satu langkah lagi untuk memproses pesananmu.</p>
            </div>
        </div>

        <div class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-2xl shadow-[#bde0fe]/40 border-4 border-white overflow-hidden p-6 md:p-8">
            
            <div class="flex items-center justify-between mb-5 pb-5 border-b-4 border-dashed border-[#f4f9ff]">
                <span class="text-[#8faaf3] text-[10px] font-black uppercase tracking-widest">Nomor Invoice</span>
                <span class="font-black text-[#5a76c8] bg-[#f4f9ff] px-4 py-1.5 rounded-full text-sm border-2 border-white shadow-sm">{{ $transaction->invoice_number }}</span>
            </div>

            <div class="flex items-center justify-between mb-5 pb-5 border-b-4 border-dashed border-[#f4f9ff]">
                <span class="text-[#8faaf3] text-[10px] font-black uppercase tracking-widest">Layanan</span>
                <span class="font-black text-[#2b3a67] text-right max-w-[60%] leading-tight bg-[#f8faff] px-4 py-2 rounded-xl border border-white">{{ $transaction->product->name }}</span>
            </div>

            <div class="flex items-center justify-between mb-8 bg-[#f8faff] p-5 rounded-[1.5rem] border-2 border-[#f0f5ff] shadow-inner">
                <span class="text-[#8faaf3] text-[10px] font-black uppercase tracking-widest">Total Tagihan</span>
                <span class="text-3xl font-black text-[#4bc6b9] tracking-tight drop-shadow-sm">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
            </div>

            <button id="pay-button" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black py-4 rounded-full shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 flex justify-center items-center gap-2 border-4 border-white text-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Pilih Metode Bayar
            </button>

            <div class="mt-6 text-center">
                <a href="{{ route('user.history') }}" class="text-sm font-black text-[#8faaf3] hover:text-[#ff6b6b] hover:bg-[#ffe5e5] px-6 py-3 rounded-full transition-colors border-2 border-transparent hover:border-white inline-block shadow-sm">Batal & Kembali</a>
            </div>
        </div>
        
        <p class="text-center text-[10px] font-black text-[#8faaf3] mt-6 uppercase tracking-widest flex items-center justify-center gap-1 opacity-80">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            Pembayaran Aman Terenkripsi
        </p>
    </div>

    <script type="text/javascript">
        var payButton = document.getElementById('pay-button');
        payButton.addEventListener('click', function () {
            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function(result){
                    // Redirect dengan membawa parameter auto_open
                    window.location.href = "{{ route('user.history') }}?auto_open={{ $transaction->id }}";
                },
                onPending: function(result){
                    // Redirect dengan membawa parameter auto_open
                    window.location.href = "{{ route('user.history') }}?auto_open={{ $transaction->id }}";
                },
                onError: function(result){
                    alert("Pembayaran gagal. Silakan coba lagi.");
                },
                onClose: function(){
                    // Arahkan kembali ke history meskipun popup ditutup tanpa bayar
                    window.location.href = "{{ route('user.history') }}?auto_open={{ $transaction->id }}";
                }
            });
        });
    </script>

    @include('partials.floating-admin-report')
</body>
</html>