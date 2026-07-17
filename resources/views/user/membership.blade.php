@extends('layouts.user')

@section('title', 'Membership - Wiboost Store')

@section('content')
@php
    $user = $user ?? auth()->user();
    $isMember = $isMember ?? ($user?->isMember() ?? false);
    $membershipExpiresAt = $membershipExpiresAt ?? $user?->membership_expires_at;
    $membershipExpiresAtTimestamp = $membershipExpiresAt && method_exists($membershipExpiresAt, 'getTimestamp')
        ? $membershipExpiresAt->getTimestamp()
        : ($membershipExpiresAt ? strtotime((string) $membershipExpiresAt) : null);
    $remainingTotalSeconds = $remainingTotalSeconds ?? ($isMember && $membershipExpiresAtTimestamp ? max(0, $membershipExpiresAtTimestamp - now()->getTimestamp()) : 0);
    $remainingTotalSeconds = (int) floor($remainingTotalSeconds);
    $remainingDays = $remainingDays ?? intdiv($remainingTotalSeconds, 86400);
    $remainingHours = $remainingHours ?? intdiv($remainingTotalSeconds % 86400, 3600);
    $remainingMinutes = $remainingMinutes ?? intdiv($remainingTotalSeconds % 3600, 60);
    $remainingSeconds = $remainingSeconds ?? ($remainingTotalSeconds % 60);
    $memberName = $user?->short_name ?? $user?->name ?? 'Member';
@endphp

<section class="space-y-8">
    @if(session('error'))
        <div class="rounded-[1.5rem] border-4 border-white bg-[#ffe5e5] px-5 py-4 text-sm font-black text-[#ff4d6d] shadow-lg shadow-[#ff6b6b]/20">
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="rounded-[1.5rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-sm font-black text-emerald-500 shadow-lg shadow-emerald-100/50">
            {{ session('success') }}
        </div>
    @endif

    @if($isMember)
        <header class="relative mb-8 flex flex-col items-start gap-6 overflow-hidden rounded-[2.5rem] border-4 border-white p-8 shadow-xl md:p-10" style="background: linear-gradient(135deg, #ddd6fe 0%, #ede9fe 48%, #fff7ff 100%); box-shadow: 0 18px 35px rgba(139, 92, 246, 0.24);">
            <div class="relative z-10 min-w-0">
                <div class="mb-3 inline-block rounded-full border-2 border-white bg-white px-4 py-1 text-xs font-black shadow-sm" style="color: #8b5cf6;">
                    Dashboard Member
                </div>
                <h1 class="wiboost-dashboard-greeting mb-2 whitespace-nowrap font-black text-[#2b3a67] drop-shadow-sm" style="font-size: clamp(1.45rem, 7vw, 3rem); line-height: 1.08;" data-fit-single-line>
                    Halo, {{ $memberName }}!
                    <span class="inline-flex align-middle" style="color:#8b5cf6; transform: translateY(-0.06em);">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.5 4.5h11L21 9l-9 11L3 9l3.5-4.5Z M3 9h18" />
                        </svg>
                    </span>
                </h1>
                <p class="wiboost-dashboard-subtitle whitespace-nowrap font-extrabold" style="color: #8b5cf6; font-size: clamp(0.82rem, 3.8vw, 1rem); line-height: 1.25;" data-fit-single-line>Selamat menikmati produk dan layanan dengan harga yang lebih murah.</p>
            </div>
        </header>

        <div class="rounded-[2rem] border-4 border-white bg-[#f8fbff] p-6 shadow-xl shadow-[#bde0fe]/20 sm:p-8">
            <p class="text-sm font-black uppercase tracking-[0.2em]" style="color: #8b5cf6;">Durasi Membership</p>
            <h2 class="mt-3 text-3xl font-black text-[#2b3a67]" data-membership-countdown data-expires-at="{{ $membershipExpiresAtTimestamp }}">{{ $remainingDays }} hari {{ $remainingHours }} jam {{ $remainingMinutes }} menit {{ $remainingSeconds }} detik</h2>
            <p class="mt-3 text-sm font-bold leading-relaxed text-[#5a76c8]">
                Aktif sampai {{ $membershipExpiresAt?->translatedFormat('d F Y H:i') ?? '-' }}.
            </p>
        </div>
    @else
        <header class="relative mb-8 flex flex-col items-start gap-6 overflow-hidden rounded-[2.5rem] border-4 border-white p-8 shadow-xl md:p-10" style="background: linear-gradient(135deg, #ddd6fe 0%, #ede9fe 48%, #fff7ff 100%); box-shadow: 0 18px 35px rgba(139, 92, 246, 0.24);">
            <span class="mb-3 inline-block rounded-full border-2 border-white bg-white px-4 py-1 text-xs font-black shadow-sm" style="color: #8b5cf6;">Member</span>
            <div class="relative z-10 min-w-0">
                <h1 class="wiboost-dashboard-greeting mb-2 font-black leading-tight text-[#2b3a67] drop-shadow-sm" style="font-size: clamp(1.45rem, 7vw, 3rem); line-height: 1.08;">
                    Yuk Jadi Member!
                    <span class="inline-flex align-middle" style="color:#8b5cf6; transform: translateY(-0.06em);">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.5 4.5h11L21 9l-9 11L3 9l3.5-4.5Z M3 9h18" />
                        </svg>
                    </span>
                </h1>
                <p class="wiboost-dashboard-subtitle max-w-3xl text-sm font-extrabold leading-relaxed sm:text-base" style="color: #8b5cf6;">
                    Nikmati keuntungan potongan harga produk dan layanan yang jauh lebih murah.
                </p>
            </div>
        </header>

        <div class="rounded-[2rem] border-4 border-white bg-[#f8fbff] p-6 shadow-xl shadow-[#bde0fe]/20 sm:p-8">
            <p class="text-sm font-black uppercase tracking-[0.2em]" style="color: #8b5cf6;">Biaya Membership</p>
            <p class="mt-3 text-lg font-black text-[#8faaf3]" style="text-decoration: line-through;">Rp 50.000</p>
            <h2 class="mt-1 text-3xl font-black text-[#2b3a67]">Rp 30.000 / bulan.</h2>
        </div>
    @endif

    <div class="grid gap-6">
        <div class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-xl shadow-[#bde0fe]/20 sm:p-8">
            <p class="text-sm font-black uppercase tracking-[0.2em]" style="color: #8b5cf6;">Benefit</p>
            <div class="mt-5 grid gap-3 text-sm font-bold text-[#5a76c8]">
                <p class="rounded-[1.25rem] bg-[#f4f9ff] px-5 py-4">Banyak potongan harga.</p>
                <p class="rounded-[1.25rem] bg-[#f4f9ff] px-5 py-4">Harga produk dan layanan jauh lebih murah.</p>
                <p class="rounded-[1.25rem] bg-[#f4f9ff] px-5 py-4">Bebas biaya admin untuk semua transaksi.</p>
            </div>
        </div>
    </div>

    @unless($isMember)
        <form action="{{ route('user.membership.join') }}" method="POST">
            @csrf
            <button type="submit" class="flex w-full items-center justify-center rounded-full border-4 border-white px-7 py-4 text-sm font-black transition active:scale-95" style="background-color: #8b5cf6; color: #ffffff; box-shadow: 0 18px 30px rgba(139, 92, 246, 0.32);">
                Gabung Membership
            </button>
        </form>
    @endunless
</section>

@if($isMember && $membershipExpiresAtTimestamp)
    <script>
        (() => {
            const countdown = document.querySelector('[data-membership-countdown]');
            if (! countdown) {
                return;
            }

            const expiresAt = Number(countdown.dataset.expiresAt || 0) * 1000;
            const membershipUrl = @json(route('user.membership.index'));
            let redirectScheduled = false;
            const formatCountdown = (totalSeconds) => {
                const days = Math.floor(totalSeconds / 86400);
                const hours = Math.floor((totalSeconds % 86400) / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;

                return `${days} hari ${hours} jam ${minutes} menit ${seconds} detik`;
            };
            const tick = () => {
                const totalSeconds = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
                countdown.textContent = formatCountdown(totalSeconds);

                if (totalSeconds <= 0 && ! redirectScheduled) {
                    redirectScheduled = true;
                    window.setTimeout(() => {
                        window.location.href = membershipUrl;
                    }, 400);
                }
            };

            tick();
            window.setInterval(tick, 1000);
        })();
    </script>
@endif
@endsection
