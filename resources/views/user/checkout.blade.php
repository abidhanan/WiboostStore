<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran #{{ $transaction->invoice_number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-md mx-auto py-20 px-4 text-center">
        <div class="bg-white p-8 rounded-2xl shadow-lg border">
            <h2 class="text-xl font-bold mb-2">Konfirmasi Pembayaran</h2>
            <p class="text-gray-500 mb-6">Invoice: {{ $transaction->invoice_number }}</p>
            
            <div class="flex justify-between mb-2 text-sm">
                <span>Produk:</span>
                <span class="font-semibold">{{ $transaction->product->name }}</span>
            </div>
            <div class="flex justify-between mb-6 text-sm">
                <span>Total Bayar:</span>
                <span class="font-bold text-blue-600 text-lg">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
            </div>

            <button id="pay-button" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl shadow-lg">
                Bayar Sekarang
            </button>
        </div>
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
                    alert("Pembayaran gagal!");
                }
            });
        });
    </script>
</body>
</html>