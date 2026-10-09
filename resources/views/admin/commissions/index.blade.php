@extends('layouts.admin')

@section('title', 'Manajemen Komisi')
@section('admin_header_subtitle', 'Pantau komisi masuk reseller.')

@section('content')
@php
    $searchValue = $search ?? request('search');
    $activeCommissionFilter = $commissionFilter ?? request('commission_filter', 'all');
    $selectedMonth = $selectedMonth ?? request('month', now()->format('m'));
    $selectedYear = $selectedYear ?? request('year', now()->format('Y'));
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
    $commissionFilters = [
        'all' => ['label' => 'Semua'],
        'suntik-sosmed' => ['label' => 'Suntik Sosmed'],
        'top-up-game' => ['label' => 'Top Up Game'],
        'paket-data' => ['label' => 'Paket Data'],
        'aplikasi-premium' => ['label' => 'Aplikasi Premium'],
    ];
    $formatRupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $formatDate = fn ($value) => $value ? $value->timezone(config('app.timezone'))->format('d/m/Y H:i') : '-';
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
        inset: 0 !important;
        align-items: flex-start;
        justify-content: center;
        padding: 7rem .85rem 1rem;
    }
    @media (min-width: 640px) {
        .admin-detail-modal { padding: 7.25rem 1.5rem 1.25rem; }
    }
    @media (min-width: 1280px) {
        .admin-detail-modal {
            padding-top: 7.5rem;
            padding-left: 26rem;
            padding-right: 2rem;
        }
    }
    @media (max-width: 639px) {
        .admin-detail-modal { padding-top: 8rem; }
    }
    .admin-detail-modal.modal-active {
        display: flex;
        opacity: 1 !important;
        pointer-events: auto !important;
    }
    .admin-detail-modal.modal-active .admin-detail-card { transform: scale(1) !important; }
    .admin-detail-card {
        width: min(100%, 52rem);
        max-height: calc(100dvh - 9rem) !important;
        overflow: hidden;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .admin-detail-card * { min-width: 0; }
    .admin-detail-card p,
    .admin-detail-card h3,
    .admin-detail-card span {
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .admin-detail-header {
        border-bottom: 3px dashed #f4f9ff;
        padding: 0 0 .85rem 0;
    }
    .admin-detail-panel {
        overflow: hidden;
        border-radius: 1.2rem;
        border: 1px solid #eaf2ff;
        background: #f8faff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.9);
        padding: .85rem 1rem;
    }
    .admin-detail-panel-soft {
        border-color: #d9fff5;
        background: #f2fffb;
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
    .admin-detail-body::-webkit-scrollbar-track {
        background: transparent;
        border-radius: 999px;
        margin: .75rem 0;
    }
    .admin-detail-body::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #d8ecff, #8faaf3);
        border: 2px solid #ffffff;
        border-radius: 999px;
    }
    .admin-detail-close {
        box-shadow: 0 10px 22px rgba(255, 107, 107, .14), inset 0 1px 0 rgba(255,255,255,.85);
    }
    .admin-theme-select {
        position: relative;
        min-width: 0;
    }
    .admin-theme-select.is-open {
        z-index: 1000;
    }
    .admin-theme-select-button {
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        gap: .6rem;
        min-height: 2.75rem;
        border: 4px solid #fff;
        background: #f4f9ff;
        color: #2b3a67;
        box-shadow: inset 0 2px 8px rgba(143, 170, 243, .16), 0 8px 18px rgba(189, 224, 254, .18);
        transition: border-color .18s ease, background .18s ease, transform .18s ease;
    }
    .admin-theme-select-button [data-admin-theme-select-label] {
        min-width: 0;
        flex: 1 1 auto;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .admin-theme-select-button:hover,
    .admin-theme-select.is-open .admin-theme-select-button {
        border-color: #bde0fe;
        background: #eef8ff;
    }
    .admin-theme-select-arrow {
        display: inline-flex;
        flex: 0 0 .5rem;
        width: .5rem;
        height: .5rem;
        border-right: 2px solid currentColor;
        border-bottom: 2px solid currentColor;
        color: #5a76c8;
        font-size: 0;
        line-height: 0;
        transform: rotate(45deg) translate(-1px, -1px);
        transform-origin: center;
        transition: transform .18s ease;
    }
    .admin-theme-select.is-open .admin-theme-select-arrow {
        transform: rotate(225deg) translate(-1px, -1px);
    }
    .admin-theme-select-menu {
        position: absolute;
        z-index: 1100;
        left: 0;
        right: 0;
        top: calc(100% + .45rem);
        min-width: 100%;
        max-height: 16rem;
        overflow-y: auto;
        border: 4px solid #fff;
        border-radius: 1.1rem;
        background: #fff;
        padding: .35rem;
        box-shadow: 0 18px 40px rgba(90, 118, 200, .22);
    }

    .admin-theme-select-menu.is-floating-open {
        display: block !important;
        opacity: 1 !important;
        pointer-events: auto !important;
        transform: none !important;
    }
    .admin-theme-select:not(.is-open) .admin-theme-select-menu {
        display: none;
    }
    .admin-theme-select-option {
        width: 100%;
        display: block;
        border-radius: .85rem;
        padding: .65rem .8rem;
        text-align: left;
        font-size: .72rem;
        font-weight: 900;
        background: #f4f9ff;
        color: #2b3a67;
        white-space: nowrap;
        transition: background .16s ease, color .16s ease;
    }
    .admin-theme-select-option:hover {
        background: #e0fbfc;
        color: #4760a9;
    }
    .admin-theme-select-option.is-selected {
        background: #5a76c8;
        color: #ffffff;
    }
    .admin-theme-select-month,
    .admin-theme-select-year {
        flex: 1 1 0;
        width: auto;
    }
    @media (min-width: 768px) {
        .admin-theme-select-month {
            width: 9rem;
            flex: 0 0 9rem;
        }
        .admin-theme-select-year {
            width: 7rem;
            flex: 0 0 7rem;
        }
    }
    @media (max-width: 767px) {
        .admin-theme-select-menu {
            max-height: 12rem;
        }
    }
</style>

<div class="wiboost-font relative z-10 w-full min-w-0 pb-12">
    <div class="relative z-30 mb-6 flex w-full min-w-0 flex-col gap-3 overflow-visible sm:mb-8 sm:gap-4">
        <div class="wiboost-admin-toolbar-static min-w-0 rounded-[2rem] border-4 border-white bg-white/90 p-3 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:rounded-[2.5rem] sm:p-4">
            <form action="{{ route('admin.commissions.index') }}" method="GET" class="flex w-full flex-col gap-3 overflow-visible">
                @if($activeCommissionFilter !== 'all')
                    <input type="hidden" name="commission_filter" value="{{ $activeCommissionFilter }}">
                @endif
                <div class="flex w-full flex-col gap-3 md:flex-row">
                    <div class="flex min-w-0 flex-1 gap-2 sm:gap-3">
                        <input
                            type="text"
                            name="search"
                            value="{{ $searchValue }}"
                            class="min-w-0 flex-1 truncate rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:rounded-[1.5rem] sm:px-6 sm:py-4 sm:text-sm"
                            placeholder="Cari Komisi"
                        >

                        <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-xs font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:rounded-[1.2rem]" title="Cari Komisi">
                            @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                        </button>

                        @if($searchValue)
                            <a href="{{ route('admin.commissions.index', array_filter(['commission_filter' => $activeCommissionFilter === 'all' ? null : $activeCommissionFilter, 'month' => $selectedMonth, 'year' => $selectedYear])) }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#ffe5e5] text-xs font-black text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform hover:bg-[#ffcccc] active:scale-95 sm:rounded-[1.2rem]" title="Reset Pencarian">
                                @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                            </a>
                        @endif
                    </div>

                    <a href="{{ route('admin.commissions.index', array_filter(['search' => $searchValue, 'commission_filter' => $activeCommissionFilter === 'all' ? null : $activeCommissionFilter, 'month' => $selectedMonth, 'year' => $selectedYear])) }}" class="flex h-12 w-full shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#8faaf3] px-6 py-3 text-sm font-black text-white shadow-md transition-transform hover:bg-[#6f8ddc] active:scale-95 md:w-auto sm:rounded-[1.2rem]">
                        @include('partials.wiboost-icon', ['name' => 'refresh', 'class' => 'h-5 w-5'])
                        <span class="whitespace-nowrap">Segarkan</span>
                    </a>
                </div>

                <div class="mt-1 flex w-full flex-col items-center justify-start gap-3 border-t-4 border-dashed border-[#f4f9ff] pt-4 md:flex-row md:justify-end">
                    <div class="flex w-full items-center gap-2 md:w-auto">
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        <div class="admin-theme-select admin-theme-select-month" data-admin-theme-select data-submit-on-change="true" data-input-name="month">
                            <button type="button" class="admin-theme-select-button rounded-[1rem] px-4 py-2 text-xs font-black sm:text-sm">
                                <span data-admin-theme-select-label>{{ $adminMonthNames[$selectedMonthNumber] ?? '-' }}</span>
                                <span class="admin-theme-select-arrow">⌄</span>
                            </button>
                            <div class="admin-theme-select-menu">
                            @for($m = 1; $m <= 12; $m++)
                                <button type="button" class="admin-theme-select-option {{ (string) $selectedMonth === sprintf('%02d', $m) ? 'is-selected' : '' }}" data-value="{{ sprintf('%02d', $m) }}" data-label="{{ $adminMonthNames[$m] }}">
                                    {{ $adminMonthNames[$m] }}
                                </button>
                            @endfor
                            </div>
                        </div>
                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                        <div class="admin-theme-select admin-theme-select-year" data-admin-theme-select data-submit-on-change="true" data-input-name="year">
                            <button type="button" class="admin-theme-select-button rounded-[1rem] px-4 py-2 text-xs font-black sm:text-sm">
                                <span data-admin-theme-select-label>{{ $selectedYear }}</span>
                                <span class="admin-theme-select-arrow">⌄</span>
                            </button>
                            <div class="admin-theme-select-menu">
                            @for($y = now()->year + 1; $y >= now()->year - 10; $y--)
                                <button type="button" class="admin-theme-select-option {{ (string) $selectedYear === (string) $y ? 'is-selected' : '' }}" data-value="{{ $y }}" data-label="{{ $y }}">{{ $y }}</button>
                            @endfor
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('admin.commissions.export', array_filter(['search' => $searchValue, 'commission_filter' => $activeCommissionFilter === 'all' ? null : $activeCommissionFilter, 'month' => $selectedMonth, 'year' => $selectedYear])) }}" target="_blank" class="flex h-12 w-full shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#4bc6b9] px-6 py-2 text-xs font-black text-white shadow-md shadow-[#4bc6b9]/25 transition-transform hover:bg-[#3ba398] active:scale-95 md:w-auto sm:min-h-[3rem] sm:rounded-[1.2rem] sm:text-sm" title="Cetak Laporan">
                        @include('partials.wiboost-icon', ['name' => 'chart', 'class' => 'h-5 w-5'])
                        <span class="whitespace-nowrap">Cetak Laporan</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    @include('admin.partials.filter-tabs', [
        'filters' => $commissionFilters,
        'activeFilter' => $activeCommissionFilter,
        'filterParam' => 'commission_filter',
        'filterRoute' => 'admin.commissions.index',
        'filterQuery' => ['search' => $searchValue, 'month' => $selectedMonth, 'year' => $selectedYear],
    ])

    <div class="relative z-10 flex w-full min-w-0 flex-col overflow-hidden rounded-[2rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:rounded-[2.5rem]">
        <div class="table-scroll w-full overflow-x-auto pb-2">
            <div class="inline-block min-w-full p-4 align-middle sm:p-6">
                <table class="w-full whitespace-nowrap text-left" style="min-width:2170px;table-layout:auto;">
                    <thead>
                        <tr>
                            <th style="width:220px;" class="border-b-4 border-dashed border-[#f4f9ff] px-7 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Invoice</th>
                            <th style="width:175px;" class="border-b-4 border-dashed border-[#f4f9ff] px-7 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu</th>
                            <th style="min-width:340px;" class="border-b-4 border-dashed border-[#f4f9ff] px-7 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Reseller</th>
                            <th style="width:310px;" class="border-b-4 border-dashed border-[#f4f9ff] px-7 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Email</th>
                            <th style="width:470px;" class="border-b-4 border-dashed border-[#f4f9ff] px-7 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Produk</th>
                            <th style="width:210px;" class="border-b-4 border-dashed border-[#f4f9ff] px-7 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Kategori</th>
                            <th style="width:170px;" class="border-b-4 border-dashed border-[#f4f9ff] px-7 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Harga</th>
                            <th style="width:175px;" class="border-b-4 border-dashed border-[#f4f9ff] px-7 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Komisi</th>
                            <th style="width:150px;" class="border-b-4 border-dashed border-[#f4f9ff] px-7 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($incomeCommissions as $commission)
                            @php
                                $commissionTransaction = $commission->transaction;
                                $commissionInvoice = $commissionTransaction?->invoice_number ?? '-';
                                $commissionTime = $commissionTransaction?->created_at ?: $commission->created_at;
                                $commissionProductName = $commission->product_name ?: ($commissionTransaction?->product?->name ?? '-');
                                $commissionPrice = $commissionTransaction?->display_total_payable
                                    ?? $commissionTransaction?->amount
                                    ?? $commission->base_amount;
                            @endphp
                            <tr onclick="openAdminCommissionDetail('admin-commission-detail-{{ $commission->id }}')" class="group cursor-pointer transition-colors hover:bg-[#f8faff]">
                                <td class="px-7 py-4 align-top sm:py-5">
                                    <p class="inline-block whitespace-nowrap rounded-md border border-white bg-[#f4f9ff] px-2 py-1 text-xs font-black text-[#5a76c8] sm:text-sm">{{ $commissionInvoice }}</p>
                                </td>
                                <td class="px-7 py-4 align-top sm:py-5">
                                    <p class="whitespace-nowrap text-[9px] font-bold text-[#8faaf3] sm:text-[10px]">{{ $formatDate($commissionTime) }}</p>
                                </td>
                                <td class="px-7 py-4 align-top sm:py-5" style="min-width:340px;">
                                    <p class="whitespace-nowrap text-sm font-black leading-relaxed text-[#2b3a67]">{{ $commission->user?->name ?? '-' }}</p>
                                </td>
                                <td class="px-7 py-4 align-top sm:py-5">
                                    <p class="whitespace-normal break-all text-[10px] font-bold leading-relaxed text-[#8faaf3] sm:text-[11px]">{{ $commission->user?->email ?? '-' }}</p>
                                </td>
                                <td class="px-7 py-4 align-top sm:py-5" style="width:470px;max-width:470px;">
                                    <p class="text-[11px] font-black leading-relaxed text-[#2b3a67] sm:text-xs" style="display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden;text-overflow:clip;white-space:normal;word-break:normal;overflow-wrap:anywhere;" title="{{ $commissionProductName }}">{{ $commissionProductName }}</p>
                                </td>
                                <td class="px-7 py-4 align-top sm:py-5">
                                    <p class="whitespace-normal text-[10px] font-bold leading-relaxed text-[#8faaf3]">{{ $commission->category_name ?: '-' }}</p>
                                </td>
                                <td class="px-7 py-4 align-top sm:py-5">
                                    <p class="text-sm font-black text-[#2b3a67]">{{ $formatRupiah($commissionPrice) }}</p>
                                </td>
                                <td class="px-7 py-4 align-top sm:py-5">
                                    <p class="text-sm font-black text-[#4bc6b9] sm:text-base">+ {{ $formatRupiah($commission->amount) }}</p>
                                </td>
                                <td class="px-7 py-4 text-center align-top sm:py-5">
                                    <span class="inline-block rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1.5 text-[9px] font-black uppercase tracking-widest text-[#5a76c8] shadow-sm">{{ $commission->display_status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-16 text-center">
                                    <div class="mb-4 inline-flex h-20 w-20 items-center justify-center rounded-[2rem] border-4 border-white bg-[#f0f5ff] shadow-inner sm:mb-5 sm:h-24 sm:w-24 sm:rounded-[2.5rem]">
                                        @include('partials.wiboost-icon', ['name' => 'cash', 'class' => 'h-10 w-10 text-[#5a76c8]'])
                                    </div>
                                    <p class="text-lg font-black text-[#5a76c8] sm:text-xl">Belum ada komisi masuk.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($incomeCommissions, 'links') && $incomeCommissions->hasPages())
            <div class="p-5 sm:p-8 w-full min-w-0">
                {{ $incomeCommissions->links('vendor.pagination.tailwind') }}
            </div>
        @endif
    </div>

    @foreach($incomeCommissions as $commission)
        @php
            $detailTransaction = $commission->transaction;
            $detailBuyer = $detailTransaction?->user;
            $detailInvoice = $detailTransaction?->invoice_number ?? '-';
            $detailTime = $detailTransaction?->created_at ?: $commission->created_at;
            $detailProductName = $commission->product_name ?: ($detailTransaction?->product?->name ?? '-');
            $detailPrice = $detailTransaction?->display_total_payable
                ?? $detailTransaction?->amount
                ?? $commission->base_amount;
            $detailRate = (float) $commission->rate;
            $detailPaymentMethod = $detailTransaction?->display_payment_method ?? '-';
            $detailOrderStatus = $detailTransaction?->display_status ?? '-';
            $detailTargetSummary = $detailTransaction?->order_input_summary
                ?: (filled($detailTransaction?->target_data) && $detailTransaction?->target_data !== '- (Tidak membutuhkan target tambahan)' ? $detailTransaction?->target_data : null);
            $detailLoginProofUrl = $detailTransaction?->login_proof_path
                ? url(\Illuminate\Support\Facades\Storage::url($detailTransaction->login_proof_path))
                : null;
            $detailOrderData = $detailTransaction
                ? \App\Support\WiboostOrderDetailData::forTransaction($detailTransaction)
                : [];
            $detailCategory = collect([
                $detailTransaction?->product?->category?->parent?->parent?->name,
                $detailTransaction?->product?->category?->parent?->name,
                $detailTransaction?->product?->category?->name,
            ])->filter()->implode(' / ');
            $detailInputFields = collect($detailTransaction?->order_input_fields ?? []);
            $detailInputText = $detailInputFields
                ->reject(fn ($field) => ($field['name'] ?? '') === 'order_quantity')
                ->map(fn ($field) => trim((string) ($field['value'] ?? '')))
                ->filter()
                ->implode("\n");
            $detailInputText = $detailInputText !== '' ? $detailInputText : ($detailTargetSummary ?: '-');
            $detailCredentials = is_array($detailTransaction?->credential_data)
                ? $detailTransaction?->credential_data
                : (json_decode((string) $detailTransaction?->credential_data, true) ?: []);
            $detailIsFilledValue = function ($value): bool {
                if ($value === null || is_array($value)) {
                    return false;
                }

                $value = trim((string) $value);

                return ! in_array(strtolower($value), [
                    '',
                    '-',
                    "\xE2\x80\x94",
                    'n/a',
                    'na',
                    'null',
                    'undefined',
                    '[]',
                    '{}',
                    'tidak ada',
                    'belum ada',
                    'belum tersedia',
                ], true);
            };
            $detailAppendLine = function (array &$lines, string $label, $value) use ($detailIsFilledValue): void {
                if (! $detailIsFilledValue($value)) {
                    return;
                }

                $lines[] = [
                    'label' => $label,
                    'value' => trim((string) $value),
                ];
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
            $detailAppendLine($detailUserLines, 'Nama', $detailBuyer?->name);
            $detailAppendLine($detailUserLines, 'Email', $detailBuyer?->email);
            $detailAppendLine($detailUserLines, 'WhatsApp', $detailBuyer?->whatsapp);

            if ($detailUserLines !== []) {
                $detailRelatedSections[] = ['title' => 'Data Pengguna', 'lines' => $detailUserLines];
            }

            if ($detailIsFilledValue($detailLoginProofUrl)) {
                $detailRelatedSections[] = [
                    'title' => 'Bukti Masuk',
                    'lines' => [[
                        'label' => 'Tautan Bukti Masuk',
                        'value' => trim((string) $detailLoginProofUrl),
                    ]],
                ];
            }

            $detailOrderLines = [];
            $detailAppendLine($detailOrderLines, 'Invoice', $detailOrderData['invoice'] ?? $detailInvoice);
            $detailAppendLine($detailOrderLines, 'Kategori', $detailOrderData['category'] ?? $detailCategory);
            $detailAppendLine($detailOrderLines, 'Produk', $detailOrderData['product'] ?? $detailProductName);
            $detailAppendLine($detailOrderLines, 'Total Bayar', $detailOrderData['total'] ?? $detailPrice);
            $detailAppendLine($detailOrderLines, 'Metode', $detailOrderData['payment'] ?? $detailPaymentMethod);
            $detailAppendLine($detailOrderLines, 'Status Pesanan', $detailOrderData['status'] ?? $detailOrderStatus);
            $detailAppendLine($detailOrderLines, 'ID Penyedia', $detailOrderData['provider_id'] ?? $detailTransaction?->provider_order_id);
            $detailAppendLine($detailOrderLines, 'Waktu', $detailOrderData['time'] ?? $formatDate($detailTime));

            if ($detailOrderLines !== []) {
                $detailRelatedSections[] = ['title' => 'Data Pesanan', 'lines' => $detailOrderLines];
            }

            $detailAccountLines = [];
            foreach ([
                'email' => 'Email',
                'username' => 'Nama Pengguna',
                'password' => 'Kata Sandi',
                'profile' => 'Profil',
                'pin' => 'PIN',
                'link' => 'Tautan Akses',
                'tutorial_link' => 'Tautan Tutorial',
                'notes' => 'Catatan',
            ] as $credentialKey => $credentialLabel) {
                $detailAppendLine($detailAccountLines, $credentialLabel, $detailCredentials[$credentialKey] ?? null);
            }

            if ($detailAccountLines !== []) {
                $detailRelatedSections[] = ['title' => 'Detail Akun', 'lines' => $detailAccountLines];
            }

            $detailInputBuyerLines = $detailParseTextLines($detailOrderData['input_text'] ?? $detailInputText);

            if ($detailInputBuyerLines !== []) {
                $detailRelatedSections[] = ['title' => 'Input Pembeli', 'lines' => $detailInputBuyerLines];
            }
        @endphp
        <div id="admin-commission-detail-{{ $commission->id }}" class="admin-detail-modal fixed z-[9999] items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
            <div class="absolute inset-0" onclick="closeAdminCommissionDetail('admin-commission-detail-{{ $commission->id }}')"></div>
            <div class="admin-detail-card relative flex max-h-[calc(100dvh-8.25rem)] w-full max-w-3xl scale-95 flex-col rounded-[1.75rem] border-4 border-white bg-white p-4 shadow-2xl shadow-[#2b3a67]/20 transition-transform duration-200 sm:p-5">
                <div class="admin-detail-header mb-3.5 flex items-start justify-between gap-3 p-3.5">
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Detail Komisi</p>
                        <h2 class="mt-1 break-all text-xl font-black text-[#2b3a67]">{{ $detailInvoice }}</h2>
                        <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $formatDate($detailTime) }}</p>
                    </div>
                    <button type="button" onclick="closeAdminCommissionDetail('admin-commission-detail-{{ $commission->id }}')" class="admin-detail-close flex h-9 w-9 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#ffe5e5] text-[#ff6b6b]">
                        @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-4 w-4'])
                    </button>
                </div>

                <div class="admin-detail-body">
                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama Reseller</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $commission->user?->name ?? '-' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Email Reseller</p>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $commission->user?->email ?? '-' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama Pembeli</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $detailBuyer?->name ?? '-' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Email Pembeli</p>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $detailBuyer?->email ?? '-' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Produk</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $detailProductName }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Kategori</p>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $commission->category_name ?: '-' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Harga Transaksi</p>
                            <p class="mt-1 text-lg font-black text-[#2b3a67]">{{ $formatRupiah($detailPrice) }}</p>
                        </div>
                        <div class="admin-detail-panel admin-detail-panel-soft">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#08a878]">Komisi Masuk</p>
                            <p class="mt-1 text-2xl font-black text-[#4bc6b9]">+ {{ $formatRupiah($commission->amount) }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Rate Komisi</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ number_format($detailRate * 100, 2, ',', '.') }}%</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</p>
                            <p class="mt-1 text-sm font-black uppercase text-[#5a76c8]">{{ $commission->display_status }}</p>
                        </div>
                        <div class="admin-detail-panel admin-detail-panel-soft md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Data Pesanan Terkait</p>
                            @if($detailRelatedSections !== [])
                                <div class="mt-3 space-y-3">
                                    @foreach($detailRelatedSections as $section)
                                        <div class="rounded-[1rem] border-2 border-white bg-white/70 p-3 shadow-sm">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-[#5a76c8]">{{ $section['title'] }}</p>
                                            <div class="admin-detail-subgrid mt-3">
                                                @foreach($section['lines'] as $line)
                                                    @php
                                                        $lineValue = (string) ($line['value'] ?? '');
                                                        $lineIsUrl = preg_match('/^https?:\/\//i', $lineValue);
                                                    @endphp
                                                    <div class="admin-detail-subfield">
                                                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $line['label'] }}</p>
                                                        @if($lineIsUrl)
                                                            <a href="{{ $lineValue }}" target="_blank" rel="noopener" class="mt-1 block break-all text-sm font-bold text-[#5a76c8] underline decoration-[#bde0fe] underline-offset-4">
                                                                {{ $lineValue }}
                                                            </a>
                                                        @else
                                                            <p class="mt-1 break-words text-sm font-bold text-[#2b3a67]">{{ $lineValue }}</p>
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
                        <div class="admin-detail-panel md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Catatan</p>
                            <p class="mt-1 whitespace-pre-line break-words text-left text-sm font-bold leading-relaxed text-[#2b3a67]">{{ $commission->notes ?: '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
    function openAdminCommissionDetail(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('modal-active');
        document.body.classList.add('admin-detail-open');
    }

    function closeAdminCommissionDetail(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('modal-active');
        document.body.classList.remove('admin-detail-open');
    }

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        document.querySelectorAll('.admin-detail-modal.modal-active').forEach((modal) => {
            modal.classList.remove('modal-active');
        });
        document.querySelectorAll('[data-admin-theme-select].is-open').forEach((dropdown) => {
            dropdown.classList.remove('is-open');
            window.wiboostResetFloatingMenu?.(dropdown);
        });

        document.body.classList.remove('admin-detail-open');
    });

    (() => {
        const closeAllDropdowns = (except = null) => {
            document.querySelectorAll('[data-admin-theme-select].is-open').forEach((dropdown) => {
                if (dropdown !== except) {
                    dropdown.classList.remove('is-open');
                    resetFloatingMenu(dropdown);
                }
            });
        };

        const resetFloatingMenu = (dropdown) => {
            const menu = dropdown.querySelector('.admin-theme-select-menu') || dropdown._wiboostFloatingMenu;

            if (!menu) {
                return;
            }

            if (dropdown._wiboostFloatingMenuParent && menu.parentElement !== dropdown._wiboostFloatingMenuParent) {
                if (dropdown._wiboostFloatingMenuNext && dropdown._wiboostFloatingMenuNext.parentElement === dropdown._wiboostFloatingMenuParent) {
                    dropdown._wiboostFloatingMenuParent.insertBefore(menu, dropdown._wiboostFloatingMenuNext);
                } else {
                    dropdown._wiboostFloatingMenuParent.appendChild(menu);
                }
            }

            menu.style.position = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.top = '';
            menu.style.bottom = '';
            menu.style.width = '';
            menu.style.minWidth = '';
            menu.style.maxHeight = '';
            menu.style.zIndex = '';
            menu.style.display = '';
            menu.classList.remove('is-floating-open');
        };

        const positionFloatingMenu = (dropdown) => {
            const menu = dropdown.querySelector('.admin-theme-select-menu') || dropdown._wiboostFloatingMenu;

            if (!menu) {
                return;
            }

            if (!dropdown._wiboostFloatingMenuParent) {
                dropdown._wiboostFloatingMenuParent = menu.parentElement;
                dropdown._wiboostFloatingMenuNext = menu.nextSibling;
                dropdown._wiboostFloatingMenu = menu;
                dropdown._wiboostSharedFloatingMenuParent = menu.parentElement;
                dropdown._wiboostSharedFloatingMenuNext = menu.nextSibling;
                dropdown._wiboostSharedFloatingMenu = menu;
            }

            if (menu.parentElement !== document.body) {
                document.body.appendChild(menu);
            }

            const rect = dropdown.getBoundingClientRect();
            const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
            const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
            const gap = 8;
            const maxHeight = 256;
            menu.style.display = 'block';
            menu.classList.add('is-floating-open');
            const desiredHeight = Math.min(menu.scrollHeight || maxHeight, maxHeight);
            const openUp = viewportHeight - rect.bottom < Math.min(desiredHeight, 160) && rect.top > desiredHeight;
            const availableHeight = openUp
                ? Math.max(120, rect.top - (gap * 2))
                : Math.max(120, viewportHeight - rect.bottom - (gap * 2));
            const width = Math.max(rect.width, 152);
            const left = Math.min(Math.max(gap, rect.left), Math.max(gap, viewportWidth - width - gap));

            menu.style.position = 'fixed';
            menu.style.left = `${left}px`;
            menu.style.right = 'auto';
            menu.style.width = `${width}px`;
            menu.style.minWidth = `${width}px`;
            menu.style.maxHeight = `${Math.min(maxHeight, availableHeight)}px`;
            menu.style.bottom = 'auto';
            menu.style.top = openUp
                ? `${Math.max(gap, rect.top - Math.min(desiredHeight, availableHeight) - gap)}px`
                : `${Math.min(viewportHeight - gap, rect.bottom + gap)}px`;
            menu.style.zIndex = '100000';
        };

        document.querySelectorAll('[data-admin-theme-select]').forEach((dropdown) => {
            dropdown.dataset.adminThemeSelectReady = 'true';
            const button = dropdown.querySelector('.admin-theme-select-button');
            const label = dropdown.querySelector('[data-admin-theme-select-label]');
            const inputName = dropdown.dataset.inputName;
            const form = dropdown.closest('form');
            const input = form?.querySelector(`input[type="hidden"][name="${inputName}"]`);

            button?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const willOpen = ! dropdown.classList.contains('is-open');
                closeAllDropdowns(dropdown);
                dropdown.classList.toggle('is-open', willOpen);
                if (willOpen) {
                    window.requestAnimationFrame(() => positionFloatingMenu(dropdown));
                } else {
                    resetFloatingMenu(dropdown);
                }
            });

            dropdown.querySelectorAll('[data-value]').forEach((option) => {
                option.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    if (input) {
                        input.value = option.dataset.value ?? '';
                    }

                    if (label) {
                        label.textContent = option.dataset.label || option.textContent.trim();
                    }

                    dropdown.querySelectorAll('[data-value]').forEach((item) => {
                        const isSelected = item === option;
                        item.classList.toggle('is-selected', isSelected);
                        item.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                    });
                    dropdown.classList.remove('is-open');
                    resetFloatingMenu(dropdown);

                    if (dropdown.dataset.submitOnChange === 'true' && form) {
                        form.submit();
                    }
                });
            });
        });

        document.addEventListener('click', () => closeAllDropdowns());
        window.addEventListener('resize', () => {
            document.querySelectorAll('[data-admin-theme-select].is-open').forEach(positionFloatingMenu);
        });
        window.addEventListener('scroll', () => {
            document.querySelectorAll('[data-admin-theme-select].is-open').forEach(positionFloatingMenu);
        }, true);
    })();
</script>
@endsection
