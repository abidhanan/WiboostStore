<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Transaksi - Wiboost Store</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Nunito', sans-serif; } </style>
</head>
<body class="bg-[#f4f9ff] text-slate-800 antialiased pb-20">

    <nav class="bg-white/90 backdrop-blur-md border-b-4 border-white shadow-sm sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 flex items-center h-20">
            <a href="{{ route('user.dashboard') }}" class="w-10 h-10 bg-[#f0f5ff] hover:bg-[#e0ebff] text-[#5a76c8] rounded-full flex items-center justify-center transition mr-4 border-2 border-white shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h1 class="text-2xl font-black text-[#2b3a67]">Riwayat Pesanan</h1>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto py-10 px-4">
        
        @if(session('success'))
            <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-sm">
                <span class="text-2xl">🎉</span> {{ session('success') }}
            </div>
        @endif

        <div class="space-y-6">
            @forelse($transactions as $trx)
                <div class="bg-white rounded-[2rem] p-6 shadow-lg shadow-[#bde0fe]/20 border-4 border-white flex flex-col md:flex-row justify-between items-start md:items-center hover:border-[#bde0fe] hover:-translate-y-1 transition-all group">
                    
                    <div class="mb-5 md:mb-0 w-full md:w-2/3">
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span class="text-xs font-black text-white bg-[#5a76c8] px-3 py-1 rounded-full border-2 border-white shadow-sm">{{ $trx->invoice_number }}</span>
                            <span class="text-xs font-bold text-[#8faaf3]">{{ $trx->created_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                        
                        <h4 class="font-black text-xl text-[#2b3a67]">{{ $trx->product->name ?? 'Produk Dihapus' }}</h4>
                        
                        <div class="inline-flex items-center gap-2 mt-2 bg-[#f0f5ff] px-4 py-1.5 rounded-xl border border-white shadow-inner">
                            <span class="text-lg">🎯</span>
                            <p class="text-sm text-[#5a76c8] font-bold">Target: <span class="font-black">{{ $trx->target_data }}</span></p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col items-start md:items-end w-full md:w-auto bg-[#f4f9ff] md:bg-transparent p-5 md:p-0 rounded-[1.5rem] md:rounded-none border-2 border-white md:border-0 shadow-inner md:shadow-none">
                        <p class="font-black text-[#5a76c8] text-2xl mb-3">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                        
                        <div class="flex flex-wrap justify-start md:justify-end gap-2">
                            @if($trx->payment_status == 'paid')
                                <span class="bg-[#e6fff7] text-emerald-500 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest border border-white shadow-sm">Lunas</span>
                            @elseif($trx->payment_status == 'failed')
                                <span class="bg-[#ffe5e5] text-[#ff6b6b] px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest border border-white shadow-sm">Gagal Bayar</span>
                            @else
                                <span class="bg-[#fff5eb] text-amber-500 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest border border-white shadow-sm">Menunggu Bayar</span>
                            @endif

                            @if($trx->payment_status == 'paid')
                                @if($trx->order_status == 'success')
                                    <span class="bg-[#e6fff7] text-emerald-500 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest border border-white shadow-sm">Pesanan Sukses</span>
                                @elseif($trx->order_status == 'processing')
                                    <span class="bg-[#f0f5ff] text-[#5a76c8] px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest border border-white shadow-sm animate-pulse">Diproses</span>
                                @elseif($trx->order_status == 'failed')
                                    <span class="bg-[#ffe5e5] text-[#ff6b6b] px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest border border-white shadow-sm">Pesanan Gagal</span>
                                @else
                                    <span class="bg-[#f4f9ff] text-[#8faaf3] px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest border border-white shadow-sm">Menunggu</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-24 bg-white rounded-[2rem] border-4 border-dashed border-[#bde0fe] shadow-sm">
                    <div class="text-7xl mb-4 opacity-50">🛒</div>
                    <h3 class="text-xl font-black text-[#5a76c8] mb-2">Belum Ada Transaksi</h3>
                    <p class="text-[#8faaf3] font-bold max-w-sm mx-auto">Pesanan yang kamu buat akan otomatis muncul di sini. Yuk mulai jajan!</p>
                </div>
            @endforelse
        </div>
    </main>
</body>
</html>