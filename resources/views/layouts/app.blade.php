<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Wiboost Store') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/wiboost-logo.png') }}?v=20260426-full">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Nunito', sans-serif; }
            .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
            .animate-float { animation: float 6s ease-in-out infinite; }
            .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
            @keyframes float {
                0% { transform: translateY(0px); }
                50% { transform: translateY(-15px); }
                100% { transform: translateY(0px); }
            }
        </style>
    </head>
    <body class="font-sans antialiased text-slate-800 selection:bg-[#7b9eed] selection:text-white bg-wiboost-sky min-h-screen relative overflow-x-hidden">
        
        <div class="fixed top-20 left-10 text-4xl animate-float opacity-60 pointer-events-none z-0">☁️</div>
        <div class="fixed top-32 right-20 text-3xl animate-float-delayed opacity-60 pointer-events-none z-0">✨</div>
        <div class="fixed bottom-40 left-1/4 text-2xl animate-float-delayed opacity-50 pointer-events-none z-0">⭐</div>
        <div class="fixed bottom-20 right-1/4 text-5xl animate-float opacity-60 pointer-events-none z-0">☁️</div>

        <div class="min-h-screen relative z-10 flex flex-col">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white/80 backdrop-blur-md shadow-xl shadow-[#bde0fe]/40 border-4 border-white mt-6 mx-4 sm:mx-6 lg:mx-8 rounded-[2rem]">
                    <div class="max-w-7xl mx-auto py-5 px-6 sm:px-8 font-black text-[#2b3a67]">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1 w-full max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] border-4 border-white shadow-2xl shadow-[#bde0fe]/30 p-6 sm:p-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
