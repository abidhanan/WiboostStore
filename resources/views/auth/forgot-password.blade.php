<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Sandi - Wiboost Store</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Nunito', sans-serif; background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); } </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    <div class="w-full max-w-md relative z-10">
        <div class="flex justify-center mb-8">
            <a href="{{ route('login') }}" class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-[#5a76c8] font-black text-3xl shadow-lg border-4 border-white hover:scale-110 transition-transform">W</a>
        </div>

        <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-2xl shadow-[#5a76c8]/20 border-4 border-white">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-black text-[#2b3a67]">Lupa Sandi? 🤔</h2>
                <p class="text-[#8faaf3] font-bold text-sm mt-2">Nggak masalah! Masukkan email yang terdaftar, nanti kami kirimkan link untuk buat sandi baru.</p>
            </div>

            @if (session('status'))
                <div class="mb-6 font-bold text-sm text-emerald-500 bg-[#e6fff7] p-4 rounded-[1.5rem] border-2 border-white text-center">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-6">
                    <label for="email" class="block text-sm font-black text-[#8faaf3] mb-2 pl-2">Alamat Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full bg-[#f4f9ff] border-2 border-[#e0fbfc] focus:border-[#5a76c8] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]"
                           placeholder="nama@email.com">
                    @error('email')<p class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg shadow-[#5a76c8]/30 border-2 border-white">
                    Kirim Link Reset 📩
                </button>
                
                <div class="text-center mt-6">
                    <a href="{{ route('login') }}" class="text-sm font-bold text-[#8faaf3] hover:text-[#5a76c8] transition-colors">Kembali ke halaman Masuk</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>