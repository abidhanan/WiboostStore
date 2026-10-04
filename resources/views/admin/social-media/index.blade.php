@extends('layouts.admin')

@section('title', 'Manajemen Media Sosial')
@section('admin_header_subtitle', 'Pantau performa Instagram dan TikTok.')

@section('content')
@php
    $platformLabels = ['instagram' => 'Instagram', 'tiktok' => 'TikTok'];
@endphp

<style>
    .social-media-table-scroll::-webkit-scrollbar { height: 8px; }
    .social-media-table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .social-media-table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border: 2px solid #f0f5ff; border-radius: 10px; }
    .social-media-table-scroll::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
    .social-media-year-select { width: 100%; }
    @media (min-width: 768px) { .social-media-year-select { width: 8rem; } }
</style>

<div class="wiboost-font relative z-10 w-full min-w-0 pb-12">
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-sm font-black text-emerald-500 shadow-lg shadow-emerald-100/50 sm:px-6 sm:text-base">
            @include('partials.wiboost-icon', ['name' => 'success', 'class' => 'h-6 w-6 shrink-0'])
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="mb-6 w-full min-w-0 rounded-[2rem] border-4 border-white bg-white/90 p-4 shadow-xl shadow-[#bde0fe]/25 backdrop-blur-sm sm:rounded-[2.5rem] sm:p-5">
        <form action="{{ route('admin.social-media.index') }}" method="GET" class="flex w-full flex-col gap-3">
            <div class="flex w-full flex-col gap-3 md:flex-row">
                <div class="flex min-w-0 flex-1 gap-2">
                    @if($platform !== 'all')
                        <input type="hidden" name="platform" value="{{ $platform }}">
                    @endif
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <input type="text" name="search" value="{{ $search }}" class="min-w-0 flex-1 truncate rounded-[1.1rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:px-6 sm:py-4 sm:text-sm" placeholder="Cari kategori, bulan, tahun, atau link postingan...">
                    <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-white shadow-md shadow-[#5a76c8]/25 transition-colors hover:bg-[#4760a9] active:scale-95" title="Cari Data">
                        @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                    </button>
                    @if($search !== '')
                        <a href="{{ route('admin.social-media.index', array_filter(['platform' => $platform !== 'all' ? $platform : null, 'year' => $selectedYear])) }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-md transition-colors hover:bg-[#ffcccc] active:scale-95" title="Reset Pencarian">
                            @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                        </a>
                    @endif
                </div>

                <a href="{{ route('admin.social-media.create', array_filter(['platform' => $platform !== 'all' ? $platform : null, 'return_to' => request()->fullUrl()])) }}" class="inline-flex h-12 w-full shrink-0 items-center justify-center gap-2 rounded-[1.1rem] border-4 border-white bg-[#5a76c8] px-6 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-colors hover:bg-[#4760a9] active:scale-95 md:w-auto">
                    @include('partials.wiboost-icon', ['name' => 'plus', 'class' => 'h-5 w-5'])
                    Tambah Data
                </a>
            </div>

            <div class="mt-1 flex w-full flex-col gap-3 border-t-4 border-dashed border-[#f4f9ff] pt-4 md:flex-row md:items-center md:justify-end">
                <div class="admin-theme-select social-media-year-select" data-admin-theme-select data-submit-on-change="true" data-input-name="year">
                    <button type="button" class="admin-theme-select-button rounded-[1rem] px-4 py-2 text-xs font-black sm:text-sm">
                        <span data-admin-theme-select-label>{{ $selectedYear }}</span>
                        <span class="admin-theme-select-arrow">v</span>
                    </button>
                    <div class="admin-theme-select-menu">
                        @foreach($availableYears as $yearOption)
                            <button type="button" class="admin-theme-select-option {{ (int) $selectedYear === (int) $yearOption ? 'is-selected' : '' }}" data-value="{{ $yearOption }}" data-label="{{ $yearOption }}">{{ $yearOption }}</button>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('admin.social-media.export_excel', array_filter(['platform' => $platform !== 'all' ? $platform : null, 'search' => $search ?: null, 'year' => $selectedYear])) }}" class="inline-flex h-12 w-full shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#4bc6b9] px-6 text-sm font-black text-white shadow-md shadow-[#4bc6b9]/25 transition-colors hover:bg-[#3ba398] active:scale-95 md:w-auto">
                    @include('partials.wiboost-icon', ['name' => 'chart', 'class' => 'h-5 w-5'])
                    <span class="whitespace-nowrap">Cetak Laporan</span>
                </a>
            </div>
        </form>
    </div>

    <div class="mb-6 flex min-w-0 flex-wrap gap-2 px-1 sm:px-2">
        <a href="{{ route('admin.social-media.index', array_filter(['search' => $search ?: null, 'year' => $selectedYear])) }}" class="inline-flex h-11 items-center justify-center rounded-full border-2 border-white px-5 text-xs font-black uppercase tracking-wide shadow-sm transition-colors {{ $platform === 'all' ? 'bg-[#5a76c8] text-white' : 'bg-white text-[#5a76c8] hover:bg-[#e0fbfc]' }}">Semua</a>
        @foreach($platformLabels as $platformKey => $platformLabel)
            <a href="{{ route('admin.social-media.index', array_filter(['platform' => $platformKey, 'search' => $search ?: null, 'year' => $selectedYear])) }}" class="inline-flex h-11 items-center justify-center rounded-full border-2 border-white px-5 text-xs font-black uppercase tracking-wide shadow-sm transition-colors {{ $platform === $platformKey ? 'bg-[#5a76c8] text-white' : 'bg-white text-[#5a76c8] hover:bg-[#e0fbfc]' }}">{{ $platformLabel }}</a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-[2rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem]">
        <div class="social-media-table-scroll w-full overflow-x-auto pb-2">
            <div class="inline-block min-w-full p-4 align-middle sm:p-6">
                <table class="w-full text-left" style="min-width:1750px;table-layout:fixed;">
                    <thead>
                        <tr>
                            <th style="width:145px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu</th>
                            <th style="width:135px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Kategori</th>
                            <th style="width:155px;" class="whitespace-nowrap border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Total Postingan</th>
                            <th style="width:160px;" class="whitespace-nowrap border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Total Followers</th>
                            <th style="width:135px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Jangkauan</th>
                            <th style="width:155px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Kunjungan Profil</th>
                            <th style="width:135px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Klik Link Bio</th>
                            <th style="width:260px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Link Postingan Teramai</th>
                            <th style="width:260px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Link Postingan Tersepi</th>
                            <th style="width:145px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($stats as $stat)
                            <tr onclick="openAdminUniversalDetail('admin-social-detail-{{ $stat->id }}')" class="cursor-pointer transition-colors hover:bg-[#f8faff]">
                                <td class="px-5 py-5 align-middle"><span class="whitespace-nowrap text-xs font-black text-[#2b3a67]">{{ $monthLabels[$stat->month] ?? '-' }} {{ $stat->year }}</span></td>
                                <td class="px-5 py-5 align-middle">
                                    <span class="inline-flex items-center whitespace-nowrap rounded-[1rem] border-2 px-4 py-2 text-[10px] font-black uppercase tracking-wide shadow-sm {{ $stat->platform === 'instagram' ? 'border-[#ffc7dc] bg-[#ffeaf3] text-[#e1306c]' : 'border-[#a9eaf2] bg-[#e8fbff] text-[#16889a]' }}">
                                        {{ $platformLabels[$stat->platform] ?? ucfirst($stat->platform) }}
                                    </span>
                                </td>
                                <td class="px-5 py-5 text-center align-middle"><span class="whitespace-nowrap font-black text-[#2b3a67]">{{ number_format($stat->total_posts, 0, ',', '.') }}</span></td>
                                <td class="px-5 py-5 text-center align-middle"><span class="whitespace-nowrap font-black text-[#5a76c8]">{{ number_format($stat->total_followers, 0, ',', '.') }}</span></td>
                                <td class="px-5 py-5 text-center align-middle"><span class="whitespace-nowrap font-black text-[#4bc6b9]">{{ number_format($stat->reach, 0, ',', '.') }}</span></td>
                                <td class="px-5 py-5 text-center align-middle"><span class="whitespace-nowrap font-black text-[#2b3a67]">{{ number_format($stat->profile_visits, 0, ',', '.') }}</span></td>
                                <td class="px-5 py-5 text-center align-middle"><span class="whitespace-nowrap font-black text-[#d99b16]">{{ number_format($stat->bio_link_clicks, 0, ',', '.') }}</span></td>
                                <td class="px-5 py-5 align-middle">
                                    @if($stat->top_post_url)
                                        <a href="{{ $stat->top_post_url }}" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation()" class="block truncate text-xs font-black text-[#5a76c8] underline decoration-current decoration-2 underline-offset-4" title="{{ $stat->top_post_url }}">{{ $stat->top_post_url }}</a>
                                    @else
                                        <span class="text-xs font-bold text-[#8faaf3]">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    @if($stat->low_post_url)
                                        <a href="{{ $stat->low_post_url }}" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation()" class="block truncate text-xs font-black text-[#5a76c8] underline decoration-current decoration-2 underline-offset-4" title="{{ $stat->low_post_url }}">{{ $stat->low_post_url }}</a>
                                    @else
                                        <span class="text-xs font-bold text-[#8faaf3]">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-5 text-center align-middle">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.social-media.edit', ['socialMediaStat' => $stat, 'return_to' => request()->fullUrl()]) }}" onclick="event.stopPropagation()" class="flex h-11 w-11 items-center justify-center rounded-[1rem] border-4 border-white bg-[#fff1cc] text-[#d99b16] shadow-md transition-colors hover:bg-[#ffe59b] active:scale-95" title="Ubah Data">
                                            @include('partials.wiboost-icon', ['name' => 'edit', 'class' => 'h-5 w-5'])
                                        </a>
                                        <form action="{{ route('admin.social-media.destroy', $stat) }}" method="POST" onclick="event.stopPropagation()" onsubmit="return confirm('Hapus data media sosial ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="return_to" value="{{ request()->fullUrl() }}">
                                            <button type="submit" class="flex h-11 w-11 items-center justify-center rounded-[1rem] border-4 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-md transition-colors hover:bg-[#ffcccc] active:scale-95" title="Hapus Data">
                                                @include('partials.wiboost-icon', ['name' => 'trash', 'class' => 'h-5 w-5'])
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-16 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem] border-4 border-white bg-[#f0f5ff] text-[#8faaf3] shadow-md">@include('partials.wiboost-icon', ['name' => 'chart', 'class' => 'h-8 w-8'])</div>
                                    <p class="mt-5 text-lg font-black text-[#2b3a67]">{{ $search !== '' ? 'Data media sosial tidak ditemukan.' : 'Belum ada data media sosial.' }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($stats->hasPages())
            <div class="w-full min-w-0 p-5 sm:p-8">{{ $stats->links() }}</div>
        @endif
    </div>

    @foreach($stats as $stat)
        <div id="admin-social-detail-{{ $stat->id }}" class="admin-detail-modal pointer-events-none fixed inset-0 z-[9999] flex items-center justify-center opacity-0 transition-all duration-200">
            <div class="absolute inset-0" onclick="closeAdminUniversalDetail('admin-social-detail-{{ $stat->id }}')"></div>
            <div class="admin-detail-card relative flex w-full scale-95 transform flex-col rounded-[1.75rem] border-4 border-white bg-white p-4 shadow-2xl shadow-[#2b3a67]/20 transition-transform duration-200 sm:p-5">
                <div class="admin-detail-header mb-3.5 flex items-start justify-between gap-3 p-3.5">
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Detail Media Sosial</p>
                        <h3 class="mt-1 text-xl font-black text-[#2b3a67]">{{ $platformLabels[$stat->platform] ?? ucfirst($stat->platform) }}</h3>
                        <p class="mt-1 whitespace-nowrap text-xs font-bold text-[#8faaf3]">{{ $monthLabels[$stat->month] ?? '-' }} {{ $stat->year }}</p>
                    </div>
                    <button type="button" onclick="closeAdminUniversalDetail('admin-social-detail-{{ $stat->id }}')" class="admin-detail-close flex h-11 w-11 shrink-0 items-center justify-center border-2 border-white">@include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-4 w-4'])</button>
                </div>
                <div class="admin-detail-body">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Kategori</p>
                            <span class="mt-2 inline-flex items-center whitespace-nowrap rounded-[1rem] border-2 px-4 py-2 text-[10px] font-black uppercase tracking-wide shadow-sm {{ $stat->platform === 'instagram' ? 'border-[#ffc7dc] bg-[#ffeaf3] text-[#e1306c]' : 'border-[#a9eaf2] bg-[#e8fbff] text-[#16889a]' }}">{{ $platformLabels[$stat->platform] ?? ucfirst($stat->platform) }}</span>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu</p>
                            <p class="mt-1 whitespace-nowrap text-sm font-black text-[#2b3a67]">{{ $monthLabels[$stat->month] ?? '-' }} {{ $stat->year }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Total Postingan</p>
                            <p class="mt-1 text-2xl font-black text-[#2b3a67]">{{ number_format($stat->total_posts, 0, ',', '.') }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Total Followers</p>
                            <p class="mt-1 text-2xl font-black text-[#5a76c8]">{{ number_format($stat->total_followers, 0, ',', '.') }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Jangkauan</p>
                            <p class="mt-1 text-2xl font-black text-[#4bc6b9]">{{ number_format($stat->reach, 0, ',', '.') }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Kunjungan Profil</p>
                            <p class="mt-1 text-2xl font-black text-[#2b3a67]">{{ number_format($stat->profile_visits, 0, ',', '.') }}</p>
                        </div>
                        <div class="admin-detail-panel md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Klik Link Bio</p>
                            <p class="mt-1 text-2xl font-black text-[#d99b16]">{{ number_format($stat->bio_link_clicks, 0, ',', '.') }}</p>
                        </div>
                        <div class="admin-detail-panel md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Link Postingan Teramai</p>
                            @if($stat->top_post_url)
                                <a href="{{ $stat->top_post_url }}" target="_blank" rel="noopener noreferrer" class="mt-2 block break-all text-sm font-black text-[#5a76c8] underline decoration-current decoration-2 underline-offset-4">{{ $stat->top_post_url }}</a>
                            @else
                                <p class="mt-1 text-sm font-bold text-[#8faaf3]">-</p>
                            @endif
                        </div>
                        <div class="admin-detail-panel md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Link Postingan Tersepi</p>
                            @if($stat->low_post_url)
                                <a href="{{ $stat->low_post_url }}" target="_blank" rel="noopener noreferrer" class="mt-2 block break-all text-sm font-black text-[#5a76c8] underline decoration-current decoration-2 underline-offset-4">{{ $stat->low_post_url }}</a>
                            @else
                                <p class="mt-1 text-sm font-bold text-[#8faaf3]">-</p>
                            @endif
                        </div>
                        <div class="admin-detail-panel admin-detail-panel-soft md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Aksi</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a href="{{ route('admin.social-media.edit', ['socialMediaStat' => $stat, 'return_to' => request()->fullUrl()]) }}" class="rounded-full border-2 border-white bg-[#fbbf24] px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white shadow-sm">Ubah Data</a>
                                <form action="{{ route('admin.social-media.destroy', $stat) }}" method="POST" onsubmit="return confirm('Hapus data media sosial ini?');" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="return_to" value="{{ request()->fullUrl() }}">
                                    <button type="submit" class="inline-flex items-center rounded-full border-2 border-white bg-[#ff6b6b] px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white shadow-sm">Hapus Data</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
