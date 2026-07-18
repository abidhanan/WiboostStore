@extends('layouts.admin')

@section('title', 'Penarikan Komisi')
@section('admin_header_subtitle', 'Konfirmasi penarikan reseller setelah transfer.')

@section('content')
@php
    $searchValue = $search ?? request('search');
    $activeStatus = $status ?? request('status', 'all');
    $formatRupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $formatDate = fn ($value) => $value ? $value->timezone(config('app.timezone'))->format('d M Y, H:i') : '-';
    $statusFilters = [
        'all' => ['label' => 'Semua', 'text' => '#5a76c8', 'bg' => '#f4f9ff'],
        'pending' => ['label' => 'Menunggu', 'text' => '#d99b16', 'bg' => '#fff7d6'],
        'approved' => ['label' => 'Disetujui', 'text' => '#22c55e', 'bg' => '#e6fff7'],
        'rejected' => ['label' => 'Ditolak', 'text' => '#ff6b6b', 'bg' => '#ffe5e5'],
    ];
    $statusMeta = function (?string $status): array {
        return match ($status) {
            'approved' => ['label' => 'Disetujui', 'class' => 'bg-[#e6fff7] text-emerald-500'],
            'rejected' => ['label' => 'Ditolak', 'class' => 'bg-[#ffe5e5] text-[#ff6b6b]'],
            default => ['label' => 'Menunggu', 'class' => 'bg-[#fff7d6] text-[#d99b16]'],
        };
    };
@endphp

<style>
    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; border: 2px solid #f0f5ff; }
    .admin-detail-modal {
        display: none;
        inset: 0 !important;
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
            padding-top: 7.5rem;
            padding-left: 26rem;
            padding-right: 2rem;
        }
    }
    @media (max-width: 639px) {
        .admin-detail-modal { padding-top: 8.5rem; }
    }
    .admin-detail-modal.modal-active {
        display: flex;
        opacity: 1 !important;
        pointer-events: auto !important;
    }
    .admin-detail-modal.modal-active .admin-detail-card { transform: scale(1) !important; }
    .admin-detail-card {
        width: min(100%, 48rem);
        max-height: calc(100dvh - 9rem) !important;
        overflow: hidden;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .admin-detail-card * { min-width: 0; }
    .admin-detail-header {
        border-bottom: 3px dashed #f4f9ff;
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
    .admin-detail-panel {
        overflow: hidden;
        border-radius: 1.2rem;
        border: 1px solid #eaf2ff;
        background: #f8faff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.9);
        padding: .85rem 1rem;
    }
    .admin-detail-panel-soft {
        border-color: #d9fff5;
        background: #f2fffb;
    }
</style>

<div class="wiboost-font relative z-10 w-full min-w-0 pb-12">
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-sm font-black text-emerald-500 shadow-lg shadow-emerald-100/50 sm:px-6 sm:text-base">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-5 py-4 text-sm font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="relative z-10 mb-5 w-full min-w-0 rounded-[2rem] border-4 border-white bg-white/90 p-4 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm transition-transform duration-300 hover:border-[#bde0fe] sm:mb-6 sm:rounded-[2.5rem] sm:p-5">
        <form action="{{ route('admin.withdrawals.index') }}" method="GET" class="flex w-full flex-col gap-3 md:flex-row">
            <div class="flex w-full min-w-0 flex-1 gap-2">
                @if($activeStatus !== 'all')
                    <input type="hidden" name="status" value="{{ $activeStatus }}">
                @endif
                <input type="text" name="search" value="{{ $searchValue }}"
                       class="min-w-0 flex-1 truncate rounded-[1rem] border-4 border-white bg-[#f4f9ff] px-4 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:rounded-[1.2rem] sm:px-5 sm:text-sm"
                       placeholder="Cari reseller, e-wallet, atau nomor tujuan...">
                <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-xs font-black text-white shadow-md transition-transform active:scale-95 sm:rounded-[1.2rem]" title="Cari Penarikan">
                    @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                </button>
                @if($searchValue)
                    <a href="{{ route('admin.withdrawals.index', array_filter(['status' => $activeStatus === 'all' ? null : $activeStatus])) }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#ffe5e5] text-xs font-black text-[#ff6b6b] shadow-md transition-transform active:scale-95 sm:rounded-[1.2rem]" title="Reset">
                        @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                    </a>
                @endif
            </div>

            <a href="{{ route('admin.withdrawals.index') }}" class="flex h-12 w-full shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#8faaf3] px-6 py-3 text-sm font-black text-white shadow-md transition-transform hover:bg-[#6f8ddc] active:scale-95 md:w-auto sm:rounded-[1.2rem]">
                @include('partials.wiboost-icon', ['name' => 'refresh', 'class' => 'mr-2 h-5 w-5'])
                Segarkan
            </a>
        </form>
    </div>

    @include('admin.partials.filter-tabs', [
        'filters' => $statusFilters,
        'activeFilter' => $activeStatus,
        'filterParam' => 'status',
        'filterRoute' => 'admin.withdrawals.index',
        'filterQuery' => ['search' => $searchValue],
    ])

    <div class="relative z-10 w-full min-w-0 overflow-hidden rounded-[2rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:rounded-[2.5rem]">
        <div class="table-scroll w-full overflow-x-auto pb-2">
            <div class="inline-block min-w-full p-4 align-middle sm:p-6">
                <table class="w-full min-w-[1240px] whitespace-nowrap text-left">
                    <thead>
                        <tr>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Invoice</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama Reseller</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Email</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Metode</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nomor Tujuan</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama Tujuan</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-right text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nominal</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($withdrawals ?? [] as $withdrawal)
                            @php
                                $meta = $statusMeta($withdrawal->status);
                                $payoutName = data_get($withdrawal->metadata, 'payout_name') ?: $withdrawal->user?->reseller_payout_name;
                            @endphp
                            <tr onclick="openAdminWithdrawalDetail('admin-withdrawal-detail-{{ $withdrawal->id }}')" class="group cursor-pointer transition-colors hover:bg-[#f8faff]">
                                <td class="px-5 py-5 align-middle">
                                    <p class="inline-flex rounded-md border border-white bg-[#f4f9ff] px-3 py-1 text-xs font-black text-[#5a76c8]">WD-{{ str_pad((string) $withdrawal->id, 5, '0', STR_PAD_LEFT) }}</p>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <p class="text-sm font-black text-[#2b3a67]">{{ $formatDate($withdrawal->created_at) }}</p>
                                </td>
                                <td class="min-w-[180px] px-5 py-5 align-middle">
                                    <p class="text-sm font-black text-[#2b3a67]">{{ $withdrawal->user?->name ?: '-' }}</p>
                                </td>
                                <td class="min-w-[220px] px-5 py-5 align-middle">
                                    <p class="text-[10px] font-bold text-[#8faaf3]">{{ $withdrawal->user?->email ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <p class="text-sm font-black text-[#5a76c8]">{{ $withdrawal->payout_method ?: '-' }}</p>
                                </td>
                                <td class="min-w-[190px] px-5 py-5 align-middle">
                                    <p class="break-all text-xs font-bold text-[#8faaf3]">{{ $withdrawal->payout_account ?: '-' }}</p>
                                </td>
                                <td class="min-w-[160px] px-5 py-5 align-middle">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-[#4bc6b9]">{{ $payoutName ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-5 text-right align-middle">
                                    <p class="text-lg font-black text-[#4bc6b9]">{{ $formatRupiah($withdrawal->amount) }}</p>
                                </td>
                                <td class="px-5 py-5 text-center align-middle">
                                    <span class="{{ $meta['class'] }} inline-flex w-32 justify-center rounded-full border-2 border-white px-4 py-1.5 text-[9px] font-black uppercase tracking-widest shadow-sm">
                                        {{ $meta['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-20 text-center">
                                    <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-[2rem] border-4 border-white bg-[#f0f5ff] text-4xl font-black text-[#5a76c8] shadow-inner">
                                        WD
                                    </div>
                                    <p class="text-xl font-black text-[#5a76c8]">Belum ada pengajuan penarikan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @foreach($withdrawals ?? [] as $withdrawal)
        @php
            $detailMeta = $statusMeta($withdrawal->status);
            $detailPayoutName = data_get($withdrawal->metadata, 'payout_name') ?: $withdrawal->user?->reseller_payout_name;
        @endphp
        <div id="admin-withdrawal-detail-{{ $withdrawal->id }}" class="admin-detail-modal fixed z-[9999] items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
            <div class="absolute inset-0" onclick="closeAdminWithdrawalDetail('admin-withdrawal-detail-{{ $withdrawal->id }}')"></div>
            <div class="admin-detail-card relative flex max-h-[calc(100dvh-8.25rem)] w-full max-w-3xl scale-95 flex-col rounded-[1.75rem] border-4 border-white bg-white p-4 shadow-2xl shadow-[#2b3a67]/20 transition-transform duration-200 sm:p-5">
                <div class="admin-detail-header mb-3.5 flex items-start justify-between gap-3 p-3.5">
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Detail Penarikan</p>
                        <h3 class="mt-1 text-xl font-black text-[#2b3a67]">WD-{{ str_pad((string) $withdrawal->id, 5, '0', STR_PAD_LEFT) }}</h3>
                        <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $formatDate($withdrawal->created_at) }}</p>
                    </div>
                    <button type="button" onclick="closeAdminWithdrawalDetail('admin-withdrawal-detail-{{ $withdrawal->id }}')" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#ffe5e5] text-lg font-black text-[#ff6b6b]">
                        x
                    </button>
                </div>

                <div class="admin-detail-body">
                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $withdrawal->user?->name ?: '-' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Email</p>
                            <p class="mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $withdrawal->user?->email ?: '-' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nomor Kontak</p>
                            <p class="mt-1 text-sm font-black text-[#5a76c8]">{{ $withdrawal->user?->whatsapp ?: '-' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</p>
                            <span class="{{ $detailMeta['class'] }} mt-2 inline-flex rounded-full border-2 border-white px-4 py-1.5 text-[9px] font-black uppercase tracking-widest shadow-sm">{{ $detailMeta['label'] }}</span>
                        </div>
                        <div class="admin-detail-panel md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Tujuan Penarikan</p>
                            <p class="mt-2 text-base font-black text-[#2b3a67]">{{ $withdrawal->payout_method ?: '-' }}</p>
                            <p class="mt-1 break-all text-sm font-bold text-[#5a76c8]">{{ $withdrawal->payout_account ?: '-' }}</p>
                            <p class="mt-1 text-xs font-black uppercase tracking-widest text-[#4bc6b9]">{{ $detailPayoutName ?: '-' }}</p>
                        </div>
                        <div class="admin-detail-panel admin-detail-panel-soft">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#08a878]">Total Komisi</p>
                            <p class="mt-1 text-2xl font-black text-[#4bc6b9]">{{ $formatRupiah($withdrawal->amount) }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Dikonfirmasi Oleh</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $withdrawal->approver?->name ?: '-' }}</p>
                            <p class="mt-1 text-[10px] font-bold text-[#8faaf3]">{{ $formatDate($withdrawal->approved_at) }}</p>
                        </div>
                        <div class="admin-detail-panel md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Catatan</p>
                            <p class="mt-2 whitespace-pre-wrap text-sm font-bold leading-relaxed text-[#2b3a67]">{{ $withdrawal->notes ?: '-' }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.withdrawals.update', $withdrawal) }}" class="mt-4 rounded-[1.5rem] border-4 border-white bg-[#f8fbff] p-4 shadow-sm" data-no-submit-lock="true">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="search" value="{{ $searchValue }}">
                        <input type="hidden" name="filter_status" value="{{ $activeStatus }}">
                        <label class="mb-2 block text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Catatan Admin</label>
                        <textarea name="notes" rows="3" class="w-full rounded-[1.2rem] border-4 border-white bg-white px-4 py-3 text-sm font-bold text-[#2b3a67] shadow-inner outline-none focus:border-[#bde0fe]" placeholder="Tambahkan catatan konfirmasi transfer...">{{ old('notes', $withdrawal->notes) }}</textarea>
                        <div class="mt-3 grid gap-2 sm:grid-cols-3">
                            <button type="submit" name="status" value="approved" class="rounded-full border-2 border-white bg-[#22c55e] px-4 py-3 text-xs font-black uppercase tracking-widest text-white shadow-md shadow-emerald-100 transition active:scale-95">
                                Setujui
                            </button>
                            <button type="submit" name="status" value="rejected" class="rounded-full border-2 border-white bg-[#ff6b6b] px-4 py-3 text-xs font-black uppercase tracking-widest text-white shadow-md shadow-[#ff6b6b]/20 transition active:scale-95">
                                Tolak
                            </button>
                            <button type="submit" name="status" value="pending" class="rounded-full border-2 border-white bg-[#f4b740] px-4 py-3 text-xs font-black uppercase tracking-widest text-white shadow-md shadow-amber-100 transition active:scale-95">
                                Menunggu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
    function openAdminWithdrawalDetail(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('modal-active');
        document.body.classList.add('overflow-hidden', 'admin-detail-open');
    }

    function closeAdminWithdrawalDetail(id) {
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
