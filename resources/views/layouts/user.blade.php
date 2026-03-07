<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Wiboost Store</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-[#F8FAFC] antialiased">

    <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shadow-indigo-200 shadow-lg">
                        <span class="text-white font-bold text-lg">W</span>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-gray-900">Wiboost<span class="text-indigo-600">.</span></span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('user.dashboard') }}" class="text-sm font-semibold text-gray-600 hover:text-indigo-600 transition">Beranda</a>
                    <a href="{{ route('user.history') }}" class="text-sm font-semibold text-gray-600 hover:text-indigo-600 transition">Pesanan Saya</a>
                    <div class="h-6 w-[1px] bg-gray-200"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-bold text-red-500 hover:text-red-600 uppercase tracking-wider">Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <footer class="mt-20 py-10 border-t border-gray-200 bg-white">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <p class="text-gray-400 text-sm">&copy; 2026 Wiboost Store. Made with 🔥 for Digital Growth.</p>
        </div>
    </footer>

</body>
</html>