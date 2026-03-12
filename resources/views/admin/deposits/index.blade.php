@extends('layouts.admin')

@section('title', 'Manajemen Top Up Saldo')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">Riwayat Top Up Saldo</h3>
        <p class="text-sm text-slate-500 mt-1">Pantau dan setujui pengisian dompet digital pelanggan.</p>
    </div>
</div>

@if(session('success'))
    <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 px-4 py-3 rounded-xl mb-6 font-bold flex items-center gap-3">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left whitespace-nowrap">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Waktu & Invoice</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Pelanggan</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Nominal Top Up</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest text-center">Status</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest text-center">Aksi (Manual)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($deposits as $deposit)
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-6 py-4">
                        <p class="font-bold text-indigo-600 text-sm mb-0.5">{{ $deposit->invoice_number }}</p>
                        <p class="text-xs text-slate-500">{{ $deposit->created_at->format('d M Y, H:i') }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-bold text-slate-800 text-sm">{{ $deposit->user->name ?? 'Guest' }}</p>
                        <p class="text-xs text-slate-500">{{ $deposit->user->email ?? '-' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-extrabold text-emerald-600 text-sm">+ Rp {{ number_format($deposit->amount, 0, ',', '.') }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ strtoupper($deposit->payment_method ?? 'Menunggu') }}</p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($deposit->payment_status == 'paid')
                            <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide">Sukses</span>
                        @elseif($deposit->payment_status == 'failed')
                            <span class="bg-rose-100 text-rose-700 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide">Gagal</span>
                        @else
                            <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide">Pending</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($deposit->payment_status == 'unpaid')
                            <form action="{{ route('admin.deposits.update', $deposit->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menyetujui Top Up ini? Saldo user akan otomatis bertambah.')">
                                @csrf @method('PATCH')
                                <input type="hidden" name="payment_status" value="paid">
                                <button type="submit" class="bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white px-4 py-2 rounded-lg text-xs font-bold transition shadow-sm border border-indigo-100">
                                    Approve Manual
                                </button>
                            </form>
                        @else
                            <span class="text-slate-300 text-xs font-bold italic">Selesai</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-slate-500 font-medium">Belum ada riwayat top up.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($deposits->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $deposits->links() }}
        </div>
    @endif
</div>
@endsection