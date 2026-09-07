@extends('layouts.user')

@section('title', 'Riwayat Pesanan')

@section('content')
{{-- Wiboost patch: history-pagination-window-eof-fix-20260614-1030 --}}
<style>
    .wiboost-font { font-family: 'Nunito', sans-serif; }

    .modal-active { opacity: 1 !important; pointer-events: auto !important; }
    .modal-content-active { opacity: 1 !important; transform: none !important; }

    @keyframes pulse-soft {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .animate-pulse-soft { animation: pulse-soft 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    .hide-scroll::-webkit-scrollbar { display: none; }
    html.modal-scroll-locked,
    body.modal-scroll-locked {
        overflow: hidden !important;
        overscroll-behavior: none;
        touch-action: none;
    }
    .history-modal-shell {
        -webkit-backdrop-filter: blur(2px);
        backdrop-filter: blur(2px);
        background: rgba(15, 23, 42, 0.82) !important;
        height: 100vh;
        height: 100dvh;
        max-height: 100vh;
        max-height: 100dvh;
        overflow: hidden;
        padding: max(0.75rem, env(safe-area-inset-top)) max(0.75rem, env(safe-area-inset-right)) max(0.75rem, env(safe-area-inset-bottom)) max(0.75rem, env(safe-area-inset-left));
    }
    .history-modal-card {
        max-height: calc(100vh - 1.5rem);
        max-height: calc(100dvh - 1.5rem);
        min-height: 0;
        min-width: 0;
        opacity: 0;
        transform: none !important;
        transition: opacity 120ms ease-out !important;
    }
    .history-modal-card *,
    .history-order-card * {
        min-width: 0;
    }
    .history-modal-card p,
    .history-modal-card span,
    .history-modal-card a {
        overflow-wrap: anywhere;
    }
    .history-modal-body {
        min-height: 0;
        overflow-x: hidden;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }

    @media (min-width: 768px) {
        .history-modal-shell {
            padding: 1.5rem;
        }
        .history-modal-card {
            max-height: min(760px, calc(100vh - 3rem));
            max-height: min(760px, calc(100dvh - 3rem));
        }
    }

    @media (max-width: 767px) {
        .history-order-card {
            transition: border-color 120ms ease-out, box-shadow 120ms ease-out !important;
        }

        .history-order-card:hover {
            transform: none !important;
        }

        .history-modal-card {
            border-radius: 1.45rem;
            padding: 0.85rem;
        }
    }

    @media (max-width: 420px) {
        .history-modal-card .grid.grid-cols-2 {
            grid-template-columns: minmax(0, 1fr) !important;
        }

        .history-modal-card [class*="tracking-widest"] {
            letter-spacing: 0.08em !important;
        }
    }

    .history-pagination-link,
    .history-pagination-disabled,
    .history-pagination-active {
        align-items: center;
        border: 2px solid #ffffff;
        border-radius: 9999px;
        display: inline-flex;
        font-size: 0.78rem;
        font-weight: 900;
        height: 2.45rem;
        justify-content: center;
        min-width: 2.45rem;
        padding: 0 0.9rem;
        transition: all 120ms ease-out;
    }

    .history-pagination-link {
        background: #f4f9ff;
        color: #5a76c8;
        box-shadow: 0 10px 24px rgba(189, 224, 254, 0.25);
    }

    .history-pagination-link:hover {
        background: #e0fbfc;
        border-color: #bde0fe;
        color: #2b3a67;
        transform: translateY(-1px);
    }

    .history-pagination-active {
        background: #5a76c8;
        color: #ffffff;
        box-shadow: 0 12px 24px rgba(90, 118, 200, 0.28);
    }

    .history-pagination-disabled {
        background: #f8faff;
        color: #b9c8ef;
        cursor: not-allowed;
    }

    .history-pagination-info {
        color: #5a76c8;
        font-weight: 900;
    }

    .history-pagination-layout {
        align-items: center;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
    }

    .history-pagination-info--desktop,
    .history-pagination-desktop {
        display: flex;
    }

    .history-pagination-info--mobile,
    .history-pagination-mobile {
        display: none;
    }

    .history-pagination-mobile-current {
        min-width: 4.75rem;
    }

    @media (max-width: 767px) {
        .history-pagination-layout {
            flex-direction: column;
            justify-content: center;
        }

        .history-pagination-info--desktop,
        .history-pagination-desktop {
            display: none !important;
        }

        .history-pagination-info--mobile {
            display: block !important;
        }

        .history-pagination-mobile {
            display: flex !important;
        }
    }

    @media (min-width: 768px) {
        .history-order-summary {
            align-items: flex-end !important;
            display: flex !important;
            flex-direction: column !important;
        }

        .history-order-status {
            order: 1 !important;
        }

        .history-order-total {
            order: 2 !important;
        }
    }
</style>

<div class="wiboost-font pb-12 max-w-6xl mx-auto mt-4 relative z-10">
    
    <div class="mb-10 text-center md:text-left pl-2">
        <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">Catatan Transaksi</div>
        <h2 class="flex items-center justify-center gap-2 text-3xl sm:text-4xl font-black text-[#2b3a67] tracking-tight drop-shadow-sm md:justify-start">
            <span class="text-[#5a76c8]">@include('partials.wiboost-icon', ['name' => 'history', 'class' => 'h-8 w-8'])</span>
            <span>Riwayat Pesanan</span>
        </h2>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-5 rounded-[2.5rem] mb-8 font-black flex items-center gap-4 shadow-lg shadow-emerald-100/50">
            <span class="drop-shadow-sm">@include('partials.wiboost-icon', ['name' => 'success', 'class' => 'h-8 w-8'])</span> {{ session('success') }}
        </div>
    @endif

    <div class="space-y-6 relative z-10">
        @forelse($transactions as $trx)
            @php
                $statusLabel = '';
                $statusClass = '';
                $isUnpaid = false;
                $credentials = [];
                $isCancelled = $trx->cancelled_due_to_qris_expiry;
                $transactionTime = $trx->created_at?->copy()->locale('id')->translatedFormat('d F Y, H:i') ?? '-';

                if ($trx->hasManualRefund()) {
                    $statusLabel = 'Refund';
                    $statusClass = 'bg-[#e0fbfc] text-[#5a76c8] border-[#bde0fe]';
                } elseif ($isCancelled) {
                    $statusLabel = 'Batal';
                    $statusClass = 'bg-[#ffe5e5] text-[#ff6b6b] border-red-200';
                } elseif (in_array($trx->payment_status, ['unpaid', 'pending'], true)) {
                    $statusLabel = 'Menunggu Bayar';
                    $statusClass = 'bg-[#fff5eb] text-amber-500 border-amber-200 animate-pulse-soft';
                    $isUnpaid = true;
                } elseif ($trx->payment_status == 'failed' || $trx->order_status == 'failed') {
                    $statusLabel = 'Gagal';
                    $statusClass = 'bg-[#ffe5e5] text-[#ff6b6b] border-red-200';
                } else {
                    if ($trx->order_status == 'success') {
                        $statusLabel = 'Sukses';
                        $statusClass = 'bg-[#e6fff7] text-emerald-500 border-emerald-200';

                    } elseif ($trx->order_status == 'processing') {
                        $statusLabel = 'Proses';
                        $statusClass = 'bg-[#e0fbfc] text-[#5a76c8] border-[#bde0fe]';
                    } else {
                        $statusLabel = 'Pending';
                        $statusClass = 'bg-[#f4f9ff] text-[#8faaf3] border-[#e0ebff]';
                    }
                }

                if (! empty($trx->credential_data)) {
                    $decodedCredentials = is_array($trx->credential_data)
                        ? $trx->credential_data
                        : json_decode($trx->credential_data, true);

                    $credentials = is_array($decodedCredentials) ? $decodedCredentials : [];
                }

                $credentialAccessLabels = [
                    'email' => 'Email',
                    'password' => 'Password',
                    'profile' => 'Profil',
                    'pin' => 'PIN',
                    'link' => 'Link Akses',
                ];
                $hasCredentialAccess = collect($credentialAccessLabels)->keys()->contains(fn ($key) => ! empty($credentials[$key]));
                $hasCredentialInfo = ! empty($credentials['notes']) || ! empty($credentials['requires_login_proof']);
                $historyProductName = $trx->display_product_name;
            @endphp

            <div onclick="openModal('modal-{{ $trx->id }}')" class="history-order-card bg-white/95 backdrop-blur-sm rounded-[2.5rem] p-6 md:p-8 shadow-xl shadow-[#bde0fe]/30 border-4 border-white flex flex-col md:flex-row justify-between items-start md:items-center hover:border-[#bde0fe] md:hover:-translate-y-2 transition-all duration-300 group cursor-pointer relative overflow-hidden">
                <div class="mb-4 md:mb-0 w-full md:w-2/3 pr-0 md:pr-4 relative z-10">
                    <div class="mb-4 flex w-full min-w-0 flex-nowrap items-center justify-between gap-2 md:justify-start">
                        <span class="max-w-[50%] truncate whitespace-nowrap rounded-full border-2 border-white bg-[#f4f9ff] px-3 py-1.5 font-black tracking-widest text-[#5a76c8] shadow-sm md:max-w-none" style="font-size: 8.5px; line-height: 1.1;">{{ $trx->invoice_number }}</span>
                        <span class="shrink-0 whitespace-nowrap font-black uppercase tracking-widest text-[#8faaf3]" style="font-size: 8.5px; line-height: 1.1;">{{ $transactionTime }}</span>
                    </div>

                    <h4 class="font-black text-xl md:text-2xl text-[#2b3a67] line-clamp-2 group-hover:text-[#5a76c8] transition-colors leading-tight drop-shadow-sm">{{ $historyProductName }}</h4>

                    @if($trx->has_order_input && filled($trx->order_input_summary))
                        <div class="mt-3 w-full overflow-hidden rounded-xl border border-white bg-[#f8faff] px-4 py-2 shadow-inner md:inline-block md:w-auto md:max-w-full">
                            <p class="min-w-0 truncate text-xs font-bold text-[#8faaf3]">
                                {{ $trx->order_input_summary }}
                            </p>
                        </div>
                    @endif
                </div>

                <div class="history-order-summary flex flex-row md:flex-col items-center md:items-end justify-between w-full md:w-auto bg-[#f8faff] md:bg-transparent p-5 md:p-0 rounded-[1.5rem] border-2 border-white md:border-0 shadow-inner md:shadow-none gap-3 relative z-10">
                    <p class="history-order-total order-1 md:order-2 shrink-0 whitespace-nowrap font-black text-[#5a76c8] text-xl sm:text-2xl drop-shadow-sm">Rp {{ number_format($trx->display_total_payable, 0, ',', '.') }}</p>
                    <span class="history-order-status {{ $statusClass }} order-2 md:order-1 px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest border-2 shadow-sm text-center min-w-[118px] sm:min-w-[130px]">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>

            <div id="modal-{{ $trx->id }}" class="history-modal-shell fixed inset-0 flex items-center justify-center bg-[#0f172a]/80 opacity-0 pointer-events-none transition-all duration-200 backdrop-blur-[2px]" style="z-index: 99999;">
                <div class="absolute inset-0" onclick="closeModal('modal-{{ $trx->id }}')"></div>

                <div class="history-modal-card relative bg-white w-full max-w-lg md:max-w-xl rounded-[2rem] p-4 md:p-5 shadow-xl flex flex-col overflow-hidden border-4 border-white">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b-4 border-dashed border-[#f4f9ff]">
                        <h3 class="flex items-center gap-2 text-xl font-black text-[#2b3a67] drop-shadow-sm">
                            <span>Detail Pesanan</span>
                            <span class="text-[#5a76c8]">@include('partials.wiboost-icon', ['name' => 'receipt', 'class' => 'h-5 w-5'])</span>
                        </h3>
                        <button onclick="closeModal('modal-{{ $trx->id }}')" class="w-12 h-12 bg-[#ffe5e5] text-[#ff6b6b] hover:bg-[#ff6b6b] hover:text-white rounded-[1.2rem] flex items-center justify-center transition-colors font-black border-4 border-white shadow-sm text-xl shrink-0 active:scale-95">@include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])</button>
                    </div>

                    <div data-history-detail-scroll class="history-modal-body flex-1 overflow-y-auto hide-scroll space-y-3 pb-3">
                        @if($trx->order_status == 'failed' && !empty($trx->target_notes))
                            <div class="bg-[#ffe5e5] p-5 rounded-[1.5rem] border-4 border-white shadow-inner relative overflow-hidden mb-5">
                                <div class="relative z-10">
                                    <p class="text-[10px] font-black text-[#ff6b6b] uppercase tracking-widest mb-1">Keterangan Sistem</p>
                                    <p class="font-bold text-[#2b3a67] text-sm leading-tight">{{ $trx->target_notes }}</p>
                                </div>
                            </div>
                        @endif

                        @if($hasCredentialAccess || !empty($credentials['tutorial_link']) || (isset($credentials['needs_otp']) && $credentials['needs_otp']))
                            <div class="bg-[#fffcf0] p-6 rounded-[2rem] border-4 border-white shadow-md shadow-amber-100/50 relative overflow-hidden">
                                <div class="relative z-10">
                                    @if($hasCredentialAccess)
                                    <div class="flex items-center gap-3 mb-5">
                                        <span class="drop-shadow-sm">@include('partials.wiboost-icon', ['name' => 'profile', 'class' => 'h-6 w-6'])</span>
                                        <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Akses Akun Kamu</p>
                                    </div>

                                    <div class="space-y-4">
                                        @foreach($credentialAccessLabels as $key => $label)
                                            @if(!empty($credentials[$key]))
                                                <div>
                                                    <p class="text-[9px] font-black text-amber-600/70 uppercase tracking-widest mb-1 ml-1">{{ $label }}</p>
                                                    <div class="bg-white px-5 py-3.5 rounded-[1.2rem] border-4 border-amber-100 flex justify-between items-center group shadow-sm">
                                                        <p class="font-black text-[#2b3a67] text-sm break-all">{{ $credentials[$key] }}</p>
                                                        <button onclick="copyToClipboard('{{ addslashes($credentials[$key]) }}')" class="text-amber-400 hover:text-amber-600 transition-colors bg-amber-50 p-2 rounded-xl border border-amber-100 active:scale-95" title="Salin">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    @endif

                                    @if(false && !empty($credentials['notes']))
                                        <div class="{{ $hasCredentialAccess ? 'mt-6' : '' }} rounded-[1.7rem] border-4 border-white bg-[#fff8dc] p-5 shadow-sm">
                                            <p class="mb-3 text-[10px] font-black uppercase tracking-widest text-amber-500">Informasi Login</p>
                                            <p class="text-[9px] font-black text-amber-600/70 uppercase tracking-widest mb-1 ml-1">Catatan</p>
                                            <div class="bg-white px-5 py-3.5 rounded-[1.2rem] border-4 border-amber-100 flex justify-between items-center gap-3 group shadow-sm">
                                                <p class="font-black text-[#2b3a67] text-sm break-words">{{ $credentials['notes'] }}</p>
                                                <button onclick="copyToClipboard('{{ addslashes($credentials['notes']) }}')" class="shrink-0 text-amber-400 hover:text-amber-600 transition-colors bg-amber-50 p-2 rounded-xl border border-amber-100 active:scale-95" title="Salin">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    @if(false && !empty($credentials['requires_login_proof']))
                                        @php
                                            $loginProofUrl = $trx->login_proof_path
                                                ? url(\Illuminate\Support\Facades\Storage::url($trx->login_proof_path))
                                                : null;
                                        @endphp
                                        <div class="mt-6 rounded-[1.7rem] border-4 border-white bg-[#eef6ff] p-5 shadow-sm">
                                            <div class="mb-4">
                                                <p class="text-[10px] font-black uppercase tracking-widest text-[#5a76c8]">Bukti Login</p>
                                                <p class="mt-1 text-xs font-bold leading-relaxed text-[#8faaf3]">Upload screenshot bukti login akun yang kamu terima.</p>
                                            </div>
                                            <form action="{{ route('user.history.login-proof', $trx) }}" method="POST" enctype="multipart/form-data" data-login-proof-form>
                                                @csrf
                                                <div class="relative">
                                                    <label class="relative flex min-h-[10rem] cursor-pointer items-center justify-center overflow-hidden rounded-[1.5rem] border-4 border-dashed border-[#bde0fe] bg-white text-center shadow-inner transition hover:border-[#5a76c8]">
                                                        <img data-login-proof-image src="{{ $loginProofUrl ?: '' }}" alt="Bukti login" class="{{ $loginProofUrl ? '' : 'hidden' }} absolute inset-0 h-full w-full object-cover">
                                                        <span data-login-proof-empty class="{{ $loginProofUrl ? 'hidden' : '' }} flex flex-col items-center gap-3 px-5 text-[#5a76c8]">
                                                            <span class="flex h-14 w-14 items-center justify-center rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] shadow-sm">
                                                                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0l-4 4m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"></path></svg>
                                                            </span>
                                                            <span class="text-sm font-black">Upload Bukti Login</span>
                                                        </span>
                                                        <input data-login-proof-input type="file" name="login_proof" accept="image/*" class="sr-only">
                                                    </label>
                                                    <button type="button" data-login-proof-delete data-delete-url="{{ route('user.history.login-proof.destroy', $trx) }}" class="{{ $loginProofUrl ? '' : 'hidden' }} absolute right-3 top-3 flex h-11 w-11 items-center justify-center rounded-full border-4 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-lg transition hover:bg-[#ff6b6b] hover:text-white active:scale-95" title="Hapus bukti login">
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </div>
                                                <p data-login-proof-uploaded class="{{ $trx->login_proof_uploaded_at ? '' : 'hidden' }} mt-3 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">
                                                    @if($trx->login_proof_uploaded_at)
                                                        Diunggah {{ $trx->login_proof_uploaded_at->copy()->locale('id')->translatedFormat('d F Y, H:i') }}
                                                    @endif
                                                </p>
                                            </form>
                                        </div>
                                    @endif

                                    @if(!empty($credentials['tutorial_link']) || (isset($credentials['needs_otp']) && $credentials['needs_otp']))
                                        <div class="mt-6 space-y-3">
                                            @if(!empty($credentials['tutorial_link']))
                                                <a href="{{ $credentials['tutorial_link'] }}" target="_blank" class="w-full bg-[#f4f9ff] border-4 border-white hover:border-[#bde0fe] hover:bg-[#e0fbfc] text-[#5a76c8] font-black text-xs py-4 rounded-[1.5rem] flex items-center justify-center gap-2 transition-colors shadow-sm active:scale-95">
                                                    <span>Panduan</span>
                                                    <span>Cara Pakai</span>
                                                    @include('partials.wiboost-icon', ['name' => 'book', 'class' => 'h-4 w-4'])
                                                </a>
                                            @endif

                                            @if(isset($credentials['needs_otp']) && $credentials['needs_otp'] == true)
                                                @php
                                                    $otpDetail = \App\Support\WiboostOrderDetailData::forTransaction($trx);
                                                    $otpUser = auth()->user();
                                                    $hasOtpValue = null;
                                                    $hasOtpValue = static function ($value) use (&$hasOtpValue): bool {
                                                        if ($value === null) {
                                                            return false;
                                                        }

                                                        if (is_array($value)) {
                                                            foreach ($value as $item) {
                                                                if ($hasOtpValue($item)) {
                                                                    return true;
                                                                }
                                                            }

                                                            return false;
                                                        }

                                                        $value = trim((string) $value);
                                                        $emptyValues = [
                                                            '',
                                                            '-',
                                                            "\xE2\x80\x94",
                                                            'n/a',
                                                            'na',
                                                            'null',
                                                            'undefined',
                                                            '[]',
                                                            '{}',
                                                            'tidak ada',
                                                            'belum ada',
                                                            'belum tersedia',
                                                        ];

                                                        return ! in_array(\Illuminate\Support\Str::lower($value), $emptyValues, true);
                                                    };
                                                    $appendOtpLine = static function (array &$lines, string $label, $value) use ($hasOtpValue): void {
                                                        if ($hasOtpValue($value)) {
                                                            $lines[] = $label . ': ' . trim((string) $value);
                                                        }
                                                    };
                                                    $otpLines = [
                                                        'Halo Admin Wiboost, saya ingin meminta bantuan OTP untuk pesanan saya.',
                                                        '',
                                                    ];

                                                    $appendOtpLine($otpLines, 'Nama Pembeli', $otpUser->name ?? null);
                                                    $appendOtpLine($otpLines, 'Email Pembeli', $otpUser->email ?? null);
                                                    $appendOtpLine($otpLines, 'WhatsApp Pembeli', $otpUser->whatsapp ?? null);

                                                    $otpOrderLines = [];
                                                    $appendOtpLine($otpOrderLines, 'Invoice', $otpDetail['invoice'] ?? null);
                                                    $appendOtpLine($otpOrderLines, 'Kategori', $otpDetail['category'] ?? null);
                                                    $appendOtpLine($otpOrderLines, 'Produk', $otpDetail['product'] ?? null);
                                                    $appendOtpLine($otpOrderLines, 'Total Bayar', $otpDetail['total'] ?? null);
                                                    $appendOtpLine($otpOrderLines, 'Metode', $otpDetail['payment'] ?? null);
                                                    $appendOtpLine($otpOrderLines, 'Status Pesanan', $otpDetail['status'] ?? null);
                                                    $appendOtpLine($otpOrderLines, 'ID Provider', $otpDetail['provider_id'] ?? null);
                                                    $appendOtpLine($otpOrderLines, 'Waktu Transaksi', $otpDetail['time'] ?? null);

                                                    if ($otpOrderLines !== []) {
                                                        $otpLines[] = '';
                                                        $otpLines[] = 'Data Pesanan:';
                                                        array_push($otpLines, ...$otpOrderLines);
                                                    }

                                                    $otpAccountLines = [];
                                                    $appendOtpLine($otpAccountLines, 'Email', $credentials['email'] ?? null);
                                                    $appendOtpLine($otpAccountLines, 'Password', $credentials['password'] ?? null);
                                                    $appendOtpLine($otpAccountLines, 'Profil', $credentials['profile'] ?? null);
                                                    $appendOtpLine($otpAccountLines, 'PIN', $credentials['pin'] ?? null);
                                                    $appendOtpLine($otpAccountLines, 'Link Akses', $credentials['link'] ?? null);
                                                    $appendOtpLine($otpAccountLines, 'Link Tutorial', $credentials['tutorial_link'] ?? null);

                                                    if ($trx->login_proof_path) {
                                                        $appendOtpLine($otpAccountLines, 'Bukti Login', url(\Illuminate\Support\Facades\Storage::url($trx->login_proof_path)));
                                                    }

                                                    if ($otpAccountLines !== []) {
                                                        $otpLines[] = '';
                                                        $otpLines[] = 'Detail Akun:';
                                                        array_push($otpLines, ...$otpAccountLines);
                                                    }

                                                    $otpInputLines = collect($otpDetail['input_lines'] ?? [])
                                                        ->map(fn ($line) => trim((string) $line))
                                                        ->filter(function ($line) use ($hasOtpValue) {
                                                            $lineValue = \Illuminate\Support\Str::contains($line, ':')
                                                                ? \Illuminate\Support\Str::after($line, ':')
                                                                : $line;

                                                            return $hasOtpValue($lineValue);
                                                        })
                                                        ->values();

                                                    $otpCategoryText = \Illuminate\Support\Str::lower((string) ($otpDetail['category'] ?? ''));

                                                    if ($otpInputLines->isNotEmpty() && ! \Illuminate\Support\Str::contains($otpCategoryText, 'aplikasi premium')) {
                                                        $otpLines[] = '';
                                                        $otpLines[] = 'Input Pembeli:';
                                                        foreach ($otpInputLines as $inputLine) {
                                                            $otpLines[] = $inputLine;
                                                        }
                                                    }

                                                    $otpLines[] = '';
                                                    $otpLines[] = 'Mohon dibantu kirim OTP login untuk pesanan ini.';

                                                    $otpUrl = \App\Support\WiboostAdminContact::otpWhatsappUrl(implode("\n", $otpLines));
                                                @endphp
                                                <div class="bg-white p-5 rounded-[1.5rem] border-4 border-amber-100 flex flex-col gap-2 shadow-sm text-center">
                                                    <p class="text-[10px] font-black text-amber-600 leading-relaxed uppercase tracking-widest">Silahkan cek tutorial untuk login atau minta bantuan OTP ke admin.</p>
                                                    @if($otpUrl)
                                                        <a href="{{ $otpUrl }}" target="_blank" rel="noopener" onclick="window.markWiboostAdminChatOpened?.()" class="mt-2 inline-flex w-full items-center justify-center rounded-full border-4 border-white bg-[#25D366] px-4 py-3 text-xs font-black text-white shadow-lg shadow-[#25D366]/20 transition active:scale-95">
                                                            Minta OTP via WhatsApp
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($hasCredentialInfo)
                            @php
                                $loginProofUrl = $trx->login_proof_path
                                    ? url(\Illuminate\Support\Facades\Storage::url($trx->login_proof_path))
                                    : null;
                            @endphp
                            <div class="bg-[#fffcf0] p-6 rounded-[2rem] border-4 border-white shadow-md shadow-amber-100/50 relative overflow-hidden">
                                <div class="relative z-10">
                                <div class="mb-5 flex items-center gap-3">
                                    <span class="drop-shadow-sm">@include('partials.wiboost-icon', ['name' => 'note', 'class' => 'h-6 w-6'])</span>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-amber-500">Informasi Login</p>
                                </div>

                                @if(!empty($credentials['notes']))
                                    <div class="mb-5">
                                        <p class="mb-1 ml-1 text-[9px] font-black uppercase tracking-widest text-amber-600/70">Catatan</p>
                                        <div class="flex items-center justify-between gap-3 rounded-[1.2rem] border-4 border-amber-100 bg-white px-5 py-3.5 shadow-sm">
                                            <p class="text-sm font-black text-[#2b3a67] break-words">{{ $credentials['notes'] }}</p>
                                            <button onclick="copyToClipboard('{{ addslashes($credentials['notes']) }}')" class="shrink-0 rounded-xl border border-amber-100 bg-amber-50 p-2 text-amber-400 transition-colors hover:text-amber-600 active:scale-95" title="Salin">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($credentials['requires_login_proof']))
                                    <div class="rounded-[2rem] border-4 border-white bg-white p-5 shadow-md shadow-[#bde0fe]/20">
                                        <p class="mb-5 text-[10px] font-black uppercase tracking-[0.25em] text-[#5a76c8]">Bukti Login</p>
                                        <form action="{{ route('user.history.login-proof', $trx) }}" method="POST" enctype="multipart/form-data" data-login-proof-form>
                                            @csrf
                                            <div class="relative">
                                                <label class="relative flex min-h-[15rem] cursor-pointer items-center justify-center overflow-hidden rounded-[2rem] border-4 border-dashed border-[#bde0fe] bg-[#f8fbff] text-center shadow-inner transition hover:border-[#8faaf3] md:min-h-[18rem]" style="position:relative;min-height:240px;width:100%;overflow:hidden;border:4px dashed #bde0fe;border-radius:32px;background:#f8fbff;text-align:center;">
                                                    <img data-login-proof-image src="{{ $loginProofUrl ?: '' }}" alt="Bukti login" class="{{ $loginProofUrl ? '' : 'hidden' }} absolute inset-0 h-full w-full object-cover" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
                                                    <span data-login-proof-empty class="{{ $loginProofUrl ? 'hidden' : '' }} flex h-full w-full flex-col items-center justify-center gap-5 px-6 py-12 text-[#5a76c8]" style="min-height:232px;width:100%;align-items:center;justify-content:center;">
                                                        <span class="flex h-20 w-20 items-center justify-center rounded-[1.8rem] border-4 border-white bg-white shadow-xl shadow-[#bde0fe]/30">
                                                            <svg class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="2.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0l-4 4m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"></path></svg>
                                                        </span>
                                                        <span class="text-lg font-black leading-tight text-[#2b3a67] sm:text-xl">Upload screenshot atau foto bukti</span>
                                                        <span class="max-w-sm text-sm font-bold leading-relaxed text-[#8faaf3] sm:text-base">Kalau tidak bisa screenshot, foto layar dari device lain juga boleh.</span>
                                                    </span>
                                                    <input data-login-proof-input type="file" name="login_proof" accept="image/*" class="sr-only">
                                                </label>
                                                <button type="button" data-login-proof-delete data-delete-url="{{ route('user.history.login-proof.destroy', $trx) }}" class="{{ $loginProofUrl ? '' : 'hidden' }} absolute right-4 top-4 flex h-14 w-14 items-center justify-center rounded-[1.4rem] border-4 border-white bg-[#ffe5e5] text-[#ff4d4d] shadow-xl shadow-red-100 transition hover:bg-[#ff4d4d] hover:text-white active:scale-95" title="Hapus bukti login" style="position:absolute;top:16px;right:16px;width:56px;height:56px;border-radius:22px;border:4px solid #fff;background:#ffe5e5;color:#ff4d4d;box-shadow:0 12px 24px rgba(255,77,77,.18);z-index:20;">
                                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M10 11v6m4-6v6M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m3 0v12a2 2 0 01-2 2H8a2 2 0 01-2-2V7h12z"></path></svg>
                                                </button>
                                            </div>
                                            <p class="hidden" aria-hidden="true"></p>
                                        </form>
                                    </div>
                                @endif
                                </div>
                            </div>
                        @endif

                        <div class="bg-[#f4f9ff] p-6 rounded-[1.5rem] border-4 border-white shadow-inner flex justify-between items-center">
                            <div>
                                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-1">Invoice</p>
                                <p class="font-black text-[#5a76c8] text-xl drop-shadow-sm">{{ $trx->invoice_number }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-[#f8faff] p-5 rounded-[1.5rem] border-4 border-white shadow-sm flex flex-col justify-center">
                                <p class="text-[9px] font-black text-[#8faaf3] uppercase tracking-widest mb-2 whitespace-nowrap">Metode Bayar</p>
                                @php
                                    $methodDisplay = $trx->display_payment_method;
                                    if ($methodDisplay === 'SALDO') {
                                        $colorClass = 'bg-[#e0fbfc] text-[#5a76c8] border-[#bde0fe]';
                                    } elseif ($methodDisplay === 'BELUM PILIH') {
                                        $colorClass = 'bg-[#fff5eb] text-amber-500 border-amber-200';
                                    } else {
                                        $colorClass = 'bg-white text-[#5a76c8] border-[#f0f5ff]';
                                    }
                                @endphp
                                <span class="text-[9px] font-black uppercase tracking-widest px-3 py-2 rounded-full border-2 shadow-sm inline-block text-center {{ $colorClass }}">
                                    {{ $methodDisplay }}
                                </span>
                            </div>
                            <div class="bg-[#f8faff] p-5 rounded-[1.5rem] border-4 border-white shadow-sm flex flex-col justify-center text-left">
                                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-2">Total Bayar</p>
                                <p class="font-black text-[#4bc6b9] text-xl leading-tight truncate drop-shadow-sm">Rp {{ number_format($trx->display_total_payable, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="bg-[#f8faff] p-6 rounded-[1.5rem] border-4 border-white shadow-sm">
                            <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-1">Produk</p>
                            <p class="font-black text-[#2b3a67] text-base leading-tight">{{ $historyProductName }}</p>
                        </div>

                        @if($trx->has_order_input)
                            <div class="bg-[#f8faff] p-6 rounded-[1.5rem] border-4 border-white shadow-sm">
                                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-3">Input Buyer</p>
                                <div class="space-y-3">
                                    @foreach($trx->order_input_fields as $inputField)
                                        <div class="bg-white p-4 rounded-[1.2rem] border-2 border-[#f0f5ff] shadow-inner">
                                            <p class="font-black text-[#2b3a67] text-sm break-all">{{ $inputField['value'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4 px-2 pt-2 items-start">
                            <div class="min-w-0">
                                <p class="text-[9px] font-black text-[#8faaf3] uppercase tracking-widest mb-2 whitespace-nowrap">Waktu Transaksi</p>
                                <p class="font-bold text-[#2b3a67] text-sm">{{ $transactionTime }}</p>
                            </div>
                            <div class="min-w-0 text-right">
                                <p class="text-[9px] font-black text-[#8faaf3] uppercase tracking-widest mb-2 whitespace-nowrap">Status Transaksi</p>
                                <span class="{{ $statusClass }} inline-flex min-w-[8.5rem] items-center justify-center px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest border-2 shadow-sm text-center leading-tight">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t-4 border-dashed border-[#f4f9ff] shrink-0">
                        @if($isUnpaid)
                            <?php $paymentUrl = filter_var($trx->snap_token, FILTER_VALIDATE_URL) ? $trx->snap_token : null; ?>
                            @if($paymentUrl)
                                <a href="{{ $paymentUrl }}" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white py-4 rounded-full font-black transition-transform active:scale-95 shadow-xl shadow-[#4bc6b9]/30 border-4 border-white flex justify-center items-center gap-2 text-lg mb-3">
                                    Lanjut Bayar
                                </a>
                            @else
                                <div class="mb-3 rounded-[1.5rem] border-4 border-white bg-[#fff5eb] px-5 py-4 text-center text-sm font-black text-amber-600 shadow-sm">
                                    Tagihan belum memiliki link otomatis. Tunggu admin atau gunakan metode lain.
                                </div>
                            @endif
                        @else
                            <button onclick="closeModal('modal-{{ $trx->id }}')" class="w-full bg-[#f4f9ff] hover:bg-[#5a76c8] hover:text-white text-[#5a76c8] py-4 rounded-full font-black transition-all duration-300 border-4 border-white shadow-md text-base active:scale-95">Tutup Detail</button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-20 bg-white/90 backdrop-blur-sm rounded-[2.5rem] border-4 border-dashed border-[#bde0fe] shadow-lg shadow-[#bde0fe]/20 relative z-10">
                <div class="mb-5 flex justify-center text-[#8faaf3] opacity-70 animate-float">@include('partials.wiboost-icon', ['name' => 'receipt', 'class' => 'h-16 w-16'])</div>
                <h3 class="text-2xl font-black text-[#5a76c8] mb-2 drop-shadow-sm">Belum Ada Pesanan</h3>
                <p class="text-[#8faaf3] font-bold max-w-sm mx-auto px-4 mb-8">Yuk, mulai buat pesanan pertamamu dan nikmati layanan kami!</p>
                <a href="{{ route('user.dashboard') }}" class="inline-flex items-center justify-center gap-2 bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black px-8 py-4 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white">
                    <span>Lihat Katalog Layanan</span>
                </a>
            </div>
        @endforelse
    </div>

    <?php if (method_exists($transactions, 'links') && $transactions->hasPages()): ?>
        <?php
            $currentPage = $transactions->currentPage();
            $lastPage = $transactions->lastPage();
            $desktopWindow = 10;
            $mobileWindow = 5;
            $desktopStart = (int) (floor(($currentPage - 1) / $desktopWindow) * $desktopWindow) + 1;
            $desktopEnd = min($lastPage, $desktopStart + $desktopWindow - 1);
            $mobileStart = (int) (floor(($currentPage - 1) / $mobileWindow) * $mobileWindow) + 1;
            $mobileEnd = min($lastPage, $mobileStart + $mobileWindow - 1);
        ?>
        <div class="mt-10 p-5 bg-white/90 backdrop-blur-md rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white relative z-10">
            <div class="history-pagination-layout">
                <p class="history-pagination-info history-pagination-info--desktop text-sm">
                    Menampilkan <?php echo e($transactions->firstItem()); ?>-<?php echo e($transactions->lastItem()); ?> dari <?php echo e($transactions->total()); ?> pesanan
                </p>

                <nav class="history-pagination-desktop max-w-full flex-wrap items-center justify-center gap-2" aria-label="Pagination desktop">
                    <?php if ($transactions->onFirstPage()): ?>
                        <span class="history-pagination-disabled" aria-disabled="true" aria-label="Previous page">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                        </span>
                    <?php else: ?>
                        <a href="<?php echo e($transactions->previousPageUrl()); ?>" class="history-pagination-link" aria-label="Previous page">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                        </a>
                    <?php endif; ?>

                    <?php for ($page = $desktopStart; $page <= $desktopEnd; $page++): ?>
                        <?php if ($page === $currentPage): ?>
                            <span class="history-pagination-active" aria-current="page"><?php echo e($page); ?></span>
                        <?php else: ?>
                            <a href="<?php echo e($transactions->url($page)); ?>" class="history-pagination-link"><?php echo e($page); ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($transactions->hasMorePages()): ?>
                        <a href="<?php echo e($transactions->nextPageUrl()); ?>" class="history-pagination-link" aria-label="Next page">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    <?php else: ?>
                        <span class="history-pagination-disabled" aria-disabled="true" aria-label="Next page">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                        </span>
                    <?php endif; ?>
                </nav>

                <nav class="history-pagination-mobile max-w-full items-center justify-center gap-2" aria-label="Pagination mobile">
                    <?php if ($transactions->onFirstPage()): ?>
                        <span class="history-pagination-disabled" aria-disabled="true" aria-label="Previous page">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                        </span>
                    <?php else: ?>
                        <a href="<?php echo e($transactions->previousPageUrl()); ?>" class="history-pagination-link" aria-label="Previous page">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                        </a>
                    <?php endif; ?>

                    <?php for ($page = $mobileStart; $page <= $mobileEnd; $page++): ?>
                        <?php if ($page === $currentPage): ?>
                            <span class="history-pagination-active" aria-current="page"><?php echo e($page); ?></span>
                        <?php else: ?>
                            <a href="<?php echo e($transactions->url($page)); ?>" class="history-pagination-link"><?php echo e($page); ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($transactions->hasMorePages()): ?>
                        <a href="<?php echo e($transactions->nextPageUrl()); ?>" class="history-pagination-link" aria-label="Next page">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    <?php else: ?>
                        <span class="history-pagination-disabled" aria-disabled="true" aria-label="Next page">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                        </span>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    let lockedScrollY = 0;

    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    document.addEventListener('DOMContentLoaded', function () {
        const modals = document.querySelectorAll('[id^="modal-"]');
        modals.forEach(modal => document.body.appendChild(modal));

        @if(session('new_trx_id'))
            setTimeout(() => { openModal('modal-{{ session('new_trx_id') }}'); }, 300);
        @endif

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('auto_open')) {
            setTimeout(() => { openModal('modal-' + urlParams.get('auto_open')); }, 300);
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });

    function lockPageScroll() {
        if (document.body.classList.contains('modal-scroll-locked')) {
            return;
        }

        lockedScrollY = window.scrollY || document.documentElement.scrollTop || 0;
        document.documentElement.classList.add('modal-scroll-locked');
        document.body.classList.add('modal-scroll-locked');
    }

    function unlockPageScroll() {
        if (!document.body.classList.contains('modal-scroll-locked')) {
            return;
        }

        const restoreY = lockedScrollY;
        document.activeElement?.blur?.();
        document.documentElement.classList.remove('modal-scroll-locked');
        document.body.classList.remove('modal-scroll-locked');

        const restore = () => window.scrollTo({ top: restoreY, left: 0, behavior: 'auto' });
        restore();
        requestAnimationFrame(restore);
        window.setTimeout(restore, 80);
        window.setTimeout(restore, 180);
    }

    function restoreModalScrollPosition(modal) {
        const restoreY = Number(modal?.dataset?.restoreScrollY ?? lockedScrollY ?? 0);
        const restore = () => window.scrollTo({ top: restoreY, left: 0, behavior: 'auto' });

        window.scrollTo({ top: restoreY, left: 0, behavior: 'auto' });
        requestAnimationFrame(restore);
        window.setTimeout(restore, 80);
        window.setTimeout(restore, 180);
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const modalContent = modal.querySelector('.history-modal-card');
            const scrollArea = modal.querySelector('[data-history-detail-scroll]');

            if (scrollArea) {
                scrollArea.scrollTop = 0;
            }

            if (modalContent) {
                modalContent.scrollTop = 0;
            }

            modal.dataset.restoreScrollY = String(window.scrollY || document.documentElement.scrollTop || 0);
            modal.classList.add('modal-active');
            modalContent?.classList.add('modal-content-active');
            lockPageScroll();

            requestAnimationFrame(() => {
                if (scrollArea) {
                    scrollArea.scrollTop = 0;
                }
            });
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('modal-active');
            modal.querySelector('.history-modal-card')?.classList.remove('modal-content-active');
            unlockPageScroll();
            restoreModalScrollPosition(modal);
        }
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(
            () => alert('Data berhasil disalin ke clipboard.'),
            () => alert('Gagal menyalin teks.')
        );
    }

    document.addEventListener('change', async function (event) {
        const input = event.target.closest('[data-login-proof-input]');
        if (!input || !input.files || input.files.length === 0) {
            return;
        }

        const form = input.closest('[data-login-proof-form]');
        if (!form) {
            return;
        }

        const formData = new FormData(form);
        const token = form.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.content || '';

        input.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                },
                body: formData,
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Bukti login gagal diupload.');
            }

            const image = form.querySelector('[data-login-proof-image]');
            const emptyState = form.querySelector('[data-login-proof-empty]');
            const deleteButton = form.querySelector('[data-login-proof-delete]');
            const uploadedText = form.querySelector('[data-login-proof-uploaded]');

            if (image) {
                image.src = data.url;
                image.classList.remove('hidden');
            }
            emptyState?.classList.add('hidden');
            deleteButton?.classList.remove('hidden');
            if (uploadedText) {
                uploadedText.textContent = data.uploaded_label || '';
                uploadedText.classList.remove('hidden');
            }
            input.value = '';
        } catch (error) {
            alert(error.message || 'Bukti login gagal diupload.');
        } finally {
            input.disabled = false;
        }
    });

    document.addEventListener('click', async function (event) {
        const button = event.target.closest('[data-login-proof-delete]');
        if (!button) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const form = button.closest('[data-login-proof-form]');
        const token = form?.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.content || '';

        try {
            const response = await fetch(button.dataset.deleteUrl, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                },
            });

            const data = await response.json();
            if (!response.ok || !data.deleted) {
                throw new Error(data.message || 'Bukti login gagal dihapus.');
            }

            const image = form?.querySelector('[data-login-proof-image]');
            const emptyState = form?.querySelector('[data-login-proof-empty]');
            const uploadedText = form?.querySelector('[data-login-proof-uploaded]');
            const input = form?.querySelector('[data-login-proof-input]');

            if (image) {
                image.removeAttribute('src');
                image.classList.add('hidden');
            }
            emptyState?.classList.remove('hidden');
            button.classList.add('hidden');
            if (uploadedText) {
                uploadedText.textContent = '';
                uploadedText.classList.add('hidden');
            }
            if (input) {
                input.value = '';
            }
        } catch (error) {
            alert(error.message || 'Bukti login gagal dihapus.');
        }
    });
</script>
@endsection
