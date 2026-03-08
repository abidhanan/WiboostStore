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
<body class="bg-gray-50 pb-12">

    <nav class="bg-white shadow-sm border-b p-4 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('user.dashboard') }}" class="text-blue-600 mr-4 hover:bg-blue-50 p-2 rounded-full transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Riwayat Transaksi</h1>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto py-8 px-4">
        <div class="space-y-4">
            @forelse($transactions as $trx)
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center hover:shadow-md transition">
                    
                    <div class="mb-4 md:mb-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-mono font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded">{{ $trx->invoice_number }}</span>
                            <span class="text-xs text-gray-400">{{ $trx->created_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                        
                        <h4 class="font-bold text-lg text-gray-800 mt-2">{{ $trx->product->name ?? 'Produk Tidak Tersedia' }}</h4>
                        
                        <div class="flex items-center gap-2 mt-1">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <p class="text-sm text-gray-600">Target: <span class="font-bold">{{ $trx->target_data }}</span></p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col items-start md:items-end w-full md:w-auto bg-gray-50 md:bg-transparent p-4 md:p-0 rounded-xl md:rounded-none">
                        <p class="font-extrabold text-gray-900 text-xl mb-3">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                        
                        <div class="flex gap-2">
                            @if($trx->payment_status == 'paid')
                                <span class="bg-green-100 text-green-700 px-3 py-1.5 rounded-lg text-xs font-bold uppercase border border-green-200">Lunas</span>
                            @elseif($trx->payment_status == 'failed')
                                <span class="bg-red-100 text-red-700 px-3 py-1.5 rounded-lg text-xs font-bold uppercase border border-red-200">Gagal Bayar</span>
                            @else
                                <span class="bg-yellow-100 text-yellow-700 px-3 py-1.5 rounded-lg text-xs font-bold uppercase border border-yellow-200">Belum Bayar</span>
                            @endif

                            @if($trx->payment_status == 'paid')
                                @if($trx->order_status == 'success')
                                    <span class="bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg text-xs font-bold uppercase border border-indigo-200">Pesanan Masuk</span>
                                @elseif($trx->order_status == 'processing')
                                    <span class="bg-amber-100 text-amber-700 px-3 py-1.5 rounded-lg text-xs font-bold uppercase border border-amber-200">Diproses Provider</span>
                                @elseif($trx->order_status == 'failed')
                                    <span class="bg-red-100 text-red-700 px-3 py-1.5 rounded-lg text-xs font-bold uppercase border border-red-200">Gagal Provider</span>
                                @else
                                    <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-lg text-xs font-bold uppercase border border-gray-200">Menunggu</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-24 bg-white rounded-3xl border border-dashed border-gray-300 shadow-sm">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-1">Belum Ada Transaksi</h3>
                    <p class="text-sm text-gray-500 max-w-sm mx-auto">Pesanan yang kamu buat akan otomatis muncul di halaman ini.</p>
                </div>
            @endforelse
        </div>
    </main>

</body>
</html>