@extends('layouts.admin')

@section('title', 'Laporan Kendala')
@section('admin_header_subtitle', 'Pantau laporan kendala pelanggan.')

@section('content')
<style>
    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; border: 2px solid #f0f5ff; }
    .table-scroll::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
    .admin-detail-modal {
        display: none;
        top: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        left: 0 !important;
        background: rgba(43, 58, 103, .58) !important;
        -webkit-backdrop-filter: blur(8px);
        backdrop-filter: blur(8px);
        overflow: hidden;
        overscroll-behavior: contain;
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
        width: min(100%, 44rem);
        max-height: calc(100dvh - 9rem) !important;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    @media (min-width: 640px) {
        .admin-detail-card { max-height: calc(100dvh - 9.5rem) !important; }
    }
    @media (max-width: 767px) {
        .admin-detail-modal {
            top: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            left: 0 !important;
            z-index: 99999 !important;
            height: auto !important;
            padding: 8.5rem .9rem 1rem !important;
            background: rgba(43, 58, 103, .64) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            backdrop-filter: blur(8px) !important;
            align-items: flex-start !important;
            justify-content: center !important;
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
        padding-bottom: .85rem;
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
    .admin-detail-body::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
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
    .admin-detail-close {
        box-shadow: 0 10px 22px rgba(255,107,107,.14), inset 0 1px 0 rgba(255,255,255,.85);
    }
</style>

<div class="wiboost-font relative z-10 w-full min-w-0 pb-12">
    @php
        $searchValue = $search ?? request('search');
        $activeReportFilter = $reportFilter ?? request('report_filter', 'all');
        $reportFilters = [
            'all' => ['label' => 'Semua'],
            'new' => ['label' => 'Baru'],
            'read' => ['label' => 'Dibaca'],
            'closed' => ['label' => 'Selesai'],
        ];
    @endphp

    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-sm font-black text-emerald-500 shadow-lg shadow-emerald-100/50 sm:px-6 sm:text-base">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="relative z-10 mb-6 w-full min-w-0 rounded-[2rem] border-4 border-white bg-white/90 p-4 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm transition-transform duration-300 hover:border-[#bde0fe] sm:mb-8 sm:rounded-[2.5rem] sm:p-5">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="flex w-full flex-col gap-3 md:flex-row">
            <div class="flex w-full min-w-0 flex-1 gap-2">
                @if($activeReportFilter !== 'all')
                    <input type="hidden" name="report_filter" value="{{ $activeReportFilter }}">
                @endif
                <input type="text" name="search" value="{{ $searchValue }}"
                       class="min-w-0 flex-1 truncate rounded-[1rem] border-4 border-white bg-[#f4f9ff] px-4 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:rounded-[1.2rem] sm:px-5 sm:text-sm"
                       placeholder="Cari invoice, pelanggan, atau kendala...">
                <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-white shadow-md transition-transform active:scale-95 sm:rounded-[1.2rem]" title="Cari Laporan">
                    @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                </button>
                @if($searchValue)
                    <a href="{{ route('admin.reports.index', array_filter(['report_filter' => $activeReportFilter === 'all' ? null : $activeReportFilter])) }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-md transition-transform active:scale-95 sm:rounded-[1.2rem]" title="Reset">
                        @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                    </a>
                @endif
            </div>

            <a href="{{ route('admin.reports.index', array_filter(['report_filter' => $activeReportFilter === 'all' ? null : $activeReportFilter])) }}" class="flex h-12 w-full shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#8faaf3] px-6 py-3 text-sm font-black text-white shadow-md transition-transform hover:bg-[#6f8ddc] active:scale-95 md:w-auto sm:rounded-[1.2rem]">
                @include('partials.wiboost-icon', ['name' => 'refresh', 'class' => 'h-5 w-5'])
                <span class="whitespace-nowrap">Segarkan</span>
            </a>
        </form>
    </div>

    <section class="relative z-10 mb-6 overflow-hidden rounded-[2rem] border-4 border-white bg-white shadow-xl shadow-[#bde0fe]/20 sm:mb-8 sm:rounded-[2.5rem]">
        <div class="flex flex-col gap-3 border-b-4 border-dashed border-[#f4f9ff] bg-[#f8faff] px-5 py-5 sm:px-6 sm:py-6 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3] mb-1">Kontak Admin</p>
                <h3 class="text-xl font-black tracking-tight text-[#2b3a67] sm:text-2xl">Nomor WhatsApp Aktif</h3>
            </div>
            <div class="grid grid-cols-2 gap-2 text-[9px] font-black uppercase tracking-widest text-[#5a76c8] sm:text-[10px]">
                <span class="min-w-0 rounded-full border-2 border-white bg-[#f4f9ff] px-3 py-2 text-center shadow-sm sm:px-4">CS: {{ filled($adminCsWhatsapp ?? null) ? '+' . $adminCsWhatsapp : '-' }}</span>
                <span class="min-w-0 rounded-full border-2 border-white bg-[#f4f9ff] px-3 py-2 text-center shadow-sm sm:px-4">OTP: {{ filled($adminOtpWhatsapp ?? null) ? '+' . $adminOtpWhatsapp : '-' }}</span>
            </div>
        </div>

        <form action="{{ route('admin.dashboard.contact-numbers.update') }}" method="POST" class="p-4 sm:p-6" x-data="{ cs: @js(old('admin_cs_whatsapp', $adminCsWhatsapp ?? '')), otp: @js(old('admin_otp_whatsapp', $adminOtpWhatsapp ?? '')), originalCs: @js($adminCsWhatsapp ?? ''), originalOtp: @js($adminOtpWhatsapp ?? '') }">
            @csrf
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="rounded-[1.6rem] border-4 border-white bg-[#f8fbff] p-4 shadow-inner">
                    <label for="admin_cs_whatsapp" class="mb-2 ml-2 block text-[10px] font-black uppercase tracking-[0.25em] text-[#8faaf3]">Nomor Admin CS</label>
                    <input id="admin_cs_whatsapp" type="text" name="admin_cs_whatsapp" x-model="cs" value="{{ old('admin_cs_whatsapp', $adminCsWhatsapp ?? '') }}" placeholder="0895611244000" class="w-full rounded-[1.25rem] border-4 border-white bg-white px-5 py-4 text-sm font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]">
                    <p class="mt-2 ml-2 text-[10px] font-bold text-[#8faaf3]">Dipakai untuk tombol WhatsApp dan bantuan laporan.</p>
                </div>

                <div class="rounded-[1.6rem] border-4 border-white bg-[#f8fbff] p-4 shadow-inner">
                    <label for="admin_otp_whatsapp" class="mb-2 ml-2 block text-[10px] font-black uppercase tracking-[0.25em] text-[#8faaf3]">Nomor Admin OTP</label>
                    <input id="admin_otp_whatsapp" type="text" name="admin_otp_whatsapp" x-model="otp" value="{{ old('admin_otp_whatsapp', $adminOtpWhatsapp ?? '') }}" placeholder="0895611244000" class="w-full rounded-[1.25rem] border-4 border-white bg-white px-5 py-4 text-sm font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]">
                    <p class="mt-2 ml-2 text-[10px] font-bold text-[#8faaf3]">Dipakai khusus tombol minta OTP di detail pesanan user.</p>
                </div>
            </div>

            <div class="mt-5 flex justify-start">
                <button x-cloak x-show="cs !== originalCs || otp !== originalOtp" type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-[#5a76c8] px-7 py-3 text-sm font-black text-white shadow-lg shadow-[#5a76c8]/25 transition-transform active:scale-95 sm:w-auto">
                    @include('partials.wiboost-icon', ['name' => 'save', 'class' => 'h-5 w-5'])
                    Simpan Nomor
                </button>
            </div>
        </form>
    </section>

    @include('admin.partials.filter-tabs', [
        'filters' => $reportFilters,
        'activeFilter' => $activeReportFilter,
        'filterParam' => 'report_filter',
        'filterRoute' => 'admin.reports.index',
        'filterQuery' => ['search' => $searchValue],
    ])

    <div class="relative z-10 w-full min-w-0 overflow-hidden rounded-[2rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:rounded-[2.5rem]">
        <div class="table-scroll w-full overflow-x-auto pb-2">
            <div class="inline-block min-w-full p-4 align-middle sm:p-6">
                <table class="w-full min-w-[1260px] whitespace-nowrap text-left">
                    <thead>
                        <tr>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Invoice</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Email</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Jenis</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Kendala</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Bukti</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($reports ?? [] as $report)
                            @php
                                $proofUrl = $report->proof_url;
                                $isHandled = $report->status === 'closed';
                                $statusClass = $isHandled ? 'bg-[#e6fff7] text-emerald-500' : 'bg-[#fff5eb] text-amber-500';
                                $statusText = $isHandled ? 'Sudah Ditangani' : 'Belum Ditangani';
                            @endphp
                            <tr onclick="openAdminReportDetail('admin-report-detail-{{ $report->id }}')" class="group cursor-pointer transition-colors hover:bg-[#f8faff]">
                                <td class="min-w-[180px] px-5 py-5 align-middle">
                                    <p class="inline-flex whitespace-nowrap rounded-md border border-white bg-[#f4f9ff] px-2 py-1 text-xs font-black text-[#5a76c8]">
                                        {{ $report->related_invoice ?: 'LAP-' . str_pad((string) $report->id, 5, '0', STR_PAD_LEFT) }}
                                    </p>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <p class="whitespace-nowrap text-sm font-black text-[#2b3a67]">{{ $report->created_at?->format('d M Y, H:i') }}</p>
                                </td>
                                <td class="min-w-[180px] whitespace-nowrap px-5 py-5 align-middle">
                                    <p class="whitespace-nowrap text-sm font-black leading-snug text-[#2b3a67]">{{ $report->user?->name ?: '-' }}</p>
                                </td>
                                <td class="min-w-[240px] whitespace-nowrap px-5 py-5 align-middle">
                                    <p class="whitespace-nowrap text-[10px] font-bold text-[#8faaf3]">{{ $report->user?->email ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <span class="inline-flex rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1.5 text-[9px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                                        {{ $report->category_label }}
                                    </span>
                                </td>
                                <td class="min-w-[260px] max-w-[330px] whitespace-normal px-5 py-5 align-middle">
                                    <p class="admin-clamp-3 text-xs font-bold leading-relaxed text-[#2b3a67]">{{ $report->issue }}</p>
                                </td>
                                <td class="px-5 py-5 text-center align-middle">
                                    @if($proofUrl)
                                        <a href="{{ $proofUrl }}" target="_blank" onclick="event.stopPropagation()" class="inline-flex rounded-full border-2 border-white bg-[#fff7e0] px-4 py-1.5 text-[9px] font-black uppercase tracking-widest text-[#d38b00] shadow-sm">
                                            Lihat
                                        </a>
                                    @else
                                        <span class="text-sm font-black text-[#a3bbfb]">-</span>
                                    @endif
                                </td>
                                <td class="min-w-[150px] px-5 py-5 text-center align-middle">
                                    <span class="{{ $statusClass }} inline-flex w-full justify-center rounded-full border-2 border-white px-4 py-1.5 text-[9px] font-black uppercase tracking-widest shadow-sm">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="px-5 py-5 text-center align-middle">
                                    <form method="POST" action="{{ route('admin.reports.update', $report) }}" onclick="event.stopPropagation()" class="inline-flex" data-no-submit-lock="true">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $isHandled ? 'new' : 'closed' }}">
                                        <button type="submit" class="{{ $isHandled ? 'bg-[#ffe5e5] text-[#ff6b6b] shadow-[#ff6b6b]/15' : 'bg-[#e6fff7] text-emerald-500 shadow-emerald-100/70' }} flex h-10 w-10 items-center justify-center rounded-[1rem] border-2 border-white text-lg font-black shadow-md transition-transform hover:scale-105 active:scale-95" title="{{ $isHandled ? 'Tandai belum ditangani' : 'Tandai sudah ditangani' }}">
                                            @include('partials.wiboost-icon', ['name' => $isHandled ? 'x' : 'success', 'class' => 'h-5 w-5'])
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-20 text-center">
                                    <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-[2rem] border-4 border-white bg-[#f0f5ff] text-4xl shadow-inner">
                                        @include('partials.wiboost-icon', ['name' => 'note', 'class' => 'h-10 w-10'])
                                    </div>
                                    <p class="text-xl font-black text-[#5a76c8]">Belum ada laporan kendala.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @foreach($reports ?? [] as $report)
            @php
                $proofUrl = $report->proof_url;
                $snapshot = $report->related_snapshot ?: [];
                $isHandled = $report->status === 'closed';
                $statusText = $isHandled ? 'Sudah Ditangani' : 'Belum Ditangani';
                $statusClass = $isHandled ? 'bg-[#e6fff7] text-emerald-500' : 'bg-[#fff5eb] text-amber-500';
                $isFilledReportValue = function ($value): bool {
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
                $appendReportDetailLine = function (array &$lines, string $label, $value) use ($isFilledReportValue): void {
                    if (! $isFilledReportValue($value)) {
                        return;
                    }

                    $lines[] = [
                        'label' => $label,
                        'value' => trim((string) $value),
                    ];
                };
                $parseReportTextLines = function ($text) use ($appendReportDetailLine): array {
                    $lines = [];

                    foreach (preg_split('/\R/', trim((string) $text)) ?: [] as $line) {
                        $line = trim((string) $line);

                        if ($line === '') {
                            continue;
                        }

                        if (str_contains($line, ':')) {
                            [$label, $value] = array_map('trim', explode(':', $line, 2));
                            $appendReportDetailLine($lines, $label !== '' ? $label : 'Data', $value);
                        } else {
                            $appendReportDetailLine($lines, 'Data', $line);
                        }
                    }

                    return $lines;
                };
                $relatedSections = [];

                if (($report->category_key ?? null) === 'deposit') {
                    $depositLines = [];
                    $appendReportDetailLine($depositLines, 'Invoice', $snapshot['invoice'] ?? $report->related_invoice);
                    $appendReportDetailLine($depositLines, 'Nominal Masuk Saldo', $snapshot['amount'] ?? null);
                    $appendReportDetailLine($depositLines, 'Jumlah Dibayar', $snapshot['paidAmount'] ?? null);
                    $appendReportDetailLine($depositLines, 'Metode', $snapshot['method'] ?? null);
                    $appendReportDetailLine($depositLines, 'Referensi QRIS', $snapshot['reference'] ?? null);
                    $appendReportDetailLine($depositLines, 'Waktu', $snapshot['created'] ?? null);

                    if ($depositLines !== []) {
                        $relatedSections[] = ['title' => 'Data Deposit', 'lines' => $depositLines];
                    }
                } elseif ($snapshot !== []) {
                    if ($isFilledReportValue($snapshot['loginProofUrl'] ?? null)) {
                        $relatedSections[] = [
                            'title' => 'Bukti Login',
                            'lines' => [[
                                'label' => 'Link Bukti Login',
                                'value' => trim((string) $snapshot['loginProofUrl']),
                            ]],
                        ];
                    }

                    $orderLines = [];
                    $appendReportDetailLine($orderLines, 'Invoice', $snapshot['invoice'] ?? $report->related_invoice);
                    $appendReportDetailLine($orderLines, 'Kategori', $snapshot['category'] ?? $report->category_label);
                    $appendReportDetailLine($orderLines, 'Produk', $snapshot['product'] ?? $report->related_title);
                    $appendReportDetailLine($orderLines, 'Total Bayar', $snapshot['amount'] ?? null);
                    $appendReportDetailLine($orderLines, 'Metode', $snapshot['payment'] ?? null);
                    $appendReportDetailLine($orderLines, 'Status Pesanan', $snapshot['status'] ?? null);
                    $appendReportDetailLine($orderLines, 'ID Provider', $snapshot['providerId'] ?? null);
                    $appendReportDetailLine($orderLines, 'Waktu', $snapshot['created'] ?? null);

                    if ($orderLines !== []) {
                        $relatedSections[] = ['title' => 'Data Pesanan', 'lines' => $orderLines];
                    }

                    $accountLines = [];
                    foreach (($snapshot['accountLines'] ?? []) as $accountLine) {
                        foreach ($parseReportTextLines($accountLine) as $parsedLine) {
                            $accountLines[] = $parsedLine;
                        }
                    }

                    if ($accountLines !== []) {
                        $relatedSections[] = ['title' => 'Detail Akun', 'lines' => $accountLines];
                    }

                    $inputBuyerLines = $parseReportTextLines($snapshot['target'] ?? '');

                    if ($inputBuyerLines !== []) {
                        $relatedSections[] = ['title' => 'Input Pembeli', 'lines' => $inputBuyerLines];
                    }
                }
            @endphp
            <div id="admin-report-detail-{{ $report->id }}" class="admin-detail-modal fixed inset-0 z-[9999] items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
                <div class="absolute inset-0" onclick="closeAdminReportDetail('admin-report-detail-{{ $report->id }}')"></div>
                <div class="admin-detail-card relative flex w-full max-w-2xl max-h-[calc(100dvh-8.25rem)] flex-col rounded-[1.75rem] border-4 border-white bg-white p-4 shadow-2xl shadow-[#2b3a67]/20 transform scale-95 transition-transform duration-200 sm:p-5">
                    <div class="admin-detail-header mb-3.5 flex items-start justify-between gap-3 p-3.5">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Detail Laporan</p>
                            <h3 class="mt-1 break-all text-xl font-black text-[#2b3a67]">{{ $report->related_invoice ?: 'LAP-' . str_pad((string) $report->id, 5, '0', STR_PAD_LEFT) }}</h3>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $report->created_at?->format('d M Y, H:i') }}</p>
                        </div>
                        <button type="button" onclick="closeAdminReportDetail('admin-report-detail-{{ $report->id }}')" class="admin-detail-close flex h-9 w-9 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#ffe5e5] text-lg font-black text-[#ff6b6b]">
                            @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-4 w-4'])
                        </button>
                    </div>

                    <div class="admin-detail-body">
                        <div class="grid gap-3.5 md:grid-cols-2">
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Pelanggan</p>
                                <p class="mt-1 font-black text-[#2b3a67]">{{ $report->user?->name ?: '-' }}</p>
                                <p class="mt-1 break-all text-xs font-bold text-[#8faaf3]">{{ $report->user?->email ?: '-' }}</p>
                                @if($report->user?->whatsapp)
                                    <p class="mt-1 break-all text-xs font-bold text-[#8faaf3]">WhatsApp: {{ $report->user->whatsapp }}</p>
                                @endif
                            </div>
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</p>
                                <span class="{{ $statusClass }} mt-2 inline-flex rounded-full border-2 border-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest shadow-sm">{{ $statusText }}</span>
                            </div>
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Jenis</p>
                                <p class="mt-1 font-black text-[#2b3a67]">{{ $report->category_label }}</p>
                            </div>
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Produk / Judul</p>
                                <p class="mt-1 break-words font-black text-[#2b3a67]">{{ $report->related_title ?: '-' }}</p>
                            </div>
                            <div class="admin-detail-panel md:col-span-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Kendala</p>
                                <p class="mt-2 whitespace-pre-wrap break-words text-sm font-bold leading-relaxed text-[#2b3a67]">{{ $report->issue }}</p>
                            </div>
                            <div class="admin-detail-panel md:col-span-2">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Bukti Kendala</p>
                                    @if($proofUrl)
                                        <a href="{{ $proofUrl }}" target="_blank" class="w-fit rounded-full border-2 border-white bg-[#5a76c8] px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white shadow-sm">Buka Gambar</a>
                                    @endif
                                </div>
                                @if($proofUrl)
                                    <div class="mt-3 overflow-hidden rounded-[1.2rem] border-4 border-white bg-white shadow-inner">
                                        <img src="{{ $proofUrl }}" alt="Bukti laporan #{{ $report->id }}" class="max-h-72 w-full object-contain">
                                    </div>
                                    <p class="mt-2 break-all text-xs font-bold text-[#8faaf3]">{{ $report->proof_original_name ?: $proofUrl }}</p>
                                @else
                                    <p class="mt-2 text-sm font-bold text-[#8faaf3]">Tidak ada bukti.</p>
                                @endif
                            </div>
                            <div class="admin-detail-panel admin-detail-panel-soft md:col-span-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Data Terkait</p>
                                @if($relatedSections !== [])
                                    <div class="mt-3 space-y-3">
                                        @foreach($relatedSections as $section)
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
                    </div>
                </div>
            </div>
        @endforeach

        @if(isset($reports) && method_exists($reports, 'links') && $reports->hasPages())
            <div class="w-full min-w-0 border-t-4 border-dashed border-[#f4f9ff] bg-[#f8faff] p-5 sm:p-8">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function openAdminReportDetail(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('modal-active');
        document.body.classList.add('overflow-hidden', 'admin-detail-open');
    }

    function closeAdminReportDetail(id) {
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
        document.body.classList.remove('overflow-hidden', 'admin-detail-open');
    });
</script>
@endsection
