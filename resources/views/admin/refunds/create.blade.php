@extends('layouts.admin')

@section('title', 'Tambah Refund')
@section('admin_header_subtitle', 'Pilih transaksi yang akan direfund ke saldo user.')

@section('content')
<div class="wiboost-font relative z-10 mx-auto max-w-5xl pb-12">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.refunds.index'),
        'badge' => 'Refund',
        'title' => 'Tambah Refund',
        'description' => 'Pilih data transaksi yang direfund. Saldo user akan otomatis dikembalikan.',
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

    @php
        $transactionOptions = $transactions->map(function ($transaction) {
            $productName = $transaction->product?->name ?: ($transaction->isMembershipPayment() ? 'Membership Wiboost 30 Hari' : 'Produk Dihapus');

            return [
                'value' => (string) $transaction->id,
                'label' => $transaction->invoice_number . ' - ' . ($transaction->user?->name ?? 'Guest') . ' - ' . $productName . ' - Rp ' . number_format((int) $transaction->display_total_payable, 0, ',', '.'),
            ];
        })->prepend([
            'value' => '',
            'label' => '-- Pilih Transaksi --',
            'disabled' => true,
        ])->values()->all();
    @endphp

    <form action="{{ route('admin.refunds.store') }}" method="POST" class="relative overflow-visible rounded-[2.5rem] border-4 border-white bg-white/95 p-6 shadow-xl shadow-[#bde0fe]/30 md:p-10">
        @csrf

        <div class="grid gap-8">
            <section class="space-y-6">
                <div>
                    <p class="mb-1 text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Data Transaksi</p>
                    <h4 class="text-2xl font-black tracking-tight text-[#2b3a67] md:text-3xl">Pilih Transaksi Refund</h4>
                </div>

                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Transaksi</label>
                    @include('admin.products.partials.theme-select', [
                        'name' => 'transaction_id',
                        'id' => 'transaction_id',
                        'selectedValue' => (string) old('transaction_id', request('transaction_id')),
                        'placeholder' => '-- Pilih Transaksi --',
                        'options' => $transactionOptions,
                        'required' => true,
                    ])
                    <p class="mt-2 ml-2 text-xs font-bold text-[#8faaf3]">Yang tampil hanya transaksi sudah dibayar dan belum pernah dicatat refund.</p>
                </div>
            </section>

            @if($selectedTransaction)
                @php
                    $selectedProductName = $selectedTransaction->product?->name ?: ($selectedTransaction->isMembershipPayment() ? 'Membership Wiboost 30 Hari' : 'Produk Dihapus');
                @endphp
                <section class="rounded-[2rem] border-4 border-white bg-[#f8fbff] p-6 shadow-inner">
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Transaksi Terpilih</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Invoice</p>
                            <p class="mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $selectedTransaction->invoice_number }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Pelanggan</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $selectedTransaction->user?->name ?? 'Guest' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Layanan</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $selectedProductName }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Total Bayar</p>
                            <p class="mt-1 text-sm font-black text-[#4bc6b9]">Rp {{ number_format((int) $selectedTransaction->display_total_payable, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $selectedTransaction->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                        </div>
                    </div>
                </section>
            @endif
        </div>

        <div class="mt-10 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.refunds.index') }}" class="flex items-center justify-center rounded-full border-4 border-white bg-[#f4f9ff] px-7 py-4 text-sm font-black text-[#5a76c8] shadow-sm transition active:scale-95">
                Batal
            </a>
            <button type="submit" class="flex items-center justify-center rounded-full border-4 border-white bg-[#5a76c8] px-7 py-4 text-sm font-black text-white shadow-xl shadow-[#5a76c8]/25 transition active:scale-95">
                Tambah Refund
            </button>
        </div>
    </form>
</div>
@endsection
