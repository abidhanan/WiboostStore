@extends('layouts.admin')

@section('title', 'Log Aktivitas')
@section('admin_header_subtitle', 'Aktivitas & lokasi (IP) seluruh pengunjung situs.')

@section('content')
@php
    $searchValue = $search ?? request('search', '');
    $activeFilter = $filter ?? request('filter', 'all');
    $selectedMonth = $selectedMonth ?? request('month', now()->format('m'));
    $selectedYear = $selectedYear ?? request('year', now()->format('Y'));
    $adminMonthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $filterTabs = [
        'all' => 'Semua',
        'member' => 'Pengguna Masuk',
        'guest' => 'Tamu',
        'proxy' => 'Proxy/VPN',
        'hosting' => 'Hosting/Bot',
    ];

    $ccFlag = function (?string $cc): string {
        $cc = strtoupper(trim((string) $cc));
        if (strlen($cc) !== 2 || ! ctype_alpha($cc)) {
            return '🌐';
        }
        return mb_chr(0x1F1E6 + ord($cc[0]) - 65) . mb_chr(0x1F1E6 + ord($cc[1]) - 65);
    };

    $parseUa = function (?string $ua): array {
        $ua = (string) $ua;
        $browser = 'Lainnya';
        foreach (['Edg' => 'Microsoft Edge', 'OPR' => 'Opera', 'SamsungBrowser' => 'Samsung Internet', 'MiuiBrowser' => 'Mi Browser', 'Firefox' => 'Firefox', 'Chrome' => 'Chrome', 'Safari' => 'Safari'] as $needle => $name) {
            if (stripos($ua, $needle) !== false) { $browser = $name; break; }
        }
        $os = 'Lainnya';
        foreach (['Android' => 'Android', 'iPhone' => 'iOS', 'iPad' => 'iPadOS', 'Windows' => 'Windows', 'Mac OS X' => 'macOS', 'Linux' => 'Linux'] as $needle => $name) {
            if (stripos($ua, $needle) !== false) { $os = $name; break; }
        }
        $device = preg_match('/Mobile|Android|iPhone/i', $ua) ? 'Mobile' : (preg_match('/iPad|Tablet/i', $ua) ? 'Tablet' : 'Desktop');
        return ['browser' => $browser, 'os' => $os, 'device' => $device];
    };
@endphp

<style>
    [x-cloak] { display: none !important; }
    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; border: 2px solid #f0f5ff; }
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

<div class="wiboost-font relative z-10 mx-auto max-w-7xl pb-12" x-data="{ detailOpen: false, detailData: {} }" @admin-detail-closed.window="detailOpen = false">

    {{-- Pencarian --}}
    <div class="mb-6 rounded-[2rem] border-4 border-white bg-white/95 p-4 shadow-xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem] sm:p-5">
        <form action="{{ route('admin.logs.index') }}" method="GET" class="w-full space-y-3">
            <input type="hidden" name="filter" value="{{ $activeFilter }}">
            <input type="hidden" name="month" value="{{ $selectedMonth }}">
            <input type="hidden" name="year" value="{{ $selectedYear }}">
            <div class="flex w-full flex-col gap-2 sm:flex-row">
            <div class="flex min-w-0 flex-1 gap-2">
                <input type="text" name="search" value="{{ $searchValue }}"
                    class="min-w-0 flex-1 truncate rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:rounded-[1.5rem] sm:px-6 sm:py-4 sm:text-sm"
                    placeholder="Cari IP, user, negara, kota, ISP, URL...">
                <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.2rem] border-4 border-white bg-[#5a76c8] text-white shadow-md transition-transform active:scale-95 sm:h-14 sm:w-14 sm:rounded-[1.5rem]" title="Cari">
                    @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                </button>
                @if($searchValue !== '')
                    <a href="{{ route('admin.logs.index', ['filter' => $activeFilter, 'month' => $selectedMonth, 'year' => $selectedYear]) }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.2rem] border-4 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-md transition-transform active:scale-95 sm:h-14 sm:w-14 sm:rounded-[1.5rem]" title="Reset">
                        @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                    </a>
                @endif
            </div>
            <a href="{{ route('admin.logs.index', array_filter(['filter' => $activeFilter === 'all' ? null : $activeFilter, 'search' => $searchValue ?: null, 'month' => $selectedMonth, 'year' => $selectedYear])) }}" class="flex h-12 w-full shrink-0 items-center justify-center gap-2 rounded-[1.2rem] border-4 border-white bg-[#8faaf3] px-4 text-xs font-black text-white shadow-md transition-transform hover:bg-[#6f8ddc] active:scale-95 sm:h-14 sm:w-auto sm:rounded-[1.5rem] sm:px-5 sm:text-sm" title="Refresh Log">
                @include('partials.wiboost-icon', ['name' => 'refresh', 'class' => 'h-5 w-5'])
                <span class="whitespace-nowrap">Refresh</span>
            </a>
            </div>

            <div class="mt-1 flex w-full flex-col items-center justify-start gap-3 border-t-4 border-dashed border-[#f4f9ff] pt-4 md:flex-row md:justify-end">
                <div class="flex w-full items-center gap-2 md:w-auto">
                <div class="admin-theme-select admin-theme-select-month" data-admin-theme-select data-submit-on-change="true" data-input-name="month">
                    <button type="button" class="admin-theme-select-button rounded-[1rem] px-4 py-2 text-xs font-black sm:text-sm">
                        <span data-admin-theme-select-label>{{ $adminMonthNames[(int) $selectedMonth] ?? '-' }}</span>
                        <span class="admin-theme-select-arrow">v</span>
                    </button>
                    <div class="admin-theme-select-menu">
                        @foreach($adminMonthNames as $monthNumber => $monthName)
                            <button type="button" class="admin-theme-select-option {{ (string) $selectedMonth === sprintf('%02d', $monthNumber) ? 'is-selected' : '' }}" data-value="{{ sprintf('%02d', $monthNumber) }}" data-label="{{ $monthName }}">{{ $monthName }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="admin-theme-select admin-theme-select-year" data-admin-theme-select data-submit-on-change="true" data-input-name="year">
                    <button type="button" class="admin-theme-select-button rounded-[1rem] px-4 py-2 text-xs font-black sm:text-sm">
                        <span data-admin-theme-select-label>{{ $selectedYear }}</span>
                        <span class="admin-theme-select-arrow">v</span>
                    </button>
                    <div class="admin-theme-select-menu">
                        @for($year = now()->year + 1; $year >= now()->year - 10; $year--)
                            <button type="button" class="admin-theme-select-option {{ (string) $selectedYear === (string) $year ? 'is-selected' : '' }}" data-value="{{ $year }}" data-label="{{ $year }}">{{ $year }}</button>
                        @endfor
                    </div>
                </div>
                </div>
                <a href="{{ route('admin.logs.export_excel', array_filter(['search' => $searchValue ?: null, 'filter' => $activeFilter === 'all' ? null : $activeFilter, 'month' => $selectedMonth, 'year' => $selectedYear])) }}" target="_blank" class="flex h-12 w-full shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#4bc6b9] px-6 text-sm font-black text-white shadow-md shadow-[#4bc6b9]/25 transition-transform hover:bg-[#3ba398] active:scale-95 md:w-auto sm:rounded-[1.2rem]" title="Cetak Laporan">
                    @include('partials.wiboost-icon', ['name' => 'chart', 'class' => 'h-5 w-5'])
                    <span class="whitespace-nowrap">Cetak Laporan</span>
                </a>
            </div>
        </form>
    </div>

    {{-- Filter (gaya sama seperti halaman Penarikan) --}}
    @include('admin.partials.filter-tabs', [
        'filters' => $filterTabs,
        'filterRoute' => 'admin.logs.index',
        'filterParam' => 'filter',
        'activeFilter' => $activeFilter,
        'defaultFilter' => 'all',
        'filterQuery' => array_filter(['search' => $searchValue, 'month' => $selectedMonth, 'year' => $selectedYear]),
    ])

    <div class="overflow-hidden rounded-[2rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem]">
        <div class="w-full overflow-x-auto table-scroll pb-2">
            <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                <table class="w-full text-left" style="min-width: 2230px; table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="width: 145px;" class="border-b-4 border-dashed border-[#f4f9ff] px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu</th>
                            <th style="width: 175px;" class="border-b-4 border-dashed border-[#f4f9ff] px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama</th>
                            <th style="width: 235px;" class="border-b-4 border-dashed border-[#f4f9ff] px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Email</th>
                            <th style="width: 340px;" class="border-b-4 border-dashed border-[#f4f9ff] px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">IP</th>
                            <th style="width: 155px;" class="border-b-4 border-dashed border-[#f4f9ff] px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status IP</th>
                            <th style="width: 95px;" class="border-b-4 border-dashed border-[#f4f9ff] px-4 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Metode</th>
                            <th style="width: 300px;" class="border-b-4 border-dashed border-[#f4f9ff] px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">URL</th>
                            <th style="width: 190px;" class="border-b-4 border-dashed border-[#f4f9ff] px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Route</th>
                            <th style="width: 250px;" class="border-b-4 border-dashed border-[#f4f9ff] px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Lokasi</th>
                            <th style="width: 245px;" class="border-b-4 border-dashed border-[#f4f9ff] px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Jaringan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($logs as $log)
                            @php
                                $geo = $log->geo;
                                $methodClass = match ($log->method) {
                                    'POST' => 'bg-[#fff5eb] text-amber-600',
                                    'GET' => 'bg-[#f4f9ff] text-[#5a76c8]',
                                    default => 'bg-[#f4f9ff] text-[#8faaf3]',
                                };
                                $locationParts = array_filter([$geo?->city, $geo?->region_name, $geo?->country]);
                                $ua = $parseUa($log->user_agent);
                                $geoArr = $geo ? $geo->toIpApiArray() : [];
                                $detailArr = array_merge($geoArr, [
                                    'reverse' => $geoArr['reverse'] ?? (($geo && is_array($geo->raw)) ? ($geo->raw['reverse'] ?? null) : null),
                                    'accessed_by' => $log->user?->name ?? 'Tamu',
                                    'accessed_email' => $log->user?->email,
                                    'accessed_at' => $log->created_at?->timezone(config('app.timezone', 'Asia/Jakarta'))->format('Y-m-d H:i:s'),
                                    'method' => $log->method,
                                    'path' => '/' . $log->url,
                                    'route' => $log->route_name,
                                    'browser' => $ua['browser'],
                                    'os' => $ua['os'],
                                    'device' => $ua['device'],
                                    'user_agent' => $log->user_agent,
                                ]);
                            @endphp
                            <tr @click="detailData = @js($detailArr); detailOpen = true" class="cursor-pointer transition-colors hover:bg-[#f8faff]">
                                <td class="px-4 py-5 align-middle">
                                    <p class="whitespace-nowrap text-[9px] font-bold text-[#8faaf3] sm:text-[10px]">{{ $log->created_at ? $log->created_at->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y, H:i') : '-' }}</p>
                                </td>
                                <td class="px-4 py-5 align-middle">
                                    <p class="truncate text-xs font-black text-[#2b3a67]" title="{{ $log->user?->name ?? 'Tamu' }}">{{ $log->user?->name ?? 'Tamu' }}</p>
                                </td>
                                <td class="px-4 py-5 align-middle">
                                    <p class="truncate text-[10px] font-bold text-[#8faaf3]" title="{{ $log->user?->email ?? '-' }}">{{ $log->user?->email ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-5 align-middle" style="width: 340px;">
                                    <p class="inline-block whitespace-nowrap rounded-md border border-white bg-[#f4f9ff] px-2 py-1 text-[10px] font-black leading-tight text-[#5a76c8]">{{ $log->ip ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-5 align-middle">
                                    @if($geo)
                                        <div class="flex flex-wrap gap-1">
                                            @if($geo->mobile)<span class="rounded border border-white bg-[#e0fbfc] px-1.5 py-0.5 text-[8px] font-black uppercase text-[#4bc6b9]">Mobile</span>@endif
                                            @if($geo->proxy)<span class="rounded border border-white bg-[#ffe5e5] px-1.5 py-0.5 text-[8px] font-black uppercase text-[#ff6b6b]">Proxy</span>@endif
                                            @if($geo->hosting)<span class="rounded border border-white bg-[#fff5eb] px-1.5 py-0.5 text-[8px] font-black uppercase text-amber-600">Hosting</span>@endif
                                            @if(!$geo->mobile && !$geo->proxy && !$geo->hosting)<span class="text-[10px] font-bold text-[#8faaf3]">Normal</span>@endif
                                        </div>
                                    @else
                                        <span class="text-[10px] font-bold text-[#8faaf3]">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-5 text-center align-middle">
                                    <span class="inline-flex rounded-md px-2 py-1 text-[9px] font-black uppercase tracking-widest {{ $methodClass }}">{{ $log->method ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-5 align-middle">
                                    <p class="truncate text-[11px] font-bold text-[#2b3a67]" title="/{{ $log->url }}">/{{ $log->url }}</p>
                                </td>
                                <td class="px-4 py-5 align-middle">
                                    <p class="truncate text-[10px] font-bold text-[#a3bbfb]" title="{{ $log->route_name ?: '-' }}">{{ $log->route_name ?: '-' }}</p>
                                </td>
                                <td class="px-4 py-5 align-middle">
                                    @if($geo && count($locationParts))
                                        <p class="truncate text-xs font-black text-[#2b3a67]" title="{{ implode(', ', $locationParts) }}">{{ $ccFlag($geo->country_code) }} {{ implode(', ', $locationParts) }}</p>
                                    @else
                                        <p class="text-[11px] font-bold text-[#8faaf3]">Lokasi belum terdeteksi</p>
                                    @endif
                                </td>
                                <td class="px-4 py-5 align-middle">
                                    <p class="truncate text-[10px] font-bold text-[#8faaf3]" title="{{ $geo?->isp ?: '-' }}">{{ $geo?->isp ?: '-' }}</p>
                                    <p class="mt-1 truncate text-[10px] font-bold text-[#a3bbfb]" title="{{ $geo?->asname ?: '-' }}">{{ $geo?->asname ?: '-' }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-5 py-12 text-center">
                                    <div class="mx-auto max-w-md rounded-[2rem] border-4 border-dashed border-[#e5edf9] bg-[#f8fbff] p-8">
                                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-[1.5rem] border-4 border-white bg-[#e0fbfc] text-[#5a76c8] shadow-sm">
                                            @include('partials.wiboost-icon', ['name' => 'report', 'class' => 'h-8 w-8'])
                                        </div>
                                        <p class="text-xl font-black text-[#5a76c8]">Belum ada aktivitas tercatat.</p>
                                        <p class="mt-2 text-sm font-bold text-[#8faaf3]">Log akan terisi otomatis saat pengunjung mengakses website.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($logs->hasPages())
            <div class="p-5 sm:p-8 w-full min-w-0">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <p class="mt-4 px-2 text-[11px] font-bold text-[#8faaf3]">
        Geolokasi IP diambil dari ip-api.com (IP asli pengunjung dari header proxy/Cloudflare) & di-cache per IP (refresh 30 hari). Log lama dihapus otomatis setelah 90 hari.
    </p>

    {{-- Modal Detail (di-teleport ke body agar tidak ketiban navbar/konten) --}}
    <template x-teleport="body">
        <div x-show="detailOpen" x-cloak
             :class="{ 'modal-active': detailOpen }"
             @keydown.escape.window="detailOpen = false"
             @click.self="detailOpen = false"
             class="admin-detail-modal fixed inset-0 z-[9999] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
            <div class="admin-detail-card relative flex w-full flex-col rounded-[1.75rem] border-4 border-white bg-white p-4 shadow-2xl shadow-[#2b3a67]/20 transform scale-95 transition-transform duration-200 sm:p-5" style="font-family: 'Nunito', sans-serif;">
                <div class="admin-detail-header mb-3.5 flex items-center justify-between gap-3 p-3.5">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Detail Aktivitas</p>
                        <h2 class="mt-1 text-lg font-black text-[#2b3a67]">Detail Aktivitas &amp; Geolokasi</h2>
                    </div>
                    <button type="button" @click="detailOpen = false" class="admin-detail-close flex h-11 w-11 shrink-0 items-center justify-center border-2 border-white">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" /></svg>
                    </button>
                </div>
                <div class="admin-detail-body">
                    <div class="grid gap-3 md:grid-cols-2">
                        @foreach([
                            'accessed_by' => 'Nama Pengguna',
                            'accessed_email' => 'Email',
                            'accessed_at' => 'Waktu Akses',
                            'query' => 'IP',
                            'method' => 'Metode',
                            'path' => 'URL',
                            'route' => 'Route',
                            'status' => 'Status Geolokasi',
                            'continent' => 'Benua',
                            'continentCode' => 'Kode Benua',
                            'country' => 'Negara',
                            'countryCode' => 'Kode Negara',
                            'region' => 'Kode Wilayah',
                            'regionName' => 'Provinsi',
                            'city' => 'Kota',
                            'district' => 'Kecamatan',
                            'zip' => 'Kode Pos',
                            'lat' => 'Lintang',
                            'lon' => 'Bujur',
                            'timezone' => 'Zona Waktu',
                            'offset' => 'Offset Waktu',
                            'currency' => 'Mata Uang',
                            'isp' => 'ISP',
                            'org' => 'Organisasi',
                            'as' => 'Nomor AS',
                            'asname' => 'Nama AS',
                            'reverse' => 'Reverse DNS',
                            'browser' => 'Browser',
                            'os' => 'Sistem Operasi',
                            'device' => 'Perangkat',
                            'mobile' => 'Jaringan Seluler',
                            'proxy' => 'Proxy / VPN',
                            'hosting' => 'Hosting / Bot',
                            'user_agent' => 'User Agent',
                        ] as $detailKey => $detailLabel)
                            <div class="admin-detail-panel {{ in_array($detailKey, ['path', 'user_agent'], true) ? 'md:col-span-2' : '' }}">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $detailLabel }}</p>
                                <p class="mt-1 break-words text-sm font-bold text-[#2b3a67]" x-text="[true, false].includes(detailData[@js($detailKey)]) ? (detailData[@js($detailKey)] ? 'Ya' : 'Tidak') : ([null, undefined, ''].includes(detailData[@js($detailKey)]) ? '-' : detailData[@js($detailKey)])"></p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
