@extends('layouts.user')

@section('title', 'Riwayat Pesanan')

@section('content')
{{-- Wiboost patch: history-pagination-window-eof-fix-20260614-1030 --}}
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
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
        opacity: 0;
        transform: none !important;
        transition: opacity 120ms ease-out !important;
    }
    .history-modal-body {
        min-height: 0;
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
        <h2 class="text-3xl sm:text-4xl font-black text-[#2b3a67] tracking-tight drop-shadow-sm">Riwayat Pesanan 🧾</h2>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-5 rounded-[2.5rem] mb-8 font-black flex items-center gap-4 shadow-lg shadow-emerald-100/50">
            <span class="text-3xl drop-shadow-sm">✅</span> {{ session('success') }}
        </div>
    @endif

    <div class="space-y-6 relative z-10">
        @forelse($transactions as $trx)
            @php
                $statusLabel = '';
                $statusClass = '';
                $isUnpaid = false;
                $credentials = null;
                $isCancelled = $trx->cancelled_due_to_qris_expiry;
                $transactionTime = $trx->created_at?->copy()->locale('id')->translatedFormat('d F Y, H:i') ?? '-';

                if ($isCancelled) {
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

                        if (! empty($trx->credential_data)) {
                            $credentials = is_array($trx->credential_data)
                                ? $trx->credential_data
                                : json_decode($trx->credential_data, true);
                        }
                    } elseif ($trx->order_status == 'processing') {
                        $statusLabel = 'Proses';
                        $statusClass = 'bg-[#e0fbfc] text-[#5a76c8] border-[#bde0fe]';
                    } else {
                        $statusLabel = 'Pending';
                        $statusClass = 'bg-[#f4f9ff] text-[#8faaf3] border-[#e0ebff]';
                    }
                }
            @endphp

            <div onclick="openModal('modal-{{ $trx->id }}')" class="history-order-card bg-white/95 backdrop-blur-sm rounded-[2.5rem] p-6 md:p-8 shadow-xl shadow-[#bde0fe]/30 border-4 border-white flex flex-col md:flex-row justify-between items-start md:items-center hover:border-[#bde0fe] md:hover:-translate-y-2 transition-all duration-300 group cursor-pointer relative overflow-hidden">
                <div class="mb-4 md:mb-0 w-full md:w-2/3 pr-0 md:pr-4 relative z-10">
                    <div class="mb-4 flex w-full min-w-0 flex-nowrap items-center justify-between gap-2 md:justify-start">
                        <span class="max-w-[50%] truncate whitespace-nowrap rounded-full border-2 border-white bg-[#f4f9ff] px-3 py-1.5 font-black tracking-widest text-[#5a76c8] shadow-sm md:max-w-none" style="font-size: 8.5px; line-height: 1.1;">{{ $trx->invoice_number }}</span>
                        <span class="shrink-0 whitespace-nowrap font-black uppercase tracking-widest text-[#8faaf3]" style="font-size: 8.5px; line-height: 1.1;">{{ $transactionTime }}</span>
                    </div>

                    <h4 class="font-black text-xl md:text-2xl text-[#2b3a67] line-clamp-2 group-hover:text-[#5a76c8] transition-colors leading-tight drop-shadow-sm">{{ $trx->product->name ?? 'Produk Dihapus' }}</h4>

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
                        <h3 class="text-xl font-black text-[#2b3a67] drop-shadow-sm">Detail Pesanan 🧾</h3>
                        <button onclick="closeModal('modal-{{ $trx->id }}')" class="w-12 h-12 bg-[#ffe5e5] text-[#ff6b6b] hover:bg-[#ff6b6b] hover:text-white rounded-[1.2rem] flex items-center justify-center transition-colors font-black border-4 border-white shadow-sm text-xl shrink-0 active:scale-95">✕</button>
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

                        @if($credentials)
                            <div class="bg-[#fffcf0] p-6 rounded-[2rem] border-4 border-white shadow-md shadow-amber-100/50 relative overflow-hidden">
                                <div class="relative z-10">
                                    <div class="flex items-center gap-3 mb-5">
                                        <span class="text-2xl drop-shadow-sm">🔑</span>
                                        <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Akses Kredensial Kamu</p>
                                    </div>

                                    <div class="space-y-4">
                                        @foreach(['email' => 'Email', 'password' => 'Password', 'profile' => 'Profil', 'pin' => 'PIN', 'link' => 'Link Akses'] as $key => $label)
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

                                    @if(!empty($credentials['tutorial_link']) || (isset($credentials['needs_otp']) && $credentials['needs_otp']))
                                        <div class="mt-6 space-y-3">
                                            @if(!empty($credentials['tutorial_link']))
                                                <a href="{{ $credentials['tutorial_link'] }}" target="_blank" class="w-full bg-[#f4f9ff] border-4 border-white hover:border-[#bde0fe] hover:bg-[#e0fbfc] text-[#5a76c8] font-black text-xs py-4 rounded-[1.5rem] flex items-center justify-center gap-2 transition-colors shadow-sm active:scale-95">
                                                    <span>Panduan</span> Cara Pakai 📖
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

                                                    $appendOtpLine($otpLines, 'Invoice', $otpDetail['invoice'] ?? null);
                                                    $appendOtpLine($otpLines, 'Kategori', $otpDetail['category'] ?? null);
                                                    $appendOtpLine($otpLines, 'Produk', $otpDetail['product'] ?? null);
                                                    $appendOtpLine($otpLines, 'Total Bayar', $otpDetail['total'] ?? null);
                                                    $appendOtpLine($otpLines, 'Metode', $otpDetail['payment'] ?? null);
                                                    $appendOtpLine($otpLines, 'Status Pesanan', $otpDetail['status'] ?? null);
                                                    $appendOtpLine($otpLines, 'ID Provider', $otpDetail['provider_id'] ?? null);
                                                    $appendOtpLine($otpLines, 'Waktu Transaksi', $otpDetail['time'] ?? null);
                                                    $appendOtpLine($otpLines, 'Nama Pembeli', $otpUser->name ?? null);
                                                    $appendOtpLine($otpLines, 'Email Pembeli', $otpUser->email ?? null);
                                                    $appendOtpLine($otpLines, 'WhatsApp Pembeli', $otpUser->whatsapp ?? null);
                                                    $appendOtpLine($otpLines, 'Email Akun', $credentials['email'] ?? null);
                                                    $appendOtpLine($otpLines, 'Password Akun', $credentials['password'] ?? null);

                                                    $otpInputLines = collect($otpDetail['input_lines'] ?? [])
                                                        ->map(fn ($line) => trim((string) $line))
                                                        ->filter(function ($line) use ($hasOtpValue) {
                                                            $lineValue = \Illuminate\Support\Str::contains($line, ':')
                                                                ? \Illuminate\Support\Str::after($line, ':')
                                                                : $line;

                                                            return $hasOtpValue($lineValue);
                                                        })
                                                        ->values();

                                                    if ($otpInputLines->isNotEmpty()) {
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
                            <p class="font-black text-[#2b3a67] text-base leading-tight">{{ $trx->product->name ?? 'Produk Dihapus' }}</p>
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
                            @php($paymentUrl = filter_var($trx->snap_token, FILTER_VALIDATE_URL) ? $trx->snap_token : null)
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
                <div class="text-6xl mb-5 opacity-50 animate-float block">📦</div>
                <h3 class="text-2xl font-black text-[#5a76c8] mb-2 drop-shadow-sm">Belum Ada Pesanan</h3>
                <p class="text-[#8faaf3] font-bold max-w-sm mx-auto px-4 mb-8">Yuk, mulai buat pesanan pertamamu dan nikmati layanan kami!</p>
                <a href="{{ route('user.dashboard') }}" class="inline-flex items-center justify-center gap-2 bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black px-8 py-4 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white">
                    Lihat Katalog Layanan 🚀
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
</script>
@endsection
