<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Top Up #{{ $deposit->invoice_number }} - Wiboost</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Nunito', sans-serif; 
            background-color: #f4f9ff; 
        }
    </style>
    
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
</head>
<body class="min-h-screen flex items-center justify-center py-10 px-4 relative overflow-hidden">
    
    <div class="absolute -top-20 -left-20 w-64 h-64 bg-[#bde0fe] rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
    <div class="absolute top-20 -right-20 w-72 h-72 bg-[#e0fbfc] rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
    <div class="absolute -bottom-20 left-20 w-64 h-64 bg-[#e0ebff] rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>

    <div class="max-w-md w-full relative z-10">
        <div class="bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] rounded-[2rem] p-8 text-center text-white shadow-xl shadow-[#5a76c8]/30 mb-6 border-4 border-white relative overflow-hidden">
            <div class="absolute -right-6 -top-6 text-7xl opacity-20 transform rotate-12 pointer-events-none">💳</div>
            <div class="relative z-10">
                <h2 class="font-black text-3xl tracking-tight drop-shadow-sm">Pembayaran Top Up</h2>
                <p class="text-[#e0fbfc] text-sm mt-2 font-bold">Selesaikan pembayaran untuk mengisi saldo Wiboost kamu.</p>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/30 border-4 border-white overflow-hidden p-6 md:p-8">
            
            <div class="flex items-center justify-between mb-5 pb-5 border-b-2 border-dashed border-[#f0f5ff]">
                <span class="text-[#8faaf3] text-xs font-black uppercase tracking-widest">Nomor Invoice</span>
                <span class="font-black text-[#5a76c8] bg-[#f0f5ff] px-3 py-1 rounded-xl text-sm border border-white shadow-inner">{{ $deposit->invoice_number }}</span>
            </div>

            <div class="flex items-center justify-between mb-5 pb-5 border-b-2 border-dashed border-[#f0f5ff]">
                <span class="text-[#8faaf3] text-xs font-black uppercase tracking-widest">Layanan</span>
                <span class="font-black text-[#2b3a67] text-right">Top Up Saldo Wiboost</span>
            </div>

            <div class="flex items-center justify-between mb-8">
                <span class="text-[#8faaf3] text-xs font-black uppercase tracking-widest">Total Deposit</span>
                <span class="text-3xl font-black text-[#4bc6b9] tracking-tight drop-shadow-sm">Rp {{ number_format($deposit->amount, 0, ',', '.') }}</span>
            </div>

            <button id="pay-button" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black py-4 rounded-[1.5rem] shadow-lg shadow-[#5a76c8]/30 transition-transform active:scale-95 flex justify-center items-center gap-2 border-2 border-white text-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Pilih Metode Pembayaran
            </button>

            <div class="mt-6 text-center">
                <a href="{{ route('user.wallet.index') }}" class="text-sm font-black text-[#8faaf3] hover:text-[#ff6b6b] hover:bg-[#ffe5e5] px-6 py-2 rounded-full transition-colors border-2 border-transparent hover:border-white inline-block">Batal & Kembali</a>
            </div>
        </div>
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
</body>
</html>