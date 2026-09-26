@extends('layouts.admin')

@section('title', 'Edit Refund')
@section('admin_header_subtitle', 'Perbarui transaksi yang direfund ke saldo user.')

@section('content')
@php
    $transaction = $refund->transaction;
    $productName = $transaction?->display_product_name ?? 'Produk Wiboost';
    $transactionOptions = $transactions->map(function ($item) {
        $userName = $item->user?->name ?? 'Guest';

        return [
            'value' => (string) $item->id,
            'label' => $item->invoice_number . ' - ' . $userName . ' - Rp ' . number_format((int) $item->display_total_payable, 0, ',', '.'),
        ];
    })->values()->all();
@endphp

<div class="wiboost-font relative z-10 mx-auto max-w-5xl pb-12">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.refunds.index'),
        'badge' => 'Refund',
        'title' => 'Edit Refund',
        'description' => 'Perbarui transaksi refund. Saldo lama akan ditarik balik lalu saldo transaksi baru dikembalikan.',
    ])

    @if ($errors->any())
        <div class="mb-8 flex items-start gap-4 rounded-[2.5rem] border-4 border-white bg-[#ffe5e5] px-6 py-5 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20">
            <span class="mt-1 shrink-0">@include('partials.wiboost-icon', ['name' => 'alert', 'class' => 'h-8 w-8'])</span>
            <div>
                <p class="mb-3 text-lg">Masih ada data yang perlu diperbaiki:</p>
                <ul class="list-inside list-disc rounded-2xl border border-white bg-white/50 p-4 text-sm font-bold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.refunds.update', $refund) }}" method="POST" class="relative overflow-visible rounded-[2.5rem] border-4 border-white bg-white/95 p-6 shadow-xl shadow-[#bde0fe]/30 md:p-10">
        @csrf
        @method('PATCH')

        <div class="grid gap-8">
            <section class="rounded-[2rem] border-4 border-white bg-[#f8fbff] p-6 shadow-inner">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Refund Saat Ini</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Invoice</p>
                        <p class="mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $transaction?->invoice_number ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Pelanggan</p>
                        <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $transaction?->user?->name ?? 'Guest' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Produk</p>
                        <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $productName }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Total Refund</p>
                        <p class="mt-1 text-sm font-black text-[#4bc6b9]">Rp {{ number_format((int) ($transaction?->display_total_payable ?? 0), 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status Pesanan</p>
                        <p class="mt-1 text-sm font-black uppercase text-[#5a76c8]">{{ $transaction?->display_status ?? '-' }}</p>
                    </div>
                </div>
            </section>

            <section class="space-y-3">
                <label class="ml-2 block text-sm font-black text-[#5a76c8]">Ganti Transaksi Refund</label>
                @include('admin.products.partials.theme-select', [
                    'name' => 'transaction_id',
                    'id' => 'transaction_id',
                    'selectedValue' => (string) old('transaction_id', $refund->transaction_id),
                    'placeholder' => '-- Pilih Transaksi --',
                    'options' => $transactionOptions,
                    'required' => true,
                ])
            </section>
        </div>

        <div class="mt-10 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.refunds.index') }}" class="flex items-center justify-center rounded-full border-4 border-white bg-[#f4f9ff] px-7 py-4 text-sm font-black text-[#5a76c8] shadow-sm transition active:scale-95">
                Batal
            </a>
            <button type="submit" class="flex items-center justify-center rounded-full border-4 border-white bg-[#5a76c8] px-7 py-4 text-sm font-black text-white shadow-xl shadow-[#5a76c8]/25 transition active:scale-95">
                Simpan Refund
            </button>
        </div>
    </form>
</div>
@endsection
