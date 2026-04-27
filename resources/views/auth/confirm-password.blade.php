@extends('layouts.guest')

@section('title', 'Konfirmasi Keamanan - Wiboost Store')

@section('content')
    <div class="mx-auto w-full max-w-md text-center sm:text-left">
        <div class="mb-8">
            <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-xs border-2 border-white shadow-sm uppercase tracking-widest">
                Keamanan
            </div>
            <h2 class="text-3xl sm:text-4xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm">
                Konfirmasi <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#5a76c8] to-[#9a8ce5]">Sandi</span> 🔐
            </h2>
            <p class="mt-2 text-sm font-bold text-[#8faaf3]">Masukkan kata sandi kamu lagi untuk melanjutkan ke area yang lebih sensitif.</p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5 text-left">
            @csrf

            <div>
                <label for="password" class="mb-2 block text-sm font-black text-[#5a76c8]">Kata Sandi</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 text-sm font-bold text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] focus:bg-white"
                    placeholder="Masukkan kata sandi">
                @error('password')
                    <p class="mt-2 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-full border-4 border-white bg-[#5a76c8] px-5 py-4 text-base font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9] flex items-center justify-center gap-2 mt-2">
                Konfirmasi & Lanjut 🚀
            </button>
        </form>
    </div>
@endsection