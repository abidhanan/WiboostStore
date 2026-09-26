@extends(Auth::check() ? 'layouts.user' : 'layouts.catalog_guest')

@section('title', $page['title'])
@section('meta_description', $page['intro'])
@section('body_class', 'wiboost-legal-page')

@section('content')
    <style>
        .wiboost-legal-content {
            width: 100%;
        }

        .wiboost-legal-hero,
        .wiboost-legal-card,
        .wiboost-legal-update {
            border: 4px solid #ffffff;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 22px 50px rgba(189, 224, 254, 0.32);
        }

        .wiboost-legal-card {
            transition: transform 220ms ease, border-color 220ms ease;
        }

        .wiboost-legal-card:hover {
            transform: translateY(-4px);
            border-color: #bde0fe;
        }

        @media (max-width: 767px) {
            .wiboost-legal-hero,
            .wiboost-legal-card,
            .wiboost-legal-update {
                border-radius: 1.75rem;
            }
        }
    </style>

    <div class="wiboost-font wiboost-legal-content mx-auto max-w-6xl py-8 md:py-10">
        <section class="wiboost-legal-hero rounded-[2.5rem] p-8 text-left backdrop-blur-md md:p-12">
            <div class="mb-5 inline-block rounded-full border-2 border-white bg-[#e0fbfc] px-5 py-2 text-xs font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                {{ $page['badge'] }}
            </div>
            <h1 class="text-3xl font-black tracking-tight text-[#2b3a67] md:text-5xl">{{ $page['title'] }}</h1>
            <p class="mt-5 max-w-3xl text-sm font-bold leading-relaxed text-[#4a5f96] md:text-base">{{ $page['intro'] }}</p>
        </section>

        <section class="mt-8 grid grid-cols-1 gap-5 md:grid-cols-3">
            @foreach($page['sections'] as $section)
                <article class="wiboost-legal-card rounded-[2rem] p-6 text-left md:p-7">
                    <div class="mb-4 flex items-center gap-4">
                        <div class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border-2 border-white bg-[#f4f9ff] text-lg font-black text-[#5a76c8] shadow-inner">
                            {{ $loop->iteration }}
                        </div>
                        <h2 class="text-xl font-black leading-tight text-[#2b3a67] md:text-2xl">{{ $section['title'] }}</h2>
                    </div>
                    <p class="whitespace-pre-line text-sm font-bold leading-relaxed text-[#4a5f96]">{{ $section['body'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="wiboost-legal-update mt-8 rounded-[2rem] bg-[#e6fff7] p-6 text-left md:p-7">
            <p class="text-[10px] font-black uppercase tracking-widest text-[#4bc6b9]">Update Kebijakan</p>
            <p class="mt-2 text-sm font-bold leading-relaxed text-[#4a5f96]">
                Terakhir diperbarui:
                <span class="font-black text-[#2b3a67]">{{ now()->translatedFormat('d F Y') }}</span>.
                Kebijakan dapat diperbarui sewaktu-waktu mengikuti kebutuhan operasional dan aturan dari provider/payment gateway.
            </p>
        </section>
    </div>
@endsection
