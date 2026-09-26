@extends('layouts.admin')

@section('title', 'Durasi Produk')
@section('admin_header_subtitle', 'Pantau produk aplikasi premium yang masa aksesnya sudah habis.')

@section('content')
<style>
    .duration-table-scroll::-webkit-scrollbar { height: 8px; }
    .duration-table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .duration-table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border: 2px solid #f0f5ff; border-radius: 10px; }
    .duration-table-scroll::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
    .duration-product-clamp {
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        display: -webkit-box;
        overflow: hidden;
    }
    .duration-table-row,
    .duration-table-row:hover,
    .duration-table-proof-link,
    .duration-table-proof-link:hover,
    .duration-table-proof-empty,
    .duration-table-proof-link * {
        animation: none !important;
        transform: none !important;
        transition: none !important;
    }
    .duration-table-row:hover {
        background: transparent !important;
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
    @php
        $searchValue = $search ?? request('search');
        $activeDurationFilter = $durationFilter ?? request('duration_filter', 'all');
        $selectedMonth = $selectedMonth ?? request('month', now()->format('m'));
        $selectedYear = $selectedYear ?? request('year', now()->format('Y'));
        $selectedMonthNumber = (int) $selectedMonth;
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
        $durationFilters = [
            'all' => ['label' => 'Semua'],
            'waiting_logout' => ['label' => 'Belum Keluar'],
            'uploaded' => ['label' => 'Sudah Unggah'],
        ];
    @endphp

    <div class="relative z-10 mb-6 w-full min-w-0 rounded-[2rem] border-4 border-white bg-white/90 p-4 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm transition-transform duration-300 hover:border-[#bde0fe] sm:mb-8 sm:rounded-[2.5rem] sm:p-5">
        <form action="{{ route('admin.durations.index') }}" method="GET" class="flex w-full flex-col gap-3">
            <div class="flex w-full flex-col gap-3 md:flex-row">
                <div class="flex w-full min-w-0 flex-1 gap-2">
                    @if($activeDurationFilter !== 'all')
                        <input type="hidden" name="duration_filter" value="{{ $activeDurationFilter }}">
                    @endif
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <input type="text" name="search" value="{{ $searchValue }}"
                        class="min-w-0 flex-1 truncate rounded-[1rem] border-4 border-white bg-[#f4f9ff] px-4 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:rounded-[1.2rem] sm:px-5 sm:text-sm"
                        placeholder="Cari invoice, pelanggan, email, atau produk...">
                    <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-white shadow-md transition-transform active:scale-95 sm:rounded-[1.2rem]" title="Cari Durasi">
                        @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                    </button>
                    @if($searchValue)
                        <a href="{{ route('admin.durations.index', array_filter(['duration_filter' => $activeDurationFilter === 'all' ? null : $activeDurationFilter, 'month' => $selectedMonth, 'year' => $selectedYear])) }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-md transition-transform active:scale-95 sm:rounded-[1.2rem]" title="Reset">
                            @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                        </a>
                    @endif
                </div>

                <a href="{{ route('admin.durations.index', array_filter(['duration_filter' => $activeDurationFilter === 'all' ? null : $activeDurationFilter, 'month' => $selectedMonth, 'year' => $selectedYear])) }}" class="flex h-12 w-full shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#8faaf3] px-6 py-3 text-sm font-black text-white shadow-md transition-transform hover:bg-[#6f8ddc] active:scale-95 md:w-auto sm:rounded-[1.2rem]">
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
                            @for($y = now()->year + 1; $y >= now()->year - 3; $y--)
                                <button type="button" class="admin-theme-select-option {{ (string) $selectedYear === (string) $y ? 'is-selected' : '' }}" data-value="{{ $y }}" data-label="{{ $y }}">{{ $y }}</button>
                            @endfor
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.durations.export_excel', array_filter(['search' => $searchValue, 'duration_filter' => $activeDurationFilter === 'all' ? null : $activeDurationFilter, 'month' => $selectedMonth, 'year' => $selectedYear])) }}" target="_blank" class="flex h-12 w-full shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#4bc6b9] px-6 py-3 text-sm font-black text-white shadow-md shadow-[#4bc6b9]/25 transition-transform hover:bg-[#3ba398] active:scale-95 md:w-auto sm:rounded-[1.2rem]">
                    @include('partials.wiboost-icon', ['name' => 'chart', 'class' => 'h-5 w-5'])
                    <span class="whitespace-nowrap">Cetak Laporan</span>
                </a>
            </div>
        </form>
    </div>

    @include('admin.partials.filter-tabs', [
        'filters' => $durationFilters,
        'activeFilter' => $activeDurationFilter,
        'filterParam' => 'duration_filter',
        'filterRoute' => 'admin.durations.index',
        'filterQuery' => ['search' => $searchValue, 'month' => $selectedMonth, 'year' => $selectedYear],
    ])

    <div class="overflow-hidden rounded-[2rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem]">
        <div class="duration-table-scroll overflow-x-auto p-4 sm:p-6">
            <table class="w-full min-w-[2100px] border-separate border-spacing-y-3 text-left">
                <thead>
                    <tr>
                        <th class="min-w-[255px] border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Invoice</th>
                        <th class="min-w-[190px] border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu Beli</th>
                        <th class="min-w-[190px] border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu Habis</th>
                        <th class="min-w-[245px] border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama</th>
                        <th class="min-w-[360px] border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Email</th>
                        <th class="min-w-[200px] border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nomor Kontak</th>
                        <th class="min-w-[360px] border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Produk</th>
                        <th class="min-w-[185px] border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</th>
                        <th class="min-w-[110px] border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Bukti</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($durations as $duration)
                        @php
                            $credentials = is_array($duration->credential_data) ? $duration->credential_data : [];
                            $proofUrl = $duration->logout_proof_path ? url(\Illuminate\Support\Facades\Storage::url($duration->logout_proof_path)) : null;
                            $isUploaded = filled($duration->logout_proof_path);
                        @endphp
                        <tr onclick="openAdminUniversalDetail('admin-duration-detail-{{ $duration->id }}')" class="duration-table-row cursor-pointer align-middle">
                            <td class="rounded-[1rem] bg-white px-5 py-5 shadow-sm shadow-[#bde0fe]/10">
                                <p class="inline-block whitespace-nowrap rounded-md border border-white bg-[#f4f9ff] px-2 py-1 text-xs font-black text-[#5a76c8] sm:text-sm">{{ $duration->invoice_number }}</p>
                            </td>
                            <td class="rounded-[1rem] bg-white px-5 py-5 shadow-sm shadow-[#bde0fe]/10">
                                <p class="whitespace-nowrap text-[9px] font-bold text-[#8faaf3] sm:text-[10px]">{{ $duration->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                            </td>
                            <td class="rounded-[1rem] bg-white px-5 py-5 shadow-sm shadow-[#bde0fe]/10">
                                <p class="whitespace-nowrap text-[9px] font-bold text-[#8faaf3] sm:text-[10px]">{{ $duration->credential_expires_at?->format('d M Y, H:i') ?? '-' }}</p>
                            </td>
                            <td class="rounded-[1rem] bg-white px-5 py-5 shadow-sm shadow-[#bde0fe]/10">
                                <p class="whitespace-nowrap text-sm font-black text-[#2b3a67]">{{ $duration->user?->name ?? 'Pengguna' }}</p>
                            </td>
                            <td class="rounded-[1rem] bg-white px-5 py-5 shadow-sm shadow-[#bde0fe]/10">
                                <p class="whitespace-nowrap text-xs font-bold text-[#5a76c8]">{{ $duration->user?->email ?? '-' }}</p>
                            </td>
                            <td class="rounded-[1rem] bg-white px-5 py-5 shadow-sm shadow-[#bde0fe]/10">
                                <p class="whitespace-nowrap text-xs font-bold text-[#5a76c8]">{{ $duration->user?->whatsapp ?: '-' }}</p>
                            </td>
                            <td class="rounded-[1rem] bg-white px-5 py-5 shadow-sm shadow-[#bde0fe]/10" style="width:360px;max-width:360px;">
                                <p class="duration-product-clamp text-sm font-black leading-relaxed text-[#5a76c8]" title="{{ $duration->display_product_name }}">{{ $duration->display_product_name }}</p>
                            </td>
                            <td class="rounded-[1rem] bg-white px-5 py-5 shadow-sm shadow-[#bde0fe]/10">
                                <span class="inline-flex min-w-[8.5rem] items-center justify-center rounded-full border-2 border-white px-4 py-2 text-center text-[10px] font-black uppercase tracking-widest shadow-sm {{ $isUploaded ? 'bg-[#e6fff7] text-emerald-500' : 'bg-[#fff5eb] text-amber-500' }}">
                                    {{ $isUploaded ? 'Sudah Unggah' : 'Belum Keluar' }}
                                </span>
                            </td>
                            <td class="px-5 py-5 text-center align-middle">
                                @if($proofUrl)
                                    <a href="{{ $proofUrl }}" target="_blank" rel="noopener" onclick="event.stopPropagation()" class="duration-table-proof-link inline-flex rounded-full border-2 border-white bg-[#fff7e0] px-4 py-1.5 text-[9px] font-black uppercase tracking-widest text-[#d38b00] shadow-sm">
                                        Lihat
                                    </a>
                                @else
                                    <span class="duration-table-proof-empty text-sm font-black text-[#a3bbfb]">
                                        -
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-20 text-center">
                                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] text-[#8faaf3] shadow-inner">
                                    @include('partials.wiboost-icon', ['name' => 'calendar', 'class' => 'h-8 w-8'])
                                </div>
                                <p class="text-xl font-black text-[#5a76c8]">Belum ada durasi yang habis.</p>
                                <p class="mt-1 text-sm font-bold text-[#8faaf3]">Data akan muncul otomatis setelah masa akses produk lewat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @foreach($durations as $duration)
            @php
                $detailCredentials = is_array($duration->credential_data) ? $duration->credential_data : [];
                $detailProofUrl = $duration->logout_proof_path ? url(\Illuminate\Support\Facades\Storage::url($duration->logout_proof_path)) : null;
                $detailIsUploaded = filled($duration->logout_proof_path);
            @endphp
            <div id="admin-duration-detail-{{ $duration->id }}" class="admin-detail-modal fixed inset-0 z-[9999] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
                <div class="absolute inset-0" onclick="closeAdminUniversalDetail('admin-duration-detail-{{ $duration->id }}')"></div>
                <div class="admin-detail-card relative flex w-full flex-col rounded-[1.75rem] border-4 border-white bg-white p-4 shadow-2xl shadow-[#2b3a67]/20 transform scale-95 transition-transform duration-200 sm:p-5">
                    <div class="admin-detail-header mb-3.5 flex items-start justify-between gap-3 p-3.5">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Detail Durasi</p>
                            <h2 class="mt-1 break-words text-xl font-black text-[#2b3a67]">{{ $duration->invoice_number }}</h2>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $duration->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                        </div>
                        <button type="button" onclick="closeAdminUniversalDetail('admin-duration-detail-{{ $duration->id }}')" class="admin-detail-close flex h-11 w-11 shrink-0 items-center justify-center border-2 border-white">
                            @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-4 w-4'])
                        </button>
                    </div>

                    <div class="admin-detail-body">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama</p>
                                <p class="mt-1 font-black text-[#2b3a67]">{{ $duration->user?->name ?? 'Pengguna' }}</p>
                            </div>
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Email</p>
                                <p class="mt-1 break-all font-bold text-[#2b3a67]">{{ $duration->user?->email ?? '-' }}</p>
                            </div>
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nomor Kontak</p>
                                <p class="mt-1 whitespace-nowrap font-bold text-[#2b3a67]">{{ $duration->user?->whatsapp ?: '-' }}</p>
                            </div>
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status Keluar</p>
                                <span class="mt-2 inline-flex rounded-full border-2 border-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest shadow-sm {{ $detailIsUploaded ? 'bg-[#e6fff7] text-emerald-500' : 'bg-[#fff5eb] text-amber-500' }}">
                                    {{ $detailIsUploaded ? 'Sudah Unggah' : 'Belum Keluar' }}
                                </span>
                            </div>
                            <div class="admin-detail-panel md:col-span-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Produk</p>
                                <p class="mt-1 font-black text-[#2b3a67]">{{ $duration->display_product_name }}</p>
                            </div>
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu Beli</p>
                                <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $duration->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                            </div>
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu Habis</p>
                                <p class="mt-1 text-sm font-black text-[#ff6b6b]">{{ $duration->credential_expires_at?->format('d M Y, H:i') ?? '-' }}</p>
                            </div>
                            <div class="admin-detail-panel md:col-span-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Bukti Keluar</p>
                                @if($detailProofUrl)
                                    <a href="{{ $detailProofUrl }}" target="_blank" rel="noopener" class="mt-2 inline-flex w-fit rounded-full border-2 border-white bg-[#5a76c8] px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white shadow-sm">Buka Bukti</a>
                                    <div class="mt-3 overflow-hidden rounded-[1.2rem] border-4 border-white bg-white shadow-inner">
                                        <img src="{{ $detailProofUrl }}" alt="Bukti keluar {{ $duration->invoice_number }}" class="max-h-72 w-full object-contain">
                                    </div>
                                @else
                                    <p class="mt-2 text-sm font-bold text-[#8faaf3]">Belum ada bukti keluar.</p>
                                @endif
                            </div>
                            @if($detailCredentials !== [])
                                <div class="admin-detail-panel admin-detail-panel-soft md:col-span-2">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Kredensial</p>
                                    <div class="admin-detail-subgrid mt-3">
                                        @foreach($detailCredentials as $key => $value)
                                            @if(filled($value) && ! is_array($value))
                                                @php
                                                    $credentialLabel = [
                                                        'email' => 'Email',
                                                        'username' => 'Nama Pengguna',
                                                        'email_username' => 'Email / Nama Pengguna',
                                                        'email_or_username' => 'Email / Nama Pengguna',
                                                        'credential_id' => 'ID Kredensial',
                                                        'password' => 'Kata Sandi',
                                                        'profile' => 'Profil',
                                                        'pin' => 'PIN',
                                                        'link' => 'Tautan Akses',
                                                        'url' => 'Tautan',
                                                        'tutorial_link' => 'Tautan Tutorial',
                                                        'duration' => 'Durasi',
                                                        'duration_value' => 'Nilai Durasi',
                                                        'duration_unit' => 'Satuan Durasi',
                                                        'duration_label' => 'Label Durasi',
                                                        'account' => 'Akun',
                                                        'akun' => 'Akun',
                                                        'type' => 'Tipe',
                                                        'name' => 'Nama',
                                                        'nama' => 'Nama',
                                                        'phone' => 'Nomor HP',
                                                        'whatsapp' => 'Nomor WhatsApp',
                                                        'number' => 'Nomor',
                                                        'nomor' => 'Nomor',
                                                        'target' => 'Target',
                                                        'server' => 'Server',
                                                        'zone' => 'Zona',
                                                        'zone_id' => 'ID Zona',
                                                        'user_id' => 'ID Pengguna',
                                                        'customer_id' => 'ID Pelanggan',
                                                        'customer_no' => 'Nomor Pelanggan',
                                                        'needs_otp' => 'Butuh OTP',
                                                        'requires_otp' => 'Butuh OTP',
                                                        'requires_login_proof' => 'Wajib Bukti Masuk',
                                                        'requires_logout_proof' => 'Wajib Bukti Keluar',
                                                        'provider_id' => 'ID Penyedia',
                                                        'provider_order_id' => 'ID Pesanan Penyedia',
                                                        'serial_number' => 'Nomor Seri',
                                                        'sn' => 'SN',
                                                        'token' => 'Token',
                                                        'voucher' => 'Voucher',
                                                        'code' => 'Kode',
                                                        'kode' => 'Kode',
                                                        'nickname' => 'Nama Panggilan',
                                                        'expired_at' => 'Waktu Habis',
                                                        'expires_at' => 'Waktu Habis',
                                                        'credential_started_at' => 'Kredensial Mulai',
                                                        'credential_expires_at' => 'Kredensial Habis',
                                                        'notes' => 'Catatan',
                                                        'note' => 'Catatan',
                                                    ][(string) $key] ?? str_replace('_', ' ', ucfirst((string) $key));
                                                    $credentialValue = $value;
                                                    $normalizedKey = strtolower((string) $key);
                                                    $normalizedValue = strtolower(trim((string) $value));
                                                    if (in_array($normalizedKey, ['needs_otp', 'requires_otp', 'requires_login_proof', 'requires_logout_proof'], true)) {
                                                        $credentialValue = in_array($normalizedValue, ['1', 'true', 'yes', 'ya', 'on'], true) ? 'Ya' : 'Tidak';
                                                    } elseif (in_array($normalizedKey, ['duration_unit'], true)) {
                                                        $credentialValue = [
                                                            'day' => 'Hari',
                                                            'days' => 'Hari',
                                                            'week' => 'Minggu',
                                                            'weeks' => 'Minggu',
                                                            'month' => 'Bulan',
                                                            'months' => 'Bulan',
                                                            'year' => 'Tahun',
                                                            'years' => 'Tahun',
                                                        ][$normalizedValue] ?? $value;
                                                    } elseif ($normalizedKey === 'type') {
                                                        $credentialValue = [
                                                            'account' => 'Akun',
                                                            'api' => 'API',
                                                            'manual' => 'Manual',
                                                        ][$normalizedValue] ?? $value;
                                                    }
                                                @endphp
                                                <div class="admin-detail-subfield">
                                                    <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $credentialLabel }}</p>
                                                    <p class="mt-1 break-words text-sm font-bold text-[#2b3a67]">{{ $credentialValue }}</p>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @if($durations->hasPages())
            <div class="w-full min-w-0 p-5 sm:p-8">
            {{ $durations->links() }}
            </div>
        @endif
    </div>
</div>
<script>
    function openAdminUniversalDetail(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('modal-active');
        document.body.classList.add('admin-detail-open');
    }

    function closeAdminUniversalDetail(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('modal-active');
        document.body.classList.remove('admin-detail-open');
    }
</script>
@endsection
