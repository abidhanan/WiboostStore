@extends('layouts.admin')

@section('title', 'Semua Transaksi Masuk')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h3 class="text-xl font-bold text-gray-800">Riwayat Semua Transaksi</h3>
        <p class="text-sm text-gray-500 mt-1">Pantau seluruh pesanan pelanggan Wiboost Store di sini.</p>
    </div>
    <button class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl font-bold hover:bg-indigo-100 transition flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        Export Laporan
    </button>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead class="bg-gray-50/50 border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Tgl / Invoice</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Pelanggan</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Layanan / Produk</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Data Target</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Harga</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Status Pembayaran</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Status API</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($transactions as $trx)
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-indigo-600 mb-1">{{ $trx->invoice_number }}</p>
                        <p class="text-xs text-gray-400">{{ $trx->created_at->format('d/m/Y H:i') }}</p>
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs uppercase">
                                {{ substr($trx->user->name ?? '?', 0, 2) }}
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm">{{ $trx->user->name ?? 'Guest' }}</p>
                                <p class="text-xs text-gray-500">{{ $trx->user->email ?? '-' }}</p>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4 font-bold text-gray-800 text-sm">
                        {{ $trx->product->name ?? 'Produk Dihapus' }}
                    </td>

                    <td class="px-6 py-4 font-mono text-sm text-gray-600 bg-gray-50/50 rounded">
                        {{ $trx->target_data }}
                    </td>

                    <td class="px-6 py-4 font-bold text-gray-900">
                        Rp {{ number_format($trx->amount, 0, ',', '.') }}
                    </td>

                    <td class="px-6 py-4">
                        @if($trx->payment_status == 'paid')
                            <span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-xs font-bold border border-emerald-100">LUNAS</span>
                        @elseif($trx->payment_status == 'failed')
                            <span class="bg-rose-50 text-rose-600 px-3 py-1 rounded-full text-xs font-bold border border-rose-100">GAGAL</span>
                        @else
                            <span class="bg-amber-50 text-amber-600 px-3 py-1 rounded-full text-xs font-bold border border-amber-100">BELUM BAYAR</span>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        @if($trx->order_status == 'success')
                            <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-md text-xs font-bold">Sukses</span>
                        @elseif($trx->order_status == 'processing')
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-md text-xs font-bold">Diproses Provider</span>
                        @elseif($trx->order_status == 'failed')
                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-md text-xs font-bold">Gagal API</span>
                        @else
                            <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-md text-xs font-bold">Menunggu</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 italic">
                        Belum ada transaksi masuk.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection