@extends('layouts.user')

@section('title', 'Pembayaran QRIS')

@section('content')
<!-- qris-membership-force-fix-20260717 -->
@php
    $baseAmount = (int) ($baseAmount ?? 0);
    $adminFee = (int) ($adminFee ?? 0);
    $totalPayable = (int) ($totalPayable ?: ($baseAmount + $adminFee));
    $statusRaw = strtolower((string) ($status ?: 'pending'));
    $isPaid = in_array($statusRaw, ['paid', 'settlement', 'success', 'berhasil'], true);
    $isFailed = in_array($statusRaw, ['failed', 'failure', 'error', 'expired', 'cancel', 'cancelled', 'canceled', 'gagal'], true);
    $statusKey = $isPaid ? 'success' : ($isFailed ? 'failed' : 'pending');
    $statusLabel = $isPaid ? 'Sukses' : ($isFailed ? 'Gagal' : 'Menunggu');
    $expiresAt = $expiresAt ?? null;
    $expiresAtLocal = $expiresAt ? $expiresAt->copy()->timezone(config('app.timezone')) : null;
    $expiresAtIso = $expiresAtLocal?->toIso8601String();
    $expiresAtLabel = $expiresAtLocal?->locale('id')->translatedFormat('d F Y, H:i') . ($expiresAtLocal ? ' WIB' : '');
@endphp

<style>
    .qris-status-badge {
        align-items: center;
        border: 2px solid rgba(255, 255, 255, 0.9);
        border-radius: 9999px;
        display: inline-flex;
        font-size: 0.7rem;
        font-weight: 900;
        justify-content: center;
        letter-spacing: 0.12em;
        min-width: 7.5rem;
        padding: 0.65rem 1.1rem;
        text-transform: uppercase;
    }

    .qris-status-badge[data-status="pending"] {
        background: #fbbf24;
        color: #422006;
    }

    .qris-status-badge[data-status="success"] {
        background: #10b981;
        color: #ffffff;
    }

    .qris-status-badge[data-status="failed"] {
        background: #ef4444;
        color: #ffffff;
    }

    .qris-page-shell {
        margin-left: auto;
        margin-right: auto;
        max-width: 64rem;
    }

    .qris-expiry-countdown,
    .qris-total-label,
    .qris-total-amount {
        white-space: nowrap;
    }

    @media (max-width: 640px) {
        .qris-page-shell {
            max-width: 26rem;
        }

        .qris-hero-card,
        .qris-payment-card {
            border-radius: 1.8rem;
        }

        .qris-hero-title {
            font-size: clamp(1.55rem, 7vw, 2rem);
            line-height: 1.08;
        }

        .qris-code-frame {
            max-width: 16.25rem;
        }

        .qris-info-panel,
        .qris-info-box {
            border-radius: 1.45rem;
        }

        .qris-status-row {
            align-items: stretch;
            flex-direction: column;
            text-align: center;
        }

        .qris-status-badge {
            width: 100%;
        }

        .qris-total-row {
            align-items: center;
            flex-direction: row;
            gap: 0.75rem;
        }

        .qris-expiry-countdown {
            font-size: clamp(0.56rem, 2.65vw, 0.72rem);
            letter-spacing: 0.05em;
        }

        .qris-total-label {
            flex: 0 0 auto;
            letter-spacing: 0.08em;
        }

        .qris-total-amount {
            flex: 1 1 auto;
            font-size: clamp(1.4rem, 7.2vw, 1.9rem);
            text-align: right;
        }
    }
</style>

<div class="qris-page-shell wiboost-font space-y-6 sm:space-y-8">
    <section class="qris-hero-card rounded-[2rem] border-4 border-white bg-white/95 p-5 text-center shadow-xl shadow-[#bde0fe]/30 sm:p-8">
        <div class="flex flex-col items-center gap-4">
            <span class="inline-flex rounded-full border-2 border-white bg-[#e0fbfc] px-5 py-2 text-[11px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                QRIS
            </span>
            <div>
                <h1 class="qris-hero-title text-3xl font-black text-[#2b3a67] sm:text-4xl">Pembayaran QRIS</h1>
                <p class="mt-3 max-w-2xl text-sm font-bold leading-relaxed text-[#5a76c8] sm:text-base">
                    Scan kode QRIS untuk menyelesaikan pembayaran.
                </p>
            </div>
        </div>
    </section>

    <section class="qris-payment-card rounded-[2.25rem] border-4 border-white bg-white p-4 shadow-2xl shadow-[#bde0fe]/35 sm:p-7">
        <div class="grid gap-4 lg:grid-cols-[320px_minmax(0,1fr)] lg:items-stretch">
            <div class="flex flex-col items-center justify-center rounded-[1.7rem] bg-[#f8fbff] p-4 text-center shadow-inner sm:rounded-[2rem] sm:p-5">
                <div class="qris-code-frame w-full max-w-[270px] rounded-[1.8rem] bg-white p-3 shadow-lg sm:p-4">
                    @if($qrisImageUrl)
                        <img src="{{ $qrisImageUrl }}" alt="QRIS {{ $invoice }}" class="mx-auto aspect-square w-full rounded-[1.4rem] bg-white object-contain">
                    @else
                        <div class="mx-auto flex aspect-square w-full items-center justify-center rounded-[1.4rem] border-4 border-dashed border-[#bde0fe] bg-white p-6">
                            <p class="text-sm font-black text-[#5a76c8]">QRIS belum tersedia. Tekan Segarkan sebentar lagi.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="qris-info-panel rounded-[2rem] bg-gradient-to-br from-[#8faaf3] via-[#7695e8] to-[#5a76c8] p-4 text-white shadow-xl shadow-[#5a76c8]/20 sm:p-7">
                <div class="qris-status-row flex items-center justify-between gap-4 rounded-[1.7rem] border-2 border-white/60 bg-[#4965b5] p-4 shadow-inner">
                    <p class="text-xs font-black uppercase tracking-widest text-white">Status Pembayaran</p>
                    <span id="qris-payment-status" class="qris-status-badge" data-status="{{ $statusKey }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="mt-5 space-y-4">
                    <div class="qris-info-box rounded-[1.5rem] border-2 border-white/50 bg-[#4965b5] p-4 shadow-inner">
                        <p class="text-[10px] font-black uppercase tracking-widest text-white">Invoice</p>
                        <p class="mt-1 break-all text-lg font-black">{{ $invoice }}</p>
                    </div>
                    <div class="qris-info-box rounded-[1.5rem] border-2 border-white/50 bg-[#4965b5] p-4 shadow-inner">
                        <p class="text-[10px] font-black uppercase tracking-widest text-white">Layanan</p>
                        <p class="mt-1 text-lg font-black">{{ $title }}</p>
                    </div>
                    <div class="qris-info-box rounded-[1.5rem] border-2 border-white/50 bg-[#4965b5] p-4 shadow-inner">
                        <p class="text-[10px] font-black uppercase tracking-widest text-white">QRIS Berlaku Sampai</p>
                        @if($expiresAtLocal)
                            <p id="qris-expiry-label" class="mt-1 text-lg font-black">{{ $expiresAtLabel }}</p>
                            <p id="qris-expiry-countdown" class="qris-expiry-countdown mt-1 text-xs font-black uppercase tracking-widest text-[#e0fbfc]">Menghitung waktu tersisa...</p>
                        @else
                            <p class="mt-1 text-sm font-black text-[#e0fbfc]">Mengikuti masa aktif dari provider.</p>
                        @endif
                    </div>
                </div>

                <div class="mt-5 rounded-[1.7rem] border-2 border-white/60 bg-[#4965b5] p-5 shadow-inner">
                    <div class="flex items-center justify-between gap-4 border-b border-white/40 pb-3">
                        <span class="text-[10px] font-black uppercase tracking-widest text-white">Nominal</span>
                        <span class="text-sm font-black">Rp {{ number_format($baseAmount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-white/40 py-3">
                        <span class="text-[10px] font-black uppercase tracking-widest text-white">Biaya Admin</span>
                        <span class="text-sm font-black">Rp {{ number_format($adminFee, 0, ',', '.') }}</span>
                    </div>
                    <div class="qris-total-row flex items-center justify-between gap-4 pt-3">
                        <span class="qris-total-label text-[10px] font-black uppercase tracking-widest text-white">Total Bayar</span>
                        <span class="qris-total-amount text-2xl font-black text-[#e0fbfc] sm:text-3xl">Rp {{ number_format($totalPayable, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <button type="button" data-qris-refresh class="inline-flex w-full items-center justify-center rounded-full border-4 border-white bg-[#5a76c8] px-6 py-4 text-sm font-black text-white shadow-lg shadow-[#5a76c8]/25 transition active:scale-95">
        Segarkan
    </button>
</div>

<script>
    (() => {
        const statusUrl = @json($statusUrl);
        const fallbackRedirectUrl = @json($redirectUrl);
        const expiresAt = @json($expiresAtIso);
        const badge = document.getElementById('qris-payment-status');
        const buttons = document.querySelectorAll('[data-qris-refresh]');
        const countdown = document.getElementById('qris-expiry-countdown');
        let redirectScheduled = false;
        let pollingStopped = false;

        const formatDuration = (milliseconds) => {
            const totalSeconds = Math.max(0, Math.floor(milliseconds / 1000));
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            if (hours > 0) {
                return `${hours} jam ${minutes} menit ${seconds} detik`;
            }

            if (minutes > 0) {
                return `${minutes} menit ${seconds} detik`;
            }

            return `${seconds} detik`;
        };

        const refreshCountdown = () => {
            if (!countdown || !expiresAt) {
                return;
            }

            const remaining = new Date(expiresAt).getTime() - Date.now();

            if (Number.isNaN(remaining)) {
                countdown.textContent = 'Masa berlaku mengikuti provider.';
                return;
            }

            if (remaining <= 0) {
                countdown.textContent = 'QRIS sudah melewati batas waktu.';
                countdown.classList.remove('text-[#e0fbfc]');
                countdown.classList.add('text-[#ffe5e5]');
                return;
            }

            countdown.textContent = 'Sisa waktu: ' + formatDuration(remaining);
        };

        const applyStatus = (payload) => {
            if (!badge || !payload?.status) {
                return;
            }

            badge.dataset.status = payload.status;
            badge.textContent = payload.label || 'Menunggu';

            if (payload.status === 'success' && !redirectScheduled) {
                redirectScheduled = true;
                pollingStopped = true;
                window.setTimeout(() => {
                    window.location.assign(payload.redirect_url || fallbackRedirectUrl);
                }, 1300);
            }

            if (payload.status === 'failed') {
                pollingStopped = true;
            }
        };

        const refreshStatus = async () => {
            if (pollingStopped) {
                return;
            }

            buttons.forEach((button) => {
                button.disabled = true;
            });

            try {
                const response = await fetch(statusUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    cache: 'no-store',
                    credentials: 'same-origin',
                });

                if (response.ok) {
                    applyStatus(await response.json());
                }
            } finally {
                buttons.forEach((button) => {
                    button.disabled = false;
                    button.textContent = 'Segarkan';
                });
            }
        };

        buttons.forEach((button) => button.addEventListener('click', refreshStatus));

        if (badge?.dataset.status === 'success') {
            applyStatus({
                status: 'success',
                label: 'Sukses',
                redirect_url: fallbackRedirectUrl,
            });
        } else if (badge?.dataset.status !== 'failed') {
            window.setTimeout(refreshStatus, 1500);
            window.setInterval(refreshStatus, 4000);
        }

        refreshCountdown();
        if (expiresAt) {
            window.setInterval(refreshCountdown, 1000);
        }
    })();
</script>
@endsection
