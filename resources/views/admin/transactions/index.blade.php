@extends('layouts.admin')
@section('title', 'Riwayat Transaksi')
@section('admin_header_subtitle', 'Pantau transaksi dan pesanan pelanggan.')

@section('content')
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
        .admin-detail-modal {
            padding: 7.25rem 1.5rem 1.25rem;
        }
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
    @media (min-width: 640px) {
        .admin-detail-card {
            max-height: calc(100dvh - 9.5rem) !important;
        }
    }
    @media (max-width: 639px) {
        .admin-detail-modal {
            padding-top: 8rem;
        }
        .admin-detail-card {
            max-height: calc(100dvh - 9.5rem) !important;
        }
    }
    .admin-detail-card * { min-width: 0; }
    .admin-detail-card p,
    .admin-detail-card h3,
    .admin-detail-card span {
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .admin-detail-header {
        border-radius: 0;
        border: 0;
        border-bottom: 3px dashed #f4f9ff;
        background: transparent;
        box-shadow: none;
        padding: 0 0 .85rem 0;
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
    .admin-detail-panel-dropdown {
        overflow: visible;
        position: relative;
        z-index: 30;
    }
    .admin-detail-panel-dropdown .admin-theme-select-menu {
        top: auto;
        bottom: calc(100% + .45rem);
    }
    .admin-detail-status-form {
        display: grid !important;
        grid-template-columns: minmax(7.75rem, 8.8rem) minmax(0, 1fr);
        align-items: center;
        gap: .65rem;
    }
    .admin-detail-status-form .admin-theme-select-status {
        width: 100%;
        flex: 0 1 auto;
    }
    .admin-detail-status-form .admin-status-save-button {
        width: 100%;
        min-width: 0;
        white-space: normal;
        line-height: 1.15;
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
    .admin-detail-body::-webkit-scrollbar {
        width: 8px;
    }
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
    .admin-detail-body::-webkit-scrollbar-thumb:hover {
        background: #8faaf3;
    }
    .admin-detail-close {
        box-shadow: 0 10px 22px rgba(255, 107, 107, .14), inset 0 1px 0 rgba(255,255,255,.85);
    }

    .table-scroll {
        contain: none;
        overscroll-behavior-x: contain;
    }
    body.admin-detail-open .admin-transactions-table-shell {
        z-index: 10000;
    }

    .admin-theme-select {
        position: relative;
        min-width: 0;
    }

    .admin-theme-select.is-open {
        z-index: 120;
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
        z-index: 140;
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
        color: #2b3a67;
        white-space: nowrap;
        transition: background .16s ease, color .16s ease;
    }

    .admin-theme-select-option:hover,
    .admin-theme-select-option.is-selected {
        background: #e0fbfc;
        color: #5a76c8;
    }

    .admin-theme-select-status .admin-theme-select-menu {
        min-width: 9.5rem;
    }

    .admin-theme-select-month {
        flex: 1 1 0;
        width: auto;
    }

    .admin-theme-select-year {
        flex: 1 1 0;
        width: auto;
    }

    .admin-theme-select-status {
        width: 8.8rem;
        flex: 0 0 8.8rem;
    }

    .admin-theme-select-status .admin-theme-select-button {
        min-height: 2.25rem;
    }

    .admin-transactions-table-shell tr:has(.admin-theme-select.is-open) {
        position: relative;
        z-index: 10000;
    }

    .admin-transactions-table-shell .admin-theme-select-status .admin-theme-select-menu {
        top: auto;
        bottom: calc(100% + .45rem);
        z-index: 100000;
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
        .admin-detail-modal {
            top: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            left: 0 !important;
            height: auto !important;
            align-items: flex-start !important;
            justify-content: center !important;
            padding: 8.5rem .9rem 1rem !important;
            background: rgba(43, 58, 103, .64) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            backdrop-filter: blur(8px) !important;
        }

        .admin-detail-card {
            width: min(100%, 36rem) !important;
            max-height: calc(100dvh - 9.5rem) !important;
        }

        .admin-detail-body {
            padding-right: .45rem;
            scrollbar-gutter: stable;
        }

        .table-scroll {
            -webkit-overflow-scrolling: touch;
        }

        .table-scroll table {
            transform: none !important;
        }

        .admin-theme-select-menu {
            max-height: 12rem;
        }

        .admin-detail-status-form {
            grid-template-columns: minmax(7rem, 7.9rem) minmax(0, 1fr);
            gap: .5rem;
        }

        .admin-detail-status-form .admin-theme-select-button {
            padding-left: .75rem;
            padding-right: .75rem;
            font-size: .65rem;
        }

        .admin-detail-status-form .admin-status-save-button {
            padding-left: .65rem;
            padding-right: .65rem;
            font-size: .62rem;
        }
    }
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="wiboost-font pb-12 relative z-10 w-full min-w-0">
    @php
        $searchValue = request('search');
        $activeTransactionFilter = $transactionFilter ?? request('transaction_filter', 'all');
        $transactionFilters = [
            'all' => ['label' => 'Semua'],
            'pending' => ['label' => 'Menunggu'],
            'processing' => ['label' => 'Proses'],
            'success' => ['label' => 'Sukses'],
            'failed' => ['label' => 'Gagal'],
        ];
    @endphp
    <!-- Alert Messages (Aman dari 500 Error) -->
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 sm:px-6 py-4 font-black text-emerald-500 shadow-lg shadow-emerald-100/50 relative z-10 text-sm sm:text-base w-full">
            @include('partials.wiboost-icon', ['name' => 'success', 'class' => 'h-6 w-6 shrink-0'])
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-5 sm:px-6 py-4 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 relative z-10 text-sm sm:text-base w-full">
            @include('partials.wiboost-icon', ['name' => 'alert', 'class' => 'h-6 w-6 shrink-0'])
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Toolbar Utama: Pencarian, Segarkan, Filter & Cetak -->
    <div class="mb-6 sm:mb-8 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-4 sm:p-5 shadow-xl shadow-[#bde0fe]/30 relative z-30 w-full min-w-0 transition-transform duration-300 hover:border-[#bde0fe]">
        <form action="{{ route('admin.transactions.index') }}" method="GET" class="flex flex-col gap-4 w-full">
            
            <!-- Baris Atas: Input Cari + Tombol Cari + Reset + Segarkan -->
            <div class="flex flex-col md:flex-row gap-3 w-full min-w-0">
                <!-- Wrapper Input Cari -->
                <div class="flex-1 flex gap-2 w-full min-w-0">
                    @if($activeTransactionFilter !== 'all')
                        <input type="hidden" name="transaction_filter" value="{{ $activeTransactionFilter }}">
                    @endif
                    <input type="text" name="search" value="{{ $searchValue }}"
                        class="min-w-0 flex-1 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] py-3 px-4 sm:px-5 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe] text-xs sm:text-sm truncate"
                        placeholder="Cari Invoice atau Pelanggan...">
                    
                    <button type="submit" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] w-12 h-12 text-[0px] text-white shadow-md active:scale-95 transition-transform" title="Cari">
                        @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                    </button>
                    
                    @if($searchValue)
                        <a href="{{ route('admin.transactions.index', array_filter(['transaction_filter' => $activeTransactionFilter === 'all' ? null : $activeTransactionFilter, 'month' => $selectedMonth ?? date('m'), 'year' => $selectedYear ?? date('Y')])) }}" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#ffe5e5] w-12 h-12 text-[0px] text-[#ff6b6b] shadow-md active:scale-95 transition-transform" title="Reset">
                            @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                        </a>
                    @endif
                </div>
                
                <!-- Tombol Segarkan (Samping Search di Desktop) -->
                <a href="{{ route('admin.transactions.index', array_filter(['refresh_api' => 1, 'search' => $searchValue, 'transaction_filter' => $activeTransactionFilter === 'all' ? null : $activeTransactionFilter, 'month' => $selectedMonth ?? date('m'), 'year' => $selectedYear ?? date('Y')])) }}" class="shrink-0 flex items-center justify-center gap-2 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#8faaf3] px-6 py-3 h-12 text-sm font-black text-white shadow-md transition-transform active:scale-95 hover:bg-[#6f8ddc] w-full md:w-auto">
                    @include('partials.wiboost-icon', ['name' => 'refresh', 'class' => 'h-5 w-5'])
                    <span class="whitespace-nowrap">Segarkan</span>
                </a>
            </div>

            <!-- Baris Bawah: Filter Bulan/Tahun + Cetak PDF -->
            <div class="flex flex-col md:flex-row justify-start md:justify-end items-center gap-3 w-full border-t-4 border-dashed border-[#f4f9ff] pt-4 mt-1">
                
                <!-- Filter Dropdown -->
                <div class="flex items-center gap-2 w-full md:w-auto">
                    @php
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
                        $selectedMonthNumber = (int) ($selectedMonth ?? date('m'));
                    @endphp
                    <input type="hidden" name="month" value="{{ $selectedMonth ?? date('m') }}">
                    <div class="admin-theme-select admin-theme-select-month" data-admin-theme-select data-submit-on-change="true" data-input-name="month">
                        <button type="button" class="admin-theme-select-button rounded-[1rem] px-4 py-2 text-xs font-black sm:text-sm">
                            <span data-admin-theme-select-label>{{ $adminMonthNames[$selectedMonthNumber] ?? '-' }}</span>
                            <span class="admin-theme-select-arrow">⌄</span>
                        </button>
                        <div class="admin-theme-select-menu">
                        @for($m=1; $m<=12; $m++)
                            <button type="button" class="admin-theme-select-option {{ ($selectedMonth ?? date('m')) == sprintf('%02d', $m) ? 'is-selected' : '' }}" data-value="{{ sprintf('%02d', $m) }}" data-label="{{ $adminMonthNames[$m] }}">
                                {{ $adminMonthNames[$m] }}
                            </button>
                        @endfor
                        </div>
                    </div>
                    <input type="hidden" name="year" value="{{ $selectedYear ?? date('Y') }}">
                    <div class="admin-theme-select admin-theme-select-year" data-admin-theme-select data-submit-on-change="true" data-input-name="year">
                        <button type="button" class="admin-theme-select-button rounded-[1rem] px-4 py-2 text-xs font-black sm:text-sm">
                            <span data-admin-theme-select-label>{{ $selectedYear ?? date('Y') }}</span>
                            <span class="admin-theme-select-arrow">⌄</span>
                        </button>
                        <div class="admin-theme-select-menu">
                        @for($y=date('Y'); $y>=date('Y')-3; $y--)
                            <button type="button" class="admin-theme-select-option {{ ($selectedYear ?? date('Y')) == $y ? 'is-selected' : '' }}" data-value="{{ $y }}" data-label="{{ $y }}">{{ $y }}</button>
                        @endfor
                        </div>
                    </div>
                </div>

                <!-- Tombol Cetak Laporan -->
                <a href="{{ route('admin.transactions.export_excel', ['month' => $selectedMonth ?? date('m'), 'year' => $selectedYear ?? date('Y')]) }}" target="_blank" class="shrink-0 flex items-center justify-center gap-2 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#4bc6b9] px-6 py-2 h-auto min-h-[2.8rem] sm:min-h-[3rem] text-xs sm:text-sm font-black text-white shadow-md transition-transform active:scale-95 hover:bg-[#3ba398] w-full md:w-auto">
                    @include('partials.wiboost-icon', ['name' => 'chart', 'class' => 'h-5 w-5'])
                    <span class="whitespace-nowrap">Cetak Laporan</span>
                </a>
            </div>
        </form>

    </div>

    @include('admin.partials.filter-tabs', [
        'filters' => $transactionFilters,
        'activeFilter' => $activeTransactionFilter,
        'filterParam' => 'transaction_filter',
        'filterRoute' => 'admin.transactions.index',
        'filterQuery' => [
            'search' => $searchValue,
            'month' => $selectedMonth ?? date('m'),
            'year' => $selectedYear ?? date('Y'),
        ],
    ])

    <!-- Tabel Data Transaksi -->
    <div class="admin-transactions-table-shell overflow-visible rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/30 relative z-10 min-w-0 w-full">
        <div class="w-full overflow-x-auto table-scroll pb-2">
            <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                <table class="w-full text-left" style="min-width:1900px;table-layout:fixed;">
                    <thead>
                        <tr>
                            <th style="font-size: 10px; width: 190px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Invoice</th>
                            <th style="font-size: 10px; width: 150px;" class="px-6 py-4 pr-8 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Waktu</th>
                            <th style="font-size: 10px; width: 190px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Nama</th>
                            <th style="font-size: 10px; width: 240px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Email</th>
                            <th style="font-size: 10px; width: 360px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Layanan</th>
                            <th style="font-size: 10px; width: 130px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Harga</th>
                            <th style="font-size: 10px; width: 320px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Target</th>
                            <th style="font-size: 10px; width: 120px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Metode</th>
                            <th style="font-size: 10px; width: 120px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Bukti Login</th>
                            <th style="font-size: 10px; width: 180px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($transactions ?? [] as $trx)
                        @php
                            $adminProductName = $trx->display_product_name;
                            $adminTargetSummary = $trx->isMembershipPayment()
                                ? null
                                : ($trx->order_input_summary
                                    ?: (filled($trx->target_data) && $trx->target_data !== '- (Tidak membutuhkan target tambahan)' ? $trx->target_data : null));
                            $adminTargetText = $trx->isMembershipPayment()
                                ? '-'
                                : ($trx->order_input_text ?: $adminTargetSummary);
                            $canManualUpdate = in_array((int) (auth()->user()?->role_id), [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_WAREHOUSE], true)
                                && $trx->payment_status === 'paid'
                                && ($trx->product?->process_type === 'account')
                                && (bool) ($trx->product?->requires_buyer_email);
                            $adminStatusClass = match ($trx->display_status) {
                                'Refund' => ['bg-[#e0fbfc]', 'text-[#5a76c8]'],
                                'Sukses' => ['bg-[#e6fff7]', 'text-emerald-500'],
                                'Proses' => ['bg-[#e0fbfc]', 'text-[#5a76c8]'],
                                'Batal', 'Gagal' => ['bg-[#ffe5e5]', 'text-[#ff6b6b]'],
                                default => ['bg-[#fff5eb]', 'text-amber-500'],
                            };
                            $adminLoginProofUrl = $trx->login_proof_path
                                ? url(\Illuminate\Support\Facades\Storage::url($trx->login_proof_path))
                                : null;
                        @endphp
                        <tr onclick="openAdminDetailModal('admin-trx-detail-{{ $trx->id }}')" class="transition-colors hover:bg-[#f8faff] group cursor-pointer">
                            
                            <!-- Invoice -->
                            <td class="px-5 py-4 sm:py-5 align-top">
                                <p class="inline-block whitespace-nowrap rounded-md border border-white bg-[#f4f9ff] px-2 py-1 text-xs font-black text-[#5a76c8] sm:text-sm">{{ $trx->invoice_number ?? '-' }}</p>
                            </td>

                            <!-- Waktu -->
                            <td class="px-6 py-4 pr-8 sm:py-5 align-top">
                                <p class="whitespace-nowrap text-[9px] font-bold text-[#8faaf3] sm:text-[10px]">{{ isset($trx->created_at) ? $trx->created_at->format('d M Y, H:i') : '-' }}</p>
                            </td>
                            
                            <!-- Nama User -->
                            <td class="px-5 py-4 sm:py-5 align-top">
                                <p class="admin-clamp-2 text-xs font-black leading-snug text-[#2b3a67] sm:text-sm">{{ $trx->user->name ?? 'Guest' }}</p>
                            </td>

                            <!-- Email User -->
                            <td class="px-5 py-4 sm:py-5 align-top">
                                <p class="whitespace-nowrap text-[9px] font-bold text-[#8faaf3] sm:text-[10px]">{{ $trx->user->email ?? '-' }}</p>
                            </td>
                            
                            <!-- Layanan -->
                            <td class="px-5 py-4 align-top sm:py-5" style="width:360px;max-width:360px;">
                                <p class="text-[11px] font-black leading-tight text-[#2b3a67] sm:text-xs" style="display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden;text-overflow:clip;white-space:normal;word-break:normal;overflow-wrap:anywhere;" title="{{ $adminProductName }}">{{ $adminProductName }}</p>
                            </td>
                            
                            <!-- Harga -->
                            <td class="px-5 py-4 sm:py-5 align-top">
                                <p class="whitespace-nowrap text-xs font-black text-[#4bc6b9] drop-shadow-sm sm:text-sm">Rp {{ number_format($trx->display_total_payable, 0, ',', '.') }}</p>
                            </td>
                            
                            <!-- Target Input -->
                            <td class="px-5 py-4 sm:py-5 align-top text-center">
                                @if($adminTargetSummary)
                                    <span style="font-size: 9px;white-space:normal;overflow-wrap:anywhere;word-break:break-word;" class="inline-block w-full max-w-[320px] rounded-xl border-2 border-white bg-[#f0f5ff] px-3 py-1.5 font-black leading-snug text-[#2b3a67] shadow-sm" title="{{ $adminTargetText ?? '' }}">
                                        {{ $adminTargetSummary }}
                                    </span>
                                @else
                                    <span class="text-sm font-black text-[#a3bbfb]">-</span>
                                @endif
                            </td>
                            
                            <!-- Metode Pembayaran -->
                            <td class="px-5 py-4 sm:py-5 align-top text-center">
                                @php
                                    $methodDisplay = $trx->display_payment_method;
                                    if ($methodDisplay === 'SALDO') {
                                        $colorClass = 'bg-[#e0fbfc] text-[#5a76c8] border-white';
                                    } elseif ($methodDisplay === 'BELUM PILIH') {
                                        $colorClass = 'bg-[#fff5eb] text-amber-500 border-white';
                                    } else {
                                        $colorClass = 'bg-[#f4f9ff] text-[#8faaf3] border-white';
                                    }
                                @endphp
                                <span style="font-size: 9px;" class="inline-block whitespace-nowrap rounded-full border-2 px-3 py-1.5 font-black uppercase tracking-widest shadow-sm {{ $colorClass }}">
                                    {{ $methodDisplay }}
                                </span>
                            </td>

                            <!-- Bukti Login -->
                            <td class="px-5 py-4 sm:py-5 align-top text-center">
                                @if($adminLoginProofUrl)
                                    <a href="{{ $adminLoginProofUrl }}" target="_blank" rel="noopener" onclick="event.stopPropagation()" style="font-size: 9px;" class="inline-flex items-center justify-center whitespace-nowrap rounded-full border-2 border-white bg-[#e0fbfc] px-3 py-1.5 font-black uppercase tracking-widest text-[#5a76c8] shadow-sm transition hover:bg-[#d4f7f4]">
                                        Lihat
                                    </a>
                                @else
                                    <span class="text-sm font-black text-[#a3bbfb]">-</span>
                                @endif
                            </td>
                            
                            <!-- Status -->
                            <td class="px-5 py-4 sm:py-5 align-top text-center">
                                @if($canManualUpdate)
                                    <form onclick="event.stopPropagation()" action="{{ route('admin.transactions.update', $trx->id) }}" method="POST" class="flex items-center gap-1.5 justify-center w-full">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="order_status" value="{{ $trx->order_status ?? 'pending' }}">
                                        <div class="admin-theme-select admin-theme-select-status" data-admin-theme-select data-input-name="order_status">
                                            <button type="button" style="font-size: 9px;" class="admin-theme-select-button rounded-full px-3 py-1.5 text-center font-black uppercase tracking-widest">
                                                <span class="whitespace-nowrap" data-admin-theme-select-label>{{ match($trx->order_status ?? 'pending') { 'processing' => 'Proses', 'success' => 'Sukses', 'failed' => 'Gagal', default => 'Menunggu' } }}</span>
                                                <span class="admin-theme-select-arrow">⌄</span>
                                            </button>
                                            <div class="admin-theme-select-menu">
                                                <button type="button" class="admin-theme-select-option {{ ($trx->order_status ?? '') == 'pending' ? 'is-selected' : '' }}" data-value="pending" data-label="Menunggu">Menunggu</button>
                                                <button type="button" class="admin-theme-select-option {{ ($trx->order_status ?? '') == 'processing' ? 'is-selected' : '' }}" data-value="processing" data-label="Proses">Proses</button>
                                                <button type="button" class="admin-theme-select-option {{ ($trx->order_status ?? '') == 'success' ? 'is-selected' : '' }}" data-value="success" data-label="Sukses">Sukses</button>
                                                <button type="button" class="admin-theme-select-option {{ ($trx->order_status ?? '') == 'failed' ? 'is-selected' : '' }}" data-value="failed" data-label="Gagal">Gagal</button>
                                            </div>
                                        </div>
                                        <button type="submit" class="bg-[#5a76c8] hover:bg-[#4760a9] text-[0px] text-white p-1.5 rounded-[0.8rem] transition shadow-md active:scale-95 border-2 border-white shrink-0 text-xs" title="Simpan Status">
                                            @include('partials.wiboost-icon', ['name' => 'save', 'class' => 'h-4 w-4'])
                                        </button>
                                    </form>
                                @else
                                    @php
                                        $st = [$adminStatusClass[0], $adminStatusClass[1], $trx->display_status];
                                    @endphp
                                    <span style="font-size: 9px;" class="inline-block w-full whitespace-nowrap rounded-full border-2 border-white px-4 py-1.5 text-center font-black uppercase tracking-widest shadow-sm {{ $st[0] }} {{ $st[1] }}">
                                        {{ $st[2] }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-5 py-16 sm:py-20 text-center">
                                <div class="mb-4 sm:mb-5 inline-flex h-20 w-20 sm:h-24 sm:w-24 items-center justify-center rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-[#f0f5ff] text-[#5a76c8] shadow-inner animate-float">
                                    @include('partials.wiboost-icon', ['name' => 'receipt', 'class' => 'h-10 w-10'])
                                </div>
                                <p class="text-lg sm:text-xl font-black text-[#5a76c8]">Tidak ada transaksi ditemukan.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @foreach($transactions ?? [] as $trx)
            @php
                $detailProductName = $trx->display_product_name;
                $detailTargetSummary = $trx->isMembershipPayment()
                    ? null
                    : ($trx->order_input_summary
                        ?: (filled($trx->target_data) && $trx->target_data !== '- (Tidak membutuhkan target tambahan)' ? $trx->target_data : null));
                $detailTargetText = $trx->isMembershipPayment()
                    ? '-'
                    : ($trx->order_input_text ?: ($detailTargetSummary ?: '-'));
                $detailInputFields = collect($trx->order_input_fields ?? []);
                $detailInputText = $detailInputFields
                    ->reject(fn ($field) => ($field['name'] ?? '') === 'order_quantity')
                    ->map(fn ($field) => trim((string) ($field['value'] ?? '')))
                    ->filter()
                    ->implode("\n");
                $detailInputText = $trx->isMembershipPayment() ? '-' : ($detailInputText !== '' ? $detailInputText : ($detailTargetSummary ?: '-'));
                $detailQuantity = $trx->provider_order_quantity;
                $detailCategory = collect([
                    $trx->product?->category?->parent?->parent?->name,
                    $trx->product?->category?->parent?->name,
                    $trx->product?->category?->name,
                ])->filter()->implode(' / ');
                $detailLoginProofUrl = $trx->login_proof_path
                    ? url(\Illuminate\Support\Facades\Storage::url($trx->login_proof_path))
                    : null;
                $detailCanManualUpdate = in_array((int) (auth()->user()?->role_id), [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_WAREHOUSE], true)
                    && $trx->payment_status === 'paid'
                    && ($trx->product?->process_type === 'account')
                    && (bool) ($trx->product?->requires_buyer_email);
                $detailOrderData = \App\Support\WiboostOrderDetailData::forTransaction($trx);
                $detailCredentials = is_array($trx->credential_data)
                    ? $trx->credential_data
                    : (json_decode((string) $trx->credential_data, true) ?: []);
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
                $detailAppendLine($detailUserLines, 'Nama', $trx->user?->name);
                $detailAppendLine($detailUserLines, 'Email', $trx->user?->email);
                $detailAppendLine($detailUserLines, 'WhatsApp', $trx->user?->whatsapp);

                if ($detailUserLines !== []) {
                    $detailRelatedSections[] = ['title' => 'Data Pengguna', 'lines' => $detailUserLines];
                }

                if ($detailIsFilledValue($detailLoginProofUrl)) {
                    $detailRelatedSections[] = [
                        'title' => 'Bukti Login',
                        'lines' => [[
                            'label' => 'Link Bukti Login',
                            'value' => trim((string) $detailLoginProofUrl),
                        ]],
                    ];
                }

                $detailOrderLines = [];
                $detailAppendLine($detailOrderLines, 'Invoice', $detailOrderData['invoice'] ?? $trx->invoice_number);
                $detailAppendLine($detailOrderLines, 'Kategori', $detailOrderData['category'] ?? $detailCategory);
                $detailAppendLine($detailOrderLines, 'Produk', $detailOrderData['product'] ?? $detailProductName);
                $detailAppendLine($detailOrderLines, 'Total Bayar', $detailOrderData['total'] ?? null);
                $detailAppendLine($detailOrderLines, 'Metode', $detailOrderData['payment'] ?? $trx->display_payment_method);
                $detailAppendLine($detailOrderLines, 'Status Pesanan', $detailOrderData['status'] ?? $trx->display_status);
                $detailAppendLine($detailOrderLines, 'ID Provider', $detailOrderData['provider_id'] ?? $trx->provider_order_id);
                $detailAppendLine($detailOrderLines, 'Waktu', $detailOrderData['time'] ?? optional($trx->created_at)->format('d M Y, H:i'));

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

                $detailInputBuyerLines = $detailParseTextLines($detailOrderData['input_text'] ?? $detailInputText);

                if ($detailInputBuyerLines !== []) {
                    $detailRelatedSections[] = ['title' => 'Input Pembeli', 'lines' => $detailInputBuyerLines];
                }
            @endphp
            <div id="admin-trx-detail-{{ $trx->id }}" class="admin-detail-modal fixed inset-0 z-[9999] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
                <div class="absolute inset-0" onclick="closeAdminDetailModal('admin-trx-detail-{{ $trx->id }}')"></div>
                <div class="admin-detail-card relative flex w-full max-w-3xl max-h-[calc(100dvh-8.25rem)] flex-col rounded-[1.75rem] border-4 border-white bg-white p-4 shadow-2xl shadow-[#2b3a67]/20 transform scale-95 transition-transform duration-200 sm:p-5">
                    <div class="admin-detail-header mb-3.5 flex items-start justify-between gap-3 p-3.5">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Detail Transaksi</p>
                            <h3 class="mt-1 break-all text-xl font-black text-[#2b3a67]">{{ $trx->invoice_number }}</h3>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ optional($trx->created_at)->format('d M Y, H:i') }}</p>
                        </div>
                        <button type="button" onclick="closeAdminDetailModal('admin-trx-detail-{{ $trx->id }}')" class="admin-detail-close flex h-9 w-9 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#ffe5e5] text-[#ff6b6b]">
                            @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-4 w-4'])
                        </button>
                    </div>

                    <div class="admin-detail-body">
                    <div class="grid gap-3.5 md:grid-cols-2">
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Pelanggan</p>
                            <p class="mt-1 font-black text-[#2b3a67]">{{ $trx->user->name ?? 'Guest' }}</p>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3] break-all">{{ $trx->user->email ?? '-' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Total Bayar</p>
                            <p class="mt-1 font-black text-[#4bc6b9]">Rp {{ number_format($trx->display_total_payable, 0, ',', '.') }}</p>
                        </div>
                        <div class="admin-detail-panel md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Layanan</p>
                            <p class="mt-1 font-black text-[#2b3a67]">{{ $detailProductName }}</p>
                        </div>
                        <div class="admin-detail-panel md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Metode Pembayaran</p>
                            <p class="mt-1 text-sm font-black uppercase text-[#2b3a67]">{{ $trx->display_payment_method }}</p>
                        </div>
                        <div class="admin-detail-panel md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Kategori</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $detailCategory ?: 'Kategori tidak tersedia' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Input Pembeli</p>
                            <p class="mt-2 whitespace-pre-line break-all text-xs font-black text-[#2b3a67]">{{ $detailInputText }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Jumlah Pesanan</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $detailQuantity ? number_format($detailQuantity, 0, ',', '.') : '-' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status Pesanan</p>
                            <p class="mt-1 font-black uppercase text-[#5a76c8]">{{ $trx->display_status }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">ID Provider</p>
                            <p class="mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $trx->provider_order_id ?? '-' }}</p>
                        </div>
                        <div class="admin-detail-panel md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Bukti Login</p>
                            @if($detailLoginProofUrl)
                                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <a href="{{ $detailLoginProofUrl }}" target="_blank" rel="noopener" class="w-fit rounded-full border-2 border-white bg-[#5a76c8] px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white shadow-sm">
                                        Buka Gambar
                                    </a>
                                </div>
                                <div class="mt-3 overflow-hidden rounded-[1.2rem] border-4 border-white bg-white shadow-inner">
                                    <img src="{{ $detailLoginProofUrl }}" alt="Bukti login {{ $trx->invoice_number }}" class="max-h-72 w-full object-contain">
                                </div>
                                <p class="mt-2 break-all text-xs font-bold text-[#8faaf3]">{{ $detailLoginProofUrl }}</p>
                            @else
                                <p class="mt-2 text-sm font-bold text-[#8faaf3]">Tidak ada bukti.</p>
                            @endif
                        </div>
                        @if($trx->target_notes)
                            <div class="admin-detail-panel admin-detail-panel-soft md:col-span-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-amber-500">Catatan</p>
                                <p class="mt-1 text-sm font-bold text-[#2b3a67]">{{ $trx->target_notes }}</p>
                            </div>
                        @endif
                        <div class="admin-detail-panel admin-detail-panel-soft md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Data Terkait</p>
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
                    </div>

                    @if($detailCanManualUpdate)
                        <form action="{{ route('admin.transactions.update', $trx->id) }}" method="POST" class="admin-detail-panel admin-detail-panel-dropdown admin-detail-status-form mt-3.5">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="order_status" value="{{ $trx->order_status ?? 'pending' }}">
                            <div class="admin-theme-select admin-theme-select-status" data-admin-theme-select data-input-name="order_status">
                                <button type="button" class="admin-theme-select-button rounded-full px-4 py-2.5 text-xs font-black uppercase">
                                    <span data-admin-theme-select-label>{{ match($trx->order_status ?? 'pending') { 'processing' => 'Proses', 'success' => 'Sukses', 'failed' => 'Gagal', default => 'Menunggu' } }}</span>
                                    <span class="admin-theme-select-arrow">⌄</span>
                                </button>
                                <div class="admin-theme-select-menu">
                                    <button type="button" class="admin-theme-select-option {{ ($trx->order_status ?? '') == 'pending' ? 'is-selected' : '' }}" data-value="pending" data-label="Menunggu">Menunggu</button>
                                    <button type="button" class="admin-theme-select-option {{ ($trx->order_status ?? '') == 'processing' ? 'is-selected' : '' }}" data-value="processing" data-label="Proses">Proses</button>
                                    <button type="button" class="admin-theme-select-option {{ ($trx->order_status ?? '') == 'success' ? 'is-selected' : '' }}" data-value="success" data-label="Sukses">Sukses</button>
                                    <button type="button" class="admin-theme-select-option {{ ($trx->order_status ?? '') == 'failed' ? 'is-selected' : '' }}" data-value="failed" data-label="Gagal">Gagal</button>
                                </div>
                            </div>
                            <button type="submit" class="admin-status-save-button min-h-[2.75rem] rounded-full border-4 border-white bg-[#5a76c8] px-3 py-2.5 text-[10px] font-black text-white shadow-md sm:px-5 sm:text-xs">Simpan Status</button>
                        </form>
                    @endif
                    </div>
                </div>
            </div>
        @endforeach
        
        <!-- Paginasi -->
        @if(isset($transactions) && method_exists($transactions, 'links') && $transactions->hasPages())
            <div class="border-t-4 border-dashed border-[#f4f9ff] bg-[#f8faff] p-5 sm:p-8 w-full min-w-0">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
<script>
    function openAdminDetailModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('modal-active');
        document.body.classList.add('overflow-hidden', 'admin-detail-open');
    }

    function closeAdminDetailModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('modal-active');
        document.body.classList.remove('overflow-hidden', 'admin-detail-open');
    }

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('.admin-detail-modal.modal-active').forEach((modal) => {
            modal.classList.remove('modal-active');
        });
        document.querySelectorAll('[data-admin-theme-select].is-open').forEach((dropdown) => {
            dropdown.classList.remove('is-open');
            window.wiboostResetFloatingMenu?.(dropdown);
        });
        document.body.classList.remove('overflow-hidden', 'admin-detail-open');
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

                        dropdown.querySelectorAll('[data-value]').forEach((item) => item.classList.remove('is-selected'));
                        option.classList.add('is-selected');
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

    (() => {
        const refreshUrl = @json(route('admin.transactions.refresh-provider-statuses'));
        const csrfToken = @json(csrf_token());

        const shouldDelayRefresh = () => {
            const active = document.activeElement;

            return document.hidden
                || document.body.classList.contains('admin-detail-open')
                || !!document.querySelector('[data-admin-theme-select].is-open')
                || (active && ['INPUT', 'SELECT', 'TEXTAREA'].includes(active.tagName));
        };

        window.setInterval(async () => {
            if (shouldDelayRefresh()) {
                return;
            }

            try {
                await fetch(refreshUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'text/html',
                    },
                    credentials: 'same-origin',
                });

                window.setTimeout(() => {
                    if (! shouldDelayRefresh()) {
                        window.location.reload();
                    }
                }, 2500);
            } catch (error) {
                window.location.reload();
            }
        }, 60000);
    })();
</script>
@endsection
