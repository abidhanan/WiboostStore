@extends('layouts.admin')
@section('title', 'Riwayat Transaksi')
@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-12">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4 pl-2">
        <div>
            <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Manajemen Transaksi 🧾</h3>
            <p class="text-sm text-[#8faaf3] font-bold mt-1">Pantau pesanan pelanggan dan filter berdasarkan bulan.</p>
        </div>
        <a href="{{ route('admin.transactions.index') }}" class="bg-white text-[#5a76c8] px-6 py-3 rounded-full font-black hover:bg-[#f0f5ff] hover:-translate-y-1 transition-all flex items-center gap-2 shadow-lg shadow-[#bde0fe]/30 border-4 border-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            Segarkan Data
        </a>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-sm">
            <span class="text-2xl">🎉</span> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-5 rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white mb-8">
        <form action="{{ route('admin.transactions.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-center w-full">
            <div class="relative flex-1 w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-[#8faaf3]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" 
                       class="w-full pl-12 pr-4 py-3 bg-[#f4f9ff] rounded-[1rem] border-2 border-[#e0fbfc] focus:border-[#5a76c8] outline-none transition text-[#2b3a67] font-black text-sm placeholder-[#a3bbfb]" 
                       placeholder="Cari Invoice atau Nama...">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto bg-[#f4f9ff] rounded-[1rem] border-2 border-[#e0fbfc] p-1">
                <select name="month" onchange="this.form.submit()" class="bg-transparent text-[#2b3a67] px-3 py-2 font-black outline-none cursor-pointer text-sm">
                    @for($m=1; $m<=12; $m++)
                        <option value="{{ sprintf('%02d', $m) }}" {{ $selectedMonth == sprintf('%02d', $m) ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
                <select name="year" onchange="this.form.submit()" class="bg-transparent text-[#2b3a67] px-3 py-2 font-black outline-none cursor-pointer text-sm border-l-2 border-[#e0fbfc]">
                    @for($y=date('Y'); $y>=date('Y')-3; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <button type="submit" class="flex-1 md:flex-none bg-[#5a76c8] hover:bg-[#4760a9] text-white px-6 py-3 rounded-[1rem] font-black transition-transform active:scale-95 shadow-md shadow-[#5a76c8]/30 border-2 border-white text-sm">Cari</button>
                <a href="{{ route('admin.transactions.export_pdf', ['month' => $selectedMonth, 'year' => $selectedYear]) }}" target="_blank" class="flex-1 md:flex-none bg-[#4bc6b9] hover:bg-[#3ba398] text-white px-5 py-3 rounded-[1rem] font-black transition-transform active:scale-95 shadow-md shadow-[#4bc6b9]/30 border-2 border-white text-sm flex items-center justify-center gap-2" title="Cetak PDF Bulan Ini">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white overflow-hidden">
        <div class="overflow-x-auto p-2 md:p-4">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr>
                        <th class="px-4 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Waktu & Invoice</th>
                        <th class="px-4 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Pelanggan</th>
                        <th class="px-4 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Layanan</th>
                        <th class="px-4 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Harga</th>
                        <th class="px-4 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center">Target</th>
                        <th class="px-4 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center">Metode</th>
                        <th class="px-4 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-dashed divide-[#f0f5ff]">
                    @forelse($transactions as $trx)
                    <tr class="hover:bg-[#f4f9ff] transition-colors rounded-xl group">
                        
                        <td class="px-4 py-5 align-top">
                            <p class="font-black text-[#5a76c8] text-sm mb-1">{{ $trx->invoice_number }}</p>
                            <p class="text-[10px] font-bold text-[#8faaf3]">{{ $trx->created_at->format('d M Y, H:i') }}</p>
                        </td>
                        
                        <td class="px-4 py-5 align-top">
                            <p class="font-black text-[#2b3a67] text-sm truncate max-w-[120px] md:max-w-[150px]">{{ $trx->user->name ?? 'Guest' }}</p>
                            <p class="text-[10px] font-bold text-[#8faaf3] truncate max-w-[120px] md:max-w-[150px] mt-1">{{ $trx->user->email ?? '-' }}</p>
                        </td>
                        
                        <td class="px-4 py-5 align-top min-w-[180px] whitespace-normal">
                            <p class="font-black text-[#2b3a67] text-sm line-clamp-2 leading-snug">{{ $trx->product->name ?? 'Produk Dihapus' }}</p>
                        </td>

                        <td class="px-4 py-5 align-top">
                            <p class="font-black text-[#4bc6b9] text-sm">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                        </td>

                        <td class="px-4 py-5 align-top text-center">
                            @if($trx->has_order_input && filled($trx->order_input_summary))
                                <span class="text-[11px] font-black text-[#2b3a67] bg-[#f0f5ff] inline-block px-3 py-1.5 rounded-lg border border-white shadow-inner truncate max-w-[120px] md:max-w-[150px]" title="{{ $trx->order_input_text }}">
                                    {{ $trx->order_input_summary }}
                                </span>
                            @else
                                <span class="text-[14px] font-black text-[#a3bbfb] bg-[#f0f5ff] inline-block px-4 py-1 rounded-lg border border-white shadow-inner">-</span>
                            @endif
                        </td>
                        
                        <td class="px-4 py-5 align-top text-center">
                            @php
                                $rawMethod = strtolower($trx->payment_method ?? '');
                                
                                if ($rawMethod == 'wallet') {
                                    $methodDisplay = 'SALDO';
                                    $colorClass = 'bg-[#e0fbfc] text-[#5a76c8]';
                                } elseif (($rawMethod == 'manual' || empty($rawMethod)) && $trx->payment_status != 'paid') {
                                    $methodDisplay = 'BELUM PILIH';
                                    $colorClass = 'bg-[#fff5eb] text-amber-500';
                                } else {
                                    // PEMETAAN MANUAL KODE MIDTRANS KE NAMA CANTIK
                                    $methodMap = [
                                        'qris'           => 'QRIS',
                                        'gopay'          => 'GoPay',
                                        'shopeepay'      => 'ShopeePay',
                                        'bank_transfer'  => 'Bank Transfer',
                                        'cstore'         => 'Indomaret/ALFA',
                                        'credit_card'    => 'Kartu Kredit',
                                        'echannel'       => 'Mandiri Bill',
                                        'permata_va'     => 'Permata VA',
                                        'bca_va'         => 'BCA Transfer',
                                        'bni_va'         => 'BNI Transfer',
                                        'bri_va'         => 'BRI Transfer',
                                        'cimb_va'        => 'CIMB Transfer',
                                        'other_va'       => 'ATM Bersama',
                                        'danamon_online' => 'Danamon Online',
                                        'akulaku'        => 'Akulaku',
                                        'kredivo'        => 'Kredivo',
                                    ];

                                    $methodDisplay = $methodMap[$rawMethod] ?? ucwords(str_replace('_', ' ', $rawMethod));
                                    
                                    // Fallback jika masih kata-kata umum
                                    if ($methodDisplay == 'Manual') $methodDisplay = 'E-WALLET';
                                    
                                    $colorClass = 'bg-[#f4f9ff] text-[#8faaf3]';
                                }
                            @endphp
                            <span class="text-[9px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full border border-white shadow-sm inline-block {{ $colorClass }}">
                                {{ $methodDisplay }}
                            </span>
                        </td>
                        
                        <td class="px-4 py-5 align-top text-center min-w-[160px]">
                            @if($trx->product && $trx->product->process_type === 'manual')
                                <form action="{{ route('admin.transactions.update', $trx->id) }}" method="POST" class="flex items-center gap-1.5 justify-center">
                                    @csrf
                                    @method('PATCH')
                                    <select name="order_status" class="bg-[#f8faff] border-2 border-[#e0fbfc] text-[#2b3a67] text-[10px] uppercase tracking-widest rounded-full focus:border-[#5a76c8] block px-3 py-1.5 font-black outline-none shadow-sm cursor-pointer hover:bg-[#e0fbfc] transition-colors appearance-none text-center">
                                        <option value="pending" {{ $trx->order_status == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                        <option value="processing" {{ $trx->order_status == 'processing' ? 'selected' : '' }}>⚙️ Proses</option>
                                        <option value="success" {{ $trx->order_status == 'success' ? 'selected' : '' }}>✅ Sukses</option>
                                        <option value="failed" {{ $trx->order_status == 'failed' ? 'selected' : '' }}>❌ Gagal</option>
                                    </select>
                                    <button type="submit" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white p-2 rounded-full transition shadow-sm active:scale-95 border border-[#5a76c8] shrink-0" title="Simpan Status">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                </form>
                            @else
                                @php
                                    $statusLabels = [
                                        'success' => ['bg-[#e6fff7]', 'text-emerald-500', '✅ Sukses'],
                                        'processing' => ['bg-[#e0fbfc]', 'text-[#5a76c8]', '⚙️ Proses'],
                                        'pending' => ['bg-[#fff5eb]', 'text-amber-500', '⏳ Pending'],
                                        'failed' => ['bg-[#ffe5e5]', 'text-[#ff6b6b]', '❌ Gagal'],
                                    ];
                                    $st = $statusLabels[$trx->order_status] ?? ['bg-gray-100', 'text-gray-500', $trx->order_status];
                                @endphp
                                <span class="{{ $st[0] }} {{ $st[1] }} px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white shadow-sm inline-block w-24 text-center">
                                    {{ $st[2] }}
                                </span>
                            @endif
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-16 text-center">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-[2rem] bg-[#f0f5ff] border-4 border-white mb-4 shadow-inner">
                                <span class="text-4xl">📭</span>
                            </div>
                            <p class="text-[#8faaf3] font-black text-lg">Tidak ada transaksi pada periode ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($transactions->hasPages())
            <div class="p-4 md:p-6 border-t-2 border-dashed border-[#f0f5ff] bg-[#f4f9ff]">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
