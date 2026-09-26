@extends('layouts.guest')

@section('title', 'Masuk - Wiboost Store')

@section('content')
    <div class="mx-auto w-full max-w-md text-center sm:text-left">
        <div class="mb-8">
            <div class="inline-block rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1 text-xs font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                Yuk Masuk!
            </div>
            <h1 class="font-black tracking-tight text-[#2b3a67] drop-shadow-sm" style="white-space: nowrap; font-size: clamp(1.35rem, 6.6vw, 2.25rem); line-height: 1.1;">
                Selamat Datang Kembali!
            </h1>
            <p class="mt-2 text-sm font-bold text-[#8faaf3]">Silakan masuk untuk melakukan transaksi!</p>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-[1.5rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-left text-sm font-bold text-emerald-600 shadow-lg shadow-emerald-100">
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
                <div class="relative">
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                        class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 pr-14 text-sm font-bold text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] focus:bg-white"
                        placeholder="Masukkan kata sandi">
                    <button type="button" data-password-toggle="password" aria-label="Lihat password" class="absolute right-4 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border-2 border-white bg-white text-[#5a76c8] shadow-sm transition hover:bg-[#e0fbfc]">
                        <svg data-eye-open class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                        <svg data-eye-closed class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.6 5.4A9.6 9.6 0 0 1 12 5.25c6 0 9.75 6.75 9.75 6.75a16.4 16.4 0 0 1-3.1 3.8M6.1 6.8A16.5 16.5 0 0 0 2.25 12S6 18.75 12 18.75c1.55 0 2.96-.45 4.2-1.08"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="mt-2 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>
                @enderror
            </div>

            <label for="remember_me" class="flex cursor-pointer items-center gap-3 px-1">
                <input id="remember_me" type="checkbox" name="remember" class="h-5 w-5 rounded-md border-2 border-[#5a76c8] bg-white text-[#5a76c8] shadow-sm focus:ring-2 focus:ring-[#bde0fe]" style="accent-color: #5a76c8;">
                <span class="text-sm font-black text-[#5a76c8]">Tetap masuk di perangkat ini</span>
            </label>

            <button type="submit" class="flex w-full items-center justify-center rounded-full border-4 border-white bg-[#5a76c8] px-5 py-4 text-base font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform hover:bg-[#4760a9] active:scale-95">
                Masuk Sekarang
            </button>

            <div class="space-y-4">
                <div class="flex items-center gap-3 text-center">
                    <span class="h-1 flex-1 rounded-full bg-[#edf5ff]"></span>
                    <span class="text-xs font-black uppercase tracking-widest text-[#8faaf3]">atau</span>
                    <span class="h-1 flex-1 rounded-full bg-[#edf5ff]"></span>
                </div>
                @include('auth.partials.google-button', ['label' => 'Masuk dengan Google'])
            </div>
        </form>

        <p class="mt-6 text-center text-sm font-black text-[#8faaf3]">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-[#5a76c8] underline decoration-2 underline-offset-4 transition hover:text-[#4760a9]">Daftar di sini</a>
        </p>
    </div>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.passwordToggle);
                if (!input) {
                    return;
                }

                const shouldShow = input.type === 'password';
                input.type = shouldShow ? 'text' : 'password';
                button.setAttribute('aria-label', shouldShow ? 'Sembunyikan password' : 'Lihat password');
                button.querySelector('[data-eye-open]')?.classList.toggle('hidden', shouldShow);
                button.querySelector('[data-eye-closed]')?.classList.toggle('hidden', !shouldShow);
            });
        });
    </script>
@endsection
