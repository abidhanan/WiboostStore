@extends('layouts.user')

@section('title', 'Poin Wiboost')

@section('content')
@php
    $currentPoints = $currentPoints ?? (int) (Auth::user()->points ?? 0);
    $rewards = $rewards ?? [
        ['points' => 3, 'amount' => 1000, 'label' => 'Saldo Rp 1.000'],
        ['points' => 5, 'amount' => 3000, 'label' => 'Saldo Rp 3.000'],
        ['points' => 8, 'amount' => 6000, 'label' => 'Saldo Rp 6.000'],
    ];
    $currentRewardIndex = $currentRewardIndex ?? min((int) (Auth::user()->point_reward_stage ?? 0), count($rewards) - 1);
@endphp

<div class="wiboost-font mx-auto max-w-5xl pb-16">
    <div class="mb-8 text-left">
        <div class="inline-block rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1 text-[10px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
            Loyalty
        </div>
        <h1 class="mt-3 flex items-center justify-start gap-2 text-3xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm sm:text-4xl">
            <span class="text-[#5a76c8]">@include('partials.wiboost-icon', ['name' => 'points', 'class' => 'h-8 w-8'])</span>
            <span>Poin Wiboost</span>
        </h1>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-6 py-4 font-black text-emerald-500 shadow-lg shadow-emerald-100/50">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-6 py-4 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20">
            {{ session('error') }}
        </div>
    @endif

    <section class="mb-8 rounded-[2.5rem] border-4 border-white bg-white/95 p-8 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm">
        <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Total Poin</p>
        <h2 class="text-5xl font-black tracking-tight text-[#2b3a67]">
            {{ $currentPoints }} <span class="text-lg font-black text-[#8faaf3]">POIN</span>
        </h2>
    </section>

    <section class="rounded-[2.5rem] border-4 border-white bg-white/95 p-6 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm md:p-8">
        <div class="mb-6">
            <p class="text-[10px] font-black uppercase tracking-widest text-[#4bc6b9]">Pilihan Penukaran Poin</p>
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            @foreach($rewards as $index => $reward)
                @php
                    $isCompleted = $index < $currentRewardIndex;
                    $isActive = $index === $currentRewardIndex;
                    $isLocked = $index > $currentRewardIndex;
                    $progress = $isCompleted ? 100 : ($isActive ? min(100, (int) round(($currentPoints / max(1, (int) $reward['points'])) * 100)) : 0);
                    $displayPoints = $isLocked ? 0 : min($currentPoints, (int) $reward['points']);
                    $canClaim = $isActive && $currentPoints >= (int) $reward['points'];
                    $remaining = max(0, (int) $reward['points'] - $currentPoints);
                @endphp

                <article class="relative overflow-hidden rounded-[2rem] border-4 {{ $isActive ? 'border-[#bde0fe] bg-[#f8fbff]' : 'border-white bg-white' }} p-5 shadow-lg shadow-[#bde0fe]/25">
                    <div class="mb-5 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest {{ $isActive ? 'text-[#5a76c8]' : 'text-[#8faaf3]' }}">
                                Tahap {{ $index + 1 }}
                            </p>
                            <h3 class="mt-2 text-2xl font-black text-[#2b3a67]">{{ $reward['label'] }}</h3>
                        </div>
                        <span class="rounded-full border-2 border-white px-3 py-1 text-[10px] font-black uppercase tracking-widest shadow-sm {{ $isCompleted ? 'bg-[#e6fff7] text-emerald-500' : ($isActive ? 'bg-[#e0fbfc] text-[#4bc6b9]' : 'bg-[#f4f9ff] text-[#8faaf3]') }}">
                            {{ $isCompleted ? 'Selesai' : ($isActive ? 'Aktif' : 'Terkunci') }}
                        </span>
                    </div>

                    <div class="mb-4 rounded-[1.25rem] border-4 border-dashed border-[#d8ecff] bg-white p-4 shadow-inner">
                        <div class="mb-3 flex items-center justify-between gap-3 text-xs font-black uppercase tracking-widest text-[#8faaf3]">
                            <span>{{ $displayPoints }}/{{ $reward['points'] }} poin</span>
                            @if($canClaim)
                                <span>Siap klaim</span>
                            @endif
                        </div>
                        <div class="h-4 overflow-hidden rounded-full border-2 border-white bg-[#eef6ff] shadow-inner">
                            <div class="h-full rounded-full bg-[#5a76c8] shadow-[0_0_18px_rgba(90,118,200,0.35)] transition-all duration-500" style="width: {{ $progress }}%; min-width: {{ $progress > 0 ? '1rem' : '0' }};"></div>
                        </div>
                    </div>

                    @if($canClaim)
                        <form action="{{ route('user.wallet.exchange') }}" method="POST">
                            @csrf
                            <input type="hidden" name="reward_index" value="{{ $index }}">
                            <button type="submit" class="flex w-full items-center justify-center rounded-full border-4 border-white bg-[#5a76c8] py-3.5 text-sm font-black text-white shadow-lg shadow-[#5a76c8]/30 transition-transform hover:bg-[#4d66b1] active:scale-95">
                                Klaim
                            </button>
                        </form>
                    @else
                        <button type="button" disabled class="flex w-full items-center justify-center rounded-full border-4 border-white bg-[#f4f9ff] py-3.5 text-sm font-black text-[#8faaf3] shadow-sm">
                            {{ $isCompleted ? 'Sudah diklaim' : ($isLocked ? 'Belum terbuka' : 'Kumpulkan poin') }}
                        </button>
                    @endif
                </article>
            @endforeach
        </div>

        <p class="mt-6 text-center text-xs font-extrabold leading-relaxed text-[#8faaf3]">
            Tahap akan reset setiap berganti bulan, dan sisa poin tetap bisa dipakai untuk tahap berikutnya.
        </p>
    </section>
</div>
@endsection
