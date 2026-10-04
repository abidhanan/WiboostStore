@extends('layouts.admin')

@section('title', 'OTP')

@section('content')
@php
    $searchValue = $search ?? '';
    $selectedMonth = (string) ($selectedMonth ?? now()->format('m'));
    $selectedYear = (string) ($selectedYear ?? now()->format('Y'));
    $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $returnQuery = array_filter([
        'search' => $searchValue,
        'month' => $selectedMonth,
        'year' => $selectedYear,
    ]);
@endphp

<style>
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

<div class="wiboost-font relative z-10 mx-auto flex max-w-7xl flex-col pb-12">
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-sm font-black text-emerald-500 shadow-lg shadow-emerald-100/50 sm:px-6 sm:text-base">
            @include('partials.wiboost-icon', ['name' => 'success', 'class' => 'h-5 w-5 shrink-0'])
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('admin.otp.admins.save') }}" method="POST" class="relative z-10 order-2 mb-6 rounded-[2rem] border-4 border-white bg-white p-4 shadow-xl shadow-[#bde0fe]/20 sm:mb-8 sm:rounded-[2.5rem] sm:p-6" style="order: 2;" x-data="otpAdminManager(@js($formOtpAdmins), @js($savedOtpAdmins))">
        @csrf

        <div class="mb-5 flex items-center justify-between gap-4 border-b-4 border-dashed border-[#f4f9ff] pb-5">
            <div class="min-w-0">
                <p class="mb-1 text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Kontak Admin</p>
                <h2 class="text-xl font-black tracking-tight text-[#2b3a67] sm:text-2xl">Admin OTP</h2>
            </div>
            <button type="button" @click="add()" class="flex h-12 shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#5a76c8] px-4 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:px-5" title="Tambah Nomor">
                @include('partials.wiboost-icon', ['name' => 'plus', 'class' => 'h-5 w-5'])
                <span class="whitespace-nowrap">Tambah Nomor</span>
            </button>
        </div>

        <div class="space-y-4">
            <template x-for="(admin, index) in rows" :key="admin.key">
                <div class="grid items-end gap-2 sm:gap-4" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) 3rem;">
                    <div class="min-w-0 rounded-[1.6rem] border-4 border-white bg-[#f8fbff] p-3 shadow-inner sm:p-4">
                        <label class="mb-2 ml-1 block whitespace-nowrap text-[8px] font-black uppercase tracking-widest text-[#8faaf3] sm:ml-2 sm:text-[10px] sm:tracking-[0.25em]">NAMA ADMIN OTP</label>
                        <input type="text" :name="'admins[' + index + '][name]'" x-model="admin.name" data-wiboost-name-only class="w-full min-w-0 rounded-[1rem] border-4 border-white bg-white px-3 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe] sm:rounded-[1.25rem] sm:px-5 sm:py-4 sm:text-sm">
                    </div>
                    <div class="min-w-0 rounded-[1.6rem] border-4 border-white bg-[#f8fbff] p-3 shadow-inner sm:p-4">
                        <label class="mb-2 ml-1 block whitespace-nowrap text-[8px] font-black uppercase tracking-widest text-[#8faaf3] sm:ml-2 sm:text-[10px] sm:tracking-[0.25em]">NOMOR ADMIN OTP</label>
                        <input type="text" :name="'admins[' + index + '][whatsapp]'" x-model="admin.whatsapp" inputmode="numeric" pattern="[0-9]*" data-wiboost-number-only class="w-full min-w-0 rounded-[1rem] border-4 border-white bg-white px-3 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe] sm:rounded-[1.25rem] sm:px-5 sm:py-4 sm:text-sm">
                    </div>
                    <button type="button" @click="remove(index)" class="flex h-12 w-12 shrink-0 items-center justify-center justify-self-end rounded-[1rem] border-2 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-md transition-transform hover:scale-105 active:scale-95" style="align-self: center;" title="Hapus Nomor">
                        @include('partials.wiboost-icon', ['name' => 'trash', 'class' => 'h-5 w-5'])
                    </button>
                </div>
            </template>

            <div x-show="rows.length === 0" x-cloak class="rounded-[1.6rem] border-4 border-dashed border-[#dbe8ff] bg-[#f8fbff] px-5 py-8 text-center text-sm font-black text-[#8faaf3]">
                Belum ada nomor admin.
            </div>
        </div>

        @if($errors->any())
            <div class="mt-4 rounded-[1.25rem] border-2 border-white bg-[#ffe5e5] px-4 py-3 text-xs font-black text-[#ff6b6b]">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="mt-5 flex items-center justify-end gap-3">
            <button x-cloak x-show="changed()" type="submit" class="inline-flex items-center justify-center gap-2 rounded-full border-4 border-white bg-[#5a76c8] px-7 py-3 text-sm font-black text-white shadow-lg shadow-[#5a76c8]/25 transition-transform active:scale-95">
                @include('partials.wiboost-icon', ['name' => 'save', 'class' => 'h-5 w-5'])
                Simpan Nomor
            </button>
        </div>
    </form>

    <div class="relative z-10 order-1 mb-6 w-full min-w-0 rounded-[2rem] border-4 border-white bg-white/90 p-4 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:mb-8 sm:rounded-[2.5rem] sm:p-5" style="order: 1;">
        <form action="{{ route('admin.otp.index') }}" method="GET" class="flex w-full flex-col gap-3">
            <div class="flex w-full flex-col gap-3 md:flex-row">
                <div class="flex min-w-0 flex-1 gap-2">
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <input type="text" name="search" value="{{ $searchValue }}" class="min-w-0 flex-1 truncate rounded-[1rem] border-4 border-white bg-[#f4f9ff] px-4 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:rounded-[1.2rem] sm:px-5 sm:text-sm" placeholder="Cari invoice, pengguna, produk, atau admin...">
                    <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-white shadow-md transition-transform active:scale-95" title="Cari">
                        @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                    </button>
                    @if($searchValue)
                        <a href="{{ route('admin.otp.index', ['month' => $selectedMonth, 'year' => $selectedYear]) }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-md transition-transform active:scale-95" title="Reset">
                            @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                        </a>
                    @endif
                </div>
                <a href="{{ route('admin.otp.index', ['month' => $selectedMonth, 'year' => $selectedYear]) }}" class="flex h-12 w-full shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#8faaf3] px-6 py-3 text-sm font-black text-white shadow-md transition-transform hover:bg-[#6f8ddc] active:scale-95 md:w-auto">
                    @include('partials.wiboost-icon', ['name' => 'refresh', 'class' => 'h-5 w-5'])
                    <span>Segarkan</span>
                </a>
            </div>

            <div class="mt-1 flex w-full flex-col items-center justify-start gap-3 border-t-4 border-dashed border-[#f4f9ff] pt-4 md:flex-row md:justify-end">
                <div class="flex w-full items-center gap-2 md:w-auto">
                    <div class="admin-theme-select admin-theme-select-month" data-admin-theme-select data-submit-on-change="true" data-input-name="month">
                        <button type="button" class="admin-theme-select-button rounded-[1rem] px-4 py-2 text-xs font-black sm:text-sm"><span data-admin-theme-select-label>{{ $monthNames[(int) $selectedMonth] ?? '-' }}</span><span class="admin-theme-select-arrow">v</span></button>
                        <div class="admin-theme-select-menu">
                            @foreach($monthNames as $monthNumber => $monthName)
                                <button type="button" class="admin-theme-select-option {{ $selectedMonth === sprintf('%02d', $monthNumber) ? 'is-selected' : '' }}" data-value="{{ sprintf('%02d', $monthNumber) }}" data-label="{{ $monthName }}">{{ $monthName }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div class="admin-theme-select admin-theme-select-year" data-admin-theme-select data-submit-on-change="true" data-input-name="year">
                        <button type="button" class="admin-theme-select-button rounded-[1rem] px-4 py-2 text-xs font-black sm:text-sm"><span data-admin-theme-select-label>{{ $selectedYear }}</span><span class="admin-theme-select-arrow">v</span></button>
                        <div class="admin-theme-select-menu">
                            @for($year = now()->year + 1; $year >= now()->year - 10; $year--)
                                <button type="button" class="admin-theme-select-option {{ $selectedYear === (string) $year ? 'is-selected' : '' }}" data-value="{{ $year }}" data-label="{{ $year }}">{{ $year }}</button>
                            @endfor
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.otp.export_excel', $returnQuery) }}" target="_blank" class="flex h-12 w-full shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#4bc6b9] px-6 py-3 text-sm font-black text-white shadow-md shadow-[#4bc6b9]/25 transition-transform hover:bg-[#3ba398] active:scale-95 md:w-auto">
                    @include('partials.wiboost-icon', ['name' => 'chart', 'class' => 'h-5 w-5'])
                    <span class="whitespace-nowrap">Cetak Laporan</span>
                </a>
            </div>
        </form>
    </div>

    <div class="relative z-10 order-3 w-full min-w-0 overflow-hidden rounded-[2rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem]" style="order: 3;">
        <div class="table-scroll w-full overflow-x-auto">
            <div class="inline-block min-w-full p-4 align-middle sm:p-6">
                <table class="w-full whitespace-nowrap text-left" style="min-width:2100px;table-layout:fixed;">
                    <colgroup>
                        <col style="width:190px">
                        <col style="width:150px">
                        <col style="width:240px">
                        <col style="width:260px">
                        <col style="width:180px">
                        <col style="width:360px">
                        <col style="width:220px">
                        <col style="width:220px">
                        <col style="width:170px">
                        <col style="width:110px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Invoice</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-6 py-4 pr-8 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Email</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nomor Kontak</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Produk</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama Admin OTP</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nomor Admin OTP</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($requests as $otpRequest)
                            @php
                                $handled = $otpRequest->status === 'handled';
                            @endphp
                            <tr onclick="openOtpRequestDetail('otp-request-detail-{{ $otpRequest->id }}')" class="cursor-pointer transition-colors hover:bg-[#f8faff]">
                                <td class="px-5 py-5 align-top"><span class="inline-block whitespace-nowrap rounded-md border border-white bg-[#f4f9ff] px-2 py-1 text-xs font-black text-[#5a76c8]">{{ $otpRequest->invoice_number }}</span></td>
                                <td class="px-6 py-5 pr-8 align-top"><p class="whitespace-nowrap text-[10px] font-bold text-[#8faaf3]">{{ $otpRequest->requested_at?->format('d M Y, H:i') }}</p></td>
                                <td class="px-5 py-5 align-top"><p class="truncate whitespace-nowrap text-sm font-black text-[#2b3a67]" title="{{ $otpRequest->user?->name ?: '-' }}">{{ $otpRequest->user?->name ?: '-' }}</p></td>
                                <td class="px-5 py-5 align-top"><p class="truncate whitespace-nowrap text-[10px] font-bold text-[#8faaf3]" title="{{ $otpRequest->user?->email ?: '-' }}">{{ $otpRequest->user?->email ?: '-' }}</p></td>
                                <td class="px-5 py-5 align-top"><p class="whitespace-nowrap text-xs font-bold text-[#5a76c8]">{{ $otpRequest->user?->whatsapp ?: '-' }}</p></td>
                                <td class="whitespace-normal px-5 py-5 align-top"><p class="text-xs font-black leading-tight text-[#2b3a67]" style="display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden;overflow-wrap:anywhere;" title="{{ $otpRequest->product_name }}">{{ $otpRequest->product_name ?: '-' }}</p></td>
                                <td class="px-5 py-5 align-top"><p class="truncate whitespace-nowrap text-xs font-black text-[#2b3a67]" title="{{ $otpRequest->otp_admin_name ?: '-' }}">{{ $otpRequest->otp_admin_name ?: '-' }}</p></td>
                                <td class="px-5 py-5 align-top"><p class="whitespace-nowrap text-xs font-bold text-[#5a76c8]">{{ $otpRequest->otp_admin_whatsapp ? '+' . $otpRequest->otp_admin_whatsapp : '-' }}</p></td>
                                <td class="px-5 py-5 text-center align-top"><span class="{{ $handled ? 'bg-[#e6fff7] text-emerald-500' : 'bg-[#fff5eb] text-amber-500' }} inline-flex whitespace-nowrap rounded-full border-2 border-white px-4 py-1.5 text-[9px] font-black uppercase tracking-widest shadow-sm">{{ $otpRequest->status_label }}</span></td>
                                <td class="px-5 py-5 text-center align-top">
                                    <form action="{{ route('admin.otp.requests.update', $otpRequest) }}" method="POST" onclick="event.stopPropagation()" data-no-submit-lock="true" class="inline-flex">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $handled ? 'pending' : 'handled' }}">
                                        <button type="submit" class="{{ $handled ? 'bg-[#ffe5e5] text-[#ff6b6b]' : 'bg-[#e6fff7] text-emerald-500' }} flex h-10 w-10 items-center justify-center rounded-[1rem] border-2 border-white shadow-md transition-transform hover:scale-105 active:scale-95" title="{{ $handled ? 'Tandai belum' : 'Tandai ditangani' }}">@include('partials.wiboost-icon', ['name' => $handled ? 'x' : 'success', 'class' => 'h-4 w-4'])</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="px-5 py-20 text-center text-lg font-black text-[#8faaf3]">Belum ada permintaan OTP.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @foreach($requests as $otpRequest)
            @php
                $handled = $otpRequest->status === 'handled';
                $otpTransaction = $otpRequest->transaction;
                $otpRelatedSections = [];
                $otpAppendLine = static function (array &$lines, string $label, $value): void {
                    if ($value === null || is_array($value) || trim((string) $value) === '') {
                        return;
                    }
                    $lines[] = ['label' => $label, 'value' => trim((string) $value)];
                };

                if ($otpTransaction) {
                    $otpOrderData = \App\Support\WiboostOrderDetailData::forTransaction($otpTransaction);
                    $orderLines = [];
                    $otpAppendLine($orderLines, 'Invoice', $otpOrderData['invoice'] ?? null);
                    $otpAppendLine($orderLines, 'Kategori', $otpOrderData['category'] ?? null);
                    $otpAppendLine($orderLines, 'Produk', $otpOrderData['product'] ?? null);
                    $otpAppendLine($orderLines, 'Total Bayar', $otpOrderData['total'] ?? null);
                    $otpAppendLine($orderLines, 'Metode Pembayaran', $otpOrderData['payment'] ?? null);
                    $otpAppendLine($orderLines, 'Status Pesanan', $otpOrderData['status'] ?? null);
                    $otpAppendLine($orderLines, 'ID Penyedia', $otpOrderData['provider_id'] ?? null);
                    $otpAppendLine($orderLines, 'Waktu Transaksi', $otpOrderData['time'] ?? null);
                    if ($orderLines !== []) {
                        $otpRelatedSections[] = ['title' => 'Data Pesanan', 'lines' => $orderLines];
                    }

                    $credentialLabels = [
                        'credential_id' => 'ID Kredensial', 'email' => 'Email', 'username' => 'Nama Pengguna',
                        'password' => 'Kata Sandi', 'profile' => 'Profil', 'pin' => 'PIN', 'link' => 'Tautan Akses',
                        'url' => 'Tautan', 'tutorial_link' => 'Tautan Tutorial', 'notes' => 'Catatan',
                        'needs_otp' => 'Butuh OTP', 'requires_otp' => 'Butuh OTP',
                        'otp_admin_name' => 'Nama Admin OTP', 'otp_admin_whatsapp' => 'Nomor Admin OTP',
                        'duration_label' => 'Durasi', 'credential_started_at' => 'Mulai Akses',
                        'credential_expires_at' => 'Waktu Habis',
                    ];
                    $credentialLines = [];
                    foreach ((array) ($otpTransaction->credential_data ?? []) as $key => $value) {
                        if (is_array($value) || $value === null || trim((string) $value) === '') {
                            continue;
                        }
                        $normalized = strtolower((string) $key);
                        if (in_array($normalized, ['needs_otp', 'requires_otp', 'requires_login_proof', 'requires_logout_proof'], true)) {
                            $value = in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'ya', 'on'], true) ? 'Ya' : 'Tidak';
                        }
                        $otpAppendLine($credentialLines, $credentialLabels[$normalized] ?? str($key)->replace('_', ' ')->title(), $value);
                    }
                    if ($credentialLines !== []) {
                        $otpRelatedSections[] = ['title' => 'Detail Akun', 'lines' => $credentialLines];
                    }

                    $inputLines = collect($otpOrderData['input_lines'] ?? [])->map(function ($line) {
                        [$label, $value] = array_pad(explode(':', (string) $line, 2), 2, '-');
                        return ['label' => trim($label), 'value' => trim($value)];
                    })->filter(fn ($line) => $line['label'] !== '')->values()->all();
                    if ($inputLines !== []) {
                        $otpRelatedSections[] = ['title' => 'Input Pembeli', 'lines' => $inputLines];
                    }

                    if (filled($otpTransaction->login_proof_path)) {
                        $otpRelatedSections[] = ['title' => 'Bukti Masuk', 'lines' => [[
                            'label' => 'Tautan Bukti Masuk',
                            'value' => url(\Illuminate\Support\Facades\Storage::url($otpTransaction->login_proof_path)),
                        ]]];
                    }
                }
            @endphp
            <div id="otp-request-detail-{{ $otpRequest->id }}" class="admin-detail-modal fixed inset-0 z-[9999] items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
                <div class="absolute inset-0" onclick="closeOtpRequestDetail('otp-request-detail-{{ $otpRequest->id }}')"></div>
                <div class="admin-detail-card relative flex w-full max-w-4xl flex-col rounded-[1.75rem] border-4 border-white bg-white p-4 shadow-2xl sm:p-5">
                    <div class="admin-detail-header mb-3.5 flex items-start justify-between gap-3 p-3.5">
                        <div><p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Detail Permintaan OTP</p><h2 class="mt-1 text-xl font-black text-[#2b3a67]">{{ $otpRequest->invoice_number }}</h2><p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $otpRequest->requested_at?->format('d M Y, H:i') }}</p></div>
                        <button type="button" onclick="closeOtpRequestDetail('otp-request-detail-{{ $otpRequest->id }}')" class="admin-detail-close flex h-9 w-9 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#ffe5e5] text-[#ff6b6b]">@include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-4 w-4'])</button>
                    </div>
                    <div class="admin-detail-body">
                        <div class="grid gap-3.5 md:grid-cols-2">
                            @foreach([
                            'Nama' => $otpRequest->user?->name,
                            'Email' => $otpRequest->user?->email,
                            'Nomor Kontak' => $otpRequest->user?->whatsapp,
                            'Produk' => $otpRequest->product_name,
                            'Nama Admin OTP' => $otpRequest->otp_admin_name,
                            'Nomor Admin OTP' => $otpRequest->otp_admin_whatsapp ? '+' . $otpRequest->otp_admin_whatsapp : null,
                            ] as $label => $value)
                                <div class="admin-detail-panel"><p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $label }}</p><p class="mt-1 break-words font-bold text-[#2b3a67]">{{ $value ?: '-' }}</p></div>
                            @endforeach
                            <div class="admin-detail-panel md:col-span-2"><p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</p><span class="{{ $handled ? 'bg-[#e6fff7] text-emerald-500' : 'bg-[#fff5eb] text-amber-500' }} mt-2 inline-flex rounded-full border-2 border-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest">{{ $otpRequest->status_label }}</span></div>
                            <div class="admin-detail-panel admin-detail-panel-soft md:col-span-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Data Terkait</p>
                                @if($otpRelatedSections !== [])
                                    <div class="mt-3 space-y-3">
                                        @foreach($otpRelatedSections as $section)
                                            <div class="rounded-[1rem] border-2 border-white bg-white/70 p-3 shadow-sm">
                                                <p class="text-[10px] font-black uppercase tracking-widest text-[#5a76c8]">{{ $section['title'] }}</p>
                                                <div class="admin-detail-subgrid mt-3">
                                                    @foreach($section['lines'] as $line)
                                                        @php
                                                            $otpLineIsUrl = preg_match('/^https?:\/\//i', (string) $line['value']);
                                                        @endphp
                                                        <div class="admin-detail-subfield">
                                                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $line['label'] }}</p>
                                                            @if($otpLineIsUrl)
                                                                <a href="{{ $line['value'] }}" target="_blank" rel="noopener" class="mt-1 block break-all text-sm font-bold text-[#5a76c8] underline decoration-[#bde0fe] underline-offset-4">{{ $line['value'] }}</a>
                                                            @else
                                                                <p class="mt-1 whitespace-pre-line break-words text-sm font-bold text-[#2b3a67]">{{ $line['value'] }}</p>
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

        @if($requests->hasPages())
            <div class="border-t-4 border-dashed border-[#f4f9ff] px-4 py-4 sm:px-6">{{ $requests->links('vendor.pagination.tailwind') }}</div>
        @endif
    </div>
</div>

<script>
    window.otpAdminManager = function (rows, originalRows) {
        const clean = (items) => (items || []).map((item) => ({
            name: String(item.name || '').trim(),
            whatsapp: String(item.whatsapp || '').replace(/\D+/g, ''),
        }));

        return {
            rows: (rows || []).map((item, index) => ({ ...item, key: `${Date.now()}-${index}` })),
            original: clean(originalRows),
            add() {
                this.rows.push({ id: null, name: '', whatsapp: '', key: `${Date.now()}-${Math.random()}` });
                this.$nextTick(() => window.dispatchEvent(new Event('wiboost:content-ready')));
            },
            remove(index) {
                this.rows.splice(index, 1);
            },
            changed() {
                return JSON.stringify(clean(this.rows)) !== JSON.stringify(this.original);
            },
        };
    };

    window.openOtpRequestDetail = function (id) {
        window.openAdminUniversalDetail?.(id);
    };
    window.closeOtpRequestDetail = function (id) {
        window.closeAdminUniversalDetail?.(id);
    };
</script>
@endsection
