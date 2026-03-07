<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Transaksi - Wiboost Store</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-50">

    <nav class="bg-white shadow-sm border-b p-4">
        <div class="max-w-4xl mx-auto flex items-center">
            <a href="{{ route('user.dashboard') }}" class="text-blue-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-xl font-bold text-gray-800">Riwayat Transaksi</h1>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto py-8 px-4">
        <div class="space-y-4">
            @forelse($transactions as $trx)
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div>
                        <p class="text-xs text-gray-500 font-mono">{{ $trx->invoice_number }}</p>
                        <h4 class="font-bold text-gray-800">{{ $trx->product->name }}</h4>
                        <p class="text-sm text-gray-600">Target: <span class="font-medium">{{ $trx->target_data }}</span></p>
                        <p class="text-xs text-gray-400 mt-1">{{ $trx->created_at->format('d M Y, H:i') }} WIB</p>
                    </div>
                    
                    <div class="mt-4 md:mt-0 text-right w-full md:w-auto">
                        <p class="font-bold text-blue-600 mb-2">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                        
                        @if($trx->payment_status == 'paid')
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Lunas</span>
                        @elseif($trx->payment_status == 'failed')
                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Gagal</span>
                        @else
                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Menunggu Pembayaran</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-gray-300">
                    <p class="text-gray-500 italic">Belum ada transaksi apa pun.</p>
                </div>
            @endforelse
        </div>
    </main>

</body>
</html>