@extends('layouts.admin')

@section('title', 'Halaman Komisi')
@section('admin_header_subtitle', 'Pantau komisi masuk dan konfirmasi penarikan reseller.')

@section('content')
@php
    $formatRupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $statusClass = [
        'pending' => 'bg-[#fff7e6] text-[#f2a516]',
        'approved' => 'bg-[#e6fff7] text-[#08a878]',
        'rejected' => 'bg-[#ffe5e5] text-[#ff6b6b]',
        'success' => 'bg-[#e0fbfc] text-[#5a76c8]',
    ];
@endphp

<div class="wiboost-font pb-12">
    @if(session('success'))
        <div class="mb-6 rounded-[1.5rem] border-4 border-[#d9fff5] bg-[#e9fff8] px-6 py-4 text-sm font-black text-[#08a878] shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/25">
            <p class="text-xs font-black uppercase tracking-[0.22em] text-[#8faaf3]">Komisi Masuk</p>
            <p class="mt-3 text-2xl font-black text-[#4bc6b9]">{{ $formatRupiah($totalIncome) }}</p>
        </div>
        <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/25">
            <p class="text-xs font-black uppercase tracking-[0.22em] text-[#8faaf3]">Sudah Ditarik</p>
            <p class="mt-3 text-2xl font-black text-[#ff6b6b]">{{ $formatRupiah($totalApprovedWithdrawal) }}</p>
        </div>
        <div class="rounded-[2rem] border-4 border-white bg-[#fff7d6] p-6 shadow-xl shadow-[#f4b740]/15">
            <p class="text-xs font-black uppercase tracking-[0.22em] text-[#d99b16]">Menunggu Konfirmasi</p>
            <p class="mt-3 text-2xl font-black text-[#d99b16]">{{ $formatRupiah($pendingWithdrawalTotal) }}</p>
        </div>
    </div>

    <section class="mt-8 rounded-[2rem] border-4 border-white bg-white p-5 shadow-xl shadow-[#bde0fe]/25 sm:p-7">
        <h2 class="text-2xl font-black text-[#2b3a67]">Konfirmasi Penarikan</h2>
        <div class="mt-5 space-y-4">
            @forelse($pendingWithdrawals as $withdrawal)
                <div class="rounded-[1.75rem] border-4 border-[#f4f9ff] bg-[#fbfdff] p-5">
                    <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <p class="text-lg font-black text-[#2b3a67]">{{ $withdrawal->user?->name ?? '-' }}</p>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $withdrawal->user?->email ?? '-' }}</p>
                            <p class="mt-3 text-xl font-black text-[#d99b16]">{{ $formatRupiah($withdrawal->amount) }}</p>
                            <p class="mt-1 text-xs font-bold text-[#5a76c8]">{{ $withdrawal->payout_method ?: '-' }} - {{ $withdrawal->payout_account ?: '-' }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.resellers.withdrawals.update', $withdrawal) }}" class="grid gap-3 sm:grid-cols-2 lg:w-72">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="notes" value="">
                            <button type="submit" name="status" value="approved" class="rounded-full border-4 border-white bg-[#4bc6b9] px-5 py-3 text-sm font-black text-white shadow-lg shadow-[#4bc6b9]/25 transition active:scale-95">Setujui</button>
                            <button type="submit" name="status" value="rejected" class="rounded-full border-4 border-white bg-[#ffe5e5] px-5 py-3 text-sm font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/15 transition active:scale-95">Tolak</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="rounded-[1.75rem] border-4 border-dashed border-[#d7ecff] bg-[#f8fbff] p-10 text-center text-sm font-black text-[#5a76c8]">Belum ada penarikan komisi yang menunggu konfirmasi.</p>
            @endforelse
        </div>
    </section>

    <section class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-[2rem] border-4 border-white bg-white p-5 shadow-xl shadow-[#bde0fe]/25 sm:p-7">
            <h2 class="text-2xl font-black text-[#2b3a67]">Komisi Masuk Terbaru</h2>
            <div class="mt-5 space-y-3">
                @forelse($incomeCommissions as $commission)
                    <div class="rounded-[1.5rem] bg-[#f8fbff] p-4">
                        <p class="text-sm font-black text-[#2b3a67]">{{ $commission->user?->name ?? '-' }}</p>
                        <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $commission->product_name ?: '-' }}</p>
                        <p class="mt-2 text-sm font-black text-[#4bc6b9]">+ {{ $formatRupiah($commission->amount) }}</p>
                    </div>
                @empty
                    <p class="rounded-[1.5rem] bg-[#f8fbff] p-8 text-center text-sm font-black text-[#5a76c8]">Belum ada komisi masuk.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-[2rem] border-4 border-white bg-white p-5 shadow-xl shadow-[#bde0fe]/25 sm:p-7">
            <h2 class="text-2xl font-black text-[#2b3a67]">Riwayat Penarikan</h2>
            <div class="mt-5 space-y-3">
                @forelse($withdrawalHistories as $history)
                    <div class="flex flex-col gap-3 rounded-[1.5rem] bg-[#f8fbff] p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-black text-[#2b3a67]">{{ $history->user?->name ?? '-' }}</p>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $history->created_at?->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-widest {{ $statusClass[$history->status] ?? 'bg-[#f4f9ff] text-[#5a76c8]' }}">{{ $history->display_status }}</span>
                            <p class="text-sm font-black text-[#ff6b6b]">- {{ $formatRupiah($history->amount) }}</p>
                        </div>
                    </div>
                @empty
                    <p class="rounded-[1.5rem] bg-[#f8fbff] p-8 text-center text-sm font-black text-[#5a76c8]">Riwayat penarikan masih kosong.</p>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
