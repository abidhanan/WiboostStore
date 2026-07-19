@extends('layouts.admin')

@section('title', 'Refund')
@section('admin_header_subtitle', 'Catat refund transaksi ke saldo user.')

@section('content')
@php
    $searchValue = $search ?? request('search');
    $selectedMonth = $selectedMonth ?? request('month', date('m'));
    $selectedYear = $selectedYear ?? request('year', date('Y'));
    $adminMonthNames = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];
    $selectedMonthNumber = (int) $selectedMonth;
@endphp
<style>
    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; border: 2px solid #f0f5ff; }
    .table-scroll::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
    .admin-detail-modal {
        display: none;
        background: rgba(43, 58, 103, .58) !important;
        -webkit-backdrop-filter: blur(8px);
        backdrop-filter: blur(8px);
        overflow: hidden;
        overscroll-behavior: contain;
        top: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        left: 0 !important;
        align-items: flex-start;
        justify-content: center;
        padding: 7rem .85rem 1rem;
    }
    @media (min-width: 640px) {
        .admin-detail-modal { padding: 7.25rem 1.5rem 1.25rem; }
    }
    @media (min-width: 1280px) {
        .admin-detail-modal {
            left: 0 !important;
            padding-top: 7.5rem;
            padding-left: 26rem;
            padding-right: 2rem;
        }
    }
    .admin-detail-modal.modal-active { display: flex; opacity: 1 !important; pointer-events: auto !important; }
    .admin-detail-modal.modal-active .admin-detail-card { transform: scale(1) !important; }
    .admin-detail-card {
        border-radius: 1.75rem !important;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        width: min(100%, 52rem);
        max-height: calc(100dvh - 9rem) !important;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    @media (max-width: 639px) {
        .admin-detail-modal { padding-top: 8rem; }
        .admin-detail-card { max-height: calc(100dvh - 9.5rem) !important; }
    }
    .admin-detail-card * { min-width: 0; }
    .admin-detail-header {
        border-radius: 0;
        border: 0;
        border-bottom: 3px dashed #f4f9ff;
        background: transparent;
        box-shadow: none;
        padding: 0 0 .85rem 0;
    }
    .admin-detail-body {
        min-height: 0;
        flex: 1 1 auto;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        padding-right: .9rem;
        padding-bottom: .25rem;
        scrollbar-color: #bde0fe transparent;
        scrollbar-width: thin;
        scrollbar-gutter: stable both-edges;
    }
    .admin-detail-body::-webkit-scrollbar { width: 8px; }
    .admin-detail-body::-webkit-scrollbar-track { background: transparent; border-radius: 999px; margin: .75rem 0; }
    .admin-detail-body::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #d8ecff, #8faaf3);
        border: 2px solid #ffffff;
        border-radius: 999px;
    }
    .admin-detail-panel {
        border-radius: 1.2rem;
        border: 1px solid #eaf2ff;
        background: #f8faff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.9);
        padding: .85rem 1rem;
        overflow: hidden;
    }
    .admin-detail-panel-soft {
        border-color: #fff1d6;
        background: #fffdf7;
    }
    .admin-theme-select-month,
    .admin-theme-select-year {
        flex: 1 1 0;
        width: auto;
    }
    @media (min-width: 768px) {
        .admin-theme-select-month { width: 9rem; flex: 0 0 9rem; }
        .admin-theme-select-year { width: 7rem; flex: 0 0 7rem; }
    }
</style>

<div class="wiboost-font relative z-10 w-full min-w-0 pb-12">
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-sm font-black text-emerald-500 shadow-lg shadow-emerald-100/50 sm:px-6 sm:text-base">
            @include('partials.wiboost-icon', ['name' => 'success', 'class' => 'h-6 w-6'])
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="relative z-10 mb-8 w-full min-w-0 rounded-[2rem] border-4 border-white bg-white/90 p-4 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:rounded-[2.5rem] sm:p-5">
        <form action="{{ route('admin.refunds.index') }}" method="GET" class="flex w-full flex-col gap-3">
            <div class="flex w-full flex-col gap-3 md:flex-row">
            <div class="flex w-full min-w-0 flex-1 gap-2">
                <input type="hidden" name="month" value="{{ $selectedMonth }}">
                <input type="hidden" name="year" value="{{ $selectedYear }}">
                <input type="text" name="search" value="{{ $searchValue }}"
                       class="min-w-0 flex-1 truncate rounded-[1rem] border-4 border-white bg-[#f4f9ff] px-4 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:rounded-[1.2rem] sm:px-5 sm:text-sm"
                       placeholder="Cari invoice, pelanggan, atau produk...">
                <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-white shadow-md transition-transform active:scale-95 sm:rounded-[1.2rem]" title="Cari Refund">
                    @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                </button>
                @if($searchValue)
                    <a href="{{ route('admin.refunds.index', ['month' => $selectedMonth, 'year' => $selectedYear]) }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-md transition-transform active:scale-95 sm:rounded-[1.2rem]" title="Reset">
                        @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                    </a>
                @endif
            </div>

            <a href="{{ route('admin.refunds.create') }}" class="flex h-12 w-full shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#5a76c8] px-6 py-3 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 md:w-auto sm:rounded-[1.2rem]">
                @include('partials.wiboost-icon', ['name' => 'plus', 'class' => 'h-5 w-5'])
                <span class="whitespace-nowrap">Tambah Refund</span>
            </a>
            </div>

            <div class="flex w-full items-center justify-start gap-2 border-t-4 border-dashed border-[#f4f9ff] pt-4 md:justify-end md:border-t-0 md:pt-0">
                <div class="admin-theme-select admin-theme-select-month" data-admin-theme-select data-submit-on-change="true" data-input-name="month">
                    <button type="button" class="admin-theme-select-button rounded-[1rem] px-4 py-2 text-xs font-black sm:text-sm">
                        <span data-admin-theme-select-label>{{ $adminMonthNames[$selectedMonthNumber] ?? '-' }}</span>
                        <span class="admin-theme-select-arrow">v</span>
                    </button>
                    <div class="admin-theme-select-menu">
                        @for($m = 1; $m <= 12; $m++)
                            <button type="button" class="admin-theme-select-option {{ (string) $selectedMonth === sprintf('%02d', $m) ? 'is-selected' : '' }}" data-value="{{ sprintf('%02d', $m) }}" data-label="{{ $adminMonthNames[$m] }}">{{ $adminMonthNames[$m] }}</button>
                        @endfor
                    </div>
                </div>
                <div class="admin-theme-select admin-theme-select-year" data-admin-theme-select data-submit-on-change="true" data-input-name="year">
                    <button type="button" class="admin-theme-select-button rounded-[1rem] px-4 py-2 text-xs font-black sm:text-sm">
                        <span data-admin-theme-select-label>{{ $selectedYear }}</span>
                        <span class="admin-theme-select-arrow">v</span>
                    </button>
                    <div class="admin-theme-select-menu">
                        @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                            <button type="button" class="admin-theme-select-option {{ (string) $selectedYear === (string) $y ? 'is-selected' : '' }}" data-value="{{ $y }}" data-label="{{ $y }}">{{ $y }}</button>
                        @endfor
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-visible rounded-[2rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem]">
        <div class="w-full overflow-x-auto table-scroll pb-2">
            <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                <table class="w-full text-left" style="min-width:1210px;table-layout:fixed;">
                    <thead>
                        <tr>
                            <th style="width:190px;font-size:10px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3]">Invoice</th>
                            <th style="width:160px;font-size:10px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3]">Waktu Refund</th>
                            <th style="width:190px;font-size:10px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3]">Nama</th>
                            <th style="width:240px;font-size:10px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3]">Email</th>
                            <th style="width:360px;font-size:10px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3]">Layanan</th>
                            <th style="width:130px;font-size:10px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3]">Harga</th>
                            <th style="width:160px;font-size:10px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center font-black uppercase tracking-widest text-[#8faaf3]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($refunds as $refund)
                            @php
                                $trx = $refund->transaction;
                                $productName = $trx?->product?->name ?: ($trx?->isMembershipPayment() ? 'Membership Wiboost 30 Hari' : 'Produk Dihapus');
                                $categoryName = collect([
                                    $trx?->product?->category?->parent?->parent?->parent?->name,
                                    $trx?->product?->category?->parent?->parent?->name,
                                    $trx?->product?->category?->parent?->name,
                                    $trx?->product?->category?->name,
                                ])->filter()->implode(' / ');
                            @endphp
                            <tr onclick="openAdminRefundModal('admin-refund-detail-{{ $refund->id }}')" class="cursor-pointer transition-colors hover:bg-[#f8faff]">
                                <td class="px-5 py-5 align-middle">
                                    <p class="inline-block whitespace-nowrap rounded-md border border-white bg-[#f4f9ff] px-2 py-1 text-xs font-black text-[#5a76c8] sm:text-sm">{{ $trx?->invoice_number ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <p class="whitespace-nowrap text-[10px] font-bold text-[#8faaf3]">{{ $refund->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <p class="admin-clamp-2 text-xs font-black leading-snug text-[#2b3a67] sm:text-sm">{{ $trx?->user?->name ?? 'Guest' }}</p>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <p class="whitespace-nowrap text-[10px] font-bold text-[#8faaf3]">{{ $trx?->user?->email ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <p class="text-[11px] font-black leading-tight text-[#2b3a67] sm:text-xs" style="display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden;text-overflow:clip;white-space:normal;word-break:normal;overflow-wrap:anywhere;" title="{{ $productName }}">{{ $productName }}</p>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <p class="whitespace-nowrap text-xs font-black text-[#4bc6b9] sm:text-sm">Rp {{ number_format((int) ($trx?->display_total_payable ?? 0), 0, ',', '.') }}</p>
                                </td>
                                <td class="px-5 py-5 text-center align-middle">
                                    <div class="flex items-center justify-center gap-2">
                                        <form action="{{ route('admin.refunds.destroy', $refund) }}" method="POST" onclick="event.stopPropagation()" onsubmit="return confirm('Hapus data refund ini?')" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-[1rem] border-2 border-white bg-white text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform hover:scale-110 hover:bg-[#ffe5e5] active:scale-95" title="Hapus Refund">
                                                @include('partials.wiboost-icon', ['name' => 'trash', 'class' => 'h-4 w-4'])
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-20 text-center">
                                    <div class="mb-5 inline-flex h-24 w-24 items-center justify-center rounded-[2.5rem] border-4 border-white bg-[#f0f5ff] text-[#5a76c8] shadow-inner">
                                        @include('partials.wiboost-icon', ['name' => 'receipt', 'class' => 'h-10 w-10'])
                                    </div>
                                    <p class="text-xl font-black text-[#5a76c8]">Belum ada refund.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @foreach($refunds as $refund)
            @php
                $trx = $refund->transaction;
                $productName = $trx?->product?->name ?: ($trx?->isMembershipPayment() ? 'Membership Wiboost 30 Hari' : 'Produk Dihapus');
                $categoryName = collect([
                    $trx?->product?->category?->parent?->parent?->parent?->name,
                    $trx?->product?->category?->parent?->parent?->name,
                    $trx?->product?->category?->parent?->name,
                    $trx?->product?->category?->name,
                ])->filter()->implode(' / ');
                $detailOrderData = $trx ? \App\Support\WiboostOrderDetailData::forTransaction($trx) : [];
                $detailLoginProofUrl = $trx?->login_proof_path ? url(\Illuminate\Support\Facades\Storage::url($trx->login_proof_path)) : null;
                $detailCredentials = is_array($trx?->credential_data)
                    ? $trx->credential_data
                    : (json_decode((string) $trx?->credential_data, true) ?: []);
                $detailIsFilledValue = function ($value): bool {
                    if ($value === null || is_array($value)) {
                        return false;
                    }

                    $value = trim((string) $value);

                    return ! in_array(strtolower($value), ['', '-', "\xE2\x80\x94", 'n/a', 'na', 'null', 'undefined', '[]', '{}', 'tidak ada', 'belum ada', 'belum tersedia'], true);
                };
                $detailAppendLine = function (array &$lines, string $label, $value) use ($detailIsFilledValue): void {
                    if (! $detailIsFilledValue($value)) {
                        return;
                    }

                    $lines[] = ['label' => $label, 'value' => trim((string) $value)];
                };
                $detailParseTextLines = function ($text) use ($detailAppendLine): array {
                    $lines = [];

                    foreach (preg_split('/\R/', trim((string) $text)) ?: [] as $line) {
                        $line = trim((string) $line);

                        if ($line === '') {
                            continue;
                        }

                        if (str_contains($line, ':')) {
                            [$label, $value] = array_map('trim', explode(':', $line, 2));
                            $detailAppendLine($lines, $label !== '' ? $label : 'Data', $value);
                        } else {
                            $detailAppendLine($lines, 'Data', $line);
                        }
                    }

                    return $lines;
                };
                $detailRelatedSections = [];

                $detailUserLines = [];
                $detailAppendLine($detailUserLines, 'Nama', $trx?->user?->name);
                $detailAppendLine($detailUserLines, 'Email', $trx?->user?->email);
                $detailAppendLine($detailUserLines, 'WhatsApp', $trx?->user?->whatsapp);

                if ($detailUserLines !== []) {
                    $detailRelatedSections[] = ['title' => 'Data Pengguna', 'lines' => $detailUserLines];
                }

                $detailOrderLines = [];
                $detailAppendLine($detailOrderLines, 'Invoice', $detailOrderData['invoice'] ?? $trx?->invoice_number);
                $detailAppendLine($detailOrderLines, 'Kategori', $detailOrderData['category'] ?? $categoryName);
                $detailAppendLine($detailOrderLines, 'Produk', $detailOrderData['product'] ?? $productName);
                $detailAppendLine($detailOrderLines, 'Total Bayar', $detailOrderData['total'] ?? null);
                $detailAppendLine($detailOrderLines, 'Metode', $detailOrderData['payment'] ?? $trx?->display_payment_method);
                $detailAppendLine($detailOrderLines, 'Status Pesanan', $detailOrderData['status'] ?? $trx?->display_status);
                $detailAppendLine($detailOrderLines, 'ID Provider', $detailOrderData['provider_id'] ?? $trx?->provider_order_id);
                $detailAppendLine($detailOrderLines, 'Waktu', $detailOrderData['time'] ?? $trx?->created_at?->format('d M Y, H:i'));

                if ($detailOrderLines !== []) {
                    $detailRelatedSections[] = ['title' => 'Data Pesanan', 'lines' => $detailOrderLines];
                }

                $detailAccountLines = [];
                foreach ([
                    'email' => 'Email',
                    'password' => 'Password',
                    'profile' => 'Profil',
                    'pin' => 'PIN',
                    'link' => 'Link Akses',
                    'tutorial_link' => 'Link Tutorial',
                ] as $credentialKey => $credentialLabel) {
                    $detailAppendLine($detailAccountLines, $credentialLabel, $detailCredentials[$credentialKey] ?? null);
                }

                if ($detailAccountLines !== []) {
                    $detailRelatedSections[] = ['title' => 'Detail Akun', 'lines' => $detailAccountLines];
                }

                if ($detailLoginProofUrl) {
                    $detailRelatedSections[] = ['title' => 'Bukti Login', 'lines' => [['label' => 'Link Bukti Login', 'value' => $detailLoginProofUrl]]];
                }

                $detailInputBuyerLines = $detailParseTextLines($detailOrderData['input_text'] ?? ($trx?->isMembershipPayment() ? '-' : ($trx?->order_input_text ?: ($trx?->target_data ?: '-'))));

                if ($detailInputBuyerLines !== []) {
                    $detailRelatedSections[] = ['title' => 'Input Pembeli', 'lines' => $detailInputBuyerLines];
                }
            @endphp
            <div id="admin-refund-detail-{{ $refund->id }}" class="admin-detail-modal fixed inset-0 z-[9999] opacity-0 pointer-events-none transition-all duration-200">
                <div class="absolute inset-0" onclick="closeAdminRefundModal('admin-refund-detail-{{ $refund->id }}')"></div>
                <div class="admin-detail-card relative flex w-full max-w-3xl flex-col rounded-[1.75rem] border-4 border-white bg-white p-4 shadow-2xl shadow-[#2b3a67]/20 transform scale-95 transition-transform duration-200 sm:p-5">
                    <div class="admin-detail-header mb-3.5 flex items-start justify-between gap-3 p-3.5">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Detail Refund</p>
                            <h3 class="mt-1 break-all text-xl font-black text-[#2b3a67]">{{ $trx?->invoice_number ?? '-' }}</h3>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $refund->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                        </div>
                        <button type="button" onclick="closeAdminRefundModal('admin-refund-detail-{{ $refund->id }}')" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#ffe5e5] text-[#ff6b6b]">
                            @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-4 w-4'])
                        </button>
                    </div>

                    <div class="admin-detail-body">
                        <div class="grid gap-3.5 md:grid-cols-2">
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Pelanggan</p>
                                <p class="mt-1 font-black text-[#2b3a67]">{{ $trx?->user?->name ?? 'Guest' }}</p>
                                <p class="mt-1 break-all text-xs font-bold text-[#8faaf3]">{{ $trx?->user?->email ?? '-' }}</p>
                            </div>
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nominal Refund</p>
                                <p class="mt-1 font-black text-[#4bc6b9]">Rp {{ number_format((int) ($trx?->display_total_payable ?? 0), 0, ',', '.') }}</p>
                            </div>
                            <div class="admin-detail-panel md:col-span-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Layanan</p>
                                <p class="mt-1 font-black text-[#2b3a67]">{{ $productName }}</p>
                            </div>
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Metode Pembayaran</p>
                                <p class="mt-1 text-sm font-black uppercase text-[#2b3a67]">{{ $trx?->display_payment_method ?? '-' }}</p>
                            </div>
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status Pesanan</p>
                                <p class="mt-1 text-sm font-black uppercase text-[#5a76c8]">{{ $trx?->display_status ?? '-' }}</p>
                            </div>
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Kategori</p>
                                <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $categoryName ?: '-' }}</p>
                            </div>
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu Transaksi</p>
                                <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $trx?->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                            </div>
                            <div class="admin-detail-panel admin-detail-panel-soft md:col-span-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-amber-500">Data Terkait</p>
                                @if($detailRelatedSections !== [])
                                    <div class="mt-3 space-y-3">
                                        @foreach($detailRelatedSections as $section)
                                            <div class="rounded-[1rem] border-2 border-white bg-white/70 p-3 shadow-sm">
                                                <p class="text-[10px] font-black uppercase tracking-widest text-[#5a76c8]">{{ $section['title'] }}</p>
                                                <div class="mt-3 grid gap-3 md:grid-cols-2">
                                                    @foreach($section['lines'] as $line)
                                                        @php
                                                            $lineValue = (string) ($line['value'] ?? '');
                                                            $lineIsUrl = preg_match('/^https?:\/\//i', $lineValue);
                                                        @endphp
                                                        <div>
                                                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $line['label'] }}</p>
                                                            @if($lineIsUrl)
                                                                <a href="{{ $lineValue }}" target="_blank" rel="noopener" class="mt-1 block break-all text-sm font-bold text-[#5a76c8] underline decoration-[#bde0fe] underline-offset-4">
                                                                    {{ $lineValue }}
                                                                </a>
                                                            @else
                                                                <p class="mt-1 whitespace-pre-line break-all text-sm font-bold text-[#2b3a67]">{{ $lineValue }}</p>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mt-2 text-sm font-bold text-[#8faaf3]">Tidak ada data terkait.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @if($refunds->hasPages())
            <div class="border-t-4 border-dashed border-[#f4f9ff] px-5 py-4">
                {{ $refunds->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function openAdminRefundModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('modal-active');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        document.body.classList.add('admin-detail-open', 'overflow-hidden');
    }

    function closeAdminRefundModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('modal-active');
        modal.classList.add('opacity-0', 'pointer-events-none');
        document.body.classList.remove('admin-detail-open', 'overflow-hidden');
    }

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('.admin-detail-modal.modal-active').forEach(function (modal) {
            closeAdminRefundModal(modal.id);
        });
    });
</script>
@endsection
