<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sandi Baru - Wiboost Store</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Nunito', sans-serif; background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); } </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-y-auto py-12">
    
    <div class="w-full max-w-md relative z-10">
        <div class="flex justify-center mb-8">
            <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-[#5a76c8] font-black text-3xl shadow-lg border-4 border-white">W</div>
        </div>

        <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-2xl shadow-[#5a76c8]/20 border-4 border-white">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-black text-[#2b3a67]">Buat Sandi Baru 🔐</h2>
            </div>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="mb-5">
                    <label for="email" class="block text-sm font-black text-[#8faaf3] mb-2 pl-2">Alamat Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required readonly
                           class="w-full bg-[#f0f5ff] border-2 border-transparent rounded-[1.5rem] px-5 py-4 text-[#8faaf3] font-black outline-none cursor-not-allowed">
                    @error('email')<p class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2">{{ $message }}</p>@enderror
                </div>

                <div class="mb-5">
                    <label for="password" class="block text-sm font-black text-[#8faaf3] mb-2 pl-2">Sandi Baru</label>
                    <input id="password" type="password" name="password" required autofocus autocomplete="new-password"
                           class="w-full bg-[#f4f9ff] border-2 border-[#e0fbfc] focus:border-[#5a76c8] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition"
                           placeholder="Sandi baru kamu">
                    @error('password')<p class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2">{{ $message }}</p>@enderror
                </div>

                <div class="mb-8">
                    <label for="password_confirmation" class="block text-sm font-black text-[#8faaf3] mb-2 pl-2">Ulangi Sandi Baru</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full bg-[#f4f9ff] border-2 border-[#e0fbfc] focus:border-[#5a76c8] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition"
                           placeholder="Ketik ulang sandi baru">
                </div>

                <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg shadow-[#5a76c8]/30 border-2 border-white">
                    Simpan Sandi Baru
                </button>
            </form>
        </div>
    </div>
</body>
</html>