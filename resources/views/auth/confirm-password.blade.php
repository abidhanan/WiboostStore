@extends('layouts.guest')

@section('title', 'Konfirmasi Keamanan - Wiboost Store')

@section('content')
    <div class="mx-auto w-full max-w-md text-center sm:text-left">
        <div class="mb-8">
            <div class="inline-block rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1 text-xs font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                Keamanan
            </div>
            <h2 class="text-3xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm sm:text-4xl">
                Konfirmasi Sandi!
            </h2>
            <p class="mt-2 text-sm font-bold text-[#8faaf3]">Masukkan kata sandi kamu lagi untuk melanjutkan!</p>
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

            <button type="submit" class="mt-2 flex w-full items-center justify-center rounded-full border-4 border-white bg-[#5a76c8] px-5 py-4 text-base font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform hover:bg-[#4760a9] active:scale-95">
                Konfirmasi & Lanjut
            </button>
        </form>
    </div>
@endsection
