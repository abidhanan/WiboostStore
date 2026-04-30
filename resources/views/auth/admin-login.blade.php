@extends('layouts.guest')

@section('title', 'Login Admin - Wiboost Store')

@section('content')
    <div class="mx-auto w-full max-w-md text-center sm:text-left">
        <div class="mb-8">
            <div class="mb-4 flex justify-center sm:justify-start">
            </div>
            <div class="inline-block rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1 text-xs font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                Login Admin
            </div>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm sm:text-4xl">
                Selamat Datang Admin!
            </h2>
            <p class="mt-2 text-sm font-bold text-[#8faaf3]">Portal masuk khusus admin Wiboost Store.</p>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-[1.5rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-left text-sm font-bold text-emerald-600 shadow-lg shadow-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}" class="space-y-5 text-left">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-black text-[#5a76c8]">Email Admin</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 text-sm font-bold text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe] focus:bg-white"
                    placeholder="admin@email.com">
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
                    class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 text-sm font-bold text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe] focus:bg-white"
                    placeholder="Masukkan kata sandi admin">
                @error('password')
                    <p class="mt-2 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>
                @enderror
            </div>

            <label for="remember_me" class="flex cursor-pointer items-center gap-3 rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-4 py-3 shadow-sm transition hover:border-[#bde0fe]">
                <input id="remember_me" type="checkbox" name="remember" class="h-5 w-5 rounded-md border-2 border-white bg-white text-[#5a76c8] shadow-inner focus:ring-[#5a76c8]">
                <span class="text-sm font-black text-[#8faaf3]">Tetap masuk di perangkat ini</span>
            </label>

            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-[#5a76c8] px-5 py-4 text-base font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform hover:bg-[#4760a9] active:scale-95">
                Masuk Sebagai Admin
            </button>
        </form>
        
    </div>
@endsection
