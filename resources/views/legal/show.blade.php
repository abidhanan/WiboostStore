<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page['title'] }} - Wiboost Store</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-[#f4f9ff] text-[#2b3a67] antialiased" style="font-family: 'Nunito', sans-serif;">
    <main class="mx-auto max-w-4xl px-4 py-10 md:py-16">
        <a href="{{ route('home') }}" class="mb-8 inline-flex items-center gap-3 rounded-full border-2 border-white bg-white px-5 py-3 text-sm font-black text-[#5a76c8] shadow-sm transition hover:-translate-y-0.5">
            <span>&larr;</span>
            <span>Kembali ke Beranda</span>
        </a>

        <section class="overflow-hidden rounded-[2.5rem] border-4 border-white bg-white shadow-2xl shadow-[#bde0fe]/30">
            <div class="bg-gradient-to-br from-[#8faaf3] via-[#5a76c8] to-[#4bc6b9] p-8 text-white md:p-10">
                <p class="mb-4 inline-flex rounded-full bg-white/15 px-4 py-2 text-xs font-black uppercase tracking-[0.28em]">{{ $page['badge'] }}</p>
                <h1 class="text-4xl font-black tracking-tight md:text-5xl">{{ $page['title'] }}</h1>
                <p class="mt-4 max-w-2xl text-sm font-bold leading-relaxed text-white/85 md:text-base">{{ $page['intro'] }}</p>
            </div>

            <div class="space-y-5 p-6 md:p-8">
                @foreach($page['sections'] as $section)
                    <article class="rounded-[1.75rem] border-2 border-[#e0ebff] bg-[#f8fbff] p-5">
                        <h2 class="text-xl font-black text-[#2b3a67]">{{ $section['title'] }}</h2>
                        <p class="mt-3 text-sm font-bold leading-relaxed text-[#4a5f96]">{{ $section['body'] }}</p>
                    </article>
                @endforeach

                <div class="rounded-[1.75rem] bg-[#e6fff7] p-5 text-sm font-bold leading-relaxed text-emerald-700">
                    Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}. Kebijakan dapat diperbarui mengikuti kebutuhan operasional dan aturan provider/payment gateway.
                </div>
            </div>
        </section>
    </main>

    @include('partials.floating-admin-report')
</body>
</html>
