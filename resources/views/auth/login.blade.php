<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Wiboost Store</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; }
        .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
        .animate-float { animation: float 6s ease-in-out infinite; }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="bg-wiboost-sky min-h-screen flex items-center justify-center p-4 relative overflow-hidden selection:bg-[#7b9eed] selection:text-white">
    
    <div class="absolute top-10 left-10 text-7xl animate-float opacity-60 pointer-events-none">☁️</div>
    <div class="absolute bottom-20 right-10 text-8xl animate-float opacity-60 pointer-events-none" style="animation-delay: 2s;">☁️</div>
    <div class="absolute top-1/4 right-20 text-4xl animate-float opacity-50 pointer-events-none" style="animation-delay: 1s;">✨</div>

    <div class="w-full max-w-md relative z-10">
        <div class="flex justify-center mb-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-[#5a76c8] font-black text-3xl shadow-lg border-4 border-white group-hover:scale-110 transition-transform">W</div>
                <span class="font-black text-3xl tracking-tight text-[#2b3a67]">Wiboost</span>
            </a>
        </div>

        <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-2xl shadow-[#5a76c8]/20 border-4 border-white">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-black text-[#2b3a67]">Selamat Datang! 👋</h2>
                <p class="text-[#8faaf3] font-bold text-sm mt-1">Masuk untuk mulai jajan kebutuhan digitalmu.</p>
            </div>

            @if (session('status'))
                <div class="mb-4 font-bold text-sm text-emerald-500 bg-[#e6fff7] p-4 rounded-[1.5rem] border-2 border-white text-center">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="mb-5">
                    <label for="email" class="block text-sm font-black text-[#8faaf3] mb-2 pl-2">Alamat Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="w-full bg-[#f4f9ff] border-2 border-[#e0fbfc] focus:border-[#5a76c8] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]"
                           placeholder="nama@email.com">
                    @error('email')
                        <p class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-sm font-black text-[#8faaf3] mb-2 pl-2">Kata Sandi</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           class="w-full bg-[#f4f9ff] border-2 border-[#e0fbfc] focus:border-[#5a76c8] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]"
                           placeholder="••••••••">
                    @error('password')
                        <p class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-8 px-2">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                        <input id="remember_me" type="checkbox" class="rounded-md border-2 border-[#8faaf3] text-[#5a76c8] shadow-sm focus:ring-[#5a76c8] w-5 h-5 transition-colors" name="remember">
                        <span class="ml-2 text-sm font-bold text-[#8faaf3] group-hover:text-[#5a76c8] transition-colors">Ingat saya</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-sm font-bold text-[#5a76c8] hover:text-[#4760a9] transition-colors" href="{{ route('password.request') }}">
                            Lupa sandi?
                        </a>
                    @endif
                </div>

                <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-lg py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg shadow-[#5a76c8]/30 border-2 border-white">
                    Masuk Sekarang 🚀
                </button>

                <div class="text-center mt-6">
                    <p class="text-sm font-bold text-[#8faaf3]">Belum punya akun? 
                        <a href="{{ route('register') }}" class="text-[#5a76c8] font-black hover:underline">Daftar di sini</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>