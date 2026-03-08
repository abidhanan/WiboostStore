@extends('layouts.admin')

@section('title', 'Semua Transaksi Masuk')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h3 class="text-xl font-bold text-gray-800">Riwayat Semua Transaksi</h3>
        <p class="text-sm text-gray-500 mt-1">Pantau dan kelola seluruh pesanan pelanggan di sini.</p>
    </div>
    <button class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl font-bold hover:bg-indigo-100 transition flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        Export Laporan
    </button>
</div>

@if(session('success'))
    <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 px-4 py-3 rounded-xl mb-6 font-bold flex items-center gap-3">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead class="bg-gray-50/50 border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Tgl / Invoice</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Pelanggan</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Layanan</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Harga</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Pembayaran</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Status Pesanan</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Aksi Manual</th>
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
                                <p class="text-xs text-gray-500">{{ $trx->target_data }}</p>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4 font-bold text-gray-800 text-sm">
                        {{ $trx->product->name ?? 'Produk Dihapus' }}
                    </td>

                    <td class="px-6 py-4 font-bold text-gray-900">
                        Rp {{ number_format($trx->amount, 0, ',', '.') }}
                    </td>

                    <td class="px-6 py-4 text-xs font-bold">
                        @if($trx->payment_status == 'paid')
                            <span class="text-emerald-600">LUNAS</span>
                        @else
                            <span class="text-amber-500 font-mono italic">PENDING</span>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        @if($trx->order_status == 'success')
                            <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-md text-xs font-bold uppercase">Sukses</span>
                        @elseif($trx->order_status == 'processing')
                            <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-md text-xs font-bold uppercase italic">Diproses</span>
                        @elseif($trx->order_status == 'failed')
                            <span class="bg-red-50 text-red-600 px-3 py-1 rounded-md text-xs font-bold uppercase">Gagal</span>
                        @else
                            <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-md text-xs font-bold uppercase italic">Menunggu</span>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex justify-center gap-2">
                            <form action="{{ route('admin.transactions.update', $trx->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="success">
                                <button type="submit" class="p-2 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition shadow-sm" title="Tandai Sukses">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </form>
                            <form action="{{ route('admin.transactions.update', $trx->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="failed">
                                <button type="submit" class="p-2 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-100 transition shadow-sm" title="Tandai Gagal">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 italic">Belum ada transaksi masuk.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection