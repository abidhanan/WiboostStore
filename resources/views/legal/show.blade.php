<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page['title'] }} - Wiboost Store</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="min-h-screen bg-[#f4f9ff] text-[#2b3a67] antialiased relative overflow-x-hidden selection:bg-[#7b9eed] selection:text-white" style="font-family: 'Nunito', sans-serif;">
    
    <div class="fixed top-20 left-10 text-5xl animate-float opacity-50 pointer-events-none z-0 hidden md:block">☁️</div>
    <div class="fixed top-32 right-20 text-4xl animate-float-delayed opacity-50 pointer-events-none z-0 hidden md:block">✨</div>
    <div class="fixed bottom-40 left-[15%] text-4xl animate-float-delayed opacity-50 pointer-events-none z-0 hidden md:block">📄</div>
    <div class="fixed bottom-10 right-10 text-6xl animate-float opacity-60 pointer-events-none z-0 hidden md:block">☁️</div>

    <main class="mx-auto max-w-4xl px-4 py-10 md:py-16 relative z-10">
        <a href="{{ route('home') }}" class="mb-8 inline-flex items-center gap-3 rounded-full border-4 border-white bg-white/90 backdrop-blur-sm px-6 py-3.5 text-sm font-black text-[#5a76c8] shadow-lg hover:shadow-[#bde0fe]/50 transition-transform active:scale-95">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Kembali ke Beranda</span>
        </a>

        <section class="space-y-6">
            <div class="rounded-[2.5rem] border-4 border-white bg-gradient-to-br from-[#8faaf3] via-[#5a76c8] to-[#4bc6b9] p-8 text-white shadow-xl shadow-[#bde0fe]/40 md:p-12 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 text-9xl opacity-20 transform rotate-12 pointer-events-none animate-float">⚖️</div>
                <div class="relative z-10">
                    <p class="mb-4 inline-flex rounded-full bg-white/20 border-2 border-white/50 shadow-inner px-4 py-2 text-[10px] font-black uppercase tracking-widest">{{ $page['badge'] }}</p>
                    <h1 class="text-4xl font-black tracking-tight md:text-5xl drop-shadow-md">{{ $page['title'] }}</h1>
                    <p class="mt-4 max-w-2xl text-sm font-bold leading-relaxed text-white/90 md:text-base drop-shadow-sm bg-white/10 p-4 rounded-[1.5rem] backdrop-blur-sm border border-white/20">{{ $page['intro'] }}</p>
                </div>
            </div>

            <div class="space-y-5">
                @foreach($page['sections'] as $section)
                    <article class="rounded-[2rem] border-4 border-white bg-white p-6 md:p-8 shadow-md shadow-[#bde0fe]/20 hover:border-[#bde0fe] hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-2xl drop-shadow-sm">📌</span>
                            <h2 class="text-xl md:text-2xl font-black text-[#2b3a67]">{{ $section['title'] }}</h2>
                        </div>
                        <div class="rounded-[1.5rem] bg-[#f8fbff] p-5 border-2 border-[#f0f5ff] shadow-inner">
                            <p class="text-sm font-bold leading-relaxed text-[#4a5f96] whitespace-pre-line">{{ $section['body'] }}</p>
                        </div>
                    </article>
                @endforeach

                <div class="rounded-[2rem] border-4 border-white bg-[#e6fff7] p-6 text-sm font-bold leading-relaxed text-emerald-600 shadow-md flex items-start gap-4">
                    <span class="text-2xl drop-shadow-sm animate-bounce">💡</span>
                    <p class="mt-1">Terakhir diperbarui: <span class="font-black text-emerald-700">{{ now()->translatedFormat('d F Y') }}</span>. Kebijakan dapat diperbarui sewaktu-waktu mengikuti kebutuhan operasional dan aturan dari provider/payment gateway.</p>
                </div>
            </div>
        </section>
    </main>

    @include('partials.floating-admin-report')
</body>
</html>