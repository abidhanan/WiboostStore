@extends('layouts.admin')

@section('title', 'Manajemen Reseller')
@section('admin_header_subtitle', 'Pantau komisi reseller dan konfirmasi penarikan.')

@section('content')
@php
    $formatRupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $statusClass = [
        'pending' => 'bg-[#fff7e6] text-[#f2a516]',
        'approved' => 'bg-[#e6fff7] text-[#08a878]',
        'rejected' => 'bg-[#ffe5e5] text-[#ff6b6b]',
    ];
@endphp

<style>
    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 999px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 999px; border: 2px solid #f0f5ff; }
    .admin-reseller-cell {
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
</style>

<div class="wiboost-font pb-12">
    @if(session('success'))
        <div class="mb-6 rounded-[1.5rem] border-4 border-[#d9fff5] bg-[#e9fff8] px-6 py-4 text-sm font-black text-[#08a878] shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-[1.5rem] border-4 border-[#ffe5e5] bg-[#fff3f3] px-6 py-4 text-sm font-black text-[#ff6b6b] shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/25">
            <p class="text-xs font-black uppercase tracking-[0.22em] text-[#8faaf3]">Total Reseller</p>
            <p class="mt-3 text-4xl font-black text-[#2b3a67]">{{ $resellers->count() }}</p>
        </div>
        <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/25">
            <p class="text-xs font-black uppercase tracking-[0.22em] text-[#8faaf3]">Penarikan Pending</p>
            <p class="mt-3 text-4xl font-black text-[#f2a516]">{{ $pendingWithdrawals->count() }}</p>
        </div>
        <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/25">
            <p class="text-xs font-black uppercase tracking-[0.22em] text-[#8faaf3]">Komisi Tercatat</p>
            <p class="mt-3 text-4xl font-black text-[#4bc6b9]">{{ $formatRupiah($resellers->sum('total_income')) }}</p>
        </div>
    </div>

    <section class="mt-8 rounded-[2rem] border-4 border-white bg-white p-5 shadow-xl shadow-[#bde0fe]/25 sm:p-7">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="inline-flex rounded-full border-2 border-white bg-[#e0fbfc] px-5 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-[#4bc6b9] shadow-sm">Konfirmasi</p>
                <h2 class="mt-3 text-2xl font-black text-[#2b3a67]">Penarikan Komisi</h2>
            </div>
            <p class="text-sm font-bold text-[#8faaf3]">Admin konfirmasi maksimal 24 jam.</p>
        </div>

        <div class="mt-6 space-y-4">
            @forelse($pendingWithdrawals as $withdrawal)
                <div class="rounded-[1.75rem] border-4 border-[#f4f9ff] bg-[#fbfdff] p-5">
                    <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div class="min-w-0">
                            <p class="text-lg font-black text-[#2b3a67]">{{ $withdrawal->user?->name ?? '-' }}</p>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $withdrawal->user?->email ?? '-' }}</p>
                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-[1.25rem] bg-white px-4 py-3 shadow-sm">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nominal</p>
                                    <p class="mt-1 text-lg font-black text-[#4bc6b9]">{{ $formatRupiah($withdrawal->amount) }}</p>
                                </div>
                                <div class="rounded-[1.25rem] bg-white px-4 py-3 shadow-sm">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Tujuan</p>
                                    <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $withdrawal->payout_method ?: '-' }}</p>
                                </div>
                                <div class="rounded-[1.25rem] bg-white px-4 py-3 shadow-sm">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nomor</p>
                                    <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $withdrawal->payout_account ?: '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.resellers.withdrawals.update', $withdrawal) }}" class="grid gap-3 sm:grid-cols-[1fr_1fr] lg:w-72">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="notes" value="">
                            <button type="submit" name="status" value="approved" class="rounded-full border-4 border-white bg-[#4bc6b9] px-5 py-3 text-sm font-black text-white shadow-lg shadow-[#4bc6b9]/25 transition active:scale-95">
                                Setujui
                            </button>
                            <button type="submit" name="status" value="rejected" class="rounded-full border-4 border-white bg-[#ffe5e5] px-5 py-3 text-sm font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/15 transition active:scale-95">
                                Tolak
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="rounded-[1.75rem] border-4 border-dashed border-[#d7ecff] bg-[#f8fbff] p-10 text-center text-sm font-black text-[#5a76c8]">
                    Belum ada penarikan komisi yang menunggu konfirmasi.
                </p>
            @endforelse
        </div>
    </section>

    <section class="mt-8 rounded-[2rem] border-4 border-white bg-white p-5 shadow-xl shadow-[#bde0fe]/25 sm:p-7">
        <h2 class="text-2xl font-black text-[#2b3a67]">Daftar Reseller</h2>
        <div class="table-scroll mt-5 overflow-x-auto pb-2">
            <table class="w-full min-w-[820px] text-left">
                <thead>
                    <tr class="border-b-4 border-dashed border-[#f4f9ff]">
                        <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Reseller</th>
                        <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Tujuan Penarikan</th>
                        <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Transaksi Sukses</th>
                        <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Total Komisi</th>
                        <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Bisa Ditarik</th>
                    </tr>
                </thead>
                <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                    @forelse($resellers as $reseller)
                        <tr>
                            <td class="admin-reseller-cell px-4 py-4 align-top">
                                <p class="text-sm font-black text-[#2b3a67]">{{ $reseller->name }}</p>
                                <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $reseller->email }}</p>
                            </td>
                            <td class="admin-reseller-cell px-4 py-4 align-top">
                                <p class="text-sm font-black text-[#2b3a67]">{{ $reseller->reseller_payout_method ?: '-' }}</p>
                                <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $reseller->reseller_payout_account ?: '-' }}</p>
                            </td>
                            <td class="px-4 py-4 align-top text-sm font-black text-[#5a76c8]">{{ $reseller->total_sales }}</td>
                            <td class="px-4 py-4 align-top text-sm font-black text-[#4bc6b9]">{{ $formatRupiah($reseller->total_income) }}</td>
                            <td class="px-4 py-4 align-top text-sm font-black text-[#d99b16]">{{ $formatRupiah($reseller->available_commission) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-sm font-black text-[#5a76c8]">Belum ada user yang bergabung menjadi reseller.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-8 rounded-[2rem] border-4 border-white bg-white p-5 shadow-xl shadow-[#bde0fe]/25 sm:p-7">
        <h2 class="text-2xl font-black text-[#2b3a67]">Riwayat Penarikan</h2>
        <div class="mt-5 space-y-3">
            @forelse($withdrawalHistories as $history)
                <div class="flex flex-col gap-3 rounded-[1.5rem] bg-[#f8fbff] p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-black text-[#2b3a67]">{{ $history->user?->name ?? '-' }}</p>
                        <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $history->created_at?->format('d M Y, H:i') }} - {{ $history->payout_method ?: '-' }} - {{ $history->payout_account ?: '-' }}</p>
                        @if($history->approver)
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">Admin: {{ $history->approver->name }}</p>
                        @endif
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <span class="rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-widest {{ $statusClass[$history->status] ?? 'bg-[#f4f9ff] text-[#5a76c8]' }}">
                            {{ $history->display_status }}
                        </span>
                        <p class="text-sm font-black text-[#ff6b6b]">- {{ $formatRupiah($history->amount) }}</p>
                    </div>
                </div>
            @empty
                <p class="rounded-[1.75rem] border-4 border-dashed border-[#d7ecff] bg-[#f8fbff] p-10 text-center text-sm font-black text-[#5a76c8]">
                    Riwayat penarikan reseller masih kosong.
                </p>
            @endforelse
        </div>
    </section>
</div>
@endsection
