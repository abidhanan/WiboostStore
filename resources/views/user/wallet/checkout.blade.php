<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Top Up #{{ $deposit->invoice_number }} - Wiboost</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
    
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
</head>
<body class="bg-[#F8FAFC] min-h-screen flex items-center justify-center py-10 px-4">
    
    <div class="max-w-md w-full">
        <div class="bg-white rounded-[2rem] shadow-xl shadow-indigo-100/50 border border-slate-100 overflow-hidden">
            <div class="bg-indigo-600 p-8 text-center relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-white font-extrabold text-2xl tracking-tight">Pembayaran Top Up</h2>
                    <p class="text-indigo-200 text-sm mt-2 font-medium">Selesaikan pembayaran untuk mengisi saldo Wiboost kamu.</p>
                </div>
            </div>

            <div class="p-8">
                <div class="flex items-center justify-between mb-5 pb-5 border-b border-dashed border-slate-200">
                    <span class="text-slate-500 text-sm font-semibold">Nomor Invoice</span>
                    <span class="font-mono font-bold text-slate-800 bg-slate-100 px-3 py-1 rounded-lg text-sm">{{ $deposit->invoice_number }}</span>
                </div>

                <div class="flex items-center justify-between mb-5 pb-5 border-b border-dashed border-slate-200">
                    <span class="text-slate-500 text-sm font-semibold">Layanan</span>
                    <span class="font-bold text-slate-800 text-right">Top Up Saldo Wiboost</span>
                </div>

                <div class="flex items-center justify-between mb-8">
                    <span class="text-slate-500 text-sm font-semibold">Total Deposit</span>
                    <span class="text-3xl font-extrabold text-indigo-600 tracking-tight">Rp {{ number_format($deposit->amount, 0, ',', '.') }}</span>
                </div>

                <button id="pay-button" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-200 transition-all active:scale-95 flex justify-center items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Pilih Metode Pembayaran
                </button>

                <div class="mt-5 text-center">
                    <a href="{{ route('user.wallet.index') }}" class="text-sm font-bold text-slate-400 hover:text-rose-500 transition-colors">Batal & Kembali</a>
                </div>
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