@extends('layouts.admin')

@section('title', 'Log Aktivitas')
@section('admin_header_subtitle', 'Aktivitas & lokasi (IP) seluruh pengunjung situs.')

@section('content')
@php
    $searchValue = $search ?? request('search', '');
    $activeFilter = $filter ?? request('filter', 'all');

    $filterTabs = [
        'all' => 'Semua',
        'member' => 'User Login',
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
    .log-json {
        margin: 0;
        white-space: pre-wrap;
        word-break: break-word;
        font-size: 12px;
        line-height: 1.65;
        color: #2b3a67;
        background: #f8fbff;
        border: 2px solid #fff;
        border-radius: 1.25rem;
        padding: 1rem 1.15rem;
        box-shadow: inset 0 2px 6px rgba(189, 224, 254, .25);
    }
</style>

<div class="wiboost-font relative z-10 mx-auto max-w-7xl pb-12" x-data="{ jsonOpen: false, jsonText: '' }">

    {{-- Pencarian --}}
    <div class="mb-6 rounded-[2rem] border-4 border-white bg-white/95 p-4 shadow-xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem] sm:p-5">
        <form action="{{ route('admin.logs.index') }}" method="GET" class="flex w-full gap-2">
            <input type="hidden" name="filter" value="{{ $activeFilter }}">
            <input type="text" name="search" value="{{ $searchValue }}"
                class="min-w-0 flex-1 truncate rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:rounded-[1.5rem] sm:px-6 sm:py-4 sm:text-sm"
                placeholder="Cari IP, user, negara, kota, ISP, URL...">
            <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.2rem] border-4 border-white bg-[#5a76c8] text-white shadow-md transition-transform active:scale-95 sm:h-14 sm:w-14 sm:rounded-[1.5rem]" title="Cari">
                @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
            </button>
            <a href="{{ route('admin.logs.index', array_filter(['filter' => $activeFilter === 'all' ? null : $activeFilter, 'search' => $searchValue ?: null])) }}" class="flex h-12 shrink-0 items-center justify-center gap-2 rounded-[1.2rem] border-4 border-white bg-[#8faaf3] px-4 text-xs font-black text-white shadow-md transition-transform hover:bg-[#6f8ddc] active:scale-95 sm:h-14 sm:rounded-[1.5rem] sm:px-5 sm:text-sm" title="Refresh Log">
                @include('partials.wiboost-icon', ['name' => 'refresh', 'class' => 'h-5 w-5'])
                <span class="whitespace-nowrap">Refresh</span>
            </a>
            @if($searchValue !== '')
                <a href="{{ route('admin.logs.index', ['filter' => $activeFilter]) }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.2rem] border-4 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-md transition-transform active:scale-95 sm:h-14 sm:w-14 sm:rounded-[1.5rem]" title="Reset">
                    @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                </a>
            @endif
        </form>
    </div>

    {{-- Filter (gaya sama seperti halaman Penarikan) --}}
    @include('admin.partials.filter-tabs', [
        'filters' => $filterTabs,
        'filterRoute' => 'admin.logs.index',
        'filterParam' => 'filter',
        'activeFilter' => $activeFilter,
        'defaultFilter' => 'all',
        'filterQuery' => array_filter(['search' => $searchValue]),
    ])

    <div class="overflow-hidden rounded-[2rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem]">
        <div class="w-full overflow-x-auto table-scroll pb-2">
            <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                <table class="w-full text-left" style="min-width: 1280px; table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="font-size: 10px; width: 130px;" class="border-b-4 border-dashed border-[#f4f9ff] px-4 py-4 font-black uppercase tracking-widest text-[#8faaf3]">Waktu</th>
                            <th style="font-size: 10px; width: 210px;" class="border-b-4 border-dashed border-[#f4f9ff] px-4 py-4 font-black uppercase tracking-widest text-[#8faaf3]">User</th>
                            <th style="font-size: 10px; width: 190px;" class="border-b-4 border-dashed border-[#f4f9ff] px-4 py-4 font-black uppercase tracking-widest text-[#8faaf3]">IP</th>
                            <th style="font-size: 10px; width: 300px;" class="border-b-4 border-dashed border-[#f4f9ff] px-4 py-4 font-black uppercase tracking-widest text-[#8faaf3]">Aktivitas</th>
                            <th style="font-size: 10px; width: 230px;" class="border-b-4 border-dashed border-[#f4f9ff] px-4 py-4 font-black uppercase tracking-widest text-[#8faaf3]">Lokasi &amp; Jaringan</th>
                            <th style="font-size: 10px; width: 120px;" class="border-b-4 border-dashed border-[#f4f9ff] px-4 py-4 text-center font-black uppercase tracking-widest text-[#8faaf3]">Detail</th>
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
                                $detailPretty = json_encode($detailArr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                            @endphp
                            <tr class="align-top transition-colors hover:bg-[#f8faff]">
                                <td class="px-4 py-5">
                                    <p class="whitespace-nowrap text-[9px] font-bold text-[#8faaf3] sm:text-[10px]">{{ $log->created_at ? $log->created_at->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y, H:i') : '-' }}</p>
                                </td>
                                <td class="px-4 py-5">
                                    @if($log->user)
                                        <p class="truncate text-xs font-black text-[#2b3a67]">{{ $log->user->name }}</p>
                                        <p class="truncate text-[10px] font-bold text-[#8faaf3]">{{ $log->user->email }}</p>
                                    @else
                                        <span class="inline-flex rounded-full border-2 border-white bg-[#f4f9ff] px-3 py-1 text-[9px] font-black uppercase tracking-widest text-[#8faaf3]">Tamu</span>
                                    @endif
                                </td>
                                <td class="px-4 py-5">
                                    <p class="inline-block max-w-full break-all rounded-md border border-white bg-[#f4f9ff] px-2 py-1 text-[10px] font-black leading-tight text-[#5a76c8]">{{ $log->ip ?? '-' }}</p>
                                    @if($geo)
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            @if($geo->mobile)<span class="rounded border border-white bg-[#e0fbfc] px-1.5 py-0.5 text-[8px] font-black uppercase text-[#4bc6b9]">Mobile</span>@endif
                                            @if($geo->proxy)<span class="rounded border border-white bg-[#ffe5e5] px-1.5 py-0.5 text-[8px] font-black uppercase text-[#ff6b6b]">Proxy</span>@endif
                                            @if($geo->hosting)<span class="rounded border border-white bg-[#fff5eb] px-1.5 py-0.5 text-[8px] font-black uppercase text-amber-600">Hosting</span>@endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-5">
                                    <div class="flex items-start gap-2">
                                        <span class="shrink-0 rounded-md px-2 py-0.5 text-[9px] font-black uppercase tracking-widest {{ $methodClass }}">{{ $log->method ?? '-' }}</span>
                                        <span class="min-w-0 break-all text-[11px] font-bold text-[#2b3a67]">/{{ $log->url }}</span>
                                    </div>
                                    @if($log->route_name)
                                        <p class="mt-1 truncate text-[10px] font-bold text-[#a3bbfb]">{{ $log->route_name }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-5">
                                    @if($geo && count($locationParts))
                                        <p class="text-xs font-black leading-snug text-[#2b3a67]">{{ $ccFlag($geo->country_code) }} {{ implode(', ', $locationParts) }}</p>
                                    @else
                                        <p class="text-[11px] font-bold text-[#8faaf3]">Lokasi belum terdeteksi</p>
                                    @endif
                                    <p class="mt-1 break-words text-[10px] font-bold text-[#8faaf3]" title="{{ $geo?->isp }}">{{ $geo?->isp ?: '-' }}</p>
                                    @if($geo?->asname)
                                        <p class="break-words text-[10px] font-bold text-[#a3bbfb]">{{ $geo->asname }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-5 text-center">
                                    <button type="button" @click="jsonText = @js($detailPretty); jsonOpen = true"
                                        class="mx-auto inline-flex items-center justify-center rounded-[1rem] border-2 border-white bg-[#f4f9ff] px-3 py-2 text-[10px] font-black uppercase tracking-widest text-[#5a76c8] shadow-sm transition hover:bg-[#e0fbfc]">
                                        Lihat JSON
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
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
            <div class="border-t-4 border-dashed border-[#f4f9ff] px-5 py-5">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <p class="mt-4 px-2 text-[11px] font-bold text-[#8faaf3]">
        Geolokasi IP diambil dari ip-api.com (IP asli pengunjung dari header proxy/Cloudflare) & di-cache per IP (refresh 30 hari). Log lama dihapus otomatis setelah 90 hari.
    </p>

    {{-- Modal Detail (di-teleport ke body agar tidak ketiban navbar/konten) --}}
    <template x-teleport="body">
        <div x-show="jsonOpen" x-cloak
             @keydown.escape.window="jsonOpen = false"
             @click.self="jsonOpen = false"
             class="flex items-center justify-center"
             style="position: fixed; inset: 0; z-index: 2147483000; padding: 1rem; background: rgba(43, 58, 103, .6); -webkit-backdrop-filter: blur(4px); backdrop-filter: blur(4px);">
            <div class="w-full max-w-2xl rounded-[2rem] border-4 border-white bg-white p-5 shadow-2xl sm:p-6" style="max-height: 88vh; overflow: auto; font-family: 'Nunito', sans-serif;">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="text-lg font-black text-[#2b3a67]">Detail Aktivitas &amp; Geolokasi</h3>
                    <button type="button" @click="jsonOpen = false" class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-white bg-[#ffe5e5] text-[#ff4d4d] shadow-sm transition hover:bg-[#ffcccc]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" /></svg>
                    </button>
                </div>
                <pre class="log-json" x-text="jsonText"></pre>
            </div>
        </div>
    </template>
</div>
@endsection
