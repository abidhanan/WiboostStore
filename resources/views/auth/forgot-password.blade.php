@extends('layouts.guest')

@section('title', 'Lupa Sandi - Wiboost Store')
@section('hero_badge', 'Pemulihan akun')
@section('hero_title', 'Reset kata sandi dengan alur yang rapi dan mudah dipakai di layar kecil.')
@section('hero_copy', 'Kami kirim tautan reset ke email terdaftar agar pelanggan bisa kembali masuk tanpa kebingungan.')

@section('content')
    <div class="mx-auto w-full max-w-md">
        <div class="mb-8">
            <p class="text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Reset Password</p>
            <h2 class="mt-2 text-3xl font-black tracking-tight text-[#2b3a67]">Lupa kata sandi?</h2>
            <p class="mt-2 text-sm font-bold text-slate-500">Masukkan email yang terdaftar dan kami akan kirim tautan untuk membuat sandi baru.</p>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-[1.75rem] border-2 border-white bg-[#e6fff7] px-5 py-4 text-sm font-bold text-emerald-600 shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-black text-[#5a76c8]">Alamat Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full rounded-[1.4rem] border-2 border-[#e0ebff] bg-[#f8fbff] px-5 py-4 text-sm font-bold text-[#2b3a67] outline-none transition focus:border-[#5a76c8] focus:bg-white"
                    placeholder="nama@email.com">
                @error('email')
                    <p class="mt-2 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-[1.5rem] border-2 border-white bg-[#5a76c8] px-5 py-4 text-base font-black text-white shadow-lg shadow-[#5a76c8]/30 transition hover:bg-[#4760a9]">
                Kirim Link Reset
            </button>
        </form>

        <p class="mt-6 text-center text-sm font-bold text-slate-500">
            <a href="{{ route('login') }}" class="text-[#5a76c8] transition hover:text-[#4760a9]">Kembali ke halaman masuk</a>
        </p>
    </div>
@endsection
