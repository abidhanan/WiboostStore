@extends('layouts.admin')

@section('title', 'Tambah Refund')
@section('admin_header_subtitle', 'Pilih transaksi yang akan direfund ke saldo user.')

@section('content')
<div class="wiboost-font relative z-10 mx-auto max-w-7xl pb-12">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.refunds.index'),
        'badge' => 'Refund',
        'title' => 'Tambah Refund',
        'description' => 'Pilih transaksi dari tabel. Saldo user akan otomatis dikembalikan.',
    ])

    @if ($errors->any())
        <div class="mb-8 flex items-start gap-4 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-6 py-5 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20">
            <span class="mt-1 shrink-0">@include('partials.wiboost-icon', ['name' => 'alert', 'class' => 'h-7 w-7'])</span>
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

    <div class="mb-6 rounded-[2rem] border-4 border-white bg-white/95 p-4 shadow-xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem] sm:p-5">
        <form action="{{ route('admin.refunds.create') }}" method="GET" class="flex w-full flex-col gap-3 md:flex-row">
            @if(request('return_to'))
                <input type="hidden" name="return_to" value="{{ request('return_to') }}">
            @endif
            <div class="flex min-w-0 flex-1 gap-2">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    class="min-w-0 flex-1 truncate rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:rounded-[1.5rem] sm:px-6 sm:py-4 sm:text-sm"
                    placeholder="Cari invoice, pelanggan, atau produk...">
                <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.2rem] border-4 border-white bg-[#5a76c8] text-white shadow-md transition-transform active:scale-95 sm:h-14 sm:w-14 sm:rounded-[1.5rem]" title="Cari">
                    @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                </button>
                @if($search !== '')
                    <a href="{{ route('admin.refunds.create', array_filter(['return_to' => request('return_to')])) }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.2rem] border-4 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-md transition-transform active:scale-95 sm:h-14 sm:w-14 sm:rounded-[1.5rem]" title="Reset">
                        @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-[2rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem]">
        <div class="w-full overflow-x-auto table-scroll pb-2">
            <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                <table class="w-full text-left" style="min-width: 1470px; table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="width: 190px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Invoice</th>
                            <th style="width: 150px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu</th>
                            <th style="width: 190px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama</th>
                            <th style="width: 240px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Email</th>
                            <th style="width: 360px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Produk</th>
                            <th style="width: 130px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Harga</th>
                            <th style="width: 120px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Metode</th>
                            <th style="width: 190px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($transactions as $trx)
                            @php
                                $productName = $trx->display_product_name;
                                $methodDisplay = $trx->display_payment_method;
                                $methodClass = match ($methodDisplay) {
                                    'SALDO' => 'bg-[#e0fbfc] text-[#5a76c8]',
                                    'BELUM PILIH' => 'bg-[#fff5eb] text-amber-500',
                                    default => 'bg-[#f4f9ff] text-[#8faaf3]',
                                };
                            @endphp
                            <tr class="transition-colors hover:bg-[#f8faff]">
                                <td class="px-5 py-5 align-middle">
                                    <p class="inline-block whitespace-nowrap rounded-md border border-white bg-[#f4f9ff] px-2 py-1 text-xs font-black text-[#5a76c8] sm:text-sm">{{ $trx->invoice_number ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <p class="whitespace-nowrap text-[9px] font-bold text-[#8faaf3] sm:text-[10px]">{{ $trx->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <p class="line-clamp-2 text-sm font-black leading-snug text-[#2b3a67]">{{ $trx->user?->name ?? 'Guest' }}</p>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <p class="truncate text-[10px] font-bold text-[#8faaf3]">{{ $trx->user?->email ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <p class="text-xs font-black leading-snug text-[#2b3a67]" style="display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden;overflow-wrap:anywhere;" title="{{ $productName }}">{{ $productName }}</p>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <p class="whitespace-nowrap text-sm font-black text-[#4bc6b9]">Rp {{ number_format((int) $trx->display_total_payable, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-5 py-5 text-center align-middle">
                                    <span class="inline-flex whitespace-nowrap rounded-full border-2 border-white px-3 py-1.5 text-[9px] font-black uppercase tracking-widest shadow-sm {{ $methodClass }}">
                                        {{ $methodDisplay }}
                                    </span>
                                </td>
                                <td class="px-5 py-5 text-center align-middle">
                                    <form action="{{ route('admin.refunds.store') }}" method="POST"
                                          x-data="{ open: false, percent: '100', opts: ['25','50','75','100'], coords: { top: 0, left: 0, width: 0 },
                                                    place() { const r = $refs.trigger.getBoundingClientRect(); this.coords = { top: r.bottom + 6, left: r.left, width: r.width }; },
                                                    toggle() { this.open = ! this.open; if (this.open) this.$nextTick(() => this.place()); },
                                                    pick(v) { this.percent = v; this.open = false; } }"
                                          @click.outside="open = false"
                                          @keydown.escape.window="open = false"
                                          @scroll.window="open = false"
                                          class="inline-flex items-center justify-center gap-2"
                                          onsubmit="return confirm('Tambah refund ' + this.refund_percent.value + '% untuk transaksi {{ $trx->invoice_number }}?')">
                                        @csrf
                                        <input type="hidden" name="transaction_id" value="{{ $trx->id }}">
                                        @if(request('return_to'))
                                            <input type="hidden" name="return_to" value="{{ request('return_to') }}">
                                        @endif
                                        <input type="hidden" name="refund_percent" :value="percent">

                                        <div class="relative">
                                            <button type="button" x-ref="trigger" @click="toggle()" title="Persentase refund"
                                                    class="flex h-10 min-w-[5.6rem] items-center justify-between gap-2 rounded-full border-2 border-white bg-[#f4f9ff] px-4 text-xs font-black text-[#5a76c8] shadow-inner outline-none transition hover:bg-[#eef4ff] focus:border-[#bde0fe]">
                                                <span x-text="percent + '%'">100%</span>
                                                <svg class="h-4 w-4 shrink-0 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                                </svg>
                                            </button>

                                            <template x-teleport="body">
                                                <div x-show="open" x-cloak
                                                     x-transition:enter="transition ease-out duration-150"
                                                     x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                                     x-transition:leave="transition ease-in duration-100"
                                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                                     x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                                                     :style="`position: fixed; top: ${coords.top}px; left: ${coords.left}px; min-width: max(${coords.width}px, 9rem); z-index: 9999;`"
                                                     class="rounded-[1.5rem] border-4 border-white bg-white p-2 shadow-2xl shadow-[#bde0fe]/50"
                                                     style="font-family: 'Nunito', sans-serif;">
                                                    <div class="flex flex-col gap-1">
                                                        <template x-for="opt in opts" :key="opt">
                                                            <button type="button" @click="pick(opt)"
                                                                    class="flex w-full items-center justify-between gap-3 rounded-full px-4 py-2.5 text-xs font-black transition"
                                                                    :class="percent === opt ? 'bg-[#5a76c8] text-white shadow-md shadow-[#5a76c8]/30' : 'text-[#5a76c8] hover:bg-[#f4f9ff]'">
                                                                <span x-text="opt + '%'"></span>
                                                                <svg x-show="percent === opt" class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>

                                        <button type="submit" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-white bg-white text-[#5a76c8] shadow-md shadow-[#5a76c8]/20 transition-transform hover:scale-110 hover:bg-[#eef4ff] active:scale-95" title="Tambah refund">
                                            @include('partials.wiboost-icon', ['name' => 'plus', 'class' => 'h-4 w-4'])
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center">
                                    <div class="mx-auto max-w-md rounded-[2rem] border-4 border-dashed border-[#e5edf9] bg-[#f8fbff] p-8">
                                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-[1.5rem] border-4 border-white bg-[#e0fbfc] text-[#5a76c8] shadow-sm">
                                            @include('partials.wiboost-icon', ['name' => 'refund', 'class' => 'h-8 w-8'])
                                        </div>
                                        <p class="text-xl font-black text-[#5a76c8]">Belum ada transaksi.</p>
                                        <p class="mt-2 text-sm font-bold text-[#8faaf3]">Transaksi yang tampil hanya yang sudah dibayar dan belum pernah dicatat refund.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($transactions->hasPages())
            <div class="p-5 sm:p-8 w-full min-w-0">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
