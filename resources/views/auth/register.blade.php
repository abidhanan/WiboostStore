<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar - Wiboost Store</title>
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
<body class="bg-wiboost-sky min-h-screen flex flex-col items-center justify-center p-4 relative selection:bg-[#7b9eed] selection:text-white py-12 overflow-y-auto">
    
    <div class="fixed top-20 left-5 md:left-20 text-6xl animate-float opacity-60 pointer-events-none">☁️</div>
    <div class="fixed bottom-10 right-5 md:right-20 text-7xl animate-float opacity-60 pointer-events-none" style="animation-delay: 2s;">☁️</div>
    <div class="fixed top-1/3 right-10 text-4xl animate-float opacity-50 pointer-events-none" style="animation-delay: 1s;">✨</div>

    <div class="w-full max-w-md relative z-10 my-8">
        <div class="flex justify-center mb-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-[#5a76c8] font-black text-3xl shadow-lg border-4 border-white group-hover:scale-110 transition-transform">W</div>
                <span class="font-black text-3xl tracking-tight text-[#2b3a67]">Wiboost</span>
            </a>
        </div>

        <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-2xl shadow-[#5a76c8]/20 border-4 border-white">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-black text-[#2b3a67]">Buat Akun Baru 🚀</h2>
                <p class="text-[#8faaf3] font-bold text-sm mt-1">Gabung sekarang dan nikmati top up super murah!</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-5">
                    <label for="name" class="block text-sm font-black text-[#8faaf3] mb-2 pl-2">Nama Panggilan</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                           class="w-full bg-[#f4f9ff] border-2 border-[#e0fbfc] focus:border-[#5a76c8] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]"
                           placeholder="Ketik namamu">
                    @error('name')<p class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2">{{ $message }}</p>@enderror
                </div>

                <div class="mb-5">
                    <label for="email" class="block text-sm font-black text-[#8faaf3] mb-2 pl-2">Alamat Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                           class="w-full bg-[#f4f9ff] border-2 border-[#e0fbfc] focus:border-[#5a76c8] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]"
                           placeholder="nama@email.com">
                    @error('email')<p class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2">{{ $message }}</p>@enderror
                </div>

                <div class="mb-5">
                    <label for="whatsapp" class="block text-sm font-black text-[#8faaf3] mb-2 pl-2">Nomor WhatsApp</label>
                    <input id="whatsapp" type="text" name="whatsapp" value="{{ old('whatsapp') }}" required
                           class="w-full bg-[#f4f9ff] border-2 border-[#e0fbfc] focus:border-[#5a76c8] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]"
                           placeholder="Contoh: 081234567890">
                    @error('whatsapp')<p class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2">{{ $message }}</p>@enderror
                </div>

                <div class="mb-5">
                    <label for="password" class="block text-sm font-black text-[#8faaf3] mb-2 pl-2">Kata Sandi</label>
                    <input id="password" type="password" name="password" required
                           class="w-full bg-[#f4f9ff] border-2 border-[#e0fbfc] focus:border-[#5a76c8] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]"
                           placeholder="Minimal 8 karakter">
                    @error('password')<p class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2">{{ $message }}</p>@enderror
                </div>

                <div class="mb-8">
                    <label for="password_confirmation" class="block text-sm font-black text-[#8faaf3] mb-2 pl-2">Konfirmasi Sandi</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                           class="w-full bg-[#f4f9ff] border-2 border-[#e0fbfc] focus:border-[#5a76c8] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]"
                           placeholder="Ulangi sandi">
                </div>

                <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-lg py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg shadow-[#5a76c8]/30 border-2 border-white">
                    Daftar Sekarang 🎉
                </button>

                <div class="text-center mt-6">
                    <p class="text-sm font-bold text-[#8faaf3]">Sudah punya akun? 
                        <a href="{{ route('login') }}" class="text-[#5a76c8] font-black hover:underline">Masuk di sini</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>