@extends('layouts.admin')

@section('title', 'Manajemen Email')
@section('admin_header_subtitle', 'Kirim informasi kepada pengguna.')

@section('content')
@php
    $scopeLabels = [
        'all' => 'Semua User',
        'buyer' => 'Buyer',
        'member' => 'Member',
        'reseller' => 'Reseller',
        'specific' => 'Email Tertentu',
    ];
    $statusLabels = [
        'queued' => 'Dalam Antrean',
        'processing' => 'Sedang Dikirim',
        'completed' => 'Selesai',
        'completed_with_errors' => 'Selesai Sebagian',
        'failed' => 'Gagal',
    ];
    $statusClasses = [
        'queued' => 'bg-[#fff7e0] text-[#d38b00]',
        'processing' => 'bg-[#e8f1ff] text-[#5a76c8]',
        'completed' => 'bg-[#e0fbfc] text-[#35b8aa]',
        'completed_with_errors' => 'bg-[#fff1d6] text-[#d38b00]',
        'failed' => 'bg-[#ffe5e5] text-[#ff6b6b]',
    ];
@endphp

<style>
    .email-table-scroll::-webkit-scrollbar { height: 8px; }
    .email-table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .email-table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border: 2px solid #f0f5ff; border-radius: 10px; }
    .email-table-scroll::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
</style>

<div class="wiboost-font relative z-10 w-full min-w-0 pb-12">
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-sm font-black text-emerald-500 shadow-lg shadow-emerald-100/50 sm:px-6 sm:text-base">
            @include('partials.wiboost-icon', ['name' => 'success', 'class' => 'h-6 w-6 shrink-0'])
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="relative z-10 mb-8 w-full min-w-0 rounded-[2rem] border-4 border-white bg-white/90 p-4 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:rounded-[2.5rem] sm:p-5">
        <form action="{{ route('admin.emails.index') }}" method="GET" class="flex w-full flex-col gap-3 md:flex-row">
            <div class="flex w-full min-w-0 flex-1 gap-2">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    class="min-w-0 flex-1 truncate rounded-[1rem] border-4 border-white bg-[#f4f9ff] px-4 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:rounded-[1.2rem] sm:px-5 sm:text-sm"
                    placeholder="Cari subjek, isi email, penerima, atau pengirim..."
                >
                <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-white shadow-md transition-transform active:scale-95 sm:rounded-[1.2rem]" title="Cari Email">
                    @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                </button>
                @if($search !== '')
                    <a href="{{ route('admin.emails.index') }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-md transition-transform active:scale-95 sm:rounded-[1.2rem]" title="Reset">
                        @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                    </a>
                @endif
            </div>

            <a href="{{ route('admin.emails.create') }}" class="flex h-12 w-full shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#5a76c8] px-6 py-3 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 md:w-auto sm:rounded-[1.2rem]">
                @include('partials.wiboost-icon', ['name' => 'send', 'class' => 'h-5 w-5'])
                <span class="whitespace-nowrap">Kirim Email</span>
            </a>
        </form>
    </div>

    <div class="overflow-visible rounded-[2rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem]">
        <div class="email-table-scroll w-full overflow-x-auto pb-2">
            <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                <table class="w-full text-left" style="min-width:1250px;table-layout:fixed;">
                    <thead>
                        <tr>
                            <th style="width:155px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu</th>
                            <th style="width:310px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Email</th>
                            <th style="width:145px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Penerima</th>
                            <th style="width:105px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Jumlah</th>
                            <th style="width:105px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Terkirim</th>
                            <th style="width:95px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Gagal</th>
                            <th style="width:175px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Pengirim</th>
                            <th style="width:160px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($campaigns as $campaign)
                            <tr class="transition-colors hover:bg-[#f8faff]">
                                <td class="px-5 py-5 align-middle">
                                    <p class="whitespace-nowrap text-[10px] font-bold text-[#8faaf3]">{{ $campaign->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <p class="truncate text-sm font-black text-[#2b3a67]" title="{{ $campaign->subject }}">{{ $campaign->subject }}</p>
                                    <p class="mt-1 truncate text-[10px] font-bold text-[#8faaf3]" title="{{ $campaign->body }}">{{ $campaign->body }}</p>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <span class="inline-flex whitespace-nowrap rounded-full border-2 border-white bg-[#f0f5ff] px-3 py-2 text-[10px] font-black uppercase tracking-wide text-[#5a76c8] shadow-sm">{{ $scopeLabels[$campaign->recipient_scope] ?? ucfirst($campaign->recipient_scope) }}</span>
                                </td>
                                <td class="px-5 py-5 text-center align-middle">
                                    <span class="font-black text-[#2b3a67]">{{ number_format($campaign->recipient_count, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-5 py-5 text-center align-middle">
                                    <span class="font-black text-[#35b8aa]">{{ number_format($campaign->sent_count, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-5 py-5 text-center align-middle">
                                    <span class="font-black text-[#ff6b6b]">{{ number_format($campaign->failed_count, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <p class="truncate text-xs font-black text-[#2b3a67]" title="{{ $campaign->creator?->name ?? 'Admin' }}">{{ $campaign->creator?->name ?? 'Admin' }}</p>
                                </td>
                                <td class="px-5 py-5 text-center align-middle">
                                    <span class="inline-flex whitespace-nowrap rounded-full border-2 border-white px-3 py-2 text-[10px] font-black uppercase tracking-wide shadow-sm {{ $statusClasses[$campaign->status] ?? 'bg-[#f0f5ff] text-[#5a76c8]' }}">{{ $statusLabels[$campaign->status] ?? ucfirst($campaign->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem] border-4 border-white bg-[#f0f5ff] text-[#8faaf3] shadow-md">
                                        @include('partials.wiboost-icon', ['name' => 'mail', 'class' => 'h-8 w-8'])
                                    </div>
                                    <p class="mt-5 text-lg font-black text-[#2b3a67]">{{ $search !== '' ? 'Email tidak ditemukan.' : 'Belum ada riwayat email.' }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($campaigns->hasPages())
            <div class="p-5 sm:p-8 w-full min-w-0">
                {{ $campaigns->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
