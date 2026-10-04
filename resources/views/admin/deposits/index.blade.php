@extends('layouts.admin')
@section('title', 'Riwayat Deposit')
@section('admin_header_subtitle', 'Pantau top-up dan refund saldo pengguna.')

{{-- Section admin_header_actions dihapus, dipindah ke toolbar agar responsif --}}

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
        width: min(100%, 44rem);
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
    }
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
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

<div class="wiboost-font pb-12 relative z-10 w-full min-w-0">
    @php
        $searchValue = request('search');
        $activeDepositFilter = $depositFilter ?? request('deposit_filter', 'all');
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
        $depositFilters = [
            'all' => ['label' => 'Semua'],
            'paid' => ['label' => 'Berhasil'],
            'failed' => ['label' => 'Gagal'],
            'topup' => ['label' => 'Top Up'],
            'poin' => ['label' => 'Poin'],
            'refund' => ['label' => 'Refund'],
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

    <!-- Toolbar: Pencarian & Segarkan -->
    <div class="mb-6 sm:mb-8 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-4 sm:p-5 shadow-xl shadow-[#bde0fe]/30 relative z-10 w-full min-w-0 transition-transform duration-300 hover:border-[#bde0fe]">
        <form action="{{ route('admin.deposits.index') }}" method="GET" class="flex w-full flex-col gap-3">
            <div class="flex w-full flex-col gap-3 md:flex-row">
            
            <!-- Wrapper Input Cari -->
            <div class="flex-1 flex gap-2 w-full min-w-0">
                @if($activeDepositFilter !== 'all')
                    <input type="hidden" name="deposit_filter" value="{{ $activeDepositFilter }}">
                @endif
                <input type="hidden" name="month" value="{{ $selectedMonth }}">
                <input type="hidden" name="year" value="{{ $selectedYear }}">
                <input type="text" name="search" value="{{ $searchValue }}"
                    class="min-w-0 flex-1 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] py-3 px-4 sm:px-5 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe] text-xs sm:text-sm truncate"
                    placeholder="Cari Invoice atau Pelanggan...">
                
                <button type="submit" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] w-12 h-12 text-white shadow-md active:scale-95 transition-transform" title="Cari Deposit">
                    @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                </button>
                
                @if($searchValue)
                    <a href="{{ route('admin.deposits.index', array_filter(['deposit_filter' => $activeDepositFilter === 'all' ? null : $activeDepositFilter, 'month' => $selectedMonth, 'year' => $selectedYear])) }}" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#ffe5e5] w-12 h-12 text-[#ff6b6b] shadow-md active:scale-95 transition-transform" title="Reset">
                        @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                    </a>
                @endif
            </div>
            
            <!-- Tombol Segarkan -->
            <a href="{{ route('admin.deposits.index', array_filter(['deposit_filter' => $activeDepositFilter === 'all' ? null : $activeDepositFilter, 'month' => $selectedMonth, 'year' => $selectedYear])) }}" class="shrink-0 flex items-center justify-center gap-2 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#8faaf3] px-6 py-3 h-12 text-sm font-black text-white shadow-md transition-transform active:scale-95 hover:bg-[#6f8ddc] w-full md:w-auto">
                @include('partials.wiboost-icon', ['name' => 'refresh', 'class' => 'h-5 w-5'])
                <span class="whitespace-nowrap">Segarkan</span>
            </a>
            </div>

            <div class="mt-1 flex w-full flex-col items-center justify-start gap-3 border-t-4 border-dashed border-[#f4f9ff] pt-4 md:flex-row md:justify-end">
                <div class="flex w-full items-center gap-2 md:w-auto">
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
                            @for($y = now()->year + 1; $y >= now()->year - 10; $y--)
                                <button type="button" class="admin-theme-select-option {{ (string) $selectedYear === (string) $y ? 'is-selected' : '' }}" data-value="{{ $y }}" data-label="{{ $y }}">{{ $y }}</button>
                            @endfor
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.deposits.export_excel', array_filter(['search' => $searchValue, 'deposit_filter' => $activeDepositFilter === 'all' ? null : $activeDepositFilter, 'month' => $selectedMonth, 'year' => $selectedYear])) }}" target="_blank" class="shrink-0 flex items-center justify-center gap-2 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#4bc6b9] px-6 py-3 h-12 text-sm font-black text-white shadow-md shadow-[#4bc6b9]/25 transition-transform active:scale-95 hover:bg-[#3ba398] w-full md:w-auto">
                    @include('partials.wiboost-icon', ['name' => 'chart', 'class' => 'h-5 w-5'])
                    <span class="whitespace-nowrap">Cetak Laporan</span>
                </a>
            </div>
            
        </form>
    </div>

    @include('admin.partials.filter-tabs', [
        'filters' => $depositFilters,
        'activeFilter' => $activeDepositFilter,
        'filterParam' => 'deposit_filter',
        'filterRoute' => 'admin.deposits.index',
        'filterQuery' => ['search' => $searchValue, 'month' => $selectedMonth, 'year' => $selectedYear],
    ])

    <!-- Tabel Data Deposit -->
    <div class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm shadow-2xl shadow-[#bde0fe]/30 relative z-10 min-w-0 w-full">
        <div class="w-full overflow-x-auto table-scroll pb-2">
            <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                <table class="w-full min-w-[1120px] text-left whitespace-nowrap">
                    <thead>
                        <tr>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Invoice</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Waktu</th>
                            <th style="min-width: 280px; font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Nama</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Email</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Jenis</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Nominal</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Dibayar</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($deposits ?? [] as $deposit)
                        <tr onclick="openAdminDepositDetail('admin-deposit-detail-{{ $deposit->id }}')" class="transition-colors hover:bg-[#f8faff] group cursor-pointer">
                            
                            <!-- Invoice -->
                            <td class="px-5 py-4 sm:py-5 align-middle min-w-[150px]">
                                <p class="inline-block whitespace-nowrap rounded-md border border-white bg-[#f4f9ff] px-2 py-1 text-xs font-black text-[#5a76c8] sm:text-sm">{{ $deposit->invoice_number ?? '-' }}</p>
                            </td>

                            <!-- Waktu -->
                            <td class="px-5 py-4 sm:py-5 align-middle min-w-[150px]">
                                <p class="whitespace-nowrap text-[9px] font-bold text-[#8faaf3] sm:text-[10px]">{{ isset($deposit->created_at) ? $deposit->created_at->format('d M Y, H:i') : '-' }}</p>
                            </td>
                            
                            <!-- Nama -->
                            <td class="px-5 py-4 sm:py-5 align-middle" style="min-width: 280px;">
                                <p class="whitespace-nowrap font-black text-[#2b3a67] text-xs sm:text-sm">{{ $deposit->user->name ?? 'Guest' }}</p>
                            </td>

                            <!-- Email -->
                            <td class="px-5 py-4 sm:py-5 align-middle min-w-[210px]">
                                <p class="text-[9px] sm:text-[10px] font-bold text-[#8faaf3]" style="white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;">{{ $deposit->user->email ?? '-' }}</p>
                            </td>

                            <td class="px-5 py-4 sm:py-5 align-middle text-center">
                                @if($deposit->isRefundCredit())
                                    <span style="font-size: 9px;" class="inline-block rounded-full border-2 border-white bg-[#e6fff7] px-3 py-1.5 font-black uppercase tracking-widest text-emerald-500 shadow-sm">Refund</span>
                                @elseif($deposit->isMembershipPayment())
                                    <span style="font-size: 9px;" class="inline-block rounded-full border-2 border-white bg-[#ede9fe] px-3 py-1.5 font-black uppercase tracking-widest text-[#8b5cf6] shadow-sm">Membership</span>
                                @elseif($deposit->isPointExchange())
                                    <span style="font-size: 9px;" class="inline-block rounded-full border-2 border-white bg-[#f4f9ff] px-3 py-1.5 font-black uppercase tracking-widest text-[#5a76c8] shadow-sm">Poin</span>
                                @else
                                    <span style="font-size: 9px;" class="inline-block rounded-full border-2 border-white bg-[#e0fbfc] px-3 py-1.5 font-black uppercase tracking-widest text-[#5a76c8] shadow-sm">Top Up</span>
                                @endif
                            </td>
                            
                            <!-- Nominal -->
                            <td class="px-5 py-4 sm:py-5 align-middle">
                                <p class="font-black text-[#4bc6b9] text-sm sm:text-base drop-shadow-sm">Rp {{ number_format($deposit->amount ?? 0, 0, ',', '.') }}</p>
                            </td>

                            <td class="px-5 py-4 sm:py-5 align-middle">
                                <p class="font-black text-[#5a76c8] text-sm sm:text-base drop-shadow-sm">Rp {{ number_format($deposit->paid_amount ?? $deposit->amount ?? 0, 0, ',', '.') }}</p>
                            </td>
                            
                            <!-- Status -->
                            <td class="px-5 py-4 sm:py-5 align-middle text-center min-w-[120px]">
                                @php
                                    // Status pada deposit umumnya adalah paid, unpaid, failed, atau expired
                                    $statusLabels = [
                                        'paid' => ['bg-[#e6fff7]', 'text-emerald-500', 'Berhasil'],
                                        'success' => ['bg-[#e6fff7]', 'text-emerald-500', 'Berhasil'],
                                        'unpaid' => ['bg-[#fff5eb]', 'text-amber-500', 'Menunggu'],
                                        'pending' => ['bg-[#fff5eb]', 'text-amber-500', 'Menunggu'],
                                        'failed' => ['bg-[#ffe5e5]', 'text-[#ff6b6b]', 'Gagal'],
                                        'expired' => ['bg-[#ffe5e5]', 'text-[#ff6b6b]', 'Expired'],
                                        'refund' => ['bg-[#e6fff7]', 'text-emerald-500', 'Refund'],
                                    ];
                                    $rawStatus = strtolower($deposit->payment_status ?? 'pending');
                                    $st = $statusLabels[$rawStatus] ?? ['bg-gray-100', 'text-gray-500', $rawStatus];
                                @endphp
                                <span style="font-size: 9px;" class="{{ $st[0] }} {{ $st[1] }} px-4 py-1.5 rounded-full font-black uppercase tracking-widest border-2 border-white shadow-sm inline-block w-full text-center">
                                    {{ $st[2] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 sm:py-20 text-center">
                                <div class="mb-4 sm:mb-5 inline-flex h-20 w-20 sm:h-24 sm:w-24 items-center justify-center rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-[#f0f5ff] text-[#5a76c8] shadow-inner animate-float">
                                    @include('partials.wiboost-icon', ['name' => 'wallet', 'class' => 'h-10 w-10'])
                                </div>
                                <p class="text-lg sm:text-xl font-black text-[#5a76c8]">Belum ada riwayat deposit.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @foreach($deposits ?? [] as $deposit)
            @php
                $depositStatusLabels = [
                    'paid' => ['bg-[#e6fff7]', 'text-emerald-500', 'Berhasil'],
                    'success' => ['bg-[#e6fff7]', 'text-emerald-500', 'Berhasil'],
                    'unpaid' => ['bg-[#fff5eb]', 'text-amber-500', 'Menunggu'],
                    'pending' => ['bg-[#fff5eb]', 'text-amber-500', 'Menunggu'],
                    'failed' => ['bg-[#ffe5e5]', 'text-[#ff6b6b]', 'Gagal'],
                    'expired' => ['bg-[#ffe5e5]', 'text-[#ff6b6b]', 'Expired'],
                    'refund' => ['bg-[#e6fff7]', 'text-emerald-500', 'Refund'],
                ];
                $depositRawStatus = strtolower($deposit->payment_status ?? 'pending');
                $depositStatus = $depositStatusLabels[$depositRawStatus] ?? ['bg-gray-100', 'text-gray-500', $depositRawStatus];
            @endphp
            <div id="admin-deposit-detail-{{ $deposit->id }}" class="admin-detail-modal fixed inset-0 z-[9999] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
                <div class="absolute inset-0" onclick="closeAdminDepositDetail('admin-deposit-detail-{{ $deposit->id }}')"></div>
                <div class="admin-detail-card relative flex w-full max-w-2xl max-h-[calc(100dvh-8.25rem)] flex-col rounded-[1.75rem] border-4 border-white bg-white p-4 shadow-2xl shadow-[#2b3a67]/20 transform scale-95 transition-transform duration-200 sm:p-5">
                    <div class="admin-detail-header mb-3.5 flex items-start justify-between gap-3 p-3.5">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Detail Deposit</p>
                            <h2 class="mt-1 break-all text-xl font-black text-[#2b3a67]">{{ $deposit->invoice_number }}</h2>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ optional($deposit->created_at)->format('d M Y, H:i') }}</p>
                        </div>
                        <button type="button" onclick="closeAdminDepositDetail('admin-deposit-detail-{{ $deposit->id }}')" class="admin-detail-close flex h-9 w-9 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#ffe5e5] text-[#ff6b6b]">
                            @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-4 w-4'])
                        </button>
                    </div>

                    <div class="admin-detail-body">
                    <div class="grid gap-3.5 md:grid-cols-2">
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama Pelanggan</p>
                            <p class="mt-1 font-black text-[#2b3a67]">{{ $deposit->user->name ?? 'Guest' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Email</p>
                            <p class="mt-1 break-all text-xs font-bold text-[#8faaf3]">{{ $deposit->user->email ?? '-' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</p>
                            <span class="{{ $depositStatus[0] }} {{ $depositStatus[1] }} mt-2 inline-flex rounded-full border-2 border-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest shadow-sm">{{ $depositStatus[2] }}</span>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Jenis Pembayaran</p>
                            <p class="mt-1 text-lg font-black {{ $deposit->isMembershipPayment() ? 'text-[#8b5cf6]' : ($deposit->isRefundCredit() ? 'text-emerald-500' : 'text-[#5a76c8]') }}">{{ $deposit->isRefundCredit() ? 'Refund Saldo' : ($deposit->isMembershipPayment() ? 'Membership' : ($deposit->isPointExchange() ? 'Penukaran Poin' : 'Top Up Saldo')) }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $deposit->isRefundCredit() ? 'Nominal Refund' : ($deposit->isMembershipPayment() ? 'Biaya Membership' : 'Nominal Masuk Saldo') }}</p>
                            <p class="mt-1 text-lg font-black text-[#4bc6b9]">Rp {{ number_format($deposit->amount ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Jumlah Dibayar</p>
                            <p class="mt-1 text-lg font-black text-[#5a76c8]">Rp {{ number_format($deposit->paid_amount ?? $deposit->amount ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $deposit->isRefundCredit() ? 'Referensi Refund' : 'Referensi QRIS' }}</p>
                            <p class="mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $deposit->isRefundCredit() ? (data_get($deposit->payment_response, 'source_invoice_number') ?: '-') : ($deposit->tripay_reference ?? '-') }}</p>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        @endforeach
        
        <!-- Paginasi -->
        @if(isset($deposits) && method_exists($deposits, 'links') && $deposits->hasPages())
            <div class="p-5 sm:p-8 w-full min-w-0">
                {{ $deposits->links() }}
            </div>
        @endif
    </div>
</div>
<script>
    function openAdminDepositDetail(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('modal-active');
        document.body.classList.add('admin-detail-open');
    }

    function closeAdminDepositDetail(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('modal-active');
        document.body.classList.remove('admin-detail-open');
    }

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('.admin-detail-modal.modal-active').forEach((modal) => {
            modal.classList.remove('modal-active');
        });
        document.body.classList.remove('admin-detail-open');
    });
</script>
@endsection
