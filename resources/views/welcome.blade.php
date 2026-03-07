<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wiboost Store - Solusi Kebutuhan Digitalmu</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; }
        .bg-wiboost { background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%); }
        .floating { animation: float 3s ease-in-out infinite; }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="bg-gray-50 antialiased text-gray-800 flex flex-col min-h-screen">

    <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 shadow-sm border-b border-blue-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center gap-2">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-md">W</div>
                    <span class="text-2xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600">
                        Wiboost Store
                    </span>
                </div>
                <div>
                    @auth
                        <a href="{{ url('/user/dashboard') }}" class="px-5 py-2 rounded-full text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md transition-all">Dasbor Saya</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-bold text-gray-600 hover:text-blue-600 mr-4 transition-colors">Masuk</a>
                        <a href="{{ route('register') }}" class="px-5 py-2 rounded-full text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md hover:shadow-lg transition-all">Daftar</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="bg-wiboost relative overflow-hidden pb-16 pt-12 sm:pb-24 sm:pt-20">
        <div class="absolute inset-x-0 bottom-0">
            <svg viewBox="0 0 1440 120" class="w-full h-auto text-gray-50 fill-current" preserveAspectRatio="none">
                <path d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64L1440,120L1360,120C1280,120,1120,120,960,120C800,120,640,120,480,120C320,120,160,120,80,120L0,120Z"></path>
            </svg>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center z-10">
            <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl drop-shadow-sm">
                <span class="block text-gray-800">Satu Platform Untuk</span>
                <span class="block text-blue-700 mt-2">Semua Kebutuhan Digital</span>
            </h1>
            <p class="mt-4 max-w-xl mx-auto text-base text-gray-700 sm:text-lg md:mt-6 md:text-xl font-medium">
                Suntik Sosmed, Top Up Game, Aplikasi Premium, hingga Jasa Buzzer. Cepat, otomatis, dan dijamin aman!
            </p>
            <div class="mt-8 flex justify-center gap-4">
                <a href="{{ route('register') }}" class="floating px-8 py-3 border border-transparent text-base font-bold rounded-full text-white bg-blue-600 hover:bg-blue-700 shadow-xl hover:shadow-2xl transition-all">
                    Mulai Transaksi
                </a>
            </div>
        </div>
    </main>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20 mb-16">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 text-center hover:-translate-y-1 transition-transform cursor-pointer">
                <div class="w-14 h-14 mx-auto bg-pink-100 text-pink-500 rounded-xl flex items-center justify-center text-2xl mb-3 shadow-inner">❤️</div>
                <h3 class="font-bold text-gray-800">Suntik Sosmed</h3>
                <p class="text-xs text-gray-500 mt-1">Followers, Likes, Views</p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 text-center hover:-translate-y-1 transition-transform cursor-pointer">
                <div class="w-14 h-14 mx-auto bg-purple-100 text-purple-500 rounded-xl flex items-center justify-center text-2xl mb-3 shadow-inner">🎮</div>
                <h3 class="font-bold text-gray-800">Top Up Game</h3>
                <p class="text-xs text-gray-500 mt-1">MLBB, FF, PUBG, dll</p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 text-center hover:-translate-y-1 transition-transform cursor-pointer">
                <div class="w-14 h-14 mx-auto bg-red-100 text-red-500 rounded-xl flex items-center justify-center text-2xl mb-3 shadow-inner">🎬</div>
                <h3 class="font-bold text-gray-800">Aplikasi Premium</h3>
                <p class="text-xs text-gray-500 mt-1">Netflix, Canva, Spotify</p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 text-center hover:-translate-y-1 transition-transform cursor-pointer">
                <div class="w-14 h-14 mx-auto bg-blue-100 text-blue-500 rounded-xl flex items-center justify-center text-2xl mb-3 shadow-inner">🌐</div>
                <h3 class="font-bold text-gray-800">Kuota Murah</h3>
                <p class="text-xs text-gray-500 mt-1">All Operator</p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 text-center hover:-translate-y-1 transition-transform cursor-pointer">
                <div class="w-14 h-14 mx-auto bg-green-100 text-green-500 rounded-xl flex items-center justify-center text-2xl mb-3 shadow-inner">📱</div>
                <h3 class="font-bold text-gray-800">Nomor Luar</h3>
                <p class="text-xs text-gray-500 mt-1">OTP Telegram, WA</p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 text-center hover:-translate-y-1 transition-transform cursor-pointer">
                <div class="w-14 h-14 mx-auto bg-yellow-100 text-yellow-600 rounded-xl flex items-center justify-center text-2xl mb-3 shadow-inner">📣</div>
                <h3 class="font-bold text-gray-800">Buzzer Custom</h3>
                <p class="text-xs text-gray-500 mt-1">Komentar & Rating</p>
            </div>
        </div>
    </div>

    <div class="bg-white py-8 border-y border-gray-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-around items-center gap-6">
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Total Pengguna</p>
                    <p class="mt-1 text-4xl font-extrabold text-blue-600">{{ number_format($totalUsers, 0, ',', '.') }}<span class="text-2xl">+</span></p>
                </div>
                <div class="hidden md:block w-px h-16 bg-gray-200"></div>
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Transaksi Sukses</p>
                    <p class="mt-1 text-4xl font-extrabold text-green-500">{{ number_format($totalTransactions, 0, ',', '.') }}<span class="text-2xl">+</span></p>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed bottom-4 left-4 z-50 bg-white/90 backdrop-blur-sm border border-blue-100 p-3 rounded-xl shadow-lg flex items-center gap-3 animate-bounce" style="animation-duration: 3s;">
        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-xs font-bold">✓</div>
        <div>
            <p class="text-xs text-gray-500">Baru saja membeli</p>
            <p class="text-sm font-bold text-gray-800">50 Diamond MLBB</p>
        </div>
    </div>

    <footer class="mt-auto bg-white py-6 text-center border-t border-gray-200">
        <p class="text-sm text-gray-500 font-medium">&copy; {{ date('Y') }} Wiboost Store. All rights reserved.</p>
    </footer>

</body>
</html>