@extends('layouts.admin')

@section('title', 'Halaman Member')
@section('admin_header_subtitle', 'Pantau member aktif dan masa berlaku.')

@section('content')
@php
    $formatRupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $formatDate = fn ($value) => $value ? $value->timezone(config('app.timezone'))->format('d/m/Y H:i') : '-';
    $remainingText = function ($value): string {
        if (! $value) {
            return '-';
        }

        $diff = now()->diff($value);

        return trim($diff->days . ' hari ' . $diff->h . ' jam ' . $diff->i . ' menit');
    };
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
    .admin-detail-panel-member {
        border-color: #ede9fe;
        background: #f7f3ff;
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
</style>

<div class="wiboost-font relative z-10 w-full min-w-0 pb-12">
    <div class="relative z-10 mb-6 flex w-full min-w-0 flex-col gap-3 sm:mb-8 sm:gap-4">
        <div class="min-w-0 rounded-[2rem] border-4 border-white bg-white/90 p-3 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm transition-transform duration-300 hover:border-[#bde0fe] sm:rounded-[2.5rem] sm:p-4">
            <form action="{{ route('admin.members.index') }}" method="GET" class="flex w-full items-center gap-2 sm:gap-3">
                <input
                    type="text"
                    name="search"
                    value="{{ $search ?? request('search') }}"
                    class="min-w-0 flex-1 truncate rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:rounded-[1.5rem] sm:px-6 sm:py-4 sm:text-sm"
                    placeholder="Cari Member"
                >

                <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#8b5cf6] text-xs font-black text-white shadow-md shadow-[#8b5cf6]/25 transition-transform hover:bg-[#7c3aed] active:scale-95 sm:rounded-[1.2rem]" title="Cari Member">
                    Cari
                </button>

                @if(request('search'))
                    <a href="{{ route('admin.members.index') }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#ffe5e5] text-xs font-black text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform hover:bg-[#ffcccc] active:scale-95 sm:rounded-[1.2rem]" title="Reset Pencarian">
                        X
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="relative z-10 flex w-full min-w-0 flex-col overflow-hidden rounded-[2rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:rounded-[2.5rem]">
        <div class="table-scroll w-full overflow-x-auto pb-2">
            <div class="inline-block min-w-full p-4 align-middle sm:p-6">
                <table class="w-full min-w-[860px] whitespace-nowrap text-left">
                    <thead>
                        <tr>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama & Email</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nomor Kontak</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Masa Member</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($members as $member)
                            <tr onclick="openAdminMemberDetail('admin-member-detail-{{ $member->id }}')" class="group cursor-pointer transition-colors hover:bg-[#f8faff]">
                                <td class="min-w-[300px] px-5 py-4 align-middle sm:py-5">
                                    <p class="mb-1 whitespace-nowrap text-sm font-black text-[#2b3a67]">{{ $member->name }}</p>
                                    <p class="inline-block whitespace-nowrap rounded-md border border-white bg-[#f4f9ff] px-3 py-1 text-[10px] font-bold text-[#8faaf3] sm:text-[11px]">{{ $member->email }}</p>
                                </td>
                                <td class="px-5 py-4 align-middle sm:py-5">
                                    <p class="text-sm font-black text-[#5a76c8]">{{ $member->whatsapp ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-center align-middle sm:py-5">
                                    <span class="inline-block rounded-full border-2 border-white bg-[#ede9fe] px-4 py-1.5 text-[9px] font-black uppercase tracking-widest text-[#8b5cf6] shadow-sm">Member</span>
                                    <p class="mt-2 text-[10px] font-bold text-[#8faaf3]">{{ $formatDate($member->membership_expires_at) }}</p>
                                </td>
                                <td class="px-5 py-4 align-middle sm:py-5">
                                    <p class="text-sm font-black text-[#4bc6b9] drop-shadow-sm sm:text-base">{{ $formatRupiah($member->balance) }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-16 text-center">
                                    <div class="mb-4 inline-flex h-20 w-20 items-center justify-center rounded-[2rem] border-4 border-white bg-[#f0f5ff] shadow-inner sm:mb-5 sm:h-24 sm:w-24 sm:rounded-[2.5rem]">
                                        <span class="text-4xl font-black text-[#8b5cf6] sm:text-5xl">?</span>
                                    </div>
                                    <p class="text-lg font-black text-[#5a76c8] sm:text-xl">Belum ada member aktif.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @foreach($members as $member)
        <div id="admin-member-detail-{{ $member->id }}" class="admin-detail-modal fixed z-[9999] items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
            <div class="absolute inset-0" onclick="closeAdminMemberDetail('admin-member-detail-{{ $member->id }}')"></div>
            <div class="admin-detail-card relative flex max-h-[calc(100dvh-8.25rem)] w-full max-w-3xl scale-95 flex-col rounded-[1.75rem] border-4 border-white bg-white p-4 shadow-2xl shadow-[#2b3a67]/20 transition-transform duration-200 sm:p-5">
                <div class="admin-detail-header mb-3.5 flex items-start justify-between gap-3 p-3.5">
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Detail Member</p>
                        <h3 class="mt-1 text-xl font-black text-[#2b3a67]">{{ $member->name }}</h3>
                    </div>
                    <button type="button" onclick="closeAdminMemberDetail('admin-member-detail-{{ $member->id }}')" class="admin-detail-close flex h-9 w-9 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#ffe5e5] text-lg font-black text-[#ff6b6b]">x</button>
                </div>

                <div class="admin-detail-body">
                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $member->name }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Email</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $member->email }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nomor Kontak</p>
                            <p class="mt-1 text-sm font-black text-[#5a76c8]">{{ $member->whatsapp ?? '-' }}</p>
                        </div>
                        <div class="admin-detail-panel admin-detail-panel-member">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8b5cf6]">Status Member</p>
                            <p class="mt-1 text-sm font-black uppercase text-[#8b5cf6]">Aktif</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Aktif Sampai</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $formatDate($member->membership_expires_at) }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Sisa Durasi</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $remainingText($member->membership_expires_at) }}</p>
                        </div>
                        <div class="admin-detail-panel admin-detail-panel-member md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8b5cf6]">Saldo</p>
                            <p class="mt-1 text-2xl font-black text-[#4bc6b9]">{{ $formatRupiah($member->balance) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
    function openAdminMemberDetail(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('modal-active');
        document.body.classList.add('overflow-hidden', 'admin-detail-open');
    }

    function closeAdminMemberDetail(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('modal-active');
        document.body.classList.remove('overflow-hidden', 'admin-detail-open');
    }

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        document.querySelectorAll('.admin-detail-modal.modal-active').forEach((modal) => {
            modal.classList.remove('modal-active');
        });

        document.body.classList.remove('overflow-hidden', 'admin-detail-open');
    });
</script>
@endsection
