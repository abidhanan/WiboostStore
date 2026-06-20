@extends('layouts.admin')
@section('title', 'Riwayat Deposit')
@section('admin_header_subtitle', 'Pantau riwayat top-up atau pengisian saldo pengguna.')

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
</style>

<div class="wiboost-font pb-12 relative z-10 w-full min-w-0">
    <!-- Alert Messages (Aman dari 500 Error) -->
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 sm:px-6 py-4 font-black text-emerald-500 shadow-lg shadow-emerald-100/50 relative z-10 text-sm sm:text-base w-full">
            <span class="text-2xl sm:text-3xl drop-shadow-sm">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-5 sm:px-6 py-4 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 relative z-10 text-sm sm:text-base w-full">
            <span class="text-2xl sm:text-3xl drop-shadow-sm">⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Toolbar: Pencarian & Segarkan -->
    <div class="mb-6 sm:mb-8 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-4 sm:p-5 shadow-xl shadow-[#bde0fe]/30 relative z-10 w-full min-w-0 transition-transform duration-300 hover:border-[#bde0fe]">
        <form action="{{ route('admin.deposits.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 w-full">
            
            <!-- Wrapper Input Cari -->
            <div class="flex-1 flex gap-2 w-full min-w-0">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="min-w-0 flex-1 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] py-3 px-4 sm:px-5 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe] text-xs sm:text-sm truncate"
                    placeholder="Cari Invoice atau Pelanggan...">
                
                <button type="submit" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] w-12 h-12 text-white shadow-md active:scale-95 transition-transform" title="Cari Deposit">
                    🔍
                </button>
                
                @if(request('search'))
                    <a href="{{ route('admin.deposits.index') }}" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#ffe5e5] w-12 h-12 text-[#ff6b6b] shadow-md active:scale-95 transition-transform" title="Reset">
                        ✖️
                    </a>
                @endif
            </div>
            
            <!-- Tombol Segarkan -->
            <a href="{{ route('admin.deposits.index') }}" class="shrink-0 flex items-center justify-center gap-2 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#8faaf3] px-6 py-3 h-12 text-sm font-black text-white shadow-md transition-transform active:scale-95 hover:bg-[#6f8ddc] w-full md:w-auto">
                <span class="text-base">🔄</span> <span class="whitespace-nowrap">Segarkan</span>
            </a>
            
        </form>
    </div>

    <!-- Tabel Data Deposit -->
    <div class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm shadow-2xl shadow-[#bde0fe]/30 relative z-10 min-w-0 w-full">
        <div class="w-full overflow-x-auto table-scroll pb-2">
            <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                <table class="w-full min-w-[800px] text-left whitespace-nowrap">
                    <thead>
                        <tr>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Waktu & Invoice</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Pelanggan</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Nominal</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Metode</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($deposits ?? [] as $deposit)
                        <tr onclick="openAdminDepositDetail('admin-deposit-detail-{{ $deposit->id }}')" class="transition-colors hover:bg-[#f8faff] group cursor-pointer">
                            
                            <!-- Invoice & Waktu -->
                            <td class="px-5 py-4 sm:py-5 align-middle min-w-[150px]">
                                <p class="font-black text-[#5a76c8] text-xs sm:text-sm mb-1 bg-[#f4f9ff] inline-block px-2 py-1 rounded-md border border-white truncate max-w-full">{{ $deposit->invoice_number ?? '-' }}</p>
                                <p class="text-[9px] sm:text-[10px] font-bold text-[#8faaf3] ml-1">{{ isset($deposit->created_at) ? $deposit->created_at->format('d M Y, H:i') : '-' }}</p>
                            </td>
                            
                            <!-- User -->
                            <td class="px-5 py-4 sm:py-5 align-middle min-w-[150px] max-w-[200px]">
                                <p class="font-black text-[#2b3a67] text-xs sm:text-sm truncate">{{ $deposit->user->name ?? 'Guest' }}</p>
                                <p class="text-[9px] sm:text-[10px] font-bold text-[#8faaf3] truncate mt-1">{{ $deposit->user->email ?? '-' }}</p>
                            </td>
                            
                            <!-- Nominal -->
                            <td class="px-5 py-4 sm:py-5 align-middle">
                                <p class="font-black text-[#4bc6b9] text-sm sm:text-base drop-shadow-sm">Rp {{ number_format($deposit->amount ?? 0, 0, ',', '.') }}</p>
                                @if(($deposit->paid_amount ?? null) && (float) $deposit->paid_amount !== (float) $deposit->amount)
                                    <p class="mt-1 text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">
                                        Dibayar: Rp {{ number_format($deposit->paid_amount, 0, ',', '.') }}
                                    </p>
                                @endif
                            </td>
                            
                            <!-- Metode Pembayaran -->
                            <td class="px-5 py-4 sm:py-5 align-middle text-center">
                                @php
                                    $methodDisplay = $deposit->display_payment_method;
                                    if ($methodDisplay === 'BELUM PILIH') {
                                        $colorClass = 'bg-[#fff5eb] text-amber-500 border-white';
                                    } else {
                                        $colorClass = 'bg-[#f4f9ff] text-[#8faaf3] border-white';
                                    }
                                @endphp
                                <span style="font-size: 9px;" class="font-black uppercase tracking-widest px-3 py-1.5 rounded-full border-2 shadow-sm inline-block {{ $colorClass }}">
                                    {{ $methodDisplay }}
                                </span>
                            </td>
                            
                            <!-- Status -->
                            <td class="px-5 py-4 sm:py-5 align-middle text-center min-w-[120px]">
                                @php
                                    // Status pada deposit umumnya adalah paid, unpaid, failed, atau expired
                                    $statusLabels = [
                                        'paid' => ['bg-[#e6fff7]', 'text-emerald-500', 'Berhasil'],
                                        'success' => ['bg-[#e6fff7]', 'text-emerald-500', 'Berhasil'],
                                        'unpaid' => ['bg-[#fff5eb]', 'text-amber-500', 'Pending'],
                                        'pending' => ['bg-[#fff5eb]', 'text-amber-500', 'Pending'],
                                        'failed' => ['bg-[#ffe5e5]', 'text-[#ff6b6b]', 'Gagal'],
                                        'expired' => ['bg-[#ffe5e5]', 'text-[#ff6b6b]', 'Expired'],
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
                            <td colspan="5" class="px-5 py-16 sm:py-20 text-center">
                                <div class="mb-4 sm:mb-5 inline-flex h-20 w-20 sm:h-24 sm:w-24 items-center justify-center rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-[#f0f5ff] text-4xl sm:text-5xl shadow-inner animate-float">💰</div>
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
                    'unpaid' => ['bg-[#fff5eb]', 'text-amber-500', 'Pending'],
                    'pending' => ['bg-[#fff5eb]', 'text-amber-500', 'Pending'],
                    'failed' => ['bg-[#ffe5e5]', 'text-[#ff6b6b]', 'Gagal'],
                    'expired' => ['bg-[#ffe5e5]', 'text-[#ff6b6b]', 'Expired'],
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
                            <h3 class="mt-1 break-all text-xl font-black text-[#2b3a67]">{{ $deposit->invoice_number }}</h3>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ optional($deposit->created_at)->format('d M Y, H:i') }}</p>
                        </div>
                        <button type="button" onclick="closeAdminDepositDetail('admin-deposit-detail-{{ $deposit->id }}')" class="admin-detail-close flex h-9 w-9 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#ffe5e5] text-lg font-black text-[#ff6b6b]">×</button>
                    </div>

                    <div class="admin-detail-body">
                    <div class="grid gap-3.5 md:grid-cols-2">
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Pelanggan</p>
                            <p class="mt-1 font-black text-[#2b3a67]">{{ $deposit->user->name ?? 'Guest' }}</p>
                            <p class="mt-1 break-all text-xs font-bold text-[#8faaf3]">{{ $deposit->user->email ?? '-' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</p>
                            <span class="{{ $depositStatus[0] }} {{ $depositStatus[1] }} mt-2 inline-flex rounded-full border-2 border-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest shadow-sm">{{ $depositStatus[2] }}</span>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nominal Masuk Saldo</p>
                            <p class="mt-1 text-lg font-black text-[#4bc6b9]">Rp {{ number_format($deposit->amount ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Jumlah Dibayar</p>
                            <p class="mt-1 text-lg font-black text-[#5a76c8]">Rp {{ number_format($deposit->paid_amount ?? $deposit->amount ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Metode</p>
                            <p class="mt-1 font-black uppercase text-[#2b3a67]">{{ $deposit->display_payment_method }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Referensi QRIS</p>
                            <p class="mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $deposit->tripay_reference ?? '-' }}</p>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        @endforeach
        
        <!-- Paginasi -->
        @if(isset($deposits) && method_exists($deposits, 'links') && $deposits->hasPages())
            <div class="border-t-4 border-dashed border-[#f4f9ff] bg-[#f8faff] p-5 sm:p-8 w-full min-w-0">
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
        document.body.classList.add('overflow-hidden', 'admin-detail-open');
    }

    function closeAdminDepositDetail(id) {
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
