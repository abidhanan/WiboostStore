@extends('layouts.guest')

@section('title', 'Lupa Sandi - Wiboost Store')

@section('content')
    <div class="mx-auto w-full max-w-md text-center sm:text-left">
        <div class="mb-8">
            <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-xs border-2 border-white shadow-sm uppercase tracking-widest">
                Reset Kata Sandi
            </div>
            <h2 class="text-3xl sm:text-4xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm">
                Lupa <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#5a76c8] to-[#9a8ce5]">Sandi?</span> 🤔
            </h2>
            <p class="mt-2 text-sm font-bold text-[#8faaf3]">Masukkan email kamu, dan kami akan kirimkan tautan untuk membuat kata sandi baru.</p>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-[1.5rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-sm font-bold text-emerald-600 shadow-lg shadow-emerald-100 text-left">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5 text-left">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-black text-[#5a76c8]">Alamat Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 text-sm font-bold text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] focus:bg-white"
                    placeholder="nama@email.com">
                @error('email')
                    <p class="mt-2 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-full border-4 border-white bg-[#5a76c8] px-5 py-4 text-base font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9] flex items-center justify-center gap-2 mt-2">
                Kirim Tautan Reset ✉️
            </button>
        </form>

        <p class="mt-6 text-center text-sm font-black text-[#8faaf3]">
            <a href="{{ route('login') }}" class="text-[#5a76c8] transition hover:text-[#4760a9] underline decoration-2 underline-offset-4">Kembali ke halaman masuk</a>
        </p>
    </div>
@endsection