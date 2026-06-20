@extends('layouts.admin')
@section('title', 'Riwayat Transaksi')
@section('admin_header_subtitle', 'Pantau pesanan pelanggan dan filter berdasarkan bulan.')

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
    .admin-detail-panel-soft {
        border-color: #fff1d6;
        background: #fffdf7;
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

    .table-scroll {
        contain: layout paint;
        overscroll-behavior-x: contain;
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

        .table-scroll {
            -webkit-overflow-scrolling: touch;
        }

        .table-scroll table {
            transform: translateZ(0);
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

    <!-- Toolbar Utama: Pencarian, Segarkan, Filter & Cetak -->
    <div class="mb-6 sm:mb-8 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-4 sm:p-5 shadow-xl shadow-[#bde0fe]/30 relative z-10 w-full min-w-0 transition-transform duration-300 hover:border-[#bde0fe]">
        <form action="{{ route('admin.transactions.index') }}" method="GET" class="flex flex-col gap-4 w-full">
            
            <!-- Baris Atas: Input Cari + Tombol Cari + Reset + Segarkan -->
            <div class="flex flex-col md:flex-row gap-3 w-full min-w-0">
                <!-- Wrapper Input Cari -->
                <div class="flex-1 flex gap-2 w-full min-w-0">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="min-w-0 flex-1 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] py-3 px-4 sm:px-5 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe] text-xs sm:text-sm truncate"
                        placeholder="Cari Invoice atau Pelanggan...">
                    
                    <button type="submit" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] w-12 h-12 text-white shadow-md active:scale-95 transition-transform" title="Cari">
                        🔍
                    </button>
                    
                    @if(request('search'))
                        <a href="{{ route('admin.transactions.index') }}" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#ffe5e5] w-12 h-12 text-[#ff6b6b] shadow-md active:scale-95 transition-transform" title="Reset">
                            ✖️
                        </a>
                    @endif
                </div>
                
                <!-- Tombol Segarkan (Samping Search di Desktop) -->
                <a href="{{ route('admin.transactions.index', array_filter(['refresh_api' => 1, 'search' => request('search'), 'month' => $selectedMonth ?? date('m'), 'year' => $selectedYear ?? date('Y')])) }}" class="shrink-0 flex items-center justify-center gap-2 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#8faaf3] px-6 py-3 h-12 text-sm font-black text-white shadow-md transition-transform active:scale-95 hover:bg-[#6f8ddc] w-full md:w-auto">
                    <span class="text-base">🔄</span> <span class="whitespace-nowrap">Segarkan</span>
                </a>
            </div>

            <!-- Baris Bawah: Filter Bulan/Tahun + Cetak PDF -->
            <div class="flex flex-col md:flex-row justify-start md:justify-end items-center gap-3 w-full border-t-4 border-dashed border-[#f4f9ff] pt-4 mt-1">
                
                <!-- Filter Dropdown -->
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select name="month" onchange="this.form.submit()" class="bg-[#f4f9ff] rounded-[1rem] border-4 border-white shadow-inner text-[#2b3a67] px-4 py-2 font-black outline-none cursor-pointer text-xs sm:text-sm flex-1 md:flex-none text-center hover:border-[#bde0fe] transition-colors">
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ sprintf('%02d', $m) }}" {{ ($selectedMonth ?? '') == sprintf('%02d', $m) ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                    <select name="year" onchange="this.form.submit()" class="bg-[#f4f9ff] rounded-[1rem] border-4 border-white shadow-inner text-[#2b3a67] px-4 py-2 font-black outline-none cursor-pointer text-xs sm:text-sm flex-1 md:flex-none text-center hover:border-[#bde0fe] transition-colors">
                        @for($y=date('Y'); $y>=date('Y')-3; $y--)
                            <option value="{{ $y }}" {{ ($selectedYear ?? '') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Tombol Cetak PDF -->
                <a href="{{ route('admin.transactions.export_pdf', ['month' => $selectedMonth ?? date('m'), 'year' => $selectedYear ?? date('Y')]) }}" target="_blank" class="shrink-0 flex items-center justify-center gap-2 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#4bc6b9] px-6 py-2 h-auto min-h-[2.8rem] sm:min-h-[3rem] text-xs sm:text-sm font-black text-white shadow-md transition-transform active:scale-95 hover:bg-[#3ba398] w-full md:w-auto">
                    <span class="whitespace-nowrap">Cetak PDF</span>
                </a>
            </div>
        </form>

    </div>

    <!-- Tabel Data Transaksi -->
    <div class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm shadow-2xl shadow-[#bde0fe]/30 relative z-10 min-w-0 w-full">
        <div class="w-full overflow-x-auto table-scroll pb-2">
            <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                <table class="w-full min-w-[900px] text-left whitespace-nowrap">
                    <thead>
                        <tr>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Waktu & Invoice</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Pelanggan</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Layanan</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Harga</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Target</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Metode</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($transactions ?? [] as $trx)
                        @php
                            $adminTargetSummary = $trx->order_input_summary
                                ?: (filled($trx->target_data) && $trx->target_data !== '- (Tidak membutuhkan target tambahan)' ? $trx->target_data : null);
                            $adminTargetText = $trx->order_input_text
                                ?: $adminTargetSummary;
                            $canManualUpdate = $trx->payment_status === 'paid'
                                && ($trx->product?->process_type === 'account')
                                && (bool) ($trx->product?->requires_buyer_email);
                            $adminStatusClass = match ($trx->display_status) {
                                'Sukses' => ['bg-[#e6fff7]', 'text-emerald-500'],
                                'Proses' => ['bg-[#e0fbfc]', 'text-[#5a76c8]'],
                                'Batal', 'Gagal' => ['bg-[#ffe5e5]', 'text-[#ff6b6b]'],
                                default => ['bg-[#fff5eb]', 'text-amber-500'],
                            };
                        @endphp
                        <tr onclick="openAdminDetailModal('admin-trx-detail-{{ $trx->id }}')" class="transition-colors hover:bg-[#f8faff] group cursor-pointer">
                            
                            <!-- Invoice & Waktu -->
                            <td class="px-5 py-4 sm:py-5 align-top min-w-[150px]">
                                <p class="font-black text-[#5a76c8] text-xs sm:text-sm mb-1 bg-[#f4f9ff] inline-block px-2 py-1 rounded-md border border-white truncate max-w-full">{{ $trx->invoice_number ?? '-' }}</p>
                                <p class="text-[9px] sm:text-[10px] font-bold text-[#8faaf3] ml-1">{{ isset($trx->created_at) ? $trx->created_at->format('d M Y, H:i') : '-' }}</p>
                            </td>
                            
                            <!-- User -->
                            <td class="px-5 py-4 sm:py-5 align-top min-w-[120px] max-w-[150px]">
                                <p class="font-black text-[#2b3a67] text-xs sm:text-sm truncate">{{ $trx->user->name ?? 'Guest' }}</p>
                                <p class="text-[9px] sm:text-[10px] font-bold text-[#8faaf3] truncate mt-1">{{ $trx->user->email ?? '-' }}</p>
                            </td>
                            
                            <!-- Layanan -->
                            <td class="px-5 py-4 sm:py-5 align-top whitespace-normal min-w-[180px] max-w-[250px]">
                                <p class="font-black text-[#2b3a67] text-xs sm:text-sm line-clamp-2 leading-snug">{{ $trx->product->name ?? 'Produk Dihapus' }}</p>
                            </td>
                            
                            <!-- Harga -->
                            <td class="px-5 py-4 sm:py-5 align-top">
                                <p class="font-black text-[#4bc6b9] text-xs sm:text-sm drop-shadow-sm">Rp {{ number_format($trx->display_total_payable, 0, ',', '.') }}</p>
                            </td>
                            
                            <!-- Target Input -->
                            <td class="px-5 py-4 sm:py-5 align-top text-center max-w-[150px]">
                                @if($adminTargetSummary)
                                    <span style="font-size: 9px;" class="font-black text-[#2b3a67] bg-[#f0f5ff] inline-block px-3 py-1.5 rounded-xl border-2 border-white shadow-sm truncate max-w-full" title="{{ $adminTargetText ?? '' }}">
                                        {{ $adminTargetSummary }}
                                    </span>
                                @else
                                    <span class="text-sm font-black text-[#a3bbfb]">-</span>
                                @endif
                            </td>
                            
                            <!-- Metode Pembayaran -->
                            <td class="px-5 py-4 sm:py-5 align-top text-center">
                                @php
                                    $methodDisplay = $trx->display_payment_method;
                                    if ($methodDisplay === 'SALDO') {
                                        $colorClass = 'bg-[#e0fbfc] text-[#5a76c8] border-white';
                                    } elseif ($methodDisplay === 'BELUM PILIH') {
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
                            <td class="px-5 py-4 sm:py-5 align-top text-center min-w-[140px]">
                                @if($canManualUpdate)
                                    <form onclick="event.stopPropagation()" action="{{ route('admin.transactions.update', $trx->id) }}" method="POST" class="flex items-center gap-1.5 justify-center w-full">
                                        @csrf
                                        @method('PATCH')
                                        <select name="order_status" style="font-size: 9px;" class="bg-[#f8faff] border-4 border-white text-[#2b3a67] uppercase tracking-widest rounded-full focus:border-[#bde0fe] block px-3 py-1.5 font-black outline-none shadow-sm cursor-pointer hover:bg-[#e0fbfc] transition-colors appearance-none text-center w-full min-w-[85px]">
                                            <option value="pending" {{ ($trx->order_status ?? '') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                                            <option value="processing" {{ ($trx->order_status ?? '') == 'processing' ? 'selected' : '' }}>Proses</option>
                                            <option value="success" {{ ($trx->order_status ?? '') == 'success' ? 'selected' : '' }}>Sukses</option>
                                            <option value="failed" {{ ($trx->order_status ?? '') == 'failed' ? 'selected' : '' }}>Gagal</option>
                                        </select>
                                        <button type="submit" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white p-1.5 rounded-[0.8rem] transition shadow-md active:scale-95 border-2 border-white shrink-0 text-xs" title="Simpan Status">
                                            💾
                                        </button>
                                    </form>
                                @else
                                    @php
                                        $st = [$adminStatusClass[0], $adminStatusClass[1], $trx->display_status];
                                    @endphp
                                    <span style="font-size: 9px;" class="{{ $st[0] }} {{ $st[1] }} px-4 py-1.5 rounded-full font-black uppercase tracking-widest border-2 border-white shadow-sm inline-block w-full text-center">
                                        {{ $st[2] }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 sm:py-20 text-center">
                                <div class="mb-4 sm:mb-5 inline-flex h-20 w-20 sm:h-24 sm:w-24 items-center justify-center rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-[#f0f5ff] text-4xl sm:text-5xl shadow-inner animate-float">🧾</div>
                                <p class="text-lg sm:text-xl font-black text-[#5a76c8]">Tidak ada transaksi ditemukan.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @foreach($transactions ?? [] as $trx)
            @php
                $detailTargetSummary = $trx->order_input_summary
                    ?: (filled($trx->target_data) && $trx->target_data !== '- (Tidak membutuhkan target tambahan)' ? $trx->target_data : null);
                $detailTargetText = $trx->order_input_text
                    ?: ($detailTargetSummary ?: '-');
                $detailInputFields = collect($trx->order_input_fields ?? []);
                $detailInputText = $detailInputFields
                    ->reject(fn ($field) => ($field['name'] ?? '') === 'order_quantity')
                    ->map(fn ($field) => trim((string) ($field['value'] ?? '')))
                    ->filter()
                    ->implode("\n");
                $detailInputText = $detailInputText !== '' ? $detailInputText : ($detailTargetSummary ?: '-');
                $detailQuantity = $trx->provider_order_quantity;
                $detailCategory = collect([
                    $trx->product?->category?->parent?->parent?->name,
                    $trx->product?->category?->parent?->name,
                    $trx->product?->category?->name,
                ])->filter()->implode(' / ');
                $detailCanManualUpdate = $trx->payment_status === 'paid'
                    && ($trx->product?->process_type === 'account')
                    && (bool) ($trx->product?->requires_buyer_email);
            @endphp
            <div id="admin-trx-detail-{{ $trx->id }}" class="admin-detail-modal fixed inset-0 z-[9999] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
                <div class="absolute inset-0" onclick="closeAdminDetailModal('admin-trx-detail-{{ $trx->id }}')"></div>
                <div class="admin-detail-card relative flex w-full max-w-2xl max-h-[calc(100dvh-8.25rem)] flex-col rounded-[1.75rem] border-4 border-white bg-white p-4 shadow-2xl shadow-[#2b3a67]/20 transform scale-95 transition-transform duration-200 sm:p-5">
                    <div class="admin-detail-header mb-3.5 flex items-start justify-between gap-3 p-3.5">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Detail Transaksi</p>
                            <h3 class="mt-1 break-all text-xl font-black text-[#2b3a67]">{{ $trx->invoice_number }}</h3>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ optional($trx->created_at)->format('d M Y, H:i') }}</p>
                        </div>
                        <button type="button" onclick="closeAdminDetailModal('admin-trx-detail-{{ $trx->id }}')" class="admin-detail-close flex h-9 w-9 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#ffe5e5] text-lg font-black text-[#ff6b6b]">×</button>
                    </div>

                    <div class="admin-detail-body">
                    <div class="grid gap-3.5 md:grid-cols-2">
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Pelanggan</p>
                            <p class="mt-1 font-black text-[#2b3a67]">{{ $trx->user->name ?? 'Guest' }}</p>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3] break-all">{{ $trx->user->email ?? '-' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Total Bayar</p>
                            <p class="mt-1 font-black text-[#4bc6b9]">Rp {{ number_format($trx->display_total_payable, 0, ',', '.') }}</p>
                        </div>
                        <div class="admin-detail-panel md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Layanan</p>
                            <p class="mt-1 font-black text-[#2b3a67]">{{ $trx->product->name ?? 'Produk Dihapus' }}</p>
                        </div>
                        <div class="admin-detail-panel md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Metode Pembayaran</p>
                            <p class="mt-1 text-sm font-black uppercase text-[#2b3a67]">{{ $trx->display_payment_method }}</p>
                        </div>
                        <div class="admin-detail-panel md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Kategori</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $detailCategory ?: 'Kategori tidak tersedia' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Input Pembeli</p>
                            <p class="mt-2 whitespace-pre-line break-all text-xs font-black text-[#2b3a67]">{{ $detailInputText }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Jumlah Pesanan</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $detailQuantity ? number_format($detailQuantity, 0, ',', '.') : '-' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status Pesanan</p>
                            <p class="mt-1 font-black uppercase text-[#5a76c8]">{{ $trx->display_status }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">ID Provider</p>
                            <p class="mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $trx->provider_order_id ?? '-' }}</p>
                        </div>
                        @if($trx->target_notes)
                            <div class="admin-detail-panel admin-detail-panel-soft md:col-span-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-amber-500">Catatan</p>
                                <p class="mt-1 text-sm font-bold text-[#2b3a67]">{{ $trx->target_notes }}</p>
                            </div>
                        @endif
                    </div>

                    @if($detailCanManualUpdate)
                        <form action="{{ route('admin.transactions.update', $trx->id) }}" method="POST" class="admin-detail-panel mt-3.5 flex flex-col gap-2.5 sm:flex-row">
                            @csrf
                            @method('PATCH')
                            <select name="order_status" class="flex-1 rounded-full border-4 border-white bg-white px-4 py-2.5 text-xs font-black uppercase text-[#2b3a67] outline-none">
                                <option value="pending" {{ ($trx->order_status ?? '') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                                <option value="processing" {{ ($trx->order_status ?? '') == 'processing' ? 'selected' : '' }}>Proses</option>
                                <option value="success" {{ ($trx->order_status ?? '') == 'success' ? 'selected' : '' }}>Sukses</option>
                                <option value="failed" {{ ($trx->order_status ?? '') == 'failed' ? 'selected' : '' }}>Gagal</option>
                            </select>
                            <button type="submit" class="rounded-full border-4 border-white bg-[#5a76c8] px-5 py-2.5 text-xs font-black text-white shadow-md">Simpan Status Manual</button>
                        </form>
                    @endif
                    </div>
                </div>
            </div>
        @endforeach
        
        <!-- Paginasi -->
        @if(isset($transactions) && method_exists($transactions, 'links') && $transactions->hasPages())
            <div class="border-t-4 border-dashed border-[#f4f9ff] bg-[#f8faff] p-5 sm:p-8 w-full min-w-0">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
<script>
    function openAdminDetailModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('modal-active');
        document.body.classList.add('overflow-hidden', 'admin-detail-open');
    }

    function closeAdminDetailModal(id) {
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
