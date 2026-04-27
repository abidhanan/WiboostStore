@extends('layouts.guest')

@section('title', 'Masuk - Wiboost Store')

@section('content')
    <div class="mx-auto w-full max-w-md text-center sm:text-left">
        <div class="mb-8">
            <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-xs border-2 border-white shadow-sm uppercase tracking-widest">
                Yuk, Masuk!
            </div>
            <h2 class="text-3xl sm:text-4xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm">
                Selamat Datang <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#5a76c8] to-[#9a8ce5]">Kembali!</span> ✨
            </h2>
            <p class="mt-2 text-sm font-bold text-[#8faaf3]">Silakan masuk untuk melanjutkan transaksi.</p>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-[1.5rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-sm font-bold text-emerald-600 shadow-lg shadow-emerald-100 text-left">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5 text-left">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-black text-[#5a76c8]">Alamat Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 text-sm font-bold text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] focus:bg-white"
                    placeholder="nama@email.com">
                @error('email')
                    <p class="mt-2 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="mb-2 flex items-center justify-between gap-4">
                    <label for="password" class="block text-sm font-black text-[#5a76c8]">Kata Sandi</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs font-black text-[#8faaf3] transition hover:text-[#5a76c8]">Lupa sandi?</a>
                    @endif
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 text-sm font-bold text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] focus:bg-white"
                    placeholder="Masukkan kata sandi">
                @error('password')
                    <p class="mt-2 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>
                @enderror
            </div>

            <label for="remember_me" class="flex items-center gap-3 rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-4 py-3 shadow-sm hover:border-[#bde0fe] transition cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember" class="h-5 w-5 rounded-md border-2 border-white bg-white text-[#5a76c8] focus:ring-[#5a76c8] shadow-inner">
                <span class="text-sm font-black text-[#8faaf3]">Tetap masuk di perangkat ini</span>
            </label>

            <button type="submit" class="w-full rounded-full border-4 border-white bg-[#5a76c8] px-5 py-4 text-base font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9] flex items-center justify-center gap-2">
                Masuk Sekarang 🚀
            </button>
        </form>

        <p class="mt-6 text-center text-sm font-black text-[#8faaf3]">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-[#5a76c8] transition hover:text-[#4760a9] underline decoration-2 underline-offset-4">Daftar di sini</a>
        </p>
    </div>
@endsection