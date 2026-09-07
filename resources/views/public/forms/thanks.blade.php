@extends('layouts.guest')

@section('title', 'Formulir Terkirim - Wiboost Store')

@section('content')
@php
    $rawBenefitCredentials = is_array($form->benefit_credentials ?? null) ? $form->benefit_credentials : [];

    if (empty($rawBenefitCredentials['link']) && ! empty($form->benefit_url)) {
        $rawBenefitCredentials['link'] = $form->benefit_url;
    }

    $benefitCredentialLabels = [
        'email' => 'Email',
        'password' => 'Password',
        'profile' => 'Profil',
        'pin' => 'PIN',
        'link' => 'Link Akses',
        'tutorial_link' => 'Link Tutorial',
        'notes' => 'Catatan Login',
    ];

    $benefitCredentialItems = [];

    foreach ($benefitCredentialLabels as $key => $label) {
        $value = trim((string) ($rawBenefitCredentials[$key] ?? ''));

        if ($value !== '') {
            $benefitCredentialItems[] = [
                'key' => $key,
                'label' => $label,
                'value' => $value,
                'is_url' => preg_match('/^https?:\/\//i', $value) === 1,
            ];
        }
    }
@endphp

<style>
    .wiboost-form-thanks {
        width: 100%;
        text-align: center;
        color: #2b3a67;
        font-family: 'Nunito', sans-serif;
    }

    .wiboost-form-thanks-success {
        display: flex;
        width: 5rem;
        height: 5rem;
        min-width: 5rem;
        min-height: 5rem;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        margin: 0 auto 1.5rem;
        border: 4px solid #fff;
        border-radius: 2rem;
        background: #e6fff7;
        color: #4bc6b9;
        box-shadow: 0 18px 36px rgba(110, 231, 183, .26);
    }

    .wiboost-form-thanks-badge {
        display: inline-flex;
        border: 2px solid #fff;
        border-radius: 9999px;
        background: #e0fbfc;
        padding: .5rem 1.25rem;
        color: #4bc6b9;
        font-size: .625rem;
        font-weight: 900;
        letter-spacing: .24em;
        text-transform: uppercase;
        box-shadow: 0 2px 8px rgba(75, 198, 185, .12);
    }

    .wiboost-form-thanks-title {
        margin: 1.25rem 0 0;
        color: #2b3a67;
        font-size: clamp(1.9rem, 5vw, 2.45rem);
        font-weight: 900;
        line-height: 1.12;
    }

    .wiboost-form-thanks-copy {
        max-width: 36rem;
        margin: .75rem auto 0;
        color: #5a76c8;
        font-size: .95rem;
        font-weight: 800;
        line-height: 1.65;
    }

    .wiboost-form-benefit-card {
        margin-top: 2rem;
        border: 4px solid #fff;
        border-radius: 2.2rem;
        background: #fffdf2;
        padding: 1.25rem;
        text-align: left;
        box-shadow: 0 22px 44px rgba(254, 243, 199, .7);
    }

    .wiboost-form-benefit-head {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .wiboost-form-benefit-icon {
        display: flex;
        width: 3.5rem;
        height: 3.5rem;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        border-radius: 1.5rem;
        background: #fff7d6;
        color: #d99b16;
        box-shadow: 0 8px 18px rgba(217, 155, 22, .12);
    }

    .wiboost-form-benefit-icon svg,
    .wiboost-form-benefit-access-head svg {
        display: block;
        width: 1.5rem;
        height: 1.5rem;
    }

    .wiboost-form-benefit-eyebrow,
    .wiboost-form-benefit-access-title,
    .wiboost-form-benefit-label {
        font-size: .625rem;
        font-weight: 900;
        letter-spacing: .22em;
        text-transform: uppercase;
    }

    .wiboost-form-benefit-eyebrow,
    .wiboost-form-benefit-access-title {
        margin: 0;
        color: #d99b16;
    }

    .wiboost-form-benefit-title {
        margin: .5rem 0 0;
        color: #2b3a67;
        font-size: clamp(1.35rem, 4vw, 1.55rem);
        font-weight: 900;
        line-height: 1.15;
    }

    .wiboost-form-benefit-description {
        margin: .75rem 0 0;
        color: #5a76c8;
        font-size: .9rem;
        font-weight: 800;
        line-height: 1.55;
    }

    .wiboost-form-benefit-access {
        margin-top: 1.5rem;
        border: 4px solid #fff;
        border-radius: 2rem;
        background: #fffcf0;
        padding: 1.25rem;
        box-shadow: 0 16px 34px rgba(251, 191, 36, .16);
    }

    .wiboost-form-benefit-access-head {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-bottom: 1.25rem;
    }

    .wiboost-form-benefit-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .wiboost-form-benefit-label {
        margin: 0 0 .25rem .25rem;
        color: rgba(217, 119, 6, .72);
    }

    .wiboost-form-benefit-value-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        border: 4px solid #fef3c7;
        border-radius: 1.2rem;
        background: #fff;
        padding: .85rem 1.1rem;
        box-shadow: 0 8px 18px rgba(251, 191, 36, .1);
    }

    .wiboost-form-benefit-value {
        min-width: 0;
        margin: 0;
        color: #2b3a67;
        font-size: .9rem;
        font-weight: 900;
        line-height: 1.45;
        word-break: break-word;
    }

    .wiboost-form-benefit-value-link {
        color: #5a76c8;
        text-decoration: underline;
        text-decoration-color: #bde0fe;
        text-underline-offset: 4px;
        word-break: break-all;
    }

    .wiboost-form-benefit-copy {
        display: flex;
        width: 2.35rem;
        height: 2.35rem;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border: 1px solid #fef3c7;
        border-radius: .8rem;
        background: #fffbeb;
        color: #f59e0b;
        cursor: pointer;
        transition: color .16s ease, transform .16s ease;
    }

    .wiboost-form-benefit-copy svg {
        display: block;
        width: 1.25rem;
        height: 1.25rem;
    }

    .wiboost-form-benefit-copy:hover {
        color: #d97706;
    }

    .wiboost-form-benefit-copy:active {
        transform: scale(.96);
    }

    .wiboost-form-thanks-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-top: 2rem;
        border: 4px solid #fff;
        border-radius: 9999px;
        background: #f4f9ff;
        padding: .75rem 1.5rem;
        color: #5a76c8;
        font-size: .9rem;
        font-weight: 900;
        text-decoration: none;
        box-shadow: 0 10px 22px rgba(143, 170, 243, .16);
    }
</style>

<div class="wiboost-font wiboost-form-thanks text-center">
    <div class="wiboost-form-thanks-success mx-auto mb-6 flex aspect-square h-20 w-20 min-w-20 shrink-0 items-center justify-center rounded-[2rem] border-4 border-white bg-[#e6fff7] text-[#4bc6b9] shadow-xl shadow-emerald-100/60" style="width:5rem;height:5rem;min-width:5rem;min-height:5rem;">
        <svg width="40" height="40" class="block shrink-0" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5"/>
        </svg>
    </div>

    <span class="wiboost-form-thanks-badge inline-flex rounded-full border-2 border-white bg-[#e0fbfc] px-5 py-2 text-[10px] font-black uppercase tracking-[0.24em] text-[#4bc6b9] shadow-sm">
        Jawaban Terkirim
    </span>

    <h1 class="wiboost-form-thanks-title mt-5 text-3xl font-black leading-tight text-[#2b3a67] sm:text-4xl">Terima kasih sudah mengisi formulir.</h1>
    <p class="wiboost-form-thanks-copy mx-auto mt-3 max-w-xl text-sm font-bold leading-relaxed text-[#5a76c8] sm:text-base">
        Jawaban kamu sudah masuk ke sistem Wiboost Store.
    </p>

    @if($form->benefit_enabled)
        <section class="wiboost-form-benefit-card mt-8 rounded-[2rem] border-4 border-white bg-[#fffdf2] p-5 text-left shadow-xl shadow-[#fef3c7]/70 sm:rounded-[2.5rem] sm:p-6">
            <div class="wiboost-form-benefit-head flex items-start gap-4">
                <span class="wiboost-form-benefit-icon flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.5rem] border-2 border-white bg-[#fff7d6] text-[#d99b16] shadow-sm">
                    @include('partials.wiboost-icon', ['name' => 'gift', 'class' => 'h-7 w-7'])
                </span>
                <div class="min-w-0">
                    <p class="wiboost-form-benefit-eyebrow text-[10px] font-black uppercase tracking-[0.24em] text-[#d99b16]">Benefit Aplikasi Premium</p>
                    <h2 class="wiboost-form-benefit-title mt-2 text-2xl font-black leading-tight text-[#2b3a67]">{{ $form->benefit_title ?: 'Benefit Aplikasi Premium' }}</h2>
                    @if($form->benefit_description)
                        <p class="wiboost-form-benefit-description mt-3 text-sm font-bold leading-relaxed text-[#5a76c8]">{{ $form->benefit_description }}</p>
                    @endif
                </div>
            </div>

            @if($benefitCredentialItems !== [])
                <div class="wiboost-form-benefit-access mt-6 rounded-[2rem] border-4 border-white bg-[#fffcf0] p-5 shadow-md shadow-amber-100/50">
                    <div class="wiboost-form-benefit-access-head mb-5 flex items-center gap-3">
                        <span class="drop-shadow-sm">@include('partials.wiboost-icon', ['name' => 'profile', 'class' => 'h-6 w-6 text-amber-500'])</span>
                        <p class="wiboost-form-benefit-access-title text-[10px] font-black uppercase tracking-widest text-amber-500">Akses Akun Kamu</p>
                    </div>

                    <div class="wiboost-form-benefit-list space-y-4">
                        @foreach($benefitCredentialItems as $item)
                            <div>
                                <p class="wiboost-form-benefit-label mb-1 ml-1 text-[9px] font-black uppercase tracking-widest text-amber-600/70">{{ $item['label'] }}</p>
                                <div class="wiboost-form-benefit-value-row flex items-center justify-between gap-3 rounded-[1.2rem] border-4 border-amber-100 bg-white px-5 py-3.5 shadow-sm">
                                    @if($item['is_url'])
                                        <a href="{{ $item['value'] }}" target="_blank" rel="noopener noreferrer" class="wiboost-form-benefit-value wiboost-form-benefit-value-link min-w-0 break-all text-sm font-black text-[#5a76c8] underline decoration-[#bde0fe] underline-offset-4">{{ $item['value'] }}</a>
                                    @else
                                        <p class="wiboost-form-benefit-value min-w-0 break-words text-sm font-black text-[#2b3a67]">{{ $item['value'] }}</p>
                                    @endif
                                    <button type="button" data-copy-benefit="{{ $item['value'] }}" class="wiboost-form-benefit-copy shrink-0 rounded-xl border border-amber-100 bg-amber-50 p-2 text-amber-400 transition-colors hover:text-amber-600 active:scale-95" title="Salin">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2m-6 12h8a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-8a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2Z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    @endif

    <a href="{{ route('home') }}" class="wiboost-form-thanks-back mt-8 inline-flex items-center justify-center rounded-full border-4 border-white bg-[#f4f9ff] px-6 py-3 text-sm font-black text-[#5a76c8] shadow-md transition-transform active:scale-95">
        Kembali ke Wiboost Store
    </a>
</div>

<script>
    document.addEventListener('click', async function (event) {
        const button = event.target.closest('[data-copy-benefit]');

        if (!button) {
            return;
        }

        const value = button.dataset.copyBenefit || '';

        try {
            await navigator.clipboard.writeText(value);
        } catch (error) {
            window.prompt('Salin data benefit:', value);
        }
    });
</script>
@endsection
