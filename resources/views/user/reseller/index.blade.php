@extends('layouts.user')

@section('title', 'Reseller - Wiboost Store')

@section('content')
@php
    $formatRupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $commissionRates = [
        ['name' => 'Suntik Sosmed', 'rate' => '10%'],
        ['name' => 'Top Up Game', 'rate' => '2%'],
        ['name' => 'Paket Data', 'rate' => '2%'],
        ['name' => 'Aplikasi Premium', 'rate' => '10%'],
    ];
    $eWalletMethods = ['DANA', 'OVO', 'GoPay', 'ShopeePay'];
    $resellerUserName = auth()->user()?->short_name ?? auth()->user()?->name ?? 'Reseller';
@endphp

<style>
    .reseller-faq[open] .reseller-faq-arrow {
        transform: rotate(180deg);
    }

    .reseller-faq > summary {
        list-style: none;
    }

    .reseller-faq > summary::-webkit-details-marker {
        display: none;
    }

    .reseller-scroll-panel {
        flex: 1 1 auto;
        max-height: 28rem;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: auto;
        scrollbar-width: none;
    }

    .reseller-scroll-panel::-webkit-scrollbar {
        display: none;
    }

    /* Deskripsi header reseller: membungkus & ukuran sama seperti deskripsi header member (tidak terpotong). */
    .reseller-lead {
        display: block;
        max-width: 44rem;
        white-space: normal;
        overflow-wrap: anywhere;
        font-size: clamp(0.82rem, 3.8vw, 1rem);
        line-height: 1.35;
    }
</style>

<section class="space-y-8">
    @if($isReseller)
        <header class="relative mb-8 flex flex-col items-start gap-6 overflow-hidden rounded-[2.5rem] border-4 border-white p-8 shadow-xl shadow-[#f5d36b]/35 md:p-10" style="background: linear-gradient(135deg, #ffe082 0%, #fff0b3 48%, #fffdf2 100%);">
            <div class="relative z-10 min-w-0">
                <div class="mb-3 inline-block rounded-full border-2 border-white bg-white px-4 py-1 text-xs font-black shadow-sm" style="color:#d99b16 !important;">
                    Dashboard Reseller
                </div>
                <h1 class="wiboost-dashboard-greeting font-black text-[#2b3a67] mb-2 drop-shadow-sm" data-fit-single-line>
                    Halo, {{ $resellerUserName }}!
                    <span class="inline-flex align-[-0.18em]" style="color:#d99b16;">
                        @include('partials.wiboost-icon', ['name' => 'sparkles', 'class' => 'h-8 w-8'])
                    </span>
                </h1>
                <p class="reseller-lead font-extrabold" style="color:#d99b16 !important;">Lakukan transaksi dan dapatkan komisi!</p>
            </div>
        </header>
    @else
        <header class="relative mb-8 flex flex-col items-start gap-6 overflow-hidden rounded-[2.5rem] border-4 border-white p-8 shadow-xl shadow-[#f5d36b]/35 md:p-10" style="background: linear-gradient(135deg, #ffe082 0%, #fff0b3 48%, #fffdf2 100%);">
            <span class="mb-3 inline-block rounded-full border-2 border-white bg-white px-4 py-1 text-xs font-black shadow-sm" style="color:#d99b16 !important;">Reseller</span>
            <div class="min-w-0">
                <h1 class="wiboost-dashboard-greeting mb-2 font-black leading-tight text-[#2b3a67] drop-shadow-sm" style="font-size: clamp(1.45rem, 7vw, 3rem); line-height: 1.08;">
                    Yuk Jadi Reseller!
                    <span class="inline-flex align-middle" style="color:#d99b16; transform: translateY(-0.06em);">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 10.5V8A3.5 3.5 0 0 1 12 4.5 3.5 3.5 0 0 1 15.5 8v2.5M5.5 10.5h13l-1 9h-11l-1-9ZM9 14h6" />
                        </svg>
                    </span>
                </h1>
                <p class="reseller-lead font-extrabold" style="color:#d99b16 !important;">
                    Promosikan produk dan layanan lalu dapatkan komisi setiap transaksi sukses.
                </p>
            </div>
        </header>
    @endif

    @if(session('error'))
        <div class="rounded-[1.5rem] border-4 border-[#ffe5e5] bg-[#fff3f3] px-6 py-4 text-sm font-black text-[#ff6b6b] shadow-sm">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="mb-8 flex items-center gap-4 rounded-[2.5rem] border-4 border-white bg-[#e6fff7] px-6 py-5 font-black text-emerald-500 shadow-lg shadow-emerald-100/50">
            <span class="drop-shadow-sm">@include('partials.wiboost-icon', ['name' => 'success', 'class' => 'h-8 w-8'])</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @unless($isReseller)
        <div class="grid gap-6">
            <div class="grid gap-6">
                <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/25 sm:p-8">
                    <h2 class="text-2xl font-black text-[#2b3a67]">Syarat & Ketentuan</h2>
                    <div class="mt-5 grid gap-3 text-sm font-bold leading-relaxed text-[#5a76c8]">
                        <p class="rounded-[1.25rem] bg-[#f4f9ff] px-5 py-4">Reseller tetap bertransaksi seperti biasa dengan harga yang sama di website.</p>
                        <p class="rounded-[1.25rem] bg-[#f4f9ff] px-5 py-4">Komisi otomatis masuk saat status pesanan sukses.</p>
                        <p class="rounded-[1.25rem] bg-[#f4f9ff] px-5 py-4">Penarikan komisi bisa dilakukan setiap hari dengan minimal komisi {{ $formatRupiah($minimumWithdrawal ?? 20000) }}.</p>
                        <p class="rounded-[1.25rem] bg-[#f4f9ff] px-5 py-4">Proses penarikan maksimal 24 jam setelah pengajuan penarikan.</p>
                    </div>
                </div>

                <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/25 sm:p-8">
                    <h2 class="text-2xl font-black text-[#2b3a67]">Komisi per Kategori</h2>
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        @foreach($commissionRates as $rate)
                            <div class="rounded-[1.5rem] border-2 border-white bg-[#f8fbff] p-4 shadow-sm sm:p-5">
                                <p class="text-xs font-black text-[#2b3a67] sm:text-sm">{{ $rate['name'] }}</p>
                                <p class="mt-2 text-2xl font-black text-[#5a76c8] sm:text-3xl">{{ $rate['rate'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <form id="reseller-join-form" method="POST" action="{{ route('user.reseller.join') }}" x-data="{ payoutOpen: false, payoutMethod: @js(old('reseller_payout_method', 'DANA')), methods: @js($eWalletMethods) }" class="overflow-visible rounded-[2rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/25 sm:p-8">
                @csrf
                <h2 class="text-2xl font-black text-[#2b3a67]">Data Penarikan</h2>
                <p class="mt-2 text-sm font-bold text-[#8faaf3]">Isi nomor e-wallet untuk penarikan komisi.</p>

                <label class="mt-6 block text-sm font-black text-[#5a76c8]">Metode Penarikan</label>
                <div class="relative z-20 mt-3" @click.outside="payoutOpen = false">
                    <input type="hidden" name="reseller_payout_method" :value="payoutMethod">
                    <button type="button" @click="payoutOpen = !payoutOpen" class="flex w-full items-center justify-between rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 text-left text-sm font-black text-[#2b3a67] shadow-inner outline-none ring-2 ring-transparent transition focus:ring-[#bde0fe]">
                        <span x-text="payoutMethod || 'Pilih e-wallet'"></span>
                        <svg class="h-5 w-5 shrink-0 text-[#5a76c8] transition-transform duration-200" :style="payoutOpen ? 'transform: rotate(180deg)' : 'transform: rotate(0deg)'" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M5 8l5 5 5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div x-cloak x-show="payoutOpen" x-transition class="absolute left-0 right-0 top-full z-[90] mt-2 overflow-visible rounded-[1.5rem] border-4 border-white bg-white p-2 shadow-2xl shadow-[#8faaf3]/30">
                        <template x-for="method in methods" :key="method">
                            <button type="button" @click="payoutMethod = method; payoutOpen = false" class="wiboost-dropdown-item block w-full rounded-[1rem] border-2 border-white bg-[#f8fbff] px-4 py-3 text-left text-sm font-black text-[#2b3a67] transition hover:bg-[#e0fbfc]">
                                <span x-text="method"></span>
                            </button>
                        </template>
                    </div>
                </div>
                @error('reseller_payout_method')<p class="mt-2 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>@enderror

                <label class="mt-5 block text-sm font-black text-[#5a76c8]">Nomor E-wallet</label>
                <input name="reseller_payout_account" value="{{ old('reseller_payout_account') }}" placeholder="Masukkan nomor e-wallet" inputmode="numeric" pattern="[0-9]*" data-wiboost-number-only class="mt-3 w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 text-sm font-black text-[#2b3a67] shadow-inner outline-none ring-2 ring-transparent focus:ring-[#bde0fe]" required>
                @error('reseller_payout_account')<p class="mt-2 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>@enderror

                <label class="mt-5 block text-sm font-black text-[#5a76c8]">Nama Pemilik E-wallet</label>
                <input name="reseller_payout_name" value="{{ old('reseller_payout_name') }}" placeholder="Masukkan nama pemilik e-wallet" data-wiboost-name-only class="mt-3 w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 text-sm font-black text-[#2b3a67] shadow-inner outline-none ring-2 ring-transparent focus:ring-[#bde0fe]" required>
                @error('reseller_payout_name')<p class="mt-2 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>@enderror

            </form>
            <button type="submit" form="reseller-join-form" class="w-full rounded-full border-4 border-white bg-[#5a76c8] px-6 py-4 text-sm font-black text-white shadow-xl shadow-[#5a76c8]/25 transition active:scale-[0.98]">
                Gabung Reseller
            </button>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="flex items-center gap-5 rounded-[1.75rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/20">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border-2 border-white bg-[#e0fbfc] text-[#4bc6b9] shadow-inner">
                    @include('partials.wiboost-icon', ['name' => 'cash', 'class' => 'h-7 w-7'])
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-[#4bc6b9]">Total Komisi</p>
                    <p class="mt-2 truncate text-3xl font-black text-[#2b3a67]">{{ $formatRupiah($availableCommission ?? 0) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-5 rounded-[1.75rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/20">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border-2 border-white bg-[#eef5ff] text-[#5a76c8] shadow-inner">
                    @include('partials.wiboost-icon', ['name' => 'history', 'class' => 'h-7 w-7'])
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-[#5a76c8]">Proses Penarikan</p>
                    <p class="mt-2 truncate text-3xl font-black text-[#111827]">{{ $formatRupiah($totals['pending_withdrawal']) }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-[1.75rem] border-4 border-white bg-gradient-to-br from-[#fff7d6] via-white to-[#fff4c7] p-5 shadow-lg shadow-[#f4b740]/20">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.25rem] border-4 border-white text-white shadow-md" style="background:#f4b740 !important; color:#ffffff !important; box-shadow:0 14px 28px rgba(244, 183, 64, .28);">
                        @include('partials.wiboost-icon', ['name' => 'reseller', 'class' => 'h-7 w-7'])
                    </span>
                    <div class="min-w-0">
                        <p class="text-xl font-black text-[#2b3a67]">Join Grup Reseller!</p>
                        <p class="mt-1 text-sm font-bold leading-relaxed text-[#5a76c8]">Yuk gabung grup reseller untuk mendapat info khusus reseller.</p>
                    </div>
                </div>
                <a href="https://chat.whatsapp.com/IVkWJNsYqItLoT4GmtM6pL" target="_blank" rel="noopener noreferrer" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-full border-4 border-white bg-[#4bc6b9] px-5 py-3 text-sm font-black text-white shadow-lg shadow-[#4bc6b9]/25 transition hover:-translate-y-0.5 active:scale-95">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center">
                        @include('partials.wiboost-icon', ['name' => 'whatsapp', 'class' => 'block h-[18px] w-[18px]'])
                    </span>
                    Join Grup
                </a>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/25">
                <h2 class="text-2xl font-black text-[#2b3a67]">Penarikan Komisi</h2>

                <form method="POST" action="{{ route('user.reseller.payout.update') }}"
                      x-data="{
                          payoutOpen: false,
                          originalMethod: @js($user->reseller_payout_method ?: 'DANA'),
                          originalAccount: @js($user->reseller_payout_account ?: ''),
                          originalName: @js($user->reseller_payout_name ?: ''),
                          payoutMethod: @js(old('reseller_payout_method', $user->reseller_payout_method ?: 'DANA')),
                          payoutAccount: @js(old('reseller_payout_account', $user->reseller_payout_account ?: '')),
                          payoutName: @js(old('reseller_payout_name', $user->reseller_payout_name ?: '')),
                          methods: @js($eWalletMethods)
                      }"
                      class="mt-5 grid gap-4 rounded-[1.5rem] bg-[#f4f9ff] p-5 md:grid-cols-[1fr_1fr_1fr_auto] md:items-end">
                    @csrf
                    @method('PATCH')

                    <div class="relative z-30 min-w-0" @click.outside="payoutOpen = false">
                        <label class="block text-sm font-black text-[#5a76c8]">Metode</label>
                        <input type="hidden" name="reseller_payout_method" :value="payoutMethod">
                        <button type="button" @click="payoutOpen = !payoutOpen" class="mt-2 flex w-full items-center justify-between rounded-[1.5rem] border-4 border-white bg-white px-4 py-3 text-left text-sm font-black text-[#2b3a67] shadow-inner outline-none ring-2 ring-transparent transition focus:ring-[#bde0fe]">
                            <span x-text="payoutMethod || 'Pilih e-wallet'"></span>
                            <svg class="h-5 w-5 shrink-0 text-[#5a76c8] transition-transform duration-200" :style="payoutOpen ? 'transform: rotate(180deg)' : 'transform: rotate(0deg)'" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M5 8l5 5 5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <div x-cloak x-show="payoutOpen" x-transition class="absolute left-0 right-0 top-full z-[100] mt-2 rounded-[1.25rem] border-4 border-white bg-white p-2 shadow-2xl shadow-[#8faaf3]/30">
                            <template x-for="method in methods" :key="method">
                                <button type="button" @click="payoutMethod = method; payoutOpen = false" class="wiboost-dropdown-item block w-full rounded-[1rem] border-2 border-white bg-[#f8fbff] px-4 py-3 text-left text-sm font-black text-[#2b3a67] transition hover:bg-[#e0fbfc]">
                                    <span x-text="method"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="min-w-0">
                        <label class="block text-sm font-black text-[#5a76c8]">Nomor E-wallet</label>
                        <input name="reseller_payout_account" x-model="payoutAccount" inputmode="numeric" pattern="[0-9]*" data-wiboost-number-only class="mt-2 w-full rounded-[1.5rem] border-4 border-white bg-white px-4 py-3 text-sm font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]" required>
                    </div>

                    <div class="min-w-0">
                        <label class="block text-sm font-black text-[#5a76c8]">Nama Pemilik</label>
                        <input name="reseller_payout_name" x-model="payoutName" data-wiboost-name-only class="mt-2 w-full rounded-[1.5rem] border-4 border-white bg-white px-4 py-3 text-sm font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]" required>
                    </div>

                    <button x-cloak x-show="payoutMethod !== originalMethod || payoutAccount !== originalAccount || payoutName !== originalName"
                            type="submit"
                            class="rounded-full border-4 border-white bg-[#5a76c8] px-5 py-3 text-sm font-black text-white shadow-lg shadow-[#5a76c8]/25 transition active:scale-[0.98]">
                        Simpan
                    </button>
                </form>

                @unless($pendingWithdrawal)
                    <form method="POST" action="{{ route('user.reseller.withdraw') }}" class="mt-5">
                        @csrf
                        <p class="mb-3 text-left text-xs font-black text-amber-500">*Pajak ditanggung reseller</p>
                        <button type="submit"
                                @disabled(! $canWithdrawNow)
                                class="w-full rounded-full border-4 border-white px-6 py-4 text-sm font-black text-white shadow-xl transition active:scale-95 {{ $canWithdrawNow ? 'bg-[#5a76c8] shadow-[#5a76c8]/25' : 'cursor-not-allowed' }}"
                                @unless($canWithdrawNow) style="background:#cbd5e1;box-shadow:0 18px 30px rgba(148, 163, 184, .2);" @endunless>
                            Tarik
                        </button>
                    </form>
                @endunless
            </div>
        </div>

        <div class="grid gap-6">
            <div class="grid gap-6">
                <div class="flex max-h-[36rem] min-h-[20rem] flex-col overflow-hidden rounded-[2rem] border-4 border-white bg-white p-0 shadow-xl shadow-[#bde0fe]/25">
                    <div class="border-b-4 border-dashed border-[#f4f9ff] bg-white px-6 py-5">
                        <h2 class="text-2xl font-black text-[#2b3a67]">Riwayat Komisi</h2>
                    </div>
                    <div class="reseller-scroll-panel space-y-4 bg-[#f8faff]/50 p-5 md:p-6 {{ $commissionHistories->count() === 0 ? 'flex' : '' }}">
                        @forelse($commissionHistories as $history)
                            <div class="flex flex-col gap-3 rounded-[1.5rem] bg-[#f8fbff] p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    @php
                                        $historyProduct = $history->product_name ?: ($history->transaction?->display_product_name ?: null);
                                    @endphp
                                    <p class="text-sm font-black text-[#2b3a67]">{{ $history->display_type }}</p>
                                    <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $history->created_at?->format('d M Y, H:i') }} - {{ $history->display_status }}</p>
                                    @if($history->transaction)
                                        <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $history->transaction->invoice_number }}</p>
                                    @endif
                                    @if($history->type === 'income')
                                        <p class="mt-2 text-xs font-bold leading-snug text-[#5a76c8]">{{ $historyProduct ?: '-' }}</p>
                                    @endif
                                </div>
                                <p @class([
                                    'shrink-0 text-lg font-black',
                                    'text-[#4bc6b9]' => $history->type === 'income',
                                    'text-[#ff6b6b]' => $history->type === 'withdrawal',
                                ])>
                                    {{ $history->type === 'withdrawal' ? '-' : '+' }} {{ $formatRupiah($history->amount) }}
                                </p>
                            </div>
                        @empty
                            <div class="flex min-h-full w-full flex-1 items-center justify-center rounded-[2rem] border-4 border-dashed border-[#bde0fe] bg-white/60 px-6 py-10 text-center">
                                <div>
                                    <div class="mb-3 flex justify-center text-[#5a76c8] opacity-70">@include('partials.wiboost-icon', ['name' => 'cash', 'class' => 'h-12 w-12'])</div>
                                    <p class="text-sm font-black text-[#5a76c8]">Riwayat komisi masih kosong.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <div class="grid gap-6">
                <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/25">
                    <h2 class="text-2xl font-black text-[#2b3a67]">Komisi per Kategori</h2>
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        @foreach($commissionRates as $rate)
                            <div class="rounded-[1.5rem] border-2 border-white bg-[#f8fbff] p-4 shadow-sm sm:p-5">
                                <p class="text-xs font-black text-[#2b3a67] sm:text-sm">{{ $rate['name'] }}</p>
                                <p class="mt-2 text-2xl font-black text-[#5a76c8] sm:text-3xl">{{ $rate['rate'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/25">
                    <h2 class="text-2xl font-black text-[#2b3a67]">FAQ</h2>
                    <div class="mt-5 flex flex-col gap-4">
                        <details class="reseller-faq rounded-[1.5rem] border-4 border-white bg-[#f8fbff] shadow-sm">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5 text-sm font-black text-[#2b3a67]">
                                Apakah reseller perlu ubah harga?
                                <svg class="reseller-faq-arrow h-5 w-5 shrink-0 text-[#5a76c8] transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </summary>
                            <div class="px-6 pb-6 text-sm font-bold leading-relaxed text-[#5a76c8] border-t-2 border-[#f0f5ff] pt-4 mt-1 bg-[#f8fbff] text-left">
                                Tidak. Reseller cukup mempromosikan dengan harga yang sudah ada di website.
                            </div>
                        </details>
                        <details class="reseller-faq rounded-[1.5rem] border-4 border-white bg-[#f8fbff] shadow-sm">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5 text-sm font-black text-[#2b3a67]">
                                Kapan komisi masuk?
                                <svg class="reseller-faq-arrow h-5 w-5 shrink-0 text-[#5a76c8] transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </summary>
                            <div class="px-6 pb-6 text-sm font-bold leading-relaxed text-[#5a76c8] border-t-2 border-[#f0f5ff] pt-4 mt-1 bg-[#f8fbff] text-left">
                                Komisi otomatis masuk saat status transaksi sukses.
                            </div>
                        </details>
                        <details class="reseller-faq rounded-[1.5rem] border-4 border-white bg-[#f8fbff] shadow-sm">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5 text-sm font-black text-[#2b3a67]">
                                Kapan bisa tarik komisi?
                                <svg class="reseller-faq-arrow h-5 w-5 shrink-0 text-[#5a76c8] transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </summary>
                            <div class="px-6 pb-6 text-sm font-bold leading-relaxed text-[#5a76c8] border-t-2 border-[#f0f5ff] pt-4 mt-1 bg-[#f8fbff] text-left">
                                Penarikan bisa dilakukan setiap hari dengan minimal komisi {{ $formatRupiah($minimumWithdrawal ?? 20000) }}.
                            </div>
                        </details>
                        <details class="reseller-faq rounded-[1.5rem] border-4 border-white bg-[#f8fbff] shadow-sm">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5 text-sm font-black text-[#2b3a67]">
                                Berapa lama proses penarikan komisi?
                                <svg class="reseller-faq-arrow h-5 w-5 shrink-0 text-[#5a76c8] transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </summary>
                            <div class="px-6 pb-6 text-sm font-bold leading-relaxed text-[#5a76c8] border-t-2 border-[#f0f5ff] pt-4 mt-1 bg-[#f8fbff] text-left">
                                Maksimal 24 jam.
                            </div>
                        </details>
                    </div>
                </div>

                <div class="rounded-[2rem] border-4 border-[#ffe5e5] bg-[#fff3f3] p-6 shadow-xl shadow-[#ffb3b3]/20">
                    <h2 class="text-2xl font-black text-[#ff6b6b]">Berhenti Menjadi Reseller</h2>
                    <p class="mt-2 text-sm font-bold leading-relaxed text-[#ff9999]">Kamu akan kembali menjadi buyer biasa dan kamu tidak akan menerima komisi lagi.</p>
                    <form method="POST" action="{{ route('user.reseller.leave') }}" onsubmit="return confirm('Yakin ingin berhenti menjadi reseller?')" class="mt-5">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-full border-4 border-white bg-[#ff6b6b] px-8 py-4 text-sm font-black text-white shadow-xl shadow-[#ff6b6b]/30 transition-transform active:scale-95 hover:bg-[#e55050]">
                            Berhenti
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endunless
</section>
@endsection
