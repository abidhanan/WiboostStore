<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran #{{ $transaction->invoice_number }} - Wiboost</title>
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
                    <h2 class="text-white font-extrabold text-2xl tracking-tight">Selesaikan Pembayaran</h2>
                    <p class="text-indigo-200 text-sm mt-2 font-medium">Satu langkah lagi untuk memproses pesananmu.</p>
                </div>
                <div class="absolute -right-8 -top-8 opacity-20 pointer-events-none">
                    <svg width="120" height="120" viewBox="0 0 24 24" fill="white"><path d="M12 2L1 21h22L12 2zm0 3.83L19.17 19H4.83L12 5.83zM11 16h2v2h-2v-2zm0-7h2v5h-2V9z"/></svg>
                </div>
            </div>

            <div class="p-8">
                <div class="flex items-center justify-between mb-5 pb-5 border-b border-dashed border-slate-200">
                    <span class="text-slate-500 text-sm font-semibold">Nomor Invoice</span>
                    <span class="font-mono font-bold text-slate-800 bg-slate-100 px-3 py-1 rounded-lg text-sm">{{ $transaction->invoice_number }}</span>
                </div>

                <div class="flex items-center justify-between mb-5 pb-5 border-b border-dashed border-slate-200">
                    <span class="text-slate-500 text-sm font-semibold">Produk / Layanan</span>
                    <span class="font-bold text-slate-800 text-right max-w-[60%]">{{ $transaction->product->name }}</span>
                </div>

                <div class="flex items-center justify-between mb-8">
                    <span class="text-slate-500 text-sm font-semibold">Total Tagihan</span>
                    <span class="text-3xl font-extrabold text-indigo-600 tracking-tight">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                </div>

                <button id="pay-button" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-200 transition-all active:scale-95 flex justify-center items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Bayar via Midtrans
                </button>

                <div class="mt-5 text-center">
                    <a href="{{ route('user.history') }}" class="text-sm font-bold text-slate-400 hover:text-rose-500 transition-colors">Batal & Kembali</a>
                </div>
            </div>
        </div>
        
        <p class="text-center text-xs font-semibold text-slate-400 mt-6 uppercase tracking-widest flex items-center justify-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            Pembayaran Aman
        </p>
    </div>

    <script type="text/javascript">
        var payButton = document.getElementById('pay-button');
        payButton.addEventListener('click', function () {
            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function(result){
                    window.location.href = "{{ route('user.history') }}";
                },
                onPending: function(result){
                    window.location.href = "{{ route('user.history') }}";
                },
                onError: function(result){
                    alert("Terjadi kesalahan teknis pada pembayaran!");
                },
                onClose: function(){
                    // Opsional: Kode di sini jalan jika user menutup popup (X) sebelum selesai bayar
                }
            });
        });
    </script>
</body>
</html>