@extends('layouts.guest')

@section('title', 'Masuk - Wiboost Store')
@section('hero_badge', 'Akses pelanggan')
@section('hero_title', 'Masuk untuk cek pesanan, saldo, dan riwayat transaksi kapan saja.')
@section('hero_copy', 'Halaman login sekarang mengikuti layout guest yang sama dengan flow lain, jadi tampilannya lebih stabil di mobile dan lebih mudah dirawat.')

@section('content')
    <div class="mx-auto w-full max-w-md">
        <div class="mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 rounded-full border-2 border-white bg-[#f4f9ff] px-4 py-2 text-sm font-black text-[#5a76c8] shadow-sm transition hover:bg-[#eaf4ff]">
                <span class="flex h-10 w-10 items-center justify-center rounded-[1rem] bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] text-base text-white shadow-inner">W</span>
                <span>Kembali ke homepage</span>
            </a>
        </div>

        <div class="mb-8">
            <p class="text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Masuk</p>
            <h2 class="mt-2 text-3xl font-black tracking-tight text-[#2b3a67]">Selamat datang kembali</h2>
            <p class="mt-2 text-sm font-bold text-slate-500">Masuk untuk lanjut checkout layanan digital, lihat history, dan kelola saldo akunmu.</p>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-[1.75rem] border-2 border-white bg-[#e6fff7] px-5 py-4 text-sm font-bold text-emerald-600 shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-black text-[#5a76c8]">Alamat Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    class="w-full rounded-[1.4rem] border-2 border-[#e0ebff] bg-[#f8fbff] px-5 py-4 text-sm font-bold text-[#2b3a67] outline-none transition focus:border-[#5a76c8] focus:bg-white"
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
                    class="w-full rounded-[1.4rem] border-2 border-[#e0ebff] bg-[#f8fbff] px-5 py-4 text-sm font-bold text-[#2b3a67] outline-none transition focus:border-[#5a76c8] focus:bg-white"
                    placeholder="Masukkan kata sandi">
                @error('password')
                    <p class="mt-2 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>
                @enderror
            </div>

            <label for="remember_me" class="flex items-center gap-3 rounded-[1.4rem] border-2 border-white bg-[#f8fbff] px-4 py-3 shadow-sm">
                <input id="remember_me" type="checkbox" name="remember" class="h-5 w-5 rounded-md border-[#8faaf3] text-[#5a76c8] focus:ring-[#5a76c8]">
                <span class="text-sm font-bold text-slate-500">Tetap masuk di perangkat ini</span>
            </label>

            <button type="submit" class="w-full rounded-[1.5rem] border-2 border-white bg-[#5a76c8] px-5 py-4 text-base font-black text-white shadow-lg shadow-[#5a76c8]/30 transition hover:bg-[#4760a9]">
                Masuk Sekarang
            </button>
        </form>

        <p class="mt-6 text-center text-sm font-bold text-slate-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-[#5a76c8] transition hover:text-[#4760a9]">Daftar di sini</a>
        </p>
    </div>
@endsection
